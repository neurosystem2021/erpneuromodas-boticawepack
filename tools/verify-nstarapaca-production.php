<?php
if (PHP_SAPI !== 'cli') exit;
require 'C:/xampp/htdocs/boticav1/config/database.php';
function pvAssert($ok, $message) { if (!$ok) throw new RuntimeException($message); }
function pvImport($defaults, $database, $file) {
    $p = proc_open(array('C:/xampp/mysql/bin/mysql.exe','--defaults-extra-file='.$defaults,'--default-character-set=utf8mb4',$database), array(0=>array('file',$file,'r'),1=>array('pipe','w'),2=>array('pipe','w')), $pipes, null, null, array('bypass_shell'=>true));
    stream_get_contents($pipes[1]); $error=stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
    pvAssert(proc_close($p)===0, 'SQL restore failed: '.$error);
}
function pvFingerprint(PDO $db, $table, $omitOrder = false) {
    $rows=array();$stmt=$db->query('SELECT * FROM `'.$table.'`');
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)) {
        if($omitOrder)unset($row['IdPreOrden']);
        $rows[]=hash('sha256',serialize(array_values($row)));
    }
    sort($rows,SORT_STRING);return array('rows'=>count($rows),'sha256'=>hash('sha256',implode('',$rows)));
}
$root=dirname(__DIR__);$dir=trim(file_get_contents($root.'/tmp/nstarapaca-production/export-directory.txt'));
$package=$dir.'/paquete';$map=json_decode(file_get_contents($package.'/nombres-tablas.json'),true);$export=json_decode(file_get_contents($dir.'/export-report.json'),true);
$cfg=appDatabaseConfig();pvAssert($cfg['dbname']==='nst','Expected local nst connection.');
$connections=array();$created=array();$defaults=array();
$escape=function($value){return '"'.str_replace(array('\\','"',"\n","\r"),array('\\\\','\\"','\\n','\\r'),$value).'"';};
try{
    foreach(array(3306,3307) as $port){
        $c=$cfg;$c['port']=$port;$c['dbname']='information_schema';
        $connections[$port]=new PDO(appDatabaseDsn($c),$c['user'],$c['pass'],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC));
        $defaults[$port]=$dir.'/.verify-'.$port.'.cnf';
        file_put_contents($defaults[$port],"[client]\nhost=".$escape($cfg['host'])."\nport=".$port."\nuser=".$escape($cfg['user'])."\npassword=".$escape($cfg['pass'])."\n");
        $name='nst_export_verify_'.bin2hex(random_bytes(6));
        $connections[$port]->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');$created[$port]=$name;
        pvImport($defaults[$port],$name,$port===3306?$dir.'/nst-respaldo-completo.sql':$package.'/fact_nstarapaca.sql');
        $connections[$port]->exec('USE `'.$name.'`');
    }
    $original=$connections[3306];$clean=$connections[3307];
    $objects=$clean->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM);
    $actual=array_column($objects,0);$expected=array_values($map);sort($actual,SORT_STRING);sort($expected,SORT_STRING);
    $caseMode=(int)$clean->query('SELECT @@lower_case_table_names')->fetchColumn();
    // MariaDB on Windows folds VIEW filenames even in mode 2. Check base-table
    // spelling exactly, and validate the view spelling in the exported SQL itself.
    $windowsViewFolding=array();
    foreach($objects as $object){
        $canonical=$map[strtolower($object[0])];
        if($object[0]!==$canonical){
            pvAssert($caseMode===2&&$object[1]==='VIEW'&&strtolower($object[0])===strtolower($canonical),'Unexpected table spelling: '.$object[0]);
            $windowsViewFolding[$object[0]]=$canonical;
        }
    }
    $actualLower=array_map('strtolower',$actual);$expectedLower=array_map('strtolower',$expected);
    sort($actualLower,SORT_STRING);sort($expectedLower,SORT_STRING);
    pvAssert($actualLower===$expectedLower,'Object set changed.');
    $sql=file_get_contents($package.'/fact_nstarapaca.sql');
    foreach($map as $canonical)pvAssert(strpos($sql,'`'.$canonical.'`')!==false,'Canonical SQL identifier missing: '.$canonical);
    foreach($map as $source=>$target){
        $actualData=pvFingerprint($clean,$target);$expectedData=$export['after'][$source];
        pvAssert($actualData['rows']===$expectedData['rows']&&$actualData['sha256']===$expectedData['sha256'],'Restored rows differ: '.$target);
        pvAssert($clean->query('SHOW COLUMNS FROM `'.$target.'`')->fetchAll()===$expectedData['columns'],'Column definitions differ: '.$target);
    }
    foreach($export['cleared_tables'] as $table)pvAssert((int)$clean->query('SELECT COUNT(*) FROM `'.$map[$table].'`')->fetchColumn()===0,'Transaction rows remain: '.$table);
    pvAssert(pvFingerprint($original,'gen_producto',true)===pvFingerprint($clean,$map['gen_producto'],true),'Product attributes or prices changed.');
    pvAssert((int)$clean->query('SELECT COUNT(*) FROM `'.$map['gen_producto_totales'].'` WHERE TotalCompras<>0 OR TotalVentas<>0')->fetchColumn()===0,'Stock is not zero.');
    pvAssert((int)$clean->query('SELECT COUNT(*) FROM `'.$map['gen_producto'].'` WHERE COALESCE(IdPreOrden,0)<>0')->fetchColumn()===0,'Product order links remain.');
    $report=array('source_database'=>'nst','source_unchanged'=>$export['source_unchanged'],'objects'=>count($objects),'empty_transaction_tables'=>count($export['cleared_tables']),'restored_rows_and_columns_verified'=>true,'exact_base_table_spelling_verified'=>true,'canonical_sql_table_and_view_identifiers_verified'=>true,'windows_view_name_folding'=>$windowsViewFolding,'all_product_attributes_and_prices_preserved'=>true,'stock'=>'zero','products'=>$export['after']['gen_producto']['rows'],'clients'=>$export['after']['ve_docventacliente']['rows'],'providers'=>$export['after']['lo_proveedor']['rows'],'users'=>$export['after']['seg_usuario']['rows'],'independent_cash_movements_preserved'=>$export['independent_cash_movements_preserved'],'existing_bank_opening_balances_preserved'=>$export['after']['cb_saldobanco']['rows'],'case_test_server_lower_case_table_names'=>$caseMode,'linux_note'=>'SQL contains canonical case for every object. Windows mode 2 preserves base-table case but folds view names and does not enforce Linux case-sensitive query resolution; use the included PHP case report and normalizer before activation.');
    file_put_contents($package.'/restauracion-verificada.json',json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    echo json_encode($report,JSON_UNESCAPED_SLASHES).PHP_EOL;
}finally{
    foreach($created as $port=>$name)$connections[$port]->exec('DROP DATABASE `'.$name.'`');
    foreach($defaults as $file)if(is_file($file))unlink($file);
}
