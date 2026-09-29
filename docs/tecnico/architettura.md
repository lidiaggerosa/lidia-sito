# Architettura del tema

## File

```
theme/lidia/
├── theme.json            token: colori, tipografia, spaziature, larghezze
├── style.css             solo intestazione del tema
├── functions.php         carica i file di inc/
├── templates/            front-page, page, page-composta, single, archive, search, 404, index
├── parts/                header, footer (+ versioni -en)
├── patterns/             le sezioni, un file per sezione
├── inc/                  la logica PHP
├── assets/css/           tokens.css, base.css, parti/, sezioni/, components/
├── assets/js/            modulo, consenso, tracking, funzioni, editor
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

## Pagine

Le pagine sono fatte di pattern. Il contenuto di ogni pagina è un file in `scripts/blocchi/`
(`scripts/blocchi/en/` per l'inglese). Lo script della pagina (`scripts/01-home.md` … `08-legale.md`)
la crea o la aggiorna da quel file. Il copy approvato sta in `content/`.

Per cambiare una pagina: si modifica il file di blocchi, si rilascia, si aggiorna la pagina con
`wp post update <ID> <file>`. Mai solo dall'editor, altrimenti il repo non corrisponde più al sito.

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

## Ambiente locale

WordPress Studio, sito con PHP 8.4. Il tema non si copia: una junction Windows collega
`<sito Studio>\wp-content\themes\lidia` a `theme\lidia` del repo. Lo stesso per il mu-plugin
(`scripts/06-tracking.md`). Il sito locale si configura con `scripts/00-bootstrap.md`.
