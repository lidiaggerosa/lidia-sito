<?php
/**
 * Title: Prodotto · 9 Domande frequenti
 * Slug: lidia/prodotto-faq
 * Categories: lidia-sezioni
 * Description: Cinque domande, nessuna sovrapposizione con la home; le due sulle fonti sono il contenuto GEO della pagina. Riusa il foglio della home, variante centrata.
 *
 * Sezione di /prodotto/, dal copy approvato il 24/09/2026. Da replicare identiche nel JSON-LD `FAQPage` in Fase 7.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"lidia-sezione lidia-prodotto lidia-faq lidia-faq-centrata","align":"full","anchor":"faq","layout":{"type":"constrained","contentSize":"1040px"}} -->
<div id="faq" class="wp-block-group alignfull lidia-sezione lidia-prodotto lidia-faq lidia-faq-centrata"><!-- wp:group {"className":"lidia-colonna","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-colonna"><!-- wp:heading -->
<h2 class="wp-block-heading">Domande frequenti</h2>
<!-- /wp:heading -->

<!-- wp:group {"className":"lidia-domande","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-domande"><!-- wp:details {"showContent":true} -->
<details class="wp-block-details" open><summary>Che cos’è un software di intelligenza artificiale per studi legali?</summary><!-- wp:paragraph -->
<p>È un sistema che applica l’intelligenza artificiale alle attività proprie di uno studio: ricerca su fonti normative e giurisprudenziali ufficiali, analisi di fascicoli e contratti, redazione assistita. Si distingue da un assistente generalista perché lavora sul patrimonio documentale dello studio, cita le fonti che usa e rende verificabile ogni passaggio. Lidia è un software AI per studi legali con i dati in Unione Europea.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>Quali fonti normative usa Lidia?</summary><!-- wp:paragraph -->
<p>Normativa primaria nazionale ed europea e normativa secondaria delle autorità di settore: Banca d’Italia, IVASS, CONSOB. Le fonti ufficiali sono comprese nel canone, senza abbonamenti separati a banche dati.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>Quali fonti giurisprudenziali usa Lidia?</summary><!-- wp:paragraph -->
<p>Corte Suprema di Cassazione (Sezioni Unite, Civile, Penale, Lavoro, Tributaria); Corte Costituzionale, Corte dei Conti, Corte di Giustizia UE e Corte EDU; Consiglio di Stato, TAR di tutte le sedi, giustizia tributaria di primo e secondo grado; una selezione di sentenze di Corti d’Appello e Tribunali ordinari.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>Che cos’è GraphRAG e perché conta nella ricerca giuridica?</summary><!-- wp:paragraph -->
<p>GraphRAG è una tecnica di recupero delle informazioni che, oltre a cercare documenti simili alla domanda, ricostruisce le relazioni fra le entità coinvolte — una norma, le sentenze che la applicano, i precedenti collegati. Nel diritto conta perché la risposta utile non è quasi mai un documento isolato: è la catena che li lega. Lidia usa GraphRAG per la ricerca su fonti ufficiali.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>Lidia funziona dentro Microsoft Word?</summary><!-- wp:paragraph -->
<p>Sì. La redazione e la revisione dei documenti avvengono in Word tramite add-in: l’analisi e le bozze prodotte da Lidia arrivano nel file su cui lo studio sta già lavorando, senza esportazioni né copia-incolla.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
