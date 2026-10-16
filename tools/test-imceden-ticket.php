<?php
require __DIR__.'/../integration/direct-print/ticket-raster.php';
$sale=['CodSunat'=>'03','Serie'=>'PRUEBA','Numero'=>1,'FechaDoc'=>'2026-10-16 10:00:00','Cliente'=>'PRUEBA TECNICA - SIN VALOR TRIBUTARIO','DniRuc'=>'00000000','Direccion'=>'','EsCredito'=>0,'MetodoPago'=>'Efectivo','UsuarioReg'=>'tecnico','PagoCon'=>10,'TieneIgv'=>1];
$products=[['Producto'=>'Servicio de prueba','Cantidad'=>1,'Precio'=>10,'Descuento'=>0,'TOTAL'=>10,'CodigoBarra'=>'TEST','ProductoModelo'=>'','ProductoMedicion'=>'UND','Descripcion'=>'Verificacion de ticket termico de 80 mm']];
$company=['razon_social'=>'PRUEBA DE IMPRESION','ruc'=>'','direccion'=>'','telefono'=>'','ubicacion'=>'','actividad'=>''];
$qr=imagecreatetruecolor(40,40);$white=imagecolorallocate($qr,255,255,255);imagefill($qr,0,0,$white);
$image=construirTicketRaster($sale,$products,$company,[['MetodoPago'=>'Efectivo','Importe'=>10]],['fuente_normal'=>'C:/Windows/Fonts/arial.ttf','fuente_negrita'=>'C:/Windows/Fonts/arialbd.ttf','logo'=>'','qr_imagen'=>$qr,'titulo_marca'=>'','total_letras'=>'DIEZ SOLES','resolucion_sunat'=>'','mensaje_ticket'=>'SIN VALOR TRIBUTARIO','consulta_web'=>'']);
if(imagesx($image)!==576||imagesy($image)<200)throw new RuntimeException('Invalid ticket dimensions');
$bytes=ticketRasterToEscpos($image);if(strlen($bytes)<1000)throw new RuntimeException('Empty ticket');
imagepng($image,__DIR__.'/../output/imceden-ticket-test.png');file_put_contents(__DIR__.'/../output/imceden-ticket-test.bin',$bytes);imagedestroy($image);imagedestroy($qr);echo "PASS thermal raster: 576 pixels / 80mm, Spanish text, product, totals, QR and ESC/POS generated. No print submitted.\n";
