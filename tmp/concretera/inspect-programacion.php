<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();$out=[];
foreach(['Gen_Producto','Gen_ProductoMedicion','Lo_Movimiento','Lo_MovimientoDetalle','Lo_MovimientoTipo','Lo_Proveedor','Gen_Producto_Totales'] as $t)$out[$t]=$db->query('SHOW COLUMNS FROM '.$t)->fetchAll(PDO::FETCH_ASSOC);
$out['tipos']=$db->query('SELECT * FROM Lo_MovimientoTipo WHERE COALESCE(Anulado+0,0)=0')->fetchAll(PDO::FETCH_ASSOC);
$out['unidades']=$db->query('SELECT * FROM Gen_ProductoMedicion')->fetchAll(PDO::FETCH_ASSOC);
$out['relacionadas']=$db->query("SELECT TABLE_NAME,ENGINE,TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND (TABLE_NAME LIKE '%vehiculo%' OR TABLE_NAME LIKE '%flota%' OR TABLE_NAME LIKE '%conductor%' OR TABLE_NAME LIKE '%stock%' OR TABLE_NAME LIKE '%total%')")->fetchAll(PDO::FETCH_ASSOC);
file_put_contents('tmp/concretera/programacion-arquitectura.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));echo json_encode(['tipos'=>$out['tipos'],'unidades'=>$out['unidades'],'relacionadas'=>$out['relacionadas']],JSON_UNESCAPED_UNICODE);
