<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * RTE Celltype Class for EE2 & EE3
 *
 * @package      Matrix
 * @author       Tom Jaeger <Tom@EEHarbor.com>
 * @copyright    Copyright (c) 2016, Tom Jaeger/EEHarbor
 */
class Matrix_rte_ft
{

    public $info = array(
        'name' => 'Rich Text',
    );

    private $_default_settings = array(
        'field_text_direction' => 'ltr',
        'field_ta_rows'        => 10,
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        ee()->load->add_package_path(PATH_MOD . 'rte/');
        // -------------------------------------------
        //  Prepare Cache
        // -------------------------------------------

        if (!isset(ee()->session->cache['matrix']['celltypes']['rte'])) {
            ee()->session->cache['matrix']['celltypes']['rte'] = array();
        }
        $this->cache = &ee()->session->cache['matrix']['celltypes']['rte'];

        // -------------------------------------------
        //  Load RTE Buttons
        // -------------------------------------------

        ee()->load->model(array('rte_toolset_model', 'rte_tool_model'));

        // grab the rte toolset so we can get the buttons
        $toolset_id = ee()->rte_toolset_model->get_member_toolset();

        // get the toolset
        $toolset = ee()->rte_toolset_model->get($toolset_id);

        $this->settings['buttons'] = array();

        if (!empty($toolset['tools']) && $tools = ee()->rte_tool_model->get_tools($toolset['tools'])) {
            foreach ($tools as $tool) {
                // skip tools that are not available to the front-end
                if ($tool['info']['cp_only'] === 'y' && REQ !== 'CP') {
                    continue;
                }

                // add to toolbar
                $this->settings['buttons'][] = strtolower(str_replace(' ', '_', $tool['info']['name']));
            }
        }
    }

    /**
     * Prep Settings
     */
    private function _prep_settings(&$settings)
    {
        $settings = array_merge($this->_default_settings, $settings);
    }

    // --------------------------------------------------------------------

    /**
     * Display Cell Settings
     */
    public function display_cell_settings($data)
    {
        ee()->lang->loadfile('admin_content');

        $this->_prep_settings($data);

        return array(
            array(
                lang('textarea_rows', 'rte_ta_rows'),
                form_input(array(
                    'id'    => 'field_ta_rows',
                    'name'  => 'field_ta_rows',
                    'class' => 'matrix-textarea',
                    'value' => $data['field_ta_rows'] ? $data['field_ta_rows'] : 10,
                )),
            ),
        );

    }

    // --------------------------------------------------------------------

    /**
     * Display Cell
     */
    public function display_cell($data)
    {
        $this->_prep_settings($this->settings);

        ee()->load->library('rte_lib');

        if (!isset($this->cache['displayed'])) {
            // include matrix_rte.js
            $theme_url = ee()->session->cache['matrix']['theme_url'];
            ee()->cp->add_to_foot('<script type="text/javascript" src="' . $theme_url . 'scripts/matrix_rte.js"></script>');
            ee()->cp->add_to_foot('<script type="text/javascript">' . @ee()->rte_lib->build_js(0, '') . '</script>');

            $this->cache['displayed'] = true;
        }

        ee()->load->add_package_path(PATH_MOD . 'rte/');
        ee()->load->library('rte_lib');

        //prep the data
        form_prep($data, $this->cell_name);

        //use the Rte_ft::display_field method
        $cell = array(
            'data'  => ee()->rte_lib->display_field($data, $this->cell_name, $this->settings),
            // 'class' => 'matrix-rte',
        );

        return $cell;
    }

    /**
     * Validate Cell
     */
    public function validate_cell($data)
    {
        ee()->load->add_package_path(PATH_MOD . 'rte/');
        ee()->load->library('rte_lib');

        if ($this->settings['col_required'] === 'y' && ee()->rte_lib->is_empty($data)) {
            return lang('col_required');
        }

        return true;
    }

    /**
     * Save Cell
     */
    public function save_cell($data)
    {
        ee()->load->add_package_path(PATH_MOD . 'rte/');
        ee()->load->library('rte_lib');

        return ee()->rte_lib->save_field($data);
    }

    /**
     * Display the RTE cell.
     *
     * @param $data
     * @return mixed
     */
    public function replace_tag($data)
    {
        $rte      = new Rte_ft();
        $rte->row = $this->row;
        return $rte->replace_tag($data);
    }
}
