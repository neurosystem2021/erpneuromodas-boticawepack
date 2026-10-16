<?php require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();echo json_encode($db->query('DESCRIBE Cb_TipoCajaBanco')->fetchAll(),JSON_UNESCAPED_UNICODE);
