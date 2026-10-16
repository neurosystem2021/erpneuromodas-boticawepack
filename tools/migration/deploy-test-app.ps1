$ErrorActionPreference='Stop'
$source=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..\output\migration-test-app'))
$target='C:\xampp\htdocs\neuro-migration-test'
if(!(Test-Path -LiteralPath (Join-Path $source 'index.php'))){throw 'Test application not packaged'}
New-Item -ItemType Directory -Path $target -Force | Out-Null
foreach($file in Get-ChildItem -LiteralPath $source -Recurse -File){$relative=$file.FullName.Substring($source.Length+1);$destination=Join-Path $target $relative;New-Item -ItemType Directory -Path (Split-Path $destination) -Force | Out-Null;Copy-Item -LiteralPath $file.FullName -Destination $destination -Force;if((Get-FileHash -LiteralPath $destination).Hash -ne (Get-FileHash -LiteralPath $file.FullName).Hash){throw 'Test deployment checksum mismatch'}}
Write-Output 'Test application installed: http://localhost:8099/ (start-test-server.ps1)'
