# Inventario URL — formato e istruzioni di compilazione

> `inventario-url.csv` è il **prerequisito bloccante** della Fase 0 e l'unica fonte della mappa 301.
> Senza di esso non si va in produzione (`CLAUDE.md` §5 Fase 0 punto 3).
>
> Il file è **già pre-compilato con i 20 URL della sitemap** (rilevati il 9/09/2026). Restano da
> aggiungere le metriche, che vivono solo in Search Console e GA4, e la colonna `decisione`.

---

## 1. Cosa è già stato rilevato, e cosa cambia

Letto direttamente dal sito live, in sola lettura:

| Rilievo | Conseguenza |
|---|---|
| `https://lidiatech.ai/sitemap.xml` **redirige a `www.`** — oggi la forma canonica di fatto è **con** `www` | la decisione dell'8/09 (`lidiatech.ai` senza `www`) è un **cambio**, non una conferma: serve un 301 `www` → non-`www` a livello di host, oltre a quelli di percorso |
| La sitemap è un **file XML scritto a mano**, generato il 21/07/2026, con hreflang `xhtml:link` corretti e 20 URL | è una buona base ma **non è una garanzia di completezza**: pagine mai inserite a mano non ci sono. Il crawl serve comunque |
| `robots.txt` è il **default di Joomla**, senza nessuna direttiva `Sitemap:` | Google trova la sitemap solo perché è stata inviata in Search Console. Da correggere nel nuovo `robots.txt` |
| L'italiano sta sotto **`/it/`**, l'inglese sotto **`/en/`**, ma `contatti`, `privacy-policy` e `cookies` stanno **alla radice** | struttura incoerente. Vedi §4: è una **decisione aperta nuova** |
| La home **non ha `link rel=canonical`** e ha **tre `h1`** | coerente con l'attacco che ha azzerato i metadati. Non si corregge sul Joomla (regola 9): si risolve nascendo bene |
| Esiste `app.lidiatech.ai` — l'applicazione | va nell'inventario dei sottodomini, non in questo file: non si migra, ma le CTA del nuovo sito puntano lì |

---

## 2. Le colonne

| Colonna | Chi la compila | Da dove |
|---|---|---|
| `url_vecchio` | ✅ fatto | sitemap + crawl |
| `lingua` | ✅ fatto | `it` / `en` |
| `tipo` | ✅ fatto | `home` · `pagina` · `archivio` · `articolo` · `legale` |
| `status` | crawl | codice HTTP effettivo (200/301/404/410) |
| `title` | crawl | `<title>` attuale — serve a capire cosa Google ha indicizzato |
| `keyword_principale` | Gianluca | la query per cui l'URL si posiziona meglio in GSC |
| `click_gsc_12m` | GSC | Prestazioni → 12 mesi → Pagine → esporta |
| `impression_gsc_12m` | GSC | stessa esportazione |
| `posizione_media_gsc` | GSC | stessa esportazione |
| `sessioni_ga4_12m` | GA4 | Report → Coinvolgimento → Pagine e schermate → 12 mesi |
| `referring_domains` | Ahrefs / Semrush / GSC Link | **domini** referenti, non backlink totali: 40 link dallo stesso sito valgono meno di 3 da tre siti |
| `decisione` | Gianluca | vocabolario chiuso, §3 |
| `url_nuovo` | Gianluca + Claude | obbligatorio se `decisione = 301` |
| `note` | chiunque | motivo della decisione, se non è ovvio |

---

## 3. `decisione`: quattro valori, nessun altro

| Valore | Quando | Serve `url_nuovo` |
|---|---|---|
| `mantieni` | l'URL resta identico nel nuovo sito | no |
| `301` | il contenuto vive altrove nel nuovo sito | **sì** |
| `410` | il contenuto non esiste più e non ha equivalente. Da usare solo con zero click e zero domini referenti | no |
| `da-decidere` | stato di partenza | no |

**Regola di chiusura della Fase 7:** nessuna riga con `click_gsc_12m > 0` o `referring_domains > 0`
può restare su `da-decidere` o `410`. È esattamente il gate «zero URL di valore senza destinazione
301». Con l'inventario compilato la verifica è meccanica, non un giudizio.

---

## 4. Una decisione aperta che l'inventario ha fatto emergere

**L'italiano va alla radice o resta sotto `/it/`?**

Non era nella lista delle sette decisioni aperte, e vincola **ogni riga della mappa 301**:

- **IT alla radice** (`lidiatech.ai/piattaforma`) — più corto, più forte come segnale, coerente con
  «IT primaria» e con `x-default` su IT. Costa un 301 su **tutti** gli URL italiani esistenti.
- **IT sotto `/it/`** (`lidiatech.ai/it/piattaforma`) — simmetrico con `/en/`, più semplice da
  configurare in Polylang/WPML, e conserva la struttura degli URL che oggi hanno storia. Costa un
  prefisso su ogni URL italiano per sempre.

C'è anche un fatto da sistemare in ogni caso: oggi tre URL italiani (`/contatti`,
`/privacy-policy`, `/cookies`) stanno alla radice e gli altri sotto `/it/`. Qualunque sia la
scelta, quella incoerenza si chiude adesso.

**Va decisa insieme a `/blog/` vs `/risorse/blog/`**, perché sono la stessa decisione vista da due
lati: la struttura degli URL. E va decisa **prima** dell'albero pagine della Fase 1.

Nota utile per quella decisione: il blog oggi vive su **`/it/news/`** con 7 articoli, tre dei quali
su temi che il nuovo posizionamento usa direttamente (governance dei dati, AI Act / legge 132/2025,
EIOPA e assicurazioni). Non sono URL da buttare.

---

## 5. Come si completa, in pratica

1. **Crawl** — Screaming Frog (500 URL bastano) su `https://www.lidiatech.ai/`, esporta
   `Internal → HTML` con Address, Status Code, Title 1. Serve a trovare gli URL **non** in sitemap:
   la sitemap è scritta a mano e quasi certamente ne manca qualcuno.
2. **Search Console** — Prestazioni, ultimi 12 mesi, scheda Pagine, esporta in CSV.
3. **GA4** — Pagine e schermate, ultimi 12 mesi, esporta.
4. **Domini referenti** — Ahrefs/Semrush se disponibili; altrimenti GSC → Link → Pagine con
   maggior numero di link. Meglio un dato approssimato che una colonna vuota.
5. Manda i quattro export: **l'incrocio in un unico CSV lo faccio io**, incluse le righe nuove
   trovate dal crawl e la colonna `decisione` pre-proposta pagina per pagina, da confermare o
   correggere.

Se un passaggio è più veloce a schermo condiviso che da export, si fa a schermo: l'obiettivo è il
file compilato, non la procedura.
