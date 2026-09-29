# 09 — Yoast e SEO on-page

Configura Yoast e scrive title, description e keyword di ogni pagina. Rieseguibile.

## Cosa sta dove

| Cosa | Dove |
|---|---|
| Title, description, keyword | front-matter di `content/it/*.md` → copiati in `scripts/09-yoast.php` |
| Impostazioni Yoast, logo, immagine social | `scripts/09-yoast.php` |
| Organization (P. IVA, sede, LinkedIn), FAQPage, SoftwareApplication | `theme/lidia/inc/schema.php` |
| BreadcrumbList, WebSite, WebPage | Yoast, da sé |
| robots.txt, llms.txt | `theme/lidia/inc/seo.php` (+ `theme/lidia/llms.txt`) |
| noindex del ramo `/legale/` | `theme/lidia/inc/legale.php` |

Un testo cambia in `content/`, poi in `09-yoast.php`, poi si rilancia. Mai dall'admin.

## Locale

Da Studio (WP-CLI raggiunge la cartella di progetto):

```
wp eval-file "<cartella del repo>\scripts\09-yoast.php"
```

`wp yoast index` in locale non serve e non gira: Yoast crea gli indexable solo con
`WP_ENVIRONMENT_TYPE=production`. Fuori produzione legge i campi direttamente.

## Staging / produzione

```powershell
scp -P 18765 -i $KEY scripts\09-yoast.php "${S}:scripts/09-yoast.php"
ssh -p 18765 -i $KEY $S "cd ~/www/staging.lidiatech.ai/public_html && wp eval-file ~/scripts/09-yoast.php"
ssh -p 18765 -i $KEY $S "cd ~/www/staging.lidiatech.ai/public_html && wp sg purge https://staging.lidiatech.ai/"
```

Solo in produzione, dopo lo script: `wp yoast index --reindex --skip-confirmation`.

Un comando per volta. Sul server **non** deve esistere un `robots.txt` o `llms.txt` fisico in
`public_html`: `ls public_html/*.txt` deve tornare vuoto.

## Verifica

```
wp eval-file scripts/16-seo-stato.php     # nessun «—» su title e description delle pagine indicizzabili
```

Poi, da browser: `/robots.txt`, `/llms.txt`, `/sitemap_index.xml`; sorgente della home, di
`/prodotto/` e di `/prezzi/` → blocco `yoast-schema-graph` con `FAQPage`, `BreadcrumbList`,
`Organization` (con `vatID`) e, su `/prodotto/`, `SoftwareApplication`. In produzione:
Rich Results Test e Schema Markup Validator sulle stesse pagine.
