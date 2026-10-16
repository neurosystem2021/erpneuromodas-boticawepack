<?php
function getNow($format = null) { return $format === 'Y' ? '2026' : '2026-10-10 16:00:00'; }
class RequestMock {
    public function getParam($name) {
        $data = array('movimiento' => array('proveedor' => array('IdProveedor' => 111)), 'IdAlmacen' => 1,
            'productos' => array(array('IdProducto' => 1, 'cantidad' => 7, 'precio' => 127.65)));
        return $data[$name];
    }
}
class ResponseMock { public function withJson($data, $status = 200) { return array($data, $status); } }
class StatementMock {
    private $db;
    public function __construct($db) { $this->db = $db; }
    public function execute($params) {}
    public function fetchColumn() { return 1; }
    public function fetchAll($mode) { return $this->db->duplicates; }
}
class DatabaseMock {
    public $duplicates = array(); public $insertions = 0; public $committed = false; public $transaction = false;
    public function query($sql) { return new StatementMock($this); }
    public function prepare($sql) { if (strpos($sql, 'INSERT') === 0) $this->insertions++; return new StatementMock($this); }
    public function beginTransaction() { $this->transaction = true; }
    public function inTransaction() { return $this->transaction; }
    public function rollBack() { $this->transaction = false; }
    public function commit() { $this->committed = true; $this->transaction = false; }
    public function lastInsertId() { return 28; }
}
$handler = require __DIR__ . '/../server/order-create.php';
$db = new DatabaseMock();
$handler = $handler->bindTo((object)array('db' => $db));
$db->duplicates = array(array('IdOrdenCompra' => 27, 'Correlativo' => '2026-3', 'IdProducto' => 1));
$result = $handler(new RequestMock(), new ResponseMock());
if ($result[1] !== 409 || $db->insertions !== 0 || $db->transaction) throw new Exception('Duplicate was not blocked before insertion.');
$db->duplicates = array();
$result = $handler(new RequestMock(), new ResponseMock());
if ($result[1] !== 200 || !$db->committed || $db->insertions !== 2) throw new Exception('Valid order was not committed.');
echo "OK: duplicate rejected with 409 before inserts; valid order committed.\n";
