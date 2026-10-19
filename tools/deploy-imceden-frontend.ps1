$ErrorActionPreference = 'Stop'
$workspace = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$source = [IO.Path]::GetFullPath((Join-Path $workspace 'dist'))
$target = [IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\nuevo')
if ($workspace -ne 'C:\xampp\htdocs\boticawebpack' -or $target -ne 'C:\xampp\htdocs\boticav1\nuevo') { throw 'Rutas inesperadas.' }
$branch = & git -c safe.directory=C:/xampp/htdocs/boticawebpack -C $workspace branch --show-current
if ($LASTEXITCODE -ne 0 -or $branch.Trim() -ne 'imceden') { throw 'La compilación debe corresponder a imceden.' }
$index = Join-Path $source 'index.html'
if (!(Test-Path -LiteralPath $index)) { throw 'Falta compilar dist/index.html.' }
$html = [IO.File]::ReadAllText($index)
$required = [regex]::Matches($html, '(?:src|href)=["'']?\.?/([^\s"''>]+)')
foreach ($match in $required) {
    $assetPath = ($match.Groups[1].Value -split '[?#]',2)[0]
    if (!(Test-Path -LiteralPath (Join-Path $source $assetPath))) { throw ('Asset de entrada ausente: ' + $assetPath) }
}
$backup = Join-Path 'C:\xampp\accounting-private' ('imceden-frontend-' + (Get-Date -Format 'yyyyMMdd-HHmmss'))
New-Item -ItemType Directory -Path $backup -Force | Out-Null
$manifest = @()
# Copy assets before switching the entry page. Keep older assets for open tabs.
$files = Get-ChildItem -LiteralPath $source -Recurse -File | Sort-Object @{Expression={if ($_.FullName -eq $index) { 1 } else { 0 }}},FullName
foreach ($file in $files) {
    $relative = $file.FullName.Substring($source.Length + 1)
    $destination = Join-Path $target $relative
    $hash = (Get-FileHash -LiteralPath $file.FullName -Algorithm SHA256).Hash
    if ((Test-Path -LiteralPath $destination) -and (Get-FileHash -LiteralPath $destination -Algorithm SHA256).Hash -eq $hash) { continue }
    $existed = Test-Path -LiteralPath $destination
    if ($existed) {
        $saved = Join-Path $backup $relative
        New-Item -ItemType Directory -Path (Split-Path $saved -Parent) -Force | Out-Null
        Copy-Item -LiteralPath $destination -Destination $saved
    }
    New-Item -ItemType Directory -Path (Split-Path $destination -Parent) -Force | Out-Null
    Copy-Item -LiteralPath $file.FullName -Destination $destination -Force
    if ((Get-FileHash -LiteralPath $destination -Algorithm SHA256).Hash -ne $hash) { throw ('Verificación fallida: ' + $relative) }
    $manifest += [pscustomobject]@{path=$relative;replaced=$existed;sha256=$hash}
}
$manifest | ConvertTo-Json -Depth 4 | Set-Content -LiteralPath (Join-Path $backup 'manifest.json') -Encoding UTF8
Write-Output ('IMCEDEN compilado instalado en ' + $target + '. Archivos actualizados: ' + $manifest.Count + '. Respaldo: ' + $backup)
