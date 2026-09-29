# Tracciamento e consenso

- **Un solo container GTM.** GA4, Meta, LinkedIn e Google Ads vivono dentro GTM. Nessun tag nel tema, nessuno snippet nell'admin.
- Il container lo stampa il mu-plugin `tracking/lidia-tracking.php`, solo con `LIDIA_ENV=production` e mai per utenti loggati.
- Ordine nell'`<head>`: consent default (tutto `denied`) → `lidia_page_context` → GTM.
- Consenso: Complianz, con Consent Mode v2. Il default lo stampa il mu-plugin, non Complianz.
- Unica conversione: evento `lidia_lead` al submit del modulo.

## File

| File | Cosa |
|---|---|
| `tracking/datalayer-spec.md` | eventi e parametri: fonte di verità |
| `tracking/gtm-container.json` | export del container, ID sostituiti da segnaposto |
| `tracking/gtm-import.md` | come importarlo |
| `tracking/utm-convention.md` | convenzione UTM |
| `scripts/06-tracking.md` | collegamento del mu-plugin in locale, costanti |

## Regole

- Nessun dato personale nel dataLayer, nemmeno in hash.
- Dopo ogni pubblicazione in GTM: riesportare il container, rimettere i segnaposto al posto degli ID, aggiornare il file.
- Test obbligatorio dopo ogni modifica: con consenso **rifiutato** nessun cookie di marketing.
