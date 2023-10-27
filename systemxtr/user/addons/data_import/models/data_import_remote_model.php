<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class Data_import_remote_model extends CI_Model
{
	public $rdb;
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

	}
	
	public function connect($vars)
	{
		if( ! @$vars['username'] or ! @$vars['hostname']) return false;
		
		/*$dsn = "mysql://$vars[username]:$vars[password]@$vars[hostname]/$vars[database]?db_debug=0";
		$this->rdb = '';
		//echo "<pre>";print_r($dsn);exit;
		$this->rdb =& $this->load->database($dsn, true);
		//echo "<pre>";print_r($this->rdb);exit;
		$this->rdb->dbprefix = $vars['dbprefix'];
		if( ! $this->rdb->conn_id) return false;*/

		/*$query = ee('data_import:db')->get('stanja');

	    if ($query->num_rows() == 0)
	    {
	      return ee()->TMPL->no_results();
	    }

	    echo "<pre>";print_r($query->num_rows());exit;*/


		$this->rdb = '';
		$this->rdb = ee('data_import:db');

		//$this->rdb = mysqli_connect($vars['hostname'],$vars['username'],$vars['password'],$vars['database']);
		
		return true;
	}
	
	public function get_entries($settings, $limit, $offset, $cnt=false)
	{
		$lastTable = '';
		$where = array();
		foreach ($settings['remote_table'] as $table)
		{
			if( ! isset($from))
			{
				$from = 1;
				$this->rdb->from($table);
			}
			if(isset($settings['condition'][$lastTable."_".$table]))
			{
				
				$this->rdb->join($table, $settings['condition'][$lastTable."_".$table], 'left');
			}
			$lastTable = $table;
		}
		
		if($settings['import_if'])
		{
			$this->rdb->where($settings['import_if'], $settings['condition_equal_value']);
		}
		//required
		foreach ($settings['assigned_fields'] as $key => $field_row)
		{
			if(is_array($field_row) and strstr($key, 'matrix'))
			{
				foreach ($field_row as $key1 => $field_row1) {
					if(@$this->settings['required'][$key][$key1] == 'Y' and $field_row1)
					{
						$where[$field_row1.' !='] = '';
					}
				}
				continue;
			}

			if(is_array($field_row) and strstr($key, 'store'))
			{
				foreach ($field_row as $key1 => $field_row1) {
					if(@$settings['required'][$key][$key1] == 'Y' and $field_row1)
					{
						$where[$field_row1.' !='] = '';
					}
				}
				continue;
			}
	
			if(@$settings['required'][$key] == 'Y' and $field_row)
			{
				$where[$field_row.' !='] = '';
			}			
		}
		
		$this->rdb->where($where);
				
		if($cnt)
		{
			return $this->rdb->count_all_results();
		}	
		$this->rdb->limit($limit, $offset);
		
		$res = $this->rdb->get();
		if($res)
		return  $res->result_array();
	}
	
	public function get_from($table, $where=array())
	{
		return  $this->rdb->from($table)->where($where)->get()->result_array();
	}
	
	public function table_exists($table)
	{
		return $this->rdb->table_exists($table);
	}
	
	public function get_tables()
	{
		$tables = $this->rdb->list_tables();
		foreach ($tables as $t) 
		{
			$ret[$t] = $t;
		}
		
		return $ret;
	}
	
	public function get_table_keys($table)
	{
		if( ! $this->table_exists($table)) return array();
		$fields = $this->rdb->list_fields($table);
		sort($fields);
		foreach ($fields as $f) 
		{
			$ret[$f] = $f;
		}
		
		return $ret;
	}
	
	public function get_table_keys_ext($tables)
	{
		if ( ! is_array($tables)) $tables = array($tables);
		foreach ($tables as $table)
		{
			if( ! $this->table_exists($table)) return array();
			$fields = $this->rdb->list_fields($table);
			sort($fields);
			foreach ($fields as $f)
			{
				$ret[$table.'.'.$f] = $table.'.'.$f;
			}
		}
		return $ret;
	}
				
	public function get($table, $where=array())
	{
		return  $this->rdb->from($table)->where($where)->get()->result_array();
	}
	
	public function get_row($table, $where=array())
	{
		return  $this->rdb->from($table)->where($where)->get()->row_array();
	}
}

/* End of file data_import_remote_model.php */