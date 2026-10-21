<?php
require_once dirname(__DIR__) . '/config/database.php';
$config['db'] = appDatabaseConfig();
$config['db_destino'] = appDatabaseConfig('DB_DESTINO_');
if ($config['db_destino']['dbname'] === '') $config['db_destino'] = $config['db'];
$app = new \Slim\App(['settings'=>$config]);
$container['db'] = function($c) {
 $db=$c['settings']['db'];
    $dsn = appDatabaseDsn($db);
 return $dsn;
};
$container['db_destino'] = function($c) {
 $db=$c['settings']['db_destino'];
    $dsn = appDatabaseDsn($db);
 return $dsn;
};
