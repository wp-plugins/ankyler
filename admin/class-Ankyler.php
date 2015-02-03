<?php
/**
 * Ankyler Widget
 *
 * @package   Ankyler-Widget
 * @author    Ankyler <info@ankyler.com>
 * @license   GPL-2.0+
 * @link      http://www.ankyler.com/
 * @copyright 2014, Ankyler
 */

// Block direct requests
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'Ankyler' ) ) {

	/**
	 * Plugin class. This class envokes the class to start the widget.
	 *
	 * The widget class is located in `class-ankyler-widget.php`
	 *
	 * @package   Ankyler-Widget
 	 * @author    Ankyler <info@ankyler.com>
	 */
	
	class Ankyler {

		/**
		 * Instance of this class.
		 *
		 * @since    1.0.0
		 *
		 * @var      object
		 */
		protected static $instance = null;
	
		/**
		 * Slug of the plugin screen.
		 *
		 * @since    1.0.0
		 *
		 * @var      string
		 */
		protected $plugin_screen_hook_suffix = null;
	
		/**
		 * Plugin version, used for cache-busting of style and script file references.
		 *
		 * @since   1.0.0
		 *
		 * @var     string
		 */
		const VERSION = '1.0';
		
		/**
		 * The variable name is used as the text domain when internationalizing strings
	     * of text. Its value should match the Text Domain file header in the main
	     * plugin file.
	     * 
	     *  @var 	string
		 */		
		public $text_domain = 'ankyler-widget';
		
		/**
		 * Initialize the plugin by loading admin scripts & styles and adding the
		 * localisation
		 *
		 * @since     1.0.0
		 */
		private function __construct() {

			// Load admin style sheet and JavaScript.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

			// Load plugin text domain
			add_action( 'init', array( $this, 'load_plugin_text_domain' ) );
		}

		/**
		 * Determine if the current of WordPress supports the new media manager.
		 * 
		 * @since    1.0.0
		 * 
		 * @return 	 bool true if the current version of WordPress does NOT support the current media manager
		 */
		public static function use_old_uploader() {
			
			global $wp_version;
			return ( version_compare( $wp_version, '3.4', '>=' ) AND ! function_exists( 'wp_enqueue_media' ) );		
		}
		
		/**
		 * 
		 * Merges <options> from $posttypesArray into each other
		 * 
		 * @since    1.0.0
		 * 
		 * @return 	 string		of all merged dropdown Select options
		 */
		public static function get_merged_options_from_posttypes( $posttypesArray ) {
			
			$mergedOptions[] = '<option value="0">'. __( 'No choice', 'ankyler-widget' ) .'</option>';
			foreach ( $posttypesArray as $posttype ) {
				$args = array(
					'posts_per_page' => - 1,
					'orderby' => 'title',
					'order' => 'ASC',
					'post_type' => $posttype[0],
					'post_status' => 'publish' );
	
				$options = get_posts( $args );
				if ( ! empty( $options ) ) {
					$mergedOptions[] = '<optgroup label="'. $posttype[1] .'" value="-1">';
					foreach ( $options as $option ) {
						$mergedOptions[] = '<option value="' . $option->ID . '">' . $option->post_title . '</option>';
					}
					$mergedOptions[] = '</optgroup>';
				}
			}
			$mergeString = implode( $mergedOptions );
			return $mergeString;
		}
	
		/**
		 * Get default widget options
		 * 
		 * @since     1.0.0
		 * 
		 * @return	  array 	An array with all default options
		 */
		
		public static function widget_defaults() {
		
			$widget_defaults = array (
				'title' => '',
				'description' => array( '' ),
				'image' => '0',
				'linktarget' => '',
				'external_link' => '',
				'internal_link' => '',
				'read_more' => '',
			);
		
			return $widget_defaults;
		}
		
		/**
		 * Returns the widget defaults array
		 * 
		 * @since     1.0.0
		 * 
		 * @return    array		An array with all default options
		 */
		
		public static function get_widget_defaults() {
		
			return self::widget_defaults();
		}
		
		/**
		 * Return an instance of this class.
		 *
		 * @since     1.0.0
		 *
		 * @return    object    A single instance of this class.
		 */
		public static function get_instance() {
	
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
	
			return self::$instance;
		}
	
		/**
		 * Register and enqueue admin-specific style sheet.
		 *
		 * @since     1.0.0
		 *
		 * @return    null    Return early if no settings page is registered.
		 */
		public function enqueue_admin_styles() {
			
			global $pagenow;
			
			if ( is_admin() && ( $pagenow == 'widgets.php' || $pagenow == 'media-upload.php' || $pagenow == 'async-upload.php' || $pagenow == 'customize.php' ) ) {
				wp_enqueue_style( $this->text_domain .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), $this::VERSION );
			}
		}
	
		/**
		 * Register and enqueue admin-specific JavaScript.
		 *
		 * @since     1.0.0
		 *
		 * @return    null    Return early if no settings page is registered.
		 */
		public function enqueue_admin_scripts() {

			global $pagenow;

			if ( is_admin() && ( 'widgets.php' == $pagenow || 'media-upload.php' == $pagenow || 'async-upload.php' == $pagenow || 'customize.php' == $pagenow ) ) {
				if ( $this->use_old_uploader() ) {
					wp_enqueue_style('thickbox');
					wp_enqueue_script('media-upload');
	
					wp_enqueue_script( $this->text_domain . '-widget-script', plugins_url('assets/js/ankyler-widget-image-deprecated.js', __FILE__ ), array( 'thickbox' ), $this::VERSION );
					
					add_filter( 'image_send_to_editor', array( $this,'image_send_to_editor'), 1, 8 );
					add_filter( 'gettext', array( $this, 'replace_text_in_thickbox' ), 1, 3 );
					add_filter( 'media_upload_tabs', array( $this, 'media_upload_tabs' ) );
				} else {
					
					wp_enqueue_media();
					wp_enqueue_script( $this->text_domain . '-widget-script', plugins_url('assets/js/ankyler-widget-image.js', __FILE__), array( 'jquery', 'media-upload', 'media-views' ), $this::VERSION );
					wp_localize_script( $this->text_domain . '-widget-script', 'ankyler_widget', array(
						'frame_title'	=> __( 'Select an image', $this->text_domain ),
						'button_title'	=> __( 'Attach image to widget', $this->text_domain ),
						'delete_title'	=> __( 'Remove image', $this->text_domain ),
					) );
				}
				
			}
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function load_plugin_text_domain() {
		
			$domain = $this->text_domain;
			$locale = get_locale();
		
			load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
			load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
		}
		
		/**
		 * Somewhat hacky way of replacing "Insert into Post" with "Insert into Widget"
		 *
		 * @since	1.0.0
		 *
		 * @param	string 		$translated_text text that has already been translated (normally passed straight through)
		 * @param	string		$source_text text as it is in the code
		 * @param	string		$domain domain of the text
		 * 
		 * @return	string 		returns the $text 
		 */
		function replace_text_in_thickbox( $translated_text, $source_text, $domain ) {
			if ( 'Insert into Post' == $source_text ) {
				return __( 'Attach image to widget', $this->text_domain );
			}
			return $translated_text;
		}
		
		/**
		 * Remove from url tab until that functionality is added to widgets.
		 *
		 * @since   1.0.0
		 *
		 * @param 	array		returns $tabs
		 */
		function media_upload_tabs( $tabs ) {
			unset( $tabs['type_url'] );
			return $tabs;
		}
	}
}