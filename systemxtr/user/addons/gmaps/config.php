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

if ( ! defined('GMAPS_NAME'))
{
	define('GMAPS_NAME', 'Gmaps');
	define('GMAPS_CLASS', 'Gmaps');
	define('GMAPS_MAP', 'gmaps');
	define('GMAPS_VERSION', '5.3.3');
	define('GMAPS_AUTHOR', 'Reinos.nl');
	define('GMAPS_AUTHOR_URL', 'http://reinos.nl/add-ons');
	define('GMAPS_DESCRIPTION', 'Simplified Google Maps for ExpressionEngine');
	define('GMAPS_DOCS', 'http://reinos.nl/add-ons/gmaps/');
	define('GMAPS_DEVOTEE', 'http://devot-ee.com/add-ons/gmaps');
	define('GMAPS_STATS_URL', 'http://reinos.nl/index.php/module_stats_api/v1');
}

$config['name'] = GMAPS_NAME;
$config['version'] = GMAPS_VERSION;

//load compat file
require_once(PATH_THIRD.GMAPS_MAP.'/compat.php');
require_once PATH_THIRD.GMAPS_MAP.'/libraries/vendor/autoload.php';
