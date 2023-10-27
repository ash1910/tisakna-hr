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
 
class Gmaps_upd_400
{
	private $EE;
	private $version = '4.0.0';
	
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
		//change action name for the default act route
		ee()->db->where('class', GMAPS_CLASS);
		ee()->db->where('method', 'gmaps_act');
		ee()->db->update('actions', array(
			'method' => 'act_route'
		));

		//remove the action for the gmaps_api and use the default action
		ee()->db->where('class', GMAPS_CLASS);
		ee()->db->where('method', 'gmaps_api');
		ee()->db->delete('actions');

		//remove all extensions
		ee()->db->where('class', 'Gmaps_ext');
		ee()->db->where('hook', 'sessions_start');
		ee()->db->where('hook', 'sessions_end');
		ee()->db->delete('extensions');
	}
}