<?php

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

//updates
$this->updates = array(
	'2.4',
	'2.6',
	'2.9.2',
	'2.9.4',
	'2.11.1',
	'2.13',
	'3.0',
	'3.0.2',
	'3.2.1',
	'3.2.2',
	'4.0.0',
	'4.2.6',
	'4.3.0',
	'5.0.0',
	'5.1.0',
	'5.2.0',
	'5.3.0',
	'5.3.2',
);

//Default Post
$this->default_post = array(
	'license_key' => '',
	'report_date' => time(),
	'report_stats' => true,
	'files_info' => '',
	'data_transfer' => 'curl',
	'geocoding_providers' => 'a:11:{s:11:"google_maps";s:1:"1";s:9:"bing_maps";s:1:"0";s:5:"yahoo";s:1:"0";s:9:"cloudmade";s:1:"0";s:13:"openstreetmap";s:1:"0";s:8:"mapquest";s:1:"0";s:6:"yandex";s:1:"0";s:6:"tomtom";s:1:"0";s:9:"nominatim";s:1:"0";s:11:"geocoder_ca";s:1:"0";i:0;s:11:"google_maps";}',
	'dev_mode' => 0,
	'google_api_key_client' => '',
	'google_api_key_server' => '',
	'bing_maps_key' => '',
	'map_quest_key' => '',
	'tomtom_key' => '',
	'cache_time' => 60*60*24*7,

);

$this->def_geocoding_providers = array(
	'google_maps' => array('Google Maps', 'The default provider'),
	'bing_maps' => array('Bing Maps', 'Bing maps can only be used with the param bing_maps_key="".'),
	'openstreetmap' => array('Openstreetmap'),
	'mapquest' => array('Mapquest', 'Mapquest can only be used with the param mapquest_maps_key="".'),
	'yandex' => array('Yandex'),
	'tomtom' => array('TomTom', 'TomTom can only be used with the param tomtom_maps_key="".'),
	'nominatim' => array('Nominatim')
);

//overrides
$this->overide_settings = array(
	'gmaps_icon_dir' => '[theme_dir]images/icons/',
	'gmaps_icon_url' => '[theme_url]images/icons/',
);

//cache date in hour
$this->cache_time = 168; // one week (7 days)

// Backwards-compatibility with pre-2.6 Localize class
$this->format_date_fn = (version_compare(APP_VER, '2.6', '>=')) ? 'format_date' : 'decode_date';

//mcp veld header
$this->table_headers = array(
	GMAPS_MAP.'_address' => array('data' => lang(GMAPS_MAP.'_address'), 'style' => 'width:10%;'),
	GMAPS_MAP.'_lat' => array('data' => lang(GMAPS_MAP.'_lat'), 'style' => 'width:40%;'),
	GMAPS_MAP.'_lng' => array('data' => lang(GMAPS_MAP.'_lng'), 'style' => 'width:40%;'),
	GMAPS_MAP.'_date' => array('data' => lang(GMAPS_MAP.'_date'), 'style' => 'width:40%;'),
	//'actions' => array('data' => '', 'style' => 'width:10%;')
);
