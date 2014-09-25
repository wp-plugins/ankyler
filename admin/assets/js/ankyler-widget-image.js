jQuery( document ).ready(function( $ ) {

	Ankyler_Widget = {

		// Call this from the upload button to initiate the upload frame.
		uploader : function( widget_id, widget_id_string ) {

			var frame = wp.media({
				title : ankyler_widget.frame_title,
				multiple : false,
				library : { type : 'image' },
				button : { text :ankyler_widget.button_title }
			});

			// Handle results from media manager.
			frame.on('close',function( ) {
				var images = frame.state().get('selection').toJSON();
				Ankyler_Widget.render( widget_id, widget_id_string, images[0] );
			});

			frame.open();
			return false;
		},
		
		delete_image : function ( widget_id_string ) {

			$("#" + widget_id_string + "preview").html('');
			$("#" + widget_id_string + "image").val('0');
		},

		// Output Image preview and populate widget form.
		render : function( widget_id, widget_id_string, image ) {
			
			$("#" + widget_id_string + "preview").html( Ankyler_Widget.imgHTML( image ) );
			$("#" + widget_id_string + "image").val( image.id );
			
			$("#" + widget_id_string + "preview").append( "<div class='ankyler-image-toolbar' contenteditable='false' onclick='Ankyler_Widget.delete_image( \"" + widget_id_string + "\" ); return false;'>" +
					
					"<div class='dashicons dashicons-no-alt remove' title='" + ankyler_widget.delete_title + "'></div>" );
		},

		// Render html for the image.
		imgHTML : function( image ) {
			var img_html = '<img id="ankyler-widget-image" src="' + image.url + '" ';
			img_html += 'width="' + image.width + '" ';
			img_html += 'height="' + image.height + '" ';
			if ( image.alt != '' ) {
				img_html += 'alt="' + image.alt + '" ';
			}
			img_html += '/>';
			return img_html;
		}
	}
});

