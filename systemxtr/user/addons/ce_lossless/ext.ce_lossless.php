<?php  if (!defined('BASEPATH'))
{
	exit('No direct script access allowed');
}
/**
 * CE Lossless Extension
 *
 * @author     Aaron Waldon
 * @copyright  Copyright (c) 2013 Causing Effect
 * @license    http://www.causingeffect.com/software/expressionengine/ce-lossless/license-agreement
 * @link       http://www.causingeffect.com
 */
if (!defined('CE_LOSSLESS_VERSION'))
{
	include(PATH_THIRD . 'ce_lossless/config.php');
}

class Ce_lossless_ext
{
	public $settings = array();
	public $description = 'Lossless image compression';
	public $docs_url = '';
	public $name = 'CE Lossless';
	public $settings_exist = 'n';
	public $version = CE_LOSSLESS_VERSION;

	private $EE;

	private static $debug_mode = null;
	private static $log_mode = false;
	private static $enabled = null;

	/**
	 * Constructor
	 *
	 * @param string $settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->EE = get_instance();
		$this->settings = $settings;

		if (!isset(self::$debug_mode))
		{
			//if the template debugger is enabled, and a super admin user is logged in, enable debug mode
			self::$debug_mode = false;
			if ($this->EE->session->userdata['group_id'] == 1 && $this->EE->config->item('template_debugging') == 'y' && isset($this->EE->TMPL))
			{
				self::$debug_mode = true;
			}

			if ( $this->EE->config->item('ce_lossless_log_output') == 'y' )
			{
				self::$log_mode = true;
			}
		}

		if (!isset(self::$enabled))
		{
			//get the enabled items from the config
			self::$enabled = explode('|', $this->EE->config->item('ce_lossless_enabled'));

			//include the CE Lossless library
			if (!class_exists('Ce_lossless'))
			{
				require PATH_THIRD . 'ce_lossless/libraries/Ce_lossless.php';
			}

			//set the enabled items
			Ce_lossless::set_enabled_drivers(self::$enabled);

			//set the debug mode
			Ce_lossless::$debug_mode = ( self::$debug_mode || self::$log_mode );

			//set the mac path - this defaults to '/usr/local/bin/' (common for brew), but can be changed to '/opt/local/bin/' (common for ports)
			Ce_lossless::$mac_path = $this->EE->config->item('ce_lossless_mac_path') != '' ? $this->EE->config->item('ce_lossless_mac_path') : '/usr/local/bin/';
			Ce_lossless::$linux_path = $this->EE->config->item('ce_lossless_linux_path') != '' ? $this->EE->config->item('ce_lossless_linux_path') : '';
			//command overrides
			$commands = $this->EE->config->item('ce_lossless_commands');
			if ( ! empty( $commands ) && is_array( $commands ) )
			{
				Ce_lossless::set_config( $commands );
			}
		}
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

		$hooks = array(
			'ce_img_saved' => 'compress',
			'file_after_save' => 'ee_file_saved',
			'assets_ee_subfolder_upload' => 'assets_subfolder_upload'
		);

		foreach ($hooks as $hook => $method)
		{
			//sessions end hook
			$data = array(
				'class' => __CLASS__,
				'method' => $method,
				'hook' => $hook,
				'settings' => serialize($this->settings),
				'priority' => 9,
				'version' => $this->version,
				'enabled' => 'y'
			);
			$this->EE->db->insert('extensions', $data);
		}
	}

	/**
	 * Disables the extension by removing it from the exp_extensions table.
	 *
	 * @return void
	 */
	public function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	/**
	 * Updates the extension by performing any necessary db updates when the extension page is visited.
	 *
	 * @param string $current
	 * @return mixed void on update, false if none
	 */
	public function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return false;
		}

		//some of the hooks have changed, so clear out all of the hooks and install them again
		if (version_compare($current, '1.4', '<'))
		{
			$this->disable_extension();
			$this->activate_extension();
		}

		return true;
	}

	/**
	 * Compress the image. Called by the CE Image hook.
	 *
	 * @param string $path The server path to the saved image.
	 * @param string $type The saved image file type. Can be: 'png', 'jpg', or 'gif'
	 * @return void
	 */
	public function compress($path = '', $type = '')
	{
		if ( isset($this->EE->TMPL) && $this->EE->TMPL->fetch_param('ce_lossless_disabled') == 'yes') //param
		{
			return;
		}

		//determine which programs are enabled from the config
		if (empty(self::$enabled))
		{
			$this->log_debug_messages('No drivers are enabled. Please add the installed drivers to your config.');
			return;
		}

		Ce_lossless::compress($path, $type, false);
		$this->log_debug_messages(Ce_lossless::$debug_messages);
	}

	/**
	 * Called after a file is uploaded through EE. Available as of version 2.5.3.
	 * This method is also called by Assets if uploaded to an EE directory.
	 *
	 * @param string $file_id
	 * @param array $data
	 */
	public function ee_file_saved($file_id, $data)
	{
		//if the file is an image
		if (isset($data['mime_type']) //we have a mime type
				&& in_array($data['mime_type'], array('image/jpeg', 'image/png', 'image/gif')) //a jpg, png, or gif
				&& !empty(self::$enabled)
				&& $this->EE->config->item('ce_lossless_native_hook_enabled') != 'no'
		) //one or more lossless compression programs are enabled in the config
		{
			Ce_lossless::compress($data['upload_location_id'], $data['mime_type'], true);
			$this->log_debug_messages(Ce_lossless::$debug_messages);
		}
	}

	/**
	 * Called when a file is uploaded to an Asset's subdirectory.
	 *
	 * @param string $file_path
	 */
	public function assets_subfolder_upload( $file_path )
	{
		if (!empty(self::$enabled)
			&& $this->EE->config->item('ce_lossless_native_hook_enabled') != 'no'
		) //one or more lossless compression programs are enabled in the config
		{
			Ce_lossless::compress($file_path, '', true);
			$this->log_debug_messages(Ce_lossless::$debug_messages);
		}
	}

	/**
	 * Simple method to log an array of debug messages to the EE Debug console.
	 *
	 * @param array $messages The debug messages.
	 * @return void
	 */
	protected function log_debug_messages($messages = array())
	{
		if (is_string($messages))
		{
			$messages = array($messages);
		}

		if (!is_array( $messages ) || empty($messages))
		{
			return;
		}

		if ( self::$log_mode )
		{
			//include the CE Lossless Logger library
			if ( ! class_exists('Ce_lossless_logger') )
			{
				require PATH_THIRD . 'ce_lossless/libraries/Ce_lossless_logger.php';
			}

			//set the base cache path
			//$cache_base = $this->EE->config->item( 'cache_path' );
			$cache_base = ee()->config->item( 'cache_path' );
			if ( empty( $cache_base ) )
			{
				$cache_base = str_replace( '\\', '/', APPPATH ) . 'cache/';
			}

			//instantiate the class
			$logger = new Ce_lossless_logger( str_replace( '/', DIRECTORY_SEPARATOR , $cache_base . 'ce_lossless_log.txt' ) );

			//log the messages
			$logger->log( $messages );

			//close the logger
			$logger->close();
		}

		//log to debugger
		if ( self::$debug_mode )
		{
			foreach ($messages as $message)
			{
				$this->EE->TMPL->log_item('&nbsp;&nbsp;***&nbsp;&nbsp;CE Lossless debug: ' . $message);
			}
		}
	}
}
/* End of file ext.ce_lossless.php */
/* Location: /system/expressionengine/third_party/ce_lossless/ext.ce_lossless.php */