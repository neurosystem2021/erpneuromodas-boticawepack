<?php
// Identity always comes from the authenticated PHP session, never request fields.
function nstMovementActor($db) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['user']) || (isset($_SESSION['expire']) && time() > $_SESSION['expire'])) throw new \RuntimeException('INICIA SESIÓN NUEVAMENTE.', 401);
    $query = $db->prepare('SELECT Usuario, IdUsuarioPerfil FROM Seg_Usuario WHERE Usuario = ? AND COALESCE(Anulado, 0) = 0');
    $query->execute(array($_SESSION['user']));
    $user = $query->fetch(\PDO::FETCH_ASSOC);
    if (!$user) throw new \RuntimeException('USUARIO NO AUTORIZADO.', 403);
    $allowed = require __DIR__ . '/movement-control-config.php';
    $user['superadmin'] = (int)$user['IdUsuarioPerfil'] === 1 && in_array(strtolower($user['Usuario']), array_map('strtolower', $allowed), true);
    $permission = isset($_SESSION['permisos']['ReporteRegMovimiento.php']) ? $_SESSION['permisos']['ReporteRegMovimiento.php'] : array();
    $user['read'] = (int)$user['IdUsuarioPerfil'] === 1 || !empty($permission['Lectura']);
    $user['write'] = (int)$user['IdUsuarioPerfil'] === 1 || (!empty($permission['Lectura']) && !empty($permission['Escritura']));
    return $user;
}
function nstMovementReason($request) {
    $reason = trim((string)$request->getParam('motivo'));
    if (mb_strlen($reason) < 5 || mb_strlen($reason) > 1000) throw new \RuntimeException('INGRESA UN MOTIVO ENTRE 5 Y 1000 CARACTERES.', 400);
    return $reason;
}
function nstMovementRecord($db, $id) {
    if (!$id || strlen((string)$id) > 255) throw new \RuntimeException('MOVIMIENTO INVÁLIDO.', 400);
    $query = $db->prepare('SELECT m.*, t.Tipo, t.VaRegCompra, t.TipoMovimiento, p.Proveedor FROM Lo_Movimiento m INNER JOIN Lo_MovimientoTipo t ON t.IdMovimientoTipo = m.IdMovimientoTipo LEFT JOIN Lo_Proveedor p ON p.IdProveedor = m.IdProveedor WHERE m.Hash = ? FOR UPDATE');
    $query->execute(array($id));
    $row = $query->fetch(\PDO::FETCH_ASSOC);
    if (!$row) throw new \RuntimeException('EL MOVIMIENTO YA NO EXISTE. ACTUALIZA EL REPORTE.', 404);
    return $row;
}
function nstMovementAudit($db, $row, $actor, $action, $reason, $before, $after) {
    $query = $db->prepare('INSERT INTO Lo_MovimientoAuditoria (HashMovimiento, Accion, Usuario, Fecha, Motivo, Proveedor, DocumentoAnterior, DocumentoNuevo, Antes, Despues) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $document = function ($value) { return $value ? $value['TipoMovimiento'] . ' | ' . $value['Serie'] . '-' . $value['Numero'] : ''; };
    $query->execute(array($row['Hash'], $action, $actor['Usuario'], getNow(), $reason, $row['Proveedor'], $document($before), $document($after), json_encode($before, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), json_encode($after, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)));
}
function nstMovementFailure($db, $response, $error) {
    if ($db->inTransaction()) $db->rollBack();
    $status = in_array($error->getCode(), array(400, 401, 403, 404, 409), true) ? $error->getCode() : 500;
    if ($status === 500) error_log('Movement audit: ' . $error->getMessage());
    return $response->withJson(array('error' => $status === 500 ? 'NO SE PUDO COMPLETAR LA OPERACIÓN. NO SE GUARDARON CAMBIOS.' : $error->getMessage()), $status);
}
$app->get('/movimiento/control/permisos', function ($request, $response) {
    try {
        $actor = nstMovementActor($this->db);
        if (!$actor['read']) throw new \RuntimeException('SIN PERMISO PARA VER ESTE REPORTE.', 403);
        $types = $this->db->query('SELECT IdMovimientoTipo, TipoMovimiento FROM Lo_MovimientoTipo WHERE Tipo = 0 AND COALESCE(Anulado,0) = 0 ORDER BY TipoMovimiento')->fetchAll(\PDO::FETCH_ASSOC);
        return $response->withJson(array('editar' => $actor['superadmin'], 'eliminar' => $actor['write'], 'tipos' => $types));
    } catch (\Throwable $error) { return nstMovementFailure($this->db, $response, $error); }
});
$app->get('/movimiento/control/historial', function ($request, $response) {
    try {
        $actor = nstMovementActor($this->db);
        if (!$actor['read']) throw new \RuntimeException('SIN PERMISO PARA VER ESTE REPORTE.', 403);
        $from = $request->getParam('desde'); $to = $request->getParam('hasta');
        foreach (array($from, $to) as $date) {
            $parsed = \DateTime::createFromFormat('!Y-m-d', (string)$date);
            if (!$parsed || $parsed->format('Y-m-d') !== $date) throw new \RuntimeException('SELECCIONA FECHAS VÁLIDAS.', 400);
        }
        if ($from > $to) throw new \RuntimeException('REVISA EL RANGO DE FECHAS.', 400);
        $hash = trim((string)$request->getParam('hash'));
        $query = $this->db->prepare('SELECT IdAuditoria, HashMovimiento, Accion, Usuario, Fecha, Motivo, Proveedor, DocumentoAnterior, DocumentoNuevo FROM Lo_MovimientoAuditoria WHERE Fecha >= ? AND Fecha < DATE_ADD(?, INTERVAL 1 DAY)' . ($hash ? ' AND HashMovimiento = ?' : '') . ' ORDER BY IdAuditoria DESC LIMIT 5001');
        $params = array($from, $to); if ($hash) $params[] = $hash;
        $query->execute($params);
        $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
        return $response->withJson(array('rows' => array_slice($rows, 0, 5000), 'limitado' => count($rows) > 5000));
    } catch (\Throwable $error) { return nstMovementFailure($this->db, $response, $error); }
});
$app->post('/movimiento/control/editar', function ($request, $response) {
    try {
        $actor = nstMovementActor($this->db);
        if (!$actor['superadmin']) throw new \RuntimeException('SOLO EL SUPERADMIN PUEDE CORREGIR COMPROBANTES.', 403);
        $reason = nstMovementReason($request);
        $series = mb_strtoupper(trim((string)$request->getParam('serie')), 'UTF-8');
        $number = filter_var($request->getParam('numero'), FILTER_VALIDATE_INT);
        $type = filter_var($request->getParam('tipo'), FILTER_VALIDATE_INT);
        if (!$series || mb_strlen($series) > 255 || !$number || $number < 1 || $number > 2147483647 || !$type) throw new \RuntimeException('REVISA TIPO, SERIE Y NÚMERO DEL COMPROBANTE.', 400);
        $this->db->beginTransaction();
        $row = nstMovementRecord($this->db, $request->getParam('idMov'));
        if ((int)$row['Tipo'] !== 0 || (int)$row['Anulado'] !== 0) throw new \RuntimeException('SOLO SE CORRIGEN COMPRAS O INGRESOS ACTIVOS.', 400);
        $query = $this->db->prepare('SELECT TipoMovimiento, VaRegCompra FROM Lo_MovimientoTipo WHERE IdMovimientoTipo = ? AND Tipo = 0 AND COALESCE(Anulado,0) = 0');
        $query->execute(array($type)); $newType = $query->fetch(\PDO::FETCH_ASSOC);
        if (!$newType || (int)$newType['VaRegCompra'] !== (int)$row['VaRegCompra']) throw new \RuntimeException('EL NUEVO TIPO DEBE PERTENECER AL MISMO REGISTRO DE COMPRAS / INGRESOS.', 400);
        $query = $this->db->prepare('SELECT Hash FROM Lo_Movimiento WHERE IdProveedor = ? AND IdMovimientoTipo = ? AND Serie = ? AND Numero = ? AND Hash <> ? AND COALESCE(Anulado,0) = 0 LIMIT 1');
        $query->execute(array($row['IdProveedor'], $type, $series, $number, $row['Hash']));
        if ($query->fetchColumn()) throw new \RuntimeException('YA EXISTE ESE COMPROBANTE PARA EL PROVEEDOR.', 409);
        if ((int)$row['IdMovimientoTipo'] === $type && $row['Serie'] === $series && (int)$row['Numero'] === $number) throw new \RuntimeException('NO HAY CAMBIOS PARA GUARDAR.', 400);
        $after = array_merge($row, array('IdMovimientoTipo' => $type, 'TipoMovimiento' => $newType['TipoMovimiento'], 'Serie' => $series, 'Numero' => $number, 'UsuarioMod' => $actor['Usuario'], 'FechaMod' => getNow()));
        $query = $this->db->prepare('UPDATE Lo_Movimiento SET IdMovimientoTipo = ?, Serie = ?, Numero = ?, UsuarioMod = ?, FechaMod = ? WHERE Hash = ?');
        $query->execute(array($type, $series, $number, $actor['Usuario'], $after['FechaMod'], $row['Hash']));
        nstMovementAudit($this->db, $row, $actor, 'EDITAR', $reason, $row, $after);
        $this->db->commit();
        return $response->withJson(array('success' => true));
    } catch (\Throwable $error) { return nstMovementFailure($this->db, $response, $error); }
});
$nstDeleteMovement = function ($request, $response) {
    try {
        $actor = nstMovementActor($this->db);
        if (!$actor['write']) throw new \RuntimeException('SIN PERMISO PARA ELIMINAR MOVIMIENTOS.', 403);
        $reason = nstMovementReason($request);
        $this->db->beginTransaction();
        $row = nstMovementRecord($this->db, $request->getParam('idMov'));
        if ((int)$row['Anulado'] !== 0) throw new \RuntimeException('EL MOVIMIENTO YA ESTA ANULADO.', 409);
        $detail = $this->db->prepare('SELECT * FROM Lo_MovimientoDetalle WHERE hashMovimiento = ?');
        $detail->execute(array($row['Hash']));
        $before = $row; $before['Detalle'] = $detail->fetchAll(\PDO::FETCH_ASSOC);
        $after = array_merge($before, array('Anulado' => 1, 'UsuarioMod' => $actor['Usuario'], 'FechaMod' => getNow()));
        $query = $this->db->prepare('UPDATE Lo_Movimiento SET Anulado = 1, UsuarioMod = ?, FechaMod = ? WHERE Hash = ?');
        $query->execute(array($actor['Usuario'], $after['FechaMod'], $row['Hash']));
        nstMovementAudit($this->db, $row, $actor, 'ANULAR', $reason, $before, $after);
        $this->db->commit();
        return $response->withJson(array('success' => true));
    } catch (\Throwable $error) { return nstMovementFailure($this->db, $response, $error); }
};
$nstUpdateMovementPeriod = function ($request, $response) {
    try {
        $actor = nstMovementActor($this->db);
        if (!$actor['superadmin']) throw new \RuntimeException('SOLO EL SUPERADMIN PUEDE CORREGIR COMPROBANTES.', 403);
        $reason = nstMovementReason($request);
        $period = (string)$request->getParam('FechaPeriodoTributario');
        if (!preg_match('/^20[0-9]{2}(0[1-9]|1[0-2])$/', $period)) throw new \RuntimeException('PERIODO TRIBUTARIO INVÁLIDO.', 400);
        $this->db->beginTransaction();
        $row = nstMovementRecord($this->db, $request->getParam('IdMovimiento'));
        $after = array_merge($row, array('FechaPeriodoTributario' => $period));
        $query = $this->db->prepare('UPDATE Lo_Movimiento SET FechaPeriodoTributario = ?, UsuarioMod = ?, FechaMod = ? WHERE Hash = ?');
        $query->execute(array($period, $actor['Usuario'], getNow(), $row['Hash']));
        nstMovementAudit($this->db, $row, $actor, 'PERIODO', $reason, $row, $after);
        $this->db->commit(); return $response->withJson(array('updated' => true));
    } catch (\Throwable $error) { return nstMovementFailure($this->db, $response, $error); }
};
