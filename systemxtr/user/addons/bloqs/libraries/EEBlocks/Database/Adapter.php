<?php

namespace EEBlocks\Database;

use EEBlocks\Model\BlockDefinition;
use EEBlocks\Model\AtomDefinition;
use EEBlocks\Model\Block;
use EEBlocks\Model\Atom;

class Adapter
{
	private $EE;

	function __construct($ee) {
		if (is_null($ee))
		{
			throw new Exception("ExpressionEngine object is required");
		}
		$this->EE = $ee;
	}

	public function getBlocks($siteId, $entryId, $fieldId)
	{
		$arr = array();

		$querystring = <<<EOF
SELECT
	bd.id as bd_id,
	bd.shortname as bd_shortname,
	bd.name as bd_name,
	bd.instructions as bd_instructions,
	b.id as b_id,
	b.order as b_order,
	ad.id as ad_id,
	ad.shortname as ad_shortname,
	ad.name as ad_name,
	ad.instructions as ad_instructions,
	ad.order as ad_order,
	ad.type as ad_type,
	ad.settings as ad_settings,
	a.id as a_id,
	IFNULL(a.data, '') as a_data
FROM exp_blocks_block b
LEFT JOIN exp_blocks_blockdefinition bd
  ON b.blockdefinition_id = bd.id
LEFT JOIN exp_blocks_atomdefinition ad
  ON bd.id = ad.blockdefinition_id
LEFT JOIN exp_blocks_atom a
  ON a.block_id = b.id AND a.atomdefinition_id = ad.id
WHERE b.field_id = ? AND entry_id = ? AND site_id = ?
ORDER BY b.order, ad.order
EOF;

		$ee = $this->EE;

		$query = $ee->db->query($querystring, array($fieldId, $entryId, $siteId));

		$previousBlockId = NULL;
		$currentBlock = NULL;

		foreach ($query->result() as $dbrow)
		{
			if ($previousBlockId !== intval($dbrow->b_id)) {
				$previousBlockId = intval($dbrow->b_id);
				if (!is_null($currentBlock)) {
					$arr[] = $currentBlock;
				}
				$blockDefinition = new BlockDefinition(
					intval($dbrow->bd_id),
					$dbrow->bd_shortname,
					$dbrow->bd_name,
					$dbrow->bd_instructions);
				$currentBlock = new Block(
					intval($dbrow->b_id),
					intval($dbrow->b_order),
					$blockDefinition);
			}

			$atomDefinition = new AtomDefinition(
				intval($dbrow->ad_id),
				$dbrow->ad_shortname,
				$dbrow->ad_name,
				$dbrow->ad_instructions,
				intval($dbrow->ad_order),
				$dbrow->ad_type,
				json_decode($dbrow->ad_settings, TRUE)
				);

			$currentBlock->atoms[$atomDefinition->shortname] = new Atom(
				intval($dbrow->a_id),
				$dbrow->a_data,
				$atomDefinition);
		}

		if (!is_null($currentBlock))
		{
			$arr[] = $currentBlock;
		}

		return $arr;
	}

	public function getBlockDefinitions()
	{
		$arr = array();

		$querystring = <<<EOF
SELECT
	bd.id as bd_id,
	bd.shortname as bd_shortname,
	bd.name as bd_name,
	bd.instructions as bd_instructions,
	ad.id as ad_id,
	ad.shortname as ad_shortname,
	ad.name as ad_name,
	ad.instructions as ad_instructions,
	ad.order as ad_order,
	ad.type as ad_type,
	ad.settings as ad_settings
FROM exp_blocks_blockdefinition bd
LEFT JOIN exp_blocks_atomdefinition ad
  ON ad.blockdefinition_id = bd.id
ORDER BY bd.shortname, ad.order
EOF;

		$ee = $this->EE;

		$query = $ee->db->query($querystring, array());

		$previousBlockId = NULL;
		$currentBlock = NULL;

		foreach ($query->result() as $dbrow)
		{
			if ($previousBlockId !== intval($dbrow->bd_id)) {
				$previousBlockId = intval($dbrow->bd_id);
				if (!is_null($currentBlock)) {
					$arr[] = $currentBlock;
				}
				$currentBlock = new BlockDefinition(
					intval($dbrow->bd_id),
					$dbrow->bd_shortname,
					$dbrow->bd_name,
					$dbrow->bd_instructions);
			}

			$currentBlock->atoms[$dbrow->ad_shortname] = new AtomDefinition(
				intval($dbrow->ad_id),
				$dbrow->ad_shortname,
				$dbrow->ad_name,
				$dbrow->ad_instructions,
				intval($dbrow->ad_order),
				$dbrow->ad_type,
				json_decode($dbrow->ad_settings, TRUE));
		}

		if (!is_null($currentBlock))
		{
			$arr[] = $currentBlock;
		}

		return $arr;
	}

	public function getBlockDefinitionById($blockDefinitionId)
	{
		// Could query the database for this, and maybe should, but for now
		// we'll rely on getBlockDefinition to do the hard work for us.

		$blockDefinitions = $this->getBlockDefinitions();
		$blockDefinition = null;

		foreach ($blockDefinitions as $blockDefinitionCandidate)
		{
			if ($blockDefinitionCandidate->id === $blockDefinitionId)
			{
				$blockDefinition = $blockDefinitionCandidate;
				break;
			}
		}

		return $blockDefinition;
	}

	public function getBlockDefinitionByShortname($shortname)
	{
		// Could query the database for this, and maybe should, but for now
		// we'll rely on getBlockDefinition to do the hard work for us.

		$blockDefinitions = $this->getBlockDefinitions();
		$blockDefinition = null;

		foreach ($blockDefinitions as $blockDefinitionCandidate)
		{
			if ($blockDefinitionCandidate->shortname === $shortname)
			{
				$blockDefinition = $blockDefinitionCandidate;
				break;
			}
		}

		return $blockDefinition;
	}

	public function getBlockDefinitionsForField($fieldId)
	{
		$arr = array();

		$querystring = <<<EOF
SELECT
	bd.id as bd_id,
	bd.shortname as bd_shortname,
	bd.name as bd_name,
	bd.instructions as bd_instructions,
	ad.id as ad_id,
	ad.shortname as ad_shortname,
	ad.name as ad_name,
	ad.instructions as ad_instructions,
	ad.order as ad_order,
	ad.type as ad_type,
	ad.settings as ad_settings
FROM exp_blocks_blockfieldusage bfu
LEFT JOIN exp_blocks_blockdefinition bd
  ON bd.id = bfu.blockdefinition_id
LEFT JOIN exp_blocks_atomdefinition ad
  ON ad.blockdefinition_id = bd.id
WHERE bfu.field_id = ?
ORDER BY bfu.order, ad.order
EOF;

		$ee = $this->EE;

		$query = $ee->db->query($querystring, array($fieldId));

		$previousBlockId = NULL;
		$currentBlock = NULL;

		foreach ($query->result() as $dbrow)
		{
			if ($previousBlockId !== intval($dbrow->bd_id)) {
				$previousBlockId = intval($dbrow->bd_id);
				if (!is_null($currentBlock)) {
					$arr[] = $currentBlock;
				}
				$currentBlock = new BlockDefinition(
					intval($dbrow->bd_id),
					$dbrow->bd_shortname,
					$dbrow->bd_name,
					$dbrow->bd_instructions);
			}

			$currentBlock->atoms[$dbrow->ad_shortname] = new AtomDefinition(
				intval($dbrow->ad_id),
				$dbrow->ad_shortname,
				$dbrow->ad_name,
				$dbrow->ad_instructions,
				intval($dbrow->ad_order),
				$dbrow->ad_type,
				json_decode($dbrow->ad_settings, TRUE));
		}

		if (!is_null($currentBlock))
		{
			$arr[] = $currentBlock;
		}

		return $arr;
	}

	public function associateBlockDefinitionWithField($fieldId, $blockDefinitionId, $order)
	{
		$querystring = <<<EOF
INSERT INTO exp_blocks_blockfieldusage
	(field_id, blockdefinition_id, `order`)
VALUES
	(?, ?, ?)
ON DUPLICATE KEY UPDATE
	`order` = ?
EOF;
		$ee = $this->EE;

		$query = $ee->db->query($querystring, array($fieldId, $blockDefinitionId, $order, $order));
	}

	public function disassociateBlockDefinitionWithField($fieldId, $blockDefinitionId)
	{
		$ee = $this->EE;

		$querystring1 = <<<EOF
DELETE a
FROM exp_blocks_atom a
LEFT JOIN exp_blocks_block b
ON a.block_id = b.id
WHERE field_id = ?
  AND blockdefinition_id = ?
EOF;

		$querystring2 = <<<EOF
DELETE FROM exp_blocks_block
WHERE field_id = ?
  AND blockdefinition_id = ?
EOF;

		$querystring3 = <<<EOF
DELETE FROM exp_blocks_blockfieldusage
WHERE field_id = ?
  AND blockdefinition_id = ?
EOF;

		$ee->db->trans_start();
		$ee->db->query($querystring1, array($fieldId, $blockDefinitionId));
		$ee->db->query($querystring2, array($fieldId, $blockDefinitionId));
		$ee->db->query($querystring3, array($fieldId, $blockDefinitionId));
		$ee->db->trans_complete();

		return $ee->db->trans_status();
	}

	public function setAtomData($blockId, $atomDefinitionId, $data)
	{
		$querystring = <<<EOF
INSERT INTO exp_blocks_atom
	(block_id, atomdefinition_id, data)
VALUES
	(?, ?, ?)
ON DUPLICATE KEY UPDATE
	data = ?
EOF;
		$ee = $this->EE;

		$query = $ee->db->query($querystring, array($blockId, $atomDefinitionId, $data, $data));
	}

	public function createBlock($blockDefinitionId, $siteId, $entryId, $fieldId, $order)
	{
		$querystring = <<<EOF
INSERT INTO exp_blocks_block
	(blockdefinition_id, site_id, entry_id, field_id, `order`)
VALUES
	(?, ?, ?, ?, ?)
EOF;
		$ee = $this->EE;

		$query = $ee->db->query($querystring, array($blockDefinitionId, $siteId, $entryId, $fieldId, $order));
		return $ee->db->insert_id();
	}

	public function setBlockOrder($blockId, $order)
	{
		$querystring = "UPDATE exp_blocks_block SET `order` = ? WHERE id = ?";

		$ee = $this->EE;

		$ee->db->query($querystring, array($order, $blockId));
	}

	public function deleteBlock($blockId)
	{
		$ee = $this->EE;

		$ee->db->trans_start();
		$ee->db->query("DELETE FROM exp_blocks_atom WHERE block_id = ?", array($blockId));
		$ee->db->query("DELETE FROM exp_blocks_block WHERE id = ?", array($blockId));
		$ee->db->trans_complete();

		return $ee->db->trans_status();
	}


	/**
	 * Create the core parts of a block definition. Note that this does not
	 * create the atoms within a block definition.
	 */
	public function createBlockDefinition($blockDefinition)
	{
		$querystring = <<<EOF
INSERT INTO exp_blocks_blockdefinition
	(name, shortname, instructions)
VALUES
	(?,    ?,         ?)
EOF;
		$ee = $this->EE;

		$ee->db->query($querystring, array(
			$blockDefinition->name,
			$blockDefinition->shortname,
			$blockDefinition->instructions));

		$blockDefinition->id = $ee->db->insert_id();
	}

	/**
	 * Update the core parts of a block definition. Note that this does not
	 * update the atoms within a block definition.
	 */
	public function updateBlockDefinition($blockDefinition)
	{
		$querystring = <<<EOF
UPDATE exp_blocks_blockdefinition
SET
	name = ?,
	shortname = ?,
	instructions = ?
WHERE
	id = ?
EOF;
		$ee = $this->EE;

		$ee->db->query($querystring, array(
			$blockDefinition->name,
			$blockDefinition->shortname,
			$blockDefinition->instructions,
			$blockDefinition->id));
	}

	public function deleteBlockDefinition($blockDefinitionId)
	{
		$ee = $this->EE;

		$queries = array(
<<<EOF
DELETE a
FROM exp_blocks_atom a
INNER JOIN exp_blocks_block b
  ON a.block_id = b.id
WHERE b.blockdefinition_id = ?
EOF
, <<<EOF
DELETE
FROM exp_blocks_atomdefinition
WHERE blockdefinition_id = ?
EOF
, <<<EOF
DELETE
FROM exp_blocks_block
WHERE blockdefinition_id = ?
EOF
, <<<EOF
DELETE
FROM exp_blocks_blockfieldusage
WHERE blockdefinition_id = ?
EOF
, <<<EOF
DELETE FROM exp_blocks_blockdefinition WHERE id = ?
EOF
);

		$ee->db->trans_start();
		foreach ($queries as $querystring)
		{
			$ee->db->query($querystring, array($blockDefinitionId));
		}
		$ee->db->trans_complete();

		return $ee->db->trans_status();
	}

	public function createAtomDefinition($blockDefinitionId, $atomDefinition)
	{
		$querystring = <<<EOF
INSERT INTO exp_blocks_atomdefinition
  (blockdefinition_id, shortname, name, instructions,
   `order`, type, settings)
VALUES
  (?, ?, ?, ?,
   ?, ?, ?)
EOF;
		$ee = $this->EE;

		$ee->db->query($querystring, array(
			$blockDefinitionId,
			$atomDefinition->shortname,
			$atomDefinition->name,
			$atomDefinition->instructions,
			$atomDefinition->order,
			$atomDefinition->type,
			json_encode($atomDefinition->settings)));

		$atomDefinition->id = $ee->db->insert_id();
	}

	public function updateAtomDefinition($atomDefinition)
	{
		$querystring = <<<EOF
UPDATE exp_blocks_atomdefinition
SET
	name = ?,
	shortname = ?,
	instructions = ?,
	`order` = ?,
	type = ?,
	settings = ?
WHERE
	id = ?
EOF;
		$ee = $this->EE;

		$ee->db->query($querystring, array(
			$atomDefinition->name,
			$atomDefinition->shortname,
			$atomDefinition->instructions,
			$atomDefinition->order,
			$atomDefinition->type,
			json_encode($atomDefinition->settings),
			$atomDefinition->id));
	}

	public function deleteAtomDefinition($atomDefinitionId)
	{
		$ee = $this->EE;

		$querystring1 = <<<EOF
DELETE FROM exp_blocks_atom WHERE atomdefinition_id = ?
EOF;
		$querystring2 = <<<EOF
DELETE FROM exp_blocks_atomdefinition WHERE id = ?
EOF;

		$ee->db->trans_start();
		$ee->db->query($querystring1, array($atomDefinitionId));
		$ee->db->query($querystring2, array($atomDefinitionId));
		$ee->db->trans_complete();

		return $ee->db->trans_status();
	}

	public function updateFieldData($siteId, $entryId, $fieldId, $data)
	{
		$ee = $this->EE;

		$updates = array(
			'field_id_' . $fieldId => $data
		);

		$ee->db->where('entry_id', $entryId)
			->where('site_id', $siteId)
			->update('channel_data', $updates);
	}
}
