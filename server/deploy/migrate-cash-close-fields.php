<?php

/**
 * Agrega de forma idempotente la descripcion del cierre de caja.
 *
 * Uso:
 *   php build/deploy/migrate-cash-close-fields.php [raiz-del-proyecto]
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$projectRoot = isset($argv[1]) ? $argv[1] : dirname(__DIR__, 2);
$projectRoot = rtrim(str_replace('\\', '/', $projectRoot), '/');
$databaseBootstrap = $projectRoot . '/config/database.php';

if (!is_readable($databaseBootstrap)) {
    fwrite(STDERR, '[ERROR] No se puede leer la configuracion de base de datos: ' . $databaseBootstrap . PHP_EOL);
    exit(1);
}

try {
    require_once $databaseBootstrap;
    $pdo = appPdoConnection();
    $database = appDatabaseConfig();
    $databaseName = $database['dbname'];

    $existsStatement = $pdo->prepare(
        'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS '
        . 'WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table AND COLUMN_NAME = :column'
    );
    $existsStatement->execute(array(
        ':schema' => $databaseName,
        ':table' => 'Cb_CierreCaja',
        ':column' => 'Descripcion',
    ));

    if ((int) $existsStatement->fetchColumn() === 0) {
        $pdo->exec('ALTER TABLE `Cb_CierreCaja` ADD COLUMN `Descripcion` VARCHAR(500) NULL AFTER `Total`');
        fwrite(STDOUT, '[OK] Columna creada: Cb_CierreCaja.Descripcion' . PHP_EOL);
    } else {
        fwrite(STDOUT, '[OK] Columna disponible: Cb_CierreCaja.Descripcion' . PHP_EOL);
    }

    fwrite(STDOUT, '[OK] Descripcion del cierre de caja lista.' . PHP_EOL);
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, '[ERROR] No se pudo actualizar Cb_CierreCaja: ' . get_class($exception) . ' (' . $exception->getCode() . ')' . PHP_EOL);
    exit(1);
}
