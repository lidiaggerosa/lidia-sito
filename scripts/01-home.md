# scripts/01-home — la home in WordPress

> Come per `00-bootstrap.md`: questi comandi **sono** la configurazione. La home non si
> compone a mano nell'editor. Il contenuto sta in `scripts/blocchi/home.html`, generato dalle
> nove sezioni del tema (`theme/lidia/patterns/home-*.php`), approvate il 16/09/2026 e riviste il
> 23/09/2026 sul nuovo storytelling (testi in `content/it/home.md`).
>
> Si lanciano con lo strumento WP-CLI di WordPress Studio (`wp_cli`) sul sito **Lidia 2026**.

---

## 1. Tema attivo

```
theme activate lidia
```

## 2. Creare (o aggiornare) la pagina Home

La prima volta:

```
post create --post_type=page --post_title="Home" --post_name=home --post_status=publish
```

Poi, ogni volta che cambia una sezione, si riscrive il contenuto dal file — mai dall'editor:

```
post update <ID> --post_content="$(cat scripts/blocchi/home.html)"
```

Su Windows, con lo strumento `wp_cli` di Studio, il contenuto si passa da file:

```
post update <ID> ./scripts/blocchi/home.html
```

## 3. Impostarla come pagina iniziale

```
option update show_on_front page
option update page_on_front <ID>
rewrite flush --hard
```

## 4. Verifica

```
post get <ID> --field=content | head -5
```

Poi, dallo strumento di Studio: `validate_blocks` sulla home e `take_screenshot` a 1440, 768 e 390.

---

## Segnaposto ancora aperti nella home

| Dove | Cosa manca |
|---|---|
| Sezione 3 · Lettura | L'immagine a destra è un segnaposto tratteggiato: asset da fornire |

Le demo delle cinque schede (sezione 4) hanno testi d'esempio approvati il 23/09: sono
illustrazioni, non contenuto.
