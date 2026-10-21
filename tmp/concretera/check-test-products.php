<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
echo json_encode($db->query("SHOW COLUMNS FROM Gen_Producto WHERE Field IN ('ControlaStock','Anulado','Producto','PrecioContado')")->fetchAll(),JSON_UNESCAPED_UNICODE).PHP_EOL;
echo json_encode($db->query("SELECT IdProducto,Producto,CodigoBarra,ControlaStock+0 ControlaStock,PrecioContado FROM Gen_Producto WHERE CodigoBarra LIKE 'WM-%'")->fetchAll(),JSON_UNESCAPED_UNICODE).PHP_EOL;
