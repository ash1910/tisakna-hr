<?php

namespace Store\Tag;

use Store\Model\OrderItem;

/**
 * {exp:store:purchased}
 *
 * @package         DevDemon_Store
 * @author          DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright       Copyright (c) 2007-2015 Parscale Media <http://www.parscale.com>
 * @license         http://www.devdemon.com/license/
 * @link            http://www.devdemon.com
 */
class PurchasedTag extends AbstractTag
{
    public function parse()
    {
        // -----------------------------------------
        // Member ID
        // -----------------------------------------
        $member_id = $this->param('member_id', $this->ee->session->userdata['member_id']);

        if (!$member_id) {
            $this->log('Member ID is empty');
            return $this->no_results('not_purchased');
        }

        if ($member_id == 'CURRENT_USER') {
            $member_id = $this->ee->session->userdata['member_id'];
        }

        // Multiple Authors?
        if (strpos($member_id, '|') !== false) {
            $member_id = explode('|', $member_id);
        } else {
            $member_id = array($member_id);
        }

        // -----------------------------------------
        // Entry ID
        // -----------------------------------------
        $entry_id = $this->param('entry_id');

        if (!$entry_id) {
            $this->log('Entry ID is empty');
            return $this->no_results('not_purchased');
        }

        // Multiple Authors?
        if (strpos($entry_id, '|') !== false) {
            $entry_id = explode('|', $entry_id);
        } else {
            $entry_id = array($entry_id);
        }


        $items = OrderItem::whereHas('order', function($query) use ($member_id) {
            $query->whereIn('member_id', $member_id);
            $query->where('site_id', config_item('site_id'));
            $query->where('order_completed_date', '>', 0);
        })->whereIn('entry_id', $entry_id)->get();

        if ($items->count() == 0) {
            $this->log('Did not find any orders');
            return $this->no_results('not_purchased');
        }

        // Found orders! Jeej!
        return $this->no_results('purchased');
    }
}
