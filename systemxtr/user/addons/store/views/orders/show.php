<div id="single_order" class="container-fluid" data-id="<?=$order->id?>">

<div class="row pagehead">
    <div class="col-xs-6">
        <h2><i class="fa fa-list-ul"></i> <?=lang('store.order_details')?></h2>
    </div>
    <div class="col-xs-6" style="text-align:right">
        <?php if ($invoice_link): ?>
        <a href="<?=$invoice_link;?>" class="btn btn-default" target="_blank"><?= lang('store.show_invoice'); ?></a>
        <?php endif; ?>
        <a href="<?=$print_url?>" class="btn btn-default" target="_blank"><?=lang('store.print')?></a>
    </div>
</div>


<div class="row">

<div class="col-xs-6">
    <div class="dtable">
        <h2>
            <?=lang('store.order')?> #<?=$order->order_id?>
            <button style="float:right;position:relative;top:-2px;" class="btn btn-sm btn-default edit_order" data-section="custom_fields"><?=lang('store.edit_custom_fields')?></button>
        </h2>
        <table><tbody>
            <tr>
                <td style="width:150px"><?=lang('store.order_id')?></td>
                <td>
                    <?=$order->order_id?>
                    <span style="float:right"><?=lang('store.cart_id')?>: <?=$order->id?></span>
                </td>
            </tr>
            <tr>
                <td><?=lang('store.order_total')?></td>
                <td><?=store_currency($order->order_total)?></td>
            </tr>
            <tr>
                <td><?=lang('store.payment_method')?></td>
                <td><?=$order->payment_method?></td>
            </tr>
            <tr>
                <td><?=lang('store.paid?')?></td>
                <td><?=store_order_paid($order)?></td>
            </tr>
            <tr>
                <td><?=lang('store.orders.order_status')?></td>
                <td>
                    <?= store_order_status($order) ?>
                    <?php if ($order->order_completed_date): ?>
                        (<?= $this->localize->human_time($order->order_status_updated) ?>)
                        <a id="store_status_edit" href="#"><?= lang('store.edit_status') ?></a>

                        <div id="store_status_form" style="display: none;">
                            <br />
                            <?= form_open($update_status_url).
                                lang('store.orders.order_status').': '.form_dropdown('status_id', $status_select_options).BR.BR.
                                lang('store.message').': '.form_input('message','','width=10%, style=width:50%').BR.BR.
                                form_hidden('order_id', (int) $order->id) ?>
                                <div style="text-align:right">
                                    <a id="store_status_cancel" href="#"><?= lang('cancel') ?></a> &nbsp;&nbsp;
                                    <?= form_submit(array('name' => 'action_submit', 'value' => lang('store.submit'), 'class' => 'submit', 'id' => 'status_submit')) ?>
                                </div>
                            <?= form_close(); ?>
                        </div>
                    <?php endif ?>
                </td>
            </tr>
        </tbody></table>
    </div>
</div> <!-- .col-xs-6 -->

<div class="col-xs-6">
<div class="dtable">
        <h2><?=lang('store.member')?></h2>
        <table><tbody>
            <tr>
                <td style="width:150px"><?=lang('store.member')?></td>
                <td><?=store_member_link($order->member)?></td>
            </tr>
        </tbody></table>
    </div>
</div> <!-- .col-xs-6 -->

</div> <!-- .row -->


<?php if ($has_custom_fields):?>
<div class="dtable">
    <h2><?=lang('store.custom_fields')?></h2>
    <table><tbody>
        <?php foreach ($order_fields as $field_name => $field): ?>
            <?php if (strpos($field_name, 'order_custom') !== FALSE): ?>
                <?php if ($field['title'] != '' || $order->$field_name != ''): ?>
                    <tr>
                        <td style="width:150px"><?=$field['title'] == '' ? $field_name : $field['title']?></td>
                        <td><?=$order->$field_name?></td>
                    </tr>
                <?php endif ?>
            <?php endif ?>
        <?php endforeach ?>
    </tbody></table>
</div>
<?php endif;?>


<div class="row">

<div class="col-xs-6">
<div class="dtable">
        <h2>
            <?=lang('store.billing_details')?>
            <?php if ($order->billing_same_as_shipping == 1):?>
            <em style="font-size:10px;font-weight:normal"><?=lang('store.billing_same_as_shipping')?></em>
            <?php endif;?>
            <button style="float:right;position:relative;top:-2px;" class="btn btn-sm btn-default edit_order" data-section="billing_details"><?=lang('store.edit')?></button>
        </h2>
        <table><tbody>
            <tr>
                <td style="width:150px"><?=lang('store.billing_name')?></td>
                <td><?=$order->billing_name?></td>
            </tr>
            <tr>
                <td><?=lang('store.billing_company')?></td>
                <td><?=$order->billing_company?></td>
            </tr>
            <tr>
                <td><?=lang('store.billing_address')?></td>
                <td><?=$order->billing_address_full?></td>
            </tr>
            <tr>
                <td><?=lang('store.billing_phone')?></td>
                <td><?=$order->billing_phone?></td>
            </tr>
            <tr>
                <td><?=lang('store.order_email')?></td>
                <td><?=$order->order_email?></td>
            </tr>
        </tbody></table>
    </div>
</div> <!-- .col-xs-6 -->

<div class="col-xs-6">
<div class="dtable">
        <h2>
            <?=lang('store.shipping_details')?>
            <?php if ($order->shipping_same_as_billing == 1):?>
            <em style="font-size:10px;font-weight:normal"><?=lang('store.shipping_same_as_billing')?></em>
            <?php endif;?>
            <button style="float:right;position:relative;top:-2px;" class="btn btn-sm btn-default edit_order" data-section="shipping_details"><?=lang('store.edit')?></button>
        </h2>
        <table><tbody>
            <tr>
                <td style="width:150px"><?=lang('store.shipping_name')?></td>
                <td><?=$order->shipping_name?></td>
            </tr>
            <tr>
                <td><?=lang('store.shipping_company')?></td>
                <td><?=$order->shipping_company?></td>
            </tr>
            <tr>
                <td><?=lang('store.shipping_address')?></td>
                <td><?=$order->shipping_address_full?></td>
            </tr>
            <tr>
                <td><?=lang('store.shipping_phone')?></td>
                <td><?=$order->shipping_phone?></td>
            </tr>
            <tr>
                <td><strong><?=lang('store.shipping_method')?></strong></td>
                <td><?=$order->shipping_method_name?></td>
            </tr>
            <?php if ($order->promo_code): ?>
                <tr>
                    <td><?=lang('store.promo_code')?></td>
                    <td><?=$order->promo_code?></td>
                </tr>
            <?php endif; ?>
        </tbody></table>
    </div>
</div> <!-- .col-xs-6 -->

</div> <!-- .row -->



<div class="dtable">
    <h2>
        <?=lang('store.items')?>
        <button style="float:right;position:relative;top:-2px;margin-left:5px;" class="btn btn-sm btn-default edit_order" data-section="item"><?=lang('store.item_add')?></button>
        <button style="float:right;position:relative;top:-2px;margin-left:5px;" class="btn btn-sm btn-default edit_order" data-section="shipping"><?=lang('store.edit_shipping')?></button>
        <button style="float:right;position:relative;top:-2px;" class="btn btn-sm btn-default edit_order" data-section="tax"><?=lang('store.edit_tax')?></button>

    </h2>
    <table>
        <thead>
            <tr class="heading">
                <th style="width:50px"><?=lang('store.#')?></th>
                <th><?=lang('store.product')?></th>
                <th><?=lang('store.sku')?></th>
                <th><?=lang('store.modifiers')?></th>
                <th style="width:100px"><?=lang('store.price')?></th>
                <th style="width:50px"><?=lang('store.quantity')?></th>
                <th style="width:100px"><?=lang('store.total')?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order->items as $item):?>
            <tr>
                <td><a href="#" class="edit_order" data-section="item" data-item_id="<?=$item->id?>"><?=$item->entry_id?></a></td>
                <td><?=$item->title?></td>
                <td><?=$item->sku?></td>
                <td><?=$item->modifiers_html?></td>
                <td><?=store_currency($item->price)?></td>
                <td><?=$item->item_qty?></td>
                <td class="text-right"><?=store_currency($item->item_subtotal)?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right"><strong><?=lang('store.order_subtotal')?></strong></td>
                <td class="text-right"><?=store_currency($order->order_subtotal)?></td>
            </tr>

            <?php if (count($order->adjustments) > 0):?>
                <?php foreach ($order->adjustments as $adjustment):?>
                <tr>
                    <td colspan="6" class="text-right"><?=$adjustment->label?></td>
                    <td class="text-right"><?=store_currency($adjustment->amount)?></td>
                </tr>
                <?php endforeach;?>
            <?php elseif ($order->order_discount > 0 || $order->order_shipping > 0 || $order->order_tax > 0):?>
                <!-- // legacy attributes for orders without adjustments -->
                <tr>
                    <td colspan="6" class="text-right"><strong><?=lang('store.order_discount')?></strong></td>
                    <td class="text-right"><?=store_currency(-$order->order_discount)?></td>
                </tr>
                <tr>
                    <td colspan="6" class="text-right"><strong><?=lang('store.order_shipping').' ('.$order->shipping_method_name.')'?></strong></td>
                    <td class="text-right"><?=store_currency($order->order_shipping)?></td>
                </tr>
                <tr>
                    <td colspan="6" class="text-right"><strong><?=empty($order->tax_name) ? lang('store.order_tax') : $order->tax_name.' ('.((double) $order->tax_rate*100).'%)'?></strong></td>
                    <td class="text-right"><?=store_currency($order->order_tax)?></td>
                </tr>
            <?php endif;?>

            <tr>
                <td colspan="6" class="text-right"><strong><?=lang('store.order_total')?></strong></td>
                <td class="text-right"><?=store_currency($order->order_total)?></td>
            </tr>
            <tr>
                <td colspan="6" class="text-right"><?=lang('store.paid')?></td>
                <td class="text-right"><?=store_currency($order->order_paid)?></td>
            </tr>
            <tr>
                <td colspan="6" class="text-right"><strong><?=lang('store.balance_due')?></strong></td>
                <td class="text-right"><?=store_currency($order->order_owing)?></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="dtable transaction">
    <h2><?=lang('store.transactions')?></h2>
    <table>
        <thead>
        <tr class="heading">
            <th>#</th>
            <th><?=lang('date')?></th>
            <th><?=lang('store.payment_method')?></th>
            <th><?=lang('type')?></th>
            <th><?=lang('store.amount')?></th>
            <th><?=lang('store.status')?></th>
            <th><?=lang('store.reference')?></th>
            <th><?=lang('store.message')?></th>
            <th><?=lang('store.recorded_by')?></th>
            <th class="store_transaction_actions"><?=lang('store.actions')?></th>
        </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transaction):?>
            <tr>
                <td><?=$transaction->id?></td>
                <td><?=$this->localize->human_time($transaction->date)?></td>
                <td><?=$transaction->payment_method?></td>
                <td><?=store_transaction_status($transaction->type)?></td>
                <td><?=store_currency($transaction->amount)?></td>
                <td><?=store_transaction_status($transaction->status)?></td>
                <td><?=$transaction->reference?></td>
                <td><?=$transaction->message?></td>
                <td><?=store_member_link($transaction->member, lang('store.system'))?></td>
                <td class="store_transaction_actions"><?=store_transaction_actions($transaction)?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (count($transactions) == 0):?>
                <tr><td colspan="99"><i><?=lang('store.no_transactions')?></i></td></tr>
            <?php endif;?>
        </tbody>
        <?php if ($can_add_payments): ?>
        <tfoot class="hide_from_print">
            <tr><td colspan="99">
                <a href="<?= $new_payment_url ?>" class="submit"><?= lang('store.new_payment') ?></a>
            </td></tr>
        </tfoot>
        <?php endif ?>
    </table>
</div>


<div class="dtable">
    <h2><?=lang('store.order_status_history')?></h2>
    <table>
        <thead>
        <tr class="heading">
            <th><?=lang('store.status')?></th>
            <th><?=lang('store.order_status_updated')?></th>
            <th><?=lang('store.updated_by')?></th>
            <th><?=lang('store.message')?></th>
        </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $item):?>
            <tr>
                <td><?=store_order_status_name($item->order_status_name)?></td>
                <td><?=$this->localize->human_time($item->order_status_updated)?></td>
                <td><?=store_member_link($item->member, lang('store.system'))?></td>
                <td><?=$item->order_status_message?></td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</div>


</div> <!-- .container -->