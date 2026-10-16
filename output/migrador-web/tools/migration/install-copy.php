<?php
// Deliberately CLI only; no automatic installation during an HTTP request.
if(PHP_SAPI!=='cli')exit(1);
require __DIR__.'/../../api/migration/runtime.php';
try{$c=neuroMigrationConfig();if(($argv[1]??'')!==('--copy='.$c['target']['database']))throw new RuntimeException('Especifique --copy=nombre_copia');neuroMigrationEngine($c)->install();echo "Auditoria instalada exclusivamente en copia aislada.\n";}catch(Throwable $e){fwrite(STDERR,"Instalacion bloqueada. Verifique perfil privado, copia y respaldo.\n");exit(1);}
