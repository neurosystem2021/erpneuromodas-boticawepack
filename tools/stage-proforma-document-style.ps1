$ErrorActionPreference='Stop'
$workspaceRoot=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$styleSource=Join-Path $workspaceRoot 'static\css\proforma-document.css'
$styleTarget=Join-Path $workspaceRoot 'dist\static\css\proforma-document.css'
$builtIndex=Join-Path $workspaceRoot 'dist\index.html'
if(!(Test-Path -LiteralPath $builtIndex)){throw 'Missing compiled application'}
New-Item -ItemType Directory -Path (Split-Path $styleTarget -Parent) -Force | Out-Null
Copy-Item -LiteralPath $styleSource -Destination $styleTarget -Force
$html=[IO.File]::ReadAllText($builtIndex)
$html=[regex]::Replace($html,'proforma-document\.css\?v=[0-9-]+','proforma-document.css?v=20261017-5')
if(!$html.Contains('proforma-document.css')){
 if(!$html.Contains('</head>')){throw 'Missing head in compiled application'}
 $html=$html.Replace('</head>','<link rel="stylesheet" href="./static/css/proforma-document.css?v=20261017-5"></head>')
}
[IO.File]::WriteAllText($builtIndex,$html,(New-Object Text.UTF8Encoding($false)))
Write-Output 'Proforma typography staged. Source template includes the same stylesheet for future builds.'
