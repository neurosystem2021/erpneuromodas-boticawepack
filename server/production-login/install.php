<?php
if(PHP_SAPI!=='cli')exit;
$options=getopt('',array('root:','backup:'));
$root=realpath($options['root']??'');$backup=$options['backup']??'';
if(!$root||!$backup||file_exists($backup))throw new RuntimeException('Use --root=/ruta/ERP --backup=/ruta/privada/nueva');
$parent=realpath(dirname($backup));
if(!$parent||strpos(strtolower(str_replace('\\','/',$parent)).'/',strtolower(str_replace('\\','/',$root)).'/')===0)throw new RuntimeException('Backup must be outside the public ERP.');
foreach(array('index.php','api/index.php','models/DBManager.php','controllers/validarLogin.php','config/database.local.php') as $file)if(!is_file($root.'/'.$file))throw new RuntimeException('Required file unavailable: '.$file);
$manager=file_get_contents($root.'/models/DBManager.php');
$manager=preg_replace('~^[ \t]*//[^\r\n]*new[ \t]+mysqli[^\r\n]*\r?\n?~m','',$manager);
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
$api=preg_replace('~^[ \t]*\$dsn[ \t]*=[ \t]*\x27mysql:host=\x27[^\r\n]*;[ \t]*\r?$~m', "    \$dsn = appDatabaseDsn(\$db);",$api,-1,$dsnChanges);
// The DSN expression contains semicolons inside quoted strings; match one line.
if(strpos($api,'appDatabaseDsn($db)')===false)throw new RuntimeException('API DSN structure needs review.');
$html=file_get_contents($root.'/index.php');
if(strpos($html,'id="nst-login-handler"')===false){
    $html=preg_replace('~^[ \t]*function acceder\(event\)\s*\{.*?(?=\s*</script>)~sm','',$html,1,$htmlChanges);
    if($htmlChanges!==1)throw new RuntimeException('Login page structure needs review.');
    $html=str_replace('</body>','<script id="nst-login-handler" src="/resources/js/nstarapaca-login.js?v=20261021-1"></script>'."\n</body>",$html);
}
$files=array('models/DBManager.php'=>$manager,'api/index.php'=>$api,'index.php'=>$html,
    'controllers/validarLogin.php'=>file_get_contents(__DIR__.'/validarLogin.php'),
    'controllers/nst-login-auth.php'=>file_get_contents(__DIR__.'/auth.php'),
    'resources/js/nstarapaca-login.js'=>file_get_contents(__DIR__.'/login.js'),
    'config/database.php'=>file_get_contents(__DIR__.'/database.php'));
if(!mkdir($backup,0700,true))throw new RuntimeException('Cannot create private backup.');
$manifest=array();
foreach($files as $relative=>$contents){
    $candidate=$backup.'/staged/'.$relative;if(!is_dir(dirname($candidate)))mkdir(dirname($candidate),0700,true);file_put_contents($candidate,$contents);
    if(substr($relative,-4)==='.php'){
        $p=proc_open(array(PHP_BINARY,'-l',$candidate),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes,null,null,array('bypass_shell'=>true));
        fclose($pipes[0]);$lint=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
        if(proc_close($p)!==0)throw new RuntimeException('Syntax check failed: '.$relative);
    }
    $target=$root.'/'.$relative;$old=is_file($target)?file_get_contents($target):null;
    if($old!==null){$saved=$backup.'/original/'.$relative;if(!is_dir(dirname($saved)))mkdir(dirname($saved),0700,true);file_put_contents($saved,$old);}
    $manifest[$relative]=array('before'=>$old===null?null:hash('sha256',$old),'after'=>hash('sha256',$contents));
}
foreach($files as $relative=>$contents){$target=$root.'/'.$relative;if(is_file($target)&&hash_file('sha256',$target)!==$manifest[$relative]['before'])throw new RuntimeException('File changed during preflight: '.$relative);}
foreach($files as $relative=>$contents){$target=$root.'/'.$relative;if(!is_dir(dirname($target)))mkdir(dirname($target),0755,true);if(file_put_contents($target,$contents)===false)throw new RuntimeException('Cannot update '.$relative);}
file_put_contents($backup.'/manifest.json',json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo "Connection/login patch installed. database.local.php preserved. No database rows modified.\n";
