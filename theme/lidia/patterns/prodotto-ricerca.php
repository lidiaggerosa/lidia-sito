<?php
/**
 * Title: Prodotto · 3 Ricerca legale
 * Slug: lidia/prodotto-ricerca
 * Categories: lidia-sezioni
 * Description: Box funzione (testo a sinistra, mock a destra): icona, titolo, descrizione, tre punti. La colonna mock porta il riempitivo finché il widget `mock/ricerca-legale.html` non viene incollato come blocco HTML personalizzato (regole in docs/10a-guida-box-funzione.md).
 *
 * Sezione di /prodotto/, dal copy approvato il 24/09/2026. Riusa `funzione.css`.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"lidia-sezione lidia-prodotto lidia-funzione-box","align":"full","anchor":"ricerca-legale","layout":{"type":"constrained","contentSize":"1040px"}} -->
<div id="ricerca-legale" class="wp-block-group alignfull lidia-sezione lidia-prodotto lidia-funzione-box"><!-- wp:group {"className":"lidia-funzione-griglia","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-funzione-griglia"><!-- wp:group {"className":"lidia-funzione-testo","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-funzione-testo"><!-- wp:heading {"className":"lidia-icona lidia-icona-contesto"} -->
<h2 class="wp-block-heading lidia-icona lidia-icona-contesto">Ricerca legale</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Norme, sentenze e prassi dalle fonti ufficiali, collegate tra loro con tecnologia GraphRAG. Ogni risposta arriva con i riferimenti ai testi usati, così la verifica è una lettura e non una ricostruzione.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"lidia-funzione-punti"} -->
<ul class="wp-block-list lidia-funzione-punti"><!-- wp:list-item -->
<li>Fonti ufficiali comprese nel canone</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Ricerca web tramite un agente interno</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Riferimenti puntuali in ogni risposta</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph {"className":"lidia-freccia"} -->
<p class="lidia-freccia"><a href="/sicurezza/">Dove stanno i dati e chi può vederli →</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"lidia-funzione-mock","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-funzione-mock"><!-- wp:paragraph {"className":"lidia-mock-titolo"} -->
<p class="lidia-mock-titolo">Ricerca legale</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"lidia-demo-nota"} -->
<p class="lidia-demo-nota">Mock in arrivo — componente HTML, secondo passaggio</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
