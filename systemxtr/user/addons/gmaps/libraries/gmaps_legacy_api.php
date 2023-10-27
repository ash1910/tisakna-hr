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

class Gmaps_legacy_api
{

    public function __construct()
    {
        ee()->load->library('gmaps_library', null, 'gmaps');

        //load the config
        ee()->load->library(GMAPS_MAP.'_settings');

        //require the default settings
        require PATH_THIRD.GMAPS_MAP.'/settings.php';
    }

    // ----------------------------------------------------------------------------------

    /**
     * Create an api call for the marker
     *
     * @return unknown_type
     */
    function create_marker()
    {
        //set return var
        $js_array = array();

        //get the data
        $map_id = gmaps_helper::get_from_tagdata('map:id');
        $address = gmaps_helper::get_from_tagdata('address');
        $latlng = gmaps_helper::get_from_tagdata('latlng');
        $fit_map = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('fit_map', 'no'));
        $show_title = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('show_title', 'yes'));
        $animation = gmaps_helper::check_yes(gmaps_helper::get_from_tagdata('animation', 'no'));
        $marker_html = gmaps_helper::get_from_tagdata('html', '');
        //icons
        $marker_icon_url = gmaps_helper::get_from_tagdata('icon:url');
        $marker_icon_default_url = gmaps_helper::get_from_tagdata('icon_default:url', '');
        $marker_shadow_url = gmaps_helper::get_from_tagdata('shadow:url');
        $marker_shadow_default_url = gmaps_helper::get_from_tagdata('shadow_default:url', '');
        $marker_icon_size = gmaps_helper::get_from_tagdata('icon:size');
        $marker_icon_default_size = gmaps_helper::get_from_tagdata('icon_default:size', '');
        $marker_shadow_size = gmaps_helper::get_from_tagdata('shadow:size');
        $marker_shadow_default_size = gmaps_helper::get_from_tagdata('shadow_default:size', '');
        $marker_icon_origin = gmaps_helper::get_from_tagdata('icon:origin');
        $marker_icon_default_origin = gmaps_helper::get_from_tagdata('icon_default:origin', '');
        $marker_shadow_origin = gmaps_helper::get_from_tagdata('shadow:origin');
        $marker_shadow_default_origin = gmaps_helper::get_from_tagdata('shadow_default:origin', '');
        $marker_icon_anchor = gmaps_helper::get_from_tagdata('icon:anchor');
        $marker_icon_default_anchor = gmaps_helper::get_from_tagdata('icon_default:anchor', '');
        $marker_shadow_anchor = gmaps_helper::get_from_tagdata('shadow:anchor');
        $marker_shadow_default_anchor = gmaps_helper::get_from_tagdata('shadow_default:anchor', '');
        $marker_shape_coord = gmaps_helper::get_from_tagdata('shape:coords');
        $marker_shape_default_coord = gmaps_helper::get_from_tagdata('shape_default:coord', '');
        $marker_shape_type = gmaps_helper::get_from_tagdata('shape:type');
        $marker_shape_default_type = gmaps_helper::get_from_tagdata('shape_default:type', '');

        //if address empty return a error.
        if($address == '' && $latlng == '')
        {
            ee()->gmaps->errors[] = 'You forgot to fill in an address or latlng.';
            return ee()->gmaps->parse_errors();
        }

        //gecode data
        if($address != '')
        {
            $geocode_result = ee()->gmaps->geocode_address(array($address));
            //$geocode_object = ee()->gmaps->geocode_address(explode('|', $address), 'array', 'all');
            $address = $geocode_result['address'];
            $latlng = $geocode_result['latlng'];
        }
        else
        {
            $latlng = ($latlng);
            $address = "";
        }

        //convert to array
        $address = gmaps_helper::remove_empty_values(explode('|', $address));
        $latlng = gmaps_helper::remove_empty_values(explode('|', $latlng));

        //loop over the values
        if(!empty($latlng))
        {
            $_latlng = explode(',', $latlng[0]);

            //set the location name
            $location = isset($address[0]) ? $address[0] : '('.$_latlng[0].', '.$_latlng[1].')';

            //set latlng
            $js_array[0]['lat'] = $_latlng[0];
            $js_array[0]['lng'] = $_latlng[1];

            //set the fit map
            $js_array[0]['fitTheMap'] = $fit_map;

            //set title
            if($show_title)
                $js_array[0]['title'] = isset($address[0]) ? trim($address[0]) : $_latlng[0].', '.$_latlng[1];

            //set the animation ("google.maps.Animation.DROP" = number 2)
            if($animation)
                $js_array[0]['animation'] = 2;

            //set the icons
            if(isset($marker_icon_url[0]) || !empty($marker_icon_default_url))
                $js_array[0]['icon']['url'] = $marker_icon_url != '' ? $marker_icon_url : $marker_icon_default_url;
            if(isset($marker_icon_size[0]) || !empty($marker_icon_default_size ))
                $js_array[0]['icon']['size'] = $marker_icon_size != '' ? $marker_icon_size : $marker_icon_default_size;
            if(isset($marker_icon_origin[0]) || !empty($marker_icon_default_origin))
                $js_array[0]['icon']['origin'] = $marker_icon_origin != '' ? $marker_icon_origin : $marker_icon_default_origin;
            if(isset($marker_icon_anchor[0]) || !empty($marker_icon_default_anchor))
                $js_array[0]['icon']['anchor'] = $marker_icon_anchor != '' ? $marker_icon_anchor : $marker_icon_default_anchor;
            //shadow - fully @deprecated v2.3 (https://developers.google.com/maps/documentation/javascript/overlays#ComplexIcons)
            if(isset($marker_shadow_url[0]) || !empty($marker_shadow_default_url))
                $js_array[0]['shadow']['url'] = $marker_shadow_url != '' ? $marker_shadow_url : $marker_shadow_default_url;
            if(isset($marker_shadow_size[0]) || !empty($marker_shadow_default_size ))
                $js_array[0]['shadow']['size'] = $marker_shadow_size != '' ? $marker_shadow_size : $marker_shadow_default_size;
            if(isset($marker_shadow_origin[0]) || !empty($marker_shadow_default_origin))
                $js_array[0]['shadow']['origin'] = $marker_shadow_origin != '' ? $marker_shadow_origin : $marker_shadow_default_origin;
            if(isset($marker_shadow_anchor[0]) || !empty($marker_shadow_default_anchor))
                $js_array[0]['shadow']['anchor'] = $marker_shadow_anchor != '' ? $marker_shadow_anchor : $marker_shadow_default_anchor;
            //shape
            if(isset($marker_shape_coord[0]) || !empty($marker_shape_default_coord))
                $js_array[0]['shape']['coords'] = $marker_shape_coord != '' ? explode(',', $marker_shape_coord) : explode(',', $marker_shape_default_coord);
            if(isset($marker_shape_type[0]) || !empty($marker_shape_default_type ))
                $js_array[0]['shape']['type'] = $marker_shape_type != '' ? $marker_shape_type : $marker_shape_default_type;

            //set the marker HTML
            if($marker_html != '')
                $js_array[0]['infoWindow']['content'] = $this->_parse_vars($marker_html, $_latlng, $location);
        }
        else
        {
            ee()->gmaps->errors[] = 'No result founded';
            return ee()->gmaps->parse_errors();
        }

        //set the js
        $js = '
			EE_GMAPS.ready(function(){
				EE_GMAPS.api("addMarker", {
				  mapID : "ee_gmap_'.$map_id.'",
				  multi : '.json_encode($js_array).'
				});
			});
		';

        return '<script>'.$js.'</script>';
    }

    // ----------------------------------------------------------------------------------

    /**
     * Create an api call for the marker
     *
     * @return unknown_type
     */
    function create_polyline()
    {
        //set return var
        $js_array = array();

        //get the data
        $map_id = gmaps_helper::get_from_tagdata('map:id');
        $address = gmaps_helper::get_from_tagdata('address');
        $latlng = gmaps_helper::get_from_tagdata('latlng');
        $stoke_color = gmaps_helper::get_from_tagdata('stoke_color', '#000000');
        $stroke_opacity = gmaps_helper::get_from_tagdata('stroke_opacity', '1');
        $stroke_weight = gmaps_helper::get_from_tagdata('stroke_weight', '1');

        //if address empty return a error.
        if($address == '' && $latlng == '')
        {
            ee()->gmaps->errors[] = 'You forgot to fill in an address or latlng.';
            return ee()->gmaps->parse_errors();
        }

        //gecode data
        if($address != '')
        {
            $geocode_result = ee()->gmaps->geocode_address(explode('|', $address));
            //$geocode_object = ee()->gmaps->geocode_address(explode('|', $address), 'array', 'all');
            $address = $geocode_result['address'];
            $latlng = $geocode_result['latlng'];
        }
        else
        {
            $latlng = ($latlng);
            $address = "";
        }

        //convert to array
        $address = gmaps_helper::remove_empty_values(explode('|', $address));
        $latlng = gmaps_helper::remove_empty_values(explode('|', $latlng));

        //loop over the values
        if(!empty($latlng))
        {
            foreach($latlng as $key=>$val)
            {
                $js_array['path'][] = explode(',', $latlng[$key]);
            }
        }
        else
        {
            ee()->gmaps->errors[] = 'No result founded';
            return ee()->gmaps->parse_errors();
        }

        //set the js
        $js = '
			EE_GMAPS.ready(function(){
				EE_GMAPS.triggerEvent("addPolyline", {
				  mapID : "ee_gmap_'.$map_id.'",
				  path : EE_GMAPS.reParseLatLngArray('.json_encode($js_array['path']).'),
				  stokeColor : "'.$stoke_color.'",
				  strokeOpacity : "'.$stroke_opacity.'",
				  strokeWeight : "'.$stroke_weight.'"
				});
			});
		';

        return '<script>'.$js.'</script>';
    }

    // ----------------------------------------------------------------------------------

    /**
     * Create an api call for the marker
     *
     * @return unknown_type
     */
    function create_polygon()
    {
        //set return var
        $js_array = array();

        //get the data
        $map_id = gmaps_helper::get_from_tagdata('map:id');
        $address = gmaps_helper::get_from_tagdata('address');
        $latlng = gmaps_helper::get_from_tagdata('latlng');
        $stoke_color = gmaps_helper::get_from_tagdata('stoke_color', '#000000');
        $stroke_opacity = gmaps_helper::get_from_tagdata('stroke_opacity', '1');
        $stroke_weight = gmaps_helper::get_from_tagdata('stroke_weight', '1');
        $fill_color = gmaps_helper::get_from_tagdata('fill_color', '#000000');
        $fill_opacity = gmaps_helper::get_from_tagdata('fill_opacity', '0.4');

        //if address empty return a error.
        if($address == '' && $latlng == '')
        {
            ee()->gmaps->errors[] = 'You forgot to fill in an address or latlng.';
            return ee()->gmaps->parse_errors();
        }

        //gecode data
        if($address != '')
        {
            $geocode_result = ee()->gmaps->geocode_address(explode('|', $address));
            //$geocode_object = ee()->gmaps->geocode_address(explode('|', $address), 'array', 'all');
            $address = $geocode_result['address'];
            $latlng = $geocode_result['latlng'];
        }
        else
        {
            $latlng = ($latlng);
            $address = "";
        }

        //convert to array
        $address = gmaps_helper::remove_empty_values(explode('|', $address));
        $latlng = gmaps_helper::remove_empty_values(explode('|', $latlng));

        //loop over the values
        if(!empty($latlng))
        {
            foreach($latlng as $key=>$val)
            {
                $js_array['paths'][] = explode(',', $latlng[$key]);
            }
        }
        else
        {
            ee()->gmaps->errors[] = 'No result founded';
            return ee()->gmaps->parse_errors();
        }

        //set the js
        $js = '
			EE_GMAPS.ready(function(){
				EE_GMAPS.triggerEvent("addPolygon", {
				  mapID : "ee_gmap_'.$map_id.'",
				  paths : EE_GMAPS.reParseLatLngArray('.json_encode($js_array['paths']).'),
				  stokeColor : "'.$stoke_color.'",
				  strokeOpacity : "'.$stroke_opacity.'",
				  strokeWeight : "'.$stroke_weight.'",
				  fillColor : "'.$fill_color.'",
				  fillOpacity : "'.$fill_opacity.'"
				});
			});
		';

        return '<script>'.$js.'</script>';
    }

    // ----------------------------------------------------------------------------------

    /**
     * Create an api call for the marker
     *
     * @return unknown_type
     */
    function create_circle()
    {
        //set return var
        $js_array = array();

        //get the data
        $map_id = gmaps_helper::get_from_tagdata('map:id');
        $address = gmaps_helper::get_from_tagdata('address');
        $latlng = gmaps_helper::get_from_tagdata('latlng');
        $stoke_color = gmaps_helper::get_from_tagdata('stoke_color', '#000000');
        $stroke_opacity = gmaps_helper::get_from_tagdata('stroke_opacity', '1');
        $stroke_weight = gmaps_helper::get_from_tagdata('stroke_weight', '1');
        $fill_color = gmaps_helper::get_from_tagdata('fill_color', '#000000');
        $fill_opacity = gmaps_helper::get_from_tagdata('fill_opacity', '0.4');
        $radius = gmaps_helper::get_from_tagdata('radius', '1000');

        //if address empty return a error.
        if($address == '' && $latlng == '')
        {
            ee()->gmaps->errors[] = 'You forgot to fill in an address or latlng.';
            return ee()->gmaps->parse_errors();
        }

        //gecode data
        if($address != '')
        {
            $geocode_result = ee()->gmaps->geocode_address(array($address));
            //$geocode_object = ee()->gmaps->geocode_address(explode('|', $address), 'array', 'all');
            $address = $geocode_result['address'];
            $latlng = $geocode_result['latlng'];
        }
        else
        {
            $latlng = ($latlng);
            $address = "";
        }

        //convert to array
        $address = gmaps_helper::remove_empty_values(explode('|', $address));
        $latlng = gmaps_helper::remove_empty_values(explode('|', $latlng));

        //loop over the values
        if(!empty($latlng))
        {
            $_latlng = explode(',', $latlng[0]);

        }
        else
        {
            ee()->gmaps->errors[] = 'No result founded';
            return ee()->gmaps->parse_errors();
        }

        //set the js
        $js = '
			EE_GMAPS.ready(function(){
				EE_GMAPS.triggerEvent("addCircle", {
				  mapID : "ee_gmap_'.$map_id.'",
				  lat : '.$_latlng[0].',
				  lng : '.$_latlng[1].',
				  stokeColor : "'.$stoke_color.'",
				  strokeOpacity : "'.$stroke_opacity.'",
				  strokeWeight : "'.$stroke_weight.'",
				  fillColor : "'.$fill_color.'",
				  fillOpacity : "'.$fill_opacity.'",
				  radius : '.$radius.'
				});
			});
		';

        return '<script>'.$js.'</script>';
    }

    // ----------------------------------------------------------------------------------

    /**
     * Create an api call for the marker
     *
     * @return unknown_type
     */
    function create_rectangle()
    {
        //set return var
        $js_array = array();

        //get the data
        $map_id = gmaps_helper::get_from_tagdata('map:id');
        $address = gmaps_helper::get_from_tagdata('address');
        $latlng = gmaps_helper::get_from_tagdata('latlng');
        $stoke_color = gmaps_helper::get_from_tagdata('stoke_color', '#000000');
        $stroke_opacity = gmaps_helper::get_from_tagdata('stroke_opacity', '1');
        $stroke_weight = gmaps_helper::get_from_tagdata('stroke_weight', '1');
        $fill_color = gmaps_helper::get_from_tagdata('fill_color', '#000000');
        $fill_opacity = gmaps_helper::get_from_tagdata('fill_opacity', '0.4');

        //if address empty return a error.
        if($address == '' && $latlng == '')
        {
            ee()->gmaps->errors[] = 'You forgot to fill in an address or latlng.';
            return ee()->gmaps->parse_errors();
        }

        //if address is lower than two.
        if(gmaps_helper::count_multiple_values($address) != 2 && gmaps_helper::count_multiple_values($latlng) != 2)
        {
            ee()->gmaps->errors[] = 'This method need only 2 addresses or latlng to create a rectangle';
            return ee()->gmaps->parse_errors();
        }

        //gecode data
        if($address != '')
        {
            $geocode_result = ee()->gmaps->geocode_address(explode('|', $address));
            //$geocode_object = ee()->gmaps->geocode_address(explode('|', $address), 'array', 'all');
            $address = $geocode_result['address'];
            $latlng = $geocode_result['latlng'];
        }
        else
        {
            $latlng = ($latlng);
            $address = "";
        }

        //convert to array
        $address = gmaps_helper::remove_empty_values(explode('|', $address));
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
            ee()->gmaps->errors[] = 'No result founded';
            return ee()->gmaps->parse_errors();
        }

        //set the js
        $js = '
			EE_GMAPS.ready(function(){
				EE_GMAPS.triggerEvent("addRectangle", {
				  mapID : "ee_gmap_'.$map_id.'",
				  bounds : EE_GMAPS.reParseLatLngArray('.json_encode($js_array['bounds']).'),
				  stokeColor : "'.$stoke_color.'",
				  strokeOpacity : "'.$stroke_opacity.'",
				  strokeWeight : "'.$stroke_weight.'",
				  fillColor : "'.$fill_color.'",
				  fillOpacity : "'.$fill_opacity.'"
				});
			});
		';

        return '<script>'.$js.'</script>';
    }

    // ----------------------------------------------------------------------------------

    /**
     * Create an api call for the marker
     *
     * @return unknown_type
     */
    function fit_map()
    {
        //output var, because the addMarker trigger on this var
        $js = 'EE_GMAPS.fitTheMap = true;';

        return '<script>'.$js.'</script>';
    }

    // ----------------------------------------------------------------------------------

    /**
     * Create an api call for the marker
     *
     * @return unknown_type
     */
    function set_center()
    {
        //set the vars
        $map_id = gmaps_helper::get_from_tagdata('map:id');
        $address = gmaps_helper::get_from_tagdata('address');
        $latlng = gmaps_helper::get_from_tagdata('latlng');

        //set return var
        $js_array = array();

        //if address empty return a error.
        if($address == '' && $latlng == '')
        {
            ee()->gmaps->errors[] = 'You forgot to fill in an address or latlng.';
            return ee()->gmaps->parse_errors();
        }

        //gecode data
        if($address != '')
        {
            $geocode_result = ee()->gmaps->geocode_address(array($address));
            //$geocode_object = ee()->gmaps->geocode_address(explode('|', $address), 'array', 'all');
            $address = $geocode_result['address'];
            $latlng = $geocode_result['latlng'];
        }
        else
        {
            $latlng = ($latlng);
            $address = "";
        }

        $latlng = explode(',', $latlng);

        //get the data
        //$map_id = gmaps_helper::get_from_tagdata('map:id');

        //set the js
        $js = '
			EE_GMAPS.ready(function(){
				EE_GMAPS.triggerEvent("center", {
				  mapID : "ee_gmap_'.$map_id.'",
				  lat : '.$latlng[0].',
				  lng : '.$latlng[1].'
				});
			});
		';

        return '<script>'.$js.'</script>';
    }

    // ----------------------------------------------------------------------------------

    /**
     * Create an api call for the marker
     *
     * @return unknown_type
     */
    function set_zoom()
    {}

    // ----------------------------------------------------------------------------------

    /**
     * Parse Javascript vars like [location]
     *
     * @return unknown_type
     */
    private function _parse_vars($string = '', $latlng, $location)
    {
        //[location]
        $string = str_replace('[location]', $location, $string);

        //[route_to_url]
        $string = str_replace('[route_to_url]', 'https://maps.google.com/maps?daddr='.$latlng[0].','.$latlng[1], $string);

        //[route_url]
        $string = str_replace('[route_from_url]', 'https://maps.google.com/maps?saddr='.$latlng[0].','.$latlng[1], $string);

        //[map_url]
        $string = str_replace('[map_url]', 'https://maps.google.com/maps?q='.$latlng[0].','.$latlng[1], $string);

        return trim($string);
    }

} // END CLASS
