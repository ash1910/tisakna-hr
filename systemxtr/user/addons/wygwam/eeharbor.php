<?php

// We can declare ee() in the global namespace here:
namespace {
	if(!function_exists('ee')) {
		function ee() {
			return get_instance();
		}
	}
}

namespace wygwam {

	/**
	 * EEHarbor foundation
	 *
	 * Bridges the functionality gaps between EE versions.
	 * This file namespaces, and dynamically loads the correct version of the EE helper
	 *
	 * @package			eeharbor_helper
	 * @version			1.4.3
	 * @author			Tom Jaeger <Tom@EEHarbor.com>
	 * @link			https://eeharbor.com
	 * @copyright		Copyright (c) 2016, Tom Jaeger/EEHarbor
	 */

	if(defined('APP_VER')) $app_ver = APP_VER;
	else $app_ver = ee()->config->item('app_version');

	// Pull our addon.setup.php file and define some namespaced constants because DRY.
	$addon_setup = require PATH_THIRD.'wygwam/addon.setup.php';

	define('wygwam\ADDON_AUTHOR', $addon_setup['author']);
	define('wygwam\ADDON_AUTHOR_URL', $addon_setup['author_url']);
	define('wygwam\ADDON_NAME', $addon_setup['name']);
	define('wygwam\ADDON_DESC', $addon_setup['description']);
	define('wygwam\ADDON_VER', $addon_setup['version']);

	// include the right helper, ext file, and upd file
	require_once PATH_THIRD.'wygwam/helpers/eeharbor_ee' . substr($app_ver, 0, 1) . '_helper.php';
	require_once PATH_THIRD.'wygwam/helpers/ext.eeharbor.php';
	require_once PATH_THIRD.'wygwam/helpers/upd.eeharbor.php';
	require_once PATH_THIRD.'wygwam/helpers/ft.eeharbor.php';

	class EEHarbor extends \wygwam\EEHelper {
		function __construct()
		{
			$params = array("module" => "wygwam", "module_name" => "Wygwam");

			parent::__construct($params);
		}
	}
}