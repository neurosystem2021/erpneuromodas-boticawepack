<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();$out=[];
foreach(['Gen_Producto_Serie','Ali_PosOperacion','Ali_ProductoUso','Ve_ExpertoDiagnostico','Ve_DocVentaCliente','Ve_DocVentaPuntoVentaDet','Cb_Cuenta'] as $t)$out['columns'][$t]=$db->query('SHOW COLUMNS FROM `'.$t.'`')->fetchAll();
$out['cash_detail_types']=$db->query('SELECT Tipo,COUNT(*) AS Filas FROM Cb_CajaBancoDet GROUP BY Tipo')->fetchAll();
$out['cash_references']=$db->query('SELECT IdCajaBanco,IdPreOrden,IdCierre FROM Cb_CajaBanco')->fetchAll();
$out['stock_view']=$db->query('SHOW CREATE VIEW Gen_Producto_Totales')->fetch(PDO::FETCH_NUM)[1];
$out['columns_references']=$db->query("SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND (LOWER(COLUMN_NAME) LIKE '%idproforma%' OR LOWER(COLUMN_NAME) LIKE '%iddocventa%' OR LOWER(COLUMN_NAME) LIKE '%idcierre%' OR LOWER(COLUMN_NAME) LIKE '%hashmovimiento%' OR LOWER(COLUMN_NAME) LIKE '%idpreorden%' OR LOWER(COLUMN_NAME) LIKE '%idordencompra%')")->fetchAll();
file_put_contents(__DIR__.'/relations.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));echo json_encode($out,JSON_UNESCAPED_UNICODE).PHP_EOL;
