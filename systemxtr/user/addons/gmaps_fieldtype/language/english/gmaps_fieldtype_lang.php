<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Include the config file
 */
require_once PATH_THIRD.'gmaps_fieldtype/config.php';

$lang = array(
	GMAPS_FT_MAP."_module_name"							=> GMAPS_FT_NAME,
	GMAPS_FT_MAP.'_module_description'					=> GMAPS_FT_DESCRIPTION,
	GMAPS_FT_MAP.'_settings'								=> 'Settings',
	GMAPS_FT_MAP.'_setting'								=> 'Setting',
	GMAPS_FT_MAP.'_preference'							=> 'Perferences',
	GMAPS_FT_MAP.'_license_key'							=> 'License key',
	GMAPS_FT_MAP.'_nodata'								=> 'No data',
	GMAPS_FT_MAP.'_overview'								=> 'Overview',
	GMAPS_FT_MAP.'_documentation'						=> 'Documentation',
	GMAPS_FT_MAP.'_migration'						=> 'Migration Tool',
	GMAPS_FT_MAP.'_auto_center_message_enabled'						=> '<i>Note: the gmaps is in auto center mode, what means that the Gmaps center the map to the position of the searched marker.</i>',
	GMAPS_FT_MAP.'_auto_center_message_disabled'						=> '<i>Note: the gmaps is <strong>not</strong> in auto center mode, what means that the Gmaps will <strong>not</strong> center to the location of the searched marker.</i>',

);	