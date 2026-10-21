<?php
trait CxCompanyGroup {
    public function group($company) {
        $r=$this->run('SELECT * FROM Cx_Grupo WHERE IdEmpresaGestion=? AND Activo=1',[cxId($company)])->fetch(PDO::FETCH_ASSOC);return $r?:null;
    }
    public function issuers($company) {
        $group=$this->group($company);
        if(!$group){$r=$this->company($company);$r['RevisionEmisor']=0;$r['TasaImpuestoPct']=null;$r['LogoPath']='';return [$r];}
        $rows=$this->run('SELECT e.IDEMPRESA,e.RAZONSOCIAL,e.RUC,e.DIRECCION,e.IGV,e.TELEFONO,e.EMAIL,g.DireccionFiscal,g.Telefono AS TelefonoPerfil,g.Correo,g.TasaImpuestoPct,g.Revision AS RevisionEmisor FROM Cx_GrupoEmpresa g INNER JOIN GEN_EMPRESA e ON e.IDEMPRESA=g.IdEmpresa WHERE g.IdGrupo=? AND g.Activo=1 ORDER BY e.IDEMPRESA',[$group['IdGrupo']])->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as &$r){foreach(['DireccionFiscal'=>'DIRECCION','TelefonoPerfil'=>'TELEFONO','Correo'=>'EMAIL','TasaImpuestoPct'=>'IGV'] as $from=>$to)if($r[$from]!==null)$r[$to]=$r[$from];$r['LogoPath']=$group['LogoPath'];}unset($r);return $rows;
    }
    public function issuer($company,$id=null) {
        if($id===null&&$this->group($company))throw new CxError('Seleccione la empresa que emitirá la proforma y la factura.');
        $id=$id===null?cxId($company):cxId($id);
        foreach($this->issuers($company) as $row)if((int)$row['IDEMPRESA']===$id)return $row;
        throw new CxError('La empresa emisora no pertenece al grupo autorizado.',403);
    }
    public function issuerProfile($company,array $p) {
        $this->permission($company,'admin');$id=cxId($p['idEmpresaEmisora']??0);$before=$this->issuer($company,$id);$g=$this->group($company);if(!$g)throw new CxError('Esta empresa no tiene un perfil de emisor vinculado.');
        $email=cxText($p['correo']??'',255);if($email!==''&&!filter_var($email,FILTER_VALIDATE_EMAIL))throw new CxError('Revise el correo del emisor.');
        $rate=($p['tasaImpuestoPct']??'')===''?null:cxDec($p['tasaImpuestoPct'],4,false,'100');
        return $this->atomic($company,function()use($company,$p,$id,$before,$g,$email,$rate){$s=$this->run('UPDATE Cx_GrupoEmpresa SET DireccionFiscal=?,Telefono=?,Correo=?,TasaImpuestoPct=?,Revision=Revision+1,UsuarioMod=?,FechaMod=? WHERE IdGrupo=? AND IdEmpresa=? AND Revision=?',[cxText($p['direccion']??'',3000),cxText($p['telefono']??'',100),$email,$rate,$this->user,cxNow(),$g['IdGrupo'],$id,(int)($p['revision']??0)]);if($s->rowCount()!==1)throw new CxError('El perfil del emisor cambió; vuelva a cargar.',409);$after=$this->issuer($company,$id);$this->audit($company,'emisor',$id,'ACTUALIZAR_PERFIL',$before,$after,cxText($p['motivo']??'',500,true));return $after;});
    }
    public function billingContext($company,$kind,$id) {
        cxChoice($kind,['proposal','order'],'el documento');$doc=$kind==='proposal'?$this->proposal($company,$id):$this->order($company,$id);
        $fiscal=$doc['Instantanea']['empresa'];$emitter=(int)($doc['IdEmpresaEmisora']?:$fiscal['IDEMPRESA']);
        return ['idEmpresaGestion'=>(int)$doc['IdEmpresa'],'idEmpresaEmisora'=>$emitter,'razonSocial'=>$fiscal['RAZONSOCIAL'],'ruc'=>$fiscal['RUC'],'direccionFiscal'=>$fiscal['DIRECCION'],'origen'=>$kind,'idOrigen'=>(int)$id,'idProforma'=>$kind==='proposal'?(int)$doc['IdProforma']:null,'idPreOrden'=>$kind==='order'?(int)$doc['IdPreOrden']:null,'puedeEmitir'=>false,'estado'=>'PENDIENTE_INTEGRACION_MULTIEMISOR','mensaje'=>'La factura debe conservar este emisor. La liquidación parcial y la emisión SUNAT requieren configurar sus series, certificado y credenciales propias. El flujo genérico permanece bloqueado.'];
    }
    private function issuerListing(array $rows) {
        foreach($rows as &$r){$s=json_decode($r['Instantanea'],true);$r['EmisorNombre']=$s['empresa']['RAZONSOCIAL']??'';$r['EmisorRuc']=$s['empresa']['RUC']??'';$r['Cliente']=$s['cliente']['Cliente']??'';$r['DniRuc']=$s['cliente']['DniRuc']??'';$r['ResponsableNombre']=$s['aceptacion']['responsable']['nombre']??$r['UsuarioReg']??'';$r['AreaResponsable']=$s['aceptacion']['responsable']['areaNombre']??'';$r['ProformaOrigen']=$s['aceptacion']['proforma']['numero']??'';}unset($r);return $rows;
    }
}
