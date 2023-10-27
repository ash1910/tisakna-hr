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

require_once(PATH_THIRD.'gmaps/libraries/api/gmaps_api_base.php');

class Gmaps_route extends Gmaps_api_base
{
    public $tag = 'gmaps:add_route';
    public $tagpair = false;
    public $early_parse = false;

    public function __construct()
    {
        parent::__construct();
    }

    // ----------------------------------------------------------------------------------

    /**
     * fetch the data
     *
     * @param int $map_id
     * @return array
     */
    public function fetch()
    {
        return $this->_fetch();
    }

    // ----------------------------------------------------------------------------------

    /**
     * fetch the marker from a marker tag pair {add_marker}
     *
     * @param int $map_id
     * @param array $data
     * @param string $inner_tagdata
     * @return unknown_type
     */
    public function build($map_id = 0, $data = array(), $inner_tagdata = '')
    {
        //set the specific vars
        $from_address = gmaps_helper::array_value($data, 'address:from');
        $from_latlng = gmaps_helper::array_value($data, 'latlng:from');
        $to_address = gmaps_helper::array_value($data, 'address:to');
        $to_latlng = gmaps_helper::array_value($data, 'latlng:to');
        $stops_address = gmaps_helper::array_value($data, 'address:stops');
        $stops_latlng = gmaps_helper::array_value($data, 'latlng:stops');

        $travel_mode = gmaps_helper::array_value($data, 'travel_mode', 'driving');
        $stroke_color = gmaps_helper::array_value($data, 'stroke_color', '#131540');
        $stroke_opacity = gmaps_helper::array_value($data, 'stroke_opacity', '0.6');
        $stroke_weight = gmaps_helper::array_value($data, 'stroke_weight', '6');

        //callbacks
        $start_callback = gmaps_helper::array_value($data, 'start_callback', 'function(){}');
        $step_callback = gmaps_helper::array_value($data, 'step_callback', 'function(){}');
        $end_callback = gmaps_helper::array_value($data, 'end_callback', 'function(){}');

        //prepare to address
        if($to_address != '')
        {
            $result = ee()->gmaps_geocoder->geocode_address(array($to_address));
            $to_latlng = ee()->gmaps_geocoder->get_field('latlng', $result, 'No result found for {gmaps:add_route to_address="'.$to_address.'"}');
        }
        else
        {
            $to_latlng = array_shift($to_latlng);
        }

        //prepare from address
        if($from_address != '')
        {
            $result = ee()->gmaps_geocoder->geocode_address(array($from_address));
            $from_latlng = ee()->gmaps_geocoder->get_field('latlng', $result, 'No result found for {gmaps:add_route from_address="'.$from_address.'"}');
        }
        else
        {
            $from_latlng = array_shift($from_latlng);
        }

        //prepare stop address
        if($stops_address != '')
        {
            $result = ee()->gmaps_geocoder->geocode_address(ee()->gmaps->explode($stops_address));
            $stops_latlng = ee()->gmaps_geocoder->pluck_field('latlng', $result, 'No result found for {gmaps:add_route stops_address="'.$stops_address.'"}');
            $stops_latlng = implode('|', $stops_latlng);
        }
        else
        {
            $stops_latlng = ($stops_latlng);
        }

        //set the js
        $js = '
            EE_GMAPS.api("addRoute", {
              mapID : "ee_gmap_'.$map_id.'",
              origin : '.gmaps_helper::build_js_array(str_replace(',', '|', $from_latlng)).',
              destination : '.gmaps_helper::build_js_array(str_replace(',', '|', $to_latlng)).',
              waypoints : '.gmaps_helper::build_js_array($stops_latlng).',
              travelMode: "'.$travel_mode.'",
              strokeColor: "'.$stroke_color.'",
              strokeOpacity: "'.$stroke_opacity.'",
              strokeWeight: "'.$stroke_weight.'",
              startCallback: '.$start_callback.',
              stepCallback: '.$step_callback.',
              endCallback: '.$end_callback.',
            });
        ';

        return $this->script($js);
    }

    // ----------------------------------------------------------------------

} // END CLASS
