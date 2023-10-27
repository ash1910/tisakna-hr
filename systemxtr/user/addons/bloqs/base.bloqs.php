<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 * Bloqs - Base Class
 *
 * @package   ExpressionEngine 3
 * @subpackage  Addons
 * @category  Module
 * @author    Clinton Reeves, Mike Wenger | Q Digital Studio
 * @link    http://www.qdigitalstudio.com/
 *
**/


class Bloqs_base
{

  //Addon specific
    protected $pkg = 'bloqs';
    protected $pkg_url;
    public $pkg_details;

    public $name;
    public $class_name;
    public $version;

  //Extension Settings
    public $settings;

  //Libraries
    public $pkg_libraries = array();

  //Models
    public $pkg_models = array();

  //Helpers
    public $pkg_helpers = array();


  /**
   * 
   * Constructor
   *
  **/
  public function __construct()
  {

    //initialize class variables
      $this->_initialize_class_vars();

    //set the pkg url for the addon
      $this->pkg_url = ee('CP/URL')->make('addons/settings/'.$this->pkg);

    //Load up the resources we need to work with
      ee()->load->model( $this->pkg_models );
      ee()->load->library( $this->pkg_libraries );
      ee()->load->helpers( $this->pkg_helpers );


  } // end __construct()


// --------------------------------------------------------------------


  /**
   *
   * _initialize_class_vars()
   *
   * @description - sets default values for class variables
   *
   * @return void 
   *
  **/
  public function _initialize_class_vars()
  {
    $this->pkg_details = ee('App')->get($this->pkg);

    $this->name = strtolower( $this->pkg_details->getName() );
    $this->version = $this->pkg_details->getVersion();
    $this->class_name = ucfirst( $this->name );

  } //end _initialize_class_vars()


// --------------------------------------------------------------------


  /**
   *
   * get_cp_url()
   *
   * @param action = controller action
   * @param params = query string parameters 
   *
   * @return void 
   *
  **/
  public function make_cp_url( $action = 'index', $params = array() )
  {
    return ee('CP/URL')->make('addons/settings/'.$this->pkg.'/'.$action, $params)->compile();

  } //end get_cp_url()


} //end class()

