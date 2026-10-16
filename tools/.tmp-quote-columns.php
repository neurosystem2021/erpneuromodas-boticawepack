<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();foreach(['Ve_ProformaDet','Gen_Producto'] as $table){echo $table.': ';foreach($db->query('SHOW COLUMNS FROM '.$table) as $row)echo $row['Field'].' ';echo PHP_EOL;}
