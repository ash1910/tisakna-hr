<div class="ce-cache">
	<div class="box">
		<h1><?php echo lang('ce_cache_clear_cache'); ?></h1>
<?php
	//open form
	echo form_open($action_url, array('class'=>'settings'));

	if (! $site_only || ($driver == 'memcached') || ($driver == 'memcache')) //multiple sites or driver that clears all
	{
		echo '<p>' . sprintf( lang('ce_cache_confirm_clear_all_drivers'), lang("ce_cache_driver_{$driver}") ) . '</p>';
	}
	else //clear the driver cache for the current site
	{
		echo '<p>' . sprintf( lang('ce_cache_confirm_clear_site_driver'), lang("ce_cache_driver_{$driver}") ) . '</p>';
	}

	//submit
	echo form_submit( array(
			'name' => 'submit',
			'value' => lang('ce_cache_confirm_clear_button'),
			'class' => 'btn action'
		)
	);

	//close form
	echo form_close();
?>
	</div><!-- .box -->
</div><!-- .ce-cache -->