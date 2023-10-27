<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Gmaps Update File
 *
 * @package             Gmaps for EE3
 * @author              Rein de Vries (info@reinos.nl)
 * @copyright           Copyright (c) 2015 Rein de Vries
 * @license  			http://reinos.nl/add-ons/commercial-license
 * @link                http://reinos.nl/add-ons/gmaps
 */

include(PATH_THIRD.'gmaps_fieldtype/config.php');

class Gmaps_fieldtype_upd {

    private $EE;

    public $version = GMAPS_FT_VERSION;

    /**
     * Constructor
     */
    public function __construct()
    {
        //load the classes
        ee()->load->dbforge();

        ee()->load->library('gmaps_fieldtype_lib');

        //require the settings
        require PATH_THIRD.'gmaps_fieldtype/settings.php';
    }

    // ----------------------------------------------------------------

    /**
     * Installation Method
     *
     * @return 	boolean 	TRUE
     */
    public function install()
    {
        if (strnatcmp(phpversion(),'5.3') <= 0)
        {
            show_error('Gmaps Fieldtype require PHP 5.3 or higher.', 500, 'Oeps!');
            return FALSE;
        }

        //set the module data
        $mod_data = array(
            'module_name'			=> GMAPS_FT_CLASS,
            'module_version'		=> GMAPS_FT_VERSION,
            'has_cp_backend'		=> "y",
            'has_publish_fields'	=> 'n'
        );

        //insert the module
        ee()->db->insert('modules', $mod_data);

        //register actions
        $this->_register_action('act_route');

        ee()->load->library('gmaps_fieldtype_lib');

        //create the Login backup tables
        $this->_create_tables();

        return TRUE;
    }

    // ----------------------------------------------------------------

    /**
     * Uninstall
     *
     * @return 	boolean 	TRUE
     */
    public function uninstall()
    {
        //delete the module
        ee()->db->where('module_name', GMAPS_FT_CLASS);
        ee()->db->delete('modules');

        //remove actions
        ee()->db->where('class', GMAPS_FT_CLASS);
        ee()->db->delete('actions');

        //remove databases
        ee()->dbforge->drop_table(GMAPS_FT_MAP.'_history');
        ee()->dbforge->drop_table(GMAPS_FT_MAP.'_migration');

        //remove the extension
        ee()->db->where('class', GMAPS_FT_CLASS.'_ext');
        ee()->db->delete('extensions');

        return TRUE;
    }

    // ----------------------------------------------------------------

    /**
     * Module Updater
     *
     * @return 	boolean 	TRUE
     */
    public function update($current = '')
    {
        //nothing to update
        if ($current == '' OR $current == $this->version)
            return FALSE;

        //loop through the updates and install them.
        if(!empty($this->updates))
        {

            foreach ($this->updates as $version)
            {
                //$current = str_replace('.', '', $current);
                //$version = str_replace('.', '', $version);

                if (version_compare($current, $version, '<'))
                    //if ($current < $version)
                {
                    $this->_init_update($version);
                }
            }

            //fix for updating a fieldtype???
            ee()->db->where('name', GMAPS_FT_MAP);
            ee()->db->update('fieldtypes', array(
                'version' => GMAPS_FT_VERSION
            ));

        }

        return true;
    }

    // ----------------------------------------------------------------

    /**
     * Add the tables for the module
     *
     * @return 	boolean 	TRUE
     */
    private function _create_tables()
    {
        //create history table
        ee()->gmaps_fieldtype_lib->create_history_table();

        // add config tables
        $fields = array(
            'migration_id'	=> array(
                'type'			=> 'int',
                'constraint'		=> 7,
                'unsigned'		=> TRUE,
                'null'			=> FALSE,
                'auto_increment'	=> TRUE
            ),
            'site_id'  => array(
                'type'			=> 'int',
                'constraint'		=> 7,
                'unsigned'		=> TRUE,
                'null'			=> FALSE,
                'default'			=> 0
            ),
            'field_id'  => array(
                'type'			=> 'int',
                'constraint'		=> 7,
                'unsigned'		=> TRUE,
                'null'			=> FALSE,
                'default'			=> 0
            ),
            'field_settings'  => array(
                'type' 			=> 'text',
                'null'			=> FALSE
            ),
            'channel_data'  => array(
                'type' 			=> 'text',
                'null'			=> FALSE
            ),
        );

        //create the backup database
        ee()->dbforge->add_field($fields);
        ee()->dbforge->add_key('migration_id', TRUE);
        ee()->dbforge->create_table(GMAPS_FT_MAP.'_migration', TRUE);
    }

    // ----------------------------------------------------------------

    /**
     * Install a hook for the extension
     *
     * @return 	boolean 	TRUE
     */
    private function _register_hook($hook, $method = NULL, $priority = 10)
    {
        if (is_null($method))
        {
            $method = $hook;
        }

        if (ee()->db->where('class', GMAPS_FT_CLASS.'_ext')
                ->where('hook', $hook)
                ->count_all_results('extensions') == 0)
        {
            ee()->db->insert('extensions', array(
                'class'		=> GMAPS_FT_CLASS.'_ext',
                'method'	=> $method,
                'hook'		=> $hook,
                'settings'	=> '',
                'priority'	=> $priority,
                'version'	=> GMAPS_FT_VERSION,
                'enabled'	=> 'y'
            ));
        }
    }

    // ----------------------------------------------------------------

    /**
     * Create a action
     *
     * @return 	boolean 	TRUE
     */
    private function _register_action($method)
    {
        if (ee()->db->where('class', GMAPS_FT_CLASS)
                ->where('method', $method)
                ->count_all_results('actions') == 0)
        {
            ee()->db->insert('actions', array(
                'class' => GMAPS_FT_CLASS,
                'method' => $method
            ));
        }
    }

    // ----------------------------------------------------------------

    /**
     * Run a update from a file
     *
     * @return 	boolean 	TRUE
     */

    private function _init_update($version, $data = '')
    {
        // run the update file
        $class_name = 'gmaps_ft_upd_'.str_replace('.', '', $version);
        require_once(PATH_THIRD.'gmaps_fieldtype/updates/'.strtolower($class_name).'.php');
        $updater = new $class_name($data);
        return $updater->run_update();
    }

}
/* End of file upd.gmaps.php */
/* Location: /system/expressionengine/third_party/gmaps/upd.gmaps.php */