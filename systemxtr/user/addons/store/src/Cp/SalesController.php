<?php

/*
 * Exp:resso Store module for ExpressionEngine
 * Copyright (c) 2010-2014 Exp:resso (support@exp-resso.com)
 */

namespace Store\Cp;

use Store\FormBuilder;
use Store\Model\MemberGroup;
use Store\Model\Sale;

class SalesController extends AbstractController
{
    public function __construct($ee)
    {
        parent::__construct($ee);

        $this->addBreadcrumb(store_cp_url('sales'), lang('nav_promotions'));
    }

    public function index()
    {
       // handle form submit
        if ( ! empty($_POST['submit'])) {
            $selected = Sale::where('site_id', config_item('site_id'))->whereIn('id', (array) ee()->input->post('selected'));

            switch (ee()->input->post('with_selected')) {
                case 'enable':
                    $selected->update(array('enabled' => 1));
                    break;
                case 'disable':
                    $selected->update(array('enabled' => 0));
                    break;
                case 'delete':
                    $selected->delete();
                    break;
            }
            //ee()->session->set_flashdata('message_success', lang('store.settings.updated'));
			ee()->session->set_flashdata(array('message_success'=>lang('store.settings.updated')));
			
            ee()->functions->redirect(store_cp_url('sales'));
        }

        // sortable ajax post
        if (!empty($_POST['sortable_ajax'])) {
            return $this->sortableAjax('\Store\Model\Sale');
        }

        $data = array();
        $data['post_url'] = store_cp_url('sales');
        $data['edit_url'] = store_cp_url('sales', 'edit').'&id=';
        $data['sales'] = Sale::where('site_id', config_item('site_id'))->orderBy('sort')->get();

		return array(
		  'body'       => $this->render('sales/index', $data),
		  'breadcrumb' => $this->getBreadcrumbs(),
		  'heading'  => lang('nav_sales')
		);
    }

    public function edit()
    {
        $this->addBreadcrumb(store_cp_url('sales', 'index'), lang('nav_sales'));
		
        $sale_id = ee()->input->get('id');
        if ($sale_id == 'new') {
            $sale = new Sale;
            $sale->site_id = config_item('site_id');
            $sale->enabled = 1;
            $title = lang('store.sale_new');
        }		
		else {
            $sale = Sale::where('site_id', config_item('site_id'))->find($sale_id);

            if (empty($sale)) {
                return $this->show404();
            }

            $title = lang('store.sale_edit');
        }

        // handle form submit
        $sale->fill((array) ee()->input->post('sale'));
			
        //Change validation like below		
		$rules = array(
			array(
					'field' => 'sale[name]',
					'label' => 'Sale Name',
					'rules' => 'required'
			)		);
	
		
		ee()->load->library('form_validation');		
		ee()->form_validation->set_rules($rules);		
       if (ee()->form_validation->run() == TRUE)
        {
            $sale->save();	
            ee()->session->set_flashdata(array('message_success'=>lang('store.settings.updated')));			
            ee()->functions->redirect(store_cp_url('sales'));
        }

        $data = array();
        $data['post_url'] = STORE_CP.'&sc=promotions&sm=edit&id='.$sale_id;
        $data['sale'] = $sale;
        $data['form'] = new FormBuilder($sale);
        $data['category_options'] = ee()->store->products->get_categories();
        $data['product_options'] = ee()->store->products->get_product_titles();

        $member_groups = MemberGroup::all();

        $data['member_groups'] = array();
        foreach ($member_groups as $row) {
            // ignore banned, guests, pending
            if (!in_array($row->group_id, array(2, 4))) {
                $data['member_groups'][$row->group_id] = $row->group_title;
            }
        }
        ee()->cp->add_js_script(array('ui' => 'datepicker'));	
      
		return array(
		  'body'       => $this->render('sales/edit', $data),
		  'breadcrumb' => $this->getBreadcrumbs(),
		  'heading'  => $title
		);
    }

    /**
     * Quick list of limited products for use of searching in multi select
     */
    public function get_product_list()
    {
		$q = $this->ee->input->get_post('q');
		$limit = $this->ee->input->get_post('limit');
		$q = isset($q) ? $q : "";
		$limit = isset($limit) ? $limit : "";
			
		$this->ee->db->select("exp_channel_titles.entry_id, exp_channel_titles.title");
		$this->ee->db->order_by("exp_channel_titles.title", "asc");
		$this->ee->db->like('LOWER(exp_channel_titles.title)', strtolower($q));
		$this->ee->db->join('exp_channel_titles', 'exp_channel_titles.entry_id = exp_store_products.entry_id');
		$this->ee->db->limit($limit);
		$query = $this->ee->db->get('exp_store_products');
			
		return $this->ee->output->send_ajax_response($query->result_array());
    }
    
}
