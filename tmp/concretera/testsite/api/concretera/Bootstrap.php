<?php
require_once __DIR__.'/Domain.php';
function cxBootstrap() {
    if(session_status()!==PHP_SESSION_ACTIVE)session_start();
    $user=(string)($_SESSION['user']??'');
    if($user===''||(isset($_SESSION['expire'])&&$_SESSION['expire']<time()))throw new CxError('Inicie sesión nuevamente.',401);
    $_SESSION['concretera_csrf']=$_SESSION['concretera_csrf']??bin2hex(random_bytes(32));$token=$_SESSION['concretera_csrf'];session_write_close();
    require_once dirname(__DIR__,2).'/config/database.php';
    // Este archivo se instala en api/concretera: la raíz del ERP está dos niveles arriba.
    $db=cxFixtureConnect(getenv('CX_TEST_SCHEMA'));$s=$db->prepare('SELECT IdUsuarioPerfil FROM Seg_Usuario WHERE Usuario=? AND COALESCE(Anulado+0,0)=0');$s->execute([$user]);$profile=$s->fetchColumn();
    if(!$profile)throw new CxError('Usuario no autorizado.',403);
    require_once dirname(__DIR__,2).'/models/ModuleAccess.php';
    if(!moduleAccessAllowed($user,'/views/Concretera.php'))throw new CxError('Su usuario no tiene acceso al módulo.',403);
    $method=$_SERVER['REQUEST_METHOD']??'GET';
    if(!in_array($method,['GET','POST'],true))throw new CxError('Método no permitido.',405);
    if((int)$profile!==1) {
        $s=$db->prepare('SELECT p.Lectura+0 AS Lectura,p.Escritura+0 AS Escritura FROM Seg_UsuarioModulo_has_UsuarioPerfil p INNER JOIN Seg_UsuarioModulo m ON m.IdUsuarioModulo=p.IdUsuarioModulo WHERE p.IdUsuarioPerfil=? AND m.UsuarioModulo=? AND COALESCE(p.Anulado+0,0)=0');$s->execute([$profile,'Concretera.php']);$grant=$s->fetch(PDO::FETCH_ASSOC);
        if(!$grant||!(int)$grant['Lectura']||($method==='POST'&&!(int)$grant['Escritura']))throw new CxError('Su perfil no autoriza esta acción.',403);
    }
    if($method==='POST'&&!hash_equals($token,(string)($_SERVER['HTTP_X_CONCRETERA_CSRF']??'')))throw new CxError('Actualice la página antes de guardar.',403);
    if((int)($_SERVER['CONTENT_LENGTH']??0)>2*1024*1024)throw new CxError('La solicitud excede el tamaño permitido.');
    $exists=$db->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='Cx_Configuracion'")->fetchColumn();
    if(!$exists)throw new CxError('El módulo requiere instalar la migración comercial. Los otros módulos permanecen disponibles.',503);
    return [new CxService($db,$user,(int)$profile===1),$token];
}
