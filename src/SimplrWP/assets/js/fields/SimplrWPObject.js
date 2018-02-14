jQuery(function($){
	var parent = $('.simplrwp-input select').closest('.simplrwp--simplrwpobject');
	
	// Select2 AJAX call for users to query object in the wp-admin
	$('.simplrwp-input select', parent).select2({
		ajax: {
		    url: ajaxurl,
		    dataType: 'json',
		    delay: 250,
			type: 'post',
			cache: false,
		    data: function (args) {
		      return {
				action: 	'simplrwp/fields/simplrwp_object/query',
				field_key: 	$(this).attr('id'),
				simplrwp_object: $('.current_simplrwp_object').val(),
				post_id: 	1,
				s: 			args.term,
				paged: 		args.page
		      };
		    },
		    processResults: function (data, params) {

		      params.page = params.page || 1;

		      return {
		        results: data.items,
		        pagination: {
		          more: (params.page * 20) < data.total_count
		        }
		      };
		    }
		  }
	});
	
});