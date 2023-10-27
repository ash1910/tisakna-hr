$(document).ready(function (){
	

	//	----------------------------------------
	//	Table checkbox toggler
	//	----------------------------------------

	$('td.select-all .choice').on('click', function(){
		
		pos = $(this).parent('td').index();

		if($(this).find('input[type="checkbox"]').is(':checked'))
		{
			$('tr:not([class*="sub-heading"])').each(function(index) {
				$(this).find('td').eq(pos).find('input[type="checkbox"]:not(:disabled)').prop('checked', true).closest('label.choice').addClass('chosen');
			});
		}
		else
		{
			$('tr:not([class*="sub-heading"])').each(function(index) {
				$(this).find('td').eq(pos).find('input[type="checkbox"]:not(:disabled)').prop('checked', false).closest('label.choice').removeClass('chosen');
			});
		}
	})
});