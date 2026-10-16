<?php
if(session_status()!==PHP_SESSION_ACTIVE)session_start();
if(empty($_SESSION['user'])||(isset($_SESSION['expire'])&&(int)$_SESSION['expire']<time())){header('Location: /');exit;}
require_once __DIR__.'/../config/database.php';$diaryDb=appPdoConnection();$s=$diaryDb->prepare('SELECT p.UsuarioPerfil FROM Seg_Usuario u JOIN Seg_UsuarioPerfil p ON p.IdUsuarioPerfil=u.IdUsuarioPerfil WHERE u.Usuario=? AND IFNULL(u.Anulado,0)=0');$s->execute([$_SESSION['user']]);$diaryProgrammer=strcasecmp(trim((string)$s->fetchColumn()),'PROGRAMADOR')===0;
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Diario de actividades</title>
<link rel="stylesheet" href="/resources/font-awesome/css/font-awesome.css"><link rel="stylesheet" href="/resources/css/bootstrap.css"><link rel="stylesheet" href="/resources/css/main.css">
<script src="/resources/js/jquery-3.2.1.min.js"></script><script src="/resources/js/main.js"></script><script src="/resources/js/bootstrap.js"></script>
</head><body>
<?php if(!$diaryProgrammer)include 'header.php';else { ?><header style="display:flex;align-items:center;justify-content:space-between;padding:12px 20px;background:linear-gradient(110deg,#101f31,#1b3b4b);border-bottom:1px solid #c3ad7855;color:#f0eadb;font:13px 'Segoe UI',Arial"><strong>NEUROSOFT · Programación y soporte</strong><a href="/controllers/logout.php" style="color:#c1dcd6">Cerrar sesión</a></header><?php } ?>
<iframe title="Diario de actividades" src="/nuevo/index.html?v=<?php echo (int)filemtime(__DIR__.'/../nuevo/index.html'); ?>#/diario<?php echo isset($_GET['reportes'])?'?seccion=reportes':''; ?>" style="display:block;width:100%;height:calc(100vh - <?php echo $diaryProgrammer?'44':'90'; ?>px);border:0"></iframe>
</body></html>
