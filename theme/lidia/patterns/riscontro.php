<?php
/**
 * Title: Riscontro
 * Slug: lidia/riscontro
 * Categories: lidia-gesti
 * Block Types: core/paragraph
 * Inserter: no
 * Description: La prova attaccata all'affermazione — fonte, norma, documento o dato. Massimo 4 per pagina, massimo 1 per sezione.
 *
 * ARCHIVIO — gesto del concept del 15/09, fuori uso dal 16/09/2026 (direzione
 * Palette D). Tenuto da parte per le pagine interne: prima di riprenderlo vanno
 * riportati i token (colori e corpi tipografici) sulla palette in uso.
 *
 * @package Lidia
 */

?>
<!-- wp:paragraph {"className":"is-style-riscontro","fontSize":"riscontro"} -->
<p class="is-style-riscontro has-riscontro-font-size">— <?php echo esc_html__( 'Fonte o documento a cui l\'affermazione qui sopra si appoggia, con il', 'lidia' ); ?> <a href="#"><?php echo esc_html__( 'riferimento verificabile', 'lidia' ); ?></a>.</p>
<!-- /wp:paragraph -->
