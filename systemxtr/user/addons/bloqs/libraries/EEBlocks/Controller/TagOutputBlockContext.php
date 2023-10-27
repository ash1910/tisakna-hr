<?php

namespace EEBlocks\Controller;

use \EEBlocks\Model\Block;
use \EEBlocks\Model\Atom;

/**
 * The context for a single block when being outputted by the TagController.
 */
class TagOutputBlockContext
{
	private $_previousContext;
	private $_currentBlock;
	private $_nextContext;
	private $_index;
	private $_total;
	private $_indexOfType;
	private $_totalOfType;

	public function __construct(
		$currentBlock,
		$index,
		$total,
		$indexOfType,
		$totalOfType)
	{
		$this->_currentBlock  = $currentBlock;
		$this->_index         = $index;
		$this->_total         = $total;
		$this->_indexOfType   = $indexOfType;
		$this->_totalOfType   = $totalOfType;

		$this->_previousContext = null;
		$this->_nextContext = null;
	}

	public function getCurrentBlock()
	{
		return $this->_currentBlock;
	}

	/**
	 * Get the shortname for the associated block
	 *
	 * Provides a simple (and abstract) way to get the block's shortname, so
	 * that the caller doesn't have to have a huge chain of property lookups
	 * and function calls.
	 */
	public function getShortname()
	{
		return $this->_currentBlock->definition->shortname;
	}

	public function setPreviousContext($previousContext)
	{
		$this->_previousContext = $previousContext;
	}

	public function getPreviousContext()
	{
		return $this->_previousContext;
	}

	public function setNextContext($nextContext)
	{
		$this->_nextContext = $nextContext;
	}

	public function getNextContext()
	{
		return $this->_nextContext;
	}

	public function getCount()
	{
		return $this->_index + 1;
	}

	public function getIndex()
	{
		return $this->_index;
	}

	public function getTotal()
	{
		return $this->_total;
	}

	public function getIndexOfType()
	{
		return $this->_indexOfType;
	}

	public function getCountOfType()
	{
		return $this->_indexOfType + 1;
	}

	public function getTotalOfType()
	{
		return $this->_totalOfType;
	}
}
