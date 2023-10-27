<?php

namespace EEBlocks\Controller;

/**
 * Load and unload packages for field types
 *
 * Why does this class exist? Because I still refuse to pass the EE monolith
 * to FieldTypeWrapper, but I want to move responsibility for loading and
 * unloading package libraries into FieldTypeWrapper.
 */
class FieldTypePackageLoader
{
	private $_path;
	private $EE;

	function __construct($EE, $path)
	{
		$this->EE = $EE;
		$this->_path = $path;
	}

	public function load()
	{
		$this->EE->load->add_package_path($this->_path, FALSE);
	}

	public function unload()
	{
		$this->EE->load->remove_package_path($this->_path);
	}
}
