# lidia-sito

Codice del sito **lidiatech.ai**: tema WordPress, script di configurazione, copy, tracciamento, redirect.

Repository privato di Lidia S.r.l. (MESA Group). Owner: Gianluca Gerosa.

## Com'è fatto

| Voce | Valore |
|---|---|
| CMS | WordPress, block theme proprietario `lidia` (nessun tema padre, nessun page builder) |
| Hosting | SiteGround GrowBig, Francoforte |
| Ambienti | locale (WordPress Studio) · `staging.lidiatech.ai` · `lidiatech.ai` |
| Lingue | IT (primaria) + EN, con Polylang |
| SEO | Yoast + JSON-LD del tema |
| Consenso | Complianz, Consent Mode v2 |
| Tracciamento | un solo container GTM, stampato dal mu-plugin `lidia-tracking` |
| Moduli | blocco nativo `lidia/modulo` → Delera via webhook |

## Struttura

```
theme/lidia/     il tema: theme.json, template, parti, pattern, CSS, JS, inc/*.php
scripts/         comandi WP-CLI che creano e aggiornano pagine e impostazioni
scripts/blocchi/ il contenuto delle pagine, in markup di blocchi
content/         copy approvato (it/, en/), un .md per pagina
tracking/        spec dataLayer, export container GTM, convenzione UTM
migration/       mappa 301, regole di redirect, verifica
brand/logo/      logo vettoriale
docs/redazione/  guide per chi pubblica
docs/tecnico/    guide per chi sviluppa e rilascia
```

## Chi fa cosa

- **Redazione** — pubblica articoli e whitepaper dall'admin di WordPress. Non tocca il repo. → `docs/redazione/`
- **Sviluppo** — modifica tema, script e redirect con una pull request. → `docs/tecnico/` e `CONTRIBUTING.md`
- **Owner** — approva ogni pull request e ogni rilascio in produzione.

## Da leggere prima di iniziare

1. `CONTRIBUTING.md` — flusso di lavoro e regole.
2. `docs/tecnico/architettura.md` — come è costruito il tema.
3. `docs/tecnico/ambienti-e-rilascio.md` — come si porta una modifica online.

Alcuni commenti nel codice citano documenti interni non presenti qui (`docs/09-decision-log.md`,
`docs/01a-concept.md`, `docs/10-guida-redazione.md`…). Le parti operative sono riportate in `docs/`.
Per il resto, si chiede all'owner.
