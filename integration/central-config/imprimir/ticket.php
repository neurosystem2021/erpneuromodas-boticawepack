<?php
include_once("../controllers/NumerosEnLetras/NumerosEnLetras.php");
include_once($_SERVER["DOCUMENT_ROOT"] . '/views/validateUser.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/models/EmpresaConfig.php');
$ticketEmpresaConfig = empresaConfigObtener();
$ticketLogo = '/' . ltrim(empresaConfigValor($ticketEmpresaConfig, 'logo_ticket', 'resources/images/logo-ticket.png'), '/');
$empresaImpresion = empresaConfigDatosImpresion($ticketEmpresaConfig);

//var_dump($docVenta);
$emision = strtotime($docVenta['FechaDoc']);
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
  '12' =>'Diciembre'
);
$lastAnio = substr(date("Y", $emision), -1);
$cliente = strtoupper($docVenta['Cliente']);
if ($docVenta['CampoDireccion']) {
  $direccion = strtoupper($docVenta['CampoDireccion']);
} else {
  $direccion = strtoupper($docVenta['Direccion']);
}
$dniRuc = $docVenta['DniRuc'];
$tieneIgv = $docVenta['TieneIgv'];
$limitProducto = $docVenta['LimiteItems'];
$docVentaNro = $docVenta['Serie'] . ' - ' . str_pad( $docVenta['Numero'], 8, "0", STR_PAD_LEFT);
$fechaDoc = $docVenta['FechaDoc'];
$serieMaq = $docVenta['SerieImpresora'];
$tipoDoc = $docVenta['TipoDoc'];

$subtotal = 0;
$total = 0;
$totalDescuento = 0;
$igv = 0;
?>
<style>
  * {
    font-size: 3mm;
    font-family: sans-serif;
  }
  body {
    display: block;
    margin: 8px;
  }
  td,
  th,
  tr,
  table {
    border-top: 1px solid black;
    border-collapse: collapse;
  }
  .container {
    width: 100%;
    max-width: 100%;
    padding: 0 4mm;
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
  td, th {
    padding: 0 6px;
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
  <div class="center"><img src="<?php echo htmlspecialchars($ticketLogo, ENT_QUOTES, 'UTF-8'); ?>" style="display:block; max-width:100%; width:68mm; height:28mm; object-fit:cover; object-position:center 49%; filter:grayscale(1) brightness(.57) contrast(8); margin:0 auto"/></div>
  <!--<div class="center"><b style="font-size: 30px !important;">GENESIS</b></div><br>-->
  <div class="center"><b><?php echo htmlspecialchars($empresaImpresion['razon_social']); ?></b></div>
  <div class="center"><b>RUC: <?php echo htmlspecialchars($empresaImpresion['ruc']); ?></b></div>
  <?php if ($empresaImpresion['telefono'] !== ''): ?><div class="center small">TEL. <?php echo htmlspecialchars($empresaImpresion['telefono']); ?></div><?php endif; ?>
  <?php if ($empresaImpresion['actividad'] !== ''): ?><div class="center small"><?php echo htmlspecialchars($empresaImpresion['actividad']); ?></div><?php endif; ?>
  <?php if (!$docVenta['EsAlmacenPrincipal'] && $docVenta['DireccionAlmacen']) : ?>
    <div class="center small"><b>SUCURSAL: <?php echo $docVenta['DireccionAlmacen'] ?></b></div>
  <?php endif; ?>
  <br />
  
  <?php if ($tipoDoc == 'TICKET BOLETA' || $tipoDoc == 'TICKET FACTURA'): ?>
    <div class="strike">
      <span><?php echo str_replace('TICKET ', '', $tipoDoc); ?></span>
    </div>
    <br />
  <?php endif; ?>
  <center>
  <div class="">NOTA DE VENTA N°: <?php echo $docVentaNro; ?></div>
  <div class="">FECHA: <?php echo $fechaDoc; ?></div>
  <!-- <div class="">SERIE MAQ REG : <?php echo $serieMaq; ?></div> -->
  </center>
  <div class="separar"></div>
  <div>SR(ES) : <?php echo $cliente ?></div>
  <div>RUC/DNI : <?php echo $dniRuc; ?></div>
  <div>DIR : <?php echo $direccion; ?></div>

  <br />

  <div class="productos">
    <table width="100%">
      <thead>
        <tr>
          <th class="cantidad">CANT</th>
          <th class="producto">PRODUCTO</th>
          <!--<th class="unitario">P/U</th>-->
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
              <span><?php echo htmlspecialchars($producto['Producto'], ENT_QUOTES, 'UTF-8'); ?></span>
              <?php if (!empty($producto['Descripcion'])): ?><br><small><?php echo htmlspecialchars($producto['Descripcion'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
            </td>
            <!--<td class="precio">
              <span>S/.<?php echo $producto['Precio'] ?></span>
            </td>-->
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
          <td class="text-right">SUBTOTAL</td>
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
            <td class="text-right">DESCUENTO</td>
            <td class="text-right">- S/.<?php echo number_format($totalDescuento, 2); ?></td>
          </tr>
        <?php endif; ?>
        <tr>
          <td></td>
          <td class="text-right"><strong>TOTAL</strong></td>
          <td class="text-right">S/.<?php echo number_format($total - $totalDescuento, 2); ?></td>
        </tr>
      </tbody>
    </table>
    <br />
    <span class="son">SON: <?php echo strtoupper(NumerosEnLetras::convertir(number_format($total - $totalDescuento, 2, '.', ''),'SOLES',true, 'asd')); ?></span>

  </div>
  <br />
 
  <div class="center small"><?php echo htmlspecialchars($empresaImpresion['direccion']); ?></div>
  <!--<div class="center small">CEL: 968 405 486</div>-->
  <div class="center small"><?php echo htmlspecialchars($empresaImpresion['ubicacion']); ?></div>
  <div class="center small">GRACIAS POR SU COMPRA</div>
  <center><br /><span style="font-size:11px">DESARROLLADO POR:<br>neurosoft.com.pe</span></center>
  <br />

</div>
