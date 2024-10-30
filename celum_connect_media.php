<?php
//Adds the celum:connect tab to the media upload tabs
function celum_connect_upload_tab($tabs) {
	$options = (array)get_option('celum_connect-settings');
	if(array_key_exists('tab_name', $options) && $options['tab_name'] != ''){
		$tab_name = $options['tab_name'];
	}else{
		$tab_name = 'celum:connect';
	}
	$tabs['celum_connect'] = $tab_name;
	return $tabs;
}
add_filter('media_upload_tabs', 'celum_connect_upload_tab');

// Adds the Asset Picker to the celum:connect tab
function celum_connect_add_asset_picker() {
	$options = get_option( 'celum_connect-settings' );
    $expired = false;
	if(strpos(celum_connect_decrypt($options['license_key']), '_') !== false){
		list($url, $t) = explode("_", celum_connect_decrypt($options['license_key']).'_');
		$now=time();
		if($now > $t){
			$expired = true;
		}
	}else{
		$expired = true;
    }
	if($expired){
		wp_iframe( 'license_expired');
	}else{
		wp_register_style( 'celum_connect-css', plugins_url( 'celum_connect-styles.css', __FILE__ ) );
		wp_enqueue_style( 'celum_connect-css' );

        wp_register_script( 'load_media_picker', plugins_url( 'assetPicker/load_media_picker.js', __FILE__ ), array( 'jquery' ), '1.0.0', true);
		wp_register_script( 'assetPickerAPI', plugins_url( 'assetPicker/assetPicker_'.$options['version'].'/assetPickerApi.js', __FILE__ ), array( 'jquery' ), '1.0.0', true);

		$translation_array = array(
			'loading' => __( 'Loading...', 'celum_asset_picker' ),
			'save_success' => __( 'Asset has been saved to your server and inserted into the editor.', 'celum_asset_picker'),
			'save_error' => __('Error saving Assets to server:', 'celum_asset_picker')
		);
		$arr = explode("/", plugin_basename(__FILE__), 2);
		$basename = $arr[0];
		$data_array = array(
			'asset_picker_plugin_url' => plugins_url( '', __FILE__ ),
			'plugin_basename' => $basename,
			'my_security_nonce' => wp_create_nonce('asset_picker_security_nonce'),
		);
		wp_localize_script( 'assetPickerAPI', 'plugin_data', $data_array );
		wp_enqueue_script( 'assetPickerAPI' );
        wp_localize_script( 'load_media_picker', 'messages', $translation_array );
		wp_localize_script( 'load_media_picker', 'plugin_data', $data_array );
		wp_enqueue_script( 'load_media_picker' );
		wp_iframe( 'celum_connect_form');
    }
}
add_action('media_upload_celum_connect', 'celum_connect_add_asset_picker');

function license_expired() {
	media_upload_header();
	echo file_get_contents(plugins_url( 'license_expired.html', __FILE__ ));
}

function celum_connect_form() {
	$options = (array) get_option( 'celum_connect-settings' );
	media_upload_header();
	echo file_get_contents(plugins_url( 'assetPicker/assetPicker_'.$options['version'].'/asset_picker.html', __FILE__ ));
}

function celum_connect_content() {
	echo file_get_contents(plugins_url( 'assetPicker/asset_picker.html', __FILE__ ));
}

add_action('update_option_strong_authentication_server', 'strong_auth_on_save_changes');
add_action('update_option_strong_authentication_verify_host', 'strong_auth_on_save_changes');

function celum_connect_head() {
	global $post;
	?>
	<script type="text/javascript">
        var cur_post_id = <?php echo $post->ID; ?>;
        var my_security_nonce = {
            security: '<?php echo wp_create_nonce('asset_picker_security_nonce');?>'
        };
        var asset_picker_plugin_url = '<?php echo plugins_url( '', __FILE__ ); ?>';
	</script>
	<?php

}

?>
