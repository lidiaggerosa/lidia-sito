<?php
/**
 * Title: Prodotto · 5 Add-in Word
 * Slug: lidia/prodotto-word
 * Categories: lidia-sezioni
 * Description: Box funzione (testo a sinistra, mock a destra): icona, titolo, descrizione, tre punti. La colonna mock porta il riempitivo finché il widget `mock/add-in-word.html` non viene incollato come blocco HTML personalizzato (regole in docs/10a-guida-box-funzione.md).
 *
 * Sezione di /prodotto/, dal copy approvato il 24/09/2026. Riusa `funzione.css`.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"lidia-sezione lidia-prodotto lidia-funzione-box","align":"full","anchor":"add-in-word","layout":{"type":"constrained","contentSize":"1040px"}} -->
<div id="add-in-word" class="wp-block-group alignfull lidia-sezione lidia-prodotto lidia-funzione-box"><!-- wp:group {"className":"lidia-funzione-griglia","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-funzione-griglia"><!-- wp:group {"className":"lidia-funzione-testo","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-funzione-testo"><!-- wp:heading {"className":"lidia-icona lidia-icona-output"} -->
<h2 class="wp-block-heading lidia-icona lidia-icona-output">Add-in Word</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Le modifiche arrivano direttamente nel documento, dove si fa il grosso del lavoro. Interroghi Lidia da Word, chiedi revisioni e approfondimenti sul testo aperto, ricevi le proposte in revisione: le verifichi e le approvi una per una.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"lidia-funzione-punti"} -->
<ul class="wp-block-list lidia-funzione-punti"><!-- wp:list-item -->
<li>Interroghi Lidia senza uscire da Word</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Modifiche proposte in revisione, mai imposte</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Approvi o rifiuti ogni intervento</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"lidia-funzione-mock","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-funzione-mock"><!-- wp:paragraph {"className":"lidia-mock-titolo"} -->
<p class="lidia-mock-titolo">Add-in Word</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"lidia-demo-nota"} -->
<p class="lidia-demo-nota">Mock in arrivo — componente HTML, secondo passaggio</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
