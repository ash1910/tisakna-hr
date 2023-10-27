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
 
class Gmaps_upd_530
{
	private $EE;
	private $version = '5.3.0';
	
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

        $sql[] = 'ALTER TABLE `exp_gmaps_cache` ADD `search_key` VARCHAR(255) NOT NULL AFTER `result_object`;';
        $sql[] = "ALTER TABLE `exp_gmaps_cache` ADD INDEX(`search_key`);";

        foreach ($sql as $query)
        {
            ee()->db->query($query);
        }
	}
}