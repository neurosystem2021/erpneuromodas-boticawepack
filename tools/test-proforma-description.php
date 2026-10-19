<?php
require 'C:/xampp/htdocs/boticav1/api/vendor/autoload.php';require 'C:/xampp/htdocs/boticav1/config/database.php';
set_error_handler(function($level,$message,$file,$line){if($level===E_WARNING&&basename($file)==='StatementContainer.php'&&strpos($message,'sizeof()')===0)return true;throw new ErrorException($message,0,$level,$file,$line);});
class Request {private $p;function __construct($p){$this->p=$p;}function getParam($k){return $this->p[$k]??null;}}
class Response {public $data;function withJson($data){$this->data=$data;return $this;}}
class ProformaRoutes {public $routes=[];function post($p,$fn){$this->routes['post'.$p]=$fn;}function get($p,$fn){$this->routes['get'.$p]=$fn;}}
class ProformaContext {public $db;function __construct($db){$this->db=$db;}}
function getNow($format='Y-m-d H:i:s'){return date($format,strtotime('2026-10-18 12:00:00'));}
function descriptionCheck($ok,$message){if(!$ok)throw new RuntimeException($message);}
$file=$argv[1]??__DIR__.'/../tmp/proforma-description/api-index.php';$code=file_get_contents($file);$app=new ProformaRoutes();
foreach([['post','/proformas'],['get','/proformas/id/{id}'],['get','/proformas/productos/{id}'],['get','/proformas/detalle']] as [$method,$path]){preg_match('~\$app->'.$method.'\(\''.preg_quote($path,'~').'\',.*?\n\}\);~s',$code,$m);descriptionCheck(isset($m[0]),'Missing route '.$path);eval($m[0]);}
$cfg=appDatabaseConfig();$db=new \Slim\PDO\Database(appDatabaseDsn($cfg),$cfg['user'],$cfg['pass']);$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
$db->exec('CREATE TEMPORARY TABLE Ve_Proforma (IdProforma INTEGER PRIMARY KEY AUTO_INCREMENT,Anio INTEGER,Numero INTEGER,IdCliente INTEGER,FechaReg DATETIME,UsuarioReg VARCHAR(45),DiasValidos INTEGER,DescripcionProforma TEXT,DescripcionProforma2 TEXT,DescripcionProforma3 TEXT,DescripcionProforma4 TEXT,DescripcionProforma5 TEXT,EsDescripcion INTEGER,EsCreditoProforma INTEGER,FechaCreditoProforma DATETIME);
CREATE TEMPORARY TABLE Ve_ProformaDet (IdProformaDet INTEGER PRIMARY KEY AUTO_INCREMENT,IdProforma INTEGER,IdProducto INTEGER,Cantidad DOUBLE,Precio DOUBLE,Descuento DOUBLE,Descripcion TEXT);
CREATE TEMPORARY TABLE Ve_DocVentaCliente (IdCliente INTEGER,Cliente TEXT,DniRuc TEXT,Direccion TEXT,Email TEXT);
CREATE TEMPORARY TABLE Gen_Producto (IdProducto INTEGER,Producto TEXT,Descripcion TEXT,Color TEXT,Genero TEXT,Botapie TEXT,ProductoModelo TEXT,IdProductoMarca INTEGER,IdProductoTalla INTEGER,IdProductoMedicion INTEGER);
CREATE TEMPORARY TABLE Gen_ProductoMarca (IdProductoMarca INTEGER,ProductoMarca TEXT);
CREATE TEMPORARY TABLE Gen_ProductoTalla (IdProductoTalla INTEGER,ProductoTalla TEXT);
CREATE TEMPORARY TABLE Gen_ProductoMedicion (IdProductoMedicion INTEGER,Codigo TEXT);
INSERT INTO Ve_Proforma (IdProforma,Anio,Numero) VALUES (99,2026,99);
INSERT INTO Ve_DocVentaCliente VALUES (1,"CLIENTE DE PRUEBA","00000000",""," ");
INSERT INTO Gen_Producto VALUES (1,"PRODUCTO DE PRUEBA","Texto de catálogo que no debe reemplazar la nota","","","","MODELO",1,1,1);
INSERT INTO Gen_ProductoMarca VALUES (1,"MARCA");INSERT INTO Gen_ProductoTalla VALUES (1,"");INSERT INTO Gen_ProductoMedicion VALUES (1,"NIU");');
$context=new ProformaContext($db);$call=function($method,$path,$params=[],$args=[])use($app,$context){return $app->routes[$method.$path]->call($context,new Request($params),new Response(),$args)->data;};
$texts=["Para el cliente: válvula de 1/2\"\nCon adaptador del cliente y prueba d'agua <b>sin HTML</b>",'Otra descripción para el mismo producto'];
$body=['cliente'=>['IdCliente'=>1],'vendedor'=>['Usuario'=>'vendedor-prueba'],'diasValidosProf'=>7,'EsDescripcion'=>0,'EsCreditoProforma'=>0,'productos'=>[['IdProducto'=>1,'cantidad'=>2,'precio'=>12,'descuento'=>1,'Descripcion'=>$texts[0]],['IdProducto'=>1,'cantidad'=>1,'precio'=>18,'descuento'=>0,'Descripcion'=>$texts[1]],['IdProducto'=>1,'cantidad'=>1,'precio'=>5,'descuento'=>0]]];
$saved=$call('post','/proformas',$body);$id=$saved['insertId'];descriptionCheck((int)$id>99,'New fixture quote created');$detail=$call('get','/proformas/detalle',['idProforma'=>$id]);descriptionCheck(count($detail)===3,'Three independent items');descriptionCheck(array_column($detail,'Descripcion')===[$texts[0],$texts[1],''],'Saved descriptions preserve accents, quotes, line breaks and empty notes');descriptionCheck((float)$detail[0]['Cantidad']===2.0&&(float)$detail[0]['Precio']===12.0&&(float)$detail[0]['Descuento']===1.0,'Quantities, prices and discounts unchanged');
$quote=$call('get','/proformas/id/{id}',[],['id'=>$id]);descriptionCheck(array_column($quote['productos'],'Descripcion')===[$texts[0],$texts[1],''],'Report gets per-item notes after catalog columns');$products=$call('get','/proformas/productos/{id}',[],['id'=>$id]);descriptionCheck(array_column($products,'Descripcion')===[$texts[0],$texts[1],''],'Product endpoint retains notes');
$db->exec('UPDATE Ve_ProformaDet SET Descripcion=NULL WHERE IdProformaDet=3');$quote=$call('get','/proformas/id/{id}',[],['id'=>$id]);descriptionCheck($quote['productos'][2]['Descripcion']==='','Legacy null description returns empty text');
$pdfFile=$argv[2]??__DIR__.'/../tmp/proforma-description/proforma-pdf.php';$pdf=file_get_contents($pdfFile);$start=strpos($pdf,"\$productosRows = '';");$end=strpos($pdf,'// ─── HTML',$start);descriptionCheck($start!==false&&$end!==false,'PDF product block exists');$productos=$quote['productos'];$moneda='S/';eval(substr($pdf,$start,$end-$start));descriptionCheck(strpos($productosRows,htmlspecialchars($texts[0],ENT_QUOTES,'UTF-8'))!==false,'PDF includes escaped multiline note');descriptionCheck(strpos($productosRows,'<b>sin HTML</b>')===false,'Description is not interpreted as HTML');
file_put_contents(__DIR__.'/../output/proforma-description-fixture.json',json_encode(['quote'=>$quote,'list'=>['IdProforma'=>$id,'IdCliente'=>1,'DniRuc'=>'00000000','Cliente'=>'CLIENTE DE PRUEBA','UsuarioReg'=>'vendedor-prueba','productos'=>$detail]],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
echo "PASS actual proforma save/detail/report routes and PDF: multiline descriptions persist per item, repeated products keep independent notes, null/empty notes handled, catalog text cannot override, prices and discounts unchanged. MySQL temporary tables only; no real proforma or invoice created.\n";
