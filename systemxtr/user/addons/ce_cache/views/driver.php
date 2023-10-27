<div class="ce-cache<?php $disabled ? ' ce-cache-caching-off' : ''; ?>">
	<?php
		if ( $disabled )
		{
			echo lang( 'ce_cache_off' );
		}
	?>

	<div class="box">
		<h1><?php echo lang("ce_cache_driver_{$driver}"); ?></h1>
		<div class="md-wrap settings">
			<?php
				//is the driver in the active driver array?
				$is_active = in_array( $driver, $active_drivers );
			?>
			<p><b><?php echo lang('ce_cache_driver_status'); ?></b>: <?php echo $is_active ? lang('ce_cache_active_driver') : lang('ce_cache_supported_driver');?></p>

			<p>
			<?php
				//view items button
				if ($driver != 'memcached' && $driver != 'memcache' && $driver != 'dummy')
				{
					echo '<a class="ce-cache-view-items btn action" href="'.ee('CP/URL', 'addons/settings/ce_cache/view_items/'.$driver).'">'.lang('ce_cache_view_items').'</a> ';
				}

				//clear site cache button
				if ($driver != 'dummy')
				{
					$clear_text = $is_msm ? lang('ce_cache_clear_cache_site') : lang('ce_cache_clear');
					echo '<a class="ce-cache-clear-driver-site btn action" href="'.ee('CP/URL', 'addons/settings/ce_cache/clear_cache/'.$driver.'/y').'">'.$clear_text.'</a> ';
				}

				//msm clear entire driver cache
				if ( $is_msm && $driver != 'dummy' ) //msm option to break entire driver cache
				{
					echo '<a class="ce-cache-clear-driver btn action" href="'.ee('CP/URL', 'addons/settings/ce_cache/clear_cache/'.$driver).'">'.lang('ce_cache_clear_cache_driver').'</a> ';
				}
			?>
			</p>
		</div><!-- .md-wrap -->
	</div><!-- .box -->
</div><!-- .ce-cache -->