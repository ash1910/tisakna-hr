<?= form_open($action_url.'/add_import_item'); ?>

<?php 

echo $this->lang->line('new_import_item').nbs(). form_input('title', '', 'style="width:150px"'); ?>

<?= form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit')); ?>

<?= form_close(); ?><br>

<?php
	$this->table->clear();
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(
		array('data' => lang('preference'), 'width' => "40%"),
		array('data' => ''),
		array('data' => ''),
		array('data' => ''));
		
		foreach ($import_list as $value) 
		{
			$this->table->add_row(
		        '<a href="#" class="import_list_item" id='.$value['import_id'].'>'.$value['title'].'</a>', 
		        '<a href="'. $base_url.'/settings/'.$value['import_id'].'" >'.lang('settings').'</a>', 
		        '<a href="'. $base_url.'/remove_import_item'.AMP.'import_id='.$value['import_id'].'" class="delete_link">'.lang('remove').'</a>',
		        '<a href="'. $base_url.'/do_import'.AMP.'import='.$value['title'].'">'.lang('proceed').'</a>'
		    );			
		}
	    
	echo $this->table->generate();
?>
