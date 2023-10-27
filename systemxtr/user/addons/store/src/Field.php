<?php

/*
 * Exp:resso Store module for ExpressionEngine
 * Copyright (c) 2010-2014 Exp:resso (support@exp-resso.com)
 */

namespace Store;

use EE_Fieldtype;
use Store\Model\Product;
use Store\Model\Stock;

class Field extends EE_Fieldtype
{
    public $info = array(
        'name' => 'Store Product Details',
        'version' => STORE_VERSION
    );

    public $has_array_data = true;

    /**
     * Display field on the publish tab
     */
    public function display_field($field_data)
    {
        foreach (array('length_with_units', 'width_with_units', 'height_with_units') as $key) {
            ee()->lang->language['store.'.$key] = sprintf(
                lang('store.'.$key), config_item('store_dimension_units'));
        }
        ee()->lang->language['store.weight_with_units'] = sprintf(
            lang('store.weight_with_units'), config_item('store_weight_units'));

        ee()->load->library('table');

        $data = array();
        $data['field_name'] = $this->field_name;
        $data['field_required'] = $this->is_required();

       //$product = $this->find_or_create_product(ee()->input->get('entry_id'));
	   $entry_id = ee()->uri->segment(5)?(int)ee()->uri->segment(5):0;
	   $product = $this->find_or_create_product($entry_id);
	   
        $data['product'] = $product;

        $post_data = ee()->input->post('store_product_field', true);
        if ($post_data) {
            $product->fill((array) $post_data);
        }

        $data['modifiers'] = isset($post_data['modifiers']) ? $post_data['modifiers'] : $product->getModifiersArray();

        // load store css + js
        ee()->store->config->load_cp_assets();
        ee()->cp->add_to_foot('
            <script type="text/javascript">
            ExpressoStore.productStock = '.$product->stock->toJSON().';
			$("#store_product_field").parent(".setting-field").removeClass("w-8");	
            $("#store_product_field").parent(".setting-field").addClass("w-16");  
            </script>');
        ee()->cp->add_js_script(array(
            'ui' => array('datepicker', 'sortable'),
            'file' => array('underscore'),
        ));
	
        return ee()->load->view('field', $data, true);
    }

    protected function find_or_create_product($entry_id)
    {
        $entry_id = (int) $entry_id;
        $product = Product::with(array(
            'modifiers' => function($query) { $query->orderBy('mod_order'); },
            'modifiers.options' => function($query) { $query->orderBy('opt_order'); },
            'stock',
            'stock.stockOptions',
        ))->find($entry_id);

        if (!$product) {
            $product = new Product;
            $product->entry_id = $entry_id;
        }

        return $product;
    }

    /**
     * Prep the data for saving
     *
     * Cache product SKUs inside our custom field, so that it can be found by EE search tags.
     * We never actually use the data stored in the custom field, it is purely here for search.
     */
    public function save($data)
    {
        $field_data = ee()->input->post('store_product_field', true);
        $skus = array('[store]');

        if (!empty($field_data['stock'])) {
            foreach ($field_data['stock'] as $stock) {
                $skus[] = $stock['sku'];
            }
        }

        return implode(' ', $skus);
    }

    /**
     * Runs after an entry has been saved
     */
    public function post_save($data)
    {
        $this->post_save_settings($data);
		$product = $this->find_or_create_product($this->settings['entry_id']);
 
		$product->fill((array) ee()->input->post('store_product_field', true));
		     
		ee()->store->products->save_product($product);
    }
	
	public function post_save_settings($data)
    {    
		$settings = ee()->input->post('store', true);
		$settings['entry_id'] = $this->get_entry_id(ee()->input->post('url_title', true),ee()->input->post('channel_id', true));
        $settings['field_fmt'] = 'none';
        $settings['field_show_fmt'] = 'n';
        $settings['field_type'] = 'store';
		$this->settings = $settings;
        //return $settings;
    }
	
    public function delete($entry_ids)
    {
        ee()->store->products->delete_all($entry_ids);
    }

    public function validate($data)
    {
        $entry_id = (int) ee()->input->post('entry_id');
        $field_data = ee()->input->post('store_product_field');
        $error = false;

        if ($this->is_required() && !$this->run_validation("store_product_field[price]", 'lang:store.price', 'required')) {
            $error = true;
        }

        // require names for any modifiers which haven't been removed
		//This validation is not working in Store's version for EE2
        /*if (isset($field_data['modifiers'])) {
            foreach ($field_data['modifiers'] as $mod_id => $modifier) {
                if (isset($modifier['mod_type'])) {
                    if(!$this->run_validation("store_product_field[modifiers][{$mod_id}][mod_name]", 'lang:name', 'required')) 
					{
                        $error = true;
                    }
                }
            }
        }*/

        return $error;
    }

    protected function is_required()
    {
        return 'y' === $this->settings['field_required'];
    }

    /**
     * Immediately run validation rules
     */
    protected function run_validation($field, $label = '', $rules = '')
    {
        // set up validation rules
       /* $result = ee('Validation')->make($rules)->validate($field);
		$validation = ee()->form_validation;
		
        $validation->set_rules($field, $label, $rules);

        // inject post data into validation library
        $row =& $validation->_field_data[$field];
        $row['postdata'] = $validation->_reduce_array($_POST, $row['keys']);

        // run new validation rules
        $validation->_execute($row, explode('|', $row['rules']), $row['postdata']);
		
		
        return empty($row['error']);
		return $result;*/
		
    }

    /**
     * Allow {product_details} to be used as a tag pair
     */
    public function replace_tag($data, $params = array(), $tagdata = false)
    {
        if ($tagdata) {
            return ee()->TMPL->parse_variables($tagdata, array($data));
        }
    }

    /**
     * EE bug: replace_tag_catchall doesn't seem to work with conditionals
     * e.g. {if product_details:on_sale}
     */
    public function replace_on_sale($data)
    {
        if (isset($data['on_sale'])) {
            return $data['on_sale'];
        }
    }

    public function replace_tag_catchall($data, $params = array(), $tagdata = false, $modifier)
    {
        if (isset($data[$modifier])) {
            return $data[$modifier];
        }
    }

    public function load_settings($data)
    {
       
		$settings = array(
			'field_options_store' => array(
				'label' => 'field_options',
				'group' => 'store',
				'settings' => array(
					array(
						'title' => lang('store.enable_custom_prices', 'enable_custom_prices'),
						'desc' => lang('store.enable_custom_prices_subtext').'<i>'.lang('store.enable_custom_prices_warning').'</i>',
						'fields' => array(
							'store[enable_custom_prices]' => array(
								'type' => 'radio',
								'choices' => array('1'=> lang('yes', 'enable_custom_prices_y'), '0' => lang('no', 'enable_custom_prices_n')),
								'value' => isset($data['enable_custom_prices'])?$data['enable_custom_prices']:''
							)
						)
					),
					array(
						'title' =>  lang('store.enable_custom_weights', 'enable_custom_weights'),
						'desc' => lang('store.enable_custom_weights_subtext'),
						'fields' => array(
							'store[enable_custom_weights]' => array(
								'type' => 'radio',
								'choices' => array('1'=>lang('yes', 'enable_custom_weights_y'),'0'=>lang('no', 'enable_custom_weights_n')),
								'value' => isset($data['enable_custom_weights'])?$data['enable_custom_weights']:''
							)
						)
					)
				)
			) );
        return $settings;
    }

    /**
     * Display Settings Screen
     *
     * @access	public
     * @return default global settings
     *
     */
    public function display_settings($data)
    {
       ee()->lang->loadfile('fieldtypes');		
		return $this->load_settings($data);
    }

    /**
     * Save Settings
     *
     * @access	public
     * @return field settings
     *
     */
    public function save_settings($data)
    {
        $settings = ee()->input->post('store', true);

        $settings['field_fmt'] = 'none';
        $settings['field_show_fmt'] = 'n';
        $settings['field_type'] = 'store';

        return $settings;
    }
	function get_entry_id($url_title,$channel_id){
		$entries = 0;
		if($url_title!=''){
			$entries = ee('Model')->get('ChannelEntry')
					 ->filter('channel_id', $channel_id) 
					 ->filter('url_title', $url_title)				
					 ->fields('entry_id')
					 ->all()->first()->entry_id;
		}
			
		return $entries;
	}
    /**
     * Support Entry API v3
     */
    public function entry_api_pre_process($data = null, $free_access = false, $entry_id = 0)
    {
        $product = Product::with(array(
            'modifiers' => function($query) { $query->orderBy('mod_order'); },
            'modifiers.options' => function($query) { $query->orderBy('opt_order'); },
            'stock',
        ))->whereNotNull('price')
        ->find($entry_id);

        if (empty($product)) return;

        ee()->store->products->apply_sales($product);

        // ew, gross
        ee()->load->helper('form');

        return $product->toTagArray();
    }

    /**
	*	===================================
	*	function zenbu_field_extra_settings
	*	===================================
	*	Set up display for this fieldtype in "display settings"
	*/
	function zenbu_field_extra_settings($table_col, $channel_id, $extra_options)
	{
		$store_price = (isset($extra_options['store_price'])) ? TRUE : FALSE;
		$store_handling = (isset($extra_options['store_handling'])) ? TRUE : FALSE;
		$store_sku = (isset($extra_options['store_sku'])) ? TRUE : FALSE;
		$store_width = (isset($extra_options['store_width'])) ? TRUE : FALSE;
		$store_length = (isset($extra_options['store_length'])) ? TRUE : FALSE;
		$store_height = (isset($extra_options['store_height'])) ? TRUE : FALSE;
		$store_weight = (isset($extra_options['store_weight'])) ? TRUE : FALSE;

		$output['store_price'] = form_label(form_checkbox('settings['.$channel_id.']['.$table_col.'][store_price]', 'y', $store_price).'&nbsp;'.ee()->lang->line('zenbu_show_price').'<br />');

		$output['store_handling'] = form_label(form_checkbox('settings['.$channel_id.']['.$table_col.'][store_handling]', 'y', $store_handling).'&nbsp;'.ee()->lang->line('zenbu_show_handling').'<br />');

		$output['store_sku'] = form_label(form_checkbox('settings['.$channel_id.']['.$table_col.'][store_sku]', 'y', $store_sku).'&nbsp;'.ee()->lang->line('zenbu_show_sku').'<br />');

		$output['store_width'] = form_label(form_checkbox('settings['.$channel_id.']['.$table_col.'][store_width]', 'y', $store_width).'&nbsp;'.ee()->lang->line('zenbu_show_width').'<br />');

		$output['store_height'] = form_label(form_checkbox('settings['.$channel_id.']['.$table_col.'][store_height]', 'y', $store_height).'&nbsp;'.ee()->lang->line('zenbu_show_width').'<br />');

		$output['store_length'] = form_label(form_checkbox('settings['.$channel_id.']['.$table_col.'][store_length]', 'y', $store_length).'&nbsp;'.ee()->lang->line('zenbu_show_length').'<br />');

		$output['store_weight'] = form_label(form_checkbox('settings['.$channel_id.']['.$table_col.'][store_weight]', 'y', $store_weight).'&nbsp;'.ee()->lang->line('zenbu_show_weight'));

		return $output;
	}

	/**
	*	=============================
	*	function zenbu_get_table_data
	*	=============================
	*	Retrieve data stored in other database tables
	*	based on results from Zenbu's entry list
	*	@uses	Instead of many small queries, this function can be used to carry out
	*			a single query of data to be later processed by the zenbu_display() method
	*
	*	@param	$entry_ids				array	An array of entry IDs from Zenbu's entry listing results
	*	@param	$field_ids				array	An array of field IDs tied to/associated with result entries
	*	@param	$channel_id				int		The ID of the channel in which Zenbu searched entries (0 = "All channels")
	*	@param	$output_upload_prefs	array	An array of upload preferences
	*	@param	$settings				array	The settings array, containing saved field order, display, extra options etc settings (optional)
	*	@param	$rel_array				array	A simple array useful when using related entry-type fields (optional)
	*	@return	$output					array	An array of data (typically broken down by entry_id then field_id) that can be used and processed by the zenbu_display() method
	*/
	function zenbu_get_table_data($entry_ids, $field_ids, $channel_id)
	{
		$output = array();
		if( empty($entry_ids) || empty($field_ids))
		{
			return $output;
		}

		$product = Product::with(array(
            'stock',
        ))->find($entry_ids)->toTagArray();

        if (empty($product)) return;

		foreach($product as $p)
		{
			$pArray[$p['entry_id']] = $p;
		}

		ee()->session->set_cache('expresso_store', 'product_ids', $pArray);
	}

	/**
	*	======================
	*	function zenbu_display
	*	======================
	*	Set up display in entry result cell
	*	@return	$output		The HTML used to display data
	*/
	function zenbu_display($entry_id, $channel_id, $data, $grid_data = array(), $field_id, $settings, $rules = array(), $upload_prefs = array(), $installed_addons)
	{

		$output = NBS;

		$pArray = ee()->session->cache('expresso_store','product_ids');

		// $s = $settings['setting'][$channel_id]['extra_options']['field_'.$field_id];
        $s = (array) $settings;

		if (empty($s))
			return $output;

		 ee()->store->config->load_cp_assets();

		$store_price = (isset($s['store_price'])) ? $s['store_price'] : FALSE;
		$store_handling = (isset($s['store_handling'])) ? $s['store_handling'] : FALSE;
		$store_sku = (isset($s['store_sku'])) ? $s['store_sku'] : FALSE;
		$store_length = (isset($s['store_length'])) ? $s['store_length'] : FALSE;
		$store_height = (isset($s['store_height'])) ? $s['store_height'] : FALSE;
		$store_width = (isset($s['store_width'])) ? $s['store_width'] : FALSE;
		$store_weight = (isset($s['store_weight'])) ? $s['store_weight'] : FALSE;

		if( isset($store_price) || isset($store_handling) || isset($store_sku))
		{
			$div = "<table class='store_ft'><tr>";

			if ($store_price)
				$div .=  "<td><strong>" . ee()->lang->line('store.item_price') . "</strong></td><td class='store_ft_text'>" . (($pArray[$entry_id]['price']!=="") ? $pArray[$entry_id]['price'] : "-") . "</td></tr>";

			if ($store_handling)
				$div .=  "<td><strong>" . ee()->lang->line('store.handling') . "</strong></td><td class='store_ft_text'>" . (($pArray[$entry_id]['handling']!=="") ? $pArray[$entry_id]['handling'] : "-") . "</td></tr>";

			if ($store_sku)
				$div .=  "<td><strong>" . ee()->lang->line('store.sku') . "</strong></td><td class='store_ft_text'>" . (($pArray[$entry_id]['sku']!=="") ? $pArray[$entry_id]['sku'] : "-") . "</td></tr>";

			if ($store_width)
				$div .=  "<td><strong>" . sprintf(lang('store.width_with_units'), config_item('store_dimension_units')) . "</strong></td><td class='store_ft_text'>" . (($pArray[$entry_id]['width']!="") ? $pArray[$entry_id]['width'] : "-") . "</td></tr>";

			if ($store_height)
				$div .=  "<td><strong>" . sprintf(lang('store.height_with_units'), config_item('store_dimension_units')) . "</strong></td><td class='store_ft_text'>" . (($pArray[$entry_id]['height']!="") ? $pArray[$entry_id]['height'] : "-") . "</td></tr>";

			if ($store_length)
				$div .=  "<td><strong>" . sprintf(lang('store.length_with_units'), config_item('store_dimension_units')) . "</strong></td><td class='store_ft_text'>" . (($pArray[$entry_id]['length']!="") ? $pArray[$entry_id]['length'] : "-") . "</td></tr>";

			if ($store_weight)
				$div .=  "<td><strong>" . sprintf(lang('store.weight_with_units'), config_item('store_weight_units')) . "</strong></td><td class='store_ft_text'>" . (($pArray[$entry_id]['weight']!="") ? $pArray[$entry_id]['weight'] : "-") . "</td></tr>";

				$div .= "</table>";

			return $div;
		} else {
			// ..else return the table as-is, and see it displayed directly in the row
			return $output;
		}
	}

}
