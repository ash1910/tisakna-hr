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
	GMAPS_MAP.'_default_settings' => 'Zadane postavke',
	GMAPS_MAP.'_settings' => 'Postavke',
	GMAPS_MAP.'_preference' => 'Preference',
	GMAPS_MAP.'_setting' => 'Postavka',
	GMAPS_MAP.'_dev_mode' => 'Developer mod',
	GMAPS_MAP.'_log' => 'Logovi',

	GMAPS_MAP.'_license_key' => 'Licenca',
	GMAPS_MAP.'_report_stats' => 'Izvješće statistika',
	GMAPS_MAP.'_data_transfer' => 'Prijenos podataka',
	GMAPS_MAP.'_geocoding_providers' => 'Pružatelji geokodiranja',

	GMAPS_MAP.'_address' => 'Adresa',
	GMAPS_MAP.'_lat' => 'Latitude',
	GMAPS_MAP.'_lng' => 'Longitude',
	GMAPS_MAP.'_date' => 'Datum',
	GMAPS_MAP.'_cache' => 'Pohrane',
	GMAPS_MAP.'_delete_cache' => 'Izbriši pohrane',
	GMAPS_MAP.'_delete_all_logs' => 'Izbriši sve logove',
	GMAPS_MAP.'_logs_deleted' => 'Logovi su izbrisani',
	GMAPS_MAP.'_cache_deleted' => 'Pohrane su izbrisane',
	GMAPS_MAP.'_add_cache' => 'Dodaj pohrane',
	GMAPS_MAP.'_show_cache' => 'Prikaži pohrane',
	GMAPS_MAP.'_google_api_key' => 'Google Maps API Key',
	GMAPS_MAP.'_google_api_key_client' => 'Google Maps Client API Key',
	GMAPS_MAP.'_google_api_key_server' => 'Google Maps Server API Key',
	GMAPS_MAP.'_bing_maps_key' => 'Bing Maps API Key',
	GMAPS_MAP.'_map_quest_key' => 'Map Quest API Key',
	GMAPS_MAP.'_tomtom_key' => 'TomTom API Key',
	'general_settings' => 'Opće postavke',
	'column_address' => 'Adresa',
	'column_lat' => 'Lat',
	'column_lng' => 'Lng',
	'column_date' => 'Datum',
	'column_log_id' => 'Log ID',
	'column_time' => 'Vrijeme',
	'column_message' => 'Poruka',
);