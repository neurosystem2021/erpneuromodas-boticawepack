<?php require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();echo json_encode($db->query('SHOW COLUMNS FROM Seg_UsuarioModulo')->fetchAll(PDO::FETCH_COLUMN));
