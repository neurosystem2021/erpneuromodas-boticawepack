<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
// Invoked by core-test.php while its isolated database exists.
function coHttpTests(PDO $db,$schema,$companyA,$companyB){
 $db->exec('ALTER TABLE Seg_Usuario ADD IdUsuarioPerfil INT NOT NULL DEFAULT 2');
 $dir=dirname(__DIR__,3).'/tmp/co-http-'.bin2hex(random_bytes(6));mkdir($dir,0700,true);mkdir($dir.'/api',0700);mkdir($dir.'/config',0700);mkdir($dir.'/sessions',0700);
 foreach(glob(__DIR__.'/../api/*.php') as $f)copy($f,$dir.'/api/'.basename($f));copy(__DIR__.'/../api/accounting-pcge2019.json',$dir.'/api/accounting-pcge2019.json');
 $cfg=appDatabaseConfig();$cfg['dbname']=$schema;
 $config='<?php function appPdoConnection(){ $c='.var_export($cfg,true).';return new PDO("mysql:host=".$c["host"].";port=".$c["port"].";dbname=".$c["dbname"].";charset=utf8mb4",$c["user"],$c["pass"],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);}';file_put_contents($dir.'/config/database.php',$config);
 $token=bin2hex(random_bytes(24));$router='<?php ini_set("display_errors","0");session_save_path(__DIR__."/sessions");if(parse_url($_SERVER["REQUEST_URI"],PHP_URL_PATH)==="/fixture-login"){if(!hash_equals('.var_export($token,true).',$_GET["token"]??"")) {http_response_code(403);exit;}session_start();$_SESSION["user"]=$_GET["user"];$_SESSION["expire"]=time()+60;echo "ok";return;}return false;';file_put_contents($dir.'/router.php',$router);
 $port=random_int(19000,23000);$process=proc_open([PHP_BINARY,'-S','127.0.0.1:'.$port,'-t',$dir,$dir.'/router.php'],[0=>['pipe','r'],1=>['file',$dir.'/server.out','a'],2=>['file',$dir.'/server.err','a']],$pipes,null,null,['bypass_shell'=>true]);fclose($pipes[0]);
 $request=function($path,$method='GET',$payload=null,$cookie='',$csrf='')use($port){$headers=['Content-Type: application/json'];if($cookie)$headers[]='Cookie: '.$cookie;if($csrf)$headers[]='X-Accounting-CSRF: '.$csrf;$context=stream_context_create(['http'=>['method'=>$method,'ignore_errors'=>true,'timeout'=>5,'header'=>implode("\r\n",$headers),'content'=>$payload===null?'':coJson($payload)]]);$body=file_get_contents('http://127.0.0.1:'.$port.$path,false,$context);$responseHeaders=$http_response_header??[];$status=isset($responseHeaders[0])?(int)explode(' ',$responseHeaders[0])[1]:0;$session='';foreach($responseHeaders as $h)if(stripos($h,'Set-Cookie: PHPSESSID=')===0)$session=explode(';',substr($h,12))[0];return [$status,$body,$session];};
 try{
  $ready=false;for($i=0;$i<40;$i++){$sock=@fsockopen('127.0.0.1',$port,$err,$msg,0.1);if($sock){fclose($sock);$ready=true;break;}usleep(100000);}check($ready,'isolated HTTP server available');
  $url='/api/accounting.php';check($request($url)[0]===401,'HTTP requires ERP session');$login=$request('/fixture-login?token='.$token.'&user=alice');$cookie=$login[2];$context=$request($url,$method='GET',null,$cookie);check($context[0]===200,'HTTP authenticated context');$context=json_decode($context[1],true);check(count($context['companies'])===1,'HTTP company membership scope');$csrf=$context['csrf'];
  check($request($url.'?action=company&companyId='.$companyB,'GET',null,$cookie)[0]===403,'HTTP cross-company read denied');
  check($request($url,'POST',['action'=>'draft','companyId'=>$companyA],$cookie)[0]===403,'HTTP missing CSRF denied');
  check($request($url,'POST',['action'=>'draft','companyId'=>$companyA],$cookie,'wrong')[0]===403,'HTTP wrong CSRF denied');
  check($request($url.'?action=period&companyId='.$companyA.'&period=2026-12','GET',null,$cookie)[0]===404,'GET cannot mutate accounting');
  check($request($url,'POST',['action'=>'createCompany','name'=>'Unauthorized','ruc'=>'20131312955','regime'=>'mype'],$cookie,$csrf)[0]===403,'HTTP company creation restricted');
  check($request($url,'POST',['action'=>'draft','companyId'=>$companyA,'sourceType'=>'erp_sale'],$cookie,$csrf)[0]===422,'HTTP cannot forge ERP source identity');
  check($request($url.'?action=report&report=trial&companyId='.$companyA.'&from=2026-10-01&to=2026-10-31','GET',null,$cookie)[0]===200,'HTTP accounting report');
  check($request($url.'?action=export&format=csv&report=trial&companyId='.$companyB.'&from=2026-10-01&to=2026-10-31','GET',null,$cookie)[0]===403,'HTTP export isolation');
  $login=$request('/fixture-login?token='.$token.'&user=assistant');$cookie=$login[2];$context=json_decode($request($url,'GET',null,$cookie)[1],true);check($request($url,'POST',['action'=>'transition','transition'=>'post','companyId'=>$companyA,'id'=>1,'version'=>1],$cookie,$context['csrf'])[0]===403,'HTTP assistant cannot post');
 }finally{proc_terminate($process);proc_close($process);foreach(['api','config','sessions'] as $part){foreach(glob($dir.'/'.$part.'/*') as $file)if(is_file($file))unlink($file);rmdir($dir.'/'.$part);}foreach(glob($dir.'/*') as $file)if(is_file($file))unlink($file);rmdir($dir);}
}
