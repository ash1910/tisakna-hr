<?php namespace Zenbu\librairies;

use Zenbu\librairies\platform\ee\Cache;
use Zenbu\librairies\platform\ee\Lang;
use Zenbu\librairies\platform\ee\Request;
use Zenbu\librairies\platform\ee\Session;
use Zenbu\librairies\platform\ee\Convert;
use Zenbu\librairies\platform\ee\Db;
use Zenbu\librairies\platform\ee\FieldsBase as FieldsBase;
use Zenbu\librairies\platform\ee\Base as Base;
use Zenbu\librairies;

class Fields extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getDefaultFields()
    {
        $fieldBase = new FieldsBase();
        return $fieldBase->std_fields;
    }

    // public function getAllFieldsData()
    // {
    //     if(Cache::get('get_fields_User_' . Session::user()->id) !== FALSE)
    //     {
    //         $output = Cache::get('get_fields_User_' . Session::user()->id);
    //     }
    //     else
    //     {
    //         // Create FieldsBase object, set its variables ]
    //         // and get the field data
    //         $fieldBase = new FieldsBase();
    //         // Section data passed down from instantiated Fields object ($this)
    //         $fieldBase->setSections($this->getSections());
    //         $fieldBase->setAllFields(null);
    //         $output = $fieldBase->getAllFields();

    //         Cache::set('get_fields_User_' . Session::user()->id, $output, 10);
    //     }
    // }

    public function getFields($sectionId = FALSE, $subSectionId = FALSE)
    {
        $sectionId    = $sectionId === FALSE ? Request::param(Convert::string('sectionId')) : $sectionId;
        $subSectionId = $subSectionId === FALSE ? Request::param(Convert::col('subSectionId')) : $subSectionId;

        if(Cache::get('get_fields') !== FALSE)
        {
            $output = Cache::get('get_fields');
        }
        else
        {
            // If the class already has $this->allFields set,
            // simply fetch this data, if not query FieldsBase
            if(isset($this->allFields))
            {
                $output = $this->allFields;
            }
            else
            {
                // Create FieldsBase object, set its variables ]
                // and get the field data
                $fieldBase = new FieldsBase();
                // Section data passed down from instantiated Fields object ($this)
                $fieldBase->setSections($this->getSections());
                $fieldBase->setAllFields(null);
                $output = $fieldBase->getAllFields();
            }

            Cache::set('get_fields', $output, 10);
        }

        if(isset($output[Convert::string('sectionId').'_'.$sectionId]))
        {
            if($subSectionId)
            {
                foreach($output[Convert::string('sectionId').'_'.$sectionId] as $etId => $array)
                {
                    $subSectionId = str_replace(Convert::col('subSectionId').'_', '', $etId);
                }
            }
            else
            {
                $subSectionId = 0;
            }

            return $output[Convert::string('sectionId').'_'.$sectionId][Convert::col('subSectionId').'_'.$subSectionId];
        }
        else
        {
            return $this->getDefaultFields();
        }

    } // END getFields

    // --------------------------------------------------------------------


    public function getOrderedFields($include_nonvisible = FALSE)
    {
        // $settings = new Settings();
        $all = $include_nonvisible !== FALSE ? 'all' : '';
        $settings = $this->getDisplaySettings();
        $fields   = $this->getAllFields();

        $vars = array();

        foreach($settings['fields'] as $key => $setting)
        {
            $fieldkey = $setting[Convert::col('fieldType')] == 'field' ? $setting[Convert::col('fieldId')] : $setting[Convert::col('fieldType')];
            if(isset($fields[$fieldkey]))
            {
                $vars[$fieldkey] = $fields[$fieldkey];
            }
        }

        if($include_nonvisible !== FALSE)
        {
            foreach($fields as $fieldkey => $field_data)
            {
                if( ! isset($vars[$fieldkey]) )
                {
                    $vars[$fieldkey] = $field_data;
                }
            }
        }

        return $vars;
    } // END getOrderedFields

    // --------------------------------------------------------------------

    public function getFieldsSecondFilterType()
    {
        $fieldBase = new FieldsBase();
        $fieldBase->setSections($this->getSections());
        $fieldBase->setAllFields(null);
        return $fieldBase->getFieldsSecondFilterType();
    }

    public function loadFieldtypeClass($fieldtype)
    {
        $fieldBase = new FieldsBase();
        $fieldBase->setSections($this->getSections());
        $fieldBase->setAllFields(null);
        return $fieldBase->loadFieldtypeClass($fieldtype);

    }
}