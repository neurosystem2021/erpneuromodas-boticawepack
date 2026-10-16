<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';
require __DIR__.'/../integration/ali-checkout.php';
function pagoAssert($ok,$name){if(!$ok)throw new Exception($name);}
function pagoReject($fn,$name){try{$fn();}catch(AliCheckoutError $e){return;}throw new Exception('Expected rejection: '.$name);}
function pagoFixtures(){return ['products'=>[1=>['IdProducto'=>1,'Producto'=>'CAMARA PRUEBA','CodigoBarra'=>'TEST1','PrecioCosto'=>5,'stock'=>50,'Anulado'=>0]],'sale'=>['almacen'=>['IdAlmacen'=>1],'cliente'=>['IdCliente'=>1],'tipoVenta'=>['IdTipoDoc'=>7],'productos'=>[['IdProducto'=>1,'cantidad'=>2,'precio'=>10,'descuento'=>0]],'pago'=>['yape'=>['monto'=>20,'descripcion'=>'OP-TEST']],'totalCart'=>20]];}
function pagoSaver($db,$payload){$db->exec("INSERT INTO TestVenta (Total) VALUES (20)");$id=$db->lastInsertId();$db->exec("INSERT INTO TestPago (IdVenta,Importe) VALUES ($id,20)");return ['insertId'=>$id];}
$db=appPdoConnection();$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
if(($argv[1]??'')==='worker'){
 $name=$argv[2];if(!preg_match('/^ali_pago_test_[a-f0-9]{10}$/D',$name))throw new Exception('Unsafe fixture DB');$db->exec('USE `'.$name.'`');$f=pagoFixtures();
 $result=aliCheckoutConfirm($db,$argv[3],'test-cashier',fn($sale)=>aliCheckoutCanonical($db,$sale,'test-cashier',['write'=>true],fn()=> $f['products']),function($p)use($db){usleep(600000);return pagoSaver($db,$p);});echo json_encode($result);exit;
}
$name='ali_pago_test_'.bin2hex(random_bytes(5));$db->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4');
try{
 $db->exec('USE `'.$name.'`');$db->exec(file_get_contents(__DIR__.'/../integration/ali-checkout.sql'));
 foreach([
  'CREATE TABLE Seg_Usuario (Usuario VARCHAR(100) PRIMARY KEY,IdAlmacen INT,IdDocVentaPuntoVenta INT,Anulado INT DEFAULT 0) ENGINE=InnoDB',
  'CREATE TABLE Ve_DocVentaCliente (IdCliente INT PRIMARY KEY,Cliente VARCHAR(100),DniRuc VARCHAR(11),Anulado INT DEFAULT 0,Direccion VARCHAR(200) DEFAULT \'\',Direccion2 VARCHAR(200) DEFAULT \'\',Direccion3 VARCHAR(200) DEFAULT \'\',esComisionista INT DEFAULT 0) ENGINE=InnoDB',
  'CREATE TABLE Ve_DocVentaTipoDoc (IdTipoDoc INT PRIMARY KEY,CodSunat VARCHAR(2),TipoDoc VARCHAR(100)) ENGINE=InnoDB',
  'CREATE TABLE Ve_DocVentaPuntoVentaDet (IdDocVentaPuntoVenta INT,IdDocVentaTipoDoc INT,Serie VARCHAR(15)) ENGINE=InnoDB',
  'CREATE TABLE Gen_Producto (IdProducto INT PRIMARY KEY) ENGINE=InnoDB',
  'CREATE TABLE Gen_Producto_Serie (IdProductoSerie INT PRIMARY KEY,IdProducto INT,Estado INT,IdDocVenta INT,IdMovimiento INT) ENGINE=InnoDB',
  'CREATE TABLE Lo_Movimiento (IdMovimiento INT PRIMARY KEY,IdAlmacen INT) ENGINE=InnoDB',
  'CREATE TABLE Ve_DocVentaMetodoPago (IdMetodoPago INT PRIMARY KEY) ENGINE=InnoDB',
  'CREATE TABLE TestVenta (Id INT AUTO_INCREMENT PRIMARY KEY,Total DECIMAL(12,2)) ENGINE=InnoDB',
  'CREATE TABLE TestPago (Id INT AUTO_INCREMENT PRIMARY KEY,IdVenta INT,Importe DECIMAL(12,2)) ENGINE=InnoDB',
  "INSERT INTO Seg_Usuario VALUES ('test-cashier',1,1,0)","INSERT INTO Ve_DocVentaCliente (IdCliente,Cliente,DniRuc,Anulado) VALUES (1,'CLIENTE FIXTURE','00000000',0)","INSERT INTO Ve_DocVentaTipoDoc VALUES (7,'03','BOLETA ELEC.')","INSERT INTO Ve_DocVentaPuntoVentaDet VALUES (1,7,'BTST')",'INSERT INTO Gen_Producto VALUES (1)','INSERT INTO Ve_DocVentaMetodoPago VALUES (1),(2),(3),(4)'
 ] as $sql)$db->exec($sql);
 $f=pagoFixtures();$canonical=fn($sale)=>aliCheckoutCanonical($db,$sale,'test-cashier',['write'=>true],fn()=> $f['products']);$data=$canonical($f['sale']);
 pagoAssert($data['payload']['pago']['yape']['IdMetodoPago']===4&&$data['summary']['total']===20.0,'real payment mapping and total');
 $db->exec("INSERT INTO Ve_DocVentaCliente (IdCliente,Cliente,DniRuc,esComisionista) VALUES (2617,'TIENDA','0000',1)");
 $db->exec('INSERT INTO Gen_Producto_Serie VALUES (10,1,1,NULL,NULL)');
 $defaults=$f['sale'];$defaults['comisionista']=['IdCliente'=>2617];$defaults['CampoDireccion']='';$defaults['productos'][0]['descuento']='';$defaults['pago']['efectivo']=['monto'=>''];$defaults['PagoCon']='';
 $normal=$canonical($defaults);pagoAssert($normal['payload']['comisionista']['IdCliente']===2617&&$normal['payload']['CampoDireccion']===''&&$normal['payload']['productos'][0]['descuento']===0.0,'POS defaults preserved, optional blank amounts zero, optional serial may remain unselected');
 $invalid=$defaults;$invalid['productos'][0]['IdProductoSerie']=10;pagoReject(fn()=>$canonical($invalid),'one selected serial cannot represent two units');
 $invalid=$defaults;$invalid['CampoDireccion']='Unregistered address';pagoReject(fn()=>$canonical($invalid),'unregistered address');
 $invalid=$defaults;$invalid['comisionista']['IdCliente']=1;pagoReject(fn()=>$canonical($invalid),'unavailable commission');
 $db->exec("UPDATE Ve_DocVentaCliente SET Direccion='Av. O''Brien' WHERE IdCliente=1");$address=$defaults;$address['CampoDireccion']="Av. O'Brien";$normal=$canonical($address);pagoAssert($normal['payload']['CampoDireccion']==="Av. O'Brien",'registered address punctuation preserved');$db->exec("UPDATE Ve_DocVentaCliente SET Direccion='' WHERE IdCliente=1");
 pagoReject(fn()=>aliCheckoutCanonical($db,$f['sale'],'test-cashier',['write'=>false],fn()=>[]),'permission');
 $bad=$f['sale'];$bad['almacen']['IdAlmacen']=2;pagoReject(fn()=>$canonical($bad),'warehouse');
 $bad=$f['sale'];$bad['totalCart']=19;pagoReject(fn()=>$canonical($bad),'total mismatch');
 $bad=$f['sale'];$bad['pago']['yape']['descripcion']="bad' SQL";pagoReject(fn()=>$canonical($bad),'SQL unsafe reference');
 $bad=$f['sale'];$bad['EsCredito']=1;pagoReject(fn()=>$canonical($bad),'unsupported credit');
 pagoReject(fn()=>aliCheckoutCanonical($db,$f['sale'],'test-cashier',['write'=>true],fn()=>[1=>array_merge($f['products'][1],['stock'=>1])]),'stock');
 $id='aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';$prepared=aliCheckoutPrepare($db,$id,'test-cashier',$data);pagoAssert($prepared['state']==='preparada','prepare');
 $changed=$data;$changed['payload']['totalCart']=30;pagoReject(fn()=>aliCheckoutPrepare($db,$id,'test-cashier',$changed),'key payload conflict');
 pagoReject(fn()=>aliCheckoutConfirm($db,$id,'wrong-user',$canonical,fn()=>[]),'owner');
 pagoReject(fn()=>aliCheckoutConfirm($db,$id,'test-cashier',fn()=>$changed,fn()=>[]),'changed snapshot');
 // Partial legacy writes must roll back together with the operation.
 pagoReject(fn()=>aliCheckoutConfirm($db,$id,'test-cashier',$canonical,function()use($db){$db->exec('INSERT INTO TestVenta (Total) VALUES (20)');return [];}),'partial handler failure');
 pagoAssert((int)$db->query('SELECT COUNT(*) FROM TestVenta')->fetchColumn()===0,'partial write rollback');
 // Independent PHP processes confirm the same operation concurrently.
 $children=[];for($i=0;$i<2;$i++){$pipes=[];$process=proc_open([PHP_BINARY,__FILE__,'worker',$name,$id],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);fclose($pipes[0]);$children[]=[$process,$pipes];}
 $results=[];foreach($children as [$process,$pipes]){$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);pagoAssert(proc_close($process)===0,'worker exit '.$err);$results[]=json_decode($out,true);}
 pagoAssert($results[0]['insertId']===$results[1]['insertId'],'same sale response');
 pagoAssert((int)$db->query('SELECT COUNT(*) FROM TestVenta')->fetchColumn()===1&&(int)$db->query('SELECT COUNT(*) FROM TestPago')->fetchColumn()===1,'one sale and payment across concurrent confirmations');
 $replay=aliCheckoutConfirm($db,$id,'test-cashier',function(){throw new Exception('Must not revalidate a completed operation');},function(){throw new Exception('Must not resave');});pagoAssert($replay['replayed']===true,'recovery after lost response');
 // A repeated preparation after completion recovers the receipt even if stock changed.
 require 'C:/xampp/htdocs/boticav1/api/vendor/autoload.php';$app=new \Slim\App(['settings'=>['displayErrorDetails'=>false]]);$app->getContainer()['db']=$db;
 $app->post('/ventas',function(){throw new Exception('Preparation must not save');});$app->get('/productos/stock[/{idAlmacen}]',function(){throw new Exception('Completed receipt must not read stock');});
 aliRegisterCheckout($app,function($q,$r,$next){return $next($q,$r);});$callback=aliCheckoutLegacy($app,'/ali/pago/preparar','POST');
 $request=\Slim\Http\Request::createFromEnvironment(\Slim\Http\Environment::mock(['REQUEST_METHOD'=>'POST','REQUEST_URI'=>'/ali/pago/preparar']))->withParsedBody(['operation'=>$id,'sale'=>[]])->withAttribute('ali_user','test-cashier')->withAttribute('ali_pos',['write'=>true]);
 $response=$callback($request,new \Slim\Http\Response());$receipt=json_decode((string)$response->getBody(),true);pagoAssert($response->getStatusCode()===200&&$receipt['state']==='completada','completed preparation recovers without stock or save');
 $id2='bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';aliCheckoutPrepare($db,$id2,'test-cashier',$data);$db->exec("UPDATE Ali_PosOperacion SET Expira=DATE_SUB(NOW(),INTERVAL 1 MINUTE) WHERE Operacion='$id2'");pagoReject(fn()=>aliCheckoutConfirm($db,$id2,'test-cashier',$canonical,fn()=>[]),'expired summary');
 require __DIR__.'/test-ali-original-sale-fixture.php';testAliOriginalSale($name);
 echo 'PASS isolated MySQL fixtures: canonical totals, permissions, warehouse, payment reference, stock, payload conflict, owner, changed cart, rollback, two concurrent confirmations -> one sale/one payment, cached recovery and expiry. No real ERP sale or fiscal calls.'.PHP_EOL;
}finally{if(!preg_match('/^ali_pago_test_[a-f0-9]{10}$/D',$name))throw new Exception('Unsafe cleanup');$db->exec('USE nst');$db->exec('DROP DATABASE `'.$name.'`');}
