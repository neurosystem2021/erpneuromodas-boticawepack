<?php
ob_start();$dir=__DIR__.'/../.tmp-test-sessions';if(!is_dir($dir))mkdir($dir);session_save_path($dir);session_id('projectcreator'.bin2hex(random_bytes(6)));
$source=file_get_contents('C:/xampp/htdocs/boticav1/api/index.php');$start=strpos($source,"\$app->post('/producto/registro_proyecto'");$start=strpos($source,'if (session_status()', $start);$end=strpos($source,'$sql =', $start);$code=substr($source,$start,$end-$start);
class ProjectCreatorResponse{function withJson($data,$status=200){return $status;}}
function captureCreator($code){$response=new ProjectCreatorResponse;return eval($code.' return $usuarioReg;');}
foreach(['superadmin','operador-prueba'] as $user){session_start();$_SESSION['user']=$user;$_SESSION['User']='wrong-uppercase-user';session_write_close();if(captureCreator($code)!==$user)throw new Exception('Wrong creator: '.$user);}
session_start();unset($_SESSION['user']);session_write_close();if(captureCreator($code)!==401)throw new Exception('Missing session accepted');
if(strpos($source,"\$descripcion, \$usuarioReg")===false)throw new Exception('Creator not included in insert');session_start();session_destroy();ob_end_clean();echo "Project creator: superadmin and another session user captured correctly; missing session rejected. No real projects created.\n";
