---
url: /contatti/
lingua: it
title: "Contatti — Lidia"
meta_description: "Contatti di Lidia S.r.l.: lidia@lidiatech.ai, +39 010 8991141. Sedi di Genova e Milano. Per le prove gratuite usate il form dedicato."
h1: "Contatti"
keyword_primaria: "contatti lidia"
keyword_secondarie: "lidiatech contatti; telefono lidia"
cta_primaria: { testo: "Scrivici", url: "mailto:lidia@lidiatech.ai" }
step_loop: nessuno
stato: approvato
approvato_da: Gianluca Gerosa
approvato_il: 2026-09-14
revisione: "25/09/2026 — sezione «Personalizzare Lidia per lo studio» con il modulo commerciale; riga in «A chi scrivere». Approvato in chat."
revisione_30_09: "30/09/2026 — revisione testi prima della messa online, approvata in chat da Gianluca."
---

# Contatti

**lidia@lidiatech.ai**
**+39 010 8991141**

---

## Personalizzare Lidia per lo studio

Formazione, workflow, playbook, integrazioni: l'offerta Enterprise si costruisce insieme, a partire
da un incontro. Lasciateci i vostri riferimenti e vi ricontattiamo per fissarlo.

*(Modulo Lidia, intento «commerciale» · pulsante «Scrivi al team commerciale».)*

---

## Dove siamo

### Genova
Corso Andrea Podestà 8/3, 16128

### Milano
Centro Direzionale Milanofiori, Strada 3, Palazzo B4, 20057 Assago

---

## A chi scrivere

**Volete provare Lidia** — usate il form della prova gratuita: arriva direttamente a chi segue
le prove ed è la strada più rapida.
→ Prova gratuita di sette giorni (`/prova-gratuita/`)

**Volete personalizzare Lidia per lo studio** — usate il modulo qui sopra.

**Tutto il resto** — lidia@lidiatech.ai

**Comunicazioni formali** — lidiapec@pec.it

---

## Dati societari

Lidia S.r.l. — P. IVA 02976860995
PEC lidiapec@pec.it

---

## Note per la build (non sono copy)

- Recapiti presi dal footer del sito live l'11/09; l'unico dato aggiunto è l'email ordinaria
  **lidia@lidiatech.ai**, che nel footer non c'è.
- **Da verificare prima della messa online:** quale delle tre sedi è la **sede legale**, e se
  vanno aggiunti numero REA e capitale sociale. Oggi il footer live non li riporta.
- JSON-LD `Organization` con `contactPoint` (email e telefono) e `address` della sede legale.
  È la pagina da cui i motori generativi prendono i recapiti: se il dato non è qui strutturato,
  lo prendono altrove e spesso sbagliato.
- **Modulo commerciale** (25/09, supera la regola del 14/09 «nessun form qui»): blocco
  `lidia/modulo` con `intento: commerciale`, per l'offerta Enterprise (personalizzazione e
  incontro). Ci arriva la CTA «Parla con il team» di `/prezzi/`. La prova resta su
  `/prova-gratuita/`. Stesso webhook: in Delera il workflow si divide su `intento`.
- Nessun blocco `:::prova` né `:::coppia`.
