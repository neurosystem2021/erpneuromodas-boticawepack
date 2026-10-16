<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

function responderVigencia($ok, $message, $status = 200)
{
    http_response_code($status);
    echo json_encode(array('ok' => $ok, 'message' => $message), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') responderVigencia(false, 'Método no permitido.', 405);
if (!isset($_SESSION['user']) || strtolower(trim((string) $_SESSION['user'])) !== 'superadmin') responderVigencia(false, 'Solo SUPERADMIN puede modificar la vigencia.', 403);
if (empty($_POST['csrf']) || empty($_SESSION['vigencia_csrf']) || !hash_equals($_SESSION['vigencia_csrf'], $_POST['csrf'])) responderVigencia(false, 'La sesión de seguridad venció. Actualice la página.', 403);

$desde = trim($_POST['vigencia_desde'] ?? '');
$hasta = trim($_POST['vigencia_hasta'] ?? '');
$fechaLimitePago = trim($_POST['vigencia_fecha_ultimo_pago'] ?? '');
$fechaLimiteApp = trim($_POST['ventas_app_fecha_limite'] ?? '');
$estado = strtoupper(trim($_POST['vigencia_estado_pago'] ?? 'PENDIENTE'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) responderVigencia(false, 'Seleccione fechas de vigencia válidas.', 422);
if ($hasta < $desde) responderVigencia(false, 'La fecha final no puede ser anterior a la fecha inicial.', 422);
if ($fechaLimitePago !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaLimitePago)) responderVigencia(false, 'Seleccione una fecha limite de pago valida.', 422);
if ($fechaLimiteApp !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaLimiteApp)) responderVigencia(false, 'Seleccione una fecha limite valida para Ventas App.', 422);
if (!in_array($estado, array('PENDIENTE', 'PAGADO', 'VENCIDO'), true)) responderVigencia(false, 'El estado de pago no es válido.', 422);

$zonasPais = array('PE'=>'America/Lima','CO'=>'America/Bogota','EC'=>'America/Guayaquil','BO'=>'America/La_Paz','CL'=>'America/Santiago','AR'=>'America/Argentina/Buenos_Aires','MX'=>'America/Mexico_City','US'=>'America/New_York','ES'=>'Europe/Madrid');
$paisSistema = strtoupper(trim((string) ($_POST['sistema_pais'] ?? 'PE')));
if (!isset($zonasPais[$paisSistema])) $paisSistema = 'PE';

$avisosEntrada = json_decode($_POST['social_avisos_json'] ?? '[]', true);
if (!is_array($avisosEntrada)) responderVigencia(false, 'La lista de avisos no es válida.', 422);
$avisos = array();
foreach (array_slice($avisosEntrada, 0, 50) as $aviso) {
    if (!is_array($aviso)) continue;
    $tipoAviso = strtoupper(trim((string) ($aviso['tipo'] ?? 'FESTEJO')));
    $iconoAviso = strtolower(trim((string) ($aviso['icono'] ?? 'star')));
    $mensajeAviso = trim((string) ($aviso['mensaje'] ?? ''));
    $desdeAviso = trim((string) ($aviso['desde'] ?? ''));
    $hastaAviso = trim((string) ($aviso['hasta'] ?? ''));
    if ($mensajeAviso === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $desdeAviso) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hastaAviso)) responderVigencia(false, 'Revise el mensaje y las fechas del aviso programado.', 422);
    if ($desdeAviso !== '' && $hastaAviso !== '' && $hastaAviso < $desdeAviso) $hastaAviso = $desdeAviso;
    if (!in_array($tipoAviso, array('FESTEJO','SALUDO','INFORMATIVO'), true)) $tipoAviso = 'FESTEJO';
    if (!in_array($iconoAviso, array('star','heart','gift','birthday-cake','bell','smile-o','info-circle'), true)) $iconoAviso = 'star';
    $socialGif = trim((string) ($aviso['gif'] ?? ''));
    $gifData = (string) ($aviso['gif_data'] ?? '');
    if ($gifData !== '' && preg_match('#^data:image/gif;base64,(.+)$#s', $gifData, $gifMatch)) {
        $gifBin = base64_decode($gifMatch[1], true);
        if ($gifBin === false || strlen($gifBin) > 3145728) responderVigencia(false, 'Uno de los GIF de avisos no es válido o supera los 3 MB.', 422);
        $socialDir = __DIR__ . '/../resources/uploads/avisos'; if (!is_dir($socialDir) && !mkdir($socialDir, 0775, true)) responderVigencia(false, 'No se pudo preparar la carpeta de avisos.', 500);
        $socialName = 'social-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.gif';
        if (file_put_contents($socialDir . '/' . $socialName, $gifBin) === false) responderVigencia(false, 'No se pudo guardar el GIF del aviso.', 500);
        $socialGif = 'resources/uploads/avisos/' . $socialName;
    }
    $avisos[] = array('id'=>preg_replace('/[^a-zA-Z0-9_-]/','',(string)($aviso['id'] ?? uniqid('aviso-'))),'activo'=>true,'tipo'=>$tipoAviso,'icono'=>$iconoAviso,'emoji'=>mb_substr(trim((string)($aviso['emoji']??'')),0,8),'gif'=>$socialGif,'mensaje'=>mb_substr($mensajeAviso,0,500),'desde'=>$desdeAviso,'hasta'=>$hastaAviso,'hora_inicio'=>preg_match('/^\d{2}:\d{2}$/',(string)($aviso['hora_inicio']??''))?$aviso['hora_inicio']:'00:00','hora_fin'=>preg_match('/^\d{2}:\d{2}$/',(string)($aviso['hora_fin']??''))?$aviso['hora_fin']:'23:59','intervalo'=>max(1,min(1440,(int)($aviso['intervalo']??30))),'duracion'=>max(5,min(120,(int)($aviso['duracion']??15))));
}
$vigenciaGif = trim((string) ($_POST['vigencia_gif_actual'] ?? ''));
if (!empty($_FILES['vigencia_gif']['name']) && is_uploaded_file($_FILES['vigencia_gif']['tmp_name'])) {
    if ((int) $_FILES['vigencia_gif']['size'] > 3145728) responderVigencia(false, 'El GIF no puede superar los 3 MB.', 422);
    $mime = function_exists('mime_content_type') ? mime_content_type($_FILES['vigencia_gif']['tmp_name']) : '';
    if ($mime !== 'image/gif') responderVigencia(false, 'Seleccione un archivo GIF válido.', 422);
    $dir = __DIR__ . '/../resources/uploads/avisos'; if (!is_dir($dir) && !mkdir($dir, 0775, true)) responderVigencia(false, 'No se pudo preparar la carpeta de avisos.', 500);
    $nombreGif = 'aviso-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.gif';
    if (!move_uploaded_file($_FILES['vigencia_gif']['tmp_name'], $dir . '/' . $nombreGif)) responderVigencia(false, 'No se pudo guardar el GIF.', 500);
    $vigenciaGif = 'resources/uploads/avisos/' . $nombreGif;
}
$pagoQr = trim((string) ($_POST['pago_qr_actual'] ?? ''));
if (!empty($_FILES['pago_qr']['name']) && is_uploaded_file($_FILES['pago_qr']['tmp_name'])) {
    if ((int) $_FILES['pago_qr']['size'] > 4194304) responderVigencia(false, 'La imagen QR no puede superar los 4 MB.', 422);
    $mimeQr = function_exists('mime_content_type') ? mime_content_type($_FILES['pago_qr']['tmp_name']) : '';
    $extQr = array('image/png'=>'png','image/jpeg'=>'jpg','image/webp'=>'webp')[$mimeQr] ?? '';
    if ($extQr === '') responderVigencia(false, 'Seleccione una imagen QR PNG, JPG o WEBP.', 422);
    $dirQr = __DIR__ . '/../resources/uploads/avisos'; if (!is_dir($dirQr) && !mkdir($dirQr, 0775, true)) responderVigencia(false, 'No se pudo preparar la carpeta de pagos.', 500);
    $nombreQr = 'pago-qr-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extQr;
    if (!move_uploaded_file($_FILES['pago_qr']['tmp_name'], $dirQr . '/' . $nombreQr)) responderVigencia(false, 'No se pudo guardar la imagen QR.', 500);
    $pagoQr = 'resources/uploads/avisos/' . $nombreQr;
}

$meta = array(
    'sistema_pais' => $paisSistema,
    'sistema_zona_horaria' => $zonasPais[$paisSistema],
    'vigencia_cliente' => trim($_POST['vigencia_cliente'] ?? ''),
    'vigencia_desde' => $desde,
    'vigencia_hasta' => $hasta,
    'vigencia_estado_pago' => $estado,
    'vigencia_importe' => number_format(max(0, (float) ($_POST['vigencia_importe'] ?? 0)), 2, '.', ''),
    'vigencia_fecha_ultimo_pago' => $fechaLimitePago,
    'ventas_app_bloqueo_activo' => isset($_POST['ventas_app_bloqueo_activo']) ? '1' : '0',
    'ventas_app_aviso_activo' => isset($_POST['ventas_app_aviso_activo']) ? '1' : '0',
    'ventas_app_fecha_limite' => $fechaLimiteApp,
    'ventas_app_estado_pago' => in_array(strtoupper(trim($_POST['ventas_app_estado_pago'] ?? 'PAGADO')), array('PENDIENTE','PAGADO','VENCIDO'), true) ? strtoupper(trim($_POST['ventas_app_estado_pago'])) : 'PAGADO',
    'ventas_app_dias_anticipacion' => (string) max(1, min(365, (int) ($_POST['ventas_app_dias_anticipacion'] ?? 20))),
    'ventas_app_mensaje' => trim((string) ($_POST['ventas_app_mensaje'] ?? '')),
    'vigencia_dias_anticipacion' => (string) max(1, min(365, (int) ($_POST['vigencia_dias_anticipacion'] ?? 20))),
    'vigencia_mensaje' => trim($_POST['vigencia_mensaje'] ?? ''),
    'vigencia_icono' => in_array(strtolower(trim($_POST['vigencia_icono'] ?? 'bell')), array('bell','clock-o','exclamation-triangle','info-circle','shield','hourglass-half'), true) ? strtolower(trim($_POST['vigencia_icono'])) : 'bell',
    'vigencia_velocidad_segundos' => (string) max(6, min(90, (int) ($_POST['vigencia_velocidad_segundos'] ?? 22))),
    'vigencia_emoji' => mb_substr(trim((string) ($_POST['vigencia_emoji'] ?? '')), 0, 8),
    'vigencia_gif' => $vigenciaGif,
    'pago_flotante_activo' => isset($_POST['pago_flotante_activo']) ? '1' : '0',
    'pago_whatsapp' => preg_replace('/\D+/', '', (string) ($_POST['pago_whatsapp'] ?? '')),
    'pago_cuentas' => trim((string) ($_POST['pago_cuentas'] ?? '')),
    'pago_qr' => $pagoQr,
    'vigencia_aviso_activo' => isset($_POST['vigencia_aviso_activo']) ? '1' : '0',
    'social_aviso_activo' => isset($_POST['social_aviso_activo']) ? '1' : '0',
    'social_tipo' => in_array(strtoupper(trim($_POST['social_tipo'] ?? 'FESTEJO')), array('FESTEJO', 'SALUDO', 'INFORMATIVO'), true) ? strtoupper(trim($_POST['social_tipo'])) : 'FESTEJO',
    'social_mensaje' => trim($_POST['social_mensaje'] ?? ''),
    'social_desde' => trim($_POST['social_desde'] ?? ''),
    'social_hasta' => trim($_POST['social_hasta'] ?? ''),
    'social_hora_inicio' => preg_match('/^\d{2}:\d{2}$/', $_POST['social_hora_inicio'] ?? '') ? $_POST['social_hora_inicio'] : '00:00',
    'social_hora_fin' => preg_match('/^\d{2}:\d{2}$/', $_POST['social_hora_fin'] ?? '') ? $_POST['social_hora_fin'] : '23:59',
    'social_intervalo_minutos' => (string) max(1, min(1440, (int) ($_POST['social_intervalo_minutos'] ?? 30))),
    'social_duracion_segundos' => (string) max(5, min(120, (int) ($_POST['social_duracion_segundos'] ?? 12)))
);
$meta['social_avisos_json'] = json_encode($avisos, JSON_UNESCAPED_UNICODE);

if ($meta['social_aviso_activo'] === '1' && !isset($_POST['social_avisos_json'])) {
    if ($meta['social_mensaje'] === '') responderVigencia(false, 'Escriba el mensaje del aviso de festejo o saludo.', 422);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $meta['social_desde']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $meta['social_hasta'])) responderVigencia(false, 'Seleccione fechas válidas para el aviso de festejo.', 422);
    if ($meta['social_hasta'] < $meta['social_desde']) responderVigencia(false, 'La fecha final del aviso no puede ser anterior a la inicial.', 422);
}

require_once __DIR__ . '/../models/DBManager.php';
$db = getMysqliLink();
$db->begin_transaction();
try {
    $idEmpresa = (int) ($_POST['id_empresa'] ?? 1);
    $select = $db->prepare('SELECT IdEmpresaConfig FROM Gen_EmpresaConfig WHERE IdEmpresa=? AND MetaKey=? ORDER BY IdEmpresaConfig ASC LIMIT 1');
    $update = $db->prepare('UPDATE Gen_EmpresaConfig SET MetaValue=? WHERE IdEmpresaConfig=?');
    $insert = $db->prepare('INSERT INTO Gen_EmpresaConfig (IdEmpresa, MetaKey, MetaValue) VALUES (?, ?, ?)');
    foreach ($meta as $key => $value) {
        $select->bind_param('is', $idEmpresa, $key); $select->execute();
        $row = $select->get_result()->fetch_assoc();
        if ($row) { $id = (int) $row['IdEmpresaConfig']; $update->bind_param('si', $value, $id); $update->execute(); }
        else { $insert->bind_param('iss', $idEmpresa, $key, $value); $insert->execute(); }
    }
    $select->close(); $update->close(); $insert->close();
    $db->commit();
    responderVigencia(true, 'Comunicado y vigencia guardados correctamente.');
} catch (Throwable $e) {
    $db->rollback();
    responderVigencia(false, 'No se pudo guardar la configuración: ' . $e->getMessage(), 422);
}
