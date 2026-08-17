$ErrorActionPreference = 'Stop'

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

$sourceHash = (Get-FileHash -LiteralPath (Join-Path $sourceRoot 'index.html') -Algorithm SHA256).Hash
$targetHash = (Get-FileHash -LiteralPath (Join-Path $targetRoot 'index.html') -Algorithm SHA256).Hash
if ($sourceHash -ne $targetHash) { throw 'La verificacion del despliegue fallo.' }

Write-Host 'Compilacion publicada correctamente: boticawebpack -> boticav1/nuevo'
