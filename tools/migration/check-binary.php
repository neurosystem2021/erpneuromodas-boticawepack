<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();$sql="SELECT TABLE_NAME,COLUMN_NAME,DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND DATA_TYPE IN ('blob','mediumblob','longblob','binary','varbinary','bit')";echo json_encode($db->query($sql)->fetchAll(PDO::FETCH_ASSOC));
