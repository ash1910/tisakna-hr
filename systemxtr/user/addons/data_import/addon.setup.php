<?php

use EllisLab\ExpressionEngine\Service\Database;

return array(
      'author'      => 'Percipio',
      'author_url'  => 'http://brandnewbox.co.uk/',
      'name'        => 'Data_import',
      'description' => 'Data_import',
      'version'     => '2.0',
      'namespace'   => 'Data_import',
      'settings_exist' => TRUE,

      'services' => array(

	    // This service will be used to query our external database
	    // e.g., ee('help_desk:db')->select()
	    'db' => function($addon)
	    {
	      return $addon->make('data_import:Database')->newQuery();
	    },

	    // This service manages our external database connection
	    // e.g., ee('help_desk:Database')->getLog()
	    'Database' => function($addon)
	    {
	      // Makes sure we only do this work once per page request
	      static $db;

	      if (empty($db))
	      {
	        // fetch config from system/user/config/help_desk_database.php
	        $config = ee('Config')->getFile('data_import_database');

	        // create the DBConfig object
	        $db_config = new Database\DBConfig($config);

	        // select the database connection group
	        $db_config->getGroupConfig('data_import');

	        // connect to and make the Database object
	        $db = new Database\Database($db_config);
	      }

	      return $db;
	    }

	  )

);