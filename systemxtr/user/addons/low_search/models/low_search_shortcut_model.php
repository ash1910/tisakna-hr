<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include super model
if ( ! class_exists('Low_search_model'))
{
	require_once(PATH_THIRD.'low_search/model.low_search.php');
}

/**
 * Low Search Search Model class
 *
 * @package        low_search
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-search
 * @copyright      Copyright (c) 2017, Low
 */
class Low_search_shortcut_model extends Low_search_model {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Errors after validating
	 */
	private $_errors = array();

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access      public
	 * @return      void
	 */
	function __construct()
	{
		// Call parent constructor
		parent::__construct();

		// Initialize this model
		$this->initialize(
			'low_search_shortcuts',
			'shortcut_id',
			array(
				'site_id'        => 'int(4) unsigned NOT NULL',
				'group_id'       => 'int(4) unsigned NOT NULL',
				'shortcut_name'  => 'varchar(40) NOT NULL',
				'shortcut_label' => 'varchar(150)',
				'parameters'     => 'TEXT NOT NULL',
				'sort_order'     => 'int(4) unsigned NOT NULL'
			)
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Installs given table
	 *
	 * @access      public
	 * @return      void
	 */
	public function install()
	{
		// Call parent install
		parent::install();

		// Add indexes to table
		foreach (array('site_id', 'group_id') AS $key)
		{
			ee()->db->query("ALTER TABLE {$this->table()} ADD INDEX (`{$key}`)");
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Get template attrs
	 *
	 * @access      public
	 * @return      array
	 */
	public function get_template_attrs()
	{
		return array('shortcut_id', 'shortcut_name', 'shortcut_label');
	}

	// --------------------------------------------------------------------

	/**
	 * Get shortcuts for given group
	 *
	 * @access      public
	 * @return      void
	 */
	public function get_by_group($id)
	{
		ee()->db->where('group_id', $id);
		ee()->db->order_by('sort_order', 'asc');
		return $this->get_all();
	}

	// --------------------------------------------------------------------

	/**
	 * Get shortcuts for given group
	 *
	 * @access      public
	 * @return      void
	 */
	public function get_group_counts($site_id)
	{
		$query = ee()->db->select('group_id, COUNT(*) AS num')
		       ->from($this->table())
		       ->where('site_id', $site_id)
		       ->group_by('group_id')
		       ->get();

		return low_flatten_results($query->result_array(), 'num', 'group_id');
	}

	// --------------------------------------------------------------------

	/**
	 * Get one saved search
	 *
	 * @access      public
	 * @return      void
	 */
	public function get_one($id, $attr = FALSE)
	{
		static $cache = array();

		// Make sure $attr is set
		if ( ! $attr) $attr = $this->pk();

		// Compose cache key
		$key = $attr.':'.$id;

		// Check cache
		if ( ! array_key_exists($key, $cache))
		{
			// For this site only
			ee()->db->where('site_id', $this->site_id);

			if ($row = parent::get_one($id, $attr))
			{
				$row['parameters'] = low_search_decode($row['parameters'], FALSE);
			}

			$cache[$key] = $row;
		}

		return $cache[$key];
	}

	// --------------------------------------------------------------------

	/**
	 * Validate given array
	 *
	 * @access      public
	 * @param       array
	 * @return      mixed
	 */
	public function validate($data)
	{
		// Reset errors
		$this->_errors = array();

		// --------------------------------------
		// Trim input
		// --------------------------------------

		$data = array_map('trim', $data);

		// --------------------------------------
		// Validate saved_id
		// --------------------------------------

		if (empty($data['shortcut_id']) || ! is_numeric($data['shortcut_id']))
		{
			$data['shortcut_id'] = NULL;
		}

		// --------------------------------------
		// Validate group_id
		// --------------------------------------

		if (empty($data['group_id']) || ! is_numeric($data['group_id']))
		{
			$this->_errors['shortcut_invalid_group'];
		}

		// --------------------------------------
		// Validate parameters
		// --------------------------------------

		if ( ! empty($data['parameters']))
		{
			// String, but not json
			if (is_string($data['parameters']) && substr($data['parameters'], 0, 1) != '{')
			{
				$data['parameters'] = low_search_decode($data['parameters']);
			}

			// Convert array to json
			if (is_array($data['parameters']))
			{
				$data['parameters'] = low_search_encode($data['parameters'], FALSE);
			}

			// If something went wrong, skip it
			if (empty($data['parameters']))
			{
				$this->_errors[] = 'shortcut_invalid_params';
			}
		}
		else
		{
			$this->_errors[] = 'shortcut_no_params';
		}

		// --------------------------------------
		// Validate name
		// --------------------------------------

		if ( ! empty($data['shortcut_name']))
		{
			// shortcut_name should be url-safe
			if (preg_match('/^[\w-]+$/', $data['shortcut_name']))
			{
				// shortcut_name should be unique
				$query = ee()->db->from($this->table())
				       ->where('shortcut_name' , $data['shortcut_name']);

				// Exclude this row
				if ($data['shortcut_id'])
				{
					ee()->db->where('shortcut_id !=', $data['shortcut_id']);
				}

				// Check it
				if ($query->count_all_results())
				{
					$this->_errors[] = 'shortcut_name_not_available';
				}
			}
			else
			{
				$this->_errors[] = 'shortcut_invalid_name';
			}
		}
		else
		{
			$this->_errors[] = 'shortcut_no_name';
		}

		// --------------------------------------
		// Validate Label; fall back to name
		// --------------------------------------

		if (empty($data['shortcut_label']))
		{
			$data['shortcut_label'] = $data['shortcut_name'];
		}

		// --------------------------------------
		// Return modified data if valid; FALSE if invalid
		// --------------------------------------

		return empty($this->_errors) ? $data : FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Return errors
	 *
	 * @access      public
	 * @return      array
	 */
	public function errors()
	{
		return $this->_errors;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete shortcuts by group
	 */
	public function delete_by_group($group_id)
	{
		if ( ! is_array($group_id)) $group_id = array($group_id);
		ee()->db->where_in('group_id', $group_id);
		ee()->db->delete($this->table());
	}

} // End class

/* End of file Low_search_shortcut_model.php */