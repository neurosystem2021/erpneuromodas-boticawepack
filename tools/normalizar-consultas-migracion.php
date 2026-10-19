<?php
if(PHP_SAPI!=='cli')exit(1);
$o=getopt('',['root:','apply','backup:']);$root=realpath($o['root']??'');if(!$root||!is_dir($root)||strlen($root)<4)throw new RuntimeException('Indica --root=/ruta/del/ERP');
$root=rtrim(str_replace('\\','/',$root),'/');$map=json_decode(file_get_contents(__DIR__.'/nombres-tablas.json'),true);if(!$map)throw new RuntimeException('Falta nombres-tablas.json');
$csv=fopen(__DIR__.'/referencias-php-a-revisar.csv','r');fgetcsv($csv);$paths=[];while($row=fgetcsv($csv)){if(count($row)>=4)$paths[str_replace('\\','/',$row[0])]=true;}fclose($csv);
$prepared=[];$total=0;
foreach(array_keys($paths) as $relative){if(strpos($relative,'..')!==false||substr($relative,-4)!=='.php')throw new RuntimeException('Ruta no válida.');$path=realpath($root.'/'.$relative);if(!$path||strpos(str_replace('\\','/',$path),$root.'/')!==0)throw new RuntimeException('Archivo no disponible dentro del ERP: '.$relative);$before=file_get_contents($path);$count=0;
 $after=preg_replace_callback('~(\b(?:FROM|JOIN|INTO|UPDATE|REFERENCES|TABLE(?:\s+IF\s+NOT\s+EXISTS)?)\s+`?)([A-Za-z][A-Za-z0-9_]*)~i',function($m)use($map,&$count){$name=$map[strtolower($m[2])]??$m[2];if($name!==$m[2])$count++;return $m[1].$name;},$before);
 if($count){$prepared[$relative]=['path'=>$path,'before'=>$before,'after'=>$after,'count'=>$count];$total+=$count;echo $relative.': '.$count.' referencias'.PHP_EOL;}
}
echo count($prepared).' archivos; '.$total.' referencias.'.PHP_EOL;
if(!isset($o['apply'])){echo 'Solo revisión. No se modificó ningún archivo. Para aplicar, añade --apply --backup=/ruta/privada/nueva'.PHP_EOL;exit;}
if(!$prepared){echo 'Las consultas ya coinciden con el esquema.'.PHP_EOL;exit;}
if(empty($o['backup'])||file_exists($o['backup']))throw new RuntimeException('Indica una carpeta de respaldo nueva con --backup.');
if(!mkdir($o['backup'],0700,true))throw new RuntimeException('No se pudo crear el respaldo.');$backup=str_replace('\\','/',realpath($o['backup']));if($backup===$root||strpos($backup,$root.'/')===0)throw new RuntimeException('El respaldo debe quedar fuera de la carpeta del ERP.');
$report=[];
// Back up and lint every candidate before changing the running application's files.
foreach($prepared as $relative=>$item){$dest=$backup.'/'.$relative;if(!is_dir(dirname($dest)))mkdir(dirname($dest),0700,true);file_put_contents($dest,$item['before']);$candidate=$dest.'.revisado';file_put_contents($candidate,$item['after']);$process=proc_open([PHP_BINARY,'-l',$candidate],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,null,null,['bypass_shell'=>true]);fclose($pipes[0]);stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);if(proc_close($process)!==0)throw new RuntimeException('No pasa la sintaxis PHP: '.$relative.' '.$error);$report[$relative]=['before'=>hash('sha256',$item['before']),'after'=>hash('sha256',$item['after']),'references'=>$item['count']];}
foreach($prepared as $relative=>$item){if(!hash_equals($report[$relative]['before'],hash_file('sha256',$item['path'])))throw new RuntimeException('El archivo cambió durante la revisión: '.$relative);if(file_put_contents($item['path'],$item['after'])===false)throw new RuntimeException('No se pudo actualizar: '.$relative);if(!hash_equals($report[$relative]['after'],hash_file('sha256',$item['path'])))throw new RuntimeException('Checksum no coincide: '.$relative);}
file_put_contents($backup.'/cambios.json',json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));echo 'Consultas ajustadas. Respaldo: '.$backup.PHP_EOL;
