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
// $direccion = strtoupper($docVenta['Direccion']);
$dniRuc = $docVenta['DniRuc'];
$tieneIgv = $docVenta['TieneIgv'];
$limitProducto = $docVenta['LimiteItems'];
$docVentaNro = $docVenta['Serie'] . ' - ' . str_pad( $docVenta['Numero'], 8, "0", STR_PAD_LEFT);
$fechaDoc = $docVenta['FechaDoc'];
$fecha = date("Y-m-d", strtotime($docVenta['FechaDoc']));
$serieMaq = $docVenta['SerieImpresora'];
$tipoDoc = $docVenta['TipoDoc'];

$docModifica = $docVenta['NroComprobanteModifica'];
$notaDescMotivo = $docVenta['NotaDescMotivo'];
$codSunatModifica = $docVenta['CodSunatModifica'];
$notaIdMotivo = $docVenta['NotaIdMotivo'];
$tipoDocModifica = $codSunatModifica=='01'? 'FACTURA ELECTRÓNICA':'BOLETA ELECTRÓNICA';
$comprobantemod = explode('-', $docModifica);
$seriemod = $comprobantemod[0];
$numeromod = $comprobantemod[1];

$subtotal = 0;
$total = 0;
$totalDescuento = 0;
$igv = 0;

if ($docVenta['CampoDireccion']) {
  $direccion = strtoupper($docVenta[$docVenta['CampoDireccion']]);
} else {
  $direccion = strtoupper($docVenta['Direccion']);
}
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
  <div class="center"><img src="<?php echo htmlspecialchars($ticketLogo, ENT_QUOTES, 'UTF-8'); ?>" style="max-width:100%; width: 40mm"/></div>
  <!--<div class="center"><b style="font-size: 30px !important;">GENESIS</b></div><br>-->
  <div class="center"><b><?php echo htmlspecialchars($empresaImpresion['razon_social']); ?></b></div>
  <div class="center"><b>RUC: <?php echo htmlspecialchars($empresaImpresion['ruc']); ?></b></div>
  <?php if ($empresaImpresion['telefono'] !== ''): ?><div class="center small">TEL. <?php echo htmlspecialchars($empresaImpresion['telefono']); ?></div><?php endif; ?>
  <?php if ($empresaImpresion['actividad'] !== ''): ?><div class="center small"><?php echo htmlspecialchars($empresaImpresion['actividad']); ?></div><?php endif; ?>

<?php if (!$docVenta['EsAlmacenPrincipal'] && $docVenta['DireccionAlmacen']) : ?>
    <div class="center small"><b>SUCURSAL: <?php echo $docVenta['DireccionAlmacen'] ?></b></div>
  <?php endif; ?>
  
  <?php if ($tipoDoc == 'TICKET BOLETA' || $tipoDoc == 'TICKET FACTURA'): ?>
    <div class="strike">
      <span><?php echo str_replace('TICKET ', '', $tipoDoc); ?></span>
    </div>
    <br />
  <?php endif; ?> 
  <center>
  <div class="">NOTA DE CREDITO ELECTRÓNICA<br>
  <?php echo $docVentaNro; ?></div>
  </center>
  <div class="">FECHA: <?php echo $fechaDoc; ?></div>

  </center>
  <div class="separar"></div>
  <div>DOCUMENTO QUE MOFIDICA:</div>
  <div>TIPO DOCUMENTO:  <?php echo $tipoDocModifica ?></div>
  <div>COMPROBANTE: <?php echo $docModifica; ?></div>
  <div>MOTIVO: <?php echo $notaIdMotivo." - ".$notaDescMotivo ?></div>

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
          <th class="unitario text-right">P/U</th>
          <th class="precio text-right">TOTAL</th>
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
              <span><?php echo $producto['Producto'] ?></span>
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
        <?php if ($igv > 0) : ?>
          <tr>
            <td></td>
            <td colspan="2" class="text-right">IGV</td>
            <td class="text-right">S/.<?php echo number_format($igv, 2); ?></td>
          </tr>
        <?php endif; ?>
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
    <span class="son">SON: <?php echo strtoupper(NumerosEnLetras::convertir(number_format($total - $totalDescuento, 2, '.', ''),'SOLES',true, 'asd')); ?></span><br/>
    <?php if ($docVenta['PagoCon'] > 0) : ?>
      <span>PAGÓ CON: S/. <?php echo number_format($docVenta['PagoCon'], 2); ?></span><br />
      <span>VUELTO: S/.<?php echo number_format($docVenta['PagoCon'] - $total, 2) ?></span>
    <?php endif; ?>
    <span style="text-transform: uppercase">FORMA DE PAGO: <?php echo empty($docVenta['EsCredito']) ? 'CONTADO' : 'CRÉDITO' ; ?></span><br />
    <?php if(!empty($docVenta['EsCredito'])): ?>
      <span style="text-transform: uppercase">FECHA DE PAGO: <?php echo date('Y-m-d', strtotime($docVenta['FechaCredito'])); ?></span>
    <?php endif; ?>
    <br/>

<?php if ($empresaImpresion['consulta_web'] !== ''): ?><span style="font-size:11px">CONSULTA WEB:<br><?php echo htmlspecialchars($empresaImpresion['consulta_web']); ?></span><?php endif; ?>
  </div>
  <br />
  <div class="center">
        <?php
        $numeroDocumentoEmpresa = $empresaImpresion['ruc'];
        $tipoDocCliente = strlen($docVenta['DniRuc']) > 9 ? "6" : "1";
        if($docVenta['CodSunat']=='03'){ $tdocumento='BOLETA ELECTRÓNICA'; }
        if($docVenta['CodSunat']=='01'){ $tdocumento='FACTURA ELECTRÓNICA'; }

        $text = $numeroDocumentoEmpresa . ' | ' . $tdocumento. ' | ' . $docVenta['Serie'] . ' | ' . $docVenta['Numero'].
          ' | ' . number_format($igv, 2) . ' | ' . number_format($total - $totalDescuento, 2) . ' | ' . $fecha . 
          ' | ' . $tipoDocCliente . ' | ' . $docVenta['DniRuc'] . ' |';        

        QRcode::png($text, 'qr.png', 'Q',15, 0);
        $imagedata = file_get_contents("qr.png");
        $base64 = base64_encode($imagedata);
        ?>
        <img style="max-width: 100%; width: 100px;" src="data:image/png;base64,<?php echo $base64 ?>" />
  </div>
  <br />
  <div class="center small"><?php echo htmlspecialchars($empresaImpresion['direccion']); ?></div>
  <div class="center small"><?php echo htmlspecialchars($empresaImpresion['ubicacion']); ?></div>
  <div class="center small">GRACIAS POR SU COMPRA</div>
  <center><br /><span style="font-size:11px">DESARROLLADO POR:<br>neurosoft.com.pe</span></center>
  <br />
</div>
