<?php

/*
 * Exp:resso Store module for ExpressionEngine
 * Copyright (c) 2010-2014 Exp:resso (support@exp-resso.com)
 */

namespace Store\Cp;

use Store\FormBuilder;
use Store\Model\MemberGroup;
use Store\Model\Discount;

class DiscountsController extends AbstractController
{
    public function index()
    {
        $title = lang('nav_discounts');
		$this->addBreadcrumb(store_cp_url('discounts'), lang('nav_promotions'));       	
        // handle form submit
        if ( ! empty($_POST['submit'])) {
            $selected = Discount::where('site_id', config_item('site_id'))->whereIn('id', (array) $this->ee->input->post('selected'));
			
            switch ($this->ee->input->post('with_selected')) {
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
			
            $this->ee->session->set_flashdata(array('message_success'=>lang('store.settings.updated')));
            $this->ee->functions->redirect(store_cp_url('discounts'));
        }
		
        // sortable ajax post
        if (!empty($_POST['sortable_ajax'])) {
            return $this->sortableAjax('\Store\Model\Discount');
        }

        $data = array();
        $data['post_url'] = store_cp_url().'&sc=discounts';
        $data['edit_url'] = store_cp_url('discounts', 'edit').'&id=';
        $data['discounts'] = Discount::where('site_id', config_item('site_id'))->orderBy('sort')->get();
		
		return array(		  
			'body'       => $this->render('discounts/index', $data),
			'breadcrumb' => $this->getBreadcrumbs(),
			'heading'  => $title
		);
    }

    public function edit()
    {
        $this->addBreadcrumb(store_cp_url('discounts'), lang('nav_discounts'));

        $discount_id = $this->ee->input->get('id');
        
		if ($discount_id == 'new') {
            $discount = new Discount;
            $discount->site_id = config_item('site_id');
            $discount->enabled = 1;
            $discount->break = 1;
            $this->setTitle(lang('store.discount_new'));
        } 
		else {
            $discount = Discount::where('site_id', config_item('site_id'))->find($discount_id);

            if (empty($discount)) {
                return $this->show404();
            }
            $this->setTitle(lang('store.discount_edit'));
        }
		// handle form submit
        $discount->fill((array) $this->ee->input->post('discount'));
		
		$rules = array(
        array(
                'field' => 'discount[name]',
                'label' => 'name',
                'rules' => 'required'
        ));
       	$this->ee->load->library('form_validation');		
		$this->ee->form_validation->set_rules($rules);
	
		
       if ($this->ee->form_validation->run() == TRUE)
        {
			$discount->save();
					
            ee()->session->set_flashdata('message_success', lang('store.settings.updated'));
            ee()->functions->redirect(store_cp_url('discounts'));
        }
		

        $data = array();
        $data['post_url'] = STORE_CP.'&sc=discounts&sm=edit&id='.$discount_id;
        $data['discount'] = $discount;
        $data['form'] = new FormBuilder($discount);
        $data['category_options'] = ee()->store->products->get_categories();
        $data['product_options'] =  ee()->store->products->get_product_titles();

        $member_groups = MemberGroup::all();

        $data['member_groups'] = array();
        foreach ($member_groups as $row) {
            // ignore banned, guests, pending
            if (!in_array($row->group_id, array(2, 4))) {
                $data['member_groups'][$row->group_id] = $row->group_title;
            }
        }

        ee()->cp->add_js_script(array('ui' => 'datepicker'));

        return $this->render('discounts/edit', $data);
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
