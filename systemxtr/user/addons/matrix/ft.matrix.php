<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

include_once 'eeharbor.php';
require_once PATH_THIRD . 'matrix/config.php';

/**
 * Matrix Fieldtype Class for EE2 & EE3
 *
 * @package      Matrix
 * @author       Tom Jaeger <Tom@EEHarbor.com>
 * @copyright    Copyright (c) 2016, Tom Jaeger/EEHarbor
 */
class Matrix_ft extends \matrix\Eeharbor_ft
{
    public $info = array(
        'name'    => MATRIX_NAME,
        'version' => MATRIX_VER,
    );

    public $has_array_data = true;

    public $entry_id;
    public $var_id; // Set by Low Variables
    public $is_draft = 0; // Set by Better Workflow

    /**
     * @var boolean
     */
    private static $_RTE_installed = null;

    private $_bundled_celltypes = array('text', 'date', 'file', 'number');

    /**
     * Fieldtype Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->eeharbor = new \matrix\EEHarbor;

        // Make RTE available only if EE version is at least 2.5.3 and it is actually installed
        if (is_null(self::$_RTE_installed)) {
            self::$_RTE_installed = version_compare(APP_VER, '2.5.3', '>=') && ee()->db->select('fieldtype_id')->where('name', 'rte')->get('fieldtypes')->row();
        }
        if (self::$_RTE_installed) {
            $this->_bundled_celltypes[] = 'rte';
        }

        // -------------------------------------------
        //  Prepare Cache
        // -------------------------------------------

        if (!isset(ee()->session->cache['matrix'])) {
            ee()->session->cache['matrix'] = array('celltypes' => array());
        }
        $this->cache = &ee()->session->cache['matrix'];
    }

    // --------------------------------------------------------------------

    /**
     * Update Field Column Associations
     *
     * Before Matrix 2.2, Matrix would associate Matrix columns to fields via the fields’ col_ids setting.
     * But now (on EE2 only), those associations are made via the exp_matrix_cols.field_id column.
     * This function populates that field_id column accordingly, and also duplicates any Matrix columns that belong to more than one field (via MSM field duplication)
     */
    public function _update_field_col_associations($field_id = false)
    {
        ee()->load->dbforge();

        $affected_cols = 0;

        // get each of the Matrix fields
        if ($this->var_id) {
            $fields = ee()->db->select('low_variables.variable_id, site_id, variable_settings')
                ->from('low_variables')
                ->join('global_variables', 'global_variables.variable_id = low_variables.variable_id')
                ->where('variable_type', 'matrix')
                ->order_by('group_id')
                ->get();
        } else {
            ee()->db->select('field_id, site_id, field_settings')
                ->where('field_type', 'matrix');

            if ($field_id) {
                ee()->db->where('field_id', $field_id);
            }

            $fields = ee()->db->order_by('site_id')
                ->get('channel_fields');
        }

        if ($fields->num_rows()) {
            $field_id_column = ($this->var_id ? 'variable_id' : 'field_id');
            $col_id_column   = ($this->var_id ? 'var_id' : 'field_id');

            foreach ($fields->result() as $field) {
                // unserialize the field settings
                if ($this->var_id) {
                    $settings = $this->low_array_decode($field->variable_settings);
                } else {
                    $settings = unserialize(base64_decode($field->field_settings));
                }

                $new_col_ids        = array();
                $old_data_col_names = array();
                $new_data_col_names = array();

                // make sure the col_ids setting is in-tact
                if (isset($settings['col_ids'])) {
                    foreach (array_filter($settings['col_ids']) as $col_id) {
                        // get the column data
                        $col = ee()->db->get_where('matrix_cols', array('col_id' => $col_id));

                        if ($col->num_rows()) {
                            $col_data = $col->row_array();

                            // does this col already belong to this field?
                            if ($col_data[$col_id_column] == $field->$field_id_column) {
                                $new_col_ids[] = $col_id;
                            } else {
                                $affected_cols++;

                                // start assempling the new column data
                                $new_col_data            = array_merge($col_data);
                                $new_col_data['site_id'] = $field->site_id;

                                if ($this->var_id) {
                                    $new_col_data['var_id'] = $field->variable_id;
                                } else {
                                    $new_col_data['field_id'] = $field->field_id;
                                }

                                // does it belong to another?
                                if ($col_data[$col_id_column]) {
                                    // duplicate it
                                    unset($new_col_data['col_id']);
                                    ee()->db->insert('matrix_cols', $new_col_data);

                                    // get the new col_id
                                    $new_col_data['col_id'] = ee()->db->insert_id();
                                    $new_col_ids[]          = $new_col_data['col_id'];

                                    // remember the old data column names
                                    $celltype           = $this->_get_celltype($col_data['col_type']);
                                    $old_data_cols      = $this->_apply_settings_modify_matrix_column($celltype, $col_data, 'get_data');
                                    $old_data_col_names = array_merge($old_data_col_names, array_keys($old_data_cols));

                                    // add the new data column(s)
                                    $new_data_cols = $this->_apply_settings_modify_matrix_column($celltype, $new_col_data, 'add');
                                    ee()->dbforge->add_column('matrix_data', $new_data_cols);

                                    // remember the new data column names
                                    $new_data_col_names = array_merge($new_data_col_names, array_keys($new_data_cols));
                                } else {
                                    // just assign it to this field
                                    ee()->db->where('col_id', $col_id)
                                        ->update('matrix_cols', $new_col_data);

                                    $new_col_ids[] = $col_id;
                                }
                            }
                        }
                    }
                }

                // update the field settings with the new col_ids array
                $settings['col_ids'] = $new_col_ids;

                if ($this->var_id) {
                    ee()->db->where('variable_id', $field->variable_id)
                        ->update('low_variables', array('variable_settings' => $this->low_array_encode($settings)));
                } else {
                    ee()->db->where('field_id', $field->field_id)
                        ->update('channel_fields', array('field_settings' => base64_encode(serialize($settings))));
                }

                // migrate the field data
                if ($old_data_col_names) {
                    $sql = 'UPDATE exp_matrix_data SET ';

                    foreach ($new_data_col_names as $i => $new_col_name) {
                        $old_col_name = $old_data_col_names[$i];

                        if ($i) {
                            $sql .= ', ';
                        }

                        $sql .= "{$new_col_name} = {$old_col_name}, {$old_col_name} = NULL";
                    }

                    if ($this->var_id) {
                        $sql .= " WHERE var_id = {$field->variable_id}";
                    } else {
                        $sql .= " WHERE field_id = {$field->field_id}";
                    }

                    ee()->db->query($sql);
                }
            }
        }

        return $affected_cols;
    }

    /**
     * Install
     */
    public function install()
    {
        ee()->load->dbforge();

        // -------------------------------------------
        //  Create the exp_matrix_cols table
        // -------------------------------------------

        if (!ee()->db->table_exists('matrix_cols')) {
            ee()->dbforge->add_field(array(
                'col_id'           => array('type' => 'int', 'constraint' => 6, 'unsigned' => true, 'auto_increment' => true),
                'site_id'          => array('type' => 'int', 'constraint' => 4, 'unsigned' => true, 'default' => 1),
                'field_id'         => array('type' => 'int', 'constraint' => 6, 'unsigned' => true),
                'var_id'           => array('type' => 'int', 'constraint' => 6, 'unsigned' => true),
                'col_name'         => array('type' => 'varchar', 'constraint' => 32),
                'col_label'        => array('type' => 'varchar', 'constraint' => 50),
                'col_instructions' => array('type' => 'text'),
                'col_type'         => array('type' => 'varchar', 'constraint' => 50, 'default' => 'text'),
                'col_required'     => array('type' => 'char', 'constraint' => 1, 'default' => 'n'),
                'col_search'       => array('type' => 'char', 'constraint' => 1, 'default' => 'n'),
                'col_order'        => array('type' => 'int', 'constraint' => 3, 'unsigned' => true),
                'col_width'        => array('type' => 'varchar', 'constraint' => 4),
                'col_settings'     => array('type' => 'text'),
            ));

            ee()->dbforge->add_key('col_id', true);
            ee()->dbforge->add_key('site_id');
            ee()->dbforge->add_key('field_id');
            ee()->dbforge->add_key('var_id');

            ee()->dbforge->create_table('matrix_cols');
        }

        // -------------------------------------------
        //  Create the exp_matrix_data table
        // -------------------------------------------

        if (!ee()->db->table_exists('matrix_data')) {
            ee()->dbforge->add_field(array(
                'row_id'    => array('type' => 'int', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true),
                'site_id'   => array('type' => 'int', 'constraint' => 4, 'unsigned' => true, 'default' => 1),
                'entry_id'  => array('type' => 'int', 'constraint' => 10, 'unsigned' => true),
                'field_id'  => array('type' => 'int', 'constraint' => 6, 'unsigned' => true),
                'var_id'    => array('type' => 'int', 'constraint' => 6, 'unsigned' => true),
                'is_draft'  => array('type' => 'TINYINT', 'constraint' => '1', 'unsigned' => true, 'default' => 0),
                'row_order' => array('type' => 'int', 'constraint' => 4, 'unsigned' => true),
            ));

            ee()->dbforge->add_key('row_id', true);
            ee()->dbforge->add_key('site_id');
            ee()->dbforge->add_key('entry_id');
            ee()->dbforge->add_key('field_id');
            ee()->dbforge->add_key('var_id');

            ee()->dbforge->create_table('matrix_data');
        } else {
            // Just add the is_draft column
            ee()->dbforge->add_column('matrix_data', array(
                'is_draft' => array('type' => 'TINYINT', 'constraint' => '1', 'unsigned' => true, 'default' => 0),
            ));
        }

        // -------------------------------------------
        //  EE1 Conversion
        // -------------------------------------------

        if (!class_exists('FF2EE2')) {
            require_once PATH_THIRD . 'matrix/includes/ff2ee2/ff2ee2.php';
        }

        // FF Matrix 1 conversion
        $converter = new FF2EE2(array('ff_matrix', 'matrix'), array(&$this, '_convert_ff_matrix_field'));

        // Matrix 2 conversion
        $converter = new FF2EE2('matrix', array(&$this, '_convert_ee1_matrix2_field'));
        return $converter->global_settings;
    }

    /**
     * Convert FF Matrix Field
     *
     * @todo - find unique words and add them to the exp_channel_data cell
     */
    public function _convert_ff_matrix_field($settings, $field)
    {
        $settings['col_ids'] = array();

        if (isset($settings['cols'])) {
            if ($settings['cols']) {
                // -------------------------------------------
                //  Add the rows to exp_matrix_cols
                // -------------------------------------------

                $col_ids_by_key      = array();
                $matrix_data_columns = array();

                foreach ($settings['cols'] as $col_key => $col) {
                    $col_type     = $col['type'];
                    $col_settings = $col['settings'];

                    switch ($col_type) {
                        case 'ff_checkbox':
                        case 'ff_checkbox_group':
                            if ($col_type == 'ff_checkbox') {
                                $col_settings = array('options' => array('y' => $col_settings['label']));
                            }

                            $col_type = 'fieldpack_checkboxes';
                            break;

                        case 'ff_select':
                            $col_type = 'fieldpack_dropdown';
                            break;

                        case 'ff_multiselect':
                            $col_type = 'fieldpack_multiselect';
                            break;

                        case 'ff_radio_group':
                            $col_type = 'fieldpack_radio_buttons';
                            break;

                        case 'ff_matrix_text':
                        case 'ff_matrix_textarea':
                            $col_settings['multiline'] = ($col_type == 'ff_matrix_text' ? 'n' : 'y');
                            $col_type                  = 'text';
                            break;

                        case 'ff_matrix_date':
                            $col_type = 'date';
                            break;
                    }

                    ee()->db->insert('matrix_cols', array(
                        'site_id'      => $field['site_id'],
                        'field_id'     => $field['field_id'],
                        'col_name'     => $col['name'],
                        'col_label'    => $col['label'],
                        'col_type'     => $col_type,
                        'col_search'   => $field['field_search'],
                        'col_order'    => $col_key,
                        'col_settings' => base64_encode(serialize($col_settings)),
                    ));

                    // get the new col_id
                    $col_id = ee()->db->insert_id();

                    // add it to the matrix_data_columns queue
                    $matrix_data_columns['col_id_' . $col_id] = array('type' => 'text');

                    // map the col_id to the col_key for later
                    $col_ids_by_key[$col_key] = $col_id;
                }

                // -------------------------------------------
                //  Add the columns to matrix_data
                // -------------------------------------------

                ee()->dbforge->add_column('matrix_data', $matrix_data_columns);

                // -------------------------------------------
                //  Move the field data into exp_matrix_data
                // -------------------------------------------

                $field_id = 'field_id_' . $field['field_id'];

                ee()->db->select('entry_id, ' . $field_id);
                ee()->db->where($field_id . ' !=', '');
                $entries = ee()->db->get('channel_data');

                foreach ($entries->result_array() as $entry) {
                    // unserialize the data
                    $old_data = FF2EE2::_unserialize($entry[$field_id]);

                    foreach ($old_data as $row_count => $row) {
                        $data = array(
                            'site_id'   => $field['site_id'],
                            'entry_id'  => $entry['entry_id'],
                            'field_id'  => $field['field_id'],
                            'row_order' => $row_count + 1,
                        );

                        foreach ($row as $col_key => $cell_data) {
                            // does this col exist?
                            if (!isset($col_ids_by_key[$col_key])) {
                                continue;
                            }

                            // get the col_id
                            $col_id = $col_ids_by_key[$col_key];

                            // flatten the cell data if necessary
                            $cell_data = $this->_flatten_data($cell_data);

                            // queue it up
                            $data['col_id_' . $col_id] = $cell_data;
                        }

                        // add the row to exp_matrix_data
                        ee()->db->insert('matrix_data', $data);
                    }

                    // clear out the old field data from exp_channel_data
                    $new_data = $this->_flatten_data($old_data);
                    ee()->db->where('entry_id', $entry['entry_id']);
                    ee()->db->update('channel_data', array($field_id => $new_data));
                }
            }

            // -------------------------------------------
            //  Remove 'cols' from field settings
            // -------------------------------------------

            unset($settings['cols']);
        }

        return $settings;
    }

    /**
     * Convert EE1 Matrix 2 Field
     */
    public function _convert_ee1_matrix2_field($settings, $field)
    {
        $this->_update_field_col_associations($field['field_id']);

        return $settings;
    }

    /**
     * Update
     */
    public function update($from)
    {
        if (!$from || $from == MATRIX_VER) {
            return false;
        }

        if (version_compare($from, '2.2', '<')) {
            $this->_update_field_col_associations();
        }

        if (version_compare($from, '2.3', '<')) {
            // Add the var_id columns

            if (!ee()->db->field_exists('var_id', 'exp_matrix_cols')) {
                ee()->db->query('ALTER TABLE exp_matrix_cols ADD var_id INT(6) UNSIGNED AFTER field_id, ADD INDEX (var_id)');
            }
            if (!ee()->db->field_exists('var_id', 'exp_matrix_data')) {
                ee()->db->query('ALTER TABLE exp_matrix_data ADD var_id INT(6) UNSIGNED AFTER field_id, ADD INDEX (var_id)');
            }
        }

        if (version_compare($from, '2.5', '<')) {
            ee()->load->dbforge();

            // Add is_draft column for Better Workflow add-on by Electric Putty.
            if (!ee()->db->field_exists('is_draft', 'exp_matrix_data')) {
                $new_field = array(
                    'is_draft' => array('type' => 'TINYINT', 'constraint' => '1', 'unsigned' => true, 'default' => 0),
                );
                ee()->dbforge->add_column('matrix_data', $new_field);
            }

            $rows = ee()->db->where('col_type', 'text')->get('matrix_cols')->result_object();
            foreach ($rows as $row) {
                $settings = unserialize(base64_decode($row->col_settings));

                if (!isset($settings['content'])) {
                    continue;
                }

                if (in_array($settings['content'], array('numeric', 'integer', 'decimal'))) {
                    $new_settings = array(
                        'min_value' => '',
                        'max_value' => '',
                        'decimals'  => '0',
                    );

                    switch ($settings['content']) {
                        case 'numeric':
                            ee()->db->query("ALTER TABLE exp_matrix_data MODIFY `col_id_" . $row->col_id . "` DECIMAL(12,2)");
                            $new_settings['decimals'] = 2;
                            break;
                        case 'integer':
                            ee()->db->query("ALTER TABLE exp_matrix_data MODIFY `col_id_" . $row->col_id . "` INT ");
                            break;
                        case 'decimal':
                            ee()->db->query("ALTER TABLE exp_matrix_data MODIFY `col_id_" . $row->col_id . "` DECIMAL(14,4)");
                            $new_settings['decimals'] = 4;
                            break;
                    }

                    ee()->db->update('matrix_cols', array('col_settings' => base64_encode(serialize($new_settings)), 'col_type' => 'number'), array('col_id' => $row->col_id));
                }
            }
        }

        return true;
    }

    /**
     * Uninstall
     */
    public function uninstall()
    {
        ee()->load->dbforge();
        ee()->dbforge->drop_table('matrix_data');
        ee()->dbforge->drop_table('matrix_cols');
    }

    // --------------------------------------------------------------------

    public function accepts_content_type($name)
    {
        return ($name == 'channel');
    }

    /**
     * Theme URL
     */
    private function _theme_url()
    {
        if (!isset($this->cache['theme_url'])) {
            $this->cache['theme_url'] = $this->eeharbor->getAddonThemesDir();
        }

        return $this->cache['theme_url'];
    }

    /**
     * Include Theme CSS
     */
    private function _include_theme_css($file)
    {
        ee()->cp->add_to_head('<link rel="stylesheet" type="text/css" href="' . $this->_theme_url() . $file . '?' . MATRIX_VER . '" />');
    }

    /**
     * Include Theme JS
     */
    private function _include_theme_js($file)
    {
        ee()->cp->add_to_foot('<script type="text/javascript" src="' . $this->_theme_url() . $file . '?' . MATRIX_VER . '"></script>');
    }

    // --------------------------------------------------------------------

    /**
     * Insert CSS
     */
    private function _insert_css($css)
    {
        ee()->cp->add_to_head('<style type="text/css">' . $css . '</style>');
    }

    /**
     * Insert JS
     */
    private function _insert_js($js)
    {
        ee()->cp->add_to_foot('<script type="text/javascript">' . $js . '</script>');
    }

    // --------------------------------------------------------------------

    /**
     * Prepare Params
     */
    private function _prep_params(&$params)
    {
        $params = array_merge(array(
            'cellspacing'        => '1',
            'cellpadding'        => '10',
            'dynamic_parameters' => '',
            'row_id'             => '',
            'orderby'            => '',
            'sort'               => 'asc',
            'offset'             => '',
            'limit'              => '',
            'backspace'          => '',
        ), $params);
    }

    // --------------------------------------------------------------------

    /**
     * Display Global Settings
     */
    public function display_global_settings()
    {
        if (ee()->addons_model->module_installed('matrix')) {
            ee()->functions->redirect($this->eeharbor->moduleURL('settings'));
        } else {
            ee()->lang->loadfile('matrix');
            $this->eeharbor->flashData('message_failure', lang('no_module'));
            ee()->functions->redirect($this->eeharbor->cpURL('addons'));
        }
    }

    /**
     * Save Global Settings
     */
    // public function save_global_settings()
    // {
    //     return array(
    //         'license_key' => isset($_POST['license_key']) ? $_POST['license_key'] : '',
    //     );
    // }

    // --------------------------------------------------------------------

    /**
     * Get Cols
     * @param int $id Either the field_id or var_id, depending on whether this is a variable or field.
     */
    private function _get_cols($id)
    {
        $cache_key = ($this->var_id ? 'var' : 'field') . $id;
        if (!isset($this->cache['field_cols'][$cache_key])) {
            $col   = ($this->var_id ? 'var_id' : 'field_id');
            $query = ee()->db->select('col_id, col_type, col_label, col_name, col_instructions, col_width, col_required, col_search, col_settings')
                ->where($col, $id)
                ->order_by('col_order')
                ->get('matrix_cols');

            if (!$query->num_rows()) {
                // Is this field a duplicate of another field?
                if ($this->_update_field_col_associations()) {
                    // probably need to update the fieldtypes version number so update() doesn't get called...
                    ee()->db->where('name', 'matrix')
                        ->update('fieldtypes', array('version' => MATRIX_VER));

                    // try again
                    return $this->_get_cols($id);
                }

                $cols = array();
            } else {
                $cols = $query->result_array();

                // unserialize the settings and cache
                foreach ($cols as &$col) {
                    $col['col_settings'] = unserialize(base64_decode($col['col_settings']));
                    if (!is_array($col['col_settings'])) {
                        $col['col_settings'] = array();
                    }

                    $celltype = $this->_get_celltype($col['col_type']);

                    if (!empty($celltype) && !empty($celltype->settings)) {
                        $celltype_settings = $celltype->settings;
                    } else {
                        $celltype_settings = array();
                    }

                    $col['has_validate_cell']  = (!$this->var_id && method_exists($celltype, 'validate_cell'));
                    $col['has_save_cell']      = method_exists($celltype, 'save_cell');
                    $col['has_post_save_cell'] = method_exists($celltype, 'post_save_cell');

                    // prepare the celltype's column settings
                    $col['celltype_settings'] = array_merge($this->settings, $celltype_settings, $col['col_settings'], array(
                        'col_id'       => $col['col_id'],
                        'col_name'     => 'col_id_' . $col['col_id'],
                        'col_required' => $col['col_required'],
                    ));
                }
            }

            $this->cache['field_cols'][$cache_key] = $cols;
        }

        return $this->cache['field_cols'][$cache_key];
    }

    // --------------------------------------------------------------------

    /**
     * Get All Celltype Settings
     *
     * @return array
     */
    private function _get_all_celltype_settings()
    {
        if (!isset($this->cache['all_celltype_settings'])) {
            $this->cache['all_celltype_settings'] = array();

            ee()->db->select('name, settings');
            $query = ee()->db->get('fieldtypes');

            foreach ($query->result() as $row) {
                $settings                                         = is_array($row->settings) ? $row->settings : unserialize(base64_decode($row->settings));
                $this->cache['all_celltype_settings'][$row->name] = $settings;
            }
        }

        return $this->cache['all_celltype_settings'];
    }

    /**
     * Get Celltype Settings
     *
     * @param string $name
     * @return array
     */
    private function _get_celltype_settings($name)
    {
        // get all of the celltype settings up front
        $all_celltype_settings = $this->_get_all_celltype_settings();

        if (!isset($all_celltype_settings[$name]) || !is_array($all_celltype_settings[$name])) {
            $all_celltype_settings[$name] = array();
        }

        return $all_celltype_settings[$name];
    }

    /**
     * Get Celltype Class
     */
    private function _get_celltype_class($name, $text_fallback = false)
    {
        // $name should look like exp_fieldtypes.name values
        if (substr($name, -3) == '_ft') {
            $name = substr($name, 0, -3);
        }

        $name = strtolower($name);

        static $classCache = array();

        if (!empty($classCache[$name])) {
            $class = $classCache[$name];
        } else {

            // is this a bundled celltype?
            if (in_array($name, $this->_bundled_celltypes)) {
                $class = 'Matrix_' . $name . '_ft';

                if (!class_exists($class)) {
                    // load it from matrix/celltypes/
                    require_once PATH_THIRD . 'matrix/celltypes/' . $name . '.php';
                }
            } else {
                $class = ucfirst($name) . '_ft';
                ee()->load->library('api');
                $this->eeharbor->instantiate('channel_fields');
                ee()->api_channel_fields->include_handler($name);
            }
            $classCache[$name] = $class;
        }

        if (class_exists($class) && !(version_compare(APP_VER, '2.5.3', '<') && $class == 'Matrix_rte_ft')) {
            if (method_exists($class, 'display_cell')) {
                return $class;
            }
        }

        return $text_fallback ? $this->_get_celltype_class('text') : false;
    }

    // --------------------------------------------------------------------

    /**
     * Get Celltype
     */
    private function _get_celltype($name, $text_fallback = false)
    {
        $class = $this->_get_celltype_class($name, $text_fallback);

        if (!$class) {
            return false;
        }

        $celltype           = new $class();
        $celltype->settings = $this->_get_celltype_settings($name);

        return $celltype;
    }

    // --------------------------------------------------------------------

    /**
     * Get All Celltypes
     */
    private function _get_all_celltypes()
    {
        // this is only called once, from display_settings(),
        // so don't worry about caching the results

        // begin with what we already know about
        $ft_names = array_merge($this->_bundled_celltypes);

        // get the fieldtypes from exp_fieldtypes
        $query = ee()->db->select('name')
            ->get('fieldtypes');

        foreach ($query->result_array() as $ft) {
            $ft_names[] = $ft['name'];
        }

        // now get the actual celltype instances
        $celltypes = array();
        $names     = array();

        foreach ($ft_names as $name) {
            if (!isset($celltypes[$name])) {
                if (($ct = $this->_get_celltype($name)) !== false) {
                    $celltypes[$name] = $ct;
                    $names[]          = $ct->info['name'];
                }
            }
        }

        // sort them alphabetically by name
        array_multisort($names, $celltypes);

        return $celltypes;
    }

    // --------------------------------------------------------------------

    /**
     * Add Package Path
     */
    private function _add_package_path($celltype)
    {
        $name = strtolower(substr(get_class($celltype), 0, -3));
        $path = PATH_THIRD . $name . '/';
        ee()->load->add_package_path($path);

        // manually add the view path if this is less than EE 2.1.5
        if (version_compare(APP_VER, '2.1.5', '<')) {
            ee()->load->_ci_view_path = $path . 'views/';
        }
    }

    // --------------------------------------------------------------------

    /**
     * Namespace Settings
     */
    public function _namespace_settings(&$settings, $namespace)
    {
        $settings = preg_replace('/(name=([\'\"]))([^\'"\[\]]+)([^\'"]*)(\2)/i', '$1' . $namespace . '[$3]$4$5', $settings);
    }

    // --------------------------------------------------------------------

    /**
     * Celltype Settings HTML
     */
    private function _celltype_settings_html($namespace, $celltype, $data = array())
    {
        if (method_exists($celltype, 'display_cell_settings')) {
            $this->_add_package_path($celltype);
            $returned = $celltype->display_cell_settings($data);

            // should we create the html for them?
            if (is_array($returned)) {
                $r = '<table class="matrix-col-settings" cellspacing="0" cellpadding="0" border="0">';

                $total_cell_settings = count($returned);

                foreach ($returned as $cs_key => $cell_setting) {
                    $tr_class = '';
                    if ($cs_key == 0) {
                        $tr_class .= ' matrix-first';
                    }

                    if ($cs_key == $total_cell_settings - 1) {
                        $tr_class .= ' matrix-last';
                    }

                    $r .= '<tr class="' . $tr_class . '">'
                        . '<th class="matrix-first">' . (!empty($cell_setting[0]) ? $cell_setting[0] : '') . '</th>'
                        . '<td class="matrix-last">' . (!empty($cell_setting[1]) ? $cell_setting[1] : '') . '</td>'
                        . '</tr>';
                }

                $r .= '</table>';
            } else {
                $r = $returned;
            }

            $this->_namespace_settings($r, $namespace);
        } else {
            $r = '';
        }

        return $r;
    }

    // --------------------------------------------------------------------

    /**
     * Get Settings
     * Used by display_settings() and display_var_settings()
     * @access private
     * @param array $data
     */
    private function _settings($data)
    {
        $min_rows = (isset($data['min_rows']) ? $data['min_rows'] : '0');
        $max_rows = (isset($data['max_rows']) ? $data['max_rows'] : '');

        // include css and js
        $this->_include_theme_css('styles/matrix.css');
        $this->_include_theme_js('scripts/matrix.js');
        $this->_include_theme_js('scripts/matrix_text.js');
        $this->_include_theme_js('scripts/matrix_conf.js');

        // language
        $this->_insert_js('MatrixConf.lang = { '
            . 'delete_col: "' . lang('delete_col') . '" };');

        // load the language files
        ee()->lang->loadfile('matrix');
        ee()->lang->loadfile('admin_content');

        // -------------------------------------------
        //  Get the celltypes
        // -------------------------------------------
        $celltypes = $this->_get_all_celltypes();

        $celltypes_select_options = array();
        $celltypes_js             = array();

        foreach ($celltypes as $name => $celltype) {
            $celltypes_select_options[$name] = $celltype->info['name'];

            // default cell settings
            $celltypes_js[$name] = $this->_celltype_settings_html('matrix[cols][{COL_ID}][settings]', $celltype, $data);
        }

        // -------------------------------------------
        //  Get the columns
        // -------------------------------------------

        if ($this->var_id) {
            if ($this->var_id != 'new') {
                $cols = $this->_get_cols($this->var_id);
            }
        } else {
            if (!empty($data['field_id']) && $data['field_type'] == 'matrix') {
                $cols = $this->_get_cols($data['field_id']);
            }
        }

        if ($is_new = empty($cols)) {
            // Are we cloning a variable?
            if ($this->var_id && ($clone = ee()->input->get('clone'))) {
                $cols = $this->_get_cols($clone);

                foreach ($cols as $i => &$col) {
                    $col['col_id'] = (string) $i;

                    unset($col['celltype_settings']['col_id'], $col['celltype_settings']['col_name']);
                }
            } else {
                // start off with a couple text cells
                $cols = array(
                    array('col_id' => '0', 'col_label' => 'Cell 1', 'col_instructions' => '', 'col_name' => 'cell_1', 'col_type' => 'text', 'col_width' => '33%', 'col_required' => 'n', 'col_search' => 'n', 'col_settings' => array('maxl' => '', 'multiline' => 'n')),
                    array('col_id' => '1', 'col_label' => 'Cell 2', 'col_instructions' => '', 'col_name' => 'cell_2', 'col_type' => 'text', 'col_width' => '', 'col_required' => 'n', 'col_search' => 'n', 'col_settings' => array('maxl' => '140', 'multiline' => 'y')),
                );
            }
        }

        $cols_js = array();

        foreach ($cols as &$col) {
            $cols_js[] = array(
                'id'   => ($is_new ? 'col_new_' : 'col_id_') . $col['col_id'],
                'type' => $col['col_type'],
            );
        }

        // -------------------------------------------
        //  Minimum Rows
        // -------------------------------------------

        $return[] = array(
            lang('min_rows', 'matrix_min_rows'),
            form_input('matrix[min_rows]', $min_rows, 'id="matrix_min_rows" style="width: 3em;"'),
        );

        // -------------------------------------------
        //  Maximum Rows
        // -------------------------------------------

        $return[] = array(
            lang('max_rows', 'matrix_max_rows'),
            form_input('matrix[max_rows]', $max_rows, 'id="matrix_max_rows" style="width: 3em;"'),
        );

        // -------------------------------------------
        //  Matrix Configuration
        // -------------------------------------------

        // We can't display the Matrix Configuration settings when first creating a new variable
        // since we won't be able to get the var_id in time while saving

        $total_cols = count($cols);

        $table = '<div id="matrix-conf-add-box"><a id="matrix-conf-add-col" class="matrix-btn matrix-add" title="' . lang('add_col') . '">' . lang('add_col') . '</a></div><div id="matrix-conf-container"><div id="matrix-conf">'
            . '<table class="matrix matrix-conf ee' . $this->eeharbor->getEEVersion() . '" cellspacing="0" cellpadding="0" border="0" style="background: #ecf1f4;">'
            . '<thead class="matrix">'
            . '<tr class="matrix matrix-first">'
            . '<td class="matrix-breakleft"></td>';

        // -------------------------------------------
        //  Labels
        // -------------------------------------------

        foreach ($cols as $col_index => &$col) {
            // If suddenly we can't find this column anymore, for example, if it got uninstalled.
            if (empty($celltypes[$col['col_type']])) {
                $col['col_type'] = 'text';
            }
            $col_id = $is_new ? 'col_new_' . $col_index : 'col_id_' . $col['col_id'];

            $class = 'matrix';
            if ($col_index == 0) {
                $class .= ' matrix-first';
            }

            if ($col_index == $total_cols - 1) {
                //$class .= ' matrix-last';
            }

            $table .= '<th class="' . $class . '" scope="col">'
                . '<input type="hidden" name="matrix[col_order][]" value="' . $col_id . '" />'
                . '<span>' . $col['col_label'] . '</span>'
                . '</th>';
        }

        // $table .= '<th class="matrix matrix-last"></th></tr>';

        $table .= '</tr>'
            . '<tr class="matrix matrix-last">'
            . '<td class="matrix-breakleft"></td>';

        // -------------------------------------------
        //  Instructions
        // -------------------------------------------

        foreach ($cols as $col_index => &$col) {
            $class = 'matrix';
            if ($col_index == 0) {
                $class .= ' matrix-first';
            }

            if ($col_index == $total_cols - 1) {
                //$class .= ' matrix-last';
            }

            $table .= '<td class="' . $class . '">' . ($col['col_instructions'] ? nl2br($col['col_instructions']) : '&nbsp;') . '</td>';
        }

        // $table .= '<td class="matrix matrix-last"></td>';

        $table .= '</tr>'
            . '</thead>'
            . '<tbody class="matrix">';

        // -------------------------------------------
        //  Col Settings
        // -------------------------------------------

        $col_settings = array('type', 'label', 'name', 'instructions', 'width');

        // Required and Searchable only apply to Channel Matrix fields
        if (!$this->var_id) {
            $col_settings[] = 'required';
            $col_settings[] = 'search';
        }

        $col_settings[] = 'settings';

        $total_settings = count($col_settings);

        foreach ($col_settings as $row_index => $col_setting) {
            $tr_class = 'matrix';
            if ($row_index == 0) {
                $tr_class .= ' matrix-first';
            }

            if ($row_index == $total_settings - 1) {
                $tr_class .= ' matrix-last';
            }

            $heading_key = ($col_setting == 'required' ? 'is_col_required' : 'col_' . $col_setting);

            $table .= '<tr class="' . $tr_class . '">'
            . '<th class="matrix-breakleft" scope="row">' . lang($heading_key) . '</th>';

            foreach ($cols as $col_index => &$col) {
                $col_id       = $is_new ? 'col_new_' . $col_index : 'col_id_' . $col['col_id'];
                $setting_name = 'matrix[cols][' . $col_id . '][' . $col_setting . ']';

                $td_class = 'matrix';
                if ($col_index == 0) {
                    $td_class .= ' matrix-first';
                }

                if ($col_index == $total_cols - 1) {
                    //$td_class .= ' matrix-last';
                }

                switch ($col_setting) {
                    case 'type':
                        $shtml = form_dropdown($setting_name, $celltypes_select_options, $col['col_' . $col_setting]);
                        break;

                    case 'name':
                    case 'width':
                        $td_class .= ' matrix-text';
                        $shtml = form_input($setting_name, $col['col_' . $col_setting], 'class="matrix-textarea"');
                        break;

                    case 'required':
                    case 'search':
                        $shtml = form_checkbox($setting_name, 'y', ($col['col_' . $col_setting] == 'y'));
                        break;

                    case 'settings':
                        $cell_data = array_merge($data, is_array($col['col_' . $col_setting]) ? $col['col_' . $col_setting] : array());
                        if (!($shtml = $this->_celltype_settings_html($setting_name, $celltypes[$col['col_type']], $cell_data))) {
                            $td_class .= ' matrix-disabled';
                            $shtml = '&nbsp;';
                        }
                        break;

                    default:
                        $td_class .= ' matrix-text';
                        $shtml = '<textarea class="matrix-textarea" name="' . $setting_name . '" rows="1">' . $col['col_' . $col_setting] . '</textarea>';
                }

                $table .= '<td class="' . $td_class . '">' . $shtml . '</td>';
            }

            // $table .= '<td class="matrix matrix-last"></td>';
            $table .= '</tr>';
        }

        // -------------------------------------------
        //  Delete Row buttons
        // -------------------------------------------

        $table .= '<tr>'
            . '<td class="matrix-breakleft"></td>';

        foreach ($cols as $col_index => &$col) {
            $table .= '<td class="matrix-breakdown">';

            // Normally we'd only have 1 extra "add column" button but for now we're adding it to every column
            // because having it just in the last one causes issues if the last column is removed.
            if($col_index == $total_cols - 1) {
            // $table .= '<a class="matrix-btn matrix-add" title="' . lang('add_col') . '"></a>';
            }

            $table .= '<a class="matrix-btn matrix-remove" title="' . lang('delete_col') . '"></a></td>';
        }

        $table .= '</tr>'
        . '</tbody>'
        . '</table>'
        // . '<a class="matrix-btn matrix-add" title="' . lang('add_col') . '"></a>'
            . '</div></div>';

        //  Initialize the configurator js
        $js = 'MatrixConf.EE2 = true;' . NL
        . 'var matrixConf = new MatrixConf("matrix", '
        . $this->_get_json($celltypes_js) . ', '
        . $this->_get_json($cols_js) . ', '
        . $this->_get_json($col_settings)
            . ');';

        if ($is_new) {
            $js .= NL . 'matrixConf.totalNewCols = ' . count($cols) . ';';
        }

        $this->_insert_js($js);

        $return[] = array(
            lang('matrix_configuration', 'matrix_configuration'),
            $table,
        );

        return $return;
    }

    /**
     * Display Field Settings
     */
    public function display_settings($data)
    {
        $settings = $this->_settings($data);

        // Pop the Matrix Configuration settings off
        $conf_setting = array_pop($settings);

        foreach ($settings as $setting) {
            $this->_display_settings_add_row($setting[0], $setting[1]);
        }

        $this->_display_settings_add_row($conf_setting[0], $conf_setting[1], '', true);

        return $this->_package_display_settings('field_options_matrix', 'matrix');
    }

    /**
     * Display Variable Settings
     * @param array $data
     */
    public function display_var_settings($data)
    {
        if (!defined('LOW_VAR_VERSION') || version_compare(LOW_VAR_VERSION, '2.2', '<')) {
            return array(
                array('', 'Matrix requires Low Variables 2.2 or later.'),
            );
        }

        return $this->_settings($data);
    }

    /**
     * Save Field Settings
     */
    public function save_settings($data)
    {
        $settings = ee()->input->post('matrix');

        // cross the T's
        $settings['field_fmt']      = 'none';
        $settings['field_show_fmt'] = 'n';
        $settings['field_type']     = 'matrix';

        // Give it the full width
        $settings['field_wide'] = true;

        return $settings;
    }

    /**
     * Post-Save Field Settings
     */
    public function post_save_settings($data)
    {
        $settings = $this->_save_settings();
        $data     = array('field_settings' => base64_encode(serialize($settings)));
        ee()->db->where('field_id', $this->field_id)
            ->update('channel_fields', $data);
    }

    /**
     * Post-Save Variable Settings
     */
    public function post_save_var_settings()
    {
        if (!$this->var_id) {
            return;
        }

        $settings = $this->_save_settings();
        $data     = array('variable_settings' => $this->low_array_encode($settings));
        ee()->db->where('variable_id', $this->var_id)
            ->update('low_variables', $data);
    }

    /**
     * Low Variables: Note that this field should be displated wide
     */
    public function var_wide()
    {
        return TRUE;
    }

    /**
     * Low Variables: Encode array to string
     * Replacement function for the no-longer-present function of the same name
     * in Low Variables for EE3.
     *
     * @access private
     * @return serialized, encoded, trimmed string
     */
    private function low_array_encode($array = array())
    {
        return str_replace('/', '_', rtrim(base64_encode(serialize($array)), '='));
    }

    /**
     * Low Variables: Decode string back to array
     * Replacement function for the no-longer-present function of the same name
     * in Low Variables for EE3.
     *
     * @access private
     * @param      string    String to decode
     * @return     array
     */
    private function low_array_decode($str = '')
    {
        return (is_string($str) && strlen($str)) ? @unserialize(base64_decode(str_replace('_', '/', $str))) : FALSE;
    }

    /**
     * Save Settings
     * Used by post_save_settings() and save_var_settings()
     * @access private
     * @return array
     */
    private function _save_settings()
    {
        ee()->load->dbforge();

        $post = ee()->input->post('matrix');

        // -------------------------------------------
        //  Delete any removed columns
        // -------------------------------------------

        if (isset($post['deleted_cols'])) {
            $delete_cols = array();

            foreach ($post['deleted_cols'] as $col_name) {
                $delete_cols[] = substr($col_name, 7);
            }

            $this->_delete_cols($delete_cols);
        }

        // -------------------------------------------
        //  Add/update columns
        // -------------------------------------------

        $settings = array(
            'min_rows'   => (!empty($post['min_rows']) ? $post['min_rows'] : '0'),
            'max_rows'   => (!empty($post['max_rows']) ? $post['max_rows'] : ''),
            'col_ids'    => array(),
            'field_wide' => true,
        );

        if (!empty($post['col_order'])) {
            foreach ($post['col_order'] as $col_order => $col_name) {
                $col      = $post['cols'][$col_name];
                $celltype = $this->_get_celltype($col['type']);

                $is_new = (substr($col_name, 0, 8) == 'col_new_');
                if (!$is_new) {
                    $col_id           = substr($col_name, 7);
                    $celltype->col_id = $col_id;
                }

                $cell_settings = isset($col['settings']) ? $col['settings'] : array();

                // give the celltype a chance to override
                if (method_exists($celltype, 'save_cell_settings')) {
                    $cell_settings = $celltype->save_cell_settings($cell_settings);
                }

                $col_data = array(
                    'col_name'         => $col['name'],
                    'col_label'        => str_replace('$', '&#36;', $col['label']),
                    'col_instructions' => str_replace('$', '&#36;', $col['instructions']),
                    'col_type'         => $col['type'],
                    'col_required'     => (isset($col['required']) && $col['required'] ? 'y' : 'n'),
                    'col_search'       => (isset($col['search']) && $col['search'] ? 'y' : 'n'),
                    'col_width'        => $col['width'],
                    'col_order'        => $col_order,
                    'col_settings'     => base64_encode(serialize($cell_settings)),
                );

                if ($is_new) {
                    $col_data['site_id'] = ee()->config->item('site_id');

                    if ($this->var_id) {
                        $col_data['var_id'] = $this->var_id;
                    } else {
                        $col_data['field_id'] = $this->field_id;
                    }

                    // insert the row
                    ee()->db->insert('matrix_cols', $col_data);

                    // get the col_id
                    $col_id             = ee()->db->insert_id();
                    $col_data['col_id'] = $col_id;

                    // notify the celltype
                    $fields = $this->_apply_settings_modify_matrix_column($celltype, $col_data, 'add');

                    // add the new column(s) to exp_matrix_data
                    ee()->dbforge->add_column('matrix_data', $fields);
                } else {
                    $col_data['col_id'] = $col_id;
                    $primary_col_name   = 'col_id_' . $col_id;

                    // get the previous col_type
                    $prev_col_type = ee()->db->select('col_type')
                        ->where('col_id', $col_id)
                        ->get('matrix_cols')
                        ->row('col_type');

                    // has the col type changed?
                    if ($prev_col_type != $col['type']) {
                        // notify the old celltype
                        $fields = $this->_apply_settings_modify_matrix_column($prev_col_type, $col_data, 'delete');

                        // delete any extra exp_matrix_data cols
                        unset($fields[$primary_col_name]);
                        foreach (array_keys($fields) as $field_name) {
                            ee()->dbforge->drop_column('matrix_data', $field_name);
                        }

                        // notify the new celltype
                        $fields = $this->_apply_settings_modify_matrix_column($celltype, $col_data, 'add');

                        // extract the primary field
                        $primary_field = array($primary_col_name => $fields[$primary_col_name]);
                        unset($fields[$primary_col_name]);

                        // update the primary column
                        $primary_field[$primary_col_name]['name'] = $primary_col_name;
                        ee()->dbforge->modify_column('matrix_data', $primary_field);

                        // add any extra cols
                        ee()->dbforge->add_column('matrix_data', $fields);
                    } else {
                        // notify the celltype
                        $fields = $this->_apply_settings_modify_matrix_column($celltype, $col_data, 'get_data');

                        // update the columns
                        foreach ($fields as $field_name => &$field) {
                            $field['name'] = $field_name;
                        }
                        ee()->dbforge->modify_column('matrix_data', $fields);
                    }

                    // update the existing row
                    ee()->db->where('col_id', $col_id);
                    ee()->db->update('matrix_cols', $col_data);
                }

                // add the col_id to the field settings
                $settings['col_ids'][] = $col_id;
            }
        }

        return $settings;
    }

    // --------------------------------------------------------------------

    /**
     * Delete Rows
     */
    public function delete_rows($row_ids)
    {
        // ignore if there are no rows to delete
        if (empty($row_ids)) {
            return;
        }

        // -------------------------------------------
        //  Notify the celltypes
        // -------------------------------------------

        $celltypes = $this->_get_all_celltypes();

        foreach ($celltypes as $name => $celltype) {
            if (method_exists($celltype, 'delete_rows')) {
                $celltype->delete_rows($row_ids);
            }
        }

        // -------------------------------------------
        //  Delete the rows
        // -------------------------------------------

        ee()->db->where_in('row_id', $row_ids)
            ->delete('matrix_data');
    }

    /**
     * Delete Columns
     */
    private function _delete_cols($col_ids)
    {
        ee()->load->dbforge();

        $cols = ee()->db->select('col_id, col_type, col_label, col_name, col_instructions, col_width, col_required, col_search, col_settings')
            ->where_in('col_id', $col_ids)
            ->get('matrix_cols')
            ->result_array();

        // -------------------------------------------
        //  exp_matrix_data
        // -------------------------------------------

        foreach ($cols as &$col) {
            // notify the celltype
            $fields = $this->_apply_settings_modify_matrix_column($col['col_type'], $col, 'delete');

            // drop the exp_matrix_data columns
            foreach (array_keys($fields) as $field_name) {
                ee()->dbforge->drop_column('matrix_data', $field_name);
            }
        }

        // -------------------------------------------
        //  exp_matrix_cols
        // -------------------------------------------

        ee()->db->where_in('col_id', $col_ids)
            ->delete('matrix_cols');
    }

    // --------------------------------------------------------------------

    /**
     * Modify exp_channel_data Column Settings
     */
    public function settings_modify_column($data)
    {
        if ($data['ee_action'] == 'delete') {
            $this->_delete_field(array('field_id' => $data['field_id']));
        }

        // just return the default column settings
        return parent::settings_modify_column($data);
    }

    /**
     * Delete Variable
     * @param int $var_id
     */
    public function delete_var($var_id)
    {
        $this->_delete_field(array('var_id' => $var_id));
    }

    /**
     * Delete Field
     * @param array $where Data to pass into db->where() when selecting the field's rows and cols
     */
    private function _delete_field($where)
    {
        // -------------------------------------------
        //  Delete the field data
        // -------------------------------------------

        $rows = ee()->db->select('row_id')
            ->where($where)
            ->get('matrix_data');

        if ($rows->num_rows()) {
            $delete_rows = array();

            foreach ($rows->result() as $row) {
                $delete_rows[] = $row->row_id;
            }

            $this->delete_rows($delete_rows);
        }

        // -------------------------------------------
        //  Delete the columns
        // -------------------------------------------

        // decode the field settings
        $query = ee()->db->select('col_id')->where($where)->get('matrix_cols');

        if ($query->num_rows()) {
            foreach ($query->result() as $col) {
                $col_ids[] = $col->col_id;
            }

            $this->_delete_cols($col_ids);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Apply settings_modify_matrix_column
     */
    private function _apply_settings_modify_matrix_column($celltype, $data, $action)
    {
        $primary_col_name = 'col_id_' . $data['col_id'];

        if (is_string($celltype)) {
            $celltype = $this->_get_celltype($celltype);
        }

        // give the celltype a chance to override the settings of the exp_matrix_data columns
        if (method_exists($celltype, 'settings_modify_matrix_column')) {
            $data['matrix_action'] = $action;

            $fields = (array) $celltype->settings_modify_matrix_column($data);

            // make sure the celltype returned the required column
            if (!isset($fields[$primary_col_name])) {
                $fields[$primary_col_name] = array('type' => 'text');
            }
        } else {
            $fields = array($primary_col_name => array('type' => 'text'));
        }

        return $fields;
    }

    // --------------------------------------------------------------------

    /**
     * Display Field
     */
    public function display_field($data)
    {
        // -------------------------------------------
        //  Include dependencies
        //   - this needs to happen *before* we load the celltypes,
        //     in case the celltypes are loading their own JS
        // -------------------------------------------

        if (!isset($this->cache['included_dependencies'])) {
            // load the language file
            ee()->lang->loadfile('matrix');

            // include css and js
            $this->_include_theme_css('styles/matrix.css');
            $this->_include_theme_js('scripts/matrix.js');

            // menu language
            $this->_insert_js('Matrix.lang = { '
                . 'options: "' . lang('options') . '", '
                . 'add_row_above: "' . lang('add_row_above') . '", '
                . 'add_row_below: "' . lang('add_row_below') . '", '
                . 'delete_row: "' . lang('delete_row') . '", '
                . 'move_to_top: "' . lang('move_to_top') . '", '
                . 'move_to_bottom: "' . lang('move_to_bottom') . '", '
                . 'remove_file: "' . lang('remove_file') . '", '
                . 'select_file_error: "' . lang('select_file_error') . '" };');

            $this->cache['included_dependencies'] = true;
        }

        // -------------------------------------------
        //  Initialize the field
        // -------------------------------------------

        $min_rows = isset($this->settings['min_rows']) ? (int) $this->settings['min_rows'] : 0;
        $max_rows = isset($this->settings['max_rows']) ? (int) $this->settings['max_rows'] : 0;

        // default $min_rows to 1 if the field is required
        if (!$min_rows && isset($this->settings['field_required']) && $this->settings['field_required'] == 'y') {
            $min_rows = 1;
        }

        if ($this->var_id) {
            $cols = $this->_get_cols($this->var_id);
            $this->entry_id = ($this->content_id()) ? $this->content_id() : ee()->input->get('entry_id');
        } else {
            $cols           = $this->_get_cols($this->field_id);
            $this->entry_id = ($this->content_id()) ? $this->content_id() : ee()->input->get('entry_id');
        }

        if (!$cols) {
            return;
        }

        $total_cols = count($cols);

        $select_col_ids    = '';
        $show_instructions = false;

        $cols_js = array();
        foreach ($cols as &$col) {
            // index the col by ID
            $select_col_ids .= ', col_id_' . $col['col_id'];

            // show instructions?
            if ($col['col_instructions']) {
                $show_instructions = true;
            }

            // For unknown celltypes, always fall back to text.
            $celltype = $this->_get_celltype($col['col_type'], true);
            $this->_add_package_path($celltype);
            $celltype->settings   = $col['celltype_settings'];
            $celltype->field_name = $this->field_name;
            $celltype->col_id     = $col['col_id'];
            $celltype->cell_name  = '{DEFAULT}';

            if ($this->var_id) {
                $celltype->var_id = $celltype->settings['var_id'] = $this->var_id;
            } else {
                $celltype->field_id = $celltype->settings['field_id'] = $this->field_id;
            }

            $new_cell_html = $celltype->display_cell('');

            $new_cell_settings = false;
            $new_cell_class    = false;

            if (is_array($new_cell_html)) {
                if (isset($new_cell_html['settings'])) {
                    $new_cell_settings = $new_cell_html['settings'];
                }

                if (isset($new_cell_html['class'])) {
                    $new_cell_class = $new_cell_html['class'];
                }

                $new_cell_html = $new_cell_html['data'];
            }

            // store the js-relevant stuff in $cols_js

            $cols_js[] = array(
                'id'              => 'col_id_' . $col['col_id'],
                'name'            => $col['col_name'],
                'label'           => $col['col_label'],
                'required'        => ($col['col_required'] == 'y' ? true : false),
                'settings'        => $col['col_settings'],
                'type'            => $col['col_type'],
                'newCellHtml'     => $new_cell_html,
                'newCellSettings' => $new_cell_settings,
                'newCellClass'    => $new_cell_class,
            );
        }

        // -------------------------------------------
        //  Get the data
        // -------------------------------------------

        // If this seems like encoded data, might be a revision being pulled up
        if (is_array($data)) {
            $current_data = ee()->db->select('*')->where('entry_id', $this->entry_id)->where('field_id', $this->field_id)->get('matrix_data')->result();

            $revision_row_ids = array();
            $current_row_ids  = array();

            $new_rows = array();

            if (!isset($data['deleted_rows'])) {
                $data['deleted_rows'] = array();
            }

            // move out all the existing rows so we can add them back in later in the right order
            if (!empty($data['row_order'])) {
                foreach ($data['row_order'] as $key) {
                    // Take note of what row ids we have here in case we need to delete them
                    if (preg_match('/row_id_(?P<row_id>[0-9]+)/', $key, $matches)) {
                        $revision_row_ids[]     = $matches['row_id'];
                        $data['deleted_rows'][] = $key;
                    }

                    $new_rows[$key] = $data[$key];
                    unset($data[$key]);
                }
            }

            // Make sure that all rows queued up for deletion actually get deleted...
            foreach ($data['deleted_rows'] as $key) {
                if (preg_match('/row_id_(?P<row_id>[0-9]+)/', $key, $matches)) {
                    // ..by killing them, if they still remain in the data array.
                    unset($data['row_id_' . $matches['row_id']]);
                }
            }

            // Determine row ids currently in play
            foreach ($current_data as $row) {
                $current_row_ids[] = $row->row_id;
            }

            // rows that exist in the current data but are missing in the revision data need to be deleted
            $missing_from_revision = array_diff($current_row_ids, $revision_row_ids);

            // Queue up the unneded rows for deletion
            if (!empty($missing_from_revision)) {
                foreach ($missing_from_revision as $row_id) {
                    // Queue up for deletion and make sure they are gone fod good
                    $data['deleted_rows'][] = 'row_id_' . $row_id;
                    unset($data['row_id_' . $row_id]);
                }
            }

            // Add all the rows back in in the correct order.
            $increment = 0;
            foreach ($new_rows as $row_key => $row_data) {
                $new_key                               = 'row_new_' . $increment++;
                $current_row_order                     = array_search($row_key, $data['row_order']);
                $data['row_order'][$current_row_order] = $new_key;
                $data[$new_key]                        = $row_data;
            }
        }

        // autosave data or validation error?
        if (is_array($data)) {
            if (isset($data['row_order'])) {
                foreach ($data['row_order'] as $row_index => $row_name) {
                    if (isset($data[$row_name])) {
                        if (substr($row_name, 0, 7) == 'row_id_') {
                            $data[$row_name]['row_id'] = substr($row_name, 7);
                        }
                    }
                }
                unset($data['row_order']);
            }

            if (isset($data['deleted_rows'])) {
                $deleted_rows = $data['deleted_rows'];
                unset($data['deleted_rows']);
            }

            // Unset the variable used to get around the marvelous revision save process.
            unset($data['trigger_revisions']);
        } else {
            $data = array();

            // is there post data?
            if (!$this->var_id && isset($_POST[$this->field_name]) && isset($_POST[$this->field_name]['row_order']) && $_POST[$this->field_name]['row_order']) {
                foreach ($_POST[$this->field_name]['row_order'] as $row_id) {
                    $row = isset($_POST[$this->field_name][$row_id]) ? $_POST[$this->field_name][$row_id] : array();

                    foreach ($cols as &$col) {
                        $data[$row_id]['col_id_' . $col['col_id']] = isset($row['col_id_' . $col['col_id']]) ? $row['col_id_' . $col['col_id']] : '';
                    }
                }
            } else {
                // is this an existing entry?
                if ($this->var_id || $this->entry_id) {
                    if ($this->entry_id && !empty(ee()->session->cache['ep_better_workflow']['is_draft'])) {
                        $this->is_draft = 1;
                        if (!isset(ee()->session->cache['ep_better_workflow']['preview_entry_data'])) {
                            ee()->session->cache['ep_better_workflow']['preview_entry_data'] = new StdClass;
                        }
                        ee()->session->cache['ep_better_workflow']['preview_entry_data']->entry_id = $this->entry_id;
                    }
                    $query = $this->_data_query();

                    // is this a clone?
                    $clone = (ee()->input->get('clone') == 'y');

                    // re-index the query data
                    foreach ($query as $count => $row) {
                        $key        = $clone ? 'row_new_' . $count : 'row_id_' . $row['row_id'];
                        $data[$key] = $row;
                    }

                    // Prep the data the same way EE does if this were an autosave or validation error
                    $data = form_prep($data);
                }
            }
        }

        if (ee()->extensions->active_hook("matrix_modify_field_data")) {
            $parameters = array('matrix_modify_field_data', $data, $this->settings);
            $data       = call_user_func_array(array(ee()->extensions, 'call'), $parameters);
        }

        // -------------------------------------------
        //  Reach the Minimum Rows count
        // -------------------------------------------

        $total_rows = count($data);

        if ($total_rows < $min_rows) {
            $extra_rows = $min_rows - $total_rows;

            for ($i = 0; $i < $extra_rows; $i++) {
                foreach ($cols as &$col) {
                    $data['row_new_' . $i]['col_id_' . $col['col_id']] = '';
                }
            }

            $total_rows = $min_rows;
        }

        // -------------------------------------------
        //  Table Head
        // -------------------------------------------

        // single-row mode?
        $single_row_mode = ($min_rows == 1 && $max_rows == 1 && $total_rows == 1);

        $thead = '<thead class="matrix">';

        $headings     = '';
        $instructions = '';

        // add left gutters if there can be more than one row
        if (!$single_row_mode) {
            $headings .= '<th class="matrix matrix-first matrix-tr-header"></th>';

            if ($show_instructions) {
                $instructions .= '<td class="matrix matrix-first matrix-tr-header"></td>';
            }
        }

        // add the labels and instructions
        foreach ($cols as $col_index => &$col) {
            $col_count = $col_index + 1;

            $class = 'matrix';
            if ($single_row_mode && $col_count == 1) {
                $class .= ' matrix-first';
            }

            if ($col_count == $total_cols) {
                $class .= ' matrix-last';
            }

            $headings .= '<th class="' . $class . '" scope="col" width="' . $col['col_width'] . '">' . $col['col_label'] . '</th>';

            if ($show_instructions) {
                $instructions .= '<td class="' . $class . '">' . nl2br($col['col_instructions']) . '</td>';
            }
        }

        $thead = '<thead class="matrix">'
            . '<tr class="matrix matrix-first' . ($show_instructions ? '' : ' matrix-last') . '">' . $headings . '</tr>'
            . ($show_instructions ? '<tr class="matrix matrix-last">' . $instructions . '</tr>' : '')
            . '</thead>';

        // -------------------------------------------
        //  Table Body
        // -------------------------------------------

        $row_settings_js = array();

        $row_ids = array();

        $tbody = '<tbody class="matrix">';

        // add the "No rows yet" row if Min Rows == 0
        if ($min_rows == 0) {
            $tbody .= '<tr class="matrix matrix-first matrix-last matrix-norows"' . ($total_rows > 0 ? ' style="display: none"' : '') . '>'
            . '<td colspan="' . ($total_cols + 1) . '" class="matrix matrix-first matrix-firstcell matrix-last">'
            . lang('no_rows') . ' <a>' . lang('create_first_row') . '</a>'
                . '</td>'
                . '</tr>';
        }

        $row_count      = 0;
        $total_new_rows = 0;

        foreach ($data as $row_name => &$row) {
            $row_count++;

            // new?
            $is_new = (substr($row_name, 0, 8) == 'row_new_');
            if ($is_new) {
                $total_new_rows++;
            }

            $tr_class = 'matrix';
            if ($row_count == 1) {
                $tr_class .= ' matrix-first';
            }

            if ($row_count == $total_rows) {
                $tr_class .= ' matrix-last';
            }

            $tbody .= '<tr class="' . $tr_class . '">';

            // add left heading if there can be more than one row
            if (!$single_row_mode) {
                $tbody .= '<th class="matrix matrix-first matrix-tr-header">'
                . '<div><span>' . $row_count . '</span><a title="' . lang('options') . '"></a></div>'
                . '<input type="hidden" name="' . $this->field_name . '[row_order][]" value="' . $row_name . '" />'
                    . '</th>';
            }

            // add the cell data
            foreach ($cols as $col_index => &$col) {
                $col_name = 'col_id_' . $col['col_id'];

                $col_count = $col_index + 1;

                $td_class = 'matrix';

                // is this the first data cell?
                if ($col_count == 1) {
                    // is this also the first cell in the <tr>?
                    if ($single_row_mode) {
                        $td_class .= ' matrix-first';
                    }

                    // use .matrix-firstcell for active state
                    $td_class .= ' matrix-firstcell';
                }

                if ($col_count == $total_cols) {
                    $td_class .= ' matrix-last';
                }

                // was there a validation error for this cell?
                if (!$this->var_id && isset($this->cache['cell_errors'][$this->field_id][$row_name][$col_name])) {
                    $td_class .= ' matrix-error';
                }

                // get new instance of this celltype, fall back to text, if unknown
                $celltype = $this->_get_celltype($col['col_type'], true);
                $this->_add_package_path($celltype);

                $cell_name = $this->field_name . '[' . $row_name . '][' . $col_name . ']';
                $cell_data = isset($row['col_id_' . $col['col_id']]) ? $row['col_id_' . $col['col_id']] : '';

                // fill it up with crap
                $celltype->settings   = $col['celltype_settings'];
                $celltype->field_name = $this->field_name;
                $celltype->col_id     = $col['col_id'];
                $celltype->cell_name  = $cell_name;
                if (isset($row['row_id'])) {
                    $celltype->row_id = $row['row_id'];
                }

                // get the cell html
                if ($this->var_id) {
                    $celltype->var_id = $celltype->settings['var_id'] = $this->var_id;
                } else {
                    $celltype->field_id = $celltype->settings['field_id'] = $this->field_id;
                }

                $cell_html = $celltype->display_cell($cell_data, $this->entry_id);

                // is the celltype sending settings too?
                if (is_array($cell_html)) {
                    if (isset($cell_html['settings'])) {
                        $row_settings_js[$col_name] = $cell_html['settings'];
                    }

                    if (isset($cell_html['class'])) {
                        $td_class .= ' ' . $cell_html['class'];
                    }

                    $cell_html = $cell_html['data'];
                }

                $tbody .= '<td width="' . $col['col_width'] . '" class="' . $td_class . '">' . $cell_html . '</td>';
            }

            $tbody .= '</tr>';

            $row_ids[] = $row_name;
        }

        $tbody .= '</tbody>';

        // -------------------------------------------
        //  Plug it all together
        // -------------------------------------------

        $field_id = str_replace(array('[', ']'), array('_', ''), $this->field_name);

        $margin = ($this->var_id ? '0' : '11px 0 1px');

        $r = '<div id="' . $field_id . '" class="matrix matrix-ee' . substr(APP_VER, 0, 1) . '" style="margin: ' . $margin . '">'
            . '<table class="matrix" cellspacing="0" cellpadding="0" border="0">'
            . $thead
            . $tbody
            . '</table>';

        if ($single_row_mode) {
            // no <th>s in the <tbody>, so we need to store the row_order outside of the table
            $r .= '<input type="hidden" name="' . $this->field_name . '[row_order][]" value="' . $row_ids[0] . '" />';
        } else {
            // add the '+' button
            $r .= '<a class="matrix-btn matrix-add' . ($max_rows && $total_rows >= $max_rows ? ' matrix-btn-disabled' : '') . '" title="' . lang('add_row') . '"></a>';
        }

        if (isset($deleted_rows)) {
            foreach ($deleted_rows as $row_id) {
                $r .= '<input type="hidden" name="' . $this->field_name . '[deleted_rows][]" value="' . $row_id . '" />';
            }
        }

        $r .= '<input type="hidden" name="' . $this->field_name . '[trigger_revisions]" value="1" />';

        $r .= '</div>';

        // initialize the field js
        $js = 'function initMatrix_' . md5($field_id) . '(){'
        . 'var m = new Matrix("#' . $field_id . '", "' . $this->field_name . '", '
        . '"' . (isset($this->settings['field_label']) ? addslashes($this->settings['field_label']) : '') . '", '
        . $this->_get_json($cols_js) . ', '
        . $this->_get_json($row_settings_js) . ', '
        . $min_rows . ', '
        . $max_rows
        . ');' . NL
        . 'm.totalNewRows = ' . $total_new_rows . ';' . NL
        . '};' . NL
        . 'jQuery(document).ready(function(){' . NL
        . 'initMatrix_' . md5($field_id) . '();' . NL
        . '});' . NL
        . 'if (typeof(Bwf) != \'undefined\'){' . NL
        . 'Bwf.bind(\'matrix\', \'previewClose\', function(){' . NL
        . 'initMatrix_' . md5($field_id) . '();' . NL
            . '});' . NL
            . '}';

        $this->_insert_js($js);

        return $r;
    }

    /**
     * Display Variable Field
     * @param string $data
     * @return string
     */
    public function display_var_field($data)
    {
        if (!$this->var_id) {
            return;
        }

        return $this->display_field($data);
    }

    // --------------------------------------------------------------------

    /**
     * Returns the row names for some given post data.
     *
     * @access private
     * @param array $data
     * @param bool  &$update_order
     * @return array
     */
    private function _get_data_row_names($data, &$update_order = null)
    {
        $update_order = (isset($data['row_order']) && is_array($data['row_order']));

        if ($update_order) {
            return $data['row_order'];
        } else {
            $row_names = array();

            if (is_array($data)) {
                foreach (array_keys($data) as $row_name) {
                    if (preg_match('/^row_(id|new)_\d+$/', $row_name)) {
                        $row_names[] = $row_name;
                    }
                }
            }

            return $row_names;
        }
    }

    /**
     * Validate
     */
    public function validate($data)
    {
        $errors = array();

        $field_id  = $this->field_id;
        $cols      = $this->_get_cols($field_id);
        $row_names = $this->_get_data_row_names($data);

        if ($row_names && $cols) {
            // load the language file
            ee()->lang->loadfile('matrix_validation', 'matrix');

            foreach ($row_names as $row_name) {
                if (isset($data[$row_name]) && isset($data['row_order']) && in_array($row_name, $data['row_order'])) {
                    $row = $data[$row_name];

                    foreach ($cols as &$col) {
                        // if the celltype has a validate_cell() method, use that for validation
                        if ($col['has_validate_cell']) {
                            $col_name  = 'col_id_' . $col['col_id'];
                            $cell_data = isset($row[$col_name]) ? $row[$col_name] : '';

                            $celltype                       = $this->_get_celltype($col['col_type']);
                            $celltype->settings             = $col['celltype_settings'];
                            $celltype->settings['row_name'] = $row_name;

                            $celltype->field_name = $this->field_name;
                            $celltype->col_id     = $col['col_id'];
                            $celltype->cell_name  = $col['col_name'];
                            if (isset($row['row_id'])) {
                                $celltype->row_id = $row['row_id'];
                            }

                            // get the cell html
                            if ($this->var_id) {
                                $celltype->var_id = $celltype->settings['var_id'] = $this->var_id;
                            } else {
                                $celltype->field_id = $celltype->settings['field_id'] = $this->field_id;
                            }

                            if ($col['col_type'] == 'file'
                                && (empty($cell_data['filename']))
                                && (!empty($_FILES[$this->field_name]['name'][$row_name][$col_name]))
                            ) {
                                $cell_data['filename'] = $_FILES[$this->field_name]['name'][$row_name][$col_name];
                            }

                            if (($error = $celltype->validate_cell($cell_data)) !== true) {
                                $this->cache['cell_errors'][$field_id][$row_name][$col_name] = true;

                                $errors[] = sprintf($error, $col['col_label']);
                            }
                        }
                    }
                }
            }
        } else {
            // is this a required field?
            if (isset($this->settings['field_required']) && $this->settings['field_required'] == 'y') {
                return lang('required');
            }
        }

        if ($errors) {
            return '<ul><li>' . implode('</li><li>', array_unique($errors)) . '</li></ul>';
        }

        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Flatten Data
     */
    private function _flatten_data($data)
    {
        $r = array();

        if (is_array($data)) {
            foreach ($data as $val) {
                $r[] = $this->_flatten_data($val);
            }
        } else {
            $r[] = $data;
        }

        return implode(NL, array_filter($r));
    }

    // --------------------------------------------------------------------

    /**
     * Save
     *
     * Cache the data for post_save() and return an empty string
     * so EE doesn't try saving an array to exp_channel_data
     *
     * @param array $data
     * @return string
     */
    public function save($data)
    {
        if (empty($data)) {
            $data = array();
        }
        $this->cache['post_data'][$this->field_id] = $data;
        return base64_encode(serialize($data));
    }

    /**
     * Post-Save Field
     *
     * Now that the entry has been saved and we can rely on having the entry_id set,
     * save the posted Matrix data, collect the keywords and save those in exp_channel_data
     */
    public function post_save($data)
    {
        // Ignore if we can't find the cached post data
        if (empty($this->cache['post_data'][$this->field_id])) {
            return;
        }

        $data             = $this->cache['post_data'][$this->field_id];
        $data['is_draft'] = $this->is_draft;
        $keywords         = $this->_save($data);

        // Cache the keywords in case Super/Low Search wants them later
        $this->cache['keywords'][$this->field_id] = $keywords;

        // Update exp_channel_data with the keywords
        $data     = array('field_id_' . $this->field_id => $keywords);
        $entry_id = (!empty($this->settings['entry_id']) ? $this->settings['entry_id'] : $this->entry_id);
        ee()->db->where('entry_id', $entry_id)
            ->update('channel_data', $data);
    }

    /**
     * Save Variable Field
     * @param array $data
     * @return string Keywords
     */
    public function save_var_field($data)
    {
        if (!$this->var_id) {
            return;
        }

        $keywords = $this->_save($data);
        return $keywords;
    }

    /**
     * Save
     * @access private
     * @param array $data
     * @return string Keywords pulled from the searchable columns
     */
    private function _save($data)
    {
        // -------------------------------------------
        //  Get the cols
        // -------------------------------------------

        if ($this->var_id) {
            $cols = $this->_get_cols($this->var_id);
        } else {
            $cols = $this->_get_cols($this->field_id);

            // The Channel Entries API doesn't set $this->entry_id because inconsistency is awesome!
            // see http://expressionengine.stackexchange.com/questions/10724/matrix-bug-with-channel-entries-api
            if (!empty($this->settings['entry_id'])) {
                $this->entry_id = $this->settings['entry_id'];
            }

            if (empty($this->entry_id)) {
                $this->entry_id = $this->content_id();
            }

            $this->is_draft = $data['is_draft'];
        }

        // -------------------------------------------
        //  Get any existing rows, indexed by row ID
        // -------------------------------------------

        $unaltered_rows = array();

        $query = $this->_data_query();

        if (!empty($query)) {
            foreach ($query as $row) {
                $unaltered_rows[$row['row_id']] = $row;
            }
        }

        // -------------------------------------------
        //  Add/update rows
        // -------------------------------------------

        $keywords   = '';
        $total_rows = 0;

        $row_names = $this->_get_data_row_names($data, $update_order);

        $only_new_rows = true;
        if ($row_names && $cols) {
            foreach ($row_names as $i => $row_name) {
                $only_new_rows = substr($row_name, 0, 8) == 'row_new_' && $only_new_rows;
            }
        }

        $max_order = 0;
        if ($only_new_rows && $row_names && !$this->var_id) {
            $rows      = ee()->db->query("SELECT MAX(row_order) AS max_order FROM `exp_matrix_data` WHERE `entry_id` = " . (int) $this->entry_id . " AND `field_id` = " . (int) $this->field_id)->result();
            $row       = $rows[0];
            $max_order = (int) $row->max_order;
        }

        // -------------------------------------------
        //  'matrix_save_field' hook
        //   - Modify the field data before it gets saved to exp_matrix_data
        //
        if (ee()->extensions->active_hook('matrix_save_field')) {
            $data = ee()->extensions->call('matrix_save_field', $this, $data);
        }

        //
        // -------------------------------------------

        if ($row_names && $cols) {
            foreach ($row_names as $i => $row_name) {
                // get the row data
                $row = isset($data[$row_name]) ? $data[$row_name] : array();

                // is this a new row?
                $is_new = (substr($row_name, 0, 8) == 'row_new_');

                if (!$is_new) {
                    $row_id = substr($row_name, 7);

                    // check for it in the existing data
                    if (isset($unaltered_rows[$row_id])) {
                        // not going to need to get the old keywords for this row
                        unset($unaltered_rows[$row_id]);
                    } else {
                        // someone else must have just deleted it?
                        $is_new   = false;
                        $row_name = 'row_new_' . (count($row_names) + $i + 1);
                    }

                    $only_new_rows = false;
                }

                // -------------------------------------------
                //  Prep the row's DB data
                // -------------------------------------------

                $row_data = array();

                $include_row = false;

                foreach ($cols as &$col) {
                    $cell_data = isset($row['col_id_' . $col['col_id']]) ? $row['col_id_' . $col['col_id']] : '';

                    // give the celltype a chance to do what it wants with it
                    if ($col['has_save_cell']) {
                        $celltype                       = $this->_get_celltype($col['col_type']);
                        $celltype->settings             = $col['celltype_settings'];
                        $celltype->settings['row_name'] = $row_name;

                        if ($this->var_id) {
                            $celltype->var_id = $celltype->settings['var_id'] = $this->var_id;
                        } else {
                            $celltype->field_id             = $celltype->settings['field_id']             = $this->field_id;
                            $celltype->settings['entry_id'] = $this->entry_id;
                            $celltype->settings['is_draft'] = $this->is_draft;
                        }

                        if ($col['col_type'] == 'file'
                            && (empty($cell_data['filename']))
                            && (!empty($_FILES[$this->field_name]['name'][$row_name]['col_id_' . $col['col_id']]))
                        ) {
                            $cell_data['filename'] = $_FILES[$this->field_name]['name'][$row_name]['col_id_' . $col['col_id']];
                        }

                        $cell_data = $celltype->save_cell($cell_data);
                    }

                    // include the row?
                    if (!$include_row && strlen($cell_data)) {
                        $include_row = true;
                    }

                    // searchable?
                    if ($col['col_search'] == 'y') {
                        $flattened_cell_data = $this->_flatten_data($cell_data);

                        if (strlen($flattened_cell_data)) {
                            $keywords .= $flattened_cell_data . NL;
                        }
                    }

                    $row_data['col_id_' . $col['col_id']] = $cell_data;
                }

                // Make sure there was data in this row
                if (!$include_row) {
                    if (!$is_new) {
                        // Mark it for deletion
                        $delete_rows[] = $row_id;
                    }

                    // Move onto the next row
                    continue;
                }

                // -------------------------------------------
                //  Save or update the row
                // -------------------------------------------

                $total_rows++;
                $max_order++;

                if ($update_order) {
                    $row_data['row_order'] = $max_order;
                }

                if (!$is_new) {
                    $row_data['row_id'] = $row_id;
                }

                $row_data['site_id'] = ee()->config->item('site_id');

                if ($this->var_id) {
                    $row_data['var_id'] = $this->var_id;
                } else {
                    $row_data['field_id'] = $this->field_id;
                    $row_data['entry_id'] = $this->entry_id;
                    $row_data['is_draft'] = $this->is_draft;
                }

                // -------------------------------------------
                //  'matrix_save_row' hook
                //   - Modify the row data before it gets saved to exp_matrix_data
                //
                if (ee()->extensions->active_hook('matrix_save_row')) {
                    $row_data = ee()->extensions->call('matrix_save_row', $this, $row_data);
                }
                //
                // -------------------------------------------

                if ($is_new) {
                    // insert the row
                    ee()->db->insert('matrix_data', $row_data);

                    // get the new row_id
                    $row_id = ee()->db->insert_id();
                } else {
                    // just update the existing row
                    ee()->db->where('row_id', $row_id)
                        ->update('matrix_data', $row_data);
                }

                // -------------------------------------------
                //  Call post_save_cell()
                // -------------------------------------------

                foreach ($cols as &$col) {
                    if ($col['has_post_save_cell']) {
                        $celltype = $this->_get_celltype($col['col_type']);
                        if ($this->is_draft) {
                            $celltype->is_draft = 1;
                        }
                        $celltype->settings = $col['celltype_settings'];

                        if ($this->var_id) {
                            $celltype->var_id = $this->var_id;
                        } else {
                            $celltype->field_id             = $celltype->settings['field_id']             = $this->field_id;
                            $celltype->settings['entry_id'] = $this->entry_id;
                        }

                        $celltype->settings['row_id']   = $row_id;
                        $celltype->settings['row_name'] = $row_name;

                        $cell_data = $row_data['col_id_' . $col['col_id']];
                        $celltype->post_save_cell($cell_data);
                    }
                }
            }
        }

        // -------------------------------------------
        //  Add keywords for rows that weren't updated
        // -------------------------------------------

        foreach ($unaltered_rows as $row_id => $row) {
            foreach ($cols as &$col) {
                if ($col['col_search'] == 'y') {
                    $cell_data           = isset($row['col_id_' . $col['col_id']]) ? $row['col_id_' . $col['col_id']] : '';
                    $flattened_cell_data = $this->_flatten_data($cell_data);

                    if (strlen($flattened_cell_data)) {
                        $keywords .= $flattened_cell_data . NL;
                    }
                }
            }
        }

        // -------------------------------------------
        //  Delete the deleted rows
        // -------------------------------------------

        if (!empty($data['deleted_rows']) && is_array($data['deleted_rows'])) {
            foreach ($data['deleted_rows'] as $row_name) {
                $delete_rows[] = substr($row_name, 7);
            }
        }

        if (!empty($delete_rows)) {
            $this->delete_rows($delete_rows);
        }

        // -------------------------------------------
        //  Return the keywords
        // -------------------------------------------

        if ($total_rows) {
            return $keywords ? $keywords : '1';
        } else {
            return '';
        }
    }

    /**
     * Gives Matrix a chance to return the real keywords
     * rather than that empty string crap we fed to save().
     */
    public function third_party_search_index($data)
    {
        if (empty($this->settings['field_id']) && !empty($this->field_id)) {
            $this->settings['field_id'] = $this->field_id;
        }

        // Just return - it's unclear what's asked for here.
        if (empty($this->settings['field_id'])) {
            return $data;
        }

        // Did the entry just save?
        if (isset($this->cache['keywords'][$this->settings['field_id']])) {
            return $this->cache['keywords'][$this->settings['field_id']];
        } else {
            return $data;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Delete
     */
    public function delete($entry_ids)
    {
        // ignore if there are no entries to delete
        if (empty($entry_ids)) {
            return;
        }

        $rows = ee()->db->select('row_id')
            ->where_in('entry_id', $entry_ids)
            ->get('matrix_data');

        if ($rows->num_rows()) {
            $row_ids = array();

            foreach ($rows->result() as $row) {
                $row_ids[] = $row->row_id;
            }

            $this->delete_rows($row_ids);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Data Query
     * @param array  $params
     * @param string $select_mode
     * @param string $select_aggregate
     * @return mixed
     */
    private function _data_query($params = array(), $select_mode = 'data', $select_aggregate = '')
    {
        // Check to see if we need to retrieve the EE Entry's Matrix rows, or the BWF Draft's Matrix Rows
        if (!empty(ee()->session->cache['ep_better_workflow']['is_draft'])
            && isset(ee()->session->cache['ep_better_workflow']['preview_entry_data']->entry_id)
            && ee()->session->cache['ep_better_workflow']['preview_entry_data']->entry_id == $this->entry_id) {
            $this->is_draft = 1;
        }

        // -------------------------------------------
        //  Get the columns
        // -------------------------------------------

        if ($this->var_id) {
            $cols = $this->_get_cols($this->var_id);
        } else {
            $cols = $this->_get_cols($this->field_id);
        }

        if (!$cols) {
            return false;
        }

        // -------------------------------------------
        //  What's and Where's
        // -------------------------------------------

        $col_ids_by_name = array();

        $select    = 'row_id';
        $where     = '';
        $use_where = false;

        foreach ($cols as &$col) {
            $col_id                            = 'col_id_' . $col['col_id'];
            $col_ids_by_name[$col['col_name']] = $col['col_id'];

            if ($select_mode == 'data') {
                $select .= ', ' . $col_id;
            }

            if (isset($params['search:' . $col['col_name']])) {
                $use_where = true;
                $terms     = $params['search:' . $col['col_name']];

                if (strncmp($terms, '=', 1) == 0) {
                    // -------------------------------------------
                    //  Exact Match e.g.: search:body="=pickle"
                    // -------------------------------------------

                    $terms = substr($terms, 1);

                    // Swap “IS_EMPTY” for an empty string
                    $terms = str_replace('IS_EMPTY', '', $terms);

                    $where .= ee()->functions->sql_andor_string($terms, $col_id) . ' ';
                } else {
                    // -------------------------------------------
                    //  "Contains" e.g.: search:body="pickle"
                    // -------------------------------------------

                    $negate = (strncmp($terms, 'not ', 4) === 0);

                    if ($negate) {
                        $terms   = substr($terms, 4);
                        $sql_not = 'NOT';
                    } else {
                        $sql_not = '';
                    }

                    // Is this an AND or an OR search?
                    if (strpos($terms, '&&') !== false) {
                        $delimiter           = '&&';
                        $sql_where_connector = $negate ? 'OR' : 'AND';
                    } else {
                        $delimiter           = '|';
                        $sql_where_connector = $negate ? 'AND' : 'OR';
                    }

                    $terms = explode($delimiter, $terms);

                    $where .= ' AND (';

                    foreach ($terms as $term) {
                        if ($term == 'IS_EMPTY') {
                            if ($negate) {
                                $where .= " ({$col_id} != \"\" AND {$col_id} IS NOT NULL) {$sql_where_connector}";
                            } else {
                                $where .= " ({$col_id} = \"\" OR {$col_id} IS NULL) {$sql_where_connector}";
                            }
                        } elseif (preg_match('/^[<>]=?/', $term, $match)) { // less than/greater than
                            $term = substr($term, strlen($match[0]));

                            $where .= ' ' . $col_id . ' ' . $match[0] . ' "' . ee()->db->escape_str($term) . '" ' . $sql_where_connector;
                        } elseif (strpos($term, '\W') !== false) { // full word only, no partial matches
                            // Note: MySQL's nutty POSIX regex word boundary is [[:>:]]
                            $term = '([[:<:]]|^)' . preg_quote(str_replace('\W', '', $term)) . '([[:>:]]|$)';

                            $where .= " {$col_id} {$sql_not} LIKE REGEXP \"" . ee()->db->escape_str($term) . "\" {$sql_where_connector}";
                        } else {
                            $where .= " {$col_id} {$sql_not} LIKE \"%" . ee()->db->escape_like_str($term) . "%\" {$sql_where_connector}";
                        }
                    }

                    $where = substr($where, 0, -strlen($sql_where_connector)) . ') ';
                }
            }
        }

        // -------------------------------------------
        //  Row IDs
        // -------------------------------------------

        if ($fixed_order = (isset($params['fixed_order']) && $params['fixed_order'])) {
            $params['row_id'] = $params['fixed_order'];
        }

        if (isset($params['row_id']) && $params['row_id']) {
            $use_where = true;

            if (strncmp($params['row_id'], 'not ', 4) == 0) {
                $sql_not          = 'NOT';
                $params['row_id'] = substr($params['row_id'], 4);
            } else {
                $sql_not = '';
            }

            $row_ids = explode('|', $params['row_id']);

            foreach ($row_ids as &$row_id) {
                $row_id = intval($row_id);
            }

            $where .= " AND row_id {$sql_not} IN (" . implode(',', $row_ids) . ')';
        }

        $sql = 'SELECT ' . ($select_mode == 'aggregate' ? $select_aggregate . ' aggregate' : $select) . '
		        FROM exp_matrix_data';

        if ($this->var_id) {
            $sql .= ' WHERE var_id = ' . intval($this->var_id);
        } else {
            $sql .= ' WHERE field_id = ' . intval($this->field_id) . '
						AND entry_id = ' . intval($this->entry_id) . '
						AND is_draft = ' . intval($this->is_draft);
        }

        if ($use_where) {
            $sql .= ' ' . $where;
        }

        // -------------------------------------------
        //  Orberby + Sort
        // -------------------------------------------

        $orderbys = (isset($params['orderby']) && $params['orderby']) ? explode('|', $params['orderby']) : array('row_order');
        $sorts    = (isset($params['sort']) && $params['sort']) ? explode('|', $params['sort']) : array();

        $all_orderbys = array();
        foreach ($orderbys as $i => $name) {
            $name           = (isset($col_ids_by_name[$name])) ? 'col_id_' . $col_ids_by_name[$name] : $name;
            $sort           = (isset($sorts[$i]) && strtoupper($sorts[$i]) == 'DESC') ? 'DESC' : 'ASC';
            $all_orderbys[] = $name . ' ' . $sort;
        }

        $sql .= ' ORDER BY ' . implode(', ', $all_orderbys);

        // -------------------------------------------
        //  Offset and Limit
        // -------------------------------------------

        // if we're not sorting randomly, go ahead and set the offset and limit in the SQL
        if ((empty($params['sort']) || $params['sort'] != 'random') && (!empty($params['limit']) || !empty($params['offset']))) {
            $offset = (!empty($params['offset']) && $params['offset'] > 0) ? $params['offset'] . ', ' : '';
            $limit  = (!empty($params['limit']) && $params['limit'] > 0) ? $params['limit'] : 1000;

            $sql .= ' LIMIT ' . $offset . $limit;
        }

        // -------------------------------------------
        //  Run and return
        // -------------------------------------------

        // -------------------------------------------
        //  'matrix_data_query' hook
        //   - Override the SQL query
        //
        if (ee()->extensions->active_hook('matrix_data_query')) {
            $query = ee()->extensions->call('matrix_data_query', $this, $params, $sql, $select_mode);
        } else {
            $query = ee()->db->query($sql);
        }
        //
        // -------------------------------------------

        switch ($select_mode) {
            case 'data':

                if (!is_object($query)) {
                    return array();
                }

                $data = $query->result_array();

                if ($fixed_order) {
                    $data_by_id = array();

                    foreach ($data as $row) {
                        $data_by_id[$row['row_id']] = $row;
                    }

                    $data = array();

                    foreach ($row_ids as $row_id) {
                        if (isset($data_by_id[$row_id])) {
                            $data[] = $data_by_id[$row_id];
                        }
                    }
                }

                return $data;

            case 'aggregate':

                return $query->row('aggregate');

            case 'row_ids':

                $row_ids = array();

                foreach ($query->result() as $row) {
                    $row_ids[] = $row->row_id;
                }

                return $row_ids;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Replace Tag
     */
    public function replace_tag($data, $params = array(), $tagdata = false)
    {

        // ignore if no tagdata
        if (!$tagdata) {
            return;
        }

        // dynamic params
        if (isset($params['dynamic_parameters'])) {
            $dynamic_parameters = explode('|', $params['dynamic_parameters']);
            foreach ($dynamic_parameters as $param) {
                if (($val = ee()->input->post($param)) !== false) {
                    $params[$param] = ee()->db->escape_str($val);
                }
            }
        }

        $r = '';

        // -------------------------------------------
        //  Get the columns
        // -------------------------------------------

        if ($this->var_id) {
            $cols = $this->_get_cols($this->var_id);
        } elseif (isset($this->row['entry_id'])) {
            $cols           = $this->_get_cols($this->field_id);
            $this->entry_id = $this->row['entry_id'];
        } else {
            $cols = false;
        }

        if (!$cols) {
            return $r;
        }

        // -------------------------------------------
        //  Get the data
        // -------------------------------------------

        // limit to 100 rows by default
        if (empty($params['limit'])) {
            $params['limit'] = 100;
        }

        $data = $this->_data_query($params);

        if (!$data) {
            return '';
        }

        // -------------------------------------------
        //  Randomize
        // -------------------------------------------

        if (isset($params['sort']) && $params['sort'] == 'random') {
            shuffle($data);

            // apply the limit now, since we didn't do it in the original query
            if (isset($params['limit']) && $params['limit']) {
                $data = array_splice($data, 0, $params['limit']);
            }
        }

        // -------------------------------------------
        //  Tagdata
        // -------------------------------------------

        $var_prefix = (isset($params['var_prefix']) ? rtrim($params['var_prefix'], ':') . ':' : '');

        // get the full list of row IDs
        $field_row_ids_params    = array();
        $this->_field_row_ids    = $this->_data_query($field_row_ids_params, 'row_ids');
        $this->_field_total_rows = count($this->_field_row_ids);

        // are {prev_row} or {next_row} being used?
        $siblings_in_use = ((strstr($tagdata, "{$var_prefix}prev_row") !== false) || (strstr($tagdata, "{$var_prefix}next_row") !== false));

        // see which col tags are being used
        foreach ($cols as &$col) {
            $col['in_use'] = preg_match("/\{{$var_prefix}" . $col['col_name'] . '[\}: ]/', $tagdata) ? true : false;
        }

        // {total_rows} and {field_total_rows}
        $vars = array(
            "{$var_prefix}total_rows"       => count($data),
            "{$var_prefix}field_total_rows" => $this->_field_total_rows,
        );
        $tagdata = ee()->functions->var_swap($tagdata, $vars);

        $tagdata = ee()->functions->prep_conditionals($tagdata, $vars);

        // process each row
        foreach ($data as $this->_row_index => &$row) {
            $row_tagdata = $tagdata;

            // get the row's index within the entire field
            $this->_field_row_index = array_search($row['row_id'], $this->_field_row_ids);

            // parse sibling tags
            if ($siblings_in_use) {
                $conditionals = array(
                    "{$var_prefix}prev_row" => ($this->_field_row_index > 0 ? 'y' : ''),
                    "{$var_prefix}next_row" => ($this->_field_row_index < $this->_field_total_rows - 1 ? 'y' : ''),
                );

                $row_tagdata = ee()->functions->prep_conditionals($row_tagdata, $conditionals);

                // {prev_row} and {next_row} tag pairs
                $row_tagdata = preg_replace_callback('/' . LD . "{$var_prefix}(prev_row|next_row)" . RD . '(.*)' . LD . "\/{$var_prefix}\\1" . RD . '/sU', array(&$this, '_parse_sibling_tag'), $row_tagdata);
            }

            $conditionals = array();
            $tags         = array();

            foreach ($cols as &$col) {
                $col_name = 'col_id_' . $col['col_id'];

                $cell_data = $row[$col_name];

                $conditionals[$var_prefix . $col['col_name']] = $cell_data;

                if ($col['in_use']) {
                    // Fall back to text on unknown cell type
                    $celltype = $this->_get_celltype($col['col_type'], true);

                    $celltype_vars = array(
                        'col_id'   => $col['col_id'],
                        'col_name' => $col_name,
                        'row_id'   => $row['row_id'],
                        'row_name' => 'row_id_' . $row['row_id'],
                        'settings' => array_merge($this->settings, $celltype->settings, $col['col_settings']),
                    );

                    if ($this->var_id) {
                        $celltype_vars['var_id'] = $this->var_id;
                    } else {
                        $celltype_vars['row']        = $this->row;
                        $celltype_vars['field_id']   = $this->field_id;
                        $celltype_vars['field_name'] = $this->field_name;
                    }

                    if ($this->is_draft) {
                        $celltype_vars['is_draft'] = 1;
                    }

                    // call pre_process?
                    if (method_exists($celltype, 'pre_process')) {
                        foreach ($celltype_vars as $key => $value) {
                            $celltype->$key = $value;
                        }

                        $cell_data = $celltype->pre_process($cell_data);
                    }

                    $tags[$col['col_name']] = array(
                        'data' => $cell_data,
                        'type' => $col['col_type'],
                        'vars' => $celltype_vars,
                    );
                }
            }

            $vars = array(
                "{$var_prefix}field_row_index" => $this->_field_row_index,
                "{$var_prefix}field_row_count" => $this->_field_row_index + 1,
                "{$var_prefix}row_index"       => $this->_row_index,
                "{$var_prefix}row_count"       => $this->_row_index + 1,
                "{$var_prefix}row_id"          => $row['row_id'],
            );

            $row_tagdata = ee()->functions->var_swap($row_tagdata, $vars);
            $row_tagdata = ee()->functions->prep_conditionals($row_tagdata, array_merge($vars, $conditionals));

            if ($tags) {
                $this->_parse_tagdata($row_tagdata, $tags, $var_prefix);
            }

            // {switch} tags
            $row_tagdata = preg_replace_callback('/' . LD . $var_prefix . 'switch\s*=\s*([\'\"])([^\1]+)\1' . RD . '/sU', array(&$this, '_parse_switch_tag'), $row_tagdata);

            $r .= $row_tagdata;
        }

        unset($this->_field_row_ids, $this->_field_total_rows, $this->_field_row_index, $this->_row_index);

        if (isset($params['backspace']) && $params['backspace']) {
            $r = substr($r, 0, -$params['backspace']);
        }

        return $r;
    }

    /**
     * Display Variable Tag
     */
    public function display_var_tag($data, $params = array(), $tagdata = false)
    {
        if (!$this->var_id) {
            return;
        }

        return $this->replace_tag($data, $params, $tagdata);
    }

    // --------------------------------------------------------------------

    /**
     * Parse Step Tag
     */
    private function _parse_sibling_tag($match)
    {
        if ($match[1] == 'prev_row') {
            // ignore if this is the first row
            if ($this->_field_row_index == 0) {
                return;
            }

            $row_id = $this->_field_row_ids[$this->_field_row_index - 1];
        } else {
            // ignore if this is the last row
            if ($this->_field_row_index == $this->_field_total_rows - 1) {
                return;
            }

            $row_id = $this->_field_row_ids[$this->_field_row_index + 1];
        }

        $obj           = new Matrix_ft();
        $obj->settings = $this->settings;

        if ($this->var_id) {
            $obj->var_id = $this->var_id;
        } else {
            $obj->row        = $this->row;
            $obj->field_id   = $this->field_id;
            $obj->field_name = $this->field_name;
        }

        return $obj->replace_tag('', array('row_id' => $row_id), $match[2]);
    }

    /**
     * Parse Switch Tag
     */
    private function _parse_switch_tag($match)
    {
        $options = explode('|', $match[2]);

        $option = $this->_row_index % count($options);

        return $options[$option];
    }

    // --------------------------------------------------------------------

    /**
     * Table
     */
    public function replace_table($data, $params = array())
    {
        $thead   = '';
        $tagdata = '    <tr>' . "\n";

        // get the cols
        $cols = $this->_get_cols($this->field_id);
        if (!$cols) {
            return '';
        }

        // which table features do they want?
        $set_row_ids = (isset($params['set_row_ids']) && $params['set_row_ids'] == 'yes');
        $set_classes = (isset($params['set_classes']) && $params['set_classes'] == 'yes');
        $set_widths  = (isset($params['set_widths']) && $params['set_widths'] == 'yes');

        $thead   = '';
        $tagdata = '    <tr' . ($set_row_ids ? ' id="row_id_' . LD . 'row_id' . RD . '"' : '') . '>' . "\n";

        foreach ($cols as &$col) {
            $attr = '';
            if ($set_classes) {
                $attr .= ' class="' . $col['col_name'] . '"';
            }

            if ($set_widths) {
                $attr .= ' width="' . $col['col_width'] . '"';
            }

            $thead .= '      <th scope="col"' . $attr . '>' . $col['col_label'] . '</th>' . "\n";
            $tagdata .= '      <td' . $attr . '>' . LD . $col['col_name'] . RD . '</td>' . "\n";
        }

        $tagdata .= '    </tr>' . "\n";

        $attr = '';
        if (isset($params['cellspacing'])) {
            $attr .= ' cellspacing="' . $params['cellspacing'] . '"';
        }

        if (isset($params['cellpadding'])) {
            $attr .= ' cellpadding="' . $params['cellpadding'] . '"';
        }

        if (isset($params['border'])) {
            $attr .= ' border="' . $params['border'] . '"';
        }

        if (isset($params['width'])) {
            $attr .= ' width="' . $params['width'] . '"';
        }

        if (isset($params['class'])) {
            $attr .= ' class="' . $params['class'] . '"';
        }

        return '<table' . $attr . '>' . "\n"
        . '  <thead>' . "\n"
        . '    <tr>' . "\n"
        . $thead
        . '    </tr>' . "\n"
        . '  </thead>' . "\n"
        . '  <tbody>' . "\n"
        . $this->replace_tag($data, $params, $tagdata)
            . '  </tbody>' . "\n"
            . '</table>';
    }

    // --------------------------------------------------------------------

    /**
     * Replace Sibling Tag
     */
    private function _replace_sibling_tag($params, $tagdata, $which)
    {
        // ignore if no tagdata
        if (!$tagdata) {
            return;
        }

        $this->entry_id = $this->row['entry_id'];

        // get the full list of row IDs
        $field_row_ids_params = array_merge($params, array('row_id' => '', 'limit' => '', 'offset' => ''));
        $field_row_ids        = $this->_data_query($field_row_ids_params, 'row_ids');

        if (!$field_row_ids) {
            return;
        }

        $field_total_rows = count($field_row_ids);

        // get the starting row's ID
        if (isset($params['row_id']) && $params['row_id']) {
            $row_id = $params['row_id'];
        } else {
            $query_params = array_merge($params, array('limit' => '1'));
            $query        = $this->_data_query($query_params, 'row_ids');
            $row_id       = $query[0];
        }

        // get the starting row's index within the entire field
        $field_row_index = array_search($row_id, $field_row_ids);

        if ($which == 'prev') {
            // ignore if this is the first row
            if ($field_row_index == 0) {
                return;
            }

            $sibling_row_id = $field_row_ids[$field_row_index - 1];
        } else {
            // ignore if this is the last row
            if ($field_row_index == $field_total_rows - 1) {
                return;
            }

            $sibling_row_id = $field_row_ids[$field_row_index + 1];
        }

        return $this->replace_tag('', array('row_id' => $sibling_row_id), $tagdata);
    }

    /**
     * Previous Row
     */
    public function replace_prev_row($data, $params = array(), $tagdata = false)
    {
        return $this->_replace_sibling_tag($params, $tagdata, 'prev');
    }

    /**
     * Next Row
     */
    public function replace_next_row($data, $params = array(), $tagdata = false)
    {
        return $this->_replace_sibling_tag($params, $tagdata, 'next');
    }

    // --------------------------------------------------------------------

    /**
     * Total Rows
     */
    public function replace_total_rows($data, $params = array())
    {
        $this->entry_id = $this->row['entry_id'];
        $count          = $this->_data_query($params, 'aggregate', 'COUNT(row_id)');
        return $count ? $count : 0;
    }

    // --------------------------------------------------------------------

    /**
     * Get Col ID by Name
     */
    public function _get_col_id_by_name($col_name, $cols)
    {
        // find the target col
        foreach ($cols as $col) {
            if ($col['col_name'] == $col_name) {
                return $col['col_id'];
            }
        }
    }

    /**
     * Format Number
     */
    private function _format_number($params, $num)
    {
        $decimals      = isset($params['decimals']) ? (int) $params['decimals'] : 0;
        $dec_point     = isset($params['dec_point']) ? $params['dec_point'] : '.';
        $thousands_sep = isset($params['thousands_sep']) ? $params['thousands_sep'] : ',';

        return number_format($num, $decimals, $dec_point, $thousands_sep);
    }

    /**
     * Aggregate Column
     */
    private function _aggregate_col($params, $func)
    {
        // ignore if no col= param is set
        if (!isset($params['col']) || !$params['col']) {
            return;
        }

        $cols = $this->_get_cols($this->field_id);
        if (!$cols) {
            return;
        }

        // get the col_id
        $col_id = $this->_get_col_id_by_name($params['col'], $cols);

        // ignore if that col doesn't exist
        if (!$col_id) {
            return;
        }

        $this->entry_id = $this->row['entry_id'];
        $num            = $this->_data_query($params, 'aggregate', "{$func}(col_id_{$col_id})");
        return $this->_format_number($params, $num);
    }

    /**
     * Average
     */
    public function replace_average($data, $params = array())
    {
        return $this->_aggregate_col($params, 'AVG');
    }

    /**
     * Sum
     */
    public function replace_sum($data, $params = array())
    {
        return $this->_aggregate_col($params, 'SUM');
    }

    /**
     * Lowest
     */
    public function replace_lowest($data, $params = array())
    {
        return $this->_aggregate_col($params, 'MIN');
    }
    /**
     * Highest
     */
    public function replace_highest($data, $params = array())
    {
        return $this->_aggregate_col($params, 'MAX');
    }

    // --------------------------------------------------------------------

    /**
     * Parse Tagdata
     */
    private function _parse_tagdata(&$tagdata, $tags, $var_prefix)
    {
        global $DSP;

        // find the next celltype tag
        $offset      = 0;
        $field_names = array_keys($tags);
        rsort($field_names);
        while (preg_match('/' . LD . "{$var_prefix}(?P<field_name>" . implode('|', $field_names) . ')(?::)?(?P<modifier>[\w\-]+)?(?::)' .
            '?(?P<modifier_parameter>[\w\-]+)?(?P<params>\s+.*?)?' . RD . '/s', $tagdata, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $field_name = $matches['field_name'][0];
            $field      = $tags[$field_name];

            $tag_pos     = $matches[0][1];
            $tag_len     = strlen($matches[0][0]);
            $tagdata_pos = $tag_pos + $tag_len;
            $endtag      = LD . "/{$var_prefix}{$field_name}" . (!empty($matches['modifier'][0]) ? ':' . $matches['modifier'][0] : '') . RD;
            $endtag_len  = strlen($endtag);
            $endtag_pos  = strpos($tagdata, $endtag, $tagdata_pos);
            $modifier    = !empty($matches['modifier'][0]) ? $matches['modifier'][0] : 'tag';
            $tag_func    = 'replace_' . $modifier;

            $class         = $this->_get_celltype_class($field['type'], true);
            $method_exists = method_exists($class, $tag_func);
            $use_catchall  = false;

            // if a replace method doesn't exist or we have additional parameter to pass along
            if (!$method_exists or !empty($matches['modifier_parameter'][0])) {
                // Does the celltype have a 'catchall' tag?
                $tag_func      = 'replace_tag_catchall';
                $method_exists = method_exists($class, $tag_func);
                if (!empty($matches['modifier'][0])) {
                    $modifier = $matches['modifier'][0] . (!empty($matches['modifier_parameter'][0]) ? ':' . $matches['modifier_parameter'][0] : '');
                } else {
                    $modifier = '';
                }

                $use_catchall = true;
            }

            if ($method_exists) {
                // get the params
                $params = array();

                if (isset($matches['params'][0]) && $matches['params'][0] && preg_match_all('/\s+(?P<parameter>[\w-:]+)\s*=\s*([\'\"])(?P<value>[^\2]*)\2/sU', $matches['params'][0], $param_matches)) {
                    for ($j = 0; $j < count($param_matches['parameter']); $j++) {
                        $params[$param_matches['parameter'][$j]] = $param_matches['value'][$j];
                    }
                }

                // get inner tagdata
                $field_tagdata = ($endtag_pos !== false)
                ? substr($tagdata, $tagdata_pos, $endtag_pos - $tagdata_pos)
                : '';

                // Fall back to text cell if celltype not found
                $celltype = $this->_get_celltype($field['type'], true);
                $this->_add_package_path($celltype);

                foreach ($field['vars'] as $key => $value) {
                    $celltype->$key = $value;
                }

                if ($use_catchall) {
                    $new_tagdata = (string) $celltype->$tag_func($field['data'], $params, $field_tagdata, $modifier);
                } else {
                    $new_tagdata = (string) $celltype->$tag_func($field['data'], $params, $field_tagdata);
                }
            } else {
                $new_tagdata = $field['data'];
            }

            $offset = $tag_pos;

            $tagdata = substr($tagdata, 0, $tag_pos)
            . $new_tagdata
            . substr($tagdata, ($endtag_pos !== false ? $endtag_pos + $endtag_len : $tagdata_pos));

            unset($new_tagdata);
        }
    }

    /**
     * Save a Better Workflow draft
     *
     * @param array $data Field data as submitted form publish form
     * @param string $action 'create' or 'upadte'
     * @return string
     */
    public function draft_save($data, $action)
    {
        if (!is_array($data)) {
            return '';
        }

        $this->field_id = $this->settings['field_id'];
        // Are we in create mode?
        if ($action == 'create') {
            // If so any existing Row ID needs to be made a new row
            $draft_data = array();
            $c          = 0;
            if (isset($data['row_order'])) {
                foreach ($data['row_order'] as $row_id) {
                    $new_row_id                = 'row_new_' . $c;
                    $draft_data['row_order'][] = $new_row_id;
                    $draft_data[$new_row_id]   = $data[$row_id];
                    $c++;
                }
            }
        } else {
            $draft_data = $data;
        }
        $draft_data['is_draft'] = 1;
        return $this->_save($draft_data);
    }

    /**
     * Delete a Better Workflow draft
     */
    public function draft_discard()
    {
        // Do this for all Matrix columns for this field
        $cols = $this->_get_cols($this->field_id);
        foreach ($cols as $col) {
            $celltype = $this->_get_celltype($col['col_type']);
            if (method_exists($celltype, 'discard_draft')) {
                $celltype->settings = $col['celltype_settings'];
                $celltype->discard_draft();
            }
        }

        // Delete all the current draft content
        ee()->db->delete('matrix_data', array('entry_id' => $this->settings['entry_id'], 'field_id' => $this->settings['field_id'], 'is_draft' => 1));
        return;
    }

    /**
     * Publish a Better Workflow draft
     */
    public function draft_publish()
    {
        // Do this for all Matrix columns for this field
        $cols = $this->_get_cols($this->field_id);
        foreach ($cols as $col) {
            $celltype = $this->_get_celltype($col['col_type']);
            if (method_exists($celltype, 'draft_publish')) {
                $celltype->settings             = $col['celltype_settings'];
                $celltype->settings['entry_id'] = $this->settings['entry_id'];
                $celltype->draft_publish();
            }
        }

        // Get the entry_id and field_id
        $entry_id = $this->settings['entry_id'];
        $field_id = $this->settings['field_id'];

        // Delete all the current live content
        ee()->db->delete('matrix_data', array('entry_id' => $entry_id, 'field_id' => $field_id, 'is_draft' => 0));

        // Update the current draft content to be live
        ee()->db->where('entry_id', $entry_id);
        ee()->db->where('field_id', $field_id);
        ee()->db->update('matrix_data', array('is_draft' => 0));

        return;
    }

    /**
     * Get JSON formatted data for any given data.
     *
     * @param $data
     * @return string
     */
    private function _get_json($data)
    {
        if (version_compare(APP_VER, '2.6', '<') or !function_exists('json_encode')) {
            return ee()->javascript->generate_json($data, true);
        } else {
            return json_encode($data);
        }
    }
}
