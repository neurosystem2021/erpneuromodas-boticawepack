<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$c=appDatabaseConfig();$c['dbname']='nst';$db=new PDO(appDatabaseDsn($c),$c['user'],$c['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);$out=[];
$out['modules']=$db->query("SELECT * FROM Seg_UsuarioModulo WHERE UsuarioModulo LIKE '%concre%' OR UsuarioModulo LIKE '%wuanuko%'")->fetchAll();
$out['company_columns']=$db->query("SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND LOWER(COLUMN_NAME)='idempresa' AND TABLE_NAME NOT LIKE 'cx\_%' AND TABLE_NAME<>'gen_empresa'")->fetchAll();
foreach($out['company_columns'] as $r){$t=$r['TABLE_NAME'];$out['company_references'][$t]=$db->query('SELECT COUNT(*) FROM `'.$t.'` WHERE `'.$r['COLUMN_NAME'].'` IN (2,3)')->fetchColumn();}
$out['product_matches']=$db->query("SELECT IdProducto,Producto,CodigoBarra FROM Gen_Producto WHERE Producto LIKE '%WUANUKO%' OR CodigoBarra LIKE 'WM-%'")->fetchAll();
$out['company_schema']=$db->query('SHOW COLUMNS FROM Co_Company')->fetchAll();
$out['config_keys']=$db->query('SELECT IdEmpresa,MetaKey FROM Gen_EmpresaConfig')->fetchAll();
file_put_contents(__DIR__.'/references.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));echo json_encode($out,JSON_UNESCAPED_UNICODE).PHP_EOL;
