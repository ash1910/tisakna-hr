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
 
class Gmaps_upd_213
{
	private $EE;
	private $version = '2.13';
	
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
		//add new hook ee_debug_toolbar_add_panel
		ee()->db->insert('extensions', array(
			'class'		=> 'Gmaps_ext',
			'hook'		=> 'ee_debug_toolbar_add_panel',
			'method'	=> 'ee_debug_toolbar_add_panel',
			'settings'	=> serialize(array()),
			'priority'	=> 10,
			'version'	=> $this->version,
			'enabled'	=> 'y'
		));
	}
}