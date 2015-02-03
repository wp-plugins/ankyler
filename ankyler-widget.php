<?php
/*
Plugin Name:	Ankyler Widget
Plugin URI:		http://www.ankyler.com/
Description:	With the Ankyler widget you can place title-image-text-link combinations directly in a widget area.
Author:			Ruud Laan, Edwin Siebel
Author URI:		http://www.ankyler.com/
Version:		1.0.1
Text Domain:	ankyler-widget
License:		GPL-2.0+
License URI:	http://www.gnu.org/licenses/gpl-2.0.txt
Domain Path:	/languages
*/

// Block direct requests
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

require_once( plugin_dir_path( __FILE__ ) .'admin/class-Ankyler.php' );
require_once( plugin_dir_path( __FILE__ ) .'admin/class-Ankyler-widget.php' );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
*----------------------------------------------------------------------------*/

add_action( 'plugins_loaded', array( 'ankyler', 'get_instance' ) );
add_action( 'widgets_init', create_function( '', 'register_widget("ankyler_widget");' ) );

?>