<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
foreach(['Ve_DocVenta'=>'idDocVenta INT,IdPreOrden INT,Anulado INT','Ve_DocVentaDet'=>'IdDocVenta INT,IdProducto INT','Ve_PreOrden'=>'IdPreOrden INT,IdDocVenta INT','Ve_PreOrdenDet'=>'IdPreOrden INT,IdProducto INT'] as $table=>$columns)$db->exec('CREATE TEMPORARY TABLE '.$table.'('.$columns.')');
$db->exec('INSERT INTO Ve_PreOrdenDet VALUES(1,2340)');$db->exec('INSERT INTO Ve_PreOrden VALUES(1,NULL)');
$s=file_get_contents(__DIR__.'/../integration/project-invoice/api/index.php');$a=strpos($s,"\$app->get('/preorden/detalle'");$s=substr($s,$a);preg_match('/(EXISTS\(SELECT 1 FROM Ve_DocVenta documento[\s\S]*?AS Facturado),/',$s,$match);if(empty($match[1]))throw new Exception('Missing invoice status');
$check=function($expected)use($db,$match){$actual=$db->query('SELECT '.$match[1].' FROM Ve_PreOrdenDet')->fetchColumn();if((int)$actual!==$expected)throw new Exception('Incorrect invoicing status');};
$check(0);$db->exec('INSERT INTO Ve_DocVenta VALUES(10,1,0)');$db->exec('INSERT INTO Ve_DocVentaDet VALUES(10,999)');$check(0);$db->exec('INSERT INTO Ve_DocVentaDet VALUES(10,2340)');$check(1);$db->exec('UPDATE Ve_DocVenta SET Anulado=1');$check(0);
echo "Invoice status: no invoice, other product, actual invoice, cancelled invoice OK. Temporary tables only.\n";
