<?php

namespace Store;

use Store\FormValidation;
use Store\Model\Order;
use Store\Model\Product;

class Cp
{
    protected $ee;
    protected $site_id;

    public function __construct($ee = null)
    {
        //$this->ee = $ee ?: ee();
		$this->ee = ee();
    }

    public function index()
    {
        ee()->lang->loadfile('content');
        ee()->lang->loadfile('design');
        ee()->load->library(array('javascript', 'table'));
		
        ee()->load->helper(array('form', 'text', 'search'));
		ee()->cp->set_breadcrumb('link', 'title');	
		
						
        // check site enabled
        if (!config_item('store_site_enabled')) {
            return $this->route('dashboard', 'install');
        }

        // Load MCP CSS+JS
        $this->loadCpAssets();

        // load store css + js
        $this->ee->store->config->load_cp_assets();

        // default global view variables
        $this->ee->load->vars(array(
            'store_table_template' => array(
                'table_open' => '<table class="mainTable store_table">'),
            'store_fixed_table_template' => array(
                'table_open' => '<table class="mainTable store_table store_table_fixed">'),
            'store_sortable_table_template' => array(
                'table_open' => '<table class="mainTable store_table store_table_sortable">'),
        ));

        // simple router
        if ($controller = $this->ee->input->get('sc')) {
            $method = $this->ee->input->get('sm') ?: 'index';

            return $this->route($controller, $method);
        }

        return $this->route('dashboard');
    }

    protected function route($controller, $method = 'index')
    {
        $class = 'Store\Cp\\'.ucfirst(strtolower($controller)).'Controller';

        if (class_exists($class)) {
            $controller = new $class($this->ee);

            if (is_callable(array($controller, $method))) {
                return $controller->$method();
            }
        }

        show_404();
    }

    private function loadCpAssets()
    {
        $site_id = ee()->config->item('site_id');
        $mcpBaseUrl = store_cp_url();
        $themeUrl = ee()->store->config->assetUrl();
        $cookiePrefix = ee()->config->item('cookie_prefix') ? ee()->config->item('cookie_prefix') : 'exp_';

        $mcpBaseUrl = str_replace('&amp;', '&', $mcpBaseUrl);

        $mainJs = "
            var Store = Store ? Store : {};
            Store.SITE_ID = '{$site_id}';
            Store.MCP_BASE_URL = '{$mcpBaseUrl}';
            Store.THEME_URL = '{$themeUrl}';
            Store.COOKIE_PREFIX = '{$cookiePrefix}';
        ";

        $this->ee->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->ee->store->config->assetUrl('fonts/font-awesome.min.css').'">');
        $this->ee->cp->add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->ee->store->config->assetUrl('css/mcp.css').'">');	

        $this->ee->cp->add_to_foot('<script type="text/javascript">' . $mainJs . '</script>');
        
		$this->ee->cp->add_to_foot('<script type="text/javascript" src="'.$this->ee->store->config->assetUrl('js/vendor.min.js').'"></script>');
		
        $this->ee->cp->add_to_foot('<script type="text/javascript" src="'.$this->ee->store->config->assetUrl('js/mcp.min.js').'"></script>');
    }
	
	public function _customers_index_data($state='', $data=''){        
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
        $customers = $query->take($per_page)
            ->skip($state['offset'])
            ->get();

        // table headings
        ee()->table->set_columns(array(
            'customer_name'     => array('header' => lang('store.customer_name')),
            'order_email'       => array('header' => lang('store.order_email')),
            'customer_orders'   => array('header' => lang('store.customer_orders')),
            'customer_revenue'  => array('header' => lang('store.customer_revenue')),
        ));

        // table data
        $data['rows'] = array();
        foreach ($customers as $customer) {
            $customer_url = store_cp_url('orders', array('keywords' => $customer->order_email));
            $data['rows'][] = array(
                'customer_name'     => '<a href="'.$customer_url.'">'.$customer->customer_name.'</a>',
                'order_email'       => $customer->order_email,
                'customer_orders'   => array('data' => $customer->customer_orders, 'class' => 'store_numeric'),
                'customer_revenue'  => array('data' => store_currency($customer->customer_revenue), 'class' => 'store_numeric'),
            );
        }

        $data['no_results'] = '<p class="notice">'.lang('no_entries_matching_that_criteria').'</p>';
        $data['search'] = $search;
        $data['pagination'] = array(
            'per_page' => $per_page,
            'total_rows' => Order::distinct()->where('order_completed_date', '>', 0)->count('order_email'),
        );		
        return $data;
	}
	
	public function _inventory_index_data($state, $data)
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
        $products = $query->take($per_page)
            ->skip($state['offset'])
            ->get();

        // table headings
        $this->ee->table->set_columns(array(
            'id'            => array('header' => array('data' => lang('store.#'), 'width' => '2%')),
            'title'         => array('header' => lang('title')),
            'total_stock'   => array('header' => lang('store.total_stock')),
            'price'         => array('header' => lang('store.price')),
            'options'       => array('sort' => false),
        ));

        // table data
        $data['rows'] = array();
        foreach ($products as $product) {
            $data['rows'][] = array(
                'id'            => $product->entry_id,
                'title'         => $product->title,
                'total_stock'   => $product->total_stock,
                'price'         => array('data' => store_currency($product->regular_price), 'class' => 'currency'),
                'options'       => '<a href="'.BASE.'cp/publish/edit/entry/'.$product->entry_id.'">'.lang('edit_entry').'</a>',
            );
        }

        $data['no_results'] = '<p class="notice">'.lang('no_entries_matching_that_criteria').'</p>';
        $data['search'] = $search;
        $data['pagination'] = array(
            'per_page' => $per_page,
            'total_rows' => Product::count(),
        );

        return $data;
    }
}
