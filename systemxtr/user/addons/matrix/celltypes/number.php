<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Number Celltype Class for EE2 & EE3
 *
 * @package      Matrix
 * @author       Tom Jaeger <Tom@EEHarbor.com>
 * @copyright    Copyright (c) 2016, Tom Jaeger/EEHarbor
 */
class Matrix_number_ft
{

    public $info = array(
        'name' => 'Number',
    );

    public $default_settings = array(
        'min_value' => '',
        'max_value' => '',
        'decimals'  => '',
    );

    /**
     * Integer column sizes
     *
     * @static
     * @access private
     * @var array
     */
    private static $_int_column_sizes = array(
        'tinyint'   => 128,
        'smallint'  => 32768,
        'mediumint' => 8388608,
        'int'       => 2147483648,
        'bigint'    => 9223372036854775808,
    );

    /**
     * Constructor
     */
    public function __construct()
    {

        // -------------------------------------------
        //  Prepare Cache
        // -------------------------------------------

        if (!isset(ee()->session->cache['matrix']['celltypes']['number'])) {
            ee()->session->cache['matrix']['celltypes']['number'] = array();
        }
        $this->cache = &ee()->session->cache['matrix']['celltypes']['number'];
    }

    /**
     * Prep Settings
     */
    private function _prep_settings(&$settings)
    {
        $settings              = array_merge($this->default_settings, $settings);
        $settings['min_value'] = is_numeric($settings['min_value']) ? $settings['min_value'] : -self::$_int_column_sizes['int'];
        $settings['max_value'] = is_numeric($settings['max_value']) ? $settings['max_value'] : self::$_int_column_sizes['int'] - 1;
        $settings['decimals']  = is_numeric($settings['decimals']) && $settings['decimals'] > 0 ? intval($settings['decimals']) : 0;
    }

    // --------------------------------------------------------------------

    /**
     * Display Cell Settings
     */
    public function display_cell_settings($data)
    {
        $data = array_merge(array(
            'min_value' => '',
            'max_value' => '',
            'decimals'  => '0',
        ), $data);

        return array(
            array(str_replace(' ', '&nbsp;', lang('min_value')), form_input('min_value', $data['min_value'], 'class="matrix-textarea"')),
            array(str_replace(' ', '&nbsp;', lang('max_value')), form_input('max_value', $data['max_value'], 'class="matrix-textarea"')),
            array(str_replace(' ', '&nbsp;', lang('decimals')), form_input('decimals', $data['decimals'], 'class="matrix-textarea"')),
        );
    }

    /**
     * Modify exp_matrix_data Column Settings
     */
    public function settings_modify_matrix_column($data)
    {
        // decode the field settings
        $settings = unserialize(base64_decode($data['col_settings']));

        $this->_prep_settings($settings);

        // Unsigned?
        $unsigned = ($settings['min_value'] >= 0);

        // Figure out the max length
        $max_abs_size = intval($unsigned ? $settings['max_value'] : max(abs($settings['min_value']), abs($settings['max_value'])));

        // Decimal type
        if ($settings['decimals'] > 0) {
            return array('col_id_' . $data['col_id'] => array(
                'type'     => 'DECIMAL(' . (strlen($max_abs_size) + $settings['decimals']) . ',' . $settings['decimals'] . ')',
                'unsigned' => $unsigned,
                'default'  => 0,
            ));
        } else {
            foreach (self::$_int_column_sizes as $column_type => $size) {
                if ($unsigned) {
                    if ($settings['max_value'] < $size * 2) {
                        return array('col_id_' . $data['col_id'] => array(
                            'type'     => $column_type,
                            'unsigned' => true,
                            'default'  => 0,
                        ));
                    }
                } else {
                    if ($settings['min_value'] >= -$size && $settings['max_value'] < $size) {
                        return array('col_id_' . $data['col_id'] => array(
                            'type'     => $column_type,
                            'unsigned' => false,
                            'default'  => 0,
                        ));
                    }
                }
            }
        }
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
        $r['data']  = '<input type="text" class="matrix-textarea" name="' . $this->cell_name . '" rows="1" value="' . $data . '" />';

        return $r;
    }

    // --------------------------------------------------------------------

    /**
     * Validate Cell
     */
    public function validate_cell($data)
    {
        ee()->lang->loadfile('matrix');

        if (!strlen($data)) {
            // is this a required column?
            if ($this->settings['col_required'] == 'y') {
                return lang('col_required');
            } else {
                return true;
            }
        }

        if (!is_numeric(($data))) {
            return lang('value_not_numeric');
        }

        if (is_numeric($this->settings['min_value']) && $data < $this->settings['min_value']) {
            return str_replace('{min}', $this->settings['min_value'], lang('value_too_small'));
        }

        if (is_numeric($this->settings['max_value']) && $data > $this->settings['max_value']) {
            return str_replace('{max}', $this->settings['max_value'], lang('value_too_big'));
        }

        if ($this->settings['decimals'] == 0 && (float) $data != (int) $data) {
            return lang('decimals_not_allowed');
        }

        return true;
    }

    /**
     * Parse tag for number type.
     *
     * @param $data
     * @param $params
     * @param $field_tagdata
     * @return string
     */
    public function replace_tag($data, $params, $field_tagdata)
    {
        if (!empty($params['thousands_sep'])) {
            if (empty($params['dec_point'])) {
                $params['dec_point'] = '.';
            }
            $data = number_format($data, $this->settings['decimals'], $params['dec_point'], $params['thousands_sep']);
        }
        return $data;
    }

    /**
     * Ensure an integer.
     *
     * @param $data
     * @return int
     */
    public function save_cell($data)
    {
        if (is_numeric($data)) {
            return $data;
        } else {
            return (float) $data;
        }
    }
}
