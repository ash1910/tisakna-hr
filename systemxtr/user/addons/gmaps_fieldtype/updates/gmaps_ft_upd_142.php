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
 
include(PATH_THIRD.'gmaps_fieldtype/config.php');
 
class Gmaps_ft_upd_142
{
	private $EE;
	private $version = '1.4.2';
	
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
		//get latest version
		$version = ee()->db->select('version, settings')->from('fieldtypes')->where('name', 'gmaps_fieldtype')->get()->row();

		$version->settings = unserialize(base64_decode($version->settings));
		$version->settings['license'] = '';

		//update the settings
		ee()->db->where('name', 'gmaps_fieldtype');
		ee()->db->update('fieldtypes', array(
			'settings' => base64_encode(serialize($version->settings)),
			'has_global_settings' =>  'y'
		));
	}
}