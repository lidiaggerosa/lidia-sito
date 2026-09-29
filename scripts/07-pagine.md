# 07 — Le pagine secondarie: /prova-gratuita/, /contatti/, /azienda/, /azienda/lavora-con-noi/, /risorse/articoli/

Ricostruisce le cinque pagine costruite il 17/09. Rieseguibile: se la pagina esiste già si
aggiorna il contenuto, non se ne crea una seconda. Stesso schema di `02-prodotto.md`.

Il contenuto di ciascuna è il file omonimo in `scripts/blocchi/`; il copy approvato sta in
`content/it/`. Tutte usano il template `page-composta`: l'H1 è dentro la prima sezione.

| Pagina | File di blocchi | Titolo | Slug | Genitore |
|---|---|---|---|---|
| Prova gratuita | `prova-gratuita.html` | Richiedi subito una prova gratuita di 7 giorni. | `prova-gratuita` | — |
| Contatti | `contatti.html` | Contatti | `contatti` | — |
| Azienda | `azienda.html` | Chi c’è dietro Lidia | `azienda` | — |
| Lavora con noi | `lavora-con-noi.html` | Lavora con noi | `lavora-con-noi` | `azienda` |
| Articoli | `articoli.html` | Articoli | `articoli` | `risorse` |

## Crea

Radice: `<cartella del repo>\scripts\blocchi\`. Le figlie vanno create dopo il genitore,
perché serve il suo ID.

```
wp post create "…\prova-gratuita.html" --post_type=page --post_status=publish --post_title="Richiedi subito una prova gratuita di 7 giorni." --post_name=prova-gratuita --page_template=page-composta --porcelain
wp post create "…\contatti.html"       --post_type=page --post_status=publish --post_title="Contatti" --post_name=contatti --page_template=page-composta --porcelain
wp post create "…\azienda.html"        --post_type=page --post_status=publish --post_title="Chi c’è dietro Lidia" --post_name=azienda --page_template=page-composta --porcelain
wp post create "…\lavora-con-noi.html" --post_type=page --post_status=publish --post_title="Lavora con noi" --post_name=lavora-con-noi --post_parent=<ID di azienda> --page_template=page-composta --porcelain
wp post create "…\articoli.html"       --post_type=page --post_status=publish --post_title="Articoli" --post_name=articoli --post_parent=<ID di risorse> --page_template=page-composta --porcelain
```

`/risorse/` si crea con `05-risorse.md`, prima di questa.

## Aggiorna

```
wp post update <ID> "…\<file>.html"
```

## Verifica

```
wp post list --post_type=page --fields=ID,post_title,post_name,post_parent --format=csv
```

Poi `validate_blocks` e `take_screenshot` a 390 sulle cinque pagine.

## Metadati Yoast

Title e meta description stanno nel front-matter dei file in `content/it/`. Si impostano in
`scripts/09-yoast.md` (Fase 7) insieme a quelli delle altre pagine, non a mano dall'admin.
