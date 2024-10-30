<?php
/**
 *
 * Plugin Name: celum:connect
 * Description: Integrates the Celum Asset Picker to connect to Celum load assets directly to te media library and into new pages, posts or comments, or for wordpress 5 directly into the Gutenberg editor.
 * Version: 2.29
 * Author: brix cross media
 * Author URI: http://www.brix.ch/
 * License: GPL2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt *
 */
global $wp_version;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
//Define global constants.
if ( ! defined( 'celum_connect_VERSION' ) ) {
    define( 'celum_connect_VERSION', '2.0' );
}
if ( ! defined( 'celum_connect_NAME' ) ) {
    define( 'celum_connect_NAME', trim( dirname( plugin_basename( __FILE__ ) ), '/' ) );
}
if ( ! defined( 'celum_connect_DIR' ) ) {
    define( 'celum_connect_DIR', WP_PLUGIN_DIR . '/' . celum_connect_NAME );
}
if ( ! defined( 'celum_connect_URL' ) ) {
    define( 'celum_connect_URL', WP_PLUGIN_URL . '/' . celum_connect_NAME );
}

require_once('celum_connect_ajax.php');
require_once('celum_connect_settings.php');

if ( version_compare( $wp_version, '5.0', '>=' ) && !is_classic_editor_plugin_active()) {
    require_once( celum_connect_DIR . '/block/index.php' );
} else{
    require_once('celum_connect_media.php');
}

function is_classic_editor_plugin_active() {
    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    if ( is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
        return true;
    }
    return false;
}

//Loads the plugin's translated strings.
function celum_connect_load_plugin_textdomain() {
    $domain = 'celum_connect';
    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
    load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
    load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'celum_connect_load_plugin_textdomain' );

add_action('wp_ajax_celum_connect_media_save_assets', 'celum_connect_media_save_assets');
add_action('wp_ajax_celum_connect_block_save_assets', 'celum_connect_block_save_assets');
add_action('wp_ajax_celum_connect_assets_to_media_library', 'celum_connect_assetsToMediaLibrary');


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
if(!$expired){
    add_action('admin_enqueue_scripts', function() use ($options) {
        $arr = explode("/", plugin_basename(__FILE__), 2);
        $basename = $arr[0];
        $data_array = array(
            'asset_picker_plugin_url' => plugins_url( '', __FILE__ ),
            'plugin_basename' => $basename,
            'my_security_nonce' => wp_create_nonce('asset_picker_security_nonce'),
        );
        wp_enqueue_script( 'celum_connect-tab', plugin_dir_url( __FILE__ ) . '/celum_connect_tab.js', array( 'jquery' ), '', true );
        wp_enqueue_script( 'load_media_picker_tab', plugins_url( 'assetPicker/load_media_picker_tab.js', __FILE__ ));
        wp_enqueue_style( 'celum_connect-tab', plugin_dir_url( __FILE__ ) . '/celum_connect_tab.css', false);
        wp_register_script( 'assetPickerAPI', plugins_url( 'assetPicker/assetPicker_'.$options['version'].'/assetPickerApi.js', __FILE__ ), array( 'jquery' ), '1.0.0', true);
        wp_localize_script( 'assetPickerAPI', 'plugin_data', $data_array );
        wp_enqueue_script( 'assetPickerAPI' );
    });
}

