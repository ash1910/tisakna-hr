<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

include_once 'eeharbor.php';
require_once PATH_THIRD . 'matrix/config.php';

/**
 * Matrix MCP Class for EE2 & EE3
 *
 * @package      Matrix
 * @author       Tom Jaeger <Tom@EEHarbor.com>
 * @copyright    Copyright (c) 2016, Tom Jaeger/EEHarbor
 */
class Matrix_mcp
{
    public $version = MATRIX_VER;

    public function __construct()
    {
        $this->eeharbor = new matrix\EEHarbor;
    }

    public function index()
    {
        // // load the language file
        ee()->lang->loadfile('matrix');

        $vars['action_url']              = $this->eeharbor->moduleURL('save_settings');
        $vars['settings']['license_key'] = $this->eeharbor->getLicenseKey();

        ee()->load->library('table');

        return ee()->load->view('mcp/settings', $vars, true);
    }

    public function settings()
    {
        if (ee()->addons_model->module_installed('matrix')) {
            ee()->functions->redirect($this->eeharbor->moduleURL('index'));
        } else {
            ee()->lang->loadfile('matrix');
            $this->eeharbor->flashData('message_failure', lang('no_module'));
            ee()->functions->redirect($this->eeharbor->cpURL('addons'));
        }
    }

    public function save_settings()
    {
        if (ee()->session->userdata['group_id'] != 1) {
            $this->_forbidden();
        }

        $settings = ee()->input->post('settings');

        $data['settings'] = base64_encode(serialize($settings));

        ee()->db->where('name', 'matrix')
            ->update('fieldtypes', $data);

        // redirect to Index
        $this->eeharbor->flashData('message_success', lang('global_settings_saved'));
        ee()->functions->redirect($this->eeharbor->moduleURL('index'));
    }

    /**
     * Handle access restriction
     */
    private function _forbidden()
    {
        header('HTTP/1.1 403 Forbidden');
        exit();
    }
}
