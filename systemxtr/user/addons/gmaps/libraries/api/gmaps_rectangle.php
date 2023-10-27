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

class Gmaps_rectangle extends Gmaps_api_base
{
    public $tag = 'gmaps:add_rectangle';
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
        //set return var
        $js_array = array();

        //get the data
        $address = rtrim(gmaps_helper::array_value($data, 'address'), '|');
        $latlng = rtrim(gmaps_helper::array_value($data, 'latlng'), '|');
        $stroke_color = gmaps_helper::array_value($data, 'stroke_color', '#000000');
        $stroke_opacity = gmaps_helper::array_value($data, 'stroke_opacity', '1');
        $stroke_weight = gmaps_helper::array_value($data, 'stroke_weight', '1');
        $fill_color = gmaps_helper::array_value($data, 'fill_color', '#000000');
        $fill_opacity = gmaps_helper::array_value($data, 'fill_opacity', '0.4');

        //if address is lower than two.
        if(gmaps_helper::count_multiple_values($address) != 2 && gmaps_helper::count_multiple_values($latlng) != 2)
        {
            ee()->gmaps->errors[] = 'This method need 2 addresses or latlng to create a rectangle';
            return ee()->gmaps->parse_errors();
        }

        //gecode data
        if($address != '')
        {
            $result = ee()->gmaps_geocoder->geocode_address(explode('|', $address));
            $latlng = ee()->gmaps_geocoder->pluck_field('latlng', $result, 'No result found for {gmaps:add_rectangle address="'.$address.'"}');
            $latlng = implode('|', $latlng);
        }

        //convert to array
        $latlng = gmaps_helper::remove_empty_values(explode('|', $latlng));

        //loop over the values
        if(!empty($latlng) && count($latlng) == 2)
        {
            foreach($latlng as $key=>$val)
            {
                $js_array['bounds'][] = explode(',', $latlng[$key]);
            }
        }
        else
        {
            gmaps_helper::log('No result founded for {gmaps:add_rectangle} method', 2, true);
            return;
        }

        //set the js keys for the markers
        $keys = $this->parse_keys('', gmaps_helper::array_value($data, 'keys'));

        //set the js
        $js = '
            EE_GMAPS.api("addRectangle", {
              mapID : "ee_gmap_'.$map_id.'",
              bounds : EE_GMAPS.reParseLatLngArray('.json_encode($js_array['bounds']).'),
              strokeColor : "'.$stroke_color.'",
              strokeOpacity : "'.$stroke_opacity.'",
              strokeWeight : "'.$stroke_weight.'",
              fillColor : "'.$fill_color.'",
              fillOpacity : "'.$fill_opacity.'",
              keys : "'.$keys.'"
            });
		';

        return $this->script($js);
    }

    // ----------------------------------------------------------------------

} // END CLASS
