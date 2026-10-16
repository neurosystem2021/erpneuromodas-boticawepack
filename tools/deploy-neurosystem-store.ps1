$ErrorActionPreference='Stop'
$live='C:\xampp\htdocs\boticav1'
$stage='C:\xampp\htdocs\boticawebpack\deploy-src\neurosystem-store'
$backup=Join-Path $live ('.store-brand-backup-'+(Get-Date -Format 'yyyyMMdd-HHmmss'))
New-Item -ItemType Directory -Path $backup | Out-Null
$flyers=Join-Path $live 'resources\images\tienda\flyers'
$oldFlyers=Join-Path $backup 'previous-flyers'
New-Item -ItemType Directory -Path $oldFlyers | Out-Null
Get-ChildItem -LiteralPath $flyers -File -Force | ForEach-Object {
    if($_.Extension -match '^\.(png|jpe?g|gif|webp)$' -or $_.Name -eq '.order.json') {
        Move-Item -LiteralPath $_.FullName -Destination (Join-Path $oldFlyers $_.Name)
    }
}
Get-ChildItem -LiteralPath $stage -File -Recurse | ForEach-Object {
    $relative=$_.FullName.Substring($stage.Length+1)
    $target=Join-Path $live $relative
    if(Test-Path -LiteralPath $target){
        $saved=Join-Path $backup $relative
        New-Item -ItemType Directory -Path (Split-Path $saved) -Force | Out-Null
        Copy-Item -LiteralPath $target -Destination $saved
    }
    New-Item -ItemType Directory -Path (Split-Path $target) -Force | Out-Null
    Copy-Item -LiteralPath $_.FullName -Destination $target -Force
}
$order='["01-neurosystem-computadoras.png","02-neurosystem-laptops.png","03-neurosystem-accesorios.png"]'
[IO.File]::WriteAllText((Join-Path $flyers '.order.json'),$order,(New-Object Text.UTF8Encoding($false)))
Write-Output "Published three flyers and red/white store. Backup: $backup"
