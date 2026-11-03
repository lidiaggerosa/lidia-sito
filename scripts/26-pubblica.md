# 26 — Pubblicare su staging e produzione (senza locale)

Dal 22/10/2026 si lavora solo su **staging** e **produzione**. Studio non si usa più.
Il repo resta l'unica fonte: si scrive il file qui, lo script lo porta sul server.

Ordine fisso: **staging → verifica → produzione**.

Una sola connessione per rilascio: la passphrase della chiave si inserisce **una volta**
(niente `ssh-agent`, che sul PC richiede i permessi di amministratore).

> Lo script `26-pubblica.ps1` contiene server, utente e chiave: lo tiene l'owner e non è nel repo.

## Uso

Dalla cartella del progetto:

```powershell
cd "<cartella del repo>"

# aggiornare una pagina
.\scripts\26-pubblica.ps1 -Pagina prezzi

# creare una pagina
.\scripts\26-pubblica.ps1 -Pagina integrazione-mcp -Titolo "Lidia, dentro il tuo assistente AI." -Genitore prodotto

# pagina + file del tema + Yoast
.\scripts\26-pubblica.ps1 -Pagina integrazione-mcp -Tema inc\enqueue.php,patterns,assets\css\sezioni\mcp.css -Yoast

# solo tema
.\scripts\26-pubblica.ps1 -Tema assets\css\sezioni\landing.css

# produzione: stessa riga + -Ambiente produzione (chiede di scrivere PRODUZIONE)
.\scripts\26-pubblica.ps1 -Pagina prezzi -Ambiente produzione
```

Se PowerShell blocca lo script: `powershell -ExecutionPolicy Bypass -File .\scripts\26-pubblica.ps1 …`

## Cosa fa

1. Controlla i blocchi sul server (aperti = chiusi, blocchi registrati, niente HTML fuori dai blocchi, niente lorem ipsum). Se fallisce, non tocca nulla.
2. Salva in `~/backup-pagine/<ambiente>-<data>/` la pagina e i file del tema che sta per sovrascrivere.
3. Carica tema e blocchi, aggiorna o crea la pagina (`--user=<admin>`), Yoast se richiesto, purge della cache.
4. In produzione, se ha creato una pagina o eseguito Yoast: `wp yoast index --reindex`.
5. Stampa l'URL e i comandi di ripristino.

## Verifica (su staging)

- screenshot a 1440, 768 e 390 dal browser, con l'accesso Protected URLs fatto;
- PageSpeed Insights al posto di `need_for_speed` (solo produzione: lo staging è protetto);
- sorgente: title, description, JSON-LD.

## Limiti

- `-Tema` non cancella: un file tolto dal repo va rimosso a mano sul server.
- Le pagine nuove vanno create con `-Titolo`; menu, Polylang e opzioni restano negli script dedicati.
