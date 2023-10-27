<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Files Module FieldType
 *
 * @package			DevDemon_ChannelFiles
 * @version			3.6
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class Channel_files_ft extends EE_Fieldtype
{

	/**
	 * Field info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'name' 		=> CHANNEL_FILES_NAME,
		'version'	=> CHANNEL_FILES_VERSION
	);

	/**
	 * The field settings array
	 *
	 * @access public
	 * @var array
	 */
	public $settings = array();

	public $has_array_data = TRUE;

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		$this->site_id = ee()->config->item('site_id');

		ee()->lang->loadfile('channel_files');
		ee()->load->library('channel_files_helper');
		ee()->load->model('channel_files_model');
	}

	// ********************************************************************************* //

	function display_field($data)
	{
		$settings = ee('channel_files:Settings')->getFieldtypeSettings($this->field_id, $this->settings);

		//----------------------------------------
		// Global Vars
		//----------------------------------------
		$vData = array();
		$vData['missing_settings'] = FALSE;
		$vData['field_name'] = $this->field_name;
		$vData['field_id'] = $this->field_id;
		$vData['site_id'] = $this->site_id;
		$vData['temp_key'] = ee()->localize->now;
		$vData['channel_id'] = (ee()->input->get_post('channel_id') != FALSE) ? ee()->input->get_post('channel_id') : 0;
		$entry_id = $this->content_id();
		$vData['entry_id'] = $this->content_id();
		$vData['total_files'] = 0;
		$vData['assigned_files'] = array();

		//----------------------------------------
		// Add Global JS & CSS & JS Scripts
		//----------------------------------------
		ee('channel_files:Helper')->mcpAssets('gjs');
		ee('channel_files:Helper')->mcpAssets('css', 'addon_pbf.css', null, true);
		ee('channel_files:Helper')->mcpAssets('css', 'jquery.colorbox.css');
		ee('channel_files:Helper')->mcpAssets('js', 'jquery.editable.js');
		ee('channel_files:Helper')->mcpAssets('js', 'jquery.base64.js');
		ee('channel_files:Helper')->mcpAssets('js', 'jquery.liveurltitle.js');
		ee('channel_files:Helper')->mcpAssets('js', 'hogan.js');
		ee('channel_files:Helper')->mcpAssets('js', 'json2.js');
		ee('channel_files:Helper')->mcpAssets('js', 'swfupload.js');
		ee('channel_files:Helper')->mcpAssets('js', 'swfupload.queue.js');
		ee('channel_files:Helper')->mcpAssets('js', 'swfupload.speed.js');
		ee('channel_files:Helper')->mcpAssets('js', 'jquery.colorbox.js');
		ee('channel_files:Helper')->mcpAssets('js', 'addon_pbf.js', null, true);
		ee()->cp->add_js_script(array(
		        'ui'        => array('sortable'),
		    )
		);

		//----------------------------------------
		// Settings
		//----------------------------------------
		// Settings SET?
		if (isset($settings['upload_location']) == FALSE OR $settings['upload_location'] == FALSE) {
			$vData['missing_settings'] = TRUE;
			return ee()->load->view('pbf/field', $vData, TRUE);
		}

		// Map it Back
		$defaults = ee()->config->item('cf_defaults');

		// Columns?
		if (isset($settings['columns']) == FALSE) $settings['columns'] = ee()->config->item('cf_columns');

		// Stored Images
		if (isset($settings['show_stored_files']) == FALSE) $settings['show_stored_files'] = $defaults['show_stored_files'];

		// Limit Images?
		if (isset($settings['file_limit']) == FALSE OR trim($settings['file_limit']) == FALSE) $settings['file_limit'] = 999999;

		$vData['settings'] = ee()->channel_files_helper->array_extend($defaults, $settings);

		// Post DATA?
		if (isset($_POST[$this->field_name])) {
			$data = $_POST[$this->field_name];
		}

		//----------------------------------------
		// Field JSON
		//----------------------------------------
		$field_json = array();
		$field_json['key'] = $vData['temp_key'];
		$field_json['field_name'] = $this->field_name;
		$field_json['field_label'] = $this->settings['field_label'];
		$field_json['settings'] = $vData['settings'];
		$field_json['categories'] = array();

		// Add Categories
		if (isset($settings['categories']) == TRUE && empty($settings['categories']) == FALSE)
		{
			foreach ($settings['categories'] as $cat) $field_json['categories'][$cat] = $cat;
		}

		// Remove some unwanted stuff
		unset($field_json['settings']['categories']);
		unset($field_json['settings']['locations']);
		unset($field_json['settings']['import_path']);

		//----------------------------------------
		// JS Templates
		//----------------------------------------
		$js_templates = FALSE;
		if (isset( ee()->session->cache['ChannelFiles']['JSTemplates'] ) === FALSE)
		{
			$js_templates = TRUE;
			ee()->session->cache['ChannelFiles']['JSTemplates'] = TRUE;

			$vData['langjson'] = array();

			foreach (ee()->lang->language as $key => $val)
			{
				if (strpos($key, 'cf:json:') === 0)
				{
					$vData['langjson'][substr($key, 8)] = $val;
					unset(ee()->lang->language[$key]);
				}

			}

			$vData['langjson'] = ee()->channel_files_helper->generate_json($vData['langjson']);
		}

		//----------------------------------------
		// Grab Assigned Files
		//----------------------------------------

		if ($vData['entry_id'] != FALSE)
		{
			//----------------------------------------
			// Grab all files
			//----------------------------------------
			ee()->db->select('*');
			ee()->db->from('exp_channel_files');
			ee()->db->where('entry_id', $vData['entry_id']);
			ee()->db->where('field_id', $this->field_id);

			$is_draft = 0;

            if (isset(ee()->publisher_lib) === true && isset(ee()->publisher_lib->status) ==- true) {
                if (ee()->publisher_lib->status == 'draft')  {
                    $is_draft = 1;
                }
            } else if (isset(ee()->session->cache['ep_better_workflow']['is_draft']) && ee()->session->cache['ep_better_workflow']['is_draft']) {
                $is_draft = 1;
            }

			ee()->db->order_by('file_order');
			$query = ee()->db->get();

			// Preview URL
			$preview_url = ee()->channel_files_helper->get_router_url('url', 'simple_file_url');

			//----------------------------------------
			// Loop over them!
			//----------------------------------------
			foreach ($query->result() as $file)
			{
				$file->linked = FALSE;

				// Is it a linked image?
				// Then we need to "fake" the channel_id/field_id
				if ($file->link_file_id > 0)
				{
					$file->entry_id = $file->link_entry_id;
					$file->field_id = $file->link_field_id;
					$file->channel_id = $file->link_channel_id;
					$file->linked = TRUE; // Display the break link icon
				}

				// ReAssign Field ID
				$file->field_id = $this->field_id;
				$file->primary = $file->file_primary;

				// URL
				$file->fileurl = $preview_url . '&amp;fid=' . $file->field_id . '&amp;d=' . $file->entry_id . '&amp;f=' . $file->filename;

				if (isset($settings['locked_url_fieldtype']) === TRUE && $settings['locked_url_fieldtype'] == 'yes')
				{
					$locked = array();
					$locked['time'] = ee()->localize->now + 3600;
					$locked['file_id'] = $file->file_id;
					$locked['ip'] = ee()->input->ip_address();
					$locked['fid'] = $file->field_id;
					$locked['d'] = $file->entry_id;
					$locked['f'] = $file->filename;

					$file->fileurl = $preview_url . '&amp;key=' . base64_encode(ee()->channel_files_helper->encrypt_string(serialize($locked)));
				}
				else
				{
					$file->fileurl = $preview_url . '&amp;fid=' . $file->field_id . '&amp;d=' . $file->entry_id . '&amp;f=' . $file->filename;
				}

				$file->title = str_replace('&quot;', '"', $file->title);
				$file->description = str_replace('&quot;', '"', $file->description);
				$file->cffield_1 = str_replace('&quot;', '"', $file->cffield_1);
				$file->cffield_2 = str_replace('&quot;', '"', $file->cffield_2);
				$file->cffield_3 = str_replace('&quot;', '"', $file->cffield_3);
				$file->cffield_4 = str_replace('&quot;', '"', $file->cffield_4);
				$file->cffield_5 = str_replace('&quot;', '"', $file->cffield_5);

				$vData['assigned_files'][] = $file;
			}


			$vData['total_files'] = $query->num_rows();
		}

		//----------------------------------------
		// Form Submission Error?
		//----------------------------------------
		if (isset($_POST[$this->field_name]) OR isset($_POST['field_id_' . $this->field_id]))
		{
			// Kill the already assigned ones
			$vData['assigned_files'] = array();

			// Post DATA?
			if (isset($_POST[$this->field_name])) {
				$data = $_POST[$this->field_name];
			}

			if (isset($_POST['field_id_' . $this->field_id])) {
				$data = $_POST['field_id_' . $this->field_id];
			}

			// First.. The Key!
			$vData['temp_key'] = $data['key'];

			if (isset($data['files']) == TRUE)
			{

				foreach($data['files'] as $num => $fl)
				{
					$fl = ee()->channel_files_helper->decode_json(html_entity_decode($fl['data']));
					$fl->field_id = $this->field_id;

					$fl->title = str_replace('&quot;', '"', $fl->title);
					$fl->description = str_replace('&quot;', '"', $fl->description);
					$fl->cffield_1 = str_replace('&quot;', '"', $fl->cffield_1);
					$fl->cffield_2 = str_replace('&quot;', '"', $fl->cffield_2);
					$fl->cffield_3 = str_replace('&quot;', '"', $fl->cffield_3);
					$fl->cffield_4 = str_replace('&quot;', '"', $fl->cffield_4);
					$fl->cffield_5 = str_replace('&quot;', '"', $fl->cffield_5);

					$vData['assigned_files'][] = $fl;

					unset($fl);
				}

				$vData['total_files'] = @count($data['files']);
			}
		}

		$field_json['files'] = $vData['assigned_files'];

		// Base64encode why? Safecracker loves to mess with quotes/unicode etc!!
		$field_json = base64_encode(ee()->channel_files_helper->generate_json($field_json));

		ee()->cp->add_to_foot("
		<script type='text/javascript'>
		var ChannelFiles = ChannelFiles ? ChannelFiles : new Object();
		ChannelFiles.Fields = ChannelFiles.Fields ? ChannelFiles.Fields : new Object();
		ChannelFiles.Fields.Field_{$vData['field_id']} = '{$field_json}';
		</script>
		");

		if ($js_templates) ee()->cp->add_to_foot(ee()->load->view('pbf/js_templates', $vData, true));


		return ee()->load->view('pbf/field', $vData, TRUE);
	}

	// ********************************************************************************* //

	/**
	 * Validates the field input
	 *
	 * @param $data Contains the submitted field data.
	 * @return mixed Must return TRUE or an error message
	 */
	public function validate($data)
	{
		// Is this a required field?
        if ($this->settings['field_required'] == 'y')
        {
            if (isset($data['files']) == false OR empty($data['files']) == true)
            {
                return ee()->lang->line('cf:required_field');
            }
        }

		return TRUE;
	}

	// ********************************************************************************* //

	function save($data)
	{
		ee()->session->cache['ChannelFiles']['FieldData'][$this->field_id] = $data;

		if (isset($data['files']) == FALSE)
		{
			return '';
		}
		else
		{
			return 'ChannelFiles';
		}
	}

	// ********************************************************************************* //

	function post_save($data)
	{
		return $this->_process_post_save($data);
	}

	// ********************************************************************************* //

	function delete($ids)
	{
		foreach ($ids as $entry_id)
		{
			// -----------------------------------------
			// ENTRY TO FIELD (we need settigns :()
			// -----------------------------------------
			ee()->db->select('field_id');
			ee()->db->from('exp_channel_files');
			ee()->db->where('entry_id', $entry_id);
			$query = ee()->db->get();

			if ($query->num_rows() == 0) continue;

			$field_id = $query->row('field_id');

			// Grab the field settings
			$settings = ee('channel_files:Settings')->getFieldtypeSettings($field_id);

			// -----------------------------------------
			// Load Location
			// -----------------------------------------
			$location_type = $settings['upload_location'];
			$location_class = 'CF_Location_'.$location_type;

			// Load Settings
			if (isset($settings['locations'][$location_type]) == FALSE)
			{
				continue;
			}

			$location_settings = $settings['locations'][$location_type];

			// Load Main Class
			if (class_exists('Cfile_location') == FALSE) require PATH_THIRD.'channel_files/locations/cfile_location.php';

			// Try to load Location Class
			if (class_exists($location_class) == FALSE)
			{
				$location_file = PATH_THIRD.'channel_files/locations/'.$location_type.'/'.$location_type.'.php';

				if (file_exists($location_file) == FALSE)
				{
					continue;
				}

				require $location_file;
			}

			// Init
			$LOC = new $location_class($location_settings);

			// Delete from db
			ee()->db->where('entry_id', $entry_id);
			ee()->db->or_where('link_entry_id', $entry_id);
			ee()->db->delete('exp_channel_files');

			// -----------------------------------------
			// Delete!
			// -----------------------------------------
			$LOC->delete_dir($entry_id);
		}

	}

	// ********************************************************************************* //

	/**
	 * Display the settings page. The default ExpressionEngine rows can be created using built in methods.
	 * All of these take the current $data and the fieltype name as parameters:
	 *
	 * @param $data array
	 * @access public
	 * @return void
	 */
	public function display_settings($data)
	{
		$settings = ee('channel_files:Settings')->getFieldtypeSettings($this->field_id, $data);

		//----------------------------------------
        // CSS/JS
        //----------------------------------------
        ee('channel_files:Helper')->mcpAssets('gjs');
        ee('channel_files:Helper')->mcpAssets('css', 'addon_fts.css', null, true);
        ee('channel_files:Helper')->mcpAssets('js', 'spin.js');
        ee('channel_files:Helper')->mcpAssets('js', 'addon_fts.js', null, true);

        return DevDemon\ChannelFiles\Service\FieldtypeSettingsHelper::showSettings($this->field_id, $settings);
	}

	// ********************************************************************************* //

	/**
	 * Save the fieldtype settings.
	 *
	 * @param $data array Contains the submitted settings for this field.
	 * @access public
	 * @return array
	 */
	public function save_settings($data)
	{
		return DevDemon\ChannelFiles\Service\FieldtypeSettingsHelper::saveSettings($this->field_id, $data);

		$settings = array();

		// Is it there?
		if (isset($_POST['channel_files']['file_extensions']) == FALSE) return $settings;

		$P = $_POST['channel_files'];


		// We need this for the url_title() method!
		ee()->load->helper('url');

		// Some tests
		$P['file_extensions'] = trim($P['file_extensions']);
		if (substr($P['locations']['ftp']['path'], -1) != '/') $P['locations']['ftp']['path'] .= '/';
		if (substr($P['locations']['sftp']['path'], -1) != '/') $P['locations']['sftp']['path'] .= '/';
		if (substr($P['import_path'], -1) != '/') $P['import_path'] .= '/';

		// -----------------------------------------
		// Parse categories
		// -----------------------------------------
		$categories = array();
		foreach (explode(',', $P['categories']) as $cat)
		{
			$cat = trim ($cat);
			if ($cat != FALSE) $categories[] = $cat;
		}

		$P['categories'] = $categories;

		// -----------------------------------------
		// Put it Back!
//		// -----------------------------------------
		$settings['channel_files'] = $P;

		return array(
                        'settings' =>$settings,
                        'field_wide' => true,
		);
	}

	// ********************************************************************************* //

	/**
	 * Replace Tag - Replace the field tag on the frontend.
	 *
	 * @param  mixed   $data    contains the field data (or prepped data, if using pre_process)
	 * @param  array   $params  contains field parameters (if any)
	 * @param  boolean $tagdata contains data between tag (for tag pairs)
	 * @return string           template data
	 */
	public function replace_tag($data, $params=array(), $tagdata=FALSE)
	{
		// We always need tagdata
		if ($tagdata === FALSE) return '';

		if (isset($params['prefetch']) == TRUE && $params['prefetch'] == 'yes')
		{
			// In some cases EE stores the entry_ids of the whole loop
			// We can use this to our advantage by grabbing
			if (isset(ee()->session->cache['channel']['entry_ids']) === TRUE)
			{
				ee()->channel_files_model->pre_fetch_data(ee()->session->cache['channel']['entry_ids'], $params);
			}
		}

		return ee()->channel_files_model->parse_template($this->row['entry_id'], $this->field_id, $params, $tagdata);
	}

	// ********************************************************************************* //

	public function draft_save($data, $draft_action)
	{
		//ee()->firephp->log($draft_action);

		// -----------------------------------------
		// Are we creating a new draft?
		// -----------------------------------------
		if ($draft_action == 'create')
		{
			// We are doing this because if you delete an image in live mode
			// and hit the draft button, we need to reflect that delete action in the draft
			$files = array();
			if (isset($data['files']) == TRUE)
			{
				foreach ($data['files'] as $key => $file)
				{
					$file = ee()->channel_files_helper->decode_json($file['data']);
					if (isset($file->delete) === TRUE)
					{
						unset($data['file'][$key]);
						continue;
					}

					if (isset($file->file_id) === TRUE && $file->file_id > 0) $files[] = $file->file_id;
				}
			}

			if (count($files) > 0)
			{
				// Grab all existing files
				$query = ee()->db->select('*')->from('exp_channel_files')->where_in('file_id', $files)->get();

				foreach ($query->result_array() as $row)
				{
					$row['is_draft'] = 1;
					unset($row['file_id']);
					ee()->db->insert('exp_channel_files', $row);
				}
			}
		}

		$this->_process_post_save($data, $draft_action);

		if (isset($data['files']) == FALSE) return '';
		else return 'ChannelFiles';
	}

	// ********************************************************************************* //

	public function draft_discard()
	{
		$entry_id = $this->settings['entry_id'];
		$field_id = $this->settings['field_id'];

		// Load the API
		if (class_exists('Channel_Files_API') != TRUE) include 'api.channel_files.php';
		$API = new Channel_Files_API();

		// Grab all existing images
		$query = ee()->db->select('*')->from('exp_channel_files')->where('entry_id', $this->settings['entry_id'])->where('field_id', $this->settings['field_id'])->where('is_draft', 1)->get();

		foreach ($query->result() as $row)
		{
			$API->delete_file($row);
		}
	}

	// ********************************************************************************* //


	public function draft_publish()
	{
		// Load the API
		if (class_exists('Channel_Files_API') != TRUE) include 'api.channel_files.php';
		$API = new Channel_Files_API();

		// Grab all existing images
		$query = ee()->db->select('*')->from('exp_channel_files')->where('entry_id', $this->settings['entry_id'])->where('field_id', $this->settings['field_id'])->where('is_draft', 0)->get();

		foreach ($query->result() as $row)
		{
			$API->delete_file($row);
		}

		// Grab all existing images
		$query = ee()->db->select('file_id')->from('exp_channel_files')->where('entry_id', $this->settings['entry_id'])->where('field_id', $this->settings['field_id'])->where('is_draft', 1)->get();

		foreach ($query->result() as $row)
		{
			ee()->db->set('is_draft', 0);
			ee()->db->where('file_id', $row->file_id);
			ee()->db->update('exp_channel_files');
		}
	}

	// ********************************************************************************* //

	private function _process_post_save($data, $draft_action=NULL)
	{
		ee()->load->library('channel_files_helper');
		ee()->load->helper('url');

		// Are we using Better Workflow?
		if ($draft_action !== NULL)
		{
			$is_draft = 1;
			//$entry_id = $this->settings['entry_id'];
			$entry_id = $this->content_id();
			$field_id = $this->settings['field_id'];
			$channel_id = $this->settings['channel_id'];

		}
		else
		{
			$is_draft = 0;
			$data = (isset(ee()->session->cache['ChannelFiles']['FieldData'][$this->field_id])) ? ee()->session->cache['ChannelFiles']['FieldData'][$this->field_id] : FALSE;
			//$entry_id = $this->settings['entry_id'];
			$entry_id = $this->content_id();
			$channel_id = ee()->input->post('channel_id');
			$field_id = $this->field_id;

			// Grab Settings
			$settings = ee('channel_files:Settings')->getFieldtypeSettings($this->field_id, $this->settings);
		}

		// Moving Channels?
		if (ee()->input->get_post('new_channel') != FALSE) {
			$channel_id = ee()->input->get_post('new_channel');
		}

		// Do we need to skip?
		if (isset($data['files']) == FALSE) return;

		// Our Key
		$key = $data['key'];

		// Mimetype!
		if (class_exists('CFMimeTypes') == FALSE) include 'libraries/mimetypes.class.php';
		$MIME = new CFMimeTypes();

		// -----------------------------------------
		// Entry_id FOLDER?
		// -----------------------------------------
		$entry_id_folder = TRUE;
		$prefix_entry_id = TRUE;

		if (isset($settings['entry_id_folder']) && $settings['entry_id_folder'] == 'no') $entry_id_folder = FALSE;
		if (isset($settings['prefix_entry_id']) && $settings['prefix_entry_id'] == 'no') $prefix_entry_id = FALSE;

		// -----------------------------------------
		// Load Location
		// -----------------------------------------
		$location_type = $settings['upload_location'];
		$location_class = 'CF_Location_'.$location_type;
		$location_settings = $settings['locations'][$location_type];

		// Load Main Class
		if (class_exists('Cfile_location') == FALSE) require PATH_THIRD.'channel_files/locations/cfile_location.php';

		// Try to load Location Class
		if (class_exists($location_class) == FALSE)
		{
			$location_file = PATH_THIRD.'channel_files/locations/'.$location_type.'/'.$location_type.'.php';
			require $location_file;
		}

		// Init!
		$LOC = new $location_class($location_settings);

		// We only create the dir if we this is true
		if ($entry_id_folder == TRUE)
		{
			// Create the DIR!
			$LOC->create_dir($entry_id);
		}

		// Load the API
		if (class_exists('Channel_Files_API') != TRUE) include 'api.channel_files.php';
		$API = new Channel_Files_API();

		// -----------------------------------------
		// Upload all Files
		// -----------------------------------------
		$filenames = array();
		$temp_dir = PATH_CACHE.'channel_files/field_'.$field_id.'/'.$key;

		// Loop over all files
		$tempfiles = @scandir($temp_dir);

		if (is_array($tempfiles) == TRUE)
		{
			$dir_entry_id = $entry_id;

			foreach ($tempfiles as $filename)
			{
				if ($filename == '.' OR $filename == '..') continue;

				$filepath = $temp_dir . '/' . $filename;

				$original_filename = $filename;

				// Entry ID as Folders?
				if ($entry_id_folder == FALSE)
				{
					if ($prefix_entry_id === TRUE) $filename = $entry_id .'-'. $filename;
					$dir_entry_id = FALSE;
				}

				// -------------------------------------------
				// 'channel_files_download_init' hook.
				//  - Executes before the the upload of a file
				//
					if (ee()->extensions->active_hook('channel_files_upload_start') === TRUE)
					{
						$edata = ee()->extensions->universal_call('channel_files_upload_start', $field_id, $entry_id, $filepath, $filename, $dir_entry_id);
						if (ee()->extensions->end_script === TRUE) return;
					}
				//
				// -------------------------------------------

				$res = $LOC->upload_file($filepath, $filename, $dir_entry_id);

		    	if (is_array($res) === TRUE && $res['success'] == TRUE)
		    	{
		    		if ($res['filename'] != FALSE) $filenames[$original_filename] = $res['filename'];
		    	}
		    	else
		    	{
		    		$filenames[$original_filename] = $filename;
		    	}

				@unlink($filepath);

				// -------------------------------------------
				// 'channel_files_download_init' hook.
				//  - Executes after the upload of a file, the source file already has been deleted.
				//
					if (ee()->extensions->active_hook('channel_files_upload_end') === TRUE)
					{
						$edata = ee()->extensions->universal_call('channel_files_upload_end', $field_id, $entry_id, $filename, $dir_entry_id);
						if (ee()->extensions->end_script === TRUE) return;
					}
				//
				// -------------------------------------------
			}
		}

		@rmdir($temp_dir);

		// -----------------------------------------
		// Grab all the files from the DB
		// -----------------------------------------
		ee()->db->select('*');
		ee()->db->from('exp_channel_files');
		ee()->db->where('entry_id', $entry_id);
		ee()->db->where('field_id', $field_id);

		if ($is_draft === 1 && $draft_action == 'update')
		{
			ee()->db->where('is_draft', 1);
		}
		else
		{
			ee()->db->where('is_draft', 0);
		}

		$query = ee()->db->get();

		// Lets create an file hash! So we can do unique image
		$dbfiles = array();
		foreach ($query->result() as $row)
		{
			$dbfiles[] = $row->file_id.$row->filename;
		}

		if ($is_draft === 1 && $draft_action == 'create')
		{
			$dbfiles = array();
		}

		// Any files?
		if (count($dbfiles) > 0)
        {
			// -----------------------------------------
			// Not fresh, lets see whats new.
			// -----------------------------------------
			foreach ($data['files'] as $order => $file)
			{
				$file = ee()->channel_files_helper->decode_json($file['data']);

				if (isset($file->delete) == TRUE)
				{
					$API->delete_file($file);
				}


/*
				// If we are creating a new draft, we already copied all data.. So lets kill the ones that came through POST
				if ($is_draft === 1 && $file->file_id > 0)
				{
					// -----------------------------------------
					// Old File
					// -----------------------------------------
					$data = array(	'entry_id'	=>	$entry_id,
									'field_id'	=>	$field_id,
									'channel_id'=>	$channel_id,
									'title'		=>	$API->process_field_string($file->title),
									'url_title'	=>	$API->process_field_string($file->url_title),
									'description' => $API->process_field_string($file->description),
									'category' 	=>	(isset($file->category) == true) ? $file->category : '',
									'cffield_1'	=>	$API->process_field_string($file->cffield_1),
									'cffield_2'	=>	$API->process_field_string($file->cffield_2),
									'cffield_3'	=>	$API->process_field_string($file->cffield_3),
									'cffield_4'	=>	$API->process_field_string($file->cffield_4),
									'cffield_5'	=>	$API->process_field_string($file->cffield_5),
									'file_primary'	=>	$file->primary,
									'file_order'	=>	$order,
									'is_draft'	=>	$is_draft,
								);

					ee()->db->update('exp_channel_files', $data, array('file_id' => $file->file_id));
					continue;
				}
*/
				// Mime type
				$filemime = $MIME->get_mimetype($file->extension);

				// Check for linked_fileid
				if (isset($file->link_file_id) == FALSE) $file->link_file_id = 0;
				$file->link_entry_id = 0;
				$file->link_channel_id = 0;
				$file->link_field_id = 0;

				// Is it a new file? Lets grab it's new filename
				if (isset($filenames[$file->filename]) === TRUE && $file->file_id < 1) $file->filename = $filenames[$file->filename];

				if (ee()->channel_files_helper->in_multi_array($file->file_id.$file->filename, $dbfiles) === FALSE)
				{
					// Grab MD5/Filesize
					if ($file->link_file_id > 0)
					{
						$q = ee()->db->select('entry_id, field_id, channel_id, md5, filesize')->from('exp_channel_files')->where('file_id', $file->link_file_id)->limit(1)->get();
						$file->link_entry_id = $q->row('entry_id');
						$file->link_channel_id = $q->row('channel_id');
						$file->link_field_id = $q->row('field_id');
						$file->filesize = $q->row('filesize');
						$file->md5 = $q->row('md5');
						$q->free_result();
					}

					// -----------------------------------------
					// New Files
					// --------------------------------------
					$data = array(	'site_id'	=>	$this->site_id,
									'entry_id'	=>	$entry_id,
									'field_id'	=>	$field_id,
									'channel_id'=>	$channel_id,
									'member_id'=>	ee()->session->userdata['member_id'],
									'is_draft'	=>	$is_draft,
									'link_file_id' => $file->link_file_id,
									'link_entry_id' => $file->link_entry_id,
									'link_channel_id' => $file->link_channel_id,
									'link_field_id' => $file->link_field_id,
									'filename'	=>	$file->filename,
									'extension' =>	$file->extension,
									'mime'		=>	$filemime,
									'upload_service'=> $location_type,
									'title'		=>	$API->process_field_string($file->title),
									'url_title'	=>	$API->process_field_string($file->url_title),
									'description' => $API->process_field_string($file->description),
									'category' 	=>	(isset($file->category) == true) ? $file->category : '',
									'cffield_1'	=>	$API->process_field_string($file->cffield_1),
									'cffield_2'	=>	$API->process_field_string($file->cffield_2),
									'cffield_3'	=>	$API->process_field_string($file->cffield_3),
									'cffield_4'	=>	$API->process_field_string($file->cffield_4),
									'cffield_5'	=>	$API->process_field_string($file->cffield_5),
									'filesize'	=>	$file->filesize,
									'md5' 		=>	$file->md5,
									'file_primary'	=>	$file->primary,
									'file_order'=>	$order,
									'date'		=>	ee()->localize->now,
								);

					ee()->db->insert('exp_channel_files', $data);
				}
				else
				{
					// -----------------------------------------
					// Old File
					// -----------------------------------------
					$data = array(	'entry_id'	=>	$entry_id,
									'field_id'	=>	$field_id,
									'channel_id'=>	$channel_id,
									'title'		=>	$API->process_field_string($file->title),
									'url_title'	=>	$API->process_field_string($file->url_title),
									'description' => $API->process_field_string($file->description),
									'category' 	=>	(isset($file->category) == true) ? $file->category : '',
									'cffield_1'	=>	$API->process_field_string($file->cffield_1),
									'cffield_2'	=>	$API->process_field_string($file->cffield_2),
									'cffield_3'	=>	$API->process_field_string($file->cffield_3),
									'cffield_4'	=>	$API->process_field_string($file->cffield_4),
									'cffield_5'	=>	$API->process_field_string($file->cffield_5),
									'file_primary'	=>	$file->primary,
									'file_order'	=>	$order,
									'is_draft'	=>	$is_draft,
								);

					ee()->db->update('exp_channel_files', $data, array('file_id' => $file->file_id));
				}
			}
		}
		else
		{
			// -----------------------------------------
			// No previous entries, fresh fresh
			// -----------------------------------------
			foreach ($data['files'] as $order => $file)
			{
				$file = ee()->channel_files_helper->decode_json($file['data']);

				///ee()->firephp->log($file);
				///ee()->firephp->log($draft_action);
				///ee()->firephp->log($is_draft);

				// If we are creating a new draft, we already copied all data.. So lets kill the ones that came through POST
				if ($is_draft === 1 && $file->file_id > 0 && $draft_action !== 'create')
				{
					// -----------------------------------------
					// Old File
					// -----------------------------------------
					$data = array(	'entry_id'	=>	$entry_id,
									'field_id'	=>	$field_id,
									'channel_id'=>	$channel_id,
									'title'		=>	$API->process_field_string($file->title),
									'url_title'	=>	$API->process_field_string($file->url_title),
									'description' => $API->process_field_string($file->description),
									'category' 	=>	(isset($file->category) == true) ? $file->category : '',
									'cffield_1'	=>	$API->process_field_string($file->cffield_1),
									'cffield_2'	=>	$API->process_field_string($file->cffield_2),
									'cffield_3'	=>	$API->process_field_string($file->cffield_3),
									'cffield_4'	=>	$API->process_field_string($file->cffield_4),
									'cffield_5'	=>	$API->process_field_string($file->cffield_5),
									'file_primary'	=>	$file->primary,
									'file_order'	=>	$order,
									'is_draft'	=>	$is_draft,
								);

					ee()->db->update('exp_channel_files', $data, array('file_id' => $file->file_id));

					continue;
				}

				if ($file->file_id > 0) continue;

				// Mime type
				$filemime = $MIME->get_mimetype($file->extension);

				// Check for linked_fileid
				if (isset($file->link_file_id) == FALSE) $file->link_file_id = 0;
				$file->link_entry_id = 0;
				$file->link_channel_id = 0;
				$file->link_field_id = 0;

				if ($file->link_file_id  == 0)
				{
					$file->filename = $filenames[$file->filename];
				}

				// Grab MD5/Filesize
				if ($file->link_file_id > 0)
				{
					$q = ee()->db->select('entry_id, field_id, channel_id, md5, filesize')->from('exp_channel_files')->where('file_id', $file->link_file_id)->limit(1)->get();
					$file->link_entry_id = $q->row('entry_id');
					$file->link_channel_id = $q->row('channel_id');
					$file->link_field_id = $q->row('field_id');
					$file->filesize = $q->row('filesize');
					$file->md5 = $q->row('md5');
					$q->free_result();
				}

				// -----------------------------------------
				// New Files
				// -----------------------------------------
				$data = array(	'site_id'	=>	$this->site_id,
								'entry_id'	=>	$entry_id,
								'field_id'	=>	$field_id,
								'channel_id'=>	$channel_id,
								'member_id'=>	ee()->session->userdata['member_id'],
								'is_draft'	=>	$is_draft,
								'link_file_id' => $file->link_file_id,
								'link_entry_id' => $file->link_entry_id,
								'link_channel_id' => $file->link_channel_id,
								'link_field_id' => $file->link_field_id,
								'filename'	=>	$file->filename,
								'extension' =>	$file->extension,
								'mime'		=>	$filemime,
								'upload_service'=> $location_type,
								'title'		=>	$API->process_field_string($file->title),
								'url_title'	=>	$API->process_field_string($file->url_title),
								'description' => $API->process_field_string($file->description),
								'category' 	=>	(isset($file->category) == true) ? $file->category : '',
								'cffield_1'	=>	$API->process_field_string($file->cffield_1),
								'cffield_2'	=>	$API->process_field_string($file->cffield_2),
								'cffield_3'	=>	$API->process_field_string($file->cffield_3),
								'cffield_4'	=>	$API->process_field_string($file->cffield_4),
								'cffield_5'	=>	$API->process_field_string($file->cffield_5),
								'filesize'	=>	$file->filesize,
								'md5' 		=>	$file->md5,
								'file_primary'	=>	$file->primary,
								'file_order'=>	$order,
								'date'		=>	ee()->localize->now,
							);

				ee()->db->insert('exp_channel_files', $data);
			}
		}

		// Kill Folder
		ee()->channel_files_helper->delete_files($temp_dir, TRUE);
		@rmdir($temp_dir);

		// -----------------------------------------
		// Delete old cache folders
		// -----------------------------------------'
		$path_temp_dir = PATH_CACHE.'channel_files/field_'.$field_id.'/';
		$day_ago = ee()->localize->now - 86400;

		// Get directory contents
		$tempdirs = @scandir($path_temp_dir);

		if (is_array($tempdirs) == TRUE)
		{
			// Loop over all files
			foreach ($tempdirs as $tempdir)
			{
				// We don't want those
				if ($tempdir == '.' OR $tempdir == '..' OR $temp_dir == FALSE) continue;

				// Is it a Directory?
				if (@is_dir($path_temp_dir.$tempdir) == TRUE)
				{
					// And 24 old?
					if ($day_ago > $tempdir)
					{
						// Kill it
						ee()->channel_files_helper->delete_files($path_temp_dir.$tempdir, TRUE);
						@rmdir($path_temp_dir.$tempdir);
					}
				}
			}
		}

		// -----------------------------------------
		// WYGWAM
		// -----------------------------------------

		// Which field_group is assigned to this channel?
		$query = ee()->db->select('field_group')->from('exp_channels')->where('channel_id', $channel_id)->get();
		if ($query->num_rows() == 0) return;
		$field_group = $query->row('field_group');

		// Which fields are WYGWAM/wyvern
		$query = ee()->db->select('field_id')->from('exp_channel_fields')->where('group_id', $field_group)->where('field_type', 'wygwam')->or_where('field_type', 'wyvern')->get();
		if ($query->num_rows() == 0) return;

		// Harvest all of them
		$fields = array();

		foreach ($query->result() as $row)
		{
			$fields[] = 'field_id_' . $row->field_id;
		}

		if (count($fields) > 0)
		{
			// Grab them!
			foreach ($fields as $field)
			{
				ee()->db->set($field, " REPLACE({$field}, 'd=CFKEYDIR&', 'd={$entry_id}&') ", FALSE);
				ee()->db->where('entry_id', $entry_id);
				ee()->db->update('exp_channel_data');
			}

		}

		// Delete old dirs
		$API->clean_temp_dirs($this->field_id);

		// -----------------------------------------
		// Just to be sure
		// -----------------------------------------
		$query = ee()->db->select('file_id')->from('exp_channel_files')->where('field_id', $this->field_id)->where('entry_id', $entry_id)->get();
		if ($query->num_rows() == 0) ee()->db->set('field_id_'.$this->field_id, '');
		else ee()->db->set('field_id_'.$this->field_id, 'Channel Files');
		ee()->db->where('entry_id', $entry_id);
		ee()->db->update('exp_channel_data');

		return;
	}
}

/* End of file ft.channel_files.php */
/* Location: ./system/expressionengine/third_party/tagger/ft.channel_files.php */
