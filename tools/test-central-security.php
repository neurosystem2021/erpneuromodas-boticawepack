<?php
ob_start();session_id('centralsecurity'.bin2hex(random_bytes(6)));session_start();$_SESSION=array('user'=>'superadmin','idPerfil'=>1,'expire'=>time()+60);$sid=session_id();session_write_close();$failed=false;
foreach(array('EmpresaConfigGuardar','ComunicadosVigenciaGuardar','EtiquetasConfigGuardar','PlanesMenuGuardar') as $name){$ch=curl_init('http://localhost/controllers/'.$name.'.php');curl_setopt_array($ch,array(CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>'csrf=invalid',CURLOPT_COOKIE=>session_name().'='.$sid));$body=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);$ok=$status===403;echo ($ok?'PASS':'FAIL').' CSRF '.$name." HTTP ".$status."\n";if(!$ok){$failed=true;echo substr(strip_tags($body),0,300)."\n";}}
$ch=curl_init('http://localhost/api/index.php/correo/configuracion');curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);echo ($status===403?'PASS':'FAIL')." Anonymous mail configuration blocked\n";if($status!==403)$failed=true;
session_id($sid);session_start();$_SESSION=array();session_destroy();ob_end_flush();exit($failed?1:0);

