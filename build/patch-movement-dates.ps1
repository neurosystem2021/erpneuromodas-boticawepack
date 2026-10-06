$ErrorActionPreference = 'Stop'

$target = [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\api\index.php')
$expectedTarget = [System.IO.Path]::GetFullPath('C:\xampp\htdocs\boticav1\api\index.php')

if ($target -ne $expectedTarget -or -not (Test-Path -LiteralPath $target)) {
    throw "No se encontro la API esperada: $target"
}

$utf8WithoutBom = New-Object System.Text.UTF8Encoding($false)
$content = [System.IO.File]::ReadAllText($target, $utf8WithoutBom)

if ($content.Contains('$normalizarFechaMovimiento = function ($valor) {')) {
    Write-Host 'La API ya normaliza las fechas de movimientos.'
    exit 0
}

$movementDateOriginal = @'
    $movimientoFecha = $request->getParam('movimiento')['MovimientoFecha'];
'@

$movementDateReplacement = @'
    $normalizarFechaMovimiento = function ($valor) {
        $texto = str_replace('T', ' ', trim((string) $valor));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $texto)) {
            $texto .= ' 00:00:00';
        }
        $fecha = DateTime::createFromFormat('!Y-m-d H:i:s', $texto);
        $errores = DateTime::getLastErrors();
        if (!$fecha || (is_array($errores) && ($errores['warning_count'] > 0 || $errores['error_count'] > 0))) {
            return null;
        }
        return $fecha->format('Y-m-d H:i:s');
    };
    $movimientoFecha = $normalizarFechaMovimiento($request->getParam('movimiento')['MovimientoFecha']);
'@

$stockDateOriginal = '    $fechaStock = getNow();'

$stockDateReplacement = @'
    $fechaStockSolicitada = isset($movimientoParam['FechaStock']) && $movimientoParam['FechaStock']
        ? $movimientoParam['FechaStock']
        : getNow();
    $fechaStock = $normalizarFechaMovimiento($fechaStockSolicitada);
    if (!$movimientoFecha || !$fechaStock) {
        return $response->withJson(array(
            'error' => 'La fecha contable o la fecha de stock no tiene un formato valido.'
        ), 422);
    }
'@

if (-not $content.Contains($movementDateOriginal.TrimEnd()) -or
    -not $content.Contains($stockDateOriginal)) {
    throw 'La estructura del endpoint de movimientos cambio; no se aplico el parche.'
}

$backup = $target + '.bak-movement-dates-20261006'
if (-not (Test-Path -LiteralPath $backup)) {
    Copy-Item -LiteralPath $target -Destination $backup
}

$content = $content.Replace($movementDateOriginal.TrimEnd(), $movementDateReplacement.TrimEnd())
$content = $content.Replace($stockDateOriginal, $stockDateReplacement.TrimEnd())
[System.IO.File]::WriteAllText($target, $content, $utf8WithoutBom)

Write-Host 'Fechas contable y de stock habilitadas en la API de movimientos.'
