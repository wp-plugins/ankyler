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

wp_nonce_field( 'ankyler_widget_update', 'ankyler_widget_nonce' );
?>

<p class="ankyler-uploader">
	<?php echo ( Ankyler::use_old_uploader() ) ? '<input data-selector="'. $this->get_field_id('') .'" type="submit" class="button" name="'. $this->get_field_name( 'uploader_button' ) .'" id="'. $this->get_field_id('uploader_button') .'" value="'. __( 'Select an image', $this->text_domain ) .'" />' : '<input type="submit" class="widefat button" name="'. $this->get_field_name( 'uploader_button' ) .'" id="'. $this->get_field_id('uploader_button') .'" value="'.  __( 'Select an image', $this->text_domain ) .'" onclick="Ankyler_Widget.uploader( \''. $this->id .'\', \''. $this->get_field_id('') .'\' ); return false;" />'; ?>
	<div class="ankyler-preview" id="<?php echo $this->get_field_id( 'preview' ); ?>">
<?php	
		if ( abs( $instance['image'] ) > 0 ) {
			echo wp_get_attachment_image( $instance['image'], 'medium', false );
			echo sprintf( '<div class="ankyler-image-toolbar%s" contenteditable="false">', ( Ankyler::use_old_uploader() ? ' old-uploader' : '' ) );

			if ( Ankyler::use_old_uploader() ) {
				echo sprintf( '<div class="dashicons dashicons-no-alt remove" onclick="delete_image( \'%s\' ); return false;" title="%s">X</div>', $this->get_field_id(''), __( 'Remove image', $this->text_domain ) );
			} else {
				echo sprintf( '<div class="dashicons dashicons-no-alt remove" onclick="Ankyler_Widget.delete_image( \'%s\' ); return false;" title="%s"></div>', $this->get_field_id(''), __( 'Remove image', $this->text_domain ) );
			}
			echo '</div>';
		}
?>
	</div><!-- /ankyler-preview -->

	<input type="hidden" id="<?php echo $this->get_field_id('image'); ?>" name="<?php echo $this->get_field_name('image'); ?>" value="<?php echo $instance['image']; ?>" />
</p><!-- /ankyler-uploader -->
<p class="ankyler-title">
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', $this->text_domain ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( strip_tags( $instance['title'] ) ); ?>" /></p>
</p>
<!-- /ankyler-title -->


<?php

$descriptions = $instance['description'];

$description_title = __( 'Description', $this->text_domain );
foreach ( $descriptions as $key => $description ) {
	$field_id = $this->get_field_id( 'description' ) .'_'. $key;
	$field_name = $this->get_field_name( 'description' ) .'['. $key .']';
	echo sprintf( '<p><label for="%s">%s</label>', $field_id, $description_title );
	echo sprintf( '<textarea rows="8" class="widefat" id="%s" name="%s">%s</textarea></p>', $field_id, $field_name, format_to_edit( $description ) );
}

if ( empty( $instance['read_more'] ) ) {
	
	$instance['read_more'] = __( 'Read more', $this->text_domain );
}
?>
<p><label for="<?php echo $this->get_field_id( 'read_more' ); ?>"><?php _e( 'Read more text', $this->text_domain ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('read_more'); ?>" name="<?php echo $this->get_field_name('read_more'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['read_more'])); ?>" /></p>

<?php

	$singular_names = array();
	$posttypesArray = array();
	$args = array( '_builtin' => false );
	$custom_post_types = get_post_types( $args );
	$posts_and_pages = array(
		'post' => 'post',
		'page' => 'page'
	);
	$all_post_types = array_merge( $posts_and_pages, $custom_post_types );
	
	foreach ( $all_post_types as $post_type ) {
		$post_type_object = get_post_type_object( $post_type );
		if ( isset( $post_type_object->labels->singular_name ) ) {
			$singular_name = $post_type_object->labels->singular_name;
		}
		else {
			$singular_name = $post_type_object->labels->name;
		}
		$singular_names[] = $singular_name;
		$posttypesArray[] = array( $post_type, $singular_name );
	}

	$internal_link_title = '';
	$internal_link_title = __( 'Choose from a', $this->text_domain ) .' '. implode( ", ", $singular_names );
	if ( ! empty( $posttypesArray ) ) {
?>

<p><label for="<?php echo $this->get_field_id('internal_link'); ?>"><?php echo $internal_link_title; ?></label><br>
<select id="<?php echo $this->get_field_id('internal_link'); ?>" name="<?php echo $this->get_field_name('internal_link'); ?>" class="widefat">
<?php
	$key = '';
	foreach ( $posttypesArray as $posttype ) {
		$key .= $posttype[0];
	}
	$transient_key = 'ankyler_dropdown_options_'. md5( $key );

	$optionsString = get_transient( $transient_key );
	
	if ( $optionsString === false ) {

		$optionsString = Ankyler::get_merged_options_from_posttypes( $posttypesArray );
		set_transient( $transient_key, $data = $optionsString, $expiration = 120 );
	}
	$optionsString = str_replace( 'value="'. $instance['internal_link'] .'"', 'value="'. $instance['internal_link'] .'" selected="selected"', $optionsString );
	echo $optionsString;
?>
</select></p>
<?php } ?>

<p><label for="<?php echo $this->get_field_id('external_link'); ?>">
<?php
	echo ( empty( $posttypesArray ) ) ? __( 'Type an external link', $this->text_domain ) : __( 'Or type an external link', $this->text_domain );
?></label>
<input class="widefat" id="<?php echo $this->get_field_id('external_link'); ?>" name="<?php echo $this->get_field_name('external_link'); ?>" type="text" value="<?php echo esc_attr( strip_tags( $instance['external_link'] ) ); ?>" /></p>

<p><label for="<?php echo $this->get_field_id('linktarget'); ?>"><?php _e( 'Link target' ); ?></label>
<select class="widefat" name="<?php echo $this->get_field_name('linktarget'); ?>" id="<?php echo $this->get_field_id('linktarget'); ?>">
	<option value="_self"<?php selected( $instance['linktarget'], '_self' ); ?>><?php _e( 'Open link in a same window/tab', $this->text_domain ); ?></option>
	<option value="_blank"<?php selected( $instance['linktarget'], '_blank' ); ?>><?php _e( 'Open link in a new window/tab', $this->text_domain ); ?></option>
</select></p>
