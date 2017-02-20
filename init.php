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
	 * CMB2_Field object
	 *
	 * @var CMB2_Field
	 */
	protected $field;

	/**
	 * Whether to output the type label.
	 * Determined when multiple post types exist in the query_args field arg.
	 *
	 * @var bool
	 */
	protected $do_type_label = false;

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
		add_action( 'cmb2_attached_posts_field_add_find_posts_div', array( $this, 'add_find_posts_div' ) );
		add_action( 'cmb2_after_init', array( $this, 'ajax_find_posts' ) );
	}

	/**
	 * Add a CMB custom field to allow for the selection of multiple posts
	 * attached to a single page
	 */
	public function render( $field, $escaped_value, $object_id, $object_type, $field_type ) {
		self::setup_scripts();
		$this->field = $field;
		$this->do_type_label = false;

		if ( ! is_admin() ) {
			// Will need custom styling!
			// @todo add styles for front-end
			require_once( ABSPATH . 'wp-admin/includes/template.php' );
			do_action( 'cmb2_attached_posts_field_add_find_posts_div' );
		} else {
			// markup needed for modal
			add_action( 'admin_footer', 'find_posts_div' );
		}

		$query_args = (array) $this->field->options( 'query_args' );
		$query_users = $this->field->options( 'query_users' );

		if ( ! $query_users ) {

			// Setup our args
			$args = wp_parse_args( $query_args, array(
				'post_type'      => 'post',
				'posts_per_page' => 100,
			) );

			if ( isset( $_POST['post'] ) ) {
				$args['post__not_in'] = array( absint( $_POST['post'] ) );
			}

			// loop through post types to get labels for all
			$post_type_labels = array();
			foreach ( (array) $args['post_type'] as $post_type ) {
				// Get post type object for attached post type
				$post_type_obj = get_post_type_object( $post_type );

				// continue if we don't have a label for the post type
				if ( ! $post_type_obj || ! isset( $post_type_obj->labels->name ) ) {
					continue;
				}

				if ( is_post_type_hierarchical( $post_type_obj ) ) {
					$args['orderby'] = isset( $args['orderby'] ) ? $args['orderby'] : 'name';
					$args['order']   = isset( $args['order'] ) ? $args['order'] : 'ASC';
				}

				$post_type_labels[] = $post_type_obj->labels->name;
			}

			$this->do_type_label = count( $post_type_labels ) > 1;

			$post_type_labels = implode( '/', $post_type_labels );

		} else {
			// Setup our args
			$args = wp_parse_args( $query_args, array(
				'number' => 100,
			) );
			$post_type_labels = $field_type->_text( 'users_text', esc_html__( 'Users' ) );
		}

		$filter_boxes = '';
		// Check 'filter' setting
		if ( $this->field->options( 'filter_boxes' ) ) {
			$filter_boxes = '<div class="search-wrap"><input type="text" placeholder="' . sprintf( __( 'Filter %s', 'cmb' ), $post_type_labels ) . '" class="regular-text search" name="%s" /></div>';
		}

		// Check to see if we have any meta values saved yet
		$attached = (array) $escaped_value;

		$objects = $this->get_all_objects( $args, $attached );

		// If there are no posts found, just stop
		if ( empty( $objects ) ) {
			return;
		}

		// Wrap our lists
		echo '<div class="attached-posts-wrap widefat" data-fieldname="'. $field_type->_name() .'">';

		// Open our retrieved, or found posts, list
		echo '<div class="retrieved-wrap column-wrap">';
		echo '<h4 class="attached-posts-section">' . sprintf( __( 'Available %s', 'cmb' ), $post_type_labels ) . '</h4>';

		// Set .has_thumbnail
		$has_thumbnail = $this->field->options( 'show_thumbnails' ) ? ' has-thumbnails' : '';
		$hide_selected = $this->field->options( 'hide_selected' ) ? ' hide-selected' : '';

		if ( $filter_boxes ) {
			printf( $filter_boxes, 'available-search' );
		}

		echo '<ul class="retrieved connected' . $has_thumbnail . $hide_selected . '">';

		// Loop through our posts as list items
		$this->display_retrieved( $objects, $attached );

		// Close our retrieved, or found, posts
		echo '</ul><!-- .retrieved -->';

		// @todo make User search work.
		if ( ! $query_users ) {
			$findtxt = $field_type->_text( 'find_text', __( 'Search' ) );

			$js_data = json_encode( array(
				'queryUsers' => $query_users,
				'types'      => $query_users ? 'user' : (array) $args['post_type'],
				'cmbId'      => $this->field->cmb_id,
				'errortxt'   => esc_attr( $field_type->_text( 'error_text', __( 'An error has occurred. Please reload the page and try again.' ) ) ),
				'findtxt'    => esc_attr( $field_type->_text( 'find_text', __( 'Find Posts or Pages' ) ) ),
				'groupId'    => $this->field->group ? $this->field->group->id() : false,
				'fieldId'    => $this->field->_id(),
				'exclude'    => isset( $args['post__not_in'] ) ? $args['post__not_in'] : array(),
			) );

			echo '<p><button type="button" class="button cmb2-attached-posts-search-button" data-search=\''. $js_data .'\'>'. $findtxt .' <span title="'. esc_attr( $findtxt ) .'" class="dashicons dashicons-search"></span></button></p>';
		}

		echo '</div><!-- .retrieved-wrap -->';

		// Open our attached posts list
		echo '<div class="attached-wrap column-wrap">';
		echo '<h4 class="attached-posts-section">' . sprintf( __( 'Attached %s', 'cmb' ), $post_type_labels ) . '</h4>';

		if ( $filter_boxes ) {
			printf( $filter_boxes, 'attached-search' );
		}

		echo '<ul class="attached connected', $has_thumbnail ,'">';

		// If we have any ids saved already, display them
		$ids = $this->display_attached( $attached );

		// Close up shop
		echo '</ul><!-- #attached -->';
		echo '</div><!-- .attached-wrap -->';

		echo $field_type->input( array(
			'type'  => 'hidden',
			'class' => 'attached-posts-ids',
			'value' => ! empty( $ids ) ? implode( ',', $ids ) : '',
			'desc'  => '',
		) );

		echo '</div><!-- .attached-posts-wrap -->';

		// Display our description if one exists
		$field_type->_desc( true, true );
	}

	/**
	 * Outputs the <li>s in the retrieved (left) column.
	 *
	 * @since  1.2.5
	 *
	 * @param  mixed  $objects  Posts or users.
	 * @param  array  $attached Array of attached posts/users.
	 *
	 * @return void
	 */
	protected function display_retrieved( $objects, $attached ) {
		$count = 0;

		// Loop through our posts as list items
		foreach ( $objects as $object ) {

			// Set our zebra stripes
			$class = ++$count % 2 == 0 ? 'even' : 'odd';

			// Set a class if our post is in our attached meta
			$class .= ! empty ( $attached ) && in_array( $this->get_id( $object ), $attached ) ? ' added' : '';

			$this->list_item( $object, $class );
		}
	}

	/**
	 * Outputs the <li>s in the attached (right) column.
	 *
	 * @since  1.2.5
	 *
	 * @param  array  $attached Array of attached posts/users.
	 *
	 * @return void
	 */
	protected function display_attached( $attached ) {
		$ids = array();

		// Remove any empty values
		$attached = array_filter( $attached );

		if ( empty( $attached ) ) {
			return $ids;
		}

		$count = 0;

		// Loop through and build our existing display items
		foreach ( $attached as $id ) {
			$object = $this->get_object( $id );
			$id     = $this->get_id( $object );

			if ( empty( $object ) ) {
				continue;
			}

			// Set our zebra stripes
			$class = ++$count % 2 == 0 ? 'even' : 'odd';

			$this->list_item( $object, $class, 'dashicons-minus' );
			$ids[ $id ] = $id;
		}

		return $ids;
	}

	/**
	 * Outputs a column list item.
	 *
	 * @since  1.2.5
	 *
	 * @param  mixed  $object     Post or User.
	 * @param  string  $li_class   The list item (zebra) class.
	 * @param  string  $icon_class The icon class. Either 'dashicons-plus' or 'dashicons-minus'.
	 *
	 * @return void
	 */
	public function list_item( $object, $li_class, $icon_class = 'dashicons-plus' ) {
		// Build our list item
		printf(
			'<li data-id="%1$d" class="%2$s" target="_blank">%3$s<a title="' . __( 'Edit' ) . '" href="%4$s">%5$s</a>%6$s<span class="dashicons %7$s add-remove"></span></li>',
			$this->get_id( $object ),
			$li_class,
			$this->get_thumb( $object ),
			$this->get_edit_link( $object ),
			$this->get_title( $object ),
			$this->get_object_label( $object ),
			$icon_class
		);
	}

	/**
	 * Get thumbnail for the object.
	 *
	 * @since  1.2.4
	 *
	 * @param  mixed  $object Post or User
	 *
	 * @return string         The thumbnail, if endabled/found.
	 */
	public function get_thumb( $object ) {
		$thumbnail = '';

		if ( $this->field->options( 'show_thumbnails' ) ) {
			// Set thumbnail if the options is true
			$thumbnail = $this->field->options( 'query_users' )
				? get_avatar( $object->ID, 25 )
				: get_the_post_thumbnail( $object->ID, array( 50, 50 ) );
		}

		return $thumbnail;
	}

	/**
	 * Get ID for the object.
	 *
	 * @since  1.2.4
	 *
	 * @param  mixed  $object Post or User
	 *
	 * @return int            The object ID.
	 */
	public function get_id( $object ) {
		return $object->ID;
	}

	/**
	 * Get title for the object.
	 *
	 * @since  1.2.4
	 *
	 * @param  mixed  $object Post or User
	 *
	 * @return string         The object title.
	 */
	public function get_title( $object ) {
		return $this->field->options( 'query_users' )
			? $object->data->display_name
			: get_the_title( $object );
	}

	/**
	 * Get object label.
	 *
	 * @since  1.2.6
	 *
	 * @param  mixed  $object Post or User
	 *
	 * @return string         The object label.
	 */
	public function get_object_label( $object ) {
		if ( ! $this->do_type_label ) {
			return '';
		}

		$post_type_obj = get_post_type_object( $object->post_type );
		$label = isset( $post_type_obj->labels->singular_name ) ? $post_type_obj->labels->singular_name : $post_type_obj->label;

		return ' &mdash; <span class="object-label">'. $label .'</span>';
	}

	/**
	 * Get edit link for the object.
	 *
	 * @since  1.2.4
	 *
	 * @param  mixed  $object Post or User
	 *
	 * @return string         The object edit link.
	 */
	public function get_edit_link( $object ) {
		return $this->field->options( 'query_users' )
			? get_edit_user_link( $object->ID )
			: get_edit_post_link( $object );
	}

	/**
	 * Get object by id.
	 *
	 * @since  1.2.4
	 *
	 * @param  int   $id Post or User ID.
	 *
	 * @return mixed     Post or User if found.
	 */
	public function get_object( $id ) {
		return $this->field->options( 'query_users' )
			? get_user_by( 'id', absint( $id ) )
			: get_post( absint( $id ) );
	}

	/**
	 * Fetches the default query for items, and combines with any objects attached.
	 *
	 * @since  1.2.4
	 *
	 * @param  array  $args     Array of query args.
	 * @param  array  $attached Array of attached object ids.
	 *
	 * @return array            Array of attached object ids.
	 */
	public function get_all_objects( $args, $attached = array() ) {
		$objects = $this->get_objects( $args );

		$attached_objects = array();
		foreach ( $objects as $object ) {
			$attached_objects[ $this->get_id( $object ) ] = $object;
		}

		if ( ! empty( $attached ) ) {
			$is_users = $this->field->options( 'query_users' );
			$args[ $is_users ? 'include' : 'post__in' ] = $attached;
			$args[ $is_users ? 'number' : 'posts_per_page' ] = count( $attached );

			$new = $this->get_objects( $args );

			foreach ( $new as $object ) {
				if ( ! isset( $attached_objects[ $this->get_id( $object ) ] ) ) {
					$attached_objects[ $this->get_id( $object ) ] = $object;
				}
			}
		}

		return $attached_objects;
	}

	/**
	 * Peforms a get_posts or get_users query.
	 *
	 * @since  1.2.4
	 *
	 * @param  array  $args Array of query args.
	 *
	 * @return array        Array of results.
	 */
	public function get_objects( $args ) {
		return call_user_func( $this->field->options( 'query_users' ) ? 'get_users' : 'get_posts', $args );
	}

	/**
	 * Enqueue admin scripts for our attached posts field
	 */
	protected static function setup_scripts() {
		static $once = false;

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
			'wp-backbone',
		);

		wp_enqueue_script( 'cmb2-attached-posts-field', $url . 'js/attached-posts.js', $requirements, self::VERSION, true );
		wp_enqueue_style( 'cmb2-attached-posts-field', $url . 'css/attached-posts-admin.css', array(), self::VERSION );

		if ( ! $once ) {
			wp_localize_script( 'cmb2-attached-posts-field', 'CMBAP', array(
				'edit_link_template' => str_replace( get_the_ID(), 'REPLACEME', get_edit_post_link( get_the_ID() ) ),
				'ajaxurl'            => admin_url( 'admin-ajax.php', 'relative' ),
			) );

			$once = true;
		}
	}

	/**
	 * Add the find posts div via a hook so we can relocate it manually
	 */
	public function add_find_posts_div() {
		add_action( 'wp_footer', 'find_posts_div' );
	}

	/**
	 * Sanitizes/formats the attached-posts field value.
	 *
	 * @since  1.2.4
	 *
	 * @param  string  $sanitized_val The sanitized value to be saved.
	 * @param  string  $val           The unsanitized value.
	 *
	 * @return string                 The (maybe-modified) sanitized value to be saved.
	 */
	public function sanitize( $sanitized_val, $val ) {
		if ( ! empty( $val ) ) {
			$sanitized_val = explode( ',', $val );
		}

		return $sanitized_val;
	}

	/**
	 * Check to see if we have a post type set and, if so, add the
	 * pre_get_posts action to set the queried post type
	 *
	 * @since  1.2.4
	 *

	 * @return void
	 */
	public function ajax_find_posts() {
		if (
			defined( 'DOING_AJAX' )
			&& DOING_AJAX
			&& isset( $_POST['cmb2_attached_search'], $_POST['retrieved'], $_POST['action'], $_POST['search_types'] )
			&& 'find_posts' == $_POST['action']
			&& ! empty( $_POST['search_types'] )
		) {
			// This is not working until we fix the user query bit.
			if ( ! empty( $_POST['query_users'] ) ) {
				add_action( 'pre_get_users', array( $this, 'modify_query' ) );
			} else {
				add_action( 'pre_get_posts', array( $this, 'modify_query' ) );
			}
		}
	}

	/**
	 * Modify the search query.
	 *
	 * @since  1.2.4
	 *
	 * @param  WP_Query  $query WP_Query instance during the pre_get_posts hook.
	 *
	 * @return void
	 */
	public function modify_query( $query ) {
		$is_users = 'pre_get_users' === current_filter();

		if ( $is_users ) {
			// This is not working until we fix the user query bit.
		} else {
			$types = $_POST['search_types'];
			$types = is_array( $types ) ? array_map( 'esc_attr', $types ) : esc_attr( $types );
			$query->set( 'post_type', $types );
		}

		if ( ! empty( $_POST['retrieved'] ) && is_array( $_POST['retrieved'] ) ) {
			// Exclude posts/users already existing.
			$ids = array_map( 'absint', $_POST['retrieved'] );

			if ( ! empty( $_POST['exclude'] ) && is_array( $_POST['exclude'] ) ) {
				// Exclude the post that we're looking at.
				$exclude = array_map( 'absint', $_POST['exclude'] );
				$ids = array_merge( $ids, $exclude );
			}

			$query->set( $is_users ? 'exclude' : 'post__not_in', $ids );
		}

		$this->maybe_callback( $query, $_POST );
	}

	/**
	 * If field has a 'attached_posts_search_query_cb', run the callback.
	 *
	 * @since  1.2.4
	 *
	 * @param  WP_Query $query     WP_Query instance during the pre_get_posts hook.
	 * @param  array    $post_args The $_POST array.
	 *
	 * @return void
	 */
	public function maybe_callback( $query, $post_args ) {
		$cmb   = isset( $post_args['cmb_id'] ) ? $post_args['cmb_id'] : '';
		$group = isset( $post_args['group_id'] ) ? $post_args['group_id'] : '';
		$field = isset( $post_args['field_id'] ) ? $post_args['field_id'] : '';

		$cmb = cmb2_get_metabox( $cmb );
		if ( $cmb && $group ) {
			$group = $cmb->get_field( $group );
		}

		if ( $cmb && $field ) {
			$group = $group ? $group : null;
			$field = $cmb->get_field( $field, $group );
		}

		if ( $field && ( $cb = $field->maybe_callback( 'attached_posts_search_query_cb' ) ) ) {
			call_user_func( $cb, $query, $field, $this );
		}
	}

}
WDS_CMB2_Attached_Posts_Field::get_instance();
