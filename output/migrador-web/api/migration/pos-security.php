<?php
// Only the authenticated ERP session supplies identity and POS permissions.
function aliPosCapabilities($db, $username) {
    $stmt=$db->prepare('SELECT Usuario,IdUsuarioPerfil,IdAlmacen FROM Seg_Usuario WHERE LOWER(Usuario)=LOWER(?) AND (Anulado IS NULL OR Anulado<>1) LIMIT 1');
    $stmt->execute([$username]);$user=$stmt->fetch(PDO::FETCH_ASSOC);
    if(!$user)return ['read'=>false,'write'=>false,'admin'=>false,'warehouse'=>0,'checkout'=>false];
    $admin=(int)$user['IdUsuarioPerfil']===1;
    $read=$admin;$write=$admin;
    if(!$admin){
        $stmt=$db->prepare("SELECT CAST(p.Lectura AS UNSIGNED) AS can_read,CAST(p.Escritura AS UNSIGNED) AS can_write FROM Seg_UsuarioModulo_has_UsuarioPerfil p JOIN Seg_UsuarioModulo m ON m.IdUsuarioModulo=p.IdUsuarioModulo WHERE p.IdUsuarioPerfil=? AND LOWER(m.UsuarioModulo)='v_ventaform.php' AND (p.Anulado IS NULL OR p.Anulado<>1) LIMIT 1");
        $stmt->execute([(int)$user['IdUsuarioPerfil']]);$permission=$stmt->fetch(PDO::FETCH_ASSOC)?:[];
        $read=(int)($permission['can_read']??0)===1;$write=$read&&(int)($permission['can_write']??0)===1;
    }
    return ['read'=>$read,'write'=>$write,'admin'=>$admin,'warehouse'=>(int)$user['IdAlmacen'],'checkout'=>$write];
}

function aliValidatePosAction($body,$capabilities) {
    $reads=['greet','encouragement','help','focus','barcodeDraft','methods','total','search','learn','cancel'];
    $writes=['barcode','selectCode','add','accept','customer','documentType','method','pay','confirm'];
    if(!is_array($body)||array_diff(array_keys($body),['action','warehouse']))return [422,'Parámetros de acción inválidos.'];
    $action=$body['action']??null;$warehouse=$body['warehouse']??0;
    if(!is_string($action)||!is_int($warehouse)||$warehouse<0)return [422,'Parámetros de acción inválidos.'];
    if(!in_array($action,array_merge($reads,$writes),true))return [422,'Acción de ALI no permitida.'];
    if(empty($capabilities['read']))return [403,'No tienes permiso de lectura del punto de venta.'];
    if(in_array($action,$writes,true)&&empty($capabilities['write']))return [403,'No tienes permiso de escritura del punto de venta.'];
    if($warehouse>0&&empty($capabilities['admin'])&&$warehouse!==(int)$capabilities['warehouse'])return [403,'Esta cuenta no puede operar en ese almacén.'];
    return [200,''];
}
