<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Store_recalculate_price Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		WMD.hr
 * @link		
 */

class Store_recalculate_price_ext {
	
	public $settings 		= array();
	public $description		= 'store recalculate price from code';
	public $docs_url		= '';
	public $name			= 'Store :: recalculate price';
	public $version			= '1.1';
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->EE = ee();
		$this->settings = $settings;
	}
		
	// ----------------------------------------------------------------------
	
	/**
	 * Settings Form
	 *
	 * If you wish for ExpressionEngine to automatically create your settings
	 * page, work in this method.  If you wish to have fine-grained control
	 * over your form, use the settings_form() and save_settings() methods 
	 * instead, and delete this one.
	 *
	 * @see http://expressionengine.com/user_guide/development/extensions.html#settings
	 */
		
	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array();
		
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'update_item',
			'hook'		=> 'store_order_item_recalculate_end',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		$this->EE->db->insert('extensions', $data);		
		
	}

	/**
	 * sending_email
	 *
	 * @param 
	 * @return 
	 */
	function update_item($order, $item)
	{

		if ($this->EE->extensions->last_call)
		{
			$order = $this->EE->extensions->last_call;
		}
		//echo "<pre>";print_r($this->settings);
		//echo "<pre>";print_r($item);exit;
		if(!empty($item->sku)){
			$price_from_sku = explode("-", $item->sku);
			if( !empty(@$price_from_sku) && !empty(@$price_from_sku[1]) ){
				$price_new = (float)$price_from_sku[1];
				if($price_new > 0){
					$item->price = $price_new;
					$item->item_total = $item->item_subtotal = $item->price * $item->item_qty;
					$item->save();
				}
			}
		}

		return $order;

		$percent_discount = isset($order->discount->percent_discount) ? $order->discount->percent_discount : 0;

		if(!empty($percent_discount)){
			foreach($order->items as $item) {

				$item_discount = $item->item_subtotal * $percent_discount * .01;
				$item->item_discount = $item_discount;
				$item->item_total = $item->item_subtotal - $item->item_discount;
				$item->save();
			}
        }

        // if(empty($order->member_id) || ($order->member_id != 8)){
        //     return $order;
        // }
        // if ( ee()->session->userdata('group_id') != 6 ){
        //     return $order;
        // }
        
        // apply cheapest product discoount type and discount 2
        $total_items = count($order->items);
        $order_qty = 0;
        $item_qtys = array();
        $item_discounts = array();
        $item_prices = array();
        $item_entries = array();
        foreach($order->items as $item) {
            $item_discounts[$item->id] = 0;
            $item_prices[$item->id] = $item->price;
            $item_qtys[$item->id] = $item->item_qty;
            $order_qty += $item->item_qty;
        }
        asort($item_prices);
        $item_entries_let = array_keys($item_prices);

        foreach ($item_entries_let as $id) {
            for($i = 0; $i < $item_qtys[$id]; $i++){
                $item_entries[] = $id;
            }
        }
        //echo "<pre>";print_r($this->settings);
		//echo "<pre>";print_r($order);exit;
        //echo "<pre>";print_r($item_entries);print_r($item_prices);print_r($item_discounts);exit;
        
        if(isset($this->settings["cheapest_prod_dis_act"]) && ($this->settings["cheapest_prod_dis_act"] == 1)){ //check active

            if(isset($this->settings["cheapest_prod_min"]) && ( $order_qty >= (int)$this->settings["cheapest_prod_min"])){ // check total product

                $total_discount = 0;

                $cheapest_prod_dis_per1 = "cheapest_prod_dis_per3";
                $cheapest_prod_dis_per2 = "cheapest_prod_dis_per2";
                $cheapest_prod_dis_per3 = "cheapest_prod_dis_per1";

                // switch (count($item_entries)) {
                //     case 1:
                //         $cheapest_prod_dis_per1 = "cheapest_prod_dis_per1";
                //         break;
                //     case 2:
                //         $cheapest_prod_dis_per1 = "cheapest_prod_dis_per2";
                //         $cheapest_prod_dis_per2 = "cheapest_prod_dis_per1";
                //         break;
                //     default:
                //         $cheapest_prod_dis_per1 = "cheapest_prod_dis_per3";
                //         $cheapest_prod_dis_per2 = "cheapest_prod_dis_per2";
                //         $cheapest_prod_dis_per3 = "cheapest_prod_dis_per1";
                // }
                
                if( isset($item_entries[0]) && isset($this->settings[$cheapest_prod_dis_per1]) && ( (float)$this->settings[$cheapest_prod_dis_per1] > 0) && ((float)$this->settings[$cheapest_prod_dis_per1] <= 100) ){ //check first cheapest product discoount
                    $item_discounts[$item_entries[0]] += $item_prices[$item_entries[0]] * (float)$this->settings[$cheapest_prod_dis_per1] * .01;
                }
                if( isset($item_entries[1]) && isset($this->settings[$cheapest_prod_dis_per2]) && ( (float)$this->settings[$cheapest_prod_dis_per2] > 0) && ((float)$this->settings[$cheapest_prod_dis_per2] <= 100) ){
                    $item_discounts[$item_entries[1]] += $item_prices[$item_entries[1]] * (float)$this->settings[$cheapest_prod_dis_per2] * .01;
                }
                if( isset($item_entries[2]) && isset($this->settings[$cheapest_prod_dis_per3]) && ( (float)$this->settings[$cheapest_prod_dis_per3] > 0) && ((float)$this->settings[$cheapest_prod_dis_per3] <= 100) ){
                    $item_discounts[$item_entries[2]] += $item_prices[$item_entries[2]] * (float)$this->settings[$cheapest_prod_dis_per3] * .01;
                }
                // if( isset($item_entries[3]) && isset($this->settings["cheapest_prod_dis_per4"]) && ( (float)$this->settings["cheapest_prod_dis_per4"] > 0) && ((float)$this->settings["cheapest_prod_dis_per4"] <= 100) ){
                //     $item_discounts[$item_entries[3]] += $item_prices[$item_entries[3]] * (float)$this->settings["cheapest_prod_dis_per4"] * .01;
                // }
                // if( isset($item_entries[4]) && isset($this->settings["cheapest_prod_dis_per5"]) && ( (float)$this->settings["cheapest_prod_dis_per5"] > 0) && ((float)$this->settings["cheapest_prod_dis_per5"] <= 100) ){
                //     $item_discounts[$item_entries[4]] += $item_prices[$item_entries[4]] * (float)$this->settings["cheapest_prod_dis_per5"] * .01;
                // }

                //echo "<pre>";print_r($total_discount);print_r($item_discounts);exit;

                foreach($order->items as $row){
                    if($item_discounts[$row->id] > 0){
                        $total_discount += $item_discounts[$row->id];
                        $item->item_discount = $item_discounts[$row->id];
                        $item->item_total = $item->item_subtotal - $item->item_discount;
                        $item->save();
                    }
                }
                //echo "<pre>";print_r($total_discount);exit;

                // update Order
                if($total_discount > 0){
                    $order->order_custom3 = $this->settings["discount_message"] . " {$total_discount}";
                    $order->order_discount = $total_discount;
                    $order->order_total = $order->order_subtotal - $order->order_discount + $order->order_shipping;
                    $order->save();

                    return $order;
                }
                
            }
        }
        elseif(isset($this->settings["dis2_act"]) && ($this->settings["dis2_act"] == 1)){ //check active discount 2

            if(isset($this->settings["dis2_product"]) && (count($this->settings["dis2_product"]) > 0) && isset($this->settings["dis2_per"]) && ( (float)$this->settings["dis2_per"] > 0) && ((float)$this->settings["dis2_per"] <= 100)  ){

                $item_prices = array();
                $item_qtys = array();
                $item_entries = array();
                $order_qty = 0;

                foreach($order->items as $item) {
                    if( in_array($item->entry_id, $this->settings["dis2_product"]) ){
                        $item_prices[$item->entry_id] = (float)$item->price;
                        $order_qty += $item->item_qty;
                        $item_qtys[$item->entry_id] = $item->item_qty;
                    }
                }
                asort($item_prices);
                $item_entries = array_keys($item_prices);

                if(isset($this->settings["dis2_min"]) && ( $order_qty >= (int)$this->settings["dis2_min"])){ // check total qty of products
                    
                    $total_qty = 0;
                    $last_price = 0;
                    $entry_id_get_dis = 0;
                    foreach($item_entries as $entry_id){

                        $current_price = $item_prices[$entry_id];
                        $total_qty += $item_qtys[$entry_id];
    
                        if( $item_qtys[$entry_id] >= (int)$this->settings["dis2_min"] ){
                            $entry_id_get_dis = $entry_id;
                            break;
                        }
                        elseif( ($total_qty >= (int)$this->settings["dis2_min"]) && ($last_price == $current_price) ){
                            $entry_id_get_dis = $entry_id;
                            break;
                        }
                        $last_price = $item_prices[$entry_id];
                    }

                    if( $entry_id_get_dis > 0 ){

                        $item = "";
                        foreach($order->items as $row){
                            if($row->entry_id == $entry_id_get_dis){
                                $item = $row;
                                break;
                            }
                        }
                        if(isset($item)){
                            $item_discount = $item->price * (float)$this->settings["dis2_per"] * .01;
                            $item->item_discount = $item_discount;
                            $item->item_total = $item->item_subtotal - $item->item_discount;
                            $item->save();

                            // update Order
                            $order->order_custom3 = $this->settings["dis2_message"] . " {$item_discount}";
                            $order->order_discount = $item_discount;
                            $order->order_total = $order->order_subtotal - $order->order_discount + $order->order_shipping;
                            $order->save();

                            return $order;
                        }
                        
                    }

                }

            }

        }

        $order->order_custom3 = "";
        $order->save();
		return $order;
    }
	
	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	// ----------------------------------------------------------------------
}

/* End of file ext.store_sending_email_before_payment.php */
/* Location: /system/expressionengine/third_party/store_sending_email_before_payment/ext.store_sending_email_before_payment.php */