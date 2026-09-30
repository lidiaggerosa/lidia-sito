<?php
/**
 * Title: Home · 8 Domande frequenti
 * Slug: lidia/home-faq
 * Categories: lidia-home
 * Description: Otto risposte autoconsistenti, replicate identiche nel JSON-LD FAQPage. Sulla home la sezione è a colonna singola centrata (`lidia-faq-centrata`): titolo e fisarmonica al centro. Testi rivisti il 23/09/2026.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"lidia-sezione lidia-faq lidia-faq-centrata lidia-registro-alterno","align":"full","anchor":"domande-frequenti","layout":{"type":"constrained","contentSize":"1040px"}} -->
<div id="domande-frequenti" class="wp-block-group alignfull lidia-sezione lidia-faq lidia-faq-centrata lidia-registro-alterno"><!-- wp:heading -->
<h2 class="wp-block-heading">Domande frequenti</h2>
<!-- /wp:heading -->

<!-- wp:group {"className":"lidia-domande","layout":{"type":"default"}} -->
<div class="wp-block-group lidia-domande">
<!-- wp:details {"showContent":true} -->
<details class="wp-block-details" open><summary>Che cos'è un'AI legale?</summary><!-- wp:paragraph -->
<p>Un'AI legale è un sistema di intelligenza artificiale progettato per il lavoro giuridico: ricerca su fonti normative e giurisprudenziali ufficiali, analisi di documenti e contratti, redazione assistita. Si distingue da un'AI generalista per tre cose: le fonti sono verificabili e citate; il contesto è il fascicolo dello studio, le fonti ufficiali e le fonti web selezionate; ogni passaggio resta tracciabile. Lidia è una piattaforma AI legale italiana, con i dati che restano in Unione Europea.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>Che differenza c'è tra fare una domanda a un'AI e affidarle un lavoro?</summary><!-- wp:paragraph -->
<p>Una domanda produce una risposta; un lavoro legale è fatto di passaggi — cercare, confrontare, estrarre, redigere — da eseguire nell'ordine giusto. Lidia scompone l'obiettivo in questi passaggi, li esegue in sequenza e mostra il percorso. È ciò che fanno la ricerca legale e i <strong>Lidia Workflow</strong>: agenti specializzati che procedono per passaggi logici, tracciabili e verificabili, con il professionista sempre nel loop.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>Come funziona la ricerca legale con l'intelligenza artificiale?</summary><!-- wp:paragraph -->
<p>La ricerca legale con AI parte da una domanda in linguaggio naturale invece che da stringhe booleane. Lidia interroga fonti normative e giurisprudenziali ufficiali organizzate con tecnologia <strong>GraphRAG</strong>, che mette in relazione norme, sentenze e documenti, e gli agenti di Lidia restituiscono la risposta con i riferimenti ai testi usati. Quando serve uscire dal perimetro ufficiale, un agente di ricerca web interno estende il campo alle fonti online.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>L'intelligenza artificiale può analizzare e redigere contratti?</summary><!-- wp:paragraph -->
<p>Sì, con un limite preciso: l'AI produce analisi e bozza, l'avvocato decide e firma. Lidia legge contratti anche molto lunghi, con OCR avanzato su PDF e scansioni, individua le clausole, le confronta con i precedenti dello studio e accompagna la redazione dentro Microsoft Word, dove il testo si rivede come sempre, con l'aiuto di Lidia. Con Smart Answer interroghi un singolo documento fino a <strong>1.000 pagine</strong>.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>Un'AI legale può sbagliare? Come si verifica una risposta?</summary><!-- wp:paragraph -->
<p>Può sbagliare, come qualsiasi sistema generativo. Per questo conta il modo in cui la risposta arriva: Lidia cita i documenti e le fonti su cui si è basata e rende ispezionabile ogni passaggio del ragionamento, così la verifica è una lettura e non una ricostruzione. Il controllo umano resta obbligatorio su ogni output.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>I documenti del mio studio finiscono nei modelli AI?</summary><!-- wp:paragraph -->
<p>No. Nessun dato viene trasmesso o conservato dai fornitori dei modelli LLM, e nessun dato viene usato per addestrarli. Infrastruttura AWS con data residency UE, crittografia a riposo e in transito, ISO/IEC 27001, CSA STAR Level 1, ISO 9001, conformità GDPR.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>L'AI sostituisce il lavoro dell'avvocato?</summary><!-- wp:paragraph -->
<p>No. Lidia produce ricerca, analisi e bozze; decisione, responsabilità e firma restano al professionista. Il Workflow Builder replica il metodo dello studio invece di imporne uno nuovo. Lidia è costruita sul principio dello human in the loop: l'AI prepara, il professionista decide.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>Quanto costa un'intelligenza artificiale per studi legali?</summary><!-- wp:paragraph -->
<p>Lidia parte da <strong>125 € al mese</strong>. Il piano di partenza copre già tutte le materie del diritto: non ci sono moduli di materia da aggiungere per consultare le fonti. La prova è gratuita e si fa sui documenti reali dello studio. L'offerta è personalizzabile in base alle esigenze del singolo studio.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->
</div>
<!-- /wp:group --></div>
<!-- /wp:group -->
