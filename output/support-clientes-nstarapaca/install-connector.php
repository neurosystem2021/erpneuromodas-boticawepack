<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
// Add only a support endpoint and isolated UI directory to an existing ERP.
$o=getopt('',['erp-root:','private-config:']);if(empty($o['erp-root'])||empty($o['private-config'])){echo "Usage: php install-connector.php --erp-root=/path/to/erp --private-config=/private/support/support-client.php\n";exit(1);}
$root=realpath($o['erp-root']);$private=realpath($o['private-config']);if(!$root||!$private||!is_file($private))throw new RuntimeException('ERP or private config not found');$normalize=function($p){return strtolower(str_replace('\\','/',$p));};if(strpos($normalize($private),$normalize($root).'/')===0)throw new RuntimeException('Private config must be outside ERP webroot');
$index=$root.'/api/index.php';$header=$root.'/views/header.php';if(!is_file($index)||!is_file($header)||!is_file($root.'/config/database.php'))throw new RuntimeException('Expected NST-compatible ERP structure');$api=file_get_contents($index);if(strpos($api,"require __DIR__.'/support-api.php'")!==false)throw new RuntimeException('This is a support central; do not replace it with a client connector');
$hook='$registerSupportConnector=require __DIR__.\'/support-connector.php\';$registerSupportConnector($app);';$anchor='$app->map([\'GET\', \'POST\', \'PUT\', \'DELETE\', \'PATCH\']';if(strpos($api,$hook)===false){if(substr_count($api,$anchor)!==1)throw new RuntimeException('API registration needs review');$api=str_replace($anchor,$hook."\n\n".$anchor,$api);}
$html=file_get_contents($header);$link='<li><a href="/views/support-client.php">Centro de soporte</a></li>';$html=str_replace($link,'',$html);$widget='<script defer src="/support-ui/widget.js"></script>';if(strpos($html,$widget)===false)$html.="\n".$widget;

if(!is_file(__DIR__.'/ui/index.html'))throw new RuntimeException('Compiled support UI package is missing');
$backup=dirname($private).'/installation-backup-'.date('Ymd-His').'-'.bin2hex(random_bytes(3));if(!mkdir($backup,0700,true))throw new RuntimeException('Private backup directory unavailable');copy($index,$backup.'/api-index.php');copy($header,$backup.'/header.php');foreach(['api/support-connector.php','api/support-connector-location.php','views/support-client.php'] as $relative)if(is_file($root.'/'.$relative))copy($root.'/'.$relative,$backup.'/'.str_replace('/','-',$relative));
// Copy the separate UI; existing /nuevo assets are untouched.
$copy=function($source,$target)use(&$copy){if(!is_dir($target)&&!mkdir($target,0755,true))throw new RuntimeException('UI directory unavailable');foreach(new DirectoryIterator($source) as $entry){if($entry->isDot())continue;$dest=$target.'/'.$entry->getFilename();if($entry->isDir())$copy($entry->getPathname(),$dest);elseif(!copy($entry->getPathname(),$dest))throw new RuntimeException('UI copy failed');}};
$copy(__DIR__.'/ui',$root.'/support-ui');copy(__DIR__.'/widget.js',$root.'/support-ui/widget.js');copy(__DIR__.'/support-connector.php',$root.'/api/support-connector.php');file_put_contents($root.'/api/support-connector-location.php','<?php return '.var_export($private,true).';');
$view=<<<'PHP'
<?php
if(session_status()!==PHP_SESSION_ACTIVE)session_start();
if(empty($_SESSION['user'])||(isset($_SESSION['expire'])&&$_SESSION['expire']<time())){header('Location: /');exit;}
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Centro de soporte</title></head><body style="margin:0"><iframe title="Centro de soporte" src="/support-ui/index.html#/soporte" style="width:100%;height:100vh;border:0;display:block"></iframe></body></html>
PHP;
file_put_contents($root.'/views/support-client.php',$view);file_put_contents($index.'.support-tmp',$api);rename($index.'.support-tmp',$index);file_put_contents($header.'.support-tmp',$html);rename($header.'.support-tmp',$header);echo "Connector installed with backup. Register its public manifest at the central before use.\n";
