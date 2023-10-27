<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author		Rein de Vries <support@reinos.nl>
 * @link		http://ee.reinos.nl
 * @copyright 	Copyright (c) 2017 Reinos.nl Internet Media
 * @license     http://ee.reinos.nl/commercial-license
 *
 * Copyright (c) 2017. Reinos.nl Internet Media
 * All rights reserved.
 *
 * This source is commercial software. Use of this software requires a
 * site license for each domain it is used on. Use of this software or any
 * of its source code without express written permission in the form of
 * a purchased commercial or other license is prohibited.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE.
 *
 * As part of the license agreement for this software, all modifications
 * to this source must be submitted to the original author for review and
 * possible inclusion in future releases. No compensation will be provided
 * for patches, although where possible we will attribute each contribution
 * in file revision notes. Submitting such modifications constitutes
 * assignment of copyright to the original author (Rein de Vries and
 * Reinos.nl Internet Media) for such modifications. If you do not wish to assign
 * copyright to the original author, your license to  use and modify this
 * source is null and void. Use of this software constitutes your agreement
 * to this clause.
 */

require_once(PATH_THIRD.'gmaps/config.php');

class Gmaps_ACT
{
	private $EE; 

	/**
	 * Constructor
	 * 
	 * @return unknown_type
	 */
	function __construct()
	{		
		//Load the gmaps lib	
		ee()->load->library('gmaps_library', null, 'gmaps');

		//require the settings and the actions
		require PATH_THIRD.'gmaps/settings.php';
	}

	// ----------------------------------------------------------------------------------
	
	/**
	 * dispatch the actions via ajax or so.
	 * 
	 * @return unknown_type
	 */
	function init ()
	{
        //needed in some cases
        header('Access-Control-Allow-Origin: *');

		//get the method
		$method = ee()->input->get_post('method');

		//call the method if exists
		if(method_exists($this, $method))
		{
			echo $this->{$method}();
            
            die();
		}

        echo 'no_method';
		exit;
	}

	// ----------------------------------------------------------------------------------

	/**
	 * The API action
	 *
	 * @return unknown_type
	 */
	public function api()
	{
		//needed in some cases
		header('Access-Control-Allow-Origin: *');

		//no input
		if(ee()->input->post('input') == '')
		{
			echo 'no_post_value';
			die();
		}

		//no method
		if(!isset($_GET['type']) || $_GET['type'] == '')
		{
			echo 'no_method';
			die();
		}

		//input value
		$input = explode('|', ee()->input->post('input'));

		//result var
		$result = '';

		switch($_GET['type'])
		{
			case 'address':
                $result = ee()->gmaps_geocoder->geocode_address($input);
				break;
			case 'latlng':
                $result = ee()->gmaps_geocoder->geocode_latlng($input);
				break;
			case 'ip':
                $result = ee()->gmaps_geocoder->geocode_ip($input);
				break;
		}
		//echo a json object
		if($result->count() > 0)
		{
            //get the result as array
            $result = $result->map(function($data){
                return (array)$data;
            });

			echo json_encode($result);
		}
		else
		{
			echo 'no_result';
		}

		exit;
	}
}