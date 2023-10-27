
<!-- Start Dashboard Analytics -->
<script>
	$('.breadcrumb').hide();
	$('.da-create-menu').appendTo('.tbl-ctrls');
	settingsLink = $('.tbl-ctrls > h1 > ul');
	$('.tbl-ctrls > h1').text('<?=$page_title;?>').append(settingsLink);
</script>
<?php if($this->enabled('monthly')) : ?>
<script src="https://www.gstatic.com/charts/loader.js"></script>
<script>
	google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawCharts);
    
	var delay = (function(){
		var timer = 0;
		return function(callback, ms)
		{
	    	clearTimeout(timer);
	    	timer = setTimeout(callback, ms);
		};
	})();
	
	$(window).resize(function()
	{
		delay(function(){ drawCharts(); }, 500);
	});
	
	function drawCharts()
	{
		if((typeof google === 'undefined') || (typeof google.visualization === 'undefined'))
		{
			return false;
		}
		else
		{
			var formatter = new google.visualization.NumberFormat({ fractionDigits: 0 });
	
			/*
				Sparklines	
			*/
			if($('.da-sparkline').length)
			{
				$('.da-sparkline').empty().each(function(e)
				{
					sparkline = new google.visualization.LineChart($(this)[0]);
					sparklineHeight = $(this).height();
					sparklineWidth = $(this).width();
					sparklineOptions = {
						axisTitlesPosition: 'none',
						backgroundColor:  { fill:'transparent' },
						chartArea: { height: sparklineHeight, left: 0, top: 0, width: sparklineWidth },
						colors: ['<?=$colors['accent1']?>'],
						enableInteractivity: false,
						hAxis: { baselineColor: 'transparent', gridlineColor: 'transparent', textPosition: 'none' },
						height: sparklineHeight,
						legend: 'none',
						lineWidth: 2,
						vAxis: { baselineColor: 'transparent', gridlineColor: 'transparent', textPosition: 'none' },
						width: sparklineWidth
					}
					sparklineData = new google.visualization.DataTable($(this).data('data'));
					sparkline.draw(sparklineData, sparklineOptions);
				});		
			}	
				
			/*
				Device Chart	
			*/
			if($('.da-device-chart').length)
			{
				deviceChartEl = $('.da-device-chart')[0];
				deviceChart = new google.visualization.PieChart(deviceChartEl);
				deviceChartWidth = $(deviceChartEl).width();
				deviceData = new google.visualization.DataTable($(deviceChartEl).data('data'));
				deviceChartOptions = {
					chartArea: {left:10,top:10,width:'90%',height:'90%'},
					colors:['<?=$colors['accent1']?>','<?=$colors['accent3']?>','<?=$colors['accent4']?>'],
					legend: 'none',
					pieHole: 0.2,
					pieSliceText: 'label',
				  	width: deviceChartWidth
		        };
		        
				formatter.format(deviceData, 1);        
				deviceChart.draw(deviceData, deviceChartOptions);
			}
			
			/*
				Users Chart	
			*/
			if($('.da-user-chart').length)
			{
				usersChartEl = $('.da-user-chart')[0];
				usersChart = new google.visualization.PieChart(usersChartEl);
				usersChartWidth = $(usersChartEl).width();
				usersData = new google.visualization.DataTable($(usersChartEl).data('data'));
				usersChartOptions = {
					chartArea: {left:10,top:10,width:'90%',height:'90%'},
					colors:['<?=$colors['accent1']?>','<?=$colors['accent3']?>'],
					legend: 'none',
					pieHole: 0.2,
					pieSliceText: 'label',
				  	width: usersChartWidth
		        };
		        
				formatter.format(usersData, 1);        
				usersChart.draw(usersData, usersChartOptions);
			}
					
			/*
				Pageviews/Session Chart	
			*/
			if($('.da-traffic-chart').length)
			{
				trafficChartEl = $('.da-traffic-chart')[0];
				trafficChart = new google.visualization.AreaChart(trafficChartEl);
				trafficData = new google.visualization.DataTable($(trafficChartEl).data('data'));
				trafficFrameWidth = $(trafficChartEl).width();
				trafficChartWidth = trafficFrameWidth - 20;
				trafficFrameHeight = $('.da-device-chart').height();
				trafficChartHeight = trafficFrameHeight - 20;		
				trafficChartOptions = {
					backgroundColor:  '#FFFFFF',
					chartArea: { height: trafficChartHeight, left: 10, top: 10, width: trafficChartWidth },
					colors: ['<?=$colors['accent2']?>','<?=$colors['accent1']?>'],
					hAxis: { textPosition: 'in', textStyle: { color: '<?=$colors['text']?>', fontSize: 11 }, showTextEvery: 4 },
					height: trafficFrameHeight,
					isStacked: false,
					legend: 'none',
					lineWidth: 2,
					pointSize: 4,
					series: [ { areaOpacity: 0 }, null],
					tooltipTextStyle: { color: '<?=$colors['text']?>', fontSize: 11 },
					vAxis: { baselineColor: 'transparent', gridlineColor: 'transparent', textPosition: 'in', textStyle: { color: '<?=$colors['text']?>', fontSize: 11 }, viewWindowMode: 'pretty' },
					width: trafficFrameWidth
				}
		
				formatter.format(trafficData, 1);
				formatter.format(trafficData, 2);
				trafficChart.draw(trafficData, trafficChartOptions);
			}
			
			$('.da-col-group .box').matchHeight();
		}
	}
</script>
<?php endif; ?>
	
<script>
	$(window).load(function()
	{
		startSpinner();
		fetchMonthly();
		<?php if($this->enabled('realtime')) : ?>
		fetchRealTime();
		setInterval(fetchRealTime, 60000);
		<?php endif; ?>
	});
	
	function startSpinner()
	{
		opts = {
			color: '<?=$colors['subtle']?>',
			lines: 13,
			length: 4,
			width: 2,
			radius: 6,
		};
		if($('.da-realtime-outer').length)
		{
			spinner = new Spinner(opts).spin();
			$('.da-realtime-outer').append(spinner.el);		
		}
		if($('.da-monthly-outer').length)
		{
			spinner = new Spinner(opts).spin();
			$('.da-monthly-outer').append(spinner.el);
		}
	}
	
	function fetchRealTime()
	{
		url = $('.da-realtime-outer').data('url')+'&csrf_token='+EE.CSRF_TOKEN;
		$.get(url, function(data)
		{
			$('.da-realtime-outer').animate({ 'opacity': 0 }, 100, function()
			{
				$('.da-realtime-inner').replaceWith($(data).find('.da-realtime-inner'));
				$('.da-realtime-outer .spinner').remove();
				$('.da-realtime-inner .box').matchHeight();
				$('.da-realtime-outer').animate({ 'opacity': 1 }, 500);
			});
		});
	}
	
	function fetchMonthly()
	{
		url = $('.da-monthly-outer').data('url')+'&csrf_token='+EE.CSRF_TOKEN;
		$.get(url, function(data)
		{
			$('.da-monthly-outer').animate({ 'opacity': 0 }, 100, function()
			{
				$('.da-monthly-inner').replaceWith($(data).find('.da-monthly-inner'));
				$('.da-monthly-outer .spinner').remove();
				drawCharts();
				$('.da-monthly-inner .box').matchHeight();
				$('.da-monthly-outer').animate({ 'opacity': 1 }, 500);
			});
		});
	}
</script>
<!-- End Dashboard Analytics -->
