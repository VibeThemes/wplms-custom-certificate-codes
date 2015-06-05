<?php
/*
Plugin Name: WPLMS Custom Certificate Codes
Plugin URI: http://www.vibethemes.com
Description: Custom Certificate Codes Plugin for WPLMS
Version: 1.0
Author: VibeThemes
Author URI: http://www.VibeThemes.com/
*/


if ( ! defined( 'PLUGIN_DOMAIN' ) )
    define ( 'PLUGIN_DOMAIN', 'wplms_custom_certificate_codes' );

/*====== BEGIN VSLIDER======*/
include_once('includes/class.config.php');
include_once('includes/class.init.php');
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