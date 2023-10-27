$( document ).ready(function() {
	$('select[name="add[channel]"]').on('change', function() {
		var label = $(this).find('option:selected').text();
		if (label) {
			$('select[name="add[fields][]"] optgroup').each(function() {
				if ($(this).attr('label') == label) {
					$(this).show();
				} else {
					$(this).hide();
				}
			});
		}
		
	});
	
	$('.add_select').on('click', function() {
		$(this).removeClass('add_select');
	});
	
	$('.remove_row').on('click', function() {
		$(this).closest('tr').remove();
	});
});