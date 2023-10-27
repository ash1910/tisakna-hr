<?php

namespace EEBlocks\Model;

class Atom
{
	var $id;
	var $value;
	var $definition;

	function __construct($id = NULL, $value = NULL, $definition = NULL)
	{
		$this->id = $id;
		$this->value = $value;
		$this->definition = $definition;
	}
}