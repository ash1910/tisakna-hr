<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class Data_import_model extends Db_lib
{
	var $fields;
	var $channels_data;
	public function __construct()
	{
		parent::__construct();
	}
	
	public function get_channels()
	{
		$data = $this->get('','channels');
		$ret  = array("" => $this->lang->line('select_channel'));
		foreach ($data as $row) 
		{
			$ret[$row['channel_id']] = $row['channel_title'];
		}
		asort($ret);
		return $ret;
	}
	
	public function get_field_by_name($field_name)
	{
		foreach ($this->fields as $k => $v) 
		{
			if($v['field_name'] == $field_name)
				return $v;
		}
			
	}
	
	public function get_field_by_id($field_id)
	{
		if(isset($this->fields[$field_id]))
			return $this->fields[$field_id];
	}
	
	public function get_channel_fields($channel_id)
	{
		$options['where'] = array('channel_id' => $channel_id);
		$channel_data = $this->get_row($options, 'channels');
		$options['where'] = array('group_id' => $channel_data['field_group']);
		$data = $this->get($options,'channel_fields');
		foreach ($data as $row) 
		{
			$this->fields[$row['field_id']] = $row;
			if(strstr($row['field_type'], 'matrix'))
			{
				$ret[$row['field_name']][$row['field_id']] = $this->get_matrix_fields($row['field_id']);
				continue;
			}
			if($row['field_type'] == 'store')
			{
				$ret[$row['field_name']][$row['field_id']] = $this->get_store_fields($row['field_id']);
				continue;
			}
			if($row['field_type'] == 'zoo_visitor')
			{
				$ret[$row['field_name']][$row['field_id']] = $this->get_zoo_fields();
				continue;
			}
			$ret[$row['field_id']] = $row['field_name'];
		}

		@asort($ret);
		return $ret;
	}
	
	public function get_matrix_field_id($col_id)
	{
		$options['where'] = array('col_id' => $col_id);
		$data = $this->get_row($options, 'matrix_cols');
		return @$data['field_id'];
	}
	
	public function get_upload_prefs_info($name)
	{
		$options['where'] = array('name' => $name);
		$data = $this->get_row($options, 'upload_prefs');
		return $data;
	}
	
	public function get_dir_id($name)
	{
		$data = $this->get_upload_prefs_info($name);
		return $data['id'];
	}
	
	public function get_filedir($name)
	{
		$data = $this->get_upload_prefs_info($name);
		return "{filedir_{$data['id']}}";
	}
	
	public function get_matrix_fields($field_id)
	{
		$options['where'] = array('field_id' => $field_id);
		$data = $this->get_matrix_cols($options);
		foreach ($data as $row) 
		{
			$ret[$row['col_id']] = $row['col_label'];
		}
		@asort($ret);
		return $ret;
	}
	
	public function get_store_fields()
	{
		return array(
			'sku' => 'sku',
			'stock_level' => 'stock_level',
			'track_stock' => 'track_stock',
			'price' => 'price',
			'sale_price' => 'sale_price',
			'dimension_l' => 'dimension_l',
			'dimension_w' => 'dimension_w',
			'dimension_h' => 'dimension_h',
			'sale_price_enabled' => 'sale_price_enabled',
			'weight' => 'weight',
			'free_shipping' => 'free_shipping',
			
		);
	}
	
	public function get_zoo_fields()
	{
		return array(
			'email' => 'email',
			'username' => 'username',
			'password' => 'password',
		);
	}

	
	public function get_member_groups()
	{
		$options['order_by'] = 'group_title';
		$data = $this->get($options,'member_groups');
		foreach ($data as $row) 
		{
			$ret[$row['group_id']] = $row['group_title'];
		}
		return $ret;
	}
		
	public function get_table_keys($table)
	{
		$fields = $this->db->list_fields($table);
		sort($fields);
		foreach ($fields as $f) 
		{
			$ret[$f] = $f;
		}
		
		return $ret;
	}
	
	public function get_channel_categories($channel_id)
	{
		$ret = array(''=>lang('select_key'));
		
		$channel_data = $this->get_row(array('channel_id'=>$channel_id), 'channels');
		$groups = explode("|", $channel_data['cat_group']);
		foreach ($groups as $group) 
		{
			$opt['where'] = array('group_id'=>$group);
			$opt['order_by'] = 'cat_name';
			$cats = $this->get($opt, 'categories');
			foreach ($cats as $cat) 
			{
				$ret[$cat['cat_id']] = $cat['cat_name'];
			}
		}
		
		return $ret;
	}
	
	public function get_channel_category_groups($channel_id)
	{
		$ret = array();
		$opt['where'] = array('channel_id'=>$channel_id);
		$channel_data = $this->get_row($opt, 'channels');
		$groups = explode("|", $channel_data['cat_group']);

		foreach ($groups as $group) 
		{
			$opt['where'] = array('group_id'=>$group);
			$opt['order_by'] = 'group_name';
			$cats = $this->get($opt, 'category_groups');
			foreach ($cats as $cat) 
			{
				$ret[$cat['group_id']] = $cat['group_name'];
			}
		}
		
		return $ret;
	}
	
	public function update_category_post($cat_id, $entry_id, $parent=0)
	{
		if( ! $cat_id) return ;

		// for multiple categories ( format must be 66;335;22;19 )
		$categories = explode(";",$cat_id);
		foreach ($categories as $cat_id) {
			$cat_id = trim($cat_id);
						
			$cat_data = $this->get_row(array('cat_id'=>$cat_id), 'categories');
			if($cat_data and $cat_data['parent_id'] > 0)
			{
				$this->update_category_post($cat_data['parent_id'],$entry_id);
			}
			//create cat if not exists
	//		if( ! $cat_data)
	//		{
	//			$data['site_id'] = $this->config->item('site_id');
	//			$data['group_id'] = $group_id;
	//			$data['parent_id'] = $parent;
	//			$data['cat_name'] = $cat_name;
	//			$data['cat_url_title'] = str_replace(' ','-',strtolower(trim($cat_name)));
	//			$data['cat_description'] = $cat_name;
	//			$options['where'] = array('group_id'=>$group_id);
	//			$options['max'] = 'cat_order';
	//			$order = $this->get_row($options,'categories');
	//			$data['cat_order'] = $order['cat_order']['cat_order']+1;
	//			$cat_data['cat_id'] = $this->insert('categories', $data);
	//		}
			

			if( ! $c = $this->get_row(array('entry_id'=>$entry_id,'cat_id'=>$cat_id), 'category_posts'))
			{
				$this->insert('category_posts', array('entry_id'=>$entry_id,'cat_id'=>$cat_id));
			}

		}

		return $cat_id;
	}
	
	public function delete_category_post($channel_id, $entry_id)
	{
//		if( ! isset($this->channels_data[$channel_id]))
//		{
//			$channel_data = $this->get_row(array('channel_id'=>$channel_id), 'channels');
//			$cat_groups = explode('|', $channel_data['cat_group']);
//			$cat_arr = array();
//			
//			foreach ($cat_groups as $cat_group)
//			{
//				$cat_arr += $this->get(array('group_id'=>$cat_group), 'categories');
//			}
//			$this->channels_data[$channel_id] = $cat_arr;
//		}
//		else
//		{
//			$cat_arr = $this->channels_data[$channel_id];
//		}
//		
//		
//		foreach ($cat_arr as $cat_row)
//		{
//			$this->delete('category_posts', array('entry_id'=>$entry_id,'cat_id'=>$cat_row['cat_id']));
//		}
	}
	
//	public function get_jewellery_group()
//	{
//		$data = $this->get_row(array('group_name'=>'Jewellery'), 'category_groups');
//		return $data['group_id'];
//	}
}

/* End of file data_import_model.php */