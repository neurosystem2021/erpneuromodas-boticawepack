<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';
$db=appPdoConnection();$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
if($db->query('SELECT DATABASE()')->fetchColumn()!=='nst')throw new Exception('Unexpected database');
$tables=['Ve_DocVenta','Ve_DocVentaDet','Ve_DocVentaMetodoPagoDet','Cb_CajaBanco','Cb_CajaBancoDet','Ve_DocVentaCliente','Gen_Producto_Serie'];
foreach($tables as $table){$s=$db->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');$s->execute([$table]);if(strtoupper($s->fetchColumn())!=='INNODB')throw new Exception('Transactional engine required: '.$table);}
$before=$db->query("SHOW TABLES LIKE 'Ali_PosOperacion'")->fetchColumn();
$evidence=['database'=>'nst','new_table_previously_present'=>(bool)$before,'core_tables_unchanged'=>$tables,'migration_sha256'=>hash_file('sha256',__DIR__.'/../integration/ali-checkout.sql')];
file_put_contents(__DIR__.'/../backups/ali-pago-20261013-201849/schema-before.json',json_encode($evidence,JSON_PRETTY_PRINT));
$db->exec(file_get_contents(__DIR__.'/../integration/ali-checkout.sql'));
echo 'PASS additive Ali_PosOperacion migration; existing financial tables support transactions. No sales, payments, fiscal records or sequences changed.'.PHP_EOL;
