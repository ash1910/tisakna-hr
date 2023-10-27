<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Update description
 *
 * @package             Gmaps for EE3
 * @author              Rein de Vries (info@reinos.nl)
 * @copyright           Copyright (c) 2015 Rein de Vries
 * @license  			http://reinos.nl/add-ons/commercial-license
 * @link                http://reinos.nl/add-ons/gmaps
 */
 
include(PATH_THIRD.'gmaps/config.php');
 
class Gmaps_upd_294
{
	private $EE;
	private $version = '2.9.4';
	
	// ----------------------------------------------------------------

	/**
	 * Construct method
	 *
	 * @return      boolean         TRUE
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
	 * Run the update
	 *
	 * @return      boolean         TRUE
	 */
	public function run_update()
	{
		// ----------------------------------------------------------------
		// add config tables
		// ----------------------------------------------------------------
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

		// ----------------------------------------------------------------
		// insert settings data
		// ----------------------------------------------------------------
		$data = array();
		foreach($this->default_post as $key=>$val)
		{
			$data[] = array(
				'site_id' => ee()->config->item('site_id'),
				'var' => $key,
				'value'=> $val,
			);
		}
		//insert into db
		ee()->db->insert_batch(GMAPS_MAP.'_settings', $data);

		// ----------------------------------------------------------------
		// Update module settings so there is a CP 
		// ----------------------------------------------------------------
		ee()->db->where('module_name', GMAPS_CLASS);
		ee()->db->update('modules', array(
			'has_cp_backend' => 'y'
		));
		
		
		$sql = array();

		foreach ($sql as $query)
		{
			ee()->db->query($query);
		}
	}
}