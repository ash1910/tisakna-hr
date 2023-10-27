<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


if ( ! class_exists('Bloqs_base'))
{
  require_once(PATH_THIRD.'bloqs/base.bloqs.php');
}

 
// ------------------------------------------------------------------------


/**
 * Bloqs Front End Module File
 *
 * @package   ExpressionEngine
 * @subpackage  Addons
 * @category  Module
 * @author    Q Digital Studio
 * @link    http://qdigitalstudio.com
 */


class Bloqs extends bloqs_base {
  
  /**
   *
   * Constructor
   * 
  **/
  public function __construct()
  {
    //no front end module - nothing to do.
  }

  
// ----------------------------------------------------------------

  
}
/* End of file mod.bloqs.php */
/* Location: /system/users/addons/bloqs/mod.bloqs.php */