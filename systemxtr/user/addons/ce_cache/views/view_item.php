<div class="ce-cache">
	<div class="box">
		<h1><?php echo lang('ce_cache_view_item'); ?></h1>
		<div class="md-wrap settings">

			<div class="ce-cache-item-meta"><?php echo '<b>'.lang('ce_cache_id').':</b> <code class="nohighlight hljs">'.$item.'</code>' ?></div>
			<div class="ce-cache-item-meta"><?php echo '<b>'.lang('ce_cache_created').':</b> <code class="nohighlight hljs">'.$made.'</code>' ?></div>
			<div class="ce-cache-item-meta"><?php echo '<b>'.lang('ce_cache_ttl').':</b> <code class="nohighlight hljs">'.$ttl.' '. lang('ce_cache_seconds').'</code>' ?></div>
			<div class="ce-cache-item-meta"><?php
				echo '<b>'.lang('ce_cache_expires').':</b> <code class="nohighlight hljs">'.$expiry;
				if ($ttl_remaining != 0)
				{
					echo ' ('.$ttl_remaining.' '.lang('ce_cache_seconds_from_now').')';
				}
				echo '</code>';
			?></div>
			<div class="ce-cache-item-meta"><?php echo '<b>'.lang('ce_cache_size').':</b> <code class="nohighlight hljs">'.$size.' ('.$size_raw.' '.lang('ce_cache_bytes').') </code>' ?></div>
			<?php
			$tag_count = count( $tags );
			if ( ! empty( $tag_count ) )
			{
				echo '<p><b>';
				echo ( $tag_count === 1 ) ? lang('ce_cache_tag') : lang('ce_cache_tags');
				echo ':</b> <code class="nohighlight hljs">'.implode('</code>|<code>', $tags ).'</code></p>';
			}
			?>
			<div class="ce-cache-item-meta"><?php echo '<b>'.lang('ce_cache_content').':</b> ' ?></div>

			<pre id="ce_cache_code_holder"><?php echo htmlentities( $content ) ?></pre><!-- #ce_cache_code_holder -->
		</div><!-- .md-wrap -->
	</div><!-- .box -->
</div><!-- .ce-cache -->