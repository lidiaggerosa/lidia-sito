<#
pubblica.ps1 - Pubblica pagine e file del tema su staging (default) o in produzione.

Uso, da PowerShell nella cartella del progetto:

  .\strumenti\pubblica.ps1 prezzi                                   # pagina su staging
  .\strumenti\pubblica.ps1 prezzi -Produzione                       # stessa pagina in produzione
  .\strumenti\pubblica.ps1 nuova -Titolo "Titolo" -Genitore prodotto # crea una pagina
  .\strumenti\pubblica.ps1 prezzi -Tema assets\css\sezioni\prezzi.css -Yoast
  .\strumenti\pubblica.ps1 -Tema assets\css\base.css                # solo tema
  .\strumenti\pubblica.ps1 appuntamento -Tipo landing               # /lp/<slug>/ (pagine\lp-<slug>.html)
  .\strumenti\pubblica.ps1 nome -Tipo webinar -Titolo "Titolo" -Campi @{ lidia_webinar_inizio = '2026-12-10T17:00' }
  .\strumenti\pubblica.ps1 -Riallinea                               # porta su staging tutto DISALLINEAMENTI.md

  -Nota "testo"   descrizione della riga in REGISTRO.md
  -NoGit          in produzione non salva su GitHub

File dei blocchi: pagine\<slug>.html (inglese: -Blocchi en\<file>.html; webinar: pagine\webinar\<slug>.html).
Se PowerShell blocca lo script: powershell -ExecutionPolicy Bypass -File .\strumenti\pubblica.ps1 ...

Cosa fa: controlla i blocchi sul server, salva un backup di pagine e file del tema, pubblica,
svuota la cache. Poi scrive REGISTRO.md e, in produzione, segna in DISALLINEAMENTI.md cio' che
non e' passato da staging e salva su GitHub (strumenti\salva.ps1).
#>

param(
  [Parameter(Position = 0)][string]$Pagina,
  [string]$Blocchi,
  [string]$Titolo,
  [string]$Genitore,
  [string]$Template = "page-composta",
  [string[]]$Tema = @(),
  [switch]$Yoast,
  [ValidateSet("page", "webinar", "landing")]
  [string]$Tipo = "page",
  [hashtable]$Campi = @{},
  [switch]$Produzione,
  [switch]$Riallinea,
  [string]$Nota = "",
  [switch]$NoGit
)

$ErrorActionPreference = "Stop"

# --- Percorsi e server ----------------------------------------------------------------------
$Progetto       = Split-Path -Parent $PSScriptRoot
$CartellaTema   = Join-Path $Progetto "theme\lidia"
$CartellaPagine = Join-Path $Progetto "pagine"
$FileYoast      = Join-Path $PSScriptRoot "yoast.php"
$FileDis        = Join-Path $Progetto "DISALLINEAMENTI.md"
$FileReg        = Join-Path $Progetto "REGISTRO.md"
$FileHash       = Join-Path $PSScriptRoot ".staging.json"
$TitoloDis      = "## In produzione, non su staging"
$Utf8           = New-Object System.Text.UTF8Encoding($false)

$FileServer = Join-Path $PSScriptRoot "server.ps1"
if (-not (Test-Path $FileServer)) { throw "Manca strumenti\server.ps1 (accessi al server)." }
. $FileServer   # definisce $S, $Porta, $Chiave

# --- Funzioni --------------------------------------------------------------------------------
function Leggi($f) { if (Test-Path $f) { return [IO.File]::ReadAllText($f, $Utf8) } else { return "" } }
function Scrivi($f, $t) { [IO.File]::WriteAllText($f, $t, $Utf8) }
function Impronta($f) { return (Get-FileHash -Algorithm SHA256 -LiteralPath $f).Hash }
function Chiave-Di($full) { return ($full.Substring($Progetto.Length + 1) -replace '\\', '/') }

function Blocchi-Di($tipo, $slug) {
  switch ($tipo) {
    "webinar" { return "webinar\$slug.html" }
    "landing" { return "lp-$slug.html" }
    default   { return "$slug.html" }
  }
}

function File-Tema($t) {
  $p = Join-Path $CartellaTema $t
  if (Test-Path $p -PathType Container) { return @(Get-ChildItem $p -Recurse -File | ForEach-Object { $_.FullName }) }
  return @($p)
}

function Leggi-Dis {
  $voci = @()
  $dentro = $false
  foreach ($r in ((Leggi $FileDis) -split "`r?`n")) {
    if ($r.StartsWith("## ")) { $dentro = ($r.Trim() -eq $TitoloDis); continue }
    if (-not $dentro -or -not $r.StartsWith("|")) { continue }
    $c = @($r.Trim().Trim("|").Split("|") | ForEach-Object { $_.Trim().Trim('`').Trim() })
    if ($c.Count -lt 4) { continue }
    if ($c[0] -eq "Data" -or $c[0] -match '^-+$') { continue }
    $voci += [pscustomobject]@{ Riga = $r; Data = $c[0]; Tipo = $c[1]; Voce = $c[2]; File = $c[3] }
  }
  return ,$voci
}

function Aggiungi-Dis($tipo, $voce, $file, $nota) {
  foreach ($v in (Leggi-Dis)) { if ($v.Tipo -eq $tipo -and $v.Voce -eq $voce) { return } }
  $colFile = if ($file) { "``$file``" } else { "-" }
  $riga = "| $(Get-Date -Format 'dd/MM/yyyy') | $tipo | ``$voce`` | $colFile | $nota |"
  $righe = [System.Collections.Generic.List[string]]((Leggi $FileDis) -split "`r?`n")
  $dentro = $false
  for ($k = 0; $k -lt $righe.Count; $k++) {
    $r = $righe[$k]
    if ($r.StartsWith("## ")) { $dentro = ($r.Trim() -eq $TitoloDis); continue }
    if ($dentro -and $r -match '^\|\s*-') {
      $righe.Insert($k + 1, $riga)
      Scrivi $FileDis ($righe -join "`n")
      Write-Host "Segnato in DISALLINEAMENTI.md: $tipo $voce" -ForegroundColor Yellow
      return
    }
  }
  throw "In DISALLINEAMENTI.md manca la tabella '$TitoloDis'."
}

function Togli-Dis($voci) {
  if ($voci.Count -eq 0) { return }
  $via = @{}
  foreach ($v in $voci) { $via[$v.Riga] = 1 }
  $righe = @((Leggi $FileDis) -split "`r?`n" | Where-Object { -not $via.ContainsKey($_) })
  Scrivi $FileDis ($righe -join "`n")
  Write-Host "Tolte da DISALLINEAMENTI.md: $($voci.Count) voci" -ForegroundColor Cyan
}

function Aggiungi-Registro($ambiente, $cosa, $file, $backup) {
  $riga = "| $(Get-Date -Format 'dd/MM/yyyy HH:mm') | $ambiente | $cosa | $file | $backup |"
  $righe = [System.Collections.Generic.List[string]]((Leggi $FileReg) -split "`r?`n")
  for ($k = 0; $k -lt $righe.Count; $k++) {
    if ($righe[$k] -match '^\|\s*-') {
      $righe.Insert($k + 1, $riga)
      Scrivi $FileReg ($righe -join "`n")
      return
    }
  }
  Write-Host "REGISTRO.md senza tabella: riga non scritta." -ForegroundColor Yellow
}

function Leggi-Impronte {
  $h = @{}
  $t = Leggi $FileHash
  if ($t.Trim()) { ($t | ConvertFrom-Json).PSObject.Properties | ForEach-Object { $h[$_.Name] = $_.Value } }
  return $h
}

# --- Cosa pubblicare -------------------------------------------------------------------------
$Voci = @()        # pagine
$VociTema = @()    # percorsi relativi a theme\lidia
$FaiYoast = [bool]$Yoast

if ($Riallinea) {
  if ($Produzione) { throw "-Riallinea vale solo per lo staging." }
  if ($Pagina -or $Tema.Count -gt 0 -or $Yoast) { throw "-Riallinea si usa da solo." }
  $daRiallineare = Leggi-Dis
  if ($daRiallineare.Count -eq 0) { Write-Host "Niente da riallineare." -ForegroundColor Green; exit 0 }
  foreach ($v in $daRiallineare) {
    if ($v.Tipo -eq "tema") { $VociTema += $v.Voce; continue }
    if ($v.Tipo -eq "yoast") { $FaiYoast = $true; continue }
    if (@("page", "webinar", "landing") -notcontains $v.Tipo) { throw "Tipo sconosciuto in DISALLINEAMENTI.md: $($v.Tipo)" }
    $b = if ($v.File -and $v.File -ne "-") { $v.File } else { Blocchi-Di $v.Tipo $v.Voce }
    $Voci += [pscustomobject]@{ Tipo = $v.Tipo; Slug = $v.Voce; Blocchi = $b; Titolo = ""; Genitore = ""; Template = $Template }
  }
} else {
  if ($Pagina) {
    $b = if ($Blocchi) { $Blocchi } else { Blocchi-Di $Tipo $Pagina }
    $Voci += [pscustomobject]@{ Tipo = $Tipo; Slug = $Pagina; Blocchi = $b; Titolo = $Titolo; Genitore = $Genitore; Template = $Template }
  }
  $VociTema = @($Tema)
  if (-not $Pagina -and $VociTema.Count -eq 0 -and -not $Yoast) { throw "Niente da pubblicare: indica una pagina, -Tema o -Yoast." }
  if ($Campi.Count -gt 0 -and -not $Pagina) { throw "-Campi vuole anche la pagina." }
}

foreach ($v in $Voci) {
  $v.Blocchi = $v.Blocchi -replace '/', '\'
  if (-not (Test-Path (Join-Path $CartellaPagine $v.Blocchi))) { throw "File di blocchi non trovato: pagine\$($v.Blocchi)" }
}
$VociTema = @($VociTema | ForEach-Object { $_ -replace '/', '\' })
foreach ($t in $VociTema) { if (-not (Test-Path (Join-Path $CartellaTema $t))) { throw "Nel tema non esiste: $t" } }
if ($FaiYoast -and -not (Test-Path $FileYoast)) { throw "Non trovato: strumenti\yoast.php" }

# --- Ambiente --------------------------------------------------------------------------------
if ($Produzione) {
  $Ambiente = "produzione"; $Sito = "www/lidiatech.ai/public_html"; $Url = "https://lidiatech.ai"
  Write-Host ""
  Write-Host "PRODUZIONE - lidiatech.ai" -ForegroundColor Red
  $ok = Read-Host "Scrivi PRODUZIONE per continuare"
  if ($ok -cne "PRODUZIONE") { Write-Host "Annullato."; exit 1 }
} else {
  $Ambiente = "staging"; $Sito = "www/staging.lidiatech.ai/public_html"; $Url = "https://staging.lidiatech.ai"
}
$Stamp = Get-Date -Format "yyyyMMdd-HHmmss"

# --- Pacchetto -------------------------------------------------------------------------------
$Lavoro = Join-Path $env:TEMP "lidia-pacchetto"
if (Test-Path $Lavoro) { Remove-Item $Lavoro -Recurse -Force }
New-Item -ItemType Directory -Path $Lavoro | Out-Null

function Copia($origine, $destinazione) {
  $dir = Split-Path -Parent $destinazione
  if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
  Copy-Item $origine $destinazione -Recurse -Force
}

foreach ($t in $VociTema) { Copia (Join-Path $CartellaTema $t) (Join-Path $Lavoro "tema\$t") }
foreach ($v in $Voci) { Copia (Join-Path $CartellaPagine $v.Blocchi) (Join-Path $Lavoro "scripts\blocchi\$($v.Blocchi)") }
if ($FaiYoast) { Copia $FileYoast (Join-Path $Lavoro "scripts\09-yoast.php") }

# Elenco delle pagine: un campo per colonna, separatore 0x1F
$Sep = [string][char]0x1F
$elenco = ""
foreach ($v in $Voci) {
  $elenco += (@($v.Tipo, $v.Slug, ($v.Blocchi -replace '\\', '/'), $v.Titolo, $v.Genitore, $v.Template) -join $Sep) + "`n"
}
Scrivi (Join-Path $Lavoro "elenco.txt") $elenco

# Controllo dei blocchi, eseguito sul server prima di scrivere le pagine
$Php = @'
<?php
// Uso: wp eval-file controlla-blocchi.php <file>
$file = $args[0] ?? '';
$c = @file_get_contents( $file );
if ( ! $c || trim( $c ) === '' ) { WP_CLI::error( "File vuoto o illeggibile: $file" ); }

$errori = [];
if ( stripos( $c, 'lorem ipsum' ) !== false ) { $errori[] = 'contiene lorem ipsum'; }

preg_match_all( '#<!--\s+wp:([a-z0-9/-]+)(\s+\{.*?\})?\s+(/)?-->#s', $c, $aperti, PREG_SET_ORDER );
$chiusi = preg_match_all( '#<!--\s+/wp:([a-z0-9/-]+)\s+-->#', $c );
$daChiudere = 0;
foreach ( $aperti as $a ) { if ( empty( $a[3] ) ) { $daChiudere++; } }
if ( $daChiudere !== $chiusi ) { $errori[] = "blocchi aperti $daChiudere, chiusi $chiusi"; }

$registro = WP_Block_Type_Registry::get_instance();
$sconosciuti = [];
$vagante = false;
$visita = function ( $blocchi, $livello ) use ( &$visita, $registro, &$sconosciuti, &$vagante ) {
  foreach ( $blocchi as $b ) {
    if ( $b['blockName'] === null ) {
      if ( $livello === 0 && trim( $b['innerHTML'] ) !== '' ) { $vagante = true; }
      continue;
    }
    $nome = strpos( $b['blockName'], '/' ) === false ? 'core/' . $b['blockName'] : $b['blockName'];
    if ( ! $registro->is_registered( $nome ) ) { $sconosciuti[ $nome ] = true; }
    $visita( $b['innerBlocks'], $livello + 1 );
  }
};
$visita( parse_blocks( $c ), 0 );
if ( $vagante ) { $errori[] = 'HTML fuori dai blocchi'; }
if ( $sconosciuti ) { $errori[] = 'blocchi non registrati: ' . implode( ', ', array_keys( $sconosciuti ) ); }

if ( $errori ) { WP_CLI::error( basename( $file ) . ' - ' . implode( '; ', $errori ) ); }
WP_CLI::success( basename( $file ) . ': blocchi validi' );
'@
Scrivi (Join-Path $Lavoro "controlla-blocchi.php") $Php.Replace("`r`n", "`n")

# Script eseguito sul server
function Q($s) { if ($null -eq $s) { return "''" }; return "'" + ($s -replace "'", "'\''") + "'" }

$RigheCampi = @(":")
foreach ($c in $Campi.GetEnumerator()) {
  $val = [string]$c.Value
  if ($val -eq "") { $RigheCampi += "  wp post meta delete `"`$1`" $(Q ([string]$c.Key)) || true" }
  else { $RigheCampi += "  wp post meta update `"`$1`" $(Q ([string]$c.Key)) $(Q $val)" }
}
$FunzioneCampi = "campi() {`n  " + ($RigheCampi -join "`n") + "`n}"
$CampiFlag = if ($Campi.Count -gt 0) { "1" } else { "" }
$TemaFlag  = if ($VociTema.Count -gt 0) { "1" } else { "" }
$YoastFlag = if ($FaiYoast) { "1" } else { "" }

$Bash = @"
set -e
AMBIENTE=$(Q $Ambiente)
SITO=~/$Sito
URL=$(Q $Url)
STAMP=$(Q $Stamp)
P=~/lidia-pacchetto
TEMA=$TemaFlag
YOAST=$YoastFlag
CAMPI=$CampiFlag
$FunzioneCampi
"@ + @'

SEP=$'\x1f'
B=~/backup-pagine/$AMBIENTE-$STAMP
mkdir -p "$B" ~/scripts/blocchi
cd "$SITO"
: > "$B/ripristino.txt"

# 1. Controllo dei blocchi di tutte le pagine, prima di toccare qualunque cosa
while IFS="$SEP" read -r -u 3 TIPO SLUG BLOCCHI TITOLO GENITORE TEMPLATE; do
  wp eval-file "$P/controlla-blocchi.php" "$P/scripts/blocchi/$BLOCCHI"
done 3< "$P/elenco.txt"

# 2. Tema: backup dei file che verranno sovrascritti, poi copia
if [ -n "$TEMA" ]; then
  T="$SITO/wp-content/themes/lidia"
  ( cd "$P/tema" && find . -type f ) | while read -r f; do
    if [ -f "$T/$f" ]; then mkdir -p "$B/tema/$(dirname "$f")"; cp "$T/$f" "$B/tema/$f"; fi
  done
  cp -r "$P/tema/." "$T/"
  echo "Tema: $(cd "$P/tema" && find . -type f | wc -l) file caricati"
  echo "Tema:   cp -r $B/tema/. $T/ && cd $SITO && wp sg purge $URL/" >> "$B/ripristino.txt"
fi

# 3. File di blocchi e Yoast
if [ -d "$P/scripts" ]; then cp -r "$P/scripts/." ~/scripts/; fi

# 4. Pagine: backup, poi aggiornamento o creazione
CREATA=""
ULTIMO=""
while IFS="$SEP" read -r -u 3 TIPO SLUG BLOCCHI TITOLO GENITORE TEMPLATE; do
  ID=$(wp post list --post_type="$TIPO" --name="$SLUG" --post_status=any --field=ID)
  if [ "$(echo "$ID" | grep -c .)" -gt 1 ]; then echo "Piu' contenuti con slug $SLUG: $ID"; exit 1; fi
  if [ -n "$ID" ]; then
    wp post get "$ID" --field=post_content > "$B/pagina-$SLUG-$ID.html"
    wp post update "$ID" ~/scripts/blocchi/"$BLOCCHI" --user=gianluca
    echo "$SLUG: cd $SITO && wp post update $ID $B/pagina-$SLUG-$ID.html --user=gianluca && wp sg purge $URL/" >> "$B/ripristino.txt"
  else
    if [ -z "$TITOLO" ]; then echo "$SLUG non esiste su $AMBIENTE: per crearla serve -Titolo"; exit 1; fi
    if [ "$TIPO" = "page" ]; then
      PARENT=0
      if [ -n "$GENITORE" ]; then
        PARENT=$(wp post list --post_type=page --name="$GENITORE" --field=ID)
        if [ -z "$PARENT" ]; then echo "Pagina madre non trovata: $GENITORE"; exit 1; fi
      fi
      ID=$(wp post create ~/scripts/blocchi/"$BLOCCHI" --post_type=page --post_status=publish \
        --post_title="$TITOLO" --post_name="$SLUG" --post_parent="$PARENT" \
        --page_template="$TEMPLATE" --user=gianluca --porcelain)
    else
      ID=$(wp post create ~/scripts/blocchi/"$BLOCCHI" --post_type="$TIPO" --post_status=publish \
        --post_title="$TITOLO" --post_name="$SLUG" --user=gianluca --porcelain)
    fi
    CREATA=1
    echo "$SLUG (creata): cd $SITO && wp post delete $ID --force && wp sg purge $URL/" >> "$B/ripristino.txt"
  fi
  ULTIMO=$ID
  echo "$SLUG -> $(wp post list --post_type="$TIPO" --post__in="$ID" --field=url)"
done 3< "$P/elenco.txt"

# 4b. Campi personalizzati (una sola pagina)
if [ -n "$CAMPI" ] && [ -n "$ULTIMO" ]; then campi "$ULTIMO"; echo "Campi scritti su $ULTIMO"; fi

# 5. Yoast, permalink, cache
if [ -n "$YOAST" ]; then wp eval-file ~/scripts/09-yoast.php --user=gianluca; fi
if [ -n "$CREATA" ]; then wp rewrite flush; fi
wp sg purge "$URL/" || true
if [ "$AMBIENTE" = "produzione" ] && { [ -n "$CREATA" ] || [ -n "$YOAST" ]; }; then
  wp yoast index --reindex --skip-confirmation
fi

# 6. Riepilogo
echo ""
echo "== Fatto su $AMBIENTE =="
echo "Backup: $B"
if [ -s "$B/ripristino.txt" ]; then echo "Ripristino:"; cat "$B/ripristino.txt"; fi
rm -rf "$P"
'@
Scrivi (Join-Path $Lavoro "esegui.sh") $Bash.Replace("`r`n", "`n")

$Tar = Join-Path $env:TEMP "lidia-pacchetto.tar"
if (Test-Path $Tar) { Remove-Item $Tar -Force }
& tar.exe -cf $Tar -C $Lavoro .
if ($LASTEXITCODE -ne 0) { throw "Creazione del pacchetto non riuscita." }

# --- Invio ed esecuzione: una sola connessione, una sola passphrase --------------------------
Write-Host "Connessione a $Ambiente (passphrase della chiave)..."
$Remoto = "rm -rf ~/lidia-pacchetto && mkdir ~/lidia-pacchetto && tar -xf - -C ~/lidia-pacchetto && bash ~/lidia-pacchetto/esegui.sh"

$psi = New-Object System.Diagnostics.ProcessStartInfo
$psi.FileName = (Get-Command ssh.exe).Source
$psi.Arguments = "-p $Porta -i `"$Chiave`" $S `"$Remoto`""
$psi.UseShellExecute = $false
$psi.RedirectStandardInput = $true

$proc = [System.Diagnostics.Process]::Start($psi)
$flusso = [System.IO.File]::OpenRead($Tar)
try { $flusso.CopyTo($proc.StandardInput.BaseStream) } catch { } finally { $flusso.Close(); try { $proc.StandardInput.Close() } catch { } }
$proc.WaitForExit()
$esito = $proc.ExitCode

Remove-Item $Lavoro -Recurse -Force
Remove-Item $Tar -Force

if ($esito -ne 0) { Write-Host "Errore (codice $esito): niente e' stato pubblicato dopo il passo fallito." -ForegroundColor Red; exit $esito }

# --- Registro --------------------------------------------------------------------------------
$fileTesto = @()
foreach ($v in $Voci) { $fileTesto += "``pagine/$($v.Blocchi -replace '\\', '/')``" }
foreach ($t in $VociTema) { $fileTesto += "``theme/lidia/$($t -replace '\\', '/')``" }
if ($FaiYoast) { $fileTesto += "``strumenti/yoast.php``" }
$cosa = if ($Nota) { $Nota } elseif ($Riallinea) { "Riallineamento staging da DISALLINEAMENTI.md" } else { "Pubblicazione" }
Aggiungi-Registro $Ambiente $cosa ($fileTesto -join ", ") "``~/backup-pagine/$Ambiente-$Stamp``"

# --- Disallineamenti -------------------------------------------------------------------------
$impronte = Leggi-Impronte

if ($Ambiente -eq "staging") {
  # staging ora ha queste versioni: le registro e tolgo le voci coperte
  foreach ($v in $Voci) { $f = Join-Path $CartellaPagine $v.Blocchi; $impronte[(Chiave-Di $f)] = Impronta $f }
  foreach ($t in $VociTema) { foreach ($f in (File-Tema $t)) { $impronte[(Chiave-Di $f)] = Impronta $f } }
  if ($FaiYoast) { $impronte[(Chiave-Di $FileYoast)] = Impronta $FileYoast }
  Scrivi $FileHash ($impronte | ConvertTo-Json)

  $pubblicatiTema = @($VociTema | ForEach-Object { $_ -replace '\\', '/' })
  $coperte = @()
  foreach ($d in (Leggi-Dis)) {
    $voce = $d.Voce -replace '\\', '/'
    if ($d.Tipo -eq "yoast" -and $FaiYoast) { $coperte += $d; continue }
    if ($d.Tipo -eq "tema") {
      foreach ($p in $pubblicatiTema) { if ($voce -eq $p -or $voce.StartsWith("$p/")) { $coperte += $d; break } }
      continue
    }
    foreach ($v in $Voci) { if ($d.Tipo -eq $v.Tipo -and $d.Voce -eq $v.Slug) { $coperte += $d; break } }
  }
  Togli-Dis $coperte
} else {
  # produzione: segno cio' che non coincide con l'ultima versione pubblicata su staging
  foreach ($v in $Voci) {
    $f = Join-Path $CartellaPagine $v.Blocchi
    if ($impronte[(Chiave-Di $f)] -ne (Impronta $f)) { Aggiungi-Dis $v.Tipo $v.Slug ($v.Blocchi -replace '\\', '/') "solo produzione" }
  }
  foreach ($t in $VociTema) {
    $diverso = $false
    foreach ($f in (File-Tema $t)) { if ($impronte[(Chiave-Di $f)] -ne (Impronta $f)) { $diverso = $true; break } }
    if ($diverso) { Aggiungi-Dis "tema" ($t -replace '\\', '/') "" "solo produzione" }
  }
  if ($FaiYoast -and $impronte[(Chiave-Di $FileYoast)] -ne (Impronta $FileYoast)) { Aggiungi-Dis "yoast" "yoast.php" "" "solo produzione" }
}

Write-Host "Fatto su $Ambiente. Ctrl+F5 sulla pagina." -ForegroundColor Green

# --- GitHub (solo produzione) ----------------------------------------------------------------
if ($Ambiente -eq "produzione" -and -not $NoGit) {
  $nomi = @($Voci | ForEach-Object { $_.Slug }) + @($VociTema)
  if ($FaiYoast) { $nomi += "yoast" }
  $messaggio = if ($Nota) { "Produzione: $Nota" } else { "Produzione: " + ($nomi -join ", ") }
  & (Join-Path $PSScriptRoot "salva.ps1") -Messaggio $messaggio
}
