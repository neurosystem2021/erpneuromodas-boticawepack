<?php
if(($argv[1]??'')!=='--install')throw new RuntimeException('Use --install');
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);$db->exec(file_get_contents(__DIR__.'/../integration/product-knowledge.sql'));$column=$db->query("SHOW COLUMNS FROM Ali_ProductoFicha LIKE 'used_for'")->fetch();if(!$column)$db->exec("ALTER TABLE Ali_ProductoFicha ADD COLUMN used_for TEXT NOT NULL");echo "Persistent product knowledge tables installed. No catalog or sales rows changed.\n";
