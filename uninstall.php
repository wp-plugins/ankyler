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
	exit;
}

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

if ( is_multisite() ) {

	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
	
	delete_option( 'widget_ankyler-widget' );
	if ( $blogs ) {

	 	foreach ( $blogs as $blog ) {
			switch_to_blog( $blog['blog_id'] );
			
			delete_option( 'widget_ankyler-widget' );
			
			restore_current_blog();
		}
	}

} else {
	
	delete_option( 'widget_ankyler-widget' );
}