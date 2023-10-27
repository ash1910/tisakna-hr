<?php

echo form_open($action_url);

$this->table->set_template($cp_table_template);

// -------------------------------------------
//  License Key
// -------------------------------------------

ee()->table->set_heading(array('data' => lang('matrix_preferences'), 'style' => 'width: 50%'), lang('setting'));

$this->table->add_row(
	array('style' => 'width: 50%', 'data' => lang('license_key', 'license_key')),
	form_input('settings[license_key]', $settings['license_key'], 'id="license_key" style="width: 98%"')
);

echo $this->table->generate();

echo form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'));
echo form_close();
