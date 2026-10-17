<?php
if(PHP_SAPI!=='cli')exit;require 'C:/xampp/htdocs/boticav1/config/database.php';$f=json_decode(file_get_contents(__DIR__.'/.tmp-module-access-fixture.json'),true);if(!preg_match('/^test_access_[a-f0-9]{12}$/',$f['user']))exit(1);$q=appPdoConnection()->prepare('DELETE FROM Rhu_DiarioPersonal WHERE Usuario=?');$q->execute([$f['user']]);
