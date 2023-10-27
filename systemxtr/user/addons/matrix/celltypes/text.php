<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Text Celltype Class for EE2 & EE3
 *
 * @package      Matrix
 * @author       Tom Jaeger <Tom@EEHarbor.com>
 * @copyright    Copyright (c) 2016, Tom Jaeger/EEHarbor
 */
class Matrix_text_ft
{

    public $info = array(
        'name' => 'Text',
    );

    public $default_settings = array(
        'maxl'      => '',
        'multiline' => 'n',
        'fmt'       => 'none',
        'dir'       => 'ltr',
    );

    /**
     * Constructor
     */
    public function __construct()
    {

        // -------------------------------------------
        //  Prepare Cache
        // -------------------------------------------

        if (!isset(ee()->session->cache['matrix']['celltypes']['text'])) {
            ee()->session->cache['matrix']['celltypes']['text'] = array();
        }
        $this->cache = &ee()->session->cache['matrix']['celltypes']['text'];
    }

    /**
     * Prep Settings
     */
    private function _prep_settings(&$settings)
    {
        $settings = array_merge($this->default_settings, $settings);
    }

    // --------------------------------------------------------------------

    /**
     * Display Cell Settings
     */
    public function display_cell_settings($data)
    {
        $this->_prep_settings($data);

        $field_content_options = array('all' => lang('all'), 'numeric' => lang('type_numeric'), 'integer' => lang('type_integer'), 'decimal' => lang('type_decimal'));

        return array(
            array(lang('maxl'), form_input('maxl', $data['maxl'], 'class="matrix-textarea"')),
            array(lang('multiline'), form_checkbox('multiline', 'y', ($data['multiline'] == 'y'))),
            array(lang('formatting'), form_dropdown('fmt', ee()->addons_model->get_plugin_formatting(true), $data['fmt'])),
            array(lang('direction'), form_dropdown('dir', array('ltr' => lang('ltr'), 'rtl' => lang('rtl')), $data['dir'])),
        );
    }

    // --------------------------------------------------------------------

    /**
     * Display Cell
     */
    public function display_cell($data)
    {
        $this->_prep_settings($this->settings);

        if (!isset($this->cache['displayed'])) {
            // include matrix_text.js
            $theme_url = ee()->session->cache['matrix']['theme_url'];
            ee()->cp->add_to_foot('<script type="text/javascript" src="' . $theme_url . 'scripts/matrix_text.js"></script>');

            $this->cache['displayed'] = true;
        }

        $r['class'] = 'matrix-text';
        $r['data']  = '<textarea class="matrix-textarea" name="' . $this->cell_name . '" rows="1" dir="' . $this->settings['dir'] . '">' . $data . '</textarea>';

        if ($this->settings['maxl']) {
            $r['data'] .= '<div class="matrix-charsleft-container"><div class="matrix-charsleft"></div></div>';
        }

        return $r;
    }

    // --------------------------------------------------------------------

    /**
     * Validate Cell
     */
    public function validate_cell($data)
    {
        // is this a required column?
        if ($this->settings['col_required'] == 'y' && !strlen($data)) {
            return lang('col_required');
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Pre-process
     */
    public function pre_process($data)
    {
        $this->_prep_settings($this->settings);

        ee()->load->library('typography');

        $data = ee()->typography->parse_type(
            ee()->functions->encode_ee_tags($data),
            array(
                'text_format'   => $this->settings['fmt'],
                'html_format'   => (isset($this->row['channel_html_formatting']) ? $this->row['channel_html_formatting'] : 'all'),
                'auto_links'    => (isset($this->row['channel_auto_link_urls']) ? $this->row['channel_auto_link_urls'] : 'n'),
                'allow_img_url' => (isset($this->row['channel_allow_img_urls']) ? $this->row['channel_allow_img_urls'] : 'y'),
            )
        );

        return $data;
    }

    /**
     * Replace Tag
     */
    public function replace_tag($data, $params = array())
    {
        $this->_prep_settings($this->settings);

        return $data;
    }

}
