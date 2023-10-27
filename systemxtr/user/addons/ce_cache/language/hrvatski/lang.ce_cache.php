<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$lang = array(
	'ce_cache_module_name' => 'CE Cache',
	'ce_cache_module_description' => 'Fragment caching via db, files, APC, Redis, SQLite, Memcache, and/or Memcached + static file caching',
	'ce_cache_module_home' => 'CE Cache Home',
	'ce_cache_home' => 'Home',
	'ce_cache_drivers' => 'Drivers',
	'ce_cache_channel_cache_breaking' => 'Cache Breaking',
	'ce_cache_breaking_instructions' => 'Cache break settings allow you to control which cache items to remove (and optionally refresh) when one or more entries from one or more channels are added, updated, or deleted. The "Any Channel" settings are applied when an entry changes from any channel for the current site. Individual channel cache break settings will also be applied in addition to the "Any Channel" settings.',
	'ce_cache_debug' => 'Debug',
	'ce_cache_channel_breaking_settings' => '&ldquo;%s&rdquo; Cache Breaking Settings',
	'ce_cache_addon_breaking_settings' => '&ldquo;%s&rdquo; Cache Breaking Settings',
	'ce_cache_break_settings' => 'Cache Break Settings',
	'ce_cache_driver' => 'Driver',
	'ce_cache_channel' => 'Channel',
	'ce_cache_error_no_channels' => 'It doesn\'t look like there are any channels yet. Once you have created a channel, you can come back here and set up cache breaking settings for it.',
	'ce_cache_addon' => 'Add-on',
	'ce_cache_driver_status' => 'Status',
	'ce_cache_active_driver' => 'Active Driver',
	'ce_cache_supported_driver' => 'Supported Driver (Inactive)',
	//'ce_cache_error_no_addons' => 'It doesn\'t look like CE Cache add-on breaking has been set up yet. You can enable cache breaking for the following add-ons, by adding one or more of them to your EE config file: <code>$config[\'ce_cache_enabled_addons\'] = \'structure|taxonomy|low_variables|low_reorder\';</code> Please visit the documentation for more details.',
	'ce_cache_structure' => 'Structure',
	'ce_cache_taxonomy' => 'Taxonomy',
	'ce_cache_low_variables' => 'Low Variables',
	'ce_cache_low_reorder' => 'Low Reorder',
	'ce_cache_is_supported' => 'Supported',
	'ce_cache_yes' => 'Yes',
	'ce_cache_no' => 'No',
	'ce_cache_bytes' => 'Bytes',
	'ce_cache_size' => 'Size',
	'ce_cache_driver_file' => 'File',
	'ce_cache_driver_all' => 'All',
	'ce_cache_driver_apc' => 'APC',
	'ce_cache_driver_memcached' => 'Memcached',
	'ce_cache_driver_memcache' => 'Memcache',
	'ce_cache_driver_dummy' => 'Dummy',
	'ce_cache_driver_db' => 'Database',
	'ce_cache_driver_redis' => 'Redis',
	'ce_cache_driver_sqlite' => 'SQLite',
	'ce_cache_driver_static' => 'Static',
	'ce_cache_clear_cache_question_site' => 'Clear Driver Site Cache?',
	'ce_cache_clear_cache_question_driver' => 'Clear Entire Driver Cache?',
	'ce_cache_clear_cache_site' => 'Clear Driver Site Cache',
	'ce_cache_clear' => 'Clear',
	'ce_cache_clear_all' => 'Clear All',
	'ce_cache_clear_cache_driver' => 'Clear Entire Driver Cache',
	'ce_cache_clear_cache_all_drivers' => 'Clear Entire Cache For All Drivers',
	'ce_cache_clear_cache_site_all' => 'Clear Site Cache For All Drivers',
	'ce_cache_clear_cache' => 'Clear Cache',
	'ce_cache_view_items' => 'View Items',
	'ce_cache_view_driver_items' => 'View %s Driver Items',
	'ce_cache_driver_items' => '%s Items',
	'ce_cache_view_item' => 'View Item',
	'ce_cache_view' => 'View',
	'ce_cache_id' => 'Cache Item Id',
	'ce_cache_seconds' => 'seconds',
	'ce_cache_seconds_from_now' => 'seconds from now',
	'ce_cache_created' => 'Created',
	'ce_cache_expires' => 'Expires',
	'ce_cache_ttl' => 'Time To Live',
	'ce_cache_content' => 'Content',
	'ce_cache_delete_children' => 'Delete Children',
	'ce_cache_delete_item' => 'Delete Item',
	'ce_cache_delete' => 'Delete',
	'ce_cache_back_to' => 'Back To',
	'ce_cache_viewing_item_meta' => 'Cache Item <i>%s</i>',
	'ce_cache_clear_cache_success' => 'The cache has been cleared successfully.',
	'ce_cache_clear_all_cache_success' => 'The caches have been cleared successfully.',
	'ce_cache_clear_site_cache_success' => 'The site caches for all drivers have been cleared successfully.',
	'ce_cache_delete_item_success' => 'The item has been deleted successfully.',
	'ce_cache_delete_children_success' => 'The child items of the path have been deleted successfully.',
	'ce_cache_confirm_clear_site_driver' => 'Are you sure you want to clear the %s driver cache for the current site?',
	'ce_cache_confirm_clear_all_drivers' => 'Are you sure you want to clear the %s driver cache for all sites?',
	'ce_cache_confirm_clear_all_driver' => 'Are you sure you want to clear the entire cache for all drivers of the current site?',
	'ce_cache_confirm_clear_site_drivers' => 'Are you sure you want to clear the entire cache for all drivers of all sites?',
	'ce_cache_confirm_clear_button' => "Yes I'm Sure, Clear the Cache",
	'ce_cache_confirm_clear_all_button' => "Yes I'm Sure, Clear All Driver Caches",
	'ce_cache_confirm_clear_sites_button' => "Yes I'm Sure, Clear The Site Cache For All Drivers",
	'ce_cache_confirm_delete_button' => "Delete the Item",
	'ce_cache_confirm_delete_children_button' => "Yes I'm Sure, Delete the Child Items",
	'ce_cache_error_no_driver' => 'No driver was specified.',
	'ce_cache_no_items' => 'No items were found.',
	'ce_cache_no_more_items' => 'All found items were expired. Please refresh the page.',
	'ce_cache_error_no_path' => 'No path was specified.',
	'ce_cache_error_no_item' => 'No item was specified.',
	'ce_cache_error_invalid_driver' => 'The specified driver is not valid.',
	'ce_cache_error_invalid_path' => 'An item path was not received.',
	'ce_cache_error_getting_items' => 'No cache items were found.',
	'ce_cache_error_getting_meta' => 'No information could be found for the specified item.',
	'ce_cache_error_cleaning_cache' => 'Something went wrong and the cache was *not* cleaned successfully.',
	'ce_cache_error_cleaning_driver_cache' => 'The cache may have *not* been cleaned successfully for the %s driver.',
	'ce_cache_error_deleting_item' => 'Something went wrong and the item "%s" was *not* deleted successfully.',
	'ce_cache_error_no_channel' => 'No channel was specified.',
	'ce_cache_error_no_addon' => 'The specified add-on is invalid.',
	'ce_cache_error_no_addon_or_channel' => 'No channel or add-on was specified.',
	'ce_cache_error_channel_not_found' => 'Channel not found.',
	'ce_cache_save_settings' => 'Save Settings',
	'ce_cache_save_settings_success' => 'Your settings have been saved successfully.',
	'ce_cache_save_settings_error' => 'Some corrections need to be made before your settings can be saved.',
	'ce_cache_save_settings_fail' => 'Your settings were not saved.',
	'ce_cache_any_channel' => 'Any Channel',
	'ce_cache_add' => 'Add',
	'ce_cache_remove' => 'Remove',
	'ce_cache_tags' => 'Tags',
	'ce_cache_tag' => 'Tag',
	'ce_cache_tag_add' => 'Add Tag',
	'ce_cache_items' => 'Items',
	'ce_cache_paths' => 'Paths',
	'ce_cache_path_any' => 'Any',
	'ce_cache_path_local' => 'local',
	'ce_cache_path_global' => 'global',
	'ce_cache_path_static' => 'static',
	'ce_cache_path_non_global' => 'Non-global',
	'ce_cache_path_select' => 'Please select one',
	'ce_cache_variables' => 'Variables',
	'ce_cache_error_module_not_installed' => 'The correct version of the module is not installed, so cache breaking cannot be implemented.',
	'ce_cache_error_invalid_refresh_time' => 'The refresh time must be a number between 0 and 15 inclusively.',
	'ce_cache_error_invalid_path_start' => 'Paths must begin with <code>local/</code>, <code>global/</code>, or <code>any/</code>.',
	'ce_cache_error_invalid_path_length' => 'Paths must be less than or equal to 250 characters in length',
	'ce_cache_error_invalid_tag_character' => 'A tag contains one or more disallowed characters.',
	'ce_cache_error_invalid_tag_length' => 'Tags must be less than or equal to 100 characters in length.',
	'ce_cache_break_intro_html' => '
		<p>This page allows you to remove certain cache items whenever one or more entries from the &ldquo;%s&rdquo; channel are added, updated, or deleted for the current site.</p>',
	'ce_cache_break_error_html' => '<p>The following error occurred:</p>',
	'ce_cache_break_errors_html' => '<p>The following errors occurred:</p>',
	'ce_cache_break_not_loaded_html' => '
		<p>There was a problem loading the JavaScript on this page.</p>',
	'ce_cache_break_intro_any' => '
			<p>This page allows you to remove certain cache items whenever one or more entries from any channel are added, updated, or deleted for the current site. Individual channel cache break settings will also be applied in addition to these settings.</p>',
	'ce_cache_break_intro_addon' => '<h3>Cache Breaking</h3>
			<p>This page allows you to remove certain cache items from the current site whenever the %s add-on triggers a cache break. You can choose to have cache items recreate themselves after they are removed. This will only work for non-global items, as they contain a relative path to a specific page. However, any removed global items that happen to be on a refreshed page will also be recreated.</p>',
	'ce_cache_refresh_cached_items_question' => 'Refresh cached items after deleting them?',
	'ce_cache_refresh_all_at_once' => "All At Once (not recommended)",
	'ce_cache_refresh_delay' => 'Staggered - %d second delay between',
	'ce_cache_refreshing' => 'Cache Refreshing',
	'ce_cache_refresh_cached_items_intro_html' => '<p>You can choose to have cache items recreate themselves after they are removed. This will only work for non-global items, as they contain a relative path to a specific page. However, any removed global items that happen to be on a refreshed page will also be recreated.</p>',
	'ce_cache_refresh_cached_items_instructions_html' => '<p>Please choose the number of seconds to wait between refreshing cached items. This can be really helpful if you are refreshing a large number of pages, and you don&rsquo;t want to bog down your server all at one time. However, keep in mind that this will take more time; if you have 200 pages with items to be refreshed, and you are delaying 2 seconds between each one, it will take at least 400 seconds (almost 7 minutes) for all of the cache items to be recreated. You will not need to stay on the page while the cache is being recreated.</p>',
	'ce_cache_breaking_tags_instructions_html' => '<p>In your templates, you can assign tags to the <code class="hljs no-highlight">It</code>, <code class="hljs no-highlight">Save</code>, and <code class="hljs no-highlight">Static</code> methods using the <code class="hljs no-highlight">tags=</code> parameter. You can specify one or more tags below and any items that have those tags will be removed when an entry in this channel changes.</p>',
	'ce_cache_breaking_tags_examples_html' => '
			<p><b>Tag Examples:</b></p>
			<ul class="ce_cache_break_item_examples">
				<li>To clear all items with a tag of &ldquo;apple&rdquo;, you would add <code class="hljs no-highlight">apple</code></li>
				<li>To clear all items with a tag of the current channel name, you could add <code class="hljs no-highlight">{channel_name}</code></li>
			</ul>
			<p><b>Note:</b> Tags are not case sensitive, so <code class="hljs no-highlight">apple</code> is considered the same as <code class="hljs no-highlight">Apple</code>. Although discouraged, spaces in your tags are allowed, so <code class="hljs no-highlight">bricks in the wall</code> is technically a valid tag. Tags may not contain any pipe (<code class="hljs no-highlight">|</code>) characters.</p>',
	'ce_cache_breaking_items_instructions_html' => '<p>You can add item id paths or item parent paths to remove when an entry in this channel changes. If you are specifying a parent path (as opposed to an item id), then be sure to give it a trailing slash (<code class="hljs no-highlight">/</code>).</p>',
	'ce_cache_breaking_paths_examples_html' => '
			<p><b>Path Examples:</b></p>
			<ul class="ce_cache_break_item_examples">
				<li>To clear all static items for the entire site, you would add: <code class="hljs no-highlight">static/</code></li>
				<li>If you had a &ldquo;blog&rdquo; section of your site, and wanted to remove all local cached content under that section, you would add: <code class="hljs no-highlight">local/blog/</code></li>
				<li>If you wanted to clear a specific item, like your home page, you could add: <code class="hljs no-highlight">local/item</code> (assuming your home page has a cache item with the id &ldquo;item&rdquo;)</li>
				<li>To clear a global item with an id of &ldquo;footer&rdquo;, you could add: <code class="hljs no-highlight">global/footer</code></li>
				<li>To clear all local caches where {segment_1} matched the current {channel_name} and {segment_2} matched the {url_title}, use <code class="hljs no-highlight">local/{channel_name}/{url_title}/</code></li>
			</ul>',
	'ce_cache_breaking_variables_html' => '<p class="alert inline warn">The following variables can be used in the tags and paths: <code class="hljs">{entry_id}</code>, <code class="hljs">{url_title}</code>, <code class="hljs">{channel_id}</code>, <code class="hljs">{channel_name}</code>, <code class="hljs">{author_username}</code>, <code class="hljs">{author_id}</code>, <code class="hljs">{entry_date format=""}</code>, and <code class="hljs">{edit_date format=""}</code></p>',
	'ce_cache_clear_tagged_items' => 'Clear Tagged Items',
	'ce_cache_clear_tags_instructions' => '<p>The following tags represent cached tag items. Please select which tags you wish to clear.</p>',
	'ce_cache_no_tags' => 'No tags were found for the current site.',
	'ce_cache_confirm_delete_tags_button' => 'Clear The Selected Tags',
	'ce_cache_delete_tags_success' => 'The tags have been cleared successfully.',
	'ce_cache_delete_tags_fail' => 'No tags were submitted to clear.',
	'ce_cache_static_installation' => 'Static Driver Installation',
	'ce_cache_static_instructions' => '
			<div class="tab-wrap">
				<ul class="tabs">
					<li><a href="" rel="t-0" class="act">Installation</a></li>
					<li><a href="" rel="t-1">Troubleshooting</a></li>
				</ul>
				<div class="tab t-0 tab-open">
					<div class="md-wrap">
						<p>These instructions are for setting up the CE Cache static driver for the current site only. Since these settings are dependent on the site&rsquo;s name for uniqueness, if you change the site&rsquo;s name (by going to Settings -> General Settings -> "Name" on a standalone installation, or by editing the Site Label for an MSM site), you will need to update these settings.</p>
						
						<h2>Step 1 - Create The Static Cache Directory</h2>
						<p>If you haven&rsquo;t already, create a directory named \'<i>static</i>\' in your site&rsquo;s web root directory. Make sure the directory is writable by Apache and can display the files (permissions of 0775 should do).</p>
						
						<h2>Step 2 - Update The Cache Handler Path</h2>
						<p>If you haven&rsquo;t already, upload the \'<i>_static_cache_handler.php</i>\' file to your site&rsquo;s web root directory. Change this line:<br> <code>private $cache_folder = \'static/ce_cache/xxxxxx\';</code><br> to this:<br> <code>private $cache_folder = \'static/{prefix}\';</code></p>
						
						<h2>Step 3 - Set Up .htaccess Rules</h2>
						<p>If it doesn&rsquo;t exist already, create an <i>.htaccess</i> file in your web root. Merge the following rules into the <i>.htaccess</i> file:</p>
					
<pre><code style="display: block;">&lt;IfModule mod_rewrite.c&gt;
	RewriteEngine On

	#------------------- remove trailing slash (optional, but recommended) -------------------
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteCond %{REQUEST_URI} ^(.+)/$
	RewriteRule ^(.+)/$ /$1 [R=301,NE,L]

	#------------------- remove index.php -------------------
	#see http://ellislab.com/expressionengine/user-guide/urls/remove_index.php.html
	RewriteCond %{THE_REQUEST} ^GET.*index\\\.php [NC]
	#RewriteCond %{REQUEST_URI} !^/system [NC]
	RewriteRule (.*?)index\\\.php/*(.*) /$1$2 [R=301,NE,L]

	#------------------- CE Cache Static Driver -------------------
	RewriteCond %{REQUEST_METHOD} GET
	RewriteCond $1 !\\\.(css|js|gif|jpe?g|png) [NC]
	#RewriteCond %{REQUEST_URI} !^/system [NC]
	RewriteCond %{QUERY_STRING} !ACT|URL|CSS|preview [NC]
	RewriteCond %{DOCUMENT_ROOT}/static/{prefix}/static/$1/index\\\.html -f
	RewriteRule ^(.*?)/?$ /_static_cache_handler.php/$1/index.html [NE,L,QSA]

	#------------------- rewrite requests to index.php -------------------
	#see http://ellislab.com/expressionengine/user-guide/urls/remove_index.php.html
	RewriteCond $1 !\\\.(css|js|gif|jpe?g|png) [NC]
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^(.*)$ /index.php/$1 [L,QSA]
&lt;/IfModule&gt;
</code></pre>
						<div class="alert inline warn">
							<p><b>Note 1</b>: The order of the <i>.htaccess</i> rules is important and should be preserved.</p>
						</div><!-- .alert -->
						<div class="alert inline warn">
							<p><b>Note 2</b>: If you are using a folder as your control panel login (as opposed to the default <i>admin.php</i> file), uncomment the <code>#RewriteCond %{REQUEST_URI} !^/system [NC]</code> lines above (by removing the octothorp <code>#</code> from the beginning of the lines). If your folder has a different name than the default ("<i>system</i>"), be sure to update those lines to reflect the correct folder name.</p>
						</div><!-- .alert -->
						
						<h2>Step 4 - Add The Configuration Setting</h2>
						<p>Add <code>$config[\'ce_cache_static_enabled\'] = \'yes\';</code> to your <i>config.php</i> file (located in the <i>/system/user/config/</i> directory by default).</p>
						
						
						<h2>Step 5 - Test</h2>
						<p>If the Static Driver&rsquo;s cache directory is found, the driver should appear in the drivers table on the <a href="{ce_cache_home_link}" target="_blank">CE Cache Module home page</a>. If it is not showing up, you can override the path in config.php with the following setting:<br>
							<code>$config[\'ce_cache_static_path\'] = \'/server/path/to/web_root/static\';</code></p>
						
						<p>Now add <code>&#123;exp:ce_cache:stat:ic&#125;</code> to one of your templates, and visit a URL that uses that template. The page should now be cached, and when you refresh the page again, it should be faster than ever. If it is not working, take a look at the next section for troubleshooting tips.</p>
					</div><!-- .md-wrap -->
				</div><!-- .tab -->
				<div class="tab t-1">
					<div class="md-wrap">
						<h2>Debug Mode</h2>
						<p>If something is not working (like a redirect loop, or the page redirects to the homepage), or you want to see how fast the page is being rendered by the driver, you can enable debug mode by opening \'<i>/_static_cache_handler.php</i>\' and changing <code>private $debug = false;</code> to <code>private $debug = true;</code>. Debug messages will now be shown in an HTML comment at the bottom of the rendered pages (view source to see). Don&rsquo;t forget to set this back to <code>false</code> when you are done debugging.</p>
						
						
						<h2>Query String Mode</h2>
						<p>If your site links return 404 pages, a "No Input File Specified" error, or all links return the same content, you may need to modify your <i>.htaccess</i> file. You&rsquo;ll want to replace this line:<br>
						<code>RewriteRule ^(.*)$ /index.php/$1 [L,QSA]</code><br> with this:<br> <code>RewriteRule ^(.*)$ /index.php?/$1 [L,QSA]</code><br>
						and replace this line:<br>
						<code>RewriteRule ^(.*?)/?$ /_static_cache_handler.php/$1/index.html [NE,L,QSA]</code><br>
						with this:<br>
						<code>RewriteRule ^(.*?)/?$ /_static_cache_handler.php?/$1/index.html [NE,L,QSA]</code><br>
						(notice the added question marks after <code>.php</code>).</p>
						
						
						<h2>.htaccess Variable Issue</h2>
						<p>Several people have reported that they have problems with htaccess variables on Rackspace hosting (this could potentially be an issue with other hosts as well). In particular, the <code class="apache">%{DOCUMENT_ROOT}</code> variable is not set to the correct value and will need to be replace with the actual server path to the document root (ex: <code>/path/to/web/root</code>).</p>
						
						
						<h2>Logged Out Only</h2>
						<p>If you only want static caching to only be enabled when users are not logged in, you can add:<br>
						<code>$config[\\\'ce_cache_static_logged_out_only\\\'] = \\\'yes\\\';</code><br>
						to your ExpressionEngine <i>config.php</i> file, and then add this conditional to your <i>.htaccess</i>:<br>
						<code>RewriteCond %{HTTP_COOKIE} !exp_sessionid= [NC]</code><br>
						(add that line directly after the line <code>RewriteCond %{QUERY_STRING} !ACT|URL|CSS|preview [NC]</code>).</p>
						<p>If you are using a different cookie prefix (other than <code>exp_</code>), be sure to update your rewrite conditional accordingly.</p>
						
						
						<h2>Static Flat File Caching (Optional, Not Recommended)</h2>
						<p>The static driver normally utilizes the <i>_static_cache_handler.php</i> script to give the static driver some extra functionality (such as outputting the original page\'s headers and expiring the cache as needed). However, if you would rather not have any PHP overhead (though it&rsquo;s quite negligible already), you can bypass the cache handler script completely. By doing this, all files cached with the static driver will not expire on their own; it effectively sets <code>seconds="0"</code> for every cache item. Cache breaking, tagging, and everything else should still work as expected though.</p>
						
						<p>To use static flat file caching, you&rsquo;ll need to add this to config.php:<br>
						<code>$config[\\\'ce_cache_static_flat\\\'] = \\\'yes\\\';</code></p>
						
						<p>Next, <b>clear your static driver cache in the control panel</b>. This is important, as the cache files will now be flat HTML (as opposed to containing serialized data).</p>
						
						<p>Finally, you&rsquo;ll want to replace this line in your <i>.htaccess</i> file:<br>
						<code>RewriteRule ^(.*?)/?$ /_static_cache_handler.php/$1/index.html [NE,L,QSA]</code><br>
						with this:<br>
						<code>RewriteRule ^(.*?)/?$ /static/{prefix}/static/$1/index.html [NE,L,QSA]</code><br>
						to ensure that the <i>.htaccess</i> rule serves the static cache files.</p>
					</div><!-- .md-wrap -->
				</div><!-- .tab -->
			</div><!-- .tab-wrap -->',

	//misc ajax errors
	'ce_cache_ajax_unknown_error' => 'An unknown error occurred.',
	'ce_cache_ajax_no_items_found' => 'No items were found.',
	'ce_cache_ajax_error' => 'An unexpected response was received:',
	'ce_cache_ajax_error_title' => 'Unexpected Response',
	'ce_cache_ajax_install_error' => 'An error has occurred! Please ensure the CE Cache module is installed correctly.',

	//delete child items
	'ce_cache_ajax_delete_child_items_confirmation' => 'Are you sure you want to delete all of the \\\"%s\\\" child items?',
	'ce_cache_ajax_delete_child_items_button' => 'Delete Child Items',
	'ce_cache_ajax_delete_child_items_refresh' => 'Refresh items after deleteing them?',
	'ce_cache_ajax_delete_child_items_refresh_time' => 'How many seconds would you like to wait between refreshing items?',
	//delete item
	'ce_cache_ajax_delete_child_item_confirmation' => 'Are you sure you want to delete the \\\"%s\\\" item?',
	'ce_cache_ajax_delete_child_item_refresh' => 'Refresh the item after it is deleted?',
	'ce_cache_ajax_delete_child_item_button' => 'Delete Item',
	//cancel button
	'ce_cache_ajax_cancel' => 'Cancel',
	//turned off
	'ce_cache_off' => '
		<div class="alert inline warn">
			<h3>Caching Disabled</h3>
			<p>Caching via CE Cache is currently turned off in the config file.</p>
		</div>',
	//debug
	'ce_cache_debug_url' => '<p>Attempting to synchronously call <a href="%s">%s</a>.</p>',
	'ce_cache_debug_curl' => '<p>Synchronous cache breaking appears to be working with cURL.</p>',
	'ce_cache_debug_fsockopen' => '<p>Synchronous cache breaking appears to be working with fsockopen.</p>',
	'ce_cache_debug_not_working' => '<p>Synchronous cache breaking is not working.</p>',
	'ce_cache_debug_working' => '<p>Success!</p>',
	'ce_cache_show_examples' => 'Show Examples',
	'ce_cache_hide_examples' => 'Hide Examples',
	'ce_cache_alert_success' => 'Success',
	'ce_cache_alert_issue' => 'Error',
	'ce_cache_alert_warning' => 'Warning'
);

/* End of file lang.ce_cache.php */
/* Location: /system/expressionengine/third_party/ce_cache/language/english/lang.ce_cache.php */
