<?php

/**
 * Carga variables desde el .env privado de la raíz.
 *
 * Los valores del archivo tienen prioridad. Esto evita que variables DB_ antiguas
 * definidas por Apache, PHP-FPM o una sesión SSH desvíen la aplicación hacia otra
 * base después de una actualización. Las variables del sistema siguen funcionando
 * cuando no existe un .env o cuando una clave no está declarada en él.
 */
function appLoadEnvironment($file = null)
{
    static $loaded = false;
    if ($loaded) return;
    $loaded = true;

    $file = $file ?: dirname(__DIR__) . '/.env';
    if (!is_readable($file)) return;

    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) continue;
        $name = trim($parts[0]);
        if (!preg_match('/^[A-Z_][A-Z0-9_]*$/i', $name)) continue;
        $value = trim($parts[1]);
        if (strlen($value) >= 2 && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))) {
            $value = substr($value, 1, -1);
        }
        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

function appEnv($name, $default = null)
{
    appLoadEnvironment();
    $value = getenv($name);
    return $value === false ? $default : $value;
}

/**
 * Configuración privada y permanente de cada instalación.
 * Este archivo tiene prioridad sobre .env y nunca debe versionarse.
 */
function appLocalDatabaseConfig()
{
    static $config = null;
    if ($config !== null) return $config;

    $config = array();
    $file = __DIR__ . '/database.local.php';
    if (!is_readable($file)) return $config;

    $loaded = require $file;
    if (is_array($loaded)) $config = $loaded;
    return $config;
}

function appDatabaseConfig($prefix = 'DB_')
{
    $local = appLocalDatabaseConfig();
    $section = $prefix === 'DB_' ? 'default' : strtolower(trim($prefix, '_'));
    if ($section === 'default' && isset($local['host'])) {
        $localSection = $local;
    } else {
        $localSection = isset($local[$section]) && is_array($local[$section]) ? $local[$section] : array();
    }

    $user = appEnv($prefix . 'USER', appEnv($prefix . 'USERNAME', ''));
    $pass = appEnv($prefix . 'PASS', appEnv($prefix . 'PASSWORD', ''));
    $environment = array(
        'host' => appEnv($prefix . 'HOST', '127.0.0.1'),
        'port' => appEnv($prefix . 'PORT', '3306'),
        'dbname' => appEnv($prefix . 'NAME', ''),
        'user' => $user,
        'pass' => $pass,
        'charset' => appEnv($prefix . 'CHARSET', 'utf8mb4'),
    );

    return array_merge($environment, array_intersect_key($localSection, $environment));
}

function appDatabaseDsn(array $db)
{
    return 'mysql:host=' . $db['host']
        . ';port=' . $db['port']
        . ';dbname=' . $db['dbname']
        . ';charset=' . $db['charset'];
}

function appMysqliConnection()
{
    $db = appDatabaseConfig();
    $mysqli = new mysqli($db['host'], $db['user'], $db['pass'], $db['dbname'], (int) $db['port']);
    if ($mysqli->connect_errno) {
        throw new RuntimeException('No se pudo conectar con la base de datos.', (int) $mysqli->connect_errno);
    }
    if (!$mysqli->set_charset($db['charset'])) throw new RuntimeException('No se pudo configurar el juego de caracteres.');
    return $mysqli;
}

function appPdoConnection()
{
    $db = appDatabaseConfig();
    return new PDO(appDatabaseDsn($db), $db['user'], $db['pass'], array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ));
}
