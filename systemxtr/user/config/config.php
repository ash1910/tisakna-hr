<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['session_crypt_key'] = 'c50a7acfccf19cc85284b1878672d800af69aebb';
$config['cookie_prefix'] = '';
$config['enable_online_user_tracking'] = 'n';
$config['enable_hit_tracking'] = 'n';
$config['enable_entry_view_tracking'] = 'n';
$config['dynamic_tracking_disabling'] = '500';
$config['debug'] = '1';
$config['enable_devlog_alerts'] = 'n';
$config['cache_driver'] = 'file';
$config['index_page'] = '';
$config['is_system_on'] = 'y';
$config['multiple_sites_enabled'] = 'n';
// ExpressionEngine Config Items

$config['ce_cache_drivers'] = 'dummy';
$config['ce_lossless_enabled'] = 'smushit';
//Custom overrides

// Find more configs and overrides at
// https://docs.expressionengine.com/latest/general/system_configuration_overrides.html

$config['app_version'] = '3.5.11';
$config['encryption_key'] = 'df3bce93c769c84a407203d76e8c6077df8bf2bb';
$config['database'] = array(
	'expressionengine' => array(
		'hostname' => '127.0.0.1',
		'database' => 'tisakna_baza',
		'username' => 'tisakna_baza',
		'password' => 'iogZpHqQIrsE',
		'dbprefix' => 'exp_',
		'port'     => ''
	),
);

// EOF