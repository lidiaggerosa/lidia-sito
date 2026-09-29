# Guida rapida — Box funzione e mock (pagina /laboratorio/)

Ogni funzione è un **box**: testo a sinistra, mock a destra (o specchiato). I mock si generano
con Claude come widget HTML autonomi e si incollano in un blocco *HTML personalizzato*.
Serve un utente **amministratore o editor**.

## 1. Inserire un box

`+` → scheda **Pattern** → **Lidia — sezioni** → **Funzione · box** oppure **Funzione · box
(invertito)** (mock a sinistra). Alternarli: uno dritto, uno invertito.

## 2. Orientarsi nella pagina

In alto a sinistra clicca l'icona con le **tre righe orizzontali** (≡, "Vista elenco"): a sinistra
si apre l'albero dei blocchi.

- Il **primo Gruppo** è l'intestazione della pagina (occhiello, titolo, testo). Non si tocca.
- Ogni Gruppo successivo è un **box**. Aprilo con la freccina: dentro c'è un Gruppo che contiene
  due Gruppi affiancati — il primo è la colonna **testo**, il secondo la colonna **mock**.
  Cliccando un elemento nell'albero, sulla pagina si evidenzia.

## 3. Colonna testo

- **Titolo, descrizione, tre punti**: clicca e scrivi. I punti si duplicano o si tolgono.
- **Icona**: seleziona il titolo → barra laterale destra → *Avanzate* → *Classi CSS aggiuntive*
  → cambia `lidia-icona-…` con una di: `conoscenza`, `contesto`, `ragionamento`, `output`,
  `scudo`, `nomodello`, `europa`, `verifica`.

## 4. Colonna mock — incollare il widget

1. Apri il file del widget (es. `mock/<nome>.html`) con **Blocco note**, non con il
   browser. **Ctrl+A**, poi **Ctrl+C**.
2. In WordPress, nell'albero, apri il Gruppo **mock** del box: dentro ci sono paragrafi ed
   elenchi d'esempio. Cliccali uno per uno e cancellali (tasto **Canc**). Il Gruppo deve restare
   **vuoto**.
3. Clicca il Gruppo vuoto: sulla pagina compare un **+** nel riquadro. Cliccalo.
4. Scrivi **html** nella ricerca e scegli **HTML personalizzato**.
5. Nel riquadro nero: **Ctrl+V**.
6. **Salva** in alto a destra, poi **Visualizza pagina**: il widget si anima. Nell'editor no,
   è normale.

## 5. Regole per generare i widget con Claude

Da incollare nel prompt, insieme alla descrizione del mock che si vuole ottenere:

> Produci un widget HTML autonomo da incollare in un blocco «HTML personalizzato» di WordPress,
> in una pagina che ne conterrà altri uguali a questo. Rispetta queste regole:
>
> 1. **Nessun** `<!DOCTYPE>`, `<html>`, `<head>`, `<body>`, `<title>`: solo un contenitore
>    `<div class="lidia-mock lidia-mock-NOME">` che racchiude `<style>`, il markup e `<script>`.
>    NOME è il nome della funzione in minuscolo con i trattini (es. `ricerca-legale`,
>    `workflow`, `add-in-word`).
> 2. **CSS**: ogni selettore inizia con `.lidia-mock-NOME` (es. `.lidia-mock-NOME .badge`).
>    Niente regole su `body`, `html`, `h1`–`h6`, `p` nudi. I nomi delle `@keyframes` iniziano
>    con `lidia-mock-`. Il titolo del widget è un `<p class="widget-title">`, non un `<h2>`.
> 3. **Larghezza**: il widget ha `width:100%; max-width:380px`, il contenitore lo centra con
>    `display:flex; justify-content:center`. Nessun `min-height:100vh`, nessun padding sul
>    contenitore.
> 4. **Nessun `id`**: gli elementi che lo script deve trovare usano attributi `data-…`
>    (`data-box`, `data-text`, `data-result`…).
> 5. **Script** chiuso in `(function(){ … })();`. La prima riga è
>    `var root=document.currentScript.closest('.lidia-mock'); if(!root) return;` e ogni
>    elemento si cerca con `root.querySelector('[data-…]')`, mai con `document.getElementById`.
>    Usa `var`, non `const`/`let` a livello superiore.
> 6. L'animazione va in loop da sola, senza interazione. Testi dell'esempio: verosimili,
>    in italiano, ambito legale, mai nomi di clienti reali.
> 7. Consegna un solo file `.html` con tutto dentro, senza spiegazioni.

I file generati si salvano nella cartella `mock/` del repo (da creare al primo widget), con il NOME
della funzione.

## 6. Se qualcosa non torna

- Cercando «html» non esce niente → il tema sul sito non è aggiornato (`inc/setup.php`): segnalarlo.
- Il widget appare dentro un secondo riquadro con bordo → manca `sezioni/funzione.css` aggiornato.
- Il codice si vede come testo sulla pagina → il blocco è un paragrafo, non *HTML personalizzato*:
  cancellalo e rifai dal punto 4.3.
- Con due o più box si anima solo il primo, o il titolo del box a sinistra cambia dimensione →
  il file non rispetta le regole 2, 4 o 5: rigenerarlo.
- L'HTML viene "pulito" al salvataggio → l'utente non è amministratore/editor.

## Non fare

- Non toccare le classi `lidia-funzione-*` dei gruppi.
- Non incollare il widget fuori dal Gruppo mock, né nell'intestazione della pagina.
- La pagina è privata: non pubblicarla.
