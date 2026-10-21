<?php
trait CxAcceptance {
    public function acceptanceResponsible($company,$allowDisabled=false) {
        $access=$this->permission($company,'accept',$allowDisabled);
        $user=$this->run('SELECT Usuario,NombreUsuario FROM Seg_Usuario WHERE Usuario=? AND COALESCE(Anulado+0,0)=0',[$this->user])->fetch(PDO::FETCH_ASSOC);
        if(!$user)throw new CxError('El usuario responsable ya no está activo.',403);
        return ['usuario'=>$user['Usuario'],'nombre'=>trim((string)$user['NombreUsuario'])?:$user['Usuario'],'rol'=>$access['role'],'area'=>'COMERCIAL','areaNombre'=>'Comercial y ventas','proceso'=>'Confirmación de proforma y pedido'];
    }
    private function acceptanceRecord($company,array $quote,$contact,$note,array $selections) {
        $responsible=$this->acceptanceResponsible($company);$code='CW-'.$quote['Anio'].'-'.str_pad($quote['Numero'],5,'0',STR_PAD_LEFT);$date=cxNow();
        $record=['responsable'=>$responsible,'fecha'=>$date,'proforma'=>['idPropuesta'=>(int)$quote['IdPropuesta'],'idVersion'=>(int)$quote['IdVersion'],'numero'=>$code,'version'=>(int)$quote['Version'],'idProformaERP'=>(int)$quote['IdProforma'],'huella'=>$quote['Huella']],'empresaEmisora'=>['id'=>(int)$quote['IdEmpresaEmisora'],'ruc'=>$quote['Instantanea']['empresa']['RUC']],'cliente'=>['id'=>(int)$quote['IdCliente'],'nombre'=>$quote['Instantanea']['cliente']['Cliente'],'documento'=>$quote['Instantanea']['cliente']['DniRuc']],'contactoCliente'=>$contact?:null,'observaciones'=>$note,'selecciones'=>$selections,'tipo'=>'ACEPTACION_REGISTRADA_POR_USUARIO_AUTORIZADO'];
        $evidence='Proforma '.$code.' - versión '.$quote['Version'].' - ERP '.$quote['IdProforma']."\n".'Empresa emisora: '.$record['empresaEmisora']['ruc']."\n".'Cliente: '.$record['cliente']['nombre'].' - '.$record['cliente']['documento']."\n".'Área responsable: '.$responsible['areaNombre']."\n".'Confirmado por: '.$responsible['nombre'].' ('.$responsible['usuario'].') - permiso '.$responsible['rol']."\n".'Contacto del cliente: '.($contact?:'No indicado')."\n".'Fecha de registro: '.$date.' America/Lima';
        if($note!=='')$evidence.="\nObservaciones: ".$note;
        return ['snapshot'=>$record,'evidencia'=>$evidence];
    }
}
