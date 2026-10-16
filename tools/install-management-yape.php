<?php
if (PHP_SAPI!=='cli') exit;
$root='C:/xampp/htdocs/boticav1';
require $root.'/config/database.php';
$db=appPdoConnection();
$dir=$root.'/storage/management-report-backups';
if (!is_dir($dir)) mkdir($dir,0700,true);
file_put_contents($dir.'/.htaccess',"Require all denied\nDeny from all\n");
$old=$db->query("SELECT * FROM Cb_CajaBanco WHERE IdTipoCajaBanco=7 AND IdCuenta=3 AND COALESCE(IdCliente,0)=0 AND Concepto LIKE 'YAPE/PLIN - %'")->fetchAll(PDO::FETCH_ASSOC);
$types=$db->query('SELECT IdTipoCajaBanco,TipoCajaBanco,Tipo FROM Cb_TipoCajaBanco')->fetchAll(PDO::FETCH_ASSOC);
file_put_contents($dir.'/yape-before-'.date('Ymd-His').'.json',json_encode(array('records'=>$old,'types'=>$types),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
$db->beginTransaction();
try {
    $stmt=$db->query("SELECT IdTipoCajaBanco FROM Cb_TipoCajaBanco WHERE TipoCajaBanco='COBROS YAPE / PLIN' AND Tipo=2 LIMIT 1");$id=(int)$stmt->fetchColumn();
    if (!$id) { $db->exec("INSERT INTO Cb_TipoCajaBanco(TipoCajaBanco,Tipo,AplicaDocAPagar,AplicaDocACobrar,Anulado) VALUES('COBROS YAPE / PLIN',2,0,0,0)"); $id=(int)$db->lastInsertId(); }
    $stmt=$db->prepare("UPDATE Cb_CajaBanco SET IdTipoCajaBanco=? WHERE IdTipoCajaBanco=7 AND IdCuenta=3 AND COALESCE(IdCliente,0)=0 AND Concepto LIKE 'YAPE/PLIN - %'");$stmt->execute(array($id));$count=$stmt->rowCount();
    $db->exec("UPDATE Cb_TipoCajaBanco SET TipoCajaBanco='PAGOS ANTIGUOS (ANTES YAPE)' WHERE IdTipoCajaBanco=7 AND TipoCajaBanco='Pagos Yape'");
    $db->commit();
    echo 'Control Yape neutral instalado: tipo '.$id.'. Registros automáticos reclasificados: '.$count.'. No se cambiaron importes.'.PHP_EOL;
} catch (Throwable $error) { $db->rollBack(); throw $error; }
