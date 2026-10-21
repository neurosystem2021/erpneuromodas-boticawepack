<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$c=appDatabaseConfig();$all=[];
foreach(['nst','fact_nst','fact_wuanukomix'] as $name){try{$c['dbname']=$name;$db=new PDO(appDatabaseDsn($c),$c['user'],$c['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);$out=['database'=>$name,'objects'=>$db->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM)];
foreach(['Gen_Empresa','Gen_Producto','Ve_DocVenta'] as $t){try{$out['counts'][$t]=$db->query('SELECT COUNT(*) FROM `'.$t.'`')->fetchColumn();if($t==='Gen_Empresa')$out['companies']=$db->query('SELECT IDEMPRESA,RAZONSOCIAL,RUC FROM `'.$t.'`')->fetchAll();}catch(Exception $e){$out['counts'][$t]=['error'=>$e->getCode()];}}
$out['foreign_keys']=$db->query("SELECT TABLE_NAME,COLUMN_NAME,REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL")->fetchAll();
$all[]=$out;echo json_encode(['database'=>$name,'objects'=>count($out['objects']),'counts'=>$out['counts'],'companies'=>isset($out['companies'])?$out['companies']:[]]).PHP_EOL;
}catch(Exception $e){echo json_encode(['database'=>$name,'error'=>$e->getCode()]).PHP_EOL;}}
file_put_contents(__DIR__.'/schemas.json',json_encode($all,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
