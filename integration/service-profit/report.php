<?php
// Shared by the report screen and its PDF export.
function serviceProfitReport(PDO $db, array $filters)
{
    $dates = array();
    foreach (array('fechaInicio', 'fechaFin') as $key) {
        $value = isset($filters[$key]) ? (string) $filters[$key] : '';
        $date = DateTime::createFromFormat('!Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) throw new InvalidArgumentException('Selecciona fechas validas.');
        $dates[$key] = $date;
    }
    if ($dates['fechaInicio'] > $dates['fechaFin']) throw new InvalidArgumentException('La fecha inicial debe ser anterior o igual a la final.');
    $types = array();
    if ((string) ($filters['ServicioFlag'] ?? '1') === '1') $types[] = 2;
    if ((string) ($filters['MercaderiaFlag'] ?? '0') === '1') $types[] = 1;
    if (!$types) throw new InvalidArgumentException('Selecciona servicios o mercaderias.');
    $params = array(':start' => $dates['fechaInicio']->format('Y-m-d'), ':end' => $dates['fechaFin']->modify('+1 day')->format('Y-m-d'));
    $user = trim((string) ($filters['usuarioReg'] ?? ''));
    $userSql = '';
    if ($user !== '' && $user !== '%') {
        $userSql = ' AND v.UsuarioReg = :user';
        $params[':user'] = $user;
    }
    $sql = "SELECT v.UsuarioReg, p.IdProducto, p.Producto, DATE(v.FechaDoc) FechaReg,
        SUM(d.Cantidad) Cantidad,
        COALESCE(SUM(d.Cantidad * COALESCE(d.PrecioCosto,p.PrecioCosto,0))/NULLIF(SUM(d.Cantidad),0),0) PrecioCosto,
        COALESCE(SUM(d.Cantidad*d.Precio-COALESCE(d.Descuento,0))/NULLIF(SUM(d.Cantidad),0),0) PrecioVenta,
        ROUND(SUM(d.Cantidad*d.Precio-COALESCE(d.Descuento,0)),2) TotalVenta,
        ROUND(SUM(d.Cantidad*COALESCE(d.PrecioCosto,p.PrecioCosto,0)),2) TotalCosto
        FROM Ve_DocVenta v
        INNER JOIN Ve_DocVentaDet d ON d.IdDocVenta=v.IdDocVenta
        INNER JOIN Gen_Producto p ON p.IdProducto=d.IdProducto
        WHERE v.Anulado=0 AND v.IdTipoDoc<>11
        AND v.FechaDoc >= :start AND v.FechaDoc < :end
        AND p.OpcContable IN (" . implode(',', $types) . ")" . $userSql . "
        GROUP BY v.UsuarioReg,p.IdProducto,p.Producto,DATE(v.FechaDoc)
        ORDER BY FechaReg DESC,v.UsuarioReg,p.Producto";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$row) {
        foreach (array('Cantidad','PrecioCosto','PrecioVenta','TotalVenta','TotalCosto') as $key) $row[$key] = (float) $row[$key];
        $row['UtilidadBruta'] = round($row['TotalVenta'] - $row['TotalCosto'], 2);
    }
    unset($row);
    return $rows;
}
