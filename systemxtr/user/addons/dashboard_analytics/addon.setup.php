<?php

include(PATH_THIRD.'/dashboard_analytics/config.php');	
return array(
	'author' => 'Amphibian',
	'author_url' => 'http://amphibian.info',
	'description' => 'Display realtime and monthly statistics from Google Analytics in your ExpressionEngine¨ admin dashboard.',
	'docs_url' => 'http://amphibian.info/software/ee/dashboard-analytics',
	'models' => array(
		'Settings' => 'Model\Settings',
	),
	'name' => 'Dashboard Analytics',
	'namespace' => 'Amphibian\DashboardAnalytics',
	'services' => array(
		'AnalyticsData' => 'Service\AnalyticsData'
	),
	'settings_exist' => true,
	'version' => DASHBOARD_ANALYTICS_VERSION
);
