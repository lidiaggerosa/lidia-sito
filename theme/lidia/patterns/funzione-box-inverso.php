<?php
/**
 * Title: Funzione · box (invertito)
 * Slug: lidia/funzione-box-inverso
 * Categories: lidia-sezioni
 * Description: Lo stesso box di «Funzione · box» con le colonne specchiate: mock a sinistra, testo a destra. Si alterna con quello dritto per dare ritmo alla pagina; su mobile il testo sta sempre sopra. La differenza è solo la classe lidia-funzione-inverso sul gruppo esterno.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"lidia-sezione lidia-funzione-box lidia-funzione-inverso","align":"full","layout":{"type":"constrained","contentSize":"1040px"}} -->
<div class="wp-block-group alignfull lidia-sezione lidia-funzione-box lidia-funzione-inverso"><!-- wp:group {"className":"lidia-funzione-griglia","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-funzione-griglia"><!-- wp:group {"className":"lidia-funzione-testo","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-funzione-testo"><!-- wp:heading {"className":"lidia-icona lidia-icona-output"} -->
<h2 class="wp-block-heading lidia-icona lidia-icona-output">Add-in Word</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Redazione e revisione senza uscire da Word. Lidia propone il testo e lo confronta con i precedenti dello studio; l'avvocato rivede dove ha sempre scritto, con il controllo umano su ogni modifica.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"lidia-funzione-punti"} -->
<ul class="wp-block-list lidia-funzione-punti"><!-- wp:list-item -->
<li>Bozze e revisioni dentro il documento</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Confronto con i modelli dello studio</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Ogni modifica tracciata e revisionabile</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"lidia-funzione-mock","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-funzione-mock"><!-- wp:paragraph {"className":"lidia-mock-titolo"} -->
<p class="lidia-mock-titolo">Word · Contratto di fornitura.docx</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"lidia-doc"} -->
<p class="lidia-doc">Il Fornitore <s>potrà</s> <mark>dovrà</mark> consegnare entro 30 giorni <s>dalla richiesta</s> <mark>dalla ricezione dell'ordine scritto</mark>, salvo diverso accordo tra le parti.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"lidia-demo-nota"} -->
<p class="lidia-demo-nota">Confrontato con il modello dello studio</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
