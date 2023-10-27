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

class Gmaps_places extends Gmaps_api_base
{
    public $tag = 'gmaps:get_places';
    public $tagpair = true;
    public $early_parse = true;

    public function __construct()
    {
        parent::__construct();
    }

    // ----------------------------------------------------------------------------------

    /**
     * fetch the marker from a marker tag pair {add_marker}
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
     * @return array
     */
    public function build($map_id = 0, $data = array(), $inner_tagdata = '')
    {
        if(ee()->addons_model->module_installed('gmaps_services'))
        {
            //get the data
            $address = gmaps_helper::array_value($data, 'address');
            $latlng = gmaps_helper::array_value($data, 'latlng');

            $search_types = gmaps_helper::array_value($data, 'search_types');
            $search_keyword = gmaps_helper::array_value($data, 'search_keyword');
            $radius = gmaps_helper::array_value($data, 'radius', '1000');
            $type = gmaps_helper::array_value($data, 'type', 'search');
            $lang = gmaps_helper::array_value($data, 'lang', 'en');

            //gecode data
            if($address != '')
            {
                $result = ee()->gmaps_geocoder->geocode_address(array($address));
                $latlng = ee()->gmaps_geocoder->get_field('latlng', $result, 'No result found for {gmaps:add_places address="'.$address.'"}');
            }

            //no result
            if($latlng == '')
            {
                gmaps_helper::log('No result founded for {gmaps:add_places latlng="'.$latlng.'"} method', 2, true);
                return '';
            }

            //load package
            ee()->load->add_package_path(PATH_THIRD . 'gmaps_services/');
            ee()->load->library('gmaps_services_lib');
            ee()->load->remove_package_path(PATH_THIRD . 'gmaps_services/');

            //fetch result
            $results = ee()->gmaps_services_lib->fetch_places($latlng, $radius, $type,  $search_types, $search_keyword, $lang, 'places:');

            /*
             The result looks like

            [lat] => 52.2251366
            [lng] => 5.9977584
            [icon] => https://maps.gstatic.com/mapfiles/place_api/icons/geocode-71.png
            [id] => 28b1a2e4d66432f3817d7546d3eb99cdda0cf00a
            [name] => Apeldoorn Noord-Oost
            [place_id] => ChIJ0QjET0fHx0cR7oO19Skkpx4
            [reference] => CpQBiQAAAPdyaMshsLVbj039XH_RAY_UUHtxo-snl36Fvkaz-BVen_f5mhpdbU8K-xFqAUr13aAy_7L5GPHRBLbqb00Yz8xhH-eKEwnk1ClTtwnc4lGjQ6T6r2J_46QlhtFRGh5alNEof6fm7AH0tv16f1COnaqUdxuqUHRrgu2xLG8UDsoro4wUqYbs6y-48otUpRapkRIQ08tbGKYCIwca1xWeXOplwBoUrnPLSp1GwgiIum4N_SfjGAhqwBA
            [scope] => GOOGLE
            [vicinity] => Apeldoorn Noord-Oost
            [photos] => Array
                (
                    [0] => Array
                        (
                            [height] => 1365
                            [width] => 2048
                            [url] => https://maps.googleapis.com/maps/api/place/photo?key=AIzaSyDHdgIFPOBqYcIknffKvkymkEgeWtY2XuM&photoreference=CoQBdwAAAHquSessVZyH-JVlzbwzXfgQulJM-_uolsyEskf9ItrB2vAdYFZhbW09u0jl1i-FgQ1J1rsjJivOtDa6N3_x8qKeqElsBC3Ft9ScJtg1SquNxyU0IBsveOWQIfS3Wc5QR3EJJjp5c5A3SoVSGXbTddB0zh_Go53Y_dTfWQ0SDH_gEhDqBqW0-2irZLQrXZOZ3HihGhRkBTxB5qGIN8or2rWtHgBb8OPPZg
                        )

                )

            [types] => Array
                (
                    [0] => Array
                        (
                            [name] => sublocality_level_1
                        )

                    [1] => Array
                        (
                            [name] => sublocality
                        )

                    [2] => Array
                        (
                            [name] => political
                        )
                )
             */

            // @todo error handling?

            //no result
            if(empty($results))
            {
                gmaps_helper::log('No result founded for {gmaps:add_places} method', 2, true);
                return '';
            }

            //parse the places results
            return ee()->TMPL->parse_variables($inner_tagdata, $results);
        }

        return '';
    }

} // END CLASS
