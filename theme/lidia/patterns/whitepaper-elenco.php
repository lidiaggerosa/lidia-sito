<?php
/**
 * Title: Whitepaper · 2 Elenco
 * Slug: lidia/whitepaper-elenco
 * Categories: lidia-sezioni
 * Description: L’elenco completo dei whitepaper, paginato. Ordinato per menu_order.
 *
 * Sezione di /risorse/whitepaper/, dal copy approvato il 14/09/2026.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"lidia-sezione lidia-risorse lidia-ris-tutti","align":"full","layout":{"type":"constrained","contentSize":"1040px"}} -->
<div class="wp-block-group alignfull lidia-sezione lidia-risorse lidia-ris-tutti"><!-- wp:group {"className":"lidia-colonna","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-colonna"><!-- wp:query {"queryId":0,"query":{"perPage":20,"pages":0,"offset":0,"postType":"risorsa","order":"asc","orderBy":"menu_order","inherit":false},"className":"lidia-ris-lista","layout":{"type":"default"}} -->
<div class="wp-block-query lidia-ris-lista"><!-- wp:post-template -->
<!-- wp:post-title {"isLink":true,"level":3,"className":"lidia-ris-titolo"} /-->

<!-- wp:post-excerpt {"showMoreOnNewLine":false,"className":"lidia-ris-sommario"} /-->
<!-- /wp:post-template -->

<!-- wp:query-pagination {"className":"lidia-ris-pagine","layout":{"type":"flex","justifyContent":"space-between"}} -->
<!-- wp:query-pagination-previous {"label":"← Più recenti"} /-->

<!-- wp:query-pagination-numbers /-->

<!-- wp:query-pagination-next {"label":"Meno recenti →"} /-->
<!-- /wp:query-pagination -->

<!-- wp:query-no-results -->
<!-- wp:paragraph {"className":"lidia-ris-vuoto"} -->
<p class="lidia-ris-vuoto">Non c’è ancora niente qui. Il primo documento arriva presto.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
