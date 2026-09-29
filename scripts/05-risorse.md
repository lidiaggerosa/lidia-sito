# 05 — /risorse/, /risorse/whitepaper/ e i sette contenuti migrati

Ricostruisce i due hub e i contenuti che ci stanno dentro. Rieseguibile.

## Prerequisiti

Il CPT `risorsa`, le tassonomie e i campi custom sono nel tema — `inc/post-types.php` e
`inc/meta.php` — quindi esistono appena il tema è attivo. Non serve nessun plugin.

La struttura dei permalink degli articoli è `/risorse/%postname%/`:

```
wp rewrite structure "/risorse/%postname%/" --hard
```

I whitepaper stanno su `/risorse/whitepaper/{slug}/` per via del `rewrite` del CPT, e
l'archivio automatico è disattivato (`has_archive => false`): l'indice è una pagina vera.

## I sette contenuti

Titoli, slug, tipo e stato stanno in `scripts/blocchi/risorse/manifest.json`; il corpo di
ciascuno è il file di blocchi omonimo nella stessa cartella, convertito dal markdown di
`content/it/risorse/`. Si ricreano tutti con:

```
wp eval '$d = __DIR__; $m = json_decode( file_get_contents( "…/risorse/manifest.json" ), true );
foreach ( $m as $v ) { … wp_insert_post( … ); }'
```

Il comando completo è nel decision log alla voce del 17/09. In sintesi, per ciascuna voce:
`post_type` dal manifest, `post_status=publish`, `post_name` dallo slug,
`post_excerpt` dalla meta description, e per i whitepaper `lidia_gated = true`.

**Ordine dei whitepaper:** `menu_order` da 1 a 5 nell'ordine del copy — odissea, EIOPA,
governance dei dati, diritto e LLM, responsabilità. Il blocco query ordina per `menu_order`
crescente, non per data: così l'ordine è una scelta editoriale e il team può cambiarlo
trascinando, senza toccare le date.

## Le due pagine

```
wp post create "…\scripts\blocchi\risorse.html" --post_type=page --post_status=publish \
  --post_title="Risorse" --post_name=risorse --porcelain
wp post create "…\scripts\blocchi\whitepaper.html" --post_type=page --post_status=publish \
  --post_title="Whitepaper" --post_name=whitepaper --post_parent=<ID di risorse> --porcelain
```

Entrambe con `_wp_page_template = page-composta`.

## Stato dei contenuti

I sette testi sono migrati **alla lettera** dal Joomla e restano `in-revisione`: la revisione
editoriale è una voce aperta di Fase 1. Sono pubblicati sul locale perché senza contenuti gli
elenchi non si possono verificare, non perché siano pronti.

## Cosa manca, e dove

- **Il modulo di download** dei cinque whitepaper è `lidia/modulo` (`tipo: whitepaper`), nativo e
  di prima parte: si vede sempre, anche con tutti i consensi rifiutati. Restano da configurare le
  URL dei webhook in `wp-config.php` e l'automazione Delera che spedisce il documento (18/09).
- **Il modulo di iscrizione** di `/risorse/` resta un segnaposto, ma non più per la CMP: manca la
  newsletter, né copy né lista. Il testo del segnaposto in `blocchi/risorse.html` cita ancora la
  raccolta lead rinviata e il modulo di terza parte: va riscritto.
- **`lidia_file`** (il PDF da scaricare) e **`lidia_delera_tag`** sono registrati ma vuoti: i
  documenti veri non sono ancora in media library.
- **Nessuna sezione webinar**, e il CPT `webinar` non è registrato: si registra quando esiste
  il primo webinar, non prima.
