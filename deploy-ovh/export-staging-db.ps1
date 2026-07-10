# Exporte la base locale XAMPP vers un fichier SQL prêt pour OVH staging.
# Usage : .\deploy-ovh\export-staging-db.ps1

$ErrorActionPreference = 'Stop'

$mysql = 'C:\xampp\mysql\bin\mysqldump.exe'
$db    = 'anrhpub_db'
$out   = Join-Path (Split-Path $PSScriptRoot -Parent) 'export-pubanrh-ovh.sql'

if (-not (Test-Path $mysql)) {
    Write-Error "mysqldump introuvable : $mysql (démarrez XAMPP / MySQL)"
}

Write-Host "Export de $db vers $out ..."
& $mysql -u root --single-transaction --routines --triggers $db |
    ForEach-Object {
        $_ -replace 'http://localhost:8080/ANRPUB', 'https://pub.anrh.fr' `
           -replace 'http:\\/\\/localhost:8080\\/ANRPUB', 'https:\\/\\/pub.anrh.fr'
    } |
    Set-Content -Path $out -Encoding UTF8

Write-Host "OK — importez ce fichier dans phpMyAdmin (base anrservipubanrh)."
