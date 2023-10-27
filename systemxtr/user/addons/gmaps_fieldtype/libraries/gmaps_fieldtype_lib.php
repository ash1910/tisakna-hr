<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Default library helper
 *
 * @package		Module name
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl
 * @copyright 	Copyright (c) 2015 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once(PATH_THIRD.'gmaps_fieldtype/config.php');


class Gmaps_fieldtype_lib
{

	public function __construct()
	{
		ee()->load->add_package_path(PATH_THIRD . 'gmaps/');
		ee()->lang->loadfile('gmaps');
		ee()->load->library('gmaps_library', null, 'gmaps');
		ee()->load->remove_package_path(PATH_THIRD . 'gmaps/');

		ee()->load->library('gmaps_fieldtype_api');

		//get the action id
		$this->act = gmaps_helper::fetch_action_id(GMAPS_FT_CLASS, 'act_route');

		//require the default settings
		require PATH_THIRD.GMAPS_FT_MAP.'/settings.php';
	}

	// ----------------------------------------------------------------------

	/**
	 * Create the history table
	 *
	 * @access public
	 */
	public function create_history_table()
	{
		if (!ee()->db->table_exists(GMAPS_MAP.'_history') )
		{
			//load the classes
			ee()->load->dbforge();

			// add config tables
			$fields = array(
				'history_id' => array(
					'type' => 'int',
					'constraint' => 7,
					'unsigned' => TRUE,
					'null' => FALSE,
					'auto_increment' => TRUE
				),
				'entry_id' => array(
					'type' => 'int',
					'constraint' => 7,
					'unsigned' => TRUE,
					'null' => FALSE,
					'default' => 0
				),
				'field_id' => array(
					'type' => 'int',
					'constraint' => 7,
					'unsigned' => TRUE,
					'null' => FALSE,
					'default' => 0
				),
				'timestamp' => array(
					'type' => 'varchar',
					'constraint' => '200',
					'null' => FALSE,
					'default' => ''
				),
				'data' => array(
					'type' => 'text'
				),
			);

			//create the backup database
			ee()->dbforge->add_field($fields);
			ee()->dbforge->add_key('history_id', TRUE);
			ee()->dbforge->create_table(GMAPS_FT_MAP . '_history', TRUE);
		}
	}

} // END CLASS
