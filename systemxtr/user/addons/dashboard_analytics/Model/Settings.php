<?php
	
namespace Amphibian\DashboardAnalytics\Model;
use EllisLab\ExpressionEngine\Service\Model\Model;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Settings extends Model {

	protected static $_primary_key = 'id';
	protected static $_table_name = 'dashboard_analytics';
	protected static $_typed_columns = array(
		'site_id' => 'int',
		'refresh_token' => 'string',
		'profile' => 'json',
		'settings' => 'json',
		'hourly_cache' => 'json',
		'daily_cache' => 'json'
	);
	
	protected $id;
	protected $site_id;
	protected $refresh_token;
	protected $profile;
	protected $settings;
	protected $hourly_cache;
	protected $daily_cache;

}