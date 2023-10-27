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

class Gmaps_api
{
    public $classes = array(
        'gmaps_marker',
        'gmaps_route',
        'gmaps_circle',
        'gmaps_rectangle',
        'gmaps_polygon',
        'gmaps_polyline',
        'gmaps_fusion_table',
        'gmaps_kml',
        'gmaps_places',
        'gmaps_fit_map',
    );
    
    /**
     * constructor.
     */
    public function __construct()
    {
        //load the api classes
        foreach($this->classes as $class)
        {
            ee()->load->library('api/'.$class);
        }
    }

    // ----------------------------------------------------------------------------------

    /**
     * Add a map_id to all tags
     *
     * @param $tagdata
     * @param $map_id
     * @return mixed
     */
    public function add_map_id($tagdata, $map_id)
    {
        foreach($this->classes as $class)
        {
            if(isset(ee()->{$class}->tag))
            {
                $tagdata = str_replace('{'.ee()->{$class}->tag, '{'.ee()->{$class}->tag.' map_id="'.$map_id.'"', $tagdata);
            }
        }

        return $tagdata;
    }

    // ----------------------------------------------------------------------------------

    /**
     * Get all tags and wrap it with a {gmaps:parse} tag
     *
     * @param $tagdata
     * @param $map_id
     * @return mixed
     */
//    public function wrap_it($tagdata, $map_id)
//    {
//        foreach($this->classes as $class)
//        {
//            if(isset(ee()->{$class}->tag))
//            {
//                //determine if we need to catch tagpair or singlepair
//                $regex = ee()->{$class}->tagpair ?
//                    "/".LD.ee()->{$class}->tag."(.*?)".RD."(.*?)".LD.'\/'.ee()->{$class}->tag.RD."$/ims"
//                    :
//                    "/".LD.ee()->{$class}->tag."(.*?)".RD."$/ims";
//
//                if (preg_match_all($regex, $tagdata, $matches)!=0)
//                {
//                    foreach($matches[0] as $match)
//                    {
//                        $tagdata = str_replace($match, '{gmaps:parse map_id="'.$map_id.'"}'.$match.'{/gmaps:parse}', $tagdata);
//                    }
//                }
//            }
//        }
//
//       return $tagdata;
//    }

    // ----------------------------------------------------------------------------------

    /**
     * Get all gmaps:parse tags
     *
     * @param $tagdata
     * @return mixed
     */
//    public function get_parse_tags($tagdata)
//    {
//        $tags = array();
//
//        if (preg_match_all("/".LD."gmaps:parse"."(.*?)".RD."(.*?)".LD."\/gmaps:parse".RD."/ims", $tagdata, $matches)!=0)
//        {
//            foreach($matches[0] as $key => $match)
//            {
//                $params = ee()->functions->assign_parameters($matches[1][$key]);
//                $tags[$params['map_id']][] = array(
//                    'params' => $params,
//                    'tag' => $matches[2][$key]
//                );
//
//                //remove the trace
//                $tagdata = str_replace($match, '', $tagdata);
//            }
//        }
//
//        return array(
//            'tags' => $tags,
//            'tagdata' => $tagdata
//        );
//    }

    // ----------------------------------------------------------------------------------

    /**
     * parse all tags
     *
     * @param $tagdata
     * @param bool $early_parse
     * @return string
     */
    public function parse($tagdata, $early_parse = false)
    {
        foreach($this->classes as $class)
        {
            if(!$early_parse && !ee()->{$class}->early_parse)
            {
                ee()->{$class}->set_tagdata($tagdata);
                $tagdata = ee()->{$class}->fetch();
            }
            else if($early_parse && ee()->{$class}->early_parse)
            {
                ee()->{$class}->set_tagdata($tagdata);
                $tagdata = ee()->{$class}->fetch();
            }
        }

        //return the tagdata
        return $tagdata;
    }

} // END CLASS
