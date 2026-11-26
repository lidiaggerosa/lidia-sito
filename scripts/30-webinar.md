# 30 — Webinar Teams: /risorse/webinar/{slug}/

Dal 18/11/2026, online in produzione dal 26/11. Una pagina per webinar (CPT `webinar`), indicizzabile, fuori dal menu.

**Modo attuale (dal 26/11, webinar del 9/12): link unico via Delera.** Modulo della pagina → Delera
(`LIDIA_DELERA_WEBHOOK_WEBINAR`) → flusso «Richiesta Webinar» → mail di conferma con link Teams e `.ics` allegato →
attesa fino al giorno del webinar ore 9 → mail di promemoria con il link. Il sito non iscrive su Teams: in produzione
`LIDIA_TEAMS_TENANT_ID` non c'è e la conferma dice «Ti mandiamo via email il link per partecipare.».
Teams: **riunione normale** (non webinar con registrazione: chi non è registrato su Teams vede «Non hai accesso a questo evento»),
link nella forma `https://teams.microsoft.com/meet/<ID>?p=<passcode>`. Prima di usarlo, aprirlo in una finestra anonima.

**Modo automatico (da gennaio, sotto i §1–2 e il §4 vecchio):** il sito iscrive su Teams via Microsoft Graph. Va attivato
con un organizzatore non personale, altrimenti le mail di Teams partono a nome della persona e finiscono in spam.
Codice: `inc/webinar.php`.

## 1. Costanti in wp-config.php (una volta per ambiente)

Da PowerShell, nella cartella del progetto. Il secret si inserisce con `Read-Host` e non si stampa (`--quiet`).

```powershell
$S    = "<utente>@<server>"
$WH   = "<URL webhook in entrata del flusso Delera>"
$Scad = "AAAA-MM-GG"                       # scadenza del client secret
$Sec  = Read-Host "Client secret"          # il VALORE del secret, non l'ID
ssh -p <porta> -i <chiave> $S "cd ~/www/lidiatech.ai/public_html && wp rewrite flush && wp config set LIDIA_DELERA_WEBHOOK_WEBINAR '$WH' --type=constant --quiet && wp config set LIDIA_TEAMS_TENANT_ID '<tenant>' --type=constant --quiet && wp config set LIDIA_TEAMS_CLIENT_ID '<client>' --type=constant --quiet && wp config set LIDIA_TEAMS_CLIENT_SECRET '$Sec' --type=constant --quiet && wp config set LIDIA_TEAMS_SECRET_SCADENZA '$Scad' --type=constant --quiet && wp config set LIDIA_TEAMS_ORGANIZZATORE '<ID oggetto Entra dell'organizzatore>' --type=constant --quiet && wp config set LIDIA_WEBINAR_TOKEN `$(openssl rand -hex 32) --type=constant --quiet && wp sg purge https://lidiatech.ai/"
```

Staging: stessa riga con `~/www/staging.lidiatech.ai/public_html` e `https://staging.lidiatech.ai/`.
Tenant, client e organizzatore sono nel `wp-config.php` dei due ambienti (non nel repo).

App Entra «Lidia - Iscrizioni webinar» (IT): permessi applicativi `VirtualEventRegistration-Anon.ReadWrite.All` e
`VirtualEvent.Read.All` con consenso dell'amministratore; application access policy di Teams `Lidia-Webinar`
assegnata all'organizzatore (serve un amministratore Teams: `New-CsApplicationAccessPolicy` e
`Grant-CsApplicationAccessPolicy`). Un organizzatore nuovo vuole la sua `Grant-`.
Rinnovo del secret: nuovo secret in Entra, poi `CLIENT_SECRET` e `SECRET_SCADENZA` nei due ambienti. L'admin avvisa 30 giorni prima.

Controllo: `wp eval-file ~/scripts/31-verifica-teams.php` (costanti, token, permessi, webinar del tenant da oggi).
Errori tipici: `invalid_client` = secret sbagliato o cambiato; 403 su Graph = manca la policy Teams.

## 2. Il webinar in Teams

Teams → Calendario → Nuova riunione ▾ → **Webinar** (non riunione, non municipio), creato dall'organizzatore,
pubblico «Chiunque», registrazione attiva, **Pubblica**. Poi l'ID:

```bash
wp lidia webinar elenco                         # webinar dell'organizzatore
wp eval-file ~/scripts/31-verifica-teams.php    # se il webinar nuovo non compare: tutti i webinar del tenant da oggi
```

## 3. Pagina

Blocchi in `scripts/blocchi/webinar/<slug>.html`, copy in `content/it/webinar/<slug>.md` (approvato prima, registro tu).
Modello: `lidia-live-lab-dicembre-2026.html`. Due sezioni:
1. `lidia-wbn-testa`: a sinistra occhiello, H1, descrizione, voci (Data e Orario con binding `lidia/webinar` chiavi `data` e `orario`, poi Relatrice, Partecipazione); a destra `lidia-wbn-modulo` con `<!-- wp:lidia/modulo {"tipo":"webinar","posizione":"hero"} /-->`.
2. `lidia-wbn-programma`: «Cosa vedremo», righe numerate, chiusura.

Pubblicazione (prima staging, poi `-Ambiente produzione`). I campi sono tra virgolette doppie
(un apostrofo tipografico dentro virgolette singole rompe PowerShell):

```powershell
$Campi = @{ lidia_webinar_inizio = "2026-12-09T14:00"; lidia_webinar_fine = "2026-12-09T14:45"; lidia_webinar_teams_id = "<id>"; _yoast_wpseo_title = "<title>"; _yoast_wpseo_metadesc = "<meta description>" }
.\scripts\26-pubblica.ps1 -Tipo webinar -Pagina <slug> -Titolo "<titolo>" -Campi $Campi
```

Webinar successivo: `lidia_webinar_prossimo = '<ID>'` sul webinar vecchio. Dopo la fine il modulo sparisce da solo.

## 4. Flusso Delera «Richiesta Webinar»

**Modo attuale:** webhook in entrata → Crea contatto → attribuzioni → opportunità pipeline **Webinar**, fase **Iscritto** →
tag `webinar-<data>` → Invia email «Conferma iscrizione webinar» (oggetto, link, `.ics` allegato) → Attendi «fino a una data/ora specifica» (giorno del webinar, 9:00) →
Invia email «Promemoria webinar». Per un webinar nuovo: tag, link nelle due mail, `.ics` (in `theme/lidia/assets/calendario/`), data dell'attesa.

**Modo automatico (gennaio):**

Cartella «Attivi Sito». Webhook in entrata → Crea contatto → attribuzioni (dati sito, primo contatto e click id) →
Crea opportunità pipeline **Webinar**, fase **Iscritto** → tag `webinar-<data>` → **Webhook personalizzato**
«Iscrizione Teams via sito» (azione premium):
- `POST https://lidiatech.ai/wp-json/lidia/v1/webinar-registration`, Autorizzazione None
- intestazione `X-Lidia-Token: <LIDIA_WEBINAR_TOKEN di produzione>` (lo incolla l'owner)
- corpo: `{"first_name": "{{contact.first_name}}", "last_name": "{{contact.last_name}}", "email": "{{contact.email}}", "webinar_slug": "{{inboundWebhookRequest.webinar}}"}`

Per un webinar nuovo cambia solo il tag. Il trigger vuole una richiesta di esempio («Recupera richieste di esempio») la prima volta.
Lo staging è dietro Basic Auth: verso lo staging serve Autorizzazione «Basic Auth» con le credenziali di Protected URLs.

Risposte: 200 iscritto o già iscritto · 202 Teams non risponde, il sito riprova (5 min, 30 min, 3 h) ·
400 dati mancanti · 401 token · 404 webinar inesistente · 410 webinar concluso · 502 Teams ha rifiutato ·
503 configurazione mancante. Su 202 e 502 finali parte la mail a `LIDIA_FORM_ALERT_EMAIL`: iscrizione da fare a mano.

## 5. Verifica e pulizia

- 1440, 768, 390: titolo, voci, modulo a destra (sotto su mobile).
- `wp lidia webinar iscrivi --slug=<slug> --nome=… --cognome=… --email=<esterna>` → iscrizione su Teams senza Delera.
- Invio dal modulo → Delera, Registri di esecuzione: tutti i passi verdi, l'ultimo con 200 → iscritto in Teams → mail di Teams (controllare lo spam).
- Iscrizioni di prova: da Teams non si annullano. `wp eval-file ~/scripts/32-iscrizioni-teams.php <slug>` (elenco) e
  `wp eval-file ~/scripts/32-iscrizioni-teams.php <slug> annulla <email>`.
- Dopo il webinar: report presenze di Teams → in Delera opportunità in «Ha partecipato» / «Non ha partecipato», poi a sales.
