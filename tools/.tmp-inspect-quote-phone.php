<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();foreach(['Ve_DocVentaCliente','Ve_DocVenta','Ve_Proforma'] as $table){echo $table.PHP_EOL;foreach($db->query('SHOW COLUMNS FROM '.$table) as $row)if(in_array(strtolower($row['Field']),['telefono','iddocventa','idproforma','anulado','esfavoritoflag','presentacion']))echo $row['Field'].' '.$row['Type'].PHP_EOL;}
