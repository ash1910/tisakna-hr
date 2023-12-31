<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include base class
if ( ! class_exists('Low_search_base'))
{
	require_once(PATH_THIRD.'low_search/base.low_search.php');
}

/**
 * Low Search Update class
 *
 * @package        low_search
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-search
 * @copyright      Copyright (c) 2017, Low
 */
class Low_search_upd extends Low_search_base {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Actions used
	 *
	 * @access      private
	 * @var         array
	 */
	private $actions = array(
		array('Low_search', 'catch_search'),
		array('Low_search', 'build_index'),
		array('Low_search', 'save_search')
	);

	/**
	 * Hooks used
	 *
	 * @access      private
	 * @var         array
	 */
	private $hooks = array(
		'after_channel_entry_save', // was 'entry_submission_end'
		'after_channel_entry_delete',  // was 'delete_entries_loop'
		'channel_entries_query_result',
		'after_category_save', // was 'category_save'
		'after_category_delete', // 'category_delete'
		'after_channel_field_delete'
	);

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Install the module
	 *
	 * @access      public
	 * @return      bool
	 */
	public function install()
	{
		// --------------------------------------
		// Install tables
		// --------------------------------------

		foreach ($this->models AS $model)
		{
			ee()->$model->install();
		}

		// --------------------------------------
		// Add row to modules table
		// --------------------------------------

		ee()->db->insert('modules', array(
			'module_name'    => $this->class_name,
			'module_version' => $this->version,
			'has_cp_backend' => 'y'
		));

		// --------------------------------------
		// Add rows to action table
		// --------------------------------------

		foreach ($this->actions AS $row)
		{
			$this->_add_action($row);
		}

		// --------------------------------------
		// Add rows to extensions table
		// --------------------------------------

		foreach ($this->hooks AS $hook)
		{
			$this->_add_hook($hook);
		}

		// --------------------------------------

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Uninstall the module
	 *
	 * @return	bool
	 */
	public function uninstall()
	{
		// --------------------------------------
		// get module id
		// --------------------------------------

		$query = ee()->db
			->select('module_id')
			->from('modules')
			->where('module_name', $this->class_name)
			->get();

		// --------------------------------------
		// remove references from module_member_groups
		// --------------------------------------

		ee()->db->where('module_id', $query->row('module_id'));
		ee()->db->delete('module_member_groups');

		// --------------------------------------
		// remove references from modules
		// --------------------------------------

		ee()->db->where('module_name', $this->class_name);
		ee()->db->delete('modules');

		// --------------------------------------
		// remove references from actions
		// --------------------------------------

		ee()->db->where_in('class', array($this->class_name, $this->class_name.'_mcp'));
		ee()->db->delete('actions');

		// --------------------------------------
		// remove references from extensions
		// --------------------------------------

		ee()->db->where('class', $this->class_name.'_ext');
		ee()->db->delete('extensions');

		// --------------------------------------
		// Uninstall tables
		// --------------------------------------

		foreach ($this->models AS $model)
		{
			ee()->$model->uninstall();
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Update the module
	 *
	 * @return	bool
	 */
	public function update($current = '')
	{
		// --------------------------------------
		// Same version? A-okay, daddy-o!
		// --------------------------------------

		if ($current == '' OR version_compare($current, $this->version) === 0)
		{
			return FALSE;
		}

		// --------------------------------------
		// Update to 1.2.0
		// --------------------------------------

		if (version_compare($current, '1.2.0', '<'))
		{
			ee()->low_search_replace_log_model->install();
		}

		// --------------------------------------
		// Update to 2.0.0
		// --------------------------------------

		if (version_compare($current, '2.0.0', '<'))
		{
			// Insert another action
			$this->_add_action($this->actions[2]);

			// Change hook entry_submission_absolute_end to entry_submission_end
			// so the API triggers it
			ee()->db->where('class', $this->class_name.'_ext');
			ee()->db->where('hook', 'entry_submission_absolute_end');
			ee()->db->update('extensions', array(
				'method' => 'entry_submission_end',
				'hook'   => 'entry_submission_end'
			));
		}

		// --------------------------------------
		// Update to 2.1.0
		// --------------------------------------

		if (version_compare($current, '2.1.0', '<'))
		{
			$this->_v210();
		}

		// --------------------------------------
		// Update to 2.4.0
		// --------------------------------------

		if (version_compare($current, '2.4.0', '<'))
		{
			$this->_v240();
		}

		// --------------------------------------
		// Update to 3.0.0
		// --------------------------------------

		if (version_compare($current, '3.0.0', '<'))
		{
			$this->_v300();
		}

		// --------------------------------------
		// Update to 3.0.2
		// --------------------------------------

		if (version_compare($current, '3.0.2', '<'))
		{
			$this->_v302();
		}

		// --------------------------------------
		// Update to 3.3.0
		// --------------------------------------

		if (version_compare($current, '3.3.0', '<'))
		{
			$this->_v330();
		}

		// --------------------------------------
		// Update to 3.5.0
		// --------------------------------------

		if (version_compare($current, '3.5.0', '<'))
		{
			$this->_v350();
		}

		// --------------------------------------
		// Update to 3.5.1
		// --------------------------------------

		if (version_compare($current, '3.5.1', '<'))
		{
			$this->_v351();
		}

		// --------------------------------------
		// Update to 4.0.0
		// --------------------------------------

		if (version_compare($current, '4.0.0', '<'))
		{
			$this->_v400();
		}

		// --------------------------------------
		// Update to 4.3.0
		// --------------------------------------

		if (version_compare($current, '4.3.0', '<'))
		{
			$this->_v430();
		}

		// --------------------------------------
		// Update to 5.0.0
		// --------------------------------------

		if (version_compare($current, '5.0.0', '<'))
		{
			$this->_v500();
		}

		// --------------------------------------
		// Update to 5.1.0
		// --------------------------------------

		if (version_compare($current, '5.1.0', '<'))
		{
			$this->_v510();
		}

		// --------------------------------------
		// Update extension version
		// --------------------------------------

		ee()->db->where('class', $this->class_name.'_ext')
		        ->update('extensions', array('version' => $this->version));

		// Return TRUE to update version number in DB
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Add action to actions table
	 *
	 * @access     private
	 * @param      array
	 * @return     void
	 */
	private function _add_action($array)
	{
		list($class, $method) = $array;

		ee()->db->insert('actions', array(
			'class'  => $class,
			'method' => $method,
			'csrf_exempt' => (int) ($method == 'catch_search')
		));
	}

	/**
	 * Add hook to extensions table
	 *
	 * @access     private
	 * @param      string
	 * @return     void
	 */
	private function _add_hook($hook)
	{
		ee()->db->insert('extensions', array(
			'class'     => $this->class_name.'_ext',
			'method'    => $hook,
			'hook'      => $hook,
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y',
			'settings'  => serialize(ee()->low_search_settings->get())
		));
	}

	// --------------------------------------------------------------------

	/**
	 * Update routines for version 2.1.0
	 *
	 * @access     private
	 * @return     void
	 */
	private function _v210()
	{
		// Fields to add to the DB
		$fields = array(
			'modifier' => 'decimal(2,1) unsigned NOT NULL default 1.0',
			'excerpt'  => 'int(6) unsigned NOT NULL default 0'
		);

		// Template query
		$tmpl = 'ALTER TABLE `%s` ADD `%s` %s AFTER `collection_label`';
		$tbl = ee()->low_search_collection_model->table();

		// Add fields
		foreach ($fields AS $field => $properties)
		{
			ee()->db->query(sprintf($tmpl, $tbl, $field, $properties));
		}

		// Get the collections and re-do the settings
		foreach (ee()->low_search_collection_model->get_all() AS $row)
		{
			// Initiate data array
			$data = array();

			// Decode the settings
			$settings = low_search_decode($row['settings'], FALSE);

			// Set new property values
			$data['modifier'] = (float) (isset($settings['modifier']) ? $settings['modifier'] : 1.0);
			$data['excerpt']  = (int) (isset($settings['excerpt']) ? $settings['excerpt'] : 0);

			// Remove these properties from settings
			unset($settings['modifier'], $settings['excerpt']);

			// filter it
			$settings = array_filter($settings);

			// Encode the new settings for DB usage
			$data['settings'] = low_search_encode($settings, FALSE);

			// Update row
			ee()->low_search_collection_model->update($row['collection_id'], $data);
		}
	}

	/**
	 * Update routines for version 2.4.0
	 *
	 * @access     private
	 * @return     void
	 */
	private function _v240()
	{
		// Register category hooks
		$this->_add_hook($this->hooks[3]);
		$this->_add_hook($this->hooks[4]);
	}

	/**
	 * Update routines for version 3.0.0
	 *
	 * @access     private
	 * @return     void
	 */
	private function _v300()
	{
		// Install Groups and Shortcuts table
		ee()->low_search_group_model->install();
		ee()->low_search_shortcut_model->install();

		// Insert another action
		$this->_add_action($this->actions[2]);
	}

	/**
	 * Update routines for version 3.0.2
	 *
	 * @access     private
	 * @return     void
	 */
	private function _v302()
	{
		// Increase priority number for first hook
		ee()->db->where('class', $this->class_name.'_ext')
		        ->where('hook', $this->hooks[0])
		        ->update('extensions', array('priority' => '101'));
	}

	/**
	 * Update routines for version 3.3.0
	 *
	 * @access     private
	 * @return     void
	 */
	private function _v330()
	{
		// Template query
		$sql = sprintf(
			'ALTER TABLE `%s` ADD `num_results` int(10) unsigned',
			ee()->low_search_log_model->table()
		);

		// Add the field to the table
		ee()->db->query($sql);
	}

	/**
	 * Update routines for version 3.5.0
	 *
	 * @access     private
	 * @return     void
	 */
	private function _v350()
	{
		// Template query
		$sql = sprintf(
			'ALTER TABLE `%s` ADD `language` varchar(5) AFTER `collection_label`',
			ee()->low_search_collection_model->table()
		);

		// Add the field to the table
		ee()->db->query($sql);
	}

	/**
	 * Update routines for version 3.5.1
	 *
	 * @access     private
	 * @return     void
	 */
	private function _v351()
	{
		// Template query
		$sql = sprintf(
			'ALTER TABLE `%s` MODIFY `modifier` decimal(4,1) unsigned NOT NULL default 1.0',
			ee()->low_search_collection_model->table()
		);

		// Add the field to the table
		ee()->db->query($sql);
	}

	/**
	 * Update routines for version 4.0.0
	 *
	 * @access     private
	 * @return     void
	 */
	private function _v400()
	{
		ee()->low_search_word_model->install();
	}

	/**
	 * Update routines for version 4.3.0
	 *
	 * @access     private
	 * @return     void
	 */
	private function _v430()
	{
		// Remove custom_field_modify_data hook
		ee()->db
			->where('class', $this->class_name.'_ext')
			->where('method', 'custom_field_modify_data')
			->delete('extensions');
	}

	/**
	 * Update routines for version 5.0.0
	 *
	 * @access     private
	 * @return     void
	 */
	private function _v500()
	{
		// Remove and re-add the renamed hooks
		ee()->db
			->where('class', $this->class_name.'_ext')
			->delete('extensions');

		foreach ($this->hooks as $hook)
		{
			$this->_add_hook($hook);
		}
	}

	/**
	 * Update routines for version 5.1.0
	 *
	 * @access     private
	 * @return     void
	 */
	private function _v510()
	{
		// New hook to add
		$hook = $this->hooks[5];

		// Check if it already exists
		$count = ee()->db
			->from('extensions')
			->where('class', $this->class_name.'_ext')
			->where('hook', $hook)
			->count_all_results();

		// If not, add the hook
		if ( ! $count)
		{
			$this->_add_hook($hook);
		}
	}

} // End class

/* End of file upd.low_search.php */
