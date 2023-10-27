<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author		Rein de Vries <support@reinos.nl>
 * @link		http://ee.reinos.nl
 * @copyright 	Copyright (c) 2017 Reinos.nl Internet Media
 * @license     http://ee.reinos.nl/commercial-license
 *
 * Copyright (c) 2017. Reinos.nl Internet Media
 * All rights reserved.
 *
 * This source is commercial software. Use of this software requires a
 * site license for each domain it is used on. Use of this software or any
 * of its source code without express written permission in the form of
 * a purchased commercial or other license is prohibited.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE.
 *
 * As part of the license agreement for this software, all modifications
 * to this source must be submitted to the original author for review and
 * possible inclusion in future releases. No compensation will be provided
 * for patches, although where possible we will attribute each contribution
 * in file revision notes. Submitting such modifications constitutes
 * assignment of copyright to the original author (Rein de Vries and
 * Reinos.nl Internet Media) for such modifications. If you do not wish to assign
 * copyright to the original author, your license to  use and modify this
 * source is null and void. Use of this software constitutes your agreement
 * to this clause.
 */

require_once(PATH_THIRD.'gmaps/libraries/api/gmaps_api_base.php');

class Gmaps_fusion_table extends Gmaps_api_base
{
    public $tag = 'gmaps:add_fusion_table';
    public $tagpair = false;
    public $early_parse = false;

    public function __construct()
    {
        parent::__construct();
    }

    // ----------------------------------------------------------------------------------

    /**
     * fetch the data
     *
     * @param int $map_id
     * @return array
     */
    public function fetch()
    {
        return $this->_fetch();
    }

    // ----------------------------------------------------------------------------------

    /**
     * fetch the marker from a marker tag pair {add_marker}
     *
     * @param $map_id
     * @param array $data
     * @param string $inner_tagdata
     * @return string
     */
    public function build($map_id = 0, $data = array(), $inner_tagdata = '')
    {
        //get the data
        $table_id = gmaps_helper::array_value($data, 'id');
        $styles = gmaps_helper::array_value($data, 'styles');
        $suppress_info_windows = gmaps_helper::check_yes(gmaps_helper::array_value($data, 'suppress_info_windows', 'no'), true);
        $clickable = gmaps_helper::check_yes(gmaps_helper::array_value($data, 'clickable', 'yes'), true);
        $heatmap = gmaps_helper::check_yes(gmaps_helper::array_value($data, 'heatmap', 'no'), true);

        //loop over the values
        if($table_id == '')
        {
            gmaps_helper::log('No table_id is given for the fusion table');
            return;
        }
        //set the correct values
        $_fields = $this->preg_array_key_exists('/fields:/', $data);
        $fields = array();
        $field_options = array();

        if(!empty($_fields))
        {
            // Create a tmp array which can be treated for the js converting
            foreach($_fields as $val)
            {
                $_val = explode(':', $val);

                //explode on | by multiple values
                $param = explode('|', gmaps_helper::array_value($data, $val, ''));

                //multiple values?
                if(count($param) > 1)
                {
                    $tmp_val = array();
                    foreach($param as $v)
                    {
                        $tmp_val[] = $v;
                    }

                    $fields[$_val[1]][$_val[2]] = $tmp_val;

                }

                //single value
                else
                {
                    $fields[$_val[1]][$_val[2]] = $param[0];
                }
            }

            //create a good array for converting to JS
            foreach($fields as $key=>$val)
            {
                $tmp = array();

                //set default
                $fields['default']['fill_color'] = isset($fields['default']['fill_color']) ? $fields['default']['fill_color'] : '' ;
                $fields['default']['fill_opacity'] = isset($fields['default']['fill_opacity']) ? $fields['default']['fill_opacity'] : '' ;
                $fields['default']['stroke_color'] = isset($fields['default']['stroke_color']) ? $fields['default']['stroke_color'] : '' ;
                $fields['default']['stroke_weight'] = isset($fields['default']['stroke_weight']) ? $fields['default']['stroke_weight'] : '' ;

                if($key == 'default')
                {

                    $tmp['fillColor'] =  isset($val['fill_color']) ? $val['fill_color'] : '' ;
                    $tmp['fillOpacity'] =  isset($val['fill_opacity']) ? $val['fill_opacity'] : '' ;
                    $tmp['strokeWeight'] =  isset($val['stroke_weight']) ? $val['stroke_weight'] : '' ;
                    $tmp['strokeColor'] =  isset($val['stroke_color']) ? $val['stroke_color'] : '' ;

                    $field_options[] = array(
                        'polygonOptions' => $tmp
                    );
                }
                else
                {
                    //check for multiple values
                    if(isset($val['where']) && count($val['where']) > 1)
                    {
                        foreach($val['where'] as $k=>$v)
                        {
                            //fill_color
                            if(isset($val['fill_color'][$k]) && is_array($val['fill_color']))
                            {
                                $tmp['fillColor'] = $val['fill_color'][$k];
                            }
                            else
                            {
                                if(!isset($val['fill_color']) || !isset($val['fill_color'][$k]))
                                {
                                    $tmp['fillColor'] = isset($fields['default']['fill_color']) ? $fields['default']['fill_color'] : '';
                                }
                                else if(!is_array($val['fill_color']))
                                {
                                    $tmp['fillColor'] = $val['fill_color'];
                                }
                            }

                            //fill_opacity
                            if(isset($val['fill_opacity'][$k]) && is_array($val['fill_opacity']))
                            {
                                $tmp['fillOpacity'] = $val['fill_opacity'][$k];
                            }
                            else
                            {
                                if(!isset($val['fill_opacity']) || !isset($val['fill_opacity'][$k]))
                                {
                                    $tmp['fillColor'] = isset($fields['default']['fill_opacity']) ? $fields['default']['fill_opacity'] : '';
                                }
                                else if(!is_array($val['fill_opacity']))
                                {
                                    $tmp['fillOpacity'] = $val['fill_opacity'];
                                }
                            }

                            //stroke_color
                            if(isset($val['stroke_color'][$k]) && is_array($val['stroke_color']))
                            {
                                $tmp['strokeColor'] = $val['stroke_color'][$k];
                            }
                            else
                            {
                                if(!isset($val['stroke_color']) || !isset($val['stroke_color'][$k]))
                                {
                                    $tmp['strokeColor'] = isset($fields['default']['stroke_color']) ? $fields['default']['stroke_color'] : '';
                                }
                                else if(!is_array($val['stroke_color']))
                                {
                                    $tmp['strokeColor'] = $val['stroke_color'];
                                }
                            }

                            //stroke_weight
                            if(isset($val['stroke_weight'][$k]) && is_array($val['stroke_weight']))
                            {
                                $tmp['strokeWeight'] = $val['stroke_weight'][$k];
                            }
                            else
                            {
                                if(!isset($val['stroke_weight']) || !isset($val['stroke_weight'][$k]))
                                {
                                    $tmp['strokeWeight'] = isset($fields['default']['stroke_weight']) ? $fields['default']['stroke_weight'] : '';
                                }
                                else if(!is_array($val['stroke_weight']))
                                {
                                    $tmp['strokeWeight'] = $val['stroke_weight'];
                                }
                            }

                            //get the operator (=,>= etc..)
                            $operators = array('>', '<', '>=', '<=', '=');
                            $operator_selected = '';

                            //get the active operator
                            foreach($operators as $op)
                            {
                                $founded = stripos($v, $op);
                                if($founded !== false)
                                {
                                    $v = str_replace($op, '', $v);
                                    $operator_selected = $op;
                                }
                            }

                            //push the values
                            $field_options[] = array(
                                'where' =>  "'".$key."' ".$operator_selected." '".$v."'",
                                'polygonOptions' => $tmp
                            );
                        }
                    }

                    //single values
                    else
                    {
                        $where =  isset($val['where']) ? $key.''.$val['where'] : '' ;
                        if($where != '')
                        {
                            $tmp['fillColor'] =  isset($val['fill_color']) ? $val['fill_color'] : $fields['default']['fill_color'] ;
                            $tmp['fillOpacity'] =  isset($val['fill_opacity']) ? $val['fill_opacity'] : $fields['default']['fill_opacity'] ;
                            $tmp['strokeColor'] =  isset($val['stroke_color']) ? $val['stroke_color'] : $fields['default']['stroke_color'] ;
                            $tmp['strokeWeight'] =  isset($val['stroke_weight']) ? $val['stroke_weight'] : $fields['default']['stroke_weight'] ;

                            //get the operator (=,>= etc..)
                            $operators = array('>', '<', '>=', '<=', '=');
                            $operator_selected = '';

                            //get the active operator
                            foreach($operators as $op)
                            {
                                $founded = stripos($where, $op);

                                if($founded !== false)
                                {
                                    $where_ = explode($op, $where);
                                    $operator_selected = $op;
                                }
                            }

                            //push the values
                            $field_options[] = array(
                                'where' => "'".$where_[0]."' ".$operator_selected." '".$where_[1]."'",
                                'polygonOptions' => $tmp
                            );
                        }
                    }
                }
            }
        }

        //set the js
        $js = '
            EE_GMAPS.api("loadFusionTable", {
              mapID : "ee_gmap_'.$map_id.'",
              tableID : "'.$table_id.'",
              styles : '.$this->convert_array_to_js($field_options).',
              suppressInfoWindows : '.$suppress_info_windows.',
              clickable : '.$clickable.',
              heatmap: '.$heatmap.'
            });
		';

        return $this->script($js);
    }

    // ----------------------------------------------------------------------

} // END CLASS
