<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Utilities class
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2017 Causing Effect
 * @license		http://docs.causingeffect.com/expressionengine/ce-cache/license-agreement.html
 * @link		https://www.causingeffect.com
 */
class Ce_cache_utils
{
	/**
	 * Return the current site's label.
	 *
	 * @static
	 * @return string
	 */
	public static function get_site_label()
	{
		$label = trim( ee()->config->item('site_label') );
		$label = self::sanitize_filename($label);

		if ( empty( $label ) )
		{
			$label = 'default_site';
		}

		return $label;
	}

	/**
	 * Returns the hash generated from the site label.
	 *
	 * @return string
	 */
	public static function get_site_hash()
	{
		return substr( md5(self::get_site_label()), 0, 6 );
	}

	/**
	 * Returns the current sites cache prefix.
	 */
	public static function get_site_prefix()
	{
		return 'ce_cache/' . self::get_site_hash() . '/';
	}

	/**
	 * Gets the secret config item.
	 *
	 * @return string
	 */
	public static function get_secret()
	{
		//prep the secret
		$secret = ee()->config->item('ce_cache_secret');
		if (! $secret)
		{
			$secret = '';
		}
		return substr( md5($secret), 0, 10 );
	}

	/**
	 * Removes double slashes, except when they are preceded by ':', so that 'http://', etc are preserved.
	 *
	 * @param string $str The string from which to remove the double slashes.
	 * @return string The string with double slashes removed.
	 */
	public static function remove_duplicate_slashes( $str )
	{
		return preg_replace( '#(?<!:)//+#', '/', $str );
	}

	/**
	 * Determines the given setting by checking for the param, and then for the global var, and then for the config item.
	 *
	 * @param string $name The name of the parameter. The string 'ce_cache_' will automatically be prepended for the global and config setting checks.
	 * @param string $default The default setting value
	 * @param string $long_prefix
	 * @param bool $check_config
	 * @param bool $check_param
	 * @return string The setting value if found, or the default setting if not found.
	 */
	public static function determine_setting( $name, $default = '', $long_prefix = '', $check_config = true, $check_param = true )
	{
		if ( ! empty( $long_prefix ) )
		{
			$long_prefix = $long_prefix . '_';
		}

		$long_name = 'ce_cache_' . $long_prefix . $name;
		if ( $check_param && isset( ee()->TMPL ) && ee()->TMPL->fetch_param( $name ) !== false ) //param
		{
			$default = ee()->TMPL->fetch_param( $name );
		}
		/*else if ( $check_global_vars && isset( ee()->config->_global_vars[ $long_name ] ) && ee()->config->_global_vars[ $long_name ] !== false ) //first check global array
		{
			$default = ee()->config->_global_vars[ $long_name ];
		}*/
		else if ( $check_config && ee()->config->item( $long_name ) !== false ) //then check config
		{
			$default = ee()->config->item( $long_name );
		}
		else if ( $check_config )
		{
			$ce_cache_config = ee()->config->item( 'ce_cache' );

			if ( ! empty( $ce_cache_config ) && is_array( $ce_cache_config ) && isset( $ce_cache_config[ ee()->config->item('site_id') ][ $name ] ) )
			{
				$default = $ce_cache_config[ ee()->config->item('site_id') ][ $name ];
			}
		}

		return $default;
	}

	/**
	 * Little helper method to convert parameters to a boolean value.
	 *
	 * @param $string
	 * @return bool
	 */
	public static function ee_string_to_bool( $string )
	{
		return ( $string == 'y' || $string == 'yes' || $string == 'on' || $string === true );
	}

	/**
	 * Calls the sanitize filename method depending on the EE version.
	 *
	 * @param $input string The filename to sanitize.
	 * @param $relative_path string Will remove `./` and `/` from the filename if false.
	 * @return mixed
	 */
	public static function sanitize_filename( $input, $relative_path = false )
	{
		//get the label
		if (version_compare(APP_VER, '2.5.0', '<'))
		{
			ee()->load->helper('security');
			$input = sanitize_filename($input, $relative_path);
		}
		else
		{
			$input = ee()->security->sanitize_filename($input, $relative_path);
		}

		return $input;
	}

	/**
	 * Shortcut to cut down on the boilerplate code for creating module URLs.
	 *
	 * @param string $path
	 * @return mixed
	 */
	public static function cp_url($path = '', $get_params = array())
	{
		$path = 'addons/settings/ce_cache/' . ltrim($path, '/');
		$url = ee('CP/URL', $path, $get_params);

		return $url->compile();
	}

	/**
	 * Creates an alert and redirects to the specified path.
	 *
	 * @param string $langLine
	 * @param string $type
	 * @param string $path
	 * @return mixed
	 */
	public static function redirect_alert( $body = '', $type = 'success', $path = '/')
	{
		switch ($type)
		{
			case 'warning':
				$alert = ee('CP/Alert')
					->makeStandard('ce_cache_'.$type)
					->asWarning()
					->withTitle(lang('ce_cache_alert_warning'));
				break;
			case 'issue':
			case 'error':
			case 'fail':
				$alert = ee('CP/Alert')
					->makeStandard('ce_cache_'.$type)
					->asIssue()
					->withTitle(lang('ce_cache_alert_issue'));
				break;
			default:
				$alert = ee('CP/Alert')
					->makeStandard('ce_cache_success')
					->asSuccess()
					->withTitle(lang('ce_cache_alert_success'));
				break;
		}
		$alert
			->addToBody($body)
			->defer();

		return ee()->functions->redirect(self::cp_url($path));
	}
}