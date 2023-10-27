<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Module file.
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2017 Causing Effect
 * @license		http://docs.causingeffect.com/expressionengine/ce-cache/license-agreement.html
 * @link		https://www.causingeffect.com
 */
class Ce_cache
{
	//a reference to the instantiated class factory
	private $drivers;

	//debug mode flag
	private $debug = false;

	//the relative directory path to be appended to the cache path
	private $id_prefix = '';

	//a flag to indicate whether or not the cache is setup
	public $is_cache_setup = false;

	//this will hold the actual URL, or the URL specified by the user
	private $cache_url = '';

	//default cache time to live (1 hour)
	private $default_seconds = 3600;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		//if the template debugger is enabled, and a super admin user is logged in, enable debug mode
		$this->debug = false;
		if ( ee()->session->userdata['group_id'] == 1 && ee()->config->item('template_debugging') == 'y' )
		{
			$this->debug = true;
		}

		//include CE Cache Utilities
		$this->include_library('Ce_cache_utils');
	}

	/**
	 * This method will check if the cache id exists, and return it if it does. If the cache id does not exists, it will cache the data and return it.
	 *
	 * @return string
	 */
	public function it()
	{
		//setup the cache drivers if needed
		$this->setup_cache();

		//get the tagdata
		$tagdata = $this->no_results_tagdata();

		//get the id
		if ( false === $id = $this->fetch_id( __METHOD__ ) )
		{
			return $tagdata;
		}

		//check the cache for the id
		$item = $this->get( true );

		if ( $item === false ) //the item could not be found for any of the drivers
		{
			//specify that we want the save method to return the content
			ee()->TMPL->tagparams['show'] = 'yes';

			//attempt to save the content
			return $this->save();
		}

		//the item was found, parse the item and return it
		return $this->process_return_data( $item );
	}

	/**
	 * Save an item to the cache.
	 *
	 * @return string
	 */
	public function save()
	{
		//setup the cache drivers if needed
		$this->setup_cache();

		//did the user elect to ignore this tag?
		if ( strtolower( ee()->TMPL->fetch_param( 'ignore_if_dummy' ) ) == 'yes' && $this->drivers[0]->name() == 'dummy' ) //ignore this entire tag if the dummy driver is being used
		{
			return ee()->TMPL->no_results();
		}

		//don't process googlebot save requests, as it can caused problems by hitting an insane number of non-existant URLs
		//note: we do this here, because we still want to return pages quickly to google bot if they are already cached, we just don't want to save pages it requests
		if ( ee()->config->item( 'ce_cache_block_bots' ) != 'no' && $this->is_bot() )
		{
			$this->include_library('Ce_cache_drivers');
			$this->drivers = Ce_cache_drivers::factory( array( 'dummy' ) );
			$this->is_cache_setup = false;
		}

		//get the tagdata
		$tagdata = $this->no_results_tagdata();

		//trim the tagdata?
		$should_trim = strtolower( Ce_cache_utils::determine_setting( 'trim', 'no' ) );
		$should_trim = Ce_cache_utils::ee_string_to_bool( $should_trim );

		//trim here in case the data needs to be returned early
		if ( $should_trim )
		{
			$tagdata = trim( $tagdata );
		}

		//get the id
		if ( false === $id = $this->fetch_id( __METHOD__ ) )
		{
			return $tagdata;
		}

		//determine the ttl (time to live)
		$ttl = $this->determine_time_parameter();

		//store the current ttl, this may be overridden by the Until tag
		ee()->session->cache['Ce_cache']['seconds'][$id] = $ttl;

		//save the previous tags
		$previous_tags = isset( ee()->session->cache['Ce_cache']['tags'] ) ? ee()->session->cache['Ce_cache']['tags'] : '';

		//clear the current tags
		ee()->session->cache['Ce_cache']['tags'] = '';

		//flag that caching is happening--important for escaped content
		ee()->session->cache['Ce_cache']['is_caching'] = true;

		//do we need to process the data?
		if ( ee()->TMPL->fetch_param( 'process' ) != 'no' ) //we need to process the data
		{
			//we're going to escape the logged_in and logged_out conditionals, since the Channel Entries loop adds them as variables.
			$tagdata = str_replace( array( 'logged_in', 'logged_out' ), array( 'ce_cache-in_logged', 'ce_cache-out_logged' ), $tagdata );

			//pre parse hook
			if (ee()->extensions->active_hook('ce_cache_pre_parse'))
			{
				$tagdata = ee()->extensions->call('ce_cache_pre_parse', $tagdata);
			}

			//parse the data
			$tagdata = $this->parse_as_template( $tagdata );

			//post parse hook
			if (ee()->extensions->active_hook('ce_cache_post_parse'))
			{
				$tagdata = ee()->extensions->call('ce_cache_post_parse', $tagdata);
			}
		}

		$tagdata = $this->unescape_tagdata( $tagdata );

		//make sure the template debugger is not getting cached, as that is bad
		$debugger_pos = strpos( $tagdata, '<div style="color: #333; background-color: #ededed; margin:10px; padding-bottom:10px;"><div style="text-align: left; font-family: Sans-serif; font-size: 11px; margin: 12px; padding: 6px"><hr size=\'1\'><b>TEMPLATE DEBUGGING</b><hr' );
		if ( $debugger_pos !== false )
		{
			$tagdata = substr_replace( $tagdata, '', $debugger_pos, -1 );
		}

		//pre save hook
		if (ee()->extensions->active_hook('ce_cache_pre_save'))
		{
			$tagdata = ee()->extensions->call('ce_cache_pre_save', $tagdata, 'fragment');
		}

		//trim again since the data may be much different now
		if ( $should_trim )
		{
			$tagdata = trim( $tagdata );
		}

		//get the time to live (may have been overridden by the Until tag)
		$ttl = ee()->session->cache[ 'Ce_cache' ]['seconds'][$id];

		//unset this item
		unset( ee()->session->cache[ 'Ce_cache' ]['seconds'][$id] );

		//loop through the drivers and try to save the data
		foreach ( $this->drivers as $driver )
		{
			if ( $driver->set( $id, $tagdata, $ttl ) === false ) //save unsuccessful
			{
				$this->log_debug_message( __METHOD__, "Something went wrong and the data for '{$id}' was not cached using the " . $driver->name() . " driver." );
			}
			else //save successful
			{
				$this->log_debug_message( __METHOD__, "The data for '{$id}' was successfully cached using the " . $driver->name() . " driver." );

				//if we are saving the item for the first time, we are going to keep track of the drivers and ids, so we can clear the cached items later if this ends up being a 404 page
				if ( $driver->name() != 'dummy' )
				{
					ee()->session->cache[ 'Ce_cache' ]['cached_items'][] = array( 'driver' => $driver->name(), 'id' => $id );

					$this->register_the_shutdown();
				}

				break;
			}
		}

		//flag that caching is finished--important for escaped content
		ee()->session->cache[ 'Ce_cache' ]['is_caching'] = false;

		//save the tags, if applicable
		if ( isset($this->drivers[0]) && $this->drivers[0]->name() != 'dummy' )
		{
			$this->save_tags( $id, ee()->TMPL->fetch_param( 'tags' ) . ee()->session->cache[ 'Ce_cache' ]['tags'] );
		}

		//add the new tags to the previous tags, so they can be used for the static driver
		ee()->session->cache[ 'Ce_cache' ]['tags'] = $previous_tags . ee()->session->cache[ 'Ce_cache' ]['tags'];

		if ( ee()->TMPL->fetch_param( 'show' ) == 'yes' )
		{
			//parse any segment variables
			return $this->parse_vars( $tagdata );
		}

		unset( $tagdata );

		return '';
	}

	/**
	 * Save the static page.
	 */
	public function static_cache()
	{
		//is the user logged in?
		$logged_in = (ee()->session->userdata['member_id'] != 0);

		//see if there is a reason to prevent caching the page
		if (
			( isset( ee()->session->cache['ep_better_workflow']['is_preview'] ) && ee()->session->cache['ep_better_workflow']['is_preview'] === true ) //better workflow draft
			|| ( isset( $_GET['bwf_dp'] ) && $_GET['bwf_dp'] == 't' ) //another bwf check (from Matt Green)
			|| ( isset( $_GET['publisher_status'] ) && $_GET['publisher_status'] == 'draft' ) // publisher check (from Fusionary)
			|| Ce_cache_utils::ee_string_to_bool( Ce_cache_utils::determine_setting( 'off', 'no' ) ) //ce cache is off
			|| ( ee()->config->item( 'ce_cache_block_bots' ) != 'no' && $this->is_bot() ) //bot page
			|| (Ce_cache_utils::ee_string_to_bool( Ce_cache_utils::determine_setting( 'logged_in_only', 'no', 'static' ) ) && ! $logged_in ) //logged in only, but not logged in
			|| (Ce_cache_utils::ee_string_to_bool( Ce_cache_utils::determine_setting( 'logged_out_only', 'no', 'static' ) ) && $logged_in ) //logged out only, but is logged in
			|| ( ! empty( $_POST ) && Ce_cache_utils::ee_string_to_bool( Ce_cache_utils::determine_setting( 'ignore_post_requests', 'yes' ) ) && $_POST != array( 'entry_id' => '' ) ) //a POST page and ignore_post_requests is set to "yes"
		) //no caching
		{
			return;
		}

		if ( ! isset( ee()->session->cache[ 'Ce_cache' ][ 'static' ] ) )
		{
			//make sure we set the cache_url for the path
			$this->determine_cache_url();

			$static_ttl = $this->determine_time_parameter();

			//get the time to live (defaults to 60 minutes)
			ee()->session->cache[ 'Ce_cache' ][ 'static' ] = array(
				'ttl' => $static_ttl,
				'tags' => ee()->TMPL->fetch_param( 'tags' )
			);
		}

		$this->register_the_shutdown();

		//return the tagdata, optionally trimmed
		$should_trim = strtolower( Ce_cache_utils::determine_setting( 'trim', 'no' ) );
		$should_trim = Ce_cache_utils::ee_string_to_bool( $should_trim );
		return ( $should_trim ) ? trim( $this->no_results_tagdata() ) : $this->no_results_tagdata();
	}

	/**
	 * @param int $seconds
	 * @return int
	 */
	private function validate_time_to_live( $seconds )
	{
		//infinite
		if ( $seconds === 0 || $seconds === "0" )
		{
			return (int) $seconds;
		}

		//empty
		if ( empty( $seconds ) || ! is_numeric( $seconds ) )
		{
			$seconds = $this->default_seconds;
		}

		$seconds = floor( $seconds );
		return (int) $seconds;
	}

	/**
	 * This method allows the cache time to be overridden with a timestamp. This can be useful if entries have an expiration date.
	 *
	 * @param string $time A timestamp, or time string (like '10 minutes').
	 * @return string
	 */
	public function until( $time = null )
	{
		if ( ! isset( $time ) )
		{
			//get the tagdata
			$tagdata = trim( ee()->TMPL->tagdata );
			if ( empty( $tagdata ))
			{
				return ee()->TMPL->no_results();
			}

			//the passed in timestamp or time string
			$time = $tagdata;

			$seconds = $this->parse_time( $time, true );
		}

		//exit( 'seconds: "' . $seconds . '"' );
		$seconds = $this->validate_time_to_live( $seconds );

		//determine the mode
		$mode = ee()->TMPL->fetch_param( 'mode', 'earliest' );
		$mode = strtolower( $mode );
		if ( $mode == 'f' || $mode == 'force' )
		{
			$mode = 'force';
		}
		else if ( $mode == 'l' || $mode == 'keep_latest' || $mode == 'latest'  )
		{
			$mode = 'latest';
		}
		else // if ( $mode == 'e' || $mode == 'keep_earliest' )
		{
			$mode = 'earliest';
		}

		ee()->session->cache[ 'Ce_cache' ]['seconds_static'][] = array( 'mode' => $mode, 'seconds' => $seconds );

		//loop through each currently caching item, and determine which time setting to use
		foreach ( ee()->session->cache['Ce_cache']['seconds'] as $id => $current )
		{
			ee()->session->cache['Ce_cache']['seconds'][ $id ] = $this->compare_times( $mode, $current, $seconds );
		}

		return '';
	}

	/**
	 * Compares two times and determines which one to keep.
	 *
	 * @param string $mode Can be 'force', 'latest', or 'earliest'
	 * @param int $time1 The current time string.
	 * @param int $time2 The new time string.
	 * @return int The keeper time.
	 */
	private function compare_times( $mode, $time1, $time2 )
	{
		if ( $mode == 'force' )
		{
			//use this value no matter what the current value is
			return $this->validate_time_to_live( $time2 );
		}
		else if ( $mode == 'latest' )
		{
			//takes the latest of the two values
			//if there is no expiraction (seconds="0"), this expiration time will take precedence
			return $this->validate_time_to_live( max( $time2, $time1 ) );
		}
		else //$mode == 'earliest'
		{
			//if there is no limit (seconds="0"), use this value, otherwise, use the earliest of the two values
			return empty( $time1 ) ? $time2 : $this->validate_time_to_live( min( $time2, $time1 ) );
		}
	}

	/**
	 * Time to live (from now) in seconds.
	 *
	 * @param string $string The time string.
	 * @param bool $is_timestamp Is this a timestamp, or simply seconds?
	 * @return int
	 */
	private function parse_time( $string, $is_timestamp = false )
	{
		$seconds = (string) $this->default_seconds;

		$string = trim( $string );

		if ( $string === "0" ) //infinite
		{
			return 0;
		}

		if ( is_numeric( $string ) ) //seconds
		{
			$seconds = $string;

			if ( $is_timestamp )
			{
				$seconds = $seconds - time();
			}
		}
		else if ( is_string( $string ) && ! empty( $string ) ) //a possible time string
		{
			$timestamp = strtotime( $string );

			if ( $timestamp === false )
			{
				$this->log_debug_message( __METHOD__, 'Unable to parse the time string: "' . $string . '".' );
			}
			else
			{
				$seconds = $timestamp - time();
			}
		}

		if ( $seconds === 0 ) //since the passed in time was not 0, this wasn't intended to be infinite, so let's make sure there is an expiration
		{
			$seconds = -1;
		}

		return (int) $seconds;
	}

	/**
	 * Determine the ttl from the 'for' and 'seconds' parameters.
	 *
	 * @return int
	 */
	private function determine_time_parameter()
	{
		//setup the times array as needed
		if ( ! isset( ee()->session->cache['Ce_cache']['seconds'] ) )
		{
			ee()->session->cache['Ce_cache']['seconds'] = array();
			ee()->session->cache['Ce_cache']['seconds_static'] = array();
		}

		//first see if there is a time specified
		$ttl = Ce_cache_utils::determine_setting( 'for', 'none' );
		if ( $ttl == 'none' ) //no time was specified
		{
			//now we'll try the old seconds= parameters
			$ttl = Ce_cache_utils::determine_setting( 'seconds', 'none' );
			if ( $ttl == 'none' ) //no seconds param either, use the default
			{
				$ttl = $this->default_seconds;
			}

			//parse the time setting as seconds
			$ttl = $this->parse_time( $ttl );
		}
		else //we received a setting, parse as a time string or timestamp
		{
			$ttl = $this->parse_time( $ttl, true );
		}

		//validate the time to live
		return $this->validate_time_to_live( $ttl );
	}

	/**
	 * Registers the shutdown function.
	 */
	private function register_the_shutdown()
	{
		//register the shutdown function if needed
		if ( empty( ee()->session->cache[ 'Ce_cache' ][ 'shutdown_is_registered' ] ) )
		{
			ee()->session->cache[ 'Ce_cache' ][ 'shutdown_is_registered' ] = true;

			//register the shutdown function
			register_shutdown_function( array( $this, 'shut_it_down' ) );
		}
	}

	/**
	 * Escapes the passed-in content so that it will not be parsed before being cached.
	 *
	 * @return string
	 */
	public function escape()
	{
		$tagdata = false;

		//if there is pre_escaped tagdata, use it
		$tag_parts = ee()->TMPL->tagparts;
		if ( is_array( $tag_parts ) && isset( $tag_parts[2] ) )
		{
			if ( isset( ee()->session->cache[ 'Ce_cache' ]['pre_escape'][ 'id_' . $tag_parts[2] ] ) )
			{
				$tagdata = ee()->session->cache[ 'Ce_cache' ]['pre_escape'][ 'id_' . $tag_parts[2] ];
			}
		}

		if ( $tagdata === false ) //there was no pre-escaped tagdata, get the no_results tagdata
		{
			$tagdata = $this->no_results_tagdata();
		}

		if ( trim( $tagdata ) == '' ) //there is no tagdata
		{
			return $tagdata;
		}
		else if ( empty( ee()->session->cache[ 'Ce_cache' ]['is_caching'] ) ) //we're not inside of a tagdata loop
		{
			return $this->parse_vars( $tagdata );
		}

		//create a 16 character placeholder
		$placeholder = '-ce_cache_placeholder:' . hash( 'md5', $tagdata ); // . '_' . mt_rand( 0, 1000000 );

		//add to the cache
		ee()->session->cache[ 'Ce_cache' ]['placeholder-keys'][] = $placeholder;
		ee()->session->cache[ 'Ce_cache' ]['placeholder-values'][] = $tagdata;

		//return the placeholder
		return $placeholder;
	}

	/**
	 * Add one or more tags.
	 *
	 * @return string
	 */
	public function add_tags()
	{
		//get the tagdata
		$tagdata = trim( ee()->TMPL->tagdata );
		if ( empty( $tagdata ) )
		{
			return ee()->TMPL->no_results();
		}

		//make sure the tags session cache exists
		if ( empty( ee()->session->cache[ 'Ce_cache' ]['tags'] ) )
		{
			ee()->session->cache[ 'Ce_cache' ]['tags'] = '';
		}

		//turn the tagdata into cleaned tags
		$tagdata = mb_strtolower( trim( $tagdata ) );
		$tags = explode( '|', $tagdata );
		foreach ( $tags as $index => $tag )
		{
			$tag = trim( $tag );

			if ( empty( $tag ) ) //remove empty tag
			{
				unset( $tags[ $index ] );
			}
			else //add the cleaned up tag
			{
				$tags[ $index ] = $tag;
			}
		}
		$tags = implode( '|', $tags );

		//add the tags
		$this->log_debug_message( __METHOD__, 'The following tags were added: ' . $tags );
		ee()->session->cache[ 'Ce_cache' ]['tags'] .= '|' . $tags;

		//return an empty string
		return '';
	}

	/**
	 * Alias for the add_tags method.
	 *
	 * @return string
	 */
	public function add_tag()
	{
		return $this->add_tags();
	}

	/**
	 * Returns whether or not a driver is supported.
	 *
	 * @return int
	 */
	public function is_supported()
	{
		//get the driver
		$driver = ee()->TMPL->fetch_param( 'driver' );

		//load the factory class if needed
		$this->include_library('Ce_cache_drivers');

		//see if the driver is supported
		return ( Ce_cache_drivers::is_supported( $driver ) ) ? 1 : 0;
	}

	/**
	 * Get an item from the cache.
	 *
	 * @param bool $internal_request Was this method requested from this class (true) or from the template (false).
	 * @return bool|int
	 */
	public function get( $internal_request = false )
	{
		//setup the cache drivers if needed
		$this->setup_cache();

		//get the id
		if ( false === $id = $this->fetch_id( __METHOD__ ) )
		{
			return ee()->TMPL->no_results();
		}

		//loop through the drivers and attempt to find the cache item
		foreach ( $this->drivers as $driver )
		{
			$item = $driver->get( $id );

			if ( $item !== false ) //we found the item
			{
				$this->log_debug_message( __METHOD__, "The '{$id}' item was found for the " . $driver->name() . " driver." );

				//process the data and return it
				return $this->process_return_data( $item );
			}
		}

		//the item was not found in the cache of any of the drivers
		return ( $internal_request ) ? false : ee()->TMPL->no_results();
	}

	/**
	 * Delete something from the cache.
	 *
	 * @return string|void
	 */
	public function delete()
	{
		//setup the cache drivers if needed
		$this->setup_cache();

		//get the id
		if ( false === $id = $this->fetch_id( __METHOD__ ) )
		{
			return ee()->TMPL->no_results();
		}

		//loop through the drivers and attempt to delete the cache item for each one
		foreach ( $this->drivers as $driver )
		{
			if ( $driver->delete( $id ) !== false )
			{
				$this->log_debug_message( __METHOD__, "The '{$id}' item was deleted for the " . $driver->name() . " driver." );
			}
		}

		//delete all of the current tags for this item
		ee()->db->query( 'DELETE FROM exp_ce_cache_tagged_items WHERE item_id = ?', array( $id ) );
	}

	/**
	 * Manually clears items and/or tags, and optionally refreshes the cleared items.
	 *
	 * @return void
	 */
	public function clear()
	{
		//get the items
		$items = ee()->TMPL->fetch_param( 'items' );
		$items = empty( $items ) ? array() : explode( '|', $this->reduce_pipes( $items, false ) );

		//get the tags
		$tags = ee()->TMPL->fetch_param( 'tags' );
		$tags = empty( $tags ) ? array() : explode( '|', $this->reduce_pipes( $tags ) );

		//do we need to continue?
		if ( empty( $items ) && empty( $tags ) ) //we don't have any items or tags
		{
			return;
		}

		//refresh?
		$refresh = ee()->TMPL->fetch_param( 'refresh' );
		$refresh_time = 1;
		if ( is_numeric( $refresh ) )
		{
			$refresh_time = round( $refresh );
			$refresh = true;
		}
		else
		{
			$refresh = false;
		}

		//load the cache break class, if needed
		$this->include_library('Ce_cache_break');

		//instantiate the class break and call the break cache method
		$cache_break = new Ce_cache_break();
		$cache_break->break_cache( $items, $tags, $refresh, $refresh_time );
	}

	/**
	 * Get information about a cached item.
	 *
	 * @return string
	 */
	public function get_metadata()
	{
		//setup the cache drivers if needed
		$this->setup_cache();

		//get the id
		if ( false === $id = $this->fetch_id( __METHOD__ ) )
		{
			return ee()->TMPL->no_results();
		}

		//the array of meta data items
		$item = array();

		//loop through the drivers and attempt to find the cache item
		foreach ( $this->drivers as $driver )
		{
			//get the info
			if( !! $info = $driver->meta( $id, false  ) )
			{
				//info contains the keys: 'expiry', 'made', 'ttl', 'ttl_remaining', and 'content'
				//add in legacy keys
				$info['expire'] = $info['expiry'];
				$info['mtime'] = $info['made'];
				//add in driver key
				$info['driver'] = $driver->name();
				$item = $info;
				break;
			}
		}

		//make sure we have at least one result
		if ( count( $item ) == 0 )
		{
			return ee()->TMPL->no_results();
		}

		//get the tagdata
		$tagdata = $this->no_results_tagdata();

		//parse the conditionals
		$tagdata = ee()->functions->prep_conditionals( $tagdata, $item );

		//return the parsed tagdata
		return ee()->TMPL->parse_variables_row( $tagdata, $item );
	}

	/**
	 * Purges the cache.
	 *
	 * @return void
	 */
	public function clean()
	{
		$site_only = trim( ee()->TMPL->fetch_param( 'site_only', 'yes' ) );
		$force = Ce_cache_utils::ee_string_to_bool( trim( ee()->TMPL->fetch_param( 'force', 'yes' ) ) );

		//get the driver classes
		$this->include_library('Ce_cache_drivers');
		$this->drivers = Ce_cache_drivers::get_active_driver_classes(true, ! $force );

		//loop through the drivers and purge their respective caches
		foreach ( $this->drivers as $driver )
		{
			if ( Ce_cache_utils::ee_string_to_bool( $site_only ) )
			{
				//get the site name
				$site_prefix = Ce_cache_utils::get_site_prefix();

				//attempt to get the items for the path
				if ( false === $items = $driver->get_all( $site_prefix ) )
				{
					$this->log_debug_message( __METHOD__, "No items were found for the current site cache for the " . $driver->name() . " driver." );
					return;
				}

				//we've got items
				foreach ( $items as $item )
				{
					if ( $driver->delete( $site_prefix . ( $driver->get_all_is_array() ? $item['id'] : $item ) ) === false )
					{
						$this->log_debug_message( __METHOD__, "Something went wrong, and the current site cache for the " . $driver->name() . " driver was not cleaned successfully." );
					}
				}
				unset( $items );

				return;
			}
			else
			{
				if ( $driver->clear() === false )
				{
					$this->log_debug_message( __METHOD__, "Something went wrong, and the cache for the " . $driver->name() . " driver was not cleaned successfully." );
				}
				else
				{
					$this->log_debug_message( __METHOD__, "The cache for the " . $driver->name() . " driver was cleaned successfully." );
				}
			}
		}
	}

	/**
	 * Deprecated. Does not return anything.
	 *
	 * @return mixed
	 */
	public function cache_info()
	{
		return ee()->TMPL->no_results();
	}

	/**
	 * Breaks the cache. This method is an EE action (called from the CE Cache extension).
	 *
	 * @return void
	 */
	public function break_cache()
	{
		//debug mode
		if ( ee()->input->get_post( 'act_test', true ) === 'y' )
		{
			ee()->lang->loadfile( 'ce_cache' );
			echo lang('ce_cache_debug_working');
			exit();
		}

		//this method is not intended to be called as an EE template tag
		if ( isset( ee()->TMPL ) )
		{
			return;
		}

		//make sure the secret is valid
		if ( ! $this->is_secret_valid() )
		{
			return;
		}

		//load the cache break class, if needed
		$this->include_library('Ce_cache_break');

		//instantiate the class and call the break cache method
		$cache_break = new Ce_cache_break();
		$cache_break->break_cache_hook( null, null );
	}

	/**
	 * Determines whether or not a GET/POST secret matches the config secret setting.
	 *
	 * @return bool
	 */
	private function is_secret_valid()
	{
		//grab the secret from the get/post data
		$secret = ee()->input->get_post( 'secret', true );
		if ( $secret === false ) //no secret, no reason to stick around
		{
			return false;
		}

		$real_secret = ee()->config->item( 'ce_cache_secret' );
		if ( ! $real_secret )
		{
			$real_secret = '';
		}

		$real_secret = substr( md5( $real_secret ), 0, 10 );
		//check the passed in secret against the real secret
		if ( $secret != $real_secret )
		{
			return false;
		}

		return true;
	}

	/**
	 * Simple method to log a debug message to the EE Debug console.
	 *
	 * @param string $method
	 * @param string $message
	 * @return void
	 */
	private function log_debug_message( $method = '', $message = '' )
	{
		if ( $this->debug )
		{
			ee()->TMPL->log_item( "&nbsp;&nbsp;***&nbsp;&nbsp;CE Cache $method debug: " . $message );
		}
	}

	/**
	 * Sets up the cache if needed. This is its own method, as opposed to being in the constructor, because some methods will not need it.
	 */
	private function setup_cache()
	{
		if ( ! $this->is_cache_setup ) //only run if the flag indicated it has not already been setup
		{
			//set the set up flag
			$this->is_cache_setup = true;

			//get the driver classes
			$this->include_library('Ce_cache_drivers');
			$this->drivers = Ce_cache_drivers::get_active_driver_classes(false, true );

			//get the prefix
			$this->id_prefix = Ce_cache_utils::get_site_prefix();

			if ( ee()->TMPL->fetch_param( 'global' ) == 'yes' ) //global cache
			{
				$this->id_prefix .= 'global/';
			}
			else //page specific cache
			{
				$this->determine_cache_url();

				//set the id prefix
				$this->id_prefix .= 'local/' . Ce_cache_utils::sanitize_filename( $this->cache_url, true );
			}

			$this->id_prefix = trim( $this->id_prefix, '/' ) . '/';
		}
	}

	/**
	 * Determines cache URLs.
	 */
	private function determine_cache_url()
	{
		$override = ee()->TMPL->fetch_param( 'url_override' );
		if ( $override != false )
		{
			$this->cache_url = $override;
		}
		else
		{
			//triggers:original_uri
			if ( isset( ee()->config->_global_vars[ 'triggers:original_paginated_uri' ] ) ) //Zoo Triggers hijacked the URL
			{
				$this->cache_url = ee()->config->_global_vars[ 'triggers:original_paginated_uri' ];
			}
			else if ( isset( ee()->config->_global_vars[ 'freebie_original_uri' ] ) ) //Freebie hijacked the URL
			{
				$this->cache_url = ee()->config->_global_vars[ 'freebie_original_uri' ];
			}
			else //the URL was not hijacked
			{
				$this->cache_url = ee()->uri->uri_string();
			}
		}

		$prefix = Ce_cache_utils::determine_setting('url_prefix', '');
		if ( ! empty( $prefix ) )
		{
			$this->cache_url = rtrim($prefix, '/') . '/' . $this->cache_url;
		}

		//UTF-8 decode any special characters
		$this->cache_url = utf8_decode( $this->cache_url );
	}

	/**
	 * Parses the tagdata as if it were a template.
	 *
	 * @param string $tagdata The unprocessed template string.
	 * @return string The processed template string.
	 */
	public function parse_as_template( $tagdata = '' )
	{
		//store the current template object
		$TMPL2 = ee()->TMPL;

		//remove the current template object and create a new one
		ee()->remove('TMPL');
		ee()->set('TMPL', new EE_Template());

		//copy properties
		ee()->TMPL->start_microtime = $TMPL2->start_microtime;
		ee()->TMPL->tag_data   = array();
		ee()->TMPL->var_single = array();
		ee()->TMPL->var_cond   = array();
		ee()->TMPL->var_pair   = array();
		ee()->TMPL->plugins = $TMPL2->plugins;
		ee()->TMPL->modules = $TMPL2->modules;
		ee()->TMPL->module_data = $TMPL2->module_data;
		ee()->TMPL->segment_vars = $TMPL2->segment_vars;
		ee()->TMPL->global_vars = $TMPL2->global_vars;
		ee()->TMPL->embed_vars = $TMPL2->embed_vars;
		ee()->TMPL->template_route_vars = isset( $TMPL2->template_route_vars ) ? $TMPL2->template_route_vars : array();
		ee()->TMPL->layout_vars = isset( $TMPL2->layout_vars ) ? $TMPL2->layout_vars : array();
		ee()->TMPL->layout_conditionals = isset( $TMPL2->layout_conditionals ) ? $TMPL2->layout_conditionals : array();

		//parse tags
		ee()->TMPL->parse_tags();
		ee()->TMPL->process_tags();

		//parse the current tagdata
		ee()->TMPL->parse( $tagdata, false, ee()->config->item('site_id'), false );

		//get the parsed tagdata back
		$tagdata = ee()->TMPL->final_template;

		if ( $this->debug )
		{
			//these first items are boilerplate, and were already included in the first log - like "Parsing Site Variables", Snippet keys and values, etc
			unset( ee()->TMPL->log[0], ee()->TMPL->log[1], ee()->TMPL->log[2], ee()->TMPL->log[3], ee()->TMPL->log[4], ee()->TMPL->log[5], ee()->TMPL->log[6] );

			$TMPL2->log = array_merge( $TMPL2->log, ee()->TMPL->log );
		}

		//now let's check to see if this page is a 404 page
		if ( $this->is_404() )
		{
			ee()->output->out_type = '404';
			ee()->TMPL->template_type = '404';
			ee()->TMPL->final_template = $this->unescape_tagdata( $tagdata );
			ee()->TMPL->cease_processing = true;
			ee()->TMPL->no_results();
			ee()->session->cache[ 'Ce_cache' ]['is_404'] = true;
		}

		//restore the original template object
		ee()->remove('TMPL');
		ee()->set('TMPL', $TMPL2);

		unset($TMPL2, $temp);

		if ( ! ee()->session->cache('Ce_cache', 'post_parsing') )
		{
			//call the post parse hook for this data
			if ( ee()->extensions->active_hook( 'template_post_parse' ) )
			{
				ee()->session->set_cache('Ce_cache', 'mod_parsing', true);
				$tagdata = ee()->extensions->call( 'template_post_parse', $tagdata, false,  ee()->config->item('site_id') );
				ee()->session->set_cache('Ce_cache', 'mod_parsing', false);
			}
		}

		//return the tagdata
		return $tagdata;
	}

	/**
	 * Determines whether or not EE has 404 headers set
	 */
	private function is_404()
	{
		if ( isset( ee()->output->headers[0] ) )
		{
			foreach ( ee()->output->headers as $value )
			{
				foreach ( $value as $v )
				{
					if ( strpos( $v, '404' ) !== false ) // a 404 header was found
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Parses segment and global variables. Used to parse data in escape tags.
	 *
	 * @param string $str
	 * @return mixed
	 */
	public function parse_vars( $str )
	{
		//remove the comments
		$str = ee()->TMPL->remove_ee_comments( $str );

		//parse segment variables
		if ( strpos( $str, '{segment_' ) !== false )
		{
			for ( $i = 1; $i < 10; $i++ )
			{
				$str = str_replace( '{segment_' . $i . '}', ee()->uri->segment( $i ), $str );
			}
		}

		//parse global variables
		$str = ee()->TMPL->parse_variables_row( $str, ee()->config->_global_vars );

		//parse current_time
		$str = $this->current_time( $str );

		return $str;
	}

	/**
	 * Helper method that simplifies the data parsing and returning process.
	 *
	 * @param string $str
	 * @return string
	 */
	public function process_return_data( $str )
	{
		//parse globals and segment variables in case there were escaped during parsing
		$str = $this->parse_vars( $str );

		//parse current_time
		$str = $this->current_time( $str );

		//insert the action ids
		$str = $this->insert_action_ids( $str );
		return $str;
	}

	/**
	 * Replaces the {current_time} variable with the actual current time. Useful if the variable was escaped. This method mimics the functionality from the Template class.
	 *
	 * @param string $str
	 * @return string
	 */
	public function current_time( $str )
	{
		if ( strpos( $str, '{current_time' ) === false )
		{
			return $str;
		}

		if ( preg_match_all( '/{current_time\s+format=([\"\'])([^\\1]*?)\\1}/', $str, $matches ) )
		{
			for ($j = 0; $j < count($matches[0]); $j++)
			{
				if ( version_compare( APP_VER, '2.6.0', '<' ) )
				{
					$str = str_replace($matches[0][$j], ee()->localize->decode_date($matches[2][$j], ee()->localize->now), $str);
				}
				else
				{
					$str = str_replace($matches[0][$j], ee()->localize->format_date($matches[2][$j]), $str);
				}
			}
		}

		return str_replace( '{current_time}', ee()->localize->now, $str);
	}

	/**
	 * The following is a lot like the Functions method of inserting the action ids, except that this will first find the actions. The original method does not look to find the ids on cached data (it just stores them in an array as they are called).
	 *
	 * @param string $str
	 * @return string
	 */
	public function insert_action_ids( $str )
	{
		//will hold the actions
		$actions = array();

		//do we need to check for actions?
		if ( strpos( $str, LD . 'AID:' ) !== false && preg_match_all( '@' . LD . 'AID:([^:}]*):([^:}]*)' . RD . '@Us', $str, $matches, PREG_SET_ORDER ) ) //actions found
		{
			foreach ( $matches as $match )
			{
				$actions[ $match[ 1 ] ] = $match[ 2 ];
			}
		}
		else //no actions to parse
		{
			return $str;
		}

		//create the sql
		$sql = "SELECT action_id, class, method FROM exp_actions WHERE";
		foreach ( $actions as $key => $value )
		{
			$sql .= " (class= '" . ee()->db->escape_str( $key ) . "' AND method = '" . ee()->db->escape_str( $value ) . "') OR";
		}

		//run the query
		$query = ee()->db->query( substr( $sql, 0, -3 ) );

		if ( $query->num_rows() > 0 )
		{
			foreach ( $query->result_array() as $row )
			{
				$str = str_replace( LD . 'AID:' . $row[ 'class' ] . ':' . $row[ 'method' ] . RD, $row[ 'action_id' ], $str );
			}
		}

		return $str;
	}

	/**
	 * Determines the id to use.
	 *
	 * @param string $method The calling method.
	 * @return string|bool The id on success, or false on failure.
	 */
	public function fetch_id( $method )
	{
		if ( ee()->TMPL->fetch_param( 'global' ) == 'yes' ) //global cache
		{
			$id = ee()->TMPL->fetch_param( 'id', '' );
		}
		else //page specific cache
		{
			$id = Ce_cache_utils::determine_setting( 'id', 'item' );
		}

		$id = trim( $id );

		//get the id
		if ( empty( $id ) )
		{
			$this->log_debug_message( $method, "An id was not specified." );

			return false;
		}
		if ( ! $this->id_is_valid( $id ) )
		{
			$this->log_debug_message( $method, "The specified id '{$id}' is invalid. An id may only contain alpha-numeric characters, dashes, and underscores." );
			return false;
		}

		//add the id prefix
		return trim( Ce_cache_utils::remove_duplicate_slashes( $this->id_prefix . $id ), '/' );
	}

	/**
	 * Validates an id.
	 *
	 * @param string $id
	 * @return int 1 for valid, 0 for invalid
	 */
	public function id_is_valid( $id )
	{
		return preg_match( '@[^\s]+@i', $id );
	}



	/**
	 * Cleverly gets the tagdata with the original no_results tagdata, so it will still work as expected when parsed.
	 *
	 * @return string
	 */
	public function no_results_tagdata()
	{
		//$tagdata = ee()->TMPL->tagdata;
		$index = 0;
		foreach ( ee()->TMPL->tag_data as $i => $tag_dat )
		{
			if ( ee()->TMPL->tagchunk == $tag_dat['chunk'] )
			{
				$index = $i;
			}
		}

		return ee()->TMPL->tag_data[$index]['block'];
	}

	/**
	 * This is a shutdown function registered when an item is saved.
	 */
	public function shut_it_down()
	{
		//determine if this is a 404 page
		$is_404 = (
			ee()->config->item( 'ce_cache_exclude_404s' ) != 'no' //we are not excluding 404 pages (in other words, we are caching 404 pages)
			&& (
				isset( ee()->session->cache[ 'Ce_cache' ]['is_404'] ) //if previously evaluated to be a 404 page by fragment caching
				|| ee()->output->out_type == '404' //or if the output type is set to a 404 page
				|| $this->is_404() //or if there is a 404 header
			)
		);

		if ( $is_404 ) //this is a 404 page
		{
			//if there are cached items, and there are drivers, let's delete the cached items
			if ( isset ( ee()->session->cache[ 'Ce_cache' ]['cached_items'] ) && ! empty( $this->drivers ) )
			{
				//loop through each driver
				foreach ( $this->drivers as $driver )
				{
					foreach ( ee()->session->cache[ 'Ce_cache' ]['cached_items'] as $index => $item )
					{
						if ( $item['driver'] == $driver->name() )
						{
							$driver->delete( $item['id'] );
							unset( ee()->session->cache[ 'Ce_cache' ]['cached_items'][$index] );
						}
					}
				}
			}

			//remove the cached items from memory (although this will probably happen soon anyway)
			unset( ee()->session->cache[ 'Ce_cache' ]['cached_items'] );
		}
		else //this is not a 404 page (or it is a 404 page, but the config setting says not to exclude them)
		{
			if ( isset( ee()->session->cache[ 'Ce_cache' ][ 'static' ] ) ) //we have a static page, let's cache it
			{
				//load the factory class if needed
				$this->include_library('Ce_cache_drivers');

				//setup the driver
				$drivers = Ce_cache_drivers::factory( 'static' );
				foreach ( $drivers as $driver )
				{
					$id = Ce_cache_utils::get_site_prefix() . 'static/' .Ce_cache_utils::sanitize_filename( $this->cache_url, true );

					//get the final template
					$final = ee()->TMPL->final_template;

					//trim the tagdata?
					$should_trim = strtolower( Ce_cache_utils::determine_setting( 'trim', 'no' ) );
					$should_trim = Ce_cache_utils::ee_string_to_bool( $should_trim );
					if ( $should_trim )
					{
						$final = trim( ee()->TMPL->final_template );
					}

					//handle feed issues
					if ( ee()->output->out_type == 'feed' )
					{
						//this normally happens in the output class, so we need to take care of it here
						$final = preg_replace( '@<ee\:last_update>(.*?)<\/ee\:last_update>@', '', $final );
						$final = preg_replace( '@{\?xml(.+?)\?}@', '<?xml\\1?'.'>', $final);
					}

					//pre save hook
					if ( ee()->extensions->active_hook( 'ce_cache_pre_save' ) )
					{
						$final = ee()->extensions->call('ce_cache_pre_save', $final, 'static');
					}

					//get the headers
					$headers = array();

					//get the headers
					if ( isset( ee()->output->headers ) && is_array( ee()->output->headers ) )
					{
						foreach( ee()->output->headers as $header )
						{
							if ( isset( $header[0] ) )
							{
								$headers[] = $header[0];
							}
						}
					}

					//the stored ttl (time to live) for the static cache
					$ttl = ee()->session->cache[ 'Ce_cache' ][ 'static' ][ 'ttl' ];

					//loop through the various Until tags to see if the ttl needs to be updated
					foreach ( ee()->session->cache[ 'Ce_cache' ]['seconds_static'] as $times )
					{
						$ttl = $this->compare_times( $times['mode'], $ttl, $times['second'] );
					}

					//attempt to save the cache
					if ( $driver->set( $id, $final, $ttl, $headers ) === false ) //save unsuccessful
					{
						//probably too late to log debug messages - oh well
						$this->log_debug_message( __METHOD__, "Something went wrong and the data for '{$this->cache_url}' was not cached using the " . $driver->name() . " driver." );
					}
					else //save successful
					{
						//probably too late to log debug messages - oh well
						$this->log_debug_message( __METHOD__, "The data for '{$this->cache_url}' was successfully cached using the " . $driver->name() . " driver." );

						//get the tags
						$tags = ee()->session->cache[ 'Ce_cache' ][ 'static' ][ 'tags' ];

						//add in the tags
						if ( isset( ee()->session->cache[ 'Ce_cache' ]['tags'] ) )
						{
							$tags .=  ee()->session->cache[ 'Ce_cache' ]['tags'];
						}

						$this->save_tags( $driver->clean_id( $id ), $tags );
					}
				}
			}
		}
	}

	/**
	 * A very simple method to attempt to determine if the current user agent is a bot
	 *
	 * @return bool
	 */
	public function is_bot()
	{
		if ( ! isset( ee()->session->cache[ 'Ce_cache' ][ 'is_bot' ] ) )
		{
			$user_agent = ee()->input->user_agent();

			ee()->session->cache[ 'Ce_cache' ][ 'is_bot' ] = (bool)( ! empty( $user_agent ) && preg_match( '@bot|spider|crawl|curl@i', $user_agent ) );
		}

		return ee()->session->cache[ 'Ce_cache' ][ 'is_bot' ];
	}

	/**
	 * Swaps out placeholders with their escaped values.
	 *
	 * @param null $tagdata
	 * @return mixed|null
	 */
	public function unescape_tagdata( $tagdata = null )
	{
		if ( ! isset( $tagdata ) )
		{
			$tagdata = $this->no_results_tagdata();
		}

		//unescape any content escaped by the escape() method
		if ( isset( ee()->session->cache[ 'Ce_cache' ]['placeholder-keys'] ) )
		{
			$tagdata = str_replace( ee()->session->cache[ 'Ce_cache' ]['placeholder-keys'], ee()->session->cache[ 'Ce_cache' ]['placeholder-values'], $tagdata);

			$tagdata = str_replace( '{::segment_', '{segment_', $tagdata );
		}

		//unescape any escaped logged_in and logged_out conditionals if they were escaped above
		if ( ee()->TMPL->fetch_param( 'process' ) != 'no' )
		{
			//now we'll swap the logged_in and logged_out variables back to their old selves
			$tagdata = str_replace( array( 'ce_cache-in_logged', 'ce_cache-out_logged' ), array( 'logged_in', 'logged_out' ), $tagdata );
		}

		//remove ee comments
		$tagdata = ee()->TMPL->remove_ee_comments( $tagdata );

		return $tagdata;
	}

	/**
	 * Alias for the unescape_tagdata method.
	 *
	 * @param null $tagdata
	 * @return string
	 */
	public function unescape( $tagdata = null )
	{
		return $this->unescape_tagdata( $tagdata );
	}

	/**
	 * Saves any tags that are specified in the tag parameter.
	 *
	 * @param string $id
	 * @param string/bool $tag_string The string from ee()->TMPL->fetch_param( 'tags' )
	 */
	public function save_tags( $id, $tag_string = '' )
	{
		//tag the content if applicable
		if ( $tag_string !== false )
		{
			//cleanup the tag string
			$tag_string = $this->reduce_pipes( $tag_string );

			//explode into tags
			$temps = explode( '|', $tag_string );

			$data = array();

			//loop through the items
			foreach ( $temps as $temp )
			{
				$temp = trim( $temp );
				if ( empty( $temp ) )
				{
					$this->log_debug_message( __METHOD__, 'An empty tag was found and will not be applied to the saved item "' . $id . '".' );
					continue;
				}

				if ( strlen( $temp ) > 100 )
				{
					$this->log_debug_message( __METHOD__, 'The tag "' . $temp . '" could not be saved for the "' . $id . '" item, because it is over 100 characters long.' );
					continue;
				}

				$data[] = array( 'item_id' => $id, 'tag' => $temp );
			}
			unset( $temps );

			//delete all of the current tags for this item
			ee()->db->query( 'DELETE FROM exp_ce_cache_tagged_items WHERE item_id = ?', array( $id ) );

			//add in the new tags
			if ( count( $data ) > 1 )
			{
				ee()->db->insert_batch( 'ce_cache_tagged_items', $data );
			}
			else if ( count( $data ) > 0 )
			{
				ee()->db->insert( 'ce_cache_tagged_items', $data[0] );
			}

			unset( $data );
		}
	}

	/**
	 * Include the library by name. Done this way instead of
	 * ee()->load->library('example') so that the class is not
	 * instantiated, but rather used to call static methods.
	 *
	 * @param $name
	 */
	private function include_library($name)
	{
		//load the class if needed
		if (!class_exists($name))
		{
			include PATH_THIRD . 'ce_cache/libraries/'.$name.'.php';
		}
	}

	/**
	 * Removes duplicate pipes.
	 *
	 * @param string $string
	 * @param bool $make_lowercase
	 * @return string
	 */
	private function reduce_pipes( $string, $make_lowercase = true )
	{
		$string = trim( $string, '|' ); //trim pipes
		$string = str_replace( '||', '', $string ); //remove double pipes (empty tags)
		if ( $make_lowercase )
		{
			$string = mb_strtolower( $string ); //convert to lowercase
		}

		return $string;
	}

	/**
	 * Static tag aliases.
	 * See http://www.php.net/manual/en/language.oop5.overloading.php#object.call
	 *
	 * @param string $name The name of the method being called.
	 * @param array $arguments An enumerated array containing the parameters passed to the $name'ed method.
	 */
	public function __call( $name, $arguments )
	{
		if ( $name == 'static' || $name == 'stat' )
		{
			return $this->static_cache();
		}
	}
}
/* End of file mod.ce_cache.php */
/* Location: /system/expressionengine/third_party/ce_cache/mod.ce_cache.php */