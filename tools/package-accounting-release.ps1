$ErrorActionPreference = 'Stop'
$repo = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$module = Join-Path $repo 'integration\accounting'
$dist = Join-Path $repo 'dist'
if (!(Test-Path -LiteralPath (Join-Path $dist 'index.html'))) { throw 'Build the application before packaging.' }
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$release = Join-Path $repo ('output\contabilidad-neurosoft-' + $stamp)
New-Item -ItemType Directory -Path $release -Force | Out-Null
Copy-Item -LiteralPath $module -Destination (Join-Path $release 'module') -Recurse
Copy-Item -LiteralPath $dist -Destination (Join-Path $release 'frontend') -Recurse
New-Item -ItemType Directory -Path (Join-Path $release 'source\src\views'),(Join-Path $release 'source\src\utils') -Force | Out-Null
Copy-Item -LiteralPath (Join-Path $repo 'src\views\Contabilidad.vue') -Destination (Join-Path $release 'source\src\views\Contabilidad.vue')
Copy-Item -LiteralPath (Join-Path $repo 'src\utils\accounting.js') -Destination (Join-Path $release 'source\src\utils\accounting.js')
Copy-Item -LiteralPath (Join-Path $module 'router.snippet.js') -Destination (Join-Path $release 'source\router.snippet.js')
$manifest = Get-ChildItem -LiteralPath $release -Recurse -File | ForEach-Object {
    [PSCustomObject]@{ file = $_.FullName.Substring($release.Length + 1).Replace('\','/'); bytes = $_.Length; sha256 = (Get-FileHash -LiteralPath $_.FullName -Algorithm SHA256).Hash.ToLowerInvariant() }
}
$manifest | ConvertTo-Json -Depth 4 | Set-Content -LiteralPath (Join-Path $release 'manifest-sha256.json') -Encoding UTF8
$archive = $release + '.zip'
Compress-Archive -LiteralPath (Join-Path $release 'module'),(Join-Path $release 'frontend'),(Join-Path $release 'source'),(Join-Path $release 'manifest-sha256.json') -DestinationPath $archive
Write-Output $archive
