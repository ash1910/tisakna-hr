<?php if (ee()->session->flashdata('message_success')) { ?>
	<div class="alert inline success">
	<?php echo ee()->session->flashdata('message_success');?>
	<a class="close" href="javascript:void"></a>
	</div>
<?php 
} ?>
<?= form_open($post_url,'id='.$form_name) ?>

<div id="store_edit_country_form">
    <?php
        $this->table->clear();
        $this->table->set_template($store_table_template);
        $this->table->set_heading(
            lang('store.state'),
            array('data' => lang('store.code'), 'width' => '20%'),
            array('data' => lang('store.delete'), 'width' => '5%')
        );

        foreach ($states as $key => $state) {
            $this->table->add_row(
                form_hidden("states[{$key}][id]", $state->id).
                form_input("states[{$key}][name]", $state->name).
                form_error("states[{$key}][name]"),
                form_input("states[{$key}][code]", $state->code).
                form_error("states[{$key}][code]"),
                store_form_checkbox("states[{$key}][delete]", $state->delete)
            );
        }

        $this->table->add_row(array(
            'data' => '<a id="store_settings_add_region" href="javascript:void(0)" class="add_region">'.lang('store.add_state').'</a>',
            'colspan' => 3
        ));

        echo $this->table->generate();
    ?>

    <div style="clear: left; text-align: right;">
        <?= form_submit(array('name' => 'submit', 'value' => lang('store.submit'), 'class' => 'submit')); ?>
    </div>

</div>
<?= form_close() ?>
	
