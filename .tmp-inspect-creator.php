<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
$q=$db->query("SELECT p.IdProducto,p.Producto,o.IdPreOrden,o.UsuarioReg FROM Gen_Producto p JOIN Ve_PreOrden o ON o.IdPreOrden=p.IdPreOrden WHERE p.IdProducto=4681");echo json_encode($q->fetch(PDO::FETCH_ASSOC)),"\n";
$q=$db->query("SELECT Usuario,NombreUsuario FROM Seg_Usuario WHERE Usuario='superadmin'");echo json_encode($q->fetch(PDO::FETCH_ASSOC)),"\n";
