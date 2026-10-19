<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();$r=$db->query('SELECT RAZONSOCIAL FROM GEN_EMPRESA ORDER BY IDEMPRESA LIMIT 1')->fetch(PDO::FETCH_ASSOC);echo json_encode($r,JSON_UNESCAPED_UNICODE).PHP_EOL;
