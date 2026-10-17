$ErrorActionPreference='Stop'
$live='C:/xampp/htdocs/boticav1'
$backup='C:/xampp/accounting-private/user-module-access-'+(Get-Date -Format 'yyyyMMdd-HHmmss')
New-Item -ItemType Directory -Path $backup | Out-Null
$files=@('models/ModuleAccess.php','models/AccountingModuleAccess.php','controllers/UsuariosModulosGuardar.php','views/Seg_PlanesMenu.php','views/module-access-ui.js')
foreach($relative in $files){$target=Join-Path $live $relative;if(Test-Path -LiteralPath $target){Copy-Item -LiteralPath $target -Destination (Join-Path $backup ([IO.Path]::GetFileName($relative)))};Copy-Item -LiteralPath (Join-Path 'integration/central-config' $relative) -Destination $target -Force}
Copy-Item -LiteralPath (Join-Path $live 'api/accounting.php') -Destination (Join-Path $backup 'accounting.php')
Copy-Item -LiteralPath integration/accounting/api/accounting.php -Destination (Join-Path $live 'api/accounting.php') -Force
$header=Join-Path $live 'views/header.php';Copy-Item -LiteralPath $header -Destination (Join-Path $backup 'header.php');$text=[IO.File]::ReadAllText($header)
$anchor='$headerZonaNombre ='
$insert=@'
require_once __DIR__.'/../models/ModuleAccess.php';
if(strcasecmp((string)($_SESSION['user']??''),'superadmin')!==0){
 $moduleUserRules=moduleAccessPolicy()['users'][$_SESSION['user']??'']??[];
 $headerMenuDeshabilitados=array_values(array_unique(array_merge($headerMenuDeshabilitados,$moduleUserRules['disabled']??[])));
 $headerGruposDeshabilitados=array_values(array_unique(array_merge($headerGruposDeshabilitados,$moduleUserRules['disabled_groups']??[])));
}
'@
if(!$text.Contains('$moduleUserRules')){$text=$text.Replace($anchor,$insert+[Environment]::NewLine+$anchor)}
$old="return u.origin===location.origin?u.pathname.toLowerCase():u.href.toLowerCase()"
$new="return u.origin===location.origin?(u.pathname.toLowerCase()+(u.pathname.toLowerCase()==='/views/contabilidad.php'?'?tab='+(u.searchParams.get('tab')||'dashboard'):(u.pathname.toLowerCase()==='/views/diario.php'&&u.searchParams.get('reportes')?'?reportes=1':''))):u.href.toLowerCase()"
$text=$text.Replace($old,$new)
$text=$text.Replace("disabled.indexOf(key(a))!==-1","(disabled.indexOf(key(a))!==-1||disabled.indexOf(key(a).split('?')[0])!==-1)")
[IO.File]::WriteAllText($header,$text,(New-Object Text.UTF8Encoding($false)))
$validate=Join-Path $live 'views/validateUser.php';Copy-Item -LiteralPath $validate -Destination (Join-Path $backup 'validateUser.php');$text=[IO.File]::ReadAllText($validate)
$guard=@'
require_once __DIR__.'/../models/ModuleAccess.php';
moduleAccessRequire($_SERVER['REQUEST_URI']??$_SERVER['PHP_SELF']);
if($modulo==='contabilidad.php'){
 $accountingUserCheck=appPdoConnection()->prepare('SELECT m.CompanyId FROM Co_Member m JOIN Co_Company c ON c.Id=m.CompanyId AND c.Active=1 WHERE m.Username=? LIMIT 1');
 $accountingUserCheck->execute([$_SESSION['user']]);
 if($accountingUserCheck->fetchColumn())return;
}
'@
if(!$text.Contains('moduleAccessRequire(')){$text=$text.Replace('$permisos = $_SESSION',$guard+[Environment]::NewLine+'$permisos = $_SESSION')}
[IO.File]::WriteAllText($validate,$text,(New-Object Text.UTF8Encoding($false)))
foreach($file in @($header,$validate,(Join-Path $live 'api/accounting.php'),(Join-Path $live 'controllers/UsuariosModulosGuardar.php'))){& C:/xampp/php/php.exe -l $file;if($LASTEXITCODE -ne 0){throw 'PHP validation failed'}}
Write-Output "User module controls installed. Backup: $backup"
