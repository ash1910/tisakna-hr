<div class="modal fade"><div class="modal-dialog"><div class="modal-content">

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title"><?=lang('store.shipping_details')?></h4>
</div>

<div class="modal-body">
    <?=form_hidden('order_id', $order->id);?>
    <?=form_hidden('section', 'shipping_details');?>

    <table class="store_form" style="width:100%">
    <tbody>
        <tr>
            <td style="width: 150px"><?=lang('store.shipping_first_name')?></td>
            <td><?=form_input('shipping_first_name', $order->shipping_first_name, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.shipping_last_name')?></td>
            <td><?=form_input('shipping_last_name', $order->shipping_last_name, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.shipping_address1')?></td>
            <td><?=form_input('shipping_address1', $order->shipping_address1, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.shipping_address2')?></td>
            <td><?=form_input('shipping_address2', $order->shipping_address2, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.shipping_city')?></td>
            <td><?=form_input('shipping_city', $order->shipping_city, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.shipping_state')?></td>
            <td><?=form_input('shipping_state', $order->shipping_state, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.shipping_country')?></td>
            <td><select name="shipping_country"><?=$country_options?></select></td>
        </tr>
        <tr>
            <td><?=lang('store.shipping_postcode')?></td>
            <td><?=form_input('shipping_postcode', $order->shipping_postcode, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.shipping_phone')?></td>
            <td><?=form_input('shipping_phone', $order->shipping_phone, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.shipping_company')?></td>
            <td><?=form_input('shipping_company', $order->shipping_company, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.shipping_same_as_billing')?></td>
            <td>
                <input type="radio" name="shipping_same_as_billing" value="1" <?php if ($order->shipping_same_as_billing == 1) echo "checked";?> > <?=lang('yes')?>&nbsp;&nbsp;
                <input type="radio" name="shipping_same_as_billing" value="0" <?php if ($order->shipping_same_as_billing == 0) echo "checked";?>> <?=lang('no')?>
            </td>
        </tr>
    </tbody>
    </table>
</div>

<div class="modal-footer">
    <button type="button" class="btn" data-dismiss="modal"><?=lang('store.cancel')?></button>
    <button type="button" class="btn btn-green modal-save"><?=lang('store.save')?></button>
</div>

</div></div></div>