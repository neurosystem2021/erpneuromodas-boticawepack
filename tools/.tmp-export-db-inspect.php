<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();echo 'ERP database: '.appDatabaseConfig()['dbname'].PHP_EOL;foreach($db->query('SELECT ENGINE,COUNT(*) n FROM information_schema.tables WHERE table_schema=DATABASE() GROUP BY ENGINE') as $row)echo $row['ENGINE'].': '.$row['n'].PHP_EOL;
if(is_file('C:/xampp/support-private/runtime.php')){$cfg=require 'C:/xampp/support-private/runtime.php';echo 'Support database: '.($cfg['database']??'external').PHP_EOL;}
