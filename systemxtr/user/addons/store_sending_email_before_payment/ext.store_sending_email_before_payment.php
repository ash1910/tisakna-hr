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
 * Store_sending_email_before_payment Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		WMD.hr
 * @link		
 */
use Store\Model\Status;
use Store\Model\Email;

class Store_sending_email_before_payment_ext {
	
	public $settings 		= array();
	public $description		= 'Sending email before payment on gateway page';
	public $docs_url		= '';
	public $name			= 'Store :: Sending email before payment';
	public $settings_exist	= 'y';
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

		ee()->load->helper(array('html'));
		ee()->load->library(array('table'));
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

	function settings_form($current)
	{
		if (!empty($_POST))
		{
			$settings["payment_method"] = $this->EE->input->post("payment_method");
		
			$this->EE->db->where('class', __CLASS__);
			$this->EE->db->update('extensions', array('settings' => serialize($settings)));
	
			$this->EE->session->set_flashdata(
			'message_success',
			$this->EE->lang->line('preferences_updated')
			);
			$this->EE->functions->redirect( ee('CP/URL', 'addons/settings/store_sending_email_before_payment') );
		}
		//echo "<pre>";print_r($current);

		$vars['current'] = $current;
		$payment_methods = $this->EE->db->where(array('enabled' => 1, 'site_id' => $this->EE->config->item('site_id')))->get('exp_store_payment_methods')->result_array();
		
		//$vars['payment_methods'][""] = "--";
		foreach ($payment_methods as $payment_method)
		{
			$vars['payment_methods'][$payment_method['class']] = $payment_method['title'];
		}
		
		return $this->EE->load->view('settings', $vars, TRUE);
	}
	/**
	 * Save Settings
	 *
	 * This function provides a little extra processing and validation
	 * than the generic settings form.
	 *
	 * @return void
	 */
	function save_settings()
	{
		
		if (empty($_POST))
		{
			show_error($this->EE->lang->line('unauthorized_access'));
		}

		$settings["payment_method"] = $this->EE->input->post("payment_method");
		
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->update('extensions', array('settings' => serialize($settings)));

		$this->EE->session->set_flashdata(
		'message_success',
		$this->EE->lang->line('preferences_updated')
		);
	}
		
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
			'method'	=> 'sending_email',
			'hook'		=> 'store_transaction_update_start',
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
	public function sending_email()
	{
		$args = func_get_args();
		$request = $args[0];
		$transaction = $args[1];

		//$order = $request->order->toTagArray();
		//echo "<pre>";print_r($order);exit;
		//echo "<pre>";print_r($_POST["_params"]);exit;
		
		// get settings 
		$this->EE->db->where('class', __CLASS__);
		$settings = unserialize($this->EE->db->get('extensions')->row('settings'));
		
		//if( $request->order->payment_method != $settings['payment_method'] ) return false;
		if(!in_array($request->order->payment_method, $settings['payment_method'])) return false;
		if( empty($_POST["_params"]) ) return false; 
		if( $request->order->order_status != "" ) return false;
		
		
		$installed = $this->EE->addons->get_installed();
		$this->EE->load->add_package_path($installed['store']['path'], TRUE);
		
		// send email
		$emails = Email::whereIn('id', array(3))
                ->where('enabled', 1)
				->get();
		foreach ($emails as $email) {
			$this->EE->store->email->send($email, $request->order);
		}
		//echo "<pre>";print_r($emails);exit;

		/*$status = Status::where('site_id', $this->EE->config->item('site_id') )->where('is_default', 1)->first();
		if ($status->email_ids) {
            $emails = Email::whereIn('id', $status->email_ids)
                ->where('enabled', 1)
                ->get();

            foreach ($emails as $email) {
                $this->EE->store->email->send($email, $request->order);
            }
        }*/
		
		return true;
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