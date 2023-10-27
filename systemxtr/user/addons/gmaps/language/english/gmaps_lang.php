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

include(PATH_THIRD.'gmaps/config.php');

$lang = array(
	GMAPS_MAP.'_module_name' => GMAPS_NAME,
	GMAPS_MAP.'_module_description' => GMAPS_DESCRIPTION,
	GMAPS_MAP.'_default_settings' => 'Default Settings',
	GMAPS_MAP.'_settings' => 'Settings',
	GMAPS_MAP.'_preference' => 'Preference',
	GMAPS_MAP.'_setting' => 'Setting',
	GMAPS_MAP.'_dev_mode' => 'Developer mode',
	GMAPS_MAP.'_log' => 'Logs',

	GMAPS_MAP.'_license_key' => 'License key',
	GMAPS_MAP.'_report_stats' => 'Report Statistics',
	GMAPS_MAP.'_data_transfer' => 'Data Transfer',
	GMAPS_MAP.'_geocoding_providers' => 'Geocoding Providers',

	GMAPS_MAP.'_address' => 'Address',
	GMAPS_MAP.'_lat' => 'Latitude',
	GMAPS_MAP.'_lng' => 'Longitude',
	GMAPS_MAP.'_date' => 'Date',
	GMAPS_MAP.'_cache' => 'Cache',
	GMAPS_MAP.'_delete_cache' => 'Delete Cache',
	GMAPS_MAP.'_delete_all_logs' => 'Delete all Logs',
	GMAPS_MAP.'_logs_deleted' => 'Logs deleted',
	GMAPS_MAP.'_cache_deleted' => 'Cache deleted',
	GMAPS_MAP.'_add_cache' => 'Add Cache',
	GMAPS_MAP.'_show_cache' => 'Show Cache',
	GMAPS_MAP.'_google_api_key' => 'Google Maps API Key',
	GMAPS_MAP.'_google_api_key_client' => 'Google Maps Client API Key',
	GMAPS_MAP.'_google_api_key_server' => 'Google Maps Server API Key',
	GMAPS_MAP.'_bing_maps_key' => 'Bing Maps API Key',
	GMAPS_MAP.'_map_quest_key' => 'Map Quest API Key',
	GMAPS_MAP.'_tomtom_key' => 'TomTom API Key',
	'general_settings' => 'General Settings',
	'column_address' => 'Address',
	'column_lat' => 'Lat',
	'column_lng' => 'Lng',
	'column_date' => 'Date',
	'column_log_id' => 'Log ID',
	'column_time' => 'Time',
	'column_message' => 'Message',
);