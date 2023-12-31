<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Low Variables Types Class
 *
 * Loads up Low Variables types
 *
 * @package        low_variables
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-variables
 * @copyright      Copyright (c) 2009-2016, Low
 */

class Low_variables_types {

	/**
	 * Default variable type
	 */
	const DEFAULT_TYPE = 'low_textarea';

	/**
	 * Loaded types
	 */
	private $_types;

	// --------------------------------------------------------------------

	/*
	 * Load one given type
	 */
	public function load($what = NULL)
	{
		if (is_null($what))
		{
			return $this->_load();
		}
		elseif (is_string($what))
		{
			return $this->_load(array($what));
		}
		elseif (is_array($what))
		{
			return $this->_load($what);
		}
		else
		{
			return $this->load_enabled();
		}
	}

	/*
	 * Load one given type
	 */
	public function load_one($type)
	{
		return $this->_load(array($type));
	}

	/**
	 * Load all possible types
	 */
	public function load_all()
	{
		return $this->_load();
	}

	/**
	 * Load allowed types
	 */
	public function load_enabled()
	{
		return $this->_load(ee()->low_variables_settings->enabled_types);
	}

	/**
	 * Get array of Variable Types
	 *
	 * This method can be called directly throughout the package with $this->get_types()
	 *
	 * @param	mixed	$which		FALSE for complete list or array containing which types to get
	 * @return	array
	 */
	private function _load($which = FALSE)
	{
		// -------------------------------------
		// Reset
		// -------------------------------------

		$this->_types = array();

		// -------------------------------------
		// Load parent class
		// -------------------------------------

		if ( ! class_exists('Low_variables_type'))
		{
			include_once PATH_THIRD.'low_variables/types/type.low_variables.php';
		}

		// -------------------------------------
		//  Set variable types path
		// -------------------------------------

		$types_path = PATH_THIRD.'low_variables/types/';

		// -------------------------------------
		//  If path is not valid, bail
		// -------------------------------------

		if ( ! is_dir($types_path) ) return;

		// -------------------------------------
		//  Read dir, create instances
		// -------------------------------------

		$dir = opendir($types_path);

		while (($type = readdir($dir)) !== FALSE)
		{
			// skip these
			if ($type == '.' || $type == '..' || ! is_dir($types_path.$type)) continue;

			// if given, only get the given ones
			if (is_array($which) && ! in_array($type, $which)) continue;

			// determine file name
			$file = "vt.{$type}.php";
			$path = $types_path.$type.'/';

			// If the class doesn't exist, try to load it
			if ( ! class_exists($type) && file_exists($path.$file))
			{
				include($path.$file);
			}

			// Still not there? skip.
			if ( ! class_exists($type)) continue;

			$obj = new $type;
			$vars = get_class_vars($type);

			// Check requirements
			$reqs = isset($vars['info']['var_requires']) ? $vars['info']['var_requires'] : array();

			if ($this->_check_requirements($reqs))
			{
				$this->_types[$type] = array(
					'path'    => $path,
					'file'    => $file,
					'name'    => (isset($vars['info']['name']) ? $vars['info']['name'] : $type),
					'type'    => $type,
					'class'   => ucfirst($type),
					'version' => (isset($vars['info']['version']) ? $vars['info']['version'] : LOW_VAR_VERSION),
					'bridge'  => FALSE
				);
			}

		}

		// clean up
		closedir($dir);
		unset($dir);

		// -------------------------------------
		//  Get fieldtypes
		// -------------------------------------

		ee()->load->library('addons');
		$ftypes = ee()->addons->get_installed('fieldtypes');

		foreach ($ftypes as $package => $ftype)
		{
			// if given, only get the given ones
			if (is_array($which) && ! in_array($ftype['class'], $which) && ! in_array($package, $which)) continue;

			// Include EE Fieldtype class
			if ( ! class_exists('EE_Fieldtype'))
			{
				include_once (APPPATH.'fieldtypes/EE_Fieldtype.php');
			}

			if ( ! class_exists($ftype['class']) && file_exists($ftype['path'].$ftype['file']))
			{
				include_once ($ftype['path'].$ftype['file']);
			}

			// Check if fieldtype is compatible
			if ($this->_check_compatibility($ftype['class']))
			{
				$vars = get_class_vars($ftype['class']);

				// Check requirements
				$reqs = isset($vars['info']['var_requires']) ? $vars['info']['var_requires'] : array();

				if ($this->_check_requirements($reqs))
				{
					$this->_types[$ftype['name']] = array(
						'path'    => $ftype['path'],
						'file'    => $ftype['file'],
						'name'    => (isset($vars['info']['name']) ? $vars['info']['name'] : $ftype['name']),
						'type'    => $ftype['name'],
						'class'   => $ftype['class'],
						'version' => $ftype['version'],
						'bridge'  => TRUE
					);
				}
			}
		}

		// Sort types by alpha
		uasort($this->_types, create_function(
			'$a, $b',
			'return strcasecmp($a["name"], $b["name"]);'
		));

		return $this->_types;
	}

	// --------------------------------------------------------------------

	/**
	 * Check FT compatibility
	 *
	 * @access     private
	 * @param      str
	 * @return     bool
	 */
	private function _check_compatibility($class)
	{
		// This is the preferred method now
		if (method_exists($class, 'var_display_field')) return TRUE;

		// This is legacy
		if (method_exists($class, 'display_var_field')) return TRUE;

		// EE is nowhere near ready for this
		// $obj = new $class;
		// if ($obj->accepts_content_type('low_variables')) return TRUE;

		return FALSE;
	}

	/**
	 * Validate variable type requirements
	 *
	 * @access     private
	 * @param      array
	 * @return     bool
	 */
	private function _check_requirements($reqs)
	{
		// Reqs met?
		$met = TRUE;

		// Loop through reqs and check 'em
		foreach ($reqs as $package => $version_needed)
		{
			// Initiate installed version
			$installed_version = '0.0.0';

			// Check EE itself
			if ($package == 'ee')
			{
				$installed_version = APP_VER;
			}
			// Check packages
			else
			{
				// Get version for given package
				$types = array(
					'modules'    => 'module_version',
					'fieldtypes' => 'version',
					'extensions' => 'version'
				);

				// Loop through types and get the version number
				foreach ($types as $type => $key)
				{
					$rows = ee()->addons->get_installed($type);

					if (array_key_exists($package, $rows))
					{
						$installed_version = $rows[$package][$key];
						break;
					}
				}
			}

			// Compare the versions
			if (version_compare($installed_version, $version_needed, '<'))
			{
				$met = FALSE;
				break;
			}
		}

		return $met;
	}

	// --------------------------------------------------------------------

	/**
	 * Is given type a valid type?
	 *
	 * @param      string
	 * @return     bool
	 */
	public function is_type($str)
	{
		return array_key_exists($str, $this->_types);
	}

	// --------------------------------------------------------------------

	/**
	 * Get object for given variable
	 *
	 * @access     public
	 * @param      string
	 * @param      bool
	 * @return     mixed
	 */
	public function get($var)
	{
		// Get the type
		$type = $this->is_type($var['variable_type'])
			? $var['variable_type']
			: static::DEFAULT_TYPE;

		// Get the array
		$type = $this->_types[$type];

		// Create object
		$type = ($type['bridge'])
			? new Low_variables_type($type)
			: new $type['class'];

		$type->init($var);

		return $type;
	}
}