$ErrorActionPreference = 'Stop'
$workspace = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$api = 'C:\xampp\htdocs\boticav1\api'
$index = Join-Path $api 'index.php'
$routes = Join-Path $api 'product-images-routes.php'
$text = [IO.File]::ReadAllText($index)
$include = "require __DIR__ . '/product-images-routes.php';"
if (-not $text.Contains($include)) {
    $pattern = '(?s)\$app->post\(''/producto/imagen/\{id\}''.*?(?=\$app->get\(''/movimiento/productos'')'
    if ([regex]::Matches($text, $pattern).Count -ne 1) { throw 'Expected exactly one legacy image endpoint.' }
    $text = [regex]::Replace($text, $pattern, [System.Text.RegularExpressions.MatchEvaluator]{ param($match) $include + "`r`n`r`n" })
}
$backup = Join-Path 'C:\xampp\htdocs\boticav1' ('.nst-images-backup-' + (Get-Date -Format 'yyyyMMdd-HHmmss'))
New-Item -ItemType Directory -Path $backup | Out-Null
Copy-Item -LiteralPath $index -Destination (Join-Path $backup 'index.php')
if(Test-Path -LiteralPath $routes) { Copy-Item -LiteralPath $routes -Destination (Join-Path $backup 'product-images-routes.php') }
$utf8 = New-Object System.Text.UTF8Encoding($false)
$staged = Join-Path $workspace '.tmp-product-images-api.php'
[IO.File]::WriteAllText($staged, $text, $utf8)
& C:\xampp\php\php.exe -l $staged
if($LASTEXITCODE -ne 0) { throw 'API syntax check failed.' }
& C:\xampp\php\php.exe -l (Join-Path $workspace 'server\product-images-routes.php')
if($LASTEXITCODE -ne 0) { throw 'Image routes syntax check failed.' }
Copy-Item -LiteralPath (Join-Path $workspace 'server\product-images-routes.php') -Destination $routes
[IO.File]::WriteAllText($index, $text, $utf8)
Write-Output ('Product image API deployed. Backup: ' + $backup)
