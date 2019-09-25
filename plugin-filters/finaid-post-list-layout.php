<?php
/**
 * Provides post list layouts for media
 */
namespace FinAid\Utils\Plugins;

/**
 * Function for displaying the beginning of a media post list
 * @author Jim Barnes
 * @since 1.0.0
 * @param string $content The content passed to the filter
 * @param array $posts The posts
 * @param array $atts The shortcode arguments
 * @return string
 */
function ucf_post_list_display_media_before( $content, $posts, $atts ) {
	ob_start();
?>
	<div class="ucf-post-list ucf-post-list-media" id="post-list-<?php echo $atts['list_id']; ?>">
<?php
	return ob_get_clean();
}

// add_filter( 'ucf_post_list_display_media_before', array( __NAMESPACE__ . "\ucf_post_list_display_media_before" ), 10, 3 );

/**
 * Function for displaying the title of the media post list
 * @author Jim Barnes
 * @since 1.0.0
 * @param string $content The content passed to the filter
 * @param array $posts The posts
 * @param array $atts The shortcode arguments
 * @return string
 */
function ucf_post_list_display_media_title( $content, $posts, $atts ) {
	$formatted_title = '';

	if ( $list_title = $atts['list_title'] ) {
		$formatted_title = "<h2 class=\"ucf-post-list-title\">$list_title</h2>";
	}

	return $formatted_title;
}

add_filter( 'ucf_post_list_display_media_title', __NAMESPACE__ . '\ucf_post_list_display_media_title', 10, 3 );

/**
 * Function for displaying the items of the media post list
 * @author Jim Barnes
 * @since 1.0.0
 * @param string $content The content passed to the filter
 * @param array $posts The posts
 * @param array $atts The shortcode arguments
 * @return string
 */
function ucf_post_list_display_media( $content, $posts, $atts ) {
	$group_by_subterm = false;

	if ( isset( $atts['group_by_subterm'] ) ) {
		$group_by_subterm = true;
	}

	// If we're grouping by subterm, go ahead and do it
	list($grouped, $posts) = $group_by_subterm ? ucf_post_list_media_groupby_subterm( $posts, $atts ) : array(false, $posts);

	ob_start();

	if ( $grouped ) :
		foreach( $posts as $term ) :
?>
	<h2 class="heading-underline"><?php echo $term['term_name']; ?></h2>
	<ul class="mb-2 list-unstyled">
		<?php foreach( $term['posts'] as $post ) : ?>
			<li><a href="<?php echo get_permalink( $post->ID ); ?>"><?php echo $post->post_title; ?></a></li>
		<?php endforeach; // End foreach post ?>
	</ul>
		<?php endforeach; // End foreach term ?>
<?php
	else:
?>
	<ul class="list-unstyled">
	<?php foreach ( $posts as $post ) : ?>
		<li><a href="<?php echo get_permalink( $post->ID ); ?>"><?php echo $post->post_title; ?></a></li>
	<?php endforeach; ?>
	</ul>
<?php
	endif;
	return ob_get_clean();
}

add_filter( 'ucf_post_list_display_media', __NAMESPACE__ . '\ucf_post_list_display_media', 10, 3 );

/**
 * Function for displaying the end of the media post list
 * @author Jim Barnes
 * @since 1.0.0
 * @param string $content The content passed to the filter
 * @param array $posts The posts
 * @param array $atts The shortcode arguments
 * @return string
 */
function ucf_post_list_display_media_after( $content, $posts, $atts ) {
	ob_start();
?>
	</div>
<?php
	return ob_get_clean();
}

add_filter( 'ucf_post_list_display_media_after', __NAMESPACE__ . '\ucf_post_list_display_media_after', 10, 3 );

/**
 * Adds the media layout to the layout array
 * @author Jim Barnes
 * @since 1.0.0
 * @param array $layouts The layout array
 * @return array
 */
function ucf_post_list_add_media_layout( $layouts ) {
	if ( ! isset( $layouts['media'] ) ) {
		$layouts[] = 'media';
	}

	return $layouts;
}

add_filter( 'ucf_post_list_get_layouts', __NAMESPACE__ . '\ucf_post_list_add_media_layout', 10, 1 );

/**
 * Filter for adding custom shortcode atts
 * for the media layout
 * @author Jim Barnes
 * @since 1.0.0
 * @param array $sc_atts The default shortcode atts
 * @param string $layout The layout being used.
 * @return array
 */
function ucf_post_list_add_shortcode_atts( $sc_atts, $layout ) {
	if ( $layout === 'media' ) {
		$sc_atts['group_by_subterm'] = false;
		$sc_atts['group_by_subterm_tax'] = 'media';
		$sc_atts['group_by_subterm_term'] = '';
	}

	return $sc_atts;
}

add_filter( 'ucf_post_list_get_sc_atts', __NAMESPACE__ . '\ucf_post_list_add_shortcode_atts', 10, 2 );

/**
 * Regroups the post by subterms
 * @author Jim Barnes
 * @since 1.0.0
 * @param array $posts The post array
 * @param array $atts The argument array
 * @return array
 */
function ucf_post_list_media_groupby_subterm( $posts, $atts ) {
	$taxonomy = isset( $atts['group_by_subterm_tax'] ) ? $atts['group_by_subterm_tax'] : false;
	$term     = isset( $atts['group_by_subterm_term'] ) ? $atts['group_by_subterm_term'] : false;

	$retarr = array();
	$retval = array(false, $posts);

	// If the taxonomy and term aren't set, return unmodified;
	if ( ! $taxonomy || ! $term ) {
		return $retval;
	}

	// Get the parent term
	$parent = get_term_by( 'slug', $term, $taxonomy );

	// Can't find the parent, return.
	if ( ! $parent ) {
		return $retval;
	}

	$term_args = array(
		'taxonomy'    => $taxonomy,
		'parent'      => $parent->term_id
	);

	$terms = get_terms( $term_args );

	// If there aren't any terms, return.
	if ( count( $terms ) === 1 ) {
		return $retval;
	}

	foreach( $terms as $term ) {
		$retarr[$term->term_id] = array(
			'term_name' => $term->name,
			'term_slug' => $term->slug,
			'posts'     => array()
		);

		foreach( $posts as $i => $post ) {
			$post_terms = wp_get_post_terms(
							$post->ID,
							$taxonomy,
							array(
								'exclude' => array( $parent->term_id )
							) );

			if ( in_array( $term, $post_terms ) ) {
				$retarr[$term->term_id]['posts'][] = $post;
				unset( $posts[$i] );
			}
		}
	}

	if ( $retarr && count( $retarr ) > 0 ) {
		$retval = array( true, $retarr );
	}

	return $retval;
}
