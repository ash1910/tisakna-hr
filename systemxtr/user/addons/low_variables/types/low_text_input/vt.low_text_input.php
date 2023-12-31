<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Low Text Input variable type
 *
 * @package        low_variables
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-variables
 * @copyright      Copyright (c) 2009-2016, Low
 */
class Low_text_input extends Low_variables_type {

	public $info = array(
		'name' => 'Text Input'
	);

	public $default_settings = array(
		'maxlength'      => '',
		'size'           => 'medium',
		'pattern'        => '',
		'text_direction' => 'ltr'
	);

	// --------------------------------------------------------------------

	/**
	 * Display settings sub-form for this variable type
	 *
	 * @param	mixed	$var_id			The id of the variable: 'new' or numeric
	 * @param	array	$var_settings	The settings of the variable
	 * @return	array
	 */
	function display_settings()
	{
		// -------------------------------------
		//  Init return value
		// -------------------------------------

		$r = array();

		// -------------------------------------
		//  Build rows for values
		// -------------------------------------

		$r[] = array(
			'title' => 'variable_maxlength',
			'fields' => array(
				$this->setting_name('maxlength') => array(
					'type' => 'short-text',
					'value' => $this->settings('maxlength'),
					'label' => ''
				)
			)
		);

		$r[] = array(
			'title' => 'variable_size',
			'fields' => array(
				$this->setting_name('size') => array(
					'type' => 'select',
					'value' => $this->settings('size'),
					'choices' => array(
						'large' => lang('large'),
						'medium' => lang('medium'),
						'small' => lang('small'),
						'x-small' => lang('x-small')
					)
				)
			)
		);

		$r[] = array(
			'title' => 'variable_pattern',
			'desc' => 'variable_pattern_help',
			'fields' => array(
				$this->setting_name('pattern') => array(
					'type' => 'text',
					'value' => $this->settings('pattern')
				)
			)
		);

		// -------------------------------------
		//  Build settings text_direction
		// -------------------------------------

		$r[] = LVUI::setting('dir', $this->setting_name('text_direction'), $this->settings('text_direction'));

		// -------------------------------------
		//  Return output
		// -------------------------------------

		return $this->settings_form($r);
	}

	// --------------------------------------------------------------------

	/**
	 * Display input field for regular user
	 *
	 * @param	string	$var_data		The value of the variable
	 * @return	string
	 */
	public function display_field($var_data)
	{
		// -------------------------------------
		//  Check current value from settings
		// -------------------------------------

		$attrs = array(
			'dir' => $this->settings('text_direction'),
			'maxlength' => $this->settings('maxlength') ?: FALSE,
			'class' => 'low-'.$this->settings('size')
		);

		$props = array(
			'type'  => $this->settings('size') == 'x-small' ? 'short-text' : 'text',
			'value' => $var_data,
			'label' => '',
			'attrs' => $this->attr_string($attrs)
		);

		// -------------------------------------
		//  Return input field
		// -------------------------------------

		return array($this->input_name() => $props);
	}

	// --------------------------------------------------------------------

	/**
	 * Prep variable data for saving
	 *
	 * @param	mixed	$var_data		The value of the variable, array or string
	 * @return	string
	 */
	public function save($var_data)
	{
		// -------------------------------------
		//  Check if pattern is defined
		// -------------------------------------

		if (($pattern = $this->settings('pattern')) && ! preg_match($pattern, $var_data, $match))
		{
			$this->error_msg = 'invalid_value';
			$var_data = FALSE;
		}

		return $var_data;
	}

	// --------------------------------------------------------------------

	/**
	 * Array to attribute string
	 *
	 * @param      string    String to decode
	 * @return     array
	 */
	private function attr_string($array)
	{
		$out = array();

		foreach ($array as $key => $val)
		{
			if (is_array($val) || $val === FALSE) continue;

			$out[] = sprintf('%s="%s"', htmlspecialchars($key, ENT_QUOTES), htmlspecialchars($val, ENT_QUOTES));
		}

		return implode(' ', $out);
	}

}