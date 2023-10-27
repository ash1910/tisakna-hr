<?php
require_once __DIR__.'/vendor/autoload.php';

use Zenbu\controllers;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * =======
 *  Zenbu
 * =======
 * See more data in your control panel entry listing
 * @version 	See addon.setup.php
 * @copyright 	Nicolas Bottari - Zenbu Studio 2011-2014
 * @author 		Nicolas Bottari - Zenbu Studio
 * ------------------------------
 *
 * *** IMPORTANT NOTES ***
 * I (Nicolas Bottari and Zenbu Studio) am not responsible for any
 * damage, data loss, etc caused directly or indirectly by the use of this add-on.
 * @license		See the license documentation (text file) included with the add-on.
 *
 * Description
 * -----------
 * Zenbu is a powerful and customizable entry list manager similar to
 * ExpressionEngine's Edit Channel Entries section in the control panel.
 * Accessible from Content » Edit, Zenbu enables you to see, all on the same page:
 * Entry ID, Entry title, Entry date, Author name, Channel name, Live Look,
 * Comment count, Entry status URL Title, Assigned categories, Sticky state,
 * All (or a portion of) custom fields for the entry, etc
 *
 * @link	http://zenbustudio.com/software/zenbu/
 * @link	http://zenbustudio.com/software/docs/zenbu/
 *
 * Special thanks to Koen Veestraeten (StudioKong) for his excellent bug reporting during the initial 1.x beta
 * @link	http://twitter.com/#!/studiokong
 *
 */

class Zenbu_mcp {

	var $default_limit = 25;
	var $addon_short_name = 'zenbu';
	var $settings = array();
	var $installed_addons = array();
	var $permissions = array(
			'can_admin',
			'can_copy_profile',
			'can_access_settings',
			'edit_replace',
			'can_view_group_searches',
			'can_admin_group_searches'
		);
	var $non_ft_extra_options = array(
			"date_option_1" 	=> "date_option_1",
			"date_option_2"		=> "date_option_2",
			"view_count_1" 		=> "view_count_1",
			"view_count_2" 		=> "view_count_2",
			"view_count_3" 		=> "view_count_3",
			"view_count_4" 		=> "view_count_4",
			"livelook_option_1" => "livelook_option_1",
			"livelook_option_2" => "livelook_option_2",
			"livelook_option_3" => "livelook_option_3",
			"category_option_1"	=> "category_option_1",
		);

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
	}

	// --------------------------------------------------------------------

	/**
	 * Zenbu Clear Cache Utility
	 * An attempt at an electroshock to restart caching
	 *
	 * @access	public
	 */
	function clearcache()
	{
		$controller = new Zenbu\controllers\ZenbuController;
		$out = $controller->actionClearZenbuCache();
		return $out;
	} // END clearcache()

	// --------------------------------------------------------------------

	/**
	 * Main Page
	 *
	 * @access	public
	 */
	function index($limit = '', $perpage = '')
	{
		$controller = new Zenbu\controllers\ZenbuController;
		return $controller->actionIndex();
	} // END index()

	// --------------------------------------------------------------------


	/*
	*	function multi_edit
	*	Build view for Zenbu's multi-entry editor
	*/
	function multi_edit()
	{
		$controller = new Zenbu\controllers\Zenbu_MultiEntryController;
		return $controller->actionIndex();
	} // END multi_edit()

	// --------------------------------------------------------------------


	/**
	 * Ajax results
	 *
	 * @access	public
	 * @return	string Entry listing table, AJAX response
	 */
	function ajax_results($output = "")
	{
		$output = $this->index();

		$this->EE->output->send_ajax_response($output);

	} // END ajax_results()

	// --------------------------------------------------------------------


	/**
	 * Ajax search saving
	 *
	 * @access	public
	 * @return	listing of saved searches
	 */
	function save_searches()
	{
		$controller = new Zenbu\controllers\Zenbu_SavedSearchesController;
		return $controller->actionSave();
	} // END save_search()

	// --------------------------------------------------------------------


	/**
	 * Ajax temp search saving
	 *
	 * @access	public
	 * @return	listing of saved searches
	 */
	public function cache_temp_filters()
	{
		$controller = new Zenbu\controllers\Zenbu_SavedSearchesController;
		echo $controller->actionCacheTempFilters();
		exit();
	} // END temp_save_search()

	// --------------------------------------------------------------------


	/**
	 * Manage saved searches
	 */
	function searches() { return $this->manage_searches(); }
	function saved_searches() { return $this->manage_searches(); }
	function manage_searches()
	{
		$controller = new Zenbu\controllers\Zenbu_SavedSearchesController;
		return $controller->actionIndex();
	} // END manage_searches()

	// --------------------------------------------------------------------

	function fetch_search_filters()
	{
		@header('Content-Type: application/json');
		$controller = new Zenbu\controllers\Zenbu_SavedSearchesController;
		echo $controller->actionFetchFilters();
		exit();
	}


	function fetch_cached_temp_filters()
	{
		@header('Content-Type: application/json');
		$controller = new Zenbu\controllers\Zenbu_SavedSearchesController;
		echo $controller->actionFetchCachedTempFilters();
		exit();
	}

	function fetch_saved_searches()
	{
		@header('Content-Type: application/json');
		$controller = new Zenbu\controllers\Zenbu_SavedSearchesController;
		echo $controller->actionFetchSavedSearches();
		exit();
	}

	/**
	 * Display settings
	 *
	 * @access	public
	 */
	function display_settings() { return $this->settings(); }
	function settings()
	{
		$controller = new Zenbu\controllers\Zenbu_DisplaySettingsController;
		return $controller->actionIndex();
	} // END function settings

	function save_settings()
	{
		$controller = new Zenbu\controllers\Zenbu_DisplaySettingsController;
		return $controller->actionSave();
	}

	/**
	 * Member access settings
	 *
	 * @access	public
	 */
	public function permissions() {	return $this->settings_admin(); }
	public function settings_admin()
	{
		$controller = new Zenbu\controllers\Zenbu_PermissionsController;
		return $controller->actionIndex();
	} // END function settings_admin

	function save_permissions()
	{
		$controller = new Zenbu\controllers\Zenbu_PermissionsController;
		return $controller->actionSave();
	}

	function saveSidebarState()
	{
		$controller = new Zenbu\controllers\ZenbuController;
		return $controller->actionSaveSidebarState();
	}

}
// END CLASS

/* End of file mcp.download.php */
/* Location: ./system/expressionengine/third_party/modules/zenbu/mcp.zenbu.php */