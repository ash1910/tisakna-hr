<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChannelFilesUpdate_320
{

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{

	}

	// ********************************************************************************* //

	public function do_update()
	{
		// Add the link_field_id Column
    	if (ee()->db->field_exists('date', 'channel_files') == FALSE)
		{
			$fields = array( 'date'	=> array('type' => 'INT',	'unsigned' => TRUE, 'default' => 0) );
			ee()->dbforge->add_column('channel_files', $fields, 'file_order');
		}


		//----------------------------------------
		// EXP_CHANNEL_FILES_DOWNLOAD_LOG
		//----------------------------------------
		$columns = array(
			'log_id' 	=> array('type' => 'INT',	'unsigned' => TRUE,	'auto_increment' => TRUE),
			'site_id'	=> array('type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 1),
			'file_id'	=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'entry_id'	=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'member_id'	=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'ip_address'=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'date'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
		);

		ee()->dbforge->add_field($columns);
		ee()->dbforge->add_key('log_id', TRUE);
		ee()->dbforge->add_key('file_id');
		ee()->dbforge->add_key('member_id');
		ee()->dbforge->add_key('entry_id');
		ee()->dbforge->create_table('channel_files_download_log', TRUE);
	}

	// ********************************************************************************* //

}

/* End of file 310.php */
/* Location: ./system/expressionengine/third_party/channel_files/updates/320.php */