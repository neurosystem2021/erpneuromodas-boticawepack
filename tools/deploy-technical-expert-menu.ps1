$ErrorActionPreference='Stop'
$workspace=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$target='C:\xampp\htdocs\boticav1'
$header=Join-Path $target 'views\header.php'
$headerText=[IO.File]::ReadAllText($header)
$entry='<li><a href="/views/tecnico-experto.php" class="text-primary-color"><i class="fa fa-wrench" aria-hidden="true"></i>Técnico experto</a></li>'
if(!$headerText.Contains('/views/tecnico-experto.php')){
 $anchor=[regex]::Match($headerText,'(?m)^\s*<li><a href="/views/Gen_ProductoForm.php"[^\r\n]*</li>')
 if(!$anchor.Success){throw 'Product menu anchor missing; no changes made.'}
 $headerText=$headerText.Insert($anchor.Index+$anchor.Length,"`r`n            "+$entry)
}
$backup=Join-Path $target ('.technical-expert-backup-'+(Get-Date -Format 'yyyyMMdd-HHmmss'))
New-Item -ItemType Directory -Path $backup | Out-Null
Copy-Item -LiteralPath $header -Destination (Join-Path $backup 'header.php')
$utf8=New-Object System.Text.UTF8Encoding($false)
[IO.File]::WriteAllText(($header+'.technical-tmp'),$headerText,$utf8)
Move-Item -LiteralPath ($header+'.technical-tmp') -Destination $header -Force
$page=Join-Path $target 'views\tecnico-experto.php'
if(Test-Path -LiteralPath $page){Copy-Item -LiteralPath $page -Destination (Join-Path $backup 'tecnico-experto.php')}
Copy-Item -LiteralPath (Join-Path $workspace 'server\tecnico-experto.php') -Destination $page -Force
Write-Output ('Technical expert product submenu installed. Backup: '+$backup)
