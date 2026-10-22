# 25 — Pagina /prodotto/integrazione-mcp/ e riquadro su /prodotto/

Costruita il 22/10/2026. Rieseguibile: se la pagina esiste si aggiorna, non se ne crea una seconda.
Stesso schema di `07-pagine.md`.

| Pagina | File di blocchi | Titolo | Slug | Genitore |
|---|---|---|---|---|
| Server MCP | `integrazione-mcp.html` | Lidia, dentro il tuo assistente AI. | `integrazione-mcp` | `prodotto` |

Template `page-composta`, solo IT, indicizzabile, nessuna voce di menu: l'ingresso è il riquadro
`#server-mcp` su `/prodotto/` (pattern `lidia/prodotto-mcp`, già dentro `prodotto.html`).
Copy: `content/it/integrazione-mcp.md`. Title e meta: `09-yoast.php`.

**Sempre con `--user=gianluca`:** la testa contiene un blocco HTML con SVG (l'animazione), e
senza un utente con `unfiltered_html` WordPress lo ripulisce. Vale anche per `/prodotto/` (mock).

## Con lo script (dal 22/10/2026)

```powershell
.\scripts\26-pubblica.ps1 -Pagina integrazione-mcp -Titolo "Lidia, dentro il tuo assistente AI." -Genitore prodotto -Tema inc\enqueue.php,patterns,assets\css\sezioni\mcp.css,assets\css\sezioni\ingresso.css,assets\js\mcp-animazione.js,assets\images\mcp,assets\images\loghi-ai -Yoast
.\scripts\26-pubblica.ps1 -Pagina prodotto
```

Poi verifica su staging; per la produzione le stesse due righe con `-Ambiente produzione`
(prima il logo Claude ufficiale al posto del segnaposto).

## Staging e produzione (comandi a mano, storico)

File del tema da caricare (solo questi, non il tema intero):

- `inc/enqueue.php`
- `patterns/mcp-*.php` (10) e `patterns/prodotto-mcp.php`
- `assets/css/sezioni/mcp.css`, `assets/css/sezioni/ingresso.css`
- `assets/js/mcp-animazione.js`
- `assets/images/mcp/claude-spark.png`
- `assets/images/loghi-ai/` (`claude.png` è un **segnaposto**: in produzione il logo ufficiale o niente logo)

Poi in `~/scripts/`: `blocchi/integrazione-mcp.html`, `blocchi/prodotto.html`, `09-yoast.php`.

```
wp post list --post_type=page --name=prodotto --field=ID
wp post create ~/scripts/blocchi/integrazione-mcp.html --post_type=page --post_status=publish --post_title="Lidia, dentro il tuo assistente AI." --post_name=integrazione-mcp --post_parent=<ID di prodotto> --page_template=page-composta --user=gianluca --porcelain
wp post update <ID di prodotto> ~/scripts/blocchi/prodotto.html --user=gianluca
wp eval-file ~/scripts/09-yoast.php --user=gianluca
wp rewrite flush
wp sg purge https://<dominio>/
```

Solo produzione: `wp yoast index --reindex --skip-confirmation`, poi controllo URL in Search Console.

## Aggiorna

```
wp post list --post_type=page --name=integrazione-mcp --field=ID
wp post update <ID> <percorso>/integrazione-mcp.html --user=<admin>
```

## Verifica

- `validate_blocks` e `take_screenshot` a 1440, 768, 390 su `/prodotto/integrazione-mcp/` e sul riquadro di `/prodotto/`;
- animazione ferma con «riduci movimento»; `mcp.css` e `mcp-animazione.js` solo su questa pagina, `ingresso.css` solo su `/prodotto/`;
- sorgente: title e description di Yoast, `FAQPage` con 8 domande, briciole Home › Prodotto › Server MCP.
