<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

function responder($ok, $message, $extra = array(), $status = 200)
{
    http_response_code($status);
    echo json_encode(array_merge(array('ok' => $ok, 'message' => $message), $extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método no permitido.', array(), 405);
}
if (!isset($_SESSION['user']) || strtolower(trim((string) $_SESSION['user'])) !== 'superadmin') {
    responder(false, 'Solo SUPERADMIN puede cambiar esta configuración.', array(), 403);
}
if (empty($_POST['csrf']) || empty($_SESSION['empresa_config_csrf']) || !hash_equals($_SESSION['empresa_config_csrf'], $_POST['csrf'])) {
    responder(false, 'La sesión de seguridad venció. Actualice la página.', array(), 403);
}

require_once __DIR__ . '/../models/DBManager.php';
header('Content-Type: application/json; charset=utf-8');
$db = getMysqliLink();
$db->begin_transaction();

try {
    $idEmpresa = (int) ($_POST['id_empresa'] ?? 1);
    $razonSocial = trim($_POST['razon_social'] ?? '');
    $ruc = preg_replace('/\D+/', '', $_POST['ruc'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $celular = trim($_POST['celular'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $ubigeo = trim($_POST['ubigeo'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $provincia = trim($_POST['provincia'] ?? '');
    $distrito = trim($_POST['distrito'] ?? '');
    $igv = ((float) ($_POST['igv'] ?? 18)) / 100;

    if ($razonSocial === '' || strlen($ruc) !== 11 || $direccion === '') {
        throw new RuntimeException('Complete razón social, RUC válido de 11 dígitos y dirección fiscal.');
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('El correo electrónico no es válido.');
    }

    $stmt = $db->prepare("UPDATE GEN_EMPRESA SET RAZONSOCIAL=?, RUC=?, DIRECCION=?, IGV=?, TELEFONO=?, EMAIL=?, CELULAR=?, UBIGEO=?, DEPARTAMENTO=?, PROVINCIA=?, DISTRITO=? WHERE IDEMPRESA=?");
    if (!$stmt) {
        throw new RuntimeException('No se pudo preparar la actualización de la empresa: ' . $db->error);
    }
    $stmt->bind_param('sssdsssssssi', $razonSocial, $ruc, $direccion, $igv, $telefono, $email, $celular, $ubigeo, $departamento, $provincia, $distrito, $idEmpresa);
    if (!$stmt->execute()) {
        throw new RuntimeException('No se pudieron guardar los datos de empresa.');
    }
    $stmt->close();

    $meta = array(
        'nombre_comercial' => trim($_POST['nombre_comercial'] ?? ''),
        'telefono_soporte' => trim($_POST['telefono_soporte'] ?? ''),
        'sunat_entorno' => in_array($_POST['sunat_entorno'] ?? '', array('FE_BETA', 'FE_PRODUCCION'), true) ? $_POST['sunat_entorno'] : 'FE_BETA',
        'usuario_sol' => trim($_POST['usuario_sol'] ?? '')
    );
    foreach (array('clave_sol', 'clave_certificado') as $secretKey) {
        if (isset($_POST[$secretKey]) && trim($_POST[$secretKey]) !== '') {
            $meta[$secretKey] = trim($_POST[$secretKey]);
        }
    }

    $uploadDir = realpath(__DIR__ . '/../resources') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'empresa';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        throw new RuntimeException('No se pudo crear la carpeta para imágenes.');
    }
    $imageFields = array('logo_principal' => 3145728, 'logo_ticket' => 3145728, 'imagen_proforma' => 4194304);
    $allowedMime = array('image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp');
    foreach ($imageFields as $field => $maxBytes) {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) continue;
        if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK || $_FILES[$field]['size'] > $maxBytes) {
            throw new RuntimeException('El archivo de ' . str_replace('_', ' ', $field) . ' no es válido o supera el tamaño permitido.');
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES[$field]['tmp_name']);
        if (!isset($allowedMime[$mime]) || @getimagesize($_FILES[$field]['tmp_name']) === false) {
            throw new RuntimeException('Solo se permiten imágenes PNG, JPG o WEBP.');
        }
        $filename = $field . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . '.' . $allowedMime[$mime];
        if (!move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . DIRECTORY_SEPARATOR . $filename)) {
            throw new RuntimeException('No se pudo guardar la imagen seleccionada.');
        }
        $meta[$field] = 'resources/uploads/empresa/' . $filename;
    }

    if (isset($_FILES['certificado']) && $_FILES['certificado']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['certificado']['error'] !== UPLOAD_ERR_OK || $_FILES['certificado']['size'] > 5242880) {
            throw new RuntimeException('El certificado no es válido o supera 5 MB.');
        }
        $extension = strtolower(pathinfo($_FILES['certificado']['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, array('pfx', 'p12'), true)) {
            throw new RuntimeException('El certificado debe tener extensión PFX o P12.');
        }
        $meta['certificado'] = base64_encode(file_get_contents($_FILES['certificado']['tmp_name']));
        $meta['certificado_nombre'] = basename($_FILES['certificado']['name']);
    }

    $selectMeta = $db->prepare("SELECT IdEmpresaConfig FROM Gen_EmpresaConfig WHERE IdEmpresa=? AND MetaKey=? ORDER BY IdEmpresaConfig ASC LIMIT 1");
    $updateMeta = $db->prepare("UPDATE Gen_EmpresaConfig SET MetaValue=? WHERE IdEmpresaConfig=?");
    $insertMeta = $db->prepare("INSERT INTO Gen_EmpresaConfig (IdEmpresa, MetaKey, MetaValue) VALUES (?, ?, ?)");
    if (!$selectMeta || !$updateMeta || !$insertMeta) {
        throw new RuntimeException('No se pudo preparar la configuración de la empresa: ' . $db->error);
    }
    foreach ($meta as $key => $value) {
        $selectMeta->bind_param('is', $idEmpresa, $key);
        $selectMeta->execute();
        $existing = $selectMeta->get_result()->fetch_assoc();
        if ($existing) {
            $metaId = (int) $existing['IdEmpresaConfig'];
            $updateMeta->bind_param('si', $value, $metaId);
            $updateMeta->execute();
        } else {
            $insertMeta->bind_param('iss', $idEmpresa, $key, $value);
            $insertMeta->execute();
        }
    }
    $selectMeta->close(); $updateMeta->close(); $insertMeta->close();
    $db->commit();
    responder(true, 'Configuración guardada y sincronizada correctamente.');
} catch (Throwable $e) {
    $db->rollback();
    responder(false, $e->getMessage(), array(), 422);
}
