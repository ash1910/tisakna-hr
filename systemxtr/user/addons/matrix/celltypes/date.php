<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Date Celltype Class for EE2 & EE3
 *
 * @package      Matrix
 * @author       Tom Jaeger <Tom@EEHarbor.com>
 * @copyright    Copyright (c) 2016, Tom Jaeger/EEHarbor
 */
class Matrix_date_ft
{

    public $info = array(
        'name' => 'Date',
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        ee()->lang->loadfile('matrix');

        // -------------------------------------------
        //  Prepare Cache
        // -------------------------------------------

        if (!isset(ee()->session->cache['matrix']['celltypes']['date'])) {
            ee()->session->cache['matrix']['celltypes']['date'] = array();
        }
        $this->cache = &ee()->session->cache['matrix']['celltypes']['date'];
    }

    // --------------------------------------------------------------------

    /**
     * Modify exp_matrix_data Column Settings
     */
    public function settings_modify_matrix_column($data)
    {
        return array(
            'col_id_' . $data['col_id'] => array('type' => 'int', 'constraint' => 10, 'default' => 0),
        );
    }

    // --------------------------------------------------------------------

    /**
     * Display Cell
     */
    public function display_cell($data)
    {
        if (!isset($this->cache['displayed'])) {
            if(substr(APP_VER, 0, 1) == 2) {
                // include matrix_text.js
                $theme_url = ee()->session->cache['matrix']['theme_url'];
                ee()->cp->add_to_foot('<script type="text/javascript" src="' . $theme_url . 'scripts/matrix_date_ee2.js"></script>');
                ee()->cp->add_js_script(array('ui' => 'datepicker'));
            }
            $this->cache['displayed'] = true;
        }

        $r['class'] = 'matrix-date matrix-text';

        if(substr(APP_VER, 0, 1) >= 3) $r['class'] .= ' matrix-focus-disabled';

        // quick save / validation error?
        $datestr = trim($data);
        $datestr = preg_replace('/\040+/', ' ', $datestr);

        $timestamp = $data && is_numeric($data) ? $data : false;

        if (preg_match('/^(?P<first_part>[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}\s)(?P<hours>[0-9]{1,2})(?P<second_part>:[0-9]{1,2}(:[0-9]{1,2})?)(?P<meridiem>\s[AP]M)?$/i', $datestr, $matches)) {
            // Prevent a seemingly valid, yet totally invalid, date from crashing EE.
            if (isset($matches['meridiem']) && strtolower($matches['meridiem']) == ' pm' && $matches['hours'] === '0') {
                $datestr = $matches['first_part'] . '12' . $matches['second_part'] . $matches['meridiem'];
            }

            // convert human time to a unix timestamp
            $timestamp = version_compare(APP_VER, '2.6', '<') ? ee()->localize->convert_human_date_to_gmt($datestr) : ee()->localize->string_to_timestamp($datestr);
        }

        // set the default date to the current time
        if (version_compare(APP_VER, '2.6', '<')) {
            $r['settings']['defaultDate'] = ($timestamp ? ee()->localize->set_localized_time($timestamp) : ee()->localize->set_localized_time()) * 1000;
        } else {
            $r['settings']['defaultDate'] = ($timestamp ? ee()->localize->format_date("%U", $timestamp) : ee()->localize->format_date("%U", $timestamp)) * 1000;
        }

        $r['settings']['dateFormat'] = ee()->localize->datepicker_format();
        $r['settings']['timeFormat'] = ee()->session->userdata('time_format', ee()->config->item('time_format'));

        // get the initial input value
        if ($timestamp) {
            $formatted_date = ($data ? (version_compare(APP_VER, '2.6', '<') ? ee()->localize->set_human_time($timestamp) : ee()->localize->human_time($timestamp)) : '');
        } else {
            $formatted_date = $data ? $data : ''; // Let's just leave the wrong date and marvel
        }

        $format    = 'format-' . ee()->config->item('time_format');
        $error     = !(bool) strtotime($data);
        $r['data'] = form_input(array(
            'name'  => $this->cell_name,
            'value' => $formatted_date,
            'class' => 'matrix-textarea ' . $format . ($error ? ' hasError' : ''),
            'rel' => 'date-picker'
        ));

        return $r;
    }

    // --------------------------------------------------------------------

    /**
     * Validate Cell
     */
    public function validate_cell($data)
    {
        // is this a required column?
        if ($this->settings['col_required'] == 'y' && !$data) {
            return lang('col_required');
        }

        if ($data && !strtotime($data)) {
            return lang('incorrect_date');
        }

        return true;
    }

    /**
     * Save Cell
     */
    public function save_cell($data)
    {
        // convert the formatted date to a Unix timestamp
        return (int) (version_compare(APP_VER, '2.6', '<') ? ee()->localize->convert_human_date_to_gmt($data) : ee()->localize->string_to_timestamp($data));
    }

    // --------------------------------------------------------------------

    /**
     * Replace Tag
     */
    public function replace_tag($data, $params = array())
    {
        if (!$data) {
            return '';
        }

        if (isset($params['format'])) {
            if (version_compare(APP_VER, '2.6', '<')) {
                $data = ee()->localize->decode_date($params['format'], $data);
            } else {
                $data = ee()->localize->format_date($params['format'], $data);
            }
        }

        return $data;
    }

}
