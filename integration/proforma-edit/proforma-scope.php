<?php
function qpScopeSentences($text){return array_values(array_filter(array_map('trim',explode("\n",preg_replace('/([.!?])\s+(?=[A-ZÁÉÍÓÚÑ¿¡])/u',"$1\n",$text)))));}
function qpTextBlocks($value,$kind='scope'){
 $blocks=[];$add=function($label,$text,$type='text',$items=[])use(&$blocks){$blocks[]=['label'=>$label,'text'=>$text,'type'=>$type,'items'=>$items];};
 foreach(preg_split('/\n\s*\n/',str_replace("\r\n","\n",(string)$value)) as $paragraph){$paragraph=trim($paragraph);if($paragraph==='')continue;$lines=array_values(array_filter(array_map('trim',explode("\n",$paragraph))));$heading=$lines[0];
  if(count($lines)>1&&preg_match('/^(SERVICIOS INCLUIDOS|ACOMPAÑAMIENTO E IMPLEMENTACIÓN|SOPORTE Y ATENCIÓN)$/iu',$heading)){$add($heading,'','services',array_map(function($line){return preg_replace('/^[•\-*]\s*/u','',$line);},array_slice($lines,1)));continue;}
  if($kind!=='scope'){$hasList=false;foreach($lines as $line)if(preg_match('/^[•\-*]|^\d+[.)]\s/u',$line))$hasList=true;$items=$hasList?array_map(function($line){return preg_replace('/^(?:[•\-*]|\d+[.)])\s*/u','',$line);},$lines):qpScopeSentences($paragraph);$add($kind==='delivery'?'PLAN DE ENTREGA':'CONDICIONES COMERCIALES','','list',$items);continue;}
  foreach(qpScopeSentences($paragraph) as $part){
   if(preg_match('/^Propuesta (?:para|de sistemas (?:de|para))\s+/iu',$part))$add('PROPUESTA',preg_replace('/^Propuesta (?:para|de sistemas (?:de|para))\s+/iu','PROPUESTA DE SISTEMAS DE ',$part),'intro');
   elseif(preg_match('/^(?:El objetivo(?:\s+es)?|Objetivo\s*:)/iu',$part))$add('OBJETIVO DEL PROYECTO',$part,'objective');
   elseif(preg_match('/^Soluci[oó]n\s*:/iu',$part))$add('SOLUCIÓN PROPUESTA',$part,'solution');
   elseif(preg_match('/^Necesidades del cliente\s*:/iu',$part))$add('NECESIDADES DEL CLIENTE',$part,'needs');
   elseif(preg_match('/^Para ello\b/iu',$part)){$prefix=[];$matched=preg_match('/^(Para ello se integran los m[oó]dulos de)\s+/iu',$part,$prefix);$text=$matched?substr($part,strlen($prefix[0])):$part;$items=preg_split('/,\s+(?:y\s+)?(?=(?:Punto de venta|Inventario(?: y stock)?|Cat[aá]logo de productos(?: y servicios)?|Compras|Caja y bancos|Clientes|Reportes de ventas|Usuarios y accesos)\b)/iu',$text);$add('OPERACIÓN DEL NEGOCIO',count($items)>1?($matched?$prefix[1]:''):$part,count($items)>1?'operations':'text',count($items)>1?$items:[]);}
   elseif(preg_match('/^Adem[aá]s\b/iu',$part))$add('COBERTURA DE LA SOLUCIÓN',$part,'coverage');
   else $add('DETALLE DEL ALCANCE',$part);
  }
 }
 return $blocks;
}
function qpContextHtml($value,$kind='scope'){
 $html='<div class="context">';foreach(qpTextBlocks($value,$kind) as $block){$html.='<div class="context-block context-'.qpEscape($block['type']).'"><h3>'.qpEscape($block['label']).'</h3>';if($block['text']!=='')$html.='<p>'.nl2br(qpEscape($block['text'])).'</p>';
  if($block['items']){if(in_array($block['type'],['operations','services'],true)){$single=count($block['items'])===1;$html.='<table class="context-items'.($single?' context-single':'').'">';foreach(array_chunk($block['items'],2) as $row){$html.='<tr>';foreach($row as $item)$html.='<td><span>✓</span> '.nl2br(qpEscape($item)).'</td>';if(count($row)===1&&!$single)$html.='<td></td>';$html.='</tr>';}$html.='</table>';}else{$html.='<ol>';foreach($block['items'] as $item)$html.='<li>'.nl2br(qpEscape($item)).'</li>';$html.='</ol>';}}
  $html.='</div>';
 }return $html.'</div>';
}
