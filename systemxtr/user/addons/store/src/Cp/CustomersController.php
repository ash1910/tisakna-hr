<?php

/*
 * Exp:resso Store module for ExpressionEngine
 * Copyright (c) 2010-2014 Exp:resso (support@exp-resso.com)
 */

namespace Store\Cp;

use Store\Model\Order;

class CustomersController extends AbstractController
{
    public function index()
    {
		$this->ee->load->library('table');	
		$title= lang('nav_customers');
		$base_url = ee('CP/URL', STORE_CP.'&sc=customers');			
		$table = ee('CP/Table', array('autosort' => TRUE, 'autosearch' => TRUE));	
		$table->setColumns(
			array(
					lang('store.customer_name'),
					lang('store.order_email'),
					lang('store.customer_orders'),
					lang('store.customer_revenue') 
			));
														
		$table->setNoResultsText('<p class="notice">'.lang('no_entries_matching_that_criteria').'</p>', '',$base_url);			
		
		$search = array();
        $search['keywords'] = (string) ee()->input->get_post('keywords');
		$data['search'] = $search;		
		$per_page = $this->ee->input->get_post('per_page') ?: 50;
		$offset = $this->ee->input->get('tbl_offset') ?: 0;
		
		$data['per_page'] = $per_page;
		$currentPage = $this->ee->input->get_post('page')?:1;
		
		$tabdata = $this->_customers_data(array('sort' => array('customer_name' => 'asc')),$offset);			
		$table->setData($tabdata['data']);		
		
		$data['table_html'] = $this->ee->load->view('inventory/table', $table->viewData($base_url), TRUE);	
		
		$data['pagination'] = array(
            'per_page' => $per_page,
            'total_rows' => $tabdata['total'] 
        );
		///////////Adding pgination 
		$config = array(
			'base_url'				=> '',
			'per_page'				=> $per_page,
			'cur_page'				=> $offset,
			'num_links'				=> 2,
			'full_tag_open'			=> '<div class="paginate"><ul>', 
			'full_tag_close'		=> '</ul></div>',
		);
		$config = array_merge($config, $data['pagination']);
		ee()->load->library('pagination');
		ee()->pagination->initialize($config);
		$links = ee()->pagination->create_link_array();		
		if($links){
			foreach ($links as &$section)
			{
				foreach ($section as &$link)
				{
					if (empty($link)) continue;

					$url = clone $base_url;

					$offset = str_replace('/', '', $link['pagination_url']);
					if ( ! empty($offset))
					{
						$url->setQueryStringVariable('tbl_offset', $offset);
						$url->setQueryStringVariable('per_page', $per_page);
						if($search['keywords'])
							$url->setQueryStringVariable('keywords', $search['keywords']);							
					}
					$link['pagination_url'] = $url->compile();
				}
			}
		}
		$data['pagination_links'] = $links ;
		///////////////Ending pagination
        $data['post_url'] = store_cp_url('customers');
        $data['per_page_select_options'] = array('10' => '10 '.lang('results'), '25' => '25 '.lang('results'), '50' => '50 '.lang('results'), '75' => '75 '.lang('results'), '100' => '100 '.lang('results'), '150' => '150 '.lang('results'));
		
		
		return array(		  
			'body'       => $this->render('customers/index', $data),
			'breadcrumb' => $this->getBreadcrumbs(),
			'heading'  => $title
		);
    }
	
	public function _customers_data(array $state,$offset=''){        
		$search = array();
        $search['keywords'] = (string) ee()->input->get_post('keywords');

        // find results
        $query = Order::where('order_completed_date', '>', 0)
            ->groupBy('order_email')
            ->select(array(
                'store_orders.*',
                ee()->store->db->raw('CONCAT_WS(" ", `billing_first_name`, `billing_last_name`) AS `customer_name`'),
                ee()->store->db->raw('COUNT(`id`) AS customer_orders'),
                ee()->store->db->raw('SUM(`order_total`) AS customer_revenue'),
            ));

        if ($search['keywords'] !== '') {
            $query->where(function($query) use ($search) {
                $query->where('store_orders.order_email', 'like', '%'.$search['keywords'].'%')
                    ->orWhere('store_orders.billing_first_name', 'like', '%'.$search['keywords'].'%')
                    ->orWhere('store_orders.billing_last_name', 'like', '%'.$search['keywords'].'%');
            });
        }

        $order_by = key($state['sort']);
        $direction = reset($state['sort']);
        switch ($order_by) {
            default:
                $query->orderBy($order_by, $direction);
        }

        $per_page = ee()->input->get_post('per_page') ?: 50;
		
		$customers = $query->get();
		
		$ret_data['total'] = sizeof($customers);
		
        $customers = $query->take($per_page)
            ->skip($offset)
            ->get();

        // table data
        $data = array();
        foreach ($customers as $customer) {
            $customer_url = store_cp_url('orders', array('keywords' => $customer->order_email));
            $row  = array(
                'customer_name'     => array(
										  'content' => $customer->customer_name,
										  'href' => $customer_url
										),
                'order_email'       => $customer->order_email,
                'customer_orders'   => $customer->customer_orders,
                'customer_revenue'  => store_currency($customer->customer_revenue),
            );
			$data[] = $row;	
        }

		$ret_data['data'] = $data;		
		return $ret_data;        
	}
	
}
