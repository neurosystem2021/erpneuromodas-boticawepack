<?php
require __DIR__.'/../../integration/migration/Engine.php';
function check($condition,$message){if(!$condition)throw new RuntimeException($message);}
function blocked($fn,$message){try{$fn();}catch(Throwable $e){return;}throw new RuntimeException($message);}
function fixture(){return ['format'=>'neuro-migration-v1','source_hash'=>str_repeat('a',64),'validation'=>['sales_reconciliation'=>['documents_difference_over_one_cent'=>0]],'tables'=>[
 'Gen_Producto'=>['primary'=>['IdProducto'],'columns'=>['IdProducto'=>'int','Producto'=>'varchar','CodigoBarra'=>'varchar'],'rows'=>[['IdProducto'=>'99','Producto'=>' laptop ','CodigoBarra'=>'00099']]],
 'Ve_DocVenta'=>['primary'=>['idDocVenta'],'columns'=>['idDocVenta'=>'int','Serie'=>'varchar','Numero'=>'int'],'rows'=>[['idDocVenta'=>'123','Serie'=>'B001','Numero'=>'5']]],
 'Ve_DocVentaDet'=>['primary'=>['IdDocVentaDet'],'columns'=>['IdDocVentaDet'=>'int','IdDocVenta'=>'int','IdProducto'=>'int','Precio'=>'float'],'rows'=>[['IdDocVentaDet'=>'1','IdDocVenta'=>'123','IdProducto'=>'99','Precio'=>'1.2300']]],
 'Ve_DocVentaMetodoPagoDet'=>['primary'=>[],'columns'=>['IdDocVenta'=>'int','Importe'=>'float'],'rows'=>[['IdDocVenta'=>'123','Importe'=>'1.23'],['IdDocVenta'=>'123','Importe'=>'1.23']]],
 'Ve_DocVentaNotaCredito'=>['primary'=>['IdDocVentaNotaCredito'],'columns'=>['IdDocVentaNotaCredito'=>'int','idDocVenta'=>'int'],'rows'=>[['IdDocVentaNotaCredito'=>'7','idDocVenta'=>'999']]]
 ]];}
$db=new PDO('sqlite::memory:');$db->exec('PRAGMA foreign_keys=ON');
$db->exec('CREATE TABLE CurrentStock(id INTEGER PRIMARY KEY,qty INTEGER)');$db->exec('INSERT INTO CurrentStock VALUES(99,42)');
$writes=false;$guard=function()use(&$writes){if(!$writes)throw new RuntimeException('Read only');};$engine=new NeuroMigration($db,fixture(),$guard);
blocked(fn()=>$engine->install(),'Installation must be blocked');$writes=true;$engine->install();
$plan=$engine->simulate(['sales'],['Gen_Producto'=>['Producto'=>['to'=>'Nombre','operation'=>'trim']]]);
check($plan['total']===6,'Preserve every source row including identical payments');
check($plan['entries'][0]['mapped']['Nombre']==='laptop','Mapping transform');check($plan['entries'][0]['row']['Producto']===' laptop ','Original preserved');
blocked(fn()=>$engine->simulate(['sales'],['Gen_Producto'=>['Producto'=>['to'=>'IdProducto','operation'=>'copy']]]),'Reject field overwrite');
$id=$engine->create($plan,'admin');check($engine->create($plan,'admin')===$id,'Repeated simulation idempotent');
blocked(fn()=>$engine->batch($id,'admin',0),'Unapproved batch blocked');blocked(fn()=>$engine->approve($id,'admin',$plan['hash']),'Orphans/currency block approval');
foreach($engine->status($id)['issues'] as $issue)$engine->justify($id,$issue['code'],'Verified evidence retained: quarantine is intentional in isolated test.','admin');
blocked(fn()=>$engine->approve($id,'admin',str_repeat('b',64)),'Wrong hash blocked');
$engine->approve($id,'admin',$plan['hash']);
$s=$engine->batch($id,'admin',0,2);check($s['stored_rows']===2,'First batch committed');
$again=$engine->batch($id,'admin',0,2);check($again['stored_rows']===2,'Repeated request no extra rows');
// A failure after a partial batch must roll back the whole transaction and checkpoint.
$db->exec("CREATE TRIGGER fail_record BEFORE INSERT ON Mig_Record BEGIN SELECT RAISE(FAIL,'test failure'); END");
blocked(fn()=>$engine->batch($id,'admin',2,2),'Injected failure should throw');check($engine->status($id)['cursor_pos']==2,'Failure keeps checkpoint');check($engine->status($id)['stored_rows']===2,'Failure leaves no partial records');$db->exec('DROP TRIGGER fail_record');
do{$s=$engine->batch($id,'admin',(int)$engine->status($id)['cursor_pos'],2);}while($s['status']!=='completed');
check($s['stored_rows']===6,'All rows archived');check($db->query('SELECT qty FROM CurrentStock')->fetchColumn()==42,'Operational stock untouched');
$detail=$db->query("SELECT m.links_json FROM Mig_IdMap m WHERE entity='Ve_DocVentaDet'")->fetchColumn();$links=json_decode($detail,true);check(isset($links['IdProducto'],$links['IdDocVenta']),'Historical references mapped');
check($engine->report($id)['operational_tables_written']===false,'Report explicitly archive mode');
$record=$db->query('SELECT id,original_json FROM Mig_Record LIMIT 1')->fetch(PDO::FETCH_ASSOC);$db->prepare('UPDATE Mig_Record SET original_json=? WHERE id=?')->execute(['{}',$record['id']]);blocked(fn()=>$engine->rollback($id,'admin'),'Changed records block rollback');$db->prepare('UPDATE Mig_Record SET original_json=? WHERE id=?')->execute([$record['original_json'],$record['id']]);
$engine->rollback($id,'admin');check($db->query('SELECT COUNT(*) FROM Mig_Record')->fetchColumn()==0,'Rollback only this archive');check($db->query('SELECT qty FROM CurrentStock')->fetchColumn()==42,'Rollback preserves existing data');
$changed=fixture();$changed['source_hash']=str_repeat('c',64);$other=new NeuroMigration($db,$changed,$guard);blocked(fn()=>$other->approve($id,'admin',$plan['hash']),'Changed source rejected');
$writes=false;blocked(fn()=>$engine->create($plan,'admin'),'Write guard enforced every operation');
echo "PASS archive mappings, source preservation, identical rows, approval guards, batching, retry, atomic failure, source drift, rollback, and untouched operational stock. SQLite isolated fixture only.\n";
