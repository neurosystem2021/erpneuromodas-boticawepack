<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';
$db=appPdoConnection();$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$db->exec('SET TRANSACTION READ ONLY');$db->beginTransaction();
try {
$name=$db->query('SELECT DATABASE()')->fetchColumn();
$tables=$db->query('SELECT TABLE_NAME,TABLE_TYPE,ENGINE,TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME')->fetchAll(PDO::FETCH_ASSOC);
$out=['database'=>$name,'tables'=>[]];
foreach($tables as $t){$n=$t['TABLE_NAME'];$q='`'.str_replace('`','``',$n).'`';try{$t['columns']=$db->query('SHOW FULL COLUMNS FROM '.$q)->fetchAll(PDO::FETCH_ASSOC);$t['indexes']=$db->query('SHOW INDEX FROM '.$q)->fetchAll(PDO::FETCH_ASSOC);$t['ddl']=$db->query('SHOW CREATE TABLE '.$q)->fetch(PDO::FETCH_NUM)[1];
if(preg_match('/^(Ve_|Lo_|Gen_Producto|prodstock)/i',$n))$t['rows']=(int)$db->query('SELECT COUNT(*) FROM '.$q)->fetchColumn();}catch(Throwable $e){$t['inspection_error']=$e->getCode().': '.$e->errorInfo[1];}$out['tables'][$n]=$t;}
$out['relations']=$db->query('SELECT TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL')->fetchAll(PDO::FETCH_ASSOC);
file_put_contents(__DIR__.'/../output/migration-target-schema.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode(['database'=>$name,'tables'=>count($tables),'foreign_keys'=>count($out['relations']),'read_only'=>true]),PHP_EOL;
}finally{$db->rollBack();}
