<?php
if(PHP_SAPI!=='cli')exit;
require 'C:/xampp/htdocs/boticav1/config/database.php';
$db=appPdoConnection();if(appDatabaseConfig()['dbname']!=='fact_wuanukomix')throw new RuntimeException('Base inesperada');
$queries=[
'referencias'=>"SELECT Entidad,COUNT(*) Cantidad FROM Cx_ReferenciaERP WHERE IdEmpresa=2 GROUP BY Entidad",
'obras'=>"SELECT COUNT(*) FROM Cx_Obra WHERE IdEmpresa=2",
'especificaciones'=>"SELECT COUNT(*) FROM Cx_Especificacion WHERE IdEmpresa=2",
'productos_concreto'=>"SELECT IdProducto,Producto FROM Gen_Producto WHERE COALESCE(Anulado+0,0)=0 AND (Producto LIKE '%CONCRETO%' OR Producto LIKE '%BOMBEO%') LIMIT 20",
'almacenes'=>"SELECT IdAlmacen,Almacen FROM Lo_Almacen WHERE COALESCE(Anulado+0,0)=0 LIMIT 20"];
foreach($queries as $name=>$sql){try{echo json_encode([$name=>$db->query($sql)->fetchAll(PDO::FETCH_ASSOC)],JSON_UNESCAPED_UNICODE).PHP_EOL;}catch(Throwable $e){echo json_encode([$name=>'Consulta no disponible']).PHP_EOL;}}
