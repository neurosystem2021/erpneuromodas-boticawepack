from pathlib import Path
p=Path('integration/ali-api.php');s=p.read_text(encoding='utf-8')
s=s.replace('// ALI stores credentials outside the public document root.', "// ALI stores credentials outside the public document root.\nrequire_once __DIR__.'/ali-pos-security.php';",1)
s=s.replace('$auth=function($q,$r,$next){', '$auth=function($q,$r,$next)use($app){',1)
s=s.replace("if(empty($_SESSION['user']))", "if(isset($_SESSION['expire'])&&(int)$_SESSION['expire']<time()){session_write_close();return $r->withJson(['error'=>'La sesión caducó. Vuelve a iniciar sesión.','code'=>'session_expired'],401);}if(empty($_SESSION['user']))",1)
s=s.replace("session_write_close();return $next($q,$r)->withHeader", "session_write_close();try{$capabilities=aliPosCapabilities($app->getContainer()['db'],$q->getAttribute('ali_user'));}catch(Throwable $e){return $r->withJson(['error'=>'No se pudieron verificar los permisos del POS.'],503);}if(!$capabilities['read'])return $r->withJson(['error'=>'No tienes acceso al punto de venta.','code'=>'forbidden_pos'],403);$q=$q->withAttribute('ali_pos',$capabilities);return $next($q,$r)->withHeader",1)
s=s.replace("'user'=>$q->getAttribute('ali_user')", "'user'=>$q->getAttribute('ali_user'),'pos'=>$q->getAttribute('ali_pos')",1)
marker=" $app->post('/ali/config'"
s=s.replace(marker,""" $app->post('/ali/autorizar',function($q,$r){[$code,$message]=aliValidatePosAction($q->getParsedBody(),$q->getAttribute('ali_pos'));if($code!==200)return $r->withJson(['error'=>$message],$code);return $r->withJson(['authorized'=>true]);})->add($auth);
"""+marker,1)
p.write_text(s,encoding='utf-8')
