<?php
function qpAnnualPlanPresentation($value){
 $text=(string)$value;$paragraphs=preg_split('/\n\s*\n/',str_replace("\r\n","\n",$text));$heading='PLAN ANUAL INTEGRAL DE FACTURACIÓN ELECTRÓNICA';
 foreach($paragraphs as $index=>$paragraph){
  if(strpos(trim($paragraph),$heading."\n")!==0)continue;
  $lines=explode("\n",trim($paragraph));$plan=['price'=>'','terms'=>[],'services'=>[]];
  foreach(array_slice($lines,1) as $line){
   if(strpos($line,'Valor anual: ')===0){$plan['price']=substr($line,13);continue;}
   if(strpos($line,'• ')===0){$parts=explode(' — ',substr($line,strlen('• ')),2);$plan['services'][]=['name'=>$parts[0],'description'=>$parts[1]??''];continue;}
   $plan['terms'][]=$line;
  }
  if($plan['price']==='')continue;unset($paragraphs[$index]);return ['text'=>trim(implode("\n\n",$paragraphs)),'plan'=>$plan];
 }
 return ['text'=>$text,'plan'=>null];
}
function qpAnnualPlanHtml($plan){
 if(!$plan)return '';
 $html='<div class="annual-plan"><table class="annual-head"><tr><td><span>CONTINUIDAD PARA TU NEGOCIO</span><h2>Plan Anual Integral</h2><p>Facturación electrónica, infraestructura y acompañamiento</p></td><td class="annual-price"><small>UN SOLO VALOR ANUAL</small><strong>'.qpEscape($plan['price']).'</strong><p>Primer año incluido en la licencia</p></td></tr></table><table class="annual-services">';
 foreach(array_chunk($plan['services'],2) as $row){$html.='<tr>';foreach($row as $service)$html.='<td><b>✓ '.qpEscape($service['name']).'</b><p>'.qpEscape($service['description']).'</p></td>';if(count($row)===1)$html.='<td></td>';$html.='</tr>';}
 $html.='</table><div class="annual-terms">';foreach($plan['terms'] as $term)$html.='<p>'.qpEscape($term).'</p>';return $html.'</div></div>';
}
