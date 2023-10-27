<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Module Control Panel File
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2017 Causing Effect
 * @license		http://docs.causingeffect.com/expressionengine/ce-cache/license-agreement.html
 * @link		https://www.causingeffect.com
 */
class Ce_cache_mcp
{
	private $name = 'ce_cache';
	private static $theme_folder_url;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		//include CE Cache Utilities and Drivers
		$this->include_library('Ce_cache_utils');
		$this->include_library('Ce_cache_drivers');

		//set the CE Cache theme folder URL
		if (empty(self::$theme_folder_url))
		{
			self::$theme_folder_url = defined('URL_THIRD_THEMES') ? URL_THIRD_THEMES : ee()->config->slash_item('theme_folder_url') . 'third_party/';
			self::$theme_folder_url .= 'ce_cache/';
		}
	}

	/**
	 * Index Function
	 *
	 * @return string
	 */
	public function index()
	{
		ee()->cp->add_to_head(PHP_EOL . '<link rel="stylesheet" href="' . self::$theme_folder_url . '/css/styles.min.css">');

		//view data
		$data = array(
			'module' => $this->name,
			'active_drivers' => Ce_cache_drivers::get_active_driver_names(true),
			'supported_drivers' => Ce_cache_drivers::get_supported_driver_names(),
			'site' => Ce_cache_utils::get_site_prefix(),
			'disabled' => Ce_cache_utils::ee_string_to_bool(Ce_cache_utils::determine_setting('off', 'no', '', true, false)),
			'is_msm' => Ce_cache_utils::ee_string_to_bool(ee()->config->item('multiple_sites_enabled'))
		);

		//render the view
		return $this->render(array(
			'view' => 'index',
			'title' => lang('ce_cache_drivers'),
			'breadcrumbs' => array(),
			'vars' => $data
		));
	}

	/**
	 * Individual driver page.
	 *
	 * @param string $driver
	 * @return array|string
	 */
	public function driver($driver = '')
	{
		ee()->cp->add_to_head(PHP_EOL . '<link rel="stylesheet" href="' . self::$theme_folder_url . '/css/styles.min.css">');

		//back link
		$back_link = '<div><a class="btn" href="'.Ce_cache_utils::cp_url().'">'.lang('ce_cache_back_to').' '.lang('ce_cache_module_home').'</a></div>';

		//make sure the driver is set and valid
		if (empty($driver))
		{
			return $this->show_inline_error(lang('ce_cache_error_no_driver').PHP_EOL.$back_link);
		}
		else if (!in_array($driver, Ce_cache_drivers::get_valid_drivers()) || !Ce_cache_drivers::is_supported($driver))
		{
			return $this->show_inline_error(lang('ce_cache_error_invalid_driver').PHP_EOL.$back_link);
		}

		//view data
		$data = array(
			'driver' => $driver,
			'is_msm' => Ce_cache_utils::ee_string_to_bool(ee()->config->item('multiple_sites_enabled')),
			'disabled' => Ce_cache_utils::ee_string_to_bool(Ce_cache_utils::determine_setting('off', 'no', '', true, false)),
			'active_drivers' => Ce_cache_drivers::get_active_driver_names(true)
		);

		//return the index view
		return $this->render(array(
			'view' => 'driver',
			'title' => lang("ce_cache_driver_{$driver}"),
			'activeDriver' => $driver,
			'breadcrumbs' => array(),
			'vars' => $data
		));
	}

	/**
	 * View the cache items for the specified driver.
	 * This method expects the 'driver' get_post variable, and
	 * the 'offset' variable if paginating and the offset is not 0.
	 *
	 * @return string
	 */
	public function view_items($driver = '')
	{
		ee()->cp->add_to_head(PHP_EOL . '<link rel="stylesheet" href="' . self::$theme_folder_url . '/css/styles.min.css">');

		//back link
		$back_link = '<div><a class="btn" href="'.Ce_cache_utils::cp_url().'">'.lang('ce_cache_back_to').' '.lang('ce_cache_module_home').'</a></div>';

		//make sure the driver is set and valid
		if (empty($driver))
		{
			return $this->show_inline_error(lang('ce_cache_error_no_driver').PHP_EOL.$back_link);
		}
		else if (!in_array($driver, Ce_cache_drivers::get_valid_drivers()) || !Ce_cache_drivers::is_supported($driver))
		{
			return $this->show_inline_error(lang('ce_cache_error_invalid_driver').PHP_EOL.$back_link);
		}

		//the prefix for this site
		$prefix = Ce_cache_utils::get_site_prefix();

		//get the current site
		$site_id = ee()->config->item('site_id');

		//get the secret
		$secret = Ce_cache_utils::get_secret();

		//create the ajax urls
		$ajax_url_get_level = ee()->functions->create_url(QUERY_MARKER.'ACT='.ee()->cp->fetch_action_id(__CLASS__, 'ajax_get_level'));
		$ajax_url_delete = ee()->functions->create_url(QUERY_MARKER.'ACT='.ee()->cp->fetch_action_id(__CLASS__, 'ajax_delete'));

		//the home URL
		$home = '/' . ltrim(Ce_cache_utils::cp_url(), '/');

		//switch to https if the control panel is running it
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
		{
			$ajax_url_get_level = str_replace('http://', 'https://', $ajax_url_get_level);
			$ajax_url_delete = str_replace('http://', 'https://', $ajax_url_delete);
		}

		//add the js
		$vue = (getenv('ENV_CE_CACHE_DEV') == '1') ? 'vue.js' : 'vue.min.js';
		ee()->cp->add_to_foot(PHP_EOL . '<script type="text/javascript" src="'.self::$theme_folder_url.'js/'.$vue.'"></script>
		<script type="text/javascript" src="'.self::$theme_folder_url.'js/jquery.jsonp-2.3.1.min.js"></script>
		<script type="text/javascript">
			var CE_CACHE_VIEW_ITEMS_SETTINGS = {
				site_id : "' . $site_id . '",
				prefix : "' . $prefix . '",
				urls : {
					getLevel : "' . $ajax_url_get_level . '",
					deleteItem : "' . $ajax_url_delete . '",
					home : "' . $home . '"
				},
				driver : "' . $driver . '",
				lang : {
					unknown_error : "' . lang('ce_cache_ajax_unknown_error') . '",
					no_items_found : "' . lang('ce_cache_ajax_no_items_found') . '",
					ajax_error : "' . lang('ce_cache_ajax_error') . '",
					ajax_error_title : "' . lang('ce_cache_ajax_error_title') . '",
					install_error : "' . lang('ce_cache_ajax_install_error') . '",

					delete_child_items_confirmation : "' . lang('ce_cache_ajax_delete_child_items_confirmation') . '",
					delete_child_items_button : "' . lang('ce_cache_ajax_delete_child_items_button') . '",
					delete_child_items_refresh : "' . lang('ce_cache_ajax_delete_child_items_refresh') . '",
					delete_child_items_refresh_time : "' . lang('ce_cache_ajax_delete_child_items_refresh_time') . '",
					delete_child_item_confirmation : "' . lang('ce_cache_ajax_delete_child_item_confirmation') . '",
					delete_child_item_button : "' . lang('ce_cache_ajax_delete_child_item_button') . '",
					delete_child_item_refresh : "' . lang('ce_cache_ajax_delete_child_item_refresh') . '",
					cancel : "' . lang('ce_cache_ajax_cancel') . '"
				},
				secret : "' . $secret . '"
			};
		</script>
		<script type="text/javascript" src="'.self::$theme_folder_url.'js/ce_cache_view_items.min.js"></script>');

		//view data
		$data = array(
			'module' => $this->name,
			'driver' => $driver,
			'back_link' => $back_link,
			'title' => sprintf(lang('ce_cache_view_driver_items'), lang('ce_cache_driver_'.$driver))
		);

		//return the index view
		return $this->render(array(
			'view' => 'view_items',
			'title' => sprintf(lang('ce_cache_driver_items'), lang('ce_cache_driver_'.$driver)),
			'activeDriver' => $driver,
			'breadcrumbs' => array(),
			'vars' => $data
		));
	}

	/**
	 * Makes sure the callback is not malicious and that the secret matches.
	 *
	 * @param string $callback
	 * @param string $secret
	 */
	private function check_ajax_request($callback, $secret)
	{
		if (preg_match('/\W/', $callback) || $secret !== Ce_cache_utils::get_secret()) //if the callback contains a non-word character (possible XSS attack) or if the secret doesn't match, let's bail
		{
			header('HTTP/1.1 400 Bad Request');
			exit();
		}
	}

	/**
	 * Get a level of items.
	 */
	public function ajax_get_level()
	{
		//get the callback and secret, and check the request
		$callback = ee()->input->get('callback', true);
		$secret = ee()->input->get('secret', true);
		$this->check_ajax_request($callback, $secret);

		//ajax header
		header('Content-type: application/json');

		//load the language file
		ee()->lang->loadfile('ce_cache');

		//the ajax response
		$response = array(
			'success' => true
		);

		//get the path
		$path = ee()->input->get('path', true);
		if (empty($path)) //the item path was not received
		{
			$response['success'] = false;
			$response['message'] = lang('ce_cache_error_invalid_path');
			echo $callback.'('.json_encode($response).')';
			exit();
		}

		//get the prefix
		$prefix = ee()->input->get('prefix', true);
		if (empty($prefix)) //the item path was not received
		{
			$response['success'] = false;
			$response['message'] = lang('ce_cache_error_invalid_path');
			echo $callback.'('.json_encode($response).')';
			exit();
		}

		//get the driver
		$driver = ee()->input->get('driver', true);
		if (!in_array($driver, Ce_cache_drivers::get_valid_drivers())) //the driver is not valid
		{
			$response['success'] = false;
			$response['message'] = lang('ce_cache_error_invalid_driver');
			echo $callback.'('.json_encode($response).')';
			exit();
		}

		$drivers = Ce_cache_drivers::factory($driver);

		//make sure the driver is valid
		if (!Ce_cache_drivers::is_supported($driver))
		{
			$response['success'] = false;
			$response['message'] = lang('ce_cache_error_invalid_driver');
		}

		$class = $drivers[0];

		//attempt to get the items for this site
		$items = $class->get_level($prefix . ltrim($path, '/'));

		//if items are empty, make them an array
		if (empty($items))
		{
			$items = array();
		}

		if ($class->get_all_is_array()) //some drivers already come with all of the meta data (more efficient due to how they work)
		{
			foreach ($items as $index => $item)
			{
				if (!$this->ends_with($item['id'], '/')) //cache item
				{
					$items[$index]['id_full'] = ltrim($path, '/') . $item['id'];
					$items[$index]['type'] = 'file';
				}
				else //directory
				{
					$items[$index]['id_full'] = ltrim($path, '/') . $item['id'];
					$items[$index]['type'] = 'folder';
					$items[$index]['expiry'] = '';
					$items[$index]['made'] = '';
					$items[$index]['ttl'] = '';
					$items[$index]['ttl_remaining'] = '';
					$items[$index]['size'] = '';
					$items[$index]['size_raw'] = '';
				}
			}
		}
		else //driver that returns just the item id
		{
			$temps = $items;
			$folders = array();
			$files = array();

			//get the meta data without the content for each item
			foreach ($temps as $temp)
			{
				if (!$this->ends_with($temp, '/')) //cache item
				{
					//attempt to get the items for this site
					$data = $class->meta($prefix . ltrim($path, '/') . $temp, false);
					if ($data !== false)
					{
						$data['id'] = $temp;
						$data['id_full'] = ltrim($path, '/') . $temp;
						$data['type'] = 'file';
						$files[] = $data;
					}
				}
				else //directory
				{
					$folders[] = array(
						'id' => $temp,
						'id_full' => ltrim($path, '/') . $temp,
						'type' => 'folder',
						'expiry' => '',
						'made' => '',
						'ttl' => '',
						'ttl_remaining' => '',
						'size' => '',
						'size_raw' => ''
					);
				}
			}

			$items = array_merge($folders, $files);
			unset($temps, $folders, $files);
		}

		//load the CE Cache control panel library
		ee()->load->library('Ce_cache_cp');

		$response['data'] = array(
			'items' => ee()->ce_cache_cp->prep_items($items),
			'breadcrumbs' => ee()->ce_cache_cp->prep_breadcrumbs($driver, $path)
			//'items_html' => ee()->ce_cache_cp->items_to_html_list($items),
			//'breadcrumbs_html' => ee()->ce_cache_cp->breadcrumb_html($driver, $path)
		);
		unset($items);

		echo $callback.'('.json_encode($response).')';
		exit();
	}

	/**
	 * Delete a child.
	 */
	public function ajax_delete()
	{
		//get the callback and secret, and check the request
		$callback = ee()->input->get('callback', true);
		$secret = ee()->input->get('secret', true);
		$this->check_ajax_request($callback, $secret);

		//ajax header
		header('Content-type: application/json');

		//load the language file
		ee()->lang->loadfile('ce_cache');

		//the ajax response
		$response = array(
			'success' => true
		);

		//get the path
		$path = ee()->input->get('path', true);
		if (empty($path)) //the item path was not received
		{
			$response['success'] = false;
			$response['message'] = lang('ce_cache_error_invalid_path');
			echo $callback.'('.json_encode($response).')';
			exit();
		}

		//get the prefix
		$prefix = ee()->input->get('prefix', true);
		if (empty($prefix)) //the item path was not received
		{
			$response['success'] = false;
			$response['message'] = lang('ce_cache_error_invalid_path');
			echo $callback.'('.json_encode($response).')';
			exit();
		}

		//get the driver
		$driver = ee()->input->get('driver', true);
		if (!in_array($driver, Ce_cache_drivers::get_valid_drivers())) //the driver is not valid
		{
			$response['success'] = false;
			$response['message'] = lang('ce_cache_error_invalid_driver');
			echo $callback.'('.json_encode($response).')';
			exit();
		}

		//get the path
		$refresh = ee()->input->get('refresh', true);
		$refresh = (empty($refresh) || $refresh == 'false') ? false : true;

		$drivers = Ce_cache_drivers::factory($driver);

		//make sure the driver is valid
		if (!Ce_cache_drivers::is_supported($driver))
		{
			$response['success'] = false;
			$response['message'] = lang('ce_cache_error_invalid_driver');
		}

		$class = $drivers[0];

		if (!$this->ends_with($path, '/')) //if the item is not a directory
		{
			//attempt to delete the item
			if ($class->delete($prefix . ltrim($path, '/')) === false)
			{
				$response['success'] = false;
				$response['message'] = sprintf(lang("ce_cache_error_deleting_item"), $path);
				echo $callback.'('.json_encode($response).')';
				exit();
			}

			if ($refresh !== false && (substr($path, 0, 5) == 'local' || substr($path, 0, 6) == 'static'))
			{
				//determine whether this item is local or static
				$is_local = strpos($path, 'local/') === 0;

				//create the URL
				$url = ee()->functions->fetch_site_index(0, 0);

				//trim the 'local/' or 'static/' from the beginning of the path
				$path = substr($path, $is_local ? 6 : 7);

				//find the last '/'
				$last_slash = strrpos($path, '/');

				//if a last '/' was found, get the path up to that point (remove the cache id name)
				$path = ($last_slash === false) ? '' : substr($path, 0, $last_slash);

				//load the CE Cache Break class
				$this->include_library('Ce_cache_break');

				//instantiate the class break and call the break cache method
				$cache_break = new Ce_cache_break();

				//make sure that allow_url_fopen is set to true if permissible
				@ini_set('allow_url_fopen', true);
				//some servers will not accept the asynchronous requests if there is no user_agent
				@ini_set('user_agent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:5.0) Gecko/20100101');

				//try to request the page, first using cURL, then falling back to fsocketopen
				if (!$cache_break->curl_it($url . $path)) //send a cURL request
				{
					//attempt a fsocketopen request
					$cache_break->fsockopen_it($url . $path);
				}
			}
		}
		else //the item is a directory
		{
			//attempt to get the items for the path
			if (false === $items = $class->get_all($prefix . ltrim($path, '/')))
			{
				$response['success'] = false;
				$response['message'] = lang('ce_cache_error_getting_items');
				echo $callback.'('.json_encode($response).')';
				exit();
			}

			//we've got items
			$errors = array();

			if ($refresh) //delete and refresh
			{
				$refresh_time = ee()->input->get('refresh_time', true);

				if (empty($refresh_time) || !is_numeric($refresh_time))
				{
					$refresh_time = 0;
				}

				//make sure that allow_url_fopen is set to true if permissible
				@ini_set('allow_url_fopen', true);
				//some servers will not accept the asynchronous requests if there is no user_agent
				@ini_set('user_agent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:5.0) Gecko/20100101');

				//create the URL
				$url = ee()->functions->fetch_site_index(0, 0);

				//load the CE Cache Break class
				$this->include_library('Ce_cache_break');

				//instantiate the class break
				$cache_break = new Ce_cache_break();

				//loop through the items, and delete and refresh each one
				foreach ($items as $item)
				{
					@sleep($refresh_time);

					//determine whether this item is local or static
					$is_local = strpos($path, 'local') === 0;

					$url_string = $prefix.ltrim($path, '/').(($class->get_all_is_array()) ? $item['id'] : $item);

					//delete the item
					if ($class->delete($url_string) === false)
					{
						$errors[] = sprintf(lang('ce_cache_error_deleting_item'), $url_string);
					}

					//remove the prefix
					$url_string = substr($url_string, strlen($prefix));

					//trim the 'local/' from the beginning of the path
					$url_string = substr($url_string, $is_local ? 6 : 7);

					//find the last '/'
					$last_slash = strrpos($url_string, '/');

					//if a last '/' was found, get the path up to that point
					$url_string = ($last_slash === false) ? '' : substr($url_string, 0, $last_slash);

					//try to request the page, first using cURL, then falling back to fsocketopen
					if (!$cache_break->curl_it($url.$url_string)) //send a cURL request
					{
						//attempt a fsocketopen request
						$cache_break->fsockopen_it($url.$url_string);
					}
				}
			}
			else //just delete, don't refresh
			{
				foreach ($items as $item)
				{
					$t = $prefix.ltrim($path, '/').(($class->get_all_is_array()) ? $item['id'] : $item);

					if ($class->delete($t) === false)
					{
						$errors[] = sprintf(lang('ce_cache_error_deleting_item'), $t);
					}
				}
			}

			unset($items);

			//show the errors if there were any
			if (count($errors) > 0)
			{
				$response['success'] = false;
				$response['message'] = implode("\n", $errors);
				echo $callback.'('.json_encode($response).')';
				exit();
			}
		}

		echo $callback.'('.json_encode($response).')';
		exit();
	}

	/**
	 * View a cache item by id. This method expects the 'item' and 'driver' get_post variables.
	 *
	 * @return string
	 */
	public function view_item($driver = '')
	{
		ee()->cp->add_to_head(PHP_EOL . '<link rel="stylesheet" href="' . self::$theme_folder_url . '/css/styles.min.css">');

		ee()->cp->add_to_foot(PHP_EOL . '<script type="text/javascript" src="' . self::$theme_folder_url . 'js/highlight.pack.js"></script><script>$(function() {
		$("#ce_cache_code_holder, code").each(function(i,e){hljs.highlightBlock(e);});
		});</script>');

		//load classes/helpers
		ee()->load->helper(array('url'));

		if (empty($driver))
		{
			return '<p>' . lang("ce_cache_error_no_driver") . '</p>';
		}
		else if (!in_array($driver, Ce_cache_drivers::get_valid_drivers()))
		{
			return '<p>' . lang("ce_cache_error_invalid_driver") . '</p>';
		}

		//grab the item from the get/post data
		$item = ee()->input->get_post('item', true);

		if (empty($item))
		{
			return '<p>' . lang("ce_cache_error_no_item") . '</p>';
		}

		$item = urldecode($item);

		//make sure the driver is valid
		if (!Ce_cache_drivers::is_supported($driver))
		{
			return $this->show_inline_error(lang('ce_cache_error_invalid_driver'));
		}

		$drivers = Ce_cache_drivers::factory($driver);

		$class = $drivers[0];

		//attempt to get the item metadata
		if (false === $meta = $class->meta($item))
		{
			return $this->show_inline_error(lang('ce_cache_error_getting_meta'));
		}

		//load the date helper
		ee()->load->helper('date');

		//the time format string
		$time_string = '%Y-%m-%d - %h:%i:%s %a';

		//determine and set the expiry
		$expiry = ($meta['expiry'] == 0) ? '&infin;' : mdate($time_string, $meta['expiry']);
		$ttl = $meta['ttl'];
		$ttl_remaining = $meta['ttl_remaining'];
		$made = mdate($time_string, $meta['made']);
		$content = $meta['content'];
		$size = $meta['size'];
		$size_raw = $meta['size_raw'];

		unset($meta);

		//get item's tags
		$tags = array();
		$result = ee()->db->query('SELECT tag FROM exp_ce_cache_tagged_items WHERE item_id = ?', array($item));
		if ($result->num_rows() > 0)
		{
			$rows = $result->result_array();
			foreach ($rows as $row)
			{
				$tags[] = $row['tag'];
			}
			unset($rows);
		}
		$result->free_result();

		//view data
		$data = array(
			'module' => $this->name,
			'item' => $item,
			'made' => $made,
			'expiry' => $expiry,
			'ttl' => $ttl,
			'size' => $size,
			'size_raw' => $size_raw,
			'ttl_remaining' => $ttl_remaining,
			'content' => $content,
			'prefix' => Ce_cache_utils::get_site_prefix(),
			'tags' => $tags
		);

		//return the index view
		return $this->render(array(
			'view' => 'view_item',
			'title' => lang('ce_cache_view_item'),
			'activeDriver' => $driver,
			'breadcrumbs' => array(
				Ce_cache_utils::cp_url('view_items/'.$driver) => lang('ce_cache_view_items')
			),
			'vars' => $data
		));
	}

	/**
	 * Method to clear the cache for a specific driver
	 *
	 * @return string
	 */
	public function clear_cache($driver = '', $site_only = 'y')
	{
		ee()->cp->add_to_head(PHP_EOL . '<link rel="stylesheet" href="' . self::$theme_folder_url . '/css/styles.min.css">');

		$site_only = ($site_only == 'y');

		//load classes/helpers
		ee()->load->helper(array('form', 'url'));
		ee()->load->library('form_validation');

		if (empty($driver))
		{
			return $this->show_inline_error(lang('ce_cache_error_no_driver'));
		}
		else if (!in_array($driver, Ce_cache_drivers::get_valid_drivers()))
		{
			return $this->show_inline_error(lang('ce_cache_error_invalid_driver'));
		}

		if (ee()->input->post('submit', true) !== false) //the form was submitted
		{
			//make sure the driver is valid
			if (!Ce_cache_drivers::is_supported($driver))
			{
				//redirect back to the main page with the failure message
				ee()->session->set_flashdata('message_failure', lang('ce_cache_error_invalid_driver'));
				return ee()->functions->redirect(Ce_cache_utils::cp_url());
			}

			$drivers = Ce_cache_drivers::factory($driver);

			$class = $drivers[0];

			if ($site_only) //clear only for this site
			{
				$path = Ce_cache_utils::get_site_prefix();

				//attempt to get the items for the path
				if (false === $items = $class->get_all($path))
				{
					//redirect back to the main page with the failure message
					return Ce_cache_utils::redirect_alert(lang('ce_cache_error_getting_items'), 'issue');
				}

				//we've got items
				$errors = array();

				foreach ($items as $item)
				{
					$t = $path . (($class->get_all_is_array()) ? $item['id'] : $item);

					if ($class->delete($t) === false)
					{
						$errors[] = sprintf(lang('ce_cache_error_deleting_item'), $t);
					}
				}

				//show the errors if there were any
				if (count($errors) > 0)
				{
					//redirect back to the main page with the failure message
					return Ce_cache_utils::redirect_alert(implode(PHP_EOL.' ', $errors), 'issue');
				}
			}
			else //attempt to clear the driver cache
			{
				if ($class->clear() === false)
				{
					//redirect back to the main page with the failure message
					return Ce_cache_utils::redirect_alert(lang('ce_cache_error_cleaning_cache'), 'issue');
				}
			}

			//redirect back to the main page with the success message
			return Ce_cache_utils::redirect_alert(lang('ce_cache_clear_cache_success'));
		}

		//view data
		$data = array(
			'module' => $this->name,
			'action_url' => Ce_cache_utils::cp_url('clear_cache/'.$driver.'/'.$site_only),
			'driver' => $driver,
			'back_link' => '<a class="submit" href="'.Ce_cache_utils::cp_url().'">'.lang("ce_cache_back_to").' '.lang('ce_cache_module_home').'</a>',
			'site_only' => $site_only
		);

		//render the view
		return $this->render(array(
			'view' => 'clear_cache',
			'title' => lang('ce_cache_clear_cache'),
			'activeDriver' => $driver,
			'breadcrumbs' => array(),
			'vars' => $data
		));
	}

	/**
	 * Method to clear all caches.
	 *
	 * @return string
	 */
	public function clear_all_caches()
	{
		ee()->cp->add_to_head(PHP_EOL . '<link rel="stylesheet" href="' . self::$theme_folder_url . '/css/styles.min.css">');

		//load classes/helpers
		ee()->load->helper(array('form', 'url'));
		ee()->load->library('form_validation');

		if (ee()->input->post('submit', true) !== false) //the form was submitted
		{
			$classes = Ce_cache_drivers::get_all_driver_classes();

			//set an empty error array
			$errors = array();

			foreach ($classes as $class)
			{
				//attempt to clear the cache
				if ($class->clear() === false)
				{
					//add the error for the current driver
					$errors[] = sprintf(lang("ce_cache_error_cleaning_driver_cache"), lang('ce_cache_driver_' . $class->name()));
				}
			}

			//if the error string is not blank, return the error(s)
			if (count($errors) > 0)
			{
				//redirect back to the main page with the failure message
				return Ce_cache_utils::redirect_alert(implode(PHP_EOL.' ', $errors), 'issue');
			}

			//redirect back to the main page with the success message
			return Ce_cache_utils::redirect_alert(lang('ce_cache_clear_all_cache_success'));
		}

		//view data
		$data = array(
			'action_url' => Ce_cache_utils::cp_url('clear_all_caches'),
			'back_link' => '<a class="submit" href="'.Ce_cache_utils::cp_url().'">'.lang('ce_cache_back_to').' '.lang('ce_cache_module_home') . '</a>'
		);

		//render the view
		return $this->render(array(
			'view' => 'clear_all_caches',
			'title' => lang('ce_cache_clear_cache_all_drivers'),
			'breadcrumbs' => array(),
			'vars' => $data
		));
	}

	/**
	 * Method to clear all site caches.
	 *
	 * @return string
	 */
	public function clear_site_caches()
	{
		ee()->cp->add_to_head(PHP_EOL . '<link rel="stylesheet" href="' . self::$theme_folder_url . '/css/styles.min.css">');

		//load classes/helpers
		ee()->load->helper(array('form', 'url'));
		ee()->load->library('form_validation');

		if (ee()->input->post('submit', true) !== false) //the form was submitted
		{
			$classes = Ce_cache_drivers::get_all_driver_classes();

			//set an empty error array
			$errors = array();

			//the prefix for this site
			$prefix = Ce_cache_utils::get_site_prefix();

			foreach ($classes as $class)
			{
				//attempt to get the items for the path
				$items = $class->get_all($prefix);

				if ($items !== false)
				{
					foreach ($items as $item)
					{
						$t = $prefix . (($class->get_all_is_array()) ? $item['id'] : $item);
						if ($class->delete($t) === false)
						{
							$errors[] = sprintf(lang('ce_cache_error_deleting_item'), $t);
						}
					}
				}
			}

			//if the error string is not blank, return the error(s)
			if (count($errors) > 0)
			{
				return Ce_cache_utils::redirect_alert(implode(PHP_EOL.' ', $errors), 'issue');
			}

			//redirect back to the main page with the success message
			return Ce_cache_utils::redirect_alert(lang('ce_cache_clear_site_cache_success'));
		}

		//view data
		$data = array(
			'action_url' => Ce_cache_utils::cp_url('clear_site_caches'),
			'back_link' => '<a class="submit" href="'.Ce_cache_utils::cp_url().'">'.lang("ce_cache_back_to").' '.lang('ce_cache_module_home').'</a>'
		);

		//render the view
		return $this->render(array(
			'view' => 'clear_site_caches',
			'title' => lang('ce_cache_clear_cache_site_all'),
			'breadcrumbs' => array(),
			'vars' => $data
		));
	}

	/**
	 * The breaking index page.
	 *
	 * @return mixed
	 */
	public function breaking()
	{
		ee()->cp->add_to_head(PHP_EOL . '<link rel="stylesheet" href="' . self::$theme_folder_url . '/css/styles.min.css">');

		//load needed classes
		ee()->load->library('table');
		ee()->load->helper('form');
		ee()->load->model('ce_cache_break_index_model');

		//view data
		$data = array(
			'module' => $this->name,
			'channels' => ee()->ce_cache_break_index_model->get_site_channels()
		);

		//render the view
		return $this->render(array(
			'view' => 'cache_break_index',
			'title' => lang('ce_cache_channel_cache_breaking'),
			'breadcrumbs' => array(),
			'vars' => $data
		));
	}

	/**
	 * Individual breaking settings page.
	 *
	 * @return mixed
	 */
	public function breaking_settings($channel_id='')
	{
		ee()->cp->add_to_head(PHP_EOL . '<link rel="stylesheet" href="' . self::$theme_folder_url . '/css/styles.min.css">');

		//load classes/helpers/models
		ee()->load->helper(array('form', 'url'));
		ee()->load->library('form_validation');
		ee()->load->model('ce_cache_break_settings_model');

		//grab the channel_id from the get/post data
		if (! isset($channel_id))
		{
			$channel_id = ee()->input->get_post('channel_id', true);
		}

		//channel id
		if (!isset($channel_id) || !is_numeric($channel_id))
		{
			return '<p>' . lang('ce_cache_error_no_channel') . '</p>';
		}

		//channel title
		$channel_title = ee()->ce_cache_break_settings_model->get_channel_title_by_id($channel_id);
		if ($channel_title === false) //the channel was not found
		{
			return '<p>' . lang('ce_cache_error_channel_not_found') . '</p>';
		}

		//set the page title
		$title = sprintf(lang('ce_cache_channel_breaking_settings'), $channel_title);

		//make sure the correct version of the module is installed
		if (!ee()->db->table_exists('ce_cache_breaking'))
		{
			return '<p>' . lang('ce_cache_error_module_not_installed') . '</p>';
		}

		//link to here
		$self = Ce_cache_utils::cp_url('breaking_settings/'.$channel_id);

		//get the form variables
		$vars = ee()->ce_cache_break_settings_model->process_vars($channel_id, $self);

		//view data
		$data = array(
			'action_url' => $self,
			'channel_id' => $channel_id,
			'channel_title' => $channel_title,
			'back_link' => '<a class="submit" href="'.Ce_cache_utils::cp_url('breaking').'">' . lang("ce_cache_back_to") . ' ' . lang('ce_cache_channel_cache_breaking') . '</a>',
			'errors' => $vars['errors'],
			'title' => $title
		);

		//load in the JavaScript
		$vue = (getenv('ENV_CE_CACHE_DEV') == '1') ? 'vue.js' : 'vue.min.js';
		ee()->cp->add_to_foot(PHP_EOL . '<script type="text/javascript" src="'.self::$theme_folder_url.'js/'.$vue.'"></script>
			<script type="text/javascript" src="'.self::$theme_folder_url.'js/jquery.tagsinput.min.js"></script>
			<script type="text/javascript">
				var CE_CACHE_BREAK_SETTINGS = {
					items : ' . json_encode( $vars['items'] ) . ',
					tags : ' . json_encode( implode('|',$vars['tags']) ) . ',
					refresh_time : ' . json_encode( $vars['refresh_time'] ) . ',
					refresh : ' . json_encode( $vars['refresh'] ) . '
				};
			</script>
			<script type="text/javascript" src="'.self::$theme_folder_url.'js/ce_cache_break.min.js"></script>'
		);

		//render the view
		return $this->render(array(
			'view' => 'cache_break_settings',
			'title' => $title,
			'breadcrumbs' => array(
				Ce_cache_utils::cp_url('breaking') => lang('ce_cache_channel_cache_breaking')
			),
			'vars' => $data
		));
	}

	/**
	 * Clear the tags. //TODO add item refreshing
	 *
	 * @return mixed
	 */
	public function clear_tags()
	{
		ee()->cp->add_to_head(PHP_EOL . '<link rel="stylesheet" href="' . self::$theme_folder_url . '/css/styles.min.css">');

		//load needed classes
		ee()->load->helper('form');
		ee()->load->model('ce_cache_tags_model');

		//the prefix for this site
		$prefix = Ce_cache_utils::get_site_prefix();

		//selected tags empty by default
		$selected = array();

		//get all of the tags for the current site
		$tags = ee()->ce_cache_tags_model->get_tags($prefix);

		if (!! ee()->input->post('submit')) //the form was submitted
		{
			//selected
			$selected = ee()->input->post('ce_cache_tags', true);
			if (empty($selected) || !is_array($selected))
			{
				$selected = array();
			}

			//make sure the selected items have tags that exist, mostly for validation purposes
			foreach ($selected as $index => $tag)
			{
				if (!in_array($tag, $tags))
				{
					unset($selected[$index]);
				}
			}

			//check if we have tags
			if (count($selected) > 0) //we have valid selected tags to delete
			{
				//load the CE Cache Break class
				$this->include_library('Ce_cache_break');

				//instantiate the class break and call the break cache method
				$cache_break = new Ce_cache_break();

				//clear the tag items and tags
				$cache_break->break_cache(array(), $selected, false);

				//redirect back to this page with a success message
				return Ce_cache_utils::redirect_alert(lang('ce_cache_delete_tags_success'), 'success', 'clear_tags');
			}
			else //no selected tags
			{
				//redirect back to this page with an error message
				return Ce_cache_utils::redirect_alert(lang('ce_cache_delete_tags_fail'), 'issue', 'clear_tags');
			}
		}

		//view data
		$data = array(
			'action_url' => Ce_cache_utils::cp_url('clear_tags'),
			'tags' => $tags,
			'selected' => empty($selected) ? array() : $selected
		);

		//render the view
		return $this->render(array(
			'view' => 'clear_tags',
			'title' => lang('ce_cache_clear_tagged_items'),
			'breadcrumbs' => array(),
			'vars' => $data
		));
	}

	/**
	 * Provides instructions on how to install the static driver.
	 *
	 * @return string
	 */
	public function static_installation()
	{
		ee()->cp->add_to_head(PHP_EOL . '<link rel="stylesheet" href="' . self::$theme_folder_url . '/css/styles.min.css">');

		ee()->cp->add_to_foot(PHP_EOL . '<script type="text/javascript" src="' . self::$theme_folder_url . 'js/highlight.pack.js"></script><script>$(function() {
		$("code").each(function(i,e){hljs.highlightBlock(e);});
		});</script>');

		//parse the instructions
		$instructions = str_replace(
			array(
				'{prefix}',
				'{hash}',
				'{ce_cache_home_link}'
			),
			array(
				'prefix' => rtrim(Ce_cache_utils::get_site_prefix(), '/'),
				'hash' => Ce_cache_utils::get_site_hash(),
				'home_link' => Ce_cache_utils::cp_url()
			),
			lang('ce_cache_static_instructions')
		);

		//view data
		$data = array(
			'instructions' => $instructions
		);

		//render the view
		return $this->render(array(
			'view' => 'static_installation',
			'title' => lang('ce_cache_static_installation'),
			'breadcrumbs' => array(),
			'vars' => $data
		));
	}

	/**
	 * Debug method to ensure that cache breaking is working.
	 */
	public function debug()
	{
		ee()->cp->add_to_head(PHP_EOL . '<link rel="stylesheet" href="' . self::$theme_folder_url . '/css/styles.min.css">');

		//create the URL
		$url = ee()->functions->fetch_site_index(0, 0) . QUERY_MARKER . 'ACT=' . $this->fetch_action_id('Ce_cache', 'break_cache') . '&act_test=y';

		//load the CE Cache Break class
		$this->include_library('Ce_cache_break');

		$cache_break = new Ce_cache_break();

		//make sure that allow_url_fopen is set to true if permissible
		@ini_set('allow_url_fopen', true);
		//some servers will not accept the asynchronous requests if there is no user_agent
		@ini_set('user_agent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:5.0) Gecko/20100101');

		//first try synchronous cache breaking
		ee()->config->config['ce_cache_async'] = 'no';

		$output = sprintf(lang('ce_cache_debug_url'), $url, $url);

		//attempt to asynchronously send the secrets to the cache_break method of the module
		if ($cache_break->curl_it($url))
		{
			$output .= lang('ce_cache_debug_curl');
		}
		else if ($cache_break->fsockopen_it($url))
		{
			$output .= lang('ce_cache_debug_fsockopen');
		}
		else
		{
			$output .= lang('ce_cache_debug_not_working');
		}

		return $output;
	}

	/**
	 * Determines if a string ends with a specified string.
	 *
	 * @param $string
	 * @param $test
	 * @return bool
	 */
	private function ends_with($string, $test)
	{
		$strlen = strlen($string);
		$testlen = strlen($test);
		if ($testlen > $strlen)
		{
			return false;
		}
		return substr_compare($string, $test, -$testlen) === 0;
	}

	/**
	 * This little helper function is the same one used in the cp class, but Datagrab apparently breaks that one when working with CE Cache.
	 *
	 * @param $class
	 * @param $method
	 * @return bool
	 */
	private function fetch_action_id($class, $method)
	{
		ee()->db->select('action_id');
		ee()->db->where('class', $class);
		ee()->db->where('method', $method);
		$query = ee()->db->get('actions');

		if ($query->num_rows() == 0)
		{
			return false;
		}

		return $query->row('action_id');
	}

	/**
	 * Include the library by name. Done this way instead of
	 * ee()->load->library('example') so that the class is not
	 * instantiated, but rather used to call static methods.
	 *
	 * @param $name
	 */
	private function include_library($name)
	{
		//load the class if needed
		if (!class_exists($name))
		{
			include PATH_THIRD . 'ce_cache/libraries/'.$name.'.php';
		}
	}

	/**
	 * Renders the view and sets the navigation.
	 *
	 * @param array $args
	 */
	private function render($args = array())
	{
		//the base URL
		$baseUrl = Ce_cache_utils::cp_url();

		//the module name
		$moduleName = lang('ce_cache_module_name');

		//always add the homepage breadcrumb
		$breadcrumbs = array();
		$breadcrumbs[$baseUrl] = $moduleName;

		//get the variables from the args or their defaults
		$view = !empty($args['view']) ? $args['view'] : 'index';
		$title = !empty($args['title']) ? $args['title'] : '';
		$vars = !empty($args['vars']) && is_array($args['vars']) ? $args['vars'] : array();
		$activeDriver = !empty($args['activeDriver']) ? $args['activeDriver'] : '';

		//add the driver breadcrumbs, if applicable
		if (!empty($activeDriver))
		{
			$breadcrumbs[$baseUrl.'#'] = lang('ce_cache_drivers');

			if ($view != 'driver')
			{
				$breadcrumbs[Ce_cache_utils::cp_url('driver/'.$activeDriver)] = lang("ce_cache_driver_{$activeDriver}");
			}
		}

		//add the other breadcrumbs, if applicable
		if (!empty($args['breadcrumbs']) && is_array($args['breadcrumbs'])) //add other breadcrumbs
		{
			$breadcrumbs = array_merge($breadcrumbs, $args['breadcrumbs']);
		}

		//make the sidebar
		$sidebar = ee('CP/Sidebar')->make();

		//add the homepage (drivers)
		$header = $sidebar->addHeader(lang('ce_cache_drivers'), $baseUrl);
		if ($view == 'index') {
			$header->isActive();
		}

		//add the drivers basic list
		$active_drivers = Ce_cache_drivers::get_active_driver_names(true);
		$supported_drivers = Ce_cache_drivers::get_supported_driver_names();
		$drivers = array_merge( $active_drivers, $supported_drivers );

		//determine if CE Cache is disabled in the config
		$is_disabled = Ce_cache_utils::ee_string_to_bool(Ce_cache_utils::determine_setting('off', 'no', '', true, false));
		if (!empty($drivers))
		{
			$basic_list  = $header->addBasicList();
			foreach( $drivers as $driver )
			{
				//determine whether or not the driver is active
				$is_active = in_array($driver, $active_drivers);

				//add the driver status icon
				$status_icon = '<span class="ce-cache-driver-status';
				$status_icon .= ($is_disabled) ? ' ce-cache-caching-off' : '';
				$status_icon .= ($is_active) ? ' ce-cache-active-driver' : ' ce-cache-supported-driver';
				$status_icon .= '" title="'.($is_active ? lang('ce_cache_active_driver') : lang('ce_cache_supported_driver')).'"></span>';

				//add the item
				$basic_item  = $basic_list->addItem($status_icon.lang("ce_cache_driver_{$driver}"), Ce_cache_utils::cp_url('driver/'.$driver));

				//set to active if applicable
				if ($driver == $activeDriver) {
					$basic_item->isActive();
				}
			}
		}

		//add the cache breaking page
		$header = $sidebar->addHeader(lang('ce_cache_channel_cache_breaking'), Ce_cache_utils::cp_url('breaking'));
		if ($view == 'cache_break_index' || $view == 'cache_break_settings') {
			$header->isActive();
		}

		//add the clear tagged items page
		$header = $sidebar->addHeader(lang('ce_cache_clear_tagged_items'), Ce_cache_utils::cp_url('clear_tags'));
		if ($view == 'clear_tags') {
			$header->isActive();
		}

		//add the static driver installation page
		$header = $sidebar->addHeader(lang('ce_cache_static_installation'), Ce_cache_utils::cp_url('static_installation'));
		if ($view == 'static_installation') {
			$header->isActive();
		}

		$view = ee('View')->make('ce_cache:' . $view);

		//Add the header
		ee()->view->header = array(
			'title' => $moduleName
		);

		return array(
			'heading'    => $title,
			'breadcrumb' => $breadcrumbs,
			'body'       => $view->render($vars)
		);
	}

	/**
	 * Renders an inline EE error.
	 *
	 * @param string $message
	 */
	private function show_inline_error($message = '')
	{
		return "
			<div class=\"ce-cache\">
				<div class=\"alert inline issue\">
					<h3>Error</h3>
					<p>{$message}</p>
				</div>
			</div>";
	}
}
/* End of file mcp.ce_cache.php */
/* Location: /system/expressionengine/third_party/ce_cache/mcp.ce_cache.php */