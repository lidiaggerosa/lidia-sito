---
url: /azienda/lavora-con-noi/
lingua: it
title: "Lavora con noi — Lidia"
meta_description: "Lidia S.r.l., legal tech italiana. Al momento non ci sono posizioni aperte, ma le candidature spontanee si leggono tutte."
h1: "Lavora con noi"
keyword_primaria: "lavora con noi lidia"
keyword_secondarie: "posizioni aperte legal tech italia"
cta_primaria: { testo: "Invia la candidatura", url: "mailto:lidia@lidiatech.ai" }
step_loop: nessuno
stato: approvato
approvato_da: Gianluca Gerosa
approvato_il: 2026-09-14
---

# Lavora con noi

Siamo un gruppo piccolo che costruisce un prodotto per un mestiere che non perdona
l'approssimazione. Cerchiamo persone disposte a capire il lavoro legale, non solo a scrivere
software: qui la differenza fra una funzione utile e una inutile la decide un avvocato, non una
metrica di prodotto.

---

## Posizioni aperte

Al momento non ce ne sono.

---

## Candidature spontanee

Se pensate di essere la persona giusta anche senza un annuncio, scriveteci. Leggiamo tutto.

**CTA primaria:** Invia la candidatura → lidia@lidiatech.ai

---

## Note per la build (non sono copy)

- **Nessuna posizione aperta al 14/09**, e la pagina lo dice invece di riempirsi di aria: un
  elenco finto costa credibilità e le candidature spontanee arrivano comunque.
- Fuori dal menu, come da albero. Serve il 301 da `/it/lavora-con-noi` e da `/en/careers`.
- Quando compariranno posizioni, lo stato vuoto va sostituito dall'elenco. Sopra le due o tre
  posizioni conviene un CPT `posizione` con JSON-LD `JobPosting`, che è anche ciò che le fa
  comparire su Google Jobs; con una sola non vale il debito.
- L'indirizzo per le candidature è quello generale, **lidia@lidiatech.ai**: se ne nasce uno
  dedicato, si cambia qui.
- Nessun blocco `:::prova` né `:::coppia`.
