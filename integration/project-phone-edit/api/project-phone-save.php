<?php
return function ($request, $response) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['user'])) return $response->withJson(['error'=>'Inicie sesión nuevamente.'],401);
    $id=(int)$request->getParam('IdProducto');
    $phone=trim((string)$request->getParam('Telefono'));
    if (!$id || strlen($phone)>100 || !preg_match('/^[0-9+() .\/-]{3,100}$/',$phone) || strlen(preg_replace('/\D/','',$phone))<3) return $response->withJson(['error'=>'Ingrese un teléfono válido.'],422);
    $db=$this->db;
    $stmt=$db->prepare('SELECT c.IdCliente FROM Gen_Producto p INNER JOIN Ve_DocVentaCliente c ON c.IdCliente=p.IdClienteReserva WHERE p.IdProducto=? AND p.EsHabitacion=1 AND IFNULL(p.Anulado,0)=0');
    $stmt->execute([$id]);$client=$stmt->fetchColumn();
    if (!$client) return $response->withJson(['error'=>'No se encontró el cliente del proyecto.'],404);
    $stmt=$db->prepare('UPDATE Ve_DocVentaCliente SET Telefono=? WHERE IdCliente=?');
    $stmt->execute([$phone,$client]);
    return $response->withJson(['IdCliente'=>(int)$client,'Telefono'=>$phone]);
};
