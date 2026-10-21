<?php
require_once __DIR__.'/proforma-contact-core.php';
function quotePresentationInput(array $body): array {
 $input=$body['presentacionProforma']??['tipo'=>'productos'];
 if(!is_array($input)||!in_array($input['tipo']??'productos',['productos','sistemas'],true))throw new InvalidArgumentException('Elige una presentación válida para la proforma.');
 $result=['tipo'=>$input['tipo']??'productos'];
 $format=$input['formatoProducto']??'catalogo';if(!is_string($format)||!in_array($format,['catalogo','destacado'],true))throw new InvalidArgumentException('Elige un formato válido para los productos.');
 $result['formatoProducto']=$result['tipo']==='sistemas'?'catalogo':$format;
 foreach(['alcance','entregables','condiciones'] as $key){$value=$input[$key]??'';if(!is_string($value)||mb_strlen($value)>12000)throw new InvalidArgumentException('Revisa el campo '.$key.' (máximo 12000 caracteres).');$result[$key]=trim($value);}
 if($result['tipo']==='sistemas'&&$result['alcance']==='')throw new InvalidArgumentException('Describe qué incluye el sistema antes de guardar la proforma.');
 return $result;
}
function quotePresentationSave($db,int $id,array $data): void {
 require_once __DIR__ . '/concretera/LegacyGuard.php';cxLegacyRequireUnmanaged($db,'PROFORMA',$id);
 $s=$db->prepare('UPDATE Ve_Proforma SET Presentacion=?,FormatoProducto=?,AlcanceSistema=?,EntregablesSistema=?,CondicionesSistema=? WHERE IdProforma=?');$s->execute([$data['tipo'],$data['formatoProducto']??'catalogo',$data['alcance'],$data['entregables'],$data['condiciones'],$id]);
}
