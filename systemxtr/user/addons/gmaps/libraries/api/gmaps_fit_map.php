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

class Gmaps_fit_map extends Gmaps_api_base
{

    public $tag = 'gmaps:fit_map';
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
     * @return string
     */
    public function build($map_id = 0, $data = array(), $inner_tagdata = '')
    {
        //get the data
        $type = gmaps_helper::array_value($data, 'type');

        //fit maps
        switch($type)
        {
            case 'markers':
            case 'marker':
                $js = $this->fit_markers_on_map($map_id);
                break;
            case 'routes':
            case 'route':
                $js = $this->fit_routes_on_map($map_id);
                break;
            case 'circles':
            case 'circle':
                $js = $this->fit_circles_on_map($map_id);
                break;
            case 'rectangles':
            case 'rectangle':
                $js = $this->fit_rectangles_on_map($map_id);
                break;
            case 'polylines':
            case 'polyline':
                $js = $this->fit_polylines_on_map($map_id);
                break;
            case 'polygons':
            case 'polygon':
                $js = $this->fit_polygons_on_map($map_id);
                break;
            default:
                $js = '';
                break;
        }

        return $this->script($js, false, false);
    }

    // ----------------------------------------------------------------------------------

    /**
     * Fit the markers on the map
     *
     * @param $map_id
     * @return unknown_type
     */
    private function fit_markers_on_map($map_id)
    {
        $js = 'EE_GMAPS.fitMarkersOnMap.push("ee_gmap_'.$map_id.'");';

        return $js;
    }

    // ----------------------------------------------------------------------------------

    /**
     * Fit the routes on the map.
     *
     * Set a variable, because the data must be loaded by google first
     *
     * @param $map_id
     * @return unknown_type
     */
    private function fit_routes_on_map($map_id)
    {
        $js = 'EE_GMAPS.fitRoutesOnMap.push("ee_gmap_'.$map_id.'");';

        return $js;
    }

    // ----------------------------------------------------------------------------------

    /**
     * Fit the markers on the map
     *
     * @param $map_id
     * @return unknown_type
     */
    private function fit_circles_on_map($map_id)
    {
        $js = 'EE_GMAPS.fitCirclesOnMap.push("ee_gmap_'.$map_id.'");';

        return $js;
    }

    // ----------------------------------------------------------------------------------

    /**
     * Fit the markers on the map
     *
     * @param $map_id
     * @return unknown_type
     */
    private function fit_rectangles_on_map($map_id)
    {
        $js = 'EE_GMAPS.fitRectanglesOnMap.push("ee_gmap_'.$map_id.'");';

        return $js;
    }

    // ----------------------------------------------------------------------------------

    /**
     * Fit the markers on the map
     *
     * @param $map_id
     * @return unknown_type
     */
    private function fit_polylines_on_map($map_id)
    {
        $js = 'EE_GMAPS.fitPolylinesOnMap.push("ee_gmap_'.$map_id.'");';

        return $js;
    }

    // ----------------------------------------------------------------------------------

    /**
     * Fit the markers on the map
     *
     * @param $map_id
     * @return unknown_type
     */
    private function fit_polygons_on_map($map_id)
    {
        $js = 'EE_GMAPS.fitPolygonsOnMap.push("ee_gmap_'.$map_id.'");';

        return $js;
    }

} // END CLASS
