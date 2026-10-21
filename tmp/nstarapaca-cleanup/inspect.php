<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$c=appDatabaseConfig();
echo json_encode(['active_database'=>$c['dbname'],'port'=>$c['port']]).PHP_EOL;
foreach([3306,3307] as $port){
try{$c['port']=$port;$c['dbname']='information_schema';$db=new PDO(appDatabaseDsn($c),$c['user'],$c['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_TIMEOUT=>2]);
$names=$db->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);echo json_encode(['port'=>$port,'databases'=>array_values(array_filter($names,function($n){return preg_match('/nst|tarapaca|wuanuko|imceden|neuro/i',$n);} ))]).PHP_EOL;
foreach($names as $name){if(!preg_match('/^(fact_nst|fact_nstarapaca|nstarapaca|nst|fact_wuanukomix|fact_imceden)$/i',$name))continue;
$db->exec('USE `'.$name.'`');$tables=$db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);$out=['port'=>$port,'database'=>$name,'tables'=>$tables];
foreach(['Gen_Empresa','Gen_Producto','Ve_DocVenta'] as $t){foreach($tables as $actual){if(strcasecmp($actual,$t)!==0)continue;
$out['counts'][$actual]=$db->query('SELECT COUNT(*) FROM `'.$actual.'`')->fetchColumn();
if($t==='Gen_Empresa')$out['companies']=$db->query('SELECT IDEMPRESA,RAZONSOCIAL,RUC FROM `'.$actual.'`')->fetchAll();}}
echo json_encode($out,JSON_UNESCAPED_UNICODE).PHP_EOL;
}
}catch(Exception $e){echo json_encode(['port'=>$port,'error_code'=>$e->getCode()]).PHP_EOL;}
}
