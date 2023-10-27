<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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

class Gmaps {

	private $libraries = array();
	private $default_js_array;
	private $default_zoom = 15;
	
	public $return_data = '';

	/**
	 * Constructor
	 *
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
	 * {exp:gmaps:init}
	 * 
	 * Init function to place all files
	 * 
	 * 
	 * @return unknown_type
	 */
	function init()
	{
		//license check
		if(!gmaps_helper::license_check())
		{
			gmaps_helper::log('Your Gmaps license key appear to be invalid. Please fill in the license in the Gmaps CP.', 1, true);
		}

		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, true);

		//set session cache
        gmaps_helper::set_cache(GMAPS_MAP.'_init', true);
        gmaps_helper::set_cache(GMAPS_MAP.'_caller', 0);

		//set lang
		$this->lang = str_replace('_', '-',gmaps_helper::get_from_tagdata('lang', ee()->gmaps->get_default_language()));
		gmaps_helper::set_ee_cache('lang', $this->lang);

		//catch output JS
		$catch_output_js = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('catch_output_js', 'no'));
		gmaps_helper::set_ee_cache('catch_output_js', $catch_output_js);

		//Load jQuery if its not here
		$load_jquery = gmaps_helper::check_yes(ee()->TMPL->fetch_param('load_jquery', 'yes'));
		if($load_jquery)
		{
			$this->return_data .= '
				<script type="text/javascript">
					if(!window.jQuery || window.jQuery === undefined){
					   document.write(unescape("%3Cscript src=\'' . ee()->gmaps_settings->get_setting('theme_url') . 'js/jquery.js\' type=\'text/javascript\'%3E%3C/script%3E"));
					}
				</script>
			';
		}

		/*
		The libraries are always loaded, not good. 
		@todo load based on the need
		 */
		$this->_add_library('weather', 'true');
		$this->_add_library('panoramio', 'true');
		$this->_add_library('places', 'true');
		$this->_add_library('geometry', 'true');
		$this->_add_library('drawing', 'true');
		
		//create library string
		if(!empty($this->libraries))
		{
			$this->libraries = '&libraries='.implode(',', $this->libraries);
		} 
		else
		{
			$this->libraries = '';
		}

        //google api url
		$google_api_url = 'https://maps.googleapis.com/maps/api/js?key='.ee()->gmaps_settings->item('google_api_key_client').'&v=3'.$this->libraries.'&language='.$this->lang;

		if (gmaps_helper::is_ssl() == TRUE)
		{
			$google_api_url = str_replace('http://', 'https://', $google_api_url);
		}

        $load_google_maps_js = gmaps_helper::check_yes(ee()->TMPL->fetch_param('load_google_maps_js', 'yes'));
        if($load_google_maps_js)
        {
            //Google api
            $this->return_data .= '<script type="text/javascript" src="'.$google_api_url.'"></script>';
        }

		$this->return_data .= '
			<script type="text/javascript">
				var EE_GMAPS = {
					version : "'.GMAPS_VERSION.'",
					author_url : "http://reinos.nl/add-ons/gmaps",
					base_path : "'.ee()->gmaps_settings->item('site_url').'",
					act_path : "'.ee()->gmaps_settings->item('site_url').'?ACT='.gmaps_helper::fetch_action_id('Gmaps', 'act_route').'",
					api_path : "'.ee()->gmaps_settings->item('site_url').'?ACT='.gmaps_helper::fetch_action_id('Gmaps', 'act_route').'&method=api",
					theme_path : "'.ee()->gmaps_settings->item('theme_url').'"
				}
			</script>
		';

		//minify css on DEV only
		if ( ee()->gmaps_settings->item('dev_mode'))
		{
			//add js
			$this->return_data .= '
				<script type="text/javascript" src="' . ee()->gmaps_settings->get_setting('theme_url') . 'js/gmaps.js" ></script>
			';
		}
		else
		{
			//add js
			$this->return_data .= '
				<script type="text/javascript" src="' . ee()->gmaps_settings->get_setting('theme_url') . 'js/gmaps.min.js" ></script>
			';
		}

		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, false);

		if($catch_output_js)
		{
			gmaps_helper::set_ee_cache('init_js', $this->return_data);
		}
		else
		{
			return ee()->gmaps->minify_html_output($this->return_data, true);
		}
	}

	// ----------------------------------------------------------------------------------

    /**
     * this init function is called by almost every method
     * this method add all the default handlers and logic
     *
     * @param string $method
     * @param bool $get_other_pars
     * @return unknown_type
     */
	private function _init($method = '', $get_other_pars = true)
	{
		//errors in the {exp:gmaps:init}
		if(gmaps_helper::log_has_error())
		{
			//{errors}{error}{/errors}
			return ee()->gmaps->parse_errors();
		}
		else
		{
			if(preg_match_all("/".LD."errors".RD."(.*?)".LD."\/errors".RD."/s", ee()->TMPL->tagdata, $all_matches))
			{
				ee()->TMPL->tagdata = str_replace($all_matches[0][0], '', ee()->TMPL->tagdata);
			}
		}

		//report stats
		gmaps_helper::stats();

		//EDT Benchmark
		ee()->gmaps->benchmark($method, true);

		//set the caller times
		$this->_add_call_timer();

		//set the selector
		$this->div_id = 'ee_gmap_'.$this->caller_id;
		$this->div_class = gmaps_helper::get_from_tagdata('div_class', '').' ee_gmap ee_gmaps ee_gmap_'.$this->caller_id.' ee_gmaps_'.$this->caller_id;
		$this->selector = '#'.$this->div_id;

		//catch output JS
		$this->catch_output_js = gmaps_helper::get_ee_cache('catch_output_js');

		//cache param
		$this->cache_time = ee()->gmaps->cache_time = (gmaps_helper::get_from_tagdata('cache_time', $this->cache_time)) * 60 * 60;
		//$this->cache_time = $this->cache_time * 60 *60;

		//width and height
		$this->width = gmaps_helper::get_from_tagdata('width', 700);
		$this->height = gmaps_helper::get_from_tagdata('height', 400);

		//get the overlay
		$this->overlay_html = gmaps_helper::get_from_tagdata('overlay:html', '');
		$this->overlay_position = gmaps_helper::get_from_tagdata('overlay:position', 'left');

		//new/old style
		$this->enable_new_style = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('enable_new_style', 'yes'), true);

		//set the default location based on the current location
		$this->focus_current_location = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('focus_current_location', 'no'), true);

		//set the default vars
		$this->map_type = gmaps_helper::get_from_tagdata('map_type', 'roadmap');
		$this->map_types = gmaps_helper::build_js_array(gmaps_helper::get_from_tagdata('map_types', 'hybrid|roadmap|satellite|terrain'), true);

		//others
		//(https://developers.google.com/maps/documentation/javascript/controls?hl=nl#ControlPositioning)
		$this->scroll_wheel = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('scroll_wheel', 'yes'), true);
		$this->zoom_control = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('zoom_control', 'yes'), true);
		//$this->zoom_control_style = gmaps_helper::get_from_tagdata('zoom_control_style', 'DEFAULT'); //large, small removed in 3.22 (https://developers.google.com/maps/articles/v322-controls-diff#zoom-control)
		$this->zoom_control_position = gmaps_helper::get_from_tagdata('zoom_control_position', 'RIGHT_BOTTOM');
		$this->map_type_control = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('map_type_control', 'yes'), true);
		$this->map_type_control_style = gmaps_helper::get_from_tagdata('map_type_control_style', 'DEFAULT');
		$this->map_type_control_position = gmaps_helper::get_from_tagdata('map_type_control_position', 'TOP_LEFT');
		$this->scale_control = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('scale_control', 'yes'), true);
		$this->street_view_control = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('street_view_control', 'yes'), true);
		$this->street_view_control_position = gmaps_helper::get_from_tagdata('street_view_control_position', 'RIGHT_BOTTOM');
		$this->hidden_div = gmaps_helper::get_from_tagdata('hidden_div', '');

		//get the styled map settings and set it correct
		$this->styled_map = $this->_set_styled_map();

		//layers
		$this->show_traffic = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('show_traffic', 'no'), true) ;
		$this->show_transit = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('show_transit', 'no'), true) ;
		$this->show_bicycling = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('show_bicycling', 'no'), true) ;
		$this->show_panoramio = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('show_panoramio', 'no'), true) ;
		$this->panoramio_tag = gmaps_helper::get_from_tagdata('panoramio_tag', '');

        //cooperative gesture handling.
        //https://developers.google.com/maps/documentation/javascript/interaction
        $this->gesture_handling = gmaps_helper::get_from_tagdata('gesture_handling', 'auto');

		//format for the adress object
		//$this->address_format = ee()->gmaps->address_format = gmaps_helper::get_from_tagdata('address_format', '[streetName] [streetNumber], [city], [country]');

		//build the default array for the js functions
		$this->default_js_array = '
			selector : "'.$this->selector.'",
			map_type : "'.$this->map_type.'",
			map_types : '.$this->map_types.',
			width : "'.$this->width.'",
			height : "'.$this->height.'",
			scroll_wheel : '.$this->scroll_wheel.',
			zoom_control : '.$this->zoom_control.',
			zoom_control_position : "'.strtoupper($this->zoom_control_position).'",
			map_type_control : '.$this->map_type_control.',
			map_type_control_style : "'.strtoupper($this->map_type_control_style).'",
			map_type_control_position : "'.strtoupper($this->map_type_control_position).'",
			scale_control : '.$this->scale_control.',
			street_view_control : '.$this->street_view_control.',
			street_view_control_position : "'.strtoupper($this->street_view_control_position).'",
			styled_map : '.$this->styled_map.',
			show_traffic : '.$this->show_traffic.',
			show_transit : '.$this->show_transit.',
			show_bicycling : '.$this->show_bicycling.',
			show_panoramio : '.$this->show_panoramio.',
			panoramio_tag : "'.$this->panoramio_tag.'",
			hidden_div : "'.$this->hidden_div.'",
			enable_new_style : '.$this->enable_new_style.',
			overlay_html : "'.$this->overlay_html.'",
			overlay_position : "'.$this->overlay_position.'",
			focus_current_location : '.$this->focus_current_location.',
			gesture_handling : "'.$this->gesture_handling.'"
		';

		/* -------------------------------------------
		/* 'gmaps_init' hook.
		/*  - Added: 2.3
		*/
		if (ee()->extensions->active_hook('gmaps_init') === TRUE)
		{
			ee()->extensions->call('gmaps_init', '');
		}
		// -------------------------------------------
	}

    // ----------------------------------------------------------------------------------

    /**
     * {exp:gmaps:map}
     *
     * This is the map method according to http://reinos.nl/add-ons/gmaps/docs#map
     *
     * @return unknown_type
     */
    function map()
    {
        //call the init function to init some default values
        $error = $this->_init(__FUNCTION__);
        if(gmaps_helper::log_has_error())
        {
            return $error;
        }

        //set the specific vars
        $address_center = gmaps_helper::get_from_tagdata('center:address');
        $latlng_center = gmaps_helper::get_from_tagdata('center:latlng');
        $zoom = gmaps_helper::get_from_tagdata('zoom', $this->default_zoom);
        $max_zoom = gmaps_helper::get_from_tagdata('zoom:max', 'null');
        $zoom_override = $zoom == $this->default_zoom ? 'false' : 'true';
        $static = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('static', 'no'), true);
        $fit_map = gmaps_helper::get_from_tagdata('fit_map', '');

        //cluster
        $show_marker_cluster = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('show_marker_cluster', 'no'), true);
        $marker_cluster_grid_size = gmaps_helper::get_from_tagdata('marker_cluster:grid_size', '60');
        $marker_cluster_image_path = gmaps_helper::get_from_tagdata('marker_cluster:image_path', ee()->gmaps_settings->item('theme_url').'images/cluster/m');
        $marker_cluster_style = $this->_set_cluster_style('marker_cluster_style');

        //get the center address
        if($address_center != '')
        {
            $center = ee()->gmaps_geocoder->geocode_address(array($address_center));
            $center = ee()->gmaps_geocoder->get_field('latlng', $center, 'No result found for {exp:gmaps:map center:address="'.$address_center.'"}');
        }
        else
        {
            $center = $latlng_center;
        }

        //load the api
        ee()->load->library('api/gmaps_api');

        //add the map id to the tag
        ee()->TMPL->tagdata = ee()->gmaps_api->add_map_id(ee()->TMPL->tagdata, $this->caller_id);

        //early parse some tags, what mostly are just some PHP instead of JS
        //JS is always parsed at the end
        ee()->TMPL->tagdata = ee()->gmaps_api->parse(ee()->TMPL->tagdata, true);

        //return a div
        $this->return_data .= '<div data-gmaps-number="'.$this->caller_id.'" class="'.$this->div_class.'" id="'.$this->div_id.'"></div>';

        //return the js
        $this->return_data .= $this->_output_js('
			jQuery(window).ready(function(){
				EE_GMAPS.setMap({
					zoom : '.$zoom.',
					zoom_override : '.$zoom_override.',
					max_zoom : '.$max_zoom.',
					center : "'.base64_encode($center).'",
					static : '.$static.',
                    show_marker_cluster : '.$show_marker_cluster.',
                    marker_cluster_grid_size : '.$marker_cluster_grid_size.',
                    marker_cluster_style : '.$marker_cluster_style.',
                    marker_cluster_image_path : "'.$marker_cluster_image_path.'",
					'.$this->default_js_array.'
				});	
			});
		').
        '{!-- gmaps:tags_found --}'.
        ee()->TMPL->tagdata;

        if($fit_map != '')
        {
            $this->return_data .= '{gmaps:fit_map map_id="'.$this->caller_id.'" type="'.$fit_map.'"}';
        }

        /* -------------------------------------------
        /* 'gmaps_map_end' hook.
        /*  - Added: 5.0.0
        */
        if (ee()->extensions->active_hook('gmaps_map_end') === TRUE)
        {
            ee()->extensions->call('gmaps_map_end', '');
        }
        // -------------------------------------------

        //parse {map_id}
        $this->return_data = ee()->TMPL->parse_variables($this->return_data, array(array('map_id'=>$this->caller_id)));

        //EDT Benchmark
        ee()->gmaps->benchmark(__FUNCTION__, false);

        //return the gmaps
        return ee()->gmaps->minify_html_output($this->return_data);
    }

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:street_view_panorama}
	 * 
	 * This is the street_view_panorama method according to http://reinos.nl/add-ons/gmaps/docs#Street_view_panorama
	 * 
	 * @return unknown_type
	 */
	function street_view_panorama()
	{
		//call the init function to init some default values
		$error = $this->_init(__FUNCTION__);
		if(gmaps_helper::log_has_error())
		{
			return $error;
		}
		
		//set the specific vars
		$address = gmaps_helper::get_from_tagdata('address');
		$latlng = gmaps_helper::get_from_tagdata('latlng');
		//$address = gmaps_helper::get_from_tagdata('address', '');
		//$latlng = gmaps_helper::get_from_tagdata('latlng', '');
		
		$pov_heading = gmaps_helper::get_from_tagdata('pov:heading', 0);
		$pov_pitch = gmaps_helper::get_from_tagdata('pov:pitch', 0);
		$pov_zoom = gmaps_helper::get_from_tagdata('pov:zoom', 0);
		$address_control = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('address_control', 'yes'), true);
		$click_to_go = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('click_to_go', 'yes'), true);
		$disable_double_click_zoom = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('disable_double_click_zoom', 'yes'), true);
		$enable_close_button = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('enable_close_button', 'no'), true);
		$image_date_control = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('image_date_control', 'yes'), true);
		$links_control = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('links_control', 'yes'), true);
		$visible = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('visible', 'yes'), true);
		$checkaround = gmaps_helper::get_from_tagdata('checkaround', '50');
		$pancontrol = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('pan_control', 'yes'), true);

		//if address empty return a error.
		// fill in by the user
		if($address == '' && $latlng == '') 
		{
			gmaps_helper::log('You forgot to fill in an address or latlng', 1, true);
			//ee()->gmaps->errors[] = 'You forgot to fill in an address or latlng';
			return ee()->gmaps->parse_errors();	
		}

		//get the adresses via the geocoder
		if($address != '')
		{
            $result = ee()->gmaps_geocoder->geocode_address(array($address));
            $latlng = ee()->gmaps_geocoder->get_field('latlng', $result, 'No result found for address="'.$address.'"');
            $address = ee()->gmaps_geocoder->get_field('address', $result, 'No result found for address="'.$address.'"');
		}

		//if address empty return a error.
		// after geocoding
		if($latlng == false || $latlng == '')
		{
			gmaps_helper::log('No result founded for {exp:gmaps:street_view_panorama}', 1, true);
			//ee()->gmaps->errors[] = 'No result founded';
			return ee()->gmaps->parse_errors().ee()->TMPL->no_results();	
		}

		//return a div
		$this->return_data .= '<div data-gmaps-number="'.$this->caller_id.'" class="'.$this->div_class.'" id="'.$this->div_id.'"></div>';

		//return the js 
		$this->return_data .= $this->_output_js('
			jQuery(window).ready(function(){
				EE_GMAPS.setStreetViewPanorama({
					address : "'.base64_encode($address).'",
					latlng : "'.base64_encode($latlng).'",
					address_control : '.$address_control.',
					click_to_go : '.$click_to_go.',
					disable_double_click_zoom : '.$disable_double_click_zoom.',
					enable_close_button : '.$enable_close_button.',
					image_date_control : '.$image_date_control.',
					links_control : '.$links_control.',
					visible : '.$visible.',
					checkaround : '.$checkaround.',
					pan_control : '.$pancontrol.',
					pov : {
						heading : '.$pov_heading.',
						pitch : '.$pov_pitch.',
						zoom : '.$pov_zoom.'
					},
					'.$this->default_js_array.'					
				});
			});
		');

		/* -------------------------------------------
		/* 'gmaps_geolocation_end' hook.
		/*  - Added: 2.5
		*/
		if (ee()->extensions->active_hook('gmaps_panorama_end') === TRUE)
		{
			ee()->extensions->call('gmaps_panorama_end', '');
		}
		// -------------------------------------------
		
		//parse {map_id}
		$this->return_data .= ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array(array('map_id'=>$this->caller_id)));

		//EDT Benchmark
		ee()->gmaps->benchmark(__FUNCTION__, false);

		//return the gmaps
		return ee()->gmaps->minify_html_output($this->return_data);
	}

    // ----------------------------------------------------------------------------------

    /**
     * {exp:gmaps:geocoder}
     *
     * This is the geocoder method according to http://reinos.nl/add-ons/gmaps/docs#Geocoder
     *
     * @return unknown_type
     */
    function geocoder()
    {
        //set the specific vars
        $address = gmaps_helper::remove_empty_array_values(explode('|', gmaps_helper::get_from_tagdata('address')));
        $latlng = gmaps_helper::remove_empty_array_values(explode('|', gmaps_helper::get_from_tagdata('latlng')));
        $ip = gmaps_helper::remove_empty_array_values(explode('|', gmaps_helper::get_from_tagdata('ip')));
        //$address = remove_empty_array_values(explode('|', gmaps_helper::get_from_tagdata('address', '')));
        //$latlng = remove_empty_array_values(explode('|', gmaps_helper::get_from_tagdata('latlng', '')));
        //$ip = remove_empty_array_values(explode('|', gmaps_helper::get_from_tagdata('ip', '')));

        //define var
        $variables = array();

        //switch
        if(!empty($address))
        {
            $result = ee()->gmaps_geocoder->geocode_address($address);
        }
        else if(!empty($latlng))
        {
            $result = ee()->gmaps_geocoder->geocode_latlng($latlng);
        }
        else if(!empty($ip))
        {
            $result = ee()->gmaps_geocoder->geocode_ip($ip);
        }

        /* -------------------------------------------
        /* 'gmaps_geocoder_end' hook.
        /*  - Added: 2.3
        */
        if (ee()->extensions->active_hook('gmaps_geocoder_end') === TRUE)
        {
            ee()->extensions->call('gmaps_geocoder_end', '');
        }
        // -------------------------------------------

        //no result?
        if($result->count() == 0)
        {
            return false;
        }

        //get the result as array
        $result = $result->map(function($data){
            return (array)$data;
        });

        //set the var to a new var
        $variables = array(
            array('result'=> $result)
        );

        //EDT Benchmark
        ee()->gmaps->benchmark('geocoder', false);

        //return the gmaps
        return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $variables);
    }

	// ----------------------------------------------------------------------------------

	/**
	 * {exp:gmaps:calculate_distance}
	 *
	 * This is the calculate_distance method according to http://reinos.nl/add-ons/gmaps/docs#calculate_distance
	 *
	 * @return unknown_type
	 */
	function calculate_distance()
	{
		//call the init function to init some default values
		$this->_init('calculate_distance');

		//set the specific vars
		$address_from = gmaps_helper::get_from_tagdata('address_from');
		$address_to = gmaps_helper::get_from_tagdata('address_to');
		$latlng_from = gmaps_helper::get_from_tagdata('latlng_from');
		$latlng_to = gmaps_helper::get_from_tagdata('latlng_to');

		$direct = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('direct', 'yes'));

		//switch
		if(!empty($address_from) && !empty($address_to))
		{
            $result1 = ee()->gmaps_geocoder->geocode_address(array($address_from));
            $result2 = ee()->gmaps_geocoder->geocode_address(array($address_to));

		}
		else if(!empty($latlng_from) && !empty($latlng_to))
		{
			$result1 = ee()->gmaps_geocoder->geocode_latlng(array($latlng_from));
			$result2 = ee()->gmaps_geocoder->geocode_latlng(array($latlng_to));
		}

		//if address empty return a error.
		// after geocoding
		if($result1->count() == 0 || $result2->count() == 0)
		{
			gmaps_helper::log('No result founded {exp:gmaps:calculate_distance}', 1, true);
			//ee()->gmaps->errors[] = 'No result founded';
			return ee()->gmaps->parse_errors().ee()->TMPL->no_results();
		}

		//set the result
        $result1 = $result1->first();
        $result2 = $result2->first();
        $latlng_from = $result1->latlng;
        $latlng_to = $result2->latlng;
        $address_from = $result1->address;
        $address_to = $result2->address;

		/* -------------------------------------------
		/* 'gmaps_geocoder_end' hook.
		/*  - Added: 2.3
		*/
		if (ee()->extensions->active_hook('gmaps_calculate_distance_end') === TRUE)
		{
			ee()->extensions->call('gmaps_calculate_distance_end', '');
		}
		// -------------------------------------------

		$result = array();

		//calculate the distance
		if($direct)
		{
			$result['m'] = $this->haversine_great_circle_distance($result1->lat, $result1->lng, $result2->lat, $result2->lng);
			$result['k'] = $result['m'] / 1000;
		}
		else
		{
			$result = $this->get_driving_information($latlng_from, $latlng_to);

			//return false?
			if(!$result)
			{
				gmaps_helper::log('No result founded', 1, true);
				return ee()->gmaps->parse_errors().ee()->TMPL->no_results();
			}

			//convert to meters
			else
			{
				$result = array(
					'k' => $result['distance'],
					'm' => $result['distance'] * 1000
				);
			}
		}

		//set the var to a new var
		$variables = array($result);

		//EDT Benchmark
		ee()->gmaps->benchmark('calculate_distance', false);

		//return the gmaps
		return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $variables);
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
		if (class_exists('Gmaps_ACT') != TRUE) include 'act.gmaps.php';

		$ACT = new Gmaps_ACT();

		$ACT->init();

		exit;
	}

	// ----------------------------------------------------------------------------------

	// Output JS tags
	public function output_js()
	{
	    return '{gmaps:output_js}';
	}

    // ----------------------------------------------------------------------------------

    /**
     * {exp:gmaps:add_marker}
     *
     * @return unknown_type
     */
    function add_marker()
    {
        ee()->load->library('gmaps_legacy_api');
        return ee()->gmaps->minify_html_output(ee()->gmaps_legacy_api->create_marker(), true);
    }

	// ----------------------------------------------------------------------------------
	// Prvate functions
	// ----------------------------------------------------------------------------------
	
	//add a library
	private function _add_library($library_name, $check)
	{
		if($check == "true")
		{
			$this->libraries[] = $library_name;
		}
	}

	// ----------------------------------------------------------------------------------

	//add call timer
	private function _add_call_timer()
	{
		//caller ID
       /* if(!isset(ee()->session->cache[GMAPS_MAP]['caller']))
        {
            ee()->session->cache[GMAPS_MAP]['caller'] = 1;
        }
        else
        {
        	ee()->session->cache[GMAPS_MAP]['caller'] = ee()->session->cache[GMAPS_MAP]['caller'] + 1;
        }

        //prefix
        $prefix = gmaps_helper::get_from_tagdata('cache_prefix', '');	
        $suffix = gmaps_helper::get_from_tagdata('cache_suffix', '');	
		
		//set caller ID
        $this->caller_id = $prefix.ee()->session->cache[GMAPS_MAP]['caller'].$suffix;	
	
        */
        gmaps_helper::set_cache(GMAPS_MAP.'_caller', (gmaps_helper::get_cache(GMAPS_MAP.'_caller') + 1));

		//prefix
        $prefix = gmaps_helper::get_from_tagdata('cache_prefix', '');	
        $suffix = gmaps_helper::get_from_tagdata('cache_suffix', '');	

		//set caller ID
        $this->caller_id = $prefix.gmaps_helper::get_cache(GMAPS_MAP.'_caller').$suffix;

		//set the time of calling inthe session
		/*if(ee()->session->userdata('gmaps_call_times') == '')
		{
			ee()->session->userdata['gmaps_call_times'] = 1;
		}
		else
		{
			ee()->session->userdata['gmaps_call_times'] = ee()->session->userdata('gmaps_call_times') + 1;
		}*/

		//$this->caller_id = ee()->session->userdata('gmaps_call_times');	
		
		//and if the add_base_files="yes" than set this to 1 to avoid problems when the first is not loaded
		/*if($this->add_base_files)
		{
			$this->caller_id = 1;
		}
		else
		{
			$this->caller_id = ee()->session->userdata('gmaps_call_times');	
		}	*/
	}

	// ----------------------------------------------------------------------------------

	//serach array with regex
	private function preg_array_key_exists($pattern, $array) 
	{
		if(!is_array($array))
		{
			return false;
		}
		$keys = array_keys($array);    
		return preg_grep($pattern,$keys);
	}

	// ----------------------------------------------------------------------------------

	/**
	 * is_assoc
	 * @param  [type]  $array 
	 * @return boolean        
	 */
	function is_assoc($array) { 
        foreach ( array_keys ( $array ) as $k => $v ) 
        { 
            if ($k !== $v) 
                return true; 
        } 
        return false; 
    } 

    // ----------------------------------------------------------------------------------

    /**
     * convert_array_to_js
     * @param  [type] $data
     * @return [type]       
     */
    function convert_array_to_js($data) 
    { 
        if (is_null($data)) return 'null'; 
        if (is_string($data)) return '"' . $data . '"'; 
        if (self::is_assoc($data)) 
        { 
            $a=array(); 
            foreach ($data as $key => $val ) 
                $a[]='"' . $key . '" :' .self::convert_array_to_js($val); 
            return "{" . implode ( ', ', $a ) . "}"; 
        } 
        if (is_array($data)) 
        { 
            $a=array(); 
            foreach ($data as $val ) 
                $a[]=self::convert_array_to_js($val); 
            return "[" . implode ( ', ', $a ) . "]"; 
        } 
        return $data; 
    } 

    // ----------------------------------------------------------------------------------

    //set cluster settings
    private function _set_cluster_style($prefix = 'marker:cluster_style')
    {
    	$_fields = $this->preg_array_key_exists('/'.$prefix.':/',ee()->TMPL->tagparams);
    	$fields = array();
		$field_options = array();
		if(!empty($_fields))
		{
			// Create a tussen array which can be threated for the js converting
			foreach($_fields as $val)
			{
				$_val = explode(':', $val);

				$param = gmaps_helper::get_from_tagdata($val, '');

				$fields[$_val[1]][$_val[2]] = $param;
				
			}

		    $i = 0;
			foreach($fields as $key=>$val)
			{
				$field_options[$i] = array();

				//size
				if(isset($val['size']))
				{
					$size = explode(',', $val['size']);
					$width = isset($size[0]) ? $size[0] : '';
					$height = isset($size[1]) ? $size[1] : '';
				}
				else
				{
					$width = '';
					$height = '';
				}

				$field_options[$i] = array(
						'url' 			=> isset($val['url']) ? $val['url'] : '',
						'width' 		=> $width,
						'height' 		=> $height,
						'textColor' 	=> isset($val['color']) ? $val['color'] : '',
						'anchor'		=> isset($val['anchor']) ? '['.$val['anchor'].']' : '[]',
						'textSize' 		=> isset($val['text_size']) ? $val['text_size'] : '',
				);

				//remove empty values
				//$field_options[$i] = gmaps_helper::remove_empty_values($field_options[$i]);

				$i++;	
			}

			if(!empty($field_options))
			{
				$final = $this->convert_array_to_js($field_options);

				//create the indivudual objects, the array converter can`t create it.
				$final = str_replace("'url'", "url", $final);
				$final = str_replace("'textColor'", "textColor", $final);
				$final = str_replace("'width'", "width", $final);
				$final = str_replace("'height'", "height", $final);
				$final = str_replace("'anchor' :'[", "anchor :[", $final);
				$final = str_replace("]', 'textSize'", "], textSize", $final);

				return $final;
			}						
		}

		return '[]';
    }

    // ----------------------------------------------------------------------------------

    //set the styled map
    private function _set_styled_map()
    {
		//snazzy?
		$snazzy = gmaps_helper::get_from_tagdata('map_style:snazzymaps', '');
		if($snazzy != '')
		{
			return $snazzy;
		}

		//default styling
		$_fields = $this->preg_array_key_exists('/map_style:/',ee()->TMPL->tagparams);
		$fields = array();
		$field_options = array();

		//any fields there?
		if(!empty($_fields))
		{
			// Create a tussen array which can be threated for the js converting
			foreach($_fields as $val)
			{
				$_val = explode(':', $val);
				
				//explode on | by multiple values
				$param = explode('|', gmaps_helper::get_from_tagdata($val, ''));

				//multiple values?
				if(count($param) > 1)
				{
					$tmp_val = array();
					foreach($param as $v)
					{
						$tmp_val[] = $v;
					}

					$fields[$_val[1]][$_val[2]] = $tmp_val;
					
				}

				//single value
				else
				{
					$fields[$_val[1]][$_val[2]] = $param[0];
				}				
			}

		    $i = 0;
			foreach($fields as $key=>$val)
			{
				$field_options[$i] = array();

				if($key != 'default')
				{
					$field_options[$i]['featureType'] = $key;
					$field_options[$i]['elementType'] = isset($val['element_type']) ? $val['element_type'] : '' ;	
				}
				else
				{
					$field_options[$i]['featureType'] = 'all';
				}

				$field_options[$i]['stylers'] = array(array(
						'hue' 				=> isset($val['hue']) ? $val['hue'] : '',
						'lightness' 		=> isset($val['lightness']) ? $val['lightness'] : '',
						'saturation' 		=> isset($val['saturation']) ? $val['saturation'] : '',
						'inverseLightness' 	=> isset($val['inverse_lightness']) ? $val['inverse_lightness'] : '',
						'visibility'		=> isset($val['visibility']) ? $val['visibility'] == "no" ? 'off' : $val['visibility'] : '',
						'color' 			=> isset($val['color']) ? $val['color'] : '',
						'width'			 	=> isset($val['width']) ? $val['width'] : ''
				));

				//remove empty values
				$field_options[$i] = gmaps_helper::remove_empty_values($field_options[$i]);

				//if the stylers are empty return empty array
				if(empty($field_options[$i]['stylers']))
				{
					unset($field_options[$i]);
				}

				$i++;	
			}

			if(!empty($field_options))
			{
				$final = $this->convert_array_to_js($field_options);

				//create the indivudual objects, the array converter can`t create it.
				$final = str_replace(", 'hue'", "}, {'hue'", $final);
				$final = str_replace(", 'lightness'", "}, {'lightness'", $final);
				$final = str_replace(", 'saturation'", "}, {'saturation'", $final);
				$final = str_replace(", 'inverseLightness'", "}, {'inverseLightness'", $final);
				$final = str_replace(", 'visibility'", "}, {'visibility'", $final);
				$final = str_replace(", 'color'", "}, {'color'", $final);
				$final = str_replace(", 'width'", "}, {'width'", $final);

				return $final;
			}						
		}

		return '{}';
    }

	// ----------------------------------------------------------------------------------

	//catch the queue code and attach it
	public function _output_js($js_output = '')
	{
        $js_output = '<script type="text/javascript">'.$js_output.'</script>';

		//check if we need to catch the js output?
		if($this->catch_output_js)
		{
			$js_output .= gmaps_helper::get_ee_cache('output_js');
			gmaps_helper::set_ee_cache('output_js', $js_output, true);
			return '';
		}

		return $js_output;
	}

	// ----------------------------------------------------------------------------------

	/**
	 * Calculate distance via Google Maps
	 *
	 * @param $start
	 * @param $finish
	 * @param bool $raw
	 * @return array
	 * @throws Exception
     */
	private function get_driving_information($start, $finish, $raw = false)
	{
		if(strcmp($start, $finish) == 0)
		{
			$time = 0;
			if($raw)
			{
				$time .= ' seconds';
			}

			return array('distance' => 0, 'time' => $time);
		}

		$start  = urlencode($start);
		$finish = urlencode($finish);

		$distance   = 'unknown';
		$time		= 'unknown';

		$url = 'http://maps.googleapis.com/maps/api/directions/xml?origin='.$start.'&destination='.$finish;
		if($data = file_get_contents($url))
		{
			$xml = new SimpleXMLElement($data);

			if(isset($xml->route->leg->duration->value) AND (int)$xml->route->leg->duration->value > 0)
			{
				if($raw)
				{
					$distance = (string)$xml->route->leg->distance->text;
					$time	  = (string)$xml->route->leg->duration->text;
				}
				else
				{
					$distance = (int)$xml->route->leg->distance->value / 1000 / 1.609344;
					$time	  = (int)$xml->route->leg->duration->value;
				}
			}
			else
			{
				return false;
				//throw new Exception('Could not find that route');
			}

			return array('distance' => $distance, 'time' => $time);
		}
		else
		{
			return false;
			//throw new Exception('Could not resolve URL');
		}
	}

	// ----------------------------------------------------------------------------------

	/**
	 * Calculates the great-circle distance between two points, with
	 * the Haversine formula.
	 * @param float $latitudeFrom Latitude of start point in [deg decimal]
	 * @param float $longitudeFrom Longitude of start point in [deg decimal]
	 * @param float $latitudeTo Latitude of target point in [deg decimal]
	 * @param float $longitudeTo Longitude of target point in [deg decimal]
	 * @param float $earthRadius Mean earth radius in [m]
	 * @return float Distance between points in [m] (same as earthRadius)
	 */
	function haversine_great_circle_distance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
	{
		// convert from degrees to radians
		$latFrom = deg2rad($latitudeFrom);
		$lonFrom = deg2rad($longitudeFrom);
		$latTo = deg2rad($latitudeTo);
		$lonTo = deg2rad($longitudeTo);

		$latDelta = $latTo - $latFrom;
		$lonDelta = $lonTo - $lonFrom;

		$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
				cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
		return $angle * $earthRadius;
	}
}
