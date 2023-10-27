<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Break model.
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2017 Causing Effect
 * @license		http://docs.causingeffect.com/expressionengine/ce-cache/license-agreement.html
 * @link		https://www.causingeffect.com
 */

class Ce_cache_break_model
{
	/**
	 * Gets entries from the passed in ids.
	 *
	 * @param array $ids
	 */
	public function get_entries_from_ids($ids = array())
	{
		$entries = array();

		//validate the ids and get the fallback ids if needed
		$ids = $this->clean_ids($ids);

		if ( ! empty($ids) && is_array($ids) )
		{
			//get all of the data for each entry
			$results = ee()->db->query("SELECT ct.entry_id, ct.channel_id, ct.title, ct.url_title, ct.entry_date, ct.edit_date, c.channel_title, c.channel_name, cm.username as author_username, ct.author_id
				FROM exp_channel_titles ct
				LEFT JOIN exp_channels c
				ON ct.channel_id = c.channel_id
				LEFT JOIN exp_members cm
				ON ct.author_id = cm.member_id
				WHERE ct.entry_id IN ( '" . implode( "', '", $ids ) . "' )
				ORDER BY ct.channel_id ASC");
			unset( $ids );

			if ($results->num_rows() > 0) //we found the data
			{
				$entries = $results->result_array();
			}
			$results->free_result(); //free up memory
		}

		return $entries;
	}

	/**
	 * Validates the passed in ids and checks for ids in the get/post input as a fallback.
	 *
	 * @param null $ids
	 * @return array|null
	 */
	public function clean_ids($ids = null)
	{
		if (empty($ids))
		{
			$ids = array();

			//check the input for ids
			$input_ids = ee()->input->get_post( 'ids', true );
			if ( ! empty($input_ids) && is_string($input_ids) ) //still no secret, no reason to stick around
			{
				$ids = explode( '|', $input_ids );
			}
		}

		//make sure all ids are numeric
		foreach ( $ids as $index => $id )
		{
			if ( ! is_numeric( $id ) || $id < 1 )
			{
				unset( $ids[$index] );
			}
		}

		return $ids;
	}

	/**
	 * Checks if the passed in or input secret matches the config secret.
	 *
	 * @param string|null $secret
	 *
	 * @return bool
	 */
	public function does_secret_match($secret = null)
	{
		//get the input secret
		if (empty($secret)) //the secret was not passed in, check the GET and POST data
		{
			$secret = ee()->input->get_post('secret', true);
			if ( $secret === false ) //still no secret, no reason to stick around
			{
				return false;
			}
		}

		//get the config secret
		$real_secret = Ce_cache_utils::get_secret();

		//check the passed in secret against the real secret
		if ($secret != $real_secret)
		{
			return false;
		}

		return true;
	}
}