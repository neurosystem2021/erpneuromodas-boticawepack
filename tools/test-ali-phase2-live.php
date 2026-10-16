<?php
ob_start();session_id('nstAliPhase2'.bin2hex(random_bytes(8)));session_start();$_SESSION['user']='superadmin';$_SESSION['expire']=time()+300;$cookie='PHPSESSID='.session_id();session_write_close();
function phase2Call($path,$cookie,$body=null,$csrf=''){$h=curl_init('http://localhost/api/index.php/ali/'.$path);curl_setopt_array($h,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10,CURLOPT_COOKIE=>$cookie,CURLOPT_HTTPHEADER=>['Content-Type: application/json','X-Ali-CSRF: '.$csrf]]);if($body!==null)curl_setopt_array($h,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($body)]);$raw=curl_exec($h);$status=curl_getinfo($h,CURLINFO_HTTP_CODE);curl_close($h);return [$status,json_decode($raw,true)];}
function phase2Check($ok,$name){if(!$ok)throw new Exception($name);}
try{
 [$status,$config]=phase2Call('config',$cookie);phase2Check($status===200&&!empty($config['pos']['write'])&&!isset($config['key']),'safe capabilities');$csrf=$config['csrf'];
 foreach([['search',200],['focus',200],['method',200],['pay',200],['confirm',200],['execute_sql',422]] as $case){[$status]=phase2Call('autorizar',$cookie,['action'=>$case[0],'warehouse'=>1],$csrf);phase2Check($status===$case[1],$case[0].' status');}
 [$status]=phase2Call('autorizar',$cookie,['action'=>'search','warehouse'=>1]);phase2Check($status===403,'csrf');
 [$status]=phase2Call('autorizar',$cookie,['action'=>'search','warehouse'=>1,'admin'=>true],$csrf);phase2Check($status===422,'extra arguments');
 session_start();$_SESSION['expire']=time()-1;session_write_close();[$status]=phase2Call('config',$cookie);phase2Check($status===401,'expired session');
 session_start();$_SESSION['expire']=time()+300;$_SESSION['user']='ali-no-such-user';session_write_close();[$status]=phase2Call('config',$cookie);phase2Check($status===403,'unknown user');
 echo 'PASS live session expiry, actual POS permissions, CSRF, closed action schema and checkout action permission. Temporary sessions only; no sales, changes or OpenAI calls.'.PHP_EOL;
}finally{session_start();$_SESSION=[];session_destroy();ob_end_flush();}
