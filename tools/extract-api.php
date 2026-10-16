<?php
$s=file_get_contents($argv[1]);$t=token_get_all($s);$out=['functions'=>[],'routes'=>[]];$offset=0;$n=count($t);
for($i=0;$i<$n;$i++){
 $v=is_array($t[$i])?$t[$i][1]:$t[$i];
 if(is_array($t[$i])&&$t[$i][0]===T_FUNCTION){
  $j=$i+1;while($j<$n&&is_array($t[$j])&&$t[$j][0]===T_WHITESPACE)$j++;
  if(is_array($t[$j])&&$t[$j][0]===T_STRING){$name=$t[$j][1];$code='';$depth=0;$started=false;for($k=$i;$k<$n;$k++){$x=is_array($t[$k])?$t[$k][1]:$t[$k];$code.=$x;if(!is_array($t[$k])){if($x==='{'){$depth++;$started=true;}if($x==='}')$depth--;if($started&&$depth===0)break;}}$out['functions'][$name]=$code;}
 }
 if(is_array($t[$i])&&$t[$i][0]===T_VARIABLE&&$v==='$app'){
  $prefix='';for($j=$i;$j<min($n,$i+8);$j++)$prefix.=is_array($t[$j])?$t[$j][1]:$t[$j];
  if(preg_match('/^\$app->(get|post|put|patch|delete)\s*\(\s*[\x27"]([^\x27"]+)/',$prefix,$m)){$code='';$depth=0;for($k=$i;$k<$n;$k++){$x=is_array($t[$k])?$t[$k][1]:$t[$k];$code.=$x;if(!is_array($t[$k])){if(in_array($x,['(','{','[']))$depth++;if(in_array($x,[')','}',']']))$depth--;if($x===';'&&$depth===0)break;}}$out['routes'][$m[1].' '.$m[2]]=$code;}
 }
 $offset+=strlen($v);
}
echo json_encode($out,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
