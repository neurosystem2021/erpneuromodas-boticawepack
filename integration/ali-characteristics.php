<?php
function aliCharacteristicItems($text){
 $text=preg_replace('/\[([^\]]+)\]\(https?:\/\/[^)]+\)/u','$1',(string)$text);
 $text=preg_replace('/https?:\/\/\S+|\[\d+\]|\*\*/u','',$text);
 $text=preg_replace('/(?:No se encontr[oó]|No hay)\s+(?:un\s+)?video[^.\n]*(?:\.|$)/iu','',$text);
 $parts=preg_split('/\R+|(?<!\d)[.;]\s+(?=[A-ZÁÉÍÓÚÑ])/u',$text);$items=[];
 foreach($parts as $part){$part=trim(preg_replace('/^\s*(?:[-•*]|\d+[.)])\s*/u','',$part));if($part===''||preg_match('/^(?:videos?|recursos?|fuentes?|para confirmar|datos pendientes)\s*[:：]?$/iu',$part))continue;
  $part=preg_replace('/^(?:La|El|Esta|Este)\s+.+?\s+(?:cuenta con|está equipad[ao] con|dispone de|tiene)\s+/iu','',$part);
  $part=preg_replace('/^(?:Incluye|Cuenta con|Dispone de|Su|Sus)\s+/iu','',$part);
  $part=preg_replace('/^(?:un[ao]?\s+|la\s+|el\s+)?(procesador|memoria RAM|almacenamiento|pantalla|batería|cámara|conectividad|sistema operativo|resolución|lente|alimentación|potencia|conector)\b\s*(?:es\s+|de\s+)?\s*:?\s*/iu','$1: ',$part);
  $part=rtrim(trim($part),'.');if($part!==''&&!in_array($part,$items,true))$items[]=$part;
 }
 return $items;
}
function aliCharacteristicText($text){$lines=[];foreach(aliCharacteristicItems($text) as $index=>$item)$lines[]=($index+1).'. '.$item;return implode("\n",$lines);}
function aliProformaKnowledge($db,$body){
 if(!is_array($body)||empty($body['productos'])||!is_array($body['productos']))return $body;
 $ids=[];foreach($body['productos'] as $p){$id=filter_var($p['IdProducto']??null,FILTER_VALIDATE_INT);if($id&&$id>0)$ids[$id]=$id;}if(!$ids)return $body;
 $s=$db->prepare('SELECT f.*,p.IdProducto,p.Producto,p.ProductoModelo,p.ProductoDescCorto,p.ProductoDesc FROM Ali_ProductoFicha f JOIN Gen_Producto p ON p.IdProducto=f.product_id AND p.Anulado=0 WHERE f.product_id IN ('.implode(',',array_fill(0,count($ids),'?')).')');$s->execute(array_values($ids));$guides=[];
 foreach($s->fetchAll(PDO::FETCH_ASSOC) as $row){$catalog=[];foreach(['IdProducto','Producto','ProductoModelo','ProductoDescCorto','ProductoDesc'] as $field)$catalog[$field]=$row[$field];if(!hash_equals($row['catalog_hash'],aliKnowledgeHash($catalog)))continue;$items=aliCharacteristicItems($row['characteristics']);if(!$items)continue;if($row['model']!=='')array_unshift($items,'Modelo: '.$row['model']);$guides[(int)$row['product_id']]=implode("\n",array_map(fn($item,$n)=>($n+1).'. '.$item,$items,array_keys($items)));}
 foreach($body['productos'] as &$p){$spec=$guides[(int)($p['IdProducto']??0)]??'';if($spec==='')continue;$manual=trim((string)($p['Descripcion']??''));$manual=preg_split('/(?:^|\R)Características técnicas:\s*\R/iu',$manual)[0];$manual=trim($manual);$p['Descripcion']=($manual!==''?$manual."\n\n":'')."Características técnicas:\n".$spec;}unset($p);return $body;
}
function aliRegisterProformaKnowledge($app){$app->add(function($q,$r,$next)use($app){
 $path=$q->getUri()->getPath();
 if($q->getMethod()==='POST'&&preg_match('~(?:^|/)proformas$~',$path)){try{$q=$q->withParsedBody(aliProformaKnowledge($app->getContainer()['db'],$q->getParsedBody()));}catch(Throwable $e){return $r->withJson(['error'=>'No se pudieron recuperar las características guardadas. Intenta nuevamente.'],503);}}
 $response=$next($q,$r);
 if($q->getMethod()==='GET'&&preg_match('~(?:^|/)proformas/id/\d+$~',$path)&&$response->getStatusCode()===200){$data=json_decode((string)$response->getBody(),true);if(is_array($data)&&!empty($data['productos'])){try{$missing=[];foreach($data['productos'] as $i=>$item)if(strpos($item['Descripcion']??'','Características técnicas:')===false)$missing[$i]=$item;if($missing){$enriched=aliProformaKnowledge($app->getContainer()['db'],['productos'=>array_values($missing)]);foreach(array_keys($missing) as $j=>$i)$data['productos'][$i]=$enriched['productos'][$j];return $response->withJson($data);}}catch(Throwable $e){error_log('[PRODUCT SPECS] '.get_class($e));return $response;}}}
 return $response;
});}
