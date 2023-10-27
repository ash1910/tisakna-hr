<div class="modal fade"><div class="modal-dialog"><div class="modal-content">

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title"><?=lang('store.edit_custom_fields')?></h4>
</div>

<div class="modal-body">
    <?=form_hidden('order_id', $order->id);?>
    <?=form_hidden('section', 'custom_fields');?>

    <table class="store_form" style="width:100%">
    <tbody>
        <tr>
            <td style="width: 150px"><?=lang('store.custom_field')?> #1</td>
            <td><?=form_input('order_custom1', $order->order_custom1, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.custom_field')?> #2</td>
            <td><?=form_input('order_custom2', $order->order_custom2, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.custom_field')?> #3</td>
            <td><?=form_input('order_custom3', $order->order_custom3, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.custom_field')?> #4</td>
            <td><?=form_input('order_custom4', $order->order_custom4, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.custom_field')?> #5</td>
            <td><?=form_input('order_custom5', $order->order_custom5, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.custom_field')?> #6</td>
            <td><?=form_input('order_custom6', $order->order_custom6, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.custom_field')?> #7</td>
            <td><?=form_input('order_custom7', $order->order_custom7, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.custom_field')?> #8</td>
            <td><?=form_input('order_custom8', $order->order_custom8, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.custom_field')?> #9</td>
            <td><?=form_input('order_custom9', $order->order_custom9, ' class="text" ')?></td>
        </tr>
    </tbody>
    </table>
</div>

<div class="modal-footer">
    <button type="button" class="btn" data-dismiss="modal"><?=lang('store.cancel')?></button>
    <button type="button" class="btn btn-green modal-save"><?=lang('store.save')?></button>
</div>

</div></div></div>