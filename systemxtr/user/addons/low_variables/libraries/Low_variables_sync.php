<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Low Variables Sync class
 *
 * @package        low_variables
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-variables
 * @copyright      Copyright (c) 2009-2016, Low
 */
class Low_variables_sync {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Variable file name extension
	 *
	 * @var        string
	 * @access     private
	 */
	private $var_ext = '.html';

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Sync EE vars and Low vars
	 *
	 * Deletes Low Variables that reference to non-existing EE Variables,
	 * Creates default Low Variables that have no reference to existing EE Vars.
	 *
	 * @access     private
	 * @return     void
	 */
	public function native()
	{
		// -------------------------------------
		//  Get all native variable ids
		// -------------------------------------

		$query  = ee()->db->select('variable_id')->from('global_variables')->get();
		$ee_ids = low_flatten_results($query->result_array(), 'variable_id');

		// -------------------------------------
		//  Sync based on ee ids
		// -------------------------------------

		if ( ! empty($ee_ids))
		{
			// Delete references to non-existing native vars
			ee()->db->where_not_in('variable_id', $ee_ids)->delete('low_variables');

			// Get all Low Variables
			$query   = ee()->db->select('variable_id')->from('low_variables')->get();
			$low_ids = low_flatten_results($query->result_array(), 'variable_id');

			// Get ids that do not exist in low_var but do exist in ee_var
			if ($diff = array_diff($ee_ids, $low_ids))
			{
				foreach ($diff as $i => $var_id)
				{
					ee()->db->insert('low_variables', array(
						'variable_id'    => $var_id,
						'group_id'       => '0',
						'variable_order' => $i,
						'edit_date'      => time()
					));
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Sync variables and var files
	 *
	 * @access     protected
	 * @param      array
	 * @return     void
	 */
	public function files($ids = array())
	{
		// -------------------------------------
		//  Settings
		// -------------------------------------

		$settings = ee()->low_variables_settings->get();

		// -------------------------------------
		//  Get vars from DB
		// -------------------------------------

		$vars = ee()->low_variables_variable_model->get_file_vars($ids);

		// -------------------------------------
		//  Still no vars? Exit
		// -------------------------------------

		if ( ! $vars) return;

		// -------------------------------------
		//  Check if right directory exists
		// -------------------------------------

		$path = $this->_get_var_filepath();

		if ( ! @is_dir($path))
		{
			if ( ! @mkdir($path, DIR_WRITE_MODE))
			{
				return FALSE;
			}
			@chmod($path, DIR_WRITE_MODE);
		}

		// -------------------------------------
		//  Load file helper
		// -------------------------------------

		ee()->load->helper('file');

		// -------------------------------------
		//  Get existing files only for CP requests for performance reasons
		// -------------------------------------

		$files = (REQ == 'CP' || REQ == 'ACTION') ? get_filenames($path) : array();

		// -------------------------------------
		//  Loop thru save_as_file-variables
		// -------------------------------------

		foreach ($vars as $row)
		{
			// Determine this var's file name
			$file  = $this->_get_var_filename($row['variable_name']);
			$name  = $this->_get_var_filename($row['variable_name'], FALSE);
			$write = FALSE;

			// Check if file exists
			if (file_exists($file))
			{
				// If it does exist, check it's modified date
				$info = get_file_info($file, 'date');

				// If file is younger than DB, read file and update DB
				// Do the same for one way sync
				if (($settings['one_way_sync'] == 'n' && $info['date']  > $row['edit_date']) ||
					($settings['one_way_sync'] == 'y' && $info['date'] != $row['edit_date']))
				{
					// Read the file
					$file_data = read_file($file);

					// Beware this wonkiness
					$file_data = str_replace("\r\n", "\n", $file_data);

					// Data to update
					$data = array('variable_data' => $file_data);

					// Optionally the edit date, if on EE 3.1+
					if (version_compare(APP_VER, '3.1.0', '>='))
					{
						$data['edit_date'] = $info['date'];
					}

					// Update native table with file data
					ee()->db->update(
						'global_variables',
						$data,
						"variable_id = '{$row['variable_id']}'"
					);

					// Update low_variables table
					ee()->db->update(
						'low_variables',
						array('edit_date' => $info['date']),
						"variable_id = '{$row['variable_id']}'"
					);
				}
				elseif ($settings['one_way_sync'] == 'n' && $info['date'] < $row['edit_date'])
				{
					// Write to file if server file is older than DB
					// But only if one way sync is off
					$write = TRUE;
				}
			}
			else
			{
				// File doesn't exist - write new file
				$write = TRUE;
			}

			// Write to file, if necessary
			if ($write)
			{
				write_file($file, $row['variable_data']);
				@chmod($file, FILE_WRITE_MODE);
			}

			// Remove reference in the files list
			if (($key = array_search($name, $files)) !== FALSE)
			{
				unset($files[$key]);
			}

		} // End foreach var

		// -------------------------------------
		//  Delete rogue files
		// -------------------------------------

		foreach ($files as $filename)
		{
			@unlink($path.$filename);
		}
	}

	/**
	 * Get (full) filename for given var
	 *
	 * @access     private
	 * @param      string
	 * @param      bool
	 * @return     string
	 */
	private function _get_var_filename($var_name, $full = TRUE)
	{
		$filename = $var_name . $this->var_ext;

		if ($full)
		{
			$filename = $this->_get_var_filepath() . $filename;
		}

		return $filename;
	}

	/**
	 * Get file path for saving var files for this site
	 *
	 * @access     private
	 * @return     string
	 */
	private function _get_var_filepath()
	{
		return rtrim(ee()->low_variables_settings->file_path, '/').'/'.ee()->config->item('site_short_name').'/';
	}

}