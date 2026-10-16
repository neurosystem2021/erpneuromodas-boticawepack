<?php
require_once 'lib/html5lib/Parser.php';
require_once 'lib/php-font-lib/src/FontLib/Autoloader.php';
require_once 'lib/php-svg-lib/src/autoload.php';
require_once 'src/Autoloader.php';
Dompdf\Autoloader::register();
require_once __DIR__.'/purchaseOrderReport.php';
$cab=json_decode(file_get_contents('php://input'),true);
if(!is_array($cab)||empty($cab['ordencompra'])){http_response_code(422);exit('Orden inválida');}
$html=purchaseOrderReportHtml($cab);
$dompdf=new Dompdf\Dompdf();$dompdf->set_paper('A4','portrait');$dompdf->load_html($html);$dompdf->render();
$ruc=preg_replace('/[^0-9]/','',(string)$cab['txtNRO_DOCUMENTO_EMPRESA']);
$year=(int)$cab['ordencompra']['Anio'];$number=(int)$cab['ordencompra']['Numero'];
$target=__DIR__.'/../../api_cpe/PRODUCCION/'.$ruc.'/OrdenCompra_'.$year.'_'.$number.'.pdf';
file_put_contents($target,$dompdf->output());
