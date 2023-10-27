<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Filter by search:title="foo"
 *
 * @package        low_search
 * @author         Lodewijk Schutte ~ Low <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-search
 * @copyright      Copyright (c) 2017, Low
 */
class Low_search_filter_field_search extends Low_search_filter {

	/**
	 * Prefix
	 */
	private $_pfx = 'search:';

	/**
	 * Channel IDs
	 */
	private $_channel_ids = array();

	// --------------------------------------------------------------------

	/**
	 * Allows for search:title="foo|bar" parameter
	 *
	 * @access     private
	 * @return     void
	 */
	public function filter($entry_ids)
	{
		// --------------------------------------
		// Check if search:title is there
		// --------------------------------------

		$params = $this->params->get_prefixed($this->_pfx, TRUE);

		// --------------------------------------
		// Don't do anything if nothing's there
		// --------------------------------------

		if (empty($params)) return $entry_ids;

		// --------------------------------------
		// Log it
		// --------------------------------------

		$this->_log('Applying '.__CLASS__);

		// --------------------------------------
		// Set channel IDs
		// --------------------------------------

		$this->_channel_ids = ee()->low_search_collection_model->get_channel_ids();

		// --------------------------------------
		// Loop through search filters and prep queries accordingly
		// --------------------------------------

		$queries = array();

		foreach ($params as $key => $val)
		{
			// Make sure value is prepped correctly with exact/exclude/require_all values
			$val = $this->params->prep($this->_pfx.$key, $val);

			// Search channel_titles fields
			if ($this->fields->is_native($key))
			{
				// (URL) Title search
				$queries['channel_titles'][] = $this->fields->sql($key, $val);
			}

			// Search grid or matrix cols
			elseif (strpos($key, ':'))
			{
				list($field_name, $col_name) = explode(':', $key, 2);

				// Skip invalid fields
				if ( ! ($field_id = $this->fields->id($field_name))) continue;

				$table = FALSE;

				// Make sure it's an omelette!
				if ($this->fields->is_grid($field_name) &&
					($col_id = $this->fields->grid_col_id($field_id, $col_name)))
				{
					$table = 'channel_grid_field_'.$field_id;
					$field = 'col_id_'.$col_id;
				}
				elseif ($this->fields->is_matrix($field_name) &&
					($col_id = $this->fields->matrix_col_id($field_id, $col_name)))
				{
					$table = 'matrix_data';
					$field = 'col_id_'.$col_id;
				}

				if ($table)
				{
					$queries[$table][] = $this->fields->sql($field, $val);
				}
			}

			// Search custom channel fields
			elseif ($field_id = $this->fields->id($key))
			{
				// Get where-clause
				$where = $this->fields->sql('field_id_'.$field_id, $val);

				// Enable Smart Field Searches?
				$channel_ids = ($this->params->get('smart_field_search') == 'yes')
					? $this->_get_channel_ids_by_field($field_id)
					: array();

				// If so, add CASE to this statement
				if ( ! empty($channel_ids))
				{
					$where = sprintf("(CASE WHEN channel_id IN (%s) THEN %s ELSE TRUE END)",
						implode(', ', $channel_ids), $where);
				}

				// And add the where clause to the queries
				$queries['channel_data'][] = $where;
			}

			// For performance reasons, don't let EE perform the same search again
			$this->params->forget[] = $this->_pfx.$key;
		}

		// --------------------------------------
		// Where now contains a list of clauses
		// --------------------------------------

		if (empty($queries)) return $entry_ids;

		// --------------------------------------
		// Query the lot!
		// --------------------------------------

		foreach ($queries AS $table => $wheres)
		{
			// Start this query
			ee()->db->distinct()->select('entry_id')->from($table);

			// Add wheres
			foreach ($wheres AS $sql)
			{
				ee()->db->where($sql);
			}

			// Limit by given entry ids?
			if ( ! empty($entry_ids))
			{
				ee()->db->where_in('entry_id', $entry_ids);
			}

			// Limit only for non-grid tables
			if (in_array($table, array('channel_titles', 'channel_data')))
			{
				// Limit by channel
				if ($this->_channel_ids)
				{
					ee()->db->where_in('channel_id', $this->_channel_ids);
				}

				// Limit by site
				if ($site_ids = $this->params->site_ids())
				{
					ee()->db->where_in('site_id', $site_ids);
				}
			}

			// Execute!
			$query = ee()->db->get();

			// Get entry IDs
			$entry_ids = low_flatten_results($query->result_array(), 'entry_id');
			$entry_ids = array_unique($entry_ids);

			// Return immediately when no results are there
			if (empty($entry_ids)) break;
		}

		return $entry_ids;
	}

	// --------------------------------------------------------------------

	/**
	 * Get channel IDs based on field ID
	 */
	private function _get_channel_ids_by_field($field_id)
	{
		static $map = array();

		if ( ! $map)
		{
			$query = ee()->db
				->select('f.field_id, c.channel_id')
				->from('channel_fields f')
				->join('channels c', 'f.group_id = c.field_group')
				->where_in('c.site_id', $this->params->site_ids())
				->get();

			foreach ($query->result() AS $row)
			{
				$map[$row->field_id][] = $row->channel_id;
			}
		}

		return isset($map[$field_id]) ? $map[$field_id] : array();
	}

	// --------------------------------------------------------------------

	/**
	 * Results: remove rogue {low_search_search:...} vars
	 */
	public function results($query)
	{
		$this->_remove_rogue_vars($this->_pfx);
		return $query;
	}

}
// End of file lsf.field_search.php