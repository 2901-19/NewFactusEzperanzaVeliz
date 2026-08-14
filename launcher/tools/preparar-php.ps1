$ErrorActionPreference = 'Stop'

$origen = 'C:\php'
$destino = Join-Path $PSScriptRoot '..\php-bundle'

if (Test-Path $destino) { Remove-Item -Recurse -Force $destino }
New-Item -ItemType Directory -Path $destino -Force | Out-Null

if (-not (Test-Path (Join-Path $origen 'php.exe'))) {
    throw "No se encontro PHP en $origen. Ajusta la variable `$origen de este script."
}

Copy-Item -LiteralPath (Join-Path $origen 'php.exe') -Destination $destino -Force

Get-ChildItem -LiteralPath $origen -Filter '*.dll' -File | ForEach-Object {
    Copy-Item -LiteralPath $_.FullName -Destination $destino -Force
}

Copy-Item -Recurse -Force (Join-Path $origen 'ext') (Join-Path $destino 'ext')
Copy-Item -Recurse -Force (Join-Path $origen 'lib') (Join-Path $destino 'lib')

Write-Host "PHP portable listo en $destino"
