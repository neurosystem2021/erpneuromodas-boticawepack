<?php
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/views/validateUser.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/models/DBManager.php';
require_once __DIR__ . '/inventario-inicial-importador.php';

function inventarioInicialResponder($datos, $estado = 200)
{
    http_response_code($estado);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') inventarioInicialResponder(array('error' => 'Método no permitido.'), 405);
$usuario = trim((string) (isset($_SESSION['user']) ? $_SESSION['user'] : ''));
$idPerfil = (int) (isset($_SESSION['idPerfil']) ? $_SESSION['idPerfil'] : 0);
if ($idPerfil !== 1) {
    inventarioInicialResponder(array('error' => 'Solo el perfil SUPERADMIN puede registrar el inventario inicial.'), 403);
}
if (empty($_FILES['archivo']) || !isset($_FILES['archivo']['tmp_name'])) {
    inventarioInicialResponder(array('error' => 'Seleccione un archivo XLS o XLSX.'), 422);
}

$archivo = $_FILES['archivo'];
if ((int) $archivo['error'] !== UPLOAD_ERR_OK) inventarioInicialResponder(array('error' => 'No se pudo recibir el archivo Excel.'), 422);
if ((int) $archivo['size'] <= 0 || (int) $archivo['size'] > 12 * 1024 * 1024) {
    inventarioInicialResponder(array('error' => 'El archivo debe pesar menos de 12 MB.'), 422);
}
$extension = strtolower(pathinfo((string) $archivo['name'], PATHINFO_EXTENSION));
if (!in_array($extension, array('xls', 'xlsx'), true)) inventarioInicialResponder(array('error' => 'El archivo debe estar en formato XLS o XLSX.'), 422);
$idAlmacen = isset($_POST['idAlmacen']) ? (int) $_POST['idAlmacen'] : 0;
if ($idAlmacen <= 0) inventarioInicialResponder(array('error' => 'No se identificó el almacén de destino.'), 422);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = null;
try {
    $db = getMysqliLink();
    $db->set_charset('utf8mb4');
    $analisis = inventarioInicialAnalizarArchivo($db, $archivo['tmp_name'], $archivo['name'], $idAlmacen);
    $respuestaAnalisis = $analisis;
    unset($respuestaAnalisis['productos']);
    $modo = isset($_POST['modo']) ? (string) $_POST['modo'] : 'vista_previa';
    if ($modo !== 'importar') {
        $db->close();
        inventarioInicialResponder(array('ok' => true, 'analisis' => $respuestaAnalisis));
    }
    if (empty($analisis['puede_importar'])) {
        $db->close();
        $estado = !empty($analisis['movimiento_existente']) ? 409 : 422;
        inventarioInicialResponder(array(
            'error' => !empty($analisis['movimiento_existente'])
                ? 'Ya existe un ingreso inicial para este almacén.'
                : 'Corrija los errores del archivo antes de registrar el movimiento.',
            'analisis' => $respuestaAnalisis
        ), $estado);
    }

    $resultado = inventarioInicialGuardar($db, $analisis, $idAlmacen, $usuario);
    $db->close();
    inventarioInicialResponder(array('ok' => true, 'resultado' => $resultado));
} catch (Throwable $error) {
    if ($db) $db->close();
    error_log('Ingreso inicial de inventario: ' . $error->getMessage());
    $estado = stripos($error->getMessage(), 'ya existe') !== false ? 409 : 422;
    inventarioInicialResponder(array('error' => 'No se pudo registrar el ingreso inicial: ' . $error->getMessage()), $estado);
}

