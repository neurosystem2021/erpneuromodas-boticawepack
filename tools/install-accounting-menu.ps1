$ErrorActionPreference='Stop'
$live='C:/xampp/htdocs/boticav1'
$header=Join-Path $live 'views/header.php'
$text=[IO.File]::ReadAllText($header)
$pattern='(?s)<li class="dropdown">\s*<a class="dropdown-toggle" data-toggle="dropdown" href="#">\s*<i class="fa fa-users" aria-hidden="true"></i> Multi Agente.*?</ul>\s*</li>'
if([regex]::Matches($text,$pattern).Count -ne 1){throw 'Expected exactly one Multi Agente menu'}
$include='<?php include __DIR__.''/../api/accounting-menu.php''; ?>'
$text=$text.Replace($include,'')
$text=[regex]::Replace($text,$pattern,$include,1)
$backup='C:/xampp/accounting-private/menu-'+(Get-Date -Format 'yyyyMMdd-HHmmss')
New-Item -ItemType Directory -Path $backup | Out-Null
Copy-Item -LiteralPath $header -Destination (Join-Path $backup 'header.php')
Copy-Item -LiteralPath (Join-Path $live 'api/accounting-menu.php') -Destination (Join-Path $backup 'accounting-menu.php')
Copy-Item -LiteralPath (Join-Path $live 'views/contabilidad.php') -Destination (Join-Path $backup 'contabilidad.php')
[IO.File]::WriteAllText($header,$text,(New-Object Text.UTF8Encoding($false)))
Copy-Item integration/accounting/api/accounting-menu.php -Destination (Join-Path $live 'api/accounting-menu.php') -Force
Copy-Item integration/accounting/contabilidad.php -Destination (Join-Path $live 'views/contabilidad.php') -Force
& C:/xampp/php/php.exe -l $header
& C:/xampp/php/php.exe -l (Join-Path $live 'views/contabilidad.php')
& C:/xampp/php/php.exe -l (Join-Path $live 'api/accounting-menu.php')
Write-Output "Menu replaced. Backup: $backup"
