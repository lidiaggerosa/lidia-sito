<?php
/**
 * Title: Funzione · box
 * Slug: lidia/funzione-box
 * Categories: lidia-sezioni
 * Description: Un box a tutta larghezza per una funzione: a sinistra icona, titolo e descrizione (più un elenco facoltativo), a destra il mock che simula l'esecuzione, fatto di blocchi normali con gli idiomi delle demo (lidia-fonti, lidia-passi, lidia-documenti, lidia-doc, lidia-risposta-*). L'icona si cambia dalla classe del titolo (lidia-icona-conoscenza, -contesto, -ragionamento, -output, -scudo, -nomodello, -europa, -verifica). Per alternare la disposizione usare «Funzione · box (invertito)». Nato nel laboratorio il 24/09/2026 per la nuova /prodotto/.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"lidia-sezione lidia-funzione-box","align":"full","layout":{"type":"constrained","contentSize":"1040px"}} -->
<div class="wp-block-group alignfull lidia-sezione lidia-funzione-box"><!-- wp:group {"className":"lidia-funzione-griglia","layout":{"type":"default"}} -->
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
<!-- /wp:list --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"lidia-funzione-mock","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-funzione-mock"><!-- wp:paragraph {"className":"lidia-mock-titolo"} -->
<p class="lidia-mock-titolo">Ricerca legale · Recesso nel contratto di appalto</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"lidia-fonti"} -->
<ul class="wp-block-list lidia-fonti"><!-- wp:list-item -->
<li>Normativa di riferimento — art. 1671 c.c.</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Orientamenti giurisprudenziali — Cass. civ., sez. II</li>
<!-- /wp:list-item -->
<!-- wp:list-item -->
<li>Conclusioni, con i rinvii alle fonti</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:paragraph {"className":"lidia-risposta-testo"} -->
<p class="lidia-risposta-testo">Il committente può recedere in ogni momento, tenendo indenne l'appaltatore delle spese sostenute, dei lavori eseguiti e del mancato guadagno.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"lidia-risposta-fonte"} -->
<p class="lidia-risposta-fonte">Art. 1671 c.c. · Cass. civ. 12345/2024</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"lidia-demo-nota"} -->
<p class="lidia-demo-nota">Esempio</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
