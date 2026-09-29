# Come si lavora

## Flusso

1. Branch da `main`: `fix/…`, `feat/…`, `contenuti/…`.
2. Commit piccoli, messaggio in italiano che dice cosa cambia.
3. Pull request verso `main`, compilando il template.
4. Review e merge dell'owner. Nessun merge senza approvazione.
5. Rilascio su staging, verifica, poi produzione con OK dell'owner (`docs/tecnico/ambienti-e-rilascio.md`).

`main` è sempre quello che c'è in produzione, o che sta per andarci.

## Regole

**Canale unico.** Tutto ciò che definisce il sito è un file in questo repo. Nell'admin di WordPress
si scrive testo dentro pagine che esistono già: non si creano pagine a mano, non si cambiano
impostazioni, non si installano plugin.

**Niente segreti nel repo.** ID GTM, webhook Delera, password, chiavi SSH: stanno in `wp-config.php`
o nei gestori di credenziali. Nel repo solo i nomi delle costanti.

**Niente codice nel database.** Nessun CSS aggiuntivo nel Customizer, nessun plugin di snippet,
nessun blocco «HTML personalizzato» (unica eccezione: i mock di `docs/redazione/box-funzione.md`).

**Plugin: default no.** Ogni plugin nuovo va proposto all'owner per iscritto, con il motivo.

**Design.**
- Token solo in `theme.json` (e `assets/css/tokens.css` per ciò che theme.json non esprime).
  Nei componenti sempre `var(--…)`, mai colori o misure scritti a mano.
- Solo **Fraunces + Inter**, self-hosted in `woff2`. Niente Google Fonts, niente terza famiglia.
- CSS di una sezione in `assets/css/sezioni/<nome>.css`, caricato solo dove la sezione compare.
- Niente jQuery, framework CSS o icon font. Icone in SVG.
- Niente glassmorphism, glow, ombre come elevazione, contatori animati, stock di persone in ufficio.

**Limiti tecnici.** CSS renderizzato ≤ 120 KB, JS di prima parte ≤ 100 KB, LCP < 2 s su mobile,
CLS < 0,05. Contrasto AA, focus visibile, heading in ordine, `alt` reali, `prefers-reduced-motion`.
Si giudica prima a 390 px: se è rotto su mobile, è rotto.

**URL.** Un permalink pubblicato non si cambia senza una riga in `migration/mappa-301.csv`.

**Tono dei testi.** Competente, asciutto, con prove. Niente superlativi, niente «rivoluziona».
Non si inventano prezzi, numeri, claim o nomi di clienti: arrivano dall'owner.
