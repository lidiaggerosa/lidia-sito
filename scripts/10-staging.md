# scripts/10-staging — deploy su staging.lidiatech.ai

> Eseguito per la prima volta il 21/09/2026, sopra un
> WordPress già scaricato e installato il 18/09. È la versione **come eseguita**, non quella
> teorica del runbook `docs/tecnico/ambienti-e-rilascio.md` §4: dove le due divergono, vale questa.
>
> Da Windows si usa **PowerShell con il client OpenSSH integrato** (niente WSL, niente rsync):
> `ssh` per i comandi sul server, `scp` per i file. Chiave: `%USERPROFILE%\.ssh\<chiave>`
> Server: `<server>.siteground.biz`, porta `18765`, utente in Site Tools → Devs → SSH Keys Manager →
> SSH Credentials. La password del database non si scrive mai nel comando: `--prompt`.

---

## Stato del server (28/09/2026)

| Voce | Valore |
|---|---|
| Cartella del sito | `~/www/staging.lidiatech.ai/public_html` |
| WordPress | 7.1.1, `it_IT`, installato, utente admin dedicato |
| Tema | `lidia` 1.0.0-alpha, attivo, copia del **28/09** (con `consenso.js` per «Preferenze cookie») |
| Plugin | `wordpress-seo` 28.5, `sg-cachepress` 7.8.2, `polylang` 3.8.9 (configurato da `17-polylang.php`); `complianz-gdpr` 7.5.5 free (wizard fatto a mano il 25/09, banner stilizzato dal tema); `google-site-kit` inattivo; mu-plugin `lidia-tracking` **1.1.0** (ponte consenso, 28/09) |
| Costanti | `LIDIA_ENV=staging`, `WP_DEBUG` su log, `DISALLOW_FILE_EDIT`, `DISABLE_WP_CRON`, `LIDIA_DIETRO_PROXY`, `LIDIA_FORM_ALERT_EMAIL`, i due webhook **di prova** Delera, `AUTOMATIC_UPDATER_DISABLED` (28/09), `LIDIA_GTM_ID` + `LIDIA_FORCE_TRACKING` (28/09, per i test del consenso: **togliere `LIDIA_FORCE_TRACKING` dopo lo switch**) |
| Contenuti | 23 (16 pagine, 2 articoli, 5 whitepaper `lidia_gated`), da `migration/export/lidia-2026-staging-2026-09-21.xml` |
| Home | `page_on_front` = pagina `home` (7); permalink `/risorse/%postname%/` |
| ID pagine | home 7, prodotto 22, sicurezza 26, prezzi 28, prova-gratuita 52, contatti 53, azienda 54, lavora-con-noi 55, legale 57, privacy 58, dpa 59, termini 60, cookie 70 (shortcode Complianz), annex-a 117, annex-b 142. EN pubblicate: home-en 118, 145-150 |
| Backup | `~/backup-staging-2026-09-28.sql` (ultimo), `~/backup-staging-2026-09-25.sql` (prima di Polylang) |
| Pagine EN | 118, 145-150 **pubblicate** (28/09), contenuto da `scripts/blocchi/en/` via `wp eval-file ~/scripts/19-contenuti-en.php`. Alla pubblicazione: `wp post update <ID> --post_status=publish` e `wp rewrite flush` |
| Mock | home 7 e prodotto 22 contengono blocchi HTML con script: aggiornarle sempre con `--user=<admin>` |
| Protezione | Protected URLs su `/` (utente `marketing@lidiatech.ai`) + `X-Robots-Tag: noindex, nofollow` in `.htaccess`; `blog_public 0` |
| Redirect | 54 × 301 e 50 × 410 (25/09; prima 52 × 301) da `migration/mappa-301.csv`, in testa a `.htaccess` (22/09). Copia pulita senza redirect: `.htaccess.pre-redirect` |
| Cron | Site Tools, ogni 5 minuti: `cd ~/www/staging.lidiatech.ai/public_html && wp cron event run --due-now` |
| Cache | Dynamic cache + memcached via SG Optimizer; esclusioni `/wp-json/lidia/v1/*`, `*?lidia=*`, `*?lidia_dl=*`; CDN spenta |
| Webhook Delera | PROVA → workflow «test richiesta contatto»; WHITEPAPER → workflow «Test download». Verificati con un lead per ciascuno |
| PDF protetti | 5 file in `~/www/staging.lidiatech.ai/riservati/` (**fuori da `public_html`**, nessun server web li serve). Caricati dal pannello «Documento protetto» del whitepaper; `lidia_file` = 79, 81, 83, 85, 87. Spostati dalla vecchia `uploads/riservati/` con `scripts/13-sposta-riservati.php` |
| `/libreria/` | pagina `private` con il blocco `lidia/libreria`: tutte le sezioni del tema con scheda (`scripts/14-libreria.md`). Si vede solo con accesso fatto |
| WebP | attiva per i nuovi upload; per un'immagine importata per altre vie: `wp media regenerate <ID>` |

## Produzione (29/09/2026)

| Voce | Valore |
|---|---|
| Cartelle | `~/www/lidiatech.ai/public_html` + `~/www/lidiatech.ai/riservati/` |
| IP | `<IP del server>` |
| Database | `<nome db>`, utente condiviso con lo staging (da separare) |
| Costanti | come lo staging, tranne `LIDIA_ENV=production`, `WP_DEBUG=false`, niente debug log, niente `LIDIA_FORCE_TRACKING`, webhook veri |
| Plugin attivi | Complianz, Polylang, SG Optimizer, Yoast; mu-plugin `lidia-tracking` 1.1.0 (RSSSL e Complianz T&C disattivati) |
| Protezione fino a T-0 | `noindex` in `.htaccess`, `blog_public 0`; nessuna Protected URL |
| `.htaccess` | righe PHP 8.4 di Site Tools in testa (originali in `~/prod-originali/`), poi quello dello staging |
| Cron | Site Tools, ogni 5 minuti: `cd ~/www/lidiatech.ai/public_html && wp cron event run --due-now` |
| Anteprima senza DNS | `curl.exe -kI --resolve lidiatech.ai:443:<IP del server> https://lidiatech.ai/` oppure `Start-Process chrome "--user-data-dir=$env:TEMP\chrome-lidia --host-resolver-rules=`"MAP lidiatech.ai <IP del server>`" --ignore-certificate-errors https://lidiatech.ai/"` |

---

## Rilascio (ogni volta che cambia il tema, il mu-plugin o un file di blocchi)

Da PowerShell, nella cartella del progetto:

```powershell
$S = "<utente>@<server>.siteground.biz"
cd "<cartella del repo>"
scp -P 18765 -i $KEY -r theme\lidia\* "${S}:www/staging.lidiatech.ai/public_html/wp-content/themes/lidia/"
scp -P 18765 -i $KEY tracking\lidia-tracking.php "${S}:www/staging.lidiatech.ai/public_html/wp-content/mu-plugins/"
```

`scp` non cancella i file rimossi dal tema: se un file sparisce dal repo va tolto a mano sul server
(`rm wp-content/themes/lidia/<file>`), altrimenti resta e diverge. (`-P` maiuscola in `scp`,
minuscola in `ssh`.)

**Più comandi `wp` con una sola passphrase:** in una stessa riga `ssh`, uniti con `&&` dentro le
virgolette (25/09). Se uno fallisce, i successivi non partono.

Poi il purge della cache — **con l'URL**, senza dà «Incorrect URL!» (23/09). Il warning
«Unable to Purge File Cache» è normale: quel livello non è attivo sullo staging.

```powershell
ssh -p 18765 -i $KEY $S "cd ~/www/staging.lidiatech.ai/public_html && wp sg purge https://staging.lidiatech.ai/"
```

**Un comando per volta:** ogni `scp`/`ssh` chiede la passphrase; se si incollano più righe insieme
la seconda finisce dentro la prima (23/09: lo `scp` del tema è finito dentro le virgolette di un
`grep` e il tema non è stato caricato). Dopo ogni rilascio del tema: Ctrl+F5 sulla pagina.

**`$(...)` in PowerShell** viene espanso in locale anche dentro le virgolette doppie: per l'ID di
una pagina si fa in due passi (`wp post list … --field=ID`, poi `wp post update <ID> …`), oppure
si scrive `` `$( … ) `` con l'apice inverso.

**PDF dei whitepaper:** si caricano dall'admin, pagina del whitepaper → colonna destra → «Documento
protetto» → «Scegli il PDF» → Salva. Mai dalla Libreria media (finirebbero in una cartella pubblica,
e il pannello lo segnala in giallo).

**Dopo un test «senza JavaScript»** riattivare JavaScript per `staging.lidiatech.ai` in Chrome
(impostazioni del sito): il blocco vale anche in incognito e lascia l'admin bianco (23/09).

Le pagine si aggiornano dai file di blocchi, mai dall'editor: `scp` del file in `~/scripts/blocchi/`
e `wp post update <ID> ~/scripts/blocchi/<file>.html` (ID con `wp post list --post_type=page --name=<slug> --field=ID`).
Sullo staging la home è il post **7** (23/09).

**Un nuovo import WXR non si rifà**: duplicherebbe i contenuti. Se serve ripartire da zero,
`wp post delete $(wp post list --post_type=any --post_status=any --format=ids) --force` e poi la
sezione «Ricostruzione» qui sotto.

---

## Redirect della mappa 301 (ogni volta che cambia `migration/mappa-301.csv`)

Il file `migration/redirect.htaccess` è **generato** dalla mappa (regole `RedirectMatch`, una per
riga, ancorate a inizio e fine percorso). Non si modifica a mano. Va in testa a `.htaccess`, sopra
il blocco WordPress; `.htaccess.pre-redirect` è la copia senza redirect e resta sul server.

```powershell
scp -P 18765 -i $KEY migration\redirect.htaccess "${S}:www/staging.lidiatech.ai/public_html/redirect.htaccess"
ssh -p 18765 -i $KEY $S "cd ~/www/staging.lidiatech.ai/public_html && cat redirect.htaccess .htaccess.pre-redirect > .htaccess && rm redirect.htaccess && wp sg purge && wc -l .htaccess"
```

Verifica (chiede utente e password delle Protected URLs; scrive `migration/verifica-redirect.csv`):

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\12-verifica-redirect.ps1
```

Atteso: `KO: 0`. Il 22/09: 102 OK, 0 KO, 8 saltati (5 URL identici nel nuovo sito, 2 annex in
attesa della pagina, `/index.php`).

**Mai una regola su `/index.php`:** WordPress serve ogni pagina passando da lì, e una `RedirectMatch`
su quel percorso mette in 410 l'intero sito (successo il 22/09, ripristinato da `.htaccess.pre-redirect`).
Se il sito risponde 410 o 503 dopo un caricamento: `cp .htaccess.pre-redirect .htaccess && wp sg purge`.

**Anti-bot e blocco IP:** la prima richiesta da un client nuovo riceve un 403; più di qualche
decina di 401 consecutivi dallo stesso IP e SiteGround lo blocca (503 e pagine a metà anche nel
browser). Lo script si ferma da solo dopo tre 401.

---

## Ricostruzione da zero (se il sito va rifatto)

```bash
cd ~/www/staging.lidiatech.ai/public_html
wp core download --locale=it_IT
wp config create --dbname=<db> --dbuser=<utente_db> --dbhost=localhost --locale=it_IT --prompt=dbpass
wp core install --url=https://staging.lidiatech.ai --title="Lidia" --admin_user=<admin> --admin_email=<email admin> --prompt=admin_password --skip-email
```

Costanti (`wp config set NOME valore`, con `--raw` per `true`/`false`): l'elenco è nella tabella
sopra. I file arrivano con gli `scp` del rilascio, più il WXR:

```powershell
scp -P 18765 -i $KEY migration\export\lidia-2026-staging-<data>.xml "${S}:export.xml"
```

Poi, in SSH, le righe di `00-bootstrap.md` §1, §3, §6, §7 (tranne `core language install`, già fatto
dal download), e:

```bash
wp theme activate lidia
wp plugin install wordpress-seo --activate
wp plugin install wordpress-importer --activate
wp import ~/export.xml --authors=create
wp plugin deactivate wordpress-importer && wp plugin delete wordpress-importer
wp search-replace 'http://localhost:8882' 'https://staging.lidiatech.ai' --all-tables --precise --dry-run   # attesi: solo guid
wp search-replace 'http://localhost:8882' 'https://staging.lidiatech.ai' --all-tables --precise
wp option update show_on_front page
wp option update page_on_front $(wp post list --post_type=page --name=home --field=ID)
wp rewrite structure "/risorse/%postname%/" --hard
```

`.htaccess`: **WP-CLI non lo scrive** su SiteGround (`rewrite flush --hard` non ha i permessi), e
senza il file tutto tranne la home dà il 404 di SiteGround. Si crea a mano:

```bash
cat > .htaccess <<'EOF'
<IfModule mod_headers.c>
Header set X-Robots-Tag "noindex, nofollow"
</IfModule>
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
EOF
cp .htaccess .htaccess.pre-redirect      # poi la sezione «Redirect» qui sopra
wp rewrite flush
wp plugin install sg-cachepress --activate
wp sg purge
```

A mano, in Site Tools: Protected URLs su `/`; cron ogni 5 minuti (il campo «Minuto» non accetta
`*/5`: usare il preset o `0,5,10,…,55`); Speed → Caching (dynamic + memcached, CDN spenta).
A mano, in WordPress → Speed Optimizer → Caching: le tre esclusioni URL. È l'unica configurazione
del sito che non ha un comando: eccezione dichiarata nel decision log del 21/09.

**Non** usare `wget` su `wp-cron.php` come cron: Protected URLs risponde 401 e la coda del modulo
non riparte.

---

## Verifica dopo ogni rilascio

```bash
wp theme list --status=active --format=csv
wp plugin list --status=must-use --format=csv
wp option get blog_public                         # 0
wp option get lidia_delera_coda --format=json     # errore "does it exist?" = nessun lead in attesa: bene
wp cron event list --fields=hook,next_run_relative --format=csv
```

Da browser in incognito: home, `/prodotto/`, un whitepaper, un URL inesistente (404 di Lidia, non
di SiteGround), invio del modulo su `/prova-gratuita/` → contatto nel workflow di prova Delera.
Il resto della checklist è in `docs/tecnico/ambienti-e-rilascio.md` §5.
