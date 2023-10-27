<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author		Rein de Vries <support@reinos.nl>
 * @link		http://ee.reinos.nl
 * @copyright 	Copyright (c) 2017 Reinos.nl Internet Media
 * @license     http://ee.reinos.nl/commercial-license
 *
 * Copyright (c) 2017. Reinos.nl Internet Media
 * All rights reserved.
 *
 * This source is commercial software. Use of this software requires a
 * site license for each domain it is used on. Use of this software or any
 * of its source code without express written permission in the form of
 * a purchased commercial or other license is prohibited.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE.
 *
 * As part of the license agreement for this software, all modifications
 * to this source must be submitted to the original author for review and
 * possible inclusion in future releases. No compensation will be provided
 * for patches, although where possible we will attribute each contribution
 * in file revision notes. Submitting such modifications constitutes
 * assignment of copyright to the original author (Rein de Vries and
 * Reinos.nl Internet Media) for such modifications. If you do not wish to assign
 * copyright to the original author, your license to  use and modify this
 * source is null and void. Use of this software constitutes your agreement
 * to this clause.
 */

include(PATH_THIRD.'gmaps/config.php');
 
class gmaps_ext 
{	
	
	public $name			= GMAPS_NAME;
	public $description		= GMAPS_DESCRIPTION;
	public $version			= GMAPS_VERSION;
	public $settings 		= array();
	public $docs_url		= GMAPS_DOCS;
	public $settings_exist	= 'n';
	public $required_by 	= array('Gmaps Module');
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{		
		//require the settings
		require PATH_THIRD.'gmaps/settings.php';
	}

    // ----------------------------------------------------------------------

    /**
     * Handle the api and JS code after template rendering
     *
     * @param $final_template
     * @param $is_partial
     * @param $site_id
     * @return mixed
     */
    public function template_post_parse($final_template, $is_partial, $site_id)
    {
        //check if there is any map marker so we can proceed
        if(strpos($final_template, 'gmaps:tags_found') != false)
        {
            //load the api
            ee()->load->library('api/gmaps_api');

            //parse this string
            $final_template = ee()->gmaps_api->parse($final_template);

            //also check if there is any output tag, so we need to paste the js code there?
            if(strpos($final_template, '{gmaps:output_js}') != false)
            {
                $js = ee()->gmaps->minify_html_output(gmaps_helper::get_ee_cache('init_js')).ee()->gmaps->minify_html_output(gmaps_helper::get_ee_cache('output_js').ee()->gmaps->minify_html_output(gmaps_helper::get_ee_cache('output_js_low_prio')));
                $final_template = str_replace('{gmaps:output_js}', $js, $final_template);
            }

        }

        return $final_template;
    }

	// ----------------------------------------------------------------------

	/**
	 * Method for ee_debug_toolbar_add_panel hook
	 *
	 * @param 	array	Array of debug panels
	 * @param 	arrat	A collection of toolbar settings and values
	 * @return 	array	The amended array of debug panels
	 */
	public function ee_debug_toolbar_add_panel($panels, $view)
	{
		// do nothing if not a page
		if(REQ != 'PAGE') return $panels;

		//load lib
		ee()->load->library('gmaps_library', null, 'gmaps');

		// play nice with others
		$panels = (ee()->extensions->last_call != '' ? ee()->extensions->last_call : $panels);
	
		$panels['gmaps'] = new Eedt_panel_model();
		$panels['gmaps']->set_name('gmaps');
		$panels['gmaps']->set_button_label("Gmaps");
		$panels['gmaps']->set_panel_contents(ee()->load->view('debug_panel', array('logs' => gmaps_helper::get_log()), TRUE));

		if(gmaps_helper::log_has_error())
		{
			$panels['gmaps']->set_panel_css_class('flash');
		}

		return $panels;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		//the module will install the extension if needed
		return true;
	}	
	
	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		//the module will disable the extension if needed
		return true;
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		//the module will update the extension if needed
		return true;
	}	
	
	// ----------------------------------------------------------------------
}
