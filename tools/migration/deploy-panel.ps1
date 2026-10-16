$ErrorActionPreference='Stop'
$workspace=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..'))
$live='C:\xampp\htdocs\boticav1'
$backup=Join-Path $live ('.migration-panel-backup-'+(Get-Date -Format 'yyyyMMdd-HHmmss'))
$api=Join-Path $live 'api\ali-api.php'
if((Get-FileHash -LiteralPath $api).Hash -ne 'FC6327306BD5EC3BCAC032A2275C04D56742225B636DC8493B5272D9580487B0'){throw 'API changed; inspect before deployment'}
$header=Join-Path $live 'views\header.php'
$utf8=New-Object System.Text.UTF8Encoding($false)
$text=[IO.File]::ReadAllText($header,$utf8)
$needle='<li><a href="<?php echo V_Usuario; ?>">Usuarios</a></li>'
if(([regex]::Matches($text,[regex]::Escape($needle))).Count -ne 1){throw 'Admin menu insertion is not unique'}
$files=@('Engine.php','runtime.php','schema.sql')
foreach($file in $files){$destination=Join-Path $live ('api\migration\'+$file);New-Item -ItemType Directory -Path (Split-Path $destination) -Force | Out-Null;if(Test-Path -LiteralPath $destination){New-Item -ItemType Directory -Path (Join-Path $backup 'api\migration') -Force | Out-Null;Copy-Item -LiteralPath $destination -Destination (Join-Path $backup ('api\migration\'+$file))};Copy-Item -LiteralPath (Join-Path $workspace ('integration\migration\'+$file)) -Destination $destination}
New-Item -ItemType Directory -Path (Join-Path $backup 'api') -Force | Out-Null
New-Item -ItemType Directory -Path (Join-Path $backup 'views') -Force | Out-Null
Copy-Item -LiteralPath $api -Destination (Join-Path $backup 'api\ali-api.php')
Copy-Item -LiteralPath $header -Destination (Join-Path $backup 'views\header.php')
Copy-Item -LiteralPath (Join-Path $workspace 'integration\migration\migrador-historico.php') -Destination (Join-Path $live 'views\migrador-historico.php')
Copy-Item -LiteralPath (Join-Path $workspace 'integration\ali-api.php') -Destination $api
$entry=@'
<?php if (strcasecmp((string)($_SESSION['user']??''),'superadmin')===0): ?><li><a href="/views/migrador-historico.php">Migrador de compras y ventas</a></li><?php endif; ?>
'@
[IO.File]::WriteAllText($header,$text.Replace($needle,$needle+"`r`n              "+$entry),$utf8)
Write-Output ('Administrative panel installed. Backup: '+$backup)
