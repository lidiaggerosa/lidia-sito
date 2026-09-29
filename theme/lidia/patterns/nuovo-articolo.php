<?php
/**
 * Title: Contenuto · Nuovo articolo
 * Slug: lidia/nuovo-articolo
 * Categories: lidia-contenuti
 * Description: L'ossatura di un articolo: apertura, due o tre sezioni, chiusura con rimando. Si inserisce in un articolo vuoto e si riempie.
 * Keywords: articolo, post, risorsa, nuovo
 *
 * Come il pattern del whitepaper: struttura, non testo da pubblicare.
 * La guida sta in docs/10-guida-redazione.md.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:paragraph -->
<p>[Apertura: che cosa è successo, e perché riguarda chi legge. Due o tre frasi, senza preamboli.]</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">[Prima sezione]</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>[Il contenuto.]</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">[Seconda sezione]</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>[Il contenuto.]</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Cosa vuol dire in pratica</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>[La conseguenza operativa per uno studio. Se non c'è, l'articolo non è finito.]</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"lidia-freccia"} -->
<p class="lidia-freccia"><a href="/prova-gratuita/">Richiedi una prova gratuita →</a></p>
<!-- /wp:paragraph -->
