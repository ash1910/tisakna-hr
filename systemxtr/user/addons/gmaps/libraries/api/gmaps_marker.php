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

class Gmaps_marker extends Gmaps_api_base
{
    public $tag = 'gmaps:add_marker';
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
     * @return string
     */
    public function build($map_id = 0, $data = array(), $inner_tagdata = '')
    {
        //set return var
        $js_array = array();
        $js_events['mouseover'] = array();
        $js_events['mouseout'] = array();

        //get the data
        $address = gmaps_helper::array_value($data, 'address');
        $latlng = gmaps_helper::array_value($data, 'latlng');

        $fit_map = gmaps_helper::check_yes(gmaps_helper::array_value($data, 'fit_map', 'no'));

        $title = gmaps_helper::array_value($data, 'title', '');
        $label = gmaps_helper::array_value($data, 'label', '');
        $show_title = gmaps_helper::check_yes(gmaps_helper::array_value($data, 'show_title', 'yes'));
        $animation = gmaps_helper::check_yes(gmaps_helper::array_value($data, 'animation', 'no'));
        $marker_open_by_default = gmaps_helper::check_yes(gmaps_helper::array_value($data, 'open_by_default', 'no'));

        //need to parse elevation aswell?
        $this->get_elevation = gmaps_helper::check_yes(gmaps_helper::array_value($data, 'get_elevation', 'no'), true);

        //HTML / Infowindow
        $infowindow_html = gmaps_helper::array_value($data, 'infowindow', '');
        $infowindow_open_on_hover = gmaps_helper::check_yes(gmaps_helper::array_value($data, 'infowindow:open_on_hover', 'no'));

        //infobox
        $marker_infobox_content = gmaps_helper::array_value($data, 'infobox:content', '');
        $marker_infobox_box_style_opacity = gmaps_helper::array_value($data, 'infobox:box_style:opacity', '');
        $marker_infobox_box_style_width = gmaps_helper::array_value($data, 'infobox:box_style:width', '');
        $marker_infobox_close_box_margin = gmaps_helper::array_value($data, 'infobox:close_box_margin', '2px');
        $marker_infobox_close_box_url = gmaps_helper::array_value($data, 'infobox:close_box_url', 'http://www.google.com/intl/en_us/mapfiles/close.gif');
        $marker_infobox_box_class = gmaps_helper::array_value($data, 'infobox:box_class', '');
        $marker_infobox_max_width = gmaps_helper::array_value($data, 'infobox:max_width', '');
        $marker_infobox_z_index = gmaps_helper::array_value($data, 'infobox:z_index', '');
        $marker_infobox_pixel_offset_left = gmaps_helper::array_value($data, 'infobox:pixel_offset_left', '-140');
        $marker_infobox_pixel_offset_top = gmaps_helper::array_value($data, 'infobox:pixel_offset_top', '0');

        //icons
        $marker_icon_url = gmaps_helper::array_value($data, 'icon:url');
        $marker_icon_size = gmaps_helper::array_value($data, 'icon:size');
        $marker_icon_scaled_size = gmaps_helper::array_value($data, 'icon:scaled_size');
        $marker_icon_origin = gmaps_helper::array_value($data, 'icon:origin');
        $marker_icon_anchor = gmaps_helper::array_value($data, 'icon:anchor');
        $marker_shape_coord = gmaps_helper::array_value($data, 'shape:coords');

        $result = null;

        //gecode data
        if($address != '')
        {
            $result = ee()->gmaps_geocoder->geocode_address(array($address));
            $latlng = ee()->gmaps_geocoder->get_field('latlng', $result, 'No result found for {gmaps:add_marker address="'.$address.'"}');
            $address = ee()->gmaps_geocoder->get_field('address', $result, 'No result found for {gmaps:add_marker address="'.$address.'"}');
        }
        else
        {

            $result = ee()->gmaps_geocoder->geocode_latlng(array($latlng));
            $address = ee()->gmaps_geocoder->get_field('address', $result, 'No result found for {gmaps:add_marker latlng="'.$latlng.'"}');

            if($result->count() > 0)
            {
                $result = $result->first();

                //update latlng only when there is reverse geocoding option enable
                $reverse_geocode_latlng = gmaps_helper::check_yes(gmaps_helper::array_value($data, 'reverse_geocode_latlng', 'no'));
                if($reverse_geocode_latlng)
                {
                    $latlng = $result->latlng;
                }
            }
        }

        //set the js keys for the markers
        $keys = $this->parse_keys($address, gmaps_helper::array_value($data, 'keys'));

        //convert to array
        $address = gmaps_helper::remove_empty_values(explode('|', $address));
        $latlng = gmaps_helper::remove_empty_values(explode('|', $latlng));

        //loop over the values
        if(!empty($latlng))
        {
            $_latlng = explode(',', $latlng[0]);
            $_latlng[0] = isset($_latlng[0]) ? $_latlng[0] : '';
            $_latlng[1] = isset($_latlng[1]) ? $_latlng[1] : '';

            //set the location name
            $location = isset($address[0]) ? $address[0] : '('.$_latlng[0].', '.$_latlng[1].')';

            //set latlng
            $js_array['lat'] = $_latlng[0];
            $js_array['lng'] = $_latlng[1];

            //set title
            if($show_title)
            {
                if($title == '')
                {
                    $title = isset($address[0]) ? trim($address[0]) : $_latlng[0].', '.$_latlng[1];
                }

                $js_array['title'] = $title;
            }

            //set the label
            if($label)
                $js_array['label'] = $label;

            //set the keys
            if($keys)
                $js_array['keys'] = $keys;

            //set the animation ("google.maps.Animation.DROP" = number 2)
            if($animation)
                $js_array['animation'] = 2;

            //set the icons
            if(!empty($marker_icon_url))
                $js_array['icon']['url'] = $marker_icon_url;
            if(!empty($marker_icon_size ))
                $js_array['icon']['size'] = $marker_icon_size;
            if(!empty($marker_icon_scaled_size ))
                $js_array['icon']['scaledSize'] = $marker_icon_scaled_size;
            if(!empty($marker_icon_origin))
                $js_array['icon']['origin'] = $marker_icon_origin;
            if(!empty($marker_icon_anchor))
                $js_array['icon']['anchor'] = $marker_icon_anchor;
            //shape
            if(!empty($marker_shape_coord))
                $js_array['shape']['coords'] = explode(',', $marker_shape_coord);
            if(!empty($marker_shape_type ))
                $js_array['shape']['type'] = $marker_shape_type;

            //set the marker HTML
            if($infowindow_html != '')
            {
                $js_array['infoWindow']['content'] = $this->parse_js_vars($infowindow_html, $_latlng, $location, $result);
            }

            if($infowindow_open_on_hover)
            {
                $js_events['mouseover'][] = 'this.infoWindow.open(this.map, this);';
                $js_events['mouseout'][] = 'this.infoWindow.close();';
            }

            //set the marker HTML
            if($marker_open_by_default != '')
            {
                $js_array['open_by_default'] = $marker_open_by_default;
            }

            //infobox
            if($marker_infobox_content != '')
            {
                $js_array['infobox']['content'] = $this->parse_js_vars($marker_infobox_content, $_latlng, $location, $result);
                $js_array['infobox']['box_style']['opacity'] = $marker_infobox_box_style_opacity;
                $js_array['infobox']['box_style']['width'] = $marker_infobox_box_style_width ;
                $js_array['infobox']['close_box_margin'] = $marker_infobox_close_box_margin;
                $js_array['infobox']['close_box_url'] = $marker_infobox_close_box_url;
                $js_array['infobox']['box_class'] = $marker_infobox_box_class;
                $js_array['infobox']['max_width'] = $marker_infobox_max_width;
                $js_array['infobox']['z_index'] = $marker_infobox_z_index;
                $js_array['infobox']['pixel_offset']['width'] = $marker_infobox_pixel_offset_left;
                $js_array['infobox']['pixel_offset']['height'] = $marker_infobox_pixel_offset_top;
            }

            $js_array['fitTheMap'] = $fit_map;

            $js_array['mapID'] = "ee_gmap_".$map_id;

            //set the js
            return $this->script('
                 EE_GMAPS.api("addMarker", $.extend('.json_encode($js_array).', {
                    mouseover: function(e){'.implode($js_events['mouseover']).'},
                    mouseout: function(e){'.implode($js_events['mouseout']).'}
                }));
            ');
        }
        else
        {
            gmaps_helper::log('No result founded for {gmaps:add_marker} method', 2, true);
            return;
        }
    }

} // END CLASS
