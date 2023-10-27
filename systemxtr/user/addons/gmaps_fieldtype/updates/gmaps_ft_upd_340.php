<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Update description
 *
 * @package             Gmaps for EE3
 * @author              Rein de Vries (info@reinos.nl)
 * @copyright           Copyright (c) 2015 Rein de Vries
 * @license  			http://reinos.nl/add-ons/commercial-license
 * @link                http://reinos.nl/add-ons/gmaps
 */
 
include(PATH_THIRD.'gmaps_fieldtype/config.php');
 
class Gmaps_ft_upd_340
{
	private $EE;
	private $version = '3.4.0';
	
	// ----------------------------------------------------------------

	/**
	 * Construct method
	 *
	 * @return      boolean         TRUE
	 */
	public function __construct()
	{		
		//load the classes
		ee()->load->dbforge();

		//require the settings
		require PATH_THIRD.'gmaps/settings.php';
	}

	// ----------------------------------------------------------------
	
	/**
	 * Run the update
	 *
	 * @return      boolean         TRUE
	 */
	public function run_update()
	{
        // add config tables
        $fields = array(
            'migration_id'	=> array(
                'type'			=> 'int',
                'constraint'		=> 7,
                'unsigned'		=> TRUE,
                'null'			=> FALSE,
                'auto_increment'	=> TRUE
            ),
            'site_id'  => array(
                'type'			=> 'int',
                'constraint'		=> 7,
                'unsigned'		=> TRUE,
                'null'			=> FALSE,
                'default'			=> 0
            ),
            'field_id'  => array(
                'type'			=> 'int',
                'constraint'		=> 7,
                'unsigned'		=> TRUE,
                'null'			=> FALSE,
                'default'			=> 0
            ),
            'field_settings'  => array(
                'type' 			=> 'text',
                'null'			=> FALSE
            ),
            'channel_data'  => array(
                'type' 			=> 'text',
                'null'			=> FALSE
            ),
        );

        //create the backup database
        ee()->dbforge->add_field($fields);
        ee()->dbforge->add_key('migration_id', TRUE);
        ee()->dbforge->create_table(GMAPS_FT_MAP.'_migration', TRUE);

	}
}