# Architettura del tema

## File

```
theme/lidia/
├── theme.json            token: colori, tipografia, spaziature, larghezze
├── style.css             solo intestazione del tema
├── functions.php         carica i file di inc/
├── templates/            front-page, page, page-composta, page-feedback, single, single-landing, archive, search, 404, index
├── parts/                header, footer (+ versioni -en), lp-testata, lp-piede, fb-testata
├── patterns/             le sezioni, un file per sezione
├── inc/                  la logica PHP
├── assets/css/           tokens.css, base.css, parti/, sezioni/, components/
├── assets/js/            modulo, consenso, tracking, funzioni, feedback, editor
├── assets/fonts/         Fraunces e Inter, woff2 self-hosted
└── languages/            en_GB.po / .mo
```

## inc/

| File | Cosa fa |
|---|---|
| `setup.php` | supporti del tema, blocchi ammessi, pulizia dell'head, niente pattern core |
| `enqueue.php` | font, CSS base, CSS di sezione caricato solo dove la classe compare (`lidia_sezioni()`) |
| `patterns.php` | categorie dei pattern nell'inseritore |
| `post-types.php` | CPT `risorsa` (whitepaper, `/risorse/whitepaper/{slug}/`), tassonomie `tipo-risorsa` e `settore` |
| `meta.php` | campi del whitepaper: `lidia_gated`, `lidia_file`, `lidia_delera_tag` |
| `forms.php` | blocco `lidia/modulo` (tipo `prova` o `whitepaper`), validazione, antispam |
| `delera.php` | invio a Delera, coda di ritenta, download protetto dei PDF |
| `lingue.php` | blocco `lidia/lingua`, integrazione Polylang |
| `libreria.php` | blocco `lidia/libreria`: la pagina privata `/libreria/` con tutte le sezioni |
| `schema.php` | JSON-LD: Organization, FAQPage, SoftwareApplication |
| `seo.php` | robots.txt e llms.txt |
| `legale.php` | `noindex` del ramo `/legale/` |
| `landing.php` | CPT `landing` (`/lp/{slug}/`): template isolato, `noindex, nofollow`, fuori sitemap |
| `feedback.php` | blocco `lidia/feedback` (questionario `/feedback/`), `noindex, nofollow`, invio a Notion |

## Pagine

Le pagine sono fatte di pattern. Il contenuto di ogni pagina è un file in `pagine/`
(`pagine/en/` per l'inglese). `strumenti/pubblica.ps1` o il connettore WordPress.com la creano o la
aggiornano da quel file. Il copy approvato sta in `content/`.

Per cambiare una pagina: si modifica il file di blocchi e si rilascia (`CLAUDE.md`, «Come si rilascia»).
Mai solo dall'editor, altrimenti il repo non corrisponde più al sito.

Template `page-composta`: l'H1 sta dentro la prima sezione, non nel template.

## Aggiungere una sezione

1. Pattern in `patterns/<nome>.php`, con `Description` che dice dove e quante volte si usa.
2. Classe `lidia-<nome>` sul gruppo esterno.
3. CSS in `assets/css/sezioni/<nome>.css`, registrato in `lidia_sezioni()`.
4. Controllo su `/libreria/` e a 390 / 768 / 1440 px.

## Colori e registri

Scala blu: notte `#0F133A` per l'apertura e le fasce strumento, chiaro `#F4F5FB` per la lettura,
`#303A99` per l'azione. I valori vivono in `theme.json` e `tokens.css`: da lì, non da qui.
I registri di colore (chiaro, alterno, scuro, notte) si applicano alla sezione, non al singolo blocco.
