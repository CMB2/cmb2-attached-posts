<?php
/**
 * Plugin Name: CMB2 Field Type: Attached Posts
 * Plugin URI: https://github.com/WebDevStudios/cmb2-attached-posts
 * Description: Attached posts field type for CMB2.
 * Version: 1.2.4
 * Author: WebDevStudios
 * Author URI: http://webdevstudios.com
 * License: GPLv2+
 */

/**
 * WDS_CMB2_Attached_Posts_Field loader
 *
 * Handles checking for and smartly loading the newest version of this library.
 *
 * @category  WordPressLibrary
 * @package   WDS_CMB2_Attached_Posts_Field
 * @author    WebDevStudios <contact@webdevstudios.com>
 * @copyright 2016 WebDevStudios <contact@webdevstudios.com>
 * @license   GPL-2.0+
 * @version   1.2.4
 * @link      https://github.com/WebDevStudios/cmb2-attached-posts
 * @since     1.2.3
 */

/**
 * Copyright (c) 2016 WebDevStudios (email : contact@webdevstudios.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Loader versioning: http://jtsternberg.github.io/wp-lib-loader/
 */

if ( ! class_exists( 'WDS_CMB2_Attached_Posts_Field_125', false ) ) {

	/**
	 * Versioned loader class-name
	 *
	 * This ensures each version is loaded/checked.
	 *
	 * @category WordPressLibrary
	 * @package  WDS_CMB2_Attached_Posts_Field
	 * @author   WebDevStudios <contact@webdevstudios.com>
	 * @license  GPL-2.0+
	 * @version  1.2.4
	 * @link     https://github.com/WebDevStudios/cmb2-attached-posts
	 * @since    1.2.3
	 */
	class WDS_CMB2_Attached_Posts_Field_125 {

		/**
		 * Instance of WDS_CMB2_Attached_Posts_Field_125
		 * @var WDS_CMB2_Attached_Posts_Field_125
		 */
		public static $instance = null;

		/**
		 * WDS_CMB2_Attached_Posts_Field version number
		 * @var   string
		 * @since 1.2.3
		 */
		const VERSION = '1.2.5';

		/**
		 * Current version hook priority.
		 * Will decrement with each release
		 *
		 * @var   int
		 * @since 1.2.3
		 */
		const PRIORITY = 9998;

		public static function init() {
			if ( null == self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		private function __construct() {
			if ( ! defined( 'CMB2_ATTACHED_POSTS_FIELD_LOADED' ) ) {
				/**
				 * A constant you can use to check if WDS_CMB2_Attached_Posts_Field is loaded
				 * for your plugins/themes with WDS_CMB2_Attached_Posts_Field dependency.
				 *
				 * Can also be used to determine the priority of the hook
				 * in use for the currently loaded version.
				 */
				define( 'CMB2_ATTACHED_POSTS_FIELD_LOADED', self::PRIORITY );
			}

			add_action( 'cmb2_init', array( $this, 'include_attached_posts' ) );
		}

		public function include_attached_posts() {
			if ( class_exists( 'WDS_CMB2_Attached_Posts_Field', false ) ) {
				return;
			}

			if ( ! defined( 'CMB2_ATTACHED_POSTS_FIELD_VERSION' ) ) {
				/**
				 * Defines the currently loaded version of WDS_CMB2_Attached_Posts_Field.
				 */
				define( 'CMB2_ATTACHED_POSTS_FIELD_VERSION', self::VERSION );
			}

			if ( ! defined( 'CMB2_ATTACHED_POSTS_FIELD_DIR' ) ) {
				/**
				 * Defines the directory of the currently loaded version of WDS_CMB2_Attached_Posts_Field.
				 */
				define( 'CMB2_ATTACHED_POSTS_FIELD_DIR', dirname( __FILE__ ) . '/' );
			}

			// Include and initiate WDS_CMB2_Attached_Posts_Field.
			require_once CMB2_ATTACHED_POSTS_FIELD_DIR . 'init.php';
		}
	}

	// Load similarly to CMB2
	WDS_CMB2_Attached_Posts_Field_125::init();
}
