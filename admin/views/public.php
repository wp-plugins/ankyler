<?php
/**
 * Represents the view for the public-facing component of the plugin.
 *
 * This typically includes any information, if any, that is rendered to the
 * frontend of the theme when the plugin is activated.
 *
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

if ( ! isset( $instance['internal_link'] ) ) {
	$instance['internal_link'] = 0;
}

$linkHTML = '';
if ( empty( $instance['linktarget'] ) ) {
	$instance['linktarget'] = '';
}
if ( ! empty( $instance['external_link'] ) ) {

	$linkHTML = sprintf( '<a class="%s" href="%s" target="%s">', $this->widget_options['classname'].'-link widget-anchor', esc_url( $instance['external_link'] ), $instance['linktarget'] );
} elseif ( ! empty( $instance['internal_link'] ) ) {
	$permalink = get_permalink( $instance['internal_link'] );
	if ( $permalink !== false ) {
		$linkHTML = sprintf( '<a class="%s" href="%s">', $this->widget_options['classname'].'-link widget-anchor', $permalink );
	}
} else {
	$external_link = '';
}

echo $before_widget;

if ( ! empty( $instance['image'] ) ) {
	
	$image_output = '';
	$image_properties = wp_get_attachment_image_src( $instance['image'], 'medium' );
	$image_output = sprintf( '<img id="%s" src="%s" alt="%s" title="%s" />', 'ankyler-widget-image', $image_properties[0], esc_attr( $instance['title'] ), esc_attr( $instance['title'] ) );

	if ( !empty( $linkHTML ) ) {

		$image_output = $linkHTML . $image_output ."</a>";
	}
	echo $image_output;
}

if ( ! empty( $instance['title'] ) ) {
	
	/** This filter is documented in wp-includes/default-widgets.php */
	$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

	$title_output = '';
	$title_output .= $before_title . esc_attr( $instance['title'] ) . $after_title;

	if ( ! empty( $linkHTML ) ) {

		$title_output = $linkHTML. $title_output ."</a>";
	}
	echo $title_output;
}

if ( ! empty( $instance['description'][0] ) ) {
	
	$description = apply_filters( 'widget_text', esc_textarea( $instance['description'][0] ) );
	
	$description_output = '';	
	$description_output .= sprintf( '<div class="description %s">', $this->widget_options['classname'].'-description' );
	$description_output .= wpautop( $description );
	
	if ( ! empty( $linkHTML ) ) {
		
		if ( isset( $instance['read_more'] ) && ( ! empty( $instance['read_more'] ) ) ) {
			$read_more_link = str_replace( "class=\"", "class=\"read-more ", $linkHTML ) . $instance['read_more'] ."</a>";
		}
		$description_output .= $read_more_link;
	}
	$description_output .= "</div>";
	echo $description_output;
}

echo $after_widget;
?>