<?php
/**
 * Provides various filters for
 * taxonomies
 */
namespace FinAid\Utils\Plugins;

function disable_taxonomy_archives() {
	if ( is_category() || is_tag() || is_date() || is_author() ) {
		global $wp_query;
		$wp_query->set_404();
	}
}

add_action( 'template_redirect', __NAMESPACE__ . '\disable_taxonomy_archives' );
