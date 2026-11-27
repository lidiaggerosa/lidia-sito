<#
salva.ps1 - Salva su GitHub tutto quello che e' cambiato nella cartella del progetto.

Uso, da PowerShell nella cartella del progetto:

  .\strumenti\salva.ps1 "Cosa e' cambiato"

Crea un ramo, fa il commit, apre la pull request verso main e la unisce subito.
Lo lancia da solo pubblica.ps1 dopo ogni rilascio in produzione.
Prima del commit controlla che non ci siano password, token o chiavi: se ne trova, si ferma.
Richiede git e GitHub CLI (gh) con accesso fatto una volta: gh auth login
#>

param(
  [Parameter(Position = 0)][string]$Messaggio = ""
)

$ErrorActionPreference = "Stop"
$Progetto = Split-Path -Parent $PSScriptRoot
Set-Location $Progetto

function Esegui {
  & $args[0] @($args | Select-Object -Skip 1)
  if ($LASTEXITCODE -ne 0) { throw "Comando non riuscito: $($args -join ' ')" }
}

foreach ($c in @("git", "gh")) {
  if (-not (Get-Command $c -ErrorAction SilentlyContinue)) { throw "Manca $c. Installa con: winget install --id Git.Git -e ; winget install --id GitHub.cli -e" }
}

$ramo = (& git rev-parse --abbrev-ref HEAD).Trim()
if ($ramo -ne "main") { throw "Sei sul ramo '$ramo', non su main. Torna su main: git switch main" }

& git add -A
& git diff --cached --quiet
if ($LASTEXITCODE -eq 0) { Write-Host "Niente da salvare su GitHub." -ForegroundColor Green; exit 0 }

# --- Controllo segreti -------------------------------------------------------------------------
$sospetti = @(
  ('PRIVATE ' + 'KEY-----'),
  'ghp_[A-Za-z0-9]{30,}',
  'github_pat_[A-Za-z0-9_]{30,}',
  'ntn_[A-Za-z0-9]{30,}',
  'secret_[A-Za-z0-9]{30,}',
  'AKIA[0-9A-Z]{16}',
  'client_secret\s*[=:]\s*[''"]?[A-Za-z0-9~._-]{20,}',
  "define\(\s*'[A-Z_]*(TOKEN|SECRET|PASSWORD|PASS|WEBHOOK|KEY)[A-Z_]*'\s*,\s*'[^']{12,}'"
)
$aggiunte = & git diff --cached -U0 --no-color -- . ":(exclude)strumenti/salva.ps1" | Where-Object { $_.StartsWith("+") -and -not $_.StartsWith("+++") }
$trovati = @()
foreach ($r in $aggiunte) { foreach ($p in $sospetti) { if ($r -match $p) { $trovati += $r.Substring(0, [Math]::Min(80, $r.Length)); break } } }
if ($trovati.Count -gt 0) {
  & git reset -q
  Write-Host "Fermato: sembrano esserci credenziali nei file da salvare:" -ForegroundColor Red
  $trovati | ForEach-Object { Write-Host "  $_" }
  Write-Host "Toglile dal file (vanno in wp-config.php o in strumenti\server.ps1) e rilancia."
  exit 2
}

# --- Commit, PR, merge -------------------------------------------------------------------------
if (-not $Messaggio) { $Messaggio = "Aggiornamento del " + (Get-Date -Format "dd/MM/yyyy HH:mm") }
$nuovo = "rilascio/" + (Get-Date -Format "yyyyMMdd-HHmmss")

Esegui git switch -q -c $nuovo
Esegui git commit -q -m $Messaggio
Esegui git push -q -u origin $nuovo
Esegui gh pr create --base main --head $nuovo --title $Messaggio --body "Salvato da strumenti/salva.ps1"

& gh pr merge $nuovo --merge --delete-branch
if ($LASTEXITCODE -ne 0) { & gh pr merge $nuovo --merge --delete-branch --admin }
$unita = ($LASTEXITCODE -eq 0)

if ($unita) {
  & git switch -q main
  & git pull -q --ff-only origin main
  if (& git branch --list $nuovo) { & git branch -q -D $nuovo }
  Write-Host "Salvato su GitHub e unito a main: $Messaggio" -ForegroundColor Green
} else {
  Write-Host "PR aperta ma non unita. Resti sul ramo $nuovo (i file sono al sicuro)." -ForegroundColor Yellow
  Write-Host "Aprila con:  gh pr view $nuovo --web  -> Merge. Poi: git switch main ; git pull"
}
