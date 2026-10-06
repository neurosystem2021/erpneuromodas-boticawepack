<?php

function productoImportadorTexto($valor)
{
    if ($valor === null) return '';
    if (is_object($valor) && method_exists($valor, 'getPlainText')) $valor = $valor->getPlainText();
    return trim(preg_replace('/\s+/u', ' ', (string) $valor));
}

function productoImportadorClaveCabecera($valor)
{
    $texto = mb_strtoupper(productoImportadorTexto($valor), 'UTF-8');
    $texto = strtr($texto, array(
        'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N'
    ));
    return preg_replace('/[^A-Z0-9]+/', '', $texto);
}

function productoImportadorClaveCatalogo($valor)
{
    return mb_strtoupper(productoImportadorTexto($valor), 'UTF-8');
}

function productoImportadorNumero($valor, &$valido)
{
    $valido = true;
    if (is_int($valor) || is_float($valor)) return (float) $valor;
    $texto = productoImportadorTexto($valor);
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

function productoImportadorAnalizarArchivo($ruta, $nombreArchivo = '')
{
    $tipo = \PhpOffice\PhpSpreadsheet\IOFactory::identify($ruta);
    if (!in_array($tipo, array('Xlsx', 'Xls'), true)) {
        throw new RuntimeException('El archivo debe estar en formato XLS o XLSX.');
    }

    $lector = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($tipo);
    $lector->setReadDataOnly(true);
    $libro = $lector->load($ruta);
    $hoja = $libro->getActiveSheet();
    $nombreHoja = $hoja->getTitle();
    $filas = $hoja->toArray(null, true, true, false);

    $alias = array(
        'codigo' => array('CODIGO', 'CODIGOBARRA', 'SKU'),
        'categoria' => array('CATEGORIA'),
        'producto' => array('PRODUCTO', 'DESCRIPCIONPRODUCTO'),
        'marca' => array('MARCA'),
        'modelo' => array('MODELO'),
        'precio_costo' => array('PRECIOCOSTO', 'COSTO', 'PRECIOCOMPRA'),
        'precio_contado' => array('PRECIOCONTADO', 'PRECIOVENTA', 'VENTA')
    );
    $columnas = array();
    $filaCabecera = null;
    $limiteCabecera = min(count($filas), 25);
    for ($i = 0; $i < $limiteCabecera; $i++) {
        $encontradas = array();
        foreach ($filas[$i] as $indice => $celda) {
            $clave = productoImportadorClaveCabecera($celda);
            foreach ($alias as $campo => $opciones) {
                if (in_array($clave, $opciones, true)) $encontradas[$campo] = $indice;
            }
        }
        if (count($encontradas) === count($alias)) {
            $columnas = $encontradas;
            $filaCabecera = $i;
            break;
        }
    }
    if ($filaCabecera === null) {
        throw new RuntimeException('No se encontraron las columnas requeridas: CODIGO, CATEGORIA, PRODUCTO, MARCA, MODELO, PRECIO COSTO y PRECIO CONTADO.');
    }

    $productos = array();
    $errores = array();
    $codigosArchivo = array();
    $totalFilas = 0;
    $filasInvalidas = 0;
    for ($i = $filaCabecera + 1; $i < count($filas); $i++) {
        $fila = $filas[$i];
        $valores = array();
        foreach ($columnas as $campo => $indice) {
            $valores[$campo] = isset($fila[$indice]) ? $fila[$indice] : null;
        }
        $tieneDatos = false;
        foreach ($valores as $valor) {
            if (productoImportadorTexto($valor) !== '') {
                $tieneDatos = true;
                break;
            }
        }
        if (!$tieneDatos) continue;
        $totalFilas++;
        $numeroFila = $i + 1;
        $codigo = mb_strtoupper(productoImportadorTexto($valores['codigo']), 'UTF-8');
        $categoria = mb_strtoupper(productoImportadorTexto($valores['categoria']), 'UTF-8');
        $producto = productoImportadorTexto($valores['producto']);
        $marca = mb_strtoupper(productoImportadorTexto($valores['marca']), 'UTF-8');
        $modelo = productoImportadorTexto($valores['modelo']);
        $mensajes = array();

        if ($codigo === '') $mensajes[] = 'El código está vacío.';
        if ($categoria === '') $mensajes[] = 'La categoría está vacía.';
        if ($producto === '') $mensajes[] = 'El producto está vacío.';
        if ($marca === '') $mensajes[] = 'La marca está vacía.';
        if (mb_strlen($codigo, 'UTF-8') > 255) $mensajes[] = 'El código supera 255 caracteres.';
        if (mb_strlen($categoria, 'UTF-8') > 255) $mensajes[] = 'La categoría supera 255 caracteres.';
        if (mb_strlen($producto, 'UTF-8') > 255) $mensajes[] = 'El producto supera 255 caracteres.';
        if (mb_strlen($marca, 'UTF-8') > 255) $mensajes[] = 'La marca supera 255 caracteres.';
        if (mb_strlen($modelo, 'UTF-8') > 255) $mensajes[] = 'El modelo supera 255 caracteres.';

        $costoValido = false;
        $precioCosto = productoImportadorNumero($valores['precio_costo'], $costoValido);
        $ventaValida = false;
        $precioContado = productoImportadorNumero($valores['precio_contado'], $ventaValida);
        if (!$costoValido || $precioCosto < 0) $mensajes[] = 'El precio de costo no es válido.';
        if (!$ventaValida || $precioContado < 0) $mensajes[] = 'El precio contado no es válido.';

        $claveCodigo = productoImportadorClaveCatalogo($codigo);
        if ($claveCodigo !== '' && isset($codigosArchivo[$claveCodigo])) {
            $mensajes[] = 'El código también aparece en la fila ' . $codigosArchivo[$claveCodigo] . '.';
        } elseif ($claveCodigo !== '') {
            $codigosArchivo[$claveCodigo] = $numeroFila;
        }

        if ($mensajes) {
            $filasInvalidas++;
            if (count($errores) < 100) {
                $errores[] = array('fila' => $numeroFila, 'codigo' => $codigo, 'mensaje' => implode(' ', $mensajes));
            }
            continue;
        }

        $productos[] = array(
            'fila' => $numeroFila,
            'codigo' => $codigo,
            'categoria' => $categoria,
            'producto' => $producto,
            'marca' => $marca,
            'modelo' => $modelo,
            'precio_costo' => round($precioCosto, 4),
            'precio_contado' => round($precioContado, 4)
        );
    }
    $libro->disconnectWorksheets();
    unset($libro);

    return array(
        'archivo' => basename((string) $nombreArchivo),
        'hoja' => $nombreHoja,
        'fila_cabecera' => $filaCabecera + 1,
        'total_filas' => $totalFilas,
        'validas' => count($productos),
        'invalidas' => $filasInvalidas,
        'errores' => $errores,
        'productos' => $productos
    );
}

function productoImportadorCargarMapa($db, $tabla, $campoId, $campoNombre)
{
    $mapa = array();
    $resultado = $db->query("SELECT `$campoId` AS id, `$campoNombre` AS nombre FROM `$tabla`");
    while ($fila = $resultado->fetch_assoc()) {
        $clave = productoImportadorClaveCatalogo($fila['nombre']);
        if ($clave !== '' && !isset($mapa[$clave])) $mapa[$clave] = (int) $fila['id'];
    }
    $resultado->free();
    return $mapa;
}

function productoImportadorResumenBase($db, $analisis)
{
    $codigos = array();
    $resultado = $db->query('SELECT CodigoBarra FROM Gen_Producto');
    while ($fila = $resultado->fetch_assoc()) $codigos[productoImportadorClaveCatalogo($fila['CodigoBarra'])] = true;
    $resultado->free();
    $categorias = productoImportadorCargarMapa($db, 'Gen_ProductoCategoria', 'IdProductoCategoria', 'ProductoCategoria');
    $marcas = productoImportadorCargarMapa($db, 'Gen_ProductoMarca', 'IdProductoMarca', 'ProductoMarca');
    $categoriasNuevas = array();
    $marcasNuevas = array();
    $creados = 0;
    $actualizados = 0;
    foreach ($analisis['productos'] as $producto) {
        if (isset($codigos[productoImportadorClaveCatalogo($producto['codigo'])])) $actualizados++; else $creados++;
        $claveCategoria = productoImportadorClaveCatalogo($producto['categoria']);
        if (!isset($categorias[$claveCategoria])) $categoriasNuevas[$claveCategoria] = $producto['categoria'];
        $claveMarca = productoImportadorClaveCatalogo($producto['marca']);
        if (!isset($marcas[$claveMarca])) $marcasNuevas[$claveMarca] = $producto['marca'];
    }
    return array(
        'productos_nuevos' => $creados,
        'productos_actualizados' => $actualizados,
        'categorias_nuevas' => count($categoriasNuevas),
        'marcas_nuevas' => count($marcasNuevas)
    );
}

function productoImportadorAsegurarCatalogo($db, &$mapa, $tabla, $campoId, $campoNombre, $nombre, $usuario, &$contador)
{
    $clave = productoImportadorClaveCatalogo($nombre);
    if (isset($mapa[$clave])) return $mapa[$clave];
    $sql = "INSERT INTO `$tabla` (`$campoNombre`, Anulado, FechaReg, UsuarioReg) VALUES (?, 0, NOW(), ?)";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('ss', $nombre, $usuario);
    $stmt->execute();
    $id = (int) $db->insert_id;
    $stmt->close();
    $mapa[$clave] = $id;
    $contador++;
    return $id;
}

function productoImportadorObtenerUnidad($db, $usuario)
{
    $resultado = $db->query("SELECT IdProductoMedicion FROM Gen_ProductoMedicion WHERE UPPER(TRIM(ProductoMedicion))='UNIDAD' ORDER BY Anulado ASC, IdProductoMedicion ASC LIMIT 1");
    $fila = $resultado->fetch_assoc();
    $resultado->free();
    if ($fila) {
        $id = (int) $fila['IdProductoMedicion'];
        $stmt = $db->prepare('UPDATE Gen_ProductoMedicion SET Anulado=0, FechaMod=NOW(), UsuarioMod=? WHERE IdProductoMedicion=?');
        $stmt->bind_param('si', $usuario, $id);
        $stmt->execute();
        $stmt->close();
        return $id;
    }
    $nombre = 'UNIDAD';
    $descripcion = 'Unidad';
    $codigo = 'NIU';
    $stmt = $db->prepare('INSERT INTO Gen_ProductoMedicion (ProductoMedicion, Descripcion, Codigo, Anulado, FechaReg, UsuarioReg) VALUES (?, ?, ?, 0, NOW(), ?)');
    $stmt->bind_param('ssss', $nombre, $descripcion, $codigo, $usuario);
    $stmt->execute();
    $id = (int) $db->insert_id;
    $stmt->close();
    return $id;
}

function productoImportadorObtenerTalla($db, $usuario)
{
    $resultado = $db->query("SELECT IdProductoTalla FROM Gen_ProductoTalla WHERE UPPER(TRIM(ProductoTalla))='STD' ORDER BY Anulado ASC, IdProductoTalla ASC LIMIT 1");
    $fila = $resultado->fetch_assoc();
    $resultado->free();
    if ($fila) return (int) $fila['IdProductoTalla'];
    $talla = 'STD';
    $stmt = $db->prepare('INSERT INTO Gen_ProductoTalla (ProductoTalla, Anulado, FechaReg, UsuarioReg) VALUES (?, 0, NOW(), ?)');
    $stmt->bind_param('ss', $talla, $usuario);
    $stmt->execute();
    $id = (int) $db->insert_id;
    $stmt->close();
    return $id;
}

function productoImportadorImportar($db, $analisis, $usuario)
{
    $forma = $db->query('SELECT IdProductoFormaFarmaceutica FROM Gen_ProductoFormaFarmaceutica ORDER BY IdProductoFormaFarmaceutica ASC LIMIT 1')->fetch_assoc();
    if (!$forma) throw new RuntimeException('No existe una forma farmacéutica base para registrar productos.');
    $idForma = (int) $forma['IdProductoFormaFarmaceutica'];
    $idMedicion = productoImportadorObtenerUnidad($db, $usuario);
    $idTalla = productoImportadorObtenerTalla($db, $usuario);
    $categorias = productoImportadorCargarMapa($db, 'Gen_ProductoCategoria', 'IdProductoCategoria', 'ProductoCategoria');
    $marcas = productoImportadorCargarMapa($db, 'Gen_ProductoMarca', 'IdProductoMarca', 'ProductoMarca');
    $productosExistentes = array();
    $resultado = $db->query('SELECT IdProducto, CodigoBarra FROM Gen_Producto FOR UPDATE');
    while ($fila = $resultado->fetch_assoc()) {
        $clave = productoImportadorClaveCatalogo($fila['CodigoBarra']);
        if ($clave !== '' && !isset($productosExistentes[$clave])) $productosExistentes[$clave] = (int) $fila['IdProducto'];
    }
    $resultado->free();

    $categoriasCreadas = 0;
    $marcasCreadas = 0;
    $creados = 0;
    $actualizados = 0;
    $stmtInsertar = $db->prepare("INSERT INTO Gen_Producto
        (IdProductoMarca, IdProductoFormaFarmaceutica, IdProductoMedicion, IdProductoCategoria, IdProductoTalla,
         Producto, ProductoDesc, ProductoDescCorto, CodigoBarra, CodeBarModelo, PrecioContado, StockMinimo,
         Anulado, FechaReg, UsuarioReg, Hash, ControlaStock, EsChatarra, PrecioCosto, PorcentajeUtilidad,
         Genero, Color, Botapie, ProductoModelo, PorcentajeLiquidacion, PreciosPorProducto, TienePresentacion, Anaquel)
        VALUES (?, ?, ?, ?, ?, ?, '', '', ?, '', ?, 0, 0, NOW(), ?, ?, 1, 0, ?, 0, '', '', '', ?, 0, '[]', 0, '')");
    $stmtActualizar = $db->prepare("UPDATE Gen_Producto SET
        IdProductoMarca=?, IdProductoFormaFarmaceutica=?, IdProductoMedicion=?, IdProductoCategoria=?, IdProductoTalla=?,
        Producto=?, CodigoBarra=?, ProductoModelo=?, PrecioCosto=?, PrecioContado=?, Anulado=0,
        FechaMod=NOW(), UsuarioMod=? WHERE IdProducto=?");
    foreach ($analisis['productos'] as $indice => $producto) {
        $idCategoria = productoImportadorAsegurarCatalogo($db, $categorias, 'Gen_ProductoCategoria', 'IdProductoCategoria', 'ProductoCategoria', $producto['categoria'], $usuario, $categoriasCreadas);
        $idMarca = productoImportadorAsegurarCatalogo($db, $marcas, 'Gen_ProductoMarca', 'IdProductoMarca', 'ProductoMarca', $producto['marca'], $usuario, $marcasCreadas);
        $claveCodigo = productoImportadorClaveCatalogo($producto['codigo']);
        $nombreProducto = $producto['producto'];
        $codigo = $producto['codigo'];
        $modelo = $producto['modelo'];
        $precioCosto = (float) $producto['precio_costo'];
        $precioContado = (float) $producto['precio_contado'];
        if (isset($productosExistentes[$claveCodigo])) {
            $idProducto = $productosExistentes[$claveCodigo];
            $stmtActualizar->bind_param('iiiiisssddsi', $idMarca, $idForma, $idMedicion, $idCategoria, $idTalla, $nombreProducto, $codigo, $modelo, $precioCosto, $precioContado, $usuario, $idProducto);
            $stmtActualizar->execute();
            $actualizados++;
        } else {
            $hash = time() . '-' . ($indice + 1) . '-' . mt_rand(1000, 9999);
            $stmtInsertar->bind_param('iiiiissdssds', $idMarca, $idForma, $idMedicion, $idCategoria, $idTalla, $nombreProducto, $codigo, $precioContado, $usuario, $hash, $precioCosto, $modelo);
            $stmtInsertar->execute();
            $idProducto = (int) $db->insert_id;
            $productosExistentes[$claveCodigo] = $idProducto;
            $creados++;
        }
    }

    $stmtInsertar->close();
    $stmtActualizar->close();

    return array(
        'productos_creados' => $creados,
        'productos_actualizados' => $actualizados,
        'categorias_creadas' => $categoriasCreadas,
        'marcas_creadas' => $marcasCreadas,
        'stock_modificado' => false
    );
}

