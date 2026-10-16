<?php
require_once __DIR__ . '/../models/EmpresaConfig.php';
$etiquetaConfig = empresaConfigObtener();
$etiquetaMeta = $etiquetaConfig['meta'];
function etiquetaValor($key, $default) { global $etiquetaMeta; return isset($etiquetaMeta[$key]) && $etiquetaMeta[$key] !== '' ? $etiquetaMeta[$key] : $default; }
$etiquetaAncho = max(25, min(120, (float) etiquetaValor('etiqueta_ancho_mm', '49')));
$etiquetaAlto = max(15, min(100, (float) etiquetaValor('etiqueta_alto_mm', '36')));
$etiquetaColumnas = max(1, min(4, (int) etiquetaValor('etiqueta_columnas', '2')));
$etiquetaLogo = empresaConfigValor($etiquetaConfig, 'etiqueta_logo', empresaConfigValor($etiquetaConfig, 'logo_ticket', 'resources/images/logo-ticket.svg'));
if (!is_file(__DIR__ . '/../' . ltrim($etiquetaLogo, '/'))) $etiquetaLogo = 'resources/images/barcode-logo.png';
$etiquetaDireccion = empresaConfigValor($etiquetaConfig, 'DIRECCION', '');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Imprimir codigo de barra</title>
    <?php include_once 'linker.php'; ?>
    <style>
            @page {
                margin: 0!important;
            }
            body {
                color: #000000!important;
                margin: 0;
                font-size: 2mm!important;
                font-family: sans-serif !important;
            }
            /* ESTILOS COMPONENTES */
            #barcode {
                width: <?php echo ($etiquetaAncho + 5) * $etiquetaColumnas; ?>mm;
                margin-left: 1mm;
            }
            #barcode .negro {
                background-color: #000000 !important;
                -webkit-print-color-adjust: exact; 
                color: #fff!important;
                padding: 0 .35mm;
                font-weight: bold !important;
            }
            .float-left {
                float: left;
            }
            .float-right {
                float: right;
            }
            .text-right {
                text-align: right;
            }
            .barcode-box {
                border-radius: 2mm;
                margin: .4mm 2.5mm;
                padding: .7mm;
                border: .5mm solid #000;
                display: inline-block;
                width: <?php echo $etiquetaAncho; ?>mm;
                height: <?php echo $etiquetaAlto; ?>mm;
                box-sizing: border-box;
                overflow: hidden;
                position: relative;
            }
            .barcode-header {
                position: absolute;
                z-index: 2;
                top: .7mm;
                left: .8mm;
                right: .8mm;
                height: 8.2mm;
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                overflow: hidden;
            }
            .barcode-left {
                position: absolute;
                z-index: 2;
                top: 9.4mm;
                left: .8mm;
                right: <?php echo (etiquetaValor('etiqueta_mostrar_precio', '1') === '1' || etiquetaValor('etiqueta_mostrar_talla', '1') === '1') ? '14.2mm' : '.8mm'; ?>;
                bottom: 9.2mm;
                overflow: hidden;
                font-size: 1.85mm;
                line-height: 2.25mm;
            }
            .barcode-right {
                position: absolute;
                z-index: 5;
                top: 8.8mm;
                right: .8mm;
                width: 12.5mm;
                height: 10.4mm;
                text-align: right;
                overflow: visible;
                background: #fff;
            }
            .barcode-dato {
                position: absolute;
                left: 0;
                right: 0;
                height: 4.9mm;
                overflow: visible;
                background: #fff;
            }
            .barcode-precio { top: 0; }
            .barcode-talla { top: 5mm; }
            .barcode-dato-valor {
                display: block;
                height: 3.4mm;
                font-size: 2.8mm !important;
                line-height: 3.3mm;
                text-align: center;
                width: 100%;
                border: .1mm solid #000;
                box-sizing: border-box;
                color: #000 !important;
                background: #fff !important;
                white-space: nowrap;
                overflow: hidden;
            }
            .barcode-dato-titulo {
                display: block;
                height: 1.6mm;
                font-size: 1.45mm !important;
                line-height: 1.6mm;
                margin: 0;
                text-align: right;
                color: #000 !important;
                white-space: nowrap;
            }
            .barcode-right .valor-talla {
                display: flex !important;
                align-items: center;
                justify-content: center;
                font-size: 2.15mm !important;
                line-height: 1 !important;
                font-weight: 700;
                padding: 0 .15mm;
                letter-spacing: -.12mm;
            }
            .barcode-image {
                text-align: center;
                position: absolute;
                z-index: 3;
                left: 1.5mm;
                right: 1.5mm;
                bottom: .6mm;
                height: 8.1mm;
                overflow: hidden;
                background: #fff;
            }
            .barcode-image svg { display: block; width: 100% !important; height: 100% !important; max-width: 100%; }
            .barcode-logo {
                flex: 0 1 46%;
                height: 8mm;
                min-width: 0;
            }
            .barcode-logo img {
                display: block;
                width: 100%;
                height: 100%;
                object-fit: contain;
                object-position: left top;
            }
            .barcode-business {
                flex: 0 1 52%;
                min-width: 0;
                text-align: right;
                font-size: 1.55mm;
                line-height: 1.9mm;
            }
            #barcode .direccion {
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
            }
            .barcode-box .fecha { white-space: nowrap; }
            .barcode-row {
                display: flex;
                min-width: 0;
                height: 2.25mm;
                overflow: hidden;
            }
            .barcode-row .negro {
                flex: 0 0 12mm;
                box-sizing: border-box;
                white-space: nowrap;
            }
            .barcode-row .valor {
                flex: 1 1 auto;
                min-width: 0;
                padding-left: .6mm;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
            }
            @media print { 
                body {
                }
            }
    </style>
    
    <link rel="stylesheet" href="/resources/css/etiqueta-moderna.css?v=20261006-10">
</head>
<body>

<div id="app">
    <?php if (isset($_GET['idProducto'])) : ?>
        <?php $cantidad = isset($_GET['cantidad']) ? $_GET['cantidad'] : 10 ?>
        <codigo-barra-lista id-producto="<?php echo $_GET['idProducto']?>" cantidad="<?php echo $cantidad ?>"></codigo-barra-lista>            
    <?php elseif (isset($_GET['hash'])) : ?>
        <codigo-barra-lista hash-movimiento="<?php echo $_GET['hash']?>"></codigo-barra-lista>
    <?php elseif (isset($_GET['array'])) : ?>
        <codigo-barra-lista arreglo-pro="<?php echo $_GET['array']?>"></codigo-barra-lista>
    <?php endif; ?>
</div>

<script>
window.ETIQUETA_CONFIG = <?php echo json_encode(array(
  'logo' => '/' . ltrim($etiquetaLogo, '/'),
  'empresa' => empresaConfigValor($etiquetaConfig, 'RAZONSOCIAL', 'NEUROSYSTEM'),
  'direccion' => $etiquetaDireccion,
  'mostrarColor' => etiquetaValor('etiqueta_mostrar_color', '1') === '1',
  'mostrarGenero' => etiquetaValor('etiqueta_mostrar_genero', '1') === '1',
  'mostrarBotapie' => etiquetaValor('etiqueta_mostrar_botapie', '1') === '1',
  'mostrarPrecio' => etiquetaValor('etiqueta_mostrar_precio', '1') === '1',
  'mostrarTalla' => etiquetaValor('etiqueta_mostrar_talla', '1') === '1'
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>


<script src="https://cdn.jsdelivr.net/npm/@xkeshi/vue-barcode@0.2.0/dist/vue-barcode.min.js"></script>
<script type="module">
    import CodigoBarraLista from '../components/CodigoBarraLista.js?v=20261006-10';
    
    Vue.component('barcode', VueBarcode);

    new Vue({
      el: '#app',
      components: {
        CodigoBarraLista
      }
    });
</script>
</body>
</html>
