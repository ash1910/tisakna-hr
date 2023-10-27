<div class="modal fade"><div class="modal-dialog"><div class="modal-content">

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title"><?=lang('store.edit_shipping')?></h4>
</div>

<div class="modal-body">
    <?=form_hidden('order_id', $order->id);?>
    <?=form_hidden('section', 'shipping');?>

    <table class="store_form" style="width:100%">
    <tbody>
        <tr>
            <td style="width: 150px"><?=lang('store.shipping_method')?></td>
            <td><input name="shipping_name" type="text" min="0" value="<?=$shipping['shipping_name']?>" class="text"></td>
        </tr>
        <tr>
            <td><?=lang('store.shipping_amount')?></td>
            <td><input name="shipping_amount" type="text" min="0" value="<?=$shipping['shipping_amount']?>" class="text"></td>
        </tr>
    </tbody>
    </table>

    <p><?=lang('store.shipping_edit_override_desc')?></p>
</div>

<div class="modal-footer">
    <button type="button" class="btn" data-dismiss="modal"><?=lang('store.cancel')?></button>
    <button type="button" class="btn btn-green modal-save"><?=lang('store.save')?></button>
</div>

</div></div></div>