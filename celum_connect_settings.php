<?php
///////////////// SETTINGS PAGE /////////////////
add_action( 'admin_menu', 'celum_connect_admin_menu' );
function celum_connect_admin_menu() {
    add_options_page( __('celum:connect', 'celum_connect' ), __('celum:connect', 'celum_connect' ), 'manage_options', 'celum_connect', 'celum_connect_options_page' );
}

function celum_connect_options_page() {
    ?>
    <div class="wrap">
        <h2><?php _e('celum:connect', 'celum_connect'); ?></h2>
        <form action="options.php" method="POST">
            <?php settings_fields('celum_connect-settings-group'); ?>
            <?php do_settings_sections('celum_connect'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
<?php }

add_action( 'admin_init', 'celum_connect_admin_init' );
function celum_connect_admin_init() {
    register_setting( 'celum_connect-settings-group', 'celum_connect-settings', 'celum_connect_settings_validate' );
    register_setting( 'celum_connect-settings-group', 'celum_connect-settings' );

    add_settings_section( 'settings', __( 'Settings', 'celum_connect' ), 'celum_connect_settings_callback', 'celum_connect' );
    add_settings_field( 'license_key', __( 'License key*', 'celum_connect' ), 'celum_connect_license_key_callback', 'celum_connect', 'settings' );
    add_settings_field( 'api_key', __( 'API Key*', 'celum_connect' ), 'celum_connect_api_key_callback', 'celum_connect', 'settings' );
	add_settings_field( 'directdownload_secret', __( 'Directdownload secret', 'celum_connect' ), 'celum_connect_directdownload_secret_callback', 'celum_connect', 'settings' );
	add_settings_field( 'root_node', __( 'Root Node ID*', 'celum_connect' ), 'celum_connect_root_node_callback', 'celum_connect', 'settings' );
    add_settings_field('version', __( 'Asset Picker Version', 'celum_connect' ), 'celum_connect_version_callback', 'celum_connect', 'settings');
    add_settings_field('usage_download', __( 'Usage (download)', 'celum_connect' ), 'celum_connect_usage_download_callback', 'celum_connect', 'settings');
    add_settings_field('usage_link', __( 'Usage (link)', 'celum_connect' ), 'celum_connect_usage_link_callback', 'celum_connect', 'settings');
	add_settings_field('description_infofield', __( 'Description infofield', 'celum_connect' ), 'celum_connect_description_infofield_callback', 'celum_connect', 'settings');
    add_settings_field( 'tab_name', __( 'Tab name', 'celum_connect' ), 'celum_connect_tab_name_callback', 'celum_connect', 'settings' );
    add_settings_section( 'downloadformats', __( 'Download Formats', 'celum_connect' ), 'celum_connect_downloadformats_callback', 'celum_connect' );
    add_settings_field( 'supported_dfs', __( 'Supported Downloadformats*', 'celum_connect' ), 'celum_connect_supported_dfs_callback', 'celum_connect', 'downloadformats' );
    add_settings_field( 'unknown_df', __( 'Unknown*', 'celum_connect' ), 'celum_connect_unknown_df_callback', 'celum_connect', 'downloadformats' );
    add_settings_field( 'image_df', __( 'Image*', 'celum_connect' ), 'celum_connect_image_df_callback', 'celum_connect', 'downloadformats' );
    add_settings_field( 'document_df', __( 'Document*', 'celum_connect' ), 'celum_connect_document_df_callback', 'celum_connect', 'downloadformats' );
    add_settings_field( 'video_df', __( 'Video*', 'celum_connect' ), 'celum_connect_video_df_callback', 'celum_connect', 'downloadformats' );
    add_settings_field( 'audio_df', __( 'Audio*', 'celum_connect' ), 'celum_connect_audio_df_callback', 'celum_connect', 'downloadformats' );
    add_settings_field( 'text_df', __( 'Text*', 'celum_connect' ), 'celum_connect_text_df_callback', 'celum_connect', 'downloadformats' );
	add_settings_section( 'publicurls', __( 'Public Urls', 'celum_connect' ), 'celum_connect_publicurls_callback', 'celum_connect' );
    add_settings_field( 'dlf_publicUrl_mapping', __( 'Downloadformat Public URL Mapping', 'celum_connect' ), 'celum_connect_dlf_publicUrl_mapping', 'celum_connect', 'publicurls' );

	/*	add_settings_field( 'unknown_stagehandler', __( 'Unknown stage handler', 'celum_connect' ), 'celum_connect_unknown_stagehandler_callback', 'celum_connect', 'publicurls' );
	add_settings_field( 'unknown_description', __( 'Unknown description', 'celum_connect' ), 'celum_connect_unknown_description_callback', 'celum_connect', 'publicurls' );
	add_settings_field( 'image_stagehandler', __( 'Image stage handler', 'celum_connect' ), 'celum_connect_image_stagehandler_callback', 'celum_connect', 'publicurls' );
	add_settings_field( 'image_description', __( 'Image description', 'celum_connect' ), 'celum_connect_image_description_callback', 'celum_connect', 'publicurls' );
	add_settings_field( 'document_stagehandler', __( 'Document stage handler', 'celum_connect' ), 'celum_connect_document_stagehandler_callback', 'celum_connect', 'publicurls' );
	add_settings_field( 'document_description', __( 'Document description', 'celum_connect' ), 'celum_connect_document_description_callback', 'celum_connect', 'publicurls' );
	add_settings_field( 'video_stagehandler', __( 'Video stage handler', 'celum_connect' ), 'celum_connect_video_stagehandler_callback', 'celum_connect', 'publicurls' );
	add_settings_field( 'video_description', __( 'Video description', 'celum_connect' ), 'celum_connect_video_description_callback', 'celum_connect', 'publicurls' );
	add_settings_field( 'audio_stagehandler', __( 'Audio stage handler', 'celum_connect' ), 'celum_connect_audio_stagehandler_callback', 'celum_connect', 'publicurls' );
	add_settings_field( 'audio_description', __( 'Audio description', 'celum_connect' ), 'celum_connect_audio_description_callback', 'celum_connect', 'publicurls' );
	add_settings_field( 'text_stagehandler', __( 'Text stagehandler', 'celum_connect' ), 'celum_connect_text_stagehandler_callback', 'celum_connect', 'publicurls' );
	add_settings_field( 'text_description', __( 'Text description', 'celum_connect' ), 'celum_connect_text_description_callback', 'celum_connect', 'publicurls' );
	*/celum_connect_settings_validate((array) get_option( 'celum_connect-settings' ));
}

function celum_connect_settings_callback() {
    _e( 'Specify the required settings for celum:connect', 'celum_connect' );
}
function celum_connect_downloadformats_callback() {
    _e( 'Enter the the supported downloadformat-IDs and the default downloadformat-IDs for the various file types', 'celum_connect' );
}

function celum_connect_publicurls_callback() {
	_e( 'Enter the Mapping from downloadformat id to public url Stage Handler and descriptions with the schema {Downloadformat ID}:{Public URL stage handler};{Public URL description} e.g.: 1:Asset Exporter;public_orig,6:Asset Exporter;public_jpg,10:Asset Exporter;public_lowres', 'celum_asset_picker' );
}

function celum_connect_api_key_callback() {
    $settings = (array) get_option( 'celum_connect-settings' );
    $field = "api_key";
    $value = esc_attr( $settings[$field] );
    echo "<input type='text' name='celum_connect-settings[$field]' value='$value' style='min-width: 500px;'/>";
}
function celum_connect_root_node_callback() {
    $settings = (array) get_option( 'celum_connect-settings' );
    $field = "root_node";
    $value = esc_attr( $settings[$field] );
    echo "<input type='text' name='celum_connect-settings[$field]' value='$value' />";
}

function  celum_connect_version_callback() {
    $settings = (array) get_option( 'celum_connect-settings' );
    $field = "version";
    $items = array("2.0", "2.1", "2.2", "2.3", "2.4", "2.5", "2.5.1", "2.5.2", "3.0", "6.8.11", "6.9.3","6.10.2","6.11.9","6.12.4","6.13.10","6.14.12","6.15.12","6.16.14","6.17.4","6.18.4","6.19.1");
    echo "<select id='version' name='celum_connect-settings[$field]'>";
    foreach($items as $item) {
        $selected = ($settings['version']==$item) ? 'selected="selected"' : '';
        echo "<option value='$item' $selected>$item</option>";
    }
    echo "</select>";
}

function celum_connect_usage_download_callback() {
    $settings = (array) get_option( 'celum_connect-settings' );
    $field = "usage_download";
    $value = esc_attr( $settings[$field] );
    echo "<input type='text' name='celum_connect-settings[$field]' value='$value' />";
}

function celum_connect_usage_link_callback() {
    $settings = (array) get_option( 'celum_connect-settings' );
    $field = "usage_link";
    $value = esc_attr( $settings[$field] );
    echo "<input type='text' name='celum_connect-settings[$field]' value='$value' />";
}

function celum_connect_description_infofield_callback() {
	$settings = (array) get_option( 'celum_connect-settings' );
	$field = "description_infofield";
	$value = esc_attr( $settings[$field] );
	echo "<input type='text' name='celum_connect-settings[$field]' value='$value' />";
}

function celum_connect_license_key_callback() {
    $settings = (array) get_option( 'celum_connect-settings' );
    $field = "license_key";
    $value = esc_attr( $settings[$field] );
    echo "<input type='text' name='celum_connect-settings[$field]' value='$value'  style='min-width: 500px;'/>";
}
function celum_connect_tab_name_callback() {
    $settings = (array) get_option( 'celum_connect-settings' );
    $field = "tab_name";
    $value = esc_attr( $settings[$field] );
    echo "<input type='text' name='celum_connect-settings[$field]' value='$value' />";
}
function celum_connect_directdownload_secret_callback() {
	$settings = (array) get_option( 'celum_connect-settings' );
	$field = "directdownload_secret";
	$value = esc_attr( $settings[$field] );
	echo "<input type='text' name='celum_connect-settings[$field]' value='$value' style='min-width: 500px;'/>";
}
function celum_connect_unknown_df_callback() {
    $settings = (array) get_option( 'celum_connect-settings' );
    $field = "unknown_df";
    $value = esc_attr( $settings[$field] );
    echo "<input type='number' name='celum_connect-settings[$field]' value='$value' />";
}
function celum_connect_image_df_callback() {
    $settings = (array) get_option( 'celum_connect-settings' );
    $field = "image_df";
    $value = esc_attr( $settings[$field] );
    echo "<input type='number' name='celum_connect-settings[$field]' value='$value' />";
}
function celum_connect_document_df_callback() {
    $settings = (array) get_option( 'celum_connect-settings' );
    $field = "document_df";
    $value = esc_attr( $settings[$field] );
    echo "<input type='number' name='celum_connect-settings[$field]' value='$value' />";
}
function celum_connect_video_df_callback() {
    $settings = (array) get_option( 'celum_connect-settings' );
    $field = "video_df";
    $value = esc_attr( $settings[$field] );
    echo "<input type='number' name='celum_connect-settings[$field]' value='$value' />";
}
function celum_connect_audio_df_callback() {
    $settings = (array) get_option( 'celum_connect-settings' );
    $field = "audio_df";
    $value = esc_attr( $settings[$field] );
    echo "<input type='number' name='celum_connect-settings[$field]' value='$value' />";
}
function celum_connect_text_df_callback() {
    $settings = (array) get_option( 'celum_connect-settings' );
    $field = "text_df";
    $value = esc_attr( $settings[$field] );
    echo "<input type='number' name='celum_connect-settings[$field]' value='$value' />";
}
function celum_connect_supported_dfs_callback() {
    $settings = (array) get_option( 'celum_connect-settings' );
    $field = "supported_dfs";
    $value = esc_attr( $settings[$field] );
    echo "<input type='text' name='celum_connect-settings[$field]' value='$value'  style='min-width: 500px;'/>";
}

function celum_connect_dlf_publicUrl_mapping() {
    $settings = (array) get_option( 'celum_connect-settings' );
    $field = "dlf_publicUrl_mapping";
    $value = esc_attr( $settings[$field] );
    echo "<input type='text' name='celum_connect-settings[$field]' value='$value' style='min-width: 1000px;'>";
}

function celum_connect_unknown_stagehandler_callback() {
	$settings = (array) get_option( 'celum_connect-settings' );
	$field = "unknown_stagehandler";
	$value = esc_attr( $settings[$field] );
	echo "<input type='text' name='celum_connect-settings[$field]' value='$value' />";
}

function celum_connect_unknown_description_callback() {
	$settings = (array) get_option( 'celum_connect-settings' );
	$field = "unknown_description";
	$value = esc_attr( $settings[$field] );
	echo "<input type='text' name='celum_connect-settings[$field]' value='$value' />";
}

function celum_connect_image_stagehandler_callback() {
	$settings = (array) get_option( 'celum_connect-settings' );
	$field = "image_stagehandler";
	$value = esc_attr( $settings[$field] );
	echo "<input type='text' name='celum_connect-settings[$field]' value='$value' />";
}

function celum_connect_image_description_callback() {
	$settings = (array) get_option( 'celum_connect-settings' );
	$field = "image_description";
	$value = esc_attr( $settings[$field] );
	echo "<input type='text' name='celum_connect-settings[$field]' value='$value' />";
}

function celum_connect_document_stagehandler_callback() {
	$settings = (array) get_option( 'celum_connect-settings' );
	$field = "document_stagehandler";
	$value = esc_attr( $settings[$field] );
	echo "<input type='text' name='celum_connect-settings[$field]' value='$value' />";
}

function celum_connect_document_description_callback() {
	$settings = (array) get_option( 'celum_connect-settings' );
	$field = "document_description";
	$value = esc_attr( $settings[$field] );
	echo "<input type='text' name='celum_connect-settings[$field]' value='$value' />";
}

function celum_connect_video_stagehandler_callback() {
	$settings = (array) get_option( 'celum_connect-settings' );
	$field = "video_stagehandler";
	$value = esc_attr( $settings[$field] );
	echo "<input type='text' name='celum_connect-settings[$field]' value='$value' />";
}

function celum_connect_video_description_callback() {
	$settings = (array) get_option( 'celum_connect-settings' );
	$field = "video_description";
	$value = esc_attr( $settings[$field] );
	echo "<input type='text' name='celum_connect-settings[$field]' value='$value' />";
}

function celum_connect_audio_stagehandler_callback() {
	$settings = (array) get_option( 'celum_connect-settings' );
	$field = "audio_stagehandler";
	$value = esc_attr( $settings[$field] );
	echo "<input type='text' name='celum_connect-settings[$field]' value='$value' />";
}

function celum_connect_audio_description_callback() {
	$settings = (array) get_option( 'celum_connect-settings' );
	$field = "audio_description";
	$value = esc_attr( $settings[$field] );
	echo "<input type='text' name='celum_connect-settings[$field]' value='$value' />";
}

function celum_connect_text_stagehandler_callback() {
	$settings = (array) get_option( 'celum_connect-settings' );
	$field = "text_stagehandler";
	$value = esc_attr( $settings[$field] );
	echo "<input type='text' name='celum_connect-settings[$field]' value='$value' />";
}

function celum_connect_text_description_callback() {
	$settings = (array) get_option( 'celum_connect-settings' );
	$field = "text_description";
	$value = esc_attr( $settings[$field] );
	echo "<input type='text' name='celum_connect-settings[$field]' value='$value' />";
}
//////// INPUT VALIDATION ////////
function celum_connect_settings_validate( $input ) {
    celum_connect_createPickerConfig($input);
    return $input;
}

function celum_connect_createPickerConfig($input){
	$expired = false;
	if(strpos(celum_connect_decrypt($input['license_key']), '_') !== false){
		list($url, $t) = explode("_", celum_connect_decrypt($input['license_key']).'_');
		$now=time();
		if($now > $t){
			$expired = true;
		}
	}else{
		$expired = true;
	}
	if($expired){
		$script="license is expired!";
	}else{
		$locale = substr(get_locale(),0,2);
		$script="Custom.AssetPickerConfig = {
    endPoint: '".$url."/cora',
    apiKey: '".$input['api_key']."',
    locale: '".$locale."',
    searchScope: {
      rootNodes: [".$input['root_node']."]
    },
    downloadFormats: {
      defaults: {
        unknown: ".$input['unknown_df'].",
        image: ".$input['image_df'].",
        document:".$input['document_df'].",
        video: ".$input['video_df'].",
        audio: ".$input['audio_df'].",
        text: ".$input['text_df']."
        },
        supported: [".$input['supported_dfs']."],
        additionalDownloadFormats: [".$input['supported_dfs']."]
    },
    requiredAssetData: ['fileCategory', 'versionInformation', 'fileInformation'],".($input['description_infofield'] == "" ? "" :
				"
    assetInformationFields: ['".$input['description_infofield']."'],"). "
    forceDownloadSelection: true,
    keepSelectionOnExport: false
};";
    }
	$file = plugin_dir_path(__FILE__).'assetPicker/config.js';
	file_put_contents($file, $script);

}

function celum_connect_decode_base64($sData){
    $sBase64 = strtr($sData, '-_', '+/');
    return base64_decode($sBase64.'==');
}

function celum_connect_decrypt($sData){
    $secretKey = "ZbMchtd9DivzjPDi5QIio1iVERFnNZiSE33QKY3Gw9rYfCNLFiKloJQt3zi4";
    $sResult = '';
    $sData   = celum_connect_decode_base64($sData);
    for($i=0;$i<strlen($sData);$i++){
        $sChar    = substr($sData, $i, 1);
        $sKeyChar = substr($secretKey, ($i % strlen($secretKey)) - 1, 1);
        $sChar    = chr(ord($sChar) - ord($sKeyChar));
        $sResult .= $sChar;
    }
    return $sResult;
}

?>
