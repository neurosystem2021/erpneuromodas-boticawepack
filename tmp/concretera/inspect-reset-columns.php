<?php
if(PHP_SAPI!=='cli')exit;require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
foreach(['gen_producto','lo_almacen','ve_docventapuntoventa','gen_productocategoria','gen_productomarca','gen_productoformafarmaceutica','gen_productomedicion','cb_cuenta','ve_docventacliente'] as $table){echo $table.PHP_EOL;echo json_encode($db->query('SHOW COLUMNS FROM `'.$table.'`')->fetchAll(PDO::FETCH_ASSOC),JSON_UNESCAPED_UNICODE).PHP_EOL;}
echo json_encode($db->query('SELECT IdEmpresa,TasaImpuestoPct FROM Cx_GrupoEmpresa')->fetchAll(PDO::FETCH_ASSOC)).PHP_EOL;
echo json_encode($db->query('SELECT IdProductoMedicion,ProductoMedicion,Codigo FROM Gen_ProductoMedicion')->fetchAll(PDO::FETCH_ASSOC)).PHP_EOL;
