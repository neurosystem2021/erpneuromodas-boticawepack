$ErrorActionPreference = 'Stop'
$workspace = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$api = 'C:\xampp\htdocs\boticav1\api'
$index = Join-Path $api 'index.php'
$text = [IO.File]::ReadAllText($index)
$include = "require __DIR__ . '/movement-control.php';"
if (-not $text.Contains($include)) {
    $anchor = "require __DIR__ . '/product-suppliers-routes.php';"
    if (-not $text.Contains($anchor)) { throw 'Expected routes include missing.' }
    $text = $text.Replace($anchor, $anchor + "`r`n" + $include)
}
$replacements = @{
    '/movimiento/eliminar' = '$app->post(''/movimiento/eliminar'', $nstDeleteMovement);'
    '/movmiento/actualizarperiodo' = '$app->post(''/movmiento/actualizarperiodo'', $nstUpdateMovementPeriod);'
}
foreach ($path in $replacements.Keys) {
    $pattern = '(?s)\$app->post\(''' + [regex]::Escape($path) + ''', function \(Request \$request, Response \$response, array \$args\) \{.*?\r?\n\}\);'
    $replacement = $replacements[$path]
    if ($text -notmatch $pattern -and -not $text.Contains($replacement)) { throw "Route not found: $path" }
    $text = [regex]::Replace($text, $pattern, [System.Text.RegularExpressions.MatchEvaluator]{ param($match) $replacement })
}
$staged = Join-Path $workspace '.tmp-movement-control-api.php'
[IO.File]::WriteAllText($staged, $text, (New-Object Text.UTF8Encoding($false)))
foreach ($file in @($staged, (Join-Path $workspace 'server\movement-control.php'), (Join-Path $workspace 'server\movement-control-config.php'))) {
    & C:\xampp\php\php.exe -l $file
    if ($LASTEXITCODE -ne 0) { throw 'PHP syntax check failed.' }
}
& C:\xampp\php\php.exe (Join-Path $workspace 'tools\migrate-movement-audit.php')
if ($LASTEXITCODE -ne 0) { throw 'Migration failed.' }
$backup = Join-Path 'C:\xampp\htdocs\boticav1' ('.nst-movement-control-backup-' + (Get-Date -Format 'yyyyMMdd-HHmmss'))
New-Item -ItemType Directory -Path $backup | Out-Null
Copy-Item -LiteralPath $index -Destination (Join-Path $backup 'index.php')
if (Test-Path -LiteralPath (Join-Path $api 'movement-control.php')) { Copy-Item -LiteralPath (Join-Path $api 'movement-control.php') -Destination (Join-Path $backup 'movement-control.php') }
if (Test-Path -LiteralPath (Join-Path $api 'movement-control-config.php')) { Copy-Item -LiteralPath (Join-Path $api 'movement-control-config.php') -Destination (Join-Path $backup 'movement-control-config.php') }
Copy-Item -LiteralPath (Join-Path $workspace 'server\movement-control-config.php') -Destination (Join-Path $api 'movement-control-config.php')
Copy-Item -LiteralPath (Join-Path $workspace 'server\movement-control.php') -Destination (Join-Path $api 'movement-control.php')
Copy-Item -LiteralPath $staged -Destination $index
Write-Output "Movement controls deployed. Backup: $backup"
