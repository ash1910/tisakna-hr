<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

include_once 'eeharbor.php';
require_once PATH_THIRD . 'matrix/config.php';

/**
 * Matrix Extension Class for EE2 & EE3
 *
 * @package        Matrix
 * @author        Tom Jaeger <Tom@EEHarbor.com>
 * @copyright    Copyright (c) 2016, Tom Jaeger/EEHarbor
 */
class Matrix_ext extends \matrix\Eeharbor_ext
{

    public $name           = MATRIX_NAME;
    public $version        = MATRIX_VER;
    public $settings_exist = 'n';
    public $docs_url       = 'https://eeharbor.com/matrix/documentation';

    /**
     * Extension Constructor
     */
    public function __construct()
    {
        $this->eeharbor = new \matrix\EEHarbor;

        // -------------------------------------------
        //  Prepare Cache
        // -------------------------------------------

        if (!isset(ee()->session->cache['matrix'])) {
            ee()->session->cache['matrix'] = array();
        }
        $this->cache = &ee()->session->cache['matrix'];
    }

    // --------------------------------------------------------------------

    /**
     * Activate Extension
     */
    public function activate_extension()
    {
        $this->register_extension('channel_entries_tagdata');
    }

    /**
     * Update Extension
     */
    public function update_extension($current = false)
    {
        if (!$current || $current == $this->version) {
            return false;
        }

        $this->update_version();
    }

    /**
     * Disable Extension
     */
    public function disable_extension()
    {
        parent::disable_extension();
    }

    /**************************************************\
     ******************* ALL HOOKS: *******************
    \**************************************************/

    /**
     * channel_entries_tagdata hook
     */
    public function channel_entries_tagdata($tagdata, $row)
    {
        // has this hook already been called?
        if (isset(ee()->extensions->last_call) && ee()->extensions->last_call) {
            $tagdata = ee()->extensions->last_call;
        }

        $disable = explode('|', ee()->TMPL->fetch_param('disable'));

        if (in_array('matrix', $disable)) {
            return $tagdata;
        }

        $this->row = $row;

        // get the fields
        $entry_site_id = isset($row['entry_site_id']) ? $row['entry_site_id'] : 0;
        $fields        = $this->_get_site_fields($entry_site_id);

        // iterate through each Matrix field
        foreach ($fields as $field) {
            if (strpos($tagdata, '{' . $field['field_name']) !== false) {
                $this->field = $field;
                $tagdata     = preg_replace_callback("/\{({$field['field_name']}(\s+.*?)?)\}(.*?)\{\/{$field['field_name']}\}/s", array(&$this, '_parse_tag_pair'), $tagdata);
            }
        }

        unset($this->row, $this->field);

        return $tagdata;
    }

    /**************************************************\
     ******************* ALL ELSE: ********************
    \**************************************************/

    /**
     * Get Site Fields
     */
    private function _get_site_fields($site_id)
    {
        if (!isset($this->cache['fields'][$site_id])) {
            ee()->db->select('field_id, field_name, field_settings');
            ee()->db->where('field_type', 'matrix');
            if ($site_id) {
                ee()->db->where('site_id', $site_id);
            }

            $query = ee()->db->get('channel_fields');

            $fields = $query->result_array();

            foreach ($fields as &$field) {
                $field['field_settings'] = unserialize(base64_decode($field['field_settings']));
            }

            $this->cache['fields'][$site_id] = $fields;
        }

        return $this->cache['fields'][$site_id];
    }

    /**
     * Parse Tag Pair
     */
    private function _parse_tag_pair($m)
    {
        // prevent {exp:channel:entries} from double-parsing this tag
        unset(ee()->TMPL->var_pair[$m[1]]);

        //$params_str = isset($m[2]) ? $m[2] : '';
        $tagdata = isset($m[3]) ? $m[3] : '';

        // get the params
        $params = array();
        if (isset($m[2]) && $m[2] && preg_match_all('/\s+([\w-:]+)\s*=\s*([\'\"])([^\2]*)\2/sU', $m[2], $param_matches)) {
            for ($i = 0; $i < count($param_matches[0]); $i++) {
                $params[$param_matches[1][$i]] = $param_matches[3][$i];
            }
        }

        // get the tagdata
        $tagdata = isset($m[3]) ? $m[3] : '';

        // -------------------------------------------
        //    Call the tag's method
        // -------------------------------------------

        if (!class_exists('Matrix_ft')) {
            require_once PATH_THIRD . 'matrix/ft.matrix.php';
        }

        $Matrix_ft             = new Matrix_ft();
        $Matrix_ft->row        = $this->row;
        $Matrix_ft->field_id   = $this->field['field_id'];
        $Matrix_ft->field_name = $this->field['field_name'];
        $Matrix_ft->entry_id   = $this->row['entry_id'];
        $Matrix_ft->settings   = array_merge($this->row, $this->field['field_settings']);

        return (string) $Matrix_ft->replace_tag(null, $params, $tagdata);
    }
}
