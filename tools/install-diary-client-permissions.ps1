$ErrorActionPreference='Stop'
$live='C:/xampp/htdocs/boticav1'
$backup='C:/xampp/accounting-private/diary-client-permissions-'+(Get-Date -Format 'yyyyMMdd-HHmmss')
New-Item -ItemType Directory -Path $backup | Out-Null
foreach($relative in @('api/diario-api.php','views/diario.php','views/header.php')){Copy-Item -LiteralPath (Join-Path $live $relative) -Destination (Join-Path $backup ([IO.Path]::GetFileName($relative)))}
Copy-Item -LiteralPath integration/diario/DiaryAccess.php -Destination (Join-Path $live 'models/DiaryAccess.php') -Force
Copy-Item -LiteralPath integration/diario/diario-api.php -Destination (Join-Path $live 'api/diario-api.php') -Force
Copy-Item -LiteralPath integration/diario/diario.php -Destination (Join-Path $live 'views/diario.php') -Force
$header=Join-Path $live 'views/header.php';$s=[IO.File]::ReadAllText($header)
$pattern='(?s)<li class="dropdown" id="diario-reportes-menu">.*?</ul></li>'
if([regex]::Matches($s,$pattern).Count -ne 1){throw 'Diary navigation anchor missing'}
$menu=[regex]::Match($s,$pattern).Value
$wrapped='<?php require_once __DIR__.''/../models/DiaryAccess.php''; $headerDiaryAccess=diaryAccessIdentity(appPdoConnection(),(string)($_SESSION[''user'']??'''')); if($headerDiaryAccess[''allowed'']): ?>'+$menu+'<?php endif; ?>'
$s=[regex]::Replace($s,$pattern,[System.Text.RegularExpressions.MatchEvaluator]{param($m) $wrapped},1)
[IO.File]::WriteAllText($header,$s,(New-Object Text.UTF8Encoding($false)))
foreach($relative in @('api/diario-api.php','views/diario.php','views/header.php','models/DiaryAccess.php')){& C:/xampp/php/php.exe -l (Join-Path $live $relative);if($LASTEXITCODE -ne 0){throw 'PHP syntax failed'}}
Write-Output "Diary roles installed. Backup: $backup"
