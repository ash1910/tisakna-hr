<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Break index model.
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2017 Causing Effect
 * @license		http://docs.causingeffect.com/expressionengine/ce-cache/license-agreement.html
 * @link		https://www.causingeffect.com
 */

class Ce_cache_break_index_model
{
	/**
	 * Get the channels for the specified site.
	 *
	 * @param $site_id
	 * @return array
	 */
	public function get_site_channels()
	{
		$channels = array();

		//get the current site
		$site_id = ee()->config->item('site_id');

		//get all of the channels
		$results = ee()->db->query('
		SELECT channel_title AS title, channel_id AS id
		FROM exp_channels
		WHERE site_id = ?
		ORDER BY channel_title ASC', array($site_id));


		if ($results->num_rows() > 0)
		{
			$channels = $results->result_array();
		}

		return $channels;
	}
}