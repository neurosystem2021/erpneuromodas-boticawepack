<?php
$rutat = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
$rutat = str_replace("plugins/dompdf/index.php", "", $rutat);

require_once 'lib/html5lib/Parser.php';
require_once 'lib/php-font-lib/src/FontLib/Autoloader.php';
require_once 'lib/php-svg-lib/src/autoload.php';
require_once 'src/Autoloader.php';
Dompdf\Autoloader::register();

use Dompdf\Dompdf;
use Dompdf\Options;

include "../phpqrcode/qrlib.php";

// ─── REQUEST ──────────────────────────────────────────────────────────────────
$bodyRequest = file_get_contents("php://input");
$cab         = json_decode($bodyRequest, true);
$datos       = $cab['datos'];
$productos   = $cab['datos']['productos'];

// ─── RUTAS ────────────────────────────────────────────────────────────────────
$tipop   = 'PRODUCCION';
$ruta    = "../../api_cpe/" . $tipop . "/" . $cab['txtNRO_DOCUMENTO_EMPRESA'] . "/";
$fichero = 'COTIZACION_' . $datos['Anio'] . '_' . $datos['Numero'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/models/EmpresaConfig.php';
$pdfEmpresaConfig = empresaConfigPorRucImpresion($cab['txtNRO_DOCUMENTO_EMPRESA'] ?? '');
$pdfLogoRelativo = empresaConfigValor($pdfEmpresaConfig, 'imagen_proforma', empresaConfigValor($pdfEmpresaConfig, 'logo_ticket', 'resources/images/logo-ticket.png'));
$logo = realpath($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($pdfLogoRelativo, '/'));
if (!$logo) $logo = "../../../../resources/images/logo-ticket.png";
$wsp     = "../../../../resources/images/wsp.png";

// ─── TOTALES ──────────────────────────────────────────────────────────────────
$total          = 0;
$TotalDescuento = 0;
foreach ($productos as $det) {
    $total          += ($det['Precio'] * $det['Cantidad']) - $det['Descuento'];
    $TotalDescuento += $det['Descuento'];
}
$IGV      = !empty($datos['IGV']) ? round($total * 0.18, 2) : 0;
$totalIGV = $total;

$totalFmt          = number_format($total, 2, '.', '');
$totalIGVFmt       = number_format($totalIGV, 2, '.', '');
$IGVFmt            = number_format($IGV, 2, '.', '');
$TotalDescuentoFmt = number_format($TotalDescuento, 2, '.', '');
$moneda            = $datos['Moneda'] ?? 'S/.';
$numeroPad         = str_pad($datos['Numero'], 7, '0', STR_PAD_LEFT);

// ─── CAMPOS NUEVOS ────────────────────────────────────────────────────────────
$esCreditoProforma    = !empty($datos['EsCreditoProforma']) && $datos['EsCreditoProforma'] == 1;
$fechaCreditoProforma = $datos['FechaCreditoProforma'] ?? '';
$esDescripcion        = !empty($datos['EsDescripcion']) && $datos['EsDescripcion'] == 1;
$desc1 = $datos['DescripcionProforma']  ?? '';
$desc2 = $datos['DescripcionProforma2'] ?? '';
$desc3 = $datos['DescripcionProforma3'] ?? '';
$desc4 = $datos['DescripcionProforma4'] ?? '';
$desc5 = $datos['DescripcionProforma5'] ?? '';

// ─── HELPER: fila de condición ────────────────────────────────────────────────
function condRow($label, $value) {
    return '
    <tr>
      <td style="padding:4px 8px 4px 0; font-weight:700; color:#444; white-space:nowrap; width:40%; font-size:2.8rem">'
        . htmlspecialchars($label) . '</td>
      <td style="padding:4px 0; color:#222; font-size:3.1rem">'
        . htmlspecialchars($value) . '</td>
    </tr>';
}

// ─── CONDICIONES ──────────────────────────────────────────────────────────────
$condHtml = condRow('VALIDEZ DE OFERTA:', $datos['DiasValidos'] . ' días');

$formaPago = $esCreditoProforma
    ? 'CRÉDITO' . ($fechaCreditoProforma ? ' — hasta ' . $fechaCreditoProforma : '')
    : 'CONTADO';
$condHtml .= condRow('FORMA DE PAGO:', $formaPago);

if ($esDescripcion) {
    if ($desc1) $condHtml .= condRow('PLAZO DE ENTREGA:', $desc1);
    if ($desc2) $condHtml .= condRow('VALIDEZ DE LA OFERTA:', $desc2);
    if ($desc3) $condHtml .= condRow('LUGAR DE ENTREGA:', $desc3);
    if ($desc4) $condHtml .= condRow('GARANTÍA:', $desc4);
    if ($desc5) $condHtml .= '<tr><td colspan="2" style="padding:4px 0; color:#222;">' . htmlspecialchars($desc5) . '</td></tr>';
}

// ─── FILAS PRODUCTOS ──────────────────────────────────────────────────────────
$productosRows = '';
foreach ($productos as $index => $det) {
    $subtotal  = ($det['Precio'] * $det['Cantidad']) - $det['Descuento'];
    $bg        = $index % 2 === 0 ? '#ffffff' : '#f7f8fc';
    $dsctHtml  = $det['Descuento'] > 0
        ? '<br><span style="font-size:0.72rem;color:#e67e22;">Dsct: ' . $moneda . ' ' . number_format($det['Descuento'], 2) . '</span>'
        : '';
    $extras = '';
    foreach (['ProductoDesc','ProductoDesc2','ProductoDesc3'] as $f) {
        if (!empty($det[$f])) $extras .= '<div style="font-size:0.75rem;color:#666;margin-top:2px;">' . htmlspecialchars($det[$f]) . '</div>';
    }

    $productosRows .= '
    <tr style="background:' . $bg . ';">
      <td style="text-align:center;padding:6px 4px;color:#aaa;font-size:1.8rem;">' . ($index + 1) . '</td>
      <td style="text-align:center;padding:6px 4px; font-size:1.8rem">
        <span style="background:#eef0f5;border-radius:20px;padding:2px 10px;font-weight:700;">'
            . htmlspecialchars($det['Cantidad']) . '</span>
      </td>
      <td colspan="2" style="padding:6px 8px; font-size:2rem">
        <span style="font-weight:700;">' . htmlspecialchars($det['Producto']) . '</span>'
        . (!empty($det['CodigoBarra']) ? ' <span style="color:#888;font-size:0.8rem;">— ' . htmlspecialchars($det['CodigoBarra']) . '</span>' : '')
        . $extras . '
      </td>
      <td style="text-align:center;padding:6px 4px; font-size:1.5rem">' . htmlspecialchars($det['ProductoModelo']      ?? '') . '</td>
      <td style="text-align:center;padding:6px 4px; font-size:1.5rem">' . htmlspecialchars($det['ProductoMarca']       ?? '') . '</td>
      <td style="text-align:center;padding:6px 4px; font-size:1.5rem">' . htmlspecialchars($det['ProductoTalla']       ?? '') . '</td>
      <td style="text-align:center;padding:6px 4px; font-size:1.5rem">' . htmlspecialchars($det['Color']               ?? '') . '</td>
      <td style="text-align:center;padding:6px 4px; font-size:1.5rem">' . htmlspecialchars($det['Genero']              ?? '') . '</td>
      <td style="text-align:center;padding:6px 4px; font-size:1.5rem">' . htmlspecialchars($det['Botapie']             ?? '') . '</td>
      <td style="text-align:right;padding:6px 8px;color:#010067;font-weight:700; font-size:1.5rem">'
            . $moneda . ' ' . number_format($det['Precio'], 2) . '</td>
      <td style="text-align:right;padding:6px 8px;color:#010067;font-weight:700; font-size:1.5rem">'
            . $moneda . ' ' . number_format($subtotal, 2) . $dsctHtml . '</td>
    </tr>';
}

// ─── HTML ─────────────────────────────────────────────────────────────────────
$html = '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cotización ' . $datos['Anio'] . '-' . $numeroPad . '</title>
  <style>
    * { font-family: Arial, sans-serif; font-size: 0.86rem; box-sizing: border-box; margin:0; padding:0; }
    body { background:#fff; color:#222; padding: 15px; }

    /* encabezado */
    .hdr        { background:#010067; width:100%; border-collapse:collapse; table-layout: fixed; }
    .hdr td     { padding:14px 18px; vertical-align:middle; }
    .logo-wrap  { background:#fff; border-radius:6px; padding:6px 10px; display:inline-block; }
    .badge      { background:#fd8100; border-radius:8px; padding:10px 20px; text-align:center; }
    .badge-type { font-size:2rem; text-transform:uppercase; letter-spacing:1px; color:rgba(255,255,255,.8); }
    .badge-num  { font-size:3rem; font-weight:700; color:#fff; line-height:1.2; }
    .badge-date { font-size:2rem; color:rgba(255,255,255,.75); margin-top:3px; }
    .hdr-info p { color:#b8c8e8; font-size:2rem; margin:2px 0; }
    .hdr-info b { color:#fff; font-size:2rem;}

    /* barra acento */
    .accent { height:5px; background:linear-gradient(to right,#010067 60%,#fd8100 100%); }

    /* cliente */
    .cbox   { border:1px solid #c0c0c0; border-radius:4px; padding:8px 12px; margin:8px 0; }
    .cbox table { width:100%; border-collapse:collapse; table-layout: fixed; }
    .cbox td    { padding:3px 5px; font-size:1.8rem; }
    .cbox b     { color:#333; }

    /* tabla productos */
    .ptable              { width:100%; border-collapse:collapse; margin:6px 0; font-size:0.78rem; table-layout: fixed; }
    .ptable thead tr     { background:#010067; }
    .ptable thead th     { color:#fff; font-size:1.4rem; text-transform:uppercase;
                           letter-spacing:.4px; padding:8px 5px;
                           border-right:1px solid #1a1a8a; }
    .ptable tbody td     { border-bottom:1px solid #ebebeb; vertical-align:middle; }
    .ptable tfoot td     { padding:7px 8px; font-weight:700; border-top:1px solid #ccc; font-size:0.83rem; }
    .ptable .subtotal-row td { background:#f5f5f5; font-size:1.5rem; }
    .ptable .total-row td    { background:#010067; color:#fff; font-size:1.5rem; }

    /* footer table */
    .footer-tbl   { width:100%; border-collapse:collapse; margin-top:14px; table-layout: fixed; }
    .footer-tbl > tbody > tr > td { vertical-align:top; }
    .cond-title   { font-size:1.8rem; font-weight:700; text-transform:uppercase;
                    letter-spacing:.6px; color:#010067; border-bottom:2px solid #010067;
                    padding-bottom:4px; margin-bottom:8px; }
    .cond-tbl     { border-collapse:collapse; width:100%; font-size:0.80rem; table-layout: fixed; }

    /* totales */
    .tot-tbl  { width:100%; border-collapse:collapse; border:1px solid #c0c0c0; border-radius:4px; table-layout: fixed; }
    .tot-tbl td      { padding:7px 12px; border-bottom:1px solid #ebebeb; }
    .tot-lbl         { font-weight:700; color:#444; font-size:3rem}
    .tot-val         { font-weight:700; color:#010067; text-align:right; font-size:4.5rem}
    .tot-total-row td{ background:#010067; color:#fff; font-size:3rem; font-weight:700; }
    .tot-total-row .tot-val { color:#fff; font-size:4.5rem}

    /* firma */
    .firma-wrap { margin-top:28px; }
    .firma-line { border-top:2px solid #010067; width:200px; padding-top:5px;
                  font-size:1.2rem; text-transform:uppercase; letter-spacing:.5px; color:#555;
                  text-align:center; }
  </style>
</head>
<body>

<div class="accent"></div>
<table class="hdr" width="100%">
  <tr>
    <td width="28%">
      <div class="logo-wrap">
        <img src="' . $logo . '" style="max-height:85px;max-width:220px;">
      </div>
    </td>
    <td width="44%" class="hdr-info">
      <b>' . htmlspecialchars($cab['txtNOMBRE_COMERCIAL_EMPRESA']) . '</b>
      <p>RUC: ' . htmlspecialchars($cab['txtNRO_DOCUMENTO_EMPRESA']) . '</p>
      <p>TELÉFONO: ' . htmlspecialchars($cab['txtTELEFONOS_EMPRESA']) . '</p>
      <p>' . htmlspecialchars($cab['txtDIRECCION_EMPRESA']) . '</p>
    </td>
    <td width="28%" align="right">
      <div class="badge">
        <div class="badge-type">Cotización</div>
        <div class="badge-num">' . $datos['Anio'] . '-' . $numeroPad . '</div>
        <div class="badge-date">Fecha: ' . htmlspecialchars($cab['FECHA_HOY']) . '</div>
      </div>
    </td>
  </tr>
</table>

<div class="cbox">
  <table>
    <tr>
      <td width="12%"><b>SEÑOR(ES):</b></td>
      <td colspan="5">' . htmlspecialchars($datos['Cliente']) . '</td>
    </tr>
    <tr>
      <td><b>DIRECCIÓN:</b></td>
      <td colspan="5">' . htmlspecialchars($datos['ClienteDireccion']) . '</td>
    </tr>
    <tr>
      <td><b>DOCUMENTO:</b></td>
      <td width="20%">' . htmlspecialchars($datos['DniRuc']) . '</td>
      <td width="12%"><b>VENDEDOR:</b></td>
      <td width="20%">' . htmlspecialchars($datos['UsuarioReg']) . '</td>
      <td width="14%"><b>FECHA DE EMISIÓN:</b></td>
      <td>' . htmlspecialchars(date("d/m/Y", strtotime($datos['FechaReg']))) . '</td>
    </tr>
  </table>
</div>

<table class="ptable">
  <thead>
    <tr>
      <th style="text-align:center;width:3%;">#</th>
      <th style="text-align:center;width:6%;">Cant.</th>
      <th colspan="2" style="text-align:left;width:23%;">Descripción</th>
      <th style="text-align:center;width:10%;">Modelo</th>
      <th style="text-align:center;width:10%;">Marca</th>
      <th style="text-align:center;width:6%;">Talla</th>
      <th style="text-align:center;width:8%;">Color</th>
      <th style="text-align:center;width:8%;">Género</th>
      <th style="text-align:center;width:8%;">Botapie</th>
      <th style="text-align:right;width:9%;">Precio U.</th>
      <th style="text-align:right;width:9%;">Total</th>
    </tr>
  </thead>
  <tbody>
    ' . $productosRows . '
  </tbody>
  <tfoot>
    <tr class="subtotal-row">
      <td colspan="11" style="text-align:right;color:#555;">Subtotal</td>
      <td style="text-align:right;color:#010067;">' . $moneda . ' ' . $totalFmt . '</td>
    </tr>
    ' . ($IGV > 0 ? '
    <tr class="subtotal-row">
      <td colspan="11" style="text-align:right;color:#555;">IGV (18%)</td>
      <td style="text-align:right;color:#010067;">' . $moneda . ' ' . $IGVFmt . '</td>
    </tr>' : '') . '
    ' . ($TotalDescuento > 0 ? '
    <tr class="subtotal-row">
      <td colspan="11" style="text-align:right;color:#e67e22;">Descuentos</td>
      <td style="text-align:right;color:#e67e22;">- ' . $moneda . ' ' . $TotalDescuentoFmt . '</td>
    </tr>' : '') . '
    <tr class="total-row">
      <td colspan="11" style="text-align:right;">TOTAL</td>
      <td style="text-align:right;">' . $moneda . ' ' . $totalIGVFmt . '</td>
    </tr>
  </tfoot>
</table>

<table class="footer-tbl">
  <tbody>
    <tr>
      <td width="55%" style="padding-right:20px;">
        <div class="cond-title">Condiciones Comerciales</div>
        <table class="cond-tbl">
          <tbody>
            ' . $condHtml . '
          </tbody>
        </table>
      </td>

      <td width="45%" style="vertical-align:top;">
        <div class="cond-title">Resumen de Pago</div>
        <table class="tot-tbl">
          <tr>
            <td class="tot-lbl">Subtotal</td>
            <td class="tot-val">' . $moneda . ' ' . $totalFmt . '</td>
          </tr>
          ' . ($IGV > 0 ? '
          <tr>
            <td class="tot-lbl">IGV (18%)</td>
            <td class="tot-val">' . $moneda . ' ' . $IGVFmt . '</td>
          </tr>' : '') . '
          ' . ($TotalDescuento > 0 ? '
          <tr>
            <td class="tot-lbl">Descuentos</td>
            <td class="tot-val" style="color:#e67e22;">- ' . $moneda . ' ' . $TotalDescuentoFmt . '</td>
          </tr>' : '') . '
          <tr class="tot-total-row">
            <td class="tot-lbl">TOTAL</td>
            <td class="tot-val">' . $moneda . ' ' . $totalIGVFmt . '</td>
          </tr>
        </table>
      </td>
    </tr>
  </tbody>
</table>
<br>
<div class="firma-wrap">
  <div class="firma-line">Firma / Sello de autorización</div>
</div>

</body>
</html>';

// ─── GENERAR PDF ──────────────────────────────────────────────────────────────
$dompdf = new DOMPDF();
$dompdf->set_paper('A4', 'portrait');
$dompdf->load_html($html);
$dompdf->render();
$pdf = $dompdf->output();
file_put_contents($ruta . $fichero . '.pdf', $pdf);
