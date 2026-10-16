from pathlib import Path
out=Path('integration/service-products');(out/'imprimir').mkdir(parents=True,exist_ok=True)
helper='''<?php
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
'''
(out/'imprimir/serviceReceiptProducts.php').write_text(helper,encoding='utf-8')
root=Path(r'C:\xampp\htdocs\boticav1')
rel='imprimir/imprimirReciboServicio.php';s=(root/rel).read_text(encoding='utf-8');s=s.replace("$proyecto = $proyecto[0];","$proyecto = $proyecto[0];\n    require_once __DIR__.'/serviceReceiptProducts.php';\n    $serviceProducts=serviceReceiptProducts($IdProducto);",1)
s=s.replace('    <table width="100%" style="border-top:',"    <?php echo serviceReceiptProductsHtml($serviceProducts); ?>\n    <table width=\"100%\" style=\"border-top:",1)
s=s.replace("'code'=>(int)$IdProducto,","'products'=>$serviceProducts,'code'=>(int)$IdProducto,",1)
s=s.replace("  document.getElementById('shareServiceWhatsapp')", "  text += '\\n\\nPRODUCTOS / SERVICIOS\\n' + receipt.products.map(function(p){return 'Código: '+p.Codigo+' · '+p.Producto+'\\n'+p.Cantidad+' x S/ '+Number(p.Precio).toFixed(2)+' = S/ '+Number(p.Importe).toFixed(2);}).join('\\n\\n');\n  document.getElementById('shareServiceWhatsapp')",1)
s=s.replace('<style>','<style>\n    @page{size:80mm 297mm;margin:4mm} .container{max-width:72mm!important;width:72mm!important} p,td{overflow-wrap:anywhere} @media print{body{margin:0!important}}',1)
(out/rel).write_text(s,encoding='utf-8')
rel='api/index.php';s=(root/rel).read_text(encoding='utf-8');a=s.index("$app->get('/imprimirpdfreciboservicio/{id}'");b=s.index("$app->get('/ordenCompra'",a);part=s[a:b];needle="        $data = array(\n            'txtTIPO_PROCESO' => '03',";assert needle in part;part=part.replace(needle,"        require_once __DIR__.'/../imprimir/serviceReceiptProducts.php';\n        $data = array(\n            'productos' => serviceReceiptProducts($id),\n            'txtTIPO_PROCESO' => '03',",1);s=s[:a]+part+s[b:];p=out/rel;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8')
rel='api/sunat/plugins/dompdf/impirimirReciboServicio.php';s=(root/rel).read_text(encoding='utf-8');s=s.replace("$cab = json_decode($bodyRequest, true);","$cab = json_decode($bodyRequest, true);\nrequire_once __DIR__.'/../../../../imprimir/serviceReceiptProducts.php';\n$productsHtml=serviceReceiptProductsHtml($cab['productos'] ?? []);",1)
s=s.replace('    <table width="100%" style="margin-top:18px',"    ' . $productsHtml . '\n    <table width=\"100%\" style=\"margin-top:18px",1)
p=out/rel;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8')
