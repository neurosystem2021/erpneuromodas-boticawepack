<?php
// CLI operator must have access to the private profile and the isolated destination only.
if(PHP_SAPI!=='cli')exit(1);
require __DIR__.'/../../integration/migration/runtime.php';
try{
 $c=neuroMigrationConfig();$engine=neuroMigrationEngine($c);$actor=$c['operator']??'cli-copy-operator';$command=$argv[1]??'history';$id=$argv[2]??'';
 if($command==='simulate'){$plan=$engine->simulate(['catalog','sales','purchases','related']);$id=$engine->create($plan,$actor);$out=$engine->status($id);}
 elseif($command==='history')$out=$engine->history();
 elseif($command==='status')$out=$engine->status($id);
 elseif($command==='report')$out=$engine->report($id);
 elseif($command==='approve'){$engine->approve($id,$actor,$argv[3]??'');$out=$engine->status($id);}
 elseif($command==='batch')$out=$engine->batch($id,$actor,(int)($argv[3]??0));
 elseif($command==='rollback')$out=$engine->rollback($id,$actor);
 else throw new RuntimeException('Invalid command');
 echo NeuroMigration::json($out),PHP_EOL;
}catch(Throwable $e){fwrite(STDERR,"Operacion bloqueada; consulte perfil privado e incidencias en la copia. No se muestran detalles de conexion.\n");exit(1);}
