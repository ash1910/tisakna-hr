<?php

class Checkbox_freeform_ft extends Freeform_base_ft
{
	public 	$info 	= array(
		'name'			=> 'Checkbox',
		'version'		=> '',
		'description'	=> 'A field with a single checkbox with \"y\" or \"n\" options.'
	);


	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	null
	 */

	public function __construct()
	{
		parent::__construct();

		$this->info['name'] 		= lang('default_checkbox_name');
		$this->info['description'] 	= lang('default_checkbox_desc');
	}
	//END __construct()


	// --------------------------------------------------------------------

	/**
	 * Display Field
	 *
	 * @access	public
	 * @param	string 	saved data input
	 * @param  	array 	input params from tag
	 * @param 	array 	attribute params from tag
	 * @return	string 	display output
	 */

	public function display_field ($data = '', $params = array(), $attr = array())
	{
		return form_hidden($this->field_name, 'n') .
			form_checkbox(array_merge(array(
				'name'			=> $this->field_name,
				'id'			=> 'freeform_' . $this->field_name,
				'value'			=> 'y',
				'checked'		=> ($data === 'y')
			), $attr)
		);
	}
	//END display_field


	// --------------------------------------------------------------------

	/**
	 * Display Field Settings
	 *
	 * @access	public
	 * @param	array
	 * @return	string
	 */

	public function display_settings($data = array())
	{
		return '';
	}
	//END display_settings
}
//END class Checkbox_freeform_ft
