<?php
trait CxFiscalSettings {
    public function issuerModules() {
        return [
            'comercial'=>['nombre'=>'Proformas y pedidos de concretera','estado'=>'DISPONIBLE'],
            'inventario'=>['nombre'=>'Inventario por empresa y planta','estado'=>'PENDIENTE_ADAPTADOR'],
            'produccion'=>['nombre'=>'Recetas, preparación y consumo','estado'=>'PENDIENTE_ETAPA'],
            'despacho'=>['nombre'=>'Guía por carga y recepción','estado'=>'PENDIENTE_ETAPA'],
            'facturacion'=>['nombre'=>'Facturación electrónica y liquidación','estado'=>'CONFIGURACION_DISPONIBLE'],
            'caja'=>['nombre'=>'Caja, bancos y adelantos por empresa','estado'=>'PENDIENTE_ADAPTADOR'],
            'reportes'=>['nombre'=>'Reportes operativos por empresa','estado'=>'PENDIENTE_ETAPA']
        ];
    }
    private function fiscalMeta($id) {
        $s=$this->run('SELECT MetaKey,MetaValue FROM Gen_EmpresaConfig WHERE IdEmpresa=? ORDER BY IdEmpresaConfig',[$id]);
        return $s->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    private function fiscalMetaSave($id,$key,$value) {
        $ids=$this->run('SELECT IdEmpresaConfig FROM Gen_EmpresaConfig WHERE IdEmpresa=? AND MetaKey=? ORDER BY IdEmpresaConfig',[$id,$key])->fetchAll(PDO::FETCH_COLUMN);
        if($ids)$this->run('UPDATE Gen_EmpresaConfig SET MetaValue=? WHERE IdEmpresa=? AND MetaKey=?',[$value,$id,$key]);
        else $this->run('INSERT INTO Gen_EmpresaConfig(IdEmpresa,MetaKey,MetaValue) VALUES(?,?,?)',[$id,$key,$value]);
    }
    public function fiscalSettings($company,$id) {
        $this->permission($company,'admin');$issuer=$this->issuer($company,$id);$id=(int)$issuer['IDEMPRESA'];
        $row=$this->run('SELECT * FROM Cx_EmisorConfig WHERE IdEmpresa=?',[$id])->fetch(PDO::FETCH_ASSOC);
        $m=$this->fiscalMeta($id);$modules=json_decode($row['ModulosJson']??'{}',true)?:[];
        foreach($this->issuerModules() as $key=>$definition)if(!array_key_exists($key,$modules))$modules[$key]=in_array($key,['comercial','facturacion'],true);
        return ['idEmpresaEmisora'=>$id,'ruc'=>$issuer['RUC'],'razonSocial'=>$issuer['RAZONSOCIAL'],'revision'=>(int)($row['Revision']??0),
            'entorno'=>$m['sunat_entorno']??'FE_BETA','usuarioSol'=>$m['usuario_sol']??'','nombreComercial'=>$m['nombre_comercial']??'',
            'ubigeo'=>$m['UBIGEO']??'','tieneClaveSol'=>!empty($m['clave_sol']),'tieneClaveCertificado'=>isset($m['clave_certificado']),
            'tieneCertificado'=>!empty($m['certificado']),'certificadoNombre'=>$m['certificado_nombre']??'',
            'certificadoVence'=>$m['certificado_vence']??'','certificadoRucValidado'=>($m['certificado_ruc_validado']??'')===$issuer['RUC'],
            'series'=>json_decode($row['SeriesJson']??'[]',true)?:[], 'modulos'=>$modules,'catalogoModulos'=>$this->issuerModules(),
            'emisionHabilitada'=>false,'estadoIntegracion'=>'CONFIGURACION_POR_EMISOR; ADAPTADOR_DE_EMISION_PENDIENTE'];
    }
    private function certificateData($base64,$password,$ruc) {
        $binary=base64_decode($base64,true);
        if($binary===false||strlen($binary)>1048576||strlen($binary)<50)throw new CxError('El certificado debe ser PFX/P12 de hasta 1 MB.');
        if(!function_exists('openssl_pkcs12_read'))throw new CxError('El servidor requiere OpenSSL para validar el certificado.',503);
        $certs=[];
        if(!@openssl_pkcs12_read($binary,$certs,$password)||empty($certs['pkey'])||empty($certs['cert']))throw new CxError('El certificado o su contraseña no son válidos, o no incluye clave privada.');
        if(!@openssl_x509_check_private_key($certs['cert'],$certs['pkey']))throw new CxError('La clave privada no corresponde al certificado.');
        $info=openssl_x509_parse($certs['cert']);$now=time();
        if(!$info||$info['validFrom_time_t']>$now||$info['validTo_time_t']<$now)throw new CxError('El certificado no está vigente.');
        $subject=json_encode($info['subject']??[]);
        if(!preg_match('/(?<!\d)'.preg_quote($ruc,'/').'(?!\d)/',$subject))throw new CxError('El RUC del certificado no corresponde a la empresa seleccionada.');
        return ['certificado'=>base64_encode($binary),'certificado_vence'=>gmdate('Y-m-d',$info['validTo_time_t']),'certificado_ruc_validado'=>$ruc];
    }
    public function saveFiscalSettings($company,array $p) {
        $this->permission($company,'admin');$id=cxId($p['idEmpresaEmisora']??0);$issuer=$this->issuer($company,$id);
        $before=$this->fiscalSettings($company,$id);$old=$this->fiscalMeta($id);
        $meta=['sunat_entorno'=>cxChoice($p['entorno']??'',['FE_BETA','FE_PRODUCCION'],'el entorno'),
            'usuario_sol'=>cxText($p['usuarioSol']??'',100),'nombre_comercial'=>cxText($p['nombreComercial']??'',255),
            'UBIGEO'=>cxText($p['ubigeo']??'',6)];
        if($meta['UBIGEO']!==''&&!preg_match('/^\d{6}$/D',$meta['UBIGEO']))throw new CxError('El ubigeo debe tener 6 dígitos.');
        foreach(['claveSol'=>'clave_sol','claveCertificado'=>'clave_certificado'] as $input=>$key){
            if(isset($p[$input])&&$p[$input]!==''){$v=(string)$p[$input];if(strlen($v)>2000)throw new CxError('La clave excede el tamaño permitido.');$meta[$key]=$v;}
        }
        $cert=$p['certificadoBase64']??'';
        if($cert!==''||isset($meta['clave_certificado'])){
            $b64=$cert!==''?$cert:($old['certificado']??'');
            if($b64==='')throw new CxError('Seleccione el certificado para validar su contraseña.');
            $validated=$this->certificateData($b64,$meta['clave_certificado']??($old['clave_certificado']??''),$issuer['RUC']);
            $meta=array_merge($meta,$validated);
            if($cert!==''){$name=cxText($p['certificadoNombre']??'',180,true);if(!preg_match('/\.(pfx|p12)$/i',$name))throw new CxError('Seleccione un archivo PFX o P12.');$meta['certificado_nombre']=basename(str_replace('\\','/',$name));}
        }
        $modules=$p['modulos']??null;if(!is_array($modules))throw new CxError('Revise la selección de módulos.');
        foreach($modules as $key=>$value)if(!isset($this->issuerModules()[$key])||!is_bool($value))throw new CxError('Hay un módulo o selección no válidos.');
        foreach($this->issuerModules() as $key=>$value)if(!array_key_exists($key,$modules))throw new CxError('Falta indicar el estado de un módulo.');
        $series=$p['series']??[];if(!is_array($series)||count($series)>20)throw new CxError('Revise las series de comprobantes.');
        $seen=[];$normalized=[];
        foreach($series as $row){
            if(!is_array($row))throw new CxError('Serie inválida.');
            $type=cxChoice($row['tipo']??'',['01','03','07','08'],'el tipo de comprobante');$serie=strtoupper(cxText($row['serie']??'',4,true));
            if(!preg_match('/^[FB][A-Z0-9]{3}$/D',$serie)||($type==='01'&&$serie[0]!=='F')||($type==='03'&&$serie[0]!=='B'))throw new CxError('Revise el formato de la serie: F para factura y B para boleta; 4 caracteres.');
            $next=cxId($row['siguiente']??0);if($next>99999999)throw new CxError('El próximo número excede 8 dígitos.');
            $key=$type.':'.$serie;if(isset($seen[$key]))throw new CxError('La serie está repetida.');$seen[$key]=true;
            $normalized[]=['tipo'=>$type,'serie'=>$serie,'siguiente'=>$next];
        }
        return $this->atomic($company,function()use($company,$p,$id,$before,$meta,$modules,$normalized){
            $row=$this->run('SELECT Revision FROM Cx_EmisorConfig WHERE IdEmpresa=? FOR UPDATE',[$id])->fetchColumn();
            if((int)($row===false?0:$row)!==(int)($p['revision']??-1))throw new CxError('La configuración cambió. Vuelva a cargar antes de guardar.',409);
            if($row===false)$this->run('INSERT INTO Cx_EmisorConfig VALUES(?,1,?,?,?,?)',[$id,cxJson($modules),cxJson($normalized),$this->user,cxNow()]);
            else $this->run('UPDATE Cx_EmisorConfig SET Revision=Revision+1,ModulosJson=?,SeriesJson=?,UsuarioMod=?,FechaMod=? WHERE IdEmpresa=?',[cxJson($modules),cxJson($normalized),$this->user,cxNow(),$id]);
            foreach($meta as $key=>$value)$this->fiscalMetaSave($id,$key,$value);
            $after=$this->fiscalSettings($company,$id);
            $this->audit($company,'configuracion_emisor',$id,'CONFIGURAR_FISCAL_Y_MODULOS',$before,$after,cxText($p['motivo']??'',500,true));
            return $after;
        });
    }
    public function assertCommercialIssuer($company,$id) {
        $this->issuer($company,$id);
        $raw=$this->run('SELECT ModulosJson FROM Cx_EmisorConfig WHERE IdEmpresa=?',[$id])->fetchColumn();
        $modules=$raw===false?[]:(json_decode($raw,true)?:[]);
        if(array_key_exists('comercial',$modules)&&!$modules['comercial'])throw new CxError('El módulo de proformas y pedidos está deshabilitado para esta empresa.',403);
    }
}
