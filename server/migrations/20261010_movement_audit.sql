CREATE TABLE IF NOT EXISTS Lo_MovimientoAuditoria (
    IdAuditoria BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    HashMovimiento VARCHAR(255) NOT NULL,
    Accion VARCHAR(20) NOT NULL,
    Usuario VARCHAR(255) NOT NULL,
    Fecha DATETIME NOT NULL,
    Motivo TEXT NOT NULL,
    Proveedor VARCHAR(255) NULL,
    DocumentoAnterior VARCHAR(700) NOT NULL,
    DocumentoNuevo VARCHAR(700) NOT NULL,
    Antes LONGTEXT NOT NULL,
    Despues LONGTEXT NOT NULL,
    PRIMARY KEY (IdAuditoria),
    KEY ix_movimiento (HashMovimiento),
    KEY ix_fecha (Fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
