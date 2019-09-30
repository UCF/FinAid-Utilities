<?php
/**
 * Functions that customize the admin interface for Downloads
 * (via the Download Monitor plugin)
 */
namespace FinAid\Utils\Admin\Downloads;


/**
 * Modifies the download CPT's registered supported features.
 *
 * @since 1.0.0
 * @author Jo Dickson
 * @param array $supports Existing array of supported features for the post type
 * @return array Modified array of supported features
 */
function downloads_cpt_supports( $supports ) {
	foreach ( $supports as $key => $val ) {
		// Remove the post editor from single downloads
		if ( $val === 'editor' ) {
			unset( $supports[$key] );
		}
	}
	return $supports;
}

add_filter( 'dlm_cpt_dlm_download_supports', __NAMESPACE__ . '\downloads_cpt_supports', 11, 1 );


/**
 * Modifies columns in the Downloads list admin view.
 *
 * @since 1.0.0
 * @author Jo Dickson
 * @param array $columns Existing column definitions
 * @return array Modified columns array
 */
function downloads_columns( $columns ) {
	// Remove unused columns from the Downloads list admin view:
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

add_filter( 'manage_edit-dlm_download_columns', __NAMESPACE__ . '\downloads_columns', 11, 1 );


/**
 * Modifies metaboxes registered for single downloads
 *
 * @since 1.0.0
 * @author Jo Dickson
 * @return void
 */
function downloads_metaboxes() {
	// Remove the "Download Options" metabox on single downloads
	remove_meta_box( 'download-monitor-options', 'dlm_download', 'side' );
}

add_action( 'do_meta_boxes', __NAMESPACE__ . '\downloads_metaboxes' );


/**
 * Modifies available upload buttons for single file versions.
 *
 * @since 1.0.0
 * @author Jo Dickson
 * @param array $upload_buttons Available upload button names
 * @return array Modified upload button names
 */
function downloads_file_version_buttons( $upload_buttons ) {
	// Remove "Browse for file" button under Download version add fields
	if ( isset( $upload_buttons['browse_for_file'] ) ) {
		unset( $upload_buttons['browse_for_file'] );
	}
	return $upload_buttons;
}

add_filter( 'dlm_downloadable_file_version_buttons', __NAMESPACE__ . '\downloads_file_version_buttons' );
