# 02 — Pagina /prodotto/

Ricostruisce la pagina prodotto dal file di blocchi versionato. Rieseguibile: se la pagina
esiste già, si aggiorna il contenuto invece di crearne una seconda.

Contenuto: `scripts/blocchi/prodotto.html` — somma delle dieci sezioni `lidia/prodotto-*` (testa, indice,
azioni, ricerca, workflow, word, assistant, altre, misura, faq) più il modulo `lidia/home-form`
(lo stesso della home, `#prova`). Copy di riferimento: `content/it/prodotto.md` (approvato il
24/09/2026; sostituisce il 16/09).
I file `prodotto-catena`, `-dati`, `-limiti`, `-prezzo`, `-integrazioni`, `-chiusura` sono
svuotati dal 24/09: **da eliminare** dal repo.

**Secondo passaggio — i mock.** Le colonne mock dei quattro box portano un riempitivo. I widget
(`mock/<funzione>.html`) si incollano dall'editor come blocco HTML personalizzato seguendo
`docs/redazione/box-funzione.md`, poi il contenuto della pagina si riesporta nel file di blocchi
(`wp post get <ID> --field=post_content > scripts/blocchi/prodotto.html`) per restare nel canale
unico. Prima di incollarli: i widget non devono caricare font esterni (guardrail §7 — solo
Fraunces + Inter self-hosted): `mock/ricerca-legale.html` oggi importa Switzer da fontshare e va
corretto.

## Crea

```
wp post create "<cartella del repo>\scripts\blocchi\prodotto.html" \
  --post_type=page --post_status=publish \
  --post_title="Ogni funzione, un passo verso il risultato." \
  --post_name=prodotto \
  --page_template=page-composta \
  --porcelain
```

`--page_template=page-composta` è obbligatorio: la pagina ha il suo H1 dentro la prima
sezione, quindi non deve usare il template `page` che stampa il titolo.

## Aggiorna

```
wp post update <ID> "<cartella del repo>\scripts\blocchi\prodotto.html"
```

## Metadati Yoast

Title e meta description stanno nel front-matter di `content/it/prodotto.md`. Si impostano in
`scripts/09-yoast.md` insieme a quelli delle altre pagine, non a mano dall'admin.

## Da fare in Fase 7

JSON-LD `FAQPage` con le cinque domande di questa pagina, testo identico al visibile.
