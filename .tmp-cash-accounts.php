<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();foreach($db->query('SHOW COLUMNS FROM Cb_Cuenta') as $c)echo $c['Field']."\n";foreach($db->query('SELECT IdCuenta,Cuenta,EsTarjeta FROM Cb_Cuenta') as $r)echo $r['IdCuenta'].' '.$r['Cuenta'].' tarjeta='.$r['EsTarjeta']."\n";
