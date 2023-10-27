<?php

namespace EEBlocks\Controller;

use \Exception;
use \EEBlocks\Controller\FieldTypePackageLoader;
use \EEBlocks\Controller\FieldTypeWrapper;
use \stdClass;

class FieldTypeManager
{
	private $EE;
	private $_hookExecutor;
	private $_prefix;
	private $_filter;
	private $_fieldtypeArray = null;

	function __construct($ee, $filter, $hookExecutor) {
		$this->EE = $ee;
		$this->_prefix = 'blocks';
		$this->_filter = $filter;
		$this->_hookExecutor = $hookExecutor;
	}


// ----------------------------------------------------------------


	private function getFieldTypePackageLoader($fieldtype) {
		$fieldtypeApi = $this->EE->api_channel_fields;

		$_ftPath = $fieldtypeApi->ft_paths[$fieldtype];
		return new FieldTypePackageLoader($this->EE, $_ftPath);
	}


// ----------------------------------------------------------------


	public function instantiateFieldtype(
		$atomDefinition,
		$rowName = null,
		$blockId = null,
		$fieldId = 0,
		$entryId = 0)
	{
		$fieldtypeArray = $this->buildFieldTypeArray();
		$fieldtypeObject = $this->findFieldTypeInArray($fieldtypeArray, $atomDefinition->type);

		if (is_null($fieldtypeObject))
		{
			return null;
		}

		// Instantiate fieldtype
		$fieldtype = $this->EE->api_channel_fields->setup_handler($fieldtypeObject->type, true);

		if (!$fieldtype)
		{
			return null;
		}

		FieldTypeWrapper::initializeFieldtype(
			$fieldtype,
			$atomDefinition,
			$rowName,
			$blockId,
			$fieldId,
			$entryId);

		$ftpl = $this->getFieldTypePackageLoader($atomDefinition->type);
		$fta = null;
		if (isset($fieldtypeObject->adapter))
		{
			$fta = $fieldtypeObject->adapter;
			if (is_callable(array($fta, 'setFieldtype'))) {
				call_user_func(array($fta, 'setFieldtype'), $fieldtype);
			}
		}
		$ftw = new FieldTypeWrapper($fieldtype, $ftpl, $fta);

		if ($ftw->getContentType() === 'none')
		{
			throw new Exception("Specified fieldtype '{$atomDefinition->type}' does not support blocks");
		}
		
		return $ftw;
	}


// ----------------------------------------------------------------


	private function findFieldTypeInArray($array, $type)
	{
		foreach ($array as $fieldtype)
		{
			if ($fieldtype->type == $type)
			{
				return $fieldtype;
			}
		}

		return null;
	}


// ----------------------------------------------------------------


	public function getFieldTypes()
	{
		return $this->buildFieldTypeArray();
	}


// ----------------------------------------------------------------


	private function instantiateAdapter($name)
	{
		$adapterName = null;
		if (!is_null($this->_filter))
		{
			$adapterName = $this->_filter->adapter($name);
		}

		if (is_null($adapterName))
		{
			return null;
		}

		try {
			return new $adapterName();
		}
		catch (Exception $e)
		{
			return null;
		}
	}


// ----------------------------------------------------------------


	private function buildFieldTypeArray()
	{
		if (!is_null($this->_fieldtypeArray))
		{
			return $this->_fieldtypeArray;
		}

		$this->EE->load->library('api');
		$this->EE->legacy_api->instantiate('channel_fields');

		$fieldtypeApi = $this->EE->api_channel_fields;

		$fieldtypes = $fieldtypeApi->fetch_installed_fieldtypes();

		// For some reason, calling setup_handler on blocks makes it so that
		// the module can't load any views. So, don't let setup_handler be
		// called on blocks.
		unset($fieldtypes['blocks']);

		$fieldtypeArray = array();

		foreach ($fieldtypes as $fieldName => $data)
		{
			$fieldtype = $fieldtypeApi->setup_handler($fieldName, true);
			$ftpl = $this->getFieldTypePackageLoader($fieldName);
			$ftw = new FieldTypeWrapper($fieldtype, $ftpl, null);

			if (!$ftw->supportsGrid())
			{
				continue;
			}

			if (!$ftw->supportsBlocks())
			{
				// It doesn't support Blocks. But don't be too hasty; maybe
				// it's in the whitelist.

				if (is_null($this->_filter) || !$this->_filter->filter($fieldName, $fieldtypes[$fieldName]['version']))
				{
					// OK, the whitelist didn't like it, either.
					continue;
				}
			}

			$obj = new stdClass();
			$obj->type = $fieldName;
			$obj->version = $fieldtypes[$fieldName]['version'];
			$obj->name = $fieldtypes[$fieldName]['name'];
			$obj->adapter = $this->instantiateAdapter($fieldName);

			$fieldtypeArray[] = $obj;
		}

		$fieldtypeArray = $this->_hookExecutor->discoverFieldtypes($fieldtypeArray);

		usort($fieldtypeArray, function($a, $b)
		{
			if ($a->name < $b->name)
			{
				return -1;
			}
			else if ($a->name == $b->name) {
				return 0;
			}
			else {
				return 1;
			}
		});

		$this->_fieldtypeArray = $fieldtypeArray;

		return $this->_fieldtypeArray;
	}


// ----------------------------------------------------------------


} //end class
