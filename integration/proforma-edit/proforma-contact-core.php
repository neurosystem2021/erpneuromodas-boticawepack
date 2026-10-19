<?php
function quotePhoneValid($value){if(!is_string($value))return false;$phone=trim($value);$digits=preg_replace('/\D/','',$phone);return strlen($phone)<=40&&preg_match('/^\+?[0-9][0-9 ()-]*$/D',$phone)&&strlen($digits)>=7&&strlen($digits)<=15&&!preg_match('/^0+$/D',$digits);}
function quoteRequireClientPhone($db,$id,$type){if($type!=='sistemas')return;$s=$db->prepare('SELECT Telefono FROM Ve_DocVentaCliente WHERE IdCliente=? AND COALESCE(Anulado,0)=0');$s->execute([$id]);$phone=$s->fetchColumn();if(!quotePhoneValid($phone))throw new InvalidArgumentException('Las proformas de sistemas requieren un teléfono válido del cliente. Actualízalo con Guardar teléfono antes de continuar.');}
function quoteUpdateClientPhone($db,$id,$phone,$previous){
 if(!quotePhoneValid($phone)||!is_string($previous))throw new InvalidArgumentException('Escribe un teléfono válido de 7 a 15 dígitos, con código de país si corresponde.');
 $s=$db->prepare('SELECT Cliente,Telefono FROM Ve_DocVentaCliente WHERE IdCliente=? AND COALESCE(Anulado,0)=0 FOR UPDATE');$s->execute([$id]);$client=$s->fetch(PDO::FETCH_ASSOC);if(!$client)throw new InvalidArgumentException('Selecciona un cliente disponible.');
 if((string)$client['Telefono']!==$previous)throw new RuntimeException('El teléfono fue actualizado por otra persona. Vuelve a cargarlo antes de guardar.',409);
 $s=$db->prepare('UPDATE Ve_DocVentaCliente SET Telefono=? WHERE IdCliente=?');$s->execute([trim($phone),$id]);return ['IdCliente'=>(int)$id,'Cliente'=>$client['Cliente'],'Telefono'=>trim($phone)];
}
