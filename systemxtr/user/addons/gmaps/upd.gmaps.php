<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author		Rein de Vries <support@reinos.nl>
 * @link		http://ee.reinos.nl
 * @copyright 	Copyright (c) 2017 Reinos.nl Internet Media
 * @license     http://ee.reinos.nl/commercial-license
 *
 * Copyright (c) 2017. Reinos.nl Internet Media
 * All rights reserved.
 *
 * This source is commercial software. Use of this software requires a
 * site license for each domain it is used on. Use of this software or any
 * of its source code without express written permission in the form of
 * a purchased commercial or other license is prohibited.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE.
 *
 * As part of the license agreement for this software, all modifications
 * to this source must be submitted to the original author for review and
 * possible inclusion in future releases. No compensation will be provided
 * for patches, although where possible we will attribute each contribution
 * in file revision notes. Submitting such modifications constitutes
 * assignment of copyright to the original author (Rein de Vries and
 * Reinos.nl Internet Media) for such modifications. If you do not wish to assign
 * copyright to the original author, your license to  use and modify this
 * source is null and void. Use of this software constitutes your agreement
 * to this clause.
 */

include(PATH_THIRD.'gmaps/config.php');
 
class Gmaps_upd {
		
	private $EE;
	
	public $version = GMAPS_VERSION;

	/**
	 * Constructor
	 */
	public function __construct()
	{		
		//load the classes
		ee()->load->dbforge();

		//require the settings
		require PATH_THIRD.'gmaps/settings.php';
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Installation Method
	 *
	 * @return 	boolean 	TRUE
	 */
	public function install()
	{	
		if (strnatcmp(phpversion(),'5.3') <= 0) 
   	 	{ 
   	 		show_error('Gmaps require PHP 5.3 or higher.', 500, 'Oeps!');
        	return FALSE;
    	}
    	
		//set the module data
		$mod_data = array(
			'module_name'			=> GMAPS_CLASS,
			'module_version'		=> GMAPS_VERSION,
			'has_cp_backend'		=> "y",
			'has_publish_fields'	=> 'n'
		);
	
		//insert the module
		ee()->db->insert('modules', $mod_data);

		//install the extension
		$this->_register_hook('template_post_parse', 'template_post_parse');
		$this->_register_hook('ee_debug_toolbar_add_panel', 'ee_debug_toolbar_add_panel');

		//register actions
		$this->_register_action('act_route', 1);

		//create the Login backup tables
		$this->_create_tables();	

		//load the helper
		ee()->load->library('gmaps_library');
		
		//insert the settings data
		ee()->gmaps_settings->first_import_settings();	
		
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
		//delete the module
		ee()->db->where('module_name', GMAPS_CLASS);
		ee()->db->delete('modules');

		//remove databases
		ee()->dbforge->drop_table(GMAPS_MAP.'_cache');
		ee()->dbforge->drop_table(GMAPS_MAP.'_settings');
		ee()->dbforge->drop_table(GMAPS_MAP.'_logs');

		//remove actions
		ee()->db->where('class', GMAPS_CLASS);
		ee()->db->delete('actions');

		//remove the extension
		ee()->db->where('class', GMAPS_CLASS.'_ext');
		ee()->db->delete('extensions');
				
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
		//nothing to update
		if ($current == '' OR $current == $this->version)
			return FALSE;
		
		//loop through the updates and install them.
		if(!empty($this->updates))
		{
			foreach ($this->updates as $version)
			{
				//$current = str_replace('.', '', $current);
				//$version = str_replace('.', '', $version);

				if (version_compare($current, $version, '<'))
				//if ($current < $version)
				{
					$this->_init_update($version);
				}
			}

			//fix for updating a fieldtype???
			ee()->db->where('name', GMAPS_MAP);
			ee()->db->update('fieldtypes', array(
				'version' => GMAPS_VERSION
			));
		}
			
		return true;
	}
		
	// ----------------------------------------------------------------
	
	/**
	 * Add the tables for the module
	 *
	 * @return 	boolean 	TRUE
	 */	
	private function _create_tables()
	{	
		// add config tables
		$fields = array(
				'settings_id'	=> array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'auto_increment'	=> TRUE
								),
				'site_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'var'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '200',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'value'  => array(
									'type' 			=> 'text',
									'null'			=> FALSE
								),
		);
		
		//create the backup database
		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('settings_id', TRUE);
		ee()->dbforge->create_table(GMAPS_MAP.'_settings', TRUE);


		// add channel setting table
		$fields = array(
            'cache_id'	=> array(
                                'type'				=> 'int',
                                'constraint'		=> 7,
                                'unsigned'			=> TRUE,
                                'null'				=> FALSE,
                                'auto_increment'	=> TRUE,
                            ),
            'address'  => array(
                                'type' 				=> 'varchar',
                                'constraint'		=> '80',
                                'null'				=> FALSE,
                                'default'			=> ''
                            ),
            'date'  => array(
                                'type' 				=> 'varchar',
                                'constraint'		=> '35',
                                'null'				=> FALSE,
                                'default'			=> ''
                            ),
            'search_key'  => array(
                'type' 				=> 'varchar',
                'constraint'		=> '255',
                'null'				=> FALSE,
                'default'			=> ''
            ),
            'geocoder'  => array(
                                'type' 				=> 'varchar',
                                'constraint'		=> '15',
                                'null'				=> FALSE,
                                'default'			=> ''
                            ),
            'result_object'  => array(
                                'type' 				=> 'text'
                            ),
            'type'  => array(
                'type' 				=> 'varchar',
                'constraint'		=> '80',
                'null'				=> FALSE,
                'default'			=> ''
            ),
            'elevation'  => array(
                'type'			=> 'int',
                'constraint'		=> 7,
                'unsigned'		=> TRUE,
                'null'			=> FALSE,
                'default'			=> 0
            ),
        );

		//create the channel setting table
		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('cache_id', TRUE);
		ee()->dbforge->add_key('search_key');
		ee()->dbforge->create_table('gmaps_cache', TRUE);

        // add config tables
        $fields = array(
            'log_id'	=> array(
                'type'			=> 'int',
                'constraint'		=> 7,
                'unsigned'		=> TRUE,
                'null'			=> FALSE,
                'auto_increment'	=> TRUE
            ),
            'site_id'  => array(
                'type'			=> 'int',
                'constraint'		=> 7,
                'unsigned'		=> TRUE,
                'null'			=> FALSE,
                'default'			=> 0
            ),
            'time'  => array(
                'type' 			=> 'varchar',
                'constraint'		=> '200',
                'null'			=> FALSE,
                'default'			=> ''
            ),
            'message'  => array(
                'type' 			=> 'text',
                'null'			=> FALSE
            ),
        );

        //create the backup database
        ee()->dbforge->add_field($fields);
        ee()->dbforge->add_key('log_id', TRUE);
        ee()->dbforge->create_table(GMAPS_MAP.'_logs', TRUE);
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Install a hook for the extension
	 *
	 * @return 	boolean 	TRUE
	 */		
	private function _register_hook($hook, $method = NULL, $priority = 10)
	{
		if (is_null($method))
		{
			$method = $hook;
		}

		if (ee()->db->where('class', GMAPS_CLASS.'_ext')
			->where('hook', $hook)
			->count_all_results('extensions') == 0)
		{
			ee()->db->insert('extensions', array(
				'class'		=> GMAPS_CLASS.'_ext',
				'method'	=> $method,
				'hook'		=> $hook,
				'settings'	=> '',
				'priority'	=> $priority,
				'version'	=> GMAPS_VERSION,
				'enabled'	=> 'y'
			));
		}
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Create a action
	 *
	 * @return 	boolean 	TRUE
	 */
	private function _register_action($method = '', $csrf_exempt = 0)
	{
		if (ee()->db->where('class', GMAPS_CLASS)
				->where('method', $method)
				->count_all_results('actions') == 0)
		{
			ee()->db->insert('actions', array(
				'class' => GMAPS_CLASS,
				'method' => $method,
				'csrf_exempt' => $csrf_exempt
			));
		}
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Run a update from a file
	 *
	 * @return 	boolean 	TRUE
	 */	
	
	private function _init_update($version, $data = '')
	{
		// run the update file
		$class_name = 'Gmaps_upd_'.str_replace('.', '', $version);
		require_once(PATH_THIRD.'gmaps/updates/'.strtolower($class_name).'.php');
		$updater = new $class_name($data);
		return $updater->run_update();
	}
}
