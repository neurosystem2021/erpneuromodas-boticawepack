<?php require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();echo json_encode($db->query('SELECT * FROM Ve_TipoDoc')->fetchAll(PDO::FETCH_ASSOC));
