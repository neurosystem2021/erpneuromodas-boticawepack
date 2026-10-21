<?php
// Read-only preparation from the accepted order. Never creates bookings or stock movements.
trait CxSchedulingForm {
    public function scheduleDefaults($company,array $p) {
        $this->operational($company,'PROGRAMACION');$o=$this->order($company,$p['idPedido']??0);$existing=null;
        if(!empty($p['idProgramacion'])){$existing=$this->scheduleRow($company,$p['idProgramacion']);if((int)$existing['IdPedido']!==(int)$o['IdPedido'])throw new CxError('La programación no pertenece al pedido.',404);}
        $lines=[];$chosen=null;foreach($o['partidas'] as $line){if($line['Tipo']!=='CONCRETO')continue;
            $used=$this->run("SELECT COALESCE(SUM(Cantidad),0) FROM Cx_Programacion WHERE IdEmpresa=? AND IdPedidoPartida=? AND Estado IN ('BORRADOR','CONFIRMADA','EN_PREPARACION','EN_CURSO','ATENDIDA') AND IdProgramacion<>?",[$company,$line['IdPedidoPartida'],$existing['IdProgramacion']??0])->fetchColumn();
            $line['CantidadProgramada']=bcadd($used,'0',4);$line['CantidadPendiente']=bcsub($line['CantidadContratada'],$used,4);if(bccomp($line['CantidadPendiente'],'0',4)<0)$line['CantidadPendiente']='0.0000';$lines[]=$line;
        }
        $lineId=$p['idPedidoPartida']??($existing['IdPedidoPartida']??null);foreach($lines as $line)if($lineId&&(int)$line['IdPedidoPartida']===(int)$lineId)$chosen=$line;
        if($lineId&&!$chosen)throw new CxError('La partida no pertenece al pedido.',404);
        if(!$chosen){foreach($lines as $line)if(bccomp($line['CantidadPendiente'],'0',4)>0){$chosen=$line;break;}if(!$chosen&&$lines)$chosen=$lines[0];}
        if(!$chosen)throw new CxError('El pedido no contiene partidas de concreto.');
        $quote=$this->proposal($company,$o['IdVersion']);$context=$this->operationsContext($company);$plants=$context['plantas'];$plantId=$p['idAlmacen']??($existing['IdAlmacen']??$quote['IdAlmacen']);$plant=null;foreach($plants as $v)if((int)$v['id']===(int)$plantId)$plant=$v;
        if(isset($p['idAlmacen'])&&$p['idAlmacen']!==''&&!$plant)throw new CxError('Seleccione una planta de esta empresa.',403);if(!$plant&&count($plants)===1)$plant=$plants[0];
        $v=$existing?:['IdPedidoPartida'=>$chosen['IdPedidoPartida'],'Cantidad'=>$chosen['CantidadPendiente'],'IdAlmacen'=>$plant['id']??null,'Inicio'=>'','Elemento'=>'','Slump'=>'','UnidadSlump'=>'','Descarga'=>'','MinIda'=>null,'MinDescarga'=>null,'MinRetorno'=>null,'Observaciones'=>$o['Condiciones']['particulares']??''];
        $v['IdPedidoPartida']=$chosen['IdPedidoPartida'];$v['IdAlmacen']=$plant['id']??null;
        $spec=$chosen['Instantanea']['Instantanea']['especificacion']??[];$sources=['Cantidad'=>$existing?'Volumen de la programación que se está revisando':'Saldo de la partida aceptada: contratado menos programaciones activas','IdAlmacen'=>'Planta de la proforma aceptada'];
        if(!$existing){
            if(!empty($spec['Elemento'])){$v['Elemento']=$spec['Elemento'];$sources['Elemento']='Especificación aceptada';}
            if(in_array($spec['Descarga']??'', ['CAIDA_LIBRE','BOMBEADO'],true)){$v['Descarga']=$spec['Descarga'];$sources['Descarga']='Especificación aceptada';}
            if(in_array($spec['UnidadSlump']??'', ['pulg','mm'],true))$v['UnidadSlump']=$spec['UnidadSlump'];
            if(isset($spec['SlumpMin'],$spec['SlumpMax'])&&bccomp($spec['SlumpMin'],$spec['SlumpMax'],4)===0&&bccomp($spec['SlumpMin'],'0',4)>0){$v['Slump']=$spec['SlumpMin'];$sources['Slump']='Slump exacto de la especificación aceptada';}
        }
        $byCode=[];$candidates=$this->run("SELECT * FROM Cx_DisenoVersion WHERE IdEmpresa=? AND IdProducto=? AND IdAlmacen=? AND Estado='APROBADA' ORDER BY Version DESC",[$company,$chosen['IdProducto'],$v['IdAlmacen']])->fetchAll(PDO::FETCH_ASSOC);foreach($candidates as $d){
            if($d['Estado']!=='APROBADA'||(int)$d['IdProducto']!==(int)$chosen['IdProducto']||(int)$d['IdAlmacen']!==(int)$v['IdAlmacen'])continue;
            if(!empty($spec['EdadDias'])&&(int)$d['EdadDias']!==(int)$spec['EdadDias'])continue;
            if($v['Descarga']&&$d['Descarga']!==$v['Descarga'])continue;
            if($v['UnidadSlump']&&$d['UnidadSlump']!==$v['UnidadSlump'])continue;
            if($v['Slump']!==''&&(bccomp($v['Slump'],$d['SlumpMin'],4)<0||bccomp($v['Slump'],$d['SlumpMax'],4)>0))continue;
            if(!isset($byCode[$d['Codigo']])||(int)$d['Version']>(int)$byCode[$d['Codigo']]['Version'])$byCode[$d['Codigo']]=$d;
        }
        $designs=array_values($byCode);if(!$existing&&count($designs)===1){$d=$designs[0];$v['IdDisenoVersion']=$d['IdDisenoVersion'];$sources['IdDisenoVersion']='Único diseño aprobado compatible: '.$d['Codigo'].' v'.$d['Version'];if(!$v['Descarga']){$v['Descarga']=$d['Descarga'];$sources['Descarga']='Diseño técnico aprobado';}if(!$v['UnidadSlump'])$v['UnidadSlump']=$d['UnidadSlump'];if($v['Slump']===''&&bccomp($d['SlumpMin'],$d['SlumpMax'],4)===0&&bccomp($d['SlumpMin'],'0',4)>0){$v['Slump']=$d['SlumpMin'];$sources['Slump']='Slump exacto del diseño aprobado';}}
        $responsibles=[];if($existing)foreach($existing['responsables'] as $u)$responsibles[$u['Area']]=$u['Usuario'];
        foreach(['ALMACEN','TECNICA','PLANTA','DESPACHO'] as $a)if(!isset($responsibles[$a])){$eligible=[];$users=$this->run('SELECT Usuario FROM Cx_AreaUsuario WHERE IdEmpresa=? AND Area=? AND Activo=1',[$company,$a])->fetchAll(PDO::FETCH_COLUMN);foreach($users as $user)try{$this->opUser($company,$user,$a);$eligible[]=$user;}catch(CxError $e){}if(count($eligible)===1)$responsibles[$a]=$eligible[0];}
        if(!$existing){foreach(['MIXER'=>'IdMixer','BOMBA'=>'IdBomba'] as $type=>$key){if($type==='BOMBA'&&$v['Descarga']!=='BOMBEADO')continue;$eligible=array_values(array_filter($context['equipos'],function($e)use($type){return $e['Tipo']===$type&&(int)$e['Activo']&&$e['DocumentoHasta']>=substr(cxNow(),0,10);}));if(count($eligible)===1){$v[$key]=$eligible[0]['IdEquipo'];$sources[$key]='Único equipo activo con documentación vigente; disponibilidad se valida al confirmar';}}
            $drivers=[];foreach($context['conductores'] as $d)if($d['VigenteHasta']>=substr(cxNow(),0,10))try{$this->opUser($company,$d['Usuario'],'DESPACHO');$drivers[]=$d;}catch(CxError $e){}if(count($drivers)===1){$v['Conductor']=$drivers[0]['Usuario'];$sources['Conductor']='Único conductor documentado y autorizado; disponibilidad se valida al confirmar';}
        }
        $v['Responsables']=$responsibles;$v['resistencia']=$spec['Resistencia']??null;
        return ['pedido'=>$o,'partidas'=>$lines,'seleccionada'=>$chosen,'valores'=>$v,'disenosCompatibles'=>$designs,'disenoSeleccionado'=>empty($v['IdDisenoVersion'])?null:$this->design($company,$v['IdDisenoVersion']),'procedencia'=>$sources,'soloConsulta'=>true];
    }
    public function previewSchedule($company,array $p) {
        $this->operational($company,'PROGRAMACION');$defaults=$this->scheduleDefaults($company,$p);$o=$defaults['pedido'];$line=$defaults['seleccionada'];$warehouse=$this->reference($company,'ALMACEN',$p['idAlmacen']??0);$quantity=cxDec($p['cantidad']??'',2,true);$errors=[];
        if(bccomp($quantity,$line['CantidadPendiente'],4)>0)$errors[]='Solo quedan '.cxOpNumber($line['CantidadPendiente']).' m³ pendientes de programar.';
        $start=empty($p['inicio'])?null:cxDateTime($p['inicio']);if(!$start)$errors[]='Indicar fecha y hora solicitadas para preparar el suministro.';
        if(trim((string)($p['elemento']??''))==='')$errors[]='Indicar el elemento de la obra; no está registrado en el pedido.';
        $slump=empty($p['slump'])?'0':cxDec($p['slump'],2,true);if($slump==='0')$errors[]='Indicar el slump solicitado; no se toma el promedio de un rango.';
        $unit=$p['unidadSlump']??'';if(!in_array($unit,['pulg','mm'],true))$errors[]='Seleccionar la unidad del slump.';
        $discharge=$p['descarga']??'';if(!in_array($discharge,['CAIDA_LIBRE','BOMBEADO'],true))$errors[]='Confirmar el tipo de descarga.';
        $plant=$this->run('SELECT * FROM Cx_PlantaOperacion WHERE IdEmpresa=? AND IdAlmacen=?',[$company,$warehouse])->fetch(PDO::FETCH_ASSOC);
        $mixer=$this->scheduleEquipment($company,$p['idMixer']??null,'MIXER');$pump=$this->scheduleEquipment($company,$p['idBomba']??null,'BOMBA');$driver=empty($p['conductor'])?null:$this->opUser($company,$p['conductor'],'DESPACHO');
        $times=[];foreach(['minIda','minDescarga','minRetorno'] as $k){$v=$p[$k]??null;if($v===null||$v==='')$times[$k]=null;else{if(filter_var($v,FILTER_VALIDATE_INT)===false||(int)$v<($k==='minDescarga'?1:0)||(int)$v>10080)throw new CxError('Revise los minutos de viaje y descarga.');$times[$k]=(int)$v;}}
        $loads=$this->scheduleLoads($quantity,$start,$plant,$mixer,$driver,$times);$responsibles=[['Area'=>'PROGRAMACION','Usuario'=>$this->user]];foreach(['ALMACEN','TECNICA','PLANTA','DESPACHO'] as $a)if(!empty($p['responsables'][$a]))$responsibles[]=['Area'=>$a,'Usuario'=>$p['responsables'][$a]];
        $plan=['IdProgramacion'=>(int)($p['idProgramacion']??0),'IdPedido'=>$o['IdPedido'],'IdPedidoPartida'=>$line['IdPedidoPartida'],'IdAlmacen'=>$warehouse,'Inicio'=>$start?:'','Fin'=>$loads['fin'],'Cantidad'=>$quantity,'Slump'=>$slump,'UnidadSlump'=>$unit,'Descarga'=>$discharge,'IdDisenoVersion'=>empty($p['idDisenoVersion'])?null:cxId($p['idDisenoVersion']),'IdMixer'=>$mixer['IdEquipo']??null,'IdBomba'=>$pump['IdEquipo']??null,'Conductor'=>$driver['usuario']??null,'Estado'=>'BORRADOR','Instantanea'=>['datos'=>['planta'=>$plant,'mixer'=>$mixer]],'responsables'=>$responsibles,'cargas'=>[]];
        foreach($loads['cargas'] as $c)$plan['cargas'][]=['IdCarga'=>null,'Inicio'=>$c['inicio'],'FinCarga'=>$c['finCarga'],'Llegada'=>$c['llegada'],'FinDescarga'=>$c['finDescarga'],'Retorno'=>$c['retorno'],'CantidadNominal'=>$c['cantidad']];
        $requirements=$this->scheduleRequirements($company,$plan);return ['soloConsulta'=>true,'cantidad'=>$quantity,'pendientePedido'=>$line['CantidadPendiente'],'cargas'=>$loads['cargas'],'fin'=>$loads['fin'],'materiales'=>$requirements['materiales'],'pendientes'=>array_values(array_unique(array_merge($errors,$requirements['pendientes'])))];
    }
}
