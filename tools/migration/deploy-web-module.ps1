$ErrorActionPreference='Stop'
$source=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\output\migrador-web'))
$erp='C:\xampp\htdocs\boticav1'
$backup=Join-Path $erp ('.migrador-web-backup-'+(Get-Date -Format 'yyyyMMdd-HHmmss'))
$header=Join-Path $erp 'views\header.php'
$utf8=New-Object System.Text.UTF8Encoding($false)
$text=[IO.File]::ReadAllText($header,$utf8)
$needle='<li><a href="<?php echo V_Usuario; ?>">Usuarios</a></li>'
if(([regex]::Matches($text,[regex]::Escape($needle))).Count -ne 1){throw 'Inspect current Security menu before applying patch'}
foreach($relativeRoot in @('api','views','migration-ui')){
 foreach($file in Get-ChildItem -LiteralPath (Join-Path $source $relativeRoot) -Recurse -File){
  $relative=$file.FullName.Substring($source.Length+1)
  $target=Join-Path $erp $relative
  if(Test-Path -LiteralPath $target){$saved=Join-Path $backup $relative;New-Item -ItemType Directory -Path (Split-Path $saved) -Force | Out-Null;Copy-Item -LiteralPath $target -Destination $saved}
  New-Item -ItemType Directory -Path (Split-Path $target) -Force | Out-Null
  Copy-Item -LiteralPath $file.FullName -Destination $target -Force
  if((Get-FileHash -LiteralPath $target).Hash -ne (Get-FileHash -LiteralPath $file.FullName).Hash){throw 'Module file checksum mismatch'}
 }
}
New-Item -ItemType Directory -Path (Join-Path $backup 'views') -Force | Out-Null
Copy-Item -LiteralPath $header -Destination (Join-Path $backup 'views\header.php')
$entry=[IO.File]::ReadAllText((Join-Path $source 'menu-superadmin.snippet.php'),$utf8)
if(!$text.Contains('/views/migrador-historico.php')){[IO.File]::WriteAllText($header,$text.Replace($needle,$needle+"`r`n"+$entry),$utf8)}
Write-Output ('Integrated web module installed. File backup: '+$backup)
Write-Output 'Menu: Seguridad > Migrador de compras y ventas (superadmin only). No database migration executed.'
