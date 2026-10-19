<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
$s=$db->prepare('SELECT g.IdProducto,g.Producto,g.Imagen,g.ProductoDesc,g.ProductoDesc2,g.ProductoDesc3 FROM Gen_Producto g WHERE g.IdProducto=?');$s->execute([2447]);$p=$s->fetch(PDO::FETCH_ASSOC);echo json_encode($p,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT).PHP_EOL;
foreach($db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table)if(preg_match('/imagen|foto/i',$table))echo $table.PHP_EOL;
$s=$db->prepare('SELECT p.IdProforma,p.Presentacion,g.IdProducto,g.Producto,g.Imagen,d.Descripcion FROM Ve_Proforma p JOIN Ve_ProformaDet d ON d.IdProforma=p.IdProforma JOIN Gen_Producto g ON g.IdProducto=d.IdProducto WHERE p.IdProforma=?');$s->execute([20]);echo json_encode($s->fetchAll(PDO::FETCH_ASSOC),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT).PHP_EOL;
