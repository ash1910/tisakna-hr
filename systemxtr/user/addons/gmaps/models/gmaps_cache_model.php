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


/**
 * Include the config file
 */
require_once PATH_THIRD.'gmaps/config.php';

class Gmaps_cache_model
{

    /*
        //get the result if needed and parse it through the cache object
        $result = ee()->gmaps_cache_model->init($key, array(
            'location' => 'amsterdam',
            'second_var' => 'just a value'
        ), 'geocode', function($location, $second_var){

            // success result
            return array(
                'location_name' => $search_location,
                'result' => $result,

                //extra data goes via the mapping key
                'mapping' => array(
                    'address' 		=> trim($search_location),
                    'lat'			=> $result->getLatitude(),
                    'lng'			=> $result->getLongitude(),
                    'geocoder'		=> $provider
                )
            );

            //no result
            return array(
                'location_name' => $search_location, //location name for in the log
                'result' => false
            );
        });
     */

    /**
     * @param string $key
     * @param array $fetch_function_vars
     * @param string $type
     * @param null $fetch_function
     * @return bool
     */
    public function init($key = '', $fetch_function_vars = array(), $type = '', $fetch_function = null)
    {
        //set the key
        $key = md5($key);

        //check if we have a places in the cache
        $location = ee('Model')
            ->get('gmaps:Cache')
            ->filter('search_key', $key)
            ->filter('type', $type)
            ->limit(1);

        //need to refresh marker
        $fetch_data = false;

        //found in cache and not refreshed
        if($location->count() > 0)
        {
            //get the place
            $location = $location->first();

            if($location->needToRefresh(ee()->gmaps_settings->item('cache_time')) == false)
            {
                //log
                gmaps_helper::log('Get the '.$type.' result from the cache', 3);
                return $location;
            }
            else
            {
                $fetch_data = true;
            }
        }

        //Need to refresh to get the result
        else
        {
            $fetch_data = true;
        }

        if($fetch_data)
        {
            //do we have a fetch function, to get the data?
            if(is_callable($fetch_function))
            {
                //inject the function vars array to the fetch function
                $result = call_user_func_array($fetch_function, $fetch_function_vars);

                if($result['result'] != false)
                {
                    //log
                    gmaps_helper::log('Save '.$result['location_name'].' to the cache', 3);

                    //insert data
                    $insert = array(
                        'search_key' => $key,
                        'date' => time(),
                        'result_object'	=> $result['result'],
                        'type' => $type
                    );

                    //extend the data
                    if(isset($result['mapping']))
                    {
                        $insert = array_merge($result['mapping'], $insert);
                    }

                    // save to the cache
                    $result = ee('Model')->make('gmaps:Cache')->set($insert)->save();

                    return $result;
                }
                else
                {
                    gmaps_helper::log('Location not found: '.$result['location_name'], 2);
                }
            }
        }

        return false;
    }

} // END CLASS
