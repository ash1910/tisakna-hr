<div class="container-fluid container-paddingtb">

<div style="text-align: right; margin: 5px 0 15px 0;">
    <a href="<?= $edit_url ?>new" class="submit"><?= lang('store.discount_new') ?></a>
</div>

<?= form_open($post_url) ?>

<?php
    $this->table->clear();
    $this->table->set_template($store_sortable_table_template);
    $this->table->set_heading(
        array('data' => NBS, 'width' => '2%'),
        array('data' => '#', 'width' => '2%'),
        array('data' =>lang('name'), 'width' => '10%'),
        array('data' =>lang('store.code'), 'width' => '10%'),
        array('data' =>lang('store.sale_start_date'), 'width' => '12%'),
        array('data' =>lang('store.sale_end_date'), 'width' => '12%'),
        array('data' =>lang('store.base_discount'), 'width' => '12%'),
        array('data' =>lang('store.per_item_discount'), 'width' => '12%'),
        array('data' =>lang('store.percent_discount'), 'width' => '12%'),
        array('data' =>lang('store.status'), 'width' => '12%'),
        array('data' => form_checkbox(array('id' => 'checkall')), 'width' => '5%')
    );

    foreach ($discounts as $discount) {
        $this->table->add_row(
            '<div class="store_sortable_handle"></div>',
            form_hidden('sorted_ids[]', $discount->id).$discount->id,
            '<a href="'.$edit_url.$discount->id.'">'.$discount->name.'</a>',
            $discount->code,
            $discount->start_date_str,
            $discount->end_date_str,
            store_currency($discount->base_discount),
            store_currency($discount->per_item_discount),
            $discount->percent_discount ? ((float) $discount->percent_discount).'%' : null,
            store_enabled_str($discount->enabled),
            form_checkbox('selected[]', $discount->id, false)
        );
    }

    if (!count($discounts)) {
        $this->table->add_row(array('data' => '<i>'.lang('store.no_discounts').'</i>', 'colspan' => 11));
    }

    echo $this->table->generate();
?>

<div style="text-align: right;">
    <?= form_dropdown('with_selected', array('enable' => lang('store.enable_selected'), 'disable' => lang('store.disable_selected'), 'delete' => lang('store.delete_selected'))) ?>
    <?= form_submit(array('name' => 'submit', 'value' => lang('store.submit'), 'class' => 'submit')) ?>
</div>

<?= form_close() ?>

</div>