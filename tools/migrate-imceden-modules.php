<?php
if(PHP_SAPI!=='cli')exit;
$target='C:/xampp/htdocs/boticav1';$source=file_get_contents($target.'/api/index.php');$settings=[];foreach(['host','user','pass','dbname']as$key){if(!preg_match('/^\$config\[\x27db\x27\]\[\x27'.$key.'\x27\]\s*=\s*[\x27"]([^\x27"]*)[\x27"]/m',$source,$m))throw new RuntimeException('Database configuration unavailable.');$settings[$key]=$m[1];}
$db=new PDO('mysql:host='.$settings['host'].';dbname='.$settings['dbname'].';charset=utf8mb4',$settings['user'],$settings['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
// Private backup before additive schema changes.
$dir=$target.'/storage/imceden-backups';if(!is_dir($dir))mkdir($dir,0700,true);file_put_contents($dir.'/.htaccess',"Require all denied\nDeny from all\n");
$backup=$dir.'/database-'.date('Ymd-His').'.sql';$env=getenv();$env['MYSQL_PWD']=$settings['pass'];$cmd=['C:/xampp/mysql/bin/mysqldump.exe','--single-transaction','--routines','--triggers','--host='.$settings['host'],'--user='.$settings['user'],$settings['dbname']];$proc=proc_open($cmd,[0=>['pipe','r'],1=>['file',$backup,'w'],2=>['pipe','w']],$pipes,null,$env);fclose($pipes[0]);$err=stream_get_contents($pipes[2]);fclose($pipes[2]);if(proc_close($proc)!==0)throw new RuntimeException('Database backup failed.');
$cfg=$settings+['charset'=>'utf8mb4','port'=>'3306'];file_put_contents($target.'/config/database.local.php',"<?php\nreturn ".var_export($cfg,true).";\n");
foreach(glob($target.'/data/crm/*_up.sql')as$file)$db->exec(file_get_contents($file));
$db->exec(file_get_contents($target.'/migration_tienda_cliente_cuenta.sql'));
$db->exec(file_get_contents($target.'/migration_product_images.sql'));
$db->exec('ALTER TABLE Gen_Producto ADD COLUMN IF NOT EXISTS IdProductoTalla INT NULL, ADD COLUMN IF NOT EXISTS CodeBarModelo VARCHAR(128) NULL');
$exists=$db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='Gen_Producto_Totales'")->fetchColumn();if(!$exists)$db->exec(file_get_contents($target.'/data/imceden-stock-view.sql'));
echo "Database backup created. CRM and store schema ready; stock reads the current ledger.\n";
