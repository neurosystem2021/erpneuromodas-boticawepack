$ErrorActionPreference = 'Stop'
$workspace = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$api = 'C:\xampp\htdocs\boticav1\api'
$index = Join-Path $api 'index.php'
$text = [IO.File]::ReadAllText($index)
$pattern = '(?s)\$app->post\(''/ordencompra'', function \(Request \$request, Response \$response\) \{.*?\r?\n\}\);'
$replacement = '$app->post(''/ordencompra'', require __DIR__ . ''/order-create.php'');'
if ($text -notmatch $pattern -and -not $text.Contains($replacement)) { throw 'Order route not found.' }
$text = [regex]::Replace($text, $pattern, [System.Text.RegularExpressions.MatchEvaluator]{ param($match) $replacement })
$cancelPattern = '(?s)(\$app->post\(''/ordencompra/anular''.*?update\(array\("Estado" => )1(\)\))'
$text = [regex]::Replace($text, $cancelPattern, '${1}0${2}')
$staged = Join-Path $workspace '.tmp-order-validator-api.php'
[IO.File]::WriteAllText($staged, $text, (New-Object Text.UTF8Encoding($false)))
foreach ($file in @($staged, (Join-Path $workspace 'server\order-create.php'), (Join-Path $workspace 'server\product-suppliers-routes.php'))) {
    & C:\xampp\php\php.exe -l $file
    if ($LASTEXITCODE -ne 0) { throw 'PHP validation failed.' }
}
$backup = Join-Path 'C:\xampp\htdocs\boticav1' ('.nst-order-validator-backup-' + (Get-Date -Format 'yyyyMMdd-HHmmss'))
New-Item -ItemType Directory -Path $backup | Out-Null
foreach ($name in @('index.php', 'order-create.php', 'product-suppliers-routes.php')) {
    $target = Join-Path $api $name
    if (Test-Path -LiteralPath $target) { Copy-Item -LiteralPath $target -Destination (Join-Path $backup $name) }
}
Copy-Item -LiteralPath (Join-Path $workspace 'server\order-create.php') -Destination (Join-Path $api 'order-create.php')
Copy-Item -LiteralPath (Join-Path $workspace 'server\product-suppliers-routes.php') -Destination (Join-Path $api 'product-suppliers-routes.php')
Copy-Item -LiteralPath $staged -Destination $index
Write-Output "Order validator deployed. Backup: $backup"
