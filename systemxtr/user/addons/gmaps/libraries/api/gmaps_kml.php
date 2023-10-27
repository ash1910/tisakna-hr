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

class Gmaps_kml extends Gmaps_api_base
{
    public $tag = 'gmaps:add_kml';
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
        $zIndex = gmaps_helper::array_value($data, 'z_index', 1);
        $preserveViewport = gmaps_helper::check_yes(gmaps_helper::array_value($data, 'preserve_viewport', 'no'), true);
        $screenOverlays = gmaps_helper::check_yes(gmaps_helper::array_value($data, 'screen_overlays', 'yes'), true);
        $suppress_info_windows = gmaps_helper::check_yes(gmaps_helper::array_value($data, 'suppress_info_windows', 'no'), true);
        $clickable = gmaps_helper::check_yes(gmaps_helper::array_value($data, 'clickable', 'yes'), true);
        $url = gmaps_helper::array_value($data, 'url');

        //loop over the values
        if($url == '')
        {
            gmaps_helper::log('No url is given for the KML url');
            return;
        }

        //set the js
        $js = '
            EE_GMAPS.api("loadKML", {
              mapID : "ee_gmap_'.$map_id.'",
              clickable : '.$clickable.',
              preserveViewport : '.$preserveViewport.',
              screenOverlays : '.$screenOverlays.',
              suppressInfoWindows : '.$suppress_info_windows.',
              url : "'.$url.'",
              zIndex : '.$zIndex.'
            });
		';

        return $this->script($js);
    }

    // ----------------------------------------------------------------------

} // END CLASS
