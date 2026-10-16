<?php
declare(strict_types=1);
ini_set('display_errors', '0');
ini_set('session.use_strict_mode', '1');
$isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_set_cookie_params(array(
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
));
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
require_once dirname(__DIR__, 2) . '/config/database.php';

function tiendaJsonError(string $message, int $status): void
{
    http_response_code($status);
    echo json_encode(array('error' => $message), JSON_UNESCAPED_UNICODE);
    exit;
}

function tiendaValidateRequestOrigin(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') return;
    $source = (string) ($_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '');
    if ($source === '') return;
    $sourceHost = strtolower((string) parse_url($source, PHP_URL_HOST));
    $requestHost = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
    if ($sourceHost === '' || !hash_equals($requestHost, $sourceHost)) {
        tiendaJsonError('Solicitud no autorizada.', 403);
    }
}

function tiendaRateLimit(string $scope, int $limit, int $windowSeconds): void
{
    $directory = dirname(__DIR__, 2) . '/storage/security/rate-limits';
    if (!is_dir($directory) && !@mkdir($directory, 0750, true) && !is_dir($directory)) {
        tiendaJsonError('No se pudo validar temporalmente la solicitud.', 503);
    }
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $key = hash('sha256', $scope . '|' . $ip);
    $path = $directory . '/' . $key . '.json';
    $handle = @fopen($path, 'c+');
    if (!$handle || !flock($handle, LOCK_EX)) tiendaJsonError('No se pudo validar temporalmente la solicitud.', 503);
    $raw = stream_get_contents($handle);
    $attempts = json_decode((string) $raw, true);
    if (!is_array($attempts)) $attempts = array();
    $cutoff = time() - $windowSeconds;
    $attempts = array_values(array_filter($attempts, static fn($timestamp) => (int) $timestamp > $cutoff));
    if (count($attempts) >= $limit) {
        flock($handle, LOCK_UN);
        fclose($handle);
        header('Retry-After: ' . $windowSeconds);
        tiendaJsonError('Demasiados intentos. Espere unos minutos y vuelva a intentarlo.', 429);
    }
    $attempts[] = time();
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($attempts));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
}

tiendaValidateRequestOrigin();

try {
    $pdo = appPdoConnection();
    $action = $_GET['action'] ?? 'catalogo';
    if (in_array($action, array('login','register','logout'), true) && ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        header('Allow: POST');
        tiendaJsonError('Método no permitido.', 405);
    }
    $body = json_decode((string) file_get_contents('php://input'), true) ?: array();
    $publicClient = function (array $row) {
        return array('IdCliente'=>(int)$row['IdCliente'],'documento'=>$row['DniRuc'],'nombre'=>$row['Cliente'],'direccion'=>$row['Direccion'],'telefono'=>$row['Telefono'],'correo'=>$row['Email']);
    };
    if ($action === 'auth-status') {
        if (empty($_SESSION['tienda_cliente_id'])) { echo json_encode(array('authenticated'=>false)); exit; }
        $stmt=$pdo->prepare('SELECT IdCliente,DniRuc,Cliente,Direccion,Telefono,Email FROM Ve_DocVentaCliente WHERE IdCliente=? AND COALESCE(Anulado,0)=0 LIMIT 1');
        $stmt->execute(array((int)$_SESSION['tienda_cliente_id'])); $client=$stmt->fetch();
        echo json_encode($client?array('authenticated'=>true,'client'=>$publicClient($client)):array('authenticated'=>false),JSON_UNESCAPED_UNICODE); exit;
    }
    if ($action === 'logout') { $_SESSION=array(); session_destroy(); echo json_encode(array('ok'=>true)); exit; }
    if ($action === 'login') {
        tiendaRateLimit('login', 8, 900);
        $documento=preg_replace('/[^A-Za-z0-9]/','',strtoupper(trim((string)($body['documento']??''))));
        $stmt=$pdo->prepare('SELECT a.IdCliente,a.PasswordHash,c.DniRuc,c.Cliente,c.Direccion,c.Telefono,c.Email FROM Tienda_ClienteCuenta a JOIN Ve_DocVentaCliente c ON c.IdCliente=a.IdCliente WHERE a.Documento=? AND COALESCE(c.Anulado,0)=0 LIMIT 1');
        $stmt->execute(array($documento)); $account=$stmt->fetch();
        if(!$account||!password_verify((string)($body['password']??''),$account['PasswordHash'])){http_response_code(401);echo json_encode(array('error'=>'Documento o contraseña incorrectos.'));exit;}
        session_regenerate_id(true); $_SESSION['tienda_cliente_id']=(int)$account['IdCliente']; $pdo->prepare('UPDATE Tienda_ClienteCuenta SET UltimoAcceso=NOW() WHERE Documento=?')->execute(array($documento));
        echo json_encode(array('ok'=>true,'client'=>$publicClient($account)),JSON_UNESCAPED_UNICODE);exit;
    }
    if ($action === 'register') {
        tiendaRateLimit('register', 5, 3600);
        $tipo=strtoupper(trim((string)($body['tipo']??'DNI'))); $documento=preg_replace('/[^A-Za-z0-9]/','',strtoupper(trim((string)($body['documento']??''))));
        $nombre=trim((string)($body['nombre']??''));$direccion=trim((string)($body['direccion']??''));$telefono=preg_replace('/[^0-9+]/','',(string)($body['telefono']??''));$correo=trim((string)($body['correo']??''));$password=(string)($body['password']??'');
        if(!in_array($tipo,array('DNI','CE','RUC'),true)||strlen($documento)<8||strlen($documento)>12){http_response_code(422);echo json_encode(array('error'=>'Ingrese un documento válido.'));exit;}
        if(mb_strlen($nombre)<4||mb_strlen($direccion)<4||strlen(preg_replace('/\D/','',$telefono))<7||!filter_var($correo,FILTER_VALIDATE_EMAIL)||strlen($password)<8){http_response_code(422);echo json_encode(array('error'=>'Complete nombre, dirección, teléfono, correo válido y una contraseña de 8 caracteres.'));exit;}
        $exists=$pdo->prepare('SELECT IdCuenta FROM Tienda_ClienteCuenta WHERE Documento=?');$exists->execute(array($documento));if($exists->fetch()){http_response_code(409);echo json_encode(array('error'=>'Este documento ya tiene una cuenta. Inicie sesión.'));exit;}
        $pdo->beginTransaction();
        $stmt=$pdo->prepare('SELECT IdCliente FROM Ve_DocVentaCliente WHERE DniRuc=? LIMIT 1');$stmt->execute(array($documento));$id=(int)($stmt->fetchColumn()?:0);
        if($id){$pdo->prepare('UPDATE Ve_DocVentaCliente SET Cliente=?,Direccion=?,Telefono=?,Email=?,Anulado=0 WHERE IdCliente=?')->execute(array($nombre,$direccion,$telefono,$correo,$id));}
        else{$pdo->prepare('INSERT INTO Ve_DocVentaCliente (Cliente,DniRuc,Direccion,Telefono,Email,Anulado,FechaReg,EsVIP) VALUES (?,?,?,?,?,0,NOW(),0)')->execute(array($nombre,$documento,$direccion,$telefono,$correo));$id=(int)$pdo->lastInsertId();}
        $pdo->prepare('INSERT INTO Tienda_ClienteCuenta (IdCliente,Documento,TipoDocumento,PasswordHash,FechaReg,UltimoAcceso) VALUES (?,?,?,?,NOW(),NOW())')->execute(array($id,$documento,$tipo,password_hash($password,PASSWORD_DEFAULT)));
        $pdo->commit();session_regenerate_id(true);$_SESSION['tienda_cliente_id']=$id;
        echo json_encode(array('ok'=>true,'client'=>array('IdCliente'=>$id,'documento'=>$documento,'nombre'=>$nombre,'direccion'=>$direccion,'telefono'=>$telefono,'correo'=>$correo)),JSON_UNESCAPED_UNICODE);exit;
    }
    if ($action === 'lookup') {
        tiendaRateLimit('lookup', 20, 3600);
        $documento=preg_replace('/\D/','',(string)($_GET['documento']??''));
        if(strlen($documento)!==8&&strlen($documento)!==11){http_response_code(422);echo json_encode(array('error'=>'La consulta admite DNI de 8 dígitos o RUC de 11 dígitos.'));exit;}
        $empresa=$pdo->query('SELECT CONSULTARUCURL,CONSULTARUCTOKEN FROM GEN_EMPRESA ORDER BY IDEMPRESA LIMIT 1')->fetch()?:array();
        $url=rtrim((string)($empresa['CONSULTARUCURL']??''),'/').'/'.(strlen($documento)===11?'ruc':'dni').'/'.$documento.'?token='.rawurlencode((string)($empresa['CONSULTARUCTOKEN']??''));
        $ch=curl_init($url);curl_setopt_array($ch,array(CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>15,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_FOLLOWLOCATION=>false));$raw=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);$data=json_decode((string)$raw,true)?:array();
        if($code>=400||!$data){http_response_code(404);echo json_encode(array('error'=>'No se encontraron datos para el documento.'));exit;}
        $nombre=strlen($documento)===11?(string)($data['razonSocial']??''):trim(implode(' ',array_filter(array($data['nombres']??'',$data['apellidoPaterno']??'',$data['apellidoMaterno']??''))));
        echo json_encode(array('nombre'=>$nombre,'direccion'=>(string)($data['direccion']??'')),JSON_UNESCAPED_UNICODE);exit;
    }
    if ($action === 'empresa') {
        $row = $pdo->query('SELECT RAZONSOCIAL, DIRECCION, TELEFONO FROM Gen_Empresa ORDER BY IDEMPRESA LIMIT 1')->fetch() ?: array();
        $metaStmt = $pdo->query("SELECT MetaKey, MetaValue FROM Gen_EmpresaConfig WHERE IdEmpresa=(SELECT IDEMPRESA FROM Gen_Empresa ORDER BY IDEMPRESA LIMIT 1) AND MetaKey IN ('logo_principal','nombre_comercial','actividad_comercial')");
        foreach ($metaStmt->fetchAll() as $metaRow) $row[$metaRow['MetaKey']] = $metaRow['MetaValue'];
        $row['DESCRIPCION'] = $row['actividad_comercial'] ?? 'CATÁLOGO DE PRODUCTOS';
        $row['logo_principal'] = isset($row['logo_principal']) && trim((string) $row['logo_principal']) !== '' ? '/' . ltrim(str_replace('\\', '/', $row['logo_principal']), '/') : '/resources/images/neurosoft-logo-transparent.png';
        $row['logo_principal'] .= (strpos($row['logo_principal'], '?') === false ? '?' : '&') . 'v=20261007-eden-1';
        echo json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); exit;
    }
    if ($action === 'filtros') {
        $categorias = $pdo->query("SELECT c.IdProductoCategoria id, TRIM(c.ProductoCategoria) nombre, COUNT(*) cantidad FROM Gen_ProductoCategoria c JOIN Gen_Producto p ON p.IdProductoCategoria=c.IdProductoCategoria AND p.Anulado=0 WHERE TRIM(COALESCE(c.ProductoCategoria,''))<>'' GROUP BY c.IdProductoCategoria,c.ProductoCategoria ORDER BY c.ProductoCategoria")->fetchAll();
        $marcas = $pdo->query("SELECT m.IdProductoMarca id, TRIM(m.ProductoMarca) nombre, COUNT(*) cantidad FROM Gen_ProductoMarca m JOIN Gen_Producto p ON p.IdProductoMarca=m.IdProductoMarca AND p.Anulado=0 WHERE TRIM(COALESCE(m.ProductoMarca,''))<>'' GROUP BY m.IdProductoMarca,m.ProductoMarca ORDER BY m.ProductoMarca")->fetchAll();
        $modelos = $pdo->query("SELECT TRIM(p.ProductoModelo) nombre, COUNT(*) cantidad FROM Gen_Producto p WHERE p.Anulado=0 AND TRIM(COALESCE(p.ProductoModelo,''))<>'' GROUP BY TRIM(p.ProductoModelo) ORDER BY TRIM(p.ProductoModelo)")->fetchAll();
        echo json_encode(array('categorias'=>$categorias,'marcas'=>$marcas,'modelos'=>$modelos), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); exit;
    }
    if ($action === 'categorias') {
        $rows = $pdo->query("SELECT c.IdProductoCategoria id, c.ProductoCategoria nombre, COUNT(*) cantidad FROM Gen_ProductoCategoria c JOIN Gen_Producto p ON p.IdProductoCategoria=c.IdProductoCategoria AND p.Anulado=0 GROUP BY c.IdProductoCategoria,c.ProductoCategoria ORDER BY c.ProductoCategoria")->fetchAll();
        echo json_encode($rows, JSON_UNESCAPED_UNICODE); exit;
    }
    $q = trim((string)($_GET['q'] ?? ''));
    $categoria = (int)($_GET['categoria'] ?? 0);
    $sql = "SELECT p.IdProducto id,p.CodigoBarra codigo,p.Producto nombre,p.ProductoDescCorto descripcion,p.ProductoDesc descripcion_detallada,p.PrecioContado precio,p.ProductoModelo modelo,p.Color color,p.Genero genero,p.Botapie botapie,p.Imagen imagen,p.Imagen2 imagen2,p.Imagen3 imagen3,m.ProductoMarca marca,t.ProductoTalla talla,c.ProductoCategoria categoria,GREATEST(0,COALESCE((SELECT SUM(pt.TotalCompras-pt.TotalVentas) FROM Gen_Producto_Totales pt WHERE pt.IdProducto=p.IdProducto),0)) stock FROM Gen_Producto p JOIN Gen_ProductoMarca m ON m.IdProductoMarca=p.IdProductoMarca JOIN Gen_ProductoCategoria c ON c.IdProductoCategoria=p.IdProductoCategoria LEFT JOIN Gen_ProductoTalla t ON t.IdProductoTalla=p.IdProductoTalla WHERE p.Anulado=0";
    $params = array();
    if ($categoria > 0) { $sql .= ' AND p.IdProductoCategoria=?'; $params[]=$categoria; }
    if ($q !== '') {
        $searchFields = array('p.Producto','p.CodigoBarra','p.ProductoModelo','m.ProductoMarca','p.Color','p.ProductoDescCorto','p.ProductoDesc','c.ProductoCategoria','t.ProductoTalla','p.Genero','p.Botapie');
        $terms = array_values(array_unique(array_filter(preg_split('/\s+/u', $q) ?: array())));
        foreach (array_slice($terms, 0, 12) as $term) {
            $sql .= ' AND (' . implode(' OR ', array_map(static fn($field) => $field . ' LIKE ?', $searchFields)) . ')';
            foreach ($searchFields as $_) $params[] = '%' . $term . '%';
        }
    }
    $sql .= ' ORDER BY p.IdProducto DESC LIMIT 300';
    $stmt=$pdo->prepare($sql); $stmt->execute($params); $rows=$stmt->fetchAll();
    foreach ($rows as &$row) {
        foreach (array('imagen','imagen2','imagen3') as $field) {
            $image = trim(str_replace('\\', '/', (string)$row[$field]));
            $row[$field] = '';
            if ($image === '') continue;
            $relative = strpos(ltrim($image, '/'), 'resources/images/') === 0 ? ltrim($image, '/') : 'resources/images/productos/' . basename($image);
            $root = realpath(dirname(__DIR__, 2) . '/resources/images');
            $actual = realpath(dirname(__DIR__, 2) . '/' . $relative);
            if ($root && $actual && is_file($actual) && strpos(str_replace('\\', '/', $actual), str_replace('\\', '/', $root) . '/') === 0) {
                $row[$field] = '/' . implode('/', array_map('rawurlencode', explode('/', $relative)));
            }
        }
        $row['precio']=(float)$row['precio']; $row['stock']=(float)$row['stock'];
    }
    echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500); echo json_encode(array('error'=>'No se pudo cargar la tienda.'));
}
