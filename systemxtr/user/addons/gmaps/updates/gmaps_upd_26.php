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
 
class Gmaps_upd_26
{
	private $EE;
	private $version = '2.6';
	
	/**
	 * Construct method
	 *
	 * @return      boolean         TRUE
	 */
	public function __construct()
	{		
		//load the classes
		ee()->load->dbforge();
	}
	
	/**
	 * Run the update
	 *
	 * @return      boolean         TRUE
	 */
	public function run_update()
	{
		$sql = array();
		
		//Add a new extension sessions_start
		$sql[] = "INSERT INTO  exp_extensions (class, method, hook, settings, priority, version, enabled) VALUES ('Gmaps_ext', 'sessions_start', 'sessions_start', '', '10', '2.6', 'y');";
		
		foreach ($sql as $query)
		{
			ee()->db->query($query);
		}
	}
}