$ErrorActionPreference='Stop'
$workspace=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$target='C:\xampp\htdocs\boticav1\api'
$api=Join-Path $target 'ali-api.php'
$expected='382E368B7A3A780F3A8774BF8390E59C443EAE6DA4639049B29078A0BFFAFF19'
$current=(Get-FileHash -LiteralPath $api).Hash
$sourceHash=(Get-FileHash -LiteralPath (Join-Path $workspace 'integration\ali-api.php')).Hash
if($current -ne $expected -and $current -ne $sourceHash){throw 'ALI API changed since inspection; review before replacing it.'}
$backup=Join-Path (Split-Path $target -Parent) ('.product-guide-backup-'+(Get-Date -Format 'yyyyMMdd-HHmmss'))
New-Item -ItemType Directory -Path $backup -Force | Out-Null
foreach($file in @('ali-expert-solutions.php','ali-product-labor.php','ali-technical-expert.php','ali-product-images.php','ali-characteristics.php','ali-product-knowledge.php','ali-product-guide.php','ali-api.php')){
 $source=Join-Path $workspace ('integration\'+$file)
 $dest=Join-Path $target $file
 if(Test-Path -LiteralPath $dest){Copy-Item -LiteralPath $dest -Destination (Join-Path $backup $file)}
 $temp=$dest+'.guide-tmp'
 Copy-Item -LiteralPath $source -Destination $temp -Force
 if((Get-FileHash -LiteralPath $source).Hash -ne (Get-FileHash -LiteralPath $temp).Hash){throw 'Checksum mismatch'}
 Move-Item -LiteralPath $temp -Destination $dest -Force
}
Write-Output ('Product guide API installed. Backup: '+$backup)
