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