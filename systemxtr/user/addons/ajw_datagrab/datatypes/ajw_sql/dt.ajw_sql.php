<?php

/**
 * DataGrab SQL import class
 *
 * Allows SQL imports
 * 
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */

use EllisLab\ExpressionEngine\Service\Database;

class Ajw_sql extends Datagrab_type {

	var $datatype_info = array(
		'name'		=> 'SQL',
		'version'	=> '0.1',
		'allow_subloop' => TRUE,
		'allow_multiple_fields' => TRUE
		);

	var $settings = array(
		"server" => "",
		"database" => "",
		"username" => "",
		"password" => "",
		"query" => ""
		);

	var $items;

	function settings_form( $values = array() ) {

		$form = array(
		array( 
			form_label('Database Server', 'server') .
			'<div class="subtext"></div>', 
			form_input(
				array(
					'name' => 'server',
					'id' => 'server',
					'value' => $this->get_value( $values, "server" ),
					'size' => '50'
					)
				) 
			),
		array( 
			form_label('Database Name', 'database') .
			'<div class="subtext"></div>', 
			form_input(
				array(
					'name' => 'database',
					'id' => 'database',
					'value' => $this->get_value( $values, "database" ),
					'size' => '50'
					)
				) 
			),
		array( 
			form_label('Database User', 'username') .
			'<div class="subtext"></div>', 
			form_input(
				array(
					'name' => 'username',
					'id' => 'username',
					'value' => $this->get_value( $values, "username" ),
					'size' => '50'
					)
				) 
			),
		array( 
			form_label('Database Password', 'password') .
			'<div class="subtext"></div>', 
			form_password(
				array(
					'name' => 'password',
					'id' => 'password',
					'value' => $this->get_value( $values, "password" ),
					'size' => '50'
					)
				) 
			),
		array( 
			form_label('SQL Query', 'query') .
			'<div class="subtext"></div>', 
			form_textarea(
				array(
					'name' => 'query',
					'id' => 'query',
					'value' => $this->get_value( $values, "query" ),
					'size' => '50'
					)
				) 
			)
		);

		return $form;
	}

	function fetch() {
		
		// Try to connect to database using settings provided
		$config = array(
			'hostname' => $this->settings['server'],
			'username' => $this->settings['username'],
			'password' => $this->settings['password'],
			'database' => $this->settings['database'],
			'dbdriver' => 'mysql',
			'pconnect' => FALSE,
		);

		$get_defined_constants = get_defined_constants(true);
		$PATH_DICT = $get_defined_constants['user']['PATH_DICT'];

		$file = $PATH_DICT.'datagrab_database.php';
		// Open the file to get existing content
		//$current = file_get_contents($file);
		// Append a new person to the file
		$current = "<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');";
		$current .= "\n\n$";
		$current .= "config['database'] = array( 'datagrab' => array( 
		'hostname' => '{$this->settings['server']}', 
		'database' => '{$this->settings['database']}', 
		'username' => '{$this->settings['username']}', 
		'password' => '{$this->settings['password']}', 
		'dbprefix' => '', 
		'port'  => '' ), 
		);";
		// Write the contents back to the file
		file_put_contents($file, $current);

		$db_config = new Database\DBConfig(ee('Config')->getFile('datagrab_database'));

		$db_config->getGroupConfig('datagrab');
		$connection = new Database\Database($db_config);
		$db = $connection->newQuery();

		//echo "<pre>";print_r($db);exit;
		
		//$db = $this->EE->load->database( $config, TRUE );
		
		/*if( $db->conn_id == "" ) {
			$this->errors[] = "Could not connect to database";
			return -1;
		}*/
		
		// Try to run query
		$db->trans_start();
		$query = $db->query( $this->settings["query"] );
		$db->trans_complete();

		if( $db->trans_status() === FALSE ) {
			$this->errors[] = "Error in SQL";
			return -1;
		}

		// No results
		if( $query->num_rows() == 0 ) {
			$this->errors[] = "No rows returned by query";
			return -1;
		}
		
		$this->items = $query->result_array();

		//echo "<pre>";print_r($this->items);exit;
		
	}

	function next() {
		
		$item = current( $this->items );
		next( $this->items );
		
		return $item;

		//return $this->items[0];
	}
	function initialise_sub_item( $item, $id, $config, $field ) {
		// Reset sub loop
		$this->sub_item_ptr = 0;
		return TRUE;
	}
	
	function get_sub_item( $item, $id, $config, $field ) {
		
		// Find delimiter (if set)
		$delimiter = ",";
		if( isset( $config["cf"][ $field . "_delimiter" ] ) ) {
			$delimiter = $config["cf"][ $field . "_delimiter" ];
		}
	
		// Find item and split into sub items
		$item = $this->get_item( $item, $id );
		$sub_items = explode($delimiter, $item);
		// print_r( $item );
		//$sub_items = $this->_csvstring_to_array( $item, $delimiter, "'" );
		$no_elements = count($sub_items);
		
		// Return false if there are no items to return
		$this->sub_item_ptr++;
		if( $no_elements == "" || $this->sub_item_ptr > $no_elements ) {
			return FALSE;
		}
		
		// Return sub item
		return trim($sub_items[$this->sub_item_ptr - 1]);
	}
	function fetch_columns() {

		$this->fetch();

		$columns = $this->items[0]; //$this->next();


		$titles = array();
		$count = 0;
		foreach( $columns as $idx => $label ) {
			$title = $columns[ $idx ];
			if ( strlen( $title ) > 48 ) {
				$title = substr( $title, 0, 48 ) . "...";
			}
			$titles[ $idx ] = $idx . " - eg, " . $title;
		}

		return $titles;
	}

}

?>