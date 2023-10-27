<?php

namespace EEBlocks\Controller;

use \EEBlocks\Controller\TagOutputBlockContext;

/**
 * A parser and outputter for the root tag of the Blocks fieldtype.
 *
 * This class is primarily used from Blocks_ft::replace_tag
 */
class TagController
{
	private $EE;
	private $_ftManager;
	private $_fieldId;
	private $_prefix;

	/**
	 * Create the controller
	 *
	 * @param object $ee The ExpressionEngine instance.
	 * @param int $fieldId The database ID for the EE field itself.
	 * @param \EEBlocks\Controller\FieldTypeManager $fieldTypeManager The
	 *        object responsible for creating and loading field types.
	 */
	public function __construct($ee, $fieldId, $fieldTypeManager)
	{
		$this->EE = $ee;
		$this->_prefix = 'blocks';
		$this->_fieldId = $fieldId;
		$this->_ftManager = $fieldTypeManager;
	}

	public function buildContexts($blocks)
	{
		$contexts = array();

		$totalsForBlockDefinitions = array();
		$indexForBlockDefinitions = array();
		$total = count($blocks);
		$index = 0;

		// Establish totals for each block definition.
		foreach ($blocks as $block)
		{
			$shortname = $block->definition->shortname;
			if (!isset($totalsForBlockDefinitions[$shortname]))
			{
				$totalsForBlockDefinitions[$shortname] = 0;
				$indexForBlockDefinitions[$shortname] = 0;
			}
			$totalsForBlockDefinitions[$shortname]++;
		}

		foreach ($blocks as $block)
		{
			$shortname = $block->definition->shortname;

			$indexForBlockDefinition = $indexForBlockDefinitions[$shortname];

			$context = new TagOutputBlockContext(
				$block,
				$index,
				$total,
				$indexForBlockDefinition,
				$totalsForBlockDefinitions[$shortname]);
			$contexts[] = $context;

			$index++;
			$indexForBlockDefinitions[$shortname]++;
		}

		for ($i = 0; $i < count($contexts); $i++)
		{
			if (0 <= $i - 1)
			{
				$contexts[$i]->setPreviousContext($contexts[$i - 1]);
			}
			if ($i + 1 < count($contexts))
			{
				$contexts[$i]->setNextContext($contexts[$i + 1]);
			}
		}

		return $contexts;
	}

	/**
	 * The primary entry point for the Blocks parser
	 *
	 * @param string $tagdata The parsed template that EE gives.
	 * @param \EEBlocks\Model\Block[] $blocks The blocks that will be
	 *        outputted.
	 * @param array $channelRow Top-level row data that EE provides.
	 *        Typically $this->row from the fieldtype.
	 *
	 * @return string
	 */
	public function replace($tagdata, $blocks, $channelRow)
	{
		$output = '';

		$contexts = $this->buildContexts($blocks);

		foreach ($contexts as $context)
		{
			$output .= $this->_renderBlockSections(
				$tagdata,
				$context,
				$channelRow);
		}

		return $output;
	}

	/**
	 * Display the total number of Blocks.
	 *
	 * @param array $params Parameters given via the EE tag.
	 */
	public function totalBlocks($blocks, $params)
	{
		if (isset($params['type']))
		{
			$type = $params['type'];
			$types = explode('|', $type);
			$count = 0;
			foreach ($blocks as $block)
			{
				$shortname = $block->definition->shortname;
				if (in_array($shortname, $types))
				{
					$count++;
				}
			}
			return $count;
		}
		else
		{
			return count($blocks);
		}
	}

	// Given the root $tagdata object and the current $context, do the correct
	// replacements.
	protected function _renderBlockSections($tagdata, $context, $channelRow)
	{
		$foundsections = $this->EE->api_channel_fields->get_pair_field(
			$tagdata,
			$context->getShortname(),
			'');

		$output = '';

		//
		// There can be multiple sections.
		//
		// {block-field}
		//   {simple}
		//    <p>{content}</p>
		//   {/simple}
		//
		//   {simple}
		//   <div>Why would anybody do this?</div>
		//   {/simple}
		// {/block-field}
		//
		// So we need to run the process for each section.
		//
		foreach ($foundsections as $foundsection)
		{
			$interiortagdata = $foundsection[1];
			$output .= $this->_renderBlockSection(
				$interiortagdata,
				$context,
				$channelRow);
		}

		return $output;
	}

	protected function buildRelationshipParser($block, $tagdata)
	{
		$this->EE->load->library('relationships_parser');
		$channel = $this->EE->session->cache('mod_channel', 'active');

		$relationships = array();

		foreach ($block->atoms as $shortname => $atom)
		{
			$atomDefinition = $atom->definition;
			if ($atomDefinition->type == 'relationship')
			{
				$relationships[$atomDefinition->shortname] = $atomDefinition->id;
			}
		}

		try
		{
			if (!empty($relationships))
			{
				$relationshipParser = $this->EE->relationships_parser->create(
					(isset($channel->rfields[config_item('site_id')]) ? $channel->rfields[config_item('site_id')] : array()),
					array($block->id), // Um, only gonna parse this one?
					$tagdata,
					$relationships, // field_name => field_id
					$this->_fieldId
				);
			}
			else
			{
				$relationshipParser = NULL;
			}
		}
		catch (EE_Relationship_exception $e)
		{
			$relationshipParser = NULL;
		}

		return $relationshipParser;
	}

	protected function parseRelationships($block, $tagdata)
	{
		$relationshipParser = $this->buildRelationshipParser($block, $tagdata);

		$channel = $this->EE->session->cache('mod_channel', 'active');

		$rowId = $block->id;

		if ($relationshipParser)
		{
			try
			{
				$tagdata = $relationshipParser->parse($rowId, $tagdata, $channel);
			}
			catch (EE_Relationship_exception $e)
			{
				$this->EE->TMPL->log_item($e->getMessage());
			}
		}

		return $tagdata;
	}

	protected function _renderBlockSection($tagdata, $context, $channelRow)
	{
		$field_name = ''; // It's just nothing. Period.
		$entryId = $channelRow['entry_id'];

		$block = $context->getCurrentBlock();

		$tagdata = $this->parseRelationships($block, $tagdata);

		$tagdata = $this->_parseConditionals($tagdata, $context);
		$grid_row = $tagdata;

		// Get the special blocks variables and prepare to replace them.
		$blocksVariables = $this->getContextVariables($context);

		// Gather the variables to parse
		if ( ! preg_match_all(
				"/".LD.'?[^\/]((?:(?:'.preg_quote($field_name).'):?)+)\b([^}{]*)?'.RD."/",
				$tagdata,
				$matches,
				PREG_SET_ORDER)
			)
		{
			return $tagdata;
		}

		foreach ($matches as $match)
		{
			// Get tag name, modifier and params for this tag
			$field = $this->EE->api_channel_fields->get_single_field(
				$match[2],
				$field_name . ':');

			// Get any field pairs
			$pchunks = $this->EE->api_channel_fields->get_pair_field(
				$tagdata,
				$field['field_name'],
				'' // No prefixes required.
				);

			// Work through field pairs first
			foreach ($pchunks as $chk_data)
			{
				list($modifier, $content, $params, $chunk) = $chk_data;

				if ( ! isset($block->atoms[$field['field_name']])) {
					continue;
				}

				$atom = $block->atoms[$field['field_name']];
				// Prepend the column ID with "blocks_" so it doesn't collide
				// with any real grid columns.
				$columnid = 'col_id_' .
					$this->_prefix .
					'_' .
					$atom->definition->id;
				$channelRow[$columnid] = $atom->value;

				$replace_data = $this->replaceTag(
					$atom->definition,
					$this->_fieldId,
					$entryId,
					$block->id,
					array(
						'modifier'  => $modifier,
						'params'    => $params),
					$channelRow,
					$content);

				// Replace tag pair
				$grid_row = str_replace($chunk, $replace_data, $grid_row);
			}

			// Now handle any Blocks-specific variables.
			if (isset($blocksVariables[$match[2]]))
			{
				$replace_data = $blocksVariables[$match[2]];
			}

			// Now handle any single variables
			else if (isset($block->atoms[$field['field_name']]) &&
				strpos($grid_row, $match[0]) !== FALSE)
			{
				$atom = $block->atoms[$field['field_name']];
				$columnid = 'col_id_' .
					$this->_prefix .
					'_' .
					$atom->definition->id;
				$channelRow[$columnid] = $atom->value;

				$replace_data = $this->replaceTag(
					$atom->definition,
					$this->_fieldId,
					$entryId,
					$block->id,
					$field,
					$channelRow);
			}

			// Check to see if this is a field in the table for
			// this field, e.g. row_id

			// TODO: What's $row? What should we do with it?
			elseif (isset($row[$match[2]]))
			{
				$replace_data = $row[$match[2]];
			}
			else
			{
				$replace_data = $match[0];
			}

			// Finally, do the replacement
			$grid_row = str_replace(
				$match[0],
				$replace_data,
				$grid_row);
		}

		return $grid_row;
	}

	protected function _parseConditionals($tagdata, $context)
	{
		// Compile conditional vars
		$cond = array();

		$cond = array_merge($cond, $this->getContextVariables($context));

		$block = $context->getCurrentBlock();

		// Map column names to their values in the DB
		foreach ($block->atoms as $atom)
		{
			$cond[$atom->definition->shortname] = $atom->value;
		}

		$tagdata = $this->EE->functions->prep_conditionals($tagdata, $cond);

		return $tagdata;
	}

	protected function getContextVariables($context)
	{
		$vars = array();

		// Should all of this blocks:code go into the context?
		$vars['blocks:shortname'] = $context->getShortname();
		$vars['blocks:index'] = $context->getIndex();
		$vars['blocks:count'] = $context->getCount();
		$vars['blocks:total_blocks'] = $context->getTotal();
		$vars['blocks:total_rows'] = $context->getTotal();
		$vars['blocks:index:of:type'] = $context->getIndexOfType();
		$vars['blocks:count:of:type'] = $context->getCountOfType();
		$vars['blocks:total_blocks:of:type'] = $context->getTotalOfType();
		$vars['blocks:total_rows:of:type'] = $context->getTotalOfType();
		$vars['blocks:previous:shortname'] = '';
		$vars['blocks:next:shortname'] = '';
		$previousContext = $context->getPreviousContext();
		if (!is_null($previousContext))
		{
			$vars['blocks:previous:shortname'] = $previousContext->getShortname();
		}
		$nextContext = $context->getNextContext();
		if (!is_null($nextContext))
		{
			$vars['blocks:next:shortname'] = $nextContext->getShortname();
		}

		return $vars;
	}

	protected function replaceTag(
		$atomDefinition,
		$fieldId,
		$entryId,
		$blockId,
		$field,
		$data,
		$content = FALSE)
	{
		$colId = $this->_prefix . '_' . $atomDefinition->id;

		$fieldtype = $this->_ftManager->instantiateFieldtype(
			$atomDefinition,
			null,
			$blockId,
			$fieldId,
			$entryId);

		// Return the raw data if no fieldtype found
		if ( ! $fieldtype)
		{
			return $this->EE->typography->parse_type(
				$this->EE->functions->encode_ee_tags($data['col_id_' . $colId]));
		}

		// Determine the replace function to call based on presence of modifier
		$modifier = $field['modifier'];
		$parse_fnc = ($modifier) ? 'replace_' . $modifier : 'replace_tag';

		$fieldtype->initialize(array(
			'row' => $data,
			'content_id' => $entryId
		));

		// Add row ID to settings array
		$fieldtype->setSetting('grid_row_id', $blockId);
		$fieldtype->setSetting('blocks_block_id', $blockId);

		$data = $fieldtype->preProcess($data['col_id_' . $colId]);
		$result = $fieldtype->replace(
			$modifier ? $modifier : NULL,
			$data,
			$field['params'],
			$content);
		return $result;
	}
}
