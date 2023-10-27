<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API lib for the fieldtype
 *
 * @package		Module name
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl
 * @copyright 	Copyright (c) 2016 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once(PATH_THIRD.'gmaps_fieldtype/config.php');


class Gmaps_fieldtype_api
{
    /*The fieldtype settings*/
    public $setttings;

    //-------------------------------------------------------

    /**
     * Get the right value for the {exp:gmaps:map}
     *
     * @param $field_names
     * @param $data
     * @param $params
     * @param string $default_value
     * @return string
     */
    public function setParam($field_names, $data, $params, $default_value = '')
    {
        //0 = params
        //1 = data
        //2 = settings
        $field_names = explode('|', $field_names);
        $params_fieldname = $field_names[0];
        $data_fieldname = isset($field_names[1]) ? $field_names[1] : $params_fieldname;
        $settings_fieldname = isset($field_names[2]) ? $field_names[2] : $params_fieldname;

        //default value
        $return_value = '';

        //do the checks
        if(isset($params[$params_fieldname]))
        {
            $return_value = $params[$params_fieldname];
        }
        //in the data we should check the map array
        else if(isset($data['map'][$data_fieldname]))
        {
            $return_value = $data['map'][$data_fieldname];
        }
        //the settings have a prefix
        else if(isset($this->settings['gmaps_fieldtype_'.$settings_fieldname]))
        {
            $return_value = $this->settings['gmaps_fieldtype_'.$settings_fieldname];
        }
        else if($default_value != '')
        {
            $return_value = $default_value;
        }

        //only return something when needded
        //format would be e.g. zoom="10"
        if($return_value != '')
        {
            //check if bool
            if(is_bool($return_value) || $return_value == 'true' || $return_value == 'false')
            {
                $return_value = $return_value ? 'yes' : 'no';
            }

            //build the param
            return $params_fieldname.'="'.$return_value.'"';
        }
    }

    //-------------------------------------------------------

    /**
     * Set the markers
     *
     * @param $data
     * @param $entry_data
     * @return string
     */
    public function setMarkers($data, $entry_data)
    {
        $tags = array();

        if(isset($data['markers']) && !empty($data['markers']))
        {
            //set the markers correct, to output also the infowindows
            foreach($data['markers'] as $key => $marker)
            {
                //parse the data for the title
                $marker->title = utf8_decode(ee()->TMPL->parse_variables($marker->title, array($entry_data)));

                //set the content and parse it
                if(isset($marker->content))
                {
                    $marker->infoWindow = new stdClass;
                    $marker->infoWindow->content = utf8_decode(ee()->TMPL->parse_variables($marker->content, array($entry_data)));
                }

                //set the tag
                $tags[] = '{gmaps:add_marker 
                    title="'.$marker->title.'"
                    latlng="'.$marker->lat.','.$marker->lng.'"
                    '.(isset($marker->icon) ? 'icon:url="'.$marker->icon.'"' : '').'
                    '.(isset($marker->infoWindow->content) && $marker->infoWindow->content != '' ? 'infowindow="'.$marker->infoWindow->content.'"' : '').'
                }';
            }
        }

        return implode(' ', $tags);
    }

    //-------------------------------------------------------

    /**
     * Set the polylines
     *
     * @param $data
     * @param $entry_data
     * @return string
     */
    public function setPolylines($data, $entry_data)
    {
        $tags = array();

        if(isset($data['polylines']) && !empty($data['polylines']))
        {
            foreach($data['polylines'] as $polyline)
            {
                //set the path
                $paths = array();
                foreach($polyline->path as $path)
                {
                    $paths[] = $path[0].','.$path[1];
                }

                $tags[] = '{gmaps:add_polyline 
                    latlng="'.implode('|', $paths).'"
                    '.(isset($polyline->strokeColor) ? 'stroke_color="'.$polyline->strokeColor.'"' : '').'
                    '.(isset($polyline->strokeOpacity) ? 'stroke_opacity="'.$polyline->strokeOpacity.'"' : '').'
                    '.(isset($polyline->strokeWeight) ? 'stroke_weight="'.$polyline->strokeWeight.'"' : '').'
                }';
            }
        }

        return implode(' ', $tags);
    }

    //-------------------------------------------------------

    /**
     * Set the polygons
     *
     * @param $data
     * @param $entry_data
     * @return string
     */
    public function setPolygons($data, $entry_data)
    {
        $tags = array();

        if(isset($data['polygons']) && !empty($data['polygons']))
        {
            foreach($data['polygons'] as $polygons)
            {
                //set the path
                $paths = array();
                foreach($polygons->paths as $path)
                {
                    $paths[] = $path[0].','.$path[1];
                }

                $tags[] = '{gmaps:add_polygon 
                    latlng="'.implode('|', $paths).'"
                    '.(isset($polygons->strokeColor) ? 'stroke_color="'.$polygons->strokeColor.'"' : '').'
                    '.(isset($polygons->strokeOpacity) ? 'stroke_opacity="'.$polygons->strokeOpacity.'"' : '').'
                    '.(isset($polygons->strokeWeight) ? 'stroke_weight="'.$polygons->strokeWeight.'"' : '').'
                    '.(isset($polygons->fillColor) ? 'fill_color="'.$polygons->fillColor.'"' : '').'
                    '.(isset($polygons->fillOpacity) ? 'fill_opacity="'.$polygons->fillOpacity.'"' : '').'
                }';
            }
        }


        return implode(' ', $tags);
    }

    //-------------------------------------------------------

    /**
     * Set the circles
     *
     * @param $data
     * @param $entry_data
     * @return string
     */
    public function setCircles($data, $entry_data)
    {
        $tags = array();

        if(isset($data['circles']) && !empty($data['circles']))
        {
            foreach($data['circles'] as $circle)
            {

                $tags[] = '{gmaps:add_circle
                    latlng="'.$circle->lat.','.$circle->lng.'"
                    radius="'.$circle->radius.'"
                    '.(isset($circle->strokeColor) ? 'stroke_color="'.$circle->strokeColor.'"' : '').'
                    '.(isset($circle->strokeOpacity) ? 'stroke_opacity="'.$circle->strokeOpacity.'"' : '').'
                    '.(isset($circle->strokeWeight) ? 'stroke_weight="'.$circle->strokeWeight.'"' : '').'
                    '.(isset($circle->fillColor) ? 'fill_color="'.$circle->fillColor.'"' : '').'
                    '.(isset($circle->fillOpacity) ? 'fill_opacity="'.$circle->fillOpacity.'"' : '').'
                }';
            }
        }

        return implode(' ', $tags);
    }

    //-------------------------------------------------------

    /**
     * Set the Rectangles
     *
     * @param $data
     * @param $entry_data
     * @return string
     */
    public function setRectangles($data, $entry_data)
    {
        $tags = array();

        if(isset($data['rectangles']) && !empty($data['rectangles']))
        {
            foreach($data['rectangles'] as $rectangle)
            {
                //set the bounds
                $bounds = explode(',', $rectangle->bounds);

                $tags[] = '{gmaps:add_rectangle
                    latlng="'.$bounds[0].','.$bounds[1].'|'.$bounds[2].','.$bounds[3].'"
                    '.(isset($circle->strokeColor) ? 'stroke_color="'.$circle->strokeColor.'"' : '').'
                    '.(isset($circle->strokeOpacity) ? 'stroke_opacity="'.$circle->strokeOpacity.'"' : '').'
                    '.(isset($circle->strokeWeight) ? 'stroke_weight="'.$circle->strokeWeight.'"' : '').'
                    '.(isset($circle->fillColor) ? 'fill_color="'.$circle->fillColor.'"' : '').'
                    '.(isset($circle->fillOpacity) ? 'fill_opacity="'.$circle->fillOpacity.'"' : '').'
                }';
            }
        }

        return implode(' ', $tags);
    }


} // END CLASS
