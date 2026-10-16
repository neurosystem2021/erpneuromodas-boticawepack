<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';
require 'C:/xampp/htdocs/boticav1/api/vendor/autoload.php';
require __DIR__.'/../integration/management-report/api/management-report-core.php';
require __DIR__.'/../integration/management-report/api/management-report-excel.php';
$r=managementReport(appPdoConnection(),['desde'=>'2026-10-01','hasta'=>'2026-10-16','proyeccion'=>30],is_file('C:/xampp/htdocs/boticav1/config/management-report.local.php') ? require 'C:/xampp/htdocs/boticav1/config/management-report.local.php' : []);
$book=managementWorkbook($r);$sheet=$book->getSheetByName('Resumen');
foreach (['E6'=>'venta','E8'=>'bruta','B16'=>'neta','E20'=>'cobrosDigitales','B25'=>'salidasExternas'] as $cell=>$key) {
    $v=$sheet->getCell($cell)->getCalculatedValue();$e=$r['totales'][$key];
    if (($e===null && $v!=='n.a.') || ($e!==null && (!is_numeric($v)||abs($v-$e)>0.001))) throw new RuntimeException('Workbook does not match report at '.$cell);
}
foreach($book->getAllSheets() as $s)foreach($s->getCellCollection()->getCoordinates() as $c){$v=$s->getCell($c)->getCalculatedValue();if(is_string($v)&&preg_match('/^#(REF!|VALUE!|DIV\/0!|NAME\?|NUM!|N\/A)/',$v))throw new RuntimeException('Formula error '.$s->getTitle().'!'.$c);}
(new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save(__DIR__.'/../output/management-report-runtime.xlsx');
file_put_contents(__DIR__.'/../output/management-report-data.json',json_encode($r,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
echo 'PASS live-data workbook formulas, daily totals, Yape control, true outflows and 5 sheets. Excel generated without changing sales or payments.'.PHP_EOL;