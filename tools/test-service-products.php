<?php
require __DIR__.'/../integration/service-products/imprimir/serviceReceiptProducts.php';
$html=serviceReceiptProductsHtml([['IdProducto'=>4623,'Codigo'=>'4623','Producto'=>'IMP. TERMICA DE RECIBOS RPT004 USB/ETH/CORTADOR/230MM/S','Cantidad'=>1,'Precio'=>350,'Importe'=>350]]);
foreach(['4623','IMP. TERMICA','350.00','1 × S/'] as $text)if(strpos($html,$text)===false)throw new Exception('Missing product detail '.$text);
echo "Service receipt product name, code, quantity, unit price and line amount OK\n";
