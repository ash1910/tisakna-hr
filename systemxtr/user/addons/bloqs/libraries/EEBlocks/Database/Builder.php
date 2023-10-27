<?php


namespace EEBlocks\Database;


/**
 *
 * Installation and update routines
 *
**/ 
class Builder {

  public $current;


  /**
   *
   * __constructor()
   *
   *
  **/
  public function __constructor( $current = null )
  {
    $this->current = $current;


  } //end __constructor()


// ----------------------------------------------------------------  


  /**
   *
   * runVersionInstaller()
   *
   * @description: given a version number, run version function if it exists
   *
   * @param none
   *
   * @return void
   *
  **/
  public function runVersionInstaller( $version )
  {

    //build out proper function name based off of version provided
    $functionName = str_replace(array('.', '-'), '', $version);
    $functionName = '_version_'.$functionName;

    //call the function
    if( method_exists($this, $functionName) )
    {
      $this->$functionName();
    }


  } //end runVersionInstaller()



/* ----------------------------------------------------------------   */
// 
//  ExpressionEngine 3 
//
/* ----------------------------------------------------------------  */


  /**
   *
   * _version_300
   *
   * @description: Initial conversion from EE2 to EE3
   *
   * @param none
   *
   * @return void
   *
  **/
  public function _version_300() 
  {

    //
    // Update module name/version
    // 
    $old_mod_name = ee()->db->select('module_name')
                            ->where('module_name', 'Blocks')
                            ->get('modules');

    if( $old_mod_name->num_rows() >= 1 )
    {

      ee()->db->where('module_name', 'blocks')
              ->update( 'modules', array('module_name' => 'Bloqs', 'module_version' => '3.0.0') );
    }

    //
    //Update fieldtype name/version
    //
    $old_ft_name = ee()->db->select('name')
                            ->where('name', 'blocks')
                            ->get('fieldtypes');

    if( $old_ft_name->num_rows() >= 1 )
    {

      ee()->db->where('name', 'blocks')
              ->update( 'fieldtypes', array('name' => 'bloqs', 'version' => '3.0.0') );
    }


    //
    //Update channel fields
    //
    $old_cf_ft = ee()->db->select('field_type')
                            ->where('field_type', 'blocks')
                            ->get('channel_fields');

    if( $old_cf_ft->num_rows() >= 1 )
    {

      ee()->db->where('field_type', 'blocks')
              ->update( 'channel_fields', array('field_type' => 'bloqs') );
    }

  } //end _version_300() 






/* ----------------------------------------------------------------   */
// 
//  ExpressionEngine 2 
//
/* ----------------------------------------------------------------  */


  /**
   *
   * _version_124
   *
   * @description: version 1.2.4
   *
   * @param none
   *
   * @return void
   *
  **/
  public function _version_124() 
  {
    $constraint_exists = ee()->db->query('SELECT * FROM information_schema.table_constraints WHERE constraint_name = "fk_blocks_atomdefinition_blockdefinition" AND table_schema = database()');
    if( $constraint_exists->num_rows() > 0 )
    {
      ee()->db->query('ALTER TABLE exp_blocks_atomdefinition DROP FOREIGN KEY fk_blocks_atomdefinition_blockdefinition;');
    }

    $constraint_exists = ee()->db->query('SELECT * FROM information_schema.table_constraints WHERE constraint_name = "fk_blocks_blockdefinition_block" AND table_schema = database()');
    if( $constraint_exists->num_rows() > 0 )
    {
      ee()->db->query('ALTER TABLE exp_blocks_block DROP FOREIGN KEY fk_blocks_blockdefinition_block;');
    }
    
    $constraint_exists = ee()->db->query('SELECT * FROM information_schema.table_constraints WHERE constraint_name = "fk_blocks_atom_block" AND table_schema = database()');
      if( $constraint_exists->num_rows() > 0 )
      {
        ee()->db->query('ALTER TABLE exp_blocks_atom DROP FOREIGN KEY fk_blocks_atom_block;');
      }

    $constraint_exists = ee()->db->query('SELECT * FROM information_schema.table_constraints WHERE constraint_name = "fk_blocks_atom_atomdefinition" AND table_schema = database()');
    if( $constraint_exists->num_rows() > 0 )
    {
      ee()->db->query('ALTER TABLE exp_blocks_atom DROP FOREIGN KEY fk_blocks_atom_atomdefinition;');
    }

    $constraint_exists = ee()->db->query('SELECT * FROM information_schema.table_constraints WHERE constraint_name = "fk_blocks_blockfieldusage_blockdefinition" AND table_schema = database()');
    if( $constraint_exists->num_rows() > 0 )
    {
      ee()->db->query('ALTER TABLE exp_blocks_blockfieldusage DROP FOREIGN KEY fk_blocks_blockfieldusage_blockdefinition;');
    }

    $constraint_exists = ee()->db->query('SELECT * FROM information_schema.columns WHERE table_schema = database() and table_name = "exp_blocks_atom" and column_name = "data" and is_nullable = "NO"');
    if( $constraint_exists->num_rows() > 0 )
    {
      ee()->db->query('ALTER TABLE exp_blocks_atom MODIFY data longtext;');
    }

  } //end _version_124() 


// ----------------------------------------------------------------  


  /**
   *
   * _version_110
   *
   * @description: version 1.1.0
   *
   * @param none
   *
   * @return void
   *
  **/
  public function _version_110() 
  {
    
    $content_type_exists = ee()->db->select('name')
                                   ->where('name', 'blocks')
                                   ->get('content_types');

    if( $content_type_exists->num_rows() <= 0 )
    {
      ee()->db->insert( 'content_types', array('name' => 'blocks') );
    }                 

  } //end _version_110()


// ----------------------------------------------------------------  

  /**
   *
   * _version_100
   *
   * @description: initial build
   *
   * @param none
   *
   * @return void
   *
  **/
  public function _version_100() 
  {

    $tables = array(
      'tbl_one' => array(
          'name' => 'exp_blocks_blockdefinition',
          'definition' => "CREATE TABLE `exp_blocks_blockdefinition` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `shortname` tinytext NOT NULL,
            `name` text NOT NULL,
            `instructions` text,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        ),

      'tbl_two' => array(
          'name' => 'exp_blocks_atomdefinition',
          'definition' => "CREATE TABLE `exp_blocks_atomdefinition` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `blockdefinition_id` bigint(20) NOT NULL,
            `shortname` tinytext NOT NULL,
            `name` text NOT NULL,
            `instructions` text NOT NULL,
            `order` int(11) NOT NULL,
            `type` varchar(50) DEFAULT NULL,
            `settings` text,
            PRIMARY KEY (`id`),
            KEY `fk_blocks_atomdefinition_blockdefinition` (`blockdefinition_id`),
            CONSTRAINT `fk_blocks_atomdefinition_blockdefinition` FOREIGN KEY (`blockdefinition_id`) REFERENCES `exp_blocks_blockdefinition` (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        ),

      'tbl_three' => array(
          'name' => 'exp_blocks_block',
          'definition' => "CREATE TABLE `exp_blocks_block` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `blockdefinition_id` bigint(20) NOT NULL,
            `site_id` int(11) NOT NULL,
            `entry_id` int(11) NOT NULL,
            `field_id` int(6) NOT NULL,
            `order` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `fk_blocks_blockdefinition_block` (`blockdefinition_id`),
            CONSTRAINT `fk_blocks_blockdefinition_block` FOREIGN KEY (`blockdefinition_id`) REFERENCES `exp_blocks_blockdefinition` (`id`),
            KEY `ix_blocks_block_siteid_entryid_fieldid` (`site_id`,`entry_id`,`field_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        ),

      'tbl_four' => array(
          'name' => 'exp_blocks_atom',
          'definition' => "CREATE TABLE `exp_blocks_atom` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `block_id` bigint(20) NOT NULL,
            `atomdefinition_id` bigint(20) NOT NULL,
            `data` longtext NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_blocks_atom_blockid_atomdefinitionid` (`block_id`,`atomdefinition_id`),
            KEY `fk_blocks_atom_block` (`atomdefinition_id`),
            CONSTRAINT `fk_blocks_atom_block` FOREIGN KEY (`block_id`) REFERENCES `exp_blocks_block` (`id`),
            CONSTRAINT `fk_blocks_atom_atomdefinition` FOREIGN KEY (`atomdefinition_id`) REFERENCES `exp_blocks_atomdefinition` (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        ),

      'tbl_five' => array(
          'name' => 'exp_blocks_blockfieldusage',
          'definition' => "CREATE TABLE `exp_blocks_blockfieldusage` (
            `id` int(20) NOT NULL AUTO_INCREMENT,
            `field_id` int(6) NOT NULL,
            `blockdefinition_id` bigint(20) NOT NULL,
            `order` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_blocks_blockfieldusage_fieldid_blockdefinitionid` (`field_id`,`blockdefinition_id`),
            CONSTRAINT `fk_blocks_blockfieldusage_blockdefinition` FOREIGN KEY (`blockdefinition_id`) REFERENCES `exp_blocks_blockdefinition` (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        ),
    );

    foreach( $tables as $table )
    {
      if( !ee()->db->table_exists($table['name']) )
      {
        ee()->db->query( $table['definition'] );
      }
    }


  } //end _version_100() 


// ----------------------------------------------------------------  
  




} //end class