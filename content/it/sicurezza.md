---
url: /sicurezza/
lingua: it
title: "Sicurezza dei dati e AI negli studi legali | Lidia"
meta_description: "Sicurezza dei dati negli studi legali che usano l'AI: dati su AWS in Unione Europea, nessun accesso dei fornitori dei modelli, ISO 27001, CSA STAR, GDPR."
h1: "Sicurezza dei dati: dove finiscono i documenti dei vostri clienti"
keyword_primaria: "sicurezza dei dati ai studi legali"
keyword_secondarie: "segreto professionale intelligenza artificiale; gdpr ai legale; dove risiedono i dati"
intento: fiduciario
cta_primaria: { testo: "Richiedi una prova gratuita", url: "/prova-gratuita/" }
step_loop: nessuno
stato: approvato
approvato_da: Gianluca Gerosa
approvato_il: 2026-09-14
revisione_07_10: "07/10/2026 — SEO: occhiello, H1, sommario e due H2 con «sicurezza dei dati»; title e description nuovi. Approvato in chat da Gianluca."
revisione_30_09: "30/09/2026 — revisione testi prima della messa online, approvata in chat da Gianluca."
---

# Sicurezza dei dati: dove finiscono i documenti dei vostri clienti

È la prima domanda che fa un socio quando lo studio legale valuta un'AI, ed è quella giusta. Un
fascicolo non è un file: è materiale coperto da segreto professionale, e chi lo tratta risponde.
Questa pagina spiega la sicurezza dei dati per punti, senza rimandi a un'informativa da leggere dopo.

---

## Dati in Unione Europea, e non si spostano

Infrastruttura **AWS in Unione Europea**. I documenti sono cifrati **a riposo e in transito**
e non lasciano l'Unione Europea. È la condizione minima perché uno studio italiano possa affidare a un sistema di AI materiale
coperto da segreto professionale: i dati restano dove la legge europea li protegge.

---

## I fornitori dei modelli non vedono niente

:::prova
numero: "0"  unita: "documenti"  contesto: "trasmessi o conservati dai fornitori dei modelli LLM, e mai usati per addestrarli"  fonte: "architettura Lidia"
:::

Nessun dato dello studio viene trasmesso o conservato dai fornitori dei modelli linguistici, e
nessun dato viene usato per addestrarli. È la differenza fra usare un assistente generalista e
usare un sistema costruito per il lavoro legale: nel primo caso il vostro fascicolo esce dal
vostro perimetro, nel secondo no.

---

## Cosa certificano, in una riga ciascuna

### ISO/IEC 27001
Il sistema di gestione della sicurezza delle informazioni è certificato da un ente terzo, non
autodichiarato.

### CSA STAR Lvl. 1
La sicurezza dell'infrastruttura cloud è documentata secondo lo standard della Cloud Security
Alliance.

### ISO 9001
I processi aziendali sono certificati per qualità e tracciabilità.

### GDPR
Trattamento conforme al Regolamento (UE) 2016/679.

---

## Domande frequenti sulla sicurezza dei dati

Quattro risposte autoconsistenti, nel JSON-LD `FAQPage` con testo identico al visibile.

### Dove risiedono i dati di un'AI legale?
Dipende dal fornitore, e va verificato prima di caricare qualsiasi fascicolo. I dati trattati da
Lidia risiedono su infrastruttura AWS in Unione Europea, cifrati a riposo e in transito.
La residenza dei dati in Unione Europea è la condizione minima perché uno studio italiano possa
usare un sistema di intelligenza artificiale su materiale coperto da segreto professionale.

### Usare l'intelligenza artificiale viola il segreto professionale?
No, a tre condizioni: che i dati non escano dall'Unione Europea, che nessun terzo — compresi i
fornitori dei modelli linguistici — vi acceda, e che il rapporto con il fornitore sia regolato da
un accordo sul trattamento dei dati. Con Lidia le prime due sono proprietà dell'architettura; la
terza è il Data Processing Agreement, che vi consegniamo prima della firma.

### I miei documenti vengono usati per addestrare l'AI?
No. Nessun documento dello studio viene trasmesso o conservato dai fornitori dei modelli LLM, né
utilizzato per addestrarli. È una proprietà dell'architettura, non un'impostazione da
disattivare.

### Quali certificazioni deve avere un software AI per studi legali?
Le due che contano sono **ISO/IEC 27001**, che certifica il sistema di gestione della sicurezza
delle informazioni, e una verifica indipendente sulla sicurezza cloud come **CSA STAR**. Lidia ha
entrambe, più ISO 9001 sui processi. Le dichiarazioni di conformità non certificate da un ente
terzo non sono equivalenti.

---

## Fate la due diligence prima, non dopo

Ruoli, sub-responsabili, condizioni e tempi di trattamento stanno nel Data Processing Agreement,
che vi consegniamo insieme alla proposta contrattuale, prima della firma.

**CTA primaria:** Richiedi una prova gratuita → `/prova-gratuita/`

---

## Note per la build (non sono copy)

- **Nessun link pubblico al DPA da questa pagina.** `/legale/dpa/` è `noindex, nofollow`, fuori
  dalla sitemap e fuori dal menu e dal footer: si raggiunge solo dal link diretto inviato con la
  proposta contrattuale (decisione del 14/09).
- Un solo blocco `:::prova` su questa pagina, nessun `:::coppia`: è la regola di scarsità.
- Le quattro certificazioni sono quattro riquadri «sigillo» (sigla in Fraunces, icona di verifica,
  una riga di testo): nessun logo (decisione del 24/09, che supera l'elenco in riga del 14/09).
- «In Unione Europea» e «I fornitori dei modelli» stanno in una sola sezione, in due card
  affiancate con icone «europa» e «nomodello» (24/09). Testi invariati.
- Nessuna cifra o claim di sicurezza oltre a quelli già pubblici: niente SLA, niente tempi di
  conservazione, niente risultati di penetration test finché non esistono per iscritto.
