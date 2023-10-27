<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  GMAPS lib
 *
 * @package		Gmaps
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @link        http://reinos.nl/add-ons/gmaps
 * @copyright 	Copyright (c) 2012 Reinos.nl Internet Media
 */

require_once(PATH_THIRD.'gmaps_fieldtype/config.php');


class Gmaps_fieldtype_migration
{
    public function __construct()
    {

    }


    public function find_fields()
    {
        $q = ee()->db->select('field_id, field_label')
            ->where('field_type', 'gmap')
            ->from('channel_fields')
            ->get();

        if($q->num_rows() > 0)
        {
            return $q->result_array();
        }

        return false;
    }

    public function migrate()
    {
        $field_id = ee()->input->get('field_id');

        $q = ee()->db
            ->where('field_type', 'gmap')
            ->where('field_id', $field_id)
            ->from('channel_fields')
            ->get();


        //copy data to the migration database for save
        $save_old_migration_data = array(
            'site_id' => ee()->config->item('site_id'),
            'field_id' => $field_id,
            'field_settings' => $q->row('field_settings')
        );

        $old_settings = unserialize(base64_decode($q->row('field_settings')));

        //build the new array
        $new_settings = array (
            'gmaps_fieldtype_icon_dir' => ee()->gmaps_settings->get_setting('gmaps_icon_dir'),
            'gmaps_fieldtype_icon_url' => ee()->gmaps_settings->get_setting('gmaps_icon_url'),
            'gmaps_fieldtype_location' => $old_settings['gmap_latitude'] == 0 ? 'europa' : $old_settings['gmap_latitude'].','.$old_settings['gmap_longitude'],
            'gmaps_fieldtype_height' => $old_settings['gmap_map_height'],
            'gmaps_fieldtype_max_markers' => '',
            'gmaps_fieldtype_zoom_level' => $old_settings['gmap_zoom'],
            'gmaps_fieldtype_show_map_tools' => '1',
            'gmaps_fieldtype_show_search_tools' => '1',
            'gmaps_fieldtype_show_marker_icon' => '1',
            'gmaps_fieldtype_show_circle_icon' => '1',
            'gmaps_fieldtype_show_polygon_icon' => '1',
            'gmaps_fieldtype_show_polyline_icon' => '1',
            'gmaps_fieldtype_show_rectangle_icon' => '1',
            'gmaps_fieldtype_auto_center' => '0',
            'gmaps_fieldtype_api_key' => '',
            'field_show_smileys' => 'n',
            'field_show_glossary' => 'n',
            'field_show_spellcheck' => 'n',
            'field_show_formatting_btns' => 'n',
            'field_show_file_selector' => 'n',
            'field_show_writemode' => 'n',
        );

        //convert the settings
        ee()->db
            ->where('field_name', 'gmap')
            ->where('field_id', $q->row('field_id'))
            ->update('channel_fields', array(
                'field_settings' => base64_encode(serialize($new_settings)),
                'field_type' => 'gmaps_fieldtype'
            ));

        //get the data from the channnel data field
        $data = ee()->db
            ->select('field_id_'.$field_id.', entry_id')
            ->from('channel_data')
            ->get();

        //set the new data
        $new_data = new stdClass();
        $new_data->{'0'} =  (object)array(
            'markers' => array(),
            'polylines' => array(),
            'polygons' => array(),
            'circles' => array(),
            'rectangles' => array(),
            'map' => array(),
        );

        //set the map
        $new_data->{'0'}->map = (object)array(
            'map_types' => array(
                'hybrid',
                'roadmap',
                'satellite',
                'terrain'
            ),
            'map_type' => 'roadmap',
            'zoom_level' => 0,
            'scroll_wheel' => 'true',
            'zoom_control' => 'true',
            'zoom_control_style' => 'LARGE',
            'zoom_control_position' => 'TOP_LEFT',
            'pan_control' => 'true',
            'pan_control_position' => 'TOP_LEFT',
            'map_type_control' => 'true',
            'map_type_control_style' => 'DEFAULT',
            'map_type_control_position' => 'TOP_RIGHT',
            'street_view_control' => 'true',
            'street_view_control_position' => 'TOP_LEFT',
            'google_overlay_html' => '',
            'google_overlay_position' => 'left',
            'center' => array(
                '35.6852961275',
                '-333.7914204'
            )
        );

        //any result?
        if($data->num_rows() > 0)
        {
            foreach($data->result() as $row)
            {
                $field_data_raw = $row->{'field_id_'.$field_id};
                $field_data = json_decode($field_data_raw);

                if($field_data_raw != '')
                {
                    //markers
                    if(isset($field_data->markers->results) && is_array($field_data->markers->results) && $field_data->markers->total > 0)
                    {
                        foreach($field_data->markers->results as $marker)
                        {
                            $place = $this->get_place($marker->place_id);

                            $new_data->{'0'}->markers[] = (object)array(
                                'lat' => $place->result->geometry->location->lat,
                                'lng' => $place->result->geometry->location->lng,
                                'title' => $place->result->formatted_address,
                                'icon' => '',
                                'content' => '',
                            );
                        }
                    }

                    //waypoints --> polyline
                    if(isset($field_data->waypoints->results) && is_object($field_data->waypoints->results) && $field_data->waypoints->total > 0)
                    {
                        $new_polyline = (object)array(
                            'strokeColor' => "#000000",
                            'strokeOpacity' => "1",
                            'strokeWeight' => "1",
                            'path' => array()
                        );

                        foreach($field_data->waypoints->results as $marker)
                        {
                            $place = $this->get_place($marker->place_id);

                            $new_polyline->path[] = array(
                                $place->result->geometry->location->lat,
                                $place->result->geometry->location->lng
                            );
                        }

                        //set the new data
                        $new_data->{'0'}->polylines[] = $new_polyline;
                    }

                    $save_old_migration_data['channel_data'] = $field_data_raw;
                    $save_old_migration_data['entry_id'] = $row->entry_id;
                    ee()->db->insert('gmaps_migration', $save_old_migration_data);

                    //update the channel data
                    ee()->db->where('entry_id', $row->entry_id);
                    ee()->db->update('channel_data', array(
                        'field_id_'.$field_id => base64_encode(json_encode($new_data))
                    ));
                }
            }
        }

        echo 'true';
    }

    public function get_place($place_id)
    {
        return json_decode(file_get_contents('https://maps.googleapis.com/maps/api/place/details/json?placeid='.$place_id.'&key='.ee()->gmaps_settings->item('google_api_key_server')));
    }
}