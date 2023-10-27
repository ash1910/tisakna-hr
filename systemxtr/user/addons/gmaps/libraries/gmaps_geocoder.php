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

use EllisLab\ExpressionEngine\Library\Data\Collection;

require_once(PATH_THIRD.'gmaps/config.php');

class Gmaps_geocoder
{

    public $debug = array();

    private $adapter;
    private $geocoder = null;
    private $providers = array();

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
        //load the config
        ee()->load->library(GMAPS_MAP.'_settings');

        ee()->load->model(GMAPS_MAP.'_cache_model');

        //require the default settings
        require PATH_THIRD.GMAPS_MAP.'/settings.php';
    }

    // ----------------------------------------------------------------------
    // CUSTOM FUNCTIONS
    // ----------------------------------------------------------------------

    /**
     * Load the geocoders
     *
     * @param string $group
     */
    protected function load_geocoder($group = 'geocoding')
    {
        //geocoder not loaded? Create the object
        if($this->geocoder == null)
        {
            //Create instances
            if(ee()->gmaps_settings->item('data_transfer') == 'curl' && gmaps_helper::is_curl_loaded())
            {
                $this->adapter  = new \Ivory\HttpAdapter\CurlHttpAdapter();
                gmaps_helper::log('Using cURL to load the results from the geocoder', 3);
                //$this->debug[] = 'Use cURL to load the results from the geocoder';
            }
            else
            {
                $this->adapter  = new \Ivory\HttpAdapter\BuzzHttpAdapter();
                gmaps_helper::log('Using HTTP BUZZ to load the results from the geocoder', 3);
                //$this->debug[] = 'Use HTTP BUZZ to load the results from the geocoder';
            }
            $this->geocoder = new \Geocoder\ProviderAggregator();

            //empty provider array
            $this->providers = array();

            //which group
            if($group == 'geocoding')
            {
                //the geocoders
                $geocoding_providers = ee()->gmaps_settings->item('geocoding_providers');

                //set the key
                //$this->google_maps_key = $this->google_maps_key != '' ? $this->google_maps_key : ee()->gmaps_settings->item('google_api_key');

                // Google Maps - Address-Based geocoding and reverse geocoding provider;
                $this->providers['google_maps'] = new \Geocoder\Provider\GoogleMaps($this->adapter, null, null, true, ee()->gmaps_settings->item('google_api_key_server'));
                $this->providers['google_maps']->setLocale(gmaps_helper::get_ee_cache('lang'));

                //Bing maps - Address-Based geocoding and reverse geocoding provider;
                if(in_array('bing_maps', $geocoding_providers) && ee()->gmaps_settings->item('bing_maps_key') != '' ) {
                    $this->providers['bing_maps'] = new \Geocoder\Provider\BingMaps($this->adapter, ee()->gmaps_settings->item('bing_maps_key'));
                }

                // Openstreetmap - Address-Based geocoding and reverse geocoding provider;
                if(in_array('openstreetmap', $geocoding_providers))
                {
                    $this->providers['openstreetmap'] = new \Geocoder\Provider\OpenStreetMap($this->adapter);
                }

                // MapQuest - Address-Based geocoding and reverse geocoding provider;
                if(in_array('mapquest', $geocoding_providers) && ee()->gmaps_settings->item('map_quest_key') != '') {
                    $this->providers['mapquest'] = new \Geocoder\Provider\MapQuest($this->adapter, ee()->gmaps_settings->item('map_quest_key'));
                }

                // Yandex - Address-Based geocoding and reverse geocoding provider;
                if(in_array('yandex', $geocoding_providers))
                {
                    $this->providers['yandex'] = new \Geocoder\Provider\Yandex($this->adapter);
                }

                // TOMTOM - as Address-Based geocoding and reverse geocoding provider;
                if(in_array('tomtom', $geocoding_providers) && ee()->gmaps_settings->item('tom_tom_key') != '') {
                    $this->providers['tomtom'] = new \Geocoder\Provider\TomTom($this->adapter, ee()->gmaps_settings->item('tom_tom_key'));
                }

                // Nominatim - as Address-Based geocoding and reverse geocoding provider;
                if(in_array('nominatim', $geocoding_providers))
                {
                    $this->providers['nominatim'] = new \Geocoder\Provider\Nominatim($this->adapter, 'http://nominatim.openstreetmap.org/');
                }
            }

            // Register IP-Based providers
            else if($group == 'ip')
            {
                //GeoIp provider is an extension of PHP.
                //Rarly used but we will support this
                //http://nl3.php.net/manual/en/book.geoip.php
                try
                {
                    $this->providers['GeoipProvider'] = new \Geocoder\Provider\Geoip($this->adapter);
                }
                catch (Exception $e)
                {
                    gmaps_helper::log('Unable to use the GeoIp Provider', 2);
                    //$this->debug[] = 'Unable to use the GeoIp Provider';
                }

                //normal GeoIP providers
                $this->providers['FreeGeoIpProvider'] = new \Geocoder\Provider\FreeGeoIp($this->adapter);
                $this->providers['HostIpProvider'] = new \Geocoder\Provider\HostIp($this->adapter);

            }

            //register the providers
            $this->geocoder->registerProviders($this->providers);
        }
    }

    // ----------------------------------------------------------------------------------

    /**
     * _geocode_latlng
     *
     * @param array $latlng
     * @return array [type]
     */
    public function geocode_latlng($latlng = array())
    {
        //init the geococer includes
        $this->load_geocoder('geocoding');

        //default vars
        $geocoded_array = array();

        //query results
        foreach($latlng as $key => $ll)
        {
            //is latlng?
            if(!$this->is_latlng($ll))
            {
                gmaps_helper::log('Not an latlng coordinates: '.$ll, 2);
                unset($latlng[$key]);
                continue;
            }

            //get the result if needed and parse it through the cache object
            $result = ee()->gmaps_cache_model->init($key = $ll, array('search_location' => $ll), 'geocode', function($search_location){
                $providers = $this->geocoder->getProviders();

                $i = 1;
                foreach($providers as $provider=>$v)
                {
                    //use an provider
                    $this->geocoder->using($provider);

                    //get the address
                    try
                    {
                        $ll = explode(',', $search_location);
                        $ll[0] = isset($ll[0]) ? $ll[0] : '';
                        $ll[1] = isset($ll[1]) ? $ll[1] : '';
                        $result = $this->geocoder->reverse($ll[0], $ll[1]);
                    }
                    catch (Exception $e)
                    {
                        gmaps_helper::log($e->getMessage(), 2, true);
                        continue;
                    }

                    //we got result
                    if($result->count() > 0)
                    {
                        $result = $result->first();

                        return array(
                            'location_name' => $search_location,
                            'result' => $result,
                            'mapping' => array(
                                'address' 		=> trim($this->set_default_title($result)),
                                'lat'			=> $result->getLatitude(),
                                'lng'			=> $result->getLongitude(),
                                'geocoder'		=> $provider
                            )
                        );
                    }

                    //not found
                    if($i == count($this->providers))
                    {
                        return array(
                            'location_name' => $search_location,
                            'result' => false
                        );
                    }
                    $i++;
                }
            });

            if($result)
            {
                //prepare data
                $geocoded_array[] = (object)$this->prepare_address($result->result_object);
            }
        }

        //make a collection of it
        $geocoded_collection = new Collection($geocoded_array);

        return $geocoded_collection;
    }

    // ----------------------------------------------------------------------------------

    /**
     * _geocode_latlng
     *
     * @param array $ips
     * @return array [type]
     * @internal param array $addresses
     */
    public function geocode_ip($ips = array())
    {
        //init the geococer includes
        $this->load_geocoder('ip');

        //default vars
        $geocoded_array = array();

        //query results
        foreach($ips as $key => $ip)
        {
            //CURRENT_IP?
            if(strtolower($ip) == 'current_ip')
            {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            //SERVER_IP
            if(strtolower($ip) == 'server_ip')
            {
                $ip = $_SERVER['SERVER_ADDR'];
            }

            //is is valid IP?
            if(!filter_var($ip, FILTER_VALIDATE_IP))
            {
                gmaps_helper::log('Not an valid IP: '.$ip, 2, true);
                //$this->debug[] = 'Not an valid IP: '.$ip;
                unset($ips[$key]);
                continue;
            }

            //get the result if needed and parse it through the cache object
            $result = ee()->gmaps_cache_model->init($key = $ip, array('search_location' => $ip), 'geocode', function($search_location){
                $providers = $this->geocoder->getProviders();

                $i = 1;
                foreach($providers as $provider=>$v)
                {
                    //use an provider
                    $this->geocoder->using($provider);

                    //get the address
                    try
                    {
                        $result = $this->geocoder->geocode($search_location);
                    }
                    catch (Exception $e)
                    {
                        echo $e->getMessage();
                        gmaps_helper::log($e->getMessage(), 2, true);
                        continue;
                    }

                    //we got result
                    if($result->count() > 0)
                    {
                        $result = $result->first();

                        return array(
                            'location_name' => $search_location,
                            'result' => $result,
                            'mapping' => array(
                                'address' 		=> trim($this->set_default_title($result)),
                                'lat'			=> $result->getLatitude(),
                                'lng'			=> $result->getLongitude(),
                                'geocoder'		=> $provider
                            )
                        );
                    }

                    //not found
                    if($i == count($this->providers))
                    {
                        return array(
                            'location_name' => $search_location,
                            'result' => false
                        );
                    }
                    $i++;
                }
            });

            if($result)
            {
                //prepare data
                $geocoded_array[] = (object)$this->prepare_address($result->result_object);
            }

        }

        //make a collection of it
        $geocoded_collection = new Collection($geocoded_array);

        return $geocoded_collection;
    }

    // ----------------------------------------------------------------------------------

    /**
     * geocode_address
     *
     * @param  array $addresses
     * @return array [type]
     */
    public function geocode_address($addresses = array())
    {
        //init the geococer includes
        $this->load_geocoder('geocoding');

        //default vars
        $geocoded_array = array();

        //query results
        foreach($addresses as $address)
        {
            //clean address
            $address = gmaps_helper::transliterate_string($address);
            $address = preg_replace('/\`\~\!\@\#\$\%\^\&\*\(\)\_\+\=\{\[\}\}\\\|:\;\"\'\<\,\>\.\?\//si','---',$address);

            //get the result if needed and parse it through the cache object
            $result = ee()->gmaps_cache_model->init($key = $address, array('search_location' => $address), 'geocode', function($search_location){
                $providers = $this->geocoder->getProviders();

                $i = 1;
                foreach($providers as $provider=>$v)
                {
                    //use an provider
                    $this->geocoder->using($provider);

                    //get the address
                    try
                    {
                        $result = $this->geocoder->geocode($search_location);
                    }
                    catch (Exception $e)
                    {
                        gmaps_helper::log($e->getMessage(), 2, true);
                        continue;
                    }

                    //we got result
                    if($result->count() > 0)
                    {
                        $result = $result->first();

                        return array(
                            'location_name' => $search_location,
                            'result' => $result,
                            'mapping' => array(
                                'address' 		=> trim($this->set_default_title($result)),
                                'geocoder'		=> $provider
                            )
                        );
                    }

                    //not found
                    if($i == count($this->providers))
                    {
                        return array(
                            'location_name' => $search_location,
                            'result' => false
                        );
                    }
                    $i++;
                }
            });

            if($result)
            {
                //prepare data
                $geocoded_array[] = (object)$this->prepare_address($result->result_object);
            }
        }

        //make a collection of it
        $geocoded_collection = new Collection($geocoded_array);

        return $geocoded_collection;
    }

    // ----------------------------------------------------------------------------------

    /**
     * Helper function to get a value from the geocode object and return it or generate an error message
     *
     * @param $field
     * @param $result
     * @param $error_message
     * @return string
     */
    public function get_field($field, $result, $error_message)
    {
        if($result->count() > 0)
        {
            $row = $result->first();
            return isset($row->{$field}) ? $row->{$field} : '';
        }

        //no result
        gmaps_helper::log($error_message, 2, true);
        return '';
    }

    // ----------------------------------------------------------------------------------

    /**
     * Helper function to pluck a value from the geocode object and return it or generate an error message
     *
     * @param $field
     * @param $result
     * @param $error_message
     * @return string
     */
    public function pluck_field($field, $result, $error_message)
    {
        if($result->count() > 0)
        {
            return $result->pluck($field);
        }

        //no result
        gmaps_helper::log($error_message, 2, true);
        return array();
    }

    // ----------------------------------------------------------------------------------

    /**
     * set a default title
     * @param  [type] $serialize_address_object
     * @return mixed|string [type]
     */
    private function set_default_title($result_object)
    {
        $address_placehoders = array('[streetName]','[streetNumber]','[city]','[country]', '[countryCode]', '[zipCode]');
        $addess_format = '[streetName] [streetNumber], [city], [country]';

        $address_data = array(
            $result_object->getStreetName(),
            $result_object->getStreetNumber(),
            $result_object->getLocality(),
            $result_object->getCountry(),
            $result_object->getCountryCode(),
            $result_object->getPostalCode()
        );
        $return = str_replace($address_placehoders, $address_data, $addess_format);
        //remove trailing comma
        $return = preg_replace('/,/', '', $return,1);
        //remove empty values
        $return = gmaps_helper::remove_empty_array_values(explode(',', $return));

        if(count($return) < 2)
        {
            return trim(array_shift($return));
        }
        else
        {
            return trim(implode(',', $return));
        }
    }

    // ----------------------------------------------------------------------------------

    /**
     * _prepare_all_address
     *
     * Names are in EE format because we give this right back to the template parser
     *
     * @param  [type] $serialize_address_object
     * @return array [type]
     */
    private function prepare_address($result_object)
    {
        //default value for the bounds
        if($result_object->getBounds() != '')
        {
            $bounds = $result_object->getBounds()->toArray();
        }
        else
        {
            $bounds = array(
                'south'=>'',
                'west'=>'',
                'north'=>'',
                'east'=>'',
            );
        }

        //return values
        return array(
            'latitude' => $result_object->getLatitude(),
            'lat' => $result_object->getLatitude(),
            'longitude'=> $result_object->getLongitude(),
            'lng'=> $result_object->getLongitude(),
            'latlng'=> $result_object->getLatitude().','.$result_object->getLongitude(),
            'bounds' => array($bounds),
            'street_name' => $result_object->getStreetName(),
            'street_number' => $result_object->getStreetNumber(),
            'city' => $result_object->getLocality(),
            'zipcode' => $result_object->getPostalCode(),
            'city_district' => $result_object->getSubLocality(),
            'country' => $result_object->getCountry()->getName(),
            'country_code' => $result_object->getCountryCode(),
            'timezone' => $result_object->getTimezone(),
            'default_title' => $this->set_default_title($result_object),
            'address' => $this->set_default_title($result_object),
        );
    }

    // ----------------------------------------------------------------------------------

    //is latlng
    private function is_latlng($latlng)
    {
        if(preg_match('/([0-9.-]+).+?([0-9.-]+)/', $latlng, $matches))
        {
            return true;
        }
        return false;
    }


} // END CLASS
