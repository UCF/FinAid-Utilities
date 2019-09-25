<?php
/*
Plugin Name: FinAid Utilities
Description: Custom utilities and functionality for the Financial Aid WordPress site.
Version: 0.0.0
Author: UCF Web Communications
License: GPL3
GitHub Plugin URI: UCF/FinAid-Utilities
*/
namespace FinAid\Utils;

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'FINAID_UTILS__PLUGIN_FILE', __FILE__ );
define( 'FINAID_UTILS__PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'FINAID_UTILS__PLUGIN_URL', plugins_url( basename( dirname( __FILE__ ) ) ) );
define( 'FINAID_UTILS__STATIC_URL', FINAID_UTILS__PLUGIN_URL . '/static' );
define( 'FINAID_UTILS__JS_URL', FINAID_UTILS__STATIC_URL . '/js' );


// Admin files
require_once FINAID_UTILS__PLUGIN_PATH . 'admin/admin.php';

// Includes
// ...

// Plugin-dependent files
add_action( 'plugins_loaded', function() {

	// Download monitor
	if ( defined( 'DLM_PLUGIN_FILE' ) ) {
		require_once FINAID_UTILS__PLUGIN_PATH . 'admin/downloads.php';
	}

} );
