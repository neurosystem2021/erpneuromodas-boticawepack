<?php
// quitar esto cuando este mejorada la validacion
// include_once('../views/validateUser.php');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require 'sunat/sunat.php';

// EXCEL
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$config['debug'] = true;
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host']   = "127.0.0.1";
$config['db']['user']   = "root";
// $config['db']['user']   = "neurosys_CUSTODIO";
$config['db']['pass']   = "";
// $config['db']['pass']   = "IX!!q!t(&Fc^";
$config['db']['dbname'] = "nst";
// $config['db']['dbname'] = "neurosys_CUSTODIO";

$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();

$container['db'] = function ($c) {
    $db = $c['settings']['db'];

    $dsn = 'mysql:host=' .  $db['host'] . ';dbname=' . $db['dbname'] . ';charset=utf8';
    $usr = $db['user'];
    $pwd = $db['pass'];

    $pdo = new \Slim\PDO\Database($dsn, $usr, $pwd);

    return $pdo;
};



$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});



function getNow($format = "Y-m-d H:i:s")
{
    return date_create('now', timezone_open('America/Lima'))->format($format);
}
/*
$app->get('/rest/categorias', FUNCTION (Request $request, Response $response, array $args) {
    $select = "SELECT Gen_ProductoCategoria.IdProductoCategoria, Gen_ProductoCategoria.ProductoCategoria,Gen_ProductoCategoria.IdImpresora
    FROM Gen_ProductoCategoria WHERE Anulado <> 1 ORDER BY Orden ASC";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});

$app->get('/rest/productos/categoria', FUNCTION (Request $request, Response $response, array $args)  USE ($app) {
    
    if($request->getParam('IdProductoCategoria')){
        $idProductoCategoria = $request->getParam('IdProductoCategoria');
        $select = "SELECT Gen_Producto.IdProducto,Gen_Producto.Imagen,Gen_Producto.PrecioContado,Gen_Producto.Producto,Gen_ProductoCategoria.IdImpresora
        FROM Gen_Producto
        INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
        WHERE Gen_Producto.PrecioContado > 0 AND Gen_ProductoCategoria.IdProductoCategoria = $idProductoCategoria";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

    }else if($request->getParam('q')){
        $select = "SELECT Gen_Producto.IdProducto,Gen_Producto.Imagen,Gen_Producto.PrecioContado,Gen_Producto.Producto,Gen_ProductoCategoria.IdImpresora FROM Gen_Producto
        INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
        WHERE Gen_Producto.PrecioContado > 0 AND Gen_Producto.Producto LIKE '%" . $request->getParam('q'). "%'";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
    }  

    return $response->withJson($data);
 
});

$app->get('/rest/preorden/all', function (Request $request, Response $response) {
    $select = "SELECT Ve_PreOrden.IdPreOrden, Ve_PreOrden.IdCliente, Ve_PreOrden.Mesa,Ve_PreOrden.Estado
    FROM Ve_PreOrden WHERE Ve_PreOrden.Estado = 0 ORDER BY Ve_PreOrden.IdPreOrden DESC";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/rest/preorden/{id}', function (Request $request, Response $response, array $args) {
    $select = "SELECT Mesa, UsuarioReg, IdCliente, Estado  FROM Ve_PreOrden WHERE  Ve_PreOrden.IdPreOrden = " . $args['id'];
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/rest/preordendet/{id}', function (Request $request, Response $response, array $args) {
    $select = "SELECT Ve_PreOrden.Mesa, Ve_PreOrden.UsuarioReg, Ve_PreOrden.IdCliente, Ve_PreOrden.Estado,
    Ve_DocVentaCliente.Cliente, Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.Direccion  FROM Ve_PreOrden
    LEFT JOIN Ve_DocVentaCliente ON Ve_PreOrden.IdCliente = Ve_DocVentaCliente.IdCliente WHERE Ve_PreOrden.IdPreOrden = " . $args['id'];

    $stmt = $this->db->query($select);
    $stmt->execute();
    $preorden = $stmt->fetch();

    $select = "SELECT Ve_PreOrdenDet.IdPreOrdenDet, Ve_PreOrdenDet.IdPreOrden, Ve_PreOrdenDet.IdProducto,
    Ve_PreOrdenDet.Cantidad, Ve_PreOrdenDet.Precio, Ve_PreOrdenDet.Indicacion, Ve_PreOrdenDet.Enviado,Gen_Producto.Imagen,Gen_Producto.Producto,Gen_ProductoCategoria.IdImpresora FROM Ve_PreOrdenDet 
    INNER JOIN Gen_Producto ON Ve_PreOrdenDet.IdProducto = Gen_Producto.IdProducto 
    INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
    WHERE Ve_PreOrdenDet.IdPreOrden = ". $args['id'];

    $stmt = $this->db->query($select);
    $stmt->execute();
    $preordenDet = $stmt->fetchAll();
    $preorden['VePreOrdenDet'] = $preordenDet;

    return $response->withJson($preorden);
});

$app->post('/rest/preorden', function (Request $request, Response $response) {

    $mesa = $request->getParam('Mesa');
    $usuario = $request->getParam('Usuario');
    $insert = $this->db->insert(array('IdCliente', 'FechaReg', 'UsuarioReg','FechaMod','UsuarioMod','Estado','Mesa'))
                        ->into('Ve_PreOrden')
                        ->values(array(null, getNow(),$usuario,getNow(),'admin',0, $mesa));
    $insertId = $insert->execute();

    return $response->withJson(array("IdPreOrden" => $insertId));
  
});

$app->post('/rest/preordendet', function (Request $request, Response $response) {
    $cantidad = $request->getParam('Cantidad');
    $idProducto = $request->getParam('IdProducto');
    $idPreOrden = $request->getParam('IdPreOrden');
    $precio = $request->getParam('Precio');
    $indicacion = $request->getParam('Indicacion');
    $enviado = $request->getParam('Enviado');
    $insert = $this->db->insert(array('IdPreOrden','IdProducto','Cantidad','Precio','Indicacion','Enviado'))
                        ->into('Ve_PreOrdenDet')
                        ->values(array($idPreOrden, $idProducto,$cantidad,$precio,$indicacion, $enviado));
    $insertId = $insert->execute();
    return $response->withJson(array("success" => true,"insertId"=>$insertId));
  
});

$app->put('/rest/add/preordendet', function (Request $request, Response $response) {
    $idPreOrdenDet = $request->getParam('IdPreOrdenDet');

    $update = "UPDATE Ve_PreOrdenDet SET Cantidad = (Cantidad + 1) WHERE IdPreOrdenDet = $idPreOrdenDet";
    $stmt1 = $this->db->prepare($update);
    $affectedRows = $stmt1->execute();

    if($affectedRows){
        $select ="SELECT Cantidad FROM Ve_PreOrdenDet WHERE IdPreOrdenDet = $idPreOrdenDet ";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetch(); 
    }
    return $response->withJson($data);
  
});

$app->put('/rest/rm/preordendet', function (Request $request, Response $response) {
    $idPreOrdenDet = $request->getParam('IdPreOrdenDet');

    $update = "UPDATE Ve_PreOrdenDet SET Cantidad = (Cantidad - 1) WHERE IdPreOrdenDet = $idPreOrdenDet";
    $stmt1 = $this->db->prepare($update);
    $affectedRows = $stmt1->execute();

    if($affectedRows){
        $select ="SELECT Cantidad FROM Ve_PreOrdenDet WHERE IdPreOrdenDet = $idPreOrdenDet ";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetch(); 
    }
    return $response->withJson($data);
  
});

$app->delete('/rest/preordendet/delete/{id}', function (Request $request, Response $response, array $args) {

    $sql = "DELETE FROM Ve_PreOrdenDet WHERE IdPreOrdenDet = ". $args['id'];
    $stmt = $this->db->prepare($sql);
    $deleted = $stmt->execute();

    return $response->withJson($deleted);
});

$app->get('/rest/impresoras', function (Request $request, Response $response, array $args) {
    $select = "SELECT IdImpresora,NombreImpresora,Ip,Puerto,EsCocina
    FROM Gen_Impresora";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->put('/rest/preorden/{id}', function (Request $request, Response $response, array $args) {
    $idCliente = $request->getParam('IdCliente');
    $control = 0;
    $update = "UPDATE Ve_PreOrden SET IdCliente = $idCliente WHERE IdPreOrden = ". $args['id'];
    $stmt1 = $this->db->prepare($update);
    $affectedRows = $stmt1->execute();

    if($affectedRows){
        $control++;
    }

    $update = "UPDATE Ve_PreOrdenDet SET Enviado = 1 WHERE IdPreOrden = ". $args['id'];
    $stmt1 = $this->db->prepare($update);
    $affectedRows2 = $stmt1->execute();
    if($affectedRows2){
        $control++;
    }
    return $response->withJson($control);

});

$app->put('/rest/preordendet/{id}', function (Request $request, Response $response, array $args) {
    $control = 0;
    $update = "UPDATE Ve_PreOrdenDet SET Enviado = 1 WHERE IdPreOrden = ". $args['id'];
    $stmt1 = $this->db->prepare($update);
    $affectedRows2 = $stmt1->execute();
    if($affectedRows2){
        $control++;
    }
    return $response->withJson($control);

});

$app->get('/rest/cliente', function (Request $request, Response $response, array $args) {
    $dni = $request->getParam('dni');
    $select = "SELECT * FROM Ve_DocVentaCliente WHERE DniRuc LIKE '%" . $dni. "%'";
    $select .= " ORDER BY DniRuc ASC LIMIT 15";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->put('/rest/cambiarmesa/{id}', function (Request $request, Response $response, array $args) {
    $mesa = $request->getParam('Mesa');
    $control = 0;
    $update = "UPDATE Ve_PreOrden SET Mesa = '$mesa' WHERE IdPreOrden = ". $args['id'];
    $stmt1 = $this->db->prepare($update);
    $affectedRows2 = $stmt1->execute();
    if($affectedRows2){
        $control++;
    }
    return $response->withJson($control);
});

$app->delete('/rest/preordendelete/{id}', function (Request $request, Response $response, array $args) {
    $select ="SELECT IdCliente FROM Ve_PreOrden WHERE IdPreOrden = ".$args['id'];
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(); 
    $success=false;
    if($data['IdCliente']==null){
        $sql1 = "DELETE FROM Ve_PreOrdenDet WHERE IdPreOrden = ".$args['id'];
        $stmt1 = $this->db->prepare($sql1);
        $deleted = $stmt1->execute();

        $sql2 = "DELETE FROM Ve_PreOrden WHERE IdPreOrden = ".$args['id'];
        $stmt2 = $this->db->prepare($sql2);
        $success = $stmt2->execute();
        
    }else{
        $success=false;
    }   

    return $response->withJson($success);
});

$app->post('/rest/clientes', function (Request $request, Response $response) {
    $cliente = $request->getParam('nombre');
    $dniRuc = $request->getParam('ruc');
    $direccion = $request->getParam('direccion');


    $select = "SELECT * FROM Ve_DocVentaCliente WHERE DniRuc=$dniRuc";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    if (!empty($data)) {
        $data = array(
            'success'=>false
        );
        return $response->withJson($data);
    }

    $insert = $this->db->insert(array('Cliente', 'DniRuc', 'Direccion','Anulado', 'FechaReg'))
                        ->into('Ve_DocVentaCliente')
                        ->values(array($cliente, $dniRuc, $direccion, '0', getNow()));

    $insertId = $insert->execute();

    return $response->withJson(array(
        'success'=>true,
        'idCliente'=> $insertId
    ));
});*/

$app->get('/delivery/categorias', function (Request $request, Response $response, array $args) {
    $select = "SELECT Gen_ProductoCategoria.IdProductoCategoria, Gen_ProductoCategoria.ProductoCategoria,Gen_ProductoCategoria.IdImpresora,Gen_ProductoCategoria.EsOferta
    FROM Gen_ProductoCategoria WHERE Anulado <> 1 AND MostrarDelivery=1 ORDER BY EsOferta Desc, Orden   ASC";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});

$app->get('/delivery/productos/categoria', function (Request $request, Response $response, array $args)  use ($app) {

    if ($request->getParam('IdProductoCategoria')) {
        $idProductoCategoria = $request->getParam('IdProductoCategoria');
        $select = "SELECT Gen_Producto.IdProducto,Gen_Producto.Imagen,IF(Gen_Producto.PorcentajeLiquidacion>0,ROUND(Gen_Producto.PrecioContado - (Gen_Producto.PorcentajeLiquidacion*Gen_Producto.PrecioContado/100)),Gen_Producto.PrecioContado) AS PrecioContado, IF(Gen_Producto.PorcentajeLiquidacion>0,ROUND(Gen_Producto.PrecioContado - (Gen_Producto.PorcentajeLiquidacion*Gen_Producto.PrecioContado/100)),Gen_Producto.PrecioContado) AS PrecioFinal, Gen_Producto.PorcentajeLiquidacion, Gen_Producto.PrecioContado AS PrecioAnterior, Gen_Producto.Producto,Gen_Producto.ProductoDesc,Gen_Producto.IdOfertaCategoria, Gen_Producto.MostrarApp, Gen_Producto.PosOferta, Gen_Producto.ProductoDesc
        FROM Gen_Producto
        INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
        WHERE Gen_Producto.PrecioContado > 0 AND  Gen_Producto.MostrarApp = 1 AND Gen_ProductoCategoria.IdProductoCategoria = $idProductoCategoria 
        HAVING PrecioFinal > 0
        ORDER BY Gen_Producto.PosOferta ASC, Gen_Producto.Producto ASC";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    } else {
        return $response->withJson([]);
    }
});

$app->get('/delivery/productos/busqueda', function (Request $request, Response $response, array $args)  use ($app) {
    $busqueda = $request->getParam('Busqueda');
    $select = "SELECT Gen_Producto.IdProducto,Gen_Producto.Imagen,IF(Gen_Producto.PorcentajeLiquidacion>0,ROUND(Gen_Producto.PrecioContado - (Gen_Producto.PorcentajeLiquidacion*Gen_Producto.PrecioContado/100)),Gen_Producto.PrecioContado) AS PrecioContado, IF(Gen_Producto.PorcentajeLiquidacion>0,ROUND(Gen_Producto.PrecioContado - (Gen_Producto.PorcentajeLiquidacion*Gen_Producto.PrecioContado/100)),Gen_Producto.PrecioContado) AS PrecioFinal, Gen_Producto.PorcentajeLiquidacion, Gen_Producto.PrecioContado AS PrecioAnterior,Gen_Producto.Producto,Gen_Producto.IdOfertaCategoria, Gen_Producto.ProductoDesc
    FROM Gen_Producto
    INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
    WHERE Gen_Producto.PrecioContado > 0 AND Gen_ProductoCategoria.MostrarDelivery=1 AND Gen_ProductoCategoria.Anulado <> 1 AND Gen_Producto.Anulado <> 1 AND Gen_Producto.Producto LIKE '%" . $busqueda . "%' 
    HAVING PrecioFinal > 0 ";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});

$app->get('/delivery/productos/categoria/oferta', function (Request $request, Response $response, array $args)  use ($app) {
    if ($request->getParam('IdProductoCategoria')) {
        $idProductoCategoria = $request->getParam('IdProductoCategoria');
        $select = "SELECT Gen_Producto.IdProducto,Gen_Producto.Imagen,COALESCE(Gen_Producto.PrecioApp,Gen_Producto.PrecioContado) PrecioContado,Gen_Producto.Producto,Gen_Producto.ProductoDesc,Gen_Producto.IdOfertaCategoria,Gen_Producto.PosOferta,Gen_Producto.FechaOfertaInicio,Gen_Producto.FechaOfertaFin
        FROM Gen_Producto
        INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdOfertaCategoria = Gen_ProductoCategoria.IdProductoCategoria
        WHERE Gen_Producto.PrecioContado > 0
          AND COALESCE(Gen_Producto.Anulado,0)=0
          AND Gen_Producto.MostrarApp=1
          AND Gen_Producto.PosOferta>0
          AND Gen_Producto.PosOferta<10000
          AND (Gen_Producto.FechaOfertaInicio IS NULL OR Gen_Producto.FechaOfertaInicio<=CURDATE())
          AND (Gen_Producto.FechaOfertaFin IS NULL OR Gen_Producto.FechaOfertaFin>=CURDATE())
          AND Gen_ProductoCategoria.IdProductoCategoria = $idProductoCategoria
        ORDER BY Gen_Producto.PosOferta ASC, Gen_Producto.Producto ASC";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    } else {
        return $response->withJson([]);
    }
});

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});

$app->run();
