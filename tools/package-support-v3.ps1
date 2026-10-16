$ErrorActionPreference='Stop'
$root=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$package=Join-Path $root 'output\support-connector-nst-v3'
New-Item -ItemType Directory -Path $package -Force | Out-Null
foreach($name in @('INSTALACION.txt','install-connector.php','openssl.cnf','setup-connector.php','support-connector.php','widget.js')){
 Copy-Item -LiteralPath (Join-Path $root ('integration\helpdesk\connector\'+$name)) -Destination (Join-Path $package $name) -Force
}
$ui=Join-Path $package 'ui'
New-Item -ItemType Directory -Path $ui -Force | Out-Null
Copy-Item -Path (Join-Path $root 'output\support-ui\*') -Destination $ui -Recurse -Force
Compress-Archive -Path (Join-Path $package '*') -DestinationPath (Join-Path $root 'output\support-connector-nst-v3.zip') -Force
Write-Output 'Prepared public connector v3 with updated support UI. No private configuration or keys included.'
