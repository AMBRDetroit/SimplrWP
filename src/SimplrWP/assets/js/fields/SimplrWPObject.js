jQuery(function($){
	// Select2 AJAX call for users to query object in the wp-admin
	$('.simplrwp-input select').select2({
		ajax: {
		    url: ajaxurl,
		    dataType: 'json',
		    delay: 250,
			type: 'post',
			cache: false,
		    data: function (args) {
		      console.log(args);
		      return {
				action: 	'simplrwp/fields/simplrwp_object/query',
				field_key: 	args.key,
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