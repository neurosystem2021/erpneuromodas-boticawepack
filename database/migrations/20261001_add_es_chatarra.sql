-- Habilita la clasificación de productos que pueden comprarse como chatarra.
SET @es_chatarra_existe = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'Gen_Producto'
      AND COLUMN_NAME = 'EsChatarra'
);

SET @es_chatarra_sql = IF(
    @es_chatarra_existe = 0,
    'ALTER TABLE Gen_Producto ADD COLUMN EsChatarra TINYINT(1) NOT NULL DEFAULT 0 AFTER ControlaStock',
    'SELECT 1'
);

PREPARE es_chatarra_stmt FROM @es_chatarra_sql;
EXECUTE es_chatarra_stmt;
DEALLOCATE PREPARE es_chatarra_stmt;
