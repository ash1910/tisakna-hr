<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Control Panel Library Class
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2017 Causing Effect
 * @license		http://docs.causingeffect.com/expressionengine/ce-cache/license-agreement.html
 * @link		https://www.causingeffect.com
 */

class Ce_cache_cp
{
	/**
	 * Preps the items values for output.
	 *
	 * @param $items
	 * @return mixed
	 */
	public function prep_items($items)
	{
		//load the language file
		ee()->lang->loadfile('ce_cache');

		//load the date helper
		ee()->load->helper('date');

		//the time format string
		$time_string = '%Y-%m-%d - %h:%i:%s %a';

		foreach ( $items as &$item )
		{
			//expiry date
			$item['expiry'] = ($item['ttl'] == 0) ? '∞' : mdate($time_string, $item['made']+$item['ttl']);

			//made date
			$item['made'] = mdate($time_string, $item['made']);
		}

		return $items;
	}

	/**
	 * Creates an array of breadcrumbs.
	 *
	 * @param $driver
	 * @param $path
	 * @return array
	 */
	public function prep_breadcrumbs($driver, $path)
	{
		//load the language file
		ee()->lang->loadfile('ce_cache');

		//the breadcrumbs
		$breadcrumbs = array();

		//the driver items base breadcrumb
		$breadcrumbs[] = array(
			'path' => '/',
			'name' => sprintf(lang('ce_cache_driver_items'), lang('ce_cache_driver_'.$driver))
		);

		//split the path into breadcrumb pieces
		$path = trim($path, '/');
		if (! empty($path))
		{
			$pieces = explode('/', $path);

			$current = '';

			foreach ($pieces as $piece)
			{
				$current .= $piece.'/';

				$breadcrumbs[] = array(
					'path' => $current,
					'name' => $piece
				);
			}
		}

		return $breadcrumbs;
	}
}