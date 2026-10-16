<?php
header('Content-Type: application/json; charset=utf-8');
if(session_status()!==PHP_SESSION_ACTIVE)session_start();
if(empty($_SESSION['user'])||(isset($_SESSION['expire'])&&(int)$_SESSION['expire']<time())){http_response_code(401);echo json_encode(['error'=>'Inicia sesion.']);exit;}
require_once dirname(__DIR__).'/config/database.php';
require_once dirname(__DIR__).'/api/ali-pos-security.php';
$cap=aliPosCapabilities(appPdoConnection(),$_SESSION['user']);
if(empty($cap['admin'])){http_response_code(403);echo json_encode(['error'=>'Solo administradores.']);exit;}
session_write_close();
if(PHP_OS_FAMILY!=='Windows'){echo json_encode(['windows'=>false,'printers'=>[]]);exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(($_SERVER['HTTP_X_REQUESTED_WITH']??'')!=='XMLHttpRequest'){http_response_code(403);exit;}
 $body=json_decode(file_get_contents('php://input'),true);$printer=trim((string)($body['printer']??''));
 if(($body['action']??'')!=='test'||$printer===''||strlen($printer)>100||preg_match('/[\x00-\x1f\x7f]/',$printer)){http_response_code(422);exit;}
 $temp=tempnam(sys_get_temp_dir(),'printer_test_');
 if(!$temp){http_response_code(500);exit;}
 try{
  $bytes="\x1b@PRUEBA DE IMPRESION - NEUROSOF\nIMPRESORA: ".$printer."\nSIN VALOR TRIBUTARIO\n\n\n\x1dV\x00";
  file_put_contents($temp,$bytes);$lines=[];$code=1;
  exec('powershell.exe -NoProfile -NonInteractive -ExecutionPolicy Bypass -File '.escapeshellarg(__DIR__.'/usb-print.ps1').' -PrinterName '.escapeshellarg($printer).' -FilePath '.escapeshellarg($temp).' 2>&1',$lines,$code);
  if($code!==0){http_response_code(503);echo json_encode(['ok'=>false,'error'=>'La cola no acepto la prueba.']);}
  else echo json_encode(['ok'=>true,'message'=>'Prueba enviada a '.$printer]);
 }finally{@unlink($temp);}
 exit;
}
if($_SERVER['REQUEST_METHOD']!=='GET'){http_response_code(405);exit;}
$command='powershell.exe -NoProfile -NonInteractive -Command "Get-Printer | Select-Object @{Name=\'name\';Expression={$_.Name}},@{Name=\'driver\';Expression={$_.DriverName}},@{Name=\'port\';Expression={$_.PortName}},@{Name=\'status\';Expression={[string]$_.PrinterStatus}} | ConvertTo-Json -Compress"';
$lines=[];$code=1;exec($command.' 2>NUL',$lines,$code);$data=json_decode(implode("\n",$lines),true);
if($code!==0||!is_array($data)){http_response_code(503);echo json_encode(['error'=>'No se pudo consultar la cola de Windows.']);exit;}
if(isset($data['name']))$data=[$data];
echo json_encode(['windows'=>true,'printers'=>$data],JSON_UNESCAPED_UNICODE);
