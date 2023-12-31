<div class="box">
	<div class="tbl-ctrls">
		<h1><?=$form_data['form_label']?></h1>
		<div id="composer_wrapper" class="solspace-wrapper">

			<!-- start side nav -->
			<div id="composer_side_bar">

			<?php if ( ! empty($available_templates)):?>
					<select id="composer_template_select" name="composer_template" class="chzn_select_no_search">
						<option value="0"><?=lang('composer_template')?></option>
				<?php foreach ($available_templates as $template_id => $template_label):?>
						<option <?php
							if ($template_id == $form_data['template_id']):
								?> selected="selected"<?php endif;
						?>value="<?=$template_id?>"><?=$template_label?></option>
				<?php endforeach;?>
					</select>
			<?php else: ?>
				<input type="hidden" id="composer_template_select" name="composer_template" value="0" />
			<?php endif;?>

				<!-- start insert elements -->
				<div id="composer_elements">
					<div class="header">
						<!-- div class="freeform_info_button">
							<div class="tooltip" style="display:none;">
								<?=lang('composer_instructions')?>
							</div>
						</div -->
						<h4><?=lang('insert')?></h4>
						<div class="form_subtext subtext"><?=lang('click_or_drag_to_add')?></div>
					</div>
					<div class="content">
						<?=lang('row')?>:
						<div class="freeform_ui_element freeform_ui_button row_button single_row_button"
							id="single_row_button">
							<span class="first">&nbsp;</span>
						</div>
						<div class="freeform_ui_element freeform_ui_button row_button double_row_button"
							id="double_row_button">
							<span class="first">&nbsp;</span>
						</div>
						<div class="freeform_ui_element freeform_ui_button row_button triple_row_button"
							id="triple_row_button">
							<span class="first">&nbsp;</span>
							<span class="second">&nbsp;</span>
						</div>
						<div class="freeform_ui_element freeform_ui_button row_button quadruple_row_button"
							id="quadruple_row_button">
							<span class="first">&nbsp;</span>
							<span class="second">&nbsp;</span>
							<span class="third">&nbsp;</span>
						</div>
					</div>
					<div id="insert_elements" class="content">
						<div id="title_button" data-element-id="title"
							 class="insert_element freeform_ui_element freeform_ui_button">
							<?=lang('title')?>
						</div>
						<div id="paragraph_button" data-element-id="paragraph"
							 class="insert_element freeform_ui_element freeform_ui_button">
							<?=lang('paragraph')?>
						</div>
						<div id="page_break_button" data-element-id="page_break"
							 class="freeform_ui_element freeform_ui_button page_break">
							<?=lang('page_break')?>
						</div>
						<div id="captcha_button" data-element-id="captcha"
							 class="insert_element freeform_ui_element freeform_ui_button">
							<?=lang('captcha')?>
						</div>
						<div id="dynamic_recipients_button" data-element-id="dynamic_recipients"
							 class="insert_element freeform_ui_element freeform_ui_button">
							<?=lang('dynamic_recipients')?>
						</div>
						<div id="user_recipients_button" data-element-id="user_recipients"
							 class="insert_element freeform_ui_element freeform_ui_button">
							<?=lang('user_recipients')?>
						</div>
						<div id="submit_button_button" data-element-id="submit"
							 class="insert_element freeform_ui_element freeform_ui_button">
							<?=lang('submit_button')?>
						</div>
						<div id="submit_previous_button_button" data-element-id="submit_previous"
							 class="insert_element freeform_ui_element freeform_ui_button last">
							<?=lang('submit_previous_button')?>
						</div>
					</div>
					<div class="content last">
						<label>
							<input
								type="checkbox"
								id="sticky_controls"
								name="sticky_controls"
								value="y"
								checked="checked"/>
							&nbsp;
							<?=lang('sticky_controls')?>
						</label>
					</div>
				</div>
				<!-- end insert elements -->

				<!-- start available fields -->
				<div id="available_fields">
					<div class="header">
						<button id="new_field_button" class="btn"><?=lang('new_field')?></button>
						<h4><?=lang('fields')?></h4>
						<div class="form_subtext subtext"><?=lang('click_or_drag_to_add')?></div>
					</div>
					<div class="content dark">
						<div>
							<input	type="text"
									name="search_fields"
									id="search_fields"
									placeholder="Search Fields" />
						</div>
						<div id="search_clear_button" class="search_clear_button"></div>
					</div>
					<div id="field_list" class="content field_list field_list_full uses_header last">
				<?php foreach($available_fields as $field_name => $field_data):?>
						<div class="field_tag" data-field-name="<?=$field_data['field_name']?>">
							<div class="freeform_edit_button"></div>
							<span class="field_label"><?=$field_data['field_label']?></span>
						</div>
				<?php endforeach;?>
					</div>
				</div>
				<!-- end available fields -->
			</div>
			<!-- end side nav -->

			<!-- start composer -->
			<div id="composer">
				<table cellpadding="0" cellspacing="0">
					<?php /*
						Cheap width trick? Yes. <div>s were not working here
						because there was nothing they could do to prevent
						breaking down to the next line when they where floated
						or inline-blocked and the content pushed,
						because they could still break layout with their width.
						This way nothing can be larger than its set width and we
						can specify overflow and handling with an inner wrapper.
						Such is HTML :/.

						Why 13 columns? 12 is the least common multiple between 4,3,2,1
						and the 13th is for the row control so its still inside of
						Needed strictly evenly spaced fields.
					*/?>
					<thead>
						<tr>
							<th style="width:8%;"></th>
							<th style="width:8%;"></th>
							<th style="width:8%;"></th>
							<th style="width:8%;"></th>
							<th style="width:8%;"></th>
							<th style="width:8%;"></th>
							<th style="width:8%;"></th>
							<th style="width:8%;"></th>
							<th style="width:8%;"></th>
							<th style="width:8%;"></th>
							<th style="width:8%;"></th>
							<th style="width:8%;"></th>
							<!-- controller row -->
							<th style="width:4%;"></th>
						</tr>
					</thead>
					<tbody id="composer_rows">

					</tbody>
				</table>
			</div>
			<!-- end composer -->
		</div>
		<!-- end composer wrapper -->

		<fieldset class="form-ctrls">
			<div class="bottom_right_submit_block">
				<button id="preview" class="btn"><?=lang('preview')?></button>
				<form id="save_composer" action="<?=$composer_save_url?>" method="post">
					<input type="hidden" name="<?=$csrf_hidden_name?>"	value="<?=$CSRF_TOKEN?>" />
					<input type="hidden" id="composer_save_data" name="composer_data" value="" />
					<input type="hidden" name="preview" value="n" />
					<input type="hidden" id="composer_template_id" name="template_id" value="0" />
					<button id="quicksave" class="btn"><?=lang('quick_save')?></button>
					<button id="save_and_finish" class="btn"><?=lang('save_and_finish')?></button>
				</form>
			</div>

			<div class="bottom_left_submit_block">
				<button id="clear_all"
					 class="btn">
					 <?=lang('clear_all_rows')?>
				</button>
			</div>
		</fieldset>
	</div>
</div>
<!--
	Start UndersoreJS templates
-->

<!--
	This has the widths on each column so that jQUI sortable dragging
	has width attributes to work with when its floating above other things.
	Do not remove them.
-->
<script id="column_template" type="text/html">
	<td style="width:<{= Freeform.fractionToFloat(colspan, 12, 96) }>%;"
		colspan="<{= colspan }>"
	<{ if (typeof notSortable == 'undefined' || notSortable !== false){ }>
		class="connectedSortable"
	<{ } }> >
	<{ if (typeof dataInner !== 'undefined') { }>
			<{= dataInner }>
	<{ } }>
	</td>
</script>


<!-- page break -->
<script id="page_break_template" type="text/html">
	<div class="page_break">
		<div class="freeform_delete_button row_delete"></div>
		<?=lang('page_break')?>
	</div>
</script>

<!-- TOO MANY DIVS ON THE THE DANCEFLOOR! -->
<script id="composer_row_template" type="text/html">
	<tr>
		<{ _.each(data, function(dataInner, i, list) { }>
			<{= columnTemplate }>
		<{ }); }>
		<td class="row_control_holder" style="width:4%;">
			<div class="row_control">
				<div class="freeform_cog_button">
					<div class="control_flyout">
						<div class="flyout_petal"></div>
						<div class="flyout_container">
							<ul>
								<li>
									<a class="row_delete" href="#">
										<?=lang('delete_row_lower')?>
									</a>
								</li>
								<li>
									<a class="row_add_column" href="#">
										<?=lang('add_column')?>
									</a>
								</li>
								<li>
									<a class="row_remove_column" href="#">
										<?=lang('remove_column')?>
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="freeform_drag_button"></div>
			</div>
		</td>
	</tr>
</script>

<!-- template for paragraph inputs, etc -->
<script id="paragraph_template" type="text/html">
	<div class="editable_paragraph">
	<{ if (typeof data !== 'undefined') { }>
		<div class="paragraph_content"><{= data }></div>
		<div class="subfield_instructions bottom"><?=lang('double_click_to_edit')?></div>
	<{ } else { }>
		<?=lang('double_click_to_edit')?>
	<{ } }>
	</div>
</script>

<!-- template for paragraph inputs, etc -->
<script id="edit_paragraph_template" type="text/html">
	<div class="editor ss_clearfix">
		<textarea rows="4"><{ if (typeof data !== 'undefined') { }><{= data }><{ } }></textarea>
		<div style="float:left"><?=$lang_allowed_html_tags?></div>
		<button class="btn paragraph_edit finished"><?=lang('finished')?></button>
	</div>
</script>

<!-- template for dynamic recipients etc -->
<script id="dynrec_template" type="text/html">
	<div class="editable_dynrec">
	<{ if (typeof data !== 'undefined' && typeof jsonData !== 'undefined') { }>
		<{ if (typeof label !== 'undefined') { }>
			<div class="element_label"><{= label }><span class="element_required">*</span></div>
		<{ } }>
		<div data-recipients='<{= jsonData }>'
			 data-type="<{ if (typeof type !== 'undefined'){}><{= type }><{ }}>"
			 data-notification-id="<{ if (typeof notificationId !== 'undefined'){}><{= notificationId }><{ } else {}>0<{ } }>">
			<{ if (typeof type === 'undefined' || type != 'checks') { }>
				<select>
				<{	var counter = 0;
					$.each(data, function(i, item){ }>
						<option value="<{= counter }>"><{= item }></option>
				<{ counter++; }); }>
				</select>
			<{ } else { }>
				<ul>
				<{	var counter = 0;
					$.each(data, function(i, item){ }>
					<li>
						<input type="checkbox" name="<{= counter }>" />
						&nbsp;
						<label>
							<{= item }>
						</label>
					</li>
				<{ counter++; }); }>
				</ul>
			<{ } }>
		</div>
		<div class="subfield_instructions bottom"><?=lang('double_click_to_edit_recipients')?></div>
	<{ } else { }>
		<?=lang('double_click_to_edit_recipients')?>
	<{ } }>
	</div>
</script>

<!-- template for dynamic recipients, etc -->
<script id="edit_dynrec_template" type="text/html">
	<div id="dynrec_editor" class="editor ss_clearfix">
		<div class="inner_wrapper">
			<h4><?=lang('dynrec_output_label')?></h4>
			<p>
				<input	type="text"
						name="dynrec_output_label"
						value="<{ if (typeof label !== 'undefined') { }><{= Freeform.formPrep(label) }><{ } }>" />
			</p>
		</div>
		<div class="inner_wrapper">
			<h4><?=lang('dynrec_output_type')?></h4>
			<p>
				<label>
					<input type="radio" name="dynrec_type" value="select"
					<{ if (typeof type === 'undefined' || type != 'checks') { }>
						checked="checked"
					<{ } }>/>
					&nbsp;
					<?=lang('select_dropdown')?>
				</label>
				&nbsp;
				&nbsp;
				<label>
					<input type="radio" name="dynrec_type" value="checks"
					<{ if (typeof type !== 'undefined' && type == 'checks') { }>
						checked="checked"
					<{ } }>/>
					&nbsp;
					<?=lang('checkbox_group')?>
				</label>
			</p>
		</div>
		<div class="inner_wrapper">
			<h4><?=lang('notification_template')?></h4>
			<p>
				<select id="dynrec_notification_id" name="dynrec_notification_id"
							class="chzn_select">
			<?php foreach( $notifications as $n_id => $n_name): ?>
					<option <{ if (typeof notificationId !== 'undefined' && notificationId == '<?=$n_id?>') { }>
						selected="selected"
					<{ } }> value="<?=$n_id?>"><?=$n_name?></option>
			<?php endforeach;?>
				</select>
			</p>
		</div>
		<div class="type_value_label_holder inner_wrapper">
			<h4><?=lang('dynamic_recipients')?></h4>
			<p><?=lang('dynrec_edit_instructions')?></p>
			<div class="value_label_header">
				<span class="value_label_header_sub">
					<?=lang('email')?>
				</span>
				<span class="value_label_header_sub">
					<?=lang('name')?>
				</span>
			</div>
		<{	var counter = 0;
			data = (typeof data !== 'undefined') ? data : {};
			$.each(data, function(key, value){ }>
			<div class="value_label_holder_input">
				<input 	type="text"
						name="list_value_holder_input[<{= counter }>]"
						value="<{= Freeform.formPrep(key) }>"/>
				<input 	type="text"
						name="list_label_holder_input[<{= counter }>]"
						value="<{= Freeform.formPrep(value) }>"/>
				<div class="freeform_delete_button"></div>
			</div>
		<{ counter++; });}>
			<div class="value_label_holder_input">
				<input 	type="text"
						name="list_value_holder_input[<{= counter }>]" />
				<input 	type="text"
						name="list_label_holder_input[<{= counter }>]" />
				<div class="freeform_delete_button"></div>
			</div>
		</div>
		<button class="btn dynrec_edit finished"><?=lang('finished')?></button>
	</div>
</script>


<!-- captcha button output -->
<script id="captcha_template" type="text/html">
	<img src="<?=$captcha_dummy_url?>" />
</script>

<!-- submit button output -->
<script id="submit_button_template" type="text/html">
	<div class="editable_submit">
	<button>
	<{ if (typeof data !== 'undefined') { }>
		<{= data }>
	<{ } else { }>
		<?=lang('submit')?>
	<{ } }>
	</button>
	<div class="subfield_instructions"><?=lang('double_click_to_edit')?></div>
	</div>
</script>

<!-- template for paragraph inputs, etc -->
<script id="edit_submit_button_template" type="text/html">
	<div class="editor ss_clearfix">
		<input	type="text" name="submit_edit"
				value="<{ if (typeof data !== 'undefined') { }><{= data }><{ } }>" />
		<button class="btn submit_edit finished"><?=lang('finished')?></button>
	</div>
</script>


<!-- submit previous button output -->
<script id="submit_previous_button_template" type="text/html">
	<div class="editable_submit_previous">
	<button>
	<{ if (typeof data !== 'undefined') { }>
		<{= data }>
	<{ } else { }>
		<?=lang('previous')?>
	<{ } }>
	</button>
	<div class="subfield_instructions"><?=lang('double_click_to_edit')?></div>
	</div>
</script>

<!-- template for paragraph inputs, etc -->
<script id="edit_submit_previous_button_template" type="text/html">
	<div class="editor ss_clearfix">
		<input	type="text" name="submit_previous_edit"
				value="<{ if (typeof data !== 'undefined') { }><{= data }><{ } }>" />
		<button class="btn submit_previous_edit finished"><?=lang('finished')?></button>
	</div>
</script>


<!-- submit button output -->
<script id="userrec_template" type="text/html">
	<div class="editable_userrec">
		<div class="element_label">
		<{ if (typeof data !== 'undefined') { }>
			<{= data }>
		<{ } else { }>
			<?=lang('notify_friends')?>
		<{ } }>
		<span class="element_required">*</span>
		</div>
		<div class="inner_element">
			<input type="text" name="recipient_email_user" />
		</div>
		<div class="subfield_instructions"><?=lang('double_click_to_edit')?></div>
	</div>
</script>

<!-- template for paragraph inputs, etc -->
<script id="edit_userrec_template" type="text/html">
	<div class="editor ss_clearfix">
		<input	type="text" name="userrec_edit"
				value="<{ if (typeof data !== 'undefined') { }><{= data }><{ } }>" />
		<button class="btn userrec_edit finished"><?=lang('finished')?></button>
	</div>
</script>

<!-- field wrapper -->
<script id="field_wrapper_template" type="text/html">
	<div class="element_wrapper"
		data-element-id="<{= elementId }>"
		data-required="<{ if (typeof required !== 'undefined' && required == 'yes') { }>yes<{ } else { }>no<{ }}>">
		<div class="element_cover"></div>
	<{ if (typeof elementLabel !== 'undefined') { }>
		<div class="element_label"><{= elementLabel }><span class="element_required">*</span></div>
	<{ } }>
		<div class="inner_element">
			<{= element }>
		</div>
		<div class="element_control">
			<div class="freeform_cog_button">
				<div class="control_flyout">
					<div class="flyout_petal"></div>
					<div class="flyout_container">
						<ul>
							<li><{= elementId }></li>
							<li>
								<a class="element_delete" href="#">
									<?=lang('delete_field_lower')?>
								</a>
							</li>
						<{ if (elementId.indexOf('nonfield') == -1 ||
							(typeof requireable !== 'undefined' && requireable == 'yes')){ }>
							<li>
								<a class="element_require" href="#">
									<?=lang('require_field_lower')?>
								</a>
								<a class="element_unrequire" href="#">
									<?=lang('unrequire_field_lower')?>
								</a>
							</li>
						<{ } }>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>

<!-- field tag -->
<script id="field_tag_template" type="text/html">
	<div class="field_tag" data-field-name="<{= fieldName }>">
		<div class="freeform_edit_button"></div>
		<span class="field_label"><{= fieldLabel }></span>
	</div>
</script>

<!-- field drag template -->
<script id="field_drag_template" type="text/html">
	<div class="field_dragger">
		<{= fieldLabel }>
	</div>
</script>

<!-- field drag template -->
<script id="nav_toggle_template" type="text/html">
	<div class="composer-mcp-nav-toggle">
		<?=lang('toggle_mcp_nav_menu')?>
		<i class="hamburger-icon"></i>
	</div>
</script>

<script type="text/javascript">
	(function(global){
		var Freeform = global.Freeform = global.Freeform || {};
		//jshint ignore:start
		Freeform.composerLang			= {
			"cancel"				: "<?=htmlentities(lang('dialog_cancel'), ENT_QUOTES, 'UTF-8')?>",
			"continueAnyway"		: "<?=htmlentities(lang('dialog_continue_anyway'), ENT_QUOTES, 'UTF-8')?>",
			"clearAllWarn"			: "<?=htmlentities(lang('clear_all_warning'), ENT_QUOTES, 'UTF-8')?>",
			"dblClickEdit"			: "<?=htmlentities(lang('double_click_to_edit'), ENT_QUOTES, 'UTF-8')?>",
			"dblClickEditDynrec"	: "<?=htmlentities(lang('double_click_to_edit_recipients'), ENT_QUOTES, 'UTF-8')?>",
			"formLabel"				: "<?=htmlentities($form_data['form_label'], ENT_QUOTES, 'UTF-8')?>",
			"missingSubmits"		: "<?=htmlentities(lang('missing_submits'), ENT_QUOTES, 'UTF-8')?>",
			"notfyFriends"			: "<?=htmlentities(lang('notify_friends'), ENT_QUOTES, 'UTF-8')?>",
			"previewTitle"			: "<?=htmlentities(lang('composer_preview'), ENT_QUOTES, 'UTF-8')?>",
			"submit"				: "<?=htmlentities(lang('submit'), ENT_QUOTES, 'UTF-8')?>",
			"submit_previous"		: "<?=htmlentities(lang('previous'), ENT_QUOTES, 'UTF-8')?>"
		};
		Freeform.prohibitedFieldNames	= [
			'<?=implode("','", array_unique($prohibited_field_names))?>'
		];
		Freeform.url					= {
			"newField"			: '<?=$new_field_url?>',
			"fieldData"			: '<?=$field_data_url?>',
			"composerPreview"	: '<?=$composer_preview_url?>',
			"composerSave"		: '<?=$composer_ajax_save_url?>'
		};
		Freeform.allowedHtmlTags		= [<?=$allowed_html_tags?>];
		Freeform.composerFieldData		= <?=$field_composer_output_json?>;
		Freeform.composerFieldIdList	= <?=$field_id_list?>;
		Freeform.composerLayoutData		= <?=$composer_layout_data?>;
		Freeform.disableMissingSubmit	= <?=($disable_missing_submit_warning ? 'true' : 'false')?>;
		//jshint ignore:end
	}(window));
</script>
