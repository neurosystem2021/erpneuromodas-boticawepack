<?php
// Include from the ERP header. Missing schema hides the menu until the approved installation.
try {
 if(session_status()!==PHP_SESSION_ACTIVE)session_start();$user=$_SESSION['user']??'';if(!$user)return;
 require_once dirname(__DIR__).'/config/database.php';$pdo=appPdoConnection();$q=$pdo->prepare('SELECT IdUsuarioPerfil FROM Seg_Usuario WHERE Usuario=? AND COALESCE(Anulado,0)=0');$q->execute([$user]);$profile=$q->fetchColumn();$allowed=(int)$profile===1&&strcasecmp($user,'superadmin')===0;
 if(!$allowed){$q=$pdo->prepare('SELECT m.CompanyId FROM Co_Member m JOIN Co_Company c ON c.Id=m.CompanyId AND c.Active=1 WHERE m.Username=? LIMIT 1');$q->execute([$user]);$allowed=(bool)$q->fetchColumn();}
 if($allowed)echo '<li><a href="/views/contabilidad.php"><i class="fas fa-book" aria-hidden="true"></i> CONTABILIDAD</a></li>';
}catch(Throwable $e){error_log('[ACCOUNTING MENU] Module not available.');}
