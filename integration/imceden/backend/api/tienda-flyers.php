<?php
declare(strict_types=1);

ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$root = dirname(__DIR__);
$relativeDirectory = 'resources/images/tienda/flyers';
$directory = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeDirectory);
$allowedMime = array(
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
);

function flyerResponse(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function flyerRequireStaff(): void
{
    if (empty($_SESSION['user'])) flyerResponse(array('error' => 'Sesion no autorizada.'), 401);
    $source = (string) ($_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '');
    if ($source === '') return;
    $sourceHost = strtolower((string) parse_url($source, PHP_URL_HOST));
    $requestHost = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
    if ($sourceHost === '' || !hash_equals($requestHost, $sourceHost)) {
        flyerResponse(array('error' => 'Solicitud no autorizada.'), 403);
    }
}

function flyerList(string $directory, string $relativeDirectory): array
{
    if (!is_dir($directory)) return array();
    $files = array_values(array_filter(scandir($directory) ?: array(), static function ($name) use ($directory) {
        return $name !== '.' && $name !== '..'
            && is_file($directory . DIRECTORY_SEPARATOR . $name)
            && preg_match('/\.(?:jpe?g|png|gif|webp)$/i', $name);
    }));
    $orderPath = $directory . DIRECTORY_SEPARATOR . '.order.json';
    $order = is_file($orderPath) ? json_decode((string) file_get_contents($orderPath), true) : array();
    if (!is_array($order)) $order = array();
    $positions = array_flip(array_values(array_filter($order, static fn($name) => is_string($name))));
    usort($files, static function ($left, $right) use ($directory, $positions) {
        $leftPosition = $positions[$left] ?? PHP_INT_MAX;
        $rightPosition = $positions[$right] ?? PHP_INT_MAX;
        if ($leftPosition !== $rightPosition) return $leftPosition <=> $rightPosition;
        $leftTime = filemtime($directory . DIRECTORY_SEPARATOR . $left) ?: 0;
        $rightTime = filemtime($directory . DIRECTORY_SEPARATOR . $right) ?: 0;
        return $rightTime <=> $leftTime;
    });
    return array_map(static function ($name) use ($directory, $relativeDirectory) {
        $path = $directory . DIRECTORY_SEPARATOR . $name;
        return array(
            'name' => $name,
            'url' => '/' . $relativeDirectory . '/' . rawurlencode($name),
            'size' => filesize($path) ?: 0,
            'modified' => filemtime($path) ?: 0,
        );
    }, $files);
}

function flyerSaveOrder(string $directory, array $names): void
{
    $valid = array_values(array_filter(array_unique($names), static function ($name) use ($directory) {
        return is_string($name)
            && basename($name) === $name
            && preg_match('/\.(?:jpe?g|png|gif|webp)$/i', $name)
            && is_file($directory . DIRECTORY_SEPARATOR . $name);
    }));
    file_put_contents(
        $directory . DIRECTORY_SEPARATOR . '.order.json',
        json_encode($valid, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    );
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if ($method === 'GET') {
    flyerResponse(array('items' => flyerList($directory, $relativeDirectory)));
}
if ($method !== 'POST') {
    header('Allow: GET, POST');
    flyerResponse(array('error' => 'Metodo no permitido.'), 405);
}

flyerRequireStaff();
if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
    flyerResponse(array('error' => 'No se pudo preparar la carpeta de publicidad.'), 500);
}

$action = strtolower(trim((string) ($_POST['action'] ?? 'upload')));
if ($action === 'delete') {
    $name = basename((string) ($_POST['name'] ?? ''));
    if ($name === '' || !preg_match('/\.(?:jpe?g|png|gif|webp)$/i', $name)) {
        flyerResponse(array('error' => 'Flyer no valido.'), 422);
    }
    $path = $directory . DIRECTORY_SEPARATOR . $name;
    if (is_file($path) && !unlink($path)) {
        flyerResponse(array('error' => 'No se pudo eliminar el flyer.'), 500);
    }
    $remaining = array_column(flyerList($directory, $relativeDirectory), 'name');
    flyerSaveOrder($directory, $remaining);
    flyerResponse(array('ok' => true, 'items' => flyerList($directory, $relativeDirectory)));
}

if ($action === 'order') {
    $names = json_decode((string) ($_POST['names'] ?? '[]'), true);
    if (!is_array($names)) flyerResponse(array('error' => 'Orden no valido.'), 422);
    flyerSaveOrder($directory, $names);
    flyerResponse(array('ok' => true, 'items' => flyerList($directory, $relativeDirectory)));
}

$uploads = $_FILES['flyers'] ?? null;
if (!$uploads || !isset($uploads['name'])) {
    flyerResponse(array('error' => 'Seleccione al menos una imagen.'), 422);
}

$names = is_array($uploads['name']) ? $uploads['name'] : array($uploads['name']);
$temporary = is_array($uploads['tmp_name']) ? $uploads['tmp_name'] : array($uploads['tmp_name']);
$errors = is_array($uploads['error']) ? $uploads['error'] : array($uploads['error']);
$sizes = is_array($uploads['size']) ? $uploads['size'] : array($uploads['size']);
$finfo = new finfo(FILEINFO_MIME_TYPE);
$saved = 0;
$savedNames = array();

foreach ($names as $index => $originalName) {
    if (($errors[$index] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
    $temp = (string) ($temporary[$index] ?? '');
    $size = (int) ($sizes[$index] ?? 0);
    if ($size <= 0 || $size > 8 * 1024 * 1024 || !is_uploaded_file($temp)) continue;
    $mime = (string) $finfo->file($temp);
    if (!isset($allowedMime[$mime]) || @getimagesize($temp) === false) continue;
    $base = pathinfo((string) $originalName, PATHINFO_FILENAME);
    $base = trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', $base), '-');
    if ($base === '') $base = 'publicidad';
    $name = date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '-' . substr($base, 0, 60) . '.' . $allowedMime[$mime];
    if (move_uploaded_file($temp, $directory . DIRECTORY_SEPARATOR . $name)) {
        $saved++;
        $savedNames[] = $name;
    }
}

if ($saved === 0) {
    flyerResponse(array('error' => 'No se guardaron imagenes. Use JPG, PNG, GIF o WEBP de hasta 8 MB.'), 422);
}

$currentNames = array_column(flyerList($directory, $relativeDirectory), 'name');
flyerSaveOrder($directory, array_merge($savedNames, $currentNames));
flyerResponse(array(
    'ok' => true,
    'saved' => $saved,
    'items' => flyerList($directory, $relativeDirectory),
));
