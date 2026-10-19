<?php
function qcNumberLabel($number,$year){return str_pad((string)$number,3,'0',STR_PAD_LEFT).'-'.$year;}
function qcSequenceInfo($db,$id,$year){
 $s=$db->prepare('SELECT Anio,Numero FROM Ve_ContratoNumero WHERE IdProforma=?');$s->execute([$id]);$existing=$s->fetch(PDO::FETCH_ASSOC);
 if($existing)return ['numeroContrato'=>qcNumberLabel($existing['Numero'],$existing['Anio']),'numeroAsignado'=>true];
 $s=$db->prepare('SELECT Ultimo FROM Ve_ContratoSerie WHERE Anio=?');$s->execute([$year]);$last=$s->fetchColumn();
 return ['numeroContrato'=>qcNumberLabel(($last===false?0:(int)$last)+1,$year),'numeroAsignado'=>false];
}
function qcAssignNumber($db,$id,$year){
 if(!$db->inTransaction())throw new RuntimeException('Contract numbering requires a transaction');
 $s=$db->prepare('SELECT Anio,Numero FROM Ve_ContratoNumero WHERE IdProforma=?');$s->execute([$id]);$existing=$s->fetch(PDO::FETCH_ASSOC);
 if($existing)return qcNumberLabel($existing['Numero'],$existing['Anio']);
 $s=$db->prepare('INSERT INTO Ve_ContratoSerie(Anio,Ultimo) VALUES(?,0) ON DUPLICATE KEY UPDATE Anio=Anio');$s->execute([$year]);
 $s=$db->prepare('SELECT Ultimo FROM Ve_ContratoSerie WHERE Anio=? FOR UPDATE');$s->execute([$year]);$next=(int)$s->fetchColumn()+1;if($next>999999999)throw new RuntimeException('Contract sequence limit');
 $s=$db->prepare('UPDATE Ve_ContratoSerie SET Ultimo=? WHERE Anio=?');$s->execute([$next,$year]);
 $s=$db->prepare('INSERT INTO Ve_ContratoNumero(IdProforma,Anio,Numero) VALUES(?,?,?)');$s->execute([$id,$year,$next]);
 return qcNumberLabel($next,$year);
}
