<?php

/*
 * Exp:resso Store module for ExpressionEngine
 * Copyright (c) 2010-2014 Exp:resso (support@exp-resso.com)
 */

namespace Store\Action;

use Store\FormValidation;
use Store\Model\Order;
use Store\Model\Transaction;

class PaymentAction extends AbstractAction
{
    public static $form_errors;

    public function perform()
    {
        $order = Order::where('site_id', config_item('site_id'))
            ->where('order_hash', ee()->input->post('order_hash'))
            ->first();

        if (empty($order)) {
            return ee()->output->show_user_error('general', array(lang('not_authorized')));
        }

        if ($order->is_order_paid) {
            ee()->session->set_flashdata(array('store_payment_error'=>lang('order_already_paid')));
            ee()->functions->redirect($order->parsed_return_url);
        }

        $order->payment_method = ee()->input->post('payment_method', true);
        $order->return_url = $this->get_return_url();
        $order->cancel_url = ee()->store->store->create_url();

        $form_validation = new FormValidation();
	    $form_validation->add_rules_from_params($this->form_params());
	    $form_validation->add_rules('payment_method', 'lang:store.payment_method', 'required|valid_payment_method');

         if ($form_validation->run()) {
            // prevent duplicate payment form submissions
            $this->invalidate_csrf_token();

            $order->save();
            // process payment info
            $credit_card = ee()->input->post('payment');
            $transaction = ee()->store->payments->new_transaction($order);
            $transaction->amount = $order->order_owing;
            $transaction->payment_method = $order->payment_method;
            ee()->store->payments->process_payment($order, $transaction, $credit_card);
        }

        static::$form_errors = $form_validation->error_array();

        if ($this->form_param('error_handling') != 'inline') {
            ee()->output->show_user_error(false, static::$form_errors);
        }

        return ee()->core->generate_page();
    }
}
