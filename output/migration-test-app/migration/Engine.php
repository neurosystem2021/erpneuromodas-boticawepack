<?php
// Historical archive only. No legacy sales endpoint, stock update or fiscal sender is called.
final class NeuroMigration {
 private $db; private $snapshot; private $guard;
 public function __construct(PDO $db,array $snapshot,callable $guard){
  $this->db=$db;$this->snapshot=$snapshot;$this->guard=$guard;
  $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  if(($snapshot['format']??'')!=='neuro-migration-v1'||!preg_match('/^[a-f0-9]{64}$/',$snapshot['source_hash']??''))throw new InvalidArgumentException('Invalid snapshot');
 }
 public static function json($v){return json_encode($v,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);}
 private function query($sql,$args=[]){$s=$this->db->prepare($sql);$s->execute($args);return $s;}
 private function writeGuard(){call_user_func($this->guard);}
 private function stamp(){return (new DateTimeImmutable('now',new DateTimeZone('America/Lima')))->format(DATE_ATOM);}
 private function event($id,$actor,$action,$data=[]){$this->query('INSERT INTO Mig_Event VALUES(?,?,?,?,?,?)',[bin2hex(random_bytes(16)),$id,$actor,$action,self::json($data),$this->stamp()]);}
 public function install(){
  $this->writeGuard();$sql=file_get_contents(__DIR__.'/schema.sql');
  if($this->db->getAttribute(PDO::ATTR_DRIVER_NAME)==='sqlite'){$sql=preg_replace('/\) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4/',')',$sql);$sql=preg_replace('/UNIQUE KEY [A-Za-z_]+\(/','UNIQUE (',$sql);}
  foreach(explode(';',$sql) as $statement)if(trim($statement)!=='')$this->db->exec($statement);
 }
 public static function modules(){return [
  'catalog'=>['Gen_Producto','Gen_ProductoCategoria','Gen_ProductoMarca','Gen_ProductoMedicion','Gen_ProductoModelo','Gen_ProductoTalla','Gen_ProductoFormaFarmaceutica','Gen_ProductoBloque','Gen_ProductoDet','Gen_ProductoCompuesto','Gen_ProductoCompuestoDet','Gen_Producto_Serie','Lo_Almacen','Lo_Proveedor','Lo_Impuesto','Gen_Moneda','Ve_DocVentaCliente','Ve_DocVentaTipoDoc','Ve_DocVentaMetodoPago','Ve_DocVentaPuntoVenta','Ve_DocVentaPuntoVentaDet'],
  'sales'=>['Ve_DocVenta','Ve_DocVentaDet','Ve_DocVentaMetodoPagoDet','Ve_DocVentaNotaCredito','Ve_DocVentaDescuento','Ve_DocVentaDetEntrega','Ve_DocVentaHistorialKit','Ve_DocVentaRemision','Ve_DocVentaRemisionDet'],
  'purchases'=>['Lo_OrdenCompra','Lo_OrdenCompraDet','Lo_Movimiento','Lo_MovimientoDetalle','Lo_MovimientoDetalleImpuesto','Lo_MovimientoTipo'],
  'related'=>['Ve_PreOrden','Ve_PreOrdenDet','Ve_PreOrdenDetPago','Ve_Proforma','Ve_ProformaDet','prodstock','Cb_CierreCaja','Ag_CierreCaja','Cb_CajaBanco','Cb_CajaBancoDet','Cb_Cuenta','Cb_TipoCajaBanco','Gen_Oferta','Sunat_Resumen','remision','tblKardex','tblKardexvalor']
 ];}
 private function field($row,$name){foreach($row as $k=>$v)if(strcasecmp($k,$name)===0)return $v;return null;}
 private function table($name){foreach($this->snapshot['tables'] as $n=>$t)if(strcasecmp($n,$name)===0)return $t;return ['rows'=>[],'primary'=>[]];}
 private function key($table,$row,$ordinal){$pk=$this->table($table)['primary']??[];if(!$pk)return 'ordinal:'.$ordinal;return 'pk:'.self::json(array_map(fn($f)=>$this->field($row,$f),$pk));}
 private function relationRules($table){
  $rules=[];$fields=$this->table($table)['columns']??[];$names=array_keys($fields);
  $targets=['IdProducto'=>['Gen_Producto','IdProducto'],'IdProductoDet'=>['Gen_Producto','IdProducto'],'IdCliente'=>['Ve_DocVentaCliente','IdCliente'],'IdProveedor'=>['Lo_Proveedor','IdProveedor'],'IdAlmacen'=>['Lo_Almacen','IdAlmacen'],'IdAlmacenOrigen'=>['Lo_Almacen','IdAlmacen'],'IdAlmacenDestino'=>['Lo_Almacen','IdAlmacen'],'IdTipoDoc'=>['Ve_DocVentaTipoDoc','IdTipoDoc'],'IdMetodoPago'=>['Ve_DocVentaMetodoPago','IdMetodoPago'],'IdDocVentaPuntoVenta'=>['Ve_DocVentaPuntoVenta','IdDocVentaPuntoVenta'],'IdMovimientoTipo'=>['Lo_MovimientoTipo','IdMovimientoTipo'],'IdOrdenCompra'=>['Lo_OrdenCompra','IdOrdenCompra'],'IdDocVenta'=>['Ve_DocVenta','idDocVenta'],'hashMovimiento'=>['Lo_Movimiento','Hash'],'IdProforma'=>['Ve_Proforma','IdProforma'],'IdPreOrden'=>['Ve_PreOrden','IdPreOrden'],'IdComisionista'=>['Ve_DocVentaCliente','IdCliente']];
  $targets=array_merge($targets,['IdDocVentaDet'=>['Ve_DocVentaDet','IdDocVentaDet'],'IdProductoMarca'=>['Gen_ProductoMarca','IdProductoMarca'],'IdProductoCategoria'=>['Gen_ProductoCategoria','IdProductoCategoria'],'IdProductoMedicion'=>['Gen_ProductoMedicion','IdProductoMedicion'],'IdProductoFormaFarmaceutica'=>['Gen_ProductoFormaFarmaceutica','IdProductoFormaFarmaceutica'],'IdProductoSerie'=>['Gen_Producto_Serie','IdProductoSerie'],'IdOferta'=>['Gen_Oferta','IdOferta'],'IdProductoAsociado'=>['Gen_Producto','IdProducto'],'IdCajaBanco'=>['Cb_CajaBanco','IdCajaBanco'],'IdCuenta'=>['Cb_Cuenta','IdCuenta']]);
  foreach($names as $f)foreach($targets as $k=>$target)if(strcasecmp($f,$k)===0&&strcasecmp($table,$target[0])!==0)$rules[$f]=$target;
  foreach($this->table($table)['relations']??[] as $r)$rules[$r['column']]=[$r['table'],$r['target_column']];
  return $rules;
 }
 private function scrub($row){return array_filter($row,fn($k)=>!preg_match('/password|passwd|token|certificate|certificado|private.?key|clave/i',$k),ARRAY_FILTER_USE_KEY);}
 public function compare(){return $this->snapshot['comparison']??[];}
 public function simulate(array $modules,array $transforms=[]){
  $all=self::modules();if(!$modules||array_diff($modules,array_keys($all)))throw new InvalidArgumentException('Select valid modules');
  $modules=array_values(array_unique(array_merge(['catalog'],$modules)));$selected=[];
  foreach($modules as $m)foreach($all[$m] as $t)$selected[strtolower($t)]=true;
  do{$expanded=false;foreach($this->snapshot['tables'] as $name=>$table)if(isset($selected[strtolower($name)]))foreach($this->relationRules($name) as $ref)foreach($this->snapshot['tables'] as $candidate=>$ignored)if(strcasecmp($ref[0],$candidate)===0&&!isset($selected[strtolower($candidate)])){$selected[strtolower($candidate)]=true;$expanded=true;}}while($expanded);
  foreach($transforms as $table=>$cols)foreach($cols as $col=>$spec){if(!isset($selected[strtolower($table)])||!isset($this->table($table)['columns'][$col])||!is_array($spec)||!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,99}$/',$spec['to']??'')||!in_array($spec['operation']??'',['copy','trim','uppercase'],true))throw new InvalidArgumentException('Invalid mapping');}
  $entries=[];$counts=[];$lookup=[];$issues=[];$duplicates=[];
  foreach($this->snapshot['tables'] as $name=>$table){if(!isset($selected[strtolower($name)]))continue;$counts[$name]=count($table['rows']);
   foreach($table['rows'] as $i=>$row){$key=$this->key($name,$row,$i);$identity=$name.':'.$key;
    if(isset($duplicates[$identity]))$issues['duplicate_primary:'.$name]=['severity'=>'fatal','count'=>($issues['duplicate_primary:'.$name]['count']??0)+1];$duplicates[$identity]=true;
    $id=hash('sha256',$this->snapshot['source_hash'].'|'.$identity);
    foreach($row as $column=>$value){$lk=strtolower($name.'.'.$column).':'.(string)$value;if(!isset($lookup[$lk]))$lookup[$lk]=$id;else $lookup[$lk]=false;}
    $entries[]=['entity'=>$name,'key'=>$key,'id'=>$id,'row'=>$this->scrub($row)];
   }
  }
  foreach($entries as &$e){$e['links']=[];$e['mapped']=$e['row'];
   foreach($this->relationRules($e['entity']) as $col=>$ref){$value=$this->field($e['row'],$col);if($value===null||$value===''||(string)$value==='0')continue;$lk=strtolower($ref[0].'.'.$ref[1]).':'.(string)$value;
    if(!isset($lookup[$lk])||$lookup[$lk]===false){$code='relation:'.$e['entity'].'.'.$col;$issues[$code]=['severity'=>'review','count'=>($issues[$code]['count']??0)+1,'target'=>$ref[0]];}
    else $e['links'][$col]=['entity'=>$ref[0],'archive_id'=>$lookup[$lk],'old_value'=>$value];
   }
   foreach($e['row'] as $col=>$value){if(preg_match('/^Id[A-Z_]/i',$col)&&$value!==null&&$value!==''&&(string)$value!=='0'&&!in_array($col,$this->table($e['entity'])['primary']??[],true)&&!array_key_exists($col,$this->relationRules($e['entity']))){$code='unmapped_reference:'.$e['entity'].'.'.$col;$issues[$code]=['severity'=>'review','count'=>($issues[$code]['count']??0)+1,'detail'=>'Preserved original value; classify and document this reference before approval'];}}
   foreach($transforms[$e['entity']]??[] as $col=>$spec){$v=$this->field($e['row'],$col);if($v!==null&&$spec['operation']==='trim')$v=trim($v);if($v!==null&&$spec['operation']==='uppercase')$v=mb_strtoupper($v,'UTF-8');if($spec['to']!==$col&&array_key_exists($spec['to'],$e['mapped']))throw new InvalidArgumentException('Mapping overwrites a field');unset($e['mapped'][$col]);$e['mapped'][$spec['to']]=$v;}
   $e['hash']=hash('sha256',self::json($e['row']));
  }unset($e);
  foreach($this->snapshot['validation']['blockers']??[] as $code=>$detail)$issues['source:'.$code]=['severity'=>'fatal','detail'=>$detail];
  $validation=$this->snapshot['validation'];
  if(($validation['Ve_DocVenta_duplicate_groups']??0)>0)$issues['duplicate_documents']=['severity'=>'fatal','detail'=>'Source has duplicate documents'];
  if(in_array('purchases',$modules,true)&&count($this->table('Lo_OrdenCompra')['rows'])&&($validation['totals']['Lo_OrdenCompra']??'')==='0')$issues['purchase_order_total']=['severity'=>'review','detail'=>'Purchase order total is zero; document must not be interpreted as received'];
  if(in_array('sales',$modules,true)){if(($validation['sales_reconciliation']['documents_difference_over_one_cent']??0)>0)$issues['financial_difference']=['severity'=>'fatal','detail'=>'Differences above 0.01 require corrected source or verified rules'];$issues['sale_currency']=['severity'=>'review','detail'=>'Verify and document the historical currency; Ve_DocVenta does not declare it'];}
  // Raw source financial numbers are retained; archival writes never recalculate them.
  $plan=['version'=>1,'mode'=>'history_without_stock','source_hash'=>$this->snapshot['source_hash'],'modules'=>$modules,'transforms'=>$transforms,'resolved_entities'=>array_keys($counts),'counts'=>$counts,'total'=>count($entries),'issues'=>$issues,'validation'=>$validation,'entries'=>$entries];
  $plan['hash']=hash('sha256',self::json($plan));return $plan;
 }
 public function create(array $plan,$actor){
  $this->writeGuard();if($plan['source_hash']!==$this->snapshot['source_hash'])throw new RuntimeException('Source changed');
  $existing=$this->query("SELECT id,plan_hash FROM Mig_Run WHERE source_hash=? AND status<>'rolled_back'",[$plan['source_hash']])->fetch(PDO::FETCH_ASSOC);if($existing){if(hash_equals($existing['plan_hash'],$plan['hash']))return $existing['id'];throw new RuntimeException('Existing source run must be rolled back before changing modules');}
  $id=bin2hex(random_bytes(16));$this->db->beginTransaction();try{
   $metadata=$plan;unset($metadata['entries']);$this->query('INSERT INTO Mig_Run(id,owner,source_hash,plan_hash,status,total_rows,plan_json,created_at) VALUES(?,?,?,?,?,?,?,?)',[$id,$actor,$plan['source_hash'],$plan['hash'],'simulated',$plan['total'],self::json($metadata),$this->stamp()]);foreach($plan['entries'] as $position=>$entry)$this->query('INSERT INTO Mig_PlanRow VALUES(?,?,?)',[$id,$position,self::json($entry)]);
   foreach($plan['issues'] as $code=>$issue)$this->query('INSERT INTO Mig_Issue(run_id,code,severity,detail_json) VALUES(?,?,?,?)',[$id,$code,$issue['severity'],self::json($issue)]);
   $this->event($id,$actor,'simulated',['total'=>$plan['total']]);$this->db->commit();return $id;
  }catch(Throwable $e){$this->db->rollBack();throw $e;}
 }
 public function run($id){$suffix=$this->db->inTransaction()&&$this->db->getAttribute(PDO::ATTR_DRIVER_NAME)==='mysql'?' FOR UPDATE':'';$r=$this->query('SELECT * FROM Mig_Run WHERE id=?'.$suffix,[$id])->fetch(PDO::FETCH_ASSOC);if(!$r)throw new RuntimeException('Run not found');return $r;}
 public function justify($id,$code,$reason,$actor){$this->writeGuard();if(mb_strlen(trim($reason))<20)throw new InvalidArgumentException('Explain the evidence (20 characters minimum)');$this->db->beginTransaction();try{$r=$this->run($id);if($r['status']!=='simulated')throw new RuntimeException('Simulation is locked');$n=$this->query("UPDATE Mig_Issue SET justification=?,resolved_by=? WHERE run_id=? AND code=? AND severity='review'",[trim($reason),$actor,$id,$code])->rowCount();if(!$n)throw new RuntimeException('Issue cannot be justified');$this->event($id,$actor,'justified',['code'=>$code,'reason'=>trim($reason)]);$this->db->commit();}catch(Throwable $e){$this->db->rollBack();throw $e;}}
 public function approve($id,$actor,$expectedHash){$this->writeGuard();$this->db->beginTransaction();try{$r=$this->run($id);$this->fresh($r);if($r['status']!=='simulated'||!hash_equals($r['plan_hash'],$expectedHash))throw new RuntimeException('Stale approval');$unresolved=$this->query("SELECT COUNT(*) FROM Mig_Issue WHERE run_id=? AND (severity='fatal' OR justification IS NULL)",[$id])->fetchColumn();if($unresolved)throw new RuntimeException('Unresolved issues block approval');$this->query("UPDATE Mig_Run SET status='approved',approved_by=?,approved_at=? WHERE id=?",[$actor,$this->stamp(),$id]);$this->event($id,$actor,'approved');$this->db->commit();}catch(Throwable $e){$this->db->rollBack();throw $e;}}
 private function loadPlan($r){$p=json_decode($r['plan_json'],true,512,JSON_THROW_ON_ERROR);if(!isset($p['entries'])){$p['entries']=[];foreach($this->query('SELECT entry_json FROM Mig_PlanRow WHERE run_id=? ORDER BY ordinal_pos',[$r['id']])->fetchAll(PDO::FETCH_ASSOC) as $entry)$p['entries'][]=json_decode($entry['entry_json'],true,512,JSON_THROW_ON_ERROR);}return $p;}
 private function fresh($r){if(!hash_equals($r['source_hash'],$this->snapshot['source_hash']))throw new RuntimeException('Source changed');$p=$this->loadPlan($r);$hash=$p['hash'];unset($p['hash']);if(!hash_equals($r['plan_hash'],$hash)||!hash_equals($hash,hash('sha256',self::json($p))))throw new RuntimeException('Plan integrity failed');}
 public function batch($id,$actor,$expectedCursor,$limit=100){
  $this->writeGuard();$limit=max(1,min(500,(int)$limit));$this->db->beginTransaction();try{
   $r=$this->run($id);$this->fresh($r);if($r['status']==='completed'){$this->db->commit();return $this->status($id);}
   if(!in_array($r['status'],['approved','running','failed'],true)||!$r['approved_by'])throw new RuntimeException('Approval required');
   if((int)$r['cursor_pos']!==(int)$expectedCursor){$this->db->commit();return $this->status($id);}
   $locked=$this->query("UPDATE Mig_Run SET status='running',last_error=NULL,cursor_pos=cursor_pos WHERE id=? AND cursor_pos=? AND status IN ('approved','running','failed')",[$id,$expectedCursor]);
   $plan=$this->loadPlan($r);$slice=array_slice($plan['entries'],$expectedCursor,$limit);
   foreach($slice as $e){$rid=hash('sha256',$id.'|'.$e['id']);$links=$e['links'];foreach($links as &$link)$link['archive_id']=hash('sha256',$id.'|'.$link['archive_id']);unset($link);
    $this->query('INSERT INTO Mig_Record VALUES(?,?,?,?,?,?,?)',[$rid,$id,$e['entity'],$e['key'],self::json($e['row']),self::json($e['mapped']),$e['hash']]);
    $this->query('INSERT INTO Mig_IdMap VALUES(?,?,?,?,?)',[$id,$e['entity'],$e['key'],$rid,self::json($links)]);
   }
   $end=$expectedCursor+count($slice);$this->query('INSERT INTO Mig_Batch VALUES(?,?,?,?,?)',[$id,$expectedCursor,$end,count($slice),$this->stamp()]);
   $status=$end===(int)$r['total_rows']?'completed':'running';if($status==='completed')$this->verify($id,$plan,$end);$this->query('UPDATE Mig_Run SET cursor_pos=?,status=? WHERE id=?',[$end,$status,$id]);$this->event($id,$actor,'batch',['start'=>$expectedCursor,'end'=>$end]);$this->db->commit();return $this->status($id);
  }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();try{$this->query("UPDATE Mig_Run SET status='failed',last_error='batch_failed' WHERE id=? AND cursor_pos=? AND approved_by IS NOT NULL AND status IN ('approved','running','failed')",[$id,$expectedCursor]);$this->event($id,$actor,'batch_failed',['cursor'=>$expectedCursor,'code'=>'batch_failed']);}catch(Throwable $ignored){}throw $e;}
 }
 public function rollback($id,$actor){$this->writeGuard();$this->db->beginTransaction();try{$r=$this->run($id);$this->fresh($r);$this->verify($id,$this->loadPlan($r),(int)$r['cursor_pos']);
  $this->query('DELETE FROM Mig_IdMap WHERE run_id=?',[$id]);$this->query('DELETE FROM Mig_Record WHERE run_id=?',[$id]);$this->query("UPDATE Mig_Run SET status='rolled_back' WHERE id=?",[$id]);$this->event($id,$actor,'rolled_back');$this->db->commit();return $this->status($id);
 }catch(Throwable $e){$this->db->rollBack();throw $e;}}
 private function verify($id,$plan,$cursor){
  $records=$this->query('SELECT * FROM Mig_Record WHERE run_id=?',[$id])->fetchAll(PDO::FETCH_ASSOC);$actual=[];foreach($records as $r)$actual[$r['entity'].':'.$r['source_key']]=$r;
  if(count($records)!==$cursor)throw new RuntimeException('Archive count mismatch');
  foreach(array_slice($plan['entries'],0,$cursor) as $e){$r=$actual[$e['entity'].':'.$e['key']]??null;if(!$r||!hash_equals($e['hash'],hash('sha256',$r['original_json']))||$r['mapped_json']!==self::json($e['mapped']))throw new RuntimeException('Archive content mismatch');}
  if((int)$this->query('SELECT COUNT(*) FROM Mig_IdMap WHERE run_id=?',[$id])->fetchColumn()!==$cursor)throw new RuntimeException('Mapping count mismatch');
 }
 public function status($id){$r=$this->run($id);unset($r['plan_json']);$r['issues']=$this->query('SELECT code,severity,detail_json,justification,resolved_by FROM Mig_Issue WHERE run_id=?',[$id])->fetchAll(PDO::FETCH_ASSOC);$r['stored_rows']=(int)$this->query('SELECT COUNT(*) FROM Mig_Record WHERE run_id=?',[$id])->fetchColumn();return $r;}
 public function history(){return $this->query('SELECT id,owner,status,total_rows,cursor_pos,created_at,last_error FROM Mig_Run ORDER BY created_at DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC);}
 public function preview($entity,$page=1){if(!in_array($entity,array_merge(...array_values(self::modules())),true))throw new InvalidArgumentException('Invalid entity');return ['total'=>count($this->table($entity)['rows']),'rows'=>array_map(fn($r)=>$this->scrub($r),array_slice($this->table($entity)['rows'],(max(1,(int)$page)-1)*25,25))];}
 public function report($id){$r=$this->run($id);$p=$this->loadPlan($r);if($r['status']!=='rolled_back')$this->verify($id,$p,(int)$r['cursor_pos']);return ['run'=>$this->status($id),'counts'=>$p['counts'],'validation'=>$p['validation'],'mode'=>'history_without_stock','operational_tables_written'=>false,'events'=>$this->query('SELECT actor,action,data_json,created_at FROM Mig_Event WHERE run_id=? ORDER BY created_at,id',[$id])->fetchAll(PDO::FETCH_ASSOC)];}
}
