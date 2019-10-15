<?php
/**
 * Provides various filters for
 * taxonomies
 */
namespace FinAid\Utils\Plugins;

function disable_taxonomy_archives() {
	if ( is_category() || is_tag() ) {
		wp_redirect( home_url() );
	}
}

add_action( 'template_redirect', __NAMESPACE__ . '\disable_taxonomy_archives' );
