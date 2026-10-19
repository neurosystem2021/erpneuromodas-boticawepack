<?php
// FACTURA ELEC. and FACTURA/IGV ELEC. share the same SUNAT document code.
// When their configured series match, both must continue the same sequence.
function imcFacturaIgvNumero($db, $idTipoDoc, $serie) {
    $stmt = $db->prepare('SELECT CodSunat FROM Ve_DocVentaTipoDoc WHERE IdTipoDoc=?');
    $stmt->execute([(int) $idTipoDoc]);
    if ((string) $stmt->fetchColumn() !== '01') return null;
    if (!preg_match('/^F[A-Z0-9]{3}$/iD', (string) $serie)) throw new RuntimeException('La factura no tiene una serie electrónica válida configurada.');
    $stmt = $db->prepare("SELECT COALESCE(MAX(v.Numero),0)+1 FROM Ve_DocVenta v INNER JOIN Ve_DocVentaTipoDoc t ON t.IdTipoDoc=v.IdTipoDoc WHERE t.CodSunat='01' AND v.Serie=?");
    $stmt->execute([(string) $serie]);
    return (int) $stmt->fetchColumn();
}
