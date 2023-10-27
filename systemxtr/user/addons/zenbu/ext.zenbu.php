<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if( ! defined('PATH_THIRD')) { define('PATH_THIRD', APPPATH . 'third_party'); };
require_once PATH_THIRD . 'zenbu/addon.setup.php';
require_once __DIR__.'/vendor/autoload.php';

use Zenbu\librairies\ArrayHelper;
use Zenbu\librairies\Settings;
use Zenbu\librairies\Fields;
use Zenbu\librairies\Sections;
use Zenbu\librairies\platform\ee\Base as Base;
use Zenbu\librairies\platform\ee\Lang;
use Zenbu\librairies\platform\ee\Session;
use Zenbu\librairies\platform\ee\Cache;
use Zenbu\librairies\platform\ee\Url;
use Zenbu\librairies\platform\ee\View;

class Zenbu_ext extends Base {

	var $name				= ZENBU_NAME;
	var $version 			= ZENBU_VER;
	var $addon_short_name 	= 'zenbu';
	var $description		= 'Extension companion to module of the same name';
	var $settings_exist		= ZENBU_SETTINGS_EXIST;
	var $docs_url			= 'https://zenbustudio.com/software/docs/zenbu';
	var $settings        	= array();

	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings='')
	{
		$this->settings			= $settings;

		//	----------------------------------------
		//	Load Session Libraries if not available
		//	(eg. in cp_js_end hook) - EE 2.6
		//	----------------------------------------

		// Get the old last_call first, just to be sure we have it
		$old_last_call = ee()->extensions->last_call;

		if ( ! isset(ee()->session) || ! isset(ee()->session->userdata) )
        {

            if (file_exists(APPPATH . 'libraries/Localize.php'))
            {
                ee()->load->library('localize');
            }

            if (file_exists(APPPATH . 'libraries/Remember.php'))
            {
                ee()->load->library('remember');
            }

            if (file_exists(APPPATH.'libraries/Session.php'))
            {
                ee()->load->library('session');
            }
        }

		parent::__construct();

        // Restore last_call
        ee()->extensions->last_call = $old_last_call;

        ee()->lang->loadfile('zenbu');

		// Setup settings object
		$this->settingsObj    = new Settings();

		// Create settings vars
		$this->permissions = $this->settingsObj->getPermissions();
	} // END __construct()

	// --------------------------------------------------------------------


	/**
	 * after_channel_entry_save
	 * Hook: after_channel_entry_save
	 * @param $entry (object) – Current ChannelEntry model object
	 * @param $values (array) – The ChannelEntry model object data as an array
	 * @return void Redirection
	 */
	public function after_channel_entry_save($entry, $values)
	{
		// Clear the Zenbu cache after New/Edit entry
		$this->cache->delete();

		// Check if we're in the CP and the submit value.
		// We want to redirect only if the user has chosen
		// "Save & Close" (i.e. "finish").
		if(REQ == 'CP' && isset($values['submit']) && $values['submit'] == 'finish' && isset($this->permissions['edit_replace']) && $this->permissions['edit_replace'] == 'y')
		{
			$channel_id_str = isset($entry->channel_id) ? '&channel_id='.$entry->channel_id : '';
			ee()->functions->redirect(rtrim(Url::zenbuUrl(), '/') . $channel_id_str);
		}
	} // END after_channel_entry_save()

	// --------------------------------------------------------------------


	/**
	 * send_to_addon_post_edit
	 * Hook: update_multi_entries_start
	 * @return void 	Set redirection POST variable
	 */
	public function send_to_addon_post_edit()
	{
		// Taking over redirection
		// return_to_zenbu attempts to fetch the latest rules saved in session if present
		// First, check if we're in the CP and that we're accessing the update_multi_entries routine.
		if((ee()->uri->segment(1) == 'cp' && ee()->uri->segment(2) == 'content_edit' && ee()->uri->segment(3) == 'update_multi_entries') || (isset($_GET['D']) && $_GET['D'] == 'cp' && isset($_GET['C']) && $_GET['C'] == 'content_edit' && isset($_GET['M']) && $_GET['M'] == 'delete_entries'))
		{
			unset($_POST['redirect']);
			$_POST['redirect'] = base64_encode(BASE.AMP."C=addons_modules".AMP."M=show_module_cp".AMP."module=zenbu".AMP."return_to_zenbu=y");
		}
	}

	/**
	 * replace_edit_dropdown
	 * Hook: cp_js_end
	 * @return string $output The added JS.
	 */
	public function replace_edit_dropdown()
	{
		ee()->lang->loadfile('zenbu');
		ee()->lang->loadfile('content', 'cp');

		$output = '';

		// Sorry I forgot to add this, devs:
		if (ee()->extensions->last_call !== FALSE)
		{
			$output = ee()->extensions->last_call;
		}

		// Replaces the main CP nav with the addon
		if(isset($this->permissions['edit_replace']) && $this->permissions['edit_replace'] == 'y')
		{
			$output .= View::render('extension/edit_replace.js.twig');
		}

		$output .= View::render('extension/index.js.twig');

		return $output;
	} // END replace_edit_dropdown()

	// --------------------------------------------------------------------


	/**
	 * Add a Zenbu CP Nav submenu
	 * @param  object $menu The EL Menu object
	 * @return void         Submenu gets built
	 */
	public function cp_custom_menu($menu = EllisLab\ExpressionEngine\Service\CustomMenu\Menu)
	{
		$sub = $menu->addSubmenu(Lang::t('entry_manager'));
		$sub->addItem(Lang::t('entry_listing'), Url::zenbuUrl());
		$sub->addItem(Lang::t('saved_searches_list'), Url::zenbuUrl('saved_searches'));

		if(isset($this->permissions['can_access_settings']) && $this->permissions['can_access_settings'] == 'y' || $this->user->group_id == 1)
		{
			$sub->addItem(Lang::t('display_settings'), Url::zenbuUrl('settings'));
		}

		if(isset($this->permissions['can_admin']) && $this->permissions['can_admin'] == 'y' || $this->user->group_id == 1)
		{
			$sub->addItem(Lang::t('permissions'), Url::zenbuUrl('permissions'));
		}
	} // END cp_custom_menu

	// --------------------------------------------------------------------


	/**
	 * Settings Form
	 *
	 * @param	Array	Settings
	 * @return 	void
	 */
	public function settings_form()
	{
		ee()->load->helper('form');
		ee()->load->library('table');

		$query = ee()->db->query("SELECT settings FROM exp_extensions WHERE class = '".__CLASS__."'");
		$license = '';

		if($query->num_rows() > 0)
		{
			foreach($query->result_array() as $result)
			{
				$settings = unserialize($result['settings']);
				if(!empty($settings))
				{
					$license = $settings['license'];
				}
			}
		}

		$vars = array();

		$vars['settings'] = array(
			'license'	=> form_input('license', $license, "size='80'"),
			);


		return View::render('extension/settings.twig', $vars);
	} // END settings_form()

	// --------------------------------------------------------------------

	/**
	* Save Settings
	*
	* This public function provides a little extra processing and validation
	* than the generic settings form.
	*
	* @return void
	*/
	public function save_settings()
	{
		if (empty($_POST))
		{
			show_error(ee()->lang->line('unauthorized_access'));
		}

		unset($_POST['submit']);

		$settings = $_POST;

		ee()->db->where('class', __CLASS__);
		ee()->db->update('extensions', array('settings' => serialize($settings)));

		ee()->session->set_flashdata(
			'message_success',
		 	ee()->lang->line('preferences_updated')
		);
	} // END save_settings()

	// --------------------------------------------------------------------


	public function activate_extension()
	{
		$data[] = array(
		    'class'      => __CLASS__,
		    'method'    => "send_to_addon_post_edit",
		    'hook'      => "update_multi_entries_start",
		    'settings'    => serialize(array()),
		    'priority'    => 10,
		    'version'    => $this->version,
		    'enabled'    => "y"
		  );

		$data[] = array(
		    'class'      => __CLASS__,
		    'method'    => "after_channel_entry_save",
		    'hook'      => "after_channel_entry_save",
		    'settings'    => serialize(array()),
		    'priority'    => 900,
		    'version'    => $this->version,
		    'enabled'    => "y"
		  );

		$data[] = array(
		    'class'      => __CLASS__,
		    'method'    => "replace_edit_dropdown",
		    'hook'      => "cp_js_end",
		    'settings'    => serialize(array()),
		    'priority'    => 100,
		    'version'    => $this->version,
		    'enabled'    => "y"
		 );
		$data[] = array(
		    'class'      => __CLASS__,
		    'method'    => "cp_custom_menu",
		    'hook'      => "cp_custom_menu",
		    'settings'    => serialize(array()),
		    'priority'    => 100,
		    'version'    => $this->version,
		    'enabled'    => "y"
		 );

		// insert in database
		foreach($data as $key => $data)
		{
			ee()->db->insert('exp_extensions', $data);
		}
	}


	public function disable_extension()
	{
	  ee()->db->where('class', __CLASS__);
	  ee()->db->delete('exp_extensions');
	}

	  /**
	 * Update Extension
	 *
	 * This public function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	public function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		if ($current < $this->version)
		{
			// Update to version 1.0
		}

		if(version_compare($current, '2.1.0', '<'))
		{
			$data[] = array(
			    'class'      => __CLASS__,
			    'method'    => "cp_custom_menu",
			    'hook'      => "cp_custom_menu",
			    'settings'    => serialize($this->settings),
			    'priority'    => 100,
			    'version'    => $this->version,
			    'enabled'    => "y"
			 );

			// insert in database
			foreach($data as $key => $data)
			{
				ee()->db->insert('exp_extensions', $data);
			}
		} // END 2.1.0 update script

		if(version_compare($current, '2.1.3', '<'))
		{
			ee()->db->update('exp_extensions',
				array(
					'method'   => 'after_channel_entry_save',
					'hook'     => 'after_channel_entry_save',
					'priority' => 900
					),
				array(
					'class'  => __CLASS__,
					'method' => 'send_to_addon_post_delete'
					)
			);
		} // END 2.1.3 update script

		ee()->db->where('class', __CLASS__);
		ee()->db->update(
					'extensions',
					array('version' => $this->version)
		);
	}




}
// END CLASS