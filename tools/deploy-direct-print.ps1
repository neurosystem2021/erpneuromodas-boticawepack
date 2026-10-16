$ErrorActionPreference='Stop'
$root=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$live='C:\xampp\htdocs\boticav1\imprimir'
$usb=Join-Path $live 'usb.php'
if((Get-FileHash -LiteralPath $usb).Hash -ne 'DEA2AEC2F6C2F0ED607B1169CED8CDCB802A9F323B9C787FCFFC72D5B201AC94'){throw 'Print endpoint changed; inspect before replacing'}
$backup=Join-Path (Split-Path $live) ('.direct-print-backup-'+(Get-Date -Format 'yyyyMMdd-HHmmss'))
New-Item -ItemType Directory -Path $backup -Force | Out-Null
foreach($file in @('usb.php','usb-print.ps1','ticket-raster.php','configuracion.php')){
 $destination=Join-Path $live $file
 if(Test-Path -LiteralPath $destination){Copy-Item -LiteralPath $destination -Destination (Join-Path $backup $file)}
 Copy-Item -LiteralPath (Join-Path $root ('integration\direct-print\'+$file)) -Destination $destination -Force
 if((Get-FileHash -LiteralPath $destination).Hash -ne (Get-FileHash -LiteralPath (Join-Path $root ('integration\direct-print\'+$file))).Hash){throw 'Print deployment checksum mismatch'}
}
Write-Output ('IMCEDEN print transport installed. Backup: '+$backup)
