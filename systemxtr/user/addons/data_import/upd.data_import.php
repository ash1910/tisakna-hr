<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
 * Data Import Module Install/Update File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		addonlabs
 * @link		http://addonlabs.com		
 */

class Data_import_upd {

	public $version = '2.0';

	private $EE;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
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
		'module_name'			=> 'Data_import',
		'module_version'		=> $this->version,
		'has_cp_backend'		=> "y",
		'has_publish_fields'	=> 'n'
		);

		$this->EE->db->insert('modules', $mod_data);

		$this->EE->load->dbforge();
		$this->_install_sql_tables();

		return TRUE;
	}

	private function _install_sql_tables()
	{
		// data_import_settings
		$this->EE->dbforge->add_field(array(
		'settings'				=> array('type' => 'text'),
		));
		$this->EE->dbforge->create_table('data_import_settings');
		// data_import_list
		$this->EE->dbforge->add_field(array(
		'import_id'			=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE, 'auto_increment' => TRUE),
		'title'					=> array('type' => 'varchar', 'constraint' => 255, 'null' => TRUE),
		'settings'				=> array('type' => 'text'),
		));
		$this->EE->dbforge->add_key('import_id', TRUE);
		$this->EE->dbforge->create_table('data_import_list');
	}
	// ----------------------------------------------------------------

	/**
	 * Uninstall
	 *
	 * @return 	boolean 	TRUE
	 */	
	public function uninstall()
	{
		$mod_id = $this->EE->db->select('module_id')
		->get_where('modules', array(
		'module_name'	=> 'Data_import'
		))->row('module_id');

		$this->EE->db->where('module_id', $mod_id)
		->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Data_import')
		->delete('modules');

		$this->EE->load->dbforge();
		$this->EE->dbforge->drop_table('data_import_settings');
		$this->EE->dbforge->drop_table('data_import_list');

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
		if(version_compare($current, '1.4', '<'))
		{
			$this->EE->db->truncate('data_import_list');
		}return TRUE;
		if(version_compare($current, '1.8', '<'))
		{
			$this->EE->dbforge->create_table('data_import_process');
			// data_import_list
			$this->EE->dbforge->add_field(array(
			'process_id'			=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE, 'auto_increment' => TRUE),
			'import_id'			=> array('type' => 'int', 'constraint' => 10, 'unsigned' => TRUE),
			'title'					=> array('type' => 'varchar', 'constraint' => 255, 'null' => TRUE),
			'settings'				=> array('type' => 'text'),
			));
			$this->EE->dbforge->add_key('import_id', TRUE);
			$this->EE->dbforge->create_table('data_import_list');
		}
		
	}

}
/* End of file upd.data_import.php */
/* Location: /system/expressionengine/third_party/data_import/upd.data_import.php */