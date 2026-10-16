<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();foreach(array('Cb_CajaBancoDet','Cb_CajaBanco') as $name){echo $name."\n";foreach($db->query('SHOW COLUMNS FROM '.$name) as $c)echo $c['Field'].' '.$c['Type']."\n";}
