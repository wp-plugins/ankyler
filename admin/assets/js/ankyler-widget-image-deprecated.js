jQuery( document ).ready(function( $ ) {
	
	$(document).on( 'click', '.ankyler-uploader .button', function( evt ) {
		
		window.widget_id = $(this).data('selector'); 
		
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true&post_id=0' );
		return false;
	});
	
	window.send_to_editor = function( html ) {
		var class_string    = jQuery( 'img', html ).attr( 'class' );
		var classes         = class_string.split( /\s+/ );
		var image_url       = jQuery( 'img', html ).attr( 'src' );
		var image_id        = 0;

		for ( var i = 0; i < classes.length; i++ ) {
			var source = classes[i].match(/wp-image-([0-9]+)/);
			if ( source && source.length > 1 ) {
				image_id = parseInt( source[1] );
			}
		}
		
		$("#" + window.widget_id + "preview").html( imgHTML( image_url ) );
		$("#" + window.widget_id + "image").val( image_id );
		
		$("#" + window.widget_id + "preview").append( "<div class='ankyler-image-toolbar' contenteditable='false' onclick='delete_image( \"" + window.widget_id + "\" ); return false;'><div class='remove' title='" + ankyler_widget.delete_title + "'>X</div>" );
		
		tb_remove();
	}
	
	// Render html for the image.
	imgHTML = function( image_url ) {
		var img_html = '<img id="ankyler-widget-image" src="' + image_url + '" />';
		return img_html;
	}
	
	delete_image = function ( widget_id_string ) {

		$("#" + widget_id_string + "preview").html( '' );
		$("#" + widget_id_string + "image").val( '0' );
	}
});