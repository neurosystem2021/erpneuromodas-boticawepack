$ErrorActionPreference = 'Stop'
$workspace = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$api = 'C:\xampp\htdocs\boticav1\api'
$index = Join-Path $api 'index.php'
$routes = Join-Path $api 'product-suppliers-routes.php'
$text = [IO.File]::ReadAllText($index)
$include = "require __DIR__ . '/product-suppliers-routes.php';"
if (-not $text.Contains($include)) {
    $anchor = "require __DIR__ . '/product-images-routes.php';"
    if (-not $text.Contains($anchor)) { throw 'Expected product routes include missing.' }
    $text = $text.Replace($anchor, $anchor + "`r`n" + $include)
}
$backup = Join-Path 'C:\xampp\htdocs\boticav1' ('.nst-suppliers-backup-' + (Get-Date -Format 'yyyyMMdd-HHmmss'))
New-Item -ItemType Directory -Path $backup | Out-Null
Copy-Item -LiteralPath $index -Destination (Join-Path $backup 'index.php')
if(Test-Path -LiteralPath $routes) { Copy-Item -LiteralPath $routes -Destination (Join-Path $backup 'product-suppliers-routes.php') }
$utf8 = New-Object System.Text.UTF8Encoding($false)
$staged = Join-Path $workspace '.tmp-product-suppliers-api.php'
[IO.File]::WriteAllText($staged, $text, $utf8)
& C:\xampp\php\php.exe -l $staged
if($LASTEXITCODE -ne 0) { throw 'API syntax check failed.' }
& C:\xampp\php\php.exe -l (Join-Path $workspace 'server\product-suppliers-routes.php')
if($LASTEXITCODE -ne 0) { throw 'Supplier routes syntax check failed.' }
Copy-Item -LiteralPath (Join-Path $workspace 'server\product-suppliers-routes.php') -Destination $routes
[IO.File]::WriteAllText($index, $text, $utf8)
Write-Output ('Supplier API deployed. Backup: ' + $backup)
