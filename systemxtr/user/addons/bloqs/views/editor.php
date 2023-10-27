<!-- -->
<div class="blocksft grid-publish grid-input-form" data-field-id="<?= $fieldid ?>">
	<div id="field_id_<?= $fieldid ?>" class="blocksft-wrapper grid_field_container"><!-- start: blocksft-wrapper -->
		<!-- Existing Bloq Data -->
		<div class="blocksft-blocks"><!-- start: blocksft-blocks -->
			<?php foreach ($bloqs as $blockdata): ?>
				<div class="blocksft-block<?php if ($blockdata['block']->deleted == 'true'): ?> deleted<?php endif; ?>" data-blocktype="<?= $blockdata['block']->definition->shortname ?>" data-blockvisibility="<?= $blockdata['visibility'] ?>">
					<input type="hidden" name="<?= $blockdata['fieldnames']->id ?>" value="<?= $blockdata['block']->id ?>">
					<input type="hidden" name="<?= $blockdata['fieldnames']->definitionId ?>" value="<?= $blockdata['block']->definition->id ?>">
					<input type="hidden" data-order-field name="<?= $blockdata['fieldnames']->order ?>" value="<?= $blockdata['block']->order ?>">

					<?php if (isset($blockdata['fieldnames']->deleted)): ?>
						<input type="hidden" data-deleted-field name="<?= $blockdata['fieldnames']->deleted ?>" value="<?= $blockdata['block']->deleted ?>">
					<?php endif; ?>

					<div class="blocksft-header"><!-- start: blocksft-header -->
						<span class="blocksft-block-handle">::</span>
						<button type="button" class="blocksft-contextbutton" js-context>Context</button>
						<div class="blocksft-contextmenu" style="display:none;">
							<button type="button" class="expandbutton" js-expand>Expand</button>
							<button type="button" class="collapsebutton" js-collapse>Collapse</button>
							<div class="multistep">
								<div class="multistep-container">
									<button type="button" class="step1 warning" js-nextstep>Remove&hellip;</button>
									<div class="step2">Remove? <button type="button" class="warning" js-remove>Remove</button> <button type="button" js-previousstep>Cancel</button></div>
								</div>
							</div>
							<hr>

							<button type="button" js-expandall>Expand All</button>
							<button type="button" js-collapseall>Collapse All</button>
							<hr>

							<div class="sectionheader">Insert above</div>
							<?php foreach ($blockdefinitions as $blockdefinition): ?>
							<button js-newblock type="button" data-template="<?= $blockdefinition['templateid'] ?>" data-location="above"><?= $blockdefinition['name'] ?></button>
							<?php endforeach; ?>
							<hr>

							<div class="sectionheader">Insert below</div>
							<?php foreach ($blockdefinitions as $blockdefinition): ?>
							<button js-newblock type="button" data-template="<?= $blockdefinition['templateid'] ?>" data-location="below"><?= $blockdefinition['name'] ?></button>
							<?php endforeach; ?>
							<hr>
						</div>
						<div class="blocksft-title">
							<span class="title"><?= $blockdata['block']->definition->name ?></span>
							<span class="summary" js-summary></span>
						</div>
					</div><!-- end: blocksft-header -->

					<div class="blocksft-content"><!-- start: blocksft-content -->
						<?php if (!is_null($blockdata['block']->definition->instructions) && $blockdata['block']->definition->instructions != ''): ?>
							<div class="blocksft-instructions"><?= $blockdata['block']->definition->instructions ?></div>
						<?php endif; ?>

						<div class="blocksft-atoms"><!-- start: blocksft-atoms -->
							<?php foreach ($blockdata['controls'] as $control): ?>
							<?php
								$blocksft_atom_class = 'blocksft-atom';
								$blocksft_atom_class .= (isset($control['atom']->error)) ?  ' invalid' : '';
								$blocksft_atom_class .= (isset($control['atom']->definition->settings['col_required']) && $control['atom']->definition->settings['col_required'] == 'y') ? ' required' : '';
							?>

							<div class="<?=$blocksft_atom_class?>" data-fieldtype="<?= $control['atom']->definition->type ?>" data-column-id="<?= $control['atom']->definition->id  ?>" data-row-id="<?= $blockdata['block']->id  ?>">
								<h3 class="blocksft-atom-name"><?= $control['atom']->definition->name ?></h3>

								<?php if (!is_null($control['atom']->definition->instructions) && $control['atom']->definition->instructions != ''): ?>
									<label class="blocksft-atom-instructions"><?= $control['atom']->definition->instructions ?></label>
								<?php endif; ?>

								<div class="blocksft-atomcontainer grid-<?= $control['atom']->definition->type ?>">
									<?php echo $control['html']; ?>
									<?php if (isset($control['atom']->error)): ?>
										<em class="blocks-ee-form-error-message"><?= $control['atom']->error ?></em>
									<?php endif; ?>
								</div>
							</div>
							<?php endforeach; ?>
						</div><!-- end: blocksft-atoms -->
					</div><!-- end: blocksft-content -->
				</div><!-- end: blocksft-block -->
			<?php endforeach; ?>
		</div><!-- end: blocksft-blocks -->
		<!-- End: Existing Bloq Data -->

		<!-- Add new Bloq Data -->
		<div class="blocksft-new"><!-- start: blocksft-new -->
			Add:
			<?php foreach ($blockdefinitions as $blockdefinition): ?>
				<a href="#" class="btn action" js-newblock data-template="<?= $blockdefinition['templateid'] ?>" data-location="bottom"><?= $blockdefinition['name'] ?></a>
			<?php endforeach; ?>
		</div><!-- end: blocksft-new -->
		<!-- End: Add new Bloq Data -->
	</div><!-- end: blocksft-wrapper -->

	<!-- Templates for new blocks -->
	<?php foreach( $blockdefinitions as $blockdefinition ): ?>
		<div id="<?= $blockdefinition['templateid'] ?>" style="display:none;" class="">
	    <div class="blocksft-block" data-blocktype="<?= $blockdefinition['shortname'] ?>" data-blockvisibility="expanded">
	      <input type="hidden" name="<?= $blockdefinition['fieldnames']->blockdefinitionid ?>" value="<?= $blockdefinition['blockdefinitionid'] ?>">
	      <input type="hidden" data-order-field name="<?= $blockdefinition['fieldnames']->order ?>" value="0">

	      <div class="blocksft-header"><!-- start: blocksft-header -->
	        <span class="blocksft-block-handle">::</span>
	        <button type="button" class="blocksft-contextbutton" js-context>Context</button>
	        <div class="blocksft-contextmenu" style="display:none;">
	          <button type="button" class="expandbutton" js-expand>Expand</button>
	          <button type="button" class="collapsebutton" js-collapse>Collapse</button>
	          <div class="multistep">
	              <div class="multistep-container">
	                  <button type="button" class="step1 warning" js-nextstep>Remove&hellip;</button>
	                  <div class="step2">Remove? <button type="button" class="warning" js-remove>Remove</button> <button type="button" js-previousstep>Cancel</button></div>
	              </div>
	          </div>
	          <hr>

	          <button type="button" js-expandall>Expand All</button>
	          <button type="button" js-collapseall>Collapse All</button>
	          <hr>

	          <div class="sectionheader">Insert above</div>
	          <?php foreach ($blockdefinitions as $blockdefinition2): ?>
	          <button js-newblock type="button" data-template="<?= $blockdefinition2['templateid'] ?>" data-location="above"><?= $blockdefinition2['name'] ?></button>
	          <?php endforeach; ?>
	          <hr>

	          <div class="sectionheader">Insert below</div>
	          <?php foreach ($blockdefinitions as $blockdefinition2): ?>
	          <button js-newblock type="button" data-template="<?= $blockdefinition2['templateid'] ?>" data-location="below"><?= $blockdefinition2['name'] ?></button>
	          <?php endforeach; ?>
	          <hr>
	        </div>
	        <div class="blocksft-title">
	          <span class="title"><?= $blockdefinition['name'] ?></span>
	          <span class="summary" js-summary></span>
	        </div>
	      </div><!-- end: blocksft-header -->

	      <div class="blocksft-content"><!-- start: blocksft-content -->
	        <?php if (!is_null($blockdefinition['instructions']) && $blockdefinition['instructions'] != ''): ?>
	          <div class="blocksft-instructions"><?= $blockdefinition['instructions'] ?></div>
	        <?php endif; ?>

	        <div class="blocksft-atoms"><!-- start: blocksft-atoms -->
	          <?php foreach ($blockdefinition['controls'] as $control): ?>
							<?php
								$blocksft_atom_class = 'blocksft-atom';
								$blocksft_atom_class .= ' grid-'.$control['atom']->type;
								$blocksft_atom_class .= (isset($control['atom']->settings['col_required']) && $control['atom']->settings['col_required'] == 'y') ? ' required' : '';
							?>
	          <div class="<?=$blocksft_atom_class?>" data-fieldtype="<?= $control['atom']->type ?>" data-column-id="<?= $control['atom']->id ?>">
	            <h3 class="blocksft-atom-name"><?= $control['atom']->name ?></h3>

	            <?php if (!is_null($control['atom']->instructions) && $control['atom']->instructions != ''): ?>
	              <label class="blocksft-atom-instructions"><?= $control['atom']->instructions ?></label>
	            <?php endif; ?>

							<div class="blocksft-atomcontainer">
              	<?php echo $control['html']; ?>
           	 	</div>
	          </div>
	          <?php endforeach; ?>
	        </div><!-- end: blocksft-atoms -->
	      </div><!-- end: blocksft-content -->
	    </div><!-- end: blocksft-block -->
		</div>
	<?php endforeach; ?>

</div><!-- end: blocksft grid-publish -->









