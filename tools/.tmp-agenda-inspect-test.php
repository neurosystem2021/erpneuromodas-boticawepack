<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$f=json_decode(file_get_contents(__DIR__.'/.tmp-module-access-fixture.json'),true);$q=appPdoConnection()->prepare('SELECT Id,Fecha,Recordar,Estado,Anticipacion,Frecuencia FROM Rhu_DiarioPersonal WHERE Usuario=?');$q->execute([$f['user']]);echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
