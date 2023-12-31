<?php

namespace Store\Cp;

abstract class AbstractController
{
    protected $ee;
    protected $db;
    protected $breadcrumbs = array();
    protected $globalData = array();

    public function __construct($ee)
    {
        //$this->ee = $ee;
		$this->ee = ee();
        $this->db = $ee->store->db;
        $this->addBreadcrumb(store_cp_url(), lang('store_module_name'));
    }

    protected function render($view, array $data, $selected_tab='', $title='Store Settings')
    {
        // Merge our local view data with our global data
       
		$data = array_merge($this->globalData, $data);

        $vdata = array();
        $vdata['section'] = ee()->input->get('sc');
        //$vdata['content'] = ee()->cp->render($view, $data);		
		$vdata['content'] = $this->ee->load->view($view, $data, TRUE);
        
		return $this->ee->load->view('_layout', $vdata, TRUE);
		//ee('View')->make('store/_layout')->render($vdata);
    }

    protected function requirePrivilege($privilege)
    {
        if (!$this->ee->store->config->has_privilege($privilege)) {
            show_error(lang('store.no_access'));
        }
    }

    protected function show404()
    {
        show_404();
    }

    protected function sortableAjax($class, $with_site_id = true)
    {
        $sort = 0;
        foreach ((array) $this->ee->input->post('sorted_ids') as $id) {
            $query = new $class;
            if ($with_site_id) {
                $query = $query->where('site_id', config_item('site_id'));
            }
            $query->where('id', $id)->update(array('sort' => $sort++));
        }

        return $this->ee->output->send_ajax_response(array(
            'type'      => 'success',
            'message'   => lang('store.settings.updated'),
        ));
    }

    /**
     * Set the CP page title
     */
    protected function setTitle($title)
    {
       $this->setVariable('cp_page_title', 'Any title');
    }

    /**
     * We use our own breadcrumb function to override the useless "Modules" crumb added by
     * the modules controller.
     */
     protected function addBreadcrumb($link, $title)
    {
        $key = $link->__toString();
		$this->breadcrumbs[$key] = $title;
        $this->setVariable('cp_breadcrumbs', $this->breadcrumbs);		
    }
	public function getBreadcrumbs(){
		return $this->breadcrumbs;
	}
    /**
     * Backwards compatible view variable setter
     */
    protected function setVariable($key, $value)
    {
        $this->ee->view->$key = $value;			
    }
}
