# scripts/06-tracking — deploy del mu-plugin di tracciamento

> Il sorgente del mu-plugin è `tracking/lidia-tracking.php`, e resta l'unica copia.
> Il sito lo vede attraverso un **collegamento fisico** (hard link), come il tema lo vede
> attraverso la junction: un solo file su disco, nessuna copia da tenere allineata.
>
> Serve una volta sola per sito. Se il collegamento esiste già, ricrearlo non rompe niente.

---

## 1. Collegare il mu-plugin al sito locale

Da **PowerShell**. Non servono privilegi di amministratore: i collegamenti fisici non ne richiedono.
(`mklink` non esiste in PowerShell: è un comando interno di `cmd`. Il comando equivalente è `New-Item`.)

```powershell
New-Item -ItemType HardLink -Path "%USERPROFILE%\Studio\lidia-2026\wp-content\mu-plugins\lidia-tracking.php" -Target "<cartella del repo>\tracking\lidia-tracking.php"
```

La cartella `mu-plugins` esiste già — contiene `sqlite-database-integration`, che è ciò che fa
girare Studio su SQLite. Se il file `lidia-tracking.php` esiste già (una copia vecchia), il comando
fallisce: rimuoverlo prima con
`Remove-Item "%USERPROFILE%\Studio\lidia-2026\wp-content\mu-plugins\lidia-tracking.php"` e ripetere.

**Verifica:**

```
plugin list --status=must-use
```

Deve comparire `lidia-tracking` (versione 1.0.0). Accanto compare `99-studio-loader`, il loader
di Studio: `sqlite-database-integration` non è elencato perché vive in una sottocartella.

> ⚠ Il collegamento fisico funziona solo **sullo stesso volume**. Se il progetto e il sito Studio
> stanno su dischi diversi, `mklink /H` fallisce: in quel caso si copia il file e si aggiunge la
> copia al passo di rilascio, accettando la seconda copia come debito dichiarato.

---

## 2. Costanti in `wp-config.php`

**Non stanno nel repo.** Si scrivono a mano in
`%USERPROFILE%\Studio\lidia-2026\wp-config.php`, sopra la riga
`/* That's all, stop editing! */`.

```php
define( 'LIDIA_ENV', 'local' );
define( 'LIDIA_GTM_ID', 'GTM-XXXXXXX' );
define( 'LIDIA_FORCE_TRACKING', true );

define( 'LIDIA_DELERA_WEBHOOK_PROVA',      'https://…' );
define( 'LIDIA_DELERA_WEBHOOK_WHITEPAPER', 'https://…' );
define( 'LIDIA_FORM_ALERT_EMAIL',          'lidia@lidiatech.ai' );
```

`LIDIA_FORCE_TRACKING` accende il tracciamento fuori dalla produzione: serve solo in locale, per
la matrice di test della Fase 5. **Su staging non va messa**: staging resta senza tracciamento.

Per ambiente:

| Costante | locale | staging | produzione |
|---|---|---|---|
| `LIDIA_ENV` | `local` | `staging` | `production` |
| `LIDIA_GTM_ID` | il container vero | **assente** | il container vero |
| `LIDIA_FORCE_TRACKING` | `true` | assente | assente |

---

## 3. Verifica che il tracciamento parta

Il mu-plugin non stampa nulla per chi è loggato con permessi di redazione. La verifica si fa in
**finestra anonima**, altrimenti sembra rotto e non lo è.

Nel sorgente della pagina, in `<head>`, in quest'ordine:

1. un solo `gtag('consent','default', …)` con tutto `denied` tranne `functionality_storage` e
   `security_storage`;
2. il `dataLayer.push` con `event: 'lidia_page_context'`;
3. lo script di `googletagmanager.com/gtm.js`.

Se il consent default compare **due volte**, uno dei due lo sta stampando Complianz: va spento
lì, il default è responsabilità del mu-plugin. Se compare **dopo** GTM, il Consent Mode non
funziona e i dati sono inutilizzabili.
