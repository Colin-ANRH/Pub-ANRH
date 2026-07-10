# Exporte la base locale XAMPP vers un fichier SQL prêt pour OVH staging.
# Usage : .\deploy-ovh\export-staging-db.ps1

$ErrorActionPreference = 'Stop'

$mysql   = 'C:\xampp\mysql\bin\mysqldump.exe'
$dbLocal = 'anrhpub_db'
$dbOvh   = 'anrservipubanrh'
$out     = Join-Path (Split-Path $PSScriptRoot -Parent) 'export-pubanrh-ovh.sql'
$tempDump = Join-Path $env:TEMP "anrhpub-dump-$(Get-Date -Format 'yyyyMMddHHmmss').sql"

if (-not (Test-Path $mysql)) {
    Write-Error "mysqldump introuvable : $mysql (démarrez XAMPP / MySQL)"
}

Write-Host "Export de $dbLocal vers $out (utf8mb4)..."

# Évite la corruption d'accents via le pipe PowerShell : mysqldump écrit directement le fichier.
& $mysql `
    -u root `
    --default-character-set=utf8mb4 `
    --single-transaction `
    --routines `
    --triggers `
    --result-file="$tempDump" `
    $dbLocal

if (-not (Test-Path $tempDump)) {
    Write-Error "Export mysqldump échoué (fichier temporaire absent)."
}

$utf8NoBom = New-Object System.Text.UTF8Encoding $false
$content = [System.IO.File]::ReadAllText($tempDump, $utf8NoBom)
$content = $content `
    -replace 'http://localhost:8080/ANRPUB', 'https://pub.anrh.fr' `
    -replace 'http:\\/\\/localhost:8080\\/ANRPUB', 'https:\\/\\/pub.anrh.fr'

$header = @(
    "-- Export ANRHPUB staging pour OVH"
    "-- Base cible phpMyAdmin : $dbOvh"
    "-- Charset : utf8mb4"
    ""
    "SET NAMES utf8mb4;"
    "SET CHARACTER SET utf8mb4;"
    ""
    "USE ``$dbOvh``;"
    ""
) -join "`n"

[System.IO.File]::WriteAllText($out, $header + "`n" + $content, $utf8NoBom)
Remove-Item -Path $tempDump -Force -ErrorAction SilentlyContinue

Write-Host "OK - importez $out dans phpMyAdmin (base $dbOvh, interclassement utf8mb4_unicode_ci)."
