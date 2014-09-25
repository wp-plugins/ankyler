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

if ( !class_exists( 'Ankyler_Widget' ) ) {

	/**
	 * Plugin widget class. This class registers the widget.
	 *
	 * @package   Ankyler-Widget
	 * @author    Ankyler <info@ankyler.com>
	 */
	
	class Ankyler_Widget extends WP_Widget {

		/**
	     * Unique identifier for your plugin.
	     *
	     * The variable name is used to create the option name 
	     *
	     * @since    1.0.0
	     *
	     * @var      string
	     */
	    protected $plugin_slug = 'ankyler_widget';
	    
	   /**
		 * The variable name is used as the text domain when internationalizing strings
	     * of text. Its value should match the Text Domain file header in the main
	     * plugin file.
	     * 
	     * @since    1.0.0
	     * 
	     * @var 	string
	     */		
		public $text_domain = 'ankyler-widget';
	    
	    /**
	     * Specifies the classname and description, instantiates the widget and adds the
		 * localisation
		 * 
		 * @since    1.0.0
	     */
		public function __construct() {
			
			$widget_ops = array(
	    		'classname' => $this->get_plugin_slug(),
	    		'description' => __( 'Displays image with a title &amp; description, optional linked to a page, post or custom post from a widget area', $this->text_domain ),
	    		'ankyler_options_name' => $this->get_plugin_slug(),
	    		'customizer_support' => true,
	    	);
	    	$control_ops = array(
	    		'width' => 250
	    	);
	    	$this->WP_Widget( $this->text_domain, 'Ankyler', $widget_ops, $control_ops );

	    	// Refreshing the widget's cached output with each new post
	    	add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
	    	add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
	    	add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
	    	
	    } // end constructor

	    /**
	     * Return the widget slug.
	     *
	     * @since    1.0.0
	     *
	     * @return    Plugin slug variable.
	     */
	    public function get_plugin_slug() {
	    	return $this->plugin_slug;
	    }

		/*--------------------------------------------------*/
		/* Widget API Functions
		/*--------------------------------------------------*/

	    /**
	     * Outputs the content of the widget.
	     *
	     * @since    1.0.0
	     * 
	     * @param array args  The array of form elements
	     * @param array instance The current instance of the widget
	     */
	    public function widget( $args, $instance ) {

	    	// Check if there is a cached output
	    	$cache = wp_cache_get( $this->get_plugin_slug() .'_options', 'widget' );

	    	if ( ! is_array( $cache ) ) {
	    		$cache = array();
	    	}

	    	if ( ! isset ( $args['widget_id'] ) ) {
	    		$args['widget_id'] = $this->id;
	    	}

	    	if ( isset ( $cache[ $args['widget_id'] ] ) ) {
	    		echo $cache[ $args['widget_id'] ];
	    	}
	    	else {

		    	// go on with your widget logic, put everything into a string and …
		        extract( $args, EXTR_SKIP );
		    	
		    	$widget_string = '';
	
		    	ob_start();
		    	include( plugin_dir_path( __FILE__ ) .'views/public.php' );
		    	$widget_string .= ob_get_clean();
		    
		    	$cache[ $args['widget_id'] ] = $widget_string;
		    
		    	wp_cache_set( $this->get_plugin_slug() .'_options', $cache, 'widget' );
		    
		    	echo $widget_string;
	    	}
	    
	    } // end widget
	    
	    /**
	     * Deletes the stored cache
	     * 
	     * @since    1.0.0
	     * 
	     */
	    
	    public function flush_widget_cache() {
	    	wp_cache_delete( $this->get_plugin_slug() .'_options', 'widget' );
	    }
	    
	    /**
	     * Processes the widget's options to be saved. Uses `update_instance` function to validate options
	     *
	     * @since    1.0.0
	     *
	     * @param array new_instance The new instance of values to be generated via the update.
	     * @param array old_instance The previous instance of values before the update.
	     */
	    public function update( $new_instance, $old_instance ) {
	    
	    	$nonce = $_REQUEST[ 'ankyler_widget_nonce' ];
	    	$instance = $old_instance;
	    	
	    	if ( wp_verify_nonce( $nonce, 'ankyler_widget_update' ) ) {
	    
	    		if ( current_user_can( 'edit_theme_options' ) ) {
	    
	    			$instance = $this->update_instance( $new_instance, $old_instance );
	    		}
	    	}
	    	
	    	$this->flush_widget_cache();
	    	
	    	return $instance;
	    
	    } // end update
	    
	    /**
	     * Update instance values
	     *
	     * @since    1.0.0
	     *
	     * @param object $new_instance Widget Instance
	     * @param object $old_instance Widget Instance
	     * @return object
	     */
	    function update_instance( $new_instance, $old_instance ) {

	    	$default_widget_options = Ankyler::get_widget_defaults();
	    	$instance = wp_parse_args( (array) $old_instance, $default_widget_options );
	    
	    	foreach ( $default_widget_options as $field => $value ) {
	    
	    		if ( isset( $new_instance[ $field ] ) ) { //if not set: use defaults
	    			switch ( $field ) {
	    				case 'title':
	    				case 'read_more':
	    					$instance[ $field ] = strip_tags( $new_instance[ $field ] );
	    					break;
	    				case 'description':
	    					if ( ! is_array( $new_instance[ $field ] ) ) { //from 0.8 onward we use a description array instead of string
	    						$new_instance[ $field ] = (array) $new_instance[ $field ];
	    					}
	    					foreach ( $new_instance[ $field ] as $key => $description ) {
	    						if ( ! current_user_can( 'unfiltered_html' ) ) {
	    							$new_instance[ $field ][ $key ] = wp_filter_post_kses( $description );
	    						}
	    					}
	    					$instance[$field] = $new_instance[$field];
	    					break;
	    				case 'image':
	    					$instance[$field] = $new_instance[$field];
	    					break;
	    				case 'internal_link':
	    					$instance[$field] = absint( $new_instance[$field] );
	    					break;
	    				case 'external_link':
	    					$instance[$field] = esc_url_raw( $new_instance[$field] );
	    					if ( ! empty( $instance[$field] ) ) {
	    						if ( strpos( $instance[$field], get_home_url() ) === 0 ) { //link starts with current domain, make link relative
	    							$instance[$field] = wp_make_link_relative( $instance[$field] );
	    						}
	    					}
	    					break;
	    				case 'linktarget':
	    					switch ( $new_instance[$field] ) {
	    						case '_blank':
	    							$instance[$field] = '_blank';
	    							break;
	    						case '_self':
	    						default:
	    							$instance[$field] = '_self';
	    							break;
	    					}
	    					break;
	    			}
	    		}
	    	}
	    	return $instance;
	    }
	    
	    /**
	     * Generates the administration form for the widget.
	     *
	     * @since    1.0.0
	     * 
	     * @param array instance The array of keys and values for the widget.
	     */
	    public function form( $instance ) {
	    	
	    	// Define default values for your variables
	    	$instance = wp_parse_args( (array) $instance, Ankyler::get_widget_defaults() );
	    	
	    	// Display the admin form
	    	include( plugin_dir_path(__FILE__) . 'views/widget-admin.php' );
	    
	    } // end form
	}
}