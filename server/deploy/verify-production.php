<?php

/**
 * Verificacion de una instalacion ERP sin mostrar credenciales.
 *
 * Uso:
 *   php build/deploy/verify-production.php [raiz-del-proyecto]
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$projectRoot = isset($argv[1]) ? $argv[1] : dirname(__DIR__, 2);
$projectRoot = rtrim(str_replace('\\', '/', $projectRoot), '/');
$errors = array();
$warnings = array();

function checkOk($message)
{
    fwrite(STDOUT, "[OK] " . $message . PHP_EOL);
}

function checkError($message)
{
    global $errors;
    $errors[] = $message;
    fwrite(STDERR, "[ERROR] " . $message . PHP_EOL);
}

function checkWarning($message)
{
    global $warnings;
    $warnings[] = $message;
    fwrite(STDOUT, "[AVISO] " . $message . PHP_EOL);
}

if (!is_dir($projectRoot)) {
    checkError('No existe la raiz del proyecto: ' . $projectRoot);
    exit(1);
}

$databaseBootstrap = $projectRoot . '/config/database.php';
$databaseLocal = $projectRoot . '/config/database.local.php';
$apiIndex = $projectRoot . '/api/index.php';
$apiAutoload = $projectRoot . '/api/vendor/autoload.php';

foreach (array($databaseBootstrap, $databaseLocal, $apiIndex, $apiAutoload) as $requiredFile) {
    if (!is_readable($requiredFile)) {
        checkError('No se puede leer: ' . $requiredFile);
    } else {
        checkOk('Archivo disponible: ' . substr($requiredFile, strlen($projectRoot) + 1));
    }
}

if (PHP_VERSION_ID >= 80000) {
    checkWarning('La aplicacion heredada esta certificada con PHP 7.4; PHP actual: ' . PHP_VERSION);
} else {
    checkOk('Version PHP compatible: ' . PHP_VERSION);
}

foreach (array('mysqli', 'pdo_mysql') as $extension) {
    if (!extension_loaded($extension)) {
        checkError('Extension PHP requerida no instalada: ' . $extension);
    } else {
        checkOk('Extension PHP cargada: ' . $extension);
    }
}

if (!empty($errors)) exit(1);

try {
    require_once $databaseBootstrap;
    $dbConfig = appDatabaseConfig();
    foreach (array('host', 'port', 'dbname', 'user', 'charset') as $key) {
        if (!isset($dbConfig[$key]) || trim((string) $dbConfig[$key]) === '') {
            checkError('Falta configurar el valor de base de datos: ' . $key);
        }
    }

    if (empty($errors)) {
        checkOk('Configuracion privada cargada para la base ' . $dbConfig['dbname'] . ' en ' . $dbConfig['host']);
    }
} catch (Throwable $exception) {
    checkError('No se pudo cargar la configuracion: ' . get_class($exception) . ' (' . $exception->getCode() . ')');
}

if (!empty($errors)) exit(1);

try {
    $mysqli = appMysqliConnection();
    $result = $mysqli->query('SELECT 1 AS health_check');
    if (!$result || !(int) $result->fetch_assoc()['health_check']) {
        throw new RuntimeException('SELECT 1 no devolvio el valor esperado.');
    }
    $mysqli->close();
    checkOk('Conexion mysqli y consulta de prueba correctas.');
} catch (Throwable $exception) {
    checkError('Fallo mysqli: ' . get_class($exception) . ' (' . $exception->getCode() . ')');
}

try {
    $pdo = appPdoConnection();
    $value = $pdo->query('SELECT 1')->fetchColumn();
    if ((int) $value !== 1) throw new RuntimeException('SELECT 1 no devolvio el valor esperado.');
    $pdo = null;
    checkOk('Conexion PDO y consulta de prueba correctas.');
} catch (Throwable $exception) {
    checkError('Fallo PDO: ' . get_class($exception) . ' (' . $exception->getCode() . ')');
}

if (!empty($errors)) {
    fwrite(STDERR, PHP_EOL . 'La instalacion tiene ' . count($errors) . ' error(es).' . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, PHP_EOL . 'VERIFICACION COMPLETA: configuracion y base de datos disponibles.' . PHP_EOL);
exit(0);
