<?php namespace Store\Cp;
	/*
	 * Exp:resso Store module for ExpressionEngine
	 * Copyright (c) 2010-2014 Exp:resso (support@exp-resso.com)
	 */
	use Store\Model\Product;

	class InventoryController extends AbstractController
	{
		public function index()
		{
			$title = lang('nav_inventory');
			$this->requirePrivilege('can_access_inventory');			
			$base_url = ee('CP/URL', STORE_CP.'&sc=inventory');	
			
			$per_page = $this->ee->input->get_post('per_page') ?: 50;			
			$offset = $this->ee->input->get('tbl_offset') ?: 0;
			
			$data['post_url'] = store_cp_url('inventory');			
			$data['category_options'] = array('' => lang('store.any')) +
				ee()->store->products->get_categories();
				
			$data['per_page_select_options'] = array('10' => '10 '.lang('results'), '25' => '25 '.lang('results'), '50' => '50 '.lang('results'), '75' => '75 '.lang('results'), '100' => '100 '.lang('results'), '150' => '150 '.lang('results'));
			$search = array();
			$search['category_id'] = $this->ee->input->get_post('category_id');
			$search['keywords'] = (string) $this->ee->input->get_post('keywords');
			$data['search'] = $search;
			
			$table = ee('CP/Table', array('autosort' => TRUE, 'autosearch' => TRUE));	
			$table->setColumns(
				array(
					lang('store.#'),lang('title'), lang('store.total_stock'),lang('store.price'),'options'
				));
															
			$table->setNoResultsText('<p class="notice">'.lang('no_entries_matching_that_criteria').'</p>', '',$base_url);
			
			$tabdata = $this->_inventory_data(array('sort' => array('title' => 'asc')),$offset);			
			$table->setData($tabdata['data']);		
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
			
			$data['table_html'] = $this->ee->load->view('inventory/table', $table->viewData($base_url), TRUE);
			 return array(
			  'body'       => $this->render('inventory/index', $data),
			  'breadcrumb' => $this->getBreadcrumbs(),
			  'heading'  => $title,
			);
		}
		
		public function _inventory_data($state,$offset)
		{
			$search = array();
			$search['category_id'] = $this->ee->input->get_post('category_id');
			$search['keywords'] = (string) $this->ee->input->get_post('keywords');

			// find results
			$query = Product::join('channel_titles', 'channel_titles.entry_id', '=', 'store_products.entry_id')
				->join('store_stock', 'store_stock.entry_id', '=', 'store_products.entry_id')
				->select(array('store_products.*', 'channel_titles.title', $this->ee->store->db->raw('SUM(`stock_level`) AS `total_stock`')))
				->where('channel_titles.site_id', '=', config_item('site_id'))
				->groupBy('store_products.entry_id');

			if ($search['category_id']) {
				$query->join('category_posts', 'category_posts.entry_id', '=', 'channel_titles.entry_id')
					->where('category_posts.cat_id', '=', $search['category_id']);
			}

			if ($search['keywords'] !== '') {
				$query->where(function($query) use ($search) {
					$query->where('channel_titles.title', 'like', '%'.$search['keywords'].'%')
						->orWhere('channel_titles.entry_id', $search['keywords']);
				});
			}

			$order_by = key($state['sort']);
			$direction = reset($state['sort']);
			switch ($order_by) {
				case 'id':
					$query->orderBy('store_products.entry_id', $direction);
					break;
				case 'price':
					$query->orderBy('price', $direction);
					break;
				default:
					$query->orderBy($order_by, $direction);
			}
			$per_page = $this->ee->input->get_post('per_page') ?: 50;
			$products = $query->get();
			$ret_data['total'] = sizeof($products);			
			$products = $query->take($per_page)
				->skip($offset)
				->get();
			
			$data = array();
			foreach ($products as $product) {
				$row = array(
					'id'            => $product->entry_id,
					'title'         => $product->title,
					'total_stock'   => $product->total_stock,
					'price'         => store_currency($product->regular_price),
					'options'       =>  array(
										  'content' => lang('edit_entry'),
										  'href' => ee('CP/URL')->make('publish/edit/entry/' . $product->entry_id)
										),				
					);
				$data[] = $row;
			}	
				
			$ret_data['data'] = $data;
			
			return $ret_data;
		}
	
	}
