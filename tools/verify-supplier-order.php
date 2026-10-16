<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';
require 'C:/xampp/htdocs/boticav1/api/vendor/autoload.php';
require 'C:/xampp/htdocs/boticav1/api/purchase-order-report.php';
$db=appPdoConnection();
if(in_array('--repair',$argv,true)){
 $db->beginTransaction();$q=$db->query('SELECT * FROM Lo_OrdenCompra WHERE IdOrdenCompra=29 FOR UPDATE');$before=$q->fetch(PDO::FETCH_ASSOC);
 if($before['Anio']!=2026 || $before['Numero']!=5)throw new Exception('Wrong order');
 if(empty($before['UsuarioReg'])){file_put_contents(__DIR__.'/../.tmp-order29-before.json',json_encode($before));$q=$db->prepare('UPDATE Lo_OrdenCompra SET UsuarioReg=? WHERE IdOrdenCompra=29 AND UsuarioReg IS NULL');$q->execute(['superadmin']);}
 elseif($before['UsuarioReg']!=='superadmin')throw new Exception('Unexpected creator');$db->commit();
}
$data=nstSupplierOrderData($db,29);if($data['ordencompra']['UsuarioReg']!=='superadmin')throw new Exception('Creator');
$out=__DIR__.'/../output/pdf';if(!is_dir($out))mkdir($out,0777,true);
(new \PhpOffice\PhpSpreadsheet\Writer\Xlsx(nstSupplierOrderWorkbook($data)))->save($out.'/OrdenCompra_2026_5.xlsx');
require_once 'C:/xampp/htdocs/boticav1/api/sunat/plugins/dompdf/lib/html5lib/Parser.php';require_once 'C:/xampp/htdocs/boticav1/api/sunat/plugins/dompdf/lib/php-font-lib/src/FontLib/Autoloader.php';require_once 'C:/xampp/htdocs/boticav1/api/sunat/plugins/dompdf/lib/php-svg-lib/src/autoload.php';require_once 'C:/xampp/htdocs/boticav1/api/sunat/plugins/dompdf/src/Autoloader.php';\Dompdf\Autoloader::register();
require __DIR__.'/../integration/order-supplier/api/sunat/plugins/dompdf/purchaseOrderReport.php';
$html=purchaseOrderReportHtml($data);foreach(['P. unitario','IMPORTE TOTAL','S/ '] as $bad)if(strpos($html,$bad)!==false)throw new Exception('Price leak');
$pdf=new \Dompdf\Dompdf();$pdf->set_paper('A4','portrait');$pdf->load_html($html);$pdf->render();file_put_contents($out.'/OrdenCompra_2026_5.pdf',$pdf->output());echo "PDF and Excel generated without prices. Creator superadmin.\n";
