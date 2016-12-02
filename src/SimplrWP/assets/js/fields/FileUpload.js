jQuery(function($) {
	// we attach a change event to the file upload
	// BUG: when we attached this to the body and dispatch it triggers twice, something in WordPress causing this
	// so instead we attach directly to the upload
	$("#wdi_file_upload").on("change", {}, function(evt) {
		var files = evt.target.files || evt.dataTransfer.files;
		if(files.length > 0) {
			var thisFile = files[0];
			var acceptedTypes = ["application/pdf","application/x-download"];
			if(acceptedTypes.indexOf(thisFile.type) != -1) {
				// create the form data
				var formData = new FormData();
				formData.append("action", "wdi_file_upload");
				formData.append("file", thisFile);
				formData.append("wdi_file_upload_nonce",$('#wdi_file_upload_nonce').val());
				formData.append("post_ID",$('#post_ID').val());
				// send the data
				$.ajax({
					type : "POST",
					url : ajaxurl,
					processData: false,
					contentType: false,
					data : formData
				}).done(function(response){
					console.log(response);
					// TODO
					if(response.path && response.url){
						var wdiFilesHTML = '<a href="'+response.url+'" target="_blank">Original File</a>';
						$('#wdi-files').html(wdiFilesHTML);
					}
				}).fail(function(error){
					// TODO
					console.log(error);
				});
			}
		}
	});
	
});



