CREATE TABLE IF NOT EXISTS Tienda_ClienteCuenta (
    IdCuenta INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    IdCliente INT NOT NULL,
    Documento VARCHAR(20) NOT NULL,
    TipoDocumento VARCHAR(12) NOT NULL DEFAULT 'DNI',
    PasswordHash VARCHAR(255) NOT NULL,
    FechaReg DATETIME NOT NULL,
    UltimoAcceso DATETIME NULL,
    UNIQUE KEY uq_tienda_documento (Documento),
    KEY ix_tienda_cliente (IdCliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
