<?php
if(PHP_SAPI!=='cli')exit(1);$root=dirname(__DIR__);$in=imagecreatefromwebp($root.'/static/images/proforma-logo-color.webp');imagepng($in,$root.'/static/images/proforma-logo-color.png',9);imagedestroy($in);echo 'PDF logo prepared';
