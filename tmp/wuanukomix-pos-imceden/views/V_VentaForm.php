<?php
if (isset($_GET['pos']) && $_GET['pos'] === 'imceden') {
    require __DIR__ . '/V_VentaFormImceden.php';
    return;
}
include_once("../clases/helpers/Modal.php");

$pageWasRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';
if ($pageWasRefreshed && isset($_GET['idPreOrden'])) {
	header("Location: /views/V_VentaForm.php");
	exit();
}
?>

<html>
<head>
	<title>Inventario</title>
</head>
<?php include_once 'linker.php'; ?>

<style media="screen">
	body {
		margin: 0;
		overflow-x: hidden;
	}

	#iframeMovimiento {
		display: block;
		width: 100%;
		min-height: 420px;
	}
</style>

<body>
<?php include("header.php"); ?>
<script>
	function resizeIframe(obj) {
		var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
		var iframeTop = obj.getBoundingClientRect().top;
		obj.style.height = Math.max(420, viewportHeight - iframeTop - 2) + 'px';
	}

	window.addEventListener('resize', function () {
		var iframe = document.getElementById('iframeMovimiento');
		if (iframe) {
			resizeIframe(iframe);
		}
	});
</script>

<?php
$indexPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'nuevo' . DIRECTORY_SEPARATOR . 'index.html';
$assetVersion = is_file($indexPath) ? filemtime($indexPath) : time();
$baseRoute = "/nuevo/index.html?v=" . rawurlencode((string) $assetVersion) . "#/ventas";

if (isset($_GET['idPreOrden']) && isset($_GET['idCliente'])) {
	$src = $baseRoute
		. "?idPreOrden=" . rawurlencode($_GET['idPreOrden'])
		. "&idCliente=" . rawurlencode($_GET['idCliente']);
} else {
	$src = $baseRoute;
}
?>

<iframe id="iframeMovimiento" src="<?php echo htmlspecialchars($src, ENT_QUOTES, 'UTF-8'); ?>" width="100%" frameborder="0" onload="resizeIframe(this)"></iframe>

</body>
</html>
