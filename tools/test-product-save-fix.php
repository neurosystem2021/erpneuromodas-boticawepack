<?php
require 'C:/xampp/htdocs/boticav1/api/vendor/autoload.php';
$precioAnterior = null;
$stockMinimo = null;
$piso = null;
$productoDesc2 = null;
$opcContable = null;
$porcentajeLiquidacion = null;
$codigoBarra = null;
$ofertaWeb = null;
$botapie = null;
$idProductoFormaFarmaceutica = null;
$fechaOfertaInicio = null;
$esHabitacion = null;
$genero = null;
$esPadre = null;
$productoDesc3 = null;
$controlaStock = null;
$controlaStockMinimo = null;
$precioWeb = null;
$productoDesc = null;
$precioConvenio = null;
$PrecioApp = null;
$productoModelo = null;
$fechaOfertaFin = null;
$color = null;
$idProductoModelo = null;
$porcentajeUtilidad = null;
$tienePresentacion = null;
$precioPorMayor = null;
$idProductoCategoria = null;
$mostrarApp = null;
$precioCosto = null;
$productoPresentacion = null;
$producto = null;
$preciosPorProducto = null;
$idOfertaCategoria = null;
$posOferta = null;
$precioContado = null;
$anulado = null;
$idProductoTalla = null;
$idProductoMarca = null;
$idProductoMedicion = null;
$values=array(
            "CodigoBarra" =>                    $codigoBarra,
            "Producto" =>                       $producto,
            "ProductoDesc" =>                   $productoDesc,
            "ProductoDesc2" =>                  $productoDesc2,
            "ProductoDesc3" =>                  $productoDesc3,
            "IdProductoMarca" =>                $idProductoMarca,
            "IdProductoFormaFarmaceutica" =>    $idProductoFormaFarmaceutica,
            "IdProductoMedicion" =>             $idProductoMedicion,
            "IdProductoCategoria" =>            $idProductoCategoria,
            // "IdProductoModelo" => $idProductoModelo,
            // "IdProductoTalla" => $idProductoTalla,
            "ControlaStock" =>                  $controlaStock,
            "PorcentajeUtilidad" =>             $porcentajeUtilidad,
            "Genero" =>                         $genero,
            "Color" =>                          $color,
            "Botapie" =>                        $botapie,
            "Anulado" =>                        $anulado,
            "ProductoModelo" =>                 $productoModelo,
            "PrecioCosto" =>                    $precioCosto,
            "PrecioContado" =>                  $precioContado,
            "precioConvenio" =>                 $precioConvenio,
            "ProductoPresentacion" =>           $productoPresentacion,
            "EsPadre" =>                        $esPadre,
            "ControlaStockMinimo" =>             $controlaStockMinimo,
            "StockMinimo" =>                    $stockMinimo,
            "PrecioPorMayor" =>                 $precioPorMayor,
            "EsHabitacion" =>                   $esHabitacion,
            "Piso" =>                           $piso,
            "PreciosPorProducto" =>             $preciosPorProducto,
            "IdOfertaCategoria" =>              $idOfertaCategoria,
            "MostrarApp" =>                     $mostrarApp,
            "PosOferta" =>                      $posOferta,
            "PorcentajeLiquidacion" =>          $porcentajeLiquidacion,
            "TienePresentacion" =>              $tienePresentacion,
            "OpcContable" =>                    $opcContable,
            "PrecioApp" =>                      $PrecioApp,
            "OfertaWeb" => $ofertaWeb,
            "PrecioWeb" => $precioWeb,
            "PrecioAnterior" => $precioAnterior,
            "FechaOfertaInicio" =>              $fechaOfertaInicio,
            "FechaOfertaFin" =>                 $fechaOfertaFin,
        );
if(array_keys($values)!==array_filter(array_keys($values),'is_string'))throw new Exception('Unnamed update field');
$db=new PDO('mysql:host=127.0.0.1;dbname=nst;charset=utf8','root','',array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
$db->beginTransaction();
try {
$values=array_intersect_key($values,array_flip(array('OfertaWeb','PrecioWeb','PrecioAnterior')));
$values['OfertaWeb']=1;$values['PrecioWeb']=80;$values['PrecioAnterior']=100;
$sql='UPDATE Gen_Producto SET '.implode(',',array_map(function($key){return '`'.$key.'`=?';},array_keys($values))).' WHERE IdProducto=1';
$db->prepare($sql)->execute(array_values($values));
$r=$db->query('SELECT OfertaWeb,PrecioWeb,PrecioAnterior FROM Gen_Producto WHERE IdProducto=1')->fetch(PDO::FETCH_ASSOC);
if((int)$r['OfertaWeb']!==1||(float)$r['PrecioWeb']!==80.0||(float)$r['PrecioAnterior']!==100.0)throw new Exception('Save failed');
echo 'Product update mapping and database save OK; test rolled back';
}finally{$db->rollBack();}
