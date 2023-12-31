<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Files Module Extension Files
 *
 * @package			DevDemon_ChannelFiles
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/module_tutorial.html#core_module_file
 */
class Channel_files_ext
{
	public $version			= CHANNEL_FILES_VERSION;
	public $name			= 'Channel Files Extension';
	public $description		= 'Supports the Channel Files Module in various functions.';
	public $docs_url		= 'http://www.devdemon.com';
	public $settings_exist	= FALSE;
	public $settings		= array();
	public $hooks			= array('wygwam_config');

	// ********************************************************************************* //

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		$this->site_id = ee()->config->item('site_id');
	}

	// ********************************************************************************* //

	/**
	 * This hook will enable you to override your Wygwam fields� CKEditor config settings right on page load, taking your Wygwam customizations to a whole new level.
	 *
	 * @param array $config The array of config settings that are about to be JSON-ified and sent to CKEditor during field initialization.
	 * @param array $settings The full array of your field�s settings, as they were before being translated into the $config array.
	 * @access public
	 * @see http://pixelandtonic.com/wygwam/docs/wygwam_config
	 * @return array
	 */
	public function wygwam_config($config, $settings)
	{
		// Check if we're not the only one using this hook
		if(ee()->extensions->last_call !== FALSE)
		{
			$config = ee()->extensions->last_call;
		}

		// Check just to be sure!
		if (isset($config['extraPlugins']) != FALSE)
		{
			$config['extraPlugins'] .= ',channelfiles';
			$config['toolbar'][] = array('ChannelFiles');
		}

		return $config;
	}

	// ********************************************************************************* //

	/**
	 * Called by ExpressionEngine when the user activates the extension.
	 *
	 * @access		public
	 * @return		void
	 **/
	public function activate_extension()
	{
		foreach ($this->hooks as $hook)
		{
			 $data = array(	'class'		=>	__CLASS__,
			 				'method'	=>	$hook,
							'hook'      =>	$hook,
							'settings'	=>	serialize($this->settings),
							'priority'	=>	100,
							'version'	=>	$this->version,
							'enabled'	=>	'y'
      			);

			// insert in database
			ee()->db->insert('exp_extensions', $data);
		}
	}

	// ********************************************************************************* //

	/**
	 * Called by ExpressionEngine when the user disables the extension.
	 *
	 * @access		public
	 * @return		void
	 **/
	public function disable_extension()
	{
		ee()->db->where('class', __CLASS__);
		ee()->db->delete('exp_extensions');
	}

	// ********************************************************************************* //

	/**
	 * Called by ExpressionEngine updates the extension
	 *
	 * @access public
	 * @return void
	 **/
	public function update_extension($current=FALSE)
	{
		if($current == $this->version) return false;

		// Update the extension
		ee()->db
			->where('class', __CLASS__)
			->update('extensions', array('version' => $this->version));

	}

	// ********************************************************************************* //

} // END CLASS

/* End of file ext.channel_files.php */
/* Location: ./system/expressionengine/third_party/channel_files/ext.channel_files.php */