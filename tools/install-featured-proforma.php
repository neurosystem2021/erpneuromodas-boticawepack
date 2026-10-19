<?php
if(PHP_SAPI!=='cli')exit(1);$root=dirname(__DIR__);$live='C:/xampp/htdocs/boticav1';$backup='C:/xampp/accounting-private/featured-proforma-'.date('Ymd-His');mkdir($backup,0700,true);
$files=['proforma-featured.php','proforma-pdf-render.php','proforma-pdf.php','proforma-presentation.php'];
foreach($files as $file){exec('C:/xampp/php/php.exe -l '.escapeshellarg($root.'/integration/proforma-edit/'.$file),$out,$status);if($status!==0)throw new RuntimeException('Syntax failed: '.$file);if(is_file($live.'/api/'.$file))copy($live.'/api/'.$file,$backup.'/'.$file);}
require $live.'/config/database.php';$db=appPdoConnection();$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
file_put_contents($backup.'/Ve_Proforma-schema.sql',$db->query('SHOW CREATE TABLE Ve_Proforma')->fetch(PDO::FETCH_NUM)[1]);
$s=$db->prepare('SHOW COLUMNS FROM Ve_Proforma LIKE ?');$s->execute(['FormatoProducto']);if(!$s->fetch())$db->exec("ALTER TABLE Ve_Proforma ADD COLUMN FormatoProducto VARCHAR(16) NOT NULL DEFAULT 'catalogo'");
$source=$root.'/resources/images/productos/1773254068.jpg';$target=$live.'/resources/images/productos/1773254068.jpg';if(!is_file($target)){if(!is_dir(dirname($target)))mkdir(dirname($target),0755,true);if(!copy($source,$target))throw new RuntimeException('Cannot restore original product photo');}if(hash_file('sha256',$source)!==hash_file('sha256',$target))throw new RuntimeException('Product photo differs from supplied original');
foreach($files as $file){$source=$root.'/integration/proforma-edit/'.$file;$target=$live.'/api/'.$file;if(!copy($source,$target)||hash_file('sha256',$source)!==hash_file('sha256',$target))throw new RuntimeException('Install verification failed: '.$file);}
echo 'Featured format installed with original product photo. Existing quotes default to catalog; systems and POS functions unchanged. Backup: '.$backup;
