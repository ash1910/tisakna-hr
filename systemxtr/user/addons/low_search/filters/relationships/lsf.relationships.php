<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Filter by relationships
 *
 * @package        low_search
 * @author         Lodewijk Schutte ~ Low <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-search
 * @copyright      Copyright (c) 2017, Low
 */
class Low_search_filter_relationships extends Low_search_filter {

	/**
	 * Search parameters for (parents|children):field params and return set of ids that match it
	 *
	 * @access      public
	 * @return      void
	 */
	public function filter($entry_ids)
	{
		// --------------------------------------
		// Check prefixed parameters needed
		// --------------------------------------

		$rels = array_filter(array_merge(
			$this->params->get_prefixed('parent:'),
			$this->params->get_prefixed('child:')
		));

		// --------------------------------------
		// Don't do anything if nothing's there
		// --------------------------------------

		if (empty($rels)) return $entry_ids;

		// --------------------------------------
		// Log it
		// --------------------------------------

		$this->_log('Applying '.__CLASS__);

		// --------------------------------------
		// Loop through relationships
		// --------------------------------------

		foreach ($rels AS $key => $val)
		{
			// Split param into [child|parent] [field_name]
			list($type, $field) = explode(':', $key, 2);

			// Initiate some variables
			$field_id = $table = $grid = $col_id = $col_type = FALSE;
			$where = array();
			$parent = ($type == 'parent');

			// Check if field consists of multiple names
			if (strpos($field, ':') !== FALSE)
			{
				// We're in a grid
				list($field, $column) = explode(':', $field, 2);

				// ...are we really?
				if ($grid = $this->fields->is_grid($field))
				{
					// Get the grid field ID and column ID
					$field_id = $this->fields->id($field);
					$col_id   = $this->fields->grid_col_id($field_id, $column);
					$col_type = $this->fields->grid_col_type($field_id, $column);
				}

				// No valid column ID or type? Bail out
				if ( ! $col_id || ! in_array($col_type, array('relationship', 'playa')))
				{
					$this->_log("{$field}:{$column} is not a valid relationship column");
					continue;
				}
			}
			// We're not in a grid, just get the field ID
			elseif ( ! ($field_id = $this->fields->id($field)))
			{
				$this->_log($field.' is not a valid relationship field');
				continue;
			}

			// Native relationship field
			if ($this->fields->is_rel($field) || ($grid && $col_type == 'relationship'))
			{
				$table  = 'relationships';
				$select = $parent ? 'child_id' : 'parent_id';
				$target = $parent ? 'parent_id' : 'child_id';

				if ($grid)
				{
					$where['grid_field_id'] = $field_id;
					$where['grid_col_id'] = $col_id;
					$group = 'grid_row_id';
				}
				else
				{
					$where['field_id'] = $field_id;
					$group = $select;
				}
			}
			// Support for playa or tax-playa
			elseif ($this->fields->is_playa($field, TRUE) || ($grid && $col_type == 'playa'))
			{
				$table  = 'playa_relationships';
				$select = $parent ? 'child_entry_id' : 'parent_entry_id';
				$target = $parent ? 'parent_entry_id' : 'child_entry_id';

				if ($grid)
				{
					$where['parent_field_id'] = $field_id;
					$where['parent_col_id'] = col_id;
					$group = 'parent_row_id';
				}
				else
				{
					$where['parent_field_id'] = $field_id;
					$group = $select;
				}
			}

			// Execute query
			if ($table)
			{
				// Prep the value
				$val = $this->params->prep($key, $val);

				// Get the parameter
				list($ids, $in) = $this->params->explode($val);

				// Match all?
				$all = (bool) strpos($val, '&');

				// Check if $ids are numeric
				if ( ! low_array_is_numeric($ids))
				{
					// Log it!
					$this->_log("Getting entry IDs for given relationship url_titles (field {$field_id})");

					// Translate url_titles to IDs based on field
					$ids = $this->_get_entry_ids($ids, $field_id, $parent);

					if (empty($ids))
					{
						$this->_log('No valid entry IDs found');
						return array();
					}
				}

				// Start query
				ee()->db
					->select($select.' AS entry_id')
					->from($table)
					->{$in ? 'where_in' : 'where_not_in'}($target, $ids);

				foreach ($where as $a => $b)
				{
					ee()->db->where($a, $b);
				}

				// Limit by already existing ids
				if ($entry_ids)
				{
					ee()->db->where_in($select, $entry_ids);
				}

				// Do the having-trick to account for *all* given entry ids
				if ($in && $all)
				{
					ee()->db
						->select('COUNT(*) AS num')
						->group_by($group)
						->having('num', count($ids));
				}

				// Execute query
				$query = ee()->db->get();

				// And get the entry ids
				$entry_ids = low_flatten_results($query->result_array(), 'entry_id');
				$entry_ids = array_unique($entry_ids);

				// Bail out if there aren't any matches
				if (is_array($entry_ids) && empty($entry_ids)) break;
			}
		}

		return $entry_ids;
	}

	/**
	 * Get entry ids based on given url_titles
	 */
	private function _get_entry_ids($ids, $field_id, $parent)
	{
		static $cache = array();

		if ($parent)
		{
			// Get the channels that have this field assigned to it
			if ( ! isset($cache[$field_id]))
			{
				$query = ee()->db
					->select('c.channel_id')
					->from('channels c')
					->join('channel_fields cf', 'c.field_group = cf.group_id')
					->where('cf.field_id', $field_id)
					->get();

				$cache[$field_id] = low_flatten_results($query->result_array(), 'channel_id');
			}

			$channel_ids = $cache[$field_id];
		}
		else
		{
			// Get the channel IDs assigned to this relationship/playa field
			$settings = ee()->api_channel_fields->get_settings($field_id);
			$channel_ids = $settings['channels'];
		}

		// Price to pay for using url_titles instead of IDs:
		// At least 1 query per parameter. At least it's fairly quick.
		$query = ee()->db
			->select('entry_id')
			->from('channel_titles')
			->where_in('channel_id', $channel_ids)
			->where_in('url_title', $ids)
			->get();

		return low_flatten_results($query->result_array(), 'entry_id');
	}

	/**
	 * Results: remove rogue {low_search_parent/child:...} vars
	 */
	public function results($query)
	{
		$this->_remove_rogue_vars(array('parent:', 'child:'));
		return $query;
	}

}
