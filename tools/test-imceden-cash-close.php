<?php
// Execute the actual route closures against isolated in-memory fixtures.
class Request {private $p;function __construct($p){$this->p=$p;}function getParam($k){return $this->p[$k]??null;}}
class Response {public $data,$status;function withJson($data,$status=200){$this->data=$data;$this->status=$status;return $this;}}
class ClosingRoutes {public $routes=[];function post($p,$fn){$this->routes['post'.$p]=$fn;}function get($p,$fn){$this->routes['get'.$p]=$fn;}}
class ClosingContext {public $db;function __construct($db){$this->db=$db;}}
class ClosingDatabase {public $db,$fail=false;function __construct($db){$this->db=$db;}function __call($name,$args){return $this->db->$name(...$args);}function prepare($sql){if($this->fail&&strpos($sql,'UPDATE Cb_CajaBanco')!==false)throw new RuntimeException('simulated movement write failure');return $this->db->prepare($sql);}}
function getNow(){return '2026-10-18 09:00:00';}
function obtenerIdCuentaCajaPrincipal($db){return 1;}
function checkClosing($ok,$label){if(!$ok)throw new RuntimeException($label);}
$mysql=in_array('--mysql-temporary',$argv,true);$file=isset($argv[1])&&$argv[1]!=='--mysql-temporary'?$argv[1]:'C:/xampp/htdocs/boticav1/api/index.php';$source=file_get_contents($file);$app=new ClosingRoutes();
foreach([['post','/cierrecaja'],['get','/cierrecaja/ingresos'],['get','/cierrecaja/salidas']] as [$method,$path]){preg_match('~\$app->'.$method.'\(\''.preg_quote($path,'~').'\',.*?\n\}\);~s',$source,$m);checkClosing(isset($m[0]),'Route present '.$path);eval($m[0]);}
if($mysql){require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();}else{$db=new PDO('sqlite::memory:');}$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
$schema='CREATE TABLE Cb_CierreCaja (IdCierreCaja INTEGER PRIMARY KEY AUTOINCREMENT,FechaCierre TEXT,IdTurno INTEGER,UsuarioReg TEXT,Ingresos NUMERIC,Salidas NUMERIC,Ventas NUMERIC,Total NUMERIC,Descripcion TEXT);
CREATE TABLE Ve_DocVenta (idDocVenta INTEGER PRIMARY KEY,UsuarioReg TEXT,IdCierre INTEGER);
CREATE TABLE Cb_CajaBanco (IdCajaBanco INTEGER PRIMARY KEY,IdTipoCajaBanco INTEGER,IdCuenta INTEGER,FechaDoc TEXT,FechaReg TEXT,Concepto TEXT,Importe NUMERIC,Anulado INTEGER,EsDelVendedor INTEGER,UsuarioReg TEXT,IdAlmacen INTEGER,IdCierre INTEGER);
CREATE TABLE Cb_TipoCajaBanco (IdTipoCajaBanco INTEGER,Tipo INTEGER);
CREATE TABLE Cb_Cuenta (IdCuenta INTEGER,Cuenta TEXT);
CREATE TABLE Seg_Usuario (Usuario TEXT,IdUsuarioPerfil INTEGER,Anulado INTEGER);
INSERT INTO Cb_TipoCajaBanco VALUES (1,0),(2,1);
INSERT INTO Cb_Cuenta VALUES (1,"CAJA PRINCIPAL"),(2,"BANCO BCP");
INSERT INTO Seg_Usuario VALUES ("caja",16,0),("otro",16,0);
INSERT INTO Ve_DocVenta VALUES (1,"CAJA",NULL),(2,"otro",NULL),(3,"caja",99);';
if($mysql){$schema=str_replace('CREATE TABLE ','CREATE TEMPORARY TABLE ',$schema);$schema=str_replace('INTEGER PRIMARY KEY AUTOINCREMENT','INTEGER PRIMARY KEY AUTO_INCREMENT',$schema);$schema=str_replace('Descripcion TEXT);','Descripcion TEXT) ENGINE=MyISAM;',$schema);$schema=str_replace('IdCierre INTEGER);','IdCierre INTEGER) ENGINE=InnoDB;',$schema);}
$db->exec($schema);
$stmt=$db->prepare('INSERT INTO Cb_CajaBanco VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
$fixtures=[
 [374,1,1,'2026-10-14 13:35:56','2026-10-14 13:35:56','Vale antiguo',69.4,0,0,'caja',1,null],
 [375,2,1,'2026-10-14 14:34:35','2026-10-14 14:34:35','Compra de chatarra',50,0,null,'CAJA',1,null],
 [376,2,1,'2026-10-14 20:37:26','2026-10-14 20:37:26','Gasolina',10,0,1,'caja',1,null],
 [418,1,1,'2026-10-16 08:39:08','2026-10-16 08:39:08','Vale ingreso',9.9,0,1,'caja',1,null],
 [419,1,2,'2026-10-16 09:05:17','2026-10-16 09:05:17','Cancelación BCP',1017.57,0,0,'caja',1,null],
 [420,2,2,'2026-10-17 23:59:00','2026-10-17 23:59:00','Salida banco anterior',20,0,1,'caja',1,null],
 [421,1,1,'2026-10-18 08:00:00','2026-10-18 08:00:00','Anulado',15,1,0,'caja',1,null],
 [422,1,1,'2026-10-18 08:00:00','2026-10-18 08:00:00','Otro vendedor',50,0,0,'otro',1,null],
 [423,1,1,'2026-10-10 08:00:00','2026-10-10 08:00:00','Ya cerrado',40,0,1,'caja',1,99],
];foreach($fixtures as $row)$stmt->execute($row);
$context=new ClosingContext(new ClosingDatabase($db));$_SESSION=['user'=>'caja'];$call=function($method,$path,$params=[])use($app,$context){return $app->routes[$method.$path]->call($context,new Request($params),new Response(),[]);};
$pending=function($bank,$direction)use($call){return $call('get','/cierrecaja/'.$direction,['usuario'=>'caja','soloBancos'=>$bank])->data;};
checkClosing(count($pending(0,'ingresos'))===3&&count($pending(0,'salidas'))===2,'All pending cash listed');checkClosing(count($pending(1,'ingresos'))===1&&count($pending(1,'salidas'))===1,'Earlier bank references listed');
$before=$db->query('SELECT * FROM Cb_CajaBanco ORDER BY IdCajaBanco')->fetchAll();$params=['idTurno'=>1,'user'=>'caja','ingresos'=>79.3,'salidas'=>60,'totalVentas'=>0,'total'=>19.3,'descripcion'=>'Cierre de prueba'];$closed=$call('post','/cierrecaja',$params);checkClosing($closed->status===200,'Close succeeds');$id=$closed->data['idCierraCaja'];
foreach([0,1] as $bank)foreach(['ingresos','salidas'] as $direction)checkClosing($pending($bank,$direction)===[],'Next shift empty');
$after=$db->query('SELECT * FROM Cb_CajaBanco ORDER BY IdCajaBanco')->fetchAll();foreach($before as $i=>$row){$changed=$after[$i];checkClosing($changed['IdCierre']==($row['UsuarioReg']==='otro'||$row['IdCierre']!==null?$row['IdCierre']:(int)$id),'Only selected pending seller closed: '.json_encode([$row['IdCajaBanco'],$changed['IdCierre'],$id]));unset($row['IdCierre'],$changed['IdCierre']);checkClosing($row===$changed,'Amounts/concepts/dates/history unchanged');}
checkClosing((int)$db->query('SELECT IdCierre FROM Ve_DocVenta WHERE idDocVenta=1')->fetchColumn()===(int)$id,'Sale linked');checkClosing($db->query('SELECT IdCierre FROM Ve_DocVenta WHERE idDocVenta=2')->fetchColumn()===null,'Other seller sale untouched');
$historical=$call('get','/cierrecaja/ingresos',['usuario'=>'caja','idCierre'=>$id,'soloBancos'=>1]);checkClosing(count($historical->data)===1&&(int)$historical->data[0]['IdCajaBanco']===419,'Earlier bank reference stays in historical close');
$stmt->execute([424,1,1,'2026-10-18 09:01:00','2026-10-18 09:01:00','Nuevo turno',25,0,0,'caja',1,null]);checkClosing(count($pending(0,'ingresos'))===1&&(int)$pending(0,'ingresos')[0]['IdCajaBanco']===424,'New turn only new movement');
$db->exec('INSERT INTO Ve_DocVenta VALUES (4,"caja",NULL)');$context->db->fail=true;$count=$db->query('SELECT COUNT(*) FROM Cb_CierreCaja')->fetchColumn();$failed=$call('post','/cierrecaja',$params);checkClosing($failed->status===500&&$db->query('SELECT COUNT(*) FROM Cb_CierreCaja')->fetchColumn()===$count,'Header rollback on failure');checkClosing($db->query('SELECT IdCierre FROM Ve_DocVenta WHERE idDocVenta=4')->fetchColumn()===null&&count($pending(0,'ingresos'))===1,'Sale and movement rollback together');
$context->db->fail=false;$second=$call('post','/cierrecaja',$params);checkClosing($second->status===200&&$second->data['idCierraCaja']!==$id&&$pending(0,'ingresos')===[],'Second shift closes independently');checkClosing((int)$db->query('SELECT COUNT(*) FROM Cb_CajaBanco WHERE IdCierre='.(int)$id)->fetchColumn()===7,'First close keeps all its movements');
echo 'PASS actual IMCEDEN routes: earlier dates, midnight bank references, manual vouchers, cancelled rows, selected seller isolation, preserved history, clean next shift and rollback. '.($mysql?'MySQL temporary tables with MyISAM header and InnoDB movements':'SQLite memory only')."; no real closing executed.\n";
