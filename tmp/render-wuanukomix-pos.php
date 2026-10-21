<?php
// Render only an offline fixture; no persistent authenticated session is created.
class PosFixtureSession implements SessionHandlerInterface {
 public function open($path,$name){return true;} public function close(){return true;}
 public function read($id){return '';} public function write($id,$data){return true;}
 public function destroy($id){return true;} public function gc($life){return 0;}
}
session_set_save_handler(new PosFixtureSession(),true);session_start(['use_cookies'=>0]);
$_SESSION=['user'=>'superadmin','idPerfil'=>1,'idAlmacen'=>1,'hostUrl'=>'http://localhost','permisos'=>['V_VentaForm.php'=>['Lectura'=>1,'Escritura'=>1]]];
$_SERVER['PHP_SELF']='/views/V_VentaForm.php';$_SERVER['SCRIPT_NAME']=$_SERVER['PHP_SELF'];$_SERVER['HTTP_HOST']='localhost';$_SERVER['DOCUMENT_ROOT']='C:/xampp/htdocs/boticav1';$_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'];
if(($argv[1]??'')==='imceden'){$_GET['pos']='imceden';$_SERVER['REQUEST_URI'].='?pos=imceden';}
chdir('C:/xampp/htdocs/boticav1/views');ob_start();require 'V_VentaForm.php';$html=ob_get_clean();session_abort();
$target=dirname(__DIR__).'/output/wuanukomix-pos-'.(($argv[1]??'')==='imceden'?'imceden':'ia').'-fixture.html';file_put_contents($target,$html);echo 'Rendered fixture '.basename($target).PHP_EOL;
