<?php
/**
 * Functions that customize the admin interface for Downloads
 * (via the Download Monitor plugin)
 */
namespace FinAid\Utils\Admin\Downloads;


/**
 * Removes unused columns from the Downloads list admin view.
 *
 * @since 1.0.0
 * @author Jo Dickson
 * @param array $columns Existing column definitions
 * @return array Modified columns array
 */
function disable_columns( $columns ) {
	$remove_keys = array(
		'featured',
		'members_only',
		'redirect_only'
	);
	foreach ( $remove_keys as $key ) {
		if ( isset( $columns[$key] ) ) {
			unset( $columns[$key] );
		}
	}
	return $columns;
}

add_filter( 'manage_edit-dlm_download_columns', __NAMESPACE__ . '\disable_columns', 11, 1 );


/**
 * TODO
 */
function disable_download_options_metabox() {
	remove_meta_box( 'download-monitor-options', 'dlm_download', 'side' );
}

add_action( 'do_meta_boxes', __NAMESPACE__ . '\disable_download_options_metabox' );


/**
 * Remove "Browse for file" button under Download version add fields
 */
function disable_browse_for_file( $upload_buttons ) {
	if ( isset( $upload_buttons['browse_for_file'] ) ) {
		unset( $upload_buttons['browse_for_file'] );
	}
	return $upload_buttons;
}

add_filter( 'dlm_downloadable_file_version_buttons', __NAMESPACE__ . '\disable_browse_for_file' );
