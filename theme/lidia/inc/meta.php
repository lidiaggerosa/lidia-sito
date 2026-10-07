<?php
/**
 * Campi custom, registrati nel tema.
 *
 * Niente ACF: la decisione aperta n. 5 aveva come default «campi via
 * register_post_meta», e finché i campi sono tre pannelli nativi bastano.
 *
 * @package Lidia
 */

defined( 'ABSPATH' ) || exit;

/**
 * Campi del CPT `risorsa`.
 *
 * - `lidia_gated`      il documento richiede il modulo prima del download
 * - `lidia_file`       ID dell'allegato in media library
 * - `lidia_delera_tag` etichetta con cui il lead entra in Delera
 * - `lidia_autori`     autori del documento, in chiaro («Nome Cognome, Nome Cognome»): li
 *                      mostra `single-risorsa.html` e li legge lo schema Article (07/10/2026)
 *
 * `lidia_file` non è esposto nella REST API pubblica: con l'ID dell'allegato chiunque
 * ricava l'indirizzo del file da /wp/v2/media, e il modulo non serve più a niente.
 * I tre campi si compilano dal pannello «Documento protetto» (qui sotto), che salva
 * con il form classico del post e non passa dalla REST.
 */
function lidia_registra_meta() {
	register_post_meta(
		'risorsa',
		'lidia_gated',
		array(
			'type'          => 'boolean',
			'single'        => true,
			'default'       => true,
			'show_in_rest'  => true,
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	register_post_meta(
		'risorsa',
		'lidia_file',
		array(
			'type'              => 'integer',
			'single'            => true,
			'show_in_rest'      => false,
			'sanitize_callback' => 'absint',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	register_post_meta(
		'risorsa',
		'lidia_delera_tag',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => false,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	register_post_meta(
		'risorsa',
		'lidia_autori',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'lidia_registra_meta' );

/**
 * L'ID dell'allegato non deve comparire nemmeno fra i meta della risposta REST del contenuto.
 *
 * `show_in_rest => false` basta per il campo registrato; questo filtro copre il caso in cui
 * un plugin esponga i meta grezzi.
 *
 * @param WP_REST_Response $risposta Risposta.
 * @return WP_REST_Response
 */
function lidia_meta_nascondi_file( $risposta ) {
	$dati = $risposta->get_data();

	if ( isset( $dati['meta'] ) && is_array( $dati['meta'] ) ) {
		unset( $dati['meta']['lidia_file'], $dati['meta']['lidia_delera_tag'] );
		$risposta->set_data( $dati );
	}

	return $risposta;
}
add_filter( 'rest_prepare_risorsa', 'lidia_meta_nascondi_file' );

/* -------------------------------------------------------------------------
 * Pannello «Documento protetto»
 *
 * Chi pubblica un whitepaper deve poter scegliere il PDF con un pulsante, non
 * scrivere un ID a mano. Il pannello è un meta box classico: si salva con il form
 * del post, quindi `lidia_file` resta fuori dalla REST come deciso il 21/09. Il
 * caricamento dalla libreria media avviene dalla pagina del whitepaper, e il tema
 * lo instrada in uploads/riservati/ (inc/delera.php).
 * ---------------------------------------------------------------------- */

/**
 * Registra il pannello sui whitepaper.
 */
function lidia_documento_meta_box() {
	add_meta_box(
		'lidia-documento-protetto',
		__( 'Documento protetto', 'lidia' ),
		'lidia_documento_meta_box_html',
		'risorsa',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes_risorsa', 'lidia_documento_meta_box' );

/**
 * Il PDF di un whitepaper sta nella cartella riservata?
 *
 * @param int $allegato ID dell'allegato.
 * @return bool
 */
function lidia_documento_e_riservato( $allegato ) {
	return function_exists( 'lidia_delera_file_riservato' ) && lidia_delera_file_riservato( $allegato );
}

/**
 * Markup del pannello.
 *
 * @param WP_Post $post Il whitepaper.
 */
function lidia_documento_meta_box_html( $post ) {
	$allegato = absint( get_post_meta( $post->ID, 'lidia_file', true ) );
	$tag      = (string) get_post_meta( $post->ID, 'lidia_delera_tag', true );
	$gated    = metadata_exists( 'post', $post->ID, 'lidia_gated' ) ? (bool) get_post_meta( $post->ID, 'lidia_gated', true ) : true;

	$nome      = '';
	$riservato = true;

	if ( $allegato && 'attachment' === get_post_type( $allegato ) ) {
		$nome      = wp_basename( (string) get_post_meta( $allegato, '_wp_attached_file', true ) );
		$riservato = lidia_documento_e_riservato( $allegato );
	} elseif ( $allegato ) {
		$allegato = 0;
	}

	wp_nonce_field( 'lidia_documento_' . $post->ID, 'lidia_documento_nonce' );
	?>
	<div class="lidia-documento" data-riservati="<?php echo esc_attr( LIDIA_CARTELLA_RISERVATA ); ?>">
		<input type="hidden" name="lidia_file" id="lidia-file" value="<?php echo esc_attr( $allegato ); ?>">

		<p class="lidia-documento__nome" id="lidia-documento-nome" <?php echo $nome ? '' : 'hidden'; ?>>
			<span class="dashicons dashicons-media-document" aria-hidden="true"></span>
			<strong><?php echo esc_html( $nome ); ?></strong>
		</p>

		<p class="lidia-documento__vuoto" id="lidia-documento-vuoto" <?php echo $nome ? 'hidden' : ''; ?>>
			<?php esc_html_e( 'Nessun PDF collegato: il modulo di download non ha niente da consegnare.', 'lidia' ); ?>
		</p>

		<p class="lidia-documento__avviso notice notice-warning inline" id="lidia-documento-avviso" <?php echo $riservato ? 'hidden' : ''; ?>>
			<?php esc_html_e( 'Questo file sta in una cartella pubblica: chi ne conosce l’indirizzo lo scarica senza modulo. Caricalo di nuovo da qui, poi salva.', 'lidia' ); ?>
		</p>

		<p>
			<button type="button" class="button" id="lidia-documento-scegli">
				<?php echo $nome ? esc_html__( 'Cambia PDF', 'lidia' ) : esc_html__( 'Scegli il PDF', 'lidia' ); ?>
			</button>
			<button type="button" class="button-link button-link-delete" id="lidia-documento-togli" <?php echo $nome ? '' : 'hidden'; ?>>
				<?php esc_html_e( 'Scollega', 'lidia' ); ?>
			</button>
		</p>

		<p class="description">
			<?php esc_html_e( 'Carica il PDF da qui, non dalla Libreria media: solo così finisce nella cartella riservata.', 'lidia' ); ?>
		</p>

		<hr>

		<p>
			<label>
				<input type="checkbox" name="lidia_gated" value="1" <?php checked( $gated ); ?>>
				<?php esc_html_e( 'Richiede il modulo prima del download', 'lidia' ); ?>
			</label>
		</p>

		<p>
			<label for="lidia-delera-tag"><?php esc_html_e( 'Etichetta in Delera', 'lidia' ); ?></label>
			<input type="text" class="widefat" id="lidia-delera-tag" name="lidia_delera_tag" value="<?php echo esc_attr( $tag ); ?>" placeholder="<?php echo esc_attr( $post->post_name ); ?>">
			<span class="description"><?php esc_html_e( 'Vuota: usa lo slug del whitepaper.', 'lidia' ); ?></span>
		</p>
	</div>
	<?php
}

/**
 * Salva i tre campi dal form del post.
 *
 * @param int $post_id ID del whitepaper.
 */
function lidia_documento_salva( $post_id ) {
	if ( ! isset( $_POST['lidia_documento_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['lidia_documento_nonce'] ), 'lidia_documento_' . $post_id ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$allegato = isset( $_POST['lidia_file'] ) ? absint( $_POST['lidia_file'] ) : 0;

	if ( $allegato && 'attachment' !== get_post_type( $allegato ) ) {
		$allegato = 0;
	}

	if ( $allegato ) {
		update_post_meta( $post_id, 'lidia_file', $allegato );
	} else {
		delete_post_meta( $post_id, 'lidia_file' );
	}

	update_post_meta( $post_id, 'lidia_gated', ! empty( $_POST['lidia_gated'] ) );

	$tag = isset( $_POST['lidia_delera_tag'] ) ? sanitize_text_field( wp_unslash( $_POST['lidia_delera_tag'] ) ) : '';

	if ( '' !== $tag ) {
		update_post_meta( $post_id, 'lidia_delera_tag', $tag );
	} else {
		delete_post_meta( $post_id, 'lidia_delera_tag' );
	}
}
add_action( 'save_post_risorsa', 'lidia_documento_salva' );

/**
 * Script del pannello: solo nella pagina di modifica di un whitepaper.
 *
 * @param string $schermata Hook della schermata admin.
 */
function lidia_documento_script( $schermata ) {
	if ( ! in_array( $schermata, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$post = get_post();

	if ( ! $post || 'risorsa' !== $post->post_type ) {
		return;
	}

	wp_enqueue_media( array( 'post' => $post ) );

	wp_enqueue_script(
		'lidia-documento-protetto',
		get_theme_file_uri( 'assets/js/documento-protetto.js' ),
		array( 'media-editor' ),
		lidia_versione_file( 'assets/js/documento-protetto.js' ),
		true
	);

	wp_localize_script(
		'lidia-documento-protetto',
		'lidiaDocumento',
		array(
			'titolo'  => __( 'PDF del whitepaper', 'lidia' ),
			'scegli'  => __( 'Usa questo PDF', 'lidia' ),
			'cambia'  => __( 'Cambia PDF', 'lidia' ),
			'nuovo'   => __( 'Scegli il PDF', 'lidia' ),
			'nonPdf'  => __( 'Il file scelto non è un PDF.', 'lidia' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'lidia_documento_script' );
