<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$p=appPdoConnection();$row=$p->query('SELECT RAZONSOCIAL,TELEFONO,CELULAR,EMAIL FROM GEN_EMPRESA LIMIT 1')->fetch(PDO::FETCH_ASSOC);echo json_encode($row,JSON_UNESCAPED_UNICODE);
