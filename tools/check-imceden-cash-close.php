<?php
if(PHP_SAPI!=='cli')exit(1);
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
$rows=$db->query("SELECT TABLE_NAME,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('Cb_CierreCaja','Cb_CajaBanco','Ve_DocVenta') ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_ASSOC);
if(count($rows)!==3)throw new RuntimeException('Missing closing tables');
foreach($rows as $row)if(strcasecmp($row['TABLE_NAME'],'Cb_CierreCaja')!==0&&strcasecmp($row['ENGINE'],'InnoDB')!==0)throw new RuntimeException('Sales and movements must support rollback: '.$row['TABLE_NAME']);
$counts=$db->query("SELECT SUM(IdCierre IS NULL) AS pending, SUM(IdCierre IS NULL AND DATE(COALESCE(FechaReg,FechaDoc))<CURDATE()) AS earlier_pending, SUM(IdCierre IS NULL AND COALESCE(EsDelVendedor,0)<>1) AS manual_pending FROM Cb_CajaBanco")->fetch(PDO::FETCH_ASSOC);
echo json_encode(['tables'=>$rows,'pending_movement_counts'=>$counts,'mode'=>'read-only'],JSON_UNESCAPED_UNICODE).PHP_EOL;
