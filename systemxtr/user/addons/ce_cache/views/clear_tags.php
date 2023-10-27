<div class="ce-cache">
	<div class="box">
		<h1><?php echo lang('ce_cache_clear_tagged_items'); ?></h1>
		<div class="md-wrap">
<?php
	if ( count( $tags ) > 0 ) //we've got tags
	{
		//tag instructions
		echo '<p>' . lang( 'ce_cache_clear_tags_instructions' ) . '</p>';

		//open form
		echo form_open( $action_url, '' );

		//load the table library
		ee()->load->library('table');

		//set up the table headings
		ee()->table->set_heading( '<input type="checkbox" id="ce_cache_tag_master" name="ce_cache_tag_master" /> ' . lang( 'ce_cache_tag' ) );

		//loop through the tags
		foreach( $tags as $index => $tag )
		{
			ee()->table->add_row(
				form_checkbox(
					array(
						'name' => 'ce_cache_tags[]',
						'id' => 'ce_cache_tag_' . $index,
						'class' => 'ce_cache_tag_item',
						'value' => $tag,
						'checked' =>  in_array( $tag, $selected ),
					 )
				) . ' ' . form_label( $tag, 'ce_cache_tag_' . $index )
			);
		}

		//generate the table
		echo ee()->table->generate();

		//submit
		echo '<p>';
		echo form_submit( array( 'name' => 'submit', 'value' => lang( "ce_cache_confirm_delete_tags_button" ), 'class' => 'btn action' ) );
		echo '</p>';

		//close form
		echo form_close();
	}
	else //no tags
	{
		echo '<div class="no-results"><p>'.lang('ce_cache_no_tags').'</p></div>';
	}
?>
		</div>
	</div><!-- .box -->
</div><!-- .ce-cache -->