<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD . 'simple_cloner/config.php';

setlocale(LC_ALL, 'en_US.UTF8');

/**
 * Simple Cloner Extension class
 *
 * @package        simple_cloner
 * @author         Stuart Thornton <sthornton@knightknox.com>
 * @link           http://www.github.com/ThorntonStuart
 * @copyright      Copyright (c) 2016, Stuart Thornton
 */

class Simple_cloner_ext {

	/**
	 * Settings array
	 *
	 * @var 	array
	 * @access 	public
	 */
	public $settings = array();

	var $addon_name		=	SIMPLE_CLONER_NAME;
	var $name 			=	SIMPLE_CLONER_NAME;
	var $version 		=	SIMPLE_CLONER_VER;
	var $description	=	SIMPLE_CLONER_DESC;
	var $settings_exist	=	'y';
	var $docs_url		=	'';

	/**
	 * Constructor
	 *
	 * @access 	public
	 * @param 	array
	 * @return 	void
	 */
	public function __construct($settings = array())
	{
		// Load the settings library and initialize settings to default values.
		ee()->load->library('simple_cloner_settings');
		ee()->simple_cloner_settings->set($settings);

		// Fetch current settings values and override.
		$this->settings = ee()->simple_cloner_settings->get();
	}

	/**
	 * Settings
	 *
	 * @access 	public
	 * @return 	void
	 */
	public function settings()
	{
		// Redirect extension settings to module settings.
		ee()->functions->redirect('addons/settings/simple_cloner');
	}

	/**
	 * Format string to URI
	 *
	 * @access 	public
	 * @param 	string
	 * @return 	string
	 */
	public function strToUrl($str, $replace = array(), $delimiter = '-')
	{
		// If replace parameter empty,
		if(! empty($replace))
		{
			$str = str_replace((array)$replace, ' ', $str);
		}

		// Convert to UTF-8, replace characters that would break URL, convert to lowercase and replace splitting characters with hyphen.
		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
		$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $str);
		$clean = strtolower(trim($clean, '-'));
		$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

		return $clean;
	}

	/**
	 * Clone after entry save
	 *
	 * @access 	public
	 * @param 	object
	 * @param 	array
	 * @return 	void
	 */
	public function simple_cloner_content_save($entry, $data)
	{
		// Query for entry row in Simple Cloner content created from tab save. If it returns correctly, assign row and convert from object to array.
		$entry_query = ee()->db->query("SELECT * FROM exp_simple_cloner_content WHERE entry_id = " . $data['entry_id']);
		$entry_query = $entry_query->result();

		if(! empty($entry_query))
		{
			$entry_query_array = $entry_query[0];
			$entry_query_array = get_object_vars($entry_query_array);
		}
		// Check that row has been assigned and that the entry cloning flag has been checked.
		if(isset($entry_query_array) == TRUE && $entry_query_array['clone_entry'] != 0)
		{
			// Format date for database re-entry.
			if(! is_string($data['recent_comment_date']))
			{
				$data['recent_comment_date'] = $data['recent_comment_date']->format('Y-m-d');
			}

			// Load channel entries API.
			ee()->load->library('api');
			ee()->legacy_api->instantiate('channel_entries');
			ee()->legacy_api->instantiate('channel_fields');

			// Query for settings in extensions table. If set, process settings values for cloned entry. Tab settings take precedence over default settings.
			$settings_query = ee()->db->select('settings')
				->where('enabled', 'y')
				->where('class', __CLASS__)
				->get('extensions', 1);

			if($settings_query->num_rows())
			{
				$settings_query = unserialize($settings_query->row()->settings);

				if(strlen($entry_query_array['title_suffix']) > 0)
				{
					$data['title'] = $data['title'] . '_' . $entry_query_array['title_suffix'];
				}
				elseif (strlen($settings_query['title_suffix']) > 0)
				{
					$data['title'] = $data['title'] . '_' . $settings_query['title_suffix'];
				}

				if(strlen($entry_query_array['url_title_suffix']) > 0)
				{
					$urlString = $data['url_title'] . '_' . $entry_query_array['url_title_suffix'];
					$data['url_title'] = $this->strToUrl($urlString);
				}
				elseif (strlen($settings_query['url_title_suffix']) > 0)
				{
					$urlString = $data['url_title'] . '_' . $settings_query['url_title_suffix'];
					$data['url_title'] = $this->strToUrl($urlString);
				}

				if($entry_query_array['update_entry_time'] == '1' || $settings_query['update_entry_time'] == 'y')
				{
					$data['entry_date'] = time();
					$data['year'] = date('Y', time());
					$data['month'] = date('m', time());
					$data['day'] = date('d', time());
				}
			}

			// Assign meta settings for new cloned entry and save entry to database.
			$data_template = array(
				'entry_id' => $data['entry_id'],
				'site_id' => $data['site_id'],
				'channel_id' => $data['channel_id'],
				'author_id' => $data['author_id'],
				'forum_topic_id' => $data['forum_topic_id'],
				'ip_address' => $data['ip_address'],
				'title' => $data['title'],
				'url_title' => $data['url_title'],
				'status' => $data['status'],
				'versioning_enabled' => $data['versioning_enabled'],
				'view_count_one' => $data['view_count_one'],
				'view_count_two' => $data['view_count_two'],
				'view_count_three' => $data['view_count_three'],
				'view_count_four' => $data['view_count_four'],
				'allow_comments' => $data['allow_comments'],
				'entry_date' => $data['entry_date'],
				'edit_date' => $data['edit_date'],
				'year' => $data['year'],
				'month' => $data['month'],
				'day' => $data['day'],
				'expiration_date' => $data['expiration_date'],
				'comment_expiration_date' => $data['comment_expiration_date'],
				'sticky' => $data['sticky']
			);

			// Validate for required fields.
			foreach ($data as $key => $value) {
				if(strpos($key, 'field_id') !== FALSE)
				{
					// Get field ID number to query exp_channel_fields table.
					$field_id = explode('field_id_', $key);
					$field_id = $field_id[1];

					// Loop through every ID in data row and select those that are required fields.
					$validate_require = ee()->db->query("SELECT field_id, field_name FROM exp_channel_fields WHERE field_id = " . $field_id . " AND field_required = 'y'");

					if($validate_require->num_rows != 0)
					{
						$validate_require = $validate_require->result();

						foreach ($validate_require as $k => $v) {
							$arrayValue = get_object_vars($v);

							$str_id = 'field_id_' . $arrayValue['field_id'];
							$str_ft = 'field_ft_' . $arrayValue['field_id'];
							$str_name = $arrayValue['field_name'];

							// Assign required values to data_template array so that they can be processed on API save_entry.
							$data_template[$str_id] = $data[$str_id];
						}
					}
				}
			}

			ee()->api_channel_fields->setup_entry_settings($data['channel_id'], $data_template);

			if (! ee()->api_channel_entries->save_entry($data_template, $data['channel_id']))
			{
				$errors = ee()->api_channel_entries->get_errors();
				print_r($errors);
			}

			// Select entry ID of newly cloned entry.
			$query = ee()->db->query("SELECT MAX(entry_id) FROM `exp_channel_titles`");
			$entry_id_of_duplicate = $query->result();

			foreach ($entry_id_of_duplicate as $key => $value) {
				$array = get_object_vars($value);
				foreach ($array as $k => $v) {
					$entry_id_of_duplicate = $v;
				}
			}

			// Remove all meta fields from data array leaving just custom fields.
			foreach ($data as $key => $value) {
				if(!array_key_exists($key, $data_template))
				{
					if(preg_match('/field_ft_[0-9]/', $key) == 0 && preg_match('/field_id_[0-9]/', $key) == 0)
					{
						unset($data[$key]);
					}
				}
			}

			// Check for Seo_lite module and if it exists, create a row for this entry in the seolite_content table.
			// There is a known bug regarding interaction with the Seolite module when deleting cloned entries. This should soon be resolved.
			// This should also be expanded in a later version to copy over the values of the values of the cloned entry for the Seolite module.

			$seolite = ee()->db->query("SELECT * FROM exp_modules WHERE module_name = 'Seo_lite'");

			if($seolite->num_rows() != 0)
			{
				ee()->db->insert(
					'seolite_content',
					array(
						'entry_id'	=>	$entry_id_of_duplicate,
						'site_id'	=>	$data['site_id'],
						'title'		=>	'',
						'keywords'	=>	'',
						'description'	=>	'',
					)
				);
			}

			// Update cloned entry with custom field data.

			ee()->api_channel_entries->update_entry($entry_id_of_duplicate, $data);

			//carry over the categories

			$cats = ee()->db->query('SELECT * FROM exp_category_posts WHERE entry_id = '.$data['entry_id']);

			if ($cats->num_rows != 0){
				$all_cats = $cats->result();
				foreach($all_cats as $kee => $vals){
					$prop = get_object_vars($vals);

					$prop['entry_id'] = $entry_id_of_duplicate;
					ee()->db->insert('category_posts', $prop);

				}
			}

			$ansel = ee('Addon')->get('ansel');
			$assets = ee('Addon')->get('assets');

			if ($ansel && $ansel->isInstalled()) {
				// Ansel support
				$ansel_images = ee()->db->query('SELECT * FROM exp_ansel_images WHERE content_id = '.$data['entry_id'].' AND content_type = "channel"');

				$this->duplicate_ansel($ansel_images, $entry_id_of_duplicate);
			}

			if ($assets && $assets->isInstalled()) {
				//assets support alone
				$assets_selections = ee()->db->query('SELECT * FROM exp_assets_selections WHERE entry_id = '.$data['entry_id'].' AND content_type IS NULL');

				if ($assets_selections->num_rows != 0){
					$all_assets = $assets_selections->result();
					foreach($all_assets as $kee => $vals) {
						$prop = get_object_vars($vals);
						$prop['entry_id'] = $entry_id_of_duplicate;
						ee()->db->insert('assets_selections', $prop);
					}
				}
			}

			// Simple Cloner bug - attempted fix for Bloqs compatibility.

			// 'GRID' - DONE (Relationship field not done in grid)
			// 'BLOQS' -- DONE (Relationship field not done in bloqs)
			// 'RELATIONSHIP' -- DONE

			foreach($data as $key => $value) {
				if(strpos($key, 'field_id') !== FALSE)
				{
					// Get field ID number to query exp_channel_fields table.
					$field_id = explode('field_id_', $key);
					$field_id = $field_id[1];

					$grid_fields = ee()->db->query("SELECT field_id, field_name FROM exp_channel_fields WHERE field_id = " . $field_id . " AND field_type = 'grid'");

					if($grid_fields->num_rows != 0)
					{
						$grid_fields = $grid_fields->result();

						foreach($grid_fields as $k => $v) {
							$arrayValue = get_object_vars($v);

							$grid_id = $arrayValue['field_id'];
							$grid_data = ee()->db->query("SELECT * FROM exp_channel_grid_field_" . $grid_id . " WHERE entry_id = ". $data['entry_id']);

							$grid = $grid_data->result();


							foreach($grid as $gridKey => $gridRow) {
								$row = get_object_vars($gridRow);
								//Removed EXP from string because not all users use EXP and ee()->db->insert prepends your prefix anyhow --peter
								$table_id = 'channel_grid_field_' . $grid_id;
								$row['entry_id'] = $entry_id_of_duplicate;
								$save_row_id = $row['row_id'];
								$row['row_id'] = 0;
								// Loop all rows for grid and insert new rows for duplicated entry. Will have to do something similar for bloqs. --peter
								ee()->db->insert($table_id, $row);

								$new_row_id = ee()->db->insert_id();

								if ($assets && $assets->isInstalled()) {
									$assets_selections = ee()->db->query('SELECT * FROM exp_assets_selections WHERE entry_id = '.$data['entry_id'].' AND content_type = "grid" AND row_id ='.$save_row_id);
									if ($assets_selections->num_rows != 0){
										$all_assets = $assets_selections->result();
										foreach($all_assets as $kee => $vals){
											$prop = get_object_vars($vals);
											$prop['entry_id'] = $entry_id_of_duplicate;
											$prop['row_id'] = $new_row_id;
											ee()->db->insert('assets_selections', $prop);
										}
									}
								}

								if ($ansel && $ansel->isInstalled()) {
									// Ansel support
									$ansel_images = ee()->db->query('SELECT * FROM exp_ansel_images WHERE content_id = '.$data['entry_id'].' AND content_type = "grid" AND row_id ='.$save_row_id);
									$this->duplicate_ansel($ansel_images, $entry_id_of_duplicate, $new_row_id);
								}
							}
						}
					}
				}
			}

			foreach($data as $key => $value) {
				if(strpos($key, 'field_id') !== FALSE)
				{
					// Get field ID number to query exp_channel_fields table.
					$field_id = explode('field_id_', $key);
					$field_id = $field_id[1];

					$grid_fields = ee()->db->query("SELECT field_id, field_name FROM exp_channel_fields WHERE field_id = " . $field_id . " AND field_type = 'relationship'");

					if($grid_fields->num_rows != 0)
					{
						$grid_fields = $grid_fields->result();

						foreach($grid_fields as $k => $v) {
							$arrayValue = get_object_vars($v);

							$grid_id = $arrayValue['field_id'];
							$grid_data = ee()->db->query("SELECT * FROM exp_relationships WHERE parent_id = ". $data['entry_id']);

							$grid = $grid_data->result();


							foreach($grid as $gridKey => $gridRow) {
								$row = get_object_vars($gridRow);
								$row['parent_id'] = $entry_id_of_duplicate;
								$row['relationship_id'] = 0;
								// Loop all rows for grid and insert new rows for duplicated entry. Will have to do something similar for bloqs. --peter
								ee()->db->insert('relationships', $row);
							}
						}
					}
				}
			}

			foreach($data as $key => $value) {
				if(strpos($key, 'field_id') !== FALSE)
				{
					// Get field ID number to query exp_channel_fields table.
					$field_id = explode('field_id_', $key);
					$field_id = $field_id[1];

					$grid_fields = ee()->db->query("SELECT field_id, field_name FROM exp_channel_fields WHERE field_id = " . $field_id . " AND field_type = 'bloqs'");

					if($grid_fields->num_rows != 0)
					{
						$grid_fields = $grid_fields->result();

						foreach($grid_fields as $grid_field) {
							$arrayValue = get_object_vars($grid_field);

							$grid_id = $arrayValue['field_id'];
							$grid_data = ee()->db->query("SELECT * FROM exp_blocks_block WHERE entry_id = ". $data['entry_id']. " AND field_id = " . $field_id);

							$grid = $grid_data->result();

							foreach($grid as $gridKey => $gridRow) {

								ee()->db->insert('blocks_block', array(
									'blockdefinition_id' => $gridRow->blockdefinition_id,
									'site_id' => $gridRow->site_id,
									'entry_id' => $entry_id_of_duplicate,
									'field_id' => $gridRow->field_id,
									'order' => $gridRow->order
								));

								$save_row_id = $gridRow->id;
								$latest_id = ee()->db->insert_id();

								if ($assets && $assets->isInstalled()) {
									$assets_selections = ee()->db->query('SELECT * FROM exp_assets_selections WHERE entry_id = '.$data['entry_id'].' AND content_type = "grid" AND row_id ='.$save_row_id);
									if ($assets_selections->num_rows != 0){
										$all_assets = $assets_selections->result();
										foreach($all_assets as $kee => $vals){
											$prop = get_object_vars($vals);
											$prop['entry_id'] = $entry_id_of_duplicate;
											$prop['row_id'] = $latest_id;
											ee()->db->insert('assets_selections', $prop);
										}
									}
								}

								if ($ansel && $ansel->isInstalled()) {
									// Ansel support
									$ansel_images = ee()->db->query('SELECT * FROM exp_ansel_images WHERE content_id = '.$data['entry_id'].' AND content_type = "blocks" AND row_id ='.$save_row_id);
									$this->duplicate_ansel($ansel_images, $entry_id_of_duplicate, $latest_id);
								}

								$bloqs_content_rows = ee()->db->query("SELECT * FROM exp_blocks_atom WHERE block_id = ". $gridRow->id);
								$result = $bloqs_content_rows->result();

								foreach($result as $ki => $val) {
									ee()->db->insert('blocks_atom', array(
										'block_id' => $latest_id,
										'atomdefinition_id' => $val->atomdefinition_id,
										'data' => $val->data
									));
								}

								// Relationship in bloqs support
								$grid_data = ee()->db->query("SELECT * FROM exp_relationships WHERE parent_id = ". $data['entry_id']);

								$grid = $grid_data->result();

								foreach($grid as $gridKey => $gridRow) {
									$row = get_object_vars($gridRow);
									$row['parent_id'] = $entry_id_of_duplicate;
									$row['grid_row_id'] = $latest_id;
									unset($row['relationship_id']);

									// Loop all rows for grid and insert new rows for duplicated entry. Will have to do something similar for bloqs. --peter
									ee()->db->insert('relationships', $row);
								}
							}
						}
					}
				}
			}
			// Store Field
			foreach($data as $key => $value) {
				if(strpos($key, 'field_id') !== FALSE)
				{
					// Get field ID number to query exp_channel_fields table.
					$field_id = explode('field_id_', $key);
					$field_id = $field_id[1];

					$store_fields = ee()->db->query("SELECT field_id, field_name FROM exp_channel_fields WHERE field_id = " . $field_id . " AND field_type = 'store'");

					if($store_fields->num_rows != 0)
					{
						$store_fields = $store_fields->result();

						$modifiers = $product_mod_ids = $options = $stocks = array();

						//echo "<pre>";print_r($store_fields);exit;

						$exp_store_products = ee()->db->from("exp_store_products")->where('entry_id', $data['entry_id'])->get()->row_array();
						$exp_store_products['entry_id'] = $entry_id_of_duplicate;
						ee()->db->insert('exp_store_products', $exp_store_products);

						$exp_store_product_modifiers = ee()->db->from("exp_store_product_modifiers")->where('entry_id', $data['entry_id'])->get()->result_array();
						foreach ($exp_store_product_modifiers as $key => $row) {
							$exp_store_product_modifier = $row;
							$exp_store_product_modifier['entry_id'] = $entry_id_of_duplicate;
							unset($exp_store_product_modifier['product_mod_id']);
							ee()->db->insert('exp_store_product_modifiers', $exp_store_product_modifier);
							$modifiers[$row['product_mod_id']] = ee()->db->insert_id();
							$product_mod_ids[] = $row['product_mod_id'];
						}

						$exp_store_product_options = ee()->db->from("exp_store_product_options")->where_in('product_mod_id', $product_mod_ids)->get()->result_array();
						foreach ($exp_store_product_options as $key => $row) {
							$exp_store_product_option = $row;
							$exp_store_product_option['product_mod_id'] = $modifiers[$row['product_mod_id']];
							unset($exp_store_product_option['product_opt_id']);
							ee()->db->insert('exp_store_product_options', $exp_store_product_option);
							$options[$row['product_opt_id']] = ee()->db->insert_id();
						}

						$exp_store_stocks = ee()->db->from("exp_store_stock")->where('entry_id', $data['entry_id'])->get()->result_array();
						foreach ($exp_store_stocks as $key => $row) {
							$exp_store_stock = $row;
							$exp_store_stock['entry_id'] = $entry_id_of_duplicate;
							unset($exp_store_stock['id']);
							ee()->db->insert('exp_store_stock', $exp_store_stock);
							$stocks[$row['id']] = ee()->db->insert_id();
						}

						$exp_store_stock_options = ee()->db->from("exp_store_stock_options")->where('entry_id', $data['entry_id'])->get()->result_array();
						foreach ($exp_store_stock_options as $key => $row) {
							$exp_store_stock_option = $row;
							$exp_store_stock_option['entry_id'] = $entry_id_of_duplicate;
							$exp_store_stock_option['stock_id'] = $stocks[$row['stock_id']];
							$exp_store_stock_option['product_mod_id'] = $modifiers[$row['product_mod_id']];
							$exp_store_stock_option['product_opt_id'] = $options[$row['product_opt_id']];
							unset($exp_store_stock_option['id']);
							ee()->db->insert('exp_store_stock_options', $exp_store_stock_option);
						}


					}
				}
			}
			// End Store Field

			ee()->db->update(
				'exp_simple_cloner_content',
				array(
					'clone_entry'	=>	'0'
				),
				array(
					'entry_id'	=>	$data['entry_id']
				)
			);
		} else {
			return FALSE;
		}
	}

	/**
	 * [generate_new_ansel_image description]
	 * @param  [type] $ansel_data [description]
	 * @return [type]             [description]
	 */
	private function generate_new_ansel_image($ansel_data, $original_ansel_id, $new_ansel_id)
	{
		// get the server path of the images
		$upload_prefs = ee()->db->select('server_path')
			->from('upload_prefs')
			->where('id', preg_replace('/\D/', '', $ansel_data['upload_location_id']))
			->get()->row_array();

		$server_path = $upload_prefs['server_path'];

		// Find all the folders that the previous ansel field used
		$assets_folders = ee()->db->select('*')
			->from('assets_folders')
			->where('folder_name', $original_ansel_id)
			->get();

		// Loop through each folder and make a new copy of it
		foreach($assets_folders->result() as $assets_folder_object)
		{
			$assets_folder = get_object_vars($assets_folder_object);

			// get the old and new image paths
			$original_path = $server_path . $assets_folder['full_path'];
			$new_path = str_replace($original_ansel_id, $new_ansel_id, $original_path);

			// create the new folder and copy all of the original contents to it
			$this->recurse_copy($original_path, $new_path);

			// Create a new array with the updated data in it, and insert it into the assets_folders table
			$new_assets_folder = $assets_folder;
			$new_assets_folder['folder_name'] = $new_ansel_id;
			$new_assets_folder['full_path'] = str_replace($original_ansel_id, $new_ansel_id, $new_assets_folder['full_path']);
			unset($new_assets_folder['folder_id']);

			ee()->db->insert('assets_folders', $new_assets_folder);
		}

		return true;
	}

	/**
	 * Pass ansel images that need to be duplicated and id's, and duplicate it
	 * @param  [type] $ansel_images          [description]
	 * @param  [type] $entry_id_of_duplicate [description]
	 * @param  [type] $new_row_id            [description]
	 * @return [type]                        [description]
	 */
	private function duplicate_ansel($ansel_images, $entry_id_of_duplicate, $new_row_id = null)
	{
		foreach($ansel_images->result() as $ansel_object)
		{
			// Get data as array, set the new entry_id, unset the original id
			$ansel_data = get_object_vars($ansel_object);
			$ansel_data['content_id'] = $entry_id_of_duplicate;

			if($new_row_id)
			{
				$ansel_data['row_id'] = $new_row_id;
				// $ansel_data['col_id'] = ??? * TODO do I need to update/change this?
			}

			$original_ansel_id = $ansel_data['id'];
			unset($ansel_data['id']);

			ee()->db->insert('ansel_images', $ansel_data);
			$new_ansel_id = ee()->db->insert_id();

			// Now generate the new ansel image
			$this->generate_new_ansel_image($ansel_data, $original_ansel_id, $new_ansel_id);
		}
	}

	/**
	 * Copies one folder to another location (recursively)
	 * @param  [type] $src [description]
	 * @param  [type] $dst [description]
	 * @return [type]      [description]
	 */
	private function recurse_copy($src,$dst)
	{
		$dir = opendir($src);
		@mkdir($dst);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					$this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
				}
				else {
					copy($src . '/' . $file,$dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}

}
