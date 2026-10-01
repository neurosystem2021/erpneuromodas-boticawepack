<?php
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/views/validateUser.php';
require_once dirname(__DIR__) . '/clases/BnGeneral.php';
require_once dirname(__DIR__) . '/models/EmpresaConfig.php';

function respuestaUsb($ok, $mensaje, $codigo = 200)
{
    http_response_code($codigo);
    echo json_encode(['ok' => $ok, 'mensaje' => $mensaje], JSON_UNESCAPED_UNICODE);
    exit;
}

function textoTicket($valor)
{
    return mb_strtoupper(trim((string) $valor), 'UTF-8');
}

function lineaTicket($izquierda, $derecha = '', $ancho = 48)
{
    $izquierda = (string) $izquierda;
    $derecha = (string) $derecha;
    $espacios = max(1, $ancho - mb_strlen($izquierda, 'UTF-8') - mb_strlen($derecha, 'UTF-8'));
    return $izquierda . str_repeat(' ', $espacios) . $derecha . "\n";
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respuestaUsb(false, 'Metodo no permitido.', 405);
}

$entrada = json_decode(file_get_contents('php://input'), true);
if (!is_array($entrada)) {
    respuestaUsb(false, 'No se recibieron datos validos para imprimir.', 422);
}

$idVenta = isset($entrada['IdDocVenta']) ? (int) $entrada['IdDocVenta'] : 0;
if ($idVenta <= 0) {
    respuestaUsb(false, 'No se recibio una venta valida.', 422);
}

$impresoraSolicitada = isset($entrada['PrinterName'])
    ? trim((string) $entrada['PrinterName'])
    : '';
$impresoraSolicitada = preg_replace('/[\x00-\x1F\x7F]/u', '', $impresoraSolicitada);
$impresora = $impresoraSolicitada !== ''
    ? $impresoraSolicitada
    : (getenv('NEURO_USB_PRINTER') ?: 'bar');

$resultado = fn_devolverDocVenta('Ve_DocVenta.IdDocVenta=' . $idVenta, '');
$venta = $resultado ? mysqli_fetch_assoc($resultado) : null;
if (!$venta) {
    respuestaUsb(false, 'No se encontro la venta.', 404);
}

$resultadoDetalle = fn_devolverDocVentaDet(
    'Ve_DocVentaDet.IdDocVenta=' . $idVenta,
    'Ve_DocVentaDet.IdDocVentaDet ASC'
);
$productos = $resultadoDetalle ? mysqli_fetch_all($resultadoDetalle, MYSQLI_ASSOC) : [];
$empresa = empresaConfigDatosImpresion();

$ancho = 48;
$separador = str_repeat('-', $ancho) . "\n";
$ticket = "\x1B\x40";
$ticket .= "\x1B\x61\x01";
$ticket .= "\x1B\x45\x01" . textoTicket($empresa['nombre_comercial'] ?: $empresa['razon_social']) . "\x1B\x45\x00\n";
$ticket .= textoTicket($empresa['razon_social']) . "\n";
$ticket .= 'RUC: ' . textoTicket($empresa['ruc']) . "\n";
if (!empty($empresa['telefono'])) {
    $ticket .= 'TEL: ' . textoTicket($empresa['telefono']) . "\n";
}
if (!empty($empresa['direccion'])) {
    $ticket .= textoTicket($empresa['direccion']) . "\n";
}
$ticket .= "\n" . textoTicket($venta['TipoDoc']) . "\n";
$ticket .= textoTicket($venta['Serie']) . '-' . str_pad((string) $venta['Numero'], 8, '0', STR_PAD_LEFT) . "\n";
$ticket .= "\x1B\x61\x00" . $separador;
$ticket .= 'FECHA: ' . textoTicket($venta['FechaDoc']) . "\n";
$ticket .= 'CLIENTE: ' . textoTicket($venta['Cliente']) . "\n";
$ticket .= 'DOC.: ' . textoTicket($venta['DniRuc']) . "\n";
$ticket .= $separador;

$total = 0.0;
foreach ($productos as $producto) {
    $cantidad = (float) $producto['Cantidad'];
    $precio = (float) $producto['Precio'];
    $importe = isset($producto['TOTAL']) ? (float) $producto['TOTAL'] : $cantidad * $precio;
    $total += $importe;
    $descripcion = textoTicket($producto['ProductoDesc'] ?: $producto['Producto']);
    $ticket .= mb_substr($descripcion, 0, $ancho, 'UTF-8') . "\n";
    $cantidadTexto = rtrim(rtrim(number_format($cantidad, 2, '.', ''), '0'), '.');
    $ticket .= lineaTicket(
        $cantidadTexto . ' x S/ ' . number_format($precio, 2),
        'S/ ' . number_format($importe, 2),
        $ancho
    );
}

$ticket .= $separador;
$ticket .= "\x1B\x45\x01" . lineaTicket('TOTAL', 'S/ ' . number_format($total, 2), $ancho) . "\x1B\x45\x00";
if (!empty($venta['MetodoPago'])) {
    $ticket .= 'FORMA DE PAGO: ' . textoTicket($venta['MetodoPago']) . "\n";
}
$ticket .= "\n\x1B\x61\x01GRACIAS POR SU COMPRA\nDESARROLLADO POR: neurosoft.com.pe\n\n\n\x1D\x56\x00";

$ticketCp = mb_convert_encoding($ticket, 'CP850', 'UTF-8');
$archivo = tempnam(sys_get_temp_dir(), 'ticket_usb_');
if ($archivo === false || file_put_contents($archivo, $ticketCp) === false) {
    respuestaUsb(false, 'No se pudo preparar el ticket.', 500);
}

$script = __DIR__ . DIRECTORY_SEPARATOR . 'usb-print.ps1';
$comando = 'powershell.exe -NoProfile -ExecutionPolicy Bypass -File ' . escapeshellarg($script)
    . ' -PrinterName ' . escapeshellarg($impresora)
    . ' -FilePath ' . escapeshellarg($archivo) . ' 2>&1';
$salida = [];
$estado = 1;
exec($comando, $salida, $estado);
@unlink($archivo);

if ($estado !== 0) {
    respuestaUsb(false, 'No se pudo enviar a la impresora ' . $impresora . ': ' . implode(' ', $salida), 500);
}

respuestaUsb(true, 'Ticket enviado a la impresora ' . $impresora . '.');
