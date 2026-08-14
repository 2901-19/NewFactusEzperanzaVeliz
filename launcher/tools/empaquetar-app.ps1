$ErrorActionPreference = 'Stop'

$raiz = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$destino = Join-Path $raiz 'launcher\app-bundle'

if (Test-Path $destino) { Remove-Item -Recurse -Force $destino }
New-Item -ItemType Directory -Path $destino -Force | Out-Null

$excluir = @('node_modules', '.git', '.agents', 'tests', 'launcher', 'docs', '.env', '.env.example', '.phpunit.result.cache', 'skills-lock.json', '.gitattributes', '.editorconfig')

Get-ChildItem -Force -LiteralPath $raiz | Where-Object { $_.Name -notin $excluir } | ForEach-Object {
    Copy-Item -Recurse -Force -LiteralPath $_.FullName -Destination (Join-Path $destino $_.Name)
}

Push-Location $destino
try {
    & php artisan config:clear
    & composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction
} finally {
    Pop-Location
}

Get-ChildItem (Join-Path $destino 'bootstrap\cache') -File -ErrorAction SilentlyContinue | Where-Object { $_.Name -ne '.gitignore' } | Remove-Item -Force

foreach ($sub in @('cache\data', 'sessions', 'views', 'logs')) {
    $dir = Join-Path $destino "storage\framework\$sub"
    if (Test-Path $dir) {
        Get-ChildItem -Force -LiteralPath $dir | Where-Object { $_.Name -ne '.gitignore' } | Remove-Item -Recurse -Force
    }
}

$envDev = Get-Content (Join-Path $raiz '.env') -Raw
function Valor([string]$clave) {
    if ($envDev -match "(?m)^$clave=(.*)$") { return $Matches[1].Trim() }
    return ''
}

$appKey = Valor 'APP_KEY'
$dbName = Valor 'DB_DATABASE'
$dbUser = Valor 'DB_USERNAME'
$dbPass = Valor 'DB_PASSWORD'

$envCliente = @"
APP_NAME="Factus Esperanza Veliz"
APP_ENV=production
APP_KEY=$appKey
APP_DEBUG=false
APP_URL=http://127.0.0.1:8000

APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_VE

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=$dbName
DB_USERNAME=$dbUser
DB_PASSWORD=$dbPass

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=file

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="factus@localhost"
MAIL_FROM_NAME="`${APP_NAME}"

APP_TIMEZONE=America/Caracas
VITE_APP_NAME="`${APP_NAME}"
"@

Set-Content -Path (Join-Path $destino '.env') -Value $envCliente -Encoding UTF8

Write-Host "App empaquetada en $destino"
