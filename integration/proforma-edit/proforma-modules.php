<?php
function qpSystemTitle($scope,$products=[]){
 $text=(string)$scope;$business='';if(preg_match('/^Propuesta (?:para|de sistemas de)\s+([^\r\n]+)/imu',$text,$match))$business=rtrim(trim($match[1]),'.');$context=$business?:implode(' ',array_column($products,'Producto'));
 $description=trim(preg_replace('/^sistemas?\s+(?:a medida\s+)?(?:de|para)\s+/iu','',$context));
 return 'PROPUESTA DE SISTEMAS DE '.mb_strtoupper($description?:'GESTIÓN EMPRESARIAL','UTF-8');
}
function qpModuleNormalize($value){
 $value=mb_strtolower(trim((string)$value),'UTF-8');$value=strtr($value,['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n']);return rtrim(preg_replace('/\s+/u',' ',$value),'.');
}
function qpModulePresentation($scope,$guidePath=null){
 $text=(string)$scope;$result=['text'=>$text,'modules'=>[]];
 if(!preg_match('/M[oó]dulos incluidos:\s*/iu',$text,$match,PREG_OFFSET_CAPTURE))return $result;
 $offset=$match[0][1];$tail=substr($text,$offset+strlen($match[0][0]));$parts=preg_split('/\r?\n\s*\r?\n/',$tail,2);$list=$parts[0];$rest=isset($parts[1])?"\n\n".$parts[1]:'';
 $path=$guidePath?:dirname(__DIR__).'/nuevo/static/data/proforma-module-guide.json';if(!$guidePath&&!is_file($path))$path=dirname(__DIR__,2).'/static/data/proforma-module-guide.json';$guide=is_file($path)?json_decode(file_get_contents($path),true):null;
 // Keep original scope when the presentation asset is unavailable.
 if(!is_array($guide)||empty($guide['modules']))return $result;
 $seen=[];foreach(preg_split('/[;\n]+/u',$list) as $name){$name=rtrim(trim($name),'.');if($name==='')continue;$entry=null;foreach($guide['modules'] as $module)foreach($module['aliases'] as $alias)if(qpModuleNormalize($alias)===qpModuleNormalize($name)){$entry=$module;break 2;}
  $key=$entry?$entry['key']:qpModuleNormalize($name);if(isset($seen[$key]))continue;$seen[$key]=true;
  $module=$entry?:['key'=>$key,'name'=>$name,'description'=>'Función incluida en esta propuesta, con configuración según el alcance acordado.','benefit'=>'Su detalle se valida durante la definición de la solución.','icon'=>'settings'];
  $module['paths']=$guide['icons'][$module['icon']]??$guide['icons']['settings'];$result['modules'][]=$module;
 }
 if($result['modules'])$result['text']=trim(substr($text,0,$offset).$rest);return $result;
}
function qpModuleCards($modules){
 if(!$modules)return '';$html='<h2>Módulos y submódulos incluidos en tu solución</h2><p class="module-intro">Qué permite hacer cada función y cómo acompaña la operación de tu negocio.</p><table class="module-grid">';
 foreach(array_chunk($modules,2) as $pair){$html.='<tr>';foreach($pair as $module){$paths='';foreach($module['paths'] as $path)$paths.='<path d="'.htmlspecialchars($path,ENT_QUOTES,'UTF-8').'"/>';
  $svg='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#b3243d" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">'.$paths.'</svg>';
  $features='';if(!empty($module['features'])){$features='<ul class="module-features">';foreach($module['features'] as $feature)$features.='<li>'.qpEscape($feature).'</li>';$features.='</ul>';}
  $level=empty($module['parent'])?'MÓDULO':'SUBMÓDULO · '.$module['parent'];
  $example=empty($module['example'])?'':'<p class="module-example"><b>En tu negocio:</b> '.qpEscape($module['example']).'</p>';
  $html.='<td><div class="module-card"><div class="module-icon" style="position:absolute;left:7px;top:7px;width:25px"><img src="data:image/svg+xml;base64,'.base64_encode($svg).'" width="21" height="21"></div><div style="margin-left:30px"><small class="module-level">'.qpEscape($level).'</small><h3>'.qpEscape($module['name']).'</h3><p>'.qpEscape($module['description']).'</p>'.$features.$example.'<p class="module-benefit">'.qpEscape($module['benefit']).'</p></div></div></td>';
 }if(count($pair)===1)$html.='<td></td>';$html.='</tr>';}
 return $html.'</table>';
}
