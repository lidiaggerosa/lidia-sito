# scripts/24-genera-redirect.ps1 — genera migration/redirect.htaccess da migration/mappa-301.csv
#
# La mappa è l'unica fonte: redirect.htaccess non si modifica a mano.
# Per ogni riga: 301 -> RedirectMatch 301 ^<percorso>/?$ <destinazione>
#                410 -> RedirectMatch 410 ^<percorso>/?$
#                nessuno -> nessuna regola
# 301 senza destinazione interna o con «in attesa» nel motivo -> riga commentata.
#
# Uso, da PowerShell nella cartella del progetto:
#   powershell -ExecutionPolicy Bypass -File .\scripts\24-genera-redirect.ps1
# Poi caricamento e verifica: scripts/10-staging.md, sezione «Redirect».

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
$mappa = Join-Path $root "migration\mappa-301.csv"
$uscita = Join-Path $root "migration\redirect.htaccess"
$oggi = Get-Date -Format "yyyy-MM-dd"

$righe = @(
    "# Redirect da lidiatech.ai (Joomla) al nuovo sito — generato da migration/mappa-301.csv con scripts/24-genera-redirect.ps1 il $oggi"
    "# Va in testa a .htaccess, sopra il blocco WordPress. Non modificare a mano: cambiare la mappa e rigenerare."
    "# Solo percorsi: il 301 di host da www a senza www sta in migration/host-www.htaccess."
    "# Mai regole su /index.php: WordPress serve ogni pagina passando da lì."
    "<IfModule mod_alias.c>"
)

$n301 = 0; $n410 = 0; $nAttesa = 0; $visti = @{}

foreach ($r in (Import-Csv $mappa -Encoding UTF8)) {
    if ($r.tipo -eq "nessuno") { continue }

    $percorso = ($r.da -replace '^https?://[^/]+', '').TrimEnd('/')
    if (-not $percorso) { throw "Riga senza percorso: $($r.da)" }
    if ($percorso -eq "/index.php") { throw "Regola su /index.php rifiutata: metterebbe in errore l'intero sito." }
    if ($visti.ContainsKey($percorso)) { throw "Percorso duplicato nella mappa: $percorso" }
    $visti[$percorso] = $true

    $regex = $percorso -replace '([-.])', '\$1'

    switch ($r.tipo) {
        "301" {
            if ($r.a -notmatch '^/' -or $r.motivo -match 'in attesa') {
                $righe += "# in attesa: RedirectMatch 301 ^$regex/?$ $($r.a)"
                $nAttesa++
            } else {
                $righe += "RedirectMatch 301 ^$regex/?$ $($r.a)"
                $n301++
            }
        }
        "410" { $righe += "RedirectMatch 410 ^$regex/?$"; $n410++ }
        default { throw "Tipo non previsto '$($r.tipo)' per $($r.da)" }
    }
}

$righe += "</IfModule>"

# UTF-8 senza BOM: un BOM in testa a .htaccess rende invalida la prima riga e Apache risponde 500.
$testo = ($righe -join "`n") + "`n"
[System.IO.File]::WriteAllText($uscita, $testo, (New-Object System.Text.UTF8Encoding($false)))

Write-Host "301: $n301   410: $n410   in attesa: $nAttesa   -> $uscita"
