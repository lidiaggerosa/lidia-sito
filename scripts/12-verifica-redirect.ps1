# scripts/12-verifica-redirect.ps1 — verifica dei redirect di migration/mappa-301.csv
#
# Chiama ogni vecchio indirizzo sull'host indicato, senza seguire i redirect, e confronta
# status e destinazione con la mappa. Scrive migration/verifica-redirect.csv.
#
# Usa curl.exe (incluso in Windows 10/11): negozia da solo l'autenticazione delle Protected URLs
# e non segue i redirect. Invoke-WebRequest con MaximumRedirection 0 non completa la
# negoziazione e risponde sempre 401 (verificato il 22/09).
#
# Uso, da PowerShell nella cartella del progetto:
#   powershell -ExecutionPolicy Bypass -File .\scripts\12-verifica-redirect.ps1
#       staging: chiede utente e password delle Protected URLs
#   powershell -ExecutionPolicy Bypass -File .\scripts\12-verifica-redirect.ps1 -Base "https://lidiatech.ai" -NoAuth
#       produzione, dopo il cutover
#
# Righe con tipo "nessuno" (stesso indirizzo nel nuovo sito) e 301 verso pagine ancora da creare
# (motivo con "in attesa") vengono saltate e contate a parte.

param(
    [string]$Base = "https://staging.lidiatech.ai",
    [switch]$NoAuth
)

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
$mappa = Join-Path $root "migration\mappa-301.csv"
$report = Join-Path $root "migration\verifica-redirect.csv"
$ua = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) verifica-redirect"

# Le credenziali passano a curl come file di configurazione su stdin (-K -):
# non compaiono nella riga di comando ne' nella cronologia.
$config = ""
if (-not $NoAuth) {
    $u = Read-Host "Utente Protected URLs"
    $p = Read-Host "Password" -AsSecureString
    $plain = (New-Object System.Management.Automation.PSCredential($u, $p)).GetNetworkCredential().Password
    $config = "user = `"$u`:$plain`"`nanyauth`n"
    $plain = $null
}

function Chiama([string]$url) {
    # restituisce "status url_di_redirect"
    $out = $config | & curl.exe -s -K - -A $ua -o NUL --max-time 20 -w "%{http_code} %{redirect_url}" $url
    return "$out"
}

# Riscaldamento: la prima richiesta da un client nuovo riceve un 403 dal filtro anti-bot di SiteGround.
$null = Chiama "$Base/"
Start-Sleep -Milliseconds 500

$righe = Import-Csv $mappa -Encoding UTF8
$esiti = @()
$ok = 0; $ko = 0; $saltati = 0

foreach ($r in $righe) {
    $percorso = $r.da -replace '^https?://[^/]+', ''
    if (-not $percorso) { $percorso = "/" }

    if ($r.tipo -eq "nessuno" -or ($r.tipo -eq "301" -and ($r.a -notmatch '^/' -or $r.motivo -match 'in attesa'))) {
        $saltati++
        $esiti += [pscustomobject]@{ da = $percorso; atteso = $r.tipo; ottenuto = "-"; destinazione = "-"; esito = "saltato"; motivo = $r.motivo }
        continue
    }

    $risposta = Chiama "$Base$percorso"
    $parti = $risposta.Trim() -split ' ', 2
    $status = [int]$parti[0]
    $loc = if ($parti.Count -gt 1) { $parti[1] } else { "" }
    # la destinazione torna assoluta: si confronta solo il percorso
    $locPath = $loc -replace '^https?://[^/]+', ''

    $atteso = [int]$r.tipo
    $esito = "KO"
    if ($status -eq $atteso) {
        if ($atteso -eq 410) { $esito = "OK" }
        elseif ($locPath -eq $r.a) { $esito = "OK" }
    }
    if ($esito -eq "OK") { $ok++ } else { $ko++ }

    $esiti += [pscustomobject]@{ da = $percorso; atteso = $r.tipo; ottenuto = $status; destinazione = $locPath; esito = $esito; motivo = $r.motivo }
    Write-Host ("{0,-4} {1,3} -> {2,3}  {3}  {4}" -f $esito, $r.tipo, $status, $percorso, $locPath)

    # Con credenziali sbagliate ci si fa bloccare l'IP da SiteGround: meglio fermarsi subito.
    if ($status -eq 401 -and ($ok + $ko) -ge 3 -and $ok -eq 0) {
        Write-Host ""
        Write-Host "Tre 401 di fila: utente o password delle Protected URLs non accettati. Mi fermo per non far bloccare l'IP."
        break
    }
}

$esiti | Export-Csv $report -NoTypeInformation -Encoding UTF8
Write-Host ""
Write-Host "OK: $ok   KO: $ko   saltati: $saltati   -> $report"
