<?php
/*
Plugin Name: FinAid Utilities
Description: Custom utilities and functionality for the Financial Aid WordPress site.
Version: 1.0.0
Author: UCF Web Communications
License: GPL3
GitHub Plugin URI: UCF/FinAid-Utilities
*/

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'FINAID_UTILS__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once FINAID_UTILS__PLUGIN_DIR . 'plugin-filters/post-list-layout.php';
require_once FINAID_UTILS__PLUGIN_DIR . 'plugin-filters/taxonomy-filters.php';
