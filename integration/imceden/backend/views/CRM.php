<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
require_once dirname(__DIR__) . '/config/crm.php';
if (!crmIsEnabled()) {
    http_response_code(404);
    exit('Módulo no habilitado.');
}
if (empty($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}
if (isset($_SESSION['expire']) && time() > $_SESSION['expire']) { header('Location: /index.php'); exit; }
if (empty($_SESSION['login_csrf'])) $_SESSION['login_csrf'] = bin2hex(random_bytes(32));
?><!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>CRM NeuroSoft</title>
<script>window.CRM_CONTEXT=<?php echo json_encode(array('csrfToken' => $_SESSION['login_csrf'] ?? '')); ?>;</script></head>
<?php $crmBuildVersion = (string) (@filemtime(dirname(__DIR__) . '/crm/dist/index.html') ?: time()); ?>
<body style="margin:0"><a href="/views/Gen_ProductoForm.php" style="position:fixed;right:16px;top:12px;z-index:99;padding:9px 13px;background:#123f51;color:white;border-radius:9px;text-decoration:none;font:12px Arial">VOLVER AL SISTEMA</a><iframe title="CRM NeuroSoft" src="/crm/dist/index.html?v=<?php echo rawurlencode($crmBuildVersion); ?>" style="border:0;width:100%;height:100vh"></iframe></body>
</html>
