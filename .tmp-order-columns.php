<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();foreach($db->query('SHOW COLUMNS FROM Lo_OrdenCompra')->fetchAll(PDO::FETCH_ASSOC) as $r)echo $r['Field'],PHP_EOL;
