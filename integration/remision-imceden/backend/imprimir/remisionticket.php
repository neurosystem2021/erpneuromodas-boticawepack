<?php
require_once __DIR__ . '/../models/EmpresaConfig.php';
$configEmpresa = empresaConfigObtener();
$empresa = empresaConfigDatosImpresion($configEmpresa);
$logoRelativo = empresaConfigValor($configEmpresa, 'logo_principal', 'resources/images/logo.png');
$logoFisico = realpath(__DIR__ . '/../' . ltrim($logoRelativo, '/\\'));
$logoUrl = $logoFisico && is_file($logoFisico) ? '../' . str_replace('\\', '/', ltrim($logoRelativo, '/\\')) : '../resources/images/logo.png';
$h = function ($valor) { return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8'); };
$docVentaNro = ($movimiento['Serie'] ?? '') . ' - ' . str_pad((string) ($movimiento['Numero'] ?? ''), 8, '0', STR_PAD_LEFT);
$fechaDoc = $movimiento['MovimientoFecha'] ?? '';
$tipoDoc = $movimiento['MovimientoTipo'] ?? '';
$total = 0;
$totalDescuento = 0;
?>
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link rel="stylesheet" href="../resources/css/movement-print-mobile.css?v=2026081701">
<style>
  *{box-sizing:border-box;font-family:Arial,sans-serif}body{display:block;margin:12px;color:#111;background:#fff}.container{width:100%;max-width:100%}.center{text-align:center}.empresa{display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:10px}.empresa img{width:76px;max-height:58px;object-fit:contain}.empresa-nombre{font-size:18px;font-weight:700}.empresa-dato{margin-top:2px;font-size:11px}.separar{margin:9px 0;border-top:1px dashed #555}.movimiento-datos{line-height:1.55;font-size:13px}.productos table{width:100%;border-collapse:collapse;font-size:.9rem}.productos th,.productos td{padding:5px 7px;border-top:1px solid #333}.productos th{font-size:12px;text-transform:uppercase}.productos td{line-height:1.35}.cantidad{text-align:center}.text-right{text-align:right}.producto{font-weight:600}.total-row td{border-top:2px solid #222;font-size:14px}.print-actions{display:flex;justify-content:center;gap:10px;margin:28px 0}.print-actions button{padding:11px 18px;border:0;border-radius:7px;color:#fff;background:#075f91;font-weight:700;cursor:pointer}.print-actions .volver{background:#555}
  @media print{@page{size:A4 portrait;margin:12mm}body{margin:0}.no-print{display:none!important}}
</style>
<div class="container">
  <div class="empresa">
    <img src="<?= $h($logoUrl) ?>" alt="Logo JOBI">
    <div><div class="empresa-nombre"><?= $h($empresa['nombre_comercial']) ?></div><div class="empresa-dato">RUC: <?= $h($empresa['ruc']) ?></div><div class="empresa-dato"><?= $h($empresa['direccion']) ?></div></div>
  </div>
  <div class="separar"></div>
  <?php if ($tipoDoc === 'TICKET BOLETA' || $tipoDoc === 'TICKET FACTURA'): ?><div class="center"><strong><?= $h(str_replace('TICKET ', '', $tipoDoc)) ?></strong></div><br><?php endif; ?>
  <div class="movimiento-datos"><div><strong>MOVIMIENTO NRO:</strong> <?= $h($docVentaNro) ?></div><div><strong>FECHA:</strong> <?= $h($fechaDoc) ?></div><div><strong>TIPO:</strong> <?= $h($movimiento['TipoMovimiento'] ?? '') ?></div></div>
  <div class="separar"></div><br>
  <div class="productos"><table><thead><tr><th class="cantidad">Cant.</th><th>Producto</th><th>Modelo</th><th>Marca</th><th>Talla</th><th>Color</th><th>Género</th><th>Botapié</th><th>P/U</th><th class="text-right">Total</th></tr></thead><tbody>
  <?php foreach ($movimientoDet as $producto): $importe=(float)($producto['Cantidad']??0)*(float)($producto['Precio']??0); ?>
    <tr><td class="cantidad" data-label="Cantidad"><?= $h($producto['Cantidad']??'') ?></td><td class="producto" data-label="Producto"><?= $h($producto['Producto']??'') ?></td><td data-label="Modelo"><?= $h($producto['ProductoModelo']??'') ?></td><td data-label="Marca"><?= $h($producto['ProductoMarca']??'') ?></td><td data-label="Talla"><?= $h($producto['ProductoTalla']??'') ?></td><td data-label="Color"><?= $h($producto['Color']??'') ?></td><td data-label="Género"><?= $h($producto['Genero']??'') ?></td><td data-label="Botapié"><?= $h($producto['Botapie']??'') ?></td><td data-label="Precio unitario">S/.<?= $h(number_format((float)($producto['Precio']??0),2)) ?></td><td class="text-right" data-label="Importe">S/.<?= $h(number_format($importe,2)) ?></td></tr>
  <?php $total += $importe; $totalDescuento += (float)($producto['Descuento']??0); endforeach; ?>
  <?php if ($totalDescuento>0): ?><tr><td colspan="8"></td><td class="text-right">DESCUENTO</td><td class="text-right">- S/.<?= $h(number_format($totalDescuento,2)) ?></td></tr><?php endif; ?>
  <tr class="total-row"><td colspan="8"></td><td class="text-right" data-label="Resumen"><strong>TOTAL</strong></td><td class="text-right" data-label="Importe total"><strong>S/.<?= $h(number_format($total,2)) ?></strong></td></tr>
  </tbody></table></div>
</div>
<div class="print-actions no-print"><button type="button" onclick="window.print()">Imprimir</button><button type="button" class="volver" onclick="returnToMovementReport()">Volver</button></div>
<script>
  function returnToMovementReport() {
    if (window.opener && !window.opener.closed) {
      try {
        window.opener.focus();
        window.close();
        return;
      } catch (error) {}
    }
    if (window.history.length > 1 && document.referrer) {
      window.history.back();
      return;
    }
    window.location.href = '../views/VentasApp.php?view=movementRegister';
  }
</script>
