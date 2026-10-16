<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();foreach($db->query('SELECT CodSunat,TipoDoc FROM Ve_DocVentaTipoDoc') as $r)echo $r['CodSunat'].' '.$r['TipoDoc']."\n";
foreach($db->query('SHOW COLUMNS FROM Gen_Empresa') as $r)if(preg_match('/ruc|razon|direccion/i',$r['Field']))echo $r['Field']."\n";
