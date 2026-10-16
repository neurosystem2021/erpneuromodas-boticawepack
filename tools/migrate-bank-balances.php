<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
$db->exec("CREATE TABLE IF NOT EXISTS Cb_SaldoBanco (IdCuenta INT NOT NULL PRIMARY KEY, FechaInicio DATE NOT NULL, SaldoInicial DECIMAL(14,2) NOT NULL, Version INT NOT NULL DEFAULT 1, UsuarioReg VARCHAR(255) NOT NULL, FechaReg DATETIME NOT NULL, UsuarioMod VARCHAR(255) NULL, FechaMod DATETIME NULL, Motivo VARCHAR(500) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$db->exec("CREATE TABLE IF NOT EXISTS Cb_SaldoBancoAuditoria (IdAuditoria BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY, IdCuenta INT NOT NULL, Version INT NOT NULL, DatosAntes LONGTEXT NULL, DatosDespues LONGTEXT NOT NULL, Usuario VARCHAR(255) NOT NULL, Motivo VARCHAR(500) NOT NULL, Fecha DATETIME NOT NULL, INDEX CuentaVersion(IdCuenta,Version)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo 'Bank balance tables ready; no bank balances or movements changed.'.PHP_EOL;
