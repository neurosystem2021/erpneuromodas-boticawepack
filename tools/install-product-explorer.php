<?php
if(PHP_SAPI!=='cli')exit(1);$root=dirname(__DIR__);$live='C:/xampp/htdocs/boticav1/api';$backup='C:/xampp/accounting-private/product-explorer-'.date('Ymd-His');mkdir($backup,0700,true);
$api=file_get_contents($live.'/ali-api.php');copy($live.'/ali-api.php',$backup.'/ali-api.php');
if(strpos($api,"require_once __DIR__.'/ali-product-explorer.php';")===false){$needle="require_once __DIR__.'/ali-product-labor.php';";if(substr_count($api,$needle)!==1)throw new RuntimeException('Unexpected ALI dependency block');$api=str_replace($needle,$needle."\nrequire_once __DIR__.'/ali-product-explorer.php';",$api);}
if(strpos($api,'return aliProductExplorer(')===false){$needle="\$app->post('/ali/producto',function(\$q,\$r){";if(substr_count($api,$needle)!==1)throw new RuntimeException('Unexpected product route');$api=str_replace($needle,$needle."if(in_array(\$q->getParsedBody()['mode']??'',['explore','explore_images'],true))return aliProductExplorer(\$this->db,\$q,\$r);",$api);}
$api=str_replace("['explore','explore_images']","['explore','explore_images','explore_videos']",$api);
if(strpos($api,"['explore','explore_images','explore_videos']")===false)throw new RuntimeException('Unexpected explorer modes');
$files=['ali-product-explorer.php'=>file_get_contents($root.'/integration/ali-product-explorer.php'),'ali-api.php'=>$api];
foreach($files as $name=>$content){$candidate=$backup.'/'.$name.'.candidate';file_put_contents($candidate,$content);exec('C:/xampp/php/php.exe -l '.escapeshellarg($candidate),$out,$status);if($status!==0)throw new RuntimeException('Invalid PHP: '.$name);if(is_file($live.'/'.$name))copy($live.'/'.$name,$backup.'/'.$name);}
foreach($files as $name=>$content){file_put_contents($live.'/'.$name,$content);if(hash_file('sha256',$live.'/'.$name)!==hash('sha256',$content))throw new RuntimeException('Install verification failed');}
file_put_contents($root.'/integration/ali-api.php',$api);echo 'Read-only product explorer installed under existing POS permissions, CSRF and rate limits. No database schema or sale changed. Backup: '.$backup;
