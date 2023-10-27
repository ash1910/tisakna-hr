<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'ce_cache/config.php';

/**
 * CE Cache - Module Update File
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2017 Causing Effect
 * @license		http://docs.causingeffect.com/expressionengine/ce-cache/license-agreement.html
 * @link		https://www.causingeffect.com
 */
class Ce_cache_upd {

	public $version = CE_CACHE_VERSION;

	/**
	 * Installation Method
	 *
	 * @return 	boolean 	true
	 */
	public function install()
	{
		//install the module
		$mod_data = array(
			'module_name'			=> 'Ce_cache',
			'module_version'		=> $this->version,
			'has_cp_backend'		=> 'y',
			'has_publish_fields'	=> 'n'
		);
		ee()->db->insert( 'modules', $mod_data );

		//setup the tables
		$this->set_up_tables();

		//add actions
		ee()->db->insert( 'actions', array( 'class' => 'Ce_cache', 'method' => 'break_cache' ) );
		ee()->db->insert( 'actions', array( 'class' => 'Ce_cache_mcp', 'method' => 'ajax_get_level' ) );
		ee()->db->insert( 'actions', array( 'class' => 'Ce_cache_mcp', 'method' => 'ajax_delete' ) );

		return true;
	}

	/**
	 * Uninstall
	 *
	 * @return 	boolean 	true
	 */
	public function uninstall()
	{
		//get the module id
		$mod_id = ee()->db->select( 'module_id' )->get_where( 'modules', array( 'module_name'	=> 'Ce_cache' ) )->row( 'module_id' );

		//remove the module by id from the module member groups
		ee()->db->where( 'module_id', $mod_id )->delete( 'module_member_groups' );

		//remove the module
		ee()->db->where( 'module_name', 'Ce_cache' )->delete( 'modules' );

		//remove the actions
		ee()->db->where( 'class', 'Ce_cache' );
		ee()->db->delete( 'actions' );
		ee()->db->where( 'class', 'Ce_cache_mcp' );
		ee()->db->delete( 'actions' );

		ee()->load->dbforge();

		//tables
		$tables = array(
			'ce_cache_db_driver',
			'ce_cache_breaking',
			'ce_cache_tagged_items'
		);

		//remove the installed tables
		foreach ($tables as $table )
		{
			if ( ee()->db->table_exists( $table ) )
			{
				ee()->dbforge->drop_table( $table );
			}
		}

		return true;
	}

	/**
	 * Module Updater
	 *
	 * @param string $current
	 * @return boolean true
	 */
	public function update( $current = '' )
	{
		//if up-do-date or a new install, don't worry about it
		if ( empty( $current ) || $current == $this->version )
		{
			return false;
		}

		//clear all caches and add the new tables
		if ( version_compare( $current, '1.5', '<' )  )
		{
			$this->clear_all_caches();

			//setup the tables
			$this->set_up_tables();
		}

		//make sure the break cache action is added to the db
		if ( version_compare( $current, '1.5.2', '<' ) )
		{
			//remove the actions
			ee()->db->where( 'class', 'Ce_cache' );
			ee()->db->delete( 'actions' );

			//add actions
			ee()->db->insert( 'actions', array( 'class' => 'Ce_cache', 'method' => 'break_cache' ) );
		}

		//make sure the break cache action is added to the db
		if ( version_compare( $current, '1.7', '<' ) )
		{
			//add actions
			ee()->db->insert( 'actions', array( 'class' => 'Ce_cache_mcp', 'method' => 'ajax_get_level' ) );
			ee()->db->insert( 'actions', array( 'class' => 'Ce_cache_mcp', 'method' => 'ajax_delete' ) );
		}

		//the tagged items table has changed, so drop it and add it again
		if ( version_compare( $current, '1.9.1', '<' ) )
		{
			//drop the cached tagged items table
			if ( ee()->db->table_exists( 'ce_cache_tagged_items' ) )
			{
				ee()->load->dbforge();
				ee()->dbforge->drop_table( 'ce_cache_tagged_items' );
			}

			//add the new cached items table
			$this->set_up_tables();
		}

		return true;
	}

	/**
	 * Sets up the CE Cache tables.
	 *
	 * @return void
	 */
	private function set_up_tables()
	{
		//since one or more tables may have been dropped, let's clear the table name cache.
		unset( ee()->db->data_cache['table_names'] );

		//create the cache table for the db driver
		if ( ! ee()->db->table_exists( 'ce_cache_db_driver' ) )
		{
			ee()->load->dbforge();

			//specify the fields
			$fields = array(
				'id' => array( 'type' => 'VARCHAR', 'constraint' => '250', 'auto_increment' => false, 'null' => false ),
				'ttl' => array( 'type' => 'INT', 'constraint' => '10', 'null' => false, 'default' => '360' ),
				'made' => array( 'type' => 'INT', 'constraint' => '10' ),
				'content' => array( 'type' => 'LONGTEXT' )
			);
			ee()->dbforge->add_field( $fields );
			ee()->dbforge->add_key( 'id', true );
			ee()->dbforge->create_table( 'ce_cache_db_driver' );
		}

		//create the cache breaking table
		if ( ! ee()->db->table_exists( 'ce_cache_breaking' ) )
		{
			ee()->load->dbforge();

			//specify the fields
			$fields = array(
				'channel_id' => array( 'type' => 'INT', 'constraint' => '10', 'null' => false, 'unsigned' => true ),
				'tags' => array( 'type' => 'TEXT' ),
				'items' => array( 'type' => 'TEXT' ),
				'refresh_time' => array( 'type' => 'INT', 'constraint' => '1', 'unsigned' => true ),
				'refresh' => array( 'type' => 'VARCHAR', 'constraint' => '1', 'default' => 'n' )
			);
			ee()->dbforge->add_field( $fields );
			ee()->dbforge->add_key( 'channel_id', true );
			ee()->dbforge->create_table( 'ce_cache_breaking' );
		}

		//create the tagging table
		if ( ! ee()->db->table_exists( 'ce_cache_tagged_items' ) )
		{
			ee()->load->dbforge();

			//specify the fields
			$fields = array(
				'id' => array( 'type' => 'INT', 'constraint' => '10', 'unsigned' => true, 'auto_increment' => true ),
				'item_id' => array( 'type' => 'VARCHAR', 'constraint' => '250', 'null' => false ),
				'tag' => array( 'type' => 'VARCHAR', 'constraint' => '100', 'null' => false )
			);
			ee()->dbforge->add_field( $fields );
			ee()->dbforge->add_key( 'id', true );
			ee()->dbforge->create_table( 'ce_cache_tagged_items' );
		}
	}

	/**
	 * Clears all of the caches.
	 */
	private function clear_all_caches()
	{
		//clear all caches
		if ( ! class_exists( 'Ce_cache_drivers' ) ) //load the class if needed
		{
			include PATH_THIRD . 'ce_cache/libraries/Ce_cache_drivers.php';
		}
		$classes = Ce_cache_drivers::get_all_driver_classes();
		foreach ( $classes as $class )
		{
			//attempt to clear the cache for this driver class
			$class->clear();
		}
	}
}
/* End of file upd.ce_cache.php */
/* Location: /system/expressionengine/third_party/ce_cache/upd.ce_cache.php */