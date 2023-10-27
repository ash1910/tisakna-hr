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

/**
 * Include the config file
 */
require_once PATH_THIRD.'gmaps_fieldtype/config.php';

class Gmaps_fieldtype_mcp {

    public $return_data;
    public $settings;

    private $show_per_page = 25;
    private $_base_url;
    private $error_msg;

    /**
     * Constructor
     */
    public function __construct()
    {
        //load the library`s
        ee()->load->library(GMAPS_FT_MAP.'_lib' );

        $this->base_url = ee('CP/URL', 'addons/settings/'.GMAPS_FT_MAP);

        //require the default settings
        require PATH_THIRD.GMAPS_FT_MAP.'/settings.php';
    }

    // ----------------------------------------------------------------

    /**
     * Index Function
     *
     * @return 	void
     */
    public function index()
    {
        ///show error if needed
        if ($this->error_msg != '')
        {
            return $this->error_msg;
        }

        //load the view
        return $this->migration();
    }

    // ----------------------------------------------------------------

    /**
     * Overview Function
     *
     * @return 	void
     */
    public function migration()
    {
        if(isset($_GET['action']))
        {
            ee()->load->library('gmaps_fieldtype_migration');
            switch($_GET['action'])
            {
                case 'get_fields':
                    $fields = ee()->gmaps_fieldtype_migration->find_fields();
                    echo json_encode($fields);
                    break;

                case 'migrate':
                    ee()->gmaps_fieldtype_migration->migrate();

                    break;
            }

            exit;
        }
        /*






        //set vars
        $vars = array();
        $vars['theme_url'] = ee()->gmaps_settings->item('theme_url');
        $vars['base_url_js'] = ee()->gmaps_settings->item('base_url_js');

        //load the view
        return $this->output('migration', $vars);
         */

        //--------------------------
        //custom settings
        $title_page = 'View Submission';
        //end custom settings
        //-------------------------

        ee()->javascript->set_global(array(
            'gmaps_field_type_base_url' => ee('CP/URL', 'addons/settings/'.GMAPS_FT_MAP)
        ));

        ee()->cp->add_js_script(array(
                'ui'     => array('progressbar'),
            )
        );

        ee()->cp->add_to_foot('<script src="' . URL_THIRD_THEMES.GMAPS_FT_MAP.'/' . 'js/gmaps_migration.js" type="text/javascript"></script>');

        $vars['title_page'] = $title_page;
        return $this->output('migration', $vars, $title_page);
    }

    // ----------------------------------------------------------------


    // ----------------------------------------------------------------

    private function output($template, $vars, $heading = '')
    {
        //support for sidebar?
        $sidebar = ee('CP/Sidebar')->make();
        $module_sidebar = $sidebar->addHeader(lang(GMAPS_FT_MAP.'_module_name'));

        //create a list
        $module_list = $module_sidebar->addBasicList();
        $module_list->addItem(lang(GMAPS_FT_MAP.'_migration'), ee('CP/URL', 'addons/settings/'.GMAPS_FT_MAP.'/migration'));
//		$module_list->addItem(lang(GMAPS_FT_MAP.'_cache'), ee('CP/URL', 'addons/settings/'.GMAPS_FT_MAP.'/cache'));
//		$module_list->addItem(lang(GMAPS_FT_MAP.'_delete_cache'), ee('CP/URL', 'addons/settings/'.GMAPS_FT_MAP.'/delete_cache'));

        return array(
            'body'       => ee('View')->make(GMAPS_FT_MAP.':'.$template)->render($vars),
//            'breadcrumb' => array(
//                ee('CP/URL', 'addons/settings/fortune_cookie')->compile() => lang('fortune_cookie_management')
//            ),
            'heading'    => GMAPS_FT_NAME.' - '.lang($heading)
        );
    }

    // ----------------------------------------------------------------


}