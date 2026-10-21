<?php
function nstLoginTable(PDO $db, $expected) {
    $statement=$db->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE BINARY TABLE_SCHEMA=BINARY DATABASE() AND LOWER(TABLE_NAME)=LOWER(?)');
    $statement->execute(array($expected));$names=$statement->fetchAll(PDO::FETCH_COLUMN);
    if(in_array($expected,$names,true))$name=$expected;
    elseif(count($names)===1)$name=$names[0];
    else throw new RuntimeException('Required authentication table unavailable.');
    return '`'.str_replace('`','``',$name).'`';
}
function nstLoginResponse($method, array $input, callable $connect, $now=null) {
    $now=$now??time();$attempts=(int)($_SESSION['failed_attempts']??0);
    if($method!=='POST')return array(405,array('isLogin'=>false,'message'=>'Utilice el formulario de ingreso.'));
    if($attempts>=5&&$now-(int)($_SESSION['last_attempt_time']??0)<300)return array(429,array('isLogin'=>false,'message'=>'Demasiados intentos. Espere unos minutos antes de volver a ingresar.'));
    if($attempts>=5)$attempts=$_SESSION['failed_attempts']=0;
    $user=trim((string)($input['nameUser']??''));$pass=(string)($input['namePass']??'');
    if($user===''||$pass==='')return array(422,array('isLogin'=>false,'message'=>'Ingrese usuario y contraseña.'));
    try {
        $db=$connect();$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $users=nstLoginTable($db,'Seg_Usuario');
        $statement=$db->prepare('SELECT Usuario,IdUsuarioPerfil,IdAlmacen FROM '.$users.' WHERE Usuario=? AND Password=SHA2(?,256) AND COALESCE(Anulado,0)=0 LIMIT 1');
        $statement->execute(array($user,$pass));$account=$statement->fetch(PDO::FETCH_ASSOC);
        if(!$account){$_SESSION['failed_attempts']=$attempts+1;$_SESSION['last_attempt_time']=$now;return array(401,array('isLogin'=>false,'message'=>'Usuario o contraseña incorrectos.','cantidad_res'=>max(0,4-$attempts)));}
        $modules=nstLoginTable($db,'Seg_UsuarioModulo');$grants=nstLoginTable($db,'Seg_UsuarioModulo_has_UsuarioPerfil');$profiles=nstLoginTable($db,'Seg_UsuarioPerfil');
        $statement=$db->prepare('SELECT m.UsuarioModulo,p.Lectura,p.Escritura FROM '.$grants.' p INNER JOIN '.$modules.' m ON m.IdUsuarioModulo=p.IdUsuarioModulo WHERE p.IdUsuarioPerfil=?');
        $statement->execute(array($account['IdUsuarioPerfil']));$permissions=array();
        foreach($statement->fetchAll(PDO::FETCH_ASSOC) as $permission)$permissions[$permission['UsuarioModulo']]=array('Lectura'=>$permission['Lectura'],'Escritura'=>$permission['Escritura']);
        $statement=$db->prepare('SELECT UsuarioPerfil FROM '.$profiles.' WHERE IdUsuarioPerfil=?');$statement->execute(array($account['IdUsuarioPerfil']));
        $programmer=strcasecmp(trim((string)$statement->fetchColumn()),'PROGRAMADOR')===0;
        if(session_status()===PHP_SESSION_ACTIVE)session_regenerate_id(true);
        $_SESSION['failed_attempts']=0;$_SESSION['last_attempt_time']=0;
        $_SESSION['user']=$account['Usuario'];$_SESSION['idPerfil']=(int)$account['IdUsuarioPerfil'];$_SESSION['idAlmacen']=$account['IdAlmacen'];
        $_SESSION['permisos']=$permissions;$_SESSION['diario_programador']=$programmer;$_SESSION['start']=$now;$_SESSION['limitado']=0;
        $_SESSION['hostUrl']='https://'.($_SERVER['HTTP_HOST']??'localhost');
        return array(200,array('isLogin'=>true,'message'=>'Datos correctos.','redirect'=>$programmer?'/views/diario.php':'/views/V_VentaForm.php','cantidad_res'=>5));
    } catch(Throwable $error) {
        error_log('[NST LOGIN] Database/identity error code: '.$error->getCode());
        return array(503,array('isLogin'=>false,'message'=>'No se pudo acceder a la base de datos del sistema. Revise la conexión y las tablas configuradas en el servidor.'));
    }
}
