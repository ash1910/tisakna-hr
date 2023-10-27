<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
* Bloqs Module Control Panel File
*
* @package    ExpressionEngine 3
* @subpackage Addons
* @category Module
* @author   Q Digital Studio
* @link   http://www.qdigitalstudio.com
*/


// Includes
if ( ! class_exists('Bloqs_base'))
{
  require_once(PATH_THIRD.'bloqs/base.bloqs.php');
}
require_once __DIR__ . '/libraries/autoload.php';



class Bloqs_mcp EXTENDS Bloqs_base {

  public $vars;
  

// ----------------------------------------------------------------


  /**
   *
   * Constructor
   *
  **/
  public function __construct()
  {
    parent::__construct();

    //Initialize hook executor
      $this->_hookExecutor = new \EEBlocks\Controller\HookExecutor( ee() );

    //Initialize fieldtype filter
      $filter = new \EEBlocks\Controller\FieldTypeFilter();
      $filter->load(PATH_THIRD.'bloqs/fieldtypes.xml');

    //Initialize fieldtype manager
      $this->_ftManager = new \EEBlocks\Controller\FieldTypeManager(ee(), $filter, $this->_hookExecutor);


  } //end __construct()

  
// ----------------------------------------------------------------
//
// Views
//
// ----------------------------------------------------------------
  

  /**
   * index()
   *
   * Load the index view
   *
   * @return   void
   *
  **/
  public function index()
  {
    //Page Specific content/sidebar settings
      //title
      ee()->view->header = array( 'title' => 'Block Types' );

      //sidebar
      $sidebar = ee('CP/Sidebar')->make();
        $sidebar->addHeader( lang('bloqs_module_home'), $this->make_cp_url('index') );

    $adapter = new \EEBlocks\Database\Adapter( ee() );
    $blockDefinitions = $adapter->getBlockDefinitions();

    $vars['blockDefinitions'] = $blockDefinitions;
    $vars['blockdefinition_url'] = $this->make_cp_url('blockdefinition', array('blockdefinition' => 'new') );
    $vars['confirmdelete_url'] = $this->make_cp_url('confirmdelete', array('blockdefinition' => '') );

    //handle the delete bloq functionality in the Add-on Manager view.
    ee()->javascript->output('
     $("a.m-link").click(function (e) {
        var modalIs = $("." + $(this).attr("rel"));
        $(".checklist", modalIs)
          .html("") // Reset it
          .append("<li>" + $(this).data("confirm") + "</li>");
        $("input[name=\'blockdefinition\']", modalIs).val($(this).data("blockdefinition"));
        e.preventDefault();
      });
    ');

    return $this->render_view( 'cp-blockdefinitions', $vars );

  } //end index()
  

// ----------------------------------------------------------------
  

  /**
   * blockdefinition()
   *
   * Load the blockdefinition view
   *
   * @return   void
   *
  **/
  public function blockdefinition()
  {

    // Load native fields language files
      ee()->lang->loadfile('fieldtypes');
      ee()->lang->loadfile('admin_content');
      ee()->load->library('form_validation');

    $adapter = new \EEBlocks\Database\Adapter( ee() );

    $blockDefinitionId = ee()->input->get_post('blockdefinition');

    if ($blockDefinitionId == 'new') {
      $blockDefinitionId = null;
      $blockDefinition = new \EEBlocks\Model\BlockDefinition(
        null, // id
        '',   // shortname
        '',   // name
        '');  // instructions
    }
    else {
      $blockDefinitionId = intval($blockDefinitionId);
      $blockDefinition = $adapter->getBlockDefinitionById($blockDefinitionId);
    }

    //Build out the fields in the section array
      $sections[0][] = array(
        'title' => 'bloqs_blockdefinition_name',
        'desc'  => lang('bloqs_blockdefinition_name_info'),
        'fields' => array(
          'blockdefinition_name' => array(
            'required' => 1,
            'type' => 'text',
            'value' => $blockDefinition->name,
            //'note' => lang('bloqs_blockdefinition_name_note'),
          )
        )      
      );
      $sections[0][] = array(
        'title' => 'bloqs_blockdefinition_shortname',
        'desc'  => lang('bloqs_blockdefinition_shortname_info'),
        'fields' => array(
          'blockdefinition_shortname' => array(
            'required' => 1,
            'type' => 'text',
            'value' => $blockDefinition->shortname,
            //'note' => lang('bloqs_blockdefinition_shortname_note'),
          )
        )
      );
      $sections[0][] = array(
        'title' => 'bloqs_blockdefinition_instructions',
        'desc'  => lang('bloqs_blockdefinition_instructions_info'),
        'fields' => array(
          'blockdefinition_instructions' => array(
            'required' => 0,
            'type' => 'textarea',
            'value' => $blockDefinition->instructions,
            //'note' => lang('bloqs_blockdefinition_instructions_note'),
          )
        )
      );


    $errors = array();

    if ( $_SERVER['REQUEST_METHOD'] == 'POST' )
    {
      ee()->form_validation->setCallbackObject($blockDefinition);
      ee()->form_validation->set_rules('blockdefinition_name', 'Name', 'trim|required');
      ee()->form_validation->set_rules('blockdefinition_shortname', 'Short Name', 'trim|required|callback_hasUniqueShortname['.$blockDefinitionId.']');
      $is_valid = ee()->form_validation->run();

      if( $is_valid === FALSE )
      {
        $this->_add_alert( false, 'blocks_settings_alert', lang('bloqs_blockdefinition_alert_title'), lang('bloqs_blockdefinition_alert_message') );
      }
      else
      {
        $name = ee()->input->post('blockdefinition_name');
        $shortname = ee()->input->post('blockdefinition_shortname');
        $instructions = ee()->input->post('blockdefinition_instructions');

        $settings = ee()->input->post('grid');
        $errors = array_merge( $errors, $this->validateAtomSettings($settings) );

        if ( empty($errors) )
        {
          $blockDefinition->name = $name;
          $blockDefinition->shortname = $shortname;
          $blockDefinition->instructions = $instructions;

          if ($blockDefinitionId == null) {
            $adapter->createBlockDefinition($blockDefinition);
          }
          else {
            $adapter->updateBlockDefinition($blockDefinition);
          }

          $this->applyAtomSettings($blockDefinition, $settings, $adapter);

          ee()->functions->redirect( $this->pkg_url, false, 302 );
          return;
        }
      }
    }

    $atomDefinitionsView = $this->getAtomDefinitionsView($blockDefinition, $errors);

    //Page Specific
      //Resources
        ee()->cp->add_js_script('plugin', 'ee_url_title');
        ee()->cp->add_js_script('ui', 'sortable');
        ee()->cp->add_js_script('file', 'cp/sort_helper');
        ee()->cp->add_js_script('file', 'cp/grid');
        ee()->cp->add_js_script( array('file' => array('cp/confirm_remove')) );

      //Title
        $vars['cp_page_title'] = lang('bloqs_module_name');
        ee()->view->header = ($blockDefinition->name == '') ? array( 'title' => 'New Block' ) : array('title' => $blockDefinition->name);

      //Sidebar items
        $sidebar = ee('CP/Sidebar')->make()
                    ->addHeader( lang('bloqs_module_home'), $this->make_cp_url('index') );

      //Build out the fields...
        $vars['sections'] = $sections;
        $vars['base_url'] = $this->pkg_url;
        $vars['blockDefinition'] = $blockDefinition;
        $vars['hiddenValues'] = array('blockdefinition' => is_null($blockDefinitionId) ? 'new' : $blockDefinitionId);
        $vars['atomDefinitionsView'] = $atomDefinitionsView;
        $vars['post_url'] = $this->make_cp_url('blockdefinition', array('blockdefinition' => $blockDefinitionId));
        $vars['save_btn_text'] = 'save';
        $vars['save_btn_text_working'] = 'saving';
 
    ee()->javascript->output('EE.grid_settings();');

    // If this is a new block definition, turn on the EE feature where the
    // shortname gets autopopulated when the name gets entered.
    if ($blockDefinition->name == '')
    {
      ee()->javascript->output('
        $("input[name=blockdefinition_name]").bind("keyup keydown", function() {
          $(this).ee_url_title("input[name=blockdefinition_shortname]", true);
        });
      ');
    }

    return $this->render_view( 'cp-blockdefinition', $vars );


  } //end blockdefinition()


// ----------------------------------------------------------------  


  /**
   * getAtomDefinitionsView()
   *
   * Generate the cp-atomdefinitions view
   *
   * @return   string
   *
  **/
  private function getAtomDefinitionsView($blockDefinition, $atom_errors = array())
  {
    $vars = array();
    $vars['columns'] = array();

    foreach ($blockDefinition->atoms as $atomDefinition)
    {
      $field_errors = ( !empty($atom_errors['col_id_'.$atomDefinition->id]) ) ? $atom_errors['col_id_'.$atomDefinition->id] : array();
      $atomDefinitionView = $this->getAtomDefinitionView($atomDefinition, NULL, $field_errors);
      $vars['columns'][] = $atomDefinitionView;
    }

    // Fresh settings forms ready to be used for added columns
    $vars['settings_forms'] = array();
    foreach ( $this->_ftManager->getFieldTypes() as $fieldType )
    {
      $fieldName = $fieldType->type;
      $vars['settings_forms'][$fieldName] = $this->getAtomDefinitionSettingsForm(null, $fieldName);
    }

    // Will be our template for newly-created columns
    $vars['blank_col'] = $this->getAtomDefinitionView(null);


    if (empty($vars['columns']))
    {
      $vars['columns'][] = $vars['blank_col'];
    }

    return $this->render_view( 'cp-atomdefinitions', $vars );

  } //end getAtomDefinitionsView()


// ----------------------------------------------------------------


  /**
   * getAtomDefinitionView
   *
   * create the single view for each atom 'block'
   *
   * @param atomDefinition
   * @param column
   * @param field_errors 
   *
   * @return  string  Rendered column view for settings page
  **/
  public function getAtomDefinitionView($atomDefinition, $column = NULL, $field_errors = array())
  {
    $fieldtypes = $this->_ftManager->getFieldTypes();

    // Create a dropdown-frieldly array of available fieldtypes
    $fieldtypesLookup = array();
    foreach ($fieldtypes as $fieldType)
    {
      $fieldtypesLookup[$fieldType->type] = $fieldType->name;
    }

    $field_name = ( is_null($atomDefinition) ) ? 'new_0' : 'col_id_'.$atomDefinition->id;

    $settingsForm = ( is_null($atomDefinition) )
      ? $this->getAtomDefinitionSettingsForm(null, 'text')
      : $this->getAtomDefinitionSettingsForm($atomDefinition, $atomDefinition->type, $column);

    $vars = array(
        'atomDefinition'  => $atomDefinition,
        'field_name'      => $field_name,
        'settingsForm'    => $settingsForm,
        'fieldtypes'      => $fieldtypesLookup,
        'field_errors'    => $field_errors
      );
    $ret = $this->render_view('cp-atomdefinition', $vars);

    return $ret['body'];

  } //end getAtomDefinitionView()


// ----------------------------------------------------------------


  /**
   * Returns rendered HTML for the custom settings form of a grid column type
   *
   * @param string  Name of fieldtype to get settings form for
   * @param array Column data from database to populate settings form
   * @return  array Rendered HTML settings form for given fieldtype and
   *          column data
   */
  public function getAtomDefinitionSettingsForm($atomDefinition, $type)
  {
    $ft_api = ee()->api_channel_fields;
    $settings = NULL;

    // Returns blank settings form for a specific fieldtype
    if ( is_null($atomDefinition) )
    {
      $ft = $ft_api->setup_handler( $type, true );

      $_default_grid_ct = array( 'text', 'textarea', 'rte' );
      $ft->_init( array('content_type' => 'grid') );

      if( $ft_api->check_method_exists('grid_display_settings') )
      {
        if( $ft->accepts_content_type('blocks/1') )
        {
          $ft->_init( array('content_type' => 'blocks/1') );
        }
        elseif( $ft->accepts_content_type('grid') )
        {
          $ft->_init( array('content_type' => 'grid') );
        }
        $settings = $ft_api->apply( 'grid_display_settings', array(array()) );

      }
      elseif( $ft_api->check_method_exists('display_settings') )
      {
        if( $ft->accepts_content_type('grid') )
        {
          $ft->_init( array('content_type' => 'grid') );
        }
        $settings = $ft_api->apply( 'display_settings', array(array()) );
      }
      return $this->_view_for_col_settings($atomDefinition, $type, $settings);
    }

    $fieldtype = $this->_ftManager->instantiateFieldtype(
      $atomDefinition,
      null,
      null,
      0, // Field ID? At this point, we don't have one.
      0);

    $settings = $fieldtype->displaySettings($atomDefinition->settings);
  
    // Otherwise, return the prepopulated settings form based on column settings
    return $this->_view_for_col_settings($atomDefinition, $type, $settings);

  } //getAtomDefinitionSettingsForm()


// ----------------------------------------------------------------


  /**
   * confirmdelete()
   *
   * @description: delete a block
   *
   * @return   void
   *
  **/
  public function confirmdelete()
  {
    $adapter = new \EEBlocks\Database\Adapter( ee() );
    $blockDefinitionId = ee()->input->get_post('blockdefinition');
    $blockDefinitionId = intval($blockDefinitionId);
    $blockDefinition = $adapter->getBlockDefinitionById($blockDefinitionId);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !is_null($blockDefinition)) {
      $adapter->deleteBlockDefinition($blockDefinitionId);
      ee()->functions->redirect($this->pkg_url, false, 302);
    }
    return;

  } //end confirmdelete()


// ----------------------------------------------------------------
//
// Helper functions
//
// ----------------------------------------------------------------


  /**
   *
   * render_view
   *
   * @description - generates the mcp view for the controller action
   *
   * @param name - string - name of view file
   * @param vars - array
   * 
   * @return void
   *
  **/
  public function render_view( $name, $vars )
  {

    $toolbar = array(
      'toolbar_items' => array()
    );

    //and build the view
      $view = array(
        //'breadcrumb' => array( '#' => 'Test'),
        'breadcrumb' => array(),
        'body' => ee('View')->make($this->pkg.':'.$name)->render($vars)
      );

    //return it
      return $view; 

  } //end render_view()


// ----------------------------------------------------------------


  /**
   * Returns rendered HTML for the custom settings form of a grid column type,
   * helper method for Grid_lib::get_settings_form
   *
   * @param string  Name of fieldtype to get settings form for
   * @param array Column data from database to populate settings form
   * @param int   Column ID for field naming
   * @return  array Rendered HTML settings form for given fieldtype and
   *          column data
   */
  protected function _view_for_col_settings($atomDefinition, $type, $settings)
  {
    $settings_view = $this->render_view(
        'cp-atomdefinitionsettings',
        array(
          'atomDefinition' => $atomDefinition,
          'col_type'       => $type,
          'col_settings'   => (empty($settings)) ? array() : $settings
        )
    );

    $col_id = (is_null($atomDefinition)) ? 'new_0' : 'col_id_'.$atomDefinition->id;

    // Namespace form field names
    return $this->_namespace_inputs(
      $settings_view['body'],
      '$1name="grid[cols]['.$col_id.'][col_settings][$2]$3"'
    );
    
  } //end _view_for_col_settings()


// ----------------------------------------------------------------


  /**
   * Performes find and replace for input names in order to namespace them
   * for a POST array
   *
   * @param string  String to search
   * @param string  String to use for replacement
   * @return  string  String with namespaced inputs
   */
  protected function _namespace_inputs($search, $replace)
  {
    return preg_replace(
      '/(<[input|select|textarea][^>]*)name=["\']([^"\'\[\]]+)([^"\']*)["\']/',
      $replace,
      $search
    );

  } //end _namespace_inputs()


// ----------------------------------------------------------------


  protected function prepareErrors($validate)
  {
    $errors = array();
    $field_names = array();

    // Gather error messages and fields with errors so that we can
    // display the error messages and highlight the fields that
    // have errors

    foreach ($validate as $column => $fields)
    {
      foreach ($fields as $field => $error)
      {
        $errors[] = $error;
        $field_names[] = 'grid[cols]['.$column.']['.$field.']';
      }
    }

    // Make error messages unique and convert to a string to pass
    // to form validaiton library
    $errors = array_unique($errors);
    $error_string = '';
    foreach ($errors as $error)
    {
      $error_string .= lang($error).'<br>';
    }

    return array(
      'field_names' => $field_names,
      'error_string' => $error_string
    );

  } //end prepareErrors()


// ----------------------------------------------------------------



  private function validateAtomSettings($settings)
  {
    $errors = array();
    $col_names = array();

    // Create an array of column names for counting to see if there are
    // duplicate column names; they should be unique
    foreach ($settings['cols'] as $col_field => $column)
    {
      $col_names[] = $column['col_name'];
    }

    $col_name_count = array_count_values($col_names);

    foreach ($settings['cols'] as $col_field => $column)
    {
      // Column labels are required
      if (empty($column['col_label']))
      {
        $errors[$col_field]['col_label'] = 'grid_col_label_required';
      }

      // Column names are required
      if (empty($column['col_name']))
      {
        $errors[$col_field]['col_name'] = 'grid_col_name_required';
      }
      // Columns cannot be the same name as our protected modifiers
      /*
      elseif (in_array($column['col_name'], ee()->grid_parser->reserved_names))
      {
        $errors[$col_field]['col_name'] = 'grid_col_name_reserved';
      }
      */
      // There cannot be duplicate column names
      elseif ($col_name_count[$column['col_name']] > 1)
      {
        $errors[$col_field]['col_name'] = 'grid_duplicate_col_name';
      }

      // Column names must contain only alpha-numeric characters and no spaces
      if (preg_match('/[^a-z0-9\-\_]/i', $column['col_name']))
      {
        $errors[$col_field]['col_name'] = 'grid_invalid_column_name';
      }

      $column['col_id'] = (strpos($col_field, 'new_') === FALSE)
        ? str_replace('col_id_', '', $col_field) : FALSE;
      $column['col_required'] = isset($column['col_required']) ? 'y' : 'n';
      $column['col_settings']['col_required'] = $column['col_required'];

      $atomDefinition = new \EEBlocks\Model\AtomDefinition(
        intval($column['col_id']),
        $column['col_name'],
        $column['col_label'],
        $column['col_instructions'],
        1, // order
        $column['col_type'],
        $column['col_settings']);

      $fieldtype = $this->_ftManager->instantiateFieldtype($atomDefinition, null, null, 0, 0);

      // Let fieldtypes validate their Grid column settings; we'll
      // specifically call grid_validate_settings() because validate_settings
      // works differently and we don't want to call that on accident
      $ft_validate = $fieldtype->validateSettings($column['col_settings']);

      if (is_string($ft_validate))
      {
        $errors[$col_field]['custom'] = $ft_validate;
      }
    }

    if( !empty($errors) )
    {
      $this->_add_alert( false, 'blocks_block_alert', lang('bloqs_blockdefinition_atomdefinition_alert_title'), lang('bloqs_blockdefinition_atomdefinition_alert_message') );
    }

    return $errors;

  } //end validateAtomSettings()


// ----------------------------------------------------------------


  private function applyAtomSettings($blockDefinition, $settings, $adapter)
  {
    //$new_field = ee()->grid_model->create_field($settings['field_id'], $this->content_type);

    // Keep track of column IDs that exist so we can compare it against
    // other columns in the DB to see which we should delete
    $col_ids = array();

    // Determine the order of each atom definition.
    $order = 0;

    // Go through ALL posted columns for this field
    foreach ($settings['cols'] as $col_field => $column)
    {
      $order++;
      // Attempt to get the column ID; if the field name contains 'new_',
      // it's a new field, otherwise extract column ID
      $column['col_id'] = (strpos($col_field, 'new_') === FALSE)
        ? str_replace('col_id_', '', $col_field) : FALSE;

      $id = $column['col_id'] ? intval($column['col_id']) : null;

      $column['col_required'] = isset($column['col_required']) ? 'y' : 'n';
      $column['col_settings']['col_required'] = $column['col_required'];

      // We could find the correct atom definition in the block
      // definition, but we'd end up overwriting all of it's properties
      // anyway, so we may as well make a new model object that
      // represents the same atom definition.
      $atomDefinition = new \EEBlocks\Model\AtomDefinition(
        $id,
        $column['col_name'],
        $column['col_label'],
        $column['col_instructions'],
        $order,
        $column['col_type'],
        $column['col_settings']);

      $atomDefinition->settings = $this->_save_settings($atomDefinition);
      $atomDefinition->settings['col_required'] = $column['col_required'];
      $atomDefinition->settings['col_search'] = isset($column['col_search']) ? $column['col_search'] : 'n';

      if (is_null($atomDefinition->id))
      {
        $adapter->createAtomDefinition($blockDefinition->id, $atomDefinition);
      }
      else
      {
        $adapter->updateAtomDefinition($atomDefinition);
      }

      $col_ids[] = $atomDefinition->id;
    }

    // Delete existing atoms that were not included.
    foreach ($blockDefinition->atoms as $atomDefinition)
    {
      if (!in_array($atomDefinition->id, $col_ids)) {
        $adapter->deleteAtomDefinition($atomDefinition->id);
      }
    }

  } //end applyAtomSettings()


// ----------------------------------------------------------------


  protected function _save_settings($atomDefinition)
  {
    if (!isset($atomDefinition->settings))
    {
      $atomDefinition->settings = array();
    }

    $fieldtype = $this->_ftManager->instantiateFieldtype(
      $atomDefinition,
      null,
      null,
      0,
      0);

    if ( ! ($settings = $fieldtype->saveSettings($atomDefinition->settings)))
    {
      return $atomDefinition->settings;
    }

    return $settings;

  } //end _save_settings()


// ----------------------------------------------------------------


  /**
   * _add_alert()
   *
   * handles any sort of response actions required by the module
   *
   * @param type - bool - false = issue/error || true = success
   * @param source - string - where response is coming from - used to generate response message from lang
   * @param redirect_success - string - url to redirect to on success
   * @param redirect_fail - string - url to redirect to on failure
   *
   *
   * @return  void
   *
  **/
  private function _add_alert( $type, $name, $title, $msg )
  {
    // prep up a response message for the user
    if( $type === true )
    {
      ee('CP/Alert')->makeInline( $name )
        ->asSuccess()
        ->withTitle( $title )
        ->addToBody( $msg )
        ->defer();
    }
    else
    {
      ee('CP/Alert')->makeInline( $name )
        ->asIssue()
        ->withTitle( $title )
        ->addToBody( $msg )
        ->now();
    }

  } //end _do_response()


// ----------------------------------------------------------------


} //end class


/* End of file mcp.bloqs.php */
/* Location: /system/user/addons/bloqs/mcp.bloqs.php */