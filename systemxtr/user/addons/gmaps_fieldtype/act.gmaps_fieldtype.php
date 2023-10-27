<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Gmaps action File
 *
 * @package             Gmaps for EE3
 * @author              Rein de Vries (info@reinos.nl)
 * @copyright           Copyright (c) 2015 Rein de Vries
 * @license  			http://reinos.nl/add-ons/commercial-license
 * @link                http://reinos.nl/add-ons/gmaps
 */

require_once(PATH_THIRD.'gmaps_fieldtype/config.php');

class Gmaps_fieldtype_ACT
{
	private $EE; 

	/**
	 * Constructor
	 * 
	 * @return unknown_type
	 */
	function __construct()
	{		
		//Load the gmaps lib	
		ee()->load->library('gmaps_fieldtype_lib');

        ee()->lang->loadfile(GMAPS_FT_MAP);

		//require the settings and the actions
		require PATH_THIRD.'gmaps/settings.php';
	}

	// ----------------------------------------------------------------------------------
	
	/**
	 * dispatch the actions via ajax or so.
	 * 
	 * @return unknown_type
	 */
	function init ()
	{
		//get the method
		$method = ee()->input->get_post('method');

		//call the method if exists
		if(method_exists($this, $method))
		{
			echo $this->{$method}();
            
            die();
		}

        echo 'no_method';
		exit;
	}

	// ----------------------------------------------------------------------------------

	/**
	 * Create a new map for all the fieldtypes
	 * 
	 * @return unknown_type
	 */
	function gmaps_fieldtype ()
	{
		//load stuff
		ee()->load->helper('form');

        //weird session bug
        $user_vars	= array(
            'member_id', 'group_id', 'group_description', 'group_title', 'username', 'screen_name',
            'email', 'ip_address', 'location', 'total_entries',
            'total_comments', 'private_messages', 'total_forum_posts', 'total_forum_topics', 'total_forum_replies'
        );
        foreach ($user_vars as $user_var)
        {
            if(!isset(ee()->session->userdata[$user_var]))
            {
                ee()->session->userdata[$user_var] = '';
            }
        }

		//save data
        $data = trim(ee()->input->get_post('data'));
        $zoom_level = ee()->input->get_post('zoom_level');
        $max_markers = ee()->input->get_post('max_markers');
        $show_map_tools = ee()->input->get_post('show_map_tools');
        $show_search_tools = ee()->input->get_post('show_search_tools');
        $show_marker_icon = ee()->input->get_post('show_marker_icon');
        $show_circle_icon = ee()->input->get_post('show_circle_icon');
        $show_polygon_icon = ee()->input->get_post('show_polygon_icon');
        $show_polyline_icon = ee()->input->get_post('show_polyline_icon');
        $show_rectangle_icon = ee()->input->get_post('show_rectangle_icon');
        $location = ee()->input->get_post('location');
        $scroll = ee()->input->get_post('scroll');
        $icon_url = ee()->input->get_post('icon_url');
        $icon_dir = ee()->input->get_post('icon_dir');
        $field_name = ee()->input->get_post('fieldname');
        $field_name_input = ee()->input->get_post('fieldname_input');
        $auto_center = ee()->input->get_post('auto_center');
        $height = ee()->input->get_post('height') != '' ? ee()->input->get_post('height') : '500px';

        //the tag
        $tag_gmaps = '
            {exp:gmaps:map}
                {width}100%{/width}
                {center:address}heerde{/center:address}
                {height}'.$height.'{/height}
            {/exp:gmaps:map}
        ';

        //parse the gmaps
        $parsed_gmaps = gmaps_helper::parse_tags($tag_gmaps);

        $map_html = '';
        
        //data-zoom="'.$zoom.'"
        $map_html .= '
        <div  
            id="gmaps_ft_'.gmaps_helper::get_cache(GMAPS_MAP.'_caller').'"
            class="gmap_holder ee_gmap_'.gmaps_helper::get_cache(GMAPS_MAP.'_caller').' gmaps_ft_'.gmaps_helper::get_cache(GMAPS_MAP.'_caller').'"
            data-mapid="ee_gmap_'.gmaps_helper::get_cache(GMAPS_MAP.'_caller').'"
            data-gmaps-number="'.gmaps_helper::get_cache(GMAPS_MAP.'_caller').'"
            data-fieldname="'.$field_name.'"
            data-fieldname_input="'.$field_name_input.'"
            data-location="'.$location.'"
            data-max-markers="'.$max_markers.'"
            data-zoom-level="'.$zoom_level.'"
            data-show-map-tools="'.$show_map_tools.'"
            data-show-search-tools="'.$show_search_tools.'"
            data-show-marker-icon="'.$show_marker_icon.'"
            data-show-circle-icon="'.$show_circle_icon.'"
            data-show-polygon-icon="'.$show_polygon_icon.'"
            data-show-polyline-icon="'.$show_polyline_icon.'"
            data-show-rectangle-icon="'.$show_rectangle_icon.'"
            data-auto-center="'.$auto_center.'"
            data-scroll="'.$scroll.'"
            data-group-id="'.ee()->session->userdata('group_id').'"
        >

            <div class="alert alert-info alert-block">
                <a class="close" data-dismiss="alert" href="javascript:;"></a>
                <i class="icon-info-sign"></i> <strong class="txt"></strong>
            </div>
        ';

        if(in_array(ee()->session->userdata('group_id'), explode('|', $show_map_tools)))
        {
            $map_html .= '
                <div class="gmaps_controls">

                        <a class="btn action refresh_map_wrapper"><span class="refresh_map fa fa-refresh"></span> Refresh Map</a>
                        <a class="btn action reset_map_wrapper"><span class="reset_map fa fa-trash"></span> Reset Map</a>
                        <a class="btn action edit_map_wrapper m-link" rel="modal-edit_settings_'.gmaps_helper::get_cache(GMAPS_MAP.'_caller').'"><i class="edit_map fa fa-edit"></i> Map Settings</a>

                </div>
            ';
        }

        if(in_array(ee()->session->userdata('group_id'), explode('|', $show_search_tools)))
        {
            $map_html .= '   <div class="search_holder">
                    <input placeholder="Traži lokaciju" type="text" class="search_address_input" name="address_'.gmaps_helper::get_cache(GMAPS_MAP.'_caller').'"/>
                       <a href="#" class="btn search_address">Traži</a><br>
                       <span><input checked id="geocode_latlng" name="geocode_latlng" type="checkbox"/><label style="font-size:80%;" for="geocode_latlng">Obrnuti Geocode latlng</label> </span>
                      
                    <div class="markers_holder hidden">
                        <ul class="markers"></ul>
                    </div>
                </div>
            ';
        }

        if($auto_center == 1)
        {
            $auto_center_message = ' <span class="notice-auto-center-mode-enabled">'.lang(GMAPS_FT_MAP.'_auto_center_message_enabled').'</span>';
        }
        else
        {
            $auto_center_message = ' <span class="notice-auto-center-mode-enabled">'.lang(GMAPS_FT_MAP.'_auto_center_message_disabled').'</span>';
        }

        $map_html .= '  <div style="display:none;" class="markers_holder markers_on_map">
                <fieldset class="gmaps_fieldset">
                    <label>Markeri</label>
                    <ul class="selected_markers markers sortable"></ul>
                </fieldset>
            </div>

            <div class="col-group">
                <div class="col w-8">
                    <div style="display:none;" class="markers_holder polylines_on_map">
                        <fieldset class="gmaps_fieldset">
                            <label>Polylines</label>
                            <ul class="selected_polylines polylines"></ul>
                        </fieldset>
                    </div>
                </div>
                 <div class="col w-8">
                    <div style="display:none;" class="markers_holder polygons_on_map">
                        <fieldset class="gmaps_fieldset">
                            <label>Polygons</label>
                            <ul class="selected_polygons polygons"></ul>
                        </fieldset>
                    </div>
                 </div>

                <div class="col w-8">
                    <div style="display:none;" class="markers_holder circles_on_map">
                        <fieldset class="gmaps_fieldset">
                            <label>Circles</label>
                            <ul class="selected_circles circles"></ul>
                        </fieldset>
                    </div>
                </div>
                <div class="col w-8">
                    <div style="display:none;" class="markers_holder rectangles_on_map">
                        <fieldset class="gmaps_fieldset">
                            <label>Rectangles</label>
                            <ul class="selected_rectangles rectangles"></ul>
                        </fieldset>
                    </div>
                </div>
            </div>
            
            <div class="gmaps_fieldtype_mode">'.$auto_center_message.'</div>

        '.$parsed_gmaps.'
        '.form_input($field_name_input, $data, 'id="'.$field_name.'" style="display: none;"').'
        </div>
        ';

        $modal_html = '';

        $modal_html_settings = '
            <h1>Edit settings</h1>
            <div class="settings">
                <fieldset class="col-group">

                    <div class="setting-txt col w-8">
                        <h3>Available Map Types</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input map_types" data-name="map_types" data-type="checkbox">
                            hybrid <input type="checkbox" value="hybrid" name="map_types[]"/>
                            roadmap <input type="checkbox" value="roadmap" name="map_types[]"/>
                            satellite <input type="checkbox" value="satellite" name="map_types[]"/>
                            terrain <input type="checkbox" value="terrain" name="map_types[]"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Default Map Types</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input map_type" data-name="map_type" data-type="normal">
                            <select class="value" name="map_type">
                                <option value="hybrid">hybrid</option>
                                <option value="roadmap">roadmap</option>
                                <option value="satellite">satellite</option>
                                <option value="terrain">terrain</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Zoom level</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input zoom_level" data-name="zoom_level" data-type="normal">
                            <select class="value" name="zoom_level">
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option>
                                <option value="13">13</option>
                                <option value="14">14</option>
                                <option value="15">15</option>
                                <option value="16">16</option>
                                <option value="17">17</option>
                                <option value="18">18</option>
                                <option value="19">19</option>
                                <option value="20">20</option>
                                <option value="21">21</option>
                                <option value="22">22</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Scrollwheel</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input scroll_wheel" data-name="scroll_wheel" data-type="normal">
                            <select class="value" name="scroll_wheel">
                                <option value="true">Enabled</option>
                                <option value="false">Disabled</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>ZoomControl</h3>
                    </div>
                    <div class="setting-field col w-8">
                         <div class="input zoom_control" data-name="zoom_control" data-type="normal">
                            <select class="value" name="zoom_control">
                                <option value="true">Enabled</option>
                                <option value="false">Disabled</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>ZoomControl Position</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input zoom_control_position" data-name="zoom_control_position" data-type="normal">
                            <select class="value" name="zoom_control_position">
                                <option value="TOP_CENTER">top center</option>
                                <option value="TOP_LEFT">top left</option>
                                <option value="TOP_RIGHT">top right</option>
                                <option value="LEFT_TOP">left top</option>
                                <option value="RIGHT_TOP">right top</option>
                                <option value="LEFT_CENTER">left center</option>
                                <option value="RIGHT_CENTER">right center</option>
                                <option value="LEFT_BOTTOM">left bottom</option>
                                <option value="RIGHT_BOTTOM">right bottom</option>
                                <option value="BOTTOM_CENTER">bottom center</option>
                                <option value="BOTTOM_LEFT">bottom left</option>
                                <option value="BOTTOM_RIGHT">bottom right</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>MapTypeControl</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input map_type_control" data-name="map_type_control" data-type="normal">
                            <select class="value" name="map_type_control">
                                <option value="true">Enabled</option>
                                <option value="false">Disabled</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>MapTypeControl Style</h3>
                    </div>
                    <div class="setting-field col w-8">
                         <div class="input map_type_control_style" data-name="map_type_control_style" data-type="normal">
                            <select class="value" name="map_type_control_style">
                                <option value="DEFAULT">Default</option>
                                <option value="DROPDOWN_MENU">Dropdown menu</option>
                                <option value="HORIZONTAL_BAR">Horizontal bar</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>MapTypeControl Position</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input map_type_control_position" data-name="map_type_control_position" data-type="normal">
                            <select class="value" name="map_type_control_position">
                                <option value="TOP_CENTER">top center</option>
                                <option value="TOP_LEFT">top left</option>
                                <option value="TOP_RIGHT">top right</option>
                                <option value="LEFT_TOP">left top</option>
                                <option value="RIGHT_TOP">right top</option>
                                <option value="LEFT_CENTER">left center</option>
                                <option value="RIGHT_CENTER">right center</option>
                                <option value="LEFT_BOTTOM">left bottom</option>
                                <option value="RIGHT_BOTTOM">right bottom</option>
                                <option value="BOTTOM_CENTER">bottom center</option>
                                <option value="BOTTOM_LEFT">bottom left</option>
                                <option value="BOTTOM_RIGHT">bottom right</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>StreetViewControl</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input street_view_control" data-name="street_view_control" data-type="normal">
                            <select class="value" name="street_view_control">
                                <option value="true">Enabled</option>
                                <option value="false">Disabled</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>StreetViewControl Position</h3>
                    </div>
                    <div class="setting-field col w-8">
                         <div class="input street_view_control_position" data-name="street_view_control_position" data-type="normal">
                            <select class="value" name="street_view_control_position">
                                <option value="TOP_CENTER">top center</option>
                                <option value="TOP_LEFT">top left</option>
                                <option value="TOP_RIGHT">top right</option>
                                <option value="LEFT_TOP">left top</option>
                                <option value="RIGHT_TOP">right top</option>
                                <option value="LEFT_CENTER">left center</option>
                                <option value="RIGHT_CENTER">right center</option>
                                <option value="LEFT_BOTTOM">left bottom</option>
                                <option value="RIGHT_BOTTOM">right bottom</option>
                                <option value="BOTTOM_CENTER">bottom center</option>
                                <option value="BOTTOM_LEFT">bottom left</option>
                                <option value="BOTTOM_RIGHT">bottom right</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Google Overlay</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input google_overlay_html" data-name="google_overlay_html" data-type="textarea">
                            <textarea name="google_overlay_html" class="value"></textarea>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Overlay position</h3>
                    </div>
                    <div class="setting-field col w-8 last">
                       <div class="input google_overlay_position" data-name="google_overlay_position" data-type="normal">
                            <select class="value" name="google_overlay_position">
                                <option value="right">Right</option>
                                <option value="left">Left</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-ctrls">
                    <a href="#" class="btn save">Save Settings</a>
                </fieldset>
            </div>
        ';
        $modal_html .= gmaps_helper::createModal('edit_settings_'.gmaps_helper::get_cache(GMAPS_MAP.'_caller'), $modal_html_settings, true);

        //markar modal
        $modal_html_edit_marker = '
            <h1>Edit Marker</h1>

            <input type="hidden" name="marker_number" />
            <input type="hidden" name="marker_uuid" />

            <div class="settings">
                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Title</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="marker_title" data-type="normal">
                            <input type="text" name="marker_title"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Icon</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="map_type_control" data-type="normal">
                           '.ee()->gmaps->set_icon_options($icon_url, $icon_dir).'
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Text</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="marker_infowindow" data-type="normal">
                            <textarea name="marker_infowindow"></textarea>
                        </div>
                    </div>
                </fieldset>

                 <fieldset class="form-ctrls">
                    <a href="#" class="btn save">Save Marker</a>
                </fieldset>
            </div>
        ';
        $modal_html .= gmaps_helper::createModal('edit_marker_'.gmaps_helper::get_cache(GMAPS_MAP.'_caller'), $modal_html_edit_marker, true);

        //polyline html
        $modal_html_edit_polyline = '
            <h1>Edit Polyline</h1>

            <input type="hidden" name="polyline_number" />
            <input type="hidden" name="polyline_uuid" />

            <div class="settings">
                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Stroke Color</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="polyline_strokecolor" data-type="normal">
                            <input type="text" name="polyline_strokecolor"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Stroke Opacity</h3>
                        <em>a <strong>number</strong> between 0.0 and 1</em>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="polyline_opacity" data-type="normal">
                            <input type="text" name="polyline_opacity"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Stroke Weight</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="polyline_weight" data-type="normal">
                            <input type="text" name="polyline_weight"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-ctrls">
                    <a href="#" class="btn save">Save Polyline</a>
                </fieldset>
            </div>
        ';
        $modal_html .= gmaps_helper::createModal('edit_polyline_'.gmaps_helper::get_cache(GMAPS_MAP.'_caller'), $modal_html_edit_polyline, true);

        //edit polygon
        $modal_html_edit_polygon = '

            <h1>Edit Polygon</h1>

            <input type="hidden" name="polygon_number" />
            <input type="hidden" name="polygon_uuid" />

            <div class="settings">
                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Stroke Color</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="polygon_strokecolor" data-type="normal">
                            <input type="text" name="polygon_strokecolor"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Stroke Opacity</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="polygon_opacity" data-type="normal">
                            <input type="text" name="polygon_opacity"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Stroke Weight</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="polygon_weight" data-type="normal">
                            <input type="text" name="polygon_weight"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Fill Color</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="polygon_fillcolor" data-type="normal">
                            <input type="text" name="polygon_fillcolor"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Fill Opacity</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="polygon_fillopacity" data-type="normal">
                            <input type="text" name="polygon_fillopacity"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-ctrls">
                    <a href="#" class="btn save">Save Polygon</a>
                </fieldset>
            </div>
        ';
        $modal_html .= gmaps_helper::createModal('edit_polygon_'.gmaps_helper::get_cache(GMAPS_MAP.'_caller'), $modal_html_edit_polygon, true);

        //edit circle modal
        $modal_html_edit_circle = '
            <h1>Edit Circle</h1>

            <input type="hidden" name="circle_number" />
            <input type="hidden" name="circle_uuid" />

            <div class="settings">
                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Stroke Color</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="circle_strokecolor" data-type="normal">
                            <input type="text" name="circle_strokecolor"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Stroke Opacity</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="circle_opacity" data-type="normal">
                            <input type="text" name="circle_opacity"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Stroke Weight</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="circle_weight" data-type="normal">
                            <input type="text" name="circle_weight"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Fill Color</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="circle_fillcolor" data-type="normal">
                            <input type="text" name="circle_fillcolor"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Fill Opacity</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="circle_fillopacity" data-type="normal">
                            <input type="text" name="circle_fillopacity"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Radius</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="circle_radius" data-type="normal">
                            <input type="text" name="circle_radius"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-ctrls">
                    <a href="#" class="btn save">Save Circle</a>
                </fieldset>
            </div>
        ';
        $modal_html .= gmaps_helper::createModal('edit_circle_'.gmaps_helper::get_cache(GMAPS_MAP.'_caller'), $modal_html_edit_circle, true);

        //edit rectangle modal
        $modal_html_edit_rectangle = '
            <h1>Edit Rectangle</h1>

            <input type="hidden" name="rectangle_number" />
            <input type="hidden" name="rectangle_uuid" />

            <div class="settings">
                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Stroke Color</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="rectangle_strokecolor" data-type="normal">
                            <input type="text" name="rectangle_strokecolor"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Stroke Opacity</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="rectangle_opacity" data-type="normal">
                            <input type="text" name="rectangle_opacity"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Stroke Weight</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="rectangle_weight" data-type="normal">
                            <input type="text" name="rectangle_weight"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Fill Color</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="rectangle_fillcolor" data-type="normal">
                            <input type="text" name="rectangle_fillcolor"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-group">
                    <div class="setting-txt col w-8">
                        <h3>Fill Opacity</h3>
                    </div>
                    <div class="setting-field col w-8">
                        <div class="input" data-name="rectangle_fillopacity" data-type="normal">
                            <input type="text" name="rectangle_fillopacity"/>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-ctrls">
                    <a href="#" class="btn save">Save Rectangle</a>
                </fieldset>
            </div>
        ';
        $modal_html .= gmaps_helper::createModal('edit_rectangle_'.gmaps_helper::get_cache(GMAPS_MAP.'_caller'), $modal_html_edit_rectangle, true);

        //init the js
        //ee()->cp->add_to_foot(' <script>$(window).load(function(){init_gmaps('.ee()->session->userdata(GMAPS_MAP.'_caller').');});</script>');

        //return data

        $data = array('map' => $map_html, 'modals' => $modal_html, 'map_nr' => gmaps_helper::get_cache(GMAPS_MAP.'_caller'));

        return base64_encode(json_encode($data));
	}

	// ----------------------------------------------------------------------------------
}