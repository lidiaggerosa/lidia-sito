---
url: /prova-gratuita/
lingua: it
title: "Prova gratuita software legal AI — 7 giorni | Lidia"
meta_description: "Sette giorni di prova gratuita su documenti reali del vostro studio. Tutte le funzioni attive, fonti ufficiali comprese, dati in Unione Europea."
h1: "Richiedi subito una prova gratuita di 7 giorni."
keyword_primaria: "prova gratuita software legal ai"
keyword_secondarie: "demo software legal ai; provare ai studio legale; richiedi demo lidia"
cta_primaria: { testo: "Richiedi la prova gratuita", url: "#form" }
step_loop: nessuno
stato: approvato
approvato_da: Gianluca Gerosa
approvato_il: 2026-09-14
revisione: 2026-09-24 (H1 e apertura allineati al modulo della home)
---

# Richiedi subito una prova gratuita di 7 giorni.

Compila il modulo: ti contattiamo per mostrarti la piattaforma e attivare la prova sui tuoi documenti e sui tuoi flussi di lavoro. Tutte le funzioni attive, per una settimana.

**FORM — in cima, accanto all'apertura.** Modulo del tema (`lidia/modulo`, `tipo: prova`).
Campi e consensi stanno in `docs/tecnico/moduli-delera.md`, non in questo file.

---

## Tre cose, poi siete dentro

Compilate il form. Vi chiamiamo per mostrarvi la piattaforma e attivare la prova. Caricate i documenti su cui state
lavorando e cominciate.

Per sette giorni la usate come volete: ricerca sulle fonti, domande sul fascicolo, un workflow,
una bozza in Word. Non c'è un percorso da seguire.

---

## Valgono le stesse regole del contratto

I documenti che caricate stanno su infrastruttura **AWS, regione di Milano**, cifrati a riposo e
in transito, e **non vengono trasmessi né conservati dai fornitori dei modelli, né usati per
addestrarli**. La prova non è un ambiente di serie B: è lo stesso sistema, con le stesse
garanzie.

→ Sicurezza e trattamento dei dati (`/sicurezza/`)

---

## Domande frequenti

Tre risposte autoconsistenti, nel JSON-LD `FAQPage` con testo identico al visibile.

### Quanto dura la prova gratuita di Lidia?
Sette giorni, con tutte le funzioni attive: ricerca legale sulle fonti ufficiali, Smart Answer
sul fascicolo, Lidia Workflow, Workflow Builder e l'add-in per Word. Non è una versione ridotta:
è il prodotto completo su documenti reali dello studio.

### Posso usare documenti reali dei miei clienti durante la prova?
Sì, ed è il modo in cui la prova ha senso. I documenti restano su infrastruttura AWS in Unione
Europea, cifrati, e non vengono trasmessi ai fornitori dei modelli linguistici né usati per
addestrarli. Le condizioni di trattamento sono le stesse del contratto.

### Cosa succede alla fine dei sette giorni?
Si parla dei risultati e, se lo studio vuole proseguire, arriva il preventivo. Lidia parte da
125 € al mese, con tutte le materie del diritto comprese.

---

**FORM — ripetuto in fondo.** Stesso modulo della cima.

---

## Note per la build (non sono copy)

- **Il form è il modulo nativo del tema** (`lidia/modulo`, `tipo: prova`), che consegna a Delera
  via webhook (decisione del 18/09, che ritira l'iframe del 14/09). Campi, antispam e coda stanno
  in `docs/tecnico/moduli-delera.md`: non vanno duplicati qui.
- **Due istanze dello stesso modulo**, in cima e in fondo. Un solo evento `lidia_lead`, con un
  parametro di posizione (`hero` / `footer`): senza, in GA4 non si distingue quale converte.
- **Il submit avviene in pagina**, quindi GTM lo vede: nessun `postMessage`, nessuna conversione
  da registrare lato Delera.
- **UTM, `gclid`, `fbclid`, `li_fat_id`** viaggiano in campi nascosti del modulo, o il lead arriva
  in Delera senza sorgente.
- **Il modulo è di prima parte:** non scrive cookie e non chiama nessuno, quindi si vede sempre,
  anche con tutti i consensi rifiutati. Nessun segnaposto da mostrare.
- **Nessun accenno a carta di credito o rinnovo automatico** su questa pagina: non servono, ma
  non si scrive (decisione del 14/09).
- Nessun tempo di attivazione dichiarato: «quanto prima», non un numero di ore.
- Nessun blocco `:::prova` né `:::coppia` su questa pagina.
