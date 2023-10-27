<?php

namespace EEBlocks\Adapter;

use \ReflectionObject;

class BuiltinContentTypeAdapter
{
	public function setFieldtype($fieldtype)
	{
		// Some of the builtin fieldtypes have some things hardcoded when
		// content_type is 'grid'. Since Blocks is pretty darn close to Grid,
		// we want these builtin fieldtypes to treat us like Grid. So, let's
		// lie and say we're Grid.
		//
		// $fieldtype->content_type = 'grid';
		//
		// Unfortunately, content_type is a private variable. So, we need to
		// be even sneakier.
		$refObject = new ReflectionObject($fieldtype);
		$refProperty = $refObject->getProperty('content_type');
		$refProperty->setAccessible(true);
		$refProperty->setValue($fieldtype, 'grid');
	}
}
