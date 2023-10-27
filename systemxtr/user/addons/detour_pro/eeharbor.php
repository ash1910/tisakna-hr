<?php

// We can declare ee() in the global namespace here:
namespace {
	if(!function_exists('ee')) {
		function ee() {
			return get_instance();
		}
	}
}

namespace detour_pro {

	/**
	 * EEHarbor foundation
	 *
	 * Bridges the functionality gaps between EE versions.
	 * This file namespaces, and dynamically loads the correct version of the EE helper
	 *
	 * @package         eeharbor_helper
	 * @version         1.2.2
	 * @author          Tom Jaeger <Tom@EEHarbor.com>
	 * @link            https://eeharbor.com
	 * @copyright       Copyright (c) 2016, Tom Jaeger/EEHarbor
	 */

	if(defined('APP_VER')) $app_ver = APP_VER;
	else $app_ver = ee()->config->item('app_version');

	// include the right helper, ext file, and upd file
	require_once PATH_THIRD.'detour_pro/helpers/eeharbor_ee' . substr($app_ver, 0, 1) . '_helper.php';
	require_once PATH_THIRD.'detour_pro/helpers/ext.eeharbor.php';
	require_once PATH_THIRD.'detour_pro/helpers/upd.eeharbor.php';

	class EEHarbor extends \detour_pro\EEHelper {
		function __construct()
		{
			$params = array("module" => "detour_pro", "module_name" => "Detour Pro");

			parent::__construct($params);
		}
	}
}