---
url: /contatti/
lingua: it
title: "Contatta Lidia, l'AI legale per studi e aziende | Lidia"
meta_description: "Richiedi un appuntamento con il team di Lidia: lascia i tuoi riferimenti e ti ricontattiamo."
h1: "Richiedi un appuntamento"
keyword_primaria: "contatti lidia"
keyword_secondarie: "lidiatech contatti; telefono lidia"
cta_primaria: { testo: "Scrivi al team commerciale", url: "#modulo" }
step_loop: nessuno
stato: approvato
approvato_da: Gianluca Gerosa
approvato_il: 2026-09-14
revisione: "25/09/2026 — sezione «Personalizzare Lidia per lo studio» con il modulo commerciale; riga in «A chi scrivere». Approvato in chat."
revisione_30_09: "30/09/2026 — revisione testi prima della messa online, approvata in chat da Gianluca."
revisione_26_11: "26/11/2026 — testata: tolti email e telefono, H1 «Richiedi un appuntamento», modulo commerciale in testata (sezione «Personalizzare Lidia per lo studio» assorbita). Poi tolte anche Dove siamo, A chi scrivere e Dati societari: la pagina è solo il form. Approvato in chat."
---

*Contatti*

# Richiedi un appuntamento

Lascia i tuoi riferimenti e verrai ricontattato dal nostro team per fissare un appuntamento.

*(Modulo Lidia, intento «commerciale» · pulsante «Scrivi al team commerciale». Ancora `#modulo`.)*

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
