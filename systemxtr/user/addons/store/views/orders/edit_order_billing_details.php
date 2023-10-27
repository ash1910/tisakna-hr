<div class="modal fade"><div class="modal-dialog"><div class="modal-content">

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title"><?=lang('store.billing_details')?></h4>
</div>

<div class="modal-body">
    <?=form_hidden('order_id', $order->id);?>
    <?=form_hidden('section', 'billing_details');?>

    <table class="store_form" style="width:100%">
    <tbody>
        <tr>
            <td style="width: 150px"><?=lang('store.billing_first_name')?></td>
            <td><?=form_input('billing_first_name', $order->billing_first_name, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.billing_last_name')?></td>
            <td><?=form_input('billing_last_name', $order->billing_last_name, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.billing_address1')?></td>
            <td><?=form_input('billing_address1', $order->billing_address1, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.billing_address2')?></td>
            <td><?=form_input('billing_address2', $order->billing_address2, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.billing_city')?></td>
            <td><?=form_input('billing_city', $order->billing_city, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.billing_state')?></td>
            <td><?=form_input('billing_state', $order->billing_state, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.billing_country')?></td>
            <td><select name="billing_country"><?=$country_options?></select></td>
        </tr>
        <tr>
            <td><?=lang('store.billing_postcode')?></td>
            <td><?=form_input('billing_postcode', $order->billing_postcode, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.billing_phone')?></td>
            <td><?=form_input('billing_phone', $order->billing_phone, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.billing_company')?></td>
            <td><?=form_input('billing_company', $order->billing_company, ' class="text" ')?></td>
        </tr>
        <tr>
            <td><?=lang('store.billing_same_as_shipping')?></td>
            <td>
                <input type="radio" name="billing_same_as_shipping" value="1" <?php if ($order->billing_same_as_shipping == 1) echo "checked";?> > <?=lang('yes')?>&nbsp;&nbsp;
                <input type="radio" name="billing_same_as_shipping" value="0" <?php if ($order->billing_same_as_shipping == 0) echo "checked";?>> <?=lang('no')?>
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