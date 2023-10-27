<div class="modal fade"><div class="modal-dialog"><div class="modal-content">

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title"><?=lang('store.item')?></h4>
</div>

<ul class="osections-toggler osections-enabled">
    <li class="selected"><a href="#" data-section="general"><?=lang('store.general')?></a></li>
    <li><a href="#" data-section="dimensions"><?=lang('store.dimensions')?></a></li>
    <li><a href="#" data-section="misc"><?=lang('store.options')?></a></li>
</ul>

<div class="modal-body">
    <?=form_hidden('order_id', $order->id);?>
    <?=form_hidden('item_id', $item->id);?>
    <?=form_hidden('section', 'item');?>

    <?php if ($item->id):?>
    <?=form_hidden('entry_id', $item->entry_id);?>
    <?php endif;?>

    <div class="osections section-general">
        <table class="store_form" style="width:100%">
        <tbody>
            <?php if (!$item->id):?>
            <tr>
                <td><?=lang('store.product')?></td>
                <td>
                    <input type="hidden" name="entry_id" class="select2-ajax item-entry_id" data-url="<?=store_cp_url('select2', 'products')?>" style="width:100%">
                </td>
            </tr>
            <?php endif;?>
            <tr>
                <td><?=lang('store.quantity')?></td>
                <td><?=form_input('item_qty', $item->item_qty, ' class="text" ')?></td>
            </tr>
            <tr>
                <td style="width: 150px"><?=lang('store.product_title')?></td>
                <td><?=form_input('title', $item->title, ' class="text" placeholder="'.lang('store.item_empty_inheritance').'" ')?></td>
            </tr>
            <tr>
                <td><?=lang('store.price')?></td>
                <td><?=form_input('price', store_currency_cp($item->price), ' class="text" placeholder="'.lang('store.item_empty_inheritance').'" ')?></td>
            </tr>
            <tr>
                <td><?=lang('store.handling')?></td>
                <td><?=form_input('handling', store_currency_cp($item->handling), ' class="text" placeholder="'.lang('store.item_empty_inheritance').'" ')?></td>
            </tr>
        </tbody>
        </table>

        <?php if (!$item->id):?>
        <div class="modifiers"></div>
        <?php else:?>
        <div class="modifiers"><?=$modifiers_html?></div>
        <?php endif;?>
    </div>
    <div class="osections section-dimensions">
        <table class="store_form" style="width:100%">
        <tbody>
            <tr>
                <td style="width:150px"><?=sprintf(lang('store.weight_with_units'), config_item('store_weight_units'))?></td>
                <td><?=form_input('weight', $item->weight, ' class="text" placeholder="'.lang('store.item_empty_inheritance').'" ')?></td>
            </tr>
            <tr>
                <td><?=sprintf(lang('store.length_with_units'), config_item('store_dimension_units'))?></td>
                <td><?=form_input('length', $item->length, ' class="text" placeholder="'.lang('store.item_empty_inheritance').'" ')?></td>
            </tr>
            <tr>
                <td><?=sprintf(lang('store.width_with_units'), config_item('store_dimension_units'))?></td>
                <td><?=form_input('width', $item->width, ' class="text" placeholder="'.lang('store.item_empty_inheritance').'" ')?></td>
            </tr>
            <tr>
                <td><?=sprintf(lang('store.height_with_units'), config_item('store_dimension_units'))?></td>
                <td><?=form_input('height', $item->height, ' class="text" placeholder="'.lang('store.item_empty_inheritance').'" ')?></td>
            </tr>
        </tbody>
        </table>
    </div>

    <div class="osections section-misc">
        <table class="store_form" style="width:100%">
        <tbody>
            <tr>
                <td><?=lang('store.tax_exempt')?></td>
                <td>
                    <input type="radio" name="tax_exempt" value="y" <?php if ($item->tax_exempt == 1) echo "checked";?> > <?=lang('yes')?>&nbsp;&nbsp;
                    <input type="radio" name="tax_exempt" value="n" <?php if ($item->tax_exempt == 0) echo "checked";?>> <?=lang('no')?>
                </td>
            </tr>
            <tr>
                <td><?=lang('store.free_shipping')?></td>
                <td>
                    <input type="radio" name="free_shipping" value="y" <?php if ($item->free_shipping == 1) echo "checked";?> > <?=lang('yes')?>&nbsp;&nbsp;
                    <input type="radio" name="free_shipping" value="n" <?php if ($item->free_shipping == 0) echo "checked";?>> <?=lang('no')?>
                </td>
            </tr>
        </tbody>
        </table>
    </div>

</div>

<div class="modal-footer">
    <?php if ($item->id):?>
    <button type="button" class="btn btn-red modal-save modal-delete" style="float:left"><?=lang('store.delete')?></button>
    <?php endif;?>

    <button type="button" class="btn" data-dismiss="modal"><?=lang('store.cancel')?></button>
    <button type="button" class="btn btn-green modal-save"><?=lang('store.save')?></button>
</div>

</div></div></div>