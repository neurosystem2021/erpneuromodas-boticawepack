<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';foreach(appPdoConnection()->query('SHOW COLUMNS FROM Seg_Usuario')->fetchAll(PDO::FETCH_ASSOC) as $c)if($c['Null']==='NO'&&$c['Default']===null)echo $c['Field'].PHP_EOL;
