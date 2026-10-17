$ErrorActionPreference='Stop'
$live='C:/xampp/htdocs/boticav1'
$backup='C:/xampp/accounting-private/agenda-personal-files-'+(Get-Date -Format 'yyyyMMdd-HHmmss')
New-Item -ItemType Directory -Path $backup | Out-Null
Copy-Item -LiteralPath (Join-Path $live 'api/index.php') -Destination (Join-Path $backup 'index.php')
Copy-Item -LiteralPath (Join-Path $live 'api/diario-api.php') -Destination (Join-Path $backup 'diario-api.php')
if(Test-Path -LiteralPath (Join-Path $live 'api/agenda-api.php')){Copy-Item -LiteralPath (Join-Path $live 'api/agenda-api.php') -Destination (Join-Path $backup 'agenda-api.php')}
& C:/xampp/php/php.exe tools/install-agenda-personal.php
if($LASTEXITCODE -ne 0){throw 'Agenda migration failed'}
Copy-Item -LiteralPath integration/diario/agenda-api.php -Destination (Join-Path $live 'api/agenda-api.php') -Force
Copy-Item -LiteralPath integration/diario/diario-api.php -Destination (Join-Path $live 'api/diario-api.php') -Force
$path=Join-Path $live 'api/index.php';$text=[IO.File]::ReadAllText($path)
$anchor='$registerDiary=require __DIR__.''/diario-api.php'';$registerDiary($app);'
if(!$text.Contains('/agenda-api.php')){if(!$text.Contains($anchor)){throw 'Diary API registration missing'};$text=$text.Replace($anchor,$anchor+[Environment]::NewLine+'$registerAgenda=require __DIR__.''/agenda-api.php'';$registerAgenda($app);');[IO.File]::WriteAllText($path,$text,(New-Object Text.UTF8Encoding($false)))}
& C:/xampp/php/php.exe -l $path
if($LASTEXITCODE -ne 0){throw 'PHP index validation failed'}
Write-Output "Agenda API installed. Backup: $backup"
