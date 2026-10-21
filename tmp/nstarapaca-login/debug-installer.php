<?php
$root=__DIR__.'/installer-fixture/erp';
$manager=file_get_contents($root.'/models/DBManager.php');
$manager=preg_replace('~^\s*\$mysqli\s*=\s*new\s+mysqli\([^;]+\);~m',"\n    \$mysqli = appMysqliConnection();",$manager,-1,$changes);
if($changes===0&&strpos($manager,'appMysqliConnection(')===false)throw new RuntimeException('DBManager structure needs review.');
if(strpos($manager,"require_once dirname(__DIR__) . '/config/database.php';")===false)$manager=preg_replace('~<\?php~',"<?php\nrequire_once dirname(__DIR__) . '/config/database.php';",$manager,1);
$manager=str_replace("header('Content-Type: text/html;charset=utf-8');",'',$manager);
$api=file_get_contents($root.'/api/index.php');
if(strpos($api,"\$config['db'] = appDatabaseConfig();")===false){
    $connection=<<<'PHP'
require_once dirname(__DIR__) . '/config/database.php';
$config['db'] = appDatabaseConfig();
$config['db_destino'] = appDatabaseConfig('DB_DESTINO_');
if ($config['db_destino']['dbname'] === '') $config['db_destino'] = $config['db'];

PHP;
    $api=preg_replace('~\$config\[\x27db\x27\]\[\x27host\x27\]\s*=.*?(?=\$app\s*=\s*new\s+\\\\?Slim\\\\?App)~s',$connection,$api,1,$apiChanges);
    if($apiChanges!==1)throw new RuntimeException('API connection structure needs review.');
}
$api=preg_replace('~^\s*\$dsn\s*=\s*\x27mysql:host=\x27[^\r\n]*;[ \t]*$~m', "\n    \$dsn = appDatabaseDsn(\$db);",$api,-1,$dsnChanges);
// The DSN expression contains semicolons inside quoted strings; match one line.
echo 'DSN changes: '.var_export($dsnChanges,true).' ERROR '.preg_last_error().PHP_EOL;echo $api;
