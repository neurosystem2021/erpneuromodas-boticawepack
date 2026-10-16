<?php
// Scheduler entry point, disabled until an operator deliberately installs/configures it.
if(PHP_SAPI!=='cli')exit(1);
$o=getopt('',['app:','company:','user:','from:','to:']);foreach(['app','company','user','from','to'] as $key)if(empty($o[$key])){fwrite(STDERR,"Usage: php accounting-sync.php --app=/private/erp --company=ID --user=ERP_USER --from=YYYY-MM-DD --to=YYYY-MM-DD\n");exit(2);}
require rtrim($o['app'],'/\\').'/config/database.php';require __DIR__.'/api/accounting-core.php';require __DIR__.'/api/accounting-sources.php';
try{$s=new CoService(appPdoConnection(),$o['user'],false);$s->permit((int)$o['company'],'draft');$result=(new CoSources($s))->capture((int)$o['company'],['from'=>$o['from'],'to'=>$o['to']]);echo coJson($result).PHP_EOL;}catch(Throwable $e){fwrite(STDERR,$e->getMessage().PHP_EOL);exit(1);}
