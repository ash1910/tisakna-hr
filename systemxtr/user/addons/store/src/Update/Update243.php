<?php

namespace Store\Update;

class Update243
{
    public function up()
    {
        // tax_override to exp_store_orders
        if (ee()->db->field_exists('tax_override', 'store_orders') === false) {
            $fields = array('tax_override' => array('type' => 'TEXT'));
            ee()->dbforge->add_column('store_orders', $fields, 'tax_rate');
        }
    }
}
