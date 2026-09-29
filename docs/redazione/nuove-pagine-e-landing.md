# Nuove pagine e landing

## Cosa pubblica la redazione da sola

Articoli e whitepaper: `articoli-e-whitepaper.md`. Box funzione: `box-funzione.md`.

## Cosa si chiede

Una **pagina nuova**, una **sezione nuova** o una **modifica di struttura** non si fanno dall'admin.
Si apre una richiesta su GitHub (Issues → New issue → «Richiesta») con:

- URL e scopo della pagina;
- testi approvati;
- data di messa online.

Lo sviluppo crea il file di blocchi e lo script, lo prova su staging, e lo pubblica dopo l'OK dell'owner.

## Correggere un testo su una pagina esistente

Si corregge nell'editor, dentro la sezione. Poi si avvisa lo sviluppo, che riporta la correzione
nel file della pagina in `scripts/blocchi/`: altrimenti al rilascio successivo la correzione si perde.

## Landing di campagna

Il modello per le landing isolate (senza menu, `noindex`, modulo verso Delera) **non è ancora
disponibile**. Fino ad allora, una landing si chiede come pagina nuova.
