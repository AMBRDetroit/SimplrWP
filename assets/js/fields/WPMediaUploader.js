jQuery(function($){
  var frame;
  
  // add media
  $('body').on( 'click', '.upload-media', function( evt ){
	  	evt.preventDefault();
	  	
	  	var metaBox = $(this).closest('.simplrwp--media_uploader'),
	  	 	upload_media_link = $(this),
	  	 	delete_media_link = metaBox.find( '.delete-media'),
	  	 	image_container = metaBox.find( '.image-container'),
	  	 	file_container = metaBox.find( '.file-container'),
	  	 	media_input_id = metaBox.find( '.media-id' );
	    
	    // If the media frame already exists, reopen it.
	    if ( frame ) {
	      frame.open();
	      return;
	    }
	  
	    // Create a new media frame
	    frame = wp.media({
	    	title: 'Select or Upload Media',
	    	button: {
	    		text: 'Use this media'
	    	},
	    	multiple: false
	    });
	    
	    // When an image is selected in the media frame...
	    frame.on( 'select', function() {
	      
	    	// Get media attachment details from the frame state
	    	var attachment = frame.state().get('selection').first().toJSON();
	    	
	    	if(['image/png', 'image/jpg', 'image/jpeg', 'image/gif'].indexOf(attachment.mime) > -1) {
		    	// Send the attachment URL to our custom image input field.
		    	image_container.html('<img src="' + attachment.url + '" alt="" style="max-width:100%;" />');
		    	
		    	// Show preview image
			    image_container.removeClass( 'hidden' );
	    	} else {
	    		$('.file-title').text(attachment.title);
	    		$('.file-name').html('<a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a>');
	    		$('.file-size').text(attachment.filesize);
	    		// Show the preview file
			    file_container.removeClass( 'hidden' )
	    	}
	
	    	// Send the attachment id to our hidden input
	    	media_input_id.val( attachment.id );
	
	    	// Hide the add image link
	    	upload_media_link.addClass( 'hidden' );
	
	    	// Unhide the remove image link
	    	delete_media_link.removeClass( 'hidden' );
	    });
	
	    // Finally, open the modal on click
	    frame.open();
  }).on( 'click', '.delete-media', function( evt ){
	  evt.preventDefault();
	  
	  var metaBox = $(this).closest('.simplrwp--media_uploader'),
	 	upload_media_link = metaBox.find('.upload-media'),
	 	delete_media_link = $(this),
	 	image_container = metaBox.find( '.image-container'),
	 	file_container = metaBox.find( '.file-container'),
	 	media_input_id = metaBox.find( '.media-id' );

	    // Hide preview image
	    image_container.addClass( 'hidden' );
	    
	    // Hide the preview file
	    file_container.addClass( 'hidden' )
	
	    // Un-hide the add image link
	    upload_media_link.removeClass( 'hidden' );
	
	    // Hide the delete image link
	    delete_media_link.addClass( 'hidden' );
	
	    // Delete the image id from the hidden input
	    media_input_id.val( '' );
  });

});