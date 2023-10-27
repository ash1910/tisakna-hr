<?php

namespace EEBlocks\Model;

class AtomDefinition
{
	var $id;
	var $shortname;
	var $name;
	var $order;
	var $type;
	var $settings;

	function __construct(
		$id = NULL,
		$shortname = NULL,
		$name = NULL,
		$instructions = NULL,
		$order = NULL,
		$type = NULL,
		$settings = NULL)
	{
		$this->id = $id;
		$this->shortname = $shortname;
		$this->name = $name;
		$this->instructions = $instructions;
		$this->order = $order;
		$this->type = $type;
		$this->settings = $settings;
	}

	function isRequired() {
		return isset($this->settings['col_required'])
			&& $this->settings['col_required'] === 'y';
	}

	function isSearchable() {
		return isset($this->settings['col_search'])
			&& $this->settings['col_search'] === 'y';
	}
}
