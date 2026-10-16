<?php

require_once 'lib/html5lib/Parser.php';
require_once 'lib/php-font-lib/src/FontLib/Autoloader.php';
require_once 'lib/php-svg-lib/src/autoload.php';
require_once 'src/Autoloader.php';
Dompdf\Autoloader::register();

use Dompdf\Dompdf;
use Dompdf\Options;

include '../phpqrcode/qrlib.php';

$bodyRequest = file_get_contents('php://input');
$cab = json_decode($bodyRequest, true);

if (!is_array($cab)) {
    http_response_code(400);
    exit('Datos del comprobante no validos');
}

$tipop = $cab['txtTIPO_PROCESO'] === '03' ? 'BETA' : 'PRODUCCION';
$monedas = array('PEN' => 'SOLES', 'USD' => 'DOLARES', 'EUR' => 'EUROS');
$valmoneda = isset($monedas[$cab['txtCOD_MONEDA']]) ? $monedas[$cab['txtCOD_MONEDA']] : $cab['txtCOD_MONEDA'];

$rutaRelativa = '../../api_cpe/' . $tipop . '/' . $cab['txtNRO_DOCUMENTO_EMPRESA'] . '/';
$ruta = __DIR__ . DIRECTORY_SEPARATOR . $rutaRelativa;
if (!is_dir($ruta)) {
    @mkdir($ruta, 0775, true);
}

$fichero = $cab['txtNRO_DOCUMENTO_EMPRESA'] . '-' . $cab['txtCOD_TIPO_DOCUMENTO'] . '-' . $cab['txtNRO_COMPROBANTE'];
$tiposDocumento = array('01' => 'FACTURA ELECTRONICA', '03' => 'BOLETA ELECTRONICA', '12' => 'NOTA DE VENTA');
$tdocumento = isset($tiposDocumento[$cab['txtCOD_TIPO_DOCUMENTO']])
    ? $tiposDocumento[$cab['txtCOD_TIPO_DOCUMENTO']]
    : 'COMPROBANTE ELECTRONICO';
$esNotaVenta = (string) $cab['txtCOD_TIPO_DOCUMENTO'] === '12';

$comprobante = explode('-', $cab['txtNRO_COMPROBANTE']);
$serie = isset($comprobante[0]) ? $comprobante[0] : '';
$numero = isset($comprobante[1]) ? $comprobante[1] : '';
$rutaQr = '';
if (!$esNotaVenta) {
    $textoQr = $cab['txtNRO_DOCUMENTO_EMPRESA'] . ' | ' . $cab['txtCOD_TIPO_DOCUMENTO'] . ' | ' . $serie . ' | ' . $numero . ' | ' . $cab['txtTOTAL_IGV'] . ' | ' . $cab['txtTOTAL'] . ' | ' . $cab['txtFECHA_DOCUMENTO'] . ' | ' . $cab['txtTIPO_DOCUMENTO_CLIENTE'] . ' | ' . $cab['txtNRO_DOCUMENTO_CLIENTE'] . ' |';
    ob_start();
    QRcode::png($textoQr, false, 'Q', 8, 1);
    $rutaQr = 'data:image/png;base64,' . base64_encode(ob_get_clean());
}

$e = function ($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};
$money = function ($valor) {
    return number_format((float) $valor, 2, '.', ',');
};

require_once $_SERVER['DOCUMENT_ROOT'] . '/models/EmpresaConfig.php';
$pdfEmpresaConfig = empresaConfigObtener();
$pdfLogoRelativo = empresaConfigValor($pdfEmpresaConfig, 'logo_principal', empresaConfigValor($pdfEmpresaConfig, 'logo_ticket', 'resources/images/logo-ticket.png'));
$logo = realpath($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($pdfLogoRelativo, '/'));
if (!$logo) $logo = realpath(__DIR__ . '/../../images/logo.png');
$fecha = date('d/m/Y', strtotime($cab['txtFECHA_DOCUMENTO']));
$formaPago = empty($cab['txtES_CREDITO']) ? 'CONTADO' : 'CREDITO';
$gravadas = ((float) $cab['txtTOTAL_IGV'] > 0) ? (float) $cab['txtTOTAL'] - (float) $cab['txtTOTAL_IGV'] : 0;
$exonerada = ((float) $cab['txtTOTAL_IGV'] > 0) ? 0 : (float) $cab['txtTOTAL'];
$esFactura = $cab['txtCOD_TIPO_DOCUMENTO'] === '01';
$accent = '#075247';
$accentDark = '#043b35';
$accentSoft = '#e5f2ef';

$html = '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
@page { margin: 24px 30px 25px; }
* { box-sizing: border-box; }
body { margin: 0; color: #102448; font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; line-height: 1.35; }
table { width: 100%; border-collapse: collapse; }
.top-accent { height: 8px; margin-bottom: 13px; border-radius: 8px; background: ' . $accent . '; }
.brand { width: 66%; padding: 7px 12px 9px 5px; vertical-align: middle; }
.brand-logo { width: 105px; height: 64px; object-fit: contain; vertical-align: middle; }
.brand-copy { padding-left: 13px; vertical-align: middle; }
.brand-name { margin: 0 0 4px; color: #043b35; font-size: 20px; font-weight: 700; }
.brand-label { margin-bottom: 7px; color: #075247; font-size: 7px; font-weight: 700; letter-spacing: 1.4px; }
.brand-detail { color: #52647c; font-size: 8px; line-height: 1.45; }
.fiscal-box { width: 34%; padding: 0; overflow: hidden; border: 1.5px solid ' . $accentDark . '; border-radius: 10px; vertical-align: middle; text-align: center; }
.fiscal-ruc { padding: 9px 7px 7px; color: ' . $accentDark . '; font-size: 10px; font-weight: 700; }
.fiscal-type { padding: 9px 5px; color: #fff; background: ' . $accentDark . '; font-size: 12px; font-weight: 700; letter-spacing: .4px; }
.fiscal-number { padding: 9px 6px; color: ' . $accentDark . '; background: ' . $accentSoft . '; font-size: 15px; font-weight: 700; }
.section-title { margin: 15px 0 6px; color: ' . $accent . '; font-size: 7px; font-weight: 700; letter-spacing: 1.25px; text-transform: uppercase; }
.info-card { overflow: hidden; border: 1px solid #d7e1ec; border-radius: 9px; background: #f8fbfe; }
.info-card td { padding: 6px 9px; border-bottom: 1px solid #e4ebf2; vertical-align: top; }
.info-card tr:last-child td { border-bottom: 0; }
.info-label { width: 12%; color: #738198; font-size: 7px; font-weight: 700; text-transform: uppercase; }
.info-value { width: 53%; color: #102448; font-size: 9px; font-weight: 600; }
.info-label-right { width: 12%; color: #738198; font-size: 7px; font-weight: 700; text-transform: uppercase; }
.info-value-right { width: 23%; color: #102448; font-size: 9px; font-weight: 600; }
.items { margin-top: 12px; border: 1px solid #cfdbe8; }
.items thead th { padding: 8px 6px; color: #fff; background: ' . $accentDark . '; border-right: 1px solid rgba(255,255,255,.18); font-size: 7px; letter-spacing: .55px; text-transform: uppercase; }
.items thead th:last-child { border-right: 0; }
.items tbody td { padding: 7px 6px; color: #233956; border-right: 1px solid #dbe4ee; border-bottom: 1px solid #e1e8f0; font-size: 8px; }
.items tbody tr:nth-child(even) td { background: #f5f9fc; }
.items tbody td:last-child { border-right: 0; }
.center { text-align: center; }
.right { text-align: right; }
.footer-block { position: fixed; top: 565px; right: 30px; left: 30px; }
.amount-words { margin-bottom: 9px; padding: 8px 11px; color: #102448; border-left: 4px solid ' . $accent . '; background: ' . $accentSoft . '; font-size: 8px; }
.summary-layout td { vertical-align: top; }
.legal-card { width: ' . ($esNotaVenta ? '72%' : '55%') . '; padding: 10px 12px; border: 1px solid #d7e1ec; border-radius: 9px; background: #f8fbfe; }
.legal-title { margin-bottom: 7px; color: #075247; font-size: 7px; font-weight: 700; letter-spacing: 1px; }
.legal-row { margin-bottom: 4px; color: #52647c; font-size: 8px; }
.legal-row strong { color: #102448; }
.hash { margin-top: 7px; color: #75839a; font-size: 6px; word-break: break-all; }
.qr-cell { width: 17%; padding: 3px 8px; text-align: center; }
.qr-cell img { width: 85px; height: 85px; }
.qr-caption { margin-top: 3px; color: #738198; font-size: 6px; }
.totals-card { width: 28%; overflow: hidden; border: 1px solid #cbd7e4; border-radius: 9px; }
.totals-card td { padding: 4px 8px; border-bottom: 1px solid #e0e7ef; font-size: 7px; }
.totals-card tr:last-child td { border-bottom: 0; }
.totals-label { color: #61728a; font-weight: 600; }
.totals-value { color: #102448; font-weight: 700; text-align: right; }
.grand-total td { padding-top: 8px; padding-bottom: 8px; color: #fff; background: ' . $accentDark . '; font-size: 10px; }
.footer-note { margin-top: 10px; padding-top: 7px; color: #718096; border-top: 1px solid #d9e2eb; font-size: 6.5px; text-align: center; }
.footer-note strong { color: #102448; }
</style>
</head>
<body>
<div class="top-accent"></div>
<table>
<tr>
<td class="brand">
  <table><tr>
    <td style="width:118px"><img class="brand-logo" src="' . $e($logo) . '"></td>
    <td class="brand-copy">
      <div class="brand-label">ERP - BATER&Iacute;AS, AROS Y LLANTAS</div>
      <div class="brand-name">' . $e($cab['txtNOMBRE_COMERCIAL_EMPRESA']) . '</div>
      <div class="brand-detail"><strong>Direccion:</strong> ' . $e($cab['txtDIRECCION_EMPRESA']) . '<br><strong>Ubicacion:</strong> ' . $e($cab['txtDISTRITO_EMPRESA']) . ', ' . $e($cab['txtPROVINCIA_EMPRESA']) . ' - ' . $e($cab['txtDEPARTAMENTO_EMPRESA']) . '</div>
    </td>
  </tr></table>
</td>
<td class="fiscal-box">
  <div class="fiscal-ruc">RUC ' . $e($cab['txtNRO_DOCUMENTO_EMPRESA']) . '</div>
  <div class="fiscal-type">' . $e($tdocumento) . '</div>
  <div class="fiscal-number">' . $e($cab['txtNRO_COMPROBANTE']) . '</div>
</td>
</tr>
</table>

<div class="section-title">Informacion del cliente y comprobante</div>
<table class="info-card">
<tr><td class="info-label">Cliente</td><td class="info-value">' . $e($cab['txtRAZON_SOCIAL_CLIENTE']) . '</td><td class="info-label-right">Fecha</td><td class="info-value-right">' . $e($fecha) . '</td></tr>
<tr><td class="info-label">DNI / RUC</td><td class="info-value">' . $e($cab['txtNRO_DOCUMENTO_CLIENTE']) . '</td><td class="info-label-right">Moneda</td><td class="info-value-right">' . $e($valmoneda) . '</td></tr>
<tr><td class="info-label">Direccion</td><td class="info-value">' . $e($cab['txtDIRECCION_CLIENTE']) . '</td><td class="info-label-right">Pago</td><td class="info-value-right">' . $e($formaPago) . '</td></tr>
</table>

<div class="section-title">Detalle de productos</div>
<table class="items">
<thead><tr><th style="width:13%">Codigo</th><th style="width:49%">Descripcion</th><th style="width:13%">Precio</th><th style="width:11%">Cant.</th><th style="width:14%">Importe</th></tr></thead>
<tbody>';

foreach ($cab['detalle'] as $producto) {
    $descripcion = $producto['txtDESCRIPCION_DET'];
    if (!empty($producto['txtDESCRIPCION_DET_DESCRIP'])) {
        $descripcion .= ' - ' . $producto['txtDESCRIPCION_DET_DESCRIP'];
    }
    $html .= '<tr>'
        . '<td class="center">' . $e($producto['txtCODIGO_DET']) . '</td>'
        . '<td>' . $e($descripcion) . '</td>'
        . '<td class="right">S/ ' . $money($producto['txtPRECIO_DET']) . '</td>'
        . '<td class="center">' . $e($producto['txtCANTIDAD_DET']) . '</td>'
        . '<td class="right"><strong>S/ ' . $money($producto['txtIMPORTE_DET']) . '</strong></td>'
        . '</tr>';
}

$html .= '</tbody></table>

<div class="footer-block">
  <div class="amount-words"><strong>SON:</strong> ' . $e($cab['txtTOTAL_LETRAS']) . '</div>
  <table class="summary-layout"><tr>
    <td class="legal-card">
      <div class="legal-title">INFORMACION DEL COMPROBANTE</div>
      <div class="legal-row"><strong>Forma de pago:</strong> ' . $e($formaPago) . '</div>'
      . (!empty($cab['txtES_CREDITO']) ? '<div class="legal-row"><strong>Fecha de pago:</strong> ' . $e($cab['txtFECHA_CREDITO']) . '</div>' : '') . '
      <div class="legal-row"><strong>Vendedor:</strong> ' . $e(strtoupper($cab['txtVENDEDOR'])) . '</div>
      ' . (!empty($cab['txtPLACA']) ? '<div class="legal-row"><strong>PLACA:</strong> ' . $e($cab['txtPLACA']) . '</div>' : '') . '
      ' . (!empty($cab['txtOBSERVACION']) ? '<div class="legal-row"><strong>OBSERVACION:</strong> ' . $e($cab['txtOBSERVACION']) . '</div>' : '') . '
      <div class="legal-row">' . ($esNotaVenta ? 'Documento interno de venta.' : 'Representacion impresa de la ' . $e($tdocumento) . '.') . '</div>
      ' . (!$esNotaVenta ? '<div class="hash"><strong>HASH:</strong> ' . $e($cab['hash_cpe']) . '</div>' : '') . '
    </td>
    ' . (!$esNotaVenta ? '<td class="qr-cell"><img src="' . $e($rutaQr) . '"><div class="qr-caption">Valide este comprobante mediante el codigo QR</div></td>' : '') . '
    <td class="totals-card">
      <table>
        <tr><td class="totals-label">SUBTOTAL</td><td class="totals-value">S/ ' . $money($cab['txtSUB_TOTAL']) . '</td></tr>
        <tr><td class="totals-label">GRAVADA</td><td class="totals-value">S/ ' . $money($gravadas) . '</td></tr>
        <tr><td class="totals-label">EXONERADA</td><td class="totals-value">S/ ' . $money($exonerada) . '</td></tr>
        <tr><td class="totals-label">DESCUENTO</td><td class="totals-value">S/ ' . $money($cab['txtTOTAL_DESCUENTO']) . '</td></tr>
        <tr><td class="totals-label">IGV (18%)</td><td class="totals-value">S/ ' . $money($cab['txtTOTAL_IGV']) . '</td></tr>
        <tr class="grand-total"><td><strong>TOTAL</strong></td><td class="right"><strong>S/ ' . $money($cab['txtTOTAL']) . '</strong></td></tr>
      </table>
    </td>
  </tr></table>
  <div class="footer-note"><strong>Gracias por su compra.</strong> Conserve este documento para cualquier consulta. No se aceptan devoluciones sin comprobante de pago.<br><strong>Desarrollado por neurosoft.com.pe</strong></div>
</div>
</body>
</html>';

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->setPaper('A4', 'portrait');
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->render();
$pdf = $dompdf->output();
@file_put_contents($ruta . $fichero . '.pdf', $pdf);
header('Content-Type: application/pdf');
echo $pdf;

?>
