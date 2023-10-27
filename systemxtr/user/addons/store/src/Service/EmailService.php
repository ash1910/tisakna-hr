<?php

/*
 * Exp:resso Store module for ExpressionEngine
 * Copyright (c) 2010-2014 Exp:resso (support@exp-resso.com)
 */

namespace Store\Service;

use EE_Template;
use Store\Model\Email;
use Store\Model\Order;

class EmailService extends AbstractService
{
    protected $snippets;

    /**
     * Send an email
     */
    public function send(Email $email, Order $order)
    {
        ee()->load->helper('text');
      ee()->load->library('email');
         
        $tag_vars = array($order->toTagArray());
         
         ee()->email->to($this->parse($email->to, $tag_vars));
         ee()->email->wordwrap = $email->word_wrap;
         ee()->email->mailtype = $email->mail_format;

        if (ee()->config->item('store_from_email')) {
          
            ee()->email->from(ee()->config->item('store_from_email'), ee()->config->item('store_from_name'));
            
        } else {
            ee()->email->from(ee()->config->item('webmaster_email'), ee()->config->item('webmaster_name'));
        }

        if ($email->bcc) {
             ee()->email->bcc($email->bcc);
        }
         ee()->email->subject($this->parse($email->subject, $tag_vars));
         ee()->email->message($this->parse_html($email->contents, $tag_vars, true));
 
         ee()->email->send();
    }

    /**
     * Parse a template and return as plain text string (no html entities)
     */
    public function parse($template, $tag_vars, $parse_embeds = false)
    {
        return html_entity_decode($this->parse_html($template, $tag_vars, $parse_embeds));
    }

    /**
     * Seriously weak
     */
    public function parse_html($template, $tag_vars, $parse_embeds = false)
    {
       
        ee()->load->library('template', NULL, 'TMPL');
                // back up existing TMPL class
        $OLD_TMPL = isset(ee()->TMPL) ? ee()->TMPL : null;

        if ($parse_embeds) {

            // extra weak
            if (null === $this->snippets) {     

                $result = ee()->db->select('snippet_name, snippet_contents');
                ee()->db->where('(site_id = '.ee()->db->escape_str(ee()->config->item('site_id')).' OR site_id = 0)');
				$fresh = ee()->db->get('snippets');
                if ($fresh->num_rows() > 0){
             
                $this->snippets = array();

                foreach ($fresh->result() as $var){
               
					$snippets[$var->snippet_name] = $var->snippet_contents;
               }
			}
			if(is_array($this->snippets))
				ee()->config->_global_vars = array_merge(ee()->config->_global_vars, $this->snippets);
		}
            // parse simple variables
            $template = ee()->TMPL->parse_variables($template, $tag_vars);

            // parse as complete template (embeds, snippets, and globals)
            ee()->TMPL->parse($template);
            $template = ee()->TMPL->parse_globals(ee()->TMPL->final_template);
        }

       

        // parse simple variables
       return ee()->TMPL->parse_variables($template, $tag_vars);

        // restore old TMPL class
       // ee()->TMPL = $OLD_TMPL;

       // return $template;
    }
}
