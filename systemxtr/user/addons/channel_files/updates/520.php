<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChannelFilesUpdate_520
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
		// -----------------------------------------
		// Add sizes_metadata Column
		// -----------------------------------------
		if (ee()->db->field_exists('is_draft', 'channel_files') == FALSE)
		{
			$fields = array( 'is_draft'	=> array('type' => 'TINYINT',		'unsigned' => TRUE, 'default' => 0) );
			ee()->dbforge->add_column('channel_files', $fields, 'member_id');
		}

		//exit();
	}

	// ********************************************************************************* //

}

/* End of file 400.php */
/* Location: ./system/expressionengine/third_party/channel_files/updates/400.php */