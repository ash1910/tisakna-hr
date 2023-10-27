<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Break settings model.
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2017 Causing Effect
 * @license		http://docs.causingeffect.com/expressionengine/ce-cache/license-agreement.html
 * @link		https://www.causingeffect.com
 */

class Ce_cache_break_settings_model
{
	private $table = 'exp_ce_cache_breaking';

	/**
	 * Get the break settings for a channel.
	 *
	 * @param $channel_id
	 * @return array|false
	 */
	public function get_break_settings($channel_id)
	{
		$results = ee()->db->query("SELECT * FROM {$this->table} WHERE channel_id = ?", $channel_id);

		if ($results->num_rows() == 1) //we found the channel
		{
			return $results->row_array();
		}

		return false;
	}

	/**
	 * Get all of the break settings.
	 *
	 * @return array|false
	 */
	public function get_all_break_settings()
	{
		$results = ee()->db->query( "SELECT * FROM {$this->table} ORDER BY channel_id ASC" );

		if ( $results->num_rows() > 0 ) //we found the channel
		{
			$settings = $results->result_array();
			$results->free_result(); //free up memory

			return $settings;
		}

		return false;
	}

	/**
	 * Get the break channel title by channel id.
	 *
	 * @param $channel_id
	 * @return bool
	 */
	public function get_channel_title_by_id($channel_id)
	{
		if ($channel_id === '0') //this id is designated to include settings for all channel entries
		{
			return lang('ce_cache_any_channel');
		}

		//a specific channel id is possibly set - get all of the channels
		$results = ee()->db->query('SELECT channel_title FROM exp_channels WHERE channel_id = ?', $channel_id);

		if ($results->num_rows() == 1) //we found the channel
		{
			$result = $results->row_array();
			return $result['channel_title'];
		}

		return false;
	}

	/**
	 * Deletes break settings.
	 *
	 * @param $channel_id
	 */
	public function delete_break_settings($channel_id)
	{
		ee()->db->delete($this->table, array('channel_id' => $channel_id));
	}

	/**
	 * Saves break settings.
	 *
	 * @param $data
	 * @return mixed
	 */
	public function save_break_settings($data)
	{
		//save the data
		return ee()->db->insert($this->table, $data);
	}

	/**
	 * Processes and saves the form if submitted.
	 *
	 * @param $channel_id
	 * @return array The form variables.
	 */
	public function process_vars($channel_id, $mcp_action_url)
	{
		//the variable defaults
		$vars = array(
			'refresh_time' => 15,
			'refresh' => false,
			'tags' => array(),
			'items' => array(),
			'errors' => array()
		);

		//get saved settings if they exist
		$break_settings = $this->get_break_settings($channel_id);
		if ($break_settings) //we found the channel
		{
			$vars['refresh'] = ($break_settings['refresh'] == 'y');
			$vars['refresh_time'] = $break_settings['refresh_time'];
			$vars['tags'] = explode('|', $break_settings['tags']);
			$vars['items'] = explode('|', $break_settings['items']);
		}

		if (ee()->input->post('submit', true) !== false) //the form was submitted
		{
			$submitted_items = ee()->input->post('items', true);
			$vars['items'] = (!empty($submitted_items) && is_array($submitted_items)) ? $submitted_items : array();
			$submitted_tags = ee()->input->post('tags', true);
			$vars['tags'] = !empty($submitted_tags) ? explode('|', $submitted_tags) : array();

			$submitted_refresh = ee()->input->post('refresh', true);
			$vars['refresh'] = ($submitted_refresh == 'y');

			$submitted_refresh_time = ee()->input->post('ce_cache_refresh_time', true);
			if (is_numeric($submitted_refresh_time) && in_array($submitted_refresh_time, array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15)))
			{
				$vars['refresh_time'] = $submitted_refresh_time;
			}
			else //invalid refresh time
			{
				$vars['errors'][] = lang('ce_cache_error_invalid_refresh_time');
			}

			//validate the items
			foreach ($vars['items'] as $index => $item)
			{
				$item = trim($item);
				if (empty($item))
				{
					unset($vars['items'][$index]);
					continue;
				}

				//make sure the item has a valid start pattern
				if (!preg_match('@^(local|global|static|any|non\-global)/@si', $item))
				{
					$vars['errors'][] = lang('ce_cache_error_invalid_path_start');
					continue;
				}

				//make sure the path length is <= 250 chars
				if (strlen($item) > 250)
				{
					$vars['errors'][] = lang('ce_cache_error_invalid_path_length');
					continue;
				}
			}

			//validate the tags
			foreach ($vars['tags'] as $index => $tag)
			{
				$tag = trim($tag);

				if (empty($tag))
				{
					unset($vars['tags'][$index]);
					continue;
				}

				//make sure the tag doesn't contain a pipe character
				if (strpos($tag, '|') !== false)
				{
					$vars['errors'][] = lang('ce_cache_error_invalid_tag_character');
					continue;
				}

				//make sure the tag is <= 100 chars
				if (strlen($tag) > 100)
				{
					$vars['errors'][] = lang('ce_cache_error_invalid_tag_length');
					continue;
				}
			}

			if (! empty($vars['errors'])) //errors
			{
				//show error message in cp flash message
				ee()->session->set_flashdata('message_failure', lang('ce_cache_save_settings_error'));
			}
			else //no errors
			{
				//delete any previously saved data for this chanel
				$this->delete_break_settings($channel_id);

				//save the data
				$data = array(
					'channel_id' => $channel_id,
					'tags' => implode('|', $vars['tags']),
					'items' => implode('|', $vars['items']),
					'refresh' => ($vars['refresh']) ? 'y' : 'n',
					'refresh_time' => $vars['refresh_time']
				);
				$saveSuccess = $this->save_break_settings($data);

				//show cp flash message
				if ($saveSuccess)
				{
					ee()->session->set_flashdata('message_success', lang('ce_cache_save_settings_success'));
				}
				else
				{
					ee()->session->set_flashdata('message_failure', lang('ce_cache_save_settings_fail'));
				}
			}

			//redirect back to this page (unfortunately, a redirect seems to be required to show a cp message)
			ee()->functions->redirect($mcp_action_url);
		}

		//trim all tags and make sure empty tags are removed
		foreach ($vars['tags'] as $index => $tag)
		{
			$tag = trim($tag);
			if (empty($tag))
			{
				unset($vars['tags'][$index]);
			}
		}

		//trim all items and make sure empty items are removed
		foreach ($vars['items'] as $index => $item)
		{
			$item = trim($item);
			if (empty($item))
			{
				unset($vars['items'][$index]);
			}
		}

		return $vars;
	}
}