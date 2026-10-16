$ErrorActionPreference = 'Stop'
$workspace = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$source = Join-Path $workspace 'deploy-src\lo_confrontacion.php'
$target = 'C:\xampp\htdocs\boticav1\views\lo_confrontacion.php'
& C:\xampp\php\php.exe -l $source
if ($LASTEXITCODE -ne 0) { throw 'PHP syntax check failed.' }
& (Join-Path $PSScriptRoot 'deploy-nst-premium.ps1')
$backup = $target + '.inventory-app-backup-' + (Get-Date -Format 'yyyyMMdd-HHmmss')
Copy-Item -LiteralPath $target -Destination $backup
Copy-Item -LiteralPath $source -Destination $target
Write-Output "Inventory app deployed. PHP backup: $backup"
