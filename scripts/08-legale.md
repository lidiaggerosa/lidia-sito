# 08 — Il ramo /legale/

Ricostruisce la pagina contenitore e le quattro legali. Rieseguibile. Il `noindex, nofollow` e
l'esclusione dalla sitemap non si impostano qui: li mette il tema su tutto il ramo
(`inc/legale.php`), per costruzione.

| Pagina | File di blocchi | Titolo | Slug | Genitore |
|---|---|---|---|---|
| Informazioni legali | `legale.html` | Informazioni legali | `legale` | — |
| Privacy | `legale-privacy.html` | Informativa sulla privacy | `privacy` | `legale` |
| DPA | `legale-dpa.html` | Accordo sul Trattamento dei Dati | `dpa` | `legale` |
| Termini | `legale-termini.html` | Termini e Condizioni Generali | `termini` | `legale` |
| Cookie | `legale-cookie.html` | Cookie policy | `cookie` | `legale` |
| Allegato A | `legale-termini-annex-a.html` | Politica di Sicurezza | `annex-a` | `termini` |
| Allegato B | `legale-termini-annex-b.html` | Accordo sul Livello di Servizio | `annex-b` | `termini` |

Le prime quattro usano il template `page` (titolo stampato dal template, testo lungo su misura
di lettura). Privacy, DPA, termini e i due allegati sono convertiti dai `.docx` in `content/it/legale/`
(gli allegati da `ANNEX.docx`, un solo file per due pagine, figlie di `termini`): se il
documento cambia, si riconverte e si rilancia l'aggiornamento, non si corregge a mano.

`/legale/cookie/` è un **segnaposto dichiarato**: il footer la collega, quindi deve esistere.
Il testo lo genera Complianz in Fase 5.

## Crea

```
wp post create "…\legale.html"         --post_type=page --post_status=publish --post_title="Informazioni legali" --post_name=legale --porcelain
wp post create "…\legale-privacy.html" --post_type=page --post_status=publish --post_title="Informativa sulla privacy" --post_name=privacy --post_parent=<ID di legale> --porcelain
wp post create "…\legale-dpa.html"     --post_type=page --post_status=publish --post_title="Accordo sul Trattamento dei Dati" --post_name=dpa --post_parent=<ID di legale> --porcelain
wp post create "…\legale-termini.html" --post_type=page --post_status=publish --post_title="Termini e Condizioni Generali" --post_name=termini --post_parent=<ID di legale> --porcelain
wp post create "…\legale-cookie.html"  --post_type=page --post_status=publish --post_title="Cookie policy" --post_name=cookie --post_parent=<ID di legale> --porcelain
wp post create "…\legale-termini-annex-a.html" --post_type=page --post_status=publish --post_title="Politica di Sicurezza" --post_name=annex-a --post_parent=<ID di termini> --porcelain
wp post create "…\legale-termini-annex-b.html" --post_type=page --post_status=publish --post_title="Accordo sul Livello di Servizio" --post_name=annex-b --post_parent=<ID di termini> --porcelain
```

## Aggiorna

```
wp post update <ID> "…\<file>.html"
```

## Verifica

Con `blog_public` a 0 tutto il sito è `noindex`: per provare la regola del ramo si accende per un
istante (`option update blog_public 1`), si controlla che `/legale/privacy/` dia
`noindex, nofollow` e `/azienda/` dia `index, follow`, e si rimette a 0.

## Prima della messa online

- verificare che l'informativa privacy sia la versione aggiornata (quella convertita è datata
  maggio 2025, termini e DPA sono del 2026);
- l'informativa deve citare **SiteGround Spain S.L.** come fornitore di hosting e la coda di
  consegna del modulo (dati conservati fino a 7 giorni se Delera non risponde);
- sostituire il segnaposto di `/legale/cookie/` con il testo di Complianz.
