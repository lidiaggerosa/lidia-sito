<?php
/**
 * Title: Home · 9 Prova gratuita
 * Slug: lidia/home-form
 * Categories: lidia-home
 * Description: Fascia scura a due colonne (argomenti a sinistra, modulo a destra), quindi titolo a sinistra. Il modulo è di tipo `prova` senza la scelta iniziale prova/commerciale: dalla home il contatto è solo la prova (23/09/2026).
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"lidia-sezione lidia-form lidia-registro-scuro","align":"full","anchor":"prova","layout":{"type":"constrained","contentSize":"1040px"}} -->
<div id="prova" class="wp-block-group alignfull lidia-sezione lidia-form lidia-registro-scuro"><!-- wp:group {"className":"lidia-form-griglia","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-form-griglia"><!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:heading -->
<h2 class="wp-block-heading">Richiedi subito una prova gratuita di 7 giorni.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Compila il modulo e inizia a lavorare con Lidia sui tuoi documenti e sui tuoi flussi di lavoro.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"lidia-elenco-argomenti"} -->
<ul class="wp-block-list lidia-elenco-argomenti"><!-- wp:list-item -->
<li>Prova gratuita di 7 giorni, sui documenti reali dello studio</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Nessun dato trasmesso ai fornitori dei modelli</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Fonti ufficiali comprese nel canone</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"lidia-form-box","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-form-box"><!-- wp:lidia/modulo {"tipo":"prova"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
