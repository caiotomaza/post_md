$ErrorActionPreference = 'Stop'

$failed = $false

function Test-Command {
    param([Parameter(Mandatory)][string]$Name)

    if (Get-Command $Name -ErrorAction SilentlyContinue) {
        Write-Host "OK   $Name"
    }
    else {
        Write-Host "ERRO $Name não encontrado" -ForegroundColor Red
        $script:failed = $true
    }
}

Test-Command git
Test-Command docker

try {
    docker compose version | Out-Host
}
catch {
    Write-Host "ERRO Docker Compose v2 não encontrado." -ForegroundColor Red
    $failed = $true
}

if ($failed) {
    exit 1
}

Write-Host "Ambiente Docker disponível."
