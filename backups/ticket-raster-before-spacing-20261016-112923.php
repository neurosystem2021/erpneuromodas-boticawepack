<?php

function ticketRasterTextWidth($texto, $tamano, $fuente)
{
    $caja = imagettfbbox($tamano, 0, $fuente, (string) $texto);
    return abs($caja[2] - $caja[0]);
}

function ticketRasterWrap($texto, $tamano, $fuente, $anchoMaximo)
{
    $texto = trim(preg_replace('/\s+/u', ' ', (string) $texto));
    if ($texto === '') return array('');
    $palabras = preg_split('/\s+/u', $texto);
    $lineas = array();
    $linea = '';
    foreach ($palabras as $palabra) {
        $candidata = $linea === '' ? $palabra : $linea . ' ' . $palabra;
        if (ticketRasterTextWidth($candidata, $tamano, $fuente) <= $anchoMaximo) {
            $linea = $candidata;
            continue;
        }
        if ($linea !== '') {
            $lineas[] = $linea;
            $linea = '';
        }
        if (ticketRasterTextWidth($palabra, $tamano, $fuente) <= $anchoMaximo) {
            $linea = $palabra;
            continue;
        }
        $fragmento = '';
        foreach (preg_split('//u', $palabra, -1, PREG_SPLIT_NO_EMPTY) as $caracter) {
            $prueba = $fragmento . $caracter;
            if ($fragmento !== '' && ticketRasterTextWidth($prueba, $tamano, $fuente) > $anchoMaximo) {
                $lineas[] = $fragmento;
                $fragmento = $caracter;
            } else {
                $fragmento = $prueba;
            }
        }
        $linea = $fragmento;
    }
    if ($linea !== '') $lineas[] = $linea;
    return $lineas ?: array('');
}

function ticketRasterDrawText($imagen, $texto, $tamano, $x, $y, $fuente, $color, $alineacion = 'left')
{
    $ancho = ticketRasterTextWidth($texto, $tamano, $fuente);
    if ($alineacion === 'center') $x -= (int) round($ancho / 2);
    if ($alineacion === 'right') $x -= $ancho;
    imagettftext($imagen, $tamano, 0, (int) $x, (int) ($y + $tamano), $color, $fuente, (string) $texto);
}

function ticketRasterDrawWrapped($imagen, $texto, $tamano, $x, &$y, $fuente, $color, $anchoMaximo, $alineacion = 'left', $interlineado = null)
{
    $interlineado = $interlineado ?: (int) ceil($tamano * 1.35);
    foreach (ticketRasterWrap($texto, $tamano, $fuente, $anchoMaximo) as $linea) {
        $posicionX = $x;
        if ($alineacion === 'center') $posicionX = $x + ($anchoMaximo / 2);
        if ($alineacion === 'right') $posicionX = $x + $anchoMaximo;
        ticketRasterDrawText($imagen, $linea, $tamano, $posicionX, $y, $fuente, $color, $alineacion);
        $y += $interlineado;
    }
}

function ticketRasterDashedLine($imagen, $x1, $x2, $y, $color, $grosor = 1)
{
    imagesetthickness($imagen, $grosor);
    for ($x = $x1; $x < $x2; $x += 14) {
        imageline($imagen, $x, $y, min($x + 7, $x2), $y, $color);
    }
    imagesetthickness($imagen, 1);
}

function ticketRasterLoadImage($ruta)
{
    if (!$ruta || !is_file($ruta)) return null;
    $contenido = @file_get_contents($ruta);
    return $contenido === false ? null : (@imagecreatefromstring($contenido) ?: null);
}

function ticketRasterCropWhiteMargins($imagen, $umbral = 220, $margen = 12)
{
    if (!$imagen) return $imagen;
    $ancho = imagesx($imagen);
    $alto = imagesy($imagen);
    if ($ancho <= 0 || $alto <= 0) return $imagen;

    $minX = $ancho;
    $minY = $alto;
    $maxX = -1;
    $maxY = -1;
    $paso = max(1, (int) floor(max($ancho, $alto) / 900));

    for ($y = 0; $y < $alto; $y += $paso) {
        for ($x = 0; $x < $ancho; $x += $paso) {
            $color = imagecolorat($imagen, $x, $y);
            $rojo = ($color >> 16) & 0xff;
            $verde = ($color >> 8) & 0xff;
            $azul = $color & 0xff;
            $luminancia = ($rojo * .299) + ($verde * .587) + ($azul * .114);
            $saturacion = max($rojo, $verde, $azul) - min($rojo, $verde, $azul);
            // Ignora el ruido casi blanco propio de los JPG. Los colores del
            // logotipo siguen contando como contenido aunque sean luminosos.
            if ($luminancia < $umbral || ($saturacion >= 22 && $luminancia < 248)) {
                if ($x < $minX) $minX = $x;
                if ($y < $minY) $minY = $y;
                if ($x > $maxX) $maxX = $x;
                if ($y > $maxY) $maxY = $y;
            }
        }
    }

    if ($maxX < $minX || $maxY < $minY) return $imagen;
    $minX = max(0, $minX - $margen);
    $minY = max(0, $minY - $margen);
    $maxX = min($ancho - 1, $maxX + $margen);
    $maxY = min($alto - 1, $maxY + $margen);
    $recortada = imagecrop($imagen, array(
        'x' => $minX,
        'y' => $minY,
        'width' => ($maxX - $minX) + 1,
        'height' => ($maxY - $minY) + 1
    ));
    return $recortada ?: $imagen;
}

/**
 * Convierte el logo corporativo a una escala monocromatica de alto contraste.
 * Las impresoras termicas reproducen los colores claros con muy poca fuerza;
 * este ajuste conserva el fondo blanco y lleva el contenido del logo a negro
 * intenso sin depender de la configuracion del controlador de impresion.
 */
function ticketRasterIntensifyLogo($imagen)
{
    if (!$imagen) return $imagen;

    $ancho = imagesx($imagen);
    $alto = imagesy($imagen);
    if ($ancho <= 0 || $alto <= 0) return $imagen;

    for ($y = 0; $y < $alto; $y++) {
        for ($x = 0; $x < $ancho; $x++) {
            $color = imagecolorat($imagen, $x, $y);
            $alpha = ($color >> 24) & 0x7f;
            $opacidad = (127 - $alpha) / 127;
            $rojo = 255 - ((255 - (($color >> 16) & 0xff)) * $opacidad);
            $verde = 255 - ((255 - (($color >> 8) & 0xff)) * $opacidad);
            $azul = 255 - ((255 - ($color & 0xff)) * $opacidad);
            $luminancia = (int) round(($rojo * .299) + ($verde * .587) + ($azul * .114));

            $saturacion = max($rojo, $verde, $azul) - min($rojo, $verde, $azul);
            // El fondo queda blanco puro y el contenido negro puro. Esto evita
            // que la termica convierta el fondo JPG en un rectangulo tramado.
            $esContenido = $luminancia < 210 || ($saturacion >= 18 && $luminancia < 248);
            $nivel = $esContenido ? 0 : 255;

            imagesetpixel($imagen, $x, $y, ($nivel << 16) | ($nivel << 8) | $nivel);
        }
    }

    return $imagen;
}

function ticketRasterPasteCentered($destino, $origen, $y, $anchoMaximo, $altoMaximo, $escalaMaxima = 1)
{
    if (!$origen) return 0;
    $ancho = imagesx($origen);
    $alto = imagesy($origen);
    if ($ancho <= 0 || $alto <= 0) return 0;
    $escala = min($anchoMaximo / $ancho, $altoMaximo / $alto, $escalaMaxima);
    $nuevoAncho = max(1, (int) round($ancho * $escala));
    $nuevoAlto = max(1, (int) round($alto * $escala));
    $x = (int) round((imagesx($destino) - $nuevoAncho) / 2);
    imagealphablending($destino, true);
    imagecopyresampled($destino, $origen, $x, $y, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
    return $nuevoAlto;
}

function ticketRasterMetaRow($imagen, $etiqueta, $valor, &$y, $fuenteNegrita, $negro, $gris, $margen, $anchoContenido)
{
    $xValor = $margen + 185;
    $anchoValor = $anchoContenido - 185;
    $lineas = ticketRasterWrap($valor, 17, $fuenteNegrita, $anchoValor);
    ticketRasterDrawText($imagen, $etiqueta, 15, $margen, $y + 1, $fuenteNegrita, $gris);
    $lineaY = $y;
    foreach ($lineas as $linea) {
        ticketRasterDrawText($imagen, $linea, 17, $xValor, $lineaY, $fuenteNegrita, $negro);
        $lineaY += 24;
    }
    $y = max($y + 24, $lineaY) + 3;
}

function ticketRasterMoney($valor)
{
    return number_format((float) $valor, 2, '.', ',');
}

function construirTicketRaster($venta, $productos, $empresa, $pagos, $opciones)
{
    $ancho = 576;
    $altoMaximo = 4200;
    $margen = 22;
    $anchoContenido = $ancho - ($margen * 2);
    $fuenteNormal = $opciones['fuente_normal'];
    $fuenteNegrita = $opciones['fuente_negrita'];
    $imagen = imagecreatetruecolor($ancho, $altoMaximo);
    $blanco = imagecolorallocate($imagen, 255, 255, 255);
    $negro = imagecolorallocate($imagen, 0, 0, 0);
    $gris = imagecolorallocate($imagen, 45, 45, 45);
    imagefill($imagen, 0, 0, $blanco);

    $y = 6;
    $logo = ticketRasterLoadImage($opciones['logo']);
    if ($logo) {
        $logoOriginal = $logo;
        $logo = ticketRasterCropWhiteMargins($logoOriginal);
        if ($logo !== $logoOriginal) imagedestroy($logoOriginal);
        $logo = ticketRasterIntensifyLogo($logo);
        // Aprovecha el ancho de la impresora de 80 mm para que los detalles
        // del logo corporativo se lean con nitidez.
        $y += ticketRasterPasteCentered($imagen, $logo, $y, 510, 190, 1.75) + 8;
        imagedestroy($logo);
    }
    $tituloMarca = trim((string) (isset($opciones['titulo_marca']) ? $opciones['titulo_marca'] : 'IMPORT COMPANY'));
    if ($tituloMarca !== '') {
        ticketRasterDrawWrapped($imagen, strtoupper($tituloMarca), 21, $margen, $y, $fuenteNegrita, $negro, $anchoContenido, 'center', 28);
    }
    ticketRasterDrawWrapped($imagen, strtoupper(trim((string) $empresa['razon_social'])), 19, $margen, $y, $fuenteNegrita, $negro, $anchoContenido, 'center', 26);
    if (trim((string) $empresa['ruc']) !== '') {
        ticketRasterDrawText($imagen, 'RUC ' . $empresa['ruc'], 17, $ancho / 2, $y, $fuenteNegrita, $negro, 'center');
        $y += 24;
    }
    $actividad = trim((string) (isset($empresa['actividad']) ? $empresa['actividad'] : ''));
    if ($actividad !== '') ticketRasterDrawWrapped($imagen, strtoupper($actividad), 18, $margen, $y, $fuenteNegrita, $negro, $anchoContenido, 'center', 24);
    if (trim((string) $empresa['direccion']) !== '') {
        ticketRasterDrawWrapped($imagen, strtoupper($empresa['direccion']), 16, $margen + 20, $y, $fuenteNegrita, $negro, $anchoContenido - 40, 'center', 22);
    }
    if (trim((string) $empresa['ubicacion']) !== '') {
        ticketRasterDrawWrapped($imagen, $empresa['ubicacion'], 15, $margen + 20, $y, $fuenteNegrita, $negro, $anchoContenido - 40, 'center', 21);
    }
    if (!empty($empresa['telefono'])) {
        ticketRasterDrawText($imagen, 'TEL. ' . $empresa['telefono'], 14, $ancho / 2, $y, $fuenteNegrita, $gris, 'center');
        $y += 21;
    }

    $y += 10;
    $tipoSunat = str_pad((string) (isset($venta['CodSunat']) ? $venta['CodSunat'] : ''), 2, '0', STR_PAD_LEFT);
    $tipos = array('01' => 'FACTURA ELECTRÓNICA', '03' => 'BOLETA DE VENTA ELECTRÓNICA', '07' => 'NOTA DE CRÉDITO ELECTRÓNICA');
    $tipoDocumento = isset($tipos[$tipoSunat]) ? $tipos[$tipoSunat] : strtoupper(trim((string) (isset($venta['TipoDoc']) ? $venta['TipoDoc'] : 'COMPROBANTE DE VENTA')));
    $numeroDocumento = trim((string) $venta['Serie']) . '-' . str_pad((string) $venta['Numero'], 8, '0', STR_PAD_LEFT);
    imagesetthickness($imagen, 2);
    imagerectangle($imagen, $margen, $y, $ancho - $margen, $y + 92, $negro);
    imagesetthickness($imagen, 1);
    ticketRasterDrawText($imagen, $tipoDocumento, 19, $ancho / 2, $y + 13, $fuenteNegrita, $negro, 'center');
    ticketRasterDrawText($imagen, $numeroDocumento, 28, $ancho / 2, $y + 48, $fuenteNegrita, $negro, 'center');
    $y += 110;

    ticketRasterDashedLine($imagen, $margen, $ancho - $margen, $y, $gris);
    $y += 10;
    $fecha = !empty($venta['FechaDoc']) ? date('d/m/Y H:i:s', strtotime($venta['FechaDoc'])) : date('d/m/Y H:i:s');
    $cliente = strtoupper(trim((string) (isset($venta['Cliente']) ? $venta['Cliente'] : 'CLIENTE VARIOS')));
    $documentoCliente = trim((string) (isset($venta['DniRuc']) ? $venta['DniRuc'] : '00000000'));
    $direccionCliente = strtoupper(trim((string) (isset($venta['Direccion']) ? $venta['Direccion'] : '')));
    ticketRasterMetaRow($imagen, 'FECHA EMISIÓN', $fecha, $y, $fuenteNegrita, $negro, $gris, $margen, $anchoContenido);
    ticketRasterMetaRow($imagen, 'CLIENTE', $cliente ?: 'CLIENTE VARIOS', $y, $fuenteNegrita, $negro, $gris, $margen, $anchoContenido);
    ticketRasterMetaRow($imagen, 'DNI / RUC', $documentoCliente ?: '00000000', $y, $fuenteNegrita, $negro, $gris, $margen, $anchoContenido);
    ticketRasterMetaRow($imagen, 'DIRECCIÓN', $direccionCliente ?: '-', $y, $fuenteNegrita, $negro, $gris, $margen, $anchoContenido);
    ticketRasterDashedLine($imagen, $margen, $ancho - $margen, $y, $gris);
    $y += 14;

    $xDescripcion = $margen;
    $anchoDescripcion = 270;
    $xCantidad = $margen + 310;
    $xUnitario = $margen + 398;
    $xImporte = $ancho - $margen;
    ticketRasterDrawText($imagen, 'DESCRIPCIÓN', 14, $xDescripcion, $y, $fuenteNegrita, $negro);
    ticketRasterDrawText($imagen, 'CANT.', 14, $xCantidad, $y, $fuenteNegrita, $negro, 'center');
    ticketRasterDrawText($imagen, 'P. UNIT.', 14, $xUnitario, $y, $fuenteNegrita, $negro, 'center');
    ticketRasterDrawText($imagen, 'IMPORTE', 14, $xImporte, $y, $fuenteNegrita, $negro, 'right');
    $y += 24;
    imagesetthickness($imagen, 2);
    imageline($imagen, $margen, $y, $ancho - $margen, $y, $negro);
    imagesetthickness($imagen, 1);
    $y += 8;

    $totalBruto = 0.0;
    $descuento = 0.0;
    foreach ($productos as $indice => $producto) {
        $cantidad = (float) $producto['Cantidad'];
        $precio = (float) $producto['Precio'];
        $importe = isset($producto['TOTAL']) ? (float) $producto['TOTAL'] : $cantidad * $precio;
        $totalBruto += $importe;
        $descuento += isset($producto['Descuento']) ? (float) $producto['Descuento'] : 0;
        $codigoProducto = '';
        if (isset($producto['CodigoBarra'])) {
            $codigoProducto = trim((string) $producto['CodigoBarra']);
        } elseif (isset($producto['Codigo'])) {
            $codigoProducto = trim((string) $producto['Codigo']);
        }
        $nombreProducto = isset($producto['Producto']) ? trim((string) $producto['Producto']) : '';
        $nombre = ($indice + 1) . '. ';
        if ($codigoProducto !== '') {
            $nombre .= strtoupper($codigoProducto) . ' - ';
        }
        $nombre .= strtoupper($nombreProducto);
        $descripcionProducto = isset($producto['Descripcion'])
            ? preg_replace('/\s+/u', ' ', trim((string) $producto['Descripcion']))
            : '';
        $descripcionProducto = trim($descripcionProducto, " \t\n\r\0\x0B()");
        if ($descripcionProducto !== '') {
            $nombre .= ' (' . strtoupper($descripcionProducto) . ')';
        }
        $lineasNombre = ticketRasterWrap($nombre, 17, $fuenteNegrita, $anchoDescripcion);
        $detalle = trim((isset($producto['ProductoModelo']) ? $producto['ProductoModelo'] : '') . ' ' . (isset($producto['ProductoMedicion']) ? $producto['ProductoMedicion'] : ''));
        $lineasDetalle = $detalle !== '' ? ticketRasterWrap(strtoupper($detalle), 14, $fuenteNormal, $anchoDescripcion) : array();
        $altoFila = max(48, count($lineasNombre) * 24 + count($lineasDetalle) * 20 + 7);
        $lineaY = $y;
        foreach ($lineasNombre as $linea) {
            ticketRasterDrawText($imagen, $linea, 17, $xDescripcion, $lineaY, $fuenteNegrita, $negro);
            $lineaY += 24;
        }
        foreach ($lineasDetalle as $linea) {
            ticketRasterDrawText($imagen, $linea, 14, $xDescripcion, $lineaY, $fuenteNormal, $gris);
            $lineaY += 20;
        }
        $cantidadTexto = rtrim(rtrim(number_format($cantidad, 2, '.', ''), '0'), '.');
        ticketRasterDrawText($imagen, $cantidadTexto, 17, $xCantidad, $y + 2, $fuenteNegrita, $negro, 'center');
        ticketRasterDrawText($imagen, 'S/ ' . ticketRasterMoney($precio), 16, $xUnitario + 24, $y + 2, $fuenteNegrita, $negro, 'right');
        ticketRasterDrawText($imagen, 'S/ ' . ticketRasterMoney($importe), 16, $xImporte, $y + 2, $fuenteNegrita, $negro, 'right');
        $y += $altoFila;
        ticketRasterDashedLine($imagen, $margen, $ancho - $margen, $y, $gris);
        $y += 8;
    }

    $total = max(0, $totalBruto - $descuento);
    $tieneIgv = !empty($venta['TieneIgv']);
    $baseGravada = $tieneIgv ? $total / 1.18 : 0;
    $igv = $tieneIgv ? $total - $baseGravada : 0;
    $exonerada = $tieneIgv ? 0 : $total;
    $xResumen = 292;
    $filasResumen = array(array('SUBTOTAL', $total));
    if ($tieneIgv) {
        $filasResumen[] = array('OP. GRAVADA', $baseGravada);
        $filasResumen[] = array('IGV (18%)', $igv);
    } else {
        $filasResumen[] = array('OP. EXONERADA', $exonerada);
    }
    if ($descuento > 0) $filasResumen[] = array('DESCUENTO', -$descuento);
    foreach ($filasResumen as $filaResumen) {
        ticketRasterDrawText($imagen, $filaResumen[0], 16, $xResumen, $y, $fuenteNegrita, $negro);
        $prefijo = $filaResumen[1] < 0 ? '- S/ ' : 'S/ ';
        ticketRasterDrawText($imagen, $prefijo . ticketRasterMoney(abs($filaResumen[1])), 18, $ancho - $margen, $y, $fuenteNegrita, $negro, 'right');
        $y += 25;
    }
    $y += 3;
    imagesetthickness($imagen, 3);
    imageline($imagen, $xResumen, $y, $ancho - $margen, $y, $negro);
    $y += 8;
    ticketRasterDrawText($imagen, 'TOTAL', 28, $margen, $y, $fuenteNegrita, $negro);
    ticketRasterDrawText($imagen, 'S/ ' . ticketRasterMoney($total), 29, $ancho - $margen, $y, $fuenteNegrita, $negro, 'right');
    $y += 41;
    imageline($imagen, $xResumen, $y, $ancho - $margen, $y, $negro);
    imagesetthickness($imagen, 1);
    $y += 15;

    if (!empty($opciones['total_letras'])) {
        ticketRasterDrawWrapped($imagen, 'SON: ' . strtoupper($opciones['total_letras']), 15, $margen, $y, $fuenteNegrita, $negro, $anchoContenido, 'left', 21);
        $y += 4;
    }
    $vendedor = strtoupper(trim((string) (isset($venta['UsuarioReg']) ? $venta['UsuarioReg'] : '')));
    $formaPago = empty($venta['EsCredito']) ? 'CONTADO' : 'CRÉDITO';
    ticketRasterDrawText($imagen, 'VENDEDOR: ' . ($vendedor ?: '-'), 16, $margen, $y, $fuenteNegrita, $negro);
    $y += 23;
    ticketRasterDrawText($imagen, 'FORMA DE PAGO: ' . $formaPago, 16, $margen, $y, $fuenteNegrita, $negro);
    $y += 23;
    ticketRasterDrawText($imagen, 'MÉTODO(S) DE PAGO', 16, $margen, $y, $fuenteNegrita, $negro);
    $y += 23;
    if ($pagos) {
        foreach ($pagos as $pago) {
            ticketRasterDrawText($imagen, strtoupper($pago['MetodoPago']), 16, $margen, $y, $fuenteNegrita, $negro);
            ticketRasterDrawText($imagen, 'S/ ' . ticketRasterMoney($pago['Importe']), 16, $ancho - $margen, $y, $fuenteNegrita, $negro, 'right');
            $y += 23;
        }
    } else {
        $metodo = strtoupper(trim((string) (isset($venta['MetodoPago']) ? $venta['MetodoPago'] : 'EFECTIVO')));
        ticketRasterDrawText($imagen, $metodo ?: 'EFECTIVO', 16, $margen, $y, $fuenteNegrita, $negro);
        ticketRasterDrawText($imagen, 'S/ ' . ticketRasterMoney($total), 16, $ancho - $margen, $y, $fuenteNegrita, $negro, 'right');
        $y += 23;
    }
    $pagoCon = isset($venta['PagoCon']) ? (float) $venta['PagoCon'] : $total;
    if ($pagoCon <= 0) $pagoCon = $total;
    $vuelto = max(0, $pagoCon - $total);
    ticketRasterDrawText($imagen, 'RECIBIDO', 16, $margen, $y, $fuenteNegrita, $negro);
    ticketRasterDrawText($imagen, 'S/ ' . ticketRasterMoney($pagoCon), 16, $ancho - $margen, $y, $fuenteNegrita, $negro, 'right');
    $y += 23;
    ticketRasterDrawText($imagen, 'VUELTO', 16, $margen, $y, $fuenteNegrita, $negro);
    ticketRasterDrawText($imagen, 'S/ ' . ticketRasterMoney($vuelto), 16, $ancho - $margen, $y, $fuenteNegrita, $negro, 'right');
    $y += 29;

    ticketRasterDashedLine($imagen, $margen, $ancho - $margen, $y, $gris);
    $y += 14;
    $qr = isset($opciones['qr_imagen']) ? $opciones['qr_imagen'] : null;
    if ($qr) imagecopyresampled($imagen, $qr, $margen, $y, 0, 0, 155, 155, imagesx($qr), imagesy($qr));
    $xLegal = $margen + 175;
    $anchoLegal = $ancho - $margen - $xLegal;
    $legalY = $y + 5;
    ticketRasterDrawWrapped($imagen, $opciones['resolucion_sunat'], 13, $xLegal, $legalY, $fuenteNegrita, $negro, $anchoLegal, 'left', 18);
    ticketRasterDrawWrapped($imagen, 'Representación impresa de la ' . $tipoDocumento . '.', 13, $xLegal, $legalY, $fuenteNormal, $gris, $anchoLegal, 'left', 18);
    ticketRasterDrawText($imagen, 'CONSULTA WEB:', 13, $xLegal, $legalY, $fuenteNegrita, $negro);
    $legalY += 18;
    ticketRasterDrawWrapped($imagen, $opciones['consulta_web'], 13, $xLegal, $legalY, $fuenteNormal, $gris, $anchoLegal, 'left', 18);
    $y += max(160, $legalY - $y) + 12;

    ticketRasterDashedLine($imagen, $margen, $ancho - $margen, $y, $gris);
    $y += 14;
    ticketRasterDrawWrapped($imagen, $opciones['mensaje_ticket'], 14, $margen + 20, $y, $fuenteNegrita, $gris, $anchoContenido - 40, 'center', 20);
    $y += 4;
    ticketRasterDrawText($imagen, '¡GRACIAS POR SU PREFERENCIA!', 19, $ancho / 2, $y, $fuenteNegrita, $negro, 'center');
    $y += 28;
    ticketRasterDrawText($imagen, 'DESARROLLADO POR NEUROSOFT.COM.PE', 13, $ancho / 2, $y, $fuenteNegrita, $gris, 'center');
    $y += 34;

    $altoFinal = min($altoMaximo, max(200, $y));
    $ticket = imagecreatetruecolor($ancho, $altoFinal);
    $blancoFinal = imagecolorallocate($ticket, 255, 255, 255);
    imagefill($ticket, 0, 0, $blancoFinal);
    imagecopy($ticket, $imagen, 0, 0, 0, 0, $ancho, $altoFinal);
    imagedestroy($imagen);
    return $ticket;
}

function ticketRasterToEscpos($imagen, $umbral = 185)
{
    $ancho = imagesx($imagen);
    $alto = imagesy($imagen);
    $bytesPorFila = (int) ceil($ancho / 8);
    $salida = "\x1B\x40";
    for ($inicioY = 0; $inicioY < $alto; $inicioY += 240) {
        $altoActual = min(240, $alto - $inicioY);
        $datos = '';
        for ($y = 0; $y < $altoActual; $y++) {
            for ($byteX = 0; $byteX < $bytesPorFila; $byteX++) {
                $byte = 0;
                for ($bit = 0; $bit < 8; $bit++) {
                    $x = ($byteX * 8) + $bit;
                    if ($x >= $ancho) continue;
                    $rgb = imagecolorat($imagen, $x, $inicioY + $y);
                    $rojo = ($rgb >> 16) & 0xFF;
                    $verde = ($rgb >> 8) & 0xFF;
                    $azul = $rgb & 0xFF;
                    $luminancia = (0.299 * $rojo) + (0.587 * $verde) + (0.114 * $azul);
                    if ($luminancia < $umbral) $byte |= (1 << (7 - $bit));
                }
                $datos .= chr($byte);
            }
        }
        $salida .= "\x1D\x76\x30\x00" . pack('v', $bytesPorFila) . pack('v', $altoActual) . $datos;
    }
    return $salida . "\n\n\n\x1D\x56\x00";
}

