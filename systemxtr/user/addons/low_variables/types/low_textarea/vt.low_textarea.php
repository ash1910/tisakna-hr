<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Low Textarea variable type
 *
 * @package        low_variables
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-variables
 * @copyright      Copyright (c) 2009-2016, Low
 */
class Low_textarea extends Low_variables_type {

	public $info = array(
		'name' => 'Textarea'
	);

	public $default_settings = array(
		'text_direction' => 'ltr',
		'code_format'    => FALSE,
		'wide'           => 'n',
	);

	// --------------------------------------------------------------------

	/**
	 * Display settings sub-form for this variable type
	 */
	public function display_settings()
	{
		return $this->settings_form(array(
			LVUI::setting('dir', $this->setting_name('text_direction'), $this->settings('text_direction')),
			array(
				'title' => 'enable_code_format',
				'fields' => array(
					$this->setting_name('code_format') => array(
						'type' => 'yes_no',
						'value' => $this->settings('code_format') ?: 'n'
					)
				)
			),
			LVUI::setting('wide', $this->setting_name('wide'), $this->settings('wide'))
		));
	}

	// --------------------------------------------------------------------

	/**
	 * Display input field for regular user
	 */
	public function display_field($var_data)
	{
		// -------------------------------------
		//  Set class name for textarea
		// -------------------------------------

		$attrs = 'dir="'.$this->settings('text_direction', 'ltr').'"';

		if ($this->settings('code_format') == 'y')
		{
			$attrs .= ' class="low_code_format"';
		}

		// -------------------------------------
		//  Return input field(s)
		// -------------------------------------

		return array($this->input_name() => array(
			'type'  => 'textarea',
			'value' => $var_data,
			'attrs' => $attrs
		));
	}

	/**
	 * Are we displaying a wide field?
	 */
	public function wide()
	{
		return ($this->settings('wide') == 'y');
	}

	// --------------------------------------------------------------------

	/**
	 * Display output, possible formatting, extra processing
	 */
	public function replace_tag($tagdata)
	{
		$var_data = $this->data();

		// -------------------------------------
		//  Check for extra vars to be pre-parsed
		// -------------------------------------

		$prefix = 'preparse:';
		$offset = strlen($prefix);
		$extra  = array();

		foreach (ee()->TMPL->tagparams as $key => $val)
		{
			if (substr($key, 0, $offset) == $prefix)
			{
				$extra[substr($key, $offset)] = $val;
			}
		}

		if ($extra)
		{
			ee()->TMPL->log_item('Low Variables: Low_textarea preparse keys: '.implode('|', array_keys($extra)));
			ee()->TMPL->log_item('Low Variables: Low_textarea preparse values: '.implode('|', $extra));
			$var_data = ee()->TMPL->parse_variables_row($var_data, $extra);
		}

		// -------------------------------------
		//  Is there a formatting parameter?
		//  If so, apply the given format
		// -------------------------------------

		if ($format = ee()->TMPL->fetch_param('formatting'))
		{
			ee()->TMPL->log_item("Low Variables: Low_textarea applying {$format} formatting");

			ee()->load->library('typography');

			// Set options
			$options = array('text_format' => $format);

			// Allow for html_format
			if ($html = ee()->TMPL->fetch_param('html'))
			{
				$options['html_format'] = $html;
			}

			// Run the Typo method
			$var_data = ee()->typography->parse_type($var_data, $options);
		}

		// -------------------------------------
		// return (formatted) data
		// -------------------------------------

		return (empty($tagdata)
			? $var_data
			: str_replace(LD.$this->name().RD, $var_data, $tagdata));
	}

	// --------------------------------------------------------------------

}
// End of vt.low_textarea.php