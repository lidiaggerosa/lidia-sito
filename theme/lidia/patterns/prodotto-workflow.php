<?php
/**
 * Title: Prodotto · 4 Workflow
 * Slug: lidia/prodotto-workflow
 * Categories: lidia-sezioni
 * Description: Box funzione invertito (mock a sinistra): icona, titolo, descrizione, tre punti. La colonna mock porta il riempitivo finché il widget `mock/workflow.html` non viene incollato come blocco HTML personalizzato (regole in docs/10a-guida-box-funzione.md).
 *
 * Sezione di /prodotto/, dal copy approvato il 24/09/2026. Riusa `funzione.css`.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"lidia-sezione lidia-prodotto lidia-funzione-box lidia-funzione-inverso","align":"full","anchor":"workflow","layout":{"type":"constrained","contentSize":"1040px"}} -->
<div id="workflow" class="wp-block-group alignfull lidia-sezione lidia-prodotto lidia-funzione-box lidia-funzione-inverso"><!-- wp:group {"className":"lidia-funzione-griglia","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-funzione-griglia"><!-- wp:group {"className":"lidia-funzione-testo","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-funzione-testo"><!-- wp:heading {"className":"lidia-icona lidia-icona-ragionamento"} -->
<h2 class="wp-block-heading lidia-icona lidia-icona-ragionamento">Workflow</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Le attività complesse — una due diligence, una revisione contrattuale, un parere — eseguite per passaggi logici, specializzati per materia. A ogni passaggio Lidia mostra cosa ha fatto e chiede conferma prima di procedere: il professionista controlla, corregge e decide.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"lidia-funzione-punti"} -->
<ul class="wp-block-list lidia-funzione-punti"><!-- wp:list-item -->
<li>Libreria di workflow per materia, compresa nel canone</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Check e conferme a ogni passaggio</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Passaggi tracciabili e verificabili</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"lidia-funzione-mock","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-funzione-mock"><!-- wp:paragraph {"className":"lidia-mock-titolo"} -->
<p class="lidia-mock-titolo">Workflow</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"lidia-demo-nota"} -->
<p class="lidia-demo-nota">Mock in arrivo — componente HTML, secondo passaggio</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
