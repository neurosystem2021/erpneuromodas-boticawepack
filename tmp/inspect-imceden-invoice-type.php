<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
foreach(['Ve_DocVentaTipoDoc','Ve_DocVentaPuntoVentaDet'] as $t){echo $t.PHP_EOL;echo json_encode($db->query('SHOW COLUMNS FROM '.$t)->fetchAll(PDO::FETCH_ASSOC),JSON_UNESCAPED_UNICODE).PHP_EOL;echo json_encode($db->query('SELECT * FROM '.$t)->fetchAll(PDO::FETCH_ASSOC),JSON_UNESCAPED_UNICODE).PHP_EOL;}
echo 'Sales grouped '.json_encode($db->query('SELECT IdTipoDoc,Serie,COUNT(*) AS cantidad,MAX(Numero) AS ultimo FROM Ve_DocVenta GROUP BY IdTipoDoc,Serie')->fetchAll(PDO::FETCH_ASSOC),JSON_UNESCAPED_UNICODE).PHP_EOL;
