<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
echo 'Database engine: '.$db->query('SELECT VERSION()')->fetchColumn().PHP_EOL;
$tables=$db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);foreach($tables as $table){if(!preg_match('/empresa|sucursal|cliente|usuario|almacen|support|soporte/i',$table))continue;echo '\nTABLE '.$table.PHP_EOL;foreach($db->query('SHOW COLUMNS FROM `'.str_replace('`','``',$table).'`')->fetchAll(PDO::FETCH_ASSOC) as $col)echo $col['Field'].' '.$col['Type'].' '.$col['Key'].PHP_EOL;if(preg_match('/empresa|sucursal|usuario$/i',$table))echo 'rows='.$db->query('SELECT COUNT(*) FROM `'.$table.'`')->fetchColumn().PHP_EOL;}
