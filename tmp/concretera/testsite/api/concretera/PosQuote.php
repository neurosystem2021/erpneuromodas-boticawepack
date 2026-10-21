<?php
trait CxPosQuote {
    public function createPosProposal($company,array $p) {
        $this->permission($company,'write');
        $issuer=cxId($p['idEmpresaEmisora']??0);$this->assertCommercialIssuer($company,$issuer);
        $clientId=cxId($p['idCliente']??0);$document=cxText($p['documento']??'',11,true);
        if(!preg_match('/^(?:\d{8}|\d{11})$/D',$document)||preg_match('/^0+$/D',$document))throw new CxError('Seleccione al cliente por RUC o DNI en el punto de venta antes de cotizar.');
        $client=$this->run('SELECT IdCliente,DniRuc FROM Ve_DocVentaCliente WHERE IdCliente=? AND COALESCE(Anulado+0,0)=0',[$clientId])->fetch(PDO::FETCH_ASSOC);
        if(!$client||trim($client['DniRuc'])!==$document)throw new CxError('El cliente cambió o no está registrado. Vuelva a seleccionarlo por RUC o DNI.');
        $date=cxDate($p['fechaEmision']??'');$days=cxId($p['diasValidos']??15);if($days>3650)throw new CxError('Revise la vigencia de la proforma.');
        $until=(new DateTimeImmutable($date,new DateTimeZone('America/Lima')))->modify('+'.$days.' days')->format('Y-m-d');
        $plants=$this->catalog($company,'almacenes');$plant=!empty($p['idPlanta'])?cxId($p['idPlanta']):(count($plants)===1?(int)$plants[0]['id']:0);
        if(!$plant)throw new CxError('Seleccione la planta configurada de esta empresa para cotizar.');
        $items=$p['productos']??[];if(!is_array($items)||!count($items)||count($items)>100)throw new CxError('Agregue entre 1 y 100 productos a la cotización.');
        $lines=[];
        foreach($items as $item){
            if(!is_array($item))throw new CxError('Producto inválido.');
            $id=$this->reference($company,'PRODUCTO',$item['idProducto']??0);$offer=$this->productOffer($company,$id);
            if(!$offer||$offer['Moneda']!=='PEN')throw new CxError('El producto no tiene tarifa comercial en soles para esta empresa.');
            if(!empty($item['esPresentacion'])||!empty($item['sinTotal']))throw new CxError('Cotice estos productos en m³ con cantidad y total habilitados.');
            $product=$this->run('SELECT Producto FROM Gen_Producto WHERE IdProducto=? AND COALESCE(Anulado+0,0)=0',[$id])->fetchColumn();if(!$product)throw new CxError('El producto ya no está activo.');
            $q=cxDec($item['cantidad']??'',4,true);$price=cxDec($item['precio']??'',6,true);$discount=cxDec($item['descuento']??'0',2);
            $raw=bcmul($q,$price,12);if(bccomp($discount,$raw,12)>0)throw new CxError('El descuento supera el importe del producto.');
            $percentage=bcadd(bcdiv(bcmul($discount,'100',12),$raw,8),'0',4);
            $description=cxText($item['descripcion']??'',1500);
            $lines[]=['tipo'=>$offer['Clase'],'idProducto'=>$id,'servicio'=>$offer['Servicio'],'unidad'=>'m3','descripcion'=>$product.($description!==''?"\n".$description:''),'cantidad'=>$q,'precioUnitario'=>$price,'descuentoPct'=>$percentage,'descuentoImporte'=>$discount];
        }
        // The selected client already comes from the authorized ERP POS. Link just that identity.
        $this->atomic($company,function()use($company,$clientId){
            $owner=$this->run("SELECT IdEmpresa FROM Cx_ReferenciaERP WHERE Entidad='CLIENTE' AND IdERP=? FOR UPDATE",[$clientId])->fetchColumn();
            if($owner!==false&&(int)$owner!==(int)$company)throw new CxError('El cliente está vinculado a otra empresa de gestión.',403);
            if($owner===false){$this->run("INSERT INTO Cx_ReferenciaERP(IdEmpresa,Entidad,IdERP,FechaReg,UsuarioReg) VALUES(?,'CLIENTE',?,?,?)",[$company,$clientId,cxNow(),$this->user]);$this->audit($company,'referencia_cliente',$clientId,'VINCULAR_DESDE_POS',null,['IdERP'=>$clientId],'Cliente existente elegido desde el punto de venta; no se duplica su ficha ni se consulta otro proveedor.');}
        });
        $credit=!empty($p['credito']);
        $payload=['idCliente'=>$clientId,'idEmpresaEmisora'=>$issuer,'idObra'=>null,'idAlmacen'=>$plant,'tipo'=>'PRESUPUESTO','moneda'=>'PEN','fechaEmision'=>$date,'vigenteHasta'=>$until,'impuesto'=>'GRAVADA','preciosIncluyenImpuesto'=>true,'totalEsperado'=>cxDec($p['total']??'',2),'condiciones'=>['trasladoIncluido'=>true,'bombeoIncluido'=>false,'anticipacionHoras'=>'48','permanenciaMinutos'=>'60','ensayosDias'=>[7,14,28],'particulares'=>cxText($p['observaciones']??'',6000)],'pago'=>['modalidad'=>$credit?'CREDITO':'CONTADO','vencimiento'=>$credit?cxDate($p['vencimiento']??''):null,'liberacion'=>'POR_CONFIRMAR'],'partidas'=>$lines,'motivo'=>'Cotización desde punto de venta; no implica compra confirmada.','idempotencia'=>$p['idempotencia']??'','origenPOS'=>['IdAlmacenERP'=>(int)($p['idAlmacenPOS']??0),'total'=>cxDec($p['total'],2),'canal'=>'POS_EXISTENTE']];
        // Validate the quote before registering an optional customer-supplied work.
        $this->commercialInput($company,$payload);
        if(!empty($p['idObra'])){
            $work=$this->work($company,cxId($p['idObra']));if((int)$work['IdCliente']!==$clientId||!(int)$work['Activo'])throw new CxError('Seleccione una obra activa del mismo cliente.');$payload['idObra']=$work['IdObra'];
        }elseif(!empty($p['obraNueva'])){
            $input=(array)$p['obraNueva'];$input['idCliente']=$clientId;$input['codigo']='POS-'.substr(hash('sha256',cxText($p['idempotencia']??'',100,true)),0,30);
            $existing=$this->run('SELECT * FROM Cx_Obra WHERE IdEmpresa=? AND Codigo=?',[$company,$input['codigo']])->fetch(PDO::FETCH_ASSOC);
            if($existing){foreach(['nombre'=>'Nombre','direccion'=>'Direccion','contacto'=>'Contacto','telefono'=>'Telefono'] as $from=>$to)if(trim((string)($input[$from]??''))!==(string)$existing[$to])throw new CxError('La obra del reintento tiene otros datos. Revise la cotización.',409);if((int)$existing['IdCliente']!==$clientId)throw new CxError('La obra del reintento pertenece a otro cliente.',409);$work=$existing;}
            else $work=$this->createWork($company,$input);
            $payload['idObra']=$work['IdObra'];
        }
        $created=$this->createProposal($company,$payload);$quote=$this->proposal($company,$created['idVersion']);
        if($quote['Estado']==='BORRADOR')$quote=$this->issueProposal($company,['idVersion'=>$quote['IdVersion'],'revision'=>$quote['Revision']]);
        return array_merge($created,['estado'=>$quote['Estado'],'total'=>$quote['Total'],'moneda'=>$quote['Moneda'],'idEmpresaEmisora'=>$quote['IdEmpresaEmisora']]);
    }
}
