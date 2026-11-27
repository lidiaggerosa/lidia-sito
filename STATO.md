# STATO — lidiatech.ai
Aggiornato: 27/11/2026 · Ultimo rilascio in produzione: 26/11/2026

## Pagine live
Sorgenti in `pagine/`.

| URL | Sorgente | Ultimo rilascio |
|---|---|---|
| `/` | `home.html` | 22/10 |
| `/prodotto/` · `/prodotto/ricerca-legale/` | `prodotto.html` · `ricerca-legale.html` | 23/11 |
| `/prodotto/integrazione-mcp/` | `integrazione-mcp.html` | 22/10 |
| `/sicurezza/` · `/prezzi/` | `sicurezza.html` · `prezzi.html` | 07/10 |
| `/prova-gratuita/` | `prova-gratuita.html` | 05/10 |
| `/contatti/` (H1 «Richiedi un appuntamento» + modulo commerciale) | `contatti.html` | 26/11 |
| `/azienda/` · `/azienda/lavora-con-noi/` | `azienda.html` · `lavora-con-noi.html` | 23/11 · 17/09 |
| `/risorse/` · `/risorse/articoli/` · `/risorse/whitepaper/` | `risorse.html` · `articoli.html` | 07/10 |
| 2 articoli, 5 whitepaper | `risorse/` | 07/10 |
| `/legale/` + privacy, cookie, termini, DPA, annex A/B | `legale-*.html` | 30/09 · privacy 26/11 |
| `/risorse/webinar/lidia-live-lab-dicembre-2026/` (9/12, link via Delera) | `webinar/lidia-live-lab-dicembre-2026.html` | 26/11 |
| `/feedback/` (noindex) | blocco `lidia/feedback` | 29/10 |
| `/lp/attivita-legali/` · `/lp/ai-legale-studi/` · `/lp/ricerca-giuridica/` (noindex) | `lp-*.html` | 12/10 |
| `/lp/appuntamento/` (noindex, campagne email) | `lp-appuntamento.html` | 26/11 |
| `/en/` · `/en/product/` · `/en/product/mcp-server/` | `en/` | 23/10 |
| `/en/security/` · `/en/pricing/` · `/en/free-trial/` · `/en/company/` · `/en/contact/` · `/en/company/careers/` | `en/` | 07/10 |

## In lavorazione
- Webinar gennaio: iscrizione automatica su Teams (organizzatore marketing@) e link personale via Delera — `lavori/webinar-teams/`
- Pagine funzione: workflow, archivio, add-in Word
- Offerte: legal-ai-os, ai-managed-service, transfer-pricing (solo staging)
- Overview: `/aziende/`, `/studi-professionali/`

## Aperti
- Jetpack attivo in produzione dal 27/11 (MCP: lettura completa, scrittura solo contenuti). Manca il connettore WordPress.com in Claude
- Privacy policy: aggiungere Automattic (Jetpack) e SiteGround Spain S.L. tra i fornitori; confermare Microsoft Ireland Operations Limited
- `/lp/appuntamento/`: riquadro webinar del 9/12 da togliere o aggiornare dopo il 9/12
- Pipeline Delera «Webinar»: dopo il 9/12 spostare Ha partecipato / Non ha partecipato e passare a sales
- Webinar 9/12: se la riunione Teams cambia, aggiornare le due mail Delera e l’allegato `.ics`
- Teams: ruotare client secret e token passati in chat il 25–26/11; chiedere all’IT Gianluca come proprietario dell’app
- `/contatti/` senza email e telefono: verificare il JSON-LD `Organization` (contactPoint)
- Audit SEO/GEO del 23/11: interventi residui in `docs/audit-seo-geo-2026-11.md`
- `/prodotto/ricerca-legale/`: indicizzazione in Search Console; schermata di esempio ripetuta due volte, da decidere
- Indicizzazione `/en/product/mcp-server/` in Search Console
- Logo Claude ufficiale al posto di `loghi-ai/claude.png` (home IT/EN, pagine MCP)
- Utente MySQL separato per staging e produzione
- Token Notion del questionario: scade il 29/10/2027

## Manutenzione
- Controllo mensile: `.\strumenti\aggiornamenti.ps1` (alert il primo lunedì del mese). Versioni in `docs/versioni-installate.md`
- Prossimo passo: spegnere l’autoupdate SiteGround in produzione, cancellare i plugin spenti (complianz-terms-conditions, really-simple-ssl, google-site-kit), aggiornare staging → produzione

## Rinviati
- Meta Conversions API
- Banner cookie: «Nega» resta così
