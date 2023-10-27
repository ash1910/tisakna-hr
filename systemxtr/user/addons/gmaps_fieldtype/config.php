<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Default config
 *
 * @package		Default module
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl
 * @copyright 	Copyright (c) 2013 Reinos.nl Internet Media
 */

//contants
if ( ! defined('GMAPS_FT_NAME'))
{
	define('GMAPS_FT_NAME', 'Gmaps fieldtype');
	define('GMAPS_FT_CLASS', 'Gmaps_fieldtype');
	define('GMAPS_FT_MAP', 'gmaps_fieldtype');
	define('GMAPS_FT_VERSION', '3.4.1');
	define('GMAPS_FT_AUTHOR', 'Rein de Vries');
	define('GMAPS_FT_AUTHOR_URL', 'http://reinos.nl/add-ons');
	define('GMAPS_FT_DESCRIPTION', 'Simplified Google Maps for ExpressionEngine');
	define('GMAPS_FT_DOCS', 'http://reinos.nl/add-ons/gmaps-fieldtype/');
	define('GMAPS_FT_DEBUG', false);
}

//configs
$config['name'] = GMAPS_FT_NAME;
$config['version'] = GMAPS_FT_VERSION;

//load compat file
require_once(PATH_THIRD.GMAPS_FT_MAP.'/compat.php');

/* End of file config.php */
/* Location: /system/expressionengine/third_party/gmaps_ft/config.php */