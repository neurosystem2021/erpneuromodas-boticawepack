<?php
require_once __DIR__ . "/../config/database.php";

if (!function_exists('empresaConfigObtener')) {
    function empresaConfigObtener($idEmpresaSolicitada = null)
    {
        require_once __DIR__ . '/DBManager.php';
        $empresa = array();
        $meta = array();

        try {
            // El login debe poder renderizarse aun cuando MySQL este temporalmente
            // fuera de servicio. Las operaciones internas siguen exigiendo una
            // conexion valida, pero el arranque usa los valores visuales por defecto.
            $db = appMysqliConnection();

            $whereEmpresa = $idEmpresaSolicitada === null ? '' : ' WHERE IDEMPRESA=' . max(0, (int)$idEmpresaSolicitada);
            $result = $db->query("SELECT * FROM GEN_EMPRESA" . $whereEmpresa . " ORDER BY IDEMPRESA ASC LIMIT 1");
            if ($result) {
                $empresa = $result->fetch_assoc() ?: array();
                $result->free();
            }

            $idEmpresa = isset($empresa['IDEMPRESA']) ? (int) $empresa['IDEMPRESA'] : 1;
            if (!empty(appLocalDatabaseConfig()['wuanuko_multiissuer'])) {
                $profileStmt=$db->prepare('SELECT DireccionFiscal,Telefono,Correo FROM Cx_GrupoEmpresa WHERE IdEmpresa=? AND Activo=1');
                $profileStmt->bind_param('i',$idEmpresa);$profileStmt->execute();$profile=$profileStmt->get_result()->fetch_assoc();$profileStmt->close();
                if($profile)foreach(array('DireccionFiscal'=>'DIRECCION','Telefono'=>'TELEFONO','Correo'=>'EMAIL') as $from=>$to)if($profile[$from]!==null)$empresa[$to]=$profile[$from];
            }

            $stmt = $db->prepare("SELECT MetaKey, MetaValue FROM Gen_EmpresaConfig WHERE IdEmpresa = ? ORDER BY IdEmpresaConfig ASC");
            if ($stmt) {
                $stmt->bind_param('i', $idEmpresa);
                $stmt->execute();
                $resultMeta = $stmt->get_result();
                while ($row = $resultMeta->fetch_assoc()) {
                    $meta[$row['MetaKey']] = $row['MetaValue'];
                }
                $stmt->close();
            }
            $db->close();
        } catch (Throwable $error) {
            error_log('[IMCEDEN] No se pudo cargar la identidad de la empresa: ' . $error->getMessage());
        }

        return array('empresa' => $empresa, 'meta' => $meta);
    }
}

if (!function_exists('empresaConfigValor')) {
    function empresaConfigValor($config, $key, $default = '')
    {
        if (in_array($key, array('logo_principal','logo_ticket','imagen_proforma'), true) && !empty(appLocalDatabaseConfig()['wuanuko_multiissuer'])) return 'resources/images/wuanuko-mix-logo.png';
        if (isset($config['meta'][$key]) && trim((string) $config['meta'][$key]) !== '') {
            return $config['meta'][$key];
        }
        if (isset($config['empresa'][$key]) && trim((string) $config['empresa'][$key]) !== '') {
            return $config['empresa'][$key];
        }
        return $default;
    }
}

if (!function_exists('empresaConfigDatosImpresion')) {
    function empresaConfigDatosImpresion($config = null)
    {
        $config = $config ?: empresaConfigObtener();
        $empresa = isset($config['empresa']) ? $config['empresa'] : array();
        $razon = empresaConfigValor($config, 'RAZONSOCIAL', 'EMPRESA');
        $nombre = empresaConfigValor($config, 'nombre_comercial', $razon);
        $telefono = empresaConfigValor($config, 'CELULAR', empresaConfigValor($config, 'TELEFONO', ''));
        $ubicacion = array_filter(array(
            empresaConfigValor($config, 'DEPARTAMENTO', ''),
            empresaConfigValor($config, 'PROVINCIA', ''),
            empresaConfigValor($config, 'DISTRITO', '')
        ));

        return array(
            'razon_social' => $razon,
            'nombre_comercial' => $nombre,
            'ruc' => empresaConfigValor($config, 'RUC', ''),
            'direccion' => empresaConfigValor($config, 'DIRECCION', ''),
            'telefono' => $telefono,
            'ubicacion' => implode(' - ', $ubicacion),
            'actividad' => empresaConfigValor($config, 'actividad_comercial', ''),
            'consulta_web' => empresaConfigValor($config, 'consulta_web', '')
        );
    }
}

if (!function_exists('empresaConfigAplicarExcelLegacy')) {
    function empresaConfigAplicarExcelLegacy($libro)
    {
        $config = empresaConfigObtener();
        $datos = empresaConfigDatosImpresion($config);
        $logoRelativo = empresaConfigValor($config, 'logo_principal', 'resources/images/logo.png');
        $logo = realpath(__DIR__ . '/../' . ltrim($logoRelativo, '/\\'));

        foreach ($libro->getAllSheets() as $hoja) {
            $hoja->insertNewRowBefore(1, 4);
            $ultima = $hoja->getHighestColumn();
            if (PHPExcel_Cell::columnIndexFromString($ultima) < 6) $ultima = 'F';
            $hoja->mergeCells('B1:' . $ultima . '1')->mergeCells('B2:' . $ultima . '2');
            $hoja->setCellValue('B1', $datos['nombre_comercial']);
            $detalle = 'Razón social: ' . $datos['razon_social'] . ' | RUC: ' . $datos['ruc'] . ' | ' . $datos['direccion'];
            if ($datos['telefono'] !== '') $detalle .= ' | Tel: ' . $datos['telefono'];
            $hoja->setCellValue('B2', $detalle);
            $hoja->getStyle('B1')->getFont()->setBold(true)->setSize(15);
            $hoja->getStyle('B2')->getFont()->setSize(9);
            $hoja->getRowDimension(1)->setRowHeight(34);
            $hoja->getRowDimension(2)->setRowHeight(30);
            if ($logo && is_file($logo)) {
                $dibujo = new PHPExcel_Worksheet_Drawing();
                $dibujo->setName('Logo de empresa')->setPath($logo)->setCoordinates('A1')->setOffsetX(8)->setOffsetY(5)->setHeight(48)->setWorksheet($hoja);
            }
        }
    }
}

if (!function_exists('empresaConfigPorRucImpresion')) {
    function empresaConfigPorRucImpresion($ruc) {
        $config = empresaConfigObtener();
        if (!preg_match('/^\d{11}$/D', (string)$ruc)) return $config;
        $db = appPdoConnection();
        $stmt = $db->prepare('SELECT IDEMPRESA FROM GEN_EMPRESA WHERE RUC=? ORDER BY IDEMPRESA LIMIT 1');
        $stmt->execute([(string)$ruc]);$id=$stmt->fetchColumn();
        return $id === false ? $config : empresaConfigObtener((int)$id);
    }
}
if (!function_exists('empresaConfigDocumentoImpresion')) {
    function empresaConfigDocumentoImpresion($documento) {
        // Use the document's persisted identity; never change issuer from a query parameter.
        $id = $documento['IdEmpresaEmisora'] ?? null;
        return $id === null ? empresaConfigObtener() : empresaConfigObtener((int)$id);
    }
}
