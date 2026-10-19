<?php
/**
 * Registro de errores de arranque del API.
 *
 * OpenLiteSpeed puede devolver un 500 vacio cuando PHP falla antes de que Slim
 * instale su manejador. Este bloque conserva el detalle solamente en el log del
 * servidor y entrega al cliente un identificador seguro para poder rastrearlo.
 */
$apiSiteHome = dirname(dirname(__DIR__));
$apiSiteName = basename($apiSiteHome);
$apiLogDirectory = $apiSiteHome . '/logs';
$apiErrorLog = $apiLogDirectory . '/' . $apiSiteName . '.error_log';

ini_set('display_errors', '0');
ini_set('log_errors', '1');
if (is_dir($apiLogDirectory) && is_writable($apiLogDirectory)) {
    ini_set('error_log', $apiErrorLog);
}
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

register_shutdown_function(function () {
    $error = error_get_last();
    $fatalTypes = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);
    if (!$error || !in_array($error['type'], $fatalTypes, true)) return;

    $errorId = substr(sha1(uniqid('api-fatal-', true)), 0, 12);
    error_log(sprintf(
        '[API-FATAL:%s] %s in %s:%d',
        $errorId,
        isset($error['message']) ? $error['message'] : 'Error fatal desconocido',
        isset($error['file']) ? $error['file'] : 'archivo desconocido',
        isset($error['line']) ? $error['line'] : 0
    ));

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode(array(
        'error' => 'Error interno del API.',
        'error_id' => $errorId
    ));
});

// quitar esto cuando este mejorada la validacion
// include_once('../views/validateUser.php');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require 'sunat/sunat.php';
require_once dirname(__DIR__) . '/config/database.php';
// EXCEL 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$config['debug'] = true;
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db'] = appDatabaseConfig();


// $config['db_destino']['host']   = getenv('DB_HOST_DESTINO');
// $config['db_destino']['user']   = getenv('DB_USERNAME_DESTINO');
// $config['db_destino']['pass']   = getenv('DB_PASSWORD_DESTINO');
// $config['db_destino']['dbname'] = getenv('DB_NAME_DESTINO');

$config['db_destino'] = appDatabaseConfig('DB_DESTINO_');
if ($config['db_destino']['dbname'] === '') $config['db_destino'] = $config['db'];

$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $usr = $db['user'];
    $pwd = $db['pass'];

    $pdo = new \Slim\PDO\Database(appDatabaseDsn($db), $usr, $pwd);

    return $pdo;
};


$container['db_destino'] = function ($c) {
    $db = $c['settings']['db_destino'];
    $usr = $db['user'];
    $pwd = $db['pass'];

    $pdo = new \Slim\PDO\Database(appDatabaseDsn($db), $usr, $pwd);

    return $pdo;
};



$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});


function getNow($format = "Y-m-d H:i:s")
{
    return date_create('now', timezone_open('America/Lima'))->format($format);
}

/**
 * Identidad corporativa centralizada para reportes Excel.
 * La fuente es la misma pantalla "Datos de empresa" del sistema.
 */
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

$app->get('/categorias', function (Request $request, Response $response, array $args) {
    $q = $request->getParam('q');
    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;

    $select = "SELECT * FROM Gen_ProductoCategoria";
    $select .= " WHERE ProductoCategoria LIKE '" . $q . "%' ";

    if ($limit) {
        // $limit = $request->getParam('limit');
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->post('/categorias', function (Request $request, Response $response, array $args) {

    $productoCategoria = $request->getParam('ProductoCategoria');
    $anulado = 0;

    $insert = $this->db->insert(array('ProductoCategoria', 'Anulado', 'FechaReg'))
        ->into('Gen_ProductoCategoria')
        ->values(array($productoCategoria, $anulado, getNow()));
    $insertId = $insert->execute();

    return $response->withJson(array("insertId" => $insertId, "ProductoCategoria" => $productoCategoria));
});


$app->get('/marcas', function (Request $request, Response $response, array $args) {
    $q = $request->getParam('q');
    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;

    $select = "SELECT * FROM Gen_ProductoMarca";
    $select .= " WHERE ProductoMarca LIKE '" . $q . "%' ";

    if ($limit) {
        // $limit = $request->getParam('limit');
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});
$app->get('/imprimirpdf/{id}', function (Request $request, Response $response, array $args) use ($app) {
    $id = (int) $args['id'];
    $select = "SELECT Ve_DocVenta.idDocVenta, Ve_DocVenta.EsCredito, Ve_DocVenta.FechaCredito, Ve_DocVenta.FechaDoc, Ve_DocVentaTipoDoc.TipoDoc, Ve_DocVentaTipoDoc.TieneIgv,
        Ve_DocVenta.Anulado, Ve_DocVenta.Serie, Ve_DocVenta.Numero,
        CASE WHEN Ve_DocVenta.EsOrganizacion=1 AND TRIM(IFNULL(Ve_DocVenta.NombreOrganizacion,''))<>'' THEN Ve_DocVenta.NombreOrganizacion ELSE Ve_DocVentaCliente.Cliente END AS Cliente,
        Ve_DocVenta.UsuarioReg,
        Ve_DocVentaTipoDoc.CodSunat, Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.DniRuc,Ve_DocVenta.CampoDireccion as Direccion,
        Ve_DocVentaTipoDoc.CodigoIgv, Ve_DocVenta.Estado, Ve_DocVenta.Hash_cpe, Ve_DocVenta.Hash_cdr, Ve_DocVenta.Msj_sunat,
        Ve_DocVenta.FechaCredito, Lo_Almacen.Almacen, Lo_Almacen.Direccion DireccionAlmacen, Lo_Almacen.EsPrincipal EsAlmacenPrincipal,
        Ve_DocVentaCliente.NombreComercial,Ve_DocVenta.CodSunatModifica,
        Ve_DocVenta.NroComprobanteModifica,
        Ve_DocVenta.NotaIdMotivo,
        Ve_DocVenta.NotaDescMotivo,
        Ve_DocVenta.Observacion, Ve_DocVenta.Placa,
        IFNULL((SELECT SUM(ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2)) FROM Ve_DocVentaDet WHERE Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta), 0 ) AS Total
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        LEFT JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente 
        INNER JOIN Lo_Almacen ON Ve_DocVenta.IdAlmacen = Lo_Almacen.IdAlmacen
        WHERE idDocVenta = $id ";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $venta = $stmt->fetch();
    if (!$venta) {
        return $response->withJson(array('error' => 'Comprobante no encontrado'), 404);
    }
    $select = "SELECT Ve_DocVentaDet.*, Gen_Producto.Producto, IFNULL(Ve_DocVentaDet.Descripcion,'') as Descripcion,
        ROUND(Ve_DocVentaDet.Descuento, 2) AS Descuento,
        ROUND((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - Ve_DocVentaDet.Descuento, 2) AS Subtotal,
        ROUND(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio, 2) AS Total,
        Gen_Producto.CodigoBarra, Gen_ProductoMedicion.Codigo AS CodigoMedicion,
        Gen_ProductoMedicion.ProductoMedicion, Gen_ProductoMarca.ProductoMarca,
        Gen_Producto.ProductoModelo, Gen_Producto.Color, Gen_Producto.Genero,
        Gen_Producto.Botapie, Gen_ProductoTalla.ProductoTalla
        FROM Ve_DocVentaDet
        INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
        INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
        INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
        LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla
        WHERE IdDocVenta = $id";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $productos = $stmt->fetchAll();
    $venta['productos'] = $productos;
    // Generar el PDF como archivo
    $res = $app->subRequest('POST', '/generarpdfelectronico', http_build_query(['docVenta' => $venta]));
    $generatedContent = (string) $res->getBody();

    // Buscar el pdf y mostrarlo
    $nombrePdf = NRO_DOCUMENTO_EMPRESA . '-' . $venta['CodSunat'] . '-' . $venta['Serie'] . '-' . $venta['Numero'] . '.pdf';
    $response = $response->withHeader('Content-Type', 'application/pdf');
    if ($request->getParam('descargar')) {
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="' . $nombrePdf . '"');
    }
    $carpetaProceso = TIPO_PROCESO === '03' ? 'BETA' : 'PRODUCCION';
    $rutaPdf = dirname(__FILE__) . '/sunat/api_cpe/' . $carpetaProceso . '/' . NRO_DOCUMENTO_EMPRESA . '/' . $nombrePdf;
    $rutaAlterna = dirname(__FILE__) . '/sunat/api_cpe/' . ($carpetaProceso === 'BETA' ? 'PRODUCCION' : 'BETA') . '/' . NRO_DOCUMENTO_EMPRESA . '/' . $nombrePdf;

    if ((!is_file($rutaPdf) || filesize($rutaPdf) < 5) && is_file($rutaAlterna) && filesize($rutaAlterna) >= 5) {
        $rutaPdf = $rutaAlterna;
    }

    $content = substr($generatedContent, 0, 4) === '%PDF' ? $generatedContent : '';
    if (substr($content, 0, 4) !== '%PDF' && is_file($rutaPdf) && filesize($rutaPdf) >= 5) {
        $content = (string) file_get_contents($rutaPdf);
    }

    // Algunos servidores no permiten que el generador A4 escriba dentro de api_cpe.
    // El ticket se genera completamente en memoria, por lo que mantiene disponibles
    // PDF, WhatsApp y correo aunque falle la escritura del archivo electronico.
    if (substr($content, 0, 4) !== '%PDF') {
        $ticketResponse = $app->subRequest('GET', '/imprimirticketpdf/' . $id);
        $ticketContent = (string) $ticketResponse->getBody();
        if (substr($ticketContent, 0, 4) === '%PDF') {
            $content = $ticketContent;
        }
    }

    if (substr($content, 0, 4) !== '%PDF') {
        return $response->withJson(array('error' => 'No se pudo generar el PDF del comprobante'), 500);
    }

    $response = $response->withHeader('Content-Length', (string) strlen($content));
    return $response->write($content);
});

$app->get('/imprimirticketpdf/{id}', function (Request $request, Response $response, array $args) {
    $id = (int) $args['id'];
    if ($id <= 0) return $response->withJson(array('error' => 'Comprobante no valido'), 422);

    $stmt = $this->db->prepare("SELECT v.*, t.TipoDoc, t.TieneIgv, t.CodSunat, t.CodigoIgv,
        CASE WHEN v.EsOrganizacion=1 AND TRIM(IFNULL(v.NombreOrganizacion,''))<>'' THEN v.NombreOrganizacion ELSE c.Cliente END AS Cliente,
        c.DniRuc, c.NombreComercial, v.CampoDireccion AS Direccion,
        a.Almacen, a.Direccion AS DireccionAlmacen, a.EsPrincipal AS EsAlmacenPrincipal
        FROM Ve_DocVenta v
        INNER JOIN Ve_DocVentaTipoDoc t ON v.IdTipoDoc=t.IdTipoDoc
        LEFT JOIN Ve_DocVentaCliente c ON v.IdCliente=c.IdCliente
        INNER JOIN Lo_Almacen a ON v.IdAlmacen=a.IdAlmacen
        WHERE v.idDocVenta=:id");
    $stmt->execute(array(':id' => $id));
    $docVenta = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$docVenta) return $response->withJson(array('error' => 'Comprobante no encontrado'), 404);

    $stmt = $this->db->prepare("SELECT d.*, p.Producto, IFNULL(d.Descripcion,'') AS Descripcion,
        ROUND(d.Descuento,2) AS Descuento, ROUND(d.Cantidad*d.Precio,2) AS TOTAL,
        m.Codigo AS CodigoMedicion, m.ProductoMedicion, ma.ProductoMarca,
        p.ProductoModelo, p.Color, p.Genero, p.Botapie, ta.ProductoTalla
        FROM Ve_DocVentaDet d
        INNER JOIN Gen_Producto p ON d.IdProducto=p.IdProducto
        INNER JOIN Gen_ProductoMedicion m ON p.IdProductoMedicion=m.IdProductoMedicion
        INNER JOIN Gen_ProductoMarca ma ON p.IdProductoMarca=ma.IdProductoMarca
        LEFT JOIN Gen_ProductoTalla ta ON p.IdProductoTalla=ta.IdProductoTalla
        WHERE d.IdDocVenta=:id ORDER BY d.IdDocVentaDet ASC");
    $stmt->execute(array(':id' => $id));
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $this->db->prepare("SELECT mp.MetodoPago,pd.Importe,pd.NroTarjeta
        FROM Ve_DocVentaMetodoPagoDet pd
        INNER JOIN Ve_DocVentaMetodoPago mp ON mp.IdMetodoPago=pd.IdMetodoPago
        WHERE pd.IdDocVenta=:id ORDER BY pd.IdMetodoPago ASC");
    $stmt->execute(array(':id' => $id));
    $metodosPago = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $codigoComprobanteSunat = str_pad((string) $docVenta['CodSunat'], 2, '0', STR_PAD_LEFT);
    $nombres = array('01'=>'FACTURA ELECTRONICA','03'=>'BOLETA DE VENTA ELECTRONICA','07'=>'NOTA DE CREDITO ELECTRONICA','08'=>'NOTA DE DEBITO ELECTRONICA','12'=>'TICKET DE VENTA');
    $tipoComprobanteImpreso = isset($nombres[$codigoComprobanteSunat]) ? $nombres[$codigoComprobanteSunat] : strtoupper((string) $docVenta['TipoDoc']);

    require_once __DIR__ . '/sunat/plugins/phpqrcode/qrlib.php';
    ob_start();
    include __DIR__ . '/templates/ticket_pdf.php';
    $html = ob_get_clean();
    $html = preg_replace_callback('/src="\/(resources\/[^"?]+)"/i', function ($match) {
        $path = dirname(__DIR__) . '/' . str_replace('/', DIRECTORY_SEPARATOR, $match[1]);
        if (!is_file($path)) return $match[0];
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = in_array($ext, array('jpg','jpeg'), true) ? 'image/jpeg' : ($ext === 'svg' ? 'image/svg+xml' : 'image/png');
        return 'src="data:' . $mime . ';base64,' . base64_encode(file_get_contents($path)) . '"';
    }, $html);

    require_once __DIR__ . '/sunat/plugins/dompdf/src/Autoloader.php';
    \Dompdf\Autoloader::register();
    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $altoMm = min(900, max(240, 205 + count($productos) * 10 + count($metodosPago) * 6));
    $dompdf->setPaper(array(0, 0, 226.77, $altoMm * 2.83465));
    $dompdf->render();
    $content = $dompdf->output();
    if (substr($content, 0, 4) !== '%PDF') return $response->withJson(array('error' => 'No se pudo generar el PDF del ticket'), 500);

    $filename = 'ticket-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $docVenta['Serie'].'-'.$docVenta['Numero']) . '.pdf';
    $disposition = $request->getParam('descargar') ? 'attachment' : 'inline';
    return $response->withHeader('Content-Type', 'application/pdf')
        ->withHeader('Content-Disposition', $disposition . '; filename="' . $filename . '"')
        ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->withHeader('Pragma', 'no-cache')
        ->withHeader('Content-Length', (string) strlen($content))->write($content);
});

$app->get('/descargarxml/{id}', function (Request $request, Response $response, array $args) use ($app) {
    $id = (int) $args['id'];
    $select = "SELECT Ve_DocVenta.Serie, Ve_DocVenta.Numero, Ve_DocVenta.Estado, Ve_DocVenta.Hash_cpe, Ve_DocVenta.Msj_sunat, Ve_DocVentaTipoDoc.CodSunat
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        WHERE Ve_DocVenta.idDocVenta = :id";
    $stmt = $this->db->prepare($select);
    $stmt->execute(array(':id' => $id));
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        return $response->withJson(array('error' => 'Comprobante no encontrado'), 404);
    }

    $nombreXml = NRO_DOCUMENTO_EMPRESA . '-' . $venta['CodSunat'] . '-' . $venta['Serie'] . '-' . $venta['Numero'] . '.XML';
    $carpetaProceso = TIPO_PROCESO === '03' ? 'BETA' : 'PRODUCCION';
    $baseCpe = dirname(__FILE__) . '/sunat/api_cpe/';
    $entornos = array_unique(array($carpetaProceso, 'PRODUCCION', 'BETA', 'HOMOLOGACION'));
    $rutaXml = '';
    foreach ($entornos as $entorno) {
        $carpeta = $baseCpe . $entorno . '/' . NRO_DOCUMENTO_EMPRESA . '/';
        foreach (array($nombreXml, strtolower($nombreXml)) as $candidato) {
            if (is_file($carpeta . $candidato) && filesize($carpeta . $candidato) > 20) {
                $rutaXml = $carpeta . $candidato;
                break 2;
            }
        }
    }

    /* Compatibilidad con comprobantes históricos emitidos antes de cambiar el RUC configurado. */
    if ($rutaXml === '') {
        $sufijoHistorico = '-' . $venta['CodSunat'] . '-' . $venta['Serie'] . '-' . $venta['Numero'];
        foreach ($entornos as $entorno) {
            $patrones = array(
                $baseCpe . $entorno . '/*/*' . $sufijoHistorico . '.XML',
                $baseCpe . $entorno . '/*/*' . $sufijoHistorico . '.xml'
            );
            foreach ($patrones as $patron) {
                $coincidencias = glob($patron);
                if (!empty($coincidencias)) {
                    foreach ($coincidencias as $coincidencia) {
                        if (is_file($coincidencia) && filesize($coincidencia) > 20) {
                            $rutaXml = $coincidencia;
                            $nombreXml = basename($coincidencia);
                            break 3;
                        }
                    }
                }
            }
        }
    }

    if ($rutaXml === '' || !is_file($rutaXml)) {
        $selectDoc = "SELECT Ve_DocVenta.*, Ve_DocVentaTipoDoc.TipoDoc, Ve_DocVentaTipoDoc.TieneIgv,
            Ve_DocVentaTipoDoc.CodSunat, Ve_DocVentaTipoDoc.CodigoIgv,
            Ve_DocVentaCliente.Cliente, Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.NombreComercial,
            Ve_DocVenta.CampoDireccion AS Direccion, Lo_Almacen.Almacen,
            Lo_Almacen.Direccion AS DireccionAlmacen, Lo_Almacen.EsPrincipal AS EsAlmacenPrincipal,
            IFNULL((SELECT SUM(ROUND((d.Precio * d.Cantidad) - d.Descuento, 2))
                FROM Ve_DocVentaDet d WHERE d.IdDocVenta = Ve_DocVenta.idDocVenta), 0) AS Total
            FROM Ve_DocVenta
            INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
            LEFT JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente
            INNER JOIN Lo_Almacen ON Ve_DocVenta.IdAlmacen = Lo_Almacen.IdAlmacen
            WHERE Ve_DocVenta.idDocVenta = :id";
        $stmtDoc = $this->db->prepare($selectDoc);
        $stmtDoc->execute(array(':id' => $id));
        $docVentaXml = $stmtDoc->fetch(PDO::FETCH_ASSOC);

        $selectDet = "SELECT Ve_DocVentaDet.*, Gen_Producto.Producto,
            IFNULL(Ve_DocVentaDet.Descripcion,'') AS Descripcion,
            ROUND(Ve_DocVentaDet.Descuento, 2) AS Descuento,
            ROUND((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - Ve_DocVentaDet.Descuento, 2) AS Subtotal,
            ROUND(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio, 2) AS Total,
            Gen_Producto.CodigoBarra, Gen_ProductoMedicion.Codigo AS CodigoMedicion,
            Gen_ProductoMedicion.ProductoMedicion, Gen_ProductoMarca.ProductoMarca
            FROM Ve_DocVentaDet
            INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
            INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
            WHERE Ve_DocVentaDet.IdDocVenta = :id";
        $stmtDet = $this->db->prepare($selectDet);
        $stmtDet->execute(array(':id' => $id));
        if ($docVentaXml) $docVentaXml['productos'] = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        if ($docVentaXml && in_array($docVentaXml['CodSunat'], array('01', '03', '07', '08'), true)) {
            $app->subRequest('POST', '/emitirelectronico', http_build_query(array(
                'docVenta' => $docVentaXml,
                'generarXml' => 1
            )));
            $rutaGenerada = $baseCpe . $carpetaProceso . '/' . NRO_DOCUMENTO_EMPRESA . '/' . $nombreXml;
            if (is_file($rutaGenerada) && filesize($rutaGenerada) > 20) $rutaXml = $rutaGenerada;
        }
    }

    if ($rutaXml === '' || !is_file($rutaXml)) {
        return $response->withJson(array('error' => 'El XML del comprobante no está disponible'), 404);
    }

    $content = file_get_contents($rutaXml);
    return $response
        ->withHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->withHeader('Content-Disposition', 'attachment; filename="' . $nombreXml . '"')
        ->withHeader('Content-Length', (string) strlen($content))
        ->write($content);
});
$app->post('/marcas', function (Request $request, Response $response, array $args) {

    $productoMarca = $request->getParam('ProductoMarca');
    $anulado = 0;

    $insert = $this->db->insert(array('ProductoMarca', 'Anulado', 'FechaReg'))
        ->into('Gen_ProductoMarca')
        ->values(array($productoMarca, $anulado, getNow()));
    $insertId = $insert->execute();

    return $response->withJson(array("insertId" => $insertId, "ProductoMarca" => $productoMarca));
});


$app->get('/mediciones', function (Request $request, Response $response, array $args) {
    $select = $this->db->select()->from('Gen_ProductoMedicion');
    $stmt = $select->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


/* $app->get('/modelos', function (Request $request, Response $response, array $args) {
    $select = $this->db->select()->from('Gen_ProductoModelo')
                ->whereLike('ProductoModelo','%' . $request->getParam('q') . '%');
    $stmt = $select->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
}); */

/* $app->post('/modelos', function (Request $request, Response $response, array $args) {
    
    $productoModelo = $request->getParam('ProductoModelo');
    $anulado = 0;

    $insert = $this->db->insert(array('ProductoModelo', 'Anulado', 'FechaReg'))
                       ->into('Gen_ProductoModelo')
                       ->values(array($productoModelo, $anulado, getNow()));
    $insertId = $insert->execute();
    
    return $response->withJson(array("insertId" => $insertId, "ProductoModelo" => $productoModelo));
}); */



$app->get('/tallas', function (Request $request, Response $response, array $args) {
    $q = $request->getParam('q');
    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;

    $select = "SELECT * FROM Gen_ProductoTalla";
    $select .= " WHERE ProductoTalla LIKE '" . $q . "%' ";

    if ($limit) {
        // $limit = $request->getParam('limit');
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->post('/tallas', function (Request $request, Response $response, array $args) {

    $productoTalla = $request->getParam('ProductoTalla');
    $anulado = 0;

    $insert = $this->db->insert(array('ProductoTalla', 'Anulado', 'FechaReg'))
        ->into('Gen_ProductoTalla')
        ->values(array($productoTalla, $anulado, getNow()));
    $insertId = $insert->execute();

    return $response->withJson(array("insertId" => $insertId, "ProductoTalla" => $productoTalla));
});


$app->get('/productos/id/{id}', function (Request $request, Response $response, array $args) {
    $select = "SELECT Gen_Producto.*, Gen_ProductoCategoria.ProductoCategoria, Gen_ProductoMarca.ProductoMarca,
    Gen_ProductoTalla.ProductoTalla, Gen_ProductoMedicion.ProductoMedicion,
    COALESCE(Gen_Producto.TotalCompras,0) - COALESCE(Gen_Producto.TotalVentas,0) AS stock
    FROM Gen_Producto 
    INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
    INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
    INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
    LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla ";

    if ($request->getParam('esId') == 1) {
        $select .= " WHERE Gen_Producto.IdProducto = '" . $args['id'] . "'";
    } else {
        $select .= " WHERE Gen_Producto.CodigoBarra = '" . $args['id'] . "'";
    }


    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/productos/colores', function (Request $request, Response $response, array $args) {
    $select = "SELECT DISTINCT Color FROM Gen_Producto";
    $select .= " WHERE Gen_Producto.Color LIKE '%" . $request->getParam('q') . "%' ";

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});
$app->get('/productos/generos', function (Request $request, Response $response, array $args) {
    $select = "SELECT DISTINCT Genero FROM Gen_Producto";
    $select .= " WHERE Gen_Producto.Genero LIKE '%" . $request->getParam('q') . "%' ";

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});
$app->get('/productos/botapies', function (Request $request, Response $response, array $args) {
    $select = "SELECT DISTINCT Botapie FROM Gen_Producto";
    $select .= " WHERE Gen_Producto.Botapie LIKE '%" . $request->getParam('q') . "%' ";

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});
$app->get('/productos/modelos', function (Request $request, Response $response, array $args) {
    $term = trim((string) $request->getParam('q'));
    $limit = max(1, min(50, (int) ($request->getParam('limit') ?: 15)));
    // Al abrir el buscador sin texto, siempre se muestran los primeros 15 modelos.
    if ($term === '') {
        $limit = 15;
    }
    $page = max(1, (int) ($request->getParam('page') ?: 1));
    $offset = ($page - 1) * $limit;
    $select = "SELECT DISTINCT ProductoModelo FROM Gen_Producto
               WHERE Anulado = 0 AND ProductoModelo IS NOT NULL
                 AND TRIM(ProductoModelo) <> '' AND ProductoModelo LIKE :contains
               ORDER BY CASE
                 WHEN UPPER(ProductoModelo) = UPPER(:exact) THEN 0
                 WHEN UPPER(ProductoModelo) LIKE UPPER(:starts) THEN 1
                 ELSE 2 END, ProductoModelo ASC
               LIMIT " . $limit . " OFFSET " . $offset;
    $stmt = $this->db->prepare($select);
    $stmt->bindValue(':contains', '%' . $term . '%', PDO::PARAM_STR);
    $stmt->bindValue(':exact', $term, PDO::PARAM_STR);
    $stmt->bindValue(':starts', $term . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response
        ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->withHeader('Pragma', 'no-cache')
        ->withJson($data);
});

$app->get('/productosdet', function (Request $request, Response $response, array $args) {
    $idProducto = $request->getParam('idProducto');

    $select = "SELECT Gen_Producto.Producto, Gen_ProductoDet.* FROM Gen_ProductoDet 
        INNER JOIN Gen_Producto ON Gen_ProductoDet.IdProductoDet = Gen_Producto.IdProducto 
        WHERE Gen_ProductoDet.IdProducto=$idProducto";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/productos/eshijo', function (Request $request, Response $response, array $args) {
    $idProducto = $request->getParam('idProducto');

    $select = "SELECT * FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet=$idProducto";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();
    if ($data) {
        $data = array("esHijo" => 1);
    } else {
        $data = array("esHijo" => 0);
    }
    return $response->withJson($data);
});
$app->get('/productos/espadre', function (Request $request, Response $response, array $args) {
    $idProducto = $request->getParam('idProducto');

    $select = "SELECT * FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProducto=$idProducto";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();
    if ($data) {
        $data = array("esPadre" => 1);
    } else {
        $data = array("esPadre" => 0);
    }
    return $response->withJson($data);
});

$app->get('/productos/chatarra', function (Request $request, Response $response, array $args) {
    $stmt = $this->db->prepare("SELECT
            IdProducto,
            CodigoBarra,
            Codigo,
            Producto,
            ProductoModelo,
            PrecioCosto
        FROM Gen_Producto
        WHERE Anulado = 0 AND EsChatarra = 1
        ORDER BY Producto, CodigoBarra");
    $stmt->execute();
    return $response->withJson($stmt->fetchAll(PDO::FETCH_ASSOC));
});

$app->get('/productos', function (Request $request, Response $response, array $args) {
    $select = "SELECT Gen_Producto.*,Gen_Producto.TienePresentacion, Gen_ProductoCategoria.ProductoCategoria, Gen_ProductoMarca.ProductoMarca,
        Gen_ProductoTalla.ProductoTalla, Gen_ProductoMedicion.ProductoMedicion
        FROM Gen_Producto 
        INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
        INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
        INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
        LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla ";

    if ($request->getParam('filter')) {
        $filter = $request->getParam('filter');

        if (is_array($filter)) {
            $select .= " WHERE Gen_Producto.Producto LIKE '%" . (isset($filter['producto']) ? addslashes($filter['producto']) : '') .
                "%' AND Gen_Producto.CodigoBarra LIKE '" . (isset($filter['codigo']) ? addslashes($filter['codigo']) : '') .
                "%' ANd Gen_Producto.Color LIKE '" . (isset($filter['color']) ? addslashes($filter['color']) : '') .
                "%' ANd Gen_ProductoMarca.ProductoMarca LIKE '" . (isset($filter['marca']) ? addslashes($filter['marca']) : '') .
                "%' ANd Gen_Producto.ProductoModelo LIKE '" . (isset($filter['modelo']) ? addslashes($filter['modelo']) : '') .
                "%' AND Gen_ProductoCategoria.ProductoCategoria LIKE '" . (isset($filter['categoria']) ? addslashes($filter['categoria']) : '') .
                "%' AND Gen_Producto.Genero LIKE '" . (isset($filter['genero']) ? addslashes($filter['genero']) : '') .
                "%' AND Gen_Producto.Botapie LIKE '" . (isset($filter['botapie']) ? addslashes($filter['botapie']) : '') .
                "%' AND Gen_ProductoTalla.ProductoTalla LIKE '" . (isset($filter['talla']) ? addslashes($filter['talla']) : '%') .
                "' ";
        } else {
            $select .= " WHERE Gen_Producto.Producto LIKE '%" . $filter .
                "%' OR Gen_Producto.CodigoBarra LIKE '%" . $filter .
                "%' OR Gen_Producto.Color LIKE '%" . $filter .
                "%' OR Gen_Producto.Genero LIKE '%" . $filter .
                "%' OR Gen_ProductoMarca.ProductoMarca LIKE '%" . $filter .
                "%' OR Gen_Producto.ProductoModelo LIKE '%" . $filter .
                "%' OR Gen_ProductoCategoria.ProductoCategoria LIKE '%" . $filter .
                "%' ";
        }


        /* $select .= " WHERE Gen_Producto.Producto LIKE '%" . $filter . 
                   "%' OR Gen_Producto.CodigoBarra LIKE '%" . $filter . 
                   "%' OR Gen_Producto.Color LIKE '%" . $filter . 
                   "%' OR Gen_Producto.Genero LIKE '%" . $filter . 
                   "%' OR Gen_Producto.ProductoModelo LIKE '%" . $filter . 
                   "%' OR Gen_ProductoMarca.ProductoMarca LIKE '%" . $filter . 
                   "%' OR Gen_ProductoCategoria.ProductoCategoria LIKE '%" . $filter . 
                   "%' ";        */
    } else {
        $qProducto = addslashes((string) $request->getParam('q'));
        $select .= " WHERE Gen_Producto.Anulado=0 AND (Gen_Producto.Producto LIKE '%" . $qProducto .
            "%' OR Gen_Producto.CodigoBarra LIKE '%" . $qProducto .
            "%' OR Gen_Producto.ProductoModelo LIKE '%" . $qProducto .
            "%' OR Gen_ProductoMarca.ProductoMarca LIKE '%" . $qProducto .
            "%' OR Gen_ProductoCategoria.ProductoCategoria LIKE '%" . $qProducto . "%')";
    }

    if ($request->getParam('controlaStock')) {
        $select .= " AND Gen_Producto.ControlaStock = 1";
    }

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  10;
    if ($limit && !$request->getParam('noLimit')) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/productos/count', function (Request $request, Response $response, array $args) {
    $select = "SELECT COUNT(*) as total FROM Gen_Producto";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});

$app->get('/productos/kardex/{id}', function (Request $request, Response $response, array $args) {

    $idProducto = $args['id'];
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaDesde = $request->getParam('fechaDesde') ? $request->getParam('fechaDesde') . ' 00:00:00' : 0;
    $fechaHasta = $request->getParam('fechaHasta') . ' 23:59:59';

    $strIngresos = stringIngresoUndProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde);
    $strSalidas = stringSalidaUndProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde);
    $strVentas = stringSalidaVentaUndProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde);

    $select = $strIngresos . ' UNION ALL ' . $strSalidas . ' UNION ALL ' . $strVentas;
    $select .= ' ORDER BY Fecha ASC';

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  0;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    if ($fechaDesde != false) {

        $strIngresos = stringIngresoUndProducto($idProducto, $idAlmacen, $fechaDesde, false);
        $strSalidas = stringSalidaUndProducto($idProducto, $idAlmacen, $fechaDesde, false);
        $strVentas = stringSalidaVentaUndProducto($idProducto, $idAlmacen, $fechaDesde, false);

        $select2 = $strIngresos . ' UNION ALL ' . $strSalidas . ' UNION ALL ' . $strVentas;
        $select2 .= ' ORDER BY Fecha ASC';

        $stmt2 = $this->db->query($select2);
        $stmt2->execute();
        $datasum = $stmt2->fetchAll();

        $inicialEntradas = 0;
        $inicialSalidas = 0;


        foreach ($datasum as $key => $value) {
            $inicialEntradas += (float)($value['IngresoCantidad']);
            $inicialSalidas += (float)($value['SalidaCantidad']);
        }
        // $newKardex = array_filter(kardexSaldo($kardex), $filtro);

        array_unshift($data, array(
            'Fecha' => $fechaDesde . ' 00:00:00',
            'UsuarioReg' => '---',
            'TipoDocSunat' => 16,
            'IngresoCantidad' => $inicialEntradas,
            'IngresoPrecio' => 0,
            'SalidaCantidad' => $inicialSalidas,
            'SalidaPrecio' => 0,
            'Detalle' => 'ACUMULADO ANTERIOR',
            'Nombres' => '-',
            'Serie' => '-',
            'Numero' => '-',
            'Descuento' => 0
        ));
    }


    return $response->withJson($data);
});


$app->get('/productos/masrotacion', function (Request $request, Response $response, array $args) {

    $idProducto = 1;
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaDesde = $request->getParam('fechaDesde') ? $request->getParam('fechaDesde') : '2017-01-01T00:00:00';
    $fechaHasta = $request->getParam('fechaHasta') . ' 23:59:59';

    $strIngresoUnd = stringIngresoUnd(false, $idAlmacen, $fechaHasta, $fechaDesde);
    $strSalidaUnd = stringSalidaUnd(false, $idAlmacen, $fechaHasta, $fechaDesde);

    $strIngresoCaja = stringIngresoCaja(false, $idAlmacen, $fechaHasta, $fechaDesde);
    $strSalidaCaja = stringSalidaCaja(false, $idAlmacen, $fechaHasta, $fechaDesde);

    $strSalidaVentaUnd = stringSalidaVentaUnd(false, $idAlmacen, $fechaHasta, $fechaDesde);
    $strSalidaVentaCaja = stringSalidaVentaCaja(false, $idAlmacen, $fechaHasta, $fechaDesde);

    $strIngresoUndCount = stringIngresoUndCountMonto(false, $idAlmacen, $fechaHasta, $fechaDesde);
    $strVentaUndCount = stringVentaUndCountMonto(false, $idAlmacen, $fechaHasta, $fechaDesde);

    $select = "SELECT Gen_Producto.*, Gen_ProductoCategoria.ProductoCategoria, Gen_ProductoMarca.ProductoMarca,
        Gen_ProductoTalla.ProductoTalla, Gen_ProductoMedicion.ProductoMedicion,
        IFNULL(IngresoUnd.cantidad, 0) AS StockIngresoUnd,
        IFNULL(SalidaUnd.cantidad, 0) AS StockSalidaUnd,
        IFNULL(SalidaVentaUnd.cantidad, 0) AS StockSalidaVentaUnd,
        IFNULL(SalidaVentaCaja.cantidad, 0) AS StockSalidaVentaCaja,
        IFNULL(VentaUndCount.cantidad, 0) AS TotalVentas,
        (IFNULL(SalidaVentaUnd.cantidad, 0) + IFNULL(SalidaVentaCaja.cantidad, 0)) AS CantidadVentas,
        IFNULL(VentaUndCount.monto, 0) AS MontoVentas,
        IFNULL(IngresoUndCount.cantidad, 0) AS TotalCompras,
        (IFNULL(IngresoUnd.cantidad, 0) + IFNULL(IngresoCaja.cantidad, 0)) AS CantidadCompras,
        IFNULL(IngresoUndCount.monto, 0) AS MontoCompras,

        (IFNULL(IngresoUnd.cantidad, 0) + IFNULL(IngresoCaja.cantidad, 0)  - IFNULL(SalidaCaja.cantidad, 0) - IFNULL(SalidaUnd.cantidad, 0) - IFNULL(SalidaVentaUnd.cantidad,0) - IFNULL(SalidaVentaCaja.cantidad,0)) AS stock

        FROM Gen_Producto 
        INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
        INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
        INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
        LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla 
        LEFT JOIN $strIngresoUnd AS IngresoUnd ON Gen_Producto.IdProducto = IngresoUnd.IdProducto
        LEFT JOIN $strSalidaUnd AS SalidaUnd ON Gen_Producto.IdProducto = SalidaUnd.IdProducto
        LEFT JOIN $strIngresoCaja AS IngresoCaja ON Gen_Producto.IdProducto = IngresoCaja.IdProducto
        LEFT JOIN $strSalidaCaja AS SalidaCaja ON Gen_Producto.IdProducto = SalidaCaja.IdProducto
        LEFT JOIN $strSalidaVentaUnd AS SalidaVentaUnd ON Gen_Producto.IdProducto = SalidaVentaUnd.IdProducto 
        LEFT JOIN $strSalidaVentaCaja AS SalidaVentaCaja ON Gen_Producto.IdProducto = SalidaVentaCaja.IdProducto 
        LEFT JOIN $strIngresoUndCount AS IngresoUndCount ON Gen_Producto.IdProducto = IngresoUndCount.IdProducto
        LEFT JOIN $strVentaUndCount AS VentaUndCount ON Gen_Producto.IdProducto = VentaUndCount.IdProducto ";

    $idMarca = $request->getParam('idMarca');
    if ($idMarca) {
        $idMarca = (int) $idMarca;
        $select .= " WHERE Gen_Producto.IdProductoMarca = $idMarca";
    }

    $select .= " HAVING TotalVentas > 0 
        ORDER BY TotalVentas DESC";

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  0;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    // print_r($select);exit;
    $this->db->query('SET SQL_BIG_SELECTS=1');
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->get('/proveedores', function (Request $request, Response $response, array $args) {
    $q = $request->getParam('q');

    $select = "SELECT *, IFNULL(CONCAT(Ruc, ' - ', Proveedor), '-') AS ProveedorRuc FROM Lo_Proveedor";
    $select .= " WHERE Proveedor LIKE '%" . $q . "%' OR Ruc LIKE '%" . $q . "%' ";

    $limit = $request->getParam('limit') ? $request->getParam('limit') : 10;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/proveedores/count', function (Request $request, Response $response, array $args) {
    $select = "SELECT COUNT(*) as total FROM Lo_Proveedor";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});


$app->post('/proveedores', function (Request $request, Response $response) {
    $proveedor = $request->getParam('Proveedor');
    $ruc = $request->getParam('Ruc');
    $direccion = $request->getParam('Direccion');
    $observacion = $request->getParam('Observacion');

    $insert = $this->db->insert(array('Proveedor', 'Ruc', 'Direccion', 'Observacion', 'FechaReg'))
        ->into('Lo_Proveedor')
        ->values(array($proveedor, $ruc, $direccion, $observacion, getNow()));

    $insertId = $insert->execute();

    $data = array(
        "insertId" => $insertId
    );

    return $response->withJson($data);
});


$app->post('/productos/delete', function (Request $request, Response $response) {
    $id = $request->getParam('id');

    if ($id) {
        // Solo anulamos por ahora, mas adelante hacer la comprobacion si el producto existe en las relaciones antes de eliminar
        // definitivamente.
        // $sql = "DELETE FROM Gen_Producto WHERE IdProducto='$id'";
        $update = "UPDATE Gen_Producto SET Anulado=1 WHERE IdProducto=$id";
        $stmt = $this->db->prepare($update);
        $updated = $stmt->execute();


        return $response->withJson(array(
            "updated" => $updated,
            "IdProducto" => $id
        ));
    }
});

/////////////////////////////////////////////////////////////IMAGEN
$app->post('/producto/imagen/{id}[/{slot}]', function (Request $request, Response $response, array $args) {
    if (empty($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        return $response->withJson(array('error' => 'No se recibió una imagen válida.'), 400);
    }

    $slot = isset($args['slot']) ? (int) $args['slot'] : 1;
    $columnas = array(1 => 'Imagen', 2 => 'Imagen2', 3 => 'Imagen3');
    if (!isset($columnas[$slot])) {
        return $response->withJson(array('error' => 'La posición de imagen no es válida.'), 400);
    }

    $archivo = $_FILES['imagen']['tmp_name'];
    $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
    $permitidas = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    if (!in_array($extension, $permitidas, true)) {
        return $response->withJson(array('error' => 'Formato no permitido. Use JPG, PNG, GIF o WEBP.'), 400);
    }

    $directorio = __DIR__ . '/../resources/images/productos';
    if (!is_dir($directorio)) mkdir($directorio, 0775, true);
    $nombreImagen = time() . '-' . $slot . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
    if (!move_uploaded_file($archivo, $directorio . '/' . $nombreImagen)) {
        return $response->withJson(array('error' => 'No se pudo guardar la imagen.'), 500);
    }

    $columna = $columnas[$slot];
    $stmt = $this->db->prepare("UPDATE Gen_Producto SET {$columna}=:imagen WHERE IdProducto=:id");
    $stmt->execute(array(':imagen' => $nombreImagen, ':id' => (int) $args['id']));

    return $response->withJson(array('insertId' => (int) $args['id'], 'slot' => $slot, 'imagen' => $nombreImagen));
});
/////////////////////////////////////////////////////////////IMAGEN

// Devuelve el siguiente código libre conservando el prefijo y sufijo.
// Ejemplo: BO1ES -> BO15ES cuando BO1ES ... BO14ES ya existen.
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

$app->post('/productos', function (Request $request, Response $response) {
    $idProductoMarca = $request->getParam('marca')['IdProductoMarca'];
    $idProductoMedicion = $request->getParam('medicion')['IdProductoMedicion'];
    $idProductoCategoria = $request->getParam('categoria')['IdProductoCategoria'];
    // $idProductoModelo = $request->getParam('modelo')['IdProductoModelo'];
    $idProductoTalla = $request->getParam('talla')['IdProductoTalla'];
    $idProductoFormaFarmaceutica = 1;
    $producto = $request->getParam('Producto');
    $productoDesc = trim((string) $request->getParam('ProductoDesc'));
    $productoDescCorto = trim((string) $request->getParam('ProductoDescCorto'));
    $TienePresentacion = $request->getParam('TienePresentacion') === 1 || $request->getParam('TienePresentacion') === '1' || $request->getParam('TienePresentacion') === 'true'  || $request->getParam('TienePresentacion') === true ? 1 : 0;
    $esChatarra = $request->getParam('EsChatarra') === 1 || $request->getParam('EsChatarra') === '1' || $request->getParam('EsChatarra') === 'true' || $request->getParam('EsChatarra') === true ? 1 : 0;
    $anaquel = $request->getParam('Anaquel');
    $productoModelo = $request->getParam('ProductoModelo') ? $request->getParam('ProductoModelo') : '';
    $fechaReg = getNow();
    $hash = time();
    $controlaStock = 1;
    $porcentajeUtilidad = is_numeric($request->getParam('PorcentajeUtilidad')) ? (float) $request->getParam('PorcentajeUtilidad') : 0;
    $genero = $request->getParam('Genero');
    $color = $request->getParam('Color');
    $botapie = $request->getParam('Botapie');
    $anulado = $request->getParam('Anulado');
    $categoria = $request->getParam('categoria')['ProductoCategoria'];
    $stockMinimo = is_numeric($request->getParam('StockMinimo')) ? (float) $request->getParam('StockMinimo') : 0;
    $preciosPorProducto = json_encode($request->getParam('PreciosPorProducto'));
    $productosDet = $request->getParam('productosDet');
    $precioCosto = is_numeric($request->getParam('PrecioCosto')) ? (float) $request->getParam('PrecioCosto') : 0;
    $precioContado = is_numeric($request->getParam('PrecioContado')) ? (float) $request->getParam('PrecioContado') : 0;
    $porcentajeLiquidacion = is_numeric($request->getParam('PorcentajeLiquidacion')) && $request->getParam('PorcentajeLiquidacion') > 0
        ? (float) $request->getParam('PorcentajeLiquidacion')
        : 0;
    $codigoBarra = strtoupper(trim((string) $request->getParam('CodigoBarra')));
    $CodeBarModelo = strtoupper(trim((string) $request->getParam('CodeBarModelo')));

    // Los códigos identifican al producto y nunca pueden repetirse, incluso si
    // llegan con espacios o con una combinación diferente de mayúsculas.
    $buscarCodigoDuplicado = function ($campo, $codigo, $idExcluir = null) {
        if ($codigo === '') {
            return false;
        }

        $sql = "SELECT IdProducto, Producto, CodigoBarra, CodeBarModelo
                FROM Gen_Producto
                WHERE UPPER(TRIM($campo)) = ?";
        $parametros = array($codigo);
        if ($idExcluir !== null) {
            $sql .= " AND IdProducto <> ?";
            $parametros[] = (int) $idExcluir;
        }
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetch();
    };

    $respuestaCodigoDuplicado = function ($response, $duplicado, $codigo, $etiqueta) {
        return $response->withJson(array(
            'error' => 'No se guardó el producto: el ' . $etiqueta . ' "' . $codigo .
                '" ya pertenece al producto #' . $duplicado['IdProducto'] . ' - ' . $duplicado['Producto'] . '.',
            'codigoBarra' => $duplicado['CodigoBarra'],
            'insertId' => $duplicado['IdProducto']
        ), 409);
    };

    // Actualizamos el producto si le pasamos el ID
    if ($request->getParam('IdProducto')) {
        // aqui se actualiza el producto si existe
        $idProducto = $request->getParam('IdProducto');
        // Obtener el código actual si no se envía
        $stmt = $this->db->prepare("SELECT CodigoBarra FROM Gen_Producto WHERE IdProducto = ?");
        $stmt->execute([$idProducto]);
        $productoActual = $stmt->fetch();

        // Usar el código de barra actual si el frontend no envía uno
        $codigoBarra = $codigoBarra !== '' ? $codigoBarra : strtoupper(trim((string) $productoActual['CodigoBarra']));
        $precioContado = is_numeric($request->getParam('PrecioContado')) ? (float) $request->getParam('PrecioContado') : 0;

        $duplicado = $buscarCodigoDuplicado('CodigoBarra', $codigoBarra, $idProducto);
        if ($duplicado) {
            return $respuestaCodigoDuplicado($response, $duplicado, $codigoBarra, 'código de barras');
        }

        $update = $this->db->update(array(
            "Producto" => $producto,
            "ProductoDesc" => $productoDesc,
            "ProductoDescCorto" => $productoDescCorto,
            "IdProductoMarca" => $idProductoMarca,
            "IdProductoFormaFarmaceutica" => $idProductoFormaFarmaceutica,
            "IdProductoMedicion" => $idProductoMedicion,
            "IdProductoCategoria" => $idProductoCategoria,
            // "IdProductoModelo" => $idProductoModelo,
            "IdProductoTalla" => $idProductoTalla,
            "ControlaStock" => $controlaStock,
            "EsChatarra" => $esChatarra,
            "PorcentajeUtilidad" => $porcentajeUtilidad,
            "Genero" => $genero,
            "PrecioCosto" => $precioCosto,
            "Color" => $color,
            "Anaquel" =>            $anaquel,
            "Botapie" => $botapie,
            "Anulado" => $anulado,
            "ProductoModelo" => $productoModelo,
            "PrecioContado" => $precioContado,
            "StockMinimo" => $stockMinimo,
            "PorcentajeLiquidacion" => $porcentajeLiquidacion,
            "PreciosPorProducto" => $preciosPorProducto,
            "TienePresentacion" => $TienePresentacion,
            "CodigoBarra" => $codigoBarra,
            "CodeBarModelo" => $CodeBarModelo,
        ))
            ->table('Gen_Producto')
            ->where('IdProducto', '=', $idProducto);
        try {
            $affectedRows = $update->execute();
        } catch (PDOException $error) {
            if ((string) $error->getCode() === '23000') {
                $duplicado = $buscarCodigoDuplicado('CodigoBarra', $codigoBarra, $idProducto);
                if ($duplicado) {
                    return $respuestaCodigoDuplicado($response, $duplicado, $codigoBarra, 'código de barras');
                }
            }
            throw $error;
        }
        // Actualizar ProductoDet
        if ($productosDet) {
            $sql = "DELETE FROM Gen_ProductoDet WHERE IdProducto='$idProducto'";
            $stmt = $this->db->prepare($sql);
            $deleted = $stmt->execute();

            foreach ($productosDet as $prod) {
                $insertDet = $this->db->insert(array('IdProducto', 'IdProductoDet', 'Cantidad'))
                    ->into('Gen_ProductoDet')
                    ->values(array($idProducto, $prod['IdProductoDet'], $prod['Cantidad']));
                $insertDetId = $insertDet->execute();
            }
        }
        return $response->withJson(array("insertId" => $idProducto));
    }
    // Fin actualizacion producto

    // Verificar los códigos antes de intentar crear el producto.
    $duplicado = $buscarCodigoDuplicado('CodigoBarra', $codigoBarra);
    if ($duplicado) {
        return $respuestaCodigoDuplicado($response, $duplicado, $codigoBarra, 'código de barras');
    }

    // Evitar nombres repetidos, incluyendo registros antiguos que guardaban el código al final.
    $stmtNombreDuplicado = $this->db->prepare("
        SELECT IdProducto, Producto FROM Gen_Producto
        WHERE Anulado = 0 AND (
            UPPER(TRIM(Producto)) = UPPER(TRIM(?))
            OR UPPER(TRIM(Producto)) LIKE CONCAT(UPPER(TRIM(?)), '-%')
        )
        LIMIT 1
    ");
    $stmtNombreDuplicado->execute(array($producto, $producto));
    $productoExistente = $stmtNombreDuplicado->fetch();
    if (!empty($productoExistente)) {
        return $response->withJson(array(
            'error' => 'No se guardó el producto: ya existe un producto con el nombre "' . $producto . '".',
            'insertId' => $productoExistente['IdProducto']
        ), 409);
    }
    // Inicio verificar Producto
    $select = "SELECT * FROM Gen_Producto
        WHERE (IdProductoMarca = '" . $idProductoMarca
        . "' AND IdProductoMedicion = '" . $idProductoMedicion
        . "' AND IdProductoCategoria = '" . $idProductoCategoria
        . "' AND IdProductoTalla = '" . $idProductoTalla
        . "' AND ProductoModelo = '" . $productoModelo
        . "' AND Genero = '" . $genero
        . "' AND Color = '" . $color
        . "' AND Producto like '" . $producto . "-%')";

    $stmt = $this->db->query($select);
    $prod = $stmt->fetch();
    $stmt->execute();

    //return $response->withJson(array('select' => empty($prod)));

    if (!empty($prod)) {
        return $response->withJson(array(
            'error' => 'No se guardó el producto: ya existe otro producto con la misma marca, medición, categoría, talla, modelo, género y color.',
            'codigoBarra' => $prod['CodigoBarra'],
            'insertId' => $prod['IdProducto']
        ), 409);
    }
    // Final verificar Movimiento

    $insert = $this->db->insert(array(
        'IdProductoMarca',
        'IdProductoFormaFarmaceutica',
        'IdProductoMedicion',
        'IdProductoCategoria',
        'IdProductoTalla',
        'Producto',
        'ProductoDesc',
        'ProductoDescCorto',
        'FechaReg',
        'Hash',
        'ControlaStock',
        'EsChatarra',
        'PorcentajeUtilidad',
        'Genero',
        'Color',
        'Botapie',
        'Anulado',
        'ProductoModelo',
        'StockMinimo',
        'PrecioContado',
        'TienePresentacion',
        'PreciosPorProducto',
        'CodigoBarra',
        'PrecioCosto',
        'Anaquel',
        'PorcentajeLiquidacion',
        'CodeBarModelo'
    ))
        ->into('Gen_Producto')
        ->values(array(
            $idProductoMarca,
            $idProductoFormaFarmaceutica,
            $idProductoMedicion,
            $idProductoCategoria,
            $idProductoTalla,
            $producto,
            $productoDesc,
            $productoDescCorto,
            $fechaReg,
            $hash,
            $controlaStock,
            $esChatarra,
            $porcentajeUtilidad,
            $genero,
            $color,
            $botapie,
            $anulado,
            $productoModelo,
            $stockMinimo,
            $precioContado,
            $TienePresentacion,
            $preciosPorProducto,
            $codigoBarra,
            $precioCosto,
            $anaquel,
            $porcentajeLiquidacion,
            $CodeBarModelo
        ));
    try {
        $insertId = $insert->execute();
    } catch (PDOException $error) {
        if ((string) $error->getCode() === '23000') {
            $duplicado = $buscarCodigoDuplicado('CodigoBarra', $codigoBarra);
            if ($duplicado) {
                return $respuestaCodigoDuplicado($response, $duplicado, $codigoBarra, 'código de barras');
            }
        }
        throw $error;
    }

    $insert = "INSERT INTO Gen_Producto_Totales(IdAlmacen,IdProducto,TotalCompras,TotalVentas) 
               SELECT IdAlmacen, $insertId,0,0 from Lo_Almacen";
    $stmt = $this->db->prepare($insert);
    $stmt->execute();
    // Generando codigo de barras  // actualizar el nombre para que sea unico
    $codigoBarra = $codigoBarra ? $codigoBarra : substr($categoria, 0, 2) . $insertId . substr($producto, 0, 2);

    $update = $this->db->update(array("CodigoBarra" => $codigoBarra))
        ->table('Gen_Producto')
        ->where('IdProducto', '=', $insertId);
    $affectedRows = $update->execute();

    // Insertar productoDet
    if ($productosDet) {
        foreach ($productosDet as $prod) {
            $insertDet = $this->db->insert(array('IdProducto', 'IdProductoDet', 'Cantidad'))
                ->into('Gen_ProductoDet')
                ->values(array($insertId, $prod['IdProducto'], $prod['Cantidad']));
            $insertDetId = $insertDet->execute();
        }
    }

    return $response->withJson(array("insertId" => $insertId));
});



$app->get('/movimiento/productos', function (Request $request, Response $response, array $args) {
    $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
    $hashMovimiento = $request->getParam('hash');

    $select = "SELECT DATE_FORMAT(Lo_Movimiento.FechaReg, '%d%m%Y') as Fecha, Lo_MovimientoDetalle.IdProducto, Gen_Producto.Producto, Gen_ProductoMarca.ProductoMarca, Gen_Producto.ProductoModelo,
        Gen_Producto.Color, Gen_Producto.CodigoBarra, Gen_Producto.PrecioContado, Gen_ProductoTalla.ProductoTalla, Lo_MovimientoDetalle.Cantidad,
        Gen_Producto.Botapie, Gen_Producto.Genero
        FROM Lo_MovimientoDetalle
        INNER JOIN Lo_Movimiento ON Lo_MovimientoDetalle.hashMovimiento = Lo_Movimiento.Hash 
        INNER JOIN Gen_Producto ON Lo_MovimientoDetalle.IdProducto = Gen_Producto.IdProducto
        INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
        LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla
        WHERE hashMovimiento = '$hashMovimiento'";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});




$app->get('/movimientos/tipos', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Lo_MovimientoTipo WHERE Anulado=0";
    $select .= " AND TipoMovimiento LIKE '%" . $request->getParam('q') . "%' ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});



$app->post('/movimientos', function (Request $request, Response $response) {
    require_once __DIR__ . '/guardar-movimiento.php';
    return imcSaveMovement($this->db, $request, $response, false);
});

$app->post('/movimientos/traslado', function (Request $request, Response $response) {
    require_once __DIR__ . '/guardar-movimiento.php';
    return imcSaveMovement($this->db, $request, $response, true);
});

$app->post('/actualizarmovimientodet', function (Request $request, Response $response) {
    $hash = $request->getParam('hash');
    $idProducto = $request->getParam('idProducto');
    $cantidad = $request->getParam('cantidad');
    $precio = $request->getParam('precio');
    $precioAnterior = $request->getParam('precioAnterior');

    $update = "UPDATE Lo_MovimientoDetalle SET Precio=$precio, Cantidad=$cantidad 
    WHERE hashMovimiento='$hash' AND IdProducto=$idProducto AND CAST(Precio AS DECIMAL)=CAST($precioAnterior AS DECIMAL)";

    $stmt = $this->db->prepare($update);
    $updated = $stmt->execute();

    if ($updated) {
        // actualizar precio costo del producto
    }

    return $response->withJson(array(
        "updated" => $updated,
        "hash"    => $hash,
        "idProducto" => $idProducto,
        "cantidad"   => $cantidad,
        "precio"     => $precio,
        "precioAnterior" => $precioAnterior
    ));
});

$app->get('/monedas', function (Request $request, Response $response, array $args) {
    $select = $this->db->select()->from('Gen_Moneda')->whereLike('Moneda', '%' . $request->getParam('q') . '%');
    $stmt = $select->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/almacenes/id/{id}', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Lo_Almacen WHERE Anulado=0 AND IdAlmacen=$args[id]";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/almacenes', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Lo_Almacen WHERE Anulado=0";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/almacenes/primero', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Lo_Almacen WHERE Anulado=0 ORDER BY IdAlmacen ASC LIMIT 1 ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->post('/almacenes', function (Request $request, Response $response) {

    $almacen = $request->getParam('Almacen');
    $anulado = $request->getParam('Anulado');

    $idAlmacen = $request->getParam('IdAlmacen');
    $insertId = $idAlmacen;

    if ($idAlmacen) {
        // Tabla Almacen
        $update = $this->db->update(array(
            "Almacen" => $almacen,
            'Anulado' => $anulado,
        ))
            ->table('Lo_Almacen')
            ->where('IdAlmacen', '=', $idAlmacen);
        $affectedRows = $update->execute();

        // Tabla Punto Venta
        $update = $this->db->update(array(
            "PuntoVenta" => $almacen,
            'Anulado' => $anulado,
        ))
            ->table('Ve_DocVentaPuntoVenta')
            ->where('IdDocVentaPuntoVenta', '=', $idAlmacen);
        $affectedRows = $update->execute();

        $almacenSeries = $request->getParam('almacenesDet');

        // Actualizar ProductoDet
        $sql = "DELETE FROM Ve_DocVentaPuntoVentaDet WHERE IdDocVentaPuntoVenta='$idAlmacen'";
        $stmt = $this->db->prepare($sql);
        $deleted = $stmt->execute();

        foreach ($almacenSeries as $almacen) {
            // if ($producto['total'] > 0) {

            $IdDocVentaTipoDoc = $almacen['IdTipoDoc'];
            $serie = $almacen['Serie'];

            $insertDet = $this->db->insert(array("IdDocVentaPuntoVenta", "IdDocVentaTipoDoc", "Serie"))
                ->into('Ve_DocVentaPuntoVentaDet')
                ->values(array($idAlmacen, $IdDocVentaTipoDoc, $serie));

            $affectedRows = $insertDet->execute();

            // }
        }
    } else {
        // Tabla Almacen
        $insert = $this->db->insert(array('Almacen', 'Anulado'))
            ->into('Lo_Almacen')
            ->values(array($almacen, $anulado));
        $insertId = $insert->execute();
        $idAlmacen = $this->db->lastInsertId();
        // Tabla Punto Venta
        $insert = $this->db->insert(array('IdDocVentaPuntoVenta', 'PuntoVenta', 'Anulado'))
            ->into('Ve_DocVentaPuntoVenta')
            ->values(array($idAlmacen, $almacen, $anulado));
        $insertId = $insert->execute();
        $idAlmacen = $this->db->lastInsertId();

        $almacenSeries = $request->getParam('almacenesDet');

        foreach ($almacenSeries as $almacen) {

            $IdDocVentaTipoDoc = $almacen['IdTipoDoc'];
            $serie = $almacen['Serie'];

            $insertDet = $this->db->insert(array("IdDocVentaPuntoVenta", "IdDocVentaTipoDoc", "Serie"))
                ->into('Ve_DocVentaPuntoVentaDet')
                ->values(array($idAlmacen, $IdDocVentaTipoDoc, $serie));

            $affectedRows = $insertDet->execute();
        }
    }

    $select = "SELECT * FROM Lo_Almacen INNER JOIN Ve_DocVentaPuntoVenta ON Lo_Almacen.IdAlmacen = Ve_DocVentaPuntoVenta.IdDocVentaPuntoVenta";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->post('/almacenes/delete', function (Request $request, Response $response) {
    $id = $request->getParam('id');

    if ($id) {
        // Anular almacen
        $update = "UPDATE Lo_Almacen SET Lo_Almacen.Anulado=1 WHERE Lo_Almacen.IdAlmacen=$id;";
        $stmt = $this->db->prepare($update);
        $updated = $stmt->execute();

        // Anular punto de venta
        $update = "UPDATE Ve_DocVentaPuntoVenta SET Ve_DocVentaPuntoVenta.Anulado=1 WHERE Ve_DocVentaPuntoVenta.IdDocVentaPuntoVenta=$id;";
        $stmt = $this->db->prepare($update);
        $updated = $stmt->execute();

        return $response->withJson(array(
            "IdAlmacen" => $id
        ));
    }
});

$app->get('/gestion/almacenes', function (Request $request, Response $response, array $arg) {
    // Seleccionar la lista de almacenes y su respectiva relacion con punto de venta y los tipos de 
    // documentos asignados a dicho almacen con su respectiva serie de venta 
    $select = "SELECT * FROM Lo_Almacen INNER JOIN Ve_DocVentaPuntoVenta ON Lo_Almacen.IdAlmacen = Ve_DocVentaPuntoVenta.IdDocVentaPuntoVenta";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});

$app->get('/gestion/almacenes/series', function (Request $request, Response $response, array $arg) {
    $IdDocVentaPuntoVenta = $request->getParam('IdDocVentaPuntoVenta');

    $select = "
            SELECT Ve_DocVentaPuntoVentaDet.IdDocVentaPuntoVenta, 
                (SELECT Ve_DocVentaTipoDoc.TipoDoc FROM Ve_DocVentaTipoDoc WHERE Ve_DocVentaTipoDoc.IdTipoDOc = IdDocVentaTipoDoc) as TipoDoc,
                IdDocVentaTipoDoc as IdTipoDoc,
                Serie 
            FROM `Ve_DocVentaPuntoVentaDet`WHERE Ve_DocVentaPuntoVentaDet.IdDocVentaPuntoVenta = '$IdDocVentaPuntoVenta'";

    // $select = "SELECT * FROM `Ve_DocVentaPuntoVentaDet`WHERE IdDocVentaPuntoVenta = '$IdDocVentaPuntoVenta'";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});

$app->get('/puntoventa/id/{id}', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Ve_DocVentaPuntoVenta WHERE Anulado=0 AND IdDocVentaPuntoVenta=$args[id]";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/puntoventa/primero', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Ve_DocVentaPuntoVenta WHERE Anulado=0 ORDER BY IdDocVentaPuntoVenta ASC LIMIT 1 ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/consultarRUC', function (Request $request, Response $response, array $args) use ($app) {
    $tipo = strtoupper(trim((string) $request->getParam('type')));
    $numero = preg_replace('/\D+/', '', (string) $request->getParam('numero'));
    $longitudEsperada = $tipo === 'RUC' ? 11 : 8;
    if (!in_array($tipo, array('RUC', 'DNI'), true) || strlen($numero) !== $longitudEsperada) {
        return $response->withJson(array(
            'success' => false,
            'message' => 'Ingrese un ' . ($tipo === 'RUC' ? 'RUC de 11' : 'DNI de 8') . ' dígitos.'
        ), 422);
    }

    $stmtEmpresa = $this->db->query(
        'SELECT CONSULTARUCURL, CONSULTARUCTOKEN FROM GEN_EMPRESA ORDER BY IDEMPRESA ASC LIMIT 1'
    );
    $empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC) ?: array();
    if (empty($empresa['CONSULTARUCURL']) || empty($empresa['CONSULTARUCTOKEN'])) {
        return $response->withJson(array(
            'success' => false,
            'message' => 'El servicio de consulta DNI/RUC no está configurado.'
        ), 503);
    }
    $ruta = $tipo === 'RUC' ? 'ruc/' : 'dni/';
    $ch = curl_init(rtrim($empresa['CONSULTARUCURL'], '/') . '/' . $ruta . $numero . '?token=' . urlencode($empresa['CONSULTARUCTOKEN']));

    $headers = array(
        "Content-Type: application/json; charset=UTF-8",
        "Cache-Control: no-cache",
        "Pragma: no-cache"
    );
    //var_dump("https://www.facturacionelectronica.us/facturacion/controller/ws_consulta_rucdni_v2.php?usuario=20573027125&password=5i573m45&documento=" . $_GET['type'] . "&nro_documento=" . $_GET['numero']);
    // $ch = curl_init("https://www.facturacionelectronica.us/facturacion/controller/ws_consulta_rucdni_v2.php?usuario=20573027125&password=5i573m45&documento=" . $_GET['type'] . "&nro_documento=" . $_GET['numero']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    //quitar en produccion
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    //curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    //curl_setopt($ch, CURLOPT_USERPWD, "PRUEBA:LOG");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $contenido = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // Se cierra el recurso CURL y se liberan los recursos del sistema
    curl_close($ch);
    if ($contenido === false) {
        return $response->withJson(array(
            'success' => false,
            'message' => 'No se pudo conectar con el servicio de consulta DNI/RUC.',
            'detail' => $curlError
        ), 502);
    }

    $data = json_decode($contenido, true);
    if (!is_array($data)) {
        return $response->withJson(array(
            'success' => false,
            'message' => 'El servicio DNI/RUC devolvió una respuesta inválida.'
        ), 502);
    }
    if ($httpCode >= 400 || (isset($data['success']) && !$data['success'])) {
        return $response->withJson(array(
            'success' => false,
            'message' => isset($data['message']) ? $data['message'] : 'No se encontraron datos para el documento ingresado.'
        ), $httpCode >= 400 ? $httpCode : 404);
    }

    $data['success'] = true;
    return $response->withJson($data);
});


function stringIngresoUndProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT Lo_Movimiento.MovimientoFecha AS Fecha, Lo_Movimiento.UsuarioReg, Lo_MovimientoTipo.CodSunat,  Lo_MovimientoTipo.TipoMovSunat as TipoDocSunat,
    Lo_MovimientoTipo.TipoMovimiento AS Detalle, Lo_Movimiento.Serie, Lo_Movimiento.Numero, Lo_Proveedor.Proveedor AS Nombres, 
    Lo_MovimientoDetalle.Cantidad AS IngresoCantidad, Lo_MovimientoDetalle.Precio AS IngresoPrecio, 
    '0' AS SalidaCantidad, '0' AS SalidaPrecio, 
    '0' AS Descuento FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        LEFT JOIN Lo_Proveedor ON Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
        WHERE (Lo_MovimientoTipo.VaRegCompra = 1 OR Lo_MovimientoTipo.Tipo = 2) AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
            AND Lo_MovimientoDetalle.IdProducto=$idProducto AND Lo_Movimiento.Anulado=0 ";
    $select .= $fechaDesde ? " AND Lo_Movimiento.MovimientoFecha BETWEEN '$fechaDesde' AND '$fechaHasta' )" : " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta' )";

    return $select;
}
function stringSalidaUndProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT Lo_Movimiento.MovimientoFecha AS Fecha, Lo_Movimiento.UsuarioReg, Lo_MovimientoTipo.CodSunat, Lo_MovimientoTipo.TipoMovSunat as TipoDocSunat,
    Lo_MovimientoTipo.TipoMovimiento AS Detalle, Lo_Movimiento.Serie, Lo_Movimiento.Numero, Lo_Proveedor.Proveedor AS Nombres, 
    '0' AS IngresoCantidad, '0' AS IngresoPrecio, 
    (CASE
    WHEN Lo_MovimientoTipo.CodSunat = '07' AND Lo_Movimiento.NotaIdMotivo NOT IN ('01', '02', '06', '07') THEN '0'
    ELSE Lo_MovimientoDetalle.Cantidad
    END) AS SalidaCantidad,
    (CASE
        WHEN Lo_MovimientoTipo.CodSunat = '07' AND Lo_Movimiento.NotaIdMotivo NOT IN ('01', '02', '06', '07') THEN '0'
        ELSE Lo_MovimientoDetalle.Precio
    END) AS SalidaPrecio,
    '0' AS Descuento FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        LEFT JOIN Lo_Proveedor ON Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
        WHERE (Lo_MovimientoTipo.VaRegCompra = 1 OR Lo_MovimientoTipo.Tipo = 2) AND Lo_Movimiento.IdAlmacenOrigen = $idAlmacen
            AND Lo_MovimientoDetalle.IdProducto=$idProducto AND Lo_Movimiento.Anulado=0 ";
    $select .= $fechaDesde ? " AND Lo_Movimiento.MovimientoFecha BETWEEN '$fechaDesde' AND '$fechaHasta' )" : " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta' )";

    return $select;
}
function stringSalidaVentaUndProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT Ve_DocVenta.FechaDoc AS Fecha, Ve_DocVenta.UsuarioReg, Ve_DocVentaTipoDoc.CodSunat, Ve_DocVentaTipoDoc.TipoDocSunat,
    CONCAT('VENTA - ', Ve_DocVentaTipoDoc.TipoDoc) AS Detalle, Ve_DocVenta.Serie, Ve_DocVenta.Numero, 
    Ve_DocVentaCliente.Cliente AS Nombres, 
    (CASE
    WHEN Ve_DocVentaTipoDoc.CodSunat = '07' AND NotaIdMotivo IN ('01', '02', '06', '07') THEN Ve_DocVentaDet.Cantidad
    ELSE '0'
    END) AS IngresoCantidad,
    (CASE
        WHEN Ve_DocVentaTipoDoc.CodSunat = '07' AND NotaIdMotivo IN ('01', '02', '06', '07') THEN Ve_DocVentaDet.Precio
        ELSE '0'
    END) AS IngresoPrecio,
    (CASE
        WHEN Ve_DocVentaTipoDoc.CodSunat = '07' THEN '0'
        ELSE Ve_DocVentaDet.Cantidad
    END) AS SalidaCantidad,
    (CASE
        WHEN Ve_DocVentaTipoDoc.CodSunat = '07' THEN '0'
        ELSE Ve_DocVentaDet.Precio
    END) AS SalidaPrecio,
    Ve_DocVentaDet.Descuento FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        LEFT JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente= Ve_DocVentaCliente.IdCliente
        WHERE Ve_DocVenta.IdAlmacen = $idAlmacen
            AND Ve_DocVentaDet.IdProducto = $idProducto
            AND Ve_DocVenta.Anulado = 0 ";
    $select .= $fechaDesde ? " AND Ve_DocVenta.FechaDoc BETWEEN '$fechaDesde' AND '$fechaHasta' )" : " AND Ve_DocVenta.FechaDoc < '$fechaHasta' )";

    return $select;
}



function stringIngresoUnd($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT IFNULL(SUM(Lo_MovimientoDetalle.Cantidad), 0) AS cantidad FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        WHERE (Lo_MovimientoTipo.VaRegCompra = 1 OR Lo_MovimientoTipo.Tipo = 2) AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
            AND Lo_MovimientoDetalle.IdProducto=$idProducto AND Lo_Movimiento.Anulado=0 
            AND Lo_Movimiento.MovimientoFecha < '$fechaHasta')";

    if (!$idProducto) {
        $select = "(SELECT Lo_MovimientoDetalle.IdProducto, IFNULL(SUM(Lo_MovimientoDetalle.Cantidad), 0) AS cantidad FROM Lo_Movimiento
            INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
            INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
            WHERE (Lo_MovimientoTipo.VaRegCompra = 1 OR Lo_MovimientoTipo.Tipo = 2) AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
                AND Lo_Movimiento.Anulado=0 " .
            ($fechaDesde ? " AND Lo_Movimiento.MovimientoFecha > '$fechaDesde' " : "")
            . " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta'
            GROUP BY Lo_MovimientoDetalle.IdProducto)";
    }

    return $select;
}
function stringIngresoCaja($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT IFNULL(SUM(Lo_MovimientoDetalle.Cantidad), 0) AS cantidad FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        INNER JOIN Gen_ProductoDet ON Lo_MovimientoDetalle.IdProducto = Gen_ProductoDet.IdProducto
        WHERE (Lo_MovimientoTipo.VaRegCompra = 1 OR Lo_MovimientoTipo.Tipo = 2) AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
            AND Gen_ProductoDet.IdProductoDet=$idProducto AND Lo_Movimiento.Anulado=0 
            AND Lo_Movimiento.MovimientoFecha < '$fechaHasta')";

    if (!$idProducto) {
        $select = "(SELECT Gen_ProductoDet.IdProductoDet AS IdProducto, IFNULL(SUM(Lo_MovimientoDetalle.Cantidad * Gen_ProductoDet.Cantidad), 0) AS cantidad 
            FROM Lo_Movimiento
            INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
            INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
            INNER JOIN Gen_ProductoDet ON Lo_MovimientoDetalle.IdProducto = Gen_ProductoDet.IdProducto
            WHERE (Lo_MovimientoTipo.VaRegCompra = 1 OR Lo_MovimientoTipo.Tipo = 2) AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
                AND Lo_Movimiento.Anulado=0 " .
            ($fechaDesde ? " AND Lo_Movimiento.MovimientoFecha > '$fechaDesde' " : "")
            . " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta'
            GROUP BY Gen_ProductoDet.IdProductoDet)";
    }
    // print_r($select);exit();
    return $select;
}

function stringSalidaCaja($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT IFNULL(SUM(Lo_MovimientoDetalle.Cantidad), 0) AS cantidad FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        INNER JOIN Gen_ProductoDet ON Lo_MovimientoDetalle.IdProducto = Gen_ProductoDet.IdProducto
        WHERE (Lo_MovimientoTipo.VaRegCompra = 1 OR Lo_MovimientoTipo.Tipo = 2) AND Lo_Movimiento.IdAlmacenOrigen = $idAlmacen
            AND Gen_ProductoDet.IdProductoDet=$idProducto AND Lo_Movimiento.Anulado=0 
            AND Lo_Movimiento.MovimientoFecha < '$fechaHasta')";

    if (!$idProducto) {
        $select = "(SELECT Gen_ProductoDet.IdProductoDet AS IdProducto, IFNULL(SUM(IF(Lo_MovimientoTipo.CodSunat = '07', IF(Lo_Movimiento.NotaIdMotivo IN ('01', '02', '06', '07'),Gen_ProductoDet.cantidad * Lo_MovimientoDetalle.Cantidad,0), Gen_ProductoDet.cantidad * Lo_MovimientoDetalle.Cantidad)), 0) AS cantidad 
            FROM Lo_Movimiento
            INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
            INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
            INNER JOIN Gen_ProductoDet ON Lo_MovimientoDetalle.IdProducto = Gen_ProductoDet.IdProducto
            WHERE (Lo_MovimientoTipo.VaRegCompra = 1 OR Lo_MovimientoTipo.Tipo = 2) AND Lo_Movimiento.IdAlmacenOrigen = $idAlmacen
                AND Lo_Movimiento.Anulado=0 " .
            ($fechaDesde ? " AND Lo_Movimiento.MovimientoFecha > '$fechaDesde' " : "")
            . " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta'
            GROUP BY Gen_ProductoDet.IdProductoDet)";
    }
    // print_r($select);exit();
    return $select;
}

function stringSalidaUnd($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT IFNULL(SUM(Lo_MovimientoDetalle.Cantidad), 0) AS cantidad FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        WHERE (Lo_MovimientoTipo.VaRegCompra = 1 OR Lo_MovimientoTipo.Tipo = 2) AND Lo_Movimiento.IdAlmacenOrigen = $idAlmacen
            AND Lo_MovimientoDetalle.IdProducto=$idProducto AND Lo_Movimiento.Anulado=0 
            AND Lo_Movimiento.MovimientoFecha < '$fechaHasta')";

    if (!$idProducto) {
        $select = "(SELECT Lo_MovimientoDetalle.IdProducto, IFNULL(SUM(IF(Lo_MovimientoTipo.CodSunat = '07', IF(Lo_Movimiento.NotaIdMotivo IN ('01', '02', '06', '07'),Lo_MovimientoDetalle.Cantidad,0) ,Lo_MovimientoDetalle.Cantidad)), 0) AS cantidad FROM Lo_Movimiento
            INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
            INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
            WHERE (Lo_MovimientoTipo.VaRegCompra = 1 OR Lo_MovimientoTipo.Tipo = 2) AND Lo_Movimiento.IdAlmacenOrigen = $idAlmacen
                AND Lo_Movimiento.Anulado=0 " .
            ($fechaDesde ? " AND Lo_Movimiento.MovimientoFecha > '$fechaDesde' " : "")
            . " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta'
            GROUP BY Lo_MovimientoDetalle.IdProducto)";
    }

    return $select;
}

function stringSalidaVentaUnd($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT IFNULL(SUM(Ve_DocVentaDet.Cantidad), 0) AS cantidad FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        WHERE Ve_DocVenta.IdAlmacen = $idAlmacen
            AND Ve_DocVentaDet.IdProducto = $idProducto
            AND Ve_DocVenta.Anulado = 0
            AND Ve_DocVentaTipoDoc.VaRegVenta = 1
            AND Ve_DocVenta.FechaDoc < '$fechaHasta')";

    if (!$idProducto) {
        $select = "(SELECT Ve_DocVentaDet.IdProducto,IFNULL(SUM(IF(Ve_DocVentaTipoDoc.CodSunat = '07', IF(Ve_DocVenta.NotaIdMotivo IN ('01', '02', '06', '07'),-1*Ve_DocVentaDet.Cantidad,0),Ve_DocVentaDet.Cantidad)), 0) AS cantidad FROM Ve_DocVenta
            INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
            INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
            WHERE Ve_DocVenta.IdAlmacen = $idAlmacen
                AND Ve_DocVenta.Anulado = 0
                AND Ve_DocVentaTipoDoc.VaRegVenta = 1 " .
            ($fechaDesde ? " AND Ve_DocVenta.FechaDoc > '$fechaDesde' " : "")
            . " AND Ve_DocVenta.FechaDoc < '$fechaHasta'
            GROUP BY Ve_DocVentaDet.IdProducto)";
    }
    // print_r($select);exit();
    return $select;
}

function stringSalidaVentaCaja($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT IFNULL(SUM(Gen_ProductoDet.cantidad * Ve_DocVentaDet.Cantidad), 0) AS cantidad FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        INNER JOIN Gen_ProductoDet ON Ve_DocVentaDet.IdProducto=Gen_ProductoDet.IdProducto
        WHERE Ve_DocVenta.IdAlmacen = $idAlmacen
            AND Gen_ProductoDet.IdProductoDet = $idProducto
            AND Ve_DocVenta.Anulado = 0
            AND Ve_DocVentaTipoDoc.VaRegVenta = 1
            AND Ve_DocVenta.FechaDoc < '$fechaHasta')";

    if (!$idProducto) {
        $select = "(SELECT Gen_ProductoDet.IdProductoDet AS IdProducto, IFNULL(SUM(IF(Ve_DocVentaTipoDoc.CodSunat = '07', IF(Ve_DocVenta.NotaIdMotivo IN ('01', '02', '06', '07'),-1*Gen_ProductoDet.cantidad * Ve_DocVentaDet.Cantidad,0), Gen_ProductoDet.cantidad * Ve_DocVentaDet.Cantidad)), 0) AS cantidad 
            FROM Ve_DocVenta
            INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
            INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
            INNER JOIN Gen_ProductoDet ON Ve_DocVentaDet.IdProducto=Gen_ProductoDet.IdProducto
            WHERE Ve_DocVenta.IdAlmacen = $idAlmacen
                AND Ve_DocVenta.Anulado = 0
                AND Ve_DocVentaTipoDoc.VaRegVenta = 1 " .
            ($fechaDesde ? " AND Ve_DocVenta.FechaDoc > '$fechaDesde' " : "")
            . " AND Ve_DocVenta.FechaDoc < '$fechaHasta'
            GROUP BY Gen_ProductoDet.IdProductoDet)";
    }
    //print_r($select);exit();
    return $select;
}

function stringIngresoUndCount($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    /* $select = "(SELECT IFNULL(SUM(Lo_MovimientoDetalle.Cantidad), 0) AS cantidad FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
            AND Lo_MovimientoDetalle.IdProducto=$idProducto AND Lo_Movimiento.Anulado=0 
            AND Lo_Movimiento.MovimientoFecha < '$fechaHasta')"; */

    if (!$idProducto) {
        $select = "(SELECT Lo_MovimientoDetalle.IdProducto, COUNT(Lo_Movimiento.Hash) AS cantidad FROM Lo_Movimiento
            INNER JOIN Lo_MovimientoDetalle ON Lo_Movimiento.Hash = Lo_MovimientoDetalle.hashMovimiento
            INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
            WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
                AND Lo_Movimiento.Anulado=0 " .
            ($fechaDesde ? " AND Lo_Movimiento.MovimientoFecha > '$fechaDesde' " : "")
            . " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta'
            GROUP BY Lo_MovimientoDetalle.IdProducto)";
    }

    return $select;
}

function stringIngresoUndCountMonto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    if (!$idProducto) {
        return "(SELECT Lo_MovimientoDetalle.IdProducto,
            COUNT(Lo_Movimiento.Hash) AS cantidad,
            ROUND(SUM(Lo_MovimientoDetalle.Precio * Lo_MovimientoDetalle.Cantidad), 2) AS monto
            FROM Lo_Movimiento
            INNER JOIN Lo_MovimientoDetalle ON Lo_Movimiento.Hash = Lo_MovimientoDetalle.hashMovimiento
            INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
            WHERE Lo_MovimientoTipo.VaRegCompra = 1
                AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
                AND Lo_Movimiento.Anulado = 0 " .
                ($fechaDesde ? " AND Lo_Movimiento.MovimientoFecha >= '$fechaDesde' " : "") .
                " AND Lo_Movimiento.MovimientoFecha <= '$fechaHasta'
            GROUP BY Lo_MovimientoDetalle.IdProducto)";
    }
}

function stringVentaUndCountMonto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    if (!$idProducto) {
        return "(SELECT Ve_DocVentaDet.IdProducto,
            COUNT(Ve_DocVenta.idDocVenta) AS cantidad,
            ROUND(SUM(Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad - Ve_DocVentaDet.Descuento), 2) AS monto
            FROM Ve_DocVenta
            INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta = Ve_DocVentaDet.IdDocVenta
            INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
            WHERE Ve_DocVenta.IdAlmacen = $idAlmacen
                AND Ve_DocVenta.Anulado = 0
                AND Ve_DocVentaTipoDoc.VaRegVenta = 1 " .
                ($fechaDesde ? " AND Ve_DocVenta.FechaDoc >= '$fechaDesde' " : "") .
                " AND Ve_DocVenta.FechaDoc <= '$fechaHasta'
            GROUP BY Ve_DocVentaDet.IdProducto)";
    }
}
/*

SELECT Lo_MovimientoDetalle.IdProducto, Gen_ProductoDet.IdProductoDet, (Lo_MovimientoDetalle.Cantidad * Gen_ProductoDet.Cantidad) AS cantidad
/*IFNULL(SUM(Lo_MovimientoDetalle.Cantidad), 0) AS cantidad */
/*
FROM Lo_Movimiento 
INNER JOIN Lo_MovimientoDetalle ON Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento 
INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo 
INNER JOIN Gen_ProductoDet ON Lo_MovimientoDetalle.IdProducto = Gen_ProductoDet.IdProducto

WHERE Lo_MovimientoTipo.VaRegCompra = 1 
	AND Lo_Movimiento.IdAlmacenDestino = 1 
	AND Lo_Movimiento.Anulado=0 
	AND Lo_Movimiento.MovimientoFecha < '2018-07-23 17:25:36' 

GROUP BY Gen_ProductoDet.IdProductoDet

*/

$app->get('/productos/stock/ingresos', function (Request $request, Response $response, array $args) {
    /*$idProducto = $request->getParam('idProducto');
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaHasta = $request->getParam('fechaHasta');

    $data = stockIngresoUnd($this->db, $idProducto, $idAlmacen, $fechaHasta);

    return $response->withJson($data);*/
});
$app->get('/productos/stock/salidas', function (Request $request, Response $response, array $args) {

    /*// SALIDAS POR MOVIMIENTO
    $idProducto = $request->getParam('idProducto');
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaHasta = $request->getParam('fechaHasta');

    $salidaUnd = stockSalidaUnd($this->db, $idProducto, $idAlmacen, $fechaHasta);
    $cantidadSalida = $salidaUnd['cantidad'];

    // SALIDAS POR VENTAS
    $salidaVentaUnd = stockSalidaVentaUnd($this->db, $idProducto, $idAlmacen, $fechaHasta);
    $salidaVentaUnd['cantidad'] += $cantidadSalida;

    return $response->withJson($salidaVentaUnd);*/
});



$app->get('/productos/stock', function (Request $request, Response $response, array $args) {

    $idAlmacen = $request->getParam('idAlmacen');
    $fechaDesde = $request->getParam('fechaDesde');
    $fechaHastaSolicitada = $request->getParam('fechaHasta');
    $fechaHasta = $fechaHastaSolicitada ? $fechaHastaSolicitada . (strlen($fechaHastaSolicitada) === 10 ? ' 23:59:59' : '') : getNow();
    if ($fechaDesde && strlen($fechaDesde) === 10) $fechaDesde .= ' 00:00:00';

    // $strIngresoUnd = stringIngresoUnd('Gen_Producto.IdProducto', $idAlmacen, $fechaHasta);
    // $strSalidaUnd = stringSalidaUnd('Gen_Producto.IdProducto', $idAlmacen, $fechaHasta);
    // $strSalidaVentaUnd = stringSalidaVentaUnd('Gen_Producto.IdProducto', $idAlmacen, $fechaHasta);

    $strIngresoUnd = stringIngresoUnd(false, $idAlmacen, $fechaHasta, $fechaDesde);
    $strSalidaUnd = stringSalidaUnd(false, $idAlmacen, $fechaHasta, $fechaDesde);

    $strIngresoCaja = stringIngresoCaja(false, $idAlmacen, $fechaHasta, $fechaDesde);
    $strSalidaCaja = stringSalidaCaja(false, $idAlmacen, $fechaHasta, $fechaDesde);

    $strSalidaVentaUnd = stringSalidaVentaUnd(false, $idAlmacen, $fechaHasta, $fechaDesde);
    $strSalidaVentaCaja = stringSalidaVentaCaja(false, $idAlmacen, $fechaHasta, $fechaDesde);

    /*$select = "SELECT Gen_Producto.*, Gen_ProductoCategoria.ProductoCategoria, Gen_ProductoMarca.ProductoMarca,
            Gen_ProductoTalla.ProductoTalla, Gen_ProductoMedicion.ProductoMedicion,
            
            $strIngresoUnd AS StockIngresoUnd, 
            $strSalidaUnd AS StockSalidaUnd,
            $strSalidaVentaUnd AS StockSalidaVentaUnd,  
            ((SELECT StockIngresoUnd) - (SELECT StockSalidaUnd) - (SELECT StockSalidaVentaUnd)) AS stock 


            FROM Gen_Producto 
            INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
            INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
            LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla ";*/

    if ($request->getParam('sumaStock')) {
        $select = "SELECT
            SUM(COALESCE(Gen_Producto_Totales.TotalCompras,0) - COALESCE(Gen_Producto_Totales.TotalVentas,0)) AS sumaStock

            FROM Gen_Producto 
            INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
            INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
            LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla 
            JOIN Gen_Producto_Totales 
            ON Gen_Producto_Totales.IdProducto = Gen_Producto.IdProducto
            AND Gen_Producto_Totales.IdAlmacen = $idAlmacen";
    } else if ($request->getParam('sumaValorizado')) {
        // PRECIO VENTA (PrecioContado)
        $select = "SELECT
            SUM(Gen_Producto.PrecioContado * (COALESCE(Gen_Producto_Totales.TotalCompras,0) - COALESCE(Gen_Producto_Totales.TotalVentas,0))) AS monto 
            FROM Gen_Producto 
            INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
            INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
            LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla 
            LEFT JOIN Gen_Producto_Totales 
            ON Gen_Producto_Totales.IdProducto = Gen_Producto.IdProducto
            AND Gen_Producto_Totales.IdAlmacen = $idAlmacen";

    } else if ($request->getParam('sumaValorizadoSin')) {
        // PRECIO COSTO (PrecioCosto)
        $select = "SELECT
            SUM(Gen_Producto.PrecioCosto * (COALESCE(Gen_Producto_Totales.TotalCompras,0) - COALESCE(Gen_Producto_Totales.TotalVentas,0))) AS monto 
            FROM Gen_Producto
            INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
            INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
            LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla 
            LEFT JOIN Gen_Producto_Totales 
            ON Gen_Producto_Totales.IdProducto = Gen_Producto.IdProducto
            AND Gen_Producto_Totales.IdAlmacen = $idAlmacen";

    } else if ($fechaHastaSolicitada || $fechaDesde) {
        $select = "SELECT Gen_Producto.*, Gen_ProductoCategoria.ProductoCategoria, Gen_ProductoMarca.ProductoMarca,
            Gen_ProductoTalla.ProductoTalla, Gen_ProductoMedicion.ProductoMedicion,
            (IFNULL(IngresoUnd.cantidad, 0) + IFNULL(IngresoCaja.cantidad, 0) - IFNULL(SalidaCaja.cantidad, 0)
            - IFNULL(SalidaUnd.cantidad, 0) - IFNULL(SalidaVentaUnd.cantidad, 0) - IFNULL(SalidaVentaCaja.cantidad, 0)) AS stock
            FROM Gen_Producto
            INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
            INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
            LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla
            LEFT JOIN $strIngresoUnd AS IngresoUnd ON Gen_Producto.IdProducto = IngresoUnd.IdProducto
            LEFT JOIN $strSalidaUnd AS SalidaUnd ON Gen_Producto.IdProducto = SalidaUnd.IdProducto
            LEFT JOIN $strIngresoCaja AS IngresoCaja ON Gen_Producto.IdProducto = IngresoCaja.IdProducto
            LEFT JOIN $strSalidaCaja AS SalidaCaja ON Gen_Producto.IdProducto = SalidaCaja.IdProducto
            LEFT JOIN $strSalidaVentaUnd AS SalidaVentaUnd ON Gen_Producto.IdProducto = SalidaVentaUnd.IdProducto
            LEFT JOIN $strSalidaVentaCaja AS SalidaVentaCaja ON Gen_Producto.IdProducto = SalidaVentaCaja.IdProducto ";
    } else {
        $select = "SELECT Gen_Producto.*, Gen_ProductoCategoria.ProductoCategoria, Gen_ProductoMarca.ProductoMarca, Gen_ProductoTalla.ProductoTalla, Gen_ProductoMedicion.ProductoMedicion,
            COALESCE(Gen_Producto_Totales.TotalCompras,0) - COALESCE(Gen_Producto_Totales.TotalVentas,0) AS stock
            FROM Gen_Producto 
            INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
            INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
            LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla 
            JOIN Gen_Producto_Totales 
            ON Gen_Producto_Totales.IdProducto = Gen_Producto.IdProducto
            AND Gen_Producto_Totales.IdAlmacen = $idAlmacen
            ";

        // IFNULL(IngresoUnd.cantidad, 0) AS StockIngresoUnd,
        // IFNULL(SalidaUnd.cantidad, 0) AS StockSalidaUnd,
        // IFNULL(SalidaVentaUnd.cantidad, 0) AS StockSalidaVentaUnd,
        // IFNULL(SalidaVentaCaja.cantidad, 0) AS StockSalidaVentaCaja,

        // (IFNULL(IngresoUnd.cantidad, 0) + IFNULL(IngresoCaja.cantidad, 0)  - IFNULL(SalidaCaja.cantidad, 0) - IFNULL(SalidaUnd.cantidad, 0) - IFNULL(SalidaVentaUnd.cantidad,0) - IFNULL(SalidaVentaCaja.cantidad,0)) AS stock

        // LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla 
        // LEFT JOIN $strIngresoUnd AS IngresoUnd ON Gen_Producto.IdProducto = IngresoUnd.IdProducto
        // LEFT JOIN $strSalidaUnd AS SalidaUnd ON Gen_Producto.IdProducto = SalidaUnd.IdProducto
        // LEFT JOIN $strIngresoCaja AS IngresoCaja ON Gen_Producto.IdProducto = IngresoCaja.IdProducto
        // LEFT JOIN $strSalidaCaja AS SalidaCaja ON Gen_Producto.IdProducto = SalidaCaja.IdProducto
        // LEFT JOIN $strSalidaVentaUnd AS SalidaVentaUnd ON Gen_Producto.IdProducto = SalidaVentaUnd.IdProducto 
        // LEFT JOIN $strSalidaVentaCaja AS SalidaVentaCaja ON Gen_Producto.IdProducto = SalidaVentaCaja.IdProducto
    }



    if ($request->getParam('filter')) {
        $filter = $request->getParam('filter');
        if (is_array($filter)) {
        $conditions = [];
        
        if (!empty($filter['producto'])) $conditions[] = "Gen_Producto.Producto LIKE '%" . addslashes($filter['producto']) . "%'";
        if (!empty($filter['codigo']))   $conditions[] = "Gen_Producto.CodigoBarra LIKE '%" . addslashes($filter['codigo']) . "%'";
        if (!empty($filter['color']))    $conditions[] = "Gen_Producto.Color LIKE '%" . addslashes($filter['color']) . "%'";
        if (!empty($filter['marca']))    $conditions[] = "Gen_ProductoMarca.ProductoMarca LIKE '%" . addslashes($filter['marca']) . "%'";
        if (!empty($filter['modelo']))   $conditions[] = "Gen_Producto.ProductoModelo LIKE '%" . addslashes($filter['modelo']) . "%'";
        if (!empty($filter['categoria'])) $conditions[] = "Gen_ProductoCategoria.ProductoCategoria LIKE '%" . addslashes($filter['categoria']) . "%'";
        if (!empty($filter['genero']))   $conditions[] = "Gen_Producto.Genero LIKE '%" . addslashes($filter['genero']) . "%'";
        if (!empty($filter['botapie']))  $conditions[] = "Gen_Producto.Botapie LIKE '%" . addslashes($filter['botapie']) . "%'";

        if (!empty($conditions)) {
            $select .= " WHERE " . implode(" AND ", $conditions);
        } else {
            $select .= " WHERE 1=1 ";
        }
        } else {
            $select .= " WHERE Gen_Producto.Producto LIKE '%" . $filter .
                "%' OR Gen_Producto.CodigoBarra LIKE '%" . $filter .
                "%' OR Gen_Producto.Color LIKE '%" . $filter .
                "%' OR Gen_ProductoMarca.ProductoMarca LIKE '%" . $filter .
                "%' OR Gen_Producto.ProductoModelo LIKE '%" . $filter .
                "%' OR Gen_ProductoCategoria.ProductoCategoria LIKE '%" . $filter .
                "%' ";
        }
    } else {
        $select .= " WHERE Gen_Producto.Producto LIKE '%" . $request->getParam('q') . "%' ";
    }

    if ($request->getParam('idProductos')) {
        // mandar array de IDS 
        $idProductos = $request->getParam('idProductos');
        $select .= " AND Gen_Producto.IdProducto IN (" . implode(',', $idProductos) . ")";
    }

    if (isset($request->getparam('filter')['minimo']) && !$request->getParam('sumaStock') && !$request->getParam('sumaValorizado')) {
        $filterMinimo = $request->getParam('filter')['minimo'];
        if ($filterMinimo) {
            $select .= " HAVING stock <= Gen_Producto.StockMinimo";
        }
    }

    if (isset($request->getParam('filter')['stock']) && !$request->getParam('sumaStock') && !$request->getParam('sumaValorizado')) {
        $filterStock = $request->getParam('filter')['stock'];
        if ($filterStock == 'mayor') {
            $select .= " HAVING stock > 0";
        }
        if ($filterStock == 'menor') {
            $select .= " HAVING stock <= 0";
        }
    }


    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }


    if ($request->getParam('limit')) {
        $limit = $request->getParam('limit');
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }
    // print_r($select);exit();
    $this->db->query('SET SQL_BIG_SELECTS=1');
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->get('/movimientos/numero', function (Request $request, Response $response, array $args) {
    $serie = $request->getParam('serie');
    $idMovimientoTipo = $request->getParam('idMovimientoTipo');

    $select = "SELECT Lo_Movimiento.IdMovimientoTipo, Lo_MovimientoTipo.Tipo, Lo_Movimiento.Serie, Lo_Movimiento.Numero, (Lo_Movimiento.Numero+1) as NuevoNumero
        FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        WHERE Lo_Movimiento.Serie = '$serie' AND Lo_Movimiento.IdMovimientoTipo = '$idMovimientoTipo'
        ORDER BY Lo_Movimiento.Numero DESC
        LIMIT 1";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/movimientos/lista', function (Request $request, Response $response) {
    $select = "SELECT CONCAT(Lo_Movimiento.Serie, '-', Lo_Movimiento.Numero) AS NroComprobante, Lo_Movimiento.* 
        FROM Lo_Movimiento";
    $select .= " WHERE CONCAT(Lo_Movimiento.Serie, '-', Lo_Movimiento.Numero) LIKE '%" . $request->getParam('q') . "%'  ";
    $select .= " LIMIT 10";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});


$app->get('/movimiento/detalle/comprobante', function (Request $request, Response $response) {
    $hashMovimiento = $request->getParam('hashMovimiento');

    $select = "SELECT Lo_MovimientoDetalle.hashMovimiento,Lo_MovimientoDetalle.IdProducto,
    Lo_MovimientoDetalle.Cantidad AS cantidad,Gen_Producto.CodigoBarra,
    Lo_MovimientoDetalle.Precio AS precio, Lo_MovimientoDetalle.Precio AS PrecioContado, Lo_Movimiento.IdProveedor,
    Gen_ProductoMarca.ProductoMarca, Gen_ProductoMedicion.ProductoMedicion,
    Gen_Producto.Producto,
    Gen_Producto.ProductoDesc,
    Gen_Producto.ProductoDescCorto,
    Gen_Producto.CodigoBarra,
    Gen_Producto.Codigo,
    Gen_Producto.Dosis,
    Gen_Producto.PrecioPorMayor,
    Gen_Producto.StockPorMayor,
    Gen_Producto.StockMinimo,
    Gen_Producto.Anulado,
    Gen_Producto.FechaReg,
    Gen_Producto.Hash,
    Gen_Producto.ControlaStock,
    Gen_Producto.PrecioCosto,
    Gen_Producto.VentaEstrategica,
    Gen_Producto.IdBloque,
    Gen_Producto.EsPadre
    FROM Lo_MovimientoDetalle  
    INNER JOIN Lo_Movimiento ON Lo_MovimientoDetalle.hashMovimiento = Lo_Movimiento.Hash 
    INNER JOIN Gen_Producto ON Lo_MovimientoDetalle.IdProducto = Gen_Producto.IdProducto 
    INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
    INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
    WHERE Lo_MovimientoDetalle.hashMovimiento=$hashMovimiento  ORDER BY Lo_MovimientoDetalle.hashMovimiento DESC ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});


// VENTAS
//API ESTADISTICOS
$app->get('/ventas/estadistica/dia', function (Request $request, Response $response) {
    $anio = $request->getParam('anio');
    $mes = $request->getParam('mes');
    $idAlmacen = $request->getParam('idAlmacen');
    $select = "SELECT YEAR(Ve_DocVenta.FechaDoc) as Anio, MONTH(Ve_DocVenta.FechaDoc) as Mes,DAY(Ve_DocVenta.FechaDoc) as Dia, DAYNAME(Ve_DocVenta.FechaDoc) as DiaNombre, SUM(ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2)) AS Total FROM Ve_DocVenta
    INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta = Ve_DocVentaDet.IdDocVenta
    INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
    WHERE " . ($idAlmacen != 0 && $idAlmacen != null ? " Ve_DocVenta.IdAlmacen = $idAlmacen AND " : "") . " YEAR(Ve_DocVenta.FechaDoc) = $anio AND MONTH(Ve_DocVenta.FechaDoc) = $mes
    AND COALESCE(Ve_DocVenta.Anulado, 0) = 0
    AND Ve_DocVentaTipoDoc.CodSunat <> '07'
    GROUP BY DAY(Ve_DocVenta.FechaDoc)
    ORDER BY Ve_DocVenta.FechaDoc ASC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/ventas/estadistica/mes', function (Request $request, Response $response) {
    $anio = $request->getParam('anio');
    $idAlmacen = $request->getParam('idAlmacen');
    $select = "SELECT YEAR(Ve_DocVenta.FechaDoc) as Anio, MONTH(Ve_DocVenta.FechaDoc) as Mes, SUM(ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2)) AS Total FROM Ve_DocVenta
    INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta = Ve_DocVentaDet.IdDocVenta
    INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
    WHERE " . ($idAlmacen != 0 && $idAlmacen != null ? " Ve_DocVenta.IdAlmacen = $idAlmacen AND " : "") . " YEAR(Ve_DocVenta.FechaDoc) = $anio
    AND COALESCE(Ve_DocVenta.Anulado, 0) = 0
    AND Ve_DocVentaTipoDoc.CodSunat <> '07'
    GROUP BY MONTH(Ve_DocVenta.FechaDoc)
    ORDER BY Ve_DocVenta.FechaDoc ASC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->get('/ventas/estadistica/rango', function (Request $request, Response $response) {
    $fechaDesde = $request->getParam('fechaDesde');
    $fechaHasta = $request->getParam('fechaHasta');
    $idAlmacen = $request->getParam('idAlmacen');
    $select = "SELECT YEAR(Ve_DocVenta.FechaDoc) as Anio, MONTH(Ve_DocVenta.FechaDoc) as Mes,DAY(Ve_DocVenta.FechaDoc) as Dia, DAYNAME(Ve_DocVenta.FechaDoc) as DiaNombre, SUM(ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2)) AS Total FROM Ve_DocVenta
    INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta = Ve_DocVentaDet.IdDocVenta
    INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
    WHERE " . ($idAlmacen != 0 && $idAlmacen != null ? " Ve_DocVenta.IdAlmacen = $idAlmacen AND " : "") . " Ve_DocVenta.FechaDoc BETWEEN '$fechaDesde 00:00:00' AND '$fechaHasta 23:59:59'
    AND COALESCE(Ve_DocVenta.Anulado, 0) = 0
    AND Ve_DocVentaTipoDoc.CodSunat <> '07'
    GROUP BY DAY(Ve_DocVenta.FechaDoc),MONTH(Ve_DocVenta.FechaDoc)
    ORDER BY Ve_DocVenta.FechaDoc ASC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});



$app->get('/ventas/tipos', function (Request $request, Response $response, array $args) {
    //$select = $this->db->select()->from('Ve_DocVentaTipoDoc')->whereLike('TipoDoc', '%' . $request->getParam('q') . '%');


    $select = "SELECT * FROM Ve_DocVentaTipoDoc WHERE Anulado=0 AND TipoDoc LIKE '%" . $request->getParam('q') . "%' ORDER BY Orden ASC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/ventas/busqueda', function (Request $request, Response $response) {
    $select = "SELECT Ve_DocVentaTipoDoc.CodSunat, CONCAT(Ve_DocVenta.Serie, '-', Ve_DocVenta.Numero) AS NroComprobante, 
        Ve_DocVenta.idDocVenta AS IdDocVenta FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc";
    $select .= " WHERE Ve_DocVentaTipoDoc.CodSunat IN ('01', '03') AND Ve_DocVenta.Anulado=0 AND
    CONCAT(Ve_DocVenta.Serie, '-', Ve_DocVenta.Numero) LIKE '%" . $request->getParam('q') . "%'  ";
    $select .= " LIMIT 10";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});

$app->get('/ventas/tipos/electronicos', function (Request $request, Response $response, array $args) {
    //$select = $this->db->select()->from('Ve_DocVentaTipoDoc')->whereLike('TipoDoc', '%' . $request->getParam('q') . '%');


    $select = "SELECT * FROM Ve_DocVentaTipoDoc WHERE TipoDoc LIKE '%" . $request->getParam('q') . "%' AND EsElectronico=1 ORDER BY Orden ASC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/ventas/puntos', function (Request $request, Response $response, array $args) {
    $select = $this->db->select()->from('Ve_DocVentaPuntoVenta')->whereLike('PuntoVenta', '%' . $request->getParam('q') . '%');
    $stmt = $select->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/ventas/descuento/[{puntos}]', function (Request $request, Response $response, array $args) {
    $puntos = $request->getAttribute('puntos', 0);

    $select = "SELECT * FROM Ve_DocVentaDescuento WHERE IdDocVentaDescuento = 1";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();
    $data['descuento'] = $data['ValorPunto'] * $puntos;

    return $response->withJson($data);
});

$app->get('/ventas/descuento', function (Request $request, Response $response, array $args) {
    $select = $this->db->select()->from('Ve_DocVentaDescuento')->where('IdDocVentaDescuento', '=', 1);
    $stmt = $select->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->post('/ventas/descuento', function (Request $request, Response $response) {
    $id = 1;
    $porCada = $request->getParam('porCada');
    $puntos = $request->getParam('puntos');
    $valorPunto = $request->getParam('valorPunto');

    $update = "UPDATE Ve_DocVentaDescuento SET PorCada=$porCada, Puntos=$puntos, ValorPunto=$valorPunto 
        WHERE IdDocVentaDescuento=$id";

    $stmt = $this->db->prepare($update);
    $updated = $stmt->execute();

    return $response->withJson(array(
        "IdDocVentaDescuento" => $id,
        "PorCada" => $porCada,
        "Puntos" => $puntos,
        "ValorPunto" => $valorPunto
    ));
});


$app->get('/ventas/numero', function (Request $request, Response $response, array $args) {
    $idPuntoVenta = $request->getParam('idPuntoVenta');
    $idTipoDoc = $request->getParam('idTipoDoc');

    // Obtener Serie
    $selectSerie = "SELECT Serie FROM Ve_DocVentaPuntoVentaDet WHERE IdDocVentaPuntoVenta=$idPuntoVenta AND IdDocVentaTipoDoc=$idTipoDoc";

    $stmt = $this->db->query($selectSerie);
    $stmt->execute();
    $selectSerie = $stmt->fetch();
    $serie = $selectSerie['Serie'];

    // Obtener siguiente Numero
    $selectNumero = "SELECT Numero + 1 AS NuevoNumero FROM Ve_DocVenta 
        WHERE IdDocVentaPuntoVenta=$idPuntoVenta AND IdTipoDoc=$idTipoDoc 
        ORDER BY Numero DESC LIMIT 1";
    $stmt = $this->db->query($selectNumero);
    $stmt->execute();
    $selectNumero = $stmt->fetch();
    $numero = $selectNumero['NuevoNumero'];
    require_once __DIR__ . '/factura-igv.php';
    $numeroFactura = imcFacturaIgvNumero($this->db, $idTipoDoc, $serie);
    if ($numeroFactura !== null) $numero = $numeroFactura;
    return $response->withJson(array(
        "serie" => $serie,
        "numero" => $numero ? $numero : 1
    ));
});


$app->get('/ventas/total', function (Request $request, Response $response, array $args) {
    $fechaInicio = $request->getParam('fechaInicio');
    $fechaFin = $request->getParam('fechaFin');
    $idTipoDoc = $request->getParam('idTipoDoc');
    // $fechaInicio = $fechaInicio ? $fechaInicio : getNow('Y') . '-01-01';
    $fechaInicio = $fechaInicio ? $fechaInicio : getNow('Y-m-d');
    $fechaFin = $fechaFin ? $fechaFin : getNow('Y-m-d');

    $select = "SELECT ROUND(SUM((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - Ve_DocVentaDet.Descuento), 2) AS total 
        FROM Ve_DocVentaDet
        INNER JOIN Ve_DocVenta ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta
        WHERE Ve_DocVenta.Anulado=0 AND Ve_DocVenta.FechaDoc BETWEEN CAST('" . $fechaInicio . "' AS DATETIME) AND CONCAT('" . $fechaFin . "',' 23:59:59')";
    //print_r($select);exit();

    $idAlmacen = $request->getParam('idAlmacen');
    if ($idAlmacen) {
        $select .= " AND Ve_DocVenta.IdAlmacen = $idAlmacen ";
    }

    if ($idTipoDoc) {
        $select .= " AND Ve_DocVenta.IdTipoDoc = $idTipoDoc";
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});


$app->get('/ventas/usuario', function (Request $request, Response $response) {
    $usuario = $request->getParam('usuario');
    $fechaInicio = $request->getParam('fechaInicio');
    $fechaFin = $request->getParam('fechaFin');
    $fechaInicio = $fechaInicio ? $fechaInicio : getNow('Y') . '-01-01';
    $fechaFin = $fechaFin ? $fechaFin : getNow('Y-m-d');
    $idTipoDoc = $request->getParam('idTipoDoc');

    $select = "SELECT ROUND(SUM((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - Ve_DocVentaDet.Descuento), 2) AS total FROM Ve_DocVentaDet
    INNER JOIN Ve_DocVenta ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta
    INNER JOIN Seg_Usuario ON Ve_DocVenta.UsuarioReg = Seg_Usuario.Usuario
    WHERE Ve_DocVenta.Anulado=0 AND Seg_Usuario.Usuario = '$usuario' 
    AND Ve_DocVenta.FechaDoc BETWEEN CAST('" . $fechaInicio . "' AS DATETIME) AND CONCAT('" . $fechaFin . "',' 23:59:59')";
    //print_r($select);exit();

    if ($idTipoDoc) {
        $select .= " AND Ve_DocVenta.IdTipoDoc = $idTipoDoc";
    }

    $idAlmacen = $request->getParam('idAlmacen');
    if ($idAlmacen) {
        $select .= " AND Ve_DocVenta.IdAlmacen = $idAlmacen ";
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/ventas/detalle', function (Request $request, Response $response) {
    $idDocVentas = $request->getParam('idDocVentas');

    $select = "SELECT Ve_DocVentaDet.*, Gen_Producto.Producto, 
        ROUND((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - Ve_DocVentaDet.Descuento, 2) AS Subtotal,
        ROUND(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio, 2) AS Total,
        Gen_Producto.CodigoBarra, Gen_ProductoMedicion.Codigo AS CodigoMedicion
        FROM Ve_DocVentaDet 
        INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
        INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
        WHERE IdDocVenta IN (" . implode(',', $idDocVentas) . ")";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/ventas/solonumero', function (Request $request, Response $response, array $args) {
    $serie = $request->getParam('serie');
    $idTipoDoc = $request->getParam('idTipoDoc');

    // Obtener siguiente Numero
    $selectNumero = "SELECT Numero + 1 AS NuevoNumero FROM Ve_DocVenta 
        WHERE IdTipoDoc=$idTipoDoc AND Serie='$serie'
        ORDER BY Numero DESC LIMIT 1";
    $stmt = $this->db->query($selectNumero);
    $stmt->execute();
    $selectNumero = $stmt->fetch();
    $numero = $selectNumero['NuevoNumero'];
    return $response->withJson(array(
        "numero" => $numero ? $numero : 1
    ));
});

$app->get('/ventas/lista/[{idPuntoVenta}]', function (Request $request, Response $response) {
    $idPuntoVenta =  $request->getAttribute('idPuntoVenta', 0);
    $select = "SELECT Ve_DocVentaTipoDoc.CodSunat, CONCAT(Ve_DocVenta.Serie, '-', Ve_DocVenta.Numero) AS NroComprobante, Ve_DocVenta.idDocVenta AS IdDocVenta FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc";
    $select .= " WHERE Ve_DocVentaTipoDoc.EsElectronico=1 AND Ve_DocVentaTipoDoc.CodSunat IN ('01', '03') AND Ve_DocVenta.Anulado=0 AND  Ve_DocVenta.IdDocVentaPuntoVenta = $idPuntoVenta AND
    CONCAT(Ve_DocVenta.Serie, '-', Ve_DocVenta.Numero) LIKE '%" . $request->getParam('q') . "%'  ";
    $select .= " ORDER BY Ve_DocVenta.idDocVenta DESC LIMIT 100";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/venta/detalle/comprobante', function (Request $request, Response $response) {
    $nroComprobante = $request->getParam('NroComprobante');
    $idDocVenta = $request->getParam('idDocVenta');
    $IdDocVentaPuntoVenta = $request->getParam('IdDocVentaPuntoVenta');
    $comprobante = explode('-', $nroComprobante);
    $serie = $comprobante[0];
    $numero = $comprobante[1];

    $select = "SELECT Ve_DocVentaDet.IdDocVentaDet,Ve_DocVentaDet.IdDocVenta,Ve_DocVentaDet.IdProducto,
    Ve_DocVentaDet.Cantidad AS cantidad,
    IFNULL(Ve_DocVentaDet.Descripcion,'') as Descripcion,
    Ve_DocVentaDet.Precio AS precio, Ve_DocVentaDet.Precio as PrecioContado, Ve_DocVentaDet.Descuento AS descuento,Ve_DocVenta.IdCliente,
    Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica,Gen_ProductoMarca.ProductoMarca,Gen_ProductoMedicion.ProductoMedicion,
    Gen_ProductoCategoria.ProductoCategoria,
    Gen_Producto.*,
    0 AS PorcentajeUtilidad,
    Ve_DocVentaDet.Precio AS PrecioCosto,
    Ve_DocVentaDet.PorcentajeLiquidacion,
    Gen_ProductoTalla.ProductoTalla
    FROM Ve_DocVentaDet 
    INNER JOIN Ve_DocVenta ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.IdDocVenta
    INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto 
    INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
    INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
    INNER JOIN Gen_ProductoFormaFarmaceutica ON Gen_Producto.IdProductoFormaFarmaceutica = Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica
    INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
    INNER JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla= Gen_ProductoTalla.IdProductoTalla
    WHERE Ve_DocVenta.idDocVenta=$idDocVenta  ORDER BY Ve_DocVentaDet.IdDocVentaDet DESC ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});

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
$app->get('/ventas', function (Request $request, Response $response) {
    $filtros = '';

    $select = "SELECT Ve_DocVenta.idDocVenta, Ve_DocVenta.FechaDoc, Ve_DocVentaTipoDoc.TipoDoc, Ve_DocVentaTipoDoc.TieneIgv, 
        Ve_DocVenta.Anulado, 
        CONCAT(Ve_DocVenta.Serie, '-', Ve_DocVenta.Numero) as Correlativo,
        Ve_DocVenta.Serie, Ve_DocVenta.Numero, Ve_DocVentaCliente.Cliente, Ve_DocVentaCliente.Email, Ve_DocVentaCliente.Telefono, Ve_DocVenta.UsuarioReg,
        Ve_DocVentaTipoDoc.CodSunat, Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.Direccion,
        Ve_DocVentaTipoDoc.CodigoIgv, Ve_DocVenta.Estado, Ve_DocVenta.Hash_cpe, Ve_DocVenta.Hash_cdr, Ve_DocVenta.Msj_sunat,
        Ve_DocVenta.CodSunatModifica,
        Ve_DocVenta.NroComprobanteModifica,
        Ve_DocVenta.NotaIdMotivo,
        Ve_DocVenta.NotaDescMotivo,
        Ve_DocVenta.Observacion, Ve_DocVenta.Placa,
        Ve_DocVenta.EsCredito, Ve_DocVenta.FechaCredito,
        IFNULL((SELECT SUM(ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2)) FROM Ve_DocVentaDet WHERE Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta), 0 ) AS Total
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        LEFT JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente";

    $select .= " WHERE CONCAT(Ve_DocVenta.Serie, '-', Ve_DocVenta.Numero) LIKE '%" . $request->getParam('q') . "%' ";


    $idAlmacen = $request->getParam('idAlmacen');
    if ($idAlmacen) {
        $filtros .= " AND Ve_DocVenta.IdAlmacen =  $idAlmacen";
    }

    $filter = ($request->getParam('filter')) ? $request->getParam('filter') : [];
    if (!isset($filter['fechaInicio']) && is_array($filter)) {
        //$filter['fechaInicio'] = getNow('Y') . '-01-01';
        $filter['fechaInicio'] = getNow('Y-m-d');
    }

    if (!isset($filter['fechaFin']) && is_array($filter)) {
        $filter['fechaFin'] = getNow('Y-m-d');
    }

    $noFecha = $request->getParam('noFecha');
    if ($noFecha == 1 && isset($filter['fechaInicio']) && isset($filter['fechaFin'])) {
        unset($filter['fechaInicio']);
        unset($filter['fechaFin']);
    }

    if ($filter) {
        if (is_array($filter)) {

            if (isset($filter['vendedor']) && $filter['vendedor']) $filtros .= " AND Ve_DocVenta.UsuarioReg = '" . $filter['vendedor'] . "'";
            if (isset($filter['declarado'])) $filtros .= " AND Ve_DocVentaTipoDoc.VaRegVenta = " . $filter['declarado'];
            if (isset($filter['fechaInicio']) && isset($filter['fechaFin'])) $filtros .= " AND Ve_DocVenta.FechaDoc BETWEEN CAST('" . $filter['fechaInicio'] . "' AS DATETIME) AND CONCAT('" . $filter['fechaFin'] . "',' 23:59:59')";
        } else {
            $select .= " AND (Ve_DocVenta.idDocVenta LIKE '%" . $filter .
                "%' OR CONCAT(Ve_DocVenta.Serie, '-', Ve_DocVenta.Numero) LIKE '%" . $filter .
                "%' OR Ve_DocVentaTipoDoc.TipoDoc LIKE '%" . $filter .
                "%' OR Ve_DocVentaCliente.Cliente LIKE '%" . $filter .
                "%' OR Ve_DocVentaCliente.DniRuc LIKE '%" . $filter .
                "%' )";
        }
    }

    $idTipoDoc = $request->getParam('idTipoDoc');
    if ($idTipoDoc) {
        $filtros .= " AND Ve_DocVenta.IdTipoDoc IN (" . implode(',', $idTipoDoc) . ")";
    }

    $estado = $request->getParam('estado');
    if ($estado || $estado == "0") {
        $filtros .= " AND Ve_DocVenta.Estado=$estado";
    }

    $anulado = $request->getParam('anulado');
    if (!is_null($anulado) && $anulado != '' && ($anulado == 0 || $anulado == 1)) {
        $filtros .= " AND Ve_DocVenta.Anulado='$anulado'";
    }

    $select .= $filtros;

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }


    if ($request->getParam('limit')) {
        $limit = $request->getParam('limit');
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    // print_r($select);exit();
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->get('/ventas/count', function (Request $request, Response $response) {
    try {

        $idAlmacen = $request->getParam('idAlmacen');
        $select = "SELECT COUNT(*) as total FROM Ve_DocVenta WHERE TRUE";
        if (isset($idAlmacen)) {
            $select .= " Ve_DocVenta.IdAlmacen = $idAlmacen";
        }

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['err' => $e->getMessage()], 500);
        //throw $th;
    }
});


$app->post('/ventas', function (Request $request, Response $response) {
    $esVentaDesdeApp = $request->getHeaderLine('X-Ventas-App') === '1';
    // Bloqueo de seguridad: impide registrar ventas cuando la vigencia finalizó.
    $stmtVigencia = $this->db->query("SELECT MetaKey, MetaValue FROM Gen_EmpresaConfig WHERE IdEmpresa=1 AND MetaKey IN ('vigencia_fecha_ultimo_pago','vigencia_hasta','vigencia_estado_pago','ventas_app_bloqueo_activo','ventas_app_fecha_limite','ventas_app_estado_pago','sistema_zona_horaria') ORDER BY IdEmpresaConfig ASC");
    $stmtVigencia->execute();
    $vigenciaConfig = array();
    foreach ($stmtVigencia->fetchAll() as $vigenciaFila) {
        $vigenciaConfig[$vigenciaFila['MetaKey']] = $vigenciaFila['MetaValue'];
    }
    $fechaLimitePagoApi = trim((string) ($vigenciaConfig['vigencia_fecha_ultimo_pago'] ?? ''));
    $vigenciaHastaApi = trim((string) ($vigenciaConfig['vigencia_hasta'] ?? ''));
    $fechaBloqueoApi = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaLimitePagoApi) ? $fechaLimitePagoApi : $vigenciaHastaApi;
    $vigenciaEstadoApi = strtoupper(trim((string) ($vigenciaConfig['vigencia_estado_pago'] ?? 'PENDIENTE')));
    if ($esVentaDesdeApp) {
        $bloqueoAppApi = ($vigenciaConfig['ventas_app_bloqueo_activo'] ?? '0') === '1';
        $fechaBloqueoApi = trim((string) ($vigenciaConfig['ventas_app_fecha_limite'] ?? ''));
        $vigenciaEstadoApi = strtoupper(trim((string) ($vigenciaConfig['ventas_app_estado_pago'] ?? 'PAGADO')));
    } else {
        $bloqueoAppApi = true;
    }
    $zonaVigenciaApi = trim((string) ($vigenciaConfig['sistema_zona_horaria'] ?? 'America/Lima'));
    try {
        $hoyVigenciaApi = (new DateTime('today', new DateTimeZone($zonaVigenciaApi)))->format('Y-m-d');
    } catch (Throwable $e) {
        $hoyVigenciaApi = (new DateTime('today', new DateTimeZone('America/Lima')))->format('Y-m-d');
    }
    if ($bloqueoAppApi && $vigenciaEstadoApi !== 'PAGADO' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaBloqueoApi) && $fechaBloqueoApi <= $hoyVigenciaApi) {
        return $response->withJson(array(
            'error' => 'Punto de venta bloqueado: se cumplio la fecha limite de pago.',
            'vigenciaVencida' => true,
            'fechaLimitePago' => $fechaBloqueoApi
        ), 423);
    }

    $vendedor = 'xx';
    if (isset($_SESSION['user'])) {
        $vendedor = $_SESSION['user'];
    }

    // START INSERTAR NUEVA VENTA
    $idDocVentaPuntoVenta = $request->getParam('puntoVenta')['IdDocVentaPuntoVenta'];
    $idCliente = $request->getParam('cliente')['IdCliente'];
    $idTipoDoc = $request->getParam('tipoVenta')['IdTipoDoc'];
    $codSunat = $request->getParam('tipoVenta')['CodSunat'];
    $idAlmacen = $request->getParam('almacen')['IdAlmacen'];
    // $serie = $request->getParam('Serie'); 
    // $numero = $request->getParam('Numero');
    $anulado = 0;
    $usuarioReg = isset($request->getParam('vendedor')['Usuario']) ? $request->getParam('vendedor')['Usuario'] : $vendedor;
    $pagoCon = $request->getParam('PagoCon') == '' || $request->getParam('PagoCon') == null ? 0 : $request->getParam('PagoCon');

    $codSunatModifica = $request->getParam('CodSunatModifica');
    $nroComprobanteModifica = $request->getParam('NroComprobanteModifica');
    $notaIdMotivo = $request->getParam('NotaIdMotivo');
    $notaDescMotivo = $request->getParam('NotaDescMotivo');

    $esCredito = $request->getParam('EsCredito');
    $fechaCredito = $request->getParam('FechaCredito');
    $campoDireccion = $request->getParam('CampoDireccion');
    $esOrganizacion = (int) ($request->getParam('EsOrganizacion') == 1);
    $nombreOrganizacion = $esOrganizacion
        ? mb_strtoupper(trim((string) $request->getParam('nombreOrganizacion')), 'UTF-8')
        : '';
    if ($esOrganizacion && $nombreOrganizacion === '') {
        return $response->withJson(array('error' => 'Ingrese el nombre de la organización.'), 422);
    }
    $nombreOrganizacionSql = str_replace("'", "''", mb_substr($nombreOrganizacion, 0, 150));
    $observacion = trim((string) $request->getParam('Observacion'));
    $observacion = mb_substr($observacion, 0, 500, 'UTF-8');
    $observacionSql = str_replace("'", "''", $observacion);
    $placa = trim((string) $request->getParam('Placa'));
    $placa = preg_replace('/[^A-Za-z0-9-]/', '', $placa);
    $placa = mb_substr($placa, 0, 20, 'UTF-8');
    $placaSql = str_replace("'", "''", $placa);

    //Obtener almacen y punto según vendedor
    $selectUsuario = "SELECT * FROM Seg_Usuario WHERE Usuario = '$usuarioReg'";
    $stmtUser = $this->db->query($selectUsuario);
    $user = $stmtUser->fetch();
    $stmtUser->execute();

    if (!empty($user)) {
        $idDocVentaPuntoVenta = $user['IdDocVentaPuntoVenta'];
        $idAlmacen = $user['IdAlmacen'];
    }

    // OBTENER LA SERIE
    //$selectSerie = "SELECT Serie FROM Ve_DocVentaPuntoVentaDet
    //    WHERE IdDocVentaPuntoVenta=$idDocVentaPuntoVenta AND IdDocVentaTipoDoc=$idTipoDoc
    //    LIMIT 1";
    //$stmt = $this->db->query($selectSerie);
    //$stmt->execute();
    //$selectSerie = $stmt->fetch();
    //$serie = $selectSerie['Serie'] ? $selectSerie['Serie'] : '';

    $selectSerie = "SELECT Serie FROM Ve_DocVentaPuntoVentaDet 
    WHERE IdDocVentaPuntoVenta=$idDocVentaPuntoVenta AND IdDocVentaTipoDoc=$idTipoDoc";

    $stmt = $this->db->query($selectSerie);
    $stmt->execute();
    $selectSerie = $stmt->fetch();
    $serie = $selectSerie['Serie'] ? $selectSerie['Serie'] : $request->getParam('Serie');

    // OBTENER EL SIGUIENTE NUMERO
    if ($codSunat == '07' || $codSunat == '08') { // 07 nota de credito, 08 nota de debito

        if ($codSunatModifica == '01') {
            $serie = str_replace("_", "F", $serie);
        }

        if ($codSunatModifica == '03') {
            $serie = str_replace("_", "B", $serie);
        }

        $selectNumero = "SELECT Numero + 1 AS NuevoNumero FROM Ve_DocVenta
            WHERE IdTipoDoc=$idTipoDoc AND Serie='$serie'
            ORDER BY Numero DESC LIMIT 1";
    } else {
        $selectNumero = "SELECT Numero + 1 AS NuevoNumero FROM Ve_DocVenta
            WHERE IdDocVentaPuntoVenta=$idDocVentaPuntoVenta AND IdTipoDoc=$idTipoDoc
            ORDER BY Numero DESC LIMIT 1";
    }

    $stmt = $this->db->query($selectNumero);
    $stmt->execute();
    $selectNumero = $stmt->fetch();
    $numero = $selectNumero['NuevoNumero'] ? $selectNumero['NuevoNumero'] : 1;
    require_once __DIR__ . '/factura-igv.php';
    $numeroFactura = imcFacturaIgvNumero($this->db, $idTipoDoc, $serie);
    if ($numeroFactura !== null) $numero = $numeroFactura;
    $productos = $request->getParam('productos');

    $insert = "INSERT INTO Ve_DocVenta (IdDocVentaPuntoVenta,IdCliente,IdTipoDoc,IdAlmacen,Serie,Numero,FechaDoc,Anulado,FechaReg,UsuarioReg,Hash, EsCredito, FechaCredito, PagoCon, CampoDireccion, CodSunatModifica, NroComprobanteModifica, NotaIdMotivo, NotaDescMotivo, EsOrganizacion, NombreOrganizacion, Observacion, Placa)
        VALUES ($idDocVentaPuntoVenta, $idCliente, $idTipoDoc, $idAlmacen, '$serie', '$numero', '" . getNow() . "', $anulado, '" . getNow() . "', '$usuarioReg', UNIX_TIMESTAMP(), $esCredito, '$fechaCredito', '$pagoCon', '$campoDireccion', '$codSunatModifica', '$nroComprobanteModifica', '$notaIdMotivo', '$notaDescMotivo', $esOrganizacion, '$nombreOrganizacionSql', :observacion, :placa)";

    $stmt = $this->db->prepare($insert);
    $inserted = $stmt->execute(array(':observacion' => $observacion, ':placa' => $placa));
    $idDocVenta = $this->db->lastInsertId();
    // END INSERTAR NUEVA VENTA


    // START INSERTAR VENTA DETALLE

    $descuentoTotal = 0;
    foreach ($productos as $producto) {

        $idProducto = $producto['IdProducto'];
        $cantidad = $producto['cantidad'];
        $precio = $producto['precio'];
        $precioCosto = $producto['PrecioCosto'];
        $descuento = $producto['descuento'] ? $producto['descuento'] : 0;
        $porcentajeLiquidacion = $producto['PorcentajeLiquidacion'] ? $producto['PorcentajeLiquidacion'] : 0;
        $descripcion = isset($producto['Descripcion']) ? $producto['Descripcion'] : '';
        $CantidadxPresentacion = null;

        $Presentacion = isset($producto['presentacion']) && trim((string) $producto['presentacion']) !== ''
            ? $producto['presentacion']
            : 'UNIDAD';
        if ($Presentacion != 'UNIDAD') {
            $cantidadPresentacion = isset($producto['CantidadPresentacion']) && (float) $producto['CantidadPresentacion'] > 0
                ? (float) $producto['CantidadPresentacion']
                : 1;
            $cantidad = $cantidadPresentacion * $producto['cantidad'];
            $CantidadxPresentacion = $cantidadPresentacion;
        }




        $insert = $this->db->insert(array(
            'IdDocVenta',
            'IdProducto',
            'Cantidad',
            'Precio',
            'PrecioCosto',
            'Descuento',
            'PorcentajeLiquidacion',
            'Descripcion',
            'Presentacion',
            'CantidadxPresentacion'
        ))
            ->into('Ve_DocVentaDet')
            ->values(array(
                $idDocVenta,
                $idProducto,
                $cantidad,
                $precio,
                $precioCosto,
                $descuento,
                $porcentajeLiquidacion,
                $descripcion,
                $Presentacion,
                $CantidadxPresentacion
            ));

        if ($codSunat == '07') {
            // $arrayMotivo = ['01', '02', '06', '07'];
            // if (!in_array($NotaIdMotivo, $arrayMotivo)) {
            # code...
            $dml = "UPDATE Gen_Producto_Totales SET TotalVentas=TotalVentas-$cantidad WHERE IdProducto=$idProducto AND IdAlmacen=$idAlmacen";
            $stmt = $this->db->prepare($dml);
            $updated = $stmt->execute();
            // }
        } else {
            $dml = "UPDATE Gen_Producto_Totales SET TotalVentas=TotalVentas+$cantidad WHERE IdProducto=$idProducto AND IdAlmacen=$idAlmacen";
            $stmt = $this->db->prepare($dml);
            $updated = $stmt->execute();
        }

        $insert->execute();
        $descuentoTotal += $descuento;
    }
    // END VENTA DETALLE
    if (!empty($ids)) {
        $listaIds = implode(',', $ids);
        $updateStockMasivo = "
        UPDATE Gen_Producto_Totales 
        SET TotalVentas = CASE 
            $casos
            ELSE TotalVentas 
        END
        WHERE IdAlmacen = $idAlmacen AND IdProducto IN ($listaIds)";

        $this->db->prepare($updateStockMasivo)->execute();
    }
    // START INSERTAR PAGO DETALLE
    $pagos = $request->getParam('pago');
    if (!$esCredito) {
        foreach ($pagos as $pago) {
            if ($pago['monto'] > 0) {
                $idMetodoPago = $pago['IdMetodoPago'];
                $importe = $pago['monto'];
                $nroTarjeta = isset($pago['descripcion']) ? $pago['descripcion'] : '';
                $insert = $this->db->insert(array('IdDocVenta', 'IdMetodoPago', 'Importe', 'NroTarjeta'))
                    ->into('Ve_DocVentaMetodoPagoDet')
                    ->values(array($idDocVenta, $idMetodoPago, $importe, $nroTarjeta));
                $insert->execute();
            }
        }
    }
    // END PAGO DETALLE

    // START AÑADIR PUNTOS CLIENTE
    $puntosAplicados = $request->getParam('puntosAplicados');
    $totalCart = $request->getParam('totalCart');
    if ($codSunat != '07' && $codSunat != '08') {
        if (!$descuentoTotal) { // quitar condicional si siempre se otorgarán puntos
        // actualizar si la venta se pago en su totalidad, sin descuentos
            $select = "SELECT * FROM Ve_DocVentaDescuento WHERE IdDocVentaDescuento=1";
            $stmt = $this->db->query($select);
            $stmt->execute();
            $tablaDescuento = $stmt->fetch();

            if ($tablaDescuento['PorCada'] && $totalCart >= $tablaDescuento['PorCada']) {
                $puntosAdicionales = ($totalCart / $tablaDescuento['PorCada']) * $tablaDescuento['Puntos'];
                $update = "UPDATE Ve_DocVentaCliente SET Puntos=Puntos+$puntosAdicionales WHERE IdCliente=$idCliente";
                $stmt = $this->db->prepare($update);
                $updated = $stmt->execute();
            }
        }
        if ($puntosAplicados) {
            $update = "UPDATE Ve_DocVentaCliente SET Puntos=Puntos-$puntosAplicados WHERE IdCliente=$idCliente";
            $stmt = $this->db->prepare($update);
            $updated = $stmt->execute();
        }
    }
    // END PUNTOS CLIENTE

    // return $response->withJson(array('insertId' => $nroTarjeta));

    $envioSunatAutomatico = false;
    $envioSunatMensaje = 'Comprobante guardado para envío automático';
    try {
        if (EMISION_AUTO == '1' && isset($_SERVER['HTTP_HOST'])) {
            $selectDocVenta = "SELECT Ve_DocVenta.Serie, Ve_DocVenta.Numero, Ve_DocVentaTipoDoc.CodSunat, Ve_DocVentaTipoDoc.EsElectronico FROM Ve_DocVenta 
            INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc WHERE idDocVenta = $idDocVenta";
            $stmt = $this->db->query($selectDocVenta);
            $stmt->execute();
            $selectDocVentaData = $stmt->fetch();
            if ($selectDocVentaData['EsElectronico'] == 1 && in_array($selectDocVentaData['CodSunat'], array('01', '03'))) {
                $dataEmision = array(
                    'Id' => $idDocVenta,
                    'Ruc' => NRO_DOCUMENTO_EMPRESA,
                    'Url' => 'http://' . $_SERVER['HTTP_HOST'],
                    'Serie' => $selectDocVentaData['Serie'],
                    'Numero' => $selectDocVentaData['Numero'],
                    'CodSunat' => $selectDocVentaData['CodSunat'],
                    'EsElectronico' => $selectDocVentaData['EsElectronico'],
                );
                $headers = array(
                    "Content-Type: application/json; charset=UTF-8",
                    "Cache-Control: no-cache",
                    "Pragma: no-cache"
                );
                $ch = curl_init();
                $ch = curl_init("https://admin.factura.vip:5000/api/facturacion/sistema/comprobante/agregar");

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
                curl_setopt($ch, CURLOPT_TIMEOUT, 8);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataEmision));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $resp = curl_exec($ch);
                $err = curl_error($ch);
                $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $envioSunatAutomatico = $resp !== false && $err === '' && $httpCode >= 200 && $httpCode < 300;
                $envioSunatMensaje = $envioSunatAutomatico
                    ? 'Comprobante enviado automáticamente a SUNAT'
                    : 'Comprobante pendiente; se reintentará automáticamente';
            }
        }
    } catch (Exception $e) {
        $envioSunatMensaje = 'Comprobante pendiente; se reintentará automáticamente';
    } finally {
        $data = array(
            'insertId' => $idDocVenta,
            'envioSunatAutomatico' => $envioSunatAutomatico,
            'envioSunatMensaje' => $envioSunatMensaje
        );
        return $response->withJson($data);
    }
});

//Reporte Utilidad Bruta SbVe_ReporteUtilidadBruta
$app->get('/reporte/utlidadbruta', function (Request $request, Response $response, array $args) {
    try {


        $fechaInicio = $request->getParam('fechaInicio');
        $fechaFin = $request->getParam('fechaFin');

        $select = "SELECT
	Gen_Producto.IdProducto,
	Gen_Producto.Producto,
	SUM(Ve_DocVentaDet.Cantidad) AS Cantidad,
    ProductoModelo,ProductoMarca,ProductoTalla,Color,Genero,Botapie,
    Gen_ProductoTalla.ProductoTalla,Gen_ProductoMarca.ProductoMarca,
    ROUND(SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto))/SUM(Ve_DocVentaDet.Cantidad),2) AS PrecioCosto,
	ROUND(SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio - IFNULL(Ve_DocVentaDet.Descuento,0))/SUM(Ve_DocVentaDet.Cantidad), 2)  AS PrecioVenta,
	ROUND(SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio - IFNULL(Ve_DocVentaDet.Descuento,0)), 2) AS TotalVenta,
	ROUND(SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto)), 2) AS TotalCosto,
	ROUND(SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio - IFNULL(Ve_DocVentaDet.Descuento,0))  -  SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto)), 2) AS UtilidadBruta
	FROM Ve_DocVenta
	INNER JOIN Ve_DocVentaDet ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta
	INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
    INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
    INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca 
	INNER JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla

	WHERE Ve_DocVenta.Anulado=0 AND Ve_DocVenta.IdTipoDoc <> 11 AND Ve_DocVenta.FechaDoc BETWEEN '" . $fechaInicio . " 00:00:00' AND '" . $fechaFin . " 23:59:59'
	GROUP BY Gen_Producto.IdProducto";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['e' => $e->getMessage()], 500);
        //throw $th;
    }
});
$app->get('/reporte/utlidadbruta/usuario', function (Request $request, Response $response, array $args) {

    $fechaInicio = $request->getParam('fechaInicio');
    $fechaFin = $request->getParam('fechaFin');
    $usuarioReg = $request->getParam('usuarioReg');

    $select = "SELECT
    Ve_DocVenta.UsuarioReg,
	Gen_Producto.IdProducto,
	Gen_Producto.Producto,
	SUM(Ve_DocVentaDet.Cantidad) AS Cantidad,
    ProductoModelo,ProductoMarca,ProductoTalla,Color,Genero,Botapie,
    Gen_ProductoTalla.ProductoTalla,Gen_ProductoMarca.ProductoMarca,
    ROUND(SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto))/SUM(Ve_DocVentaDet.Cantidad),2) AS PrecioCosto,
	ROUND(SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio - IFNULL(Ve_DocVentaDet.Descuento,0))/SUM(Ve_DocVentaDet.Cantidad), 2)  AS PrecioVenta,
	ROUND(SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio - IFNULL(Ve_DocVentaDet.Descuento,0)), 2) AS TotalVenta,
	ROUND(SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto)), 2) AS TotalCosto,
	ROUND(SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio - IFNULL(Ve_DocVentaDet.Descuento,0))  -  SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto)), 2) AS UtilidadBruta
	FROM Ve_DocVenta
	INNER JOIN Ve_DocVentaDet ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta
	INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
    INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
    INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca 
	INNER JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla

	WHERE 
    Ve_DocVenta.Anulado=0 AND Ve_DocVenta.IdTipoDoc <> 11 
    AND Ve_DocVenta.FechaDoc BETWEEN '" . $fechaInicio . " 00:00:00' AND '" . $fechaFin . " 23:59:59'
    AND Ve_DocVenta.UsuarioReg = '$usuarioReg'
	GROUP BY Gen_Producto.IdProducto";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    // Normaliza productos sin costo para que Vue no propague valores NaN.
    $numericFields = ['Cantidad', 'PrecioCosto', 'PrecioVenta', 'TotalVenta', 'TotalCosto', 'UtilidadBruta'];
    foreach ($data as &$reportRow) {
        foreach ($numericFields as $numericField) {
            if (!isset($reportRow[$numericField]) || !is_numeric($reportRow[$numericField])) {
                $reportRow[$numericField] = 0;
            }
        }
        $reportRow['UtilidadBruta'] = round((float) $reportRow['TotalVenta'] - (float) $reportRow['TotalCosto'], 2);
    }
    unset($reportRow);

    return $response->withJson($data);
});

// Tipo de cambio para el encabezado. La consulta se hace en el servidor para
// evitar bloqueos CORS del navegador y conserva respaldos cuando SUNAT no responde.
$app->get('/tipo-cambio-sunat', function (Request $request, Response $response, array $args) {
    $fetchJson = function ($url) {
        if (!function_exists('curl_init')) return null;
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => array('Accept: application/json', 'User-Agent: NeuroSoft-POS/1.0')
        ));
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($status < 200 || $status >= 300 || !$body) return null;
        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : null;
    };

    $result = null;
    $remote = $fetchJson('https://deperu.com/api/rest/cotizaciondolar.json');
    if (!empty($remote['Cotizacion'][0])) {
        $row = $remote['Cotizacion'][0];
        $compra = isset($row['Compra']) ? (float) str_replace(',', '.', $row['Compra']) : 0;
        $venta = isset($row['Venta']) ? (float) str_replace(',', '.', $row['Venta']) : 0;
        if ($compra > 0 && $venta > 0) {
            $result = array(
                'compra' => $compra,
                'venta' => $venta,
                'fecha' => isset($row['Fecha']) ? $row['Fecha'] : date('d/m/Y'),
                'fuente' => 'Tipo de cambio SUNAT'
            );
        }
    }

    if (!$result) {
        try {
            $row = $this->db->select(array('TipoCambio'))->from('Gen_Moneda')->where('Moneda', '=', 'USD')->execute()->fetch();
            $tipo = $row && isset($row['TipoCambio']) ? (float) $row['TipoCambio'] : 0;
            if ($tipo > 0) {
                $result = array('compra' => $tipo, 'venta' => $tipo, 'fecha' => date('d/m/Y'), 'fuente' => 'Tipo de cambio registrado');
            }
        } catch (Exception $e) {
            $result = null;
        }
    }

    if (!$result) {
        return $response->withJson(array('error' => 'TIPO_CAMBIO_NO_DISPONIBLE'), 503);
    }
    return $response->withHeader('Cache-Control', 'no-store')->withJson($result);
});

$app->get('/reporte/utlidadbruta/excel', function (Request $request, Response $response, array $args) {
    try {
        $fechaInicio = (string) $request->getParam('fechaInicio');
        $fechaFin = (string) $request->getParam('fechaFin');
        $usuarioReg = trim((string) $request->getParam('usuarioReg'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
            return $response->withJson(['errorr' => 'Rango de fechas inválido'], 422);
        }

        $sql = "SELECT
            Ve_DocVenta.UsuarioReg,
            Ve_DocVenta.idDocVenta,
            Ve_DocVenta.FechaDoc,
            Ve_DocVentaTipoDoc.TipoDoc,
            Ve_DocVentaTipoDoc.CodSunat,
            Ve_DocVenta.Serie,
            Ve_DocVenta.Numero,
            Ve_DocVenta.Anulado,
            IFNULL(Ve_DocVentaCliente.Cliente, 'VARIOS') AS Cliente,
            Gen_Producto.IdProducto,
            Gen_Producto.Producto,
            SUM(Ve_DocVentaDet.Cantidad) *
                (CASE WHEN Ve_DocVenta.Anulado = 1 OR Ve_DocVentaTipoDoc.CodSunat = '07' THEN 0 ELSE 1 END) AS Cantidad,
            ROUND(SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto)) / NULLIF(SUM(Ve_DocVentaDet.Cantidad),0), 2) AS PrecioCosto,
            ROUND(SUM((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - IFNULL(Ve_DocVentaDet.Descuento,0)) / NULLIF(SUM(Ve_DocVentaDet.Cantidad),0), 2) AS PrecioVenta,
            ROUND(SUM((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - IFNULL(Ve_DocVentaDet.Descuento,0)) *
                (CASE WHEN Ve_DocVenta.Anulado = 1 OR Ve_DocVentaTipoDoc.CodSunat = '07' THEN 0 ELSE 1 END), 2) AS TotalVenta,
            ROUND(SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto)) *
                (CASE WHEN Ve_DocVenta.Anulado = 1 OR Ve_DocVentaTipoDoc.CodSunat = '07' THEN 0 ELSE 1 END), 2) AS TotalCosto,
            0 AS UtilidadBruta
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta
        INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        LEFT JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente
        WHERE Ve_DocVenta.FechaDoc BETWEEN :fechaInicio AND :fechaFin";
        $params = [
            ':fechaInicio' => $fechaInicio . ' 00:00:00',
            ':fechaFin' => $fechaFin . ' 23:59:59'
        ];
        if ($usuarioReg !== '') {
            $sql .= " AND Ve_DocVenta.UsuarioReg = :usuarioReg";
            $params[':usuarioReg'] = $usuarioReg;
        }
        $sql .= " GROUP BY Ve_DocVenta.UsuarioReg, Ve_DocVenta.idDocVenta, Ve_DocVenta.FechaDoc,
                    Ve_DocVentaTipoDoc.TipoDoc, Ve_DocVentaTipoDoc.CodSunat, Ve_DocVenta.Serie, Ve_DocVenta.Numero, Ve_DocVenta.Anulado,
                    Ve_DocVentaCliente.Cliente, Gen_Producto.IdProducto, Gen_Producto.Producto
                  ORDER BY Ve_DocVenta.UsuarioReg, Ve_DocVenta.FechaDoc, Ve_DocVenta.idDocVenta, Gen_Producto.Producto";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        $excel = new Spreadsheet();
        $detail = $excel->getActiveSheet();
        $detail->setTitle('Detalle');
        $detail->mergeCells('A1:P1');
        $detail->setCellValue('A1', 'REPORTE DETALLADO DE VENTAS Y UTILIDAD BRUTA');
        $detail->setCellValue('A2', 'RANGO: ' . $fechaInicio . ' AL ' . $fechaFin);
        $detail->setCellValue('A3', 'VENDEDOR: ' . ($usuarioReg !== '' ? $usuarioReg : 'TODOS'));
        $headers = ['Vendedor', 'Fecha', 'Comprobante', 'Serie', 'Número', 'Cliente', 'Estado', 'Id Producto', 'Producto', 'Cantidad', 'Precio Costo', 'Precio Venta', 'Total Venta', 'Total Costo', 'Utilidad Bruta', '% Utilidad'];
        foreach ($headers as $index => $label) {
            $detail->setCellValueByColumnAndRow($index + 1, 5, $label);
        }

        $rowNumber = 6;
        $totalVenta = 0;
        $totalCosto = 0;
        $totalUtilidad = 0;
        $resumen = [];
        $ventasEnumeradas = [];
        $subtotalRows = [];
        $currentUser = null;
        $userVenta = 0;
        $userCosto = 0;
        $userUtilidad = 0;
        $userPercentageSum = 0;
        $userPercentageCount = 0;
        $detailPercentageSum = 0;
        $detailPercentageCount = 0;

        $writeUserSubtotal = function () use (&$detail, &$rowNumber, &$subtotalRows, &$currentUser, &$userVenta, &$userCosto, &$userUtilidad, &$userPercentageSum, &$userPercentageCount) {
            if ($currentUser === null) return;
            $detail->setCellValue('A' . $rowNumber, 'SUMATORIA DE ' . strtoupper($currentUser));
            $detail->mergeCells('A' . $rowNumber . ':L' . $rowNumber);
            $detail->setCellValue('M' . $rowNumber, $userVenta);
            $detail->setCellValue('N' . $rowNumber, $userCosto);
            $detail->setCellValue('O' . $rowNumber, $userUtilidad);
            $detail->setCellValue('P' . $rowNumber, $userPercentageCount > 0 ? $userPercentageSum / $userPercentageCount : 0);
            $subtotalRows[] = $rowNumber;
            $rowNumber++;
        };

        foreach ($data as $row) {
            $user = $row['UsuarioReg'];
            if ($currentUser !== null && $currentUser !== $user) {
                $writeUserSubtotal();
                $userVenta = 0;
                $userCosto = 0;
                $userUtilidad = 0;
                $userPercentageSum = 0;
                $userPercentageCount = 0;
            }
            $currentUser = $user;
            $venta = (float) $row['TotalVenta'];
            $costo = (float) $row['TotalCosto'];
            // La utilidad del reporte debe cuadrar siempre con los totales visibles.
            $utilidad = $venta - $costo;
            $porcentaje = $costo != 0 ? $utilidad / $costo : 0;
            $values = [
                $row['UsuarioReg'], $row['FechaDoc'], $row['TipoDoc'], $row['Serie'], $row['Numero'],
                $row['Cliente'], ((int) $row['Anulado'] === 1 ? 'ANULADO' : ($row['CodSunat'] === '07' ? 'NOTA DE CRÉDITO' : 'ACTIVO')),
                $row['IdProducto'], $row['Producto'], $row['Cantidad'],
                $row['PrecioCosto'], $row['PrecioVenta'], $venta, $costo, $utilidad, $porcentaje
            ];
            foreach ($values as $index => $value) {
                $detail->setCellValueByColumnAndRow($index + 1, $rowNumber, $value);
            }
            $totalVenta += $venta;
            $totalCosto += $costo;
            $totalUtilidad += $utilidad;
            $userVenta += $venta;
            $userCosto += $costo;
            $userUtilidad += $utilidad;
            if ($costo != 0) {
                $userPercentageSum += $porcentaje;
                $userPercentageCount++;
                $detailPercentageSum += $porcentaje;
                $detailPercentageCount++;
            }
            if (!isset($resumen[$user])) $resumen[$user] = ['venta' => 0, 'costo' => 0, 'utilidad' => 0];
            $resumen[$user]['venta'] += $venta;
            $resumen[$user]['costo'] += $costo;
            $resumen[$user]['utilidad'] += $utilidad;
            $documentKey = (string) $row['idDocVenta'];
            if (!isset($ventasEnumeradas[$documentKey])) {
                $ventasEnumeradas[$documentKey] = [
                    'vendedor' => $user,
                    'fecha' => $row['FechaDoc'],
                    'comprobante' => $row['TipoDoc'],
                    'serie' => $row['Serie'],
                    'numero' => $row['Numero'],
                    'cliente' => $row['Cliente'],
                    'estado' => ((int) $row['Anulado'] === 1 ? 'ANULADO' : ($row['CodSunat'] === '07' ? 'NOTA DE CRÉDITO' : 'ACTIVO')),
                    'venta' => 0,
                    'costo' => 0,
                    'utilidad' => 0
                ];
            }
            $ventasEnumeradas[$documentKey]['venta'] += $venta;
            $ventasEnumeradas[$documentKey]['costo'] += $costo;
            $ventasEnumeradas[$documentKey]['utilidad'] += $utilidad;
            $rowNumber++;
        }

        $writeUserSubtotal();

        $detail->setCellValue('A' . $rowNumber, 'TOTAL GENERAL');
        $detail->mergeCells('A' . $rowNumber . ':L' . $rowNumber);
        $detail->setCellValue('M' . $rowNumber, $totalVenta);
        $detail->setCellValue('N' . $rowNumber, $totalCosto);
        $detail->setCellValue('O' . $rowNumber, $totalUtilidad);
        $detail->setCellValue('P' . $rowNumber, $detailPercentageCount > 0 ? $detailPercentageSum / $detailPercentageCount : 0);

        $detailTotalRow = $rowNumber;
        $rowNumber++;
        $detailCount = count($data);
        $detail->setCellValue('A' . $rowNumber, 'PROMEDIO GENERAL');
        $detail->mergeCells('A' . $rowNumber . ':L' . $rowNumber);
        $detail->setCellValue('M' . $rowNumber, $detailCount > 0 ? $totalVenta / $detailCount : 0);
        $detail->setCellValue('N' . $rowNumber, $detailCount > 0 ? $totalCosto / $detailCount : 0);
        $detail->setCellValue('O' . $rowNumber, $detailCount > 0 ? $totalUtilidad / $detailCount : 0);
        $detail->setCellValue('P' . $rowNumber, $detailPercentageCount > 0 ? $detailPercentageSum / $detailPercentageCount : 0);
        $detailAverageRow = $rowNumber;

        $summary = $excel->createSheet();
        $summary->setTitle('Ventas enumeradas');
        $summary->mergeCells('A1:L1');
        $summary->setCellValue('A1', 'LISTADO ENUMERADO DE TODAS LAS VENTAS');
        $summary->setCellValue('A2', 'RANGO: ' . $fechaInicio . ' AL ' . $fechaFin);
        foreach (['N°', 'Vendedor', 'Fecha', 'Comprobante', 'Serie', 'Número', 'Cliente', 'Estado', 'Total Venta', 'Total Costo', 'Utilidad Bruta', '% Utilidad'] as $index => $label) {
            $summary->setCellValueByColumnAndRow($index + 1, 4, $label);
        }
        $summaryRow = 5;
        $saleNumber = 1;
        $summaryUser = null;
        $summaryUserVenta = 0;
        $summaryUserCosto = 0;
        $summaryUserUtilidad = 0;
        $summaryUserPercentageSum = 0;
        $summaryUserPercentageCount = 0;
        $summaryPercentageSum = 0;
        $summaryPercentageCount = 0;
        $summarySubtotalRows = [];
        $writeSummarySubtotal = function () use (&$summary, &$summaryRow, &$summaryUser, &$summaryUserVenta, &$summaryUserCosto, &$summaryUserUtilidad, &$summaryUserPercentageSum, &$summaryUserPercentageCount, &$summarySubtotalRows) {
            if ($summaryUser === null) return;
            $summary->setCellValue('A' . $summaryRow, 'SUMATORIA DE ' . strtoupper($summaryUser));
            $summary->mergeCells('A' . $summaryRow . ':H' . $summaryRow);
            $summary->setCellValue('I' . $summaryRow, $summaryUserVenta);
            $summary->setCellValue('J' . $summaryRow, $summaryUserCosto);
            $summary->setCellValue('K' . $summaryRow, $summaryUserUtilidad);
            $summary->setCellValue('L' . $summaryRow, $summaryUserPercentageCount > 0 ? $summaryUserPercentageSum / $summaryUserPercentageCount : 0);
            $summarySubtotalRows[] = $summaryRow;
            $summaryRow++;
        };
        foreach ($ventasEnumeradas as $sale) {
            if ($summaryUser !== null && $summaryUser !== $sale['vendedor']) {
                $writeSummarySubtotal();
                $summaryUserVenta = 0;
                $summaryUserCosto = 0;
                $summaryUserUtilidad = 0;
                $summaryUserPercentageSum = 0;
                $summaryUserPercentageCount = 0;
            }
            $summaryUser = $sale['vendedor'];
            $values = [
                $saleNumber++, $sale['vendedor'], $sale['fecha'], $sale['comprobante'], $sale['serie'], $sale['numero'],
                $sale['cliente'], $sale['estado'], $sale['venta'], $sale['costo'], $sale['utilidad'],
                $sale['costo'] != 0 ? $sale['utilidad'] / $sale['costo'] : 0
            ];
            foreach ($values as $index => $value) $summary->setCellValueByColumnAndRow($index + 1, $summaryRow, $value);
            $summaryUserVenta += $sale['venta'];
            $summaryUserCosto += $sale['costo'];
            $summaryUserUtilidad += $sale['utilidad'];
            if ($sale['costo'] != 0) {
                $salePercentage = $sale['utilidad'] / $sale['costo'];
                $summaryUserPercentageSum += $salePercentage;
                $summaryUserPercentageCount++;
                $summaryPercentageSum += $salePercentage;
                $summaryPercentageCount++;
            }
            $summaryRow++;
        }
        $writeSummarySubtotal();
        $summary->setCellValue('A' . $summaryRow, 'TOTAL GENERAL');
        $summary->mergeCells('A' . $summaryRow . ':H' . $summaryRow);
        $summary->setCellValue('I' . $summaryRow, $totalVenta);
        $summary->setCellValue('J' . $summaryRow, $totalCosto);
        $summary->setCellValue('K' . $summaryRow, $totalUtilidad);
        $summary->setCellValue('L' . $summaryRow, $summaryPercentageCount > 0 ? $summaryPercentageSum / $summaryPercentageCount : 0);

        $summaryTotalRow = $summaryRow;
        $summaryRow++;
        $summaryCount = count($ventasEnumeradas);
        $summary->setCellValue('A' . $summaryRow, 'PROMEDIO GENERAL');
        $summary->mergeCells('A' . $summaryRow . ':H' . $summaryRow);
        $summary->setCellValue('I' . $summaryRow, $summaryCount > 0 ? $totalVenta / $summaryCount : 0);
        $summary->setCellValue('J' . $summaryRow, $summaryCount > 0 ? $totalCosto / $summaryCount : 0);
        $summary->setCellValue('K' . $summaryRow, $summaryCount > 0 ? $totalUtilidad / $summaryCount : 0);
        $summary->setCellValue('L' . $summaryRow, $summaryPercentageCount > 0 ? $summaryPercentageSum / $summaryPercentageCount : 0);
        $summaryAverageRow = $summaryRow;

        foreach ([$detail, $summary] as $sheet) {
            $lastColumn = $sheet === $detail ? 'P' : 'L';
            $headerRow = $sheet === $detail ? 5 : 4;
            $lastRow = $sheet->getHighestRow();
            $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A1:' . $lastColumn . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF075F94');
            $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF075F94');
            foreach (range('A', $lastColumn) as $column) $sheet->getColumnDimension($column)->setAutoSize(true);
            $sheet->freezePane('A' . ($headerRow + 1));
            $sheet->setAutoFilter('A' . $headerRow . ':' . $lastColumn . max($headerRow, $lastRow - 1));
        }
        foreach ([[$detail, $detailTotalRow, $detailAverageRow, 'P'], [$summary, $summaryTotalRow, $summaryAverageRow, 'L']] as $totalsStyle) {
            list($totalsSheet, $totalRowStyle, $averageRowStyle, $totalLastColumn) = $totalsStyle;
            $totalsSheet->getStyle('A' . $totalRowStyle . ':' . $totalLastColumn . $totalRowStyle)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $totalsSheet->getStyle('A' . $totalRowStyle . ':' . $totalLastColumn . $totalRowStyle)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF006D77');
            $totalsSheet->getStyle('A' . $averageRowStyle . ':' . $totalLastColumn . $averageRowStyle)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $totalsSheet->getStyle('A' . $averageRowStyle . ':' . $totalLastColumn . $averageRowStyle)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF075F94');
        }
        $detail->getStyle('K6:O' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0.00');
        $detail->getStyle('P6:P' . $rowNumber)->getNumberFormat()->setFormatCode('0.00%');
        $detail->getStyle('B6:B' . $rowNumber)->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm');
        foreach ($subtotalRows as $subtotalRow) {
            $detail->getStyle('A' . $subtotalRow . ':P' . $subtotalRow)->getFont()->setBold(true)->getColor()->setARGB('FF063970');
            $detail->getStyle('A' . $subtotalRow . ':P' . $subtotalRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAF7');
        }
        $summary->getStyle('I5:K' . $summaryRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $summary->getStyle('L5:L' . $summaryRow)->getNumberFormat()->setFormatCode('0.00%');
        $summary->getStyle('C5:C' . $summaryRow)->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm');
        foreach ($summarySubtotalRows as $subtotalRow) {
            $summary->getStyle('A' . $subtotalRow . ':L' . $subtotalRow)->getFont()->setBold(true)->getColor()->setARGB('FF063970');
            $summary->getStyle('A' . $subtotalRow . ':L' . $subtotalRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAF7');
        }

        // Abrir el archivo directamente en el listado solicitado por el usuario.
        $excel->setActiveSheetIndex(1);

        $fileName = 'Utilidad_Bruta_' . ($usuarioReg !== '' ? preg_replace('/[^A-Za-z0-9_-]/', '_', $usuarioReg) : 'TODOS') . '_' . $fechaInicio . '_al_' . $fechaFin . '_' . substr(uniqid(), -6) . '.xlsx';
        aplicarIdentidadEmpresaExcel($excel, $this->db);
        $writer = new Xlsx($excel);
        $writer->save(__DIR__ . '/reporte/' . $fileName);
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        return $response->withJson($protocol . $_SERVER['HTTP_HOST'] . '/api/reporte/' . $fileName);
    } catch (Exception $e) {
        return $response->withJson(['errorr' => $e->getMessage()], 500);
    }
});

$app->post('/proformas', function (Request $request, Response $response) {
    $idCliente = $request->getParam('cliente')['IdCliente'];
    $usuarioReg = isset($request->getParam('vendedor')['Usuario']) ? $request->getParam('vendedor')['Usuario'] : 'xx';
    $esCreditoProforma = $request->getParam('EsCreditoProforma') == 1 ? 1 : 0;
    $fechaCreditoProforma = !empty($fechaCreditoProforma) ? "'$fechaCreditoProforma'" : null;
    $esDescripcion = $request->getParam('EsDescripcion') == 1 ? 1 : 0;
    $descripcionProforma = $request->getParam('DescripcionProforma') ? $request->getParam('DescripcionProforma') : '';
    $descripcionProforma2 = $request->getParam('DescripcionProforma2') ? $request->getParam('DescripcionProforma2') : '';
    $descripcionProforma3 = $request->getParam('DescripcionProforma3') ? $request->getParam('DescripcionProforma3') : '';
    $descripcionProforma4 = $request->getParam('DescripcionProforma4') ? $request->getParam('DescripcionProforma4') : '';
    $descripcionProforma5 = $request->getParam('DescripcionProforma5') ? $request->getParam('DescripcionProforma5') : '';
    $descripcionProforma = str_replace("'", "''", mb_substr((string) $descripcionProforma, 0, 500));
    $descripcionProforma2 = str_replace("'", "''", mb_substr((string) $descripcionProforma2, 0, 500));
    $descripcionProforma3 = str_replace("'", "''", mb_substr((string) $descripcionProforma3, 0, 500));
    $descripcionProforma4 = str_replace("'", "''", mb_substr((string) $descripcionProforma4, 0, 500));
    $descripcionProforma5 = str_replace("'", "''", mb_substr((string) $descripcionProforma5, 0, 500));
    $diasValidos = $request->getParam('diasValidosProf') ? $request->getParam('diasValidosProf') : 7;
    // Verificar si existe la proforma
    $select = "SELECT Numero, Anio FROM Ve_Proforma WHERE Anio='" . getNow("Y") . "' ORDER BY Numero DESC LIMIT 1";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $proforma = $stmt->fetch();

    $numero = $proforma['Numero'];
    $anio = $proforma['Anio'];

    $newNumero = 1;
    $newAnio = getNow("Y");

    if ($numero && $anio) {
        $newNumero = $numero + 1;
    }
    $EsOrganizacion = $request->getParam('EsOrganizacion');

    $nombreOrganizacion = '';
    if ($EsOrganizacion  == 1) {
        $nombreOrganizacion = $request->getParam('nombreOrganizacion');
    }
    // START INSERTAR NUEVA PROFORMA
    if ($fechaCreditoProforma) {
        $insert = "INSERT INTO Ve_Proforma
                (Anio, Numero, IdCliente, FechaReg, UsuarioReg, DiasValidos, DescripcionProforma, DescripcionProforma2, DescripcionProforma3, DescripcionProforma4, DescripcionProforma5, EsDescripcion, EsCreditoProforma, FechaCreditoProforma) VALUES($newAnio, $newNumero, $idCliente, '" . getNow() . "', '$usuarioReg', $diasValidos, '$descripcionProforma','$descripcionProforma2','$descripcionProforma3','$descripcionProforma4','$descripcionProforma5', $esDescripcion, $esCreditoProforma, '$fechaCreditoProforma')";
    } else {
        $insert = "INSERT INTO Ve_Proforma
                (Anio, Numero, IdCliente, FechaReg, UsuarioReg, DiasValidos, DescripcionProforma, DescripcionProforma2, DescripcionProforma3, DescripcionProforma4, DescripcionProforma5, EsDescripcion, EsCreditoProforma, FechaCreditoProforma) VALUES($newAnio, $newNumero, $idCliente, '" . getNow() . "', '$usuarioReg', $diasValidos, '$descripcionProforma','$descripcionProforma2','$descripcionProforma3','$descripcionProforma4','$descripcionProforma5', $esDescripcion, $esCreditoProforma, null)";
    }
    $stmt = $this->db->prepare($insert);
    $inserted = $stmt->execute();
    $idProforma = $this->db->lastInsertId();
    // END INSERTAR NUEVA PROFORMA


    // START INSERTAR PROFORMA DETALLE
    $productos = $request->getParam('productos');
    //$descuentoTotal = 0;
    foreach ($productos as $producto) {

        $idProducto = $producto['IdProducto'];
        $cantidad = $producto['cantidad'];
        $precio = $producto['precio'];
        $descuento = $producto['descuento'];
        $descripcion = isset($producto['Descripcion']) ? (string) $producto['Descripcion'] : '';

        $insert = $this->db->insert(array('IdProforma', 'IdProducto', 'Cantidad', 'Precio', 'Descuento', 'Descripcion'))
            ->into('Ve_ProformaDet')
            ->values(array($idProforma, $idProducto, $cantidad, $precio, $descuento, $descripcion));

        $insert->execute();
        //$descuentoTotal += $descuento;

    }
    // END PROFORMA DETALLE

    $data = array(
        'insertId' => $idProforma
    );

    return $response->withJson($data);
});

$app->get('/proformas/id/{id}', function (Request $request, Response $response, array $args) {
    $select = "SELECT Ve_Proforma.*, Ve_DocVentaCliente.Cliente, Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.Direccion AS ClienteDireccion, Ve_DocVentaCliente.Email AS ClienteEmail
    
        FROM Ve_Proforma
        INNER JOIN Ve_DocVentaCliente ON Ve_Proforma.IdCliente = Ve_DocVentaCliente.IdCliente
        WHERE Ve_Proforma.IdProforma = '" . $args['id'] . "' ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $proforma = $stmt->fetch();

    $select = "SELECT Ve_ProformaDet.IdProforma, Ve_ProformaDet.Cantidad, 
        Ve_ProformaDet.Precio, Ve_ProformaDet.Descuento, Gen_Producto.*, COALESCE(Ve_ProformaDet.Descripcion,'') AS Descripcion,
        Gen_Producto.Color, Gen_Producto.Genero, Gen_Producto.Botapie,
        Gen_Producto.ProductoModelo, Gen_ProductoMarca.ProductoMarca,
        Gen_ProductoTalla.ProductoTalla,
        Gen_ProductoMedicion.Codigo AS IdProductoMedicion
        FROM Ve_ProformaDet
        INNER JOIN Gen_Producto ON Ve_ProformaDet.IdProducto = Gen_Producto.IdProducto
        LEFT JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
        LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla
        LEFT JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
        WHERE Ve_ProformaDet.IdProforma = '" . $args['id'] . "'";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $proformaDet = $stmt->fetchAll();

        $proforma['productos'] = $proformaDet;

        return $response->withJson($proforma);
});


$app->get('/proformas/productos/{id}', function (Request $request, Response $response, array $args) {
    $select = "SELECT Ve_ProformaDet.IdProforma, Ve_ProformaDet.Cantidad, Ve_ProformaDet.Precio, Gen_Producto.*, COALESCE(Ve_ProformaDet.Descripcion,'') AS Descripcion FROM Ve_ProformaDet
    INNER JOIN Gen_Producto ON Ve_ProformaDet.IdProducto = Gen_Producto.IdProducto
    WHERE Ve_ProformaDet.IdProforma = '" . $args['id'] . "'";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->get('/proformas', function (Request $request, Response $response) {
    $filter = $request->getParam('filter');
    $select = "SELECT Ve_Proforma.IdProforma, Ve_Proforma.DiasValidos, Ve_DocVentaCliente.IdCliente, Ve_Proforma.Anio, Ve_Proforma.Numero, Ve_Proforma.FechaReg, Ve_Proforma.UsuarioReg,  Ve_DocVentaCliente.Cliente,
        Ve_DocVentaCliente.DniRuc,
        ROUND((
            SELECT
            SUM(
                    Ve_ProformaDet.Cantidad * Ve_ProformaDet.precio
            )
            FROM Ve_ProformaDet
            WHERE Ve_ProformaDet.IdProforma= Ve_Proforma.IdProforma
         ),2) Total
        FROM Ve_Proforma
        LEFT JOIN Ve_DocVentaCliente ON Ve_Proforma.IdCliente = Ve_DocVentaCliente.IdCliente
        WHERE Ve_Proforma.Anulado = 0
        AND (Ve_DocVentaCliente.Cliente LIKE '%$filter%'
        OR Ve_DocVentaCliente.DniRuc LIKE '%$filter%'
        OR Ve_Proforma.Anio LIKE '%$filter%'
        OR Ve_Proforma.Numero LIKE '%$filter%'
        OR Ve_Proforma.FechaReg LIKE '%$filter%'
        OR Ve_Proforma.UsuarioReg LIKE '%$filter%') ";

    $select .= " ORDER BY Ve_Proforma.IdProforma DESC";

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->get('/proformas/count', function (Request $request, Response $response, array $args) {
    $select = "SELECT COUNT(*) as total FROM Ve_Proforma";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});

$app->get('/proformas/detalle', function (Request $request, Response $response) {
    $idProforma = $request->getParam('idProforma');

    $select = "SELECT * FROM Ve_ProformaDet WHERE IdProforma=$idProforma";
    // $select = "SELECT Ve_ProformaDet.*, Gen_Producto.Producto FROM Ve_ProformaDet 
    //     INNER JOIN Gen_Producto ON Ve_ProformaDet.IdProducto = Gen_Producto.IdProducto     
    //     WHERE IdProforma=$idProforma ";
    // $select = "SELECT * FROM Ve_ProformaDet AS proDet JOIN Ve_Proforma AS pro WHERE IdProforma=$idProforma";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/remision', function (Request $request, Response $response) {
    try {
        //code...


        $estado = $request->getParam('estado');
        $select = "SELECT * FROM Ve_DocVentaRemision WHERE Estado=$estado";

        $filter = $request->getParam('filter');
        if (isset($filter['fechaReferencia'])) {
            $select .= " AND FechaReg LIKE '" . $filter['fechaReferencia'] . "%'";
        }

        $stmt = $this->db->query($select);
        $stmt->execute();

        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['err' => $e->getMessage()], 500);
    }
});

$app->get('/remision/detalle', function (Request $request, Response $response) {
    $idRemisiones = $request->getParam('idRemisiones');

    $select = "SELECT Ve_DocVentaRemisionDet.*, Gen_Producto.Producto,
        Gen_Producto.CodigoBarra, Gen_ProductoMedicion.Codigo AS CodigoMedicion, Gen_ProductoMedicion.ProductoMedicion
        FROM Ve_DocVentaRemisionDet
        INNER JOIN Gen_Producto ON Ve_DocVentaRemisionDet.IdProducto = Gen_Producto.IdProducto
        INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
        WHERE IdDocVentaRemision IN (" . implode(',', $idRemisiones) . ")";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});



$app->post('/remision', function (Request $request, Response $response) {
    try {
        //code...

        $idGuiaRemision = $request->getParam('idGuiaRemision');
        $remision = $request->getParam('remision');

        $fechaReg = getNow();
        $fechaTraslado = $remision['fechaTraslado'];
        $observacion = $remision['observacion'];
        $codUndPesoBruto = $remision['codUndPesoBruto'] ?? 'KGM';
        $pesoBruto = $remision['pesoBruto'];
        $codModalidadTraslado = $remision['codModalidadTraslado'];
        $codMotivoTraslado = $remision['codMotivoTraslado'];
        $descripcionMotivoTraslado = $remision['descripcionMotivoTraslado'];
        $codUbigeoOrigen = $remision['codUbigeoOrigen'];
        $direccionOrigen = $remision['direccionOrigen'];
        $documentoReferencia = $remision['documentoReferencia'];

        $nroDocumentoCliente = $remision['nroDocumentoCliente'];
        $razonSocialCliente = $remision['razonSocialCliente'];
        $direccionCliente = $remision['direccionCliente'];

        $codUbigeoDestino = $remision['codUbigeoDestino'];
        $direccionDestino = $remision['direccionDestino'];

        $nroDocumentoTransportista = $remision['nroDocumentoTransportista'];
        $razonSocialTransportista = $remision['razonSocialTransportista'];
        $placaVehiculo = $remision['placaVehiculo'];
        $placaCarreta = $remision['placaCarreta'];
        $nroDocChofer = $remision['nroDocChofer'];
        $totalBultos = $remision['totalBultos'] ? $remision['totalBultos'] : 0;
        $licenciaChofer = $remision['licenciaChofer'];

        if ($idGuiaRemision) {
        } else {
            // OBTENER SERIE
            $selectSerie = "SELECT Serie, Numero + 1 AS NuevoNumero FROM Ve_DocVentaRemision ORDER BY Numero DESC LIMIT 1";
            $stmt = $this->db->query($selectSerie);

            $stmt->execute();
            $selectSerie = $stmt->fetch();
            $serie = $selectSerie['Serie'] ? $selectSerie['Serie'] : 'T001';
            $numero = $selectSerie['NuevoNumero'] ? $selectSerie['NuevoNumero'] : '1';

            // START INSERTAR GUIA REMISION
            $insert = "INSERT INTO Ve_DocVentaRemision(FechaReg, FechaTraslado, Observacion, CodUndPesoBruto, PesoBruto, CodModalidadTraslado, CodMotivoTraslado, DescripcionMotivoTraslado, CodUbigeoOrigen,
            DireccionOrigen, DocumentoReferencia, NroDocumentoCliente, RazonSocialCliente, DireccionCliente, CodUbigeoDestino, DireccionDestino, 
            NroDocumentoTransportista, RazonSocialTransportista, PlacaVehiculo, PlacaCarreta, NroDocChofer, Serie, Numero, TotalBultos, LicenciaChofer) 
            VALUES('$fechaReg', '$fechaTraslado', '$observacion', '$codUndPesoBruto', '$pesoBruto', '$codModalidadTraslado', '$codMotivoTraslado', '$descripcionMotivoTraslado', '$codUbigeoOrigen',
            '$direccionOrigen', '$documentoReferencia', '$nroDocumentoCliente', '$razonSocialCliente', '$direccionCliente', '$codUbigeoDestino', '$direccionDestino',
            '$nroDocumentoTransportista', '$razonSocialTransportista', '$placaVehiculo', '$placaCarreta', '$nroDocChofer','$serie', $numero, '$totalBultos', '$licenciaChofer')";
            $stmt = $this->db->prepare($insert);
            $inserted = $stmt->execute();
            $insertId = $this->db->lastInsertId();

            // END REMISION

            // START INSERTAR REMISION DETALLE
            $productos = $request->getParam('productos');

            foreach ($productos as $producto) {
                $idProducto = $producto['IdProducto'];
                $cantidad = $producto['cantidad'];

                $insert = $this->db->insert(array('IdDocVentaRemision', 'IdProducto', 'Cantidad'))
                    ->into('Ve_DocVentaRemisionDet')
                    ->values(array($insertId, $idProducto, $cantidad));

                $insert->execute();
            }

            return $response->withJson(array("idGuiaRemision" => $insertId));
        }
    } catch (Exception $e) {
        return $response->withJson(['e' => $e->getMessage()], 500);
    }
});


$app->get('/preorden/count', function (Request $request, Response $response, array $args) {
    $select = "SELECT COUNT(*) as total FROM Ve_PreOrden";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});


$app->get('/preorden', function (Request $request, Response $response) {
    $filter = $request->getParam('filter');
    $select = "SELECT Ve_PreOrden.IdPreOrden, Ve_PreOrden.IdCliente, Ve_DocVentaCliente.Cliente, Ve_DocVentaCliente.DniRuc, Ve_PreOrden.FechaReg 
        FROM Ve_PreOrden INNER JOIN Ve_DocVentaCliente ON Ve_PreOrden.IdCliente = Ve_DocVentaCliente.IdCliente";
    $select .= " WHERE Ve_DocVentaCliente.Cliente LIKE '%" . $filter . "%' 
                 OR Ve_DocVentaCliente.DniRuc LIKE '" . $filter . "%'";
    $select .= " ORDER BY Ve_PreOrden.IdPreOrden DESC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/preorden/detalle', function (Request $request, Response $response) {
    $idPreOrden = $request->getParam('idPreOrden');

    $select = "SELECT * FROM Ve_PreOrdenDet WHERE IdPreOrden=$idPreOrden";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/cuentas', function (Request $request, Response $response, array $args) {
    $select = "SELECT IdCuenta, Cuenta, Anulado FROM Cb_Cuenta WHERE Anulado=0";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});

$app->get('/cliente', function (Request $request, Response $response, array $args) {
    $idCliente = $request->getParam('idCliente');
    $dni = $request->getParam('dni');

    if ($dni) {
        $select = "SELECT * FROM Ve_DocVentaCliente WHERE DniRuc='$dni'";
    } else {
        $select = "SELECT * FROM Ve_DocVentaCliente WHERE IdCliente='$idCliente'";
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/empresa/id/{id}', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM GEN_EMPRESA WHERE IDEMPRESA=$args[id]";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/empresa/config/{id}', function (Request $request, Response $response, array $args) {
    $select = "SELECT CONFIG FROM GEN_EMPRESA WHERE IDEMPRESA=$args[id]";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();
    $data['CONFIG'] = unserialize($data['CONFIG']);

    return $response->withJson($data);
});


$app->get('/usuarios', function (Request $request, Response $response, array $args) {
    $select = "SELECT lower(Usuario) Usuario,NombreUsuario FROM `Seg_Usuario` WHERE (Anulado != 1 OR Anulado IS NULL)";
    $select .= " AND Seg_Usuario.Usuario LIKE '%" . $request->getParam('q') . "%' ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/usuarios/{usuario}', function (Request $request, Response $response, array $args) {
    try {
        $select = "SELECT * FROM Seg_Usuario WHERE (Anulado != 1 OR Anulado IS NULL)";
        $select .= " AND Seg_Usuario.Usuario = '$args[usuario]' ";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetch();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->post('/usuarios/delete', function (Request $request, Response $response, array $args) {
    $usuario = $request->getParam('usuario');

    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (strtolower(trim((string) $usuario)) === 'superadmin') {
        return $response->withJson(array('error' => 'La cuenta SUPERADMIN está protegida y no puede anularse.'), 403);
    }

    if ($usuario) {
        $update = "UPDATE Seg_Usuario SET Anulado=1 WHERE Usuario='$usuario'";
        $stmt = $this->db->prepare($update);
        $updated = $stmt->execute();


        return $response->withJson(array(
            "updated" => $updated,
            "usuario" => $usuario
        ));
    }
});

$app->get('/clientes/{dni}', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Ve_DocVentaCliente WHERE (Anulado != 1 OR Anulado IS NULL)";
    $select .= " AND Ve_DocVentaCliente.DniRuc = '" . $args['dni'] . "' LIMIT 1";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/clientes', function (Request $request, Response $response, array $args) {
    $select = "SELECT *, IFNULL(CONCAT(DniRuc, ' - ', Cliente), '-') AS ClienteDniRuc FROM Ve_DocVentaCliente WHERE (Anulado != 1 OR Anulado IS NULL)";
    $select .= " AND (Ve_DocVentaCliente.Cliente LIKE '%" . $request->getParam('q') . "%'";
    $select .= " OR Ve_DocVentaCliente.DniRuc LIKE '%" . $request->getParam('q') . "%') ";

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->post('/clientes', function (Request $request, Response $response) {
    $cliente = $request->getParam('Cliente');
    $dniRuc = $request->getParam('DniRuc');
    $direccion = $request->getParam('Direccion');
    $direccion2 = $request->getParam('Direccion2');
    $direccion3 = $request->getParam('Direccion3');
    $telefono = $request->getParam('Telefono');
    $email = $request->getParam('Email');
    $esVIP = $request->getParam('EsVIP');
    $fechaNacimiento = $request->getParam('FechaNacimiento') ? $request->getParam('FechaNacimiento') : NULL;

    $idCliente = $request->getParam('IdCliente');
    $insertId = $idCliente;

    if ($idCliente) {
        $update = $this->db->update(array(
            "Cliente" => $cliente,
            'DniRuc' => $dniRuc,
            'Direccion' => $direccion,
            'Direccion2' => $direccion2,
            'Direccion3' => $direccion3,
            'Telefono' => $telefono,
            'Email' => $email,
            'EsVIP' => $esVIP,
            'FechaNacimiento' => $fechaNacimiento,
        ))
            ->table('Ve_DocVentaCliente')
            ->where('IdCliente', '=', $idCliente);

        $affectedRows = $update->execute();
    } else {

        $insert = $this->db->insert(array('Cliente', 'DniRuc', 'Direccion', 'Direccion2', 'Direccion3', 'Telefono', 'Email', 'Anulado', 'FechaReg', 'EsVIP', 'FechaNacimiento'))
            ->into('Ve_DocVentaCliente')
            ->values(array($cliente, $dniRuc, $direccion, $direccion2, $direccion3, $telefono, $email, '0', getNow(), $esVIP, $fechaNacimiento));

        $insertId = $insert->execute();
    }

    $select = "SELECT * FROM Ve_DocVentaCliente WHERE IdCliente=$insertId";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});


$app->get('/cliente/deudas', function (Request $request, Response $response, array $args) {
    $idCliente = $request->getParam('idCliente');

    if ($idCliente) {
        $select = "SELECT
        Ve_DocVenta.IdDocVenta,
        Ve_DocVenta.FechaDoc,
        Ve_DocVenta.FechaCredito,
        Ve_DocVenta.IdTipoDoc,
        CONCAT(Ve_DocVenta.Serie,'-', CONVERT(Ve_DocVenta.Numero, CHAR)) AS SerieNumero,
        CONCAT(CASE WHEN Ve_DocVentaTipoDoc.CodSunat ='01' THEN
            'F'
        ELSE
            CASE WHEN Ve_DocVentaTipoDoc.CodSunat='03' THEN
                'B'
            ELSE
                CASE WHEN Ve_DocVentaTipoDoc.CodSunat='12' THEN
                    'T'
                ELSE
                    'OTRO'
                END
            END
        END,Ve_DocVenta.Serie,'-' , CONVERT(Ve_DocVenta.Numero, CHAR) )AS Correlativo,
        ROUND(SUM((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento ),2) AS Total,
        ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='VE' AND Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)),2) AS Aplicado,
        
        ROUND( (SELECT IFNULL((SELECT SUM((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento)FROM
        Ve_DocVentaDet INNER JOIN Ve_DocVenta ON Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.IdDocVenta WHERE Ve_DocVenta.IdTipoDoc=11 AND Ve_DocVenta.NroComprobanteModifica = SerieNumero AND Ve_DocVenta.Anulado=0  ),0)
        ),2) AS NotaCreApli,
            ROUND(SUM((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento ),2) -
        ROUND((SELECT NotaCreApli) ,2)	-
            ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='VE' AND Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)),2) AS Saldo,
        DocVentaNotas.Notas AS NotasCredito
        
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.IdDocVenta=Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc=Ve_DocVentaTipoDoc.IdTipoDoc
        LEFT JOIN (SELECT Ve_DocVenta.NroComprobanteModifica,
        CONCAT( 
        '[', 
            GROUP_CONCAT( CONCAT( '{ \"IdDocVenta\":', Ve_DocVenta.idDocVenta ,',\"FechaDoc\":\"', Ve_DocVenta.FechaDoc , '\",\"Serie\":\"', Ve_DocVenta.Serie, '\",\"Numero\":',Ve_DocVenta.Numero,',\"Total\":',ROUND(((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento ),2),' }' ) SEPARATOR ', '),
            ']'
        ) AS Notas 
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
        WHERE Ve_DocVenta.idTipoDoc=11 AND Ve_DocVenta.Anulado=0
        GROUP BY Ve_DocVenta.NroComprobanteModifica )  AS DocVentaNotas ON DocVentaNotas.NroComprobanteModifica = CONCAT(Ve_DocVenta.Serie,'-',Ve_DocVenta.Numero)
        
        
        
        WHERE Ve_DocVenta.IdCliente=$idCliente AND Ve_DocVenta.EsCredito=1 AND Ve_DocVenta.Anulado=0
        GROUP BY
        Ve_DocVenta.IdDocVenta,
        Ve_DocVenta.FechaDoc,
        Ve_DocVenta.FechaCredito,
        Ve_DocVentaTipoDoc.CodSunat,
        Ve_DocVenta.Serie,
        Ve_DocVenta.Numero
        HAVING ROUND(Saldo,2)>0
        
        ORDER BY FechaDoc;";
    } else {

        $select = "SELECT
        Ve_DocVentaCliente.DniRuc,
        Ve_DocVentaCliente.Cliente,
        Ve_DocVenta.IdDocVenta,
        Ve_DocVenta.FechaDoc,
        Ve_DocVenta.FechaCredito,
        Ve_DocVenta.IdTipoDoc,
        CONCAT(Ve_DocVenta.Serie,'-', CONVERT(Ve_DocVenta.Numero, CHAR)) AS SerieNumero,
        CONCAT(CASE WHEN Ve_DocVentaTipoDoc.CodSunat ='01' THEN
            'F'
        ELSE
            CASE WHEN Ve_DocVentaTipoDoc.CodSunat='03' THEN
                'B'
            ELSE
                CASE WHEN Ve_DocVentaTipoDoc.CodSunat='12' THEN
                    'T'
                ELSE
                    'OTRO'
                END
            END
        END,Ve_DocVenta.Serie,'-' , CONVERT(Ve_DocVenta.Numero, CHAR) )AS Correlativo,
        
        ROUND(SUM((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento ),2) AS Total,
        ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='VE' AND Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)),2) AS Aplicado,
        
        ROUND( (SELECT IFNULL((SELECT SUM((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento)FROM
        Ve_DocVentaDet INNER JOIN Ve_DocVenta ON Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.IdDocVenta WHERE Ve_DocVenta.IdTipoDoc=11 AND Ve_DocVenta.NroComprobanteModifica = SerieNumero AND Ve_DocVenta.Anulado=0  ),0)
        ),2) AS NotaCreApli,
            ROUND(SUM((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento ),2) -
        ROUND((SELECT NotaCreApli) ,2)	-
            ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='VE' AND Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)),2) AS Saldo,
        DocVentaNotas.Notas AS NotasCredito
        
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.IdDocVenta=Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc=Ve_DocVentaTipoDoc.IdTipoDoc
        INNER JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente
        LEFT JOIN (SELECT Ve_DocVenta.NroComprobanteModifica,
        CONCAT( 
        '[', 
            GROUP_CONCAT( CONCAT( '{ \"IdDocVenta\":', Ve_DocVenta.idDocVenta ,',\"FechaDoc\":\"', Ve_DocVenta.FechaDoc , '\",\"Serie\":\"', Ve_DocVenta.Serie, '\",\"Numero\":',Ve_DocVenta.Numero,',\"Total\":',ROUND(((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento ),2),' }' ) SEPARATOR ', '),
            ']'
        ) AS Notas 
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
        WHERE Ve_DocVenta.idTipoDoc=11 AND Ve_DocVenta.Anulado=0
        GROUP BY Ve_DocVenta.NroComprobanteModifica )  AS DocVentaNotas ON DocVentaNotas.NroComprobanteModifica = CONCAT(Ve_DocVenta.Serie,'-',Ve_DocVenta.Numero)
            
        WHERE Ve_DocVenta.EsCredito=1 AND Ve_DocVenta.Anulado=0 ";

        $select .= " AND Ve_DocVenta.IdAlmacen = " . $request->getParam('idalmacen');
        $select .= " AND (Ve_DocVentaCliente.Cliente LIKE '%" . $request->getParam('q') . "%'";
        $select .= " OR Ve_DocVentaCliente.DniRuc LIKE '%" . $request->getParam('q') . "%') ";

        $select .= "GROUP BY
        Ve_DocVenta.IdDocVenta,
        Ve_DocVenta.FechaDoc,
        Ve_DocVenta.FechaCredito,
        Ve_DocVentaTipoDoc.CodSunat,
        Ve_DocVenta.Serie,
        Ve_DocVenta.Numero
        HAVING ROUND(Saldo,2)>0
        
        ORDER BY FechaDoc";

        $limit = $request->getParam('limit') ? $request->getParam('limit') :  30;
        if ($limit) {
            $offset = 0;
            if ($request->getParam('page')) {
                $page = $request->getParam('page');
                $offset = (--$page) * $limit;
            }
            $select .= " LIMIT " . $limit;
            $select .= " OFFSET " . $offset;
        }
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/clientes/deuda/count', function (Request $request, Response $response, array $args) {
    $select = "SELECT COUNT(*) as total
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet On Ve_DocVenta.IdDocVenta=Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc On Ve_DocVenta.IdTipoDoc=Ve_DocVentaTipoDoc.IdTipoDoc
        INNER JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente
        WHERE EsCredito=1";
    $select .= " AND Ve_DocVenta.IdAlmacen = " . $request->getParam('idalmacen');
    $select .= "
        GROUP BY
        Ve_DocVenta.IdDocVenta,
        Ve_DocVenta.FechaDoc,
        Ve_DocVenta.FechaCredito,
        Ve_DocVentaTipoDoc.CodSunat,
        Ve_DocVenta.Serie,
        Ve_DocVenta.Numero
        HAVING ROUND(SUM((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento),2) -
        ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='VE' AND Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)),2)>0
        ORDER BY FechaDoc;";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});

$app->get('/proveedores/deuda/count', function (Request $request, Response $response, array $args) {
    $select = "SELECT COUNT(*) AS total
    From Lo_Movimiento
    INNER JOIN Lo_MovimientoDetalle ON Lo_Movimiento.Hash=Lo_MovimientoDetalle.hashMovimiento
    INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo=Lo_MovimientoTipo.IdMovimientoTipo
    INNER JOIN Lo_Proveedor ON Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
    Where EsCredito=1
    Group by
    Lo_Movimiento.Hash,
    Lo_Movimiento.MovimientoFecha,
    Lo_Movimiento.FechaVenCredito,
    Lo_MovimientoTipo.CodSunat,
    Lo_Movimiento.Serie,
    Lo_Movimiento.Numero
    HAVING Round(Sum(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) -
    Round((SELECT IfNull((Select Sum(Cb_CajaBancoDet.Importe) From Cb_CajaBancoDet Where Tipo='MO' And Cb_CajaBancoDet.Hash=Lo_Movimiento.Hash),0)),2)>0
    Order by MovimientoFecha;";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});

$app->get('/proveedores/deudas', function (Request $request, Response $response, array $args) {
    $select = "SELECT
    Lo_Movimiento.Hash as Id,
    Lo_Proveedor.Ruc,
    Lo_Proveedor.Proveedor,
    Lo_Movimiento.MovimientoFecha as FechaDoc,
    Lo_Movimiento.FechaVenCredito as FechaCredito,
    CONCAT(CASE WHEN Lo_MovimientoTipo.CodSunat ='01' THEN
        'F'
    ELSE
        CASE WHEN Lo_MovimientoTipo.CodSunat='03' THEN
            'B'
        ELSE
            CASE WHEN Lo_MovimientoTipo.CodSunat='12' THEN
                'T'
            ELSE
                'OTRO'
            END
        END
    END,Lo_Movimiento.Serie,'-' , convert(Lo_Movimiento.Numero, char) )as Correlativo,
    Round(Sum(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) as Total,
    Round((SELECT IfNull((SELECT Sum(Cb_CajaBancoDet.Importe) From Cb_CajaBancoDet Where Tipo='MO' And Cb_CajaBancoDet.Hash=Lo_Movimiento.Hash),0)),2) as Aplicado,
    Round(SUM(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) -
    Round((SELECT IfNull((SELECT Sum(Cb_CajaBancoDet.Importe) From Cb_CajaBancoDet Where Tipo='MO' And Cb_CajaBancoDet.Hash=Lo_Movimiento.Hash),0)),2) as Saldo
    From Lo_Movimiento
    Inner Join Lo_MovimientoDetalle On Lo_Movimiento.Hash=Lo_MovimientoDetalle.hashMovimiento
    Inner Join Lo_MovimientoTipo On Lo_Movimiento.IdMovimientoTipo=Lo_MovimientoTipo.IdMovimientoTipo
    INNER JOIN Lo_Proveedor On Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
    Where EsCredito=1";

    $select .= " AND (Lo_Proveedor.Ruc LIKE '%" . $request->getParam('q') . "%'";
    $select .= " OR Lo_Proveedor.Proveedor LIKE '%" . $request->getParam('q') . "%') ";

    $select .= "        
    Group by
    Lo_Movimiento.Hash,
    Lo_Movimiento.MovimientoFecha,
    Lo_Movimiento.FechaVenCredito,
    Lo_MovimientoTipo.CodSunat,
    Lo_Movimiento.Serie,
    Lo_Movimiento.Numero
    HAVING Round(Sum(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) -
    Round((SELECT IfNull((Select Sum(Cb_CajaBancoDet.Importe) From Cb_CajaBancoDet Where Tipo='MO' And Cb_CajaBancoDet.Hash=Lo_Movimiento.Hash),0)),2)>0
    Order by MovimientoFecha";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/reporte/deudaclientes2', function (Request $request, Response $response, array $args) use ($app) {
    $idalmacen = $request->getParam('idalmacen');
    $limit = $request->getParam('limit') ? $request->getParam('limit') :  30;
    $res = $app->subRequest('GET', 'cliente/deudas', 'idalmacen=' . $idalmacen . '&limit=' . $limit);



    $deudacliente = (string) $res->getBody();
    $deudacliente = json_decode($deudacliente, true);

    $excel = new Spreadsheet();
    //$sheet = $excel->setActiveSheetIndex(0);
    $sheet = $excel->getActiveSheet();
    $sheet->setCellValue('A1', 'DNI / RUC');
    $sheet->setCellValue('B1', 'CLIENTE');
    $sheet->setCellValue('C1', 'SERIE');
    $sheet->setCellValue('D1', 'TOTAL');
    $sheet->setCellValue('E1', 'APLICADO');
    $sheet->setCellValue('F1', 'APLICADO NOTA CRED.');
    $sheet->setCellValue('G1', 'SALDO');
    $sheet->setCellValue('H1', 'FECHA EMISION');
    $sheet->setCellValue('I1', 'FECHA VENCIMIENTO');
    $cont = 3;
    foreach ($deudacliente as $prod) {
        $sheet->setCellValue('A' . $cont, $prod['DniRuc']);
        $sheet->setCellValue('B' . $cont, $prod['Cliente']);
        $sheet->setCellValue('C' . $cont, $prod['Correlativo']);
        $sheet->setCellValue('D' . $cont, 'S/. ' . $prod['Total']);
        $sheet->setCellValue('E' . $cont, 'S/. ' . $prod['Aplicado']);
        $sheet->setCellValue('F' . $cont, 'S/. ' . $prod['NotaCreApli']);
        $sheet->setCellValue('G' . $cont, 'S/. ' . $prod['Saldo']);
        $sheet->setCellValue('H' . $cont, date("d/m/Y", strtotime($prod['FechaDoc'])));
        $sheet->setCellValue('I' . $cont, date("d/m/Y", strtotime($prod['FechaCredito'])));
        $cont += 1;
    }

    aplicarIdentidadEmpresaExcel($excel, $this->db);
    $excelWriter = new Xlsx($excel);

    $fileName = 'deudacliente2' . getNow('Y-m-d-H-i-s') .  '.xlsx';
    $excelFileName = __DIR__ . '/reporte/' . $fileName;
    $excelWriter->save($excelFileName);

    echo "<script>window.location.href = '/api/reporte/" . $fileName . "'</script>";
    exit;
});

$app->get('/reporte/deudaproveedores', function (Request $request, Response $response, array $args) use ($app) {

    $res = $app->subRequest('GET', 'proveedores/deudas');


    $deudacliente = (string) $res->getBody();
    $deudacliente = json_decode($deudacliente, true);

    $excel = new Spreadsheet();
    //$sheet = $excel->setActiveSheetIndex(0);
    $sheet = $excel->getActiveSheet();
    $sheet->setCellValue('A1', 'DNI / RUC');
    $sheet->setCellValue('B1', 'PROVEEDOR');
    $sheet->setCellValue('C1', 'SERIE');
    $sheet->setCellValue('D1', 'TOTAL');
    $sheet->setCellValue('E1', 'APLICADO');
    $sheet->setCellValue('F1', 'SALDO');
    $sheet->setCellValue('G1', 'FECHA EMISION');
    $sheet->setCellValue('H1', 'FECHA VENCIMIENTO');

    $cont = 3;
    foreach ($deudacliente as $prod) {
        $sheet->setCellValue('A' . $cont, $prod['Ruc']);
        $sheet->setCellValue('B' . $cont, $prod['Proveedor']);
        $sheet->setCellValue('C' . $cont, $prod['Correlativo']);
        $sheet->setCellValue('D' . $cont, 'S/. ' . $prod['Total']);
        $sheet->setCellValue('E' . $cont, 'S/. ' . $prod['Aplicado']);
        $sheet->setCellValue('F' . $cont, 'S/. ' . $prod['Saldo']);
        $sheet->setCellValue('G' . $cont, date("d/m/Y", strtotime($prod['FechaDoc'])));
        $sheet->setCellValue('H' . $cont, date("d/m/Y", strtotime($prod['FechaCredito'])));
        $cont += 1;
    }

    aplicarIdentidadEmpresaExcel($excel, $this->db);
    $excelWriter = new Xlsx($excel);

    $fileName = 'deudaproveedores' . getNow('Y-m-d-H-i-s') .  '.xlsx';
    $excelFileName = __DIR__ . '/reporte/' . $fileName;
    $excelWriter->save($excelFileName);

    echo "<script>window.location.href = '/api/reporte/" . $fileName . "'</script>";
    exit;
});

$app->get('/cliente/pagos', function (Request $request, Response $response, array $args) {
    $idCliente = $request->getParam('idCliente');
    $select = "SELECT Cb_CajaBanco.*,Cb_Cuenta.Cuenta FROM Cb_CajaBanco INNER JOIN Cb_Cuenta ON Cb_CajaBanco.IdCuenta = Cb_Cuenta.IdCuenta
    WHERE Cb_CajaBanco.IdCliente=$idCliente AND Cb_CajaBanco.EsDelVendedor=1 ORDER BY Cb_CajaBanco.FechaDoc DESC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->post('/cliente/deudas/pagar', function (Request $request, Response $response) {
    $cajaBanco = (array) $request->getParam('cajaBanco');
    $documentos = (array) $request->getParam('documentos');
    $vendedorRequest = (array) $request->getParam('vendedor');
    $cliente = isset($cajaBanco['cliente']) ? (array) $cajaBanco['cliente'] : array();
    $idCliente = isset($cliente['IdCliente']) ? (int) $cliente['IdCliente'] : 0;
    $idCuentaDefault = isset($cajaBanco['IdCuenta']) ? (int) $cajaBanco['IdCuenta'] : 1;
    $idAlmacen = (int) $request->getParam('idAlmacen');

    $usuarioSesion = isset($_SESSION['user']) ? trim((string) $_SESSION['user']) : '';
    $usuarioReg = isset($vendedorRequest['Usuario'])
        ? trim((string) $vendedorRequest['Usuario'])
        : $usuarioSesion;

    if ($idCliente <= 0) {
        return $response->withJson(array('error' => 'Seleccione un cliente válido.'), 422);
    }
    if ($idCuentaDefault <= 0) {
        return $response->withJson(array('error' => 'Seleccione la cuenta donde ingresó el pago.'), 422);
    }
    if ($usuarioReg === '') {
        return $response->withJson(array('error' => 'Seleccione el vendedor responsable del cobro.'), 422);
    }

    $concepto = isset($cajaBanco['Concepto']) ? trim((string) $cajaBanco['Concepto']) : '';
    if ($concepto === '') {
        $concepto = 'PAGO DE DEUDA DE CLIENTE';
    }
    $concepto = function_exists('mb_substr')
        ? mb_substr($concepto, 0, 255, 'UTF-8')
        : substr($concepto, 0, 255);

    $fechaPago = isset($cajaBanco['FechaDoc']) ? trim((string) $cajaBanco['FechaDoc']) : '';
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaPago)) {
        $hora = date_create('now', timezone_open('America/Lima'))->format('H:i:s');
        $fechaDoc = $fechaPago . ' ' . $hora;
    } else {
        $fechaDoc = getNow();
    }

    $aplicacionesPorDocumento = array();
    foreach ($documentos as $doc) {
        $doc = (array) $doc;
        $idDocVenta = isset($doc['IdDocVenta']) ? (int) $doc['IdDocVenta'] : 0;
        $importe = isset($doc['aplicar']) ? round((float) $doc['aplicar'], 2) : 0;
        if ($idDocVenta <= 0 || $importe <= 0) {
            continue;
        }

        $idCuenta = isset($doc['cuenta']) && (int) $doc['cuenta'] > 0
            ? (int) $doc['cuenta']
            : $idCuentaDefault;
        if (isset($aplicacionesPorDocumento[$idDocVenta])) {
            if ($aplicacionesPorDocumento[$idDocVenta]['IdCuenta'] !== $idCuenta) {
                return $response->withJson(array(
                    'error' => 'Cada documento debe pagarse en una sola cuenta.'
                ), 422);
            }
            $aplicacionesPorDocumento[$idDocVenta]['Importe'] = round(
                $aplicacionesPorDocumento[$idDocVenta]['Importe'] + $importe,
                2
            );
        } else {
            $aplicacionesPorDocumento[$idDocVenta] = array(
                'IdDocVenta' => $idDocVenta,
                'IdCuenta' => $idCuenta,
                'Importe' => $importe
            );
        }
    }
    $aplicaciones = array_values($aplicacionesPorDocumento);

    if (count($aplicaciones) === 0) {
        return $response->withJson(array('error' => 'Ingrese un importe mayor a cero.'), 422);
    }

    try {
        $this->db->beginTransaction();

        if ($idAlmacen <= 0) {
            $almacenStmt = $this->db->prepare("SELECT IdAlmacen FROM Seg_Usuario
                WHERE LOWER(Usuario) = LOWER(:usuario) AND (Anulado != 1 OR Anulado IS NULL)
                LIMIT 1");
            $almacenStmt->execute(array('usuario' => $usuarioReg));
            $idAlmacenUsuario = $almacenStmt->fetchColumn();
            $idAlmacen = $idAlmacenUsuario !== false ? (int) $idAlmacenUsuario : 0;
        }

        $cuentasUsadas = array();
        foreach ($aplicaciones as $aplicacion) {
            $cuentasUsadas[$aplicacion['IdCuenta']] = true;
        }
        $cuentaStmt = $this->db->prepare("SELECT IdCuenta FROM Cb_Cuenta
            WHERE IdCuenta = :idCuenta AND (Anulado = 0 OR Anulado IS NULL)");
        foreach (array_keys($cuentasUsadas) as $idCuenta) {
            $cuentaStmt->execute(array('idCuenta' => $idCuenta));
            if (!$cuentaStmt->fetchColumn()) {
                throw new InvalidArgumentException('La cuenta de ingreso seleccionada no está disponible.');
            }
        }

        $ventaStmt = $this->db->prepare("SELECT IdDocVenta, IdCliente, EsCredito, Anulado, Serie, Numero
            FROM Ve_DocVenta WHERE IdDocVenta = :idDocVenta FOR UPDATE");
        $totalStmt = $this->db->prepare("SELECT ROUND(COALESCE(SUM((Precio * Cantidad) - COALESCE(Descuento, 0)), 0), 2)
            FROM Ve_DocVentaDet WHERE IdDocVenta = :idDocVenta");
        $notasStmt = $this->db->prepare("SELECT ROUND(COALESCE(SUM((detalle.Precio * detalle.Cantidad) - COALESCE(detalle.Descuento, 0)), 0), 2)
            FROM Ve_DocVenta nota
            INNER JOIN Ve_DocVentaDet detalle ON detalle.IdDocVenta = nota.IdDocVenta
            WHERE nota.IdTipoDoc = 11
              AND nota.NroComprobanteModifica = :comprobante
              AND COALESCE(nota.Anulado, 0) = 0");
        $aplicadoStmt = $this->db->prepare("SELECT ROUND(COALESCE(SUM(Importe), 0), 2)
            FROM Cb_CajaBancoDet WHERE Tipo = 'VE' AND IdDocDet = :idDocVenta");

        $aplicacionesValidadas = array();
        foreach ($aplicaciones as $aplicacion) {
            $ventaStmt->execute(array('idDocVenta' => $aplicacion['IdDocVenta']));
            $venta = $ventaStmt->fetch(PDO::FETCH_ASSOC);
            if (!$venta || (int) $venta['IdCliente'] !== $idCliente || (int) $venta['EsCredito'] !== 1 || (int) $venta['Anulado'] === 1) {
                throw new InvalidArgumentException('Uno de los documentos ya no es una deuda válida del cliente.');
            }

            $totalStmt->execute(array('idDocVenta' => $aplicacion['IdDocVenta']));
            $totalDocumento = round((float) $totalStmt->fetchColumn(), 2);
            $notasStmt->execute(array('comprobante' => $venta['Serie'] . '-' . $venta['Numero']));
            $notasCredito = round((float) $notasStmt->fetchColumn(), 2);
            $aplicadoStmt->execute(array('idDocVenta' => $aplicacion['IdDocVenta']));
            $importeAplicado = round((float) $aplicadoStmt->fetchColumn(), 2);
            $saldoActual = round(max(0, $totalDocumento - $notasCredito - $importeAplicado), 2);

            if ($aplicacion['Importe'] > $saldoActual + 0.009) {
                throw new InvalidArgumentException(
                    'El pago del documento ' . $venta['Serie'] . '-' . $venta['Numero'] .
                    ' supera su saldo pendiente de S/ ' . number_format($saldoActual, 2, '.', '') . '.'
                );
            }

            $aplicacion['SaldoAnterior'] = $saldoActual;
            $aplicacion['SaldoNuevo'] = round($saldoActual - $aplicacion['Importe'], 2);
            $aplicacionesValidadas[] = $aplicacion;
        }

        $aplicacionesPorCuenta = array();
        foreach ($aplicacionesValidadas as $aplicacion) {
            $idCuenta = $aplicacion['IdCuenta'];
            if (!isset($aplicacionesPorCuenta[$idCuenta])) {
                $aplicacionesPorCuenta[$idCuenta] = array();
            }
            $aplicacionesPorCuenta[$idCuenta][] = $aplicacion;
        }

        $cajaStmt = $this->db->prepare("INSERT INTO Cb_CajaBanco
            (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, Anulado,
             UsuarioReg, FechaReg, IdCliente, EsDelVendedor, IdAlmacen)
            VALUES
            (2, :idCuenta, :fechaDoc, :concepto, :importe, 0,
             :usuarioReg, :fechaReg, :idCliente, 1, :idAlmacen)");
        $detalleStmt = $this->db->prepare("INSERT INTO Cb_CajaBancoDet
            (IdCajaBanco, IdDocDet, Importe, Tipo)
            VALUES (:idCajaBanco, :idDocVenta, :importe, 'VE')");

        $idsCajaBanco = array();
        $importeTotal = 0;
        foreach ($aplicacionesPorCuenta as $idCuenta => $aplicacionesCuenta) {
            $importeCuenta = 0;
            foreach ($aplicacionesCuenta as $aplicacion) {
                $importeCuenta += $aplicacion['Importe'];
            }
            $importeCuenta = round($importeCuenta, 2);

            $cajaStmt->execute(array(
                'idCuenta' => $idCuenta,
                'fechaDoc' => $fechaDoc,
                'concepto' => $concepto,
                'importe' => $importeCuenta,
                'usuarioReg' => $usuarioReg,
                'fechaReg' => getNow(),
                'idCliente' => $idCliente,
                'idAlmacen' => $idAlmacen > 0 ? $idAlmacen : null
            ));
            $idCajaBanco = (int) $this->db->lastInsertId();
            $idsCajaBanco[] = $idCajaBanco;

            foreach ($aplicacionesCuenta as $aplicacion) {
                $detalleStmt->execute(array(
                    'idCajaBanco' => $idCajaBanco,
                    'idDocVenta' => $aplicacion['IdDocVenta'],
                    'importe' => $aplicacion['Importe']
                ));
            }
            $importeTotal += $importeCuenta;
        }

        $this->db->commit();
        return $response->withJson(array(
            'ok' => true,
            'idCajaBanco' => $idsCajaBanco[0],
            'idsCajaBanco' => $idsCajaBanco,
            'importe' => round($importeTotal, 2),
            'documentos' => $aplicacionesValidadas
        ));
    } catch (InvalidArgumentException $error) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        return $response->withJson(array('error' => $error->getMessage()), 422);
    } catch (Throwable $error) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        error_log('Pago de deuda de cliente: ' . $error->getMessage());
        return $response->withJson(array(
            'error' => 'No se pudo registrar el pago. La deuda y la caja no fueron modificadas.'
        ), 500);
    }
});

$app->get('/ranking/clientes', function (Request $request, Response $response, array $args) {
    $idAlmacen = max(0, (int)$request->getParam('idalmacen'));
    $q = trim((string)$request->getParam('q'));
    $fechaInicio = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$request->getParam('fechaInicio')) ? $request->getParam('fechaInicio') : date('Y-m-01');
    $fechaFin = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$request->getParam('fechaFin')) ? $request->getParam('fechaFin') : date('Y-m-d');
    $limit = max(1, min(1000, (int)($request->getParam('limit') ?: 15)));
    $page = max(1, (int)($request->getParam('page') ?: 1));
    $offset = ($page - 1) * $limit;
    $select = "SELECT
        Ve_DocVentaCliente.DniRuc,
        Ve_DocVentaCliente.Cliente,
        Ve_DocVentaCliente.Telefono,
        Ve_DocVentaCliente.Puntos,
        Ve_DocVentaCliente.FechaNacimiento,
        Round(Sum((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento ),2) as Total
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.IdDocVenta=Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente=Ve_DocVentaCliente.IdCliente
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc=Ve_DocVentaTipoDoc.IdTipoDoc
        WHERE COALESCE(Ve_DocVenta.Anulado,0)=0
          AND Ve_DocVentaTipoDoc.CodSunat IN ('01','03','12')
          AND Ve_DocVenta.FechaDoc BETWEEN :fechaInicio AND :fechaFin
          AND (:idAlmacen=0 OR Ve_DocVenta.IdAlmacen=:idAlmacenFiltro)
          AND (Ve_DocVentaCliente.Cliente LIKE :qCliente OR Ve_DocVentaCliente.DniRuc LIKE :qDocumento)
        GROUP BY Ve_DocVentaCliente.IdCliente
        ORDER BY Total DESC,Ve_DocVentaCliente.Puntos DESC
        LIMIT {$limit} OFFSET {$offset}";
    $stmt = $this->db->prepare($select);
    $stmt->execute(array('fechaInicio'=>$fechaInicio.' 00:00:00','fechaFin'=>$fechaFin.' 23:59:59','idAlmacen'=>$idAlmacen,'idAlmacenFiltro'=>$idAlmacen,'qCliente'=>'%'.$q.'%','qDocumento'=>'%'.$q.'%'));
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/ranking/clientes/count', function (Request $request, Response $response, array $args) {
    $idAlmacen=max(0,(int)$request->getParam('idalmacen'));$q=trim((string)$request->getParam('q'));
    $fechaInicio=preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)$request->getParam('fechaInicio'))?$request->getParam('fechaInicio'):date('Y-m-01');
    $fechaFin=preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)$request->getParam('fechaFin'))?$request->getParam('fechaFin'):date('Y-m-d');
    $select="SELECT COUNT(DISTINCT Ve_DocVenta.IdCliente) total FROM Ve_DocVenta
      INNER JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente=Ve_DocVentaCliente.IdCliente
      INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc=Ve_DocVentaTipoDoc.IdTipoDoc
      WHERE COALESCE(Ve_DocVenta.Anulado,0)=0 AND Ve_DocVentaTipoDoc.CodSunat IN ('01','03','12')
      AND Ve_DocVenta.FechaDoc BETWEEN :fechaInicio AND :fechaFin
      AND (:idAlmacen=0 OR Ve_DocVenta.IdAlmacen=:idAlmacenFiltro)
      AND (Ve_DocVentaCliente.Cliente LIKE :qCliente OR Ve_DocVentaCliente.DniRuc LIKE :qDocumento)";
    $stmt=$this->db->prepare($select);
    $stmt->execute(array('fechaInicio'=>$fechaInicio.' 00:00:00','fechaFin'=>$fechaFin.' 23:59:59','idAlmacen'=>$idAlmacen,'idAlmacenFiltro'=>$idAlmacen,'qCliente'=>'%'.$q.'%','qDocumento'=>'%'.$q.'%'));
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});











// DATOS GLOBALES DE LA EMPRESA
$empresaFiscal = array();
$empresaMeta = array();
try {
    $empresaPdo = appPdoConnection();
    $empresaFiscal = $empresaPdo->query('SELECT * FROM GEN_EMPRESA ORDER BY IDEMPRESA ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: array();
    if (!empty($empresaFiscal['IDEMPRESA'])) {
        $empresaMetaStmt = $empresaPdo->prepare('SELECT MetaKey, MetaValue FROM Gen_EmpresaConfig WHERE IdEmpresa = ? ORDER BY IdEmpresaConfig ASC');
        $empresaMetaStmt->execute(array($empresaFiscal['IDEMPRESA']));
        foreach ($empresaMetaStmt->fetchAll(PDO::FETCH_ASSOC) as $empresaMetaRow) $empresaMeta[$empresaMetaRow['MetaKey']] = $empresaMetaRow['MetaValue'];
    }
} catch (Throwable $empresaError) {
    error_log('[API-EMPRESA] ' . $empresaError->getMessage());
}
$empresaDato = function ($key, $default = '') use ($empresaFiscal, $empresaMeta) {
    if (isset($empresaMeta[$key]) && trim((string)$empresaMeta[$key]) !== '') return $empresaMeta[$key];
    if (isset($empresaFiscal[$key]) && trim((string)$empresaFiscal[$key]) !== '') return $empresaFiscal[$key];
    return $default;
};
define('NRO_DOCUMENTO_EMPRESA', $empresaDato('RUC', '10421693086'));
define('TIPO_DOCUMENTO_EMPRESA', '6'); //1 DNI 6 RUC
define('TIPO_PROCESO', $empresaDato('sunat_entorno', 'FE_BETA') === 'FE_PRODUCCION' ? '01' : '03'); //01 PRODUCCION 03 BETA
define('RAZON_SOCIAL_EMPRESA', $empresaDato('RAZONSOCIAL', ''));
define('NOMBRE_COMERCIAL_EMPRESA', $empresaDato('nombre_comercial', ''));
define('CODIGO_UBIGEO_EMPRESA', $empresaDato('UBIGEO', ''));
define('DIRECCION_EMPRESA', $empresaDato('DIRECCION', ''));
define('DEPARTAMENTO_EMPRESA', $empresaDato('DEPARTAMENTO', ''));
define('PROVINCIA_EMPRESA', $empresaDato('PROVINCIA', ''));
define('DISTRITO_EMPRESA', $empresaDato('DISTRITO', ''));
define('TELEFONOS_EMPRESA', trim($empresaDato('TELEFONO', '') . ' ' . $empresaDato('CELULAR', '')));

define('CODIGO_PAIS_EMPRESA', 'PE');
define('USUARIO_SOL_EMPRESA', $empresaDato('usuario_sol', ''));
define('PASS_SOL_EMPRESA', $empresaDato('clave_sol', ''));
define('EMISION_AUTO', "1");

$app->post('/emitirelectronico', function (Request $request, Response $response) {
    include_once("../controllers/NumerosEnLetras/NumerosEnLetras.php");

    $docVenta = $request->getParam('docVenta');

    // Obteber venta.
    $select = "SELECT * FROM Ve_DocVenta WHERE idDocVenta=" . $docVenta['idDocVenta'];
    $stmt = $this->db->query($select);
    $stmt->execute();
    $venta = $stmt->fetch();

    $new = new api_sunat();

    $detalle = array();
    $json = array();
    $n = 0;
    $descuento = 0;

    foreach ($docVenta['productos'] as $producto) {
        $n = $n + 1;

        // $igv = $docVenta['TieneIgv'] ? round($producto['Total'] * 0.18, 2) : 0;
        $igv = $docVenta['TieneIgv'] ? round($producto['Total'] - ($producto['Total'] / 1.18), 2) : 0;
        $subtotal = $docVenta['TieneIgv'] ? $producto['Total'] - $igv : $producto['Total'];

        $subtotal = $producto['Descuento'] > 0 ? $subtotal - $producto['Descuento'] : $subtotal;

        $json['txtITEM'] = $n;
        $json["txtUNIDAD_MEDIDA_DET"] = $producto['CodigoMedicion'];
        $json["txtUNIDAD_MEDIDA_NOMBRE_DET"] = $producto['ProductoMedicion'];
        $json["txtCANTIDAD_DET"] = $producto['Cantidad'];
        //$json["txtPRECIO_DET"] = $producto['Precio'];
        $json["txtPRECIO_DET"] = round(($subtotal + $igv) / $producto['Cantidad'], 4);
        $json["txtPRECIO_DESC_DET"] = $producto['Descuento'];
        $json["txtSUB_TOTAL_DET"] = $subtotal;  //PRECIO * CANTIDAD
        $json["txtPRECIO_TIPO_CODIGO"] = "01"; // 02 valor referencial unitario en operaciones no onerosas
        $json["txtIGV"] = $igv;
        $json["txtISC"] = "0";
        //$json["txtIMPORTE_DET"] = $producto['Total']; //rowData.IMPORTE; //SUB_TOTAL + IGV
        $json["txtIMPORTE_DET"] = $subtotal; //rowData.IMPORTE; //SUB_TOTAL + IGV
        $json["txtCOD_TIPO_OPERACION"] = $docVenta['CodigoIgv']; //20 si es exonerado
        $json["txtCODIGO_DET"] = $producto['CodigoBarra'];
        $partesDescripcion = array();
        $nombreProducto = trim((string) $producto['Producto']);
        if ($nombreProducto !== '') $partesDescripcion[] = $nombreProducto;

        $atributosProducto = array(
            'MARCA' => isset($producto['ProductoMarca']) ? $producto['ProductoMarca'] : '',
            'MODELO' => isset($producto['ProductoModelo']) ? $producto['ProductoModelo'] : '',
            'COLOR' => isset($producto['Color']) ? $producto['Color'] : '',
            'TALLA' => isset($producto['ProductoTalla']) ? $producto['ProductoTalla'] : '',
            'GENERO' => isset($producto['Genero']) ? $producto['Genero'] : '',
            'BOTAPIE' => isset($producto['Botapie']) ? $producto['Botapie'] : ''
        );
        foreach ($atributosProducto as $etiqueta => $valor) {
            $valor = trim((string) $valor);
            if ($valor !== '') {
                $partesDescripcion[] = $etiqueta . ': ' . $valor;
            }
        }
        $json["txtDESCRIPCION_DET"] = implode(' | ', $partesDescripcion);

        $json["txtMEDICION"] = $producto['ProductoMedicion'];
        $json["txtLABORATORIO"] = $producto['ProductoMarca'];
        $json["txtLOTE"] = $producto['IdLote'];
        $json["txtFECHAVEN"] = $producto['FechaVen'];
        $json["txtFECHAVENCIMIENTO"] = $producto['FechaVencimiento'];
        $json["txtDESCRIPCION_DET_DESCRIP"] = trim($producto['Descripcion']);

        //$json["txtPRECIO_SIN_IGV_DET"] = round($producto['Precio'] - ($producto['Precio'] * 0.18), 2);
        $json["txtPRECIO_SIN_IGV_DET"] =  $docVenta['TieneIgv'] ? round($producto['Precio'] / 1.18, 4) : $producto['Precio'];

        $detalle[] = $json;
        $descuento += $producto['Descuento'];
    }

    // $gravadas = $docVenta['TieneIgv'] ? $docVenta['Total'] - ($docVenta['Total'] * 0.18) : $docVenta['Total'];
    $gravadas = $docVenta['TieneIgv'] ? round($docVenta['Total'] / 1.18, 2) : $docVenta['Total'];
    // $subtotal = ($descuento > 0) ? $gravadas + $descuento : $gravadas;
    $subtotal = $gravadas;

    //$total = ($descuento > 0) ? $subtotal - $descuento : '0';

    $data = array(
        "txtTIPO_OPERACION" => "0101", // corregir esto despues
        "txtTOTAL_DESCUENTO" => $descuento,
        "txtSUB_TOTAL" => $subtotal,
        "txtPOR_IGV" => $docVenta['TieneIgv'] ? "18" : "0",
        // "txtTOTAL_IGV"=> $docVenta['TieneIgv'] ? round($docVenta['Total'] * 0.18, 2) : 0,
        "txtTOTAL_IGV" => $docVenta['TieneIgv'] ? round($docVenta['Total'] - ($docVenta['Total'] / 1.18), 2) : 0,
        "txtTOTAL" => $docVenta['Total'],
        "txtTOTAL_LETRAS" => NumerosEnLetras::convertir(number_format($docVenta['Total'], 2, '.', ''), 'SOLES', true),
        "txtNRO_COMPROBANTE" => $docVenta['Serie'] . "-" . $docVenta['Numero'], //
        "txtFECHA_DOCUMENTO" => date("Y-m-d", strtotime($docVenta['FechaDoc'])),
        "txtFECHA_CREDITO" => date("Y-m-d", strtotime($docVenta['FechaCredito'])),
        "txtFECHA_VTO" => date("Y-m-d", strtotime($docVenta['FechaDoc'])),
        "txtCOD_TIPO_DOCUMENTO" => $docVenta['CodSunat'], //01=factura,03=boleta,07=notacrediro,08=notadebito
        "txtCOD_MONEDA" => 'PEN', //PEN= PERU
        "txtVENDEDOR" => $docVenta['UsuarioReg'], //VENDEDOR
        "txtOBSERVACION" => isset($docVenta['Observacion']) ? $docVenta['Observacion'] : '',
        "txtPLACA" => isset($docVenta['Placa']) ? $docVenta['Placa'] : '',
        //'detalle_forma_pago' => [
        //    ["COD_FORMA_PAGO" => "Contado"]
        //],
        "txtALMACEN" => $docVenta['Almacen'],
        "txtALMACEN_DIRECCION" => $docVenta['DireccionAlmacen'],
        "txtALMACEN_ES_PRINCIPAL" => $docVenta['EsAlmacenPrincipal'],
        //==========documentos de referencia(nota credito, debito)=============
        "txtTIPO_COMPROBANTE_MODIFICA" => isset($docVenta['CodSunatModifica']) && $docVenta['CodSunatModifica'] ? $docVenta['CodSunatModifica'] : "", //aqui completar
        "txtNRO_DOCUMENTO_MODIFICA" => isset($docVenta['NroComprobanteModifica']) && $docVenta['NroComprobanteModifica'] ? $docVenta['NroComprobanteModifica'] : "",
        "txtCOD_TIPO_MOTIVO" => isset($docVenta['NotaIdMotivo']) && $docVenta['NotaIdMotivo'] ? $docVenta['NotaIdMotivo'] : "",
        "txtDESCRIPCION_MOTIVO" => isset($docVenta['NotaDescMotivo']) && $docVenta['NotaDescMotivo'] ? $docVenta['NotaDescMotivo'] : "", //$("[name='txtID_MOTIVO']
        //=================datos del cliente=================8
        "txtNRO_DOCUMENTO_CLIENTE" => trim($docVenta['DniRuc']),
        "txtRAZON_SOCIAL_CLIENTE" => trim($docVenta['Cliente']),
        "txtNOMBRE_COMERCIAL_CLIENTE" => $docVenta['NombreComercial'],
        "txtTIPO_DOCUMENTO_CLIENTE" => strlen($docVenta['DniRuc']) > 9 ? "6" : "1", //1 DNI 6 RUC
        "txtDIRECCION_CLIENTE" => trim($docVenta['Direccion']),
        "txtCIUDAD_CLIENTE" => "",
        "txtCOD_PAIS_CLIENTE" => "PE",
        //=================datos de LA EMPRESA=================
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
        "txtTIPO_PROCESO" => TIPO_PROCESO, //01 PRODUCCION 03 BETA
        "detalle" => $detalle,
        //"detalle" => []
    );
    // if($docVenta['CodSunat'] == '07'){
    //     unset($data['detalle_forma_pago']);
    // }
    if ($docVenta['CodigoIgv'] == "20") { // 20 = exonerado Igv
        $data["txtTOTAL_GRAVADAS"] = 0;
        $data["txtTOTAL_EXONERADAS"] = $gravadas;
    } else {
        $data["txtTOTAL_GRAVADAS"] = $gravadas;
        $data["txtTOTAL_EXONERADAS"] = 0;
    }

    // forma de pago
    if ($venta['EsCredito']) {
        if (date('Y-m-d', strtotime($venta['FechaCredito'])) == date('Y-m-d', strtotime($venta['FechaDoc']))) {
            return $response->withJson(array("msj_sunat" => 'Error, La fecha de credito debe ser diferente a la del documento', "Estado" => 1));
        }
        $data['detalle_forma_pago'] = [
            [
                "COD_FORMA_PAGO" => "Credito",
                "MONTO_FORMA_PAGO" => $gravadas,
                "FECHA_FORMA_PAGO" => date('Y-m-d', strtotime($venta['FechaCredito']))
            ],
            [
                "COD_FORMA_PAGO" => "Cuota001",
                "MONTO_FORMA_PAGO" => $gravadas,
                "FECHA_FORMA_PAGO" => date('Y-m-d', strtotime($venta['FechaCredito']))
            ]
        ];
    } else {
        $data['detalle_forma_pago'] = [
            ["COD_FORMA_PAGO" => "Contado"]
        ];
    }

    if ($request->getParam('generarXml')) {
        $data['generarXml'] = true;
        $resultado = $new->sendPostCPE(json_encode($data));
        return $response->withJson([
            'success' => true
        ]);
    }
    $data['generarXml'] = false;

    $resultado = $new->sendPostCPE(json_encode($data));
    $me = json_decode($resultado, true);

    // Datos $me
    $me['data'] = array(
        "txtNRO_COMPROBANTE" => $data['txtNRO_COMPROBANTE']
    );

    $estado = 1;
    if ($me['cod_sunat'] == '0') {
        $estado = 2;
        // generamos PDF para su descarga
        $data['hash_cpe'] = $me['hash_cpe'];

        if ($docVenta['CodSunat'] == '07') {
            $new->creaPDFNota(json_encode($data));
        } else {
            $new->creaPDF(json_encode($data));
        }

        // Si es nota de credito/debito insertar en tabla
        if ($docVenta['CodSunat'] == '07') { // nota de credito
            /* $sqlNC = "INSERT INTO Ve_DocVentaNotaCredito (idDocVenta, Hash_cpe, Hash_cdr, Msj_sunat) VALUES
                ($docVenta[idDocVenta], '$me[hash_cpe]', '$me[hash_cdr]', '$me[msj_sunat]')";
            $stmt = $this->db->prepare($sqlNC);
            $insert = $stmt->execute();
            $lastInsert = $this->db->lastInsertId();

            return $response->withJson($me); */
        }

        if ($docVenta['CodSunat'] == '08') {
        }
    }

    // Nota de credito

    // Para los casos de factura y boleta
    $sql = "UPDATE Ve_DocVenta SET Estado=$estado, hash_cpe='$me[hash_cpe]', Hash_cdr='$me[hash_cdr]',
            Msj_sunat='$me[msj_sunat]' WHERE idDocVenta='$docVenta[idDocVenta]' ";

    $stmt = $this->db->prepare($sql);
    $updated = $stmt->execute();
    $me['Estado'] = $estado;


    return $response->withJson($me);
});


$app->post('/generarpdfelectronico', function (Request $request, Response $response) {
    include_once("../controllers/NumerosEnLetras/NumerosEnLetras.php");

    $docVenta = $request->getParam('docVenta');
    $new = new api_sunat();

    $detalle = array();
    $json = array();
    $n = 0;
    $descuento = 0;

    foreach ($docVenta['productos'] as $producto) {
        $n = $n + 1;

        $igv = $docVenta['TieneIgv'] ? round($producto['Total'] * 0.18, 2) : 0;
        $subtotal = $docVenta['TieneIgv'] ? $producto['Total'] - $igv : $producto['Total'];

        $json['txtITEM'] = $n;
        $json["txtUNIDAD_MEDIDA_DET"] = $producto['CodigoMedicion'];
        $json["txtUNIDAD_MEDIDA_NOMBRE_DET"] = $producto['ProductoMedicion'];
        $json["txtCANTIDAD_DET"] = $producto['Cantidad'];
        $json["txtPRECIO_DET"] = $producto['Precio'];
        $json["txtPRECIO_DESC_DET"] = $producto['Descuento'];
        $json["txtSUB_TOTAL_DET"] = $subtotal;  //PRECIO * CANTIDAD                       
        $json["txtPRECIO_TIPO_CODIGO"] = "01"; // 02 valor referencial unitario en operaciones no onerosas
        $json["txtIGV"] = $igv;
        $json["txtISC"] = "0";
        $json["txtIMPORTE_DET"] = $producto['Total']; //rowData.IMPORTE; //SUB_TOTAL + IGV
        $json["txtCOD_TIPO_OPERACION"] = $docVenta['CodigoIgv']; //20 si es exonerado
        $json["txtCODIGO_DET"] = $producto['CodigoBarra'];
        $partesDescripcionPdf = array();
        $nombreProductoPdf = trim((string) $producto['Producto']);
        if ($nombreProductoPdf !== '') $partesDescripcionPdf[] = $nombreProductoPdf;

        $atributosProductoPdf = array(
            'MARCA' => isset($producto['ProductoMarca']) ? $producto['ProductoMarca'] : '',
            'MODELO' => isset($producto['ProductoModelo']) ? $producto['ProductoModelo'] : '',
            'COLOR' => isset($producto['Color']) ? $producto['Color'] : '',
            'TALLA' => isset($producto['ProductoTalla']) ? $producto['ProductoTalla'] : '',
            'GENERO' => isset($producto['Genero']) ? $producto['Genero'] : '',
            'BOTAPIE' => isset($producto['Botapie']) ? $producto['Botapie'] : ''
        );
        foreach ($atributosProductoPdf as $etiquetaPdf => $valorPdf) {
            $valorPdf = trim((string) $valorPdf);
            if ($valorPdf !== '') {
                $partesDescripcionPdf[] = $etiquetaPdf . ': ' . $valorPdf;
            }
        }
        $json["txtDESCRIPCION_DET"] = implode(' | ', $partesDescripcionPdf);

        $json["txtMEDICION"] = $producto['ProductoMedicion'];
        $json["txtLABORATORIO"] = $producto['ProductoMarca'];
        $json["txtLOTE"] = isset($producto['IdLote']) ? $producto['IdLote'] : '';
        $json["txtFECHAVEN"] = isset($producto['FechaVen']) ? $producto['FechaVen'] : '';
        $json["txtFECHAVENCIMIENTO"] = isset($producto['FechaVencimiento']) ? $producto['FechaVencimiento'] : '';
        $json["txtDESCRIPCION_DET_DESCRIP"] = trim($producto['Descripcion']);

        //$json["txtPRECIO_SIN_IGV_DET"] = round($producto['Precio'] - ($producto['Precio'] * 0.18), 2);
        $json["txtPRECIO_SIN_IGV_DET"] = $subtotal;

        $detalle[] = $json;
        $descuento += $producto['Descuento'];
    }

    $gravadas = $docVenta['TieneIgv'] ? $docVenta['Total'] - ($docVenta['Total'] * 0.18) : $docVenta['Total'];
    $subtotal = ($descuento > 0) ? $gravadas + $descuento : $gravadas;

    //ELIMINAR ESPACIOS EN BLANCO DNI/RUC
    $docVenta['DniRuc'] = trim($docVenta['DniRuc']);

    //$total = ($descuento > 0) ? $subtotal - $descuento : '0';
    $data = array(
        "txtTIPO_OPERACION" => "0101", // corregir esto despues
        "txtTOTAL_DESCUENTO" => $descuento,
        "txtTOTAL_GRAVADAS" => $gravadas,
        "txtSUB_TOTAL" => $subtotal,
        "txtPOR_IGV" => $docVenta['TieneIgv'] ? "18" : "0",
        "txtTOTAL_IGV" => $docVenta['TieneIgv'] ? round($docVenta['Total'] * 0.18, 2) : 0,
        "txtTOTAL" => $docVenta['Total'],
        "txtTOTAL_LETRAS" => NumerosEnLetras::convertir(number_format($docVenta['Total'], 2, '.', ''), 'SOLES', true),
        "txtNRO_COMPROBANTE" => $docVenta['Serie'] . "-" . $docVenta['Numero'], //
        "txtFECHA_DOCUMENTO" => date("Y-m-d", strtotime($docVenta['FechaDoc'])),
        "txtFECHA_CREDITO" => date("Y-m-d", strtotime($docVenta['FechaCredito'])),
        "txtFECHA_VTO" => date("Y-m-d", strtotime($docVenta['FechaDoc'])),
        "txtCOD_TIPO_DOCUMENTO" => $docVenta['CodSunat'], //01=factura,03=boleta,07=notacrediro,08=notadebito
        "txtCOD_MONEDA" => 'PEN', //PEN= PERU
        "txtVENDEDOR" => $docVenta['UsuarioReg'], //VENDEDOR
        "txtOBSERVACION" => isset($docVenta['Observacion']) ? $docVenta['Observacion'] : '',
        "txtPLACA" => isset($docVenta['Placa']) ? $docVenta['Placa'] : '',
        "txtALMACEN" => $docVenta['Almacen'],
        "txtALMACEN_DIRECCION" => $docVenta['DireccionAlmacen'],
        "txtALMACEN_ES_PRINCIPAL" => $docVenta['EsAlmacenPrincipal'],
        //==========documentos de referencia(nota credito, debito)=============
        "txtTIPO_COMPROBANTE_MODIFICA" => isset($docVenta['CodSunatModifica']) && $docVenta['CodSunatModifica'] ? $docVenta['CodSunatModifica'] : "", //aqui completar
        "txtNRO_DOCUMENTO_MODIFICA" => isset($docVenta['NroComprobanteModifica']) && $docVenta['NroComprobanteModifica'] ? $docVenta['NroComprobanteModifica'] : "",
        "txtCOD_TIPO_MOTIVO" => isset($docVenta['NotaIdMotivo']) && $docVenta['NotaIdMotivo'] ? $docVenta['NotaIdMotivo'] : "",
        "txtDESCRIPCION_MOTIVO" => isset($docVenta['NotaDescMotivo']) && $docVenta['NotaDescMotivo'] ? $docVenta['NotaDescMotivo'] : "", //$("[name='txtID_MOTIVO']
        //=================datos del cliente=================8
        "txtNRO_DOCUMENTO_CLIENTE" => $docVenta['DniRuc'],
        "txtRAZON_SOCIAL_CLIENTE" => $docVenta['Cliente'],
        "txtNOMBRE_COMERCIAL_CLIENTE" => isset($docVenta['NombreComercial']) ?  $docVenta['NombreComercial'] : 'Cliente Varios',
        "txtTIPO_DOCUMENTO_CLIENTE" => strlen($docVenta['DniRuc']) > 9 ? "6" : "1", //1 DNI 6 RUC
        "txtDIRECCION_CLIENTE" => $docVenta['Direccion'],
        "txtES_CREDITO" => $docVenta['EsCredito'],
        "txtCIUDAD_CLIENTE" => "",
        "txtCOD_PAIS_CLIENTE" => "PE",
        //=================datos de LA EMPRESA================= 
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
        // "txtTELEFONOS_EMPRESA" => TELEFONOS_EMPRESA,
        "txtUSUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
        "txtPASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
        "txtTIPO_PROCESO" => TIPO_PROCESO, //01 PRODUCCION 03 BETA
        "detalle" => $detalle,
        //"detalle" => []
    );
    if ($docVenta['CodigoIgv'] == "20") { // 20 = exonerado Igv
        $data["txtTOTAL_EXONERADAS"] = $docVenta['Total'];
    }

    $data['hash_cpe'] = $docVenta['Hash_cpe'];
    if ($docVenta['CodSunat'] == '07') {
        $pdf = $new->creaPDFNota(json_encode($data));
    } else {
        $pdf = $new->creaPDF(json_encode($data));
    }

    if (is_string($pdf) && substr($pdf, 0, 4) === '%PDF') {
        return $response->withHeader('Content-Type', 'application/pdf')
            ->withHeader('Content-Length', (string) strlen($pdf))
            ->write($pdf);
    }

    return $response->withJson(array('error' => 'No se pudo generar el PDF electronico'), 500);
});


$app->post('/moveraemitidos', function (Request $request, Response $response) {
    $idDocVenta = $request->getParam('idDocVenta');

    if ($idDocVenta) {
        $update = "UPDATE Ve_DocVenta SET Estado=2 WHERE idDocVenta=$idDocVenta";
        $stmt = $this->db->prepare($update);
        $updated = $stmt->execute();


        return $response->withJson(array(
            "updated" => $updated,
            "IdDocVenta" => $idDocVenta
        ));
    }
});

$app->post('/moveraemitidosguia', function (Request $request, Response $response) {
    $idDocVentaRemision = $request->getParam('idDocVentaRemision');

    if ($idDocVentaRemision) {
        $update = "UPDATE Ve_DocVentaRemision SET Estado=2 WHERE IdDocVentaRemision=$idDocVentaRemision";
        $stmt = $this->db->prepare($update);
        $updated = $stmt->execute();

        return $response->withJson(array(
            "updated" => $updated,
            "IdDocVentaRemision" => $idDocVentaRemision
        ));
    }
});

$app->post('/cambiaraanonimo', function (Request $request, Response $response) {
    $idDocVenta = $request->getParam('idDocVenta');

    if ($idDocVenta) {
        $update = "UPDATE Ve_DocVenta SET IdCliente=(SELECT IdCliente FROM `Ve_DocVentaCliente` WHERE DniRuc='00000000' LIMIT 1) WHERE idDocVenta=$idDocVenta";
        $stmt = $this->db->prepare($update);
        $updated = $stmt->execute();


        return $response->withJson(array(
            "updated" => $updated,
            "IdDocVenta" => $idDocVenta
        ));
    }
});

$app->post('/emitirelectronicoboleta', function (Request $request, Response $response) {
    include_once("../controllers/NumerosEnLetras/NumerosEnLetras.php");

    $new = new api_sunat();
    $boletas = $request->getParam('boletas');
    $statu = $request->getParam('statu') ? $request->getParam('statu') : 1;
    $codSunat = $request->getParam('codSunat');
    $codMoneda = "PEN";

    $fechaReferencia = $request->getParam('fechaReferencia'); // "2018-03-28";
    $fechaDocumento = getNow('Y-m-d');

    $serie = getNow('Ymd');
    // Inicio Correlativo
    $selectSec = "SELECT Secuencia FROM Sunat_Resumen WHERE Serie=$serie AND CodSunat=$codSunat ORDER BY Secuencia DESC LIMIT 1";
    $stmtSec = $this->db->query($selectSec);
    $stmtSec->execute();
    $secuencia = $stmtSec->fetch()['Secuencia'];
    $secuencia += 1; // cambiar correlativo
    // Fin Correlativo

    $detalle = array();
    $json = array();
    $n = 0;
    foreach ($boletas as $boleta) {
        $n = $n + 1;

        $json['ITEM'] = $n;
        $json['TIPO_COMPROBANTE'] = $boleta['CodSunat'];
        $json['NRO_COMPROBANTE'] = $boleta['Serie'] . '-' . $boleta['Numero'];
        $json['TIPO_DOCUMENTO'] = strlen($boleta['DniRuc']) > 9 ? "6" : "1";
        $json['NRO_DOCUMENTO'] = $boleta['DniRuc'];
        $json['TIPO_COMPROBANTE_REF'] = $boleta['CodSunatModifica'] ? $boleta['CodSunatModifica'] : '';
        $json['NRO_COMPROBANTE_REF'] = $boleta['NroComprobanteModifica'] ? $boleta['NroComprobanteModifica'] : '';
        $json['STATU'] = $statu; // 1 declara boleta , 3 mandar anuladas... primero envio y luego se anula
        $json['COD_MONEDA'] = $codMoneda;
        $json['TOTAL'] = $boleta['Total'];
        $json['GRAVADA'] = $boleta['Total']; // $boleta['Total'] - IGV //corregir gravada en Factura

        $json['ISC'] = "0";
        $json['IGV'] = "0";
        $json['OTROS'] = "0";
        $json['CARGO_X_ASIGNACION'] = "1";
        $json['MONTO_CARGO_X_ASIG'] = "0";
        $json['EXONERADO'] = $boleta['Total'];
        $json['INAFECTO'] = "0";
        $json['EXPORTACION'] = "0";
        $json['GRATUITAS'] = "0";
        $detalle[] = $json;
    }

    $data = array(
        "NRO_DOCUMENTO_EMPRESA" => NRO_DOCUMENTO_EMPRESA,
        "RAZON_SOCIAL" => RAZON_SOCIAL_EMPRESA,
        "TIPO_DOCUMENTO" => TIPO_DOCUMENTO_EMPRESA,
        "CODIGO" => "RC",
        "SERIE" => $serie,
        "SECUENCIA" => $secuencia,
        "FECHA_REFERENCIA" => $fechaReferencia,
        "FECHA_DOCUMENTO" => $fechaDocumento,
        "TIPO_PROCESO" => TIPO_PROCESO,
        "USUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
        "PASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
        "detalle" => $detalle
    );

    $resultado = $new->sendresumen(json_encode($data));
    $me = json_decode($resultado, true);
    $estado = 1;
    $me['ticket'] = $me['msj_sunat'];

    if ($me['cod_sunat'] == '0') {

        // insert correlativo del resumen
        $insert = "INSERT INTO Sunat_Resumen (Serie, Secuencia, CodSunat) VALUES ('$serie', $secuencia, '$codSunat')";
        $stmt = $this->db->prepare($insert);
        $inserted = $stmt->execute();
        $lastInsert = $this->db->lastInsertId();
        $estado = ($statu == 3) ? 0 : 2; // si se manda a anular el estado sera 0

        // Inicio Consulta Ticket
        $dataTicket = array(
            "TIPO_PROCESO" => TIPO_PROCESO,
            "NRO_DOCUMENTO_EMPRESA" => NRO_DOCUMENTO_EMPRESA,
            "USUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
            "PASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
            "TICKET" => $me['msj_sunat'],
            "TIPO_DOCUMENTO" => "RC",
            "NRO_DOCUMENTO" => $serie . '-' . $secuencia
        );
        $resTicket = $new->sendticket(json_encode($dataTicket));
        $meTicket = json_decode($resTicket, true);
        if ($meTicket['cod_sunat'] == '0') {
            // $me = $meTicket;
            $me['msj_sunat'] = $meTicket['msj_sunat'];
            $me['hash_cdr'] = $meTicket['hash_cdr'];

            // si todo es correcto generar pdf para cada boleta
            foreach ($boletas as $boleta) {
                $detallePdf = array();
                $json = array();
                $n = 0;

                foreach ($boleta['productos'] as $producto) {
                    $n = $n + 1;
                    $igv = $boleta['TieneIgv'] ? round($producto['Subtotal'] * 0.18, 2) : 0;
                    $subtotal = $boleta['TieneIgv'] ? $producto['Subtotal'] - $igv : $producto['Subtotal'];
                    $json["txtCANTIDAD_DET"] = $producto['Cantidad'];
                    $json["txtPRECIO_DET"] = $producto['Precio'];
                    $json["txtPRECIO_DESC_DET"] = $producto['Descuento'];
                    $json["txtIMPORTE_DET"] = $subtotal + $igv; //rowData.IMPORTE; //SUB_TOTAL + IGV
                    $json["txtCODIGO_DET"] = $producto['CodigoBarra'];
                    $json["txtDESCRIPCION_DET"] = trim($producto['Producto']);
                    $detallePdf[] = $json;
                }
                $dataPdf = array(
                    'txtTIPO_PROCESO' => TIPO_PROCESO,
                    'txtCOD_MONEDA' => $codMoneda,
                    'txtNRO_DOCUMENTO_EMPRESA' => NRO_DOCUMENTO_EMPRESA,
                    'txtCOD_TIPO_DOCUMENTO' => $boleta['CodSunat'],
                    'txtNRO_COMPROBANTE' => $boleta['Serie'] . '-' . $boleta['Numero'],
                    'txtSUB_TOTAL' => $boleta['Total'],
                    'txtTOTAL_IGV' => "0",
                    'txtTOTAL' => $boleta['Total'],
                    'txtTOTAL_LETRAS' => NumerosEnLetras::convertir(number_format($boleta['Total'], 2, '.', ''), 'SOLES', true),
                    'txtFECHA_DOCUMENTO' => date("Y-m-d", strtotime($boleta['FechaDoc'])),
                    "txtFECHA_CREDITO" => date("Y-m-d", strtotime($boleta['FechaCredito'])),
                    'txtTIPO_DOCUMENTO_CLIENTE' => strlen($boleta['DniRuc']) > 9 ? "6" : "1",
                    'txtNRO_DOCUMENTO_CLIENTE' => $boleta['DniRuc'],
                    'txtNOMBRE_COMERCIAL_EMPRESA' => NOMBRE_COMERCIAL_EMPRESA,
                    'txtDIRECCION_EMPRESA' => DIRECCION_EMPRESA,
                    'txtRAZON_SOCIAL_CLIENTE' => $boleta['Cliente'],
                    "txtNOMBRE_COMERCIAL_CLIENTE" => $boleta['NombreComercial'],
                    'txtDIRECCION_CLIENTE' => $boleta['Direccion'],
                    'detalle' => $detallePdf
                );

                // generamos PDF para su descarga
                $dataPdf['hash_cpe'] = $me['hash_cpe'];
                // $new->creaPDF(json_encode($dataPdf));

            }
        }

        // $me['dataTicket'] = $dataTicket;
        $me['meticket'] = $meTicket;
        // Fin Consulta Ticket
    }

    $cadenaIds = [];
    foreach ($boletas as $boleta) {
        $cadenaIds[] = $boleta['idDocVenta'];
    }

    if ($statu == 3) { // si se manda a anular
        $sql = "UPDATE Ve_DocVenta SET Estado=$estado, hash_cpe='$me[hash_cpe]', Hash_cdr='$me[hash_cdr]', 
            Msj_sunat='$me[msj_sunat]' WHERE idDocVenta IN (" . implode(',', $cadenaIds) . ")";
    } else { //si se declara
        $sql = "UPDATE Ve_DocVenta SET Estado=$estado, hash_cpe='$me[hash_cpe]', Hash_cdr='$me[hash_cdr]', 
        Msj_sunat='$me[msj_sunat]', Ticket='$me[ticket]' WHERE idDocVenta IN (" . implode(',', $cadenaIds) . ")";
    }
    //$me['sql'] = $sql;
    $stmt = $this->db->prepare($sql);
    $updated = $stmt->execute();
    $me['Estado'] = $estado;
    // $me['data'] = $data; // ocultar en produccion

    return $response->withJson($me);
});

$app->post('/bajaelectronico', function (Request $request, Response $response) {
    $new = new api_sunat();
    $facturas = $request->getParam('facturas');
    $codSunat = $request->getParam('codSunat');
    $descripcion = $request->getParam('descripcion');

    // $fechaReferencia = $request->getParam('fechaReferencia'); // "2018-03-28";
    $fechaReferencia = date("Y-m-d", strtotime($facturas[0]['FechaDoc']));
    $fechaBaja = getNow('Y-m-d');

    $serie = getNow('Ymd');
    // Inicio Correlativo
    $selectSec = "SELECT Secuencia FROM Sunat_Resumen WHERE Serie=$serie AND CodSunat=$codSunat ORDER BY Secuencia DESC LIMIT 1";
    $stmtSec = $this->db->query($selectSec);
    $stmtSec->execute();
    $secuencia = $stmtSec->fetch()['Secuencia'];
    $secuencia += 1; // cambiar correlativo
    // Fin Correlativo

    $detalle = array();
    $json = array();
    $n = 0;
    foreach ($facturas as $factura) {
        $n = $n + 1;

        $json['ITEM'] = $n;
        $json['TIPO_COMPROBANTE'] = $factura['CodSunat'];
        $json['SERIE'] = $factura['Serie'];
        $json['NUMERO'] = $factura['Numero'];
        $json['DESCRIPCION'] = $descripcion;
        $detalle[] = $json;
    }

    $data = array(
        "NRO_DOCUMENTO_EMPRESA" => NRO_DOCUMENTO_EMPRESA,
        "RAZON_SOCIAL" => RAZON_SOCIAL_EMPRESA,
        "TIPO_DOCUMENTO" => TIPO_DOCUMENTO_EMPRESA,
        "CODIGO" => "RA",
        "SERIE" => $serie,
        "SECUENCIA" => $secuencia,
        "FECHA_REFERENCIA" => $fechaReferencia,
        "FECHA_BAJA" => $fechaBaja,
        "TIPO_PROCESO" => TIPO_PROCESO,
        "USUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
        "PASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
        "detalle" => $detalle
    );

    $resultado = $new->sendbaja(json_encode($data));
    $me = json_decode($resultado, true);
    $estado = 1;

    if ($me['cod_sunat'] == '0') {
        // insert correlativo del resumen
        $insert = "INSERT INTO Sunat_Resumen (Serie, Secuencia, CodSunat) VALUES ('$serie', $secuencia, '$codSunat')";
        $stmt = $this->db->prepare($insert);
        $inserted = $stmt->execute();
        $lastInsert = $this->db->lastInsertId();
        $estado = 0; // estado 0 anulado / 1 pendiente / 2 es emitido 

        // Inicio Consulta Ticket
        $dataTicket = array(
            "TIPO_PROCESO" => TIPO_PROCESO,
            "NRO_DOCUMENTO_EMPRESA" => NRO_DOCUMENTO_EMPRESA,
            "USUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
            "PASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
            "TICKET" => $me['msj_sunat'],
            "TIPO_DOCUMENTO" => "RA",
            "NRO_DOCUMENTO" => $serie . '-' . $secuencia
        );
        $resTicket = $new->sendticket(json_encode($dataTicket));
        $meTicket = json_decode($resTicket, true);
        if ($meTicket['cod_sunat'] == '0') {
            $me = $meTicket;
        }

        // $me['dataTicket'] = $dataTicket;
        $me['meticket'] = $meTicket;
        // Fin Consulta Ticket
    }

    $cadenaIds = [];
    foreach ($facturas as $factura) {
        $cadenaIds[] = $factura['idDocVenta'];
    }

    $sql = "UPDATE Ve_DocVenta SET Estado=$estado, hash_cpe='$me[hash_cpe]', Hash_cdr='$me[hash_cdr]', 
        Msj_sunat='$me[msj_sunat]', AnuladoDesc='$descripcion' WHERE idDocVenta IN (" . implode(',', $cadenaIds) . ")";

    //$me['sql'] = $sql;
    $stmt = $this->db->prepare($sql);
    $updated = $stmt->execute();
    $me['Estado'] = $estado;
    $me['data'] = $data; // ocultar en produccion

    return $response->withJson($me);
});






$app->get('/reporte/movimientos', function (Request $request, Response $response, array $args) use ($app) {
    try {
        //code...



        $fechaInicio = $request->getParam('fechaInicio');
        $fechaFin = $request->getParam('fechaFin');
        $declarado = $request->getParam('declarado') ? 1 : 0;
        // $IdProveedor = null;
        // include_once '../clases/BnGeneral.php';
        $result = ListarRegNovExportar($this->db, $fechaInicio, $fechaFin, $declarado, null);

        $movimientos = $result;
        //     //echo "<pre>";
        //     // print_r($movimientos);exit();
        $excel = new Spreadsheet();
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle('Movimientos');
        $sheet->mergeCells('A1:Q1');
        $sheet->setCellValue('A1', 'REPORTE DE REGISTRO DE MOVIMIENTOS');
        $sheet->getStyle('A1:Q1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF0A4F83');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('center');
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->mergeCells('A2:Q2');
        $sheet->setCellValue('A2', 'Periodo: ' . $fechaInicio . ' al ' . $fechaFin . '  |  Generado: ' . date('d/m/Y H:i'));
        $sheet->getStyle('A2:Q2')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE9F4FA');
        $sheet->getStyle('A2')->getFont()->setBold(true)->getColor()->setARGB('FF315A75');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $headersMov = array('MOVIMIENTO','VENDEDOR','ORIGEN','DESTINO','FECHA','COD. SUNAT','TIPO','SERIE','NUMERO','PROVEEDOR','SUBTOTAL','ISC','IGV','FLETE','PERCEPCION','MONEDA','TOTAL');
        foreach ($headersMov as $iMov => $headerMov) $sheet->setCellValue(chr(65 + $iMov) . '4', $headerMov);
        $sheet->getStyle('A4:Q4')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF087FAE');
        $sheet->getStyle('A4:Q4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A4:Q4')->getAlignment()->setHorizontal('center')->setVertical('center');
        $sheet->getRowDimension(4)->setRowHeight(25);

        $cont = 5;
        foreach ($movimientos as $mov) {
            $sheet->fromArray(array($mov['IdMovimiento'],$mov['UsuarioReg'],$mov['AlmacenOrigen'],$mov['AlmacenDestino'],$mov['MovimientoFecha'],$mov['CodSunat'],$mov['TipoMovimiento'],$mov['Serie'],$mov['Numero'],$mov['Proveedor'],(float)$mov['SUBTOTAL'],(float)$mov['ISC'],(float)$mov['IGV'],(float)$mov['FLETE'],(float)$mov['Percepcion'],$mov['Moneda'],(float)$mov['TOTAL']), null, 'A' . $cont);
            if ($cont % 2 === 0) $sheet->getStyle('A' . $cont . ':Q' . $cont)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFF2F8FC');
            $cont++;
        }

        $lastMovRow = $cont - 1;
        $sheet->setCellValue('J' . $cont, 'TOTALES');
        foreach (array('K','L','M','N','O','Q') as $totalCol) $sheet->setCellValue($totalCol . $cont, $lastMovRow >= 5 ? '=SUM(' . $totalCol . '5:' . $totalCol . $lastMovRow . ')' : 0);
        $sheet->getStyle('J' . $cont . ':Q' . $cont)->getFill()->setFillType('solid')->getStartColor()->setARGB('FF0A4F83');
        $sheet->getStyle('J' . $cont . ':Q' . $cont)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('K5:O' . $cont)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('Q5:Q' . $cont)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A4:Q' . $cont)->getBorders()->getAllBorders()->setBorderStyle('thin')->getColor()->setARGB('FFD5E3ED');
        foreach (array('A'=>17,'B'=>15,'C'=>17,'D'=>17,'E'=>21,'F'=>13,'G'=>18,'H'=>12,'I'=>13,'J'=>32,'K'=>14,'L'=>12,'M'=>12,'N'=>12,'O'=>16,'P'=>12,'Q'=>15) as $colMov => $widthMov) $sheet->getColumnDimension($colMov)->setWidth($widthMov);
        $sheet->getStyle('J5:J' . max(5, $lastMovRow))->getAlignment()->setWrapText(true);
        $sheet->freezePane('A5');
        $sheet->setAutoFilter('A4:Q' . max(4, $lastMovRow));
        $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(.4)->setRight(.25)->setLeft(.25)->setBottom(.4);

        aplicarIdentidadEmpresaExcel($excel, $this->db);
        $excelWriter = new Xlsx($excel);

        $fileName = 'reporte-movimientos-' . getNow('Y-m-d-H-i-s') . '.xlsx';
        $excelFileName = __DIR__ . '/reporte/' . $fileName;
        $excelWriter->save($excelFileName);
        return $response->withJson(['link' =>  'http://' . $_SERVER['HTTP_HOST'] . '/api/reporte/' . $fileName]);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->post('/emitirguiaelectronica/{id}', function (Request $request, Response $response, array $args) {
    $idRemision = $args['id'];

    if ($idRemision) {
        $select = "SELECT * FROM Ve_DocVentaRemision WHERE IdDocVentaRemision = $idRemision";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $guia = $stmt->fetch();

        $select = "SELECT Gen_Producto.*, Ve_DocVentaRemisionDet.Cantidad, Gen_ProductoMedicion.Codigo AS CodigoMedicion 
            FROM Ve_DocVentaRemisionDet
            INNER JOIN Gen_Producto ON Ve_DocVentaRemisionDet.IdProducto = Gen_Producto.IdProducto 
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion 
            WHERE Ve_DocVentaRemisionDet.IdDocVentaRemision = $idRemision";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $guia['productos'] = $stmt->fetchAll();
        $new = new api_sunat();
        $detalle = array();
        $json = array();
        $n = 0;

        foreach ($guia['productos'] as $producto) {
            $n = $n + 1;
            $json['ITEM'] = $n;
            $json["UNIDAD_MEDIDA"] = $producto['CodigoMedicion'];
            $json["CANTIDAD"] = $producto['Cantidad'];
            $json["ORDER_ITEM"] = $n;
            $json["CODIGO"] = $producto['CodigoBarra']; // 02 valor referencial unitario en operaciones no onerosas
            $json["DESCRIPCION"] = trim($producto['Producto']);
            $detalle[] = $json;
        }

        $tipoDocTransportista = strlen($guia['NroDocumentoTransportista']) > 9 ? "6" : "1";
        $codTipoDocChofer = strlen($guia['NroDocChofer']) > 9 ? "6" : "1";
        $tipoDocumentoCliente = strlen($guia['NroDocumentoCliente']) > 9 ? "6" : "1";

        $data = array(
            "NRO_COMPROBANTE" => $guia['Serie'] . "-" . $guia['Numero'],
            "FECHA_DOCUMENTO" => date("Y-m-d", strtotime($guia['FechaReg'])),
            "COD_TIPO_DOCUMENTO" => "09",
            "NOTA" => "GUIA DE REMISION",
            "ITEM_ENVIO" => "1",
            "COD_MOTIVO_TRASLADO" => $guia['CodMotivoTraslado'],
            "DESCRIPCION_MOTIVO_TRASLADO" => $guia['DescripcionMotivoTraslado'],
            "COD_UND_PESO_BRUTO" => $guia['CodUndPesoBruto'],
            "PESO_BRUTO" => $guia['PesoBruto'],
            "TOTAL_BULTOS" => $guia['TotalBultos'],
            "COD_MODALIDAD_TRASLADO" => $guia['CodModalidadTraslado'],
            "DOCUMENTO_REFERENCIA" => $guia['DocumentoReferencia'],
            "FECHA_INICIO" => date("Y-m-d", strtotime($guia['FechaTraslado'])),
            "OBSERVACION" => $guia['Observacion'],
            "NRO_DOCUMENTO_TRANSPORTISTA" => $guia['NroDocumentoTransportista'],
            "RAZON_SOCIAL_TRANSPORTISTA" => $guia['RazonSocialTransportista'],
            "TIPO_DOCUMENTO_TRANSPORTISTA" => $tipoDocTransportista,
            "PLACA_VEHICULO" => $guia['PlacaVehiculo'],
            "COD_TIPO_DOC_CHOFER" => $codTipoDocChofer,
            "NRO_DOC_CHOFER" => $guia['NroDocChofer'],
            "PLACA_CARRETA" => $guia['PlacaCarreta'],
            "LICENCIA_CHOFER" => $guia['LicenciaChofer'],
            "NRO_DOCUMENTO_CLIENTE" => $guia['NroDocumentoCliente'],
            "RAZON_SOCIAL_CLIENTE" => $guia['RazonSocialCliente'],
            "TIPO_DOCUMENTO_CLIENTE" => $tipoDocumentoCliente,
            "DIRECCION_CLIENTE" => $guia['DireccionCliente'],

            "COD_UBIGEO_ORIGEN" => $guia['CodUbigeoOrigen'],
            "DIRECCION_ORIGEN" => $guia['DireccionOrigen'],
            "COD_UBIGEO_DESTINO" => $guia['CodUbigeoDestino'],
            "DIRECCION_DESTINO" => $guia['DireccionDestino'],

            //=================datos de LA EMPRESA=================	
            "NRO_DOCUMENTO_EMPRESA" => NRO_DOCUMENTO_EMPRESA,
            "TIPO_DOCUMENTO_EMPRESA" => TIPO_DOCUMENTO_EMPRESA,
            "RAZON_SOCIAL_EMPRESA" => RAZON_SOCIAL_EMPRESA,
            "NOMBRE_COMERCIAL_EMPRESA" => NOMBRE_COMERCIAL_EMPRESA,
            'DIRECCION_EMPRESA' => DIRECCION_EMPRESA,

            "USUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
            "PASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
            "TIPO_PROCESO" => TIPO_PROCESO,
            "detalle" => $detalle,
        );

        $resultado = $new->sendGuia(json_encode($data));
        $me = json_decode($resultado, true);

        // Datos $me
        $me['data'] = array(
            "NRO_COMPROBANTE" => $data['NRO_COMPROBANTE']
        );

        $estado = 1;
        if ($me['cod_sunat'] == '0') {
            $estado = 2;
            // generamos PDF para su descarga
            $data['hash_cpe'] = $me['hash_cpe'];
            $new->creaPDFGuia(json_encode($data));
        }

        $sql = "UPDATE Ve_DocVentaRemision SET Estado=$estado, Hash_cpe='$me[hash_cpe]', Hash_cdr='$me[hash_cdr]', 
            Msj_sunat='$me[msj_sunat]' WHERE IdDocVentaRemision='$idRemision' ";

        $stmt = $this->db->prepare($sql);
        $updated = $stmt->execute();
        $me['Estado'] = $estado;

        return $response->withJson($me);
    }
});


$app->post('/generarpdfguiaelectronica/{id}', function (Request $request, Response $response, array $args) {
    $idRemision = $args['id'];

    if ($idRemision) {
        $select = "SELECT * FROM Ve_DocVentaRemision WHERE IdDocVentaRemision = $idRemision";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $guia = $stmt->fetch();

        $select = "SELECT Gen_Producto.*, Ve_DocVentaRemisionDet.Cantidad, Gen_ProductoMedicion.Codigo AS CodigoMedicion 
            FROM Ve_DocVentaRemisionDet
            INNER JOIN Gen_Producto ON Ve_DocVentaRemisionDet.IdProducto = Gen_Producto.IdProducto 
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion 
            WHERE Ve_DocVentaRemisionDet.IdDocVentaRemision = $idRemision";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $guia['productos'] = $stmt->fetchAll();
        $new = new api_sunat();
        $detalle = array();
        $json = array();
        $n = 0;

        foreach ($guia['productos'] as $producto) {
            $n = $n + 1;
            $json['ITEM'] = $n;
            $json["UNIDAD_MEDIDA"] = $producto['CodigoMedicion'];
            $json["CANTIDAD"] = $producto['Cantidad'];
            $json["ORDER_ITEM"] = $n;
            $json["CODIGO"] = $producto['CodigoBarra']; // 02 valor referencial unitario en operaciones no onerosas
            $json["DESCRIPCION"] = trim($producto['Producto']);
            $detalle[] = $json;
        }

        $tipoDocTransportista = strlen($guia['NroDocumentoTransportista']) > 9 ? "6" : "1";
        $codTipoDocChofer = strlen($guia['NroDocChofer']) > 9 ? "6" : "1";
        $tipoDocumentoCliente = strlen($guia['NroDocumentoCliente']) > 9 ? "6" : "1";

        $data = array(
            "NRO_COMPROBANTE" => $guia['Serie'] . "-" . $guia['Numero'],
            "FECHA_DOCUMENTO" => date("Y-m-d", strtotime($guia['FechaReg'])),
            "COD_TIPO_DOCUMENTO" => "09",
            "NOTA" => "GUIA DE REMISION",
            "ITEM_ENVIO" => "1",
            "COD_MOTIVO_TRASLADO" => $guia['CodMotivoTraslado'],
            "DESCRIPCION_MOTIVO_TRASLADO" => $guia['DescripcionMotivoTraslado'],
            "COD_UND_PESO_BRUTO" => $guia['CodUndPesoBruto'],
            "PESO_BRUTO" => $guia['PesoBruto'],
            "TOTAL_BULTOS" => $guia['TotalBultos'],
            "COD_MODALIDAD_TRASLADO" => $guia['CodModalidadTraslado'],
            "DOCUMENTO_REFERENCIA" => $guia['DocumentoReferencia'],
            "FECHA_INICIO" => date("Y-m-d", strtotime($guia['FechaTraslado'])),
            "OBSERVACION" => $guia['Observacion'],
            "NRO_DOCUMENTO_TRANSPORTISTA" => $guia['NroDocumentoTransportista'],
            "RAZON_SOCIAL_TRANSPORTISTA" => $guia['RazonSocialTransportista'],
            "TIPO_DOCUMENTO_TRANSPORTISTA" => $tipoDocTransportista,
            "PLACA_VEHICULO" => $guia['PlacaVehiculo'],
            "COD_TIPO_DOC_CHOFER" => $codTipoDocChofer,
            "NRO_DOC_CHOFER" => $guia['NroDocChofer'],
            "PLACA_CARRETA" => $guia['PlacaCarreta'],
            "LICENCIA_CHOFER" => $guia['LicenciaChofer'],
            "NRO_DOCUMENTO_CLIENTE" => $guia['NroDocumentoCliente'],
            "RAZON_SOCIAL_CLIENTE" => $guia['RazonSocialCliente'],
            "TIPO_DOCUMENTO_CLIENTE" => $tipoDocumentoCliente,
            "DIRECCION_CLIENTE" => $guia['DireccionCliente'],

            "COD_UBIGEO_ORIGEN" => $guia['CodUbigeoOrigen'],
            "DIRECCION_ORIGEN" => $guia['DireccionOrigen'],
            "COD_UBIGEO_DESTINO" => $guia['CodUbigeoDestino'],
            "DIRECCION_DESTINO" => $guia['DireccionDestino'],

            //=================datos de LA EMPRESA=================	
            "NRO_DOCUMENTO_EMPRESA" => NRO_DOCUMENTO_EMPRESA,
            "TIPO_DOCUMENTO_EMPRESA" => TIPO_DOCUMENTO_EMPRESA,
            "RAZON_SOCIAL_EMPRESA" => RAZON_SOCIAL_EMPRESA,
            "NOMBRE_COMERCIAL_EMPRESA" => NOMBRE_COMERCIAL_EMPRESA,
            'DIRECCION_EMPRESA' => DIRECCION_EMPRESA,

            "USUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
            "PASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
            "TIPO_PROCESO" => TIPO_PROCESO,
            "detalle" => $detalle,
        );

        $new->creaPDFGuia(json_encode($data));
    }
});

$app->get('/reporte/ventas', function (Request $request, Response $response, array $args) use ($app) {
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaInicio = $request->getParam('fechaInicio');
    $fechaFin = $request->getParam('fechaFin');
    $declarado = $request->getParam('declarado') ? 1 : 0;

    $res = $app->subRequest('GET', '/ventas', '&filter[fechaInicio]=' . $fechaInicio . '&filter[fechaFin]=' . $fechaFin . '&filter[declarado]=' . $declarado . '&sortBy=Ve_DocVenta.FechaDoc&sortDesc=DESC' . '&idAlmacen=' . $idAlmacen);
    // $res = $app->subRequest('GET', '/ventas', 'idAlmacen=' . $idAlmacen . '&filter[fechaInicio]=' . $fechaInicio . '&filter[fechaFin]=' . $fechaFin . '&filter[declarado]=' . $declarado . '&sortBy=Ve_DocVenta.FechaDoc&sortDesc=DESC');
    $ventas = (string) $res->getBody();
    $ventas = json_decode($ventas, true);
    // print_r($ventas);exit();

    $excel = new Spreadsheet();
    //$sheet = $excel->setActiveSheetIndex(0);
    $sheet = $excel->getActiveSheet();
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'FECHAE');
    $sheet->setCellValue('C1', 'FECHAV');
    $sheet->setCellValue('D1', 'TIPOC');
    $sheet->setCellValue('E1', 'SERIE');
    $sheet->setCellValue('F1', 'NUMERO');
    $sheet->setCellValue('G1', 'TIPODOC');
    $sheet->setCellValue('H1', 'DOCUMENTO');
    $sheet->setCellValue('I1', 'NOMBRE');
    $sheet->setCellValue('J1', 'BASEI');
    $sheet->setCellValue('K1', 'IGV');
    $sheet->setCellValue('L1', 'EXONERADO');
    $sheet->setCellValue('M1', 'RETENCION');
    $sheet->setCellValue('N1', 'TOTAL');
    $sheet->setCellValue('O1', 'TIPO DOC. MODIFICA');
    $sheet->setCellValue('P1', 'SERIE COMPROB. MODIFICA');
    $sheet->setCellValue('Q1', 'NRO COMPROB. MODIFICA');
    $sheet->setCellValue('R1', 'MOTIVO');

    // $sheet->setCellValue('N1', 'ES ANULADO');


    $cont = 3;
    foreach ($ventas as $ven) {
        $exonerado = 0;
        $igv = 0;
        $subtotal = $ven['Total'];
        if ($ven['TieneIgv']) {
            // $igv = $ven['Total'] * 0.18;
            // $subtotal = $ven['Total'] - $igv;
            $subtotal = round($ven['Total'] / 1.18, 2);
            $igv = round($ven['Total'] - $subtotal, 2);
        }

        if ($ven['CodigoIgv'] == '20') {
            $subtotal = 0;
            $igv = 0;
            $exonerado = $ven['Total'];
        }

        if ($ven['Anulado']) {
            $subtotal = 0;
            $igv = 0;
            $exonerado = 0;
            $ven['Total'] = 0;
        }

        if ($ven['CodSunat'] == '07') {
            $exonerado = -1 * $exonerado;
            $ven['Total'] = -1 * $ven['Total'];
        }

        $sheet->setCellValue('A' . $cont, $ven['idDocVenta']);
        $sheet->setCellValue('B' . $cont, date("d/m/Y", strtotime($ven['FechaDoc'])));
        $sheet->setCellValue('C' . $cont, date("d/m/Y", strtotime($ven['FechaDoc'])));
        $sheet->setCellValue('D' . $cont, $ven['CodSunat']);
        $sheet->setCellValue('E' . $cont, $ven['Serie']);
        $sheet->setCellValue('F' . $cont, $ven['Numero']);

        // $sheet->setCellValue('F'.$cont, $ven['TipoDoc']);
        $sheet->setCellValue('G' . $cont, strlen($ven['DniRuc']) > 9 ? "6" : "1");
        $sheet->setCellValue('H' . $cont, $ven['DniRuc']);
        $sheet->setCellValue('I' . $cont, $ven['Cliente']);
        $sheet->setCellValue('J' . $cont, $subtotal);
        $sheet->setCellValue('K' . $cont, $igv);
        $sheet->setCellValue('L' . $cont, $exonerado);
        $sheet->setCellValue('M' . $cont, 0);
        $sheet->setCellValue('N' . $cont, $ven['Total']);
        if ($ven['CodSunatModifica'] != null || $ven['CodSunatModifica'] != '') {
            $sheet->setCellValue('O' . $cont, $ven['CodSunatModifica']);
            $comprobante = explode('-', $ven['NroComprobanteModifica']);
            $serie = $comprobante[0];
            $numero = $comprobante[1];
            $sheet->setCellValue('P' . $cont, $serie);
            $sheet->setCellValue('Q' . $cont, $numero);
            $sheet->setCellValue('R' . $cont, $ven['NotaDescMotivo']);
        }

        // $sheet->setCellValue('N'.$cont, $ven['Anulado']);;

        $cont += 1;
    }

    aplicarIdentidadEmpresaExcel($excel, $this->db);
    $excelWriter = new Xlsx($excel);

    $fileName = 'reporteventas.xlsx';
    $excelFileName = __DIR__ . '/reporte/' . $fileName;
    $excelWriter->save($excelFileName);
    // For Excel2007 and above .xlsx files   
    // $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    /*$response = $response->withHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    $stream = fopen($excelFileName, 'r+');
    return $response->withBody(new \Slim\Http\Stream($stream));*/

    echo "<script>window.location.href = '/api/reporte/" . $fileName . "'</script>";
    exit;
});

$app->get('/reporte/rankingclientes', function (Request $request, Response $response, array $args) use ($app) {
    $idalmacen = $request->getParam('idalmacen');
    $fechaInicio = $request->getParam('fechaInicio');
    $fechaFin = $request->getParam('fechaFin');
    $res = $app->subRequest('GET', "ranking/clientes", 'idalmacen=' . urlencode($idalmacen) . '&fechaInicio=' . urlencode($fechaInicio) . '&fechaFin=' . urlencode($fechaFin) . '&limit=1000');

    $deudacliente = (string) $res->getBody();
    $deudacliente = json_decode($deudacliente, true);

    $excel = new Spreadsheet();
    //$sheet = $excel->setActiveSheetIndex(0);
    $sheet = $excel->getActiveSheet();
    $sheet->setCellValue('A1', 'DNI / RUC');
    $sheet->setCellValue('B1', 'CLIENTE');
    $sheet->setCellValue('C1', 'TELEFONO');
    $sheet->setCellValue('D1', 'FECHA NACIMIENTO');
    $sheet->setCellValue('E1', 'PUNTOS');
    $sheet->setCellValue('F1', 'TOTAL');

    $cont = 3;
    foreach ($deudacliente as $prod) {
        $sheet->setCellValue('A' . $cont, $prod['DniRuc']);
        $sheet->setCellValue('B' . $cont, $prod['Cliente']);
        $sheet->setCellValue('C' . $cont, $prod['Telefono']);
        $sheet->setCellValue('D' . $cont, date("d/m/Y", strtotime($prod['FechaNacimiento'])));
        $sheet->setCellValue('E' . $cont, $prod['Puntos']);
        $sheet->setCellValue('F' . $cont, 'S/. ' . $prod['Total']);
        $cont += 1;
    }

    aplicarIdentidadEmpresaExcel($excel, $this->db);
    $excelWriter = new Xlsx($excel);

    $fileName = 'rankingclientes' . getNow('Y-m-d-H-i-s') .  '.xlsx';
    $excelFileName = __DIR__ . '/reporte/' . $fileName;
    $excelWriter->save($excelFileName);

    echo "<script>window.location.href = '/api/reporte/" . $fileName . "'</script>";
    exit;
});




// REPORTES 
$app->get('/reporte/stock', function (Request $request, Response $response, array $args) use ($app) {
    $idAlmacen = $request->getParam('idAlmacen');
    $filter = $request->getParam('filter');
    $fechaDesde = $request->getParam('fechaDesde');
    $fechaHasta = $request->getParam('fechaHasta');

    $reportParams = array('idAlmacen' => $idAlmacen, 'filter' => $filter, 'noLimit' => 1);
    if ($fechaDesde) $reportParams['fechaDesde'] = $fechaDesde;
    if ($fechaHasta) $reportParams['fechaHasta'] = $fechaHasta;

    $res = $app->subRequest('GET', '/productos/stock', http_build_query($reportParams));
    $productos = (string) $res->getBody();
    $productos = json_decode($productos, true);

    $excel = new Spreadsheet();
    $sheet = $excel->getActiveSheet();
    $sheet->setTitle('Stock valorizado');
    $sheet->mergeCells('A1:M1');
    $sheet->setCellValue('A1', 'REPORTE DE STOCK VALORIZADO');
    $sheet->getStyle('A1:J1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF0A4F83');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('FFFFFFFF');
    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('center');
    $sheet->getRowDimension(1)->setRowHeight(28);
    $sheet->mergeCells('A2:J2');
    $periodoReporte = ($fechaDesde && $fechaHasta) ? ('Periodo: ' . $fechaDesde . ' al ' . $fechaHasta) : 'Stock hasta la fecha actual';
    $sheet->setCellValue('A2', 'Almacen: ' . $idAlmacen . '  |  ' . $periodoReporte . '  |  Generado: ' . date('d/m/Y H:i'));
    $sheet->getStyle('A2:J2')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFEAF5FB');
    $sheet->getStyle('A2')->getFont()->setBold(true)->getColor()->setARGB('FF315A75');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

    $headersStock = array('ID PRODUCTO', 'CODIGO', 'CATEGORIA', 'PRODUCTO', 'MARCA', 'PRECIO COSTO', 'PRECIO CONTADO', 'STOCK', 'VALORIZADO COSTO', 'VALORIZADO VENTA');
    foreach ($headersStock as $indexStock => $headerStock) $sheet->setCellValue(chr(65 + $indexStock) . '4', $headerStock);
    $sheet->getStyle('A4:J4')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF087FAE');
    $sheet->getStyle('A4:J4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
    $sheet->getStyle('A4:J4')->getAlignment()->setHorizontal('center')->setVertical('center');
    $sheet->getRowDimension(4)->setRowHeight(25);

    $cont = 5;
    foreach ($productos as $prod) {
        $stock = (float)$prod['stock'];
        $costo = (float)$prod['PrecioCosto'];
        $venta = (float)$prod['PrecioContado'];
        $sheet->fromArray(array($prod['IdProducto'], $prod['CodigoBarra'], $prod['ProductoCategoria'], $prod['Producto'], $prod['ProductoMarca'], $costo, $venta, $stock, $costo * $stock, $venta * $stock), null, 'A' . $cont);
        if ($cont % 2 === 0) $sheet->getStyle('A' . $cont . ':J' . $cont)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFF2F8FC');
        $stockColor = $stock > 0 ? 'FF008A50' : 'FFBE2A45';
        $stockFill = $stock > 0 ? 'FFD9F5E7' : 'FFFFE0E6';
        $sheet->getStyle('H' . $cont)->getFont()->setBold(true)->getColor()->setARGB($stockColor);
        $sheet->getStyle('H' . $cont)->getFill()->setFillType('solid')->getStartColor()->setARGB($stockFill);
        $cont++;
    }

    $lastStockRow = $cont - 1;
    $sheet->setCellValue('G' . $cont, 'TOTALES');
    $sheet->setCellValue('H' . $cont, $lastStockRow >= 5 ? '=SUM(H5:H' . $lastStockRow . ')' : 0);
    $sheet->setCellValue('I' . $cont, $lastStockRow >= 5 ? '=SUM(I5:I' . $lastStockRow . ')' : 0);
    $sheet->setCellValue('J' . $cont, $lastStockRow >= 5 ? '=SUM(J5:J' . $lastStockRow . ')' : 0);
    $sheet->getStyle('G' . $cont . ':J' . $cont)->getFill()->setFillType('solid')->getStartColor()->setARGB('FF0A4F83');
    $sheet->getStyle('G' . $cont . ':J' . $cont)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
    $sheet->getStyle('A4:J' . $cont)->getBorders()->getAllBorders()->setBorderStyle('thin')->getColor()->setARGB('FFD5E3ED');
    $sheet->getStyle('F5:G' . $cont)->getNumberFormat()->setFormatCode('"S/ "#,##0.00');
    $sheet->getStyle('I5:J' . $cont)->getNumberFormat()->setFormatCode('"S/ "#,##0.00');
    $sheet->getStyle('H5:H' . $cont)->getNumberFormat()->setFormatCode('#,##0.00');
    foreach (array('A'=>14,'B'=>18,'C'=>20,'D'=>42,'E'=>23,'F'=>17,'G'=>18,'H'=>13,'I'=>20,'J'=>21) as $stockColumn => $stockWidth) $sheet->getColumnDimension($stockColumn)->setWidth($stockWidth);
    $sheet->getStyle('D5:D' . max(5, $lastStockRow))->getAlignment()->setWrapText(true);
    $sheet->freezePane('A5');
    $sheet->setAutoFilter('A4:J' . max(4, $lastStockRow));
    $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);
    $sheet->getPageMargins()->setTop(0.4)->setRight(0.3)->setLeft(0.3)->setBottom(0.4);

    aplicarIdentidadEmpresaExcel($excel, $this->db);
    $excelWriter = new Xlsx($excel);

    $fileName = 'stock' . getNow('Y-m-d-H-i-s') .  '.xlsx';
    $excelFileName = __DIR__ . '/reporte/' . $fileName;
    $excelWriter->save($excelFileName);
    // For Excel2007 and above .xlsx files   
    // $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    /* $response = $response->withHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    $stream = fopen($excelFileName, 'r+');
    return $response->withBody(new \Slim\Http\Stream($stream));*/
    echo "<script>window.location.href = '/api/reporte/" . $fileName . "'</script>";
    exit;
});

$app->get('/reporte/stock-minimo-proveedor', function (Request $request, Response $response, array $args) use ($app) {
    $idAlmacen = (int)($request->getParam('idAlmacen') ?: 1);
    $reportParams = array(
        'idAlmacen' => $idAlmacen,
        'filter' => array('minimo' => 1),
        'noLimit' => 1,
        'sortBy' => 'Producto',
        'sortDesc' => 0
    );
    $res = $app->subRequest('GET', '/productos/stock', http_build_query($reportParams));
    $productos = json_decode((string)$res->getBody(), true);
    if (!is_array($productos)) $productos = array();

    $proveedores = array();
    $ids = array_values(array_filter(array_map(function ($producto) { return (int)($producto['IdProducto'] ?? 0); }, $productos)));
    if ($ids) {
        $sqlProveedor = "SELECT gp.IdProducto,
            COALESCE((SELECT lp.Proveedor FROM Lo_MovimientoDetalle lmd
                INNER JOIN Lo_Movimiento lm ON lm.Hash = lmd.hashMovimiento
                LEFT JOIN Lo_Proveedor lp ON lp.IdProveedor = lm.IdProveedor
                WHERE lmd.IdProducto = gp.IdProducto AND lm.IdProveedor IS NOT NULL
                ORDER BY lm.FechaReg DESC, lm.Hash DESC LIMIT 1), 'SIN PROVEEDOR') AS Proveedor,
            COALESCE((SELECT lp.Ruc FROM Lo_MovimientoDetalle lmd
                INNER JOIN Lo_Movimiento lm ON lm.Hash = lmd.hashMovimiento
                LEFT JOIN Lo_Proveedor lp ON lp.IdProveedor = lm.IdProveedor
                WHERE lmd.IdProducto = gp.IdProducto AND lm.IdProveedor IS NOT NULL
                ORDER BY lm.FechaReg DESC, lm.Hash DESC LIMIT 1), '') AS ProveedorRuc
            FROM Gen_Producto gp WHERE gp.IdProducto IN (" . implode(',', $ids) . ")";
        $stmtProveedor = $this->db->query($sqlProveedor);
        $stmtProveedor->execute();
        foreach ($stmtProveedor->fetchAll() as $filaProveedor) $proveedores[(int)$filaProveedor['IdProducto']] = $filaProveedor;
    }
    foreach ($productos as &$producto) {
        $idProducto = (int)($producto['IdProducto'] ?? 0);
        $producto['Proveedor'] = $proveedores[$idProducto]['Proveedor'] ?? 'SIN PROVEEDOR';
        $producto['ProveedorRuc'] = $proveedores[$idProducto]['ProveedorRuc'] ?? '';
    }
    unset($producto);
    usort($productos, function ($a, $b) {
        $porProveedor = strcasecmp($a['Proveedor'], $b['Proveedor']);
        return $porProveedor !== 0 ? $porProveedor : strcasecmp($a['Producto'], $b['Producto']);
    });

    $excel = new Spreadsheet();
    $sheet = $excel->getActiveSheet();
    $sheet->setTitle('Stock mínimo por proveedor');
    $sheet->mergeCells('A1:J1');
    $sheet->setCellValue('A1', 'PRODUCTOS EN STOCK MÍNIMO POR PROVEEDOR');
    $sheet->getStyle('A1:M1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF075584');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('FFFFFFFF');
    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('center');
    $sheet->getRowDimension(1)->setRowHeight(29);
    $sheet->mergeCells('A2:M2');
    $sheet->setCellValue('A2', 'Almacén: ' . $idAlmacen . '  |  Orden: proveedor A-Z  |  Generado: ' . date('d/m/Y H:i'));
    $sheet->getStyle('A2:M2')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFEAF5FB');
    $sheet->getStyle('A2')->getFont()->setBold(true)->getColor()->setARGB('FF315A75');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

    $headers = array('PROVEEDOR', 'RUC PROVEEDOR', 'CÓDIGO', 'PRODUCTO', 'CATEGORÍA', 'MARCA', 'MODELO', 'TALLA', 'COLOR', 'MEDICIÓN', 'STOCK MÍNIMO', 'STOCK ACTUAL', 'CANTIDAD A REPONER');
    foreach ($headers as $index => $header) $sheet->setCellValue(chr(65 + $index) . '4', $header);
    $sheet->getStyle('A4:M4')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF0785A9');
    $sheet->getStyle('A4:M4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
    $sheet->getStyle('A4:M4')->getAlignment()->setHorizontal('center')->setVertical('center');
    $sheet->getRowDimension(4)->setRowHeight(26);

    $row = 5;
    $previousProvider = null;
    foreach ($productos as $producto) {
        $stockMinimo = (float)($producto['StockMinimo'] ?? 0);
        $stockActual = (float)($producto['stock'] ?? 0);
        $reponer = max(0, $stockMinimo - $stockActual);
        $sheet->fromArray(array(
            $producto['Proveedor'], $producto['ProveedorRuc'], $producto['CodigoBarra'] ?? '', $producto['Producto'] ?? '',
            $producto['ProductoCategoria'] ?? '', $producto['ProductoMarca'] ?? '', $producto['ProductoModelo'] ?? '',
            $producto['ProductoTalla'] ?? ($producto['Talla'] ?? ''), $producto['Color'] ?? '', $producto['ProductoMedicion'] ?? '',
            $stockMinimo, $stockActual, $reponer
        ), null, 'A' . $row);
        if ($previousProvider !== $producto['Proveedor']) {
            $sheet->getStyle('A' . $row . ':M' . $row)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFDDF3FA');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->getColor()->setARGB('FF075584');
            $previousProvider = $producto['Proveedor'];
        } elseif ($row % 2 === 0) {
            $sheet->getStyle('A' . $row . ':M' . $row)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFF5F9FC');
        }
        $sheet->getStyle('L' . $row)->getFont()->setBold(true)->getColor()->setARGB($stockActual <= 0 ? 'FFBE2A45' : 'FFE08600');
        $sheet->getStyle('M' . $row)->getFont()->setBold(true)->getColor()->setARGB('FFBE2A45');
        $row++;
    }
    $lastRow = max(4, $row - 1);
    $sheet->getStyle('A4:M' . max(4, $row - 1))->getBorders()->getAllBorders()->setBorderStyle('thin')->getColor()->setARGB('FFD5E3ED');
    $sheet->getStyle('K5:M' . max(5, $lastRow))->getNumberFormat()->setFormatCode('#,##0.00');
    foreach (array('A'=>34,'B'=>18,'C'=>17,'D'=>36,'E'=>20,'F'=>20,'G'=>18,'H'=>13,'I'=>16,'J'=>15,'K'=>17,'L'=>16,'M'=>21) as $column => $width) $sheet->getColumnDimension($column)->setWidth($width);
    $sheet->getStyle('A5:M' . max(5, $lastRow))->getAlignment()->setVertical('center');
    $sheet->getStyle('A5:I' . max(5, $lastRow))->getAlignment()->setWrapText(true);
    $sheet->freezePane('A5');
    $sheet->setAutoFilter('A4:M' . $lastRow);
    $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);

    $fileName = 'stock-minimo-por-proveedor-' . getNow('Y-m-d-H-i-s') . '.xlsx';
    aplicarIdentidadEmpresaExcel($excel, $this->db);
    (new Xlsx($excel))->save(__DIR__ . '/reporte/' . $fileName);
    echo "<script>window.location.href = '/api/reporte/" . $fileName . "'</script>";
    exit;
});

$app->post('/reporte/stock-minimo/importar', function (Request $request, Response $response, array $args) {
    $files = $request->getUploadedFiles();
    if (empty($files['archivo']) || $files['archivo']->getError() !== UPLOAD_ERR_OK) {
        return $response->withJson(array('error' => 'No se recibió un archivo Excel válido.'), 400);
    }
    $file = $files['archivo'];
    $extension = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
    if (!in_array($extension, array('xlsx', 'xls'))) return $response->withJson(array('error' => 'Solo se permiten archivos .xlsx o .xls.'), 400);
    try {
        $temporaryPath = $_FILES['archivo']['tmp_name'] ?? '';
        if (!$temporaryPath || !is_file($temporaryPath)) throw new Exception('No se encontró el archivo temporal.');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($temporaryPath);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        $headerIndex = -1;
        $headers = array();
        foreach ($rows as $index => $row) {
            $normalized = array_map(function ($value) { return strtoupper(trim((string)$value)); }, $row);
            if (in_array('CÓDIGO', $normalized) && in_array('CANTIDAD A REPONER', $normalized)) {
                $headerIndex = $index;
                foreach ($normalized as $column => $name) $headers[$name] = $column;
                break;
            }
        }
        if ($headerIndex < 0) return $response->withJson(array('error' => 'El archivo no corresponde al reporte de stock mínimo.'), 422);
        $items = array();
        for ($index = $headerIndex + 1; $index < count($rows); $index++) {
            $row = $rows[$index];
            $codigo = trim((string)($row[$headers['CÓDIGO']] ?? ''));
            $cantidad = (float)($row[$headers['CANTIDAD A REPONER']] ?? 0);
            if ($codigo === '' || $cantidad <= 0) continue;
            $items[] = array(
                'proveedor' => trim((string)($row[$headers['PROVEEDOR']] ?? '')),
                'rucProveedor' => trim((string)($row[$headers['RUC PROVEEDOR']] ?? '')),
                'codigo' => $codigo,
                'producto' => trim((string)($row[$headers['PRODUCTO']] ?? '')),
                'modelo' => trim((string)($row[$headers['MODELO']] ?? '')),
                'talla' => trim((string)($row[$headers['TALLA']] ?? '')),
                'color' => trim((string)($row[$headers['COLOR']] ?? '')),
                'cantidad' => $cantidad
            );
        }
        if ($items) {
            $quotedCodes = array_map(function ($item) { return $this->db->quote($item['codigo']); }, $items);
            $stmtProducts = $this->db->query("SELECT IdProducto, CodigoBarra, Codigo FROM Gen_Producto WHERE CodigoBarra IN (" . implode(',', $quotedCodes) . ") OR Codigo IN (" . implode(',', $quotedCodes) . ")");
            $stmtProducts->execute();
            $productIds = array();
            foreach ($stmtProducts->fetchAll() as $product) {
                if (!empty($product['CodigoBarra'])) $productIds[strtoupper(trim($product['CodigoBarra']))] = (int)$product['IdProducto'];
                if (!empty($product['Codigo'])) $productIds[strtoupper(trim($product['Codigo']))] = (int)$product['IdProducto'];
            }
            foreach ($items as &$item) $item['idProducto'] = $productIds[strtoupper(trim($item['codigo']))] ?? 0;
            unset($item);
        }
        return $response->withJson(array('items' => $items, 'total' => count($items)));
    } catch (Exception $error) {
        return $response->withJson(array('error' => 'No se pudo leer el Excel: ' . $error->getMessage()), 422);
    }
});

function kardexSaldo($kardex)
{
    $newKardex = [];
    $saldo = 0;
    $saldoCostoTotal = 0;
    $saldoCostoUnitario = 0;
    foreach ($kardex as $kar) {
        $saldo += ($kar['IngresoCantidad'] - $kar['SalidaCantidad']);
        $kar['saldo'] = $saldo;
        if ($kar['IngresoCantidad'] > 0) {
            $saldoCostoTotal = $saldoCostoTotal + ($kar['IngresoCantidad'] * $kar['IngresoPrecio']);
            // dxerror division por cero
            if ($saldo == 0) {
                $saldoCostoUnitario = $saldoCostoTotal;
            } else {
                $saldoCostoUnitario = $saldoCostoTotal / $saldo;
            }
        } else {
            // la salida Precio es el ultimo costo unitario de saldo
            $kar['SalidaPrecio'] = round($saldoCostoUnitario, 2);

            $saldoCostoTotal = $saldo * $kar['SalidaPrecio'];
            $saldoCostoUnitario = $kar['SalidaPrecio'];
        }
        $kar['saldoCostoUnitario'] = round($saldoCostoUnitario, 2);
        $kar['saldoCostoTotal'] = round($saldoCostoTotal, 2);

        array_push($newKardex, $kar);
    }
    return $newKardex;
}
function kardexSaldoFecha($kardex, $fechaDesde)
{
    $inicialEntradas = 0;
    $inicialSalidas = 0;
    $inicialSaldoCostoTotal = 0;
    $inicialSaldoCostoUnitario = 0;

    /*$filtro = function($item) use ($fechaDesde, $inicialEntradas, $inicialSalidas, $inicialSaldoCostoTotal, $inicialSaldoCostoUnitario) {
        if ($item['Fecha'] >= $fechaDesde) {
            return true;
        } else {
            $inicialEntradas += (float)($item['IngresoCantidad']);
            $inicialSalidas += (float)($item['SalidaCantidad']);
            $inicialSaldoCostoTotal = $item['saldoCostoTotal'];
            $inicialSaldoCostoUnitario = $item['saldoCostoUnitario'];
            return false;
        }
    };*/
    $newKardex = [];
    $kardexSaldo = kardexSaldo($kardex);
    foreach ($kardexSaldo as $item) {
        if ($item['Fecha'] >= $fechaDesde) {
            array_push($newKardex, $item);
        } else {
            $inicialEntradas += (float)($item['IngresoCantidad']);
            $inicialSalidas += (float)($item['SalidaCantidad']);
            $inicialSaldoCostoTotal = $item['saldoCostoTotal'];
            $inicialSaldoCostoUnitario = $item['saldoCostoUnitario'];
        }
    }

    // $newKardex = array_filter(kardexSaldo($kardex), $filtro);

    array_unshift($newKardex, array(
        'Fecha' => $fechaDesde,
        'UsuarioReg' => '---',
        'TipoDocSunat' => 1,
        'IngresoCantidad' => $inicialEntradas,
        'IngresoPrecio' => 0,
        'SalidaCantidad' => $inicialSalidas,
        'SalidaPrecio' => 0,
        'saldo' => $inicialEntradas - $inicialSalidas,
        'saldoCostoTotal' => round($inicialSaldoCostoTotal, 2),
        'saldoCostoUnitario' => round($inicialSaldoCostoUnitario, 2)
    ));

    return $newKardex;
}


// TEMPLATE XLXS
$app->get('/reporte/producto/kardex', function (Request $request, Response $response, array $args) use ($app) {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/productokardex.xlsx');
    $formato = $spreadsheet->getSheet(0);
    // $formato->getColumnDimension('B')->setAutoSize(true);
    $idProducto = $request->getParam('idProducto');
    $producto = $request->getParam('producto');
    $codigoBarra = $request->getParam('codigoBarra');
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaDesde = $request->getParam('fechaDesde');
    $fechaHasta = $request->getParam('fechaHasta');
    $productoMarca = $request->getParam('productoMarca');
    //$productoFF = $request->getParam('productoFF');
    $res = $app->subRequest('GET', '/productos/kardex/' . $idProducto, 'idAlmacen=' . $idAlmacen . '&fechaHasta=' . $fechaHasta . '&fechaDesde=' . $fechaDesde);
    $kardex = (string) $res->getBody();
    $kardex = json_decode($kardex, true);
    $kardex = kardexSaldo($kardex);
    $init = 14;

    foreach ($kardex as $kar) {
        $formato->getCell('A' . $init)->setValue($kar['Fecha']);
        $formato->duplicateStyle($formato->getStyle('A' . $init), 'A' . ($init + 1));

        $formato->getCell('B' . $init)->setValue(@$kar['Detalle'] . ' - ' . @$kar['Serie'] . ' - ' . @$kar['Numero']);
        $formato->duplicateStyle($formato->getStyle('B' . $init), 'B' . ($init + 1));

        $formato->getCell('C' . $init)->setValue(@$kar['Nombres']);
        $formato->duplicateStyle($formato->getStyle('C' . $init), 'C' . ($init + 1));

        $formato->getCell('D' . $init)->setValue(@$kar['IngresoCantidad']);
        $formato->duplicateStyle($formato->getStyle('D' . $init), 'D' . ($init + 1));

        $formato->getCell('E' . $init)->setValue($kar['IngresoPrecio']);
        $formato->duplicateStyle($formato->getStyle('E' . $init), 'E' . ($init + 1));

        $formato->getCell('F' . $init)->setValue($kar['SalidaCantidad']);
        $formato->duplicateStyle($formato->getStyle('F' . $init), 'F' . ($init + 1));

        $formato->getCell('G' . $init)->setValue($kar['SalidaPrecio']);
        $formato->duplicateStyle($formato->getStyle('G' . $init), 'G' . ($init + 1));

        $formato->getCell('H' . $init)->setValue($kar['saldo']);
        $formato->duplicateStyle($formato->getStyle('H' . $init), 'H' . ($init + 1));

        if ($init % 2 === 0) {
            $formato->getStyle('A' . $init . ':H' . $init)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFF2F7FB');
        }
        if ($kar['IngresoCantidad'] > 0) {
            $formato->getStyle('D' . $init . ':E' . $init)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFD9F5E7');
            $formato->getStyle('D' . $init . ':E' . $init)->getFont()->setBold(true)->getColor()->setARGB('FF007A4A');
        }
        if ($kar['SalidaCantidad'] > 0) {
            $formato->getStyle('F' . $init . ':G' . $init)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFFFE0E6');
            $formato->getStyle('F' . $init . ':G' . $init)->getFont()->setBold(true)->getColor()->setARGB('FFB51F3C');
        }
        $formato->getStyle('H' . $init)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFDCECF7');
        $formato->getStyle('H' . $init)->getFont()->setBold(true)->getColor()->setARGB('FF0B4F7F');

        $init += 1;
    }
    $formato->getCell('A' . ($init + 2))->setValue('(1) Dirección del Establecimiento o Código según el Registro Único de Contribuyentes.');

    $formato->getCell('C' . $init)->setValue('TOTALES');
    $formato->getCell('D' . $init)->setValue('=SUM(D14:D' . ($init - 1) . ')');
    $formato->getCell('E' . $init)->setValue('=SUM(E14:E' . ($init - 1) . ')');
    $formato->getCell('F' . $init)->setValue('=SUM(F14:F' . ($init - 1) . ')');
    $formato->getCell('G' . $init)->setValue('=SUM(G14:G' . ($init - 1) . ')');
    $formato->getStyle('C' . $init)->getFont()->setBold(true);
    $formato->getStyle('D' . $init)->getFont()->setBold(true);
    $formato->getStyle('E' . $init)->getFont()->setBold(true);
    $formato->getStyle('F' . $init)->getFont()->setBold(true);
    $formato->getStyle('G' . $init)->getFont()->setBold(true);

    $formato->setTitle('Kardex producto');
    $formato->getStyle('A12:H13')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF0B5787');
    $formato->getStyle('A12:H13')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
    $formato->getStyle('A12:H13')->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
    $formato->getStyle('C' . $init . ':H' . $init)->getFill()->setFillType('solid')->getStartColor()->setARGB('FF0A365F');
    $formato->getStyle('C' . $init . ':H' . $init)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
    $formato->getStyle('A12:H' . $init)->getBorders()->getAllBorders()->setBorderStyle('thin')->getColor()->setARGB('FFD4E2EC');
    $formato->getStyle('D14:H' . $init)->getNumberFormat()->setFormatCode('#,##0.00');
    foreach (array('A'=>19,'B'=>34,'C'=>28,'D'=>14,'E'=>16,'F'=>14,'G'=>16,'H'=>14) as $kardexColumn => $kardexWidth) {
        $formato->getColumnDimension($kardexColumn)->setWidth($kardexWidth);
    }
    $formato->getStyle('B14:C' . ($init - 1))->getAlignment()->setWrapText(true);
    $formato->freezePane('A14');
    $formato->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);
    $formato->getPageMargins()->setTop(0.4)->setRight(0.3)->setLeft(0.3)->setBottom(0.4);

    $formato->getCell('B8')->setValue($fechaDesde);
    $formato->getCell('B9')->setValue($fechaHasta);
    $formato->getCell('B3')->setValue($codigoBarra);
    $formato->getCell('B4')->setValue($producto);
    $formato->getCell('B5')->setValue($productoMarca);
    //$formato->getCell('B6')->setValue($productoFF);


    aplicarIdentidadEmpresaExcel($spreadsheet, $this->db);
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('reporte/productokardex.xlsx');

    echo '<script type="text/javascript">
        location.href = "/api/reporte/productokardex.xlsx";
    </script>';

    // return $response->withRedirect('/api/reporte/formato12.1.xlsx');
});

// TEMPLATE XLXS
$app->get('/reporte/kardexsimple', function (Request $request, Response $response, array $args) use ($app) {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/formato12.1.xlsx');
    $formato = $spreadsheet->getSheet(0);
    // $formato->getColumnDimension('B')->setAutoSize(true);
    $idProducto = $request->getParam('idProducto');
    $producto = $request->getParam('producto');
    $codigoBarra = $request->getParam('codigoBarra');
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaDesde = $request->getParam('fechaDesde');
    $fechaHasta = $request->getParam('fechaHasta');

    $res = $app->subRequest('GET', '/productos/kardex/' . $idProducto, 'idAlmacen=' . $idAlmacen . '&fechaHasta=' . $fechaHasta);
    $kardex = (string) $res->getBody();
    $kardex = json_decode($kardex, true);

    $kardex = kardexSaldoFecha($kardex, $fechaDesde);
    $init = 16;

    foreach ($kardex as $kar) {
        $formato->getCell('A' . $init)->setValue($kar['Fecha']);
        $formato->duplicateStyle($formato->getStyle('A' . $init), 'A' . ($init + 1));

        $formato->getCell('B' . $init)->setValue(@$kar['CodSunat']);
        $formato->duplicateStyle($formato->getStyle('B' . $init), 'B' . ($init + 1));

        $formato->getCell('C' . $init)->setValue(@$kar['Serie']);
        $formato->duplicateStyle($formato->getStyle('C' . $init), 'C' . ($init + 1));

        $formato->getCell('D' . $init)->setValue(@$kar['Numero']);
        $formato->duplicateStyle($formato->getStyle('D' . $init), 'D' . ($init + 1));

        $formato->getCell('E' . $init)->setValue($kar['TipoDocSunat']);
        $formato->duplicateStyle($formato->getStyle('E' . $init), 'E' . ($init + 1));

        $formato->getCell('F' . $init)->setValue($kar['IngresoCantidad']);
        $formato->duplicateStyle($formato->getStyle('F' . $init), 'F' . ($init + 1));

        $formato->getCell('G' . $init)->setValue($kar['SalidaCantidad']);
        $formato->duplicateStyle($formato->getStyle('G' . $init), 'G' . ($init + 1));

        $formato->getCell('H' . $init)->setValue($kar['saldo']);
        $formato->duplicateStyle($formato->getStyle('H' . $init), 'H' . ($init + 1));

        $init += 1;
    }
    $formato->getCell('A' . ($init + 2))->setValue('(1) Dirección del Establecimiento o Código según el Registro Único de Contribuyentes.');

    $formato->getCell('E' . $init)->setValue('TOTALES');
    $formato->getCell('F' . $init)->setValue('=SUM(F16:F' . ($init - 1) . ')');
    $formato->getCell('G' . $init)->setValue('=SUM(G16:G' . ($init - 1) . ')');
    $formato->getStyle('E' . $init)->getFont()->setBold(true);
    $formato->getStyle('F' . $init)->getFont()->setBold(true);
    $formato->getStyle('G' . $init)->getFont()->setBold(true);

    $formato->getCell('D4')->setValue('Del ' . $fechaDesde . ' al ' . $fechaHasta);
    $formato->getCell('D8')->setValue($codigoBarra);
    $formato->getCell('D10')->setValue($producto);


    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    aplicarIdentidadEmpresaExcel($spreadsheet, $this->db);
    $writer->save('reporte/formato12.1.xlsx');

    echo '<script type="text/javascript">
        location.href = "/api/reporte/formato12.1.xlsx";
    </script>';

    // return $response->withRedirect('/api/reporte/formato12.1.xlsx');
});

$app->get('/reporte/kardexvalorizado', function (Request $request, Response $response, array $args) use ($app) {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/formato13.1.xlsx');
    $formato = $spreadsheet->getSheet(0);
    // $formato->getColumnDimension('B')->setAutoSize(true);
    $idProducto = $request->getParam('idProducto');
    $producto = $request->getParam('producto');
    $codigoBarra = $request->getParam('codigoBarra');
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaDesde = $request->getParam('fechaDesde');
    $fechaHasta = $request->getParam('fechaHasta');

    $res = $app->subRequest('GET', '/productos/kardex/' . $idProducto, 'idAlmacen=' . $idAlmacen . '&fechaHasta=' . $fechaHasta);
    $kardex = (string) $res->getBody();
    $kardex = json_decode($kardex, true);

    $kardex = kardexSaldoFecha($kardex, $fechaDesde);
    $init = 17;

    $saldoCostoUnitario = 0;

    foreach ($kardex as $kar) {
        $formato->getCell('A' . $init)->setValue($kar['Fecha']);
        $formato->duplicateStyle($formato->getStyle('A' . $init), 'A' . ($init + 1));

        $formato->getCell('B' . $init)->setValue(@$kar['CodSunat']);
        $formato->duplicateStyle($formato->getStyle('B' . $init), 'B' . ($init + 1));

        $formato->getCell('C' . $init)->setValue(@$kar['Serie']);
        $formato->duplicateStyle($formato->getStyle('C' . $init), 'C' . ($init + 1));

        $formato->getCell('D' . $init)->setValue(@$kar['Numero']);
        $formato->duplicateStyle($formato->getStyle('D' . $init), 'D' . ($init + 1));

        $formato->getCell('E' . $init)->setValue($kar['TipoDocSunat']);
        $formato->duplicateStyle($formato->getStyle('E' . $init), 'E' . ($init + 1));

        $formato->getCell('F' . $init)->setValue($kar['IngresoCantidad']);
        $formato->duplicateStyle($formato->getStyle('F' . $init), 'F' . ($init + 1));

        $formato->getCell('G' . $init)->setValue(@$kar['IngresoPrecio']);
        $formato->duplicateStyle($formato->getStyle('G' . $init), 'G' . ($init + 1));

        $formato->getCell('H' . $init)->setValue($kar['IngresoCantidad'] * $kar['IngresoPrecio']);
        $formato->duplicateStyle($formato->getStyle('H' . $init), 'H' . ($init + 1));

        $formato->getCell('I' . $init)->setValue($kar['SalidaCantidad']);
        $formato->duplicateStyle($formato->getStyle('I' . $init), 'I' . ($init + 1));

        $formato->getCell('J' . $init)->setValue(@$kar['SalidaPrecio']);
        $formato->duplicateStyle($formato->getStyle('J' . $init), 'J' . ($init + 1));

        $formato->getCell('K' . $init)->setValue($kar['SalidaCantidad'] * $kar['SalidaPrecio']);
        $formato->duplicateStyle($formato->getStyle('K' . $init), 'K' . ($init + 1));

        $formato->getCell('L' . $init)->setValue($kar['saldo']);
        $formato->duplicateStyle($formato->getStyle('L' . $init), 'L' . ($init + 1));

        $formato->getCell('M' . $init)->setValue($kar['saldoCostoUnitario']);
        $formato->duplicateStyle($formato->getStyle('M' . $init), 'M' . ($init + 1));

        $formato->getCell('N' . $init)->setValue(@$kar['saldoCostoTotal']);
        $formato->duplicateStyle($formato->getStyle('N' . $init), 'N' . ($init + 1));

        $saldoCostoUnitario = $kar['saldoCostoUnitario'];

        $init += 1;
    }
    $formato->getCell('A' . ($init + 2))->setValue('(1) Dirección del Establecimiento o Código según el Registro Único de Contribuyentes.');

    $formato->getCell('E' . $init)->setValue('TOTALES');
    $formato->getCell('F' . $init)->setValue('=SUM(F17:F' . ($init - 1) . ')');
    $formato->getCell('H' . $init)->setValue('=SUM(H17:H' . ($init - 1) . ')');
    $formato->getCell('I' . $init)->setValue('=SUM(I17:I' . ($init - 1) . ')');
    $formato->getCell('K' . $init)->setValue('=SUM(K17:K' . ($init - 1) . ')');


    $formato->getStyle('E' . $init)->getFont()->setBold(true);
    $formato->getStyle('F' . $init)->getFont()->setBold(true);
    $formato->getStyle('H' . $init)->getFont()->setBold(true);
    $formato->getStyle('I' . $init)->getFont()->setBold(true);
    $formato->getStyle('K' . $init)->getFont()->setBold(true);

    $formato->getCell('D3')->setValue('Del ' . $fechaDesde . ' al ' . $fechaHasta);
    $formato->getCell('D7')->setValue($codigoBarra);
    $formato->getCell('D9')->setValue($producto);


    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    aplicarIdentidadEmpresaExcel($spreadsheet, $this->db);
    $writer->save('reporte/formato13.1.xlsx');

    echo '<script type="text/javascript">
        location.href = "/api/reporte/formato13.1.xlsx";
    </script>';

    //return $response->withRedirect('/api/reporte/formato13.1.xlsx');
});

$app->get('/reporte/formato37/exportar', function (Request $request, Response $response, array $args) use ($app) {
    /* $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/formato3.7.xlsx');
    $formato = $spreadsheet->getSheet(0);

    $anio = $request->getParam('anio');

    $select = "SELECT * FROM tmp_formato37";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();  

    $init = 12;

    foreach($data as $kar) {
        $formato->getCell('A'.$init)->setValue($kar['CodigoBarra']);
        $formato->duplicateStyle($formato->getStyle('A'.$init),'A'.($init+1));

        $formato->getCell('B'.$init)->setValue('01');
        $formato->duplicateStyle($formato->getStyle('B'.$init),'B'.($init+1));

        $formato->getCell('C'.$init)->setValue($kar['Producto']);
        $formato->duplicateStyle($formato->getStyle('C'.$init),'C'.($init+1));

        $formato->getCell('D'.$init)->setValue('07');
        $formato->duplicateStyle($formato->getStyle('D'.$init),'D'.($init+1));

        $formato->getCell('E'.$init)->setValue($kar['Saldo']);
        $formato->duplicateStyle($formato->getStyle('E'.$init),'E'.($init+1));

        $formato->getCell('F'.$init)->setValue($kar['SaldoCostoUnitario']);
        $formato->duplicateStyle($formato->getStyle('F'.$init),'F'.($init+1));

        $formato->getCell('G'.$init)->setValue($kar['SaldoCostoTotal']);
        $formato->duplicateStyle($formato->getStyle('G'.$init),'G'.($init+1));

        $init += 1;
    }

    $formato->getCell('F'.$init)->setValue('COSTO TOTAL GENERAL');    
    $formato->getCell('G'.$init)->setValue('=SUM(G12:G'.($init-1).')');

    $formato->getStyle('F'.$init)->getFont()->setBold( true );
    $formato->getStyle('G'.$init)->getFont()->setBold( true );
    
    $formato->getCell('E4')->setValue($anio);


    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('reporte/formato3.7.xlsx'); */

    echo '<script type="text/javascript">
        location.href = "/api/reporte/formato3.7.xlsx";
    </script>';
});

$app->post('/reporte/formato37', function (Request $request, Response $response, array $args) use ($app) {
    $row = $request->getParam('row');
    // var_dump($row);exit();
    $idProductos = $request->getParam('idProductos');
    $idAlmacen = $request->getParam('idAlmacen');
    $anio = $request->getParam('anio');


    $fechaDesde = $anio . '-01-01';
    $fechaHasta = $anio . '-12-31';
    // $fechaDesde = $request->getParam('fechaDesde');
    // $fechaHasta = $request->getParam('fechaHasta');


    if (!$row) {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/formato3.7.xlsx');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        aplicarIdentidadEmpresaExcel($spreadsheet, $this->db);
        $writer->save('reporte/formato3.7.xlsx');
    }

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('reporte/formato3.7.xlsx');
    $formato = $spreadsheet->getSheet(0);
    $init = 12;
    $init += $row;


    if ($idProductos) {
        $select = "SELECT * FROM Gen_Producto WHERE ControlaStock=1 AND IdProducto IN (" . implode(',', $idProductos) . ")";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $productosSaldo = [];

        $productosSaldo = array_map(function ($prod) use ($app, $idAlmacen, $fechaDesde, $fechaHasta) {
            $idProducto = $prod['IdProducto'];
            $res = $app->subRequest('GET', '/productos/kardex/' . $idProducto, 'idAlmacen=' . $idAlmacen . '&fechaHasta=' . $fechaHasta);
            $kardex = (string) $res->getBody();
            $kardex = json_decode($kardex, true);
            $kardex = kardexSaldoFecha($kardex, $fechaDesde);
            $final = array_merge($prod, end($kardex));
            return $final;
        }, $data);

        foreach ($productosSaldo as $kar) {
            /* $insert = "INSERT INTO tmp_formato37 (IdProducto, Producto, CodigoBarra, Saldo, SaldoCostoUnitario, SaldoCostoTotal)
                VALUES ('$kar[IdProducto]', '$kar[Producto]', '$kar[CodigoBarra]', '$kar[saldo]', '$kar[saldoCostoUnitario]', '$kar[saldoCostoTotal]')
                ON DUPLICATE KEY UPDATE Saldo='$kar[saldo]', SaldoCostoUnitario='$kar[saldoCostoUnitario]', SaldoCostoTotal='$kar[saldoCostoTotal]'";
            $stmt = $this->db->prepare($insert);
            $inserted = $stmt->execute();
            $idFormato = $this->db->lastInsertId();*/

            $formato->getCell('A' . $init)->setValue($kar['CodigoBarra']);
            $formato->duplicateStyle($formato->getStyle('A' . $init), 'A' . ($init + 1));

            $formato->getCell('B' . $init)->setValue('01');
            $formato->duplicateStyle($formato->getStyle('B' . $init), 'B' . ($init + 1));

            $formato->getCell('C' . $init)->setValue($kar['Producto']);
            $formato->duplicateStyle($formato->getStyle('C' . $init), 'C' . ($init + 1));

            $formato->getCell('D' . $init)->setValue('07');
            $formato->duplicateStyle($formato->getStyle('D' . $init), 'D' . ($init + 1));

            $formato->getCell('E' . $init)->setValue(@$kar['saldo']);
            $formato->duplicateStyle($formato->getStyle('E' . $init), 'E' . ($init + 1));

            $formato->getCell('F' . $init)->setValue(@$kar['saldoCostoUnitario']);
            $formato->duplicateStyle($formato->getStyle('F' . $init), 'F' . ($init + 1));

            $formato->getCell('G' . $init)->setValue(@$kar['saldoCostoTotal']);
            $formato->duplicateStyle($formato->getStyle('G' . $init), 'G' . ($init + 1));

            $init += 1;
        }

        $formato->getCell('F' . $init)->setValue('COSTO TOTAL GENERAL');
        $formato->getCell('G' . $init)->setValue('=SUM(G12:G' . ($init - 1) . ')');


        $config = array('SUMA_VALORIZADO' => $formato->getCell('G' . $init)->getCalculatedValue());
        $insert = "UPDATE GEN_EMPRESA SET CONFIG='" . serialize($config) . "' where IDEMPRESA=1";
        $stmt = $this->db->prepare($insert);
        $stmt->execute();


        // $formato->getStyle('F'.$init)->getFont()->setBold( true );
        // $formato->getStyle('G'.$init)->getFont()->setBold( true );

        $formato->getCell('E4')->setValue($anio);


        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        aplicarIdentidadEmpresaExcel($spreadsheet, $this->db);
        $writer->save('reporte/formato3.7.xlsx');
    }

    return;
    //return $response->withRedirect('/api/reporte/formato13.1.xlsx');
});


$app->get('/reporte/articulosmovimientos', function (Request $request, Response $response, array $args) use ($app) {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/masrotacion.xlsx');
    $formato = $spreadsheet->getSheet(0);
    // $formato->getColumnDimension('B')->setAutoSize(true);
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaDesde = $request->getParam('fechaDesde');
    $fechaHasta = $request->getParam('fechaHasta');
    $idMarca = $request->getParam('idMarca');

    $res = $app->subRequest(
        'GET',
        '/productos/masrotacion',
        'idAlmacen=' . $idAlmacen .
            '&fechaDesde=' . $fechaDesde .
            '&fechaHasta=' . $fechaHasta .
            '&idMarca=' . $idMarca
    );
    $productos = (string) $res->getBody();
    $productos = json_decode($productos, true);

    $init = 9;

    $formato->setTitle('Más movimientos');
    $formato->mergeCells('A1:J1');
    $formato->getCell('A1')->setValue('REPORTE DE ARTÍCULOS CON MÁS MOVIMIENTOS');
    $formato->getCell('A1')->getStyle()->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('FFFFFFFF');
    $formato->getCell('A1')->getStyle()->getAlignment()->setHorizontal('center')->setVertical('center');
    $formato->getStyle('A1:J1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF0A4F83');
    $formato->getRowDimension(1)->setRowHeight(28);

    $nombreMarca = 'TODAS LAS MARCAS';
    if ($idMarca && count($productos)) {
        $nombreMarca = $productos[0]['ProductoMarca'];
    }
    $formato->mergeCells('A3:J3');
    $formato->getCell('A3')->setValue('Periodo: ' . $fechaDesde . ' al ' . $fechaHasta . '  |  Marca: ' . $nombreMarca);
    $formato->getStyle('A3:J3')->getFont()->setBold(true)->getColor()->setARGB('FF315A75');
    $formato->getStyle('A3:J3')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFEAF5FB');
    $formato->getStyle('A3:J3')->getAlignment()->setHorizontal('center');

    $encabezados = array('Código', 'Descripción', 'Marca', 'U. Medida', 'Total Ventas', 'Cantidad Ventas', 'Monto Ventas', 'Total Compras', 'Cantidad Compras', 'Monto Compras');
    foreach ($encabezados as $columna => $titulo) {
        $celda = chr(65 + $columna) . '8';
        $formato->getCell($celda)->setValue($titulo);
    }
    $formato->getStyle('A8:J8')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
    $formato->getStyle('A8:D8')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF087FAE');
    $formato->getStyle('E8:G8')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF39A86B');
    $formato->getStyle('H8:J8')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF1597B1');
    $formato->getStyle('A8:J8')->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
    $formato->getRowDimension(8)->setRowHeight(24);

    foreach ($productos as $prod) {
        $formato->getCell('A' . $init)->setValue($prod['CodigoBarra']);
        $formato->duplicateStyle($formato->getStyle('A' . $init), 'A' . ($init + 1));

        $formato->getCell('B' . $init)->setValue($prod['Producto'] . ' - ' . $prod['ProductoCategoria'] . ' - ' . $prod['ProductoMarca']);
        $formato->duplicateStyle($formato->getStyle('B' . $init), 'B' . ($init + 1));

        $formato->getCell('C' . $init)->setValue($prod['ProductoMarca']);
        $formato->duplicateStyle($formato->getStyle('C' . $init), 'C' . ($init + 1));

        $formato->getCell('D' . $init)->setValue($prod['ProductoMedicion']);
        $formato->duplicateStyle($formato->getStyle('D' . $init), 'D' . ($init + 1));

        $formato->getCell('E' . $init)->setValue($prod['TotalVentas']);
        $formato->duplicateStyle($formato->getStyle('E' . $init), 'E' . ($init + 1));

        $formato->getCell('F' . $init)->setValue($prod['CantidadVentas']);
        $formato->duplicateStyle($formato->getStyle('F' . $init), 'F' . ($init + 1));

        $formato->getCell('G' . $init)->setValue($prod['MontoVentas']);
        $formato->duplicateStyle($formato->getStyle('G' . $init), 'G' . ($init + 1));

        $formato->getCell('H' . $init)->setValue($prod['TotalCompras']);
        $formato->duplicateStyle($formato->getStyle('H' . $init), 'H' . ($init + 1));

        $formato->getCell('I' . $init)->setValue($prod['CantidadCompras']);
        $formato->duplicateStyle($formato->getStyle('I' . $init), 'I' . ($init + 1));

        $formato->getCell('J' . $init)->setValue($prod['MontoCompras']);
        $formato->duplicateStyle($formato->getStyle('J' . $init), 'J' . ($init + 1));

        if ($init % 2 === 0) {
            $formato->getStyle('A' . $init . ':J' . $init)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFF2F8FC');
        }
        $formato->getStyle('E' . $init . ':G' . $init)->getFont()->setBold(true)->getColor()->setARGB('FF008A50');
        $formato->getStyle('H' . $init . ':J' . $init)->getFont()->setBold(true)->getColor()->setARGB('FF08738E');
        $init += 1;
    }

    $ultimaFilaDatos = $init - 1;
    $formato->getCell('D' . $init)->setValue('TOTALES');
    $formato->getCell('E' . $init)->setValue($ultimaFilaDatos >= 9 ? '=SUM(E9:E' . $ultimaFilaDatos . ')' : 0);
    foreach (array('F', 'G', 'H', 'I', 'J') as $columnaTotal) {
        $formato->getCell($columnaTotal . $init)->setValue($ultimaFilaDatos >= 9 ? '=SUM(' . $columnaTotal . '9:' . $columnaTotal . $ultimaFilaDatos . ')' : 0);
    }
    $formato->getStyle('D' . $init . ':J' . $init)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
    $formato->getStyle('D' . $init . ':J' . $init)->getFill()->setFillType('solid')->getStartColor()->setARGB('FF0A4F83');
    $formato->getStyle('A8:J' . $init)->getBorders()->getAllBorders()->setBorderStyle('thin')->getColor()->setARGB('FFD5E3ED');
    $formato->getStyle('E9:J' . $init)->getNumberFormat()->setFormatCode('#,##0.00');
    $formato->getColumnDimension('A')->setWidth(18);
    $formato->getColumnDimension('B')->setWidth(55);
    $formato->getColumnDimension('C')->setWidth(22);
    $formato->getColumnDimension('D')->setWidth(16);
    $formato->getColumnDimension('E')->setWidth(18);
    $formato->getColumnDimension('F')->setWidth(18);
    $formato->getColumnDimension('G')->setWidth(18);
    $formato->getColumnDimension('H')->setWidth(18);
    $formato->getColumnDimension('I')->setWidth(18);
    $formato->getColumnDimension('J')->setWidth(18);
    $formato->getStyle('B9:B' . $ultimaFilaDatos)->getAlignment()->setWrapText(true);
    $formato->freezePane('A9');
    $formato->setAutoFilter('A8:J' . max(8, $ultimaFilaDatos));
    $formato->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);
    $formato->getPageMargins()->setTop(0.4)->setRight(0.35)->setLeft(0.35)->setBottom(0.4);

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    aplicarIdentidadEmpresaExcel($spreadsheet, $this->db);
    $writer->save('reporte/masrotacion.xlsx');

    echo '<script type="text/javascript">
        location.href = "/api/reporte/masrotacion.xlsx";
    </script>';

    // return $response->withRedirect('/api/reporte/formato12.1.xlsx');
});





/**
 * Obtiene la cuenta que representa el efectivo de la caja principal.
 * Se resuelve por nombre porque el IdCuenta puede variar entre empresas.
 */
function obtenerIdCuentaCajaPrincipal($db)
{
    $sql = "SELECT IdCuenta
            FROM Cb_Cuenta
            WHERE (Anulado = 0 OR Anulado IS NULL)
              AND (
                    REPLACE(UPPER(TRIM(Cuenta)), ' ', '') = 'CAJAPRINCIPAL'
                    OR UPPER(TRIM(Cuenta)) = 'CAJA'
                    OR IdCuenta = 1
                  )
            ORDER BY
                CASE
                    WHEN REPLACE(UPPER(TRIM(Cuenta)), ' ', '') = 'CAJAPRINCIPAL' THEN 0
                    WHEN UPPER(TRIM(Cuenta)) = 'CAJA' THEN 1
                    WHEN IdCuenta = 1 THEN 2
                    ELSE 3
                END,
                IdCuenta
            LIMIT 1";
    $stmt = $db->query($sql);
    $idCuenta = $stmt ? $stmt->fetchColumn() : false;
    return $idCuenta === false ? 0 : (int) $idCuenta;
}

$app->post('/cierrecaja', function (Request $request, Response $response) {
    $idTurno        = $request->getParam('idTurno');
    $user           = $request->getParam('user');
    $actorUser      = $request->getParam('actorUser');
    $fechaCierre    = getNow();
    $ingresos       = $request->getParam('ingresos') ? $request->getParam('ingresos') : 0;
    $salidas        = $request->getParam('salidas') ? $request->getParam('salidas') : 0;
    $total          = $request->getParam('total') ? $request->getParam('total') : 0;
    $totalVentas    = $request->getParam('totalVentas') ? $request->getParam('totalVentas') : 0;
    $granTotal    = $request->getParam('granTotal') ? $request->getParam('granTotal') : 0;
    $descripcionCierre = trim((string) $request->getParam('descripcion'));
    $descripcionCierre = mb_substr($descripcionCierre, 0, 500, 'UTF-8');
    // El servidor determina la cuenta de efectivo; no se confia en un IdCuenta
    // enviado por el navegador para evitar cerrar accidentalmente Banco BCP.
    $idCuentaCaja = obtenerIdCuentaCajaPrincipal($this->db);
    if ($idCuentaCaja <= 0) {
        return $response->withJson(array('error' => 'No se encontro la cuenta CAJA PRINCIPAL.'), 422);
    }

    $usuarioReg = 'xx';
    if (isset($_SESSION['user'])) {
        $usuarioReg = $_SESSION['user'];
    }
    $usuarioReg = $user ? $user : $usuarioReg;

    // Un usuario solo puede cerrar su propia caja. El perfil administrador (1)
    // puede revisar y cerrar la caja seleccionada de cualquier vendedor.
    $actorUser = $actorUser ? $actorUser : $usuarioReg;
    if (strtolower($actorUser) !== strtolower($usuarioReg)) {
        $adminStmt = $this->db->prepare("SELECT IdUsuarioPerfil FROM Seg_Usuario WHERE LOWER(Usuario)=LOWER(?) AND (Anulado != 1 OR Anulado IS NULL) LIMIT 1");
        $adminStmt->execute([$actorUser]);
        $actor = $adminStmt->fetch();
        if (!$actor || intval($actor['IdUsuarioPerfil']) !== 1) {
            return $response->withJson(array('error' => 'Solo un administrador puede cerrar cajas de otros usuarios.'), 403);
        }
    }

    // Close the same pending movements shown in the report, including earlier
    // dates, manual vouchers and bank references. Never delete their history.
    $idCierreCaja = null;
    try {
        $this->db->beginTransaction();
        $stmt = $this->db->prepare("INSERT INTO Cb_CierreCaja
            (FechaCierre, IdTurno, UsuarioReg, Ingresos, Salidas, Ventas, Total, Descripcion)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fechaCierre, $idTurno, $usuarioReg, $ingresos, $salidas, $totalVentas, $total, $descripcionCierre]);
        $idCierreCaja = $this->db->lastInsertId();

        $stmt = $this->db->prepare("UPDATE Ve_DocVenta SET IdCierre=?
            WHERE IdCierre IS NULL AND LOWER(UsuarioReg)=LOWER(?)");
        $stmt->execute([$idCierreCaja, $usuarioReg]);

        $stmt = $this->db->prepare("UPDATE Cb_CajaBanco SET IdCierre=?
            WHERE IdCierre IS NULL AND LOWER(UsuarioReg)=LOWER(?)");
        $stmt->execute([$idCierreCaja, $usuarioReg]);
        $this->db->commit();
    } catch (Throwable $error) {
        if ($this->db->inTransaction()) $this->db->rollBack();
        // Older installations use MyISAM for the header: remove only the
        // uncommitted header created by this failed request, never an old close.
        if ($idCierreCaja !== null) {
            try {
                $cleanup = $this->db->prepare("DELETE FROM Cb_CierreCaja WHERE IdCierreCaja=? AND UsuarioReg=?");
                $cleanup->execute([$idCierreCaja, $usuarioReg]);
            } catch (Throwable $cleanupError) {
                error_log('IMCEDEN failed closing header requires review: '.$idCierreCaja);
            }
        }
        error_log('IMCEDEN cash closing failed: '.$error->getMessage());
        return $response->withJson(array('error' => 'No se pudo completar el cierre. Los movimientos siguen pendientes; vuelve a intentarlo.'), 500);
    }

    return $response->withJson(array(
        "idTurno" => $idTurno,
        "fechaCierre" => $fechaCierre,
        "idCierraCaja" => $idCierreCaja
    ));
});


$app->get('/cierrecaja', function (Request $request, Response $response, array $args) {
    $idCierre = $request->getParam('idCierre');
    $filter = $request->getParam('filter');

    $select = "SELECT Cb_CierreCaja.IdCierreCaja, Cb_CierreCaja.FechaCierre, Cb_CierreCaja.IdTurno,
    Cb_CierreCaja.UsuarioReg, Cb_CierreCaja.Ingresos, Cb_CierreCaja.Salidas, Cb_CierreCaja.Ventas, Cb_CierreCaja.Total, Cb_CierreCaja.Descripcion,
    IFNULL(Lo_Almacen.Almacen,(SELECT Almacen FROM Lo_Almacen WHERE IdAlmacen=1)) AS Almacen
    FROM Cb_CierreCaja
    LEFT JOIN Lo_Almacen ON Cb_CierreCaja.IdAlmacen=Lo_Almacen.IdAlmacen WHERE (Cb_CierreCaja.FechaCierre LIKE '%$filter%' OR Cb_CierreCaja.UsuarioReg LIKE '%$filter%' OR IFNULL(Cb_CierreCaja.Descripcion,'') LIKE '%$filter%')";

    if ($idCierre) {
        $select .= " AND IdCierreCaja=$idCierre";
    }


    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }


    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});
$app->get('/cierrecaja/count', function (Request $request, Response $response, array $args) {

    $select = "SELECT COUNT(*) AS total FROM Cb_CierreCaja";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});


$app->get('/cierrecaja/ventas', function (Request $request, Response $response, array $args) {
    $idCierre = $request->getParam('idCierre');

    $select = "SELECT
        Ve_DocVenta.idDocVenta, Ve_DocVentaPuntoVenta.PuntoVenta, Ve_DocVentaCliente.Cliente, Ve_DocVenta.FechaDoc, Ve_DocVenta.Anulado,
        Ve_DocVentaTipoDoc.TipoDoc, Ve_DocVenta.Serie, Ve_DocVenta.Numero, SUM((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - Ve_DocVentaDet.Descuento) as Total,
        Ve_DocVenta.EsCredito, Ve_DocVentaTipoDoc.CodSunat,
        MetodoDetalle.EfectivoDesc,
        MetodoDetalle.VisaDesc,
        MetodoDetalle.MastercardDesc,
        MetodoDetalle.TransferenciaDesc,
        MetodoDetalle.Efectivo,
        MetodoDetalle.Visa,
        MetodoDetalle.Mastercard,
        MetodoDetalle.Transferencia
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVentaTipoDoc.IdTipoDoc = Ve_DocVenta.IdTipoDoc
        LEFT JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente
        LEFT JOIN (
            SELECT Ve_DocVentaMetodoPagoDet.IdDocVenta, 
            IFNULL(IF(Ve_DocVentaMetodoPagoDet.IdMetodoPago = 1,Ve_DocVentaMetodoPagoDet.NroTarjeta,''),'') AS EfectivoDesc,
            IFNULL(IF(Ve_DocVentaMetodoPagoDet.IdMetodoPago = 2,Ve_DocVentaMetodoPagoDet.NroTarjeta,''),'') AS VisaDesc,
            IFNULL(IF(Ve_DocVentaMetodoPagoDet.IdMetodoPago = 3,Ve_DocVentaMetodoPagoDet.NroTarjeta,''),'') AS MastercardDesc,
            IFNULL(IF(Ve_DocVentaMetodoPagoDet.IdMetodoPago = 4,Ve_DocVentaMetodoPagoDet.NroTarjeta,''),'') AS TransferenciaDesc,
            IFNULL(SUM(IF(Ve_DocVentaMetodoPagoDet.IdMetodoPago = 1,Ve_DocVentaMetodoPagoDet.Importe,0)),0) AS Efectivo,
            IFNULL(SUM(IF(Ve_DocVentaMetodoPagoDet.IdMetodoPago = 2,Ve_DocVentaMetodoPagoDet.Importe,0)),0) AS Visa,
            IFNULL(SUM(IF(Ve_DocVentaMetodoPagoDet.IdMetodoPago = 3,Ve_DocVentaMetodoPagoDet.Importe,0)),0) AS Mastercard,
            IFNULL(SUM(IF(Ve_DocVentaMetodoPagoDet.IdMetodoPago = 4,Ve_DocVentaMetodoPagoDet.Importe,0)),0) AS Transferencia
            FROM Ve_DocVentaMetodoPagoDet
            GROUP BY Ve_DocVentaMetodoPagoDet.IdDocVenta
        ) AS MetodoDetalle ON  MetodoDetalle.IdDocVenta = Ve_DocVenta.idDocVenta
        INNER JOIN Ve_DocVentaPuntoVenta ON Ve_DocVenta.IdDocVentaPuntoVenta = Ve_DocVentaPuntoVenta.IdDocVentaPuntoVenta";

    if ($idCierre) {
        $select .= " WHERE Ve_DocVenta.IdCierre = $idCierre";
    } else {
        $select .= " WHERE Ve_DocVenta.IdCierre IS NULL";
    }

    if ($request->getParam('usuario')) {
        $usuario = strtolower($request->getParam('usuario'));
        $select .= " AND LOWER(Ve_DocVenta.UsuarioReg)= '" . $usuario  . "'";
    }

    if ($request->getParam('IdAlmacen')) {
        $select .= " AND Ve_DocVenta.IdAlmacen = '" . $request->getParam('IdAlmacen') . "'";
    }

    $select .= " GROUP BY Ve_DocVenta.idDocVenta
        ORDER BY Ve_DocVentaPuntoVenta.IdDocVentaPuntoVenta ASC, Ve_DocVenta.FechaDoc ASC;";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});
$app->get('/cierrecaja/ingresos', function (Request $request, Response $response, array $args) {
    $idCierre = (int) $request->getParam('idCierre');
    $soloBancos = (int) $request->getParam('soloBancos') === 1;
    $idCuentaCajaPrincipal = obtenerIdCuentaCajaPrincipal($this->db);
    $idCuenta = (int) $request->getParam('idCuenta');
    if ($idCuenta <= 0) {
        $idCuenta = $idCuentaCajaPrincipal;
    }

    if ($idCuentaCajaPrincipal <= 0 || (!$soloBancos && $idCuenta <= 0)) {
        return $response->withJson(array('error' => 'No se encontro la cuenta CAJA PRINCIPAL.'), 422);
    }

    $select = "SELECT Cb_CajaBanco.*, Cb_TipoCajaBanco.Tipo,  Cb_Cuenta.Cuenta
        FROM Cb_CajaBanco
        INNER JOIN Cb_TipoCajaBanco ON Cb_CajaBanco.IdTipoCajaBanco = Cb_TipoCajaBanco.IdTipoCajaBanco
        INNER JOIN Cb_Cuenta ON Cb_CajaBanco.IdCuenta = Cb_Cuenta.IdCuenta";

    if ($idCierre) {
        $select .= " WHERE Cb_CajaBanco.IdCierre=$idCierre";
    } else {
        $select .= " WHERE Cb_CajaBanco.IdCierre IS NULL";
    }

    if ($soloBancos) {
        $select .= " AND Cb_Cuenta.IdCuenta <> " . $idCuentaCajaPrincipal;

    } else {
        $select .= " AND Cb_Cuenta.IdCuenta = " . $idCuenta;
    }


    if ($request->getParam('usuario')) {
        $usuario = strtolower($request->getParam('usuario'));
        $select .= " AND LOWER(Cb_CajaBanco.UsuarioReg)= '" . $usuario . "'";
    }

    if ($request->getParam('IdAlmacen')) {
        $select .= " AND (Cb_CajaBanco.IdAlmacen = '" . $request->getParam('IdAlmacen') . "' OR Cb_CajaBanco.IdAlmacen IS NULL)";
    }

    $select .= " AND Cb_TipoCajaBanco.Tipo = 0
        ORDER BY FechaDoc ASC;";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});
$app->get('/cierrecaja/salidas', function (Request $request, Response $response, array $args) use ($app) {
    $idCierre = (int) $request->getParam('idCierre');
    $soloBancos = (int) $request->getParam('soloBancos') === 1;
    $idCuentaCajaPrincipal = obtenerIdCuentaCajaPrincipal($this->db);
    $idCuenta = (int) $request->getParam('idCuenta');
    if ($idCuenta <= 0) {
        $idCuenta = $idCuentaCajaPrincipal;
    }

    if ($idCuentaCajaPrincipal <= 0 || (!$soloBancos && $idCuenta <= 0)) {
        return $response->withJson(array('error' => 'No se encontro la cuenta CAJA PRINCIPAL.'), 422);
    }

    $select = "SELECT Cb_CajaBanco.IdCajaBanco, Cb_TipoCajaBanco.Tipo, Cb_CajaBanco.IdCuenta, Cb_Cuenta.Cuenta,
            Cb_CajaBanco.FechaDoc, Cb_CajaBanco.FechaReg, Cb_CajaBanco.UsuarioReg,
            Cb_CajaBanco.Concepto, Cb_CajaBanco.Importe, Cb_CajaBanco.Anulado
        FROM Cb_CajaBanco
        INNER JOIN Cb_TipoCajaBanco ON Cb_CajaBanco.IdTipoCajaBanco = Cb_TipoCajaBanco.IdTipoCajaBanco
        INNER JOIN Cb_Cuenta ON Cb_CajaBanco.IdCuenta = Cb_Cuenta.IdCuenta";

    if ($idCierre) {
        $select .= " WHERE Cb_CajaBanco.IdCierre=$idCierre";
    } else {
        $select .= " WHERE Cb_CajaBanco.IdCierre IS NULL";
    }

    if ($soloBancos) {
        $select .= " AND Cb_Cuenta.IdCuenta <> " . $idCuentaCajaPrincipal;

    } else {
        $select .= " AND Cb_Cuenta.IdCuenta = " . $idCuenta;
    }


    if ($request->getParam('usuario')) {
        $usuario = strtolower($request->getParam('usuario'));
        $select .= " AND LOWER(Cb_CajaBanco.UsuarioReg)= '" . $usuario . "'";
    }

    if ($request->getParam('IdAlmacen')) {
        $select .= " AND (Cb_CajaBanco.IdAlmacen = '" . $request->getParam('IdAlmacen') . "' OR Cb_CajaBanco.IdAlmacen IS NULL)";
    }

    $select .= " AND Cb_TipoCajaBanco.Tipo = 1
        ORDER BY FechaDoc ASC;";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/config/stock', function (Request $request, Response $response) {
    $select = "SELECT * FROM GEN_EMPRESA WHERE IDEMPRESA=1";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();
    if (empty($data) || $data['VENTASINSTOCK'] == null || $data['VENTASINSTOCK'] == 0) {
        return $response->withJson(0);
    }
    return $response->withJson($data['VENTASINSTOCK']);
});

$app->get('/reporte/movimiento/proveedores', function (Request $request, Response $response, array $args) {
    try {
        $id_proveedor = $request->getParam('id_proveedor');
        $fechaDesde = $request->getParam('fechaDesde')
            ? $request->getParam('fechaDesde') . ' 00:00:00'
            : null;
        $fechaHasta = $request->getParam('fechaHasta')
            ? $request->getParam('fechaHasta') . ' 23:59:59'
            : null;




        if (!$fechaDesde || !$fechaHasta || !$id_proveedor) {
            return $response->withJson([]);
        }
        $select = "SELECT 
            Lo_Movimiento.FechaReg,
            Lo_MovimientoDetalle.Precio,
            Lo_MovimientoDetalle.Cantidad,
             Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad Monto,
            Gen_Producto.Producto
            FROM 
                Lo_Movimiento 
            JOIN Lo_MovimientoDetalle ON Lo_Movimiento.Hash = Lo_MovimientoDetalle.hashMovimiento
            JOIN Gen_Producto ON Gen_Producto.IdProducto = Lo_MovimientoDetalle.IdProducto
            WHERE Lo_Movimiento.IdProveedor = $id_proveedor 
                AND Lo_Movimiento.FechaReg BETWEEN '$fechaDesde' AND '$fechaHasta'
            ORDER BY Lo_Movimiento.FechaReg DESC";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});
$app->get('/reporte/movimiento/proveedores/excel', function (Request $request, Response $response, array $args) use ($app) {
    $id_proveedor = $request->getParam('id_proveedor');
    $fechaDesde = $request->getParam('fechaDesde');

    $fechaHasta = $request->getParam('fechaHasta');



    $res = $app->subRequest(
        'GET',
        'reporte/movimiento/proveedores',
        'fechaDesde=' . $fechaDesde .
            '&fechaHasta=' . $fechaHasta .
            '&id_proveedor=' . $id_proveedor
    );

    $reporte = (string) $res->getBody();
    $reporte = json_decode($reporte, true);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Ventas por proveedor');
    $sheet->mergeCells('A1:E1');
    $sheet->setCellValue('A1', 'REPORTE DE VENTAS POR PROVEEDOR');
    $sheet->getStyle('A1:E1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF0A4F83');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('FFFFFFFF');
    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('center');
    $sheet->getRowDimension(1)->setRowHeight(30);
    $sheet->mergeCells('A2:E2');
    $sheet->setCellValue('A2', 'Periodo: ' . $fechaDesde . ' al ' . $fechaHasta . '  |  Generado: ' . date('d/m/Y H:i'));
    $sheet->getStyle('A2:E2')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE9F4FA');
    $sheet->getStyle('A2')->getFont()->setBold(true)->getColor()->setARGB('FF315A75');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
    foreach (array('FECHA DEL MOVIMIENTO','PRODUCTO','PRECIO UNITARIO','CANTIDAD','IMPORTE TOTAL') as $iRep => $headerRep) $sheet->setCellValue(chr(65 + $iRep) . '4', $headerRep);
    $sheet->getStyle('A4:E4')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF087FAE');
    $sheet->getStyle('A4:E4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
    $sheet->getStyle('A4:E4')->getAlignment()->setHorizontal('center')->setVertical('center');
    $sheet->getRowDimension(4)->setRowHeight(25);
    $cont = 5;
    $total = 0;

    foreach ($reporte as $rep) {
        $sheet->setCellValue('A' . $cont, $rep['FechaReg']);
        $sheet->setCellValue('B' . $cont, $rep['Producto']);
        $sheet->setCellValue('C' . $cont, (float)$rep['Precio']);
        $sheet->setCellValue('D' . $cont, (float)$rep['Cantidad']);
        $sheet->setCellValue('E' . $cont, (float)$rep['Monto']);
        if ($cont % 2 === 0) $sheet->getStyle('A' . $cont . ':E' . $cont)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFF2F8FC');
        $cont += 1;
        $total += $rep['Monto'];
    }
    $sheet->setCellValue('D' . $cont, 'TOTAL GENERAL');
    $sheet->setCellValue('E' . $cont, $total);
    $sheet->getStyle('D' . $cont . ':E' . $cont)->getFill()->setFillType('solid')->getStartColor()->setARGB('FF0A4F83');
    $sheet->getStyle('D' . $cont . ':E' . $cont)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
    $sheet->getStyle('A4:E' . $cont)->getBorders()->getAllBorders()->setBorderStyle('thin')->getColor()->setARGB('FFD5E3ED');
    $sheet->getStyle('C5:C' . $cont)->getNumberFormat()->setFormatCode('"S/ "#,##0.00');
    $sheet->getStyle('D5:D' . $cont)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('E5:E' . $cont)->getNumberFormat()->setFormatCode('"S/ "#,##0.00');
    foreach (array('A'=>23,'B'=>48,'C'=>19,'D'=>15,'E'=>20) as $colRep => $widthRep) $sheet->getColumnDimension($colRep)->setWidth($widthRep);
    $sheet->getStyle('B5:B' . max(5, $cont - 1))->getAlignment()->setWrapText(true);
    $sheet->freezePane('A5');
    $sheet->setAutoFilter('A4:E' . max(4, $cont - 1));
    $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);


    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $fileName = 'ventas-proveedor-' . getNow('Y-m-d-H-i-s') . '.xlsx';
    aplicarIdentidadEmpresaExcel($spreadsheet, $this->db);
    $writer->save('reporte/' . $fileName);
    return $response->withJson('/api/reporte/' . $fileName);
});

$app->get('/reporte/ranking/clientes', function (Request $request, Response $response, array $args) {
    try {
        $IdCliente = $request->getParam('IdCliente');
        $fechaInicio = $request->getParam('fechaInicio')
            ? $request->getParam('fechaInicio') . ' 00:00:00'
            : null;
        $fechaFin = $request->getParam('fechaFin')
            ? $request->getParam('fechaFin') . ' 23:59:59'
            : null;

        if (!$fechaInicio || !$fechaFin || !$IdCliente) {
            return $response->withJson([]);
        };
        $select = "SELECT 
                    Ve_DocVenta.FechaReg,
                    Gen_Producto.Producto,
                    Ve_DocVentaDet.Cantidad,
                    Ve_DocVentaDet.Precio,
                    Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio Monto
                    FROM Ve_DocVenta 
                    JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta = Ve_DocVentaDet.IdDocVenta
                    JOIN Gen_Producto ON Gen_Producto.IdProducto = Ve_DocVentaDet.IdProducto
                    WHERE Ve_DocVenta.IdCliente = $IdCliente 
                    AND Ve_DocVenta.FechaReg BETWEEN '$fechaInicio' AND '$fechaFin'
                    ORDER BY Ve_DocVenta.FechaReg DESC
                ";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});

$app->get('/reporte/ranking/clientes/excel', function (Request $request, Response $response, array $args) use ($app) {
    $IdCliente = $request->getParam('IdCliente');
    $fechaInicio = $request->getParam('fechaInicio');

    $fechaFin = $request->getParam('fechaFin');



    $res = $app->subRequest(
        'GET',
        'reporte/ranking/clientes',
        'IdCliente=' . $IdCliente .
            '&fechaInicio=' . $fechaInicio .
            '&fechaFin=' . $fechaFin
    );

    $reporte = (string) $res->getBody();
    $reporte = json_decode($reporte, true);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Compras por cliente');
    $sheet->mergeCells('A1:E1');
    $sheet->setCellValue('A1', 'REPORTE DE COMPRAS POR CLIENTE');
    $sheet->getStyle('A1:E1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF0A4F83');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('FFFFFFFF');
    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('center');
    $sheet->getRowDimension(1)->setRowHeight(30);
    $sheet->mergeCells('A2:E2');
    $sheet->setCellValue('A2', 'Periodo: ' . $fechaInicio . ' al ' . $fechaFin . '  |  Generado: ' . date('d/m/Y H:i'));
    $sheet->getStyle('A2:E2')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE9F4FA');
    $sheet->getStyle('A2')->getFont()->setBold(true)->getColor()->setARGB('FF315A75');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
    foreach (array('FECHA','PRODUCTO','PRECIO UNITARIO','CANTIDAD','IMPORTE TOTAL') as $iCli => $headerCli) $sheet->setCellValue(chr(65 + $iCli) . '4', $headerCli);
    $sheet->getStyle('A4:E4')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF087FAE');
    $sheet->getStyle('A4:E4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
    $sheet->getStyle('A4:E4')->getAlignment()->setHorizontal('center');
    $cont = 5;
    $total = 0;

    foreach ($reporte as $rep) {
        $sheet->setCellValue('A' . $cont, $rep['FechaReg']);
        $sheet->setCellValue('B' . $cont, $rep['Producto']);
        $sheet->setCellValue('C' . $cont, (float)$rep['Precio']);
        $sheet->setCellValue('D' . $cont, (float)$rep['Cantidad']);
        $sheet->setCellValue('E' . $cont, (float)$rep['Monto']);
        if ($cont % 2 === 0) $sheet->getStyle('A' . $cont . ':E' . $cont)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFF2F8FC');
        $cont += 1;
        $total += $rep['Monto'];
    }
    $sheet->setCellValue('D' . $cont, 'TOTAL GENERAL');
    $sheet->setCellValue('E' . $cont, $total);
    $sheet->getStyle('D' . $cont . ':E' . $cont)->getFill()->setFillType('solid')->getStartColor()->setARGB('FF0A4F83');
    $sheet->getStyle('D' . $cont . ':E' . $cont)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
    $sheet->getStyle('A4:E' . $cont)->getBorders()->getAllBorders()->setBorderStyle('thin')->getColor()->setARGB('FFD5E3ED');
    $sheet->getStyle('C5:C' . $cont)->getNumberFormat()->setFormatCode('"S/ "#,##0.00');
    $sheet->getStyle('D5:D' . $cont)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('E5:E' . $cont)->getNumberFormat()->setFormatCode('"S/ "#,##0.00');
    foreach (array('A'=>23,'B'=>48,'C'=>19,'D'=>15,'E'=>20) as $colCli => $widthCli) $sheet->getColumnDimension($colCli)->setWidth($widthCli);
    $sheet->getStyle('B5:B' . max(5, $cont - 1))->getAlignment()->setWrapText(true);
    $sheet->freezePane('A5');
    $sheet->setAutoFilter('A4:E' . max(4, $cont - 1));
    $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);


    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $fileName = 'compras-cliente-' . getNow('Y-m-d-H-i-s') . '.xlsx';
    aplicarIdentidadEmpresaExcel($spreadsheet, $this->db);
    $writer->save('reporte/' . $fileName);
    return $response->withJson('/api/reporte/' . $fileName);
});

$app->get('/impresora', function (Request $request, Response $response) {
    try {
        $select = "SELECT * FROM Gen_Impresora";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});
$app->post('/impresora/nuevo', function (Request $request, Response $response) {
    $NombreImpresora = $request->getParam('NombreImpresora');
    $Ip = $request->getParam('Ip');
    $Puerto = $request->getParam('Puerto');
    $LongitudLinea = $request->getParam('LongitudLinea');
    try {
        $insert = $this->db->insert(array(
            'NombreImpresora',
            'Ip',
            'Puerto',
            'LongitudLinea'
        ))
            ->into('Gen_Impresora')
            ->values(array(
                $NombreImpresora,
                $Ip,
                $Puerto,
                $LongitudLinea
            ));

        $insert->execute();
        $insertId = $this->db->lastInsertId();
        return $response->withJson(array("IdImpresora" => $insertId));
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});
$app->put('/impresora/editar', function (Request $request, Response $response) {
    $NombreImpresora = $request->getParam('NombreImpresora');
    $Ip = $request->getParam('Ip');
    $Puerto = $request->getParam('Puerto');
    $LongitudLinea = $request->getParam('LongitudLinea');
    $IdImpresora = $request->getParam('IdImpresora');
    try {
        $update = $this->db->update(array(
            "NombreImpresora" => $NombreImpresora,
            'Ip' => $Ip,
            'Puerto' => $Puerto,
            'LongitudLinea' => $LongitudLinea,

        ))
            ->table('Gen_Impresora')
            ->where('IdImpresora', '=', $IdImpresora);

        $affectedRows = $update->execute();


        return $response->withJson(array(
            "updated" => $updated,
            "IdImpresora" => $IdImpresora
        ));
        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});

$app->get('/imprimirpdf_wsp/{id}', function (Request $request, Response $response, array $args) use ($app) {
    try {

        $id = $args['id'];
        $select = "SELECT Ve_DocVenta.idDocVenta, Ve_DocVenta.EsCredito, Ve_DocVenta.FechaCredito, Ve_DocVenta.FechaDoc, Ve_DocVentaTipoDoc.TipoDoc, Ve_DocVentaTipoDoc.TieneIgv,
        Ve_DocVenta.Anulado, Ve_DocVenta.Serie, Ve_DocVenta.Numero, Ve_DocVentaCliente.Cliente, Ve_DocVenta.UsuarioReg,
        Ve_DocVentaTipoDoc.CodSunat, Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.Direccion,
        Ve_DocVentaTipoDoc.CodigoIgv, Ve_DocVenta.Estado, Ve_DocVenta.Hash_cpe, Ve_DocVenta.Hash_cdr, Ve_DocVenta.Msj_sunat,
        Ve_DocVenta.FechaCredito, Lo_Almacen.Almacen, Lo_Almacen.Direccion DireccionAlmacen, Lo_Almacen.EsPrincipal EsAlmacenPrincipal,
        Ve_DocVentaCliente.NombreComercial,
        Ve_DocVenta.CodSunatModifica,
        Ve_DocVenta.NroComprobanteModifica,
        Ve_DocVenta.NotaIdMotivo,
        Ve_DocVenta.NotaDescMotivo,
        Ve_DocVenta.Observacion, Ve_DocVenta.Placa,
        IFNULL((SELECT SUM(ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2)) FROM Ve_DocVentaDet WHERE Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta), 0 ) AS Total
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        LEFT JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente 
        INNER JOIN Lo_Almacen ON Ve_DocVenta.IdAlmacen = Lo_Almacen.IdAlmacen
        WHERE idDocVenta = $id ";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $venta = $stmt->fetch();
        $select = "SELECT Ve_DocVentaDet.*, Gen_Producto.Producto,
        ROUND(Ve_DocVentaDet.Descuento, 2) AS Descuento,
        ROUND((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - Ve_DocVentaDet.Descuento, 2) AS Subtotal,
        ROUND(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio, 2) AS Total,
        Gen_Producto.CodigoBarra, Gen_ProductoMedicion.Codigo AS CodigoMedicion,
        Gen_ProductoMedicion.ProductoMedicion, Gen_ProductoMarca.ProductoMarca
        FROM Ve_DocVentaDet
        INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
        INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
        INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
        WHERE IdDocVenta = $id";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $productos = $stmt->fetchAll();
        $venta['productos'] = $productos;
        // Generar el PDF como archivo
        $res = $app->subRequest('POST', '/generarpdfelectronico', http_build_query(['docVenta' => $venta]));
        // Buscar el pdf y mostrarlo
        // $response = $this->response->withHeader('Content-type', 'application/pdf');
        $urlArchivo =   '/sunat/api_cpe/PRODUCCION/' . NRO_DOCUMENTO_EMPRESA . '/' . NRO_DOCUMENTO_EMPRESA . '-' . $venta['CodSunat'] . '-' . $venta['Serie'] . '-' . $venta['Numero'] . '.pdf';
        $nroComprobante = $venta['Numero'];
        return $response->withJson([
            'url' => $urlArchivo,
            'documento' => $nroComprobante,
            'document' => '/sunat/api_cpe/PRODUCCION/' . NRO_DOCUMENTO_EMPRESA . '/' . NRO_DOCUMENTO_EMPRESA . '-' . $venta['CodSunat'] . '-' . $venta['Serie'] . '-' . $venta['Numero'] . '.pdf'
        ]);
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});


$app->post('/login', function (Request $request, Response $response, array $args) {

    $user = $request->getParam('user');
    $pass = $request->getParam('password');

    $select = "SELECT * FROM Seg_Usuario WHERE Usuario='$user' AND Password=SHA2('$pass',256)";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});
$app->post('/cajaybanco/compra-chatarra', function (Request $request, Response $response, array $args) {
    $idAlmacen = (int) $request->getParam('IdAlmacen');
    $idCuenta = (int) $request->getParam('IdCuenta');
    $idProducto = (int) $request->getParam('IdProducto');
    $cantidad = is_numeric($request->getParam('Cantidad')) ? (float) $request->getParam('Cantidad') : 0;
    $costo = is_numeric($request->getParam('Costo')) ? (float) $request->getParam('Costo') : 0;
    $descripcion = trim((string) $request->getParam('Descripcion'));
    $usuario = trim((string) $request->getParam('Usuario'));
    if ($usuario === '' && isset($_SESSION['User'])) {
        $usuario = (string) $_SESSION['User'];
    }
    if ($usuario === '') {
        $usuario = 'sistema';
    }

    if ($idAlmacen <= 0 || $idCuenta <= 0 || $idProducto <= 0) {
        return $response->withJson(array('error' => 'Seleccione almacén, cuenta y producto.'), 422);
    }
    if ($cantidad <= 0 || $costo <= 0) {
        return $response->withJson(array('error' => 'La cantidad y el costo deben ser mayores a cero.'), 422);
    }
    if ($descripcion === '') {
        return $response->withJson(array('error' => 'Ingrese la descripción o referencia de la compra.'), 422);
    }

    $totalCosto = round($cantidad * $costo, 2);
    if ($totalCosto <= 0) {
        return $response->withJson(array('error' => 'El total de la compra debe ser mayor a cero.'), 422);
    }

    try {
        $this->db->beginTransaction();

        $productoStmt = $this->db->prepare("SELECT IdProducto, CodigoBarra, Producto, EsChatarra
            FROM Gen_Producto
            WHERE IdProducto = :idProducto AND Anulado = 0
            FOR UPDATE");
        $productoStmt->execute(array('idProducto' => $idProducto));
        $producto = $productoStmt->fetch(PDO::FETCH_ASSOC);
        if (!$producto || (int) $producto['EsChatarra'] !== 1) {
            throw new InvalidArgumentException('El producto no existe o no está marcado como chatarra.');
        }

        $almacenStmt = $this->db->prepare("SELECT IdAlmacen, Almacen
            FROM Lo_Almacen WHERE IdAlmacen = :idAlmacen AND Anulado = 0 FOR UPDATE");
        $almacenStmt->execute(array('idAlmacen' => $idAlmacen));
        $almacen = $almacenStmt->fetch(PDO::FETCH_ASSOC);
        if (!$almacen) {
            throw new InvalidArgumentException('El almacén seleccionado no está disponible.');
        }

        $cuentaStmt = $this->db->prepare("SELECT IdCuenta, Cuenta
            FROM Cb_Cuenta WHERE IdCuenta = :idCuenta AND Anulado = 0 FOR UPDATE");
        $cuentaStmt->execute(array('idCuenta' => $idCuenta));
        $cuenta = $cuentaStmt->fetch(PDO::FETCH_ASSOC);
        if (!$cuenta) {
            throw new InvalidArgumentException('La cuenta de salida seleccionada no está disponible.');
        }

        $numeroStmt = $this->db->prepare("SELECT COALESCE(MAX(Numero), 0) + 1
            FROM Lo_Movimiento
            WHERE IdMovimientoTipo = 4 AND Serie = 'CHATARRA'");
        $numeroStmt->execute();
        $numero = (int) $numeroStmt->fetchColumn();
        $hash = (string) time() . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $fecha = getNow();
        $periodoTributario = (int) date('Ym');
        $observacion = 'Compra de chatarra - ' . $descripcion;

        $movimientoStmt = $this->db->prepare("INSERT INTO Lo_Movimiento
            (IdMovimientoTipo, IdProveedor, Serie, Numero, MovimientoFecha,
             IdAlmacenOrigen, IdAlmacenDestino, Observacion, Anulado, FechaReg,
             UsuarioReg, Hash, FechaStock, Percepcion, EsCredito,
             FechaPeriodoTributario, TipoCambio, Moneda)
            VALUES
            (4, 0, 'CHATARRA', :numero, :fecha, -1, :idAlmacen, :observacion,
             0, :fechaReg, :usuario, :hash, :fechaStock, 0, 0,
             :periodoTributario, 1, 'PEN')");
        $movimientoStmt->execute(array(
            'numero' => $numero,
            'fecha' => $fecha,
            'idAlmacen' => $idAlmacen,
            'observacion' => $observacion,
            'fechaReg' => $fecha,
            'usuario' => $usuario,
            'hash' => $hash,
            'fechaStock' => $fecha,
            'periodoTributario' => $periodoTributario
        ));

        $detalleStmt = $this->db->prepare("INSERT INTO Lo_MovimientoDetalle
            (hashMovimiento, IdProducto, Cantidad, TieneIgv, Precio, ISC, FLETE, IdLote, Descripcion)
            VALUES (:hash, :idProducto, :cantidad, 0, :precio, 0, 0, 0, :descripcion)");
        $detalleStmt->execute(array(
            'hash' => $hash,
            'idProducto' => $idProducto,
            'cantidad' => $cantidad,
            'precio' => $costo,
            'descripcion' => $descripcion
        ));

        $totalesStmt = $this->db->prepare("SELECT TotalCompras, TotalVentas
            FROM Gen_Producto_Totales
            WHERE IdAlmacen = :idAlmacen AND IdProducto = :idProducto
            FOR UPDATE");
        $totalesStmt->execute(array('idAlmacen' => $idAlmacen, 'idProducto' => $idProducto));
        $totales = $totalesStmt->fetch(PDO::FETCH_ASSOC);
        if ($totales) {
            $actualizarStockStmt = $this->db->prepare("UPDATE Gen_Producto_Totales
                SET TotalCompras = COALESCE(TotalCompras, 0) + :cantidad
                WHERE IdAlmacen = :idAlmacen AND IdProducto = :idProducto");
            $actualizarStockStmt->execute(array(
                'cantidad' => $cantidad,
                'idAlmacen' => $idAlmacen,
                'idProducto' => $idProducto
            ));
        } else {
            $insertarStockStmt = $this->db->prepare("INSERT INTO Gen_Producto_Totales
                (IdAlmacen, IdProducto, TotalCompras, TotalVentas)
                VALUES (:idAlmacen, :idProducto, :cantidad, 0)");
            $insertarStockStmt->execute(array(
                'idAlmacen' => $idAlmacen,
                'idProducto' => $idProducto,
                'cantidad' => $cantidad
            ));
        }

        $productoTotalStmt = $this->db->prepare("UPDATE Gen_Producto
            SET TotalCompras = COALESCE(TotalCompras, 0) + :cantidad,
                PrecioCosto = :costo,
                FechaMod = :fechaMod,
                UsuarioMod = :usuario
            WHERE IdProducto = :idProducto");
        $productoTotalStmt->execute(array(
            'cantidad' => $cantidad,
            'costo' => $costo,
            'fechaMod' => $fecha,
            'usuario' => $usuario,
            'idProducto' => $idProducto
        ));

        $codigoProducto = trim((string) $producto['CodigoBarra']);
        $concepto = 'Compra de chatarra - ' . ($codigoProducto !== '' ? $codigoProducto . ' ' : '')
            . trim((string) $producto['Producto']) . ' - ' . $descripcion;
        $concepto = function_exists('mb_substr') ? mb_substr($concepto, 0, 255, 'UTF-8') : substr($concepto, 0, 255);
        $cajaStmt = $this->db->prepare("INSERT INTO Cb_CajaBanco
            (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, Anulado,
             IdProveedor, IdCliente, EsDelVendedor, UsuarioReg, FechaReg, IdAlmacen)
            VALUES
            (3, :idCuenta, :fechaDoc, :concepto, :importe, 0,
             0, 0, 1, :usuario, :fechaReg, :idAlmacen)");
        $cajaStmt->execute(array(
            'idCuenta' => $idCuenta,
            'fechaDoc' => $fecha,
            'concepto' => $concepto,
            'importe' => $totalCosto,
            'usuario' => $usuario,
            'fechaReg' => $fecha,
            'idAlmacen' => $idAlmacen
        ));
        $idCajaBanco = (int) $this->db->lastInsertId();

        $stockStmt = $this->db->prepare("SELECT
                COALESCE(TotalCompras, 0) - COALESCE(TotalVentas, 0) AS StockActual
            FROM Gen_Producto_Totales
            WHERE IdAlmacen = :idAlmacen AND IdProducto = :idProducto");
        $stockStmt->execute(array('idAlmacen' => $idAlmacen, 'idProducto' => $idProducto));
        $stockActual = (float) $stockStmt->fetchColumn();

        $this->db->commit();
        return $response->withJson(array(
            'ok' => true,
            'IdCajaBanco' => $idCajaBanco,
            'HashMovimiento' => $hash,
            'ComprobanteMovimiento' => 'CHATARRA-' . $numero,
            'IdAlmacen' => $idAlmacen,
            'Almacen' => $almacen['Almacen'],
            'IdProducto' => $idProducto,
            'Producto' => $producto['Producto'],
            'Cantidad' => $cantidad,
            'Costo' => $costo,
            'TotalCosto' => $totalCosto,
            'StockActual' => $stockActual,
            'Cuenta' => $cuenta['Cuenta']
        ));
    } catch (InvalidArgumentException $error) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        return $response->withJson(array('error' => $error->getMessage()), 422);
    } catch (Throwable $error) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        error_log('Compra de chatarra: ' . $error->getMessage());
        return $response->withJson(array(
            'error' => 'No se pudo registrar la compra. Caja y stock no fueron modificados.'
        ), 500);
    }
});
$app->post('/cajaybanco/gasto', function (Request $request, Response $response, array $args) {
    try {
        $idPreOrden = $request->getParam('IdPreOrden', null);
        $idCuenta = $request->getParam('IdCuenta');
        $idHabitacion = $request->getParam('IdHabitacion');
        $gasto = $request->getParam('Gasto');
        $producto = $request->getParam('producto');
        $concepto = $request->getParam('concepto');
        $usuario = $request->getParam('Usuario');
        $vendedor = 'xx';
        if (isset($_SESSION['User'])) {
            $vendedor = $_SESSION['User'];
        }
        $usuarioReg = isset($usuario) ? $request->getParam('Usuario') : $vendedor;
        // if ($idPreOrden) {
        $insert = "INSERT INTO 
            Cb_CajaBanco (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, Anulado,  EsDelVendedor,  UsuarioReg) 
            VALUES (3, $idCuenta, '" . getNow() . "', '$concepto', $gasto, 0,  1, '$usuarioReg')";

        $stmt = $this->db->prepare($insert);
        $inserted = $stmt->execute();
        $idCajaBanco = $this->db->lastInsertId();
        if ($idCajaBanco) {
            $select = "SELECT * FROM Cb_CajaBanco where IdCajaBanco=$idCajaBanco";
            $stmt = $this->db->query($select);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $response->withJson($data);
        }
        // }else{
        // }
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});
$app->post('/cajaybanco/ingreso', function (Request $request, Response $response, array $args) {
    try {
        $idPreOrden = $request->getParam('IdPreOrden', null);
        $idCuenta = $request->getParam('IdCuenta');
        $idHabitacion = $request->getParam('IdHabitacion');
        $ingreso = $request->getParam('Ingreso');
        $producto = $request->getParam('producto');
        $concepto = $request->getParam('concepto');
        $usuario = $request->getParam('Usuario');
        $vendedor = 'xx';
        if (isset($_SESSION['User'])) {
            $vendedor = $_SESSION['User'];
        }
        $usuarioReg = isset($usuario) ? $request->getParam('Usuario') : $vendedor;
        // if ($idPreOrden) {
        $insert = "INSERT INTO 
        Cb_CajaBanco (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, Anulado,  EsDelVendedor,  UsuarioReg) 
        VALUES (4, $idCuenta, '" . getNow() . "', '$concepto', $ingreso, 0,  1, '$usuarioReg')";
        $stmt = $this->db->prepare($insert);
        $inserted = $stmt->execute();
        $idCajaBanco = $this->db->lastInsertId();
        if ($idCajaBanco) {
            $select = "SELECT * FROM Cb_CajaBanco where IdCajaBanco=$idCajaBanco";
            $stmt = $this->db->query($select);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $response->withJson($data);
        }
        // }else{
        // }
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});
$app->post('/cajaybanco/salida', function (Request $request, Response $response, array $args) {
    try {
        $idCuenta = $request->getParam('IdCuenta');
        $idPreOrden = $request->getParam('IdPreOrden', null);
        $idHabitacion = $request->getParam('IdHabitacion');
        $salida = $request->getParam('Salida');
        $producto = $request->getParam('producto');
        $concepto = $request->getParam('concepto');
        $usuario = $request->getParam('Usuario');
        $vendedor = 'xx';
        if (isset($_SESSION['User'])) {
            $vendedor = $_SESSION['User'];
        }
        $usuarioReg = isset($usuario) ? $request->getParam('Usuario') : $vendedor;
        // if ($idPreOrden) {
        $insert = "INSERT INTO 
        Cb_CajaBanco (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, Anulado,  EsDelVendedor,  UsuarioReg) 
        VALUES (3, $idCuenta, '" . getNow() . "', '$concepto', $salida, 0,  1, '$usuarioReg')";

        $stmt = $this->db->prepare($insert);
        $inserted = $stmt->execute();
        $idCajaBanco = $this->db->lastInsertId();
        if ($idCajaBanco) {
            $select = "SELECT * FROM Cb_CajaBanco where IdCajaBanco=$idCajaBanco";
            $stmt = $this->db->query($select);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $response->withJson($data);
        }
        // }else{
        // }
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});
$app->post('/cajaybanco/deposito', function (Request $request, Response $response, array $args) {
    try {
        $idCuenta = $request->getParam('IdCuenta');
        $idPreOrden = $request->getParam('IdPreOrden', null);
        $idHabitacion = $request->getParam('IdHabitacion');
        $deposito = $request->getParam('Deposito');
        $producto = $request->getParam('producto');
        $concepto = $request->getParam('concepto');
        $usuario = $request->getParam('Usuario');
        $vendedor = 'xx';
        if (isset($_SESSION['User'])) {
            $vendedor = $_SESSION['User'];
        }
        $usuarioReg = isset($usuario) ? $request->getParam('Usuario') : $vendedor;
        // if ($idPreOrden) {
        $insert = "INSERT INTO 
        Cb_CajaBanco (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, Anulado,  EsDelVendedor,  UsuarioReg) 
        VALUES (5, $idCuenta, '" . getNow() . "', '$concepto', $deposito, 0,  1, '$usuarioReg')";

        $stmt = $this->db->prepare($insert);
        $inserted = $stmt->execute();
        $idCajaBanco = $this->db->lastInsertId();
        if ($idCajaBanco) {
            $select = "SELECT * FROM Cb_CajaBanco where IdCajaBanco=$idCajaBanco";
            $stmt = $this->db->query($select);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $response->withJson($data);
        }
        // }else{
        // }
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});


$app->post('/preorden/cajaybanco/gasto', function (Request $request, Response $response, array $args) {
    $idPreOrden = $request->getParam('IdPreOrden');
    $idHabitacion = $request->getParam('IdHabitacion');
    $gasto = $request->getParam('Gasto');
    $producto = $request->getParam('producto');
    $concepto = $request->getParam('concepto');
    $usuario = $request->getParam('Usuario');

    $vendedor = 'xx';
    if (isset($_SESSION['User'])) {
        $vendedor = $_SESSION['User'];
    }
    $usuarioReg = isset($usuario) ? $request->getParam('Usuario') : $vendedor;

    if ($idPreOrden) {
        $insert = "INSERT INTO Cb_CajaBanco (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, Anulado, IdProveedor, IdCliente, EsDelVendedor, IdPreOrden, UsuarioReg) VALUES (3, 1, '" . getNow() . "', '$concepto', $gasto, 0, 0, $producto[IdCliente], 1, $idPreOrden, '$usuarioReg')";

        $stmt = $this->db->prepare($insert);
        $inserted = $stmt->execute();
        $idCajaBanco = $this->db->lastInsertId();
        if ($idCajaBanco) {
            $select = "SELECT * FROM Cb_CajaBanco where IdCajaBanco=$idCajaBanco";

            $stmt = $this->db->query($select);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $response->withJson($data);
        }
    }
});


$app->get('/preorden/cajaybanco', function (Request $request, Response $response, array $args) {
    $idPreOrden     = $request->getParam('IdPreOrden');
    $tipoCajaBanco  = $request->getParam('TipoCajaBanco');

    if ($idPreOrden) {
        $select = "SELECT Cb_CajaBanco.*, Cb_TipoCajaBanco.Tipo FROM Cb_CajaBanco 
            INNER JOIN Cb_TipoCajaBanco ON Cb_CajaBanco.IdTipoCajaBanco=Cb_TipoCajaBanco.IdTipoCajaBanco
            WHERE IdPreOrden=$idPreOrden AND Cb_TipoCajaBanco.Tipo = $tipoCajaBanco";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    }
});


$app->post('/preorden/detalle', function (Request $request, Response $response, array $args) {
    $idPreOrden = $request->getParam('IdPreOrden');
    $productos = $request->getParam('productos');
    $idHabitacion = $request->getParam('IdHabitacion');

    $idProforma = $request->getParam('IdProforma');

    if ($idProforma) {
        $sql = "UPDATE Ve_PreOrden SET IdProforma=$idProforma WHERE IdPreOrden=$idPreOrden ";

        $stmt = $this->db->prepare($sql);
        $updated = $stmt->execute();
    }

    if ($idPreOrden) {
        // Actualizar ProductoDet
        $sql = "DELETE FROM Ve_PreOrdenDet WHERE IdPreOrden='$idPreOrden' AND IdProducto != $idHabitacion";
        $stmt = $this->db->prepare($sql);
        $deleted = $stmt->execute();

        foreach ($productos as $prod) {
            $esManoDeObra = 0;
            if ($prod['EsManoDeObra'] === true || $prod['EsManoDeObra'] === 1) {
                $esManoDeObra = 1;
            }
            if ($prod['IdProducto'] != $idHabitacion) {
                $insertDet = $this->db->insert(array('IdPreOrden', 'IdProducto', 'Cantidad', 'Precio', 'EsManoDeObra'))
                    ->into('Ve_PreOrdenDet')
                    ->values(array($idPreOrden, $prod['IdProducto'], $prod['Cantidad'], $prod['Precio'], $esManoDeObra));
                $insertDetId = $insertDet->execute();
            }
        }
        return $response->withJson(array("affectedRows" => $productos));
    }
});
$app->get('/ventas/detalle/entregar', function (Request $request, Response $response) {
    $idDocVentas = $request->getParam('idDocVenta');

    $select = "SELECT 
    vdet.IdDocVenta AS IdDocVenta,
    vdet.IdDocVentaDet AS IdDocVentaDet,
    vdet.Cantidad AS Cantidad,
    vdet.Precio AS Precio,
    prod.Producto AS Producto,
    ProductoModelo,ProductoMarca,ProductoTalla,Color,Genero,Botapie,
    ROUND(vdet.Cantidad - IFNULL((
        SELECT SUM(Cantidad) FROM Ve_DocVentaDetEntrega 
        where Ve_DocVentaDetEntrega.IdDocVentaDet = vdet.IdDocVentaDet
    ), 0), 2) AS entregar
    FROM Ve_DocVentaDet AS vdet 
    INNER JOIN Gen_Producto AS prod ON
    INNER JOIN Gen_ProductoMedicion ON Gen_ProductoMedicion.IdProductoMedicion = prod.IdProductoMedicion
	INNER JOIN Gen_ProductoMarca ON Gen_ProductoMarca.IdProductoMarca = prod.IdProductoMarca
    INNER JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = prod.IdProductoTalla
		
    prod.IdProducto = vdet.IdProducto 
    WHERE IdDocVenta='$idDocVentas'";


    // $select = "SELECT 
    // vdet.IdDocVenta AS IdDocVenta,
    // vdet.IdDocVentaDet AS IdDocVentaDet,
    // vdet.Cantidad AS Cantidad,
    // vdet.Precio AS Precio,
    // prod.Producto AS Producto,
    // ROUND(vdet.Cantidad - IFNULL((
    //     SELECT SUM(Cantidad) FROM Ve_DocVentaDetEntrega 
    //     where Ve_DocVentaDetEntrega.IdDocVentaDet = vdet.IdDocVentaDet
    // ), 0), 2) AS entregar,
    // vdetEnt.Fecha AS Fecha 
    // FROM 
    // Ve_DocVentaDet AS vdet INNER JOIN Gen_Producto AS prod 
    // ON prod.IdProducto = vdet.IdProducto INNER JOIN Ve_DocVentaDetEntrega AS vdetEnt 
    // ON vdetEnt.IdDocVentaDet = vdet.IdDocVentaDet 
    // WHERE IdDocVenta='$idDocVentas'";


    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->post('/ventas/detalle', function (Request $request, Response $response) {
    try {


        $idDocVentas = $request->getParam('idDocVentas');

        $select = "SELECT Ve_DocVentaDet.*, Gen_Producto.Producto,
         ProductoModelo,ProductoMarca,ProductoTalla,Color,Genero,Botapie,
        ROUND(Ve_DocVentaDet.Descuento, 2) AS Descuento,
        ROUND((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - Ve_DocVentaDet.Descuento, 2) AS Subtotal,
        ROUND(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio, 2) AS Total,
        Gen_Producto.CodigoBarra, Gen_ProductoMedicion.Codigo AS CodigoMedicion, Gen_ProductoMedicion.ProductoMedicion
        FROM Ve_DocVentaDet
        INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
        INNER JOIN Gen_ProductoMedicion ON Gen_ProductoMedicion.IdProductoMedicion = Gen_Producto.IdProductoMedicion
		INNER JOIN Gen_ProductoMarca ON Gen_ProductoMarca.IdProductoMarca = Gen_Producto.IdProductoMarca
        INNER JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla
		WHERE IdDocVenta IN (" . implode(',', $idDocVentas) . ")";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
        //throw $th;
    }
});

$app->post('/cierrecaja/ingresos/adelantos', function (Request $request, Response $response, array $args) {
    $idDocVentas = $request->getParam('idDocVentas');

    if ($idDocVentas) {
        $select = "SELECT Cb_CajaBanco.IdCajaBanco, Cb_TipoCajaBanco.Tipo, Cb_CajaBanco.IdCuenta, Cb_Cuenta.Cuenta, Cb_CajaBanco.FechaDoc, 
        Cb_CajaBanco.Concepto  , Cb_CajaBanco.Importe, Ve_DocVenta.idDocVenta, Ve_DocVenta.Serie, Ve_DocVenta.Numero,
        Ve_DocVenta.Anulado AS AnuladoVenta, Ve_DocVenta.EsCredito AS EsCreditoVenta
          FROM Cb_CajaBanco
          INNER JOIN Cb_TipoCajaBanco ON Cb_CajaBanco.IdTipoCajaBanco = Cb_TipoCajaBanco.IdTipoCajaBanco
          INNER JOIN Cb_Cuenta ON Cb_CajaBanco.IdCuenta = Cb_Cuenta.IdCuenta
          LEFT JOIN Ve_PreOrden ON Cb_CajaBanco.IdPreOrden = Ve_PreOrden.IdPreOrden
          LEFT JOIN Ve_DocVenta ON Ve_PreOrden.IdDocVenta = Ve_DocVenta.idDocVenta";


        $select .= " WHERE Ve_DocVenta.idDocVenta IN ( " . implode(',', $idDocVentas) . " )";

        $select .= " AND Cb_TipoCajaBanco.Tipo = 0
          ORDER BY FechaDoc ASC;";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
    } else {
        $data = [];
    }

    return $response->withJson($data);
});
// ------------------------------------------------------------NUEVOS CAMBIOS
$app->get('/ordenCompra', function (Request $request, Response $response, array $args) {
    try {

        $IdAlmacen = $request->getParam('IdAlmacen');
        $IdProveedor = $request->getParam('IdProveedor') ?? null;

        $select = "
        SELECT 
        Lo_Almacen.Almacen,
        Lo_Proveedor.Proveedor,
        Lo_OrdenCompra.*,
        DATE(Lo_OrdenCompra.FechaOrdenCompra) AS FechaOrdenCompra
        FROM Lo_OrdenCompra
        JOIN Lo_Almacen ON Lo_Almacen.IdAlmacen = Lo_OrdenCompra.IdAlmacen
        JOIN Lo_Proveedor ON Lo_Proveedor.IdProveedor= Lo_OrdenCompra.IdProveedor
        WHERE Lo_OrdenCompra.IdAlmacen=" . $IdAlmacen;

        if ($IdProveedor) {
            $select .= " AND Lo_OrdenCompra.IdProveedor=" . $IdProveedor;
        }

        $select .= " ORDER BY Lo_OrdenCompra.FechaOrdenCompra DESC";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        foreach ($data as &$ordenCompra) {  // Usar referencia para modificar el arreglo original
            $sqlDetalle = "
                SELECT Lo_OrdenCompraDet.*,Gen_Producto.Producto FROM
                Lo_OrdenCompraDet 
                JOIN Gen_Producto ON Lo_OrdenCompraDet.IdProducto = Gen_Producto.IdProducto
                WHERE IdOrdenCompra=" . $ordenCompra['IdOrdenCompra'];
            $stmtDetalle = $this->db->query($sqlDetalle); // Cambiar a $stmtDetalle para evitar sobrescribir la consulta principal
            $stmtDetalle->execute();
            $detalle = $stmtDetalle->fetchAll();
            $ordenCompra['Detalle'] = $detalle;  // Asignar el detalle a la orden de compra
        }

        return $response->withJson($data);  // Devolver el resultado con los detalles
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});


$app->post('/guardarordencompra', function (Request $request, Response $response, array $args) {
    try {
        $IdAlmacen = $request->getParam('IdAlmacen');
        $fecha = $request->getParam('fecha');
        $total = $request->getParam('total');
        $IdProveedor = $request->getParam('proveedor')['IdProveedor'];
        $lstProductos = $request->getParam('lstProductos');

        $Ssql = "SELECT Numero, Anio FROM Lo_OrdenCompra WHERE IdAlmacen=$IdAlmacen AND  Anio='" . date("Y") . "' ORDER BY Numero DESC LIMIT 1";
        $stmt = $this->db->query($Ssql);
        $stmt->execute();
        $ordenCompra = $stmt->fetch();

        $numero = $ordenCompra['Numero'];
        $anio = $ordenCompra['Anio'];

        $newNumero = 1;
        $newAnio = date("Y");


        if ($numero) {
            $newNumero = $numero + 1;
        }
        if ($anio) {
            $newAnio = $anio;
        }

        $insert = $this->db->insert(array(
            'IdAlmacen',
            'IdProveedor',
            'FechaOrdenCompra',
            'Total',
            'Anio',
            'Numero'
        ))
            ->into('Lo_OrdenCompra')
            ->values(array(
                $IdAlmacen,
                $IdProveedor,
                $fecha,
                $total,
                $newAnio,
                $newNumero,
            ));
        $insertId = $insert->execute();

        foreach ($lstProductos as $producto) {
            $insert = $this->db->insert(array(
                'IdOrdenCompra',
                'IdProducto',
                'Cantidad',
                'Precio'
            ))
                ->into('Lo_OrdenCompraDet')
                ->values(array(
                    $insertId,
                    $producto['IdProducto'],
                    $producto['Cantidad'],
                    $producto['precio'],
                ));
            $insert->execute();
        }
        return $response->withJson($insertId);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->post('/ordencompra/actualizar', function (Request $request, Response $response) {

    $idOrdenCompra = $request->getParam('idOrdenCompra');
    if (!($idOrdenCompra > 0)) {
        return $response->withJson(array("success" => false, "msg" => "Error: No se especifico la orden"));
    }

    $sql = "DELETE FROM Lo_OrdenCompraDet WHERE IdOrdenCompra = " . $idOrdenCompra;
    $stmt = $this->db->prepare($sql);
    $deleted = $stmt->execute();

    $productos = $request->getParam('productos');
    foreach ($productos as $producto) {
        $idProducto = $producto['IdProducto'];
        $cantidad = $producto['cantidad'];
        $precio = $producto['precio'];

        $insert = $this->db->insert(array('IdOrdenCompra', 'IdProducto', 'Cantidad', 'Precio'))
            ->into('Lo_OrdenCompraDet')
            ->values(array($idOrdenCompra, $idProducto, $cantidad, $precio));
        $insert->execute();
    }

    return $response->withJson(array("success" => true, "msg" => "Actualizado Correctamente"));
});

$app->get('/ordencompra/detalle', function (Request $request, Response $response) {
    $idOrdenCompra = $request->getParam('IdOrdenCompra');

    $select = "SELECT Lo_OrdenCompraDet.*, Gen_Producto.Producto, Gen_Producto.CodigoBarra,
    Gen_ProductoCategoria.ProductoCategoria,
    Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica,
    Gen_ProductoMarca.ProductoMarca,
    Gen_ProductoMedicion.ProductoMedicion,
    Gen_ProductoTalla.ProductoTalla,
    Gen_Producto.ProductoModelo,
    Gen_Producto.Color,
    Gen_Producto.Genero,
    Gen_Producto.Botapie,
    Gen_Producto.Imagen,
    Gen_Producto.ProductoDesc,
    Gen_Producto.ProductoDescCorto,
    Gen_Producto.CodigoBarra,
    Gen_Producto.Codigo,
    Gen_Producto.Dosis,
    Gen_Producto.PrecioPorMayor,
    Gen_Producto.StockPorMayor,
    Gen_Producto.StockMinimo,
    Gen_Producto.Anulado,
    Gen_Producto.FechaReg,
    Gen_Producto.Hash,
    Gen_Producto.ControlaStock,
    Gen_Producto.PrecioCosto,
    Gen_Producto.VentaEstrategica,
    Gen_Producto.PorcentajeUtilidad,
    Gen_Producto.IdBloque,
    Gen_Producto.EsPadre,Lo_OrdenCompraDet.Precio AS precio, Lo_OrdenCompraDet.Cantidad AS cantidad FROM Lo_OrdenCompraDet
    INNER JOIN Gen_Producto ON Lo_OrdenCompraDet.IdProducto = Gen_Producto.IdProducto
    LEFT JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
    LEFT JOIN Gen_ProductoFormaFarmaceutica ON Gen_Producto.IdProductoFormaFarmaceutica = Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica 
	LEFT JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca	
    LEFT JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
    LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla
    WHERE IdOrdenCompra = $idOrdenCompra";


    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->post('/ordencompra/anular', function (Request $request, Response $response) {

    $idOrdenCompra = $request->getParam('idOrdenCompra');
    if (!($idOrdenCompra > 0)) {
        return $response->withJson(array("success" => false, "msg" => "Error: No se especifico la orden"));
    }

    $update = $this->db->update(array("Estado" => 1))
        ->table('Lo_OrdenCompra')
        ->where('IdOrdenCompra', '=', $idOrdenCompra);
    $update->execute();

    return $response->withJson(array("success" => true, "msg" => "Actualizado Correctamente"));
});


$app->post('/ordencompra', function (Request $request, Response $response) {
    $idProveedor = $request->getParam('movimiento')['proveedor']['IdProveedor'];
    $fechaReg = getNow();
    $user = $request->getParam('movimiento')['user'];
    $usuarioReg = 'xx';
    if (isset($_SESSION['user'])) {
        $usuarioReg = $_SESSION['user'];
    }
    $usuarioReg = $user ? $user : $usuarioReg;
    $idAlmacen = $request->getParam('IdAlmacen');

    // Verificar si existe la proforma
    $select = "SELECT Numero, Anio FROM Lo_OrdenCompra WHERE Anio='" . getNow("Y") . "' ORDER BY Numero DESC LIMIT 1";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $ordenCompra = $stmt->fetch();

    $numero = $ordenCompra['Numero'];
    $anio = $ordenCompra['Anio'];

    $newNumero = 1;
    $newAnio = getNow("Y");

    if ($numero && $anio) {
        $newNumero = $numero + 1;
    }

    // START INSERTAR NUEVA PROFORMA
    $insert = "INSERT INTO Lo_OrdenCompra(IdProveedor,FechaOrdenCompra,Anio,Numero,IdAlmacen) VALUES ($idProveedor, '$fechaReg',$newAnio, $newNumero,$idAlmacen)";

    $stmt = $this->db->prepare($insert);
    $inserted = $stmt->execute();
    $idOrdenCompra = $this->db->lastInsertId();
    // END INSERTAR NUEVA PROFORMA


    // START INSERTAR PROFORMA DETALLE
    $productos = $request->getParam('productos');
    foreach ($productos as $producto) {
        $idProducto = $producto['IdProducto'];
        $cantidad = $producto['cantidad'];
        $precio = $producto['precio'];

        $insert = $this->db->insert(array('IdOrdenCompra', 'IdProducto', 'Cantidad', 'Precio'))
            ->into('Lo_OrdenCompraDet')
            ->values(array($idOrdenCompra, $idProducto, $cantidad, $precio));
        $insert->execute();
    }
    // END PROFORMA DETALLE

    $data = array(
        'insertId' => $idOrdenCompra
    );

    return $response->withJson($data);
});

$app->get('/productos/proveedor', function (Request $request, Response $response, array $args) {

    $idAlmacen = $request->getParam('IdAlmacen');
    $idProveedor = $request->getParam('IdProveedor');

    $select = "
    SELECT Gen_Producto.*,   (
                    SELECT Lo_MovimientoDetalle.Cantidad FROM `Lo_MovimientoDetalle`
                    JOIN Lo_Movimiento ON Lo_Movimiento.Hash = Lo_MovimientoDetalle.hashMovimiento
                    WHERE Lo_Movimiento.IdProveedor = $idProveedor  AND Lo_MovimientoDetalle.IdProducto = Gen_Producto.IdProducto
                    ORDER BY FechaReg DESC 
                    LIMIT 1  
                ) cantidadPedida,
    Gen_ProductoCategoria.ProductoCategoria, 
    Gen_ProductoMarca.ProductoMarca, 
    Gen_ProductoMedicion.ProductoMedicion, 
    Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica, 
    ROUND(Gen_Producto.PrecioCosto,4) as precio,
    COALESCE(
        (
            SELECT
            Lo_MovimientoDetalle.Cantidad AS cantidad
            FROM
            Lo_Movimiento 
            JOIN Lo_MovimientoDetalle ON Lo_Movimiento.Hash =Lo_MovimientoDetalle.hashMovimiento
            WHERE Lo_Movimiento.IdProveedor =  $idProveedor AND Lo_MovimientoDetalle.IdProducto = Gen_Producto.IdProducto
            ORDER BY Lo_Movimiento.FechaReg DESC 
            LIMIT 1
        )
    ,0) cantidad,
    ROUND(TotalCompras -TotalVentas,2)  as stock 
    FROM Lo_MovimientoDetalle
    INNER JOIN Gen_Producto ON Lo_MovimientoDetalle.IdProducto = Gen_Producto.IdProducto
    INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
    INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
    INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
    INNER JOIN Gen_ProductoFormaFarmaceutica ON Gen_Producto.IdProductoFormaFarmaceutica = Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica
    INNER JOIN Lo_Movimiento ON Lo_MovimientoDetalle.hashMovimiento =  Lo_Movimiento.Hash
    WHERE Lo_Movimiento.IdMovimientoTipo IN (1,2,3) AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen AND Lo_Movimiento.IdProveedor = $idProveedor";

    $select .= " AND Gen_Producto.Anulado=0 AND Gen_Producto.StockMinimo > 0 AND Gen_Producto.ControlaStock = 1 GROUP BY Gen_Producto.IdProducto";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/ordencompra', function (Request $request, Response $response) {
    $fechaDesde = $request->getParam('fechaDesde') ? $request->getParam('fechaDesde') : '2017-01-01T00:00:00';
    $fechaHasta = $request->getParam('fechaHasta') . " 23:59:59";
    $estado = $request->getParam('estado');

    $select = "SELECT Lo_OrdenCompra.*,Lo_Proveedor.*, Lo_Almacen.Almacen, CONCAT(Lo_OrdenCompra.Anio, '-', Lo_OrdenCompra.Numero) as Correlativo,
    ROUND(SUM(Lo_OrdenCompraDet.Precio*Lo_OrdenCompraDet.Cantidad),2) AS Total FROM Lo_OrdenCompra
    INNER JOIN Lo_Proveedor ON Lo_OrdenCompra.IdProveedor = Lo_Proveedor.IdProveedor
    LEFT JOIN Lo_OrdenCompraDet ON Lo_OrdenCompra.IdOrdenCompra = Lo_OrdenCompraDet.IdOrdenCompra
    LEFT JOIN Lo_Almacen ON Lo_Almacen.IdAlmacen = Lo_OrdenCompra.IdAlmacen";

    $select .= " WHERE Lo_OrdenCompra.FechaOrdenCompra BETWEEN '$fechaDesde' AND '$fechaHasta' ";

    if ($estado >= 0) {
        $select .= " AND Lo_OrdenCompra.Estado = " . $estado;
    }

    $select .= " GROUP BY Lo_OrdenCompra.IdOrdenCompra ORDER BY Lo_OrdenCompra.FechaOrdenCompra DESC";

    // print_r($select);exit();
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/reporte/ordencomprapdf', function (Request $request, Response $response, array $args) use ($app) {
    try {

        $idOrdenCompra = $request->getParam('id');


        $sql = "SELECT Lo_OrdenCompra.*,Lo_Proveedor.Proveedor, Lo_Almacen.Almacen FROM 
                Lo_OrdenCompra 
                JOIN Lo_Proveedor ON Lo_OrdenCompra.IdProveedor = Lo_Proveedor.IdProveedor
                JOIN Lo_Almacen ON Lo_Almacen.IdAlmacen =  Lo_OrdenCompra.IdAlmacen
                WHERE Lo_OrdenCompra.IdOrdenCompra = $idOrdenCompra";

        $stmt = $this->db->query($sql);
        $stmt->execute();
        $ordencompra = $stmt->fetch();

        $Ssql = "SELECT Gen_Producto.IdProducto, Gen_Producto.Codigo, Gen_Producto.Producto, 
                Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica, Gen_ProductoMarca.ProductoMarca, Lo_OrdenCompraDet.Cantidad, Gen_ProductoMarca.ProductoMarca as Laboratorio, 
                Lo_OrdenCompraDet.Precio, Lo_OrdenCompra.Anio, Lo_OrdenCompra.Numero, Lo_Proveedor.Proveedor, Lo_OrdenCompra.FechaOrdenCompra,Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica as Forma,
                        Lo_Proveedor.Ruc, Lo_Proveedor.Direccion, Gen_ProductoMedicion.ProductoMedicion as Medicion, Gen_ProductoMedicion.ProductoMedicion, Lo_Almacen.Almacen
                FROM Lo_OrdenCompraDet 
                INNER JOIN Gen_Producto ON Lo_OrdenCompraDet.IdProducto = Gen_Producto.IdProducto
                INNER JOIN Lo_OrdenCompra ON Lo_OrdenCompraDet.IdOrdenCompra = Lo_OrdenCompra.IdOrdenCompra
                INNER JOIN Lo_Proveedor ON Lo_OrdenCompra.IdProveedor = Lo_Proveedor.IdProveedor
                LEFT JOIN Gen_ProductoFormaFarmaceutica ON Gen_Producto.IdProductoFormaFarmaceutica = Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica 
                LEFT JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca	
                LEFT JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
                LEFT JOIN Lo_Almacen ON Lo_Almacen.IdAlmacen = Lo_OrdenCompra.IdAlmacen
	            where Lo_OrdenCompraDet.IdOrdenCompra = $idOrdenCompra";

        $stmt = $this->db->query($Ssql);
        $stmt->execute();
        $detalle = $stmt->fetchAll();

        $data = array(
            'txtTIPO_PROCESO' => '03',
            'detalle' => $detalle,
            'ordencompra' => $ordencompra,
            'txtNRO_DOCUMENTO_EMPRESA' => NRO_DOCUMENTO_EMPRESA,
        );

        $rutapdf = $_SERVER['HTTP_HOST'] . '/api/sunat/plugins/dompdf/';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $rutapdf . "OrdenCompra.php",
            CURLOPT_USERAGENT => "Codular Sample cURL Request",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));

        $resp = curl_exec($curl);
        curl_close($curl);

        // Ruta del archivo generado
        $urlArchivo = $_SERVER['HTTP_HOST']  . '/api/sunat/api_cpe/PRODUCCION/' . NRO_DOCUMENTO_EMPRESA . '/OrdenCompra_' . $ordencompra['Anio'] . '_' . $ordencompra['Numero'] . '.pdf';
        $urlArchivo = stripslashes($urlArchivo);

        return $response->withJson($urlArchivo);
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], 500);
    }
});


$app->get('/reporte/ordencompra/id', function (Request $request, Response $response, array $args) use ($app) {
    $idOrdenCompra = $request->getParam('idordencompra');

    $select = "
        SELECT Gen_Producto.IdProducto, Gen_Producto.Codigo, Gen_Producto.Producto, Gen_Producto.StockMinimo,
         COALESCE(
        (
            SELECT
            Lo_MovimientoDetalle.Cantidad AS cantidad
            FROM
            Lo_Movimiento 
            JOIN Lo_MovimientoDetalle ON Lo_Movimiento.Hash =Lo_MovimientoDetalle.hashMovimiento
            WHERE Lo_Movimiento.IdProveedor =  Lo_Proveedor.IdProveedor AND Lo_MovimientoDetalle.IdProducto = Gen_Producto.IdProducto
            ORDER BY Lo_Movimiento.FechaReg DESC 
            LIMIT 1
        )
        ,0) cantidadSugeridad,
        Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica, Gen_ProductoMarca.ProductoMarca, Lo_OrdenCompraDet.Cantidad, Gen_ProductoMarca.ProductoMarca as Laboratorio, 
        Lo_OrdenCompraDet.Precio, Lo_OrdenCompra.Anio, Lo_OrdenCompra.Numero, Lo_Proveedor.Proveedor, Lo_OrdenCompra.FechaOrdenCompra,Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica as Forma,
        Lo_Proveedor.Ruc, Lo_Proveedor.Direccion, Gen_ProductoMedicion.ProductoMedicion as Medicion, Gen_ProductoMedicion.ProductoMedicion, Lo_Almacen.Almacen
    FROM Lo_OrdenCompraDet 
    INNER JOIN Gen_Producto ON Lo_OrdenCompraDet.IdProducto = Gen_Producto.IdProducto
    INNER JOIN Lo_OrdenCompra ON Lo_OrdenCompraDet.IdOrdenCompra = Lo_OrdenCompra.IdOrdenCompra
    INNER JOIN Lo_Proveedor ON Lo_OrdenCompra.IdProveedor = Lo_Proveedor.IdProveedor
    LEFT JOIN Gen_ProductoFormaFarmaceutica ON Gen_Producto.IdProductoFormaFarmaceutica = Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica 
    LEFT JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca	
    LEFT JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
    LEFT JOIN Lo_Almacen ON Lo_Almacen.IdAlmacen = Lo_OrdenCompra.IdAlmacen
    where Lo_OrdenCompraDet.IdOrdenCompra = $idOrdenCompra";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    $numero = $data[0]['Numero'] . '-' . $data[0]['Anio'];

    $excel = new Spreadsheet();
    //$sheet = $excel->setActiveSheetIndex(0);
    $sheet = $excel->getActiveSheet();
    $excel->getActiveSheet()->mergeCells('A1:J1');
    $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
    $sheet->setCellValue('A1', RAZON_SOCIAL_EMPRESA . "- " . $data[0]['Almacen']);
    $sheet->setCellValue('A2', 'RUC:' . NRO_DOCUMENTO_EMPRESA);
    $sheet->setCellValue('A3', DIRECCION_EMPRESA);
    $sheet->setCellValue('A4', 'FECHA DE ORDEN');
    $sheet->setCellValue('B4', $data[0]['FechaOrdenCompra']);
    $sheet->setCellValue('A5', 'PROVEEDOR');
    $sheet->setCellValue('B5', $data[0]['Proveedor']);
    $sheet->setCellValue('A6', 'N° DE ORDEN COMPRA');
    $sheet->setCellValue('B6', $numero);

    $sheet->setCellValue('A8', 'COD');
    $sheet->setCellValue('B8', 'PRODUCTO');
    $sheet->setCellValue('C8', 'MEDICION');
    $sheet->setCellValue('D8', 'S. M.');
    $sheet->setCellValue('E8', 'C. S.');
    $sheet->setCellValue('F8', 'CANTIDAD');
    $sheet->setCellValue('G8', 'PRECIO');
    $sheet->setCellValue('H8', 'TOTAL');


    $cont = 9;
    $sumatoria = 0;
    foreach ($data as $key => $row) {
        $total = $row["Precio"] * $row["Cantidad"];
        $sumatoria += $total;
        $sheet->setCellValue('A' . $cont, $row["IdProducto"]);
        $sheet->setCellValue('B' . $cont, $row["Producto"]);
        $sheet->setCellValue('C' . $cont, $row["ProductoMedicion"]);
        $sheet->setCellValue('D' . $cont, $row["StockMinimo"]);
        $sheet->setCellValue('E' . $cont, $row["cantidadSugeridad"]);
        $sheet->setCellValue('F' . $cont, $row["Cantidad"]);
        $sheet->setCellValue('G' . $cont, $row["Precio"]);
        $sheet->setCellValue('H' . $cont, $total);
        $cont++;
    }
    //Diseño excel
    foreach (range('B', 'H') as $columnID) {
        $excel->getActiveSheet()->getColumnDimension($columnID)
            ->setAutoSize(true);
    }

    foreach (array('A8', 'B8', 'C8', 'D8', 'E8', 'F8', 'G8', 'H8') as $columnID) {
        $excel->getActiveSheet()->getStyle($columnID)->getBorders()->getOutline()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('#000000'));
    }

    $excel->getActiveSheet()->getStyle('A5:J6')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A2:A4')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A8:H8')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('B4')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A1:A7')->getAlignment()->setHorizontal('left');
    $excel->getActiveSheet()->getStyle('A8:H8')->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('C6:B' . $cont)->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('B6:B' . $cont)->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('D6:B' . $cont)->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('E6:B' . $cont)->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('A8:H8')->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFE6E6FA');

    //Totales
    $excel->getActiveSheet()->mergeCells('A' . ($cont + 1) . ':G' . ($cont + 1));
    $excel->getActiveSheet()->getStyle('A' . ($cont + 1))->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('A' . ($cont + 1))->getFont()->setBold(true);
    $sheet->setCellValue('A' . ($cont + 1), 'SUMATORIA');
    $sheet->setCellValue('H' . ($cont + 1), $sumatoria);
    //$excel->getActiveSheet()->getStyle('H'.$cont.':J'.$cont)->getFont()->setBold( true );


    aplicarIdentidadEmpresaExcel($excel, $this->db);
    $excelWriter = new Xlsx($excel);

    $fileName = 'Orden de Compra ' . $numero . ' (' . getNow('Y-m-d-H-i-s') .  ').xlsx';
    $excelFileName = __DIR__ . '/reporte/' . $fileName;
    $excelWriter->save($excelFileName);

    echo "<script>window.location.href = '/api/reporte/" . $fileName . "'</script>";
    exit;
});

$app->get('/reporte/ListarRegVenta', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $fechaIni = $request->getParam('fechaIni');
        $fechaFin = $request->getParam('fechaFin');
        $declarado = $request->getParam('declarado');
        $almacen = $request->getParam('almacen');
        $tipoDocumento = $request->getParam('tipoDocumento');
        $tiposPermitidos = ['01', '03', '12'];
        $tiposSolicitados = array_values(array_intersect(
            array_filter(array_map('trim', explode(',', (string) $tipoDocumento))),
            $tiposPermitidos
        ));



        $Ssql = "SELECT Ve_DocVenta.idDocVenta, Ve_DocVenta.UsuarioReg, Ve_DocVenta.FechaDoc,
		Ve_DocVentaTipoDoc.CodSunat, Ve_DocVentaTipoDoc.TipoDoc, Ve_DocVenta.Anulado,
	   IFNULL(Ve_DocVentaCliente.Cliente, 'VARIOS') AS Cliente,
	   Ve_DocVenta.Serie, Ve_DocVenta.Numero, IFNULL((Select Sum(Round((Ve_DocVentaDet.Precio* Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento,2)) FROM Ve_DocVentaDet Where Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.idDocVenta),0) as SubTotal,
	   IF(Ve_DocVentaTipoDoc.TieneIgv = 1,
	   ROUND((IFNULL((SELECT SUM(Round((Ve_DocVentaDet.Precio* Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento,2)) FROM Ve_DocVentaDet WHERE Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.idDocVenta),0)) -
	   (IFNULL((SELECT SUM(Round((Ve_DocVentaDet.Precio* Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento,2)) FROM Ve_DocVentaDet WHERE Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.idDocVenta),0)) / 1.18, 2)
	   , 0) as Igv,
	   IFNULL((Select Sum(Round((Ve_DocVentaDet.Precio* Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento,2)) FROM Ve_DocVentaDet Where Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.idDocVenta),0) as Total
	   FROM Ve_DocVenta INNER JOIN Ve_DocVentaTipoDoc On Ve_DocVenta.IdTipoDoc=Ve_DocVentaTipoDoc.IdTipoDoc
	   LEFT JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente
	   WHERE Ve_DocVentaTipoDoc.VaRegVenta=$declarado";

        if (!empty($almacen) || $almacen != 0) {
            $Ssql .= " AND Ve_DocVenta.IdAlmacen = $almacen ";
        }

        if (count($tiposSolicitados) > 0) {
            $codigosSql = "'" . implode("','", $tiposSolicitados) . "'";
            $Ssql .= " AND Ve_DocVentaTipoDoc.CodSunat IN ($codigosSql) ";
        }

        $Ssql .= " AND CAST(Ve_DocVenta.Fechadoc AS DATE) BETWEEN CAST('$fechaIni' AS DATE) and  CAST('$fechaFin' AS DATE)
		  ORDER BY  Ve_DocVenta.Fechadoc DESC;";
        $stmt = $this->db->query($Ssql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['errorr' => $e->getMessage()], 500);
    }
});

$app->get('/reporte/ListarRegVenta/detalle', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $idDocVenta = $request->getParam('idDocVenta');
        $Ssql = "SELECT 
        Gen_Producto.IdProducto, Gen_Producto.Producto, 
        Gen_Producto.Codigo, Gen_Producto.CodigoBarra, 
        Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica, 
        Gen_ProductoMedicion.ProductoMedicion, Ve_DocVentaDet.IdDocVenta, 
        Ve_DocVentaDet.Cantidad, Ve_DocVentaDet.Precio, 
        Ve_DocVentaDet.Descuento, 
        Gen_ProductoMarca.ProductoMarca,
        ProductoModelo,ProductoMarca,ProductoTalla,Color,Genero,Botapie
		FROM Gen_Producto
		INNER JOIN Ve_DocVentaDet ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
		INNER JOIN Gen_ProductoFormaFarmaceutica ON Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica = Gen_Producto.IdProductoFormaFarmaceutica
		INNER JOIN Gen_ProductoMedicion ON Gen_ProductoMedicion.IdProductoMedicion = Gen_Producto.IdProductoMedicion
		INNER JOIN Gen_ProductoMarca ON Gen_ProductoMarca.IdProductoMarca = Gen_Producto.IdProductoMarca
        INNER JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla
		WHERE Ve_DocVentaDet.IdDocVenta = $idDocVenta;";


        $stmt = $this->db->query($Ssql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['errorr' => $e->getMessage()], 500);
    }
});


$app->put('/reporte/ListarRegVenta/anular', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $idDocVenta = $request->getParam('idDocVenta');

        $sql = "SELECT IdAlmacen FROM Ve_DocVenta where idDocVenta=$idDocVenta";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $idAlmacen = $stmt->fetch();
        $idAlmacen = $idAlmacen['IdAlmacen'];

        $sql = "SELECT * FROM Ve_DocVentaDet WHERE IdDocVenta = $idDocVenta";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $detalles = $stmt->fetchAll();

        foreach ($detalles as $detalle) {
            $update = "UPDATE Gen_Producto_Totales SET TotalVentas= TotalVentas-" . $detalle['Cantidad'] . " WHERE IdProducto =" . $detalle['IdProducto'] . " AND IdAlmacen=" . $idAlmacen;
            $stmt = $this->db->prepare($update);
            $stmt->execute();
        }

        $update = $this->db->update(array(
            'Anulado' => 1
        ))
            ->table('Ve_DocVenta')
            ->where('idDocVenta', '=', $idDocVenta);
        $affectedRows = $update->execute();


        return $response->withJson($affectedRows);
    } catch (Exception $e) {
        return $response->withJson(['errorr' => $e->getMessage()], 500);
    }
});

$app->get('/reporte/ListarRegVenta/excel', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $fechaIni = $request->getParam('fechaIni');
        $fechaFin = $request->getParam('fechaFin');
        $declarado = $request->getParam('declarado');
        $almacen = $request->getParam('almacen');
        $tipoDocumento = $request->getParam('tipoDocumento');
        $tiposSeleccionados = array_values(array_intersect(
            array_filter(array_map('trim', explode(',', (string) $tipoDocumento))),
            ['01', '03', '12']
        ));
        $res = $app->subRequest('GET', 'reporte/ListarRegVenta', '?fechaIni=' . urlencode($fechaIni) . '&fechaFin=' . urlencode($fechaFin) . '&declarado=' . urlencode($declarado) . '&almacen=' . urlencode($almacen) . '&tipoDocumento=' . urlencode($tipoDocumento));


        $deudacliente = (string) $res->getBody();
        $data = json_decode($deudacliente, true);



        $excel = new Spreadsheet();
        //$sheet = $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet();
        $excel->getActiveSheet()->mergeCells('A1:L1');
        $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal('center');
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
        $sheet->setCellValue('A1', 'REPORTE REGISTRO VENTAS: ' . RAZON_SOCIAL_EMPRESA);
        $sheet->setCellValue('A2', 'RUC:' . NRO_DOCUMENTO_EMPRESA);
        $sheet->setCellValue('A3', DIRECCION_EMPRESA);
        $nombresComprobante = ['01' => 'FACTURAS', '03' => 'BOLETAS', '12' => 'NOTAS DE VENTA'];
        $etiquetasFiltro = array_map(function ($codigo) use ($nombresComprobante) {
            return $nombresComprobante[$codigo];
        }, $tiposSeleccionados);
        $textoFiltro = count($etiquetasFiltro) ? implode(' + ', $etiquetasFiltro) : 'TODOS';
        $sheet->setCellValue('A2', 'RUC: ' . NRO_DOCUMENTO_EMPRESA . '   |   PERIODO: ' . $fechaIni . ' AL ' . $fechaFin);
        $sheet->setCellValue('A3', DIRECCION_EMPRESA . '   |   COMPROBANTES: ' . $textoFiltro);

        $sheet->setCellValue('A4', 'Id');
        $sheet->setCellValue('B4', 'Vendedor');
        $sheet->setCellValue('C4', 'Fecha Doc');
        $sheet->setCellValue('D4', 'Cod. Sunat');
        $sheet->setCellValue('E4', 'Tipo Doc.');
        $sheet->setCellValue('F4', 'Cliente');
        $sheet->setCellValue('G4', 'Estado');
        $sheet->setCellValue('H4', 'Serie');
        $sheet->setCellValue('I4', 'Número');
        $sheet->setCellValue('J4', 'SubTotal');
        $sheet->setCellValue('K4', 'IGV');
        $sheet->setCellValue('L4', 'Total');



        $cont = 5;
        $sumatoria = 0;
        foreach ($data as $key => $row) {
            $estaAnulado = (int) $row['Anulado'] === 1;
            if (!$estaAnulado) {
                $total = $row["Total"];
                $sumatoria += $total;
            }

            $sheet->setCellValue('A' . $cont, $row['idDocVenta']);
            $sheet->setCellValue('B' . $cont, $row['UsuarioReg']);
            $sheet->setCellValue('C' . $cont, $row['FechaDoc']);
            $sheet->setCellValue('D' . $cont, $row['CodSunat']);
            $sheet->setCellValue('E' . $cont, $row['TipoDoc']);
            $sheet->setCellValue('F' . $cont, $row['Cliente']);
            $sheet->setCellValue('G' . $cont, $estaAnulado ? 'ANULADO' : 'ACTIVO');
            $sheet->setCellValue('H' . $cont, $row['Serie']);
            $sheet->setCellValue('I' . $cont, $row['Numero']);
            $sheet->setCellValue('J' . $cont, $row['SubTotal']);
            $sheet->setCellValue('K' . $cont, $row['Igv']);
            $sheet->setCellValue('L' . $cont, $estaAnulado ? 0 : $row['Total']);

            if ($estaAnulado) {
                $excel->getActiveSheet()->getStyle('A' . $cont . ':L' . $cont)
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFCDD2');
                $excel->getActiveSheet()->getStyle('A' . $cont . ':L' . $cont)
                    ->getFont()->getColor()->setARGB('FF9F1239');
                $excel->getActiveSheet()->getStyle('G' . $cont)->getFont()->setBold(true);
            }
            $cont++;
        }


        foreach (range('B', 'L') as $columnID) {
            $excel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $excel->getActiveSheet()->getStyle('A4:L4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $excel->getActiveSheet()->getStyle('A4:L4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0B5E8E');
        $excel->getActiveSheet()->freezePane('A5');
        $excel->getActiveSheet()->setAutoFilter('A4:L' . max(4, $cont - 1));



        $excel->getActiveSheet()->getStyle('A5:J6')->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('A2:A4')->getFont()->setBold(true);
        // $excel->getActiveSheet()->getStyle('A8:H8')->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('B4')->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('A1:A7')->getAlignment()->setHorizontal('left');
        // $excel->getActiveSheet()->getStyle('A8:H8')->getAlignment()->setHorizontal('center');
        // $excel->getActiveSheet()->getStyle('C6:B' . $cont)->getAlignment()->setHorizontal('center');
        // $excel->getActiveSheet()->getStyle('B6:B' . $cont)->getAlignment()->setHorizontal('center');
        // $excel->getActiveSheet()->getStyle('D6:B' . $cont)->getAlignment()->setHorizontal('center');
        // $excel->getActiveSheet()->getStyle('E6:B' . $cont)->getAlignment()->setHorizontal('center');
        // // $excel->getActiveSheet()->getStyle('A8:H8')->getFill()
        //     ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        //     ->getStartColor()->setARGB('FFE6E6FA');

        //Totales
        $excel->getActiveSheet()->mergeCells('A' . ($cont + 1) . ':K' . ($cont + 1));
        $excel->getActiveSheet()->getStyle('A' . ($cont + 1))->getAlignment()->setHorizontal('center');
        $excel->getActiveSheet()->getStyle('A' . ($cont + 1))->getFont()->setBold(true);
        $sheet->setCellValue('A' . ($cont + 1), 'SUMATORIA');
        $sheet->setCellValue('L' . ($cont + 1), $sumatoria);
        //$excel->getActiveSheet()->getStyle('H'.$cont.':J'.$cont)->getFont()->setBold( true );


        aplicarIdentidadEmpresaExcel($excel, $this->db);
        $excelWriter = new Xlsx($excel);

        $nombreFiltro = count($tiposSeleccionados) ? implode('-', array_map(function ($codigo) use ($nombresComprobante) {
            return str_replace(' ', '_', $nombresComprobante[$codigo]);
        }, $tiposSeleccionados)) : 'TODOS';
        $fileName = 'Reg_Venta_' . $nombreFiltro . '_' . getNow('Y-m-d-H-i-s') . '_' . substr(uniqid(), -6) . '.xlsx';
        $excelFileName = __DIR__ . '/reporte/' . $fileName;
        $excelWriter->save($excelFileName);

        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        return $response->withJson($protocolo . $_SERVER['HTTP_HOST'] . '/api/reporte/' .  $fileName);
    } catch (Exception $e) {
        return $response->withJson(['errorr' => $e->getMessage()], 500);
    }
});

$app->get('/usuario/listado', function (Request $request, Response $response, array $args) {
    try {

        if (session_status() !== PHP_SESSION_ACTIVE) session_start();


        $select = "
        SELECT 
        Seg_Usuario.Usuario,
        Seg_Usuario.Anulado, 
        UPPER(Seg_UsuarioPerfil.UsuarioPerfil) UsuarioPerfil, 
        Seg_Usuario.IdUsuarioPerfil, 
        Seg_Usuario.IdDocVentaPuntoVenta, 
        upper(Seg_Usuario.NombreUsuario) NombreUsuario,
		Lo_Almacen.IdAlmacen, 
        Lo_Almacen.Almacen
		FROM Seg_Usuario
		INNER JOIN Seg_UsuarioPerfil ON Seg_Usuario.IdUsuarioPerfil = Seg_UsuarioPerfil.IdUsuarioPerfil
		LEFT JOIN Lo_Almacen ON Seg_Usuario.IdAlmacen = Lo_Almacen.IdAlmacen
        ORDER BY Seg_Usuario.IdUsuarioPerfil asc ,Seg_Usuario.Usuario asc
        ";


        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        if (strtolower(trim((string) ($_SESSION['user'] ?? ''))) !== 'superadmin') {
            $data = array_values(array_filter($data, function ($row) {
                return strtolower(trim((string) ($row['Usuario'] ?? ''))) !== 'superadmin';
            }));
        }

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], 500);
        //throw $th;
    }
});


$app->get('/puntoventa/listado', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Ve_DocVentaPuntoVenta";


    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->post('/usuario/registro', function (Request $request, Response $response) {
    try {
        // Obtener los parámetros desde getParam()
        $IdAlmacen = $request->getParam('IdAlmacen');
        $IdUsuarioPerfil = $request->getParam('IdUsuarioPerfil');
        $IdDocVentaPuntoVenta = $request->getParam('IdDocVentaPuntoVenta');
        $NombreUsuario = $request->getParam('NombreUsuario');
        $Usuario = $request->getParam('Usuario');
        $clave = $request->getParam('clave');
        $Anulado = $request->getParam('Anulado') ?? 0;

        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (strtolower(trim((string) $Usuario)) === 'superadmin' && strtolower(trim((string) ($_SESSION['user'] ?? ''))) !== 'superadmin') {
            return $response->withJson(['estado' => false, 'msg' => 'La cuenta SUPERADMIN está protegida.'], 403);
        }

        // Validar si el usuario ya existe
        $select = "SELECT * FROM Seg_Usuario WHERE upper(Usuario) = upper(:usuario)";
        $stmt = $this->db->prepare($select);
        $stmt->execute(['usuario' => $Usuario]);
        $data = $stmt->fetch();

        if ($data) {
            return $response->withJson(['estado' => false, 'msg' => 'El Nombre de Usuario ya se encuentra en uso.']);
        }


        $dml = "
            INSERT INTO Seg_Usuario 
            (IdAlmacen, IdUsuarioPerfil, NombreUsuario, Anulado, Usuario, Password,IdDocVentaPuntoVenta)
            VALUES
            (:idAlmacen, :idUsuarioPerfil, :nombreUsuario, :anulado, :usuario, SHA2(:clave, 256),:IdDocVentaPuntoVenta)
        ";

        $stmt = $this->db->prepare($dml);
        $stmt->execute([
            'idAlmacen' => $IdAlmacen,
            'idUsuarioPerfil' => $IdUsuarioPerfil,
            'nombreUsuario' => $NombreUsuario,
            'anulado' => $Anulado,
            'usuario' => $Usuario,
            'clave' => $clave,
            'IdDocVentaPuntoVenta' => $IdDocVentaPuntoVenta
        ]);

        return $response->withJson(['estado' => true, 'msg' => 'Usuario registrado exitosamente.']);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});


$app->get('/listarusuarioperfilmodulo', function (Request $request, Response $response, array $args) {
    try {

        $idUsuarioPerfil = $request->getParam('IdUsuarioPerfil');

        $sql = "
            SELECT 
            Seg_UsuarioModulo.IdUsuarioModulo, 
            Seg_UsuarioModulo.UsuarioModulo, 
            Seg_UsuarioModulo_has_UsuarioPerfil.IdUsuarioPerfil, 
            Seg_UsuarioModulo_has_UsuarioPerfil.Lectura, 
            Seg_UsuarioModulo_has_UsuarioPerfil.Escritura 
            FROM Seg_UsuarioModulo_has_UsuarioPerfil
	        LEFT JOIN Seg_UsuarioModulo ON Seg_UsuarioModulo_has_UsuarioPerfil.IdUsuarioModulo = Seg_UsuarioModulo.IdUsuarioModulo
			WHERE Seg_UsuarioModulo_has_UsuarioPerfil.IdUsuarioPerfil = $idUsuarioPerfil";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception  $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/listarmodulos', function (Request $request, Response $response, array $args) {
    try {

        $idUsuarioPerfil = $request->getParam('IdUsuarioPerfil');

        $sql = "SELECT * FROM Seg_UsuarioModulo ORDER BY IdUsuarioModulo ASC";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception  $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->post('/modulo/registro', function (Request $request, Response $response) {
    try {
        $modulo = $request->getParam('modulo');
        $insert = $this->db->insert(array('UsuarioModulo'))
            ->into('Seg_UsuarioModulo')
            ->values(array($modulo));

        $insertId = $insert->execute();
        return $response->withJson($insertId);
    } catch (Exception  $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->post('/usuarioperfil/registro', function (Request $request, Response $response, array $args) {
    try {
        date_default_timezone_set('America/Lima');
        $UsuarioPerfil = $request->getParam('UsuarioPerfil');
        $lstModulosPerfil = $request->getParam('lstModulosPerfil');
        $fecha          = date("Y-m-d h:i:s");
        $insert = $this->db->insert(array('UsuarioPerfil', 'Anulado', 'FechaReg'))
            ->into('Seg_UsuarioPerfil')
            ->values(array($UsuarioPerfil, 0, $fecha));
        $insertId = $insert->execute();


        foreach ($lstModulosPerfil as $key) {
            $insert = $this->db->insert(array(
                'IdUsuarioModulo',
                'IdUsuarioPerfil',
                'Lectura',
                'Escritura'
            ))
                ->into('Seg_UsuarioModulo_has_UsuarioPerfil')
                ->values(array(
                    $key['IdUsuarioModulo'],
                    $insertId,
                    $key['Lectura'],
                    $key['Escritura'],
                ));
            $insert->execute();
        }

        return $response->withJson($insertId);
    } catch (Exception  $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->post('/usuarioperfil/actualizar', function (Request $request, Response $response, array $args) {
    try {
        date_default_timezone_set('America/Lima');

        // Obtener parámetros del request
        $UsuarioPerfil = $request->getParam('UsuarioPerfil');
        $lstModulosPerfil = $request->getParam('lstModulosPerfil');
        $Anulado = $request->getParam('Anulado');
        $IdUsuarioPerfil = $request->getParam('IdUsuarioPerfil'); // Definir la variable
        $fecha = date("Y-m-d H:i:s"); // Corrección en el formato 24h

        // Actualizar Seg_UsuarioPerfil
        $update = $this->db->update([
            'UsuarioPerfil' => $UsuarioPerfil,
            'FechaMod' => $fecha,
            'Anulado' => $Anulado,
        ])
            ->table('Seg_UsuarioPerfil')
            ->where('IdUsuarioPerfil', '=', $IdUsuarioPerfil);

        $update->execute(); // Ejecutar la consulta correctamente

        // Actualizar Seg_UsuarioModulo_has_UsuarioPerfil
        foreach ($lstModulosPerfil as $key) {

            $sql = "SELECT * FROM Seg_UsuarioModulo_has_UsuarioPerfil where 
                    IdUsuarioModulo =" . $key['IdUsuarioModulo'] . " AND
                    IdUsuarioPerfil = $IdUsuarioPerfil
            ";
            $stmt = $this->db->query($sql);
            $stmt->execute();
            $data = $stmt->fetch();
            if (!$data) {
                $insert = $this->db->insert(array(
                    'IdUsuarioModulo',
                    'IdUsuarioPerfil',
                    'Lectura',
                    'Escritura'
                ))
                    ->into('Seg_UsuarioModulo_has_UsuarioPerfil')
                    ->values(array(
                        $key['IdUsuarioModulo'],
                        $IdUsuarioPerfil,
                        $key['Lectura'],
                        $key['Escritura'],
                    ));
                $insert->execute();
            } else {
                $IdUsuarioModulo = $key['IdUsuarioModulo'] ?? null;
                $IdUsuarioPerfil = $key['IdUsuarioPerfil'] ?? null;

                if ($IdUsuarioModulo && $IdUsuarioPerfil) { // Evitar errores con valores nulos
                    $update = $this->db->update([
                        'Lectura' => $key['Lectura'],
                        'Escritura' => $key['Escritura'],
                    ])
                        ->table('Seg_UsuarioModulo_has_UsuarioPerfil')
                        ->where('IdUsuarioModulo', '=', $IdUsuarioModulo)
                        ->where('IdUsuarioPerfil', '=', $IdUsuarioPerfil);

                    $update->execute();
                }
            }
        }

        return $response->withJson(['success' => true, 'message' => 'Actualización realizada correctamente']);
    } catch (Exception  $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});


$app->post('/usuarioperfil/eliminar', function (Request $request, Response $response, array $args) {
    try {
        date_default_timezone_set('America/Lima');

        // Obtener parámetros del request
        $UsuarioPerfil = $request->getParam('UsuarioPerfil');
        $lstModulosPerfil = $request->getParam('lstModulosPerfil');
        $IdUsuarioPerfil = $request->getParam('IdUsuarioPerfil'); // Definir la variable
        $fecha = date("Y-m-d H:i:s"); // Corrección en el formato 24h

        // Actualizar Seg_UsuarioPerfil
        $update = $this->db->update([
            'Anulado' => 1,
            'FechaMod' => $fecha
        ])
            ->table('Seg_UsuarioPerfil')
            ->where('IdUsuarioPerfil', '=', $IdUsuarioPerfil);

        $update->execute();

        return $response->withJson(['success' => true, 'message' => 'Actualización realizada correctamente']);
    } catch (Exception  $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/perfil/listado', function (Request $request, Response $response, array $args) {
    $select = "
        SELECT * FROM Seg_UsuarioPerfil
        ORDER BY COALESCE(Seg_UsuarioPerfil.Anulado,0) ASC
        ";


    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/cuentas/listarcuentas', function (Request $request, Response $response, array $args) {
    try {
        $select = "SELECT * FROM Cb_Cuenta";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/ListarTipoOpe', function (Request $request, Response $response, array $args) {
    try {
        $select = "SELECT IdTipoCajaBanco, TipoCajaBanco, Tipo FROM Cb_TipoCajaBanco;";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});


$app->get('/reporte/listarregmov', function (Request $request, Response $response, array $args) {
    try {

        $fechaIni = $request->getParam('fechaInicio');
        $fechaFin = $request->getParam('fechaFin');
        $declarado = $request->getParam('declarado') ? $request->getParam('declarado') : 0;
        $descripcion = $request->getParam('descripcion');

        $select = "SELECT
                Lo_Movimiento.`Hash` as IdMovimiento,
                Lo_Movimiento.MovimientoFecha,
                Lo_Movimiento.IdMovimientoTipo,
                Lo_MovimientoTipo.Tipo,
                Lo_MovimientoTipo.TipoMovimiento,
                Lo_Movimiento.Serie,
                Lo_Movimiento.Numero,
                Lo_Movimiento.FechaPeriodoTributario,
                Lo_Proveedor.Proveedor,
                CASE
                    WHEN Lo_Movimiento.IdAlmacenOrigen>0 THEN (
                    Select
                        Lo_Almacen.Almacen
                    From
                        Lo_Almacen
                    Where
                        Lo_Almacen.IdAlmacen = Lo_Movimiento.IdAlmacenOrigen)
                    ELSE '-'
                END AS AlmacenOrigen,
                CASE
                    WHEN Lo_Movimiento.IdAlmacenDestino>0 THEN (
                    Select
                        Lo_Almacen.Almacen
                    From
                        Lo_Almacen
                    Where
                        Lo_Almacen.IdAlmacen = Lo_Movimiento.IdAlmacenDestino)
                    ELSE '-'
                END AS AlmacenDestino,
                Lo_Movimiento.Observacion,
                Lo_Movimiento.Anulado,
                (
                SELECT
                    Sum(ROUND(Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio, 2)) as SUBTOTAL
                FROM
                    Lo_MovimientoDetalle
                WHERE
                    Lo_MovimientoDetalle.hashMovimiento = Lo_Movimiento.`Hash`) as SUBTOTAL,
                (
                SELECT
                    Sum(CASE WHEN Lo_MovimientoDetalle.TieneIgv = 1 THEN ROUND((Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio)* (Select Igv From GEN_EMPRESA), 2) ELSE 0 END) as IGV
                FROM
                    Lo_MovimientoDetalle
                WHERE
                    Lo_MovimientoDetalle.hashMovimiento = Lo_Movimiento.`Hash`) as IGV,
                (
                SELECT
                    Sum(ROUND(Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio, 2)) + Sum(ROUND(CASE WHEN ISNULL(Lo_MovimientoDetalle.ISC)= 1 THEN 0 ELSE Lo_MovimientoDetalle.ISC END, 2)) + SUM(ROUND(CASE WHEN ISNULL(Lo_MovimientoDetalle.FLETE)= 1 THEN 0 ELSE Lo_MovimientoDetalle.FLETE END, 2))
                    + CASE
                        WHEN ISNULL(Lo_Movimiento.Percepcion)= 1 THEN 0
                        ELSE Lo_Movimiento.Percepcion
                    END + Sum(CASE WHEN Lo_MovimientoDetalle.TieneIgv = 1 THEN ROUND((Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio)* (Select Igv From GEN_EMPRESA), 2) ELSE 0 END) as TOTAL
                FROM
                    Lo_MovimientoDetalle
                WHERE
                    Lo_MovimientoDetalle.hashMovimiento = Lo_Movimiento.`Hash`) as TOTAL,
                Lo_Movimiento.FechaReg,
                Lo_Movimiento.UsuarioReg,
                Lo_Movimiento.FechaMod,
                Lo_Movimiento.UsuarioMod,
                (
                SELECT
                    SUM(ROUND(CASE WHEN ISNULL(Lo_MovimientoDetalle.ISC)= 1 THEN 0 ELSE Lo_MovimientoDetalle.ISC END, 2))
                FROM
                    Lo_MovimientoDetalle
                WHERE
                    Lo_MovimientoDetalle.hashMovimiento = Lo_Movimiento.`Hash`) as ISC,
                (
                SELECT
                    SUM(ROUND(CASE WHEN ISNULL(Lo_MovimientoDetalle.FLETE)= 1 THEN 0 ELSE Lo_MovimientoDetalle.FLETE END, 2))
                FROM
                    Lo_MovimientoDetalle
                WHERE
                    Lo_MovimientoDetalle.hashMovimiento = Lo_Movimiento.`Hash`) as FLETE,
                CASE
                    WHEN ISNULL(Lo_Movimiento.Percepcion)= 1 THEN 0
                    ELSE Lo_Movimiento.Percepcion
                END AS Percepcion,
                Lo_Movimiento.TipoCambio,
                Lo_Movimiento.Moneda
            FROM
                Lo_Movimiento
            INNER JOIN Lo_MovimientoTipo On
                Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
            INNER JOIN Lo_Proveedor On
                Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
            WHERE
                Lo_MovimientoTipo.VaRegCompra = $declarado
                AND DATE(Lo_Movimiento.MovimientoFecha) BETWEEN '$fechaIni' and  '$fechaFin'
               
           ";
        if (!empty($descripcion)) {
            $select .= " AND Lo_Movimiento.Hash IN (
            SELECT DISTINCT(Lo_MovimientoDetalle.hashMovimiento)
            FROM Lo_MovimientoDetalle
            WHERE Lo_MovimientoDetalle.Descripcion LIKE CONCAT('%', '$descripcion', '%')
             )";
        }

        $select .= " ORDER BY Lo_Movimiento.MovimientoFecha DESC;";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $th) {
        return $response->withJson(['error' => $th->getMessage()], 500);
    }
});

$app->get('/movmiento/detalles', function (Request $request, Response $response, array $args) {
    $IdMovimiento = $request->getParam('IdMovimiento');
    try {
        $select = "Select Lo_MovimientoDetalle.hashMovimiento, Lo_MovimientoDetalle.Descripcion, Gen_Producto.Codigo, Gen_Producto.CodigoBarra, 
		Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica, Gen_Producto.Producto, 
		Gen_Producto.PrecioContado, Gen_ProductoMedicion.ProductoMedicion, Lo_MovimientoDetalle.Cantidad,
		Lo_MovimientoDetalle.TieneIgv, Lo_MovimientoDetalle.Precio, Gen_ProductoMarca.ProductoMarca, Lo_MovimientoDetalle.ISC,
        ProductoModelo,ProductoMarca,ProductoTalla,Color,Genero,Botapie
		   FROM Lo_MovimientoDetalle
		   INNER JOIN Gen_Producto on Gen_Producto.IdProducto = Lo_MovimientoDetalle.IdProducto
		   INNER JOIN Gen_ProductoFormaFarmaceutica ON Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica = Gen_Producto.IdProductoFormaFarmaceutica
		   INNER JOIN Gen_ProductoMedicion ON Gen_ProductoMedicion.IdProductoMedicion = Gen_Producto.IdProductoMedicion
		   INNER JOIN Gen_ProductoMarca ON Gen_ProductoMarca.IdProductoMarca = Gen_Producto.IdProductoMarca
		   INNER JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla
           WHERE Lo_MovimientoDetalle.hashMovimiento='$IdMovimiento'";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->post('/movmiento/actualizarperiodo', function (Request $request, Response $response, array $args) {
    try {

        $FechaPeriodoTributario = $request->getParam('FechaPeriodoTributario');
        $IdMovimiento = $request->getParam('IdMovimiento');
        $update = "UPDATE Lo_Movimiento SET FechaPeriodoTributario='$FechaPeriodoTributario'
               WHERE Hash='$IdMovimiento'";
        $stmt = $this->db->prepare($update);
        $updated = $stmt->execute();

        return $response->withJson(array(
            "updated" => $updated,
            "IdProducto" => $IdMovimiento
        ));
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], 500);
    }
});

$app->post('/movimiento/eliminar', function (Request $request, Response $response, array $args) {
    try {
        $idMov = $request->getParam('idMov');

        if (empty($idMov)) {
            throw new Exception("El parámetro 'idMov' es obligatorio.");
        }

        $sql = "SELECT 
                    IdAlmacenOrigen, 
                    IdAlmacenDestino, 
                    IdMovimientoTipo 
                FROM Lo_Movimiento 
                WHERE Hash = :idMov";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':idMov', $idMov);
        $stmt->execute();
        $movimiento = $stmt->fetch();

        if (!$movimiento) {
            throw new Exception("Movimiento no encontrado.");
        }

        $idOrigen = $movimiento['IdAlmacenOrigen'];
        $idDestino = $movimiento['IdAlmacenDestino'];

        // 2. Obtenemos los productos del detalle
        $sql = "SELECT IdProducto, Cantidad FROM Lo_MovimientoDetalle WHERE hashMovimiento = :idMov";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':idMov', $idMov);
        $stmt->execute();
        $detalles = $stmt->fetchAll();

        foreach ($detalles as $detalle) {
            $cant = $detalle['Cantidad'];
            $idProd = $detalle['IdProducto'];

            // LÓGICA DE TRASLADO: Si tiene origen y destino
            if ($idOrigen > 0 && $idDestino > 0) {
                // Revertir Origen: Al anular, devolvemos el stock al origen (+)
                $updSource = "UPDATE Gen_Producto_Totales SET TotalCompras = TotalCompras + $cant 
                              WHERE IdProducto = $idProd AND IdAlmacen = $idOrigen";
                $this->db->prepare($updSource)->execute();

                // Revertir Destino: Al anular, quitamos el stock que llegó al destino (-)
                $updDest = "UPDATE Gen_Producto_Totales SET TotalCompras = TotalCompras - $cant 
                            WHERE IdProducto = $idProd AND IdAlmacen = $idDestino";
                $this->db->prepare($updDest)->execute();
            } 
            // LÓGICA DE INGRESO SIMPLE (Solo Destino)
            else if ($idDestino > 0) {
                $update = "UPDATE Gen_Producto_Totales SET TotalCompras = TotalCompras - $cant 
                           WHERE IdProducto = $idProd AND IdAlmacen = $idDestino";
                $this->db->prepare($update)->execute();
            }
            // LÓGICA DE SALIDA SIMPLE (Solo Origen)
            else if ($idOrigen > 0) {
                $update = "UPDATE Gen_Producto_Totales SET TotalCompras = TotalCompras + $cant 
                           WHERE IdProducto = $idProd AND IdAlmacen = $idOrigen";
                $this->db->prepare($update)->execute();
            }
        }

        $this->db->prepare("CREATE TABLE IF NOT EXISTS Lo_OrdenCompraMovimiento (
            IdOrdenCompra INT NOT NULL,
            HashMovimiento VARCHAR(80) NOT NULL,
            FechaReg DATETIME NOT NULL,
            PRIMARY KEY (IdOrdenCompra),
            UNIQUE KEY UX_OrdenCompraMovimiento_Hash (HashMovimiento)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8")->execute();
        $ordenStmt = $this->db->prepare("SELECT IdOrdenCompra FROM Lo_OrdenCompraMovimiento WHERE HashMovimiento = :idMov");
        $ordenStmt->execute(array('idMov' => $idMov));
        $ordenCompraVinculada = $ordenStmt->fetch();

        $this->db->prepare("DELETE FROM Lo_MovimientoDetalle WHERE hashMovimiento = :idMov")->execute(['idMov' => $idMov]);
        $this->db->prepare("DELETE FROM Lo_Movimiento WHERE Hash = :idMov")->execute(['idMov' => $idMov]);

        if ($ordenCompraVinculada) {
            $this->db->prepare("DELETE FROM Lo_OrdenCompraMovimiento WHERE HashMovimiento = :idMov")
                ->execute(array('idMov' => $idMov));
            $this->db->prepare("UPDATE Lo_OrdenCompra SET Estado = 0 WHERE IdOrdenCompra = :idOrdenCompra AND Estado = 2")
                ->execute(array('idOrdenCompra' => $ordenCompraVinculada['IdOrdenCompra']));
        }

        return $response->withJson(array(
            'success' => true,
            'ordenCompraReactivada' => $ordenCompraVinculada ? intval($ordenCompraVinculada['IdOrdenCompra']) : null
        ));
    } catch (Exception $e) {
        // Registra el error en los logs
        error_log("Error en /movimiento/eliminar: " . $e->getMessage());

        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/ListarCajaBanco', function (Request $request, Response $response, array $args) {
    try {
        $IdCuenta = $request->getParam('IdCuenta');
        $FechaDoc = $request->getParam('FechaDoc');
        $select = "SELECT
                    CB.IdCajaBanco,
                    CB.UsuarioReg,
                    CB.Concepto,
                    CASE TCB.Tipo
                        WHEN 1 THEN 0
                        ELSE CB.Importe
                    END as 'Ingresos',
                    CASE TCB.Tipo
                        WHEN 1 THEN CB.Importe
                        ELSE 0
                    END as 'Salida'
                    FROM Cb_CajaBanco as CB
                    INNER JOIN Cb_TipoCajaBanco as TCB
                    ON CB.IdTipoCajaBanco = TCB.IdTipoCajaBanco
                    WHERE CB.IdCuenta = $IdCuenta AND DATE(CB.Fechadoc) = '$FechaDoc'
                    ORDER BY TCB.Tipo,CB.IdCajaBanco;";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});


$app->get('/proveedor', function (Request $request, Response $response, array $args) {
    try {


        $idProveedor = $request->getParam('idProveedor');
        $ruc = $request->getParam('ruc');
        $select = "SELECT 
                    IFNULL(CONCAT(Ruc, ' - ', Proveedor, ' - ', IFNULL(Proveedor, '')), '-') 
                    AS ProveedorDniRuc , Lo_Proveedor.* FROM Lo_Proveedor WHERE TRUE ";


        if ($ruc) {
            $select .= " AND Ruc='$ruc'";
        } else if ($idProveedor) {
            $select = " AND  IdProveedor='$idProveedor'";
        } else if ($request->getParam('q')) {
            $select .= " OR Lo_Proveedor.Ruc LIKE '%" . $request->getParam('q') . "%')  ";
            $select .= " OR Lo_Proveedor.Proveedor LIKE '%" . $request->getParam('q') . "%')  ";
        }
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception  $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/proveedores/deudas/letras', function (Request $request, Response $response, array $args) {
    $idProveedor = $request->getParam('idProveedor');
    $select = "(SELECT
    Lo_Movimiento.Hash AS Id,
    Lo_Movimiento.MovimientoFecha AS FechaDoc,
    DATE(Lo_Movimiento.FechaVenCredito) AS FechaCredito,
    CONCAT(CASE WHEN Lo_MovimientoTipo.CodSunat ='01' THEN
        'F'
    ELSE
        CASE WHEN Lo_MovimientoTipo.CodSunat='03' THEN
            'B'
        ELSE
            CASE WHEN Lo_MovimientoTipo.CodSunat='12' THEN
                'T'
            ELSE
                'OTRO'
            END
        END
    END,Lo_Movimiento.Serie,'-' , CONVERT(Lo_Movimiento.Numero, CHAR) )AS Correlativo,
    ROUND(SUM(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) 
    + CASE WHEN Lo_MovimientoDetalle.TieneIgv THEN Round(Sum(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) *0.18  else 0 end
    AS Total,
    ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='MO' AND Cb_CajaBancoDet.Hash=Lo_Movimiento.Hash),0)),2) AS Aplicado,
    ROUND(SUM(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) 
    + CASE WHEN Lo_MovimientoDetalle.TieneIgv THEN Round(Sum(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) *0.18  else 0 end
    - ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='MO' AND Cb_CajaBancoDet.Hash=Lo_Movimiento.Hash),0)),2) AS Saldo
    FROM Lo_Movimiento
    INNER JOIN Lo_MovimientoDetalle ON Lo_Movimiento.Hash=Lo_MovimientoDetalle.hashMovimiento
    INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo=Lo_MovimientoTipo.IdMovimientoTipo
    WHERE EsCredito=1 AND Lo_Movimiento.IdProveedor=$idProveedor
    GROUP BY
    Lo_Movimiento.Hash,
    Lo_Movimiento.MovimientoFecha,
    Lo_Movimiento.FechaVenCredito,
    Lo_MovimientoTipo.CodSunat,
    Lo_Movimiento.Serie,
    Lo_Movimiento.Numero
    HAVING Saldo >0 AND (SELECT COUNT(*) FROM Cb_LetraCaja WHERE Cb_LetraCaja.Tipo='MO' AND Cb_LetraCaja.IdDocDet=Lo_Movimiento.Hash)=0
    ORDER BY MovimientoFecha)
    
    UNION 
    
    (SELECT CONCAT(Cb_LetraCaja.IdDocDet,'-',Cb_LetraCaja.IdLetraCaja) AS Id,
    Cb_LetraCaja.FechaReg AS FechaDoc,
    Cb_LetraCaja.FechaPago AS FechaCredito,
    CONCAT(CASE WHEN Lo_MovimientoTipo.CodSunat ='01' THEN
        'F'
    ELSE
        CASE WHEN Lo_MovimientoTipo.CodSunat='03' THEN
            'B'
        ELSE
            CASE WHEN Lo_MovimientoTipo.CodSunat='12' THEN
                'T'
            ELSE
                'OTRO'
            END
        END
    END,Lo_Movimiento.Serie,'-',Lo_Movimiento.Numero,' (Letra N°',Cb_LetraCaja.NumeroLetra,')') AS Correlativo,
    Cb_LetraCaja.Importe AS Total,
    Cb_LetraCaja.Tipo AS Aplicado,
    Cb_LetraCaja.Cancelado AS Saldo
    FROM Cb_LetraCaja
    INNER JOIN Lo_Movimiento ON Cb_LetraCaja.IdDocDet=Lo_Movimiento.Hash
    INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo=Lo_MovimientoTipo.IdMovimientoTipo
    WHERE Lo_Movimiento.IdProveedor=$idProveedor AND Cb_LetraCaja.Cancelado=0 ORDER BY Cb_LetraCaja.FechaPago DESC)";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson(array(
        "sEcho" => 1,
        "iTotalRecords" => count($data),
        "iTotalDisplayRecords" => count($data),
        "aaData" => $data
    ));
});

$app->post('/movimiento/guardarMovimiento', function (Request $request, Response $response, array $args) {
    // return $response->withJson('glaaaa');
    try {
        $idTipo = $request->getParam('IdTipoCajaBanco');
        $idCuenta = $request->getParam('IdCuenta');
        $fechaDoc = $request->getParam('FechaDoc');
        $concepto = $request->getParam('Concepto');
        $importe = $request->getParam('Importe');
        $tipo = $request->getParam('TipoCajaBanco');
        $aplicadoDocVenta = $request->getParam('AplicadoDocVenta');
        $idCliente = $request->getParam('IdCliente') ??  0;
        $idProveedor = $request->getParam('IdProveedor') ??  0;
        $usuarioReg = 'xx';
        $hora = date_create('now', timezone_open('America/Lima'))->format('H:i:s');
        $fechaFin = $fechaDoc . " " . $hora;
        if (isset($_SESSION['user'])) {
            $usuarioReg = $_SESSION['user'];
        }
        $usuarioReg = $request->getParam('User') ? $request->getParam('User') : $vendedor;
        $Ssql = "INSERT INTO Cb_CajaBanco (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, IdProveedor, IdCliente, UsuarioReg) 
                VALUES ($idTipo, $idCuenta, '$fechaFin', '$concepto', $importe, $idProveedor, $idCliente, '$usuarioReg')";

        $stmt = $this->db->prepare($Ssql);
        $inserted = $stmt->execute();
        $idCajaBanco = $this->db->lastInsertId();

        if ($idCajaBanco && $aplicadoDocVenta) {
            foreach ($aplicadoDocVenta as $key => $value) {
                if ($tipo == 1) {
                    $Ssql = "INSERT INTO Cb_CajaBancoDet (IdCajaBanco, IdDocDet, Importe, Hash, Tipo) 
                    VALUES($idCajaBanco, " . $value['Id'] . ", " . $value['Aplicado'] . ", " . $value['Id'] . ", 'MO')";
                    $stmt = $this->db->prepare($Ssql);
                    $stmt->execute();
                } else {
                    $Ssql = "INSERT INTO Cb_CajaBancoDet (IdCajaBanco, IdDocDet, Importe, Tipo) 
                    VALUES($idCajaBanco, " . $value['Id'] . ", " . $value['Aplicado'] . ", 'VE')";
                    $stmt = $this->db->prepare($Ssql);
                    $stmt->execute();
                }
            }
        }
        return $response->withJson($idCajaBanco);
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], 500);
    }
});

$app->post('/letras/generar', function (Request $request, Response $response) {
    $letras = $request->getParam('Letras');
    $tipo = $request->getParam('Tipo');

    try {
        foreach ($letras as $key => $value) {
            if ($tipo == 1) {
                $insertDet = $this->db->insert(array('IdDocDet', 'NumeroLetra', 'Hash', 'Importe', 'FechaReg', 'FechaPago', 'Tipo', 'Cancelado'))
                    ->into('Cb_LetraCaja')
                    ->values(array($value['IdDocDet'], ($key + 1), $value['IdDocDet'], $value['MontoLetra'], getNow(), $value['FechaLetra'], 'MO', 0));
                $insertDet->execute();
            } else {
                $insertDet = $this->db->insert(array('IdDocDet', 'NumeroLetra', 'Importe', 'FechaReg', 'FechaPago', 'Tipo', 'Cancelado'))
                    ->into('Cb_LetraCaja')
                    ->values(array($value['IdDocDet'], ($key + 1), $value['MontoLetra'], getNow(), $value['FechaLetra'], 'VE', 0));
                $insertDet->execute();
            }
        }
        return $response->withJson(true);
    } catch (Exception $e) {
        return $response->withJson($e);
    }


    //return $response->withJson(array("Letras"=>$letras,"Tipo"=>$tipo));
});
$app->post('/letras/pagar', function (Request $request, Response $response) {
    $idLetraCaja = $request->getParam('IdLetraCaja');
    $idDocDet = $request->getParam('IdDocDet');
    $idCuenta = $request->getParam('IdCuenta');
    $concepto =  $request->getParam('Concepto');
    $importe = $request->getParam('Importe');
    $idTipoCajaBanco = $request->getParam('IdTipoCajaBanco');
    $tipo = $request->getParam('Tipo');
    $idCliente = $request->getParam('idCliente') ?? 0;
    $idProveedor = $request->getParam('idProveedor') ?? 0;

    $vendedor = 'xx';
    if (isset($_SESSION['user'])) {
        $vendedor = $_SESSION['user'];
    }
    $vendedor = $request->getParam('User') ? $request->getParam('User') : $vendedor;
    try {
        $insert = $this->db->insert(array('IdTipoCajaBanco', 'IdCuenta', 'FechaDoc', 'Concepto', 'Importe', 'Anulado', 'UsuarioReg', 'IdProveedor', 'IdCliente'))
            ->into('Cb_CajaBanco')
            ->values(array($idTipoCajaBanco, $idCuenta, getNow(), $concepto, $importe, 0, $vendedor, $idProveedor, $idCliente));

        $insertId = $insert->execute();

        if ($tipo == 1) {
            $insertDet = $this->db->insert(array('IdCajaBanco', 'IdDocDet', 'Importe', 'Hash', 'Tipo'))
                ->into('Cb_CajaBancoDet')
                ->values(array($insertId, $idDocDet, $importe, $idDocDet, 'MO'));
            $insertDet->execute();
        } else {
            $insertDet = $this->db->insert(array('IdCajaBanco', 'IdDocDet', 'Importe', 'Tipo'))
                ->into('Cb_CajaBancoDet')
                ->values(array($insertId, $idDocDet, $importe, 'VE'));
            $insertDet->execute();
        }

        $update = $this->db->update(array(
            "IdCajaBanco" => $insertId,
            "Cancelado" => 1
        ))
            ->table('Cb_LetraCaja')
            ->where('IdLetraCaja', '=', $idLetraCaja);
        $update->execute();
        return $response->withJson(array(
            "idCajaBanco" => $insertId
        ));
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/clientes/deudas/letras', function (Request $request, Response $response, array $args) {
    $idCliente = $request->getParam('idCliente');
    $select = "(SELECT
    Ve_DocVenta.IdDocVenta,
    Ve_DocVenta.FechaDoc,
    Ve_DocVenta.FechaCredito,
    CONCAT(CASE WHEN Ve_DocVentaTipoDoc.CodSunat ='01' THEN
        'F'
    ELSE
        CASE WHEN Ve_DocVentaTipoDoc.CodSunat='03' THEN
            'B'
        ELSE
            CASE WHEN Ve_DocVentaTipoDoc.CodSunat='12' THEN
                'T'
            ELSE
                'OTRO'
            END
        END
    END,Ve_DocVenta.Serie,'-' , CONVERT(Ve_DocVenta.Numero, CHAR) )AS Correlativo,
    ROUND(SUM(Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad),2) AS Total,
    ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='VE' AND Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)),2) AS Aplicado,
    ROUND(SUM(Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad),2) -
    ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='VE' AND Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)),2) AS Saldo
    FROM Ve_DocVenta
    INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.IdDocVenta=Ve_DocVentaDet.IdDocVenta
    INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc=Ve_DocVentaTipoDoc.IdTipoDoc
    WHERE EsCredito=1 AND Ve_DocVenta.IdCliente=$idCliente
    GROUP BY
    Ve_DocVenta.IdDocVenta,
    Ve_DocVenta.FechaDoc,
    Ve_DocVenta.FechaCredito,
    Ve_DocVentaTipoDoc.CodSunat,
    Ve_DocVenta.Serie,
    Ve_DocVenta.Numero
    HAVING Saldo >0 AND (SELECT COUNT(*) FROM Cb_LetraCaja WHERE Cb_LetraCaja.Tipo='VE' AND Cb_LetraCaja.IdDocDet=Ve_DocVenta.idDocVenta)=0
    ORDER BY FechaDoc)
    
    UNION 
    
    (SELECT CONCAT(Cb_LetraCaja.IdDocDet,'-',Cb_LetraCaja.IdLetraCaja) AS IdDocVenta,
    Cb_LetraCaja.FechaReg AS FechaDoc,
    Cb_LetraCaja.FechaPago AS FechaCredito,
    CONCAT(CASE WHEN Ve_DocVentaTipoDoc.CodSunat ='01' THEN
        'F'
    ELSE
        CASE WHEN Ve_DocVentaTipoDoc.CodSunat='03' THEN
            'B'
        ELSE
            CASE WHEN Ve_DocVentaTipoDoc.CodSunat='12' THEN
                'T'
            ELSE
                'OTRO'
            END
        END
    END,Ve_DocVenta.Serie,'-' ,Ve_DocVenta.Numero,' (Letra N°',Cb_LetraCaja.NumeroLetra,')') AS Correlativo,
    Cb_LetraCaja.Importe AS Total,
    Cb_LetraCaja.Tipo AS Aplicado,
    Cb_LetraCaja.Cancelado AS Saldo
    FROM Cb_LetraCaja
    INNER JOIN Ve_DocVenta ON Cb_LetraCaja.IdDocDet=Ve_DocVenta.idDocVenta
    INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc=Ve_DocVentaTipoDoc.IdTipoDoc
    WHERE Ve_DocVenta.IdCliente=$idCliente AND Cb_LetraCaja.Cancelado=0 ORDER BY Cb_LetraCaja.FechaPago DESC)";


    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson(array(
        "sEcho" => 1,
        "iTotalRecords" => count($data),
        "iTotalDisplayRecords" => count($data),
        "aaData" => $data
    ));
});

$app->post('/usuario/actualizar', function (Request $request, Response $response) {

    try {


        $IdAlmacen = $request->getParam('IdAlmacen');
        $IdUsuarioPerfil = $request->getParam('IdUsuarioPerfil');
        $NombreUsuario = $request->getParam('NombreUsuario');
        $IdDocVentaPuntoVenta = $request->getParam('IdDocVentaPuntoVenta');
        $Usuario = $request->getParam('Usuario');
        $actualizarClave = $request->getParam('actualizarClave');
        $clave = $request->getParam('clave');
        $Anulado = $request->getParam('Anulado');

        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $usuarioSesion = strtolower(trim((string) ($_SESSION['user'] ?? '')));
        if (strtolower(trim((string) $Usuario)) === 'superadmin') {
            if ($usuarioSesion !== 'superadmin') {
                return $response->withJson(['error' => 'Solo SUPERADMIN puede cambiar su contraseña.'], 403);
            }
            if (!$actualizarClave || trim((string) $clave) === '') {
                return $response->withJson(['error' => 'Ingrese una nueva contraseña para SUPERADMIN.'], 422);
            }
            $stmt = $this->db->prepare("UPDATE Seg_Usuario SET Password=SHA2(:clave, 256) WHERE LOWER(Usuario)='superadmin'");
            $updated = $stmt->execute(['clave' => $clave]);
            return $response->withJson($updated);
        }


        $dml = "UPDATE Seg_Usuario 
            SET
            IdAlmacen= $IdAlmacen,
            IdUsuarioPerfil= $IdUsuarioPerfil,
            IdDocVentaPuntoVenta= $IdDocVentaPuntoVenta,
            NombreUsuario= '$NombreUsuario',
            Anulado= $Anulado
            ";
        if ($actualizarClave == true) {
            $dml .= " , Password= SHA2('$clave', 256)";
        }

        $dml .= " WHERE Usuario = '$Usuario'";
        $stmt = $this->db->prepare($dml);
        $updated = $stmt->execute();
        return $response->withJson($updated);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/ventas/estadistica/dashbora/dia', function (Request $request, Response $response) {
    $this->db->query("SET lc_time_names = 'es_ES'");        
    $fechaInicio = $request->getParam('fechaInicio');
    $fechaFin = $request->getParam('fechaFin');
    $idAlmacen = $request->getParam('idAlmacen');
    $select = "SELECT YEAR(Ve_DocVenta.FechaDoc) as Anio, MONTH(Ve_DocVenta.FechaDoc) as Mes,DAY(Ve_DocVenta.FechaDoc) as Dia, DAYNAME(Ve_DocVenta.FechaDoc) as DiaNombre, SUM( IF(Ve_DocVentaTipoDoc.CodSunat='07',-1,1) * ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2) ) AS Total FROM Ve_DocVenta
    INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta = Ve_DocVentaDet.IdDocVenta
    INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
    WHERE " . ($idAlmacen != 0 && $idAlmacen != null ? " Ve_DocVenta.IdAlmacen = $idAlmacen AND " : "") . " 
        DATE(Ve_DocVenta.FechaReg) BETWEEN '$fechaInicio' AND '$fechaFin'
    AND Ve_DocVenta.Anulado = 0 AND Ve_DocVentaTipoDoc.CodSunat NOT IN ('07')
    GROUP BY DAY(Ve_DocVenta.FechaDoc)
    ORDER BY Ve_DocVenta.FechaDoc ASC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/reporte/rankingvendedor', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $fechaInicio = $request->getParam('fechaInicio');
        $fechaFin = $request->getParam('fechaFin');
        $idAlmacen = $request->getParam('almacen');
        $filtroAlmacen = ($idAlmacen != 0 && $idAlmacen != null) ? " AND Ve_DocVenta.IdAlmacen = $idAlmacen " : "";
        
        $sql = "SELECT 
                SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) totalventa, 
                Seg_Usuario.NombreUsuario 
                FROM Ve_DocVenta 
                JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta = Ve_DocVentaDet.IdDocVenta
                JOIN Seg_Usuario ON Ve_DocVenta.UsuarioReg = Seg_Usuario.Usuario
                WHERE Ve_DocVenta.FechaReg BETWEEN '$fechaInicio' AND '$fechaFin' $filtroAlmacen AND Ve_DocVenta.Anulado = 0
                GROUP BY Seg_Usuario.NombreUsuario
                ORDER BY totalventa DESC";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});
$app->get('/reporte/productomasvendidos', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $fechaInicio = $request->getParam('fechaInicio');
        $fechaFin = $request->getParam('fechaFin');
        $idAlmacen = $request->getParam('almacen');
        $filtroAlmacen = ($idAlmacen != 0 && $idAlmacen != null) ? " AND Ve_DocVenta.IdAlmacen = $idAlmacen " : "";

        $sql = "SELECT 
                SUM(Ve_DocVentaDet.Cantidad) TotalVentas,
                CONCAT(SUBSTRING(TRIM(Gen_Producto.Producto),1,15),'...') Producto
                FROM Ve_DocVentaDet
                JOIN Ve_DocVenta on Ve_DocVenta.idDocVenta = Ve_DocVentaDet.IdDocVenta
                JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
                 WHERE Ve_DocVenta.FechaReg BETWEEN '$fechaInicio' AND '$fechaFin'  AND Ve_DocVenta.Anulado = 0 $filtroAlmacen
                GROUP BY Gen_Producto.Producto
                ORDER BY TotalVentas DESC 
                LIMIT 12";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/reporte/totalcompraporproveedor', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $fechaInicio = $request->getParam('fechaInicio');
        $fechaFin = $request->getParam('fechaFin');
        $sql = "SELECT 
                Lo_Proveedor.Proveedor,
                SUM(Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio) totalCompra
                FROM 
                Lo_Movimiento 
                JOIN Lo_Proveedor ON Lo_Proveedor.IdProveedor = Lo_Movimiento.IdProveedor 
                JOIN Lo_MovimientoDetalle ON Lo_Movimiento.Hash =  Lo_MovimientoDetalle.hashMovimiento
                 WHERE Lo_Movimiento.FechaReg BETWEEN '$fechaInicio' AND '$fechaFin'  
                GROUP BY Lo_Proveedor.Proveedor";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
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


$app->get('/exportar/ventas/zip', function (Request $request, Response $response, array $args) use ($app) {
    try {
        // ======================
        // PARÁMETROS
        // ======================
        $fechaIni   = $request->getParam('fechaIni');
        $fechaFin   = $request->getParam('fechaFin');
        $declarado  = $request->getParam('declarado') ?: 0;

        // ======================
        // DATOS DE LA EMPRESA
        // ======================
        $rucEmpresa    = NRO_DOCUMENTO_EMPRESA;   // ejemplo: 20613991001
        $nombreEmpresa = RAZON_SOCIAL_EMPRESA;

        // ======================
        // RUTA BASE
        // ======================
        $basePath = dirname(__FILE__) . "/sunat/api_cpe/PRODUCCION/{$rucEmpresa}/";
        if (!is_dir($basePath)) mkdir($basePath, 0777, true);

        // Limpiar archivos previos
        foreach (glob("{$basePath}LE{$rucEmpresa}*.{txt,zip}", GLOB_BRACE) as $archivo) {
            @unlink($archivo);
        }

        // ======================
        // CONSULTA DE VENTAS
        // ======================
        $sql = "
            SELECT 
                td.CodSunat AS tipoComprobante,
                v.Serie,
                LPAD(v.Numero, 8, '0') AS Numero,
                DATE_FORMAT(v.FechaReg, '%Y-%m-%d') AS fechaEmision,
                DATE_FORMAT(v.FechaReg, '%Y-%m-%d') AS fechaVencimiento,
                c.DniRuc,
                c.Cliente,
                'PEN' AS Moneda,
                1 AS tipoCambio,
                ROUND(SUM(vd.Cantidad * vd.Precio), 2) AS total
            FROM Ve_DocVenta v 
            JOIN Ve_DocVentaDet vd ON v.idDocVenta = vd.IdDocVenta 
            JOIN Ve_DocVentaCliente c ON c.IdCliente = v.IdCliente
            JOIN Ve_DocVentaTipoDoc td ON td.IdTipoDoc = v.IdTipoDoc
            WHERE td.VaRegVenta = :declarado
              AND td.CodSunat IN ('01','03','07','08')
              AND DATE(v.FechaReg) BETWEEN :fechaIni AND :fechaFin
            GROUP BY td.CodSunat, v.Serie, v.Numero, v.FechaReg, c.DniRuc, c.Cliente
            ORDER BY v.FechaReg, v.Serie, v.Numero
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':declarado' => $declarado,
            ':fechaIni' => $fechaIni,
            ':fechaFin' => $fechaFin
        ]);
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($ventas)) {
            return $response->withJson(['error' => 'No se encontraron registros válidos para SUNAT.'], 404);
        }

        // ======================
        // NOMBRE DEL ARCHIVO (25 caracteres EXACTOS)
        // ======================
        $prefijo = 'LE';
        $periodo = date('Y', strtotime($fechaIni)) . str_pad(date('m', strtotime($fechaIni)), 2, '0', STR_PAD_LEFT) . '00';
        $codigoLibro = '140400'; // Registro de Ventas e Ingresos
        $codigoOperacion = '02'; // Primer envío (o 14 para reemplazo si fuera el caso)
        $indicadorContenido = '1'; // Con información
        $indicadorMoneda = '1'; // Soles (1 = PEN, 2 = USD)
        $generadoPor = '1'; // Sistema propio
        $identificadorEnvio = '2'; // Primer envío
        $nombreBase = "{$prefijo}{$rucEmpresa}{$periodo}{$codigoLibro}{$codigoOperacion}{$indicadorContenido}{$indicadorMoneda}{$generadoPor}{$identificadorEnvio}";
        $nombreTxt = "{$nombreBase}.txt";
        $nombreZip = "{$nombreBase}.zip";
        $rutaTxt = "{$basePath}{$nombreTxt}";
        $rutaZip = "{$basePath}{$nombreZip}";

        if (strlen($nombreBase) !== 33) {
            throw new Exception("El nombre del archivo debe tener 33 caracteres, tiene " . strlen($nombreBase) . ". Nombre generado: {$nombreBase}");
        }

        // ======================
        // GENERAR ARCHIVO TXT
        // ======================
        $contenido = '';
        $linea = 0;

        $contenido = '';
        $linea = 0;

        foreach ($ventas as $v) {
            $linea++;
            $periodo = date('Ym', strtotime($fechaIni)) . '00';
            $cuo = str_pad($linea, 5, '0', STR_PAD_LEFT);

            $rucCliente = trim($v['DniRuc']);
            $tipoDocCliente = (strlen($rucCliente) === 11) ? '6' : ((strlen($rucCliente) === 8) ? '1' : '0');

            $razonSocial = strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', $v['Cliente']));
            $razonSocial = preg_replace('/[^A-Z0-9\s\.\-]/', '', $razonSocial);
            $razonSocial = preg_replace('/\s+/', ' ', $razonSocial);
            $razonSocial = trim($razonSocial);
            $total = (float)$v['total'];
            if ($v['tipoComprobante'] === '07') {
                $total *= -1;
            }
            $baseImponible = $total / 1.18;
            $igv = $total - $baseImponible;
            $total = number_format($total, 2, '.', '');
            $baseImponible = number_format($baseImponible, 2, '.', '');
            $igv = number_format($igv, 2, '.', '');

            $campos = [
                $periodo,               // 1
                $cuo,                   // 2
                "",                     // 3
                $v['fechaEmision'],     // 4
                $v['fechaVencimiento'], // 5
                $v['tipoComprobante'],  // 6
                $v['Serie'],            // 7
                $v['Numero'],           // 8
                "",                     // 9 (vacío)
                $tipoDocCliente,        // 10
                $rucCliente,            // 11
                $razonSocial,           // 12
                $baseImponible,         // 13
                $igv,                   // 14
                "0.00",
                "0.00",
                "0.00",
                "0.00",
                "0.00",
                "0.00", // 15–20
                $total,                 // 21
                $v['Moneda'],           // 22
                $tipoCambio             // 23
            ];

            for ($i = count($campos); $i < 79; $i++) {
                $campos[] = "";
            }

            $campos[] = "1"; // Estado = 1 (con información)
            $contenido .= implode('|', $campos) . "\r\n";
        }
        // Convertir a ISO y guardar
        $contenido = mb_convert_encoding(trim($contenido) . "\r\n", 'ISO-8859-1', 'UTF-8');
        file_put_contents($rutaTxt, $contenido, LOCK_EX);

        // ======================
        // CREAR ZIP
        // ======================
        $zip = new ZipArchive();
        if ($zip->open($rutaZip, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $zip->addFile($rutaTxt, basename($rutaTxt));
            $zip->close();
        } else {
            throw new Exception("No se pudo crear el archivo ZIP.");
        }

        // ======================
        // RESPUESTA FINAL
        // ======================
        $host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

        return $response->withJson([
            'success' => true,
            'rucEmpresa' => $rucEmpresa,
            'nombreArchivo' => $nombreBase,
            'longitud' => strlen($nombreBase),
            'txt' => "{$host}/api/sunat/api_cpe/PRODUCCION/{$rucEmpresa}/{$nombreTxt}",
            'zip' => "{$host}/api/sunat/api_cpe/PRODUCCION/{$rucEmpresa}/{$nombreZip}"
        ], 200);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});


$app->get('/exportar/compras/zip', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $fechaIni   = $request->getParam('fechaIni');
        $fechaFin   = $request->getParam('fechaFin');
        $declarado  = $request->getParam('declarado') ?: 0;

        $rucEmpresa     = NRO_DOCUMENTO_EMPRESA;
        $nombreEmpresa  = RAZON_SOCIAL_EMPRESA;

        $basePath = dirname(__FILE__) . "/sunat/api_cpe/PRODUCCION/{$rucEmpresa}/";
        if (!is_dir($basePath)) mkdir($basePath, 0777, true);

        foreach (glob("{$basePath}LE{$rucEmpresa}*.{txt,zip}", GLOB_BRACE) as $archivo) {
            @unlink($archivo);
        }

        $sql = "SELECT DISTINCT
                p.Ruc AS rucProveedor,
                p.Proveedor AS razonSocialProveedor,
                DATE_FORMAT(m.MovimientoFecha, '%Y-%m-%d') AS fechaEmision,
                DATE_FORMAT(m.FechaVenCredito, '%Y-%m-%d') AS fechaVencimiento,
                m.Serie AS serie,
                m.Numero AS numero,
                mt.CodSunat tipoComprobante,
                m.Moneda AS moneda,
                m.NroComprobanteModifica,
                m.NotaIdMotivo,
                m.NotaDescMotivo,
                IFNULL(m.TipoCambio, 0) AS tipoCambio,
                ROUND(SUM(md.Cantidad * md.Precio), 2) AS total,
                ROUND(SUM(CASE WHEN md.TieneIgv = 1 THEN (md.Cantidad * md.Precio) / 1.18 ELSE 0 END), 2) AS valorVentaGravada,
                ROUND(SUM(CASE WHEN md.TieneIgv = 1 THEN ((md.Cantidad * md.Precio) - ((md.Cantidad * md.Precio) / 1.18)) ELSE 0 END), 2) AS montoIgv,
                ROUND(SUM(CASE WHEN md.TieneIgv = 0 THEN (md.Cantidad * md.Precio) ELSE 0 END), 2) AS valorVentaNoGravada
            FROM Lo_Movimiento m
            JOIN Lo_MovimientoDetalle md ON md.hashMovimiento = m.Hash
            JOIN Lo_MovimientoTipo mt ON m.IdMovimientoTipo = mt.IdMovimientoTipo
            JOIN Lo_Proveedor p ON m.IdProveedor = p.IdProveedor
            WHERE mt.CodSunat IN ('01','03','07','08','14')
                AND DATE(m.MovimientoFecha) BETWEEN '$fechaIni' AND '$fechaFin'
            GROUP BY
                p.Ruc, p.Proveedor, m.MovimientoFecha, m.Serie, m.Numero, mt.CodSunat, m.Moneda, m.TipoCambio
            ORDER BY m.MovimientoFecha, m.Serie, m.Numero";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($compras)) {
            return $response->withJson(['error' => 'No se encontraron registros válidos para SUNAT.'], 404);
        }

        $anio = date('Y', strtotime($fechaIni));
        $mes  = str_pad(date('m', strtotime($fechaIni)), 2, '0', STR_PAD_LEFT);
        $dia  = '00';

        $codigoLibro = '080400';
        $oportunidad = '02';
        $indicadorOperaciones = '1';
        $indicadorContenido = '1';
        $indicadorMoneda = '1';
        $indicadorSire = '2';

        $nombreBase = "LE{$rucEmpresa}{$anio}{$mes}{$dia}{$codigoLibro}{$oportunidad}{$indicadorOperaciones}{$indicadorContenido}{$indicadorMoneda}{$indicadorSire}";
        $nombreTxt = "{$nombreBase}.txt";
        $nombreZip = "{$nombreBase}.zip";

        $rutaTxt = "{$basePath}{$nombreTxt}";
        $rutaZip = "{$basePath}{$nombreZip}";

        // ================================
        // 🧾 GENERAR CONTENIDO SIRE TXT
        // ================================
        $contenido = '';

        $contenido = '';

        foreach ($compras as $c) {
            $periodo = date('Ym', strtotime($fechaIni));
            $rucProveedor = trim($c['rucProveedor']);
            $tipoDocProveedor = (strlen($rucProveedor) === 11) ? '6' : ((strlen($rucProveedor) === 8) ? '1' : '0');

            // Limpieza de Razón Social
            $razonSocialProveedor = strtoupper(trim($c['razonSocialProveedor']));
            $razonSocialProveedor = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', 'Ü'], ['A', 'E', 'I', 'O', 'U', 'N', 'U'], $razonSocialProveedor);
            $razonSocialProveedor = preg_replace('/[^A-Z0-9\s\.\-]/', '', $razonSocialProveedor);
            $razonSocialProveedor = preg_replace('/\s+/', ' ', $razonSocialProveedor);

            // Manejo de montos y negativos para Notas de Crédito (07)
            $valorVentaGravada   = number_format($c['valorVentaGravada'], 2, '.', '');
            $montoIgv            = number_format($c['montoIgv'], 2, '.', '');
            $valorVentaNoGravada = number_format($c['valorVentaNoGravada'], 2, '.', '');

            // TOTAL: negativo SOLO para Nota de Crédito (07)
            if ($c['tipoComprobante'] === '07') {
                $total = number_format(abs($c['total']) * -1, 2, '.', '');
            } else {
                $total = number_format($c['total'], 2, '.', '');
            }
            // Inicializar variables de referencia SIEMPRE ANTES de usarlas
            $refFecha = '';
            $refTipo  = '';
            $refSerie = '';
            $refNum   = '';
            $MovimientoFecha = '';
            if ($c['tipoComprobante'] === '07' || $c['tipoComprobante'] === '08') {
                $partes = explode('-', $c['NroComprobanteModifica']);
                if (count($partes) === 2) {
                    $refSerie = trim($partes[0]);
                    $refNum   = trim($partes[1]);

                    // 1. Corregimos la consulta y la extracción del dato
                    $sqlOrig = "SELECT MovimientoFecha FROM Lo_Movimiento WHERE Serie=? AND Numero=? LIMIT 1";
                    $stmtOrig = $this->db->prepare($sqlOrig);
                    $stmtOrig->execute([$refSerie, $refNum]);
                    $resultado = $stmtOrig->fetch(PDO::FETCH_ASSOC);

                    if ($resultado && !empty($resultado['MovimientoFecha'])) {
                        // 2. Convertimos el valor del array a formato dd/mm/yyyy
                        $refFecha = date('d/m/Y', strtotime($resultado['MovimientoFecha']));
                    } else {
                        // Si no encuentra la factura, SUNAT igual pide una fecha (ponemos la de la NC por seguridad)
                        $refFecha = date('d/m/Y', strtotime($c['fechaEmision']));
                    }

                    // Determinar tipo modificado
                    $primeraLetra = strtoupper(substr($refSerie, 0, 1));
                    $refTipo = ($primeraLetra === 'B') ? '03' : '01';
                }
            }

            // Lógica de columnas para evitar Error 325
            $c15 = '0.00';
            $c16 = '0.00';
            $c21 = '0.00';
            $c32 = '';
            $c33 = '';
            if ($c['valorVentaGravada'] > 0) {
                $c15 = $valorVentaGravada;
                $c16 = $montoIgv;
                $c33 = "1";
            } elseif ($c['valorVentaNoGravada'] > 0) {
                if ($c['tipoComprobante'] === '07') {
                    $c21 = number_format(abs($c['valorVentaNoGravada']) * -1, 2, '.', '');
                    $c32 = "";    // Limpiamos el "04" que causaba error de longitud
                    $c33 = "1";   // Cambiamos "1-0" por "1" (Mercadería) o "5" (Gastos)
                } else {
                    $c21 = $valorVentaNoGravada;
                    $c33 = "1";
                }
            }

            // === 38 CAMPOS EXACTOS ===
            $campos = [
                $rucEmpresa,            // 1
                $nombreEmpresa,         // 2
                $periodo,               // 3
                '',                     // 4
                date('d/m/Y', strtotime($c['fechaEmision'])), // 5
                date('d/m/Y', strtotime($c['fechaVencimiento'] ?: $c['fechaEmision'])), // 6
                $c['tipoComprobante'],  // 7
                $c['serie'],            // 8
                '',                     // 9
                $c['numero'],           // 10
                '',                     // 11
                $tipoDocProveedor,      // 12
                $rucProveedor,          // 13
                $razonSocialProveedor,  // 14
                $c15,                   // 15
                $c16,                   // 16
                '0.00',                 // 17
                '0.00',                 // 18
                '0.00',                 // 19
                '0.00',                 // 20
                $c21,                   // 21
                '0.00',                 // 22
                '0.00',                 // 23
                '0.00',                 // 24
                $total,                 // 25
                $c['moneda'],           // 26
                '',                     // 27
                $refFecha,              // 28
                $refTipo,               // 29
                $refSerie,              // 30
                '',                     // 31
                $refNum,                // 32
                '',                     // 33
                '',                     // 34
                '',                     // 35
                '0.00',                  // 36
                '',                     // 37
                '',                     // 37
                '',                     // 37
                '',                     // 37
                ''                      // 38
            ];

            $contenido .= implode('|', $campos) . "\r\n";
        }

        $contenido = mb_convert_encoding(trim($contenido) . "\r\n", 'ISO-8859-1', 'UTF-8');
        file_put_contents($rutaTxt, $contenido, LOCK_EX);

        // ================================
        // 🗜️ CREAR ZIP
        // ================================
        $zip = new ZipArchive();
        if ($zip->open($rutaZip, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $zip->addFile($rutaTxt, basename($rutaTxt));
            $zip->close();
        }

        $host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        return $response->withJson([
            'success' => true,
            'txt' => "{$host}/api/sunat/api_cpe/PRODUCCION/{$rucEmpresa}/{$nombreTxt}",
            'zip' => "{$host}/api/sunat/api_cpe/PRODUCCION/{$rucEmpresa}/{$nombreZip}",
            'nombreArchivo' => $nombreBase
        ], 200);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/producto/inv', function (Request $request, Response $response, array $args) {
    $q = $request->getParam('q');
    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;

    $select = "select * from Gen_Producto where ControlaStock = 1 and Anulado = 0";
    if ($q) {
        $select .= " AND Producto LIKE '" . $q . "%' ";
    }

    if ($limit) {
        // $limit = $request->getParam('limit');
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/producto/listarstockvalorizado', function (Request $request, Response $response, array $args) {
    try {

        $producto = $request->getParam('producto');
        $stock = $request->getParam('stock');
        $precio = $request->getParam('precio');
        $anno = $request->getParam('anno');
        $Tipo = $request->getParam('Tipo');

        $stmt = $this->db->prepare("call SbLo_StockValoriado(:producto, :stock, :precio, :anno, :tipo)");
        $stmt->bindParam(':producto', $producto);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':anno', $anno);
        $stmt->bindParam(':tipo', $Tipo);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
        //throw $th;
    }
});

$app->post('/producto/listarstockvalorizado/excel', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $data = $request->getParam('data');

        $cont = 1;
        $excel = new Spreadsheet();
        $sheet = $excel->getActiveSheet();
        foreach ($data as $key => $row) {

            $sheet->setCellValue('A' . $cont, $row['d1']);
            $sheet->setCellValue('B' . $cont, $row['d2']);
            $sheet->setCellValue('C' . $cont, $row['d3']);
            $sheet->setCellValue('D' . $cont, $row['d4']);
            $sheet->setCellValue('E' . $cont, $row['d5']);
            $sheet->setCellValue('F' . $cont, $row['d6']);
            $sheet->setCellValue('G' . $cont, $row['d7']);
            $sheet->setCellValue('H' . $cont, $row['d8']);
            $sheet->setCellValue('I' . $cont, $row['d9']);
            $sheet->setCellValue('J' . $cont, $row['d10']);
            $sheet->setCellValue('K' . $cont, $row['d11']);
            $cont++;
        }


        foreach (range('A', 'K') as $columnID) {
            $excel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }


        aplicarIdentidadEmpresaExcel($excel, $this->db);
        $excelWriter = new Xlsx($excel);

        $fileName = 'STOCK_VALORIZADO_' . '' . getNow('Y-m-d-H-i-s') .  '.xlsx';
        $excelFileName = __DIR__ . '/reporte/' . $fileName;
        $excelWriter->save($excelFileName);

        return $response->withJson($_SERVER['HTTP_HOST'] . '/api/reporte/' .  $fileName);
    } catch (Exception $e) {
        return $response->withJson(['errorr' => $e->getMessage()], 500);
    }
});

$app->post('/producto/listarstockvalorizado/pdf', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $data = $request->getParam('data');

        $cont = 1;
        $excel = new Spreadsheet();
        $sheet = $excel->getActiveSheet();
        foreach ($data as $key => $row) {

            $sheet->setCellValue('A' . $cont, $row['d1']);
            $sheet->setCellValue('B' . $cont, $row['d2']);
            $sheet->setCellValue('C' . $cont, $row['d3']);
            $sheet->setCellValue('D' . $cont, $row['d4']);
            $sheet->setCellValue('E' . $cont, $row['d5']);
            $sheet->setCellValue('F' . $cont, $row['d6']);
            $sheet->setCellValue('G' . $cont, $row['d7']);
            $sheet->setCellValue('H' . $cont, $row['d8']);
            $sheet->setCellValue('I' . $cont, $row['d9']);
            $sheet->setCellValue('J' . $cont, $row['d10']);
            $sheet->setCellValue('K' . $cont, $row['d11']);
            $cont++;
        }


        foreach (range('A', 'K') as $columnID) {
            $excel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }


        aplicarIdentidadEmpresaExcel($excel, $this->db);
        $excelWriter = new Xlsx($excel);

        $fileName = 'STOCK_VALORIZADO_' . '' . getNow('Y-m-d-H-i-s') .  '.xlsx';
        $excelFileName = __DIR__ . '/reporte/' . $fileName;
        $excelWriter->save($excelFileName);

        return $response->withJson($_SERVER['HTTP_HOST'] . '/api/reporte/' .  $fileName);
    } catch (Exception $e) {
        return $response->withJson(['errorr' => $e->getMessage()], 500);
    }
});

$app->get('/reporte/kardexvalorizadopdf', function (Request $request, Response $response, array $args) use ($app) {

    $producto = $request->getParam('producto');
    $anno = $request->getParam('anno');
    $stock = $request->getParam('stock');
    $precio = $request->getParam('precio');
    $Tipo = $request->getParam('Tipo');
    $mesSeleccionado = $request->getParam('Mes');

    $Ssql = "CALL SbLo_StockValoriado(:producto, :stock, :precio, :anno, :Tipo)";

    try {

        $stmt =  $this->db->prepare($Ssql);


        $stmt->bindParam(':producto', $producto, PDO::PARAM_STR);
        $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
        $stmt->bindParam(':precio', $precio, PDO::PARAM_STR);
        $stmt->bindParam(':anno', $anno, PDO::PARAM_INT);
        $stmt->bindParam(':Tipo', $Tipo, PDO::PARAM_STR);

        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // return $response->withJson($mesAnterior);


        $inventarioInicial  = 0;
        // ------------
        if ($mesSeleccionado) {
            $primerDiaMes = DateTime::createFromFormat('Y-m-d', "$anno-$mesSeleccionado-01");

            $ultimoDiaMes = clone $primerDiaMes;
            $ultimoDiaMes->modify('last day of this month');

            $primerDia = $primerDiaMes->format('Y-m-d');
            $ultimoDia = $ultimoDiaMes->format('Y-m-d');

            $mesAnterior = str_pad($mesSeleccionado - 1, 2, '0', STR_PAD_LEFT);
            foreach ($resultados as $res) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $res['d1'])) {
                    $mesDeLaFecha = explode("-", $res['d1'])[1];
                    if ($mesDeLaFecha == $mesAnterior) {
                        $inventarioInicial = round($res['d9'], 2);
                    }
                }
            }
        };
        $newData = [];
        foreach ($resultados as $index => $res) {
            if ($mesSeleccionado) {
                if (trim($res['d1']) == 'DESDE') {

                    $res['d2'] = $primerDia; // Reemplazamos el valor de "DESDE"

                }
                if (trim($res['d1']) == 'HASTA') {
                    $res['d2'] = $ultimoDia; // Reemplazamos el valor de "HASTA"
                }
            }

            if ($index == 7) {
                $res['d1'] = "";
                // if ($mesDeLaFecha == $mesAnterior) {
                //     # code...
                // }
                $res['d2'] = '';
                $res['d3'] = "Entradas";
                // $res['d3'] = "Inventario Inicial";
                $res['d6'] = "Salida";
                $res['d9'] = "Saldo";
                // return $response->withJson($res);
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $res['d1'])) {
                if ($mesSeleccionado) {
                    $mesDeLaFecha = explode("-", $res['d1'])[1];
                    if ($mesDeLaFecha == $mesSeleccionado) {
                        $newData[] = $res;
                    }
                } else {
                    $newData[] = $res;
                }
            } else {
                $newData[] = $res;
            }
        }
        // return $response->withJson($newData);
        $firstPart = array_slice($newData, 1, 6);

        // Extraer los elementos restantes
        $secondPart = array_slice($newData, 7);
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
            "TIPO_PROCESO" => '03',
            'inventarioInicial' => $inventarioInicial,
            'cabecera' => $firstPart,
            'datos' => $secondPart
        );

        $rutapdf = $_SERVER['HTTP_HOST'] . '/api/sunat/plugins/dompdf/';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $rutapdf . "kardexvalorizado.php?",
            CURLOPT_USERAGENT => "Codular Sample cURL Request",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($datosImprimir)
        ));

        $resp = curl_exec($curl);
        curl_close($curl);
        error_reporting(E_ALL);
        ini_set('display_errors', 1);



        // return $response->withRedirect('/api/reporte/reporteventas.xlsx');
        $nombreArchivo = 'KARDEX_VALORIZADO';
        // $ruta = 'https://' . $_SERVER['HTTP_HOST'] . "/api/sunat/api_cpe/" . 'PRODUCCION' . "/" . NRO_DOCUMENTO_EMPRESA . "/" . $nombreArchivo . '.pdf';

        $ruta = 'http://' . $_SERVER['HTTP_HOST'] . "/api/sunat/api_cpe/" . 'PRODUCCION' . "/" . NRO_DOCUMENTO_EMPRESA . "/" . $nombreArchivo . '.pdf';
        return $response->withJson(['url' => $ruta]);
    } catch (PDOException $e) {

        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/reporte/kardex', function (Request $request, Response $response, array $args) use ($app) {

    $producto = $request->getParam('producto');
    $fechaIni = $request->getParam('fechaIni');
    $fechaFin = $request->getParam('fechaFin');
    $Tipo = $request->getParam('Tipo');

    $Ssql = "call SbLo_Kardex('$producto', '$fechaIni', '$fechaFin', $Tipo);";

    try {

        $stmt =  $this->db->prepare($Ssql);


        $stmt->bindParam(':producto', $producto, PDO::PARAM_STR);
        $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
        $stmt->bindParam(':precio', $precio, PDO::PARAM_STR);
        $stmt->bindParam(':anno', $anno, PDO::PARAM_INT);
        $stmt->bindParam(':Tipo', $Tipo, PDO::PARAM_STR);

        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // return $response->withJson($resultados);
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/kardex.xlsx');
        $sheet = $spreadsheet->getSheet(0);

        $count = 2;
        foreach ($resultados as $res) {
            $sheet->setCellValue('A' . $count, $res["d1"]);
            $sheet->setCellValue('B' . $count, $res["d2"]);
            $sheet->setCellValue('C' . $count, $res["d3"]);
            $sheet->setCellValue('D' . $count, $res["d4"]);
            $sheet->setCellValue('E' . $count, $res["d5"]);
            $count++;
        }
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        aplicarIdentidadEmpresaExcel($spreadsheet, $this->db);
        $writer->save('reporte/kardex.xlsx');

        // return $response->withRedirect('/api/reporte/reporteventas.xlsx');

        echo "<script>window.location.href = '/api/reporte/kardex.xlsx'</script>";
        exit;
    } catch (PDOException $e) {

        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});


$app->get('/reporte/kardex/nuevo', function (Request $request, Response $response, array $args) use ($app) {
    $idAlmacen = $request->getParam('idAlmacen');
    $numMes = $request->getParam('numMes');
    $anio = $request->getParam('anio');

    $fechaDesde = $anio . "-" . $numMes . "-01";
    $fechaHasta = $anio . "-" . $numMes . "-31";

    $fechaHastaStock = $anio . "-" . $numMes . "-01 00:00:00";
    $nombreMes = "";

    switch ($numMes) {
        case '01':
            $nombreMes = "ENERO";
            break;

        case '02':
            $nombreMes = "FEBRERO";
            break;

        case '03':
            $nombreMes = "MARZO";
            break;

        case '04':
            $nombreMes = "ABRIL";
            break;

        case '05':
            $nombreMes = "MAYO";
            break;

        case '06':
            $nombreMes = "JUNIO";
            break;

        case '07':
            $nombreMes = "JULIO";
            break;

        case '08':
            $nombreMes = "AGOSTO";
            break;

        case '09':
            $nombreMes = "SEPTIEMBRE";
            break;

        case '10':
            $nombreMes = "OCTUBRE";
            break;

        case '11':
            $nombreMes = "NOVIEMBRE";
            break;

        case '12':
            $nombreMes = "DICIEMBRE";
            break;

        default:
            $nombreMes = "NO ESPECIFICADO";
            break;
    }

    if ($idAlmacen > 0) {
        $strIngresos = stringIngresoUndProductoNuevo($idAlmacen,  $fechaDesde, $fechaHasta);
        $strSalidas = stringSalidaUndProductoNuevo($idAlmacen, $fechaDesde, $fechaHasta);
        $strVentas = stringSalidaVentaUndProductoNuevo($idAlmacen, $fechaDesde, $fechaHasta);
        $select = $strIngresos . ' UNION ALL ' . $strSalidas . ' UNION ALL ' . $strVentas;
    } else {
        $strIngresos = stringGeneralProductoNuevo($idAlmacen,  $fechaDesde, $fechaHasta);
        $strVentas = stringSalidaVentaUndProductoNuevo($idAlmacen, $fechaDesde, $fechaHasta);
        $select = $strIngresos . ' UNION ALL ' . $strVentas;
    }
    $select .= ' ORDER BY Fecha ASC';
    $stmt = $this->db->query($select);
    $stmt->execute();

    $data = $stmt->fetchAll();

    if ($idAlmacen > 0) {
        $select = "SELECT * FROM Lo_Almacen WHERE IdAlmacen =" . $idAlmacen;
        $stmt = $this->db->query($select);
        $stmt->execute();
        $dataAlm = $stmt->fetch();
        $almacenNombre = $dataAlm['Almacen'];
        $almacenDireccion = $dataAlm['Direccion'];
    } else {
        $almacenNombre = 'TODOS';
        $almacenDireccion = '-------';
    }


    $ids = array();
    $idsProd = array();

    foreach ($data as $row) {
        if (!in_array($row['IdProducto'], $ids)) {
            array_push($ids, $row['IdProducto']);
            array_push($idsProd, array("IdProducto" => $row['IdProducto'], "Producto" => $row['Producto'], "CodigoBarra" => $row['CodigoBarra'], "Detalle" => array(), "Stock" => 0));
        }
    }

    $selectAllProductos = $idAlmacen > 0
        ? "SELECT Gen_Producto.IdProducto, Gen_Producto.Producto, Gen_Producto.CodigoBarra,Lo_Almacen.IdAlmacen FROM Gen_Producto
            INNER JOIN Ve_DocVentaDet ON Gen_Producto.IdProducto=Ve_DocVentaDet.idProducto
            INNER JOIN Ve_DocVenta ON Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.idDocVenta
            INNER JOIN Lo_Almacen ON Ve_DocVenta.IdAlmacen=Lo_Almacen.IdAlmacen
        WHERE Lo_Almacen.IdAlmacen = $idAlmacen"
        : "SELECT IdProducto, Producto, CodigoBarra FROM Gen_Producto";

    $stmtTodos = $this->db->query($selectAllProductos);
    $stmtTodos->execute();
    $allProductos = $stmtTodos->fetchAll();

    foreach ($allProductos as $row) {
        if (!in_array($row['IdProducto'], $ids)) {
            array_push($ids, $row['IdProducto']);
            array_push($idsProd, array(
                "IdProducto" => $row['IdProducto'],
                "Producto" => $row['Producto'],
                "CodigoBarra" => $row['CodigoBarra'],
                "Detalle" => array(),
                "Stock" => 0
            ));
        }
    }

    foreach ($idsProd as $key => $prod) {
        foreach ($data as $row) {
            if ($prod['IdProducto'] == $row['IdProducto']) {
                array_push($ids, $row['IdProducto']);
                array_push($idsProd[$key]["Detalle"], $row);
            }
        }
    }

    $idProductos = http_build_query(array(
        'idProductos' => $ids
    ));

    $productos = array();
    if ($idAlmacen > 0) {
        $res = $app->subRequest('GET', '/productos/puntoventa/stock/' . $idAlmacen, 'fechaHasta=' . $fechaHastaStock . '&noLimit=1&' . $idProductos);
        $productos = (string) $res->getBody();
        $productos = json_decode($productos, true);
    } else {
        $res = $app->subRequest('GET', '/productos/puntoventa/general/stock', 'fechaHasta=' . $fechaHastaStock . '&noLimit=1&' . $idProductos);
        $productos = (string) $res->getBody();
        $productos = json_decode($productos, true);
    }

    foreach ($idsProd as $key => $prod) {
        foreach ($productos as $row) {
            if ($prod['IdProducto'] == $row['IdProducto']) {
                $idsProd[$key]["Stock"] = $row['stock'];
            }
        }
    }


    $excel = new Spreadsheet();
    //$sheet = $excel->setActiveSheetIndex(0);
    $sheet = $excel->getActiveSheet();
    $excel->getActiveSheet()->mergeCells('A1:T1');
    $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(15);

    $sheet->setCellValue('A1', 'FORMATO 12.1: REGISTRO DEL INVENTARIO PERMANENTE EN UNIDADES FÍSICAS - DETALLE DEL INVENTARIO PERMANENTE EN UNIDADES FÍSICAS');
    $sheet->setCellValue('A3', $nombreMes . " - " . $anio);
    $sheet->setCellValue('A4', 'R.U.C.   : 20393484382');
    $sheet->setCellValue('A5', 'APELLIDOS Y NOMBRES, DENOMINACION:IMPORTACIONES Y EXPORTACIONES AGUILA DEL ORIENTE EIRL');
    $sheet->setCellValue('A6', 'ESTABLECIMIENTO: ' . $almacenDireccion . ' - ' . $idAlmacen . ' ' . $almacenNombre);
    $sheet->setCellValue('A7', 'CODIGO DE LA EXISTENCIA');
    $sheet->setCellValue('A8', 'DESCRIPCION');
    $sheet->setCellValue('A9', 'TIPO: 01-MERCADERIA');
    $sheet->setCellValue('A10', 'CODIGO DE LA UNIDAD DE MEDIDA:NIU');
    $sheet->setCellValue('A11', 'METODO DE EVALUACION: PROMEDIO PONDERADO');
    $sheet->setCellValue('A13', 'FECHA');
    $sheet->setCellValue('B13', 'TIPO DOCUM');
    $sheet->setCellValue('C13', 'SERIE');
    $sheet->setCellValue('D13', 'NUMERO');
    $sheet->setCellValue('E13', 'TIPO OPERA');
    $sheet->setCellValue('F13', 'CLIENTE - PROVEEDOR');
    $sheet->setCellValue('G13', 'INGRESOS');
    $sheet->setCellValue('H13', 'SALIDAS');
    $sheet->setCellValue('I13', 'SALDOS');

    $excel->getActiveSheet()->getStyle('A13:I13')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A13:I13')->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFE6E6FA');

    $excel->getActiveSheet()->getStyle('A13:I13')->getBorders()->getOutline()
        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
        ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('#000000'));

    $cont = 15;
    $pos = 0;
    foreach ($idsProd as $prod) {
        $pos = $cont;
        $sheet->setCellValue('A' . $cont, "CODIGO: " . $prod["CodigoBarra"]);
        $sheet->setCellValue('B' . $cont, "MED: NIU ");
        $sheet->setCellValue('C' . $cont, $prod["Producto"]);
        $sheet->setCellValue('I' . $cont, $prod["Stock"]);
        $excel->getActiveSheet()->getStyle('A' . $cont)->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('B' . $cont)->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('C' . $cont)->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('I' . $cont)->getFont()->setBold(true);

        $cont += 1;
        $stockAnt = $prod["Stock"];
        foreach ($prod["Detalle"] as $det) {
            $sheet->setCellValue('A' . $cont, $det["Fecha"]);
            $sheet->setCellValue('B' . $cont, $det["CodSunat"]);
            $sheet->setCellValue('C' . $cont, strtoupper($det["Serie"]));
            $sheet->setCellValue('D' . $cont, $det["Numero"]);
            $sheet->setCellValue('E' . $cont, $det["TipoDocSunat"]);
            $sheet->setCellValue('F' . $cont, $det["Nombres"]);
            $sheet->setCellValue('G' . $cont, $det["IngresoCantidad"]);
            $sheet->setCellValue('H' . $cont, $det["SalidaCantidad"]);
            $stockAnt = $stockAnt + $det["IngresoCantidad"] - $det["SalidaCantidad"];
            $sheet->setCellValue('I' . $cont, $stockAnt);
            $cont += 1;
        }
        $excel->getActiveSheet()->getStyle('A' . $pos . ':I' . ($cont - 1))->getBorders()->getOutline()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('#000000'));

        $cont += 1;
    }

    foreach (range('D', 'I') as $columnID) {
        $excel->getActiveSheet()->getColumnDimension($columnID)
            ->setAutoSize(true);
    }

    $excel->getActiveSheet()->getStyle('B15:B' . $cont)->getAlignment()->setHorizontal('left');
    $excel->getActiveSheet()->getStyle('C15:C' . $cont)->getAlignment()->setHorizontal('left');
    $excel->getActiveSheet()->getStyle('D15:D' . $cont)->getAlignment()->setHorizontal('left');
    $excel->getActiveSheet()->getStyle('E15:E' . $cont)->getAlignment()->setHorizontal('left');


    aplicarIdentidadEmpresaExcel($excel, $this->db);
    $excelWriter = new Xlsx($excel);

    $fileName = 'KARDEX F12.1 ' . ($idAlmacen > 0 ? $almacenNombre . ' ' : 'TODOS ') . ' ' . $nombreMes . " - " . $anio . " " . getNow('Y-m-d-H-i-s') .  '.xlsx';
    $excelFileName = __DIR__ . '/reporte/' . $fileName;
    $excelWriter->save($excelFileName);

    echo "<script>window.location.href = '/api/reporte/" . $fileName . "'</script>";
    exit;

    //
});


function stringIngresoUndProductoNuevo($idAlmacen, $fechaDesde, $fechaHasta)
{
    $select = "(SELECT Gen_Producto.IdProducto, Gen_Producto.Producto, Gen_Producto.CodigoBarra, DATE(Lo_Movimiento.MovimientoFecha) AS Fecha,  Lo_MovimientoTipo.CodSunat, Lo_MovimientoTipo.TipoMovSunat AS TipoDocSunat,
    Lo_MovimientoTipo.TipoMovimiento AS Detalle, Lo_Movimiento.Serie, Lo_Movimiento.Numero, Lo_Proveedor.Proveedor AS Nombres,
    'S/' AS Moneda,
    Lo_MovimientoDetalle.Cantidad AS IngresoCantidad, Lo_MovimientoDetalle.Precio AS IngresoPrecio,
    '0' AS SalidaCantidad, '0' AS SalidaPrecio,'0' AS Descuento, Lo_Movimiento.UsuarioReg FROM Lo_MovimientoDetalle
    INNER JOIN Lo_Movimiento ON Lo_MovimientoDetalle.hashMovimiento = Lo_Movimiento.Hash
    INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
    INNER JOIN Gen_Producto ON Lo_MovimientoDetalle.IdProducto = Gen_Producto.IdProducto
    LEFT JOIN Lo_Proveedor ON Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
    LEFT JOIN Gen_Moneda ON Lo_Movimiento.Moneda = Gen_Moneda.Moneda
    WHERE Lo_MovimientoTipo.VaRegCompra = 1 " . ($idAlmacen > 0 ? ' AND Lo_Movimiento.IdAlmacenDestino =' . $idAlmacen . ' ' : '') . "
    AND Lo_Movimiento.Anulado=0 AND Lo_Movimiento.MovimientoFecha BETWEEN '$fechaDesde 00:00:00' AND '$fechaHasta 23:59:59' ORDER BY Lo_Movimiento.MovimientoFecha ASC)";

    return $select;
}

function stringSalidaUndProductoNuevo($idAlmacen, $fechaDesde, $fechaHasta)
{
    $select = "(SELECT Gen_Producto.IdProducto, Gen_Producto.Producto, Gen_Producto.CodigoBarra, DATE(Lo_Movimiento.MovimientoFecha) AS Fecha, Lo_MovimientoTipo.CodSunat, Lo_MovimientoTipo.TipoMovSunat AS TipoDocSunat,
    Lo_MovimientoTipo.TipoMovimiento AS Detalle, Lo_Movimiento.Serie, Lo_Movimiento.Numero, Lo_Proveedor.Proveedor AS Nombres,
    'S/' AS Moneda,
    '0' AS IngresoCantidad, '0' AS IngresoPrecio,
    (CASE
        WHEN Lo_MovimientoTipo.CodSunat = '07' AND Lo_Movimiento.NotaIdMotivo NOT IN ('01', '02', '06', '07') THEN '0'
        ELSE Lo_MovimientoDetalle.Cantidad
    END) AS SalidaCantidad, 
    (CASE
        WHEN Lo_MovimientoTipo.CodSunat = '07' AND Lo_Movimiento.NotaIdMotivo NOT IN ('01', '02', '06', '07') THEN '0'
        ELSE Lo_MovimientoDetalle.Precio
    END) AS SalidaPrecio,
    '0' AS Descuento,Lo_Movimiento.UsuarioReg FROM Lo_MovimientoDetalle
    INNER JOIN Lo_Movimiento ON Lo_MovimientoDetalle.hashMovimiento = Lo_Movimiento.Hash
    INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
    INNER JOIN Gen_Producto ON Lo_MovimientoDetalle.IdProducto = Gen_Producto.IdProducto
    LEFT JOIN Lo_Proveedor ON Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
    LEFT JOIN Gen_Moneda ON Lo_Movimiento.Moneda = Gen_Moneda.Moneda
    WHERE Lo_MovimientoTipo.VaRegCompra = 1 " . ($idAlmacen > 0 ? ' AND Lo_Movimiento.IdAlmacenOrigen =' . $idAlmacen . ' ' : '') . " AND Lo_Movimiento.Anulado = 0 AND Lo_Movimiento.MovimientoFecha BETWEEN '$fechaDesde 00:00:00' AND '$fechaHasta 23:59:59' ORDER BY Lo_Movimiento.MovimientoFecha ASC)";

    return $select;
}

function stringSalidaVentaUndProductoNuevo($idAlmacen, $fechaDesde, $fechaHasta)
{
    $select = "(SELECT Gen_Producto.IdProducto, Gen_Producto.Producto, Gen_Producto.CodigoBarra, DATE(Ve_DocVenta.FechaDoc) AS Fecha, Ve_DocVentaTipoDoc.CodSunat, Ve_DocVentaTipoDoc.TipoDocSunat,
    CONCAT('VENTA - ', Ve_DocVentaTipoDoc.TipoDoc) AS Detalle, Ve_DocVenta.Serie, Ve_DocVenta.Numero,
    Ve_DocVentaCliente.Cliente AS Nombres,
    'S/' AS Moneda,
    (CASE
        WHEN Ve_DocVentaTipoDoc.CodSunat = '07' AND NotaIdMotivo IN ('01', '02', '06', '07') THEN Ve_DocVentaDet.Cantidad
        ELSE '0'
    END) AS IngresoCantidad, 
    (CASE
        WHEN Ve_DocVentaTipoDoc.CodSunat = '07' AND NotaIdMotivo IN ('01', '02', '06', '07') THEN Ve_DocVentaDet.Precio
        ELSE '0'
    END) AS IngresoPrecio, 
    (CASE
        WHEN Ve_DocVentaTipoDoc.CodSunat = '07' THEN '0'
        ELSE Ve_DocVentaDet.Cantidad
    END) AS SalidaCantidad, 
    (CASE
        WHEN Ve_DocVentaTipoDoc.CodSunat = '07' THEN '0'
        ELSE Ve_DocVentaDet.Precio
    END) AS SalidaPrecio, 
    Ve_DocVentaDet.Descuento, Ve_DocVenta.UsuarioReg FROM Ve_DocVentaDet 
        INNER JOIN Ve_DocVenta ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta
        INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        LEFT JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente= Ve_DocVentaCliente.IdCliente
        WHERE " . ($idAlmacen > 0 ? ' Ve_DocVenta.IdAlmacen =' . $idAlmacen . ' AND ' : '') . " 
            Ve_DocVenta.Anulado = 0
            AND Ve_DocVentaTipoDoc.VaRegVenta = 1
        AND Ve_DocVenta.FechaDoc BETWEEN '$fechaDesde 00:00:00' AND '$fechaHasta 23:59:59' ORDER BY Ve_DocVenta.FechaDoc ASC)";

    return $select;
}

function stringGeneralProductoNuevo($idAlmacen, $fechaDesde, $fechaHasta)
{
    $select = "(SELECT Gen_Producto.IdProducto, Gen_Producto.Producto, Gen_Producto.CodigoBarra, DATE(Lo_Movimiento.MovimientoFecha) AS Fecha,  Lo_MovimientoTipo.CodSunat, Lo_MovimientoTipo.TipoMovSunat AS TipoDocSunat,
    Lo_MovimientoTipo.TipoMovimiento AS Detalle, Lo_Movimiento.Serie, Lo_Movimiento.Numero, Lo_Proveedor.Proveedor AS Nombres,
     'S/' AS Moneda,
    IF(Lo_MovimientoTipo.Tipo=1,'0',
    Lo_MovimientoDetalle.Cantidad 
    ) AS IngresoCantidad,
    IF(Lo_MovimientoTipo.Tipo=1,'0',
    Lo_MovimientoDetalle.Precio 
    ) AS IngresoPrecio,
    IF(Lo_MovimientoTipo.Tipo=1,
    (CASE
        WHEN Lo_MovimientoTipo.CodSunat = '07' AND Lo_Movimiento.NotaIdMotivo NOT IN ('01', '02', '06', '07') THEN '0'
        ELSE Lo_MovimientoDetalle.Cantidad
    END)
    ,'0')
    AS SalidaCantidad,
    IF(Lo_MovimientoTipo.Tipo=1,
    (CASE
        WHEN Lo_MovimientoTipo.CodSunat = '07' AND Lo_Movimiento.NotaIdMotivo NOT IN ('01', '02', '06', '07') THEN '0'
        ELSE Lo_MovimientoDetalle.Precio
    END)
    ,'0')
    AS SalidaPrecio,  
    '0' AS Descuento, Lo_Movimiento.UsuarioReg FROM Lo_MovimientoDetalle
    INNER JOIN Lo_Movimiento ON Lo_MovimientoDetalle.hashMovimiento = Lo_Movimiento.Hash
    INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
    INNER JOIN Gen_Producto ON Lo_MovimientoDetalle.IdProducto = Gen_Producto.IdProducto
    LEFT JOIN Lo_Proveedor ON Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
    LEFT JOIN Gen_Moneda ON Lo_Movimiento.Moneda = Gen_Moneda.Moneda
    WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_MovimientoTipo.Tipo IN (0,1) " . ($idAlmacen > 0 ? ' AND Lo_Movimiento.IdAlmacenDestino =' . $idAlmacen . ' ' : '') . "
    AND Lo_Movimiento.Anulado=0 AND Lo_Movimiento.MovimientoFecha BETWEEN '$fechaDesde 00:00:00' AND '$fechaHasta 23:59:59' ORDER BY Lo_Movimiento.MovimientoFecha ASC)";

    return $select;
}

$app->get('/productos/categoria/carga', function (Request $request, Response $response, array $args) {

    $idcategoria = $request->getParam('IdCategoria');


    $select = "SELECT Gen_Producto.*, Gen_ProductoCategoria.ProductoCategoria, Gen_ProductoMarca.ProductoMarca,
            Gen_ProductoMedicion.ProductoMedicion, 0 as cantidad
            FROM Gen_Producto
            INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
            INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion";

    $select .= " WHERE Gen_Producto.Anulado=0 AND Gen_Producto.IdProductoCategoria = $idcategoria ORDER BY Gen_Producto.CodigoBarra DESC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->post('/reporte/confrontacion', function (Request $request, Response $response, array $args) use ($app) {
    $productoscant = $request->getParam('Productos');
    $idAlmacen = $request->getParam('IdAlmacen');
    $idProductos = $request->getParam('IdProductos');
    $fechaHasta = getNow("Y-m-d") . " 23:59:59";
    $almacenNombre = '';
    $filterStr = http_build_query(array(
        'idProductos' => $idProductos
    ));

    $res = $app->subRequest('GET', '/productos/stock', 'idAlmacen=' . $idAlmacen . '&' . $filterStr . '&noLimit=1' . '&fechaHasta=' . $fechaHasta);

    $productos = (string) $res->getBody();
    $productos = json_decode($productos, true);

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/confrontacion.xlsx');
    $formato = $spreadsheet->getSheet(0);

    if (!empty($idAlmacen)) {
        $select = "SELECT Almacen FROM Lo_Almacen WHERE IdAlmacen =" . $idAlmacen;
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetch();
        $almacenNombre = $data['Almacen'];
    }

    $formato->getCell('I5')->setValue($almacenNombre);
    $formato->getCell('L3')->setValue(getNow("Y-m-d"));

    $init = 11;

    foreach ($productos as $key => $producto) {
        foreach ($productoscant as $productocant) {
            if ($producto['IdProducto'] == $productocant['IdProducto']) {
                $productos[$key]["cantidad"] = $productocant['cantidad'];
            }
        }
    }

    foreach ($productos as $kar) {

        $formato->mergeCells('B' . ($init) . ':' . 'C' . ($init));
        $formato->mergeCells('D' . ($init) . ':' . 'E' . ($init));
        $formato->mergeCells('F' . ($init) . ':' . 'G' . ($init));
        $formato->getCell('A' . $init)->setValue(@$kar['CodigoBarra']);
        $formato->getCell('B' . $init)->setValue(@$kar['Producto']);
        $formato->getCell('D' . $init)->setValue(@$kar['stock']);
        $formato->getCell('F' . $init)->setValue(@$kar['cantidad']);
        $formato->getCell('H' . $init)->setValue("=IF((D" . $init . "-F" . $init . ")>0,(D" . $init . "-F" . $init . "),0)");
        $formato->getCell('I' . $init)->setValue("=IF((D" . $init . "-F" . $init . ")<0,-1*(D" . $init . "-F" . $init . "),0)");


        $formato->insertNewRowBefore(($init + 1), 1);
        $formato->duplicateStyle($formato->getStyle('A' . $init), 'A' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('B' . $init), 'B' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('C' . $init), 'C' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('D' . $init), 'D' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('E' . $init), 'E' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('F' . $init), 'F' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('G' . $init), 'G' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('H' . $init), 'H' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('I' . $init), 'I' . ($init + 1));
        $formato->mergeCells('B' . ($init + 1) . ':' . 'C' . ($init + 1));
        $formato->mergeCells('D' . ($init + 1) . ':' . 'E' . ($init + 1));
        $formato->mergeCells('F' . ($init + 1) . ':' . 'G' . ($init + 1));

        $init += 1;
    }
    //$formato->removeRow($init); 

    aplicarIdentidadEmpresaExcel($spreadsheet, $this->db);
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $nombreReporte = 'Confrontacion Inv. ' . getNow("Y-m-d-H-i-s") . '.xlsx';
    $writer->save('reporte/' . $nombreReporte);
    /* Mantiene actualizado el nombre usado por versiones antiguas del panel. */
    $writer->save('reporte/Confrontacion Inv. ' . getNow("Y-m-d") . '.xlsx');

    echo '<script type="text/javascript">
        setTimeout(() => {
            location.href = "' . 'http://' . $_SERVER['HTTP_HOST'] . '/api/reporte/' . rawurlencode($nombreReporte) . '?v=' . time() . '";
        }, 1000); 
    </script>';
});

$app->post('/reporte/preconfrontacion', function (Request $request, Response $response, array $args) use ($app) {
    $productoscant = $request->getParam('Productos');
    $idAlmacen = $request->getParam('IdAlmacen');
    $almacenNombre = '';

    $spreadsheet = new Spreadsheet();
    $formato = $spreadsheet->getActiveSheet();
    $formato->setTitle('CONFRONTACION');

    if (!empty($idAlmacen)) {
        $select = "SELECT Almacen FROM Lo_Almacen WHERE IdAlmacen =" . $idAlmacen;
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetch();
        $almacenNombre = $data['Almacen'];
    }

    $formato->mergeCells('A1:G1');
    $formato->setCellValue('A1', 'PRECONFRONTACION DE INVENTARIO');
    $formato->getStyle('A1:G1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF0A4F83');
    $formato->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('FFFFFFFF');
    $formato->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('center');
    $formato->getRowDimension(1)->setRowHeight(30);
    $formato->mergeCells('A2:G2');
    $formato->setCellValue('A2', 'Almacen: ' . $almacenNombre . '  |  Fecha: ' . getNow("d/m/Y H:i"));
    $formato->getStyle('A2:G2')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE9F4FA');
    $formato->getStyle('A2')->getFont()->setBold(true)->getColor()->setARGB('FF315A75');
    $formato->getStyle('A2')->getAlignment()->setHorizontal('center');
    foreach (array('CODIGO','CATEGORIA','PRODUCTO','CANTIDAD SISTEMA','CONTEO FISICO','FALTANTE','SOBRANTE') as $iConf => $headerConf) $formato->setCellValue(chr(65 + $iConf) . '4', $headerConf);
    $formato->getStyle('A4:G4')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF087FAE');
    $formato->getStyle('A4:G4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
    $formato->getStyle('A4:G4')->getAlignment()->setHorizontal('center')->setVertical('center');

    $init = 5;


    foreach ($productoscant as $kar) {

        $formato->getCell('A' . $init)->setValue(@$kar['CodigoBarra']);
        $formato->getCell('B' . $init)->setValue(@$kar['ProductoCategoria']);
        $formato->getCell('C' . $init)->setValue(@$kar['Producto']);
        $formato->getCell('D' . $init)->setValue((float)@$kar['cantidad']);
        $formato->getCell('E' . $init)->setValue('');
        $formato->getCell('F' . $init)->setValue("=IF(E$init=\"\",\"\",IF((D$init-E$init)>0,(D$init-E$init),0))");
        $formato->getCell('G' . $init)->setValue("=IF(E$init=\"\",\"\",IF((D$init-E$init)<0,-1*(D$init-E$init),0))");
        if ($init % 2 === 0) $formato->getStyle('A' . $init . ':G' . $init)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFF2F8FC');
        $init++;
    }
    $lastConfRow = $init - 1;
    $formato->getStyle('A4:G' . max(4, $lastConfRow))->getBorders()->getAllBorders()->setBorderStyle('thin')->getColor()->setARGB('FFD5E3ED');
    foreach (array('A'=>18,'B'=>22,'C'=>46,'D'=>19,'E'=>19,'F'=>15,'G'=>15) as $colConf => $widthConf) $formato->getColumnDimension($colConf)->setWidth($widthConf);
    $formato->getStyle('C5:C' . max(5, $lastConfRow))->getAlignment()->setWrapText(true);
    $formato->getStyle('F5:F' . max(5, $lastConfRow))->getFill()->setFillType('solid')->getStartColor()->setARGB('FFFFE0E6');
    $formato->getStyle('G5:G' . max(5, $lastConfRow))->getFill()->setFillType('solid')->getStartColor()->setARGB('FFD9F5E7');
    $formato->freezePane('A5');
    $formato->setAutoFilter('A4:G' . max(4, $lastConfRow));
    $formato->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $fileName = 'PreConfrontacion-Inventario-' . getNow("Y-m-d-H-i-s") . '.xlsx';
    aplicarIdentidadEmpresaExcel($spreadsheet, $this->db);
    $writer->save('reporte/' . $fileName);

    echo '<script type="text/javascript">
        setTimeout(() => {
            location.href = "' . 'http://' . $_SERVER['HTTP_HOST'] . '/api/reporte/' . $fileName . '";
        }, 1000); 
    </script>';
});

$app->get('/reporte/confrontacion/total', function (Request $request, Response $response, array $args) use ($app) {
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaHasta = date("Y-m-d") . " 23:59:59";
    $almacenNombre = '';

    $res = $app->subRequest('GET', '/productos/stock', 'idAlmacen=' . $idAlmacen . '&noLimit=1&fechaHasta=' . $fechaHasta);
    if ($res->getStatusCode() !== 200) {
        throw new Exception("Error en subRequest: " . $res->getBody());
    }

    $productos = json_decode($res->getBody(), true);
    if (!is_array($productos)) {
        throw new Exception("Respuesta no válida de productos/stock");
    }

    // Cargar plantilla de Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('CONFRONTACION');

    // Obtener nombre del almacén
    if (!empty($idAlmacen)) {
        $select = "SELECT Almacen FROM Lo_Almacen WHERE IdAlmacen = :idAlmacen";
        $stmt = $this->db->prepare($select);
        $stmt->execute(['idAlmacen' => $idAlmacen]);
        $data = $stmt->fetch();
        $almacenNombre = $data['Almacen'] ?? '';
    }

    // Insertar datos en el Excel
    $sheet->mergeCells('A1:F1');
    $sheet->setCellValue('A1', 'CONFRONTACION TOTAL DE INVENTARIO');
    $sheet->getStyle('A1:F1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF0A4F83');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('FFFFFFFF');
    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
    $sheet->mergeCells('A2:F2');
    $sheet->setCellValue('A2', 'Almacen: ' . $almacenNombre . '  |  Fecha: ' . date('d/m/Y H:i'));
    $sheet->getStyle('A2:F2')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE9F4FA');
    $sheet->getStyle('A2')->getFont()->setBold(true)->getColor()->setARGB('FF315A75');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
    foreach (array('CODIGO','PRODUCTO','STOCK SISTEMA','CONTEO FISICO','FALTANTE','SOBRANTE') as $iTotal => $headerTotal) $sheet->setCellValue(chr(65 + $iTotal) . '4', $headerTotal);
    $sheet->getStyle('A4:F4')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF087FAE');
    $sheet->getStyle('A4:F4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
    $sheet->getStyle('A4:F4')->getAlignment()->setHorizontal('center');
    $init = 5;

    foreach ($productos as $kar) {
        $sheet->getCell('A' . $init)->setValue($kar['CodigoBarra'] ?? '');
        $sheet->getCell('B' . $init)->setValue($kar['Producto'] ?? '');
        $sheet->getCell('C' . $init)->setValue((float)($kar['stock'] ?? 0));
        $sheet->getCell('D' . $init)->setValue('');
        $sheet->getCell('E' . $init)->setValue("=IF((C$init-D$init)>0,(C$init-D$init),0)");
        $sheet->getCell('F' . $init)->setValue("=IF((C$init-D$init)<0,-1*(C$init-D$init),0)");
        if ($init % 2 === 0) $sheet->getStyle('A' . $init . ':F' . $init)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFF2F8FC');

        $init++; // Avanza a la siguiente fila
    }

    // Agregar la fila de firmas después de la última fila de productos
    $finalRow = $init + 1; // Se deja una fila vacía entre productos y firmas

    $lastTotalRow = $init - 1;
    $sheet->getStyle('A4:F' . max(4, $lastTotalRow))->getBorders()->getAllBorders()->setBorderStyle('thin')->getColor()->setARGB('FFD5E3ED');
    foreach (array('A'=>20,'B'=>48,'C'=>18,'D'=>18,'E'=>16,'F'=>16) as $colTotal => $widthTotal) $sheet->getColumnDimension($colTotal)->setWidth($widthTotal);
    $sheet->getStyle('B5:B' . max(5, $lastTotalRow))->getAlignment()->setWrapText(true);
    $sheet->freezePane('A5');
    $sheet->setAutoFilter('A4:F' . max(4, $lastTotalRow));
    $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);

    $fecha = getNow("Y-m-d-H-i-s");
    // Guardar archivo
    $fileName = 'Confrontacion_Inv_Total_' . $fecha . '.xlsx';
    $filePath = __DIR__ . "/reporte/" . $fileName;
    aplicarIdentidadEmpresaExcel($spreadsheet, $this->db);
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save($filePath);

    echo '<script type="text/javascript">
        location.href = "/api/reporte/Confrontacion_Inv_Total_' . $fecha . '.xlsx?v=' . time() . '";
    </script>';
});


$app->get('/buscador_cajabanco', function (Request $request, Response $response, array $args) {
    try {
        $cuenta = $request->getParam('IdCuenta');
        $tipo = $request->getParam('IdTipoOperacion');
        $fechaIni = $request->getParam('FechaIni');
        $fechaFin = $request->getParam('FechaFin');
        $select = "SELECT IdCajaBanco, FechaDoc, Lo_Proveedor.Proveedor, Ve_DocVentaCliente.Cliente, Concepto, Importe 
			FROM Cb_CajaBanco
			LEFT JOIN Lo_Proveedor ON Cb_CajaBanco.IdProveedor = Lo_Proveedor.IdProveedor
			LEFT JOIN Ve_DocVentaCliente ON Cb_CajaBanco.IdCliente = Ve_DocVentaCliente.IdCliente
			WHERE IdCuenta=$cuenta AND IdTipoCajaBanco=$tipo AND DATE(FechaDoc) BETWEEN '$fechaIni' AND '$fechaFin'";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});


$app->get('/exportar_buscador_cajabanco', function (Request $request, Response $response, array $args)  use ($app) {
    try {
        $cuenta = $request->getParam('IdCuenta');
        $tipo = $request->getParam('IdTipoOperacion');
        $fechaIni = $request->getParam('FechaIni');
        $fechaFin = $request->getParam('FechaFin');

        $res = $app->subRequest(
            'GET',
            '/buscador_cajabanco',
            'IdCuenta=' . $cuenta . '&IdTipoOperacion=' . $tipo . '&FechaIni=' . $fechaIni . '&FechaFin=' . $fechaFin
        );
        // return $response->withJson(['error' => $res], 500);
        $productos = (string) $res->getBody();
        $productos = json_decode($productos, true);

        $excel = new Spreadsheet();

        $sheet = $excel->getActiveSheet();

        $sheet->setCellValue('A1', 'IdCajaBanco');
        $sheet->setCellValue('B1', 'FechaDoc');
        $sheet->setCellValue('C1', 'Proveedor');
        $sheet->setCellValue('D1', 'Cliente');
        $sheet->setCellValue('E1', 'Concepto');
        $sheet->setCellValue('F1', 'Importe');


        $cont = 2;
        foreach ($productos as $prod) {
            $sheet->setCellValue('A' . $cont, $prod['IdCajaBanco']);
            $sheet->setCellValue('B' . $cont, $prod['FechaDoc']);
            $sheet->setCellValue('C' . $cont, $prod['Proveedor']);
            $sheet->setCellValue('D' . $cont, $prod['Cliente']);
            $sheet->setCellValue('E' . $cont, $prod['Concepto']);
            $sheet->setCellValue('F' . $cont, $prod['Importe']);
            $cont += 1;
        }

        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A237E']],
            'alignment' => ['horizontal' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);

        aplicarIdentidadEmpresaExcel($excel, $this->db);
        $excelWriter = new Xlsx($excel);
        $fileName = 'BuscadoBancaBancos_' . getNow('Y-m-d-H-i-s') .  '.xlsx';
        $excelFileName = __DIR__ . '/reporte/' . $fileName;
        $excelWriter->save($excelFileName);
        return $response->withJson(['file' => "/api/reporte/" . $fileName]);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});


$app->get('/destino/producto/verificar', function (Request $request, Response $response, array $args) use ($app) {
    try {


        $producto = $request->getParam('producto');
        $select = "SELECT * FROM Gen_Producto
        WHERE Producto = '" . $producto
            . "' LIMIT 1";

        $stmt = $this->db_destino->query($select);
        $stmt->execute();
        $prod = $stmt->fetch();

        if (empty($prod)) {
            return $response->withJson(array(
                "producto" => false
            ));
        } else {
            return $response->withJson(array(
                "producto" => $prod
            ));
        }
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->post('/traslado', function (Request $request, Response $response) {
    try {


        $serie = "TRAL01";
        $idMovimientoTipo = 9;
        $almacenOrigen = $request->getParam('idAlmacen') ? $request->getParam('idAlmacen') : 1;
        $usuarioReg = 'usertraslado';
        $movimientoFecha = getNow();
        $hash = time();
        $tipoCambio = 1;

        //
        // BASE DE DATOS ORIGEN.....
        //

        $select = "SELECT (Lo_Movimiento.Numero+1) as NuevoNumero
        FROM Lo_Movimiento
        WHERE Lo_Movimiento.Serie = '$serie' AND Lo_Movimiento.IdMovimientoTipo = '$idMovimientoTipo'
        ORDER BY Lo_Movimiento.Numero DESC
        LIMIT 1";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetch();
        $numero = $data['NuevoNumero'] ? $data['NuevoNumero'] : 1;

        $insert = $this->db->insert(array(
            'IdMovimientoTipo',
            'IdProveedor',
            'Serie',
            'Numero',
            'MovimientoFecha',
            'IdAlmacenOrigen',
            'IdAlmacenDestino',
            'Anulado',
            'FechaReg',
            'UsuarioReg',
            'Hash',
            'FechaStock',
            'Percepcion',
            'EsCredito',
            'FechaPeriodoTributario',
            'TipoCambio',
            'Moneda'
        ))
            ->into('Lo_Movimiento')
            ->values(array(
                $idMovimientoTipo,
                0,
                $serie,
                $numero,
                getNow(),
                $almacenOrigen,
                -1,
                0,
                getNow(),
                $usuarioReg,
                $hash,
                getNow(),
                0,
                0,
                getNow('Ym'),
                $tipoCambio,
                'PEN'
            ));

        $insertId = $insert->execute();

        // start Movimiento Detalle
        $productos = $request->getParam('productos');
        foreach ($productos as $producto) {
            //if ($producto['total'] > 0) {
            $idProducto = $producto['IdProducto'];
            $cantidad = $producto['cantidad'];
            $tieneIgv = 0;
            $precio = $producto['precio'];
            $idLote = 0;
            $precio = $precio * $tipoCambio;

            $insert = $this->db->insert(array('hashMovimiento', 'IdProducto', 'Cantidad', 'TieneIgv', 'Precio', 'IdLote'))
                ->into('Lo_MovimientoDetalle')
                ->values(array($hash, $idProducto, $cantidad, $tieneIgv, $precio, $idLote));
            $insert->execute();
        }

        //
        // BASE DE DATOS DESTINO ......
        //

        $idMovimientoTipo = 4;
        $almacenDestino = 1;

        $select = "SELECT (Lo_Movimiento.Numero+1) as NuevoNumero
        FROM Lo_Movimiento
        WHERE Lo_Movimiento.Serie = '$serie' AND Lo_Movimiento.IdMovimientoTipo = '$idMovimientoTipo'
        ORDER BY Lo_Movimiento.Numero DESC
        LIMIT 1";
        $stmt = $this->db_destino->query($select);
        $stmt->execute();
        $data = $stmt->fetch();
        $numero = $data['NuevoNumero'] ? $data['NuevoNumero'] : 1;

        $insert = $this->db_destino->insert(array(
            'IdMovimientoTipo',
            'IdProveedor',
            'Serie',
            'Numero',
            'MovimientoFecha',
            'IdAlmacenOrigen',
            'IdAlmacenDestino',
            'Anulado',
            'FechaReg',
            'UsuarioReg',
            'Hash',
            'FechaStock',
            'Percepcion',
            'EsCredito',
            'FechaPeriodoTributario',
            'TipoCambio',
            'Moneda'
        ))
            ->into('Lo_Movimiento')
            ->values(array(
                $idMovimientoTipo,
                0,
                $serie,
                $numero,
                getNow(),
                -1,
                $almacenDestino,
                0,
                getNow(),
                $usuarioReg,
                $hash,
                getNow(),
                0,
                0,
                getNow('Ym'),
                $tipoCambio,
                'PEN'
            ));

        $insertId = $insert->execute();

        // start Movimiento Detalle
        $productos = $request->getParam('productos');
        foreach ($productos as $producto) {
            //if ($producto['total'] > 0) {
            $idProducto = $producto['IdProducto'];
            $cantidad = $producto['cantidad'];
            $tieneIgv = 0;
            $precio = $producto['precio'];
            $idLote = 0;
            $precio = $precio * $tipoCambio;


            //
            // Obtener el verdadero ID del producto
            // en la base de datos de destino
            //
            $select = "SELECT * FROM Gen_Producto
        WHERE Producto = '" . $producto['Producto']
                . "' LIMIT 1";

            $stmt = $this->db_destino->query($select);
            $stmt->execute();
            $prod = $stmt->fetch();

            if (empty($prod)) {
                $insert = $this->db_destino->insert(array('IdProductoMarca', 'IdProductoFormaFarmaceutica', 'IdProductoModelo', 'IdProductoMedicion', 'IdProductoCategoria', 'Producto', 'FechaReg', 'ControlaStock', 'PorcentajeUtilidad', 'PrecioCosto', 'Anulado', 'EsPadre', 'StockMinimo', 'CodigoBarra'))
                    ->into('Gen_Producto')
                    ->values(array(0, 1, 0, 6, 0, $producto['Producto'], getNow(), 1, $producto['PorcentajeUtilidad'], $precio, 0, 0, 0, $producto['CodigoBarra']));
                $idProducto = $insert->execute();
            } else {
                $idProducto = $prod['IdProducto'];
                $precio = $prod['PrecioCosto'];
            }

            $precio = floatval($precio);

            $insert = $this->db_destino->insert(array('hashMovimiento', 'IdProducto', 'Cantidad', 'TieneIgv', 'Precio', 'IdLote'))
                ->into('Lo_MovimientoDetalle')
                ->values(array($hash, $idProducto, $cantidad, $tieneIgv, $precio, $idLote));
            $insert->execute();
        }

        $data = array(
            "hash" => $hash
        );



        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/venta/detalle/comprobante/guia', function (Request $request, Response $response) {
    $idDocVenta = $request->getParam('idDocVenta');
    try {


        $select = "SELECT Ve_DocVentaDet.*,
  Ve_DocVenta.IdCliente,
    Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica,Gen_ProductoMarca.ProductoMarca,Gen_ProductoMedicion.ProductoMedicion,
    Gen_Producto.*,
     Ve_DocVentaDet.Cantidad AS cantidad,
    Ve_DocVentaDet.Precio AS precio, 
    Ve_DocVentaDet.Precio as PrecioContado, 
    Ve_DocVentaDet.Descuento AS descuento,
    Ve_DocVentaDet.Precio AS PrecioCosto,
    
    0 AS PorcentajeUtilidad
    FROM Ve_DocVentaDet 
    INNER JOIN Ve_DocVenta ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.IdDocVenta
    INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto 
    INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
    INNER JOIN Gen_ProductoFormaFarmaceutica ON Gen_Producto.IdProductoFormaFarmaceutica = Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica
    INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
    WHERE Ve_DocVenta.idDocVenta=$idDocVenta  ORDER BY Ve_DocVentaDet.IdDocVentaDet DESC ";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['err' => $e->getMessage()], 500);
        //throw $th;
    }
});

$app->get('/ventas/remision/listado', function (Request $request, Response $response) {
    $select = "SELECT * FROM Ve_Remision WHERE estado = 1 ORDER BY creted_at DESC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});
$app->get('/venta/remision/generar_pdf', function (Request $request, Response $response) {
    try {


        $IdDocVentaRemision = $request->getParam('IdDocVentaRemision');

        // Obtener los datos necesarios
        $select = "SELECT 
            IdDocVentaRemision,
            FechaReg,
            date_format(FechaTraslado, '%Y-%m-%d') as FechaTraslado,
            Observacion,
            CodUndPesoBruto,
            PesoBruto,
            CodModalidadTraslado,
            CodMotivoTraslado,
            DescripcionMotivoTraslado,
            CodUbigeoOrigen,
            DireccionOrigen,
            DocumentoReferencia,
            NroDocumentoCliente,
            RazonSocialCliente,
            DireccionCliente,
            CodUbigeoDestino,
            DireccionDestino,
            NroDocumentoTransportista,
            RazonSocialTransportista,
            PlacaVehiculo,
            PlacaCarreta,
            NroDocChofer,
            LicenciaChofer,
            NombresChofer,
            ApellidosChofer,
            Serie,
            LPAD(Numero,5,'0') AS Numero,
            TotalBultos,
            Estado,
            Hash_cpe,
            Hash_cdr,Msj_sunat,
            NumTicket
            FROM `Ve_DocVentaRemision` WHERE IdDocVentaRemision = :IdDocVentaRemision";

        $stmt = $this->db->prepare($select);
        $stmt->bindParam(':IdDocVentaRemision', $IdDocVentaRemision, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();

        // Verificación de resultados
        if (!$data) {
            return $response->withJson(['error' => 'Datos no encontrados'], 404);
        }

        $select = "SELECT 

            Gen_Producto.CodigoBarra,
            Gen_Producto.Producto,
            Gen_ProductoMedicion.ProductoMedicion as ProductoMedicion,
            Ve_DocVentaRemisionDet.Cantidad as Cantidad
            FROM Ve_DocVentaRemisionDet
            JOIN Gen_Producto ON Gen_Producto.IdProducto  = Ve_DocVentaRemisionDet.IdProducto
            JOIN Gen_ProductoMarca ON Gen_ProductoMarca.IdProductoMarca =Gen_Producto.IdProductoMarca 
            JOIN Gen_ProductoMedicion ON Gen_ProductoMedicion.IdProductoMedicion = Gen_Producto.IdProductoMedicion
            WHERE IdDocVentaRemision =$IdDocVentaRemision
            ORDER BY Ve_DocVentaRemisionDet.IdDocVentaRemisionDet DESC";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $productos = $stmt->fetchAll();


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
            'IdDocVentaRemision' => $data[0]['IdDocVentaRemision'],
            'FechaReg' => $data[0]['FechaReg'],
            'FechaTraslado' => $data[0]['FechaTraslado'],
            'Observacion' => $data[0]['Observacion'],
            'CodUndPesoBruto' => $data[0]['CodUndPesoBruto'],
            'PesoBruto' => $data[0]['PesoBruto'],
            'CodModalidadTraslado' => $data[0]['CodModalidadTraslado'],
            'CodMotivoTraslado' => $data[0]['CodMotivoTraslado'],
            'DescripcionMotivoTraslado' => $data[0]['DescripcionMotivoTraslado'],
            'CodUbigeoOrigen' => $data[0]['CodUbigeoOrigen'],
            'DireccionOrigen' => $data[0]['DireccionOrigen'],
            'DocumentoReferencia' => $data[0]['DocumentoReferencia'],
            'NroDocumentoCliente' => $data[0]['NroDocumentoCliente'],
            'RazonSocialCliente' => $data[0]['RazonSocialCliente'],
            'DireccionCliente' => $data[0]['DireccionCliente'],
            'CodUbigeoDestino' => $data[0]['CodUbigeoDestino'],
            'DireccionDestino' => $data[0]['DireccionDestino'],
            'NroDocumentoTransportista' => $data[0]['NroDocumentoTransportista'],
            'RazonSocialTransportista' => $data[0]['RazonSocialTransportista'],
            'PlacaVehiculo' => $data[0]['PlacaVehiculo'],
            'PlacaCarreta' => $data[0]['PlacaCarreta'],
            'NroDocChofer' => $data[0]['NroDocChofer'],
            'LicenciaChofer' => $data[0]['LicenciaChofer'],
            'NombresChofer' => $data[0]['NombresChofer'],
            'ApellidosChofer' => $data[0]['ApellidosChofer'],
            'Serie' => $data[0]['Serie'],
            'Numero' => $data[0]['Numero'],
            'TotalBultos' => $data[0]['TotalBultos'],
            'Estado' => $data[0]['Estado'],
            'Hash_cpe' => $data[0]['Hash_cpe'],
            'Hash_cdr' => $data[0]['Hash_cdr'],
            'NumTicket'   => $data[0]['NumTicket'],
            'Productos' => $productos
        );
        $rutapdf = $_SERVER['HTTP_HOST'] . '/api/sunat/plugins/dompdf/';

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $rutapdf . "guia_remision.php?",
            CURLOPT_USERAGENT => "Codular Sample cURL Request",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($datosImprimir)
        ));
        $resp = curl_exec($curl);
        curl_close($curl);
        // Preparar los datos para la inserción
        $nombreArchivo = 'GUIA_REMISION-' . $data[0]['Serie'] . '-' .  $data[0]['Numero'];
        $ruta = 'https://' . $_SERVER['HTTP_HOST'] . "/api/sunat/api_cpe/" . 'PRODUCCION' . "/" . NRO_DOCUMENTO_EMPRESA . "/" . $nombreArchivo . '.pdf';

        try {
            $insert = "INSERT INTO Ve_Remision 
                   (Numero, Serie, DocumentoReferencia, NombreArchivo, URL, estado)
                   VALUES (:Numero, :Serie, :DocumentoReferencia, :NombreArchivo, :URL, :estado)";

            $stmt = $this->db->prepare($insert);
            $stmt->bindParam(':Numero', $data[0]['Numero'], PDO::PARAM_STR);
            $stmt->bindParam(':Serie', $data[0]['Serie'], PDO::PARAM_STR);
            $stmt->bindParam(':DocumentoReferencia', $data[0]['DocumentoReferencia'], PDO::PARAM_STR);
            $stmt->bindParam(':NombreArchivo', $nombreArchivo, PDO::PARAM_STR);
            $stmt->bindParam(':URL', $ruta, PDO::PARAM_STR);
            $stmt->bindValue(':estado', 1, PDO::PARAM_INT);

            $inserted = $stmt->execute();

            if ($inserted) {
                return $response->withJson(['url' => $ruta]);
            } else {
                return $response->withJson(['error' => 'Error al insertar los datos'], 500);
            }
        } catch (Exception $e) {
            return $response->withJson(['error' => $e->getMessage()], 500);
        }
    } catch (\Throwable $th) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/ubigeo/listado', function (Request $request, Response $response) {
    try {
        $select = "SELECT * FROM Ubigeo ORDER BY nivel";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['err' => $e->getMessage()], 500);
        //throw $th;
    }
});

$app->get('/cierrecaja/reporte', function (Request $request, Response $response, array $args) {
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaDesde = $request->getParam('fechaDesde');
    $fechaHasta = $request->getParam('fechaHasta');
    $select = "SELECT Cb_CierreCaja.*, Seg_Usuario.IdAlmacen, Lo_Almacen.Almacen FROM Cb_CierreCaja 
        INNER JOIN Seg_Usuario ON Cb_CierreCaja.UsuarioReg = Seg_Usuario.Usuario 
        INNER JOIN Lo_Almacen ON Seg_Usuario.IdAlmacen = Lo_Almacen.IdAlmacen";
    if ($idAlmacen) {
        $select .= " WHERE Cb_CierreCaja.UsuarioReg IN ( SELECT Usuario FROM Seg_Usuario WHERE IdAlmacen = '$idAlmacen' ) ";
    } else {
        $select .= " WHERE Cb_CierreCaja.UsuarioReg IN ( SELECT Usuario FROM Seg_Usuario ) ";
    }
    $select .= " AND FechaCierre >= '$fechaDesde' AND FechaCierre <= '$fechaHasta' ORDER BY IdCierreCaja DESC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->get('/buscarproductos/nombrexml', function (Request $request, Response $response, array $args) {

    $Producto = $request->getParam('Producto');

    if (empty($Producto)) {
        return $response->withJson(['error' => 'Debe enviar el parámetro Producto'], 400);
    }


    $limpiarTexto = function ($texto) {
        $texto = mb_strtolower($texto, 'UTF-8');
        $texto = preg_replace('/[^\p{L}\p{N}\s]/u', '', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        return trim($texto);
    };

    $nombreBuscado = $limpiarTexto($Producto);

    $select = "SELECT * FROM Gen_Producto";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $productos = $stmt->fetchAll();

    $coincidencias = [];

    foreach ($productos as $p) {
        $nombreProducto = $limpiarTexto($p['Producto']);
        if (strpos($nombreProducto, $nombreBuscado) !== false || strpos($nombreBuscado, $nombreProducto) !== false) {
            $p['porcentaje_similitud'] = 100;
            $p['distancia'] = 0;
            $coincidencias[] = $p;
            continue;
        }
        similar_text($nombreBuscado, $nombreProducto, $porcentaje);
        $distancia = levenshtein($nombreBuscado, $nombreProducto);

        if ($porcentaje >= 80) {
            $p['porcentaje_similitud'] = round($porcentaje, 2);
            $p['distancia'] = $distancia;
            $coincidencias[] = $p;
        }
    }

    if (count($coincidencias) > 0) {
        usort($coincidencias, function ($a, $b) {
            if ($a['porcentaje_similitud'] == $b['porcentaje_similitud']) {
                return $a['distancia'] - $b['distancia'];
            }
            return $b['porcentaje_similitud'] - $a['porcentaje_similitud'];
        });

        return $response->withJson($coincidencias);
    } else {
        return $response->withJson([]);
    }
});

$app->post('/movimientoxml', function (Request $request, Response $response, array $args) {
    $rucProveedor = $request->getParam('rucProveedor');
    $nameProveedor = $request->getParam('nameProveedor');
    $direccionProveedor = $request->getParam('direccionProveedor');
    $tipoCambio = $request->getParam('TipoCambio');

    try {
        // VALIDANDO SI EXISTE EL PROVEEDOR + REGISTRAR SI NO EXISTE
        $select = "SELECT IdProveedor FROM Lo_Proveedor WHERE Ruc = :ruc";
        $stmt = $this->db->prepare($select);
        $stmt->bindParam(':ruc', $rucProveedor);
        $stmt->execute();

        if (!$data = $stmt->fetch()) {
            $insert = $this->db->insert(array(
                'Proveedor',
                'Ruc',
                'Direccion',
                'Observacion',
                'FechaReg',
                'Telefono'
            ))
                ->into('Lo_Proveedor')
                ->values(array(
                    $nameProveedor,
                    $rucProveedor,
                    $direccionProveedor,
                    null,
                    getNow(),
                    null
                ));

            $idProveedor = $insert->execute();
        } else {
            $idProveedor = $data['IdProveedor'];
        }

        // MOVIMIENTOS
        $select = "SELECT * FROM Lo_Movimiento
        WHERE IdMovimientoTipo = '" . $request->getParam('movimientoTipo')['IdMovimientoTipo']
            . "' AND IdProveedor = '" . $request->getParam('proveedor')['IdProveedor']
            . "' AND Serie = '" . $request->getParam('Serie')
            . "' AND Numero = '" . $request->getParam('Numero') . "'";

        $stmt = $this->db->query($select);
        $stmt->execute();

        if (count($stmt->fetchAll())) {
            $data = array(
                "error" => 'Error: Movimiento duplicado'
            );
            return $response->withJson($data);
        }
        // end verificar Movimiento

        // start Insertar Movimiento
        $idMovimientoTipo = $request->getParam('movimientoTipo')['IdMovimientoTipo'];
        $idProveedor = $idProveedor;
        $serie = $request->getParam('Serie');
        $numero = $request->getParam('Numero');
        //$movimientoFecha = $request->getParam('movimiento')['MovimientoFecha'];
        $movimientoFecha = getNow();
        $almacenOrigen = $request->getParam('almacenOrigen')['IdAlmacen'];
        $almacenDestino = $request->getParam('almacenDestino')['IdAlmacen'];
        $anulado = 0;
        $fechaReg = getNow();
        $user = $request->getParam('user');
        $usuarioReg = 'xx';
        if (isset($_SESSION['user'])) {
            $usuarioReg = $_SESSION['user'];
        }
        $usuarioReg = $user ? $user : $usuarioReg;
        $hash = time();
        //$fechaStock = $request->getParam('movimiento')['FechaStock'];
        $fechaStockSolicitada = isset($movimientoParam['FechaStock']) && $movimientoParam['FechaStock']
        ? $movimientoParam['FechaStock']
        : getNow();
    $fechaStock = $normalizarFechaMovimiento($fechaStockSolicitada);
    if (!$movimientoFecha || !$fechaStock) {
        return $response->withJson(array(
            'error' => 'La fecha contable o la fecha de stock no tiene un formato valido.'
        ), 422);
    }
        $percepcion = 0;
        $esCredito = $request->getParam('EsCredito');
        $fechaVenCredito = $request->getParam('FechaVenCredito') ? $request->getParam('FechaVenCredito') : NULL;
        $fechaPeriodoTributario = $request->getParam('FechaPeriodoTributario');
        $tipoCambio = $request->getParam('TipoCambio') ? $request->getParam('TipoCambio') : 1;
        $moneda = $request->getParam('Moneda');

        $insert = $this->db->insert(array(
            'IdMovimientoTipo',
            'IdProveedor',
            'Serie',
            'Numero',
            'MovimientoFecha',
            'IdAlmacenOrigen',
            'IdAlmacenDestino',
            'Anulado',
            'FechaReg',
            'UsuarioReg',
            'Hash',
            'FechaStock',
            'Percepcion',
            'EsCredito',
            'FechaVenCredito',
            'FechaPeriodoTributario',
            'TipoCambio',
            'Moneda'
        ))
            ->into('Lo_Movimiento')
            ->values(array(
                $idMovimientoTipo,
                $idProveedor,
                $serie,
                $numero,
                $movimientoFecha,
                $almacenOrigen,
                $almacenDestino,
                $anulado,
                $fechaReg,
                $usuarioReg,
                $hash,
                $fechaStock,
                $percepcion,
                $esCredito,
                $fechaVenCredito,
                $fechaPeriodoTributario,
                $tipoCambio,
                $moneda
            ));

        $insertId = $insert->execute();

        $data = array(
            "hash" => $hash
        );
        // end insertar Movimiento

        if ($moneda == 'USD') {
            $update = "UPDATE Gen_Moneda SET TipoCambio=$tipoCambio WHERE Moneda = 'USD'";
            $stmt = $this->db->prepare($update);
            $updated = $stmt->execute();
        }

        $productos = $request->getParam('productos');
        foreach ($productos as $producto) {


            if (isset($producto['IdProducto']) && !empty($producto['IdProducto'])) {

                $idProducto = $producto['IdProducto'];
            } else {
                try {
                    $sql = "SELECT MAX(CAST(CodigoBarra AS UNSIGNED)) + 1 AS NuevoCodigoBarra FROM Gen_Producto";
                    $stmt = $this->db->query($sql);
                    $result = $stmt->fetch();


                    $codigoBarra = $result['NuevoCodigoBarra'] ?? 1;
                    $idProductoFormaFarmaceutica = 1;
                    $idProductoMarca = $producto['IdProductoMarca'];
                    $idProductoMedicion = 6;
                    $idProductoCategoria = 28;
                    $hashProducto = time();
                    $precio = $producto['PrecioCosto'];
                    $precio = $precio * $tipoCambio;

                    $insert = $this->db->insert(array(
                        'IdProductoMarca',
                        'IdProductoFormaFarmaceutica',
                        'IdProductoMedicion',
                        'IdProductoCategoria',
                        'Producto',
                        'FechaReg',
                        'ControlaStock',
                        'PorcentajeUtilidad',
                        'PrecioCosto',
                        'PrecioContado',
                        'Hash',
                        'Anulado',
                        'EsPadre',
                        'StockMinimo',
                        'CodigoBarra'
                    ))
                        ->into('Gen_Producto')
                        ->values(array(
                            $idProductoMarca,
                            $idProductoFormaFarmaceutica,
                            $idProductoMedicion,
                            $idProductoCategoria,
                            $producto['Producto'],
                            getNow(),
                            1,
                            $producto['PorcentajeUtilidad'] ? $producto['PorcentajeUtilidad'] : 0,
                            $precio,
                            $precioContado,
                            $hashProducto,
                            0,
                            0,
                            0,
                            $codigoBarra
                        ));
                    $idProducto = $insert->execute();
                } catch (Exception $e) {
                    return $response->withJson(['error' => $e->getMessage()], 500);
                }
            }


            try {
                $cantidad = $producto['cantidad'];
                $tieneIgv = isset($producto['TieneIgv']) ? $producto['TieneIgv'] : 0;
                $precio = $producto['PrecioCosto'];
                $PrecioContado = $producto['PrecioContado'];
                $idLote = isset($producto['IdLote']) ? $producto['IdLote'] : 0;
                $descripcion = isset($producto['Descripcion']) ? $producto['Descripcion'] : '';
                $TotalFlete = isset($producto['FLETE']) ? $producto['FLETE'] : 0;
                $flete = ($producto['FLETE'] / $producto['cantidad']) * $tipoCambio;
                $nuevoPrecioContado = $producto['nuevoPrecioContado'];
                if ($tieneIgv === 1) {
                    $precio += $precio * 0.18;
                }

                $insert = $this->db->insert(array('hashMovimiento', 'IdProducto', 'Cantidad', 'TieneIgv', 'Precio', 'IdLote', 'Descripcion'))
                    ->into('Lo_MovimientoDetalle')
                    ->values(array($hash, $idProducto, $cantidad, $tieneIgv, $precio, $idLote, $descripcion));
                $insert->execute();

                $dml = "UPDATE Gen_Producto SET TotalCompras=TotalCompras+$cantidad ,PrecioContado = $PrecioContado  WHERE IdProducto=$idProducto";
                $stmt = $this->db->prepare($dml);
                $updated = $stmt->execute();


                // $update = $this->db->update(array(
                //     'PrecioContado' => $PrecioContado,
                // ))
                //     ->table('Gen_Producto')
                //     ->where('IdProducto', '=', $idProducto);
                // $update->execute();
            } catch (Exception $e) {
                return $response->withJson(['error' => $e->getMessage()], 500);
            }
        }


        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/proformas/favoritas', function (Request $request, Response $response) {
    try {
        $filter = $request->getParam('filter');
        $select = "
        SELECT Ve_Proforma.IdProforma, Ve_Proforma.IdCliente,
         Ve_Proforma.Anio, Ve_Proforma.Numero, Ve_Proforma.FechaReg,
         Ve_Proforma.UsuarioReg,  Ve_DocVentaCliente.Cliente,
         Ve_Proforma.EsFavoritoFlag,
          Ve_Proforma.Descripcion,
        Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.Direccion, IFNULL(ROUND(SUM(Ve_ProformaDet.Cantidad * Ve_ProformaDet.Precio - IFNULL(Ve_ProformaDet.Descuento,0)),2),0) AS Total
        FROM Ve_Proforma
        INNER JOIN Ve_ProformaDet ON Ve_Proforma.IdProforma = Ve_ProformaDet.IdProforma
        LEFT JOIN Ve_DocVentaCliente ON Ve_Proforma.IdCliente = Ve_DocVentaCliente.IdCliente
        WHERE Ve_Proforma.Anulado = 0
        AND Ve_Proforma.EsFavoritoFlag = 1 ";

        $select .= " GROUP BY Ve_Proforma.IdProforma ORDER BY Ve_Proforma.IdProforma DESC";


        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->put('/proforma/cambiarfavorito', function (Request $request, Response $response) {
    try {
        $EsFavoritoFlag = $request->getParam('EsFavoritoFlag');
        $IdProforma = $request->getParam('IdProforma');
        $update = "UPDATE Ve_Proforma  SET EsFavoritoFlag=$EsFavoritoFlag where IdProforma= $IdProforma";
        $stmt = $this->db->prepare($update);
        $updated = $stmt->execute();
        return $response->withJson($updated);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->put('/proforma/anular/{id}', function (Request $request, Response $response, $args) {

    $idProforma = $args['id'];

    if (!($idProforma > 0)) {
        return $response->withJson([
            "success" => false,
            "msg" => "Error: No se especificó la proforma"
        ]);
    }

    $update = $this->db->update([
        "Anulado" => 1
    ])
        ->table('Ve_Proforma')
        ->where('IdProforma', '=', $idProforma);

    $update->execute();

    return $response->withJson([
        "success" => true,
        "msg" => "Proforma anulada correctamente"
    ]);
});

$app->get('/cliente/imprimir/deudas_cliente', function (Request $request, Response $response, array $args) use ($app) {

    $idalmacen = $request->getParam('idalmacen');
    $fechaInicio = $request->getParam('fechaDesde');
    $fechaFin = $request->getParam('fechaHasta');
    $q = $request->getParam('q');
    $res = $app->subRequest('GET', 'cliente/deudas', 'q=' . $q . '&idalmacen=' . $idalmacen . '&limit=' . '' . '&fechaDesde=' . $fechaInicio . '&fechaHasta=' . $fechaFin);



    $deudacliente = (string) $res->getBody();
    $deudacliente = json_decode($deudacliente, true);

    foreach ($deudacliente as $deuda) {

        if (isset($deuda['Cliente'])) {
            $clientes[] = $deuda['Cliente'];
            $direccion[] = $deuda['Direccion'];
            $dniruc[] = $deuda['DniRuc'];
        }
    };

    $clientes_unicos = array_unique($clientes);
    $cliente = null;
    if (count($clientes_unicos) == 1) {

        $cliente = $clientes_unicos[0];
        $direccion =  $direccion[0];
        $dniruc = $dniruc[0];
    } else {

        $cliente = "CLIENTES VARIOS";
        $direccion = "AV. HABILITACION URBANA NRO. 578 URB. HABILITACION URBANA MUNICIPAL UCAYALI - CORONEL PORTILLO - MANANTAY";
        $dniruc = "00000000";
    }

    // return $response->withJson($data);
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
        "txtTEL_EMPRESA" => TELEFONOS,
        "txtUSUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
        "txtPASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
        "txtPAS_FIRMA" => PAS_FIRMA,
        "TIPO_PROCESO" => TIPO_PROCESO,
        "CLIENTE" => $cliente,
        "DIRECCION" => $direccion,
        "DNI_RUC" => $dniruc,
        'Deudas' => $deudacliente
    );
    $rutapdf = $_SERVER['HTTP_HOST'] . '/api/sunat/plugins/dompdf/';

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $rutapdf . "reporte_deuda_cliente.php?",
        CURLOPT_USERAGENT => "Codular Sample cURL Request",
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($datosImprimir)
    ));
    $resp = curl_exec($curl);
    curl_close($curl);
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    // $nombreArchivo = 'GUIA_REMISION-' . $data[0]['Serie'] . '-' .  $data[0]['Numero'];
    $ruta = 'https://' . $_SERVER['HTTP_HOST'] . "/api/sunat/api_cpe/" . 'PRODUCCION/' . NRO_DOCUMENTO_EMPRESA . "/reporte_deuda_cliente.pdf";
    return $response->withJson(['url' => $ruta]);
});

function ListarRegNovExportar($db, $fechaIni, $fechaFin, $declarado, $proveedor)
{
    // call SbLo_RegMovimiento($declarado, '$fechaIni', '$fechaFin');
    $Ssql = "SELECT
		Lo_Movimiento.`Hash` as IdMovimiento,
		Lo_Movimiento.MovimientoFecha,
		Lo_Movimiento.IdMovimientoTipo,
		Lo_MovimientoTipo.Tipo,
		Lo_MovimientoTipo.CodSunat,
		Lo_MovimientoTipo.TipoMovimiento,
		Lo_Movimiento.Serie,
		Lo_Movimiento.EsCredito,
		Lo_Movimiento.FechaVenCredito,
		Lo_Movimiento.Numero,
		Lo_Movimiento.FechaPeriodoTributario,
		Lo_Proveedor.Proveedor,
		CASE WHEN Lo_Movimiento.IdAlmacenOrigen>0 THEN
			(Select Lo_Almacen.Almacen From Lo_Almacen Where Lo_Almacen.IdAlmacen=Lo_Movimiento.IdAlmacenOrigen)
		ELSE
			'-'
		END AS AlmacenOrigen,
		CASE WHEN Lo_Movimiento.IdAlmacenDestino>0 THEN
			(Select Lo_Almacen.Almacen From Lo_Almacen Where Lo_Almacen.IdAlmacen=Lo_Movimiento.IdAlmacenDestino)
		ELSE
			'-'
		END AS AlmacenDestino,
		Lo_Movimiento.Observacion,
		Lo_Movimiento.Anulado,
		(SELECT
	Sum(ROUND(Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio,2)) as SUBTOTAL
	FROM
	Lo_MovimientoDetalle
	WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as SUBTOTAL,
	(SELECT
	Sum(CASE WHEN Lo_MovimientoDetalle.TieneIgv=1 THEN
		ROUND((Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio)*
		(Select Igv From GEN_EMPRESA),2)
	ELSE
		0
	END) as IGV
	FROM
	Lo_MovimientoDetalle
	WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as IGV,
	(SELECT
	Sum(ROUND(Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio,2)) +
	 Sum(ROUND(CASE WHEN ISNULL(Lo_MovimientoDetalle.ISC)=1 THEN  0 ELSE Lo_MovimientoDetalle.ISC END,2))
	+ SUM(ROUND(CASE WHEN ISNULL(Lo_MovimientoDetalle.FLETE)=1 THEN  0 ELSE Lo_MovimientoDetalle.FLETE END,2))
	+CASE WHEN ISNULL(Lo_Movimiento.Percepcion)=1 THEN 0 ELSE Lo_Movimiento.Percepcion END
	+Sum(CASE WHEN Lo_MovimientoDetalle.TieneIgv=1 THEN
		ROUND((Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio)*
		(Select Igv From GEN_EMPRESA),2)
	ELSE
		0
	END) as TOTAL
	FROM
	Lo_MovimientoDetalle
	WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as TOTAL,
		Lo_Movimiento.FechaReg,
		Lo_Movimiento.UsuarioReg,
		Lo_Movimiento.FechaMod,
		Lo_Movimiento.UsuarioMod,
	(SELECT SUM(ROUND(CASE WHEN ISNULL(Lo_MovimientoDetalle.ISC)=1 THEN  0 ELSE Lo_MovimientoDetalle.ISC END,2)) FROM Lo_MovimientoDetalle WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as ISC,
	(SELECT SUM(ROUND(CASE WHEN ISNULL(Lo_MovimientoDetalle.FLETE)=1 THEN  0 ELSE Lo_MovimientoDetalle.FLETE END,2)) FROM Lo_MovimientoDetalle WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as FLETE,
	CASE WHEN ISNULL(Lo_Movimiento.Percepcion)=1 THEN 0 ELSE Lo_Movimiento.Percepcion END AS Percepcion,
	
	Lo_Movimiento.TipoCambio,
	Lo_Movimiento.Moneda
	
	FROM
		Lo_Movimiento
		INNER JOIN Lo_MovimientoTipo On Lo_Movimiento.IdMovimientoTipo=Lo_MovimientoTipo.IdMovimientoTipo
		INNER JOIN Lo_Proveedor On Lo_Movimiento.IdProveedor=Lo_Proveedor.IdProveedor
	WHERE
		Lo_MovimientoTipo.VaRegCompra=$declarado and Lo_Movimiento.MovimientoFecha BETWEEN CAST('$fechaIni' AS DATETIME) and  CAST('$fechaFin' AS DATETIME)";

    if ($proveedor) {
        $Ssql .= " AND Lo_Movimiento.IdProveedor=$proveedor";
    }
    $Ssql .= " ORDER BY Lo_Movimiento.MovimientoFecha DESC;";
    //echo $Ssql;
    //exit();
    $stmt = $db->query($Ssql);
    $stmt->execute();
    return $stmt->fetchAll();
}

$app->get('/productos/anaquel', function (Request $request, Response $response, array $args) {
    $select = "SELECT DISTINCT Anaquel FROM Gen_Producto";
    $select .= " WHERE Gen_Producto.Anaquel LIKE '%" . $request->getParam('q') . "%' ";

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->post('/movimiento/eliminarCajaBanco', function (Request $request, Response $response, array $args) {
    try {
        $idCajaBanco = $request->getParam('IdCajaBanco');

        $Ssql = "UPDATE Cb_LetraCaja SET 
                IdCajaBanco = 0,
                Cancelado = 0
                WHERE IdCajaBanco = $idCajaBanco";
        $stmt = $this->db->query($Ssql);
        $stmt->execute();
        $data = $stmt->fetch();

        $Ssql = "DELETE FROM Cb_CajaBancoDet WHERE IdCajaBanco = $idCajaBanco";
        $stmt = $this->db->query($Ssql);
        $stmt->execute();
        $data = $stmt->fetch();

        $Ssql = "DELETE FROM Cb_CajaBanco WHERE IdCajaBanco = $idCajaBanco";
        $stmt = $this->db->query($Ssql);
        $stmt->execute();
        $data = $stmt->fetch();
        return $response->withJson(true);
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], 500);
    }
});


$app->get('/productos_modelo', function (Request $request, Response $response, array $args) {
    try {
        $modelo = $request->getParam('ProductoModelo');
        $select = "
            SELECT
            Gen_Producto.IdProducto,
            Gen_Producto.CodigoBarra,
            Gen_Producto.Producto,
            Gen_Producto.ProductoModelo,
            Gen_Producto.PrecioContado,
            Gen_Producto.Color,
            Gen_Producto.Genero,
            Gen_Producto.Botapie,
            Gen_Producto.Imagen,
            Gen_ProductoTalla.ProductoTalla,
            Gen_ProductoMarca.ProductoMarca,
            COALESCE(
                (SELECT TotalCompras - TotalVentas 
                 FROM Gen_Producto_Totales 
                 WHERE Gen_Producto_Totales.IdProducto = Gen_Producto.IdProducto 
                 AND Gen_Producto_Totales.IdAlmacen = 1
                 LIMIT 1)
            , 0) AS stock
            FROM Gen_Producto 
            LEFT JOIN Gen_ProductoTalla ON Gen_ProductoTalla.IdProductoTalla = Gen_Producto.IdProductoTalla
            LEFT JOIN Gen_ProductoMarca ON Gen_ProductoMarca.IdProductoMarca = Gen_Producto.IdProductoMarca
            WHERE Gen_Producto.ProductoModelo = '$modelo'
            AND Gen_Producto.Anulado = 0
            ORDER BY stock DESC";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], 500);
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


$app->get('/productos/CodeBarModelo', function (Request $request, Response $response, array $args) {
    $CodeBarModelo = $request->getParam('CodeBarModelo');

    $select = "SELECT Gen_Producto.*, Gen_ProductoCategoria.ProductoCategoria, Gen_ProductoMarca.ProductoMarca,
    Gen_ProductoTalla.ProductoTalla, Gen_ProductoMedicion.ProductoMedicion,
    COALESCE(Gen_Producto.TotalCompras,0) - COALESCE(Gen_Producto.TotalVentas,0) AS stock
    FROM Gen_Producto 
    INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
    INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
    INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
    LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla ";


    $select .= " WHERE Gen_Producto.CodeBarModelo = '" . $CodeBarModelo . "'";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});


function neuroMailAdmin($db) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $user = trim((string) ($_SESSION['user'] ?? ''));
    if ($user === '') return false;
    $stmt = $db->prepare("SELECT IdUsuarioPerfil FROM Seg_Usuario WHERE LOWER(Usuario)=LOWER(?) AND (Anulado<>1 OR Anulado IS NULL) LIMIT 1");
    $stmt->execute([$user]);
    $row = $stmt->fetch();
    return strtolower($user) === 'superadmin' || ($row && intval($row['IdUsuarioPerfil']) === 1);
}
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
function neuroMailSaveConfig($db,$config) {
    foreach ($config as $key=>$value) {
        $find=$db->prepare("SELECT IdEmpresaConfig FROM Gen_EmpresaConfig WHERE IdEmpresa=1 AND MetaKey=? LIMIT 1");$find->execute([$key]);$row=$find->fetch();
        if ($row) {$stmt=$db->prepare("UPDATE Gen_EmpresaConfig SET MetaValue=? WHERE IdEmpresaConfig=?");$stmt->execute([$value,$row['IdEmpresaConfig']]);}
        else {$stmt=$db->prepare("INSERT INTO Gen_EmpresaConfig (IdEmpresa,MetaKey,MetaValue) VALUES (1,?,?)");$stmt->execute([$key,$value]);}
    }
}

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
function neuroSmtpRead($socket) { $text=''; while (($line=fgets($socket,515))!==false) { $text.=$line; if (strlen($line)<4 || $line[3]!=='-') break; } return $text; }
function neuroSmtpCmd($socket,$command,$codes) { if ($command!==null) fwrite($socket,$command."\r\n"); $reply=neuroSmtpRead($socket); $code=intval(substr($reply,0,3)); if (!in_array($code,(array)$codes,true)) throw new Exception('SMTP rechazó la operación ('.$code.').'); return $reply; }
function neuroSmtpSend($config,$to,$subject,$html,$pdf=null,$filename='comprobante.pdf') {
    $host=trim($config['smtp_host']??'');$port=intval($config['smtp_port']??587);$security=strtolower($config['smtp_security']??'tls');
    if (!$host || empty($config['smtp_user']) || empty($config['smtp_password']) || empty($config['smtp_from_email'])) throw new Exception('La configuración SMTP está incompleta.');
    $target=($security==='ssl'?'ssl://':'').$host.':'.$port;$context=stream_context_create(['ssl'=>['verify_peer'=>true,'verify_peer_name'=>true,'allow_self_signed'=>false]]);
    $socket=@stream_socket_client($target,$errno,$errstr,15,STREAM_CLIENT_CONNECT,$context);if(!$socket)throw new Exception('No se pudo conectar al servidor SMTP: '.$errstr);stream_set_timeout($socket,20);
    try { neuroSmtpCmd($socket,null,220); neuroSmtpCmd($socket,'EHLO neuroapp.local',250); if($security==='tls'){neuroSmtpCmd($socket,'STARTTLS',220);if(!stream_socket_enable_crypto($socket,true,STREAM_CRYPTO_METHOD_TLS_CLIENT))throw new Exception('No se pudo activar TLS.');neuroSmtpCmd($socket,'EHLO neuroapp.local',250);} neuroSmtpCmd($socket,'AUTH LOGIN',334);neuroSmtpCmd($socket,base64_encode($config['smtp_user']),334);neuroSmtpCmd($socket,base64_encode($config['smtp_password']),235);neuroSmtpCmd($socket,'MAIL FROM:<'.$config['smtp_from_email'].'>',250);neuroSmtpCmd($socket,'RCPT TO:<'.$to.'>',[250,251]);neuroSmtpCmd($socket,'DATA',354);
        $boundary='=_NeuroApp_'.bin2hex(random_bytes(8));$headers=['From: '.($config['smtp_from_name']??'Neuro App').' <'.$config['smtp_from_email'].'>','To: <'.$to.'>','Subject: =?UTF-8?B?'.base64_encode($subject).'?=','MIME-Version: 1.0','Content-Type: multipart/mixed; boundary="'.$boundary.'"'];$body='--'.$boundary."\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n".chunk_split(base64_encode($html));if($pdf!==null)$body.='--'.$boundary."\r\nContent-Type: application/pdf; name=\"$filename\"\r\nContent-Disposition: attachment; filename=\"$filename\"\r\nContent-Transfer-Encoding: base64\r\n\r\n".chunk_split(base64_encode($pdf));$body.='--'.$boundary."--\r\n";$message=implode("\r\n",$headers)."\r\n\r\n".$body;neuroSmtpCmd($socket,preg_replace('/^\./m','..',$message)."\r\n.",250);neuroSmtpCmd($socket,'QUIT',221);
    } finally { fclose($socket); }
}
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

require_once __DIR__ . '/kpi.php';

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});




$app->run();
