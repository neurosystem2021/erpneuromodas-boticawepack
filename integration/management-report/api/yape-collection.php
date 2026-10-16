<?php
function yapeCollectionType(PDO $db) {
    $stmt=$db->prepare("SELECT IdTipoCajaBanco FROM Cb_TipoCajaBanco WHERE TipoCajaBanco='COBROS YAPE / PLIN' AND Tipo=2 AND COALESCE(Anulado,0)=0 LIMIT 1");
    $stmt->execute(); $id=(int)$stmt->fetchColumn();
    if (!$id) throw new RuntimeException('Instala el tipo de control COBROS YAPE / PLIN.');
    return $id;
}
