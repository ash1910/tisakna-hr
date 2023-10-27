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

abstract class Gmaps_api_base
{

    public $get_elevation = false;
    public $tagdata = null;
    public $params = array();
    public $tag = null;

    /**
     * Gmaps_api_base constructor.
     */
    public function __construct()
    {
        $this->tagdata = ee()->TMPL->tagdata;
    }

    // ----------------------------------------------------------------------------------

    /**
     * set the tagdata
     *
     * @param string $tagdata
     * @internal param $tadata
     */
    public function set_tagdata($tagdata = '')
    {
        $this->tagdata = $tagdata;
    }

    // ----------------------------------------------------------------------------------

    /**
     * set the tag to parse
     *
     * @param string $tag
     * @internal param $tag
     */
    public function set_tag($tag = '')
    {
        $this->tag = $tag;
    }

    // ----------------------------------------------------------------------------------

    /**
     * @param int $map_id
     * @param array $data
     * @param string $inner_tagdata
     * @return mixed
     */
    abstract function build($map_id = 0, $data = array(), $inner_tagdata = '');

    // ----------------------------------------------------------------------------------

    /**
     * fetch the data
     *
     * @return array
     */
    public function _fetch()
    {
        //get the data from the tag
        $pre_fetch_data = $this->_pre_fetch();

        if(!empty($pre_fetch_data))
        {
            foreach($pre_fetch_data as $match)
            {
                //get the map_id
                $map_id = gmaps_helper::array_value($match['params'], 'map_id');

                //parse the tag
                $parsed = $this->build($map_id, $match['params'], $match['inner_tagdata']);

                //place $parsed in the html
                $this->tagdata = str_replace($match['tag_marker'], $parsed, $this->tagdata);

            }
        }

        return $this->tagdata;
    }

    // ----------------------------------------------------------------------------------

    /**
     * fetch the any tag and assign the
     *
     * Also there is a optional tagdata to get a little control over the data to parse
     *
     * @return array
     */
    public function _pre_fetch()
    {
       $regex = $this->tagpair ? "/".LD.$this->tag."(.*?)".RD."(.*?)".LD."\/".$this->tag.RD."/ims" : "/".LD.$this->tag."(.*?)".RD."/ims";

        $return = array();

        if (preg_match_all($regex, $this->tagdata, $matches) != 0)
        {
            if(!empty($matches[0]))
            {
                foreach($matches[0] as $key => $match)
                {
                    $params = ee()->functions->assign_parameters($matches[1][$key]);
                    $inner_tagdata = isset($matches[2]) ? $matches[2][$key] : '';
                    $tag_marker = '{!-- gmaps:tag_marker:'.md5(time().rand(0, 100000)).' --}';

                    $return[] = array(
                        'params' => $params,
                        'inner_tagdata' => $inner_tagdata,
                        'tag_marker' => $tag_marker
                    );

                    //remove tag
                    $this->tagdata = str_replace($match, $tag_marker, $this->tagdata);
                }
            }
        }

        return $return;
    }

    // ----------------------------------------------------------------------------------

    /**
     * Parse Javascript vars like [location]
     *
     * @param string $string
     * @param $latlng
     * @param $location
     * @return string
     */
    public function parse_js_vars($string = '', $latlng, $location, $geocode_result = null)
    {
        //[location]
        $string = str_replace('[location]', $location, $string);

        //[route_to_url]
        $string = str_replace('[route_to_url]', 'https://maps.google.com/maps?daddr='.$latlng[0].','.$latlng[1], $string);

        //[route_url]
        $string = str_replace('[route_from_url]', 'https://maps.google.com/maps?saddr='.$latlng[0].','.$latlng[1], $string);

        //[map_url]
        $string = str_replace('[map_url]', 'https://maps.google.com/maps?q='.$latlng[0].','.$latlng[1], $string);

        // convert the gecoding result to vars
        if($geocode_result != null)
        {
            foreach((array)$geocode_result as $key=>$val)
            {
                if(is_string($val) || $val == '')
                {
                    $string = str_replace('['.$key.']', $val, $string);
                }
            }
        }

        //fetch the elevation if needed
        if($this->get_elevation && ee()->addons_model->module_installed('gmaps_services'))
        {
            //load package
            ee()->load->add_package_path(PATH_THIRD . 'gmaps_services/');
            ee()->load->library('gmaps_services_lib');
            ee()->load->remove_package_path(PATH_THIRD . 'gmaps_services/');

            $elevation_result = ee()->gmaps_services_lib->fetch_elevation(implode(',', $latlng));

            //[elevation]
            $string = str_replace('[elevation]', $elevation_result['elevation'], $string);
        }

        return trim($string);
    }

    // ----------------------------------------------------------------------------------

    /**
     * convert_array_to_js
     *
     * @param  [type] $data
     * @return string
     */
    public function convert_array_to_js($data)
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

    /**
     * @param $pattern
     * @param $array
     * @return array|bool
     */
    public function preg_array_key_exists($pattern, $array)
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
     *
     * @param  [type]  $array
     * @return boolean
     */
    public function is_assoc($array)
    {
        foreach ( array_keys ( $array ) as $k => $v )
        {
            if ($k !== $v)
                return true;
        }
        return false;
    }

    // ----------------------------------------------------------------------

    /**
     * Format the key array where a marker can be found on
     *
     * @param $address_keys
     * @param $marker_keys
     * @return array|string
     * @internal param $keys
     */
    public function parse_keys($address_keys, $marker_keys)
    {
        $keys = array();

        $address_keys = trim($address_keys);
        if($address_keys != '')
        {
            $keys[] = trim($address_keys);
        }


        $marker_keys = !empty($keys) && $marker_keys != '' ? ':'.$marker_keys : $marker_keys;

        return implode(':', $keys).$marker_keys;
    }

    // ----------------------------------------------------------------------------------

    /**
     * Wrap it in a script tag
     *
     * @param  string $js
     * @param bool $jquery_ready
     * @param bool $gmaps_ready
     * @return string
     */
    public function script($js = '', $jquery_ready = true, $gmaps_ready = true)
    {

        $js = $gmaps_ready ? $this->gmaps_ready($js) : $js;

        $js = $jquery_ready ? $this->jquery_ready($js) : $js;

        $js = ee()->gmaps->minify_html_output(' <script>'.$js.'</script>');

        //but first check if we need it for later?
        if(gmaps_helper::get_ee_cache('catch_output_js'))
        {
            $js .= gmaps_helper::get_ee_cache('output_js_low_prio');
            gmaps_helper::set_ee_cache('output_js_low_prio', $js, true);
            return '';
        }

       return $js;
    }

    // ----------------------------------------------------------------------------------

    /**
     * Wrap it with a jquery ready
     *
     * @param  string  $js
     * @return string
     */
    public function jquery_ready($js = '')
    {
        return 	'jQuery(window).ready(function(){
           '.$js.'
        });';
    }

    // ----------------------------------------------------------------------------------

    /**
     * Wrap it with a Gmaps.ready
     *
     * @param  string  $js
     * @return string
     */
    public function gmaps_ready($js = '')
    {
        return 	'EE_GMAPS.ready(function(){
           '.$js.'
        });';
    }

    // ---------------------------------------------------------------------

} // END CLASS
