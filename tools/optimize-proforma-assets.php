<?php
if(PHP_SAPI !== 'cli')exit(1);
$root=dirname(__DIR__);
$items=[['C:/xampp/htdocs/boticav1/resources/images/logo.png',$root.'/static/images/proforma-logo-color.webp',640]];
if(isset($argv[1]))$items[]=[$argv[1],$root.'/static/images/proforma-header-spaced.webp',1800];
foreach($items as [$source,$target,$maxWidth]){
 $raw=file_get_contents($source);$input=imagecreatefromstring($raw);if(!$input)throw new RuntimeException('Invalid asset');
 $w=imagesx($input);$h=imagesy($input);$outW=min($w,$maxWidth);$outH=(int)round($h*$outW/$w);
 $output=imagecreatetruecolor($outW,$outH);$white=imagecolorallocate($output,255,255,255);imagefill($output,0,0,$white);
 imagecopyresampled($output,$input,0,0,0,0,$outW,$outH,$w,$h);
 if(!imagewebp($output,$target,90))throw new RuntimeException('Failed encoding');
 echo basename($target).': '.strlen($raw).' -> '.filesize($target).' bytes'.PHP_EOL;
 imagedestroy($output);imagedestroy($input);
}
