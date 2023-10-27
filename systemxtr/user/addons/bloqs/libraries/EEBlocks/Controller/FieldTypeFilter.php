<?php

namespace EEBlocks\Controller;

/**
 * Provider a filter for which field types are allowed.
 */
class FieldTypeFilter
{
	private $_whitelist;

	function __construct()
	{
		$_whitelist = array();
	}

	public function load($filename)
	{
		$xmlStr = file_get_contents($filename);
		$xml = new \SimpleXMLElement($xmlStr);
		foreach ($xml->whitelist->fieldtype as $fieldtype)
		{
			$name = strval($fieldtype->attributes()->name);
			$version = strval($fieldtype->attributes()->version);
			$adapter = strval($fieldtype->attributes()->adapter);
			$this->_whitelist[$name] = array(
				'version' => $version,
				'adapter' => $adapter
				);
		}
	}

	public function filter($name, $version)
	{
		if (isset($this->_whitelist[$name])) {
			$versionSpec = $this->_whitelist[$name]['version'];
			return $this->testVersion($versionSpec, $version);
		}
		return false;
	}

	private function testVersion($spec, $actual)
	{
		if ($spec == '' || $spec == '*')
		{
			return true;
		}

		$matches = true;
		$specParts = explode(' ', $spec);

		foreach ($specParts as $specPart)
		{
			$specPart = trim($specPart);
			$matches &= $this->testVersionPart($specPart, $actual);
		}

		return $matches;
	}

	private function testVersionPart($specPart, $actual)
	{
		if ($specPart == '' || $specPart == '*')
		{
			return true;
		}

		$matches = array();
		$re = '/^(==|=|eq:|\<\>|!=|ne:|\>=|ge:|\>|gt:|\<=|le:|\<|lt:)(.*)$/';
		if (!preg_match($re, $specPart, $matches))
		{
			throw new \Exception("Unexpected version part: " . $specPart);
		}

		$comparator = $matches[1];
		$version = $matches[2];

		// Remove the trailing ':' from the comparator if necessary.
		if (strpos($comparator, ':'))
		{
			$comparator = substr($comparator, 0, strpos($comparator, ':'));
		}

		return version_compare($actual, $version, $comparator);
	}

	public function adapter($name)
	{
		if (isset($this->_whitelist[$name])) {
			$adapter = $this->_whitelist[$name]['adapter'];
			if ($adapter === '') {
				return null;
			}
			return $adapter;
		}
		return null;
	}
}
