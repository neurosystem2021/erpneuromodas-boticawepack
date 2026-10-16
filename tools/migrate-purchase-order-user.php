<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();$check=$db->query("SHOW COLUMNS FROM Lo_OrdenCompra LIKE 'UsuarioReg'")->fetch(PDO::FETCH_ASSOC);if(!$check)$db->exec('ALTER TABLE Lo_OrdenCompra ADD COLUMN UsuarioReg VARCHAR(255) NULL');echo 'Creator field available; existing orders unchanged',PHP_EOL;
