<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['user'])) { header('Location: /index.php'); exit; }

$logo = '/resources/images/imceden-store.svg?v=20261018-1';
$url = '/tienda/';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Tienda virtual | IMCEDEN</title>
  <link rel="stylesheet" href="/resources/css/tienda-flyer-manager.css?v=20261001-1">
</head>
<body>
  <header class="store-admin-bar">
    <img class="store-admin-logo" src="<?php echo htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>" alt="IMCEDEN">
    <div class="store-admin-title">
      <b>Tienda virtual</b>
      <span>Catálogo de baterías, aros, llantas y repuestos</span>
    </div>
    <div class="store-admin-actions">
      <button type="button" id="manageFlyers">Gestionar flyers</button>
      <button type="button" id="copyStoreLink" data-url="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>">Copiar enlace</button>
      <a href="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Abrir tienda</a>
    </div>
  </header>

  <iframe id="storeFrame" src="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" title="Tienda virtual IMCEDEN"></iframe>

  <div class="flyer-manager" id="flyerManager" aria-hidden="true">
    <section class="flyer-dialog" role="dialog" aria-modal="true" aria-labelledby="flyerTitle">
      <header class="flyer-head">
        <div>
          <h2 id="flyerTitle">Publicidad de la tienda</h2>
          <p>Sube flyers, cambia su orden y publica promociones.</p>
        </div>
        <button type="button" class="flyer-close" id="closeFlyers" aria-label="Cerrar">&times;</button>
      </header>
      <form class="flyer-upload" id="flyerForm">
        <label for="flyerFiles" id="flyerFileLabel">Seleccionar flyers en JPG, PNG, GIF o WEBP</label>
        <input id="flyerFiles" name="flyers[]" type="file" accept="image/jpeg,image/png,image/gif,image/webp" multiple>
        <button type="submit">Publicar flyers</button>
      </form>
      <div class="flyer-message" id="flyerMessage"></div>
      <div class="flyer-list" id="flyerList"></div>
    </section>
  </div>

  <script src="/resources/js/tienda-flyer-manager.js?v=20261001-1"></script>
</body>
</html>
