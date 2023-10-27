<?php

namespace EEBlocks\Controller;

use \InvalidArgumentException;

class FieldTypeWrapper {
	private $_fieldtype;
	private $_contentType = null;
	private $_packageLoader;
	private $_shim;

	function __construct($fieldtype, $packageLoader, $shim = null) {
		$this->_fieldtype = $fieldtype;
		$this->_contentType = $this->_getContentType();
		$this->_shim = $shim;

		if (is_null($packageLoader)) {
			throw new InvalidArgumentException('packageLoader should not be null');
		}

		$this->_packageLoader = $packageLoader;
	}


// ----------------------------------------------------------------


	private function _getContentType() 
	{
		if (!is_callable(array($this->_fieldtype, 'accepts_content_type'))) {
			throw new InvalidArgumentException('Specified fieldtype does not have method accepts_content_type');
		}

		$supportsGrid = $this->_fieldtype->accepts_content_type('grid');
		$supportsBlocks = $this->_fieldtype->accepts_content_type('blocks/1');
		$random = 'blocks/' . rand(1000, 9999);
		$supportsRandom = $this->_fieldtype->accepts_content_type($random);

		if ($supportsBlocks && !$supportsRandom) {
			return 'blocks/1';
		}
		if ($supportsBlocks && !$supportsGrid) {
			// Claims to support blocks but doesn't support Grid? Yeah, right.
			return 'none';
		}
		if ($supportsGrid) {
			return 'grid';
		}
		return 'none';
	}

// ----------------------------------------------------------------


	function supportsGrid() 
	{
		// We would have thrown an error if it didn't, so it does.
		return $this->_contentType !== 'none';
	}

// ----------------------------------------------------------------


	function supportsBlocks() 
	{
		return $this->_contentType === 'blocks/1';
	}

// ----------------------------------------------------------------


	function getContentType() 
	{
		return $this->_contentType;
	}

// ----------------------------------------------------------------


	public static function initializeFieldtype($fieldtype, $atomDefinition, $rowName, $blockId, $fieldId, $entryId)
	{
		$colId = $atomDefinition->id;

		// Assign settings to fieldtype manually so they're available like
		// normal field settings
		$fieldtype->_init(
			array(
				'field_id'      => $colId,
				'field_name'    => 'col_id_' . $colId,
				'content_id'    => $entryId,
				'content_type'  => 'blocks'
			)
		);

		$colRequired = isset($atomDefinition->settings['col_required'])
			? $atomDefinition->settings['col_required']
			: false;

		// Assign fieldtype column settings and any other information that
		// will be helpful to be accessible by fieldtypes
		$fieldtype->settings = array_merge(
			$atomDefinition->settings,
			array(
				'field_label'     => $atomDefinition->name,
				'field_required'  => $colRequired,
				'col_id'          => $colId,
				'col_name'        => $atomDefinition->shortname,
				'col_required'    => $colRequired,
				'entry_id'        => $entryId,
				'grid_field_id'   => $fieldId,
				'grid_row_id'     => $blockId,
				'grid_row_name'   => $rowName,
				'blocks_atom_id'  => $fieldId,
				'blocks_block_id' => $blockId,
				'blocks_block_name' => $rowName)
		);
	}

// ----------------------------------------------------------------


	public function reinitialize($atomDefinition, $rowName, $blockId, $fieldId, $entryId)
	{
		$this::initializeFieldtype(
			$this->_fieldtype,
			$atomDefinition,
			$rowName,
			$blockId,
			$fieldId,
			$entryId);
	}

// ----------------------------------------------------------------


	public function setSetting($setting, $value)
	{
		$this->_fieldtype->settings[$setting] = $value;
	}

// ----------------------------------------------------------------


	public function initialize($data)
	{
		$this->_fieldtype->_init($data);
	}

// ----------------------------------------------------------------


	public function replace($modifier, $data, $params = array(), $tagdata = NULL)
	{
		$ft = $this->_fieldtype;
		$shim = $this->_shim;

		if (is_null($modifier)) {
			$modifier = 'tag';
		}

		if (is_callable(array($shim, 'grid_replace_' . $modifier))) {
			return call_user_func(array($shim, 'grid_replace_' . $modifier), $data, $params, $tagdata);
		}

		if (is_callable(array($shim, 'replace_' . $modifier))) {
			return call_user_func(array($shim, 'replace_' . $modifier), $data, $params, $tagdata);
		}

		if (is_callable(array($ft, 'grid_replace_' . $modifier))) {
			return call_user_func(array($ft, 'grid_replace_' . $modifier), $data, $params, $tagdata);
		}

		// Does grid_replace_tag_catchall supercede replace_modifier?

		if (is_callable(array($ft, 'replace_' . $modifier))) {
			return call_user_func(array($ft, 'replace_' . $modifier), $data, $params, $tagdata);
		}

		if ($modifier != 'tag' && is_callable(array($ft, 'replace_tag_catchall'))) {
			return $ft->replace_tag_catchall($data, $params, $tagdata, $modifier);
		}

		// If there's a modifier that wasn't matched, do we fall back to replace_tag, throw an error, or return nothing?

		return $ft->replace_tag($data, $params, $tagdata);
	}


// ----------------------------------------------------------------


	private function call($methodName, $args, $passthrough)
	{
		$this->_packageLoader->load();
		$ft = $this->_fieldtype;
		$shim = $this->_shim;

		if (is_callable(array($shim, 'grid_' . $methodName)))
		{
			$result = call_user_func_array(array($shim, 'grid_' . $methodName), $args);
		}

		else if (is_callable(array($shim, $methodName))) {
			$result = call_user_func_array(array($shim, $methodName), $args);
		}

		else if (is_callable(array($ft, 'grid_' . $methodName)))
		{
			$result = call_user_func_array(array($ft, 'grid_' . $methodName), $args);
		}

		else if (is_callable(array($ft, $methodName))) {
			$result = call_user_func_array(array($ft, $methodName), $args);
		}

		// Hrmm... this is suspect.
		else if ($passthrough)
		{
			$result = $args[0];
		}
		else
		{
			$result = null;
		}
		$this->_packageLoader->unload();
		return $result;
	}

// ----------------------------------------------------------------


	private function callGridOnly($methodName, $args)
	{
		$this->_packageLoader->load();
		$ft = $this->_fieldtype;
		$shim = $this->_shim;

		if (is_callable(array($shim, $methodName)))
		{
			$result = call_user_func_array(array($shim, $methodName), $args);
		}
		else if (is_callable(array($ft, $methodName)))
		{
			$result = call_user_func_array(array($ft, $methodName), $args);
		}
		else {
			$result = null;
		}

		$this->_packageLoader->unload();
		return $result;
	}

// ----------------------------------------------------------------


	public function preProcess($data)
	{
		return $this->call('pre_process', array($data), true);
	}

// ----------------------------------------------------------------


	public function displayField($data)
	{
		return $this->call('display_field', array($data), false);
	}

// ----------------------------------------------------------------


	public function save($data)
	{
		return $this->call('save', array($data), true);
	}

// ----------------------------------------------------------------


	public function postSave($data)
	{
		return $this->call('post_save', array($data), true);
	}


// ----------------------------------------------------------------

	public function validate($data)
	{
		// AH! I don't know if validate should passthrough or not!
		return $this->call('validate', array($data), true);
	}

// ----------------------------------------------------------------


	public function displaySettings($data)
	{
		$method_to_call = is_callable(array($this->_fieldtype, 'grid_display_settings')) ?
			'grid_display_settings' : 'display_settings';

		return $this->callGridOnly($method_to_call, array($data));	

	}


// ----------------------------------------------------------------


	public function validateSettings($data)
	{
		return $this->callGridOnly('grid_validate_settings', array($data));
	}

// ----------------------------------------------------------------


	public function saveSettings($data)
	{
		return $this->call('save_settings', array($data), false);
	}


// ----------------------------------------------------------------

}
