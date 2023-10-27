<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author		Rein de Vries <support@reinos.nl>
 * @link		http://ee.reinos.nl
 * @copyright 	Copyright (c) 2017 Reinos.nl Internet Media
 * @license     http://ee.reinos.nl/commercial-license
 *
 * Copyright (c) 2017. Reinos.nl Internet Media
 * All rights reserved.
 *
 * This source is commercial software. Use of this software requires a
 * site license for each domain it is used on. Use of this software or any
 * of its source code without express written permission in the form of
 * a purchased commercial or other license is prohibited.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE.
 *
 * As part of the license agreement for this software, all modifications
 * to this source must be submitted to the original author for review and
 * possible inclusion in future releases. No compensation will be provided
 * for patches, although where possible we will attribute each contribution
 * in file revision notes. Submitting such modifications constitutes
 * assignment of copyright to the original author (Rein de Vries and
 * Reinos.nl Internet Media) for such modifications. If you do not wish to assign
 * copyright to the original author, your license to  use and modify this
 * source is null and void. Use of this software constitutes your agreement
 * to this clause.
 */

class Gmaps_helper
{
    /**
     * Logging levels
     */
    private static $_levels = array(
        1 => 'ERROR',
        2 => 'DEBUG',
        3 => 'INFO'
    );


    /**
     * History of logging for EE Debug Toolbar
     */
    private static $_log = array();


    /**
     * Flag for whether to 'flash' our toolbar tab
     */
    private static $_log_has_error = FALSE;


    /**
     * Remove the double slashes
     */
    public static function remove_double_slashes($str)
    {
        return preg_replace("#(^|[^:])//+#", "\\1/", $str);
    }

    // ----------------------------------------------------------------------

    /**
     * Check if Submitted String is a Yes value
     *
     * If the value is 'y', 'yes', 'true', or 'on', then returns TRUE, otherwise FALSE
     *
     */
    public static function check_yes($which, $string = false)
    {
        if (is_string($which))
        {
            $which = strtolower(trim($which));
        }

        $result = in_array($which, array('yes', 'y', 'true', 'on'), TRUE);

        if($string)
        {
            return $result ? 'true' : 'false' ;
        }

        return $result;
    }

    // ------------------------------------------------------------------------

    /**
     * Log an array to a file
     *
     */
    public static function log_array($array)
    {
        @file_put_contents(__DIR__.'/print.txt', print_r($array, true), FILE_APPEND);
    }

    // ----------------------------------------------------------------------------------

    /**
     * Log all messages
     *
     * @param array $logs The debug messages.
     * @return void
     */
    public static function log_to_ee( $logs = array(), $name = '')
    {
        if(!empty($logs))
        {
            foreach ($logs as $log)
            {
                ee()->TMPL->log_item('&nbsp;&nbsp;***&nbsp;&nbsp;'.$name.' debug: ' . $log);
            }
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Log method
     *
     * By default will pass message to log_message();
     * Also will log to template if rendering a PAGE.
     *
     *  1 = error
     *  2 = debug
     *  3 = info
     *
     * @access public
     * @param string $message The log entry message.
     * @param int $severity The log entry 'level'.
     * @param bool $log_to_ee
     */
    public static function log($message, $severity = 1, $log_to_ee = false )
    {
        // translate our severity number into text
        $severity = (array_key_exists($severity, self::$_levels)) ? self::$_levels[$severity] : self::$_levels[1];

        // save our log for EE Debug Toolbar
        self::$_log[] = array($severity, $message);
        if($severity == 'ERROR')
        {
            self::$_log_has_error = TRUE;
        }

        // basic EE logging
        log_message($severity, GMAPS_NAME . ": {$message}");

        //log to the cp
        if($log_to_ee)
        {
            $log = ee('Model')->make('gmaps:Log');
            $log->site_id = ee()->config->item('site_id');
            $log->message = $message;
            $log->time = ee()->localize->now;
            return $log->save();
        }

        // Can we also log our message to the template debugger?
        if (REQ == 'PAGE' && isset(ee()->TMPL))
        {
            ee()->TMPL->log_item(GMAPS_NAME . " [{$severity}]: {$message}");
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Fetch our static log
     *
     * @return  Array   Array of logs
     */
    public static function get_log()
    {
        return self::$_log;
    }

    // ------------------------------------------------------------------------

    /**
     * Fetch our static log
     *
     * @return  Array   Array of logs
     */
    public static function log_has_error()
    {
        return self::$_log_has_error;
    }

    // ------------------------------------------------------------------------

    /**
     * Is the string serialized
     *
     */
    public static function is_serialized($val)
    {
        /* if (!is_string($val)){ return false; }
        if (trim($val) == "") { return false; }
        if (preg_match("/^(i|s|a|o|d):(.*);/si",$val)) { return true; }*/

        $data = @unserialize($val);
        if ($data !== false) {
            return true;
        }
        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Is the string json
     *
     */
    public static function is_json($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    // ------------------------------------------------------------------------

    /**
     * Retrieve site path
     */
    public static function get_site_path()
    {
        // extract path info
        $site_url_path = parse_url(ee()->functions->fetch_site_index(), PHP_URL_PATH);

        $path_parts = pathinfo($site_url_path);
        $site_path = $path_parts['dirname'];

        $site_path = str_replace("\\", "/", $site_path);

        return $site_path;
    }

    // ------------------------------------------------------------------------

    /**
     * remove beginning and ending slashes in a url
     *
     * @param  $url
     * @return void
     */
    public static function remove_begin_end_slash($url, $slash = '/')
    {
        $url = explode($slash, $url);
        array_pop($url);
        array_shift($url);
        return implode($slash, $url);
    }

    // ----------------------------------------------------------------------

    /**
     * add slashes for an array
     *
     * @param  $arr_r
     * @return void
     */
    public static function add_slashes_extended(&$arr_r)
    {
        if(is_array($arr_r))
        {
            foreach ($arr_r as &$val)
                is_array($val) ? self::add_slashes_extended($val):$val=addslashes($val);
            unset($val);
        }
        else
            $arr_r = addslashes($arr_r);
    }

    // ----------------------------------------------------------------

    /**
     * add a element to a array
     *
     * @return  DB object
     */
    public static function array_unshift_assoc(&$arr, $key, $val)
    {
        $arr = array_reverse($arr, true);
        $arr[$key] = $val;
        $arr = array_reverse($arr, true);
        return $arr;
    }

    // ----------------------------------------------------------------------

    /**
     * get the memory usage
     *
     * @param
     * @return void
     */
    public static function memory_usage()
    {
        $mem_usage = memory_get_usage(true);

        if ($mem_usage < 1024)
            return $mem_usage." bytes";
        elseif ($mem_usage < 1048576)
            return round($mem_usage/1024,2)." KB";
        else
            return round($mem_usage/1048576,2)." MB";
    }

    // ----------------------------------------------------------------------

    /**
     * EDT benchmark
     * https://github.com/mithra62/ee_debug_toolbar/wiki/Benchmarks
     *
     * @param none
     * @return void
     */
    public static function benchmark($method = '', $start = true)
    {
        if($method != '')
        {
            $prefix = GMAPS_MAP.'_';
            $type = $start ? '_start' : '_end';
            ee()->benchmark->mark($prefix.$method.$type);
        }
    }

    // ----------------------------------------------------------------------

    /**
     * 	Fetch Action IDs
     *
     * 	@access public
     *	@param string
     * 	@param string
     *	@return mixed
     */
    public static function fetch_action_id($class = '', $method)
    {
        ee()->db->select('action_id');
        ee()->db->where('class', $class);
        ee()->db->where('method', $method);
        $query = ee()->db->get('actions');

        if ($query->num_rows() == 0)
        {
            return FALSE;
        }

        return $query->row('action_id');
    }

    // ----------------------------------------------------------------------

    /**
     * Parse only a string
     *
     * @param none
     * @return void
     */
    public static function parse_tags($tag = '', $parse = true)
    {
        //check the ee()->TMPL object
        if(isset(ee()->TMPL))
        {
            $OLD_TMPL = ee()->TMPL;
            ee()->remove('TMPL');
        }
        else
        {
            require_once APPPATH.'libraries/Template.php';
            $OLD_TMPL = null;
        }

        //set the new ee()->TMPL
        ee()->set('TMPL', new EE_Template());
        ee()->TMPL->parse($tag, true);
        $tag = ee()->TMPL->parse_globals($tag);
        $tag = ee()->TMPL->remove_ee_comments($tag);

        //remove and add the old TMPL object to the ee()->TMPL object if null
        if($OLD_TMPL !== NULL)
        {
            ee()->remove('TMPL');
            ee()->set('TMPL', $OLD_TMPL);
        }

        //return the data
        return trim($tag);
    }

    // ----------------------------------------------------------------------

    /**
     * Parse a template
     *
     * @param none
     * @return void
     */
    public static function parse_template($template_id = 0)
    {
        //load model
        ee()->load->model('template_model');

        //get the template
        $template = ee()->template_model->get_templates(NULL, array(), array('template_id' => $template_id) );

        //is there an template
        if($template->num_rows() > 0)
        {
            $template = $template->result();

            //go to the template parser
            require_once APPPATH.'libraries/Template.php';
            ee()->load->library('template', NULL, 'TMPL');
            ee()->TMPL->run_template_engine($template[0]->group_name, $template[0]->template_name);
            ee()->output->_display();
        }
        else
        {
            echo 'No template selected';
        }

        exit;
    }

    // ----------------------------------------------------------------------

    /**
     * Get the data from tagdat
     *
     * @param none
     * @return void
     */
    public static function get_from_tagdata($field = 'field', $default_value = '')
    {
        //get the tag pair data
        //can be for example {address}{/address}
        if (preg_match_all("/".LD.$field.RD."(.*?)".LD."\/".$field.RD."/s", ee()->TMPL->tagdata, $tmp)!=0)
        {
            if(isset($tmp[1][0]))
            {
                //trim to one line
                $tmp[1][0] = gmaps_helper::trim_to_one_line($tmp[1][0]);

                //convert double quotes to single quotes
                $tmp[1][0] = str_replace('"', "'", $tmp[1][0]);

                //check for stash
                if (preg_match_all("/".LD."exp:stash:(.*?)".RD."(.*?)".LD."\/exp:stash:(.*?)".RD."/s", $tmp[1][0], $stash_match))
                {
                    if ( ! class_exists('Stash'))
                    {
                        include_once PATH_THIRD . 'stash/mod.stash.php';
                    }

                    //parse the whole tag
                    $stash_result = Stash::parse(array(), $stash_match[0][0]);

                    //place the result in the template
                    $tmp[1][0] = str_replace($stash_match[0][0], $stash_result, $tmp[1][0]);
                }

                if (preg_match_all("/".LD."exp:stash:get(.*?)".RD."/s", $tmp[1][0], $stash_match))
                {
                    if ( ! class_exists('Stash'))
                    {
                        include_once PATH_THIRD . 'stash/mod.stash.php';
                    }

                    //parse the whole tag
                    $stash_result = Stash::parse(array(), $tmp[0][0]);

                    //fix?
                    $stash_result = str_replace(array(
                        LD.$field.RD,
                        LD.'/'.$field.RD,

                    ), '', $stash_result);

                    //place the result in the template
                    $tmp[1][0] = str_replace($stash_match[0][0], $stash_result, $tmp[1][0]);
                }

                //remove the tagdata
                ee()->TMPL->tagdata = str_replace($tmp[0][0], '', ee()->TMPL->tagdata);

                //go to the parser to parse any module tag data, if present
                $parsed_data = gmaps_helper::parse_tags($tmp[1][0]);

                //remove from tagdata
                ee()->TMPL->tagdata = str_replace($tmp[0][0], '', ee()->TMPL->tagdata);

                //return the data
                return $parsed_data;
            }
        }

        //get normal tagdata form params
        else
        {
            return ee()->TMPL->fetch_param($field, $default_value);
        }

        return '';
    }

    // ----------------------------------------------------------------------

    /**
     * set_cache
     *
     * @access private
     * @param string $name
     * @param string $value
     * @param bool $reset
     * @return
     */
    public static function set_ee_cache($name = '', $value = '', $reset = false)
    {
        if ( isset(ee()->session->cache[GMAPS_MAP][$name]) == FALSE || $reset == true)
        {
            ee()->session->cache[GMAPS_MAP][$name] = $value;
        }
        return ee()->session->cache[GMAPS_MAP][$name];

    }

    // ----------------------------------------------------------------------

    /**
     * get_cache
     *
     * @access private
     * @param string $name
     * @return bool
     */
    public static function get_ee_cache($name = '')
    {
        if ( isset(ee()->session->cache[GMAPS_MAP][$name]) != FALSE )
        {
            return ee()->session->cache[GMAPS_MAP][$name];
        }
        return false;
    }

    // ----------------------------------------------------------------------

    /**
     * set_cache
     *
     * @access private
     * @param string $name
     * @param string $value
     */
    public static function set_cache($name = '', $value = '')
    {
        if (session_id() == "")
        {
            session_start();
        }

        $_SESSION[$name] = $value;
    }

    // ----------------------------------------------------------------------

    /**
     * get_cache
     *
     * @access private
     * @param string $name
     * @return string
     */
    public static function get_cache($name = '')
    {
        // if no active session we start a new one
        if (session_id() == "")
        {
            session_start();
        }

        if (isset($_SESSION[$name]))
        {
            return $_SESSION[$name];
        }

        else
        {
            return '';
        }
    }

    // ----------------------------------------------------------------------

    /**
     * delete_cache
     *
     * @access private
     * @param string $name
     */
    public static function delete_cache($name = '')
    {
        // if no active session we start a new one
        if (session_id() == "")
        {
            session_start();
        }

        unset($_SESSION[$name]);
    }

    // ----------------------------------------------------------------------

    /**
     * mcp_meta_parser
     *
     * @access private
     * @param string $type
     * @param $file
     */
    public static function mcp_meta_parser($type='', $file)
    {
        // -----------------------------------------
        // CSS
        // -----------------------------------------
        if ($type == 'css')
        {
            if ( isset(ee()->session->cache[GMAPS_MAP]['CSS'][$file]) == FALSE )
            {
                ee()->cp->add_to_head('<link rel="stylesheet" href="' . ee()->gmaps_settings->get_setting('theme_url') . 'css/' . $file . '" type="text/css" media="print, projection, screen" />');
                ee()->session->cache[GMAPS_MAP]['CSS'][$file] = TRUE;
            }
        }

        // -----------------------------------------
        // CSS Inline
        // -----------------------------------------
        if ($type == 'css_inline')
        {
            ee()->cp->add_to_foot('<style type="text/css">'.$file.'</style>');

        }

        // -----------------------------------------
        // Javascript
        // -----------------------------------------
        if ($type == 'js')
        {
            if ( isset(ee()->session->cache[GMAPS_MAP]['JS'][$file]) == FALSE )
            {
                ee()->cp->add_to_foot('<script src="' . ee()->gmaps_settings->get_setting('theme_url') . 'javascript/' . $file . '" type="text/javascript"></script>');
                ee()->session->cache[GMAPS_MAP]['JS'][$file] = TRUE;
            }
        }

        // -----------------------------------------
        // Javascript Inline
        // -----------------------------------------
        if ($type == 'js_inline')
        {
            ee()->cp->add_to_foot('<script type="text/javascript">'.$file.'</script>');

        }
    }

    // ----------------------------------------------------------------------

    /**
     * Create url title
     * @param string $uri
     * @param string $replace_with
     * @return mixed
     */
    public static function create_uri($uri = '', $replace_with = '-')
    {
        return preg_replace("#[^a-zA-Z0-9_\-]+#i", $replace_with, strtolower($uri));
    }

    // ----------------------------------------------------------------------

    /**
     * Anonymously report EE & PHP versions used to improve the product.
     * @param array $overide
     */
    public static function stats($overide = array())
    {
        if (
            ee()->gmaps_settings->item('report_stats') != 0 &&
            function_exists('curl_init') &&
            ee()->gmaps_settings->item('report_date') <  ee()->localize->now)
        {
            $data = http_build_query(array(
                // anonymous reference generated using one-way hash
                'hash' => isset($overide['hash']) ? $overide['hash'] : md5(ee()->gmaps_settings->item('license_key').ee()->gmaps_settings->item('site_url')),
                'license' => isset($overide['license']) ? $overide['license'] : ee()->gmaps_settings->item('license_key'),
                'product' => isset($overide['product']) ? $overide['product'] : GMAPS_NAME,
                'version' => isset($overide['version']) ? $overide['version'] : GMAPS_VERSION,
                'ee' => APP_VER,
                'php' => PHP_VERSION,
                'time' => ee()->localize->now,
            ));
            ee()->load->library('curl');
            ee()->curl->simple_post(GMAPS_STATS_URL, $data);
            //ee()->curl->debug();

            // report again in 7 days
            ee()->gmaps_settings->save_setting('report_date', ee()->localize->now + 7*24*60*60);
        }
    }

    // ----------------------------------------------------------------------

    /**
     * Simple license check.
     *
     * @access     private
     * @return     bool
     */
    public static function license_check()
    {
        $is_valid = FALSE;

        $valid_patterns = array(
            '/^[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}$/' // devot-ee.com
        );

        foreach ($valid_patterns as $pattern)
        {
            if (preg_match($pattern, ee()->gmaps_settings->item('license_key')))
            {
                $is_valid = TRUE;
                break;
            }
        }

        return $is_valid;
    }

    // ----------------------------------------------------------------------

    /**
     * encode data
     */
    public static function encode_data($str = '')
    {
        if(is_array($str))
        {
            $str = serialize($str);
        }

        ee()->load->library('encrypt');
        $str = ee()->encrypt->encode($str);

        return $str;
    }

    // ----------------------------------------------------------------------

    /**
     * encode data
     */
    public static function decode_data($str = '')
    {
        ee()->load->library('encrypt');
        $str = ee()->encrypt->decode($str);

        if(self::is_serialized($str))
        {
            $str = unserialize($str);
        }

        return $str;
    }

    // ----------------------------------------------------------------

    /**
     * Send email template
     */
    public static function send_email($template_name_or_html = '', $data = array(), $type = 'text')
    {
        ee()->load->library(array('email', 'template'));
        ee()->load->helper('text');

        ee()->email->mailtype = $type;

        ee()->email->wordwrap = true;

        //get the template

        if($template_name_or_html != '' )
        {
            //get the template
            $template = ee()->functions->fetch_email_template($template_name_or_html);

            //no template
            if (empty($template['title']) OR empty($template['data'])) { return; }

            $template_data = $template['data'];
            $template_title = $template['title'];
        }
        else
        {
            $template_title = $data['template_title'];
            $template_data = $template_name_or_html;
        }

        //override email title?
        if(isset($data['subject']))
        {
            $template_title = $data['subject'];
        }

        //set default values
        $def_vars = array(
            'site_name'	=> stripslashes(ee()->config->item('site_name')),
            'site_url'	=> ee()->config->item('site_url'),
        );

        $vars = array_merge($def_vars, $data);

        $tmpl_vars = array($vars);
        $email_title = ee()->TMPL->parse_variables($template_title, $tmpl_vars);
        $template_data = ee()->TMPL->parse_simple_segment_conditionals($template_data);
        $template_data = ee()->TMPL->simple_conditionals($template_data, $tmpl_vars);
        $template_data = ee()->TMPL->parse_variables($template_data, $tmpl_vars);

        $template_data = ee()->TMPL->advanced_conditionals($template_data);
        $email_body = gmaps_helper::parse_tags($template_data);

        //break lines in html
        if($type == 'html')
        {
            $email_body = nl2br($email_body);
        }

        //files?
        if(isset($vars['files']))
        {
            foreach ($vars['files'] as $file)
            {
                if(is_file($file))
                {
                    ee()->email->attach($file);
                }
            }
        }

        // sender address defaults to site webmaster email
        if (!isset($vars['from']) || $vars['from'] == '' || !isset($vars['from_name']) || $vars['from_name'] == '')
        {
            ee()->email->from(ee()->config->item('webmaster_email'), ee()->config->item('webmaster_name'));
        }
        else
        {
            ee()->email->from($vars['from'], $vars['from_name']);
        }

        // do we have a BCC address?
        if (isset($vars['bcc']) && $vars['bcc'] && $vars['bcc'] != '')
        {
            ee()->email->bcc($vars['bcc']);
        }

        // send message
        ee()->email->to($vars['to']);
        ee()->email->subject(entities_to_ascii($email_title));
        ee()->email->message(entities_to_ascii($email_body));
        ee()->email->send();
        ee()->email->clear(TRUE);
    }

    // --------------------------------------------------------------------

    /**
     * add a hook
     */
    public static function add_hook($hook = '', $data = array())
    {
        if ($hook AND ee()->extensions->active_hook(GMAPS_MAP.$hook) === TRUE)
        {
            $data = ee()->extensions->call(GMAPS_MAP.$hook, $data);
            if (ee()->extensions->end_script === TRUE) return;
        }

        return $data;
    }

    // ----------------------------------------------------------------

    /**
     * Format a date
     */
    public static function format_date($format='', $date=null, $localize=true)
    {
        if (method_exists(ee()->localize, 'format_date') === true)
        {
            return ee()->localize->format_date($format, $date, $localize);
        }
        else
        {
            return ee()->localize->decode_date($format, $date, $localize);
        }
    }

    // ----------------------------------------------------------------

    /**
     * Custom No_Result conditional
     *
     * Same as {if no_result} but with your own conditional.
     *
     * @param string $cond_name
     * @param string $source
     * @param string $return_source
     * @return unknown
     */
    public static function custom_no_results_conditional($cond_name, $source, $return_source=FALSE)
    {
        if (strpos($source, LD."if {$cond_name}".RD) !== FALSE)
        {
            if (preg_match('/'.LD."if {$cond_name}".RD.'(.*?)'.LD.'\/'.'if'.RD.'/s', $source, $cond))
            {
                return $cond[1];
            }

        }

        if ($return_source !== FALSE)
        {
            return $source;
        }

        return;
    }

    // ----------------------------------------------------------------

    /**
     * Rewrite CP_URL with the 2.8.0 way
     *
     * @return unknown
     */
    public static function cp_url($url = '', $data = array(), $cp_data = false)
    {
        if (function_exists('cp_url') === true)
        {
            if($cp_data && REQ == 'CP' && isset(ee()->db))
            {
                $data = array_merge($data, array('module' => ENTRY_API_IMPORTER_MAP));
                return cp_url('cp/addons_modules/show_module_cp/', $data);
            }
            else
            {
                return cp_url($url, $data);
            }
        }
        else
        {
            //rewrite the data to an url
            $data = http_build_query($data, AMP);

            if($cp_data && REQ == 'CP' && isset(ee()->db))
            {
                return BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.ENTRY_API_IMPORTER_MAP.AMP.$data;
            }
            else
            {
                return $url.AMP.$data;
            }
        }

    }

    // ----------------------------------------------------------------------

    /**
     * Get the cache path
     *
     * @return unknown
     */
    public static function cache_path()
    {
        $cache_path = ee()->config->item('cache_path');

        if (empty($cache_path))
        {
            $cache_path = APPPATH.'cache/';
        }

        $cache_path .= GMAPS_MAP.'/';

        if ( ! is_dir($cache_path))
        {
            mkdir($cache_path, DIR_WRITE_MODE);
            @chmod($cache_path, DIR_WRITE_MODE);
        }

        return $cache_path;
    }

    // ----------------------------------------------------------------------

    /**
     * create an url title
     */
    public static function create_url_title($string = '', $delimiter = '-')
    {
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', $delimiter, html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), $delimiter));
    }

    // ----------------------------------------------------------------------

    /**
     * Create a redirect with messages for the CP
     * @param string $method
     * @param string $message_success
     * @param string $message_failure
     */
    public static function redirect_cp($method = 'settings', $message_success = '', $message_failure = '')
    {
        $notifications = array();

        //message success
        if(!empty($message_success))
        {
            $notifications['message_success'] = ee()->lang->line(GMAPS_MAP.'_'.$message_success);
        }

        //message failure
        if(!empty($message_failure))
        {
            $notifications['message_failure'] = ee()->lang->line(GMAPS_MAP.'_'.$message_failure);
        }

        //dSet the flash data
        ee()->session->set_flashdata($notifications);

        //redirect
        ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.GMAPS_MAP.AMP.'method='.$method);
    }


    // ------------------------------------------------------------------------

	/**
	 * Remove empty values (BETTER)
	 *
	 */
	public static function remove_empty_array_values($input)
    {
       return array_filter($input, create_function('$a','return trim($a)!="";'));
    }	

    // ------------------------------------------------------------------------

	/**
	 * Remove empty values
	 *
	 */
	public static function remove_empty_values($input)
    {
        // If it is an element, then just return it
        if (!is_array($input)) {
          return $input;
        }
        $non_empty_items = array();

        foreach ($input as $key => $value) {
          // Ignore empty cells
          if($value) {
            // Use recursion to evaluate cells 
            $non_empty_items[$key] = self::remove_empty_values($value);

            if($non_empty_items[$key] == '')
            {
                unset($non_empty_items[$key]);
            }
          }
        }

        if(empty($non_empty_items))
        {
            $non_empty_items = '';
        }

        // Finally return the array without empty items
        return $non_empty_items;
    }

    // ------------------------------------------------------------------------

    /**
     * avoid double latlngs
     */
    public static function create_links_from_string( $str, $target = '_blank')
    {
        return preg_replace("/(http:\/\/[^\s]+)/", "<a target='".$target."' href='$1'>$1</a>", $str);
    }

    // ----------------------------------------------------------------------

    /**
     * Trim multi line to one
     *
     * @param  $string
     * @return void
     */
    public static function trim_to_one_line($string)
    {
        $string = str_replace(array("\r\n", "\r"), "\n", $string);
        $lines = explode("\n", $string);
        $new_lines = array();
        foreach ($lines as $i => $line) {
            if(!empty($line))
                $new_lines[] = trim($line);
        }
        return implode($new_lines);
    }

    // ----------------------------------------------------------------------

    /**
     * 	Convert special to normal
     *
     * 	@access public
     *	@param string
     * 	@param string
     *	@return mixed
     */
    public static function transliterate_string($txt)
    {
        $transliterationTable = array('á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'å' => 'a', 'Å' => 'A', 'ã' => 'a', 'Ã' => 'A', 'ą' => 'a', 'Ą' => 'A', 'ā' => 'a', 'Ā' => 'A', 'ä' => 'ae', 'Ä' => 'AE', 'æ' => 'ae', 'Æ' => 'AE', 'ḃ' => 'b', 'Ḃ' => 'B', 'ć' => 'c', 'Ć' => 'C', 'ĉ' => 'c', 'Ĉ' => 'C', 'č' => 'c', 'Č' => 'C', 'ċ' => 'c', 'Ċ' => 'C', 'ç' => 'c', 'Ç' => 'C', 'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D', 'đ' => 'd', 'Đ' => 'D', 'ð' => 'dh', 'Ð' => 'Dh', 'é' => 'e', 'É' => 'E', 'è' => 'e', 'È' => 'E', 'ĕ' => 'e', 'Ĕ' => 'E', 'ê' => 'e', 'Ê' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ė' => 'e', 'Ė' => 'E', 'ę' => 'e', 'Ę' => 'E', 'ē' => 'e', 'Ē' => 'E', 'ḟ' => 'f', 'Ḟ' => 'F', 'ƒ' => 'f', 'Ƒ' => 'F', 'ğ' => 'g', 'Ğ' => 'G', 'ĝ' => 'g', 'Ĝ' => 'G', 'ġ' => 'g', 'Ġ' => 'G', 'ģ' => 'g', 'Ģ' => 'G', 'ĥ' => 'h', 'Ĥ' => 'H', 'ħ' => 'h', 'Ħ' => 'H', 'í' => 'i', 'Í' => 'I', 'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I', 'ï' => 'i', 'Ï' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I', 'į' => 'i', 'Į' => 'I', 'ī' => 'i', 'Ī' => 'I', 'ĵ' => 'j', 'Ĵ' => 'J', 'ķ' => 'k', 'Ķ' => 'K', 'ĺ' => 'l', 'Ĺ' => 'L', 'ľ' => 'l', 'Ľ' => 'L', 'ļ' => 'l', 'Ļ' => 'L', 'ł' => 'l', 'Ł' => 'L', 'ṁ' => 'm', 'Ṁ' => 'M', 'ń' => 'n', 'Ń' => 'N', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n', 'Ñ' => 'N', 'ņ' => 'n', 'Ņ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ò' => 'o', 'Ò' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O', 'õ' => 'o', 'Õ' => 'O', 'ø' => 'oe', 'Ø' => 'OE', 'ō' => 'o', 'Ō' => 'O', 'ơ' => 'o', 'Ơ' => 'O', 'ö' => 'oe', 'Ö' => 'OE', 'ṗ' => 'p', 'Ṗ' => 'P', 'ŕ' => 'r', 'Ŕ' => 'R', 'ř' => 'r', 'Ř' => 'R', 'ŗ' => 'r', 'Ŗ' => 'R', 'ś' => 's', 'Ś' => 'S', 'ŝ' => 's', 'Ŝ' => 'S', 'š' => 's', 'Š' => 'S', 'ṡ' => 's', 'Ṡ' => 'S', 'ş' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ß' => 'SS', 'ť' => 't', 'Ť' => 'T', 'ṫ' => 't', 'Ṫ' => 'T', 'ţ' => 't', 'Ţ' => 'T', 'ț' => 't', 'Ț' => 'T', 'ŧ' => 't', 'Ŧ' => 'T', 'ú' => 'u', 'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ŭ' => 'u', 'Ŭ' => 'U', 'û' => 'u', 'Û' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ű' => 'u', 'Ű' => 'U', 'ũ' => 'u', 'Ũ' => 'U', 'ų' => 'u', 'Ų' => 'U', 'ū' => 'u', 'Ū' => 'U', 'ư' => 'u', 'Ư' => 'U', 'ü' => 'ue', 'Ü' => 'UE', 'ẃ' => 'w', 'Ẃ' => 'W', 'ẁ' => 'w', 'Ẁ' => 'W', 'ŵ' => 'w', 'Ŵ' => 'W', 'ẅ' => 'w', 'Ẅ' => 'W', 'ý' => 'y', 'Ý' => 'Y', 'ỳ' => 'y', 'Ỳ' => 'Y', 'ŷ' => 'y', 'Ŷ' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y', 'ź' => 'z', 'Ź' => 'Z', 'ž' => 'z', 'Ž' => 'Z', 'ż' => 'z', 'Ż' => 'Z', 'þ' => 'th', 'Þ' => 'Th', 'µ' => 'u', 'а' => 'a', 'А' => 'a', 'б' => 'b', 'Б' => 'b', 'в' => 'v', 'В' => 'v', 'г' => 'g', 'Г' => 'g', 'д' => 'd', 'Д' => 'd', 'е' => 'e', 'Е' => 'e', 'ё' => 'e', 'Ё' => 'e', 'ж' => 'zh', 'Ж' => 'zh', 'з' => 'z', 'З' => 'z', 'и' => 'i', 'И' => 'i', 'й' => 'j', 'Й' => 'j', 'к' => 'k', 'К' => 'k', 'л' => 'l', 'Л' => 'l', 'м' => 'm', 'М' => 'm', 'н' => 'n', 'Н' => 'n', 'о' => 'o', 'О' => 'o', 'п' => 'p', 'П' => 'p', 'р' => 'r', 'Р' => 'r', 'с' => 's', 'С' => 's', 'т' => 't', 'Т' => 't', 'у' => 'u', 'У' => 'u', 'ф' => 'f', 'Ф' => 'f', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'c', 'ч' => 'ch', 'Ч' => 'ch', 'ш' => 'sh', 'Ш' => 'sh', 'щ' => 'sch', 'Щ' => 'sch', 'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'y', 'ь' => '', 'Ь' => '', 'э' => 'e', 'Э' => 'e', 'ю' => 'ju', 'Ю' => 'ju', 'я' => 'ja', 'Я' => 'ja');
        $txt = str_replace(array_keys($transliterationTable), array_values($transliterationTable), $txt);
        return $txt;
    }

    // ----------------------------------------------------------------------

    /**
     * @param string $name
     * @param string $html
     * @param bool|false $close
     * @return string
     */
    public static function createModal($name = '', $html = '', $close = false)
    {
        return '
            <div style="display:none" class="modal-wrap modal-'.$name.'">
                <div class="modal">
                    <div class="col-group">
                        <div class="col w-16">
                            '.($close ? '<a class="m-close" href="#"></a>' : '').'
                            <div class="box">
                                '.$html.'
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ';
    }

    // ----------------------------------------------------------------------
    // CUSTOM
    // ----------------------------------------------------------------------

	/**
	 * Build an js array
	 *
	 */
	public static function build_js_array($addresses, $strtolower = false, $evaluate_yes_no = false, $remove_empty_values = true) 
    {
       $addresses = trim($addresses);

        //lowercase
        if($strtolower) 
        {
            $addresses = strtolower($addresses);
        }

        //empty?
        if($addresses == '' || empty($addresses))
        {
            return '[]';
        }

        $addresses = explode('|',$addresses);

        //do we need to remove empty values
        if($remove_empty_values)
        {
            $addresses = self::remove_empty_values($addresses);
        }

        $_addresses = '[]';

        if(!empty($addresses))
        {
            $_addresses = '';
            
            foreach($addresses as $key => $address)
            {
                //evalutate yes or no
                if($evaluate_yes_no) {
                   $address = $address == 'yes' ? true : false ;
                }

                if($key == 0)
                {
                    $_addresses .= '[';
                }
                
                if(count($addresses) == ($key +1 ))
                {
                    $_addresses .= '"'.$address.'"';
                }
                else
                {
                    $_addresses .= '"'.$address.'",';
                }
                
                if(count($addresses) == ($key +1 ))
                {
                    $_addresses .= ']';
                }
            }
        }
        return $_addresses;
    }

    // ------------------------------------------------------------------------

	/**
	 * calculate the distance between 2 points
	 *
	 */
	public static function distance( $latlng1, $latlng2 )
    {
        $latlng1 = explode(',', $latlng1);
        $latitude1 = $latlng1[0];
        $longitude1 = $latlng1[1];
        $latlng2 = explode(',', $latlng2);
        $latitude2 = $latlng2[0];
        $longitude2 = $latlng2[1];

        $theta = $longitude1 - $longitude2;
        $miles = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        return compact('miles','feet','yards','kilometers','meters'); 
    }

    // ------------------------------------------------------------------------

	/**
	 * avoid double latlngs
	 */
	public static function avoid_double_latlng( $latlng = array(), $radius = 25)
    {
       $latlng_new = array();

       if(!empty($latlng))
       {
            foreach($latlng as $ll)
            {
                //is there already an ll?
                if(in_array($ll, $latlng_new))
                {
                    $ll_ = explode(',', $ll);

                    $random = self::random_latlng($ll_[0], $ll_[1], $radius);
                    $ll = $random['lat'].','.$random['lng'];
                    $latlng_new[] = $ll;
                }

                //new one
                else
                {
                    $latlng_new[] = $ll;
                }
            }
       }

       return $latlng_new;
    }



    // ------------------------------------------------------------------------

	/**
	 * random latlng in radius (miles)
	 */
	public static function random_latlng( $latitude, $longitude, $radius = 1 )
    {
        $lng_min = $longitude - $radius / abs(cos(deg2rad($latitude)) * 69);
        $lng_max = $longitude + $radius / abs(cos(deg2rad($latitude)) * 69);
        $lat_min = $latitude - ($radius / 69);
        $lat_max = $latitude + ($radius / 69);

        return array(
            'lat' => self::random_float($lat_min, $lat_max),
            'lng' => self::random_float($lng_min, $lng_max)
        );
    }

    // ------------------------------------------------------------------------

	/**
	 * Random float
	 */
	public static function random_float ($min,$max) 
    {
        return ($min+lcg_value()*(abs($max-$min)));
    }

    // ------------------------------------------------------------------------

	/**
	 * is curl loaded
	 */
	public static function is_curl_loaded() 
    {
        if (extension_loaded('curl')) {
            return true;
        }
        return false;
    }

    // ----------------------------------------------------------------------

	/**
	 * add slashes for an array
	 *
	 * @param  $arr_r
	 * @return void
	 */
	public static function addslashesextended(&$arr_r)
	{
		if(is_array($arr_r))
		{
			foreach ($arr_r as &$val)
				is_array($val) ? self::addslashesextended($val):$val=addslashes($val);
			unset($val);
		}
		else
			$arr_r = addslashes($arr_r);
	}

    // ----------------------------------------------------------------------

    /**
     * Trim multi line to one
     *
     * @param  $string
     * @return void
     */
    public static function count_multiple_values($string = '', $delimiter = '|')
    {
        $string = explode($delimiter, $string);
        return count($string);
    }

    // ----------------------------------------------------------------------

    /**
     * @return bool
     */
    public static function is_ssl()
    {
        $is_SSL = FALSE;

        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
            || $_SERVER['SERVER_PORT'] == 443) {

            $is_SSL = TRUE;
        }

        return $is_SSL;
    }

    // ----------------------------------------------------------------------

    /**
     * get the value from an array without errors
     *
     * @param array $array
     * @param string $key
     * @param string $default_value
     * @return string
     */
    public static function array_value($array = array(), $key = '', $default_value = '')
    {
        if(isset($array[$key]))
        {
            return $array[$key];
        }

        return $default_value;
    }

    // ----------------------------------------------------------------------
	
} // END CLASS
