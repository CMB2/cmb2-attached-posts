<?php
/*
 * Example setup for the custom Attached Posts field for CMB2.
 */

require_once WPMU_PLUGIN_DIR . '/cmb2/init.php';

if ( ! function_exists( 'cmb2_render_custom_attached_posts_callback' ) ) {
	require_once WPMU_PLUGIN_DIR . '/cmb2-attached-posts/cmb2-attached-posts-field.php';
}

/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */
add_filter( 'cmb2_meta_boxes', 'attached_cmb2_metaboxes_example' );
function attached_cmb2_metaboxes_example( array $meta_boxes ) {

	// Start with an underscore to hide fields from custom fields list
	$prefix = '_attached_cmb2_';

	$meta_boxes[] = array(
		'id'         => 'attached_posts',
		'title'      => __( 'Attached Posts', 'cmb2' ),
		'object_types' => array( 'page' ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => false, // Show field names on the left
		'fields'     => array(
			array(
				'name'			=> __( 'Posts', 'cmb2' ),
				'id'			=> $prefix . 'attached_posts',
				'type'			=> 'custom_attached_posts',
				'repeatable' 	=> false,
				'desc'			=> __( 'Drag posts from the left column to the right column to attach them to this page.<br />You may rearrange the order of the posts in the right column by dragging and dropping.', 'cmb2' )
			)
		),
	);

	/* End CMB for Pages */

	return $meta_boxes;
}
