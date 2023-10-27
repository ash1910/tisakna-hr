<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * gmaps_fieldtype
 *
 * @package     Gmaps fieldtype module
 * @category    Modules
 * @author      Rein de Vries <info@reinos.nl>
 * @link        http://reinos.nl
 * @copyright   Copyright (c) 2013 Reinos.nl Internet Media
 */

include(PATH_THIRD.'gmaps_fieldtype/config.php');

class Gmaps_fieldtype_ft extends EE_Fieldtype
{

    public $info = array(
        'name'      => GMAPS_FT_NAME,
        'version'   => GMAPS_FT_VERSION,
        'has_global_settings' => 'n'
    );

    public $settings = array();

    public $default_settings = array();

    public $has_array_data = TRUE;

    private $prefix = 'gmaps_fieldtype_';

    // ----------------------------------------------------------------------

    /**
     * Constructor
     *
     * @access public
     *
     * Calls the parent constructor
     */
    public function __construct()
    {
        //load package
        ee()->load->library('gmaps_fieldtype_lib');
        ee()->load->helper('file');

        //set theme url
        $this->theme_url = str_replace('/gmaps/', '/gmaps_fieldtype/', ee()->gmaps_settings->get_setting('theme_url'));

        //load lang file
        ee()->lang->loadfile(GMAPS_FT_MAP);

        //require settings
        require 'settings.php';

    }

    // ----------------------------------------------------------------------
    // Before saving the content to the database
    // ----------------------------------------------------------------------

    /**
     * save (Native EE)
     *
     * @access public
     */
    public function save($data)
    {
        return $this->_save($data);
    }

    // ----------------------------------------------------------------------

    /**
     * save (Low Variables)
     *
     * @access public
     */
    public function save_var_field($data)
    {
        return $this->_save($data);
    }

    // ----------------------------------------------------------------------

    /**
     * save (Content Elements)
     *
     * @access public
     */
    public function save_element($data)
    {
        return $this->_save($data);
    }

    // ----------------------------------------------------------------------

    /**
     * save
     *
     * @access public
     */
    private function _save($data = '')
    {
        ee()->gmaps_fieldtype_lib->create_history_table();

        //save data to the history table
        if($this->content_id() != '')
        {
            //check if the field exists
            $fields = ee()->db->list_fields('channel_data');

            //fetch the old data
            $q = ee()->db->select('field_id_'.$this->id())
                ->from('channel_data')
                ->where('entry_id', $this->content_id())
                ->get();

            if ($q->num_rows() > 0) {
                ee()->db->insert(GMAPS_FT_MAP.'_history', array(
                    'entry_id' => $this->content_id(),
                    'field_id' => $this->id(),
                    'timestamp' => time(),
                    'data' => $q->row()->{$this->field_name}
                ));

                //delete more then 10
                $this->prune_revisions($this->content_id(), $this->id(), 10);
            }
        }

        return $data;
    }

    // ----------------------------------------------------------------------
    // After saving the content to the database, now the ID is availble
    // ----------------------------------------------------------------------

    /**
     * save (Native EE)
     *
     * @access public
     */
    public function post_save($data)
    {
        /* -------------------------------------------
        /* 'gmaps_fieldtype_save_setting' hook.
        /*  - Added: 3.0.8
        */
        if (ee()->extensions->active_hook('gmaps_fieldtype_save_data') === TRUE)
        {
            ee()->extensions->call('gmaps_fieldtype_save_data', $data, $this->content_id(), $this->id());
        }
        // -------------------------------------------
    }

    // ----------------------------------------------------------------------

    /**
     * display_field
     *
     * @access public
    */
    function display_field($data)
    {
        //generate the data
        return $this->_display_gmaps($data);
    }

    // ----------------------------------------------------------------------

    /**
     * display_var_field (Low variables)
     *
     * @access public
    */
    function display_var_field ($data)
    {
        //generate the data
        return $this->_display_gmaps($data, 'low_variables');
    }

    // ----------------------------------------------------------------------
    
    /**
     * display_element (Content Elements)
     *
     * http://www.krea.com/docs/content-elements/element-development/ee2-functions-reference
     *
     * @access public
    */
    function display_element($data)
    {
        return $this->_display_gmaps($data, 'content_elements');
    }

    /**
     * replace_tag
     *
     * @access public
    */
    public function replace_tag($data, $params = array(), $tagdata = FALSE)
    {
    	// Check to see if we are loading a draft into the publish view
		if (isset(ee()->session->cache['ep_better_workflow']['is_draft']) && ee()->session->cache['ep_better_workflow']['is_draft']) 
		{
            if (is_array($data)) $data = implode($data, ',');
		}

        $data = trim($data);
        if ($data == FALSE) return;

        //decode the data string
        $data = $this->_set_map_data($data);

        //get the entry data
        $entry = ee('Model')->get('ChannelEntry')->filter('entry_id', $this->content_id());

        $entry_data = array();
        if($entry->count() > 0)
        {
            $entry_data = $entry->first()->toArray();
        }

        //set the settings on the api
        ee()->gmaps_fieldtype_api->settings = $this->settings;

        $tag = '
            {exp:gmaps:map
                '.ee()->gmaps_fieldtype_api->setParam('zoom|zoom_level', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('zoom_control_position', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('height', $data, $params, '500px').'
                '.ee()->gmaps_fieldtype_api->setParam('center:latlng|center', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('map_types', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('map_type', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('scroll_wheel', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('map_type_control', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('map_type_control_position', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('map_type_control_style', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('street_view_control', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('street_view_control_position', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('overlay:html|google_overlay_html', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('overlay:position|google_overlay_position', $data, $params).'
                
              
                '.ee()->gmaps_fieldtype_api->setParam('zoom:max', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('fit_map', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('focus_current_location', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('show_marker_cluster', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('marker_cluster_style:url', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('marker_cluster_style:size', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('marker_cluster_style:anchor', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('marker_cluster_style:text_size', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('div_id', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('div_class', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('scale_control', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('show_panoramio', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('panoramio_tag', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('width', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('lang', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('cache_time', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('show_traffic', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('show_transit', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('show_bicycling', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('map_style:snazzymap', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('google_maps_key', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('bing_maps_key', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('yahoo_maps_key', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('cloudmade_maps_key', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('address_format', $data, $params).'
                '.ee()->gmaps_fieldtype_api->setParam('gesture_handling', $data, $params).'
              
            }
                '.ee()->gmaps_fieldtype_api->setMarkers($data, $entry_data).'
                '.ee()->gmaps_fieldtype_api->setPolylines($data, $entry_data).'
                '.ee()->gmaps_fieldtype_api->setPolygons($data, $entry_data).'
                '.ee()->gmaps_fieldtype_api->setCircles($data, $entry_data).'
                '.ee()->gmaps_fieldtype_api->setRectangles($data, $entry_data).'
            {/exp:gmaps:map}
        ';

        return $tag;

    }

    // ----------------------------------------------------------------------

    /**
     * display_var_tag (Low variables)
     *
     * @access public
    */
    public function display_var_tag($var_data, $tagparams, $tagdata)
    {
        return $this->replace_tag($var_data, $tagparams, $tagdata);
    }

    // ----------------------------------------------------------------------

    /**
     * replace_element_tag (Content Elements)
     *
     * @access public
    */
    public function replace_element_tag($data, $params = array(), $tagdata)
    {
        return $this->replace_tag($data, $params, $tagdata);
    }

    // ----------------------------------------------------------------------
    
    /**
     * replace_tag_catchall
     *
     * @access public
    */
    function replace_tag_catchall($file_info, $params = array(), $tagdata = FALSE, $modifier)
    {
       //decode the data string
        $data = $this->_set_map_data($file_info);

        //explode the modifier on the :
        $modifier = explode(':', $modifier);

        //what kind of variable is it?
        switch($modifier[0])
        {
            // {field_name:markers}{/field_name:markers}
            case 'markers' : 
                $r = '';
                //create all marker vars
                if(isset($data['markers']) && $tagdata != false)
                {
                    foreach($data['markers'] as $val)
                    {
                        $val->title = utf8_decode($val->title);
                        $val->content = utf8_decode($val->content);

                        //check if add_marker exists
                        if (preg_match('/{add_marker(.*)}/', $tagdata, $matches) != 0)
                        {
                            if(isset($matches[0]))
                            {
                                $val->add_marker = '{gmaps:add_marker latlng="'.$val->lat.','.$val->lng.'" title="'.$val->title.'" icon:url="'.$val->icon.'" infowindow="'.$val->content.'" '. $matches[1].'}';

                            }
                        }

                        $r .= ee()->TMPL->parse_variables($tagdata, array((array)$val));
                    }
                }
                return $r;
            break;
            // Create the marker vars
            case 'marker' : 
                 
                //create all marker vars
                if(isset($data['markers']))
                {

                    //do we need a specific item?
                    if(is_numeric($modifier[1]))
                    {
                        $nr = ($modifier[1]-1);
                        //{field_name:marker:1:latlng}
                        if($modifier[2] == 'latlng')
                        {
                            return isset($data['markers'][$nr]->lat) && isset($data['markers'][$nr]->lng) ? $data['markers'][$nr]->lat.', '.$data['markers'][$nr]->lng : '';
                        }

                        //{field_name:marker:1:icon}
                        if($modifier[2] == 'icon')
                        {
                            return isset($data['markers'][$nr]->icon) ? $data['markers'][$nr]->icon : '';
                        }

                        //{field_name:marker:1:title}
                        if($modifier[2] == 'title')
                        {
                            return isset($data['markers'][$nr]->title) ? utf8_decode($data['markers'][$nr]->title) : '';
                        }
                    }

                    // {field_name:marker:latlng}
                    if($modifier[1] == 'latlng')
                    {
                        $return = array();
                        foreach($data['markers'] as $marker)
                        {
                            $return[] = $marker->lat.','.$marker->lng;
                        }
                        return implode('|', $return);
                    }

                    // {field_name:marker:icon}
                    if($modifier[1] == 'icon')
                    {
                         $return = array();
                        foreach($data['markers'] as $marker)
                        {
                            $return[] = $marker->icon;
                        }
                        return implode('|', $return);
                    }

                    // {field_name:marker:title}
                    if($modifier[1] == 'title')
                    {
                        $return = array();
                        foreach($data['markers'] as $marker)
                        {
                            $return[] = utf8_decode($marker->title);
                        }
                        return implode('|', $return);
                    }
                }         
            break;

            // {field_name:route:<var>}
            // only for the markers
            case 'route':
                if(isset($modifier[1]) && isset($data['markers']))
                {
                    $marker_total = count($data['markers']);

                    switch($modifier[1])
                    {
                        // {field_name:route:from}
                        case 'from' :
                            return $data['markers'][0]->lat.', '.$data['markers'][0]->lng;
                        break;
                        // {field_name:route:stops}
                        case 'stops' :
                            if($marker_total > 2)
                            {
                                $stops = array_slice($data['markers'], 1, ($marker_total-2));
                                $return = array();
                                foreach($stops as $marker)
                                {
                                    $return[] = $marker->lat.', '.$marker->lng;
                                }
                                return implode('|', $return);
                            }
                        break;
                        // {field_name:route:to}
                        case 'to' :
                            return $data['markers'][($marker_total-1)]->lat.', '.$data['markers'][($marker_total-1)]->lng;
                        break;
                    }
                }
            break;

            // Polylines
            /*
                {gmapsfieldtype:polyline[OPTIONAL:1]}
                    {stroke_color}
                    {stroke_opacity}
                    {stroke_weight}
                    {path}
                        {lat}, {lng}
                    {/path}
                {/gmapsfieldtype:polyline[OPTIONAL:1]}

                {gmapsfieldtype:polyline:1:from}<br>
                {gmapsfieldtype:polyline:1:stops}<br>
                {gmapsfieldtype:polyline:1:to}<br>
             */
            case 'polyline':
                if(isset($modifier[1]) && isset($data['polylines']))
                {
                    // {field_name:polyline:<number>}
                    if(is_numeric($modifier[1]) && isset($data['polylines'][$modifier[1]-1]))
                    {
                        $number = ($modifier[1] - 1);

                        $polyline = (array)$data['polylines'][$number];

                        //parse all
                        if(!isset($modifier[2]))
                        {
                            $r = '';
                            //create all polyline vars
                            if($tagdata != false)
                            {

                                $r .= $this->parse_polyline($polyline, $tagdata);
                            }
                            return $r;
                        }

                        //specific things
                        else
                        {
                            $marker_total = count($polyline['path']);

                            switch($modifier[2])
                            {
                                // {field_name:route:from}
                                case 'from' :
                                    return $polyline['path'][0][0].', '.$polyline['path'][0][1];
                                    break;
                                // {field_name:route:stops}
                                case 'stops' :
                                    if($marker_total > 2)
                                    {
                                        $stops = array_slice($polyline['path'], 1, ($marker_total-2));
                                        $return = array();
                                        foreach($stops as $marker)
                                        {
                                            $return[] = $marker[0].', '.$marker[1];
                                        }
                                        return implode('|', $return);
                                    }
                                    break;
                                // {field_name:route:to}
                                case 'to' :
                                    return $polyline['path'][($marker_total-1)][0].', '.$polyline['path'][($marker_total-1)][1];
                                    break;
                            }
                        }
                    }
                }

                // {field_name:polyline}{/field_name:polyline}
                else
                {
                    $r = '';

                    //create all polyline vars
                    if(isset($data['polylines']) && $tagdata != false)
                    {
                        foreach($data['polylines'] as $key => $val)
                        {
                            $r .= $this->parse_polyline($val, $tagdata);
                        }
                    }
                    return $r;
                }
                break;

            // Polygon
            /*
                {gmapsfieldtype:polygon[OPTIONAL:1]}
                    {stroke_color}
                    {stroke_opacity}
                    {stroke_weight}
                    {fill_color}
                    {fill_opacity}
                    {path}
                        {lat}, {lng}
                    {/path}
                {/gmapsfieldtype:polygon[OPTIONAL:1]}

             */
            case 'polygon':
                if(isset($modifier[1]) && isset($data['polygons']))
                {
                    // {field_name:polygon:<number>}
                    if(is_numeric($modifier[1]) && isset($data['polygons'][$modifier[1]-1]))
                    {
                        $number = ($modifier[1] - 1);

                        $polygon = (array)$data['polygons'][$number];

                        //parse all

                        $r = '';
                        //create all polygon vars
                        if($tagdata != false)
                        {

                            $r .= $this->parse_polygon($polygon, $tagdata);
                        }

                        return $r;
                    }
                }

                // {field_name:polygon}{/field_name:polygon}
                else
                {
                    $r = '';

                    //create all polyline vars
                    if(isset($data['polygons']) && $tagdata != false)
                    {
                        foreach($data['polygons'] as $key => $val)
                        {
                            $r .= $this->parse_polygon($val, $tagdata);
                        }
                    }

                    return $r;
                }
                break;

            // Circle
            /*
                {gmapsfieldtype:circle[OPTIONAL:1]}
                    {stroke_color}
                    {stroke_opacity}
                    {stroke_weight}
                    {fill_color}
                    {fill_opacity}
                    {lat}
                    {lng}
                    {radius}
                {/gmapsfieldtype:circle[OPTIONAL:1]}

             */
            case 'circle':
                if(isset($modifier[1]) && isset($data['circles']))
                {
                    // {field_name:circle:<number>}
                    if(is_numeric($modifier[1]) && isset($data['circles'][$modifier[1]-1]))
                    {
                        $number = ($modifier[1] - 1);

                        $circle = (array)$data['circles'][$number];

                        //parse all

                        $r = '';
                        //create all polygon vars
                        if($tagdata != false)
                        {

                            $r .= $this->parse_circle($circle, $tagdata);
                        }

                        return $r;
                    }
                }

                // {field_name:circle}{/field_name:circle}
                else
                {
                    $r = '';

                    //create all polyline vars
                    if(isset($data['circles']) && $tagdata != false)
                    {
                        foreach($data['circles'] as $key => $val)
                        {
                            $r .= $this->parse_circle($val, $tagdata);
                        }
                    }

                    return $r;
                }
                break;

            // Rectangle
            /*
                {gmapsfieldtype:rectangle[OPTIONAL:1]}
                    {stroke_color}
                    {stroke_opacity}
                    {stroke_weight}
                    {fill_color}
                    {fill_opacity}
                    {bounds}
                        {lng}
                        {lng}
                    {/bounds}
                {/gmapsfieldtype:rectangle[OPTIONAL:1]}

             */
            case 'rectangle':
                if(isset($modifier[1]) && isset($data['rectangles']))
                {
                    // {field_name:rectangle:<number>}
                    if(is_numeric($modifier[1]) && isset($data['rectangles'][$modifier[1]-1]))
                    {
                        $number = ($modifier[1] - 1);

                        $rectangle = (array)$data['rectangles'][$number];

                        //parse all

                        $r = '';
                        //create all rectangle vars
                        if($tagdata != false)
                        {

                            $r .= $this->parse_rectangle($rectangle, $tagdata);
                        }

                        return $r;
                    }
                }

                // {field_name:rectangle}{/field_name:rectangle}
                else
                {
                    $r = '';

                    //create all polyline vars
                    if(isset($data['rectangles']) && $tagdata != false)
                    {
                        foreach($data['rectangles'] as $key => $val)
                        {
                            $r .= $this->parse_rectangle($val, $tagdata);
                        }
                    }

                    return $r;
                }
                break;

            // get the map values
            /*
                {gmapsfieldtype:map}
                    {map_types}
                    {map_type}
                    {zoom_level}
                    {scroll_wheel}
                    {zoom_control}
                    {zoom_control_style}
                    {zoom_control_position}
                    {pan_control}
                    {pan_control_position}
                    {map_type_control}
                    {map_type_control_style}
                    {map_type_control_position}
                    {street_view_control}
                    {street_view_control_position}
                    {google_overlay_html}
                    {google_overlay_position}
                    {center}
                {/gmapsfieldtype:map}
             */

            case 'map':
                if(isset($modifier[0]) && isset($data['map']))
                {
                    return ee()->TMPL->parse_variables($tagdata, array($data['map']));
                }
                break;

            // {field_name:has:<var>}
            case 'has':
                if(isset($modifier[1]))
                {
                    $marker_total = count($data['markers']);

                    switch($modifier[1])
                    {
                        // {field_name:has:markers}
                        case 'markers' :
                            return $marker_total != 0 ? true : false;
                        break;
                    }
                }
            break;
        }
    }

    // --------------------------------------------------------------------

     /**
     * Display settings screen
     *
     * @access  public
     */
    private function _display_settings($data, $options = array(), $type = '')
    {
        $return = array();

        if(!empty($this->fieldtype_settings))
        {
            foreach($this->fieldtype_settings as $field=>$options)
            {
                //set the override value
                $override_value = false;
                if(isset($options['override']))
                {
                    $override_value = ee()->gmaps_settings->get_setting($options['override'].'_override');
                }

                //is overrided?
                if($override_value !== false && $override_value !== '')
                {
                    $value = $override_value;

                }
                //default value
                else
                {
                    $value = isset($data[$this->prefix.$options['name']]) ? $data[$this->prefix.$options['name']] : $options['def_value'];
                }

                //global?
                if($options['global'])
                    continue;


                //check if we need to load dynamic data
                if(isset($options['options']) && is_string($options['options']))
                {
                    $split = explode('|', $options['options']);
                    $class = str_replace('class:', '', $split[0]);
                    $method = str_replace('method:', '', $split[1]);

                    $options['options'] = ee()->{$class}->{$method}();
                }

                switch($options['type'])
                {
                    //multiselect
                    case 'm' :
                        $return[] =  array(
                            'title' => $options['label'],
                            'desc' => isset($options['desc']) ? $options['desc'] : '',
                            'fields' => array(
                                $this->prefix.$options['name'] => array(
                                    'type' => 'checkbox',
                                    'choices' => $options['options'],
                                    'value' => $value,
                                )
                            )
                        );
                    break;

                    //select field
                    case 's' :
                        $return[] =  array(
                            'title' => $options['label'],
                            'desc' => isset($options['desc']) ? $options['desc'] : '',
                            'fields' => array(
                                $this->prefix.$options['name'] => array(
                                    'type' => 'select',
                                    'choices' => $options['options'],
                                    'value' => $value,
                                )
                            )
                        );
                        
                    break;

                    //text field
                    default:
                    case 't' :
                        $return[] =  array(
                            'title' => $options['label'],
                            'desc' => isset($options['desc']) ? $options['desc'] : '',
                            'fields' => array(
                                $this->prefix.$options['name'] => array(
                                    'type' => 'text',
                                    'value' => $value,
                                )
                            )
                        );
                    break;
                }
            }
        }

        return $return;
    }

    // --------------------------------------------------------------------
    
    /**
     * Display settings screen (Default EE)
     *
     * @access  public
     */
    function display_settings($data)
    {
        $settings_data = $this->_display_settings($data);

        if ($this->content_type() == 'grid')
        {
            return array('field_options_'.GMAPS_FT_MAP => $settings_data);
        }

        return array(
            'field_options_'.GMAPS_FT_MAP => array(
                'label' => 'field_options',
                'group' => GMAPS_FT_MAP,
                'settings' => $settings_data
            )
        );
    }

    // --------------------------------------------------------------------
    
    /**
     * Display settings screen (Low variables)
     *
     * @access  public
     */
    function display_var_settings($data)
    {
        return $this->_display_settings($data);
    }


    // --------------------------------------------------------------------
    
    /**
     * Display settings screen (Content Elements)
     *
     * @access  public
     */
    function display_element_settings($data)
    {
        return $this->_display_settings($data);
    }

    // --------------------------------------------------------------------
    
    /**
     * save_settings
     *
     * @access public
    */
    private function _save_settings($data, $use_data = false)
    { 
        $return = array();

        if(!empty($this->fieldtype_settings))
        {
            foreach($this->fieldtype_settings as $field=>$options)
            {
                //global?
                if($options['global'])
                    continue;

                $return[$this->prefix.$options['name']] =  $use_data ? $data[$this->prefix.$options['name']] : ee()->input->post($this->prefix.$options['name']);
            }
        }

        return $return;
    }  

    // --------------------------------------------------------------------
    
    /**
     * save_settings (GRID)
     *
     * @access public
    */
    function grid_save_settings($data)
    {  
        $settings = $this->_save_settings($data, true);

        return $settings;
    }

    // --------------------------------------------------------------------

    /**
     * save_settings (Default EE)
     *
     * @access public
     */
    function save_settings($data)
    {
        $settings = $this->_save_settings($data);

        if(isset($this->field_wide) && $this->field_wide)
        {
            $settings['field_wide'] = $this->field_wide;
        }

        return $settings;
    }


    // --------------------------------------------------------------------
    
    /**
     * save_settings (Low Var)
     *
     * @access public
    */
    function save_var_settings($data)
    {
        return $this->_save_settings($data);
    }

    // ----------------------------------------------------------------

	/**
	 * Preps the data for saving
	 * 
	 * @param  mixed $data  
	 * @param  bool $is_new
	 * @param  int $entry_id
	 * @return mixed string            
	 */
	public function webservice_save($data = null, $is_new = false, $entry_id = 0)
	{
		return base64_encode('{"0":'.json_encode($data)."}");
	}

    // ----------------------------------------------------------------

	/**
	 * Validate the field
	 * 
	 * @param  mixed $data  
	 * @return bool            
	 */
	public function webservice_validate($data = null)
	{
		//empty, do nothing
		if(empty($data))
		{
			return true;
		}
		
		//do we have latlng for markers
		if(!isset($data['markers']) || empty($data['markers']))
		{
			$this->validate_error = 'You did not add any marker.';
			return false;
		}
		
		//do we have latlng for markers
		foreach($data['markers'] as &$marker)
		{
			//empty lat lng?
			if($marker['lat'] == '' || $marker['lng'] == '')
			{
				$this->validate_error = 'Some markers do not have a lat lng';
				return false;
			}
			
			if(!isset($marker['icon']))
			{
				$this->validate_error = 'Icon must be set, even if it its null';
				return false;
			}
			
			if(!isset($marker['title']))
			{
				$this->validate_error = 'Title must be set, even if it its null';
				return false;
			}
		}
		
		//polygons, set default
		if(!isset($data['polygons']))
		{
			$this->validate_error = 'Polygons is empty, set this to an array even when you have nothing';
			return false;
		}
		
		//Poliline, set default
		if(!isset($data['polylines']))
		{
			$this->validate_error = 'Polylines is empty, set this to an array even when you have nothing';
			return false;
		}
		
		// do we have some map data 
		if(!isset($data['map']))
		{
			$this->validate_error = 'There is no map setting data';
			return false;
		}
		
		//is there a map choosen
		if(!isset($data['map']['map_type']) || $data['map']['map_type'] == '')
		{
			$this->validate_error = 'There is no map type setting choosen';
			return false;
		}
		
	
		return true;
	}

    // ----------------------------------------------------------------------
    
    /**
	 * Preprocess the data to be returned
	 * 
	 * @param  mixed $data  
	 * @param  string $free_access
	 * @param  int $entry_id
	 * @return mixed string
	 */
	public function webservice_pre_process($data = null, $free_access = false, $entry_id = 0)
	{
        //decode the data string
        $data = $this->_set_map_data($data);

        return $data;
    }

    // ----------------------------------------------------------------------
    
    /**
     * install
     *
     * @access public
    */
    function install()
    {
        $return = array();

        if(!empty($this->fieldtype_settings))
        {
            foreach($this->fieldtype_settings as $field=>$options)
            {
                if(isset($options['global']) && $options['global'])
                {
                    $return[$this->prefix.$options['name']] = $options['def_value'];
                }
            }
        }

        return $return;
    }


    // ----------------------------------------------------------------------

    /**
     * display_global_settings
     *
     * @access public
    */
    function display_global_settings()
    {
        $data = array_merge($this->settings, $_POST);

        $form = '';
        foreach($this->fieldtype_settings as $field=>$options)
        {
            //global?
            if(!$options['global'])
                continue;

            switch($options['type'])
            {
                //multiselect
                case 'm' : 
                    $form .= form_label($options['label'], $options['label']).NBS.form_multiselect($this->prefix.$options['name'], $options['options'], (isset($data[$this->prefix.$options['name']]) ? $data[$this->prefix.$options['name']] : $options['def_value'] )).NBS.NBS.NBS.' ';
                break;

                //select field
                case 's' : 
                    $form .= form_label($options['label'], $options['label']).NBS.form_dropdown($this->prefix.$options['name'], $options['options'], (isset($data[$this->prefix.$options['name']]) ? $data[$this->prefix.$options['name']] : $options['def_value'] )).NBS.NBS.NBS.' ';
                break;

                //text field
                default:
                case 't' : 
                    $form .= form_label($options['label'], $options['label']).NBS.form_input($this->prefix.$options['name'], (isset($data[$this->prefix.$options['name']]) ? $data[$this->prefix.$options['name']] : $options['def_value'] )).NBS.NBS.NBS.' ';
                break;
            }
        }

        return $form;
    }

    // ----------------------------------------------------------------------

    /**
     * save_global_settings
     *
     * @access public
    */
    function save_global_settings()
    {
        return array_merge($this->settings, $_POST);
    }

    // ----------------------------------------------------------------------

    /**
     * display_field
     *
     * @access public
    */
    private function _display_gmaps($data, $type = 'default')
    {
    	// Check to see if we are loading a draft into the publish view
		if (isset(ee()->session->cache['ep_better_workflow']['is_draft']) && ee()->session->cache['ep_better_workflow']['is_draft']) 
		{
		  if (is_array($data)) $data = implode($data, ',');
		}
		
        //save data
        //decode and encode prevent the string for hidden invalid chars in JS
        $data = base64_encode(base64_decode(utf8_encode(trim($data))));

        //fieldname
        switch($type)
        {
            case 'matrix': $field_name = $this->cell_name ;
                break;
            case 'default':
            default : $field_name = $this->field_name;
                break;
        }

        //load the assets
        if(!ee()->session->userdata(GMAPS_FT_MAP.'_init'))
        {
            ee()->session->userdata[GMAPS_FT_MAP.'_init'] = true;
            
            ee()->cp->add_js_script(array('ui' => array('sortable', 'dialog')));

            ee()->cp->add_to_foot('<script type="text/javascript" src="'.$this->theme_url.'js/gmaps_fieldtype.js"></script>');
            ee()->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->theme_url.'css/gmaps_fieldtype.css" />');
            ee()->cp->add_to_head('<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" />');
            //e()->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.ee()->config->item('theme_folder_url').'cp_themes/default/css/jquery-ui-1.8.16.custom.css" />');
            //ee()->view->head_link('css/jquery-ui-1.8.16.custom.css');

            //get the key
            $google_api_key = $this->settings[$this->prefix.'api_key'] != '' ? $this->settings[$this->prefix.'api_key'] : ee()->gmaps_settings->item('google_api_key');

            //init tag
            $tag_init = '{exp:gmaps:init key="'.$google_api_key.'"}';
            $tag_init = gmaps_helper::parse_tags($tag_init);
            ee()->cp->add_to_foot($tag_init);
        }

        //license error?
        if(gmaps_helper::log_has_error())
        {
            $errors = gmaps_helper::get_log();
            return $errors[0][1];
        }

        //default location
        $this->settings[$this->prefix.'location'] = $this->get_custom_setting('location');
        return '<div 
            class="gmaps_field_type_create gmaps_field_type_create_'.$field_name.'"
            data-fieldname="'.$field_name.'"
            data-url="'.ee()->gmaps_settings->get_setting('site_url').'?ACT='.ee()->gmaps_fieldtype_lib->act.'"
            data-data="'.$data.'"
            data-fieldtype="'.$type.'"
            data-iconurl="'.$this->get_custom_setting('icon_url').'"
            data-icondir="'.str_replace("\\", '/', $this->get_custom_setting('icon_dir')).'"
            data-location="'.$this->get_custom_setting('location').'"
            data-max-markers="'.$this->get_custom_setting('max_markers').'"
            data-zoom-level="'.$this->get_custom_setting('zoom_level').'"
            data-show-map-tools="'.$this->safe_implode($this->get_custom_setting('show_map_tools')).'"
            data-show-search-tools="'.$this->safe_implode($this->get_custom_setting('show_search_tools')).'"
            data-show-marker-icon="'.$this->safe_implode($this->get_custom_setting('show_marker_icon')).'"
            data-show-circle-icon="'.$this->safe_implode($this->get_custom_setting('show_circle_icon')).'"
            data-show-polygon-icon="'.$this->safe_implode($this->get_custom_setting('show_polygon_icon')).'"
            data-show-polyline-icon="'.$this->safe_implode($this->get_custom_setting('show_polyline_icon')).'"
            data-show-rectangle-icon="'.$this->safe_implode($this->get_custom_setting('show_rectangle_icon')).'"
            data-auto-center="'.$this->get_custom_setting('auto_center').'"
            data-height="'.$this->get_custom_setting('height').'"
        >
            '.form_input($field_name, $data, 'id="'.$field_name.'" style="display: none;"').'
            <span class="fa fa-spinner fa-pulse icon-spinner icon-spin"></span> Učitavanje Google karte
        </div>';
    }

    // ----------------------------------------------------------------------

    /**
     * _set_map_data
     *
     * @access private
    */
    private function _set_map_data($data)
    {
        $data = json_decode(utf8_encode(base64_decode($data)));

        $new_data = array(
            'map' => array(
                'map_types' => '',
                'map_type' => '',
                'zoom_level' => '',
                'scroll_wheel' => '',
                'zoom_control' => '',
                'zoom_control_position' => '',
                'map_type_control' => '',
                'map_type_control_style' => '',
                'map_type_control_position' => '',
                'street_view_control' => '',
                'street_view_control_position' => '',
                'google_overlay_html' => '',
                'google_overlay_position' => '',
                'center' => '',
            ),
            'markers' => array(
                'latlng' => '',
                'icon' => '',
                'title' => ''
            ),
            'polygons' => '',
            'polylines' => '',
            'circles' => '',
            'rectangles' => '',
            'latlngs' => ''
        );

        if(!empty($data))
        {
            foreach($data as $key=>$val)
            {
                //map data
                if(isset($val->map))
                {
                    foreach($val->map as $k=>$v)
                    {
                        if(is_array($v))
                        {
                            $new_data['map'][$k] = implode('|', $v);
                        }
                        else
                        {
                            $new_data['map'][$k] = $v;
                        }    
                    }
                } 

                if(isset($val->markers))
                {
                    $new_data['markers'] = $val->markers;

                    //set the total
                    $total_results = count($new_data['markers']);

                    if(!empty($new_data['markers']))
                    {
                        foreach($new_data['markers'] as $key => $marker)
                        {
                            $new_data['latlngs'][] = $marker->lat.','.$marker->lng;
                            $new_data['title'][$key] = utf8_decode($marker->title);
                           
                            //add {count} and {abs_count} and {total_results}
                            $new_data['markers'][$key]->count = $key + 1;
                            $new_data['markers'][$key]->abs_count = $key;
                            $new_data['markers'][$key]->total_results = $total_results; 
                        }
                        
                        $new_data['latlngs'] = implode('|', $new_data['latlngs']);
                    }
                }

                if(isset($val->polylines))
                {
                    $new_data['polylines'] = $val->polylines; 
                }
                if(isset($val->polygons))
                {
                    $new_data['polygons'] = $val->polygons; 
                }
                if(isset($val->circles))
                {
                    $new_data['circles'] = $val->circles; 
                }
                if(isset($val->rectangles))
                {
                    $new_data['rectangles'] = $val->rectangles; 
                }
            }
        }


        return $new_data;
    }


    // ---------------------------------------------------------------------- 

    /**
     * accepts_content_type
     *
     * @access public
    */
    public function accepts_content_type($name)
    {
        return ($name == 'channel' || $name == 'grid');
        //return true;
    }

    // --------------------------------------------------------------------

    /**
     * Prune Revisions
     *
     * Removes all revisions of an entry except for the $max latest
     *
     * @access	public
     * @param	int
     * @return	int
     */
    function prune_revisions($entry_id, $field_id, $max)
    {
        ee()->db->where('entry_id', $entry_id);
        ee()->db->where('field_id', $field_id);
        $count = ee()->db->count_all_results(GMAPS_FT_MAP.'_history');

        if ($count > $max)
        {
            ee()->db->select('history_id');
            ee()->db->where('entry_id', $entry_id);
            ee()->db->where('field_id', $field_id);
            ee()->db->order_by('history_id', 'DESC');
            ee()->db->limit($max);

            $query = ee()->db->get(GMAPS_FT_MAP.'_history');

            $ids = array();
            foreach ($query->result_array() as $row)
            {
                $ids[] = $row['history_id'];
            }

            ee()->db->where('entry_id', $entry_id);
            ee()->db->where('field_id', $field_id);
            ee()->db->where_not_in('history_id', $ids);
            ee()->db->delete(GMAPS_FT_MAP.'_history');
            unset($ids);
        }
    }

    // --------------------------------------------------------------------

    /**
     * parse a polyline
     *
     * @access	public
     * @param	int
     * @return	int
     */
    function parse_polyline($val, $tagdata)
    {
        $val = (array)$val;

        //reset the data
        $val['stroke_color'] = $val['strokeColor'];
        $val['stroke_opacity'] = $val['strokeOpacity'];
        $val['stroke_weight'] = $val['strokeWeight'];

        //reset the lat/lng
        if(!empty($val['path']))
        {
            foreach($val['path'] as $k => $v)
            {
                $val['path'][$k] = array(
                    'lat' => $v[0],
                    'lng' => $v[1]
                );
            }

            $val['paths'] = $val['path'];
        }

        return ee()->TMPL->parse_variables($tagdata, array((array)$val));
    }

    // --------------------------------------------------------------------

    /**
     * parse a polyline
     *
     * @access	public
     * @param	int
     * @return	int
     */
    function parse_polygon($val, $tagdata)
    {
        $val = (array)$val;

        //reset the data
        $val['stroke_color'] = $val['strokeColor'];
        $val['stroke_opacity'] = $val['strokeOpacity'];
        $val['stroke_weight'] = $val['strokeWeight'];
        $val['fill_color'] = $val['fillColor'];
        $val['fill_opacity'] = $val['fillOpacity'];

        //reset the lat/lng
        if(!empty($val['paths']))
        {
            foreach($val['paths'] as $k => $v)
            {
                $val['paths'][$k] = array(
                    'lat' => $v[0],
                    'lng' => $v[1]
                );
            }

            $val['path'] = $val['paths'];

        }

        return ee()->TMPL->parse_variables($tagdata, array((array)$val));
    }

    // --------------------------------------------------------------------

    /**
     * parse a Circle
     *
     * @access	public
     * @param	int
     * @return	int
     */
    function parse_circle($val, $tagdata)
    {
        $val = (array)$val;

        //reset the data
        $val['stroke_color'] = $val['strokeColor'];
        $val['stroke_opacity'] = $val['strokeOpacity'];
        $val['stroke_weight'] = $val['strokeWeight'];
        $val['fill_color'] = $val['fillColor'];
        $val['fill_opacity'] = $val['fillOpacity'];
        $val['lat'] = $val['lat'];
        $val['lng'] = $val['lng'];
        $val['radius'] = $val['radius'];

        return ee()->TMPL->parse_variables($tagdata, array((array)$val));
    }

    // --------------------------------------------------------------------

    /**
     * parse a rectangle
     *
     * @access	public
     * @param	int
     * @return	int
     */
    function parse_rectangle($val, $tagdata)
    {
        $val = (array)$val;

        //reset the data
        $val['stroke_color'] = $val['strokeColor'];
        $val['stroke_opacity'] = $val['strokeOpacity'];
        $val['stroke_weight'] = $val['strokeWeight'];
        $val['fill_color'] = $val['fillColor'];
        $val['fill_opacity'] = $val['fillOpacity'];

        //Set the bounds
        if(!empty($val['bounds']))
        {
            $bounds = explode(',', $val['bounds']);
            $val['bounds'] = $val['bound'] = array(
                array(
                    'lat' => $bounds[0],
                    'lng' => $bounds[1]
                ),
                array(
                    'lat' => $bounds[2],
                    'lng' => $bounds[3]
                )
            );

        }

        return ee()->TMPL->parse_variables($tagdata, array((array)$val));
    }

    // ----------------------------------------------------------------------

    /**
     * Get the setting
     *
     * @param string $setting_key
     * @return mixed|string
     */
    public function get_custom_setting($setting_key = '')
    {
        //does the setting exist?
        if(isset($this->settings[$this->prefix.$setting_key]))
        {
            return $this->settings[$this->prefix.$setting_key];
        }

        //get the default value
        else
        {
            foreach($this->fieldtype_settings as $key => $setting)
            {
                if ( $setting['name'] === $setting_key )
                    return isset($setting['def_value']) ? $setting['def_value'] : '';
            }
        }
    }

    // ----------------------------------------------------------------------

    /**
     * safe implode
     *
     * @param array $array
     * @param string $glue
     * @return mixed|string
     * @internal param string $setting_key
     */
    public function safe_implode($array = array(), $glue = '|')
    {
        if(is_bool($array))
        {
            return '';
        }

        if(!is_array($array))
        {
            $array = array($array);
        }

        return implode($glue, $array);
    }


    // ----------------------------------------------------------------------

}
