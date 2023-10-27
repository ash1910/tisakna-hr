<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Update description
 *
 * @package             Gmaps for EE3
 * @author              Rein de Vries (info@reinos.nl)
 * @copyright           Copyright (c) 2012 Rein de Vries
 * @license  			http://reinos.nl/add-ons/commercial-license
 * @link                http://reinos.nl/add-ons/gmaps
 */
 
include(PATH_THIRD.'gmaps/config.php');
 
class Gmaps_upd_24
{
	private $EE;
	private $version = '2.4';
	
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
		
		//change the lat and lng type in the DB from float 10,6 to varchar 50
		$sql[] = "ALTER TABLE  `exp_gmaps_cache` CHANGE  `lng`  `lng` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
		$sql[] = "ALTER TABLE  `exp_gmaps_cache` CHANGE  `lat`  `lat` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
		
		foreach ($sql as $query)
		{
			ee()->db->query($query);
		}
	}
}