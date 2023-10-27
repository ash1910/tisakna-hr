<?php

/**
 * Config file for Unique Fields
 *
 * @package			Unique_fields
 * @author			Simon Andersohn
 * @copyright 		Copyright (c) 2016
 * @license 		
 * @link			
 * @see				
 */

if ( ! defined('UNIQUE_CHANNEL_FIELDS_NAME'))
{
	define('UNIQUE_CHANNEL_FIELDS_NAME',         'Unique Channel Fields');
	define('UNIQUE_CHANNEL_FIELDS_CLASS_NAME',   'unique_channel_fields');
	define('UNIQUE_CHANNEL_FIELDS_DESCRIPTION',  'Checks if field value already exists within a channel while editing/updating entries');
	define('UNIQUE_CHANNEL_FIELDS_VERSION',      '1.3.1');
	define('UNIQUE_CHANNEL_FIELDS_DOCS_URL', 	'https://github.com/ignetic/ee-unique-channel-fields'); 
}

$config['name'] 	= UNIQUE_CHANNEL_FIELDS_NAME;
$config['version'] 	= UNIQUE_CHANNEL_FIELDS_VERSION;

/* End of file config.php */
/* Location: ./system/expressionengine/third_party/unique_channel_fields/config.php */
