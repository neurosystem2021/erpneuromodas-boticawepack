$ErrorActionPreference = 'Stop'
$sourceRoot = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\dist'))
$targetRoot = 'C:\xampp\htdocs\boticav1\nuevo'
$backupRoot = Join-Path (Split-Path $targetRoot -Parent) ('.ali-speed-backup-' + (Get-Date -Format 'yyyyMMdd-HHmmss'))
if (!(Test-Path -LiteralPath (Join-Path $sourceRoot 'index.html'))) { throw 'Missing compiled application' }
$assets = Get-ChildItem -LiteralPath $sourceRoot -Recurse -File | Sort-Object @{Expression={if($_.Name -eq 'index.html'){1}else{0}}},FullName
foreach ($asset in $assets) {
 $relative = $asset.FullName.Substring($sourceRoot.Length+1)
 $target = Join-Path $targetRoot $relative
 if ((Test-Path -LiteralPath $target) -and ((Get-FileHash -LiteralPath $target).Hash -eq (Get-FileHash -LiteralPath $asset.FullName).Hash)) { continue }
 if (Test-Path -LiteralPath $target) {
  $backup = Join-Path $backupRoot $relative
  New-Item -ItemType Directory -Path (Split-Path $backup -Parent) -Force | Out-Null
  Copy-Item -LiteralPath $target -Destination $backup
 }
 New-Item -ItemType Directory -Path (Split-Path $target -Parent) -Force | Out-Null
 Copy-Item -LiteralPath $asset.FullName -Destination $target -Force
 if ((Get-FileHash -LiteralPath $target).Hash -ne (Get-FileHash -LiteralPath $asset.FullName).Hash) { throw 'Checksum mismatch' }
}
Write-Output "Frontend installed. Backup: $backupRoot"
