-- Additive migration. No changes to sales, fiscal tables, sequences or certificates.
CREATE TABLE IF NOT EXISTS Ali_PosOperacion (
 Operacion CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY,
 Usuario VARCHAR(100) NOT NULL,
 IdEmpresa INT NOT NULL DEFAULT 1,
 IdAlmacen INT NOT NULL,
 PayloadHash CHAR(64) CHARACTER SET ascii NOT NULL,
 Payload LONGTEXT NOT NULL,
 Resumen LONGTEXT NOT NULL,
 Estado VARCHAR(20) NOT NULL,
 IdDocVenta INT NULL,
 Resultado TEXT NULL,
 CreadoEn DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 Expira DATETIME NOT NULL,
 ConfirmadoPor VARCHAR(100) NULL,
 ConfirmadoEn DATETIME NULL,
 INDEX Ali_PosOperacion_UsuarioEstado (Usuario,Estado,CreadoEn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
