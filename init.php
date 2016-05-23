<?php
/**
 * Class WDS_CMB2_Attached_Posts_Field
 */
class WDS_CMB2_Attached_Posts_Field {

	/**
	 * Current version number
	 */
	const VERSION = CMB2_ATTACHED_POSTS_FIELD_VERSION;

	/**
	 * @var WDS_CMB2_Attached_Posts_Field
	 */
	protected static $single_instance = null;

	/**
	 * Creates or returns an instance of this class.
	 * @since  0.1.0
	 * @return WDS_CMB2_Attached_Posts_Field A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Initialize the plugin by hooking into CMB2
	 */
	protected function __construct() {
		add_action( 'cmb2_render_custom_attached_posts', array( $this, 'render' ), 10, 5 );
		add_action( 'cmb2_sanitize_custom_attached_posts', array( $this, 'sanitize' ), 10, 2 );
	}

	/**
	 * Add a CMB custom field to allow for the selection of multiple posts
	 * attached to a single page
	 */
	public function render( $field, $escaped_value, $object_id, $object_type, $field_type ) {

		$this->setup_admin_scripts();

		// Setup our args
		$args = (array) $field->options( 'query_args' ); 
		
		if ( $field->options( 'exclude_current_post' ) ) {
			global $post;
			
			$args = wp_parse_args( $args, array(
				'post__not_in' => array( $post->ID ),
			) );
		}
		
		$args = wp_parse_args( $args, array(
			'post_type'			=> 'post',
			'posts_per_page'	=> 100,
			'orderby'			=> 'name',
			'order'				=> 'ASC',
		) );

		// loop through post types to get labels for all
		$post_type_labels = array();
		foreach ( (array) $args['post_type'] as $post_type ) {
			// Get post type object for attached post type
			$attached_post_type = get_post_type_object( $post_type );

			// continue if we don't have a label for the post type
			if ( ! $attached_post_type || ! isset( $attached_post_type->labels->name ) ) {
				continue;
			}

			$post_type_labels[] = $attached_post_type->labels->name;
		}

		$post_type_labels = implode( '/', $post_type_labels );

		// Check 'filter' setting
		$filter_boxes = $field->options( 'filter_boxes' )
			? '<div class="search-wrap"><input type="text" placeholder="' . sprintf( __( 'Filter %s', 'cmb' ), $post_type_labels ) . '" class="regular-text search" name="%s" /></div>'
			: '';

		// Get our posts
		$posts = get_posts( $args );

		// If there are no posts found, just stop
		if ( ! $posts ) {
			return;
		}

		// Check to see if we have any meta values saved yet
		$attached = (array) $escaped_value;

		// Set our count class
		$count = 0;

		// Wrap our lists
		echo '<div class="attached-posts-wrap widefat" data-fieldname="'. $field_type->_name() .'">';

		// Open our retrieved, or found posts, list
		echo '<div class="retrieved-wrap column-wrap">';
		echo '<h4 class="attached-posts-section">' . sprintf( __( 'Available %s', 'cmb' ), $post_type_labels ) . '</h4>';

		// Set .has_thumbnail
		$has_thumbnail = $field->options( 'show_thumbnails' ) ? ' has-thumbnails' : '';
		$hide_selected = $field->options( 'hide_selected' ) ? ' hide-selected' : '';

		if ( $filter_boxes ) {
			printf( $filter_boxes, 'available-search' );
		}

		echo '<ul class="retrieved connected' . $has_thumbnail . $hide_selected . '">';

		// Loop through our posts as list items
		foreach ( $posts as $post ) {

			// Increase our count
			$count++;

			// Set our zebra stripes
			$zebra = $count % 2 == 0 ? 'even' : 'odd';

			// Set a class if our post is in our attached post meta
			$added = ! empty ( $attached ) && in_array( $post->ID, $attached ) ? ' added' : '';

			// Set thumbnail if the options is true
			$thumbnail = $has_thumbnail ? get_the_post_thumbnail( $post->ID, array( 50, 50 ) ) : '';

			// Build our list item
			echo '<li data-id="', $post->ID ,'" class="' . $zebra . $added . '">', $thumbnail ,'<a title="'. __( 'Edit' ) .'" href="', get_edit_post_link( $post ) ,'">', get_the_title( $post ) ,'</a><span class="dashicons dashicons-plus add-remove"></span></li>';

		}

		// Close our retrieved, or found, posts
		echo '</ul><!-- .retrieved -->';
		echo '</div><!-- .retrieved-wrap -->';

		// Open our attached posts list
		echo '<div class="attached-wrap column-wrap">';
		echo '<h4 class="attached-posts-section">' . sprintf( __( 'Attached %s', 'cmb' ), $post_type_labels ) . '</h4>';

		if ( $filter_boxes ) {
			printf( $filter_boxes, 'attached-search' );
		}

		echo '<ul class="attached connected', $has_thumbnail ,'">';

		// If we have any posts saved already, display them
		$post_ids = $this->display_attached( $field, $attached );

		$value = ! empty( $post_ids ) ? implode( ',', $post_ids ) : '';

		// Close up shop
		echo '</ul><!-- #attached -->';
		echo '</div><!-- .attached-wrap -->';

		echo $field_type->input( array(
			'type'  => 'hidden',
			'class' => 'attached-posts-ids',
			'value' => $value,
			'desc'  => '',
		) );

		echo '</div><!-- .attached-posts-wrap -->';

		// Display our description if one exists
		$field_type->_desc( true, true );

	}

	/**
	 * Helper function to grab and filter our post meta
	 */
	protected function display_attached( $field, $attached ) {

		// Start with nothing
		$output = '';

		// If we do, then we need to display them as items in our attached list
		if ( ! $attached ) {
			return;
		}

		// Set our count to zero
		$count = 0;

		$show_thumbnails = $field->options( 'show_thumbnails' );
		// Remove any empty values
		$attached = array_filter( $attached );
		$post_ids = array();

		// Loop through and build our existing display items
		foreach ( $attached as $post_id ) {
			if ( ! get_post( $post_id ) ) {
				continue;
			}

			// Increase our count
			$count++;

			// Set our zebra stripes
			$zebra = $count % 2 == 0 ? 'even' : 'odd';

			// Set thumbnail if the options is true
			$thumbnail = $show_thumbnails ? get_the_post_thumbnail( $post_id, array( 50, 50 ) ) : '';

			// Build our list item
			echo '<li data-id="' . $post_id . '" class="' . $zebra . '">', $thumbnail ,'<a title="'. __( 'Edit' ) .'" href="', get_edit_post_link( $post_id ) ,'">'.  get_the_title( $post_id ) .'</a><span class="dashicons dashicons-minus add-remove"></span></li>';

			$post_ids[] = $post_id;

		}

		return $post_ids;
	}

	public function sanitize( $sanitized_val, $val ) {
		if ( ! empty( $val ) ) {
			return explode( ',', $val );
		}
		return $sanitized_val;
	}

	/**
	 * Enqueue admin scripts for our attached posts field
	 */
	protected function setup_admin_scripts() {
		$dir = CMB2_ATTACHED_POSTS_FIELD_DIR;

		if ( 'WIN' === strtoupper( substr( PHP_OS, 0, 3 ) ) ) {
			// Windows
			$content_dir = str_replace( '/', DIRECTORY_SEPARATOR, WP_CONTENT_DIR );
			$content_url = str_replace( $content_dir, WP_CONTENT_URL, $dir );
			$url = str_replace( DIRECTORY_SEPARATOR, '/', $content_url );

		} else {
			$url = str_replace(
				array( WP_CONTENT_DIR, WP_PLUGIN_DIR ),
				array( WP_CONTENT_URL, WP_PLUGIN_URL ),
				$dir
			);
		}

		$url = set_url_scheme( $url );
		$url = apply_filters( 'cmb2_attached_posts_field_assets_url', $url );

		$requirements = array(
			'jquery-ui-core',
			'jquery-ui-widget',
			'jquery-ui-mouse',
			'jquery-ui-draggable',
			'jquery-ui-droppable',
			'jquery-ui-sortable',
		);

		wp_enqueue_script( 'cmb2-attached-posts-field', $url . 'js/attached-posts.js', $requirements, self::VERSION, true );
		wp_enqueue_style( 'cmb2-attached-posts-field', $url . 'css/attached-posts-admin.css', array(), self::VERSION );
	}
}
WDS_CMB2_Attached_Posts_Field::get_instance();
