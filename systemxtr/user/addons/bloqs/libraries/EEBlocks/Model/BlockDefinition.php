<?php

namespace EEBlocks\Model;

class BlockDefinition
{
	var $id;
	var $shortname;
	var $name;
	var $atoms;

	function __construct(
		$id = NULL,
		$shortname = NULL,
		$name = NULL,
		$instructions = NULL)
	{
		$this->id = $id;
		$this->shortname = $shortname;
		$this->name = $name;
		$this->instructions = $instructions;
		$this->atoms = array();
	}


//---------------------------------------------

  /**
   *
   * _has_unique_shortname
   * 
   * @description: method to determine whether or not the value of a shortname is unique 
   *
   *   This is used as a form_validation callback method
   *
   * @param value - string - input value
   * @param id - string - id of given BlockDefintion (null if no definition exists)
   * 
   * return bool
   *
  **/
  public function hasUniqueShortname($value, $id = null)
  {    
    ee()->db->select('shortname')
             ->where('shortname', $value);
    if( !empty($id) )
    {
      ee()->db->where('id !=', $id);
    }
    $block_def_results = ee()->db->get('blocks_blockdefinition');

    $chan_fields_results = ee()->db->select('field_name')
                                ->where('field_name', $value)
                                ->get('channel_fields');

    if( $block_def_results->num_rows() <= 0 && $chan_fields_results->num_rows <= 0)
    {
      return TRUE;
    }
    else
    {
      ee()->form_validation->set_message(__FUNCTION__, lang('bloqs_blockdefinition_alert_unique'));
      return FALSE;
    }

  } //end _has_unique_shortname()
 

//---------------------------------------------

}