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

use EllisLab\ExpressionEngine\Library\CP\Table;

class Gmaps_mcp {

	public $return_data;
	public $settings;

	/*
     * Max per page
     */
	private $show_per_page = 2;

	private $error_msg;

	/*
     * Base url
     */
	private $base_url;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		//load the library`s
		ee()->load->library('gmaps_library', null, 'gmaps');

		$this->base_url = ee('CP/URL', 'addons/settings/gmaps');

		//require the default settings
		require PATH_THIRD.GMAPS_MAP.'/settings.php';

	}


	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @return 	void
	 */
	public function index()
	{
		//show error if needed
		if ($this->error_msg != '')
		{
			return $this->error_msg;
		}

		//load the view
		return $this->settings();
	}

	// ----------------------------------------------------------------

	/**
	 * Settings Function
	 *
	 * @return 	void
	 */
	public function settings()
	{
		//reset the settings?
		if(isset($_GET['action']) && $_GET['action'] == 'reset_geocoders')
		{
			ee()->gmaps_settings->save_setting('geocoding_providers', array('google_maps'));

			//set a message
			ee('CP/Alert')->makeInline(GMAPS_MAP.'_settings')
				->asSuccess()
				->withTitle(lang('success'))
				->addToBody(lang(GMAPS_MAP.'_geocoders_reset'))
				->defer();

			//redirect
			ee()->functions->redirect($this->base_url);
		}

		//is there some data tot save?
		if(isset($_POST) && !empty($_POST))
		{
			$_POST['geocoding_providers'][] = 'google_maps';
			ee()->gmaps_settings->save_post_settings();
		}

		//set the settigns for the geocoding providers
		$geocoding_providers = array_filter((array)ee()->gmaps_settings->item('geocoding_providers'));

		//vars for the view and the form
		$geocoding_providers_settings = array();
		foreach($this->def_geocoding_providers as $provider => $val)
		{
			$desc = isset($val[1]) ? $val[1] : '';
			$val = isset($geocoding_providers[$provider]) ? $geocoding_providers[$provider] : $val[0];


			$geocoding_providers_settings[] = array(
				'title' => $provider,
				'desc' => $desc,
				'fields' => array(
					'geocoding_providers['.$provider.']' => array(
						'type' => 'inline_radio',
						'value' => $val,
						'choices' => array(
							'1' => 'Enabled',
							'0' => 'Disabled'
						)
					)
				)
			);
		}

		//set the settings form
		$vars['sections'] = array(
			array(
				array(
					'title' => GMAPS_MAP.'_license_key',
					'fields' => array(
						'license_key' => array(
							'type' => 'text',
							'value' => ee()->gmaps_settings->item('license_key'),
							'required' => TRUE
						)
					)
				),
				array(
					'title' => GMAPS_MAP.'_report_stats',
					'desc' => 'PHP & EE versions will be anonymously reported to help improve the product.',
					'fields' => array(
						'report_stats' => array(
							'type' => 'inline_radio',
							'value' => ee()->gmaps_settings->item('report_stats'),
							'choices' => array(
								'1' => 'Yes',
								'0' => 'No'
							)
						)
					)
				),
                array(
                    'title' => GMAPS_MAP.'_cache_time',
                    'desc' => 'Cache time in seconds (default 604800)',
                    'fields' => array(
                        'cache_time' => array(
                            'type' => 'text',
                            'value' => ee()->gmaps_settings->item('cache_time')
                        )
                    )
                ),
				array(
					'title' => GMAPS_MAP.'_dev_mode',
					'desc' => 'Set the Gmaps in dev mode and serve the JS files no as minified.',
					'fields' => array(
						'dev_mode' => array(
							'type' => 'inline_radio',
							'value' => ee()->gmaps_settings->item('dev_mode'),
							'choices' => array(
								'1' => 'Yes',
								'0' => 'No'
							)
						)
					)
				),
				array(
					'title' => GMAPS_MAP.'_data_transfer',
					'desc' => 'What kind of connection will be used to geocode the address',
					'fields' => array(
						'data_transfer' => array(
							'type' => 'inline_radio',
							'value' => ee()->gmaps_settings->item('data_transfer'),
							'choices' => array(
								'curl' => 'Curl',
								'http' => 'HTTP'
							)
						)
					)
				),
			),
            GMAPS_MAP.'_api_keys' => array(
                array(
                    'title' => GMAPS_MAP.'_google_api_key_client',
                    'desc' => 'This is the key with the restriction set to HTTP referrers (web sites) <br>Get your api key from https://console.developers.google.com',
                    'fields' => array(
                        'google_api_key_client' => array(
                            'type' => 'text',
                            'value' => ee()->gmaps_settings->item('google_api_key_client'),
                            'required' => true
                        )
                    )
                ),
                array(
                    'title' => GMAPS_MAP.'_google_api_key_server',
                    'desc' => 'This is the key with the restriction set to IP addresses (web servers, cron jobs, etc) <br>Get your api key from https://console.developers.google.com',
                    'fields' => array(
                        'google_api_key_server' => array(
                            'type' => 'text',
                            'value' => ee()->gmaps_settings->item('google_api_key_server'),
                            'required' => true
                        )
                    )
                ),
                array(
                    'title' => GMAPS_MAP.'_bing_maps_key',
                    'desc' => 'Set your Bing Maps key. Used if you enable the Bing Maps geocoding service',
                    'fields' => array(
                        'bing_maps_key' => array(
                            'type' => 'text',
                            'value' => ee()->gmaps_settings->item('bing_maps_key'),
                            'required' => false
                        )
                    )
                ),
                array(
                    'title' => GMAPS_MAP.'_map_quest_key',
                    'desc' => 'Set your Map Quest key. Used if you enable the Map Quest geocoding service',
                    'fields' => array(
                        'map_quest_key' => array(
                            'type' => 'text',
                            'value' => ee()->gmaps_settings->item('map_quest_key'),
                            'required' => false
                        )
                    )
                ),
                array(
                    'title' => GMAPS_MAP.'_tomtom_key',
                    'desc' => 'Set your TomTom key. Used if you enable the TomTom geocoding service',
                    'fields' => array(
                        'tomtom_key' => array(
                            'type' => 'text',
                            'value' => ee()->gmaps_settings->item('tomtom_key'),
                            'required' => false
                        )
                    )
                ),
            ),
			GMAPS_MAP.'_geocoding_providers' => $geocoding_providers_settings
		);

		// Final view variables we need to render the form
		$vars += array(
			'base_url' => ee('CP/URL', 'cp/addons/settings/gmaps'),
			'cp_page_title' => lang('general_settings'),
			'save_btn_text' => 'btn_save_settings',
			'save_btn_text_working' => 'btn_saving',
			'alerts_name' => GMAPS_MAP.'_settings'
		);

		return $this->output('form', $vars, 'settings');
	}

	// ----------------------------------------------------------------

	/**
	 * Overview Function
	 *
	 * @return 	void
	 */
	public function cache()
	{
        //delete action
        if(isset($_POST['delete']) && !empty($_POST['delete']))
        {
            //do your delete stuff here
			ee('Model')->get('gmaps:Cache')->filter('cache_id', ee()->input->post('delete'))->delete();

            //set a message
            ee('CP/Alert')->makeInline(GMAPS_MAP.'_notice')
                ->asSuccess()
                ->withTitle(lang('success'))
                ->addToBody('ID #'.ee()->input->post('delete')." deleted")
                ->defer();

            ee()->functions->redirect(ee('CP/URL', 'cp/addons/settings/'.GMAPS_MAP.'/'.__FUNCTION__));
            exit;
        }

        //--------------------------
        //custom settings
        $title_page = 'Cache overview';
        $action_buttons = array(
            GMAPS_MAP.'_delete_cache' => ee('CP/URL', 'addons/settings/'.GMAPS_MAP.'/delete_cache')
        );
        $per_page = 25;
        $sort_col = ee()->input->get('sort_col') ?: 'column_date';
        $sort_dir = ee()->input->get('sort_dir') ?: 'desc';
        //end custom settings
        //-------------------------

        // Specify other options
        $table = ee('CP/Table', array(
            'sort_col' => $sort_col,
            'sort_dir' => $sort_dir
        ));

        //set the columns
        $table->setColumns(
            array(
                'column_address',
                'column_date',
                'column_type',
                'manage' => array(
                    'type'  => Table::COL_TOOLBAR
                )
            )
        );

        //set a no result text
        $table->setNoResultsText('No recoreds available');

        //get all data
        $cur_page = ((int) ee()->input->get('page')) ?: 1;
        $offset = ($cur_page - 1) * $per_page; // Offset is 0 indexed

        $results = ee('Model')->get('gmaps:Cache')
            ->order(str_replace('column_', '', $table->config['sort_col']), $table->config['sort_dir'])
            ->limit($per_page)
            ->offset($offset);

        //format the data
        $data = array();
        $ids = array();
        foreach ($results->all() as $result)
        {
            //save IDS for the delete confirm dialog
            $ids[] = array(
                'id' => $result->cache_id,
                'msg' => 'Cache ID:'
            );

            //set the data
            $data[] = array(
                $result->address,
                ee()->localize->human_time($result->date),
                $result->type,
                array('toolbar_items' => array(
                    'remove' => array(
                        'href' => '',
                        'title' => lang('remove'),
                        'rel' => "modal-confirm-".$result->cache_id,
                        'class' => 'm-link'
                    )
                ))
            );
        }

        //set the data
        $table->setData($data);

        // Pass in a base URL to create sorting links
        $base_url = ee('CP/URL', 'addons/settings/'.GMAPS_MAP.'/cache');
        $vars['table'] = $table->viewData($base_url);
        $vars['base_url'] = $vars['table']['base_url'];
        $vars['action_url'] = $base_url;
        $vars['ids'] = $ids;

        //create the paging
        $vars['pagination'] = ee('CP/Pagination', ee('Model')->get('gmaps:Cache')->count())
            ->perPage($per_page)
            ->currentPage($cur_page)
            ->render($base_url);

        //Set the title
        $vars['title_page'] = $title_page;

        //set the buttons
        $vars['action_buttons'] = $action_buttons;

        return $this->output('overview', $vars, $title_page);
	}

    // ----------------------------------------------------------------

    /**
     * Overview Function
     *
     * @return 	void
     */
    public function log()
    {
        //delete action
        if(isset($_POST['delete']) && !empty($_POST['delete']))
        {
            //do your delete stuff here
			ee('Model')->get('gmaps:Log')->filter('log_id', ee()->input->post('delete'))->delete();

            //set a message
            ee('CP/Alert')->makeInline(GMAPS_MAP.'_notice')
                ->asSuccess()
                ->withTitle(lang('success'))
                ->addToBody('Log ID #'.ee()->input->post('delete')." deleted")
                ->defer();

            ee()->functions->redirect(ee('CP/URL', 'cp/addons/settings/'.GMAPS_MAP.'/'.__FUNCTION__));
            exit;
        }

        //--------------------------
        //custom settings
        $title_page = 'Log overview';
        $action_buttons = array(
            GMAPS_MAP.'_delete_all_logs' => ee('CP/URL', 'addons/settings/'.GMAPS_MAP.'/delete_logs')
        );
        $per_page = 10;
        $sort_col = ee()->input->get('sort_col') ?: 'column_log_id';
        $sort_dir = ee()->input->get('sort_dir') ?: 'desc';
        //end custom settings
        //-------------------------

        // Specify other options
        $table = ee('CP/Table', array(
            'sort_col' => $sort_col,
            'sort_dir' => $sort_dir
        ));

        //set the columns
        $table->setColumns(
            array(
                'column_log_id',
                'column_time',
                'column_message',
                'manage' => array(
                    'type'  => Table::COL_TOOLBAR
                )
            )
        );

        //set a no result text
        $table->setNoResultsText('No records available');

        //get all data
        $cur_page = ((int) ee()->input->get('page')) ?: 1;
        $offset = ($cur_page - 1) * $per_page; // Offset is 0 indexed

        $results = ee('Model')->get('gmaps:Log')
            ->order(str_replace('column_', '', $table->config['sort_col']), $table->config['sort_dir'])
            ->limit($per_page)
            ->offset($offset);

        //format the data
        $data = array();
        $ids = array();
        foreach ($results->all() as $result)
        {
            //save IDS for the delete confirm dialog
            $ids[] = array(
                'id' => $result->log_id,
                'msg' => 'Log ID:'
            );

            //set the data
            $data[] = array(

                $result->log_id,
                ee()->localize->human_time($result->time),
                $result->message,
                array('toolbar_items' => array(
                    'remove' => array(
                        'href' => '',
                        'title' => lang('remove'),
                        'rel' => "modal-confirm-".$result->log_id,
                        'class' => 'm-link'
                    )
                ))
            );
        }

        //set the data
        $table->setData($data);

        // Pass in a base URL to create sorting links
        $base_url = ee('CP/URL', 'addons/settings/'.GMAPS_MAP.'/log');
        $vars['table'] = $table->viewData($base_url);
        $vars['base_url'] = $vars['table']['base_url'];
        $vars['action_url'] = $base_url;
        $vars['ids'] = $ids;

        //create the paging
        $vars['pagination'] = ee('CP/Pagination', ee('Model')->get('gmaps:Log')->count())
            ->perPage($per_page)
            ->currentPage($cur_page)
            ->render($base_url);

        //Set the title
        $vars['title_page'] = $title_page;

        //set the buttons
        $vars['action_buttons'] = $action_buttons;

        return $this->output('overview', $vars, $title_page);
    }

    // ----------------------------------------------------------------

    /**
     * Overview Function
     *
     * @return 	void
     */
    public function delete_logs()
    {
        $vars = array(
            'url' => ''
        );

        if(ee()->uri->segment(6) == 'confirmed')
        {
            //get all cache
            ee('Model')->get('gmaps:Log')->delete();

            //set a message
            ee('CP/Alert')->makeInline('gmaps_delete_confirm')
                ->asSuccess()
                ->withTitle(lang('success'))
                ->addToBody(lang(GMAPS_MAP.'_logs_deleted'))
                ->now();
        }
        else
        {
            $vars = array(
                'url' => ee('CP/URL', 'addons/settings/gmaps/delete_logs/confirmed')
            );

            ee('CP/Alert')->makeInline('gmaps_delete_confirm')
                ->asWarning()
                ->withTitle(lang('warning'))
                ->addToBody('You`re about to deleting all of your Gmaps Logs.</strong> Are you sure?</strong>')
                ->now();
        }


        return $this->output('delete', $vars);
    }

	// ----------------------------------------------------------------

	/**
	 * Overview Function
	 *
	 * @return 	void
	 */
	public function delete_cache()
	{
		$vars = array(
			'url' => ''
		);

		if(ee()->uri->segment(6) == 'confirmed')
		{
			//get all cache
			ee('Model')->get('gmaps:Cache')->delete();

			//set a message
			ee('CP/Alert')->makeInline('gmaps_delete_confirm')
				->asSuccess()
				->withTitle(lang('success'))
				->addToBody(lang(GMAPS_MAP.'_cache_deleted'))
				->now();
		}
		else
		{
			$vars = array(
				'url' => ee('CP/URL', 'addons/settings/gmaps/delete_cache/confirmed')
			);

			ee('CP/Alert')->makeInline('gmaps_delete_confirm')
				->asWarning()
				->withTitle(lang('warning'))
				->addToBody('You`re about to deleting all of your Gmaps Cache.</strong> Are you sure?</strong>')
				->now();
		}

		return $this->output('delete', $vars);
	}


	private function output($template, $vars, $heading = '')
	{
		//support for sidebar?
		$sidebar = ee('CP/Sidebar')->make();
		$module_sidebar = $sidebar->addHeader(lang('gmaps_module_name'));

		//create a list
		$module_list = $module_sidebar->addBasicList();
		$module_list->addItem(lang(GMAPS_MAP.'_settings'), ee('CP/URL', 'addons/settings/'.GMAPS_MAP.'/settings'));
		$module_list->addItem(lang(GMAPS_MAP.'_log'), ee('CP/URL', 'addons/settings/'.GMAPS_MAP.'/log'));
		$module_list->addItem(lang(GMAPS_MAP.'_cache'), ee('CP/URL', 'addons/settings/'.GMAPS_MAP.'/cache'));
//		$module_list->addItem(lang(GMAPS_MAP.'_delete_cache'), ee('CP/URL', 'addons/settings/'.GMAPS_MAP.'/delete_cache'));

		return array(
			'body'       => ee('View')->make('gmaps:'.$template)->render($vars),
//            'breadcrumb' => array(
//                ee('CP/URL', 'addons/settings/fortune_cookie')->compile() => lang('fortune_cookie_management')
//            ),
			'heading'    => GMAPS_NAME.' - '.lang($heading)
		);
	}
}

