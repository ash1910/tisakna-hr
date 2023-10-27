<?php

namespace Amphibian\DashboardAnalytics\Service;

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

class AnalyticsData {

	var $client_id = '648607189724-rjo6n9u5alvm97m07mnaj4tsknvlq8uk.apps.googleusercontent.com';
	var $client_secret = 'oxaEOnq5BFwzKuCASxG7FAzZ';
	var $redirect_uri = 'urn:ietf:wg:oauth:2.0:oob';
	var $token_endpoint = 'https://accounts.google.com/o/oauth2/token';
	var $data_endpoint = 'https://www.googleapis.com/analytics/v3/data/ga';
	var $realtime_endpoint = 'https://www.googleapis.com/analytics/v3/data/realtime';
	
	var $access_token;
	var $current_date;
	var $settings;
	var $profile_id;
	var $site_id;

	
	function __construct()
	{
		ee()->load->helper('dashboard_analytics');
		
		$this->current_date = ee()->localize->format_date('%Y-%m-%d', ee()->localize->now);
		$this->site_id = (ee('Request')->get('site_id')) ? ee('Request')->get('site_id') : ee()->config->item('site_id');
		
		$settings = ee('Model')->get('dashboard_analytics:Settings')
			->filter('site_id', $this->site_id)
			->first();
		if(!empty($settings))
		{
			$this->settings = $settings;
		}
		else
		{
			$this->settings = ee('Model')->make('dashboard_analytics:Settings', array(
				'site_id' => $this->site_id
			));
		}
		
		if(!empty($this->settings->profile))
		{
			$this->profile_id = 'ga:'.$this->settings->profile['id'];
		}
	}
	

	function doCurlRequest($server, $query, $method = 'post')
	{
		$args = '';
		foreach ($query as $key => $value)
		{
			$args .= trim($key).'='.trim($value).'&';
		}
		$args = rtrim($args, '&');
		
		if($method == 'get')
		{
			$server .= '?'.$args;
		}
				
		$ch = curl_init($server);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		if($method == 'post')
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
		}
		/*
			Some issues have been popping-up with IPv6 which I do not understand,
			as I am faking my way through this whole thing.
			Forcing an IPv4 connection seems to work.
			It will likely fuck up something else though.
		*/
		if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4'))
		{
			curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		}		
		curl_setopt($ch, CURLOPT_REFERER, 'http://'.$_SERVER['SERVER_NAME']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		return json_decode($response, true);
	}


	function exchangeAuthorizationForToken($code)
	{
		$r = array();
		$args = array(
			'code' => trim($code),
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'redirect_uri' => $this->redirect_uri,
			'grant_type' => 'authorization_code'
		);
		if($response = $this->doCurlRequest($this->token_endpoint, $args))
		{
			if(isset($response['error']))
			{
				$r['error'] = true;
			}

			// Save our refresh token
			if(isset($response['refresh_token']))
			{
				$this->settings->refresh_token = $response['refresh_token'];
				$this->settings->save();
			}
			
			// Set the fresh access token
			if(isset($response['access_token']))
			{
				$this->access_token = $response['access_token'];
			}
		}
		return $r;
	}
	
	
	function getAccessToken()
	{
		if(empty($this->access_token))
		{
			if($token = $this->getRefreshToken())
			{
				$args = array(
					'refresh_token' => $token,
					'client_id' => $this->client_id,
					'client_secret' => $this->client_secret,
					'grant_type' => 'refresh_token'					
				);
				if($response = $this->doCurlRequest($this->token_endpoint, $args))
				{
					if(!empty($response['access_token']))
					{
						$this->access_token = $response['access_token'];
						return 'ok';
					}
					
					if(!empty($response['error']))
					{
						return 'error';
					}
				}				
			}
			else
			{
				return 'empty';
			}
		}
		return 'ok';
	}


	function getActionUrl($type)
	{
		ee()->load->library('csrf');
		$method = ($type == 'realtime') ? 'getRealtimeData' : 'getMonthlyData';
		if($action_id = ee()->functions->fetch_action_id('Dashboard_analytics', $method))
		{
			$base = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? str_replace('http://', 'https://', ee()->config->item('site_url')) : ee()->config->item('site_url');
			return $base.'?ACT='.ee()->functions->insert_action_ids($action_id).'&site_id='.ee()->config->item('site_id');
		}
	}
	
		
	function getColors()
	{
		return array(
			'accent1' => '#1f80bd',
			'accent2' => '#26394a',
			'accent3' => '#7baf55',
			'accent4' => '#e5f7bf',
			'accent5' => '#3c4e5f',
			'subtle' => '#808080',
			'text' => '#000000'
		);
	}


	function getDailyStats()
	{		
		if(!empty($this->settings->daily_cache) && $this->settings->daily_cache['cache_date'] >= $this->current_date)
		{
			$data = $this->settings->daily_cache;
		}
		else
		{
			$this->getAccessToken();
			$data = array(
				'yesterday' => array(
					'pageviews' => 0,
					'visits' => 0,
					'pages_per_visit' => 0,
					'avg_visit' => '00:00:00',
					'bounce_rate' => '0%'
				),
				'lastmonth' => array(
					'pageviews' => 0,
					'visits' => 0,
					'pages_per_visit' => 0,
					'avg_visit' => '00:00:00',
					'bounce_rate' => '0%',
					'content' => array(),
					'referrers' => array(),
					'devices' => array(),
					'countries' => array()
				)
			);
			$data['cache_date'] = $this->current_date;
			
			// Compile yesterday's stats
			$args = $this->getDefaultQueryArgs();
			$args['start-date'] = 'yesterday';
			$args['end-date'] = 'yesterday';
			$args['metrics'] = 'ga:pageviews,ga:sessions,ga:sessionDuration,ga:bounces,ga:entrances';
			$args['dimensions'] = 'ga:hour';
			$request = $this->doCurlRequest($this->data_endpoint, $args, 'get');
			
			if(isset($request['rows']))
			{	
				$data['yesterday']['pageviews'] = $request['totalsForAllResults']['ga:pageviews'];
				$data['yesterday']['pageviews_sparkline_data'] = daHourlyChartData($request['rows'], 'pageviews');
				
				$data['yesterday']['visits'] = $request['totalsForAllResults']['ga:sessions'];
				$data['yesterday']['visits_sparkline_data'] = daHourlyChartData($request['rows'], 'visits');
				
				$data['yesterday']['pages_per_visit'] = daAveragePagesPerVisit($request['totalsForAllResults']['ga:pageviews'], $request['totalsForAllResults']['ga:sessions']);
				$data['yesterday']['pages_per_visit_sparkline_data'] = daHourlyChartData($request['rows'], 'pages_per_visit');
							
				$data['yesterday']['avg_visit'] = daAverageVisitLength($request['totalsForAllResults']['ga:sessionDuration'], $request['totalsForAllResults']['ga:sessions']);
				$data['yesterday']['avg_visit_sparkline_data'] = daHourlyChartData($request['rows'], 'avg_visit');
				
				$data['yesterday']['bounce_rate'] = daBounceRate($request['totalsForAllResults']['ga:bounces'], $request['totalsForAllResults']['ga:sessions'], $request['totalsForAllResults']['ga:entrances']);
				$data['yesterday']['bounce_rate_sparkline_data'] = daHourlyChartData($request['rows'], 'bounce_rate');
			}
			
			
			// Compile last month's stats
			$args = $this->getDefaultQueryArgs();
			$args['start-date'] = '30daysAgo';
			$args['end-date'] = 'yesterday';
			$args['metrics'] = 'ga:pageviews,ga:sessions,ga:sessionDuration,ga:bounces,ga:entrances,ga:newUsers';
			$args['dimensions'] = 'ga:date';
			$request = $this->doCurlRequest($this->data_endpoint, $args, 'get');
			
			if(isset($request['rows']))
			{	
				$data['lastmonth']['pageviews'] = 
				$request['totalsForAllResults']['ga:pageviews'];
				$data['lastmonth']['pageviews_sparkline_data'] = daHourlyChartData($request['rows'], 'pageviews');

				$data['lastmonth']['visits'] = 
				$request['totalsForAllResults']['ga:sessions'];
				$data['lastmonth']['visits_sparkline_data'] = daHourlyChartData($request['rows'], 'visits');
								
				$data['lastmonth']['pages_per_visit'] = 
				daAveragePagesPerVisit($request['totalsForAllResults']['ga:pageviews'], $request['totalsForAllResults']['ga:sessions']);
				$data['lastmonth']['pages_per_visit_sparkline_data'] = daHourlyChartData($request['rows'], 'pages_per_visit');
				
				$data['lastmonth']['avg_visit'] = 
				daAverageVisitLength($request['totalsForAllResults']['ga:sessionDuration'], $request['totalsForAllResults']['ga:sessions']);
				$data['lastmonth']['avg_visit_sparkline_data'] = daHourlyChartData($request['rows'], 'avg_visit');
				
				$data['lastmonth']['bounce_rate'] = daBounceRate($request['totalsForAllResults']['ga:bounces'], $request['totalsForAllResults']['ga:sessions'], $request['totalsForAllResults']['ga:entrances']);
				$data['lastmonth']['bounce_rate_sparkline_data'] = daHourlyChartData($request['rows'], 'bounce_rate');
							
				$data['lastmonth']['users_chart'] = daUsersChartData($request['totalsForAllResults']['ga:newUsers'], $request['totalsForAllResults']['ga:sessions']);
				$data['lastmonth']['traffic_chart'] = daMonthlyChartData($request['rows']);
			}
		
			// Compile last month's devices
			$args = $this->getDefaultQueryArgs();
			$args['start-date'] = '30daysAgo';
			$args['end-date'] = 'yesterday';
			$args['dimensions'] = 'ga:deviceCategory';
			$args['metrics'] = 'ga:sessions';
			$args['sort'] = '-ga:sessions';
			$request = $this->doCurlRequest($this->data_endpoint, $args, 'get');

			if(!empty($request['rows']))
			{
				$data['lastmonth']['device_chart'] = daDeviceChartData($request['rows']);
			}
					
			// Compile last month's top content
			$args = $this->getDefaultQueryArgs();
			$args['start-date'] = '30daysAgo';
			$args['end-date'] = 'yesterday';
			$args['metrics'] = 'ga:pageviews';
			$args['dimensions'] = 'ga:hostname,ga:pagePath,ga:pageTitle';
			$args['sort'] = '-ga:pageviews';
			$args['max-results'] = 10;
			$request = $this->doCurlRequest($this->data_endpoint, $args, 'get');
	
			if(isset($request['rows']))
			{	
				$data['lastmonth']['content'] = array();
				$i = 0;
				
				// Make a temporary array to hold page paths
				// (for checking dupes resulting from www vs non-www hostnames)
				$paths = array();
				
				foreach($request['rows'] as $row)
				{
					// Do we already have this page path?
					$dupe_key = array_search($row[1], $paths);
					if($dupe_key !== FALSE)
					{
						// Combine the pageviews of the dupes
						$data['lastmonth']['content'][$dupe_key]['count'] = ( $row[3] + $data['lastmonth']['content'][$dupe_key]['count'] );
						$data['lastmonth']['content'][$dupe_key]['percentage'] = daPercentage($data['lastmonth']['content'][$dupe_key]['count'], $request['totalsForAllResults']['ga:pageviews'], true).'%';

					}
					else
					{
						$data['lastmonth']['content'][$i]['title'] = $row[2];
						$data['lastmonth']['content'][$i]['url'] = 'http://'.$row[0].$row[1];
						$data['lastmonth']['content'][$i]['count'] = $row[3];
						$data['lastmonth']['content'][$i]['percentage'] = daPercentage($row[3], $request['totalsForAllResults']['ga:pageviews'], true).'%';

						// Store the page path at the same position so we can check for dupes
						$paths[$i] = $row[1];
						$i++;
					}				
				}
				
				// Slice down to 5 results
				$data['lastmonth']['content'] = array_slice($data['lastmonth']['content'], 0, 5);
				
				if(DASHBOARD_ANALYTICS_DUMMY == TRUE)
				{
					if($dummy = $this->getDummyContent('http://www.amazon.com/gp/rss/bestsellers/movies-tv/2959130011/'))
					{
						foreach($dummy as $k => $item)
						{
							if(isset($data['lastmonth']['content'][$k]))
							{
								$data['lastmonth']['content'][$k]['title'] = preg_replace('/^\#\d:\s/', '', $item->get_title());
								$data['lastmonth']['content'][$k]['url'] = $item->get_permalink();	
							}
						}	
					}
				}
			}
			
			
			// Compile last month's top referrers
			$args = $this->getDefaultQueryArgs();
			$args['start-date'] = '30daysAgo';
			$args['end-date'] = 'yesterday';
			$args['metrics'] = 'ga:sessions';
			$args['dimensions'] = 'ga:source,ga:referralPath,ga:medium';
			$args['sort'] = '-ga:sessions';
			$args['max-results'] = 10;
			$request = $this->doCurlRequest($this->data_endpoint, $args, 'get');
	
			if(isset($request['rows']))
			{		
				$data['lastmonth']['sources'] = array();
				$i = 0;
				
				$titles = array();
				
				foreach($request['rows'] as $i =>$row)
				{
					// Do we already have this page path?
					$dupe_key = array_search($row[0], $titles);
					if($dupe_key !== FALSE)
					{
						// Combine the pageviews of the dupes
						$data['lastmonth']['sources'][$dupe_key]['count'] = ( $row[3] + $data['lastmonth']['sources'][$dupe_key]['count'] );
						$data['lastmonth']['sources'][$dupe_key]['percentage'] = daPercentage($data['lastmonth']['sources'][$dupe_key]['count'], $request['totalsForAllResults']['ga:sessions'], true).'%';
					}
					else
					{
						$data['lastmonth']['sources'][$i]['title'] = ($row[0] == '(direct)' || $row[0] == '(not set') ? 'direct' : $row[0];
						$data['lastmonth']['sources'][$i]['url'] = 'http://'.$row[0].$row[1];
						$data['lastmonth']['sources'][$i]['type'] = $row[2];
						$data['lastmonth']['sources'][$i]['count'] = $row[3];
						$data['lastmonth']['sources'][$i]['percentage'] = daPercentage($row[3], $request['totalsForAllResults']['ga:sessions'], true).'%';

						// Store the source title at the same position so we can check for dupes
						$titles[$i] = $row[0];
						$i++;
					}
				}
				
				// Slice down to 5 results
				$data['lastmonth']['sources'] = array_slice($data['lastmonth']['sources'], 0, 5);
			}

			// Compile last month's top countries
			$args = $this->getDefaultQueryArgs();
			$args['start-date'] = '30daysAgo';
			$args['end-date'] = 'yesterday';
			$args['metrics'] = 'ga:sessions';
			$args['dimensions'] = 'ga:country';
			$args['sort'] = '-ga:sessions';
			$args['max-results'] = 10;
			$request = $this->doCurlRequest($this->data_endpoint, $args, 'get');
			if(!empty($request['rows']))
			{
				$data['lastmonth']['countries'] = array();
				foreach($request['rows'] as $row)
				{
					if($row[0] != '(not set)')
					{
						$data['lastmonth']['countries'][] = array(
							'country' => $row[0],
							'count' => $row[1],
							'percentage' => daPercentage($row[1], $request['totalsForAllResults']['ga:sessions'], true).'%'
						);
					}
				}
				// Slice down to 5 results
				$data['lastmonth']['countries'] = array_slice($data['lastmonth']['countries'], 0, 5);
			}
						
			// Now cache it
			$this->saveSettings('daily_cache', $data);
		}

		return $data;
	}


	function getDefaultQueryArgs()
	{
		return array(
			'access_token' => $this->access_token,
			'ids' => $this->profile_id,
			'userIp' => ee()->input->ip_address()
		);
	}


	function getDummyContent($url)
	{
		ee()->load->library('rss_parser');
		try
		{
			$feed = ee()->rss_parser->create($url);
			return $feed->get_items(0,5);
		}
		catch (Exception $e)
		{
			return false;
		}
	}


	function getHourlyStats()
	{			
		if(!empty($this->settings->hourly_cache) && ($this->settings->hourly_cache['cache_time'] + 3600) >= ee()->localize->now)
		{
			$data = $this->settings->hourly_cache;
		}
		else
		{
			$this->getAccessToken();
			$data = array(
				'pageviews' => 0,
				'visits' => 0,
				'pages_per_visit' => 0,
				'avg_visit' => '00:00:00',
				'bounce_rate' => '0%'
			);
			$data['cache_time'] = ee()->localize->now;

			$args = $this->getDefaultQueryArgs();
			$args['start-date'] = 'today';
			$args['end-date'] = 'today';
			$args['metrics'] = 'ga:pageviews,ga:sessions,ga:sessionDuration,ga:bounces,ga:entrances';
			$args['dimensions'] = 'ga:hour';
			$request = $this->doCurlRequest($this->data_endpoint, $args, 'get');
			if(isset($request['rows']))
			{			
				$data['pageviews'] = $request['totalsForAllResults']['ga:pageviews'];
				$data['pageviews_sparkline_data'] = daHourlyChartData($request['rows'], 'pageviews');
				
				$data['visits'] = $request['totalsForAllResults']['ga:sessions'];
				$data['visits_sparkline_data'] = daHourlyChartData($request['rows'], 'visits');
			
				$data['pages_per_visit'] = daAveragePagesPerVisit($request['totalsForAllResults']['ga:pageviews'], $request['totalsForAllResults']['ga:sessions']);
				$data['pages_per_visit_sparkline_data'] = daHourlyChartData($request['rows'], 'pages_per_visit');
				
				$data['avg_visit'] = daAverageVisitLength($request['totalsForAllResults']['ga:sessionDuration'], $request['totalsForAllResults']['ga:sessions']);
				$data['avg_visit_sparkline_data'] = daHourlyChartData($request['rows'], 'avg_visit');
				
				$data['bounce_rate'] = daBounceRate($request['totalsForAllResults']['ga:bounces'], $request['totalsForAllResults']['ga:sessions'], $request['totalsForAllResults']['ga:entrances']);
				$data['bounce_rate_sparkline_data'] = daHourlyChartData($request['rows'], 'bounce_rate');
			}

			// Now cache it
			$this->saveSettings('hourly_cache', $data);

		}
		return $data;
	}
	

	function getOauthUrl()
	{
		return 'https://accounts.google.com/o/oauth2/auth?response_type=code'.AMP.'client_id='.$this->client_id.AMP.'redirect_uri='.$this->redirect_uri.AMP.'scope=https://www.googleapis.com/auth/analytics.readonly'.AMP.'access_type=offline';
	}
	
	
	function getProfile()
	{
		return $this->settings->profile;
	}


	function getProfileList()
	{
		$this->getAccessToken();
		
		$r = array(
			'error' => '',
			'names' => array(),
			'profiles' => array('' => '--'),
			'segments' => array()
		);

		if(!empty($this->access_token))
		{
			$args = array(
				'access_token' => $this->access_token	
			);
			
			if($response = $this->doCurlRequest(
				'https://www.googleapis.com/analytics/v3/management/accounts/~all/webproperties/~all/profiles'
				, $args, 'get')
			)
			{
				if(isset($response['error']))
				{
					$r['error'] = 'error';
				}
				
				if(isset($response['items']))
				{
					$prefixes = array('http://','https://','www.');
					foreach($response['items'] as $result)
					{
						/*
							Normalize the domain and view names 
							and determine the best value to use for the dropdown menu	
						*/
						$domain = (!empty($result['websiteUrl'])) ? rtrim(str_replace($prefixes, '', $result['websiteUrl']), '/') : false;
						$view = (!empty($result['name'])) ? rtrim(str_replace($prefixes, '', $result['name']), '/') : false;
						$title = ($domain == $view) ? $domain : $domain.' ('.$view.')';  
						
						$r['profiles'][$result['id']] = $r['names'][$result['id']] = $title;
						$r['segments'][$result['id']] = 'a'.$result['accountId'].'w'.$result['internalWebPropertyId'].'p'.$result['id'];
					}
					asort($r['profiles']);
				}
				else
				{
					$r['error'] = 'no_profiles';
				}
			}
		}
		return $r;
	}


	function getRealtimeStats()
	{
		$this->getAccessToken();
		$data = array(
			'site_id' => $this->site_id,
			'active_users' => 0,
			'sources' => array(),
			'countries' => array(),
			'content' => array()
		);
		
		$args = $this->getDefaultQueryArgs();
		$args['dimensions'] = 'rt:country';
		$args['metrics'] = 'rt:activeUsers';
		$args['sort'] = '-rt:activeUsers';
		$args['max-results'] = 6;
		$request = $this->doCurlRequest($this->realtime_endpoint, $args, 'get');
		if(!empty($request['rows']))
		{
			foreach($request['rows'] as $row)
			{
				if($row[0] != '(not set)')
				{
					$data['countries'][] = array(
						'country' => $row[0],
						'users' => $row[1],	
						'percentage' => daPercentage($row[1], $request['totalsForAllResults']['rt:activeUsers'], true).'%'
					);	
				}
			}
			if(count($data['countries']) > 5)
			{
				array_pop($data['countries']);
			}
		}
		
		$args = $this->getDefaultQueryArgs();
		$args['dimensions'] = 'rt:source';
		$args['metrics'] = 'rt:activeUsers';
		$args['sort'] = '-rt:activeUsers';
		$args['max-results'] = 5;
		$request = $this->doCurlRequest($this->realtime_endpoint, $args, 'get');
		if(!empty($request['rows']))
		{
			foreach($request['rows'] as $row)
			{
				$data['sources'][] = array(
					'source' => ($row[0] == '(not set)') ? 'direct' : $row[0],
					'users' => $row[1]	,
					'percentage' => daPercentage($row[1], $request['totalsForAllResults']['rt:activeUsers'], true).'%'
				);
			}
		}
		
		$args = $this->getDefaultQueryArgs();
		$args['dimensions'] = 'rt:pagePath,rt:pageTitle';
		$args['metrics'] = 'rt:activeUsers';
		$args['sort'] = '-rt:activeUsers';
		$args['max-results'] = 5;
		$request = $this->doCurlRequest($this->realtime_endpoint, $args, 'get');
		if(!empty($request['rows']))
		{
			foreach($request['rows'] as $row)
			{
				$data['content'][] = array(
					'page_path' => $row[0],
					'page_title' => (empty($row[1]) || $row[1] == '(not set)') ? $row[0] : $row[1],
					'users' => $row[2],
					'percentage' => daPercentage($row[2], $request['totalsForAllResults']['rt:activeUsers'], true).'%'
				);
			}
			
			if(DASHBOARD_ANALYTICS_DUMMY == TRUE)
			{
				if($dummy = $this->getDummyContent('http://www.amazon.com/gp/rss/bestsellers/books/10129/'))
				{
					foreach($dummy as $k => $item)
					{
						if(isset($data['content'][$k]))
						{
							$data['content'][$k]['page_title'] = preg_replace('/^\#\d:\s/', '', $item->get_title());
							$data['content'][$k]['page_path'] = $item->get_permalink();	
						}
					}	
				}
			}
		}

		$args = $this->getDefaultQueryArgs();
		$args['dimensions'] = 'rt:deviceCategory';
		$args['metrics'] = 'rt:activeUsers';
		$args['sort'] = '-rt:activeUsers';
		$request = $this->doCurlRequest($this->realtime_endpoint, $args, 'get');
		if(!empty($request['rows']))
		{
			foreach($request['rows'] as $row)
			{
				$data['active_users'] = $request['totalsForAllResults']['rt:activeUsers'];
				$data['devices'][] = array(
					'device' => $row[0],
					'users' => $row[1]	,
					'percentage' => daPercentage($row[1], $request['totalsForAllResults']['rt:activeUsers'], true).'%',
					'percentage_numeric' => daPercentage($row[1], $request['totalsForAllResults']['rt:activeUsers'], true),
					'percentage_precise' => daPercentage($row[1], $request['totalsForAllResults']['rt:activeUsers']).'%'
				);
			}
		}
						
		return $data;		
	}


	function getRefreshToken()
	{
		return (!empty($this->settings->refresh_token)) ? $this->settings->refresh_token : false;
	}
	

	function getSettings()
	{
		return $this->settings->settings;
	}
	
	
	function refreshCache()
	{
		$this->settings->daily_cache = $this->settings->hourly_cache = null;
		$this->getHourlyStats();
		$this->getDailyStats();
	}
	

	function revokeToken()
	{
		if($token = $this->getRefreshToken())
		{
			$args = array(
				'token' => $token
			);
			$this->doCurlRequest('https://accounts.google.com/o/oauth2/revoke', $args, 'get');
		}
		$this->settings->refresh_token = '';
		$this->settings->save();
	}

	
	function saveSettings($setting, $data = null)
	{
		$this->settings->$setting = $data;
		$this->settings->save();
	}
	
}