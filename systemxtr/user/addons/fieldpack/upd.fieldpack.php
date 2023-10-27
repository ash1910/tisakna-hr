<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

include_once 'eeharbor.php';

/**
 * Fieldpack Update
 *
 * @package   Fieldpack
 * @author    EEHarbor <help@eeharbor.com>
 * @copyright Copyright (c) 2016 EEHarbor
 */
class Fieldpack_upd
{
    public $version;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->eeharbor = new \fieldpack\EEHarbor;
        $this->version  = $this->eeharbor->getConfig('version');
    }

    /**
     * Install
     */
    public function install()
    {
        ee()->load->dbforge();

        // -------------------------------------------
        //  Add row to exp_modules
        // -------------------------------------------

        ee()->db->insert('modules', array(
            'module_name'        => "Fieldpack",
            'module_version'     => $this->eeharbor->getConfig('version'),
            'has_cp_backend'     => 'n',
            'has_publish_fields' => 'n',
        ));

        return true;
    }

    /**
     * Update
     */
    public function update($current = '')
    {
        return true;
    }

    /**
     * Uninstall
     */
    public function uninstall()
    {
        ee()->load->dbforge();

        // routine EE table cleanup

        ee()->db->select('module_id');
        $module_id = ee()->db->get_where('modules', array('module_name' => 'Fieldpack'))->row('module_id');

        ee()->db->where('module_id', $module_id);
        ee()->db->delete('module_member_groups');

        ee()->db->where('module_name', 'Fieldpack');
        ee()->db->delete('modules');

        ee()->db->delete('fieldtypes', array('name' => 'fieldpack_checkboxes'));
        ee()->db->delete('fieldtypes', array('name' => 'fieldpack_dropdown'));
        ee()->db->delete('fieldtypes', array('name' => 'fieldpack_list'));
        ee()->db->delete('fieldtypes', array('name' => 'fieldpack_multiselect'));
        ee()->db->delete('fieldtypes', array('name' => 'fieldpack_pill'));
        ee()->db->delete('fieldtypes', array('name' => 'fieldpack_radio_buttons'));
        ee()->db->delete('fieldtypes', array('name' => 'fieldpack_switch'));

        return true;
    }
}
