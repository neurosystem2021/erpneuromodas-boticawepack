$gatewayRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$healthUrl = 'http://127.0.0.1:8788/health'

try {
    Invoke-RestMethod -Uri $healthUrl -Method Get -TimeoutSec 2 | Out-Null
    Write-Output 'El gateway de WhatsApp ya está iniciado.'
    exit 0
} catch {
    # Continúa con el arranque local.
}

$node = 'C:\Program Files\nodejs\node.exe'
if (-not (Test-Path -LiteralPath $node)) {
    throw 'No se encontró Node.js.'
}

Start-Process -FilePath $node `
    -ArgumentList 'server.cjs' `
    -WorkingDirectory $gatewayRoot `
    -RedirectStandardOutput (Join-Path $gatewayRoot 'gateway.log') `
    -RedirectStandardError (Join-Path $gatewayRoot 'gateway-error.log') `
    -WindowStyle Hidden

Write-Output 'Gateway de WhatsApp iniciado en 127.0.0.1:8788.'
