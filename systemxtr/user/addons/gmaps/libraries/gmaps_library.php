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
require_once(PATH_THIRD.'gmaps/libraries/gmaps_helper.php');

//reset the session for the init cache
//this must be outside the class, because it must be rest at once
@chmod(session_save_path(), 0755);
if (session_id() == "") {session_start();}
unset($_SESSION[GMAPS_MAP.'_init']); // deze wel resetten per request, deze moet dan ook maar 1 keer aangeroepen worden
//unset($_SESSION[GMAPS_MAP.'_caller']); //niet resetten omdat dit dan per request, ook ajax, wordt gereset

class Gmaps_library
{
	public $EE;

	public $debug = array();
	public $act;

	//format
	public $address_format = '[streetName] [streetNumber], [city], [country]';

	//api keys
	public $google_maps_key;
	public $bing_maps_key;
	public $map_quest_key;
	public $tomtom_key;

	public $errors = array();

	public function __construct()
	{						
		//get the action id
		$this->act = gmaps_helper::fetch_action_id('Gmaps', 'act_route');
		
		//load logger
		ee()->load->library('logger');
		ee()->load->library('gmaps_geocoder');

		//load model
		ee()->load->model(GMAPS_MAP.'_model');

		//load the config
		ee()->load->library(GMAPS_MAP.'_settings');

		//require the default settings
		require PATH_THIRD.GMAPS_MAP.'/settings.php';
	}

	// ----------------------------------------------------------------------
	// CUSTOM FUNCTIONS
	// ----------------------------------------------------------------------


	// ----------------------------------------------------------------------------------

    /**
     * Get the keys which are presenting in an address="" or latlng="" param
     *
     * e.g. address="key:address|key:address"
     *
     * @param array $data
     * @internal param $none
     * @return array
     */
	public function parse_param_keys($data = array())
	{
		$keys = array();
		$new_data = array();

		if(!empty($data))
		{
			foreach($data as $val)
			{
				$_val = explode(':', $val);

				//set the val as key when there is only one value
				if(isset($_val[0]) && isset($_val[1]))
				{
					$keys[] = $_val[0];
					$new_data[] = $_val[1]; 
				} 
				else
				{
					$keys[] = $val;
					$new_data[] = $val; 	
				}
			}
		}

		return array('keys' => $keys, 'data' => $new_data);
	}

	// ----------------------------------------------------------------------

    /**
     * Explode and trim
     *
     * @param none
     * @param string $delimiter
     * @return array
     */
	public function explode($value, $delimiter = '|')
	{
		if($value != '')
		{
			//explode to an array
			$value = explode($delimiter, $value);
			//trim every value with array_walk
			array_walk($value, create_function('&$val', '$val = trim($val);'));
			//remove empty values
			$value = array_filter($value);
		}

		return $value;
	}

	// ----------------------------------------------------------------------

    /**
     * EDT benchmark
     * https://github.com/mithra62/ee_debug_toolbar/wiki/Benchmarks
     *
     * @param string $method
     * @param bool $start
     * @internal param $none
     */
	public function benchmark($method = '', $start = true)
	{
		if($method != '' && REQ != 'CP')
		{
			$prefix = 'gmaps_';
			$type = $start ? '_start' : '_end';
			ee()->benchmark->mark($prefix.$method.$type);
		}
	}

	// ----------------------------------------------------------------------

    /**
     * set_icon_options
     *
     * @access private
     * @param string $url
     * @param string $dir
     * @return string
     */
    public function set_icon_options($url = '', $dir = '')
    {
        //load the file helper
        ee()->load->helper('file_helper');

		//get the icons
        $this->icons = get_dir_file_info($dir);

        $return = '<select name="marker_icon">';
        $return .= '<option value="">Default</option>';

        if(!empty($this->icons))
        {
            foreach($this->icons as $val)
            {
                $return .= '<option value="'.$url.$val['name'].'">'.$val['name'].'</option>';
            }
        }

        $return .= '</select>';

        return $return;
    }

    // ----------------------------------------------------------------------

    /**
     * set_icon_options
     *
     * @access private
    */
    public function parse_errors()
    {
    	$variables = array();
    	$errors = gmaps_helper::get_log();

    	if(!empty($errors))
    	{
    		foreach($errors as $error)
    		{
    			$variables[0]['errors'][]['error'] = $error[1];
    		}
    	}

    	//there is some tagdat like {errors}{error}{/errors}
    	if(preg_match_all("/".LD.'errors'.RD."/", ee()->TMPL->tagdata, $tmp_all_matches))
    	{
    		return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $variables);
    	}		
    }

	// ----------------------------------------------------------------------

	/**
	 * Format the key array where a marker can be found on
	 *
	 * @param $address_keys
	 * @param $marker_keys
	 * @return array|string
	 * @internal param $keys
	 */
	public function parse_keys($address_keys, $marker_keys)
	{
		$marker_keys = $latlng_center = array_filter(explode('|', $marker_keys));

		if(!empty($address_keys))
		{
			foreach($address_keys as $i => $key)
			{
				if(isset($marker_keys[$i]))
				{
					$address_keys[$i] = $key.':'.$marker_keys[$i];
				}
			}

			return (implode('|', $address_keys));
		}

		return $marker_keys;
	}

    // ----------------------------------------------------------------------

    /**
     * @return mixed
     */
    public function get_default_language()
    {
        if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
            return $this->parse_default_language($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
        else
            return $this->parse_default_language(NULL);
    }

    // ----------------------------------------------------------------------

    /**
     * @param $http_accept
     * @param string $deflang
     * @return string
     */
    function parse_default_language($http_accept, $deflang = "en")
    {
        if(isset($http_accept) && strlen($http_accept) > 1)
        {
            # Split possible languages into array
            $x = explode(",",$http_accept);
            foreach ($x as $val)
            {
                #check for q-value and create associative array. No q-value means 1 by rule
                if(preg_match("/(.*);q=([0-1]{0,1}.\d{0,4})/i",$val,$matches))
                    $lang[$matches[1]] = (float)$matches[2];
                else
                    $lang[$val] = 1.0;
            }

            #return default language (highest q-value)
            $qval = 0.0;
            foreach ($lang as $key => $value)
            {
                if ($value > $qval)
                {
                    $qval = (float)$value;
                    $deflang = $key;
                }
            }
        }
        return strtolower($deflang);
    }

    // ----------------------------------------------------------------------------------

    //minify the output in the source of the browser
    public function minify_html_output($data, $need_to_parse = false)
    {
        //license check, if valid we can go futher
        if(gmaps_helper::license_check() || (!gmaps_helper::license_check() && $need_to_parse))
        {
            if(!ee()->gmaps_settings->item('dev_mode'))
            {
                return preg_replace('!\s+!smi', ' ', $data);
            }

            return $data;
        }
    }

    // ----------------------------------------------------------------------

    /**
     * Get all membergroups
     *
     * @access public
     */
    public function get_membergroups()
    {
        $return = array();

        $groups = ee('Model')->get('MemberGroup')->filter('group_id', '!=', 2)->filter('group_id', '!=', 3)->filter('group_id', '!=', 4);
        foreach($groups->all() as $group)
        {
            $return[$group->group_id] = $group->group_title;
        }

        return $return;
    }

} // END CLASS
