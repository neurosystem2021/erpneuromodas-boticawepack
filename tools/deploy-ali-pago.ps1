$ErrorActionPreference = 'Stop'
$workspacePath = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$applicationPath = 'C:\xampp\htdocs\boticav1'
$backupPath = Join-Path $applicationPath ('.ali-pago-backup-' + (Get-Date -Format 'yyyyMMdd-HHmmss'))
New-Item -ItemType Directory -Path $backupPath | Out-Null
$files = @('ali-checkout.php', 'ali-pos-security.php', 'ali-api.php')
foreach ($name in $files) {
  $target = Join-Path $applicationPath ('api\' + $name)
  if (Test-Path -LiteralPath $target) { Copy-Item -LiteralPath $target -Destination (Join-Path $backupPath $name) }
  Copy-Item -LiteralPath (Join-Path $workspacePath ('integration\' + $name)) -Destination $target -Force
}
$buildPath = Join-Path $workspacePath 'dist'
$destinationPath = Join-Path $applicationPath 'nuevo'
$buildFiles = Get-ChildItem -LiteralPath $buildPath -Recurse -File | Sort-Object @{Expression={if ($_.Name -eq 'index.html') {1} else {0}}}, FullName
foreach ($file in $buildFiles) {
  $relative = $file.FullName.Substring($buildPath.Length + 1)
  $target = Join-Path $destinationPath $relative
  if (Test-Path -LiteralPath $target) {
    if ((Get-FileHash -LiteralPath $target).Hash -ne (Get-FileHash -LiteralPath $file.FullName).Hash) {
      $saved = Join-Path $backupPath ('nuevo\' + $relative)
      New-Item -ItemType Directory -Path (Split-Path -Parent $saved) -Force | Out-Null
      Copy-Item -LiteralPath $target -Destination $saved
    }
  }
  New-Item -ItemType Directory -Path (Split-Path -Parent $target) -Force | Out-Null
  Copy-Item -LiteralPath $file.FullName -Destination $target -Force
  if ((Get-FileHash -LiteralPath $target).Hash -ne (Get-FileHash -LiteralPath $file.FullName).Hash) { throw 'Deployment checksum mismatch' }
}
Write-Output ('ALI deployed with backup: ' + $backupPath)
Write-Output 'No PHP header, fiscal service or certificate changes. The additive operation-table migration is installed separately.'
