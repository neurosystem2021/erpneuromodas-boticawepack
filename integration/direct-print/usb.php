<?php
header('Content-Type: application/json; charset=utf-8');

if(session_status()!==PHP_SESSION_ACTIVE)session_start();
if(empty($_SESSION['user'])||(isset($_SESSION['expire'])&&(int)$_SESSION['expire']<time())){http_response_code(401);echo json_encode(['ok'=>false,'mensaje'=>'Inicia sesion.']);exit;}
require_once dirname(__DIR__).'/config/database.php';
require_once dirname(__DIR__).'/api/ali-pos-security.php';
$permission=aliPosCapabilities(appPdoConnection(),$_SESSION['user']);
if(empty($permission['write'])){http_response_code(403);echo json_encode(['ok'=>false,'mensaje'=>'Sin permiso de venta.']);exit;}
if(($_SERVER['HTTP_X_REQUESTED_WITH']??'')!=='XMLHttpRequest'){http_response_code(403);echo json_encode(['ok'=>false,'mensaje'=>'Solicitud de impresion no permitida.']);exit;}
session_write_close();
require_once dirname(__DIR__) . '/clases/BnGeneral.php';
require_once dirname(__DIR__) . '/models/EmpresaConfig.php';
require_once dirname(__DIR__) . '/controllers/NumerosEnLetras/NumerosEnLetras.php';
require_once dirname(__DIR__) . '/api/sunat/plugins/phpqrcode/qrlib.php';
require_once __DIR__ . '/ticket-raster.php';

function respuestaUsb($ok, $mensaje, $codigo = 200)
{
    http_response_code($codigo);
    echo json_encode(['ok' => $ok, 'mensaje' => $mensaje], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respuestaUsb(false, 'Metodo no permitido.', 405);
}

$entrada = json_decode(file_get_contents('php://input'), true);
if (!is_array($entrada)) respuestaUsb(false, 'No se recibieron datos validos para imprimir.', 422);
$idVenta = isset($entrada['IdDocVenta']) ? (int) $entrada['IdDocVenta'] : 0;
if ($idVenta <= 0) respuestaUsb(false, 'No se recibio una venta valida.', 422);

$impresoraSolicitada = isset($entrada['PrinterName']) ? trim((string) $entrada['PrinterName']) : '';
$impresoraSolicitada = preg_replace('/[\x00-\x1F\x7F]/u', '', $impresoraSolicitada);
$impresora = $impresoraSolicitada !== '' ? $impresoraSolicitada : (getenv('NEURO_USB_PRINTER') ?: 'bar');

$resultado = fn_devolverDocVenta('Ve_DocVenta.IdDocVenta=' . $idVenta, '');
$venta = $resultado ? mysqli_fetch_assoc($resultado) : null;
if (!$venta) respuestaUsb(false, 'No se encontro la venta.', 404);

$resultadoDetalle = fn_devolverDocVentaDet('Ve_DocVentaDet.IdDocVenta=' . $idVenta, 'Ve_DocVentaDet.IdDocVentaDet ASC');
$productos = $resultadoDetalle ? mysqli_fetch_all($resultadoDetalle, MYSQLI_ASSOC) : array();
if (!$productos) respuestaUsb(false, 'La venta no tiene productos para imprimir.', 422);

$sqlPagos = "SELECT mp.MetodoPago, pd.Importe, pd.NroTarjeta
    FROM Ve_DocVentaMetodoPagoDet pd
    INNER JOIN Ve_DocVentaMetodoPago mp ON mp.IdMetodoPago = pd.IdMetodoPago
    WHERE pd.IdDocVenta = " . $idVenta . "
    ORDER BY pd.IdMetodoPago ASC";
$resultadoPagos = getSQLResultSet($sqlPagos);
$pagos = $resultadoPagos ? mysqli_fetch_all($resultadoPagos, MYSQLI_ASSOC) : array();

$configuracion = empresaConfigObtener();
$empresa = empresaConfigDatosImpresion($configuracion);
$logoRelativo = ltrim(empresaConfigValor($configuracion, 'logo_ticket', 'resources/images/logo-ticket.png'), '/\\');
$logo = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $logoRelativo);
$resolucionSunat = empresaConfigValor($configuracion, 'resolucion_sunat', 'Autorizado mediante resolucion de intendencia SUNAT.');
$mensajeTicket = empresaConfigValor($configuracion, 'mensaje_ticket', 'Conserve este comprobante para cualquier consulta o reclamo.');
$consultaWeb = empresaConfigValor($configuracion, 'consulta_web', 'http://emision.factura.vip');
$tituloMarca = empresaConfigValor($configuracion, 'titulo_marca_ticket', '');

$totalBruto = 0.0;
$descuento = 0.0;
foreach ($productos as $producto) {
    $totalBruto += isset($producto['TOTAL']) ? (float) $producto['TOTAL'] : (float) $producto['Cantidad'] * (float) $producto['Precio'];
    $descuento += isset($producto['Descuento']) ? (float) $producto['Descuento'] : 0;
}
$total = max(0, $totalBruto - $descuento);
$tieneIgv = !empty($venta['TieneIgv']);
$baseGravada = $tieneIgv ? $total / 1.18 : 0;
$igv = $tieneIgv ? $total - $baseGravada : 0;
$fechaQr = !empty($venta['FechaDoc']) ? date('Y-m-d', strtotime($venta['FechaDoc'])) : date('Y-m-d');
$dniRuc = trim((string) (isset($venta['DniRuc']) ? $venta['DniRuc'] : '00000000'));
$tipoDocumentoCliente = strlen($dniRuc) > 9 ? '6' : '1';
$codigoSunat = str_pad((string) (isset($venta['CodSunat']) ? $venta['CodSunat'] : '03'), 2, '0', STR_PAD_LEFT);
$textoQr = $empresa['ruc'] . '|' . $codigoSunat . '|' . $venta['Serie'] . '|' . $venta['Numero'] . '|'
    . number_format($igv, 2, '.', '') . '|' . number_format($total, 2, '.', '') . '|' . $fechaQr . '|'
    . $tipoDocumentoCliente . '|' . $dniRuc . '|';
ob_start();
QRcode::png($textoQr, null, 'Q', 5, 4);
$qrBinario = ob_get_clean();
header('Content-Type: application/json; charset=utf-8');
$qrImagen = @imagecreatefromstring($qrBinario);
if (!$qrImagen) respuestaUsb(false, 'No se pudo generar el codigo QR del comprobante.', 500);

$windows = getenv('WINDIR') ?: 'C:\\Windows';
$fuenteNormal = $windows . DIRECTORY_SEPARATOR . 'Fonts' . DIRECTORY_SEPARATOR . 'arial.ttf';
$fuenteNegrita = $windows . DIRECTORY_SEPARATOR . 'Fonts' . DIRECTORY_SEPARATOR . 'arialbd.ttf';
if (!is_file($fuenteNormal) || !is_file($fuenteNegrita) || !function_exists('imagettftext')) {
    if ($qrImagen) imagedestroy($qrImagen);
    respuestaUsb(false, 'No se encontraron las fuentes necesarias para imprimir el ticket legible.', 500);
}

try {
    $totalLetras = NumerosEnLetras::convertir(number_format($total, 2, '.', ''), 'SOLES', true);
    $ticketImagen = construirTicketRaster($venta, $productos, $empresa, $pagos, array(
        'fuente_normal' => $fuenteNormal,
        'fuente_negrita' => $fuenteNegrita,
        'logo' => $logo,
        'qr_imagen' => $qrImagen,
        'qr_modulo_px' => 5,
        'total_letras' => $totalLetras,
        'resolucion_sunat' => $resolucionSunat,
        'mensaje_ticket' => $mensajeTicket,
        'consulta_web' => $consultaWeb,
        'titulo_marca' => $tituloMarca
    ));
    if ($qrImagen) imagedestroy($qrImagen);
    $ticketEscpos = ticketRasterToEscpos($ticketImagen);
    imagedestroy($ticketImagen);
} catch (Throwable $error) {
    if (isset($qrImagen) && $qrImagen) imagedestroy($qrImagen);
    respuestaUsb(false, 'No se pudo preparar el formato completo del ticket: ' . $error->getMessage(), 500);
}

$archivo = tempnam(sys_get_temp_dir(), 'ticket_usb_');
if ($archivo === false || file_put_contents($archivo, $ticketEscpos) === false) {
    respuestaUsb(false, 'No se pudo preparar el ticket para la impresora.', 500);
}

$script = __DIR__ . DIRECTORY_SEPARATOR . 'usb-print.ps1';
$comando = 'powershell.exe -NoProfile -ExecutionPolicy Bypass -File ' . escapeshellarg($script)
    . ' -PrinterName ' . escapeshellarg($impresora)
    . ' -FilePath ' . escapeshellarg($archivo) . ' 2>&1';
$salida = array();
$estado = 1;
exec($comando, $salida, $estado);
@unlink($archivo);

if ($estado !== 0) respuestaUsb(false, 'No se pudo enviar a la impresora ' . $impresora . ': ' . implode(' ', $salida), 500);
respuestaUsb(true, 'Comprobante completo enviado a la impresora ' . $impresora . '.');

