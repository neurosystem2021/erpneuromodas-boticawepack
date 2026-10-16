<?php
function serviceReceiptProducts($idProducto) {
    require_once __DIR__.'/../config/database.php';
    $db=appPdoConnection();
    $stmt=$db->prepare("SELECT p.IdProducto,COALESCE(NULLIF(p.CodigoBarra,''),CAST(p.IdProducto AS CHAR)) AS Codigo,p.Producto,d.Cantidad,d.Precio,d.Cantidad*d.Precio AS Importe FROM Gen_Producto proyecto INNER JOIN Ve_PreOrdenDet d ON d.IdPreOrden=proyecto.IdPreOrden INNER JOIN Gen_Producto p ON p.IdProducto=d.IdProducto WHERE proyecto.IdProducto=:id AND p.IdProducto<>proyecto.IdProducto ORDER BY p.IdProducto");
    $stmt->execute(['id'=>(int)$idProducto]);return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function serviceReceiptProductsHtml($products) {
    $escape=function($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');};
    $html='<div style="border-top:1px dashed #000;margin-top:12px"><b>DETALLE DE PRODUCTOS / SERVICIOS</b></div>';
    foreach($products as $product){
        $html.='<div style="border-bottom:1px dashed #999;padding:9px 0;page-break-inside:avoid"><b>'.$escape($product['Producto']).'</b><div style="font-size:11px">Código: '.$escape($product['Codigo']).' · ID: '.$escape($product['IdProducto']).'</div><table width="100%"><tr><td>'.$escape($product['Cantidad']).' × S/ '.number_format((float)$product['Precio'],2).'</td><td align="right"><b>S/ '.number_format((float)$product['Importe'],2).'</b></td></tr></table></div>';
    }
    return $html;
}
