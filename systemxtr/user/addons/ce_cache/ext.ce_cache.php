<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'ce_cache/config.php';

/**
 * CE Cache Extension
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2017 Causing Effect
 * @license		http://docs.causingeffect.com/expressionengine/ce-cache/license-agreement.html
 * @link		https://www.causingeffect.com
 */
class Ce_cache_ext
{
	public $settings 		= array();
	public $description		= 'Fragment Caching for ExpressionEngine';
	public $docs_url		= 'https://www.causingeffect.com/software/expressionengine/ce-cache';
	public $name			= 'CE Cache';
	public $settings_exist	= 'n';
	public $version			= CE_CACHE_VERSION;

	/**
	 * Constructor
	 *
	 * @param string $settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->settings = $settings;
	}

	/**
	 * Activate the extension by entering it into the exp_extensions table
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		//settings
		$this->settings = array();

		//set up the hooks
		$hooks = array(
			//template hook
			'template_fetch_template',

			//channel hooks
			'after_channel_entry_save',
			'before_channel_entry_delete',

			//comment hooks
			'after_comment_save',
			'after_comment_delete'
		);

		foreach ($hooks as $hook)
		{
			//sessions end hook
			$data = array(
				'class'		=> __CLASS__,
				'method'	=> 'hook_'.$hook,
				'hook'		=> $hook,
				'settings'	=> serialize( $this->settings ),
				'priority'	=> 9,
				'version'	=> $this->version,
				'enabled'	=> 'y'
			);
			ee()->db->insert( 'extensions', $data );
		}
	}

	/**
	 * Disables the extension by removing it from the exp_extensions table.
	 *
	 * @return void
	 */
	function disable_extension()
	{
		ee()->db->where('class', __CLASS__);
		ee()->db->delete('extensions');
	}

	/**
	 * Updates the extension by performing any necessary db updates when the extension page is visited.
	 *
	 * @param string $current
	 * @return mixed void on update, false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return false;
		}

		//some of the hooks have changed, so clear out all of the hooks and install them again
		if (version_compare($current, '2.0.0', '<'))
		{
			$this->disable_extension();
			$this->activate_extension();
		}

		return true;
	}

	/**
	 * Access template data prior to template parsing.
	 * https://docs.expressionengine.com/latest/development/extension_hooks/global/template/index.html#template-fetch-template
	 *
	 * Trying to make lemonade out of this lemon of a hook. Why make a template pre-parse hook if you cannot even change the data?
	 *
	 * @param array $row
	 * @return void
	 */
	public function hook_template_fetch_template($row)
	{
		//first check for template pre escaping
		$row['template_data'] = $this->pre_escape($row['template_data']);

		//then check globals for pre escaping
		foreach (ee()->config->_global_vars as $index => $global)
		{
			ee()->config->_global_vars[$index] = $this->pre_escape($global);
		}
	}

	/**
	 * Called after the channel entry object is inserted or updated.
	 * https://docs.expressionengine.com/latest/development/extension_hooks/model/channel-entry/index.html#after-channel-entry-save
	 *
	 * @param object $entry
	 * @param array $data
	 * @return void
	 */
	public function hook_after_channel_entry_save($entry, $data)
	{
		$this->break_cache($entry->entry_id);
	}

	/**
	 * Called before the channel entry object is deleted.
	 * https://docs.expressionengine.com/latest/development/extension_hooks/model/channel-entry/index.html#before-channel-entry-delete
	 *
	 * @param object $entry
	 * @param array $values
	 * @return void
	 */
	public function hook_before_channel_entry_delete($entry, $values)
	{
		$this->break_cache($entry->entry_id, true);
	}

	/**
	 * Called after the comment object is inserted or updated.
	 * https://docs.expressionengine.com/latest/development/extension_hooks/model/comment/index.html#after-comment-save
	 *
	 * @param object $comment
	 * @param array $values
	 * @return void
	 */
	public function hook_after_comment_save($comment, $values)
	{
		$this->break_cache($comment->entry_id);
	}

	/**
	 * Called after the comment object is deleted.
	 * https://docs.expressionengine.com/latest/development/extension_hooks/model/comment/index.html#after-comment-delete
	 *
	 * @param object $comment
	 * @param array $values
	 */
	public function hook_after_comment_delete($comment, $values)
	{
		$this->break_cache($comment->entry_id);
	}

	/**
	 * This will replace any pre-escape tags with a hash to protect them. Called
	 * by the hook_template_fetch_template hook method.
	 *
	 * @param $string
	 * @return mixed
	 */
	private function pre_escape($string)
	{
		if (strpos($string, '{exp:ce_cache:escape') !== false)
		{
			preg_match_all('@\{(exp\:ce\_cache\:escape\:(\S+))[^}]*\}(.*)\{/\\1\}@is', $string, $matches, PREG_SET_ORDER);

			foreach ($matches as $match)
			{
				ee()->session->cache['Ce_cache']['pre_escape']['id_'.$match[2]] = $match[3];
			}
		}

		return $string;
	}

	/**
	 * This method is used to break the cache.
	 *
	 * @param int|array $entry_ids
	 * @param bool $force_synchronous
	 * @return void
	 */
	private function break_cache($entry_ids, $force_synchronous = false)
	{
		//if $entry_ids is a string, turn it into an array
		if (is_numeric($entry_ids))
		{
			$entry_ids = (array) $entry_ids;
		}

		if (! is_array($entry_ids))
		{
			return;
		}

		//loop through the channel ids and validate them
		foreach ($entry_ids as $index => $entry_id)
		{
			if (! is_numeric($entry_id))
			{
				unset($entry_ids[$index]);
			}
		}

		//load the CE Cache Utils class
		$this->include_library('Ce_cache_utils');
		//get the secret
		$secret = Ce_cache_utils::get_secret();

		//create the URL
		$url = ee()->functions->fetch_site_index( 0, 0 ) . QUERY_MARKER .  'ACT=' . $this->fetch_action_id( 'Ce_cache', 'break_cache' ) .  '&ids=' . implode( '|', $entry_ids ) . '&secret=' . $secret;

		//load the CE Cache Break class
		$this->include_library('Ce_cache_break');
		$cache_break = new Ce_cache_break();

		//make sure that allow_url_fopen is set to true if permissible
		@ini_set('allow_url_fopen', true);
		//some servers will not accept the asynchronous requests if there is no user_agent
		@ini_set('user_agent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:5.0) Gecko/20100101');

		if ($force_synchronous || ee()->config->item('ce_cache_async') == 'no') //not asynchronously
		{
			$cache_break->break_cache_hook( $entry_ids, $secret );
			return;
		}

		//attempt to asynchronously send the secrets to the cache_break method of the module
		if ($cache_break->curl_it($url))
		{
			return;
		}
		else if ($cache_break->fsockopen_it($url))
		{
			return;
		}
		else //still no luck, just make it happen synchronously in this script...this could take a while
		{
			$cache_break->break_cache_hook($entry_ids, $secret);
		}
	}

	/**
	 * This little helper function is the same one used in the CP class, but Datagrab apparently breaks that one when working with CE Cache.
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
			include PATH_THIRD.'ce_cache/libraries/'.$name.'.php';
		}
	}
}
/* End of file ext.ce_cache.php */
/* Location: /system/expressionengine/third_party/ce_cache/ext.ce_cache.php */