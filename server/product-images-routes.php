<?php
// Keep the legacy primary image in Gen_Producto.Imagen; two extra slots use a sidecar.
$app->get('/producto/imagenes/{id}', function ($request, $response, $args) {
    $id = filter_var($args['id'], FILTER_VALIDATE_INT);
    if (!$id || $id < 1) return $response->withJson(array('error' => 'Producto inválido.'), 400);
    $stmt = $this->db->prepare('SELECT Imagen FROM Gen_Producto WHERE IdProducto = ?');
    $stmt->execute(array($id));
    $product = $stmt->fetch(\PDO::FETCH_ASSOC);
    if (!$product) return $response->withJson(array('error' => 'Producto no encontrado.'), 404);
    $path = dirname(__DIR__) . '/resources/images/productos/gallery-' . $id . '.json';
    $extra = is_file($path) ? json_decode(file_get_contents($path), true) : array();
    if (!is_array($extra)) return $response->withJson(array('error' => 'No se pudo leer la galería.'), 500);
    return $response->withJson(array('imagenes' => array($product['Imagen'] ?: '', isset($extra[2]) ? $extra[2] : '', isset($extra[3]) ? $extra[3] : '')));
});

$app->post('/producto/imagen/{id}', function ($request, $response, $args) {
    if (empty($_FILES)) return $response->withJson(array('error' => 'No se recibieron imágenes. Revisa el tamaño del archivo.'), 400);
    $id = filter_var($args['id'], FILTER_VALIDATE_INT);
    if (!$id || $id < 1) return $response->withJson(array('error' => 'Producto inválido.'), 400);
    $stmt = $this->db->prepare('SELECT Imagen FROM Gen_Producto WHERE IdProducto = ?');
    $stmt->execute(array($id));
    $product = $stmt->fetch(\PDO::FETCH_ASSOC);
    if (!$product) return $response->withJson(array('error' => 'Producto no encontrado.'), 404);
    $directory = dirname(__DIR__) . '/resources/images/productos';
    $path = $directory . '/gallery-' . $id . '.json';
    $lock = fopen($directory . '/gallery-' . $id . '.lock', 'c');
    if (!$lock || !flock($lock, LOCK_EX)) return $response->withJson(array('error' => 'No se pudo bloquear la galería.'), 500);
    try {
        $extra = is_file($path) ? json_decode(file_get_contents($path), true) : array();
        if (!is_array($extra)) throw new \RuntimeException('No se pudo leer la galería.');
        $pending = array();
        $types = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
        foreach (array(1 => 'imagen', 2 => 'imagen2', 3 => 'imagen3') as $slot => $key) {
            if (!isset($_FILES[$key]) || $_FILES[$key]['error'] === UPLOAD_ERR_NO_FILE) continue;
            $file = $_FILES[$key];
            if (is_array($file['error']) || $file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'])) throw new \InvalidArgumentException('Imagen ' . $slot . ': archivo inválido o mayor de 5 MB.');
            $info = @getimagesize($file['tmp_name']);
            if (!$info || !isset($types[$info['mime']])) throw new \InvalidArgumentException('Usa imágenes JPG, PNG o WebP.');
            $name = 'product-' . $id . '-' . $slot . '-' . bin2hex(random_bytes(12)) . '.' . $types[$info['mime']];
            $pending[$slot] = array('temp' => $file['tmp_name'], 'name' => $name);
        }
        if (!$pending) throw new \InvalidArgumentException('No se recibieron imágenes válidas.');
        foreach ($pending as $slot => $file) {
            if (!move_uploaded_file($file['temp'], $directory . '/' . $file['name'])) throw new \RuntimeException('No se pudo guardar la imagen.');
            if ($slot === 1) {
                $update = $this->db->prepare('UPDATE Gen_Producto SET Imagen = ? WHERE IdProducto = ?');
                $update->execute(array($file['name'], $id));
                $product['Imagen'] = $file['name'];
            } else $extra[$slot] = $file['name'];
        }
        if (isset($pending[2]) || isset($pending[3])) {
            $temporary = tempnam($directory, 'gallery-');
            if (!$temporary || file_put_contents($temporary, json_encode($extra)) === false || !rename($temporary, $path)) throw new \RuntimeException('No se pudo guardar la galería.');
        }
        return $response->withJson(array('insertId' => $id, 'imagenes' => array($product['Imagen'] ?: '', isset($extra[2]) ? $extra[2] : '', isset($extra[3]) ? $extra[3] : '')));
    } catch (\InvalidArgumentException $error) {
        return $response->withJson(array('error' => $error->getMessage()), 400);
    } catch (\Exception $error) {
        return $response->withJson(array('error' => 'No se pudo guardar la galería. Intenta nuevamente.'), 500);
    } finally {
        flock($lock, LOCK_UN); fclose($lock);
    }
});
