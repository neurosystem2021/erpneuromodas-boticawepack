const fs = require('fs');
const path = require('path');
const os = require('os');
const net = require('net');
const { spawn } = require('child_process');
const assert = require('assert');
(async () => {
  const root = fs.mkdtempSync(path.join(os.tmpdir(), 'nst-gallery-test-'));
  fs.mkdirSync(path.join(root, 'api'));
  fs.mkdirSync(path.join(root, 'resources/images/productos'), { recursive: true });
  fs.copyFileSync('server/product-images-routes.php', path.join(root, 'api/routes.php'));
  fs.writeFileSync(path.join(root, 'api/index.php'), `<?php
class TestStatement {
  public $query;
  function __construct($query) { $this->query = $query; }
  function execute($args) { if (strpos($this->query, 'UPDATE') === 0) file_put_contents(__DIR__ . '/primary.json', json_encode(array('Imagen' => $args[0]))); return true; }
  function fetch($mode) { return is_file(__DIR__ . '/primary.json') ? json_decode(file_get_contents(__DIR__ . '/primary.json'), true) : array('Imagen' => 'existing.jpg'); }
}
class TestDb { function prepare($query) { return new TestStatement($query); } }
class TestResponse { function withJson($value, $status = 200) { return array('value' => $value, 'status' => $status); } }
class TestApp {
  public $handlers = array();
  function get($route, $handler) { $this->handlers['GET' . $route] = $handler; }
  function post($route, $handler) { $this->handlers['POST' . $route] = $handler; }
}
$app = new TestApp();
require __DIR__ . '/routes.php';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$key = $_SERVER['REQUEST_METHOD'] . preg_replace('/\\/1$/', '/{id}', $path);
if (!isset($app->handlers[$key])) { http_response_code(404); exit; }
$container = new stdClass(); $container->db = new TestDb();
$handler = $app->handlers[$key]->bindTo($container);
$result = $handler(null, new TestResponse(), array('id' => '1'));
http_response_code($result['status']); header('Content-Type: application/json'); echo json_encode($result['value']);
`);
  const probe = net.createServer();
  await new Promise(resolve => probe.listen(0, '127.0.0.1', resolve));
  const port = probe.address().port;
  await new Promise(resolve => probe.close(resolve));
  const server = spawn('C:/xampp/php/php.exe', ['-d', 'upload_tmp_dir=' + root, '-S', '127.0.0.1:' + port, '-t', path.join(root, 'api')], { windowsHide: true, stdio: 'ignore' });
  try {
    const base = 'http://127.0.0.1:' + port;
    for (let attempt = 0; attempt < 30; attempt++) { try { await fetch(base + '/producto/imagenes/1'); break; } catch (error) { await new Promise(resolve => setTimeout(resolve, 100)); } }
    const png = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aWZkAAAAASUVORK5CYII=', 'base64');
    const form = new FormData(); form.append('imagen2', new Blob([png], { type: 'image/png' }), 'second.png'); form.append('imagen3', new Blob([png], { type: 'image/png' }), 'third.png');
    let response = await fetch(base + '/producto/imagen/1', { method: 'POST', body: form });
    assert.strictEqual(response.status, 200);
    const body = await response.text();
    assert(body.trim().startsWith('{'), body.slice(0, 1000));
    let data = JSON.parse(body);
    assert.strictEqual(data.imagenes[0], 'existing.jpg');
    assert(data.imagenes[1] && data.imagenes[2]);
    const before = data.imagenes;
    data = await (await fetch(base + '/producto/imagenes/1')).json();
    assert.deepStrictEqual(data.imagenes, before);
    const invalid = new FormData(); invalid.append('imagen2', new Blob(['not an image'], { type: 'image/png' }), 'invalid.png');
    response = await fetch(base + '/producto/imagen/1', { method: 'POST', body: invalid });
    assert.strictEqual(response.status, 400);
    data = await (await fetch(base + '/producto/imagenes/1')).json();
    assert.deepStrictEqual(data.imagenes, before);
    const primary = new FormData(); primary.append('imagen', new Blob([png], { type: 'image/png' }), 'primary.png');
    response = await fetch(base + '/producto/imagen/1', { method: 'POST', body: primary });
    assert.strictEqual(response.status, 200);
    data = await response.json();
    assert.notStrictEqual(data.imagenes[0], 'existing.jpg');
    assert.strictEqual(data.imagenes[1], before[1]);
    assert.strictEqual(data.imagenes[2], before[2]);
    console.log('Isolated PHP upload test passed: three images persist, replacements preserve other slots, invalid files rejected.');
  } finally { server.kill(); }
  process.exit(0);
})().catch(error => { console.error(error); process.exit(1); });
