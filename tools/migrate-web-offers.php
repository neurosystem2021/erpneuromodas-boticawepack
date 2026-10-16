<?php
$db=new PDO('mysql:host=127.0.0.1;dbname=nst;charset=utf8','root','',array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
if(!$db->query("SHOW COLUMNS FROM Gen_Producto LIKE 'OfertaWeb'")->fetch())$db->exec('ALTER TABLE Gen_Producto ADD COLUMN OfertaWeb TINYINT NOT NULL DEFAULT 0');
echo "OfertaWeb ready\n";
