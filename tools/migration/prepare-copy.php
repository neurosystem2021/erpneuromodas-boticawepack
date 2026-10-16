<?php
// Explicitly authorized test-copy preparation. Never REPAIR/INSERT/ALTER the source database.
if(PHP_SAPI!=='cli')exit(1);
require 'C:/xampp/htdocs/boticav1/config/database.php';
$root='C:/xampp/neuro-private/migration';
$stamp=(new DateTimeImmutable('now',new DateTimeZone('America/Lima')))->format('Ymd_His');
$copyName='neuro_mig_test_'.$stamp.'_'.bin2hex(random_bytes(3));
$private=$root.'/'.$copyName;$locked=false;$db=null;$stage='preflight';
try{
 if(($argv[1]??'')!=='--prepare-isolated-copy')throw new RuntimeException('Explicit copy flag required');
 $cfg=appDatabaseConfig();if($cfg['dbname']!=='nst')throw new RuntimeException('Unexpected source');
 if(!is_dir($private)&&!mkdir($private,0700,true))throw new RuntimeException('Private directory unavailable');
 $db=appPdoConnection();$db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES,true);$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
 $db->exec('SET SESSION lock_wait_timeout=5');
 $tables=$db->query('SELECT TABLE_NAME,TABLE_TYPE,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME')->fetchAll(PDO::FETCH_ASSOC);
 $dataDir=rtrim(str_replace('\\','/',$db->query('SELECT @@datadir')->fetchColumn()),'/');
 $damaged=['gen_moneda','lo_ordencompra','lo_ordencompradet','tblkardexvalor'];
 foreach($damaged as $name)foreach(['frm','MYD','MYI'] as $ext)if(!is_file($dataDir.'/nst/'.$name.'.'.$ext))throw new RuntimeException('Physical source file missing');
 $backupPath=$private.'/target-before.sql';$out=fopen($backupPath,'x');if(!$out)throw new RuntimeException('Backup unavailable');
 fwrite($out,"-- Hybrid backup: damaged MyISAM tables are preserved separately. No USE, DROP or production writes.\nSET NAMES utf8mb4;\n");
 $schemas=[];$counts=[];$raw=[];$views=[];$checksums=[];$selects=[];$deadline=microtime(true)+45;
 try{
  $stage='consistent_backup';$db->exec('FLUSH TABLES WITH READ LOCK');$locked=true;
  foreach($tables as $table){
   if(microtime(true)>$deadline)throw new RuntimeException('Read-lock budget exceeded');
   $name=$table['TABLE_NAME'];if(!preg_match('/^[A-Za-z0-9_]+$/',$name))throw new RuntimeException('Invalid table name');
   if(in_array(strtolower($name),$damaged,true)){
    foreach(['frm','MYD','MYI'] as $ext){$destination=$private.'/'.$name.'.'.$ext;if(!copy($dataDir.'/nst/'.$name.'.'.$ext,$destination))throw new RuntimeException('Physical backup failed');$raw[]=['backup_file'=>$destination,'sha256'=>hash_file('sha256',$destination)];}
    continue;
   }
   $ddl=$db->query('SHOW CREATE TABLE `'.$name.'`')->fetch(PDO::FETCH_NUM)[1];
   if($table['TABLE_TYPE']==='VIEW'){$views[$name]=$ddl;continue;}
   $schemas[$name]=$ddl;$counts[$name]=0;
   fwrite($out,$ddl.";\n");
   $columnsInfo=$db->query('SHOW COLUMNS FROM `'.$name.'`')->fetchAll(PDO::FETCH_ASSOC);$selection=implode(',',array_map(fn($col)=>strpos(strtolower($col['Type']),'bit(')===0?'CAST(`'.$col['Field'].'` AS UNSIGNED) AS `'.$col['Field'].'`':'`'.$col['Field'].'`',$columnsInfo));$selects[$name]='SELECT '.$selection.' FROM `'.$name.'`';$rowHashes=[];$statement=$db->query($selects[$name]);
   while($row=$statement->fetch(PDO::FETCH_ASSOC)){
    $columns=implode(',',array_map(fn($col)=>'`'.$col.'`',array_keys($row)));
    // Hex literals preserve byte-level BIT, binary and textual contents without SQL escape ambiguity.
    $bitNames=array_column(array_filter($columnsInfo,fn($col)=>strpos(strtolower($col['Type']),'bit(')===0),'Field');$literals=[];foreach($row as $column=>$value){if($value===null)$literals[]='NULL';elseif(in_array($column,$bitNames,true)){if(!preg_match('/^[0-9]+$/',(string)$value))throw new RuntimeException('Invalid BIT value');$literals[]=(string)$value;}else $literals[]="CONVERT(X'".bin2hex((string)$value)."' USING utf8mb4)";}$values=implode(',',$literals);
    fwrite($out,'INSERT INTO `'.$name.'` ('.$columns.') VALUES ('.$values.");\n");$counts[$name]++;$rowHashes[]=hash('sha256',serialize($row));
   }sort($rowHashes,SORT_STRING);$checksums[$name]=hash('sha256',implode('',$rowHashes));
  }
 }finally{if($locked){$db->exec('UNLOCK TABLES');$locked=false;}fclose($out);}
 // Everything below targets the new, unique database or private backup files only.
 $manifest=['backup_file'=>$backupPath,'sha256'=>hash_file('sha256',$backupPath),'physical_files'=>$raw,'source_database'=>'nst','restored_database'=>$copyName,'restore_verified'=>false,'authorized_copy'=>true,'healthy_checksums'=>$checksums,'healthy_counts'=>$counts,'damaged_tables'=>$damaged,'schemas'=>$schemas,'views'=>$views,'selects'=>$selects,'created_at'=>$stamp];
 file_put_contents($private.'/backup-manifest.json',json_encode($manifest,JSON_PRETTY_PRINT));
 $stage='create_copy';$db->exec('CREATE DATABASE `'.$copyName.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
 $copyCfg=$cfg;$copyCfg['dbname']=$copyName;$target=new PDO(appDatabaseDsn($copyCfg),$cfg['user'],$cfg['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
 // Restore parents before children, preserving integrity checks throughout.
 $pending=$schemas;$ordered=[];
 while($pending){$changed=false;foreach($pending as $name=>$ddl){preg_match_all('/REFERENCES\s+`([^`]+)`/i',$ddl,$refs);$dependencies=array_filter($refs[1],fn($r)=>$r!==$name&&isset($schemas[$r])&&!isset($ordered[$r]));if($dependencies)continue;$stage='schema:'.$name;$target->exec($ddl);$ordered[$name]=true;unset($pending[$name]);$changed=true;}if(!$changed)throw new RuntimeException('Cyclic restore dependencies');}
 $target->setAttribute(PDO::ATTR_STRINGIFY_FETCHES,true);
 // Re-read data from the consistent backup, never from the now-unlocked source.
 $stage='restore_data';$in=fopen($backupPath,'r');$inserts=[];while(($line=fgets($in))!==false)if(preg_match('/^INSERT INTO `([^`]+)`/',$line,$match))$inserts[$match[1]][]=trim($line);fclose($in);foreach(array_keys($ordered) as $name){$stage='restore_data:'.$name;foreach($inserts[$name]??[] as $insert)$target->exec($insert);}unset($inserts);
 $recovered=[];
 foreach($damaged as $name){
  $stage='recover_copy:'.$name;if(!preg_match('/^neuro_mig_test_[a-zA-Z0-9_]+$/',$copyName))throw new RuntimeException('Unsafe repair destination');
  foreach(['frm','MYD','MYI'] as $ext)if(!copy($private.'/'.$name.'.'.$ext,$dataDir.'/'.$copyName.'/'.$name.'.'.$ext))throw new RuntimeException('Physical restore failed');
  $description=[];$exit=0;exec('C:/xampp/mysql/bin/myisamchk.exe --description '.escapeshellarg($private.'/'.$name.'.MYI').' 2>&1',$description,$exit);$text=implode("\n",$description);if(!preg_match('/Data records:\s*(\d+)/i',$text,$match))throw new RuntimeException('Missing pre-repair row count');$expected=(int)$match[1];
  $repair=$target->query('REPAIR TABLE `'.$name.'`')->fetchAll(PDO::FETCH_ASSOC);$check=$target->query('CHECK TABLE `'.$name.'`')->fetchAll(PDO::FETCH_ASSOC);$ok=false;foreach($check as $result)if(strtolower($result['Msg_type']??'')==='status'&&($result['Msg_text']??'')==='OK')$ok=true;
  $actual=(int)$target->query('SELECT COUNT(*) FROM `'.$name.'`')->fetchColumn();$recovered[$name]=['expected_header_rows'=>$expected,'actual_rows'=>$actual,'check_ok'=>$ok];if(!$ok||$actual!==$expected)throw new RuntimeException('Recovered copy does not reconcile');
 }
 foreach($counts as $name=>$expected){$stage='verify:'.$name;if((int)$target->query('SELECT COUNT(*) FROM `'.$name.'`')->fetchColumn()!==$expected)throw new RuntimeException('Restore count mismatch');$rowHashes=[];foreach($target->query($selects[$name])->fetchAll(PDO::FETCH_ASSOC) as $row)$rowHashes[]=hash('sha256',serialize($row));sort($rowHashes,SORT_STRING);if(!hash_equals($checksums[$name],hash('sha256',implode('',$rowHashes))))throw new RuntimeException('Restore content mismatch');}
 foreach($views as $name=>$ddl){$stage='view:'.$name;$ddl=preg_replace('/DEFINER=`[^`]+`@`[^`]+`\s*/i','',$ddl);$target->exec($ddl);}
 $manifest['restore_verified']=true;$manifest['recovered_tables']=$recovered;file_put_contents($private.'/backup-manifest.json',json_encode($manifest,JSON_PRETTY_PRINT));
 // Account restricted to the new copy. Password is never printed or written into the repo.
 $username='mig_'.bin2hex(random_bytes(6));$password=bin2hex(random_bytes(24));
 $db->exec('CREATE USER '.$db->quote($username)."@'localhost' IDENTIFIED BY ".$db->quote($password));
 $db->exec('GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,INDEX,REFERENCES ON `'.$copyName.'`.* TO '.$db->quote($username)."@'localhost'");
 $profile=['mode'=>'isolated_copy','enable_copy_writes'=>false,'forbidden_databases'=>['nst','fact_nst'],'snapshot'=>$private.'/source-reviewed.json','snapshot_sha256'=>'','backup_manifest'=>$private.'/backup-manifest.json','target'=>['host'=>$cfg['host'],'port'=>(int)$cfg['port'],'database'=>$copyName,'user'=>$username,'password'=>$password]];
 file_put_contents($private.'/profile.json',json_encode($profile,JSON_PRETTY_PRINT));
 file_put_contents(__DIR__.'/../../output/migration-copy-location.json',json_encode(['database'=>$copyName,'private_directory'=>$private,'profile'=>$private.'/profile.json','restored'=>true,'recovered_tables'=>$recovered],JSON_PRETTY_PRINT));
 echo json_encode(['copy_created'=>$copyName,'restore_verified'=>true,'healthy_tables'=>count($counts),'recovered_tables'=>$recovered]),PHP_EOL;
}catch(Throwable $e){if($locked&&$db){try{$db->exec('UNLOCK TABLES');}catch(Throwable $ignored){}}fwrite(STDERR,'Copy preparation stopped at '.$stage.': '.get_class($e).' code '.$e->getCode().'. No production repair was performed.'.PHP_EOL);exit(1);}
