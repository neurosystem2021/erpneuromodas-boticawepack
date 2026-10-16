<?php
$app->get('/productos/proveedores-reposicion', function ($request, $response) {
    $warehouse = filter_var($request->getParam('idAlmacen'), FILTER_VALIDATE_INT);
    $ids = $request->getParam('idProductos');
    if (!$warehouse || $warehouse < 1 || !is_array($ids) || !$ids || count($ids) > 100) return $response->withJson(array('error' => 'Selecciona almacén y hasta 100 productos.'), 400);
    foreach ($ids as $id) {
        if (!filter_var($id, FILTER_VALIDATE_INT) || (int)$id < 1) return $response->withJson(array('error' => 'Producto inválido.'), 400);
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT p.IdProducto, proveedor.IdProveedor AS IdProveedorReposicion,
        IFNULL(proveedor.Proveedor, '') AS ProveedorReposicion, IFNULL(proveedor.Ruc, '') AS RucProveedorReposicion,
        (SELECT COUNT(DISTINCT oc.IdOrdenCompra) FROM Lo_OrdenCompra oc
            INNER JOIN Lo_OrdenCompraDet od ON od.IdOrdenCompra = oc.IdOrdenCompra
            WHERE od.IdProducto = p.IdProducto AND oc.IdAlmacen = ? AND oc.Estado = 1
                AND od.Cantidad > 0) AS OrdenesPendientes
        FROM Gen_Producto p
        LEFT JOIN Lo_Movimiento compra ON compra.Hash = (
            SELECT m.Hash FROM Lo_Movimiento m
            INNER JOIN Lo_MovimientoDetalle d ON d.hashMovimiento = m.Hash
            INNER JOIN Lo_MovimientoTipo tipo ON tipo.IdMovimientoTipo = m.IdMovimientoTipo
            WHERE d.IdProducto = p.IdProducto AND m.IdAlmacenDestino = ?
                AND m.Anulado = 0 AND tipo.VaRegCompra = 1 AND m.IdProveedor > 0
                AND m.MovimientoFecha <= NOW()
            ORDER BY m.MovimientoFecha DESC, m.FechaReg DESC, m.Hash DESC LIMIT 1
        )
        LEFT JOIN Lo_Proveedor proveedor ON proveedor.IdProveedor = compra.IdProveedor
        WHERE p.IdProducto IN ($placeholders)";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(array_merge(array((int)$warehouse, (int)$warehouse), array_map('intval', $ids)));
    return $response->withJson($stmt->fetchAll(\PDO::FETCH_ASSOC));
});
