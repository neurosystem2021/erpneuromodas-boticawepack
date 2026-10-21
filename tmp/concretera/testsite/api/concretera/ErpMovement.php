<?php
// Shared persistence for purchase receipts, stock exits and warehouse transfers.
// Calculated stock views are updated by the document itself, never by DML.
function imcMovementRun($db, $sql, array $params = []) {
    $stmt = $db->prepare($sql);
    foreach(array_values($params) as $i=>$v)$stmt->bindValue($i+1,$v,is_int($v)||is_bool($v)?PDO::PARAM_INT:(is_null($v)?PDO::PARAM_NULL:PDO::PARAM_STR));
    if (!$stmt->execute()) throw new RuntimeException('No se pudo completar una operación de inventario.');
    return $stmt;
}
function imcMovementDate($value, $label) {
    $text = str_replace('T', ' ', trim((string) $value));
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/D', $text)) $text .= ' 00:00:00';
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/D', $text)) $text .= ':00';
    $date = DateTime::createFromFormat('!Y-m-d H:i:s', $text);
    $errors = DateTime::getLastErrors();
    if (!$date || (is_array($errors) && ($errors['warning_count'] || $errors['error_count']))) throw new InvalidArgumentException('Revise '.$label.': la fecha no es válida.');
    return $date->format('Y-m-d H:i:s');
}
function imcMovementNumber($value, $label, $positive = false) {
    if (!is_numeric($value) || !is_finite((float) $value) || ($positive ? (float) $value <= 0 : (float) $value < 0)) throw new InvalidArgumentException('Revise '.$label.': debe ser '.($positive ? 'mayor a cero.' : 'un número igual o mayor a cero.'));
    return (float) $value;
}
function imcSaveMovement($db, $request, $response, $transferOnly = false) {
    $hash = null; $headerWritten = false; $orderUpdated = false; $locked = false; $lockName = '';
    $models = []; $stockBefore = []; $stockApplied = []; $pricesBefore = []; $pricesApplied = []; $orderId = 0; $stage = 'validar el movimiento';
    try {
        $m = (array) $request->getParam('movimiento');
        $items = $request->getParam('productos');
        $typeId = (int) ($m['movimientoTipo']['IdMovimientoTipo'] ?? 0);
        $type = imcMovementRun($db, 'SELECT *,COALESCE(Anulado+0,0) AS Anulado FROM Lo_MovimientoTipo WHERE IdMovimientoTipo=?', [$typeId])->fetch(PDO::FETCH_ASSOC);
        if (!$type || !in_array((int) $type['Tipo'], [0,1,2], true) || (isset($type['Anulado']) && (int) $type['Anulado'] === 1)) throw new InvalidArgumentException('Seleccione un tipo de movimiento disponible.');
        $kind = (int) $type['Tipo'];
        if ($transferOnly && $kind !== 2) throw new InvalidArgumentException('El tipo seleccionado no corresponde a un traslado.');
        $origin = $kind === 0 ? -1 : (int) ($m['almacenOrigen']['IdAlmacen'] ?? 0);
        $destination = $kind === 1 ? -1 : (int) ($m['almacenDestino']['IdAlmacen'] ?? 0);
        foreach (['origen'=>$origin, 'destino'=>$destination] as $label=>$warehouse) {
            if ($warehouse === -1) continue;
            $row = imcMovementRun($db, 'SELECT IdAlmacen,Almacen,COALESCE(Anulado+0,0) AS Anulado FROM Lo_Almacen WHERE IdAlmacen=?', [$warehouse])->fetch(PDO::FETCH_ASSOC);
            if (!$row || (int) $row['Anulado'] === 1) throw new InvalidArgumentException('Seleccione un almacén de '.$label.' disponible.');
        }
        if (($kind !== 0 && $origin <= 0) || ($kind !== 1 && $destination <= 0)) throw new InvalidArgumentException('Seleccione los almacenes del movimiento.');
        if ($kind === 2 && $origin === $destination) throw new InvalidArgumentException('El almacén destino debe ser diferente al origen.');
        $supplier = (int) ($m['proveedor']['IdProveedor'] ?? 0);
        if ($kind !== 2 && $supplier <= 0) throw new InvalidArgumentException('Seleccione el proveedor antes de guardar.');
        if ($supplier > 0 && !imcMovementRun($db, 'SELECT IdProveedor FROM Lo_Proveedor WHERE IdProveedor=?', [$supplier])->fetchColumn()) throw new InvalidArgumentException('El proveedor seleccionado no existe. Vuelva a seleccionarlo.');
        $series = trim((string) ($m['Serie'] ?? ''));
        $numberText = trim((string) ($m['Numero'] ?? ''));
        if ($series === '' || mb_strlen($series) > 255 || !preg_match('/^\d+$/D', $numberText) || (float) $numberText < 1 || (float) $numberText > 2147483647) throw new InvalidArgumentException('Ingrese una serie y un número de documento válidos.');
        $number = (int) $numberText;
        $date = imcMovementDate($m['MovimientoFecha'] ?? '', 'la fecha contable');
        $stockDate = imcMovementDate($m['FechaStock'] ?? getNow(), 'la fecha de stock');
        $rate = imcMovementNumber($m['TipoCambio'] ?? 1, 'el tipo de cambio', true);
        $currency = trim((string) ($m['Moneda'] ?? ''));
        if ($currency === '') throw new InvalidArgumentException('Seleccione la moneda del movimiento.');
        $credit = !empty($m['EsCredito']) ? 1 : 0;
        $due = $credit ? imcMovementDate($m['FechaVenCredito'] ?? '', 'el vencimiento del crédito') : null;
        $period = (string) ($m['FechaPeriodoTributario'] ?? substr(str_replace('-', '', $date), 0, 6));
        if (!preg_match('/^\d{4}(?:0[1-9]|1[0-2])$/D', $period)) throw new InvalidArgumentException('Revise el período tributario del movimiento.');
        if (!is_array($items) || !$items) throw new InvalidArgumentException('Agregue al menos un producto antes de guardar.');
        $details = []; $quantities = []; $keys = []; $products = [];
        foreach ($items as $index=>$item) {
            if (!is_array($item)) throw new InvalidArgumentException('Revise los datos del producto '.($index+1).'.');
            $id = (int) ($item['IdProducto'] ?? 0);
            $product = imcMovementRun($db, 'SELECT IdProducto,Producto,PrecioCosto,PrecioContado,COALESCE(Anulado+0,0) AS Anulado FROM Gen_Producto WHERE IdProducto=?', [$id])->fetch(PDO::FETCH_ASSOC);
            if (!$product || (int) $product['Anulado'] === 1) throw new InvalidArgumentException('El producto '.($index+1).' no está disponible. Vuelva a seleccionarlo.');
            $quantity = round(imcMovementNumber($item['cantidad'] ?? null, 'la cantidad del producto '.($index+1), true), 4);
            if ($quantity <= 0) throw new InvalidArgumentException('La cantidad del producto '.($index+1).' es demasiado pequeña.');
            $price = round(imcMovementNumber($item['precio'] ?? $item['PrecioCosto'] ?? 0, 'el precio del producto '.($index+1)) * $rate, 4);
            $key = $id.'|'.sprintf('%.4F', $price);
            if (isset($keys[$key])) throw new InvalidArgumentException('El producto '.($index+1).' está repetido con el mismo precio. Unifique sus cantidades en una sola línea.');
            $keys[$key] = true;
            $description = $item['Descripcion'] ?? '';
            if (!is_string($description)) throw new InvalidArgumentException('Revise la descripción del producto '.($index+1).'.');
            $salePrice = isset($item['nuevoPrecioContado']) ? imcMovementNumber($item['nuevoPrecioContado'], 'el nuevo precio del producto '.($index+1)) * $rate : (float) $product['PrecioContado'];
            $details[] = ['id'=>$id,'quantity'=>$quantity,'price'=>$price,'igv'=>(int) ($item['TieneIgv'] ?? 0),'lot'=>(int) ($item['IdLote'] ?? 0),'description'=>$description,'salePrice'=>$salePrice];
            $quantities[$id] = ($quantities[$id] ?? 0) + $quantity;
            $products[$id] = $product;
        }
        $metadata = $db->query("SELECT TABLE_NAME,TABLE_TYPE,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('Lo_Movimiento','Lo_MovimientoDetalle','Gen_Producto_Totales','Gen_Producto','Lo_OrdenCompra','Lo_OrdenCompraMovimiento')")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($metadata as $row) $models[strtolower($row['TABLE_NAME'])] = $row;
        if (!isset($models['gen_producto_totales'])) throw new InvalidArgumentException('No está configurado el stock por almacén. Revise la configuración de inventario.');
        $calculatedStock = $models['gen_producto_totales']['TABLE_TYPE'] === 'VIEW';
        $orderId = (int) ($m['IdOrdenCompra'] ?? 0);
        if ($orderId > 0) {
            if ($kind !== 0) throw new InvalidArgumentException('Una orden de compra solo se puede cargar como ingreso.');
            $db->exec("CREATE TABLE IF NOT EXISTS Lo_OrdenCompraMovimiento (IdOrdenCompra INT NOT NULL,HashMovimiento VARCHAR(80) NOT NULL,FechaReg DATETIME NOT NULL,PRIMARY KEY(IdOrdenCompra),UNIQUE KEY UX_OrdenCompraMovimiento_Hash(HashMovimiento)) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        }
        $scope = (string) $db->query('SELECT DATABASE()')->fetchColumn();
        $lockName = 'imc_mov_'.substr(hash('sha256', $scope), 0, 40);
        if ((int) imcMovementRun($db, 'SELECT GET_LOCK(?,5)', [$lockName])->fetchColumn() !== 1) throw new InvalidArgumentException('Se está guardando otro movimiento. Espere unos segundos y vuelva a intentarlo.');
        $locked = true;
        $duplicate = imcMovementRun($db, 'SELECT Hash FROM Lo_Movimiento WHERE IdMovimientoTipo=? AND IdProveedor=? AND Serie=? AND Numero=? LIMIT 1', [$typeId,$supplier,$series,$number])->fetchColumn();
        if ($duplicate !== false) {
            $count = (int) imcMovementRun($db, 'SELECT COUNT(*) FROM Lo_MovimientoDetalle WHERE hashMovimiento=?', [$duplicate])->fetchColumn();
            return $response->withJson(['error'=>'Ya existe el documento '.$series.'-'.$number.($count ? '. Revíselo antes de volver a registrarlo.' : ', pero quedó sin detalle. Revise o anule ese registro incompleto antes de reintentarlo.'),'code'=>'duplicate_movement'],409);
        }
        if ($orderId > 0 && !imcMovementRun($db, 'SELECT IdOrdenCompra FROM Lo_OrdenCompra WHERE IdOrdenCompra=? AND IdProveedor=? AND Estado=0', [$orderId,$supplier])->fetchColumn()) throw new InvalidArgumentException('La orden de compra ya fue atendida o no corresponde al proveedor seleccionado.');
        if ($kind === 2) foreach ($quantities as $id=>$quantity) {
            $balance = imcMovementRun($db, 'SELECT COALESCE(TotalCompras,0)-COALESCE(TotalVentas,0) FROM Gen_Producto_Totales WHERE IdProducto=? AND IdAlmacen=?', [$id,$origin])->fetchColumn();
            $available = $balance === false ? 0 : (float) $balance;
            if ($quantity > $available + 0.00009) throw new InvalidArgumentException('Stock insuficiente para el producto código '.$id.' en el almacén origen. Disponible: '.number_format($available,4,'.','').'.');
        }
        if (!$calculatedStock) foreach ($quantities as $id=>$quantity) foreach (array_unique(array_filter([$origin,$destination],fn($v)=>$v>0)) as $warehouse) {
            $rows = imcMovementRun($db, 'SELECT * FROM Gen_Producto_Totales WHERE IdProducto=? AND IdAlmacen=?', [$id,$warehouse])->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows)>1) throw new InvalidArgumentException('Hay stock duplicado para el producto código '.$id.' en el almacén '.$warehouse.'. Revise ese registro antes de guardar.');
            $stockBefore[$id.'|'.$warehouse] = ['id'=>$id,'warehouse'=>$warehouse,'row'=>$rows ? $rows[0] : null];
        }
        $alterPrices = $kind !== 2 && !empty($m['alterarProductos']);
        if ($alterPrices) $pricesBefore = $products;
        $db->beginTransaction();
        $hash = (string) time().str_pad((string) random_int(0,999999),6,'0',STR_PAD_LEFT);
        $stage = 'guardar la cabecera del documento';
        imcMovementRun($db, 'INSERT INTO Lo_Movimiento (IdMovimientoTipo,IdProveedor,Serie,Numero,MovimientoFecha,IdAlmacenOrigen,IdAlmacenDestino,Anulado,FechaReg,UsuarioReg,Hash,FechaStock,Percepcion,EsCredito,FechaVenCredito,FechaPeriodoTributario,TipoCambio,Moneda) VALUES (?,?,?,?,?,?,?,0,?,?,?,?,0,?,?,?,?,?)', [$typeId,$supplier,$series,$number,$date,$origin,$destination,getNow(),(string) ($m['user'] ?? $_SESSION['user'] ?? 'sistema'),$hash,$stockDate,$credit,$due,(int) $period,$rate,$currency]);
        $headerWritten = true;
        foreach ($details as $index=>$detail) {
            $stage = 'guardar el detalle del producto '.($index+1);
            imcMovementRun($db, 'INSERT INTO Lo_MovimientoDetalle (hashMovimiento,IdProducto,Cantidad,TieneIgv,Precio,IdLote,Descripcion) VALUES (?,?,?,?,?,?,?)', [$hash,$detail['id'],$detail['quantity'],$detail['igv'],$detail['price'],$detail['lot'],$detail['description']]);
        }
        if (!$calculatedStock) foreach ($quantities as $id=>$quantity) foreach ([$origin=>-$quantity,$destination=>$quantity] as $warehouse=>$delta) {
            if ($warehouse<=0) continue;
            $stage = 'actualizar el stock del almacén '.$warehouse;
            $before = $stockBefore[$id.'|'.$warehouse]['row'];
            if ($before) imcMovementRun($db, 'UPDATE Gen_Producto_Totales SET TotalCompras=COALESCE(TotalCompras,0)+? WHERE IdProducto=? AND IdAlmacen=?', [$delta,$id,$warehouse]);
            else imcMovementRun($db, 'INSERT INTO Gen_Producto_Totales (IdAlmacen,IdProducto,TotalCompras,TotalVentas) VALUES (?,?,?,0)', [$warehouse,$id,$delta]);
            $stockApplied[$id.'|'.$warehouse] = ['id'=>$id,'warehouse'=>$warehouse,'delta'=>$delta,'existed'=>(bool) $before];
        }
        if ($alterPrices) foreach ($details as $detail) {
            $stage = 'actualizar los precios del producto código '.$detail['id'];
            imcMovementRun($db, 'UPDATE Gen_Producto SET PrecioCosto=?,PrecioContado=? WHERE IdProducto=?', [$detail['price'],$detail['salePrice'],$detail['id']]);
            $pricesApplied[$detail['id']] = ['cost'=>$detail['price'],'sale'=>$detail['salePrice']];
        }
        if ($orderId>0) {
            $stage = 'vincular la orden de compra';
            imcMovementRun($db, 'INSERT INTO Lo_OrdenCompraMovimiento (IdOrdenCompra,HashMovimiento,FechaReg) VALUES (?,?,?)', [$orderId,$hash,getNow()]);
            $updated = imcMovementRun($db, 'UPDATE Lo_OrdenCompra SET Estado=2 WHERE IdOrdenCompra=? AND Estado=0', [$orderId]);
            if ($updated->rowCount()!==1) throw new RuntimeException('La orden de compra cambió durante el registro.');
        }
        $stage = 'confirmar el movimiento';
        $db->commit();
        $data = ['hash'=>$hash,'ok'=>true];
        if ($orderId>0) {$data['IdOrdenCompra']=$orderId;$data['ordenEstado']=2;}
        return $response->withJson($data);
    } catch (Throwable $error) {
        if ($db->inTransaction()) $db->rollBack();
        $clean = true;
        // Rollback covers InnoDB. Compensate only this request in legacy MyISAM.
        if ($headerWritten) try {
            foreach ($stockApplied as $entry) if (strtoupper($models['gen_producto_totales']['ENGINE'] ?? '') !== 'INNODB') {
                imcMovementRun($db, 'UPDATE Gen_Producto_Totales SET TotalCompras=COALESCE(TotalCompras,0)-? WHERE IdProducto=? AND IdAlmacen=?', [$entry['delta'],$entry['id'],$entry['warehouse']]);
                if (!$entry['existed']) imcMovementRun($db, 'DELETE FROM Gen_Producto_Totales WHERE IdProducto=? AND IdAlmacen=? AND TotalCompras=0 AND COALESCE(TotalVentas,0)=0', [$entry['id'],$entry['warehouse']]);
            }
            if (strtoupper($models['gen_producto']['ENGINE'] ?? '') !== 'INNODB') foreach ($pricesApplied as $id=>$values) {
                $p = $pricesBefore[$id];
                imcMovementRun($db, 'UPDATE Gen_Producto SET PrecioCosto=?,PrecioContado=? WHERE IdProducto=? AND PrecioCosto=? AND PrecioContado=?', [$p['PrecioCosto'],$p['PrecioContado'],$id,$values['cost'],$values['sale']]);
            }
            if ($orderId>0) {
                imcMovementRun($db, 'DELETE FROM Lo_OrdenCompraMovimiento WHERE HashMovimiento=?', [$hash]);
                if ($orderUpdated && strtoupper($models['lo_ordencompra']['ENGINE'] ?? '') !== 'INNODB') imcMovementRun($db, 'UPDATE Lo_OrdenCompra SET Estado=0 WHERE IdOrdenCompra=? AND Estado=2', [$orderId]);
            }
            imcMovementRun($db, 'DELETE FROM Lo_MovimientoDetalle WHERE hashMovimiento=?', [$hash]);
            imcMovementRun($db, 'DELETE FROM Lo_Movimiento WHERE Hash=?', [$hash]);
        } catch (Throwable $cleanupError) {$clean=false;error_log('IMCEDEN inventory cleanup '.$hash.': '.$cleanupError->getMessage());}
        if ($error instanceof InvalidArgumentException) return $response->withJson(['error'=>$error->getMessage(),'code'=>'movement_validation'],422);
        $reference = substr(hash('sha256',uniqid('',true)),0,10);
        error_log('IMCEDEN inventory '.$reference.' '.$stage.': '.$error->getMessage());
        $reason = 'No se pudo '.$stage.'.';
        if ($error instanceof PDOException) {
            $number = (int) ($error->errorInfo[1] ?? 0);
            if ($number===1062) $reason .= ' Ya existe un registro con esos datos; revise serie, número y productos repetidos.';
            elseif (in_array($number,[1364,1048],true) && preg_match("/(?:Field|Column) '([A-Za-z0-9_]+)'/",$error->getMessage(),$match)) $reason .= ' Falta el dato requerido '.$match[1].'.';
            elseif ($number===1406) $reason .= ' Uno de los textos supera el tamaño permitido; reduzca la descripción o serie.';
            elseif (in_array($number,[1054,1146],true)) $reason .= ' La configuración de inventario está incompleta; requiere revisión técnica.';
            elseif (in_array($number,[1205,1213],true)) $reason .= ' Otro proceso está usando el inventario. Espere unos segundos y reintente.';
        }
        $reason .= $clean ? ' El registro no se completó. Sus datos siguen en pantalla para corregirlos y reintentar.' : ' No reintente todavía: el registro requiere revisión técnica antes de continuar.';
        return $response->withJson(['error'=>$reason,'code'=>'movement_save_failed','reference'=>$reference],500);
    } finally {
        if ($locked) try {imcMovementRun($db, 'SELECT RELEASE_LOCK(?)', [$lockName]);} catch (Throwable $ignored) {}
    }
}

if(!function_exists('getNow')){function getNow(){return cxNow();}}
