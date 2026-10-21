<?php
if(PHP_SAPI!=='cli')exit;
$live='C:/xampp/htdocs/boticav1';$_SERVER['DOCUMENT_ROOT']=$live;
require $live.'/api/sunat/plugins/phpqrcode/qrlib.php';
$a=getopt('',['company:','type:']);$id=(int)$a['company'];$type=$a['type'];
if(!in_array($id,[2,3],true)||!in_array($type,['01','03'],true))exit(1);
$docVenta=['IdEmpresaEmisora'=>$id,'FechaDoc'=>'2026-10-19 12:00:00','Serie'=>$type==='01'?'FDEM':'BDEM','Numero'=>0,'Cliente'=>'CLIENTE FICTICIO','DniRuc'=>'20000000000','Direccion'=>'DIRECCION DEMO','TieneIgv'=>0,'EsCredito'=>0,'PagoCon'=>0,'Observacion'=>'DATOS DE PRUEBA - SIN VALOR TRIBUTARIO','UsuarioReg'=>'DEMO'];
$productos=[['TOTAL'=>0,'Descuento'=>0,'Producto'=>'PRODUCTO DEMO','Descripcion'=>'PRUEBA DE LOGO','Cantidad'=>1,'Precio'=>0]];
$tipoComprobanteImpreso=$type==='01'?'FACTURA ELECTRONICA':'BOLETA ELECTRONICA';$codigoComprobanteSunat=$type;
ob_start();include $live.'/imprimir/comprobanteelectronico.php';$html=ob_get_clean();
$out=dirname(__DIR__,2).'/output/concretera/logo-formatos/ticket-'.$id.'-'.$type.'.html';file_put_contents($out,$html);
echo $out.PHP_EOL;
