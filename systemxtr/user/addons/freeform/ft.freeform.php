<?php

class Freeform_ft extends EE_Fieldtype
{
	public 	$info 			= array(
		'name'		=> 'Freeform',
		'version'	=> ''
	);

	public $field_name		= 'freeform_default';
	public $field_id		= 'freeform_default';

	public $has_array_data	= FALSE;

	protected $fob;

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 */

	public function __construct()
	{
		parent::__construct();

		$this->info = require 'addon.setup.php';

		ee()->lang->loadfile('freeform');

		$this->field_id		= isset($this->settings['field_id']) ?
								$this->settings['field_id'] :
								$this->field_id;

		$this->field_name	= isset($this->settings['field_name']) ?
								$this->settings['field_name'] :
								$this->field_name;
	}
	// END constructor


	// --------------------------------------------------------------------

	/**
	 * displays field for publish/saef
	 *
	 * @access	public
	 * @param	string	$data	any incoming data from the channel entry
	 * @return	string	html output view
	 */

	public function display_field($data)
	{
		$data = $this->prep_data($data);

		ee()->load->model('freeform_form_model');

		if ( ! $this->fob()->model('preference')->show_all_sites())
		{
			ee()->freeform_form_model->where(
				'site_id',
				ee()->config->item('site_id')
			);
		}

		$c_forms =	ee()->freeform_form_model
						->key('form_id', 'form_label')
						->where('composer_id !=', 0)
						->get();

		$return = '<p style="margin-top:0px;margin-bottom:0px;">' . lang('no_available_composer_forms', $this->field_name . '[form]') . '</p>';

		if ($c_forms !== FALSE)
		{
			$output_array = array(0 => '--');

			foreach ($c_forms as $form_id => $form_label)
			{
				$output_array[$form_id] = $form_label;
			}

			$return = '<p style="margin-top:0px;margin-bottom:5px;">';
			$return .= lang('choose_composer_form', $this->field_name . '[form]');
			$return .= '</p><p style="margin-top:0px;margin-bottom:5px;">' . form_dropdown(
				$this->field_name . '[form]',
				$output_array,
				isset($data['form']) ? $data['form'] : ''
			);
			$return .= '</p>';

			$return .= '<p style="margin-top:0px;margin-bottom:5px;">';
			$return .= lang('return_page_field', $this->field_name . '[return]');
			$return .= '</p><p style="margin-top:0px;margin-bottom:0px;">' . form_input(array(
				'name'	=> $this->field_name . '[return]',
				'value'	=> isset($data['return']) ? $data['return'] : '',
				'placeholder'	=> 'form/thank-you'
			));
			$return .= '</p>';
		}

		return $return;
	}
	//END display_field


	// --------------------------------------------------------------------

	/**
	 * Save Field
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */

	function save ($data)
	{
		$data	= $this->prep_data($data);
		$return	= '';

		//nothing set?
		if (isset($data['form']) AND
			$this->fob()->is_positive_intlike($data['form']))
		{
			$return = $data['form'] . '|' . (isset($data['return']) ? $data['return'] : '');
		}

		return $return;
	}
	//END save


	// --------------------------------------------------------------------

	/**
	 * Save Field
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */

	function validate ($data)
	{
		$data = $this->prep_data($data);

		//nothing set?
		if ( ! $this->fob()->is_positive_intlike($data['form']))
		{
			return TRUE;
		}

		ee()->load->model('freeform_form_model');

		if ( ! $this->fob()->model('preference')->show_all_sites())
		{
			ee()->freeform_form_model->where(
				'site_id',
				ee()->config->item('site_id')
			);
		}

		$c_forms =	ee()->freeform_form_model
						->where('form_id', $data['form'])
						->where('composer_id !=', 0)
						->count();

		return ($c_forms > 0);
	}
	//END validate


	// --------------------------------------------------------------------

	/**
	 * replace tag pair data
	 *
	 * @access	public
	 * @param	array 	data for preprocessing
	 * @param	array 	tag params
	 * @param	string 	tagdata
	 * @return	string	processed tag data
	 */

	public function replace_tag ($data, $params = array(), $tagdata = FALSE)
	{
		$data = $this->prep_data($data);

		//nothing set?
		if ( ! isset($data['form']) OR
			 ! $this->fob()->is_positive_intlike($data['form']))
		{
			return '';
		}

		//save old
		$old_tagdata 			= ee()->TMPL->tagdata;
		$old_params				= isset(ee()->TMPL->tagparams) ?
									ee()->TMPL->tagparams :
									array();

		//replace to trick tag :D
		ee()->TMPL->tagdata 	= '';

		if (empty($params))
		{
			$params = array();
		}

		if ( ! is_array($params))
		{
			$params = (array) $params;
		}

		ee()->TMPL->tagparams 	= array_merge(
			array(
				'form_id' 		=> $data['form'],
				'return'		=> $data['return']
			),
			$params
		);

		$return	= $this->fob()->composer();

		//reset
		ee()->TMPL->tagdata		= $old_tagdata;
		ee()->TMPL->tagparams	= $old_params;

		//for some reason CP isn't doing advanced conditionals *shrug*
		if (REQ == 'CP' && stristr($return, LD . 'if'))
		{
			$return = ee()->TMPL->advanced_conditionals($return);
		}

		return $return;
	}
	//END replace_tag

	// --------------------------------------------------------------------

	/**
	 * Form id for replace tag
	 *
	 * @access	public
	 * @param	mixed	$data		incoming data
	 * @param	array	$params		params on tag
	 * @param	mixed	$tagdata	tagdata, boolean false if missing
	 * @return	string				form id or blank
	 */

	public function replace_form_id ($data, $params = array(), $tagdata = FALSE)
	{
		$data = $this->prep_data($data);

		return (
			isset($data['form']) AND
			$this->fob()->is_positive_intlike($data['form'])
		) ? $data['form'] : '';
	}
	//END replace_form_id


	// --------------------------------------------------------------------

	/**
	 * Prep Data for consumption
	 *
	 * @access	protected
	 * @param	string	$data	incoming string data
	 * @return	array			fixed data array
	 */

	protected function prep_data ($data = '')
	{
		if ( ! is_array($data))
		{
			$data = $this->fob()->lib('Utils')->pipe_split((string) $data);
		}

		if ( ! isset($data['form']))
		{
			if (isset($data[0]) AND
				$this->fob()->is_positive_intlike($data[0]))
			{
				$data['form'] = $data[0];
			}
			else
			{
				$data['form'] = '';
			}
		}

		if ( ! isset($data['return']))
		{
			if (isset($data[1]) AND is_string($data[1]))
			{
				$data['return'] = $data[1];
			}
			else
			{
				$data['return'] = '';
			}
		}

		return $data;
	}
	//END prep_data


	// --------------------------------------------------------------------

	/**
	 * Freeform module Object.
	 *
	 * @access protected
	 * @return object cached instance of the freeform module object
	 */

	protected function fob()
	{
		if (is_null($this->fob))
		{
			if ( ! class_exists('Freeform'))
			{
				require_once rtrim(dirname(__FILE__), '/') . '/mod.freeform.php';
			}

			$this->fob = new Freeform();
		}

		return $this->fob;
	}
	//END fob


	// --------------------------------------------------------------------

	/**
	 * Update
	 *
	 * This is required for EE to update fieldtype version numbers.
	 * (This might be fixed in an upcoming update?)
	 * https://support.ellislab.com/bugs/detail/21524/fieldtypes-with-accompanying-modules-never-update-version-number-without-th
	 *
	 * @access	public
	 * @param	string $version		current version number coming from EE
	 * @return	boolean				should EE update the version number.
	 */

	public function update($version)
	{
		return ($version != $this->info['version']);
	}
	//END update
}
//END Freeform_ft
