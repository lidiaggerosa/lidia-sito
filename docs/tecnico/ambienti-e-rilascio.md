# Ambienti e rilascio

| | Staging | Produzione |
|---|---|---|
| URL | `staging.lidiatech.ai` (Protected URLs, noindex) | `lidiatech.ai` |
| Cartella sul server | `~/www/staging.lidiatech.ai/public_html` | `~/www/lidiatech.ai/public_html` |
| `LIDIA_ENV` | `staging` | `production` |
| Webhook Delera | di prova | veri |
| Connettore WordPress.com (Jetpack) | no | sì |

Accessi SSH: `strumenti/server.ps1` e `strumenti/server.md` (solo sul PC, mai su GitHub).

## Costanti in `wp-config.php` (solo i nomi)
`LIDIA_ENV` · `LIDIA_GTM_ID` · `LIDIA_DELERA_WEBHOOK_PROVA` · `LIDIA_DELERA_WEBHOOK_WHITEPAPER` ·
`LIDIA_FORM_ALERT_EMAIL` · `LIDIA_DIETRO_PROXY` · `LIDIA_WHITEPAPER_MAIL_DELERA` ·
`LIDIA_NOTION_TOKEN` · `LIDIA_NOTION_DB_UTENTI` · costanti Teams (solo staging) ·
`DISALLOW_FILE_EDIT` · `DISABLE_WP_CRON` · `AUTOMATIC_UPDATER_DISABLED`

## Rilascio
Regole e comandi: `CLAUDE.md`, «Come si rilascia». Comandi in breve:

```powershell
.\strumenti\pubblica.ps1 prezzi                 # staging
.\strumenti\pubblica.ps1 prezzi -Produzione     # produzione + registro + GitHub
.\strumenti\pubblica.ps1 -Riallinea             # staging = produzione
.\strumenti\salva.ps1 "cosa"                    # solo GitHub
.\strumenti\aggiornamenti.ps1                   # controllo mensile WordPress e plugin
```

Mu-plugin `tracking/lidia-tracking.php`: si carica a mano (`strumenti/server.md`).
`-Tema` non cancella: un file tolto dal repo va tolto a mano anche sul server.

## Ripristino
- Pagina o tema: i comandi li stampa `pubblica.ps1` a fine rilascio (backup in `~/backup-pagine/`).
- Pagina modificata col connettore: admin → pagina → Revisioni.
- Codice su GitHub: `git revert` del commit, poi rilascio.
- Database: `wp db import <backup>.sql` e `wp sg purge`.
