<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * data_import library
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		addonlabs
 * @link		http://addonlabs.com
 */

class Data_import_process {

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->library('api');
		$this->EE->load->helper('cookie');
		$this->EE->legacy_api->instantiate('channel_entries');
		$this->EE->legacy_api->instantiate('channel_fields');
	}

	public function start($import="")
	{
		set_time_limit(0);
		$this->EE =& get_instance();
		$this->EE->load->model(array('data_import_remote_model'));
		$this->EE->load->library(array('data_import_config', 'db_lib'));
		$this->settings = $this->EE->data_import_config->items();
		$this->EE->load->model(array('data_import_model', 'data_import_list_model'));

		if( ! $this->EE->data_import_remote_model->connect($this->settings))
		{
			show_error("Can't connect to DB server");
		}

		if($import)
		{
			$import_list = $this->EE->data_import_list_model->get(array('where_in'=>array('title'=>explode('|', $import))));
		}
		else {
			$import_list = $this->EE->data_import_list_model->get();
		}
		foreach ($import_list as $import_row)
		{
			$this->settings = array_merge($this->settings, $this->EE->data_import_list_model->get_settings($import_row['import_id']));

			$__current_website = $this->EE->config->item('site_id');
			$this->EE->config->site_prefs('', $this->settings['site_id']);
			set_cookie('cp_last_site_id', $this->settings['site_id'], 0);

			if( ! $this->settings['match_key'])
			{
				show_error('Please set match key');
			}
			$entries_cnt = $this->EE->data_import_remote_model->get_entries($this->settings,0,0,1);
			$offset = 0; $limit = 100;

			// get local entries list
			$channel_titles_to_remove = array();

			if($this->settings['delete_if_not_exists'] == 'Y')
			{
				$delete_if_not_exists_options['select'] = 'entry_id';
				$delete_if_not_exists_options['where'] = array('channel_id' => $this->settings['channel']);
				$channel_titles = $this->EE->data_import_model->get($delete_if_not_exists_options, 'channel_titles');

				foreach ($channel_titles as $v)
				{
					$channel_titles_to_remove[$v['entry_id']] = $v['entry_id'];
				}
			}
		

			while($entries_to_import = $this->EE->data_import_remote_model->get_entries($this->settings, $limit, $offset))
			{
				$hasentry = true;
				$offset += $limit;

				$channel_fields = $this->EE->data_import_model->get_channel_fields($this->settings['channel']);
				$channel_data = $this->EE->data_import_model->get_row(array('channel_id' => $this->settings['channel']), 'channels');

				//echo "<pre>";print_r($entries_to_import);exit; 

				foreach ($entries_to_import as $row)
				{

					$title = $this->settings['new_entry_title'];

					$entry_id = $this->_get_remote_data($row);

					//prepare category posts
					if(@is_array($this->settings['remote_field']))
					{
						foreach ($row as $row_key => $row_val)
						{
							if(in_array($row_key,$this->settings['remote_field']) and $row_val)
							{
								$this->EE->data_import_model->delete_category_post($this->settings['channel'], $entry_id);
								break;
							}
						}
					}


					if( ! $entry_id) continue;
					if(isset($channel_titles_to_remove[$entry_id]))
					unset($channel_titles_to_remove[$entry_id]);
					$this->entry_id = $entry_id;

					$where['entry_id'] = $entry_id;
					$channel_titles_update = $update_data = array();

					// update category
					if(@is_array($this->settings['remote_field']))
					{
						foreach ($row as $row_key => $row_val)
						{
							if(in_array($row_key,$this->settings['remote_field']) and $row_val)
							{


								$this->EE->data_import_model->update_category_post($row_val, $entry_id);
							}
						}
					}
					foreach ($this->settings['assigned_fields'] as $key => $field_row)
					{

						if( ! $field_row) continue;

						if(is_array($field_row))
						{
							$field_id = key($field_row);
							$field_data = $this->EE->data_import_model->get_field_by_id($field_id);
							$field_type = $field_data['field_type'];
						}

						if(is_array($field_row) and strstr($field_type, 'zoo_visitor'))
						{
							$update_data['field_id_'.$field_id] = $this->_process_zoo_visitor($row, current($field_row));
							if(empty($update_data['field_id_'.$field_id]))
							unset($update_data['field_id_'.$field_id]);

							continue;
						}

						if(is_array($field_row) and strstr($field_type, 'matrix'))
						{
							$update_matrix_data = array();
							foreach (current($field_row) as $key1 => $field_row1) {
								if( ! $field_row1) continue;
								list($null, $field_row1) = explode('.', $field_row1);
								$update_matrix_data['col_id_'.$key1] = $row[$field_row1];
								$title = str_replace('{'.$channel_fields[$key][$field_id][$key1].'}', $row[$field_row1], $title);
								$update_matrix_data['field_id'] = $this->EE->data_import_model->get_matrix_field_id($key1);
							}

							$where2 = $where + $update_matrix_data;


							if($update_matrix_data)
							{
								$matrix_delete_key = $entry_id.$update_matrix_data['field_id'];

								if( ! isset($matrix_delete_array[$matrix_delete_key]))
								{
									$matrix_delete_array[$matrix_delete_key] = true;
									$matrix_delete_where['entry_id'] = $entry_id;
									$matrix_delete_where['field_id'] = $update_matrix_data['field_id'];
									$this->EE->data_import_model->delete($matrix_delete_where, 'matrix_data');
								}

								$update_matrix_data['entry_id'] = $entry_id;
								// build order
								$temp_matrix_where['where']['field_id'] = $update_matrix_data['field_id'];
								$temp_matrix_where['where']['entry_id'] = $entry_id;
								if($matrix_row=$this->EE->data_import_model->get_row($where2, 'matrix_data'))
								{
									if( ! $matrix_row['row_order'])
									{
										$temp_matrix_where['max'] = 'row_order';
										$update_matrix_data['row_order'] = $this->EE->data_import_model->get_row($temp_matrix_where, 'matrix_data')+1;
									}

								}
								else
								{
									$temp_matrix_where['max'] = 'row_order';
									$update_matrix_data['row_order'] = $this->EE->data_import_model->get_row($temp_matrix_where, 'matrix_data')+1;
								}

								$this->EE->data_import_model->save($update_matrix_data, $where2, 'matrix_data');
							}
							continue;
						}
						if(is_array($field_row) and strstr($field_type, 'store'))
						{
							$update_product_data = $update_stock_data = array();
							foreach (current($field_row) as $key1 => $field_row1) {
								if( ! $field_row1) continue;
								list($null, $field_row1) = explode('.', $field_row1);
								switch ($key1) {
									case 'sku':
										//if( ! $this->EE->data_import_model->get_row(array('where' => array('sku'=>substr($row[$field_row1],0,20))),'store_stock'))
										//{
											$update_stock_data[$key1] = $row[$field_row1];
										//}
										$store_update_data['field_id_'.$field_id] = $row[$field_row1];
										$this->EE->data_import_model->update('channel_data', $where, $store_update_data);

										break;
									case 'stock_level':
									case 'track_stock':
										switch ($key1) {
											case 'track_stock':
												$row[$field_row1] = $row[$field_row1] == '1' ? '1' : '0';
												break;

											default:
												break;
										}

										$update_stock_data[$key1] = $row[$field_row1];
										break;
									case 'price':
									case 'sale_price':
									case 'dimension_l':
									case 'dimension_w':
									case 'dimension_h':
									case 'sale_price_enabled':
									case 'weight':
									case 'free_shipping':
										if($key1 == 'price' or $key1 == 'sale_price')
										$update_product_data[$key1] = str_replace(',', '.', $row[$field_row1]);
										else
										$update_product_data[$key1] = $row[$field_row1];
										break;

									default:
										break;
								}
								$title = str_replace('{'.$channel_fields[$key][$field_id][$key1].'}', $row[$field_row1], $title);
							}
							if($update_stock_data)
							$this->EE->data_import_model->update('store_stock', $where, $update_stock_data);
							$update_product_data['entry_id'] = $entry_id;

							$this->EE->data_import_model->save($update_product_data, $where, 'store_products');
							$this->EE->data_import_model->update('channel_data', $where, $update_data);

							continue;
						}
						if(strchr($field_row, '.'))
						list($null, $field_row) = explode('.', $field_row);
						if($key=='title' or $key=='entry_date' or $key=='expiration_date' or $key=='status')
						{
							if($key=='status')
							{
								if($field_row !='same')$channel_titles_update[$key] = $field_row;
							}
							else
							{
								$channel_titles_update[$key] = $row[$field_row];
							}
							continue;
						}
						$title = str_replace('{'.$channel_fields[$key].'}', $row[$field_row], $title);
						$update_data['field_id_'.$key] = $row[$field_row];
					}

					$this->EE->data_import_model->update('channel_data', $where, $update_data);

					if($title)
					{
						$channel_titles_update['title'] = $title;
					}
					//if(@$channel_titles_update['title'])
					//{
					//	$channel_titles_update['url_title'] = str_replace(' ', '-', $channel_titles_update['title']);
//
					//	$search = array("Š", "Đ", "Č", "Ć", "Ž", "š", "đ", "č", "ć", "ž");
					//	$replace = array("s", "d", "c", "c", "z", "s", "d", "c", "c", "z");
					//	$channel_titles_update['url_title'] = str_replace( $search, $replace, $channel_titles_update['url_title']);
//
					//	$channel_titles_update['url_title'] = str_replace('--', '-', strtolower( preg_replace('![^a-zA-Z0-9_]!', '-', $channel_titles_update['url_title'])));
					//}

					// Get URL title
					//if(@$channel_titles_update['title']){
					//	$search = array("Š", "Đ", "Č", "Ć", "Ž", "š", "đ", "č", "ć", "ž", ",");
					//	$replace = array("s", "d", "c", "c", "z", "s", "d", "c", "c", "z", "-");
					//	$titleSR = str_replace( $search, $replace, $channel_titles_update['title']);
//
					//	$data["url_title"] = url_title( strtolower( $titleSR ) );
//
					//	$option = [];
					//	$option["url_title"] = $data["url_title"];
					//	$option["channel_id"] = $this->settings['channel'];
					//	$option["entry_id"] = isset($entry_id) ? $entry_id : 0;
					//	$option = $this->check_url_title($option);
					//	$url_title_new_with_increment = isset($option["url_title"]) ? $option["url_title"] : rand(100000, 999999);
					//	$channel_titles_update['url_title'] = $url_title_new_with_increment;
					//}

					if(isset($channel_titles_to_remove[$entry_id]))
					unset($channel_titles_to_remove[$entry_id]);
					$this->EE->data_import_model->update('channel_titles', $where, $channel_titles_update);
				}
			}
		}
		if($this->settings['delete_if_not_exists'] == 'Y' and $channel_titles_to_remove and isset($hasentry))
		{
			$this->EE->api_channel_entries->delete_entry($channel_titles_to_remove);
		}
		
		$this->EE->config->site_prefs('', $__current_website);
		set_cookie('cp_last_site_id', $__current_website, 0);		
	}

	private function _get_remote_data($row)
	{
		$field_type = '';
		if(is_array($this->settings['match_key']))
		{
			$fid = key($this->settings['match_key']);
			$field_data = $this->EE->data_import_model->get_field_by_id($fid);
			$field_type = $field_data['field_type'];
			$match_key = $this->settings['match_key'][$fid];
		}

		if(strstr($field_type, 'matrix'))
		{
			$key_data = $this->EE->data_import_model->get_row('matrix_data', array('col_id_'.$match_key=>$row[$this->settings['assigned_fields'][$field_data['field_label']][$fid][$match_key]]));
		}

		elseif(strstr($field_type, 'store'))
		{
			list($null, $rf) = explode('.', $this->settings['assigned_fields'][$field_data['field_label']][$fid][$match_key]);
			switch ($fid) {
				case 'sku':
				case 'stock_level':
					$key_data = $this->EE->data_import_model->get_row('store_stock', array($match_key=>substr($row[$rf],0,20)));
					break;
				case 'price':
				case 'sale_price':
					$key_data = $this->EE->data_import_model->get_row('store_products', array($match_key=>$row[$rf]));
					break;

				default:
					break;
			}
		}

		elseif(strstr($field_type, 'zoo_visitor'))
		{
			list($null, $rf) = explode('.', $this->settings['assigned_fields'][$field_data['field_label']][$fid][$match_key]);
			if(empty($row[$rf])) return false;
			$key_data = $this->EE->data_import_model->get_row('members', array($match_key=>$row[$rf]));
			if($key_data)
			{
				$channel_data = $this->EE->data_import_model->get_row('channel_data', array('field_id_'.$fid=>$key_data['member_id']));
				if($channel_data)
				{
					$key_data['entry_id'] = $channel_data['entry_id'];
				}
			}


		}

		else
		{
			list($null, $rf) = explode('.', $this->settings['assigned_fields'][$this->settings['match_key']]);
			//$key_data = $this->EE->data_import_model->get_row('channel_data', array('field_id_'.$this->settings['match_key']=>$row[$rf]));
			$key_data = $this->EE->data_import_model->get_row('channel_data', array('field_id_'.$this->settings['match_key']=>$row[$rf], 'channel_id'=>$this->settings['channel']));
		}
		if( ! @$key_data)
		{
			return $this->_create_entry($row);
		}
		return $key_data['entry_id'];
	}

	private function _create_entry($row, $entry_id='')
	{
		if($this->settings['create_if_not_exists'] == 'N' ) return false;

		if( ! @$entry_id)
		{
			$data = array(
			'title'    => 'Temp title',
			'entry_date'    => mktime(),
			'status'    => 'open',
			'author_id'    => $this->settings['author_id'],
			'site_id'    => $this->settings['site_id'],
			);

			// check required
			$this->EE->db->select('field_id, field_name, field_label, field_type, field_required');
			$this->EE->db->join('channels', 'channels.field_group = channel_fields.group_id', 'left');
			$this->EE->db->where('channel_id', $this->settings['channel']);
			$query = $this->EE->db->get('channel_fields');
			$result = $query->result_array();
			foreach ($result as $result_row)
			{
				// Required field?
				if ($result_row['field_required'] == 'y')
				{
					$data['field_id_'.$result_row['field_id']] = NBS;
				}
			}
			//
			$this->EE->api_channel_fields->setup_entry_settings($this->settings['channel'], $data);

			if ($this->EE->api_channel_entries->submit_new_entry($this->settings['channel'], $data) === FALSE)
			{
				show_error('An Error Occurred Creating the Entry:'.var_export($this->EE->api_channel_entries->errors,1));
			}
			$entry_id = $this->EE->api_channel_entries->entry_id;
		}

		// create matrix entry
		if($this->EE->db->table_exists('matrix_data'))
		{
			$this->EE->data_import_model->insert('matrix_data', array('entry_id'=>$entry_id, 'site_id'=> $this->settings['site_id']));
		}
		// create store entry
		if($this->EE->db->table_exists('store_stock'))
		{
			$this->EE->data_import_model->insert('store_stock', array('sku'=>mktime().rand(),'entry_id'=>$entry_id));
		}
		return $entry_id;
	}

	private function _process_zoo_visitor($row, $field_row)
	{
		$upd = array();
		foreach ($field_row as $key=>$val)
		{
			if(strchr($val, '.'))
			list($null, $val) = explode('.', $val);
			$field_row[$key] = $val;
		}

		if( ! @empty($row[$field_row['username']]))
		$upd['username'] = $row[$field_row['username']];
		if( ! @empty($row[$field_row['email']]))
		{
			$upd['email'] = $row[$field_row['email']];
			$upd['screen_name'] = $row[$field_row['email']];
		}
		if( ! @empty($row[$field_row['password']]))
		$upd['password'] = do_hash($row[$field_row['email']]);

		if( ! $upd) return ;
		$where = $upd['email'] ? array('email'=>$upd['email']) : '';
		if($where)
		{
			if( ! $member=$this->EE->data_import_model->get($where, 'members'))
			{
				$upd['unique_id']	= random_string('encrypt');
				$upd['join_date']	= $this->EE->localize->now;
				$upd['language'] 	= $this->EE->config->item('deft_lang');
				$upd['timezone'] 	= ($this->EE->config->item('default_site_timezone') && $this->EE->config->item('default_site_timezone') != '') ? $this->EE->config->item('default_site_timezone') : $this->EE->config->item('server_timezone');
				$upd['daylight_savings'] = ($this->EE->config->item('default_site_dst') && $this->EE->config->item('default_site_dst') != '') ? $this->EE->config->item('default_site_dst') : $this->EE->config->item('daylight_savings');
				$upd['time_format'] = ($this->EE->config->item('time_format') && $this->EE->config->item('time_format') != '') ? $this->EE->config->item('time_format') : 'us';
			}
		}
		$upd['group_id'] = $this->settings['member_group_id'];

		if( ! $member)
		$where['email'] = '';
		$ret = $this->EE->data_import_model->save($upd, $where, 'members');

		// delete entry which creates zoo
		$cht = $this->EE->data_import_model->get_row(array('title'=>''), 'channel_titles');
		$this->EE->data_import_model->delete(array('title'=>''), 'channel_titles');
		if($cht)
		$this->EE->data_import_model->delete(array('entry_id'=>$cht['entry_id']), 'channel_data');

		if(is_array($ret))
		return $ret['member_id'];
		return $ret;
	}

	function check_url_title($data) 
	{
		$url_title = $data['url_title']; 
		$channel_id = $data['channel_id']; 
		$entry_id = $data['entry_id']; 
		// Check to see if this is an existing entry
		$entry = ee('Model')->get('ChannelEntry')
			->filter('channel_id', $channel_id)
			->filter('url_title', $url_title)			
			->filter('entry_id', '!=', $entry_id)
			->fields('entry_id')
			->first();

		$fields = [];

		// If new entry, check URL title against database
		if ($entry)
		{
			$fields['found'] = true;
			$found = FALSE;
			$num = 2;
			while ($found == FALSE)
			{
				$new_url_title = $url_title;
				// If URL title is already at the max-length, replace last two characters with -#
				if (strlen($url_title) > 198)
				{
					if (substr($url_title, -2, 1) === '-' AND is_numeric(substr($url_title, -1)))
					{
						$new_url_title = substr_replace($url_title, $num, -1);
					}				
				}
				else 
				{
					$new_url_title .= '-' . $num;
				}
				$entry = ee('Model')->get('ChannelEntry')
					->filter('channel_id', $channel_id)
					->filter('url_title', $new_url_title)			
					->filter('entry_id', '!=', $entry_id)
					->fields('entry_id')
					->first();
				// Increase number if alternate URL already exists
				if ($entry)
				{
					$num++;
				}
				else 
				{
					$found = TRUE;
					$url_title = $new_url_title;
					$fields['not_changed'] = false;					
				}
				// To avoid long loop, addon stops at 10 alternate URL titles.
				if ($num > 9)
				{
					$found = TRUE;
					$fields['not_changed'] = true;
				}
			}
			$fields['url_title'] = $url_title;
			//ee()->output->send_ajax_response(json_encode($fields));
		}
		// Existing entry so no check is performed
		else
		{
			$found = false;
			$fields['found'] = false;
			$fields['url_title'] = $url_title;
			//ee()->output->send_ajax_response(json_encode($found));
		}

		return $fields;

	}

}

if( ! function_exists("myd"))
{
	function myd($arr,$exit=false){
		if (isset($GLOBALS['debugifon']) and !isset($_REQUEST['debug'])) {
			return ;
		}

		if ($exit === 2)
		ob_start();
		if (is_array($arr)) {
			echo "<pre>";
			print_r($arr);
			echo "</pre>";
		} elseif (is_string($arr)) {
			echo $arr."<br>";
		} elseif (is_object($arr)) {
			echo "<pre>";
			var_export($arr)."<br>";
			echo "</pre>";
		} else {
			echo ($arr)."<br>";
		}

		if ($exit === 2) {
			$cont = ob_get_contents();
			ob_end_clean();
			file_put_contents('myd.debug.txt', $cont, FILE_APPEND );
		}
		if ($exit === 1) exit;
	}
}
/* End of file data_import_process.php */
/* Location: /system/expressionengine/third_party/data_import/libraries/data_import_process.php */