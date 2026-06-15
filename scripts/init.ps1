$ErrorActionPreference = 'Stop'

$Root = Split-Path -Parent $PSScriptRoot
Set-Location $Root

function Read-DotEnv {
    param([Parameter(Mandatory)][string]$Path)

    $values = @{}

    foreach ($line in Get-Content $Path) {
        if ($line -match '^\s*#' -or $line -notmatch '=') {
            continue
        }

        $parts = $line.Split('=', 2)
        $values[$parts[0].Trim()] = $parts[1].Trim()
    }

    return $values
}

if (-not (Test-Path '.env')) {
    Copy-Item '.env.example' '.env'
    Write-Host 'Criado .env a partir de .env.example.'
}

$config = Read-DotEnv '.env'

$LaravelVersion = if ($config['LARAVEL_VERSION']) { $config['LARAVEL_VERSION'] } else { '^13.0' }
$AppPort = if ($config['APP_PORT']) { $config['APP_PORT'] } else { '8000' }
$VitePort = if ($config['VITE_PORT']) { $config['VITE_PORT'] } else { '5173' }
$PostgresDb = if ($config['POSTGRES_DB']) { $config['POSTGRES_DB'] } else { 'post_md' }
$PostgresUser = if ($config['POSTGRES_USER']) { $config['POSTGRES_USER'] } else { 'post_md' }
$PostgresPassword = if ($config['POSTGRES_PASSWORD']) { $config['POSTGRES_PASSWORD'] } else { 'change_me_postgres' }
$RedisPassword = if ($config['REDIS_PASSWORD']) { $config['REDIS_PASSWORD'] } else { 'change_me_redis' }

New-Item -ItemType Directory -Force 'src', 'backups' | Out-Null

if (-not (Test-Path 'src/artisan')) {
    $unexpected = Get-ChildItem 'src' -Force -ErrorAction SilentlyContinue |
        Where-Object { $_.Name -ne '.gitkeep' }

    if ($unexpected) {
        Write-Host 'ERRO: src não está vazia e não contém Laravel.' -ForegroundColor Red
        $unexpected | Format-Table Name, Length, LastWriteTime
        exit 1
    }

    Remove-Item 'src/.gitkeep' -ErrorAction SilentlyContinue

    docker compose build app
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

    docker compose run --rm --no-deps app composer create-project `
        --prefer-dist `
        --no-interaction `
        "laravel/laravel:$LaravelVersion" .

    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

    $template = Get-Content 'docker/laravel/.env.example' -Raw
    $template = $template.
        Replace('__APP_PORT__', $AppPort).
        Replace('__VITE_PORT__', $VitePort).
        Replace('__POSTGRES_DB__', $PostgresDb).
        Replace('__POSTGRES_USER__', $PostgresUser).
        Replace('__POSTGRES_PASSWORD__', $PostgresPassword).
        Replace('__REDIS_PASSWORD__', $RedisPassword)

    Set-Content 'src/.env' $template -NoNewline -Encoding utf8

    $example = Get-Content 'docker/laravel/.env.example' -Raw
    $example = $example.
        Replace('__APP_PORT__', '8000').
        Replace('__VITE_PORT__', '5173').
        Replace('__POSTGRES_DB__', 'post_md').
        Replace('__POSTGRES_USER__', 'post_md').
        Replace('__POSTGRES_PASSWORD__', 'change_me').
        Replace('__REDIS_PASSWORD__', 'change_me')

    Set-Content 'src/.env.example' $example -NoNewline -Encoding utf8
}
else {
    Write-Host 'Laravel já existe em src. O create-project não será repetido.'
}

docker compose up -d db redis
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host 'Aguardando PostgreSQL e Redis...'
$ready = $false

for ($attempt = 1; $attempt -le 60; $attempt++) {
    docker compose exec -T db pg_isready -U $PostgresUser -d $PostgresDb *> $null
    $dbOk = $LASTEXITCODE -eq 0

    docker compose exec -T redis redis-cli -a $RedisPassword ping *> $null
    $redisOk = $LASTEXITCODE -eq 0

    if ($dbOk -and $redisOk) {
        $ready = $true
        break
    }

    Start-Sleep -Seconds 2
}

if (-not $ready) {
    Write-Host 'ERRO: PostgreSQL ou Redis não ficou pronto.' -ForegroundColor Red
    docker compose ps
    docker compose logs --tail=100 db redis
    exit 1
}

docker compose run --rm app php artisan key:generate --force
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

docker compose run --rm node npm install
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

docker compose run --rm node npm run build
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

docker compose run --rm app php artisan migrate --force
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

docker compose up -d
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host ''
Write-Host 'Inicialização concluída.' -ForegroundColor Green
Write-Host "Aplicação: http://localhost:$AppPort"
Write-Host "Vite:      http://localhost:$VitePort"
Write-Host ''
Write-Host 'Valide com:'
Write-Host 'docker compose ps'
Write-Host 'docker compose exec app php artisan test'
