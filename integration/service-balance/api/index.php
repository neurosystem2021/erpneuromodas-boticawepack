<?php
// quitar esto cuando este mejorada la validacion
// include_once('../views/validateUser.php');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

error_reporting(0);
require 'vendor/autoload.php';
require 'sunat/sunat.php';

// EXCEL
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

$config['debug'] = true;
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host']   = "127.0.0.1";
$config['db']['user']   = "root";
// $config['db']['user']   = "neurosys_NEUROSOFT";
$config['db']['pass']   = "";
// $config['db']['pass']   = "IX!!q!t(&Fc^";
$config['db']['dbname'] = "nst";
// $config['db']['dbname'] = "neurosys_NEUROSOFT";
$config['db_destino']['host']   = '127.0.0.1';
$config['db_destino']['user']   = 'root';
$config['db_destino']['pass']   = '';
$config['db_destino']['dbname'] = 'nst';


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


$container['db_destino'] = function ($c) {
    $db = $c['settings']['db_destino'];

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

function obtenerStockTotalProductoSeguro($db, $idProducto)
{
    $sql = "SELECT ROUND(
        COALESCE((SELECT SUM(md.Cantidad) FROM Lo_Movimiento m INNER JOIN Lo_MovimientoDetalle md ON m.`Hash`=md.hashMovimiento INNER JOIN Lo_MovimientoTipo mt ON m.IdMovimientoTipo=mt.IdMovimientoTipo WHERE mt.VaRegCompra=1 AND COALESCE(m.Anulado,0)=0 AND md.IdProducto=:id1),0)
        + COALESCE((SELECT SUM(md.Cantidad*pd.Cantidad) FROM Lo_Movimiento m INNER JOIN Lo_MovimientoDetalle md ON m.`Hash`=md.hashMovimiento INNER JOIN Lo_MovimientoTipo mt ON m.IdMovimientoTipo=mt.IdMovimientoTipo INNER JOIN Gen_ProductoDet pd ON md.IdProducto=pd.IdProducto WHERE mt.VaRegCompra=1 AND COALESCE(m.Anulado,0)=0 AND pd.IdProductoDet=:id2),0)
        - COALESCE((SELECT SUM(IF(mt.CodSunat='07',IF(m.NotaIdMotivo IN ('01','02','06','07'),md.Cantidad,0),md.Cantidad)) FROM Lo_Movimiento m INNER JOIN Lo_MovimientoDetalle md ON m.`Hash`=md.hashMovimiento INNER JOIN Lo_MovimientoTipo mt ON m.IdMovimientoTipo=mt.IdMovimientoTipo WHERE mt.VaRegCompra=1 AND COALESCE(m.Anulado,0)=0 AND md.IdProducto=:id3),0)
        - COALESCE((SELECT SUM(IF(mt.CodSunat='07',IF(m.NotaIdMotivo IN ('01','02','06','07'),pd.Cantidad*md.Cantidad,0),pd.Cantidad*md.Cantidad)) FROM Lo_Movimiento m INNER JOIN Lo_MovimientoDetalle md ON m.`Hash`=md.hashMovimiento INNER JOIN Lo_MovimientoTipo mt ON m.IdMovimientoTipo=mt.IdMovimientoTipo INNER JOIN Gen_ProductoDet pd ON md.IdProducto=pd.IdProducto WHERE mt.VaRegCompra=1 AND COALESCE(m.Anulado,0)=0 AND pd.IdProductoDet=:id4),0)
        - COALESCE((SELECT SUM(IF(td.CodSunat='07',IF(v.NotaIdMotivo IN ('01','02','06','07'),-vd.Cantidad,0),vd.Cantidad)) FROM Ve_DocVenta v INNER JOIN Ve_DocVentaDet vd ON v.idDocVenta=vd.IdDocVenta INNER JOIN Ve_DocVentaTipoDoc td ON v.IdTipoDoc=td.IdTipoDoc WHERE COALESCE(v.Anulado,0)=0 AND td.VaRegVenta=1 AND vd.IdProducto=:id5),0)
        - COALESCE((SELECT SUM(IF(td.CodSunat='07',IF(v.NotaIdMotivo IN ('01','02','06','07'),-pd.Cantidad*vd.Cantidad,0),pd.Cantidad*vd.Cantidad)) FROM Ve_DocVenta v INNER JOIN Ve_DocVentaDet vd ON v.idDocVenta=vd.IdDocVenta INNER JOIN Ve_DocVentaTipoDoc td ON v.IdTipoDoc=td.IdTipoDoc INNER JOIN Gen_ProductoDet pd ON vd.IdProducto=pd.IdProducto WHERE COALESCE(v.Anulado,0)=0 AND td.VaRegVenta=1 AND pd.IdProductoDet=:id6),0)
    ,2) Stock";
    $stmt = $db->prepare($sql);
    $stmt->execute(array(
        'id1' => $idProducto, 'id2' => $idProducto, 'id3' => $idProducto,
        'id4' => $idProducto, 'id5' => $idProducto, 'id6' => $idProducto
    ));
    return (float)$stmt->fetchColumn();
}

function compactarOrdenOfertasPedi2($db)
{
    $stmt = $db->query("SELECT IdProducto, PosOferta
        FROM Gen_Producto
        WHERE COALESCE(Anulado,0)=0
          AND IdOfertaCategoria IS NOT NULL
          AND IdOfertaCategoria > 0
          AND PosOferta > 0
          AND PosOferta < 10000
        ORDER BY PosOferta ASC, IdProducto ASC");
    $ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $update = $db->prepare("UPDATE Gen_Producto SET PosOferta=:posicion WHERE IdProducto=:idProducto");
    $posicion = 1;

    foreach ($ofertas as $oferta) {
        if ((int)$oferta['PosOferta'] !== $posicion) {
            $update->execute(array(
                'posicion' => $posicion,
                'idProducto' => (int)$oferta['IdProducto']
            ));
        }
        $posicion++;
    }
}

function desactivarOfertasPedi2Vencidas($db)
{
    $stmt = $db->prepare("UPDATE Gen_Producto
        SET IdOfertaCategoria=NULL, PosOferta=10000
        WHERE COALESCE(Anulado,0)=0
          AND IdOfertaCategoria IS NOT NULL
          AND IdOfertaCategoria > 0
          AND FechaOfertaFin IS NOT NULL
          AND FechaOfertaFin < CURDATE()");
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        compactarOrdenOfertasPedi2($db);
    }
}

function liberarPosicionOfertaPedi2($db, $idProducto)
{
    $idProducto = (int)$idProducto;
    if ($idProducto <= 0) {
        return;
    }

    desactivarOfertasPedi2Vencidas($db);
    $stmt = $db->prepare("SELECT PosOferta
        FROM Gen_Producto
        WHERE IdProducto=:idProducto
          AND IdOfertaCategoria IS NOT NULL
          AND IdOfertaCategoria > 0
          AND PosOferta > 0
          AND PosOferta < 10000
        LIMIT 1");
    $stmt->execute(array('idProducto' => $idProducto));
    $posicionAnterior = (int)$stmt->fetchColumn();

    if ($posicionAnterior > 0 && $posicionAnterior < 10000) {
        $shift = $db->prepare("UPDATE Gen_Producto
            SET PosOferta=PosOferta-1
            WHERE IdProducto<>:idProducto
              AND COALESCE(Anulado,0)=0
              AND IdOfertaCategoria IS NOT NULL
              AND IdOfertaCategoria > 0
              AND PosOferta>:posicionAnterior
              AND PosOferta<10000");
        $shift->execute(array(
            'idProducto' => $idProducto,
            'posicionAnterior' => $posicionAnterior
        ));
    }
}

function reservarPosicionOfertaPedi2($db, $idProducto, $posicionSolicitada)
{
    $idProducto = (int)$idProducto;
    $posicionSolicitada = max(1, (int)$posicionSolicitada);
    desactivarOfertasPedi2Vencidas($db);

    if ($idProducto > 0) {
        liberarPosicionOfertaPedi2($db, $idProducto);
    }

    $stmt = $db->prepare("SELECT COUNT(*)
        FROM Gen_Producto
        WHERE IdProducto<>:idProducto
          AND COALESCE(Anulado,0)=0
          AND IdOfertaCategoria IS NOT NULL
          AND IdOfertaCategoria > 0
          AND PosOferta > 0
          AND PosOferta < 10000");
    $stmt->execute(array('idProducto' => $idProducto));
    $cantidadOfertas = (int)$stmt->fetchColumn();
    $posicionFinal = min($posicionSolicitada, $cantidadOfertas + 1);

    $shift = $db->prepare("UPDATE Gen_Producto
        SET PosOferta=PosOferta+1
        WHERE IdProducto<>:idProducto
          AND COALESCE(Anulado,0)=0
          AND IdOfertaCategoria IS NOT NULL
          AND IdOfertaCategoria > 0
          AND PosOferta>=:posicionFinal
          AND PosOferta<10000
        ORDER BY PosOferta DESC");
    $shift->execute(array(
        'idProducto' => $idProducto,
        'posicionFinal' => $posicionFinal
    ));

    return $posicionFinal;
}

$app->post('/login', function (Request $request, Response $response, array $args) {

    $user = $request->getParam('user');
    $pass = $request->getParam('password');

    $select = "SELECT * FROM Seg_Usuario WHERE Usuario='$user' AND Password=SHA2('$pass',256) AND COALESCE(Anulado,0)=0";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/categorias', function (Request $request, Response $response, array $args) {
    $q = $request->getParam('q');
    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;

    $select = "SELECT * FROM Gen_ProductoCategoria";
    $select .= " WHERE ProductoCategoria LIKE '" . $q . "%' ";

    if ($limit) {
        // $limit = $request->getParam('limit');
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->post('/categorias', function (Request $request, Response $response, array $args) {

    $productoCategoria = $request->getParam('ProductoCategoria');
    $anulado = 0;

    $insert = $this->db->insert(array('ProductoCategoria', 'Anulado', 'FechaReg'))
        ->into('Gen_ProductoCategoria')
        ->values(array($productoCategoria, $anulado, getNow()));
    $insertId = $insert->execute();

    return $response->withJson(array("insertId" => $insertId, "ProductoCategoria" => $productoCategoria));
});


$app->get('/marcas', function (Request $request, Response $response, array $args) {
    try {
        //code...

        $q = $request->getParam('q');
        $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;

        $select = "SELECT * FROM Gen_ProductoMarca";
        $select .= " WHERE ProductoMarca LIKE '" . $q . "%' ";

        if ($limit) {
            // $limit = $request->getParam('limit');
            $offset = 0;
            if ($request->getParam('page')) {
                $page = $request->getParam('page');
                $offset = (--$page) * $limit;
            }
            $select .= " LIMIT " . $limit;
            $select .= " OFFSET " . $offset;
        }

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
        //throw $th;
    }
});
$app->get('/cargarmarcas', function (Request $request, Response $response, array $args) {
    try {
        $select = "SELECT * FROM Gen_ProductoMarca";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->post('/marcas', function (Request $request, Response $response, array $args) {

    $productoMarca = $request->getParam('ProductoMarca');
    $anulado = 0;

    $insert = $this->db->insert(array('ProductoMarca', 'Anulado', 'FechaReg'))
        ->into('Gen_ProductoMarca')
        ->values(array($productoMarca, $anulado, getNow()));
    $insertId = $insert->execute();

    return $response->withJson(array("insertId" => $insertId, "ProductoMarca" => $productoMarca));
});

// $app->get('/codigobarra', function (Request $request, Response $response, array $args) {
//     $q = $request->getParam('q');
//     $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;

//     $select = "SELECT * FROM Gen_Producto";
//     $select .= " WHERE CodigoBarra LIKE '" . $q . "%' ";

//     if ($limit) {
//         // $limit = $request->getParam('limit');
//         $offset = 0;
//         if ($request->getParam('page')) {
//             $page = $request->getParam('page');
//             $offset = (--$page) * $limit;
//         }
//         $select .= " LIMIT " . $limit;
//         $select .= " OFFSET " . $offset;
//     }

//     $stmt = $this->db->query($select);
//     $stmt->execute();
//     $data = $stmt->fetchAll();

//     return $response->withJson($data);
// });

// $app->post('/codigobarra', function (Request $request, Response $response, array $args) {

//     $productoMarca = $request->getParam('ProductoMarca');
//     $anulado = 0;

//     $insert = $this->db->insert(array('ProductoMarca', 'Anulado', 'FechaReg'))
//                        ->into('Gen_ProductoMarca')
//                        ->values(array($productoMarca, $anulado, getNow()));
//     $insertId = $insert->execute();

//     return $response->withJson(array("insertId" => $insertId, "ProductoMarca" => $productoMarca));
// });

$app->get('/mediciones', function (Request $request, Response $response, array $args) {
    $select = $this->db->select()->from('Gen_ProductoMedicion');
    $stmt = $select->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


/* $app->get('/modelos', function (Request $request, Response $response, array $args) {
    $select = $this->db->select()->from('Gen_ProductoModelo')
                ->whereLike('ProductoModelo','%' . $request->getParam('q') . '%');
    $stmt = $select->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
}); */

/* $app->post('/modelos', function (Request $request, Response $response, array $args) {

    $productoModelo = $request->getParam('ProductoModelo');
    $anulado = 0;

    $insert = $this->db->insert(array('ProductoModelo', 'Anulado', 'FechaReg'))
                       ->into('Gen_ProductoModelo')
                       ->values(array($productoModelo, $anulado, getNow()));
    $insertId = $insert->execute();

    return $response->withJson(array("insertId" => $insertId, "ProductoModelo" => $productoModelo));
}); */



$app->get('/tallas', function (Request $request, Response $response, array $args) {
    $q = $request->getParam('q');
    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;

    $select = "SELECT * FROM Gen_ProductoTalla";
    $select .= " WHERE ProductoTalla LIKE '" . $q . "%' ";

    if ($limit) {
        // $limit = $request->getParam('limit');
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->post('/tallas', function (Request $request, Response $response, array $args) {

    $productoTalla = $request->getParam('ProductoTalla');
    $anulado = 0;

    $insert = $this->db->insert(array('ProductoTalla', 'Anulado', 'FechaReg'))
        ->into('Gen_ProductoTalla')
        ->values(array($productoTalla, $anulado, getNow()));
    $insertId = $insert->execute();

    return $response->withJson(array("insertId" => $insertId, "ProductoTalla" => $productoTalla));
});


$app->get('/productos/id/{id}', function (Request $request, Response $response, array $args) {
    $select = "SELECT Gen_Producto.*, Ve_PreOrden.Descripcion, Gen_ProductoCategoria.ProductoCategoria, Gen_ProductoMarca.ProductoMarca, Gen_ProductoMedicion.ProductoMedicion ,
    Date(now()) Fecha
    FROM Gen_Producto
    INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
    INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
    INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion 
    LEFT JOIN Ve_PreOrden ON Gen_Producto.IdPreOrden = Ve_PreOrden.IdPreOrden
    ";


    if ($request->getParam('codigoBarra')) {
        $select .= " WHERE Gen_Producto.CodigoBarra = '" . $request->getParam('codigoBarra') . "'";
    } else {
        $select .= " WHERE Gen_Producto.IdProducto = '" . $args['id'] . "'";
    }

    $select .= " AND Gen_Producto.Anulado = 0";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/productos/colores', function (Request $request, Response $response, array $args) {
    $select = "SELECT DISTINCT Color FROM Gen_Producto";
    $select .= " WHERE Gen_Producto.Color LIKE '%" . $request->getParam('q') . "%' ";

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});
$app->get('/productos/presentaciones', function (Request $request, Response $response, array $args) {
    $select = "SELECT DISTINCT ProductoPresentacion FROM Gen_Producto";
    $select .= " WHERE Gen_Producto.ProductoPresentacion LIKE '%" . $request->getParam('q') . "%' ";

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});
$app->get('/productos/generos', function (Request $request, Response $response, array $args) {
    $select = "SELECT DISTINCT Genero FROM Gen_Producto";
    $select .= " WHERE Gen_Producto.Genero LIKE '%" . $request->getParam('q') . "%' ";

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});
$app->get('/productos/botapies', function (Request $request, Response $response, array $args) {
    $select = "SELECT DISTINCT Botapie FROM Gen_Producto";
    $select .= " WHERE Gen_Producto.Botapie LIKE '%" . $request->getParam('q') . "%' ";

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});
$app->get('/productos/modelos', function (Request $request, Response $response, array $args) {
    $select = "SELECT DISTINCT ProductoModelo FROM Gen_Producto";
    $select .= " WHERE Gen_Producto.ProductoModelo LIKE '%" . $request->getParam('q') . "%' ";

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/productosdet', function (Request $request, Response $response, array $args) {
    $idProducto = $request->getParam('idProducto');

    $select = "SELECT Gen_Producto.Producto, Gen_Producto.PrecioCosto, Gen_Producto.PrecioContado, Gen_ProductoDet.* FROM Gen_ProductoDet
        INNER JOIN Gen_Producto ON Gen_ProductoDet.IdProductoDet = Gen_Producto.IdProducto
        WHERE Gen_ProductoDet.IdProducto=$idProducto";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/productos/eshijo', function (Request $request, Response $response, array $args) {
    $idProducto = $request->getParam('idProducto');

    $select = "SELECT * FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet=$idProducto";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();
    if ($data) {
        $data = array("esHijo" => 1);
    } else {
        $data = array("esHijo" => 0);
    }
    return $response->withJson($data);
});
$app->get('/productos/espadre', function (Request $request, Response $response, array $args) {
    $idProducto = $request->getParam('idProducto');

    $select = "SELECT * FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProducto=$idProducto";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();
    if ($data) {
        $data = array("esPadre" => 1);
    } else {
        $data = array("esPadre" => 0);
    }
    return $response->withJson($data);
});

$app->get('/productos', function (Request $request, Response $response, array $args) {
    desactivarOfertasPedi2Vencidas($this->db);

    $select = "SELECT Gen_Producto.*, Gen_ProductoCategoria.ProductoCategoria, Gen_ProductoMarca.ProductoMarca,
        Gen_ProductoMedicion.ProductoMedicion
        FROM Gen_Producto
        INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
        INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
        INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion ";

    if ($request->getParam('filter')) {
        $filter = $request->getParam('filter');

        if (is_array($filter)) {
            $select .= " WHERE Gen_Producto.Anulado=0 AND (Gen_Producto.Producto LIKE '%" . (isset($filter['producto']) ? addslashes($filter['producto']) : '') .
                "%' AND Gen_Producto.CodigoBarra " . (isset($filter['codigo']) && $filter['codigo'] != '' ? " = '" . addslashes($filter['codigo']) . "'" : " LIKE '%'") .
                " AND Gen_ProductoMarca.ProductoMarca LIKE '" . (isset($filter['marca']) ? addslashes($filter['marca']) : '') .
                "%' ANd Gen_Producto.ProductoModelo LIKE '" . (isset($filter['modelo']) ? addslashes($filter['modelo']) : '') .
                "%' AND Gen_ProductoCategoria.ProductoCategoria LIKE '" . (isset($filter['categoria']) ? addslashes($filter['categoria']) : '') .
                "%' ) ";
        } else {
            $select .= " WHERE Gen_Producto.Anulado=0 AND ( Gen_Producto.Producto LIKE '%" . $filter .
                "%' OR Gen_Producto.CodigoBarra LIKE '%" . $filter .
                "%' OR Gen_ProductoMarca.ProductoMarca LIKE '%" . $filter .
                "%' OR Gen_Producto.ProductoModelo LIKE '%" . $filter .
                "%' OR Gen_ProductoCategoria.ProductoCategoria LIKE '%" . $filter .
                "%') ";
        }


        /* $select .= " WHERE Gen_Producto.Producto LIKE '%" . $filter .
                   "%' OR Gen_Producto.CodigoBarra LIKE '%" . $filter .
                   "%' OR Gen_Producto.Color LIKE '%" . $filter .
                   "%' OR Gen_Producto.Genero LIKE '%" . $filter .
                   "%' OR Gen_Producto.ProductoModelo LIKE '%" . $filter .
                   "%' OR Gen_ProductoMarca.ProductoMarca LIKE '%" . $filter .
                   "%' OR Gen_ProductoCategoria.ProductoCategoria LIKE '%" . $filter .
                   "%' ";        */
    } else {
        $select .= " WHERE Gen_Producto.Anulado=0 AND (Gen_Producto.Producto LIKE '%" . $request->getParam('q') . "%' OR Gen_Producto.ProductoModelo LIKE '" . $request->getParam('q') . "%')";
    }

    if ($request->getParam('controlaStock')) {
        $select .= " AND Gen_Producto.ControlaStock=1";
    }

    if ((int)$request->getParam('categoriaId') > 0) {
        $select .= " AND Gen_Producto.IdProductoCategoria=" . (int)$request->getParam('categoriaId');
    }
    if ((int)$request->getParam('marcaId') > 0) {
        $select .= " AND Gen_Producto.IdProductoMarca=" . (int)$request->getParam('marcaId');
    }
    if ((int)$request->getParam('soloOfertas') === 1) {
        $select .= " AND Gen_Producto.IdOfertaCategoria IS NOT NULL
            AND Gen_Producto.IdOfertaCategoria > 0
            AND Gen_Producto.PosOferta > 0
            AND Gen_Producto.PosOferta < 10000";
    }

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  20;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/productos/count', function (Request $request, Response $response, array $args) {
    desactivarOfertasPedi2Vencidas($this->db);

    $select = "SELECT COUNT(*) as total
        FROM Gen_Producto
        INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
        INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
        WHERE COALESCE(Gen_Producto.Anulado,0)=0";
    $params = array();
    $filter = trim((string)$request->getParam('filter'));

    if ($filter !== '') {
        $select .= " AND (
            Gen_Producto.Producto LIKE :filterProducto
            OR Gen_Producto.CodigoBarra LIKE :filterCodigo
            OR Gen_ProductoMarca.ProductoMarca LIKE :filterMarca
            OR Gen_Producto.ProductoModelo LIKE :filterModelo
            OR Gen_ProductoCategoria.ProductoCategoria LIKE :filterCategoria
        )";
        $search = '%' . $filter . '%';
        $params = array(
            'filterProducto' => $search,
            'filterCodigo' => $search,
            'filterMarca' => $search,
            'filterModelo' => $search,
            'filterCategoria' => $search
        );
    }

    if ((int)$request->getParam('categoriaId') > 0) {
        $select .= " AND Gen_Producto.IdProductoCategoria=" . (int)$request->getParam('categoriaId');
    }
    if ((int)$request->getParam('marcaId') > 0) {
        $select .= " AND Gen_Producto.IdProductoMarca=" . (int)$request->getParam('marcaId');
    }
    if ((int)$request->getParam('soloOfertas') === 1) {
        $select .= " AND Gen_Producto.IdOfertaCategoria IS NOT NULL
            AND Gen_Producto.IdOfertaCategoria > 0
            AND Gen_Producto.PosOferta > 0
            AND Gen_Producto.PosOferta < 10000";
    }

    $stmt = $this->db->prepare($select);
    $stmt->execute($params);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});

$app->post('/productos/oferta/desactivar', function (Request $request, Response $response) {
    try {
        $idProducto = (int)$request->getParam('IdProducto');

        if ($idProducto <= 0) {
            return $response->withJson(array(
                'success' => false,
                'message' => 'Producto no valido.'
            ), 400);
        }

        desactivarOfertasPedi2Vencidas($this->db);
        liberarPosicionOfertaPedi2($this->db, $idProducto);

        $stmt = $this->db->prepare("UPDATE Gen_Producto
            SET IdOfertaCategoria=NULL, PosOferta=10000
            WHERE IdProducto=:idProducto AND COALESCE(Anulado,0)=0");
        $stmt->execute(array('idProducto' => $idProducto));

        return $response->withJson(array(
            'success' => true,
            'IdProducto' => $idProducto,
            'message' => 'Oferta desactivada. El producto conserva su estado de publicacion en Pedi2.pe.'
        ));
    } catch (Exception $e) {
        return $response->withJson(array(
            'success' => false,
            'message' => $e->getMessage()
        ), 500);
    }
});

$app->post('/productos/oferta/orden', function (Request $request, Response $response) {
    try {
        $idProducto = (int)$request->getParam('IdProducto');
        $direccion = strtolower(trim((string)$request->getParam('Direccion')));

        if ($idProducto <= 0 || !in_array($direccion, array('subir', 'bajar'), true)) {
            return $response->withJson(array(
                'success' => false,
                'message' => 'Producto o direccion no valida.'
            ), 400);
        }

        desactivarOfertasPedi2Vencidas($this->db);
        compactarOrdenOfertasPedi2($this->db);

        $stmt = $this->db->prepare("SELECT PosOferta
            FROM Gen_Producto
            WHERE IdProducto=:idProducto
              AND IdOfertaCategoria IS NOT NULL
              AND IdOfertaCategoria > 0
              AND PosOferta > 0
              AND PosOferta < 10000
            LIMIT 1");
        $stmt->execute(array('idProducto' => $idProducto));
        $posicionActual = (int)$stmt->fetchColumn();

        if ($posicionActual <= 0 || $posicionActual >= 10000) {
            return $response->withJson(array(
                'success' => false,
                'message' => 'El producto no tiene una oferta activa.'
            ), 404);
        }

        $posicionDestino = $direccion === 'subir' ? $posicionActual - 1 : $posicionActual + 1;
        $stmt = $this->db->prepare("SELECT IdProducto
            FROM Gen_Producto
            WHERE COALESCE(Anulado,0)=0
              AND IdOfertaCategoria IS NOT NULL
              AND IdOfertaCategoria > 0
              AND PosOferta=:posicionDestino
            LIMIT 1");
        $stmt->execute(array('posicionDestino' => $posicionDestino));
        $idProductoDestino = (int)$stmt->fetchColumn();

        if ($posicionDestino <= 0 || $idProductoDestino <= 0) {
            return $response->withJson(array(
                'success' => true,
                'changed' => false,
                'PosOferta' => $posicionActual
            ));
        }

        $this->db->beginTransaction();
        $temporal = $this->db->prepare("UPDATE Gen_Producto SET PosOferta=99999 WHERE IdProducto=:idProducto");
        $temporal->execute(array('idProducto' => $idProducto));
        $moverDestino = $this->db->prepare("UPDATE Gen_Producto SET PosOferta=:posicionActual WHERE IdProducto=:idProductoDestino");
        $moverDestino->execute(array(
            'posicionActual' => $posicionActual,
            'idProductoDestino' => $idProductoDestino
        ));
        $moverActual = $this->db->prepare("UPDATE Gen_Producto SET PosOferta=:posicionDestino WHERE IdProducto=:idProducto");
        $moverActual->execute(array(
            'posicionDestino' => $posicionDestino,
            'idProducto' => $idProducto
        ));
        $this->db->commit();

        return $response->withJson(array(
            'success' => true,
            'changed' => true,
            'PosOferta' => $posicionDestino
        ));
    } catch (Exception $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        return $response->withJson(array(
            'success' => false,
            'message' => $e->getMessage()
        ), 500);
    }
});

$app->get('/productos/kardex/{id}', function (Request $request, Response $response, array $args) {

    $idProducto = $args['id'];
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaDesde = $request->getParam('fechaDesde') ? $request->getParam('fechaDesde') . ' 00:00:00' : 0;
    $fechaHasta = $request->getParam('fechaHasta') . ' 23:59:59';

    $strIngresos = stringIngresoUndProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde);
    $strIngresosCajas = stringIngresoCajaProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde);
    $strSalidas = stringSalidaUndProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde);
    $strSalidasCajas = stringSalidaCajaProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde);
    $strVentas = stringSalidaVentaUndProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde);
    $strVentasCajas = stringSalidaVentaCajaProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde);

    $select = $strIngresos . ' UNION ALL ' . $strIngresosCajas . ' UNION ALL ' . $strSalidas . ' UNION ALL ' .  $strSalidasCajas . ' UNION ALL ' . $strVentas . ' UNION ALL ' . $strVentasCajas;
    $select .= ' ORDER BY Fecha ASC';

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  0;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    if ($fechaDesde != false) {

        $strIngresos = stringIngresoUndProducto($idProducto, $idAlmacen, $fechaDesde, false,);
        $strIngresosCajas = stringIngresoCajaProducto($idProducto, $idAlmacen, $fechaDesde, false);
        $strSalidas = stringSalidaUndProducto($idProducto, $idAlmacen, $fechaDesde, false);
        $strSalidasCajas = stringSalidaCajaProducto($idProducto, $idAlmacen, $fechaDesde, false);
        $strVentas = stringSalidaVentaUndProducto($idProducto, $idAlmacen, $fechaDesde, false);
        $strVentasCajas = stringSalidaVentaCajaProducto($idProducto, $idAlmacen, $fechaDesde, false);

        $select2 = $strIngresos . ' UNION ALL ' . $strIngresosCajas . ' UNION ALL ' . $strSalidas . ' UNION ALL ' .  $strSalidasCajas . ' UNION ALL ' . $strVentas . ' UNION ALL ' . $strVentasCajas;
        $select2 .= ' ORDER BY Fecha ASC';

        $stmt2 = $this->db->query($select2);
        $stmt2->execute();
        $datasum = $stmt2->fetchAll();

        $inicialEntradas = 0;
        $inicialSalidas = 0;


        foreach ($datasum as $key => $value) {
            $inicialEntradas += (float)($value['IngresoCantidad']);
            $inicialSalidas += (float)($value['SalidaCantidad']);
        }
        // $newKardex = array_filter(kardexSaldo($kardex), $filtro);

        array_unshift($data, array(
            'Fecha' => $fechaDesde . ' 00:00:00',
            'TipoDocSunat' => 16,
            'IngresoCantidad' => $inicialEntradas,
            'IngresoPrecio' => 0,
            'SalidaCantidad' => $inicialSalidas,
            'SalidaPrecio' => 0,
            'Detalle' => 'ACUMULADO ANTERIOR',
            'Nombres' => '-',
            'Serie' => '-',
            'Numero' => '-',
            'Descuento' => 0
        ));
    }

    return $response->withJson($data);
});



/* NEUROSOFT */
$app->get('/habitaciones', function (Request $request, Response $response, array $args) {
    try {


        // Parámetros del cliente
        $pisoAlm = $request->getParam('pisoAlm');
        $sortBy = $request->getParam('sortBy') ?? 'Gen_Producto.IdProducto';
        $sortDesc = $request->getParam('sortDesc') ? 'DESC' : 'ASC';
        $limit = (int)($request->getParam('limit') ?? 10); // Valor predeterminado de 10
        $page = max((int)($request->getParam('page') ?? 1), 1);
        $offset = ($page - 1) * $limit;

        // Construcción de la consulta
        $select = "
        SELECT 
            Gen_Producto.*, 
            Gen_ProductoCategoria.ProductoCategoria, 
            Gen_ProductoMarca.ProductoMarca,
            Gen_ProductoMedicion.ProductoMedicion, 
            Ve_DocVentaCliente.DniRuc, 
            Ve_DocVentaCliente.Cliente, 
            Ve_DocVentaCliente.IdCliente,
            (Ve_PreOrdenTotal.granTotal - Cb_CajaBancoAdelanto.sumAdelanto) AS granTotalCalculado,
            Ve_PreOrden.IdProforma, 
            Ve_ProformaTotal.TotalProf, 
            Ve_PreOrden.Descripcion,
            Rhu_Empleado.Empleado
        FROM 
            Gen_Producto
        INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
        INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
        INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
        LEFT JOIN Rhu_Empleado ON Rhu_Empleado.IdEmpleado  = Gen_Producto.IdEmpleado
        LEFT JOIN Ve_PreOrden ON Gen_Producto.IdPreOrden = Ve_PreOrden.IdPreOrden
        LEFT JOIN (
            SELECT 
                Ve_Proforma.IdProforma, 
                SUM(Ve_ProformaDet.Cantidad * Ve_ProformaDet.Precio) AS TotalProf
            FROM 
                Ve_Proforma
            INNER JOIN Ve_ProformaDet ON Ve_Proforma.IdProforma = Ve_ProformaDet.IdProforma
            GROUP BY Ve_Proforma.IdProforma
        ) Ve_ProformaTotal ON Ve_ProformaTotal.IdProforma = Ve_PreOrden.IdProforma 
        LEFT JOIN Ve_DocVentaCliente ON Gen_Producto.IdClienteReserva = Ve_DocVentaCliente.IdCliente
        LEFT JOIN (
            SELECT 
                Ve_PreOrdenDet.IdPreOrden, 
                SUM(Ve_PreOrdenDet.Cantidad * Ve_PreOrdenDet.Precio * IF(Ve_PreOrdenDet.EsManoDeObra=1,0,1)) AS granTotal 
            FROM 
                Ve_PreOrdenDet
            GROUP BY Ve_PreOrdenDet.IdPreOrden
        ) Ve_PreOrdenTotal ON Ve_PreOrdenTotal.IdPreOrden = Gen_Producto.IdPreOrden
        LEFT JOIN (
            SELECT 
                Cb_CajaBanco.IdPreOrden, 
                SUM(Cb_CajaBanco.Importe) AS sumAdelanto 
            FROM 
                Cb_CajaBanco
            INNER JOIN Cb_TipoCajaBanco ON Cb_TipoCajaBanco.IdTipoCajaBanco = Cb_CajaBanco.IdTipoCajaBanco 
            WHERE 
                Cb_TipoCajaBanco.Tipo = 0
            GROUP BY Cb_CajaBanco.IdPreOrden
        ) Cb_CajaBancoAdelanto ON Cb_CajaBancoAdelanto.IdPreOrden = Gen_Producto.IdPreOrden
        WHERE 
            Gen_Producto.Anulado = 0 
            AND Gen_Producto.EsHabitacion = 1 
            AND Gen_Producto.EstadoHabitacion <> 1";

        // Filtro por piso
        if ($pisoAlm) {
            $select .= " AND Gen_Producto.Piso = $pisoAlm";
        }

        // Ordenar y paginar
        $select .= " ORDER BY $sortBy $sortDesc";

        // Ejecutar la consulta
        $stmt = $this->db->prepare($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], 500);
    }
});


$app->post('/habitaciones/alquilar', function (Request $request, Response $response) {
    $idProducto = $request->getParam('IdProducto');
    $idCliente = $request->getParam('IdCliente');

    $cliente = $request->getParam('cliente');
    $lugarProcedencia = $request->getParam('cliente')['LugarProcedencia'];
    $medioTransporte = $request->getParam('cliente')['MedioTransporte'];
    $proximoDestino = $request->getParam('cliente')['ProximoDestino'];

    $fechaAlquilerInicio = $request->getParam('cliente')['FechaAlquilerInicio'];
    $fechaAlquilerFin = $request->getParam('cliente')['FechaAlquilerFin'];
    $descripcion = $request->getParam('cliente')['Descripcion'] ? $request->getParam('cliente')['Descripcion'] : '';

    $vendedor = 'xx';
    if (isset($_SESSION['User'])) {
        $vendedor = $_SESSION['User'];
    }
    $usuarioReg = $request->getParam('usuario') ? $request->getParam('usuario') : $vendedor;

    // Crear la preorden
    $insert = $this->db->insert(array('IdCliente', 'FechaReg', 'LugarProcedencia', 'MedioTransporte', 'ProximoDestino', 'FechaAlquilerInicio', 'FechaAlquilerFin', 'Descripcion', 'UsuarioReg'))
        ->into('Ve_PreOrden')
        ->values(array($idCliente, getNow(), $lugarProcedencia, $medioTransporte, $proximoDestino, $fechaAlquilerInicio, $fechaAlquilerFin, $descripcion, $usuarioReg));

    $insertId = $insert->execute();

    // Insertar preorden Det
    $insertDet = $this->db->insert(array('IdPreOrden', 'IdProducto', 'Cantidad'))
        ->into('Ve_PreOrdenDet')
        ->values(array($insertId, $idProducto, 1));
    $insertDetId = $insertDet->execute();

    // Actualizar habitacion, cambio de estado
    $update = "UPDATE Gen_Producto SET EstadoHabitacion=3, IdPreOrden=$insertId, IdClienteReserva=$idCliente, FechaAlquilerFin='" . $fechaAlquilerFin . "', FechaAlquiler='" . $fechaAlquilerInicio . "' WHERE IdProducto=$idProducto";
    $stmt = $this->db->prepare($update);
    $updated = $stmt->execute();

    $data = array(
        "insertId" => $insertId
    );

    return $response->withJson($data);
});

$app->post('/proyecto/descripcion', function (Request $request, Response $response) {
    $idPreOrden = $request->getParam('IdPreOrden');
    $descripcion = $request->getParam('Descripcion');
    $update = "UPDATE Ve_PreOrden SET Descripcion='$descripcion' WHERE IdPreOrden=$idPreOrden";
    $stmt = $this->db->prepare($update);
    $updated = $stmt->execute();

    return $response->withJson($updated);
});

$app->post('/habitaciones/liberar', function (Request $request, Response $response) {
    $idProducto = $request->getParam('IdProducto');

    // Actualizar habitacion, cambio de estado
    $update = "UPDATE Gen_Producto SET EstadoHabitacion=1 WHERE IdProducto=$idProducto";
    // $update = "UPDATE Gen_Producto SET EstadoHabitacion=1, EstaSucio=0, IdPreOrden=NULL, IdClienteReserva=NULL, FechaReserva=NULL, FechaAlquiler=NULL WHERE IdProducto=$idProducto";
    $stmt = $this->db->prepare($update);
    $updated = $stmt->execute();

    return $response->withJson(array(
        "updated" => $updated,
        "IdProducto" => $idProducto
    ));
});

$app->post('/habitaciones/reservar', function (Request $request, Response $response) {
    $idProducto = $request->getParam('IdProducto');
    $idCliente = $request->getParam('IdCliente');
    $fechaReserva = $request->getParam('FechaReserva') ? $request->getParam('FechaReserva') : getNow();
    $vuelo = $request->getParam('Vuelo') ? $request->getParam('Vuelo') : '';

    // Actualizar habitacion, cambio de estado
    $update = "UPDATE Gen_Producto SET EstadoHabitacion=2, Vuelo='$vuelo', IdPreOrden=NULL, IdClienteReserva=$idCliente, FechaReserva='" . $fechaReserva . "' WHERE IdProducto=$idProducto";
    $stmt = $this->db->prepare($update);
    $updated = $stmt->execute();

    return $response->withJson(array(
        "updated" => $updated,
        "IdProducto" => $idProducto,
        "quer" => $update
    ));
});

$app->get('/productos/masrotacion', function (Request $request, Response $response, array $args) {

    $idProducto = 1;
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaDesde = $request->getParam('fechaDesde') ? $request->getParam('fechaDesde') : '2017-01-01T00:00:00';
    $fechaHasta = $request->getParam('fechaHasta') . " 23:59:59";

    $strIngresoUnd = stringIngresoUnd(false, $idAlmacen, $fechaHasta, $fechaDesde);
    $strSalidaUnd = stringSalidaUnd(false, $idAlmacen, $fechaHasta, $fechaDesde);

    $strIngresoCaja = stringIngresoCaja(false, $idAlmacen, $fechaHasta, $fechaDesde);
    $strSalidaCaja = stringSalidaCaja(false, $idAlmacen, $fechaHasta, $fechaDesde);

    $strSalidaVentaUnd = stringSalidaVentaUnd(false, $idAlmacen, $fechaHasta, $fechaDesde);
    $strSalidaVentaCaja = stringSalidaVentaCaja(false, $idAlmacen, $fechaHasta, $fechaDesde);

    $strIngresoUndCount = stringIngresoUndCount(false, $idAlmacen, $fechaHasta, $fechaDesde);
    $strVentaUndCount = stringVentaUndCount(false, $idAlmacen, $fechaHasta, $fechaDesde);

    $select = "SELECT Gen_Producto.*, Gen_ProductoCategoria.ProductoCategoria, Gen_ProductoMarca.ProductoMarca,
        Gen_ProductoMedicion.ProductoMedicion,
        IFNULL(IngresoUnd.cantidad, 0) AS StockIngresoUnd,
        IFNULL(SalidaUnd.cantidad, 0) AS StockSalidaUnd,
        IFNULL(SalidaVentaUnd.cantidad, 0) AS StockSalidaVentaUnd,
        IFNULL(SalidaVentaCaja.cantidad, 0) AS StockSalidaVentaCaja,
        (IFNULL(SalidaVentaUnd.cantidad, 0) + IFNULL(SalidaVentaCaja.cantidad, 0)) AS CantidadVentas,
        IFNULL(IngresoUndCount.cantidad, 0) AS TotalCompras,
        IFNULL(VentaUndCount.cantidad, 0) AS TotalVentas,
        IFNULL(IngresoUnd.cantidad, 0) AS CantidadCompras,
        IFNULL(IngresoUndCount.monto, 0) AS MontoCompras,
        IFNULL(VentaUndCount.monto, 0) AS MontoVentas,

        ROUND((IFNULL(IngresoUnd.cantidad, 0) + IFNULL(IngresoCaja.cantidad, 0)  - IFNULL(SalidaCaja.cantidad, 0) - IFNULL(SalidaUnd.cantidad, 0) - IFNULL(SalidaVentaUnd.cantidad,0) - IFNULL(SalidaVentaCaja.cantidad,0)),2) AS stock

        FROM Gen_Producto
        INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
        INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
        INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
        LEFT JOIN $strIngresoUnd AS IngresoUnd ON Gen_Producto.IdProducto = IngresoUnd.IdProducto
        LEFT JOIN $strSalidaUnd AS SalidaUnd ON Gen_Producto.IdProducto = SalidaUnd.IdProducto
        LEFT JOIN $strIngresoCaja AS IngresoCaja ON Gen_Producto.IdProducto = IngresoCaja.IdProducto
        LEFT JOIN $strSalidaCaja AS SalidaCaja ON Gen_Producto.IdProducto = SalidaCaja.IdProducto
        LEFT JOIN $strSalidaVentaUnd AS SalidaVentaUnd ON Gen_Producto.IdProducto = SalidaVentaUnd.IdProducto
        LEFT JOIN $strSalidaVentaCaja AS SalidaVentaCaja ON Gen_Producto.IdProducto = SalidaVentaCaja.IdProducto
        LEFT JOIN $strIngresoUndCount AS IngresoUndCount ON Gen_Producto.IdProducto = IngresoUndCount.IdProducto
        LEFT JOIN $strVentaUndCount AS VentaUndCount ON Gen_Producto.IdProducto = VentaUndCount.IdProducto
        ORDER BY MontoVentas DESC";

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  0;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $this->db->query('SET SQL_BIG_SELECTS=1');
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->get('/proveedores', function (Request $request, Response $response, array $args) {
    $q = $request->getParam('q');

    $select = "SELECT *, IFNULL(CONCAT(Ruc, ' - ', Proveedor), '-') AS ProveedorRuc FROM Lo_Proveedor";
    $select .= " WHERE Proveedor LIKE '%" . $q . "%' OR Ruc LIKE '%" . $q . "%' ";

    $limit = $request->getParam('limit') ? $request->getParam('limit') : 10;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/proveedores/count', function (Request $request, Response $response, array $args) {
    $select = "SELECT COUNT(*) as total FROM Lo_Proveedor";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});


$app->post('/proveedores', function (Request $request, Response $response) {
    $proveedor = $request->getParam('Proveedor');
    $ruc = $request->getParam('Ruc');
    $direccion = $request->getParam('Direccion');
    $telefono = $request->getParam('Telefono') ? $request->getParam('Telefono') : '';
    $observacion = $request->getParam('Observacion');

    $insert = $this->db->insert(array('Proveedor', 'Ruc', 'Direccion', 'Observacion', 'FechaReg', 'Telefono'))
        ->into('Lo_Proveedor')
        ->values(array($proveedor, $ruc, $direccion, $observacion, getNow(), $telefono));

    $insertId = $insert->execute();

    $data = array(
        "insertId" => $insertId
    );

    return $response->withJson($data);
});


$app->post('/productos/delete', function (Request $request, Response $response) {
    $id = (int)$request->getParam('id');

    if ($id) {
        $stock = obtenerStockTotalProductoSeguro($this->db, $id);
        if (abs($stock) > 0.0001) {
            return $response->withJson(array(
                "updated" => false,
                "message" => "No se puede eliminar: el producto tiene stock " . number_format($stock, 2, '.', ''),
                "stock" => $stock
            ), 409);
        }

        $stmt = $this->db->prepare("UPDATE Gen_Producto SET Anulado=1 WHERE IdProducto=:id");
        $updated = $stmt->execute(array('id' => $id));


        return $response->withJson(array(
            "updated" => $updated,
            "IdProducto" => $id
        ));
    }

    return $response->withJson(array("updated" => false, "message" => "Producto no valido."), 400);
});

/*
 * Productos sin movimiento y control de baja logica.
 * La baja nunca elimina historial: solo cambia Gen_Producto.Anulado.
 * Para desactivar se exige stock total exactamente en cero.
 */
$app->get('/productos/sin-movimiento', function (Request $request, Response $response) {
    try {
        $fechaDesde = $request->getParam('fechaDesde');
        $fechaHasta = $request->getParam('fechaHasta');
        $estado = (int)($request->getParam('estado') ?? 0);
        $busqueda = trim((string)$request->getParam('q'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesde) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHasta)) {
            return $response->withJson(array('success' => false, 'message' => 'El rango de fechas no es valido.'), 400);
        }

        if ($fechaDesde > $fechaHasta) {
            return $response->withJson(array('success' => false, 'message' => 'La fecha inicial no puede ser posterior a la fecha final.'), 400);
        }

        $estado = $estado === 1 ? 1 : 0;
        $hastaStock = date('Y-m-d H:i:s', strtotime($fechaHasta . ' +1 day'));
        $desdeVenta = $fechaDesde . ' 00:00:00';
        $hastaVenta = date('Y-m-d H:i:s', strtotime($fechaHasta . ' +1 day'));

        $ingresoUnd = "(SELECT md.IdProducto, SUM(md.Cantidad) cantidad
            FROM Lo_Movimiento m
            INNER JOIN Lo_MovimientoDetalle md ON m.`Hash`=md.hashMovimiento
            INNER JOIN Lo_MovimientoTipo mt ON m.IdMovimientoTipo=mt.IdMovimientoTipo
            WHERE mt.VaRegCompra=1 AND COALESCE(m.Anulado,0)=0 AND m.MovimientoFecha < :hastaStockIu
            GROUP BY md.IdProducto)";
        $ingresoCaja = "(SELECT pd.IdProductoDet IdProducto, SUM(md.Cantidad * pd.Cantidad) cantidad
            FROM Lo_Movimiento m
            INNER JOIN Lo_MovimientoDetalle md ON m.`Hash`=md.hashMovimiento
            INNER JOIN Lo_MovimientoTipo mt ON m.IdMovimientoTipo=mt.IdMovimientoTipo
            INNER JOIN Gen_ProductoDet pd ON md.IdProducto=pd.IdProducto
            WHERE mt.VaRegCompra=1 AND COALESCE(m.Anulado,0)=0 AND m.MovimientoFecha < :hastaStockIc
            GROUP BY pd.IdProductoDet)";
        $salidaUnd = "(SELECT md.IdProducto, SUM(IF(mt.CodSunat='07', IF(m.NotaIdMotivo IN ('01','02','06','07'),md.Cantidad,0),md.Cantidad)) cantidad
            FROM Lo_Movimiento m
            INNER JOIN Lo_MovimientoDetalle md ON m.`Hash`=md.hashMovimiento
            INNER JOIN Lo_MovimientoTipo mt ON m.IdMovimientoTipo=mt.IdMovimientoTipo
            WHERE mt.VaRegCompra=1 AND COALESCE(m.Anulado,0)=0 AND m.MovimientoFecha < :hastaStockSu
            GROUP BY md.IdProducto)";
        $salidaCaja = "(SELECT pd.IdProductoDet IdProducto, SUM(IF(mt.CodSunat='07', IF(m.NotaIdMotivo IN ('01','02','06','07'),pd.Cantidad*md.Cantidad,0),pd.Cantidad*md.Cantidad)) cantidad
            FROM Lo_Movimiento m
            INNER JOIN Lo_MovimientoDetalle md ON m.`Hash`=md.hashMovimiento
            INNER JOIN Lo_MovimientoTipo mt ON m.IdMovimientoTipo=mt.IdMovimientoTipo
            INNER JOIN Gen_ProductoDet pd ON md.IdProducto=pd.IdProducto
            WHERE mt.VaRegCompra=1 AND COALESCE(m.Anulado,0)=0 AND m.MovimientoFecha < :hastaStockSc
            GROUP BY pd.IdProductoDet)";
        $ventaUnd = "(SELECT vd.IdProducto, SUM(IF(td.CodSunat='07', IF(v.NotaIdMotivo IN ('01','02','06','07'),-vd.Cantidad,0),vd.Cantidad)) cantidad
            FROM Ve_DocVenta v
            INNER JOIN Ve_DocVentaDet vd ON v.idDocVenta=vd.IdDocVenta
            INNER JOIN Ve_DocVentaTipoDoc td ON v.IdTipoDoc=td.IdTipoDoc
            WHERE COALESCE(v.Anulado,0)=0 AND td.VaRegVenta=1 AND v.FechaDoc < :hastaStockVu
            GROUP BY vd.IdProducto)";
        $ventaCaja = "(SELECT pd.IdProductoDet IdProducto, SUM(IF(td.CodSunat='07', IF(v.NotaIdMotivo IN ('01','02','06','07'),-pd.Cantidad*vd.Cantidad,0),pd.Cantidad*vd.Cantidad)) cantidad
            FROM Ve_DocVenta v
            INNER JOIN Ve_DocVentaDet vd ON v.idDocVenta=vd.IdDocVenta
            INNER JOIN Ve_DocVentaTipoDoc td ON v.IdTipoDoc=td.IdTipoDoc
            INNER JOIN Gen_ProductoDet pd ON vd.IdProducto=pd.IdProducto
            WHERE COALESCE(v.Anulado,0)=0 AND td.VaRegVenta=1 AND v.FechaDoc < :hastaStockVc
            GROUP BY pd.IdProductoDet)";

        $sql = "SELECT p.IdProducto, p.CodigoBarra, p.Producto,
                c.ProductoCategoria, m.ProductoMarca, p.Anulado,
                uv.UltimaVenta,
                ROUND(COALESCE(iu.cantidad,0)+COALESCE(ic.cantidad,0)-COALESCE(su.cantidad,0)-COALESCE(sc.cantidad,0)-COALESCE(vu.cantidad,0)-COALESCE(vc.cantidad,0),2) Stock
            FROM Gen_Producto p
            INNER JOIN Gen_ProductoCategoria c ON p.IdProductoCategoria=c.IdProductoCategoria
            INNER JOIN Gen_ProductoMarca m ON p.IdProductoMarca=m.IdProductoMarca
            LEFT JOIN $ingresoUnd iu ON p.IdProducto=iu.IdProducto
            LEFT JOIN $ingresoCaja ic ON p.IdProducto=ic.IdProducto
            LEFT JOIN $salidaUnd su ON p.IdProducto=su.IdProducto
            LEFT JOIN $salidaCaja sc ON p.IdProducto=sc.IdProducto
            LEFT JOIN $ventaUnd vu ON p.IdProducto=vu.IdProducto
            LEFT JOIN $ventaCaja vc ON p.IdProducto=vc.IdProducto
            LEFT JOIN (
                SELECT movimientos.IdProducto, MAX(movimientos.FechaDoc) UltimaVenta
                FROM (
                    SELECT vd.IdProducto, v.FechaDoc
                    FROM Ve_DocVenta v
                    INNER JOIN Ve_DocVentaDet vd ON v.idDocVenta=vd.IdDocVenta
                    INNER JOIN Ve_DocVentaTipoDoc td ON v.IdTipoDoc=td.IdTipoDoc
                    WHERE COALESCE(v.Anulado,0)=0 AND td.VaRegVenta=1
                    UNION ALL
                    SELECT pd.IdProductoDet IdProducto, v.FechaDoc
                    FROM Ve_DocVenta v
                    INNER JOIN Ve_DocVentaDet vd ON v.idDocVenta=vd.IdDocVenta
                    INNER JOIN Ve_DocVentaTipoDoc td ON v.IdTipoDoc=td.IdTipoDoc
                    INNER JOIN Gen_ProductoDet pd ON vd.IdProducto=pd.IdProducto
                    WHERE COALESCE(v.Anulado,0)=0 AND td.VaRegVenta=1
                ) movimientos
                GROUP BY movimientos.IdProducto
            ) uv ON p.IdProducto=uv.IdProducto
            WHERE COALESCE(p.Anulado,0)=:estado
              AND p.ControlaStock=1
              AND NOT EXISTS (
                SELECT 1
                FROM Ve_DocVenta v2
                INNER JOIN Ve_DocVentaDet vd2 ON v2.idDocVenta=vd2.IdDocVenta
                INNER JOIN Ve_DocVentaTipoDoc td2 ON v2.IdTipoDoc=td2.IdTipoDoc
                LEFT JOIN Gen_ProductoDet pd2 ON vd2.IdProducto=pd2.IdProducto
                WHERE COALESCE(v2.Anulado,0)=0
                  AND td2.VaRegVenta=1
                  AND (vd2.IdProducto=p.IdProducto OR pd2.IdProductoDet=p.IdProducto)
                  AND v2.FechaDoc >= :desdeVenta
                  AND v2.FechaDoc < :hastaVenta
              )";

        $params = array(
            'hastaStockIu' => $hastaStock,
            'hastaStockIc' => $hastaStock,
            'hastaStockSu' => $hastaStock,
            'hastaStockSc' => $hastaStock,
            'hastaStockVu' => $hastaStock,
            'hastaStockVc' => $hastaStock,
            'estado' => $estado,
            'desdeVenta' => $desdeVenta,
            'hastaVenta' => $hastaVenta
        );

        if ($busqueda !== '') {
            $sql .= " AND (p.Producto LIKE :busquedaProducto OR p.CodigoBarra LIKE :busquedaCodigo OR c.ProductoCategoria LIKE :busquedaCategoria OR m.ProductoMarca LIKE :busquedaMarca)";
            $terminoBusqueda = '%' . $busqueda . '%';
            $params['busquedaProducto'] = $terminoBusqueda;
            $params['busquedaCodigo'] = $terminoBusqueda;
            $params['busquedaCategoria'] = $terminoBusqueda;
            $params['busquedaMarca'] = $terminoBusqueda;
        }

        $sql .= " ORDER BY Stock DESC, p.Producto ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $response->withJson(array(
            'success' => true,
            'items' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ));
    } catch (Exception $e) {
        return $response->withJson(array('success' => false, 'message' => $e->getMessage()), 500);
    }
});

$app->post('/productos/estado', function (Request $request, Response $response) {
    try {
        $idProducto = (int)$request->getParam('IdProducto');
        $anulado = (int)$request->getParam('Anulado') === 1 ? 1 : 0;

        if ($idProducto <= 0) {
            return $response->withJson(array('success' => false, 'message' => 'Producto no valido.'), 400);
        }

        if ($anulado === 1) {
            $sqlStock = "SELECT ROUND(
                COALESCE((SELECT SUM(md.Cantidad) FROM Lo_Movimiento m INNER JOIN Lo_MovimientoDetalle md ON m.`Hash`=md.hashMovimiento INNER JOIN Lo_MovimientoTipo mt ON m.IdMovimientoTipo=mt.IdMovimientoTipo WHERE mt.VaRegCompra=1 AND COALESCE(m.Anulado,0)=0 AND md.IdProducto=:id1),0)
                + COALESCE((SELECT SUM(md.Cantidad*pd.Cantidad) FROM Lo_Movimiento m INNER JOIN Lo_MovimientoDetalle md ON m.`Hash`=md.hashMovimiento INNER JOIN Lo_MovimientoTipo mt ON m.IdMovimientoTipo=mt.IdMovimientoTipo INNER JOIN Gen_ProductoDet pd ON md.IdProducto=pd.IdProducto WHERE mt.VaRegCompra=1 AND COALESCE(m.Anulado,0)=0 AND pd.IdProductoDet=:id2),0)
                - COALESCE((SELECT SUM(IF(mt.CodSunat='07',IF(m.NotaIdMotivo IN ('01','02','06','07'),md.Cantidad,0),md.Cantidad)) FROM Lo_Movimiento m INNER JOIN Lo_MovimientoDetalle md ON m.`Hash`=md.hashMovimiento INNER JOIN Lo_MovimientoTipo mt ON m.IdMovimientoTipo=mt.IdMovimientoTipo WHERE mt.VaRegCompra=1 AND COALESCE(m.Anulado,0)=0 AND md.IdProducto=:id3),0)
                - COALESCE((SELECT SUM(IF(mt.CodSunat='07',IF(m.NotaIdMotivo IN ('01','02','06','07'),pd.Cantidad*md.Cantidad,0),pd.Cantidad*md.Cantidad)) FROM Lo_Movimiento m INNER JOIN Lo_MovimientoDetalle md ON m.`Hash`=md.hashMovimiento INNER JOIN Lo_MovimientoTipo mt ON m.IdMovimientoTipo=mt.IdMovimientoTipo INNER JOIN Gen_ProductoDet pd ON md.IdProducto=pd.IdProducto WHERE mt.VaRegCompra=1 AND COALESCE(m.Anulado,0)=0 AND pd.IdProductoDet=:id4),0)
                - COALESCE((SELECT SUM(IF(td.CodSunat='07',IF(v.NotaIdMotivo IN ('01','02','06','07'),-vd.Cantidad,0),vd.Cantidad)) FROM Ve_DocVenta v INNER JOIN Ve_DocVentaDet vd ON v.idDocVenta=vd.IdDocVenta INNER JOIN Ve_DocVentaTipoDoc td ON v.IdTipoDoc=td.IdTipoDoc WHERE COALESCE(v.Anulado,0)=0 AND td.VaRegVenta=1 AND vd.IdProducto=:id5),0)
                - COALESCE((SELECT SUM(IF(td.CodSunat='07',IF(v.NotaIdMotivo IN ('01','02','06','07'),-pd.Cantidad*vd.Cantidad,0),pd.Cantidad*vd.Cantidad)) FROM Ve_DocVenta v INNER JOIN Ve_DocVentaDet vd ON v.idDocVenta=vd.IdDocVenta INNER JOIN Ve_DocVentaTipoDoc td ON v.IdTipoDoc=td.IdTipoDoc INNER JOIN Gen_ProductoDet pd ON vd.IdProducto=pd.IdProducto WHERE COALESCE(v.Anulado,0)=0 AND td.VaRegVenta=1 AND pd.IdProductoDet=:id6),0)
            ,2) Stock";
            $stmtStock = $this->db->prepare($sqlStock);
            $stmtStock->execute(array(
                'id1' => $idProducto, 'id2' => $idProducto, 'id3' => $idProducto,
                'id4' => $idProducto, 'id5' => $idProducto, 'id6' => $idProducto
            ));
            $stock = (float)$stmtStock->fetchColumn();

            if (abs($stock) > 0.0001) {
                return $response->withJson(array(
                    'success' => false,
                    'message' => 'No se puede eliminar: el producto tiene stock ' . number_format($stock, 2, '.', ''),
                    'stock' => $stock
                ), 409);
            }
        }

        $stmt = $this->db->prepare("UPDATE Gen_Producto SET Anulado=:anulado WHERE IdProducto=:idProducto");
        $stmt->execute(array('anulado' => $anulado, 'idProducto' => $idProducto));

        return $response->withJson(array(
            'success' => true,
            'message' => $anulado === 1 ? 'Producto eliminado y ocultado correctamente.' : 'Producto reactivado correctamente.'
        ));
    } catch (Exception $e) {
        return $response->withJson(array('success' => false, 'message' => $e->getMessage()), 500);
    }
});


$app->post('/productos', function (Request $request, Response $response) {
    $usuarioReg = 'xx';
    if (isset($_SESSION['user'])) {
        $usuarioReg = $_SESSION['user'];
    }
    $idProductoMarca = $request->getParam('marca')['IdProductoMarca'];
    $idProductoMedicion = $request->getParam('medicion')['IdProductoMedicion'];
    $idProductoCategoria = $request->getParam('categoria')['IdProductoCategoria'];
    // $idProductoModelo = $request->getParam('modelo')['IdProductoModelo'];
    // $idProductoTalla = $request->getParam('talla')['IdProductoTalla'];
    $productoSerie = $request->getParam('productoSerie');
    $idProductoFormaFarmaceutica = 1;
    $producto = $request->getParam('Producto');
    $productoDesc = $request->getParam('ProductoDesc');
    $productoDesc2 = $request->getParam('ProductoDesc2');
    $productoDesc3 = $request->getParam('ProductoDesc3');
    $productoModelo = $request->getParam('ProductoModelo') ? $request->getParam('ProductoModelo') : '';
    $fechaReg = getNow();
    $hash = time();
    // $controlaStock = 1;
    $porcentajeUtilidad = $request->getParam('PorcentajeUtilidad');
    $genero = $request->getParam('Genero');
    $color = $request->getParam('Color');
    $botapie = $request->getParam('Botapie');
    $anulado = $request->getParam('Anulado');
    $categoria = $request->getParam('categoria')['ProductoCategoria'];
    $productoPresentacion = $request->getParam('ProductoPresentacion');
    $esPadre = $request->getParam('EsPadre');
    $controlaStockMinimo = $request->getParam('ControlaStockMinimo') ? $request->getParam('ControlaStockMinimo') : 0;
    $stockMinimo = $request->getParam('StockMinimo');
    $codigoBarra = $request->getParam('CodigoBarra');
    $precioPorMayor = $request->getParam('PrecioPorMayor') ? $request->getParam('PrecioPorMayor') : null;
    $esHabitacion = $request->getParam('EsHabitacion');
    $piso = $request->getParam('Piso');
    $controlaStock = $request->getParam('ControlaStock') ? $request->getParam('ControlaStock') : 0;
    $mostrarApp = $request->getParam('MostrarApp') ? $request->getParam('MostrarApp') : 0;
    $posOferta = $request->getParam('PosOferta') > 0 ? $request->getParam('PosOferta') : 10000;
    $PrecioApp = $request->getParam('PrecioApp') ? $request->getParam('PrecioApp') : null;
    $ofertaWeb = (int)$request->getParam('OfertaWeb') === 1 ? 1 : 0;
    $precioWeb = $request->getParam('PrecioWeb') !== null && $request->getParam('PrecioWeb') !== '' ? max(0, (float)$request->getParam('PrecioWeb')) : null;
    $precioAnterior = $request->getParam('PrecioAnterior') !== null && $request->getParam('PrecioAnterior') !== '' ? max(0, (float)$request->getParam('PrecioAnterior')) : null;

    $productosDet = $request->getParam('productosDet');
    $precioCosto = $request->getParam('PrecioCosto') ? $request->getParam('PrecioCosto') : 0;
    $precioContado = $request->getParam('PrecioContado') ? $request->getParam('PrecioContado') : 0;
    $idOfertaCategoria = $request->getParam('IdOfertaCategoria');
    $fechaOfertaInicio = trim((string)$request->getParam('FechaOfertaInicio'));
    $fechaOfertaFin = trim((string)$request->getParam('FechaOfertaFin'));
    $preciosPorProducto = json_encode($request->getParam('PreciosPorProducto'));
    $porcentajeLiquidacion = $request->getParam('PorcentajeLiquidacion') > 0 ? $request->getParam('PorcentajeLiquidacion') : 0;
    $tienePresentacion = $request->getParam('TienePresentacion') > 0 ? $request->getParam('TienePresentacion') : 0;
    $opcContable = $request->getParam('OpcContable') > 0 ? $request->getParam('OpcContable') : 0;

    if (empty($idOfertaCategoria)) {
        $fechaOfertaInicio = null;
        $fechaOfertaFin = null;
    } else {
        $tieneFechaInicio = $fechaOfertaInicio !== '';
        $tieneFechaFin = $fechaOfertaFin !== '';

        if ($tieneFechaInicio !== $tieneFechaFin) {
            return $response->withJson(array(
                'error' => 'Seleccione la fecha de inicio y la fecha de fin de la oferta.'
            ), 400);
        }

        if ($tieneFechaInicio) {
            $fechaInicioValida = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaOfertaInicio);
            $fechaFinValida = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaOfertaFin);
            if (!$fechaInicioValida || !$fechaFinValida || $fechaOfertaFin < $fechaOfertaInicio) {
                return $response->withJson(array(
                    'error' => 'El rango de fechas de la oferta no es valido.'
                ), 400);
            }
        } else {
            $fechaOfertaInicio = null;
            $fechaOfertaFin = null;
        }
    }

    $idProductoOrden = (int)$request->getParam('IdProducto');
    if ($idProductoOrden > 0 && !empty($idOfertaCategoria) && $posOferta > 0 && $posOferta < 10000) {
        $posOferta = reservarPosicionOfertaPedi2($this->db, $idProductoOrden, $posOferta);
    } elseif ($idProductoOrden > 0) {
        liberarPosicionOfertaPedi2($this->db, $idProductoOrden);
    }

    // print_r($productoDesc);
    // Actualizamos el producto si le pasamos el ID
    if ($request->getParam('IdProducto')) {
        // aqui se actualiza el producto si existe
        $idProducto = $request->getParam('IdProducto');
        // $codigoBarra = $request->getParam('CodigoBarra');
        $precioConvenio = $request->getParam('precioConvenio');

        $update = $this->db->update(array(
            "CodigoBarra" =>                    $codigoBarra,
            "Producto" =>                       $producto,
            "ProductoDesc" =>                   $productoDesc,
            "ProductoDesc2" =>                  $productoDesc2,
            "ProductoDesc3" =>                  $productoDesc3,
            "IdProductoMarca" =>                $idProductoMarca,
            "IdProductoFormaFarmaceutica" =>    $idProductoFormaFarmaceutica,
            "IdProductoMedicion" =>             $idProductoMedicion,
            "IdProductoCategoria" =>            $idProductoCategoria,
            // "IdProductoModelo" => $idProductoModelo,
            // "IdProductoTalla" => $idProductoTalla,
            "ControlaStock" =>                  $controlaStock,
            "PorcentajeUtilidad" =>             $porcentajeUtilidad,
            "Genero" =>                         $genero,
            "Color" =>                          $color,
            "Botapie" =>                        $botapie,
            "Anulado" =>                        $anulado,
            "ProductoModelo" =>                 $productoModelo,
            "PrecioCosto" =>                    $precioCosto,
            "PrecioContado" =>                  $precioContado,
            "precioConvenio" =>                 $precioConvenio,
            "ProductoPresentacion" =>           $productoPresentacion,
            "EsPadre" =>                        $esPadre,
            "ControlaStockMinimo" =>             $controlaStockMinimo,
            "StockMinimo" =>                    $stockMinimo,
            "PrecioPorMayor" =>                 $precioPorMayor,
            "EsHabitacion" =>                   $esHabitacion,
            "Piso" =>                           $piso,
            "PreciosPorProducto" =>             $preciosPorProducto,
            "IdOfertaCategoria" =>              $idOfertaCategoria,
            "MostrarApp" =>                     $mostrarApp,
            "PosOferta" =>                      $posOferta,
            "PorcentajeLiquidacion" =>          $porcentajeLiquidacion,
            "TienePresentacion" =>              $tienePresentacion,
            "OpcContable" =>                    $opcContable,
            "PrecioApp" =>                      $PrecioApp,
            "OfertaWeb" => $ofertaWeb,
            "PrecioWeb" => $precioWeb,
            "PrecioAnterior" => $precioAnterior,
            "FechaOfertaInicio" =>              $fechaOfertaInicio,
            "FechaOfertaFin" =>                 $fechaOfertaFin,
        ))
            ->table('Gen_Producto')
            ->where('IdProducto', '=', $idProducto);
        $affectedRows = $update->execute();
        // Actualizar ProductoDet
        $sql = "DELETE FROM Gen_ProductoDet WHERE IdProducto='$idProducto'";
        $stmt = $this->db->prepare($sql);
        $deleted = $stmt->execute();

        if ($productosDet && $esPadre) {
            foreach ($productosDet as $prod) {
                $insertDet = $this->db->insert(array('IdProducto', 'IdProductoDet', 'Cantidad', 'Precio'))
                    ->into('Gen_ProductoDet')
                    ->values(array($idProducto, $prod['IdProductoDet'], $prod['Cantidad'], $prod['Precio']));
                $insertDetId = $insertDet->execute();
            }
        }
        foreach ($productoSerie as $serie) {
            if ($serie['IdProductoSerie'] == 0) {
                $insertDet = $this->db->insert(
                    array(
                        'IdProducto',
                        'Serie',
                        'Estado',
                        'FechaReg',
                        'UsuarioReg',
                        'FechaMod',
                        'UsuarioMod',

                    )
                )
                    ->into('Gen_Producto_Serie')
                    ->values(
                        array(
                            $idProducto,
                            $serie['Serie'],
                            1,
                            $fechaReg,
                            $usuarioReg,
                            $fechaReg,
                            $usuarioReg
                        )
                    );
                $insertDet->execute();
            } else {
                $IdProductoSerie = $serie['IdProductoSerie'];
                if ($serie['Estado'] == 0) {
                    $sql = "DELETE FROM Gen_Producto_Serie WHERE IdProductoSerie =$IdProductoSerie";
                    $stmt = $this->db->prepare($sql);
                    $deleted = $stmt->execute();
                } else {
                    $update = $this->db->update(
                        array(
                            'Serie' => $serie['Serie'],
                            'Estado' => $serie['Estado'],
                            'FechaMod' => $fechaReg,
                            'UsuarioMod' => $usuarioReg

                        )
                    )
                        ->table('Gen_Producto_Serie ')
                        ->where('IdProductoSerie', '=', $serie['IdProductoSerie']);
                    $update->execute();
                }
            }
        }
        return $response->withJson(array("insertId" => $idProducto));

        // return $response->withJson(array("affectedRows" => $productosDet));



    }
    // Fin actualizacion producto

    // Inicio verificar Producto
    $select = "SELECT * FROM Gen_Producto
        WHERE Producto = '" . $producto . "'";

    if ($codigoBarra) {
        $select .= " OR CodigoBarra = '" . $codigoBarra . "'";
    }

    $stmt = $this->db->query($select);
    $prod = $stmt->fetch();
    $stmt->execute();

    //return $response->withJson(array('select' => empty($prod)));

    if (!empty($prod)) {
        $data = array(
            'error' => 'Producto ya existe',
            'codigoBarra' => $prod['CodigoBarra'],
            'insertId' => $prod['IdProducto']
        );
        return $response->withJson($data);
    }
    // Final verificar Movimiento
    if (!empty($idOfertaCategoria) && $posOferta > 0 && $posOferta < 10000) {
        $posOferta = reservarPosicionOfertaPedi2($this->db, 0, $posOferta);
    }

    $insert = $this->db
        ->insert(
            array(
                'IdProductoMarca',
                'IdProductoFormaFarmaceutica',
                'IdProductoMedicion',
                'IdProductoCategoria',
                'Producto',
                'ProductoDesc',
                'ProductoDesc2',
                'ProductoDesc3',
                'FechaReg',
                'Hash',
                'ControlaStock',
                'PorcentajeUtilidad',
                'Genero',
                'Color',
                'Botapie',
                'Anulado',
                'ProductoModelo',
                'ProductoPresentacion',
                'EsPadre',
                'PrecioPorMayor',
                "ControlaStockMinimo",
                'StockMinimo',
                'CodigoBarra',
                'PrecioCosto',
                'PrecioContado',
                'EsHabitacion',
                'Piso',
                'PreciosPorProducto',
                'IdOfertaCategoria',
                'MostrarApp',
                'PosOferta',
                "PorcentajeLiquidacion",
                "TienePresentacion",
                'OpcContable',
                "PrecioApp",
                "OfertaWeb",
                "PrecioWeb",
                "PrecioAnterior",
                "FechaOfertaInicio",
                "FechaOfertaFin",
            )
        )
        ->into('Gen_Producto')
        ->values(
            array(
                $idProductoMarca,
                $idProductoFormaFarmaceutica,
                $idProductoMedicion,
                $idProductoCategoria,
                $producto,
                $productoDesc,
                $productoDesc2,
                $productoDesc3,
                $fechaReg,
                $hash,
                $controlaStock,
                $porcentajeUtilidad,
                $genero,
                $color,
                $botapie,
                $anulado,
                $productoModelo,
                $productoPresentacion,
                $esPadre,
                $precioPorMayor,
                $controlaStockMinimo,
                $stockMinimo,
                $codigoBarra,
                $precioCosto,
                $precioContado,
                $esHabitacion,
                $piso,
                $preciosPorProducto,
                $idOfertaCategoria,
                $mostrarApp,
                $posOferta,
                $porcentajeLiquidacion,
                $tienePresentacion,
                $opcContable,
                $PrecioApp,
                $ofertaWeb,
                $precioWeb,
                $precioAnterior,
                $fechaOfertaInicio,
                $fechaOfertaFin,
            )
        );

    $insertId = $insert->execute();

    // Generando codigo de barras  // actualizar el nombre para que sea unico
    /* $codigoBarra = substr($categoria, 0, 2) . $insertId . substr($producto, 0, 2);

    $update = $this->db->update(array("CodigoBarra" => $codigoBarra, "Producto" => $producto . '-' . $codigoBarra))
                       ->table('Gen_Producto')
                       ->where('IdProducto', '=', $insertId);
    $affectedRows = $update->execute(); */
    $codigoBarra = $insertId;

    $update = $this->db->update(array("CodigoBarra" => $codigoBarra))
        ->table('Gen_Producto')
        ->where('IdProducto', '=', $insertId);
    $affectedRows = $update->execute();

    // Insertar productoDet
    if ($productosDet) {
        foreach ($productosDet as $prod) {
            $insertDet = $this->db->insert(array('IdProducto', 'IdProductoDet', 'Cantidad', 'Precio'))
                ->into('Gen_ProductoDet')
                ->values(array($insertId, $prod['IdProducto'], $prod['Cantidad'], $prod['Precio']));
            $insertDetId = $insertDet->execute();
        }
    }

    foreach ($productoSerie as $serie) {
        $insertDet = $this->db->insert(
            array(
                'IdProducto',
                'Serie',
                'Estado',
                'FechaReg',
                'UsuarioReg',
                'FechaMod',
                'UsuarioMod',
            )
        )
            ->into('Gen_Producto_Serie ')
            ->values(
                array(
                    $insertId,
                    $serie['Serie'],
                    1,
                    $fechaReg,
                    $usuarioReg,
                    $fechaReg,
                    $usuarioReg
                )
            );
        $insertDet->execute();
    }

    return $response->withJson(array("insertId" => $insertId));
});





require __DIR__ . '/product-images-routes.php';
require __DIR__ . '/product-suppliers-routes.php';
require __DIR__ . '/movement-control.php';

$app->get('/movimiento/productos', function (Request $request, Response $response, array $args) {
    $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
    $hashMovimiento = $request->getParam('hash');

    $select = "SELECT DATE_FORMAT(Lo_Movimiento.FechaReg, '%d%m%Y') as Fecha, Lo_MovimientoDetalle.IdProducto, Gen_Producto.Producto, Gen_ProductoMarca.ProductoMarca, Gen_Producto.ProductoModelo,
        Gen_Producto.Color, Gen_Producto.CodigoBarra, Gen_Producto.PrecioContado, Lo_MovimientoDetalle.Cantidad,
        Gen_Producto.Botapie
        FROM Lo_MovimientoDetalle
        INNER JOIN Lo_Movimiento ON Lo_MovimientoDetalle.hashMovimiento = Lo_Movimiento.Hash
        INNER JOIN Gen_Producto ON Lo_MovimientoDetalle.IdProducto = Gen_Producto.IdProducto
        INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
        WHERE hashMovimiento = '$hashMovimiento'";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});




$app->get('/movimientos/tipos', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Lo_MovimientoTipo WHERE Anulado=0";
    $select .= " AND TipoMovimiento LIKE '%" . $request->getParam('q') . "%' ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->post('/movimientos', function (Request $request, Response $response) {
    try {
        //code...
        //return  $response->withJson($request->getParam('productos'));exit();
        // start verificar Movimiento
        $select = "SELECT * FROM Lo_Movimiento
	WHERE IdMovimientoTipo = '" . $request->getParam('movimiento')['movimientoTipo']['IdMovimientoTipo']
            . "' AND IdProveedor = '" . $request->getParam('movimiento')['proveedor']['IdProveedor']
            . "' AND Serie = '" . $request->getParam('movimiento')['Serie']
            . "' AND Numero = '" . $request->getParam('movimiento')['Numero'] . "'";

        $stmt = $this->db->query($select);
        $stmt->execute();

        if (count($stmt->fetchAll())) {
            $data = array(
                "error" => 'Error: Movimiento duplicado'
            );
            return $response->withJson($data);
        }
        // end verificar Movimiento

        // start Insertar Movimiento
        $idMovimientoTipo = $request->getParam('movimiento')['movimientoTipo']['IdMovimientoTipo'];
        $idProveedor = $request->getParam('movimiento')['proveedor']['IdProveedor'];
        $IdOrdenCompra = $request->getParam('ordenCompra')['IdOrdenCompra'] ? $request->getParam('ordenCompra')['IdOrdenCompra'] : false;
        $serie = $request->getParam('movimiento')['Serie'];
        $numero = $request->getParam('movimiento')['Numero'];
        //$movimientoFecha = $request->getParam('movimiento')['MovimientoFecha'];
        $movimientoFecha = getNow();
        $almacenOrigen = $request->getParam('movimiento')['almacenOrigen']['IdAlmacen'];
        $almacenDestino = $request->getParam('movimiento')['almacenDestino']['IdAlmacen'];
        $anulado = 0;
        $fechaReg = getNow();
        $user = $request->getParam('movimiento')['user'];
        $usuarioReg = 'xx';
        if (isset($_SESSION['user'])) {
            $usuarioReg = $_SESSION['user'];
        }
        $usuarioReg = $user ? $user : $usuarioReg;
        $hash = time();
        //$fechaStock = $request->getParam('movimiento')['FechaStock'];
        $fechaStock = getNow();
        $percepcion = 0;
        $esCredito = $request->getParam('movimiento')['EsCredito'];
        $fechaVenCredito = $request->getParam('movimiento')['FechaVenCredito'] ? $request->getParam('movimiento')['FechaVenCredito'] : NULL;
        $fechaPeriodoTributario = $request->getParam('movimiento')['FechaPeriodoTributario'];
        $tipoCambio = $request->getParam('movimiento')['TipoCambio'] ? $request->getParam('movimiento')['TipoCambio'] : 1;
        $moneda = $request->getParam('movimiento')['Moneda'];
        if ($IdOrdenCompra) {
            $update = "UPDATE Lo_OrdenCompra SET Estado = 2 where IdOrdenCompra = $IdOrdenCompra";
            $stmt = $this->db->prepare($update);
            $updated = $stmt->execute();
        }

        if ($moneda === 'USD') {
            $update = "UPDATE Gen_Moneda SET TipoCambio=$tipoCambio  WHERE Moneda = 'USD'";
            $stmt = $this->db->prepare($update);
            $stmt->execute();
        }

        $insert = $this->db->insert(array(
            'IdMovimientoTipo',
            'IdProveedor',
            'Serie',
            'Numero',
            'MovimientoFecha',
            'IdAlmacenOrigen',
            'IdAlmacenDestino',
            'Anulado',
            'FechaReg',
            'UsuarioReg',
            'Hash',
            'FechaStock',
            'Percepcion',
            'EsCredito',
            'FechaVenCredito',
            'FechaPeriodoTributario',
            'TipoCambio',
            'Moneda'
        ))
            ->into('Lo_Movimiento')
            ->values(array(
                $idMovimientoTipo,
                $idProveedor,
                $serie,
                $numero,
                $movimientoFecha,
                $almacenOrigen,
                $almacenDestino,
                $anulado,
                $fechaReg,
                $usuarioReg,
                $hash,
                $fechaStock,
                $percepcion,
                $esCredito,
                $fechaVenCredito,
                $fechaPeriodoTributario,
                $tipoCambio,
                $moneda
            ));

        $insertId = $insert->execute();

        $data = array(
            "hash" => $hash
        );
        // end insertar Movimiento

        if ($moneda == 'USD') {
            $update = "UPDATE Gen_Moneda SET TipoCambio=$tipoCambio WHERE Moneda = 'USD'";
            $stmt = $this->db->prepare($update);
            $updated = $stmt->execute();
        }



        // start Movimiento Detalle
        $productos = $request->getParam('productos');
        foreach ($productos as $producto) {
            //if ($producto['total'] > 0) {
            $series =    $producto['Serie'];
            $idProducto = $producto['IdProducto'];
            $cantidad = $producto['cantidad'];
            $tieneIgv = isset($producto['TieneIgv']) ? $producto['TieneIgv'] : 0;
            $precio = $producto['precio'];
            $idLote = isset($producto['IdLote']) ? $producto['IdLote'] : 0;
            $descripcion = isset($producto['Descripcion']) ? $producto['Descripcion'] : '';
            // $TotalFlete = isset($producto['FLETE']) ? $producto['FLETE'] : 0;
            $flete = $producto['FLETE'] ? $producto['FLETE'] : 0;
            $nuevoPrecioContado = $producto['nuevoPrecioContado'] + $flete;
            if ($tieneIgv === 1) {
                $precio += $precio * 0.18;
            }

            $insert = $this->db->insert(array(
                'hashMovimiento',
                'IdProducto',
                'Cantidad',
                'TieneIgv',
                'Precio',
                'IdLote',
                'Descripcion',
                'FLETE'
            ))
                ->into('Lo_MovimientoDetalle')
                ->values(array(
                    $hash,
                    $idProducto,
                    $cantidad,
                    $tieneIgv,
                    $precio,
                    $idLote,
                    $descripcion,
                    $flete
                ));
            $insert->execute();

            // start actualizar precioventa producto
            $alterarProductos = $request->getParam('movimiento')['alterarProductos'];
            if ($alterarProductos) {

                // if ($request->getParam('movimiento')['Moneda'] === 'USD') {
                //     $select = "SELECT * FROM Gen_Moneda WHERE Moneda='USD'";
                //     $stmt = $this->db->query($select);
                //     $stmt->execute();
                //     $moneda = $stmt->fetch();

                //     $precio *= $moneda['TipoCambio'];
                //     $nuevoPrecioContado *= $moneda['TipoCambio'];
                // }
                $PrecioCostoDolar =  $precio;

                $precio *= $tipoCambio;
                $porcentajeUtilidad = $producto['porcentajeUtilidad'];

                $porcentajeUtilidad =  round(((($nuevoPrecioContado - $precio) / $precio) * 100), 2);
                $porcentajeUtilidad = $porcentajeUtilidad ? $porcentajeUtilidad : 0;
                $PrecioContadoDolar = $PrecioCostoDolar + $PrecioCostoDolar * $porcentajeUtilidad / 100;

                $update = $this->db->update(array(
                    "PrecioCosto" => $precio,
                    "PrecioContado" => $nuevoPrecioContado,
                    "PorcentajeUtilidad" => $porcentajeUtilidad,
                    "PrecioCostoDolar" => $PrecioCostoDolar,
                    "PrecioContadoDolar" => $PrecioContadoDolar,
                    "TipoCambio" => $PrecioContadoDolar,
                ))
                    ->table('Gen_Producto')
                    ->where('IdProducto', '=', $idProducto);
                $update->execute();
            }
            // end actualizar precioventa producto
            //}
            foreach ($series as $serie) {
                $insertDet = $this->db->insert(
                    [
                        'IdProducto',
                        'Serie',
                        'IdMovimiento',
                        'Estado',
                        'FechaReg',
                        'UsuarioReg',
                        'FechaMod',
                        'UsuarioMod'
                    ]
                )
                    ->into('Gen_Producto_Serie')
                    ->values([
                        $idProducto,
                        $serie['Serie'],
                        $hash,
                        1,
                        $fechaReg,
                        $usuarioReg,
                        $fechaReg,
                        $usuarioReg
                    ]);
                $insertDet->execute();
            }
        }
        // end Movimiento Detalle


        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson($e->getMessage());
        //throw $th;
    }
});

$app->get('/movimientos/granTotal', function (Request $request, Response $response) {

    $IdPreOrden = $request->getParam('idPreOrden');

    // $select = "SELECT IdPreOrden, SUM(Cantidad * Precio) AS granTotal FROM Ve_PreOrdenDet WHERE IdPreOrden = $IdPreOrden";
    $select = "SELECT IdPreOrden, SUM(Cantidad * Precio) AS granTotal FROM Ve_PreOrdenDet WHERE IdPreOrden = $IdPreOrden";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});



$app->get('/monedas', function (Request $request, Response $response, array $args) {
    $select = $this->db->select()->from('Gen_Moneda')->whereLike('Moneda', '%' . $request->getParam('q') . '%');
    $stmt = $select->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/almacenes/id/{id}', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Lo_Almacen WHERE Anulado=0 AND IdAlmacen=$args[id]";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/almacenes', function (Request $request, Response $response, array $args) {
    // $select = $this->db->select()->from('Lo_Almacen');
    // $stmt = $select->execute();
    // $data = $stmt->fetchAll();

    // return $response->withJson($data);


    $select = "SELECT * FROM Lo_Almacen WHERE Anulado=0";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->get('/almacenes/primero', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Lo_Almacen WHERE Anulado=0 ORDER BY IdAlmacen ASC LIMIT 1 ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/puntoventa/id/{id}', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Ve_DocVentaPuntoVenta WHERE Anulado=0 AND IdDocVentaPuntoVenta=$args[id]";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/puntoventa/primero', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Ve_DocVentaPuntoVenta WHERE Anulado=0 ORDER BY IdDocVentaPuntoVenta ASC LIMIT 1 ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});


$app->get('/consultarRUC', function (Request $request, Response $response, array $args) use ($app) {
    $res = $app->subRequest('GET', 'empresa/id/1');
    $empresa = (string) $res->getBody();
    $empresa = json_decode($empresa, true);
    if ($_GET['type'] == "RUC") {
        $ch = curl_init($empresa['CONSULTARUCURL'] . 'ruc/' . $_GET['numero'] . '?token=' . $empresa['CONSULTARUCTOKEN']);
    } else {
        $ch = curl_init($empresa['CONSULTARUCURL'] . 'dni/' . $_GET['numero'] . '?token=' . $empresa['CONSULTARUCTOKEN']);
    }

    $headers = array(
        "Content-Type: application/json; charset=UTF-8",
        "Cache-Control: no-cache",
        "Pragma: no-cache"
    );
    //var_dump("https://www.facturacionelectronica.us/facturacion/controller/ws_consulta_rucdni_v2.php?usuario=20573027125&password=5i573m45&documento=" . $_GET['type'] . "&nro_documento=" . $_GET['numero']);
    // $ch = curl_init("https://www.facturacionelectronica.us/facturacion/controller/ws_consulta_rucdni_v2.php?usuario=20573027125&password=5i573m45&documento=" . $_GET['type'] . "&nro_documento=" . $_GET['numero']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    //quitar en produccion
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    //curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    //curl_setopt($ch, CURLOPT_USERPWD, "PRUEBA:LOG");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    // Se cierra el recurso CURL y se liberan los recursos del sistema
    curl_close($ch);
    if (!$response) {
        return false;
    } else {
        header('Content-Type: application/json');
        echo $response;
    }
});


function stringIngresoUndProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT Lo_Movimiento.MovimientoFecha AS Fecha,  Lo_MovimientoTipo.CodSunat, Lo_MovimientoTipo.TipoMovSunat as TipoDocSunat,
     Lo_MovimientoTipo.TipoMovimiento AS Detalle, Lo_Movimiento.Serie, Lo_Movimiento.Numero, Lo_Proveedor.Proveedor AS Nombres,
    Gen_Moneda.Simbolo AS Moneda,
    Lo_MovimientoDetalle.Cantidad AS IngresoCantidad, Lo_MovimientoDetalle.Precio AS IngresoPrecio,
    '0' AS SalidaCantidad, '0' AS SalidaPrecio,
    '0' AS Descuento, Lo_Movimiento.UsuarioReg FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        LEFT JOIN Lo_Proveedor ON Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
        LEFT JOIN Gen_Moneda ON Lo_Movimiento.Moneda = Gen_Moneda.Moneda
        WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
        AND Lo_MovimientoDetalle.IdProducto=$idProducto AND Lo_Movimiento.Anulado=0 ";
    $select .= $fechaDesde ? " AND Lo_Movimiento.MovimientoFecha BETWEEN '$fechaDesde' AND '$fechaHasta' )" : " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta' )";

    return $select;
}
function stringIngresoCajaProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT Lo_Movimiento.MovimientoFecha AS Fecha, Lo_MovimientoTipo.CodSunat, Lo_MovimientoTipo.TipoMovSunat as TipoDocSunat,
    Lo_MovimientoTipo.TipoMovimiento AS Detalle, Lo_Movimiento.Serie, Lo_Movimiento.Numero, Lo_Proveedor.Proveedor AS Nombres, 
    Gen_Moneda.Simbolo AS Moneda,
    (Lo_MovimientoDetalle.Cantidad * Gen_ProductoDet.Cantidad) AS IngresoCantidad, Lo_MovimientoDetalle.Precio AS IngresoPrecio, 
    '0' AS SalidaCantidad, '0' AS SalidaPrecio, 
    '0' AS Descuento, Lo_Movimiento.UsuarioReg FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        INNER JOIN Gen_ProductoDet ON Lo_MovimientoDetalle.IdProducto = Gen_ProductoDet.IdProducto
        LEFT JOIN Lo_Proveedor ON Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
        LEFT JOIN Gen_Moneda ON Lo_Movimiento.Moneda = Gen_Moneda.Moneda
        WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
        AND Gen_ProductoDet.IdProductoDet=$idProducto AND Lo_Movimiento.Anulado=0 ";
    $select .= $fechaDesde ? " AND Lo_Movimiento.MovimientoFecha BETWEEN '$fechaDesde' AND '$fechaHasta' )" : " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta' )";

    return $select;
}
function stringSalidaUndProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT Lo_Movimiento.MovimientoFecha AS Fecha, Lo_MovimientoTipo.CodSunat, Lo_MovimientoTipo.TipoMovSunat as TipoDocSunat,
    Lo_MovimientoTipo.TipoMovimiento AS Detalle, Lo_Movimiento.Serie, Lo_Movimiento.Numero, Lo_Proveedor.Proveedor AS Nombres,
    'S/' AS Moneda,
    '0' AS IngresoCantidad, '0' AS IngresoPrecio,
    (CASE
        WHEN Lo_MovimientoTipo.CodSunat = '07' AND Lo_Movimiento.NotaIdMotivo NOT IN ('01', '02', '06', '07') THEN '0'
        ELSE Lo_MovimientoDetalle.Cantidad
    END) AS SalidaCantidad, 
    (CASE
        WHEN Lo_MovimientoTipo.CodSunat = '07' AND Lo_Movimiento.NotaIdMotivo NOT IN ('01', '02', '06', '07') THEN '0'
        ELSE Lo_MovimientoDetalle.Precio
    END) AS SalidaPrecio,
    '0' AS Descuento, Lo_Movimiento.UsuarioReg FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        LEFT JOIN Lo_Proveedor ON Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
        WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_Movimiento.IdAlmacenOrigen = $idAlmacen
        AND Lo_MovimientoDetalle.IdProducto=$idProducto AND Lo_Movimiento.Anulado=0 ";
    $select .= $fechaDesde ? " AND Lo_Movimiento.MovimientoFecha BETWEEN '$fechaDesde' AND '$fechaHasta' )" : " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta' )";

    return $select;
}
function stringSalidaCajaProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT Lo_Movimiento.MovimientoFecha AS Fecha, Lo_MovimientoTipo.CodSunat, Lo_MovimientoTipo.TipoMovSunat as TipoDocSunat,
    Lo_MovimientoTipo.TipoMovimiento AS Detalle, Lo_Movimiento.Serie, Lo_Movimiento.Numero, Lo_Proveedor.Proveedor AS Nombres,
    'S/' AS Moneda, 
    '0' AS IngresoCantidad, '0' AS IngresoPrecio, 
    (CASE
        WHEN Lo_MovimientoTipo.CodSunat = '07' AND Lo_Movimiento.NotaIdMotivo NOT IN ('01', '02', '06', '07') THEN '0' 
        ELSE (Lo_MovimientoDetalle.Cantidad * Gen_ProductoDet.Cantidad) 
    END) AS SalidaCantidad, 
    (CASE
        WHEN Lo_MovimientoTipo.CodSunat = '07' AND Lo_Movimiento.NotaIdMotivo NOT IN ('01', '02', '06', '07') THEN '0' 
        ELSE Lo_MovimientoDetalle.Precio 
    END) AS SalidaPrecio, 
    '0' AS Descuento, Lo_Movimiento.UsuarioReg FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        INNER JOIN Gen_ProductoDet ON Lo_MovimientoDetalle.IdProducto = Gen_ProductoDet.IdProducto
        LEFT JOIN Lo_Proveedor ON Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
        WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_Movimiento.IdAlmacenOrigen = $idAlmacen
        AND Gen_ProductoDet.IdProductoDet=$idProducto AND Lo_Movimiento.Anulado=0 ";
    $select .= $fechaDesde ? " AND Lo_Movimiento.MovimientoFecha BETWEEN '$fechaDesde' AND '$fechaHasta' )" : " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta' )";

    return $select;
}
function stringSalidaVentaUndProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT Ve_DocVenta.FechaDoc AS Fecha, Ve_DocVentaTipoDoc.CodSunat, Ve_DocVentaTipoDoc.TipoDocSunat,
    CONCAT('VENTA - ', Ve_DocVentaTipoDoc.TipoDoc) AS Detalle, Ve_DocVenta.Serie, Ve_DocVenta.Numero,
    Ve_DocVentaCliente.Cliente AS Nombres,
    'S/' AS Moneda,
    (CASE
        WHEN Ve_DocVentaTipoDoc.CodSunat = '07' AND NotaIdMotivo IN ('01', '02', '06', '07') THEN Ve_DocVentaDet.Cantidad
        ELSE '0'
    END) AS IngresoCantidad, 
    (CASE
        WHEN Ve_DocVentaTipoDoc.CodSunat = '07' AND NotaIdMotivo IN ('01', '02', '06', '07') THEN Ve_DocVentaDet.Precio
        ELSE '0'
    END) AS IngresoPrecio, 
    (CASE
        WHEN Ve_DocVentaTipoDoc.CodSunat = '07' THEN '0'
        ELSE Ve_DocVentaDet.Cantidad
    END) AS SalidaCantidad, 
    (CASE
        WHEN Ve_DocVentaTipoDoc.CodSunat = '07' THEN '0'
        ELSE Ve_DocVentaDet.Precio
    END) AS SalidaPrecio, 
    Ve_DocVentaDet.Descuento, Ve_DocVenta.UsuarioReg FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        LEFT JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente= Ve_DocVentaCliente.IdCliente
        WHERE Ve_DocVenta.IdAlmacen = $idAlmacen
            AND Ve_DocVentaDet.IdProducto = $idProducto
            AND Ve_DocVenta.Anulado = 0
            AND Ve_DocVentaTipoDoc.VaRegVenta = 1 ";
    $select .= $fechaDesde ? " AND Ve_DocVenta.FechaDoc BETWEEN '$fechaDesde' AND '$fechaHasta' ) " : " AND Ve_DocVenta.FechaDoc < '$fechaHasta' ) ";


    return $select;
}

function stringSalidaVentaCajaProducto($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT Ve_DocVenta.FechaDoc AS Fecha, Ve_DocVentaTipoDoc.CodSunat, Ve_DocVentaTipoDoc.TipoDocSunat,
    CONCAT('VENTA - ', Ve_DocVentaTipoDoc.TipoDoc) AS Detalle, Ve_DocVenta.Serie, Ve_DocVenta.Numero, 
    Ve_DocVentaCliente.Cliente AS Nombres, 
    'S/' AS Moneda,
    (CASE
    WHEN Ve_DocVentaTipoDoc.CodSunat = '07' AND NotaIdMotivo IN ('01', '02', '06', '07') THEN (Gen_ProductoDet.cantidad * Ve_DocVentaDet.Cantidad)
    ELSE '0'
    END) AS IngresoCantidad, 
    (CASE
        WHEN Ve_DocVentaTipoDoc.CodSunat = '07' AND NotaIdMotivo IN ('01', '02', '06', '07') THEN Ve_DocVentaDet.Precio
        ELSE '0'
    END) AS IngresoPrecio, 
    (CASE
        WHEN Ve_DocVentaTipoDoc.CodSunat = '07' THEN '0'
        ELSE (Gen_ProductoDet.cantidad * Ve_DocVentaDet.Cantidad)
    END) AS SalidaCantidad, 
    (CASE
        WHEN Ve_DocVentaTipoDoc.CodSunat = '07' THEN '0'
        ELSE Ve_DocVentaDet.Precio
    END) AS SalidaPrecio,  
    Ve_DocVentaDet.Descuento, Ve_DocVenta.UsuarioReg FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        LEFT JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente= Ve_DocVentaCliente.IdCliente
        INNER JOIN Gen_ProductoDet ON Ve_DocVentaDet.IdProducto=Gen_ProductoDet.IdProducto
        WHERE Ve_DocVenta.IdAlmacen = $idAlmacen
            AND Gen_ProductoDet.IdProductoDet = $idProducto 
            AND Ve_DocVenta.Anulado = 0
            AND Ve_DocVentaTipoDoc.VaRegVenta = 1 ";
    $select .= $fechaDesde ? " AND Ve_DocVenta.FechaDoc BETWEEN '$fechaDesde' AND '$fechaHasta' ) " : " AND Ve_DocVenta.FechaDoc < '$fechaHasta' ) ";

    return $select;
}




function stringIngresoUnd($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT IFNULL(SUM(Lo_MovimientoDetalle.Cantidad), 0) AS cantidad FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
            AND Lo_MovimientoDetalle.IdProducto=$idProducto AND Lo_Movimiento.Anulado=0
            AND Lo_Movimiento.MovimientoFecha < '$fechaHasta')";

    if (!$idProducto) {
        $select = "(SELECT Lo_MovimientoDetalle.IdProducto, IFNULL(SUM(Lo_MovimientoDetalle.Cantidad), 0) AS cantidad FROM Lo_Movimiento
            INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
            INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
            WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
            AND Lo_Movimiento.Anulado=0 ";
        $select .= $fechaDesde ? " AND Lo_Movimiento.MovimientoFecha BETWEEN '$fechaDesde' AND '$fechaHasta' " : " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta' ";
        $select .= " GROUP BY Lo_MovimientoDetalle.IdProducto)";
    }

    return $select;
}
function stringIngresoCaja($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT IFNULL(SUM(Lo_MovimientoDetalle.Cantidad), 0) AS cantidad FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        INNER JOIN Gen_ProductoDet ON Lo_MovimientoDetalle.IdProducto = Gen_ProductoDet.IdProducto
        WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
            AND Gen_ProductoDet.IdProductoDet=$idProducto AND Lo_Movimiento.Anulado=0
            AND Lo_Movimiento.MovimientoFecha < '$fechaHasta')";

    if (!$idProducto) {
        $select = "(SELECT Gen_ProductoDet.IdProductoDet AS IdProducto, IFNULL(SUM(Lo_MovimientoDetalle.Cantidad * Gen_ProductoDet.Cantidad), 0) AS cantidad
            FROM Lo_Movimiento
            INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
            INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
            INNER JOIN Gen_ProductoDet ON Lo_MovimientoDetalle.IdProducto = Gen_ProductoDet.IdProducto
            WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
            AND Lo_Movimiento.Anulado=0 ";
        $select .= $fechaDesde ? " AND Lo_Movimiento.MovimientoFecha BETWEEN '$fechaDesde' AND '$fechaHasta' " : " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta' ";
        $select .= " GROUP BY Gen_ProductoDet.IdProductoDet)";
    }
    // print_r($select);exit();
    return $select;
}

function stringSalidaCaja($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT IFNULL(SUM(Lo_MovimientoDetalle.Cantidad), 0) AS cantidad FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        INNER JOIN Gen_ProductoDet ON Lo_MovimientoDetalle.IdProducto = Gen_ProductoDet.IdProducto
        WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_Movimiento.IdAlmacenOrigen = $idAlmacen
            AND Gen_ProductoDet.IdProductoDet=$idProducto AND Lo_Movimiento.Anulado=0
            AND Lo_Movimiento.MovimientoFecha < '$fechaHasta')";

    if (!$idProducto) {
        $select = "(SELECT Gen_ProductoDet.IdProductoDet AS IdProducto, IFNULL(SUM(IF(Lo_MovimientoTipo.CodSunat = '07', IF(Lo_Movimiento.NotaIdMotivo IN ('01', '02', '06', '07'),Gen_ProductoDet.cantidad * Lo_MovimientoDetalle.Cantidad,0), Gen_ProductoDet.cantidad * Lo_MovimientoDetalle.Cantidad)), 0) AS cantidad
            FROM Lo_Movimiento
            INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
            INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
            INNER JOIN Gen_ProductoDet ON Lo_MovimientoDetalle.IdProducto = Gen_ProductoDet.IdProducto
            WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_Movimiento.IdAlmacenOrigen = $idAlmacen
            AND Lo_Movimiento.Anulado=0 ";
        $select .= $fechaDesde ? " AND Lo_Movimiento.MovimientoFecha BETWEEN '$fechaDesde' AND '$fechaHasta'" : " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta'";
        $select .= " GROUP BY Gen_ProductoDet.IdProductoDet)";
    }
    // print_r($select);exit();
    return $select;
}

function stringSalidaUnd($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT IFNULL(SUM(Lo_MovimientoDetalle.Cantidad), 0) AS cantidad FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_Movimiento.IdAlmacenOrigen = $idAlmacen
            AND Lo_MovimientoDetalle.IdProducto=$idProducto AND Lo_Movimiento.Anulado=0
            AND Lo_Movimiento.MovimientoFecha < '$fechaHasta')";

    if (!$idProducto) {
        $select = "(SELECT Lo_MovimientoDetalle.IdProducto, IFNULL(SUM(IF(Lo_MovimientoTipo.CodSunat = '07', IF(Lo_Movimiento.NotaIdMotivo IN ('01', '02', '06', '07'),Lo_MovimientoDetalle.Cantidad,0) ,Lo_MovimientoDetalle.Cantidad)), 0) AS cantidad FROM Lo_Movimiento
            INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
            INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
            WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_Movimiento.IdAlmacenOrigen = $idAlmacen
            AND Lo_Movimiento.Anulado=0 ";
        $select .= $fechaDesde ? " AND Lo_Movimiento.MovimientoFecha BETWEEN '$fechaDesde' AND '$fechaHasta'" : " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta' ";
        $select .= " GROUP BY Lo_MovimientoDetalle.IdProducto)";
    }

    return $select;
}

function stringSalidaVentaUnd($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT IFNULL(SUM(Ve_DocVentaDet.Cantidad), 0) AS cantidad FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        WHERE Ve_DocVenta.IdAlmacen = $idAlmacen
            AND Ve_DocVentaDet.IdProducto = $idProducto
            AND Ve_DocVenta.Anulado = 0
            AND Ve_DocVentaTipoDoc.VaRegVenta = 1
            AND Ve_DocVenta.FechaDoc < '$fechaHasta')";

    if (!$idProducto) {
        $select = "(SELECT Ve_DocVentaDet.IdProducto,IFNULL(SUM(IF(Ve_DocVentaTipoDoc.CodSunat = '07', IF(Ve_DocVenta.NotaIdMotivo IN ('01', '02', '06', '07'),-1*Ve_DocVentaDet.Cantidad,0),Ve_DocVentaDet.Cantidad)), 0) AS cantidad FROM Ve_DocVenta
            INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
            INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
            WHERE Ve_DocVenta.IdAlmacen = $idAlmacen
                AND Ve_DocVenta.Anulado = 0
                AND Ve_DocVentaTipoDoc.VaRegVenta = 1 ";
        $select .= $fechaDesde ? " AND Ve_DocVenta.FechaDoc BETWEEN '$fechaDesde' AND '$fechaHasta' " : " AND Ve_DocVenta.FechaDoc < '$fechaHasta' ";
        $select .= " GROUP BY Ve_DocVentaDet.IdProducto)";
    }
    // print_r($select);exit();
    return $select;
}

function stringSalidaVentaCaja($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    $select = "(SELECT IFNULL(SUM(Gen_ProductoDet.cantidad * Ve_DocVentaDet.Cantidad), 0) AS cantidad FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        INNER JOIN Gen_ProductoDet ON Ve_DocVentaDet.IdProducto=Gen_ProductoDet.IdProducto
        WHERE Ve_DocVenta.IdAlmacen = $idAlmacen
            AND Gen_ProductoDet.IdProductoDet = $idProducto
            AND Ve_DocVenta.Anulado = 0
            AND Ve_DocVentaTipoDoc.VaRegVenta = 1
            AND Ve_DocVenta.FechaDoc < '$fechaHasta')";

    if (!$idProducto) {
        $select = "(SELECT Gen_ProductoDet.IdProductoDet AS IdProducto, IFNULL(SUM(IF(Ve_DocVentaTipoDoc.CodSunat = '07', IF(Ve_DocVenta.NotaIdMotivo IN ('01', '02', '06', '07'),-1*Gen_ProductoDet.cantidad * Ve_DocVentaDet.Cantidad,0), Gen_ProductoDet.cantidad * Ve_DocVentaDet.Cantidad)), 0) AS cantidad
            FROM Ve_DocVenta
            INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
            INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
            INNER JOIN Gen_ProductoDet ON Ve_DocVentaDet.IdProducto=Gen_ProductoDet.IdProducto
            WHERE Ve_DocVenta.IdAlmacen = $idAlmacen
                AND Ve_DocVenta.Anulado = 0
                AND Ve_DocVentaTipoDoc.VaRegVenta = 1 ";
        $select .= $fechaDesde ? " AND Ve_DocVenta.FechaDoc BETWEEN '$fechaDesde' AND '$fechaHasta' " : " AND Ve_DocVenta.FechaDoc < '$fechaHasta' ";
        $select .= " GROUP BY Gen_ProductoDet.IdProductoDet)";
    }
    //print_r($select);exit();
    return $select;
}

function stringProveedorUltimo($idProducto, $idAlmacen, $fechaHasta)
{
    $select = "(SELECT Lo_MovimientoDetalle.IdProducto,Lo_Proveedor.IdProveedor,Lo_Proveedor.Proveedor,Lo_Proveedor.Telefono,MAX(Lo_Movimiento.MovimientoFecha) AS UltimaFecha FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle ON  Lo_Movimiento.Hash = Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_Proveedor ON Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
        WHERE Lo_MovimientoDetalle.IdProducto = $idProducto 
        AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen 
        AND Lo_Movimiento.MovimientoFecha < '$fechaHasta' 
        AND Lo_Movimiento.IdMovimientoTipo IN (1,2,3) 
        GROUP BY Lo_MovimientoDetalle.IdProducto)";

    if (!$idProducto) {
        $select = "(SELECT Lo_MovimientoDetalle.IdProducto,Lo_Proveedor.IdProveedor,Lo_Proveedor.Proveedor,Lo_Proveedor.Telefono,MAX(Lo_Movimiento.MovimientoFecha) AS UltimaFecha FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle ON  Lo_Movimiento.Hash = Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_Proveedor ON Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
        WHERE Lo_Movimiento.IdAlmacenDestino = $idAlmacen 
        AND Lo_Movimiento.MovimientoFecha < '$fechaHasta' 
        AND Lo_Movimiento.IdMovimientoTipo IN (1,2,3) 
        GROUP BY Lo_MovimientoDetalle.IdProducto)";
    }

    return $select;
}

function stringIngresoUndCount($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    /* $select = "(SELECT IFNULL(SUM(Lo_MovimientoDetalle.Cantidad), 0) AS cantidad FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
            AND Lo_MovimientoDetalle.IdProducto=$idProducto AND Lo_Movimiento.Anulado=0
            AND Lo_Movimiento.MovimientoFecha < '$fechaHasta')"; */

    if (!$idProducto) {
        $select = "(SELECT Lo_MovimientoDetalle.IdProducto, ROUND(SUM(Lo_MovimientoDetalle.Precio * Lo_MovimientoDetalle.Cantidad),2) as monto,  COUNT(Lo_Movimiento.Hash) AS cantidad FROM Lo_Movimiento
            INNER JOIN Lo_MovimientoDetalle ON Lo_Movimiento.Hash = Lo_MovimientoDetalle.hashMovimiento
            INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
            WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
                AND Lo_Movimiento.Anulado=0 " .
            ($fechaDesde ? " AND Lo_Movimiento.MovimientoFecha > '$fechaDesde' " : "")
            . " AND Lo_Movimiento.MovimientoFecha < '$fechaHasta'
            GROUP BY Lo_MovimientoDetalle.IdProducto)";
    }

    return $select;
}

function stringVentaUndCount($idProducto, $idAlmacen, $fechaHasta, $fechaDesde = false)
{
    /* $select = "(SELECT IFNULL(SUM(Lo_MovimientoDetalle.Cantidad), 0) AS cantidad FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        WHERE Lo_MovimientoTipo.VaRegCompra = 1 AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen
            AND Lo_MovimientoDetalle.IdProducto=$idProducto AND Lo_Movimiento.Anulado=0
            AND Lo_Movimiento.MovimientoFecha < '$fechaHasta')"; */

    if (!$idProducto) {
        $select = "(SELECT Ve_DocVentaDet.IdProducto, ROUND(SUM(Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad - Ve_DocVentaDet.Descuento),2) as monto, COUNT(Ve_DocVenta.idDocVenta) AS cantidad FROM Ve_DocVenta
            INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
            INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
            WHERE Ve_DocVenta.IdAlmacen = $idAlmacen
                AND Ve_DocVenta.Anulado = 0
                AND Ve_DocVentaTipoDoc.VaRegVenta = 1 " .
            ($fechaDesde ? " AND Ve_DocVenta.FechaDoc > '$fechaDesde' " : "")
            . " AND Ve_DocVenta.FechaDoc < '$fechaHasta'
                AND Ve_DocVenta.FechaDoc < '$fechaHasta'
            GROUP BY Ve_DocVentaDet.IdProducto)";
    }

    return $select;
}

/*

SELECT Lo_MovimientoDetalle.IdProducto, Gen_ProductoDet.IdProductoDet, (Lo_MovimientoDetalle.Cantidad * Gen_ProductoDet.Cantidad) AS cantidad
/*IFNULL(SUM(Lo_MovimientoDetalle.Cantidad), 0) AS cantidad */
/*
FROM Lo_Movimiento
INNER JOIN Lo_MovimientoDetalle ON Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
INNER JOIN Gen_ProductoDet ON Lo_MovimientoDetalle.IdProducto = Gen_ProductoDet.IdProducto

WHERE Lo_MovimientoTipo.VaRegCompra = 1
	AND Lo_Movimiento.IdAlmacenDestino = 1
	AND Lo_Movimiento.Anulado=0
	AND Lo_Movimiento.MovimientoFecha < '2018-07-23 17:25:36'

GROUP BY Gen_ProductoDet.IdProductoDet

*/

$app->get('/productos/stock/ingresos', function (Request $request, Response $response, array $args) {
    /*$idProducto = $request->getParam('idProducto');
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaHasta = $request->getParam('fechaHasta');

    $data = stockIngresoUnd($this->db, $idProducto, $idAlmacen, $fechaHasta);

    return $response->withJson($data);*/
});
$app->get('/productos/stock/salidas', function (Request $request, Response $response, array $args) {

    /*// SALIDAS POR MOVIMIENTO
    $idProducto = $request->getParam('idProducto');
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaHasta = $request->getParam('fechaHasta');

    $salidaUnd = stockSalidaUnd($this->db, $idProducto, $idAlmacen, $fechaHasta);
    $cantidadSalida = $salidaUnd['cantidad'];

    // SALIDAS POR VENTAS
    $salidaVentaUnd = stockSalidaVentaUnd($this->db, $idProducto, $idAlmacen, $fechaHasta);
    $salidaVentaUnd['cantidad'] += $cantidadSalida;

    return $response->withJson($salidaVentaUnd);*/
});



$app->get('/productos/stock[/{idAlmacen}]', function (Request $request, Response $response, array $args) {
    try {


        $idAlmacen = $request->getParam('idAlmacen');
        $fechaDesde = $request->getParam('fechaDesde') && $request->getParam('fechaDesde') != '' ? $request->getParam('fechaDesde') : false;
        $fechaHasta = $request->getParam('fechaHasta') && $request->getParam('fechaHasta') != '' ? $request->getParam('fechaHasta') : getNow();

        if (isset($args['idAlmacen'])) {
            $idAlmacen = $args['idAlmacen'];
        }

        // $strIngresoUnd = stringIngresoUnd('Gen_Producto.IdProducto', $idAlmacen, $fechaHasta);
        // $strSalidaUnd = stringSalidaUnd('Gen_Producto.IdProducto', $idAlmacen, $fechaHasta);
        // $strSalidaVentaUnd = stringSalidaVentaUnd('Gen_Producto.IdProducto', $idAlmacen, $fechaHasta);

        $strIngresoUnd = stringIngresoUnd(false, $idAlmacen, $fechaHasta, $fechaDesde);
        $strSalidaUnd = stringSalidaUnd(false, $idAlmacen, $fechaHasta, $fechaDesde);

        $strIngresoCaja = stringIngresoCaja(false, $idAlmacen, $fechaHasta, $fechaDesde);
        $strSalidaCaja = stringSalidaCaja(false, $idAlmacen, $fechaHasta, $fechaDesde);

        $strSalidaVentaUnd = stringSalidaVentaUnd(false, $idAlmacen, $fechaHasta, $fechaDesde);
        $strSalidaVentaCaja = stringSalidaVentaCaja(false, $idAlmacen, $fechaHasta, $fechaDesde);

        $stringProveedorUltimo = stringProveedorUltimo(false, $idAlmacen, $fechaHasta);

        /*$select = "SELECT Gen_Producto.*, Gen_ProductoCategoria.ProductoCategoria, Gen_ProductoMarca.ProductoMarca,
            Gen_ProductoTalla.ProductoTalla, Gen_ProductoMedicion.ProductoMedicion,

            $strIngresoUnd AS StockIngresoUnd,
            $strSalidaUnd AS StockSalidaUnd,
            $strSalidaVentaUnd AS StockSalidaVentaUnd,
            ((SELECT StockIngresoUnd) - (SELECT StockSalidaUnd) - (SELECT StockSalidaVentaUnd)) AS stock


            FROM Gen_Producto
            INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
            INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
            LEFT JOIN Gen_ProductoTalla ON Gen_Producto.IdProductoTalla = Gen_ProductoTalla.IdProductoTalla ";*/

        if ($request->getParam('sumaStock')) {
            $select = "SELECT
            SUM(IFNULL(IngresoUnd.cantidad, 0) + IFNULL(IngresoCaja.cantidad, 0)  - IFNULL(SalidaCaja.cantidad, 0) - IFNULL(SalidaUnd.cantidad, 0) - IFNULL(SalidaVentaUnd.cantidad,0) - IFNULL(SalidaVentaCaja.cantidad,0)) AS sumaStock

            FROM Gen_Producto
            INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
            INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
            LEFT JOIN $strIngresoUnd AS IngresoUnd ON Gen_Producto.IdProducto = IngresoUnd.IdProducto
            LEFT JOIN $strSalidaUnd AS SalidaUnd ON Gen_Producto.IdProducto = SalidaUnd.IdProducto
            LEFT JOIN $strIngresoCaja AS IngresoCaja ON Gen_Producto.IdProducto = IngresoCaja.IdProducto
            LEFT JOIN $strSalidaCaja AS SalidaCaja ON Gen_Producto.IdProducto = SalidaCaja.IdProducto
            LEFT JOIN $strSalidaVentaUnd AS SalidaVentaUnd ON Gen_Producto.IdProducto = SalidaVentaUnd.IdProducto
            LEFT JOIN $strSalidaVentaCaja AS SalidaVentaCaja ON Gen_Producto.IdProducto = SalidaVentaCaja.IdProducto";
        } else if ($request->getParam('sumaValorizado')) {
            $select = "SELECT
            SUM(Gen_Producto.PrecioContado * (IFNULL(IngresoUnd.cantidad, 0) + IFNULL(IngresoCaja.cantidad, 0)  - IFNULL(SalidaCaja.cantidad, 0) - IFNULL(SalidaUnd.cantidad, 0) - IFNULL(SalidaVentaUnd.cantidad,0) - IFNULL(SalidaVentaCaja.cantidad,0))) AS sumaValorizado

            FROM Gen_Producto
            INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
            INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
            LEFT JOIN $strIngresoUnd AS IngresoUnd ON Gen_Producto.IdProducto = IngresoUnd.IdProducto
            LEFT JOIN $strSalidaUnd AS SalidaUnd ON Gen_Producto.IdProducto = SalidaUnd.IdProducto
            LEFT JOIN $strIngresoCaja AS IngresoCaja ON Gen_Producto.IdProducto = IngresoCaja.IdProducto
            LEFT JOIN $strSalidaCaja AS SalidaCaja ON Gen_Producto.IdProducto = SalidaCaja.IdProducto
            LEFT JOIN $strSalidaVentaUnd AS SalidaVentaUnd ON Gen_Producto.IdProducto = SalidaVentaUnd.IdProducto
            LEFT JOIN $strSalidaVentaCaja AS SalidaVentaCaja ON Gen_Producto.IdProducto = SalidaVentaCaja.IdProducto";
        } else if ($request->getParam('sumaValorizadoSin')) {
            $select = "SELECT
            SUM(Gen_Producto.PrecioCosto * (IFNULL(IngresoUnd.cantidad, 0) + IFNULL(IngresoCaja.cantidad, 0)  - IFNULL(SalidaCaja.cantidad, 0) - IFNULL(SalidaUnd.cantidad, 0) - IFNULL(SalidaVentaUnd.cantidad,0) - IFNULL(SalidaVentaCaja.cantidad,0))) AS sumaValorizadoSin

            FROM Gen_Producto
            INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
            INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
            LEFT JOIN $strIngresoUnd AS IngresoUnd ON Gen_Producto.IdProducto = IngresoUnd.IdProducto
            LEFT JOIN $strSalidaUnd AS SalidaUnd ON Gen_Producto.IdProducto = SalidaUnd.IdProducto
            LEFT JOIN $strIngresoCaja AS IngresoCaja ON Gen_Producto.IdProducto = IngresoCaja.IdProducto
            LEFT JOIN $strSalidaCaja AS SalidaCaja ON Gen_Producto.IdProducto = SalidaCaja.IdProducto
            LEFT JOIN $strSalidaVentaUnd AS SalidaVentaUnd ON Gen_Producto.IdProducto = SalidaVentaUnd.IdProducto
            LEFT JOIN $strSalidaVentaCaja AS SalidaVentaCaja ON Gen_Producto.IdProducto = SalidaVentaCaja.IdProducto";
        } else {
            $select = "SELECT Gen_Producto.*, Gen_ProductoCategoria.ProductoCategoria, Gen_ProductoMarca.ProductoMarca,
            Gen_ProductoMedicion.ProductoMedicion,
            IFNULL(IngresoUnd.cantidad, 0) AS StockIngresoUnd,
            IFNULL(SalidaUnd.cantidad, 0) AS StockSalidaUnd,
            IFNULL(SalidaVentaUnd.cantidad, 0) AS StockSalidaVentaUnd,
            IFNULL(SalidaVentaCaja.cantidad, 0) AS StockSalidaVentaCaja,

            ROUND((IFNULL(IngresoUnd.cantidad, 0) + IFNULL(IngresoCaja.cantidad, 0)  - IFNULL(SalidaCaja.cantidad, 0) - IFNULL(SalidaUnd.cantidad, 0) - IFNULL(SalidaVentaUnd.cantidad,0) - IFNULL(SalidaVentaCaja.cantidad,0)),2) AS stock ";

            $select .= ($request->getparam('stockminimo') == 1) ? ", IFNULL(ProveedorUltimo.Proveedor, '') AS ProveedorUltimo, IFNULL(ProveedorUltimo.Telefono, '') AS TelefonoUltimo, IFNULL(ProveedorUltimo.UltimaFecha, '') AS FechaUltima" : "";

            $select .= " FROM Gen_Producto
            INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
            INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
            LEFT JOIN $strIngresoUnd AS IngresoUnd ON Gen_Producto.IdProducto = IngresoUnd.IdProducto
            LEFT JOIN $strSalidaUnd AS SalidaUnd ON Gen_Producto.IdProducto = SalidaUnd.IdProducto
            LEFT JOIN $strIngresoCaja AS IngresoCaja ON Gen_Producto.IdProducto = IngresoCaja.IdProducto
            LEFT JOIN $strSalidaCaja AS SalidaCaja ON Gen_Producto.IdProducto = SalidaCaja.IdProducto
            LEFT JOIN $strSalidaVentaUnd AS SalidaVentaUnd ON Gen_Producto.IdProducto = SalidaVentaUnd.IdProducto
            LEFT JOIN $strSalidaVentaCaja AS SalidaVentaCaja ON Gen_Producto.IdProducto = SalidaVentaCaja.IdProducto ";
            $select .= ($request->getparam('stockminimo') == 1) ? " LEFT JOIN " . $stringProveedorUltimo . " AS ProveedorUltimo ON Gen_Producto.IdProducto = ProveedorUltimo.IdProducto" : "";
        }

        if ($request->getParam('filter')) {

            $filter = $request->getParam('filter');
            if (is_array($filter)) {
                $select .= " WHERE Gen_Producto.Anulado = 0 AND Gen_Producto.EsPadre = 0 AND (Gen_Producto.Producto LIKE '%" . (isset($filter['producto']) ? addslashes($filter['producto']) : '') .
                    "%' AND Gen_Producto.CodigoBarra LIKE '" . (isset($filter['codigo']) ? addslashes($filter['codigo']) : '') .
                    "%' ANd Gen_ProductoMarca.ProductoMarca LIKE '" . (isset($filter['marca']) ? addslashes($filter['marca']) : '') .
                    "%' ANd Gen_Producto.ProductoModelo LIKE '" . (isset($filter['modelo']) ? addslashes($filter['modelo']) : '') .
                    "%' AND Gen_ProductoCategoria.ProductoCategoria LIKE '%" . (isset($filter['categoria']) ? addslashes($filter['categoria']) : '') .
                    "%'  ) ";
            } else {
                $select .= " WHERE Gen_Producto.Anulado = 0 AND Gen_Producto.EsPadre = 0 AND (Gen_Producto.Producto LIKE '%" . $filter .
                    "%' OR Gen_Producto.CodigoBarra LIKE '%" . $filter .
                    "%' OR Gen_Producto.Color LIKE '%" . $filter .
                    "%' OR Gen_ProductoMarca.ProductoMarca LIKE '%" . $filter .
                    "%' OR Gen_Producto.ProductoModelo LIKE '%" . $filter .
                    "%' OR Gen_ProductoCategoria.ProductoCategoria LIKE '%" . $filter .
                    "%' ) ";
            }
        } else {
            $q = $request->getParam('q');
            $arr = explode(' ', $q);

            $filters = array();

            foreach ($arr as $term) {
                $term = trim($term);
                if ($term !== '') {
                    $filters[] = "(Gen_Producto.Producto LIKE '%$term%'
                                OR Gen_ProductoCategoria.ProductoCategoria LIKE '%$term%'
                                OR Gen_ProductoMarca.ProductoMarca LIKE '%$term%'
                                OR Gen_Producto.CodigoBarra LIKE '%$term%')";
                }
            }

            $select .= " WHERE Gen_Producto.Anulado = 0 ";

            if (!empty($filters)) {
                $select .= " AND (" . implode(' AND ', $filters) . ")";
            }
        }

        if ($request->getParam('idProductos')) {
            // mandar array de IDS
            $idProductos = $request->getParam('idProductos');
            $select .= " AND Gen_Producto.IdProducto IN (" . implode(',', $idProductos) . ")";
        }

        $select .= "AND (Gen_Producto.ControlaStock=1 OR Gen_Producto.CodigoBarra='MANODEOBRA')";

        if ($request->getparam('stockminimo') == 1) {
            $select .= " AND Gen_Producto.ControlaStockMinimo = 1 ";
        }

        if ($request->getparam('IdProveedor')) {
            $select .= " AND ProveedorUltimo.IdProveedor =  " . $request->getparam('IdProveedor');
        }

        if (isset($request->getparam('filter')['minimo']) && !$request->getParam('sumaStock') && !$request->getParam('sumaValorizado') && !$request->getParam('sumaValorizadoSin')) {
            $filterMinimo = $request->getParam('filter')['minimo'];
            if ($filterMinimo) {
                $select .= " HAVING stock <= Gen_Producto.StockMinimo";
            }
        } else {
            if (isset($request->getParam('filter')['stock']) && !$request->getParam('sumaStock') && !$request->getParam('sumaValorizado') && !$request->getParam('sumaValorizadoSin')) {
                $filterStock = $request->getParam('filter')['stock'];
                if ($filterStock == 'mayor') {
                    $select .= " HAVING stock > 0";
                }
                if ($filterStock == 'menor') {
                    $select .= " HAVING stock <= 0";
                }
            }
        }

        if ($request->getparam('condicionstockminimo') == 1) {
            $select .= " HAVING stock < Gen_Producto.StockMinimo ";
        }



        if ($request->getParam('sortBy')) {
            $sortBy = $request->getParam('sortBy');
            $sortDesc = $request->getParam('sortDesc');
            $orientation = $sortDesc ? 'DESC' : 'ASC';
            $select .= " ORDER BY " . $sortBy . " " . $orientation;
        }


        if ($request->getParam('limit') && !$request->getParam('count')) {
            $limit = $request->getParam('limit');
            $offset = 0;
            if ($request->getParam('page')) {
                $page = $request->getParam('page');
                $offset = (--$page) * $limit;
            }
            $select .= " LIMIT " . $limit;
            $select .= " OFFSET " . $offset;
        }
        // print_r($select);exit();
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        if ($request->getParam('count')) {
            $data = array('total' => $stmt->rowCount());
        }

        return $response->withJson($data);
    } catch (Exception $e) {

        return $response->withJson([$e->getMessage()], 500);
    }
});



$app->get('/movimientos/numero', function (Request $request, Response $response, array $args) {
    $serie = $request->getParam('serie');
    $idMovimientoTipo = $request->getParam('idMovimientoTipo');

    $select = "SELECT Lo_Movimiento.IdMovimientoTipo, Lo_MovimientoTipo.Tipo, Lo_Movimiento.Serie, Lo_Movimiento.Numero, (Lo_Movimiento.Numero+1) as NuevoNumero
        FROM Lo_Movimiento
        INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
        WHERE Lo_Movimiento.Serie = '$serie' AND Lo_Movimiento.IdMovimientoTipo = '$idMovimientoTipo'
        ORDER BY Lo_Movimiento.Numero DESC
        LIMIT 1";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/movimientos/lista', function (Request $request, Response $response) {
    $select = "SELECT CONCAT(Lo_Movimiento.Serie, '-', Lo_Movimiento.Numero) AS NroComprobante, Lo_Movimiento.* 
        FROM Lo_Movimiento";
    $select .= " WHERE CONCAT(Lo_Movimiento.Serie, '-', Lo_Movimiento.Numero) LIKE '%" . $request->getParam('q') . "%'  ";
    $select .= " LIMIT 10";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});


$app->get('/movimiento/detalle/comprobante', function (Request $request, Response $response) {
    $hashMovimiento = $request->getParam('hashMovimiento');

    $select = "SELECT Lo_MovimientoDetalle.hashMovimiento,Lo_MovimientoDetalle.IdProducto,
    Lo_MovimientoDetalle.Cantidad AS cantidad,
    Lo_MovimientoDetalle.Precio AS precio, Lo_MovimientoDetalle.Precio AS PrecioContado, Lo_Movimiento.IdProveedor,
    Gen_ProductoMarca.ProductoMarca, Gen_ProductoMedicion.ProductoMedicion,
    Gen_Producto.Producto,
    Gen_Producto.ProductoDesc,
    Gen_Producto.ProductoDescCorto,
    Gen_Producto.CodigoBarra,
    Gen_Producto.Codigo,
    Gen_Producto.Dosis,
    Gen_Producto.PrecioPorMayor,
    Gen_Producto.StockPorMayor,
    Gen_Producto.StockMinimo,
    Gen_Producto.Anulado,
    Gen_Producto.FechaReg,
    Gen_Producto.Hash,
    Gen_Producto.ControlaStock,
    Gen_Producto.PrecioCosto,
    Gen_Producto.VentaEstrategica,
    Gen_Producto.IdBloque,
    Gen_Producto.EsPadre
    FROM Lo_MovimientoDetalle  
    INNER JOIN Lo_Movimiento ON Lo_MovimientoDetalle.hashMovimiento = Lo_Movimiento.Hash 
    INNER JOIN Gen_Producto ON Lo_MovimientoDetalle.IdProducto = Gen_Producto.IdProducto 
    INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
    INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
    WHERE Lo_MovimientoDetalle.hashMovimiento=$hashMovimiento  ORDER BY Lo_MovimientoDetalle.hashMovimiento DESC ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});

// VENTAS

//API ESTADISTICOS

$app->get('/ventas/estadistica/dia', function (Request $request, Response $response) {
    $anio      = $request->getParam('anio');
    $mes       = $request->getParam('mes');
    $idAlmacen = $request->getParam('idAlmacen');
    $tipoDoc   = $request->getParam('tipoDoc');   // ← AÑADIR

    $condiciones = [];
    if ($idAlmacen != 0 && $idAlmacen != null)
        $condiciones[] = "Ve_DocVenta.IdAlmacen = $idAlmacen";
    $condiciones[] = "YEAR(Ve_DocVenta.FechaDoc) = $anio";
    $condiciones[] = "MONTH(Ve_DocVenta.FechaDoc) = $mes";
    $condiciones[] = "Ve_DocVenta.Anulado = 0";
    if (!empty($tipoDoc))                          // ← AÑADIR
        $condiciones[] = "Ve_DocVenta.IdTipoDoc = $tipoDoc";

    $where = implode(" AND ", $condiciones);

    $select = "SELECT YEAR(Ve_DocVenta.FechaDoc) as Anio,
        MONTH(Ve_DocVenta.FechaDoc) as Mes,
        DAY(Ve_DocVenta.FechaDoc) as Dia,
        DAYNAME(Ve_DocVenta.FechaDoc) as DiaNombre,
        SUM(IF(Ve_DocVentaTipoDoc.CodSunat='07',-1,1) *
            ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2)
        ) AS Total
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta = Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        WHERE $where
        GROUP BY DAY(Ve_DocVenta.FechaDoc)
        ORDER BY Ve_DocVenta.FechaDoc ASC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    return $response->withJson($stmt->fetchAll());
});

$app->get('/ventas/estadistica/mes', function (Request $request, Response $response) {
    $anio      = $request->getParam('anio');
    $idAlmacen = $request->getParam('idAlmacen');
    $tipoDoc   = $request->getParam('tipoDoc');   // ← AÑADIR

    $condiciones = [];
    if ($idAlmacen != 0 && $idAlmacen != null)
        $condiciones[] = "Ve_DocVenta.IdAlmacen = $idAlmacen";
    $condiciones[] = "YEAR(Ve_DocVenta.FechaDoc) = $anio";
    $condiciones[] = "Ve_DocVenta.Anulado = 0";
    if (!empty($tipoDoc))                          // ← AÑADIR
        $condiciones[] = "Ve_DocVenta.IdTipoDoc = $tipoDoc";

    $where = implode(" AND ", $condiciones);

    $select = "SELECT YEAR(Ve_DocVenta.FechaDoc) as Anio,
        MONTH(Ve_DocVenta.FechaDoc) as Mes,
        SUM(IF(Ve_DocVentaTipoDoc.CodSunat='07',-1,1) *
            ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2)
        ) AS Total
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta = Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        WHERE $where
        GROUP BY MONTH(Ve_DocVenta.FechaDoc)
        ORDER BY Ve_DocVenta.FechaDoc ASC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    return $response->withJson($stmt->fetchAll());
});


$app->get('/ventas/estadistica/rango', function (Request $request, Response $response) {
    $fechaDesde = $request->getParam('fechaDesde');
    $fechaHasta = $request->getParam('fechaHasta');
    $idAlmacen  = $request->getParam('idAlmacen');
    $tipoDoc    = $request->getParam('tipoDoc');   // ← AÑADIR

    $condiciones = [];
    if ($idAlmacen != 0 && $idAlmacen != null)
        $condiciones[] = "Ve_DocVenta.IdAlmacen = $idAlmacen";
    $condiciones[] = "Ve_DocVenta.FechaDoc BETWEEN '$fechaDesde 00:00:00' AND '$fechaHasta 23:59:59'";
    $condiciones[] = "Ve_DocVenta.Anulado = 0";
    if (!empty($tipoDoc))                          // ← AÑADIR
        $condiciones[] = "Ve_DocVenta.IdTipoDoc = $tipoDoc";

    $where = implode(" AND ", $condiciones);

    $select = "SELECT YEAR(Ve_DocVenta.FechaDoc) as Anio,
        MONTH(Ve_DocVenta.FechaDoc) as Mes,
        DAY(Ve_DocVenta.FechaDoc) as Dia,
        DAYNAME(Ve_DocVenta.FechaDoc) as DiaNombre,
        SUM(IF(Ve_DocVentaTipoDoc.CodSunat='07',-1,1) *
            ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2)
        ) AS Total
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta = Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        WHERE $where
        GROUP BY DAY(Ve_DocVenta.FechaDoc), MONTH(Ve_DocVenta.FechaDoc)
        ORDER BY Ve_DocVenta.FechaDoc ASC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    return $response->withJson($stmt->fetchAll());
});


$app->get('/ventas/tipos', function (Request $request, Response $response, array $args) {
    //$select = $this->db->select()->from('Ve_DocVentaTipoDoc')->whereLike('TipoDoc', '%' . $request->getParam('q') . '%');


    $select = "SELECT * FROM Ve_DocVentaTipoDoc 
        WHERE TipoDoc LIKE '%" . $request->getParam('q') . "%' 
        AND Oculto=0 
        ORDER BY Orden ASC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/ventas/solonumero', function (Request $request, Response $response, array $args) {
    $serie = $request->getParam('serie');
    $idTipoDoc = $request->getParam('idTipoDoc');

    // Obtener siguiente Numero
    $selectNumero = "SELECT Numero + 1 AS NuevoNumero FROM Ve_DocVenta 
        WHERE IdTipoDoc=$idTipoDoc AND Serie='$serie'
        ORDER BY Numero DESC LIMIT 1";
    $stmt = $this->db->query($selectNumero);
    $stmt->execute();
    $selectNumero = $stmt->fetch();
    $numero = $selectNumero['NuevoNumero'];
    return $response->withJson(array(
        "numero" => $numero ? $numero : 1
    ));
});

$app->get('/ventas/lista/[{idPuntoVenta}]', function (Request $request, Response $response) {
    $idPuntoVenta =  $request->getAttribute('idPuntoVenta', 0);
    $select = "SELECT Ve_DocVentaTipoDoc.CodSunat, CONCAT(Ve_DocVenta.Serie, '-', Ve_DocVenta.Numero) AS NroComprobante, Ve_DocVenta.idDocVenta AS IdDocVenta FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc";
    $select .= " WHERE Ve_DocVentaTipoDoc.CodSunat IN ('01', '03') AND Ve_DocVenta.Anulado=0 AND  Ve_DocVenta.IdDocVentaPuntoVenta = $idPuntoVenta AND
    CONCAT(Ve_DocVenta.Serie, '-', Ve_DocVenta.Numero) LIKE '%" . $request->getParam('q') . "%'  ";
    $select .= " LIMIT 10";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});

$app->get('/proveedor', function (Request $request, Response $response, array $args) {
    try {


        $idProveedor = $request->getParam('idProveedor');
        $ruc = $request->getParam('ruc');
        $select = "SELECT 
                    IFNULL(CONCAT(Ruc, ' - ', Proveedor, ' - ', IFNULL(Proveedor, '')), '-') 
                    AS ProveedorDniRuc , Lo_Proveedor.* FROM Lo_Proveedor WHERE TRUE ";


        if ($ruc) {
            $select .= " AND Ruc='$ruc'";
        } else if ($idProveedor) {
            $select = " AND  IdProveedor='$idProveedor'";
        } else if ($request->getParam('q')) {
            $select .= " OR Lo_Proveedor.Ruc LIKE '%" . $request->getParam('q') . "%')  ";
            $select .= " OR Lo_Proveedor.Proveedor LIKE '%" . $request->getParam('q') . "%')  ";
        }
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception  $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/venta/detalle/comprobante', function (Request $request, Response $response) {
    $nroComprobante = $request->getParam('NroComprobante');
    $idDocVenta = $request->getParam('idDocVenta');
    $IdDocVentaPuntoVenta = $request->getParam('IdDocVentaPuntoVenta');
    $comprobante = explode('-', $nroComprobante);
    $serie = $comprobante[0];
    $numero = $comprobante[1];

    $select = "SELECT Ve_DocVentaDet.IdDocVentaDet,Ve_DocVentaDet.IdDocVenta,Ve_DocVentaDet.IdProducto,
    Ve_DocVentaDet.Cantidad AS cantidad,Ve_DocVentaDet.Cantidad AS cantxPrecio,'1' AS cantidadPres, 
    Ve_DocVentaDet.Precio AS precio,Ve_DocVentaDet.EsManoDeObra, Ve_DocVentaDet.Descuento AS descuento,
    0 as PorcentajeUtilidad,Ve_DocVenta.IdCliente,
    Gen_Producto.Producto,Gen_Producto.PrecioContado,
    Gen_Producto.precioConvenio,Gen_Producto.PrecioCosto,Gen_Producto.ProductoDesc,Gen_Producto.ProductoDesc2,Gen_Producto.ProductoDesc3,
    Gen_Producto.PreciosPorProducto ,Gen_Producto.CodigoBarra,Gen_Producto.EsPadre
    FROM Ve_DocVentaDet 
    INNER JOIN Ve_DocVenta ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.IdDocVenta
    INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto 
    WHERE Ve_DocVenta.idDocVenta=$idDocVenta  ORDER BY Ve_DocVentaDet.IdDocVentaDet DESC ";


    try {
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});


$app->get('/ventas/busqueda', function (Request $request, Response $response) {
    $select = "SELECT Ve_DocVentaTipoDoc.CodSunat, CONCAT(Ve_DocVenta.Serie, '-', Ve_DocVenta.Numero) AS NroComprobante, 
        Ve_DocVenta.idDocVenta AS IdDocVenta FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc";
    $select .= " WHERE Ve_DocVentaTipoDoc.CodSunat IN ('01', '03') AND Ve_DocVenta.Anulado=0 AND
    CONCAT(Ve_DocVenta.Serie, '-', Ve_DocVenta.Numero) LIKE '%" . $request->getParam('q') . "%'  ";
    $select .= " LIMIT 10";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});

$app->get('/ventas/tipos/electronicos', function (Request $request, Response $response, array $args) {
    //$select = $this->db->select()->from('Ve_DocVentaTipoDoc')->whereLike('TipoDoc', '%' . $request->getParam('q') . '%');


    $select = "SELECT * FROM Ve_DocVentaTipoDoc WHERE TipoDoc LIKE '%" . $request->getParam('q') . "%' AND EsElectronico=1 ORDER BY Orden ASC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/ventas/puntos', function (Request $request, Response $response, array $args) {
    $select = $this->db->select()->from('Ve_DocVentaPuntoVenta')->whereLike('PuntoVenta', '%' . $request->getParam('q') . '%');
    $stmt = $select->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/ventas/descuento/[{puntos}]', function (Request $request, Response $response, array $args) {
    $puntos = $request->getAttribute('puntos', 0);

    $select = "SELECT * FROM Ve_DocVentaDescuento WHERE IdDocVentaDescuento = 1";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();
    $data['descuento'] = $data['ValorPunto'] * $puntos;

    return $response->withJson($data);
});

$app->get('/ventas/descuento', function (Request $request, Response $response, array $args) {
    $select = $this->db->select()->from('Ve_DocVentaDescuento')->where('IdDocVentaDescuento', '=', 1);
    $stmt = $select->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->post('/ventas/descuento', function (Request $request, Response $response) {
    $id = 1;
    $porCada = $request->getParam('porCada');
    $puntos = $request->getParam('puntos');
    $valorPunto = $request->getParam('valorPunto');

    $update = "UPDATE Ve_DocVentaDescuento SET PorCada=$porCada, Puntos=$puntos, ValorPunto=$valorPunto
        WHERE IdDocVentaDescuento=$id";

    $stmt = $this->db->prepare($update);
    $updated = $stmt->execute();

    return $response->withJson(array(
        "IdDocVentaDescuento" => $id,
        "PorCada" => $porCada,
        "Puntos" => $puntos,
        "ValorPunto" => $valorPunto
    ));
});

$app->get('/ventas/comision', function (Request $request, Response $response, array $args) {
    $select = $this->db->select()->from('GEN_EMPRESA')->where('IDEMPRESA', '=', 1);
    $stmt = $select->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->post('/ventas/comision', function (Request $request, Response $response) {
    $id = 1;
    $valorComision = $request->getParam('valorComision');

    $update = "UPDATE GEN_EMPRESA SET valorComision=$valorComision 
        WHERE IDEMPRESA=$id";

    $stmt = $this->db->prepare($update);
    $updated = $stmt->execute();

    return $response->withJson(array(
        "IDEMPRESA" => $id,
        "valorComision" => $valorComision
    ));
});

$app->get('/ventas/numero', function (Request $request, Response $response, array $args) {
    $idPuntoVenta = $request->getParam('idPuntoVenta');
    $idTipoDoc = $request->getParam('idTipoDoc');

    // Obtener Serie
    $selectSerie = "SELECT Serie FROM Ve_DocVentaPuntoVentaDet WHERE IdDocVentaPuntoVenta=$idPuntoVenta AND IdDocVentaTipoDoc=$idTipoDoc";

    $stmt = $this->db->query($selectSerie);
    $stmt->execute();
    $selectSerie = $stmt->fetch();
    $serie = $selectSerie['Serie'];

    // Obtener siguiente Numero
    $selectNumero = "SELECT Numero + 1 AS NuevoNumero FROM Ve_DocVenta
        WHERE IdDocVentaPuntoVenta=$idPuntoVenta AND IdTipoDoc=$idTipoDoc
        ORDER BY Numero DESC LIMIT 1";
    $stmt = $this->db->query($selectNumero);
    $stmt->execute();
    $selectNumero = $stmt->fetch();
    $numero = $selectNumero['NuevoNumero'];
    return $response->withJson(array(
        "serie" => $serie,
        "numero" => $numero ? $numero : 1
    ));
});


$app->get('/ventas/total', function (Request $request, Response $response, array $args) {
    $fechaInicio = $request->getParam('fechaInicio');
    $fechaFin = $request->getParam('fechaFin');
    $idTipoDoc = $request->getParam('idTipoDoc');
    // $fechaInicio = $fechaInicio ? $fechaInicio : getNow('Y') . '-01-01';
    $fechaInicio = $fechaInicio ? $fechaInicio : getNow('Y-m-d');
    $fechaFin = $fechaFin ? $fechaFin : getNow('Y-m-d');

    $select = "SELECT ROUND(SUM((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - Ve_DocVentaDet.Descuento), 2) AS total
        FROM Ve_DocVentaDet
        INNER JOIN Ve_DocVenta ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta
        WHERE Ve_DocVenta.Anulado=0 AND Ve_DocVenta.FechaDoc BETWEEN CAST('" . $fechaInicio . "' AS DATETIME) AND CONCAT('" . $fechaFin . "',' 23:59:59')";
    //print_r($select);exit();

    if ($idTipoDoc) {
        $select .= " AND Ve_DocVenta.IdTipoDoc = $idTipoDoc";
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});


$app->get('/ventas/usuario', function (Request $request, Response $response) {
    $usuario = $request->getParam('usuario');
    $fechaInicio = $request->getParam('fechaInicio');
    $fechaFin = $request->getParam('fechaFin');
    $fechaInicio = $fechaInicio ? $fechaInicio : getNow('Y') . '-01-01';
    $fechaFin = $fechaFin ? $fechaFin : getNow('Y-m-d');
    $idTipoDoc = $request->getParam('idTipoDoc');

    $select = "SELECT ROUND(SUM((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - Ve_DocVentaDet.Descuento), 2) AS total FROM Ve_DocVentaDet
    INNER JOIN Ve_DocVenta ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta
    INNER JOIN Seg_Usuario ON Ve_DocVenta.UsuarioReg = Seg_Usuario.Usuario
    WHERE Ve_DocVenta.Anulado=0 AND Seg_Usuario.Usuario = '$usuario'
    AND Ve_DocVenta.FechaDoc BETWEEN CAST('" . $fechaInicio . "' AS DATETIME) AND CONCAT('" . $fechaFin . "',' 23:59:59')";
    //print_r($select);exit();

    if ($idTipoDoc) {
        $select .= " AND Ve_DocVenta.IdTipoDoc = $idTipoDoc";
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});


$app->get('/ventas/detalle', function (Request $request, Response $response) {
    $idDocVentas = $request->getParam('idDocVentas');

    $select = "SELECT Ve_DocVentaDet.*, Gen_Producto.Producto,
        ROUND(Ve_DocVentaDet.Descuento, 2) AS Descuento,
        ROUND((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - Ve_DocVentaDet.Descuento, 2) AS Subtotal,
        ROUND(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio, 2) AS Total,
        Gen_Producto.CodigoBarra, Gen_ProductoMedicion.Codigo AS CodigoMedicion, Gen_ProductoMedicion.ProductoMedicion
        FROM Ve_DocVentaDet
        INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
        INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
        WHERE IdDocVenta IN (" . implode(',', $idDocVentas) . ")";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/ventas', function (Request $request, Response $response) {
    $filtros = '';

    $select = "SELECT Ve_DocVenta.idDocVenta, Ve_DocVenta.FechaDoc, Ve_DocVentaTipoDoc.TipoDoc, Ve_DocVentaTipoDoc.TieneIgv,
        Ve_DocVenta.Anulado, 
        CONCAT(Ve_DocVenta.Serie, '-', Ve_DocVenta.Numero) as Correlativo,
        Ve_DocVenta.Serie, Ve_DocVenta.Numero, Ve_DocVentaCliente.Cliente, Ve_DocVenta.UsuarioReg,
        Ve_DocVentaTipoDoc.CodSunat, Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.Direccion,
        Ve_DocVentaTipoDoc.CodigoIgv, Ve_DocVenta.Estado, Ve_DocVenta.Hash_cpe, Ve_DocVenta.Hash_cdr, Ve_DocVenta.Msj_sunat,
        Ve_DocVenta.CodSunatModifica,
        Ve_DocVenta.NroComprobanteModifica,
        Ve_DocVenta.NotaIdMotivo,
        Ve_DocVenta.NotaDescMotivo,
        Ve_DocVenta.EsCredito, Ve_DocVenta.FechaCredito, IFNULL(Ve_DocVenta.GRE,'') AS GRE,
        IFNULL((SELECT SUM(ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2)) FROM Ve_DocVentaDet WHERE Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta), 0 ) AS Total
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        LEFT JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente";

    $select .= " WHERE CONCAT(Ve_DocVenta.Serie, '-', Ve_DocVenta.Numero) LIKE '%" . $request->getParam('q') . "%' ";


    $idAlmacen = $request->getParam('idAlmacen');
    if ($idAlmacen) {
        $filtros .= " AND Ve_DocVenta.IdAlmacen =  $idAlmacen";
    }

    $filter = ($request->getParam('filter')) ? $request->getParam('filter') : [];
    if (!isset($filter['fechaInicio']) && is_array($filter)) {
        //$filter['fechaInicio'] = getNow('Y') . '-01-01';
        $filter['fechaInicio'] = getNow('Y-m-d');
    }

    if (!isset($filter['fechaFin']) && is_array($filter)) {
        $filter['fechaFin'] = getNow('Y-m-d');
    }

    $noFecha = $request->getParam('noFecha');
    if ($noFecha == 1 && isset($filter['fechaInicio']) && isset($filter['fechaFin'])) {
        unset($filter['fechaInicio']);
        unset($filter['fechaFin']);
    }

    if ($filter) {
        if (is_array($filter)) {

            if (isset($filter['vendedor']) && $filter['vendedor']) $filtros .= " AND Ve_DocVenta.UsuarioReg = '" . $filter['vendedor'] . "'";
            if (isset($filter['declarado'])) $filtros .= " AND Ve_DocVentaTipoDoc.VaRegVenta = " . $filter['declarado'];
            if (isset($filter['fechaInicio']) && isset($filter['fechaFin'])) $filtros .= " AND Ve_DocVenta.FechaDoc BETWEEN CAST('" . $filter['fechaInicio'] . "' AS DATETIME) AND CONCAT('" . $filter['fechaFin'] . "',' 23:59:59')";
        } else {
            $select .= " AND (Ve_DocVenta.idDocVenta LIKE '%" . $filter .
                "%' OR CONCAT(Ve_DocVenta.Serie, '-', Ve_DocVenta.Numero) LIKE '%" . $filter .
                "%' OR Ve_DocVentaTipoDoc.TipoDoc LIKE '%" . $filter .
                "%' OR Ve_DocVentaCliente.Cliente LIKE '%" . $filter .
                "%' OR Ve_DocVentaCliente.DniRuc LIKE '%" . $filter .
                "%' )";
        }
    }

    $idTipoDoc = $request->getParam('idTipoDoc');
    if ($idTipoDoc) {
        $filtros .= " AND Ve_DocVenta.IdTipoDoc IN (" . implode(',', $idTipoDoc) . ")";
    }

    $estado = $request->getParam('estado');
    // var_dump($estado);exit();
    if ($estado || $estado == "0") {
        $filtros .= " AND Ve_DocVenta.Estado=$estado";
    }

    $anulado = $request->getParam('anulado');
    if (!is_null($anulado) && $anulado != '' && ($anulado == 0 || $anulado == 1)) {
        $filtros .= " AND Ve_DocVenta.Anulado='$anulado'";
    }

    $select .= $filtros;

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }


    if ($request->getParam('limit')) {
        $limit = $request->getParam('limit');
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    // print_r($select);exit();
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->get('/ventas/count', function (Request $request, Response $response) {
    $idAlmacen = $request->getParam('idAlmacen');
    //$select = "SELECT COUNT(*) as total FROM Ve_DocVenta WHERE Ve_DocVenta.IdAlmacen = $idAlmacen";
    $select = "SELECT COUNT(*) as total FROM Ve_DocVenta";

    if ($idAlmacen) {
        $select .= " WHERE Ve_DocVenta.IdAlmacen = $idAlmacen";
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});


$app->post('/ventas', function (Request $request, Response $response) {
    $esVentaDesdeApp = $request->getHeaderLine('X-Ventas-App') === '1';
    // Bloqueo de seguridad: impide registrar ventas cuando la vigencia finalizó.
    $stmtVigencia = $this->db->query("SELECT MetaKey, MetaValue FROM Gen_EmpresaConfig WHERE IdEmpresa=1 AND MetaKey IN ('vigencia_fecha_ultimo_pago','vigencia_hasta','vigencia_estado_pago','ventas_app_bloqueo_activo','ventas_app_fecha_limite','ventas_app_estado_pago','sistema_zona_horaria') ORDER BY IdEmpresaConfig ASC");
    $stmtVigencia->execute();
    $vigenciaConfig = array();
    foreach ($stmtVigencia->fetchAll() as $vigenciaFila) {
        $vigenciaConfig[$vigenciaFila['MetaKey']] = $vigenciaFila['MetaValue'];
    }
    $fechaLimitePagoApi = trim((string) ($vigenciaConfig['vigencia_fecha_ultimo_pago'] ?? ''));
    $vigenciaHastaApi = trim((string) ($vigenciaConfig['vigencia_hasta'] ?? ''));
    $fechaBloqueoApi = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaLimitePagoApi) ? $fechaLimitePagoApi : $vigenciaHastaApi;
    $vigenciaEstadoApi = strtoupper(trim((string) ($vigenciaConfig['vigencia_estado_pago'] ?? 'PENDIENTE')));
    if ($esVentaDesdeApp) {
        $bloqueoAppApi = ($vigenciaConfig['ventas_app_bloqueo_activo'] ?? '0') === '1';
        $fechaBloqueoApi = trim((string) ($vigenciaConfig['ventas_app_fecha_limite'] ?? ''));
        $vigenciaEstadoApi = strtoupper(trim((string) ($vigenciaConfig['ventas_app_estado_pago'] ?? 'PAGADO')));
    } else {
        $bloqueoAppApi = true;
    }
    $zonaVigenciaApi = trim((string) ($vigenciaConfig['sistema_zona_horaria'] ?? 'America/Lima'));
    try {
        $hoyVigenciaApi = (new DateTime('today', new DateTimeZone($zonaVigenciaApi)))->format('Y-m-d');
    } catch (Throwable $e) {
        $hoyVigenciaApi = (new DateTime('today', new DateTimeZone('America/Lima')))->format('Y-m-d');
    }
    if ($bloqueoAppApi && $vigenciaEstadoApi !== 'PAGADO' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaBloqueoApi) && $fechaBloqueoApi <= $hoyVigenciaApi) {
        return $response->withJson(array(
            'error' => 'Punto de venta bloqueado: se cumplio la fecha limite de pago.',
            'vigenciaVencida' => true,
            'fechaLimitePago' => $fechaBloqueoApi
        ), 423);
    }


    try {
        //code...

        $vendedor = 'xx';
        $idComisionista = 0;
        if (isset($_SESSION['User'])) {
            $vendedor = $_SESSION['User'];
        }

        // START INSERTAR NUEVA VENTA
        $idDocVentaPuntoVenta = $request->getParam('puntoVenta')['IdDocVentaPuntoVenta'];
        $idCliente = $request->getParam('cliente')['IdCliente'];
        $idTipoDoc = $request->getParam('tipoVenta')['IdTipoDoc'];
        $codSunat = $request->getParam('tipoVenta')['CodSunat'];
        $idAlmacen = $request->getParam('almacen')['IdAlmacen'];
        $yapeMonto = $request->getParam('yapeMonto');
        $yapeDescripcion = $request->getParam('yapeDescripcion');
        $visaMonto = $request->getParam('visaMonto');
        $visaDescripcion = $request->getParam('visaDescripcion');
        $mastercardMonto = $request->getParam('mastercardMonto');
        $mastercardDescripcion = $request->getParam('mastercardDescripcion');
        $EsOrganizacion = $request->getParam('EsOrganizacion') ? $request->getParam('EsOrganizacion') : 0;
        $nombreOrganizacion = $request->getParam('nombreOrganizacion') ? $request->getParam('nombreOrganizacion') : null;
        // $serie = $request->getParam('Serie');
        // $numero = $request->getParam('Numero');
        $anulado = 0;
        $usuarioReg = isset($request->getParam('vendedor')['Usuario']) ? $request->getParam('vendedor')['Usuario'] : $vendedor;
        $pagoCon = $request->getParam('PagoCon') == '' || $request->getParam('PagoCon') == null ? 0 : $request->getParam('PagoCon');

        if ($usuarioReg == 'xx') {
            return $response->withJson(['error' => 'Sesión Caducada'], 500);
        }



        $codSunatModifica = $request->getParam('CodSunatModifica');
        $TipoPago = $request->getParam('TipoPago');
        $IdProforma = $request->getParam('IdProforma');
        if (empty($IdProforma)) {
            $IdProforma = 0;
        }
        $nroComprobanteModifica = $request->getParam('NroComprobanteModifica');
        $notaIdMotivo = $request->getParam('NotaIdMotivo');
        $notaDescMotivo = $request->getParam('NotaDescMotivo');

        $descripcion = $request->getParam('descripcion');
        $esCredito = $request->getParam('EsCredito');
        $fechaCredito = $request->getParam('FechaCredito');
        $idPreOrden = $request->getParam('IdPreOrden');
        $adelantosPreOrden = $request->getParam('adelantosPreOrden');
        $campoDireccion = $request->getParam('CampoDireccion');
        $IdDocVentas = $request->getParam('IdDocVentas');
        $IdOferta = $request->getParam('IdOferta') == '' ? null : $request->getParam('IdOferta');

        $idPedido = $request->getParam('IdPedido') ? $request->getParam('IdPedido') : 0;
        $esDelivery = $request->getParam('EsDelivery') ? 1 : 0;
        $direccionEntrega = $request->getParam('DireccionEntrega') ? $request->getParam('DireccionEntrega') : '';
        $nombreLlevar = $request->getParam('nombreLlevar') ? $request->getParam('nombreLlevar') : '';
        $garantia = $request->getParam('Garantia');


        //Obtener almacen y punto según vendedor
        $selectUsuario = "SELECT * FROM Seg_Usuario WHERE Usuario = '$usuarioReg'";
        $stmtUser = $this->db->query($selectUsuario);
        $user = $stmtUser->fetch();
        $stmtUser->execute();

        if (!empty($user)) {
            $idDocVentaPuntoVenta = $user['IdDocVentaPuntoVenta'];
            $idAlmacen = $user['IdAlmacen'];
        }

        $idComisionista = isset($request->getParam('comisionista')['IdCliente']) ? $request->getParam('comisionista')['IdCliente'] : $idComisionista;
        // $valorComision = $request->getParam('valorComision'); 
        if ($idComisionista <> 0) {
            $selectValorComision = "SELECT valorComision FROM GEN_EMPRESA 
            WHERE IDEMPRESA=1 LIMIT 1";
            $stmt = $this->db->query($selectValorComision);
            $stmt->execute();
            $selectValorComision = $stmt->fetch();
        }
        $valorComision = $selectValorComision['valorComision'] ? $selectValorComision['valorComision'] : 0;
        $EntregadoOfertaFlag =  0;

        if (!empty($IdOferta)) {
            $update = " UPDATE Ve_DocVenta SET IdOferta = $IdOferta WHERE IdDocVenta IN ($IdDocVentas)";
            $stmt = $this->db->prepare($update);
            $updated = $stmt->execute();
            $EntregadoOfertaFlag = 1;
        }


        // OBTENER SERIE
        $selectSerie = "SELECT Serie FROM Ve_DocVentaPuntoVentaDet WHERE IdDocVentaPuntoVenta=$idDocVentaPuntoVenta AND IdDocVentaTipoDoc=$idTipoDoc";

        $stmt = $this->db->query($selectSerie);
        $stmt->execute();
        $selectSerie = $stmt->fetch();
        $serie = $selectSerie['Serie'] ? $selectSerie['Serie'] : $request->getParam('Serie');

        /*if(is_numeric($idPreOrden)) {
        $selectPre = "SELECT * FROM Ve_PreOrden WHERE IdPreOrden = $idPreOrden";

        $stmt = $this->db->query($selectPre);
        $stmt->execute();
        $preOrden = $stmt->fetch(PDO::FETCH_ASSOC);

        $insert = "INSERT INTO Ve_DocVenta (IdDocVentaPuntoVenta,IdCliente,IdTipoDoc,IdAlmacen,Serie,Numero,FechaDoc,Anulado,FechaReg,UsuarioReg,Hash, EsCredito, FechaCredito, PagoCon, IdPreOrden, LugarProcedencia, MedioTransporte, ProximoDestino)
        VALUES ($idDocVentaPuntoVenta, $idCliente, $idTipoDoc, $idAlmacen, '$serie', '$numero', '" . getNow() . "', $anulado, '" . getNow() . "', '$usuarioReg', UNIX_TIMESTAMP(), $esCredito, '$fechaCredito', '$pagoCon', $idPreOrden, '" . $preOrden['LugarProcedencia'] . "', '" . $preOrden['MedioTransporte'] . "', '" . $preOrden['ProximoDestino'] . "')";
    } else if(is_array($idPreOrden)) {
        $idPreOrdens = [];
        foreach($idPreOrden as $pre) {
            $update = "UPDATE Ve_PreOrden SET IdDocVenta=Puntos+$puntosAdicionales WHERE IdCliente=$idCliente";
            $stmt = $this->db->prepare($update);
            $updated = $stmt->execute();

            $idPreOrdens[] = $pre['IdPreOrden'];
        }
        // print_r($idPreOrdens);exit();
        $insert = "INSERT INTO Ve_DocVenta (IdDocVentaPuntoVenta,IdCliente,IdTipoDoc,IdAlmacen,Serie,Numero,FechaDoc,Anulado,FechaReg,UsuarioReg,Hash, EsCredito, FechaCredito, PagoCon, IdPreOrden)
        VALUES ($idDocVentaPuntoVenta, $idCliente, $idTipoDoc, $idAlmacen, '$serie', '$numero', '" . getNow() . "', $anulado, '" . getNow() . "', '$usuarioReg', UNIX_TIMESTAMP(), $esCredito, '$fechaCredito', '$pagoCon', '" . serialize($idPreOrdens) . "')";
    } else {
        $insert = "INSERT INTO Ve_DocVenta (IdDocVentaPuntoVenta,IdCliente,IdTipoDoc,IdAlmacen,Serie,Numero,FechaDoc,Anulado,FechaReg,UsuarioReg,Hash, EsCredito, FechaCredito, PagoCon)
        VALUES ($idDocVentaPuntoVenta, $idCliente, $idTipoDoc, $idAlmacen, '$serie', '$numero', '" . getNow() . "', $anulado, '" . getNow() . "', '$usuarioReg', UNIX_TIMESTAMP(), $esCredito, '$fechaCredito', '$pagoCon')";
    }*/

        // OBTENER EL SIGUIENTE NUMERO
        if ($codSunat == '07' || $codSunat == '08') { // 07 nota de credito, 08 nota de debito

            if ($codSunatModifica == '01') {
                $serie = str_replace("_", "F", $serie);
            }

            if ($codSunatModifica == '03') {
                $serie = str_replace("_", "B", $serie);
            }


            $selectNumero = "SELECT Numero + 1 AS NuevoNumero FROM Ve_DocVenta
            WHERE IdTipoDoc=$idTipoDoc AND Serie='$serie'
            ORDER BY Numero DESC LIMIT 1";
        } else {
            $selectNumero = "SELECT Numero + 1 AS NuevoNumero FROM Ve_DocVenta
            WHERE IdDocVentaPuntoVenta=$idDocVentaPuntoVenta AND IdTipoDoc=$idTipoDoc
            ORDER BY Numero DESC LIMIT 1";
        }


        $stmt = $this->db->query($selectNumero);
        $stmt->execute();
        $selectNumero = $stmt->fetch();
        $numero = $selectNumero['NuevoNumero'] ? $selectNumero['NuevoNumero'] : 1;

        try {
            if ($IdProforma == null) {
                $IdProforma = 0;
            }

            $insert = "INSERT INTO Ve_DocVenta (EntregadoOfertaFlag,IdDocVentaPuntoVenta,IdCliente,IdTipoDoc,IdAlmacen,Serie,Numero,FechaDoc,Anulado,FechaReg,UsuarioReg,Hash, EsCredito, FechaCredito, PagoCon, CampoDireccion, valorComision, IdComisionista, CodSunatModifica, NroComprobanteModifica, NotaIdMotivo, NotaDescMotivo,EsDelivery,DireccionEntrega,IdPedido,NombreLlevar,EsOrganizacion,NombreOrganizacion,IdProforma,Descripcion,Garantia)
        VALUES ($EntregadoOfertaFlag,$idDocVentaPuntoVenta, $idCliente, $idTipoDoc, $idAlmacen, '$serie', '$numero', '" . getNow() . "', $anulado, '" . getNow() . "', '$usuarioReg', UNIX_TIMESTAMP(), $esCredito, '$fechaCredito', '$pagoCon', '$campoDireccion', $valorComision, $idComisionista, '$codSunatModifica', '$nroComprobanteModifica', '$notaIdMotivo', '$notaDescMotivo',$esDelivery,'$direccionEntrega',$idPedido,'$nombreLlevar',$EsOrganizacion,'$nombreOrganizacion',$IdProforma,'$descripcion',$garantia)";

            $stmt = $this->db->prepare($insert);
            $inserted = $stmt->execute();
            $idDocVenta = $this->db->lastInsertId();
        } catch (Exception $e) {
            return $response->withJson([$e->getMessage()], 500);
        }
        // $yapeMonto



        // $totalAdelantos = 0;
        // foreach ($adelantosPreOrden as $key => $adelanto) {
        //     $totalAdelantos += $adelanto['Importe'];
        // }
        // END INSERTAR NUEVA VENTA
        // AGREGAR ADELANTOS DE PREORDEN A LA VENTA
        if (count($adelantosPreOrden) > 0) {
            foreach ($adelantosPreOrden as $key => $adelanto) {
                $totalAdelantos += $adelanto['Importe'];
                $insertAdelanto = $this->db->insert(array('IdCajaBanco', 'IdDocDet', 'Importe', 'Tipo'))
                    ->into('Cb_CajaBancoDet')
                    ->values(array($adelanto['IdCajaBanco'], $idDocVenta, $adelanto['Importe'], 'VE'));
                $insertIdAdelanto = $insertAdelanto->execute();
            }
        }

        // FIN AGREGAR ADELANTOS A LA VENTA

        if ($yapeMonto > 0) {

            $insert = "INSERT INTO 
                    Cb_CajaBanco (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, Anulado,  EsDelVendedor,  UsuarioReg) 
                    VALUES (7, 3, '" . getNow() . "','YAPE/PLIN - " . $serie . "-" . $numero . " Nro Operacion: ." . $yapeDescripcion . ".', $yapeMonto, 0,  1, '$usuarioReg')";
            $stmt = $this->db->prepare($insert);
            $inserted = $stmt->execute();
        }
        if ($visaMonto > 0) {

            $insert = "INSERT INTO 
                    Cb_CajaBanco (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, Anulado,  EsDelVendedor,  UsuarioReg) 
                    VALUES (5, 3, '" . getNow() . "','Tarjeta - " . $serie . "-" . $numero . " Nro Operacion: ." . $visaDescripcion . ".', $visaMonto, 0,  1, '$usuarioReg')";
            $stmt = $this->db->prepare($insert);
            $inserted = $stmt->execute();
        }
        if ($mastercardMonto > 0) {

            $insert = "INSERT INTO 
                    Cb_CajaBanco (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, Anulado,  EsDelVendedor,  UsuarioReg) 
                    VALUES (5, 3, '" . getNow() . "','Transferencia - " . $serie . "-" . $numero . " Nro Operacion: ." . $mastercardDescripcion . ".', $mastercardMonto, 0,  1, '$usuarioReg')";
            $stmt = $this->db->prepare($insert);
            $inserted = $stmt->execute();
        }



        // Actualizar Todas las PreOrden
        if ($idPreOrden) {
            foreach ($idPreOrden as $pre) {
                $update = "UPDATE Ve_PreOrden SET IdDocVenta=$idDocVenta WHERE IdPreOrden=" . $pre['IdPreOrden'];
                $stmt = $this->db->prepare($update);
                $updated = $stmt->execute();
                $idHabitacion = 0;


                $sql = "
                    SELECT 
                    p.IdProducto
                    FROM
                    Gen_Producto p  
                    JOIN Ve_PreOrdenDet od ON od.IdProducto = p.IdProducto
                    WHERE EsHabitacion=1 and  od.IdPreOrden = " . $pre['IdPreOrden'];
                $stmt = $this->db->query($sql);
                $stmt->execute();
                $data = $stmt->fetch();

                // $update = "UPDATE Gen_Producto SET EstadoHabitacion=1 WHERE IdProducto=" . $data['IdProducto'];
                // $stmt = $this->db->prepare($update);
                // $updated = $stmt->execute();




                foreach ($request->getParam('productos') as $producto) {
                    if (!$producto['EsHabitacion']) {
                        // $update = "UPDATE Ve_PreOrdenDet SET Cantidad=Cantidad-" . $producto['cantidad'] . " WHERE IdPreOrden=" . $pre['IdPreOrden'] . " AND IdProducto=" . $producto['IdProducto'];
                        // $stmt = $this->db->prepare($update);
                        // $updated = $stmt->execute();


                        $select = "SELECT * FROM Ve_PreOrdenDet WHERE IdPreOrden=" . $pre['IdPreOrden'] . " AND IdProducto=" . $producto['IdProducto'];
                        $stmt = $this->db->query($select);
                        $stmt->execute();
                        $data = $stmt->fetch(PDO::FETCH_ASSOC);


                        $insert = $this->db->insert(array('IdPreOrden', 'IdProducto', 'Monto'))
                            ->into('Ve_PreOrdenDetPago')
                            ->values(array($pre['IdPreOrden'], $producto['IdProducto'], $producto['total']));
                        $insertId = $insert->execute();
                        // if (isset($data['Cantidad']) && ($producto['cantidad'] > $data['Cantidad'])) {
                        //     $sql = "DELETE FROM Ve_PreOrdenDet WHERE IdPreOrden=" . $pre['IdPreOrden'] . " AND IdProducto=" . $producto['IdProducto'];
                        //     $stmt = $this->db->prepare($sql);
                        //     $deleted = $stmt->execute();
                        // }
                    } else {

                        $idHabitacion = $producto['IdProducto'];
                    }
                }

                $select = "SELECT COUNT(*) as total FROM Ve_PreOrdenDet
                            INNER JOIN Gen_Producto ON Ve_PreOrdenDet.IdProducto = Gen_Producto.IdProducto
                            WHERE Gen_Producto.EsHabitacion = 0 AND
                Ve_PreOrdenDet.IdPreOrden=" . $pre['IdPreOrden'] . " AND Ve_PreOrdenDet.Cantidad > 0";
                $stmt = $this->db->query($select);
                $stmt->execute();
                $data = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($data['total'] <= 0) {
                    // $update = "UPDATE Ve_PreOrden SET Anulado=0 WHERE IdPreOrden=" . $pre['IdPreOrden'];
                    // $stmt = $this->db->prepare($update);
                    // $updated = $stmt->execute();

                    $update = "UPDATE Gen_Producto SET EstadoHabitacion=1 WHERE IdProducto=" . $idHabitacion;
                    $stmt = $this->db->prepare($update);
                    $updated = $stmt->execute();
                }
            }
        }

        // START INSERTAR VENTA DETALLE
        $productos = $request->getParam('productos');
        $descuentoTotal = 0;
        foreach ($productos as $producto) {
            // if ($producto['total'] > 0) {
            $idProducto = $producto['IdProducto'];
            $EsPadre = $producto['EsPadre'];
            $cantidad = $producto['cantidad'];
            $precio = $producto['precio'];
            $precioCosto = $producto['PrecioCosto'];
            $descuento = $producto['descuento'] ? $producto['descuento'] : 0;
            $fechaAlquilerInicio = isset($producto['FechaAlquilerInicio']) ? $producto['FechaAlquilerInicio'] : (isset($producto['FechaAlquiler']) ? $producto['FechaAlquiler'] : null);
            $fechaAlquilerFin = isset($producto['FechaAlquilerFin']) ? $producto['FechaAlquilerFin'] : getNow();
            $descripcion = isset($producto['Descripcion']) ? $producto['Descripcion'] : '';
            $esManoDeObra = isset($producto['EsManoDeObra']) ? ($producto['EsManoDeObra'] == '' || $producto['EsManoDeObra'] == null ? 0 : $producto['EsManoDeObra']) : 0;
            $IdProductoSerie = isset($producto['IdProductoSerie']) ? $producto['IdProductoSerie'] : false;

            if ($IdProductoSerie) {
                $dml = "UPDATE Gen_Producto_Serie 
                            SET Estado=3,IdDocVenta=$idDocVenta,UsuarioMod='" . $vendedor . "',FechaMod='" . getNow() . "'  
                            WHERE IdProductoSerie=$IdProductoSerie";
                $stmt = $this->db->prepare($dml);
                $updated = $stmt->execute();
            }

            if ($EsPadre == 0) {
                $insert = $this->db->insert(array('IdDocVenta', 'IdProducto', 'Cantidad', 'Precio', 'PrecioCosto', 'Descuento', 'FechaAlquilerFin', 'Descripcion', 'FechaAlquilerInicio', 'EsManoDeObra'))
                    ->into('Ve_DocVentaDet')
                    ->values(array($idDocVenta, $idProducto, $cantidad, $precio, $precioCosto, $descuento, $fechaAlquilerFin, json_encode($descripcion), $fechaAlquilerInicio, $esManoDeObra));
                $insert->execute();
                $descuentoTotal += $descuento;
            }

            if ($EsPadre == 1) {
                $insert = $this->db->insert(array('IdDocVenta', 'IdProducto', 'Cantidad', 'Precio', 'PrecioCosto', 'Descuento',))
                    ->into('Ve_DocVentaHistorialKit')
                    ->values(array($idDocVenta, $idProducto, $cantidad, $precio, $precioCosto, $descuento));
                $insert->execute();
                $select = "SELECT * FROM Gen_ProductoDet WHERE IdProducto=$idProducto";
                $stmt = $this->db->query($select);
                $stmt->execute();
                $data = $stmt->fetchAll();



                foreach ($data as $detalle) {
                    $insert = $this->db->insert(array('IdDocVenta', 'IdProducto', 'Cantidad', 'Precio', 'PrecioCosto', 'Descuento', 'FechaAlquilerFin', 'Descripcion', 'FechaAlquilerInicio', 'EsManoDeObra'))
                        ->into('Ve_DocVentaDet')
                        ->values(array($idDocVenta, $detalle['IdProductoDet'], $detalle['Cantidad'], $detalle['Precio'], $precioCosto, $descuento, $fechaAlquilerFin, $detalle['Descripcion'], $fechaAlquilerInicio, $esManoDeObra));

                    $insert->execute();
                }
            }
        }
        // END VENTA DETALLE

        // START INSERTAR PAGO DETALLE
        $pagos = $request->getParam('pago');
        if (!$esCredito) {
            foreach ($pagos as $pago) {
                if ($pago['monto'] > 0) {
                    $idMetodoPago = $pago['IdMetodoPago'];
                    $importe = $pago['monto'];
                    $nroTarjeta = isset($pago['descripcion']) ? $pago['descripcion'] : '';
                    $insert = $this->db->insert(array('IdDocVenta', 'IdMetodoPago', 'Importe', 'NroTarjeta'))
                        ->into('Ve_DocVentaMetodoPagoDet')
                        ->values(array($idDocVenta, $idMetodoPago, $importe, $nroTarjeta));
                    $insert->execute();
                }
            }
        }
        // END PAGO DETALLE

        // START AÑADIR PUNTOS CLIENTE
        $puntosAplicados = $request->getParam('puntosAplicados');
        $totalCart = $request->getParam('totalCart');
        if (!$descuentoTotal) { // quitar condicional si siempre se otorgarán puntos
            // actualizar si la venta se pago en su totalidad, sin descuentos
            $select = "SELECT * FROM Ve_DocVentaDescuento WHERE IdDocVentaDescuento=1";
            $stmt = $this->db->query($select);
            $stmt->execute();
            $tablaDescuento = $stmt->fetch();

            if ($totalCart >= $tablaDescuento['PorCada']) {
                $puntosAdicionales = ($totalCart / $tablaDescuento['PorCada']) * $tablaDescuento['Puntos'];
                $puntosAdicionales =  $puntosAdicionales > 0 ? $puntosAdicionales : 0;
                $update = "UPDATE Ve_DocVentaCliente SET Puntos=Puntos+$puntosAdicionales WHERE IdCliente=$idCliente";
                $stmt = $this->db->prepare($update);
                $updated = $stmt->execute();
            }
        }
        if ($puntosAplicados) {
            $update = "UPDATE Ve_DocVentaCliente SET Puntos=Puntos-$puntosAplicados WHERE IdCliente=$idCliente";
            $stmt = $this->db->prepare($update);
            $updated = $stmt->execute();
        }
        // END PUNTOS CLIENTE

        // return $response->withJson(array('insertId' => $nroTarjeta));

        $data = array(
            'insertId' => $idDocVenta
        );

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson($e);
        //throw $th;
    }
});



$app->post('/proformas', function (Request $request, Response $response) {
    $idCliente = $request->getParam('cliente')['IdCliente'];
    $descripcion = $request->getParam('descripcion');
    $usuarioReg = isset($request->getParam('vendedor')['Usuario']) ? $request->getParam('vendedor')['Usuario'] : 'xx';
    $diasValidos = $request->getParam('diasValidosProf') ? $request->getParam('diasValidosProf') : 7;
    // Verificar si existe la proforma
    $select = "SELECT Numero, Anio FROM Ve_Proforma WHERE Anio='" . getNow("Y") . "' ORDER BY Numero DESC LIMIT 1";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $proforma = $stmt->fetch();

    $numero = $proforma['Numero'];
    $anio = $proforma['Anio'];

    $newNumero = 1;
    $newAnio = getNow("Y");

    if ($numero && $anio) {
        $newNumero = $numero + 1;
    }

    // START INSERTAR NUEVA PROFORMA
    $insert = "INSERT INTO Ve_Proforma(Anio, Numero, IdCliente, FechaReg, UsuarioReg, DiasValidos,Descripcion) VALUES($newAnio, $newNumero, $idCliente, '" . getNow() . "', '$usuarioReg', $diasValidos,'$descripcion')";

    $stmt = $this->db->prepare($insert);
    $inserted = $stmt->execute();
    $idProforma = $this->db->lastInsertId();
    // END INSERTAR NUEVA PROFORMA


    // START INSERTAR PROFORMA DETALLE
    $productos = $request->getParam('productos');
    //$descuentoTotal = 0;
    foreach ($productos as $producto) {
        // if ($producto['total'] > 0) {
        $idProducto     = $producto['IdProducto'];
        $descripcion    = $producto['Descripcion'];
        $cantidad       = $producto['cantidad'];
        $precio         = $producto['precio'];
        $descuento      = $producto['descuento'] ? $producto['descuento'] : 0;
        $disabledProf   = $producto['disabledProf'] > 0 ? 1 : 0;

        $insert = $this->db->insert(array('IdProforma', 'IdProducto', 'Descripcion', 'Cantidad', 'Precio', 'Descuento', 'DisabledTotal'))
            ->into('Ve_ProformaDet')
            ->values(array($idProforma, $idProducto, $descripcion, $cantidad, $precio, $descuento, $disabledProf));

        $insert->execute();
        //$descuentoTotal += $descuento;
        // }
    }
    // END PROFORMA DETALLE

    $data = array(
        'insertId' => $idProforma
    );

    return $response->withJson($data);
});

$app->get('/proformas/id/{id}', function (Request $request, Response $response, array $args) {
    try {
        //code...

        $select = "SELECT Ve_Proforma.*, Ve_DocVentaCliente.Cliente, Ve_DocVentaCliente.Direccion AS ClienteDireccion, Ve_DocVentaCliente.Email AS ClienteEmail,
    Ve_DocVentaCliente.DniRuc AS Documento, Ve_Proforma.DiasValidos
    
        FROM Ve_Proforma
        INNER JOIN Ve_DocVentaCliente ON Ve_Proforma.IdCliente = Ve_DocVentaCliente.IdCliente
        WHERE Ve_Proforma.IdProforma = '" . $args['id'] . "' ";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $proforma = $stmt->fetch();

        $select = "
            SELECT 
            Ve_ProformaDet.IdProforma,
            REPLACE(
                TRIM(BOTH '\"' FROM Ve_ProformaDet.Descripcion),
                '\\\\n', '\n'
            ) AS Descripcion,
            Ve_ProformaDet.DisabledTotal,
            Ve_ProformaDet.Cantidad,
            Ve_ProformaDet.Precio,
            Ve_ProformaDet.Descuento,
            Gen_Producto.*
            FROM Ve_ProformaDet
            INNER JOIN Gen_Producto 
            ON Ve_ProformaDet.IdProducto = Gen_Producto.IdProducto
            WHERE Ve_ProformaDet.IdProforma = '" . $args['id'] . "'";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $proformaDet = $stmt->fetchAll();

        $proforma['productos'] = $proformaDet;

        return $response->withJson($proforma);
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], 500);
        //throw $th;
    }
});



$app->get('/proformas/productos/{id}', function (Request $request, Response $response, array $args) {
    $select = "SELECT Ve_ProformaDet.IdProforma, Ve_ProformaDet.Cantidad, Ve_ProformaDet.Precio, Gen_Producto.* FROM Ve_ProformaDet
    INNER JOIN Gen_Producto ON Ve_ProformaDet.IdProducto = Gen_Producto.IdProducto
    WHERE Ve_ProformaDet.IdProforma = '" . $args['id'] . "'";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->get('/proformas', function (Request $request, Response $response) {
    $filter = $request->getParam('filter');
    $select = "
        SELECT Ve_Proforma.*, Ve_DocVentaCliente.Cliente,
         Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.Direccion, IFNULL(ROUND(SUM(Ve_ProformaDet.Cantidad * Ve_ProformaDet.Precio - IFNULL(Ve_ProformaDet.Descuento,0)),2),0) AS Total
        FROM Ve_Proforma
        INNER JOIN Ve_ProformaDet ON Ve_Proforma.IdProforma = Ve_ProformaDet.IdProforma
        LEFT JOIN Ve_DocVentaCliente ON Ve_Proforma.IdCliente = Ve_DocVentaCliente.IdCliente
        WHERE Ve_Proforma.Anulado = 0 AND COALESCE(Ve_Proforma.UsuarioReg,'') <> 'TIENDA'
        AND (Ve_DocVentaCliente.Cliente LIKE '%$filter%'
        OR Ve_DocVentaCliente.DniRuc LIKE '%$filter%'
        OR Ve_Proforma.Anio LIKE '%$filter%'
        OR Ve_Proforma.Numero LIKE '%$filter%'
        OR Ve_Proforma.FechaReg LIKE '%$filter%'
        OR Ve_Proforma.Descripcion LIKE '%$filter%'
        OR Ve_Proforma.UsuarioReg LIKE '%$filter%') ";

    $select .= " GROUP BY Ve_Proforma.IdProforma ORDER BY Ve_Proforma.IdProforma DESC";

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->get('/proformas/count', function (Request $request, Response $response, array $args) {
    $select = "SELECT COUNT(*) as total FROM Ve_Proforma WHERE COALESCE(Anulado,0)=0 AND COALESCE(UsuarioReg,'') <> 'TIENDA'";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});

$app->get('/proformas/detalle', function (Request $request, Response $response) {
    $idProforma = $request->getParam('idProforma');

    $select = "SELECT  Ve_ProformaDet.*, Gen_Producto.Producto  FROM Ve_ProformaDet 
     INNER JOIN Gen_Producto ON Ve_ProformaDet.IdProducto = Gen_Producto.IdProducto 
     WHERE IdProforma=$idProforma
     ORDER BY Ve_ProformaDet.IdProformaDet desc";
    // $select = "SELECT Ve_ProformaDet.*, Gen_Producto.Producto FROM Ve_ProformaDet 
    //     INNER JOIN Gen_Producto ON Ve_ProformaDet.IdProducto = Gen_Producto.IdProducto     
    //     WHERE IdProforma=$idProforma ";
    // $select = "SELECT * FROM Ve_ProformaDet AS proDet JOIN Ve_Proforma AS pro WHERE IdProforma=$idProforma";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

/* APIS DE GUIA */
$app->get('/remision/ultima', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Ve_DocVentaRemision ORDER BY Ve_DocVentaRemision.Numero DESC LIMIT 1";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $selectNumero = $stmt->fetch();
    $numero = $selectNumero['Numero'];
    return $response->withJson(array(
        "serie" => 'T002',
        "numero" => $numero ? $numero + 1 : 1
    ));
    return $response->withJson($data);
});

$app->get('/remision/ventas/busqueda', function (Request $request, Response $response) {
    $select = "SELECT Ve_DocVentaTipoDoc.CodSunat, CONCAT(Ve_DocVenta.Serie, '-', Ve_DocVenta.Numero) AS NroComprobante, 
        Ve_DocVenta.idDocVenta AS IdDocVenta FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc";
    $select .= " WHERE Ve_DocVentaTipoDoc.CodSunat IN ('01', '03') AND Ve_DocVenta.Anulado=0 AND
    CONCAT(Ve_DocVenta.Serie, '-', Ve_DocVenta.Numero) LIKE '%" . $request->getParam('q') . "%'  ";
    $select .= " LIMIT 10";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});

$app->get('/venta/detalle/comprobante/guia', function (Request $request, Response $response) {
    $idDocVenta = $request->getParam('idDocVenta');

    $select = "SELECT Ve_DocVentaDet.IdDocVentaDet,Ve_DocVentaDet.IdDocVenta,Ve_DocVentaDet.IdProducto,
    Ve_DocVentaDet.Cantidad AS cantidad,
    Ve_DocVentaDet.Precio AS precio, Ve_DocVentaDet.Precio as PrecioContado, Ve_DocVentaDet.Descuento AS descuento,Ve_DocVenta.IdCliente,
    Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica,Gen_ProductoMarca.ProductoMarca,Gen_ProductoMedicion.ProductoMedicion,
    Gen_Producto.Producto,
    Gen_Producto.ProductoDesc,
    Gen_Producto.ProductoDescCorto,
    Gen_Producto.CodigoBarra,
    Gen_Producto.Codigo,
    Gen_Producto.Dosis,
    Gen_Producto.PrecioPorMayor,
    Gen_Producto.StockPorMayor,
    Gen_Producto.StockMinimo,
    Gen_Producto.Anulado,
    Gen_Producto.FechaReg,
    Gen_Producto.Hash,
    Gen_Producto.ControlaStock,
    Ve_DocVentaDet.Precio AS PrecioCosto,
    Gen_Producto.VentaEstrategica,
    0 AS PorcentajeUtilidad,
    Gen_Producto.IdBloque,
    Gen_Producto.EsPadre
    FROM Ve_DocVentaDet 
    INNER JOIN Ve_DocVenta ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.IdDocVenta
    INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto 
    INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
    INNER JOIN Gen_ProductoFormaFarmaceutica ON Gen_Producto.IdProductoFormaFarmaceutica = Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica
    INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
    WHERE Ve_DocVenta.idDocVenta=$idDocVenta  ORDER BY Ve_DocVentaDet.IdDocVentaDet DESC ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});

$app->get('/remision', function (Request $request, Response $response) {
    $estado = $request->getParam('estado');
    $select = "SELECT * FROM Ve_DocVentaRemision WHERE Estado=$estado";

    $filter = $request->getParam('filter');
    if (isset($filter['fechaReferencia'])) {
        $select .= " AND FechaReg LIKE '" . $filter['fechaReferencia'] . "%'";
    }

    $stmt = $this->db->query($select);
    $stmt->execute();

    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/remision/detalle', function (Request $request, Response $response) {
    $idRemisiones = $request->getParam('idRemisiones');

    $select = "SELECT Ve_DocVentaRemisionDet.*, Gen_Producto.Producto,
        Gen_Producto.CodigoBarra, Gen_ProductoMedicion.Codigo AS CodigoMedicion, Gen_ProductoMedicion.ProductoMedicion
        FROM Ve_DocVentaRemisionDet
        INNER JOIN Gen_Producto ON Ve_DocVentaRemisionDet.IdProducto = Gen_Producto.IdProducto
        INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
        WHERE IdDocVentaRemision IN (" . implode(',', $idRemisiones) . ")";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});



$app->post('/remision', function (Request $request, Response $response) {
    $idGuiaRemision = $request->getParam('idGuiaRemision');
    $remision = $request->getParam('remision');

    $fechaReg = getNow();
    $fechaTraslado = $remision['fechaTraslado'];
    $observacion = $remision['observacion'];
    $codUndPesoBruto = $remision['codUndPesoBruto'] ?? 'KGM';
    $pesoBruto = $remision['pesoBruto'];
    $codModalidadTraslado = $remision['codModalidadTraslado'];
    $codMotivoTraslado = $remision['codMotivoTraslado'];
    $descripcionMotivoTraslado = $remision['descripcionMotivoTraslado'];
    $codUbigeoOrigen = trim($remision['codUbigeoOrigen']);
    $direccionOrigen = trim($remision['direccionOrigen']);
    $documentoReferencia = $remision['documentoReferencia'];

    $nroDocumentoCliente = $remision['nroDocumentoCliente'];
    $razonSocialCliente = $remision['razonSocialCliente'];
    $direccionCliente = preg_replace('/\s+/', ' ', $remision['direccionCliente']);
    $codUbigeoDestino = trim($remision['codUbigeoDestino']);
    $direccionDestino = trim($remision['direccionDestino']);

    $nroDocumentoTransportista = $remision['nroDocumentoTransportista'];
    $razonSocialTransportista = $remision['razonSocialTransportista'];
    $placaVehiculo = $remision['placaVehiculo'];
    $placaCarreta = $remision['placaCarreta'];
    $nroDocChofer = $remision['nroDocChofer'];
    $totalBultos = $remision['totalBultos'] ? $remision['totalBultos'] : 0;
    $licenciaChofer = $remision['licenciaChofer'] ? $remision['licenciaChofer'] : '';
    $apellidosChofer = $remision['apellidosChofer'] ? $remision['apellidosChofer'] : '';
    $nombresChofer = $remision['nombresChofer'] ? $remision['nombresChofer'] : '';

    if (trim($nroDocumentoTransportista) == NRO_DOCUMENTO_EMPRESA && $codMotivoTraslado != '02') {
        return $response->withJson(array("error" => 'EL DOCUMENTO DE LA EMPRESA DE TRANSPORTES NO DEBE SER IGUAL AL RUC DE LA EMPRESA'));
    }

    if ($idGuiaRemision) {
    } else {
        // OBTENER SERIE
        $selectSerie = "SELECT Serie, Numero + 1 AS NuevoNumero FROM Ve_DocVentaRemision ORDER BY Numero DESC LIMIT 1";
        $stmt = $this->db->query($selectSerie);

        $stmt->execute();
        $selectSerie = $stmt->fetch();
        $serie = $selectSerie['Serie'] ? $selectSerie['Serie'] : 'T002';
        $numero = $selectSerie['NuevoNumero'] ? $selectSerie['NuevoNumero'] : '1';

        // START INSERTAR GUIA REMISION
        $insert = "INSERT INTO Ve_DocVentaRemision(FechaReg, FechaTraslado, Observacion, CodUndPesoBruto, PesoBruto, CodModalidadTraslado, CodMotivoTraslado, DescripcionMotivoTraslado, CodUbigeoOrigen,
            DireccionOrigen, DocumentoReferencia, NroDocumentoCliente, RazonSocialCliente, DireccionCliente, CodUbigeoDestino, DireccionDestino, 
            NroDocumentoTransportista, RazonSocialTransportista, PlacaVehiculo, PlacaCarreta, NroDocChofer, Serie, Numero, TotalBultos, LicenciaChofer, NombresChofer, ApellidosChofer) 
            VALUES('$fechaReg', '$fechaTraslado', '$observacion', '$codUndPesoBruto', '$pesoBruto', '$codModalidadTraslado', '$codMotivoTraslado', '$descripcionMotivoTraslado', '$codUbigeoOrigen',
            '$direccionOrigen', '$documentoReferencia', '$nroDocumentoCliente', '$razonSocialCliente', '$direccionCliente', '$codUbigeoDestino', '$direccionDestino',
            '$nroDocumentoTransportista', '$razonSocialTransportista', '$placaVehiculo', '$placaCarreta', '$nroDocChofer','$serie', $numero, '$totalBultos', '$licenciaChofer', '$nombresChofer', '$apellidosChofer')";
        $stmt = $this->db->prepare($insert);
        $inserted = $stmt->execute();
        $insertId = $this->db->lastInsertId();

        // END REMISION

        // START INSERTAR REMISION DETALLE
        $productos = $request->getParam('productos');

        foreach ($productos as $producto) {
            $idProducto = $producto['IdProducto'];
            $cantidad = $producto['cantidad'];

            $insert = $this->db->insert(array('IdDocVentaRemision', 'IdProducto', 'Cantidad'))
                ->into('Ve_DocVentaRemisionDet')
                ->values(array($insertId, $idProducto, $cantidad));

            $insert->execute();
        }

        if ($remision['idDocVenta'] > 0) {
            $update = "UPDATE Ve_DocVenta SET GRE='" . $serie . '-' . $numero . "' WHERE idDocVenta=" . $remision['idDocVenta'];
            $stmt = $this->db->prepare($update);
            $updated = $stmt->execute();
        }

        return $response->withJson(array("idGuiaRemision" => $insertId));
    }
});

$app->post('/preorden/cajaybanco', function (Request $request, Response $response, array $args) {
    $idPreOrden = $request->getParam('IdPreOrden');
    $idHabitacion = $request->getParam('IdHabitacion');
    $adelanto = $request->getParam('Adelanto');
    $producto = $request->getParam('producto');
    $concepto = $request->getParam('concepto');
    $usuario = $request->getParam('Usuario');

    $vendedor = 'xx';
    if (isset($_SESSION['User'])) {
        $vendedor = $_SESSION['User'];
    }
    $usuarioReg = isset($usuario) ? $request->getParam('Usuario') : $vendedor;

    if ($idPreOrden) {
        $insert = "INSERT INTO Cb_CajaBanco (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, Anulado, IdProveedor, IdCliente, EsDelVendedor, IdPreOrden, UsuarioReg) VALUES (2, 1, '" . getNow() . "', '$concepto', $adelanto, 0, 0, $producto[IdCliente], 1, $idPreOrden, '$usuarioReg')";
        $stmt = $this->db->prepare($insert);
        $inserted = $stmt->execute();
        $idCajaBanco = $this->db->lastInsertId();

        if ($idCajaBanco) {
            $select = "SELECT * FROM Cb_CajaBanco where IdCajaBanco=$idCajaBanco";

            $stmt = $this->db->query($select);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $response->withJson($data);
        }
    }
});

$app->post('/preorden/cajaybanco/gasto', function (Request $request, Response $response, array $args) {
    $idPreOrden = $request->getParam('IdPreOrden');
    $idHabitacion = $request->getParam('IdHabitacion');
    $gasto = $request->getParam('Gasto');
    $producto = $request->getParam('producto');
    $concepto = $request->getParam('concepto');
    $usuario = $request->getParam('Usuario');

    $vendedor = 'xx';
    if (isset($_SESSION['User'])) {
        $vendedor = $_SESSION['User'];
    }
    $usuarioReg = isset($usuario) ? $request->getParam('Usuario') : $vendedor;

    if ($idPreOrden) {
        $insert = "INSERT INTO Cb_CajaBanco (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, Anulado, IdProveedor, IdCliente, EsDelVendedor, IdPreOrden, UsuarioReg) VALUES (3, 1, '" . getNow() . "', '$concepto', $gasto, 0, 0, $producto[IdCliente], 1, $idPreOrden, '$usuarioReg')";

        $stmt = $this->db->prepare($insert);
        $inserted = $stmt->execute();
        $idCajaBanco = $this->db->lastInsertId();
        if ($idCajaBanco) {
            $select = "SELECT * FROM Cb_CajaBanco where IdCajaBanco=$idCajaBanco";

            $stmt = $this->db->query($select);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $response->withJson($data);
        }
    }
});


$app->get('/preorden/cajaybanco', function (Request $request, Response $response, array $args) {
    $idPreOrden     = $request->getParam('IdPreOrden');
    $tipoCajaBanco  = $request->getParam('TipoCajaBanco');

    if ($idPreOrden) {
        $select = "SELECT Cb_CajaBanco.*, Cb_TipoCajaBanco.Tipo FROM Cb_CajaBanco 
            INNER JOIN Cb_TipoCajaBanco ON Cb_CajaBanco.IdTipoCajaBanco=Cb_TipoCajaBanco.IdTipoCajaBanco
            WHERE IdPreOrden=$idPreOrden AND Cb_TipoCajaBanco.Tipo = $tipoCajaBanco";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    }
});


$app->post('/preorden/detalle', function (Request $request, Response $response, array $args) {
    $idPreOrden = $request->getParam('IdPreOrden');
    $productos = $request->getParam('productos');
    $idHabitacion = $request->getParam('IdHabitacion');

    $idProforma = $request->getParam('IdProforma');

    if ($idProforma) {
        $sql = "UPDATE Ve_PreOrden SET IdProforma=$idProforma WHERE IdPreOrden=$idPreOrden ";

        $stmt = $this->db->prepare($sql);
        $updated = $stmt->execute();
    }

    if ($idPreOrden) {
        // Actualizar ProductoDet
        $sql = "DELETE FROM Ve_PreOrdenDet WHERE IdPreOrden='$idPreOrden' AND IdProducto != $idHabitacion";
        $stmt = $this->db->prepare($sql);
        $deleted = $stmt->execute();

        foreach ($productos as $prod) {
            $esManoDeObra = 0;
            if ($prod['EsManoDeObra'] === true || $prod['EsManoDeObra'] === 1) {
                $esManoDeObra = 1;
            }
            if ($prod['IdProducto'] != $idHabitacion) {
                $insertDet = $this->db->insert(array('IdPreOrden', 'IdProducto', 'Cantidad', 'Precio', 'EsManoDeObra'))
                    ->into('Ve_PreOrdenDet')
                    ->values(array($idPreOrden, $prod['IdProducto'], $prod['Cantidad'], $prod['Precio'], $esManoDeObra));
                $insertDetId = $insertDet->execute();
            }
        }
        return $response->withJson(array("affectedRows" => $productos));
    }
});

$app->get('/preorden/count', function (Request $request, Response $response, array $args) {
    $select = "SELECT COUNT(*) as total FROM Ve_PreOrden where Anulado=0";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});


$app->get('/preorden', function (Request $request, Response $response) {
    $filter = $request->getParam('filter');
    $select = "SELECT Ve_PreOrden.IdPreOrden, Ve_PreOrden.IdCliente, Ve_DocVentaCliente.Cliente, Ve_DocVentaCliente.DniRuc, Ve_PreOrden.FechaReg
        FROM Ve_PreOrden INNER JOIN Ve_DocVentaCliente ON Ve_PreOrden.IdCliente = Ve_DocVentaCliente.IdCliente";
    $select .= " WHERE Ve_PreOrden.Anulado=0 AND (Ve_DocVentaCliente.Cliente LIKE '%" . $filter . "%'
                 OR Ve_DocVentaCliente.DniRuc LIKE '" . $filter . "%')";
    $select .= " ORDER BY Ve_PreOrden.IdPreOrden DESC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/preorden/detalle', function (Request $request, Response $response) {
    $idPreOrden = $request->getParam('idPreOrden');
    try {
        //code...

        $select = "SELECT Gen_Producto.*, Ve_PreOrdenDet.Cantidad, IFNULL(Ve_PreOrdenDet.Precio, Gen_Producto.PrecioContado) AS Precio, Ve_PreOrdenDet.IdPreOrden, IFNULL(Ve_PreOrdenDet.EsManoDeObra, 0) AS EsManoDeObra,  Ve_PreOrdenDet.Pagado,
        COALESCE(SUM(Ve_PreOrdenDetPago.Monto),0) as MontoPagado,
        Ve_PreOrdenDet.Cantidad* IFNULL(Ve_PreOrdenDet.Precio, Gen_Producto.PrecioContado) - COALESCE(SUM(Ve_PreOrdenDetPago.Monto),0) as Deuda,
        Ve_PreOrdenDet.Cantidad* IFNULL(Ve_PreOrdenDet.Precio, Gen_Producto.PrecioContado) - COALESCE(SUM(Ve_PreOrdenDetPago.Monto),0) <= 0 as Pagado
        FROM Ve_PreOrdenDet
        INNER JOIN Gen_Producto ON Ve_PreOrdenDet.IdProducto = Gen_Producto.IdProducto
        LEFT JOIN Ve_PreOrdenDetPago ON Ve_PreOrdenDetPago.IdProducto = Ve_PreOrdenDet.IdProducto AND Ve_PreOrdenDetPago.IdPreOrden = Ve_PreOrdenDet.IdPreOrden
        WHERE Ve_PreOrdenDet.IdPreOrden=$idPreOrden 
        GROUP BY Ve_PreOrdenDet.IdProducto, Ve_PreOrdenDet.IdPreOrden
        ORDER BY Ve_PreOrdenDet.IdProducto DESC
        ";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});

$app->get('/preorden/proforma', function (Request $request, Response $response) {
    $idProforma = $request->getParam('idProforma');

    if ($idProforma) {
        $select = "SELECT Ve_Proforma.*, SUM(Ve_ProformaDet.Cantidad * Ve_ProformaDet.Precio) AS Total
            FROM Ve_Proforma
            INNER JOIN Ve_ProformaDet ON Ve_Proforma.IdProforma = Ve_ProformaDet.IdProforma
            WHERE Ve_Proforma.IdProforma=$idProforma
            GROUP BY Ve_Proforma.IdProforma";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetch();

        return $response->withJson($data);
    }
});


$app->get('/cliente', function (Request $request, Response $response, array $args) {
    $idCliente = $request->getParam('idCliente');
    $dni = $request->getParam('dni');

    if ($dni) {
        $select = "SELECT * FROM Ve_DocVentaCliente WHERE DniRuc='$dni'";
    } else {
        $select = "SELECT * FROM Ve_DocVentaCliente WHERE IdCliente='$idCliente'";
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/cliente/deudas', function (Request $request, Response $response, array $args) {
    $idCliente = $request->getParam('idCliente');

    if ($idCliente) {
        $select = "SELECT
        Ve_DocVenta.IdCliente,
        Ve_DocVenta.IdDocVenta,
        Ve_DocVenta.FechaDoc,
        Ve_DocVenta.FechaCredito,
        Ve_DocVenta.IdTipoDoc,
        CONCAT(Ve_DocVenta.Serie,'-', CONVERT(Ve_DocVenta.Numero, CHAR)) AS SerieNumero,
        CONCAT(CASE WHEN Ve_DocVentaTipoDoc.CodSunat ='01' THEN
            'F'
        ELSE
            CASE WHEN Ve_DocVentaTipoDoc.CodSunat='03' THEN
                'B'
            ELSE
                CASE WHEN Ve_DocVentaTipoDoc.CodSunat='12' THEN
                    'T'
                ELSE
                    'OTRO'
                END
            END
        END,Ve_DocVenta.Serie,'-' , CONVERT(Ve_DocVenta.Numero, CHAR) )AS Correlativo,
        ROUND(SUM((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento ),2) AS Total,
        ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='VE' AND Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)),2) AS Aplicado,
        
        ROUND( (SELECT IFNULL((SELECT SUM((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento)FROM
        Ve_DocVentaDet INNER JOIN Ve_DocVenta ON Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.IdDocVenta WHERE Ve_DocVenta.IdTipoDoc=9 AND Ve_DocVenta.NroComprobanteModifica = SerieNumero AND Ve_DocVenta.Anulado=0  ),0)
        ),2) AS NotaCreApli,
            ROUND(SUM((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento ),2) -
        ROUND((SELECT NotaCreApli) ,2)	-
            ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='VE' AND Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)),2) AS Saldo,
        DocVentaNotas.Notas AS NotasCredito
        
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.IdDocVenta=Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc=Ve_DocVentaTipoDoc.IdTipoDoc
        LEFT JOIN (SELECT Ve_DocVenta.NroComprobanteModifica,
        CONCAT( 
        '[', 
            GROUP_CONCAT( CONCAT( '{ \"IdDocVenta\":', Ve_DocVenta.idDocVenta ,',\"FechaDoc\":\"', Ve_DocVenta.FechaDoc , '\",\"Serie\":\"', Ve_DocVenta.Serie, '\",\"Numero\":',Ve_DocVenta.Numero,',\"Total\":',ROUND(((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento ),2),' }' ) SEPARATOR ', '),
            ']'
        ) AS Notas 
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
        WHERE Ve_DocVenta.idTipoDoc=9 AND Ve_DocVenta.Anulado=0
        GROUP BY Ve_DocVenta.NroComprobanteModifica )  AS DocVentaNotas ON DocVentaNotas.NroComprobanteModifica = CONCAT(Ve_DocVenta.Serie,'-',Ve_DocVenta.Numero)
        
        
        
        WHERE Ve_DocVenta.IdCliente=$idCliente AND Ve_DocVenta.EsCredito=1 AND Ve_DocVenta.Anulado=0
        GROUP BY
        Ve_DocVenta.IdDocVenta,
        Ve_DocVenta.FechaDoc,
        Ve_DocVenta.FechaCredito,
        Ve_DocVentaTipoDoc.CodSunat,
        Ve_DocVenta.Serie,
        Ve_DocVenta.Numero
        HAVING ROUND(Saldo,2)>0
        
        ORDER BY FechaDoc;";
    } else {

        $select = "SELECT
        Ve_DocVenta.IdCliente,
        Ve_DocVentaCliente.DniRuc,
        Ve_DocVentaCliente.Cliente,
        Ve_DocVenta.IdDocVenta,
        Ve_DocVenta.FechaDoc,
        Ve_DocVenta.FechaCredito,
        Ve_DocVenta.IdTipoDoc,
        CONCAT(Ve_DocVenta.Serie,'-', CONVERT(Ve_DocVenta.Numero, CHAR)) AS SerieNumero,
        CONCAT(CASE WHEN Ve_DocVentaTipoDoc.CodSunat ='01' THEN
            'F'
        ELSE
            CASE WHEN Ve_DocVentaTipoDoc.CodSunat='03' THEN
                'B'
            ELSE
                CASE WHEN Ve_DocVentaTipoDoc.CodSunat='12' THEN
                    'T'
                ELSE
                    'OTRO'
                END
            END
        END,Ve_DocVenta.Serie,'-' , CONVERT(Ve_DocVenta.Numero, CHAR) )AS Correlativo,
        
        ROUND(SUM((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento ),2) AS Total,
        ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='VE' AND Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)),2) AS Aplicado,
        
        ROUND( (SELECT IFNULL((SELECT SUM((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento)FROM
        Ve_DocVentaDet INNER JOIN Ve_DocVenta ON Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.IdDocVenta WHERE Ve_DocVenta.IdTipoDoc=9 AND Ve_DocVenta.NroComprobanteModifica = SerieNumero AND Ve_DocVenta.Anulado=0  ),0)
        ),2) AS NotaCreApli,
            ROUND(SUM((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento ),2) -
        ROUND((SELECT NotaCreApli) ,2)	-
            ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='VE' AND Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)),2) AS Saldo,
        DocVentaNotas.Notas AS NotasCredito
        
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.IdDocVenta=Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc=Ve_DocVentaTipoDoc.IdTipoDoc
        INNER JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente
        LEFT JOIN (SELECT Ve_DocVenta.NroComprobanteModifica,
        CONCAT( 
        '[', 
            GROUP_CONCAT( CONCAT( '{ \"IdDocVenta\":', Ve_DocVenta.idDocVenta ,',\"FechaDoc\":\"', Ve_DocVenta.FechaDoc , '\",\"Serie\":\"', Ve_DocVenta.Serie, '\",\"Numero\":',Ve_DocVenta.Numero,',\"Total\":',ROUND(((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento ),2),' }' ) SEPARATOR ', '),
            ']'
        ) AS Notas 
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
        WHERE Ve_DocVenta.idTipoDoc=9 AND Ve_DocVenta.Anulado=0
        GROUP BY Ve_DocVenta.NroComprobanteModifica )  AS DocVentaNotas ON DocVentaNotas.NroComprobanteModifica = CONCAT(Ve_DocVenta.Serie,'-',Ve_DocVenta.Numero)
            
        WHERE Ve_DocVenta.EsCredito=1 AND Ve_DocVenta.Anulado=0 ";

        $select .= " AND Ve_DocVenta.IdAlmacen = " . $request->getParam('idalmacen');
        $select .= " AND (Ve_DocVentaCliente.Cliente LIKE '%" . $request->getParam('q') . "%'";
        $select .= " OR Ve_DocVentaCliente.DniRuc LIKE '%" . $request->getParam('q') . "%') ";

        $select .= "GROUP BY
        Ve_DocVenta.IdDocVenta,
        Ve_DocVenta.FechaDoc,
        Ve_DocVenta.FechaCredito,
        Ve_DocVentaTipoDoc.CodSunat,
        Ve_DocVenta.Serie,
        Ve_DocVenta.Numero
        HAVING ROUND(Saldo,2)>0
        
        ORDER BY FechaDoc";

        $limit = $request->getParam('limit') ? $request->getParam('limit') :  30;
        if ($limit) {
            $offset = 0;
            if ($request->getParam('page')) {
                $page = $request->getParam('page');
                $offset = (--$page) * $limit;
            }
            $select .= " LIMIT " . $limit;
            $select .= " OFFSET " . $offset;
        }
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/cuentas', function (Request $request, Response $response, array $args) {
    $select = "SELECT IdCuenta, Cuenta, Anulado FROM Cb_Cuenta WHERE Anulado=0";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});

$app->get('/cliente/pagos', function (Request $request, Response $response, array $args) {
    $idCliente = $request->getParam('idCliente');
    $select = "SELECT Cb_CajaBanco.*,Cb_Cuenta.Cuenta FROM Cb_CajaBanco INNER JOIN Cb_Cuenta ON Cb_CajaBanco.IdCuenta = Cb_Cuenta.IdCuenta
    WHERE Cb_CajaBanco.IdCliente=$idCliente AND Cb_CajaBanco.EsDelVendedor=1 ORDER BY Cb_CajaBanco.FechaDoc DESC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/clientes/deuda/count', function (Request $request, Response $response, array $args) {
    $select = "SELECT COUNT(*) as total
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet On Ve_DocVenta.IdDocVenta=Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaTipoDoc On Ve_DocVenta.IdTipoDoc=Ve_DocVentaTipoDoc.IdTipoDoc
        INNER JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente
        WHERE EsCredito=1";
    $select .= " AND Ve_DocVenta.IdAlmacen = " . $request->getParam('idalmacen');
    $select .= "
        GROUP BY
        Ve_DocVenta.IdDocVenta,
        Ve_DocVenta.FechaDoc,
        Ve_DocVenta.FechaCredito,
        Ve_DocVentaTipoDoc.CodSunat,
        Ve_DocVenta.Serie,
        Ve_DocVenta.Numero
        HAVING ROUND(SUM((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento),2) -
        ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='VE' AND Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)),2)>0
        ORDER BY FechaDoc;";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});

$app->post('/cliente/deudas/pagar', function (Request $request, Response $response) {
    $cajaBanco = $request->getParam('cajaBanco');
    $documentos = $request->getParam('documentos');
    $cuentas = $request->getParam('cuentas');
    $vendedor = 'xx';
    if (isset($_SESSION['user'])) {
        $vendedor = $_SESSION['user'];
    }
    $usuarioReg = isset($request->getParam('vendedor')['Usuario']) ? $request->getParam('vendedor')['Usuario'] : $vendedor;


    foreach ($cuentas as $cuenta) {
        $importe = 0;
        foreach ($documentos as $doc) {

            if ($doc['cuenta'] == $cuenta) {
                if (isset($doc['aplicar']) && $doc['aplicar'] > 0) {
                    $importe += $doc['aplicar'];
                }
            }
        }

        if ($importe > 0) {
            $insert = $this->db->insert(array('IdTipoCajaBanco', 'IdCuenta', 'FechaDoc', 'Concepto', 'Importe', 'Anulado', 'UsuarioReg', 'IdCliente', 'EsDelVendedor'))
                ->into('Cb_CajaBanco')
                ->values(array(2, $cuenta, getNow(), $cajaBanco['Concepto'], $importe, 0, $usuarioReg, $cajaBanco['cliente']['IdCliente'], 1));
            $insertId = $insert->execute();

            foreach ($documentos as $doc) {
                if (isset($doc['aplicar']) && $doc['aplicar'] > 0 && $doc['cuenta'] == $cuenta) {
                    $insertDet = $this->db->insert(array('IdCajaBanco', 'IdDocDet', 'Importe', 'Tipo'))
                        ->into('Cb_CajaBancoDet')
                        ->values(array($insertId, $doc['IdDocVenta'], $doc['aplicar'], 'VE'));
                    $insertDetId = $insertDet->execute();
                }
            }
        }
    }

    return $response->withJson(array(
        "idCajaBanco" => $insertId
    ));
});

$app->get('/proveedores/deuda/count', function (Request $request, Response $response, array $args) {
    $select = "SELECT COUNT(*) AS total
    From Lo_Movimiento
    INNER JOIN Lo_MovimientoDetalle ON Lo_Movimiento.Hash=Lo_MovimientoDetalle.hashMovimiento
    INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo=Lo_MovimientoTipo.IdMovimientoTipo
    INNER JOIN Lo_Proveedor ON Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
    Where EsCredito=1
    Group by
    Lo_Movimiento.Hash,
    Lo_Movimiento.MovimientoFecha,
    Lo_Movimiento.FechaVenCredito,
    Lo_MovimientoTipo.CodSunat,
    Lo_Movimiento.Serie,
    Lo_Movimiento.Numero
    HAVING Round(Sum(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) -
    Round((SELECT IfNull((Select Sum(Cb_CajaBancoDet.Importe) From Cb_CajaBancoDet Where Tipo='MO' And Cb_CajaBancoDet.Hash=Lo_Movimiento.Hash),0)),2)>0
    Order by MovimientoFecha;";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});

$app->get('/proveedores/deudas', function (Request $request, Response $response, array $args) {
    $select = "SELECT
    Lo_Movimiento.Hash as Id,
    Lo_Proveedor.Ruc,
    Lo_Proveedor.Proveedor,
    Lo_Movimiento.MovimientoFecha as FechaDoc,
    Lo_Movimiento.FechaVenCredito as FechaCredito,
    CONCAT(CASE WHEN Lo_MovimientoTipo.CodSunat ='01' THEN
        'F'
    ELSE
        CASE WHEN Lo_MovimientoTipo.CodSunat='03' THEN
            'B'
        ELSE
            CASE WHEN Lo_MovimientoTipo.CodSunat='12' THEN
                'T'
            ELSE
                'OTRO'
            END
        END
    END,Lo_Movimiento.Serie,'-' , convert(Lo_Movimiento.Numero, char) )as Correlativo,
    Round(Sum(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) as Total,
    Round((SELECT IfNull((SELECT Sum(Cb_CajaBancoDet.Importe) From Cb_CajaBancoDet Where Tipo='MO' And Cb_CajaBancoDet.Hash=Lo_Movimiento.Hash),0)),2) as Aplicado,
    Round(SUM(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) -
    Round((SELECT IfNull((SELECT Sum(Cb_CajaBancoDet.Importe) From Cb_CajaBancoDet Where Tipo='MO' And Cb_CajaBancoDet.Hash=Lo_Movimiento.Hash),0)),2) as Saldo
    From Lo_Movimiento
    Inner Join Lo_MovimientoDetalle On Lo_Movimiento.Hash=Lo_MovimientoDetalle.hashMovimiento
    Inner Join Lo_MovimientoTipo On Lo_Movimiento.IdMovimientoTipo=Lo_MovimientoTipo.IdMovimientoTipo
    INNER JOIN Lo_Proveedor On Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
    Where EsCredito=1";

    $select .= " AND (Lo_Proveedor.Ruc LIKE '%" . $request->getParam('q') . "%'";
    $select .= " OR Lo_Proveedor.Proveedor LIKE '%" . $request->getParam('q') . "%') ";

    $select .= "
    Group by
    Lo_Movimiento.Hash,
    Lo_Movimiento.MovimientoFecha,
    Lo_Movimiento.FechaVenCredito,
    Lo_MovimientoTipo.CodSunat,
    Lo_Movimiento.Serie,
    Lo_Movimiento.Numero
    HAVING Round(Sum(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) -
    Round((SELECT IfNull((Select Sum(Cb_CajaBancoDet.Importe) From Cb_CajaBancoDet Where Tipo='MO' And Cb_CajaBancoDet.Hash=Lo_Movimiento.Hash),0)),2)>0
    Order by MovimientoFecha";

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  15;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

//Reporte Utilidad Bruta SbVe_ReporteUtilidadBruta
$app->get('/reporte/utlidadbruta_operador', function (Request $request, Response $response, array $args) {
    try {
        //code...

        $desde = $request->getParam('desde');
        $hasta = $request->getParam('hasta');
        $idAlmacen = $request->getParam('idAlmacen');
        $idUsuario = $request->getParam('idUsuario');

        $select = "SELECT
	Gen_Producto.IdProducto,
	CONCAT(SUBSTRING(Gen_Producto.Producto,1,15),'...') Producto,
    Ve_DocVenta.UsuarioReg,
	SUM(Ve_DocVentaDet.Cantidad) AS Cantidad,
    ROUND(SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto))/SUM(Ve_DocVentaDet.Cantidad),2) AS PrecioCosto,
	ROUND(SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio)/SUM(Ve_DocVentaDet.Cantidad), 2)  AS PrecioVenta,
	ROUND(SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio), 2) AS TotalVenta,
	ROUND(SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto)), 2) AS TotalCosto,
	ROUND(SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio)  -  SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto)), 2) AS UtilidadBruta,
    IFNULL(ROUND((ROUND(SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio)  -  SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto)), 2) * 100
    / ROUND(SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto))/SUM(Ve_DocVentaDet.Cantidad),2) / 100 /
    SUM(Ve_DocVentaDet.Cantidad) *100),0),0) AS PorcentajeUti
	FROM Ve_DocVenta
	INNER JOIN Ve_DocVentaDet ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta
	INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
    WHERE true  AND Ve_DocVenta.FechaDoc >= '$desde 00:00:00'  AND Ve_DocVenta.FechaDoc <= '$hasta 23:59:59' ";
        if ($idAlmacen) {
            $select .= " AND Ve_DocVenta.IdAlmacen = $idAlmacen";
        }
        if ($idUsuario) {
            $select .= " AND Ve_DocVenta.UsuarioReg = '$idUsuario'";
        }

        if (!empty($almacen)) {
            $select  .= " AND Ve_DocVenta.IdAlmacen = $almacen ";
        }

        $select .= " GROUP BY Gen_Producto.IdProducto";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson($e);
        //throw $th;
    }
});

$app->get('/reporte/utlidadbruta', function (Request $request, Response $response, array $args) {

    $fechaInicio = $request->getParam('fechaInicio');
    $fechaFin = $request->getParam('fechaFin');
    $almacen = $request->getParam('almacen');

    $select = "SELECT
	Gen_Producto.IdProducto,
	CONCAT(SUBSTRING(Gen_Producto.Producto,1,15),'...') Producto,
	SUM(Ve_DocVentaDet.Cantidad) AS Cantidad,
    ROUND(SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto))/SUM(Ve_DocVentaDet.Cantidad),2) AS PrecioCosto,
	ROUND(SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio)/SUM(Ve_DocVentaDet.Cantidad), 2)  AS PrecioVenta,
	ROUND(SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio), 2) AS TotalVenta,
	ROUND(SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto)), 2) AS TotalCosto,
	ROUND(SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio)  -  SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto)), 2) AS UtilidadBruta,
    IFNULL(ROUND((ROUND(SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio)  -  SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto)), 2) * 100
    / ROUND(SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto))/SUM(Ve_DocVentaDet.Cantidad),2) / 100 /
    SUM(Ve_DocVentaDet.Cantidad) *100),0),0) AS PorcentajeUti
	FROM Ve_DocVenta
	INNER JOIN Ve_DocVentaDet ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta
	INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
	WHERE Ve_DocVenta.Anulado=0 AND Ve_DocVenta.IdTipoDoc <> 9 AND Ve_DocVenta.FechaDoc BETWEEN '$fechaInicio 00:00:00' AND '$fechaFin 23:59:59' ";

    if (!empty($almacen)) {
        $select  .= " AND Ve_DocVenta.IdAlmacen = $almacen ";
    }

    $select .= " GROUP BY Gen_Producto.IdProducto";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/reporte/deudaclientes2', function (Request $request, Response $response, array $args) use ($app) {
    $idalmacen = $request->getParam('idalmacen');
    $res = $app->subRequest('GET', 'cliente/deudas', 'idalmacen=' . $idalmacen);


    $deudacliente = (string) $res->getBody();
    $deudacliente = json_decode($deudacliente, true);

    $excel = new Spreadsheet();
    //$sheet = $excel->setActiveSheetIndex(0);
    $sheet = $excel->getActiveSheet();
    $sheet->setCellValue('A1', 'DNI / RUC');
    $sheet->setCellValue('B1', 'CLIENTE');
    $sheet->setCellValue('C1', 'SERIE');
    $sheet->setCellValue('D1', 'TOTAL');
    $sheet->setCellValue('E1', 'APLICADO');
    $sheet->setCellValue('F1', 'APLICADO NOTA CRED.');
    $sheet->setCellValue('G1', 'SALDO');
    $sheet->setCellValue('H1', 'FECHA EMISION');
    $sheet->setCellValue('I1', 'FECHA VENCIMIENTO');
    $cont = 3;
    foreach ($deudacliente as $prod) {
        $sheet->setCellValue('A' . $cont, $prod['DniRuc']);
        $sheet->setCellValue('B' . $cont, $prod['Cliente']);
        $sheet->setCellValue('C' . $cont, $prod['Correlativo']);
        $sheet->setCellValue('D' . $cont, 'S/. ' . $prod['Total']);
        $sheet->setCellValue('E' . $cont, 'S/. ' . $prod['Aplicado']);
        $sheet->setCellValue('F' . $cont, 'S/. ' . $prod['NotaCreApli']);
        $sheet->setCellValue('G' . $cont, 'S/. ' . $prod['Saldo']);
        $sheet->setCellValue('H' . $cont, date("d/m/Y", strtotime($prod['FechaDoc'])));
        $sheet->setCellValue('I' . $cont, date("d/m/Y", strtotime($prod['FechaCredito'])));
        $cont += 1;
    }

    $excelWriter = new Xlsx($excel);

    $fileName = 'deudacliente2' . getNow('Y-m-d-H-i-s') .  '.xlsx';
    $excelFileName = __DIR__ . '/reporte/' . $fileName;
    $excelWriter->save($excelFileName);

    echo "<script>window.location.href = '/api/reporte/" . $fileName . "'</script>";
    exit;
});

$app->get('/reporte/deudaproveedores', function (Request $request, Response $response, array $args) use ($app) {

    $res = $app->subRequest('GET', 'proveedores/deudas');


    $deudacliente = (string) $res->getBody();
    $deudacliente = json_decode($deudacliente, true);

    $excel = new Spreadsheet();
    //$sheet = $excel->setActiveSheetIndex(0);
    $sheet = $excel->getActiveSheet();
    $sheet->setCellValue('A1', 'DNI / RUC');
    $sheet->setCellValue('B1', 'PROVEEDOR');
    $sheet->setCellValue('C1', 'SERIE');
    $sheet->setCellValue('D1', 'TOTAL');
    $sheet->setCellValue('E1', 'APLICADO');
    $sheet->setCellValue('F1', 'SALDO');
    $sheet->setCellValue('G1', 'FECHA EMISION');
    $sheet->setCellValue('H1', 'FECHA VENCIMIENTO');

    $cont = 3;
    foreach ($deudacliente as $prod) {
        $sheet->setCellValue('A' . $cont, $prod['Ruc']);
        $sheet->setCellValue('B' . $cont, $prod['Proveedor']);
        $sheet->setCellValue('C' . $cont, $prod['Correlativo']);
        $sheet->setCellValue('D' . $cont, 'S/. ' . $prod['Total']);
        $sheet->setCellValue('E' . $cont, 'S/. ' . $prod['Aplicado']);
        $sheet->setCellValue('F' . $cont, 'S/. ' . $prod['Saldo']);
        $sheet->setCellValue('G' . $cont, date("d/m/Y", strtotime($prod['FechaDoc'])));
        $sheet->setCellValue('H' . $cont, date("d/m/Y", strtotime($prod['FechaCredito'])));
        $cont += 1;
    }

    $excelWriter = new Xlsx($excel);

    $fileName = 'deudaproveedores' . getNow('Y-m-d-H-i-s') .  '.xlsx';
    $excelFileName = __DIR__ . '/reporte/' . $fileName;
    $excelWriter->save($excelFileName);

    echo "<script>window.location.href = '/api/reporte/" . $fileName . "'</script>";
    exit;
});

$app->get('/ranking/clientes', function (Request $request, Response $response, array $args) {
    $select = "SELECT
        Ve_DocVentaCliente.DniRuc,
        Ve_DocVentaCliente.Cliente,
        Ve_DocVentaCliente.Puntos,
        Ve_DocVentaCliente.FechaNacimiento,
        Ve_DocVenta.IdDocVenta,
        Round(Sum((Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento ),2) as Total
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.IdDocVenta=Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente =Ve_DocVentaCliente.IdCliente";
    $select .= " WHERE Ve_DocVenta.IdAlmacen = " . $request->getParam('idalmacen');
    $select .= " AND (Ve_DocVentaCliente.Cliente LIKE '%" . $request->getParam('q') . "%'";
    $select .= " OR Ve_DocVentaCliente.DniRuc LIKE '%" . $request->getParam('q') . "%') ";

    $select .= "GROUP BY Ve_DocVentaCliente.DniRuc
                     ORDER BY Total DESC, Ve_DocVentaCliente.Puntos DESC";

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  15;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/ranking/clientes/count', function (Request $request, Response $response, array $args) {
    $select  = "SELECT COUNT(*) as total
                FROM Ve_DocVenta
                INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.IdDocVenta=Ve_DocVentaDet.IdDocVenta
                INNER JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente =Ve_DocVentaCliente.IdCliente";
    $select .= " WHERE Ve_DocVenta.IdAlmacen = " . $request->getParam('idalmacen');
    $select .= " GROUP BY Ve_DocVentaCliente.DniRuc
                ORDER BY Total DESC, Ve_DocVentaCliente.Puntos DESC";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});

$app->get('/clientes/count', function (Request $request, Response $response, array $args) {

    $select = "SELECT COUNT(*) AS total FROM Ve_DocVentaCliente";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});

$app->get('/clientes/comisionistas/count', function (Request $request, Response $response, array $args) {
    $idAlmacen = $request->getParam('idAlmacen');

    $select = "SELECT
	    COUNT(DISTINCT(Ve_DocVentaCliente.IdCliente)) AS total
        FROM
            Ve_DocVentaCliente
        INNER JOIN Ve_DocVenta ON
            Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente
        WHERE
            Ve_DocVentaCliente.esComisionista = 1
            AND Ve_DocVenta.valorComision IS NOT NULL ";

    if ($idAlmacen) {
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});

$app->get('/cliente/comisionistas', function (Request $request, Response $response, array $args) {
    $idAlmacen = $request->getParam('idAlmacen');

    $select = "SELECT Ve_DocVentaCliente.IdCliente, Ve_DocVentaCliente.Cliente, Ve_DocVentaCliente.DniRuc, 
        ROUND(
            SUM(((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - Ve_DocVentaDet.Descuento) * Ve_DocVenta.valorComision/100)
            -
            IFNULL((SELECT SUM(Importe) FROM Cb_CajaBanco
            WHERE IdCliente = Ve_DocVentaCliente.IdCliente AND Cb_CajaBanco.IdTipoCajaBanco = 7), 0)
        , 2) as Comision
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta = Ve_DocVentaDet.IdDocVenta
        INNER JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdComisionista = Ve_DocVentaCliente.IdCliente
        WHERE Ve_DocVentaCliente.esComisionista = 1 AND Ve_DocVenta.valorComision IS NOT NULL ";

    if ($idAlmacen) {
        $select .= " AND Ve_DocVenta.IdAlmacen = $idAlmacen ";
    }

    $select .= " GROUP BY Ve_DocVentaCliente.IdCliente";

    $limit = $request->getParam('limit');
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/cliente/comisionistas/pagos', function (Request $request, Response $response, array $args) {
    $idComisionista = $request->getParam('idComisionista');

    $select = "SELECT Cb_CajaBanco.*, Cb_TipoCajaBanco.TipoCajaBanco, Cb_CajaBanco.FechaDoc AS Fecha FROM Cb_CajaBanco
        INNER JOIN Cb_TipoCajaBanco ON Cb_CajaBanco.IdTipoCajaBanco = Cb_TipoCajaBanco.IdTipoCajabanco
        WHERE IdCliente = $idComisionista AND Cb_CajaBanco.IdTipoCajaBanco = 7";

    // $limit = $request->getParam('limit') ? $request->getParam('limit') :  15;
    // if ($limit) {
    //     $offset = 0;
    //     if ($request->getParam('page')) {
    //         $page = $request->getParam('page');
    //         $offset = (--$page) * $limit;
    //     }
    //     $select .= " LIMIT " . $limit;
    //     $select .= " OFFSET " . $offset;
    // }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->post('/cliente/comisionistas/pagar', function (Request $request, Response $response) {
    $cliente = $request->getParam('cliente');
    $importe = $request->getParam('importe');
    $usuario = $_SESSION['user'];

    $insert = $this->db->insert(array('IdTipoCajaBanco', 'IdCuenta', 'FechaDoc', 'Concepto', 'Importe', 'UsuarioReg', 'IdCliente'))
        ->into('Cb_CajaBanco')
        ->values(array(7, 1, getNow(), 'Pago a comisionista ' . $cliente['Cliente'], $importe, $usuario, $cliente['IdCliente']));

    $insertId = $insert->execute();

    return $response->withJson(array(
        "idCajaBanco" => $insertId
    ));
});






$app->get('/tipodoc/{id}', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Ve_DocVentaTipoDoc WHERE ";
    $select .= "  Ve_DocVentaTipoDoc.IdTipoDoc = '" . $args['id'] . "' ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});


$app->get('/empresa/id/{id}', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM GEN_EMPRESA WHERE IDEMPRESA=$args[id]";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});


$app->get('/lstusuarios', function (Request $request, Response $response, array $args) {
    try {
        $select = "SELECT lower(Usuario) Usuario,NombreUsuario FROM `Seg_Usuario` WHERE (Anulado != 1 OR Anulado IS NULL)";
        if ($request->getParam('q')) {
            $select .= " AND Seg_Usuario.Usuario LIKE '%" . $request->getParam('q') . "%' ";
            $stmt = $this->db->query($select);
            $stmt->execute();
            $data = $stmt->fetchAll();
            return $response->withJson($data);
        }
        if ($request->getParam('usuario')) {
            # code...
            $select .= " AND Seg_Usuario.Usuario LIKE '" . $request->getParam('usuario') . "' ";
            $stmt = $this->db->query($select);
            $stmt->execute();
            $data = $stmt->fetch();
            return $response->withJson($data);
        }
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
        // return $response->withJson($data);
        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], 500);
    }
});

$app->get('/usuarios/{usuario}', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Seg_Usuario WHERE (Anulado != 1 OR Anulado IS NULL)";
    $select .= " AND Seg_Usuario.Usuario = '$args[usuario]' ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/clientes/{dni}', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Ve_DocVentaCliente WHERE (Anulado != 1 OR Anulado IS NULL)";
    $select .= " AND Ve_DocVentaCliente.DniRuc = '" . $args['dni'] . "' LIMIT 1";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/clientes', function (Request $request, Response $response, array $args) {
    $select = "SELECT *, IFNULL(CONCAT(DniRuc, ' - ', Cliente, ' - ', IFNULL(NombreComercial, '')), '-') AS ClienteDniRuc FROM Ve_DocVentaCliente WHERE (Anulado != 1 OR Anulado IS NULL)";
    $select .= " AND (Ve_DocVentaCliente.Cliente LIKE '%" . $request->getParam('q') . "%' OR Ve_DocVentaCliente.NombreComercial LIKE '%" . $request->getParam('q') . "%'";
    $select .= " OR Ve_DocVentaCliente.DniRuc LIKE '%" . $request->getParam('q') . "%')  ";

    // $select = "SELECT *, IFNULL(CONCAT(DniRuc, ' - ', Cliente), '-') AS ClienteDniRuc FROM Ve_DocVentaCliente WHERE (Anulado != 1 OR Anulado IS NULL)";
    // $select .= " AND Ve_DocVentaCliente.Cliente LIKE '%" . $request->getParam('q') . "%'";
    // $select .= " OR Ve_DocVentaCliente.DniRuc LIKE '%" . $request->getParam('q') . "%'";

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
    if ($limit && !$request->getParam('noLimit')) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/comisionistas', function (Request $request, Response $response, array $args) {
    $select = "SELECT *, IFNULL(CONCAT(DniRuc, ' - ', Cliente), '-') AS ClienteDniRuc FROM Ve_DocVentaCliente WHERE (Anulado != 1 OR Anulado IS NULL) AND esComisionista=1";
    $select .= " AND (Ve_DocVentaCliente.Cliente LIKE '%" . $request->getParam('q') . "%'";
    $select .= " OR Ve_DocVentaCliente.DniRuc LIKE '%" . $request->getParam('q') . "%') ";

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
    if ($limit && !$request->getParam('noLimit')) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/comisionista/{dni}', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Ve_DocVentaCliente WHERE (Anulado != 1 OR Anulado IS NULL) AND esComisionista=1";
    $select .= " AND Ve_DocVentaCliente.DniRuc = '" . $args['dni'] . "' LIMIT 1";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->post('/clientes', function (Request $request, Response $response) {
    $cliente = $request->getParam('Cliente');
    $dniRuc = $request->getParam('DniRuc');
    $direccion = $request->getParam('Direccion');
    $direccion2 = $request->getParam('Direccion2');
    $direccion3 = $request->getParam('Direccion3');
    $telefono = $request->getParam('Telefono');
    $email = $request->getParam('Email');
    $sexo = $request->getParam('Sexo');
    $ocupacion = $request->getParam('Ocupacion');
    $fechaNacimiento = $request->getParam('FechaNacimiento') ? $request->getParam('FechaNacimiento') : NULL;
    $nacionalidad = $request->getParam('Nacionalidad');
    $esVIP = $request->getParam('EsVIP');
    $esComisionista = $request->getParam('esComisionista');
    $nombreComercial = $request->getParam('NombreComercial');

    $idCliente = $request->getParam('IdCliente');
    $insertId = $idCliente;
    if ($idCliente) {
        $update = $this->db->update(array(
            "Cliente" => $cliente,
            'DniRuc' => $dniRuc,
            'Direccion' => $direccion,
            'Direccion2' => $direccion2,
            'Direccion3' => $direccion3,
            'Telefono' => $telefono,
            'Email' => $email,
            "Sexo" => $sexo,
            "Ocupacion" => $ocupacion,
            "FechaNacimiento" => $fechaNacimiento,
            "Nacionalidad" => $nacionalidad,
            "EsVIP" => $esVIP,
            "esComisionista" => $esComisionista,
            "NombreComercial" => $nombreComercial
        ))
            ->table('Ve_DocVentaCliente')
            ->where('IdCliente', '=', $idCliente);
        $affectedRows = $update->execute();
    } else {
        $insert = $this->db->insert(array('Cliente', 'DniRuc', 'Direccion', 'Direccion2', 'Direccion3', 'Telefono', 'Email', 'Anulado', 'FechaReg', 'Sexo', 'Ocupacion', 'FechaNacimiento', 'Nacionalidad', 'EsVIP', 'NombreComercial', 'esComisionista'))
            ->into('Ve_DocVentaCliente')
            ->values(array($cliente, $dniRuc, $direccion, $direccion2, $direccion3, $telefono, $email, '0', getNow(), $sexo, $ocupacion, $fechaNacimiento, $nacionalidad, $esVIP, $nombreComercial, $esComisionista));

        $insertId = $insert->execute();
    }

    $select = "SELECT * FROM Ve_DocVentaCliente WHERE IdCliente=$insertId";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});


$app->get('/costo/delivery', function (Request $request, Response $response) {
    $select = "SELECT Gen_Producto.IdProducto,Gen_Producto.Imagen,Gen_Producto.PrecioContado,Gen_Producto.PrecioCosto,Gen_Producto.Producto,
    Gen_Producto.CodigoBarra, Gen_ProductoCategoria.ProductoCategoria, Gen_ProductoMarca.ProductoMarca, Gen_ProductoMedicion.ProductoMedicion
    FROM Gen_Producto
    INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
    INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
    INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
    WHERE Gen_Producto.CodigoBarra = 'COSTODELIVERY' LIMIT 1";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();
    return $response->withJson($data);
});

$app->get('/venta/pedido/existe', function (Request $request, Response $response) {
    $idPedido = $request->getParam('IdPedido');
    $selectVenta = "SELECT Ve_DocVenta.idDocVenta,Ve_DocVentaTipoDoc.TipoDoc, Ve_DocVenta.Serie, Ve_DocVenta.Numero , Ve_DocVenta.FechaDoc, Ve_DocVenta.UsuarioReg FROM Ve_DocVenta
    INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc =  Ve_DocVentaTipoDoc.IdTipoDoc
    WHERE Ve_DocVenta.Anulado = 0 AND Ve_DocVenta.EsDelivery=1 AND Ve_DocVenta.IdPedido =  $idPedido LIMIT 1";
    $stmt = $this->db->query($selectVenta);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});

$app->get('/categorias/count', function (Request $request, Response $response, array $args) {
    $select = "SELECT COUNT(*) as total FROM Gen_ProductoCategoria";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});

$app->post('/categorias/impresora', function (Request $request, Response $response, array $args) {


    $productoCategoria = $request->getParam('ProductoCategoria');
    $idImpresora = $request->getParam('IdImpresora');
    $mostrarDelivery = $request->getParam('MostrarDelivery') ? $request->getParam('MostrarDelivery') : 0;
    $esOferta = $request->getParam('EsOferta') ? $request->getParam('EsOferta') : 0;
    $mostrarPuntoVenta = $request->getParam('MostrarPuntoVenta') ? $request->getParam('MostrarPuntoVenta') : 0;
    $orden = $request->getParam('Orden') == '' || $request->getParam('Orden') == null || $request->getParam('Orden') == 0 ? 0 : $request->getParam('Orden');
    // Actualizamos el producto si le pasamos el ID
    if ($request->getParam('IdProductoCategoria')) {
        // aqui se actualiza el producto si existe
        $idCategoria = $request->getParam('IdProductoCategoria');
        // $codigoBarra = $request->getParam('CodigoBarra');

        $update = $this->db->update(array(
            "ProductoCategoria" => $productoCategoria,
            "IdImpresora" => $idImpresora,
            "MostrarDelivery" => $mostrarDelivery,
            "MostrarPuntoVenta" => $mostrarPuntoVenta,
            "Orden" => $orden,
            "EsOferta" => $esOferta

        ))
            ->table('Gen_ProductoCategoria')
            ->where('IdProductoCategoria', '=', $idCategoria);
        $affectedRows = $update->execute();

        return $response->withJson(array("insertId" => $idCategoria));

        // return $response->withJson(array("affectedRows" => $productosDet));

    }
    // Fin actualizacion producto

    $insert = $this->db
        ->insert(
            array(
                "ProductoCategoria",
                "IdImpresora",
                "MostrarDelivery",
                "MostrarPuntoVenta",
                "Orden",
                "EsOferta"
            )
        )
        ->into('Gen_ProductoCategoria')
        ->values(
            array(
                $productoCategoria,
                $idImpresora,
                $mostrarDelivery,
                $mostrarPuntoVenta,
                $orden,
                $esOferta
            )
        );
    $insertId = $insert->execute();


    return $response->withJson(array("insertId" => $insertId));
});

$app->get('/categorias/impresora', function (Request $request, Response $response, array $args) {
    $q = $request->getParam('filter');
    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;

    $select = "SELECT Gen_ProductoCategoria.*,Gen_Impresora.NombreImpresora,Gen_Impresora.Ip,Gen_Impresora.Puerto FROM Gen_ProductoCategoria
    LEFT JOIN Gen_Impresora ON Gen_ProductoCategoria.IdImpresora = Gen_Impresora.IdImpresora
    WHERE ProductoCategoria LIKE '" . $q . "%' ";


    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    if ($limit) {
        // $limit = $request->getParam('limit');
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/impresoras', function (Request $request, Response $response, array $args) {
    $q = $request->getParam('filter');
    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;

    $select = "SELECT IdImpresora,NombreImpresora,Ip,Puerto,EsCocina
    FROM Gen_Impresora WHERE NombreImpresora LIKE '%" . $q . "%' ORDER BY EsCocina DESC";


    if ($limit) {
        // $limit = $request->getParam('limit');
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/categorias/oferta', function (Request $request, Response $response, array $args) {

    $select = "SELECT IdProductoCategoria,ProductoCategoria FROM Gen_ProductoCategoria WHERE EsOferta = 1";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->post('/impresoras', function (Request $request, Response $response, array $args) {


    $nombreImpresora = $request->getParam('NombreImpresora');
    $ip = $request->getParam('Ip');
    $puerto = $request->getParam('Puerto');
    $esCocina = $request->getParam('EsCocina');
    // Actualizamos el producto si le pasamos el ID
    if ($request->getParam('IdImpresora')) {
        // aqui se actualiza el producto si existe
        $idImpresora = $request->getParam('IdImpresora');
        // $codigoBarra = $request->getParam('CodigoBarra');

        $update = $this->db->update(array(
            "NombreImpresora" => $nombreImpresora,
            "Ip" => $ip,
            "Puerto" => $puerto,
            "EsCocina" => $esCocina,
        ))
            ->table('Gen_Impresora')
            ->where('IdImpresora', '=', $idImpresora);
        $affectedRows = $update->execute();

        return $response->withJson(array("insertId" => $idImpresora));

        // return $response->withJson(array("affectedRows" => $productosDet));

    }
    // Fin actualizacion producto

    $insert = $this->db
        ->insert(
            array(
                'NombreImpresora',
                'Ip',
                'Puerto',
                'EsCocina'
            )
        )
        ->into('Gen_Impresora')
        ->values(
            array(
                $nombreImpresora,
                $ip,
                $puerto,
                $esCocina
            )
        );
    $insertId = $insert->execute();


    return $response->withJson(array("insertId" => $insertId));
});

$app->get('/impresoras/count', function (Request $request, Response $response, array $args) {
    $select = "SELECT COUNT(*) as total FROM Gen_Impresora";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});







// DATOS GLOBALES DE LA EMPRESA
require_once __DIR__ . '/../config/database.php';
$empresaFiscal = array();
$empresaMeta = array();
try {
    $empresaPdo = appPdoConnection();
    $empresaFiscal = $empresaPdo->query('SELECT * FROM GEN_EMPRESA ORDER BY IDEMPRESA ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: array();
    if (!empty($empresaFiscal['IDEMPRESA'])) {
        $empresaMetaStmt = $empresaPdo->prepare('SELECT MetaKey, MetaValue FROM Gen_EmpresaConfig WHERE IdEmpresa = ? ORDER BY IdEmpresaConfig ASC');
        $empresaMetaStmt->execute(array($empresaFiscal['IDEMPRESA']));
        foreach ($empresaMetaStmt->fetchAll(PDO::FETCH_ASSOC) as $empresaMetaRow) $empresaMeta[$empresaMetaRow['MetaKey']] = $empresaMetaRow['MetaValue'];
    }
} catch (Throwable $empresaError) {
    error_log('[API-EMPRESA] ' . $empresaError->getMessage());
}
$empresaDato = function ($key, $default = '') use ($empresaFiscal, $empresaMeta) {
    if (isset($empresaMeta[$key]) && trim((string)$empresaMeta[$key]) !== '') return $empresaMeta[$key];
    if (isset($empresaFiscal[$key]) && trim((string)$empresaFiscal[$key]) !== '') return $empresaFiscal[$key];
    return $default;
};
define('NRO_DOCUMENTO_EMPRESA', $empresaDato('RUC', '20573027125'));
define('TIPO_DOCUMENTO_EMPRESA', '6'); //1 DNI 6 RUC
define('TIPO_PROCESO', $empresaDato('sunat_entorno', 'FE_PRODUCCION') === 'FE_PRODUCCION' ? '01' : '03'); //01 PRODUCCION 03 BETA
define('RAZON_SOCIAL_EMPRESA', $empresaDato('RAZONSOCIAL', 'CONSULTORA CONSTRUCTORA NEUROSYSTEM PERU S.A.C.'));
define('NOMBRE_COMERCIAL_EMPRESA', $empresaDato('nombre_comercial', 'CONSULTORA CONSTRUCTORA NEUROSYSTEM PERU S.A.C.'));
define('CODIGO_UBIGEO_EMPRESA', $empresaDato('UBIGEO', "250101"));
define('DIRECCION_EMPRESA', $empresaDato('DIRECCION', "JR. 28 DE JULIO NRO. 313 CENT C.U HUANUCO (FRENTE AL GRIFO TORRES) HUANUCO - HUANUCO - HUANUCO"));
define('DEPARTAMENTO_EMPRESA', $empresaDato('DEPARTAMENTO', "HUANUCO"));
define('PROVINCIA_EMPRESA', $empresaDato('PROVINCIA', "HUANUCO"));
define('DISTRITO_EMPRESA', $empresaDato('DISTRITO', "HUANUCO"));
define('TELEFONOS_EMPRESA', $empresaDato('CELULAR', ""));

define('CODIGO_PAIS_EMPRESA', 'PE');
define('USUARIO_SOL_EMPRESA', $empresaDato('usuario_sol', 'NEUR0515')); // cambiar cuando se pase a produccion //NEURO123
define('PASS_SOL_EMPRESA', $empresaDato('clave_sol', 'Neur0515')); // cambiar cuando se pase a produccion
define('PAS_FIRMA', $empresaDato('clave_certificado', '5i573m452023')); // cambiar cuando se pase a produccion

$app->post('/emitirelectronico', function (Request $request, Response $response) {
    include_once("../controllers/NumerosEnLetras/NumerosEnLetras.php");

    $docVenta = $request->getParam('docVenta');

    // Obteber venta.
    $select = "SELECT * FROM Ve_DocVenta WHERE idDocVenta=" . $docVenta['idDocVenta'];
    $stmt = $this->db->query($select);
    $stmt->execute();
    $venta = $stmt->fetch();

    $new = new api_sunat();

    $detalle = array();
    $json = array();
    $n = 0;
    $descuento = 0;

    foreach ($docVenta['productos'] as $producto) {
        $n = $n + 1;

        $igv = $docVenta['TieneIgv'] ? round($producto['Total'] - ($producto['Total'] / 1.18), 2) : 0;
        $subtotal = $docVenta['TieneIgv'] ? $producto['Total'] - $igv : $producto['Total'];
        $subtotal = $docVenta['TieneIgv'] ? $subtotal : ($producto['Descuento'] > 0 ? $subtotal - $producto['Descuento'] : $subtotal);

        $json['txtITEM'] = $n;
        $json["txtUNIDAD_MEDIDA_DET"] = $producto['CodigoMedicion'];
        $json["txtUNIDAD_MEDIDA_NOMBRE_DET"] = $producto['ProductoMedicion'];
        $json["txtCANTIDAD_DET"] = $producto['Cantidad'];
        $json["txtPRECIO_DET"] = round(($subtotal + $igv) / $producto['Cantidad'], 4);
        $json["txtPRECIO_DESC_DET"] = $producto['Descuento'];
        $json["txtSUB_TOTAL_DET"] = $subtotal;  //PRECIO * CANTIDAD
        $json["txtPRECIO_TIPO_CODIGO"] = "01"; // 02 valor referencial unitario en operaciones no onerosas
        $json["txtIGV"] = $igv;
        $json["txtISC"] = "0";
        $json["txtIMPORTE_DET"] = $subtotal; //rowData.IMPORTE; //SUB_TOTAL + IGV
        $json["txtCOD_TIPO_OPERACION"] = $docVenta['CodigoIgv']; //20 si es exonerado
        $json["txtCODIGO_DET"] = $producto['CodigoBarra'];
        $json["txtDESCRIPCION_DET"] = trim($producto['Producto']);
        $json["txtDESCRIPCION_DET_PRO"] = trim(json_decode($producto['Descripcion']));
        $json["txtMANO_OBRA"] = $producto['EsManoDeObra'];
        $json["txtPRECIO_SIN_IGV_DET"] =  $docVenta['TieneIgv'] ? round($producto['Precio'] / 1.18, 5) : round(($subtotal + $igv) / $producto['Cantidad'], 5);

        $detalle[] = $json;
        $descuento += $producto['Descuento'];
    }

    $gravadas = $docVenta['TieneIgv'] ? round($docVenta['Total'] / 1.18, 2) : $docVenta['Total'];
    $subtotal = $gravadas;
    $docVenta['DniRuc'] = trim($docVenta['DniRuc']);
    $data = array(
        "txtTIPO_OPERACION" => "0101", // corregir esto despues
        "txtTOTAL_DESCUENTO" => $docVenta['TieneIgv'] ? round($descuento / 1.18, 2) : 0,
        "txtTOTAL_DESCUENTO_REF" => $descuento,
        "txtSUB_TOTAL" => $subtotal,
        "txtPOR_IGV" => $docVenta['TieneIgv'] ? "18" : "0",
        // "txtTOTAL_IGV"=> $docVenta['TieneIgv'] ? round($docVenta['Total'] * 0.18, 2) : 0,
        "txtTOTAL_IGV" => $docVenta['TieneIgv'] ? round($docVenta['Total'] - ($docVenta['Total'] / 1.18), 2) : 0,
        "txtTOTAL" => $docVenta['Total'],
        "txtTOTAL_LETRAS" => NumerosEnLetras::convertir(number_format($docVenta['Total'], 2, '.', ''), 'SOLES', true),
        "txtNRO_COMPROBANTE" => $docVenta['Serie'] . "-" . $docVenta['Numero'], //
        "txtFECHA_DOCUMENTO" => date("Y-m-d", strtotime($docVenta['FechaDoc'])),
        "txtFECHA_VTO" => date("Y-m-d", strtotime($docVenta['FechaDoc'])),
        "txtCOD_TIPO_DOCUMENTO" => $docVenta['CodSunat'], //01=factura,03=boleta,07=notacrediro,08=notadebito
        "txtCOD_MONEDA" => 'PEN', //PEN= PERU
        "txtVENDEDOR" => $docVenta['UsuarioReg'], //VENDEDOR
        // 'detalle_forma_pago' => [
        //          ["COD_FORMA_PAGO" => "Contado"]
        //      ],
        //==========documentos de referencia(nota credito, debito)=============
        "txtTIPO_COMPROBANTE_MODIFICA" => isset($docVenta['CodSunatModifica']) && $docVenta['CodSunatModifica'] ? $docVenta['CodSunatModifica'] : "", //aqui completar
        "txtNRO_DOCUMENTO_MODIFICA" => isset($docVenta['NroComprobanteModifica']) && $docVenta['NroComprobanteModifica'] ? $docVenta['NroComprobanteModifica'] : "",
        "txtCOD_TIPO_MOTIVO" => isset($docVenta['NotaIdMotivo']) && $docVenta['NotaIdMotivo'] ? $docVenta['NotaIdMotivo'] : "",
        "txtDESCRIPCION_MOTIVO" => isset($docVenta['NotaDescMotivo']) && $docVenta['NotaDescMotivo'] ? $docVenta['NotaDescMotivo'] : "", //$("[name='txtID_MOTIVO']
        //=================datos del cliente=================8
        "txtNRO_DOCUMENTO_CLIENTE" => trim($docVenta['DniRuc']),
        "txtRAZON_SOCIAL_CLIENTE" => trim($docVenta['Cliente']),
        "txtTIPO_DOCUMENTO_CLIENTE" => strlen($docVenta['DniRuc']) > 9 ? "6" : "1", //1 DNI 6 RUC
        "txtDIRECCION_CLIENTE" => trim($docVenta['Direccion']),
        "txtCIUDAD_CLIENTE" => "",
        "txtCOD_PAIS_CLIENTE" => "PE",
        "txtGRE" => $venta['GRE'],
        //=================datos de LA EMPRESA=================
        "txtNRO_DOCUMENTO_EMPRESA" => NRO_DOCUMENTO_EMPRESA,
        "txtTIPO_DOCUMENTO_EMPRESA" => TIPO_DOCUMENTO_EMPRESA,
        "txtNOMBRE_COMERCIAL_EMPRESA" => NOMBRE_COMERCIAL_EMPRESA,
        "txtCODIGO_UBIGEO_EMPRESA" => CODIGO_UBIGEO_EMPRESA,
        "txtDIRECCION_EMPRESA" => DIRECCION_EMPRESA,
        "txtDEPARTAMENTO_EMPRESA" => DEPARTAMENTO_EMPRESA,
        "txtPROVINCIA_EMPRESA" => PROVINCIA_EMPRESA,
        "txtDISTRITO_EMPRESA" => DISTRITO_EMPRESA,
        "txtCODIGO_PAIS_EMPRESA" => CODIGO_PAIS_EMPRESA,
        "txtRAZON_SOCIAL_EMPRESA" => RAZON_SOCIAL_EMPRESA,
        "txtUSUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
        "txtPASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
        "txtPAS_FIRMA" => PAS_FIRMA,
        "txtTIPO_PROCESO" => TIPO_PROCESO, //01 PRODUCCION 03 BETA
        "detalle" => $detalle,
    );

    if ($docVenta['CodigoIgv'] == "20") { // 20 = exonerado Igv
        $data["txtTOTAL_GRAVADAS"] = 0;
        $data["txtTOTAL_EXONERADAS"] = $gravadas;
    } else {
        $data["txtTOTAL_GRAVADAS"] = $gravadas;
        $data["txtTOTAL_EXONERADAS"] = 0;
    }

    // forma de pago
    if ($venta['EsCredito']) {
        if (date('Y-m-d', strtotime($venta['FechaCredito'])) == date('Y-m-d', strtotime($venta['FechaDoc']))) {
            return $response->withJson(array("msj_sunat" => 'Error, La fecha de credito debe ser diferente a la del documento', "Estado" => 1));
        }
        $data['detalle_forma_pago'] = [
            [
                "COD_FORMA_PAGO" => "Credito",
                "MONTO_FORMA_PAGO" => $gravadas,
                "FECHA_FORMA_PAGO" => date('Y-m-d', strtotime($venta['FechaCredito']))
            ],
            [
                "COD_FORMA_PAGO" => "Cuota001",
                "MONTO_FORMA_PAGO" => $gravadas,
                "FECHA_FORMA_PAGO" => date('Y-m-d', strtotime($venta['FechaCredito']))
            ]
        ];
    } else {
        $data['detalle_forma_pago'] = [
            ["COD_FORMA_PAGO" => "Contado"]
        ];
    }

    if ($request->getParam('generarXml')) {
        $data['generarXml'] = true;
        $resultado = $new->sendPostCPE(json_encode($data));
        return $response->withJson([
            'success' => true
        ]);
    }
    $data['generarXml'] = false;

    $resultado = $new->sendPostCPE(json_encode($data));
    $me = json_decode($resultado, true);

    // Datos $me
    $me['data'] = array(
        "txtNRO_COMPROBANTE" => $data['txtNRO_COMPROBANTE']
    );
    // print_r($data);exit();
    $estado = 1;
    if ($me['cod_sunat'] == '0') {
        $estado = 2;
        // generamos PDF para su descarga
        $data['hash_cpe'] = $me['hash_cpe'];

        if ($docVenta['CodSunat'] == '07' || $docVenta['CodSunat'] == '08') {
            $new->creaPDFNota(json_encode($data));
        } else {
            $new->creaPDF(json_encode($data));
        }

        // Si es nota de credito/debito insertar en tabla
        if ($docVenta['CodSunat'] == '07') { // nota de credito

        }

        if ($docVenta['CodSunat'] == '08') {
        }
    }


    $sql = "UPDATE Ve_DocVenta SET Estado=$estado, hash_cpe='$me[hash_cpe]', Hash_cdr='$me[hash_cdr]', 
        Msj_sunat='$me[msj_sunat]' WHERE idDocVenta='$docVenta[idDocVenta]' ";

    $stmt = $this->db->prepare($sql);
    $updated = $stmt->execute();
    $me['Estado'] = $estado;

    return $response->withJson($me);
});


$app->post('/generarpdfelectronico', function (Request $request, Response $response) {
    include_once("../controllers/NumerosEnLetras/NumerosEnLetras.php");

    $docVenta = $request->getParam('docVenta');
    $new = new api_sunat();

    $detalle = array();
    $json = array();
    $n = 0;
    $descuento = 0;

    foreach ($docVenta['productos'] as $producto) {
        $n = $n + 1;

        $igv = $docVenta['TieneIgv'] ? round($producto['Total'] - ($producto['Total'] / 1.18), 2) : 0;
        $subtotal = $docVenta['TieneIgv'] ? $producto['Total'] - $igv : $producto['Total'];

        $json['txtITEM'] = $n;
        $json["txtUNIDAD_MEDIDA_DET"] = $producto['CodigoMedicion'];
        $json["txtUNIDAD_MEDIDA_NOMBRE_DET"] = $producto['ProductoMedicion'];
        $json["txtCANTIDAD_DET"] = $producto['Cantidad'];
        $json["txtPRECIO_DET"] = round(($subtotal + $igv) / $producto['Cantidad'], 4);
        $json["txtPRECIO_DESC_DET"] = $producto['Descuento'];
        $json["txtSUB_TOTAL_DET"] = $subtotal;  //PRECIO * CANTIDAD
        $json["txtPRECIO_TIPO_CODIGO"] = "01"; // 02 valor referencial unitario en operaciones no onerosas
        $json["txtIGV"] = $igv;
        $json["txtISC"] = "0";
        $json["txtIMPORTE_DET"] = $subtotal; //rowData.IMPORTE; //SUB_TOTAL + IGV
        $json["txtCOD_TIPO_OPERACION"] = $docVenta['CodigoIgv']; //20 si es exonerado
        $json["txtCODIGO_DET"] = $producto['CodigoBarra'];
        $json["txtDESCRIPCION_DET"] = trim($producto['Producto']);
        $json["txtDESCRIPCION_DET_PRO"] = trim(json_decode($producto['Descripcion']));
        $json["txtMANO_OBRA"] = $producto['EsManoDeObra'];
        $json["txtPRECIO_SIN_IGV_DET"] =  $docVenta['TieneIgv'] ? round($producto['Precio'] / 1.18, 2) : $producto['Precio'];

        $detalle[] = $json;
        $descuento += $producto['Descuento'];
    }

    $gravadas = $docVenta['TieneIgv'] ? round($docVenta['Total'] / 1.18, 2) : $docVenta['Total'];
    $subtotal = $gravadas;
    $docVenta['DniRuc'] = trim($docVenta['DniRuc']);
    $data = array(
        "txtTIPO_OPERACION" => "0101", // corregir esto despues
        "txtTOTAL_DESCUENTO" => $docVenta['TieneIgv'] ? round($descuento / 1.18, 2) : $descuento,
        "txtSUB_TOTAL" => $subtotal,
        "txtPOR_IGV" => $docVenta['TieneIgv'] ? "18" : "0",
        // "txtTOTAL_IGV"=> $docVenta['TieneIgv'] ? round($docVenta['Total'] * 0.18, 2) : 0,
        "txtTOTAL_IGV" => $docVenta['TieneIgv'] ? round($docVenta['Total'] - ($docVenta['Total'] / 1.18), 2) : 0,
        "txtTOTAL" => $docVenta['Total'],
        "txtTOTAL_LETRAS" => NumerosEnLetras::convertir(number_format($docVenta['Total'], 2, '.', ''), 'SOLES', true),
        "txtNRO_COMPROBANTE" => $docVenta['Serie'] . "-" . $docVenta['Numero'], //
        "txtFECHA_DOCUMENTO" => date("Y-m-d", strtotime($docVenta['FechaDoc'])),
        "txtFECHA_VTO" => date("Y-m-d", strtotime($docVenta['FechaDoc'])),
        "txtCOD_TIPO_DOCUMENTO" => $docVenta['CodSunat'], //01=factura,03=boleta,07=notacrediro,08=notadebito
        "txtCOD_MONEDA" => 'PEN', //PEN= PERU
        "txtVENDEDOR" => $docVenta['UsuarioReg'], //VENDEDOR
        "txtGRE" => $docVenta['GRE'],
        // 'detalle_forma_pago' => [
        //          ["COD_FORMA_PAGO" => "Contado"]
        //      ],
        //==========documentos de referencia(nota credito, debito)=============
        "txtTIPO_COMPROBANTE_MODIFICA" => isset($docVenta['CodSunatModifica']) && $docVenta['CodSunatModifica'] ? $docVenta['CodSunatModifica'] : "", //aqui completar
        "txtNRO_DOCUMENTO_MODIFICA" => isset($docVenta['NroComprobanteModifica']) && $docVenta['NroComprobanteModifica'] ? $docVenta['NroComprobanteModifica'] : "",
        "txtCOD_TIPO_MOTIVO" => isset($docVenta['NotaIdMotivo']) && $docVenta['NotaIdMotivo'] ? $docVenta['NotaIdMotivo'] : "",
        "txtDESCRIPCION_MOTIVO" => isset($docVenta['NotaDescMotivo']) && $docVenta['NotaDescMotivo'] ? $docVenta['NotaDescMotivo'] : "", //$("[name='txtID_MOTIVO']
        //=================datos del cliente=================8
        "txtNRO_DOCUMENTO_CLIENTE" => $docVenta['DniRuc'],
        "txtRAZON_SOCIAL_CLIENTE" => $docVenta['Cliente'],
        "txtTIPO_DOCUMENTO_CLIENTE" => strlen($docVenta['DniRuc']) > 9 ? "6" : "1", //1 DNI 6 RUC
        "txtDIRECCION_CLIENTE" => $docVenta['Direccion'],
        "txtCIUDAD_CLIENTE" => "",
        "txtCOD_PAIS_CLIENTE" => "PE",
        //=================datos de LA EMPRESA=================
        "txtNRO_DOCUMENTO_EMPRESA" => NRO_DOCUMENTO_EMPRESA,
        "txtTIPO_DOCUMENTO_EMPRESA" => TIPO_DOCUMENTO_EMPRESA,
        "txtNOMBRE_COMERCIAL_EMPRESA" => NOMBRE_COMERCIAL_EMPRESA,
        "txtCODIGO_UBIGEO_EMPRESA" => CODIGO_UBIGEO_EMPRESA,
        "txtDIRECCION_EMPRESA" => DIRECCION_EMPRESA,
        "txtDEPARTAMENTO_EMPRESA" => DEPARTAMENTO_EMPRESA,
        "txtPROVINCIA_EMPRESA" => PROVINCIA_EMPRESA,
        "txtDISTRITO_EMPRESA" => DISTRITO_EMPRESA,
        "txtCODIGO_PAIS_EMPRESA" => CODIGO_PAIS_EMPRESA,
        "txtRAZON_SOCIAL_EMPRESA" => RAZON_SOCIAL_EMPRESA,
        "txtUSUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
        "txtPASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
        "txtPAS_FIRMA" => PAS_FIRMA,
        "txtTIPO_PROCESO" => TIPO_PROCESO, //01 PRODUCCION 03 BETA
        "detalle" => $detalle,
    );

    if ($docVenta['CodigoIgv'] == "20") { // 20 = exonerado Igv
        $data["txtTOTAL_GRAVADAS"] = 0;
        $data["txtTOTAL_EXONERADAS"] = $gravadas;
    } else {
        $data["txtTOTAL_GRAVADAS"] = $gravadas;
        $data["txtTOTAL_EXONERADAS"] = 0;
    }

    // forma de pago
    if ($venta['EsCredito']) {
        $data['detalle_forma_pago'] = [
            [
                "COD_FORMA_PAGO" => "Credito",
                "MONTO_FORMA_PAGO" => $gravadas,
                "FECHA_FORMA_PAGO" => date('Y-m-d', strtotime($venta['FechaCredito']))
            ],
            [
                "COD_FORMA_PAGO" => "Cuota001",
                "MONTO_FORMA_PAGO" => $gravadas,
                "FECHA_FORMA_PAGO" => date('Y-m-d', strtotime($venta['FechaCredito']))
            ]
        ];
    } else {
        $data['detalle_forma_pago'] = [
            ["COD_FORMA_PAGO" => "Contado"]
        ];
    }

    $data['hash_cpe'] = isset($docVenta['Hash_cpe']) ? $docVenta['Hash_cpe'] : '';
    if ($docVenta['CodSunat'] == '07' || $docVenta['CodSunat'] == '08') {
        $new->creaPDFNota(json_encode($data));
    } else {
        $new->creaPDF(json_encode($data));
    }
});

$app->get('/imprimirpdf_wsp/{id}', function (Request $request, Response $response, array $args) use ($app) {
    $id = $args['id'];
    $select = "SELECT Ve_DocVenta.idDocVenta, Ve_DocVenta.EsCredito, Ve_DocVenta.FechaCredito, Ve_DocVenta.FechaDoc, Ve_DocVentaTipoDoc.TipoDoc, Ve_DocVentaTipoDoc.TieneIgv,
        Ve_DocVenta.Anulado, Ve_DocVenta.Serie, Ve_DocVenta.Numero, Ve_DocVentaCliente.Cliente, Ve_DocVenta.UsuarioReg,
        Ve_DocVentaTipoDoc.CodSunat, Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.Direccion,
        Ve_DocVentaTipoDoc.CodigoIgv, Ve_DocVenta.Estado, Ve_DocVenta.Hash_cpe, Ve_DocVenta.Hash_cdr, Ve_DocVenta.Msj_sunat,
        Ve_DocVenta.FechaCredito, Lo_Almacen.Almacen, Lo_Almacen.Direccion DireccionAlmacen, Lo_Almacen.EsPrincipal EsAlmacenPrincipal,
        Ve_DocVentaCliente.NombreComercial,
        Ve_DocVenta.CodSunatModifica,
        Ve_DocVenta.NroComprobanteModifica,
        Ve_DocVenta.NotaIdMotivo,
        Ve_DocVenta.NotaDescMotivo,
        IFNULL((SELECT SUM(ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2)) FROM Ve_DocVentaDet WHERE Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta), 0 ) AS Total
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        LEFT JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente 
        INNER JOIN Lo_Almacen ON Ve_DocVenta.IdAlmacen = Lo_Almacen.IdAlmacen
        WHERE idDocVenta = $id ";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $venta = $stmt->fetch();
    $select = "SELECT Ve_DocVentaDet.*, Gen_Producto.Producto,
        ROUND(Ve_DocVentaDet.Descuento, 2) AS Descuento,
        ROUND((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - Ve_DocVentaDet.Descuento, 2) AS Subtotal,
        ROUND(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio, 2) AS Total,
        Gen_Producto.CodigoBarra, Gen_ProductoMedicion.Codigo AS CodigoMedicion,
        Gen_ProductoMedicion.ProductoMedicion, Gen_ProductoMarca.ProductoMarca
        FROM Ve_DocVentaDet
        INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
        INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
        INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
        WHERE IdDocVenta = $id";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $productos = $stmt->fetchAll();
    $venta['productos'] = $productos;
    // Generar el PDF como archivo
    $res = $app->subRequest('POST', '/generarpdfelectronico', http_build_query(['docVenta' => $venta]));
    // Buscar el pdf y mostrarlo
    // $response = $this->response->withHeader('Content-type', 'application/pdf');
    $urlArchivo =   '/sunat/api_cpe/PRODUCCION/' . NRO_DOCUMENTO_EMPRESA . '/' . NRO_DOCUMENTO_EMPRESA . '-' . $venta['CodSunat'] . '-' . $venta['Serie'] . '-' . $venta['Numero'] . '.pdf';
    $nroComprobante = $venta['Numero'];
    return $response->withJson([
        'url' => $urlArchivo,
        'documento' => $nroComprobante,
        'document' => '/sunat/api_cpe/PRODUCCION/' . NRO_DOCUMENTO_EMPRESA . '/' . NRO_DOCUMENTO_EMPRESA . '-' . $venta['CodSunat'] . '-' . $venta['Serie'] . '-' . $venta['Numero'] . '.pdf'
    ]);
});

$app->get('/imprimirpdf/{id}', function (Request $request, Response $response, array $args) use ($app) {
    $id = $args['id'];

    $select = "SELECT Ve_DocVenta.idDocVenta, Ve_DocVenta.EsCredito, Ve_DocVenta.FechaCredito, Ve_DocVenta.FechaDoc, Ve_DocVentaTipoDoc.TipoDoc, Ve_DocVentaTipoDoc.TieneIgv,
        Ve_DocVenta.Anulado, Ve_DocVenta.Serie, Ve_DocVenta.Numero, Ve_DocVentaCliente.Cliente, Ve_DocVenta.UsuarioReg,
        Ve_DocVentaTipoDoc.CodSunat, Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.Direccion,
        Ve_DocVentaTipoDoc.CodigoIgv, Ve_DocVenta.Estado, Ve_DocVenta.Hash_cpe, Ve_DocVenta.Hash_cdr, Ve_DocVenta.Msj_sunat,
        Ve_DocVenta.FechaCredito, Lo_Almacen.Almacen, Lo_Almacen.Direccion DireccionAlmacen, Lo_Almacen.EsPrincipal EsAlmacenPrincipal,
        Ve_DocVentaCliente.NombreComercial,
        Ve_DocVenta.CodSunatModifica,
        Ve_DocVenta.NroComprobanteModifica,
        Ve_DocVenta.NotaIdMotivo,
        Ve_DocVenta.NotaDescMotivo, IFNULL(Ve_DocVenta.GRE,'') AS GRE,
        IFNULL((SELECT SUM(ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2)) FROM Ve_DocVentaDet WHERE Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta), 0 ) AS Total
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        LEFT JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente 
        INNER JOIN Lo_Almacen ON Ve_DocVenta.IdAlmacen = Lo_Almacen.IdAlmacen
        WHERE idDocVenta = $id ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $venta = $stmt->fetch();

    $select = "SELECT Ve_DocVentaDet.*, Gen_Producto.Producto,
        ROUND(Ve_DocVentaDet.Descuento, 2) AS Descuento,
        ROUND((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - Ve_DocVentaDet.Descuento, 2) AS Subtotal,
        ROUND(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio, 2) AS Total,
        Gen_Producto.CodigoBarra, Gen_ProductoMedicion.Codigo AS CodigoMedicion,
        Gen_ProductoMedicion.ProductoMedicion, Gen_ProductoMarca.ProductoMarca
        FROM Ve_DocVentaDet
        INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
        INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
        INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
        WHERE IdDocVenta = $id";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $productos = $stmt->fetchAll();

    $venta['productos'] = $productos;

    // Generar el PDF como archivo
    $res = $app->subRequest('POST', '/generarpdfelectronico', http_build_query(['docVenta' => $venta]));

    // Buscar el pdf y mostrarlo
    $response = $this->response->withHeader('Content-type', 'application/pdf');
    $content = file_get_contents(dirname(__FILE__) . '/sunat/api_cpe/PRODUCCION/' . NRO_DOCUMENTO_EMPRESA . '/' . NRO_DOCUMENTO_EMPRESA . '-' . $venta['CodSunat'] . '-' . $venta['Serie'] . '-' . $venta['Numero'] . '.pdf');
    return $response->write($content);
});



$app->post('/moveraemitidos', function (Request $request, Response $response) {
    $idDocVenta = $request->getParam('idDocVenta');

    if ($idDocVenta) {
        $update = "UPDATE Ve_DocVenta SET Estado=2 WHERE idDocVenta=$idDocVenta";
        $stmt = $this->db->prepare($update);
        $updated = $stmt->execute();


        return $response->withJson(array(
            "updated" => $updated,
            "IdDocVenta" => $idDocVenta
        ));
    }
});

$app->post('/moveraemitidosguia', function (Request $request, Response $response) {
    $idDocVentaRemision = $request->getParam('idDocVentaRemision');

    if ($idDocVentaRemision) {
        $update = "UPDATE Ve_DocVentaRemision SET Estado=2 WHERE IdDocVentaRemision=$idDocVentaRemision";
        $stmt = $this->db->prepare($update);
        $updated = $stmt->execute();

        return $response->withJson(array(
            "updated" => $updated,
            "IdDocVentaRemision" => $idDocVentaRemision
        ));
    }
});


$app->post('/cambiaraanonimo', function (Request $request, Response $response) {
    $idDocVenta = $request->getParam('idDocVenta');

    if ($idDocVenta) {
        $update = "UPDATE Ve_DocVenta SET IdCliente=(SELECT IdCliente FROM `Ve_DocVentaCliente` WHERE DniRuc='00000000' LIMIT 1) WHERE idDocVenta=$idDocVenta";
        $stmt = $this->db->prepare($update);
        $updated = $stmt->execute();


        return $response->withJson(array(
            "updated" => $updated,
            "IdDocVenta" => $idDocVenta
        ));
    }
});


$app->post('/emitirelectronicoboleta', function (Request $request, Response $response) {
    include_once("../controllers/NumerosEnLetras/NumerosEnLetras.php");

    $new = new api_sunat();
    $boletas = $request->getParam('boletas');
    $statu = $request->getParam('statu') ? $request->getParam('statu') : 1;
    $codSunat = $request->getParam('codSunat');
    $codMoneda = "PEN";

    $fechaReferencia = $request->getParam('fechaReferencia'); // "2018-03-28";
    $fechaDocumento = getNow('Y-m-d');

    $serie = getNow('Ymd');
    // Inicio Correlativo
    $selectSec = "SELECT Secuencia FROM Sunat_Resumen WHERE Serie=$serie AND CodSunat=$codSunat ORDER BY Secuencia DESC LIMIT 1";
    $stmtSec = $this->db->query($selectSec);
    $stmtSec->execute();
    $secuencia = $stmtSec->fetch()['Secuencia'];
    $secuencia += 1; // cambiar correlativo
    // Fin Correlativo

    $detalle = array();
    $json = array();
    $n = 0;
    foreach ($boletas as $boleta) {
        $n = $n + 1;

        $json['ITEM'] = $n;
        $json['TIPO_COMPROBANTE'] = $boleta['CodSunat'];
        $json['NRO_COMPROBANTE'] = $boleta['Serie'] . '-' . $boleta['Numero'];
        $json['TIPO_DOCUMENTO'] = strlen($boleta['DniRuc']) > 9 ? "6" : "1";
        $json['NRO_DOCUMENTO'] = $boleta['DniRuc'];
        $json['TIPO_COMPROBANTE_REF'] = $boleta['CodSunatModifica'] ? $boleta['CodSunatModifica'] : '';
        $json['NRO_COMPROBANTE_REF'] = $boleta['NroComprobanteModifica'] ? $boleta['NroComprobanteModifica'] : '';
        $json['STATU'] = $statu; // 1 declara boleta , 3 mandar anuladas... primero envio y luego se anula
        $json['COD_MONEDA'] = $codMoneda;
        $json['TOTAL'] = $boleta['Total'];
        $json['GRAVADA'] = $boleta['Total']; // $boleta['Total'] - IGV //corregir gravada en Factura

        $json['ISC'] = "0";
        $json['IGV'] = "0";
        $json['OTROS'] = "0";
        $json['CARGO_X_ASIGNACION'] = "1";
        $json['MONTO_CARGO_X_ASIG'] = "0";
        $json['EXONERADO'] = $boleta['Total'];
        $json['INAFECTO'] = "0";
        $json['EXPORTACION'] = "0";
        $json['GRATUITAS'] = "0";
        $detalle[] = $json;
    }

    $data = array(
        "NRO_DOCUMENTO_EMPRESA" => NRO_DOCUMENTO_EMPRESA,
        "RAZON_SOCIAL" => RAZON_SOCIAL_EMPRESA,
        "TIPO_DOCUMENTO" => TIPO_DOCUMENTO_EMPRESA,
        "CODIGO" => "RC",
        "SERIE" => $serie,
        "SECUENCIA" => $secuencia,
        "FECHA_REFERENCIA" => $fechaReferencia,
        "FECHA_DOCUMENTO" => $fechaDocumento,
        "TIPO_PROCESO" => TIPO_PROCESO,
        "USUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
        "PASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
        "PAS_FIRMA" => PAS_FIRMA,
        "detalle" => $detalle
    );

    $resultado = $new->sendresumen(json_encode($data));
    $me = json_decode($resultado, true);
    $estado = 1;
    $me['ticket'] = $me['msj_sunat'];

    if ($me['cod_sunat'] == '0') {

        // insert correlativo del resumen
        $insert = "INSERT INTO Sunat_Resumen (Serie, Secuencia, CodSunat) VALUES ('$serie', $secuencia, '$codSunat')";
        $stmt = $this->db->prepare($insert);
        $inserted = $stmt->execute();
        $lastInsert = $this->db->lastInsertId();
        $estado = ($statu == 3) ? 0 : 2; // si se manda a anular el estado sera 0

        // Inicio Consulta Ticket
        $dataTicket = array(
            "TIPO_PROCESO" => TIPO_PROCESO,
            "NRO_DOCUMENTO_EMPRESA" => NRO_DOCUMENTO_EMPRESA,
            "USUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
            "PASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
            "TICKET" => $me['msj_sunat'],
            "TIPO_DOCUMENTO" => "RC",
            "NRO_DOCUMENTO" => $serie . '-' . $secuencia
        );
        $resTicket = $new->sendticket(json_encode($dataTicket));
        $meTicket = json_decode($resTicket, true);
        if ($meTicket['cod_sunat'] == '0') {
            // $me = $meTicket;
            $me['msj_sunat'] = $meTicket['msj_sunat'];
            $me['hash_cdr'] = $meTicket['hash_cdr'];

            // si todo es correcto generar pdf para cada boleta
            foreach ($boletas as $boleta) {
                $detallePdf = array();
                $json = array();
                $n = 0;

                foreach ($boleta['productos'] as $producto) {
                    $n = $n + 1;
                    $igv = $boleta['TieneIgv'] ? round($producto['Subtotal'] * 0.18, 2) : 0;
                    $subtotal = $boleta['TieneIgv'] ? $producto['Subtotal'] - $igv : $producto['Subtotal'];
                    $json["txtCANTIDAD_DET"] = trim($producto['Cantidad']);
                    $json["txtPRECIO_DET"] = trim($producto['Precio']);
                    $json["txtIMPORTE_DET"] = $subtotal + $igv; //rowData.IMPORTE; //SUB_TOTAL + IGV
                    $json["txtCODIGO_DET"] = trim($producto['CodigoBarra']);
                    $json["txtDESCRIPCION_DET"] = trim($producto['Producto']);
                    $detallePdf[] = $json;
                }
                $dataPdf = array(
                    'txtTIPO_PROCESO' => TIPO_PROCESO,
                    'txtCOD_MONEDA' => $codMoneda,
                    'txtNRO_DOCUMENTO_EMPRESA' => NRO_DOCUMENTO_EMPRESA,
                    'txtCOD_TIPO_DOCUMENTO' => $boleta['CodSunat'],
                    'txtNRO_COMPROBANTE' => $boleta['Serie'] . '-' . $boleta['Numero'],
                    'txtSUB_TOTAL' => $boleta['Total'],
                    'txtTOTAL_IGV' => "0",
                    'txtTOTAL' => $boleta['Total'],
                    'txtTOTAL_LETRAS' => NumerosEnLetras::convertir(number_format($boleta['Total'], 2, '.', ''), 'SOLES', true),
                    'txtFECHA_DOCUMENTO' => date("Y-m-d", strtotime($boleta['FechaDoc'])),
                    'txtTIPO_DOCUMENTO_CLIENTE' => strlen($boleta['DniRuc']) > 9 ? "6" : "1",
                    'txtNRO_DOCUMENTO_CLIENTE' => $boleta['DniRuc'],
                    'txtNOMBRE_COMERCIAL_EMPRESA' => NOMBRE_COMERCIAL_EMPRESA,
                    'txtDIRECCION_EMPRESA' => DIRECCION_EMPRESA,
                    'txtRAZON_SOCIAL_CLIENTE' => $boleta['Cliente'],
                    'txtDIRECCION_CLIENTE' => $boleta['Direccion'],
                    'detalle' => $detallePdf
                );

                // generamos PDF para su descarga
                $dataPdf['hash_cpe'] = $me['hash_cpe'];
                //$new->creaPDF(json_encode($dataPdf)); // aqui crea el PDF uno a uno

            }
        }

        // $me['dataTicket'] = $dataTicket;
        $me['meticket'] = $meTicket;
        // Fin Consulta Ticket
    }

    $cadenaIds = [];
    foreach ($boletas as $boleta) {
        $cadenaIds[] = $boleta['idDocVenta'];
    }

    if ($statu == 3) { // si se manda a anular
        $sql = "UPDATE Ve_DocVenta SET Estado=$estado, hash_cpe='$me[hash_cpe]', Hash_cdr='$me[hash_cdr]',
            Msj_sunat='$me[msj_sunat]' WHERE idDocVenta IN (" . implode(',', $cadenaIds) . ")";
    } else { //si se declara
        $sql = "UPDATE Ve_DocVenta SET Estado=$estado, hash_cpe='$me[hash_cpe]', Hash_cdr='$me[hash_cdr]',
        Msj_sunat='$me[msj_sunat]', Ticket='$me[ticket]' WHERE idDocVenta IN (" . implode(',', $cadenaIds) . ")";
    }
    //$me['sql'] = $sql;
    $stmt = $this->db->prepare($sql);
    $updated = $stmt->execute();
    $me['Estado'] = $estado;
    // $me['data'] = $data; // ocultar en produccion

    return $response->withJson($me);
});

$app->post('/bajaelectronico', function (Request $request, Response $response) {
    $new = new api_sunat();
    $facturas = $request->getParam('facturas');
    $codSunat = $request->getParam('codSunat');
    $descripcion = $request->getParam('descripcion');

    // $fechaReferencia = $request->getParam('fechaReferencia'); // "2018-03-28";
    $fechaReferencia = date("Y-m-d", strtotime($facturas[0]['FechaDoc']));
    $fechaBaja = getNow('Y-m-d');

    $serie = getNow('Ymd');
    // Inicio Correlativo
    $selectSec = "SELECT Secuencia FROM Sunat_Resumen WHERE Serie=$serie AND CodSunat=$codSunat ORDER BY Secuencia DESC LIMIT 1";
    $stmtSec = $this->db->query($selectSec);
    $stmtSec->execute();
    $secuencia = $stmtSec->fetch()['Secuencia'];
    $secuencia += 1; // cambiar correlativo
    // Fin Correlativo

    $detalle = array();
    $json = array();
    $n = 0;
    foreach ($facturas as $factura) {
        $n = $n + 1;

        $json['ITEM'] = $n;
        $json['TIPO_COMPROBANTE'] = $factura['CodSunat'];
        $json['SERIE'] = $factura['Serie'];
        $json['NUMERO'] = $factura['Numero'];
        $json['DESCRIPCION'] = $descripcion;
        $detalle[] = $json;
    }

    $data = array(
        "NRO_DOCUMENTO_EMPRESA" => NRO_DOCUMENTO_EMPRESA,
        "RAZON_SOCIAL" => RAZON_SOCIAL_EMPRESA,
        "TIPO_DOCUMENTO" => TIPO_DOCUMENTO_EMPRESA,
        "CODIGO" => "RA",
        "SERIE" => $serie,
        "SECUENCIA" => $secuencia,
        "FECHA_REFERENCIA" => $fechaReferencia,
        "FECHA_BAJA" => $fechaBaja,
        "TIPO_PROCESO" => TIPO_PROCESO,
        "USUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
        "PASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
        "PAS_FIRMA" => PAS_FIRMA,
        "detalle" => $detalle
    );

    $resultado = $new->sendbaja(json_encode($data));
    $me = json_decode($resultado, true);
    $estado = 1;

    if ($me['cod_sunat'] == '0') {
        // insert correlativo del resumen
        $insert = "INSERT INTO Sunat_Resumen (Serie, Secuencia, CodSunat) VALUES ('$serie', $secuencia, '$codSunat')";
        $stmt = $this->db->prepare($insert);
        $inserted = $stmt->execute();
        $lastInsert = $this->db->lastInsertId();
        $estado = 0; // estado 0 anulado / 1 pendiente / 2 es emitido

        // Inicio Consulta Ticket
        $dataTicket = array(
            "TIPO_PROCESO" => TIPO_PROCESO,
            "NRO_DOCUMENTO_EMPRESA" => NRO_DOCUMENTO_EMPRESA,
            "USUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
            "PASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
            "TICKET" => $me['msj_sunat'],
            "TIPO_DOCUMENTO" => "RA",
            "NRO_DOCUMENTO" => $serie . '-' . $secuencia
        );
        $resTicket = $new->sendticket(json_encode($dataTicket));
        $meTicket = json_decode($resTicket, true);
        if ($meTicket['cod_sunat'] == '0') {
            $me = $meTicket;
        }

        // $me['dataTicket'] = $dataTicket;
        $me['meticket'] = $meTicket;
        // Fin Consulta Ticket
    }

    $cadenaIds = [];
    foreach ($facturas as $factura) {
        $cadenaIds[] = $factura['idDocVenta'];
    }

    $sql = "UPDATE Ve_DocVenta SET Estado=$estado, hash_cpe='$me[hash_cpe]', Hash_cdr='$me[hash_cdr]',
        Msj_sunat='$me[msj_sunat]', AnuladoDesc='$descripcion' WHERE idDocVenta IN (" . implode(',', $cadenaIds) . ")";

    //$me['sql'] = $sql;
    $stmt = $this->db->prepare($sql);
    $updated = $stmt->execute();
    $me['Estado'] = $estado;
    $me['data'] = $data; // ocultar en produccion

    return $response->withJson($me);
});

$app->post('/emitirguiaelectronica/{id}', function (Request $request, Response $response, array $args) use ($app) {
    $new = new api_sunat();
    $select = "SELECT * FROM GEN_EMPRESA WHERE IDEMPRESA=1";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    if (empty($data) || $data['CLIENT_ID'] == null || $data['CLIENT_SECRET'] == null || $data['USERNAME'] == null || $data['PASSWORD'] == null) {
        $res = array(
            'error' => 'No tiene emision GUIA GRE',
            'Estado' => 1,
            'msj_sunat' => 'No tiene emision GUIA GRE',
            'success' => false
        );
        return $response->withJson($res);
    }

    $dataTicket = array(
        "GRANT_TYPE" => "password",
        "SCOPE" => "https://api-cpe.sunat.gob.pe",
        "CLIENT_ID" => $data['CLIENT_ID'],
        "CLIENT_SECRET" => $data['CLIENT_SECRET'],
        "USERNAME" => $data['USERNAME'],
        "PASSWORD" => $data['PASSWORD'],

    );
    $resultado = $new->sendGuiaRestToken(json_encode($dataTicket));
    $rest = json_decode(json_decode($resultado, true), true);
    if ($rest == null) {
        return $response->withJson(false);
    }

    $idRemision = $args['id'];

    if ($idRemision) {
        $select = "SELECT * FROM Ve_DocVentaRemision WHERE IdDocVentaRemision = $idRemision";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $guia = $stmt->fetch();

        $select = "SELECT Gen_Producto.*, Ve_DocVentaRemisionDet.Cantidad, Gen_ProductoMedicion.Codigo AS CodigoMedicion 
            FROM Ve_DocVentaRemisionDet
            INNER JOIN Gen_Producto ON Ve_DocVentaRemisionDet.IdProducto = Gen_Producto.IdProducto 
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion 
            WHERE Ve_DocVentaRemisionDet.IdDocVentaRemision = $idRemision";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $guia['productos'] = $stmt->fetchAll();
        $new = new api_sunat();
        $detalle = array();
        $json = array();
        $n = 0;

        foreach ($guia['productos'] as $producto) {
            $n = $n + 1;
            $json['ITEM'] = $n;
            $json["UNIDAD_MEDIDA"] = $producto['CodigoMedicion'];
            $json["CANTIDAD"] = $producto['Cantidad'];
            $json["ORDER_ITEM"] = $n;
            $json["CODIGO"] = $producto['CodigoBarra']; // 02 valor referencial unitario en operaciones no onerosas
            $json["DESCRIPCION"] = trim($producto['Producto']);
            $detalle[] = $json;
        }

        $tipoDocTransportista = strlen($guia['NroDocumentoTransportista']) > 9 ? "6" : "1";
        $codTipoDocChofer = strlen($guia['NroDocChofer']) > 9 ? "6" : "1";
        $tipoDocumentoCliente = strlen($guia['NroDocumentoCliente']) > 9 ? "6" : "1";

        $data = array(
            "NRO_COMPROBANTE" => $guia['Serie'] . "-" . $guia['Numero'],
            "NRO_COMPROBANTE_ARCHIVO" => $guia['Serie'] . "-" . str_pad($guia['Numero'], 8, "0", STR_PAD_LEFT),
            "FECHA_DOCUMENTO" => date("Y-m-d", strtotime($guia['FechaReg'])),
            "HORA_DOCUMENTO" => date("h:i:s", strtotime($guia['FechaReg'])),
            "COD_TIPO_DOCUMENTO" => "09",
            "NOTA" => "GUIA DE REMISION",
            "ITEM_ENVIO" => "1",
            "COD_MOTIVO_TRASLADO" => $guia['CodMotivoTraslado'],
            "DESCRIPCION_MOTIVO_TRASLADO" => $guia['DescripcionMotivoTraslado'],
            "COD_UND_PESO_BRUTO" => $guia['CodUndPesoBruto'],
            "PESO_BRUTO" => $guia['PesoBruto'],
            "TOTAL_BULTOS" => $guia['TotalBultos'],
            "COD_MODALIDAD_TRASLADO" => $guia['CodModalidadTraslado'],
            "DOCUMENTO_REFERENCIA" => $guia['DocumentoReferencia'],
            "FECHA_INICIO" => date("Y-m-d", strtotime($guia['FechaTraslado'])),
            "OBSERVACION" => trim($guia['Observacion']),
            "NRO_DOCUMENTO_TRANSPORTISTA" => trim($guia['NroDocumentoTransportista']),
            "RAZON_SOCIAL_TRANSPORTISTA" => $guia['RazonSocialTransportista'],
            "TIPO_DOCUMENTO_TRANSPORTISTA" => $tipoDocTransportista,
            "PLACA_VEHICULO" => trim($guia['PlacaVehiculo']),
            "COD_TIPO_DOC_CHOFER" => $codTipoDocChofer,
            "NRO_DOC_CHOFER" =>  trim($guia['NroDocChofer']),
            "PLACA_CARRETA" => $guia['PlacaCarreta'],
            "LICENCIA_CHOFER" =>  trim($guia['LicenciaChofer']),
            "NOMBRES_CHOFER" =>  trim($guia['NombresChofer']),
            "APELLIDOS_CHOFER" =>  trim($guia['ApellidosChofer']),
            "NRO_DOCUMENTO_CLIENTE" =>  trim($guia['NroDocumentoCliente']),
            "RAZON_SOCIAL_CLIENTE" => $guia['RazonSocialCliente'],
            "TIPO_DOCUMENTO_CLIENTE" => $tipoDocumentoCliente,
            "DIRECCION_CLIENTE" => $guia['DireccionCliente'],

            "COD_UBIGEO_ORIGEN" => trim($guia['CodUbigeoOrigen']),
            "DIRECCION_ORIGEN" => $guia['DireccionOrigen'],
            "COD_UBIGEO_DESTINO" => trim($guia['CodUbigeoDestino']),
            "DIRECCION_DESTINO" => $guia['DireccionDestino'],

            //=================datos de LA EMPRESA=================	
            "NRO_DOCUMENTO_EMPRESA" => NRO_DOCUMENTO_EMPRESA,
            "TIPO_DOCUMENTO_EMPRESA" => TIPO_DOCUMENTO_EMPRESA,
            "RAZON_SOCIAL_EMPRESA" => RAZON_SOCIAL_EMPRESA,
            "NOMBRE_COMERCIAL_EMPRESA" => NOMBRE_COMERCIAL_EMPRESA,
            'DIRECCION_EMPRESA' => DIRECCION_EMPRESA,
            'TOKEN_GRE' => $rest["access_token"],
            "USUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
            "PASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
            "PAS_FIRMA" => PAS_FIRMA,
            "TIPO_PROCESO" => TIPO_PROCESO,
            "detalle" => $detalle,
        );

        $resultado = $new->sendGuiaRest(json_encode($data));
        $me = json_decode($resultado, true);
        $respuesta = json_decode($me["response"], true);
        $estado = 1;
        $numTicket = null;
        $hashCdr = null;
        $fecRecepcion = null;
        $mensajeSunat = null;
        $validate = null;
        if ($respuesta["numTicket"]) {
            // generamos PDF para su descarga
            $numTicket = $respuesta["numTicket"];
            $fecRecepcion = $respuesta["fecRecepcion"];
            $new->creaPDFGuia(json_encode($data));

            $data = array(
                'TICKET_GUIA' => $numTicket,
                'TOKEN_GRE' => $rest["access_token"],
            );

            $resultado = $new->validateGuiaRest(json_encode($data));
            $result = json_decode($resultado, true);
            $validate = json_decode($result["response"], true);
            if ($validate["codRespuesta"] == "0") {
                $estado = 2;
                $hashCdr = $validate["arcCdr"];
                $mensajeSunat = "GUIA " . $guia['Serie'] . "-" . $guia['Numero'] . " ACEPTADA";
            } else if ($validate["codRespuesta"] == "98") {
                $estado = 1;
                $hashCdr = null;
                $mensajeSunat = "EN PROCESO, PORFAVOR VALIDAR";
            } else {
                $estado = 1;
                $hashCdr = null;
                $mensajeSunat = addslashes($validate["error"]["numError"] . " - " . $validate["error"]["desError"]);
            }
        } else {
            $mensajeSunat = json_encode($respuesta["errors"]);
        }
        $sql = "UPDATE Ve_DocVentaRemision SET Estado=$estado, Hash_cpe='$me[hash_cpe]',  Hash_cdr='$hashCdr', Msj_sunat='$mensajeSunat',NumTicket='$numTicket' WHERE IdDocVentaRemision='$idRemision' ";

        $stmt = $this->db->prepare($sql);
        $updated = $stmt->execute();
        $me['Estado'] = $estado;
        $me['msj_sunat'] = $mensajeSunat;
        $me['cod_sunat'] = $validate["codRespuesta"];
        return $response->withJson($me);
    }
});


$app->post('/validarguiaelectronica/{id}', function (Request $request, Response $response, array $args) use ($app) {
    $new = new api_sunat();
    $select = "SELECT * FROM GEN_EMPRESA WHERE IDEMPRESA=1";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    if (empty($data) || $data['CLIENT_ID'] == null || $data['CLIENT_SECRET'] == null || $data['USERNAME'] == null || $data['PASSWORD'] == null) {
        $res = array(
            'error' => 'No tiene emision GUIA GRE',
            'Estado' => 1,
            'msj_sunat' => 'No tiene emision GUIA GRE',
            'success' => false
        );
        return $response->withJson($res);
    }

    $dataTicket = array(
        "GRANT_TYPE" => "password",
        "SCOPE" => "https://api-cpe.sunat.gob.pe",
        "CLIENT_ID" => $data['CLIENT_ID'],
        "CLIENT_SECRET" => $data['CLIENT_SECRET'],
        "USERNAME" => $data['USERNAME'],
        "PASSWORD" => $data['PASSWORD'],

    );
    $resultado = $new->sendGuiaRestToken(json_encode($dataTicket));
    $rest = json_decode(json_decode($resultado, true), true);
    if ($rest == null) {
        return $response->withJson(false);
    }

    $idRemision = $args['id'];

    if ($idRemision) {
        $select = "SELECT * FROM Ve_DocVentaRemision WHERE IdDocVentaRemision = $idRemision";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $guia = $stmt->fetch();
        $data = array(
            'TICKET_GUIA' => $guia['NumTicket'],
            'TOKEN_GRE' => $rest["access_token"],
        );

        $resultado = $new->validateGuiaRest(json_encode($data));
        $me = json_decode($resultado, true);
        $respuesta = json_decode($me["response"], true);
        $estado = 1;
        if ($respuesta["codRespuesta"] == "0") {
            $estado = 2;
            $hashCdr = $respuesta["arcCdr"];
            $mensajeSunat = "GUIA " . $guia['Serie'] . "-" . $guia['Numero'] . " ACEPTADA";
        } else if ($respuesta["codRespuesta"] == "98") {
            $estado = 1;
            $hashCdr = null;
            $mensajeSunat = "EN PROCESO, PORFAVOR VALIDAR";
        } else {
            $estado = 1;
            $hashCdr = null;
            $mensajeSunat = addslashes($respuesta["error"]["numError"] . " - " . $respuesta["error"]["desError"]);
        }

        $sql = "UPDATE Ve_DocVentaRemision SET Estado=$estado, Hash_cdr='$hashCdr', Msj_sunat='$mensajeSunat' WHERE IdDocVentaRemision='$idRemision' ";
        $stmt = $this->db->prepare($sql);
        $updated = $stmt->execute();
        $me['Estado'] = $estado;
        $me['msj_sunat'] = $mensajeSunat;
        $me['cod_sunat'] = $respuesta["codRespuesta"];
        return $response->withJson($me);
    } else {
        return $response->withJson(false);
    }
});

$app->post('/emitirguiaelectronicaantiguo/{id}', function (Request $request, Response $response, array $args) {
    $idRemision = $args['id'];

    if ($idRemision) {
        $select = "SELECT * FROM Ve_DocVentaRemision WHERE IdDocVentaRemision = $idRemision";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $guia = $stmt->fetch();

        $select = "SELECT Gen_Producto.*, Ve_DocVentaRemisionDet.Cantidad, Gen_ProductoMedicion.Codigo AS CodigoMedicion 
            FROM Ve_DocVentaRemisionDet
            INNER JOIN Gen_Producto ON Ve_DocVentaRemisionDet.IdProducto = Gen_Producto.IdProducto 
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion 
            WHERE Ve_DocVentaRemisionDet.IdDocVentaRemision = $idRemision";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $guia['productos'] = $stmt->fetchAll();
        $new = new api_sunat();
        $detalle = array();
        $json = array();
        $n = 0;

        foreach ($guia['productos'] as $producto) {
            $n = $n + 1;
            $json['ITEM'] = $n;
            $json["UNIDAD_MEDIDA"] = $producto['CodigoMedicion'];
            $json["CANTIDAD"] = $producto['Cantidad'];
            $json["ORDER_ITEM"] = $n;
            $json["CODIGO"] = $producto['CodigoBarra']; // 02 valor referencial unitario en operaciones no onerosas
            $json["DESCRIPCION"] = trim($producto['Producto']);
            $detalle[] = $json;
        }

        $tipoDocTransportista = strlen($guia['NroDocumentoTransportista']) > 9 ? "6" : "1";
        $codTipoDocChofer = strlen($guia['NroDocChofer']) > 9 ? "6" : "1";
        $tipoDocumentoCliente = strlen($guia['NroDocumentoCliente']) > 9 ? "6" : "1";

        $data = array(
            "NRO_COMPROBANTE" => $guia['Serie'] . "-" . $guia['Numero'],
            "FECHA_DOCUMENTO" => date("Y-m-d", strtotime($guia['FechaReg'])),
            "COD_TIPO_DOCUMENTO" => "09",
            "NOTA" => "GUIA DE REMISION",
            "ITEM_ENVIO" => "1",
            "COD_MOTIVO_TRASLADO" => $guia['CodMotivoTraslado'],
            "DESCRIPCION_MOTIVO_TRASLADO" => $guia['DescripcionMotivoTraslado'],
            "DOCUMENTO_REFERENCIA" => $guia['DocumentoReferencia'],
            "COD_UND_PESO_BRUTO" => $guia['CodUndPesoBruto'],
            "PESO_BRUTO" => $guia['PesoBruto'],
            "TOTAL_BULTOS" => $guia['TotalBultos'],
            "COD_MODALIDAD_TRASLADO" => $guia['CodModalidadTraslado'],
            "FECHA_INICIO" => date("Y-m-d", strtotime($guia['FechaTraslado'])),
            "OBSERVACION" => $guia['Observacion'],
            "NRO_DOCUMENTO_TRANSPORTISTA" => $guia['NroDocumentoTransportista'],
            "RAZON_SOCIAL_TRANSPORTISTA" => $guia['RazonSocialTransportista'],
            "TIPO_DOCUMENTO_TRANSPORTISTA" => $tipoDocTransportista,
            "PLACA_VEHICULO" => $guia['PlacaVehiculo'],
            "COD_TIPO_DOC_CHOFER" => $codTipoDocChofer,
            "NRO_DOC_CHOFER" => $guia['NroDocChofer'],
            "PLACA_CARRETA" => $guia['PlacaCarreta'],
            "LICENCIA_CHOFER" => $guia['LicenciaChofer'],
            "NOMBRES_CHOFER" => $guia['NombresChofer'],
            "APELLIDOS_CHOFER" => $guia['ApellidosChofer'],
            "NRO_DOCUMENTO_CLIENTE" => $guia['NroDocumentoCliente'],
            "RAZON_SOCIAL_CLIENTE" => $guia['RazonSocialCliente'],
            "TIPO_DOCUMENTO_CLIENTE" => $tipoDocumentoCliente,
            "DIRECCION_CLIENTE" => $guia['DireccionCliente'],

            "COD_UBIGEO_ORIGEN" => $guia['CodUbigeoOrigen'],
            "DIRECCION_ORIGEN" => $guia['DireccionOrigen'],
            "COD_UBIGEO_DESTINO" => $guia['CodUbigeoDestino'],
            "DIRECCION_DESTINO" => $guia['DireccionDestino'],

            //=================datos de LA EMPRESA=================	
            "NRO_DOCUMENTO_EMPRESA" => NRO_DOCUMENTO_EMPRESA,
            "TIPO_DOCUMENTO_EMPRESA" => TIPO_DOCUMENTO_EMPRESA,
            "RAZON_SOCIAL_EMPRESA" => RAZON_SOCIAL_EMPRESA,
            "NOMBRE_COMERCIAL_EMPRESA" => NOMBRE_COMERCIAL_EMPRESA,
            'DIRECCION_EMPRESA' => DIRECCION_EMPRESA,

            "USUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
            "PASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
            "TIPO_PROCESO" => TIPO_PROCESO,
            "detalle" => $detalle,
        );

        $resultado = $new->sendGuia(json_encode($data));
        $me = json_decode($resultado, true);

        // Datos $me
        $me['data'] = array(
            "NRO_COMPROBANTE" => $data['NRO_COMPROBANTE']
        );

        $estado = 1;
        if ($me['cod_sunat'] == '0') {
            $estado = 2;
            // generamos PDF para su descarga
            $data['hash_cpe'] = $me['hash_cpe'];
            $new->creaPDFGuia(json_encode($data));
        }

        $sql = "UPDATE Ve_DocVentaRemision SET Estado=$estado, Hash_cpe='$me[hash_cpe]', Hash_cdr='$me[hash_cdr]', 
            Msj_sunat='$me[msj_sunat]' WHERE IdDocVentaRemision='$idRemision' ";

        $stmt = $this->db->prepare($sql);
        $updated = $stmt->execute();
        $me['Estado'] = $estado;

        return $response->withJson($me);
    }
});

$app->post('/generarpdfguiaelectronica/{id}', function (Request $request, Response $response, array $args) {
    $idRemision = $args['id'];

    if ($idRemision) {
        $select = "SELECT * FROM Ve_DocVentaRemision WHERE IdDocVentaRemision = $idRemision";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $guia = $stmt->fetch();

        $select = "SELECT Gen_Producto.*, Ve_DocVentaRemisionDet.Cantidad, Gen_ProductoMedicion.Codigo AS CodigoMedicion 
            FROM Ve_DocVentaRemisionDet
            INNER JOIN Gen_Producto ON Ve_DocVentaRemisionDet.IdProducto = Gen_Producto.IdProducto 
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion 
            WHERE Ve_DocVentaRemisionDet.IdDocVentaRemision = $idRemision";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $guia['productos'] = $stmt->fetchAll();
        $new = new api_sunat();
        $detalle = array();
        $json = array();
        $n = 0;

        foreach ($guia['productos'] as $producto) {
            $n = $n + 1;
            $json['ITEM'] = $n;
            $json["UNIDAD_MEDIDA"] = $producto['CodigoMedicion'];
            $json["CANTIDAD"] = $producto['Cantidad'];
            $json["ORDER_ITEM"] = $n;
            $json["CODIGO"] = $producto['CodigoBarra']; // 02 valor referencial unitario en operaciones no onerosas
            $json["DESCRIPCION"] = trim($producto['Producto']);
            $detalle[] = $json;
        }

        $tipoDocTransportista = strlen($guia['NroDocumentoTransportista']) > 9 ? "6" : "1";
        $codTipoDocChofer = strlen($guia['NroDocChofer']) > 9 ? "6" : "1";
        $tipoDocumentoCliente = strlen($guia['NroDocumentoCliente']) > 9 ? "6" : "1";

        $data = array(
            "NRO_COMPROBANTE" => $guia['Serie'] . "-" . $guia['Numero'],
            "FECHA_DOCUMENTO" => date("Y-m-d", strtotime($guia['FechaReg'])),
            "COD_TIPO_DOCUMENTO" => "09",
            "NOTA" => "GUIA DE REMISION",
            "ITEM_ENVIO" => "1",
            "COD_MOTIVO_TRASLADO" => $guia['CodMotivoTraslado'],
            "DESCRIPCION_MOTIVO_TRASLADO" => $guia['DescripcionMotivoTraslado'],
            "DOCUMENTO_REFERENCIA" => $guia['DocumentoReferencia'],
            "COD_UND_PESO_BRUTO" => $guia['CodUndPesoBruto'],
            "PESO_BRUTO" => $guia['PesoBruto'],
            "TOTAL_BULTOS" => $guia['TotalBultos'],
            "COD_MODALIDAD_TRASLADO" => $guia['CodModalidadTraslado'],
            "FECHA_INICIO" => date("Y-m-d", strtotime($guia['FechaTraslado'])),
            "OBSERVACION" => $guia['Observacion'],
            "NRO_DOCUMENTO_TRANSPORTISTA" => $guia['NroDocumentoTransportista'],
            "RAZON_SOCIAL_TRANSPORTISTA" => $guia['RazonSocialTransportista'],
            "TIPO_DOCUMENTO_TRANSPORTISTA" => $tipoDocTransportista,
            "PLACA_VEHICULO" => $guia['PlacaVehiculo'],
            "COD_TIPO_DOC_CHOFER" => $codTipoDocChofer,
            "NRO_DOC_CHOFER" => $guia['NroDocChofer'],
            "PLACA_CARRETA" => $guia['PlacaCarreta'],
            "LICENCIA_CHOFER" => $guia['LicenciaChofer'],
            "NOMBRES_CHOFER" => $guia['NombresChofer'],
            "APELLIDOS_CHOFER" => $guia['ApellidosChofer'],
            "NRO_DOCUMENTO_CLIENTE" => $guia['NroDocumentoCliente'],
            "RAZON_SOCIAL_CLIENTE" => $guia['RazonSocialCliente'],
            "TIPO_DOCUMENTO_CLIENTE" => $tipoDocumentoCliente,
            "DIRECCION_CLIENTE" => $guia['DireccionCliente'],

            "COD_UBIGEO_ORIGEN" => $guia['CodUbigeoOrigen'],
            "DIRECCION_ORIGEN" => $guia['DireccionOrigen'],
            "COD_UBIGEO_DESTINO" => $guia['CodUbigeoDestino'],
            "DIRECCION_DESTINO" => $guia['DireccionDestino'],

            //=================datos de LA EMPRESA=================	
            "NRO_DOCUMENTO_EMPRESA" => NRO_DOCUMENTO_EMPRESA,
            "TIPO_DOCUMENTO_EMPRESA" => TIPO_DOCUMENTO_EMPRESA,
            "RAZON_SOCIAL_EMPRESA" => RAZON_SOCIAL_EMPRESA,
            "NOMBRE_COMERCIAL_EMPRESA" => NOMBRE_COMERCIAL_EMPRESA,
            'DIRECCION_EMPRESA' => DIRECCION_EMPRESA,

            "USUARIO_SOL_EMPRESA" => USUARIO_SOL_EMPRESA,
            "PASS_SOL_EMPRESA" => PASS_SOL_EMPRESA,
            "TIPO_PROCESO" => TIPO_PROCESO,
            "detalle" => $detalle,
        );

        $new->creaPDFGuia(json_encode($data));
    }
});

$app->get('/reporte/habitaciones', function (Request $request, Response $response, array $args) use ($app) {
    $fechaInicio = $request->getParam('fechaInicio');
    $fechaFin = $request->getParam('fechaFin');

    $select = "SELECT Ve_PreOrden.IdPreOrden, Ve_DocVenta.idDocVenta, PreOrdenHabitacion.Producto, PreOrdenHabitacion.ProductoMarca, Ve_DocVentaCliente.Cliente,
        Ve_DocVentaCliente.Direccion, Ve_DocVentaCliente.DniRuc,
        Ve_DocVentaCliente.Sexo, Ve_DocVentaCliente.Ocupacion, Ve_DocVentaCliente.FechaNacimiento, Ve_DocVentaCliente.Nacionalidad,
        Ve_PreOrden.FechaReg, Ve_PreOrden.LugarProcedencia, Ve_PreOrden.MedioTransporte, Ve_PreOrden.ProximoDestino,
        Ve_PreOrden.FechaAlquilerInicio AS FechaAlquilerInicio, Ve_PreOrden.FechaAlquilerFin,
        /* DocVentaHabitacion.FechaAlquilerFin,  */
        (DocVentaHabitacion.Cantidad * DocVentaHabitacion.Precio) - DocVentaHabitacion.Descuento AS Tarifa
        FROM Ve_PreOrden

        INNER JOIN Ve_DocVentaCliente ON Ve_PreOrden.IdCliente = Ve_DocVentaCliente.IdCliente
        LEFT JOIN Ve_DocVenta ON Ve_PreOrden.IdDocVenta = Ve_DocVenta.idDocVenta

        INNER JOIN
        (SELECT  Ve_PreOrdenDet.IdPreOrden, Gen_Producto.EsHabitacion, Gen_Producto.Producto, Gen_ProductoMarca.ProductoMarca
        FROM Ve_PreOrdenDet
        INNER JOIN Gen_Producto ON Ve_PreOrdenDet.IdProducto = Gen_Producto.IdProducto
        LEFT JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
        WHERE Gen_Producto.EsHabitacion = 1
        GROUP BY Ve_PreOrdenDet.IdPreOrden) AS PreOrdenHabitacion ON Ve_PreOrden.IdPreOrden = PreOrdenHabitacion.IdPreOrden

        LEFT JOIN
        (SELECT Ve_DocVentaDet.IdDocVenta, Ve_DocVentaDet.IdProducto, Ve_DocVentaDet.FechaAlquilerInicio,
        Ve_DocVentaDet.FechaAlquilerFin, Ve_DocVentaDet.Cantidad, Ve_DocVentaDet.Precio, Ve_DocVentaDet.Descuento
        FROM Ve_DocVentaDet
        INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
        WHERE Gen_Producto.EsHabitacion = 1
        GROUP BY Ve_DocVentaDet.IdDocVenta) AS DocVentaHabitacion ON Ve_PreOrden.IdDocVenta = DocVentaHabitacion.IdDocVenta

        WHERE PreOrdenHabitacion.EsHabitacion = 1 AND Ve_PreOrden.FechaAlquilerInicio BETWEEN '" . $fechaInicio . " 00:00:00'" . " AND '" . $fechaFin . " 23:59:59'" . "
        ORDER BY Ve_PreOrden.IdPreOrden DESC";
    //var_dump($select); exit();

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

//REPORTE UTILIDAD BRUTAL EXECEL
$app->get('/reporte/utilidadbruta/excel', function (Request $request, Response $response, array $args) use ($app) {
    $fechaInicio = $request->getParam('fechaInicio');
    $fechaFin = $request->getParam('fechaFin');

    $almacen = $request->getParam('almacen');
    $almacenNombre = "TODOS";
    $res = $app->subRequest('GET', '/reporte/utlidadbruta', 'fechaInicio=' . $fechaInicio . '&fechaFin=' . $fechaFin . '&almacen=' . $almacen);
    $productos = (string) $res->getBody();
    $productos = json_decode($productos, true);

    if (!empty($almacen)) {
        $select = "SELECT Almacen FROM Lo_Almacen WHERE IdAlmacen =" . $almacen;
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetch();
        $almacenNombre = $data['Almacen'];
    }

    $excel = new Spreadsheet();
    //$sheet = $excel->setActiveSheetIndex(0);
    $sheet = $excel->getActiveSheet();

    $sheet->setCellValue('A1', 'REPORTE DE UTILIDAD BRUTA NEUROSYSTEM PERU S.A.C.');
    $sheet->setCellValue('A2', 'ALMACEN: ' . $almacenNombre);
    $sheet->setCellValue('A3', 'FECHA INICIO: ' . $fechaInicio);
    $sheet->setCellValue('A4', 'FECHA FIN: ' . $fechaFin);
    $sheet->setCellValue('A5', 'ID PROD.');
    $sheet->setCellValue('B5', 'PRODUCTO');
    $sheet->setCellValue('C5', 'CANTIDAD');
    $sheet->setCellValue('D5', 'PROM. PRECIO COSTO');
    $sheet->setCellValue('E5', 'PROM. PRECIO VENTA');
    $sheet->setCellValue('F5', 'TOTAL VENTA');
    $sheet->setCellValue('G5', 'TOTAL COSTO');
    $sheet->setCellValue('H5', 'UTILIDAD BRUTA');
    $sheet->setCellValue('I5', '% UTILIDAD');
    $cont = 6;
    $sumTotalVenta = 0;
    $sumTotalCosto = 0;
    $sumUtilidadBruta = 0;
    $porcUtilidadBruta = 0;
    foreach ($productos as $prod) {
        $sheet->setCellValue('A' . $cont, $prod['IdProducto']);
        $sheet->setCellValue('B' . $cont, $prod['Producto']);
        $sheet->setCellValue('C' . $cont, $prod['Cantidad']);
        $sheet->setCellValue('D' . $cont, $prod['PrecioCosto']);
        $sheet->setCellValue('E' . $cont, $prod['PrecioVenta']);
        $sheet->setCellValue('F' . $cont, $prod['TotalVenta']);
        $sheet->setCellValue('G' . $cont, $prod['TotalCosto']);
        $sheet->setCellValue('H' . $cont, $prod['UtilidadBruta']);
        $sheet->setCellValue('I' . $cont, $prod['PorcentajeUti']);
        $sumTotalVenta += (float)$prod['TotalVenta'];
        $sumTotalCosto += (float) $prod['TotalCosto'];
        $sumUtilidadBruta += (float) $prod['UtilidadBruta'];

        $cont += 1;
    }
    $porcUtilidadBruta = round(($sumUtilidadBruta * 100 / $sumTotalVenta / 100 * 100), 2);
    $sheet->setCellValue('F' . ($cont), round($sumTotalVenta, 2));
    $sheet->setCellValue('G' . ($cont), round($sumTotalCosto, 2));
    $sheet->setCellValue('H' . ($cont), round($sumUtilidadBruta, 2));
    $sheet->setCellValue('I' . ($cont), $porcUtilidadBruta);
    $excel->getActiveSheet()->mergeCells('A' . $cont . ':E' . $cont);
    $excel->getActiveSheet()->getStyle('A' . $cont)->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A' . ($cont), ' SUMATORIA ');
    foreach (range('B', 'I') as $columnID) {
        $excel->getActiveSheet()->getColumnDimension($columnID)
            ->setAutoSize(true);
    }
    foreach (array('A5', 'B5', 'C5', 'D5', 'E5', 'F5', 'G5', 'H5', 'I5') as $columnID) {
        $excel->getActiveSheet()->getStyle($columnID)->getBorders()->getOutline()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('#000000'));
    }
    $excel->getActiveSheet()->getStyle('A1:A3')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A5:I5')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A' . ($cont) . ':I' . ($cont))->getFont()->setBold(true);

    $excel->getActiveSheet()->getStyle('A5:I5')->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFE6E6FA');

    $excelWriter = new Xlsx($excel);

    $fileName = 'reporte-utilidad ' . $almacenNombre . ' ' . getNow('Y-m-d-H-i-s') .  '.xlsx';
    $excelFileName = __DIR__ . '/reporte/' . $fileName;
    $excelWriter->save($excelFileName);
    // For Excel2007 and above .xlsx files
    // $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    /* $response = $response->withHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    $stream = fopen($excelFileName, 'r+');
    return $response->withBody(new \Slim\Http\Stream($stream));*/
    echo "<script>window.location.href = '/api/reporte/" . $fileName . "'</script>";
    exit;
});

$app->get('/reporte/ventasproducto', function (Request $request, Response $response, array $args) use ($app) {
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaInicio = $request->getParam('fechaInicio');
    $fechaFin = $request->getParam('fechaFin');
    $declarado = $request->getParam('declarado') ? 1 : 0;

    $select = "SELECT Ve_DocVenta.idDocVenta, Ve_DocVenta.FechaDoc, Ve_DocVentaTipoDoc.TipoDoc, Ve_DocVentaTipoDoc.TieneIgv,
        Ve_DocVenta.Anulado, Ve_DocVenta.Serie, Ve_DocVenta.Numero, Ve_DocVentaCliente.Cliente, Ve_DocVenta.UsuarioReg,
        Ve_DocVentaTipoDoc.CodSunat, Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.Direccion, Ve_DocVentaTipoDoc.CodigoIgv,
        IFNULL(SUM(ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2)), 0) AS Total,
        Gen_Producto.IdProducto,
        Gen_Producto.Producto,
        Ve_DocVentaDet.Precio,
        Ve_DocVentaDet.Cantidad,
        Ve_DocVentaDet.Descuento
        FROM
            Ve_DocVentaDet
                INNER JOIN
            Ve_DocVenta ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta
                INNER JOIN
            Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
                LEFT JOIN
            Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente
                INNER JOIN 
                Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
        WHERE
            Ve_DocVentaTipoDoc.VaRegVenta = $declarado
                AND Ve_DocVenta.FechaDoc BETWEEN CAST('" . $fechaInicio . "' AS DATETIME) AND CONCAT('" . $fechaFin . "', ' 23:59:59')
                AND Ve_DocVentaTipoDoc.CodSunat IN ('01', '03')
        GROUP BY Ve_DocVenta.idDocVenta
        ORDER BY Ve_DocVenta.FechaDoc DESC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $ventas = $stmt->fetchAll();

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/reporteventas.xlsx');
    $sheet = $spreadsheet->getSheet(0);

    $cont = 2;
    foreach ($ventas as $ven) {
        $exonerado = 0;
        $igv = 0;
        $subtotal = $ven['Total'];
        // $estado = 1; // 1 activo, 2 anulado
        if ($ven['TieneIgv']) {
            // $igv = $ven['Total'] * 0.18;
            // $subtotal = $ven['Total'] - $igv;
            $subtotal = number_format($ven['Total'] / 1.18, 2, '.', '');
            $igv = number_format($ven['Total'] - ($ven['Total'] / 1.18), 2, '.', '');
        }

        /*if ($ven['CodigoIgv'] == '20') {
            $subtotal = 0;
            $igv = 0;  
            $exonerado = $ven['Total'];
        }*/

        if ($ven['Anulado']) {
            $subtotal = 0;
            $igv = 0;
            $exonerado = 0;
            $ven['Total'] = 0;
            $ven['Cantidad'] = 0;
            $estado = 2;
        }

        $sheet->setCellValue('A' . $cont, date("d/m/Y", strtotime($ven['FechaDoc'])));
        $sheet->setCellValue('B' . $cont, $ven['CodSunat']);
        $sheet->setCellValue('C' . $cont, $ven['Anulado'] == 1 ? 'ANULADO' : 'ACTIVO');
        $sheet->setCellValue('D' . $cont, $ven['Serie']);
        $sheet->setCellValue('E' . $cont, $ven['Numero']);
        $sheet->setCellValue('F' . $cont, $ven['DniRuc']);
        $sheet->setCellValue('G' . $cont, $ven['Cliente']);
        $sheet->setCellValue('H' . $cont, $subtotal);
        $sheet->setCellValue('I' . $cont, $igv);
        $sheet->setCellValue('J' . $cont, $ven['Total']);
        $sheet->setCellValue('L' . $cont, $ven['Producto']);
        $sheet->setCellValue('M' . $cont, $ven['Precio']);
        $sheet->setCellValue('N' . $cont, $ven['Cantidad']);

        $sheet->duplicateStyle($sheet->getStyle('A' . $cont), 'A' . ($cont + 1));
        $sheet->duplicateStyle($sheet->getStyle('B' . $cont), 'B' . ($cont + 1));
        $sheet->duplicateStyle($sheet->getStyle('C' . $cont), 'C' . ($cont + 1));
        $sheet->duplicateStyle($sheet->getStyle('D' . $cont), 'D' . ($cont + 1));
        $sheet->duplicateStyle($sheet->getStyle('E' . $cont), 'E' . ($cont + 1));
        $sheet->duplicateStyle($sheet->getStyle('F' . $cont), 'F' . ($cont + 1));
        $sheet->duplicateStyle($sheet->getStyle('G' . $cont), 'G' . ($cont + 1));
        $sheet->duplicateStyle($sheet->getStyle('H' . $cont), 'H' . ($cont + 1));
        $sheet->duplicateStyle($sheet->getStyle('I' . $cont), 'I' . ($cont + 1));
        $sheet->duplicateStyle($sheet->getStyle('J' . $cont), 'J' . ($cont + 1));
        $sheet->duplicateStyle($sheet->getStyle('K' . $cont), 'K' . ($cont + 1));
        $sheet->duplicateStyle($sheet->getStyle('L' . $cont), 'L' . ($cont + 1));
        $sheet->duplicateStyle($sheet->getStyle('M' . $cont), 'M' . ($cont + 1));
        $sheet->duplicateStyle($sheet->getStyle('N' . $cont), 'N' . ($cont + 1));


        $cont += 1;
    }

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('reporte/reporteventas.xlsx');

    // return $response->withRedirect('/api/reporte/reporteventas.xlsx'); 

    echo "<script>window.location.href = '/api/reporte/reporteventas.xlsx'</script>";
    exit;
});

$app->get('/reporte/ventas', function (Request $request, Response $response, array $args) use ($app) {
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaInicio = $request->getParam('fechaInicio');
    $fechaFin = $request->getParam('fechaFin');
    $declarado = $request->getParam('declarado') ? 1 : 0;

    $res = $app->subRequest('GET', '/ventas', '&filter[fechaInicio]=' . $fechaInicio . '&filter[fechaFin]=' . $fechaFin . '&filter[declarado]=' . $declarado . '&sortBy=Ve_DocVenta.FechaDoc&sortDesc=DESC' . '&idAlmacen=' . $idAlmacen);
    // $res = $app->subRequest('GET', '/ventas', 'idAlmacen=' . $idAlmacen . '&filter[fechaInicio]=' . $fechaInicio . '&filter[fechaFin]=' . $fechaFin . '&filter[declarado]=' . $declarado . '&sortBy=Ve_DocVenta.FechaDoc&sortDesc=DESC');
    $ventas = (string) $res->getBody();
    $ventas = json_decode($ventas, true);
    // print_r($ventas);exit();

    $excel = new Spreadsheet();
    //$sheet = $excel->setActiveSheetIndex(0);
    $sheet = $excel->getActiveSheet();
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'FECHAE');
    $sheet->setCellValue('C1', 'FECHAV');
    $sheet->setCellValue('D1', 'TIPOC');
    $sheet->setCellValue('E1', 'SERIE');
    $sheet->setCellValue('F1', 'NUMERO');
    $sheet->setCellValue('G1', 'TIPODOC');
    $sheet->setCellValue('H1', 'DOCUMENTO');
    $sheet->setCellValue('I1', 'NOMBRE');
    $sheet->setCellValue('J1', 'BASEI');
    $sheet->setCellValue('K1', 'IGV');
    $sheet->setCellValue('L1', 'EXONERADO');
    $sheet->setCellValue('M1', 'RETENCION');
    $sheet->setCellValue('N1', 'TOTAL');

    // $sheet->setCellValue('N1', 'ES ANULADO');


    $cont = 3;
    foreach ($ventas as $ven) {
        $exonerado = 0;
        $igv = 0;
        $subtotal = $ven['Total'];
        if ($ven['TieneIgv']) {
            // $igv = $ven['Total'] * 0.18;
            // $subtotal = $ven['Total'] - $igv;
            $subtotal = round($ven['Total'] / 1.18, 2);
            $igv = round($ven['Total'] - $subtotal, 2);
        }

        if ($ven['CodigoIgv'] == '20') {
            $subtotal = 0;
            $igv = 0;
            $exonerado = $ven['Total'];
        }

        if ($ven['Anulado']) {
            $subtotal = 0;
            $igv = 0;
            $exonerado = 0;
            $ven['Total'] = 0;
        }

        if ($ven['CodSunat'] == '07') {
            $exonerado = -1 * $exonerado;
            $ven['Total'] = -1 * $ven['Total'];
        }

        $sheet->setCellValue('A' . $cont, $ven['idDocVenta']);
        $sheet->setCellValue('B' . $cont, date("d/m/Y", strtotime($ven['FechaDoc'])));
        $sheet->setCellValue('C' . $cont, date("d/m/Y", strtotime($ven['FechaDoc'])));
        $sheet->setCellValue('D' . $cont, $ven['CodSunat']);
        $sheet->setCellValue('E' . $cont, $ven['Serie']);
        $sheet->setCellValue('F' . $cont, $ven['Numero']);

        // $sheet->setCellValue('F'.$cont, $ven['TipoDoc']);
        $sheet->setCellValue('G' . $cont, strlen($ven['DniRuc']) > 9 ? "6" : "1");
        $sheet->setCellValue('H' . $cont, $ven['DniRuc']);
        $sheet->setCellValue('I' . $cont, $ven['Cliente']);
        $sheet->setCellValue('J' . $cont, $subtotal);
        $sheet->setCellValue('K' . $cont, $igv);
        $sheet->setCellValue('L' . $cont, $exonerado);
        $sheet->setCellValue('M' . $cont, 0);
        $sheet->setCellValue('N' . $cont, $ven['Total']);

        // $sheet->setCellValue('N'.$cont, $ven['Anulado']);;

        $cont += 1;
    }

    $excelWriter = new Xlsx($excel);

    $fileName = 'reporteventas.xlsx';
    $excelFileName = __DIR__ . '/reporte/' . $fileName;
    $excelWriter->save($excelFileName);
    // For Excel2007 and above .xlsx files
    // $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    /*$response = $response->withHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    $stream = fopen($excelFileName, 'r+');
    return $response->withBody(new \Slim\Http\Stream($stream));*/

    echo "<script>window.location.href = '/api/reporte/" . $fileName . "'</script>";
    exit;
});

$app->get('/reporte/rankingclientes', function (Request $request, Response $response, array $args) use ($app) {
    $idalmacen = $request->getParam('idalmacen');
    $res = $app->subRequest('GET', "ranking/clientes", 'idalmacen=' . $idalmacen);

    $deudacliente = (string) $res->getBody();
    $deudacliente = json_decode($deudacliente, true);

    $excel = new Spreadsheet();
    //$sheet = $excel->setActiveSheetIndex(0);
    $sheet = $excel->getActiveSheet();
    $sheet->setCellValue('A1', 'DNI / RUC');
    $sheet->setCellValue('B1', 'CLIENTE');
    $sheet->setCellValue('C1', 'FECHA NACIMIENTO');
    $sheet->setCellValue('D1', 'PUNTOS');
    $sheet->setCellValue('E1', 'TOTAL');

    $cont = 3;
    foreach ($deudacliente as $prod) {
        $sheet->setCellValue('A' . $cont, $prod['DniRuc']);
        $sheet->setCellValue('B' . $cont, $prod['Cliente']);
        $sheet->setCellValue('C' . $cont, date("d/m/Y", strtotime($prod['FechaNacimiento'])));
        $sheet->setCellValue('D' . $cont, $prod['Puntos']);
        $sheet->setCellValue('E' . $cont, 'S/. ' . $prod['Total']);
        $cont += 1;
    }

    $excelWriter = new Xlsx($excel);

    $fileName = 'rankingclientes' . getNow('Y-m-d-H-i-s') .  '.xlsx';
    $excelFileName = __DIR__ . '/reporte/' . $fileName;
    $excelWriter->save($excelFileName);

    echo "<script>window.location.href = '/api/reporte/" . $fileName . "'</script>";
    exit;
});


// REPORTES
$app->get('/reporte/stock', function (Request $request, Response $response, array $args) use ($app) {
    $idAlmacen = $request->getParam('idAlmacen');
    $filter = $request->getParam('filter');
    $condicion = "";
    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $condicion .= '&sortBy=' . $sortBy . '&sortDesc=' . $sortDesc;
    }
    $rangoFechas = 0;
    $almacenNombre = "";
    $fechaDesde = $request->getParam('fechaDesde') && $request->getParam('fechaDesde') != '' ? $request->getParam('fechaDesde') . ' 00:00:00' : false;
    $fechaHasta = $request->getParam('fechaHasta') && $request->getParam('fechaHasta') != '' ? $request->getParam('fechaHasta') . ' 23:59:59' : false;

    $filterStr = http_build_query(array(
        'filter' => $filter
    ));

    if ($fechaDesde != false && $fechaHasta != false) {
        $rangoFechas = 1;
        $res = $app->subRequest('GET', '/productos/stock', 'idAlmacen=' . $idAlmacen . '&fechaDesde=' . $fechaDesde . '&fechaHasta=' . $fechaHasta . $condicion . '&' . $filterStr);
    } else {
        $rangoFechas = 0;
        $res = $app->subRequest('GET', '/productos/stock', 'idAlmacen=' . $idAlmacen . $condicion . '&' . $filterStr);
    }

    $productos = (string) $res->getBody();
    $productos = json_decode($productos, true);


    if (!empty($idAlmacen)) {
        $select = "SELECT Almacen FROM Lo_Almacen WHERE IdAlmacen =" . $idAlmacen;
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetch();
        $almacenNombre = $data['Almacen'];
    }



    $excel = new Spreadsheet();
    //$sheet = $excel->setActiveSheetIndex(0);
    $sheet = $excel->getActiveSheet();
    $sheet->setCellValue('A1', 'REPORTE DE STOCK - NEUROSYSTEM PERU S.A.C - FECHA DEL REPORTE: ' . getNow());
    $excel->getActiveSheet()->mergeCells('A1:J1');
    $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
    $excel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A2')->getFont()->setSize(12);
    $excel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A3')->getFont()->setSize(12);
    $sheet->setCellValue('A2', 'ALMACEN: ' . $almacenNombre);
    $sheet->setCellValue('A3', 'FILTRO: ' . ($rangoFechas == 1 ? 'POR RANGO DE FECHAS DESDE: ' . $fechaDesde . ' -  HASTA:' . $fechaHasta : 'HASTA LA FECHA ACTUAL: ' . getNow()));
    $sheet->setCellValue('A5', 'CODIGO');
    $sheet->setCellValue('B5', 'CATEGORIA');
    $sheet->setCellValue('C5', 'PRODUCTO');
    $sheet->setCellValue('D5', 'MARCA');
    $sheet->setCellValue('E5', 'PRESENTACION');
    $sheet->setCellValue('F5', 'PRECIO COSTO');
    $sheet->setCellValue('G5', 'PRECIO CONTADO');
    $sheet->setCellValue('H5', 'STOCK');
    $sheet->setCellValue('I5', 'VALORIZADO COSTO');
    $sheet->setCellValue('J5', 'VALORIZADO VENTA');

    $cont = 6;
    foreach ($productos as $prod) {
        $sheet->setCellValue('A' . $cont, $prod['CodigoBarra']);
        $sheet->setCellValue('B' . $cont, $prod['ProductoCategoria']);
        $sheet->setCellValue('C' . $cont, $prod['Producto']);
        $sheet->setCellValue('D' . $cont, $prod['ProductoMarca']);
        $sheet->setCellValue('E' . $cont, $prod['ProductoPresentacion']);;
        $sheet->setCellValue('F' . $cont, $prod['PrecioCosto']);
        $sheet->setCellValue('G' . $cont, $prod['PrecioContado']);
        $sheet->setCellValue('H' . $cont, $prod['stock']);
        $sheet->setCellValue('I' . $cont, round($prod['PrecioCosto'] * $prod['stock'], 2));
        $sheet->setCellValue('J' . $cont, round($prod['PrecioContado'] * $prod['stock'], 2));
        $cont += 1;
    }

    foreach (range('B', 'J') as $columnID) {
        $excel->getActiveSheet()->getColumnDimension($columnID)
            ->setAutoSize(true);
    }

    foreach (array('A5', 'B5', 'C5', 'D5', 'E5', 'F5', 'G5', 'H5', 'I5', 'J5') as $columnID) {
        $excel->getActiveSheet()->getStyle($columnID)->getBorders()->getOutline()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('#000000'));
    }

    $excel->getActiveSheet()->getStyle('A5:J5')->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFE6E6FA');

    $excel->getActiveSheet()->getStyle('A5:J5')->getAlignment()->setHorizontal('center');

    $excelWriter = new Xlsx($excel);

    $fileName = 'stock' . getNow('Y-m-d-H-i-s') .  '.xlsx';
    $excelFileName = __DIR__ . '/reporte/' . $fileName;
    $excelWriter->save($excelFileName);
    // For Excel2007 and above .xlsx files
    // $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    /*$response = $response->withHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    $stream = fopen($excelFileName, 'r+');
    return $response->withBody(new \Slim\Http\Stream($stream));*/

    echo "<script>window.location.href = '/api/reporte/" . $fileName . "'</script>";
    exit;
});

$app->get('/reporte/stock/minimo', function (Request $request, Response $response, array $args) use ($app) {
    $idAlmacen = $request->getParam('idAlmacen');
    $almacenNombre = "";
    $condicion = "&stockminimo=1&condicionstockminimo=1";


    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $condicion .= '&sortBy=' . $sortBy . '&sortDesc=' . $sortDesc;
    }

    $res = $app->subRequest('GET', '/productos/stock', 'idAlmacen=' . $idAlmacen . $condicion);

    $productos = (string) $res->getBody();
    $productos = json_decode($productos, true);

    if (!empty($idAlmacen)) {
        $select = "SELECT Almacen FROM Lo_Almacen WHERE IdAlmacen =" . $idAlmacen;
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetch();
        $almacenNombre = $data['Almacen'];
    }



    $excel = new Spreadsheet();
    //$sheet = $excel->setActiveSheetIndex(0);
    $sheet = $excel->getActiveSheet();
    $sheet->setCellValue('A1', 'REPORTE DE STOCK MINIMO - NEUROSYSTEM PERU S.A.C - FECHA DEL REPORTE: ' . getNow());
    $excel->getActiveSheet()->mergeCells('A1:J1');
    $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
    $excel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A2')->getFont()->setSize(12);
    $excel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A3')->getFont()->setSize(10);
    $sheet->setCellValue('A2', 'ALMACEN: ' . $almacenNombre);
    $sheet->setCellValue('A3', '*Reporte de stock mínimo que cumplen la condición mínimo stock*');
    $sheet->setCellValue('A5', 'CODIGO');
    $sheet->setCellValue('B5', 'CATEGORIA');
    $sheet->setCellValue('C5', 'PRODUCTO');
    $sheet->setCellValue('D5', 'MARCA');
    $sheet->setCellValue('E5', 'STOCK MIN.');
    $sheet->setCellValue('F5', 'STOCK');
    $sheet->setCellValue('G5', 'PROVEEDOR ULTIMO');
    $sheet->setCellValue('H5', 'TELEFONO ULTIMO');

    $cont = 6;
    foreach ($productos as $prod) {
        $sheet->setCellValue('A' . $cont, $prod['CodigoBarra']);
        $sheet->setCellValue('B' . $cont, $prod['ProductoCategoria']);
        $sheet->setCellValue('C' . $cont, $prod['Producto']);
        $sheet->setCellValue('D' . $cont, $prod['ProductoMarca']);
        $sheet->setCellValue('E' . $cont, $prod['StockMinimo']);
        $sheet->setCellValue('F' . $cont, $prod['stock']);
        $sheet->setCellValue('G' . $cont, $prod['ProveedorUltimo']);
        $sheet->setCellValue('H' . $cont, $prod['TelefonoUltimo']);
        $cont += 1;
    }

    foreach (range('B', 'H') as $columnID) {
        $excel->getActiveSheet()->getColumnDimension($columnID)
            ->setAutoSize(true);
    }

    foreach (array('A5', 'B5', 'C5', 'D5', 'E5', 'F5', 'G5', 'H5') as $columnID) {
        $excel->getActiveSheet()->getStyle($columnID)->getBorders()->getOutline()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('#000000'));
    }

    $excel->getActiveSheet()->getStyle('A5:H5')->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFE6E6FA');

    $excel->getActiveSheet()->getStyle('A5:H5')->getAlignment()->setHorizontal('center');

    $excelWriter = new Xlsx($excel);

    $fileName = 'stockminimo-' . $almacenNombre . " " . getNow('Y-m-d-H-i-s') .  '.xlsx';
    $excelFileName = __DIR__ . '/reporte/' . $fileName;
    $excelWriter->save($excelFileName);
    // For Excel2007 and above .xlsx files
    // $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    /*$response = $response->withHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    $stream = fopen($excelFileName, 'r+');
    return $response->withBody(new \Slim\Http\Stream($stream));*/

    echo "<script>window.location.href = '/api/reporte/" . $fileName . "'</script>";
    exit;
});

$app->get('/reporte/stockmensual', function (Request $request, Response $response, array $args) use ($app) {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/stockmensual.xlsx');
    $sheet = $spreadsheet->getSheet(0);
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaHasta = $request->getParam('fechaHasta') ? $request->getParam('fechaHasta') : getNow();

    // if ($request->getParam('controlaStock')) {
    //     $res = $app->subRequest('GET', '/productos/stock', 'controlaStock=1&idAlmacen=' . $idAlmacen . '&noLimit=1');
    // } else {
    $res = $app->subRequest('GET', '/productos/stock', 'idAlmacen=' . $idAlmacen . '&noLimit=1' . '&fechaHasta=' . $fechaHasta);
    // }

    $productos = (string) $res->getBody();
    $productos = json_decode($productos, true);

    $sheet->getCell('E1')->setValue($fechaHasta);

    $init = 3;
    foreach ($productos as $prod) {
        $sheet->getCell('A' . $init)->setValue($prod['CodigoBarra']);
        $sheet->getCell('B' . $init)->setValue($prod['Producto']);
        $sheet->getCell('L' . $init)->setValue($prod['stock']);
        $sheet->getCell('M' . $init)->setValue($prod['PrecioCosto']);
        $sheet->getCell('N' . $init)->setValue($prod['stock'] * $prod['PrecioCosto']);
        $init += 1;
    }

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('reporte/stockmensual.xlsx');

    echo "<script>window.location.href = '/api/reporte/stockmensual.xlsx'</script>";
    exit;
    // return $response->withRedirect('/api/reporte/stockmensual.xlsx'); 
});


function kardexSaldo($kardex)
{
    $newKardex = [];
    $saldo = 0;
    $saldoCostoTotal = 0;
    $saldoCostoUnitario = 0;
    foreach ($kardex as $kar) {
        $saldo += ($kar['IngresoCantidad'] - $kar['SalidaCantidad']);
        $kar['saldo'] = round($saldo, 2);
        if ($kar['IngresoCantidad'] > 0) {
            $saldoCostoTotal = $saldoCostoTotal + ($kar['IngresoCantidad'] * $kar['IngresoPrecio']);
            // dxerror division por cero
            if ($saldo == 0) {
                $saldoCostoUnitario = $saldoCostoTotal;
            } else {
                $saldoCostoUnitario = $saldoCostoTotal / $saldo;
            }
        } else {
            // la salida Precio es el ultimo costo unitario de saldo
            $kar['SalidaPrecio'] = round($saldoCostoUnitario, 2);

            $saldoCostoTotal = $saldo * $kar['SalidaPrecio'];
            $saldoCostoUnitario = $kar['SalidaPrecio'];
        }
        $kar['saldoCostoUnitario'] = round($saldoCostoUnitario, 2);
        $kar['saldoCostoTotal'] = round($saldoCostoTotal, 2);

        array_push($newKardex, $kar);
    }
    return $newKardex;
}
function kardexSaldoFecha($kardex, $fechaDesde)
{
    $inicialEntradas = 0;
    $inicialSalidas = 0;
    $inicialSaldoCostoTotal = 0;
    $inicialSaldoCostoUnitario = 0;

    /*$filtro = function($item) use ($fechaDesde, $inicialEntradas, $inicialSalidas, $inicialSaldoCostoTotal, $inicialSaldoCostoUnitario) {
        if ($item['Fecha'] >= $fechaDesde) {
            return true;
        } else {
            $inicialEntradas += (float)($item['IngresoCantidad']);
            $inicialSalidas += (float)($item['SalidaCantidad']);
            $inicialSaldoCostoTotal = $item['saldoCostoTotal'];
            $inicialSaldoCostoUnitario = $item['saldoCostoUnitario'];
            return false;
        }
    };*/
    $newKardex = [];
    $kardexSaldo = kardexSaldo($kardex);
    foreach ($kardexSaldo as $item) {
        if ($item['Fecha'] >= $fechaDesde) {
            array_push($newKardex, $item);
        } else {
            $inicialEntradas += (float)($item['IngresoCantidad']);
            $inicialSalidas += (float)($item['SalidaCantidad']);
            $inicialSaldoCostoTotal = $item['saldoCostoTotal'];
            $inicialSaldoCostoUnitario = $item['saldoCostoUnitario'];
        }
    }

    // $newKardex = array_filter(kardexSaldo($kardex), $filtro);

    array_unshift($newKardex, array(
        'Fecha' => $fechaDesde,
        'TipoDocSunat' => 1,
        'IngresoCantidad' => $inicialEntradas,
        'IngresoPrecio' => 0,
        'SalidaCantidad' => $inicialSalidas,
        'SalidaPrecio' => 0,
        'saldo' => $inicialEntradas - $inicialSalidas,
        'saldoCostoTotal' => round($inicialSaldoCostoTotal, 2),
        'saldoCostoUnitario' => round($inicialSaldoCostoUnitario, 2)
    ));

    return $newKardex;
}



// TEMPLATE XLXS
$app->get('/reporte/producto/kardex', function (Request $request, Response $response, array $args) use ($app) {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/productokardex.xlsx');
    $formato = $spreadsheet->getSheet(0);
    // $formato->getColumnDimension('B')->setAutoSize(true);
    $idProducto = $request->getParam('idProducto');
    $producto = $request->getParam('producto');
    $codigoBarra = $request->getParam('codigoBarra');
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaDesde = $request->getParam('fechaDesde');
    $fechaHasta = $request->getParam('fechaHasta');
    $productoMarca = $request->getParam('productoMarca');
    //$productoFF = $request->getParam('productoFF');
    $res = $app->subRequest('GET', '/productos/kardex/' . $idProducto, 'idAlmacen=' . $idAlmacen . '&fechaHasta=' . $fechaHasta . '&fechaDesde=' . $fechaDesde);
    $kardex = (string) $res->getBody();
    $kardex = json_decode($kardex, true);
    $kardex = kardexSaldo($kardex);
    $init = 14;

    foreach ($kardex as $kar) {
        $formato->getCell('A' . $init)->setValue($kar['Fecha']);
        $formato->duplicateStyle($formato->getStyle('A' . $init), 'A' . ($init + 1));

        $formato->getCell('B' . $init)->setValue(@$kar['Detalle'] . ' - ' . @$kar['Serie'] . ' - ' . @$kar['Numero']);
        $formato->duplicateStyle($formato->getStyle('B' . $init), 'B' . ($init + 1));

        $formato->getCell('C' . $init)->setValue(@$kar['Nombres']);
        $formato->duplicateStyle($formato->getStyle('C' . $init), 'C' . ($init + 1));

        $formato->getCell('D' . $init)->setValue(@$kar['IngresoCantidad']);
        $formato->duplicateStyle($formato->getStyle('D' . $init), 'D' . ($init + 1));

        $formato->getCell('E' . $init)->setValue($kar['IngresoPrecio']);
        $formato->duplicateStyle($formato->getStyle('E' . $init), 'E' . ($init + 1));

        $formato->getCell('F' . $init)->setValue($kar['SalidaCantidad']);
        $formato->duplicateStyle($formato->getStyle('F' . $init), 'F' . ($init + 1));

        $formato->getCell('G' . $init)->setValue($kar['SalidaPrecio']);
        $formato->duplicateStyle($formato->getStyle('G' . $init), 'G' . ($init + 1));

        $formato->getCell('H' . $init)->setValue($kar['saldo']);
        $formato->duplicateStyle($formato->getStyle('H' . $init), 'H' . ($init + 1));

        $init += 1;
    }
    $formato->getCell('A' . ($init + 2))->setValue('(1) Dirección del Establecimiento o Código según el Registro Único de Contribuyentes.');

    $formato->getCell('C' . $init)->setValue('TOTALES');
    $formato->getCell('D' . $init)->setValue('=SUM(D14:D' . ($init - 1) . ')');
    $formato->getCell('E' . $init)->setValue('=SUM(E14:E' . ($init - 1) . ')');
    $formato->getCell('F' . $init)->setValue('=SUM(F14:F' . ($init - 1) . ')');
    $formato->getCell('G' . $init)->setValue('=SUM(G14:G' . ($init - 1) . ')');
    $formato->getStyle('C' . $init)->getFont()->setBold(true);
    $formato->getStyle('D' . $init)->getFont()->setBold(true);
    $formato->getStyle('E' . $init)->getFont()->setBold(true);
    $formato->getStyle('F' . $init)->getFont()->setBold(true);
    $formato->getStyle('G' . $init)->getFont()->setBold(true);

    $formato->getCell('B8')->setValue($fechaDesde);
    $formato->getCell('B9')->setValue($fechaHasta);
    $formato->getCell('B3')->setValue($codigoBarra);
    $formato->getCell('B4')->setValue($producto);
    $formato->getCell('B5')->setValue($productoMarca);
    //$formato->getCell('B6')->setValue($productoFF);


    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('reporte/productokardex.xlsx');

    echo '<script type="text/javascript">
        location.href = "/api/reporte/productokardex.xlsx";
    </script>';

    // return $response->withRedirect('/api/reporte/formato12.1.xlsx');
});

$app->get('/reporte/kardexsimple', function (Request $request, Response $response, array $args) use ($app) {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/formato12.1.xlsx');
    $formato = $spreadsheet->getSheet(0);
    // $formato->getColumnDimension('B')->setAutoSize(true);
    $idProducto = $request->getParam('idProducto');
    $producto = $request->getParam('producto');
    $codigoBarra = $request->getParam('codigoBarra');
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaDesde = $request->getParam('fechaDesde');
    $fechaHasta = $request->getParam('fechaHasta');

    $res = $app->subRequest('GET', '/productos/kardex/' . $idProducto, 'idAlmacen=' . $idAlmacen . '&fechaHasta=' . $fechaHasta);
    $kardex = (string) $res->getBody();
    $kardex = json_decode($kardex, true);

    $kardex = kardexSaldoFecha($kardex, $fechaDesde);
    $init = 16;

    foreach ($kardex as $kar) {
        $formato->getCell('A' . $init)->setValue($kar['Fecha']);
        $formato->duplicateStyle($formato->getStyle('A' . $init), 'A' . ($init + 1));

        $formato->getCell('B' . $init)->setValue(@$kar['CodSunat']);
        $formato->duplicateStyle($formato->getStyle('B' . $init), 'B' . ($init + 1));

        $formato->getCell('C' . $init)->setValue(@$kar['Serie']);
        $formato->duplicateStyle($formato->getStyle('C' . $init), 'C' . ($init + 1));

        $formato->getCell('D' . $init)->setValue(@$kar['Numero']);
        $formato->duplicateStyle($formato->getStyle('D' . $init), 'D' . ($init + 1));

        $formato->getCell('E' . $init)->setValue($kar['TipoDocSunat']);
        $formato->duplicateStyle($formato->getStyle('E' . $init), 'E' . ($init + 1));

        $formato->getCell('F' . $init)->setValue($kar['IngresoCantidad']);
        $formato->duplicateStyle($formato->getStyle('F' . $init), 'F' . ($init + 1));

        $formato->getCell('G' . $init)->setValue($kar['SalidaCantidad']);
        $formato->duplicateStyle($formato->getStyle('G' . $init), 'G' . ($init + 1));

        $formato->getCell('H' . $init)->setValue($kar['saldo']);
        $formato->duplicateStyle($formato->getStyle('H' . $init), 'H' . ($init + 1));

        $init += 1;
    }
    $formato->getCell('A' . ($init + 2))->setValue('(1) Dirección del Establecimiento o Código según el Registro Único de Contribuyentes.');

    $formato->getCell('E' . $init)->setValue('TOTALES');
    $formato->getCell('F' . $init)->setValue('=SUM(F16:F' . ($init - 1) . ')');
    $formato->getCell('G' . $init)->setValue('=SUM(G16:G' . ($init - 1) . ')');
    $formato->getStyle('E' . $init)->getFont()->setBold(true);
    $formato->getStyle('F' . $init)->getFont()->setBold(true);
    $formato->getStyle('G' . $init)->getFont()->setBold(true);

    $formato->getCell('D4')->setValue('Del ' . $fechaDesde . ' al ' . $fechaHasta);
    $formato->getCell('D8')->setValue($codigoBarra);
    $formato->getCell('D10')->setValue($producto);


    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('reporte/formato12.1.xlsx');

    echo '<script type="text/javascript">
        location.href = "/api/reporte/formato12.1.xlsx";
    </script>';

    // return $response->withRedirect('/api/reporte/formato12.1.xlsx');
});

$app->get('/reporte/kardexvalorizado', function (Request $request, Response $response, array $args) use ($app) {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/formato13.1.xlsx');
    $formato = $spreadsheet->getSheet(0);
    // $formato->getColumnDimension('B')->setAutoSize(true);
    $idProducto = $request->getParam('idProducto');
    $producto = $request->getParam('producto');
    $codigoBarra = $request->getParam('codigoBarra');
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaDesde = $request->getParam('fechaDesde');
    $fechaHasta = $request->getParam('fechaHasta');

    $res = $app->subRequest('GET', '/productos/kardex/' . $idProducto, 'idAlmacen=' . $idAlmacen . '&fechaHasta=' . $fechaHasta);
    $kardex = (string) $res->getBody();
    $kardex = json_decode($kardex, true);

    $kardex = kardexSaldoFecha($kardex, $fechaDesde);
    $init = 17;

    $saldoCostoUnitario = 0;

    foreach ($kardex as $kar) {
        $formato->getCell('A' . $init)->setValue($kar['Fecha']);
        $formato->duplicateStyle($formato->getStyle('A' . $init), 'A' . ($init + 1));

        $formato->getCell('B' . $init)->setValue(@$kar['CodSunat']);
        $formato->duplicateStyle($formato->getStyle('B' . $init), 'B' . ($init + 1));

        $formato->getCell('C' . $init)->setValue(@$kar['Serie']);
        $formato->duplicateStyle($formato->getStyle('C' . $init), 'C' . ($init + 1));

        $formato->getCell('D' . $init)->setValue(@$kar['Numero']);
        $formato->duplicateStyle($formato->getStyle('D' . $init), 'D' . ($init + 1));

        $formato->getCell('E' . $init)->setValue($kar['TipoDocSunat']);
        $formato->duplicateStyle($formato->getStyle('E' . $init), 'E' . ($init + 1));

        $formato->getCell('F' . $init)->setValue($kar['IngresoCantidad']);
        $formato->duplicateStyle($formato->getStyle('F' . $init), 'F' . ($init + 1));

        $formato->getCell('G' . $init)->setValue(@$kar['IngresoPrecio']);
        $formato->duplicateStyle($formato->getStyle('G' . $init), 'G' . ($init + 1));

        $formato->getCell('H' . $init)->setValue($kar['IngresoCantidad'] * $kar['IngresoPrecio']);
        $formato->duplicateStyle($formato->getStyle('H' . $init), 'H' . ($init + 1));

        $formato->getCell('I' . $init)->setValue($kar['SalidaCantidad']);
        $formato->duplicateStyle($formato->getStyle('I' . $init), 'I' . ($init + 1));

        $formato->getCell('J' . $init)->setValue(@$kar['SalidaPrecio']);
        $formato->duplicateStyle($formato->getStyle('J' . $init), 'J' . ($init + 1));

        $formato->getCell('K' . $init)->setValue($kar['SalidaCantidad'] * $kar['SalidaPrecio']);
        $formato->duplicateStyle($formato->getStyle('K' . $init), 'K' . ($init + 1));

        $formato->getCell('L' . $init)->setValue($kar['saldo']);
        $formato->duplicateStyle($formato->getStyle('L' . $init), 'L' . ($init + 1));

        $formato->getCell('M' . $init)->setValue($kar['saldoCostoUnitario']);
        $formato->duplicateStyle($formato->getStyle('M' . $init), 'M' . ($init + 1));

        $formato->getCell('N' . $init)->setValue(@$kar['saldoCostoTotal']);
        $formato->duplicateStyle($formato->getStyle('N' . $init), 'N' . ($init + 1));

        $saldoCostoUnitario = $kar['saldoCostoUnitario'];

        $init += 1;
    }
    $formato->getCell('A' . ($init + 2))->setValue('(1) Dirección del Establecimiento o Código según el Registro Único de Contribuyentes.');

    $formato->getCell('E' . $init)->setValue('TOTALES');
    $formato->getCell('F' . $init)->setValue('=SUM(F17:F' . ($init - 1) . ')');
    $formato->getCell('H' . $init)->setValue('=SUM(H17:H' . ($init - 1) . ')');
    $formato->getCell('I' . $init)->setValue('=SUM(I17:I' . ($init - 1) . ')');
    $formato->getCell('K' . $init)->setValue('=SUM(K17:K' . ($init - 1) . ')');


    $formato->getStyle('E' . $init)->getFont()->setBold(true);
    $formato->getStyle('F' . $init)->getFont()->setBold(true);
    $formato->getStyle('H' . $init)->getFont()->setBold(true);
    $formato->getStyle('I' . $init)->getFont()->setBold(true);
    $formato->getStyle('K' . $init)->getFont()->setBold(true);

    $formato->getCell('D3')->setValue('Del ' . $fechaDesde . ' al ' . $fechaHasta);
    $formato->getCell('D7')->setValue($codigoBarra);
    $formato->getCell('D9')->setValue($producto);


    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('reporte/formato13.1.xlsx');

    echo '<script type="text/javascript">
        location.href = "/api/reporte/formato13.1.xlsx";
    </script>';

    //return $response->withRedirect('/api/reporte/formato13.1.xlsx');
});

$app->get('/reporte/formato37/exportar', function (Request $request, Response $response, array $args) use ($app) {
    /* $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/formato3.7.xlsx');
    $formato = $spreadsheet->getSheet(0);

    $anio = $request->getParam('anio');

    $select = "SELECT * FROM tmp_formato37";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();  

    $init = 12;

    foreach($data as $kar) {
        $formato->getCell('A'.$init)->setValue($kar['CodigoBarra']);
        $formato->duplicateStyle($formato->getStyle('A'.$init),'A'.($init+1));

        $formato->getCell('B'.$init)->setValue('01');
        $formato->duplicateStyle($formato->getStyle('B'.$init),'B'.($init+1));

        $formato->getCell('C'.$init)->setValue($kar['Producto']);
        $formato->duplicateStyle($formato->getStyle('C'.$init),'C'.($init+1));

        $formato->getCell('D'.$init)->setValue('07');
        $formato->duplicateStyle($formato->getStyle('D'.$init),'D'.($init+1));

        $formato->getCell('E'.$init)->setValue($kar['Saldo']);
        $formato->duplicateStyle($formato->getStyle('E'.$init),'E'.($init+1));

        $formato->getCell('F'.$init)->setValue($kar['SaldoCostoUnitario']);
        $formato->duplicateStyle($formato->getStyle('F'.$init),'F'.($init+1));

        $formato->getCell('G'.$init)->setValue($kar['SaldoCostoTotal']);
        $formato->duplicateStyle($formato->getStyle('G'.$init),'G'.($init+1));

        $init += 1;
    }

    $formato->getCell('F'.$init)->setValue('COSTO TOTAL GENERAL');    
    $formato->getCell('G'.$init)->setValue('=SUM(G12:G'.($init-1).')');

    $formato->getStyle('F'.$init)->getFont()->setBold( true );
    $formato->getStyle('G'.$init)->getFont()->setBold( true );
    
    $formato->getCell('E4')->setValue($anio);


    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('reporte/formato3.7.xlsx'); */

    echo '<script type="text/javascript">
        location.href = "/api/reporte/formato3.7.xlsx";
    </script>';
});

$app->post('/reporte/formato37', function (Request $request, Response $response, array $args) use ($app) {
    $row = $request->getParam('row');
    // var_dump($row);exit();
    $idProductos = $request->getParam('idProductos');
    $idAlmacen = $request->getParam('idAlmacen');
    $anio = $request->getParam('anio');


    $fechaDesde = $anio . '-01-01';
    $fechaHasta = $anio . '-12-31';
    // $fechaDesde = $request->getParam('fechaDesde');
    // $fechaHasta = $request->getParam('fechaHasta');


    if (!$row) {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/formato3.7.xlsx');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('reporte/formato3.7.xlsx');
    }

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('reporte/formato3.7.xlsx');
    $formato = $spreadsheet->getSheet(0);
    $init = 12;
    $init += $row;


    if ($idProductos) {
        $select = "SELECT * FROM Gen_Producto WHERE ControlaStock=1 AND IdProducto IN (" . implode(',', $idProductos) . ")";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $productosSaldo = [];

        $productosSaldo = array_map(function ($prod) use ($app, $idAlmacen, $fechaDesde, $fechaHasta) {
            $idProducto = $prod['IdProducto'];
            $res = $app->subRequest('GET', '/productos/kardex/' . $idProducto, 'idAlmacen=' . $idAlmacen . '&fechaHasta=' . $fechaHasta);
            $kardex = (string) $res->getBody();
            $kardex = json_decode($kardex, true);
            $kardex = kardexSaldoFecha($kardex, $fechaDesde);
            $final = array_merge($prod, end($kardex));
            return $final;
        }, $data);

        foreach ($productosSaldo as $kar) {
            /* $insert = "INSERT INTO tmp_formato37 (IdProducto, Producto, CodigoBarra, Saldo, SaldoCostoUnitario, SaldoCostoTotal)
                VALUES ('$kar[IdProducto]', '$kar[Producto]', '$kar[CodigoBarra]', '$kar[saldo]', '$kar[saldoCostoUnitario]', '$kar[saldoCostoTotal]')
                ON DUPLICATE KEY UPDATE Saldo='$kar[saldo]', SaldoCostoUnitario='$kar[saldoCostoUnitario]', SaldoCostoTotal='$kar[saldoCostoTotal]'";
            $stmt = $this->db->prepare($insert);
            $inserted = $stmt->execute();
            $idFormato = $this->db->lastInsertId();*/

            $formato->getCell('A' . $init)->setValue($kar['CodigoBarra']);
            $formato->duplicateStyle($formato->getStyle('A' . $init), 'A' . ($init + 1));

            $formato->getCell('B' . $init)->setValue('01');
            $formato->duplicateStyle($formato->getStyle('B' . $init), 'B' . ($init + 1));

            $formato->getCell('C' . $init)->setValue($kar['Producto']);
            $formato->duplicateStyle($formato->getStyle('C' . $init), 'C' . ($init + 1));

            $formato->getCell('D' . $init)->setValue('07');
            $formato->duplicateStyle($formato->getStyle('D' . $init), 'D' . ($init + 1));

            $formato->getCell('E' . $init)->setValue(@$kar['saldo']);
            $formato->duplicateStyle($formato->getStyle('E' . $init), 'E' . ($init + 1));

            $formato->getCell('F' . $init)->setValue(@$kar['saldoCostoUnitario']);
            $formato->duplicateStyle($formato->getStyle('F' . $init), 'F' . ($init + 1));

            $formato->getCell('G' . $init)->setValue(@$kar['saldoCostoTotal']);
            $formato->duplicateStyle($formato->getStyle('G' . $init), 'G' . ($init + 1));

            $init += 1;
        }

        $formato->getCell('F' . $init)->setValue('COSTO TOTAL GENERAL');
        $formato->getCell('G' . $init)->setValue('=SUM(G12:G' . ($init - 1) . ')');


        $config = array('SUMA_VALORIZADO' => $formato->getCell('G' . $init)->getCalculatedValue());
        $insert = "UPDATE GEN_EMPRESA SET CONFIG='" . serialize($config) . "' where IDEMPRESA=1";
        $stmt = $this->db->prepare($insert);
        $stmt->execute();


        // $formato->getStyle('F'.$init)->getFont()->setBold( true );
        // $formato->getStyle('G'.$init)->getFont()->setBold( true );

        $formato->getCell('E4')->setValue($anio);


        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('reporte/formato3.7.xlsx');
    }

    return;
    //return $response->withRedirect('/api/reporte/formato13.1.xlsx');
});

$app->get('/reporte/articulosmovimientos', function (Request $request, Response $response, array $args) use ($app) {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/masrotacion.xlsx');
    $formato = $spreadsheet->getSheet(0);
    // $formato->getColumnDimension('B')->setAutoSize(true);
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaDesde = $request->getParam('fechaDesde');
    $fechaHasta = $request->getParam('fechaHasta');

    $res = $app->subRequest('GET', '/productos/masrotacion', 'idAlmacen=' . $idAlmacen . '&fechaDesde=' . $fechaDesde . '&fechaHasta=' . $fechaHasta);
    $productos = (string) $res->getBody();
    $productos = json_decode($productos, true);

    $init = 9;

    foreach ($productos as $prod) {
        $formato->getCell('A' . $init)->setValue($prod['CodigoBarra']);
        $formato->duplicateStyle($formato->getStyle('A' . $init), 'A' . ($init + 1));

        $formato->getCell('B' . $init)->setValue($prod['Producto'] . ' - ' . $prod['ProductoCategoria'] . ' - ' . $prod['ProductoMarca']);
        $formato->duplicateStyle($formato->getStyle('B' . $init), 'B' . ($init + 1));

        $formato->getCell('C' . $init)->setValue($prod['ProductoMedicion']);
        $formato->duplicateStyle($formato->getStyle('C' . $init), 'C' . ($init + 1));

        $formato->getCell('D' . $init)->setValue($prod['TotalVentas']);
        $formato->duplicateStyle($formato->getStyle('D' . $init), 'D' . ($init + 1));

        $formato->getCell('E' . $init)->setValue($prod['TotalCompras']);
        $formato->duplicateStyle($formato->getStyle('E' . $init), 'E' . ($init + 1));
        $init += 1;
    }

    $formato->getCell('D3')->setValue('Del ' . $fechaDesde . ' al ' . $fechaHasta);

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('reporte/masrotacion.xlsx');

    echo '<script type="text/javascript">
        location.href = "/api/reporte/masrotacion.xlsx";
    </script>';

    // return $response->withRedirect('/api/reporte/formato12.1.xlsx');
});
//Consulta reporte

$app->get('/cajabanco/detalles', function (Request $request, Response $response, array $args) {
    $idCuenta = $request->getParam('IdCuenta');
    $idTipoOperacion = $request->getParam('IdTipoOperacion') ? $request->getParam('IdTipoOperacion') : 0;
    $fechaIni = $request->getParam('FechaIni');
    $fechaFin = $request->getParam('FechaFin');
    $idAlmacen = $request->getParam('IdAlmacen') ? $request->getParam('IdAlmacen') : 0;

    $Ssql = "SELECT Cb_CajaBanco.IdCajaBanco, Lo_Almacen.Almacen, Cb_CajaBanco.UsuarioReg AS Usuario, Cb_CajaBanco.FechaDoc,
    Lo_Proveedor.Proveedor, Ve_DocVentaCliente.Cliente, Cb_CajaBanco.Concepto, Cb_CajaBanco.Importe, Cb_TipoCajaBanco.TipoCajaBanco
    FROM Cb_CajaBanco
    LEFT JOIN Lo_Proveedor ON Cb_CajaBanco.IdProveedor = Lo_Proveedor.IdProveedor
    LEFT JOIN Ve_DocVentaCliente ON Cb_CajaBanco.IdCliente = Ve_DocVentaCliente.IdCliente
    INNER JOIN Seg_Usuario ON Cb_CajaBanco.UsuarioReg = Seg_Usuario.Usuario
    INNER JOIN Lo_Almacen ON Seg_Usuario.IdAlmacen = Lo_Almacen.IdAlmacen
    INNER JOIN Cb_TipoCajaBanco ON Cb_CajaBanco.IdTipoCajaBanco = Cb_TipoCajaBanco.IdTipoCajaBanco
    WHERE Cb_CajaBanco.IdCuenta=$idCuenta  AND Cb_CajaBanco.FechaDoc BETWEEN '$fechaIni' AND '$fechaFin' " .
        ($idTipoOperacion != 0 ? " AND Cb_CajaBanco.IdTipoCajaBanco= $idTipoOperacion " : "") .
        ($idAlmacen != 0 ? " AND Lo_Almacen.IdAlmacen =$idAlmacen " : "");

    $Ssql .= " ORDER BY Cb_CajaBanco.FechaDoc ASC";

    $stmt = $this->db->query($Ssql);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

//Reporte buscador caja y banco
$app->get('/reporte/buscador/cajabanco', function (Request $request, Response $response, array $args) use ($app) {
    $idCuenta = $request->getParam('IdCuenta');
    $idTipoOperacion = $request->getParam('IdTipoOperacion') ? $request->getParam('IdTipoOperacion') : 0;
    $fechaIni = $request->getParam('FechaIni');
    $fechaFin = $request->getParam('FechaFin');
    $idAlmacen = $request->getParam('IdAlmacen') ? $request->getParam('IdAlmacen') : 0;
    $almacen = $request->getParam('Almacen');
    $operaciontxt = $request->getParam('Operacion') ? $request->getParam('Operacion') : 'TODOS';
    $cuenta = $request->getParam('Cuenta');

    $res = $app->subRequest('GET', '/cajabanco/detalles', 'IdCuenta=' . $idCuenta . '&FechaIni=' . $fechaIni . '&FechaFin=' . $fechaFin . '&IdTipoOperacion=' . $idTipoOperacion . '&IdAlmacen=' . $idAlmacen);
    $datares = (string) $res->getBody();
    $data = json_decode($datares, true);

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/reportebuscadorcajabanco.xlsx');
    $formato = $spreadsheet->getSheet(0);
    $init = 10;

    foreach ($data as $operacion) {
        $formato->getCell('A' . $init)->setValue(@$operacion['IdCajaBanco']);
        $formato->duplicateStyle($formato->getStyle('A' . $init), 'A' . ($init + 1));

        $formato->getCell('B' . $init)->setValue(@$operacion['Almacen']);
        $formato->duplicateStyle($formato->getStyle('B' . $init), 'B' . ($init + 1));

        $formato->getCell('C' . $init)->setValue(@$operacion['Usuario']);
        $formato->duplicateStyle($formato->getStyle('C' . $init), 'C' . ($init + 1));

        $formato->getCell('D' . $init)->setValue(@$operacion['TipoCajaBanco']);
        $formato->duplicateStyle($formato->getStyle('D' . $init), 'D' . ($init + 1));

        $formato->getCell('E' . $init)->setValue(substr(@$operacion['FechaDoc'], 0, 10));
        $formato->duplicateStyle($formato->getStyle('E' . $init), 'E' . ($init + 1));

        $formato->getCell('F' . $init)->setValue(@$operacion['Proveedor']);
        $formato->duplicateStyle($formato->getStyle('F' . $init), 'F' . ($init + 1));

        $formato->getCell('G' . $init)->setValue(@$operacion['Cliente']);
        $formato->duplicateStyle($formato->getStyle('G' . $init), 'G' . ($init + 1));

        $formato->getCell('H' . $init)->setValue(@$operacion['Concepto']);
        $formato->duplicateStyle($formato->getStyle('H' . $init), 'H' . ($init + 1));

        $formato->getCell('I' . $init)->setValue(@$operacion['Importe']);
        $formato->duplicateStyle($formato->getStyle('I' . $init), 'I' . ($init + 1));

        $init += 1;
    }
    $formato->duplicateStyle($formato->getStyle('H' . $init), 'H' . ($init + 1));
    $formato->duplicateStyle($formato->getStyle('I' . $init), 'I' . ($init + 1));
    $formato->getCell('H' . ($init + 1))->setValue('TOTALES');
    $formato->getCell('I' . ($init + 1))->setValue('=SUM(I10:I' . ($init + 1) . ')');
    $formato->getStyle('H' . ($init + 1))->getFont()->setBold(true);
    $formato->getStyle('I' . ($init + 1))->getFont()->setBold(true);

    $formato->getCell('D3')->setValue('DEL ' . $fechaIni . ' AL ' . $fechaFin);
    $formato->getCell('D5')->setValue($almacen);
    $formato->getCell('D6')->setValue($operaciontxt);
    $formato->getCell('D7')->setValue($cuenta);

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('reporte/reportebuscadorcajabanco.xlsx');

    echo '<script type="text/javascript">
        location.href = "/api/reporte/reportebuscadorcajabanco.xlsx";
    </script>';

    // return $response->withRedirect('/api/reporte/formato12.1.xlsx');

});


$app->post('/cierrecaja', function (Request $request, Response $response) {
    $idTurno        = $request->getParam('idTurno');
    $user           = $request->getParam('user');
    $fechaCierre    = getNow();
    $ingresos       = $request->getParam('ingresos') ? $request->getParam('ingresos') : 0;
    $salidas        = $request->getParam('salidas') ? $request->getParam('salidas') : 0;
    $total          = $request->getParam('total') ? $request->getParam('total') : 0;
    $totalVentas    = $request->getParam('totalVentas') ? $request->getParam('totalVentas') : 0;
    $granTotal    = $request->getParam('granTotal') ? $request->getParam('granTotal') : 0;

    $usuarioReg = 'xx';
    if (isset($_SESSION['user'])) {
        $usuarioReg = $_SESSION['user'];
    }
    $usuarioReg = $user ? $user : $usuarioReg;

    if ($usuarioReg == 'xx') {
        return $response->withJson(['error' => 'Sesión Caducada'], 500);
    }


    $select = "SELECT IdAlmacen,IdUsuarioPerfil  FROM Seg_Usuario WHERE LOWER(Usuario)=LOWER('$usuarioReg')";
    $stmt = $this->db->query($select);
    $almacen = $stmt->fetch(PDO::FETCH_ASSOC);


    $sql = "SELECT GROUP_CONCAT(QUOTE(LOWER(u2.Usuario))) AS UsuariosEnAlmacen
                FROM Seg_Usuario u1
                INNER JOIN Seg_Usuario u2 ON u1.IdAlmacen = u2.IdAlmacen
                WHERE u1.Usuario = '$usuarioReg'";
    $stmt = $this->db->query($sql);
    $UsuariosEnAlmacen = $stmt->fetch(PDO::FETCH_ASSOC);


    $insert = "INSERT INTO Cb_CierreCaja (FechaCierre, IdTurno, UsuarioReg, Ingresos, Salidas, Ventas, Total)
        VALUES ('$fechaCierre', '$idTurno', '$usuarioReg', $ingresos, $salidas, $totalVentas, $total)";
    $stmt = $this->db->prepare($insert);
    $inserted = $stmt->execute();
    $idCierreCaja = $this->db->lastInsertId();


    // Actualizar DocVenta
    $updateVenta = "UPDATE Ve_DocVenta SET IdCierre=$idCierreCaja WHERE IdCierre IS NULL
        AND IdAlmacen='" . $almacen['IdAlmacen'] . "'";
    $stmt = $this->db->prepare($updateVenta);
    $updatedVenta = $stmt->execute();


    // Actualizar CajaBanco asignando cierre de todos los que pertenecen al almacen
    $updateCajaBanco = "UPDATE Cb_CajaBanco SET IdCierre=$idCierreCaja WHERE IdCierre IS NULL
        AND EsDelVendedor=1 AND LOWER(UsuarioReg) IN (" . $UsuariosEnAlmacen['UsuariosEnAlmacen'] . ")";
    $stmt = $this->db->prepare($updateCajaBanco);
    $updatedCajaBanco = $stmt->execute();


    $insert = "INSERT INTO 
        Cb_CajaBanco (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, Anulado,  EsDelVendedor,  UsuarioReg) 
        VALUES (4, 1, '" . getNow() . "', 'Caja Inicial', $granTotal, 0,  1, '$usuarioReg')";

    $stmt = $this->db->prepare($insert);
    $inserted = $stmt->execute();

    return $response->withJson(array(
        "idTurno" => $idTurno,
        "fechaCierre" => $fechaCierre,
        "idCierraCaja" => $idCierreCaja,
        "IdAlmacen" => $almacen['IdAlmacen']
    ));
});


$app->get('/cierrecaja', function (Request $request, Response $response, array $args) {
    $idCierre = $request->getParam('idCierre');
    $filter = $request->getParam('filter');

    $select = "SELECT * FROM Cb_CierreCaja WHERE (FechaCierre LIKE '%$filter%' OR UsuarioReg LIKE '%$filter%')";

    if ($idCierre) {
        $select .= " AND IdCierreCaja=$idCierre";
    }


    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }


    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/cierrecaja/reporte', function (Request $request, Response $response, array $args) {
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaDesde = $request->getParam('fechaDesde');
    $fechaHasta = $request->getParam('fechaHasta');
    $select = "SELECT Cb_CierreCaja.*, Seg_Usuario.IdAlmacen, Lo_Almacen.Almacen FROM Cb_CierreCaja 
        INNER JOIN Seg_Usuario ON Cb_CierreCaja.UsuarioReg = Seg_Usuario.Usuario 
        INNER JOIN Lo_Almacen ON Seg_Usuario.IdAlmacen = Lo_Almacen.IdAlmacen";
    if ($idAlmacen) {
        $select .= " WHERE Cb_CierreCaja.UsuarioReg IN ( SELECT Usuario FROM Seg_Usuario WHERE IdAlmacen = '$idAlmacen' ) ";
    } else {
        $select .= " WHERE Cb_CierreCaja.UsuarioReg IN ( SELECT Usuario FROM Seg_Usuario ) ";
    }
    $select .= " AND FechaCierre >= '$fechaDesde' AND FechaCierre <= '$fechaHasta' ORDER BY IdCierreCaja DESC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/cierrecaja/count', function (Request $request, Response $response, array $args) {

    $select = "SELECT COUNT(*) AS total FROM Cb_CierreCaja";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson($data);
});

$app->get('/cierrecaja/ventas', function (Request $request, Response $response, array $args) {
    try {


        $idCierre = $request->getParam('idCierre');
        $usuario = $request->getParam('usuario');

        if ($request->getParam('usuario')) {
            $select = "SELECT IdAlmacen,IdUsuarioPerfil  FROM Seg_Usuario WHERE Usuario='$usuario'";
            $stmt = $this->db->query($select);
            $almacen = $stmt->fetch(PDO::FETCH_ASSOC);
        }


        $select = "SELECT
        Ve_DocVenta.idDocVenta, Ve_DocVentaPuntoVenta.PuntoVenta, Ve_DocVentaCliente.Cliente, Ve_DocVenta.FechaDoc, Ve_DocVenta.Anulado,
        Ve_DocVentaTipoDoc.TipoDoc, Ve_DocVenta.Serie, Ve_DocVenta.Numero, SUM((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - Ve_DocVentaDet.Descuento) as Total,
        Ve_DocVenta.EsCredito,Ve_DocVentaTipoDoc.CodSunat,
        (SELECT Ve_DocVentaMetodoPagoDet.NroTarjeta FROM Ve_DocVentaMetodoPagoDet WHERE Ve_DocVentaMetodoPagoDet.IdDocVenta = Ve_DocVenta.idDocVenta AND Ve_DocVentaMetodoPagoDet.IdMetodoPago = 1) AS EfectivoDesc,
        (SELECT Ve_DocVentaMetodoPagoDet.NroTarjeta FROM Ve_DocVentaMetodoPagoDet WHERE Ve_DocVentaMetodoPagoDet.IdDocVenta = Ve_DocVenta.idDocVenta AND Ve_DocVentaMetodoPagoDet.IdMetodoPago = 2) AS VisaDesc,
        (SELECT Ve_DocVentaMetodoPagoDet.NroTarjeta FROM Ve_DocVentaMetodoPagoDet WHERE Ve_DocVentaMetodoPagoDet.IdDocVenta = Ve_DocVenta.idDocVenta AND Ve_DocVentaMetodoPagoDet.IdMetodoPago = 3) AS MastercardDesc,
        (SELECT Ve_DocVentaMetodoPagoDet.NroTarjeta FROM Ve_DocVentaMetodoPagoDet WHERE Ve_DocVentaMetodoPagoDet.IdDocVenta = Ve_DocVenta.idDocVenta AND Ve_DocVentaMetodoPagoDet.IdMetodoPago = 4) AS YapeDesc,

        IFNULL((SELECT SUM(Ve_DocVentaMetodoPagoDet.Importe) FROM Ve_DocVentaMetodoPagoDet WHERE Ve_DocVentaMetodoPagoDet.IdDocVenta = Ve_DocVenta.idDocVenta AND Ve_DocVentaMetodoPagoDet.IdMetodoPago = 1), 0) AS Efectivo,
        IFNULL((SELECT SUM(Ve_DocVentaMetodoPagoDet.Importe) FROM Ve_DocVentaMetodoPagoDet WHERE Ve_DocVentaMetodoPagoDet.IdDocVenta = Ve_DocVenta.idDocVenta AND Ve_DocVentaMetodoPagoDet.IdMetodoPago = 2), 0) AS Visa,
        IFNULL((SELECT SUM(Ve_DocVentaMetodoPagoDet.Importe) FROM Ve_DocVentaMetodoPagoDet WHERE Ve_DocVentaMetodoPagoDet.IdDocVenta = Ve_DocVenta.idDocVenta AND Ve_DocVentaMetodoPagoDet.IdMetodoPago = 3), 0) AS Mastercard,
        IFNULL((SELECT SUM(Ve_DocVentaMetodoPagoDet.Importe) FROM Ve_DocVentaMetodoPagoDet WHERE Ve_DocVentaMetodoPagoDet.IdDocVenta = Ve_DocVenta.idDocVenta AND Ve_DocVentaMetodoPagoDet.IdMetodoPago = 4), 0) AS Yape,
        Ve_DocVenta.UsuarioReg UsuarioVenta,
        COALESCE(Ve_Proforma.UsuarioReg,Ve_DocVenta.UsuarioReg) UsuarioProforma
        
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaDet ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVentaTipoDoc.IdTipoDoc = Ve_DocVenta.IdTipoDoc
        LEFT JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente
        INNER JOIN Ve_DocVentaPuntoVenta ON Ve_DocVenta.IdDocVentaPuntoVenta = Ve_DocVentaPuntoVenta.IdDocVentaPuntoVenta
        LEFT JOIN Ve_Proforma ON Ve_DocVenta.IdProforma = Ve_Proforma.IdProforma
        ";

        if ($idCierre) {
            $select .= " WHERE Ve_DocVenta.IdCierre = $idCierre";
        } else {
            $select .= " WHERE Ve_DocVenta.IdCierre IS NULL";
        }

        if ($request->getParam('usuario')) {
            $select .= " AND Ve_DocVenta.IdAlmacen='" . $almacen['IdAlmacen'] . "'";
        }

        $select .= " GROUP BY Ve_DocVenta.idDocVenta
        ORDER BY Ve_DocVentaPuntoVenta.IdDocVentaPuntoVenta ASC, Ve_DocVenta.FechaDoc ASC;";
        // print_r($select); exit();
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
        foreach ($data as $key => $ventas) {
            $sql = "SELECT 
                    p.IdProducto,
                    p.Producto,
                    vt.Cantidad Cantidad,
                    vt.Precio,
                    vt.Descuento
                    FROM Ve_DocVentaDet vt
                    JOIN Gen_Producto p ON p.IdProducto = vt.IdProducto
                WHERE vt.IdDocVenta = " . $ventas['idDocVenta'];
            $stmt = $this->db->query($sql);
            $stmt->execute();
            $detalle = $stmt->fetchAll();
            $data[$key]['detalle'] = $detalle;
        }
        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
        //throw $th;
    }
});
$app->get('/cierrecaja/ingresos', function (Request $request, Response $response, array $args) {
    try {
        $idCierre = $request->getParam('idCierre');
        $usuario = $request->getParam('usuario');
        if ($usuario) {
            $sql = "SELECT GROUP_CONCAT(QUOTE(LOWER(u2.Usuario))) AS UsuariosEnAlmacen
                FROM Seg_Usuario u1
                INNER JOIN Seg_Usuario u2 ON u1.IdAlmacen = u2.IdAlmacen
                WHERE u1.Usuario = '$usuario'";
            $stmt = $this->db->query($sql);
            $almacen = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        $select = "SELECT Cb_CajaBanco.IdCajaBanco, Cb_TipoCajaBanco.Tipo, Cb_CajaBanco.IdCuenta, Cb_Cuenta.Cuenta, Cb_CajaBanco.FechaDoc, Cb_CajaBanco.Concepto, Cb_CajaBanco.Importe, COALESCE(Cb_CajaBanco.Anulado+0,0) AS Anulado,
        COALESCE(Cb_Cuenta.EsTarjeta+0,0) AS EsTarjeta,Cb_CajaBanco.UsuarioReg
        FROM Cb_CajaBanco
        INNER JOIN Cb_TipoCajaBanco ON Cb_CajaBanco.IdTipoCajaBanco = Cb_TipoCajaBanco.IdTipoCajaBanco
        INNER JOIN Cb_Cuenta ON Cb_CajaBanco.IdCuenta = Cb_Cuenta.IdCuenta";
        if ($idCierre) {
            $select .= " WHERE Cb_CajaBanco.IdCierre=$idCierre";
        } else {
            $select .= " WHERE Cb_CajaBanco.IdCierre IS NULL";
        }

        if ($request->getParam('usuario')) {
            $select .= " AND EsDelVendedor=1 AND LOWER(Cb_CajaBanco.UsuarioReg) IN (" . $almacen['UsuariosEnAlmacen'] . ")";
        }

        $select .= " AND Cb_TipoCajaBanco.Tipo = 0
        ORDER BY FechaDoc ASC;";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], 500);
    }
});

$app->get('/cierrecaja/ingresos/adelantos', function (Request $request, Response $response, array $args) {
    $idDocVentas = $request->getParam('idDocVentas');

    if ($idDocVentas) {
        $select = "SELECT Cb_CajaBanco.IdCajaBanco, Cb_TipoCajaBanco.Tipo, Cb_CajaBanco.IdCuenta, Cb_Cuenta.Cuenta, Cb_CajaBanco.FechaDoc, 
        Cb_CajaBanco.Concepto  , Cb_CajaBanco.Importe, COALESCE(Cb_Cuenta.EsTarjeta+0,0) AS EsTarjeta, COALESCE(Cb_CajaBanco.Anulado+0,0) AS Anulado, Ve_DocVenta.idDocVenta, Ve_DocVenta.Serie, Ve_DocVenta.Numero,
        Ve_DocVenta.Anulado AS AnuladoVenta, Ve_DocVenta.EsCredito AS EsCreditoVenta
          FROM Cb_CajaBanco
          INNER JOIN Cb_TipoCajaBanco ON Cb_CajaBanco.IdTipoCajaBanco = Cb_TipoCajaBanco.IdTipoCajaBanco
          INNER JOIN Cb_Cuenta ON Cb_CajaBanco.IdCuenta = Cb_Cuenta.IdCuenta
          LEFT JOIN Ve_PreOrden ON Cb_CajaBanco.IdPreOrden = Ve_PreOrden.IdPreOrden
          LEFT JOIN Ve_DocVenta ON Ve_PreOrden.IdDocVenta = Ve_DocVenta.idDocVenta";


        $select .= " WHERE Ve_DocVenta.idDocVenta IN ( " . implode(',', $idDocVentas) . " )";

        $select .= " AND Cb_TipoCajaBanco.Tipo = 0
          ORDER BY FechaDoc ASC;";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
    } else {
        $data = [];
    }

    return $response->withJson($data);
});

$app->get('/cierrecaja/salidas', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $idCierre = $request->getParam('idCierre');
        $usuario = $request->getParam('usuario');
        if ($usuario) {
            $sql = "SELECT GROUP_CONCAT(QUOTE(LOWER(u2.Usuario))) AS UsuariosEnAlmacen
                FROM Seg_Usuario u1
                INNER JOIN Seg_Usuario u2 ON u1.IdAlmacen = u2.IdAlmacen
                WHERE u1.Usuario = '$usuario'";
            $stmt = $this->db->query($sql);
            $almacen = $stmt->fetch(PDO::FETCH_ASSOC);
        }


        $select = "SELECT Cb_CajaBanco.IdCajaBanco, Cb_TipoCajaBanco.Tipo, Cb_CajaBanco.IdCuenta, Cb_Cuenta.Cuenta, Cb_CajaBanco.FechaDoc, Cb_CajaBanco.Concepto, Cb_CajaBanco.Importe, COALESCE(Cb_Cuenta.EsTarjeta+0,0) AS EsTarjeta, COALESCE(Cb_CajaBanco.Anulado+0,0) AS Anulado,Cb_CajaBanco.UsuarioReg
        FROM Cb_CajaBanco
        INNER JOIN Cb_TipoCajaBanco ON Cb_CajaBanco.IdTipoCajaBanco = Cb_TipoCajaBanco.IdTipoCajaBanco
        INNER JOIN Cb_Cuenta ON Cb_CajaBanco.IdCuenta = Cb_Cuenta.IdCuenta";

        if ($idCierre) {
            $select .= " WHERE Cb_CajaBanco.IdCierre=$idCierre";
        } else {
            $select .= " WHERE Cb_CajaBanco.IdCierre IS NULL";
        }

        if ($request->getParam('usuario')) {
            $select .= " AND EsDelVendedor=1 AND LOWER(Cb_CajaBanco.UsuarioReg) IN (" . $almacen['UsuariosEnAlmacen'] . ")";
        }

        $select .= " AND Cb_TipoCajaBanco.Tipo = 1
        ORDER BY FechaDoc ASC;";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], 500);
    }
});


$app->get('/enviar/whatsapp/comprobante/{id}', function (Request $request, Response $response, array $args) use ($app) {
    $select = "SELECT RUC, RAZONSOCIAL, DEPARTAMENTO, PROVINCIA, DISTRITO, DIRECCION, UBIGEO FROM GEN_EMPRESA WHERE IDEMPRESA=1";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $empresa = $stmt->fetch();
    $id = $args['id'];
    $numero = $request->getParam('numero');
    $select = "SELECT Ve_DocVenta.idDocVenta, Ve_DocVenta.FechaDoc, Ve_DocVentaTipoDoc.TipoDoc, Ve_DocVentaTipoDoc.TieneIgv,
        Ve_DocVenta.Anulado, Ve_DocVenta.Serie, Ve_DocVenta.Numero, Ve_DocVentaCliente.Cliente, Ve_DocVenta.UsuarioReg,
        Ve_DocVentaTipoDoc.CodSunat, Ve_DocVentaCliente.DniRuc, Ve_DocVenta.CampoDireccion as Direccion,
        Ve_DocVentaTipoDoc.CodigoIgv, Ve_DocVenta.Estado, Ve_DocVenta.Hash_cpe, Ve_DocVenta.Hash_cdr,
        Ve_DocVenta.FechaCredito, Lo_Almacen.Almacen, Lo_Almacen.Direccion DireccionAlmacen, Lo_Almacen.EsPrincipal EsAlmacenPrincipal,
        Ve_DocVentaCliente.NombreComercial,Ve_DocVenta.CodSunatModifica,
        Ve_DocVenta.NroComprobanteModifica,
        Ve_DocVenta.NotaIdMotivo,
        Ve_DocVenta.NotaDescMotivo,Ve_DocVenta.EsCredito,Ve_DocVenta.FechaCredito,
        IFNULL((SELECT SUM(ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2)) FROM Ve_DocVentaDet WHERE Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta), 0 ) AS Total
        FROM Ve_DocVenta
        INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
        LEFT JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente 
        INNER JOIN Lo_Almacen ON Ve_DocVenta.IdAlmacen = Lo_Almacen.IdAlmacen
        WHERE idDocVenta = $id ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $docVenta = $stmt->fetch();


    $select = "SELECT Ve_DocVentaDet.*, Gen_Producto.Producto,
        ROUND(Ve_DocVentaDet.Descuento, 2) AS Descuento,
        ROUND((Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) - Ve_DocVentaDet.Descuento, 2) AS Subtotal,
        ROUND(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio, 2) AS Total,
        Gen_Producto.CodigoBarra, Gen_ProductoMedicion.Codigo AS CodigoMedicion,
        Gen_ProductoMedicion.ProductoMedicion, Gen_ProductoMarca.ProductoMarca
        FROM Ve_DocVentaDet
        INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
        INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
        INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
        WHERE IdDocVenta = $id";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $productos = $stmt->fetchAll();


    $url = "http://emision.factura.vip/api/whatsapp.php/generatepdf";
    try {
        $headers = array(
            "Content-Type: application/json; charset=UTF-8",
            "Cache-Control: no-cache",
            "Pragma: no-cache"
        );
        $ch = curl_init();
        $ch = curl_init($url);
        $fields = array('venta' => $docVenta, 'productos' => $productos, 'empresa' => $empresa, 'numero' => $numero);
        $dataTicket = json_encode($fields);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataTicket);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // converting
        $responsecurl = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responsecurl == "") {
            return $response->withJson(array("success" => false));;
        }
    } catch (Exception $e) {
        return $response->withJson(array("success" => false));;
    }

    return $response->write($responsecurl);
});

/* REPORTE DE MERCADERIAS Y TERMINADOS */
$app->get('/reporte/mercaderias/servicios', function (Request $request, Response $response, array $args) use ($app) {
    $fechaInicio = $request->getParam('fechaInicio');
    $fechaFin = $request->getParam('fechaFin');

    $select = "SELECT DATE_FORMAT(Ve_DocVenta.FechaReg,'%d-%m-%Y') AS FREG, DATE_FORMAT(Ve_DocVenta.FechaDoc,'%d-%m-%Y') AS FDOC,
    DATE_FORMAT(Ve_DocVenta.FechaCredito,'%d-%m-%Y') AS FVEN, Ve_DocVentaTipoDoc.CodSunat AS TD, Ve_DocVenta.Serie AS SERIE, Ve_DocVenta.Numero AS NUMERO,
    IF(Ve_DocVenta.Anulado=1,1,Ve_DocVentaCliente.DniRuc) AS RUC, IF(Ve_DocVenta.Anulado=1,'',IF(LENGTH(Ve_DocVentaCliente.DniRuc)=11,6,1)) AS TIPO, IF(Ve_DocVenta.Anulado=1,'DOCUMENTO ANULADO',Ve_DocVentaCliente.Cliente) AS RS,
    'S' AS MONEDA, IF(Ve_DocVenta.Anulado=1, 0, IF(Ve_DocVentaTipoDoc.TieneIgv=1,ROUND(SUM(ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2)) /1.18,2),0)) AS NETO,
    IF(Ve_DocVenta.Anulado=1, 0, IF(Ve_DocVentaTipoDoc.TieneIgv=1,0,SUM(ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2)))) AS NETOG, 0 AS NETOEXP,
    IF(Ve_DocVenta.Anulado=1, 0, IF(Ve_DocVentaTipoDoc.TieneIgv=1, ROUND(SUM(ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2))  -  ROUND(SUM(ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2)) /1.18,2) ,2) ,0)) AS IGV, 0 AS OTROSTRIBUTOS, 
    IF(Ve_DocVenta.Anulado=1, 0, SUM(ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2))) AS TOTAL, 0 AS TC, 20 AS CC,  IF(Gen_Producto.OpcContable=1,IF(Ve_DocVentaTipoDoc.TieneIgv=1,'701211','701212'),IF(Ve_DocVentaTipoDoc.TieneIgv=1,'702211','702212')) AS  CTA7,
    CONCAT(IF(Gen_Producto.OpcContable=1,'MERCADERIA','SERVICIO'),' ',IF(Ve_DocVentaTipoDoc.TieneIgv=1,'GRAV','EXON')) AS GLOSA, '' AS NUMREFEREN, '' AS TDREFERENC, '' AS FECHAREF FROM Ve_DocVenta
    INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
    INNER JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente
    INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta = Ve_DocVentaDet.IdDocVenta
    INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
    WHERE Ve_DocVentaTipoDoc.CodSunat IN ('03','01','12') AND (Ve_DocVenta.FechaDoc BETWEEN '" . $fechaInicio . " 00:00:00' AND '" . $fechaFin . " 23:59:59')
    GROUP BY Ve_DocVenta.idDocVenta, Gen_Producto.OpcContable
    ORDER BY Ve_DocVenta.FechaDoc ASC ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $mercaderia = $stmt->fetchAll();

    //return $response->withJson($mercaderia);

    $excel = new Spreadsheet();
    $sheet = $excel->getActiveSheet();
    $sheet->setCellValue('A1', 'NUMVOU');
    $sheet->setCellValue('B1', 'FREG');
    $sheet->setCellValue('C1', 'FDOC');
    $sheet->setCellValue('D1', 'FVEN');
    $sheet->setCellValue('E1', 'TD');
    $sheet->setCellValue('F1', 'SERIE');
    $sheet->setCellValue('G1', 'NUMERO');
    $sheet->setCellValue('H1', 'RUC');
    $sheet->setCellValue('I1', 'TIPO');
    $sheet->setCellValue('J1', 'RS');
    $sheet->setCellValue('K1', 'MONEDA');
    $sheet->setCellValue('L1', 'NETO');
    $sheet->setCellValue('M1', 'NETONG');
    $sheet->setCellValue('N1', 'NETOEXP');
    $sheet->setCellValue('O1', 'IGV');
    $sheet->setCellValue('P1', 'OTROSTRIBUTOS');
    $sheet->setCellValue('Q1', 'TOTAL');
    $sheet->setCellValue('R1', 'TC');
    $sheet->setCellValue('S1', 'CC');
    $sheet->setCellValue('T1', 'CTA7');
    $sheet->setCellValue('U1', 'GLOSA');
    $sheet->setCellValue('V1', 'NUMREFEREN');
    $sheet->setCellValue('W1', 'TDREFERENC');
    $sheet->setCellValue('X1', 'FECHAREF');

    $sheet->getStyle('H')->getNumberFormat()->setFormatCode('@');
    $sheet->getStyle('L:Q')->getNumberFormat()->setFormatCode('0.00');

    $cont = 2;
    foreach ($mercaderia as $key => $value) {

        $sheet->setCellValue('A' . $cont, ($key + 1));
        $sheet->setCellValue('B' . $cont, date("d/m/Y", strtotime($value['FREG'])));
        $sheet->setCellValue('C' . $cont, date("d/m/Y", strtotime($value['FDOC'])));
        $sheet->setCellValue('D' . $cont, date("d/m/Y", strtotime($value['FVEN'])));
        $sheet->setCellValue('E' . $cont, $value['TD']);
        $sheet->setCellValue('F' . $cont, $value['SERIE']);
        $sheet->setCellValue('G' . $cont, str_pad($value['NUMERO'], 6, "0", STR_PAD_LEFT));
        $sheet->setCellValue('H' . $cont, $value['RUC']);
        $sheet->setCellValue('I' . $cont, $value['TIPO']);
        $sheet->setCellValue('J' . $cont, $value['RS']);
        $sheet->setCellValue('K' . $cont, $value['MONEDA']);
        $sheet->setCellValue('L' . $cont, $value['NETO']);
        $sheet->setCellValue('M' . $cont, $value['NETOG']);
        $sheet->setCellValue('N' . $cont, $value['NETOEXP']);
        $sheet->setCellValue('O' . $cont, $value['IGV']);
        $sheet->setCellValue('P' . $cont, $value['OTROSTRIBUTOS']);
        $sheet->setCellValue('Q' . $cont, ($value['TD'] == '07' ? -1 * $value['TOTAL'] : $value['TOTAL']));
        $sheet->setCellValue('R' . $cont, $value['TC']);
        $sheet->setCellValue('S' . $cont, $value['CC']);
        $sheet->setCellValue('T' . $cont, $value['CTA7']);
        $sheet->setCellValue('U' . $cont, $value['GLOSA']);
        $sheet->setCellValue('V' . $cont, $value['NUMREFEREN']);
        $sheet->setCellValue('W' . $cont, $value['TDREFERENC']);
        $sheet->setCellValue('X' . $cont, $value['FECHAREF']);

        $cont += 1;
    }
    $excel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
    $excel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);

    $excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    $excel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
    $excel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);

    $excel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
    $excel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
    $excel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
    $excel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
    $excel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);

    $excel->getActiveSheet()->getColumnDimension('U')->setAutoSize(true);

    /*$excel->getActiveSheet()->mergeCells('A'.$cont.':M'.$cont);
    $excel->getActiveSheet()->getStyle('A'.$cont.':M'.$cont)->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('A'.$cont)->getFont()->setBold( true );
    $excel->getActiveSheet()->getStyle('N'.$cont)->getFont()->setBold( true );
    $sheet->setCellValue('A'.$cont,'TOTAL');
    $sheet->setCellValue('N'.$cont,'=SUM(N3:N'.($cont-1).')');*/

    /*$excel->getActiveSheet()->getStyle('A1:T1')->getFill()
    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()->setARGB('FFE6E6FA');
    foreach(array('A1' ,'B1' ,'C1' ,'D1' ,'E1','F1','G1','H1','I1','J1','K1','L1','M1','N1','O1','P1','Q1','R1','S1','T1') as $columnID) {
        $excel->getActiveSheet()->getStyle($columnID)->getBorders()->getOutline()
        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
        ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('#000000'));
    }
    $excel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
    $excel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
    $excel->getActiveSheet()->getStyle('H1:H'.($cont-1))->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('A1:T1')->getFont()->setBold( true );
    $excel->getActiveSheet()->getStyle('S3:S'.($cont-1))->getFont()->setBold( true );
    $excel->getActiveSheet()->getStyle('S1:S'.($cont-1))->getAlignment()->setHorizontal('center');*/

    $excelWriter = new Xlsx($excel);

    $fileName = 'REPORTE MERCADERIA Y SERVICIOS DEL ' . substr($fechaInicio, 0, 10) . ' AL ' . substr($fechaFin, 0, 10) . '.xlsx';
    $excelFileName = __DIR__ . '/reporte/' . $fileName;
    $excelWriter->save($excelFileName);
    echo "<script>window.location.href = '/api/reporte/" . $fileName . "'</script>";
    exit;
});

$app->get('/proyecto/detalle', function (Request $request, Response $response) {
    $idPreOrden = $request->getParam('IdPreOrden');

    $select = "SELECT Ve_PreOrdenDet.Cantidad,Gen_Producto.IdProducto,Gen_Producto.CodigoBarra,
    Gen_ProductoCategoria.ProductoCategoria,Gen_ProductoMarca.ProductoMarca,Gen_Producto.Producto,Ve_PreOrdenDet.IdPreOrden,    
    IFNULL(Ve_PreOrdenDet.Precio, Gen_Producto.PrecioContado) AS Precio, Ve_PreOrdenDet.EsManoDeObra, Gen_Producto.EsHabitacion,
    COALESCE(SUM(Ve_PreOrdenDetPago.Monto),0) as MontoPagado,
    Ve_PreOrdenDet.Cantidad* IFNULL(Ve_PreOrdenDet.Precio, Gen_Producto.PrecioContado) - COALESCE(SUM(Ve_PreOrdenDetPago.Monto),0) as Deuda,
    Ve_PreOrdenDet.Cantidad* IFNULL(Ve_PreOrdenDet.Precio, Gen_Producto.PrecioContado) - COALESCE(SUM(Ve_PreOrdenDetPago.Monto),0) <= 0 as Pagado
    
    FROM Ve_PreOrdenDet
    INNER JOIN Gen_Producto ON Ve_PreOrdenDet.IdProducto = Gen_Producto.IdProducto
    INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
    INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
    LEFT JOIN Ve_PreOrdenDetPago ON Ve_PreOrdenDetPago.IdProducto = Ve_PreOrdenDet.IdProducto AND Ve_PreOrdenDetPago.IdPreOrden = Ve_PreOrdenDet.IdPreOrden   
    WHERE Ve_PreOrdenDet.IdPreOrden=$idPreOrden 
    GROUP BY Gen_Producto.IdProducto, Ve_PreOrdenDet.IdPreOrden
    ORDER BY Gen_Producto.EsHabitacion DESC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/proyecto/cajaybanco/todo', function (Request $request, Response $response, array $args) {
    $idPreOrden     = $request->getParam('IdPreOrden');

    if ($idPreOrden) {
        $select = "SELECT Cb_CajaBanco.*, Cb_TipoCajaBanco.Tipo FROM Cb_CajaBanco 
            INNER JOIN Cb_TipoCajaBanco ON Cb_CajaBanco.IdTipoCajaBanco=Cb_TipoCajaBanco.IdTipoCajaBanco
            WHERE IdPreOrden=$idPreOrden";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } else {
        return $response->withJson(false);
    }
});

$app->get('/proyectos/count', function (Request $request, Response $response) {
    $select = "SELECT COUNT(*) AS total FROM Ve_PreOrden";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();
    return $response->withJson($data);
});

$app->get('/reporte/proyectos', function (Request $request, Response $response) {
    $idAlmacen = $request->getParam('idAlmacen');
    $filter = $request->getParam('filter');
    $fechaIni = $request->getParam('FechaIni');
    $fechaFin = $request->getParam('FechaFin');
    $select = "SELECT Ve_PreOrden.*,
     DATE_FORMAT(Ve_PreOrden.FechaAlquilerFin, '%Y-%m-%d') AS FechaAlquilerFinF,
      DATE_FORMAT(Ve_PreOrden.FechaAlquilerInicio, '%Y-%m-%d') AS FechaAlquilerInicioF, Ve_DocVentaCliente.Cliente,Ve_DocVentaCliente.DniRuc, IFNULL(DetalleProyecto.Total,0) AS TotalDetalle,
    IFNULL(DetalleProforma.Total,0) AS TotalProforma, CONCAT(Ve_DocVenta.Serie,'-',Ve_DocVenta.Numero) AS Comprobante,
    (IFNULL(DetalleProyecto.Total,0) - IFNULL(Cb_CajaBancoAdelanto.sumAdelanto,0) + IFNULL(Cb_CajaBancoGasto.sumGasto,0)) AS granTotalCalculado,
    IFNULL(Cb_CajaBancoAdelanto.sumAdelanto,0) AS Adelantos , IFNULL(Cb_CajaBancoGasto.sumGasto,0) AS Gastos,
    Ve_Proforma.Numero AS ProformaNumero, Ve_Proforma.Anio AS ProformaAnio ,  IFNULL(NombreProyecto.Nombre,'')  as Nombre,
     NombreProyecto.IdProducto
    FROM Ve_PreOrden
    LEFT JOIN Ve_DocVentaCliente ON Ve_PreOrden.IdCliente = Ve_DocVentaCliente.IdCliente

    LEFT JOIN (SELECT Ve_PreOrdenDet.IdPreOrden, Gen_Producto.Producto AS Nombre,Gen_Producto.IdProducto 
        FROM Ve_PreOrdenDet INNER JOIN Gen_Producto ON Ve_PreOrdenDet.IdProducto = Gen_Producto.IdProducto
        WHERE Gen_Producto.EsHabitacion = 1
    GROUP BY  Ve_PreOrdenDet.IdPreOrden,Gen_Producto.IdProducto) AS NombreProyecto ON Ve_PreOrden.IdPreOrden = NombreProyecto.IdPreOrden

    LEFT JOIN (SELECT Ve_PreOrdenDet.IdPreOrden, ROUND(IFNULL(SUM(IF(Ve_PreOrdenDet.EsManoDeObra=0,Ve_PreOrdenDet.Cantidad * Ve_PreOrdenDet.Precio,0)),0),2) AS Total
        FROM Ve_PreOrdenDet
    GROUP BY Ve_PreOrdenDet.IdPreOrden) AS DetalleProyecto ON Ve_PreOrden.IdPreOrden = DetalleProyecto.IdPreOrden
    LEFT JOIN (SELECT Ve_ProformaDet.IdProforma, ROUND(IFNULL(SUM(Ve_ProformaDet.Cantidad * Ve_ProformaDet.Precio - Ve_ProformaDet.Descuento),0),2) AS Total
        FROM Ve_ProformaDet
    GROUP BY Ve_ProformaDet.IdProforma) AS DetalleProforma ON Ve_PreOrden.IdProforma = DetalleProforma.IdProforma
    LEFT JOIN (
        SELECT Cb_CajaBanco.IdPreOrden, SUM(Cb_CajaBanco.Importe) AS sumAdelanto 
        FROM Cb_CajaBanco  
        INNER JOIN Cb_TipoCajaBanco ON Cb_TipoCajaBanco.IdTipoCajaBanco = Cb_CajaBanco.IdTipoCajaBanco 
        WHERE Cb_TipoCajaBanco.Tipo = 0
        GROUP BY Cb_CajaBanco.IdPreOrden
    ) Cb_CajaBancoAdelanto ON  Ve_PreOrden.IdPreOrden = Cb_CajaBancoAdelanto.IdPreOrden
    LEFT JOIN (
        SELECT Cb_CajaBanco.IdPreOrden, SUM(Cb_CajaBanco.Importe) AS sumGasto 
        FROM Cb_CajaBanco  
        INNER JOIN Cb_TipoCajaBanco ON Cb_TipoCajaBanco.IdTipoCajaBanco = Cb_CajaBanco.IdTipoCajaBanco 
        WHERE Cb_TipoCajaBanco.Tipo = 1 
        GROUP BY Cb_CajaBanco.IdPreOrden
    ) Cb_CajaBancoGasto ON  Ve_PreOrden.IdPreOrden = Cb_CajaBancoGasto.IdPreOrden
    LEFT JOIN Ve_Proforma ON Ve_PreOrden.IdProforma = Ve_Proforma.IdProforma
    LEFT JOIN Ve_DocVenta on Ve_PreOrden.IdDocVenta = Ve_DocVenta.idDocVenta
    WHERE (Ve_DocVentaCliente.Cliente LIKE '%" . $filter . "%' OR Ve_DocVentaCliente.DniRuc LIKE '%" . $filter . "%' OR Ve_PreOrden.IdPreOrden LIKE '%" . $filter . "%' OR Ve_PreOrden.FechaReg LIKE '%" . $filter . "%'  OR Nombre LIKE '%" . $filter . "%') ";

    if (($fechaIni &&  $fechaIni != '') && ($fechaFin &&  $fechaFin != '')) {
        $select .= " AND (Ve_PreOrden.FechaReg BETWEEN '" . $fechaIni . " 00:00:00' AND '" . $fechaFin . " 23:59:59')";
    }

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    if ($request->getParam('limit')) {
        $limit = $request->getParam('limit');
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();
    return $response->withJson($data);
});

$app->delete('/proforma/anular/{id}', function (Request $request, Response $response, array $args) {

    $sql2 = "UPDATE Ve_Proforma SET Anulado = 1 WHERE IdProforma = " . $args['id'];
    $stmt2 = $this->db->prepare($sql2);
    $success = $stmt2->execute();
    return $response->withJson($success);
});

// APIS DE AGENTE


$app->post('/entidad/anular', function (Request $request, Response $response) {
    $idEntidad = $request->getParam('IdEntidad');
    $anulado = $request->getParam('Anulado');
    $update = "UPDATE Ag_Entidad SET Anulado=$anulado WHERE IdEntidad = $idEntidad";
    $stmt = $this->db->prepare($update);
    $updated = $stmt->execute();

    return $response->withJson(array("success" => $updated));
});

$app->post('/empresa/anular', function (Request $request, Response $response) {
    $idEmpresaEntidad = $request->getParam('IdEmpresaEntidad');
    $anulado = $request->getParam('Anulado');
    $update = "UPDATE Ag_EmpresasEntidad SET Anulado=$anulado WHERE IdEmpresaEntidad = $idEmpresaEntidad";
    $stmt = $this->db->prepare($update);
    $updated = $stmt->execute();

    return $response->withJson(array("success" => $updated));
});

$app->post('/servicio/anular', function (Request $request, Response $response) {
    $idServicio = $request->getParam('IdServicio');
    $anulado = $request->getParam('Anulado');
    $update = "UPDATE Ag_Servicio SET Anulado=$anulado WHERE IdServicio = $idServicio";
    $stmt = $this->db->prepare($update);
    $updated = $stmt->execute();

    return $response->withJson(array("success" => $updated));
});




$app->post('/agente/entidad', function (Request $request, Response $response) {

    $nombre = $request->getParam('Nombre');
    $categoria = $request->getParam('Categoria');

    if ($request->getParam('IdEntidad')) {
        // aqui se actualiza el producto si existe
        $idEntidad = $request->getParam('IdEntidad');

        $update = $this->db->update(array(
            "Nombre" =>                 $nombre,
            "IdCategoriaEntidad" =>     $categoria,
        ))
            ->table('Ag_Entidad')
            ->where('IdEntidad', '=', $idEntidad);
        $affectedRows = $update->execute();
        // Actualizar ProductoDet
        return $response->withJson(array("insertId" => $idEntidad));
    }
    // Fin actualizacion producto

    // Inicio verificar Producto

    $insert = $this->db
        ->insert(
            array(
                'Nombre',
                'IdCategoriaEntidad',
                'FechaReg',
                'Imagen',
                'Anulado',
                'Orden'
            )
        )
        ->into('Ag_Entidad')
        ->values(
            array(
                $nombre,
                $categoria,
                getNow(),
                null,
                0,
                0
            )
        );
    $insertId = $insert->execute();
    return $response->withJson(array("insertId" => $insertId));
});


$app->get('/agente/cierrecaja/operaciones', function (Request $request, Response $response, array $args) {

    $selectFinal = "SELECT * FROM Ag_Entidad";
    $stmt = $this->db->query($selectFinal);
    $stmt->execute();
    $dataFinal = $stmt->fetchAll();

    $idCierre = $request->getParam('IdCierre');

    $select = "(SELECT Ag_Comprobante.*,Ag_Servicio.Servicio,Ag_CategoriaEmpresa.CategoriaEmpresa,Ag_Empresa.Empresa,
    Ag_Entidad.Nombre,Ag_EmpresasEntidad.IdEmpresaEntidad,Ag_Empresa.IdEmpresa,Ve_DocVentaCliente.Cliente,Ve_DocVentaCliente.DniRuc, 0 AS Tipo, CONCAT('R001', '-', Ag_Comprobante.IdComprobante) AS Correlativo FROM Ag_Comprobante
    INNER JOIN Ve_DocVentaCliente ON Ag_Comprobante.IdCliente = Ve_DocVentaCliente.IdCliente
    INNER JOIN Ag_Servicio ON Ag_Servicio.IdServicio = Ag_Comprobante.IdServicio
    INNER JOIN Ag_EmpresasEntidad ON Ag_EmpresasEntidad.IdEmpresaEntidad = Ag_Servicio.IdEmpresaEntidad
    INNER JOIN Ag_CategoriaEmpresa ON Ag_CategoriaEmpresa.IdCategoriaEmpresa = Ag_EmpresasEntidad.IdCategoriaEmpresa
    INNER JOIN Ag_Empresa ON Ag_Empresa.IdEmpresa = Ag_EmpresasEntidad.IdEmpresa
    INNER JOIN Ag_Entidad ON Ag_Entidad.IdEntidad = Ag_EmpresasEntidad.IdEntidad 
    WHERE Ag_Comprobante.IdCierre = $idCierre AND Ag_Comprobante.TipoOperacion = 2)";

    $select .= " UNION ALL ";

    $select .= "(SELECT Ag_Comprobante.*,Ag_Operacion.Operacion AS Servicio,'OPERACION' AS CategoriaEmpresa, Ag_Entidad.Nombre AS Empresa,
    Ag_Entidad.Nombre, NULL AS IdEmpresaEntidad, NULL AS IdEmpresa,Ve_DocVentaCliente.Cliente,Ve_DocVentaCliente.DniRuc, Ag_Operacion.Tipo, CONCAT('R001', '-', Ag_Comprobante.IdComprobante) AS Correlativo  FROM Ag_Comprobante
    INNER JOIN Ve_DocVentaCliente ON Ag_Comprobante.IdCliente = Ve_DocVentaCliente.IdCliente
    INNER JOIN Ag_Operacion ON Ag_Operacion.IdOperacion = Ag_Comprobante.IdServicio 
    INNER JOIN Ag_Entidad ON Ag_Entidad.IdEntidad = Ag_Operacion.IdEntidad
    WHERE Ag_Comprobante.IdCierre = $idCierre AND Ag_Comprobante.TipoOperacion = 1)";

    $select .= " ORDER BY FechaDoc ASC ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    //Restructuracion de data

    $resFinal = array();
    foreach ($dataFinal as $key => $entidad) {
        $dataFinal[$key]['Comprobantes'] = array();
        $dataFinal[$key]['TotalMonto'] = 0;
        $dataFinal[$key]['TotalComision'] = 0;
        $dataFinal[$key]['Total'] = 0;

        foreach ($data as $row) {
            if ($entidad['IdEntidad'] == $row['IdEntidad']) {
                array_push($dataFinal[$key]["Comprobantes"], $row);
                if ($row['Anulado'] != 1) {
                    $dataFinal[$key]['TotalMonto'] += $row['Monto'];
                    $dataFinal[$key]['TotalComision'] += $row['Comision'];
                    $dataFinal[$key]['Total'] += $row['Total'];
                }
            }
        }

        if (count($dataFinal[$key]['Comprobantes']) > 0) {
            array_push($resFinal, $dataFinal[$key]);
        }
    }


    return $response->withJson($resFinal);
});

$app->post('/agente/comprobante/anular', function (Request $request, Response $response, array $args) {
    $idComprobante = $request->getParam('IdComprobante');
    $motivo = $request->getParam('Motivo');

    $select = "SELECT * FROM Ag_Comprobante WHERE IdComprobante = " . $idComprobante;
    $stmt = $this->db->query($select);
    $data = $stmt->fetch();
    $stmt->execute();

    if ($data['Anulado'] == 1) {
        $data = array(
            'success' => false,
            'msg' => 'El comprobante ya tiene otro estado'
        );
        return $response->withJson($data);
    }

    $update = "UPDATE Ag_Comprobante SET Anulado=1, AnulacionMotivo='" . $motivo . "', AnulacionFecha='" . getNow() . "'  WHERE IdComprobante=$idComprobante";
    $stmt = $this->db->prepare($update);
    $updated = $stmt->execute();
    $data = array(
        'success' => true,
        'msg' => '¡Comprobante Anulado!'
    );
    return $response->withJson($data);
});


//RESTRUCTURACION DE AGENTE

$app->get('/agente/entidad', function (Request $request, Response $response, array $args) {
    $filter = $request->getParam('filter') ? $request->getParam('filter') : '';

    $select = "SELECT * FROM Ag_Entidad";

    $select .= " WHERE Ag_Entidad.Nombre LIKE '%" . $filter . "%'";

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  20;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    $select = "SELECT COUNT(*) as total FROM Ag_Entidad";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $datacount = $stmt->fetch(PDO::FETCH_ASSOC);


    return $response->withJson(array('data' => $data, 'count' => $datacount));
});


$app->get('/agente/entidad/empresa', function (Request $request, Response $response, array $args) {
    $q = $request->getParam('q');
    $idEntidad = $request->getParam('IdEntidad');

    if (!($idEntidad > 0)) {
        return $response->withJson([]);
    }

    $select = "SELECT Ag_EmpresasEntidad.*,Ag_Entidad.Nombre,Ag_Empresa.Empresa, Ag_CategoriaEmpresa.CategoriaEmpresa FROM Ag_EmpresasEntidad
    INNER JOIN Ag_Entidad ON Ag_EmpresasEntidad.IdEntidad = Ag_Entidad.IdEntidad
    INNER JOIN Ag_Empresa ON Ag_EmpresasEntidad.IdEmpresa = Ag_Empresa.IdEmpresa
    INNER JOIN Ag_CategoriaEmpresa ON Ag_EmpresasEntidad.IdCategoriaEmpresa =  Ag_CategoriaEmpresa.IdCategoriaEmpresa
    WHERE Ag_Entidad.IdEntidad =  $idEntidad  AND Ag_Empresa.Empresa LIKE '%$q%' ";

    if ($request->getParam('IdCategoriaEmpresa') > 0) {
        $select .= " AND Ag_EmpresasEntidad.IdCategoriaEmpresa = " . $request->getParam('IdCategoriaEmpresa');
    }

    if ($request->getParam('mostrarTodo') != 1) {
        $select .= " AND Ag_EmpresasEntidad.Anulado = 0 ";
    }

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    } else {
        $select .= " ORDER BY Ag_Empresa.Empresa ASC";
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  10;
    $noLimit = $request->getParam('noLimit') ? true : false;
    if ($limit && !$noLimit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    $select = "SELECT COUNT(*) as total FROM Ag_EmpresasEntidad WHERE Ag_EmpresasEntidad.IdEntidad =  $idEntidad";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $datacount = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson(array('data' => $data, 'count' => $datacount));
});

$app->get('/agente/empresa/categoria', function (Request $request, Response $response, array $args) {
    $q = $request->getParam('q');
    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;

    $select = "SELECT * FROM Ag_CategoriaEmpresa";
    $select .= " WHERE CategoriaEmpresa LIKE '" . $q . "%' ";

    if ($limit) {
        // $limit = $request->getParam('limit');
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->post('/agente/empresa/categoria', function (Request $request, Response $response, array $args) {
    $categoriaEmpresa = $request->getParam('CategoriaEmpresa');
    $anulado = 0;

    $insert = $this->db->insert(array('CategoriaEmpresa', 'Anulado', 'FechaReg'))
        ->into('Ag_CategoriaEmpresa')
        ->values(array($categoriaEmpresa, $anulado, getNow()));
    $insertId = $insert->execute();

    return $response->withJson(array("insertId" => $insertId, "CategoriaEmpresa" => $categoriaEmpresa));
});

$app->get('/agente/empresa', function (Request $request, Response $response, array $args) {
    $q = $request->getParam('q');
    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;

    $select = "SELECT * FROM Ag_Empresa";
    $select .= " WHERE Empresa LIKE '" . $q . "%' ";

    if ($limit) {
        // $limit = $request->getParam('limit');
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->post('/agente/empresa', function (Request $request, Response $response, array $args) {
    $empresa = $request->getParam('Empresa');
    $anulado = 0;

    $insert = $this->db->insert(array('Empresa', 'Anulado', 'FechaReg'))
        ->into('Ag_Empresa')
        ->values(array($empresa, $anulado, getNow()));
    $insertId = $insert->execute();

    return $response->withJson(array("insertId" => $insertId, "Empresa" => $empresa));
});

$app->post('/agente/entidad/empresa', function (Request $request, Response $response, array $args) {

    $idEntidad = $request->getParam('idEntidad');
    $idEmpresa = $request->getParam('empresa')['IdEmpresa'];
    $idEmpresaCategoria = $request->getParam('categoria')['IdCategoriaEmpresa'];

    if (!($idEntidad > 0)) {
        $data = array(
            'error' => 'La entidad no ha sido cargada',
        );
        return $response->withJson($data);
    }

    $select = "SELECT * FROM Ag_EmpresasEntidad WHERE IdEntidad = " . $idEntidad . " AND IdEmpresa =" . $idEmpresa . " AND IdCategoriaEmpresa=" . $idEmpresaCategoria;
    $stmt = $this->db->query($select);
    $empre = $stmt->fetch();
    $stmt->execute();

    if (!empty($empre)) {
        $data = array(
            'error' => 'La empresa y la categoria ya existe para la entidad seleccionada',
            'insertId' => $opera['IdEmpresaEntidad']
        );
        return $response->withJson($data);
    }

    $insert = $this->db->insert(array('IdEntidad', 'IdEmpresa', 'IdCategoriaEmpresa', 'FechaReg', 'Anulado'))
        ->into('Ag_EmpresasEntidad')
        ->values(array($idEntidad, $idEmpresa, $idEmpresaCategoria, getNow(), 0));
    $insertId = $insert->execute();

    return $response->withJson(array("insertId" => $insertId, "Empresa" => $productoMarca));
});

$app->get('/agente/entidad/empresa/servicio', function (Request $request, Response $response, array $args) {
    $q = $request->getParam('q');
    $idEmpresaEntidad = $request->getParam('IdEmpresaEntidad');

    if (!($idEmpresaEntidad > 0)) {
        return $response->withJson([]);
    }

    $select = "SELECT Ag_Servicio.*,Ag_Entidad.Nombre,Ag_Empresa.Empresa,Ag_CategoriaEmpresa.CategoriaEmpresa FROM Ag_Servicio
    INNER JOIN Ag_EmpresasEntidad ON Ag_Servicio.IdEmpresaEntidad = Ag_EmpresasEntidad.IdEmpresaEntidad
    INNER JOIN Ag_Entidad ON Ag_EmpresasEntidad.IdEntidad = Ag_Entidad.IdEntidad
    INNER JOIN Ag_Empresa ON Ag_EmpresasEntidad.IdEmpresa = Ag_Empresa.IdEmpresa
    INNER JOIN Ag_CategoriaEmpresa ON Ag_EmpresasEntidad.IdCategoriaEmpresa =  Ag_CategoriaEmpresa.IdCategoriaEmpresa
    WHERE Ag_Servicio.IdEmpresaEntidad =  $idEmpresaEntidad AND Ag_Servicio.Servicio LIKE '%$q%' ";

    if ($request->getParam('mostrarTodo') != 1) {
        $select .= " AND Ag_Servicio.Anulado = 0 ";
    }

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    } else {
        $select .= " ORDER BY Ag_Servicio.Servicio ASC ";
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  10;
    $noLimit = $request->getParam('noLimit') ? true : false;
    if ($limit && !$noLimit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    if ($request->getParam('SoloData') == 1) {
        return $response->withJson($data);
    }

    $select = "SELECT COUNT(*) as total FROM Ag_Servicio WHERE Ag_Servicio.IdEmpresaEntidad =  $idEmpresaEntidad";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $datacount = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson(array('data' => $data, 'count' => $datacount));
});

$app->post('/agente/entidad/empresa/servicio', function (Request $request, Response $response, array $args) {
    $idEmpresaEntidad = $request->getParam('IdEmpresaEntidad');

    if (!($idEmpresaEntidad > 0)) {
        $data = array(
            'error' => 'La empresa no ha sido cargada',
        );
        return $response->withJson($data);
    }

    $calculo = $request->getParam('Calculo') ? $request->getParam('Calculo') : 0;
    $comisionFija = $request->getParam('ComisionFija') ? $request->getParam('ComisionFija') : 0;
    $ganancia = $request->getParam('Ganancia') ? $request->getParam('Ganancia') : 0;
    $tieneComision = $request->getParam('TieneComision') ? $request->getParam('TieneComision') : 0;
    $requiereCodigo = $request->getParam('RequiereCodigo') ? $request->getParam('RequiereCodigo') : 0;
    $nombreCodigo = $request->getParam('NombreCodigo') ? $request->getParam('NombreCodigo') : '';
    $nombreServicio = $request->getParam('NombreServicio') ? $request->getParam('NombreServicio') : '';

    $montoComision = $request->getParam('MontoComision') > 0 ? $request->getParam('MontoComision') : 0;
    $montoMax = $request->getParam('MontoMax') > 0 ? $request->getParam('MontoMax') : 0;

    // Actualizamos el producto si le pasamos el ID
    if ($request->getParam('IdServicio')) {
        // aqui se actualiza el producto si existe
        $idServicio = $request->getParam('IdServicio');

        $select = "SELECT * FROM Ag_Servicio WHERE IdEmpresaEntidad = " . $idEmpresaEntidad . " AND Servicio ='" . $nombreServicio . "' AND IdServicio <>  $idServicio";
        $stmt = $this->db->query($select);
        $servi = $stmt->fetch();
        $stmt->execute();

        if (!empty($servi)) {
            $data = array(
                'error' => 'El servicio ya existe - ' . $servi['Servicio'],
                'insertId' => $servi['Servicio']
            );
            return $response->withJson($data);
        }

        $update = $this->db->update(array(
            "Servicio" =>                   $nombreServicio,
            "MontoMax" =>                   $montoMax,
            "MontoComision" =>              $montoComision,
            "Calculo" =>                    $calculo,
            "TieneComision" =>              $tieneComision,
            "Ganancia" =>                   $ganancia,
            "ComisionFija" =>               $comisionFija,
            "RequiereCodigo" =>             $requiereCodigo,
            "NombreCodigo" =>               $nombreCodigo,
        ))
            ->table('Ag_Servicio')
            ->where('IdServicio', '=', $idServicio);
        $affectedRows = $update->execute();
        // Actualizar ProductoDet
        return $response->withJson(array("insertId" => $idServicio));
    }
    // Fin actualizacion producto

    // Inicio verificar Producto
    $select = "SELECT * FROM Ag_Servicio WHERE IdEmpresaEntidad = " . $idEmpresaEntidad . " AND Servicio ='" . $nombreServicio . "'";
    $stmt = $this->db->query($select);
    $servi = $stmt->fetch();
    $stmt->execute();

    if (!empty($servi)) {
        $data = array(
            'error' => 'El servicio ya existe - ' . $servi['Servicio'],
            'insertId' => $servi['Servicio']
        );
        return $response->withJson($data);
    }

    $insert = $this->db
        ->insert(
            array(
                'IdEmpresaEntidad',
                'Servicio',
                'MontoMax',
                'MontoComision',
                'FechaReg',
                'Calculo',
                'Anulado',
                'TieneComision',
                'Ganancia',
                'ComisionFija',
                'RequiereCodigo',
                'NombreCodigo'
            )
        )
        ->into('Ag_Servicio')
        ->values(
            array(
                $idEmpresaEntidad,
                $nombreServicio,
                $montoMax,
                $montoComision,
                getNow(),
                $calculo,
                0,
                $tieneComision,
                $ganancia,
                $comisionFija,
                $requiereCodigo,
                $nombreCodigo
            )
        );
    $insertId = $insert->execute();
    return $response->withJson(array("insertId" => $insertId));
});

$app->get('/agente/entidad/empresa/categoria', function (Request $request, Response $response, array $args) {
    $q = $request->getParam('q') ? $request->getParam('q') : '';
    $idEntidad = $request->getParam('IdEntidad');

    $select = "SELECT Ag_CategoriaEmpresa.IdCategoriaEmpresa, Ag_CategoriaEmpresa.CategoriaEmpresa FROM Ag_EmpresasEntidad
    INNER JOIN Ag_Entidad ON Ag_EmpresasEntidad.IdEntidad = Ag_Entidad.IdEntidad
    INNER JOIN Ag_CategoriaEmpresa ON Ag_EmpresasEntidad.IdCategoriaEmpresa =  Ag_CategoriaEmpresa.IdCategoriaEmpresa
    WHERE Ag_Entidad.IdEntidad = $idEntidad AND Ag_CategoriaEmpresa.CategoriaEmpresa LIKE '%" . $q . "%' GROUP BY Ag_CategoriaEmpresa.IdCategoriaEmpresa ";

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    } else {
        $select .= " ORDER BY Ag_CategoriaEmpresa.CategoriaEmpresa ASC";
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  20;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    $select = "SELECT COUNT(*) as total FROM Ag_EmpresasEntidad
    INNER JOIN Ag_Entidad ON Ag_EmpresasEntidad.IdEntidad = Ag_Entidad.IdEntidad
    INNER JOIN Ag_CategoriaEmpresa ON Ag_EmpresasEntidad.IdCategoriaEmpresa =  Ag_CategoriaEmpresa.IdCategoriaEmpresa
    WHERE Ag_Entidad.IdEntidad = $idEntidad GROUP BY Ag_CategoriaEmpresa.IdCategoriaEmpresa ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $datacount = $stmt->fetch(PDO::FETCH_ASSOC);


    return $response->withJson(array('data' => $data, 'count' => $datacount));
});

$app->post('/agente/comprobante', function (Request $request, Response $response) {
    $vendedor = 'xx';

    if (isset($_SESSION['User'])) {
        $vendedor = $_SESSION['User'];
    }

    $idCliente = $request->getParam('cliente')['IdCliente'];
    $idEntidad = $request->getParam('idEntidad');
    $idServicio = $request->getParam('idServicio');
    $monto = $request->getParam('monto');
    $comision = $request->getParam('comision') > 0 ? $request->getParam('comision') : 0;
    $montoComision = $request->getParam('montoComision') > 0 ? $request->getParam('montoComision') : 0;
    $montoMax = $request->getParam('montoMax') > 0 ? $request->getParam('montoMax') : 0;
    $ganancia = $request->getParam('ganancia');

    $nombreCodigo = $request->getParam('nombreCodigo') ? $request->getParam('nombreCodigo') : '';
    $codigo = $request->getParam('codigo') ? $request->getParam('codigo') : '';
    $telefono = $request->getParam('telefono') ? $request->getParam('telefono') : '';
    $observacion = $request->getParam('observacion') ? $request->getParam('observacion') : '';
    $comisionFija = $request->getParam('comisionFija');
    $operacionTipo = $request->getParam('operacionTipo');
    $comisionIncluida = $request->getParam('comisionIncluida') > 0 ? 1 : 0;

    $usuarioReg = $request->getParam('usuario') ? $request->getParam('usuario') : $vendedor;

    if ($usuarioReg == 'xx') {
        $data = array(
            'error'    => true,
            'msg'      => 'Error, no se identifico su usuario, vuelva a iniciar sesión',
        );
        return $response->withJson($data);
    }

    //Obtener almacen y punto según vendedor
    $idCajaAbierta = null;
    $existeUser = false;
    $selectUsuario = "SELECT * FROM Seg_Usuario WHERE Usuario = '" . trim($usuarioReg) . "'";
    $stmtUser = $this->db->query($selectUsuario);
    $user = $stmtUser->fetch();
    $stmtUser->execute();

    if (!empty($user)) {
        $idCajaAbierta = $user['CajaAbierta'];
        $existeUser = true;
    }

    if (!($idCajaAbierta > 0)) {
        $data = array(
            'error'    => true,
            'msg'      => 'Para realizar operaciones tiene que abrir su caja',
            'noexistecaja'   => 1
        );
        return $response->withJson($data);
    }

    if (!$existeUser) {
        $data = array(
            'error'    => true,
            'msg'      => 'No existe el usuario...',
        );
        return $response->withJson($data);
    }

    if (!($operacionTipo > 0)) {
        $data = array(
            'error'    => true,
            'msg'      => 'No se cargo la opción...',
        );
        return $response->withJson($data);
    }

    $insert = "INSERT INTO Ag_Comprobante (IdCliente, IdEntidad, IdServicio, FechaDoc, Monto, Comision, ComisionValor, ComisionCada, ComisionFija, Ganancia, NombreCodigo, ValorCodigo, Telefono, Estado, Anulado, Observacion, UsuarioReg, IdCierre, TipoOperacion, ComisionIncluida)
        VALUES ($idCliente, $idEntidad, $idServicio, '" . getNow() . "', $monto, $comision, $montoComision, $montoMax, $comisionFija ,$ganancia, '" . strtoupper($nombreCodigo) . "', '$codigo', '$telefono', 1, 0, '$observacion', '$usuarioReg', $idCajaAbierta, $operacionTipo, $comisionIncluida)";

    $stmt = $this->db->prepare($insert);
    $inserted = $stmt->execute();
    $idDocVenta = $this->db->lastInsertId();

    $data = array(
        'insertId' => $idDocVenta
    );

    return $response->withJson($data);
});

$app->post('/agente/comprobante/procesar', function (Request $request, Response $response) {
    $idComprobante = $request->getParam('IdComprobante');
    $numeroOperacion = $request->getParam('NumeroOperacion');
    $update = "UPDATE Ag_Comprobante SET NumeroOperacion='$numeroOperacion', FechaOperacion = '" . getNow() . "', Estado = 2  WHERE IdComprobante = $idComprobante";
    $stmt = $this->db->prepare($update);
    $updated = $stmt->execute();

    return $response->withJson(array("success" => $updated));
});

$app->get('/agente/usuarios/{usuario}', function (Request $request, Response $response, array $args) {
    $select = "SELECT Seg_Usuario.*,Ag_CierreCaja.FechaApertura,Ag_CierreCaja.FechaCierre,Ag_CierreCaja.Estado,
    Ag_CierreCaja.IdTurno,Ag_CierreCaja.IdCierreCaja FROM Seg_Usuario 
    LEFT JOIN Ag_CierreCaja ON Seg_Usuario.CajaAbierta = Ag_CierreCaja.IdCierreCaja
    WHERE Seg_Usuario.Usuario = '$args[usuario]' ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetch();

    return $response->withJson($data);
});


$app->post('/agente/cierrecaja/aperturar', function (Request $request, Response $response, array $args) {
    $usuario = $request->getParam('Usuario');

    $existeUser = false;
    $idCajaAbierta = null;
    $selectUsuario = "SELECT * FROM Seg_Usuario WHERE Usuario = '" . trim($usuario) . "'";
    $stmtUser = $this->db->query($selectUsuario);
    $user = $stmtUser->fetch();
    $stmtUser->execute();

    if (!empty($user)) {
        $existeUser = true;
        $idCajaAbierta = $user['CajaAbierta'];
    }

    if ($idCajaAbierta > 0) {
        $data = array(
            'success'    => false,
            'msg'      => 'Ya tiene una caja aperturada, se actualizará el modulo...',
        );
        return $response->withJson($data);
    }

    if (!$existeUser) {
        $data = array(
            'success'    => false,
            'msg'      => 'No existe el usuario...',
        );
        return $response->withJson($data);
    }

    $insert = $this->db->insert(array('UsuarioReg', 'Estado', 'FechaApertura', 'Monto', 'Comision'))
        ->into('Ag_CierreCaja')
        ->values(array(trim($usuario), 1, getNow(), 0, 0));
    $insertId = $insert->execute();

    $update = "UPDATE Seg_Usuario SET CajaAbierta=$insertId WHERE Usuario = '" . trim($usuario) . "'";
    $stmt = $this->db->prepare($update);
    $updated = $stmt->execute();

    return $response->withJson(array("success" => $updated, "insertId" => $insertId));
});


$app->post('/agente/cierrecaja/cerrar', function (Request $request, Response $response, array $args) {
    $usuario = $request->getParam('Usuario');

    $existeUser = false;
    $idCajaAbierta = null;
    $selectUsuario = "SELECT * FROM Seg_Usuario WHERE Usuario = '" . trim($usuario) . "'";
    $stmtUser = $this->db->query($selectUsuario);
    $user = $stmtUser->fetch();
    $stmtUser->execute();

    if (!empty($user)) {
        $existeUser = true;
        $idCajaAbierta = $user['CajaAbierta'];
    }

    if (!($idCajaAbierta > 0)) {
        $data = array(
            'success'    => false,
            'msg'      => 'No cuenta con una caja aperturada, se actualizará el modulo...',
        );
        return $response->withJson($data);
    }

    if (!$existeUser) {
        $data = array(
            'success'    => false,
            'msg'      => 'No existe el usuario...',
        );
        return $response->withJson($data);
    }

    $select = "SELECT Ag_Comprobante.*,Ag_Servicio.Servicio,Ag_CategoriaEmpresa.CategoriaEmpresa,Ag_Empresa.Empresa,
    Ag_Entidad.Nombre,Ag_EmpresasEntidad.IdEmpresaEntidad,Ag_Empresa.IdEmpresa,Ve_DocVentaCliente.Cliente,Ve_DocVentaCliente.DniRuc FROM Ag_Comprobante
    INNER JOIN Ve_DocVentaCliente ON Ag_Comprobante.IdCliente = Ve_DocVentaCliente.IdCliente
    INNER JOIN Ag_Servicio ON Ag_Servicio.IdServicio = Ag_Comprobante.IdServicio
    INNER JOIN Ag_EmpresasEntidad ON Ag_EmpresasEntidad.IdEmpresaEntidad = Ag_Servicio.IdEmpresaEntidad
    INNER JOIN Ag_CategoriaEmpresa ON Ag_CategoriaEmpresa.IdCategoriaEmpresa = Ag_EmpresasEntidad.IdCategoriaEmpresa
    INNER JOIN Ag_Empresa ON Ag_Empresa.IdEmpresa = Ag_EmpresasEntidad.IdEmpresa
    INNER JOIN Ag_Entidad ON Ag_Entidad.IdEntidad = Ag_EmpresasEntidad.IdEntidad ";

    $select .= " WHERE Ag_Comprobante.IdCierre = $idCajaAbierta";

    $select .= " ORDER BY Ag_Comprobante.FechaDoc ASC;";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    $montoTotal = 0;
    $comisionTotal = 0;
    $sinProcesar = 0;
    foreach ($data as $key => $comprobante) {
        if ($comprobante['Anulado'] == 0) {
            $montoTotal += $comprobante['Monto'];
            $comisionTotal += $comprobante['Comision'];
        }

        if ($comprobante['Estado'] != 2 && $comprobante['Anulado'] == 0) {
            $sinProcesar++;
        }
    }

    if ($sinProcesar > 0) {
        $data = array(
            'success'    => false,
            'msg'      => 'Existen operaciones sin procesar, procesarlas antes de cerrar caja',
        );
        return $response->withJson($data);
    }

    $update = "UPDATE Ag_CierreCaja SET Estado = 2, FechaCierre = '" . getNow() . "', Monto = $montoTotal, Comision= $comisionTotal WHERE IdCierreCaja = $idCajaAbierta";
    $stmt = $this->db->prepare($update);
    $updated = $stmt->execute();

    $update = "UPDATE Seg_Usuario SET CajaAbierta = NULL WHERE Usuario = '" . trim($usuario) . "'";
    $stmt = $this->db->prepare($update);
    $updated = $stmt->execute();

    return $response->withJson(array("success" => $updated, "insertId" => $insertId));
});


$app->get('/agente/cierrecaja', function (Request $request, Response $response, array $args) {
    $idCierre = $request->getParam('idCierre');
    $filter = $request->getParam('filter');

    $select = "SELECT * FROM Ag_CierreCaja WHERE (FechaCierre LIKE '%$filter%' OR UsuarioReg LIKE '%$filter%')";

    if ($idCierre) {
        $select .= " AND IdCierreCaja=$idCierre";
    }

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
    if ($limit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }


    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    $select = "SELECT COUNT(*) as total FROM Ag_CierreCaja ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $datacount = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson(array('data' => $data, 'count' => $datacount));
});

//Operaciones
$app->get('/agente/entidad/operacion', function (Request $request, Response $response, array $args) {
    $q = $request->getParam('q');
    $idEntidad = $request->getParam('IdEntidad');

    if (!($idEntidad > 0)) {
        return $response->withJson([]);
    }

    $select = "SELECT Ag_Operacion.*,Ag_Entidad.Nombre FROM Ag_Operacion
    INNER JOIN Ag_Entidad ON Ag_Operacion.IdEntidad = Ag_Entidad.IdEntidad
    WHERE Ag_Entidad.IdEntidad =  $idEntidad AND Ag_Operacion.Operacion LIKE '%$q%' ";

    if ($request->getParam('mostrarTodo') != 1) {
        $select .= " AND Ag_Operacion.Anulado = 0 ";
    }

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    } else {
        $select .= " ORDER BY Ag_Operacion.Operacion ASC ";
    }

    $limit = $request->getParam('limit') ? $request->getParam('limit') :  10;
    $noLimit = $request->getParam('noLimit') ? true : false;
    if ($limit && !$noLimit) {
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    if ($request->getParam('SoloData') == 1) {
        return $response->withJson($data);
    }

    $select = "SELECT COUNT(*) as total FROM Ag_Operacion WHERE IdEntidad =  $idEntidad";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $datacount = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson(array('data' => $data, 'count' => $datacount));
});

$app->post('/agente/entidad/operacion', function (Request $request, Response $response, array $args) {
    $idEntidad = $request->getParam('IdEntidad');

    if (!($idEntidad > 0)) {
        $data = array(
            'error' => 'La entidad no ha sido cargada',
        );
        return $response->withJson($data);
    }

    $calculo = $request->getParam('Calculo') ? $request->getParam('Calculo') : 0;
    $comisionFija = $request->getParam('ComisionFija') ? $request->getParam('ComisionFija') : 0;
    $ganancia = $request->getParam('Ganancia') ? $request->getParam('Ganancia') : 0;
    $tieneComision = $request->getParam('TieneComision') ? $request->getParam('TieneComision') : 0;
    $requiereCodigo = $request->getParam('RequiereCodigo') ? $request->getParam('RequiereCodigo') : 0;
    $nombreCodigo = $request->getParam('NombreCodigo') ? $request->getParam('NombreCodigo') : '';
    $operacion = $request->getParam('Operacion') ? $request->getParam('Operacion') : '';

    $montoComision = $request->getParam('MontoComision') > 0 ? $request->getParam('MontoComision') : 0;
    $montoMax = $request->getParam('MontoMax') > 0 ? $request->getParam('MontoMax') : 0;

    $tipo = $request->getParam('Tipo') >= 0 ? $request->getParam('Tipo') : null;

    $tieneEnlaceExterno = $request->getParam('TieneEnlaceExterno') > 0 ? $request->getParam('TieneEnlaceExterno') : 0;
    $enlaceExterno1 = $request->getParam('EnlaceExterno1') ? $request->getParam('EnlaceExterno1') : '';
    $enlaceExterno2 = $request->getParam('EnlaceExterno2') ? $request->getParam('EnlaceExterno2') : '';

    // Actualizamos el producto si le pasamos el ID
    if ($request->getParam('IdOperacion')) {
        // aqui se actualiza el producto si existe
        $idOperacion = $request->getParam('IdOperacion');

        $select = "SELECT * FROM Ag_Operacion WHERE IdEntidad = " . $idEntidad . " AND Operacion ='" . $operacion . "' AND IdOperacion <>  $idOperacion";
        $stmt = $this->db->query($select);
        $opera = $stmt->fetch();
        $stmt->execute();

        if (!empty($opera)) {
            $data = array(
                'error' => 'La operacion ya existe - ' . $opera['Operacion'],
                'insertId' => $opera['Operacion']
            );
            return $response->withJson($data);
        }

        $update = $this->db->update(array(
            "Operacion" =>                  $operacion,
            "MontoMax" =>                   $montoMax,
            "MontoComision" =>              $montoComision,
            "Calculo" =>                    $calculo,
            "TieneComision" =>              $tieneComision,
            "Ganancia" =>                   $ganancia,
            "ComisionFija" =>               $comisionFija,
            "RequiereCodigo" =>             $requiereCodigo,
            "NombreCodigo" =>               $nombreCodigo,
            "Tipo" =>                       $tipo,
            "EnlaceExterno1" =>             $enlaceExterno1,
            "EnlaceExterno2" =>             $enlaceExterno2,
            "TieneEnlaceExterno" =>         $tieneEnlaceExterno,
        ))
            ->table('Ag_Operacion')
            ->where('IdOperacion', '=', $idOperacion);
        $affectedRows = $update->execute();
        // Actualizar ProductoDet
        return $response->withJson(array("insertId" => $idOperacion));
    }
    // Fin actualizacion producto

    // Inicio verificar Producto
    $select = "SELECT * FROM Ag_Operacion WHERE IdEntidad = " . $idEntidad . " AND Operacion ='" . $operacion . "'";
    $stmt = $this->db->query($select);
    $opera = $stmt->fetch();
    $stmt->execute();

    if (!empty($opera)) {
        $data = array(
            'error' => 'La operacion ya existe - ' . $opera['Operacion'],
            'insertId' => $opera['Operacion']
        );
        return $response->withJson($data);
    }

    $insert = $this->db
        ->insert(
            array(
                'IdEntidad',
                'Operacion',
                'MontoMax',
                'MontoComision',
                'FechaReg',
                'Calculo',
                'Anulado',
                'TieneComision',
                'Ganancia',
                'ComisionFija',
                'RequiereCodigo',
                'NombreCodigo',
                'Tipo',
                'EnlaceExterno1',
                'EnlaceExterno2',
                'TieneEnlaceExterno'
            )
        )
        ->into('Ag_Operacion')
        ->values(
            array(
                $idEntidad,
                $operacion,
                $montoMax,
                $montoComision,
                getNow(),
                $calculo,
                0,
                $tieneComision,
                $ganancia,
                $comisionFija,
                $requiereCodigo,
                $nombreCodigo,
                $tipo,
                $enlaceExterno1,
                $enlaceExterno2,
                $tieneEnlaceExterno,
            )
        );
    $insertId = $insert->execute();
    return $response->withJson(array("insertId" => $insertId));
});

$app->post('/agente/caja', function (Request $request, Response $response) {

    $usuario = $request->getParam('Usuario');
    $tipo = $request->getParam('Tipo');
    $importe = $request->getParam('Importe');
    $concepto = $request->getParam('Concepto');

    if (!$usuario) {
        $data = array(
            'error' => 'El usuario no se cargó correctamente',
            'insertId' => null
        );
        return $response->withJson($data);
    }

    if ($tipo != 0 && $tipo != 1) {
        $data = array(
            'error' => 'El tipo de la operación no se especifico',
            'insertId' => null
        );
        return $response->withJson($data);
    }

    $idCajaAbierta = null;
    $existeUser = false;
    $selectUsuario = "SELECT * FROM Seg_Usuario WHERE Usuario = '" . trim($usuario) . "'";
    $stmtUser = $this->db->query($selectUsuario);
    $user = $stmtUser->fetch();
    $stmtUser->execute();

    if (!empty($user)) {
        $idCajaAbierta = $user['CajaAbierta'];
        $existeUser = true;
    }

    if (!($idCajaAbierta > 0)) {
        $data = array(
            'error'    => 'Para realizar movimientos tiene que abrir su caja',
            'insertId' => null
        );
        return $response->withJson($data);
    }

    if (!$existeUser) {
        $data = array(
            'error'    => 'No existe el usuario',
            'insertId' => null
        );
        return $response->withJson($data);
    }



    $insertDet = $this->db->insert(array('Concepto', 'Importe', 'UsuarioReg', 'FechaReg', 'IdCierre', 'Tipo', 'Anulado'))
        ->into('Ag_Caja')
        ->values(array($concepto, $importe, trim($usuario), getNow(), $idCajaAbierta, $tipo, 0));
    $insertId = $insertDet->execute();


    return $response->withJson(array(
        "insertId" => $insertId
    ));
});

$app->get('/agente/caja', function (Request $request, Response $response, array $args) {

    $idCierre = $request->getParam('IdCierre');

    $select = "SELECT * FROM Ag_Caja
    WHERE IdCierre = $idCierre";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();


    return $response->withJson($data);
});

$app->post('/agente/caja/anular', function (Request $request, Response $response, array $args) {
    $idCaja = $request->getParam('IdCaja');
    $motivo = $request->getParam('Motivo');

    $select = "SELECT * FROM Ag_Caja WHERE IdCaja = " . $idCaja;
    $stmt = $this->db->query($select);
    $data = $stmt->fetch();
    $stmt->execute();

    if ($data['Anulado'] == 1) {
        $data = array(
            'success' => false,
            'msg' => 'El movimiento ya tiene otro estado'
        );
        return $response->withJson($data);
    }

    $update = "UPDATE Ag_Caja SET Anulado=1, AnulacionMotivo='" . $motivo . "', AnulacionFecha='" . getNow() . "'  WHERE IdCaja=$idCaja";
    $stmt = $this->db->prepare($update);
    $updated = $stmt->execute();
    $data = array(
        'success' => true,
        'msg' => '¡Movimiento Anulado!'
    );
    return $response->withJson($data);
});

$app->get('/agente/comprobante', function (Request $request, Response $response) {
    $select = "(SELECT Ag_Comprobante.*,Ag_Servicio.Servicio,Ag_CategoriaEmpresa.CategoriaEmpresa,Ag_Empresa.Empresa,
    Ag_Entidad.Nombre,Ag_EmpresasEntidad.IdEmpresaEntidad,Ag_Empresa.IdEmpresa,Ve_DocVentaCliente.Cliente,Ve_DocVentaCliente.DniRuc, 0 AS Tipo, CONCAT('R001', '-', Ag_Comprobante.IdComprobante) AS Correlativo FROM Ag_Comprobante
    INNER JOIN Ve_DocVentaCliente ON Ag_Comprobante.IdCliente = Ve_DocVentaCliente.IdCliente
    INNER JOIN Ag_Servicio ON Ag_Servicio.IdServicio = Ag_Comprobante.IdServicio
    INNER JOIN Ag_EmpresasEntidad ON Ag_EmpresasEntidad.IdEmpresaEntidad = Ag_Servicio.IdEmpresaEntidad
    INNER JOIN Ag_CategoriaEmpresa ON Ag_CategoriaEmpresa.IdCategoriaEmpresa = Ag_EmpresasEntidad.IdCategoriaEmpresa
    INNER JOIN Ag_Empresa ON Ag_Empresa.IdEmpresa = Ag_EmpresasEntidad.IdEmpresa
    INNER JOIN Ag_Entidad ON Ag_Entidad.IdEntidad = Ag_EmpresasEntidad.IdEntidad 
    WHERE Ag_Comprobante.TipoOperacion = 2 ";

    if ($filter) {
        $select .= " AND (Ag_Comprobante.IdComprobante LIKE '%" . $filter .
            "%' OR CONCAT('R001', '-', Ag_Comprobante.IdComprobante) LIKE '%" . $filter .
            "%' OR Ve_DocVentaCliente.Cliente LIKE '%" . $filter .
            "%' OR Ve_DocVentaCliente.DniRuc LIKE '%" . $filter .
            "%' )";
    }

    $select .= ")";

    $select .= " UNION ALL ";

    $select .= "(SELECT Ag_Comprobante.*,Ag_Operacion.Operacion AS Servicio,'OPERACION' AS CategoriaEmpresa, Ag_Entidad.Nombre AS Empresa,
    Ag_Entidad.Nombre, NULL AS IdEmpresaEntidad, NULL AS IdEmpresa,Ve_DocVentaCliente.Cliente,Ve_DocVentaCliente.DniRuc, Ag_Operacion.Tipo, CONCAT('R001', '-', Ag_Comprobante.IdComprobante) AS Correlativo  FROM Ag_Comprobante
    INNER JOIN Ve_DocVentaCliente ON Ag_Comprobante.IdCliente = Ve_DocVentaCliente.IdCliente
    INNER JOIN Ag_Operacion ON Ag_Operacion.IdOperacion = Ag_Comprobante.IdServicio 
    INNER JOIN Ag_Entidad ON Ag_Entidad.IdEntidad = Ag_Operacion.IdEntidad
    WHERE Ag_Comprobante.TipoOperacion = 1 ";

    if ($filter) {
        $select .= " AND (Ag_Comprobante.IdComprobante LIKE '%" . $filter .
            "%' OR CONCAT('R001', '-', Ag_Comprobante.IdComprobante) LIKE '%" . $filter .
            "%' OR Ve_DocVentaCliente.Cliente LIKE '%" . $filter .
            "%' OR Ve_DocVentaCliente.DniRuc LIKE '%" . $filter .
            "%' )";
    }

    $select .= ")";


    $select .= $filtros;

    if ($request->getParam('sortBy')) {
        $sortBy = $request->getParam('sortBy');
        $sortDesc = $request->getParam('sortDesc');
        $orientation = $sortDesc ? 'DESC' : 'ASC';
        $select .= " ORDER BY " . $sortBy . " " . $orientation;
    }


    if ($request->getParam('limit')) {
        $limit = $request->getParam('limit');
        $offset = 0;
        if ($request->getParam('page')) {
            $page = $request->getParam('page');
            $offset = (--$page) * $limit;
        }
        $select .= " LIMIT " . $limit;
        $select .= " OFFSET " . $offset;
    }

    // print_r($select);exit();
    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    $select = "SELECT COUNT(*) as total FROM Ag_Comprobante";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $datacount = $stmt->fetch(PDO::FETCH_ASSOC);

    return $response->withJson(array('data' => $data, 'count' => $datacount));
});

$app->post('/producto/registro_proyecto', function (Request $request, Response $response) {
    try {

        $vendedor = 'xx';
        if (isset($_SESSION['User'])) {
            $vendedor = $_SESSION['User'];
        }
        $usuarioReg = $request->getParam('usuario') ? $request->getParam('usuario') : $vendedor;


        $sql = "SELECT MAX(CAST(substring(Producto,10,15) AS SIGNED)) +1 as correlativo FROM `Gen_Producto` where Gen_Producto.EsHabitacion = 1";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $data = $stmt->fetch();
        $producto = 'PROYECTO ' . $data['correlativo'];
        // -------------------------------------------
        $sql = "SELECT MAX(CAST(CodigoBarra AS UNSIGNED)) +1 AS CodigoBarra FROM Gen_Producto WHERE CodigoBarra REGEXP '^[0-9]+$'";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $data = $stmt->fetch();
        $codigoBarra = $data['CodigoBarra'];

        // --------------------------------------------------
        $sql = "SELECT * FROM `Gen_Producto` where Gen_Producto.EsHabitacion = 1 LIMIT 1";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $prodEjemplo = $stmt->fetch();
        // $prodEjemplo = 'PROYECTO ' . $data['correlativo'];
        // ---------------------------------------

        $idProductoMarca = $prodEjemplo['IdProductoMarca'];
        $idProductoFormaFarmaceutica = $prodEjemplo['IdProductoFormaFarmaceutica'];
        $idProductoMedicion = $prodEjemplo['IdProductoMedicion'];
        $idProductoCategoria = $prodEjemplo['IdProductoCategoria'];
        $precioContado = 0;
        $controlaStock = 0;
        $stockMinimo = 0;
        $hash = time();
        $productoModelo = 'S/M';
        $productoPresentacion = 'S/P';
        $esHabitacion = 1;
        $EstadoHabitacion = 3;
        $idCliente = $request->getParam('IdCliente');
        $IdEmpleado = $request->getParam('IdEmpleado');
        $pisoAlm = $request->getParam('pisoAlm') ? $request->getParam('pisoAlm') : 1;
        $posOferta = 1000;
        $opcContable = 0;
        $cliente = $request->getParam('cliente');
        $lugarProcedencia = $request->getParam('cliente')['LugarProcedencia'];
        $medioTransporte = $request->getParam('cliente')['MedioTransporte'];
        $proximoDestino = $request->getParam('cliente')['ProximoDestino'];

        $fechaAlquilerInicio = $request->getParam('FechaAlquilerInicio');
        $fechaAlquilerFin = $request->getParam('FechaAlquilerFin');
        $descripcion = $request->getParam('Descripcion') ? $request->getParam('Descripcion') : '';
        $preciosPorProducto = '[{"nombre":"P.HUANUCO","cantidad":0,"precio":0},{"nombre":"P.PUCALLPA","cantidad":0,"precio":0},{"nombre":"P.ESPECIAL","cantidad":0,"precio":0},{"nombre":"OFERTA","cantidad":0,"precio":0}]';

        $insert = $this->db->insert(array(
            'IdProductoMarca',
            'IdProductoFormaFarmaceutica',
            'IdProductoMedicion',
            'IdProductoCategoria',
            'Producto',
            'CodigoBarra',
            'PrecioContado',
            'FechaReg',
            'Hash',
            'ControlaStock',
            'PrecioCosto',
            'ProductoModelo',
            'ProductoPresentacion',
            'EsPadre',
            'EsHabitacion',
            'Piso',
            'EstadoHabitacion',
            'IdClienteReserva',
            'FechaAlquiler',
            'FechaAlquilerFin',
            'PreciosPorProducto',
            'PosOferta',
            'OpcContable',
            'IdEmpleado',
            'Anulado',
        ))
            ->into('Gen_Producto')
            ->values(array(
                $idProductoMarca,
                $idProductoFormaFarmaceutica,
                $idProductoMedicion,
                $idProductoCategoria,
                $producto,
                $codigoBarra,
                $precioContado,
                getNow(),
                $hash,
                $controlaStock,
                0,
                $productoModelo,
                $productoPresentacion,
                0,
                1,
                $pisoAlm,
                3,
                $idCliente,
                $fechaAlquilerInicio,
                $fechaAlquilerFin,
                $preciosPorProducto,
                10000,
                0,
                $IdEmpleado,
                0
            ));

        $idProducto = $insert->execute();


        // Crear la preorden
        $insert = $this->db->insert(array('IdCliente', 'FechaReg', 'LugarProcedencia', 'MedioTransporte', 'ProximoDestino', 'FechaAlquilerInicio', 'FechaAlquilerFin', 'Descripcion', 'UsuarioReg'))
            ->into('Ve_PreOrden')
            ->values(array($idCliente, getNow(), $lugarProcedencia, $medioTransporte, $proximoDestino, $fechaAlquilerInicio, $fechaAlquilerFin, $descripcion, $usuarioReg));

        $insertId = $insert->execute();

        // Insertar preorden Det
        $insertDet = $this->db->insert(array('IdPreOrden', 'IdProducto', 'Cantidad'))
            ->into('Ve_PreOrdenDet')
            ->values(array($insertId, $idProducto, 1));
        $insertDetId = $insertDet->execute();


        $update = "UPDATE Gen_Producto SET IdPreOrden=$insertId WHERE IdProducto=$idProducto";
        $stmt = $this->db->prepare($update);
        $updated = $stmt->execute();




        return $response->withJson(array('data' => $idProducto));
    } catch (Exception $e) {
        return $response->withJson(array('data' => $e));
    }
});
// SELECT SUBSTRING(Producto,10,15) FROM Gen_Producto WHERE Gen_Producto.EsHabitacion=1 ORDER BY  CAST(SUBSTRING(Producto,10,15) AS INTEGER) desc LIMIT 1
$app->get('/destino/producto/verificar', function (Request $request, Response $response, array $args) use ($app) {
    // return $response->withJson(array(
    //     "producto" => $prod
    // ));

    try {

        $CodigoBarra = $request->getParam('CodigoBarra');
        $select = "SELECT * FROM Gen_Producto
        WHERE CodigoBarra = " . $CodigoBarra . " LIMIT 1";

        $stmt = $this->db_destino->query($select);
        $stmt->execute();
        $prod = $stmt->fetch();

        if (empty($prod)) {
            return $response->withJson(array(
                "producto" => false
            ));
        } else {
            return $response->withJson(array(
                "producto" => $prod
            ));
        }
    } catch (Exception $e) {
        return $response->withJson(array(
            $e
        ));
    }
});
$app->post('/traslado', function (Request $request, Response $response) {
    try {
        //code...

        $serie = "TRAL01";
        $idMovimientoTipo = 6;
        $almacenOrigen = $request->getParam('idAlmacen') ? $request->getParam('idAlmacen') : 1;
        $almacenDestino = $request->getParam('idAlmacenDestino') ? $request->getParam('idAlmacenDestino') : 1;
        $usuarioReg = 'usertraslado';
        $movimientoFecha = getNow();
        $hash = time();
        $tipoCambio = 1;

        if (isset($_SESSION['user'])) {
            $usuarioReg = $_SESSION['user'];
        }

        $usuarioReg = $request->getParam('usuario') ? $request->getParam('usuario') : $usuarioReg;

        //
        // BASE DE DATOS ORIGEN.....
        //

        $select = "SELECT (Lo_Movimiento.Numero+1) as NuevoNumero
        FROM Lo_Movimiento
        WHERE Lo_Movimiento.Serie = '$serie' AND Lo_Movimiento.IdMovimientoTipo = '$idMovimientoTipo'
        ORDER BY Lo_Movimiento.Numero DESC
        LIMIT 1";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetch();
        $numero = $data['NuevoNumero'] ? $data['NuevoNumero'] : 1;

        $insert = $this->db->insert(array(
            'IdMovimientoTipo',
            'IdProveedor',
            'Serie',
            'Numero',
            'MovimientoFecha',
            'IdAlmacenOrigen',
            'IdAlmacenDestino',
            'Anulado',
            'FechaReg',
            'UsuarioReg',
            'Hash',
            'FechaStock',
            'Percepcion',
            'EsCredito',
            'FechaPeriodoTributario',
            'TipoCambio',
            'Moneda'
        ))
            ->into('Lo_Movimiento')
            ->values(array(
                $idMovimientoTipo,
                0,
                $serie,
                $numero,
                getNow(),
                $almacenOrigen,
                -1,
                0,
                getNow(),
                $usuarioReg,
                $hash,
                getNow(),
                0,
                0,
                getNow('Ym'),
                $tipoCambio,
                'PEN'
            ));

        $insertId = $insert->execute();

        // start Movimiento Detalle
        $productos = $request->getParam('productos');
        foreach ($productos as $producto) {
            //if ($producto['total'] > 0) {
            $idProducto = $producto['IdProducto'];
            $cantidad = $producto['cantidad'];
            $tieneIgv = 0;
            $precio = $producto['precio'];
            $idLote = 0;
            $precio = $precio * $tipoCambio;

            $insert = $this->db->insert(array('hashMovimiento', 'IdProducto', 'Cantidad', 'TieneIgv', 'Precio', 'IdLote'))
                ->into('Lo_MovimientoDetalle')
                ->values(array($hash, $idProducto, $cantidad, $tieneIgv, $precio, $idLote));
            $insert->execute();
        }

        //
        // BASE DE DATOS DESTINO ......
        //

        $idMovimientoTipo = 6;
        // $almacenDestino = 1;

        $select = "SELECT (Lo_Movimiento.Numero+1) as NuevoNumero
        FROM Lo_Movimiento
        WHERE Lo_Movimiento.Serie = '$serie' AND Lo_Movimiento.IdMovimientoTipo = '$idMovimientoTipo'
        ORDER BY Lo_Movimiento.Numero DESC
        LIMIT 1";
        $stmt = $this->db_destino->query($select);
        $stmt->execute();
        $data = $stmt->fetch();
        $numero = $data['NuevoNumero'] ? $data['NuevoNumero'] : 1;

        $insert = $this->db_destino->insert(array(
            'IdMovimientoTipo',
            'IdProveedor',
            'Serie',
            'Numero',
            'MovimientoFecha',
            'IdAlmacenOrigen',
            'IdAlmacenDestino',
            'Anulado',
            'FechaReg',
            'UsuarioReg',
            'Hash',
            'FechaStock',
            'Percepcion',
            'EsCredito',
            'FechaPeriodoTributario',
            'TipoCambio',
            'Moneda'
        ))
            ->into('Lo_Movimiento')
            ->values(array(
                $idMovimientoTipo,
                0,
                $serie,
                $numero,
                getNow(),
                -1,
                $almacenDestino,
                0,
                getNow(),
                $usuarioReg,
                $hash,
                getNow(),
                0,
                0,
                getNow('Ym'),
                $tipoCambio,
                'PEN'
            ));

        $insertId = $insert->execute();

        // start Movimiento Detalle
        $productos = $request->getParam('productos');
        foreach ($productos as $producto) {
            //if ($producto['total'] > 0) {
            $idProducto = $producto['IdProducto'];
            $cantidad = $producto['cantidad'];
            $tieneIgv = 0;
            $precio = $producto['precio'];
            $idLote = 0;
            $precio = $precio * $tipoCambio;
            $precioCosto = $producto['precio'];
            $nuevoPrecioContado = $producto['nuevoPrecioContado'];
            $hashPro = time();


            // 
            // Obtener el verdadero ID del producto 
            // en la base de datos de destino
            //
            $select = "SELECT * FROM Gen_Producto
                        WHERE CodigoBarra = '" . $producto['CodigoBarra'] . "' LIMIT 1";


            $stmt = $this->db_destino->query($select);
            $stmt->execute();
            $prod = $stmt->fetch();

            if (empty($prod)) {

                //Script de inserciones
                $selectMarca = "SELECT IdProductoMarca FROM Gen_ProductoMarca WHERE ProductoMarca = '" . $producto['ProductoMarca'] . "' LIMIT 1";
                $stmt = $this->db_destino->query($selectMarca);
                $stmt->execute();
                $marc = $stmt->fetch();

                if (empty($marc)) {
                    //insert si no existe
                    $insert = $this->db_destino->insert(array('ProductoMarca', 'Anulado', 'FechaReg', 'UsuarioReg'))
                        ->into('Gen_ProductoMarca')
                        ->values(array($producto['ProductoMarca'], 0, getNow(), $usuarioReg));
                    $idMarc = $insert->execute();
                } else {
                    $idMarc = $marc['IdProductoMarca'];
                }

                $selectCateg = "SELECT IdProductoCategoria FROM Gen_ProductoCategoria WHERE ProductoCategoria = '" . $producto['ProductoCategoria'] . "' LIMIT 1";
                $stmt = $this->db_destino->query($selectCateg);
                $stmt->execute();
                $cate = $stmt->fetch();

                if (empty($cate)) {
                    $insert = $this->db_destino->insert(array('ProductoCategoria', 'Anulado', 'FechaReg', 'UsuarioReg'))
                        ->into('Gen_ProductoCategoria')
                        ->values(array($producto['ProductoCategoria'], 0, getNow(), $usuarioReg));
                    $idCat = $insert->execute();
                } else {

                    $idCat =  $cate['IdProductoCategoria'];
                }

                $selectFormaF = "SELECT IdProductoFormaFarmaceutica FROM Gen_ProductoFormaFarmaceutica WHERE ProductoFormaFarmaceutica = '" . $producto['ProductoFormaFarmaceutica'] . "' LIMIT 1";
                $stmt = $this->db_destino->query($selectFormaF);
                $stmt->execute();
                $formaf = $stmt->fetch();

                if (empty($formaf)) {
                    $insert = $this->db_destino->insert(array('ProductoFormaFarmaceutica', 'Anulado', 'FechaReg', 'UsuarioReg'))
                        ->into('Gen_ProductoFormaFarmaceutica')
                        ->values(array($producto['ProductoFormaFarmaceutica'], 0, getNow(), $usuarioReg));
                    $idFormF = $insert->execute();
                } else {
                    $idFormF = $formaf['IdProductoFormaFarmaceutica'];
                }

                $selectMed = "SELECT IdProductoMedicion FROM Gen_ProductoMedicion WHERE ProductoMedicion = '" . $producto['ProductoMedicion'] . "' LIMIT 1";
                $stmt = $this->db_destino->query($selectMed);
                $stmt->execute();
                $medi = $stmt->fetch();

                if (empty($medi)) {
                    $insert = $this->db_destino->insert(array('ProductoMedicion', 'Anulado', 'FechaReg', 'UsuarioReg'))
                        ->into('Gen_ProductoMedicion')
                        ->values(array($producto['ProductoMedicion'], 0, getNow(), $usuarioReg));
                    $idMedi = $insert->execute();
                } else {
                    $idMedi = $medi['IdProductoMedicion'];
                }

                //Insertamos nuevo producto en la DB destino
                $insert = $this->db_destino->insert(array(
                    'IdProductoMarca',
                    'IdProductoFormaFarmaceutica',
                    'IdProductoMedicion',
                    'IdProductoCategoria',
                    'Producto',
                    'FechaReg',
                    'ControlaStock',
                    'PorcentajeUtilidad',
                    'PrecioCosto',
                    'PrecioContado',
                    'Hash',
                    'Anulado',
                    'EsPadre',
                    'StockMinimo',
                    'CodigoBarra'
                ))
                    ->into('Gen_Producto')
                    ->values(array(
                        $idMarc,
                        $idFormF,
                        $idMedi,
                        $idCat,
                        $producto['Producto'],
                        getNow(),
                        1,
                        $producto['PorcentajeUtilidad'],
                        $precio,
                        $precioContado,
                        $hashPro,
                        0,
                        0,
                        0,
                        $producto['CodigoBarra']
                    ));
                $idProducto = $insert->execute();
            } else {
                $idProducto = $prod['IdProducto'];
                //$precio = $prod['PrecioCosto'];

                //UPDATE PRECIOS
                $update = $this->db_destino->update(array(
                    "PrecioCosto" =>  floatval($precioCosto),
                    "PrecioContado" =>  floatval($nuevoPrecioContado),
                ))
                    ->table('Gen_Producto')
                    ->where('IdProducto', '=', $idProducto);
                $affectedRows = $update->execute();
            }

            //$precio = floatval($precio);
            $precioCosto = floatval($precioCosto);
            $insert = $this->db_destino->insert(array('hashMovimiento', 'IdProducto', 'Cantidad', 'TieneIgv', 'Precio', 'IdLote'))
                ->into('Lo_MovimientoDetalle')
                ->values(array(
                    $hash,
                    $idProducto,
                    $cantidad,
                    $tieneIgv,
                    $precioCosto,
                    $idLote
                ));
            $insert->execute();
        }

        $data = array(
            "hash" => $hash
        );



        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson($e->getMessage());
    }
});



$app->get('/ofertas', function (Request $request, Response $response, array $args) {
    $idProducto = $request->getParam('idProducto');

    $select = "SELECT 
    Gen_Oferta.*,
    Gen_Producto.Producto,
    Gen_ProductoAsociado.Producto ProductoAsociado
    FROM Gen_Oferta
    JOIN Gen_Producto Gen_ProductoAsociado ON Gen_ProductoAsociado.IdProducto = Gen_Oferta.IdProductoAsociado
    LEFT JOIN Gen_Producto ON Gen_Producto.IdProducto = Gen_Oferta.IdProducto
    ORDER BY Gen_Oferta.Estado DESC,Gen_Oferta.IdOferta DESC

    ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->post('/ofertas/guardar', function (Request $request, Response $response, array $args) {
    try {
        // Obtener los parámetros del request
        $ProductoFlag = $request->getParam('ProductoFlag');
        $MontoFlag = $request->getParam('MontoFlag');
        $CantidadFlag = $request->getParam('CantidadFlag');
        $IdProducto = $request->getParam('IdProducto');
        $Monto = $request->getParam('Monto');
        $Cantidad = $request->getParam('Cantidad');
        $Descripcion = $request->getParam('Descripcion');
        $Porcentaje = $request->getParam('Porcentaje');
        $FechaDesde = $request->getParam('FechaDesde');
        $FechaHasta = $request->getParam('FechaHasta');
        $StockMax = $request->getParam('StockMax');
        $IdProductoAsociado  = $request->getParam('IdProductoAsociado');

        $IdProducto = ($IdProducto === '' || $Monto === null) ? null : (float) $IdProducto;
        $Monto = ($Monto === '' || $Monto === null) ? null : (float) $Monto;
        $Porcentaje = ($Porcentaje === '' || $Porcentaje === null) ? null : (float) $Porcentaje;
        $Cantidad = ($Cantidad === '' || $Cantidad === null) ? null : (float) $Cantidad;

        $hash = time();

        $insert = $this->db->insert([
            'Hash',
            'ProductoFlag',
            'MontoFlag',
            'CantidadFlag',
            'IdProducto',
            'Monto',
            'Cantidad',
            'Descripcion',
            'Porcentaje',
            'FechaDesde',
            'FechaHasta',
            'StockMax',
            'IdProductoAsociado',
            'Estado'
        ])
            ->into('Gen_Oferta')
            ->values([
                $hash,
                $ProductoFlag,
                $MontoFlag,
                $CantidadFlag,
                $IdProducto,
                $Monto,
                $Cantidad,
                $Descripcion,
                $Porcentaje,
                $FechaDesde,
                $FechaHasta,
                $StockMax,
                $IdProductoAsociado,
                1
            ]);

        $insertId = $insert->execute();

        return $response->withJson(["insertId" => $insertId]);
    } catch (Exception $e) {
        return $response->withJson(["error" => $e->getMessage(), "code" => '300']);
    }
});


$app->put('/ofertas/actualizar', function (Request $request, Response $response, array $args) {


    try {
        $ProductoFlag = $request->getParam('ProductoFlag');
        $MontoFlag = $request->getParam('MontoFlag');
        $CantidadFlag = $request->getParam('CantidadFlag');
        $IdProducto = $request->getParam('IdProducto');
        $Monto = $request->getParam('Monto');
        $Cantidad = $request->getParam('Cantidad');
        $Descripcion = $request->getParam('Descripcion');
        $Porcentaje = $request->getParam('Porcentaje');
        $FechaDesde = $request->getParam('FechaDesde');
        $FechaHasta = $request->getParam('FechaHasta');
        $StockMax = $request->getParam('StockMax');
        $IdOferta = $request->getParam('IdOferta');

        $IdProducto = ($IdProducto === '' || $Monto === null) ? null : (float) $IdProducto;
        $Monto = ($Monto === '' || $Monto === null) ? null : (float) $Monto;
        $Cantidad = ($Cantidad === '' || $Cantidad === null) ? null : (float) $Cantidad;

        // return $response->withJson($IdOferta);
        $update = $this->db->update(array(
            'ProductoFlag' => $ProductoFlag,
            'MontoFlag' => $MontoFlag,
            'CantidadFlag' => $CantidadFlag,
            'IdProducto' => $IdProducto,
            'Monto' => $Monto,
            'Cantidad' => $Cantidad,
            'Descripcion' => $Descripcion,
            'Porcentaje' => $Porcentaje,
            'FechaDesde' => $FechaDesde,
            'FechaHasta' => $FechaHasta,
            'StockMax' => $StockMax
        ))
            ->table('Gen_Oferta')
            ->where('IdOferta', '=', $IdOferta);
        $affectedRows = $update->execute();

        return $response->withJson(array("affectedRows" => $affectedRows));
    } catch (Exception $e) {
        return $response->withJson($e->getMessage());
    }
});

$app->put('/ofertas/eliminar', function (Request $request, Response $response, array $args) {

    try {
        $IdOferta = $request->getParam('IdOferta');


        $update = $this->db->update(array(
            'Estado' => 0
        ))
            ->table('Gen_Oferta')
            ->where('IdOferta', '=', $IdOferta);
        $affectedRows = $update->execute();

        return $response->withJson(array("affectedRows" => $affectedRows));
    } catch (Exception $e) {
        return $response->withJson(array("error" => $e->getMessage(), code => '300'));
    }
});


$app->get('/ofertas/listadoimpresion', function (Request $request, Response $response, array $args) {
    $idProducto = $request->getParam('idProducto');

    $select = "SELECT 
    Gen_Oferta.*, Gen_ProductoAsociado.Producto
   FROM Gen_Oferta
    JOIN Gen_Producto Gen_ProductoAsociado ON Gen_ProductoAsociado.IdProducto = Gen_Oferta.IdProductoAsociado
    WHERE Gen_Oferta.Estado = 1 AND now() BETWEEN Gen_Oferta.FechaDesde AND Gen_Oferta.FechaHasta 
    ORDER BY Gen_Oferta.Estado DESC,Gen_Oferta.IdOferta DESC

    ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/reporte/utlidadbruta/usuario', function (Request $request, Response $response, array $args) {
    try {


        $fechaInicio = $request->getParam('fechaInicio');
        $fechaFin = $request->getParam('fechaFin');
        $usuarioReg = $request->getParam('usuarioReg');
        $ServicioFlag  = $request->getParam('ServicioFlag');
        $MercaderiaFlag  = $request->getParam('MercaderiaFlag');

        // print_r($MercaderiaFlag);
        $select = "SELECT
                    Ve_DocVenta.UsuarioReg,
                    Gen_Producto.IdProducto,
                    Gen_Producto.Producto,
                    ROUND(SUM(Ve_DocVentaDet.Cantidad),2) AS Cantidad,
                    Ve_DocVenta.FechaReg,
                    ROUND(SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto))/SUM(Ve_DocVentaDet.Cantidad),2) AS PrecioCosto,
                    ROUND(SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio - IFNULL(Ve_DocVentaDet.Descuento,0))/SUM(Ve_DocVentaDet.Cantidad), 2)  AS PrecioVenta,
                    ROUND(SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio - IFNULL(Ve_DocVentaDet.Descuento,0)), 2) AS TotalVenta,
                    ROUND(SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto)), 2) AS TotalCosto,
                    ROUND(SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio - IFNULL(Ve_DocVentaDet.Descuento,0))  -  SUM(Ve_DocVentaDet.Cantidad * IFNULL(Ve_DocVentaDet.PrecioCosto, Gen_Producto.PrecioCosto)), 2) AS UtilidadBruta
                    FROM Ve_DocVenta
                    INNER JOIN Ve_DocVentaDet ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta
                    INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
                    WHERE 
                    true ";


        // if ($ServicioFlag == 1 && $MercaderiaFlag == 0) {
        //     $select .= " AND Gen_Producto.OpcContable=1";
        // }
        // if ($ServicioFlag == 0 && $MercaderiaFlag == 1) {
        //     $select .= " AND Gen_Producto.OpcContable=0";
        // }




        $select .= " AND Ve_DocVenta.Anulado=0 AND Ve_DocVenta.IdTipoDoc <> 11 
                AND Date(Ve_DocVenta.FechaDoc) BETWEEN '" . $fechaInicio . "' AND '" . $fechaFin . "'
                AND Ve_DocVenta.UsuarioReg like '$usuarioReg'
                GROUP BY Gen_Producto.IdProducto
                ORDER BY Ve_DocVenta.FechaReg desc
        ";
        // return $response->withJson($select);
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Excpetion $e) {

        return $response->withJson($e->getMessage());
    }
});

$app->get('/reporte/utlidadbruta/usuario/exportar', function (Request $request, Response $response, array $args) use ($app) {
    $fechaInicio = $request->getParam('fechaInicio');
    $fechaFin = $request->getParam('fechaFin');
    $usuarioReg = $request->getParam('usuarioReg');
    $ServicioFlag  = $request->getParam('ServicioFlag');
    $MercaderiaFlag  = $request->getParam('MercaderiaFlag');
    try {
        // Llama a la subruta
        $params = http_build_query([
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'usuarioReg' => $usuarioReg,
            'ServicioFlag' => $ServicioFlag,
            'MercaderiaFlag' => $MercaderiaFlag,
        ]);
        $res = $app->subRequest('GET', '/reporte/utlidadbruta/usuario', $params); // Usa comillas simples o dobles
        $res = (string) $res->getBody();
        $res = json_decode($res, true);

        // Prepara los datos
        $data = array(
            'txtTIPO_PROCESO' => '03',
            'detalle' => $res,
            'txtNRO_DOCUMENTO_EMPRESA' => '20573027125',
            'filtro' => [
                'desde' => $fechaInicio,
                'hasta' => $fechaFin,
                'IdUsuario' => $usuarioReg,
                'ServicioFlag' => $ServicioFlag,
                'MercaderiaFlag' => $MercaderiaFlag,
            ],
        );
        // return $response->withJson($data);
        $rutapdf = $_SERVER['HTTP_HOST'] . '/api/sunat/plugins/dompdf/';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $rutapdf . "ReporteUtilidadBrutaUsuario.php",
            CURLOPT_USERAGENT => "Codular Sample cURL Request",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));

        // Envía la solicitud y cierra
        $resp = curl_exec($curl);
        curl_close($curl);

        // Ruta del archivo generado
        $urlArchivo = $_SERVER['HTTP_HOST']  . '/api/sunat/api_cpe/PRODUCCION/' . '20573027125' . '/ReporteProduccion.pdf';
        $urlArchivo = stripslashes($urlArchivo);

        return $response->withJson($urlArchivo);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()]);
    }
});

$app->post('/tipocambio/usd', function (Request $request, Response $response) {
    try {
        //code...

        $tipoCambio = $request->getParam('tipoCambio');

        if ($tipoCambio > 0) {
            $update = "UPDATE Gen_Moneda SET TipoCambio=$tipoCambio WHERE Moneda = 'USD'";
            $stmt = $this->db->prepare($update);
            $updated = $stmt->execute();


            $update = " UPDATE Gen_Producto 
                        SET
                            PrecioCosto = PrecioCostoDolar * $tipoCambio,
                            PrecioContado = PrecioContadoDolar * $tipoCambio,  -- Se corrigió el nombre de la columna
                            TipoCambio = $tipoCambio,
                            FechaMod = NOW()
                        WHERE TipoCambio IS NULL OR TipoCambio <> 1;

       ";
            $stmt = $this->db->prepare($update);
            $updated = $stmt->execute();
            return $response->withJson(true);
        } else {
            return $response->withJson(false);
        }
    } catch (Exception $e) {
        return $response->withJson($e->getMessage());
    }
});

$app->get('/tipocambio/usd', function (Request $request, Response $response) {
    $select = "SELECT * FROM Gen_Moneda WHERE Moneda = 'USD'";
    $stmt = $this->db->query($select);
    $prod = $stmt->fetch();
    $stmt->execute();

    if (!empty($prod)) {
        return $response->withJson($prod['TipoCambio']);
    } else {
        return $response->withJson(0);
    }
});
$app->get('/ventas/ofertas', function (Request $request, Response $response) {
    try {


        $IdOferta = $request->getParam('IdOferta');
        $IdCliente = $request->getParam('IdCliente');

        $select = "SELECT * FROM Gen_Oferta WHERE IdOferta=$IdOferta";
        $stmt = $this->db->query($select);
        $oferta =  $stmt->fetch();
        $stmt->execute();
        $FechaDesde = $oferta['FechaDesde'];
        $FechaHasta = $oferta['FechaHasta'];



        $select = "SELECT 
                        Ve_DocVenta.*,
                        CONCAT(Ve_DocVenta.Serie, '-', Ve_DocVenta.Numero) AS NroDocRef,
                        ROUND((SELECT SUM(Cantidad * Precio - Descuento) 
                            FROM Ve_DocVentaDet 
                            WHERE Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.IdDocVenta), 2) AS Total,
                        (SELECT GROUP_CONCAT(IdProducto) 
                            FROM Ve_DocVentaDet  
                            WHERE Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.IdDocVenta) AS IdProductos
                    FROM Ve_DocVenta
                    WHERE DATE(FechaReg) BETWEEN '$FechaDesde' AND '$FechaHasta' AND IdOferta IS NULL AND EntregadoOfertaFlag = 0 AND Ve_DocVenta.IdCliente=$IdCliente";
        $stmt = $this->db->query($select);
        $ventas = $stmt->fetchAll();
        $stmt->execute();

        if (!empty($ventas)) {
            return $response->withJson($ventas);
        } else {
            return $response->withJson(false);
        }
    } catch (Exception $e) {
        return $response->withJson($e->getMessage());
    }
});
$app->post('/cajaybanco/gasto', function (Request $request, Response $response, array $args) {
    try {
        $idPreOrden = $request->getParam('IdPreOrden', null);
        $idHabitacion = $request->getParam('IdHabitacion');
        $gasto = $request->getParam('Gasto');
        $totalGasto = $request->getParam('TotalGasto');
        $producto = $request->getParam('producto');
        $concepto = $request->getParam('concepto');
        $usuario = $request->getParam('Usuario');
        $usuario = $request->getParam('Usuario');
        $idAlmacen = $request->getParam('idAlmacen');

        $vendedor = 'xx';
        if (isset($_SESSION['User'])) {
            $vendedor = $_SESSION['User'];
        }
        $usuarioReg = isset($usuario) ? $request->getParam('Usuario') : $vendedor;
        $idProducto = $producto['IdProducto'];
        // if ($idPreOrden) {
        $insert = "INSERT INTO 
        Cb_CajaBanco (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, Anulado,  EsDelVendedor,  UsuarioReg,IdProducto) 
        VALUES (3, 1, '" . getNow() . "', '$concepto', $totalGasto, 0,  1, '$usuarioReg','$idProducto')";

        $stmt = $this->db->prepare($insert);
        $inserted = $stmt->execute();
        $idCajaBanco = $this->db->lastInsertId();


        $selectNumero = "SELECT coalesce(numero+1,1)as numero FROM Lo_Movimiento WHERE Serie = 'CHATARRA' ORDER BY numero DESC LIMIT 1";

        $stmt = $this->db->query($selectNumero);
        $stmt->execute();
        $selectNumero = $stmt->fetch();

        $idMovimientoTipo = 4;
        $idProveedor = 16;
        $serie = 'CHATARRA';
        $numero = $selectNumero ? $selectNumero['numero'] : 1;
        $movimientoFecha = getNow();
        $almacenOrigen = -1;
        $almacenDestino = $idAlmacen;
        $fechaReg = getNow();
        $observacion = $request->getParam('concepto');

        $hash = time();
        $fechaStock = getNow();
        $percepcion = 0;
        $fechaPeriodoTributario = date('Ym');
        $tipoCambio = 1;
        $moneda = 'PEN';


        $insert = $this->db->insert(array(
            'IdMovimientoTipo',
            'IdProveedor',
            'Serie',
            'Numero',
            'MovimientoFecha',
            'IdAlmacenOrigen',
            'IdAlmacenDestino',
            'Anulado',
            'FechaReg',
            'UsuarioReg',
            'Hash',
            'FechaStock',
            'Percepcion',
            'EsCredito',
            'FechaVenCredito',
            'FechaPeriodoTributario',
            'TipoCambio',
            'Moneda'
        ))
            ->into('Lo_Movimiento')
            ->values(array(
                $idMovimientoTipo,
                $idProveedor,
                $serie,
                $numero,
                $movimientoFecha,
                $almacenOrigen,
                $almacenDestino,
                0,
                $fechaReg,
                $usuarioReg,
                $hash,
                $fechaStock,
                $percepcion,
                0,
                null,
                $fechaPeriodoTributario,
                $tipoCambio,
                $moneda
            ));

        $insertId = $insert->execute();
        $data = array(
            "hash" => $hash
        );

        $insert = $this->db->insert(array('hashMovimiento', 'IdProducto', 'Cantidad', 'TieneIgv', 'Precio', 'IdLote', 'Descripcion'))
            ->into('Lo_MovimientoDetalle')
            ->values(array($hash, $producto['IdProducto'], $producto['Cantidad'], 0, $producto['Gasto'], 0, $producto['GastoDesc']));
        $insert->execute();




        if ($idCajaBanco) {
            $select = "SELECT * FROM Cb_CajaBanco where IdCajaBanco=$idCajaBanco";

            $stmt = $this->db->query($select);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $response->withJson($data);
        }
        // }else{

        // }
    } catch (Exception $e) {
        return $response->withJson($e->getMessage());
    }
});
$app->post('/cajaybanco/ingreso', function (Request $request, Response $response, array $args) {
    try {
        $idPreOrden = $request->getParam('IdPreOrden', null);
        $idHabitacion = $request->getParam('IdHabitacion');
        $ingreso = $request->getParam('Ingreso');
        $producto = $request->getParam('producto');
        $concepto = $request->getParam('concepto');
        $usuario = $request->getParam('Usuario');

        $vendedor = 'xx';
        if (isset($_SESSION['User'])) {
            $vendedor = $_SESSION['User'];
        }
        $usuarioReg = isset($usuario) ? $request->getParam('Usuario') : $vendedor;

        // if ($idPreOrden) {
        $insert = "INSERT INTO 
        Cb_CajaBanco (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, Anulado,  EsDelVendedor,  UsuarioReg) 
        VALUES (4, 1, '" . getNow() . "', '$concepto', $ingreso, 0,  1, '$usuarioReg')";

        $stmt = $this->db->prepare($insert);
        $inserted = $stmt->execute();
        $idCajaBanco = $this->db->lastInsertId();
        if ($idCajaBanco) {
            $select = "SELECT * FROM Cb_CajaBanco where IdCajaBanco=$idCajaBanco";

            $stmt = $this->db->query($select);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $response->withJson($data);
        }
        // }else{

        // }
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});

$app->post('/cajaybanco/salida', function (Request $request, Response $response, array $args) {
    try {
        $idPreOrden = $request->getParam('IdPreOrden', null);
        $idHabitacion = $request->getParam('IdHabitacion');
        $salida = $request->getParam('Salida');
        $producto = $request->getParam('producto');
        $concepto = $request->getParam('concepto');
        $usuario = $request->getParam('Usuario');

        $vendedor = 'xx';
        if (isset($_SESSION['User'])) {
            $vendedor = $_SESSION['User'];
        }
        $usuarioReg = isset($usuario) ? $request->getParam('Usuario') : $vendedor;

        // if ($idPreOrden) {
        $insert = "INSERT INTO 
        Cb_CajaBanco (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, Anulado,  EsDelVendedor,  UsuarioReg) 
        VALUES (3, 1, '" . getNow() . "', '$concepto', $salida, 0,  1, '$usuarioReg')";

        $stmt = $this->db->prepare($insert);
        $inserted = $stmt->execute();
        $idCajaBanco = $this->db->lastInsertId();
        if ($idCajaBanco) {
            $select = "SELECT * FROM Cb_CajaBanco where IdCajaBanco=$idCajaBanco";

            $stmt = $this->db->query($select);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $response->withJson($data);
        }
        // }else{

        // }
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});

$app->post('/cajaybanco/deposito', function (Request $request, Response $response, array $args) {
    try {
        $idPreOrden = $request->getParam('IdPreOrden', null);
        $idHabitacion = $request->getParam('IdHabitacion');
        $deposito = $request->getParam('Deposito');
        $producto = $request->getParam('producto');
        $concepto = $request->getParam('concepto');
        $usuario = $request->getParam('Usuario');

        $vendedor = 'xx';
        if (isset($_SESSION['User'])) {
            $vendedor = $_SESSION['User'];
        }
        $usuarioReg = isset($usuario) ? $request->getParam('Usuario') : $vendedor;

        // if ($idPreOrden) {
        $insert = "INSERT INTO 
        Cb_CajaBanco (IdTipoCajaBanco, IdCuenta, FechaDoc, Concepto, Importe, Anulado,  EsDelVendedor,  UsuarioReg) 
        VALUES (3, 1, '" . getNow() . "', '$concepto', $deposito, 0,  1, '$usuarioReg')";

        $stmt = $this->db->prepare($insert);
        $inserted = $stmt->execute();
        $idCajaBanco = $this->db->lastInsertId();
        if ($idCajaBanco) {
            $select = "SELECT * FROM Cb_CajaBanco where IdCajaBanco=$idCajaBanco";

            $stmt = $this->db->query($select);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $response->withJson($data);
        }
        // }else{

        // }
    } catch (Exception $e) {
        return $response->withJson($e);
    }
});

$app->get('/enviarproformapdf', function (Request $request, Response $response) use ($app) {
    try {
        $IdProforma = $request->getParam('IdProforma');
        $res = $app->subRequest('GET', 'proformas/id/' . $IdProforma);
        $res = (string) $res->getBody();
        $res = json_decode($res, true);

        $data = array(
            'txtTIPO_PROCESO' => '03',
            'detalle' => $res,
            'txtNRO_DOCUMENTO_EMPRESA' => NRO_DOCUMENTO_EMPRESA,
        );

        // return $response->withJson(json_encode($data));
        $rutapdf = $_SERVER['HTTP_HOST'] . '/api/sunat/plugins/dompdf/';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $rutapdf . "ImprimirProforma.php",
            CURLOPT_USERAGENT => "Codular Sample cURL Request",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));


        $resp = curl_exec($curl);
        curl_close($curl);


        $fichero = 'Proforma_' . $res['Anio'] . '-' . $res['Numero'];
        // Ruta del archivo generado
        $urlArchivo = $_SERVER['HTTP_HOST']  . '/api/sunat/api_cpe/PRODUCCION/' . NRO_DOCUMENTO_EMPRESA . '/' . $fichero . '.pdf';
        $urlArchivo = stripslashes($urlArchivo);

        return $response->withJson($urlArchivo);
    } catch (Exception $e) {
        return $response->withJson(["error" => $e->getMessage()], 500);
    }
});

$app->get('/Ve_DocVentaHistorialKit', function (Request $request, Response $response) {
    try {
        $fechaDesde = $request->getParam('fechaDesde');
        $fechaHasta = $request->getParam('fechaHasta');
        $select = "SELECT
            Ve_DocVentaCliente.Cliente
            , Ve_DocVentaCliente.DniRuc
            , Ve_DocVenta.Numero
            , Ve_DocVenta.Serie
            , Ve_DocVenta.FechaDoc
            , Ve_DocVenta.UsuarioReg
            , Ve_DocVenta.FechaReg
            , Ve_DocVentaHistorialKit.Cantidad
            , Ve_DocVentaHistorialKit.Precio
            , Ve_DocVentaHistorialKit.Descuento
            , Gen_Producto.Producto
        FROM
            Ve_DocVenta
            INNER JOIN Ve_DocVentaHistorialKit ON (Ve_DocVenta.idDocVenta = Ve_DocVentaHistorialKit.IdDocVenta)
            INNER JOIN Ve_DocVentaCliente ON (Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente)
            INNER JOIN Gen_Producto ON (Ve_DocVentaHistorialKit.IdProducto = Gen_Producto.IdProducto)
            WHERE DATE(Ve_DocVenta.FechaDoc) BETWEEN '$fechaDesde' AND '$fechaHasta'
            
            ORDER BY Ve_DocVenta.FechaReg DESC
            ;";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(["error" => $e->getMessage()], 500);
    }
});

$app->get('/reporte/Ve_DocVentaHistorialKit', function (Request $request, Response $response, array $args) use ($app) {
    try {

        $headerStyle = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'ADD8E6'] // Azul claro (LightBlue)
            ],
            'font' => [
                'bold' => true
            ]
        ];



        $fechaDesde = $request->getParam('fechaDesde');
        $fechaHasta = $request->getParam('fechaHasta');

        $res = $app->subRequest('GET', 'Ve_DocVentaHistorialKit', 'fechaDesde=' . $fechaDesde . '&fechaHasta=' . $fechaHasta);

        $reporte = (string) $res->getBody();
        $reporte = json_decode($reporte, true);

        $excel = new Spreadsheet();
        $sheet = $excel->getActiveSheet();
        $cont = 1;
        $sheet->setCellValue('A' . $cont, 'DniRuc');
        $sheet->setCellValue('B' . $cont, 'Cliente');
        $sheet->setCellValue('C' . $cont, 'Serie');
        $sheet->setCellValue('D' . $cont, 'Numero');
        $sheet->setCellValue('E' . $cont, 'Producto');
        $sheet->setCellValue('F' . $cont, 'UsuarioReg');
        $sheet->setCellValue('G' . $cont, 'Cantidad');
        $sheet->setCellValue('H' . $cont, 'Precio');
        $sheet->setCellValue('I' . $cont, 'Descuento');
        $cont++;
        foreach ($reporte as $prod) {
            $sheet->setCellValue('A' . $cont, $prod['DniRuc']);
            $sheet->setCellValue('B' . $cont, $prod['Cliente']);
            $sheet->setCellValue('C' . $cont, $prod['Serie']);
            $sheet->setCellValue('D' . $cont, $prod['Numero']);
            $sheet->setCellValue('E' . $cont, $prod['Producto']);
            $sheet->setCellValue('F' . $cont, $prod['UsuarioReg']);
            $sheet->setCellValue('G' . $cont, $prod['Cantidad']);
            $sheet->setCellValue('H' . $cont, $prod['Precio']);
            $sheet->setCellValue('I' . $cont, $prod['Descuento']);
            $cont++;
        }

        foreach (range('A', 'I') as $columnID) {
            $excel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }


        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

        $excelWriter = new Xlsx($excel);

        $fileName = 'HistorialKit' . getNow('Y-m-d-H-i-s') .  '.xlsx';
        $excelFileName = __DIR__ . '/reporte/' . $fileName;
        $excelWriter->save($excelFileName);

        return $response->withJson(['url' =>  $_SERVER['HTTP_HOST']  .  "/api/reporte/" . $fileName]);
    } catch (Exception $e) {
        return $response->withJson(["error" => $e->getMessage()], 500);
    }
});

$app->get('/productos/kits', function (Request $request, Response $response, array $args) {
    try {
        $q = $request->getParam('q');
        $sql = "SELECT * FROM 
                Gen_Producto 
                WHERE Gen_Producto.EsPadre";
        if ($q) {
            $sql .= " AND Gen_Producto.Producto LIKE '%" . $q . "%'";
        }
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Asegura que sea un array asociativo

        foreach ($data as $key => $producto) { // Usa el índice para modificar el array directamente
            $sql = "SELECT * FROM 
            Gen_ProductoDet 
            JOIN Gen_Producto ON Gen_Producto.IdProducto = Gen_ProductoDet.IdProductoDet 
            WHERE Gen_ProductoDet.IdProducto =" . $producto['IdProducto'];

            $stmt = $this->db->query($sql);
            $stmt->execute();
            $detalle = $stmt->fetchAll(PDO::FETCH_ASSOC); // Asegura que sea un array

            $data[$key]['detalle'] = $detalle; // Modifica el array original
        }

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(["error" => $e->getMessage()], 500);
    }
});

$app->get('/imprimirpdfreciboservicio/{id}', function (Request $request, Response $response, array $args) use ($app) {


    try {


        $id = (int) $args['id'];


        $Ssql = "SELECT 
            Gen_Producto.*, 
            Gen_ProductoCategoria.ProductoCategoria, 
            Gen_ProductoMarca.ProductoMarca,
            Gen_ProductoMedicion.ProductoMedicion, 
            Ve_DocVentaCliente.DniRuc, 
            Ve_DocVentaCliente.Cliente, 
            Ve_DocVentaCliente.IdCliente,
            (IFNULL(Ve_PreOrdenTotal.granTotal,0) - IFNULL(Cb_CajaBancoAdelanto.sumAdelanto,0)) AS granTotalCalculado,
            IFNULL(Ve_PreOrdenTotal.granTotal,0) AS TotalServicio,
            IFNULL(Cb_CajaBancoAdelanto.sumAdelanto,0) AS TotalAdelantos,
            GREATEST(IFNULL(Ve_PreOrdenTotal.granTotal,0)-IFNULL(Cb_CajaBancoAdelanto.sumAdelanto,0),0) AS SaldoPendiente,
            Ve_PreOrden.IdProforma, 
            Ve_ProformaTotal.TotalProf, 
            Ve_PreOrden.Descripcion
        FROM 
            Gen_Producto
        INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
        INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
        INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
        LEFT JOIN Ve_PreOrden ON Gen_Producto.IdPreOrden = Ve_PreOrden.IdPreOrden
        LEFT JOIN (
            SELECT 
                Ve_Proforma.IdProforma, 
                SUM(Ve_ProformaDet.Cantidad * Ve_ProformaDet.Precio) AS TotalProf
            FROM 
                Ve_Proforma
            INNER JOIN Ve_ProformaDet ON Ve_Proforma.IdProforma = Ve_ProformaDet.IdProforma
            GROUP BY Ve_Proforma.IdProforma
        ) Ve_ProformaTotal ON Ve_ProformaTotal.IdProforma = Ve_PreOrden.IdProforma 
        LEFT JOIN Ve_DocVentaCliente ON Gen_Producto.IdClienteReserva = Ve_DocVentaCliente.IdCliente
        LEFT JOIN (
            SELECT 
                Ve_PreOrdenDet.IdPreOrden, 
                SUM(Ve_PreOrdenDet.Cantidad * Ve_PreOrdenDet.Precio * IF(Ve_PreOrdenDet.EsManoDeObra=1,0,1)) AS granTotal 
            FROM 
                Ve_PreOrdenDet
            GROUP BY Ve_PreOrdenDet.IdPreOrden
        ) Ve_PreOrdenTotal ON Ve_PreOrdenTotal.IdPreOrden = Gen_Producto.IdPreOrden
        LEFT JOIN (
            SELECT 
                Cb_CajaBanco.IdPreOrden, 
                SUM(Cb_CajaBanco.Importe) AS sumAdelanto 
            FROM 
                Cb_CajaBanco
            INNER JOIN Cb_TipoCajaBanco ON Cb_TipoCajaBanco.IdTipoCajaBanco = Cb_CajaBanco.IdTipoCajaBanco 
            WHERE 
                Cb_TipoCajaBanco.Tipo = 0 AND IFNULL(Cb_CajaBanco.Anulado,0) = 0
            GROUP BY Cb_CajaBanco.IdPreOrden
        ) Cb_CajaBancoAdelanto ON Cb_CajaBancoAdelanto.IdPreOrden = Gen_Producto.IdPreOrden
        WHERE 
            Gen_Producto.Anulado = 0 
            AND Gen_Producto.EsHabitacion = 1 
            AND Gen_Producto.EstadoHabitacion <> 1
			AND Gen_Producto.IdProducto=$id 
	";

        $stmt = $this->db->query($Ssql);
        $stmt->execute();
        $res = $stmt->fetch();




        // return $response->withJson($venta);



        // $res = json_decode($res, true);
        $hash = time();
        // return $response->withJson($res);

        $fechaHoraActual = date('Y-m-d H:i:s');

        $data = array(
            'txtTIPO_PROCESO' => '03',
            'detalle' =>  $res,
            'txtNRO_DOCUMENTO_EMPRESA' => NRO_DOCUMENTO_EMPRESA,
            'fecha' => $hash,
            'fechaHoraActual' => $fechaHoraActual
        );

        // return $response->withJson(json_encode($data));
        $rutapdf = $_SERVER['HTTP_HOST'] . '/api/sunat/plugins/dompdf/';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $rutapdf . "impirimirReciboServicio.php",
            CURLOPT_USERAGENT => "Codular Sample cURL Request",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));


        $resp = curl_exec($curl);
        curl_close($curl);


        $fichero = 'impirimirReciboServicio_' . $hash;

        $urlArchivo = '/sunat/api_cpe/PRODUCCION/' . NRO_DOCUMENTO_EMPRESA . '/' . $fichero . '.pdf';

        $response = $this->response->withHeader('Content-type', 'application/pdf');
        $content = file_get_contents(dirname(__FILE__) . '/sunat/api_cpe/PRODUCCION/' . NRO_DOCUMENTO_EMPRESA . '/' . $fichero . '.pdf');
        return $response->write($content);
        exit;
    } catch (Exception $e) {
        return $response->withJson(["error" => $e->getMessage()], 500);
    }
});

$app->get('/ordenCompra', function (Request $request, Response $response, array $args) {
    try {

        $IdAlmacen = $request->getParam('IdAlmacen');
        $IdProveedor = $request->getParam('IdProveedor') ?? null;

        $select = "
        SELECT 
        Lo_Almacen.Almacen,
        Lo_Proveedor.Proveedor,
        Lo_OrdenCompra.*,
        DATE(Lo_OrdenCompra.FechaOrdenCompra) AS FechaOrdenCompra
        FROM Lo_OrdenCompra
        JOIN Lo_Almacen ON Lo_Almacen.IdAlmacen = Lo_OrdenCompra.IdAlmacen
        JOIN Lo_Proveedor ON Lo_Proveedor.IdProveedor= Lo_OrdenCompra.IdProveedor
        WHERE Lo_OrdenCompra.IdAlmacen=" . $IdAlmacen;

        if ($IdProveedor) {
            $select .= " AND Lo_OrdenCompra.IdProveedor=" . $IdProveedor;
        }

        $select .= " ORDER BY Lo_OrdenCompra.FechaOrdenCompra DESC";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        foreach ($data as &$ordenCompra) {  // Usar referencia para modificar el arreglo original
            $sqlDetalle = "
                SELECT Lo_OrdenCompraDet.*,Gen_Producto.Producto FROM
                Lo_OrdenCompraDet 
                JOIN Gen_Producto ON Lo_OrdenCompraDet.IdProducto = Gen_Producto.IdProducto
                WHERE IdOrdenCompra=" . $ordenCompra['IdOrdenCompra'];
            $stmtDetalle = $this->db->query($sqlDetalle); // Cambiar a $stmtDetalle para evitar sobrescribir la consulta principal
            $stmtDetalle->execute();
            $detalle = $stmtDetalle->fetchAll();
            $ordenCompra['Detalle'] = $detalle;  // Asignar el detalle a la orden de compra
        }

        return $response->withJson($data);  // Devolver el resultado con los detalles
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});


$app->post('/guardarordencompra', function (Request $request, Response $response, array $args) {
    try {
        $IdAlmacen = $request->getParam('IdAlmacen');
        $fecha = $request->getParam('fecha');
        $total = $request->getParam('total');
        $IdProveedor = $request->getParam('proveedor')['IdProveedor'];
        $lstProductos = $request->getParam('lstProductos');

        $Ssql = "SELECT Numero, Anio FROM Lo_OrdenCompra WHERE IdAlmacen=$IdAlmacen AND  Anio='" . date("Y") . "' ORDER BY Numero DESC LIMIT 1";
        $stmt = $this->db->query($Ssql);
        $stmt->execute();
        $ordenCompra = $stmt->fetch();

        $numero = $ordenCompra['Numero'];
        $anio = $ordenCompra['Anio'];

        $newNumero = 1;
        $newAnio = date("Y");


        if ($numero) {
            $newNumero = $numero + 1;
        }
        if ($anio) {
            $newAnio = $anio;
        }

        $insert = $this->db->insert(array(
            'IdAlmacen',
            'IdProveedor',
            'FechaOrdenCompra',
            'Total',
            'Anio',
            'Numero'
        ))
            ->into('Lo_OrdenCompra')
            ->values(array(
                $IdAlmacen,
                $IdProveedor,
                $fecha,
                $total,
                $newAnio,
                $newNumero,
            ));
        $insertId = $insert->execute();

        foreach ($lstProductos as $producto) {
            $insert = $this->db->insert(array(
                'IdOrdenCompra',
                'IdProducto',
                'Cantidad',
                'Precio'
            ))
                ->into('Lo_OrdenCompraDet')
                ->values(array(
                    $insertId,
                    $producto['IdProducto'],
                    $producto['Cantidad'],
                    $producto['precio'],
                ));
            $insert->execute();
        }
        return $response->withJson($insertId);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->post('/ordencompra/actualizar', function (Request $request, Response $response) {

    $idOrdenCompra = $request->getParam('idOrdenCompra');
    if (!($idOrdenCompra > 0)) {
        return $response->withJson(array("success" => false, "msg" => "Error: No se especifico la orden"));
    }

    $sql = "DELETE FROM Lo_OrdenCompraDet WHERE IdOrdenCompra = " . $idOrdenCompra;
    $stmt = $this->db->prepare($sql);
    $deleted = $stmt->execute();

    $productos = $request->getParam('productos');
    foreach ($productos as $producto) {
        $idProducto = $producto['IdProducto'];
        $cantidad = $producto['cantidad'];
        $precio = $producto['precio'];

        $insert = $this->db->insert(array('IdOrdenCompra', 'IdProducto', 'Cantidad', 'Precio'))
            ->into('Lo_OrdenCompraDet')
            ->values(array($idOrdenCompra, $idProducto, $cantidad, $precio));
        $insert->execute();
    }

    return $response->withJson(array("success" => true, "msg" => "Actualizado Correctamente"));
});

$app->get('/ordencompra/detalle', function (Request $request, Response $response) {
    $idOrdenCompra = $request->getParam('IdOrdenCompra');

    $select = "SELECT Lo_OrdenCompraDet.*, Gen_Producto.Producto, Gen_Producto.CodigoBarra, Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica, Gen_ProductoMarca.ProductoMarca, Gen_ProductoMedicion.ProductoMedicion,Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica,Gen_ProductoMarca.ProductoMarca,Gen_ProductoMedicion.ProductoMedicion,
    Gen_Producto.ProductoDesc,
    Gen_Producto.ProductoDescCorto,
    Gen_Producto.CodigoBarra,
    Gen_Producto.Codigo,
    Gen_Producto.Dosis,
    Gen_Producto.PrecioPorMayor,
    Gen_Producto.StockPorMayor,
    Gen_Producto.StockMinimo,
    Gen_Producto.Anulado,
    Gen_Producto.FechaReg,
    Gen_Producto.Hash,
    Gen_Producto.ControlaStock,
    Gen_Producto.PrecioCosto,
    Gen_Producto.VentaEstrategica,
    Gen_Producto.PorcentajeUtilidad,
    Gen_Producto.IdBloque,
    Gen_Producto.EsPadre,Lo_OrdenCompraDet.Precio AS precio, Lo_OrdenCompraDet.Cantidad AS cantidad FROM Lo_OrdenCompraDet
    INNER JOIN Gen_Producto ON Lo_OrdenCompraDet.IdProducto = Gen_Producto.IdProducto
    LEFT JOIN Gen_ProductoFormaFarmaceutica ON Gen_Producto.IdProductoFormaFarmaceutica = Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica 
	LEFT JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca	
    LEFT JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion WHERE IdOrdenCompra = $idOrdenCompra";


    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->post('/ordencompra/anular', function (Request $request, Response $response) {

    $idOrdenCompra = $request->getParam('idOrdenCompra');
    if (!($idOrdenCompra > 0)) {
        return $response->withJson(array("success" => false, "msg" => "Error: No se especifico la orden"));
    }

    $update = $this->db->update(array("Estado" => 0))
        ->table('Lo_OrdenCompra')
        ->where('IdOrdenCompra', '=', $idOrdenCompra);
    $update->execute();

    return $response->withJson(array("success" => true, "msg" => "Actualizado Correctamente"));
});


$app->post('/ordencompra', require __DIR__ . '/order-create.php');

$app->get('/productos/proveedor', function (Request $request, Response $response, array $args) {

    $idAlmacen = $request->getParam('IdAlmacen');
    $idProveedor = $request->getParam('IdProveedor');

    $select = "
    SELECT Gen_Producto.*, 
    Gen_ProductoCategoria.ProductoCategoria, 
    Gen_ProductoMarca.ProductoMarca, 
    Gen_ProductoMedicion.ProductoMedicion, 
    Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica, 
    ROUND(Gen_Producto.PrecioCosto,4) as precio,
    COALESCE(
        (
            SELECT
            Lo_MovimientoDetalle.Cantidad AS cantidad
            FROM
            Lo_Movimiento 
            JOIN Lo_MovimientoDetalle ON Lo_Movimiento.Hash =Lo_MovimientoDetalle.hashMovimiento
            WHERE Lo_Movimiento.IdProveedor =  $idProveedor AND Lo_MovimientoDetalle.IdProducto = Gen_Producto.IdProducto
            ORDER BY Lo_Movimiento.FechaReg DESC 
            LIMIT 1
        )
    ,0) cantidad
    FROM Lo_MovimientoDetalle
    INNER JOIN Gen_Producto ON Lo_MovimientoDetalle.IdProducto = Gen_Producto.IdProducto
    INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
    INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
    INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
    INNER JOIN Gen_ProductoFormaFarmaceutica ON Gen_Producto.IdProductoFormaFarmaceutica = Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica
    INNER JOIN Lo_Movimiento ON Lo_MovimientoDetalle.hashMovimiento =  Lo_Movimiento.Hash
    WHERE Lo_Movimiento.IdMovimientoTipo IN (1,2,3) AND Lo_Movimiento.IdAlmacenDestino = $idAlmacen AND Lo_Movimiento.IdProveedor = $idProveedor";

    $select .= " AND Gen_Producto.Anulado=0 AND Gen_Producto.StockMinimo > 0 AND Gen_Producto.ControlaStock = 1 GROUP BY Gen_Producto.IdProducto";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/ordencompra', function (Request $request, Response $response) {
    try {

        $fechaDesde = $request->getParam('fechaDesde') ? $request->getParam('fechaDesde') : '2017-01-01';
        $fechaHasta = $request->getParam('fechaHasta') ? $request->getParam('fechaHasta') : getNow();
        $estado = $request->getParam('estado');

        $select = "SELECT Lo_OrdenCompra.*,Lo_Proveedor.*, Lo_Almacen.Almacen, CONCAT(Lo_OrdenCompra.Anio, '-', Lo_OrdenCompra.Numero) as Correlativo,
                    ROUND(SUM(Lo_OrdenCompraDet.Precio*Lo_OrdenCompraDet.Cantidad),2) AS Total 
                    FROM Lo_OrdenCompra
                    INNER JOIN Lo_Proveedor ON Lo_OrdenCompra.IdProveedor = Lo_Proveedor.IdProveedor
                    JOIN Lo_OrdenCompraDet ON Lo_OrdenCompra.IdOrdenCompra = Lo_OrdenCompraDet.IdOrdenCompra
                    LEFT JOIN Lo_Almacen ON Lo_Almacen.IdAlmacen = Lo_OrdenCompra.IdAlmacen";

        $select .= " WHERE DATE(Lo_OrdenCompra.FechaOrdenCompra) BETWEEN '$fechaDesde' AND '$fechaHasta' ";

        if ($estado >= 0) {
            $select .= " AND Lo_OrdenCompra.Estado = " . $estado;
        }

        $select .= " GROUP BY Lo_OrdenCompra.IdOrdenCompra ORDER BY Lo_OrdenCompra.FechaOrdenCompra DESC";

        // print_r($select);exit();
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], 500);
        //throw $th;
    }
});

$app->get('/reporte/ordencomprapdf', function (Request $request, Response $response, array $args) use ($app) {
    try {

        $idOrdenCompra = $request->getParam('id');


        $sql = "SELECT Lo_OrdenCompra.*,Lo_Proveedor.Proveedor, Lo_Almacen.Almacen FROM 
                Lo_OrdenCompra 
                JOIN Lo_Proveedor ON Lo_OrdenCompra.IdProveedor = Lo_Proveedor.IdProveedor
                JOIN Lo_Almacen ON Lo_Almacen.IdAlmacen =  Lo_OrdenCompra.IdAlmacen
                WHERE Lo_OrdenCompra.IdOrdenCompra = $idOrdenCompra";

        $stmt = $this->db->query($sql);
        $stmt->execute();
        $ordencompra = $stmt->fetch();

        $Ssql = "SELECT Gen_Producto.IdProducto, Gen_Producto.Codigo, Gen_Producto.Producto, 
                Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica, Gen_ProductoMarca.ProductoMarca, Lo_OrdenCompraDet.Cantidad, Gen_ProductoMarca.ProductoMarca as Laboratorio, 
                Lo_OrdenCompraDet.Precio, Lo_OrdenCompra.Anio, Lo_OrdenCompra.Numero, Lo_Proveedor.Proveedor, Lo_OrdenCompra.FechaOrdenCompra,Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica as Forma,
                        Lo_Proveedor.Ruc, Lo_Proveedor.Direccion, Gen_ProductoMedicion.ProductoMedicion as Medicion, Gen_ProductoMedicion.ProductoMedicion, Lo_Almacen.Almacen
                FROM Lo_OrdenCompraDet 
                INNER JOIN Gen_Producto ON Lo_OrdenCompraDet.IdProducto = Gen_Producto.IdProducto
                INNER JOIN Lo_OrdenCompra ON Lo_OrdenCompraDet.IdOrdenCompra = Lo_OrdenCompra.IdOrdenCompra
                INNER JOIN Lo_Proveedor ON Lo_OrdenCompra.IdProveedor = Lo_Proveedor.IdProveedor
                LEFT JOIN Gen_ProductoFormaFarmaceutica ON Gen_Producto.IdProductoFormaFarmaceutica = Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica 
                LEFT JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca	
                LEFT JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
                LEFT JOIN Lo_Almacen ON Lo_Almacen.IdAlmacen = Lo_OrdenCompra.IdAlmacen
	            where Lo_OrdenCompraDet.IdOrdenCompra = $idOrdenCompra";

        $stmt = $this->db->query($Ssql);
        $stmt->execute();
        $detalle = $stmt->fetchAll();

        $data = array(
            'txtTIPO_PROCESO' => '03',
            'detalle' => $detalle,
            'ordencompra' => $ordencompra,
            'txtNRO_DOCUMENTO_EMPRESA' => NRO_DOCUMENTO_EMPRESA,
        );

        $rutapdf = $_SERVER['HTTP_HOST'] . '/api/sunat/plugins/dompdf/';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $rutapdf . "OrdenCompra.php",
            CURLOPT_USERAGENT => "Codular Sample cURL Request",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));

        $resp = curl_exec($curl);
        curl_close($curl);

        // Ruta del archivo generado
        $urlArchivo = $_SERVER['HTTP_HOST']  . '/api/sunat/api_cpe/PRODUCCION/' . NRO_DOCUMENTO_EMPRESA . '/OrdenCompra_' . $ordencompra['Anio'] . '_' . $ordencompra['Numero'] . '.pdf';
        $urlArchivo = stripslashes($urlArchivo);

        return $response->withJson($urlArchivo);
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], 500);
    }
});


$app->get('/reporte/ordencompra/id', function (Request $request, Response $response, array $args) use ($app) {
    $idOrdenCompra = $request->getParam('idordencompra');

    $select = "
        SELECT Gen_Producto.IdProducto, Gen_Producto.Codigo, Gen_Producto.Producto, Gen_Producto.StockMinimo,
         COALESCE(
        (
            SELECT
            Lo_MovimientoDetalle.Cantidad AS cantidad
            FROM
            Lo_Movimiento 
            JOIN Lo_MovimientoDetalle ON Lo_Movimiento.Hash =Lo_MovimientoDetalle.hashMovimiento
            WHERE Lo_Movimiento.IdProveedor =  Lo_Proveedor.IdProveedor AND Lo_MovimientoDetalle.IdProducto = Gen_Producto.IdProducto
            ORDER BY Lo_Movimiento.FechaReg DESC 
            LIMIT 1
        )
        ,0) cantidadSugeridad,
        Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica, Gen_ProductoMarca.ProductoMarca, Lo_OrdenCompraDet.Cantidad, Gen_ProductoMarca.ProductoMarca as Laboratorio, 
        Lo_OrdenCompraDet.Precio, Lo_OrdenCompra.Anio, Lo_OrdenCompra.Numero, Lo_Proveedor.Proveedor, Lo_OrdenCompra.FechaOrdenCompra,Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica as Forma,
        Lo_Proveedor.Ruc, Lo_Proveedor.Direccion, Gen_ProductoMedicion.ProductoMedicion as Medicion, Gen_ProductoMedicion.ProductoMedicion, Lo_Almacen.Almacen
    FROM Lo_OrdenCompraDet 
    INNER JOIN Gen_Producto ON Lo_OrdenCompraDet.IdProducto = Gen_Producto.IdProducto
    INNER JOIN Lo_OrdenCompra ON Lo_OrdenCompraDet.IdOrdenCompra = Lo_OrdenCompra.IdOrdenCompra
    INNER JOIN Lo_Proveedor ON Lo_OrdenCompra.IdProveedor = Lo_Proveedor.IdProveedor
    LEFT JOIN Gen_ProductoFormaFarmaceutica ON Gen_Producto.IdProductoFormaFarmaceutica = Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica 
    LEFT JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca	
    LEFT JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
    LEFT JOIN Lo_Almacen ON Lo_Almacen.IdAlmacen = Lo_OrdenCompra.IdAlmacen
    where Lo_OrdenCompraDet.IdOrdenCompra = $idOrdenCompra";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    $numero = $data[0]['Numero'] . '-' . $data[0]['Anio'];

    $excel = new Spreadsheet();
    //$sheet = $excel->setActiveSheetIndex(0);
    $sheet = $excel->getActiveSheet();
    $excel->getActiveSheet()->mergeCells('A1:J1');
    $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
    $sheet->setCellValue('A1', RAZON_SOCIAL_EMPRESA . "- " . $data[0]['Almacen']);
    $sheet->setCellValue('A2', 'RUC:' . NRO_DOCUMENTO_EMPRESA);
    $sheet->setCellValue('A3', DIRECCION_EMPRESA);
    $sheet->setCellValue('A4', 'FECHA DE ORDEN');
    $sheet->setCellValue('B4', $data[0]['FechaOrdenCompra']);
    $sheet->setCellValue('A5', 'PROVEEDOR');
    $sheet->setCellValue('B5', $data[0]['Proveedor']);
    $sheet->setCellValue('A6', 'N° DE ORDEN COMPRA');
    $sheet->setCellValue('B6', $numero);

    $sheet->setCellValue('A8', 'COD');
    $sheet->setCellValue('B8', 'PRODUCTO');
    $sheet->setCellValue('C8', 'MEDICION');
    $sheet->setCellValue('D8', 'S. M.');
    $sheet->setCellValue('E8', 'C. S.');
    $sheet->setCellValue('F8', 'CANTIDAD');
    $sheet->setCellValue('G8', 'PRECIO');
    $sheet->setCellValue('H8', 'TOTAL');


    $cont = 9;
    $sumatoria = 0;
    foreach ($data as $key => $row) {
        $total = $row["Precio"] * $row["Cantidad"];
        $sumatoria += $total;
        $sheet->setCellValue('A' . $cont, $row["IdProducto"]);
        $sheet->setCellValue('B' . $cont, $row["Producto"]);
        $sheet->setCellValue('C' . $cont, $row["ProductoMedicion"]);
        $sheet->setCellValue('D' . $cont, $row["StockMinimo"]);
        $sheet->setCellValue('E' . $cont, $row["cantidadSugeridad"]);
        $sheet->setCellValue('F' . $cont, $row["Cantidad"]);
        $sheet->setCellValue('G' . $cont, $row["Precio"]);
        $sheet->setCellValue('H' . $cont, $total);
        $cont++;
    }
    //Diseño excel
    foreach (range('B', 'H') as $columnID) {
        $excel->getActiveSheet()->getColumnDimension($columnID)
            ->setAutoSize(true);
    }

    foreach (array('A8', 'B8', 'C8', 'D8', 'E8', 'F8', 'G8', 'H8') as $columnID) {
        $excel->getActiveSheet()->getStyle($columnID)->getBorders()->getOutline()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('#000000'));
    }

    $excel->getActiveSheet()->getStyle('A5:J6')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A2:A4')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A8:H8')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('B4')->getFont()->setBold(true);
    $excel->getActiveSheet()->getStyle('A1:A7')->getAlignment()->setHorizontal('left');
    $excel->getActiveSheet()->getStyle('A8:H8')->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('C6:B' . $cont)->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('B6:B' . $cont)->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('D6:B' . $cont)->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('E6:B' . $cont)->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('A8:H8')->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFE6E6FA');

    //Totales
    $excel->getActiveSheet()->mergeCells('A' . ($cont + 1) . ':G' . ($cont + 1));
    $excel->getActiveSheet()->getStyle('A' . ($cont + 1))->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('A' . ($cont + 1))->getFont()->setBold(true);
    $sheet->setCellValue('A' . ($cont + 1), 'SUMATORIA');
    $sheet->setCellValue('H' . ($cont + 1), $sumatoria);
    //$excel->getActiveSheet()->getStyle('H'.$cont.':J'.$cont)->getFont()->setBold( true );


    $excelWriter = new Xlsx($excel);

    $fileName = 'Orden de Compra ' . $numero . ' (' . getNow('Y-m-d-H-i-s') .  ').xlsx';
    $excelFileName = __DIR__ . '/reporte/' . $fileName;
    $excelWriter->save($excelFileName);

    echo "<script>window.location.href = '/api/reporte/" . $fileName . "'</script>";
    exit;
});


// $app->get('/buscarproductos/nombrexml', function (Request $request, Response $response, array $args) {

//     $Producto = $request->getParam('Producto');

//     $select = "SELECT * FROM Gen_Producto";
//     $stmt = $this->db->query($select);
//     $stmt->execute();
//     $productos = $stmt->fetchAll();

//     $mejorCoincidencia = null;
//     $menorDistancia = 999;

//     $nombreBuscado = strtolower(str_replace(' ', '', $Producto));

//     foreach ($productos as $p) {
//         $nombreProducto = strtolower(str_replace(' ', '', $p->Producto));
//         $distancia = levenshtein($nombreBuscado, $nombreProducto);

//         if ($distancia < $menorDistancia) {
//             $menorDistancia = $distancia;
//             $mejorCoincidencia = $p;
//         }
//     }

//     $select = "SELECT * FROM Gen_Producto";
//     $stmt = $this->db->query($select);
//     $productos = $stmt->fetchAll();

//     $mejorCoincidencia = false;
//     $menorDistancia = 999;
//     $nombreBuscado = strtolower(str_replace(' ', '', $Producto));

//     foreach ($productos as $p) {
//         $nombreProducto = strtolower(str_replace(' ', '', $p['Producto']));
//         $distancia = levenshtein($nombreBuscado, $nombreProducto);
//         // similar_text($nombreBuscado, $nombreProducto, $porcentaje);
//         if ($porcentaje >= 80 && $porcentaje > $mayorSimilitud) {
//             $menorDistancia = $distancia;
//             $mejorCoincidencia = $p;
//         }
//     }

//     return $response->withJson($mejorCoincidencia);
// });

// $app->get('/buscarproductos/nombrexml', function (Request $request, Response $response, array $args) {

//     $Producto = $request->getParam('Producto');

//     if (empty($Producto)) {
//         return $response->withJson(['error' => 'Debe enviar el parámetro Producto'], 400);
//     }

//     $limpiarTexto = function ($texto) {
//         return strtolower(preg_replace('/[^a-z0-9]/', '', $texto));
//     };

//     $nombreBuscado = $limpiarTexto($Producto);

//     $select = "SELECT * FROM Gen_Producto";
//     $stmt = $this->db->query($select);
//     $stmt->execute();
//     $productos = $stmt->fetchAll();

//     $mejorCoincidencia = null;
//     $mayorSimilitud = 0;
//     $menorDistancia = 999;

//     foreach ($productos as $p) {
//         $nombreProducto = $limpiarTexto($p['Producto']);
//         similar_text($nombreBuscado, $nombreProducto, $porcentaje);
//         $distancia = levenshtein($nombreBuscado, $nombreProducto);

//         if (
//             ($porcentaje >= 75 && $porcentaje > $mayorSimilitud) ||
//             ($distancia <= 3 && $distancia < $menorDistancia)
//         ) {
//             $mayorSimilitud = $porcentaje;
//             $menorDistancia = $distancia;
//             $mejorCoincidencia = $p;
//             $mejorCoincidencia['porcentaje_similitud'] = round($porcentaje, 2);
//             $mejorCoincidencia['distancia'] = $distancia;
//         }
//     }

//     if ($mejorCoincidencia) {
//         return $response->withJson($mejorCoincidencia);
//     } else {
//         return $response->withJson(false);
//     }
// });

$app->get('/buscarproductos/nombrexml', function (Request $request, Response $response, array $args) {

    $Producto = $request->getParam('Producto');

    if (empty($Producto)) {
        return $response->withJson(['error' => 'Debe enviar el parámetro Producto'], 400);
    }


    $limpiarTexto = function ($texto) {
        $texto = mb_strtolower($texto, 'UTF-8');
        $texto = preg_replace('/[^\p{L}\p{N}\s]/u', '', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        return trim($texto);
    };

    $nombreBuscado = $limpiarTexto($Producto);

    $select = "SELECT * FROM Gen_Producto";
    $stmt = $this->db->query($select);
    $stmt->execute();
    $productos = $stmt->fetchAll();

    $coincidencias = [];

    foreach ($productos as $p) {
        $nombreProducto = $limpiarTexto($p['Producto']);
        if (strpos($nombreProducto, $nombreBuscado) !== false || strpos($nombreBuscado, $nombreProducto) !== false) {
            $p['porcentaje_similitud'] = 100;
            $p['distancia'] = 0;
            $coincidencias[] = $p;
            continue;
        }
        similar_text($nombreBuscado, $nombreProducto, $porcentaje);
        $distancia = levenshtein($nombreBuscado, $nombreProducto);

        if ($porcentaje >= 80) {
            $p['porcentaje_similitud'] = round($porcentaje, 2);
            $p['distancia'] = $distancia;
            $coincidencias[] = $p;
        }
    }

    if (count($coincidencias) > 0) {
        usort($coincidencias, function ($a, $b) {
            if ($a['porcentaje_similitud'] == $b['porcentaje_similitud']) {
                return $a['distancia'] - $b['distancia'];
            }
            return $b['porcentaje_similitud'] - $a['porcentaje_similitud'];
        });

        return $response->withJson($coincidencias);
    } else {
        return $response->withJson([]);
    }
});



$app->get('/proformas/favoritas', function (Request $request, Response $response) {
    try {
        $filter = $request->getParam('filter');
        $select = "
        SELECT Ve_Proforma.IdProforma, Ve_Proforma.IdCliente,
         Ve_Proforma.Anio, Ve_Proforma.Numero, Ve_Proforma.FechaReg,
         Ve_Proforma.UsuarioReg,  Ve_DocVentaCliente.Cliente,
         Ve_Proforma.EsFavoritoFlag,
          Ve_Proforma.Descripcion,
        Ve_DocVentaCliente.DniRuc, Ve_DocVentaCliente.Direccion, IFNULL(ROUND(SUM(Ve_ProformaDet.Cantidad * Ve_ProformaDet.Precio - IFNULL(Ve_ProformaDet.Descuento,0)),2),0) AS Total
        FROM Ve_Proforma
        INNER JOIN Ve_ProformaDet ON Ve_Proforma.IdProforma = Ve_ProformaDet.IdProforma
        LEFT JOIN Ve_DocVentaCliente ON Ve_Proforma.IdCliente = Ve_DocVentaCliente.IdCliente
        WHERE Ve_Proforma.Anulado = 0 AND COALESCE(Ve_Proforma.UsuarioReg,'') <> 'TIENDA'
        AND Ve_Proforma.EsFavoritoFlag = 1 ";

        $select .= " GROUP BY Ve_Proforma.IdProforma ORDER BY Ve_Proforma.IdProforma DESC";


        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->put('/proforma/cambiarfavorito', function (Request $request, Response $response) {
    try {
        $EsFavoritoFlag = $request->getParam('EsFavoritoFlag');
        $IdProforma = $request->getParam('IdProforma');
        $update = "UPDATE Ve_Proforma  SET EsFavoritoFlag=$EsFavoritoFlag where IdProforma= $IdProforma";
        $stmt = $this->db->prepare($update);
        $updated = $stmt->execute();
        return $response->withJson($updated);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->post('/movimientoxml', function (Request $request, Response $response, array $args) {
    $rucProveedor = $request->getParam('rucProveedor');
    $nameProveedor = $request->getParam('nameProveedor');
    $direccionProveedor = $request->getParam('direccionProveedor');
    $tipoCambio = $request->getParam('TipoCambio');

    try {
        // VALIDANDO SI EXISTE EL PROVEEDOR + REGISTRAR SI NO EXISTE
        $select = "SELECT IdProveedor FROM Lo_Proveedor WHERE Ruc = :ruc";
        $stmt = $this->db->prepare($select);
        $stmt->bindParam(':ruc', $rucProveedor);
        $stmt->execute();

        if (!$data = $stmt->fetch()) {
            $insert = $this->db->insert(array(
                'Proveedor',
                'Ruc',
                'Direccion',
                'Observacion',
                'FechaReg',
                'Telefono'
            ))
                ->into('Lo_Proveedor')
                ->values(array(
                    $nameProveedor,
                    $rucProveedor,
                    $direccionProveedor,
                    null,
                    getNow(),
                    null
                ));

            $idProveedor = $insert->execute();
        } else {
            $idProveedor = $data['IdProveedor'];
        }

        // MOVIMIENTOS
        $select = "SELECT * FROM Lo_Movimiento
        WHERE IdMovimientoTipo = '" . $request->getParam('movimientoTipo')['IdMovimientoTipo']
            . "' AND IdProveedor = '" . $request->getParam('proveedor')['IdProveedor']
            . "' AND Serie = '" . $request->getParam('Serie')
            . "' AND Numero = '" . $request->getParam('Numero') . "'";

        $stmt = $this->db->query($select);
        $stmt->execute();

        if (count($stmt->fetchAll())) {
            $data = array(
                "error" => 'Error: Movimiento duplicado'
            );
            return $response->withJson($data);
        }
        // end verificar Movimiento

        // start Insertar Movimiento
        $idMovimientoTipo = $request->getParam('movimientoTipo')['IdMovimientoTipo'];
        $idProveedor = $idProveedor;
        $serie = $request->getParam('Serie');
        $numero = $request->getParam('Numero');
        //$movimientoFecha = $request->getParam('movimiento')['MovimientoFecha'];
        $movimientoFecha = getNow();
        $almacenOrigen = $request->getParam('almacenOrigen')['IdAlmacen'];
        $almacenDestino = $request->getParam('almacenDestino')['IdAlmacen'];
        $anulado = 0;
        $fechaReg = getNow();
        $user = $request->getParam('movimiento')['user'];
        $usuarioReg = 'xx';
        if (isset($_SESSION['user'])) {
            $usuarioReg = $_SESSION['user'];
        }
        $usuarioReg = $user ? $user : $usuarioReg;
        $hash = time();
        //$fechaStock = $request->getParam('movimiento')['FechaStock'];
        $fechaStock = getNow();
        $percepcion = 0;
        $esCredito = $request->getParam('EsCredito');
        $fechaVenCredito = $request->getParam('FechaVenCredito') ? $request->getParam('FechaVenCredito') : NULL;
        $fechaPeriodoTributario = $request->getParam('FechaPeriodoTributario');
        $tipoCambio = $request->getParam('TipoCambio') ? $request->getParam('TipoCambio') : 1;
        $moneda = $request->getParam('Moneda');

        $insert = $this->db->insert(array(
            'IdMovimientoTipo',
            'IdProveedor',
            'Serie',
            'Numero',
            'MovimientoFecha',
            'IdAlmacenOrigen',
            'IdAlmacenDestino',
            'Anulado',
            'FechaReg',
            'UsuarioReg',
            'Hash',
            'FechaStock',
            'Percepcion',
            'EsCredito',
            'FechaVenCredito',
            'FechaPeriodoTributario',
            'TipoCambio',
            'Moneda'
        ))
            ->into('Lo_Movimiento')
            ->values(array(
                $idMovimientoTipo,
                $idProveedor,
                $serie,
                $numero,
                $movimientoFecha,
                $almacenOrigen,
                $almacenDestino,
                $anulado,
                $fechaReg,
                $usuarioReg,
                $hash,
                $fechaStock,
                $percepcion,
                $esCredito,
                $fechaVenCredito,
                $fechaPeriodoTributario,
                $tipoCambio,
                $moneda
            ));

        $insertId = $insert->execute();

        $data = array(
            "hash" => $hash
        );
        // end insertar Movimiento

        if ($moneda == 'USD') {
            $update = "UPDATE Gen_Moneda SET TipoCambio=$tipoCambio WHERE Moneda = 'USD'";
            $stmt = $this->db->prepare($update);
            $updated = $stmt->execute();
        }

        $productos = $request->getParam('productos');
        foreach ($productos as $producto) {


            if (isset($producto['IdProducto']) && !empty($producto['IdProducto'])) {

                $idProducto = $producto['IdProducto'];
            } else {
                try {
                    $sql = "SELECT MAX(CAST(CodigoBarra AS UNSIGNED)) + 1 AS NuevoCodigoBarra FROM Gen_Producto";
                    $stmt = $this->db->query($sql);
                    $result = $stmt->fetch();


                    $codigoBarra = $result['NuevoCodigoBarra'] ?? 1;
                    $idProductoFormaFarmaceutica = 1;
                    $idProductoMarca = $producto['IdProductoMarca'];
                    $idProductoMedicion = 6;
                    $idProductoCategoria = 28;
                    $hashProducto = time();
                    $precio = $producto['PrecioCosto'];
                    $precio = $precio * $tipoCambio;

                    $insert = $this->db->insert(array(
                        'IdProductoMarca',
                        'IdProductoFormaFarmaceutica',
                        'IdProductoMedicion',
                        'IdProductoCategoria',
                        'Producto',
                        'FechaReg',
                        'ControlaStock',
                        'PorcentajeUtilidad',
                        'PrecioCosto',
                        'PrecioContado',
                        'Hash',
                        'Anulado',
                        'EsPadre',
                        'StockMinimo',
                        'CodigoBarra'
                    ))
                        ->into('Gen_Producto')
                        ->values(array(
                            $idProductoMarca,
                            $idProductoFormaFarmaceutica,
                            $idProductoMedicion,
                            $idProductoCategoria,
                            $producto['Producto'],
                            getNow(),
                            1,
                            $producto['PorcentajeUtilidad'] ? $producto['PorcentajeUtilidad'] : 0,
                            $precio,
                            $precioContado,
                            $hashProducto,
                            0,
                            0,
                            0,
                            $codigoBarra
                        ));
                    $idProducto = $insert->execute();
                } catch (Exception $e) {
                    return $response->withJson(['error' => $e->getMessage()], 500);
                }
            }


            try {
                $cantidad = $producto['cantidad'];
                $tieneIgv = isset($producto['TieneIgv']) ? $producto['TieneIgv'] : 0;
                $precio = $producto['PrecioCosto'];
                $PrecioContado = $producto['PrecioContado'];
                $idLote = isset($producto['IdLote']) ? $producto['IdLote'] : 0;
                $descripcion = isset($producto['Descripcion']) ? $producto['Descripcion'] : '';
                $TotalFlete = isset($producto['FLETE']) ? $producto['FLETE'] : 0;
                $flete = ($producto['FLETE'] / $producto['cantidad']) * $tipoCambio;
                $nuevoPrecioContado = $producto['nuevoPrecioContado'];
                if ($tieneIgv === 1) {
                    $precio += $precio * 0.18;
                }

                $insert = $this->db->insert(array('hashMovimiento', 'IdProducto', 'Cantidad', 'TieneIgv', 'Precio', 'IdLote', 'Descripcion'))
                    ->into('Lo_MovimientoDetalle')
                    ->values(array($hash, $idProducto, $cantidad, $tieneIgv, $precio, $idLote, $descripcion));
                $insert->execute();


                $update = $this->db->update(array(
                    'PrecioContado' => $PrecioContado,
                ))
                    ->table('Gen_Producto')
                    ->where('IdProducto', '=', $idProducto);
                $update->execute();
            } catch (Exception $e) {
                return $response->withJson(['error' => $e->getMessage()], 500);
            }
        }


        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/productos/categoria/carga', function (Request $request, Response $response, array $args) {

    $idcategoria = $request->getParam('IdCategoria');


    $select = "SELECT Gen_Producto.*, Gen_ProductoCategoria.ProductoCategoria, Gen_ProductoMarca.ProductoMarca,
            Gen_ProductoMedicion.ProductoMedicion, 0 as cantidad
            FROM Gen_Producto
            INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
            INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
            INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion";

    $select .= " WHERE Gen_Producto.Anulado=0 AND Gen_Producto.IdProductoCategoria = $idcategoria ORDER BY Gen_Producto.CodigoBarra DESC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->post('/reporte/confrontacion', function (Request $request, Response $response, array $args) use ($app) {
    $productoscant = $request->getParam('Productos');
    $idAlmacen = $request->getParam('IdAlmacen');
    $idProductos = $request->getParam('IdProductos');
    $fechaHasta = getNow("Y-m-d") . " 23:59:59";
    $almacenNombre = '';
    $filterStr = http_build_query(array(
        'idProductos' => $idProductos
    ));

    $res = $app->subRequest('GET', '/productos/stock', 'idAlmacen=' . $idAlmacen . '&' . $filterStr . '&noLimit=1' . '&fechaHasta=' . $fechaHasta);

    $productos = (string) $res->getBody();
    $productos = json_decode($productos, true);

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/confrontacion.xlsx');
    $formato = $spreadsheet->getSheet(0);

    if (!empty($idAlmacen)) {
        $select = "SELECT Almacen FROM Lo_Almacen WHERE IdAlmacen =" . $idAlmacen;
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetch();
        $almacenNombre = $data['Almacen'];
    }

    $formato->getCell('I5')->setValue($almacenNombre);
    $formato->getCell('L3')->setValue(getNow("Y-m-d"));

    $init = 11;

    foreach ($productos as $key => $producto) {
        foreach ($productoscant as $productocant) {
            if ($producto['IdProducto'] == $productocant['IdProducto']) {
                $productos[$key]["cantidad"] = $productocant['cantidad'];
            }
        }
    }

    foreach ($productos as $kar) {

        $formato->mergeCells('B' . ($init) . ':' . 'C' . ($init));
        $formato->mergeCells('D' . ($init) . ':' . 'E' . ($init));
        $formato->mergeCells('F' . ($init) . ':' . 'G' . ($init));
        $formato->getCell('A' . $init)->setValue(@$kar['CodigoBarra']);
        $formato->getCell('B' . $init)->setValue(@$kar['Producto']);
        $formato->getCell('D' . $init)->setValue(@$kar['stock']);
        $formato->getCell('F' . $init)->setValue(@$kar['cantidad']);
        $formato->getCell('H' . $init)->setValue("=IF((D" . $init . "-F" . $init . ")>0,(D" . $init . "-F" . $init . "),0)");
        $formato->getCell('I' . $init)->setValue("=IF((D" . $init . "-F" . $init . ")<0,-1*(D" . $init . "-F" . $init . "),0)");


        $formato->insertNewRowBefore(($init + 1), 1);
        $formato->duplicateStyle($formato->getStyle('A' . $init), 'A' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('B' . $init), 'B' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('C' . $init), 'C' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('D' . $init), 'D' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('E' . $init), 'E' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('F' . $init), 'F' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('G' . $init), 'G' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('H' . $init), 'H' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('I' . $init), 'I' . ($init + 1));
        $formato->mergeCells('B' . ($init + 1) . ':' . 'C' . ($init + 1));
        $formato->mergeCells('D' . ($init + 1) . ':' . 'E' . ($init + 1));
        $formato->mergeCells('F' . ($init + 1) . ':' . 'G' . ($init + 1));

        $init += 1;
    }
    //$formato->removeRow($init); 

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('reporte/Confrontacion Inv. ' . getNow("Y-m-d") . '.xlsx');

    echo '<script type="text/javascript">
        setTimeout(() => {
            location.href = "' . 'http://' . $_SERVER['HTTP_HOST'] . '/api/reporte/Confrontacion%20Inv.%20' . getNow("Y-m-d") . '.xlsx";
        }, 1000); 
    </script>';
});

$app->post('/reporte/preconfrontacion', function (Request $request, Response $response, array $args) use ($app) {
    $productoscant = $request->getParam('Productos');
    $idAlmacen = $request->getParam('IdAlmacen');
    $almacenNombre = '';

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/confrontacionpre.xlsx');
    $formato = $spreadsheet->getSheet(0);

    if (!empty($idAlmacen)) {
        $select = "SELECT Almacen FROM Lo_Almacen WHERE IdAlmacen =" . $idAlmacen;
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetch();
        $almacenNombre = $data['Almacen'];
    }

    $formato->getCell('I5')->setValue($almacenNombre);
    $formato->getCell('E3')->setValue(getNow("Y-m-d"));

    $init = 11;


    foreach ($productoscant as $kar) {

        $formato->mergeCells('B' . ($init) . ':' . 'C' . ($init));
        $formato->getCell('A' . $init)->setValue(@$kar['CodigoBarra']);
        $formato->getCell('B' . $init)->setValue(@$kar['Producto']);
        $formato->getCell('D' . $init)->setValue('');


        $formato->insertNewRowBefore(($init + 1), 1);
        $formato->duplicateStyle($formato->getStyle('A' . $init), 'A' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('B' . $init), 'B' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('C' . $init), 'C' . ($init + 1));
        $formato->duplicateStyle($formato->getStyle('D' . $init), 'D' . ($init + 1));
        $formato->mergeCells('B' . ($init + 1) . ':' . 'C' . ($init + 1));

        $init += 1;
    }
    //$formato->removeRow($init); 

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('reporte/PreConfrontacion Inv. ' . getNow("Y-m-d") . '.xlsx');

    echo '<script type="text/javascript">
        setTimeout(() => {
            location.href = "' . 'http://' . $_SERVER['HTTP_HOST'] . '/api/reporte/PreConfrontacion%20Inv.%20' . getNow("Y-m-d") . '.xlsx";
        }, 1000); 
    </script>';
});

$app->get('/reporte/confrontacion/total', function (Request $request, Response $response, array $args) use ($app) {
    $idAlmacen = $request->getParam('idAlmacen');
    $fechaHasta = date("Y-m-d") . " 23:59:59";
    $almacenNombre = '';

    $res = $app->subRequest('GET', '/productos/stock', 'idAlmacen=' . $idAlmacen . '&noLimit=1&fechaHasta=' . $fechaHasta);
    if ($res->getStatusCode() !== 200) {
        throw new Exception("Error en subRequest: " . $res->getBody());
    }

    $productos = json_decode($res->getBody(), true);
    if (!is_array($productos)) {
        throw new Exception("Respuesta no válida de productos/stock");
    }

    // Cargar plantilla de Excel
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template/confrontaciontotal.xlsx');
    $sheet = $spreadsheet->getSheet(0);

    // Obtener nombre del almacén
    if (!empty($idAlmacen)) {
        $select = "SELECT Almacen FROM Lo_Almacen WHERE IdAlmacen = :idAlmacen";
        $stmt = $this->db->prepare($select);
        $stmt->execute(['idAlmacen' => $idAlmacen]);
        $data = $stmt->fetch();
        $almacenNombre = $data['Almacen'] ?? '';
    }

    // Insertar datos en el Excel
    $sheet->getCell('I5')->setValue($almacenNombre);
    $sheet->getCell('L3')->setValue(date("Y-m-d"));

    $init = 11;

    foreach ($productos as $kar) {
        $sheet->getCell('A' . $init)->setValue($kar['CodigoBarra'] ?? '');
        $sheet->getCell('B' . $init)->setValue($kar['Producto'] ?? '');
        $sheet->getCell('D' . $init)->setValue($kar['stock'] ?? 0);
        $sheet->getCell('F' . $init)->setValue($kar['cantidad'] ?? 0);
        $sheet->getCell('H' . $init)->setValue("=IF((D$init-F$init)>0,(D$init-F$init),0)");
        $sheet->getCell('I' . $init)->setValue("=IF((D$init-F$init)<0,-1*(D$init-F$init),0)");

        $init++; // Avanza a la siguiente fila
    }

    // Agregar la fila de firmas después de la última fila de productos
    $finalRow = $init + 1; // Se deja una fila vacía entre productos y firmas

    $sheet->mergeCells("B$finalRow:D$finalRow");
    $sheet->mergeCells("E$finalRow:G$finalRow");
    $sheet->mergeCells("H$finalRow:J$finalRow");

    $sheet->setCellValue("B$finalRow", "ELABORO");
    $sheet->setCellValue("E$finalRow", "VERIFICO");
    $sheet->setCellValue("H$finalRow", "APROBO");

    $fecha = getNow("Y-m-d");
    // Guardar archivo
    $fileName = 'Confrontacion_Inv_Total_' . $fecha . '.xlsx';
    $filePath = __DIR__ . "/reporte/" . $fileName;
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save($filePath);

    echo '<script type="text/javascript">
        location.href = "/api/reporte/Confrontacion_Inv_Total_' . $fecha . '.xlsx";
    </script>';
});

$app->get('/ListarTipoOpe', function (Request $request, Response $response, array $args) {
    try {
        $select = "SELECT IdTipoCajaBanco, TipoCajaBanco, Tipo FROM Cb_TipoCajaBanco;";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/buscador_cajabanco', function (Request $request, Response $response, array $args) {
    try {
        $cuenta = $request->getParam('IdCuenta');
        $tipo = $request->getParam('IdTipoOperacion');
        $fechaIni = $request->getParam('FechaIni');
        $fechaFin = $request->getParam('FechaFin');
        $select = "SELECT IdCajaBanco, FechaDoc, Lo_Proveedor.Proveedor, Ve_DocVentaCliente.Cliente, Concepto, Importe 
			FROM Cb_CajaBanco
			LEFT JOIN Lo_Proveedor ON Cb_CajaBanco.IdProveedor = Lo_Proveedor.IdProveedor
			LEFT JOIN Ve_DocVentaCliente ON Cb_CajaBanco.IdCliente = Ve_DocVentaCliente.IdCliente
			WHERE IdCuenta=$cuenta AND IdTipoCajaBanco=$tipo AND DATE(FechaDoc) BETWEEN '$fechaIni' AND '$fechaFin'";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->put('/cajabanco/eliminar', function (Request $request, Response $response, array $args) {
    try {
        $idCajaBanco = $request->getParam('IdCajaBanco');

        $sql = "DELETE FROM Cb_CajaBancoDet WHERE IdCajaBanco =$idCajaBanco";
        $stmt = $this->db->prepare($sql);
        $deleted = $stmt->execute();

        $sql = "DELETE FROM Cb_CajaBanco WHERE IdCajaBanco =$idCajaBanco";
        $stmt = $this->db->prepare($sql);
        $deleted = $stmt->execute();
        return $response->withJson($deleted);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/exportar_buscador_cajabanco', function (Request $request, Response $response, array $args)  use ($app) {
    try {
        $cuenta = $request->getParam('IdCuenta');
        $tipo = $request->getParam('IdTipoOperacion');
        $fechaIni = $request->getParam('FechaIni');
        $fechaFin = $request->getParam('FechaFin');

        $res = $app->subRequest(
            'GET',
            '/buscador_cajabanco',
            'IdCuenta=' . $cuenta . '&IdTipoOperacion=' . $tipo . '&FechaIni=' . $fechaIni . '&FechaFin=' . $fechaFin
        );
        // return $response->withJson(['error' => $res], 500);
        $productos = (string) $res->getBody();
        $productos = json_decode($productos, true);

        $excel = new Spreadsheet();

        $sheet = $excel->getActiveSheet();

        $sheet->setCellValue('A1', 'IdCajaBanco');
        $sheet->setCellValue('B1', 'FechaDoc');
        $sheet->setCellValue('C1', 'Proveedor');
        $sheet->setCellValue('D1', 'Cliente');
        $sheet->setCellValue('E1', 'Concepto');
        $sheet->setCellValue('F1', 'Importe');


        $cont = 2;
        foreach ($productos as $prod) {
            $sheet->setCellValue('A' . $cont, $prod['IdCajaBanco']);
            $sheet->setCellValue('B' . $cont, $prod['FechaDoc']);
            $sheet->setCellValue('C' . $cont, $prod['Proveedor']);
            $sheet->setCellValue('D' . $cont, $prod['Cliente']);
            $sheet->setCellValue('E' . $cont, $prod['Concepto']);
            $sheet->setCellValue('F' . $cont, $prod['Importe']);
            $cont += 1;
        }

        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A237E']],
            'alignment' => ['horizontal' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);

        $excelWriter = new Xlsx($excel);
        $fileName = 'BuscadoBancaBancos_' . getNow('Y-m-d-H-i-s') .  '.xlsx';
        $excelFileName = __DIR__ . '/reporte/' . $fileName;
        $excelWriter->save($excelFileName);
        return $response->withJson(['file' => "/api/reporte/" . $fileName]);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});


$app->get('/reporte/ListarRegVenta', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $fechaIni = $request->getParam('fechaIni');
        $fechaFin = $request->getParam('fechaFin');
        $declarado = $request->getParam('declarado');
        $almacen = $request->getParam('almacen');
        $tipo = $request->getParam('tipo');



        $Ssql = "SELECT Ve_DocVenta.*,Ve_DocVentaCliente.Cliente,
		Ve_DocVentaTipoDoc.CodSunat, Ve_DocVentaTipoDoc.TipoDoc, 
        IFNULL((Select Sum(Round((Ve_DocVentaDet.Precio* Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento,2)) FROM Ve_DocVentaDet Where Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.idDocVenta),0) as SubTotal,
	   IF(Ve_DocVentaTipoDoc.TieneIgv = 1,
	   ROUND((IFNULL((SELECT SUM(Round((Ve_DocVentaDet.Precio* Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento,2)) FROM Ve_DocVentaDet WHERE Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.idDocVenta),0)) -
	   (IFNULL((SELECT SUM(Round((Ve_DocVentaDet.Precio* Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento,2)) FROM Ve_DocVentaDet WHERE Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.idDocVenta),0)) / 1.18, 2)
	   , 0) as Igv,
	   IFNULL((Select Sum(Round((Ve_DocVentaDet.Precio* Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento,2)) FROM Ve_DocVentaDet Where Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.idDocVenta),0) as Total
	   FROM Ve_DocVenta 
       INNER JOIN Ve_DocVentaTipoDoc On Ve_DocVenta.IdTipoDoc=Ve_DocVentaTipoDoc.IdTipoDoc
       JOIN Ve_DocVentaCliente on Ve_DocVentaCliente.IdCliente = Ve_DocVenta.IdCliente
	   WHERE Ve_DocVentaTipoDoc.VaRegVenta=$declarado";

        if (!empty($almacen) || $almacen != 0) {
            $Ssql .= " AND Ve_DocVenta.IdAlmacen = $almacen ";
        }

        if ($tipo == 1) {
            $Ssql .= " AND  Ve_DocVenta.IdTipoDoc in (11,12) ";
        }
        if ($tipo == 2) {
            $Ssql .= " AND Ve_DocVenta.IdTipoDoc in  (6,7,8,9,10) ";
        }


        $Ssql .= " AND CAST(Ve_DocVenta.Fechadoc AS DATE) BETWEEN CAST('$fechaIni' AS DATE) and  CAST('$fechaFin' AS DATE)
		  ORDER BY  Ve_DocVenta.Fechadoc DESC;";
        $stmt = $this->db->query($Ssql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['errorr' => $e->getMessage()], 500);
    }
});

$app->get('/reporte/ListarRegVentaDetallado', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $fechaIni = $request->getParam('fechaIni');
        $fechaFin = $request->getParam('fechaFin');
        $declarado = $request->getParam('declarado');
        $almacen = $request->getParam('almacen');
        $tipo = $request->getParam('tipo');



        $Ssql = "SELECT Ve_DocVenta.idDocVenta, Ve_DocVenta.UsuarioReg, Ve_DocVenta.FechaDoc,Ve_DocVenta.IdTipoDoc,
		Ve_DocVentaTipoDoc.CodSunat, Ve_DocVentaTipoDoc.TipoDoc, Ve_DocVenta.Anulado,Ve_DocVenta.ValeProcesado,Ve_DocVenta.ComprobanteProcesado,Ve_DocVenta.RefVale,
	    Ve_DocVenta.Serie, Ve_DocVenta.Numero, 
        Ve_DocVentaDet.Precio,
        Ve_DocVentaDet.Cantidad,
        Ve_DocVentaDet.Descuento,
        Ve_DocVentaTipoDoc.TieneIgv,
        Gen_Producto.Producto
	    FROM Ve_DocVenta 
        JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta = Ve_DocVentaDet.IdDocVenta
        JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto 
        JOIN Ve_DocVentaTipoDoc On Ve_DocVenta.IdTipoDoc=Ve_DocVentaTipoDoc.IdTipoDoc
	    WHERE Ve_DocVentaTipoDoc.VaRegVenta=$declarado";

        if (!empty($almacen) || $almacen != 0) {
            $Ssql .= " AND Ve_DocVenta.IdAlmacen = $almacen ";
        }

        if ($tipo == 1) {
            $Ssql .= " AND  Ve_DocVenta.IdTipoDoc in (11,12) ";
        }
        if ($tipo == 2) {
            $Ssql .= " AND Ve_DocVenta.IdTipoDoc in  (6,7,8,9,10) ";
        }


        $Ssql .= " AND CAST(Ve_DocVenta.Fechadoc AS DATE) BETWEEN CAST('$fechaIni' AS DATE) and  CAST('$fechaFin' AS DATE)
		  ORDER BY  Ve_DocVenta.Fechadoc DESC;";
        $stmt = $this->db->query($Ssql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['errorr' => $e->getMessage()], 500);
    }
});

$app->get('/reporte/ListarRegVenta/detalle', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $idDocVenta = $request->getParam('idDocVenta');
        $Ssql = "SELECT Gen_Producto.IdProducto, Gen_Producto.Producto, Gen_Producto.Codigo, Gen_Producto.CodigoBarra, Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica, Gen_ProductoMedicion.ProductoMedicion, Ve_DocVentaDet.IdDocVenta, Ve_DocVentaDet.Cantidad, Ve_DocVentaDet.Precio, Ve_DocVentaDet.Descuento, Gen_ProductoMarca.ProductoMarca
		FROM Gen_Producto
		INNER JOIN Ve_DocVentaDet ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
		INNER JOIN Gen_ProductoFormaFarmaceutica ON Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica = Gen_Producto.IdProductoFormaFarmaceutica
		INNER JOIN Gen_ProductoMedicion ON Gen_ProductoMedicion.IdProductoMedicion = Gen_Producto.IdProductoMedicion
		INNER JOIN Gen_ProductoMarca ON Gen_ProductoMarca.IdProductoMarca = Gen_Producto.IdProductoMarca
		WHERE Ve_DocVentaDet.IdDocVenta = $idDocVenta;";


        $stmt = $this->db->query($Ssql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['errorr' => $e->getMessage()], 500);
    }
});


$app->put('/reporte/ListarRegVenta/anular', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $idDocVenta = $request->getParam('idDocVenta');

        $update = $this->db->update(array(
            'Anulado' => 1
        ))
            ->table('Ve_DocVenta')
            ->where('idDocVenta', '=', $idDocVenta);
        $affectedRows = $update->execute();


        return $response->withJson($affectedRows);
    } catch (Exception $e) {
        return $response->withJson(['errorr' => $e->getMessage()], 500);
    }
});

$app->get('/reporte/ListarRegVenta/exceldetallado', function (Request $request, Response $response, array $args) use ($app) {
    try {


        $fechaIni = $request->getParam('fechaIni');
        $fechaFin = $request->getParam('fechaFin');
        $declarado = $request->getParam('declarado');
        $almacen = $request->getParam('almacen');
        $tipo = $request->getParam('tipo');

        $Ssql = "
            SELECT 
            vtd.TipoDoc, vtd.CodSunat, v.*, 
            p.Producto,vd.Cantidad,vd.Precio,vc.Cliente,vc.DniRuc,
            round(vd.Cantidad*vd.Precio,2) SubTotal

            FROM
            Ve_DocVentaDet vd
            JOIN Ve_DocVenta v ON v.idDocVenta = vd.IdDocVenta
            JOIN Ve_DocVentaTipoDoc vtd On v.IdTipoDoc=vtd.IdTipoDoc
            JOIN Ve_DocVentaCliente vc ON vc.IdCliente = v.IdCliente 
            JOIN Gen_Producto p ON p.IdProducto = vd.IdProducto
            WHERE vtd.VaRegVenta=$declarado";


        if (!empty($almacen) || $almacen != 0) {
            $Ssql .= " AND v.IdAlmacen = $almacen ";
        }




        $Ssql .= " AND CAST(v.Fechadoc AS DATE) BETWEEN CAST('$fechaIni' AS DATE) and  CAST('$fechaFin' AS DATE)
		  ORDER BY  v.Fechadoc DESC;";
        $stmt = $this->db->query($Ssql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $excel = new Spreadsheet();

        $sheet = $excel->getActiveSheet();
        $excel->getActiveSheet()->mergeCells('A1:J1');
        $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal('center');
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
        $sheet->setCellValue('A1', 'REPORTE REGISTRO VENTAS: ' . RAZON_SOCIAL_EMPRESA);
        $sheet->setCellValue('A2', 'RUC:' . NRO_DOCUMENTO_EMPRESA);
        $sheet->setCellValue('A3', DIRECCION_EMPRESA);


        $sheet->setCellValue('A4', 'Fecha');
        $sheet->setCellValue('B4', 'Id_Tipo_Comprobante');
        $sheet->setCellValue('C4', 'ESTADO');
        $sheet->setCellValue('D4', 'Serie');
        $sheet->setCellValue('E4', 'Numero');
        $sheet->setCellValue('F4', 'Documento');
        $sheet->setCellValue('G4', 'Nombre');
        $sheet->setCellValue('H4', 'subtotal');
        $sheet->setCellValue('I4', 'IGV');
        $sheet->setCellValue('J4', 'P. VENTA');
        $sheet->setCellValue('K4', 'Id_Conversion');
        $sheet->setCellValue('L4', 'Nombre_Producto');
        $sheet->setCellValue('M4', 'Precio');
        $sheet->setCellValue('N4', 'Cantidad');
        $sheet->setCellValue('O4', 'FacturaVale');

        $cont = 5;
        $sumatoria = 0;

        foreach ($data as $key => $row) {
            $total = $row["Total"];
            $TipoDoc = $row['TipoDoc'];
            if ($row['IdTipoDoc'] == 11 || $row['IdTipoDoc'] == 12) {
                if ($row['ValeProcesado'] == 0) {
                    $TipoDoc .= " (NO SUMA) (PENDIENTE)";
                } else {
                    $TipoDoc .= " (" . $row['ComprobanteProcesado'] . ")";
                }
            }
            $Estado = 'Activo';
            if ($row['Estado'] == 0) {
                $Estado = 'Anulado';
            }
            $igv = 0;

            $sumatoria += $total;
            $sheet->setCellValue('A' . $cont, $row['FechaReg']); // Fecha
            $sheet->setCellValue('B' . $cont, $row['TipoDoc']); // Id_Tipo_Comprobante
            $sheet->setCellValue('C' . $cont, $Estado); // ESTADO
            $sheet->setCellValue('D' . $cont, $row['Serie']); // Serie
            $sheet->setCellValue('E' . $cont, $row['Numero']); // Numero
            $sheet->setCellValue('F' . $cont, $row['DniRuc']); // Documento
            $sheet->setCellValue('G' . $cont, $row['Cliente']); // Nombre
            $sheet->setCellValue('H' . $cont, $row['SubTotal']); // subtotal
            $sheet->setCellValue('I' . $cont, $igv); // IGV
            $sheet->setCellValue('J' . $cont, $row['SubTotal']); // P
            $sheet->setCellValue('K' . $cont, ''); // Id_Conversion
            $sheet->setCellValue('L' . $cont, $row['Producto']); // Nombre_Producto
            $sheet->setCellValue('M' . $cont, $row['Precio']); // Precio
            $sheet->setCellValue('N' . $cont, $row['Cantidad']); // Cantidad
            $sheet->setCellValue('O' . $cont, ''); // FacturaVale

            $cont++;
        }

        foreach (range('B', 'H') as $columnID) {
            $excel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }



        $excel->getActiveSheet()->getStyle('A5:J6')->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('A2:A4')->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('B4')->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('A1:A7')->getAlignment()->setHorizontal('left');
        $excel->getActiveSheet()->mergeCells('A' . ($cont + 1) . ':J' . ($cont + 1));
        $excel->getActiveSheet()->getStyle('A' . ($cont + 1))->getAlignment()->setHorizontal('center');
        $excel->getActiveSheet()->getStyle('A' . ($cont + 1))->getFont()->setBold(true);
        $sheet->setCellValue('A' . ($cont + 1), 'SUMATORIA');
        $sheet->setCellValue('K' . ($cont + 1), $sumatoria);
        $excelWriter = new Xlsx($excel);

        $fileName = 'Reg_Venta_Detallado' . '' . getNow('Y-m-d-H-i-s') .  '.xlsx';
        $excelFileName = __DIR__ . '/reporte/' . $fileName;
        $excelWriter->save($excelFileName);

        return $response->withJson($_SERVER['HTTP_HOST'] . '/api/reporte/' .  $fileName);
    } catch (Exception $e) {
        return $response->withJson(['errorr' => $e->getMessage()], 500);
    }
});


$app->get('/reporte/ListarRegVenta/excel', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $fechaIni = $request->getParam('fechaIni');
        $fechaFin = $request->getParam('fechaFin');
        $declarado = $request->getParam('declarado');
        $almacen = $request->getParam('almacen');
        $res = $app->subRequest('GET', 'reporte/ListarRegVenta', '?fechaIni=' . $fechaIni . '&fechaFin=' . $fechaFin . '&declarado=' . $declarado . '&almacen=' . $almacen);


        $deudacliente = (string) $res->getBody();
        $data = json_decode($deudacliente, true);



        $excel = new Spreadsheet();
        //$sheet = $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet();
        $excel->getActiveSheet()->mergeCells('A1:J1');
        $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal('center');
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
        $sheet->setCellValue('A1', 'REPORTE REGISTRO VENTAS: ' . RAZON_SOCIAL_EMPRESA);
        $sheet->setCellValue('A2', 'RUC:' . NRO_DOCUMENTO_EMPRESA);
        $sheet->setCellValue('A3', DIRECCION_EMPRESA);

        $sheet->setCellValue('A4', 'Id');
        $sheet->setCellValue('B4', 'Vendedor');
        $sheet->setCellValue('C4', 'Fecha Doc');
        $sheet->setCellValue('D4', 'Cod. Sunat');
        $sheet->setCellValue('E4', 'Tipo Doc.');
        $sheet->setCellValue('F4', 'Anulado');
        $sheet->setCellValue('G4', 'Serie');
        $sheet->setCellValue('H4', 'Número');
        $sheet->setCellValue('I4', 'SubTotal');
        $sheet->setCellValue('J4', 'IGV');
        $sheet->setCellValue('K4', 'Total');



        $cont = 5;
        $sumatoria = 0;
        foreach ($data as $key => $row) {
            $total = $row["Total"];
            $TipoDoc = $row['TipoDoc'];
            if ($row['IdTipoDoc'] == 11 || $row['IdTipoDoc'] == 12) {
                if ($row['ValeProcesado'] == 0) {
                    $TipoDoc .= " (NO SUMA) (PENDIENTE)";
                } else {
                    $TipoDoc .= " (" . $row['ComprobanteProcesado'] . ")";
                }
            }
            $sumatoria += $total;
            $sheet->setCellValue('A' . $cont, $row['idDocVenta']);
            $sheet->setCellValue('B' . $cont, $row['UsuarioReg']);
            $sheet->setCellValue('C' . $cont, $row['FechaDoc']);
            $sheet->setCellValue('D' . $cont, $row['CodSunat']);
            $sheet->setCellValue('E' . $cont, $TipoDoc);
            $sheet->setCellValue('F' . $cont, $row['Anulado']);
            $sheet->setCellValue('G' . $cont, $row['Serie']);
            $sheet->setCellValue('H' . $cont, $row['Numero']);
            $sheet->setCellValue('I' . $cont, $row['SubTotal']);
            $sheet->setCellValue('J' . $cont, $row['Igv']);
            $sheet->setCellValue('K' . $cont, $row['Total']);
            $cont++;
        }


        foreach (range('B', 'H') as $columnID) {
            $excel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }



        $excel->getActiveSheet()->getStyle('A5:J6')->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('A2:A4')->getFont()->setBold(true);
        // $excel->getActiveSheet()->getStyle('A8:H8')->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('B4')->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('A1:A7')->getAlignment()->setHorizontal('left');
        // $excel->getActiveSheet()->getStyle('A8:H8')->getAlignment()->setHorizontal('center');
        // $excel->getActiveSheet()->getStyle('C6:B' . $cont)->getAlignment()->setHorizontal('center');
        // $excel->getActiveSheet()->getStyle('B6:B' . $cont)->getAlignment()->setHorizontal('center');
        // $excel->getActiveSheet()->getStyle('D6:B' . $cont)->getAlignment()->setHorizontal('center');
        // $excel->getActiveSheet()->getStyle('E6:B' . $cont)->getAlignment()->setHorizontal('center');
        // // $excel->getActiveSheet()->getStyle('A8:H8')->getFill()
        //     ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        //     ->getStartColor()->setARGB('FFE6E6FA');

        //Totales
        $excel->getActiveSheet()->mergeCells('A' . ($cont + 1) . ':J' . ($cont + 1));
        $excel->getActiveSheet()->getStyle('A' . ($cont + 1))->getAlignment()->setHorizontal('center');
        $excel->getActiveSheet()->getStyle('A' . ($cont + 1))->getFont()->setBold(true);
        $sheet->setCellValue('A' . ($cont + 1), 'SUMATORIA');
        $sheet->setCellValue('K' . ($cont + 1), $sumatoria);
        //$excel->getActiveSheet()->getStyle('H'.$cont.':J'.$cont)->getFont()->setBold( true );


        $excelWriter = new Xlsx($excel);

        $fileName = 'Reg_Venta_' . '' . getNow('Y-m-d-H-i-s') .  '.xlsx';
        $excelFileName = __DIR__ . '/reporte/' . $fileName;
        $excelWriter->save($excelFileName);

        return $response->withJson($_SERVER['HTTP_HOST'] . '/api/reporte/' .  $fileName);
    } catch (Exception $e) {
        return $response->withJson(['errorr' => $e->getMessage()], 500);
    }
});

$app->get('/reporte/ListarRegVenta/excelDetallado', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $fechaIni = $request->getParam('fechaIni');
        $fechaFin = $request->getParam('fechaFin');
        $declarado = $request->getParam('declarado');
        $almacen = $request->getParam('almacen');
        $res = $app->subRequest('GET', 'reporte/ListarRegVentaDetallado', '?fechaIni=' . $fechaIni . '&fechaFin=' . $fechaFin . '&declarado=' . $declarado . '&almacen=' . $almacen);


        $deudacliente = (string) $res->getBody();
        $data = json_decode($deudacliente, true);



        $excel = new Spreadsheet();
        //$sheet = $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet();
        $excel->getActiveSheet()->mergeCells('A1:J1');
        $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal('center');
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
        $sheet->setCellValue('A1', 'REPORTE REGISTRO VENTAS: ' . RAZON_SOCIAL_EMPRESA);
        $sheet->setCellValue('A2', 'RUC:' . NRO_DOCUMENTO_EMPRESA);
        $sheet->setCellValue('A3', DIRECCION_EMPRESA);

        $sheet->setCellValue('A4', 'Id');
        $sheet->setCellValue('B4', 'Vendedor');
        $sheet->setCellValue('C4', 'Fecha Doc');
        $sheet->setCellValue('D4', 'Cod. Sunat');
        $sheet->setCellValue('E4', 'Tipo Doc.');
        $sheet->setCellValue('F4', 'Anulado');
        $sheet->setCellValue('G4', 'Serie');
        $sheet->setCellValue('H4', 'Número');
        $sheet->setCellValue('I4', 'Producto');
        $sheet->setCellValue('I4', 'Precio');
        $sheet->setCellValue('I4', 'Cantidad');
        $sheet->setCellValue('I4', 'SubTotal');
        $sheet->setCellValue('I4', 'Descuento');
        $sheet->setCellValue('J4', 'IGV');
        $sheet->setCellValue('K4', 'Total');



        $cont = 5;
        $sumatoria = 0;
        foreach ($data as $key => $row) {
            $total = $row["Total"];
            $TipoDoc = $row['TipoDoc'];
            if ($row['IdTipoDoc'] == 11 || $row['IdTipoDoc'] == 12) {
                if ($row['ValeProcesado'] == 0) {
                    $TipoDoc .= " (NO SUMA) (PENDIENTE)";
                } else {
                    $TipoDoc .= " (" . $row['ComprobanteProcesado'] . ")";
                }
            }
            $subtotal = $row['Precio'] * $row['Cantidad'];
            if ($row['IGV'] == 1) {
                $igv = ($row['Precio'] * $row['Cantidad'] - $row['Descuento']) * 0.18;
            }
            $total = $row['Precio'] * $row['Cantidad'] - $row['Descuento'] + $igv;
            $sumatoria += $total;
            $sheet->setCellValue('A' . $cont, $row['idDocVenta']);
            $sheet->setCellValue('B' . $cont, $row['UsuarioReg']);
            $sheet->setCellValue('C' . $cont, $row['FechaDoc']);
            $sheet->setCellValue('D' . $cont, $row['CodSunat']);
            $sheet->setCellValue('E' . $cont, $TipoDoc);
            $sheet->setCellValue('F' . $cont, $row['Anulado']);
            $sheet->setCellValue('G' . $cont, $row['Serie']);
            $sheet->setCellValue('H' . $cont, $row['Numero']);
            $sheet->setCellValue('H' . $cont, $row['Producto']);
            $sheet->setCellValue('H' . $cont, $row['Precio']);
            $sheet->setCellValue('H' . $cont, $row['Cantidad']);
            $sheet->setCellValue('H' . $cont, $subtotal);
            $sheet->setCellValue('H' . $cont, $row['Descuento']);
            $sheet->setCellValue('H' . $cont, $igv);
            $sheet->setCellValue('H' . $cont, $total);

            $cont++;
        }


        foreach (range('B', 'H') as $columnID) {
            $excel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }



        $excel->getActiveSheet()->getStyle('A5:J6')->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('A2:A4')->getFont()->setBold(true);
        // $excel->getActiveSheet()->getStyle('A8:H8')->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('B4')->getFont()->setBold(true);
        $excel->getActiveSheet()->getStyle('A1:A7')->getAlignment()->setHorizontal('left');
        // $excel->getActiveSheet()->getStyle('A8:H8')->getAlignment()->setHorizontal('center');
        // $excel->getActiveSheet()->getStyle('C6:B' . $cont)->getAlignment()->setHorizontal('center');
        // $excel->getActiveSheet()->getStyle('B6:B' . $cont)->getAlignment()->setHorizontal('center');
        // $excel->getActiveSheet()->getStyle('D6:B' . $cont)->getAlignment()->setHorizontal('center');
        // $excel->getActiveSheet()->getStyle('E6:B' . $cont)->getAlignment()->setHorizontal('center');
        // // $excel->getActiveSheet()->getStyle('A8:H8')->getFill()
        //     ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        //     ->getStartColor()->setARGB('FFE6E6FA');

        //Totales
        $excel->getActiveSheet()->mergeCells('A' . ($cont + 1) . ':J' . ($cont + 1));
        $excel->getActiveSheet()->getStyle('A' . ($cont + 1))->getAlignment()->setHorizontal('center');
        $excel->getActiveSheet()->getStyle('A' . ($cont + 1))->getFont()->setBold(true);
        $sheet->setCellValue('A' . ($cont + 1), 'SUMATORIA');
        $sheet->setCellValue('K' . ($cont + 1), $sumatoria);
        //$excel->getActiveSheet()->getStyle('H'.$cont.':J'.$cont)->getFont()->setBold( true );


        $excelWriter = new Xlsx($excel);

        $fileName = 'Reg_Venta_' . '' . getNow('Y-m-d-H-i-s') .  '.xlsx';
        $excelFileName = __DIR__ . '/reporte/' . $fileName;
        $excelWriter->save($excelFileName);

        return $response->withJson($_SERVER['HTTP_HOST'] . '/api/reporte/' .  $fileName);
    } catch (Exception $e) {
        return $response->withJson(['errorr' => $e->getMessage()], 500);
    }
});

$app->get('/exportar/ventas/zip', function (Request $request, Response $response, array $args) use ($app) {
    try {
        // ======================
        // PARÁMETROS
        // ======================
        $fechaIni   = $request->getParam('fechaIni');
        $fechaFin   = $request->getParam('fechaFin');
        $declarado  = filter_var($request->getParam('declarado'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        // ======================
        // DATOS DE LA EMPRESA
        // ======================
        $rucEmpresa    = NRO_DOCUMENTO_EMPRESA;   // ejemplo: 20613991001
        $nombreEmpresa = RAZON_SOCIAL_EMPRESA;

        // ======================
        // RUTA BASE
        // ======================
        $basePath = dirname(__FILE__) . "/sunat/api_cpe/PRODUCCION/{$rucEmpresa}/";
        if (!is_dir($basePath)) mkdir($basePath, 0777, true);

        // Limpiar archivos previos

        // ======================
        // CONSULTA DE VENTAS
        // ======================
        $sql = "
            SELECT 
                td.CodSunat AS tipoComprobante,
                v.Serie,
                LPAD(v.Numero, 8, '0') AS Numero,
                DATE_FORMAT(v.FechaReg, '%Y-%m-%d') AS fechaEmision,
                DATE_FORMAT(v.FechaReg, '%Y-%m-%d') AS fechaVencimiento,
                c.DniRuc,
                c.Cliente,
                'PEN' AS Moneda,
                1 AS tipoCambio,
                ROUND(SUM(vd.Cantidad * vd.Precio), 2) AS total
            FROM Ve_DocVenta v 
            JOIN Ve_DocVentaDet vd ON v.idDocVenta = vd.IdDocVenta 
            JOIN Ve_DocVentaCliente c ON c.IdCliente = v.IdCliente
            JOIN Ve_DocVentaTipoDoc td ON td.IdTipoDoc = v.IdTipoDoc
            WHERE td.VaRegVenta = :declarado
              AND td.CodSunat IN ('01','03','07','08')
              AND DATE(v.FechaReg) BETWEEN :fechaIni AND :fechaFin
            GROUP BY td.CodSunat, v.Serie, v.Numero, v.FechaReg, c.DniRuc, c.Cliente
            ORDER BY v.FechaReg, v.Serie, v.Numero
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':declarado' => $declarado,
            ':fechaIni' => $fechaIni,
            ':fechaFin' => $fechaFin
        ]);
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($ventas)) {
            return $response->withJson(['error' => 'No se encontraron registros válidos para SUNAT.'], 404);
        }

        // ======================
        // NOMBRE DEL ARCHIVO (25 caracteres EXACTOS)
        // ======================
        $prefijo = 'LE';
        $periodo = date('Y', strtotime($fechaIni)) . str_pad(date('m', strtotime($fechaIni)), 2, '0', STR_PAD_LEFT) . '00';
        $codigoLibro = '140400'; // Registro de Ventas e Ingresos
        $codigoOperacion = '02'; // Primer envío (o 14 para reemplazo si fuera el caso)
        $indicadorContenido = '1'; // Con información
        $indicadorMoneda = '1'; // Soles (1 = PEN, 2 = USD)
        $generadoPor = '1'; // Sistema propio
        $identificadorEnvio = '2'; // Primer envío
        $nombreBase = "{$prefijo}{$rucEmpresa}{$periodo}{$codigoLibro}{$codigoOperacion}{$indicadorContenido}{$indicadorMoneda}{$generadoPor}{$identificadorEnvio}";
        $nombreTxt = "{$nombreBase}.txt";
        $nombreZip = "{$nombreBase}.zip";
        $rutaTxt = "{$basePath}{$nombreTxt}";
        $rutaZip = "{$basePath}{$nombreZip}";

        if (strlen($nombreBase) !== 33) {
            throw new Exception("El nombre del archivo debe tener 33 caracteres, tiene " . strlen($nombreBase) . ". Nombre generado: {$nombreBase}");
        }

        // ======================
        // GENERAR ARCHIVO TXT
        // ======================
        $contenido = '';
        $linea = 0;

        $contenido = '';
        $linea = 0;

        foreach ($ventas as $v) {
            $linea++;
            $periodo = date('Ym', strtotime($fechaIni)) . '00';
            $cuo = str_pad($linea, 5, '0', STR_PAD_LEFT);

            $rucCliente = trim($v['DniRuc']);
            $tipoDocCliente = (strlen($rucCliente) === 11) ? '6' : ((strlen($rucCliente) === 8) ? '1' : '0');

            $razonSocial = strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', $v['Cliente']));
            $razonSocial = preg_replace('/[^A-Z0-9\s\.\-]/', '', $razonSocial);
            $razonSocial = preg_replace('/\s+/', ' ', $razonSocial);
            $razonSocial = trim($razonSocial);
            $total = (float)$v['total'];
            if ($v['tipoComprobante'] === '07') {
                $total *= -1;
            }
            $baseImponible = $total / 1.18;
            $igv = $total - $baseImponible;
            $total = number_format($total, 2, '.', '');
            $baseImponible = number_format($baseImponible, 2, '.', '');
            $igv = number_format($igv, 2, '.', '');

            $campos = [
                $periodo,               // 1
                $cuo,                   // 2
                "",                     // 3
                $v['fechaEmision'],     // 4
                $v['fechaVencimiento'], // 5
                $v['tipoComprobante'],  // 6
                $v['Serie'],            // 7
                $v['Numero'],           // 8
                "",                     // 9 (vacío)
                $tipoDocCliente,        // 10
                $rucCliente,            // 11
                $razonSocial,           // 12
                $baseImponible,         // 13
                $igv,                   // 14
                "0.00",
                "0.00",
                "0.00",
                "0.00",
                "0.00",
                "0.00", // 15–20
                $total,                 // 21
                $v['Moneda'],           // 22
                $tipoCambio             // 23
            ];

            for ($i = count($campos); $i < 79; $i++) {
                $campos[] = "";
            }

            $campos[] = "1"; // Estado = 1 (con información)
            $contenido .= implode('|', $campos) . "\r\n";
        }
        // Convertir a ISO y guardar
        $contenido = mb_convert_encoding(trim($contenido) . "\r\n", 'ISO-8859-1', 'UTF-8');
        file_put_contents($rutaTxt, $contenido, LOCK_EX);

        // ======================
        // CREAR ZIP
        // ======================
        $zip = new ZipArchive();
        if ($zip->open($rutaZip, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $zip->addFile($rutaTxt, basename($rutaTxt));
            $zip->close();
        } else {
            throw new Exception("No se pudo crear el archivo ZIP.");
        }

        // ======================
        // RESPUESTA FINAL
        // ======================
        $host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

        return $response->withJson([
            'success' => true,
            'rucEmpresa' => $rucEmpresa,
            'nombreArchivo' => $nombreBase,
            'longitud' => strlen($nombreBase),
            'txt' => "{$host}/api/sunat/api_cpe/PRODUCCION/{$rucEmpresa}/{$nombreTxt}",
            'zip' => "{$host}/api/sunat/api_cpe/PRODUCCION/{$rucEmpresa}/{$nombreZip}"
        ], 200);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});


$app->get('/exportar/compras/zip', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $fechaIni   = $request->getParam('fechaIni');
        $fechaFin   = $request->getParam('fechaFin');
        $declarado  = filter_var($request->getParam('declarado'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        $rucEmpresa     = NRO_DOCUMENTO_EMPRESA;
        $nombreEmpresa  = RAZON_SOCIAL_EMPRESA;

        $basePath = dirname(__FILE__) . "/sunat/api_cpe/PRODUCCION/{$rucEmpresa}/";
        if (!is_dir($basePath)) mkdir($basePath, 0777, true);

        $sql = "SELECT DISTINCT
                p.Ruc AS rucProveedor,
                p.Proveedor AS razonSocialProveedor,
                DATE_FORMAT(m.MovimientoFecha, '%Y-%m-%d') AS fechaEmision,
                DATE_FORMAT(m.FechaVenCredito, '%Y-%m-%d') AS fechaVencimiento,
                m.Serie AS serie,
                m.Numero AS numero,
                mt.CodSunat tipoComprobante,
                m.Moneda AS moneda,
                m.NroComprobanteModifica,
                m.NotaIdMotivo,
                m.NotaDescMotivo,
                IFNULL(m.TipoCambio, 0) AS tipoCambio,
                ROUND(SUM(md.Cantidad * md.Precio), 2) AS total,
                ROUND(SUM(CASE WHEN md.TieneIgv = 1 THEN (md.Cantidad * md.Precio) / 1.18 ELSE 0 END), 2) AS valorVentaGravada,
                ROUND(SUM(CASE WHEN md.TieneIgv = 1 THEN ((md.Cantidad * md.Precio) - ((md.Cantidad * md.Precio) / 1.18)) ELSE 0 END), 2) AS montoIgv,
                ROUND(SUM(CASE WHEN md.TieneIgv = 0 THEN (md.Cantidad * md.Precio) ELSE 0 END), 2) AS valorVentaNoGravada
            FROM Lo_Movimiento m
            JOIN Lo_MovimientoDetalle md ON md.hashMovimiento = m.Hash
            JOIN Lo_MovimientoTipo mt ON m.IdMovimientoTipo = mt.IdMovimientoTipo
            JOIN Lo_Proveedor p ON m.IdProveedor = p.IdProveedor
            WHERE mt.CodSunat IN ('01','03','07','08','14')
                AND DATE(m.MovimientoFecha) BETWEEN '$fechaIni' AND '$fechaFin'
            GROUP BY
                p.Ruc, p.Proveedor, m.MovimientoFecha, m.Serie, m.Numero, mt.CodSunat, m.Moneda, m.TipoCambio
            ORDER BY m.MovimientoFecha, m.Serie, m.Numero";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($compras)) {
            return $response->withJson(['error' => 'No se encontraron registros válidos para SUNAT.'], 404);
        }

        $anio = date('Y', strtotime($fechaIni));
        $mes  = str_pad(date('m', strtotime($fechaIni)), 2, '0', STR_PAD_LEFT);
        $dia  = '00';

        $codigoLibro = '080400';
        $oportunidad = '02';
        $indicadorOperaciones = '1';
        $indicadorContenido = '1';
        $indicadorMoneda = '1';
        $indicadorSire = '2';

        $nombreBase = "LE{$rucEmpresa}{$anio}{$mes}{$dia}{$codigoLibro}{$oportunidad}{$indicadorOperaciones}{$indicadorContenido}{$indicadorMoneda}{$indicadorSire}";
        $nombreTxt = "{$nombreBase}.txt";
        $nombreZip = "{$nombreBase}.zip";

        $rutaTxt = "{$basePath}{$nombreTxt}";
        $rutaZip = "{$basePath}{$nombreZip}";

        // ================================
        // 🧾 GENERAR CONTENIDO SIRE TXT
        // ================================
        $contenido = '';

        $contenido = '';

        foreach ($compras as $c) {
            $periodo = date('Ym', strtotime($fechaIni));
            $rucProveedor = trim($c['rucProveedor']);
            $tipoDocProveedor = (strlen($rucProveedor) === 11) ? '6' : ((strlen($rucProveedor) === 8) ? '1' : '0');

            // Limpieza de Razón Social
            $razonSocialProveedor = strtoupper(trim($c['razonSocialProveedor']));
            $razonSocialProveedor = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', 'Ü'], ['A', 'E', 'I', 'O', 'U', 'N', 'U'], $razonSocialProveedor);
            $razonSocialProveedor = preg_replace('/[^A-Z0-9\s\.\-]/', '', $razonSocialProveedor);
            $razonSocialProveedor = preg_replace('/\s+/', ' ', $razonSocialProveedor);

            // Manejo de montos y negativos para Notas de Crédito (07)
            $valorVentaGravada   = number_format($c['valorVentaGravada'], 2, '.', '');
            $montoIgv            = number_format($c['montoIgv'], 2, '.', '');
            $valorVentaNoGravada = number_format($c['valorVentaNoGravada'], 2, '.', '');

            // TOTAL: negativo SOLO para Nota de Crédito (07)
            if ($c['tipoComprobante'] === '07') {
                $total = number_format(abs($c['total']) * -1, 2, '.', '');
            } else {
                $total = number_format($c['total'], 2, '.', '');
            }
            // Inicializar variables de referencia SIEMPRE ANTES de usarlas
            $refFecha = '';
            $refTipo  = '';
            $refSerie = '';
            $refNum   = '';
            $MovimientoFecha = '';
            if ($c['tipoComprobante'] === '07' || $c['tipoComprobante'] === '08') {
                $partes = explode('-', $c['NroComprobanteModifica']);
                if (count($partes) === 2) {
                    $refSerie = trim($partes[0]);
                    $refNum   = trim($partes[1]);

                    // 1. Corregimos la consulta y la extracción del dato
                    $sqlOrig = "SELECT MovimientoFecha FROM Lo_Movimiento WHERE Serie=? AND Numero=? LIMIT 1";
                    $stmtOrig = $this->db->prepare($sqlOrig);
                    $stmtOrig->execute([$refSerie, $refNum]);
                    $resultado = $stmtOrig->fetch(PDO::FETCH_ASSOC);

                    if ($resultado && !empty($resultado['MovimientoFecha'])) {
                        // 2. Convertimos el valor del array a formato dd/mm/yyyy
                        $refFecha = date('d/m/Y', strtotime($resultado['MovimientoFecha']));
                    } else {
                        // Si no encuentra la factura, SUNAT igual pide una fecha (ponemos la de la NC por seguridad)
                        $refFecha = date('d/m/Y', strtotime($c['fechaEmision']));
                    }

                    // Determinar tipo modificado
                    $primeraLetra = strtoupper(substr($refSerie, 0, 1));
                    $refTipo = ($primeraLetra === 'B') ? '03' : '01';
                }
            }

            // Lógica de columnas para evitar Error 325
            $c15 = '0.00';
            $c16 = '0.00';
            $c21 = '0.00';
            $c32 = '';
            $c33 = '';
            if ($c['valorVentaGravada'] > 0) {
                $c15 = $valorVentaGravada;
                $c16 = $montoIgv;
                $c33 = "1";
            } elseif ($c['valorVentaNoGravada'] > 0) {
                if ($c['tipoComprobante'] === '07') {
                    $c21 = number_format(abs($c['valorVentaNoGravada']) * -1, 2, '.', '');
                    $c32 = "";    // Limpiamos el "04" que causaba error de longitud
                    $c33 = "1";   // Cambiamos "1-0" por "1" (Mercadería) o "5" (Gastos)
                } else {
                    $c21 = $valorVentaNoGravada;
                    $c33 = "1";
                }
            }

            // === 38 CAMPOS EXACTOS ===
            $campos = [
                $rucEmpresa,            // 1
                $nombreEmpresa,         // 2
                $periodo,               // 3
                '',                     // 4
                date('d/m/Y', strtotime($c['fechaEmision'])), // 5
                date('d/m/Y', strtotime($c['fechaVencimiento'] ?: $c['fechaEmision'])), // 6
                $c['tipoComprobante'],  // 7
                $c['serie'],            // 8
                '',                     // 9
                $c['numero'],           // 10
                '',                     // 11
                $tipoDocProveedor,      // 12
                $rucProveedor,          // 13
                $razonSocialProveedor,  // 14
                $c15,                   // 15
                $c16,                   // 16
                '0.00',                 // 17
                '0.00',                 // 18
                '0.00',                 // 19
                '0.00',                 // 20
                $c21,                   // 21
                '0.00',                 // 22
                '0.00',                 // 23
                '0.00',                 // 24
                $total,                 // 25
                $c['moneda'],           // 26
                '',                     // 27
                $refFecha,              // 28
                $refTipo,               // 29
                $refSerie,              // 30
                '',                     // 31
                $refNum,                // 32
                '',                     // 33
                '',                     // 34
                '',                     // 35
                '0.00',                  // 36
                '',                     // 37
                '',                     // 37
                '',                     // 37
                '',                     // 37
                ''                      // 38
            ];

            $contenido .= implode('|', $campos) . "\r\n";
        }

        $contenido = mb_convert_encoding(trim($contenido) . "\r\n", 'ISO-8859-1', 'UTF-8');
        file_put_contents($rutaTxt, $contenido, LOCK_EX);

        // ================================
        // 🗜️ CREAR ZIP
        // ================================
        $zip = new ZipArchive();
        if ($zip->open($rutaZip, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $zip->addFile($rutaTxt, basename($rutaTxt));
            $zip->close();
        }

        $host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        return $response->withJson([
            'success' => true,
            'txt' => "{$host}/api/sunat/api_cpe/PRODUCCION/{$rucEmpresa}/{$nombreTxt}",
            'zip' => "{$host}/api/sunat/api_cpe/PRODUCCION/{$rucEmpresa}/{$nombreZip}",
            'nombreArchivo' => $nombreBase
        ], 200);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/reporte/listarregmov', function (Request $request, Response $response, array $args) {
    try {

        $fechaIni = $request->getParam('fechaInicio');
        $fechaFin = $request->getParam('fechaFin');
        $declarado = $request->getParam('declarado') ? $request->getParam('declarado') : 0;
        $descripcion = $request->getParam('descripcion');

        $select = "SELECT
		Lo_Movimiento.`Hash` as IdMovimiento,
		Lo_Movimiento.MovimientoFecha,
		Lo_Movimiento.IdMovimientoTipo,
		Lo_MovimientoTipo.CodSunat,
		Lo_MovimientoTipo.TipoMovimiento,
		Lo_Movimiento.Serie,
		Lo_Movimiento.Numero,
		Lo_Movimiento.FechaPeriodoTributario,
		Lo_Proveedor.Proveedor,
	
		CASE WHEN Lo_Movimiento.IdAlmacenOrigen>0 THEN
			(Select Lo_Almacen.Almacen From Lo_Almacen Where Lo_Almacen.IdAlmacen=Lo_Movimiento.IdAlmacenOrigen)
		ELSE
			'-'
		END AS AlmacenOrigen,
		CASE WHEN Lo_Movimiento.IdAlmacenDestino>0 THEN
			(Select Lo_Almacen.Almacen From Lo_Almacen Where Lo_Almacen.IdAlmacen=Lo_Movimiento.IdAlmacenDestino)
		ELSE
			'-'
		END AS AlmacenDestino,
		Lo_Movimiento.Observacion,
		Lo_Movimiento.Anulado,
		(SELECT
		Sum(ROUND(Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio,2)) as SUBTOTAL
		FROM
		Lo_MovimientoDetalle
		WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as SUBTOTAL,
		(SELECT
		Sum(CASE WHEN Lo_MovimientoDetalle.TieneIgv=1 THEN
		ROUND((Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio)*
		(Select Igv From GEN_EMPRESA),2)
		ELSE
		0
		END) as IGV
		FROM
		Lo_MovimientoDetalle
		WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as IGV,
		(SELECT
		Sum(ROUND(Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio,2)) +
	 	Sum(ROUND(CASE WHEN ISNULL(Lo_MovimientoDetalle.ISC)=1 THEN  0 ELSE Lo_MovimientoDetalle.ISC END,2))
		+ SUM(ROUND(CASE WHEN ISNULL(Lo_MovimientoDetalle.FLETE)=1 THEN  0 ELSE Lo_MovimientoDetalle.FLETE END,2))
		+CASE WHEN ISNULL(Lo_Movimiento.Percepcion)=1 THEN 0 ELSE Lo_Movimiento.Percepcion END
		+Sum(CASE WHEN Lo_MovimientoDetalle.TieneIgv=1 THEN
		ROUND((Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio)*
	
		(Select Igv From GEN_EMPRESA),2)
	
		ELSE
		0
		END) as TOTAL
	
		FROM
	
		Lo_MovimientoDetalle
	
		WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as TOTAL,
	
		Lo_Movimiento.FechaReg,
		Lo_Movimiento.UsuarioReg,
		Lo_Movimiento.FechaMod,
		Lo_Movimiento.UsuarioMod,
	
		(SELECT SUM(ROUND(CASE WHEN ISNULL(Lo_MovimientoDetalle.ISC)=1 THEN  0 ELSE Lo_MovimientoDetalle.ISC END,2)) FROM Lo_MovimientoDetalle WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as ISC,
	
		(SELECT SUM(ROUND(CASE WHEN ISNULL(Lo_MovimientoDetalle.FLETE)=1 THEN  0 ELSE Lo_MovimientoDetalle.FLETE END,2)) FROM Lo_MovimientoDetalle WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as FLETE,
	
		CASE WHEN ISNULL(Lo_Movimiento.Percepcion)=1 THEN 0 ELSE Lo_Movimiento.Percepcion END AS Percepcion,
	
		Lo_Movimiento.TipoCambio,
		Lo_Movimiento.Moneda
	
		FROM
		
		Lo_Movimiento
	
		INNER JOIN Lo_MovimientoTipo On Lo_Movimiento.IdMovimientoTipo=Lo_MovimientoTipo.IdMovimientoTipo
		INNER JOIN Lo_Proveedor On Lo_Movimiento.IdProveedor=Lo_Proveedor.IdProveedor
		WHERE
		Lo_MovimientoTipo.VaRegCompra= $declarado 
		and DATE(Lo_Movimiento.MovimientoFecha) BETWEEN '$fechaIni' and  '$fechaFin'
		AND Lo_Proveedor.Proveedor LIKE CONCAT('%', '$proveedor', '%')
	
		ORDER BY Lo_Movimiento.MovimientoFecha DESC;";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $th) {
        return $response->withJson(['error' => $th->getMessage()], 500);
    }
});

$app->post('/movimiento/eliminar', $nstDeleteMovement);

$app->get('/movmiento/detalles', function (Request $request, Response $response, array $args) {
    $IdMovimiento = $request->getParam('IdMovimiento');
    try {
        $select = "Select Lo_MovimientoDetalle.hashMovimiento, Lo_MovimientoDetalle.Descripcion, Gen_Producto.Codigo, Gen_Producto.CodigoBarra, 
		Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica, Gen_Producto.Producto, 
		Gen_Producto.PrecioContado, Gen_ProductoMedicion.ProductoMedicion, Lo_MovimientoDetalle.Cantidad,Lo_MovimientoDetalle.FLETE,
		Lo_MovimientoDetalle.TieneIgv, Lo_MovimientoDetalle.Precio, Gen_ProductoMarca.ProductoMarca, Lo_MovimientoDetalle.ISC
		   FROM Lo_MovimientoDetalle
		   INNER JOIN Gen_Producto on Gen_Producto.IdProducto = Lo_MovimientoDetalle.IdProducto
		   INNER JOIN Gen_ProductoFormaFarmaceutica ON Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica = Gen_Producto.IdProductoFormaFarmaceutica
		   INNER JOIN Gen_ProductoMedicion ON Gen_ProductoMedicion.IdProductoMedicion = Gen_Producto.IdProductoMedicion
		   INNER JOIN Gen_ProductoMarca ON Gen_ProductoMarca.IdProductoMarca = Gen_Producto.IdProductoMarca
		   WHERE Lo_MovimientoDetalle.hashMovimiento='$IdMovimiento'";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->post('/movmiento/actualizarperiodo', $nstUpdateMovementPeriod);

$app->post('/movimiento/guardarMovimiento', require __DIR__ . '/cash-movement-save.php');

$app->get('/reporte/rankingvendedor', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $fechaInicio = $request->getParam('fechaInicio');
        $fechaFin = $request->getParam('fechaFin');
        $sql = "SELECT 
                ROUND(vTotal.Total,2) as totalventa,
                Seg_Usuario.  NombreUsuario
            FROM Ve_DocVenta
            JOIN Seg_Usuario ON Ve_DocVenta.UsuarioReg = Seg_Usuario.Usuario
            JOIN 
                (
                    SELECT 
                    SUM(vd.Cantidad * vd.Precio) as Total,
                    v.UsuarioReg 
                    FROM Ve_DocVentaDet vd
                    JOIN Ve_DocVenta v ON v.idDocVenta = vd.IdDocVenta
                    WHERE DATE(v.FechaReg) BETWEEN '$fechaInicio' AND '$fechaFin' and v.Anulado = 0
                    GROUP BY v.UsuarioReg 
                ) vTotal ON vTotal.UsuarioReg like Seg_Usuario.Usuario
            WHERE DATE(Ve_DocVenta.FechaReg) BETWEEN '$fechaInicio' AND '$fechaFin'
            AND Ve_DocVenta.Anulado = 0
            GROUP BY Seg_Usuario.NombreUsuario
            ORDER BY totalventa DESC;";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});


$app->get('/reporte/productomasvendidos', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $fechaInicio = $request->getParam('fechaInicio');
        $fechaFin = $request->getParam('fechaFin');
        $sql = "SELECT 
                SUM(Ve_DocVentaDet.Cantidad) TotalVentas,
                CONCAT(SUBSTRING(Gen_Producto.Producto,1,15),'...') Producto
                FROM Ve_DocVentaDet
                JOIN Ve_DocVenta on Ve_DocVenta.idDocVenta = Ve_DocVentaDet.IdDocVenta
                JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
                 WHERE Ve_DocVenta.FechaReg BETWEEN '$fechaInicio' AND '$fechaFin'  AND Ve_DocVenta.Anulado = 0
                GROUP BY Gen_Producto.Producto
                ORDER BY TotalVentas DESC 
                LIMIT 20";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/reporte/totalcompraporproveedor', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $fechaInicio = $request->getParam('fechaInicio');
        $fechaFin = $request->getParam('fechaFin');
        $sql = "SELECT 
                Lo_Proveedor.Proveedor,
                SUM(Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio) totalCompra
                FROM 
                Lo_Movimiento 
                JOIN Lo_Proveedor ON Lo_Proveedor.IdProveedor = Lo_Movimiento.IdProveedor 
                JOIN Lo_MovimientoDetalle ON Lo_Movimiento.Hash =  Lo_MovimientoDetalle.hashMovimiento
                 WHERE Lo_Movimiento.FechaReg BETWEEN '$fechaInicio' AND '$fechaFin'  
                GROUP BY Lo_Proveedor.Proveedor";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});
$app->get('/reporte/movimientos', function (Request $request, Response $response, array $args) use ($app) {
    try {
        $fechaInicio = $request->getParam('fechaInicio');
        $fechaFin = $request->getParam('fechaFin');
        $sql = "SELECT 
                Lo_Proveedor.Proveedor,
                Gen_Producto.Producto,
                SUM(Lo_MovimientoDetalle.Cantidad) Cantidad
                FROM
                Lo_Movimiento
                JOIN Lo_Proveedor ON Lo_Movimiento.IdProveedor = Lo_Proveedor.IdProveedor
                JOIN Lo_MovimientoDetalle ON Lo_Movimiento.Hash = Lo_MovimientoDetalle.hashMovimiento
                JOIN Gen_Producto ON Lo_MovimientoDetalle.IdProducto = Gen_Producto.IdProducto
                 WHERE Lo_Movimiento.FechaReg BETWEEN '$fechaInicio' AND '$fechaFin'  
                GROUP BY Lo_Proveedor.Proveedor,Gen_Producto.Producto
                ORDER BY SUM(Lo_MovimientoDetalle.Cantidad) DESC
                LIMIT 15";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/listarmodulos', function (Request $request, Response $response, array $args) {
    try {

        $idUsuarioPerfil = $request->getParam('IdUsuarioPerfil');

        $sql = "SELECT * FROM Seg_UsuarioModulo ORDER BY IdUsuarioModulo ASC";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception  $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->post('/modulo/registro', function (Request $request, Response $response) {
    try {
        $modulo = $request->getParam('modulo');
        $insert = $this->db->insert(array('UsuarioModulo'))
            ->into('Seg_UsuarioModulo')
            ->values(array($modulo));

        $insertId = $insert->execute();
        return $response->withJson($insertId);
    } catch (Exception  $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->post('/usuarioperfil/registro', function (Request $request, Response $response, array $args) {
    try {
        date_default_timezone_set('America/Lima');
        $UsuarioPerfil = $request->getParam('UsuarioPerfil');
        $lstModulosPerfil = $request->getParam('lstModulosPerfil');
        $fecha          = date("Y-m-d h:i:s");
        $insert = $this->db->insert(array('UsuarioPerfil', 'Anulado', 'FechaReg'))
            ->into('Seg_UsuarioPerfil')
            ->values(array($UsuarioPerfil, 0, $fecha));
        $insertId = $insert->execute();


        foreach ($lstModulosPerfil as $key) {
            $insert = $this->db->insert(array(
                'IdUsuarioModulo',
                'IdUsuarioPerfil',
                'Lectura',
                'Escritura'
            ))
                ->into('Seg_UsuarioModulo_has_UsuarioPerfil')
                ->values(array(
                    $key['IdUsuarioModulo'],
                    $insertId,
                    $key['Lectura'],
                    $key['Escritura'],
                ));
            $insert->execute();
        }

        return $response->withJson($insertId);
    } catch (Exception  $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->post('/usuarioperfil/actualizar', function (Request $request, Response $response, array $args) {
    try {
        date_default_timezone_set('America/Lima');

        // Obtener parámetros del request
        $UsuarioPerfil = $request->getParam('UsuarioPerfil');
        $lstModulosPerfil = $request->getParam('lstModulosPerfil');
        $Anulado = $request->getParam('Anulado');
        $IdUsuarioPerfil = $request->getParam('IdUsuarioPerfil'); // Definir la variable
        $fecha = date("Y-m-d H:i:s"); // Corrección en el formato 24h

        // Actualizar Seg_UsuarioPerfil
        $update = $this->db->update([
            'UsuarioPerfil' => $UsuarioPerfil,
            'FechaMod' => $fecha,
            'Anulado' => $Anulado,
        ])
            ->table('Seg_UsuarioPerfil')
            ->where('IdUsuarioPerfil', '=', $IdUsuarioPerfil);

        $update->execute(); // Ejecutar la consulta correctamente

        // Actualizar Seg_UsuarioModulo_has_UsuarioPerfil
        foreach ($lstModulosPerfil as $key) {

            $sql = "SELECT * FROM Seg_UsuarioModulo_has_UsuarioPerfil where 
                    IdUsuarioModulo =" . $key['IdUsuarioModulo'] . " AND
                    IdUsuarioPerfil = $IdUsuarioPerfil
            ";
            $stmt = $this->db->query($sql);
            $stmt->execute();
            $data = $stmt->fetch();
            if (!$data) {
                $insert = $this->db->insert(array(
                    'IdUsuarioModulo',
                    'IdUsuarioPerfil',
                    'Lectura',
                    'Escritura',
                    'Anulado'
                ))
                    ->into('Seg_UsuarioModulo_has_UsuarioPerfil')
                    ->values(array(
                        $key['IdUsuarioModulo'],
                        $IdUsuarioPerfil,
                        $key['Lectura'],
                        $key['Escritura'],
                        $key['Anulado'],
                    ));
                $insert->execute();
            } else {
                $IdUsuarioModulo = $key['IdUsuarioModulo'] ?? null;
                $IdUsuarioPerfil = $key['IdUsuarioPerfil'] ?? null;

                if ($IdUsuarioModulo && $IdUsuarioPerfil) { // Evitar errores con valores nulos
                    $update = $this->db->update([
                        'Lectura' => $key['Lectura'],
                        'Escritura' => $key['Escritura'],
                    ])
                        ->table('Seg_UsuarioModulo_has_UsuarioPerfil')
                        ->where('IdUsuarioModulo', '=', $IdUsuarioModulo)
                        ->where('IdUsuarioPerfil', '=', $IdUsuarioPerfil);

                    $update->execute();
                }
            }
        }

        return $response->withJson(['success' => true, 'message' => 'Actualización realizada correctamente']);
    } catch (Exception  $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});


$app->post('/usuarioperfil/eliminar', function (Request $request, Response $response, array $args) {
    try {
        date_default_timezone_set('America/Lima');

        // Obtener parámetros del request
        $UsuarioPerfil = $request->getParam('UsuarioPerfil');
        $lstModulosPerfil = $request->getParam('lstModulosPerfil');
        $IdUsuarioPerfil = $request->getParam('IdUsuarioPerfil'); // Definir la variable
        $fecha = date("Y-m-d H:i:s"); // Corrección en el formato 24h

        // Actualizar Seg_UsuarioPerfil
        $update = $this->db->update([
            'Anulado' => 1,
            'FechaMod' => $fecha
        ])
            ->table('Seg_UsuarioPerfil')
            ->where('IdUsuarioPerfil', '=', $IdUsuarioPerfil);

        $update->execute();

        return $response->withJson(['success' => true, 'message' => 'Actualización realizada correctamente']);
    } catch (Exception  $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/perfil/listado', function (Request $request, Response $response, array $args) {
    $select = "
        SELECT * FROM Seg_UsuarioPerfil
        ORDER BY COALESCE(Seg_UsuarioPerfil.Anulado,0) ASC
        ";


    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/puntoventa/listado', function (Request $request, Response $response, array $args) {
    $select = "SELECT * FROM Ve_DocVentaPuntoVenta";


    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});


$app->post('/usuario/actualizar', function (Request $request, Response $response) {

    try {


        $IdAlmacen = $request->getParam('IdAlmacen');
        $IdUsuarioPerfil = $request->getParam('IdUsuarioPerfil');
        $NombreUsuario = $request->getParam('NombreUsuario');
        $IdDocVentaPuntoVenta = $request->getParam('IdDocVentaPuntoVenta');
        $Usuario = $request->getParam('Usuario');
        $actualizarClave = $request->getParam('actualizarClave');
        $clave = $request->getParam('clave');
        $Anulado = $request->getParam('Anulado');


        $dml = "UPDATE Seg_Usuario 
            SET
            IdAlmacen= $IdAlmacen,
            IdUsuarioPerfil= $IdUsuarioPerfil,
            IdDocVentaPuntoVenta= $IdDocVentaPuntoVenta,
            NombreUsuario= '$NombreUsuario',
            Anulado= $Anulado
            ";
        if ($actualizarClave == true) {
            $dml .= " , Password= SHA2('$clave', 256)";
        }

        $dml .= " WHERE Usuario = '$Usuario'";
        $stmt = $this->db->prepare($dml);
        $updated = $stmt->execute();
        return $response->withJson($updated);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/lstusuarios/listado', function (Request $request, Response $response, array $args) {
    $select = "
        SELECT 
        Seg_Usuario.Usuario,
        Seg_Usuario.Anulado, 
        UPPER(Seg_UsuarioPerfil.UsuarioPerfil) UsuarioPerfil, 
        Seg_Usuario.IdUsuarioPerfil, 
        Seg_Usuario.IdDocVentaPuntoVenta, 
        upper(Seg_Usuario.NombreUsuario) NombreUsuario,
		Lo_Almacen.IdAlmacen, 
        Lo_Almacen.Almacen
		FROM Seg_Usuario
		INNER JOIN Seg_UsuarioPerfil ON Seg_Usuario.IdUsuarioPerfil = Seg_UsuarioPerfil.IdUsuarioPerfil
		LEFT JOIN Lo_Almacen ON Seg_Usuario.IdAlmacen = Lo_Almacen.IdAlmacen
        ORDER BY Seg_Usuario.IdUsuarioPerfil asc ,Seg_Usuario.Usuario asc
        ";


    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->post('/usuario/registro', function (Request $request, Response $response) {
    try {
        // Obtener los parámetros desde getParam()
        $IdAlmacen = $request->getParam('IdAlmacen');
        $IdUsuarioPerfil = $request->getParam('IdUsuarioPerfil');
        $IdDocVentaPuntoVenta = $request->getParam('IdDocVentaPuntoVenta');
        $NombreUsuario = $request->getParam('NombreUsuario');
        $Usuario = $request->getParam('Usuario');
        $clave = $request->getParam('clave');
        $Anulado = $request->getParam('Anulado') ?? 0;

        // Validar si el usuario ya existe
        $select = "SELECT * FROM Seg_Usuario WHERE upper(Usuario) = upper(:usuario)";
        $stmt = $this->db->prepare($select);
        $stmt->execute(['usuario' => $Usuario]);
        $data = $stmt->fetch();

        if ($data) {
            return $response->withJson(['estado' => false, 'msg' => 'El Nombre de Usuario ya se encuentra en uso.']);
        }


        $dml = "
            INSERT INTO Seg_Usuario 
            (IdAlmacen, IdUsuarioPerfil, NombreUsuario, Anulado, Usuario, Password,IdDocVentaPuntoVenta)
            VALUES
            (:idAlmacen, :idUsuarioPerfil, :nombreUsuario, :anulado, :usuario, SHA2(:clave, 256),:IdDocVentaPuntoVenta)
        ";

        $stmt = $this->db->prepare($dml);
        $stmt->execute([
            'idAlmacen' => $IdAlmacen,
            'idUsuarioPerfil' => $IdUsuarioPerfil,
            'nombreUsuario' => $NombreUsuario,
            'anulado' => $Anulado,
            'usuario' => $Usuario,
            'clave' => $clave,
            'IdDocVentaPuntoVenta' => $IdDocVentaPuntoVenta
        ]);

        return $response->withJson(['estado' => true, 'msg' => 'Usuario registrado exitosamente.']);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/ventas/estadistica/dashbora/dia', function (Request $request, Response $response) {
    $fechaInicio = $request->getParam('fechaInicio');
    $fechaFin = $request->getParam('fechaFin');
    $idAlmacen = $request->getParam('idAlmacen');
    $select = "SELECT YEAR(Ve_DocVenta.FechaDoc) as Anio, MONTH(Ve_DocVenta.FechaDoc) as Mes,DAY(Ve_DocVenta.FechaDoc) as Dia, DAYNAME(Ve_DocVenta.FechaDoc) as DiaNombre, SUM( IF(Ve_DocVentaTipoDoc.CodSunat='07',-1,1) * ROUND((Ve_DocVentaDet.Precio * Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento, 2) ) AS Total FROM Ve_DocVenta
    INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.idDocVenta = Ve_DocVentaDet.IdDocVenta
    INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
    WHERE " . ($idAlmacen != 0 && $idAlmacen != null ? " Ve_DocVenta.IdAlmacen = $idAlmacen AND " : "") . " 
        DATE(Ve_DocVenta.FechaReg) BETWEEN '$fechaInicio' AND '$fechaFin'
    AND Ve_DocVenta.Anulado = 0 AND Ve_DocVentaTipoDoc.CodSunat NOT IN ('07')
    GROUP BY DAY(Ve_DocVenta.FechaDoc)
    ORDER BY Ve_DocVenta.FechaDoc ASC";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson($data);
});

$app->get('/productos/serie', function (Request $request, Response $response, array $args) {
    try {
        $IdProducto = $request->getParam('IdProducto');
        $IdAlmacen = $request->getParam('IdAlmacen') ? $request->getParam('IdAlmacen') : false;
        $Todos = (strtolower($request->getParam('Todos')) === 'true') ? true : false;

        $select = "SELECT ps.* , CONCAT(v.Serie,'-',v.Numero) as CorrelativoVenta
                    FROM Gen_Producto_Serie   ps
                    LEFT JOIN Lo_Movimiento m ON m.Hash = ps.IdMovimiento 
                    LEFT JOIN Ve_DocVenta v ON v.idDocVenta = ps.IdDocVenta 
                    WHERE ps.IdProducto=$IdProducto ";
        if (!$Todos) {
            $select .= " AND ps.Estado =1";
        }
        if ($IdAlmacen) {
            $select .= " AND m.IdAlmacenDestino =$IdAlmacen";
        }
        $select .= " ORDER BY ps.Estado ASC,ps.FechaReg DESC";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['err' => $e->getMessage()], 500);
    }
});

$app->get('/cuentas/listarcuentas', function (Request $request, Response $response, array $args) {
    try {
        $select = "SELECT * FROM Cb_Cuenta";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/ListarCajaBanco', function (Request $request, Response $response, array $args) {
    try {
        $IdCuenta = $request->getParam('IdCuenta');
        $FechaDoc = $request->getParam('FechaDoc');
        $select = "SELECT
                    CB.IdCajaBanco,
                    CB.UsuarioReg,
                    CB.Concepto,
                    CASE TCB.Tipo
                        WHEN 1 THEN 0
                        ELSE CB.Importe
                    END as 'Ingresos',
                    CASE TCB.Tipo
                        WHEN 1 THEN CB.Importe
                        ELSE 0
                    END as 'Salida'
                    FROM Cb_CajaBanco as CB
                    INNER JOIN Cb_TipoCajaBanco as TCB
                    ON CB.IdTipoCajaBanco = TCB.IdTipoCajaBanco
                    WHERE CB.IdCuenta = $IdCuenta AND DATE(CB.Fechadoc) = '$FechaDoc'
                    ORDER BY TCB.Tipo,CB.IdCajaBanco;";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->get('/clientes/deudas/letras', function (Request $request, Response $response, array $args) {
    $idCliente = $request->getParam('idCliente');
    $select = "(SELECT
    0 IdLetraCaja,
    Ve_DocVenta.IdDocVenta,
    Ve_DocVenta.FechaDoc,
    Ve_DocVenta.FechaCredito,
    CONCAT(CASE WHEN Ve_DocVentaTipoDoc.CodSunat ='01' THEN
        'F'
    ELSE
        CASE WHEN Ve_DocVentaTipoDoc.CodSunat='03' THEN
            'B'
        ELSE
            CASE WHEN Ve_DocVentaTipoDoc.CodSunat='12' THEN
                'T'
            ELSE
                'OTRO'
            END
        END
    END,Ve_DocVenta.Serie,'-' , CONVERT(Ve_DocVenta.Numero, CHAR) )AS Correlativo,
    ROUND(SUM(Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad),2) AS Total,
    ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='VE' AND Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)),2) AS Aplicado,
    ROUND(SUM(Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad),2) -
    ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='VE' AND Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)),2) AS Saldo
    FROM Ve_DocVenta
    INNER JOIN Ve_DocVentaDet ON Ve_DocVenta.IdDocVenta=Ve_DocVentaDet.IdDocVenta
    INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc=Ve_DocVentaTipoDoc.IdTipoDoc
    WHERE EsCredito=1 AND Ve_DocVenta.IdCliente=$idCliente
    GROUP BY
    Ve_DocVenta.IdDocVenta,
    Ve_DocVenta.FechaDoc,
    Ve_DocVenta.FechaCredito,
    Ve_DocVentaTipoDoc.CodSunat,
    Ve_DocVenta.Serie,
    Ve_DocVenta.Numero
    HAVING Saldo >0 AND (SELECT COUNT(*) FROM Cb_LetraCaja WHERE Cb_LetraCaja.Tipo='VE' AND Cb_LetraCaja.IdDocDet=Ve_DocVenta.idDocVenta)=0
    ORDER BY FechaDoc)
    
    UNION 
    
    (SELECT IdLetraCaja,CONCAT(Cb_LetraCaja.IdDocDet,'-',Cb_LetraCaja.IdLetraCaja) AS IdDocVenta,
    Cb_LetraCaja.FechaReg AS FechaDoc,
    Cb_LetraCaja.FechaPago AS FechaCredito,
    CONCAT(CASE WHEN Ve_DocVentaTipoDoc.CodSunat ='01' THEN
        'F'
    ELSE
        CASE WHEN Ve_DocVentaTipoDoc.CodSunat='03' THEN
            'B'
        ELSE
            CASE WHEN Ve_DocVentaTipoDoc.CodSunat='12' THEN
                'T'
            ELSE
                'OTRO'
            END
        END
    END,Ve_DocVenta.Serie,'-' ,Ve_DocVenta.Numero,' (Letra N°',Cb_LetraCaja.NumeroLetra,')') AS Correlativo,
    Cb_LetraCaja.Importe AS Total,
    Cb_LetraCaja.Tipo AS Aplicado,
    Cb_LetraCaja.Cancelado AS Saldo
    FROM Cb_LetraCaja
    INNER JOIN Ve_DocVenta ON Cb_LetraCaja.IdDocDet=Ve_DocVenta.idDocVenta
    INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVenta.IdTipoDoc=Ve_DocVentaTipoDoc.IdTipoDoc
    WHERE Ve_DocVenta.IdCliente=$idCliente AND Cb_LetraCaja.Cancelado=0 ORDER BY Cb_LetraCaja.FechaPago DESC)";


    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();

    return $response->withJson(array(
        "sEcho" => 1,
        "iTotalRecords" => count($data),
        "iTotalDisplayRecords" => count($data),
        "aaData" => $data
    ));
});

$app->get('/proveedores/deudas/letras', function (Request $request, Response $response, array $args) {
    try {
        //code...

        $idProveedor = $request->getParam('idProveedor');
        $select = "(SELECT
    Lo_Movimiento.Hash AS Id,
    Lo_Movimiento.MovimientoFecha AS FechaDoc,
    DATE(Lo_Movimiento.FechaVenCredito) AS FechaCredito,
    CONCAT(CASE WHEN Lo_MovimientoTipo.CodSunat ='01' THEN
        'F'
    ELSE
        CASE WHEN Lo_MovimientoTipo.CodSunat='03' THEN
            'B'
        ELSE
            CASE WHEN Lo_MovimientoTipo.CodSunat='12' THEN
                'T'
            ELSE
                'OTRO'
            END
        END
    END,Lo_Movimiento.Serie,'-' , CONVERT(Lo_Movimiento.Numero, CHAR) )AS Correlativo,
    ROUND(SUM(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) 
    + CASE WHEN Lo_MovimientoDetalle.TieneIgv THEN Round(Sum(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) *0.18  else 0 end
    AS Total,
    ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='MO' AND Cb_CajaBancoDet.Hash=Lo_Movimiento.Hash),0)),2) AS Aplicado,
    ROUND(SUM(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) 
    + CASE WHEN Lo_MovimientoDetalle.TieneIgv THEN Round(Sum(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) *0.18  else 0 end
    - ROUND((SELECT IFNULL((SELECT SUM(Cb_CajaBancoDet.Importe) FROM Cb_CajaBancoDet WHERE Tipo='MO' AND Cb_CajaBancoDet.Hash=Lo_Movimiento.Hash),0)),2) AS Saldo
    FROM Lo_Movimiento
    INNER JOIN Lo_MovimientoDetalle ON Lo_Movimiento.Hash=Lo_MovimientoDetalle.hashMovimiento
    INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo=Lo_MovimientoTipo.IdMovimientoTipo
    WHERE EsCredito=1 AND Lo_Movimiento.IdProveedor=$idProveedor
    GROUP BY
    Lo_Movimiento.Hash,
    Lo_Movimiento.MovimientoFecha,
    Lo_Movimiento.FechaVenCredito,
    Lo_MovimientoTipo.CodSunat,
    Lo_Movimiento.Serie,
    Lo_Movimiento.Numero
    HAVING Saldo >0 AND (SELECT COUNT(*) FROM Cb_LetraCaja WHERE Cb_LetraCaja.Tipo='MO' AND Cb_LetraCaja.IdDocDet=Lo_Movimiento.Hash)=0
    ORDER BY MovimientoFecha)
    
    UNION 
    
    (SELECT CONCAT(Cb_LetraCaja.IdDocDet,'-',Cb_LetraCaja.IdLetraCaja) AS Id,
    Cb_LetraCaja.FechaReg AS FechaDoc,
    Cb_LetraCaja.FechaPago AS FechaCredito,
    CONCAT(CASE WHEN Lo_MovimientoTipo.CodSunat ='01' THEN
        'F'
    ELSE
        CASE WHEN Lo_MovimientoTipo.CodSunat='03' THEN
            'B'
        ELSE
            CASE WHEN Lo_MovimientoTipo.CodSunat='12' THEN
                'T'
            ELSE
                'OTRO'
            END
        END
    END,Lo_Movimiento.Serie,'-',Lo_Movimiento.Numero,' (Letra N°',Cb_LetraCaja.NumeroLetra,')') AS Correlativo,
    Cb_LetraCaja.Importe AS Total,
    Cb_LetraCaja.Tipo AS Aplicado,
    Cb_LetraCaja.Cancelado AS Saldo
    FROM Cb_LetraCaja
    INNER JOIN Lo_Movimiento ON Cb_LetraCaja.IdDocDet=Lo_Movimiento.Hash
    INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo=Lo_MovimientoTipo.IdMovimientoTipo
    WHERE Lo_Movimiento.IdProveedor=$idProveedor AND Cb_LetraCaja.Cancelado=0 ORDER BY Cb_LetraCaja.FechaPago DESC)";

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson(array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        ));
    } catch (Exception $e) {
        return $response->withJson(['e' => $e->getMessage()], 500);
    }
});

$app->post('/letras/generar', function (Request $request, Response $response) {
    $letras = $request->getParam('Letras');
    $tipo = $request->getParam('Tipo');

    try {
        foreach ($letras as $key => $value) {
            if ($tipo == 1) {
                $insertDet = $this->db->insert(array('IdDocDet', 'NumeroLetra', 'Hash', 'Importe', 'FechaReg', 'FechaPago', 'Tipo', 'Cancelado'))
                    ->into('Cb_LetraCaja')
                    ->values(array($value['IdDocDet'], ($key + 1), $value['IdDocDet'], $value['MontoLetra'], getNow(), $value['FechaLetra'], 'MO', 0));
                $insertDet->execute();
            } else {
                $insertDet = $this->db->insert(array('IdDocDet', 'NumeroLetra', 'Importe', 'FechaReg', 'FechaPago', 'Tipo', 'Cancelado'))
                    ->into('Cb_LetraCaja')
                    ->values(array($value['IdDocDet'], ($key + 1), $value['MontoLetra'], getNow(), $value['FechaLetra'], 'VE', 0));
                $insertDet->execute();
            }
        }
        return $response->withJson(true);
    } catch (Exception $e) {
        return $response->withJson($e);
    }


    //return $response->withJson(array("Letras"=>$letras,"Tipo"=>$tipo));
});

$app->post('/movimiento/eliminarCajaBanco', function (Request $request, Response $response, array $args) {
    try {
        $idCajaBanco = $request->getParam('IdCajaBanco');

        $Ssql = "UPDATE Cb_LetraCaja SET 
                IdCajaBanco = 0,
                Cancelado = 0
                WHERE IdCajaBanco = $idCajaBanco";
        $stmt = $this->db->query($Ssql);
        $stmt->execute();
        $data = $stmt->fetch();

        $Ssql = "DELETE FROM Cb_CajaBancoDet WHERE IdCajaBanco = $idCajaBanco";
        $stmt = $this->db->query($Ssql);
        $stmt->execute();
        $data = $stmt->fetch();

        $Ssql = "DELETE FROM Cb_CajaBanco WHERE IdCajaBanco = $idCajaBanco";
        $stmt = $this->db->query($Ssql);
        $stmt->execute();
        $data = $stmt->fetch();
        return $response->withJson(true);
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], false);
    }
});

$app->post('/letras/pagar', function (Request $request, Response $response) {
    $idLetraCaja = $request->getParam('IdLetraCaja');
    $idDocDet = $request->getParam('IdDocDet');
    $idCuenta = $request->getParam('IdCuenta');
    $concepto =  $request->getParam('Concepto');
    $importe = $request->getParam('Importe');
    $idTipoCajaBanco = $request->getParam('IdTipoCajaBanco');
    $tipo = $request->getParam('Tipo');
    $idCliente = $request->getParam('IdCliente') ? $request->getParam('IdCliente') : 0;
    $idProveedor = $request->getParam('IdProveedor') ? $request->getParam('IdProveedor') : 0;

    $vendedor = 'xx';
    if (isset($_SESSION['user'])) {
        $vendedor = $_SESSION['user'];
    }
    $vendedor = $request->getParam('User') ? $request->getParam('User') : $vendedor;
    try {
        $insert = $this->db->insert(array('IdTipoCajaBanco', 'IdCuenta', 'FechaDoc', 'Concepto', 'Importe', 'Anulado', 'UsuarioReg', 'IdProveedor', 'IdCliente'))
            ->into('Cb_CajaBanco')
            ->values(array($idTipoCajaBanco, $idCuenta, getNow(), $concepto, $importe, 0, $vendedor, $idProveedor, $idCliente));

        $insertId = $insert->execute();

        if ($tipo == 1) {
            $insertDet = $this->db->insert(array('IdCajaBanco', 'IdDocDet', 'Importe', 'Hash', 'Tipo'))
                ->into('Cb_CajaBancoDet')
                ->values(array($insertId, $idDocDet, $importe, $idDocDet, 'MO'));
            $insertDet->execute();
        } else {
            $insertDet = $this->db->insert(array('IdCajaBanco', 'IdDocDet', 'Importe', 'Tipo'))
                ->into('Cb_CajaBancoDet')
                ->values(array($insertId, $idDocDet, $importe, 'VE'));
            $insertDet->execute();
        }

        $update = $this->db->update(array(
            "IdCajaBanco" => $insertId,
            "Cancelado" => 1
        ))
            ->table('Cb_LetraCaja')
            ->where('IdLetraCaja', '=', $idLetraCaja);
        $update->execute();
        return $response->withJson(array(
            "idCajaBanco" => $insertId
        ));
    } catch (Exception $e) {
        return $response->withJson(['e' => $e->getMessage()], 500);
    }
});

$app->get('/ventas/delivery', function (Request $request, Response $response, array $args) {
    try {

        $desde = $request->getParam('desde');
        $hasta = $request->getParam('hasta');


        $select = "SELECT *,Ve_DocVenta.FechaReg FechaReg FROM Ve_DocVenta 
            JOIN Ve_DocVentaCliente ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente
            JOIN Ve_DocVentaTipoDoc ON Ve_DocVentaTipoDoc.IdTipoDoc= Ve_DocVenta.IdTipoDoc 
            WHERE Ve_DocVenta.EsDelivery=1 
                AND 
                DATE(Ve_DocVenta.FechaReg) BETWEEN '$desde' AND '$hasta'
                 ORDER BY Ve_DocVenta.FechaReg DESC";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], 500);
        //throw $th;
    }
});


$app->get('/listarusuarioperfilmodulo', function (Request $request, Response $response, array $args) {
    try {

        $idUsuarioPerfil = $request->getParam('IdUsuarioPerfil');

        $sql = "
        SELECT 
        Seg_UsuarioModulo.IdUsuarioModulo, 
        Seg_UsuarioModulo.UsuarioModulo, 
        Seg_UsuarioModulo_has_UsuarioPerfil.IdUsuarioPerfil, 
        Seg_UsuarioModulo_has_UsuarioPerfil.Lectura, 
        Seg_UsuarioModulo_has_UsuarioPerfil.Escritura 
        FROM Seg_UsuarioModulo_has_UsuarioPerfil
        LEFT JOIN Seg_UsuarioModulo ON Seg_UsuarioModulo_has_UsuarioPerfil.IdUsuarioModulo = Seg_UsuarioModulo.IdUsuarioModulo
        WHERE Seg_UsuarioModulo_has_UsuarioPerfil.IdUsuarioPerfil = $idUsuarioPerfil";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception  $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});

$app->post('/openpay/qr', function (Request $request, Response $response, array $args) {

    try {

        // 1. Obtener credenciales
        $sql = "SELECT MerchantId, PrivateKey FROM Gen_Empresa LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$empresa) {
            return $response->withJson([
                'success' => false,
                'message' => 'No hay credenciales Openpay'
            ], 400);
        }

        // 2. URL Openpay
        $url = "https://api.openpay.co/v1/" . $empresa['MerchantId'] . "/charges";

        // 3. Payload Openpay
        $payload = [
            "method" => "store",
            "amount" => 25.50,
            "description" => "Pedido #123",
            "order_id" => "PEDIDO_123"
        ];

        // 4. CURL
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Basic " . base64_encode($empresa['PrivateKey'] . ":")
            ]
        ]);

        $result = curl_exec($ch);

        if ($result === false) {
            throw new Exception(curl_error($ch));
        }

        curl_close($ch);

        // 5. Retornar respuesta Openpay
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200)
            ->write($result);
    } catch (Exception $e) {

        return $response->withJson([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

$app->get('/menu', function (Request $request, Response $response, array $args) {
    $usuario = $request->getParam('Usuario');

    $select = "
        SELECT DISTINCT f.* FROM Seg_Funcion f
        JOIN Seg_FuncionDet fd ON f.IdFuncion = fd.IdFuncion 
        JOIN Seg_FuncionDetalleXUsuario fdu ON fdu.IdFuncionDet = fd.IdFuncionDet
        WHERE fdu.Usuario = '$usuario' AND fdu.Estado = 1
    ";

    $stmt = $this->db->query($select);
    $stmt->execute();
    $data = $stmt->fetchAll();


    foreach ($data as &$funcion) {
        $detSql = "
            SELECT fd.*
            FROM Seg_FuncionDet fd
            JOIN Seg_FuncionDetalleXUsuario fdu ON fdu.IdFuncionDet = fd.IdFuncionDet
            WHERE fd.IdFuncion = " . $funcion['IdFuncion'] . " 
            AND fdu.Usuario = '$usuario' 
            AND fdu.Estado = 1";

        $stmtDet = $this->db->query($detSql);
        $stmtDet->execute();


        $funcion['Detalle'] = $stmtDet->fetchAll();
    }

    unset($funcion);

    return $response->withJson($data);
});

$app->post('/empleados', function (Request $request, Response $response) {
    try {
        //code...

        $cliente = $request->getParam('Empleado') ? $request->getParam('Empleado') : $request->getParam('NombreComercial');
        $dniRuc = $request->getParam('DniRuc');
        $direccion = $request->getParam('Direccion');
        $telefono = $request->getParam('Telefono');
        $email = $request->getParam('Email');


        $idCliente = $request->getParam('IdCliente');
        $insertId = $idCliente;
        if ($idCliente) {
            $update = $this->db->update(array(
                "Emplead" => $cliente,
                'DniRuc' => $dniRuc,
                'Direccion' => $direccion,
                'Telefono' => $telefono,
                'Email' => $email,
            ))
                ->table('Rhu_Empleado')
                ->where('IdCliente', '=', $idCliente);
            $affectedRows = $update->execute();
        } else {
            $insert = $this->db->insert(array('Empleado', 'DniRuc', 'Direccion', 'Telefono', 'Email', 'Anulado', 'FechaReg'))
                ->into('Rhu_Empleado')
                ->values(array($cliente, $dniRuc, $direccion, $telefono, $email, '0', getNow()));

            $insertId = $insert->execute();
        }

        $select = "SELECT * FROM Rhu_Empleado WHERE IdEmpleado=$insertId";
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetch();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], 500);
    }
});

$app->get('/empleado', function (Request $request, Response $response, array $args) {
    try {
        //code...

        $select = "SELECT *, IFNULL(CONCAT(DniRuc, ' - ', Empleado, ' - ', IFNULL(Empleado, '')), '-') AS EmpleadoDniRuc FROM Rhu_Empleado WHERE (Anulado != 1 OR Anulado IS NULL)";
        $select .= " AND (Rhu_Empleado.Empleado LIKE '%" . $request->getParam('q') . "%' OR Rhu_Empleado.Empleado LIKE '%" . $request->getParam('q') . "%'";
        $select .= " OR Rhu_Empleado.DniRuc LIKE '%" . $request->getParam('q') . "%')  ";

        // $select = "SELECT *, IFNULL(CONCAT(DniRuc, ' - ', Cliente), '-') AS ClienteDniRuc FROM Ve_DocVentaCliente WHERE (Anulado != 1 OR Anulado IS NULL)";
        // $select .= " AND Ve_DocVentaCliente.Cliente LIKE '%" . $request->getParam('q') . "%'";
        // $select .= " OR Ve_DocVentaCliente.DniRuc LIKE '%" . $request->getParam('q') . "%'";

        $limit = $request->getParam('limit') ? $request->getParam('limit') :  5;
        if ($limit && !$request->getParam('noLimit')) {
            $offset = 0;
            if ($request->getParam('page')) {
                $page = $request->getParam('page');
                $offset = (--$page) * $limit;
            }
            $select .= " LIMIT " . $limit;
            $select .= " OFFSET " . $offset;
        }

        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], 500);
    }
});

$app->get('/reporte/proyectos/empleado', function (Request $request, Response $response) {
    try {


        $idAlmacen = $request->getParam('idAlmacen');
        $idEmpleado = $request->getParam('idEmpleado');
        $filter = $request->getParam('filter');
        $fechaIni = $request->getParam('FechaIni');
        $fechaFin = $request->getParam('FechaFin');
        $select = "SELECT Ve_PreOrden.*,
                    DATE_FORMAT(Ve_PreOrden.FechaAlquilerFin, '%Y-%m-%d') AS FechaAlquilerFinF,
                    DATE_FORMAT(Ve_PreOrden.FechaAlquilerInicio, '%Y-%m-%d') AS FechaAlquilerInicioF, Ve_DocVentaCliente.Cliente,Ve_DocVentaCliente.DniRuc, 
                    CONCAT(Ve_DocVenta.Serie,'-',Ve_DocVenta.Numero) AS Comprobante,
                    IFNULL(Cb_CajaBancoGasto.sumGasto,0) AS Gastos,
                    Ve_Proforma.Numero AS ProformaNumero, Ve_Proforma.Anio AS ProformaAnio ,  IFNULL(NombreProyecto.Nombre,'')  as Nombre,
                    NombreProyecto.IdProducto,
                    NombreProyecto.IdEmpleado,NombreProyecto.Empleado,
                    ManoObra.TotalManoObra,
                     COALESCE(ManoObra.TotalManoObra,0) - IFNULL(Cb_CajaBancoGasto.sumGasto,0)  TotalDetalle
                    FROM Ve_PreOrden
                    LEFT JOIN Ve_DocVentaCliente ON Ve_PreOrden.IdCliente = Ve_DocVentaCliente.IdCliente

                    JOIN (
                            SELECT Ve_PreOrdenDet.IdPreOrden, Gen_Producto.Producto AS Nombre,Gen_Producto.IdProducto,Rhu_Empleado.IdEmpleado,Rhu_Empleado.Empleado
                            FROM Ve_PreOrdenDet 
                            INNER JOIN Gen_Producto ON Ve_PreOrdenDet.IdProducto = Gen_Producto.IdProducto
                            JOIN Rhu_Empleado ON Rhu_Empleado.IdEmpleado = Gen_Producto.IdEmpleado
                            WHERE Gen_Producto.EsHabitacion = 1 AND ($idEmpleado = 0 OR Rhu_Empleado.IdEmpleado = $idEmpleado)
                            GROUP BY  Ve_PreOrdenDet.IdPreOrden,Gen_Producto.IdProducto
                    ) AS NombreProyecto ON Ve_PreOrden.IdPreOrden = NombreProyecto.IdPreOrden

                    LEFT JOIN (
                            SELECT Ve_PreOrdenDet.IdPreOrden, (COALESCE(Ve_PreOrdenDet.Cantidad,0) * COALESCE(Ve_PreOrdenDet.Precio,0)) TotalManoObra
                            FROM Ve_PreOrdenDet 
                            INNER JOIN Gen_Producto ON Ve_PreOrdenDet.IdProducto = Gen_Producto.IdProducto
                            WHERE Gen_Producto.CodigoBarra = 'MANODEOBRA'
                            GROUP BY  Ve_PreOrdenDet.IdPreOrden,Gen_Producto.IdProducto
                    ) AS ManoObra ON Ve_PreOrden.IdPreOrden = ManoObra.IdPreOrden

                    LEFT JOIN (
                        SELECT Cb_CajaBanco.IdPreOrden, SUM(Cb_CajaBanco.Importe) AS sumGasto 
                        FROM Cb_CajaBanco  
                        INNER JOIN Cb_TipoCajaBanco ON Cb_TipoCajaBanco.IdTipoCajaBanco = Cb_CajaBanco.IdTipoCajaBanco 
                        WHERE Cb_TipoCajaBanco.Tipo = 1 
                        GROUP BY Cb_CajaBanco.IdPreOrden
                    ) Cb_CajaBancoGasto ON  Ve_PreOrden.IdPreOrden = Cb_CajaBancoGasto.IdPreOrden

                    LEFT JOIN Ve_Proforma ON Ve_PreOrden.IdProforma = Ve_Proforma.IdProforma
                    LEFT JOIN Ve_DocVenta on Ve_PreOrden.IdDocVenta = Ve_DocVenta.idDocVenta
    
                     WHERE (Ve_DocVentaCliente.Cliente LIKE '%" . $filter . "%' OR Ve_DocVentaCliente.DniRuc LIKE '%" . $filter . "%' OR Ve_PreOrden.IdPreOrden LIKE '%" . $filter . "%' OR Ve_PreOrden.FechaReg LIKE '%" . $filter . "%'  OR Nombre LIKE '%" . $filter . "%') ";

        if (($fechaIni &&  $fechaIni != '') && ($fechaFin &&  $fechaFin != '')) {
            $select .= " AND (Ve_PreOrden.FechaReg BETWEEN '" . $fechaIni . " 00:00:00' AND '" . $fechaFin . " 23:59:59')";
        }

        if ($request->getParam('sortBy')) {
            $sortBy = $request->getParam('sortBy');
            $sortDesc = $request->getParam('sortDesc');
            $orientation = $sortDesc ? 'DESC' : 'ASC';
            $select .= " ORDER BY " . $sortBy . " " . $orientation;
        }

        if ($request->getParam('limit')) {
            $limit = $request->getParam('limit');
            $offset = 0;
            if ($request->getParam('page')) {
                $page = $request->getParam('page');
                $offset = (--$page) * $limit;
            }
            $select .= " LIMIT " . $limit;
            $select .= " OFFSET " . $offset;
        }
        $stmt = $this->db->query($select);
        $stmt->execute();
        $data = $stmt->fetchAll();
        return $response->withJson($data);
    } catch (Exception $e) {
        return $response->withJson([$e->getMessage()], 500);
        //throw $th;
    }
});

require_once __DIR__ . '/imceden-modules.php';

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});

$app->run();
