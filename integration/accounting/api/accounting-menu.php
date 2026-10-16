<?php
// Include from the ERP header. Missing schema hides the menu until the approved installation.
try {
 if(session_status()!==PHP_SESSION_ACTIVE)session_start();$user=$_SESSION['user']??'';if(!$user)return;
 require_once dirname(__DIR__).'/config/database.php';$pdo=appPdoConnection();$q=$pdo->prepare('SELECT IdUsuarioPerfil FROM Seg_Usuario WHERE Usuario=? AND COALESCE(Anulado,0)=0');$q->execute([$user]);$profile=$q->fetchColumn();$allowed=(int)$profile===1&&strcasecmp($user,'superadmin')===0;
 if(!$allowed){$q=$pdo->prepare('SELECT m.CompanyId FROM Co_Member m JOIN Co_Company c ON c.Id=m.CompanyId AND c.Active=1 WHERE m.Username=? LIMIT 1');$q->execute([$user]);$allowed=(bool)$q->fetchColumn();}
 if($allowed){
  $links=[['dashboard','Panel contable'],['accounts','Plan contable PCGE'],['entries','Asientos contables'],['sources','Operaciones del ERP'],['reports','Libros y estados financieros'],['tax','Impuestos y registros tributarios'],['banks','Conciliación bancaria'],['assets','Activos y depreciación'],['config','Configuración y períodos'],['audit','Auditoría contable']];
  echo '<li class="dropdown" id="contabilidad-menu"><a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-book" aria-hidden="true"></i> Contabilidad <span class="caret"></span></a><ul class="dropdown-menu">';
  $icons=['dashboard'=>'fa-tachometer','accounts'=>'fa-list-alt','entries'=>'fa-pencil-square-o','sources'=>'fa-exchange','reports'=>'fa-book','tax'=>'fa-calculator','banks'=>'fa-university','assets'=>'fa-cubes','config'=>'fa-cogs','audit'=>'fa-shield'];
  foreach($links as $link)echo '<li><a data-accounting-icon="'.$icons[$link[0]].'" href="/views/contabilidad.php?tab='.$link[0].'">'.htmlspecialchars($link[1],ENT_QUOTES,'UTF-8').'</a></li>';
  echo '</ul></li>';
 }
}catch(Throwable $e){error_log('[ACCOUNTING MENU] Module not available.');}
