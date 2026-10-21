<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';
$c=appDatabaseConfig();
echo json_encode(['active_database'=>$c['dbname'],'port'=>$c['port']]).PHP_EOL;
foreach(['fact_imceden','fact_wuanukomix'] as $name){
$c['dbname']=$name;
try{$db=new PDO(appDatabaseDsn($c),$c['user'],$c['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$tables=$db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);$out=['database'=>$name,'tables'=>count($tables),'counts'=>[]];
foreach(['Gen_Empresa','Gen_Producto','Gen_Producto_Totales','Lo_MovimientoTipo','Lo_Almacen','Ve_DocVentaTipoDoc','Ve_DocVenta','Gen_ProductoPrecio','Ve_DocVentaPuntoVentaDet'] as $t){
$actual=null;foreach($tables as $v)if(strcasecmp($v,$t)===0)$actual=$v;
if(!$actual){$out['counts'][$t]='absent';continue;}
$out['counts'][$actual]=$db->query('SELECT COUNT(*) FROM `'.$actual.'`')->fetchColumn();
if(in_array($t,['Gen_Empresa','Lo_MovimientoTipo','Ve_DocVentaTipoDoc','Gen_Producto_Totales']))$out['columns'][$actual]=$db->query('SHOW COLUMNS FROM `'.$actual.'`')->fetchAll();
}
echo json_encode($out,JSON_UNESCAPED_UNICODE).PHP_EOL;
}catch(Exception $e){echo json_encode(['database'=>$name,'error_code'=>$e->getCode()]).PHP_EOL;}
}
