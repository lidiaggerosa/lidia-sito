<?php
/**
 * Title: Sicurezza · 5 Domande frequenti
 * Slug: lidia/sicurezza-faq
 * Categories: lidia-sezioni
 * Description: Quattro domande sul trattamento dei dati. Riusa il foglio della home, variante centrata (24/09: la pagina è a colonna singola).
 *
 * Sezione di /sicurezza/, dal copy approvato il 14/09/2026.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"lidia-sezione lidia-fiduciario lidia-faq lidia-faq-centrata","align":"full","anchor":"domande-frequenti","layout":{"type":"constrained","contentSize":"1040px"}} -->
<div id="domande-frequenti" class="wp-block-group alignfull lidia-sezione lidia-fiduciario lidia-faq lidia-faq-centrata"><!-- wp:group {"className":"lidia-colonna","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-colonna"><!-- wp:heading -->
<h2 class="wp-block-heading">Domande frequenti sulla sicurezza dei dati</h2>
<!-- /wp:heading -->

<!-- wp:group {"className":"lidia-domande","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-domande"><!-- wp:details {"showContent":true} -->
<details class="wp-block-details" open><summary>Dove risiedono i dati di un’AI legale?</summary><!-- wp:paragraph -->
<p>Dipende dal fornitore, e va verificato prima di caricare qualsiasi fascicolo. I dati trattati da Lidia risiedono su infrastruttura AWS in Unione Europea, cifrati a riposo e in transito. La residenza dei dati in Unione Europea è la condizione minima perché uno studio italiano possa usare un sistema di intelligenza artificiale su materiale coperto da segreto professionale.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>Usare l’intelligenza artificiale viola il segreto professionale?</summary><!-- wp:paragraph -->
<p>No, a tre condizioni: che i dati non escano dall’Unione Europea, che nessun terzo — compresi i fornitori dei modelli linguistici — vi acceda, e che il rapporto con il fornitore sia regolato da un accordo sul trattamento dei dati. Con Lidia le prime due sono proprietà dell’architettura; la terza è il Data Processing Agreement, che vi consegniamo prima della firma.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>I miei documenti vengono usati per addestrare l’AI?</summary><!-- wp:paragraph -->
<p>No. Nessun documento dello studio viene trasmesso o conservato dai fornitori dei modelli LLM, né utilizzato per addestrarli. È una proprietà dell’architettura, non un’impostazione da disattivare.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>Quali certificazioni deve avere un software AI per studi legali?</summary><!-- wp:paragraph -->
<p>Le due che contano sono <strong>ISO/IEC 27001</strong>, che certifica il sistema di gestione della sicurezza delle informazioni, e una verifica indipendente sulla sicurezza cloud come <strong>CSA STAR</strong>. Lidia ha entrambe, più ISO 9001 sui processi. Le dichiarazioni di conformità non certificate da un ente terzo non sono equivalenti.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
