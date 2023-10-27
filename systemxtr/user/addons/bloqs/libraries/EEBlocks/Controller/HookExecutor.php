<?php

namespace EEBlocks\Controller;

class HookExecutor {
	private $EE;

	/**
	 * Create the hook executor
	 *
	 * @param object $ee The ExpressionEngine instance.
	 */
	public function __construct($ee)
	{
		$this->EE = $ee;
	}

	protected function isActive($name) {
		return $this->EE->extensions->active_hook($name);
	}

	public function isPostSaveActive() {
		return $this->isActive('blocks_post_save');
	}

	public function postSave($blocks, $context) {
		if ($this->isPostSaveActive()) {
			$this->EE->extensions->call('blocks_post_save', $blocks, $context);
		}
	}

	public function isDiscoverFieldtypesActive() {
		return $this->isActive('blocks_discover_fieldtypes');
	}

	public function discoverFieldtypes($fieldtypeArray) {
		if ($this->isDiscoverFieldtypesActive()) {
			$fieldtypeArray = $this->EE->extensions->call('blocks_discover_fieldtypes', $fieldtypeArray);
		}

		return $fieldtypeArray;
	}
}
