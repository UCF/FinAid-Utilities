<?php
/**
 * Provides a series of filters
 * related to display sections
 */
namespace FinAid\Utils\Plugins;

function add_section_fields() {
	// Bail out if the function is missing for some reason.
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

	$fields = array();

	/**
	 * Adds the section layout field
	 * @author Jim Barnes
	 * @since 1.0.0
	 */
	$fields[] = array(
		'key'           => 'finaid_section_layout',
		'label'         => 'Layout',
		'name'          => 'finaid_section_layout',
		'type'          => 'select',
		'instructions'  => 'Choose the layout to use for the section.',
		'required'      => true,
		'default_value' => array(
			0 => 'default'
		),
		'choices'      => array(
			'default'   => 'Default',
			'icon-list' => 'Icon List'
		)
	);

	/**
	 * Define the icon list group
	 * @author Jim Barnes
	 * @since 1.0.0
	 */
	$icon_list_group = array(
		'key'               => 'finaid_icon_list_fields',
		'label'             => 'Icon List Fields',
		'name'              => 'finaid_icon_list_fields',
		'type'              => 'group',
		'instructions'      => '',
		'required'          => 0,
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'finaid_section_layout',
					'operator' => '==',
					'value'    => 'icon-list'
				)
			)
		),
		'layout'            => 'block',
		'sub_fields'        => array()
	);

	/**
	 * Define the icon list items repeater
	 * @author Jim Barnes
	 * @since 1.0.0
	 */
	$icon_list_repeater = array(
		'key'          => 'finaid_icon_list_items',
		'label'        => 'Icon List Items',
		'name'         => 'finaid_icon_list_items',
		'type'         => 'repeater',
		'instructions' => 'The items of the icon list to display',
		'required'     => 0,
		'collapsed'    => 'finaid_item_label',
		'layout'       => 'block',
		'button_label' => 'Add Item',
		'sub_fields'   => array()
	);

	/**
	 * Define the icon field.
	 * Part of the icon list repeater field
	 * @author Jim Barnes
	 * @since 1.0.0
	 */
	$sub_fields[] = array(
		'key'          => 'finaid_item_icon',
		'label'        => 'Icon',
		'name'         => 'finaid_item_icon',
		'type'         => 'font-awesome',
		'instructions' => 'Choose the icon to display',
		'required'     => 1,
		'icon_sets'    => array(
			0 => 'far'
		),
		'save_format'  => 'class',
		'show_preview' => 1,
		'enqueue_fa'   => 1
	);

	/**
	 * Define the item label field
	 * Part of the item icon repeater
	 * @author Jim Barnes
	 */
	$sub_fields[] = array(
		'key'          => 'finaid_item_label',
		'label'        => 'Label',
		'name'         => 'finaid_item_label',
		'type'         => 'text',
		'instructions' => 'Enter the label to display next o the icon',
		'required'     => 1
	);

	/**
	 * Define the item content field
	 * Part of the item icon repeater
	 * @author Jim Barnes
	 * @since 1.0.0
	 */
	$sub_fields[] = array(
		'key'          => 'finaid_item_content',
		'label'        => 'Content',
		'name'         => 'finaid_item_content',
		'type'         => 'wysiwyg',
		'instructions' => 'Enter the content to be displayed under the icon and label',
		'required'     => 1,
		'tabs'         => 'all',
		'tollbar'      => 'full',
		'media_upload' => 1,
		'delay'        => 1
	);

	// Assign the subfields to the repeater
	$icon_list_repeater['sub_fields'] = $sub_fields;

	// Assign the repeater to the field group
	$icon_list_group['sub_fields'] = array( $icon_list_repeater );

	// Add the icon list group to the fields
	$fields[] = $icon_list_group;

	$field_group = array(
		'key'      => 'finaid_section_custom_fields',
		'title'    => 'Section Layout Fields',
		'fields'   => $fields,
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'ucf_section'
				)
			)
		),
		'position' => 'normal',
		'style'    => 'default',
		'active'   => true
	);

	acf_add_local_field_group( $field_group );
}

add_action( 'acf/init', __NAMESPACE__ . '\add_section_fields', 10, 0 );

/**
 * Below are filters for modifying output
 * of the section shortcode
 * based on the selected layout
 */

/**
 * Function for displaying the beginning
 * of a icon-list section
 * @author Jim Barnes
 * @since 1.0.0
 * @param string $retval The unfiltered return value
 * @param WP_Post $section The section object
 * @param string $class The class to use when displaying the section
 * @param string $title The title the display
 * @param string $section_id The section id to use
 * @return string The output
 */
function icon_list_before( $retval, $section, $class, $title, $section_id ) {
	$layout = get_field( 'finaid_section_layout', $section->ID );
	// If this isn't an icon-list, return.
	if ( $layout !== 'icon-list' ) return $retval;

	$has_content = ! empty( $section->post_content );

	ob_start();
?>
	<dl class="icon-list">
<?php
	$output = ob_get_clean();

	if ( $has_content ) {
		$retval += $output;
	} else {
		$retval = $output;
	}

	return $retval;
}

add_filter( 'ucf_section_display_before', __NAMESPACE__ . '\icon_list_before', 11, 5 );

/**
 * Function for displaying the beginning
 * of a icon-list section
 * @author Jim Barnes
 * @since 1.0.0
 * @param string $retval The unfiltered return value
 * @param WP_Post $section The section object
 * @return string The output
 */
function icon_list_content( $retval, $section ) {
	$layout = get_field( 'finaid_section_layout', $section->ID );

	// If this isn't an icon-list, return.
	if ( $layout !== 'icon-list' ) return $retval;

	$list = get_field( 'finaid_icon_list_fields', $section->ID );

	ob_start();

	if ( isset( $list['finaid_icon_list_items'] ) && count( $list['finaid_icon_list_items'] ) > 0 ) :
		foreach( $list['finaid_icon_list_items'] as $item ) :
?>
	<dt class="align-self-center mb-2">
		<span class="fa <?php echo $item['finaid_item_icon']; ?> fa-2x text-primary mr-2"></span>
		<?php echo $item['finaid_item_label']; ?>
	</dt>
	<dd>
		<?php echo apply_filters( 'the_content', $item['finaid_item_content'] ); ?>
	</dd>
<?php
		endforeach;
	endif;

	return ob_get_clean();
}

add_filter( 'ucf_section_display', __NAMESPACE__ . '\icon_list_content', 10, 2 );

/**
 * Function for displaying the beginning
 * of a icon-list section
 * @author Jim Barnes
 * @since 1.0.0
 * @param string $retval The unfiltered return value
 * @param WP_Post $section The section object
 * @return string The output
 */
function icon_list_after( $retval, $section ) {
	$layout = get_field( 'finaid_section_layout', $section->ID );

	// If this isn't an icon-list, return.
	if ( $layout !== 'icon-list' ) return $retval;

	$has_content = ! empty( $section->post_content );

	ob_start();
?>
	</dl>
<?php
	$output = ob_get_clean();

	if ( $has_content ) {
		$retval += $output;
	} else {
		$retval = $output;
	}

	return $retval;
}

add_filter( 'ucf_section_display_after', __NAMESPACE__ . '\icon_list_after', 10, 2 );
