<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';
$db=appPdoConnection();
$source=file_get_contents(__DIR__.'/../integration/service-balance/clases/BnGeneral.php');
$start=strpos($source,'function fn_devolverProyecto(');$source=substr($source,$start,strpos($source,'function fn_devolverProductosOrdenCompra',$start)-$start);
preg_match('/(IFNULL\(Ve_PreOrdenTotal.granTotal,0\) AS TotalServicio,[\s\S]*?AS SaldoPendiente),/',$source,$match);
if(empty($match[1]))throw new Exception('Missing receipt totals');
foreach([[200,50,150],[200,0,200],[null,null,0],[100,120,0]] as $case){
 $total=$case[0]===null?'NULL':$case[0];$advance=$case[1]===null?'NULL':$case[1];
 $row=$db->query('SELECT '.$match[1].' FROM (SELECT '.$total.' AS granTotal) Ve_PreOrdenTotal CROSS JOIN (SELECT '.$advance.' AS sumAdelanto) Cb_CajaBancoAdelanto')->fetch(PDO::FETCH_ASSOC);
 if((float)$row['SaldoPendiente']!==floatval($case[2]))throw new Exception('Incorrect balance');
}
if(strpos($source,'AND IFNULL(Cb_CajaBanco.Anulado,0) = 0')===false)throw new Exception('Cancelled advance included');
echo "Receipt totals: advance, outstanding balance, empty totals and overpayment OK. No stored data changed.\n";
