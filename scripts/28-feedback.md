# 28 — Questionario di feedback: /feedback/

Pagina `noindex, nofollow`, fuori da menu, sitemap e ricerca. Template `page-feedback` (solo logo, solo riga legale).
Contenuto: blocco `lidia/feedback` (`inc/feedback.php`). Risposte in Notion, non in Delera.

## 1. Costanti in wp-config.php (una volta per ambiente)

```bash
cd ~/www/staging.lidiatech.ai/public_html        # produzione: ~/www/lidiatech.ai/public_html
wp config set LIDIA_NOTION_DB_UTENTI '<id del database Notion>' --type=constant
 wp config set LIDIA_NOTION_TOKEN '<token>' --type=constant
wp config has LIDIA_NOTION_TOKEN && echo OK
```

Token: lo fornisce l'owner, mai nel repo. Alla scadenza si sostituisce con lo stesso comando.

## 2. Tema (prima della pagina: il controllo dei blocchi deve trovare `lidia/feedback`)

```powershell
.\scripts\26-pubblica.ps1 -Tema functions.php,theme.json,inc\feedback.php,templates\page-feedback.html,parts\fb-testata.html,assets\css\sezioni\feedback.css,assets\js\feedback.js
```

## 3. Pagina

```powershell
.\scripts\26-pubblica.ps1 -Pagina feedback -Titolo "Il tuo parere su Lidia" -Template page-feedback
```

Produzione: stessa riga con `-Ambiente produzione`.

## 4. Verifica

- 1440, 768, 390; tastiera (Tab, frecce, Spazio), focus visibile.
- Invio completo dopo più di 4 secondi → una riga in «Feedback utenti Lidia», con i campi giusti.
- Invio in meno di 4 secondi → nessuna riga.
- Sorgente: `noindex, nofollow`; intestazione `X-Robots-Tag`. In produzione: assenti da `sitemap_index.xml`.
- Righe di prova cancellate in Notion.
