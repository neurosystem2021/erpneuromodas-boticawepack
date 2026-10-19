<?php
require __DIR__.'/../integration/proforma-edit/proforma-pdf-render.php';
$names=['Punto de venta','Catálogo de productos y servicios','Inventario y stock','Compras','Caja y bancos','Reportes de ventas','Clientes','Usuarios y accesos','TRALADOS','NUEVO KARDEX','NUEVA ORDEN COMPRA','REGISTRO DE MOVIMIENTO','Pre Orden','Reporte Utilidad Bruta','DASHBOARD KPI'];
$scope='Objetivo confirmado.' . "\n\nMódulos incluidos: ".implode('; ',$names).'.';$data=qpModulePresentation($scope);
if($data['text']!=='Objetivo confirmado.'||count($data['modules'])!==15)throw new RuntimeException('Module selection mismatch');
$html=qpModuleCards($data['modules']);if(substr_count($html,'class="module-icon"')!==15||strpos($html,'data:image/svg+xml;base64,')===false)throw new RuntimeException('Icons missing');
if(substr_count($html,'<li>')!==45||substr_count($html,'En tu negocio:')!==15||substr_count($html,'SUBMÓDULO ·')!==7)throw new RuntimeException('Detailed module functions missing');
$unknown=qpModuleCards(qpModulePresentation('Módulos incluidos: <script>alert(1)</script>.')['modules']);if(strpos($unknown,'<script>')!==false)throw new RuntimeException('Unsafe module text');
$plain=qpModulePresentation('Objetivo sin lista.');if($plain['text']!=='Objetivo sin lista.'||count($plain['modules']))throw new RuntimeException('Original scope changed');
if(qpSystemTitle('Propuesta para Market / bodega.')!=='PROPUESTA DE SISTEMAS DE MARKET / BODEGA'||qpSystemTitle('Propuesta para Ferretería.')!=='PROPUESTA DE SISTEMAS DE FERRETERÍA')throw new RuntimeException('Business title mismatch');
echo "PASS PDF module selection, descriptions, SVG icons and escaped module labels.\n";
