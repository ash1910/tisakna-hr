<?php

namespace Store\Service;

use Store\Model\Tax;
use Store\Model\OrderItem;

class TaxService extends AbstractService
{
    public function is_item_taxable(OrderItem $item, Tax $tax)
    {
        if ($tax->tax_override === true) {
            return true;
        }

        if ($item->tax_exempt == 1) {
            return false;
        }

        if (!$tax->category_ids) {
            return true;
        }

        return array_intersect($item->category_ids, $tax->category_ids);

        //return $tax->tax_override || !$tax->category_ids || array_intersect($item->category_ids, $tax->category_ids);
    }
}