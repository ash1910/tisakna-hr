<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

include_once 'eeharbor.php';

/**
 * Matrix Update
 *
 * @package   Matrix
 * @author    EEHarbor <help@eeharbor.com>
 * @copyright Copyright (c) 2016 EEHarbor
 */
class Matrix_upd
{
    public $version;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->eeharbor = new \matrix\EEHarbor;
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
            'module_name'        => $this->eeharbor->getConfig('name'),
            'module_version'     => $this->eeharbor->getConfig('version'),
            'has_cp_backend'     => 'y',
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
        $module_id = ee()->db->get_where('modules', array('module_name' => 'Matrix'))->row('module_id');

        ee()->db->where('module_id', $module_id);
        ee()->db->delete('module_member_groups');

        ee()->db->where('module_name', 'Matrix');
        ee()->db->delete('modules');

        return true;
    }
}
