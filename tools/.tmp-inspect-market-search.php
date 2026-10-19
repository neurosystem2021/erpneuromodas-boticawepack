<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();$q=$db->query("SELECT IdProducto,Producto,ProductoModelo,CodigoBarra,ControlaStock,Anulado FROM Gen_Producto WHERE Producto LIKE '%market%' OR ProductoModelo LIKE '%market%' LIMIT 12");echo json_encode($q->fetchAll(PDO::FETCH_ASSOC),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
