<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Gmaps Module File
 *
 * @package             Gmaps for EE3
 * @author              Rein de Vries (info@reinos.nl)
 * @copyright           Copyright (c) 2015 Rein de Vries
 * @license  			http://reinos.nl/add-ons/commercial-license
 * @link                http://reinos.nl/add-ons/gmaps
 */

require_once(PATH_THIRD.'gmaps_fieldtype/config.php');

class Gmaps_fieldtype {

	/**
	 * Constructor
	 *
	 */
	function __construct()
	{		
		//Load the gmaps lib
		ee()->load->library('gmaps_fieldtype_lib');
		
		//require the settings and the actions
		require PATH_THIRD.'gmaps_fieldtype/settings.php';
	}

	// ----------------------------------------------------------------------------------

	/**
	 * the Actions
	 *
	 * @return unknown_type
	 */
	public function act_route()
	{
		//needed in some cases
		header('Access-Control-Allow-Origin: *');

		// Load Library
		if (class_exists('Gmaps_fieldtype_ACT') != TRUE) include 'act.gmaps_fieldtype.php';

		$ACT = new Gmaps_fieldtype_ACT();

		$ACT->init();

		exit;
	}

}

/* End of file mod.gmaps.php */
/* Location: /system/expressionengine/third_party/gmaps/mod.gmaps.php */