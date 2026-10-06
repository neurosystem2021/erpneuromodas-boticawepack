<?php

function inventarioInicialTexto($valor)
{
    if ($valor === null) return '';
    if (is_object($valor) && method_exists($valor, 'getPlainText')) $valor = $valor->getPlainText();
    return trim(preg_replace('/\s+/u', ' ', (string) $valor));
}

function inventarioInicialClave($valor)
{
    $texto = mb_strtoupper(inventarioInicialTexto($valor), 'UTF-8');
    $texto = strtr($texto, array(
        'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N'
    ));
    return preg_replace('/[^A-Z0-9]+/', '', $texto);
}

function inventarioInicialNumero($valor, &$valido)
{
    $valido = true;
    if (is_int($valor) || is_float($valor)) return (float) $valor;
    $texto = inventarioInicialTexto($valor);
    if ($texto === '') {
        $valido = false;
        return 0;
    }
    $texto = str_ireplace(array('S/', 'PEN', ' '), '', $texto);
    $ultimaComa = strrpos($texto, ',');
    $ultimoPunto = strrpos($texto, '.');
    if ($ultimaComa !== false && $ultimoPunto !== false) {
        if ($ultimaComa > $ultimoPunto) {
            $texto = str_replace('.', '', $texto);
            $texto = str_replace(',', '.', $texto);
        } else {
            $texto = str_replace(',', '', $texto);
        }
    } elseif ($ultimaComa !== false) {
        $texto = str_replace(',', '.', $texto);
    }
    $texto = preg_replace('/[^0-9.\-]/', '', $texto);
    if ($texto === '' || !is_numeric($texto)) {
        $valido = false;
        return 0;
    }
    return (float) $texto;
}

function inventarioInicialMovimientoExistente($db, $idAlmacen)
{
    $sql = "SELECT Hash, Serie, Numero, FechaReg, UsuarioReg
        FROM Lo_Movimiento
        WHERE IdAlmacenDestino=?
          AND COALESCE(Anulado, 0)=0
          AND UPPER(TRIM(COALESCE(Observacion, ''))) LIKE 'INGRESO INICIAL DE INVENTARIO%'
        ORDER BY FechaReg DESC LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $idAlmacen);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    return $fila ?: null;
}

function inventarioInicialAnalizarArchivo($db, $ruta, $nombreArchivo, $idAlmacen)
{
    $tipo = \PhpOffice\PhpSpreadsheet\IOFactory::identify($ruta);
    if (!in_array($tipo, array('Xlsx', 'Xls'), true)) {
        throw new RuntimeException('El archivo debe estar en formato XLS o XLSX.');
    }

    $lector = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($tipo);
    $lector->setReadDataOnly(true);
    $libro = $lector->load($ruta);
    $hoja = $libro->getActiveSheet();
    $filas = $hoja->toArray(null, true, true, false);

    $alias = array(
        'codigo' => array('CODIGO', 'CODIGOBARRA', 'SKU'),
        'producto' => array('PRODUCTO', 'ARTICULO', 'DESCRIPCIONPRODUCTO'),
        'stock' => array('STOCK', 'EXISTENCIA', 'CANTIDAD', 'STOCKACTUAL', 'SALDO'),
        'precio_costo' => array('PRECIOCOSTO', 'COSTO', 'PRECIOCOMPRA', 'PUNITARIO', 'PRECIOUNITARIO')
    );
    $columnas = array();
    $filaCabecera = null;
    $limiteCabecera = min(count($filas), 25);
    for ($i = 0; $i < $limiteCabecera; $i++) {
        $encontradas = array();
        foreach ($filas[$i] as $indice => $celda) {
            $clave = inventarioInicialClave($celda);
            foreach ($alias as $campo => $opciones) {
                if (in_array($clave, $opciones, true)) $encontradas[$campo] = $indice;
            }
        }
        if (isset($encontradas['codigo']) && isset($encontradas['stock'])) {
            $columnas = $encontradas;
            $filaCabecera = $i;
            break;
        }
    }
    if ($filaCabecera === null) {
        $libro->disconnectWorksheets();
        throw new RuntimeException('No se encontraron las columnas requeridas CODIGO y STOCK.');
    }

    $candidatos = array();
    $errores = array();
    $codigosArchivo = array();
    $totalFilas = 0;
    $filasCero = 0;
    $filasNegativas = 0;
    $muestraNegativos = array();

    for ($i = $filaCabecera + 1; $i < count($filas); $i++) {
        $fila = $filas[$i];
        $codigoValor = isset($fila[$columnas['codigo']]) ? $fila[$columnas['codigo']] : null;
        $stockValor = isset($fila[$columnas['stock']]) ? $fila[$columnas['stock']] : null;
        $productoValor = isset($columnas['producto'], $fila[$columnas['producto']]) ? $fila[$columnas['producto']] : null;
        if (inventarioInicialTexto($codigoValor) === '' && inventarioInicialTexto($stockValor) === '' && inventarioInicialTexto($productoValor) === '') continue;

        $totalFilas++;
        $numeroFila = $i + 1;
        $codigo = mb_strtoupper(inventarioInicialTexto($codigoValor), 'UTF-8');
        $claveCodigo = inventarioInicialClave($codigo);
        $mensajes = array();
        if ($codigo === '') $mensajes[] = 'El código está vacío.';
        if ($claveCodigo !== '' && isset($codigosArchivo[$claveCodigo])) {
            $mensajes[] = 'El código también aparece en la fila ' . $codigosArchivo[$claveCodigo] . '.';
        } elseif ($claveCodigo !== '') {
            $codigosArchivo[$claveCodigo] = $numeroFila;
        }

        $stockValido = false;
        $stock = inventarioInicialNumero($stockValor, $stockValido);
        if (!$stockValido) $mensajes[] = 'El stock no es un número válido.';

        $precioCosto = null;
        if (isset($columnas['precio_costo'])) {
            $costoValor = isset($fila[$columnas['precio_costo']]) ? $fila[$columnas['precio_costo']] : null;
            if (inventarioInicialTexto($costoValor) !== '') {
                $costoValido = false;
                $precioCosto = inventarioInicialNumero($costoValor, $costoValido);
                if (!$costoValido || $precioCosto < 0) $mensajes[] = 'El precio de costo no es válido.';
            }
        }

        if ($mensajes) {
            if (count($errores) < 100) $errores[] = array(
                'fila' => $numeroFila,
                'codigo' => $codigo,
                'mensaje' => implode(' ', $mensajes)
            );
            continue;
        }
        if ($stock < 0) {
            $filasNegativas++;
            if (count($muestraNegativos) < 8) $muestraNegativos[] = array('fila' => $numeroFila, 'codigo' => $codigo, 'stock' => $stock);
            continue;
        }
        if ($stock == 0) {
            $filasCero++;
            continue;
        }

        $candidatos[] = array(
            'fila' => $numeroFila,
            'codigo' => $codigo,
            'clave' => $claveCodigo,
            'producto_excel' => inventarioInicialTexto($productoValor),
            'cantidad' => round($stock, 4),
            'precio_costo_excel' => $precioCosto === null ? null : round($precioCosto, 4)
        );
    }
    $nombreHoja = $hoja->getTitle();
    $libro->disconnectWorksheets();
    unset($libro);

    $catalogo = array('barra' => array(), 'codigo' => array());
    $resultado = $db->query("SELECT IdProducto, CodigoBarra, Codigo, Producto, PrecioCosto
        FROM Gen_Producto WHERE COALESCE(Anulado, 0)=0");
    while ($producto = $resultado->fetch_assoc()) {
        $id = (int) $producto['IdProducto'];
        $claveBarra = inventarioInicialClave($producto['CodigoBarra']);
        $claveInterna = inventarioInicialClave($producto['Codigo']);
        if ($claveBarra !== '') $catalogo['barra'][$claveBarra][$id] = $producto;
        if ($claveInterna !== '') $catalogo['codigo'][$claveInterna][$id] = $producto;
    }
    $resultado->free();

    $productos = array();
    $totalUnidades = 0;
    $valorTotal = 0;
    $costosCatalogo = 0;
    $costosCero = 0;
    foreach ($candidatos as $candidato) {
        $coincidencias = isset($catalogo['barra'][$candidato['clave']])
            ? $catalogo['barra'][$candidato['clave']]
            : (isset($catalogo['codigo'][$candidato['clave']]) ? $catalogo['codigo'][$candidato['clave']] : array());
        if (count($coincidencias) !== 1) {
            if (count($errores) < 100) $errores[] = array(
                'fila' => $candidato['fila'],
                'codigo' => $candidato['codigo'],
                'mensaje' => count($coincidencias) === 0
                    ? 'El código no existe en el catálogo de productos.'
                    : 'El código coincide con más de un producto.'
            );
            continue;
        }
        $producto = reset($coincidencias);
        $precioCosto = $candidato['precio_costo_excel'];
        if ($precioCosto === null) {
            $precioCosto = (float) $producto['PrecioCosto'];
            $costosCatalogo++;
        }
        if ($precioCosto <= 0) $costosCero++;
        $subtotal = round($candidato['cantidad'] * $precioCosto, 4);
        $productos[] = array(
            'fila' => $candidato['fila'],
            'id_producto' => (int) $producto['IdProducto'],
            'codigo' => $candidato['codigo'],
            'producto' => $producto['Producto'],
            'cantidad' => $candidato['cantidad'],
            'precio_costo' => round($precioCosto, 4),
            'subtotal' => $subtotal
        );
        $totalUnidades += $candidato['cantidad'];
        $valorTotal += $subtotal;
    }

    $advertencias = array();
    if ($filasCero > 0) $advertencias[] = $filasCero . ' filas con stock 0 se omitirán porque no generan ingreso.';
    if ($filasNegativas > 0) $advertencias[] = $filasNegativas . ' filas con stock negativo se omitirán; un ingreso inicial solo admite cantidades positivas.';
    if ($costosCatalogo > 0) $advertencias[] = $costosCatalogo . ' productos usarán el precio de costo guardado en el catálogo.';
    if ($costosCero > 0) $advertencias[] = $costosCero . ' productos tienen costo 0; ingresarán con valor S/ 0.00.';

    $existente = inventarioInicialMovimientoExistente($db, $idAlmacen);
    if ($existente) $advertencias[] = 'Este almacén ya tiene un ingreso inicial registrado y no se puede duplicar.';

    return array(
        'archivo' => basename((string) $nombreArchivo),
        'hoja' => $nombreHoja,
        'fila_cabecera' => $filaCabecera + 1,
        'total_filas' => $totalFilas,
        'productos_importar' => count($productos),
        'filas_stock_cero' => $filasCero,
        'filas_stock_negativo' => $filasNegativas,
        'muestra_negativos' => $muestraNegativos,
        'total_unidades' => round($totalUnidades, 4),
        'valor_total' => round($valorTotal, 2),
        'errores' => $errores,
        'advertencias' => $advertencias,
        'muestra' => array_slice($productos, 0, 8),
        'movimiento_existente' => $existente,
        'puede_importar' => count($productos) > 0 && count($errores) === 0 && !$existente,
        'productos' => $productos
    );
}

function inventarioInicialObtenerProveedor($db, $usuario)
{
    $nombre = 'INVENTARIO INICIAL';
    $ruc = '00000000000';
    $stmt = $db->prepare("SELECT IdProveedor FROM Lo_Proveedor WHERE UPPER(TRIM(Proveedor))=? OR Ruc=? ORDER BY IdProveedor LIMIT 1");
    $nombreBusqueda = mb_strtoupper($nombre, 'UTF-8');
    $stmt->bind_param('ss', $nombreBusqueda, $ruc);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    if ($fila) return (int) $fila['IdProveedor'];

    $observacion = 'Proveedor interno para movimientos de apertura de inventario.';
    $stmt = $db->prepare("INSERT INTO Lo_Proveedor
        (Proveedor, Ruc, Direccion, Observacion, Anulado, FechaReg, UsuarioReg)
        VALUES (?, ?, '', ?, 0, NOW(), ?)");
    $stmt->bind_param('ssss', $nombre, $ruc, $observacion, $usuario);
    $stmt->execute();
    $id = (int) $db->insert_id;
    $stmt->close();
    return $id;
}

function inventarioInicialGuardar($db, $analisis, $idAlmacen, $usuario)
{
    if (empty($analisis['puede_importar']) || empty($analisis['productos'])) {
        throw new RuntimeException('El archivo no está listo para registrar el ingreso inicial.');
    }

    $stmt = $db->prepare("SELECT Almacen FROM Lo_Almacen WHERE IdAlmacen=? AND COALESCE(Anulado, 0)=0 LIMIT 1");
    $stmt->bind_param('i', $idAlmacen);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $almacen = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    if (!$almacen) throw new RuntimeException('El almacén seleccionado no existe o está anulado.');

    $nombreBloqueo = 'inventario_inicial_' . $idAlmacen;
    $stmt = $db->prepare('SELECT GET_LOCK(?, 10) AS adquirido');
    $stmt->bind_param('s', $nombreBloqueo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $bloqueo = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    if (!$bloqueo || (int) $bloqueo['adquirido'] !== 1) throw new RuntimeException('No se pudo bloquear el almacén para el ingreso. Intente nuevamente.');

    $hash = '';
    $cabeceraInsertada = false;
    try {
        if (inventarioInicialMovimientoExistente($db, $idAlmacen)) {
            throw new RuntimeException('Ya existe un INGRESO INICIAL DE INVENTARIO para este almacén.');
        }

        $resultado = $db->query("SELECT IdMovimientoTipo FROM Lo_MovimientoTipo
            WHERE UPPER(TRIM(TipoMovimiento))='INGRESO' AND Tipo=0 AND COALESCE(Anulado, 0)=0
            ORDER BY IdMovimientoTipo LIMIT 1");
        $tipo = $resultado ? $resultado->fetch_assoc() : null;
        if ($resultado) $resultado->free();
        if (!$tipo) throw new RuntimeException('No existe el tipo de movimiento INGRESO.');
        $idTipo = (int) $tipo['IdMovimientoTipo'];
        $idProveedor = inventarioInicialObtenerProveedor($db, $usuario);
        $serie = 'INICIAL';

        $stmt = $db->prepare("SELECT COALESCE(MAX(Numero), 0) + 1 AS siguiente
            FROM Lo_Movimiento WHERE IdMovimientoTipo=? AND IdProveedor=? AND Serie=?");
        $stmt->bind_param('iis', $idTipo, $idProveedor, $serie);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $filaNumero = $resultado ? $resultado->fetch_assoc() : null;
        $stmt->close();
        $numero = max(1, (int) ($filaNumero ? $filaNumero['siguiente'] : 1));

        $hash = 'INI-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
        $observacion = 'INGRESO INICIAL DE INVENTARIO | Archivo: ' . basename((string) $analisis['archivo']);
        $periodo = (int) date('Ym');

        $db->begin_transaction();
        $stmt = $db->prepare("INSERT INTO Lo_Movimiento
            (IdMovimientoTipo, IdProveedor, Serie, Numero, MovimientoFecha, IdAlmacenOrigen,
             IdAlmacenDestino, Observacion, Anulado, FechaReg, UsuarioReg, Hash, FechaStock,
             Percepcion, EsCredito, FechaVenCredito, FechaPeriodoTributario, TipoCambio, Moneda)
            VALUES (?, ?, ?, ?, NOW(), -1, ?, ?, 0, NOW(), ?, ?, NOW(), 0, 0, NULL, ?, 1, 'PEN')");
        $stmt->bind_param('iisiisssi', $idTipo, $idProveedor, $serie, $numero, $idAlmacen, $observacion, $usuario, $hash, $periodo);
        $stmt->execute();
        $stmt->close();
        $cabeceraInsertada = true;

        $detalle = $db->prepare("INSERT INTO Lo_MovimientoDetalle
            (hashMovimiento, IdProducto, Cantidad, TieneIgv, Precio, IdLote, Descripcion)
            VALUES (?, ?, ?, 0, ?, 0, ?)");
        $buscarTotal = $db->prepare("SELECT COUNT(*) AS cantidad FROM Gen_Producto_Totales
            WHERE IdAlmacen=? AND IdProducto=? FOR UPDATE");
        $actualizarTotal = $db->prepare("UPDATE Gen_Producto_Totales
            SET TotalCompras=COALESCE(TotalCompras, 0)+?
            WHERE IdAlmacen=? AND IdProducto=?");
        $insertarTotal = $db->prepare("INSERT INTO Gen_Producto_Totales
            (IdAlmacen, IdProducto, TotalCompras, TotalVentas) VALUES (?, ?, ?, 0)");

        foreach ($analisis['productos'] as $producto) {
            $idProducto = (int) $producto['id_producto'];
            $cantidad = (float) $producto['cantidad'];
            $precio = (float) $producto['precio_costo'];
            $descripcion = 'Ingreso inicial desde Excel, fila ' . (int) $producto['fila'];
            $detalle->bind_param('sidds', $hash, $idProducto, $cantidad, $precio, $descripcion);
            $detalle->execute();

            $buscarTotal->bind_param('ii', $idAlmacen, $idProducto);
            $buscarTotal->execute();
            $resultadoTotal = $buscarTotal->get_result();
            $filaTotal = $resultadoTotal ? $resultadoTotal->fetch_assoc() : null;
            $coincidencias = (int) ($filaTotal ? $filaTotal['cantidad'] : 0);
            if ($coincidencias > 1) throw new RuntimeException('El producto ' . $producto['codigo'] . ' tiene registros de stock duplicados en el almacén.');
            if ($coincidencias === 1) {
                $actualizarTotal->bind_param('dii', $cantidad, $idAlmacen, $idProducto);
                $actualizarTotal->execute();
            } else {
                $insertarTotal->bind_param('iid', $idAlmacen, $idProducto, $cantidad);
                $insertarTotal->execute();
            }
        }

        $detalle->close();
        $buscarTotal->close();
        $actualizarTotal->close();
        $insertarTotal->close();
        $db->commit();

        return array(
            'hash' => $hash,
            'serie' => $serie,
            'numero' => $numero,
            'almacen' => $almacen['Almacen'],
            'productos' => count($analisis['productos']),
            'unidades' => $analisis['total_unidades'],
            'valor_total' => $analisis['valor_total'],
            'observacion' => $observacion
        );
    } catch (Throwable $error) {
        $db->rollback();
        if ($cabeceraInsertada && $hash !== '') {
            $stmt = $db->prepare('DELETE FROM Lo_MovimientoDetalle WHERE hashMovimiento=?');
            $stmt->bind_param('s', $hash);
            $stmt->execute();
            $stmt->close();
            $stmt = $db->prepare('DELETE FROM Lo_Movimiento WHERE Hash=?');
            $stmt->bind_param('s', $hash);
            $stmt->execute();
            $stmt->close();
        }
        throw $error;
    } finally {
        $stmt = $db->prepare('SELECT RELEASE_LOCK(?)');
        $stmt->bind_param('s', $nombreBloqueo);
        $stmt->execute();
        $stmt->close();
    }
}

