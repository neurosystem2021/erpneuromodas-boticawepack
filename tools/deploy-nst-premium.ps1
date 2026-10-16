$ErrorActionPreference = 'Stop'
$workspacePath = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$buildPath = Join-Path $workspacePath 'dist'
$applicationPath = 'C:\xampp\htdocs\boticav1'
$destinationPath = [System.IO.Path]::GetFullPath((Join-Path $applicationPath 'nuevo'))
$headerPath = Join-Path $applicationPath 'views\header.php'
if ($destinationPath -ne 'C:\xampp\htdocs\boticav1\nuevo') { throw 'Unexpected deployment target.' }
if (-not (Test-Path -LiteralPath (Join-Path $buildPath 'index.html'))) { throw 'Compiled index.html is missing.' }
if (-not (Test-Path -LiteralPath (Join-Path $buildPath 'static\js\nst-legacy-ui.js'))) { throw 'Legacy adapter is missing from the build.' }
if (-not (Test-Path -LiteralPath $headerPath)) { throw 'Shared PHP header is missing.' }
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$backupPath = Join-Path $applicationPath ('.nst-premium-backup-' + $stamp)
New-Item -ItemType Directory -Path $backupPath | Out-Null
Copy-Item -LiteralPath $headerPath -Destination (Join-Path $backupPath 'header.php')
if (Test-Path -LiteralPath (Join-Path $destinationPath 'index.html')) {
  Copy-Item -LiteralPath (Join-Path $destinationPath 'index.html') -Destination (Join-Path $backupPath 'index.html')
}
# Hashed bundles are additive. Preserve previous bundles for rollback and open tabs.
New-Item -ItemType Directory -Path $destinationPath -Force | Out-Null
$utf8 = New-Object System.Text.UTF8Encoding($false)
$buildFiles = Get-ChildItem -LiteralPath $buildPath -Recurse -File | Sort-Object @{ Expression = { if ($_.FullName -eq (Join-Path $buildPath 'index.html')) { 1 } else { 0 } } }, FullName
foreach ($file in $buildFiles) {
  $relativePath = $file.FullName.Substring($buildPath.Length + 1)
  $targetPath = Join-Path $destinationPath $relativePath
  $targetDirectory = Split-Path -Parent $targetPath
  New-Item -ItemType Directory -Path $targetDirectory -Force | Out-Null
  if ((Test-Path -LiteralPath $targetPath) -and ($relativePath -ne 'index.html')) {
    $oldHash = (Get-FileHash -LiteralPath $targetPath -Algorithm SHA256).Hash
    $newHash = (Get-FileHash -LiteralPath $file.FullName -Algorithm SHA256).Hash
    if ($oldHash -ne $newHash) {
      $savedPath = Join-Path $backupPath $relativePath
      New-Item -ItemType Directory -Path (Split-Path -Parent $savedPath) -Force | Out-Null
      Copy-Item -LiteralPath $targetPath -Destination $savedPath
    }
  }
  Copy-Item -LiteralPath $file.FullName -Destination $targetPath -Force
}
$headerText = [System.IO.File]::ReadAllText($headerPath)
$adapter = @'
<!-- NST PREMIUM MODULE ADAPTER -->
<?php
$nstAssetBase = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/') . '/nuevo';
$nstIndexFile = __DIR__ . '/../nuevo/index.html';
$nstAssetVersion = is_file($nstIndexFile) ? (string) filemtime($nstIndexFile) : 'premium';
?>
<link rel="stylesheet" href="<?php echo htmlspecialchars($nstAssetBase . '/static/css/nst-legacy.css?v=' . $nstAssetVersion, ENT_QUOTES, 'UTF-8'); ?>">
<script defer src="<?php echo htmlspecialchars($nstAssetBase . '/static/js/nst-legacy-ui.js?v=' . $nstAssetVersion, ENT_QUOTES, 'UTF-8'); ?>"></script>
<!-- END NST PREMIUM MODULE ADAPTER -->
'@
if ($headerText.Contains('<!-- NST PREMIUM MODULE ADAPTER -->')) {
  $headerText = [regex]::Replace($headerText, '(?s)<!-- NST PREMIUM MODULE ADAPTER -->.*?<!-- END NST PREMIUM MODULE ADAPTER -->', [System.Text.RegularExpressions.MatchEvaluator]{ param($match) $adapter })
} else {
  $headerText += [Environment]::NewLine + $adapter + [Environment]::NewLine
}
[System.IO.File]::WriteAllText($headerPath, $headerText, $utf8)
$mismatches = @()
foreach ($file in $buildFiles) {
  $targetPath = Join-Path $destinationPath $file.FullName.Substring($buildPath.Length + 1)
  if ((Get-FileHash -LiteralPath $file.FullName).Hash -ne (Get-FileHash -LiteralPath $targetPath).Hash) { $mismatches += $targetPath }
}
if ($mismatches.Count -gt 0) { throw ('Deployment checksum mismatch: ' + ($mismatches -join ', ')) }
Write-Output ('Deployed ' + $buildFiles.Count + ' compiled files to ' + $destinationPath)
Write-Output ('Rollback backup: ' + $backupPath)
