<?php

if ( ! class_exists('Freeform_Model'))
{
	require_once 'freeform_model.php';
}

class Freeform_template_model extends Freeform_Model
{
	//intentional because we will need to set every incoming table
	public	$_table	= 'freeform_composer_templates';
	public	$id		= 'template_id';

	public $default_prefs = array(
		'template_id'	=> array(
			'type'		=> 'hidden',
			'default'	=> '',
		),
		'site_id'	=> array(
			'type'		=> 'hidden',
			'default'	=> '',
		),
		'template_label'	=> array(
			'type'		=> 'text',
			'default'	=> '',
			'validate'	=> 'required',
			'required'	=> true
		),
		'template_name'	=> array(
			'type'		=> 'text',
			'default'	=> '',
			'validate'	=> 'required|alphaDash',
			'required'	=> true
		),
		'template_description'	=> array(
			'type'		=> 'textarea',
			'default'	=> '',
			'validate'	=> '',
		),
		'enable_template'	=> array(
			'type'		=> 'yes_no',
			'default'	=> 'y',
			'validate'	=> 'enum[y,n]',
			'required'	=> true
		),
		'template_params'	=> array(
			'type'		=> 'html',
			'default'	=> '',
			'content'	=> ''
		),
		'template_data'	=> array(
			'type'		=> 'textarea',
			'default'	=> '',
			'validate'	=> 'required',
			'required'	=> true,
			'attrs'		=> ' style="min-height:600px"',
			'wide'		=> true
		),
	);

	public function get_default_prefs()
	{
		$prefs = $this->default_prefs;

		$prefs['site_id']['default'] = ee()->config->item('site_id');

		//converting to spaces here so its easier to
		//edit for end users
		$prefs['template_data']['default'] = str_replace(
			"\t",
			"    ",
			$this->view(
				'default_composer_template',
				array('wrappers' => false)
			)
		);

		//html for params
		return $prefs;
	}
}
//END Freeform_composer_model
