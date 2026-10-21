<?php
if(PHP_SAPI!=='cli')exit;require 'C:/xampp/htdocs/boticav1/config/database.php';
function resetProcess($args,$input,$output,$log){$pipes=[];$p=proc_open($args,[0=>['file',$input,'rb'],1=>['file',$output,'wb'],2=>['file',$log,'ab']],$pipes,null,null,['bypass_shell'=>true]);if(!is_resource($p)||proc_close($p)!==0)throw new RuntimeException('Falló respaldo/restauración; consultar registro privado.');}
function resetFingerprint($db,$tables){$out=[];foreach($tables as $t){$hashes=[];$q=$db->query('SELECT * FROM `'.$t.'`');while($r=$q->fetch(PDO::FETCH_ASSOC))$hashes[]=hash('sha256',serialize($r));sort($hashes);$out[$t]=['rows'=>count($hashes),'sha256'=>hash('sha256',implode("\n",$hashes))];}return $out;}
function resetInsert($db,$table,$data){$keys=array_keys($data);$s=$db->prepare('INSERT INTO `'.$table.'` (`'.implode('`,`',$keys).'`) VALUES('.implode(',',array_fill(0,count($keys),'?')).')');$s->execute(array_values($data));return (int)$db->lastInsertId();}


$report=json_decode(file_get_contents(__DIR__.'/../../output/concretera/respaldo-previo-limpieza.json'),true);
if(!$report['backupRestoreVerified']||$report['targetModified']||basename($report['backup'])!=='wuanukomix-respaldo-previo-20261019-155517')throw new RuntimeException('No coincide la prueba propietaria.');
$cfg=appDatabaseConfig();if($cfg['dbname']!=='fact_wuanukomix')throw new RuntimeException('Conexión inesperada.');$cfg['dbname']='0';
$db=new PDO(appDatabaseDsn($cfg),$cfg['user'],$cfg['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$tables=$db->query("SHOW FULL TABLES WHERE Table_type='BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);sort($tables);
if(resetFingerprint($db,$tables)!==$report['fingerprints'])throw new RuntimeException('La base temporal no coincide con el respaldo de esta prueba. No se retira.');
$db->exec('DROP DATABASE `0`');echo 'PASS base temporal de verificación retirada; no se modificó Wuanuko Mix.'.PHP_EOL;
