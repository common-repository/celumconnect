<?php
function celum_connect_block() {
	// Scripts.
	wp_register_script(
		'celum-connect-block-script', // Handle.
		plugins_url( 'block.js', __FILE__ ), // Block.js: We register the block here.
		array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-i18n', 'wp-editor' ), // Dependencies, defined above.
		filemtime( plugin_dir_path( __FILE__ ) . 'block.js' ),
		true // Load script in footer.
	);
	// Styles.
	wp_register_style(
		'celum-connect-block-editor-style', // Handle.
		plugins_url( 'editor.css', __FILE__ ), // Block editor CSS.
		array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
		filemtime( plugin_dir_path( __FILE__ ) . 'editor.css' )
	);
	wp_register_style(
		'celum-connect-block-frontend-style', // Handle.
		plugins_url( 'style.css', __FILE__ ), // Block editor CSS.
		array(), // Dependency to include the CSS after it.
		filemtime( plugin_dir_path( __FILE__ ) . 'style.css' )
	);

	// Register the block with WP using our namespacing
	// We also specify the scripts and styles to be used in the Gutenberg interface
	register_block_type( 'celum-connect/celum-connect-block', array(
		'editor_script' => 'celum-connect-block-script',
		'editor_style'  => 'celum-connect-block-editor-style',
		'style'         => 'celum-connect-block-frontend-style',
	) );
}

add_action( 'init', 'celum_connect_block' );

function assetPicker_enqueue() {
	$options = (array) get_option( 'celum_connect-settings' );
	$expired = false;
	if ( strpos( celum_connect_decrypt( $options['license_key'] ), '_' ) !== false ) {
		list( $url, $t ) = explode( "_", celum_connect_decrypt( $options['license_key'] ) . '_' );
		$now = time();
		if ( $now > $t ) {
			$expired = true;
		}
	}else{
		$expired = true;
	}
	$arr        = explode( "/", plugin_basename( __FILE__ ), 2 );
	$basename   = $arr[0];
	$data_array = array(
		'asset_picker_plugin_url' => plugins_url( '', __FILE__ ),
		'plugin_basename'         => $basename,
		'locale'                  => get_locale(),
		'expired'                 => $expired,
	);
	if ( ! $expired ) {
		wp_enqueue_script(
			'assetPicker-api',
			plugins_url( '../assetPicker/assetPicker_' . $options['version'] . '/assetPickerApi.js', __FILE__ )
		);
	}
	wp_enqueue_script(
		'assetPicker',
		plugins_url( '../assetPicker/load_block_picker.js', __FILE__ )

	);

    wp_localize_script( 'assetPicker-api', 'plugin_data', $data_array );
	wp_localize_script( 'assetPicker', 'plugin_data', $data_array );

}

add_action( 'enqueue_block_editor_assets', 'assetPicker_enqueue' );

