<?php
/*
Plugin Name: WPLMS Custom Certificate Codes
Plugin URI: http://www.vibethemes.com
Description: Custom Certificate Codes Plugin for WPLMS
Version: 1.1
Author: VibeThemes
Author URI: http://www.VibeThemes.com/
*/


if ( ! defined( 'PLUGIN_DOMAIN' ) )
    define ( 'PLUGIN_DOMAIN', 'wplms_custom_certificate_codes' );
if(!defined('WPLMS_CERTIFICATE_CODES')){
    define('WPLMS_CERTIFICATE_CODES','wplms_certificate_code_settings');    
}
/*====== BEGIN VSLIDER======*/
include_once('includes/class.config.php');
include_once('includes/class.updater.php');
include_once('includes/class.init.php');
include_once('includes/class.process.php');
include_once('includes/class.settings.php');

add_action('plugins_loaded','wplms_custom_certificate_codes_load_translations');
function wplms_custom_certificate_codes_load_translations(){
    $locale = apply_filters("plugin_locale", get_locale(), PLUGIN_DOMAIN);
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', PLUGIN_DOMAIN, $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

    if ( file_exists( $mofile_global ) ) {
        load_textdomain( PLUGIN_DOMAIN, $mofile_global );
    } else {
        load_textdomain( PLUGIN_DOMAIN, $mofile_local );
    }   
}


add_action('admin_menu','init_wplms_custom_certificate_codes_settings',100);
function init_wplms_custom_certificate_codes_settings(){
    new wplms_custom_certificate_codes_settings;    
}

add_action('init','define_wplms_custom_certificate_codes');
function define_wplms_custom_certificate_codes(){
    new wplms_custom_certificate_codes;
}


function Wplms_Custom_Certificates_Codes_Update() {
    $license_key = trim( get_option( 'wplms_custom_certificate_codes_license_key' ) );
    $edd_updater = new Wplms_Custom_Certificates_Codes_Plugin_Updater( 'https://wplms.io', __FILE__, array(
            'version'   => '1.0',               
            'license'   => $license_key,        
            'item_name' => 'WPLMS Custom Certificate Codes',    
            'author'    => 'VibeThemes' 
        )
    );
}
add_action( 'admin_init', 'Wplms_Custom_Certificates_Codes_Update', 0 );