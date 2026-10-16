<?php
include_once("../controllers/NumerosEnLetras/NumerosEnLetras.php");
include_once($_SERVER["DOCUMENT_ROOT"] . '/views/validateUser.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/models/EmpresaConfig.php');
$ticketEmpresaConfig = empresaConfigObtener();
$ticketLogo = '/' . ltrim(empresaConfigValor($ticketEmpresaConfig, 'logo_ticket', 'resources/images/logo-ticket.png'), '/');
$empresaImpresion = empresaConfigDatosImpresion($ticketEmpresaConfig);

//var_dump($docVenta);
$emision = strtotime($docVenta['FechaReg']);
$day = date("d", $emision);
$mes = date("m", $emision);
$meses = array(
  '01' => 'Enero',
  '02' => 'Febrero',
  '03' => 'Marzo',
  '04' => 'Abril',
  '05' => 'Mayo',
  '06' => 'Junio',
  '07' => 'Julio',
  '08' => 'Agosto',
  '09' => 'Septiembre',
  '10' => 'Octubre',
  '11' => 'Noviembre',
  '12' => 'Diciembre'
);
$lastAnio = substr(date("Y", $emision), -1);
$cliente = strtoupper($docVenta['Cliente']);
$direccion = strtoupper($docVenta['Direccion']);
$dniRuc = $docVenta['DniRuc'];
$telefono = $docVenta['Telefono'];
$tieneIgv = isset($docVenta['TieneIgv']) ? $docVenta['TieneIgv'] : null;
$limitProducto = isset($docVenta['LimiteItems']) ? $docVenta['LimiteItems'] : null;
$docVentaNro = $docVenta['Anio'] . ' - ' . str_pad($docVenta['Numero'], 8, "0", STR_PAD_LEFT);
$fechaDoc = $docVenta['FechaReg'];
$serieMaq = isset($docVenta['SerieImpresora']) ? $docVenta['SerieImpresora'] : null;
$tipoDoc = isset($docVenta['TipoDoc']) ? $docVenta['TipoDoc'] : null;
// $tipoDoc = $docVenta['TipoDoc'];
$diasValidos = $docVenta['DiasValidos'];

$subtotal = 0;
$total = 0;
$totalDescuento = 0;
$igv = 0;
?>
<style>
  * {
    font-size: 2.8mm;
    font-family: sans-serif;
  }

  body {
    display: block;
    margin: 0;
  }

  td,
  th,
  tr,
  table {
    border-top: 1px solid black;
    border-collapse: collapse;
  }

  .container {
    width: 80mm;
    max-width: 100%;
    padding: 3mm 4.5mm 3mm 4mm;
    box-sizing: border-box;
  }

  .center {
    text-align: center;
  }

  .separar {
    margin: 8px 0;
    border-top: 1px dashed #000;

  }

  td {
    line-height: 1.48em;
    font-size: 1em;
  }

  td,
  th {
    padding: 0 2px;
  }

  td.cantidad {
    text-align: center;
  }

  .text-right {
    text-align: right;
  }

  .small {
    font-size: 11px;
  }
</style>
<div class="container">
  <!--<div class="center">
    <img width="80px" src="../resources/images/delmancito.jpg"  /><br /><br />
  </div>-->
  <div class="center"><img src="<?php echo htmlspecialchars($ticketLogo, ENT_QUOTES, 'UTF-8'); ?>" style="max-width:100%; width: 40mm" /></div><br />
  <div class="center"><b><?php echo htmlspecialchars($empresaImpresion['razon_social']); ?></b></div>
  <div class="center"><b>RUC: <?php echo htmlspecialchars($empresaImpresion['ruc']); ?></b></div>
  <?php if ($empresaImpresion['telefono'] !== ''): ?><div class="center small">TELÉFONO: <?php echo htmlspecialchars($empresaImpresion['telefono']); ?></div><?php endif; ?><br>
  <!--<div class="center small">VENTA AL ALIMENTOS Y BEBIDAS EN GENERAL</div><br>-->

  <?php if ($tipoDoc == 'TICKET BOLETA' || $tipoDoc == 'TICKET FACTURA'): ?>
    <div class="strike">
      <span><?php echo str_replace('TICKET ', '', $tipoDoc); ?></span>
    </div>
    <br />
  <?php endif; ?>

  <div class=""><b>PROFORMA NRO: </b><?php echo $docVentaNro; ?></div>
  <div class="">FECHA: <?php echo $fechaDoc; ?></div>
  <div class="">VALIDEZ POR: <b><?php echo $diasValidos; ?></b> DIAS</div>

  <div class="separar"></div>
  <div>SR(ES) : <?php echo $cliente ?></div>
  <div>RUC/DNI : <?php echo $dniRuc; ?></div>
  <div>DIR : <?php echo $direccion; ?></div>
  <?php
  if (!empty($telefono)) {
    echo '<strong>TELÉFONO: </strong>' . $telefono . '<br>';
  }
  ?>
  <br />

  <div class="productos">
    <table width="100%">
      <thead>
        <tr>
          <th class="cantidad">CANT</th>
          <th class="producto">PRODUCTO</th>
          <th class="unidad">U.M.</th>
          <th class="unitario text-right">P/U</th>
          <th class="precio text-right">TOT</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $filas = 0;
        foreach ($productos as $key => $producto) { ?>
          <tr>
            <td class="cantidad">
              <span><?php echo $producto['Cantidad']; ?></span>
            </td>
            <td class="producto">
              <span style="font-size:11px"><?php echo $producto['Producto'] . (strlen(trim($producto['Descripcion'])) > 0 ? ' (' . trim($producto['Descripcion']) . ')' : ''); ?></span>
            </td>
            <td class="unidad">
              <?php echo !empty($producto['ProductoMedicion']) ? $producto['ProductoMedicion'] : 'UNIDAD'; ?>
            </td>
            <td class="precio text-right">
              <span>S/.<?php echo $producto['Precio'] ?></span>
            </td>
            <td class="text-right">
              <span>S/.<?php echo $producto['TOTAL'] ?></span>
            </td>
          </tr>
        <?php
          $filas += 1;
          $totalDescuento += floatval($producto['Descuento']);
          $total += floatval($producto['TOTAL']);
        }

        if ($tieneIgv) {
          $subtotal = $total / 1.18;
          $igv = $total - $subtotal;
        } else {
          $igv = '0.00';
          $subtotal = $total;
        }
        ?>
        <tr>
          <td></td>
          <td colspan="2" class="text-right">SUBTOTAL</td>
          <td class="text-right">S/.<?php echo number_format($subtotal, 2); ?></td>
        </tr>
        <!--<tr>
          <td></td>
          <td class="text-right">IGV</td>
          <td class="text-right">S/.<?php echo number_format($igv, 2); ?></td>
        </tr>-->
        <?php if ($totalDescuento > 0) : ?>
          <tr>
            <td></td>
            <td colspan="2" class="text-right">DESCUENTO</td>
            <td class="text-right">- S/.<?php echo number_format($totalDescuento, 2); ?></td>
          </tr>
        <?php endif; ?>
        <tr>
          <td></td>
          <td colspan="2" class="text-right"><strong>TOTAL</strong></td>
          <td class="text-right">S/.<?php echo number_format($total - $totalDescuento, 2); ?></td>
        </tr>
      </tbody>
    </table>
    <br />
    <span class="son">SON: <?php echo strtoupper(NumerosEnLetras::convertir(number_format($total - $totalDescuento, 2, '.', ''), 'SOLES', true, 'asd')); ?></span>

  </div>
  <br />
  <div class="center"><?php echo htmlspecialchars($empresaImpresion['direccion']); ?></div>
  <div class="center"><?php echo htmlspecialchars($empresaImpresion['ubicacion']); ?></div>
  <div class="center small">GRACIAS POR SU PREFERENCIA</div>
  <div class="center small">DESARROLLADO POR: neurosoft.com.pe</div>
  <div class="center small">.</div>
  <br />
  <br />

</div>
