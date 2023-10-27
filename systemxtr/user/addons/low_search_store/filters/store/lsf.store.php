<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Filter by store:key="val"
 *
 * @package        low_search
 * @author         Lodewijk Schutte ~ Low <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-search
 * @copyright      Copyright (c) 2014, Low
 */
class Low_search_filter_store extends Low_search_filter {

	/**
	 * Prefix
	 */
	private $_pfx = 'store:';

	/**
	 * Range attributes
	 */
	private $_ranges = array(
		'price',
		'weight',
		'width',
		'height',
		'handling',
		'stock'
	);

	/**
	 * Boolean attributes
	 */
	private $_bools = array(
		'free_shipping',
		'tax_exempt'
	);

	/**
	 * Range separator
	 */
	private $_sep = '|';

	/**
	 * Ordered results?
	 */
	private $_ordered;

	// --------------------------------------------------------------------

	/**
	 * Allows for store:key="val" parameters
	 *
	 * @access     private
	 * @return     void
	 */
	public function filter($entry_ids)
	{
		// --------------------------------------
		// Get store params
		// --------------------------------------

		$params = $this->params->get_prefixed($this->_pfx, TRUE);
		$params = array_filter($params, 'low_not_empty');

		// --------------------------------------
		// Are we ordering by a Store field?
		// --------------------------------------

		if (($orderby = $this->params->get('orderby')) &&
			(preg_match("/^{$this->_pfx}([a-z]+)$/", $orderby, $match)) &&
			in_array($match[1], $this->_ranges))
		{
			$orderby = $match[1];
			$sort = $this->params->get('sort', 'asc');
		}
		else
		{
			$orderby = $sort = NULL;
		}

		// --------------------------------------
		// Bail out of nothing's there
		// --------------------------------------

		if (empty($params) && empty($orderby))
		{
			return $entry_ids;
		}

		// --------------------------------------
		// Log it
		// --------------------------------------

		$this->_log('Applying '.__CLASS__);

		// --------------------------------------
		// Limit to modifiers?
		// --------------------------------------

		if ($mods = low_array_get_prefixed($params, 'mod:', TRUE))
		{
			$this->_log('Querying Store modifiers');

			// 1 query per modifier probably works best,
			// since we need to join and all
			foreach ($mods AS $key => $val)
			{
				// Prep the key
				$key = str_replace('_', ' ', $key);

				// Will allow for multiple values
				list($val, $in) = $this->params->explode($val);

				// Start query
				ee()->db->select('m.entry_id')
				        ->distinct()
				        ->from('store_product_modifiers m')
				        ->join('store_product_options o', 'm.product_mod_id = o.product_mod_id', 'left')
				        ->where('m.mod_name', $key)
				        ->{$in ? 'where_in' : 'where_not_in'}('o.opt_name', $val);

				// Limit by previous entry IDs
				if ($entry_ids)
				{
					ee()->db->where_in('m.entry_id', $entry_ids);
				}

				// Execute query
				$query = ee()->db->get();

				// Get the entry IDs
				$entry_ids = low_flatten_results($query->result_array(), 'entry_id');

				// Break out of the loop if there are no results
				if (empty($entry_ids)) break;
			}

			// Return no results?
			if (empty($entry_ids))
			{
				$this->_log('Modifiers returned no results');
				return $entry_ids;
			}
		}

		// --------------------------------------
		// Limit to On Sale?
		// --------------------------------------

		$on_sale  = empty($params['on_sale']) ? NULL : $params['on_sale'];
		$sale_ids = array();

		if ( ! is_null($on_sale))
		{
			$this->_log('Querying Store sales');

			// Now!
			$now = ee()->localize->now;

			// Get active sales
			$query = ee()->db->select('name, member_group_ids, entry_ids, category_ids')
			       ->from('store_sales')
			       ->where('enabled', 1)
			       ->where_in('site_id', $this->params->site_ids())
			       ->where("(start_date IS NULL OR start_date <= '{$now}')")
			       ->where("(end_date IS NULL OR end_date >= '{$now}')")
			       ->where('(per_item_discount > 0 OR percent_discount > 0)')
			       ->get();

			// Get the sales as array
			$sales = $query->result();

			// No items on sale? Exit
			if ($on_sale == 'yes' && empty($sales))
			{
				$this->_log('No active sales found');
				return array();
			}

			// Loop through sales and query the DB
			foreach ($sales AS $sale)
			{
				// Skip invalid member groups
				if ($sale->member_group_ids)
				{
					$groups = array_filter(explode('|', $sale->member_group_ids));
					if ( ! in_array(ee()->session->userdata('group_id'), $groups)) continue;
				}

				// Keep track of this
				$this->_log('Getting entries for sale '.$sale->name);

				// Begin query
				ee()->db->select('p.entry_id')
				        ->distinct()
				        ->from('store_products p');

				// Limit to Entry IDs
				if ($sale->entry_ids)
				{
					ee()->db->where_in('p.entry_id', explode('|', $sale->entry_ids));
				}

				// Limit to Categories
				if ($sale->category_ids)
				{
					ee()->db->join('category_posts cp', 'cp.entry_id = p.entry_id');
					ee()->db->where_in('cp.cat_id', explode('|', $sale->category_ids));
				}

				// Execute query and ge the IDs
				$ids = low_flatten_results(ee()->db->get()->result_array(), 'entry_id');

				// Add to on_sale ids
				$sale_ids = array_unique(array_merge($sale_ids, $ids));
			}

			// No items on sale? Exit
			if ($on_sale == 'yes' && empty($sale_ids))
			{
				$this->_log('No products on sale');
				return array();
			}

			// MOAR LOGGING
			$this->_log('There are '.count($sale_ids).' entries on sale');
		}

		// --------------------------------------
		// Initiate clauses
		// --------------------------------------

		$select = array('p.entry_id');
		$where  = $having = array();

		// --------------------------------------
		// Are there ranges here?
		// --------------------------------------

		foreach ($this->_ranges AS $key)
		{
			$array = ($key == 'stock') ? 'having' : 'where';

			// Range in single parameter
			if (isset($params[$key]))
			{
				// Exact match if no | is present in value
				if (strpos($params[$key], $this->_sep) === FALSE)
				{
					${$array}[$key] = $params[$key];
				}
				else
				{
					list($from, $to) = explode($this->_sep, $params[$key], 2);
					if (strlen($from)) ${$array}[$key . ' >='] = $from;
					if (strlen($to))   ${$array}[$key . ' <='] = $to;
				}
			}

			// Range in 2 separate parameters
			else
			{
				if (isset($params[$key . ':min']))
				{
					${$array}[$key . ' >='] = $params[$key . ':min'];
				}

				if (isset($params[$key . ':max']))
				{
					${$array}[$key . ' <='] = $params[$key . ':max'];
				}
			}
		}

		// --------------------------------------
		// Are there booleans here?
		// --------------------------------------

		foreach ($this->_bools AS $key)
		{
			if (isset($params[$key]))
			{
				$where[$key] = (int) ($params[$key] == 'yes');
			}
		}

		// --------------------------------------
		// Default to
		// --------------------------------------

		ee()->db->select('p.entry_id, SUM(`stock_level`) AS `stock`')
		        ->from('store_products p')
		        ->join('store_stock s', 'p.entry_id = s.entry_id')
		        ->where('price IS NOT NULL')
		        ->group_by('p.entry_id');

		// --------------------------------------
		// Limit by given entry ids?
		// --------------------------------------

		if ( ! empty($entry_ids))
		{
			ee()->db->where_in('p.entry_id', $entry_ids);
		}

		// --------------------------------------
		// Limit by on sale
		// --------------------------------------

		if ( ! empty($on_sale) && ! empty($sale_ids))
		{
			$in = ($on_sale != 'no');
			ee()->db->{$in ? 'where_in' : 'where_not_in'}('p.entry_id', $sale_ids);
		}

		// --------------------------------------
		// Limit by where clause
		// --------------------------------------

		if ( ! empty($where))
		{
			ee()->db->where($where);
		}

		// --------------------------------------
		// Limit to SKU?
		// --------------------------------------

		// Regular param
		if (isset($params['sku']))
		{
			list($sku, $in) = $this->params->explode($params['sku']);
			ee()->db->{$in ? 'where_in' : 'where_not_in'}('s.sku', $sku);
		}

		// Search param
		if (isset($params['search:sku']))
		{
			ee()->db->where($this->_get_where_search('s.sku', $params['search:sku']), NULL, FALSE);
		}

		// --------------------------------------
		// Limit by having clause
		// --------------------------------------

		if ( ! empty($having))
		{
			ee()->db->having($having);
		}

		// --------------------------------------
		// Ordering?
		// --------------------------------------

		if ($orderby && $sort)
		{
			ee()->db->order_by($orderby, $sort);

			// Remember that we're displaying ordered results
			$this->_ordered = TRUE;

			// Beware of the double flip
			$this->params->set('sort', 'asc');

		}
		else
		{
			$this->_ordered = FALSE;
		}

		// --------------------------------------
		// Get 'em, boys.
		// --------------------------------------

		$query = ee()->db->get();

		// --------------------------------------
		// And get the entry ids
		// --------------------------------------

		$entry_ids = low_flatten_results($query->result_array(), 'entry_id');
		$entry_ids = array_unique(array_filter($entry_ids));

		return $entry_ids;
	}


	// --------------------------------------------------------------------

	/**
	 * Fixed order?
	 */
	public function fixed_order()
	{
		return $this->_ordered;
	}
}