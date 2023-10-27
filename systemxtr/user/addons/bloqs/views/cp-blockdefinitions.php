<?php

use EllisLab\ExpressionEngine\Library\CP\Table;

$table = ee('CP/Table', array('sortable' => false));
$table->setNoResultsText('bloqs_definitions_no_results');

$tbl_cols = array(
	'bloqs_blockdefinitions_name',
	'bloqs_blockdefinitions_shortname',
	'bloqs_blockdefinitions_manage' => array('type' => Table::COL_TOOLBAR),
);
$table->setColumns($tbl_cols);


$rows = array();

foreach( $blockDefinitions as $blockDefinition )
{
	$rows[] = array(
		array(
			'href' => ee('CP/URL')->make('addons/settings/bloqs/blockdefinition', array('blockdefinition' => $blockDefinition->id))->compile(),
			'content' => $blockDefinition->name,
		),
		array(
			'content' => $blockDefinition->shortname,
		),
		array(
			'toolbar_items' => array(
				'edit' => array(
					'href' => ee('CP/URL')->make('addons/settings/bloqs/blockdefinition', array('blockdefinition' => $blockDefinition->id))->compile(),
					'title' => lang('bloqs_blockdefinitions_edit'),
				),
				'remove' => array(
					'href'    => '',
					'title'   => lang('bloqs_blockdefinitions_delete'),
					'class'   => 'm-link',
					'rel'     => 'modal-confirm-remove',
					'data-confirm' => 'Block Name: '.$blockDefinition->name,
					'data-blockdefinition' => $blockDefinition->id,
				),
			)
		),
	);

	ee('CP/Modal')->addModal('remove',
		$this->make('ee:_shared/modal_confirm_remove')->render(array(
			'name'     => 'modal-confirm-remove',
			'form_url' => $confirmdelete_url,
			'hidden' => array('blockdefinition' => $blockDefinition->id),
			'checklist' => array(array('kind' => 'Block Name', 'desc' => $blockDefinition->name))
		))
	);

}

$table->setData($rows);

?>

<div class="box bloqs">
	<div class="tbl-ctrls">
		<fieldset class="tbl-search right">
			<a class="btn tn action" href="<?=$blockdefinition_url;?>"><?=lang('bloqs_blockdefinitions_add')?></a>
		</fieldset>
		<h1><?= lang('bloqs_blockdefinitions_title') ?></h1>
		<?=ee('CP/Alert')->getAllInlines()?>
		<?php $this->embed('ee:_shared/table', $table->viewData()); ?>
	</div>
</div>

