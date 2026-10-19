<?php
if(PHP_SAPI!=='cli')exit(1);
$root=dirname(__DIR__);$live='C:/xampp/htdocs/boticav1/api';
$names=['proforma-pdf-render.php','proforma-contract-core.php'];
foreach($names as $name){
 $source=$root.'/integration/proforma-edit/'.$name;
 if(!is_file($live.'/'.$name))throw new RuntimeException('Missing installed file: '.$name);
 exec('C:/xampp/php/php.exe -l '.escapeshellarg($source),$output,$status);
 if($status!==0)throw new RuntimeException('Invalid PHP: '.$name);
}
$backup='C:/xampp/accounting-private/proforma-signatories-'.date('Ymd-His');
if(!mkdir($backup,0700,true))throw new RuntimeException('Cannot create backup');
foreach($names as $name){
 $source=$root.'/integration/proforma-edit/'.$name;$target=$live.'/'.$name;
 if(!copy($target,$backup.'/'.$name)||!copy($source,$target)||hash_file('sha256',$source)!==hash_file('sha256',$target))throw new RuntimeException('Install verification failed: '.$name);
}
echo 'Proposal consultant and three-signature contract renderer installed. No business records changed. Backup: '.$backup;
