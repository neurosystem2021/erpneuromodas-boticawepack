<?php
header('X-Content-Type-Options: nosniff');header('Cache-Control: no-store, private');
require_once dirname(__DIR__).'/config/database.php';
function attachmentFail($status,$message){http_response_code($status);header('Content-Type: application/json; charset=utf-8');echo json_encode(['error'=>$message],JSON_UNESCAPED_UNICODE);exit;}
function attachmentRoot(){return getenv('ERP_PROFORMA_FILES_ROOT') ?: (PHP_OS_FAMILY==='Windows'?'C:/xampp/accounting-private/proforma-attachments':dirname(__DIR__,2).'/private/proforma-attachments');}
try{
 $db=appPdoConnection();$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
 if(isset($_GET['token'])){
  $token=(string)$_GET['token'];if(!preg_match('/^[a-f0-9]{64}$/D',$token))attachmentFail(404,'Documento no disponible.');
  $s=$db->prepare('SELECT Nombre,Archivo FROM Ve_ProformaAdjunto WHERE TokenHash=?');$s->execute([hash('sha256',$token)]);$row=$s->fetch(PDO::FETCH_ASSOC);
  if(!$row||!preg_match('/^[a-f0-9]{64}\.pdf$/D',$row['Archivo']))attachmentFail(404,'Documento no disponible.');$file=attachmentRoot().'/'.$row['Archivo'];if(!is_file($file))attachmentFail(404,'Documento no disponible.');
  header('Content-Type: application/pdf');header('Content-Disposition: attachment; filename="documento-adjunto.pdf"; filename*=UTF-8\'\''.rawurlencode($row['Nombre']));header('Content-Length: '.filesize($file));header('Referrer-Policy: no-referrer');readfile($file);exit;
 }
 session_start();if(empty($_SESSION['user'])||(isset($_SESSION['expire'])&&$_SESSION['expire']<time()))attachmentFail(401,'Inicia sesión nuevamente.');
 $s=$db->prepare('SELECT IdUsuarioPerfil FROM Seg_Usuario WHERE Usuario=? AND COALESCE(Anulado,0)=0');$s->execute([$_SESSION['user']]);if(!$s->fetchColumn())attachmentFail(403,'Usuario no autorizado.');
 $id=filter_var($_GET['id']??$_POST['IdProforma']??0,FILTER_VALIDATE_INT);if(!$id||$id<1)attachmentFail(422,'Selecciona una proforma válida.');$s=$db->prepare('SELECT IdProforma FROM Ve_Proforma WHERE IdProforma=? AND COALESCE(Anulado,0)=0');$s->execute([$id]);if(!$s->fetchColumn())attachmentFail(404,'Proforma no disponible.');
 if($_SERVER['REQUEST_METHOD']==='GET'){$s=$db->prepare('SELECT Nombre,Token,Bytes FROM Ve_ProformaAdjunto WHERE IdProforma=?');$s->execute([$id]);$row=$s->fetch(PDO::FETCH_ASSOC);header('Content-Type: application/json; charset=utf-8');echo json_encode(['attachment'=>$row?['name'=>$row['Nombre'],'bytes'=>(int)$row['Bytes'],'url'=>'/api/proforma-attachment.php?token='.$row['Token']]:null],JSON_UNESCAPED_UNICODE);exit;}
 if($_SERVER['REQUEST_METHOD']!=='POST')attachmentFail(405,'Método no permitido.');
 if(empty($_SESSION['proforma_csrf'])||!hash_equals($_SESSION['proforma_csrf'],$_SERVER['HTTP_X_PROFORMA_CSRF']??''))attachmentFail(403,'Actualiza la página e intenta nuevamente.');session_write_close();
 $upload=$_FILES['pdf']??null;if(!$upload||$upload['error']!==UPLOAD_ERR_OK)attachmentFail(422,'No se pudo recibir el PDF. Revisa su tamaño y vuelve a intentarlo.');
 if(!is_uploaded_file($upload['tmp_name'])||$upload['size']<8||$upload['size']>8*1024*1024)attachmentFail(422,'El PDF debe pesar como máximo 8 MB.');
 $name=basename(str_replace('\\','/',(string)$upload['name']));if(mb_strlen($name)>180||strtolower(pathinfo($name,PATHINFO_EXTENSION))!=='pdf'||preg_match('/[\x00-\x1f\x7f]/',$name))attachmentFail(422,'Selecciona un archivo PDF válido.');
 $f=fopen($upload['tmp_name'],'rb');$magic=fread($f,5);fclose($f);$mime=(new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);if($magic!=='%PDF-'||$mime!=='application/pdf')attachmentFail(422,'El archivo no corresponde a un PDF.');
 $root=attachmentRoot();if(!is_dir($root)&&!mkdir($root,0700,true))throw new RuntimeException('Cannot create private attachment directory');$token=bin2hex(random_bytes(32));$filename=bin2hex(random_bytes(32)).'.pdf';$file=$root.'/'.$filename;
 if(!move_uploaded_file($upload['tmp_name'],$file))throw new RuntimeException('Cannot save attachment');@chmod($file,0600);
 try{$s=$db->prepare('INSERT INTO Ve_ProformaAdjunto(IdProforma,Nombre,Archivo,Token,TokenHash,Bytes,Usuario,Fecha) VALUES(?,?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE Nombre=VALUES(Nombre),Archivo=VALUES(Archivo),Token=VALUES(Token),TokenHash=VALUES(TokenHash),Bytes=VALUES(Bytes),Usuario=VALUES(Usuario),Fecha=VALUES(Fecha)');$s->execute([$id,$name,$filename,$token,hash('sha256',$token),$upload['size'],$_SESSION['user']]);}catch(Throwable $e){@unlink($file);throw $e;}
 header('Content-Type: application/json; charset=utf-8');echo json_encode(['attachment'=>['name'=>$name,'bytes'=>(int)$upload['size'],'url'=>'/api/proforma-attachment.php?token='.$token]],JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){error_log('Quote attachment: '.$e->getMessage());attachmentFail(500,'No se pudo procesar el PDF. Vuelve a intentarlo.');}
