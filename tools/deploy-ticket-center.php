<?php
if(PHP_SAPI!=='cli')exit;
$options=getopt('',['root:','backup:']);$target=realpath($options['root']??'');$backup=$options['backup']??'';$source=dirname(__DIR__);
if(!$target||!$backup||file_exists($backup))throw new RuntimeException('Use --root=/ERP --backup=/ruta/privada/nueva');
$parent=realpath(dirname($backup));if(!$parent||strpos(strtolower(str_replace('\\','/',$parent)).'/',strtolower(str_replace('\\','/',$target)).'/')===0)throw new RuntimeException('El respaldo debe quedar fuera del ERP público.');
$files=[];foreach(['diario-api.php','diario-ticket-report.php','diario-avances.php'] as $file)$files['api/'.$file]=$source.'/integration/diario/'.$file;
foreach(['support-api.php','support-service.php','support-report.php','support-performance.php','support-satisfaction.php'] as $file)$files['api/'.$file]=$source.'/integration/helpdesk/'.$file;
$files['tools/support/CONTROL_EQUIPO.txt']=$source.'/integration/helpdesk/CONTROL_EQUIPO.txt';
foreach(['dist'=>'nuevo','output/support-ui'=>'support-ui'] as $folder=>$destination){
 $index=$source.'/'.$folder.'/index.html';if(!is_file($index))throw new RuntimeException('Compile primero '.$folder);$files[$destination.'/index.html']=$index;
 preg_match_all('~(?:src|href)=["\x27]?([^"\x27\s>]+)~',file_get_contents($index),$matches);
 foreach($matches[1] as $url){$relative=preg_replace('~^\./~','',explode('?',$url)[0]);if(strpos($relative,'static/')!==0||strpos($relative,'..')!==false)throw new RuntimeException('Referencia inesperada en el bundle');$file=$source.'/'.$folder.'/'.$relative;if(!is_file($file))throw new RuntimeException('Recurso ausente: '.$relative);$files[$destination.'/'.$relative]=$file;if(is_file($file.'.map'))$files[$destination.'/'.$relative.'.map']=$file.'.map';}
}
foreach($files as $file)if(!is_file($file))throw new RuntimeException('Paquete incompleto.');
if(!mkdir($backup,0700,true))throw new RuntimeException('No se pudo crear respaldo privado.');
// Write assets/dependencies before publishing the indexes and API loaders.
uksort($files,function($a,$b){$last=function($s){return in_array($s,['nuevo/index.html','support-ui/index.html','api/support-service.php','api/support-api.php','api/diario-api.php'],true)?1:0;};return $last($a)<=>$last($b);});
foreach($files as $relative=>$file){$dest=$target.'/'.$relative;if(is_file($dest)&&hash_file('sha256',$dest)===hash_file('sha256',$file))continue;if(is_file($dest)){$saved=$backup.'/'.$relative;if(!is_dir(dirname($saved)))mkdir(dirname($saved),0700,true);if(!copy($dest,$saved))throw new RuntimeException('No se pudo respaldar '.$relative);}if(!is_dir(dirname($dest)))mkdir(dirname($dest),0755,true);if(!copy($file,$dest.'.ticket-tmp')||!rename($dest.'.ticket-tmp',$dest))throw new RuntimeException('No se pudo publicar '.$relative);}
echo "Tablero, reportes y evaluación instalados. Sin migraciones ni cambios de datos. Respaldo privado: ".$backup."\n";
