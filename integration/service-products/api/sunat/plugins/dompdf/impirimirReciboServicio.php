<?php

//require_once("dompdf/dompdf_config.inc.php");

$rutat =    'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
$rutat = str_replace("plugins/dompdf/nota.php", "", $rutat);

require_once 'lib/html5lib/Parser.php';
require_once 'lib/php-font-lib/src/FontLib/Autoloader.php';
require_once 'lib/php-svg-lib/src/autoload.php';
require_once 'src/Autoloader.php';
Dompdf\Autoloader::register();

use Dompdf\Dompdf;
use Dompdf\Options;

include "../phpqrcode/qrlib.php";
$bodyRequest = file_get_contents("php://input");
$cab = json_decode($bodyRequest, true);
require_once __DIR__.'/../../../../imprimir/serviceReceiptProducts.php';
$productsHtml=serviceReceiptProductsHtml($cab['productos'] ?? []);



$html = '

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .center{
        text-align:center;
        }
    </style>

    
</head>
<body>
    <div class="container">
    <div class="center"><img src="../../../../resources/images/logo-ticket.png" style="max-width:100%; width: 55mm" /></div><br>
    <div class="center">
        <span style="font-size:22px">RECIBO DE SERVICIO</span>
    </div>
    <div class="center"><b>CONSULTORA CONSTRUCTORA NEUROSYSTEM PERU S.A.C.</b></div>
    <div class="center"><b>RUC: 20573027125</b></div>
    <div class="center">
        '.$cab['fechaHoraActual'].'
    </div>
    <br>
    <div class="center" style="font-size:18px">Código: ' . $cab['detalle']['IdProducto'] . '</div>

    <table width="100%">
        <tr>
            <td>DNI/RUC:</td>
            <td>' . $cab['detalle']['DniRuc'] . '</td>
        </tr>
        <tr>
            <td>Cliente:</td>
            <td>' . $cab['detalle']['Cliente'] . '</td>
        </tr>
    </table>
    <p>Descripcion: </p>
    <p>' . $cab['detalle']['Descripcion'] . '</p>
    ' . $productsHtml . '
    <table width="100%" style="margin-top:18px;border-top:1px dashed #000;border-bottom:2px solid #000">
      <tr><td>Total del servicio</td><td align="right">S/ ' . number_format((float)($cab['detalle']['TotalServicio'] ?? 0),2) . '</td></tr>
      <tr><td>Adelanto recibido</td><td align="right">S/ ' . number_format((float)($cab['detalle']['TotalAdelantos'] ?? 0),2) . '</td></tr>
      <tr><td><b>SALDO PENDIENTE</b></td><td align="right"><b>S/ ' . number_format((float)($cab['detalle']['SaldoPendiente'] ?? 0),2) . '</b></td></tr>
    </table>

</div>
</body>
</html>

';

$ruta = "../../api_cpe/PRODUCCION/" . $cab['txtNRO_DOCUMENTO_EMPRESA'] . "/";
$fichero = 'impirimirReciboServicio_'.$cab['fecha'];
// $fichero = 'impirimirReciboServicio_';

$dompdf = new DOMPDF();
$dompdf->set_paper('A4', 'portrait');
// $dompdf->set_paper('A4', 'landscape');
//$dompdf->set_paper('legal','landscape');
$dompdf->load_html($html);
$dompdf->render();
//$dompdf->stream("pdf".Date('Y-m-d').".pdf");
//$dompdf->stream("ejemplo-basico.pdf", array('Attachment' => 0));
$pdf = $dompdf->output();
//file_put_contents('../'.$ruta, $pdf);
file_put_contents($ruta . $fichero . '.pdf', $pdf);
