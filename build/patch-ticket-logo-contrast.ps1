$ErrorActionPreference = 'Stop'

$targets = @(
    'C:\xampp\htdocs\boticav1\imprimir\comprobanteelectronico.php',
    'C:\xampp\htdocs\boticav1\imprimir\ticket.php',
    'C:\xampp\htdocs\boticav1\api\templates\ticket_pdf.php'
)
$oldFilter = 'filter:grayscale(1) contrast(2.8) brightness(.48)'
$newFilter = 'filter:grayscale(1) brightness(.57) contrast(8)'
$utf8WithoutBom = New-Object System.Text.UTF8Encoding($false)

foreach ($target in $targets) {
    $resolvedTarget = [System.IO.Path]::GetFullPath($target)
    if (-not $resolvedTarget.StartsWith('C:\xampp\htdocs\boticav1\') -or
        -not (Test-Path -LiteralPath $resolvedTarget)) {
        throw "Ruta de impresion inesperada: $resolvedTarget"
    }

    $content = [System.IO.File]::ReadAllText($resolvedTarget, $utf8WithoutBom)
    if ($content.Contains($newFilter)) {
        continue
    }
    if (-not $content.Contains($oldFilter)) {
        throw "No se encontro el filtro anterior en $resolvedTarget"
    }

    $backup = $resolvedTarget + '.bak-logo-contrast-20261006'
    if (-not (Test-Path -LiteralPath $backup)) {
        Copy-Item -LiteralPath $resolvedTarget -Destination $backup
    }
    $content = $content.Replace($oldFilter, $newFilter)
    [System.IO.File]::WriteAllText($resolvedTarget, $content, $utf8WithoutBom)
}

Write-Host 'Logo EDEN COMPANY y simbolo de bateria configurados en negro de alto contraste.'
