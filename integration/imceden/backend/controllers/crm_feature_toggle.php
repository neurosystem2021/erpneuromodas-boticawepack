<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once dirname(__DIR__) . '/config/crm.php';

function crmFeatureResponse($status, array $payload)
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$user = trim((string) ($_SESSION['user'] ?? ''));
if ($user === '' || strcasecmp($user, 'superadmin') !== 0) {
    crmFeatureResponse(403, array('error' => 'Solo superadmin puede cambiar el acceso al CRM.'));
}
if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    crmFeatureResponse(405, array('error' => 'Método no permitido.'));
}
$csrf = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
$sessionCsrf = (string) ($_SESSION['login_csrf'] ?? '');
if ($sessionCsrf === '' || $csrf === '' || !hash_equals($sessionCsrf, $csrf)) {
    crmFeatureResponse(403, array('error' => 'La sesión de seguridad venció. Recargue la página.'));
}
$input = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($input) || !array_key_exists('enabled', $input) || !is_bool($input['enabled'])) {
    crmFeatureResponse(422, array('error' => 'Estado no válido.'));
}

try {
    $saved = crmSaveFeatureState($input['enabled'], $user);
    crmFeatureResponse(200, array(
        'enabled' => $saved['enabled'],
        'message' => $saved['enabled'] ? 'CRM habilitado correctamente.' : 'CRM deshabilitado correctamente.',
    ));
} catch (Throwable $error) {
    $status = (int) $error->getCode();
    crmFeatureResponse($status >= 400 && $status <= 599 ? $status : 500, array('error' => $error->getMessage()));
}
