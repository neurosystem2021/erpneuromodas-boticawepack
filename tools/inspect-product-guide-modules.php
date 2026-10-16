<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';
$db=appPdoConnection();echo json_encode($db->query("SELECT UsuarioModulo FROM Seg_UsuarioModulo WHERE LOWER(UsuarioModulo) LIKE '%producto%' OR LOWER(UsuarioModulo) LIKE '%articulo%'")->fetchAll(PDO::FETCH_COLUMN),JSON_UNESCAPED_UNICODE)."\n";
