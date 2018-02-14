jQuery(function($){
	var repeater = $('.simplrwp--repeater');
	
	// trigger for adding a new repeater element
	$(repeater).on('click','.js-repeat-field', function(evt) {
		evt.preventDefault();
		
		var field_name = $(this).data('name');
		var parent = $('#' + field_name + ' .simplrwp--all_instances');
		
		// delete empty element, if exists
		$('.empty_repeater', parent).remove();
		
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
		
		var repeater_name = $(this).closest('.simplrwp--repeater').attr('id');
		
		$(this).closest('.simplrwp--repeater_field').remove();
		
		var repeater_item_count  = $('.simplrwp--all_instances .simplrwp--repeater_field', '#' + repeater_name).length;
		
		if(repeater_item_count == 0) {
			$('.simplrwp--all_instances', '#' + repeater_name).html('<input class="empty_repeater" type="hidden" name="' + repeater_name + '" />');
		}
		
		
	})
});
