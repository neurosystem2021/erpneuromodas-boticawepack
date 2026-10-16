<?php
// The installed spreadsheet library emits PHP 7 notices; keep binary downloads clean.
ini_set('display_errors','0');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
header('Cache-Control: no-store, private');
if (session_status()!==PHP_SESSION_ACTIVE) session_start();
function managementError($status,$message) { http_response_code($status); header('Content-Type: application/json; charset=utf-8'); echo json_encode(array('error'=>$message),JSON_UNESCAPED_UNICODE); exit; }
if (empty($_SESSION['user']) || (isset($_SESSION['expire']) && (int)$_SESSION['expire']<time())) managementError(401,'Inicia sesión nuevamente.');
require_once dirname(__DIR__).'/config/database.php';
require_once __DIR__.'/management-report-core.php';
try {
    $db=appPdoConnection();
    $users=managementRows($db,'SELECT IdUsuarioPerfil FROM Seg_Usuario WHERE Usuario=? AND COALESCE(Anulado,0)=0 LIMIT 1',array($_SESSION['user']));
    if (!$users) managementError(403,'Usuario no autorizado.');
    $profile=(int)$users[0]['IdUsuarioPerfil'];
    if ($profile!==1) {
        $permission=managementRows($db,"SELECT p.Lectura FROM Seg_UsuarioModulo_has_UsuarioPerfil p JOIN Seg_UsuarioModulo m ON m.IdUsuarioModulo=p.IdUsuarioModulo WHERE p.IdUsuarioPerfil=? AND LOWER(m.UsuarioModulo)='reportegerencial.php' AND COALESCE(p.Anulado,0)=0",array($profile));
        if (!$permission || (int)$permission[0]['Lectura']!==1) managementError(403,'Este consolidado requiere permiso gerencial.');
    }
    session_write_close();
    $input=$_GET;
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        $input=json_decode(file_get_contents('php://input'),true);
        if (!is_array($input)) managementError(422,'Filtros inválidos.');
    } elseif ($_SERVER['REQUEST_METHOD']!=='GET') managementError(405,'Método no permitido.');
    // One consistent snapshot for sales and cash, without changing any business data.
    $db->beginTransaction();
    $localFile=dirname(__DIR__).'/config/management-report.local.php';
    $defaults=is_readable($localFile) ? require $localFile : array();
    $report=managementReport($db,$input,is_array($defaults) ? $defaults : array());
    $db->commit();
    if (!empty($input['excel'])) {
        require_once __DIR__.'/vendor/autoload.php';
        require_once __DIR__.'/management-report-excel.php';
        $book=managementWorkbook($report);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Reporte-Gerencial-'.$report['filtros']['desde'].'-'.$report['filtros']['hasta'].'.xlsx"');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save('php://output');
    } else {
        header('Content-Type: application/json; charset=utf-8'); echo json_encode($report,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
    }
} catch (InvalidArgumentException $error) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    managementError(422,$error->getMessage());
} catch (Throwable $error) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    error_log('[MANAGEMENT-REPORT] '.$error->getMessage()); managementError(500,'No se pudo generar el reporte gerencial.');
}
