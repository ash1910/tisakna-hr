<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Cache Break Class
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2017 Causing Effect
 * @license		http://docs.causingeffect.com/expressionengine/ce-cache/license-agreement.html
 * @link		https://www.causingeffect.com
 */

class Ce_cache_break
{
	private $deletes = array();
	private $refreshers = array();
	private $tags = array();
	private $classes = array();
	private $prefix = '';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		//include CE Cache Utilities
		if ( ! class_exists( 'Ce_cache_utils' ) )
		{
			include PATH_THIRD . 'ce_cache/libraries/Ce_cache_utils.php';
		}

		$this->async = ( ee()->config->item( 'ce_cache_async' ) != 'no' );
		$this->curl_enabled = ( ee()->config->item( 'ce_cache_curl' ) != 'no' );
	}

	/**
	 * Allows the cache to manually be broken.
	 *
	 * @param array $paths
	 * @param array $tags
	 * @param bool $refresh
	 * @param int $refresh_time
	 */
	public function break_cache( $paths = array(), $tags = array(), $refresh = true, $refresh_time = 1 )
	{
		//set up
		$this->setup_breaking();

		//handle the tags
		$this->add_tags( $tags, $refresh, $refresh_time );

		//handle the paths
		$paths = $this->parse_convenience_path_starters($paths);
		$this->add_paths( $paths, $refresh, $refresh_time );

		//break the tag and path URLs, deleting or refreshing as needed
		$this->run_breaking();
	}

	/**
	 * This method breaks the cache. The secret can be set in the config.
	 *
	 * @param array|null  $ids The entry ids
	 * @param string|null $secret
	 * @return void
	 */
	public function break_cache_hook( $ids = null, $secret = null )
	{
		//debug mode
		if (ee()->input->get_post( 'break_test', true ) === 'y')
		{
			echo 'working';
			exit();
		}

		//include CE Cache Utilities
		$this->include_library('Ce_cache_utils');

		//load the break and break settings models
		ee()->load->model('ce_cache_break_model');
		ee()->load->model('ce_cache_break_settings_model');

		//make sure the secret matches
		if (! ee()->ce_cache_break_model->does_secret_match($secret))
		{
			return;
		}

		//entries
		$entries = ee()->ce_cache_break_model->get_entries_from_ids($ids);
		if (empty($entries))
		{
			return;
		}

		//settings
		$break_settings = ee()->ce_cache_break_settings_model->get_all_break_settings();
		if (empty($break_settings))
		{
			return;
		}

		//set up
		$this->setup_breaking();

		//loop through all the setting rows
		foreach ( $break_settings as $setting )
		{
			//the break setting's channel id
			$channel_id = $setting['channel_id'];

			//determine the entries that are affected by this break setting
			$channel_entries = array();
			if ( $channel_id == 0 )
			{
				$channel_entries = $entries;
			}
			else
			{
				foreach ( $entries as $entry )
				{
					if ( $channel_id == $entry['channel_id'] )
					{
						$channel_entries[] = $entry;
					}
				}
			}

			//the break setting's refresh and refresh time
			$refresh = ( $setting['refresh'] == 'y' );
			$refresh_time = $setting['refresh_time'];

			//get the tags
			$tags = explode( '|', $setting['tags'] );
			if ( ! empty($tags) ) //we have one or more tags
			{
				//parse the tag entry variables
				$tags = $this->parse_setting_variables( $tags, $channel_entries );
				$this->add_tags( $tags, $refresh, $refresh_time );
			}

			//get the paths
			$paths = explode( '|', $setting['items'] );
			if (! empty($paths))
			{
				//parse the paths for each entry to turn any variables into actual item paths
				$paths = $this->parse_setting_variables( $paths, $channel_entries );
				$paths = $this->parse_convenience_path_starters($paths);
				$this->add_paths( $paths, $refresh, $refresh_time );
			}
		}

		//break the tag and path URLs, deleting or refreshing as needed
		$this->run_breaking(true);
	}

	/**
	 * Refresh the caches for the specified URLs.
	 *
	 * @param $urls array in the format URL => time
	 */
	public function refresh_urls( $urls )
	{
		if ( ! is_array( $urls ) )
		{
			return;
		}

		//now let's loop through our refresh items and refresh them
		foreach ( $urls as $url => $time )
		{
			@sleep( $time );

			//try to request the page, first using cURL, then falling back to fsocketopen
			if ( ! $this->curl_it($url) )//send a cURL request
			{
				//attempt a fsocketopen request
				$this->fsockopen_it( $url );
			}
		}
	}

	/**
	 * Parses each tag/path variable with the entry data
	 *
	 * @param $tags_or_paths
	 * @param $entries
	 * @return array
	 */
	private function parse_setting_variables( $tags_or_paths, $entries )
	{
		//the parsed return tags or paths
		$finals = array();

		//swap out 'any/' and 'non-global/' paths starters
		foreach ( $tags_or_paths as $source )
		{
			foreach( $entries as $entry )
			{
				$temp = $source;

				//entry_date format
				if ( preg_match_all( '#\{entry_date\s+format=([\"\'])([^\\1]*?)\\1\}#', $temp, $matches, PREG_SET_ORDER )  )
				{
					foreach ( $matches as $match )
					{
						if ( isset( $match[2] ) )
						{
							if ( version_compare( APP_VER, '2.6.0', '<' ) )
							{
								$temp = str_replace( $match[0], ee()->localize->decode_date( $match[2], $entry['entry_date'] ) , $temp );
							}
							else
							{
								$temp = str_replace( $match[0], ee()->localize->format_date( $match[2], $entry['entry_date'] ) , $temp );
							}

						}
					}
				}
				//edit_date format
				if ( preg_match_all( '#\{edit_date\s+format=([\"\'])([^\\1]*?)\\1\}#', $temp, $matches, PREG_SET_ORDER )  )
				{
					if ( version_compare( APP_VER, '2.6.0', '<' ) )
					{
						foreach ( $matches as $match )
						{
							if ( isset( $match[2] ) )
							{
								$temp = str_replace( $match[0], ee()->localize->decode_date( $match[2], ee()->localize->timestamp_to_gmt( $entry['edit_date'] ) ) , $temp );
							}
						}
					}
					else
					{
						ee()->load->helper('date');
						foreach ( $matches as $match )
						{
							if ( isset( $match[2] ) )
							{
								$temp = str_replace( $match[0], ee()->localize->format_date( $match[2], mysql_to_unix( $entry['edit_date'] ) ) , $temp );
							}
						}
					}
				}

				//setup find and replace arrays
				$find = array();
				$replace = array_values( $entry );
				foreach ( $entry as $key => $value )
				{
					$find[] = '{' . $key . '}';
				}

				//replace the values
				$finals[] = str_replace( $find, $replace, $temp );
			}
		}

		return array_unique( $finals );
	}

	/**
	 * Swaps out the 'any/' and 'non-global/' path starters.
	 *
	 * @param $paths
	 * @return array
	 */
	public function parse_convenience_path_starters($paths)
	{
		//a temporary array
		$sources = array();

		//swap out 'any/' and 'non-global/' paths starters
		foreach ( $paths as $source )
		{
			if ( strpos( $source, 'any/' ) === 0 )
			{
				$end = substr( $source, 4 );
				$sources[] = 'local/' . $end;
				$sources[] = 'global/' . $end;
				$sources[] = 'static/' . $end;
			}
			else if ( strpos( $source, 'non-global/' ) === 0 )
			{
				$end = substr( $source, 11 );
				$sources[] = 'local/' . $end;
				$sources[] = 'static/' . $end;
			}
			else
			{
				$sources[] = $source;
			}
		}

		return array_unique( $sources );
	}

	/**
	 * A simple method that leverages cURL to make a quick GET request. If the request is asynchronous, it has a quick timeout (500ms or 1 second, depending on the PHP version) that essentially calls the URL without waiting around for a response.
	 *
	 * @param string $url
	 * @return bool
	 */
	public function curl_it( $url )
	{
		if ( ! $this->curl_enabled )
		{
			return false;
		}

		if ( function_exists( 'curl_init' ) ) //cURL should work
		{
			$curl = curl_init( $url );
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true ); //no output
			curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, false ) ; //no timeout
			curl_setopt( $curl, CURLOPT_NOSIGNAL, true );
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false ); //no ssl verification
			curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, false ); //no ssl host verification
			curl_setopt( $curl, CURLOPT_HTTPHEADER, array('Cache-Control: no-cache') ); //no caching
			curl_setopt( $curl, CURLOPT_HTTPGET, true ); //make sure it is a GET request, as some servers cannot handle HEAD requests for some reason
			curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; pl; rv:1.9) Gecko/2008052906 Firefox/3.0' ); //user agent
			curl_setopt( $curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); //some servers default to ipv6, and throw 404 errors for every request...

			$maxredirects = 5;

			//follow location only works if there are no open_basedir or safe_mode restrictions
			if ( ! ini_get('open_basedir') && ! ini_get('safe_mode') ) //follow location should work
			{
				curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
				curl_setopt( $curl, CURLOPT_MAXREDIRS, $maxredirects );
			}
			else //manually follow location
			{
				//fix from http://slopjong.de/2012/03/31/curl-follow-locations-with-safe_mode-enabled-or-open_basedir-set/
				curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, false );

				$original_url = curl_getinfo( $curl, CURLINFO_EFFECTIVE_URL );
				$newurl = $original_url;
				$rch = curl_copy_handle( $curl );

				curl_setopt( $rch, CURLOPT_HEADER, true );
				curl_setopt( $rch, CURLOPT_NOBODY, true );
				curl_setopt( $rch, CURLOPT_FORBID_REUSE, false );
				do
				{
					curl_setopt( $rch, CURLOPT_URL, $newurl );
					$header = curl_exec( $rch );
					if ( curl_errno( $rch ) )
					{
						$code = 0;
					}
					else
					{
						$code = curl_getinfo( $rch, CURLINFO_HTTP_CODE );
						if ( $code == 301 || $code == 302 )
						{
							preg_match('/Location:(.*?)\n/', $header, $matches);
							$newurl = trim( array_pop($matches) );

							// if no scheme is present then the new url is a
							// relative path and thus needs some extra care
							if ( ! preg_match( "/^https?:/i", $newurl ) )
							{
								$newurl = $original_url . $newurl;
							}
						}
						else
						{
							$code = 0;
						}
					}
				} while ( $code && --$maxredirects );

				curl_close($rch);

				if ( ! $maxredirects )
				{
					if ( $maxredirects === null )
					{
						trigger_error( 'Too many redirects.', E_USER_WARNING );
					}
					$maxredirects = 0;

					return false;
				}
				curl_setopt( $curl, CURLOPT_URL, $newurl );
			}

			if ( $this->async ) //fire off the request and timeout as soon as possible
			{
				if ( defined( 'CURLOPT_TIMEOUT_MS' ) )
				{
					curl_setopt( $curl, CURLOPT_TIMEOUT_MS, 500 );
				}
				else
				{
					curl_setopt( $curl, CURLOPT_TIMEOUT, 1 );
				}

				curl_exec( $curl );
			}
			else if (curl_exec($curl) === false) //the synchronous request failed
			{
				//echo curl_error( $curl );
				//exit();
				curl_close( $curl );
				return false;
			}

			/*if ( ! curl_errno( $curl ) )
			{
				$info = curl_getinfo( $curl );
				print_r( $info );
			}*/

			curl_close( $curl );

			return true;
		}

		return false;
	}

	/**
	 * A simple method that leverages native PHP sockets to make a GET request. If the request is asynchronous, it has a quick timeout (1s) that essentially calls the URL without waiting around for a response.
	 *
	 * @param string $url
	 * @return bool
	 */
	public function fsockopen_it( $url )
	{
		if ( function_exists( 'fsockopen' ) ) //no cURL, try fsocketopen
		{
			//make sure that allow_url_fopen is set to true if permissible
			@ini_set('allow_url_fopen', true);
			//some servers will not accept the asynchronous requests if there is no user_agent
			@ini_set('user_agent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:5.0) Gecko/20100101');

			//parse the URL
			$parts = @parse_url( $url );

			//check to make sure there wasn't an error parsing the URL
			if ( $parts === false ) //there was a problem, so we'll stop here
			{
				return false;
			}

			//open the socket
			$fp = @fsockopen( $parts['host'], isset( $parts['port'] ) ? $parts['port'] : 80, $errno, $errstr, ( $this->async ) ? 1 : null );

			//check to make sure there wasn't an error opening the socket
			if ( $fp === false )
			{
				return false;
			}

			//determine if there is a query string
			$query = ( isset( $parts['query'] ) && ! empty( $parts['query'] ) ) ?  $parts['query'] : '';

			$path = isset( $parts['path'] ) ? $parts['path'] : '';

			$out = 'GET ' . $path . ( ! empty( $parts['query'] ) ? '?' . $query : '' ) . ' HTTP/1.1' . "\r\n";
			$out .= 'Host: ' . $parts['host'] . "\r\n";
			$out .= 'Content-Type: application/x-www-form-urlencoded' . "\r\n";
			$out .= 'Content-Length: ' . strlen( $query ) . "\r\n";
			$out .= 'Connection: Close' . "\r\n\r\n";
			@fwrite( $fp, $out );
			@fclose( $fp );
			return true;
		}

		return false;
	}

	/**
	 * Resets the class variables used in breaking.
	 */
	private function reset_breaking()
	{
		//items to delete without refreshing
		$this->deletes = array();

		//items to refresh
		$this->refreshers = array();

		//tags to clear from db
		$this->tags = array();
	}

	/**
	 * Sets up breaking.
	 */
	private function setup_breaking()
	{
		//let's not worry about timing out
		set_time_limit( 0 );

		//load the class if needed
		$this->include_library('Ce_cache_drivers');

		//get the driver classes
		$this->classes = Ce_cache_drivers::get_all_driver_classes();

		//the prefix for this site
		$this->prefix = Ce_cache_utils::get_site_prefix();

		//reset deletes, refreshers, and tags
		$this->reset_breaking();
	}

	/**
	 * Determines which URLs to delete and/or refresh based on an item's path.
	 *
	 * @param array $paths An array of item paths.
	 * @param bool $refresh Whether or not to refresh an item's URL after it is cleared.
	 * @param int $refresh_time The time between pinging each URL if refreshing.
	 */
	private function add_paths( $paths = array(), $refresh = false, $refresh_time = 1 )
	{
		if ( is_array( $paths ) && count( $paths ) > 0 ) //we have one or more paths
		{
			foreach ( $paths as $path )
			{
				//is this path a regex?
				$is_regex = ( substr( $path, -1, 1 ) == '#' );

				//determine path type
				if ( strpos( $path, 'local/') === 0 )
				{
					$type = 'local';
				}
				else if ( strpos( $path, 'static/') === 0 )
				{
					$type = 'static';
				}
				else if ( strpos( $path, 'global/') === 0 )
				{
					$type = 'global';
				}
				else //this should never happen...
				{
					continue;
				}

				//the query path
				$query = $this->prefix.$path;

				//handle regex option
				if ( $is_regex )
				{
					//the part of the path after the "type/"
					$sub_path = substr( $path, strlen( $type ) + 1 );
					$sub_path = rtrim($sub_path, '#'); //trim the #'s
					$sub_path = str_replace('#', '\#', $sub_path);

					//we want our drivers to do as little work as possible, so we try to include as much of the path as possible
					$query = $this->prefix . $type . '/';
					$pieces = explode('/', $sub_path);
					foreach ($pieces as $index => $piece)
					{
						if (preg_match('@[^a-zA-Z0-9_-]@i', $piece)) //checks if there are any non-word characters
						{
							break;
						}
						else
						{
							$query .= '/' . $piece;
						}
					}
				}

				//remove duplicate slashes
				$query = Ce_cache_utils::remove_duplicate_slashes($query);

				//loop through the driver classes
				foreach ( $this->classes as $class )
				{
					$driver = $class->name();

					//if the path is static and the driver is not, or if the path is not static and the driver is, then we need to move on
					if ( ( $type == 'static' && $driver != 'static') || ( $type != 'static' && $driver == 'static' ) )
					{
						continue;
					}

					//attempt to get all items
					$hits = $class->get_all( $query );

					if ( ! empty($hits) && is_array($hits) ) //we have the items
					{
						//loop through and add the items to the all_items array
						foreach ( $hits as $hit )
						{
							//get the hit id
							$hit_id = ($class->get_all_is_array() ? $hit['id'] : $hit);

							//the final id
							$final_id = $query . $hit_id;

							if ( $is_regex )
							{
								//see if the sub_path pattern matches the sub_hit
								if (!preg_match('#'.$sub_path.'#', $hit_id))
								{
									continue;
								}
							}

							if ( $type == 'global' ) //global items cannot refresh
							{
								$this->deletes[] = $final_id;
							}
							else //local and static items can optionally refresh
							{
								if ( ! $refresh ) //delete right away
								{
									$this->deletes[] = $final_id;
								}
								else //delete and refresh
								{
									$this->refreshers[$final_id] = $refresh_time;
								}
							}
						}
					}
					unset( $hits );
				}
			}
		}
	}

	/**
	 * Determines which URLs to delete and/or refresh based on an item's tag(s).
	 *
	 * @param array $tags An array of item tags.
	 * @param bool $refresh Whether or not to refresh an item's URL after it is cleared.
	 * @param int $refresh_time The time between pinging each URL if refreshing.
	 */
	private function add_tags( $tags = array(), $refresh = false, $refresh_time = 1 )
	{
		if ( is_array( $tags ) && count( $tags ) > 0 ) //we have one or more tags
		{
			//add these tags to the overall tags array
			$this->tags = array_merge( $this->tags, $tags );

			//escape all of the tags, as we will be using them in a query
			foreach ( $tags as $index => $tag )
			{
				$tags[ $index ] = ee()->db->escape_str( $tag );
			}

			$tagged_items = ee()->db->query( "
				SELECT item_id
				FROM exp_ce_cache_tagged_items
				WHERE SUBSTRING( item_id, 1, " . strlen( $this->prefix )  . " ) = '" . ee()->db->escape_str( $this->prefix ) . "'
				AND tag IN ( '" . implode( "', '", $tags ) . "' )
				ORDER BY item_id ASC");

			if ( $tagged_items->num_rows() > 0 )
			{
				$hits = $tagged_items->result_array();

				foreach ( $hits as $hit )
				{
					if ( $refresh )
					{
						$this->refreshers[ $hit['item_id'] ] = $refresh_time;
					}
					else
					{
						$this->deletes[] = $hit['item_id'];
					}
				}
				unset( $hits );
			}
			$tagged_items->free_result();
		}
	}

	/**
	 * Handles breaking the cache URLs.
	 *
	 * @param bool $refresh_urls_on_shutdown
	 */
	private function run_breaking( $refresh_urls_on_shutdown = false )
	{
		//todo use the tags model to clear the tags
		//now let's clear the tags
		if ( count( $this->tags ) > 0 )
		{
			ee()->db->where_in( 'tag', $this->tags );
			ee()->db->delete( 'ce_cache_tagged_items' );
		}

		//now that we have all of our items to delete, let's delete them
		foreach( $this->classes as $class ) //loop through the driver classes
		{
			//loop through the delete items and delete them
			foreach ( $this->deletes as $item )
			{
				$class->delete( $item );
			}
		}

		//merge the delete array with the items from the refreshers array
		$deletes = array_merge( $this->deletes, array_keys( $this->refreshers ) );

		//now let's clear any applicable tagged items
		if ( count( $deletes ) > 0 )
		{
			ee()->db->where_in( 'item_id', $deletes );
			ee()->db->delete( 'ce_cache_tagged_items' );
		}

		//unset the deletes array as it is no longer needed
		unset( $deletes );

		//create the URL
		$url = ee()->config->slash_item('site_url');

		//see if this install has the ability to recreate the cache
		$can_recreate = ( function_exists( 'curl_init' ) || function_exists( 'fsockopen' ) );

		if ( $can_recreate ) //delete and refresh the items
		{

			//include CE Cache Utilities
			$this->include_library('Ce_cache_utils');

			//make sure that allow_url_fopen is set to true if permissible
			@ini_set('allow_url_fopen', true);
			//some servers will not accept the asynchronous requests if there is no user_agent
			@ini_set('user_agent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:5.0) Gecko/20100101');

			$urls = array();

			foreach ( $this->refreshers as $item => $time )
			{
				//loop through the driver classes and delete this item
				foreach( $this->classes as $class )
				{
					$class->delete( $item );
				}

				//trim the prefix and 'local/' or 'static/' from the beginning of the path
				$path = (strpos($item, $this->prefix . 'local/') === 0 ) ? substr( $item, strlen( $this->prefix ) + 6 ) : substr( $item, strlen( $this->prefix ) + 7 );

				//find the last '/'
				$last_slash = strrpos( $path, '/' );

				//if a last '/' was found, get the path up to that point
				$path = ( $last_slash === false ) ? '' : substr( $path, 0, $last_slash );

				$urls[ Ce_cache_utils::remove_duplicate_slashes( $url . '/' . $path ) ] = $time;
			}

			//refresh the URLs
			if ( $refresh_urls_on_shutdown )
			{
				//since we are deleting entries, we're going to make this happen as close to the end of execution as possible,
				//since we want to be sure that the entries are removed from the database before refreshing any URLs
				register_shutdown_function( array( $this, 'refresh_urls' ), $urls );
			}
			else
			{
				$this->refresh_urls( $urls );
			}
		}
		else //just delete the items, as there is no way to recreate them
		{
			//loop through the driver classes
			foreach( $this->classes as $class )
			{
				//loop through the delete items and delete them
				foreach ( $this->refreshers as $item => $time )
				{
					$class->delete( $item );
				}
			}
		}

		//reset deletes, refreshers, and tags
		$this->reset_breaking();
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
}