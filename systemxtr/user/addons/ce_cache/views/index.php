<div class="ce-cache">
<?php
	if ( $disabled )
	{
		echo '<div class="ce-cache-caching-off">';
		echo lang( 'ce_cache_off' );
	}

	//load the table library
	ee()->load->library('table');

	//set up the table headings
	$headings = array(
		lang( "ce_cache_driver" ),
		lang( "ce_cache_view_items" ),
		lang( "ce_cache_clear_cache_question_site" )
	);
	if ( $is_msm ) //msm option to break entire driver cache
	{
		$headings[] = lang( "ce_cache_clear_cache_question_driver" );
	}
	ee()->table->set_heading( $headings );

	//get the drivers
	$drivers = array_merge( $active_drivers, $supported_drivers );

	$view_text = $is_msm ? lang( "ce_cache_view_items" ) : lang( "ce_cache_view" );
	$clear_text = $is_msm ? lang( "ce_cache_clear_cache_site" ) : lang( "ce_cache_clear" );
	$clear_all_text = $is_msm ? lang( "ce_cache_clear" ) : lang( "ce_cache_clear_all" );

	//the rows
	foreach( $drivers as $driver )
	{
		//is the driver in the active driver array?
		$is_active = in_array( $driver, $active_drivers );

		//the row array
		$row = array(
			( $is_active ? '<span class="ce-cache-active-driver ce-cache-driver-status" title="'.lang("ce_cache_active_driver").'"></span>' : '<span class="ce-cache-supported-driver ce-cache-driver-status" title="'.lang("ce_cache_supported_driver").'"></span>').lang("ce_cache_driver_{$driver}"),
			( $driver != 'memcached' && $driver != 'memcache' && $driver != 'dummy' ) ? '<a class="ce-cache-view-items" href="'.ee('CP/URL', 'addons/settings/ce_cache/view_items/'.$driver).'">' . $view_text . '</a>' : '&ndash;',
			( $driver != 'dummy' ) ? '<a class="ce-cache-clear-driver-site" href="'.ee('CP/URL', 'addons/settings/ce_cache/clear_cache/'.$driver.'/y').'">'.$clear_text.'</a>' : '&ndash;'
		);
		if ( $is_msm ) //msm option to break entire driver cache
		{
			$row[] = ( $driver != 'dummy' ) ? '<a class="ce-cache-clear-driver" href="'.ee('CP/URL', 'addons/settings/ce_cache/clear_cache/'.$driver).'">'.lang("ce_cache_clear_cache_driver").'</a>' : '&ndash;';
		}

		ee()->table->add_row( $row );
	}

	//the final row
	$final_row = array(
		'<span class="ce-cache-all-drivers ce-cache-driver-status"></span>'.lang("ce_cache_driver_all" ).'',
		'&ndash;',
		'<a class="ce-cache-clear-site-drivers" href="'.ee('CP/URL', 'addons/settings/ce_cache/clear_site_caches').'">'.$clear_all_text.'</a>'
	);
	if ( $is_msm ) //msm option to break entire driver cache
	{
		$final_row[] = '<a class="ce-cache-clear-drivers" href="'.ee('CP/URL', 'addons/settings/ce_cache/clear_all_caches').'">'.lang("ce_cache_clear" ).'</a>';
	}
	ee()->table->add_row( $final_row );

	//generate the table
	echo ee()->table->generate();

	if ( $disabled )
	{
		echo '</div><!-- .ce-cache-caching-off -->';
	}
?>
</div><!-- .ce-cache -->