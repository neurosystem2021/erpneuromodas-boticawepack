<?php
require __DIR__.'/../../integration/migration/runtime.php';
function reject($c){$path=tempnam(sys_get_temp_dir(),'mig_guard_');file_put_contents($path,json_encode($c));putenv('NEURO_MIGRATION_CONFIG='.$path);try{neuroMigrationConfig();throw new LogicException('Unsafe profile accepted');}catch(RuntimeException $e){}finally{unlink($path);putenv('NEURO_MIGRATION_CONFIG');}}
reject(['mode'=>'production','target'=>['database'=>'nst']]);
reject(['mode'=>'isolated_copy','forbidden_databases'=>[],'target'=>['database'=>'nst']]);
reject(['mode'=>'isolated_copy','target'=>['database'=>'fact_nst']]);
reject(['mode'=>'isolated_copy','target'=>['database'=>'copy;DROP']]);
echo "PASS runtime rejects production, source and invalid destinations. No database connection or writes.\n";
