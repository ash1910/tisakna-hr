<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Install / Uninstall and updates the modules
 *
 * @package         DevDemon_ChannelVideos
 * @author          DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright       Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license         http://www.devdemon.com/license/
 * @link            http://www.devdemon.com
 * @see             http://expressionengine.com/user_guide/development/module_tutorial.html#update_file
 */
class Channel_videos_upd
{
    /**
     * Module version
     *
     * @var string
     * @access public
     */
    public $version     =   CHANNEL_VIDEOS_VERSION;

    /**
     * Module Short Name
     *
     * @var string
     * @access private
     */
    private $module_name    =   CHANNEL_VIDEOS_CLASS_NAME;

    /**
     * Has Control Panel Backend?
     *
     * @var string
     * @access private
     */
    private $has_cp_backend = 'y';

    /**
     * Has Publish Fields?
     *
     * @var string
     * @access private
     */
    private $has_publish_fields = 'n';


    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
  ///      ee() =& get_instance();
        ee()->load->dbforge();

        ee()->load->add_package_path(PATH_THIRD . 'channel_videos/');
    }

    // ********************************************************************************* //

    /**
     * Installs the module
     *
     * Installs the module, adding a record to the exp_modules table,
     * creates and populates and necessary database tables,
     * adds any necessary records to the exp_actions table,
     * and if custom tabs are to be used, adds those fields to any saved publish layouts
     *
     * @access public
     * @return boolean
     **/
    public function install()
    {
        // Load dbforge
        ee()->load->dbforge();

        //----------------------------------------
        // EXP_MODULES
        //----------------------------------------
        ee()->db->set('module_name', ucfirst($this->module_name));
        ee()->db->set('module_version', $this->version);
        ee()->db->set('has_cp_backend', $this->has_cp_backend);
        ee()->db->set('has_publish_fields', $this->has_publish_fields);
        ee()->db->insert('modules');

        //----------------------------------------
        // Actions
        //----------------------------------------
        ee()->db->set('class', ucfirst($this->module_name));
        //ee()->db->set('method', 'actionGeneralRouter');
        ee()->db->set('method',  $this->module_name . '_router');
        ee()->db->insert('actions');

        //----------------------------------------
        // EXP_MODULES
        // The settings column, Ellislab should have put this one in long ago.
        // No need for a seperate preferences table for each module.
        //----------------------------------------
        if (ee()->db->field_exists('settings', 'modules') == false) {
            ee()->dbforge->add_column('modules', array('settings' => array('type' => 'TEXT') ) );
        }

        //----------------------------------------
        // EXP_CHANNEL_VIDEOS
        //----------------------------------------
        $fields = array(
            'video_id'      => array('type' => 'INT',       'unsigned' => true, 'auto_increment' => true),
            'site_id'       => array('type' => 'TINYINT',   'unsigned' => true, 'default' => 1),
            'entry_id'      => array('type' => 'INT',       'unsigned' => true, 'default' => 0),
            'channel_id'    => array('type' => 'INT',       'unsigned' => true, 'default' => 0),
            'field_id'      => array('type' => 'MEDIUMINT', 'unsigned' => true, 'default' => 1),
            'service'       => array('type' => 'VARCHAR',   'constraint' => 250, 'default' => ''),
            'service_video_id'  => array('type' => 'VARCHAR',   'constraint' => 250, 'default' => ''),
            'hash_id'       => array('type' => 'VARCHAR',   'constraint' => 250, 'default' => ''), // We need to find unique videos
            'video_title'   => array('type' => 'VARCHAR',   'constraint' => 250, 'default' => ''),
            'video_desc'    => array('type' => 'TEXT'),
            'video_username'=> array('type' => 'VARCHAR',   'constraint' => 250, 'default' => ''),
            'video_author'  => array('type' => 'VARCHAR',   'constraint' => 250, 'default' => ''),
            'video_author_id'=> array('type' => 'INT',      'unsigned' => true, 'default' => 0),
            'video_date'    => array('type' => 'VARCHAR',   'constraint' => 250, 'default' => ''),
            'video_views'   => array('type' => 'VARCHAR',   'constraint' => 250, 'default' => ''),
            'video_duration'=> array('type' => 'VARCHAR',   'constraint' => 250, 'default' => ''),
            'video_url'     => array('type' => 'VARCHAR',   'constraint' => 250, 'default' => ''),
            'video_img_url' => array('type' => 'VARCHAR',   'constraint' => 250, 'default' => ''),
            'video_order'   => array('type' => 'SMALLINT',  'unsigned' => true, 'default' => 1),
            'video_cover'   => array('type' => 'TINYINT',   'constraint' => '1', 'unsigned' => true, 'default' => 0),
        );

        ee()->dbforge->add_field($fields);
        ee()->dbforge->add_key('video_id', true);
        ee()->dbforge->add_key('entry_id');
        ee()->dbforge->create_table('channel_videos', true);

        return true;
    }

    // ********************************************************************************* //

    /**
     * Uninstalls the module
     *
     * @access public
     * @return Boolean false if uninstall failed, true if it was successful
     **/
    public function uninstall()
    {
        // Load dbforge
        ee()->load->dbforge();

        // Remove
        ee()->dbforge->drop_table('channel_videos');

        ee()->db->where('module_name', ucfirst($this->module_name));
        ee()->db->delete('modules');
        ee()->db->where('class', ucfirst($this->module_name));
        ee()->db->delete('actions');

        return true;
    }

    // ********************************************************************************* //

    /**
     * Updates the module
     *
     * This function is checked on any visit to the module's control panel,
     * and compares the current version number in the file to
     * the recorded version in the database.
     * This allows you to easily make database or
     * other changes as new versions of the module come out.
     *
     * @access public
     * @return Boolean false if no update is necessary, true if it is.
     **/
    public function update($current = '')
    {
        // Are they the same?
        if ($current >= $this->version) {
            return false;
        }

        $current = str_replace('.', '', $current);

        // Two Digits? (needs to be 3)
        if (strlen($current) == 2) {
            $current .= '0';
        }

        $update_dir = PATH_THIRD.strtolower($this->module_name).'/updates/';

        // Does our folder exist?
        if (@is_dir($update_dir) === true) {
            // Loop over all files
            $files = @scandir($update_dir);

            if (is_array($files) == true) {

                foreach ($files as $file) {

                    if ($file == '.' OR $file == '..' OR strtolower($file) == '.ds_store') continue;

                    // Get the version number
                    $ver = substr($file, 0, -4);

                    // We only want greater ones
                    if ($current >= $ver) continue;

                    require $update_dir . $file;
                    $class = 'ChannelVideosUpdate_' . $ver;
                    $UPD = new $class();
                    $UPD->do_update();
                }
            }
        }

        // Upgrade The Module
        ee()->db->set('module_version', $this->version);
        ee()->db->where('module_name', ucfirst($this->module_name));
        ee()->db->update('exp_modules');

        return true;
    }

    // ********************************************************************************* //

} // END CLASS

/* End of file upd.channel_videos.php */
/* Location: ./system/expressionengine/third_party/channel_videos/upd.channel_videos.php */
