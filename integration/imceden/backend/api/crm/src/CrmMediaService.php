<?php

final class CrmMediaService
{
    private $root;
    private $types = array(
        'image/jpeg' => array('jpg', 5242880),
        'image/png' => array('png', 5242880),
        'image/webp' => array('webp', 5242880),
        'image/gif' => array('gif', 5242880),
        'video/mp4' => array('mp4', 12582912),
        'video/quicktime' => array('mov', 12582912),
        'video/3gpp' => array('3gp', 12582912),
    );

    public function __construct()
    {
        $this->root = dirname(__DIR__, 3) . '/storage/crm/media';
        if (!is_dir($this->root) && !mkdir($this->root, 0700, true) && !is_dir($this->root)) {
            throw new RuntimeException('MEDIA_STORAGE_UNAVAILABLE', 500);
        }
    }

    public function upload(array $file)
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Seleccione una imagen o video válido.');
        }
        $temporary = (string) ($file['tmp_name'] ?? '');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporary);
        if (!isset($this->types[$mime])) {
            throw new InvalidArgumentException('Formato no permitido. Use JPG, PNG, WEBP, GIF, MP4, MOV o 3GP.');
        }
        list($extension, $maximum) = $this->types[$mime];
        if ((int) ($file['size'] ?? 0) > $maximum) {
            throw new InvalidArgumentException(strpos($mime, 'video/') === 0
                ? 'El video no debe superar 12 MB.' : 'La imagen no debe superar 5 MB.');
        }
        $token = bin2hex(random_bytes(20));
        $destination = $this->root . '/' . $token . '.' . $extension;
        if (!move_uploaded_file($temporary, $destination)) {
            throw new RuntimeException('MEDIA_UPLOAD_FAILED', 500);
        }
        @chmod($destination, 0600);
        $this->purgeExpired();
        return array(
            'token' => $token,
            'type' => strpos($mime, 'video/') === 0 ? 'video' : 'image',
            'mime' => $mime,
            'name' => mb_substr(basename((string) ($file['name'] ?? 'archivo.' . $extension)), 0, 180),
            'size' => (int) filesize($destination),
        );
    }

    public function payload($token)
    {
        if (!preg_match('/^[a-f0-9]{40}$/', (string) $token)) {
            throw new InvalidArgumentException('El archivo adjunto no es válido.');
        }
        $matches = glob($this->root . '/' . $token . '.*');
        if (!$matches || !is_file($matches[0])) throw new RuntimeException('MEDIA_NOT_FOUND', 404);
        $path = $matches[0];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!isset($this->types[$mime]) || filesize($path) > $this->types[$mime][1]) {
            throw new RuntimeException('MEDIA_NOT_ALLOWED', 422);
        }
        return array(
            'data_uri' => 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($path)),
            'mime' => $mime,
            'filename' => 'campana.' . $this->types[$mime][0],
        );
    }

    private function purgeExpired()
    {
        foreach (glob($this->root . '/*') ?: array() as $path) {
            if (is_file($path) && filemtime($path) < time() - 604800) @unlink($path);
        }
    }
}
