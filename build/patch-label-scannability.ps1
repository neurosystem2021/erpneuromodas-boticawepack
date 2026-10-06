$ErrorActionPreference = 'Stop'

$projectRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$activeRoot = [System.IO.Path]::GetFullPath((Join-Path $projectRoot '..\boticav1'))
$sourceComponent = [System.IO.Path]::GetFullPath((Join-Path $projectRoot 'server\etiquetas\CodigoBarra.js'))
$sourceCss = [System.IO.Path]::GetFullPath((Join-Path $projectRoot 'server\etiquetas\etiqueta-moderna.css'))
$targetComponent = [System.IO.Path]::GetFullPath((Join-Path $activeRoot 'components\CodigoBarra.js'))
$targetList = [System.IO.Path]::GetFullPath((Join-Path $activeRoot 'components\CodigoBarraLista.js'))
$targetCss = [System.IO.Path]::GetFullPath((Join-Path $activeRoot 'resources\css\etiqueta-moderna.css'))
$targetView = [System.IO.Path]::GetFullPath((Join-Path $activeRoot 'views\codigoBarra.php'))
$version = '20261006-10'
$backupSuffix = '.bak-barcode-mobile-20261006'
$utf8WithoutBom = New-Object System.Text.UTF8Encoding($false)

$expectedActiveRoot = [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1')
if ($activeRoot -ne $expectedActiveRoot) {
    throw "Ruta activa inesperada: $activeRoot"
}

foreach ($path in @($sourceComponent, $sourceCss, $targetComponent, $targetList, $targetCss, $targetView)) {
    if (-not (Test-Path -LiteralPath $path -PathType Leaf)) {
        throw "No se encontro el archivo requerido: $path"
    }
}

foreach ($target in @($targetComponent, $targetList, $targetCss, $targetView)) {
    $backup = $target + $backupSuffix
    if (-not (Test-Path -LiteralPath $backup)) {
        Copy-Item -LiteralPath $target -Destination $backup
    }
}

Copy-Item -LiteralPath $sourceComponent -Destination $targetComponent -Force
Copy-Item -LiteralPath $sourceCss -Destination $targetCss -Force

$viewContent = [System.IO.File]::ReadAllText($targetView, $utf8WithoutBom)
$viewContent = [regex]::Replace(
    $viewContent,
    '/resources/css/etiqueta-moderna\.css\?v=[^"'']+',
    "/resources/css/etiqueta-moderna.css?v=$version"
)
$viewContent = [regex]::Replace(
    $viewContent,
    '\.\./components/CodigoBarraLista\.js\?v=[^''"]+',
    "../components/CodigoBarraLista.js?v=$version"
)
[System.IO.File]::WriteAllText($targetView, $viewContent, $utf8WithoutBom)

$listContent = [System.IO.File]::ReadAllText($targetList, $utf8WithoutBom)
$listContent = [regex]::Replace(
    $listContent,
    '\./CodigoBarra\.js\?v=[^''"]+',
    "./CodigoBarra.js?v=$version"
)
[System.IO.File]::WriteAllText($targetList, $listContent, $utf8WithoutBom)

$componentMatches = (Get-FileHash -LiteralPath $sourceComponent -Algorithm SHA256).Hash -eq (Get-FileHash -LiteralPath $targetComponent -Algorithm SHA256).Hash
$cssMatches = (Get-FileHash -LiteralPath $sourceCss -Algorithm SHA256).Hash -eq (Get-FileHash -LiteralPath $targetCss -Algorithm SHA256).Hash
if (-not $componentMatches -or -not $cssMatches) {
    throw 'La verificacion de los archivos desplegados fallo.'
}

Write-Output "Etiquetas actualizadas en $activeRoot"
Write-Output "Version de cache: $version"
Write-Output "Componente identico: $componentMatches"
Write-Output "CSS identico: $cssMatches"
