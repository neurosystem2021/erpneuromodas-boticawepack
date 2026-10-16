<?php
return function ($request, $response) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $createdBy=$_SESSION['user'] ?? null;
    if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
    if (!$createdBy) return $response->withJson(array('msg'=>'Inicie sesión nuevamente.'),401);
    $movement = $request->getParam('movimiento');
    $supplier = filter_var(isset($movement['proveedor']['IdProveedor']) ? $movement['proveedor']['IdProveedor'] : null, FILTER_VALIDATE_INT);
    $warehouse = filter_var($request->getParam('IdAlmacen'), FILTER_VALIDATE_INT);
    $products = $request->getParam('productos');
    if (!$supplier || $supplier < 1 || !$warehouse || $warehouse < 1 || !is_array($products) || !$products || count($products) > 5000) {
        return $response->withJson(array('msg' => 'Selecciona proveedor, almacén y productos válidos.'), 400);
    }
    $ids = array();
    foreach ($products as $product) {
        $id = filter_var(isset($product['IdProducto']) ? $product['IdProducto'] : null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1 || isset($ids[$id]) || !isset($product['cantidad'], $product['precio']) || !is_numeric($product['cantidad']) || !is_numeric($product['precio']) || !is_finite((float)$product['cantidad']) || !is_finite((float)$product['precio']) || $product['cantidad'] <= 0 || $product['precio'] <= 0) {
            return $response->withJson(array('msg' => 'Revisa productos repetidos, cantidades y precios.'), 400);
        }
        $ids[$id] = $id;
    }
    $locked = false;
    try {
        // Serialize validation and insertion, including requests from different tabs.
        $lock = $this->db->query("SELECT GET_LOCK('nst_order_create', 10)");
        $locked = (int)$lock->fetchColumn() === 1;
        if (!$locked) return $response->withJson(array('msg' => 'Hay otra orden procesándose. Intenta nuevamente.'), 409);
        $this->db->beginTransaction();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $check = $this->db->prepare("SELECT DISTINCT oc.IdOrdenCompra, CONCAT(oc.Anio, '-', oc.Numero) AS Correlativo, od.IdProducto
            FROM Lo_OrdenCompra oc INNER JOIN Lo_OrdenCompraDet od ON od.IdOrdenCompra = oc.IdOrdenCompra
            WHERE oc.IdAlmacen = ? AND oc.Estado = 1 AND od.Cantidad > 0 AND od.IdProducto IN ($placeholders)");
        $check->execute(array_merge(array($warehouse), array_values($ids)));
        $duplicates = $check->fetchAll(\PDO::FETCH_ASSOC);
        if ($duplicates) {
            $this->db->rollBack();
            $numbers = array_unique(array_column($duplicates, 'Correlativo'));
            return $response->withJson(array('msg' => 'No se generó la orden: hay productos en órdenes pendientes (' . implode(', ', $numbers) . ').', 'duplicados' => $duplicates), 409);
        }
        $year = (int)getNow('Y');
        $sequence = $this->db->prepare('SELECT COALESCE(MAX(Numero), 0) + 1 FROM Lo_OrdenCompra WHERE Anio = ?');
        $sequence->execute(array($year));
        $number = (int)$sequence->fetchColumn();
        $insert = $this->db->prepare('INSERT INTO Lo_OrdenCompra (IdProveedor, FechaOrdenCompra, Anio, Numero, IdAlmacen, Estado, UsuarioReg) VALUES (?, ?, ?, ?, ?, 1, ?)');
        $insert->execute(array($supplier, getNow(), $year, $number, $warehouse, $createdBy));
        $orderId = $this->db->lastInsertId();
        $detail = $this->db->prepare('INSERT INTO Lo_OrdenCompraDet (IdOrdenCompra, IdProducto, Cantidad, Precio) VALUES (?, ?, ?, ?)');
        foreach ($products as $product) $detail->execute(array($orderId, $product['IdProducto'], $product['cantidad'], $product['precio']));
        $this->db->commit();
        return $response->withJson(array('insertId' => $orderId));
    } catch (\Throwable $error) {
        if ($this->db->inTransaction()) $this->db->rollBack();
        return $response->withJson(array('msg' => 'No se pudo guardar la orden. Intenta nuevamente.'), 500);
    } finally {
        if ($locked) $this->db->query("SELECT RELEASE_LOCK('nst_order_create')");
    }
};
