<?php
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/views/validateUser.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/models/DBManager.php';
require_once __DIR__ . '/productos-importador.php';

function productoImportadorResponder($datos, $estado = 200)
{
    http_response_code($estado);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') productoImportadorResponder(array('error' => 'Método no permitido.'), 405);
if ((int) (isset($_SESSION['idPerfil']) ? $_SESSION['idPerfil'] : 0) !== 1) {
    productoImportadorResponder(array('error' => 'Solo SUPERADMIN puede importar productos.'), 403);
}
if (empty($_FILES['archivo']) || !isset($_FILES['archivo']['tmp_name'])) {
    productoImportadorResponder(array('error' => 'Seleccione un archivo XLS o XLSX.'), 422);
}
$archivo = $_FILES['archivo'];
if ((int) $archivo['error'] !== UPLOAD_ERR_OK) productoImportadorResponder(array('error' => 'No se pudo recibir el archivo Excel.'), 422);
if ((int) $archivo['size'] <= 0 || (int) $archivo['size'] > 12 * 1024 * 1024) {
    productoImportadorResponder(array('error' => 'El archivo debe pesar menos de 12 MB.'), 422);
}
$extension = strtolower(pathinfo((string) $archivo['name'], PATHINFO_EXTENSION));
if (!in_array($extension, array('xls', 'xlsx'), true)) productoImportadorResponder(array('error' => 'El archivo debe estar en formato XLS o XLSX.'), 422);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = getMysqliLink();
$db->set_charset('utf8mb4');
try {
    $analisis = productoImportadorAnalizarArchivo($archivo['tmp_name'], $archivo['name']);
    $resumenBase = productoImportadorResumenBase($db, $analisis);
    $respuestaAnalisis = $analisis;
    unset($respuestaAnalisis['productos']);
    $respuestaAnalisis['muestra'] = array_slice($analisis['productos'], 0, 8);
    $respuestaAnalisis['puede_importar'] = $analisis['validas'] > 0 && $analisis['invalidas'] === 0;
    $respuestaAnalisis['reemplaza_stock'] = false;
    $respuestaAnalisis['importa_stock'] = false;
    $respuestaAnalisis = array_merge($respuestaAnalisis, $resumenBase);
    $modo = isset($_POST['modo']) ? $_POST['modo'] : 'vista_previa';
    if ($modo !== 'importar') {
        $db->close();
        productoImportadorResponder(array('ok' => true, 'analisis' => $respuestaAnalisis));
    }
    if (!$respuestaAnalisis['puede_importar']) {
        $db->close();
        productoImportadorResponder(array('error' => 'Corrija las filas inválidas antes de importar.', 'analisis' => $respuestaAnalisis), 422);
    }
    $usuario = trim((string) (isset($_SESSION['user']) ? $_SESSION['user'] : 'SUPERADMIN'));
    $db->begin_transaction();
    try {
        $resultado = productoImportadorImportar($db, $analisis, $usuario);
        $db->commit();
    } catch (Throwable $error) {
        $db->rollback();
        throw $error;
    }
    $db->close();
    productoImportadorResponder(array('ok' => true, 'resultado' => $resultado));
} catch (Throwable $error) {
    if ($db) $db->close();
    error_log('Importación de productos: ' . $error->getMessage());
    productoImportadorResponder(array('error' => 'No se pudo importar el Excel: ' . $error->getMessage()), 422);
}

