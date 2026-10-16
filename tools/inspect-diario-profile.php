<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();foreach(['Seg_UsuarioPerfil','Seg_UsuarioModulo','Seg_UsuarioModulo_has_UsuarioPerfil'] as $t){echo $t,"\n";foreach($db->query('DESCRIBE '.$t)->fetchAll(PDO::FETCH_ASSOC) as $r)echo $r['Field'],' ',$r['Type'],"\n";}
