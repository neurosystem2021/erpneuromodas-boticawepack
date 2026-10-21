<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$c=appDatabaseConfig();$c['dbname']='nst';$db=new PDO(appDatabaseDsn($c),$c['user'],$c['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);$out=[];
$tables=$db->query("SELECT TABLE_NAME,TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME LIKE 'cx\_%'")->fetchAll();
foreach($tables as $r){$t=$r['TABLE_NAME'];$out['concretera'][$t]=['rows'=>$db->query('SELECT COUNT(*) FROM `'.$t.'`')->fetchColumn(),'ddl'=>$db->query('SHOW CREATE TABLE `'.$t.'`')->fetch(PDO::FETCH_NUM)[1]];}
$out['config_columns']=$db->query('SHOW COLUMNS FROM Gen_EmpresaConfig')->fetchAll();
$out['concrete_config']=$db->query("SELECT IdEmpresa,MetaKey FROM Gen_EmpresaConfig WHERE IdEmpresa IN (2,3) OR MetaKey LIKE '%concre%' OR MetaKey LIKE '%wuanuko%'")->fetchAll();
$out['module_columns']=$db->query('SHOW COLUMNS FROM Seg_UsuarioModulo')->fetchAll();
$out['company_fks']=$db->query("SELECT TABLE_NAME,COLUMN_NAME,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL")->fetchAll();
$out['triggers']=$db->query('SHOW TRIGGERS')->fetchAll();
file_put_contents(__DIR__.'/concrete-details.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));echo json_encode(['tables'=>array_map(function($t){return $t['rows'];},$out['concretera']),'config'=>$out['concrete_config'],'module_columns'=>array_column($out['module_columns'],'Field'),'fks'=>$out['company_fks'],'triggers'=>count($out['triggers'])],JSON_UNESCAPED_UNICODE).PHP_EOL;
