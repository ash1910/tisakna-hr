<?php

/*
=====================================================
 This ExpressionEngine plugin was created by Laisvunas
  - http://devot-ee.com/developers/ee/laisvunas/
=====================================================
 Copyright (c) Laisvunas
=====================================================
 You may use this code on any website you are developing.
 Unless you have been granted prior, written consent from Laisvunas, you may not:
 * Reproduce, distribute, or transfer the Software, or portions thereof, to any third party
 * Sell, rent, lease, assign, or sublet the Software or portions thereof
 * Grant rights to any other person
=====================================================
 File: pi.cookie_plus.php
-----------------------------------------------------
 Purpose: Stores and retrieves custom cookies.
=====================================================
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cookie_plus {

  function Cookie_plus() {

    $this->EE =& get_instance();
  }

  function set() {
  
    // Fetch params
    $name    = $this->EE->TMPL->fetch_param('name');
    $value   = $this->EE->TMPL->fetch_param('value');
    $seconds = $this->EE->TMPL->fetch_param('seconds');
    $method  = $this->EE->TMPL->fetch_param('method');
    $event   = $this->EE->TMPL->fetch_param('event');
    
    // If "method" param is undefined
    if ($method === FALSE) {

     // Bake cookie
     $this->EE->functions->set_cookie($name, $value, $seconds);
    }
    // If "method" param is defined
    else {
    
     // Create client side javascript
     $name = 'exp__'.$name;
     $expires = $seconds * 1000;
     $todaystring = 'var exp__today = new Date();';
     $expiredatestring = 'var exp__expires_date = new Date( exp__today.getTime() + '.$expires.');';
     $namevaluestring = 'document.cookie ="'.$name.'" + "=" + escape("'.$value.'") + "';
     if ($seconds === '0') {

       $expirestring = '';
     }
     else {
     
       $expirestring = ';expires=" + exp__expires_date.toGMTString() + "';
     }

     $pathstring = ';path=/"';
     if ($event === FALSE OR $event === 'load') {
     
       $scriptstring = '<script type="text/javascript">'.$todaystring.$expiredatestring.$namevaluestring.$expirestring.$pathstring.'</script>';
     }
     elseif ($event === 'unload') {
     
       $scriptstring = '<script type="text/javascript">'.$todaystring.$expiredatestring.'window.onunload=function(){'.$namevaluestring.$expirestring.$pathstring.'}</script>';  
     }
     
     // Output client side javascript
     return $scriptstring;
   }
  }
  // End of store function

  
  function get() {
    
     // Fetch the tagdata
     $tagdata = $this->EE->TMPL->tagdata;

     // Fetch param
     $name = $this->EE->TMPL->fetch_param('name');
     //echo 'name: ['.$name.']<br>';

     // Retrieve cookie value
     //$cookievalue = $this->EE->input->cookie($name);
     $cookievalue = isset($_COOKIE[$name]) ? $_COOKIE[$name] : '';
     //var_dump($this->EE->input);
     //var_dump( $_COOKIE);
     //echo 'cookievalue: ['.$cookievalue.']<br>';
    
     // Put cookie value into conditionals array
     $conds['cookie'] = $cookievalue;     
    
     // Check if there is {cookie} variable or a conditional placed between {exp:cookie_plus} and {/exp:cookie_plus} tag pair 
     if (strpos($tagdata, 'cookie') > 0 OR strpos($tagdata, 'cookie') === 0) {
     
       // Evaluate if-conditional
       $tagdata = $this->EE->functions->prep_conditionals($tagdata, $conds);
       // Return cookie value as {cookie} variable's output
       $tagdata = str_replace('{cookie}', $cookievalue, $tagdata);
       return $tagdata;
     }
     else {
     
     // If there is no {cookie} variable, then return cookie value as output of single {exp:cookie_plus} tag
     return $cookievalue; 
     }

  }
  // End of retrieve function

  
  
function usage()
  {
  ob_start(); 
  ?>

Usage:

{exp:cookie_plus:set name="mycookie" value="hellow!" seconds="3600"}

Sets a cookie named mycookie with the value "hellow!". The cookie
will last for 1 hour (3600 seconds).
Set the seconds to zero to set a session cookie.

By default cookie is set using PHP function. This means that {exp:cookie_plus:set}
tag must be on top of the page and do its job before any other output. If you need 
to set a cookie after output started, you may use parameter "method". 
In case parameter "method" has a value "script" as here

{exp:cookie_plus:set name="mycookie" value="hellow!" seconds="3600" method="script"}

the cookie will be set using client-side javascript.

Using client-side javascript cookie by default will be set during page load event.
Also cookie will be set during page load event if "event" parameter is set to "load".
To set cookie during page unload event, parameter "event"  should have the value "unload":

{exp:cookie_plus:set name="mycookie" value="hellow!" seconds="3600" method="script" event="unload"}

To retrieve a cookie:

{exp:cookie_plus:get name="mycookie"}

If the cookie "mycookie" exists then it will return the value.
If the cookie does not exist then it will return nothing.

Alternatively for retrieving a cookie you may use {cookie} variable
placed between {exp:cookie_plus:get} and {/exp:cookie_plus:get}
tag pair.

Variable {cookie} within {exp:cookie_plus:get} and {/exp:cookie_plus:get} tag pair is
more powerful because you can use its output as parameter of other tag. For such use 
you must add parse="inward" parameter to {exp:cookie_plus:get} tag.

For example, code as this will work

{exp:cookie_plus:get name="mycookie" parse="inward"}
{exp:weblog:entries weblog="{cookie}" show="1|3|5"}
Some code
{/exp:weblog:entries}
{/exp:cookie_plus:get}

Also you can use retrieved cookie value in conditionals:

{exp:cookie_plus:get name="mycookie"}
{if  cookie=="news"}
Some code
{/if}
{/exp:cookie_plus:get}

  <?php
  $buffer = ob_get_contents();
  
  ob_end_clean(); 

  return $buffer;
  }
  // END USAGE
  
  
}//End

?>