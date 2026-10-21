<?php
require_once __DIR__.'/Domain.php';
// Instalación explícita por CLI. Sin endpoint web ni copia de credenciales.
function cxRegisterWuanukoGroup(PDO $db,$user,$mode='OPERACION') {
    cxChoice($mode,['PRUEBA','OPERACION'],'el modo');$lock='cx_group_'.substr(hash('sha256',(string)$db->query('SELECT DATABASE()')->fetchColumn()),0,35);$s=$db->prepare('SELECT GET_LOCK(?,10)');$s->execute([$lock]);if((int)$s->fetchColumn()!==1)throw new CxError('Otra configuración del grupo está ejecutándose.',409);
    try{
        $companies=[['nombre'=>'CONCRETERA WUANUKO MIX EIRL.','ruc'=>'20573152531'],['nombre'=>'CONCRETERA WUANUKO MIX FULL E.I.R.L.','ruc'=>'20602664989']];$ids=[];
        $columns=[];foreach($db->query('SHOW COLUMNS FROM GEN_EMPRESA')->fetchAll(PDO::FETCH_ASSOC) as $column)$columns[$column['Field']]=$column;
        foreach($companies as $company){$s=$db->prepare('SELECT IDEMPRESA FROM GEN_EMPRESA WHERE RUC=? ORDER BY IDEMPRESA');$s->execute([$company['ruc']]);$matches=$s->fetchAll(PDO::FETCH_COLUMN);if(count($matches)>1)throw new CxError('Hay más de una empresa con RUC '.$company['ruc'].'. Resuelva el duplicado antes de continuar.',409);
            if($matches)$ids[]=(int)$matches[0];else{$values=['RAZONSOCIAL'=>$company['nombre'],'RUC'=>$company['ruc'],'DIRECCION'=>'','IGV'=>null,'TELEFONO'=>'','EMAIL'=>''];foreach(['CONSULTARUCURL','CONSULTARUCTOKEN'] as $key)if(isset($columns[$key]))$values[$key]='';$s=$db->prepare('INSERT INTO GEN_EMPRESA('.implode(',',array_keys($values)).') VALUES('.implode(',',array_fill(0,count($values),'?')).')');$s->execute(array_values($values));$ids[]=(int)$db->lastInsertId();}
        }
        $service=new CxService($db,$user,true);$owner=$ids[0];$s=$db->prepare('SELECT * FROM Cx_Configuracion WHERE IdEmpresa=?');$s->execute([$owner]);if(!$s->fetch())$service->configure($owner,['activo'=>true,'modo'=>$mode,'confirmacionEmpresa'=>$companies[0]['ruc'],'motivo'=>'Empresas y gestión compartida autorizadas por el usuario el 19/10/2026.']);
        $db->beginTransaction();try{
            $s=$db->prepare('SELECT * FROM Cx_Grupo WHERE Codigo=? FOR UPDATE');$s->execute(['WUANUKO_MIX']);$group=$s->fetch(PDO::FETCH_ASSOC);
            if($group&&(int)$group['IdEmpresaGestion']!==$owner)throw new CxError('El grupo existente tiene otra empresa de gestión; requiere revisión.',409);
            $created=false;if(!$group){$s=$db->prepare('INSERT INTO Cx_Grupo(Codigo,Nombre,IdEmpresaGestion,LogoPath,UsuarioReg,FechaReg) VALUES(?,?,?,?,?,?)');$s->execute(['WUANUKO_MIX','WUANUKO MIX · GESTIÓN COMPARTIDA',$owner,'/resources/images/wuanuko-mix-logo.jpeg',$user,cxNow()]);$group=['IdGrupo'=>(int)$db->lastInsertId()];$created=true;}
            foreach($ids as $id){$s=$db->prepare('SELECT IdGrupo FROM Cx_GrupoEmpresa WHERE IdEmpresa=?');$s->execute([$id]);$existing=$s->fetchColumn();if($existing!==false&&(int)$existing!==(int)$group['IdGrupo'])throw new CxError('Una empresa pertenece a otro grupo.',409);$s=$db->prepare('INSERT IGNORE INTO Cx_GrupoEmpresa(IdGrupo,IdEmpresa,UsuarioMod,FechaMod) VALUES(?,?,?,?)');$s->execute([$group['IdGrupo'],$id,$user,cxNow()]);if($s->rowCount())$created=true;}
            if($created)$service->audit($owner,'grupo',$group['IdGrupo'],'VINCULAR_EMISORES',null,['empresas'=>$companies,'ids'=>$ids],'Gestión compartida autorizada; identidades fiscales separadas. No se copian datos tributarios de NEUROSYSTEM.');$db->commit();
        }catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
        return ['idEmpresaGestion'=>$owner,'emisores'=>$service->issuers($owner),'grupo'=>$service->group($owner),'created'=>$created];
    }finally{$s=$db->prepare('SELECT RELEASE_LOCK(?)');$s->execute([$lock]);}
}
