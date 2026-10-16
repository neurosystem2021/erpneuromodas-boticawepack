<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();foreach($db->query('SHOW COLUMNS FROM Gen_Empresa') as $row)echo $row['Field']."\n";
