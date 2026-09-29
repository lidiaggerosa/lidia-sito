# 03 — Pagina /sicurezza/

Ricostruisce la pagina sicurezza dal file di blocchi versionato. Rieseguibile: se la pagina
esiste già, si aggiorna il contenuto invece di crearne una seconda.

Contenuto: `scripts/blocchi/sicurezza.html` — somma delle cinque sezioni `lidia/sicurezza-*`
(testa, residenza [= le due card «Dove stanno i dati»], certificazioni, faq, chiusura).
`sicurezza-fornitori.php` è assorbito in `sicurezza-residenza.php` dal 24/09: il file va eliminato.
Copy di riferimento: `content/it/sicurezza.md` (approvato il 14/09/2026).

## Crea

```
wp post create "<cartella del repo>\scripts\blocchi\sicurezza.html" \
  --post_type=page --post_status=publish \
  --post_title="Dove finiscono i documenti dei vostri clienti" \
  --post_name=sicurezza \
  --page_template=page-composta \
  --porcelain
```

`--page_template=page-composta` è obbligatorio: la pagina ha il suo H1 dentro la prima
sezione, quindi non deve usare il template `page` che stampa il titolo.

## Aggiorna

```
wp post update <ID> "<cartella del repo>\scripts\blocchi\sicurezza.html"
```

## Metadati Yoast

Title e meta description stanno nel front-matter di `content/it/sicurezza.md`. Si impostano in
`scripts/09-yoast.md` insieme a quelli delle altre pagine, non a mano dall'admin.

## Da fare in Fase 7

JSON-LD `FAQPage` con le quattro domande di questa pagina, testo identico al visibile.

## Vincoli dal copy (§ «Note per la build»)

- Nessun link pubblico al DPA da questa pagina: `/legale/dpa/` è `noindex, nofollow` e si
  raggiunge solo dal link diretto inviato con la proposta contrattuale.
- Un solo `lidia-fatto` in pagina, nessuna `lidia-coppia`: è la regola di scarsità.
- Certificazioni a quattro riquadri «sigillo», senza loghi; residenza e fornitori in due card
  affiancate (24/09).
