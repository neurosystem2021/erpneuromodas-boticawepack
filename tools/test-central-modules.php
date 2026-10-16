<?php
ob_start();
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
session_id('centralcheck'.bin2hex(random_bytes(8)));session_start();$_SESSION=array('user'=>'superadmin','idPerfil'=>1,'idAlmacen'=>1,'HostUrl'=>'http://localhost/','hostUrl'=>'http://localhost/','IdDocVentaPuntoVenta'=>1,'expire'=>time()+300,'permisos'=>array());$sid=session_id();session_write_close();
$paths=array('/views/EmpresaConfig.php'=>'empresaForm','/views/Seg_ComunicadosVigencia.php'=>'vigencia','/views/Seg_ConfigurarEtiquetas.php'=>'tagForm','/views/Seg_CorreoConfig.php'=>'smtpHost','/views/Seg_PlanesMenu.php'=>'pm-grid','/api/index.php/correo/configuracion'=>'smtp','/controllers/EmpresaConfigPublica.php'=>'razon_social','/views/codigoBarra.php?idProducto=1&cantidad=1'=>'ETIQUETA_CONFIG','/views/ve_cierreform.php'=>'/nuevo/index.html');
$failed=false;foreach($paths as $path=>$marker){$ch=curl_init('http://localhost'.$path);curl_setopt_array($ch,array(CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIE=>session_name().'='.$sid,CURLOPT_TIMEOUT=>20));$body=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);$ok=$status===200&&stripos($body,'Fatal error')===false&&stripos($body,'Warning:')===false;
if(strpos($path,'/api/')===0)$ok=$ok&&is_array(json_decode($body,true));else $ok=$ok&&strpos($body,$marker)!==false;
if(!$ok)$failed=true;echo ($ok?'PASS':'FAIL').' '.$path.' HTTP '.$status."\n";
if(strpos($path,'/views/')===0&&strpos($path,'ve_cierre')===false){$dir=__DIR__.'/../.tmp-central-render';if(!is_dir($dir))mkdir($dir);file_put_contents($dir.'/'.basename(parse_url($path,PHP_URL_PATH)).'.html',$body);}}
session_id($sid);session_start();$_SESSION=array();session_destroy();ob_end_flush();exit($failed?1:0);
