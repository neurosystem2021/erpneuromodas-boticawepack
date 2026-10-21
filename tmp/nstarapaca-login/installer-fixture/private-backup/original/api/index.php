<?php
$config['db']['host']='localhost';
$config['db']['user']='fixture';
$config['db']['pass']='';
$config['db']['dbname']='fixture';
$config['db_destino']['host']='localhost';
$config['db_destino']['user']='fixture';
$config['db_destino']['pass']='';
$config['db_destino']['dbname']='fixture';
$app = new \Slim\App(['settings'=>$config]);
$container['db'] = function($c) {
 $db=$c['settings']['db'];
 $dsn = 'mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'] . ';charset=utf8';
 return $dsn;
};
$container['db_destino'] = function($c) {
 $db=$c['settings']['db_destino'];
 $dsn = 'mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'] . ';charset=utf8';
 return $dsn;
};
