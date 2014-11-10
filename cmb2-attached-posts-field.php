<?php
/**
 * Enqueue admin scripts for our attached posts field
 */
function attached_cmb2_enqueue_attached_posts_scripts() {

	$version = '1.0.0';

	$dir = trailingslashit( dirname( __FILE__ ) );
	$url = str_replace(
		array( WP_CONTENT_DIR, WP_PLUGIN_DIR ),
		array( WP_CONTENT_URL, WP_PLUGIN_URL ),
		$dir
	);

	wp_enqueue_script( 'jquery-ui', $url . 'js/lib/jquery-ui-1.10.4.custom.min.js', array( 'jquery' ), $version, true );
	wp_enqueue_script( 'attached-cmb2-attached-posts', $url . 'js/attached-posts.js', array( 'jquery-ui' ), $version, true );
	wp_enqueue_style( 'attached-cmb2-attached-posts', $url . 'css/attached-posts-admin.css', array(), $version );

}
add_action( 'admin_enqueue_scripts', 'attached_cmb2_enqueue_attached_posts_scripts' );

/**
 * Add a CMB custom field to allow for the selection of multiple posts
 * attached to a single page
 */
add_action( 'cmb2_render_custom_attached_posts', 'cmb2_render_custom_attached_posts_callback', 10, 3);
function cmb2_render_custom_attached_posts_callback( $field, $field_args, $value ) {

	// Grab our attached posts meta
	$attached = get_post_meta( get_the_ID(), '_attached_cmb2_attached_posts', true );

	// Setup our args
	$args = array(
		'post_type'			=> 'post',
		'posts_per_page'	=> -1,
		'orderby'			=> 'name',
		'order'				=> 'ASC',
	);

	// Get our posts
	$posts = get_posts( $args );

	// If there are no posts found, just stop
	if ( ! $posts )
		return;

	// Set our count class
	$count = 0;

	// Wrap our lists
	echo '<div class="attached-posts-wrap">';

	// Open our retrieved, or found posts, list
	echo '<div class="retrieved-wrap column-wrap">';
	echo '<h4 class="attached-posts-section">' . __( 'Available Posts', 'cmb' ) . '</h4>';
	echo '<ul id="retrieved" class="connected">';

	// Loop through our posts as list items
	foreach ( $posts as $post ) {

		// Increase our count
		$count++;

		// Set our zebra stripes
		$zebra = $count % 2 == 0 ? 'even' : 'odd';

		// Set a class if our post is in our attached post meta
		$added = ! empty ( $attached ) && in_array( $post->ID, $attached ) ? ' added' : '';

		// Build our list item
		echo '<li data-id="', $post->ID ,'" class="' . $zebra . $added . '">', $post->post_title ,'<span class="dashicons dashicons-plus add-remove"></span></li>';

	}

	// Close our retrieved, or found, posts
	echo '</ul><!-- #retrieved -->';
	echo '</div><!-- .retrieved-wrap -->';

	// Open our attached posts list
	echo '<div class="attached-wrap column-wrap">';
	echo '<h4 class="attached-posts-section">' . __( 'Attached Posts', 'cmb' ) . '</h4>';
	echo '<ul id="attached" class="connected">';

	// If we have any posts saved already, display them
	echo custom_check_for_attached_posts( $field );

	// Close up shop
	echo '</ul><!-- #attached -->';
	echo '</div><!-- .attached-wrap -->';
	echo '</div><!-- .attached-posts-wrap -->';

	// Display our description if one exists
	echo '<p class="cmb_metabox_description">', $field->desc(), '</p>';

}

/**
 * Helper function to grab and filter our post meta
 */
function custom_check_for_attached_posts( $field ) {

	// Start with nothing
	$output = '';

	// Check to see if we have any meta values saved yet
	$attached = get_post_meta( get_the_ID(), '_attached_cmb2_attached_posts', true );

	// If we do, then we need to display them as items in our attached list
	if ( ! $attached ) {

		$output .= '<input type="hidden" name="' . $field->id() . '">';

	} else {

		// Set our count to zero
		$count = 0;

		// Remove any empty values
		$attached = array_filter( $attached );

		// Loop through and build our existing display items
		foreach ( $attached as $post ) {

			// Increase our count
			$count++;

			// Set our zebra stripes
			$zebra = $count % 2 == 0 ? 'even' : 'odd';

			// Build our list item
			$output .= '<li data-id="' . $post . '" class="' . $zebra . '">' . get_the_title( $post ) . '<input type="hidden" value="' . $post . '" name="' . $field->id() . '[]"><span class="dashicons dashicons-minus add-remove"></span></li>';
		}

	}

	return $output;

}
