<div class="ce-cache">
	<div class="box">
		<h1><?php echo lang('ce_cache_channel_cache_breaking'); ?></h1>
		<div class="md-wrap">
<?php
	//break instructions
	echo '<p>' . lang( 'ce_cache_breaking_instructions' ) . '</p>';

	ee()->table->set_heading(
		lang( 'ce_cache_channel' ),
		'&nbsp;'
	);

	ee()->table->add_row(
		lang('ce_cache_any_channel'),
		'<a class="ce-cache-settings-icon" href="'.ee('CP/URL', 'addons/settings/ce_cache/breaking_settings/0').'">'.lang('ce_cache_break_settings').'</a>'
	);

	foreach( $channels as $channel )
	{
		ee()->table->add_row(
			$channel['title'],
			'<a class="ce-cache-settings-icon" href="'.ee('CP/URL', 'addons/settings/ce_cache/breaking_settings/'.$channel['id']).'">'.lang('ce_cache_break_settings').'</a>'
		);
	}

	echo ee()->table->generate();
?>
		</div><!-- .md-wrap -->
	</div><!-- .box -->
</div><!-- .ce-cache -->
