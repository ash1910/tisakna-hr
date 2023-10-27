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

class Dashboard_analytics_mcp {

	
	var $settings_url;
	var $display_url;
	var $version = DASHBOARD_ANALYTICS_VERSION;


	function __construct()
	{
		ee()->lang->loadfile('dashboard_analytics');
		ee()->lang->loadfile('content');
		ee()->lang->loadfile('homepage');
		ee()->load->helper('dashboard_analytics');

		$this->settings_url = ee('CP/URL', 'addons/settings/dashboard_analytics');
		$this->display_url = ee('CP/URL', 'addons/settings/dashboard_analytics/display');
	}
	
	
	function deauth()
	{
		ee('dashboard_analytics:AnalyticsData')->revokeToken();
		
		ee('CP/Alert')->makeInline('shared-form')
	      ->asSuccess()
		  ->withTitle(lang('da_auth_removed_heading'))
		  ->addToBody(lang('da_auth_removed_desc'))
	      ->defer();

		ee()->functions->redirect($this->settings_url);
	}
	

	function display()
	{
		ee()->cp->load_package_css('display');
		ee()->cp->load_package_js('display');
		ee()->cp->load_package_js('jquery.matchHeight-min');
		ee()->cp->load_package_js('spin');
		
		$page_title = ee()->config->item('site_name') . ' ' . lang('overview');
		$display = ee('View')->make('dashboard_analytics:display');
		$javascript = ee('View')->make('dashboard_analytics:javascript');
		
		$colors = ee('dashboard_analytics:AnalyticsData')->getColors();
		$settings = ee('dashboard_analytics:AnalyticsData')->getSettings();
		$disabled = array();
				
		if(isset($settings['show_realtime']) && $settings['show_realtime'] == 'n')
		{
			$disabled[] = 'realtime';
		}
		if(!empty($disabled))
		{
			$display->disable($disabled);
			$javascript->disable($disabled);
		}
		
		$vars = array(
			'can_moderate_comments' => false,
			'colors' => $colors,
			'heading' => $page_title,
			'menu' => ee()->menu->generate_menu(),
			'monthly_data_url' => ee('dashboard_analytics:AnalyticsData')->getActionUrl('monthly'),
			'page_title' => $page_title,
			'realtime_data_url' => ee('dashboard_analytics:AnalyticsData')->getActionUrl('realtime'),
			'spam_module_installed' => false
		);
		
		/*
			Comments	
		*/
		if(ee()->config->item('enable_comments') == 'y')
		{
			$vars['number_of_pending_comments'] = ee('Model')->get('Comment')
				->filter('site_id', ee()->config->item('site_id'))
				->filter('status', 'p')
				->count();

			$vars['number_of_spam_comments'] = ee('Model')->get('Comment')
				->filter('site_id', ee()->config->item('site_id'))
				->filter('status', 's')
				->count();
			
			if($vars['number_of_pending_comments'] > 0 || $vars['number_of_spam_comments'] > 0)
			{
				$vars['spam_module_installed'] = (ee('Model')->get('Module')->filter('module_name', 'Spam')->count());
				$vars['can_moderate_comments'] = ee()->cp->allowed_group('can_moderate_comments');	
			}
		}		
		
		ee()->cp->add_to_head('<link type="text/css" rel="stylesheet" href="'.URL_THIRD_THEMES.'dashboard_analytics/css/flag-icon.min.css" />');
		ee()->cp->add_to_foot($javascript->render($vars));
		
		
		if($this->_hasSettingsAccess())
		{
			ee()->view->header = array(
				'title' => $page_title,
				'toolbar_items' =>  array(
					'settings' => array(
						'href' => $this->settings_url,
						'title' => lang('da_profile_settings')
					)
				)
			);
		}
		else
		{
			ee()->view->header = array(
				'title' => $page_title
			);
		}
		
		return array(
			'body' => $display->render($vars),
			'heading' => $page_title
		);	
	}

	
	function index() 
	{		
		$this->_setupSidebar();
		ee()->cp->load_package_js('settings');

		ee()->view->header = array(
			'title' => lang('dashboard_analytics_module_name')
		);
				
		$vars = array(
			'form_vars' => array(
				'base_url' => ee('CP/URL', 'addons/settings/dashboard_analytics/save'),
				'extra_alerts' => array('da-warning','da-error'),
				'save_btn_text_working' => 'btn_saving',
			),
			'token_status' => ee('dashboard_analytics:AnalyticsData')->getAccessToken()
		);
										
		if($vars['token_status'] == 'ok')
		{
			$profile = ee('dashboard_analytics:AnalyticsData')->getProfile();
			$profiles = ee('dashboard_analytics:AnalyticsData')->getProfileList();
			$settings = ee('dashboard_analytics:AnalyticsData')->getSettings();
			
			$vars['heading'] = lang('da_profile_settings');
			$vars['form_vars']['cp_page_title'] = lang('da_profile_settings');
			$vars['form_vars']['save_btn_text'] = 'da_save_profile';
			
			$sections = array();
					
			if( ! $this->_hasSettingsAccess())
			{
				/*
					No access	
				*/
				$vars['no_access'] = true;
				ee('CP/Alert')->makeInline('da-error')
					->asIssue()
					->withTitle(lang('da_no_access'))
					->addToBody(lang('da_no_access_desc'))
					->cannotClose()
					->now();
			}
			else
			{
				$admin_groups = array();
				$dashboard_groups = array();
				$selected_admin_groups = array();
				$selected_dashboard_groups = array();
				
				foreach($this->_getMemberGroups() as $group)
				{
					$dashboard_groups[$group->group_id] = $group->group_title;
					if($group->cp_homepage == 'custom' && $group->cp_homepage_custom == 'addons/settings/dashboard_analytics/display')
					{
						$selected_dashboard_groups[] = $group->group_id;
					}
					if($group->group_id != 1)
					{
						$admin_groups[$group->group_id] = $group->group_title;
						if(isset($settings['admin_groups']) && in_array($group->group_id, $settings['admin_groups']))
						{
							$selected_admin_groups[] = $group->group_id;
						}
					}
				}
	
				$fields = array(
					'profile' => array(
						'type' => 'select',
						'choices' => $profiles['profiles'],
						'value' => (!empty($profile['id'])) ? $profile['id'] : ''
					)
				);
				
				if(isset($profiles['segments']))
				{
					foreach($profiles['segments'] as $k => $v)
					{
						$fields['profile_segment_'.$k] = array(
							'type' => 'hidden',
							'value' => $v
						);
					}
				}
				if(isset($profiles['names']))
				{
					foreach($profiles['names'] as $k => $v)
					{
						$fields['profile_name_'.$k] = array(
							'type' => 'hidden',
							'value' => $v
						);
					}
				}
				
				$sections[] = array(
					'title' => lang('da_select_profile'),
					'desc' => '(<a href="'.ee('CP/URL', 'addons/settings/dashboard_analytics/deauth').'">'.lang('da_deauth').'</a>)',
					'fields' => $fields
				);
				
				$sections[] = array(
					'title' => lang('da_show_realtime'),
					'desc' => lang('da_show_realtime_desc'),
					'fields' => array(
						'show_realtime' => array(
							'type' => 'yes_no',
							'value' => (isset($settings['show_realtime'])) ? $settings['show_realtime'] : 'y'
						)
					)
				);
				
				$sections[] = array(
					'title' => lang('da_dashboard_groups'),
					'desc' => lang('da_dashboard_groups_desc'),
					'fields' => array(
						'dashboard_groups' => array(
							'type' => 'checkbox',
							'choices' => $dashboard_groups,
							'value' => $selected_dashboard_groups
						)
					)
				);
	
				$sections[] = array(
					'title' => lang('da_admin_groups'),
					'desc' => lang('da_admin_groups_desc'),
					'fields' => array(
						'admin_groups' => array(
							'type' => 'checkbox',
							'choices' => $admin_groups,
							'value' => $selected_admin_groups
						)
					)
				);
				
				if(!empty($profiles['error']))
				{	
					if($profiles['error'] == 'error')
					{
						ee('CP/Alert')->makeInline('da-error')
							->asIssue()
							->withTitle(lang('da_profile_error_heading'))
							->addToBody(lang('da_profile_error'))
							->cannotClose()
							->now();
					}
					
					if($profiles['error'] == 'no_profiles')
					{
						ee('CP/Alert')->makeInline('da-warning')
							->asWarning()
							->withTitle(lang('da_no_profiles_heading'))
							->addToBody(lang('da_no_profiles'))
							->cannotClose()
							->now();
					}
				}	
			}
		}
		else
		{
			$vars['heading'] = lang('da_authorization');
			$sections[] = array(
				'title' => lang('da_auth_code'),
				'desc' => lang('da_auth_code_desc'),
				'fields' => array(
					'auth_code' => array(
						'type' => 'text',
						'value' => ''
					)
				)
			);
			$vars['form_vars']['extra_alerts'] = array('da-warning','da-error');
			$vars['form_vars']['cp_page_title'] = lang('da_authorization');
			$vars['form_vars']['save_btn_text'] = 'da_generate_token';
			
			if($vars['token_status'] == 'error')
			{
				ee('CP/Alert')->makeInline('da-error')
					->asIssue()
					->withTitle(lang('da_existing_token_error_heading'))
					->addToBody(lang('da_existing_token_error'))
					->cannotClose()
					->now();
			}
			
			if($vars['token_status'] == 'empty' || $vars['token_status'] == 'error')
			{
				ee('CP/Alert')->makeInline('da-warning')
					->asWarning()
					->withTitle(lang('da_instructions_heading'))
					->addToBody(lang('da_instructions_1').' <a href="'.ee('dashboard_analytics:AnalyticsData')->getOauthUrl().'"><b>'.lang('da_instructions_2').'</b></a>')
					->cannotClose()
					->now();
			}
		}
		
		$vars['form_vars']['sections'] = array($sections);
				
		return array(
			'body' => ee('View')->make('dashboard_analytics:index')->render($vars),
			'breadcrumb' => array(
				$this->settings_url->compile() => lang('dashboard_analytics_module_name')
			),
			'heading' => $vars['heading']
		);			
	}
	
	
	function refresh()
	{
		ee('dashboard_analytics:AnalyticsData')->refreshCache();
		
		ee('CP/Alert')->makeInline('shared-form')
	      ->asSuccess()
		  ->withTitle(lang('da_cache_cleared_heading'))
		  ->addToBody(lang('da_cache_cleared_desc'))
	      ->defer();

		ee()->functions->redirect($this->settings_url);
	}
	

	function save()
	{
		if($code = ee('Request')->post('auth_code'))
		{
			$result = ee('dashboard_analytics:AnalyticsData')->exchangeAuthorizationForToken($code);
			if(empty($result['error']))
			{
				ee('CP/Alert')->makeInline('shared-form')
			      ->asSuccess()
				  ->withTitle(lang('da_token_saved_heading'))
				  ->addToBody(lang('da_token_saved_desc'))
			      ->defer();
			}
			else
			{
				ee('CP/Alert')->makeInline('shared-form')
			      ->asIssue()
				  ->withTitle(lang('da_token_error_heading'))
				  ->addToBody(lang('da_token_error_desc'))
			      ->defer();
			}
			ee()->functions->redirect($this->settings_url);
		}
		
		if($profile = ee('Request')->post('profile'))
		{
			$module = ee('Model')->get('Module')->filter('module_name', 'Dashboard_analytics')->first();
			
			$profileData = array(
				'id' => $profile,
				'name' => ee('Request')->post('profile_name_'.$profile),
				'segment' => ee('Request')->post('profile_segment_'.$profile)
			);
			ee('dashboard_analytics:AnalyticsData')->saveSettings('profile', $profileData);
			ee('dashboard_analytics:AnalyticsData')->refreshCache();

			$settingsData = array(
				'admin_groups' => array(),
				'show_realtime' => ee('Request')->post('show_realtime')
			);

			foreach($this->_getMemberGroups() as $group)
			{
				if($dashboard_groups = ee('Request')->post('dashboard_groups'))
				{
					if(in_array($group->group_id, $dashboard_groups))
					{
						$group->cp_homepage = 'custom';
						$group->cp_homepage_custom = 'addons/settings/dashboard_analytics/display';
						
						if($group->group_id != 1)
						{
							/*
								Make sure this group has access to the module	
							*/
							$assigned = $group->AssignedModules;
							if($assigned->filter('module_id', $module->module_id)->count() == 0)
							{
								$assigned[] = $module;
								$assigned->save();	
							}	
						}
					}
					else
					{
						$group->cp_homepage_custom = '';
					}
					$group->save();
				}
				
				if($admin_groups = ee('Request')->post('admin_groups'))
				{
					if(in_array($group->group_id, $admin_groups))
					{
						if($group->group_id != 1)
						{
							$settingsData['admin_groups'][] = $group->group_id;
							/*
								Make sure this group has access to the module	
							*/
							$assigned = $group->AssignedModules;
							if($assigned->filter('module_id', $module->module_id)->count() == 0)
							{
								$assigned[] = $module;
								$assigned->save();	
							}	
						}
					}
				}
			}

			ee('dashboard_analytics:AnalyticsData')->saveSettings('settings', $settingsData);

			ee('CP/Alert')->makeInline('shared-form')
		    	->asSuccess()
				->withTitle(lang('da_settings_saved'))
				->addToBody(lang('da_settings_saved_desc'))
				->defer();
			ee()->functions->redirect($this->settings_url);
		}	      
	}
	
	
	function _getMemberGroups()
	{
		return ee('Model')->get('MemberGroup')->filter('can_access_cp', 'y')->filter('site_id', ee()->config->item('site_id'))->all();
	}
	
	
	function _hasSettingsAccess()
	{
		if(ee()->session->userdata('group_id') == 1)
		{
			return true;
		}
		$settings = ee('dashboard_analytics:AnalyticsData')->getSettings();
		if(empty($settings['admin_groups']))
		{
			return false;
		}
		elseif(in_array(ee()->session->userdata('group_id'), $settings['admin_groups']))
		{
			return true;
		}				
	}
	
	
	function _setupSidebar()
	{
		$sidebar = ee('CP/Sidebar')->make();
		$globalSidebar = $sidebar->addHeader(lang('dashboard_analytics_module_name'));
		$globalSidebarLinks = $globalSidebar->addBasicList();
		$globalSidebarLinks->addItem(lang('da_profile_settings'), $this->settings_url);
		$globalSidebarLinks->addItem(lang('da_clear_cache'), ee('CP/URL', 'addons/settings/dashboard_analytics/refresh'));		
		$globalSidebarLinks->addItem(lang('da_view_dashboard'), $this->display_url);		
	}	

}