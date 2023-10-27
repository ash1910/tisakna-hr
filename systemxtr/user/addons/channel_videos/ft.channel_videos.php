<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Videos Module FieldType
 *
 * @package			DevDemon_ChannelVideos
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class Channel_videos_ft extends EE_Fieldtype
{
	/**
	 * Field info (Required)
	 *
	 * @var array
	 * @access public
	 */
	var $info = array(
		'name' 		=> CHANNEL_VIDEOS_NAME,
		'version'	=> CHANNEL_VIDEOS_VERSION,
	);

	/**
	 * The field settings array
	 *
	 * @access public
	 * @var array
	 */
	public $settings = array();


	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		if (version_compare(APP_VER, '2.1.4', '>')) { parent::__construct(); } else { parent::EE_Fieldtype(); }

		/*if ($this->EE->input->cookie('cp_last_site_id')) $this->site_id = $this->EE->input->cookie('cp_last_site_id');
		else if ($this->EE->input->get_post('site_id')) $this->site_id = $this->EE->input->get_post('site_id');
		else $this->site_id = $this->EE->config->item('site_id');*/

                if (ee()->input->cookie('cp_last_site_id')) $this->site_id = ee()->input->cookie('cp_last_site_id');
		else if (ee()->input->get_post('site_id')) $this->site_id = ee()->input->get_post('site_id');
		else $this->site_id = ee()->config->item('site_id');


               // $this->site_id = ee()->config->item('site_id');
		ee()->load->add_package_path(PATH_THIRD . 'channel_videos/');
		ee()->lang->loadfile('channel_videos');
		ee()->load->library('channel_videos_helper');
		ee()->config->load('cv_config');
	}

	// ********************************************************************************* //

	/**
	 * Display the field in the publish form
	 *
	 * @access public
	 * @param $data String Contains the current field data. Blank for new entries.
	 * @return String The custom field HTML
	 */
	public function display_field($data)
	{
		//----------------------------------------
		// Global Vars
		//----------------------------------------
		$vData = array();
		$vData['field_name'] = $this->field_name;
		$vData['field_id'] = $this->field_id;
		$vData['site_id'] = $this->site_id;
		$vData['channel_id'] = (ee()->input->get_post('channel_id') != FALSE) ? ee()->input->get_post('channel_id') : 0;
		//$vData['entry_id'] = (ee()->input->get_post('entry_id') != FALSE) ? ee()->input->get_post('entry_id') : FALSE;
		$vData['videos'] = array();
		$vData['total_videos'] = 0;
		$vData['entry_id'] = $this->content_id();//ee()->input->get_post('entry_id');


		// Post DATA?
		if (isset($_POST[$this->field_name])) {
			$data = $_POST[$this->field_name];
		}

		//----------------------------------------
		// Add Global JS & CSS & JS Scripts
		//----------------------------------------
		ee()->channel_videos_helper->addMcpAssets('gjs');
		ee()->channel_videos_helper->addMcpAssets('css', 'css/colorbox.css?v='.CHANNEL_VIDEOS_VERSION, 'jquery', 'colorbox');
                ee()->channel_videos_helper->addMcpAssets('css', 'css/pbf.css?v='.CHANNEL_VIDEOS_VERSION, 'channel_videos', 'pbf');
                ee()->channel_videos_helper->addMcpAssets('js', 'js/jquery.colorbox.js?v='.CHANNEL_VIDEOS_VERSION, 'jquery', 'colorbox');
                ee()->channel_videos_helper->addMcpAssets('js', 'js/json3.min.js?v='.CHANNEL_VIDEOS_VERSION, 'json3', 'json3', 'lte IE 8');

        if (ee()->config->item('channel_videos_debug') == 'yes') {
             ee()->channel_videos_helper->addMcpAssets('js', 'js/pbf.js?v='.CHANNEL_VIDEOS_VERSION, 'channel_videos', 'pbf');
        } else {
             ee()->channel_videos_helper->addMcpAssets('js', 'js/pbf.js?v='.CHANNEL_VIDEOS_VERSION, 'channel_videos', 'pbf');
        }

		ee()->cp->add_js_script(array('ui' => array('sortable')));

		//----------------------------------------
		// Settings
		//----------------------------------------
                $vData['config'] = ee()->config->item('cv_columns');
		$settings = $this->settings;
                $this->settings['entry_id']=$this->content_id();

              if (isset($this->settings) == TRUE) $vData['config'] = array_merge($vData['config'], $this->settings);
		//$settings = (isset($settings['channel_videos']) == TRUE) ? $settings['channel_videos'] : array();
		$defaults = ee()->config->item('cv_defaults');

		// Columns?
		if (isset($settings['columns']) == FALSE) $settings['columns'] = ee()->config->item('cv_columns');

		// Limit Videos
		if (isset($settings['video_limit']) == FALSE OR trim($settings['video_limit']) == FALSE) $settings['video_limit'] = 999999;


		$vData['settings'] = array_merge($defaults, $settings);

		if (isset($vData['settings']['cv_services']) == false) {
			$vData['settings']['cv_services'] = array('youtube', 'vimeo');
		}

		/*
		// Sometimes you forget to fill in field
		// and you will send back to the form
		// We need to fil lthe values in again.. *Sigh* (anyone heard about AJAX!)
		if (is_array($data) == TRUE && isset($data['tags']) == TRUE)
		{
			foreach ($data['tags'] as $tag)
			{
				$vData['assigned_tags'][] = $tag;
			}

			return $this->EE->load->view('pbf_field', $vData, TRUE);
		}
		*/

		//----------------------------------------
		// JSON
		//----------------------------------------
		$vData['json'] = array();
		$vData['json']['layout'] = (isset($settings['cv_layout']) == TRUE) ? $settings['cv_layout'] : 'table';
		$vData['json']['field_name'] = $this->field_name;
		$vData['json']['services'] = $settings['cv_services'];
		$vData['json'] = ee()->channel_videos_helper->generate_json($vData['json']);

		$vData['layout'] = (isset($settings['cv_layout']) == TRUE) ? $settings['cv_layout'] : 'table';

		//----------------------------------------
		// Auto-Saved Entry?
		//----------------------------------------
		if (ee()->input->get('use_autosave') == 'y')
		{
			$vData['entry_id'] = FALSE;
			$old_entry_id = $this->content_id();//ee()->input->get_post('entry_id');
			$query = ee()->db->select('original_entry_id')->from('exp_channel_entries_autosave')->where('entry_id', $old_entry_id)->get();
			if ($query->num_rows() > 0 && $query->row('original_entry_id') > 0) $vData['entry_id'] = $query->row('original_entry_id');
		}

		// Grab Assigned Videos
		if ($vData['entry_id'] != FALSE)
		{

			// Grab all the files from the DB
			ee()->db->select('*');
			ee()->db->from('exp_channel_videos');
			ee()->db->where('entry_id', $vData['entry_id']);
			ee()->db->where('field_id', $vData['field_id']);
			ee()->db->order_by('video_order');
			$query = ee()->db->get();

			$vData['videos'] = $query->result();
			$vData['total_videos'] = $query->num_rows();
			$query->free_result();
		}


		return ee()->load->view('pbf_field', $vData, TRUE);
	}

	// ********************************************************************************* //

	/**
	 * Validates the field input
	 *
	 * @param $data Contains the submitted field data.
	 * @access public
	 * @return mixed Must return TRUE or an error message
	 */
	public function validate($data)
	{
		// Is this a required field?
		if ($this->settings['field_required'] == 'y')
		{
			if (isset($data['videos']) == FALSE OR empty($data['videos']) == TRUE)
			{
				return ee()->lang->line('video:required_field');
			}
		}

		return TRUE;
	}

	// ********************************************************************************* //

	/**
	 * Preps the data for saving
	 *
	 * @param $data Contains the submitted field data.
	 * @return string Data to be saved
	 */
	public function save($data)
	{
                ee()->cache->save('ChannelVideos/FieldData/'.$this->field_id, $data, 500);

		if (isset($data['videos']) == FALSE)
		{
			return '';
		}
		else
		{
			return 'ChannelVideos';
		}
	}

	// ********************************************************************************* //

	/**
	 * Handles any custom logic after an entry is saved.
	 * Called after an entry is added or updated.
	 * Available data is identical to save, but the settings array includes an entry_id.
	 *
	 * @param $data Contains the submitted field data. (Returned by save())
	 * @access public
	 * @return void
	 */
	public function post_save($data)
	{

		ee()->load->library('channel_videos_helper');
                $data = ee()->cache->get('ChannelVideos/FieldData/'.$this->field_id);

	//if (isset(ee()->session->cache['ChannelVideos']['FieldData'][$this->field_id]) == FALSE) return;
           if (isset($data) == FALSE  )return;
		// -----------------------------------------
		// Some Vars
		// -----------------------------------------
		//$data = ee()->session->cache['ChannelVideos']['FieldData'][$this->field_id];
		$entry_id = $this->content_id();//$this->settings['entry_id'];
		$channel_id = ee()->input->post('channel_id');
		$field_id = $this->field_id;

		// -----------------------------------------
		// Grab all Videos From DB
		// -----------------------------------------
		ee()->db->select('*');
		ee()->db->from('exp_channel_videos');
		ee()->db->where('entry_id', $entry_id);
		ee()->db->where('field_id', $field_id);
		$query = ee()->db->get();
		// Check for videos
		if (isset($data['videos']) == FALSE OR is_array($data['videos']) == FALSE)
		{
			$data['videos'] = array();
		}

		if ($query->num_rows() > 0)
		{
			// Not fresh, lets see whats new.
			foreach ($data['videos'] as $order => $video)
			{
				// Check for cover first
				//if (isset($file['cover']) == FALSE) $file['cover'] = 0;

				if (isset($video['video_id']) == FALSE) // $this->EE->channel_videos_helper->in_multi_array($video['data']->hash_id, $query->result_array()) === FALSE)
				{
					$video = ee()->channel_videos_helper->decode_json($video['data']);

					// -----------------------------------------
					// New Video!
					// -----------------------------------------
					$data = array(	'site_id'	=>	$this->site_id,
									'entry_id'	=>	$entry_id,
									'channel_id'=>	$channel_id,
									'field_id'	=>	$field_id,
									'service'	=>	$video->service,
									'service_video_id'	=>	$video->service_video_id,
									'video_title'	=>	$video->video_title,
									'video_desc'	=>	$video->video_desc,
									'video_username'=>	$video->video_username,
									'video_author'	=>	$video->video_author,
									'video_author_id'=>	$video->video_author_id,
									'video_date'	=>	$video->video_date,
									'video_views'	=>	$video->video_views ?: 1,
									'video_duration'=>	$video->video_duration,
									'video_url'		=>	$video->video_url,
									'video_img_url'	=>	$video->video_img_url,
									'video_order'	=>	$order,
									'video_cover'	=>	0,
								);

					ee()->db->insert('exp_channel_videos', $data);
				}
				else
				{
					// Check for duplicate Videos!
					if (isset($video['video_id']) != FALSE)
					{
						// Update Video
						$data = array(	'video_order'	=>	$order,
										'video_cover'	=>	0,
									);

						ee()->db->update('exp_channel_videos', $data, array('video_id' =>$video['video_id']));
					}
				}
			}
		}
		else
		{
			foreach ($data['videos'] as $order => $video)
			{
				$video = ee()->channel_videos_helper->decode_json($video['data']);

				// Check for cover first
				//if (isset($file['cover']) == FALSE) $file['cover'] = 0;

				// -----------------------------------------
				// New Video
				// -----------------------------------------
				$data = array(	'site_id'	=>	$this->site_id,
								'entry_id'	=>	$entry_id,
								'channel_id'=>	$channel_id,
								'field_id'	=>	$field_id,
								'service'	=>	$video->service,
								'service_video_id'	=>	$video->service_video_id,
								'video_title'	=>	$video->video_title,
								'video_desc'	=>	$video->video_desc,
								'video_username'=>	$video->video_username,
								'video_author'	=>	$video->video_author,
								'video_author_id'=>	$video->video_author_id,
								'video_date'	=>	$video->video_date,
								'video_views'	=>	$video->video_views ?: 1,
								'video_duration'=>	$video->video_duration,
								'video_url'		=>	$video->video_url,
								'video_img_url'	=>	$video->video_img_url,
								'video_order'	=>	$order,
								'video_cover'	=>	0,
							);

				ee()->db->insert('exp_channel_videos', $data);
			}
		}

		return;
	}

	// ********************************************************************************* //

	/**
	 * Handles any custom logic after an entry is deleted.
	 * Called after one or more entries are deleted.
	 *
	 * @param $ids array is an array containing the ids of the deleted entries.
	 * @access public
	 * @return void
	 */
	public function delete($ids)
	{

		foreach ($ids as $item_id)
		{
                     $this->entry_id = $this->content_id();
                     //$this->settings['entry_id']=$item_id;
			ee()->db->where('entry_id', $item_id);
			ee()->db->delete('exp_channel_videos');
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

		// Does our settings exist?
		if (isset($data['cv_services']) == TRUE)
		{

			if (is_string($data['cv_services']) == TRUE) $d = array($data['cv_services']);
			elseif (is_array($data['cv_services']) == TRUE) $d = $data['cv_services'];
			else $d = array(lang('cv:service:youtube'),lang('cv:service:vimeo'));
		}
		else
		{

			$d =  array(
                                            lang('cv:service:youtube')=>lang('cv:service:youtube'),
                                            lang('cv:service:vimeo')=>lang('cv:service:vimeo')
                                          );
		}


                $settings = array(

                            array(
                                 'title' => lang('cv:services_option'),
                            'fields' => array(
                                'cv_services' => array(
                                      'type' => 'checkbox',
                                      'choices' => array(
                                            lang('cv:service:youtube')=>lang('cv:service:youtube'),
                                            lang('cv:service:vimeo')=>lang('cv:service:vimeo')
                                          ),
                                      'value' =>$d                            ,
                                     // 'nested' => TRUE,
                                    // 'wrap' => TRUE
                              ),
                            )),

                       array(
                                 'title' => lang('cv:layout'),
                           'fields' => array(
                              'cv_layout' => array(
                                      'type' => 'inline_radio',
                                      'choices' => array(
                                           lang('cv:layout:table')=>lang('cv:layout:table'),
                                           lang('cv:layout:tiles')=>lang('cv:layout:tiles')),
                                      'value' =>( (isset($data['cv_layout']) == TRUE) ? $data['cv_layout'] : lang('cv:layout:table')),

                              )
                            ),
                        ),
                     array(
                                 'title' =>'ACT URL',
                           'fields' => array(
                              'act_url' => array(
                                      'type' => 'html',
                                      'content' => '<a href="'.ee()->channel_videos_helper->getRouterUrl().'" target="_blank">'.ee()->channel_videos_helper->getRouterUrl().'</a>',

                              )
                            ),
                        ),
                );

        /*$row  = form_checkbox('cv_services[]', 'youtube', in_array('youtube', $d)) .NBS.NBS. lang('cv:service:youtube').NBS.NBS;
		$row .= form_checkbox('cv_services[]', 'vimeo', in_array('vimeo', $d)) .NBS.NBS. lang('cv:service:vimeo').NBS.NBS;
		//$row .= form_checkbox('cv_services[]', 'revver', in_array('revver', $d)) .NBS.NBS. lang('video:service:revver');
		ee()->table->add_row( lang('cv:services_option', 'cv_services'), $row);

		$layout = (isset($data['cv_layout']) == TRUE) ? $data['cv_layout'] : 'table';

		$row  = form_radio('cv_layout', 'table', (($layout == 'table') ? TRUE : FALSE)) .NBS.NBS. lang('cv:layout:table').NBS.NBS;
		$row .= form_radio('cv_layout', 'tiles', (($layout == 'tiles') ? TRUE : FALSE)) .NBS.NBS. lang('cv:layout:tiles').NBS.NBS;
		//$row .= form_checkbox('cv_services[]', 'revver', in_array('revver', $d)) .NBS.NBS. lang('video:service:revver');

		ee()->table->add_row( lang('cv:layout', 'cv_services'), $row);
		ee()->table->add_row('ACT URL', '<a href="'.ee()->channel_videos_helper->getRouterUrl().'" target="_blank">'.ee()->channel_videos_helper->getRouterUrl().'</a>');
                */



               return array('field_options_channel_videos' => array(
			'label' => 'field_options',
			'group' => 'channel_videos',
			'settings' => $settings
		));
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
		return array(
			'cv_services' => ee()->input->post('cv_services'),
			'cv_layout' => ee()->input->post('cv_layout'),
                        'field_wide' => true,
		);
	}

	// ********************************************************************************* //
}

/* End of file ft.channel_videos.php */
/* Location: ./system/expressionengine/third_party/channel_videos/ft.channel_videos.php */
