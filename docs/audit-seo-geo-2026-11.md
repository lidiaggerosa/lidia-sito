# Audit SEO e GEO — lidiatech.ai
23/11/2026 · produzione · sola lettura

## In sintesi
- **Brand: sotto controllo.** Se l'utente nomina Lidia, Google (AI Overview), ChatGPT, Perplexity e Gemini la descrivono bene e citano lidiatech.ai.
- **Categoria: Lidia non esiste.** Nessuna delle domande senza il nome "Lidia" porta a una citazione su nessun motore: 0 su 15 per ChatGPT e per Gemini, 0 su 5 per Perplexity e 0 nelle AI Overview.
- **Perché:** le AI copiano da articoli del tipo "migliori AI per avvocati" (ekory.ai, getcasus.com, edit.legal, ai4business.it, agendadigitale.eu, i blog di Lexroom e Aptus), e Lidia non c'è in nessuno. Sulle domande informative (sicurezza, GDPR, AI Act, ricerca giurisprudenziale, MCP) vince il blog di Lexroom.
- **Errori su Lidia:**
  - Gemini dice che Lidia è un prodotto Wolters Kluwer (confusione con Libra).
  - Gemini inventa che il nome sia l'acronimo di "Legal Intelligence Driven by AI".
  - ChatGPT non trova quali fonti usa Lidia, anche se l'elenco è nella FAQ di /prodotto/.
- **Posizionamento percepito:** nei confronti Lidia passa per "workflow e fascicoli". La ricerca giurisprudenziale e l'affidabilità delle fonti vengono attribuite a Libra, Lexroom e Lextel.

## SEO tecnica

| Voce | Stato |
|---|---|
| robots.txt | OK. Crawler AI ammessi, sitemap dichiarata |
| Sitemap | OK, 28 URL. Manca `lastmod` |
| Canonical, hreflang IT/EN | OK su tutte le pagine principali |
| Title e meta description | OK (40–60 e 120–160 caratteri). Deboli: `/en/contact/` («Contact \| Lidia») e `/en/company/careers/` («Careers — Lidia») |
| H1 home | «L'AI legale che lavora con te.» Il master prompt indica «La piattaforma AI Agentica che pensa con te»: da allineare |
| Schema | Organization, WebSite, FAQPage, BreadcrumbList, SoftwareApplication (prezzo 125 €), Article |
| Organization | Mancano description, foundingDate, founder, parentOrganization (MESA Group). sameAs senza Crunchbase né Wikidata |
| Article | Autore = l'organizzazione, nessuna persona. Debole per E-E-A-T |
| llms.txt | Presente. Mancano la pagina MCP, `/en/` e l'elenco delle fonti. llms-full.txt assente |
| Loghi clienti in home | 17 loghi con `alt=""` (Bonelli Erede, Andersen, Unipol, Benetton…): per crawler e AI non esistono |
| Prove in home | Nessun numero (quante sentenze, quali corti) e nessuna certificazione nel testo |
| Risorse | 2 articoli e 5 whitepaper da circa 400–900 parole. Troppo poco per competere sulle domande informative |
| Redirect | Vecchi URL Joomla reindirizzati correttamente. `/en/home` porta alla home IT invece che a `/en/` |
| Sottodomini | Bing indicizza `app.lidiatech.ai` (pagine dell'app) e `landing.lidiatech.ai/ricerca-legale` |
| Velocità (dal browser, non da laboratorio) | TTFB 48 ms, LCP circa 0,3 s, 283 KB. Nessun problema evidente. PageSpeed non disponibile oggi (quota) |
| Omonimi | lid-ia.com (sovraindebitamento), lidiaedu.com, persone di nome Lidia |

## Griglia GEO (22 prompt)
✓ = Lidia citata · ✗ = non citata · n.d. = non eseguito · numero = posizione organica di lidiatech.ai su Google

| # | Prompt | Google | AI Overview | ChatGPT | Perplexity | Gemini |
|---|---|---|---|---|---|---|
| 1 | Cos'è Lidia, l'intelligenza artificiale per avvocati? | 1 | ✓ | ✓ | ✓ | ✓ acronimo inventato |
| 2 | Lidia AI legale: come funziona e quanto costa? | 1 | ✓ | ✓ | ✓ | ✓ |
| 3 | Lidia vs Lexroom | — | ✓ | ✓ | ✓ | ✓ |
| 4 | Lidia vs Libra | — | no AO | ✓ | ✓ Libra preferita per le fonti | ✓ |
| 5 | Migliori software AI per avvocati in Italia | — | ✗ | ✗ | ✗ | ✗ |
| 6 | Che AI usare per uno studio legale italiano | — | ✗ | ✗ | ✗ | ✗ |
| 7 | Confronto tra i 7 (Lidia inclusa) | — | no AO | ✓ solo workflow | ✓ | ✓ |
| 8 | AI legale per uffici legali aziendali | 5 | no AO | ✗ | ✗ | ✗ |
| 9 | Alternative italiane a Harvey/ChatGPT | — | ✗ | ✗ | ✗ | ✗ |
| 10 | Le AI legali addestrano sui documenti? | — | ✗ | ✗ | ✗ | ✗ |
| 11 | AI legale con dati in UE e GDPR | 2 (/sicurezza/) | no AO | ✗ | n.d. | ✗ |
| 12 | È sicuro caricare atti riservati? | — | ✗ | ✗ | n.d. | ✗ |
| 13 | Lidia: dove sono i dati | 1 | no AO | ✓ | n.d. | ✓ |
| 14 | Quali fonti usa Lidia | — | no AO | non sa rispondere | n.d. | ✗ «prodotto Wolters Kluwer» |
| 15 | AI con giurisprudenza di Cassazione e merito | — | ✗ | ✗ | n.d. | ✗ |
| 16 | AI che cita sentenze senza inventarle | — | ✗ | ✗ | n.d. | ✗ |
| 17 | Ricerca giurisprudenziale con l'AI | — | ✗ | ✗ | n.d. | ✗ |
| 18 | AI per analizzare e redigere contratti | — | ✗ | ✗ | n.d. | ✗ |
| 19 | Banca dati giuridica in Claude/ChatGPT (MCP) | — | ✗ | ✗ | n.d. | ✗ |
| 20 | AI Act: strumenti conformi | — | ✗ | ✗ | n.d. | ✗ |
| 21 | Best legal AI platforms for Italian law | — | ✗ | ✗ | n.d. | ✗ |
| 22 | Italian legal research AI, GDPR hosting | — | ✗ | ✗ | n.d. | ✗ |

**Chi vince senza nominare Lidia:**
- Lexroom: presente quasi ovunque.
- Aptus, Simpliciter, Normo, One LEGALE/Libra, Giuffrè: presenti spesso.
- Budda Law e DirectCase: si sono presi la domanda sull'MCP, dove Lidia ha una pagina dedicata ma non compare.

**Fonti più usate dalle AI:** ekory.ai, getcasus.com, edit.legal, ai4business.it, agendadigitale.eu, lexroom.ai/blog, aptus.ai/blog, wolterskluwer.com, jurio.it, lexhero.com.

**Copertura esterna esistente:** Repubblica Genova (05/2026), legalcommunity.it e andersen.com (accordo Andersen), case study Corley, convenzioni con Ordine Avvocati Milano e AIGA Genova, Microsoft Marketplace, Hub LegalTech.

## Interventi proposti (ordine di priorità)

| # | Intervento | Dove | Impatto | Sforzo |
|---|---|---|---|---|
| 1 | Entrare negli articoli "migliori AI per avvocati": contatto con ekory, getcasus, edit.legal, ai4business, agendadigitale, intelligenza-artificiale.eu | fuori dal sito | alto | medio |
| 2 | Pubblicare `/prodotto/ricerca-legale/` (pronta dal 04/11) con l'elenco delle fonti nel testo e la sua FAQ | sito | alto | basso |
| 3 | Correggere l'entità: Organization completo (descrizione, anno, fondatori, MESA Group, Crunchbase), voce Wikidata, frase «Lidia è sviluppata da Lidia S.r.l. (MESA Group)» in /azienda/ e in llms.txt | sito + Wikidata | alto | basso |
| 4 | Contenuti per le domande scoperte, 1 a settimana: addestramento e riservatezza, dati UE e GDPR, AI Act per studi, ricerca giurisprudenziale con AI, guida MCP, alternative a Harvey | sito | alto | medio |
| 5 | Nomi dei clienti in testo (alt dei loghi + una riga) e case study Andersen e Corley | sito | medio | basso |
| 6 | Prove in home: corti coperte, certificazioni, numeri sulle fonti | sito | medio | basso |
| 7 | Autore persona negli articoli (schema Person + pagina autore) | sito | medio | basso |
| 8 | llms.txt completo (MCP, EN, fonti) e llms-full.txt | sito | basso | basso |
| 9 | Noindex su `app.lidiatech.ai` e `landing.lidiatech.ai` | IT | medio | basso |
| 10 | Minori: `lastmod` in sitemap, title EN contatti e careers, redirect `/en/home` → `/en/` | sito | basso | basso |

## Da verificare con l'owner
- L'AI Overview dice «fondata nel 2024, sedi a Genova e Milano». È corretto?
- I nomi dei clienti si possono scrivere in testo?
- H1 della home: quale versione è quella giusta?

## Note di metodo
- Ogni prompt è stato lanciato in una chat nuova, dagli account di Gianluca (ChatGPT Free, Perplexity gratuito, Gemini). Google con hl=it e gl=it.
- Perplexity si è bloccato dopo il prompt 10 (limite dell'account gratuito): 11–22 da ripetere.
- L'account ChatGPT ha il connettore Lidia installato. Sui prompt 13 e 14 è stato saltato con «Non ora».
- Le risposte AI cambiano da una volta all'altra: questa è una fotografia, da ripetere una volta al mese con lo stesso set.
