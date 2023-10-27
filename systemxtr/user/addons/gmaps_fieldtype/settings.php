<?php
/**
 * the settings for the module
 *
 * @package		Default module
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl
 * @copyright 	Copyright (c) 2013 Reinos.nl Internet Media
 */

//updates
$this->updates = array(
	'1.2',
	'1.4.2',
	'1.6.1',
	'2.1.0',
	'2.1.4',
	'3.0.0',
	'3.0.1',
	'3.0.2',
	'3.4.0',
);

//Default Post
$this->default_post = array(
	'license_key'   		=> '',
);

//show the field in wide or default
$this->field_wide = true;

$this->fieldtype_settings = array(
	/*array(
		'label' => lang('zoom'),
		'name' => 'zoom',
		'type' => 's', // s=select, m=multiselect t=text
		'options' => range(1, 20),
		'def_value' => 1,
		'global' => false,
	),*/
	array(
		'label' => lang('Icon dir'),
		'desc' => '',
		'name' => 'icon_dir',
		'type' => 't', // s=select, m=multiselect t=text
		'def_value' => ee()->gmaps_settings->get_setting('gmaps_icon_dir'),
		'global' => false,
		'override' => 'gmaps_icon_dir'
	),
	array(
		'label' => lang('Icon url'),
		'desc' => '',
		'name' => 'icon_url',
		'type' => 't', // s=select, m=multiselect t=text
		'def_value' => ee()->gmaps_settings->get_setting('gmaps_icon_url'),
		'global' => false,
		'override' => 'gmaps_icon_url'
	),
	array(
		'label' => lang('location'),
		'desc' => '',
		'name' => 'location',
		'type' => 't', // s=select, m=multiselect t=text
		'def_value' => 'Europe',
		'global' => false,
	),
	array(
		'label' => lang('height'),
		'desc' => '',
		'name' => 'height',
		'type' => 't', // s=select, m=multiselect t=text
		'def_value' => '500px',
		'global' => false,
	),
	// array(
	// 	'label' => lang('scroll'),
	// 	'name' => 'scroll',
	// 	'type' => 's', // s=select, m=multiselect t=text
	// 	'options' => array('no', 'yes'),
	// 	'def_value' => 1,
	// 	'global' => false,
	// ),
	array(
		'label' => lang('license'),
		'desc' => '',
		'name' => 'license',
		'type' => 't', // s=select, m=multiselect t=text
		'def_value' => '',
		'global' => true,
	),
	array(
		'label' => lang('Max markers'),
		'desc' => '',
		'name' => 'max_markers',
		'type' => 's', // s=select, m=multiselect t=text
		'options' => array("" => "") + array_combine(range(1,200), range(1,200)),
		'def_value' => '',
		'global' => false,
	),
	array(
		'label' => lang('Default zoom level'),
		'name' => 'zoom_level',
		'type' => 's', // s=select, m=multiselect t=text
		'options' => array_combine(range(1, 22), range(1, 22)),
		'def_value' => 1,
		'global' => false,
	),
	array(
		'label' => lang('Show map tools'),
		'desc' => '',
		'name' => 'show_map_tools',
		'type' => 'm', // s=select, m=multiselect t=text
		'options' => 'class:gmaps|method:get_membergroups',
		'def_value' => 1,
		'global' => false,
	),
	array(
		'label' => lang('Show search tools'),
		'desc' => '',
		'name' => 'show_search_tools',
		'type' => 'm', // s=select, m=multiselect t=text
		'options' => 'class:gmaps|method:get_membergroups',
		'def_value' => 1,
		'global' => false,
	),
	array(
		'label' => lang('Show add marker icon'),
		'name' => 'show_marker_icon',
		'type' => 'm', // s=select, m=multiselect t=text
		'options' => 'class:gmaps|method:get_membergroups',
		'def_value' => 1,
		'global' => false,
	),
	array(
		'label' => lang('Show circle icon'),
		'name' => 'show_circle_icon',
		'type' => 'm', // s=select, m=multiselect t=text
		'options' => 'class:gmaps|method:get_membergroups',
		'def_value' => 1,
		'global' => false,
	),
	array(
		'label' => lang('Show polygon icon'),
		'name' => 'show_polygon_icon',
		'type' => 'm', // s=select, m=multiselect t=text
		'options' => 'class:gmaps|method:get_membergroups',
		'def_value' => 1,
		'global' => false,
	),
	array(
		'label' => lang('Show polyline icon'),
		'name' => 'show_polyline_icon',
		'type' => 'm', // s=select, m=multiselect t=text
		'options' => 'class:gmaps|method:get_membergroups',
		'def_value' => 1,
		'global' => false,
	),
	array(
		'label' => lang('Show rectangle icon'),
		'name' => 'show_rectangle_icon',
		'type' => 'm', // s=select, m=multiselect t=text
		'options' => 'class:gmaps|method:get_membergroups',
		'def_value' => 1,
		'global' => false,
	),
	array(
		'label' => lang('Auto center on new elements'),
		'desc' => 'Auto center the map when a searched marker is placed on the map.',
		'name' => 'auto_center',
		'type' => 's', // s=select, m=multiselect t=text
		'options' => array('no', 'yes'),
		'def_value' => 0,
		'global' => false,
	),
	array(
		'label' => lang('Google API Key'),
		'desc' => 'The key form google http://docs.reinos.nl/gmaps/#key. if leave empty, it should get it from the global key that has been set in the Gmaps CP',
		'name' => 'api_key',
		'type' => 't', // s=select, m=multiselect t=text
		'def_value' => '',
		'global' => false,
	),

);

$this->default_array = array(
	'map' => array(
		'map_type' => 'roadmap',
		'map_types' => array(
			'hybrid',
			'roadmap',
			'satellite',
			'terrain'
		)
	),
	'markers' => array(
		array(
			'content' => null,
			'icon' => null,
			'lat' => null,
			'lng' => null,
			'title' => null
		)
	),
	'polygons' => array(
		array(
			'fillColor' => null,
			'fillOpacity' => null,
			'paths' => array(
				//array of latlngs
				//array('lat','lng'),
			),
			'strokeColor' => null,
			'stokeOpacity' => null,
			'strokeWeight' => null
		)
	),
	'polylines' => array(
		array(
			'paths' => array(
				//array of latlngs
				//array('lat','lng'),
			),
			'strokeColor' => null,
			'stokeOpacity' => null,
			'strokeWeight' => null
		)
	)
);

/* End of file settings.php  */
/* Location: ./system/expressionengine/third_party/default/settings.php */