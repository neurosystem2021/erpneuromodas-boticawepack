<?php
require __DIR__.'/../../integration/migration/Engine.php';
if(PHP_SAPI!=='cli'||!isset($argv[1]))exit(1);
$snapshot=json_decode(file_get_contents($argv[1]),true,512,JSON_THROW_ON_ERROR);
$engine=new NeuroMigration(new PDO('sqlite::memory:'),$snapshot,function(){throw new RuntimeException('No writes permitted');});
$plan=$engine->simulate(['catalog','sales','purchases','related']);
foreach(['Ve_DocVenta'=>4638,'Ve_DocVentaDet'=>7131,'Ve_DocVentaMetodoPagoDet'=>4845,'Lo_Movimiento'=>867] as $table=>$count)if(($plan['counts'][$table]??0)!==$count)throw new RuntimeException('Count mismatch');
if(($plan['issues']['relation:Ve_DocVentaNotaCredito.idDocVenta']['count']??0)!==23)throw new RuntimeException('Credit-note issue not detected');
echo json_encode(['simulation_only'=>true,'rows'=>$plan['total'],'entities'=>count($plan['counts']),'issue_groups'=>count($plan['issues']),'sales'=>4638,'details'=>7131,'payments'=>4845]),PHP_EOL;
