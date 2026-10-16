<?php
include_once($_SERVER["DOCUMENT_ROOT"] . '/views/validateUser.php');
//var_dump($remision);
//var_dump($docVenta);

/*$movimientoFecha = $movimiento['MovimientoFecha'];
$fechaStock = $movimiento['FechaStock'];

$partidaDist = $movimiento['PartidaDist'];
$partidaProv = $movimiento['PartidaProv'];
$partidaDpto = $movimiento['PartidaDpto'];
$llegadaDist = $movimiento['LlegadaDist'];
$llegadaProv = $movimiento['LlegadaProv'];
$llegadaDpto = $movimiento['LlegadaDpto'];

$destinoRS = $movimiento['DestinatarioRazonSocial'];
$destinoRUC = $movimiento['DestinatarioRUC'];

$transporteNumPlaca = $movimiento['TransporteNumPlaca'];
$transporteNumContrato = $movimiento['TransporteNumContrato'];
$transporteNumLicencia = $movimiento['TransporteNumLicencia'];
$transporteRazonSocial = $movimiento['TransporteRazonSocial'];
$transporteRUC = $movimiento['TransporteRUC'];

$docVentaSerie = $movimiento['DocVentaSerie'];
$docVentaNumero = $movimiento['DocVentaNumero'];
*/
$limitProducto = 8;

?>
<style>
  body {
    margin: 0;
    font-family: sans-serif;

  }
  .container {
    margin-top: 30mm;
    width: 200mm;
    margin-left: 6mm;
  /*  border: 1px solid red;*/
  }
  td {
    line-height: 6mm;
    font-size: 4mm;
    font-family: sans-serif;
    padding: 0 3mm;
    /*border: 1px solid black;*/
  }
  .emision {
    width: 690px;
  }
  .dia {
    margin-left: 220px;
  }
  .mes {
    width: 300px;
  }
  .mes span {
    margin-left: 10px;
  }
  .anio {
    width: 50px;

  }
  .anio span {
    margin-left: 15px;
  }
  .cliente {
    margin-left: 140px;
  }
  .ruc {
    width: 320px;
  }
  .ruc span {
    margin-left: 60px;
  }
  .totales {
    float: right;
    width: 310px;
  }
  .totales span {
    margin-left: 140px;
  }

  /* Estilos para productos */
  .productos {
  /*  height: 8em;*/
  }
  .productos {
    padding-top: 7mm;
    box-sizing: border-box;
  }
  .productos .cantidad {
    width: 70px;
    text-align: center;
  }
  .productos .detalle span{
    margin-left: 20px;
  }
  .productos .precio {
    width: 100px;
  }
  .productos .pTotal {
    width: 174px;
  }
  .productos .pTotal span {
    margin-left: 10px;
  }
  .peque td {
      white-space: nowrap;
  }
  .footer {
    width: 890px;
    float: left;
    margin-top: 5px;
  }
  .footer .son {
    margin-left: 80px;
  }
  .footer .user, .footer .fecha {
    margin-left: 230px;
  }
  .footer .small {
    display: block;
    font-size: 13px;
  }
  .footer .small:first-child {
    margin-top: 20px;
  }
  .prod-det {
    width: 126mm;
  }
</style>
<div class="container">

    <table width="100%" style="width:130mm">
        <tr>
            <td style="padding-left: 17mm;">
                <span class=""><?php echo date("Y-m-d", strtotime($remision['FechaReg'])) ?></span>
            </td>
            <td>
                <span class=""><?php echo date("Y-m-d", strtotime($remision['FechaTraslado'])) ?></span>
            </td>
        </tr>
    </table>
    <table width="100%">
        <tr>
            <td colspan="3"><?php echo strtoupper($remision['DireccionPartida']) ?></td>
            <td colspan="3"><?php echo strtoupper($remision['DireccionLlegada']) ?></td>
        </tr>
        <tr class="peque">
            <td>
                <span class=""><?php echo strtoupper($remision['DistritoPartida']) ?></span>
            </td>
            <td class="es">
                <span><?php echo strtoupper($remision['ProvinciaPartida']) ?></span>
            </td>
            <td class="">
                <span ><?php echo strtoupper($remision['DepartamentoPartida']); ?></span>
            </td>
            <td>
                <span class=""><?php echo strtoupper($remision['DistritoLlegada']) ?></span>
            </td>
            <td class="">
                <span><?php echo strtoupper($remision['ProvinciaLlegada']) ?></span>
            </td>
            <td class="">
                <span ><?php echo strtoupper($remision['DepartamentoLlegada']); ?></span>
            </td>
        </tr>
    </table>
  <table width="100%" style="height: 12mm;">
    <tr>
      <td>
        <span class=""><?php echo strtoupper($remision['RazonSocialDestinatario']); ?></span>
      </td>
      <td class="" style="text-align: right;">
        <span><?php echo strtoupper($remision['RucDestinatario']); ?></span>
      </td>
    </tr>
  </table>
  <table width="100%">
    <tr>
      <td>
        <span class=""><?php echo strtoupper($remision['EmpresaTransporte']); ?></span>
      </td>
      <td  style="text-align: right;">
        <span class=""><?php echo strtoupper($remision['MarcaPlacaTransporte']); ?></span>
      </td>
    </tr>
    <tr>
      <td  style="padding-left: 55mm;">
        <span class=""><?php echo strtoupper($remision['RucEmpresaTransporte']); ?></span>
      </td>
      <td  style="text-align: right;">
        <span class=""><?php echo strtoupper($remision['LicenciaConductor']); ?></span>
      </td>
    </tr>
  </table>
  <div class="productos">
    <table width="100%">
      <?php
      $filas = 0;
      foreach ($productos as $key => $producto) { ?>
          <tr>
            <td class="detalle prod-det">
              <span><?php echo mb_strimwidth($producto['Producto'], 0, 60, '...') ?></span>
            </td>
            <td class="cantidad">
              <span><?php echo $producto['Cantidad']; ?></span>
            </td>
            <td class="medida">
              <span><?php echo $producto['ProductoMedicion'] ?></span>
            </td>
            <td class="pesoTotal">
              <span><?php // echo $producto['PesoTotal'] ?></span>
            </td>
          </tr>
      <?php
      $filas += 1;
      }

      while ($filas < $limitProducto) {
      ?>
      <tr><td>&nbsp;</td><td></td><td></td><td></td></tr>
      <?php
        $filas += 1;
      }

     ?>
    </table>
  </div>



  <!--<table class="footer">
    <tr>
      <td>
        <span></span>
      </td>
    </tr>
    <tr>
      <td>
        <span><?php
            if ($movimiento['IdDocVenta']) {
                echo $docVentaSerie . '-' . $docVentaNumero;
            }
        ?></span>
      </td>
    </tr>
    <tr>
      <td>
        <span></span>
      </td>
    </tr>
  </table>-->
</div>
