<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

if (!function_exists('aplicarIdentidadEmpresaExcel')) {
function aplicarIdentidadEmpresaExcel($spreadsheet, $db)
{
    $empresa = obtenerEmpresaParaReporte($db);
    foreach ($spreadsheet->getAllSheets() as $sheet) {
        $yaTieneIdentidad = stripos((string) $sheet->getCell('B2')->getValue(), 'Razón social:') === 0;
        /* Retira identidades antiguas incrustadas en las plantillas. */
        $sheet->getDrawingCollection()->exchangeArray(array());
        foreach (range(1, min(10, $sheet->getHighestRow())) as $fila) {
            foreach ($sheet->getRowIterator($fila, $fila) as $row) {
                foreach ($row->getCellIterator() as $cell) {
                    $texto = (string) $cell->getValue();
                    if (stripos($texto, 'Nicol Villanueva') !== false || trim($texto) === '20489569800') $cell->setValue('');
                }
            }
        }

        if (!$yaTieneIdentidad) $sheet->insertNewRowBefore(1, 4);
        $ultimaColumna = $sheet->getHighestColumn();
        if (\PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($ultimaColumna) < 6) $ultimaColumna = 'F';
        $sheet->mergeCells('B1:' . $ultimaColumna . '1');
        $sheet->mergeCells('B2:' . $ultimaColumna . '2');
        $sheet->setCellValue('B1', $empresa['nombre']);
        $detalle = 'Razón social: ' . $empresa['razon_social'] . '  |  RUC: ' . $empresa['ruc'];
        if ($empresa['direccion'] !== '') $detalle .= '  |  ' . $empresa['direccion'];
        if ($empresa['telefono'] !== '') $detalle .= '  |  Tel: ' . $empresa['telefono'];
        $sheet->setCellValue('B2', $detalle);
        $sheet->getStyle('A1:' . $ultimaColumna . '2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3F9FC');
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(15)->getColor()->setARGB('FF092B4B');
        $sheet->getStyle('B2')->getFont()->setSize(9)->getColor()->setARGB('FF315A75');
        $sheet->getStyle('B1:B2')->getAlignment()->setVertical('center')->setWrapText(true);
        $sheet->getRowDimension(1)->setRowHeight(34);
        $sheet->getRowDimension(2)->setRowHeight(30);
        $sheet->getRowDimension(3)->setRowHeight(5);
        agregarLogoEmpresaReporte($sheet, $empresa, 'A1', 48);
        $sheet->getHeaderFooter()->setOddHeader('&C&14&B' . $empresa['nombre'] . '&R&RUC: ' . $empresa['ruc']);
    }
}
}

if (!function_exists('stringIngresoUndPunto')) {
function stringIngresoUndPunto($idProducto, $idAlmacen, $fechaHasta)
{
    $select = "(SELECT Lo_MovimientoDetalle.IdProducto, IFNULL(SUM(Lo_MovimientoDetalle.Cantidad), 0) AS cantidad FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        WHERE (Lo_MovimientoTipo.VaRegCompra = 1 OR Lo_MovimientoTipo.Tipo = 2) AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
        AND Lo_Movimiento.Anulado=0 ";
    $select .= " AND Lo_MovimientoDetalle.IdProducto IN (" . implode(',', $idProducto) . ")";
    $select .= " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta'";

    $select .= " GROUP BY Lo_MovimientoDetalle.IdProducto)";
    return $select;
}
}

if (!function_exists('stringIngresoCajaPunto')) {
function stringIngresoCajaPunto($idProducto, $idAlmacen, $fechaHasta)
{
    $select = "(SELECT Gen_ProductoDet.IdProductoDet AS IdProducto, IFNULL(SUM(Lo_MovimientoDetalle.Cantidad * Gen_ProductoDet.Cantidad), 0) AS cantidad 
        FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        INNER JOIN Gen_ProductoDet ON Lo_MovimientoDetalle.IdProducto = Gen_ProductoDet.IdProducto
        WHERE (Lo_MovimientoTipo.VaRegCompra = 1 OR Lo_MovimientoTipo.Tipo = 2) AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
        AND Lo_Movimiento.Anulado=0 ";
    $select .= " AND Lo_MovimientoDetalle.IdProducto IN (" . implode(',', $idProducto) . ")";
    $select .= " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta'";
    $select .= " GROUP BY Gen_ProductoDet.IdProductoDet)";
    return $select;
}
}

if (!function_exists('stringSalidaCajaPunto')) {
function stringSalidaCajaPunto($idProducto, $idAlmacen, $fechaHasta)
{
    $select = "(SELECT Gen_ProductoDet.IdProductoDet AS IdProducto, IFNULL(SUM(IF(Lo_MovimientoTipo.CodSunat = '07', IF(Lo_Movimiento.NotaIdMotivo IN ('01', '02', '06', '07'),Gen_ProductoDet.cantidad * Lo_MovimientoDetalle.Cantidad,0), Gen_ProductoDet.cantidad * Lo_MovimientoDetalle.Cantidad)), 0) AS cantidad
        FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        INNER JOIN Gen_ProductoDet ON Lo_MovimientoDetalle.IdProducto = Gen_ProductoDet.IdProducto
        WHERE (Lo_MovimientoTipo.VaRegCompra = 1 OR Lo_MovimientoTipo.Tipo = 2) AND Lo_Movimiento.IdAlmacenOrigen = $idAlmacen
            AND Lo_Movimiento.Anulado=0 ";
    $select .= " AND Lo_MovimientoDetalle.IdProducto IN (" . implode(',', $idProducto) . ")";
    $select .= " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta'";

    $select .= " GROUP BY Gen_ProductoDet.IdProductoDet)";
    return $select;
}
}

if (!function_exists('stringSalidaUndPunto')) {
function stringSalidaUndPunto($idProducto, $idAlmacen, $fechaHasta)
{
    $select = "(SELECT Lo_MovimientoDetalle.IdProducto, IFNULL(SUM(IF(Lo_MovimientoTipo.CodSunat = '07', IF(Lo_Movimiento.NotaIdMotivo IN ('01', '02', '06', '07'),Lo_MovimientoDetalle.Cantidad,0) ,Lo_MovimientoDetalle.Cantidad)), 0) AS cantidad FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        WHERE (Lo_MovimientoTipo.VaRegCompra = 1 OR Lo_MovimientoTipo.Tipo = 2) AND Lo_Movimiento.IdAlmacenOrigen = $idAlmacen
        AND Lo_Movimiento.Anulado=0 ";
    $select .= " AND Lo_MovimientoDetalle.IdProducto IN (" . implode(',', $idProducto) . ")";
    $select .= " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta'";
    $select .= " GROUP BY Lo_MovimientoDetalle.IdProducto)";
    return $select;
}
}

if (!function_exists('stringSalidaVentaUndPunto')) {
function stringSalidaVentaUndPunto($idProducto, $idAlmacen, $fechaHasta)
{
    /*
    CASE WHEN  
    Ve_DocVentaDet.cantidadPres > 0 then 
    Ve_DocVentaDet.cantidadPres else
    Ve_DocVentaDet.Cantidad end
    ,0),
      CASE WHEN  
    Ve_DocVentaDet.cantidadPres > 0 then 
    Ve_DocVentaDet.cantidadPres else
    Ve_DocVentaDet.Cantidad end
    )), 0) AS cantidad FROM Ve_DocVenta
     */
    $select = "
    (SELECT Ve_DocVentaDet.IdProducto, 
    IFNULL(SUM(IF(Ve_DocVentaTipoDoc.CodSunat = '07', 
    IF(Ve_DocVenta.NotaIdMotivo IN ('01', '02', '06', '07'),-1*
    Ve_DocVentaDet.Cantidad
    ,0),
    Ve_DocVentaDet.Cantidad
    )), 0) AS cantidad FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        WHERE Ve_DocVenta.IdAlmacen = $idAlmacen
        AND Ve_DocVenta.Anulado = 0
        AND Ve_DocVentaTipoDoc.VaRegVenta = 1 ";
    $select .= " AND Ve_DocVentaDet.IdProducto IN (" . implode(',', $idProducto) . ")";
    $select .= " AND Ve_DocVenta.FechaDoc < '$fechaHasta'";
    $select .= " GROUP BY Ve_DocVentaDet.IdProducto)";
    return $select;
}
}

if (!function_exists('stringSalidaVentaCajaPunto')) {
function stringSalidaVentaCajaPunto($idProducto, $idAlmacen, $fechaHasta)
{
    $select = "(SELECT Gen_ProductoDet.IdProductoDet AS IdProducto, IFNULL(SUM(IF(Ve_DocVentaTipoDoc.CodSunat = '07', IF(Ve_DocVenta.NotaIdMotivo IN ('01', '02', '06', '07'),-1*Gen_ProductoDet.cantidad * 

    Ve_DocVentaDet.Cantidad  
    ,0), Gen_ProductoDet.cantidad * 
    Ve_DocVentaDet.Cantidad 
    )), 0) AS cantidad
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        INNER JOIN Gen_ProductoDet ON Ve_DocVentaDet.IdProducto=Gen_ProductoDet.IdProducto
        WHERE Ve_DocVenta.IdAlmacen = $idAlmacen
        AND Ve_DocVenta.Anulado = 0
        AND Ve_DocVentaTipoDoc.VaRegVenta = 1 ";
    $select .= " AND Ve_DocVentaDet.IdProducto IN (" . implode(',', $idProducto) . ")";
    $select .= " AND Ve_DocVenta.FechaDoc < '$fechaHasta'";
    $select .= " GROUP BY Gen_ProductoDet.IdProductoDet)";
    return $select;
}
}

if (!function_exists('neuroMailAdmin')) {
function neuroMailAdmin($db) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $user = trim((string) ($_SESSION['user'] ?? ''));
    if ($user === '') return false;
    $stmt = $db->prepare("SELECT IdUsuarioPerfil FROM Seg_Usuario WHERE LOWER(Usuario)=LOWER(?) AND (Anulado<>1 OR Anulado IS NULL) LIMIT 1");
    $stmt->execute([$user]);
    $row = $stmt->fetch();
    return strtolower($user) === 'superadmin' || ($row && intval($row['IdUsuarioPerfil']) === 1);
}
}

if (!function_exists('neuroMailConfig')) {
function neuroMailConfig($db) {
    $keys = ['smtp_host','smtp_port','smtp_security','smtp_user','smtp_password','smtp_from_email','smtp_from_name'];
    $marks = implode(',', array_fill(0, count($keys), '?'));
    $stmt = $db->prepare("SELECT MetaKey,MetaValue FROM Gen_EmpresaConfig WHERE IdEmpresa=1 AND MetaKey IN ($marks)");
    $stmt->execute($keys); $config=[];
    foreach ($stmt->fetchAll() as $row) $config[$row['MetaKey']]=$row['MetaValue'];
    if (!empty($config['smtp_password'])) {
        $raw=base64_decode($config['smtp_password'],true); $key=hash('sha256',NRO_DOCUMENTO_EMPRESA.'|neuro-mail-v1',true);
        if ($raw!==false && strlen($raw)>16) { $iv=substr($raw,0,16); $config['smtp_password']=openssl_decrypt(substr($raw,16),'AES-256-CBC',$key,OPENSSL_RAW_DATA,$iv) ?: ''; }
    }
    return $config;
}
}

if (!function_exists('neuroMailSaveConfig')) {
function neuroMailSaveConfig($db,$config) {
    foreach ($config as $key=>$value) {
        $find=$db->prepare("SELECT IdEmpresaConfig FROM Gen_EmpresaConfig WHERE IdEmpresa=1 AND MetaKey=? LIMIT 1");$find->execute([$key]);$row=$find->fetch();
        if ($row) {$stmt=$db->prepare("UPDATE Gen_EmpresaConfig SET MetaValue=? WHERE IdEmpresaConfig=?");$stmt->execute([$value,$row['IdEmpresaConfig']]);}
        else {$stmt=$db->prepare("INSERT INTO Gen_EmpresaConfig (IdEmpresa,MetaKey,MetaValue) VALUES (1,?,?)");$stmt->execute([$key,$value]);}
    }
}
}

if (!function_exists('neuroVentasAppModulos')) {
function neuroVentasAppModulos($db) {
    $defaults = array('sale'=>1,'products'=>1,'history'=>1,'cash'=>1,'movements'=>1,'purchases'=>1,'stock'=>1,'dashboard'=>1);
    $stmt = $db->prepare("SELECT MetaKey,MetaValue FROM Gen_EmpresaConfig WHERE IdEmpresa=1 AND MetaKey LIKE 'ventas_app_modulo_%'");
    $stmt->execute();
    foreach ($stmt->fetchAll() as $row) {
        $key = substr((string) $row['MetaKey'], strlen('ventas_app_modulo_'));
        if (array_key_exists($key, $defaults)) $defaults[$key] = (int) ((string) $row['MetaValue'] === '1');
    }
    return $defaults;
}
}

if (!function_exists('neuroSmtpSend')) {
function neuroSmtpSend($config,$to,$subject,$html,$pdf=null,$filename='comprobante.pdf') {
    $host=trim($config['smtp_host']??'');$port=intval($config['smtp_port']??587);$security=strtolower($config['smtp_security']??'tls');
    if (!$host || empty($config['smtp_user']) || empty($config['smtp_password']) || empty($config['smtp_from_email'])) throw new Exception('La configuración SMTP está incompleta.');
    $target=($security==='ssl'?'ssl://':'').$host.':'.$port;$context=stream_context_create(['ssl'=>['verify_peer'=>true,'verify_peer_name'=>true,'allow_self_signed'=>false]]);
    $socket=@stream_socket_client($target,$errno,$errstr,15,STREAM_CLIENT_CONNECT,$context);if(!$socket)throw new Exception('No se pudo conectar al servidor SMTP: '.$errstr);stream_set_timeout($socket,20);
    try { neuroSmtpCmd($socket,null,220); neuroSmtpCmd($socket,'EHLO neuroapp.local',250); if($security==='tls'){neuroSmtpCmd($socket,'STARTTLS',220);if(!stream_socket_enable_crypto($socket,true,STREAM_CRYPTO_METHOD_TLS_CLIENT))throw new Exception('No se pudo activar TLS.');neuroSmtpCmd($socket,'EHLO neuroapp.local',250);} neuroSmtpCmd($socket,'AUTH LOGIN',334);neuroSmtpCmd($socket,base64_encode($config['smtp_user']),334);neuroSmtpCmd($socket,base64_encode($config['smtp_password']),235);neuroSmtpCmd($socket,'MAIL FROM:<'.$config['smtp_from_email'].'>',250);neuroSmtpCmd($socket,'RCPT TO:<'.$to.'>',[250,251]);neuroSmtpCmd($socket,'DATA',354);
        $boundary='=_NeuroApp_'.bin2hex(random_bytes(8));$headers=['From: '.($config['smtp_from_name']??'Neuro App').' <'.$config['smtp_from_email'].'>','To: <'.$to.'>','Subject: =?UTF-8?B?'.base64_encode($subject).'?=','MIME-Version: 1.0','Content-Type: multipart/mixed; boundary="'.$boundary.'"'];$body='--'.$boundary."\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n".chunk_split(base64_encode($html));if($pdf!==null)$body.='--'.$boundary."\r\nContent-Type: application/pdf; name=\"$filename\"\r\nContent-Disposition: attachment; filename=\"$filename\"\r\nContent-Transfer-Encoding: base64\r\n\r\n".chunk_split(base64_encode($pdf));$body.='--'.$boundary."--\r\n";$message=implode("\r\n",$headers)."\r\n\r\n".$body;neuroSmtpCmd($socket,preg_replace('/^\./m','..',$message)."\r\n.",250);neuroSmtpCmd($socket,'QUIT',221);
    } finally { fclose($socket); }
}
}

if (!function_exists('obtenerEmpresaParaReporte')) {
function obtenerEmpresaParaReporte($db)
{
    $empresa = array();
    $meta = array();
    $stmt = $db->query("SELECT * FROM GEN_EMPRESA ORDER BY IDEMPRESA ASC LIMIT 1");
    if ($stmt) $empresa = $stmt->fetch() ?: array();

    $idEmpresa = isset($empresa['IDEMPRESA']) ? (int) $empresa['IDEMPRESA'] : 1;
    $stmtMeta = $db->prepare("SELECT MetaKey, MetaValue FROM Gen_EmpresaConfig WHERE IdEmpresa = :idEmpresa ORDER BY IdEmpresaConfig ASC");
    $stmtMeta->execute(array('idEmpresa' => $idEmpresa));
    foreach ($stmtMeta->fetchAll() as $fila) $meta[$fila['MetaKey']] = $fila['MetaValue'];

    $valor = function ($key, $default = '') use ($empresa, $meta) {
        if (isset($meta[$key]) && trim((string) $meta[$key]) !== '') return trim((string) $meta[$key]);
        if (isset($empresa[$key]) && trim((string) $empresa[$key]) !== '') return trim((string) $empresa[$key]);
        return $default;
    };
    $razon = $valor('RAZONSOCIAL', 'EMPRESA');
    $logoRelativo = ltrim($valor('logo_principal', 'resources/images/logo.png'), '/\\');
    $logo = realpath(__DIR__ . '/../' . $logoRelativo);

    return array(
        'nombre' => $valor('nombre_comercial', $razon),
        'razon_social' => $razon,
        'ruc' => $valor('RUC'),
        'direccion' => $valor('DIRECCION'),
        'telefono' => $valor('CELULAR', $valor('TELEFONO')),
        'logo' => ($logo && is_file($logo)) ? $logo : null
    );
}
}

if (!function_exists('agregarLogoEmpresaReporte')) {
function agregarLogoEmpresaReporte($sheet, $empresa, $celda = 'A1', $alto = 70)
{
    if (empty($empresa['logo'])) return;
    $drawing = new Drawing();
    $drawing->setName('Logo de empresa');
    $drawing->setDescription($empresa['nombre']);
    $drawing->setPath($empresa['logo']);
    $drawing->setCoordinates($celda);
    $drawing->setOffsetX(8);
    $drawing->setOffsetY(5);
    $drawing->setHeight($alto);
    $drawing->setWorksheet($sheet);
}
}

if (!function_exists('neuroSmtpCmd')) {
function neuroSmtpCmd($socket,$command,$codes) { if ($command!==null) fwrite($socket,$command."\r\n"); $reply=neuroSmtpRead($socket); $code=intval(substr($reply,0,3)); if (!in_array($code,(array)$codes,true)) throw new Exception('SMTP rechazó la operación ('.$code.').'); return $reply; }
}

if (!function_exists('neuroSmtpRead')) {
function neuroSmtpRead($socket) { $text=''; while (($line=fgets($socket,515))!==false) { $text.=$line; if (strlen($line)<4 || $line[3]!=='-') break; } return $text; }
}

$app->get('/productos/codigo-siguiente/{codigo}', function (Request $request, Response $response, array $args) {
    $codigo = strtoupper(trim((string) ($args['codigo'] ?? '')));
    if ($codigo === '') {
        return $response->withJson(array('error' => 'Ingrese un código base.'), 400);
    }

    if (preg_match('/^(.*?)(\d+)(\D*)$/u', $codigo, $partes)) {
        $prefijo = $partes[1];
        $numero = (int) $partes[2];
        $sufijo = $partes[3];
        $stmt = $this->db->prepare("SELECT CodigoBarra FROM Gen_Producto WHERE CodigoBarra LIKE ?");
        $stmt->execute(array($prefijo . '%' . $sufijo));
        $ocupados = array();
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $existente = strtoupper(trim((string) $fila['CodigoBarra']));
            if (preg_match('/^' . preg_quote($prefijo, '/') . '(\d+)' . preg_quote($sufijo, '/') . '$/u', $existente, $coincidencia)) {
                $ocupados[(int) $coincidencia[1]] = true;
            }
        }
        while (isset($ocupados[$numero])) $numero++;
        $codigo = $prefijo . $numero . $sufijo;
    } else {
        $base = $codigo;
        $numero = 1;
        $stmt = $this->db->prepare("SELECT 1 FROM Gen_Producto WHERE UPPER(TRIM(CodigoBarra)) = ? LIMIT 1");
        do {
            $candidato = $numero === 1 ? $base : $base . $numero;
            $stmt->execute(array($candidato));
            $existe = $stmt->fetchColumn();
            $numero++;
        } while ($existe);
        $codigo = $candidato;
    }

    return $response->withJson(array('codigo' => $codigo));
});

$app->get('/productos/puntoventa/stock[/{idAlmacen}]', function (Request $request, Response $response, array $args) use ($app) {
    try {


        $ids = array();
        if ($request->getParam('idProductos')) {
            if ($request->getParam('idProductos')[0] != "") {
                $ids = $request->getParam('idProductos');
            } else {
                $ids = array();
            }
        } else {

            if (isset($request->getParam('q')[0]) && $request->getParam('q')[0] == '*') {

                $select = " SELECT Gen_Producto.IdProducto FROM Gen_ProductoCompuestoDet
                INNER JOIN Gen_ProductoCompuesto ON Gen_ProductoCompuestoDet.Gen_ProductoCompuesto_IdProductoCompuesto = Gen_ProductoCompuesto.IdProductoCompuesto
                INNER JOIN Gen_Producto ON Gen_ProductoCompuestoDet.Gen_Producto_IdProducto = Gen_Producto.IdProducto
                INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
                WHERE Gen_ProductoCompuesto.ProductoCompuesto LIKE  '%" . substr($request->getParam('q'), 1) . "%' AND Gen_Producto.Anulado=0  limit 20 ";
            } else {
                $select = "SELECT 
                Gen_Producto.IdProducto FROM Gen_Producto
                INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
                INNER JOIN Gen_ProductoFormaFarmaceutica ON Gen_Producto.IdProductoFormaFarmaceutica = Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica
                 INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
                 LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla
                ";
                // LEFT JOIN Gen_ProductoModelo ON Gen_ProductoModelo.IdProductoModelo = Gen_Producto.IdProductoModelo
                $select .= " WHERE true ";
                $q = $request->getParam('q');
                // $arr = explode(',', $q);
                if (!empty($q)) {
                    $arr = explode(' ', $q);
                    $filters = array();

                    foreach ($arr as $term) {
                        $term = trim($term);
                        if ($term !== '') {
                            $filters[] = "(Gen_Producto.Producto LIKE '%" . $term . "%'
                                OR Gen_Producto.CodigoBarra LIKE '%" . $term . "%'
                                OR Gen_Producto.ProductoModelo LIKE '%" . $term . "%'
                                OR Gen_Producto.CodeBarModelo LIKE '%" . $term . "%'
                                OR Gen_Producto.Genero LIKE '%" . $term . "%'
                                OR Gen_Producto.Color LIKE '%" . $term . "%'
                                OR Gen_ProductoTalla.ProductoTalla LIKE '%" . $term . "%'
                                OR Gen_ProductoCategoria.ProductoCategoria LIKE '%" . $term . "%'
                                OR Gen_ProductoMarca.ProductoMarca LIKE '%" . $term . "%'
                                OR Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica LIKE '%" . $term . "%')";
                        }
                    }

                    if (!empty($filters)) {
                        $select .= " AND (" . implode(' AND ', $filters) . ")";
                    }
                }
                // $str = '(';
                // foreach ($arr as $key => $ar) {
                //     $ar = trim($ar);
                //     $str .= "(Gen_Producto.Producto LIKE '%" . $ar .
                //         "%' OR Gen_Producto.CodigoBarra LIKE '%" . $ar .
                //         "%' OR Gen_ProductoMarca.ProductoMarca LIKE '%" . $ar . "%')";
                //     $newKeys = array_keys($arr);
                //     if (end($newKeys) != $key) {
                //         $str .= " AND ";
                //     }
                // }
                // $str .= ')';

                // $select .= " WHERE " . $str . " AND Gen_Producto.Anulado=0  ";
                if ($request->getParam('limit')) {
                    $limit = max(1, min(100, (int) $request->getParam('limit')));
                    $select .= " LIMIT " . $limit;
                }
            }

            $stmt = $this->db->query($select);
            $stmt->execute();
            $data = $stmt->fetchAll();
            foreach ($data as $row) {
                array_push($ids, $row['IdProducto']);
            }
        }

        $idAlmacen = $request->getParam('idAlmacen');
        $fechaHasta = $request->getParam('fechaHasta') ? $request->getParam('fechaHasta') : getNow();

        if (isset($args['idAlmacen'])) {
            $idAlmacen = $args['idAlmacen'];
        }

        if (count($ids) > 0) {
            // $strIngresoUnd = stringIngresoUndPunto($ids, $idAlmacen, $fechaHasta);
            // $strSalidaUnd = stringSalidaUndPunto($ids, $idAlmacen, $fechaHasta);

            // $strIngresoCaja = stringIngresoCajaPunto($ids, $idAlmacen, $fechaHasta);
            // $strSalidaCaja = stringSalidaCajaPunto($ids, $idAlmacen, $fechaHasta);

            // $strSalidaVentaUnd = stringSalidaVentaUndPunto($ids, $idAlmacen, $fechaHasta);
            // $strSalidaVentaCaja = stringSalidaVentaCajaPunto($ids, $idAlmacen, $fechaHasta);

            $select = "SELECT 
            Gen_Producto.*, 
            Gen_ProductoCategoria.ProductoCategoria, Gen_ProductoMarca.ProductoMarca,
            Gen_Producto.ProductoModelo,
            Gen_Producto.Color,
            Gen_ProductoMedicion.ProductoMedicion, Gen_Producto.Botapie,
            Gen_ProductoTalla.ProductoTalla,
            -- round(IFNULL(IngresoUnd.cantidad, 0),2) AS StockIngresoUnd,
            -- round(IFNULL(SalidaUnd.cantidad, 0),2) AS StockSalidaUnd,
            -- round(IFNULL(SalidaVentaUnd.cantidad, 0),2) AS StockSalidaVentaUnd,
            -- round(IFNULL(SalidaVentaCaja.cantidad, 0),2) AS StockSalidaVentaCaja,
            -- (
            --     round(IFNULL(IngresoUnd.cantidad, 0),2) +
            --     round(IFNULL(IngresoCaja.cantidad, 0),2)  -
            --     round(IFNULL(SalidaCaja.cantidad, 0),2) - 
            --     round(IFNULL(SalidaUnd.cantidad, 0),2) - 
            --     round(IFNULL(SalidaVentaUnd.cantidad,0) ,2)- 
            --     round(IFNULL(SalidaVentaCaja.cantidad,0) ,2)  
            -- ) AS stock2,
            COALESCE(Gen_Producto_Totales.TotalCompras,0) - COALESCE(Gen_Producto_Totales.TotalVentas,0) AS stock 
            FROM Gen_Producto
            INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
            INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
            LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla 
            LEFT JOIN Gen_Producto_Totales ON Gen_Producto.IdProducto = Gen_Producto_Totales.IdProducto 
            AND Gen_Producto_Totales.IdAlmacen = $idAlmacen ";

            // $select .= " LEFT JOIN $strIngresoUnd AS IngresoUnd ON Gen_Producto.IdProducto = IngresoUnd.IdProducto
            // LEFT JOIN $strSalidaUnd AS SalidaUnd ON Gen_Producto.IdProducto = SalidaUnd.IdProducto
            // LEFT JOIN $strIngresoCaja AS IngresoCaja ON Gen_Producto.IdProducto = IngresoCaja.IdProducto
            // LEFT JOIN $strSalidaCaja AS SalidaCaja ON Gen_Producto.IdProducto = SalidaCaja.IdProducto
            // LEFT JOIN $strSalidaVentaUnd AS SalidaVentaUnd ON Gen_Producto.IdProducto = SalidaVentaUnd.IdProducto 
            // LEFT JOIN $strSalidaVentaCaja AS SalidaVentaCaja ON Gen_Producto.IdProducto = SalidaVentaCaja.IdProducto";

            $select .= " WHERE Gen_Producto.IdProducto IN (" . implode(',', $ids) . ") ";

            $select .= " AND Gen_Producto.Anulado=0 ";
            // El almacen ya se filtra en el LEFT JOIN para incluir productos con stock 0.

            // solo productos que controlan stock
            if ($request->getParam('controlaStock')) {
                $select .= " AND Gen_Producto.ControlaStock=1";
            }


            if ($request->getParam('stock')) {
                $select .= " HAVING stock > 0";
            }

            if ($request->getParam('sortBy')) {
                $sortBy = $request->getParam('sortBy');
                $sortDesc = $request->getParam('sortDesc');
                $orientation = $sortDesc ? 'DESC' : 'ASC';
                $select .= " ORDER BY " . $sortBy . " " . $orientation;
            }

            $limit = $request->getParam('limit') ? $request->getParam('limit') :  20;
            if ($limit && !$request->getParam('noLimit')) {
                //$limit = $request->getParam('limit');
                $offset = 0;
                if ($request->getParam('page')) {
                    $page = $request->getParam('page');
                    $offset = (--$page) * $limit;
                }
                $select .= " LIMIT " . $limit;
                $select .= " OFFSET " . $offset;
            }

            $this->db->query('SET SQL_BIG_SELECTS=1');
            $stmt = $this->db->query($select);
            $stmt->execute();
            $data = $stmt->fetchAll();

            return $response->withJson($data);
        } else {
            return $response->withJson(false);
        }
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});

$app->get('/reporte/movimientos/dashboard', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $fechaInicio = $request->getParam('fechaInicio');
        $fechaFin = $request->getParam('fechaFin');
        $idAlmacen = $request->getParam('almacen'); 

        $filtroAlmacen = "";
        if ($idAlmacen != 0 && $idAlmacen != null) {
            $filtroAlmacen = " AND ($idAlmacen IN (Lo_Movimiento.IdAlmacenOrigen)) ";
        }

        $sql = "SELECT 
                CONCAT(SUBSTRING(Lo_Proveedor.Proveedor,1,20),'...') as Proveedor,
                Gen_Producto.Producto,
                SUM(Lo_MovimientoDetalle.Cantidad) Cantidad
                FROM
                Lo_Movimiento
                JOIN Lo_Proveedor ON Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
                JOIN Lo_MovimientoDetalle ON Lo_Movimiento.Hash = Lo_MovimientoDetalle.hashMovimiento
                JOIN Gen_Producto ON Lo_MovimientoDetalle.IdProducto = Gen_Producto.IdProducto
                WHERE Lo_Movimiento.FechaReg BETWEEN '$fechaInicio' AND '$fechaFin'  $filtroAlmacen
                GROUP BY Lo_Proveedor.Proveedor,Gen_Producto.Producto
                ORDER BY SUM(Lo_MovimientoDetalle.Cantidad) DESC
                LIMIT 15";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/venta/proforma/generar_pdf', function (Request $request, Response $response) use ($app) {
    $id = $request->getParam('id');
    try {

        $res = $app->subRequest('GET', '/proformas/id/' . $id);
        $data = (string) $res->getBody();
        $data = json_decode($data, true);


        // return $response->withJson(['data',$data]);


        $datosImprimir = array(
            "txtNRO_DOCUMENTO_EMPRESA" => NRO_DOCUMENTO_EMPRESA,
            "txtTIPO_DOCUMENTO_EMPRESA" => TIPO_DOCUMENTO_EMPRESA,
            "txtNOMBRE_COMERCIAL_EMPRESA" => NOMBRE_COMERCIAL_EMPRESA,
            "txtCODIGO_UBIGEO_EMPRESA" => CODIGO_UBIGEO_EMPRESA,
            "txtDIRECCION_EMPRESA" => DIRECCION_EMPRESA,
            "txtDEPARTAMENTO_EMPRESA" => DEPARTAMENTO_EMPRESA,
            "txtPROVINCIA_EMPRESA" => PROVINCIA_EMPRESA,
            "txtDISTRITO_EMPRESA" => DISTRITO_EMPRESA,
            "txtCODIGO_PAIS_EMPRESA" => CODIGO_PAIS_EMPRESA,
            "txtRAZON_SOCIAL_EMPRESA" => RAZON_SOCIAL_EMPRESA,
            "txtTELEFONOS_EMPRESA" => TELEFONOS_EMPRESA,
            "txtUSUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
            "txtPASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
            "txtPAS_FIRMA" => PAS_FIRMA,
            "TIPO_PROCESO" => TIPO_PROCESO,
            "Anio" => $data['Anio'],
            "Numero" => $data['Numero'],
            'datos' => $data
        );
        // print_r( $data);
        // return $response->withJson(['url' => $datosImprimir]);
        $protocoloProforma = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $rutapdf = $protocoloProforma . $_SERVER['HTTP_HOST'] . '/api/sunat/plugins/dompdf/';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $rutapdf . "proforma.php?",
            CURLOPT_USERAGENT => "Codular Sample cURL Request",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($datosImprimir)
        ));
        $resp = curl_exec($curl);
        curl_close($curl);
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        // return $response->withJson(['data' =>  $data]);
        $nombreArchivo = 'COTIZACION_' . $data['Anio'] . '_' .  $data['Numero'];
        $ruta = $protocoloProforma . $_SERVER['HTTP_HOST'] . "/api/sunat/api_cpe/" . 'PRODUCCION' . "/" . NRO_DOCUMENTO_EMPRESA . "/" . $nombreArchivo . '.pdf';
        return $response->withJson(['url' => $ruta]);
    } catch (Exception $e) {
        return $response->withJson(['error', $e->getMessage()]);
    }
});

$app->get('/ventas-app/modulos', function (Request $request, Response $response) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['user'])) return $response->withJson(array('error'=>'Sesión no válida.'),401);
    return $response->withJson(array('modules'=>neuroVentasAppModulos($this->db),'canEdit'=>neuroMailAdmin($this->db)));
});

$app->post('/ventas-app/modulos', function (Request $request, Response $response) {
    if (!neuroMailAdmin($this->db)) return $response->withJson(array('error'=>'Solo el superadministrador puede modificar los módulos.'),403);
    $allowed = array('sale','products','history','cash','movements','purchases','stock','dashboard');
    $received = (array) $request->getParam('modules');
    foreach ($allowed as $key) {
        $value = !empty($received[$key]) ? '1' : '0';
        $metaKey = 'ventas_app_modulo_' . $key;
        $find = $this->db->prepare('SELECT IdEmpresaConfig FROM Gen_EmpresaConfig WHERE IdEmpresa=1 AND MetaKey=? LIMIT 1');
        $find->execute(array($metaKey)); $row = $find->fetch();
        if ($row) { $save=$this->db->prepare('UPDATE Gen_EmpresaConfig SET MetaValue=? WHERE IdEmpresaConfig=?'); $save->execute(array($value,$row['IdEmpresaConfig'])); }
        else { $save=$this->db->prepare('INSERT INTO Gen_EmpresaConfig(IdEmpresa,MetaKey,MetaValue) VALUES(1,?,?)'); $save->execute(array($metaKey,$value)); }
    }
    return $response->withJson(array('ok'=>true,'modules'=>neuroVentasAppModulos($this->db)));
});

$app->get('/correo/configuracion',function(Request $request,Response $response){if(!neuroMailAdmin($this->db))return $response->withJson(['error'=>'Acceso solo para administradores.'],403);$c=neuroMailConfig($this->db);unset($c['smtp_password']);$c['password_configured']=!empty(neuroMailConfig($this->db)['smtp_password']);return $response->withJson($c);});

$app->post('/correo/configuracion',function(Request $request,Response $response){if(!neuroMailAdmin($this->db))return $response->withJson(['error'=>'Acceso solo para administradores.'],403);$allowed=['smtp_host','smtp_port','smtp_security','smtp_user','smtp_from_email','smtp_from_name'];$data=[];foreach($allowed as $key)$data[$key]=trim((string)$request->getParam($key));$password=(string)$request->getParam('smtp_password');if($password!==''){$iv=random_bytes(16);$key=hash('sha256',NRO_DOCUMENTO_EMPRESA.'|neuro-mail-v1',true);$data['smtp_password']=base64_encode($iv.openssl_encrypt($password,'AES-256-CBC',$key,OPENSSL_RAW_DATA,$iv));}neuroMailSaveConfig($this->db,$data);return $response->withJson(['ok'=>true]);});

$app->post('/correo/probar',function(Request $request,Response $response){if(!neuroMailAdmin($this->db))return $response->withJson(['error'=>'Acceso solo para administradores.'],403);$to=trim((string)$request->getParam('email'));if(!filter_var($to,FILTER_VALIDATE_EMAIL))return $response->withJson(['error'=>'Correo de prueba inválido.'],422);try{neuroSmtpSend(neuroMailConfig($this->db),$to,'Prueba de correo Neuro App','<h2>Configuración correcta</h2><p>Neuro App ya puede enviar comprobantes.</p>');return $response->withJson(['ok'=>true]);}catch(Exception $e){return $response->withJson(['error'=>$e->getMessage()],500);}});

$app->post('/correo/comprobante',function(Request $request,Response $response)use($app){if(session_status()!==PHP_SESSION_ACTIVE)session_start();if(empty($_SESSION['user']))return $response->withJson(['error'=>'Sesión no válida.'],401);$to=trim((string)$request->getParam('email'));$id=intval($request->getParam('idDocVenta'));if(!$id||!filter_var($to,FILTER_VALIDATE_EMAIL))return $response->withJson(['error'=>'Datos de envío inválidos.'],422);try{$pdfResponse=$app->subRequest('GET','/imprimirpdf/'.$id);$pdf=(string)$pdfResponse->getBody();if(strlen($pdf)<100)throw new Exception('No se pudo generar el PDF.');neuroSmtpSend(neuroMailConfig($this->db),$to,'Comprobante de compra #'.$id,'<h2>Gracias por su compra</h2><p>Adjuntamos su comprobante electrónico.</p>',$pdf,'comprobante-'.$id.'.pdf');return $response->withJson(['ok'=>true]);}catch(Exception $e){return $response->withJson(['error'=>$e->getMessage()],500);}});

$app->post('/cierrecaja/resumen-pdf',function(Request $request,Response $response){
    if(session_status()!==PHP_SESSION_ACTIVE)session_start();
    if(empty($_SESSION['user']))return $response->withJson(['error'=>'Sesión no válida.'],401);
    $html=(string)$request->getParam('html');
    if(strlen($html)<100||strlen($html)>250000)return $response->withJson(['error'=>'El contenido del resumen no es válido.'],422);
    $previousErrorLevel=error_reporting();
    error_reporting($previousErrorLevel & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
    try{
        require_once __DIR__.'/sunat/plugins/dompdf/src/Autoloader.php';
        \Dompdf\Autoloader::register();
        $options=new \Dompdf\Options();
        $options->set('isRemoteEnabled',false);
        $dompdf=new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html,'UTF-8');
        $dompdf->setPaper([0,0,226.77,992.13]);
        $dompdf->render();
        $directory=__DIR__.'/cierres_pdf';
        if(!is_dir($directory)&&!mkdir($directory,0775,true))throw new Exception('No se pudo crear la carpeta de resúmenes.');
        $safeUser=preg_replace('/[^a-z0-9_-]+/i','-',(string)$_SESSION['user']);
        $filename='cierre-caja-'.$safeUser.'-'.date('Ymd-His').'-'.bin2hex(random_bytes(3)).'.pdf';
        if(file_put_contents($directory.'/'.$filename,$dompdf->output())===false)throw new Exception('No se pudo guardar el PDF.');
        error_reporting($previousErrorLevel);
        return $response->withJson(['ok'=>true,'filename'=>$filename,'url'=>'../api/cierres_pdf/'.$filename]);
    }catch(Throwable $e){error_reporting($previousErrorLevel);return $response->withJson(['error'=>'No se pudo generar el PDF del cierre: '.$e->getMessage()],500);}
});

$app->post('/correo/cierre-caja',function(Request $request,Response $response){
    if(session_status()!==PHP_SESSION_ACTIVE)session_start();
    if(empty($_SESSION['user']))return $response->withJson(['error'=>'Sesión no válida.'],401);
    $to=trim((string)$request->getParam('email'));
    $filename=basename((string)$request->getParam('filename'));
    if(!filter_var($to,FILTER_VALIDATE_EMAIL)||!preg_match('/^cierre-caja-[a-z0-9_-]+-\d{8}-\d{6}-[a-f0-9]{6}\.pdf$/i',$filename))return $response->withJson(['error'=>'Datos de envío inválidos.'],422);
    $path=__DIR__.'/cierres_pdf/'.$filename;
    if(!is_file($path))return $response->withJson(['error'=>'El PDF del cierre ya no está disponible. Genérelo nuevamente.'],404);
    try{
        neuroSmtpSend(neuroMailConfig($this->db),$to,'Resumen de cierre de caja','<h2>Resumen de cierre de caja</h2><p>Adjuntamos el ticket PDF generado por Neuro App.</p>',file_get_contents($path),$filename);
        return $response->withJson(['ok'=>true]);
    }catch(Exception $e){return $response->withJson(['error'=>$e->getMessage()],500);}
});
require_once __DIR__.'/kpi.php';
