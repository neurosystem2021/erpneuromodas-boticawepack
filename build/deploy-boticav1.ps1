$ErrorActionPreference = 'Stop'

$root = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$backendRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1'))
$sourceRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\dist'))
$targetRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\nuevo'))
$expectedSource = [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticawebpack\dist')
$expectedTarget = [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\nuevo')

if ($sourceRoot -ne $expectedSource -or $targetRoot -ne $expectedTarget) {
    throw "Rutas de despliegue inesperadas. Origen: $sourceRoot Destino: $targetRoot"
}
if (-not (Test-Path -LiteralPath (Join-Path $sourceRoot 'index.html'))) {
    throw 'No existe una compilacion valida en dist. Ejecute npm run build primero.'
}
if (-not (Test-Path -LiteralPath $targetRoot)) {
    New-Item -ItemType Directory -Path $targetRoot | Out-Null
}

Copy-Item -LiteralPath (Join-Path $sourceRoot 'index.html') -Destination (Join-Path $targetRoot 'index.html') -Force
Copy-Item -Path (Join-Path $sourceRoot 'static\*') -Destination (Join-Path $targetRoot 'static') -Recurse -Force

$serverPrintSource = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\server\imprimir'))
$serverPrintTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\imprimir'))
if ($serverPrintTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\imprimir')) {
    throw "Ruta de impresion inesperada: $serverPrintTarget"
}
Copy-Item -LiteralPath (Join-Path $serverPrintSource 'usb.php') -Destination (Join-Path $serverPrintTarget 'usb.php') -Force
Copy-Item -LiteralPath (Join-Path $serverPrintSource 'usb-print.ps1') -Destination (Join-Path $serverPrintTarget 'usb-print.ps1') -Force
Copy-Item -LiteralPath (Join-Path $serverPrintSource 'ticket-raster.php') -Destination (Join-Path $serverPrintTarget 'ticket-raster.php') -Force
Copy-Item -LiteralPath (Join-Path $serverPrintSource 'formatear-productos.php') -Destination (Join-Path $serverPrintTarget 'formatear-productos.php') -Force

# Las plantillas HTML de boleta y factura se eligen desde imprimir/index.php.
# Se instala una sola llamada comun para conservar intacta su logica existente.
$printIndexTarget = Join-Path $serverPrintTarget 'index.php'
$utf8WithoutBom = New-Object System.Text.UTF8Encoding($false)

# Amplia el logo en la vista imprimible de boletas y facturas electronicas
# sin alterar sus datos, productos, totales ni metodos de pago.
$electronicReceiptTarget = Join-Path $serverPrintTarget 'comprobanteelectronico.php'
$electronicReceiptContent = [System.IO.File]::ReadAllText($electronicReceiptTarget, $utf8WithoutBom)
$electronicReceiptContent = $electronicReceiptContent.Replace(
    'max-width:42mm;max-height:22mm',
    'max-width:58mm;max-height:30mm'
)
$electronicReceiptContent = $electronicReceiptContent.Replace(
    '.brand img{display:block;width:auto;max-width:58mm;max-height:30mm;margin:0 auto 2mm;object-fit:contain}',
    '.brand img{display:block;width:68mm;height:28mm;max-width:100%;margin:0 auto 2mm;object-fit:cover;object-position:center 49%}'
)
$electronicReceiptContent = $electronicReceiptContent.Replace(
    '.brand img{display:block;width:68mm;height:28mm;max-width:100%;margin:0 auto 2mm;object-fit:cover;object-position:center 49%}',
    '.brand img{display:block;width:68mm;height:28mm;max-width:100%;margin:0 auto 2mm;object-fit:cover;object-position:center 49%;filter:grayscale(1) brightness(.57) contrast(8)}'
)
$electronicReceiptContent = $electronicReceiptContent.Replace(
    'filter:grayscale(1) contrast(2.8) brightness(.48)',
    'filter:grayscale(1) brightness(.57) contrast(8)'
)
[System.IO.File]::WriteAllText($electronicReceiptTarget, $electronicReceiptContent, $utf8WithoutBom)

$ticketReceiptTarget = Join-Path $serverPrintTarget 'ticket.php'
$ticketReceiptContent = [System.IO.File]::ReadAllText($ticketReceiptTarget, $utf8WithoutBom)
$ticketReceiptContent = $ticketReceiptContent.Replace(
    'style="max-width:100%; width: 44mm"',
    'style="display:block; max-width:100%; width:68mm; height:28mm; object-fit:cover; object-position:center 49%; filter:grayscale(1) brightness(.57) contrast(8); margin:0 auto"'
)
$ticketReceiptContent = $ticketReceiptContent.Replace(
    'style="display:block; max-width:100%; width:58mm; max-height:30mm; object-fit:contain; margin:0 auto"',
    'style="display:block; max-width:100%; width:68mm; height:28mm; object-fit:cover; object-position:center 49%; filter:grayscale(1) brightness(.57) contrast(8); margin:0 auto"'
)
$ticketReceiptContent = $ticketReceiptContent.Replace(
    'style="display:block; max-width:100%; width:68mm; height:28mm; object-fit:cover; object-position:center 49%; margin:0 auto"',
    'style="display:block; max-width:100%; width:68mm; height:28mm; object-fit:cover; object-position:center 49%; filter:grayscale(1) brightness(.57) contrast(8); margin:0 auto"'
)
$ticketReceiptContent = $ticketReceiptContent.Replace(
    'filter:grayscale(1) contrast(2.8) brightness(.48)',
    'filter:grayscale(1) brightness(.57) contrast(8)'
)
[System.IO.File]::WriteAllText($ticketReceiptTarget, $ticketReceiptContent, $utf8WithoutBom)

# Etiquetas de productos: composicion termica compacta con logo, empresa,
# nombre completo en negrita, barras amplias y codigo legible al pie.
$labelSource = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\server\etiquetas'))
$labelViewTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\views\codigoBarra.php'))
$labelComponentTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\components\CodigoBarra.js'))
$labelListTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\components\CodigoBarraLista.js'))
$labelCssTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\resources\css\etiqueta-moderna.css'))
$labelLogoTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\resources\images\eden-company-monochrome.png'))
if ($labelViewTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\views\codigoBarra.php') -or
    $labelComponentTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\components\CodigoBarra.js') -or
    $labelListTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\components\CodigoBarraLista.js') -or
    $labelCssTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\resources\css\etiqueta-moderna.css') -or
    $labelLogoTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\resources\images\eden-company-monochrome.png')) {
    throw 'Rutas inesperadas para las etiquetas de productos.'
}

Copy-Item -LiteralPath (Join-Path $labelSource 'CodigoBarra.js') -Destination $labelComponentTarget -Force
Copy-Item -LiteralPath (Join-Path $labelSource 'etiqueta-moderna.css') -Destination $labelCssTarget -Force
Copy-Item -LiteralPath (Join-Path $labelSource 'eden-company-monochrome.png') -Destination $labelLogoTarget -Force

$labelViewContent = [System.IO.File]::ReadAllText($labelViewTarget, $utf8WithoutBom)
if (-not $labelViewContent.Contains('/resources/css/etiqueta-moderna.css')) {
    $labelViewContent = $labelViewContent.Replace(
        '</head>',
        '    <link rel="stylesheet" href="/resources/css/etiqueta-moderna.css?v=20261006-10">' + "`r`n" + '</head>'
    )
}
$labelViewContent = [regex]::Replace(
    $labelViewContent,
    '/resources/css/etiqueta-moderna\.css\?v=[^"'']+',
    '/resources/css/etiqueta-moderna.css?v=20261006-10'
)
$labelViewContent = [regex]::Replace(
    $labelViewContent,
    '\$etiquetaLogo\s*=\s*etiquetaValor\([^;]+;',
    "`$etiquetaLogo = 'resources/images/eden-company-monochrome.png';",
    1
)
if (-not $labelViewContent.Contains("'empresa' => empresaConfigValor")) {
    $labelViewContent = $labelViewContent.Replace(
        "  'logo' => '/' . ltrim(`$etiquetaLogo, '/'),",
        "  'logo' => '/' . ltrim(`$etiquetaLogo, '/'),`r`n  'empresa' => empresaConfigValor(`$etiquetaConfig, 'RAZONSOCIAL', 'IMPORT COMPANY EDEN E.I.R.L.'),"
    )
}
$labelViewContent = [regex]::Replace(
    $labelViewContent,
    '\.\./components/CodigoBarraLista\.js\?v=[^''"]+',
    '../components/CodigoBarraLista.js?v=20261006-10'
)
[System.IO.File]::WriteAllText($labelViewTarget, $labelViewContent, $utf8WithoutBom)

$labelListContent = [System.IO.File]::ReadAllText($labelListTarget, $utf8WithoutBom)
$labelListContent = [regex]::Replace(
    $labelListContent,
    '\./CodigoBarra\.js\?v=[^''"]+',
    './CodigoBarra.js?v=20261006-10'
)
[System.IO.File]::WriteAllText($labelListTarget, $labelListContent, $utf8WithoutBom)

$labelComponentSourceHash = (Get-FileHash -LiteralPath (Join-Path $labelSource 'CodigoBarra.js') -Algorithm SHA256).Hash
$labelComponentTargetHash = (Get-FileHash -LiteralPath $labelComponentTarget -Algorithm SHA256).Hash
$labelCssSourceHash = (Get-FileHash -LiteralPath (Join-Path $labelSource 'etiqueta-moderna.css') -Algorithm SHA256).Hash
$labelCssTargetHash = (Get-FileHash -LiteralPath $labelCssTarget -Algorithm SHA256).Hash
$labelLogoSourceHash = (Get-FileHash -LiteralPath (Join-Path $labelSource 'eden-company-monochrome.png') -Algorithm SHA256).Hash
$labelLogoTargetHash = (Get-FileHash -LiteralPath $labelLogoTarget -Algorithm SHA256).Hash
if ($labelComponentSourceHash -ne $labelComponentTargetHash -or
    $labelCssSourceHash -ne $labelCssTargetHash -or
    $labelLogoSourceHash -ne $labelLogoTargetHash) {
    throw 'La verificacion de los archivos de etiquetas fallo.'
}

$printIndexContent = [System.IO.File]::ReadAllText($printIndexTarget, $utf8WithoutBom)
$printFormatCall = "include_once(__DIR__ . '/formatear-productos.php');`r`n  `$productos = fn_formatear_productos_impresion(`$productos);"
if (-not $printIndexContent.Contains('fn_formatear_productos_impresion($productos)')) {
    $productsPattern = New-Object System.Text.RegularExpressions.Regex('\$productos = mysqli_fetch_all\(\$resultDet, MYSQLI_ASSOC\);')
    if (-not $productsPattern.IsMatch($printIndexContent)) {
        throw 'No se encontro el punto de preparacion de productos en imprimir/index.php.'
    }
    $printIndexContent = $productsPattern.Replace(
        $printIndexContent,
        '$0' + "`r`n  " + $printFormatCall,
        1
    )
    [System.IO.File]::WriteAllText($printIndexTarget, $printIndexContent, $utf8WithoutBom)
}

# La consulta historica del detalle no incluia el codigo de barras. Ambos
# formatos de impresion consumen esta funcion, por eso se agrega en un solo
# punto sin alterar el registro de la venta ni los metodos de pago.
$bnGeneralTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\clases\BnGeneral.php'))
if ($bnGeneralTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\clases\BnGeneral.php')) {
    throw "Ruta inesperada para BnGeneral.php: $bnGeneralTarget"
}
$bnGeneralContent = [System.IO.File]::ReadAllText($bnGeneralTarget, $utf8WithoutBom)
$detailFunctionPattern = '(?s)function fn_devolverDocVentaDet\(\$criterio, \$orden\).*?return getSQLResultSet\(\$Ssql\);'
$detailFunctionMatch = [regex]::Match($bnGeneralContent, $detailFunctionPattern)
if (-not $detailFunctionMatch.Success) {
    throw 'No se encontro fn_devolverDocVentaDet en BnGeneral.php.'
}
if (-not $detailFunctionMatch.Value.Contains('Gen_Producto.CodigoBarra')) {
    $detailFieldPattern = New-Object System.Text.RegularExpressions.Regex('Ve_DocVentaDet\.IdProducto,\r?\nVe_DocVentaDet\.Descuento,')
    if (-not $detailFieldPattern.IsMatch($detailFunctionMatch.Value)) {
        throw 'No se encontro el punto para agregar CodigoBarra al detalle de venta.'
    }
    $updatedDetailFunction = $detailFieldPattern.Replace(
        $detailFunctionMatch.Value,
        "Ve_DocVentaDet.IdProducto,`r`nGen_Producto.CodigoBarra,`r`nGen_Producto.Codigo,`r`nVe_DocVentaDet.Descuento,",
        1
    )
    $bnGeneralContent = $bnGeneralContent.Substring(0, $detailFunctionMatch.Index) +
        $updatedDetailFunction +
        $bnGeneralContent.Substring($detailFunctionMatch.Index + $detailFunctionMatch.Length)
    [System.IO.File]::WriteAllText($bnGeneralTarget, $bnGeneralContent, $utf8WithoutBom)
}

# Tienda virtual automotriz: identidad, logo configurado y galeria de hasta
# tres imagenes por producto (JPG, PNG, GIF o WEBP).
$storeSource = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\server\tienda'))
$storeTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\tienda'))
$storeApiTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\api\tienda\index.php'))
$storeFrameTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\views\TiendaVirtual.php'))
$storeCssTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\resources\css\neurollantas-store.css'))
$storeJsTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\resources\js\neurollantas-store.js'))
$storeFlyerApiTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\api\tienda-flyers.php'))
$storeFlyerManagerCssTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\resources\css\tienda-flyer-manager.css'))
$storeFlyerManagerJsTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\resources\js\tienda-flyer-manager.js'))
$storeFlyerImageSource = [System.IO.Path]::GetFullPath((Join-Path $storeSource 'flyers'))
$storeFlyerImageTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\resources\images\tienda\flyers'))
if ($storeTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\tienda') -or
    $storeApiTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\api\tienda\index.php') -or
    $storeFrameTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\views\TiendaVirtual.php') -or
    $storeFlyerApiTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\api\tienda-flyers.php') -or
    $storeFlyerImageTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\resources\images\tienda\flyers')) {
    throw 'Rutas inesperadas para la tienda virtual.'
}

Copy-Item -LiteralPath (Join-Path $storeSource 'TiendaVirtual.php') -Destination $storeFrameTarget -Force
Copy-Item -LiteralPath (Join-Path $storeSource 'neurollantas-store.css') -Destination $storeCssTarget -Force
Copy-Item -LiteralPath (Join-Path $storeSource 'neurollantas-store.js') -Destination $storeJsTarget -Force
Copy-Item -LiteralPath (Join-Path $storeSource 'tienda-flyers.php') -Destination $storeFlyerApiTarget -Force
Copy-Item -LiteralPath (Join-Path $storeSource 'tienda-flyer-manager.css') -Destination $storeFlyerManagerCssTarget -Force
Copy-Item -LiteralPath (Join-Path $storeSource 'tienda-flyer-manager.js') -Destination $storeFlyerManagerJsTarget -Force
if (-not (Test-Path -LiteralPath $storeFlyerImageTarget)) {
    New-Item -ItemType Directory -Path $storeFlyerImageTarget | Out-Null
}
Copy-Item -Path (Join-Path $storeFlyerImageSource '*.png') -Destination $storeFlyerImageTarget -Force
$storeFlyerOrderTarget = Join-Path $storeFlyerImageTarget '.order.json'
if (-not (Test-Path -LiteralPath $storeFlyerOrderTarget)) {
    [System.IO.File]::WriteAllText(
        $storeFlyerOrderTarget,
        '["banner-baterias-enerjet.png","banner-aros.png"]',
        $utf8WithoutBom
    )
}
$storeImageTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\resources\images\tienda\producto-sin-imagen.svg'))
if ([System.IO.Path]::GetDirectoryName($storeImageTarget) -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\resources\images\tienda')) {
    throw 'Ruta inesperada para la imagen automotriz de la tienda.'
}
if (-not (Test-Path -LiteralPath ([System.IO.Path]::GetDirectoryName($storeImageTarget)))) {
    New-Item -ItemType Directory -Path ([System.IO.Path]::GetDirectoryName($storeImageTarget)) | Out-Null
}
Copy-Item -LiteralPath (Join-Path $storeSource 'producto-sin-imagen.svg') -Destination $storeImageTarget -Force

$mysqlExecutable = [System.IO.Path]::GetFullPath('C:\xampp\mysql\bin\mysql.exe')
if (-not (Test-Path -LiteralPath $mysqlExecutable)) {
    throw 'No se encontro el cliente MySQL de XAMPP.'
}
& $mysqlExecutable -u root fact_imceden -e "source $((Join-Path $storeSource 'migrate-product-images.sql').Replace('\', '/'))"
if ($LASTEXITCODE -ne 0) {
    throw 'No se pudo preparar la galeria de imagenes de productos.'
}
& $mysqlExecutable -u root fact_imceden -e "source $((Join-Path $storeSource 'migrate-company-automotive.sql').Replace('\', '/'))"
if ($LASTEXITCODE -ne 0) {
    throw 'No se pudo actualizar la actividad automotriz de la empresa.'
}

$storeIndexTarget = Join-Path $storeTarget 'index.php'
$storeIndexContent = [System.IO.File]::ReadAllText($storeIndexTarget, $utf8WithoutBom)
$storeIndexContent = [regex]::Replace(
    $storeIndexContent,
    '/resources/images/tienda/producto-sin-imagen\.svg(?:\?v=[^''"]+)?',
    '/resources/images/tienda/producto-sin-imagen.svg?v=20261001-5'
)
if (-not $storeIndexContent.Contains('$storeLogo =')) {
    $storeBootstrap = @'
<?php
require_once dirname(__DIR__) . '/models/EmpresaConfig.php';
$storeConfig = empresaConfigObtener();
$storeLogo = '/' . ltrim(empresaConfigValor($storeConfig, 'logo_principal', 'resources/images/logo.png'), '/\\');
$storeName = empresaConfigValor($storeConfig, 'RAZONSOCIAL', 'Eden Company');
?>
'@
    $storeIndexContent = $storeBootstrap.TrimEnd() + "`r`n" + $storeIndexContent
}
$storeIndexContent = [regex]::Replace(
    $storeIndexContent,
    '<img class="brand-logo" src="[^"]+" alt="[^"]*">',
    '<img class="brand-logo" src="<?php echo htmlspecialchars($storeLogo, ENT_QUOTES, ''UTF-8''); ?>" alt="<?php echo htmlspecialchars($storeName, ENT_QUOTES, ''UTF-8''); ?>">',
    1
)
$storeIndexContent = [regex]::Replace(
    $storeIndexContent,
    '<h1 id="brand">.*?</h1>',
    '<h1 id="brand"><?php echo htmlspecialchars($storeName, ENT_QUOTES, ''UTF-8''); ?></h1>',
    1
)
$storeIndexContent = [regex]::Replace(
    $storeIndexContent,
    '<p id="tagline">.*?</p>',
    '<p id="tagline">VENTA DE BATERIAS, AROS Y LLANTAS AL POR MAYOR Y MENOR</p>',
    1
)
if (-not $storeIndexContent.Contains('/resources/css/neurollantas-store.css')) {
    $storeIndexContent = $storeIndexContent.Replace(
        '</head>',
        '  <link rel="stylesheet" href="/resources/css/neurollantas-store.css?v=20261002-5">' + "`r`n" + '</head>'
    )
}
$storeIndexContent = [regex]::Replace(
    $storeIndexContent,
    '/resources/css/neurollantas-store\.css\?v=[^"'']+',
    '/resources/css/neurollantas-store.css?v=20261002-5'
)
if (-not $storeIndexContent.Contains('/resources/js/neurollantas-store.js')) {
    $storeIndexContent = $storeIndexContent.Replace(
        '</body>',
        '  <script src="/resources/js/neurollantas-store.js?v=20261002-3"></script>' + "`r`n" + '</body>'
    )
}
$storeIndexContent = [regex]::Replace(
    $storeIndexContent,
    '/resources/js/neurollantas-store\.js\?v=[^"'']+',
    '/resources/js/neurollantas-store.js?v=20261002-3'
)
[System.IO.File]::WriteAllText($storeIndexTarget, $storeIndexContent, $utf8WithoutBom)

$storeApiContent = [System.IO.File]::ReadAllText($storeApiTarget, $utf8WithoutBom)
$storeCompanyPattern = '(?s)    if \(\$action === ''empresa''\) \{.*?\r?\n    \}'
$storeCompanyReplacement = @'
    if ($action === 'empresa') {
        $row = $pdo->query('SELECT RAZONSOCIAL, DESCRIPCION, DIRECCION, TELEFONO, CELULAR, EMAIL FROM Gen_Empresa ORDER BY IDEMPRESA LIMIT 1')->fetch() ?: array();
        $metaStmt = $pdo->query("SELECT MetaKey, MetaValue FROM Gen_EmpresaConfig WHERE IdEmpresa=(SELECT IDEMPRESA FROM Gen_Empresa ORDER BY IDEMPRESA LIMIT 1) AND MetaKey IN ('logo_principal','nombre_comercial','actividad_comercial')");
        foreach ($metaStmt->fetchAll() as $metaRow) $row[$metaRow['MetaKey']] = $metaRow['MetaValue'];
        $row['DESCRIPCION'] = 'VENTA DE BATERIAS, AROS Y LLANTAS AL POR MAYOR Y MENOR';
        $row['logo_principal'] = isset($row['logo_principal']) && trim((string) $row['logo_principal']) !== '' ? '/' . ltrim(str_replace('\\', '/', $row['logo_principal']), '/') : '/resources/images/logo.png';
        echo json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); exit;
    }
'@
if (-not [regex]::IsMatch($storeApiContent, $storeCompanyPattern)) {
    throw 'No se encontro la respuesta de empresa en la API de tienda.'
}
$storeApiContent = [regex]::Replace($storeApiContent, $storeCompanyPattern, $storeCompanyReplacement.TrimEnd(), 1)
$storeFiltersPattern = '(?s)    if \(\$action === ''filtros''\) \{.*?\r?\n    \}(?=\r?\n    if \(\$action === ''categorias''\))'
$storeFiltersReplacement = @'
    if ($action === 'filtros') {
        $categorias = $pdo->query("SELECT c.IdProductoCategoria id, TRIM(c.ProductoCategoria) nombre, COUNT(*) cantidad FROM Gen_ProductoCategoria c JOIN Gen_Producto p ON p.IdProductoCategoria=c.IdProductoCategoria AND p.Anulado=0 WHERE TRIM(COALESCE(c.ProductoCategoria,''))<>'' GROUP BY c.IdProductoCategoria,c.ProductoCategoria ORDER BY c.ProductoCategoria")->fetchAll();
        $marcas = $pdo->query("SELECT m.IdProductoMarca id, TRIM(m.ProductoMarca) nombre, COUNT(*) cantidad FROM Gen_ProductoMarca m JOIN Gen_Producto p ON p.IdProductoMarca=m.IdProductoMarca AND p.Anulado=0 WHERE TRIM(COALESCE(m.ProductoMarca,''))<>'' GROUP BY m.IdProductoMarca,m.ProductoMarca ORDER BY m.ProductoMarca")->fetchAll();
        $modelos = $pdo->query("SELECT TRIM(p.ProductoModelo) nombre, COUNT(*) cantidad FROM Gen_Producto p WHERE p.Anulado=0 AND TRIM(COALESCE(p.ProductoModelo,''))<>'' GROUP BY TRIM(p.ProductoModelo) ORDER BY TRIM(p.ProductoModelo)")->fetchAll();
        echo json_encode(array('categorias'=>$categorias,'marcas'=>$marcas,'modelos'=>$modelos), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); exit;
    }
'@
if ([regex]::IsMatch($storeApiContent, $storeFiltersPattern)) {
    $storeApiContent = [regex]::Replace($storeApiContent, $storeFiltersPattern, $storeFiltersReplacement.TrimEnd(), 1)
} else {
    $storeCategoryMarker = '    if ($action === ''categorias'') {'
    if (-not $storeApiContent.Contains($storeCategoryMarker)) {
        throw 'No se encontro el punto para agregar los filtros de la tienda.'
    }
    $storeApiContent = $storeApiContent.Replace($storeCategoryMarker, $storeFiltersReplacement.TrimEnd() + "`r`n" + $storeCategoryMarker)
}
[System.IO.File]::WriteAllText($storeApiTarget, $storeApiContent, $utf8WithoutBom)

$productImportSource = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\server\productos'))
$productImportTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\api'))
if ($productImportTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\api')) {
    throw "Ruta de importacion inesperada: $productImportTarget"
}
Copy-Item -LiteralPath (Join-Path $productImportSource 'productos-importar.php') -Destination (Join-Path $productImportTarget 'productos-importar.php') -Force
Copy-Item -LiteralPath (Join-Path $productImportSource 'productos-importador.php') -Destination (Join-Path $productImportTarget 'productos-importador.php') -Force

# Ingreso inicial de inventario desde Excel. El endpoint valida el perfil SUPERADMIN y
# registra cabecera, detalle y stock como un solo movimiento identificado.
$initialInventorySource = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\server\inventario'))
$initialInventoryTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\api'))
if ($initialInventoryTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\api')) {
    throw "Ruta de inventario inicial inesperada: $initialInventoryTarget"
}
Copy-Item -LiteralPath (Join-Path $initialInventorySource 'inventario-inicial-importar.php') -Destination (Join-Path $initialInventoryTarget 'inventario-inicial-importar.php') -Force
Copy-Item -LiteralPath (Join-Path $initialInventorySource 'inventario-inicial-importador.php') -Destination (Join-Path $initialInventoryTarget 'inventario-inicial-importador.php') -Force

# KPI automotriz: elimina atributos propios de ropa y unifica el tablero con
# la identidad verde petroleo de baterias, aros y llantas.
$kpiDashboardTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\views\KpiDashboard.php'))
$kpiApiTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\api\kpi.php'))
if ($kpiDashboardTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\views\KpiDashboard.php') -or
    $kpiApiTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\api\kpi.php')) {
    throw 'Rutas inesperadas para el tablero KPI.'
}

$kpiDashboardContent = [System.IO.File]::ReadAllText($kpiDashboardTarget, $utf8WithoutBom)
$kpiTheme = @'
<style id="eden-kpi-petrol-theme">
:root{--petrol:#075247;--petrol-dark:#043b35;--petrol-light:#0a806d}
.kpi-app{background:radial-gradient(circle at 10% 0,#dff5ef,transparent 28%),linear-gradient(135deg,#f4fbf9,#fff 52%,#eaf7f3)!important}
.kpi-hero{background:linear-gradient(110deg,var(--petrol-dark),var(--petrol) 55%,var(--petrol-light))!important;box-shadow:0 18px 50px rgba(7,82,71,.22)!important}
.kpi-btn-light{color:var(--petrol)!important}.kpi-btn-green,.kpi-filter-action,.kpi-tab.active{color:#fff!important;background:linear-gradient(135deg,var(--petrol-dark),var(--petrol-light))!important}
.kpi-filters{grid-template-columns:repeat(7,minmax(120px,1fr))!important;border-color:#cce4de!important;box-shadow:0 9px 30px rgba(7,82,71,.09)!important}.kpi-field label{color:#35685f!important}.kpi-field input,.kpi-field select{border-color:#b9d9d1!important}
.kpi-tabs{box-shadow:0 8px 24px rgba(7,82,71,.11)!important}.kpi-tab{color:#285d54!important;background:#e8f5f1!important}.kpi-tab.active{color:#fff!important}
.kpi-card,.kpi-box{border-color:#cce4de!important;box-shadow:0 10px 28px rgba(7,82,71,.09)!important}.kpi-card:before{background:linear-gradient(var(--petrol-dark),var(--petrol-light))!important}.kpi-card i,.kpi-box-head i{color:var(--petrol)!important}.kpi-box-head{border-bottom-color:#d9ebe6!important}
.kpi-bar i,.kpi-track i,.kpi-progress i{background:linear-gradient(90deg,var(--petrol),#11a18a)!important}.kpi-track,.kpi-progress{background:#dfeeea!important}
.kpi-table th{background:var(--petrol)!important}.kpi-table td{border-bottom-color:#dcebe7!important}.kpi-table tr:nth-child(even){background:#f3faf8!important}.kpi-table tr:hover{background:#e5f5f0!important}.is-info{color:#075247!important;background:#dff3ed!important}
@media(max-width:1200px){.kpi-filters{grid-template-columns:repeat(4,1fr)!important}}@media(max-width:760px){.kpi-filters{grid-template-columns:repeat(2,1fr)!important}}
</style>
'@
if ([regex]::IsMatch($kpiDashboardContent, '(?s)<style id="eden-kpi-petrol-theme">.*?</style>')) {
    $kpiDashboardContent = [regex]::Replace($kpiDashboardContent, '(?s)<style id="eden-kpi-petrol-theme">.*?</style>', $kpiTheme.TrimEnd(), 1)
} else {
    $kpiDashboardContent = $kpiDashboardContent.Replace('</head>', $kpiTheme.TrimEnd() + "`r`n</head>")
}
$kpiDashboardContent = [regex]::Replace($kpiDashboardContent, '<small>NEUROMODAS[^<]*</small>', '<small>EDEN COMPANY &middot; INTELIGENCIA AUTOMOTRIZ</small>', 1)
$kpiDashboardContent = $kpiDashboardContent.Replace('<div class="kpi-field"><label>Talla</label><input name="talla" placeholder="Todas"></div>', '')
$kpiProductPanel = @'
  <section class="kpi-panel" data-panel="productos"><div class="kpi-grid"><article class="kpi-box"><header class="kpi-box-head"><h2>Ventas por categor&iacute;a</h2></header><div class="kpi-horizontal" id="categoryChart"></div></article><article class="kpi-box"><header class="kpi-box-head"><h2>Ventas por marca</h2></header><div class="kpi-horizontal" id="brandChart"></div></article><article class="kpi-box wide"><header class="kpi-box-head"><h2>Ventas por modelo</h2></header><div class="kpi-horizontal" id="modelChart"></div></article><article class="kpi-box wide"><header class="kpi-box-head"><h2>Productos por categor&iacute;a, marca y modelo</h2><button class="kpi-btn kpi-btn-green" data-export="productos">Excel</button></header><div class="kpi-table-wrap" id="productTable"></div></article><article class="kpi-box wide"><header class="kpi-box-head"><h2>Productos menos vendidos / sin movimiento</h2></header><div class="kpi-table-wrap" id="lowProductTable"></div></article></div></section>
'@
$kpiDashboardContent = [regex]::Replace($kpiDashboardContent, '(?s)\s*<section class="kpi-panel" data-panel="productos">.*?</section>', "`r`n" + $kpiProductPanel.TrimEnd(), 1)
$kpiDashboardContent = [regex]::Replace($kpiDashboardContent, "(?m)^\$\('#categoryChart'\).*?$", "`$('#categoryChart').innerHTML=bars(state.dimensions.categorias);`$('#brandChart').innerHTML=bars(state.dimensions.marcas);`$('#modelChart').innerHTML=bars(state.dimensions.modelos);")
$kpiDashboardContent = [regex]::Replace($kpiDashboardContent, "(?m)^\$\('#productTable'\).*?$", "`$('#productTable').innerHTML=table(state.top,[['rank','#',(r,i)=>i+1],['codigo','Codigo'],['producto','Producto'],['categoria','Categoria'],['marca','Marca'],['modelo','Modelo'],['unidades','Unidades',r=>num(r.unidades)],['venta','Venta',r=>money(r.venta)],['descuento','Descuento',r=>money(r.descuento)],['costo','Costo',r=>financial(r.costo)],['utilidad','Utilidad',r=>financial(r.utilidad)],['margen','Margen',r=>num(r.margen)+'%']]);")
$kpiDashboardContent = [regex]::Replace($kpiDashboardContent, "(?m)^\$\('#lowProductTable'\).*?$", "`$('#lowProductTable').innerHTML=table(state.low_products,[['codigo','Codigo'],['producto','Producto'],['categoria','Categoria'],['marca','Marca'],['modelo','Modelo'],['stock','Stock',r=>num(r.stock)],['unidades_periodo','Vendidos',r=>num(r.unidades_periodo)],['ultima_venta','Ultima venta'],['dias_sin_venta','Dias sin movimiento',r=>r.dias_sin_venta===null?'Nunca':num(r.dias_sin_venta)],['valor','Valor stock',r=>financial(r.valor)],['rotacion','Rotacion',r=>badge(r.rotacion)]]);")
$kpiDashboardContent = [regex]::Replace($kpiDashboardContent, "(?m)^\$\('#inventoryTable'\).*?$", "`$('#inventoryTable').innerHTML=table(state.inventory,[['codigo','Codigo'],['producto','Producto'],['categoria','Categoria'],['marca','Marca'],['modelo','Modelo'],['almacen','Almacen'],['stock','Stock',r=>num(r.stock)],['stock_minimo','Minimo',r=>num(r.stock_minimo)],['estado','Estado',r=>badge(r.estado)],['ultima_venta','Ultima venta'],['dias_sin_venta','Dias sin venta',r=>r.dias_sin_venta===null?'Nunca':num(r.dias_sin_venta)],['valor','Valor',r=>financial(r.valor)],['sell_through','Sell through',r=>num(r.sell_through)+'%'],['rotacion','Rotacion',r=>badge(r.rotacion)]]);")
$kpiDashboardContent = $kpiDashboardContent.Replace("card('shopping-bag','Prendas'", "card('shopping-bag','Unidades'")
$kpiDashboardContent = $kpiDashboardContent.Replace("['unidades','Prendas'", "['unidades','Unidades'")
[System.IO.File]::WriteAllText($kpiDashboardTarget, $kpiDashboardContent, $utf8WithoutBom)

$kpiApiContent = [System.IO.File]::ReadAllText($kpiApiTarget, $utf8WithoutBom)
$kpiApiContent = [regex]::Replace(
    $kpiApiContent,
    "COALESCE\(marca\.ProductoMarca,'Sin marca'\) marca,COALESCE\(talla\.ProductoTalla,'Sin talla'\) talla,COALESCE\(p\.Color,'Sin color'\) color,",
    "COALESCE(marca.ProductoMarca,'Sin marca') marca,COALESCE(NULLIF(p.ProductoModelo,''),'Sin modelo') modelo,",
    1
)
$kpiApiContent = [regex]::Replace(
    $kpiApiContent,
    "COALESCE\(marca\.ProductoMarca,'Sin marca'\) marca,COALESCE\(talla\.ProductoTalla,'Sin talla'\) talla,COALESCE\(NULLIF\(p\.Color,''\),'Sin color'\) color,",
    "COALESCE(marca.ProductoMarca,'Sin marca') marca,COALESCE(NULLIF(p.ProductoModelo,''),'Sin modelo') modelo,",
    1
)
$kpiApiContent = [regex]::Replace(
    $kpiApiContent,
    '(?m)^\s*foreach \(array\(''categorias''.*?\) as \$name=>\$def\) \{\r?$',
    '    foreach (array(''categorias''=>array("COALESCE(cat.ProductoCategoria,''Sin categoria'')",''categoria''),''marcas''=>array("COALESCE(marca.ProductoMarca,''Sin marca'')",''marca''),''modelos''=>array("COALESCE(NULLIF(p.ProductoModelo,''''),''Sin modelo'')",''modelo'')) as $name=>$def) {',
    1
)
$kpiApiContent = [regex]::Replace(
    $kpiApiContent,
    '(?m)^(\s*''seller'' => \$seller),\r?\n\s*''size'' => .*?,\r?\n\s*''color'' => .*?\r?$',
    '$1',
    1
)
$kpiApiContent = [regex]::Replace($kpiApiContent, '(?m)^\s*if \(\$f\[''size''\].*?\r?\n', '')
$kpiApiContent = [regex]::Replace($kpiApiContent, '(?m)^\s*if \(\$f\[''color''\].*?\r?\n', '')
$kpiApiContent = [regex]::Replace($kpiApiContent, '(?m)^\s*LEFT JOIN gen_productotalla talla ON talla\.IdProductoTalla=p\.IdProductoTalla\r?\n', '')
[System.IO.File]::WriteAllText($kpiApiTarget, $kpiApiContent, $utf8WithoutBom)

if ($kpiDashboardContent.Contains('Ventas por talla') -or $kpiDashboardContent.Contains("['talla','Talla']") -or
    $kpiDashboardContent.Contains('Ventas por color') -or $kpiDashboardContent.Contains("['color','Color']")) {
    throw 'No se pudieron retirar los atributos de ropa del tablero KPI.'
}
if ($kpiApiContent.Contains("'tallas'=>array") -or $kpiApiContent.Contains("'colores'=>array") -or
    $kpiApiContent.Contains('ProductoTalla') -or $kpiApiContent.Contains('p.Color')) {
    throw 'No se pudieron adaptar las dimensiones de la API KPI.'
}

$sourceHash = (Get-FileHash -LiteralPath (Join-Path $sourceRoot 'index.html') -Algorithm SHA256).Hash
$targetHash = (Get-FileHash -LiteralPath (Join-Path $targetRoot 'index.html') -Algorithm SHA256).Hash
if ($sourceHash -ne $targetHash) { throw 'La verificacion del despliegue fallo.' }

$importSourceHash = (Get-FileHash -LiteralPath (Join-Path $productImportSource 'productos-importar.php') -Algorithm SHA256).Hash
$importTargetHash = (Get-FileHash -LiteralPath (Join-Path $productImportTarget 'productos-importar.php') -Algorithm SHA256).Hash
if ($importSourceHash -ne $importTargetHash) { throw 'La verificacion del importador de productos fallo.' }

$initialInventorySourceHash = (Get-FileHash -LiteralPath (Join-Path $initialInventorySource 'inventario-inicial-importar.php') -Algorithm SHA256).Hash
$initialInventoryTargetHash = (Get-FileHash -LiteralPath (Join-Path $initialInventoryTarget 'inventario-inicial-importar.php') -Algorithm SHA256).Hash
$initialInventoryHelperSourceHash = (Get-FileHash -LiteralPath (Join-Path $initialInventorySource 'inventario-inicial-importador.php') -Algorithm SHA256).Hash
$initialInventoryHelperTargetHash = (Get-FileHash -LiteralPath (Join-Path $initialInventoryTarget 'inventario-inicial-importador.php') -Algorithm SHA256).Hash
if ($initialInventorySourceHash -ne $initialInventoryTargetHash -or $initialInventoryHelperSourceHash -ne $initialInventoryHelperTargetHash) {
    throw 'La verificacion del ingreso inicial de inventario fallo.'
}

# Gestión de clientes y proveedores: tabla amplia y acciones de Excel
# disponibles exclusivamente para el usuario SUPERADMIN.
$contactsSource = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\server\contactos\GestionContactos.php'))
$contactsTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\views\GestionContactos.php'))
if ($contactsTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\views\GestionContactos.php')) {
    throw "Ruta inesperada para GestionContactos.php: $contactsTarget"
}
Copy-Item -LiteralPath $contactsSource -Destination $contactsTarget -Force
$contactsSourceHash = (Get-FileHash -LiteralPath $contactsSource -Algorithm SHA256).Hash
$contactsTargetHash = (Get-FileHash -LiteralPath $contactsTarget -Algorithm SHA256).Hash
if ($contactsSourceHash -ne $contactsTargetHash) { throw 'La verificacion de GestionContactos.php fallo.' }

$loginBrandSource = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\server\login'))
$loginCssTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\resources\css'))
$loginImageTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\resources\images'))
$loginIndexTarget = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\boticav1\index.php'))
if ($loginCssTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\resources\css') -or
    $loginImageTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\resources\images') -or
    $loginIndexTarget -ne [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\index.php')) {
    throw 'Rutas inesperadas para la identidad de acceso NeuroLlantas.'
}

Copy-Item -LiteralPath (Join-Path $loginBrandSource 'neurollantas-splash.css') -Destination (Join-Path $loginCssTarget 'neurollantas-splash.css') -Force
Copy-Item -LiteralPath (Join-Path $loginBrandSource 'neurollantas-login.css') -Destination (Join-Path $loginCssTarget 'neurollantas-login.css') -Force
Copy-Item -LiteralPath (Join-Path $loginBrandSource 'neurollantas-splash-transparent.png') -Destination (Join-Path $loginImageTarget 'neurollantas-splash-transparent.png') -Force

$loginContent = [System.IO.File]::ReadAllText($loginIndexTarget, $utf8WithoutBom)
$loginContent = [regex]::Replace($loginContent, '<title>ERP (?:NEUROMODAS|NEUROLLANTAS)</title>', '<title>ERP NEUROLLANTAS</title>')
$loginContent = [regex]::Replace($loginContent, '<link rel="icon" type="image/(?:png|svg\+xml)" href="resources/images/(?:neuromodas\.png|neurollantas-splash(?:-transparent)?\.(?:svg|png))">', '<link rel="icon" type="image/png" href="resources/images/neurollantas-splash-transparent.png">')
$loginContent = [regex]::Replace($loginContent, 'aria-label="Cargando (?:NeuroModas|NeuroLlantas)"', 'aria-label="Cargando NeuroLlantas"')
$loginContent = [regex]::Replace($loginContent, '<img class="splash-logo" src="resources/images/[^"]+" alt="[^"]+">', '<img class="splash-logo" src="resources/images/neurollantas-splash-transparent.png" alt="NeuroLlantas">')
$loginContent = [regex]::Replace($loginContent, '<p class="splash-name">[^<]*</p>', '<p class="splash-name">NeuroLlantas</p>')
$loginContent = [regex]::Replace($loginContent, '<p class="splash-tagline">[^<]*</p>', '<p class="splash-tagline">Tecnologia para llantas y baterias</p>')
$loginContent = [regex]::Replace($loginContent, '<p class="splash-status" id="splashStatus">[^<]*</p>', '<p class="splash-status" id="splashStatus">Velocidad de carga &middot; 0%</p>')
$loginContent = [regex]::Replace($loginContent, "status\.textContent = progress < 100 \? [^;]+;", "status.textContent = progress < 100 ? 'Velocidad de carga - ' + progress + '%' : 'Velocidad maxima - 100%';")
$loginContent = [regex]::Replace($loginContent, "var minimumDuration = window\.matchMedia\('\(prefers-reduced-motion: reduce\)'\)\.matches \? 100 : \d+;", "var minimumDuration = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 100 : 2000;")

# Mensajes e identidad del acceso automotriz. Las entidades HTML conservan
# correctamente los acentos al ejecutar el despliegue en Windows PowerShell.
$loginContent = [regex]::Replace($loginContent, '<section class="showcase" aria-label="[^"]*">', '<section class="showcase" aria-label="Plataforma de gesti&oacute;n para bater&iacute;as, aros y llantas">')
$loginContent = [regex]::Replace($loginContent, '<a class="brand" href="#" aria-label="[^"]*">', '<a class="brand" href="#" aria-label="ERP NEUROSOFT, inicio">')
$loginContent = [regex]::Replace($loginContent, '<span class="brand-mark">.*?</span>', '<span class="brand-mark">NS</span>')
$loginContent = [regex]::Replace($loginContent, '(?s)(<a class="brand"[^>]*>\s*<span class="brand-mark">.*?</span>\s*)<span>.*?</span>', '$1<span>ERP NEUROSOFT</span>')
$loginContent = [regex]::Replace($loginContent, '<div class="fashion-index" aria-hidden="true">.*?</div>', '<div class="fashion-index" aria-hidden="true"><span>Tecnolog&iacute;a automotriz</span><strong>4X4</strong></div>')
$loginContent = [regex]::Replace($loginContent, '<p class="eyebrow">.*?</p>', '<p class="eyebrow">ERP para bater&iacute;as, aros y llantas</p>')
$loginContent = [regex]::Replace($loginContent, '<h1>.*?</h1>', '<h1>Potencia.<br><em>Control en cada giro.</em></h1>')
$loginContent = [regex]::Replace($loginContent, '<p class="hero-copy">.*?</p>', '<p class="hero-copy">Ventas, inventario, caja y operaci&oacute;n conectados para gestionar bater&iacute;as, aros, llantas y repuestos con m&aacute;xima velocidad.</p>')
$loginContent = [regex]::Replace($loginContent, '(?s)<div class="features" aria-label="[^"]*">.*?</div>', '<div class="features" aria-label="Caracter&iacute;sticas"><span class="feature">Stock por almac&eacute;n</span><span class="feature">Ventas sin demora</span><span class="feature">Control de bater&iacute;as y aros</span></div>')
$loginContent = [regex]::Replace($loginContent, '<div class="photo-note"><span></span>.*?</div>', '<div class="photo-note"><span></span>Inventario listo para cada ruta</div>')
$loginContent = [regex]::Replace($loginContent, '<span class="live-copy"><strong>.*?</strong><span>.*?</span></span>', '<span class="live-copy"><strong>ERP AUTOMOTRIZ</strong><span>Ventas &middot; Stock &middot; Caja sincronizados</span></span>')
$loginContent = [regex]::Replace($loginContent, '<div class="erp-rail" aria-hidden="true">(.*?)<span>[^<]*ERP Intelligence</span></div>', '<div class="erp-rail" aria-hidden="true">$1<span>NeuroLlantas &middot; ERP Automotriz</span></div>')
$loginContent = [regex]::Replace($loginContent, '(?s)(<div class="client-brand-copy">\s*)<strong>.*?</strong>', '$1<strong>ERP NEUROSOFT</strong>')
$loginContent = [regex]::Replace($loginContent, '(?s)(<div class="client-brand-copy">\s*<strong>.*?</strong>\s*)<span>.*?</span>', '$1<span>ERP &middot; Bater&iacute;as, Aros y Llantas</span>')
$loginContent = [regex]::Replace($loginContent, '<p class="login-kicker">.*?</p>', '<p class="login-kicker">Centro de control</p>')
$loginContent = [regex]::Replace($loginContent, '<h2>.*?</h2>', '<h2>Acceso al sistema</h2>')
$loginContent = [regex]::Replace($loginContent, '<p class="login-intro">.*?</p>', '<p class="login-intro">Ingresa tus credenciales para administrar ventas, inventario y caja.</p>')
$loginContent = [regex]::Replace($loginContent, '<div class="trust-row" aria-label="[^"]*">.*?</div>', '<div class="trust-row" aria-label="Sistema r&aacute;pido, seguro y centralizado"><span>R&aacute;pido</span><i></i><span>Seguro</span><i></i><span>Centralizado</span></div>')
if (-not $loginContent.Contains('resources/css/neurollantas-splash.css')) {
    $loginContent = $loginContent.Replace('</head>', '  <link rel="stylesheet" href="resources/css/neurollantas-splash.css?v=20261001">' + [Environment]::NewLine + '</head>')
}
if (-not $loginContent.Contains('resources/css/neurollantas-login.css')) {
    $loginContent = $loginContent.Replace('</head>', '  <link rel="stylesheet" href="resources/css/neurollantas-login.css?v=20261001">' + [Environment]::NewLine + '</head>')
}
$loginContent = [regex]::Replace($loginContent, 'resources/css/neurollantas-splash\.css\?v=[^"]+', 'resources/css/neurollantas-splash.css?v=20261001-7')
$loginContent = [regex]::Replace($loginContent, 'resources/css/neurollantas-login\.css\?v=[^"]+', 'resources/css/neurollantas-login.css?v=20261001-7')

$splashControllerPattern = "(?s)    \(function \(\) \{\r?\n      if \('scrollRestoration' in history\).*?\r?\n    \}\(\)\);(?=\r?\n\r?\n    \(function \(\) \{\r?\n      var form = document\.getElementById\('loginForm'\);)"
$splashController = @'
    (function () {
      if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
      window.scrollTo(0, 0);
      var splash = document.getElementById('splashScreen');
      var bar = document.getElementById('splashProgress');
      var status = document.getElementById('splashStatus');
      var progressBox = splash.querySelector('[role="progressbar"]');
      var minimumDuration = 2000;
      var repeatDelay = 60000;
      var loaded = document.readyState === 'complete';
      var running = false;
      var repeatTimer = null;
      window.addEventListener('load', function () { loaded = true; });

      function scheduleNext() {
        clearTimeout(repeatTimer);
        repeatTimer = setTimeout(runSplash, repeatDelay);
      }

      function runSplash() {
        if (running || document.hidden) {
          scheduleNext();
          return;
        }
        running = true;
        var started = performance.now();
        splash.classList.remove('is-finished');
        splash.setAttribute('aria-hidden', 'false');
        bar.style.width = '0%';
        progressBox.setAttribute('aria-valuenow', '0');
        status.textContent = 'Velocidad de carga - 0%';

        function frame(now) {
          var elapsed = now - started;
          var progress = Math.min(loaded ? 100 : 92, Math.round((elapsed / minimumDuration) * 100));
          if (loaded && elapsed >= minimumDuration) progress = 100;
          bar.style.width = progress + '%';
          progressBox.setAttribute('aria-valuenow', progress);
          status.textContent = progress < 100 ? 'Velocidad de carga - ' + progress + '%' : 'Velocidad maxima - 100%';
          if (progress >= 100) {
            setTimeout(function () {
              splash.classList.add('is-finished');
              splash.setAttribute('aria-hidden', 'true');
            }, 50);
            setTimeout(function () {
              running = false;
              var accessButton = document.getElementById('btnAcceder');
              if (accessButton) accessButton.focus({ preventScroll: true });
              scheduleNext();
            }, 500);
            return;
          }
          requestAnimationFrame(frame);
        }

        requestAnimationFrame(frame);
      }

      setTimeout(function () { loaded = true; }, 1400);
      runSplash();
    }());
'@
$loginContent = [regex]::Replace($loginContent, $splashControllerPattern, $splashController.TrimEnd())
[System.IO.File]::WriteAllText($loginIndexTarget, $loginContent, $utf8WithoutBom)

$loginCssSourceHash = (Get-FileHash -LiteralPath (Join-Path $loginBrandSource 'neurollantas-splash.css') -Algorithm SHA256).Hash
$loginCssTargetHash = (Get-FileHash -LiteralPath (Join-Path $loginCssTarget 'neurollantas-splash.css') -Algorithm SHA256).Hash
$loginThemeSourceHash = (Get-FileHash -LiteralPath (Join-Path $loginBrandSource 'neurollantas-login.css') -Algorithm SHA256).Hash
$loginThemeTargetHash = (Get-FileHash -LiteralPath (Join-Path $loginCssTarget 'neurollantas-login.css') -Algorithm SHA256).Hash
$loginLogoSourceHash = (Get-FileHash -LiteralPath (Join-Path $loginBrandSource 'neurollantas-splash-transparent.png') -Algorithm SHA256).Hash
$loginLogoTargetHash = (Get-FileHash -LiteralPath (Join-Path $loginImageTarget 'neurollantas-splash-transparent.png') -Algorithm SHA256).Hash
if ($loginCssSourceHash -ne $loginCssTargetHash -or
    $loginThemeSourceHash -ne $loginThemeTargetHash -or
    $loginLogoSourceHash -ne $loginLogoTargetHash) {
    throw 'La verificacion de la identidad NeuroLlantas fallo.'
}

$productionDeploySource = Join-Path $root 'server\deploy'
$productionDeployTarget = Join-Path $backendRoot 'build\deploy'
if (-not (Test-Path -LiteralPath $productionDeploySource)) {
    throw "No existe el estandar de despliegue: $productionDeploySource"
}
New-Item -ItemType Directory -Force -Path $productionDeployTarget | Out-Null
Copy-Item -LiteralPath (Join-Path $productionDeploySource 'verify-production.php') -Destination $productionDeployTarget -Force
Copy-Item -LiteralPath (Join-Path $productionDeploySource 'migrate-sale-fields.php') -Destination $productionDeployTarget -Force
Copy-Item -LiteralPath (Join-Path $productionDeploySource 'migrate-cash-close-fields.php') -Destination $productionDeployTarget -Force
Copy-Item -LiteralPath (Join-Path $productionDeploySource 'deploy-production.sh') -Destination $productionDeployTarget -Force
Copy-Item -LiteralPath (Join-Path $productionDeploySource 'install-production.sh') -Destination $productionDeployTarget -Force
Copy-Item -LiteralPath (Join-Path $productionDeploySource 'README.md') -Destination $productionDeployTarget -Force
Copy-Item -LiteralPath (Join-Path $productionDeploySource '.gitattributes') -Destination $productionDeployTarget -Force
$rootHtaccessSource = Join-Path $root 'server\root.htaccess'
if (-not (Test-Path -LiteralPath $rootHtaccessSource)) {
    throw "No existe la configuracion raiz de OpenLiteSpeed: $rootHtaccessSource"
}
Copy-Item -LiteralPath $rootHtaccessSource -Destination (Join-Path $backendRoot '.htaccess') -Force

# El respaldo local del tipo de cambio debe pasar las columnas como arreglo a
# Slim PDO. Con un string genera TypeError y deja el encabezado del POS en 500.
$apiIndexTarget = Join-Path $backendRoot 'api\index.php'
$apiIndexContent = [System.IO.File]::ReadAllText($apiIndexTarget, $utf8WithoutBom)

# Los movimientos pendientes del cierre se identifican por IdCierre IS NULL.
# No deben limitarse con CURDATE(): una caja puede cruzar medianoche y la zona
# horaria de MySQL puede diferir de la aplicacion. Ese filtro hacia que el API
# devolviera arreglos vacios aun cuando los ingresos y egresos seguian abiertos.
$legacyCashMovementUserFilter = @'
        $select .= " AND LOWER(Cb_CajaBanco.UsuarioReg)= '" . $usuario . "' AND DATE(Cb_CajaBanco.FechaDoc)=CURDATE()";
'@
$cashMovementUserFilter = @'
        $select .= " AND LOWER(Cb_CajaBanco.UsuarioReg)= '" . $usuario . "'";
'@
if ($apiIndexContent.Contains($legacyCashMovementUserFilter)) {
    $apiIndexContent = $apiIndexContent.Replace($legacyCashMovementUserFilter, $cashMovementUserFilter)
} elseif (-not $apiIndexContent.Contains($cashMovementUserFilter)) {
    throw 'No se encontro el filtro de usuario de los movimientos del cierre en api/index.php.'
}

# En el POS, un producto sin movimientos en Gen_Producto_Totales debe volver
# igualmente con stock 0. El almacen ya forma parte del LEFT JOIN; repetirlo en
# el WHERE convertia ese LEFT JOIN en INNER JOIN y ocultaba el producto tanto en
# la busqueda por nombre como al recargarlo despues de leer el codigo de barras.
$legacyPointOfSaleStockFilter = @'
            $select .= " AND Gen_Producto_Totales.IdAlmacen=$idAlmacen ";
'@
$pointOfSaleZeroStockMarker = @'
            // El almacen ya se filtra en el LEFT JOIN para incluir productos con stock 0.
'@
if ($apiIndexContent.Contains($legacyPointOfSaleStockFilter)) {
    $apiIndexContent = $apiIndexContent.Replace($legacyPointOfSaleStockFilter, $pointOfSaleZeroStockMarker)
} elseif (-not $apiIndexContent.Contains($pointOfSaleZeroStockMarker)) {
    throw 'No se encontro el filtro de almacen del stock del punto de venta en api/index.php.'
}

$legacyCurrencySelect = "`$this->db->select('TipoCambio')->from('Gen_Moneda')"
$fixedCurrencySelect = "`$this->db->select(array('TipoCambio'))->from('Gen_Moneda')"
if ($apiIndexContent.Contains($legacyCurrencySelect)) {
    $apiIndexContent = $apiIndexContent.Replace($legacyCurrencySelect, $fixedCurrencySelect)
} elseif (-not $apiIndexContent.Contains($fixedCurrencySelect)) {
    throw 'No se encontro la consulta de respaldo del tipo de cambio en api/index.php.'
}

# Observaciones y placa se guardan en la cabecera de la venta y se incluyen en
# todas las consultas que generan boletas, facturas, tickets o envios por WhatsApp.
if (-not $apiIndexContent.Contains('$observacion = trim((string) $request->getParam(''Observacion''))') -and
    -not $apiIndexContent.Contains('$observacion = mb_strtoupper')) {
    $saleFieldAnchor = @'
    $nombreOrganizacionSql = str_replace("'", "''", mb_substr($nombreOrganizacion, 0, 150));
'@
    $saleFieldBlock = @'
    $nombreOrganizacionSql = str_replace("'", "''", mb_substr($nombreOrganizacion, 0, 150));
    $observacion = trim((string) $request->getParam('Observacion'));
    $observacion = mb_substr($observacion, 0, 500, 'UTF-8');
    $placa = trim((string) $request->getParam('Placa'));
    $placa = preg_replace('/[^A-Za-z0-9-]/', '', $placa);
    $placa = mb_substr($placa, 0, 20, 'UTF-8');
'@
    if (-not $apiIndexContent.Contains($saleFieldAnchor)) {
        throw 'No se encontro el bloque de datos de la organizacion en api/index.php.'
    }
    $apiIndexContent = $apiIndexContent.Replace($saleFieldAnchor, $saleFieldBlock)
}

$apiIndexContent = $apiIndexContent.Replace(
    "`$observacion = mb_strtoupper(trim((string) `$request->getParam('Observacion')), 'UTF-8');",
    "`$observacion = trim((string) `$request->getParam('Observacion'));"
)
$apiIndexContent = $apiIndexContent.Replace(
    "`$placa = mb_strtoupper(trim((string) `$request->getParam('Placa')), 'UTF-8');",
    "`$placa = trim((string) `$request->getParam('Placa'));"
)
$apiIndexContent = $apiIndexContent.Replace(
    "`$placa = preg_replace('/[^A-Z0-9-]/', '', `$placa);",
    "`$placa = preg_replace('/[^A-Za-z0-9-]/', '', `$placa);"
)

if (-not $apiIndexContent.Contains('NombreOrganizacion, Observacion, Placa)')) {
    $legacySaleColumns = 'EsOrganizacion, NombreOrganizacion)'
    $saleColumns = 'EsOrganizacion, NombreOrganizacion, Observacion, Placa)'
    if (-not $apiIndexContent.Contains($legacySaleColumns)) {
        throw 'No se encontro la lista de columnas de Ve_DocVenta en api/index.php.'
    }
    $apiIndexContent = $apiIndexContent.Replace($legacySaleColumns, $saleColumns)
}

if (-not $apiIndexContent.Contains('''$nombreOrganizacionSql'', :observacion, :placa)";') -and
    -not $apiIndexContent.Contains('''$nombreOrganizacionSql'', ''$observacionSql'', ''$placaSql'')";')) {
    $legacySaleValues = '''$nombreOrganizacionSql'')";'
    $saleValues = '''$nombreOrganizacionSql'', :observacion, :placa)";'
    if (-not $apiIndexContent.Contains($legacySaleValues)) {
        throw 'No se encontro la lista de valores de Ve_DocVenta en api/index.php.'
    }
    $apiIndexContent = $apiIndexContent.Replace($legacySaleValues, $saleValues)
}

$apiIndexContent = $apiIndexContent.Replace(
    '''$nombreOrganizacionSql'', ''$observacionSql'', ''$placaSql'')";',
    '''$nombreOrganizacionSql'', :observacion, :placa)";'
)

if (-not $apiIndexContent.Contains("`$inserted = `$stmt->execute(array(':observacion' => `$observacion, ':placa' => `$placa));")) {
    $legacySaleExecute = @'
    $stmt = $this->db->prepare($insert);
    $inserted = $stmt->execute();
    $idDocVenta = $this->db->lastInsertId();
'@
    $preparedSaleExecute = @'
    $stmt = $this->db->prepare($insert);
    $inserted = $stmt->execute(array(':observacion' => $observacion, ':placa' => $placa));
    $idDocVenta = $this->db->lastInsertId();
'@
    if (-not $apiIndexContent.Contains($legacySaleExecute)) {
        throw 'No se encontro la ejecucion del INSERT de Ve_DocVenta en api/index.php.'
    }
    $apiIndexContent = $apiIndexContent.Replace($legacySaleExecute, $preparedSaleExecute)
}

$apiIndexContent = [regex]::Replace(
    $apiIndexContent,
    'Ve_DocVenta\.NotaDescMotivo,\r?\n(?!\s*Ve_DocVenta\.Observacion)',
    "Ve_DocVenta.NotaDescMotivo,`r`n        Ve_DocVenta.Observacion, Ve_DocVenta.Placa,`r`n"
)
$apiIndexContent = [regex]::Replace(
    $apiIndexContent,
    'Ve_DocVenta\.Observacion, Ve_DocVenta\.Placa,\s+(?=(?:IFNULL|Ve_DocVenta\.EsCredito))',
    "Ve_DocVenta.Observacion, Ve_DocVenta.Placa,`r`n        "
)
$apiIndexContent = [regex]::Replace(
    $apiIndexContent,
    '"txtVENDEDOR"\s*=>\s*\$docVenta\[''UsuarioReg''\],\s*//VENDEDOR\r?\n(?!\s*"txtOBSERVACION")',
    '"txtVENDEDOR" => $docVenta[''UsuarioReg''], //VENDEDOR' + "`r`n" +
    '        "txtOBSERVACION" => isset($docVenta[''Observacion'']) ? $docVenta[''Observacion''] : '''',' + "`r`n" +
    '        "txtPLACA" => isset($docVenta[''Placa'']) ? $docVenta[''Placa''] : '''',' + "`r`n"
)

# La descripcion del cierre conserva las mayusculas y minusculas ingresadas por
# el usuario, se guarda en Cb_CierreCaja y vuelve en el historial.
if (-not $apiIndexContent.Contains("`$descripcionCierre = trim((string) `$request->getParam('descripcion'));")) {
    $cashCloseRequestAnchor = @'
    $granTotal    = $request->getParam('granTotal') ? $request->getParam('granTotal') : 0;
'@
    $cashCloseRequestBlock = @'
    $granTotal    = $request->getParam('granTotal') ? $request->getParam('granTotal') : 0;
    $descripcionCierre = trim((string) $request->getParam('descripcion'));
    $descripcionCierre = mb_substr($descripcionCierre, 0, 500, 'UTF-8');
'@
    if (-not $apiIndexContent.Contains($cashCloseRequestAnchor)) {
        throw 'No se encontro la lectura de totales del cierre en api/index.php.'
    }
    $apiIndexContent = $apiIndexContent.Replace($cashCloseRequestAnchor, $cashCloseRequestBlock)
}

if (-not $apiIndexContent.Contains('Total, Descripcion)')) {
    $legacyCashCloseInsert = @'
    $insert = "INSERT INTO Cb_CierreCaja (FechaCierre, IdTurno, UsuarioReg, Ingresos, Salidas, Ventas, Total)
        VALUES ('$fechaCierre', '$idTurno', '$usuarioReg', $ingresos, $salidas, $totalVentas, $total)";
    $stmt = $this->db->prepare($insert);
    $inserted = $stmt->execute();
'@
    $cashCloseInsert = @'
    $insert = "INSERT INTO Cb_CierreCaja (FechaCierre, IdTurno, UsuarioReg, Ingresos, Salidas, Ventas, Total, Descripcion)
        VALUES ('$fechaCierre', '$idTurno', '$usuarioReg', $ingresos, $salidas, $totalVentas, $total, :descripcion)";
    $stmt = $this->db->prepare($insert);
    $inserted = $stmt->execute(array(':descripcion' => $descripcionCierre));
'@
    if (-not $apiIndexContent.Contains($legacyCashCloseInsert)) {
        throw 'No se encontro el INSERT de Cb_CierreCaja en api/index.php.'
    }
    $apiIndexContent = $apiIndexContent.Replace($legacyCashCloseInsert, $cashCloseInsert)
}

if (-not $apiIndexContent.Contains('Cb_CierreCaja.Descripcion,')) {
    $legacyCashCloseSelect = 'Cb_CierreCaja.UsuarioReg, Cb_CierreCaja.Ingresos, Cb_CierreCaja.Salidas, Cb_CierreCaja.Ventas, Cb_CierreCaja.Total,'
    $cashCloseSelect = 'Cb_CierreCaja.UsuarioReg, Cb_CierreCaja.Ingresos, Cb_CierreCaja.Salidas, Cb_CierreCaja.Ventas, Cb_CierreCaja.Total, Cb_CierreCaja.Descripcion,'
    if (-not $apiIndexContent.Contains($legacyCashCloseSelect)) {
        throw 'No se encontro el SELECT de Cb_CierreCaja en api/index.php.'
    }
    $apiIndexContent = $apiIndexContent.Replace($legacyCashCloseSelect, $cashCloseSelect)
}

if (-not $apiIndexContent.Contains("IFNULL(Cb_CierreCaja.Descripcion,'') LIKE '%`$filter%'")) {
    $legacyCashCloseFilter = "Cb_CierreCaja.UsuarioReg LIKE '%`$filter%')"
    $cashCloseFilter = "Cb_CierreCaja.UsuarioReg LIKE '%`$filter%' OR IFNULL(Cb_CierreCaja.Descripcion,'') LIKE '%`$filter%')"
    if (-not $apiIndexContent.Contains($legacyCashCloseFilter)) {
        throw 'No se encontro el filtro de Cb_CierreCaja en api/index.php.'
    }
    $apiIndexContent = $apiIndexContent.Replace($legacyCashCloseFilter, $cashCloseFilter)
}
[System.IO.File]::WriteAllText($apiIndexTarget, $apiIndexContent, $utf8WithoutBom)

$bnGeneralSaleContent = [System.IO.File]::ReadAllText($bnGeneralTarget, $utf8WithoutBom)
$bnGeneralSaleContent = [regex]::Replace(
    $bnGeneralSaleContent,
    'Ve_DocVenta\.CampoDireccion,\r?\n(?!\s*Ve_DocVenta\.Observacion)',
    "Ve_DocVenta.CampoDireccion,`r`n                Ve_DocVenta.Observacion,`r`n                Ve_DocVenta.Placa,`r`n"
)
$bnGeneralSaleContent = [regex]::Replace(
    $bnGeneralSaleContent,
    'Ve_DocVenta\.Placa,\s*(?=Ve_DocVenta\.EsCredito)',
    "Ve_DocVenta.Placa,`r`n                "
)
[System.IO.File]::WriteAllText($bnGeneralTarget, $bnGeneralSaleContent, $utf8WithoutBom)

$electronicReceiptContent = [System.IO.File]::ReadAllText($electronicReceiptTarget, $utf8WithoutBom)
if (-not $electronicReceiptContent.Contains('$placa = trim(')) {
    $observationDeclaration = '$observacion = trim((string) (isset($docVenta[''Observacion'']) ? $docVenta[''Observacion''] : ''''));'
    $plateDeclaration = $observationDeclaration + "`r`n" + '$placa = trim((string) (isset($docVenta[''Placa'']) ? $docVenta[''Placa''] : ''''));'
    if (-not $electronicReceiptContent.Contains($observationDeclaration)) {
        throw 'No se encontro la observacion en comprobanteelectronico.php.'
    }
    $electronicReceiptContent = $electronicReceiptContent.Replace($observationDeclaration, $plateDeclaration)
}
if (-not $electronicReceiptContent.Contains('<span class="label">PLACA</span>')) {
    $observationRowPattern = '(?m)^\s*<\?php if \(\$observacion !== ''''\): \?><div class="row"><span class="label">[^<]+</span><span class="value"><\?php echo \$esc\(\$observacion\); \?></span></div><\?php endif; \?>\r?$'
    $observationRowMatch = [regex]::Match($electronicReceiptContent, $observationRowPattern)
    if (-not $observationRowMatch.Success) {
        throw 'No se encontro la fila de observacion en comprobanteelectronico.php.'
    }
    $observationRow = $observationRowMatch.Value.TrimEnd("`r")
    $plateRows = '    <?php if ($placa !== ''''): ?><div class="row"><span class="label">PLACA</span><span class="value"><?php echo $esc($placa); ?></span></div><?php endif; ?>' + "`r`n" + $observationRow
    $electronicReceiptContent = $electronicReceiptContent.Remove($observationRowMatch.Index, $observationRowMatch.Length).Insert($observationRowMatch.Index, $plateRows)
}
[System.IO.File]::WriteAllText($electronicReceiptTarget, $electronicReceiptContent, $utf8WithoutBom)

$ticketPdfTarget = Join-Path $backendRoot 'api\templates\ticket_pdf.php'
$ticketPdfContent = [System.IO.File]::ReadAllText($ticketPdfTarget, $utf8WithoutBom)
$ticketPdfContent = $ticketPdfContent.Replace(
    '.brand-table .logo{display:block;margin:0 auto}',
    '.brand-table .logo{display:block;margin:0 auto;filter:grayscale(1) brightness(.57) contrast(8)}'
)
$ticketPdfContent = $ticketPdfContent.Replace(
    'filter:grayscale(1) contrast(2.8) brightness(.48)',
    'filter:grayscale(1) brightness(.57) contrast(8)'
)
if (-not $ticketPdfContent.Contains('<td>PLACA</td>')) {
    $ticketDirection = '<tr><td>DIRECCION</td><td><?php echo $esc(trim((string)$docVenta[''Direccion'']) ?: ''-''); ?></td></tr></table>'
    $ticketDocumentFields = '<tr><td>DIRECCION</td><td><?php echo $esc(trim((string)$docVenta[''Direccion'']) ?: ''-''); ?></td></tr><?php if(!empty($docVenta[''Placa''])): ?><tr><td>PLACA</td><td><?php echo $esc($docVenta[''Placa'']); ?></td></tr><?php endif; ?><?php if(!empty($docVenta[''Observacion''])): ?><tr><td>OBSERVACION</td><td><?php echo $esc($docVenta[''Observacion'']); ?></td></tr><?php endif; ?></table>'
    if (-not $ticketPdfContent.Contains($ticketDirection)) {
        throw 'No se encontro la direccion en ticket_pdf.php.'
    }
    $ticketPdfContent = $ticketPdfContent.Replace($ticketDirection, $ticketDocumentFields)
}
[System.IO.File]::WriteAllText($ticketPdfTarget, $ticketPdfContent, $utf8WithoutBom)

$domPdfTarget = Join-Path $backendRoot 'api\sunat\plugins\dompdf\index.php'
$domPdfContent = [System.IO.File]::ReadAllText($domPdfTarget, $utf8WithoutBom)
$domPdfContent = $domPdfContent.Replace('$accent = $esFactura ? ''#087f8c'' : ''#9f1736'';', '$accent = ''#075247'';')
$domPdfContent = $domPdfContent.Replace('$accentDark = $esFactura ? ''#064e59'' : ''#4b1727'';', '$accentDark = ''#043b35'';')
$domPdfContent = $domPdfContent.Replace('$accentSoft = $esFactura ? ''#e7f7f7'' : ''#fff0f3'';', '$accentSoft = ''#e5f2ef'';')
$domPdfContent = $domPdfContent.Replace('#082756', '#043b35')
$domPdfContent = $domPdfContent.Replace('#789000', '#075247')
$domPdfContent = $domPdfContent.Replace('#748700', '#075247')
$domPdfContent = $domPdfContent.Replace('ERP - MODA &amp; RETAIL', 'ERP - BATER&Iacute;AS, AROS Y LLANTAS')
if (-not $domPdfContent.Contains('<strong>PLACA:</strong>') -and
    -not $domPdfContent.Contains('<strong>Placa:</strong>')) {
    $vendorPdfRow = '      <div class="legal-row"><strong>Vendedor:</strong> '' . $e(strtoupper($cab[''txtVENDEDOR''])) . ''</div>'
    $documentPdfRows = $vendorPdfRow + "`r`n" +
        '      '' . (!empty($cab[''txtPLACA'']) ? ''<div class="legal-row"><strong>PLACA:</strong> '' . $e($cab[''txtPLACA'']) . ''</div>'' : '''') . ''' + "`r`n" +
        '      '' . (!empty($cab[''txtOBSERVACION'']) ? ''<div class="legal-row"><strong>OBSERVACION:</strong> '' . $e($cab[''txtOBSERVACION'']) . ''</div>'' : '''') . '''
    if (-not $domPdfContent.Contains($vendorPdfRow)) {
        throw 'No se encontro el vendedor en la plantilla PDF electronica.'
    }
    $domPdfContent = $domPdfContent.Replace($vendorPdfRow, $documentPdfRows)
}
$domPdfContent = $domPdfContent.Replace('<strong>Placa:</strong>', '<strong>PLACA:</strong>')
$domPdfContent = $domPdfContent.Replace('<strong>Observacion:</strong>', '<strong>OBSERVACION:</strong>')
$domPdfContent = $domPdfContent.Replace('$e(strtoupper($cab[''txtPLACA'']))', '$e($cab[''txtPLACA''])')
$domPdfContent = $domPdfContent.Replace('$e(strtoupper($cab[''txtOBSERVACION'']))', '$e($cab[''txtOBSERVACION''])')
[System.IO.File]::WriteAllText($domPdfTarget, $domPdfContent, $utf8WithoutBom)

$migrationTarget = Join-Path $productionDeployTarget 'migrate-sale-fields.php'
$localPhp = [System.IO.Path]::GetFullPath('C:\xampp\php\php.exe')
if (-not (Test-Path -LiteralPath $localPhp)) {
    throw "No existe PHP local para ejecutar la migracion: $localPhp"
}
& $localPhp $migrationTarget $backendRoot
if ($LASTEXITCODE -ne 0) {
    throw 'No se pudo aplicar la migracion de observaciones y placa.'
}

$cashCloseMigrationTarget = Join-Path $productionDeployTarget 'migrate-cash-close-fields.php'
& $localPhp $cashCloseMigrationTarget $backendRoot
if ($LASTEXITCODE -ne 0) {
    throw 'No se pudo aplicar la migracion de descripcion del cierre de caja.'
}

Write-Host 'Compilacion publicada correctamente: boticawebpack -> boticav1/nuevo'
