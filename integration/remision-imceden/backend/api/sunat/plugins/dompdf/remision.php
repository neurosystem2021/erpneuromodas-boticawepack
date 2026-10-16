<?php

//require_once("dompdf/dompdf_config.inc.php");

$rutat=	'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
$rutat= str_replace("plugins/dompdf/index.php", "", $rutat);

require_once 'lib/html5lib/Parser.php';
require_once 'lib/php-font-lib/src/FontLib/Autoloader.php';
require_once 'lib/php-svg-lib/src/autoload.php';
require_once 'src/Autoloader.php';
Dompdf\Autoloader::register();
use Dompdf\Dompdf;
use Dompdf\Options;
include "../phpqrcode/qrlib.php";

// require "../../modelos/resumen.php";
// require "../../modelos/numeros-letras.php";

// $resumen=new Resumen();

// $id=$_GET['id'];

// $sql="SELECT *FROM venta WHERE idventa='$id' ";
// $mostrar= ejecutarConsultaSimpleFila($sql);

// $sql2="SELECT *FROM persona WHERE idpersona='$mostrar[txtID_CLIENTE]' ";
// $mcliente= ejecutarConsultaSimpleFila($sql2);

// $sql3="SELECT *FROM config WHERE estado='1' ";
// $mempresa= ejecutarConsultaSimpleFila($sql3);
$bodyRequest = file_get_contents("php://input");
$cab = json_decode($bodyRequest, true);


if($cab['TIPO_PROCESO']=='03'){ $tipop='BETA'; }else{ $tipop='PRODUCCION'; }

if($cab['txtCOD_MONEDA']=='PEN'){ $valmoneda='SOLES'; }
if($cab['txtCOD_MONEDA']=='USD'){ $valmoneda='DOLARES'; }
if($cab['txtCOD_MONEDA']=='EUR'){ $valmoneda='EUROS'; }

$ruta="../../api_cpe/".$tipop."/".$cab['NRO_DOCUMENTO_EMPRESA']."/";
$fichero=$cab['NRO_DOCUMENTO_EMPRESA'].'-'.$cab['COD_TIPO_DOCUMENTO'].'-'.$cab['NRO_COMPROBANTE'];

$tdocumento='GUÍA DE REMISIÓN REMITENTE';


$comprobante = explode('-', $cab['NRO_COMPROBANTE']);
$serie = $comprobante[0];
$numero = $comprobante[1];
//QRcode::png("".$text);
//DATOS OBLIGATORIOS DE LA SUNAT EN EL QR
//RUC | TIPO DE DOCUMENTO | SERIE | NUMERO | MTO TOTAL IGV | MTO TOTAL DEL COMPROBANTE | FECHA DE //EMISION |TIPO DE DOCUMENTO ADQUIRENTE | NUMERO DE DOCUMENTO ADQUIRENTE |

$text = $cab['NRO_DOCUMENTO_EMPRESA'].' | '.$tdocumento.' | '.$serie.' | '.$numero.' | ' . ' | ' . ' | '.$cab['txtFECHA_DOCUMENTO'].' | '.$cab['txtTIPO_DOCUMENTO_CLIENTE'].' | '.$cab['txtNRO_DOCUMENTO_CLIENTE'].' |';
QRcode::png($text, $cab['NRO_DOCUMENTO_EMPRESA'].".png", 'Q',15, 0);

 $html =
   '
<html> 
   <head> 
   <style> 
body{
font:10px Arial, Tahoma, Verdana, Helvetica, sans-serif;
color:#000;
}
.cabecera table {
	width: 100%;
    color:black;
    margin-top: 0em;
    text-align: left; font-size: 10px;
}
.cabecera h1 {
    font-size:17px; padding-bottom: 0px; margin-bottom: 0px; te
}
.cabecera2 table { border-collapse: collapse; border: solid 1px #000000;}
.cabecera2 th, .cabecera2 td { text-align: center; border-collapse: collapse; border: solid 1px #000000; font-size:12px; line-height: 20px; } 
.cabeza{ text-align: left; }
.nfactura{ background-color: #D8D8D8; }
.cuerpo table { border-collapse: collapse; margin-top:1px; border: solid 1px #000000; }
.cuerpo thead { border: solid 1px #000000; } 
.cuerpo2 thead { border: solid 1px #000000; } 
table { width: 100%; color:black; }
  
tbody { background-color: #ffffff; }
th,td { padding: 3pt; }           
.celda_right{  border-right: 1px solid black;  }
.celda_left{  border-left: 1px solid black; }         
.footer th, .footer td { padding: 1pt; }
td { font-size: 9px; padding: 3px; }
.footer { position: fixed; bottom: 100px; font-size:10px;  width: 100%; border: solid 0px #000000; }
.fg { font-size: 10px;} 
.fg2 { text-align: center; }
.fg3 { border: solid 0px; } 
.total td { border: solid 0px; padding: 0px; } 
.total2 { text-align: right; } 
   </style>
    
   </head> 
    
   <body>        
   
<table width="100%" border="0" class="cabecera" cellpadding="0" cellspacing="0">
  <tbody>
    <tr>
      <td width="6%"><img src="../../images/logo.png" width="140" height="80" /></td>
        
      <td class="cabeza"><h2>'.$cab['NOMBRE_COMERCIAL_EMPRESA'].'</h2>
        <strong></strong> '.$cab['DIRECCION_EMPRESA'].'<br>
        <!--<strong>TELF. PRINCIPAL:</strong> '. '' .'<br>-->
      </td>
		
      <td width="30%">
        <table width="100%" class="cabecera2" cellspacing="0" >
          <tbody>
            <tr>
              <td >RUC N° '.$cab['NRO_DOCUMENTO_EMPRESA'].'</td>
            </tr>
            <tr>
              <td class="nfactura">'.$tdocumento.'</td>
            </tr>
            <tr>
              <td >'.$cab['NRO_COMPROBANTE'].'</td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
  </tbody>
</table>
<br>
<!--<table width="100%" class="cuerpo" cellspacing="0">
  <thead>
    <tr>
      <td width="10%">NRO.DOCU.:</td>
      <td width="60%">'.$cab['NRO_DOCUMENTO_CLIENTE'].'</td>
      <td width="10%">FECHA:</td>
      <td width="20%">'.date("Y-m-d", strtotime($cab['txtFECHA_DOCUMENTO'])).'</td>
    </tr>
    <tr>
      <td>CLIENTE:</td>
      <td>'.$cab['txtRAZON_SOCIAL_CLIENTE'].'</td>
      <td>NRO.GUIA:</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>DIRECCIÓN:</td>
      <td>'.$cab['txtDIRECCION_CLIENTE'].'</td>
      <td>MONEDA:</td>
      <td>'.$valmoneda.'</td>
    </tr>
  </thead>
</table>-->
<br>
<table width="100%" class="cuerpo2" cellspacing="0">
  <thead>
    <tr><td colspan="4" style="text-align:center; border-bottom: 1px solid #000"><b>DESTINATARIO</b></td></tr>
    <tr>
      <td colspan="2" width="50%"><b>RAZÓN SOCIAL:</b> '.$cab['RAZON_SOCIAL_CLIENTE'].'</td>
      <td colspan="2" width="50%"><b>RUC:</b> '.$cab['NRO_DOCUMENTO_CLIENTE'].'</td>
    </tr>
    <tr>
    <td colspan="4"><b>DIRECCIÓN:</b> '.$cab['DIRECCION_CLIENTE'].'</td>
    </tr>
  </thead>
</table>
<br />
<table width="100%" class="cuerpo2" cellspacing="0">
  <thead>
    <tr><td colspan="4" style="text-align:center; border-bottom: 1px solid #000"><b>ENVÍO</b></td></tr>
    <tr>
      <td colspan="2" width="50%"><b>FECHA EMISIÓN:</b> '.date("d/m/Y" , strtotime($cab['FECHA_DOCUMENTO'])).'</td>
      <td colspan="2" width="50%"><b>FECHA INICIO DE TRASLADO:</b> '.date("d/m/Y" , strtotime($cab['FECHA_INICIO'])).'</td>
    </tr>
    <tr>
      <td colspan="2" width="50%"><b>MOTIVO TRASLADO:</b> '.$cab['DESCRIPCION_MOTIVO_TRASLADO'].'</td>
      <td colspan="2" width="50%"><b>MODALIDAD DE TRANSPORTE:</b> '.$cab['COD_MODALIDAD_TRASLADO'].'</td>
    </tr>
    <tr>
      <td colspan="2" width="50%"><b>PESO BRUTO TOTAL (KGM):</b> '.$cab['PESO_BRUTO'].'</td>
      <td width="15%"></td>
      <td width="35%"></td>
    </tr>
    <tr>
      <td colspan="2" width="50%"><b>P. PARTIDA:</b> '.$cab['DIRECCION_ORIGEN'].'</td>
      <td colspan="2" width="50%"><b>P. LLEGADA:</b> '.$cab['DIRECCION_DESTINO'].'</td>
    </tr>
  </thead>
</table>
<br />
<table width="100%" class="cuerpo2" cellspacing="0">
  <thead>
    <tr><td colspan="4" style="text-align:center; border-bottom: 1px solid #000"><b>TRANSPORTE</b></td></tr>
    <tr>
      <td colspan="2" width="50%"><b>RAZÓN SOCIAL:</b> '.$cab['RAZON_SOCIAL_TRANSPORTISTA'].'</td>
      <td colspan="2" width="50%"><b>RUC: </b>'.$cab['NRO_DOCUMENTO_TRANSPORTISTA'].'</td>
    </tr>
    <tr>
      <td colspan="2" width="50%"><b>PLACA VEHÍCULO:</b> '.$cab['PLACA_VEHICULO'].'</td>
      <td colspan="2" width="50%"><b>DOCUMENTO CONDUCTOR:</b> '.$cab['NRO_DOC_CHOFER'].'</td>
    </tr>
    <tr>
      <td colspan="2"><b>PLACA CARRETA: </b> '.$cab['PLACA_CARRETA'].'</td>
      <td colspan="2"><b>LICENCIA CONDUCTOR: </b> '.$cab['LICENCIA_CHOFER'].'</td>
    </tr>
  </thead>
</table>
<br />
<table width="100%" class="cuerpo2" border="0" cellspacing="0">
<thead> 
    <tr>
      <td width="5%">ITEM</td>
      <td width="10%">CODIGO</td>
      <td width="60%">DESCRIPCION</td>
      <td width="10%">UNIDAD</td>
      <td width="10%">CANTIDAD</td>
      <!--<td width="10%">PESO</td>-->
    </tr>
</thead>
<tbody>
';
// $rspta = $resumen->detfactura($mostrar['idventa']);
// while ($reg = $rspta->fetch_object()){	

foreach ($cab['detalle'] as $producto) {
  $html.='
    <tr>
      <td>'.$producto['ORDER_ITEM'].'</td>
      <td>'.$producto['CODIGO'].'</td>
      <td>'.$producto['DESCRIPCION'].'</td>
      <td>'.$producto['UNIDAD_MEDIDA'].'</td>
      <td>'.$producto['CANTIDAD'].'</td>
      <!--<td>'.$producto['txtIMPORTE_DET'].'</td>-->
    </tr>';
}

$html.='
  </tbody>
</table>
</div>
<table width="100%"  class="footer" border="0" cellspacing="0">
  <tbody>
    <tr>
      <td width="50%" style="vertical-align:top">
      <b>OBSERVACIONES:</b> '.$cab['OBSERVACION'].'
      </td>
      <td width="30%" rowspan="4" style="text-align:center; vertical-align:bottom">
      <b>_________________________________ <br><br>
      CONFORMIDAD DEL CLIENTE</b> 
      </td>
      <td width="20%" rowspan="4" style="text-align:center">
      <img src="'.$cab['NRO_DOCUMENTO_EMPRESA'].'.png" width="100" height="100" />
      </td>
    </tr>
    <tr>
      <td>
      <b>COMPROBANTE DE PAGO</b>
      </td>
    </tr>
    <tr>
      <td style="font-size:12px;">
      '.$cab['DOCUMENTO_REFERENCIA'].'
      </td>
    </tr>
    <tr>
      <td>
        Representación impresa de la '.$tdocumento.'
      </td>
    </tr>
  </tbody>
</table>
</body> </html>';

 $dompdf = new DOMPDF();
 $dompdf->set_paper('letter','portrait');
 //$dompdf->set_paper('legal','landscape');
 $dompdf->load_html($html);
 $dompdf->render();
 //$dompdf->stream("pdf".Date('Y-m-d').".pdf");
 //$dompdf->stream("ejemplo-basico.pdf", array('Attachment' => 0));
 $pdf = $dompdf->output();
 //file_put_contents('../'.$ruta, $pdf);
//  var_dump($ruta.$fichero.'.pdf');exit;
 file_put_contents($ruta.$fichero.'.pdf', $pdf);

// var_dump($ruta.$fichero.'.pdf');exit();

?>