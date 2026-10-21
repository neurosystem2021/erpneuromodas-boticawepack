<?php
include_once __DIR__ . '/../controllers/NumerosEnLetras/NumerosEnLetras.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/EmpresaConfig.php';

$configTicket = empresaConfigDocumentoImpresion($docVenta);
$empresa = empresaConfigDatosImpresion($configTicket);
$logoTicket = '/' . ltrim(empresaConfigValor($configTicket, 'logo_ticket', 'resources/images/logo-ticket.png'), '/');
$esc = static function ($valor) { return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8'); };
$moneda = static function ($valor) { return number_format((float) $valor, 2, '.', ','); };

$fechaDoc = !empty($docVenta['FechaDoc']) ? date('d/m/Y H:i:s', strtotime($docVenta['FechaDoc'])) : date('d/m/Y H:i:s');
$fechaQr = !empty($docVenta['FechaDoc']) ? date('Y-m-d', strtotime($docVenta['FechaDoc'])) : date('Y-m-d');
$numeroComprobante = $docVenta['Serie'] . '-' . str_pad((string) $docVenta['Numero'], 8, '0', STR_PAD_LEFT);
$cliente = strtoupper(trim((string) $docVenta['Cliente']));
$dniRuc = trim((string) $docVenta['DniRuc']);
$direccion = !empty($docVenta['CampoDireccion']) && isset($docVenta[$docVenta['CampoDireccion']])
    ? $docVenta[$docVenta['CampoDireccion']]
    : (isset($docVenta['Direccion']) ? $docVenta['Direccion'] : '');
$direccion = strtoupper(trim((string) $direccion));
$totalBruto = 0.0;
$descuento = 0.0;
foreach ($productos as $producto) {
    $totalBruto += (float) $producto['TOTAL'];
    $descuento += (float) $producto['Descuento'];
}
$total = max(0, $totalBruto - $descuento);
$tieneIgv = !empty($docVenta['TieneIgv']);
$baseGravada = $tieneIgv ? $total / 1.18 : 0;
$igv = $tieneIgv ? $total - $baseGravada : 0;
$exonerada = $tieneIgv ? 0 : $total;
$formaPago = empty($docVenta['EsCredito']) ? 'CONTADO' : 'CRÉDITO';
$pagoCon = (float) $docVenta['PagoCon'];
$vuelto = max(0, $pagoCon - $total);
$observacion = trim((string) (isset($docVenta['Observacion']) ? $docVenta['Observacion'] : ''));
$placa = trim((string) (isset($docVenta['Placa']) ? $docVenta['Placa'] : ''));
$vendedor = strtoupper(trim((string) (isset($docVenta['UsuarioReg']) ? $docVenta['UsuarioReg'] : '')));
$resolucionSunat = empresaConfigValor($configTicket, 'resolucion_sunat', 'Autorizado mediante resolución de intendencia SUNAT.');
$mensajeTicket = empresaConfigValor($configTicket, 'mensaje_ticket', 'Conserve este comprobante para cualquier consulta o reclamo.');
$pagos = isset($metodosPago) && is_array($metodosPago) ? $metodosPago : array();
$consultaWeb = empresaConfigValor($configTicket, 'consulta_web', 'http://emision.factura.vip');
$descripciones = array();
foreach ($productos as $productoDescripcion) {
    $textoDescripcion = trim((string) (isset($productoDescripcion['Descripcion']) ? $productoDescripcion['Descripcion'] : ''));
    if ($textoDescripcion !== '') $descripciones[] = $textoDescripcion;
}
$descripcionComprobante = $observacion !== '' ? $observacion : implode(' / ', array_unique($descripciones));
$tipoDocumentoCliente = strlen($dniRuc) > 9 ? '6' : '1';
$textoQr = $empresa['ruc'] . '|' . $codigoComprobanteSunat . '|' . $docVenta['Serie'] . '|' . $docVenta['Numero'] . '|'
    . number_format($igv, 2, '.', '') . '|' . number_format($total, 2, '.', '') . '|' . $fechaQr . '|'
    . $tipoDocumentoCliente . '|' . $dniRuc . '|';
ob_start();
QRcode::png($textoQr, null, 'Q', 8, 4);
$qrBase64 = base64_encode(ob_get_clean());
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo $esc($tipoComprobanteImpreso . ' ' . $numeroComprobante); ?></title>
<style>
:root{--ink:#000;--muted:#222;--brand:#000;--brand-dark:#000;--soft:#fff;--line:#777}
*{box-sizing:border-box}
html,body{margin:0;min-height:100%;font-family:Arial,Helvetica,sans-serif;color:var(--ink);background:#e9e5e7}
body{padding:18px}
.receipt-actions{text-align:center;margin-bottom:12px}.receipt-actions button{border:0;border-radius:10px;background:#164f67;color:white;padding:11px 18px;font-weight:bold;cursor:pointer}
.ticket{width:80mm;max-width:100%;margin:0 auto;padding:3.2mm;background:#fff;box-shadow:0 10px 32px #2c17222c;font-size:9px;font-weight:600;line-height:1.22}
.brand{text-align:center}.brand img{display:block;width:56mm;height:40mm;max-width:100%;margin:0 auto 2mm;object-fit:contain;object-position:center}
.company{font-size:11px;font-weight:900;line-height:1.2}.company-meta{margin-top:.7mm;color:var(--muted);font-size:8px;font-weight:700;line-height:1.3}
.document{margin:2.5mm 0 2mm;padding:2.2mm 2mm;border:1.5px solid var(--brand);border-radius:3mm;background:var(--soft);text-align:center}
.document-type{color:var(--brand-dark);font-size:10px;font-weight:900;letter-spacing:.35px}.document-number{margin-top:.6mm;color:var(--brand);font-size:14px;font-weight:900;letter-spacing:.5px}
.meta{margin-bottom:2mm;padding:1.8mm 0;border-top:1px dashed var(--line);border-bottom:1px dashed var(--line)}
.row{display:flex;margin:.45mm 0;gap:1.5mm}.label{flex:0 0 19mm;color:var(--ink);font-size:7.4px;font-weight:900}.value{flex:1;min-width:0;font-size:8.3px;font-weight:800;overflow-wrap:anywhere}
.items{width:100%;table-layout:fixed;border-collapse:collapse}.items th{padding:.9mm .45mm;border-bottom:1.5px solid var(--brand-dark);font-size:7px;font-weight:900;line-height:1.1;text-align:left}.items td{padding:.9mm .45mm;border-bottom:1px dashed var(--line);font-size:8.2px;font-weight:700;line-height:1.15;vertical-align:top}.items .qty{width:8mm;text-align:center}.items .unit,.items .amount{width:15mm;text-align:right;white-space:nowrap}.product-name{font-size:9.2px;font-weight:900;line-height:1.12;overflow-wrap:anywhere}.product-code{margin-top:.25mm;color:var(--muted);font-size:7px;font-weight:700;line-height:1.1}
.summary{margin:1.3mm 0 1mm auto;width:45mm}.summary-row{display:flex;padding:.35mm 0;justify-content:space-between;font-size:8.3px}.summary-row span:first-child{color:var(--ink);font-weight:800}.summary-row strong{font-size:9.3px;font-weight:900}.summary-total{display:flex;margin-top:.8mm;padding:1.5mm 1.7mm;justify-content:space-between;border-top:2px solid #000;border-bottom:3px double #000;color:#000;background:#fff;font-size:15px;line-height:1;font-weight:900;letter-spacing:.2px}
.words{margin:1.3mm 0;padding:1.3mm;border-left:.8mm solid var(--brand);background:var(--soft);font-size:7.5px;line-height:1.25}.payment{padding:1mm 0;border-bottom:1px dashed var(--line);font-size:8px;line-height:1.35}.payment-title{margin:.7mm 0 .3mm;font-weight:900}.payment-row{display:flex;justify-content:space-between;gap:2mm}.operation{color:var(--muted);font-size:6.8px;overflow-wrap:anywhere}
.validation{display:grid;margin-top:1.5mm;grid-template-columns:32mm 1fr;gap:2mm;align-items:center}.qr{width:32mm;height:32mm;image-rendering:pixelated;background:#fff}.legal{color:var(--muted);font-size:6.8px;line-height:1.25}.legal strong{color:var(--ink)}
.footer{margin-top:3mm;padding-top:2.5mm;border-top:1px dashed var(--line);text-align:center;font-size:8px;line-height:1.45}.thanks{margin-top:2mm;color:var(--brand);font-size:10px;font-weight:900;letter-spacing:.3px}
@media(max-width:420px){body{padding:0;background:#fff}.ticket{width:100%;min-height:100vh;box-shadow:none}}
@media print{@page{size:80mm 297mm;margin:4mm}html,body{width:72mm!important;max-width:100%!important;height:auto!important;min-height:0!important;background:#fff}body{padding:0}.ticket{width:72mm;max-width:100%;height:auto;min-height:0;margin:0;padding:0;box-shadow:none;break-inside:auto;page-break-inside:auto}.items tr,.validation,.summary-total{break-inside:avoid}.no-print,#printers,#printer{display:none!important}body>br{display:none!important}*{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
</style>
</head>
<body>
<div class="no-print receipt-actions"><button type="button" onclick="window.nstPrintReceipt ? window.nstPrintReceipt() : window.print()">Imprimir comprobante</button></div>
<main class="ticket">
  <header class="brand">
    <img src="<?php echo $esc($logoTicket); ?>" alt="Logo">
    <div class="company"><?php echo $esc($empresa['razon_social']); ?></div>
    <div class="company-meta"><strong>RUC <?php echo $esc($empresa['ruc']); ?></strong><br><?php echo $esc($empresa['direccion']); ?><br><?php echo $esc($empresa['ubicacion']); ?><?php if ($empresa['telefono'] !== ''): ?><br>Tel. <?php echo $esc($empresa['telefono']); ?><?php endif; ?></div>
  </header>

  <section class="document">
    <div class="document-type"><?php echo $esc($tipoComprobanteImpreso); ?></div>
    <div class="document-number"><?php echo $esc($numeroComprobante); ?></div>
  </section>

  <section class="meta">
    <div class="row"><span class="label">FECHA EMISIÓN</span><span class="value"><?php echo $esc($fechaDoc); ?></span></div>
    <div class="row"><span class="label">CLIENTE</span><span class="value"><?php echo $esc($cliente ?: 'CLIENTE VARIOS'); ?></span></div>
    <div class="row"><span class="label">DNI / RUC</span><span class="value"><?php echo $esc($dniRuc ?: '00000000'); ?></span></div>
    <div class="row"><span class="label">DIRECCIÓN</span><span class="value"><?php echo $esc($direccion ?: '-'); ?></span></div>
    <?php if ($placa !== ''): ?><div class="row"><span class="label">PLACA</span><span class="value"><?php echo $esc($placa); ?></span></div><?php endif; ?>
    <?php if ($observacion !== ''): ?><div class="row"><span class="label">OBSERVACIÓN</span><span class="value"><?php echo $esc($observacion); ?></span></div><?php endif; ?>
  </section>

  <table class="items">
    <thead><tr><th>DESCRIPCIÓN</th><th class="qty">CANT.</th><th class="unit">P. UNIT.</th><th class="amount">IMPORTE</th></tr></thead>
    <tbody>
    <?php foreach ($productos as $indice => $producto): ?>
      <tr>
        <td><div class="product-name"><?php echo ($indice + 1) . '. ' . $esc($producto['Producto']); ?></div><?php if (!empty($producto['Descripcion'])): ?><div class="product-code"><?php echo $esc($producto['Descripcion']); ?></div><?php endif; ?><div class="product-code"><?php echo $esc(trim(($producto['ProductoModelo'] ?? '') . ' ' . ($producto['ProductoMedicion'] ?? ''))); ?></div></td>
        <td class="qty"><?php echo $esc($producto['Cantidad']); ?></td>
        <td class="unit">S/ <?php echo $moneda($producto['Precio']); ?></td>
        <td class="amount"><strong>S/ <?php echo $moneda($producto['TOTAL']); ?></strong></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <section class="summary">
    <div class="summary-row"><span>SUBTOTAL</span><strong>S/ <?php echo $moneda($total); ?></strong></div>
    <?php if ($tieneIgv): ?><div class="summary-row"><span>OP. GRAVADA</span><strong>S/ <?php echo $moneda($baseGravada); ?></strong></div><div class="summary-row"><span>IGV (18%)</span><strong>S/ <?php echo $moneda($igv); ?></strong></div><?php else: ?><div class="summary-row"><span>OP. EXONERADA</span><strong>S/ <?php echo $moneda($exonerada); ?></strong></div><?php endif; ?>
    <?php if ($descuento > 0): ?><div class="summary-row"><span>DESCUENTO</span><strong>- S/ <?php echo $moneda($descuento); ?></strong></div><?php endif; ?>
    <div class="summary-total"><span>TOTAL</span><span>S/ <?php echo $moneda($total); ?></span></div>
  </section>

  <div class="words"><strong>SON:</strong> <?php echo $esc(strtoupper(NumerosEnLetras::convertir(number_format($total, 2, '.', ''), 'SOLES', true))); ?></div>
  <section class="payment">
    <div><strong>VENDEDOR:</strong> <?php echo $esc($vendedor ?: '-'); ?></div>
    <div><strong>FORMA DE PAGO:</strong> <?php echo $esc($formaPago); ?></div>
    <div class="payment-title">MÉTODO(S) DE PAGO</div>
    <?php if ($pagos): foreach ($pagos as $pago): ?>
      <div class="payment-row"><span><?php echo $esc($pago['MetodoPago']); ?></span><strong>S/ <?php echo $moneda($pago['Importe']); ?></strong></div>
      <?php if (trim((string) $pago['NroTarjeta']) !== ''): ?><div class="operation">Operación / referencia: <?php echo $esc($pago['NroTarjeta']); ?></div><?php endif; ?>
    <?php endforeach; else: ?>
      <div class="payment-row"><span><?php echo $esc(isset($docVenta['MetodoPago']) ? $docVenta['MetodoPago'] : 'EFECTIVO'); ?></span><strong>S/ <?php echo $moneda($total); ?></strong></div>
    <?php endif; ?>
    <div class="payment-row"><span>RECIBIDO</span><strong>S/ <?php echo $moneda($pagoCon > 0 ? $pagoCon : $total); ?></strong></div>
    <div class="payment-row"><span>VUELTO</span><strong>S/ <?php echo $moneda($vuelto); ?></strong></div>
    <?php if ($descripcionComprobante !== ''): ?><div style="margin-top:1mm"><strong>DESCRIPCIÓN:</strong><br><?php echo $esc($descripcionComprobante); ?></div><?php endif; ?>
  </section>

  <section class="validation">
    <img class="qr" src="data:image/png;base64,<?php echo $qrBase64; ?>" alt="Código QR del comprobante">
    <div class="legal"><strong><?php echo $esc($resolucionSunat); ?></strong><br>Representación impresa de la <?php echo $esc($tipoComprobanteImpreso); ?>.<br><strong>CONSULTA WEB:</strong><br><?php echo $esc($consultaWeb); ?></div>
  </section>

  <footer class="footer"><?php if ($empresa['actividad'] !== ''): ?><strong><?php echo $esc($empresa['actividad']); ?></strong><br><?php endif; ?><?php echo $esc($mensajeTicket); ?><br><?php echo $esc($empresa['direccion']); ?><?php if ($empresa['telefono'] !== ''): ?><br>CEL./TEL. <?php echo $esc($empresa['telefono']); ?><?php endif; ?><div class="thanks">¡GRACIAS POR SU PREFERENCIA!</div><div>DESARROLLADO POR<br><strong>neurosoft.com.pe</strong></div></footer>
</main>
</body>
</html>
