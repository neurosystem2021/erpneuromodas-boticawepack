<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();foreach(['Seg_Usuario','Rhu_Empleado'] as $t){echo $t,"\n";foreach($db->query('DESCRIBE '.$t)->fetchAll(PDO::FETCH_ASSOC) as $r)echo $r['Field'],' ',$r['Type'],' default=',(string)$r['Default'],"\n";}
