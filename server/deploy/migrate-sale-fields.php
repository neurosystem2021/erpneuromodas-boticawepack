<?php

/**
 * Agrega de forma idempotente los datos complementarios del comprobante.
 *
 * Uso:
 *   php build/deploy/migrate-sale-fields.php [raiz-del-proyecto]
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

    $columns = array(
        'Observacion' => "VARCHAR(500) NULL AFTER `CampoDireccion`",
        'Placa' => "VARCHAR(20) NULL AFTER `Observacion`",
    );

    $existsStatement = $pdo->prepare(
        'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS '
        . 'WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table AND COLUMN_NAME = :column'
    );

    foreach ($columns as $column => $definition) {
        $existsStatement->execute(array(
            ':schema' => $databaseName,
            ':table' => 'Ve_DocVenta',
            ':column' => $column,
        ));

        if ((int) $existsStatement->fetchColumn() === 0) {
            $pdo->exec('ALTER TABLE `Ve_DocVenta` ADD COLUMN `' . $column . '` ' . $definition);
            fwrite(STDOUT, '[OK] Columna creada: Ve_DocVenta.' . $column . PHP_EOL);
        } else {
            fwrite(STDOUT, '[OK] Columna disponible: Ve_DocVenta.' . $column . PHP_EOL);
        }
    }

    fwrite(STDOUT, '[OK] Campos de observaciones y placa listos.' . PHP_EOL);
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, '[ERROR] No se pudo actualizar Ve_DocVenta: ' . get_class($exception) . ' (' . $exception->getCode() . ')' . PHP_EOL);
    exit(1);
}
