<?php

namespace Store\Update;

class Update244
{
    public function up()
    {
        // match_items_on to exp_store_discounts
        if (ee()->db->field_exists('match_items_on', 'store_discounts') === false) {
            $fields = array('match_items_on' => array('type' => 'varchar', 'constraint' => 50));
            ee()->dbforge->add_column('store_discounts', $fields, 'discount_qty');
        }
    }
}
