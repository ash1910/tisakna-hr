$(document).ready(function()
{
	$('.da-profile-intro').on('click', '.warn a', function(e)
	{
		e.preventDefault();
		window.open($(this).attr('href'), 'google_oauth', 'width=600,height=600,location=no,toolbar=no,scrollbars=no');
	});
});