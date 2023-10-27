<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'detour_pro/eeharbor.php';

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Detour Pro Module Install/Update File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Mike Hughes - City Zen
 * @author      Tom Jaeger - EEHarbor
 * @link		http://eeharbor.com/detour_pro
 */

class Detour_pro_upd {

	public $version = '2.1.1';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		if ( ! function_exists('ee') )
		{
			function ee()
			{
				return get_instance();
			}
		}
	}

	// ----------------------------------------------------------------

	/**
	 * Installation Method
	 *
	 * @return 	boolean 	TRUE
	 */
	public function install()
	{
		$mod_data = array(
			'module_name'			=> 'Detour_pro',
			'module_version'		=> $this->version,
			'has_cp_backend'		=> "y",
			'has_publish_fields'	=> 'n'
		);

		ee()->functions->clear_caching('db');
		ee()->db->insert('modules', $mod_data);

		// create settings table
		ee()->db->query("CREATE TABLE IF NOT EXISTS `".ee()->db->dbprefix('detour_pro_settings')."` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`site_id` int(11) NOT NULL DEFAULT '0',
			`url_detect` varchar(3) NOT NULL DEFAULT 'ee',
			`default_method` varchar(3) NOT NULL DEFAULT '301',
			`hit_counter` char(1) NOT NULL DEFAULT 'n',
			`allow_trailing_slash` tinyint(1) DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `site_id` (`site_id`));");

		ee()->load->dbforge();

		/* Check to see if detour table exists */
		$sql = 'SHOW TABLES LIKE \'%detours%\'';
		$result = ee()->db->query($sql);

		$prev_install = ($result->num_rows) ? TRUE : FALSE;

		if($prev_install)
		{
	       	// Detour ext installed, update table to include MSM

	       	$query = ee()->db->get('detours', 1)->row_array();

	       	// Double check to see if site_id already exists
	       	if(!array_key_exists('site_id', $query))
	       	{
		        $fields = array(
	            	'site_id' => array('type' => 'int', 'constraint' => '4', 'unsigned' => TRUE),
	            	'start_date' => array('type' => 'date', 'null' => TRUE),
					'end_date' => array('type' => 'date', 'null' => TRUE),
				);
				ee()->dbforge->add_column('detours', $fields);

				// Apply site id of 1 to all existing detours
				ee()->db->update('detours', array('site_id' => 1), 'detour_id > 0');
	       	}
		}
		else
		{
			// Create detour tables and keys
			$fields = array
			(
				'detour_id'	=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
				'original_url'	=> array('type' => 'varchar', 'constraint' => '250'),
				'new_url'	=> array('type' => 'varchar', 'constraint' => '250', 'null' => TRUE, 'default' => NULL),
				'start_date' => array('type' => 'date', 'null' => TRUE),
				'end_date' => array('type' => 'date', 'null' => TRUE),
				'detour_method' => array('type' => 'int', 'constraint' => '3', 'unsigned' => TRUE, 'default' => '301'),
				'site_id' => array('type' => 'int', 'constraint' => '4', 'unsigned' => TRUE)
			);

			ee()->dbforge->add_field($fields);
			ee()->dbforge->add_key('detour_id', TRUE);
			ee()->dbforge->create_table('detours');
		}

		unset($fields);

		// Create hits table


		$sql = 'SHOW TABLES LIKE \'%detours_hits%\'';
		$result = ee()->db->query($sql);

		$prev_install = ($result->num_rows) ? TRUE : FALSE;

		if(!$prev_install)
		{
			$this->_create_table_hits();
		}

		// Enable the extension to prevent redirect erros while installing.
		ee()->db->where('class', 'Detour_pro_ext');
		ee()->db->update('extensions', array('enabled'=>'y'));

		return TRUE;
	}

	// ----------------------------------------------------------------

	/**
	 * Uninstall
	 *
	 * @return 	boolean 	TRUE
	 */
	public function uninstall()
	{
		$mod_id = ee()->db->select('module_id')
								->get_where('modules', array(
									'module_name'	=> 'Detour_pro'
								))->row('module_id');

		ee()->db->where('module_id', $mod_id)
					 ->delete('module_member_groups');

		ee()->db->where('module_name', 'Detour_pro')
					 ->delete('modules');

		ee()->load->dbforge();
		ee()->dbforge->drop_table('detours');
		ee()->dbforge->drop_table('detours_hits');
		ee()->dbforge->drop_table('detour_pro_settings');

		return TRUE;
	}

	// ----------------------------------------------------------------

	/**
	 * Module Updater
	 *
	 * @return 	boolean 	TRUE
	 */
	public function update($current = '')
	{
		if(version_compare($current, '2.0.1', '<'))
		{
			$this->_update_from_2_0_0();
		}

		if(version_compare($current, '2.0.6', '<'))
		{
			$this->_update_from_2_0_5();
		}

		if(version_compare($current, '2.1.0', '<'))
		{
			$this->_update_to_2_1_0();
		}

		// If you have updates, drop 'em in here.
		return TRUE;
	}

	private function _update_from_2_0_0()
	{
		// Create the charge settings table.
		ee()->db->query("CREATE TABLE IF NOT EXISTS `".ee()->db->dbprefix('detour_pro_settings')."` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`site_id` int(11) NOT NULL DEFAULT '0',
			`url_detect` varchar(3) NOT NULL DEFAULT 'ee',
			`default_method` varchar(3) NOT NULL DEFAULT '301',
			`hit_counter` char(1) NOT NULL DEFAULT 'n',
			`allow_trailing_slash` tinyint(1) DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `site_id` (`site_id`));");

		// Copy the existing settings from the ee extension preferences.
		if(!isset($site_id) || empty($site_id)) $site_id = ee()->config->item('site_id');
		$sql = "SELECT settings FROM exp_extensions WHERE class='Detour_pro_ext'";
		$query = ee()->db->query( $sql );
		if ( $query->num_rows() == 0 ) return FALSE;

		ee()->load->helper('string');
 		$legacySettings = unserialize( $query->row('settings') );

 		$setting_data = array(
			'site_id' => $site_id,
			'url_detect' => (isset($legacySettings['url_detect']) ? $legacySettings['url_detect'] : 'ee'),
			'default_method' => (isset($legacySettings['default_method']) ? $legacySettings['default_method'] : '301'),
			'hit_counter' => (isset($legacySettings['hit_counter']) ? $legacySettings['hit_counter'] : 'n'),
			'allow_trailing_slash' => (isset($legacySettings['allow_trailing_slash']) ? $legacySettings['allow_trailing_slash'] : '0')
		);

 		// Find out if the settings exist, if not, insert them.
		ee()->db->where('site_id', ee()->config->item('site_id'));
		$exists = ee()->db->count_all_results('detour_pro_settings');

 		// Update or insert the legacy settings into the new table.
 		// The table should always exist when installed for the fist time as we inserted defaults and
 		// never exist for updates to an older install. This check is just for edge cases.
		if($exists) {
			ee()->db->where('site_id', ee()->config->item('site_id'));
			ee()->db->update('detour_pro_settings', $setting_data);
		} else {
			ee()->db->insert('detour_pro_settings', $setting_data);
		}
	}

	private function _update_from_2_0_5()
	{
		// Get the settings from the exp_extensions table and if they are a serialized object,
		// unserialize them, convert them to an array, and reserialize them.
		$settings_string = ee()->db->get_where('extensions', array('class'=>'Detour_pro_ext', 'method'=>'sessions_start'), 1)->row('settings');

		if(!empty($settings_string)) {
			// Unserialize the settings and check if they're an object.
			$settings_unserialized = unserialize($settings_string);

			if(gettype($settings_unserialized) == 'object') {
				// Force re-typing it from an object to an array.
				$settings_array = (array) $settings_unserialized;

				// Serialize the (now) array values.
				$detour_data['settings'] = serialize($settings_array);

				// Store the serialized array into the extensions table.
				ee()->db->where('class', 'Detour_pro_ext');
				ee()->db->where('method', 'sessions_start');
				ee()->db->update('extensions', $detour_data);
			}
		}
	}

	private function _update_to_2_1_0()
	{
		ee()->load->dbforge();

		// Add a column for the `allow_trailing_slash` setting.
		if(!ee()->db->field_exists('allow_trailing_slash', 'detour_pro_settings')) {
			$fields = array(
				'allow_trailing_slash' => array(
					'type'       => 'tinyint',
					'constraint' => 1,
					'unsigned'   => true,
					'default'    => 0
				)
			);

			ee()->dbforge->add_column('detour_pro_settings', $fields);
		}
	}

	/* Private Functions */

	function _create_table_hits()
	{
			$fields = array(
				'hit_id' => array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'auto_increment' => TRUE),
				'detour_id'	=> array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE),
				'hit_date'	=> array('type' => 'datetime')
			);

			ee()->dbforge->add_field($fields);
			ee()->dbforge->add_key('hit_id', TRUE);

			return (ee()->dbforge->create_table('detours_hits')) ? TRUE : FALSE;
	}

}
/* End of file upd.detour_pro.php */
/* Location: /system/expressionengine/third_party/detour_pro/upd.detour_pro.php */
