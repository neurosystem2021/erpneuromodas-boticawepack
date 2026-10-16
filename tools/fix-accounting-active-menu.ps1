$ErrorActionPreference='Stop'
$header='C:/xampp/htdocs/boticav1/views/header.php'
$text=[IO.File]::ReadAllText($header)
$old='      if (targetPath && targetPath === currentPath) {'
$new=@'
      var sameSection = true;
      if (targetPath === '/views/contabilidad.php') {
        var targetTab = new URLSearchParams(target.search).get('tab') || 'dashboard';
        var currentTab = new URLSearchParams(window.location.search).get('tab') || 'dashboard';
        sameSection = targetTab === currentTab;
      }
      if (targetPath && targetPath === currentPath && sameSection) {
'@
if(!$text.Contains($old)){throw 'Active menu anchor missing'}
Copy-Item -LiteralPath $header -Destination ('C:/xampp/accounting-private/menu-active-section-'+(Get-Date -Format 'yyyyMMdd-HHmmss')+'.php')
$text=$text.Replace($old,$new)
[IO.File]::WriteAllText($header,$text,(New-Object Text.UTF8Encoding($false)))
