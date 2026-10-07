---
url: /risorse/whitepaper/nuova-governance-dei-dati/
lingua: it
title: "La nuova governance dei dati: impatti legali dell'AI | Lidia"
meta_description: "I quattro pilastri della governance AI: good data, partire dal problema, compliance by design e test del legittimo interesse per il training. Download gratuito."
h1: "La nuova governance dei dati"
keyword_primaria: "governance dei dati intelligenza artificiale"
keyword_secondarie: "legittimo interesse training ai; edpb opinion 28 2024; data quality ai"
tipo: whitepaper
gated: true
url_originale: https://www.lidiatech.ai/it/news/governance-dei-dati
cta_primaria: { testo: "Scarica il whitepaper", url: "#form" }
stato: approvato
approvato_da: Gianluca Gerosa
approvato_il: 2026-10-07
revisione: "07/10/2026 — sintesi nuova scritta dal PDF completo (SEO: pagina sottile), approvata in chat."
---
# La nuova governance dei dati

Adottare l'AI in ambito legale è prima di tutto una questione di governance, metodo e competenze. Il paper raccoglie quattro contributi su come progettare sistemi AI affidabili e conformi, dalla qualità del dato alle frizioni tra le norme europee e la Legge 132/2025.

## Dai big data ai good data

Il fattore abilitante non è la quantità dei dati, ma la loro qualità. Quattro condizioni:

- sapere quali fonti si hanno e dove stanno;
- un responsabile per ogni dataset;
- qualità verificabile;
- processi formalizzati di validazione e versioning.

E un errore ricorrente da evitare: partire dal dataset invece che dal problema. I progetti nati da «cosa possiamo fare con i nostri dati?» raramente producono valore.

## La compliance come fattore abilitante

I legali vanno coinvolti all'inizio, non a valle. Il loro compito è tradurre le regole in requisiti di progetto: quali dati si possono usare, su quale base giuridica, con quale documentazione. Se intervengono a progetto finito, settimane di lavoro possono andare perse. La governance poi continua per tutta la vita del modello.

## Fonti lecite e modelli sovrani

Il caso di un modello linguistico addestrato interamente in Italia mostra come si costruisce un'AI controllata:

- fonti licenziate o lecitamente accessibili;
- dati personali rimossi prima dell'addestramento;
- nessun riuso dei dati degli utenti;
- documentazione completa.

Il punto chiave: una volta dentro il modello, un dato non si può più togliere. La liceità va verificata prima.

## Basi giuridiche per il training

Il consenso raramente funziona: è difficile da raccogliere su larga scala ed è revocabile, mentre un modello non si può «disaddestrare». Si va verso il legittimo interesse, con il test in tre passaggi indicato dall'EDPB (Opinion 28/2024): interesse legittimo, necessità, bilanciamento. Resta poco regolata la responsabilità di chi usa il modello, cioè lo studio.

## Tassonomie e frizioni normative

Quattro categorie di dato con regimi diversi:

- dati personali;
- dati protetti da proprietà intellettuale;
- dati riservati;
- dati generati da macchine.

Spesso un dato rientra in più categorie insieme. A questo si aggiungono le tensioni tra GDPR, copyright, Data Act e AI Act, e i punti in cui la Legge 132/2025 si discosta dal quadro europeo.

## Cosa trovate nel paper completo

- I quattro contributi per esteso, con due casi applicativi aziendali.
- Il test del legittimo interesse, passaggio per passaggio.
- La mappa delle intersezioni normative e le aree di ulteriore indagine.

**FORM DI DOWNLOAD** — modulo del tema (`lidia/modulo`, `tipo: whitepaper`). Campi e consensi
in `docs/tecnico/moduli-delera.md`.

---

## Note per la build (non sono copy)

- 07/10/2026: sintesi sostituita (prima: **testo migrato alla lettera** da `/it/news/governance-dei-dati` il 14/09/2026, con la CTA
  «Richiedi una demo gratuita» sostituita dal form di download. `stato: in-revisione`).
- **Firma di Marco Pagani confermata** (14/09), senza qualifica. Citazione dei quattro coautori
  esterni **autorizzata** (14/09).
- Contenuto **gated**: CPT `risorsa` con `lidia_gated = true`.
