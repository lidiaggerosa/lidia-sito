# 15 — Il laboratorio: /laboratorio/

Pagina **privata** (la vedono solo gli utenti che hanno fatto accesso), `noindex, nofollow` e fuori
dalla sitemap per costruzione (`inc/libreria.php`, `lidia_pagine_servizio()`). È il banco di prova
dei componenti: qui si inseriscono dall'editor, si cambiano testi e mock e si misurano gli ingombri
prima di portarli in una pagina vera. È l'unica pagina, con `/libreria/`, in cui il contenuto si
compone dall'admin: è un banco di prova, non definisce il sito.

Nata il 24/09/2026 per il box funzione della nuova `/prodotto/` (`patterns/funzione-box.php`,
`funzione-box-inverso.php`, `sezioni/funzione.css`, idiomi del mock in `components/demo.css`).

Il file di partenza `scripts/blocchi/laboratorio.html` contiene l'intestazione e i due box
d'esempio (dritto e invertito): serve solo alla prima creazione. Rilanciare `post update` con quel
file **azzera** quanto composto dall'editor.

## Crea

Locale, con lo strumento `wp_cli` di WordPress Studio sul sito **Lidia 2026**:

```
post create "<cartella del repo>\scripts\blocchi\laboratorio.html" --post_type=page --post_status=private --post_title="Laboratorio" --post_name=laboratorio --page_template=page-composta --porcelain
```

Staging, in SSH (il file di blocchi va prima in `~/scripts/blocchi/` con `scp`):

```
wp post create ~/scripts/blocchi/laboratorio.html --post_type=page --post_status=private --post_title="Laboratorio" --post_name=laboratorio --page_template=page-composta --porcelain
```

## Verifica

Da browser con accesso fatto: `/laboratorio/`. Da browser anonimo: 404. Poi:

```
post list --post_type=page --name=laboratorio --fields=ID,post_status --format=csv
```

Atteso: `private`. Nel sorgente, `<meta name='robots' content='noindex, nofollow…'>`.

## Quando un componente è pronto

Il pattern si aggiorna nel file in `patterns/`, il CSS in `sezioni/`; la pagina vera si compone
nel suo file di blocchi (`scripts/blocchi/<pagina>.html`) e si carica con `post update`. Il
laboratorio resta com'è: non va copiato in produzione.
