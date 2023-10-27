<h3><?=lang('database_settings')?></h3>
<?= form_open($action_url . '/database_settings'); ?>
<?php
	$this->table->clear();
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(
		array('data' => lang('preference'), 'width' => "40%"),
		array('data' => lang('setting')));

	    $this->table->add_row(lang('hostname'), form_input('hostname', @$hostname));
	    $this->table->add_row(lang('username'), form_input('username', @$username));
	    $this->table->add_row(lang('password'), form_input('password', @$password));
	    $this->table->add_row(lang('database'), form_input('database', @$database));
	    
	echo $this->table->generate();
?>
<div style="text-align: center;">
	<?= form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit')); ?>
</div>
<?= form_close(); ?>