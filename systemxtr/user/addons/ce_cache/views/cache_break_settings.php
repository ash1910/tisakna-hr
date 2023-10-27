<div class="ce-cache">
	<div class="box">
		<h1><?php echo $title; ?></h1>
		<div class="md-wrap">
<?php
	//open form
	echo form_open($action_url, '');

	//channel id
	echo form_hidden('channel_id', $channel_id);
?>
	<div class="ce-cache-breaking" data-ce-cache-break-setting data-var="CE_CACHE_BREAK_SETTINGS">
		<?php /* ---------- Intro HTML ---------- */ ?>
		<?php echo ( $channel_id != 0 ) ? sprintf( lang('ce_cache_break_intro_html'), $channel_title ) : lang('ce_cache_break_intro_any') ?>

		<?php /* ---------- Fallback HTML ---------- */ ?>
		<template v-if="false">
			<?php echo lang('ce_cache_break_not_loaded_html'); ?>
		</template>

		<?php /* ---------- Errors ---------- */ ?>
		<?php if (count($errors)): ?>
		<div class="ce-cache-errors-holder">
			<?php echo lang((count($errors) > 1) ? 'ce_cache_break_errors_html' : 'ce_cache_break_error_html') ?>
			<ul class="ce-cache-errors">
				<?php foreach($errors as $error): ?>
					<li><?php echo $error ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>

		<?php /* ---------- Variables and Tags ---------- */ ?>
		<div class="ce-cache-section" v-cloak>
			<?php /* ---------- Variables  ---------- */ ?>
			<div class="ce-cache-variables-holder">
				<?php echo lang('ce_cache_breaking_variables_html'); ?>
			</div><!-- .ce-cache-listeners-holder -->

			<div class="ce-cache-tags-and-paths-holder">
				<?php /* ---------- Tags ---------- */ ?>
				<div class="ce-cache-field-holder ce-cache-tags-holder">
					<h3 class="ce-cache-tags-after"><?php echo lang('ce_cache_tags') ?></h3>
					<?php echo lang('ce_cache_breaking_tags_instructions_html'); ?>
					<tags id="settings-tags" name="tags" v-model="tags" add-text="<?php echo lang('ce_cache_tag_add'); ?>"></tags>
					<div class="ce-cache-examples">
						<p v-if="! showTagExamples"><a href="#" @click.prevent="toggleTagExamples"><?php echo lang('ce_cache_show_examples'); ?></a></p>
						<template v-if="showTagExamples">
							<p><a href="#" @click.prevent="toggleTagExamples"><?php echo lang('ce_cache_hide_examples'); ?></a></p>
							<div class="alert inline warn">
								<?php echo lang('ce_cache_breaking_tags_examples_html'); ?>
							</div>
						</template>
					</div>
				</div><!-- .ce-cache-field-holder -->

				<?php /* ---------- Paths ---------- */ ?>
				<div class="ce-cache-field-holder ce-cache-paths-holder">
					<h3 class="ce-cache-paths-after"><?php echo lang('ce_cache_paths') ?></h3>
					<?php echo lang('ce_cache_breaking_items_instructions_html'); ?>
					<div class="ce-cache-path" v-for="(item, index) in items">
						<div class="ce-cache-path-column ce-cache-path-column-input">
							<item name="items[]" v-model="items[index]"></item>
						</div>
						<div class="ce-cache-path-column">
							<a href="#" class="ce-cache-remove-path" @click.prevent="removeItem(index)"><?php echo lang('ce_cache_remove') ?></a>
						</div>
					</div><!-- .ce-cache-path -->
					<a href="#" class="ce-cache-add-path" @click.prevent="addItem"><?php echo lang('ce_cache_add') ?></a>
					<div class="ce-cache-examples">
						<p v-if="! showPathExamples"><a href="#" @click.prevent="togglePathExamples"><?php echo lang('ce_cache_show_examples'); ?></a></p>
						<template v-if="showPathExamples">
							<p><a href="#" @click.prevent="togglePathExamples"><?php echo lang('ce_cache_hide_examples'); ?></a></p>
							<div class="alert inline warn">
								<?php echo lang('ce_cache_breaking_paths_examples_html'); ?>
							</div>
						</template>
					</div>
				</div><!-- .ce-cache-paths-holder -->
			</div><!-- .ce-cache-tags-and-paths-holder -->
		</div><!-- .ce-cache-section -->

		<?php /* ---------- Refresh? ---------- */ ?>
		<div class="ce-cache-section" v-cloak>

			<h3 class="ce-cache-refresh-after"><?php echo lang('ce_cache_refreshing') ?></h3>
			<?php echo lang('ce_cache_refresh_cached_items_intro_html') ?>

			<div class="ce-cache-checkbox-with-label">
				<input type="checkbox" name="refresh" id="ce_cache_refresh" :value="refresh ? 'y' : 'n'" v-model="refresh">
				<label for="ce_cache_refresh"><?php echo lang('ce_cache_refresh_cached_items_question') ?></label>
			</div>
			<div id="ce_cache_refresh_holder" v-show="refresh">
				<?php
				echo lang('ce_cache_refresh_cached_items_instructions_html');
				?>
				<select name="ce_cache_refresh_time" id="ce_cache_refresh_time" v-model="refresh_time">
					<option value="15"><?php echo sprintf(lang('ce_cache_refresh_delay'), 15); ?></option>
					<option value="14"><?php echo sprintf(lang('ce_cache_refresh_delay'), 14); ?></option>
					<option value="13"><?php echo sprintf(lang('ce_cache_refresh_delay'), 13); ?></option>
					<option value="12"><?php echo sprintf(lang('ce_cache_refresh_delay'), 12); ?></option>
					<option value="11"><?php echo sprintf(lang('ce_cache_refresh_delay'), 11); ?></option>
					<option value="10"><?php echo sprintf(lang('ce_cache_refresh_delay'), 10); ?></option>
					<option value="9"><?php echo sprintf(lang('ce_cache_refresh_delay'), 9); ?></option>
					<option value="8"><?php echo sprintf(lang('ce_cache_refresh_delay'), 8); ?></option>
					<option value="7"><?php echo sprintf(lang('ce_cache_refresh_delay'), 7); ?></option>
					<option value="6"><?php echo sprintf(lang('ce_cache_refresh_delay'), 6); ?></option>
					<option value="5"><?php echo sprintf(lang('ce_cache_refresh_delay'), 5); ?></option>
					<option value="4"><?php echo sprintf(lang('ce_cache_refresh_delay'), 4); ?></option>
					<option value="3"><?php echo sprintf(lang('ce_cache_refresh_delay'), 3); ?></option>
					<option value="2"><?php echo sprintf(lang('ce_cache_refresh_delay'), 2); ?></option>
					<option value="1"><?php echo sprintf(lang('ce_cache_refresh_delay'), 1); ?></option>
					<option value="0"><?php echo lang('ce_cache_refresh_all_at_once'); ?></option>
				</select>
			</div><!-- #ce_cache_refresh_holder -->
		</div><!-- .ce-cache-section -->

		<?php /* ---------- Submit Button ---------- */ ?>
		<p v-cloak><?php
			//submit
			echo form_submit(array('name'=>'submit', 'value'=>lang('ce_cache_save_settings'), 'class'=>'btn action') );
		?></p>
	</div><!-- .ce-cache-breaking -->
	<script type="text/x-template" id="tags-template">
		<input type="text" :name="name" :id="id" v-model="value" />
	</script>

	<script type="text/x-template" id="item-template">
		<div class="ce-cache-path-input">
			<select @change="typeChange" ref="typeSelect">
				<option value="local" :selected="(itemType === 'local')"><?php echo lang('ce_cache_path_local') ?></option>
				<option value="global" :selected="(itemType === 'global')"><?php echo lang('ce_cache_path_global') ?></option>
				<option value="static" :selected="(itemType === 'static')"><?php echo lang('ce_cache_path_static') ?></option>
				<option value="any" :selected="(itemType === 'any')"><?php echo lang('ce_cache_path_any') ?></option>
			</select>
			<input type="text" v-model="itemPath" @keyup.prevent="updateInput" />
			<input type="hidden" :name="name" v-model="value" data-blah="blah" />
		</div>
	</script>
<?php
	//close form
	echo form_close();
?>
		</div><!-- .md-wrap -->
	</div><!-- .box -->
</div><!-- .ce-cache -->