<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();echo json_encode($db->query("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE LOWER(SCHEMA_NAME) LIKE '%imceden%' OR LOWER(SCHEMA_NAME) LIKE '%iceden%'")->fetchAll(PDO::FETCH_COLUMN)).PHP_EOL;
