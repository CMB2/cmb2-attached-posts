<?php
/*
 * Example setup for the custom Attached Posts field for CMB2.
 */

/**
 * Get the bootstrap! If using as a plugin, REMOVE THIS!
 */
require_once WPMU_PLUGIN_DIR . '/cmb2/init.php';

if ( ! function_exists( 'cmb2_attached_posts_fields_render' ) ) {
	require_once WPMU_PLUGIN_DIR . '/cmb2-attached-posts/cmb2-attached-posts-field.php';
}

/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */
function cmb2_attached_posts_field_metaboxes_example() {

	$example_meta = new_cmb2_box( array(
		'id'           => 'cmb2_attached_posts_field',
		'title'        => __( 'Attached Posts', 'cmb2' ),
		'object_types' => array( 'page' ), // Post type
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => false, // Show field names on the left
	) );

	$example_meta->add_field( array(
		'name'    => __( 'Attached Posts', 'cmb2' ),
		'desc'    => __( 'Drag posts from the left column to the right column to attach them to this page.<br />You may rearrange the order of the posts in the right column by dragging and dropping.', 'cmb2' ),
		'id'      => 'attached_cmb2_attached_posts',
		'type'    => 'custom_attached_posts',
		'options' => array(
			'show_thumbnails' => true, // Show thumbnails on the left
			'filter_boxes'    => true, // Show a text box for filtering the results
			'query_args'      => array( 'posts_per_page' => 10 ), // override the get_posts args
		)
	) );

}
add_action( 'cmb2_init', 'cmb2_attached_posts_field_metaboxes_example' );
