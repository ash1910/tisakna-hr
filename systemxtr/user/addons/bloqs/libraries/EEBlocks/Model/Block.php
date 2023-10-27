<?php

namespace EEBlocks\Model;

class Block
{
	var $id;
	var $order;
	var $definition;
	var $atoms;

	function __construct($id = NULL, $order = NULL, $definition = NULL)
	{
		$this->id = $id;
		$this->order = $order;
		$this->definition = $definition;
		$this->atoms = array();
	}
}