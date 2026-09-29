# 14 — La libreria delle sezioni: /libreria/

Pagina **privata** (la vedono solo gli utenti che hanno fatto accesso) che mostra tutte le
sezioni del tema una sotto l'altra, ciascuna con la sua scheda: nome nell'inseritore, slug,
classe, descrizione, pagine in cui è impiegata. In testa un campionario di tipografia e bottoni.

Il contenuto è un solo blocco dinamico, `lidia/libreria` (`theme/lidia/inc/libreria.php`): legge
i pattern registrati a ogni richiesta, quindi **segue il tema da sola**. Non c'è niente da
aggiornare quando una sezione cambia o se ne aggiunge una. `noindex, nofollow` e fuori dalla
sitemap per costruzione, qualunque sia lo stato della pagina.

Rieseguibile: se la pagina esiste già si aggiorna, non se ne crea una seconda.

## Crea

Da PowerShell, nella cartella del progetto (locale, WordPress Studio):

```
wp post create ".\scripts\blocchi\libreria.html" --post_type=page --post_status=private --post_title="Libreria delle sezioni" --post_name=libreria --page_template=page-composta --porcelain
```

Su staging, in SSH (il file di blocchi va prima in `~/scripts/blocchi/` con `scp`):

```
wp post create ~/scripts/blocchi/libreria.html --post_type=page --post_status=private --post_title="Libreria delle sezioni" --post_name=libreria --page_template=page-composta --porcelain
```

## Aggiorna

```
wp post update <ID> ~/scripts/blocchi/libreria.html
```

## Verifica

Da browser con accesso fatto: `/libreria/`. Da browser anonimo: 404. Poi:

```
wp post list --post_type=page --name=libreria --fields=ID,post_status --format=csv
```

Atteso: `private`. Nel sorgente della pagina, `<meta name='robots' content='noindex, nofollow…'>`.

## Cosa mostra e come leggerla

- **Campionario**: H1-H4, prosa, occhiello, elenco, citazione, i tre stili di bottone.
- **Sezioni della home** (categoria `lidia-home`), **Sezioni delle pagine interne** (`lidia-sezioni`),
  **Ossature dei contenuti** (`lidia-contenuti`), **Gesti in archivio** (`lidia-gesti`, fuori dall'inseritore).
- Ogni scheda dice **dove** la sezione è impiegata, cercando la sua classe nei contenuti pubblicati:
  «Non impiegata in nessuna pagina pubblicata» segnala una sezione orfana.
- La descrizione della scheda è la `Description` del file del pattern: è lì che vanno scritte la
  regola di scarsità e le istruzioni d'uso (piano «autonomia del team», punto 1).
