<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


require_once __DIR__ . '/libraries/autoload.php';


class Bloqs_ft extends EE_Fieldtype
{

	public $pkg = 'bloqs';

	var $info = array(
		'name' => 'Bloqs',
		'version' => BLOQS_VERSION
	);

	var $has_array_data = true;
	var $cache = null;

	private $error_fields = array();
	private $error_string = '';
	private $_hookExecutor;
	private $_ftManager;


// ----------------------------------------------------------------


	function __construct()
	{
		//Initialize hook executor
			$this->_hookExecutor = new \EEBlocks\Controller\HookExecutor( ee() );

		//Initialize fieldtype filter
			$filter = new \EEBlocks\Controller\FieldTypeFilter();
			$filter->load(PATH_THIRD.'bloqs/fieldtypes.xml');

		//Initialize fieltype manager
			$this->_ftManager = new \EEBlocks\Controller\FieldTypeManager( ee(), $filter, $this->_hookExecutor );

		//Setup session cache
			if ( !isset(ee()->session->cache[__CLASS__]) )
			{
				ee()->session->cache[__CLASS__] = array();
			}
			$this->cache =& ee()->session->cache[__CLASS__];

			if ( !isset($this->cache['includes']) ) {
				$this->cache['includes'] = array();
			}
			if ( !isset($this->cache['validation']) ) {
				$this->cache['validation'] = array();
			}

	} //end __construct()


// ----------------------------------------------------------------


	protected function includeThemeJS($file)
	{
		if (! in_array($file, $this->cache['includes']))
		{
			$this->cache['includes'][] = $file;
			ee()->cp->add_to_foot('<script type="text/javascript" src="'.$this->getThemeURL().$file.'?version='.BLOQS_VERSION.'"></script>');
		}

	} //end includeThemeJS()


// ----------------------------------------------------------------


	protected function includeThemeCSS($file)
	{
		if (! in_array($file, $this->cache['includes']))
		{
			$this->cache['includes'][] = $file;
			ee()->cp->add_to_head('<link rel="stylesheet" href="'.$this->getThemeURL().$file.'?version='.BLOQS_VERSION.'">');
		}

	} //end includeThemeCSS()


// ----------------------------------------------------------------


	protected function getThemeURL()
	{
		if (! isset($this->cache['theme_url']))
		{
			$theme_folder_url = defined('URL_THIRD_THEMES') ? URL_THIRD_THEMES : ee()->config->slash_item('theme_folder_url').'third_party/';
			$this->cache['theme_url'] = $theme_folder_url.'bloqs/';
		}
		return $this->cache['theme_url'];

	} //getThemeURL()


// ----------------------------------------------------------------


	protected function includeGridAssets()
	{
		if ( ! ee()->session->cache(__CLASS__, 'grid_assets_loaded'))
		{
			ee()->cp->add_js_script('ui', 'sortable');
			ee()->cp->add_js_script('file', 'cp/sort_helper');
			ee()->cp->add_js_script('file', 'cp/grid');

			ee()->session->set_cache(__CLASS__, 'grid_assets_loaded', TRUE);
		}

	} //end includeGridAssets()


// ----------------------------------------------------------------


	public function save_settings($data)
	{
		$strip = array('field_name', 'field_id', 'field_required');

		//there are a few fields that are passed in with the data array
		//that we don't want to save - so we strip those out of the data array
			foreach( $strip as $field )
			{
				if( isset($data[$field]) )
				{
					unset($data[$field]);
				}
			}
			return array_merge($data, array('field_wide' => true));

	} //end save_settings()


// ----------------------------------------------------------------


	public function display_field($data)
	{
		$this->includeGridAssets();
		$this->includeThemeCSS('css/cp.css');
		$this->includeThemeJS('javascript/html.sortable.js');
		$this->includeThemeJS('javascript/cp.js');

		$adapter = new \EEBlocks\Database\Adapter( ee() );
		$entry_id = isset($this->content_id) ? $this->content_id : '';
		$site_id = isset( $this->settings['site_id'] ) ? $this->settings['site_id'] : ee()->input->get_post('site_id');
		$blockDefinitions = $adapter->getBlockDefinitionsForField($this->field_id);

		$controller = new \EEBlocks\Controller\PublishController(
			ee(),
			$this->id(),
			$this->name(),
			$adapter,
			$this->_ftManager,
			$this->_hookExecutor
		);

		if ( !is_array($data) )
		{
			//Let's build these blocks out
			$blocks = $adapter->getBlocks($site_id, $entry_id, $this->field_id);

			$viewdata = $controller->displayField(
				$entry_id,
				$blockDefinitions,
				$blocks);
		}
		else
		{
			// Validation failed. Either our validation or another validation,
			// we don't know, but now we need to output the data that was
			// entered instead of getting it from the database.
			if (isset($this->cache['validation'][$this->id()]))
			{
				$data = $this->cache['validation'][$this->id()]['value'];
			}

			$viewdata = $controller->displayValidatedField(
				$entry_id,
				$blockDefinitions,
				$data);
		}

		// TODO
		// A temporary patch for EE3 - need implement official patch
		// coming soon
		if( isset($viewdata['blocks']) )
		{
			$viewdata['bloqs'] = $viewdata['blocks'];
			unset($viewdata['blocks']);
		}

		if( AJAX_REQUEST )
		{
			foreach( $viewdata['bloqs'] as $i => $b )
			{
				$viewdata['bloqs'][$i]['visibility'] = 'expanded';
			}
		}


		return ee('View')->make($this->pkg.':editor')->render( $viewdata );

	} //end display_field()


// ----------------------------------------------------------------


	public function validate($data)
	{
		$field_id = $this->id();
		if (isset($this->cache['validation'][$field_id]))
		{
			return $this->cache['validation'][$field_id];
		}

		ee()->lang->loadfile('bloqs');

		$adapter = new \EEBlocks\Database\Adapter(ee());
		$entry_id = isset($this->settings['entry_id']) ? $this->settings['entry_id'] : ee()->input->get_post('entry_id');
		$site_id = isset( $this->settings['site_id'] ) ? $this->settings['site_id'] : ee()->input->get_post('site_id');

		$controller = new \EEBlocks\Controller\PublishController(
			ee(),
			$this->id(),
			$this->name(),
			$adapter,
			$this->_ftManager,
			$this->_hookExecutor);
		$validated = $controller->validate(
			$data,
			$site_id,
			$entry_id);

		$this->cache['validation'][$field_id] = $validated;

		return $validated;

	} //end validate()


// ----------------------------------------------------------------


	public function validate_settings($data)
	{
		$validator = ee('Validation')->make(array(
			'field_name' => 'uniqueBlockShortname',
		));
		$validator->defineRule('uniqueBlockShortname', array($this, '_validate_shortname'));

		return $validator->validate($data);

	} //end validate_settings()


// ----------------------------------------------------------------


	public function _validate_shortname($field_name, $field_value, $params, $rule)
	{
		ee()->lang->loadfile('bloqs');
		
		$this->error_fields = array();
		$this->error_string = '';
    $blockDefinition = new \EEBlocks\Model\BlockDefinition(
      null, // id
      '',   // shortname
      '',   // name
      '');  // instructions
		$ajax_field = ee()->input->post('ee_fv_field');

    //check the shortname of the field type against existing blocks to ensure 
    //we don't have duplicates.
    $adapter = new \EEBlocks\Database\Adapter( ee() );
		$isUnique = $adapter->getBlockDefinitionByShortname($field_value);

		if ( !empty($isUnique) )
		{
			//if we needed to do so, we could test to see if this was an AJAX_REQUEST and modify
			//our return data accordingly.  But for right now, we have no need to because our return data
			//is the same. 
			$this->error_fields[] = $field_name;	
			$this->error_string = lang('bloqs_field_shortname_not_unique');
			$rule->stop();
			return $this->error_string;
		}
		return TRUE;

	} //end _validate_shortname


// ----------------------------------------------------------------


	public function save($data)
	{
		ee()->session->set_cache(__CLASS__, $this->name(), $data);
		return ' ';

	} //end save()


// ----------------------------------------------------------------


	public function post_save($data)
	{
		// Prevent saving if save() was never called, happens in Channel Form
		// if the field is missing from the form
		if (($data = ee()->session->cache(__CLASS__, $this->name(), FALSE)) !== FALSE)
		{
			$adapter = new \EEBlocks\Database\Adapter(ee());
			$entry_id = isset( $this->content_id ) ? $this->content_id : ee()->input->get_post('entry_id');
			$site_id = isset( $this->settings['site_id'] ) ? $this->settings['site_id'] : ee()->input->get_post('site_id');

			$controller = new \EEBlocks\Controller\PublishController(
				ee(),
				$this->id(),
				$this->name(),
				$adapter,
				$this->_ftManager,
				$this->_hookExecutor);
			$controller->save(
				$data,
				$site_id,
				$entry_id);
		}

	} //end post_save()


// ----------------------------------------------------------------


	private function getBlocks($siteId, $entryId, $fieldId)
	{
		$key = "blocks|fetch|site_id:$siteId;entry_id:$entryId;field_id:$fieldId";

		$blocks = ee()->session->cache(__CLASS__, $key, false);
		if ($blocks)
		{
			ee()->TMPL->log_item('Blocks: retrieved cached blocks for "' . $key . '"');
			return $blocks;
		}
		else
		{
			ee()->TMPL->log_item('Blocks: fetching blocks for "' . $key . '"');

			$adapter = new \EEBlocks\Database\Adapter( ee() );
			$blocks = $adapter->getBlocks(
				$siteId,
				$entryId,
				$fieldId);

			ee()->session->set_cache(__CLASS__, $key, $blocks);

			return $blocks;
		}

	} //end getBlocks()


// ----------------------------------------------------------------


	public function replace_tag($data, $params = array(), $tagdata = false)
	{
		if (!$tagdata) return;

		$entryId = $this->row['entry_id'];
		$siteId = $this->row['entry_site_id'];

		$blocks = $this->getBlocks($siteId, $entryId, $this->field_id);

		$controller = new \EEBlocks\Controller\TagController(
			ee(),
			$this->field_id,
			$this->_ftManager);

		return $controller->replace($tagdata, $blocks, $this->row);

	} //end replace_tag()


// ----------------------------------------------------------------


	public function replace_tag_catchall($data, $params = array(), $tagdata = false, $modifier)
	{
		$entryId = $this->row['entry_id'];
		$siteId = $this->row['entry_site_id'];

		$blocks = $this->getBlocks($siteId, $entryId, $this->field_id);

		$controller = new \EEBlocks\Controller\TagController(
			ee(),
			$this->field_id,
			$this->_ftManager);

		switch ($modifier)
		{
			case 'total_blocks':
			case 'total_rows':
				return $controller->totalBlocks($blocks, $params);
		}

		return;

	} //end replace_tag_catchall()


// ----------------------------------------------------------------


	public function display_settings($data)
	{
		$this->includeThemeCSS('css/edit-field.css');
		$this->includeThemeJS('javascript/html.sortable.js');
		$this->includeThemeJS('javascript/edit-field.js');

		$blockDefinitionMaintenanceUrl = ee('CP/URL')->make('addons/settings/'.$this->pkg);

		ee()->lang->loadfile('bloqs');

		$adapter = new \EEBlocks\Database\Adapter(ee());
		$selectedBlockDefinitions = $adapter->getBlockDefinitionsForField(
			$this->field_id);
		$allBlockDefinitions = $adapter->getBlockDefinitions();

		$blockDefinitions = $this->sortBlockDefinitions(
			$selectedBlockDefinitions,
			$allBlockDefinitions);

		$output = '';

		if (count($blockDefinitions) > 0)
		{
			$output .= "<div class='blockselectors'>";
			$i = 1;

			foreach ($blockDefinitions as $blockDefinition)
			{
				$prefix = 'blockdefinitions[' . $blockDefinition->id . ']';
				$checked = '';
				if ($blockDefinition->selected)
				{
					$checked = 'checked';
				}
				$output .= "<div class='blockselector'>";
				$output .= "<input type='hidden' name='{$prefix}[order]' value='$i' js-order>";
				$output .= "<input type='hidden' name='{$prefix}[selected]' value='0'>";
				$output .= "<label class='choice block'><span class='blockselector-handle'>::</span><input type='checkbox' name='{$prefix}[selected]' value='1' $checked js-checkbox> <span>{$blockDefinition->name}</span></label>";
				$output .= "</div>\n";
				$i++;
			}
			$output .= "</div>"; // .blockselectors
		}
		else
		{
			$output .= '<p class="notice">' . lang('bloqs_fieldsettings_noblocksdefined') . '</p>';
		}
		$output .= "<p><a class='btn action' href='{$blockDefinitionMaintenanceUrl}'>" . lang('bloqs_fieldsettings_manageblockdefinitions') . "</a></p>";

		$settings = array(
        array(
            'title' => 'bloqs_fieldsettings_associateblocks',
            'desc' => 'bloqs_fieldsettings_associateblocks_desc',
            'wide' => TRUE,
            'fields' => array(
                'blockdefinitions' => array(
                    'type' => 'html',
                    'content' => $output,
                )
            )
        )
		);

    return array('field_options_bloqs' => array(
        'label' => 'field_options',
        'group' => 'bloqs',
        'settings' => $settings
    ));


	} //end display_settings()


// ----------------------------------------------------------------


	protected function sortBlockDefinitions($selected, $all)
	{
		$return = array();
		$selectedIds = array();

		foreach ($selected as $blockDefinition)
		{
			$selectedIds[] = $blockDefinition->id;
			$blockDefinition->selected = true;
			$return[] = $blockDefinition;
		}

		foreach ($all as $blockDefinition)
		{
			if (in_array($blockDefinition->id, $selectedIds)) {
				continue;
			}

			$blockDefinition->selected = false;
			$return[] = $blockDefinition;
		}

		return $return;

	} //end sortBlockDefinitions()


// ----------------------------------------------------------------


	public function post_save_settings($data)
	{
		$fieldId = $data['field_id'];

		$blockDefinitions = ee()->input->post('blockdefinitions');
		$adapter = new \EEBlocks\Database\Adapter(ee());

		if ($blockDefinitions)
		{
			foreach ($blockDefinitions as $blockDefinitionId => $values)
			{
				if ($values['selected'] == '0')
				{
					$adapter->disassociateBlockDefinitionWithField(
						$fieldId,
						$blockDefinitionId);
				}
				else if ($values['selected'] == '1')
				{
					$order = intval($values['order']);
					$adapter->associateBlockDefinitionWithField(
						$fieldId,
						$blockDefinitionId,
						$order);
				}
			}
		}

	} //end post_save_settings()


// ----------------------------------------------------------------


} //end class()
