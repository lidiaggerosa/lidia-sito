<?php
/**
 * Title: Gesto · Fatto isolato
 * Slug: lidia/fatto-isolato
 * Categories: lidia-gesti
 * Description: Una cifra sola con unità, contesto e fonte. Una per pagina: se i fatti che meritano sono due, il secondo resta testo.
 *
 * Ripreso in uso il 16/09/2026 sui token della Palette D, per le pagine interne.
 * Funziona in entrambi i registri: la cifra prende l'accento del registro.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"lidia-fatto","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-fatto"><!-- wp:paragraph {"className":"lidia-fatto__cifra","fontSize":"cifra"} -->
<p class="lidia-fatto__cifra has-cifra-font-size">0</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"lidia-fatto__contesto","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-fatto__contesto"><!-- wp:paragraph -->
<p><strong class="lidia-fatto__unita">dati</strong> trasmessi o conservati dai fornitori dei modelli LLM</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"lidia-fatto__fonte","fontSize":"riscontro"} -->
<p class="lidia-fatto__fonte has-riscontro-font-size">— e mai usati per addestrarli. Architettura Lidia, <a href="/sicurezza/">come sono protetti i dati</a>.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
