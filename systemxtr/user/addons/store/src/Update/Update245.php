<?php

namespace Store\Update;

class Update245
{
    public function up()
    {
        // shipping_override to exp_store_orders
        if (ee()->db->field_exists('shipping_override', 'store_orders') === false) {
            $fields = array('shipping_override' => array('type' => 'TEXT'));
            ee()->dbforge->add_column('store_orders', $fields, 'shipping_method_rule');
        }
    }
}
