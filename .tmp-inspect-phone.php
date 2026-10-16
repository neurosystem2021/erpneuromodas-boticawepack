<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
foreach($db->query('SHOW COLUMNS FROM Gen_Empresa') as $r)if(preg_match('/cel|tel|wha/i',$r['Field']))echo $r['Field']."\n";
foreach($db->query("SELECT MetaKey,MetaValue FROM Gen_EmpresaConfig WHERE MetaKey REGEXP 'whats|telefono|celular'") as $r)echo $r['MetaKey'].'='.$r['MetaValue']."\n";
$r=$db->query('SELECT * FROM Gen_Empresa ORDER BY IDEMPRESA LIMIT 1')->fetch();foreach($r as $k=>$v)if(preg_match('/cel|tel|wha/i',$k))echo "$k=$v\n";
