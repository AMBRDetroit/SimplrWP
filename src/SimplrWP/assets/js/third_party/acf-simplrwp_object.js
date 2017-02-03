(function($){
	// simplrwp_object
	acf.fields.simplrwp_object = acf.fields.select.extend({
		type: 'simplrwp_object',
		pagination: true
	});
	
	// dynamically load object fields for querying
	var simplrwp_object_field = $('[data-name="simplrwp_object_types"]');
	simplrwp_object_field.on('change', 'input', function(evt) {
		$('[data-name$="-object_fields"]').show();
		
		// if there's a filter of object types only show field options for that object type
		if($(this).val().length>0) {
			var object_types = $(this).val().split('||');
			// hide all object type fields
			$('[data-name$="-object_fields"]').hide();
			
			$.each(object_types, function(i, object_type) {
				$('[data-name="' + object_type + '-object_fields"]').show();
			});
		}
	})
	// trigger the hiding of object type fields
	$('input', simplrwp_object_field).change();
	
	
	// pass along the current values to the query, so not to show duplicate
	acf.add_filter('select2_ajax_data', function(value) {
		value.current_value = $('#acf-' + value.field_key + '-input').val()
		return value;
	});

})(jQuery);