# 04 — Pagina /prezzi/

Ricostruisce la pagina prezzi dal file di blocchi versionato. Rieseguibile: se la pagina
esiste già, si aggiorna il contenuto invece di crearne una seconda.

Contenuto: `scripts/blocchi/prezzi.html` — somma delle otto sezioni `lidia/prezzi-*`, in
quest'ordine: testa, funzioni, personalizzazioni, materie, garanzie, prova, faq, chiusura.
Copy di riferimento: `content/it/prezzi.md` (approvato il 14/09/2026, rivisto il 24/09/2026).

## Crea

```
wp post create "<cartella del repo>\scripts\blocchi\prezzi.html" \
  --post_type=page --post_status=publish \
  --post_title="Quanto costa Lidia" \
  --post_name=prezzi \
  --page_template=page-composta \
  --porcelain
```

`--page_template=page-composta` è obbligatorio: la pagina ha il suo H1 dentro la prima
sezione, quindi non deve usare il template `page` che stampa il titolo.

## Aggiorna

```
wp post update <ID> "<cartella del repo>\scripts\blocchi\prezzi.html"
```

## Metadati Yoast

Title e meta description stanno nel front-matter di `content/it/prezzi.md`. Si impostano in
`scripts/09-yoast.md` insieme a quelli delle altre pagine, non a mano dall'admin.

## Da fare in Fase 7

JSON-LD `FAQPage` con le quattro domande di questa pagina, testo identico al visibile.

## Vincoli dal copy (§ «Note per la build»)

- **Nessun riferimento alla struttura dell'offerta**: niente licenze per utente, niente quote di
  piattaforma, niente scaglioni. Solo la soglia di partenza e il perimetro di cosa comprende.
- Il numero «125 €» compare **una volta sola**, in apertura. Il resto della pagina lo qualifica,
  non lo ripete come claim.
- Le voci di «Cosa comprende Lidia» non si compongono come tabella di piani a confronto: non
  esistono piani da confrontare. Righe con segno di spunta, due colonne, come il riquadro della home.
- «Personalizzazioni»: tre voci titolo + testo, senza icone e senza prezzo.
- «Moduli aggiuntivi» **non ha un pattern**: è solo un segnaposto nel copy, non si pubblica (24/09).
- «Tutto il diritto» e «La stessa sicurezza» sono due righe consecutive titolo a sinistra /
  descrizione a destra (`lidia-prz-riga`), la seconda senza filetto di sezione.
- Nessun gesto del concept in pagina.

## Nota sui fogli di stile

`sezioni/prezzi.css` è questa pagina. `sezioni/prezzo.css` è la fascia prezzo della **home**:
sono due cose diverse e le classi non si toccano (`lidia-prezzi` contro `lidia-prezzo`).
Il trattamento di `.lidia-cifra` è stato spostato in `base.css` il 17/09, perché lo usano
entrambe: era l'unico modo per non duplicarlo.
