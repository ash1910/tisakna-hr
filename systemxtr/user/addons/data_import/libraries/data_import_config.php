<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * data_import Config
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		addonlabs
 * @link		http://addonlabs.com
 */

class Data_import_config {
	
	private $_config_values = array();
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->init_defaults();
		$this->init_config();
	}
	
	public function init_config()
	{
		$settings = $this->get_settings();
		if(isset($settings['settings']) and $settings['settings'])		 
		$config_values = unserialize($settings['settings']);
		foreach ((array)@$config_values as $key => $val) 
		{
			$this->_config_values[$key] = $val;
		}
	}
	
	public function get_settings()
	{
		return $this->EE->db->select('settings')
				 			 ->from('data_import_settings')
				 			 ->get()
				 			 ->row_array();		
	}
	
	/**
	 * Save config
	 *
	 */
	public function save()
	{
		$settings = $this->get_settings();
		if(isset($settings['settings']) and $settings['settings']) 
		{		
			$this->EE->db->update(
				'data_import_settings',
				array('settings'  => serialize($this->_config_values))
			);
		} 
		else 
		{
			$this->EE->db->insert(
				'data_import_settings',
				array('settings'  => serialize($this->_config_values))
				);			
		}

		$get_defined_constants = get_defined_constants(true);
		$PATH_DICT = $get_defined_constants['user']['PATH_DICT'];

		$file = $PATH_DICT.'data_import_database.php';
		// Open the file to get existing content
		//$current = file_get_contents($file);
		// Append a new person to the file
		$current = "<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');";
		$current .= "\n\n$";
		$current .= "config['database'] = array( 'data_import' => array( 
		'hostname' => '{$this->_config_values['hostname']}', 
		'database' => '{$this->_config_values['database']}', 
		'username' => '{$this->_config_values['username']}', 
		'password' => '{$this->_config_values['password']}', 
		'dbprefix' => '{$this->_config_values['dbprefix']}', 
		'port'  => '{$this->_config_values['ftp_port']}' ), 
		);";
		// Write the contents back to the file
		file_put_contents($file, $current);
	}
	
	public function item($key, $value="", $post="")
	{
		// if value set config value
		if ($value or $post) 
		{
			$this->_config_values[$key] = $value;
		}
		
		return isset($this->_config_values[$key]) ? $this->_config_values[$key] : null;
	}
	
	public function items($items=array())
	{
		if(count($items))
		{
			foreach ($items as $k => $v) 
			{
				$this->_config_values[$k] = $v;
			}
		}
		return $this->_config_values;
	}
	
	public function init_defaults()
	{

		$this->_config_values =  array(
		'remote_table'  => '',
		'table'  => '',
		'key_field'  => '',
		'remote_key_field'  => '',
		'dbprefix'  => '',
		'username'  => '',
		'password'  => '',
		'database'  => '',
		'dbprefix'  => '',
		'hostname'  => '',
		'channel'  => '',
		'match_key'  => '',
		'new_entry_title'  => '',
		'create_if_not_exists'  => 'Y',
		'remove_if_not_exists'  => 'N',
		'import_if'  => '',
		'condition_equal_value'  => '',
		'site_id'  => $this->EE->config->item('site_id'),
		'author_id'  => $this->EE->session->userdata['member_id'],
		);
		
		$this->_config_values['ftp_hostname'] 	= '';
		$this->_config_values['ftp_username'] 	= '';
		$this->_config_values['ftp_password'] 	= '';
		$this->_config_values['ftp_webroot'] 	= '';
		$this->_config_values['ftp_port'] 	 	= '21';		
	}
}
/* End of file data_import_config.php */
/* Location: /system/expressionengine/third_party/data_import/libraries/data_import_config.php */