<div id="low-lexicon" class="box">

	<h1><?=lang('lexicon')?></h1>

	<div class="low-tabs col w-4" data-names="span">
		<div class="low-tabs-pages">

			<div class="low-tab<?php if ($total_words): ?> active<?php endif; ?>">
				<span><?=lang('find_words')?></span>
			</div>
			<div class="low-tab<?php if ( ! $total_words): ?> active<?php endif; ?>">
				<span><?=lang('add_words')?></span>
			</div>

			<?=form_open()?>
				<fieldset>
					<input type="text" name="<?=$total_words?'find':'add'?>" placeholder="<?=lang('word_placeholder')?>" autocomplete="off">
					<select name="language">
					<?php foreach ($languages as $val => $key): ?>
						<option value="<?=$key?>"<?php if ($key == $default): ?>selected<?php endif; ?>>
							<?=htmlspecialchars($val)?>
							<!-- <?php if (isset($counts[$key])): ?>&ndash; <?=number_format($counts[$key])?><?php endif; ?>-->
						</option>
					<?php endforeach; ?>
					</select>
				</fieldset>
			</form>

		</div> <!-- .low-tabs-pages -->
	</div> <!-- .low-sidebar.low-tabs -->

	<div class="col w-12 last">
		<div class="txt-wrap">
			<p class="low-status"><?=$status?></p>
			<div class="low-dynamic-content"></div>
		</div>
	</div>

</div>