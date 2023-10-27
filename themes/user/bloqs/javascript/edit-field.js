jQuery(function($) {

	var blockselectors = $('.blockselectors');

//-----------------------------------------------

	//
	// A work around which re-enables our hidden fields which 
	// The ee core is trying to disable on page load
	//
	$(function(){
		var hidden_inputs = blockselectors.find( $('input:hidden[name^="blockdefinitions"]') );
		hidden_inputs.each(function(){
			$(this).removeAttr('disabled');
		});
	});

//-----------------------------------------------

	var updateSelectors = function() {
		var count = 1;
		blockselectors.find('.blockselector').each(function() {
			var selector = $(this);
			var order = selector.find('[js-order]');
			var checkbox = selector.find('[js-checkbox]');

			if (checkbox.is(':checked')) {
				order.val(count);
				count++;
			}
			else {
				order.val(0);
			}
		});
	}

	blockselectors.blocksSortable({
		handle: '.blockselector-handle'
	});
	blockselectors.bind('sortupdate', updateSelectors);
	blockselectors.bind('change', 'js-checkbox', updateSelectors);
});