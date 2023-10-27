<?php

namespace Store\Update;

class Update242
{
    public function up()
    {
        // Order ID to exp_store_orders
        if (ee()->db->field_exists('order_id', 'store_orders') === false) {
            $fields = array('order_id' => array('type' => 'INT', 'unsigned' => true,'default'=>NULL));
            ee()->dbforge->add_column('store_orders', $fields, 'site_id');
        }

        $orders = ee()->db->select('id')->from('store_orders')->where('order_completed_date >', 0)->get();

        foreach ($orders->result() as $order) {
            ee()->db->update('store_orders', array('order_id' => $order->id), array('id' => $order->id));
        }
    }
}
