<#
aggiornamenti.ps1 - Controllo e aggiornamento di WordPress, plugin e traduzioni su staging e produzione.

Si lancia quando arriva l'alert mensile (primo lunedi' del mese).

Uso (da PowerShell, nella cartella del progetto):

  # 1. controllo: versioni installate e aggiornamenti disponibili, staging e produzione
  .\strumenti\aggiornamenti.ps1

  # 2. aggiornamento su staging, poi verifica nel browser
  .\strumenti\aggiornamenti.ps1 -Aggiorna

  # 3. aggiornamento in produzione (chiede di scrivere PRODUZIONE)
  .\strumenti\aggiornamenti.ps1 -Aggiorna -Ambiente produzione

Se PowerShell blocca lo script:
  powershell -ExecutionPolicy Bypass -File .\strumenti\aggiornamenti.ps1

Cosa fa:
  - controllo: scrive docs\versioni-installate.md (lo legge l'alert mensile);
  - aggiornamento: backup del database e della cartella plugin, aggiornamento di WordPress,
    plugin e traduzioni (il tema lidia e il mu-plugin non si toccano), purge della cache,
    controllo che home e /prodotto/ rispondano 200, nuovo docs\versioni-installate.md,
    riga in REGISTRO.md.
#>

param(
  [switch]$Aggiorna,
  [ValidateSet("staging", "produzione")]
  [string]$Ambiente = "staging"
)

$ErrorActionPreference = "Stop"

# --- Server --------------------------------------------------------------------------------
$FileServer = Join-Path $PSScriptRoot "server.ps1"
if (-not (Test-Path $FileServer)) { throw "Manca strumenti\server.ps1 (accessi al server)." }
. $FileServer   # definisce $S, $Porta, $Chiave

$Progetto  = Split-Path -Parent $PSScriptRoot
$Registro  = Join-Path $Progetto "REGISTRO.md"
$Versioni  = Join-Path $Progetto "docs\versioni-installate.md"
$Stamp     = Get-Date -Format "yyyyMMdd-HHmmss"
$Utf8      = New-Object System.Text.UTF8Encoding($false)

if ($Aggiorna -and $Ambiente -eq "produzione") {
  Write-Host ""
  Write-Host "PRODUZIONE - lidiatech.ai" -ForegroundColor Red
  Write-Host "Aggiorna la produzione solo dopo aver verificato lo staging aggiornato."
  $ok = Read-Host "Scrivi PRODUZIONE per continuare"
  if ($ok -cne "PRODUZIONE") { Write-Host "Annullato."; exit 1 }
}

$Modo = if ($Aggiorna) { "aggiorna" } else { "controllo" }

# --- Script eseguito sul server ---------------------------------------------------------------
$Bash = @"
MODO='$Modo'
AMBIENTE='$Ambiente'
STAMP='$Stamp'
"@ + @'

sito_di()  { if [ "$1" = "staging" ]; then echo ~/www/staging.lidiatech.ai/public_html; else echo ~/www/lidiatech.ai/public_html; fi; }
url_di()   { if [ "$1" = "staging" ]; then echo https://staging.lidiatech.ai; else echo https://lidiatech.ai; fi; }

versioni() {
  cd "$(sito_di "$1")"
  NUOVO=$(wp core check-update --field=version 2>/dev/null | grep -E '^[0-9.]+$' | head -1)
  echo "### $1"
  echo ""
  echo "| Componente | Stato | Versione | Disponibile |"
  echo "|---|---|---|---|"
  echo "| WordPress | core | $(wp core version) | ${NUOVO:--} |"
  echo "| PHP | server | $(php -r 'echo PHP_VERSION;') | - |"
  wp plugin list --fields=name,status,version,update_version --format=csv 2>/dev/null \
    | awk -F, 'NR>1 { d = ($4 == "") ? "-" : $4; printf "| %s | %s | %s | %s |\n", $1, $2, $3, d }'
  T=$(wp language core list --status=installed --update=available --field=language 2>/dev/null | head -1)
  if [ -n "$T" ]; then echo "| Traduzioni | core | - | da aggiornare |"; fi
  echo ""
}

if [ "$MODO" = "controllo" ]; then
  echo "==VERSIONI-INIZIO=="
  versioni staging
  versioni produzione
  echo "==VERSIONI-FINE=="
  exit 0
fi

# --- Aggiornamento su un solo ambiente ---
set -e
SITO=$(sito_di "$AMBIENTE")
URL=$(url_di "$AMBIENTE")
B=~/backup-aggiornamenti/$AMBIENTE-$STAMP
mkdir -p "$B"
cd "$SITO"

echo "Backup del database e dei plugin in $B"
wp db export "$B/database.sql" --quiet
tar -czf "$B/plugins.tar.gz" -C wp-content plugins
wp core version > "$B/core-prima.txt"
wp plugin list --fields=name,version --format=csv > "$B/plugin-prima.csv"

echo "Aggiornamento di WordPress"
wp core update
wp core update-db
echo "Aggiornamento dei plugin"
wp plugin update --all
echo "Aggiornamento delle traduzioni"
wp language core update || true
wp language plugin update --all || true

wp sg purge "$URL/" || true

echo "Controllo delle pagine"
ERR=""
if [ "$AMBIENTE" = "produzione" ]; then
  for P in "/" "/prodotto/"; do
    C=$(curl -s -o /dev/null -w '%{http_code}' "$URL$P")
    echo "  $P -> $C"
    if [ "$C" != "200" ]; then ERR=1; fi
  done
else
  echo "  staging protetto: verifica nel browser"
fi

echo "==VERSIONI-INIZIO=="
versioni "$AMBIENTE"
echo "==VERSIONI-FINE=="

echo ""
echo "Backup: $B"
echo "Ripristino: cd $SITO && wp db import $B/database.sql && rm -rf wp-content/plugins && tar -xzf $B/plugins.tar.gz -C wp-content && wp core update --version=\$(cat $B/core-prima.txt) --force && wp sg purge $URL/"
if [ -n "$ERR" ]; then echo "ATTENZIONE: una pagina non risponde 200. Valuta il ripristino."; exit 3; fi
'@
$Bash = $Bash.Replace("`r`n", "`n")

# --- Esecuzione: una sola connessione ------------------------------------------------------------
Write-Host "Connessione al server (passphrase della chiave)..."
$Bash | & ssh.exe -p $Porta -i "$Chiave" $S "tr -d '\r' | bash -s" | Tee-Object -Variable Righe
$esito = $LASTEXITCODE

# --- Versioni installate -------------------------------------------------------------------------
$testoUscita = ($Righe -join "`n")
$i = $testoUscita.IndexOf("==VERSIONI-INIZIO==")
$f = $testoUscita.IndexOf("==VERSIONI-FINE==")
if ($i -ge 0 -and $f -gt $i) {
  $tabelle = $testoUscita.Substring($i + 19, $f - $i - 19).Trim()
  $data = Get-Date -Format "dd/MM/yyyy HH:mm"
  if (-not $Aggiorna) {
    $md = "# Versioni installate - lidiatech.ai`nGenerato da strumenti/aggiornamenti.ps1 il $data (controllo).`n`n$tabelle`n"
  } else {
    # tiene la tabella dell'altro ambiente dal file precedente
    $altro = ""
    if (Test-Path $Versioni) {
      $vecchio = [IO.File]::ReadAllText($Versioni, $Utf8)
      $nome = if ($Ambiente -eq "staging") { "produzione" } else { "staging" }
      $m = [regex]::Match($vecchio, "(?s)### $nome.*?(?=(\n### |\z))")
      if ($m.Success) { $altro = $m.Value.Trim() }
    }
    $md = "# Versioni installate - lidiatech.ai`nGenerato da strumenti/aggiornamenti.ps1 il $data (aggiornamento $Ambiente).`n`n$tabelle`n`n$altro`n"
  }
  [IO.File]::WriteAllText($Versioni, $md, $Utf8)
  Write-Host "Scritto: docs\versioni-installate.md" -ForegroundColor Cyan
}

# --- Registro rilasci (solo aggiornamenti) -------------------------------------------------------
if ($Aggiorna) {
  $esitoTesto = if ($esito -eq 0) { "ok, verifica nel browser" } elseif ($esito -eq 3) { "ERRORE pagine non 200" } else { "ERRORE codice $esito" }
  $data = Get-Date -Format "dd/MM/yyyy HH:mm"
  $riga = "| $data | $Ambiente | Aggiornamento WordPress, plugin e traduzioni ($esitoTesto) | ``docs/versioni-installate.md`` | ``~/backup-aggiornamenti/$Ambiente-$Stamp`` |"
  if (Test-Path $Registro) {
    $righe = [IO.File]::ReadAllText($Registro, $Utf8) -split "`n"
    $pos = -1
    for ($k = 0; $k -lt $righe.Count; $k++) { if ($righe[$k] -match '^\|\s*-') { $pos = $k; break } }
    if ($pos -ge 0) {
      $nuove = @($righe[0..$pos]) + $riga
      if ($pos + 1 -lt $righe.Count) { $nuove += $righe[($pos + 1)..($righe.Count - 1)] }
      [IO.File]::WriteAllText($Registro, ($nuove -join "`n"), $Utf8)
      Write-Host "Registro aggiornato: REGISTRO.md" -ForegroundColor Cyan
    }
  }
}

if ($esito -ne 0) { Write-Host "Esito: errore (codice $esito). Leggi le righe sopra." -ForegroundColor Red; exit $esito }
if ($Aggiorna -and $Ambiente -eq "staging") {
  Write-Host "Staging aggiornato. Verifica nel browser home, /prodotto/, un modulo; poi lancia con -Ambiente produzione." -ForegroundColor Green
} elseif ($Aggiorna) {
  Write-Host "Produzione aggiornata. Ctrl+F5 su home e /prodotto/." -ForegroundColor Green
} else {
  Write-Host "Controllo fatto." -ForegroundColor Green
}
