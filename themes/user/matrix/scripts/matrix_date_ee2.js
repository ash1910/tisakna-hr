(function($) {


Matrix.bind('date', 'display', function(cell){

	var $input = $('> input', cell.dom.$td),
		date = new Date(),
		hours = date.getHours(),
		minutes = date.getMinutes();

	if (minutes < 10) minutes = '0'+minutes;

	var meridiem = '';

	if (cell.settings.timeFormat == "12")
	{
		if (hours > 12) {
			hours = hours - 12;
			meridiem = " PM";
		} else {
			meridiem = " AM";
		}
		var time = " \'"+hours+':'+minutes+meridiem+"\'";
	}
	else
	{
		var time = " \'"+hours+':'+minutes+"\'";
	}

	var settings = {
		constrainInput: false,
		dateFormat: cell.settings.dateFormat + time,
		defaultDate: new Date(cell.settings.defaultDate)
	};

	$input.datepicker(settings);

});


})(jQuery);
