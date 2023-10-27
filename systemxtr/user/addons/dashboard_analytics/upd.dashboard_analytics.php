<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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

class Dashboard_analytics_upd { 

	var $package;
	var $version = DASHBOARD_ANALYTICS_VERSION;
	 
	function __construct()
	{
		$this->package = 'Dashboard_analytics';
	}
	
	
	function install()
	{	
		$data = array(
			'module_name' => $this->package,
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		);
		ee()->db->insert('modules', $data);

		$data = array(
			'class' => $this->package,
			'method' => 'getRealtimeData'
		);
		ee()->db->insert('exp_actions', $data);

		$data = array(
			'class' => $this->package,
			'method' => 'getMonthlyData'
		);
		ee()->db->insert('exp_actions', $data);
		
		ee()->load->dbforge();
		ee()->dbforge->add_field(array(
			'id' => array('type' => 'int', 'constraint' => '9', 'unsigned' => TRUE, 'null' => FALSE, 'auto_increment' => TRUE),
			'site_id' => array('type' => 'int', 'constraint' => '5', 'unsigned' => TRUE, 'null' => FALSE),
			'refresh_token' => array('type' => 'varchar', 'constraint' => '255'),
			'profile' => array('type' => 'text'),
			'settings' => array('type' => 'text'),
			'hourly_cache' => array('type' => 'text'),
			'daily_cache' => array('type' => 'text')
		));
		ee()->dbforge->add_key(array('id'), TRUE);
		ee()->dbforge->add_key(array('site_id'));
		ee()->dbforge->create_table('dashboard_analytics');		
		
		return TRUE;
	}

	
	function update($current = '')
	{
		if($current == $this->version)
		{
			return FALSE;
		}
		return TRUE;
	}
	
	
	function uninstall()
	{
		ee()->load->dbforge();
		ee()->dbforge->drop_table('dashboard_analytics');	
		ee()->db->delete('modules', array('module_name' => $this->package));
		ee()->db->delete('actions', array('class' => $this->package));
		
		/*
			Reset Member Group Homepages	
		*/
		$groups = ee('Model')->get('MemberGroup')
			->filter('can_access_cp', 'y')
			->filter('cp_homepage', 'custom')
			->filter('cp_homepage_custom', 'addons/settings/dashboard_analytics/display')
			->all();
		if(!empty($groups))
		{
			foreach($groups as $group)
			{
				$group->cp_homepage = 'overview';
				$group->cp_homepage_custom = '';
				$group->save();
			}
		}
		
		/*
			Reset Member Homepages	
		*/
		$members = ee('Model')->get('Member')
			->filter('cp_homepage', 'custom')
			->filter('cp_homepage_custom', 'addons/settings/dashboard_analytics/display')
			->all();
		if(!empty($members))
		{
			foreach($members as $member)
			{
				$member->cp_homepage = '';
				$member->cp_homepage_custom = '';
				$member->save();
			}
		}
		
		return TRUE;
	}
}