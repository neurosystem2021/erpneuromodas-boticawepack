<?php
// Read-only calculations from explicitly mapped historical formulas; no stock or recipe writes.
function cxRecipeHistory($volume='1') {
    $quantity=cxDec($volume,2,true);
    if(bccomp($quantity,'100000',2)>0)throw new CxError('El volumen de consulta no puede superar 100000 m³.');
    $text=file_get_contents(__DIR__.'/../../data/mayo-recetas-revision.json');
    $source=json_decode($text,true);
    if(!is_array($source)||!isset($source['variantes']))throw new CxError('Antecedentes de mayo no disponibles.',503);
    $source['volumenConsulta']=$quantity;
    foreach($source['variantes'] as &$variant) {
        $cement=bcmul($quantity,$variant['cementoKgPorM3'],6);
        $variant['consulta']=['cementoKg'=>$cement,
            'aditivoJCalculado'=>$variant['porcentajeJ']===null?null:bcdiv(bcmul($cement,$variant['porcentajeJ'],8),'100',8),
            'aditivoKCalculado'=>$variant['porcentajeK']===null?null:bcdiv(bcmul($cement,$variant['porcentajeK'],8),'100',8),
            'unidadAditivos'=>$source['unidadAditivos']??null,'esRecetaAprobada'=>false];
    }unset($variant);
    return $source;
}
