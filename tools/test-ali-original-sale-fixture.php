<?php
// Included by test-ali-pago-db.php inside its disposable database. Reuses the exact real handler.
function getNow($format='Y-m-d H:i:s'){return (new DateTimeImmutable('now',new DateTimeZone('America/Lima')))->format($format);}
function testAliOriginalSale($name){
 $db=new \Slim\PDO\Database('mysql:host=127.0.0.1;dbname='.$name.';charset=utf8','root','');
 foreach(['Ve_DocVenta','Ve_DocVentaDet','Ve_DocVentaMetodoPagoDet','Cb_CajaBanco'] as $table)$db->exec('CREATE TABLE `'.$table.'` LIKE nst.`'.$table.'`');
 $db->exec('CREATE TABLE Gen_EmpresaConfig (IdEmpresaConfig INT AUTO_INCREMENT PRIMARY KEY,IdEmpresa INT,MetaKey VARCHAR(100),MetaValue TEXT) ENGINE=InnoDB');
 $db->exec('CREATE TABLE Gen_Empresa (IdEmpresa INT PRIMARY KEY,valorComision DECIMAL(12,2)) ENGINE=InnoDB');$db->exec('INSERT INTO Gen_Empresa VALUES (1,2)');
 $db->exec('CREATE TABLE Ve_DocVentaDescuento (IdDocVentaDescuento INT PRIMARY KEY,PorCada DECIMAL(12,2),Puntos DECIMAL(12,2)) ENGINE=InnoDB');$db->exec('INSERT INTO Ve_DocVentaDescuento VALUES (1,1000,1)');
 $db->exec('ALTER TABLE Ve_DocVentaCliente ADD Puntos DECIMAL(12,2) DEFAULT 0');
 $source=file_get_contents('C:/xampp/htdocs/boticav1/api/index.php');$marker='$app->post(\'/ventas\', ';
 $start=strpos($source,$marker)+strlen($marker);$end=strpos($source,'$app->post(\'/proformas\'', $start);$closure=substr(rtrim(substr($source,$start,$end-$start)),0,-2);
 // Type aliases are scoped to eval's current file. Production source is read, never modified.
 $closure=str_replace(['Request $request','Response $response'],['\\Psr\\Http\\Message\\ServerRequestInterface $request','\\Psr\\Http\\Message\\ResponseInterface $response'],$closure);
 $handler=eval('return '.$closure.';');$container=new \Slim\Container();$container['db']=$db;$handler=$handler->bindTo($container);
 $product=['IdProducto'=>1,'Producto'=>'CAMARA FIXTURE','CodigoBarra'=>'1','PrecioCosto'=>90,'stock'=>16,'Anulado'=>0];
 $sale=['almacen'=>['IdAlmacen'=>1],'cliente'=>['IdCliente'=>1],'tipoVenta'=>['IdTipoDoc'=>7],'comisionista'=>['IdCliente'=>2617],'CampoDireccion'=>'','productos'=>[['IdProducto'=>1,'cantidad'=>2,'precio'=>120,'descuento'=>'','TieneIgv'=>0]],'pago'=>['efectivo'=>['monto'=>''],'yape'=>['monto'=>240]],'PagoCon'=>'','totalCart'=>240];
 $canonical=fn($s)=>aliCheckoutCanonical($db,$s,'test-cashier',['write'=>true],fn()=>[1=>$product]);
 $uuid='cccccccc-cccc-4ccc-8ccc-cccccccccccc';aliCheckoutPrepare($db,$uuid,'test-cashier',$canonical($sale));
 $request=\Slim\Http\Request::createFromEnvironment(\Slim\Http\Environment::mock(['REQUEST_METHOD'=>'POST','REQUEST_URI'=>'/ventas']));
 $_SESSION['User']='test-cashier';
 $save=function($payload)use($handler,$request){$r=$handler($request->withParsedBody($payload),new \Slim\Http\Response());$data=json_decode((string)$r->getBody(),true);pagoAssert($r->getStatusCode()===200&&!empty($data['insertId']),'original sale handler: '.json_encode($data));return $data;};
 $result=aliCheckoutConfirm($db,$uuid,'test-cashier',$canonical,$save);$repeat=aliCheckoutConfirm($db,$uuid,'test-cashier',$canonical,$save);
 pagoAssert($result['insertId']===$repeat['insertId'],'original service repeat id');
 foreach(['Ve_DocVenta','Ve_DocVentaDet','Ve_DocVentaMetodoPagoDet','Cb_CajaBanco'] as $table)pagoAssert((int)$db->query('SELECT COUNT(*) FROM `'.$table.'`')->fetchColumn()===1,'one actual legacy write: '.$table);
 $header=$db->query('SELECT IdComisionista,CampoDireccion FROM Ve_DocVenta')->fetch(PDO::FETCH_ASSOC);pagoAssert((int)$header['IdComisionista']===2617&&$header['CampoDireccion']==='','store commission and address persisted by original handler');
 $payment=$db->query('SELECT IdMetodoPago,Importe FROM Ve_DocVentaMetodoPagoDet')->fetch(PDO::FETCH_ASSOC);pagoAssert((int)$payment['IdMetodoPago']===4&&(float)$payment['Importe']===240.0,'Yape 240 persisted by original handler');
 echo 'PASS exact production POST /ventas callback against disposable schema: 2 cameras/S240/Yape/TIENDA -> one header, detail, payment and bank movement, repeat confirms same receipt. No real data or fiscal service changed.'.PHP_EOL;
}
