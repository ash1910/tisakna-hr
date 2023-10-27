<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Drivers.
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2017 Causing Effect
 * @license		http://docs.causingeffect.com/expressionengine/ce-cache/license-agreement.html
 * @link		https://www.causingeffect.com
 */

class Ce_cache_drivers
{
	private static $valid_drivers = array( 'file', 'db', 'static', 'apc', 'memcache', 'memcached', 'sqlite', 'redis', 'dummy' );

	/**
	 * The valid drivers.
	 *
	 * @return array
	 */
	public static function get_valid_drivers()
	{
		return self::$valid_drivers;
	}

	/**
	 * Get the user specified (or default) driver(s).
	 *
	 * @param bool $allow_static Whether or not to include the static driver.
	 * @param bool $allow_override Whether or not to return the dummy driver if it will be used.
	 * @return array|mixed|string
	 */
	public static function get_active_drivers($allow_static = false, $allow_override = true)
	{
		//include CE Cache Utilities
		self::include_library('Ce_cache_utils');

		//get the user-specified drivers
		$drivers = Ce_cache_utils::determine_setting( 'drivers', '' );

		//make sure the static driver is not included
		$drivers = str_replace( 'static', '', $drivers );

		if ( ! empty( $drivers ) ) //we have driver settings
		{
			if ( ! is_array( $drivers ) )
			{
				$drivers = explode( '|', $drivers );
			}
		}
		else //no drivers specified, see if we have some legacy settings
		{
			$drivers = array();
		}

		if ( count( $drivers ) == 0 ) //still no drivers specified, default to 'file'
		{
			$drivers[] = 'file';
		}

		//see if there is a reason to prevent caching the current page (like the current page is a better workflow draft, or ce cache is off)
		if ( $allow_override && self::should_use_dummy_driver() )
		{
			//set the drivers to dummy
			$drivers = array( 'dummy' );
		}

		if ( $allow_static )
		{
			//add in "static" driver, if it is enabled
			if (Ce_cache_utils::ee_string_to_bool(Ce_cache_utils::determine_setting('static_enabled', false, '', true, false)))
			{
				$drivers[] = 'static';
			}
		}

		//remove any duplicates
		$drivers = array_unique($drivers);

		return $drivers;
	}

	/**
	 * Returns an array of drivers that are both valid and supported, but not active.
	 *
	 * @param bool $allow_override Whether or not to return the dummy driver if it will be used.
	 * @return array
	 */
	public static function get_supported_drivers()
	{
		//get the valid drivers
		$valid_drivers = self::get_valid_drivers();

		//get the active drivers
		$active_drivers = self::get_active_drivers(true, false);

		//the supported drivers
		$supported_drivers = array();

		//loop through the valid drivers
		foreach ($valid_drivers as $valid_driver)
		{
			//if the driver is supported, but it is currently not active
			if (!in_array($valid_driver, $active_drivers))
			{
				$supported_drivers[] = $valid_driver;
			}
		}

		//sort the supported drivers
		sort($supported_drivers, SORT_STRING);

		return $supported_drivers;
	}

	/**
	 * Get the classes for the active drivers.
	 *
	 * @param bool $allow_static Whether or not to include the static driver.
	 * @param bool $allow_override Whether or not to return the dummy driver if it will be used.
	 * @return array Active driver classes.
	 */
	public static function get_active_driver_classes($allow_static = false, $allow_override = false)
	{
		//active drivers
		$active_drivers = self::get_active_drivers($allow_static, $allow_override);

		//get the active driver classes
		return self::factory($active_drivers);
	}

	/**
	 * Returns all classes for all valid drivers.
	 */
	public static function get_all_driver_classes()
	{
		return self::factory(self::$valid_drivers);
	}

	/**
	 * Get the names of the active drivers.
	 *
	 * @return array Active driver names.
	 */
	public static function get_active_driver_names($allow_static = false)
	{
		$classes = self::get_active_driver_classes($allow_static);

		$active_classes = array();
		foreach ($classes as $class)
		{
			$active_classes[] = $class->name();
		}

		//cleanup memory
		unset($classes, $active_drivers);

		return $active_classes;
	}

	/**
	 * Get the classes for the supported (valid but not active) drivers.
	 *
	 * @return array Supported driver classes.
	 */
	public static function get_supported_driver_classes()
	{
		//supported drivers
		$supported_drivers = self::get_supported_drivers();

		//get the supported driver classes
		return self::factory($supported_drivers);
	}

	/**
	 * Get the names of the supported (valid but not active) drivers.
	 *
	 * @return array Supported driver names.
	 */
	public static function get_supported_driver_names()
	{
		$classes = self::get_supported_driver_classes();

		$supported_classes = array();
		foreach ($classes as $class)
		{
			$supported_classes[] = $class->name();
		}

		//cleanup memory
		unset($classes, $supported_drivers);

		return $supported_classes;
	}

	/**
	 * Whether or not the dummy driver should be used.
	 *
	 * @return bool
	 */
	public static function should_use_dummy_driver()
	{
		//include CE Cache Utilities
		self::include_library('Ce_cache_utils');

		//is the user logged in?
		$logged_in = (ee()->session->userdata['member_id'] != 0);

		//check to see if the dummy driver should be used.
		if (
			//better workflow draft
			( isset( ee()->session->cache['ep_better_workflow']['is_preview'] ) && ee()->session->cache['ep_better_workflow']['is_preview'] === true )
			//another bwf check (from Matt Green)
			|| ( isset( $_GET['bwf_dp'] ) && $_GET['bwf_dp'] == 't' )
			//publisher check (from Fusionary)
			|| ( isset( $_GET['publisher_status'] ) && $_GET['publisher_status'] == 'draft' )
			//cache is off
			|| Ce_cache_utils::ee_string_to_bool( Ce_cache_utils::determine_setting( 'off', 'no' ) )
			//logged in only, but not logged in
			|| (Ce_cache_utils::ee_string_to_bool( Ce_cache_utils::determine_setting( 'logged_in_only', 'no', 'fragment' ) ) && ! $logged_in )
			//logged out only, but is logged in
			|| (Ce_cache_utils::ee_string_to_bool( Ce_cache_utils::determine_setting( 'logged_out_only', 'no', 'fragment' ) ) && $logged_in )
			//a POST page and ignore_post_requests is set to "yes"
			|| ( ! empty( $_POST ) && Ce_cache_utils::ee_string_to_bool( Ce_cache_utils::determine_setting( 'ignore_post_requests', 'yes' ) ) && $_POST != array( 'entry_id' => '' ) )
		)
		{
			return true;
		}

		return false;
	}



	/**
	 * Include the library by name. Done this way instead of
	 * ee()->load->library('example') so that the class is not
	 * instantiated, but rather used to call static methods.
	 *
	 * @param $name
	 */
	private static function include_library($name)
	{
		//load the class if needed
		if (!class_exists($name))
		{
			include PATH_THIRD . 'ce_cache/libraries/'.$name.'.php';
		}
	}

	/**
	 * @static
	 * @param array $drivers An array of drivers for the factory to return.
	 * @param bool $auto_add_dummy Should the 'dummy' driver automatically be included if not specified?
	 * @return array
	 */
	public static function factory( $drivers = array(), $auto_add_dummy = false )
	{
		if ( empty( $drivers ) )
		{
			$drivers = array();
		}

		//was a single driver passed in instead of a string?
		if ( is_string( $drivers ) )
		{
			//turn it into an array
			$drivers = array( $drivers );
		}

		if ( is_array( $drivers ) )
		{
			//make sure the drivers are valid
			$temps = $drivers;
			$drivers = array();

			foreach ( $temps as  $temp )
			{
				if ( in_array( strtolower( $temp ), self::$valid_drivers ) )
				{
					$drivers[] = strtolower( $temp );
				}
			}
			unset( $temps );
		}
		else
		{
			$drivers = arrays();
		}

		if ( $auto_add_dummy )
		{
			//make sure the dummy key exists
			if ( false !== $key = array_search( 'dummy', $drivers ) ) //dummy driver present
			{
				//just grab the array up to the dummy driver, as there is no need to include any additional drivers
				$drivers = array_splice( $drivers, 0, $key + 1 );
			}
			else
			{
				//add the dummy driver
				$drivers[] = 'dummy';
			}
		}

		$final = array();

		//include the driver base class
		if ( ! class_exists( 'Ce_cache_driver' ) )
		{
			include PATH_THIRD . 'ce_cache/libraries/drivers/Ce_cache_driver.php';
		}

		//load the drivers
		foreach ( $drivers as $driver )
		{
			$class = 'Ce_cache_' . $driver;

			//include the drivers if needed
			if ( ! class_exists( $class ) )
			{
				$path = PATH_THIRD . "ce_cache/libraries/drivers/{$class}.php";

				if ( file_exists( $path ) ) //we found the file
				{
					//include the driver
					include $path;
				}
				else //we could not find the driver
				{
					//skip on to the next driver
					continue;
				}
			}

			//instantiate the class
			$temp = new $class;

			//check if the class is supported
			if ( $temp->is_supported() ) //it is supported
			{
				//include the class in the final drivers array
				$final[] = $temp;
			}
		}

		//return the final array of drivers
		return $final;
	}

	/**
	 * Checks if a driver is supported.
	 *
	 * @param $driver
	 * @return bool
	 */
	public static function is_supported( $driver )
	{
		$driver = strtolower( $driver );

		//make sure the driver is valid
		if ( ! in_array( $driver, self::$valid_drivers ) )
		{
			return false;
		}

		//include the driver base class
		if ( ! class_exists( 'Ce_cache_driver' ) )
		{
			include PATH_THIRD . 'ce_cache/libraries/drivers/Ce_cache_driver.php';
		}

		//load the driver
		$class = 'Ce_cache_' . $driver;

		//include the drivers if needed
		if ( ! class_exists( $class ) )
		{
			$path = PATH_THIRD . "ce_cache/libraries/drivers/{$class}.php";

			if ( file_exists( $path ) ) //we found the file
			{
				//include the driver
				include $path;
			}
			else //we could not find the driver
			{
				return false;
			}
		}

		//instantiate the class
		$driver = new $class;

		//see if the driver is supported
		return $driver->is_supported();
	}
}