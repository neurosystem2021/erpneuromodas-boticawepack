<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
foreach(['Ve_ProformaDet','Ve_Proforma','Ve_DocVentaDet'] as $table){$rows=$db->query('SHOW COLUMNS FROM '.$table)->fetchAll(PDO::FETCH_ASSOC);echo $table.': '.json_encode(array_map(fn($r)=>[$r['Field'],$r['Type'],$r['Null'],$r['Default']],$rows),JSON_UNESCAPED_UNICODE).PHP_EOL;}
