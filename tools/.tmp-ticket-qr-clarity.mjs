import fs from 'node:fs';let p='integration/direct-print/usb.php',s=fs.readFileSync(p,'utf8');s=s.replace("QRcode::png($textoQr, null, 'Q', 6, 1);","QRcode::png($textoQr, null, 'Q', 5, 4);");s=s.replace("'qr_imagen' => $qrImagen,","'qr_imagen' => $qrImagen,\n        'qr_modulo_px' => 5,");fs.writeFileSync(p,s);p='integration/direct-print/ticket-raster.php';s=fs.readFileSync(p,'utf8');const old="    if ($qr) imagecopyresampled($imagen, $qr, $margen, $y, 0, 0, 155, 155, imagesx($qr), imagesy($qr));\r\n    $xLegal = $margen + 175;";const next=`    $qrAncho = 0;
    if ($qr) {
        // Keep complete square modules and the four-module white quiet zone.
        $moduloOrigen = max(1, (int)($opciones['qr_modulo_px'] ?? 5));
        $modulos = imagesx($qr) / $moduloOrigen;
        $escala = max(1, min(5, (int)floor(264 / $modulos)));
        $qrAncho = (int)($modulos * $escala);
        imagecopyresized($imagen, $qr, $margen, $y, 0, 0, $qrAncho, $qrAncho, imagesx($qr), imagesy($qr));
    }
    $xLegal = $margen + max(175, $qrAncho + 16);`;
if(!s.includes(old)&&!s.includes(old.replace(/\r\n/g,'\n')))throw Error('QR raster anchor missing');s=s.replace(s.includes(old)?old:old.replace(/\r\n/g,'\n'),next).replace('$y += max(160, $legalY - $y) + 12;','$y += max(160, $qrAncho + 5, $legalY - $y) + 12;');fs.writeFileSync(p,s);p='integration/receipt-finish/imprimir/comprobanteelectronico.php';s=fs.readFileSync(p,'utf8').replace("QRcode::png($textoQr, null, 'Q', 7, 1);","QRcode::png($textoQr, null, 'Q', 8, 4);").replace('grid-template-columns:24mm 1fr','grid-template-columns:32mm 1fr').replace('.qr{width:24mm;height:24mm}', '.qr{width:32mm;height:32mm;image-rendering:pixelated;background:#fff}');fs.writeFileSync(p,s);
