<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();$db->exec('SET TRANSACTION READ ONLY');$db->beginTransaction();
$queries=[
'payment_orphans'=>'SELECT COUNT(*) FROM Ve_DocVentaMetodoPagoDet d LEFT JOIN Ve_DocVenta v ON v.idDocVenta=d.IdDocVenta WHERE v.idDocVenta IS NULL',
'detail_orphans'=>'SELECT COUNT(*) FROM Ve_DocVentaDet d LEFT JOIN Ve_DocVenta v ON v.idDocVenta=d.IdDocVenta WHERE v.idDocVenta IS NULL',
'credit_note_orphans'=>'SELECT COUNT(*) FROM Ve_DocVentaNotaCredito d LEFT JOIN Ve_DocVenta v ON v.idDocVenta=d.IdDocVenta WHERE v.idDocVenta IS NULL',
'duplicate_documents'=>'SELECT COUNT(*) FROM (SELECT IdTipoDoc,IdDocVentaPuntoVenta,Serie,Numero FROM Ve_DocVenta GROUP BY IdTipoDoc,IdDocVentaPuntoVenta,Serie,Numero HAVING COUNT(*)>1) x',
'duplicate_barcodes'=>'SELECT COUNT(*) FROM (SELECT CodigoBarra FROM Gen_Producto WHERE TRIM(COALESCE(CodigoBarra,\'\'))<>\'\' GROUP BY CodigoBarra HAVING COUNT(*)>1) x',
'sale_client_orphans'=>'SELECT COUNT(*) FROM Ve_DocVenta d LEFT JOIN Ve_DocVentaCliente c ON c.IdCliente=d.IdCliente WHERE c.IdCliente IS NULL',
'detail_product_orphans'=>'SELECT COUNT(*) FROM Ve_DocVentaDet d LEFT JOIN Gen_Producto p ON p.IdProducto=d.IdProducto WHERE p.IdProducto IS NULL'];
$out=[];try{foreach($queries as $n=>$q){try{$out[$n]=(int)$db->query($q)->fetchColumn();}catch(Throwable $e){$out[$n]=['unavailable'=>true,'code'=>$e->getCode()];}}file_put_contents(__DIR__.'/../output/migration-target-validation.json',json_encode($out,JSON_PRETTY_PRINT));echo json_encode($out),PHP_EOL;}finally{$db->rollBack();}
