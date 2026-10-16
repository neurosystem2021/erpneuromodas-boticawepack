<?php
// All business tables below are temporary and shadow the live names on this
// connection only. No actual purchase, user or audit record is changed.
ini_set('session.save_path', sys_get_temp_dir());
session_start();
function getNow($format = 'Y-m-d H:i:s') { return date($format); }
class TestApp {
    public $routes = array();
    public function get($path, $handler) { $this->routes[$path] = $handler; }
    public function post($path, $handler) { $this->routes[$path] = $handler; }
}
class TestRequest {
    private $data;
    public function __construct($data) { $this->data = $data; }
    public function getParam($key) { return isset($this->data[$key]) ? $this->data[$key] : null; }
}
class TestResponse { public function withJson($data, $status = 200) { return array('status' => $status, 'data' => $data); } }
function check($condition, $message) { if (!$condition) throw new Exception($message); }
$db = new PDO('mysql:host=127.0.0.1;dbname=nst;charset=utf8mb4', 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$db->exec('CREATE TEMPORARY TABLE Seg_Usuario (Usuario VARCHAR(255), IdUsuarioPerfil INT, Anulado INT) ENGINE=InnoDB');
$db->exec("INSERT INTO Seg_Usuario VALUES ('supertest',1,0), ('otheradmin',1,0), ('seller',3,0)");
$db->exec('CREATE TEMPORARY TABLE Lo_Proveedor (IdProveedor INT PRIMARY KEY, Proveedor VARCHAR(255)) ENGINE=InnoDB');
$db->exec("INSERT INTO Lo_Proveedor VALUES (1,'Proveedor Test')");
$db->exec('CREATE TEMPORARY TABLE Lo_MovimientoTipo (IdMovimientoTipo INT PRIMARY KEY, Tipo INT, VaRegCompra INT, TipoMovimiento VARCHAR(255), Anulado INT) ENGINE=InnoDB');
$db->exec("INSERT INTO Lo_MovimientoTipo VALUES (1,0,1,'FACTURA',0), (2,0,1,'BOLETA',0), (3,1,1,'SALIDA',0)");
$db->exec('CREATE TEMPORARY TABLE Lo_Movimiento (Hash VARCHAR(255) PRIMARY KEY, IdMovimientoTipo INT, IdProveedor INT, Serie VARCHAR(255), Numero INT, Anulado INT, UsuarioMod VARCHAR(255), FechaMod DATETIME, FechaPeriodoTributario INT) ENGINE=InnoDB');
$db->exec("INSERT INTO Lo_Movimiento VALUES ('test1',1,1,'F001',1,0,NULL,NULL,202610), ('test2',2,1,'B001',2,0,NULL,NULL,202610)");
$db->exec('CREATE TEMPORARY TABLE Lo_MovimientoDetalle (hashMovimiento VARCHAR(255), IdProducto INT, Cantidad INT) ENGINE=InnoDB');
$db->exec("INSERT INTO Lo_MovimientoDetalle VALUES ('test1',7,3)");
$schema = file_get_contents(__DIR__ . '/../server/migrations/20261010_movement_audit.sql');
$db->exec(str_replace('CREATE TABLE IF NOT EXISTS', 'CREATE TEMPORARY TABLE', $schema));
$temp = sys_get_temp_dir() . '/nst-movement-test-' . uniqid(); mkdir($temp);
copy(__DIR__ . '/../server/movement-control.php', $temp . '/movement-control.php');
file_put_contents($temp . '/movement-control-config.php', "<?php return array('supertest');");
$app = new TestApp(); require $temp . '/movement-control.php';
$context = (object)array('db' => $db);
$edit = $app->routes['/movimiento/control/editar']->bindTo($context);
$delete = $nstDeleteMovement->bindTo($context);
$data = array('idMov' => 'test1', 'tipo' => 2, 'serie' => 'b001', 'numero' => 3, 'motivo' => 'Error de digitacion', 'user' => 'supertest');
$_SESSION = array();
check($edit(new TestRequest($data), new TestResponse())['status'] === 401, 'Anonymous correction allowed');
$_SESSION = array('user' => 'otheradmin');
check($edit(new TestRequest($data), new TestResponse())['status'] === 403, 'Other administrator correction allowed');
$_SESSION = array('user' => 'seller');
check($delete(new TestRequest($data), new TestResponse())['status'] === 403, 'Unauthorized deletion allowed');
$_SESSION = array('user' => 'supertest');
check($delete(new TestRequest(array('idMov' => 'test1')), new TestResponse())['status'] === 400, 'Missing reason allowed');
$duplicate = array_merge($data, array('numero' => 2));
check($edit(new TestRequest($duplicate), new TestResponse())['status'] === 409, 'Duplicate document allowed');
check($edit(new TestRequest($data), new TestResponse())['status'] === 200, 'Valid correction failed');
$audit = $db->query('SELECT * FROM Lo_MovimientoAuditoria')->fetch(PDO::FETCH_ASSOC);
check($audit['Usuario'] === 'supertest' && $audit['Accion'] === 'EDITAR' && strpos($audit['DocumentoAnterior'], 'F001-1') !== false && strpos($audit['DocumentoNuevo'], 'B001-3') !== false, 'Correction audit incomplete');
$db->exec('ALTER TABLE Lo_MovimientoAuditoria DROP COLUMN Motivo');
check($delete(new TestRequest($data), new TestResponse())['status'] === 500, 'Audit failure did not block deletion');
check((int)$db->query("SELECT COUNT(*) FROM Lo_Movimiento WHERE Hash='test1'")->fetchColumn() === 1, 'Purchase lost after audit failure');
check((int)$db->query("SELECT COUNT(*) FROM Lo_MovimientoDetalle WHERE hashMovimiento='test1'")->fetchColumn() === 1, 'Details lost after audit failure');
$db->exec("ALTER TABLE Lo_MovimientoAuditoria ADD COLUMN Motivo TEXT NOT NULL DEFAULT ''");
check($delete(new TestRequest($data), new TestResponse())['status'] === 200, 'Authorized deletion failed');
$audit = $db->query("SELECT * FROM Lo_MovimientoAuditoria WHERE Accion='ANULAR'")->fetch(PDO::FETCH_ASSOC);
$snapshot = json_decode($audit['Antes'], true);
check($audit['Usuario'] === 'supertest' && $audit['Motivo'] === $data['motivo'] && (int)$snapshot['Detalle'][0]['IdProducto'] === 7, 'Deleted snapshot missing');
check((int)$db->query("SELECT COUNT(*) FROM Lo_Movimiento WHERE Hash='test1'")->fetchColumn() === 1, 'Annulled purchase lost');
check((int)$db->query("SELECT Anulado FROM Lo_Movimiento WHERE Hash='test1'")->fetchColumn() === 1, 'Purchase not annulled');
check((int)$db->query("SELECT COUNT(*) FROM Lo_MovimientoDetalle WHERE hashMovimiento='test1'")->fetchColumn() === 1, 'Annulled details lost');
check($delete(new TestRequest($data), new TestResponse())['status'] === 409, 'Repeated annulment accepted');
unlink($temp . '/movement-control.php'); unlink($temp . '/movement-control-config.php'); rmdir($temp);
echo "OK: authenticated identity, superadmin-only corrections, write permissions, mandatory reason, duplicate rejection, before/after audit, rollback and deletion snapshot. Live records unchanged.\n";
