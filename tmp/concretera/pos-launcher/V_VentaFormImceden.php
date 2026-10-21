<?php
// The copied POS inherits the existing sales route's authentication,
// permissions, plan restrictions and subscription checks.
if (basename((string) ($_SERVER['PHP_SELF'] ?? '')) !== 'V_VentaForm.php') {
    header('Location: /views/V_VentaForm.php?pos=imceden');
    exit;
}
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
include_once("../clases/helpers/Modal.php");
?>

<html>
<head>
	<title>Inventario</title>
</head>
<?php include_once 'linker.php'; ?>

<style media="screen">
	html,
	body {
		width: 100%;
		height: 100%;
		margin: 0;
		overflow: hidden;
	}

	#iframeMovimiento {
		display: block;
		width: 100%;
		border: 0;
		box-sizing: border-box;
		box-shadow: none;
		transform: none;
	}

	/* Compact header for screens using 125%/150% Windows scaling. */
	#navBar.navbar-style {
		min-height: 58px;
		margin-bottom: 0;
		padding: 7px 10px;
		font-size: 14px;
	}

	#navBar .navbar-nav {
		font-size: 14px !important;
	}

	#navBar .navbar-nav > li > a {
		padding: 10px 0;
		font-size: 1em !important;
		white-space: nowrap;
	}

	#navBar .dropdown {
		margin: 2px 5px;
		padding: 1px;
	}

	#navBar .navbar-brand img {
		width: 92px;
	}

	#navBar .imgLogoNeuro,
	#navBar li.navli > img {
		max-width: 125px !important;
		height: auto;
	}

	@media (max-width: 1500px) {
		#navBar.navbar-style,
		#navBar .navbar-nav {
			font-size: 12px !important;
		}

		#navBar .dropdown {
			margin-right: 3px;
			margin-left: 3px;
		}
	}

	/* Encabezado premium y compacto exclusivo del punto de venta. */
	#navBar.navbar-style {
		position: relative;
		z-index: 1050;
		min-height: 72px;
		margin: 0;
		padding: 8px 22px;
		border: 0;
		border-radius: 0 0 5px 5px;
		background:
			radial-gradient(circle at 76% -90%, rgba(213, 255, 56, .18), transparent 38%),
			linear-gradient(105deg, #071b3f 0%, #0a2c5c 48%, #07477b 100%) !important;
		box-shadow:
			0 9px 24px rgba(2, 35, 81, .25),
			inset 0 1px 0 rgba(255, 255, 255, .24),
			inset 0 -1px 0 rgba(1, 35, 77, .32);
		transition: transform .28s ease, opacity .22s ease, margin-bottom .28s ease;
	}

	body.pos-nav-hidden #navBar.navbar-style {
		margin-bottom: -72px;
		opacity: 0;
		pointer-events: none;
		transform: translateY(-100%);
	}

	body.pos-nav-hidden #iframeMovimiento {
		height: auto;
	}

	/* El punto de venta nunca debe ocultar su navegación principal. */
	#navBar.navbar-style::after {
		position: absolute;
		right: 9%;
		bottom: 0;
		left: 9%;
		height: 1px;
		content: "";
		background: linear-gradient(90deg, transparent, #d5ff38, transparent);
	}

	#navBar .container-fluid {
		display: flex;
		min-height: 55px;
		padding: 0;
		align-items: center;
	}

	#navBar .navbar-header {
		flex: 0 0 105px;
	}

	#navBar .navbar-brand {
		display: flex;
		height: 55px;
		padding: 5px 0 !important;
		align-items: center;
	}

	#navBar .navbar-brand img {
		width: 82px !important;
		max-height: 48px;
		margin: 0 !important;
		object-fit: contain;
		filter: drop-shadow(0 4px 7px rgba(0, 20, 55, .28));
	}

	#navBar #myNavbar {
		flex: 1 1 auto;
		padding: 0;
	}

	#navBar .navbar-nav {
		display: flex;
		margin: 0 !important;
		align-items: center;
		justify-content: flex-end;
		gap: 7px;
		float: none !important;
		font-size: 13px !important;
	}

	#navBar .navbar-nav > li.dropdown {
		margin: 0 !important;
		padding: 0 !important;
		border: 1px solid rgba(157, 220, 255, .27);
		border-radius: 999px;
		background: rgba(7, 27, 63, .34);
		box-shadow:
			inset 0 1px 0 rgba(255, 255, 255, .12),
			0 5px 13px rgba(2, 35, 81, .12);
		transition: transform .2s ease, background .2s ease, border-color .2s ease, box-shadow .2s ease;
	}

	#navBar .navbar-nav > li.dropdown:hover,
	#navBar .navbar-nav > li.dropdown.open {
		z-index: 2;
		border-color: rgba(167, 232, 255, .72);
		background: linear-gradient(135deg, #123f78, #071f49);
		box-shadow:
			0 9px 21px rgba(0, 48, 111, .3),
			0 0 0 3px rgba(79, 195, 247, .12),
			inset 0 1px 0 rgba(255, 255, 255, .25);
		transform: translateY(-2px);
	}

	#navBar .navbar-nav > li.dropdown > a {
		display: flex;
		min-height: 38px;
		padding: 9px 13px !important;
		align-items: center;
		gap: 6px;
		border-radius: inherit;
		color: #fff !important;
		background: transparent !important;
		font-size: 12px !important;
		font-weight: 800;
		line-height: 1;
		letter-spacing: -.01em;
		text-shadow: 0 1px 2px rgba(0, 29, 70, .35);
		white-space: nowrap;
	}

	#navBar .navbar-nav > li.dropdown > a > i {
		font-size: 14px;
		color: #ddf7ff;
		filter: drop-shadow(0 2px 3px rgba(0, 24, 66, .24));
		transition: transform .2s ease, color .2s ease;
	}

	#navBar .navbar-nav > li.dropdown:hover > a > i {
		color: #fff;
		transform: scale(1.12);
	}

	#navBar .caret {
		margin-left: 1px;
		border-top-color: #dff7ff !important;
	}

	#navBar .navbar-nav > li.navli {
		margin: 0 !important;
		padding: 0 2px !important;
	}

	#navBar .navbar-nav > li.navli > div {
		padding: 0 4px !important;
		font-size: 10px !important;
		line-height: 1.35;
	}

	#navBar .navbar-nav > li.navli:last-child img {
		width: 112px !important;
		max-width: 112px !important;
		margin: 0 0 0 4px !important;
		filter: drop-shadow(0 4px 7px rgba(0, 20, 55, .3));
	}

	#navBar .dropdown-menu {
		top: 100%;
		min-width: 250px;
		max-height: calc(100vh - 82px);
		margin-top: 0;
		padding: 8px;
		overflow-x: hidden;
		overflow-y: auto;
		border: 1px solid rgba(171, 210, 237, .7);
		border-radius: 18px;
		background: rgba(250, 253, 255, .98);
		box-shadow: 0 22px 48px rgba(2, 27, 64, .24);
	}

	#navBar .dropdown-menu::-webkit-scrollbar {
		width: 7px;
	}

	#navBar .dropdown-menu::-webkit-scrollbar-thumb {
		border: 2px solid transparent;
		border-radius: 999px;
		background: #8cb8d7;
		background-clip: padding-box;
	}

	/* Puente de interacción: evita que el menú se cierre al mover el cursor. */
	#navBar .navbar-nav > li.dropdown > .dropdown-menu::before {
		position: absolute;
		top: -10px;
		right: 0;
		left: 0;
		height: 12px;
		content: "";
		background: transparent;
	}

	#navBar .navbar-nav > li.dropdown:hover > .dropdown-menu,
	#navBar .navbar-nav > li.dropdown.open > .dropdown-menu,
	#navBar .navbar-nav > li.dropdown > .dropdown-menu:hover {
		display: block !important;
		visibility: visible !important;
		opacity: 1 !important;
		pointer-events: auto !important;
	}

	#navBar .dropdown-menu > li {
		margin: 2px 0;
		padding: 0 !important;
		border-radius: 11px;
		font-size: 12px !important;
	}

	#navBar .dropdown-menu > li > a {
		padding: 9px 12px;
		border-radius: 11px;
		color: #243b57;
		font-size: 12px !important;
		font-weight: 750;
		white-space: normal;
		transition: color .16s ease, background .16s ease, transform .16s ease;
	}

	#navBar .dropdown-menu > li:hover > a,
	#navBar .dropdown-menu > li > a:hover,
	#navBar .dropdown-menu > li > a:focus,
	#navBar .dropdown-menu > li > a:active,
	#navBar .dropdown-menu > li.active > a,
	#navBar .dropdown-menu > li.active > a:hover,
	#navBar .dropdown-menu > li.active > a:focus {
		color: #fff !important;
		background-color: #0a3d77 !important;
		background-image: linear-gradient(110deg, #155ca4, #071f49) !important;
		box-shadow: inset 3px 0 0 #d5ff38, 0 6px 14px rgba(3, 48, 103, .18), 0 0 14px rgba(213, 255, 56, .12);
		transform: translateX(2px);
	}

	#navBar .dropdown-menu .divider {
		margin: 6px 8px;
		background: #dce9f3;
	}

	@media (max-width: 1500px) {
		#navBar.navbar-style {
			padding-right: 10px;
			padding-left: 10px;
		}

		#navBar .navbar-header {
			flex-basis: 88px;
		}

		#navBar .navbar-nav {
			gap: 4px;
		}

		#navBar .navbar-nav > li.dropdown > a {
			padding-right: 9px !important;
			padding-left: 9px !important;
			font-size: 10.5px !important;
		}

		#navBar .navbar-nav > li.navli:last-child img {
			width: 96px !important;
			max-width: 96px !important;
		}
	}

	@media (max-width: 1024px) {
		#navBar .container-fluid {
			display: block;
		}

		#navBar .navbar-header {
			min-height: 55px;
		}

		#navBar #myNavbar.in .navbar-nav {
			display: block;
			padding: 8px 0 12px;
		}

		#navBar #myNavbar.in .navbar-nav > li.dropdown {
			margin: 5px 0 !important;
			border-radius: 14px;
		}
	}

	#modalAddProveedor{
		/*/width: 700px; !important*/
	}

	/* Acabado automotriz para la barra principal del punto de venta. */
	#navBar.navbar-style {
		position: relative;
		overflow: visible;
		border-bottom: 1px solid rgba(0, 220, 226, .55);
		background:
			repeating-linear-gradient(135deg, rgba(255,255,255,.025) 0 1px, transparent 1px 10px),
			radial-gradient(ellipse at 78% -55%, rgba(0, 206, 222, .28), transparent 34%),
			linear-gradient(112deg, #11191f 0%, #1b2a32 42%, #163b47 75%, #102d38 100%) !important;
		box-shadow:
			0 10px 28px rgba(7, 21, 29, .42),
			inset 0 1px 0 rgba(255,255,255,.16),
			inset 0 -1px 0 rgba(0, 219, 226, .34);
	}

	#navBar.navbar-style::before {
		position: absolute;
		top: 0;
		right: 5%;
		left: 5%;
		height: 2px;
		content: "";
		background: linear-gradient(90deg, transparent, #00c6dc 24%, #b7db36 52%, #00c6dc 78%, transparent);
		box-shadow: 0 0 14px rgba(0, 210, 224, .56);
		pointer-events: none;
	}

	#navBar.navbar-style::after {
		right: 5%;
		left: 5%;
		height: 2px;
		background: linear-gradient(90deg, transparent, rgba(0,198,220,.92) 28%, rgba(183,219,54,.94) 52%, rgba(0,198,220,.92) 76%, transparent);
		box-shadow: 0 0 14px rgba(0, 198, 220, .42);
	}

	#navBar .navbar-nav > li.dropdown {
		border-color: rgba(133, 207, 218, .32);
		background: linear-gradient(145deg, rgba(255,255,255,.08), rgba(5,17,23,.33));
		box-shadow: inset 0 1px 0 rgba(255,255,255,.15), 0 5px 14px rgba(0,0,0,.2);
	}

	#navBar .navbar-nav > li.dropdown:hover,
	#navBar .navbar-nav > li.dropdown.open {
		border-color: #a9db3c;
		background: linear-gradient(135deg, rgba(0,186,204,.28), rgba(9,39,48,.92));
		box-shadow: 0 9px 23px rgba(0,0,0,.3), 0 0 0 3px rgba(0,198,220,.14), 0 0 18px rgba(0,198,220,.2), inset 0 1px 0 rgba(255,255,255,.24);
	}

	#navBar .navbar-nav > li.dropdown > a > i {
		color: #50dce3;
		filter: drop-shadow(0 0 5px rgba(0,210,224,.38));
	}

	#navBar .navbar-nav > li.dropdown:hover > a > i {
		color: #d1ef55;
	}

	#navBar .caret {
		border-top-color: #c8e957 !important;
	}

	#navBar .navbar-brand img,
	#navBar .navbar-nav > li.navli:last-child img {
		filter: drop-shadow(0 3px 5px rgba(0,0,0,.35)) drop-shadow(0 0 8px rgba(0,198,220,.16));
	}

	#navBar .dropdown-menu {
		border-color: rgba(30, 142, 159, .5);
		box-shadow: 0 22px 48px rgba(5, 20, 29, .38), 0 0 0 1px rgba(255,255,255,.55);
	}

	#navBar .dropdown-menu > li:hover > a,
	#navBar .dropdown-menu > li > a:hover,
	#navBar .dropdown-menu > li > a:focus,
	#navBar .dropdown-menu > li > a:active,
	#navBar .dropdown-menu > li.active > a,
	#navBar .dropdown-menu > li.active > a:hover,
	#navBar .dropdown-menu > li.active > a:focus {
		background-color: #08798a !important;
		background-image: linear-gradient(110deg, #087f92, #134b59) !important;
		box-shadow: inset 3px 0 0 #b7db36, 0 6px 14px rgba(3,48,67,.22), 0 0 12px rgba(0,198,220,.14);
	}

	@media (max-width: 1024px) {
		#navBar #myNavbar.in .navbar-nav > li.dropdown {
			border-color: rgba(0, 198, 220, .32);
			background: linear-gradient(135deg, rgba(255,255,255,.08), rgba(5,17,23,.28));
		}
	}
</style>


<body>
<?php include("header.php"); ?>
<script>
  function resizeIframe(obj) {
		var top = obj.getBoundingClientRect().top;
		obj.style.height = Math.max(420, window.innerHeight - top) + 'px';
  }

	function connectPosHeaderScroll(iframe) {
		if (!iframe || iframe.dataset.headerScrollConnected === '1') return;

		try {
			var iframeWindow = iframe.contentWindow;
			var iframeDocument = iframe.contentDocument || iframeWindow.document;
			var lastScrollTop = 0;
			var ticking = false;

			var updateHeader = function () {
				var scrollingElement = iframeDocument.scrollingElement || iframeDocument.documentElement;
				var currentScrollTop = Math.max(
					iframeWindow.pageYOffset || 0,
					scrollingElement ? scrollingElement.scrollTop : 0,
					iframeDocument.body ? iframeDocument.body.scrollTop : 0
				);
				var menuIsOpen = !!document.querySelector('#navBar .dropdown.open');

				if (!menuIsOpen && currentScrollTop > 45 && currentScrollTop > lastScrollTop + 3) {
					document.body.classList.add('pos-nav-hidden');
					resizeIframe(iframe);
				} else if (currentScrollTop < lastScrollTop - 3 || currentScrollTop < 20) {
					document.body.classList.remove('pos-nav-hidden');
					resizeIframe(iframe);
				}

				lastScrollTop = currentScrollTop;
				ticking = false;
			};

			iframeWindow.addEventListener('scroll', function () {
				if (!ticking) {
					window.requestAnimationFrame(updateHeader);
					ticking = true;
				}
			}, { passive: true });

			iframeDocument.addEventListener('mousemove', function (event) {
				if (event.clientY < 28) {
					document.body.classList.remove('pos-nav-hidden');
				}
			}, { passive: true });

			iframe.dataset.headerScrollConnected = '1';
		} catch (error) {
			console.warn('No se pudo conectar el desplazamiento del punto de venta.', error);
		}
	}

	function initializeVentaIframe(iframe) {
		document.body.classList.remove('pos-nav-hidden');
		resizeIframe(iframe);
		connectPosHeaderScroll(iframe);
		if (window.cxAttachProformaLauncher) window.cxAttachProformaLauncher(iframe);
		/* La barra principal debe permanecer visible. Los cambios de pestaña de
		   venta producen desplazamientos internos que antes la ocultaban y dejaban
		   un espacio blanco al pie del iframe. */
	}

	/* Protección adicional frente a scripts o una versión anterior en caché
	   que intenten volver a colocar la clase de ocultamiento. */
	window.addEventListener('resize', function () {
		var iframe = document.getElementById('iframeMovimiento');
		if (iframe) resizeIframe(iframe);
	});
</script>

<?php if (function_exists('cxConcreteraMenuVisible') && cxConcreteraMenuVisible()): ?>
<script src="/resources/js/concretera-pos.js?v=<?php echo (int) filemtime(__DIR__.'/../resources/js/concretera-pos.js'); ?>"></script>
<?php endif; ?>
<?php $ventaAppVersion = max((int) @filemtime(__DIR__ . '/../nuevo-imceden/index.html'), (int) @filemtime(__DIR__ . '/../nuevo-imceden/pos-image-preview-fix.js')); ?>
<?php if (empty($headerPuntoVentaBloqueado)): ?>
<iframe id="iframeMovimiento" src="/nuevo-imceden/index.html?v=<?php echo $ventaAppVersion; ?>#/ventas" width="100%" frameborder="0" onload="initializeVentaIframe(this)"></iframe>
<?php endif; ?>


</html>
