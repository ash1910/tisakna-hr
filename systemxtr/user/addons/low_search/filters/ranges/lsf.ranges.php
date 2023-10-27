<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Filter by search:title="foo"
 *
 * @package        low_search
 * @author         Lodewijk Schutte ~ Low <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-search
 * @copyright      Copyright (c) 2017, Low
 */
class Low_search_filter_ranges extends Low_search_filter {

	/**
	 * Prefixes
	 */
	private $_pfxs = array(
		'range:',
		'range-from:',
		'range-to:'
	);

	/**
	 * Separator character for ranges
	 */
	private $_sep = '|';

	/**
	 * Current ranges
	 */
	private $_ranges;

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Search parameters for range:field params and return set of ids that match it
	 *
	 * @access      private
	 * @return      void
	 */
	public function filter($entry_ids)
	{
		// --------------------------------------
		// Reset ranges
		// --------------------------------------

		$this->_ranges = $params = array();

		// --------------------------------------
		// Get ranges params
		// --------------------------------------

		foreach ($this->_pfxs as $pfx)
		{
			$params = array_merge($params, $this->params->get_prefixed($pfx));
		}

		$params = array_filter($params, 'low_not_empty');

		// --------------------------------------
		// Don't do anything if nothing's there
		// --------------------------------------

		if (empty($params)) return $entry_ids;

		// --------------------------------------
		// Log it
		// --------------------------------------

		$this->_log('Applying '.__CLASS__);

		// --------------------------------------
		// Load this, to be on the safe side
		// --------------------------------------

		ee()->load->library('localize');

		// --------------------------------------
		// Collect ranges
		// --------------------------------------

		$ranges = array();

		foreach ($params as $key => $val)
		{
			// Initiate range row
			$row = array(
				'reverse'   => 0,
				'table'     => NULL,
				'field'     => NULL,
				'field_min' => NULL,
				'field_max' => NULL,
				'min'       => NULL,
				'max'       => NULL,
				'point'     => NULL
			);

			// Split key into prefix and the rest of the key
			list($pfx, $key) = explode(':', $key, 2);

			// If key has a colon, it could be grid/matrix OR reverse range
			if (strpos($key, ':'))
			{
				list($field1, $field2) = explode(':', $key, 2);

				// Skip invalid fields
				if ( ! ($id = $this->fields->id($field1))) continue;

				// Grid field?
				if ($this->fields->is_grid($field1) &&
					($col_id = $this->fields->grid_col_id($id, $field2)))
				{
					$row['table'] = 'channel_grid_field_'.$id;
					$row['field'] = 'col_id_'.$col_id;
				}
				// Matrix field?
				elseif ($this->fields->is_matrix($field1) &&
					($col_id = $this->fields->matrix_col_id($id, $field2)))
				{
					$row['table'] = 'matrix_data';
					$row['field'] = 'col_id_'.$col_id;
				}
				// Both field1/field2 belong to the same table: reverse range!
				elseif ( ! ($this->fields->is_native($field1) XOR $this->fields->is_native($field2)))
				{
					// Second field must be valid
					if ( ! $this->fields->id($field2)) continue;

					// Reverse range on native fields?
					$native = $this->fields->is_native($field1);

					// Set rules accordingly
					$row['reverse']   = 1;
					$row['table']     = $native ? 'channel_titles' : 'channel_data';
					$row['field_min'] = $native ? $field1 : $this->fields->name($field1);
					$row['field_max'] = $native ? $field2 : $this->fields->name($field2);
				}
			}

			// Targeting a native field here
			elseif ($this->fields->is_native($key))
			{
				$row['table'] = 'channel_titles';
				$row['field'] = $key;
			}

			// Regular old custom fields
			elseif ($this->fields->id($key))
			{
				$row['table'] = 'channel_data';
				$row['field'] = $this->fields->name($key);
			}

			// We all good here?
			if (empty($row['table'])) continue;

			// Initiate values for this range
			$point = $min = $max = NULL;

			// Check prefix and get from/to values accordingly
			switch ($pfx)
			{
				case 'range':
					// Fallback to semi-colon for backward compatibility
					$char = (strpos($val, ';') !== FALSE) ? ';' : $this->_sep;

					// Set from/to vals or point val based on separator
					(strpos($val, $char) !== FALSE)
						? (list($min, $max) = explode($char, $val, 2))
						: ($point = $val);
				break;

				case 'range-from':
					$min = $val;
				break;

				case 'range-to':
					$max = $val;
				break;
			}

			// Make sure the values are numeric
			$row['point'] = $this->_validate_value($point, $key);
			$row['min']   = $this->_validate_value($min, $key);
			$row['max']   = $this->_validate_value($max, $key);

			// Strip out any NULL values
			$row = array_filter($row, 'low_not_empty');

			// Merge this with any existing ranges, so range-from and range-to can be split
			$existing     = array_key_exists($key, $ranges) ? $ranges[$key] : array();
			$ranges[$key] = array_merge($existing, $row);
		}

		// --------------------------------------
		// Validate complete ranges
		// --------------------------------------

		foreach ($ranges as $key => $row)
		{
			// If a regular range, either from or to must be defined
			if ( ! $row['reverse'] && (isset($row['min']) || isset($row['max'])))
			{
				$this->_ranges[$row['table']][$key] = $row;
			}

			// If reverse range, either point must be defined, or both from and to
			if ($row['reverse'] && (isset($row['point']) || (isset($row['min']) && isset($row['max']))) )
			{
				$this->_ranges[$row['table']][$key] = $row;
			}
		}

		// --------------------------------------
		// No ranges, bail out
		// --------------------------------------

		if (empty($this->_ranges))
		{
			$this->_log('No valid ranges found');
			return $entry_ids;
		}

		// --------------------------------------
		// Get channel IDs before starting the query
		// --------------------------------------

		$channel_ids = ee()->low_search_collection_model->get_channel_ids();

		// --------------------------------------
		// Query each table once
		// --------------------------------------

		foreach ($this->_ranges as $table => $ranges)
		{
			// Start query
			ee()->db->distinct()->select('entry_id')->from($table);

			// Limit by given entry ids?
			if ( ! empty($entry_ids))
			{
				ee()->db->where_in('entry_id', $entry_ids);
			}

			// Limit only for non-grid tables
			if (in_array($table, array('channel_titles', 'channel_data')))
			{
				// Limit by channel
				if ($channel_ids)
				{
					ee()->db->where_in('channel_id', $channel_ids);
				}

				// Limit by site
				if ($site_ids = $this->params->site_ids())
				{
					ee()->db->where_in('site_id', $site_ids);
				}
			}

			// And do the range thing
			foreach ($ranges as $key => $range)
			{
				// Exclude values from range?
				$exclude_both = $this->params->in_param('range:'.$key, 'exclude');
				$exclude_min  = $this->params->in_param('range-from:'.$key, 'exclude');
				$exclude_max  = $this->params->in_param('range-to:'.$key, 'exclude');

				// Reverse range: min/max fields against single value or min/max values (overlap)
				if ($range['reverse'])
				{
					// Check a point
					if (isset($range['point']))
					{
						ee()->db->where(sprintf(
							"'%s' BETWEEN %s AND %s",
							$range['point'],
							$range['field_min'],
							$range['field_max']
						), NULL, FALSE);
					}
					// Check min/max values, which must be here due to prior validation
					else
					{
						$lt = ($exclude_both || $exclude_min) ? ' <' : ' <=';
						$gt = ($exclude_both || $exclude_max) ? ' >' : ' >=';

						ee()->db->where($range['field_min'].$lt, $range['max']);
						ee()->db->where($range['field_max'].$gt, $range['min']);
					}
				}
				// Normal range: single field against min/max values
				else
				{
					// Limit by Greater Than option
					if (isset($range['min']))
					{
						$gt = ($exclude_both || $exclude_min) ? ' >' : ' >=';

						ee()->db->where($range['field'].$gt, $range['min']);
					}

					// Limit by Lesser Than option
					if (isset($range['max']))
					{
						$lt = ($exclude_both || $exclude_max) ? ' <' : ' <=';

						ee()->db->where($range['field'].$lt, $range['max']);
					}
				}
			}

			// Thunderbirds are GO!
			$query = ee()->db->get();

			// And get the entry ids
			$entry_ids = low_flatten_results($query->result_array(), 'entry_id');

			// No need for more stuff if we have no results
			if (empty($entry_ids)) break;
		}

		// --------------------------------------
		// Return it dawg
		// --------------------------------------

		return $entry_ids;
	}


	// --------------------------------------------------------------------

	/**
	 * Validate range value
	 */
	private function _validate_value($val, $field)
	{
		// If value already is numeric or NULL, return that
		if (is_numeric($val) || is_null($val))
		{
			return $val;
		}

		// Check field for colons
		if ($i = strpos($field, ':'))
		{
			$field = substr($field, 0, $i);
		}

		if ($this->fields->is_date($field) || $this->fields->is_grid($field) || $this->fields->is_matrix($field))
		{
			if ($val = ee()->localize->string_to_timestamp($val))
			{
				// Oh, edit_date, you so cray-cray!
				if ($field == 'edit_date')
				{
					$val = date('YmdHis', $val);
				}
			}
			else
			{
				$val = NULL;
			}

			return $val;
		}

		return NULL;
	}

	// --------------------------------------------------------------------

	/**
	 * Results: remove rogue {low_search_range...:...} vars
	 */
	public function results($query)
	{
		$this->_remove_rogue_vars($this->_pfxs);
		return $query;
	}

}
// End of file lsf.ranges.php