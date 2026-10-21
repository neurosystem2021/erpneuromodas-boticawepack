<?php
require 'C:/xampp/htdocs/boticav1/api/vendor/autoload.php';
$env=\Slim\Http\Environment::mock(['REQUEST_METHOD'=>'POST','SCRIPT_NAME'=>'/api/index.php','REQUEST_URI'=>'/api/index.php/emitirelectronico','PATH_INFO'=>'/emitirelectronico']);
$request=\Slim\Http\Request::createFromEnvironment($env);
echo json_encode(['path'=>$request->getUri()->getPath(),'base'=>$request->getUri()->getBasePath()]);
