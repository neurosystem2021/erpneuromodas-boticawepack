from pathlib import Path
p=Path('integration/bank-report.php');s=Path('C:/xampp/htdocs/boticav1/api/bank-report.php').read_text(encoding='utf-8');needle="if(!$user)return $r->withJson(['error'=>'Inicie sesión nuevamente.'],401);";assert needle in s;s=s.replace(needle,needle+"if(strtolower(trim((string)$user))!=='superadmin')return $r->withJson(['error'=>'Reporte disponible solo para superadmin.'],403);");p.write_text(s,encoding='utf-8')
p=Path('integration/ReporteBancos.php');s=Path('C:/xampp/htdocs/boticav1/views/ReporteBancos.php').read_text(encoding='utf-8');s=s.replace("<?php include_once('../clases/helpers/Modal.php'); ?>",'''<?php
if(session_status()!==PHP_SESSION_ACTIVE)session_start();
if(strtolower(trim((string)($_SESSION['user']??'')))!=='superadmin'){
 http_response_code(empty($_SESSION['user'])?401:403);
 header('Content-Type: text/plain; charset=utf-8');
 exit('Reporte disponible solo para superadmin.');
}
include_once('../clases/helpers/Modal.php'); ?>''');p.write_text(s,encoding='utf-8')
p=Path('integration/bank-header.php');s=Path('C:/xampp/htdocs/boticav1/views/header.php').read_text(encoding='utf-8');needle='<li><a href="<?php echo SITE_URL; ?>views/ReporteBancos.php">Reporte de bancos</a></li>';assert needle in s;s=s.replace(needle,"<?php if(strtolower(trim((string)($_SESSION['user']??'')))==='superadmin'): ?>"+needle+"<?php endif; ?>");p.write_text(s,encoding='utf-8')
