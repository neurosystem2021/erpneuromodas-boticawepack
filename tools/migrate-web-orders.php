<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';
$db=appPdoConnection();
if(!$db->query("SHOW COLUMNS FROM Ve_Proforma LIKE 'WebCargado'")->fetch())$db->exec('ALTER TABLE Ve_Proforma ADD COLUMN WebCargado TINYINT NOT NULL DEFAULT 0');
echo "WebCargado ready\n";
