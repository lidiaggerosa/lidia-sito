# Testi legali — cosa va in questa cartella

Servono gli **originali**, non una trascrizione. Da qui la conversione in pagine è meccanica
e fedele al carattere.

| File atteso | Dove prenderlo |
|---|---|
| `privacy-policy.pdf` | il PDF che è già online: `lidiatech.ai/images/privacy-policy_LIDIA.pdf` |
| `termini.html` | `lidiatech.ai/it/gtc` → Ctrl+S → «Pagina web, completa» |
| `dpa.html` | `lidiatech.ai/it/dpa` → Ctrl+S → «Pagina web, completa» |

Va bene qualunque nome: l'importante è che siano i file originali e che stiano qui.

## Cosa succede dopo

Le quattro pagine nascono sotto `/legale/` — `privacy`, `cookie`, `termini`, `dpa` — e sono
**tutte `noindex, nofollow`** per decisione del 17/09, oltre che fuori dalla sitemap. La regola
è nel tema (`inc/legale.php`), non in una spunta dell'admin: vale per costruzione, anche per le
pagine legali che verranno dopo.

La **cookie policy** non sta in questa lista: la genera Complianz in Fase 5 dalla scansione reale
degli script. Scriverla prima vorrebbe dire scriverla due volte, e quella scritta a mano sarebbe
comunque incompleta.
