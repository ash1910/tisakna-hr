<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Update description
 *
 * @package             Gmaps for EE2
 * @author              Rein de Vries (info@reinos.nl)
 * @copyright           Copyright (c) 2016 Rein de Vries
 * @license  			http://reinos.nl/add-ons/commercial-license
 * @link                http://reinos.nl/add-ons/gmaps
 */
 
include(PATH_THIRD.'gmaps/config.php');
 
class Gmaps_upd_500
{
	private $EE;
	private $version = '5.0.0';
	
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
        $sql = array();

        //change the lat and lng type in the DB from float 10,6 to varchar 50
        $sql[] = 'ALTER TABLE `exp_gmaps_cache` ADD `type` VARCHAR(100) NOT NULL AFTER `result_object`;';
        $sql[] = "UPDATE exp_gmaps_cache SET type = 'geocode';";

        foreach ($sql as $query)
        {
            ee()->db->query($query);
        }
	}
}