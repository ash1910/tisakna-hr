<?= form_open($action_url.'/settings', 'id="form1"', array('sbt'=>1)); ?>
<?= form_hidden('import_id', $import_id); ?>
<?php
	$this->table->clear();
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(
		array('data' => lang('preference'), 'width' => "30%"),
		array('data' => ''));

		$cond = '';
		if(isset($remote_table) and $remote_table)
		{
			$cont = '';
			$lastTable = 'rand';
			foreach ($remote_table as $table) 
			{
				$divs[] = '</div>';
				$cont2 = "<div class='{$lastTable}'>".(isset($condition[$lastTable."_".$table]) ? 'Join<br>':'') . form_dropdown('remote_table[]', $remote_tables, $table);
				if(isset($condition[$lastTable."_".$table]))
				{
					$cont2 .= "<span id='cond{$lastTable}' style='width:30%'>ON&nbsp;<input type='text' style='width:60%' class='condition' value='{$condition[$lastTable."_".$table]}' name='condition[{$lastTable}_$table]'></span>";
				}
				$cont[] = $cont2;
				$lastTable = $table;
			}
			$cont = implode("",$cont). "<div class='$lastTable'><br><a href='javascript:' class='join'>+ Add Table Join</a><br></div>" . implode('', $divs);
		}
		else 
			$cont =  '<div id="remote_tables">'.form_dropdown('remote_table[]', $remote_tables).'</div>';
		
		$this->table->add_row(
	        $this->lang->line('tables'), 
	        $cont
	    );
	    
	    $this->table->add_row(
	        $this->lang->line('channel'), 
	        array('data' => form_dropdown('channel', $channels, $channel))
	    );
	       

	    
	echo $this->table->generate();
?>
<div id='assign_fields'><?php echo $view_assign_fields ?></div>
<div style="text-align: center;">
	<?= form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit')); ?>
</div>
<?= form_close(); ?>