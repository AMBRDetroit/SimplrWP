(function($){
	$( document ).ready(function() {
		 
		 $('.column-' + simplrwp_sortable.sortable_field).width('75px');
		 
		 $( "#the-list" ).sortable().disableSelection().on( "sortstop", function( event, ui ) {
			 // show loading animation
			 loadSimplrWPReorder(event.srcElement, true);
			 
			 var newSortOrder = {};
			 // let's get the new order for the elements
			 for(var rowID=0; rowID<$('.ui-sortable-handle').length; rowID++) {
				 var objID = $('.dashicons-sort', $('.ui-sortable-handle').eq(rowID)).data('id');
				 newSortOrder[rowID] = objID;
			 }
			 
			 $.get(simplrwp_sortable.api_root, {
					action : 'simplrwp_sortable',
					simplrobj : getUrlParameter('page'),
					objectSortOrder : newSortOrder
				}, function(response) {
					if(response == true) {
						// stop loading animation
						loadSimplrWPReorder(event.srcElement, false);
					} else {
						alert('Error organizing!');
					}
				});
		 } );

	});
	
	var getUrlParameter = function(sParam) {
	    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
	        sURLVariables = sPageURL.split('&'),
	        sParameterName,
	        i;

	    for (i = 0; i < sURLVariables.length; i++) {
	        sParameterName = sURLVariables[i].split('=');

	        if (sParameterName[0] === sParam) {
	            return sParameterName[1] === undefined ? true : sParameterName[1];
	        }
	    }
	};
	
	var loadSimplrWPReorder = function(el, loading) {
		if(loading) {
			$(el).hide().after('<img src="' + simplrwp_sortable.simplrwp_url + '/assets/images/loader.gif" />');
		} else {
			$(el).show().next('img').remove();
		}
	}
})(jQuery);