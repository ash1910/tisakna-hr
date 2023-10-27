<?php

/**
 * DataGrab channel_images fieldtype class
 *
 * @package   DataGrab
 * @author    WMD <wmd.hr>
 * @copyright Copyright (c) WMD
 */
class Datagrab_channel_images extends Datagrab_fieldtype {

	var $cache_path = PATH_CACHE;

	var $total_channel_images = 9;

    function __construct(){
		//$this->EE =& get_instance();
		ee()->load->add_package_path(PATH_THIRD . 'channel_images/');
    }

	function register_setting( $field_name ) {
		$field_names = array();
		for ($i=2; $i <= $this->total_channel_images; $i++) {
			$field_names[] = $field_name . "_image".$i;
		}
		return $field_names;
	}

	function display_configuration( $field_name, $field_label, $field_type, $data ) {

		// Get current saved setting
		if( isset( $data["default_settings"]["cf"] ) ) {
			$default = $data["default_settings"]["cf"];
		} else {
			$default = array();
		}

		// Build config form
		$config = array();
		$config["label"] = form_label($field_label);
		$field_value = 	"";

		for ($i=1; $i <= $this->total_channel_images; $i++) {

			$fieldName = $field_name;
			if( $i>1 ) $fieldName .= '_image'.$i;

			$field_value .= "<p>Image {$i}: " . NBS .
			form_dropdown( 
				$fieldName, 
				$data["data_fields"], 
				isset( $default[ $fieldName ] ) ? $default[ $fieldName ] : ''
				) . 
			"</p>";
		}

		$config["value"] = $field_value;
				
		return $config;
	}

	function prepare_post_data( $DG, $item, $field_id, $field, &$data, $update = FALSE ) {
	}

	function final_post_data( $DG, $item, $field_id, $field, &$data, $entry_id = 0 ) {

		$update_entry = $DG->settings["config"]["update"];
		if( ($entry_id > 0) && isset($update_entry) && ($update_entry == 'n') ) return 0;

		$key = time().rand(1,999);

		$temp_dir = $this->cache_path.'channel_images/field_'.$field_id.'/'.$key.'/';

        if (@is_dir($temp_dir) === FALSE) {
            @mkdir($temp_dir, 0777, true);
            @chmod($temp_dir, 0777);
        }
		
		$field_settings = ee()->db->get_where('exp_channel_fields', array('field_id'=>$field_id))->row()->field_settings;
		$field_settings = unserialize(base64_decode( $field_settings ));
		$settings = $field_settings["channel_images"];
		$images_name = array();

		// check first image and upload
		for ($i=1; $i <= $this->total_channel_images; $i++) { 

			$field_name = $field;
			if( $i>1 ) $field_name .= '_image'.$i;
			$column_name = $DG->settings["cf"][$field_name];

			if(!empty($column_name) && !empty(@$item[$column_name])){
				$fileurl = trim($item[$column_name]);
				//$original_filename = basename($fileurl);
				$original_filename = str_replace('/','_',substr(parse_url($fileurl, PHP_URL_PATH), 1));
				$original_filename = strtolower(ee()->security->sanitize_filename($original_filename));
				$filename = str_replace(array(' ', '+', '%'), array('_', '', ''), $original_filename);

				// check image exists or not (same image name not allowed)
				//$num_rows = ee()->db->get_where('exp_channel_images', array('filename'=>$filename))->num_rows();
				//ovo je zakomentirano, da se ne stavlja link na sliku, nego za svaki entry nove slika, cak i ako postoje, iz istog razloga zakomentirano 237-257
				//dodana je nova linija, da provjerava filename ali i entry_id, tako da preskače iste slike unutar ovog entrya (bitno za update, da ne doda nove slike)
				$num_rows = ee()->db->get_where('exp_channel_images', array('filename'=>$filename, 'entry_id'=>$entry_id))->num_rows();
				if( isset($num_rows) && !empty($num_rows)) continue;

				if( in_array($filename,$images_name) ) continue;

				$images_name[] = $filename;
				$extension = '.' . substr( strrchr($filename, '.'), 1);
				//$fileurl = str_replace(' ','%20',$fileurl);
				$fileurl = str_replace(['%2F', '%3A', '+'], ['/', ':', '%20'], urlencode($fileurl));
				$content = @file_get_contents($fileurl);
				//echo "<pre>";print_r($fileurl);print_r($content);exit;
				if( ($content !== FALSE) && (file_put_contents($temp_dir.$filename, $content) !== FALSE ) && (@exif_imagetype($temp_dir.$filename) !== FALSE)) {
					$writefile = TRUE;


					// -----------------------------------------
					// Load Actions :O
					// -----------------------------------------
					$actions = ee('channel_images:Actions')->actions;

					// Just double check for actions groups
					if (isset($settings['action_groups']) == FALSE) $settings['action_groups'] = array();
					// -----------------------------------------
					// Loop over all action groups!
					// -----------------------------------------
					foreach ($settings['action_groups'] as $group) {
						$size_name = $group['group_name'];
						$size_filename = str_replace($extension, "__{$size_name}{$extension}", $filename);

						// Make a copy of the file
						@copy($temp_dir.$filename, $temp_dir.$size_filename);
						@chmod($temp_dir.$size_filename, 0777);

						// -----------------------------------------
						// Loop over all Actions and RUN! OMG!
						// -----------------------------------------
						foreach($group['actions'] as $action_name => $action_settings) {
							// RUN!
							$actions[$action_name]->settings = $action_settings;
							$actions[$action_name]->settings['field_settings'] = $settings;
							$res = $actions[$action_name]->run($temp_dir.$size_filename, $temp_dir);

							if ($res !== TRUE) {
								@unlink($temp_dir.$size_filename);
							}
						}

						if (is_resource(ImageAction::$imageResource)) imagedestroy(ImageAction::$imageResource);
					}

					$filesize = @filesize($temp_dir.$filename);

					// -----------------------------------------
					// Keep Original Image?
					// -----------------------------------------
					if (isset($settings['keep_original']) == TRUE && $settings['keep_original'] == 'no')
					{
						@unlink($temp_dir.$filename);
					}

					// process data
					$image = array();
					$preview_url = ee('channel_images:Helper')->getRouterUrl('url', 'simple_image_url');

					// Are we using the original file?
					if ($settings['small_preview'] == $filename) {
						$small_img_filename = $settings['small_preview'];
						$big_img_filename = $settings['small_preview'];
					} else {
						$small_img_filename = str_replace($extension, "__{$settings['small_preview']}{$extension}", urlencode($filename) );
						$big_img_filename = str_replace($extension, "__{$settings['big_preview']}{$extension}", urlencode($filename) );
					}

					$image['success'] = 'yes';
					$image['title'] = ucfirst(str_replace('_', ' ', str_replace($extension, '', $filename)));
					$image['url_title'] = url_title(trim(strtolower($image['title'])));
					$image['description'] = '';
					$image['image_id'] = (string)0;
					$image['category'] = '';
					$image['cifield_1'] = '';
					$image['cifield_2'] = '';
					$image['cifield_3'] = '';
					$image['cifield_4'] = '';
					$image['cifield_5'] = '';
					$image['filename'] = $filename;
					$image['filesize'] = (string)$filesize;
					$image['iptc'] = 'YTowOnt9';
					$image['exif'] = 'YTowOnt9';
					$image['xmp'] = '';
					$image['small_img_url'] = "{$preview_url}&amp;f={$small_img_filename}&amp;fid={$field_id}&amp;d={$key}&amp;temp_dir=yes";
					$image['big_img_url'] = "{$preview_url}&amp;f={$big_img_filename}&amp;fid={$field_id}&amp;d={$key}&amp;temp_dir=yes";
					$image['cover'] = 'MA==';
					$image['link_image_id'] = (string)0;

					$data["field_id_".$field_id]["images"][$i]["data"] = trim(json_encode($image));

					//echo "<pre>";print_r($data);exit;
					
				}
			}

		}

		if( @empty($data["field_id_".$field_id]["images"]) === TRUE ){
			@rmdir($temp_dir);
		}

		$data["field_id_".$field_id]["key"] = $key;
		$data["locationtype"] = "local";

	}

	function post_process_entry( $DG, $item, $field_id, $field, &$data, $entry_id ) {

		//echo "<pre>";print_r($DG->channel_defaults);print_r($entry_id);exit;

		$update_entry = $DG->settings["config"]["update"];
		if( isset($data["existing_entry"]) && ($data["existing_entry"] == 1) && isset($update_entry) && ($update_entry == 'n') ) return 0;

		$current_images = array();
		for ($i=1; $i <= $this->total_channel_images; $i++) {
			//$column_name = 'image'.$i;

			$field_name = $field;
			if( $i>1 ) $field_name .= '_image'.$i;
			$column_name = $DG->settings["cf"][$field_name];
			
			if( isset($item[$column_name]) && !empty(@$item[$column_name]) ){

				$fileurl = trim($item[$column_name]);
				//$original_filename = basename($fileurl);
				$original_filename = str_replace('/','_',substr(parse_url($fileurl, PHP_URL_PATH), 1));
				$original_filename = strtolower(ee()->security->sanitize_filename($original_filename));
				$filename = str_replace(array(' ', '+', '%'), array('_', '', ''), $original_filename);

				$current_images[] = $filename;

				// check image exists or not (if same image name then link that image if not added already)
				//$num_rows = ee()->db->get_where('exp_channel_images', array('filename'=>$filename, 'entry_id'=>$entry_id, 'field_id'=>$field_id))->num_rows();
				//if( empty($num_rows) ){
				//	// Get Image Info
				//	$image = ee()->db->get_where('exp_channel_images', array('filename'=>$filename, 'field_id'=>$field_id))->row_array();
				//	if(isset($image) && !empty($image)){
				//		$image["link_image_id"] = $image["image_id"];
				//		$image["link_entry_id"] = $image["entry_id"];
				//		$image["link_channel_id"] = $image["channel_id"];
				//		$image["link_field_id"] = $image["field_id"];
				//		unset($image["image_id"]);
				//		$image["site_id"] = $DG->channel_defaults["site_id"];
				//		$image["entry_id"] = $entry_id;
				//		$image["channel_id"] = $DG->channel_defaults["channel_id"];
				//		$image['member_id'] = ee()->session->userdata['member_id'];
				//		$image['upload_date'] = ee()->localize->now;
				//		$image['image_order'] = $i;
				//		$image['url_title'] = "";
				//		ee()->db->insert('exp_channel_images', $image);
				//	}
				//}
			}
		}
		if( !empty($current_images) ){
			ee()->db->where_not_in('filename', $current_images);
		}
		ee()->db->where( "entry_id", $entry_id );
		ee()->db->where( "field_id", $field_id );
		ee()->db->delete('exp_channel_images'); 

		//echo "<pre>";print_r($current_images);print_r($item);print_r($field);print_r($data);print_r($entry_id);exit;

	}

}

?>