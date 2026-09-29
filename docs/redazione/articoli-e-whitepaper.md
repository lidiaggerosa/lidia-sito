# Articoli e whitepaper

> Per chi pubblica sul sito Lidia senza toccare codice. Non serve sapere niente di WordPress
> oltre a quello che c'è qui.

---

## 1. Le due cose che pubblicherai

**Articolo** — analisi, resoconto, commento a una norma. Aperto: chiunque lo legge, nessun
modulo. Vive su `/risorse/{slug}/` e compare in `/risorse/` e in `/risorse/articoli/`.

**Whitepaper** — documento operativo, più lungo, con un PDF da scaricare. Vive su
`/risorse/whitepaper/{slug}/` e compare in `/risorse/` e in `/risorse/whitepaper/`.

Gli elenchi si aggiornano da soli. Non c'è nessuna pagina da modificare a mano quando esce
qualcosa di nuovo: pubblichi, e compare.

---

## 2. Come si scrive un articolo

1. **Articoli → Aggiungi nuovo.**
2. Nell'editor, apri l'inseritore (il **+** in alto a sinistra), vai su **Pattern → Lidia —
   contenuti** e inserisci **«Nuovo articolo»**. Arriva l'ossatura: apertura, due sezioni,
   chiusura, rimando finale.
3. Riscrivi il testo fra parentesi quadre. Le parentesi quadre si cancellano scrivendoci sopra:
   **se ne resta una in pagina, è un errore visibile.**
4. Compila il **riassunto** (vedi §4: è il campo che conta di più e quello che si dimentica).
5. Titolo, e pubblica.

## 3. Come si scrive un whitepaper

Stessa cosa, ma da **Whitepaper → Aggiungi nuovo**, e il pattern da inserire è
**«Nuovo whitepaper»**.

In più, nel pannello di destra, sotto **Campi personalizzati**:

| Campo | Cosa ci va |
|---|---|
| `lidia_gated` | lasciare **attivo**: il documento si scarica compilando il modulo |
| `lidia_file` | il numero (ID) del PDF: si ottiene caricandolo **da questa pagina** con «Aggiungi media», poi si legge nell'indirizzo dell'allegato (`post=123`) |
| `lidia_delera_tag` | l'etichetta con cui il contatto entra in Delera |

> Il PDF va caricato **dall'editor del whitepaper**, non dalla Libreria media generica: solo così
> finisce nella cartella protetta e si scarica soltanto compilando il modulo. Se lo carichi dal
> posto sbagliato, la pagina del whitepaper te lo dice con un avviso giallo.

---

## 4. Il riassunto è la riga che si vede negli elenchi

Nel pannello di destra c'è un campo **Riassunto**. Quel testo è **la riga che compare sotto il
titolo in tutti gli elenchi del sito**.

Se lo lasci vuoto WordPress non lascia il buco: prende le prime righe del testo e le taglia dove
capita, a metà frase. Funziona, ma si vede.

**Scrivilo sempre**, una frase, che dica cosa c'è dentro — non un'introduzione. Guarda le righe
già online per il tono: «I quattro pilastri della governance AI: good data, partire dal problema,
compliance by design e test del legittimo interesse per il training.»

---

## 5. L'ordine dei whitepaper lo decidi tu

Gli articoli sono in ordine di pubblicazione, dal più recente. Giusto così: sono notizie.

I whitepaper **no**: sono in un ordine editoriale, che si cambia da **Whitepaper → tutti i
whitepaper**, trascinando le voci nella colonna dell'ordine. Non serve cambiare le date, e non
si deve: una data falsa resta nel sito per sempre.

---

## 6. Cosa non si tocca

- **Le pagine del sito** (`/prodotto/`, `/sicurezza/`, `/prezzi/`, `/risorse/`…) sono fatte di
  sezioni costruite su copy approvato. Il testo dentro si può correggere; **la struttura no**:
  se serve una sezione nuova, si apre una richiesta su GitHub.
- **Non incollare HTML** dentro l'editor, e non usare il blocco «HTML personalizzato»: quello che
  finisce lì dentro non è modificabile da nessun altro e non rispetta gli stili.
- **Non cambiare i permalink** di una pagina già pubblicata: l'indirizzo vecchio resta nei motori
  di ricerca e nei link altrui. Se va cambiato, si fa insieme a un redirect.
- **Niente plugin.** Se serve qualcosa che il sito non fa, si apre una richiesta su GitHub.

---

## 7. Prima di pubblicare, tre controlli

1. **Riassunto compilato** (§4).
2. **Nessuna parentesi quadra** rimasta dal pattern.
3. **Titolo e descrizione SEO** compilati nel riquadro Yoast in fondo alla pagina. Il titolo che
   si vede su Google non è il titolo dell'articolo: è quello, e va scritto.

---

## 8. Quando qualcosa non torna

Gli elenchi si aggiornano da soli, quindi se un contenuto nuovo **non compare**, quasi sempre è
una di queste tre:

- è rimasto in **bozza** invece che pubblicato;
- è un whitepaper, e stai guardando l'elenco degli articoli (o viceversa);
- la pagina è in cache: ricarica tenendo premuto Maiusc.

Se non è nessuna delle tre, chiedi: è un difetto del sito, non tuo.
