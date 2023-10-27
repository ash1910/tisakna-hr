<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


if ( ! class_exists('Bloqs_base'))
{
  require_once(PATH_THIRD.'bloqs/base.bloqs.php');
}


/**
 *
 * Bloqs Module Install/Update File
 *
 * @package   ExpressionEngine
 * @subpackage  Addons
 * @category  Module
 * @author    Q Digital Studio
 * @link    http://www.qdigitalstudio.com
 *
**/


class Bloqs_upd EXTENDS Bloqs_base {

  
  /**
   *
   * Constructor
   *
  **/
  public function __construct()
  {
    parent::__construct();

  } //end __construct()
  

// ----------------------------------------------------------------

  
  /**
   *
   * Installation Method
   *
   * @return  boolean   TRUE
   *
  **/
  public function install()
  {

    $ee2_blocks = ee('Model')->get('Module')
                  ->filter( 'module_name', '==', 'Blocks' )
                  ->first();

    $mod_data = array(
      'module_name'     => $this->class_name,
      'module_version'    => $this->version,
      'has_cp_backend'    => 'y',
      'has_publish_fields'  => 'n'
    );

    //
    // TODO - the addon name change we implemented (blocks to bloqs)
    //    has caused a bit of a bind during EE2 to EE3 updates
    //    we need to revist this issue, but we're going to implement
    //    a quick work around for now..
    //
    if( empty($ee2_blocks) )
    {
      ee()->db->insert('modules', $mod_data);
      $this->runDatabaseScripts();
    }
    else
    {
      ee()->db->update('modules', $mod_data, "module_name = 'Blocks'");
      $this->runDatabaseScripts();
      $this->runFieldtypeUpdate();
    }

    return TRUE;

  } //end install()


// ----------------------------------------------------------------
  
  /**
   * Uninstall
   *
   * @return  boolean   TRUE
   */ 
  public function uninstall()
  {
    ee()->load->dbforge();

    // remove row from exp_modules
      ee()->db->delete('modules', array('module_name' => $this->class_name));

    // remove bloq's specific tables
      ee()->db->query("DROP TABLE IF EXISTS exp_blocks_blockfieldusage");
      ee()->db->query("DROP TABLE IF EXISTS exp_blocks_atom");
      ee()->db->query("DROP TABLE IF EXISTS exp_blocks_block");
      ee()->db->query("DROP TABLE IF EXISTS exp_blocks_atomdefinition");
      ee()->db->query("DROP TABLE IF EXISTS exp_blocks_blockdefinition");

    // unregister the field type / content type
      ee()->db->query("DELETE FROM exp_content_types WHERE name = 'blocks'");
    
    return TRUE;

  }


// ----------------------------------------------------------------


  /**
   *
   * Module Updater
   *
   * @return  boolean   TRUE
   *
  **/ 
  public function update($current = '')
  {
    $this->runDatabaseScripts( $current );
    $this->runFieldtypeUpdate();

    return TRUE;

  } //end update()


// ----------------------------------------------------------------
//
// Database Scripts
//
// ----------------------------------------------------------------

  /**
   *
   * runDatabaseScripts()
   *
   * @description: Build table scripts
   *                (we're keep legacy/EE2 modifications in tact for easy upgrading to EE3)
   *
   * @return void
   *
  **/ 
  private function runDatabaseScripts($current = '')
  {
    //blocks autoloader
      require_once __DIR__ . '/libraries/autoload.php';

    //blocks builder instance
      $db_script = new EEBlocks\Database\Builder( $current );

    //array of versions
      $versions = array('1.0.0', '1.1.0', '1.2.4', '3.0.0');

    foreach( $versions as $version )
    {
      if( version_compare($current, $version, "<") )
      {
        $db_script->runVersionInstaller($version);
      }
    }

  } //end runDatabaseScripts()
  

// ----------------------------------------------------------------  


  /**
   *
   * runFieldtypeUpdate()
   *
   * @description: updates version for bloqs field type record in exp_fieldtypes table
   *
   * @return void
   *
  **/ 
  private function runFieldtypeUpdate()
  {
    ee()->db->update('fieldtypes', array('version' => $this->version), "name = '{$this->pkg}'");

  } //end runFieldtypeUpdate()
  

// ----------------------------------------------------------------  


} //end class()

/* End of file upd.bloqs.php */
/* Location: /system/users/addons/bloqs/upd.bloqs.php */