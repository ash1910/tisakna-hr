<?php namespace Zenbu\librairies;

use Zenbu\librairies\platform\ee\Base as Base;
use Zenbu\librairies\platform\ee\Cache;
use Zenbu\librairies\platform\ee\Convert;
use Zenbu\librairies\platform\ee\Db;
use Zenbu\librairies\platform\ee\Lang;
use Zenbu\librairies\platform\ee\Request;
use Zenbu\librairies\platform\ee\Session;
use Zenbu\librairies\platform\ee\SettingsBase as SettingsBase;

class Settings extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->settingsBase = new SettingsBase();
    }

    /**
     * Retrieve General settings
     * @return array      The General Settings
     */
    public function getGeneralSettings($what = '')
    {
        $output = $this->settingsBase->getGeneralSettings($what);
        $output['default_2nd_filter_type'] = 3;

        if(isset($output['default_1st_filter']))
        {
            if(in_array($output['default_1st_filter'], array('id', 'author', 'category')))
            {
                $output['default_2nd_filter_type'] = 1;
            }
            elseif(in_array($output['default_1st_filter'], array('entry_date', 'expiration_date', 'edit_date')))
            {
                $output['default_2nd_filter_type'] = 2;
            }
            elseif($output['default_1st_filter'] == 'status')
            {
                $output['default_2nd_filter_type'] = 0;
            }
        }

        return $output;
    } // END getGeneralSettings()

    // --------------------------------------------------------------------


    /**
     * Retrieve Display Settings
     * @param  string $what Additional filtering of settings
     * @return array       The settings
     */
	public function getDisplaySettings($what = '')
    {
        $this->settingsBase->setAllFields($this->allFields);
        return $this->settingsBase->getDisplaySettings($what);
    } // END getDisplaySettings

    // --------------------------------------------------------------------


    /**
     * Fallback 2: Retrieve a set of default Display Settings
     * @return array The settings
     */
    public function getDefaultDisplaySettings()
    {

        return $this->settingsBase->getDefaultDisplaySettings();
    } // END getDefaultDisplaySettings()

    // --------------------------------------------------------------------


    /**
     * Retrieve a list of member groups that have access to the addon
     * @return array Array of member group_ids
     */
    public function getGroupsWithAddonAccess()
    {
        return $this->settingsBase->getGroupsWithAddonAccess();
    } // END getGroupsWithAddonAccess()

    // --------------------------------------------------------------------


    /**
     * Retrieve a permissions
     * @return array Array of permissions
     */
    public function getPermissions()
    {
        return $this->settingsBase->getPermissions();
    } // END getPermissions()

    // --------------------------------------------------------------------


    /**
     * Retrieve permissions for all groups
     * @return array Array of permissions
     */
    public function getPermissionsAllGroups()
    {
        return $this->settingsBase->getPermissionsAllGroups();
    } // END getPermissionsAllGroups()

    // --------------------------------------------------------------------


    /**
     * Is Debug enabled?
     * @return bool
     */
    public function isDebugEnabled()
    {
        return SettingsBase::isDebugEnabled();
    } // END isDebugEnabled()

    // --------------------------------------------------------------------


    /**
     * Retrieve extra display settings
     * @return bool
     */
    public function getExtraDisplaySettingsFields()
    {
        return $this->settingsBase->getExtraDisplaySettingsFields();
    } // END getExtraDisplaySettingsFields()

    // --------------------------------------------------------------------
}