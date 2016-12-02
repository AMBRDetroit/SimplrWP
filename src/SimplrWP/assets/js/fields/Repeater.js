jQuery(function($){
	var repeater = $('.simplrwp--repeater');
	
	// trigger for adding a new repeater element
	$(repeater).on('click','.js-repeat-field', function(evt) {
		evt.preventDefault();
		
		var field_name = $(this).data('name');
		var parent = $('#' + field_name + ' .simplrwp--all_instances');
		
		var html = simplr.template.build({
			component : 'field-' + field_name,
			tokens : {
				instance: $('.simplrwp--repeater_field', parent).length
			}
		});
		
		parent.append(html);
	
	// trigger for removing repeater element
	}).on('click','.js-remove-field', function(evt) {
		evt.preventDefault();
		
		$(this).closest('.simplrwp--repeater_field').remove();
		
	})
});
