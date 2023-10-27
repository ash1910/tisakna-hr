<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel FIles Module Control Panel Class
 *
 * @package			DevDemon_ChannelFiles
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_files/
 * @see				http://expressionengine.com/user_guide/development/module_tutorial.html#control_panel_file
 */
class Channel_files_mcp
{
	/**
	 * Views Data
	 * @var array
	 * @access private
	 */
	private $vData = array();

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		// Creat EE Instance

		// Load Models & Libraries & Helpers
		ee()->load->library('channel_files_helper');
		//ee()->load->model('tagger_model', 'tagger');

		// Some Globals
		$this->base = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=channel_files';
		$this->vData = array('base_url'	=> $this->base); // Global Views Data Array

		ee()->channel_files_helper->define_theme_url();

		$this->mcp_globals();

		// Add Right Top Menu
		ee()->cp->set_right_nav(array(
			'cf:download_log' 		=> $this->base.'&method=download_log',
			'cf:docs' 				=> ee()->cp->masked_url('http://www.devdemon.com/channel_files/docs/'),
		));

		$this->site_id = ee()->config->item('site_id');

		// Debug
		//ee()->db->save_queries = TRUE;
		//ee()->output->enable_profiler(TRUE);
	}

	// ********************************************************************************* //

	function index()
	{
		if (version_compare(APP_VER, '2.5.5', '>')) {
			ee()->view->cp_page_title = ee()->lang->line('cf:home');
		} else {
			ee()->cp->set_variable('cp_page_title', ee()->lang->line('cf:home'));
		}

		return ee()->load->view('mcp_index', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	function download_log()
	{
		// Page Title & BreadCumbs
		if (version_compare(APP_VER, '2.5.5', '>')) {
			ee()->view->cp_page_title = ee()->lang->line('cf:download_log');
		} else {
			ee()->cp->set_variable('cp_page_title', ee()->lang->line('cf:download_log'));
		}


		$this->vData['logs'] = array();

		return ee()->load->view('mcp_download_log', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	function mcp_globals()
	{
		ee()->cp->set_breadcrumb($this->base, ee()->lang->line('channel_files'));

		//ee()->cp->add_js_script(array('plugin' => 'fancybox'));
		//ee()->cp->add_to_head('<link type="text/css" rel="stylesheet" href="'.BASE.AMP.'C=css'.AMP.'M=fancybox" />');
		ee()->cp->add_js_script( array( 'ui'=> array('datepicker') ) );


		// Add Global JS & CSS & JS Scripts
		ee()->channel_files_helper->mcp_meta_parser('gjs', '', 'ChannelFiles');
		ee()->channel_files_helper->mcp_meta_parser('css', CHANNELFILES_THEME_URL . 'channel_files_mcp.css', 'ci-pbf');
		//ee()->channel_files_helper->mcp_meta_parser('js', CHANNELFILES_THEME_URL . 'jquery.editable.js', 'jquery.editable', 'jquery');
		ee()->channel_files_helper->mcp_meta_parser('js',  CHANNELFILES_THEME_URL . 'jquery.dataTables.min.js', 'jquery.dataTables', 'jquery');
		ee()->channel_files_helper->mcp_meta_parser('js',  CHANNELFILES_THEME_URL . 'channel_files_mcp.js', 'ci-pbf');

	}

	// ********************************************************************************* //

} // END CLASS

/* End of file mcp.channel_images.php */
/* Location: ./system/expressionengine/third_party/tagger/mcp.channel_images.php */
