<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class Data_import_list_model extends Db_lib
{
	
	public function __construct()
	{
		parent::__construct();
		$this->table = 'data_import_list';
	}
	
	public function get_settings($import_id)
	{
		$opt['where'] = array('import_id'=>$import_id);
		$data = $this->get_row($opt);
		//echo "<pre>";print_r($data);exit;
		if (isset($data['settings'])) {
			return (array)unserialize($data['settings']);
		} else {
			$data['settings'] = "";
			return (array)unserialize($data['settings']);			
		}
	}
}
	


/* End of file data_import_list_model.php */