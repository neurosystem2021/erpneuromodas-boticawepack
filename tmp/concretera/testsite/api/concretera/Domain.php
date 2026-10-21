<?php
// PHP 7.4; importes y cantidades exactos con BCMath, nunca float.
class CxError extends RuntimeException {
    public $status;
    public function __construct($message, $status=422) { parent::__construct($message); $this->status=$status; }
}
function cxNow() { return (new DateTimeImmutable('now',new DateTimeZone('America/Lima')))->format('Y-m-d H:i:s'); }
function cxJson($value) {
    $json=json_encode($value,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE);
    if($json===false)throw new CxError('No se pudo representar el contenido.',500);return $json;
}
function cxCanonical($value) {
    if(is_array($value)) { if(array_keys($value)!==range(0,count($value)-1))ksort($value);foreach($value as &$v)$v=cxCanonical($v);unset($v); }
    return $value;
}
function cxHash($value) { return hash('sha256',cxJson(cxCanonical($value))); }
function cxText($value,$max,$required=false) {
    if(!is_string($value)&&!is_int($value))throw new CxError('Se esperaba un texto.');
    $value=trim((string)$value);if(mb_strlen($value,'UTF-8')>$max||($required&&$value===''))throw new CxError('Complete un texto válido (máximo '.$max.' caracteres).');return $value;
}
function cxId($value) {
    if(filter_var($value,FILTER_VALIDATE_INT)===false||(int)$value<=0)throw new CxError('Identificador inválido.');return (int)$value;
}
function cxChoice($value,array $choices,$label) {
    if(!is_string($value)||!in_array($value,$choices,true))throw new CxError('Seleccione '.$label.'.');return $value;
}
function cxDate($value) {
    $value=cxText($value,10,true);$d=DateTimeImmutable::createFromFormat('!Y-m-d',$value,new DateTimeZone('America/Lima'));
    if(!$d||$d->format('Y-m-d')!==$value)throw new CxError('Fecha inválida; use año-mes-día.');return $value;
}
function cxDec($value,$scale=4,$positive=false,$max='9999999999') {
    if(!is_string($value)&&!is_int($value))throw new CxError('Envíe cantidades y precios como texto decimal exacto.');
    $value=trim((string)$value);
    if(!preg_match('/^\d{1,12}(?:\.\d{1,6})?$/D',$value)||bccomp($value,'0',6)<($positive?1:0)||bccomp($value,$max,6)>0)throw new CxError('Cantidad, precio o porcentaje fuera del rango permitido.');
    if(bccomp($value,bcadd($value,'0',$scale),6)!==0)throw new CxError('El dato admite como máximo '.$scale.' decimales.');
    return bcadd($value,'0',$scale);
}
function cxRound($value,$scale=2) {
    $step='0.'.str_repeat('0',$scale).'5';return bcadd($value,bccomp($value,'0',12)<0?'-'.$step:$step,$scale);
}
function cxAmounts($quantity,$unitPrice,$discount,$included,$rate,$pricesIncludeTax,$absoluteDiscount=null) {
    $gross=$included?'0':($absoluteDiscount!==null?bcsub(bcmul($quantity,$unitPrice,12),$absoluteDiscount,12):bcmul(bcmul($quantity,$unitPrice,12),bcsub('1',bcdiv($discount,'100',12),12),12));if(bccomp($gross,'0',12)<0)throw new CxError('El descuento supera el importe de la partida.');
    if($pricesIncludeTax) { $total=cxRound($gross);$base=cxRound(bcdiv($gross,bcadd('1',bcdiv($rate,'100',12),12),12));$tax=bcsub($total,$base,2); }
    else { $base=cxRound($gross);$tax=cxRound(bcdiv(bcmul($base,$rate,12),'100',12));$total=bcadd($base,$tax,2); }
    if(bccomp($total,'9999999999999999.99',2)>0)throw new CxError('El importe supera el límite monetario permitido. Revise cantidades y tarifas.');
    return ['base'=>$base,'impuesto'=>$tax,'total'=>$total];
}
function cxSumAmounts(array $rows) {
    $sum=['base'=>'0.00','impuesto'=>'0.00','total'=>'0.00'];foreach($rows as $row)foreach($sum as $k=>$v)$sum[$k]=bcadd($v,$row[$k],2);if(bccomp($sum['total'],'9999999999999999.99',2)>0)throw new CxError('El total supera el límite monetario permitido.');return $sum;
}

require_once __DIR__.'/Companies.php';
require_once __DIR__.'/FiscalSettings.php';
require_once __DIR__.'/PosQuote.php';
require_once __DIR__.'/Acceptance.php';
require_once __DIR__.'/Operations.php';
require_once __DIR__.'/Materials.php';
require_once __DIR__.'/Scheduling.php';
require_once __DIR__.'/SchedulingForm.php';
require_once __DIR__.'/StockAlerts.php';
require_once __DIR__.'/MixControl.php';
class CxService {
    use CxCompanyGroup, CxFiscalSettings, CxPosQuote, CxAcceptance, CxOperations, CxMaterials, CxScheduling, CxSchedulingForm, CxStockAlerts, CxMixControl;
    public $db,$user,$super;
    private $legacy=[];
    public function __construct($db,$user,$super=false) { $this->db=$db;$this->user=$user;$this->super=$super;if(!extension_loaded('bcmath'))throw new CxError('Se requiere BCMath para cantidades exactas.',503); }
    public function run($sql,array $args=[]) { $s=$this->db->prepare($sql);if(!$s->execute($args))throw new CxError('No se pudo completar el registro.',500);return $s; }
    public function company($company) {
        $id=cxId($company);$r=$this->run('SELECT IDEMPRESA,RAZONSOCIAL,RUC,DIRECCION,IGV,TELEFONO,EMAIL FROM GEN_EMPRESA WHERE IDEMPRESA=?',[$id])->fetch(PDO::FETCH_ASSOC);
        if(!$r)throw new CxError('Empresa no disponible.',404);return $r;
    }
    public function permission($company,$capability='read',$allowDisabled=false) {
        $id=cxId($company);$config=$this->run('SELECT * FROM Cx_Configuracion WHERE IdEmpresa=?',[$id])->fetch(PDO::FETCH_ASSOC);
        $member=$this->run('SELECT Rol,DescuentoMaxPct FROM Cx_Miembro WHERE IdEmpresa=? AND Usuario=? AND Activo=1',[$id,$this->user])->fetch(PDO::FETCH_ASSOC);
        if(!$this->super&&!$member)throw new CxError('No tiene acceso a esta empresa.',403);
        $role=$this->super?'ADMIN':$member['Rol'];
        $caps=['read'=>['ADMIN','COMERCIAL','VENTAS','LECTURA'],'write'=>['ADMIN','COMERCIAL','VENTAS'],'accept'=>['ADMIN','COMERCIAL','VENTAS'],'commercial'=>['ADMIN','COMERCIAL'],'admin'=>['ADMIN']];
        if(!isset($caps[$capability])||!in_array($role,$caps[$capability],true))throw new CxError('Su función no autoriza esta acción.',403);
        if((!$config||!(int)$config['Activo'])&&!$allowDisabled)throw new CxError('El módulo está desactivado para esta empresa.',403);
        return ['companyId'=>$id,'config'=>$config,'role'=>$role,'discountMax'=>$this->super?'100.0000':($member['DescuentoMaxPct']??'0')];
    }
    public function context() {
        $rows=$this->run('SELECT IDEMPRESA,RAZONSOCIAL,RUC FROM GEN_EMPRESA ORDER BY IDEMPRESA')->fetchAll(PDO::FETCH_ASSOC);$out=[];
        $hasGroup=(int)$this->run('SELECT COUNT(*) FROM Cx_Grupo WHERE Activo=1')->fetchColumn()>0;
        foreach($rows as $row) {try{$p=$this->permission($row['IDEMPRESA'],'read',true);$group=$this->group($row['IDEMPRESA']);if($hasGroup&&!$group)continue;$out[]=['id'=>(int)$row['IDEMPRESA'],'nombre'=>$group?$group['Nombre']:$row['RAZONSOCIAL'],'ruc'=>$row['RUC'],'activo'=>!empty($p['config']['Activo']),'modo'=>$p['config']['Modo']??'PRUEBA','rol'=>$p['role'],'version'=>(int)($p['config']['Version']??0),'grupo'=>$group,'emisores'=>$this->issuers($row['IDEMPRESA']),'responsableAceptacion'=>in_array($p['role'],['ADMIN','COMERCIAL','VENTAS'],true)?$this->acceptanceResponsible($row['IDEMPRESA'],true):null];}catch(CxError $e){if($e->status!==403)throw $e;}}
        return ['usuario'=>$this->user,'administrador'=>$this->super,'empresas'=>$out,'etapa'=>2,'fechaHoy'=>substr(cxNow(),0,10)];
    }
    public function audit($company,$entity,$id,$action,$before,$after,$reason='') {
        $this->run('INSERT INTO Cx_Auditoria(IdEmpresa,Entidad,IdEntidad,Accion,Usuario,Fecha,Motivo,Antes,Despues) VALUES(?,?,?,?,?,?,?,?,?)',[$company,$entity,$id,$action,$this->user,cxNow(),$reason,$before===null?null:cxJson($before),cxJson($after)]);
    }
    private function atomic($company,callable $fn) {
        $this->legacy=[];$this->db->beginTransaction();
        try { $this->run('SELECT IdEmpresa FROM Cx_Configuracion WHERE IdEmpresa=? FOR UPDATE',[$company]);$this->permission($company);$r=$fn();$this->db->commit();$this->legacy=[];return $r; }
        catch(Throwable $e) {if($this->db->inTransaction())$this->db->rollBack();$cleanup=true;
            foreach(array_reverse($this->legacy) as $entry)try{foreach($entry['tables'] as $table=>$column)$this->run('DELETE FROM '.$table.' WHERE '.$column.'=?',[$entry['id']]);}catch(Throwable $failed){$cleanup=false;error_log('Concretera compensación: '.$failed->getMessage());}
            $this->legacy=[];if(!$cleanup)throw new CxError('La operación requiere revisión técnica; no reintente todavía.',500);throw $e;
        }
    }
    public function configure($company,array $p) {
        $this->company($company);$access=$this->permission($company,'admin',true);$before=$access['config'];
        $groupOwner=$this->run('SELECT g.IdEmpresaGestion FROM Cx_GrupoEmpresa e INNER JOIN Cx_Grupo g ON g.IdGrupo=e.IdGrupo WHERE e.IdEmpresa=? AND g.Activo=1',[$company])->fetchColumn();if($groupOwner!==false&&(int)$groupOwner!==(int)$company)throw new CxError('Este emisor usa la configuración del grupo compartido; no se activa como una gestión separada.');
        $mode=cxChoice($p['modo']??'PRUEBA',['PRUEBA','OPERACION'],'el modo');
        if($mode==='OPERACION'&&(string)($p['confirmacionEmpresa']??'')!==(string)$this->company($company)['RUC'])throw new CxError('Escriba el RUC exacto de la empresa fiscal antes de habilitar operación.');
        $this->db->beginTransaction();try{
            if($before) { $s=$this->run('UPDATE Cx_Configuracion SET Activo=?,Modo=?,Version=Version+1,UsuarioMod=?,FechaMod=? WHERE IdEmpresa=? AND Version=?',[!empty($p['activo'])?1:0,$mode,$this->user,cxNow(),$company,(int)($p['version']??0)]);if($s->rowCount()!==1)throw new CxError('La configuración cambió; vuelva a cargar.',409); }
            else $this->run('INSERT INTO Cx_Configuracion(IdEmpresa,Activo,Modo,UsuarioMod,FechaMod) VALUES(?,?,?,?,?)',[$company,!empty($p['activo'])?1:0,$mode,$this->user,cxNow()]);
            $after=$this->run('SELECT * FROM Cx_Configuracion WHERE IdEmpresa=?',[$company])->fetch(PDO::FETCH_ASSOC);$this->audit($company,'configuracion',$company,'CONFIGURAR',$before,$after,cxText($p['motivo']??'',500,true));$this->db->commit();return $after;
        }catch(Throwable $e){if($this->db->inTransaction())$this->db->rollBack();throw $e;}
    }
    public function synchronize($company) {
        $this->permission($company,'admin');
        if((int)$this->run('SELECT COUNT(*) FROM GEN_EMPRESA')->fetchColumn()!==1)throw new CxError('Con varias empresas, asigne referencias ERP individualmente; no se puede atribuir todo el catálogo.');
        $this->company($company);
        return $this->atomic($company,function()use($company){$counts=[];foreach(['CLIENTE'=>['Ve_DocVentaCliente','IdCliente'],'PRODUCTO'=>['Gen_Producto','IdProducto'],'ALMACEN'=>['Lo_Almacen','IdAlmacen']] as $entity=>$source){$s=$this->run('INSERT IGNORE INTO Cx_ReferenciaERP(IdEmpresa,Entidad,IdERP,FechaReg,UsuarioReg) SELECT ?,?,'.$source[1].',?,? FROM '.$source[0].' WHERE COALESCE(Anulado+0,0)=0',[$company,$entity,cxNow(),$this->user]);$counts[$entity]=$s->rowCount();}$this->audit($company,'referencias',0,'SINCRONIZAR',null,$counts,'Catálogo de empresa única; solo referencias, sin duplicar registros ERP.');return $counts;});
    }
    public function assignReference($company,array $p) {
        $this->permission($company,'admin');$entity=cxChoice($p['entidad']??'',['CLIENTE','PRODUCTO','ALMACEN'],'la entidad');$id=cxId($p['idERP']??0);
        $sources=['CLIENTE'=>['Ve_DocVentaCliente','IdCliente'],'PRODUCTO'=>['Gen_Producto','IdProducto'],'ALMACEN'=>['Lo_Almacen','IdAlmacen']];$t=$sources[$entity];
        if(!$this->run('SELECT '.$t[1].' FROM '.$t[0].' WHERE '.$t[1].'=? AND COALESCE(Anulado+0,0)=0',[$id])->fetchColumn())throw new CxError('La referencia ERP no está disponible.');
        return $this->atomic($company,function()use($company,$entity,$id){$owner=$this->run('SELECT IdEmpresa FROM Cx_ReferenciaERP WHERE Entidad=? AND IdERP=? FOR UPDATE',[$entity,$id])->fetchColumn();if($owner!==false&&(int)$owner!==(int)$company)throw new CxError('La referencia ya pertenece a otra empresa.',409);$this->run('INSERT IGNORE INTO Cx_ReferenciaERP(IdEmpresa,Entidad,IdERP,FechaReg,UsuarioReg) VALUES(?,?,?,?,?)',[$company,$entity,$id,cxNow(),$this->user]);$this->audit($company,'referencias',$id,'ASIGNAR',null,['entidad'=>$entity]);return ['ok'=>true];});
    }
    public function member($company,array $p) {
        $this->permission($company,'admin');$user=cxText($p['usuario']??'',100,true);if(!$this->run('SELECT Usuario FROM Seg_Usuario WHERE Usuario=? AND COALESCE(Anulado+0,0)=0',[$user])->fetchColumn())throw new CxError('Seleccione un usuario activo existente.');
        $role=cxChoice($p['rol']??'',['ADMIN','COMERCIAL','VENTAS','LECTURA'],'la función');$max=cxDec($p['descuentoMaxPct']??'0',4,false,'100');
        return $this->atomic($company,function()use($company,$p,$user,$role,$max){$before=$this->run('SELECT * FROM Cx_Miembro WHERE IdEmpresa=? AND Usuario=?',[$company,$user])->fetch(PDO::FETCH_ASSOC);$this->run('INSERT INTO Cx_Miembro(IdEmpresa,Usuario,Rol,DescuentoMaxPct,Activo,UsuarioMod,FechaMod) VALUES(?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE Rol=VALUES(Rol),DescuentoMaxPct=VALUES(DescuentoMaxPct),Activo=VALUES(Activo),UsuarioMod=VALUES(UsuarioMod),FechaMod=VALUES(FechaMod)',[$company,$user,$role,$max,!empty($p['activo'])?1:0,$this->user,cxNow()]);$after=['usuario'=>$user,'rol'=>$role,'descuentoMaxPct'=>$max,'activo'=>!empty($p['activo'])];$this->audit($company,'miembro',0,'AUTORIZAR',$before?:null,$after,cxText($p['motivo']??'',500,true));return $after;});
    }
    private function reference($company,$entity,$id) {
        $id=cxId($id);if(!$this->run('SELECT IdERP FROM Cx_ReferenciaERP WHERE IdEmpresa=? AND Entidad=? AND IdERP=?',[$company,$entity,$id])->fetchColumn())throw new CxError('La referencia no pertenece a esta empresa.',403);return $id;
    }
    public function catalog($company,$kind,$query='') {
        $this->permission($company);
        $map=['clientes'=>['CLIENTE','Ve_DocVentaCliente','IdCliente','Cliente','DniRuc'],'productos'=>['PRODUCTO','Gen_Producto','IdProducto','Producto','CodigoBarra'],'almacenes'=>['ALMACEN','Lo_Almacen','IdAlmacen','Almacen','Direccion']];
        if(!isset($map[$kind]))throw new CxError('Catálogo no disponible.');$t=$map[$kind];$materialFilter=$kind==='productos'?' AND NOT EXISTS(SELECT 1 FROM Cx_Material cm WHERE cm.IdProducto=e.IdProducto) ':'';$q='%'.cxText($query,120).'%';
        $rows=$this->run('SELECT e.'.$t[2].' AS id,e.'.$t[3].' AS nombre,e.'.$t[4].' AS referencia FROM '.$t[1].' e INNER JOIN Cx_ReferenciaERP r ON r.Entidad=? AND r.IdERP=e.'.$t[2].' AND r.IdEmpresa=? WHERE COALESCE(e.Anulado+0,0)=0 '.$materialFilter.' AND (e.'.$t[3].' LIKE ? OR e.'.$t[4].' LIKE ?) ORDER BY e.'.$t[3].' LIMIT 100',[$t[0],$company,$q,$q])->fetchAll(PDO::FETCH_ASSOC);
        if($kind==='productos')foreach($rows as &$row){$o=$this->productOffer($company,$row['id']);$row['oferta']=$o?['resistencia'=>$o['Resistencia'],'precio'=>$o['PrecioSinImpuesto'],'moneda'=>$o['Moneda'],'unidad'=>$o['Unidad'],'clase'=>$o['Clase'],'servicio'=>$o['Servicio'],'version'=>$o['Version']]:null;}unset($row);
        return $rows;
    }
    private function productOffer($company,$product) {
        $o=$this->run('SELECT * FROM Cx_OfertaProducto WHERE IdEmpresa=? AND IdProducto=? ORDER BY Version DESC LIMIT 1',[$company,$product])->fetch(PDO::FETCH_ASSOC);
        if(!$o||!(int)$o['Activo'])return null;$o['Fuente']=json_decode($o['Fuente'],true);return $o;
    }
    public function catalogCandidates($company,$kind,$query='') {
        $this->permission($company,'admin');
        $map=['clientes'=>['CLIENTE','Ve_DocVentaCliente','IdCliente','Cliente','DniRuc'],'productos'=>['PRODUCTO','Gen_Producto','IdProducto','Producto','CodigoBarra'],'almacenes'=>['ALMACEN','Lo_Almacen','IdAlmacen','Almacen','Direccion']];
        if(!isset($map[$kind]))throw new CxError('Catálogo no disponible.');
        $query=cxText($query,120);if($kind!=='almacenes'&&mb_strlen($query)<2)throw new CxError('Escriba al menos dos letras del nombre, documento o código.');
        $t=$map[$kind];$q='%'.$query.'%';
        return $this->run('SELECT e.'.$t[2].' AS id,e.'.$t[3].' AS nombre,e.'.$t[4].' AS referencia,CASE WHEN r.IdEmpresa=? THEN 1 ELSE 0 END AS vinculado FROM '.$t[1].' e LEFT JOIN Cx_ReferenciaERP r ON r.Entidad=? AND r.IdERP=e.'.$t[2].' WHERE COALESCE(e.Anulado+0,0)=0 AND (r.IdEmpresa IS NULL OR r.IdEmpresa=?) AND (e.'.$t[3].' LIKE ? OR e.'.$t[4].' LIKE ?) ORDER BY e.'.$t[3].' LIMIT 30',[$company,$t[0],$company,$q,$q])->fetchAll(PDO::FETCH_ASSOC);
    }
    public function createWork($company,array $p) {
        $this->permission($company,'write');$client=$this->reference($company,'CLIENTE',$p['idCliente']??0);
        $data=[$company,$client,cxText($p['codigo']??'',50,true),cxText($p['nombre']??'',180,true),cxText($p['direccion']??'',3000,true),cxText($p['distrito']??'',100),cxText($p['provincia']??'',100),cxText($p['departamento']??'',100)];
        $ubigeo=cxText($p['ubigeo']??'',6);if($ubigeo!==''&&!preg_match('/^\d{6}$/D',$ubigeo))throw new CxError('El ubigeo debe tener seis dígitos o quedar pendiente.');
        $data=array_merge($data,[$ubigeo?:null,cxText($p['referencia']??'',2000),cxText($p['contacto']??'',180,true),cxText($p['telefono']??'',40),cxText($p['accesos']??'',3000),cxText($p['ventanaAtencion']??'',180),cxText($p['ubicacion']??'',250),$this->user,cxNow(),$this->user,cxNow()]);
        return $this->atomic($company,function()use($company,$data){$this->run('INSERT INTO Cx_Obra(IdEmpresa,IdCliente,Codigo,Nombre,Direccion,Distrito,Provincia,Departamento,Ubigeo,Referencia,Contacto,Telefono,Accesos,VentanaAtencion,Ubicacion,UsuarioReg,FechaReg,UsuarioMod,FechaMod) VALUES('.implode(',',array_fill(0,count($data),'?')).')',$data);$id=(int)$this->db->lastInsertId();$row=$this->work($company,$id);$this->audit($company,'obra',$id,'CREAR',null,$row);return $row;});
    }
    public function work($company,$id) {
        $this->permission($company);$r=$this->run('SELECT * FROM Cx_Obra WHERE IdEmpresa=? AND IdObra=?',[$company,cxId($id)])->fetch(PDO::FETCH_ASSOC);if(!$r)throw new CxError('Obra no disponible.',404);return $r;
    }
    public function createSpecification($company,array $p) {
        $this->permission($company,'commercial');$product=$this->reference($company,'PRODUCTO',$p['idProducto']??0);
        $min=cxDec($p['slumpMin']??'0');$max=cxDec($p['slumpMax']??'0');if(bccomp($max,$min,4)<0)throw new CxError('El rango de asentamiento no es válido.');
        $age=cxId($p['edadDias']??0);if($age>3650)throw new CxError('Revise la edad especificada.');
        $data=[$company,$product,cxText($p['codigo']??'',50,true),cxText($p['nombre']??'',180,true),cxDec($p['resistencia']??'',4,true),cxChoice($p['unidadResistencia']??'',['kg/cm2','MPa'],'la unidad de resistencia'),$age,$min,$max,cxChoice($p['unidadSlump']??'',['mm','pulg'],'la unidad de asentamiento'),cxDec($p['tamanoAgregado']??'',4,true),cxChoice($p['unidadAgregado']??'',['mm','pulg'],'la unidad del agregado'),cxChoice($p['descarga']??'',['BOMBEADO','CAIDA_LIBRE'],'el tipo de descarga'),cxText($p['observaciones']??'',3000),$this->user,cxNow()];
        return $this->atomic($company,function()use($company,$data){$this->run('INSERT INTO Cx_Especificacion(IdEmpresa,IdProducto,Codigo,Nombre,Resistencia,UnidadResistencia,EdadDias,SlumpMin,SlumpMax,UnidadSlump,TamanoAgregado,UnidadAgregado,Descarga,Observaciones,UsuarioReg,FechaReg) VALUES('.implode(',',array_fill(0,count($data),'?')).')',$data);$id=(int)$this->db->lastInsertId();$r=$this->specification($company,$id);$this->audit($company,'especificacion',$id,'CREAR',null,$r,'Prestación comercial; no es receta técnica aprobada.');return $r;});
    }
    public function specification($company,$id) {
        $this->permission($company);$r=$this->run('SELECT * FROM Cx_Especificacion WHERE IdEmpresa=? AND IdEspecificacion=? AND Activo=1',[$company,cxId($id)])->fetch(PDO::FETCH_ASSOC);if(!$r)throw new CxError('Especificación no disponible.',404);return $r;
    }
    public function listing($company,$kind) {
        $this->permission($company);$map=['obras'=>['Cx_Obra','IdObra'],'especificaciones'=>['Cx_Especificacion','IdEspecificacion'],'propuestas'=>['Cx_PropuestaVersion','IdVersion'],'pedidos'=>['Cx_Pedido','IdPedido'],'auditoria'=>['Cx_Auditoria','IdAuditoria'],'miembros'=>['Cx_Miembro','Usuario']];
        if($kind==='propuestas')return $this->issuerListing($this->run('SELECT v.*,p.Anio,p.Numero FROM Cx_PropuestaVersion v INNER JOIN Cx_Propuesta p ON p.IdPropuesta=v.IdPropuesta AND p.IdEmpresa=v.IdEmpresa WHERE v.IdEmpresa=? ORDER BY v.IdVersion DESC LIMIT 250',[$company])->fetchAll(PDO::FETCH_ASSOC));
        if($kind==='pedidos')return $this->issuerListing($this->run('SELECT * FROM Cx_Pedido WHERE IdEmpresa=? ORDER BY IdPedido DESC LIMIT 250',[$company])->fetchAll(PDO::FETCH_ASSOC));
        if(!isset($map[$kind]))throw new CxError('Listado no disponible.');if($kind==='miembros')$this->permission($company,'admin');$t=$map[$kind];return $this->run('SELECT * FROM '.$t[0].' WHERE IdEmpresa=? ORDER BY '.$t[1].' DESC LIMIT 250',[$company])->fetchAll(PDO::FETCH_ASSOC);
    }
    private function nextNumber($company,$type,$year) {
        $this->run('INSERT IGNORE INTO Cx_Serie(IdEmpresa,Tipo,Anio,Ultimo) VALUES(?,?,?,0)',[$company,$type,$year]);$n=(int)$this->run('SELECT Ultimo FROM Cx_Serie WHERE IdEmpresa=? AND Tipo=? AND Anio=? FOR UPDATE',[$company,$type,$year])->fetchColumn()+1;
        $this->run('UPDATE Cx_Serie SET Ultimo=? WHERE IdEmpresa=? AND Tipo=? AND Anio=?',[$n,$company,$type,$year]);return $n;
    }
    private function idempotency($company,$action,$key,$payload,callable $fn) {
        $key=cxText($key,100,true);if(!preg_match('/^[A-Za-z0-9_-]{8,100}$/D',$key))throw new CxError('Clave de reintento inválida.');$hash=cxHash($payload);
        $this->run("INSERT INTO Cx_Idempotencia(IdEmpresa,Accion,Clave,Huella,Resultado,Fecha) VALUES(?,?,?,?,'{}',?) ON DUPLICATE KEY UPDATE Clave=VALUES(Clave)",[$company,$action,$key,$hash,cxNow()]);
        $row=$this->run('SELECT * FROM Cx_Idempotencia WHERE IdEmpresa=? AND Accion=? AND Clave=? FOR UPDATE',[$company,$action,$key])->fetch(PDO::FETCH_ASSOC);
        if(!hash_equals($row['Huella'],$hash))throw new CxError('Esta clave ya se utilizó con otros datos. No se repitió la operación.',409);
        if($row['Resultado']!=='{}')return json_decode($row['Resultado'],true);
        $r=$fn();$this->run('UPDATE Cx_Idempotencia SET Resultado=? WHERE IdEmpresa=? AND Accion=? AND Clave=?',[cxJson($r),$company,$action,$key]);return $r;
    }
    private function legacyQuote($company,$client,$date,$days,$items,$type) {
        $this->run('INSERT INTO Ve_Proforma(Anio,Numero,IdCliente,FechaReg,UsuarioReg,DiasValidos,Descripcion) VALUES(?,0,?,?,?,?,?)',[(int)substr($date,0,4),$client,cxNow(),$this->user,$days,'CONCRETERA / '.$type.' / datos y aceptación en el módulo Concretera.']);
        $id=(int)$this->db->lastInsertId();$this->legacy[]=['id'=>$id,'tables'=>['Ve_ProformaDet'=>'IdProforma','Ve_Proforma'=>'IdProforma']];
        $this->run('UPDATE Ve_Proforma SET Numero=? WHERE IdProforma=?',[$id,$id]);
        foreach($items as $item)$this->run('INSERT INTO Ve_ProformaDet(IdProforma,IdProducto,Descripcion,Cantidad,Precio,Descuento,DisabledTotal) VALUES(?,?,?,?,?,?,?)',[$id,$item['productId'],$item['description'],$item['quantity']??'0',$item['price'],$item['snapshot']['descuentoImporte']??'0',$type==='TARIFARIO'?1:0]);
        $this->run("INSERT INTO Cx_ReferenciaERP(IdEmpresa,Entidad,IdERP,FechaReg,UsuarioReg) VALUES(?,'PROFORMA',?,?,?)",[$company,$id,cxNow(),$this->user]);return $id;
    }
    private function legacyOrder($company,$version,$items) {
        $this->run('INSERT INTO Ve_PreOrden(IdCliente,FechaReg,UsuarioReg,Estado,IdProforma,Descripcion,Anulado) VALUES(?,?,?,0,?,?,0)',[$version['IdCliente'],cxNow(),$this->user,$version['IdProforma'],'SUMINISTRO CONCRETERA; liquidar desde entregas, no repetir venta o adelanto.']);
        $id=(int)$this->db->lastInsertId();$this->legacy[]=['id'=>$id,'tables'=>['Ve_PreOrdenDet'=>'IdPreOrden','Ve_PreOrden'=>'IdPreOrden']];
        // Decisión D11: Cantidad es INT en Ve_PreOrdenDet. Conservar los m³
        // decimales en Cx_PedidoPartida; nunca truncarlos en la proyección.
        $this->run("INSERT INTO Cx_ReferenciaERP(IdEmpresa,Entidad,IdERP,FechaReg,UsuarioReg) VALUES(?,'PREORDEN',?,?,?)",[$company,$id,cxNow(),$this->user]);return $id;
    }
    private function commercialInput($company,array $p) {
        $access=$this->permission($company,'write');$work=!empty($p['idObra'])?$this->work($company,$p['idObra']):['IdObra'=>null,'IdCliente'=>cxId($p['idCliente']??0),'Nombre'=>null,'Direccion'=>null,'Distrito'=>null,'Provincia'=>null,'Departamento'=>null,'Contacto'=>null,'Telefono'=>null,'Pendiente'=>true];
        if(empty($work['Pendiente'])&&!(int)$work['Activo'])throw new CxError('La obra no está activa.');
        $this->reference($company,'CLIENTE',$work['IdCliente']);$warehouse=$this->reference($company,'ALMACEN',$p['idAlmacen']??0);
        $type=cxChoice($p['tipo']??'',['TARIFARIO','PRESUPUESTO'],'el tipo de propuesta');$currency=cxChoice($p['moneda']??'',['PEN','USD'],'la moneda');
        $date=cxDate($p['fechaEmision']??'');$until=cxDate($p['vigenteHasta']??'');$days=(new DateTimeImmutable($date))->diff(new DateTimeImmutable($until))->days;
        if($until<$date||$days>3650)throw new CxError('Revise la vigencia de la propuesta.');
        $companyRow=$this->issuer($company,$p['idEmpresaEmisora']??null);$taxKind=cxChoice($p['impuesto']??'',['GRAVADA','EXONERADA','INAFECTA'],'la condición de impuesto');
        if($taxKind==='GRAVADA'&&($companyRow['IGV']===null||bccomp((string)$companyRow['IGV'],'0',6)<=0))throw new CxError('Configure la tasa de impuesto de la empresa emisora antes de cotizar una operación gravada.');
        $taxRate=$taxKind==='GRAVADA'?cxDec((string)$companyRow['IGV'],6,true,'100'):'0.000000';
        if($taxKind==='GRAVADA'&&bccomp($taxRate,'1',6)<0)$taxRate=bcmul($taxRate,'100',6);
        $taxRate=cxDec($taxRate,4,false,'100');$includedTax=!empty($p['preciosIncluyenImpuesto'])?1:0;
        $c=(array)($p['condiciones']??[]);$testDays=$c['ensayosDias']??[];
        if(!is_array($testDays)||count($testDays)>12)throw new CxError('Revise los ensayos pactados.');$testDays=array_values(array_unique(array_map('cxId',$testDays)));foreach($testDays as $d)if($d>3650)throw new CxError('Edad de ensayo fuera de rango.');
        $conditions=['trasladoIncluido'=>!empty($c['trasladoIncluido']),'bombeoIncluido'=>!empty($c['bombeoIncluido']),'anticipacionHoras'=>cxDec($c['anticipacionHoras']??'0',4,false,'8760'),'permanenciaMinutos'=>cxDec($c['permanenciaMinutos']??'0',4,false,'10080'),'ensayosDias'=>$testDays,'requisitosAcceso'=>cxText($c['requisitosAcceso']??'',3000),'particulares'=>cxText($c['particulares']??'',6000)];
        $pay=(array)($p['pago']??[]);$payment=['modalidad'=>cxChoice($pay['modalidad']??'',['CONTADO','CREDITO'],'la modalidad de pago'),'adelantoPactadoPct'=>cxDec($pay['adelantoPactadoPct']??'0',4,false,'100'),'liberacion'=>cxChoice($pay['liberacion']??'',['POR_CONFIRMAR','CONTADO','ADELANTO','CREDITO_APROBADO'],'la condición para liberar producción'),'cuentaReferencia'=>cxText($pay['cuentaReferencia']??'',500),'vencimiento'=>empty($pay['vencimiento'])?null:cxDate($pay['vencimiento'])];
        if($payment['modalidad']==='CREDITO'&&!$payment['vencimiento'])throw new CxError('Indique el vencimiento pactado del crédito.');
        if($payment['modalidad']==='CREDITO'||$payment['liberacion']==='CREDITO_APROBADO')$this->permission($company,'commercial');
        $items=$p['partidas']??null;if(!is_array($items)||!count($items)||count($items)>100)throw new CxError('Agregue entre una y cien partidas.');
        $normalized=[];$hasConcrete=false;$amountRows=[];
        foreach($items as $index=>$item) {
            if(!is_array($item))throw new CxError('Partida inválida.');$product=$this->reference($company,'PRODUCTO',$item['idProducto']??0);
            $prod=$this->run('SELECT IdProducto,Producto,CodigoBarra FROM Gen_Producto WHERE IdProducto=? AND COALESCE(Anulado+0,0)=0',[$product])->fetch(PDO::FETCH_ASSOC);if(!$prod)throw new CxError('Producto no disponible.');
            $kind=cxChoice($item['tipo']??'',['CONCRETO','SERVICIO'],'la clase de partida');$spec=null;$service=null;
            $offer=$this->productOffer($company,$product);
            if($kind==='CONCRETO') {
                $hasConcrete=true;
                if(!empty($item['idEspecificacion'])){$spec=$this->specification($company,$item['idEspecificacion']);if((int)$spec['IdProducto']!==$product)throw new CxError('La especificación no corresponde al producto seleccionado.');}
                else {if(!$offer||$offer['Clase']!=='CONCRETO'||!$offer['Resistencia'])throw new CxError('Seleccione un concreto del catálogo o registre su especificación comercial.');$spec=['IdEspecificacion'=>null,'IdProducto'=>$product,'Nombre'=>$prod['Producto'],'Resistencia'=>$offer['Resistencia'],'UnidadResistencia'=>'kg/cm2','EdadDias'=>null,'SlumpMin'=>null,'SlumpMax'=>null,'TamanoAgregado'=>null,'Descarga'=>'POR CONFIRMAR','EstadoTecnico'=>'PENDIENTE','Origen'=>'CATALOGO_COMERCIAL'];}
                $unit='m3';$inPrice=false;
            }
            else { $unit=cxChoice($item['unidad']??'',['m3','h','viaje','fijo'],'la unidad del servicio');$service=cxChoice($item['servicio']??'',['BOMBEO','TRANSPORTE','MOVILIZACION','ESPERA','OTRO'],'el servicio');$inPrice=!empty($item['incluido']);if(($service==='BOMBEO'&&$conditions['bombeoIncluido']||$service==='TRANSPORTE'&&$conditions['trasladoIncluido'])&&!$inPrice)throw new CxError('El servicio figura incluido en las condiciones y no se puede cobrar otra vez.'); }
            $absolute=array_key_exists('descuentoImporte',$item)?cxDec($item['descuentoImporte'],2):null;$discount=cxDec($item['descuentoPct']??'0',4,false,'100');if(bccomp($discount,$access['discountMax'],4)>0)throw new CxError('El descuento de la partida '.($index+1).' supera su autorización de '.$access['discountMax'].'%.',403);
            $quantity=$type==='PRESUPUESTO'?cxDec($item['cantidad']??'',2,true):null;
            $price=cxDec($item['precioUnitario']??'',2,!$inPrice);
            $minimum=$kind==='SERVICIO'?cxDec($item['minimo']??'0',4):'0.0000';
            $row=['productId'=>$product,'specId'=>!empty($spec['IdEspecificacion'])?(int)$spec['IdEspecificacion']:null,'kind'=>$kind,'service'=>$service,'description'=>cxText($item['descripcion']??$prod['Producto'],2000,true),'unit'=>$unit,'quantity'=>$quantity,'price'=>$price,'discount'=>$discount,'minimum'=>$minimum,'included'=>$inPrice?1:0,'snapshot'=>['producto'=>$prod,'especificacion'=>$spec,'oferta'=>$offer]];
            if($absolute!==null){if($quantity===null)throw new CxError('El descuento monetario requiere cantidades definidas.');$raw=bcmul($quantity,$price,12);if(bccomp($absolute,$raw,12)>0||($inPrice&&bccomp($absolute,'0',2)>0))throw new CxError('Descuento monetario inválido.');$pct=bccomp($raw,'0',12)>0?bcdiv(bcmul($absolute,'100',12),$raw,8):'0';if(bccomp($pct,$access['discountMax'],8)>0)throw new CxError('El descuento supera su autorización.',403);$row['snapshot']['descuentoImporte']=$absolute;}
        $normalized[]=$row;if($quantity!==null)$amountRows[]=cxAmounts(bccomp($minimum,$quantity,4)>0?$minimum:$quantity,$price,$discount,$inPrice,$taxRate,$includedTax,$absolute);
        }
        if(!$hasConcrete&&empty($p['origenPOS']))throw new CxError('La propuesta de suministro requiere una partida de concreto.');
        $client=$this->run('SELECT IdCliente,Cliente,DniRuc,Direccion,Telefono,Email FROM Ve_DocVentaCliente WHERE IdCliente=? AND COALESCE(Anulado+0,0)=0',[$work['IdCliente']])->fetch(PDO::FETCH_ASSOC);if(!$client)throw new CxError('El cliente ya no está disponible.');
        $calculated=$type==='TARIFARIO'?null:cxSumAmounts($amountRows);if(isset($p['totalEsperado'])&&($calculated===null||bccomp(cxDec($p['totalEsperado'],2),$calculated['total'],2)!==0))throw new CxError('El total no coincide con el carrito. Revise cantidades, precios, descuentos o cargos adicionales antes de generar la proforma.');
        $snapshot=['empresa'=>$companyRow,'cliente'=>$client,'obra'=>$work,'origen'=>['tipo'=>isset($p['origenPOS'])?'PUNTO_VENTA':'CAPTURA_MANUAL','usuario'=>$this->user,'idEmpresaGestion'=>(int)$company,'capturaPOS'=>$p['origenPOS']??null],'notaTecnica'=>'Especificación comercial; no equivale a una receta aprobada.'];
        require_once __DIR__.'/CommercialProfile.php';$profile=cxCommercialProfile($companyRow['RUC']??'');if($profile)$snapshot['perfilComercial']=$profile;
        return ['work'=>$work,'warehouse'=>$warehouse,'type'=>$type,'currency'=>$currency,'date'=>$date,'until'=>$until,'days'=>$days,'taxKind'=>$taxKind,'taxRate'=>$taxRate,'taxIncluded'=>$includedTax,'conditions'=>$conditions,'payment'=>$payment,'items'=>$normalized,'snapshot'=>$snapshot,'totals'=>$calculated,'test'=>($access['config']['Modo']??'PRUEBA')==='PRUEBA'?1:0];
    }
    public function createProposal($company,array $p) {
        $this->assertCommercialIssuer($company,cxId($p['idEmpresaEmisora']??$company));
        $data=$this->commercialInput($company,$p);$parent=empty($p['idPropuesta'])?null:cxId($p['idPropuesta']);$reason=cxText($p['motivo']??'Creación de propuesta',1000,true);
        return $this->atomic($company,function()use($company,$p,$data,$parent,$reason){return $this->idempotency($company,'PROPUESTA',$p['idempotencia']??'',[$p,$company],function()use($company,$p,$data,$parent,$reason){
            $this->assertCommercialIssuer($company,(int)$data['snapshot']['empresa']['IDEMPRESA']);
            $versionNumber=1;$proposalId=$parent;$year=(int)substr($data['date'],0,4);
            if($parent) { $head=$this->run('SELECT * FROM Cx_Propuesta WHERE IdEmpresa=? AND IdPropuesta=? FOR UPDATE',[$company,$parent])->fetch(PDO::FETCH_ASSOC);if(!$head)throw new CxError('Propuesta no disponible.',404);
                $previous=$this->run('SELECT IdEmpresaEmisora FROM Cx_PropuestaVersion WHERE IdEmpresa=? AND IdPropuesta=? ORDER BY Version DESC LIMIT 1',[$company,$parent])->fetchColumn();if((int)($previous?:$company)!==(int)$data['snapshot']['empresa']['IDEMPRESA'])throw new CxError('Una nueva versión conserva la empresa emisora. Para cambiarla, cree otra propuesta.',409);
                if((int)$head['VersionActual']!==(int)($p['versionActual']??0))throw new CxError('Hay una versión más reciente. Vuelva a cargar.',409);
                $accepted=$this->run('SELECT IdPedido FROM Cx_Pedido p INNER JOIN Cx_PropuestaVersion v ON v.IdVersion=p.IdVersion WHERE v.IdEmpresa=? AND v.IdPropuesta=?',[$company,$parent])->fetchColumn();if($accepted)throw new CxError('Ya existe un pedido aceptado. Su contenido no se modifica; cree otra propuesta para un contrato nuevo.',409);
                $versionNumber=(int)$head['VersionActual']+1;$this->run("UPDATE Cx_PropuestaVersion SET Estado='SUSTITUIDA',Revision=Revision+1 WHERE IdEmpresa=? AND IdPropuesta=? AND Estado IN ('BORRADOR','EMITIDA')",[$company,$parent]);$this->run('UPDATE Cx_Propuesta SET VersionActual=? WHERE IdEmpresa=? AND IdPropuesta=?',[$versionNumber,$company,$parent]);
            } else { $number=$this->nextNumber($company,'CW',$year);$this->run('INSERT INTO Cx_Propuesta(IdEmpresa,Anio,Numero,UsuarioReg,FechaReg) VALUES(?,?,?,?,?)',[$company,$year,$number,$this->user,cxNow()]);$proposalId=(int)$this->db->lastInsertId(); }
            $legacyId=$this->legacyQuote($company,$data['work']['IdCliente'],$data['date'],$data['days'],$data['items'],$data['type']);$totals=$data['totals'];
            $args=[$company,$proposalId,$versionNumber,$legacyId,$data['work']['IdObra'],$data['work']['IdCliente'],$data['warehouse'],$data['type'],$data['currency'],$data['date'],$data['until'],$data['taxKind'],$data['taxRate'],$data['taxIncluded'],cxJson($data['conditions']),cxJson($data['payment']),cxJson($data['snapshot']),$totals['base']??null,$totals['impuesto']??null,$totals['total']??null,cxHash($data),$reason,$data['test'],$this->user,cxNow()];
            $this->run('INSERT INTO Cx_PropuestaVersion(IdEmpresa,IdPropuesta,Version,IdProforma,IdObra,IdCliente,IdAlmacen,Tipo,Moneda,FechaEmision,VigenteHasta,CondicionImpuesto,TasaImpuesto,PreciosIncluyenImpuesto,Condiciones,Pago,Instantanea,Base,Impuesto,Total,Huella,Motivo,EsPrueba,UsuarioReg,FechaReg) VALUES('.implode(',',array_fill(0,count($args),'?')).')',$args);$versionId=(int)$this->db->lastInsertId();
            $this->run('UPDATE Cx_PropuestaVersion SET IdEmpresaEmisora=? WHERE IdEmpresa=? AND IdVersion=?',[$data['snapshot']['empresa']['IDEMPRESA'],$company,$versionId]);
            foreach($data['items'] as $i=>$item)$this->run('INSERT INTO Cx_PropuestaPartida(IdEmpresa,IdVersion,Posicion,IdProducto,IdEspecificacion,Tipo,Servicio,Descripcion,Unidad,Cantidad,PrecioUnitario,DescuentoPct,Minimo,Incluido,Instantanea) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',[$company,$versionId,$i+1,$item['productId'],$item['specId'],$item['kind'],$item['service'],$item['description'],$item['unit'],$item['quantity'],$item['price'],$item['discount'],$item['minimum'],$item['included'],cxJson($item['snapshot'])]);
            $this->audit($company,'propuesta',$versionId,'CREAR_VERSION',null,['idPropuesta'=>$proposalId,'version'=>$versionNumber,'idProforma'=>$legacyId,'huella'=>cxHash($data)],$reason);return ['idVersion'=>$versionId,'idPropuesta'=>$proposalId,'version'=>$versionNumber,'idProforma'=>$legacyId];
        });});
    }
    public function proposal($company,$id) {
        $this->permission($company);$row=$this->run('SELECT v.*,p.Anio,p.Numero,p.VersionActual FROM Cx_PropuestaVersion v INNER JOIN Cx_Propuesta p ON p.IdPropuesta=v.IdPropuesta AND p.IdEmpresa=v.IdEmpresa WHERE v.IdEmpresa=? AND v.IdVersion=?',[$company,cxId($id)])->fetch(PDO::FETCH_ASSOC);if(!$row)throw new CxError('Versión no disponible.',404);
        foreach(['Condiciones','Pago','Instantanea'] as $key)$row[$key]=json_decode($row[$key],true);
        $row['partidas']=$this->run('SELECT * FROM Cx_PropuestaPartida WHERE IdEmpresa=? AND IdVersion=? ORDER BY Posicion',[$company,$id])->fetchAll(PDO::FETCH_ASSOC);foreach($row['partidas'] as &$item)$item['Instantanea']=json_decode($item['Instantanea'],true);unset($item);
        $row['EstadoEfectivo']=$row['Estado']==='EMITIDA'&&$row['VigenteHasta']<substr(cxNow(),0,10)?'VENCIDA':$row['Estado'];return $row;
    }
    public function issueProposal($company,array $p) {
        $this->permission($company,'write');$id=cxId($p['idVersion']??0);
        return $this->atomic($company,function()use($company,$p,$id){$row=$this->proposal($company,$id);$this->assertCommercialIssuer($company,$row['IdEmpresaEmisora']?:$row['Instantanea']['empresa']['IDEMPRESA']);if($row['Estado']!=='BORRADOR'||(int)$row['Revision']!==(int)($p['revision']??0))throw new CxError('La propuesta cambió o ya fue emitida.',409);
            if($row['VigenteHasta']<substr(cxNow(),0,10))throw new CxError('La propuesta venció. Cree una nueva versión con vigencia aprobada.');$this->run("UPDATE Cx_PropuestaVersion SET Estado='EMITIDA',Revision=Revision+1 WHERE IdEmpresa=? AND IdVersion=?",[$company,$id]);$this->audit($company,'propuesta',$id,'EMITIR',['estado'=>'BORRADOR'],['estado'=>'EMITIDA']);return $this->proposal($company,$id);});
    }
    public function acceptProposal($company,array $p) {
        $this->permission($company,'accept');$id=cxId($p['idVersion']??0);$person=cxText($p['aceptante']??'',180);$evidence=cxText($p['observacionesAceptacion']??$p['evidencia']??'',6000);$selections=$p['selecciones']??null;
        if(!is_array($selections)||!count($selections)||count($selections)>100)throw new CxError('Seleccione las partidas y cantidades contratadas.');
        foreach($selections as &$s){if(!is_array($s))throw new CxError('Selección inválida.');$s=['idPartida'=>cxId($s['idPartida']??0),'cantidad'=>cxDec($s['cantidad']??'',2,true)];}unset($s);usort($selections,function($a,$b){return $a['idPartida']<=>$b['idPartida'];});
        $payload=[$id,(int)($p['revision']??0),$person,$evidence,$selections,$p['idObra']??null,$this->user];
        return $this->atomic($company,function()use($company,$p,$id,$person,$evidence,$selections,$payload){return $this->idempotency($company,'ACEPTAR',$p['idempotencia']??'',$payload,function()use($company,$p,$id,$person,$evidence,$selections){
            $version=$this->proposal($company,$id);$today=substr(cxNow(),0,10);
            if($version['Estado']!=='EMITIDA'||(int)$version['Revision']!==(int)($p['revision']??0))throw new CxError('La versión no está emitida o cambió. No se creó otro pedido.',409);
            $this->assertCommercialIssuer($company,$version['IdEmpresaEmisora']?:$version['Instantanea']['empresa']['IDEMPRESA']);
            $orderSnapshot=$version['Instantanea'];if(empty($version['IdObra'])){if(empty($p['idObra']))throw new CxError('Seleccione una obra real del cliente antes de crear el pedido.');$acceptedWork=$this->work($company,$p['idObra']??0);if(!(int)$acceptedWork['Activo']||(int)$acceptedWork['IdCliente']!==(int)$version['IdCliente'])throw new CxError('Seleccione una obra activa del mismo cliente antes de confirmar el pedido.');$this->reference($company,'CLIENTE',$acceptedWork['IdCliente']);$orderSnapshot['obra']=$acceptedWork;}
            if($version['VigenteHasta']<$today||$version['FechaEmision']>$today)throw new CxError('La propuesta está fuera de vigencia. Registre una nueva versión; no cambie la aceptada.');
            $byId=[];foreach($version['partidas'] as $row)$byId[(int)$row['IdPartida']]=$row;$chosen=[];$seen=[];$amounts=[];$hasConcrete=false;
            foreach($selections as $selection){$lineId=$selection['idPartida'];if(isset($seen[$lineId])||!isset($byId[$lineId]))throw new CxError('Las partidas elegidas no son válidas para esta versión.');$seen[$lineId]=true;$row=$byId[$lineId];$q=$selection['cantidad'];
                if($version['Tipo']==='PRESUPUESTO'&&bccomp($q,$row['Cantidad'],4)>0)throw new CxError('La cantidad elegida excede el metrado presupuestado. Cree una nueva versión.');
                if($row['Tipo']==='CONCRETO')$hasConcrete=true;$priceQty=bccomp($q,$row['Minimo'],4)<0?$row['Minimo']:$q;
                $partialDiscount=isset($row['Instantanea']['descuentoImporte'])?cxRound(bcdiv(bcmul($row['Instantanea']['descuentoImporte'],$q,12),$row['Cantidad'],12)):null;
                $value=cxAmounts($priceQty,$row['PrecioUnitario'],$row['DescuentoPct'],(int)$row['Incluido'],$version['TasaImpuesto'],(int)$version['PreciosIncluyenImpuesto'],$partialDiscount);$amounts[]=$value;$chosen[]=['source'=>$row,'quantity'=>$q,'priceQuantity'=>$priceQty,'amounts'=>$value];
            }
            if(!$hasConcrete)throw new CxError('Seleccione al menos una partida de concreto.');$totals=cxSumAmounts($amounts);
            $confirmation=$this->acceptanceRecord($company,$version,$person,$evidence,$selections);$orderSnapshot['aceptacion']=$confirmation['snapshot'];$evidence=$confirmation['evidencia'];if($person==='')$person='Contacto del cliente no indicado';
            $legacyId=$this->legacyOrder($company,$version,$chosen);$year=(int)substr(cxNow(),0,4);$number=$this->nextNumber($company,'PW',$year);
            $this->run('INSERT INTO Cx_Pedido(IdEmpresa,IdVersion,IdPreOrden,Anio,Numero,Aceptante,FechaAceptacion,Evidencia,Condiciones,Pago,Instantanea,Moneda,Base,Impuesto,Total,EsPrueba,UsuarioReg,FechaReg) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',[$company,$id,$legacyId,$year,$number,$person,$confirmation['snapshot']['fecha'],$evidence,cxJson($version['Condiciones']),cxJson($version['Pago']),cxJson($orderSnapshot),$version['Moneda'],$totals['base'],$totals['impuesto'],$totals['total'],$version['EsPrueba'],$this->user,cxNow()]);$orderId=(int)$this->db->lastInsertId();
            $this->run('UPDATE Cx_Pedido SET IdEmpresaEmisora=? WHERE IdEmpresa=? AND IdPedido=?',[$version['IdEmpresaEmisora']?:$version['Instantanea']['empresa']['IDEMPRESA'],$company,$orderId]);
            foreach($chosen as $item){$r=$item['source'];$v=$item['amounts'];$this->run('INSERT INTO Cx_PedidoPartida(IdEmpresa,IdPedido,IdPartida,IdProducto,Tipo,Unidad,CantidadContratada,CantidadPrecio,PrecioUnitario,DescuentoPct,Incluido,Base,Impuesto,Total,Instantanea) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',[$company,$orderId,$r['IdPartida'],$r['IdProducto'],$r['Tipo'],$r['Unidad'],$item['quantity'],$item['priceQuantity'],$r['PrecioUnitario'],$r['DescuentoPct'],$r['Incluido'],$v['base'],$v['impuesto'],$v['total'],cxJson($r)]);}
            $this->run("UPDATE Cx_PropuestaVersion SET Estado='ACEPTADA',Revision=Revision+1 WHERE IdEmpresa=? AND IdVersion=?",[$company,$id]);$result=['idPedido'=>$orderId,'idPreOrden'=>$legacyId,'numero'=>'PW-'.$year.'-'.$number,'totales'=>$totals];$this->audit($company,'pedido',$orderId,'ACEPTAR_PROPUESTA',null,array_merge($result,['aceptacion'=>$confirmation['snapshot']]),'Aceptación registrada por '.$this->user.'. '.$evidence);return $result;
        });});
    }
    public function order($company,$id) {
        $this->permission($company);$r=$this->run('SELECT * FROM Cx_Pedido WHERE IdEmpresa=? AND IdPedido=?',[$company,cxId($id)])->fetch(PDO::FETCH_ASSOC);if(!$r)throw new CxError('Pedido no disponible.',404);
        foreach(['Condiciones','Pago','Instantanea'] as $key)$r[$key]=json_decode($r[$key],true);
        $r['partidas']=$this->run('SELECT * FROM Cx_PedidoPartida WHERE IdEmpresa=? AND IdPedido=? ORDER BY IdPedidoPartida',[$company,$id])->fetchAll(PDO::FETCH_ASSOC);foreach($r['partidas'] as &$line)$line['Instantanea']=json_decode($line['Instantanea'],true);unset($line);
        return $r;
    }
}
