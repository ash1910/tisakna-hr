<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Break model.
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2017 Causing Effect
 * @license		http://docs.causingeffect.com/expressionengine/ce-cache/license-agreement.html
 * @link		https://www.causingeffect.com
 */

class Ce_cache_tags_model
{
	/**
	 * Returns the tags for the current site.
	 *
	 * @param $prefix
	 * @return array
	 */
	public function get_tags($prefix)
	{
		$tags = array();

		$tagged_items = ee()->db->query("
			SELECT DISTINCT tag
			FROM exp_ce_cache_tagged_items
			WHERE SUBSTRING( item_id, 1, " . strlen($prefix) . " ) = '" . ee()->db->escape_str($prefix) . "'
			ORDER BY tag ASC");
		if ($tagged_items->num_rows() > 0)
		{
			$rows = $tagged_items->result();
			foreach ($rows as $row)
			{
				$tags[] = $row->tag;
			}
		}
		$tagged_items->free_result();

		return $tags;
	}
}