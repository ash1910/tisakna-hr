<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
    This file is part of Dashboard Analytics add-on for ExpressionEngine.

    Dashboard Analytics is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Dashboard Analytics is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    Read the terms of the GNU General Public License
    at <http://www.gnu.org/licenses/>.
    
    Copyright 2016 Derek Hogue
*/

include(PATH_THIRD.'/dashboard_analytics/config.php');	

class Dashboard_analytics implements Strict_XID
{
	function __construct()
	{
		ee()->lang->loadfile('dashboard_analytics');
		ee()->load->helper('dashboard_analytics');		
		ee()->load->library('csrf');
	}
	
	function getRealtimeData()
	{	
		if(ee('Request')->get('csrf_token') == ee()->csrf->get_user_token())
		{			
			$vars = array(
				'realtime' => ee('dashboard_analytics:AnalyticsData')->getRealtimeStats(),
				'realtime_data_url' => ee('dashboard_analytics:AnalyticsData')->getActionUrl('realtime'),	
			);
			exit(ee('View')
				->make('dashboard_analytics:display')
				->disable(array('monthly','tools'))
				->render($vars));
		}
		else
		{
			$vars = array('error' => lang('da_csrf_expired'));
			exit(ee('View')
				->make('dashboard_analytics:error')
				->disable(array('monthly'))
				->render($vars));
		}
		return false;
	}   

	function getMonthlyData()
	{	
		if(ee('Request')->get('csrf_token') == ee()->csrf->get_user_token())
		{
			$vars = array(
				'colors' => ee('dashboard_analytics:AnalyticsData')->getColors(),
				'daily' => ee('dashboard_analytics:AnalyticsData')->getDailyStats(),
				'hourly' => ee('dashboard_analytics:AnalyticsData')->getHourlyStats(),
				'monthly_data_url' => ee('dashboard_analytics:AnalyticsData')->getActionUrl('monthly'),
				'profile' => ee('dashboard_analytics:AnalyticsData')->getProfile()
			);
			
			exit(ee('View')
				->make('dashboard_analytics:display')
				->disable(array('realtime','tools'))
				->render($vars));
		}
		else
		{
			$vars = array('error' => lang('da_csrf_expired'));
			exit(ee('View')
				->make('dashboard_analytics:error')
				->disable(array('realtime'))
				->render($vars));
		}
		return false;
	}  
}