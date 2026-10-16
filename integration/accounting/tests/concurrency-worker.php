<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require 'C:/xampp/htdocs/boticav1/config/database.php';require __DIR__.'/../api/accounting-core.php';
$schema=$argv[1]??'';if(!preg_match('/^co_test_[a-f0-9]{16}$/',$schema))exit(2);$company=(int)($argv[2]??0);$worker=(int)($argv[3]??0);$db=appPdoConnection();$db->exec('USE `'.$schema.'`');$maker=new CoService($db,'mallory');$reviewer=new CoService($db,'superadmin',true);
for($i=0;$i<3;$i++){$entry=$maker->draft($company,['date'=>'2026-11-01','memo'=>'Concurrent test','currency'=>'PEN','rate'=>'1','sourceType'=>'manual','sourceId'=>'race-'.$worker.'-'.$i,'lines'=>[['account'=>'104','debit'=>'1','credit'=>'0','cashFlow'=>'financing'],['account'=>'501','debit'=>'0','credit'=>'1']]]);foreach([1=>'review',2=>'approve',3=>'post'] as $version=>$action)$reviewer->transition($company,['id'=>$entry['id'],'version'=>$version,'action'=>$action]);}
echo "ok\n";
