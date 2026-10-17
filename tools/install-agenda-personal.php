<?php
if(PHP_SAPI!=='cli')exit(1);
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();if($db->query('SELECT DATABASE()')->fetchColumn()!=='nst')throw new RuntimeException('Wrong target');
$dir='C:/xampp/accounting-private/agenda-personal-20261016';$manifest=json_decode(file_get_contents($dir.'/manifest.json'),true);if(!hash_equals($manifest['erp']['sha256'],hash_file('sha256',$dir.'/erp.sql')))throw new RuntimeException('Backup checksum mismatch');
$q=$db->query("SELECT COUNT(*) FROM information_schema.columns WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Rhu_DiarioPersonal' AND COLUMN_NAME='Recordar'");if(!$q->fetchColumn())$db->exec('ALTER TABLE Rhu_DiarioPersonal ADD COLUMN Recordar TINYINT(1) NOT NULL DEFAULT 1');echo 'Private agenda reminder option ready; existing notes preserved.'.PHP_EOL;
