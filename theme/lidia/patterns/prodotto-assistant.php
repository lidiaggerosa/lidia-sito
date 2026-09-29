<?php
/**
 * Title: Prodotto · 6 AI Assistant
 * Slug: lidia/prodotto-assistant
 * Categories: lidia-sezioni
 * Description: Box funzione invertito (mock a sinistra): icona, titolo, descrizione, tre punti. La colonna mock porta il riempitivo finché il widget `mock/ai-assistant.html` non viene incollato come blocco HTML personalizzato (regole in docs/10a-guida-box-funzione.md). Chiude i quattro box con la CTA verso il modulo.
 *
 * Sezione di /prodotto/, dal copy approvato il 24/09/2026. Riusa `funzione.css`.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"lidia-sezione lidia-prodotto lidia-funzione-box lidia-funzione-inverso","align":"full","anchor":"ai-assistant","layout":{"type":"constrained","contentSize":"1040px"}} -->
<div id="ai-assistant" class="wp-block-group alignfull lidia-sezione lidia-prodotto lidia-funzione-box lidia-funzione-inverso"><!-- wp:group {"className":"lidia-funzione-griglia","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-funzione-griglia"><!-- wp:group {"className":"lidia-funzione-testo","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-funzione-testo"><!-- wp:heading {"className":"lidia-icona lidia-icona-conoscenza"} -->
<h2 class="wp-block-heading lidia-icona lidia-icona-conoscenza">AI Assistant</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>L’interrogazione libera di Lidia. Costruisci il contesto dai documenti dell’archivio o da zero, fissi l’obiettivo e lavori per affinamenti successivi: analisi, confronti, bozze, con i riferimenti a ciò che è stato usato.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"lidia-funzione-punti"} -->
<ul class="wp-block-list lidia-funzione-punti"><!-- wp:list-item -->
<li>Contesto dall’archivio o da documenti caricati ex novo</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Obiettivo definito dal professionista</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Riferimenti in ogni risposta</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"lidia-funzione-mock","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-funzione-mock"><!-- wp:paragraph {"className":"lidia-mock-titolo"} -->
<p class="lidia-mock-titolo">AI Assistant</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"lidia-demo-nota"} -->
<p class="lidia-demo-nota">Mock in arrivo — componente HTML, secondo passaggio</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:buttons {"className":"lidia-funzione-cta"} -->
<div class="wp-block-buttons lidia-funzione-cta"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#prova">Richiedi una prova gratuita</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
