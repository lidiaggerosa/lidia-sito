# Registro rilasci — lidiatech.ai
Una riga per rilascio, la più recente in alto. Non si cancella niente.
Le righe le scrive `strumenti/pubblica.ps1`; i rilasci col connettore WordPress.com li scrive Claude.

| Data | Ambiente | Cosa | Pagine / file | Backup |
|---|---|---|---|---|
| 27/11/2026 | — | Riorganizzazione del progetto: una sola cartella collegata a GitHub, `strumenti/`, `pagine/`, `DISALLINEAMENTI.md`. Archivio su OneDrive (GESTIONE SITO) | tutto il repo | `archivio-progetto-2026-11-27` |

## Fino al 26/11/2026 (formato precedente)

| Data | Ambiente | Cosa | File / pagine | Backup | Verifica | Repo |
|---|---|---|---|---|---|---|
| 26/11 (notte) | staging + prod | **`/contatti/` solo modulo e landing `/lp/appuntamento/`:** `/contatti/` ridotta a H1 «Richiedi un appuntamento», descrizione e modulo commerciale (tolti email/telefono, Personalizzare, Dove siamo, A chi scrivere, Dati societari; meta description nuova). Nuova landing noindex per le campagne email: hero, lettura e funzioni della home, sicurezza, invito al webinar 9/12, modulo commerciale in coda; bottone della testata «Richiedi un appuntamento» dal campo `lidia_lp_cta`. `26-pubblica.ps1` accetta `-Tipo landing` | `contatti.html`, `lp-appuntamento.html` (nuovo), `inc/landing.php`, `scripts/26-pubblica.ps1`, `content/it/contatti.md`, `content/it/lp-appuntamento.md` | `backup-pagine/{staging,produzione}-20261126-*` | Gianluca (staging e prod) | PR #10 → `main` (merge `b3eb081`) |
| 26/11 (sera) | prod + Delera | **Webinar 9/12 con link unico via Delera:** iscrizione automatica su Teams spenta in produzione (costante `LIDIA_TEAMS_TENANT_ID` tolta); flusso Delera «Richiesta Webinar» senza webhook verso il sito, con mail «Conferma iscrizione webinar» (link Teams + `.ics` allegato) e attesa fino al 9/12 ore 9 + mail «Promemoria webinar 9/12»; riunione Teams nuova per il 9/12 (ID 341 642 738 118 222 8) | `theme/lidia/assets/calendario/lidia-live-lab-2026-12-09.ics` (allegato in Delera, non pubblicato sul sito), `wp-config.php` di produzione | — | Gianluca: iscrizione dal sito → mail Delera con link e .ics, prova con riunione di test ok | PR #10 → `main` (merge `b3eb081`) |
| 26/11 | staging + prod | **Webinar online:** CPT `webinar` e iscrizione automatica su Teams in produzione; pagina «Lidia Live Lab» (9/12) con layout titolo+dettagli a sinistra, modulo a destra, «Cosa vedremo» sotto; «Scegli una voce» su tutti i moduli; informativa privacy con paragrafo Webinar (Microsoft) | `functions.php`, `inc/post-types.php`, `inc/meta.php`, `inc/forms.php`, `inc/delera.php`, `inc/webinar.php`, `inc/schema.php`, `inc/enqueue.php`, `templates/single-webinar.html`, `assets/css/sezioni/webinar.css` (nuovo), `assets/js/modulo-editor.js`, `languages/en_GB.po/.mo`, `webinar/lidia-live-lab-dicembre-2026.html`, `legale-privacy.html`; costanti Teams, webhook e token in `wp-config.php` di produzione; `wp rewrite flush` | `backup-pagine/produzione-20261126-*` | Claude (prod 1440: pagina e modulo; token Microsoft e webinar visibili da `31-verifica-teams.php`). Test iscrizione completo in prod: da fare | da committare |
| 24–25/11 | staging | Pagina webinar rifatta (testi di Gianluca, tu, 5 voci), conferma che cambia con Teams, binding data/orario, opzioni `wp lidia webinar iscrivi`; costanti Teams su staging; script `31-verifica-teams.php` e `32-iscrizioni-teams.php` | come sopra + `scripts/31-*`, `scripts/32-*` | `backup-pagine/staging-2026112*` | Claude (staging 1440) · prova `wp lidia webinar iscrivi` ok (mail Teams in spam) | da committare |
| 23/11 | staging + prod | `/prodotto/ricerca-legale/` nuova; box e FAQ di `/prodotto/` ridotti (3 FAQ spostate) | `ricerca-legale.html`, `prodotto.html`, `enqueue.php`, `header.html`, `footer.html`, `prodotto-ricerca.php`, `prodotto-faq.php`, `funzione.css`, `landing.css`, `09-yoast.php` | `backup-pagine/produzione-20261123-154333` (+ staging-20261123-151820) | Claude (staging 1440/768/390, prod: index, canonical, sitemap, FAQ 3+2) | da committare |
| 03/11 | staging + prod? | Questionario: voti formazione/helpdesk, caratteri | `feedback.css`, `feedback.js`, `inc/feedback.php` | — | Gianluca | `allineamento-29-10` · produzione da verificare |
| 29/10 | staging + prod | `/feedback/` nuova | template `page-feedback`, `inc/feedback.php` | — | Claude | `allineamento-29-10` |
| 23/10 | staging + prod | MCP in inglese, tendina Prodotto | `27-mcp-en.php`, `header*.html`, `mcp*` | `backup-prod-2026-10-23-mcp-en.sql` | ok | `server-mcp-22-10` |
| 22/10 | staging + prod | `/prodotto/integrazione-mcp/`, box MCP home | pattern `mcp-*`, `home-mcp` | `backup-prod-2026-10-22-mcp.sql` | ok | `server-mcp-22-10` |
| 21/10 | prod | 301 `/chi-siamo`, title contatti/risorse | `redirect.htaccess`, `09-yoast.php` | — | ok | `redirect-chi-siamo-21-10`, `title-pagine-21-10` |
| 20/10 | prod | Attribuzione lead nel payload Delera | `modulo.js` | — | ok | `allineamento-29-10` |
| 12/10 | staging + prod | Tre landing di campagna | `lp-*.html`, `inc/landing.php` | — | ok | `allineamento-29-10` |
| 07/10 | staging + prod | Due pacchetti SEO + ripristino testi del 30/09 | `09-yoast.php`, schema, testi IT/EN | — | ok | `seo-07-10`, `seo-07-10-b` |
| 05/10 | prod | Telefono obbligatorio nel modulo prova | `prova-gratuita.html` | — | ok | — |
| 01/10 | prod | **Switch DNS: sito nuovo online** | — | — | ok | `main` |
