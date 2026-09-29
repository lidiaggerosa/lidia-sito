# Ambienti e rilascio

## Ambienti

| | Locale | Staging | Produzione |
|---|---|---|---|
| URL | WordPress Studio | `staging.lidiatech.ai` | `lidiatech.ai` |
| Cartella sul server | — | `~/www/staging.lidiatech.ai/public_html` | `~/www/lidiatech.ai/public_html` |
| `LIDIA_ENV` | `local` | `staging` | `production` |
| Indicizzazione | no | no (`noindex` + Protected URLs) | sì |
| Webhook Delera | di prova | di prova | veri |

Hosting SiteGround. Accesso SSH: Site Tools → Devs → SSH Keys Manager. Chiave, utente e server
li dà l'owner: non si scrivono nel repo.

## Costanti in `wp-config.php` (solo i nomi)

`LIDIA_ENV` · `LIDIA_GTM_ID` · `LIDIA_DELERA_WEBHOOK_PROVA` · `LIDIA_DELERA_WEBHOOK_WHITEPAPER` ·
`LIDIA_FORM_ALERT_EMAIL` · `LIDIA_DIETRO_PROXY` · `LIDIA_WHITEPAPER_MAIL_DELERA` ·
`DISALLOW_FILE_EDIT` · `DISABLE_WP_CRON` · `AUTOMATIC_UPDATER_DISABLED`

## Rilascio

Sempre prima su staging, poi in produzione con OK dell'owner.

**0. Backup del database**

```powershell
ssh -p 18765 -i $KEY $S "cd ~/www/<sito>/public_html && wp db export ~/backup-<sito>-<data>.sql"
```

**1. Tema e mu-plugin** (da PowerShell, nella cartella del repo, `$S` = `utente@server`, `$KEY` = chiave)

```powershell
scp -P 18765 -i $KEY -r theme\lidia\* "${S}:www/<sito>/public_html/wp-content/themes/lidia/"
scp -P 18765 -i $KEY tracking\lidia-tracking.php "${S}:www/<sito>/public_html/wp-content/mu-plugins/"
```

`scp` non cancella: un file tolto dal repo va tolto a mano anche sul server.

**2. Pagine cambiate**

```powershell
scp -P 18765 -i $KEY scripts\blocchi\<file>.html "${S}:scripts/blocchi/"
ssh -p 18765 -i $KEY $S "cd ~/www/<sito>/public_html && wp post update <ID> ~/scripts/blocchi/<file>.html --user=<admin>"
```

ID con `wp post list --post_type=page --name=<slug> --field=ID`. `--user` serve per le pagine con blocchi HTML (home, prodotto).

**3. Cache**

```powershell
ssh -p 18765 -i $KEY $S "cd ~/www/<sito>/public_html && wp sg purge https://<sito>/"
```

**4. Verifica** — in incognito: home, `/prodotto/`, un whitepaper, un URL inesistente (404 di Lidia),
invio del modulo di prova → contatto in Delera. Poi Ctrl+F5.

## Regole pratiche

- Un comando `ssh`/`scp` per volta: ognuno chiede la passphrase.
- In PowerShell `$( … )` si espande in locale: per usare un ID fare due passi.
- I PDF dei whitepaper si caricano dal pannello «Documento protetto» del whitepaper, mai dalla Libreria media.
- Mai un nuovo import WXR su un sito popolato: duplica i contenuti.
- Se il sito risponde 500, 410 o 503 dopo un rilascio dei redirect: `cp .htaccess.pre-redirect .htaccess && wp sg purge`.

## Rollback

- Codice: `git revert` del commit, poi rilascio come sopra.
- Database: `wp db import ~/backup-<sito>-<data>.sql` e purge.

Dettaglio completo del server e ricostruzione da zero: `scripts/10-staging.md`.
