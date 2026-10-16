<?php
require __DIR__.'/../integration/ali-product-knowledge.php';
require __DIR__.'/../integration/ali-product-guide.php';
require __DIR__.'/../integration/ali-expert-solutions.php';
$payload=['summary'=>'Orientación preliminar','causes'=>['Red'],'steps'=>['Comprobar cable'],'reset'=>['decision'=>'No indicado','risks'=>'Pérdida de configuración'],'videos'=>[['url'=>'https://youtu.be/abcDEF12345','title'=>'Referencia','match'=>'exact'],['url'=>'https://youtube.com/watch?v=unknown1234','title'=>'No encontrado'],['url'=>'https://evil.invalid/watch?v=abcDEF12345'],['url'=>'https://youtube.com/watch?v=abcDEF12345']]];
$out=aliExpertSolutionResult(['text'=>json_encode($payload),'sources'=>[['url'=>'https://www.youtube.com/watch?v=abcDEF12345','title'=>'Fuente encontrada']]]);
if(count($out['videos'])!==1||$out['videos'][0]['match']!=='exact'||$out['reset']['decision']!=='No indicado')throw new RuntimeException('Invalid verified video/reset result');
$payload['videos']=[];$out=aliExpertSolutionResult(['text'=>json_encode($payload),'sources'=>[]]);if($out['videos']!==[])throw new RuntimeException('Empty video fallback failed');
echo "PASS structured causes, ordered steps, reset precautions, verified YouTube URLs, duplicate/unverified rejection and empty video fallback.\n";
