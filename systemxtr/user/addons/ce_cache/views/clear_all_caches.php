<div class="ce-cache">
	<div class="box">
		<h1><?php echo lang('ce_cache_clear_cache_all_drivers'); ?></h1>
<?php
	//open form
	echo form_open($action_url, array('class'=>'settings'));

	echo '<p>'.lang('ce_cache_confirm_clear_site_drivers').'</p>';

	//submit
	echo form_submit( array(
			'name' => 'submit',
			'value' => lang('ce_cache_confirm_clear_all_button'),
			'class' => 'btn action'
		)
	);

	//close form
	echo form_close();
?>
	</div><!-- .box -->
</div><!-- .ce-cache -->