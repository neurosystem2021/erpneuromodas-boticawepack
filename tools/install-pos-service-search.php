<?php
if(PHP_SAPI!=='cli')exit(1);
$root=dirname(__DIR__);$live='C:/xampp/htdocs/boticav1/api/index.php';
$backup='C:/xampp/accounting-private/pos-service-search-'.date('Ymd-His');mkdir($backup,0700,true);
$original=file_get_contents($live);file_put_contents($backup.'/index.php',$original);
if(hash_file('sha256',$backup.'/index.php')!==hash('sha256',$original))throw new RuntimeException('Backup failed');
function patchPosServiceSearch($text){
 $start=strpos($text,"\$app->get('/productos/stock[/{idAlmacen}]'");
 if($start===false)throw new RuntimeException('Stock route missing');
 $end=strpos($text,"\$app->get('/movimientos/numero'",$start);
 if($end===false)throw new RuntimeException('Stock route boundary missing');
 $route=substr($text,$start,$end-$start);
 $old='        $select .= "AND (Gen_Producto.ControlaStock=1 OR Gen_Producto.CodigoBarra=\'MANODEOBRA\')";';
 if(strpos($route,"getParam('incluirServicios')")===false){
  if(substr_count($route,$old)!==1)throw new RuntimeException('Unexpected stock restriction');
  $new="        // POS explicitly includes services; inventory reports retain their stock-only filter.\n        if ((int)\$request->getParam('incluirServicios') !== 1) {\n".$old."\n        }";
  $route=str_replace($old,$new,$route);
 }
 return substr($text,0,$start).$route.substr($text,$end);
}
$candidate=patchPosServiceSearch($original);file_put_contents($backup.'/candidate.php',$candidate);
exec('C:/xampp/php/php.exe -l '.escapeshellarg($backup.'/candidate.php'),$output,$status);
if($status!==0)throw new RuntimeException('Syntax failed');
file_put_contents($live,$candidate);
if(hash_file('sha256',$live)!==hash('sha256',$candidate))throw new RuntimeException('Install hash mismatch');
$staged=$root.'/integration/ali-index.php';if(is_file($staged))file_put_contents($staged,patchPosServiceSearch(file_get_contents($staged)));
echo 'POS service search installed. Inventory filter preserved. Backup: '.$backup;
