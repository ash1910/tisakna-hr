<?php

/*
 * Exp:resso Store module for ExpressionEngine
 * Copyright (c) 2010-2014 Exp:resso (support@exp-resso.com)
 */

namespace Store\Model;

class OrderItem extends AbstractModel
{
    protected $table = 'store_order_items';

    protected $currency_attributes = array('price', 'regular_price', 'price_inc_tax',
        'regular_price_inc_tax', 'you_save', 'you_save_inc_tax',
        'handling', 'handling_tax', 'handling_inc_tax',
        'item_subtotal', 'item_discount', 'item_subtotal_inc_discount',
        'item_tax', 'item_total');

    public static function createFromStock(Stock $stock)
    {
        $product = $stock->product;

        $item = new static;
        $item->stock_id = $stock->id;
        $item->entry_id = $product->entry_id;
        $item->title = $product->entry->title;
        $item->url_title = $product->entry->url_title;
        $item->channel_id = $product->entry->channel_id;

        // record category ids for calculating tax and discounts
        $item->category_ids = ee()->store->db->table('category_posts')
            ->where('entry_id', $product->entry_id)
            ->lists('cat_id');

        $item->price = $product->sale_price;
        $item->regular_price = $product->regular_price;
        $item->on_sale = $product->on_sale;
        $item->length = $product->length;
        $item->width = $product->width;
        $item->height = $product->height;
        $item->weight = $product->weight;
        $item->handling = (float) $product->handling;
        $item->free_shipping = $product->free_shipping;

        return $item;
    }

    public function stock()
    {
        return $this->belongsTo('\Store\Model\Stock');
    }

    public function product()
    {
        return $this->belongsTo('\Store\Model\Product', 'entry_id');
    }

    public function order()
    {
        return $this->belongsTo('\Store\Model\Order');
    }

    public function getCategoryIdsAttribute()
    {
        return $this->getPipeArrayAttribute('category_ids');
    }

    public function setCategoryIdsAttribute($value)
    {
        return $this->setPipeArrayAttribute('category_ids', $value);
    }

    public function getHandlingIncTaxAttribute()
    {
        return $this->handling + $this->handling_tax;
    }

    public function getItemSubtotalIncDiscountAttribute()
    {
        return $this->item_subtotal - $this->item_discount;
    }

    public function getModifiersAttribute($value)
    {
        $modifiers = json_decode($value, true);

        return empty($modifiers) ? array() : $modifiers;
    }

    public function getModifiersHtmlAttribute()
    {
        $html = array();

        foreach ($this->modifiers as $mod) {
            if ($mod && $mod['modifier_name'] && $mod['modifier_value']) {
                $html[] = '<strong>'.$mod['modifier_name'].'</strong>: '.$mod['modifier_value'];
            }
        }

        return implode(', ', $html);
    }

    public function setModifiersAttribute($value)
    {
        $this->attributes['modifiers'] = json_encode($value);
    }

    public function getDimensionsAttribute()
    {
        $dimensions = array((float) $this->length, (float) $this->width, (float) $this->height);
        sort($dimensions);

        return $dimensions;
    }

    public function getYouSaveAttribute()
    {
        return max(0, $this->regular_price - $this->price);
    }

    public function getYouSaveIncTaxAttribute()
    {
        return $this->regular_price_inc_tax - $this->price_inc_tax;
    }

    public function getYouSavePercentAttribute()
    {
        // avoid divide by zero error on free products
        if ($this->regular_price > 0) {
            return (int) round($this->you_save / $this->regular_price * 100);
        } else {
            return 0;
        }
    }

    public function recalculate()
    {
        // skip qty validation if there is no related stock item
        $stock = $this->stock;

        if ($stock) {

            // check if the product has a minimum order qty
            if ($this->item_qty > 0 && $stock->min_order_qty > 1 && $this->item_qty < $stock->min_order_qty) {
                $this->item_qty = $stock->min_order_qty;
            }

            //--------------------------------------------
            // Get Quantities & Left for Same SKU/Entry ID
            //--------------------------------------------

            // We start with zero of course
            $qty = 0;

            // And we have left whats in stock
            $left = $stock->stock_level;

            // Only if we have an SKU and the item already exists in the DB
            // In some cases this might show the item in the cart but on recalculate it might remove it (depending on stock)
            if ($stock->sku != false && $this->exists) {
                foreach ($this->order->items as $item) {
                    // Not the same entry_id ? continue
                    if ($stock->entry_id != $item->stock->entry_id) continue;

                    // Not the same SKU? continue
                    if ($stock->sku != $item->stock->sku) continue;

                    // If we arrived to the current item object we should stop here.!!
                    if ($this->id == $item->id) break;

                    // Add the total qty for this SKU
                    $qty += $item->item_qty;

                    // Substract the total left for this SKU
                    $left -= $item->item_qty;
                }
            }

            // Just in case went below zero
            if (!$left) $left = 0;

            // Just in case somehow the quantity stayed zero
            if (!$qty) $qty = $this->item_qty;

            //--------------------------------------------
            // Apply Stock Rules
            //--------------------------------------------

            // Only if we are tracking stock of course
            if ($stock->track_stock) {

                // if we don't allow backorders,
                // then user cannot order more than we have in stock
                if ($qty > $stock->stock_level) {
                    $this->item_qty = $left;

                    // make sure new item_qty complies with min_order_qty
                    if ($stock->min_order_qty > 1 && $this->item_qty < $stock->min_order_qty) {
                        // this product is essentially out of stock
                        // because the current stock level is below minimum order qty
                        // so let's just remove it from the cart
                        $this->item_qty = 0;
                    }
                } else {
                    // If the current item quantity is higher then whats left, adjust it.
                    if ($this->item_qty > $left) {
                        $this->item_qty = $left;
                    }
                }
            }
        }

        $this->handling_tax = 0;
        $this->item_subtotal = store_round_currency($this->price * $this->item_qty);
        $this->item_discount = 0;
        $this->item_tax = 0;
        $this->item_total = $this->item_subtotal;
    }

    public function toTagArray()
    {
        $attributes = parent::toTagArray();
        $attributes['key'] = $this->id;
        $attributes['page_uri'] = $this->page_uri;
        $attributes['page_url'] = $this->page_url;
        $attributes['you_save_percent'] = $this->you_save_percent;

        // Modifiers
        $attributes['modifiers'] = $this->modifiers;

        if ($attributes['modifiers']) {
            foreach ($attributes['modifiers'] as $key => &$mod) {
                if (isset($mod['price_mod']) === false) continue;
                $mod['price_mod_val'] = $mod['price_mod'];
                $mod['price_mod'] = store_currency($mod['price_mod']);
            }
        }

        // TODO: Figure out if adding a getOnSaleAttribute() method is a better idea
        $attributes['on_sale'] = $this->on_sale ? true : false;
        $attributes['free_shipping'] = $this->free_shipping ? true : false;

        // backwards compatibility with Store 1.x
        $attributes['dimension_l'] = $attributes['length'];
        $attributes['dimension_w'] = $attributes['width'];
        $attributes['dimension_h'] = $attributes['height'];

        $page = ee()->store->store->get_entry_page_url($this->site_id, $this->entry_id);
        $attributes = array_merge($attributes, $page);

        return $attributes;
    }
}
