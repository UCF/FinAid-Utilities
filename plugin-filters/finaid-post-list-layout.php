<?php
/**
 * Provides post list layouts for grouped
 */
namespace FinAid\Utils\Plugins;

/**
 * Function for displaying the beginning of a grouped post list
 * @author Jim Barnes
 * @since 1.0.0
 * @param string $content The content passed to the filter
 * @param array $posts The posts
 * @param array $atts The shortcode arguments
 * @return string
 */
function ucf_post_list_display_grouped_before( $content, $posts, $atts ) {
	ob_start();
?>
	<div class="ucf-post-list ucf-post-list-grouped" id="post-list-<?php echo $atts['list_id']; ?>">
<?php
	return ob_get_clean();
}

add_filter( 'ucf_post_list_display_grouped_before', __NAMESPACE__ . "\ucf_post_list_display_grouped_before", 10, 3 );

/**
 * Function for displaying the title of the grouped post list
 * @author Jim Barnes
 * @since 1.0.0
 * @param string $content The content passed to the filter
 * @param array $posts The posts
 * @param array $atts The shortcode arguments
 * @return string
 */
function ucf_post_list_display_grouped_title( $content, $posts, $atts ) {
	$formatted_title = '';

	if ( $list_title = $atts['list_title'] ) {
		$formatted_title = "<h2 class=\"ucf-post-list-title\">$list_title</h2>";
	}

	return $formatted_title;
}

add_filter( 'ucf_post_list_display_grouped_title', __NAMESPACE__ . '\ucf_post_list_display_grouped_title', 10, 3 );

/**
 * Function for displaying the items of the grouped post list
 * @author Jim Barnes
 * @since 1.0.0
 * @param string $content The content passed to the filter
 * @param array $posts The posts
 * @param array $atts The shortcode arguments
 * @return string
 */
function ucf_post_list_display_grouped( $content, $posts, $atts ) {
	if ( ! is_array( $posts ) && $posts !== false ) {
		$posts = array( $posts );
	}

	$group_by_subterm = false;

	if ( isset( $atts['group_by_subterm'] ) ) {
		$group_by_subterm = true;
	}

	$post_count = count( $posts );

	// If we're grouping by subterm, go ahead and do it
	list($grouped, $posts) = $group_by_subterm ? ucf_post_list_grouped_groupby_subterm( $posts, $atts ) : array(false, $posts);

	ob_start();

	if ( $posts && $post_count > 0 ) :
		if ( $grouped ) :
			foreach( $posts as $term ) :
?>
			<h2 class="heading-underline"><?php echo $term['term_name']; ?></h2>
			<ul class="mb-4 list-unstyled">
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
	else : // Post count is 0
?>
		<p>No results found.</p>
<?php
	endif;
	return ob_get_clean();
}

add_filter( 'ucf_post_list_display_grouped', __NAMESPACE__ . '\ucf_post_list_display_grouped', 10, 3 );

/**
 * Function for displaying the end of the grouped post list
 * @author Jim Barnes
 * @since 1.0.0
 * @param string $content The content passed to the filter
 * @param array $posts The posts
 * @param array $atts The shortcode arguments
 * @return string
 */
function ucf_post_list_display_grouped_after( $content, $posts, $atts ) {
	ob_start();
?>
	</div>
<?php
	return ob_get_clean();
}

add_filter( 'ucf_post_list_display_grouped_after', __NAMESPACE__ . '\ucf_post_list_display_grouped_after', 10, 3 );

/**
 * Adds the grouped layout to the layout array
 * @author Jim Barnes
 * @since 1.0.0
 * @param array $layouts The layout array
 * @return array
 */
function ucf_post_list_add_grouped_layout( $layouts ) {
	if ( ! isset( $layouts['grouped'] ) ) {
		$layouts[] = 'grouped';
	}

	return $layouts;
}

add_filter( 'ucf_post_list_get_layouts', __NAMESPACE__ . '\ucf_post_list_add_grouped_layout', 10, 1 );

/**
 * Filter for adding custom shortcode atts
 * for the grouped layout
 * @author Jim Barnes
 * @since 1.0.0
 * @param array $sc_atts The default shortcode atts
 * @param string $layout The layout being used.
 * @return array
 */
function ucf_post_list_add_shortcode_atts( $sc_atts, $layout ) {
	if ( $layout === 'grouped' ) {
		$sc_atts['group_by_subterm'] = false;
		$sc_atts['group_by_subterm_tax'] = 'grouped';
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
function ucf_post_list_grouped_groupby_subterm( $posts, $atts ) {
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
