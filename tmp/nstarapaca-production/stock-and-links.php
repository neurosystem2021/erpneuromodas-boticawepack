<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();$out=[];
$out['cash_links']=$db->query("SELECT c.IdCajaBanco,c.IdPreOrden,COUNT(d.IdCajaBanco) AS Detalles,GROUP_CONCAT(DISTINCT d.Tipo) AS Tipos FROM Cb_CajaBanco c LEFT JOIN Cb_CajaBancoDet d ON d.IdCajaBanco=c.IdCajaBanco GROUP BY c.IdCajaBanco,c.IdPreOrden")->fetchAll();
$out['stock']=$db->query('SELECT COUNT(*) AS Filas,SUM(TotalCompras-TotalVentas<>0) AS ConExistencia,SUM(TotalCompras-TotalVentas<0) AS Negativos FROM Gen_Producto_Totales')->fetch();
$out['serial_states']=$db->query("SELECT Estado,COUNT(*) AS Filas,SUM(COALESCE(IdDocVenta,0)<>0) AS VentaRelacionada,SUM(COALESCE(IdMovimiento,'')<>'') AS CompraRelacionada FROM Gen_Producto_Serie GROUP BY Estado")->fetchAll();
$out['product_order_links']=$db->query('SELECT COUNT(*) FROM Gen_Producto WHERE COALESCE(IdPreOrden,0)<>0')->fetchColumn();
echo json_encode($out).PHP_EOL;file_put_contents(__DIR__.'/stock-and-links.json',json_encode($out,JSON_PRETTY_PRINT));
