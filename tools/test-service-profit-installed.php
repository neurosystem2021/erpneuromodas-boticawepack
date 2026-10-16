<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';
require 'C:/xampp/htdocs/boticav1/api/service-profit-report.php';
$db=appPdoConnection();$f=['fechaInicio'=>'2026-10-01','fechaFin'=>'2026-10-16','usuarioReg'=>'','ServicioFlag'=>'1','MercaderiaFlag'=>'0'];
$expected=serviceProfitReport($db,$f);
$h=curl_init('http://localhost/api/index.php/reporte/utlidadbruta/usuario?'.http_build_query($f));curl_setopt_array($h,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20]);$raw=curl_exec($h);$code=curl_getinfo($h,CURLINFO_HTTP_CODE);curl_close($h);$rows=json_decode($raw,true);
if($code!==200 || !is_array($rows) || count($rows)!==count($expected))throw new RuntimeException('Live endpoint failed: '.$code);
if(abs(array_sum(array_column($rows,'UtilidadBruta'))-array_sum(array_column($expected,'UtilidadBruta')))>0.001)throw new RuntimeException('Live totals mismatch');
echo 'PASS installed HTTP report: '.count($rows).' service detail groups, totals match database. Read only.'.PHP_EOL;
