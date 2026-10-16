<?php
return function($app){
 $app->get('/descargarxml/{id}',function($q,$r,$args){
  if(session_status()!==PHP_SESSION_ACTIVE)session_start();$user=$_SESSION['user']??'';session_write_close();if(!$user)return $r->withJson(['error'=>'Inicie sesión nuevamente.'],401);
  $s=$this->db->prepare('SELECT v.Serie,v.Numero,t.CodSunat FROM Ve_DocVenta v JOIN Ve_DocVentaTipoDoc t ON t.IdTipoDoc=v.IdTipoDoc WHERE v.idDocVenta=?');$s->execute([(int)$args['id']]);$sale=$s->fetch(PDO::FETCH_ASSOC);if(!$sale)return $r->withJson(['error'=>'Comprobante no encontrado.'],404);
  $ruc=preg_replace('/[^0-9]/','',NRO_DOCUMENTO_EMPRESA);$type=preg_replace('/[^0-9]/','',$sale['CodSunat']);$serie=preg_replace('/[^A-Za-z0-9]/','',$sale['Serie']);$number=(int)$sale['Numero'];$name=$ruc.'-'.$type.'-'.$serie.'-'.$number;
  foreach(array_unique([TIPO_PROCESO==='03'?'BETA':'PRODUCCION','PRODUCCION','BETA','HOMOLOGACION']) as $env){foreach(['XML','xml'] as $ext){$path=__DIR__.'/sunat/api_cpe/'.$env.'/'.$ruc.'/'.$name.'.'.$ext;if(is_file($path)&&filesize($path)>20){$r->getBody()->write(file_get_contents($path));return $r->withHeader('Content-Type','application/xml; charset=UTF-8')->withHeader('Content-Disposition','attachment; filename="'.$name.'.xml"')->withHeader('Cache-Control','private, no-store');}}}
  return $r->withJson(['error'=>'El XML aún no está disponible para este comprobante.'],404);
 });
};
