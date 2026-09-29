# SEO e redirect

## Cosa sta dove

| Cosa | Dove |
|---|---|
| Title, description, keyword | front-matter di `content/*.md` → `scripts/09-yoast.php` (IT), `scripts/19-contenuti-en.php` (EN) |
| Impostazioni Yoast | `scripts/09-yoast.php` |
| JSON-LD Organization, FAQ, SoftwareApplication | `theme/lidia/inc/schema.php` |
| robots.txt, llms.txt | `theme/lidia/inc/seo.php`, `theme/lidia/llms.txt` |
| `noindex` di `/legale/` | `theme/lidia/inc/legale.php` |
| Hreflang IT/EN | Polylang (`scripts/17-polylang.php`) |

Un testo SEO cambia nel front-matter, poi nello script, poi si rilancia (`scripts/09-yoast.md`). Mai dall'admin.

## Redirect

- `migration/mappa-301.csv` — la mappa: `da`, `a`, `tipo` (301 / 410 / nessuno), `motivo`.
- `migration/redirect.htaccess` — le regole che traducono la mappa (`RedirectMatch`, una per riga, ancorate con `^` e `$`). Si aggiorna insieme alla mappa, nello stesso formato. Va in testa a `.htaccess`, sopra il blocco WordPress.
- `migration/host-www.htaccess` — 301 da `www` a senza `www`.
- `scripts/12-verifica-redirect.ps1` — verifica tutte le righe della mappa; atteso `KO: 0`.

Procedura di caricamento: `scripts/10-staging.md`, sezione «Redirect».

**Mai una regola su `/index.php`**: WordPress passa da lì per ogni pagina, il sito intero andrebbe in 410.

**Cambiare un URL pubblicato:** riga nuova nella mappa, regola corrispondente in `redirect.htaccess`, verifica.
