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
 
class Gmaps_upd_321
{
	private $EE;
	private $version = '3.2.1';
	
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
		ee()->db->insert('extensions', array(
			'class'		=> GMAPS_CLASS.'_ext',
			'method'	=> 'publisher_session_start',
			'hook'		=> 'publisher_session_start',
			'settings'	=> '',
			'priority'	=> 10,
			'version'	=> GMAPS_VERSION,
			'enabled'	=> 'y'
		));
	}
}