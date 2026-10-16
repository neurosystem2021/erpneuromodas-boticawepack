<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
function salida($ok, $mensaje, $status = 200) { http_response_code($status); echo json_encode(array('ok'=>$ok,'message'=>$mensaje)); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') salida(false, 'Método no permitido.', 405);
if (strtolower(trim((string)($_SESSION['user'] ?? ''))) !== 'superadmin') salida(false, 'Solo SUPERADMIN puede configurar etiquetas.', 403);
if (empty($_POST['csrf']) || empty($_SESSION['etiqueta_config_csrf']) || !hash_equals($_SESSION['etiqueta_config_csrf'], $_POST['csrf'])) salida(false, 'La sesión venció. Actualice la página.', 403);
require_once __DIR__ . '/../models/DBManager.php';
$db = getMysqliLink(); $db->begin_transaction();
try {
  $idEmpresa = max(1, (int)($_POST['id_empresa'] ?? 1));
  $ancho = (float)($_POST['ancho_mm'] ?? 49); $alto = (float)($_POST['alto_mm'] ?? 36); $columnas = (int)($_POST['columnas'] ?? 2);
  if ($ancho < 25 || $ancho > 120 || $alto < 15 || $alto > 100) throw new RuntimeException('Las medidas permitidas son 25–120 mm de ancho y 15–100 mm de alto.');
  if ($columnas < 1 || $columnas > 4) throw new RuntimeException('Seleccione entre 1 y 4 columnas.');
  $meta = array(
    'etiqueta_ancho_mm'=>(string)$ancho, 'etiqueta_alto_mm'=>(string)$alto, 'etiqueta_columnas'=>(string)$columnas,
    'etiqueta_mostrar_color'=>isset($_POST['mostrar_color'])?'1':'0',
    'etiqueta_mostrar_genero'=>isset($_POST['mostrar_genero'])?'1':'0',
    'etiqueta_mostrar_botapie'=>isset($_POST['mostrar_botapie'])?'1':'0',
    'etiqueta_mostrar_precio'=>isset($_POST['mostrar_precio'])?'1':'0',
    'etiqueta_mostrar_talla'=>isset($_POST['mostrar_talla'])?'1':'0'
  );
  if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK || $_FILES['logo']['size'] > 3145728) throw new RuntimeException('El logo no es válido o supera 3 MB.');
    $allowed = array('image/png'=>'png','image/jpeg'=>'jpg','image/webp'=>'webp');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['logo']['tmp_name']);
    if (!isset($allowed[$mime]) || @getimagesize($_FILES['logo']['tmp_name']) === false) throw new RuntimeException('Use un logo PNG, JPG o WEBP.');
    $dir = __DIR__ . '/../resources/uploads/etiquetas'; if (!is_dir($dir) && !mkdir($dir,0755,true)) throw new RuntimeException('No se pudo crear la carpeta del logo.');
    $nombre = 'logo-etiqueta-'.date('YmdHis').'-'.bin2hex(random_bytes(3)).'.'.$allowed[$mime];
    if (!move_uploaded_file($_FILES['logo']['tmp_name'],$dir.'/'.$nombre)) throw new RuntimeException('No se pudo guardar el logo.');
    $meta['etiqueta_logo']='resources/uploads/etiquetas/'.$nombre;
  }
  $sel=$db->prepare('SELECT IdEmpresaConfig FROM Gen_EmpresaConfig WHERE IdEmpresa=? AND MetaKey=? ORDER BY IdEmpresaConfig LIMIT 1');
  $upd=$db->prepare('UPDATE Gen_EmpresaConfig SET MetaValue=? WHERE IdEmpresaConfig=?');
  $ins=$db->prepare('INSERT INTO Gen_EmpresaConfig (IdEmpresa,MetaKey,MetaValue) VALUES (?,?,?)');
  foreach($meta as $key=>$value){$sel->bind_param('is',$idEmpresa,$key);$sel->execute();$row=$sel->get_result()->fetch_assoc();if($row){$mid=(int)$row['IdEmpresaConfig'];$upd->bind_param('si',$value,$mid);$upd->execute();}else{$ins->bind_param('iss',$idEmpresa,$key,$value);$ins->execute();}}
  $sel->close();$upd->close();$ins->close();$db->commit(); salida(true,'Configuración de etiquetas guardada.');
} catch(Throwable $e){$db->rollback();salida(false,$e->getMessage(),422);}
