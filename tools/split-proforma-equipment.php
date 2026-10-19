<?php
if(PHP_SAPI!=='cli')exit(1);
$input=imagecreatefrompng($argv[1]);$root=dirname(__DIR__).'/static/images/proforma-equipment';if(!is_dir($root))mkdir($root,0777,true);
$groups=[['gaming',0,395],['laptop',402,699],['printer',700,1053],['projector',1060,1334],['pos',1335,1693],['cameras',1694,1988],['network',1989,2172]];
foreach($groups as [$name,$left,$right]){
 $minX=$right;$maxX=$left;$minY=imagesy($input);$maxY=0;
 for($x=$left;$x<min($right,imagesx($input));$x++)for($y=0;$y<imagesy($input);$y++){$rgb=imagecolorat($input,$x,$y);if((($rgb>>16)&255)<218||(($rgb>>8)&255)<218||($rgb&255)<218){$minX=min($minX,$x);$maxX=max($maxX,$x);$minY=min($minY,$y);$maxY=max($maxY,$y);}}
 $minX=max($left,$minX-3);$minY=max(0,$minY-3);$maxX=min($right-1,$maxX+3);$maxY=min(imagesy($input)-1,$maxY+3);
 $w=$maxX-$minX+1;$h=$maxY-$minY+1;$scale=min(1,500/$w,350/$h);$out=imagecreatetruecolor((int)round($w*$scale),(int)round($h*$scale));$white=imagecolorallocate($out,255,255,255);imagefill($out,0,0,$white);imagecopyresampled($out,$input,0,0,$minX,$minY,imagesx($out),imagesy($out),$w,$h);imagewebp($out,$root.'/'.$name.'.webp',90);imagedestroy($out);echo $name.' '.filesize($root.'/'.$name.'.webp').PHP_EOL;
}
imagedestroy($input);
