<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! class_exists('CI_Model'))
{
	load_class('Model', 'core');
}

if( ! function_exists('ci'))
{
	function ci()
	{
		return get_instance();
	}
}

class Db_lib extends CI_Model {
	public  $table;
	public  $Db_lib_version = '1.14';
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function debug()
	{
		return  $this->db->_compile_select();
	}
	
	public function get($options=array(), $table='', $type='result', $protect=true)
	{
		// if options is a number then we want get by primary key
		if(is_numeric($options))
		{
			$table = $table ? $table : $this->table;
			$primary = $this->get_primary_field($table);
			$where = array($primary=>$options);
			$options = array();
			$options['where'] = $where;
		}

		// for older version
		if( ! is_array($options) and $options)
		{
			$tmp = $table;
			$table = $options;
			$options = $tmp;
		}
		
		$func = "{$type}_array";
		////////////////////////////////////////		
		$table = $table ? $table : $this->table;
		$this->db->from($table);
		
		$option_match = false;
		
		if(@$options['group_by'])
		{
			$this->db->group_by($options['group_by']);
			$option_match = true;
		}
		
		if(@$options['select'])
		{
			$this->db->select($options['select']);
			$option_match = true;
		}		
		
		if(@$options['where'])
		{
			$this->db->where($options['where'], null, $protect);
			$option_match = true;
		}
		
		if(@$options['join'])
		{
			
			if(is_array($options) and ! isset($options['join']['table']))
			{
				foreach ($options['join'] as $join) 
				{
					$this->db->join($join['table'], $join['cond'], @$join['type']);
				}
			}
			elseif(is_array($options)) 
			{
				$this->db->join($options['join']['table'], $options['join']['cond'], @$options['join']['type']);
			}
			$option_match = true;
		}
		
		if(@$options['or_where'])
		{
			$this->db->or_where($options['or_where']);
			$option_match = true;
		}
		
		if(@$options['custom'])
		{
			$this->db->where($options['custom']);
			$option_match = true;
		}
		
		if(@$options['where_in'])
		{
			$this->db->where_in(key($options['where_in']),current($options['where_in']));
			$option_match = true;
		}
		
		if(@$options['where_not_in'])
		{
			$this->db->where_not_in(key($options['where_not_in']),current($options['where_not_in']));
			$option_match = true;
		}
		
		if(isset($options['count']))
		{
			return $this->db->count_all_results();
		}
		
		if(isset($options['max']))
		{
			return $this->db->select_max($options['max'])->get()->row($options['max']);
		}	
			
		if(@$options['order_by'])
		{
			$this->db->order_by($options['order_by'], @$options['dir']);
			$option_match = true;
		}	
			
		if(@$options['limit'])
		{
			$this->db->limit($options['limit'], @$options['offset']);
			$option_match = true;
		}
		
		// if no options match we use most common option
		if($options and ! $option_match)
		{
			$this->db->where($options, null, $protect);
		}
		
		if(@$options['debug'])
		{
			if($options['debug'] == '1')
			echo $this->debug();
			else
			file_put_contents('sql_debug.txt', $this->debug()."\n\n", FILE_APPEND);
		}
		return $this->db->get()->$func();
	}
		
	public function get_row($options=array(), $table='')
	{
		return $this->get($options, $table, 'row');
	}
	
	public function delete($where=array(), $table='')
	{
		if(is_array($table))
		{
			$tmp = $table;
			$table = $where;
			$where = $tmp;
		}			
		$table = $table ? $table : $this->table;
		if($where)
		return $this->db->where($where)->delete($table);
	}
	
	public function save($data, $where=array(), $table='')
	{
		if( $where and ! is_array($where))
		{
			$table = $where;
			$where = array();
		}
		
		$table = $table ? $table : $this->table;
		$primary_key = $this->_get_primary_key($table);		

		
		if(isset($data[$primary_key]) and $data[$primary_key])
		{
			$where[$primary_key] = $data[$primary_key];
			$copy_primary_key = $data[$primary_key];
			unset($data[$primary_key]);
		}
		elseif (isset($data[$primary_key]) and ! $data[$primary_key])
		{
			$copy_primary_key = $data[$primary_key];
			unset($data[$primary_key]);
		}
		
		$fields = array_flip($this->db->list_fields($table));
		$data = @array_intersect_key($data, $fields);
		if($where and ($row=$this->get_row($where, $table)))
		{
			$this->update($table, $where, $data);
			return $row;
		}
		else 
		{
			if(@$copy_primary_key)
			$data[$primary_key] = $copy_primary_key;
			$this->insert($table, $data);
			return $this->db->insert_id();
		}
	}
	
	public function insert($table, $data=array(), $batch=false)
	{
		if(func_num_args() == 1)
		{
			$data = $table;
			$table = '';
		}
		if(is_array($table))
		{
			$tmp = $table;
			$table = $data;
			$data = $tmp;
		}		
		$table = $table ? $table : $this->table;
		$insert_func = $batch ? 'insert_batch' : 'insert';
		$this->db->$insert_func($table, $data);
		return $this->db->insert_id();
	}
	
	public function update($table='', $where=array(), $data=array())
	{

		if(is_array($table))
		{
			$tmp = $table;
			$data = $where;
			$where = $tmp;
			$table = '';
		}

		if( ! $data) return ;
		$table = $table ? $table : $this->table;
		$this->db->where($where)->update($table, $data);
	}
	
	public function _get_primary_key($table)
	{
		$fields = $this->db->field_data($table);
		foreach ($fields as $f) 
		{
			if($f->primary_key)
				return $f->name;
		}
	}
	
	public function get_tables()
	{
		$tables = $this->db->list_tables();
		foreach ($tables as $t) 
		{
			$ret[$t] = $t;
		}
		
		return $ret;
	}

	public function __call($func, $arg=array())
	{
		if(strstr($func, 'get_by_'))
		{
			preg_match("!(.*?)_get_by_(.*)!", $func, $m);
			
			if($this->table_exists($m[1]))
			{
				$options['where'][$m[2]] = $arg[0];
				$data = $this->get_row($options, $m[1]);
				return @$data;
			}			
		}
		elseif(strstr($func, '_by_'))
		{
			preg_match("!(.*?)_get_(.*)_by_(.*)!", $func, $m);
			
			if($this->table_exists($m[1]))
			{
				$options['where'][$m[3]] = $arg[0];
				$data = $this->get_row($options, $m[1]);
				return @$data[$m[2]];
			}			
		}
		elseif(strstr($func, 'get_'))
		{
			preg_match("!get_(.*)!", $func, $m);
			if($this->table_exists($m[1]))
			{
				return $this->get(@$arg[0], $m[1]);
			}
		}
		elseif(strstr($func, 'format_'))
		{
			preg_match("!format_(.*)!", $func, $m);
			return $this->format(@$arg[0],@$arg[1],@$arg[2], $m[1]);
		}
		else 
		{
			throw new Exception("Function $arg[0] not found in ". __FILE__. " on line".__LINE__);
		}
	}	
	
	public function table_exists($table)
	{
		return $this->db->table_exists($table);
	}	
	
	public function create_unique_index($table_name, $col_names)
	{
		$table_name = $this->db->protect_identifiers($table_name, TRUE);

		if (is_array($col_names))
		{
			$index_name = implode('_', $col_names);
			foreach ($col_names as $key => $col)
			{
				$col_names[$key] = $this->db->protect_identifiers($col);
			}
			$col_names = implode(',', $col_names);
		}
		else
		{
			$index_name = $col_names;
			$col_names = $this->db->protect_identifiers($col_names);
		}

		$sql = "CREATE UNIQUE INDEX ";
		$sql .= "$index_name ON $table_name ($col_names)";
		return $this->db->query($sql);		
	}
	
	public function get_primary_field($table='')
	{
		$table = $table ? $table : $this->table;
		$fields = $this->db->field_data($table);
		foreach ($fields as $f) 
		{
			if($f->primary_key) return $f->name;
		}
	}
	
	public function format($val_field, $options=array(), $key_field='', $table='')
	{
		$table = $table ? $table : $this->table;

		$key_field = $key_field ? $key_field : $this->get_primary_field($table);
		$data = $this->get($options, $table);
		$ret = array();
		foreach ($data as $k => $row) 
		{
			$ret[$row[$key_field]] = $row[$val_field];
		}
		return $ret;
	}	
}

/* End of file db_lib.php */