<?php
$rutat =    'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
$rutat = str_replace("plugins/dompdf/index.php", "", $rutat);

require_once 'lib/html5lib/Parser.php';
require_once 'lib/php-font-lib/src/FontLib/Autoloader.php';
require_once 'lib/php-svg-lib/src/autoload.php';
require_once 'src/Autoloader.php';
Dompdf\Autoloader::register();

use Dompdf\Dompdf;
use Dompdf\Options;

include "../phpqrcode/qrlib.php";

$bodyRequest = file_get_contents("php://input");
print_r($bodyRequest);
$cab = json_decode($bodyRequest, true);

// if ($cab['TIPO_PROCESO'] == '03') {
// $tipop = 'BETA';
// } else {
$tipop = 'PRODUCCION';
// }


// $bodyRequest = file_get_contents("php://input");
// $cab = json_decode($bodyRequest, true);
$ruta = "../../api_cpe/" . $tipop . "/" . $cab['txtNRO_DOCUMENTO_EMPRESA'] . "/";
$fichero = 'GUIA_REMISION' . '-' . $cab['Serie'] . '-' . $cab['Numero'];
$nombreArchivo = 'GUIA_REMISION-' . $cab['Serie'] . '-' .  $cab['Numero'];
// $ruta2 = 'https://' . $_SERVER['HTTP_HOST'] . "/api/sunat/api_cpe/PRODUCCION" . "/" .  $cab['txtNRO_DOCUMENTO_EMPRESA']  . "/" . $nombreArchivo;
QRcode::png($nombreArchivo, $cab['txtNRO_DOCUMENTO_EMPRESA'] . ".png", 'Q', 15, 0);
$imagedata = file_get_contents($cab['txtNRO_DOCUMENTO_EMPRESA'] . ".png");
$base64 = base64_encode($imagedata);
$html = '<html>
            <head>
                <style>
                    body{
                        font:10px Arial, Tahoma, Verdana, Helvetica, sans-serif;
                        color:#000;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }

                thead,
                tbody {
                    border: 1px solid black;
                }

                table>thead>th {
                    text-align: left;
                }
                table th[colspan] {
                    text-align: left !important;
                }
                div {
                    margin: 10px 5px;
                }
                .container {
                    display: flex;
                    width: 100%;
                }
                .logo {
                    width: 30%;
                    padding: 10px;
                    box-sizing: border-box;
                }

                .datosEmpresa {
                    width: 30%;
                    padding: 10px;
                    box-sizing: border-box;
                }
                .guia {
                    width: 70%;
                    padding: 10px;
                    margin: 10px 5px;
                    box-sizing: border-box;
                }       
                .half:first-child {
                    border-right: 1px solid black;
                }

                .datosGuia {
                    align-items: center;
                    text-align: center;
                }
                
                section {
                    padding: 10px;
                    border: 1px solid #000;
                    border-radius: 5px;
                    
                }
                .headerGuia{
                    border: 0px;
                }
                .footer th, .footer td { 
                    padding: 1pt;
                }
                .footer {
                    position: fixed; 
                    bottom: 100px; 
                    font-size:10px;
                    width: 100%; 
                    border: solid 0px #000000; 
                }
        </style>
    </head>

    <body>
   
    <table class="headerGuia" width="100%">
        <tbody>
        <tr>
            <td>
                <img src="../../images/logo.png" width="130" height="40" />
            </td>
            <td>
                 <span>
                    <h2>'.$cab['txtNOMBRE_COMERCIAL_EMPRESA'].'</h2>
                </span>
                <span>'.$cab['txtNRO_DOCUMENTO_EMPRESA'].'</span>
                 <span>'.$cab['txtDIRECCION_EMPRESA'].'</span>
                 <span>'.$cab['txtTELEFONOS_EMPRESA'].'</span>
               
            </td>
            <td>
                <section class="guia datosGuia">
                    <p> GUIA DE REMISIÓN REMITENTE
                        ELECTRÓNICA
                    </p>

                    <p>
                        ' . $cab['Serie'] . '-' . $cab['Numero'] . '
                    </p>
                </section>
            </td>
        </tr>

        </tbody>
    </table>
        <div>
            <table width="100%">
                <thead>
                    <tr>
                        <th colspan="2">DESTINATARIO</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>RAZÓN SOCIAL:</td>
                        <td> ' . $cab['RazonSocialCliente'] . ' </td>
                    </tr>
                    <tr>
                        <td>RUC: </td>
                        <td>' . $cab['NroDocumentoCliente'] . ' </td>
                    </tr>
                    <tr>
                        <td>Dirección:</td>
                        <td> ' . $cab['DireccionCliente'] . '</td>
                    </tr>
                    <tr>
                        <td>Observacion:</td>
                        <td> ' . $cab['Observacion'] . '</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div>
            <table width="100%">
                <thead>
                    <tr>
                        <th colspan="2">ENVÍO</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td> Fecha Emisión: ' . $cab['FechaReg'] . ' </td>
                        <td> Fecha Inicio de Traslado: ' . $cab['FechaTraslado'] . ' </td>
                    </tr>
                    <tr>
                        <td>Motivo Traslado: ';

switch ($cab['CodMotivoTraslado']) {
    case '01':
        $html .= 'VENTA';
        break;
    case '14':
        $html .= 'VENTA SUJETA A CONFIRMACION DEL COMPRADOR';
        break;
    case '02':
        $html .= 'COMPRA';
        break;
    case '04':
        $html .= 'TRASLADO ENTRE ESTABLECIMIENTOS DE LA MISMA EMPRESA';
        break;
    case '18':
        $html .= 'TRASLADO EMISOR ITINERANTE CP';
        break;
    case '08':
        $html .= 'IMPORTACION';
        break;
    case '09':
        $html .= 'EXPORTACION';
        break;
    case '19':
        $html .= 'TRASLADO A ZONA PRIMARIA';
        break;





    default:
        $html .= 'OTROS';
        break;

        break;
};

$html .= '</td>
                        <td>Modalidad de Transporte: Transporte privado</td>
                    </tr>
                    <tr>
                        <td colspan="2">Peso Bruto Total(KGM): ' . $cab['PesoBruto'] . ' Número de Bultos: ' . $cab['TotalBultos'] . ' </td>
                    </tr>
                    <tr>
                        <td colspan="2">P.Partida: ' . $cab['CodUbigeoOrigen'] . '-' . $cab['DireccionOrigen'] . ' </td>
                    </tr>
                    <tr>
                        <td colspan="2">P.Llegada: ' . $cab['CodUbigeoDestino'] . '-' . $cab['DireccionDestino'] . '</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div>
            <table width="100%">
                <thead>
                    <tr>
                        <th colspan="2">TRANSPORTE</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Número de placa del vehículo: ' . $cab['PlacaVehiculo'] . '</td>
                        <td> Conductor: ' . $cab['NroDocChofer'] . ' </td>
                    </tr>
                    <tr>
                        <td>Licencia del conductor: ' . $cab['LicenciaChofer'] . '</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div>
            <table width="100%">
                <thead>
                    <tr>
                        <th>Item </th>
                        <th>Código </th>
                        <th>Descripción </th>
                        <th>Modelo </th>
                        <th>Unidad </th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>';
$index = 1;
foreach ($cab['Productos'] as $producto) {
    $html .= '
                         <tr>
                            <td>' . $index . '</td>
                            <td>' . $producto['CodigoBarra'] . ' </td>
                            <td>' . $producto['Producto'] . ' </td>
                            <td>' . $producto['ProductoMarca'] . ' </td>
                            <td>' . $producto['ProductoMedicion'] . ' </td>
                            <td>' . $producto['Cantidad'] . ' </td>
                        </tr>
                    
                    ';
    $index++;
}

$html .= '</tbody>
            </table>
        </div>
        <div>
            <table width="100%">
                <thead>
                    <tr>
                        <th>FACTURA ELECTRÓNICA</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                             ' . $cab['DocumentoReferencia'] . '
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
         <div class="">

         <table width="100%"  class="footer" border="0" cellspacing="0">
            <tbody>
                <tr>
                    <td width="50%" style="vertical-align:top">
                        <b>OBSERVACIONES:</b> ' . $cab['OBSERVACION'] . '
                    </td>
                    <td width="30%" rowspan="4" style="text-align:center; vertical-align:bottom">
                        <b>_________________________________ <br><br>
                        CONFORMIDAD DEL CLIENTE</b> 
                    </td>
                    <td width="20%" rowspan="4" style="text-align:center">
                       <img src="data:image/png;base64,' . $base64 . '"  width="100" height="100" />
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <b>COMPROBANTE DE PAGO</b>
                    </td>
                </tr>
                
                <tr>
                    <td style="font-size:12px;">
                        ' . $cab['DOCUMENTO_REFERENCIA'] . '
                    </td>
                </tr>
                
                <tr>
                    <td>
                        Representación impresa de la ' . $tdocumento . '
                    </td>
                </tr>
            </tbody>
        </table>
            
        </div>
    </body>

   

    </html>';

// print_r('llego');
$dompdf = new DOMPDF();
$dompdf->set_paper('A4', 'portrait');
//$dompdf->set_paper('legal','landscape');
$dompdf->load_html($html);
$dompdf->render();
//$dompdf->stream("pdf".Date('Y-m-d').".pdf");
//$dompdf->stream("ejemplo-basico.pdf", array('Attachment' => 0));
$pdf = $dompdf->output();
//file_put_contents('../'.$ruta, $pdf);
file_put_contents($ruta . $fichero . '.pdf', $pdf);
