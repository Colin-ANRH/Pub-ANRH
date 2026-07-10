# Exporte la base locale XAMPP vers un fichier SQL prêt pour OVH staging.
# Usage : .\deploy-ovh\export-staging-db.ps1

$ErrorActionPreference = 'Stop'

$mysql   = 'C:\xampp\mysql\bin\mysqldump.exe'
$dbLocal = 'anrhpub_db'
$dbOvh   = 'anrservipubanrh'
$out     = Join-Path (Split-Path $PSScriptRoot -Parent) 'export-pubanrh-ovh.sql'

if (-not (Test-Path $mysql)) {
    Write-Error "mysqldump introuvable : $mysql (démarrez XAMPP / MySQL)"
}

Write-Host "Export de $dbLocal vers $out ..."
$header = @(
    "-- Export ANRHPUB staging pour OVH"
    "-- Base cible phpMyAdmin : $dbOvh"
    ""
    "USE ``$dbOvh``;"
    ""
)

$header | Set-Content -Path $out -Encoding UTF8

& $mysql -u root --single-transaction --routines --triggers $dbLocal |
    ForEach-Object {
        $_ -replace 'http://localhost:8080/ANRPUB', 'https://pub.anrh.fr' `
           -replace 'http:\\/\\/localhost:8080\\/ANRPUB', 'https:\\/\\/pub.anrh.fr'
    } |
    Add-Content -Path $out -Encoding UTF8

Write-Host "OK - importez $out dans phpMyAdmin (base $dbOvh)."
