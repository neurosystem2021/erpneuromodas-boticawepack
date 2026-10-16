<?php require_once __DIR__.'/validateUser.php';
$accountingTab=$_GET['tab']??'dashboard';
if(!is_string($accountingTab)||!in_array($accountingTab,['dashboard','accounts','entries','sources','reports','tax','banks','assets','config','audit'],true))$accountingTab='dashboard';
$accountingFrame='/nuevo/index.html#/contabilidad?tab='.$accountingTab;
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Contabilidad · NEUROSOFT</title><link rel="stylesheet" href="/resources/font-awesome/css/font-awesome.css"><link rel="stylesheet" href="/resources/css/bootstrap.css"><link rel="stylesheet" href="/resources/css/main.css"><script src="/resources/js/jquery-3.2.1.min.js"></script><script src="/resources/js/main.js"></script><script src="/resources/js/bootstrap.js"></script></head><body style="margin:0"><?php include __DIR__.'/header.php'; ?><iframe title="Contabilidad empresarial" src="<?php echo htmlspecialchars($accountingFrame,ENT_QUOTES,'UTF-8'); ?>" style="border:0;width:100%;height:calc(100vh - 90px)"></iframe></body></html>
