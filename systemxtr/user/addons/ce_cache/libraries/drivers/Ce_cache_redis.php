<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Redis driver.
 *
 * See http://redis.io for more info about Redis.
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2017 Causing Effect
 * @license		http://docs.causingeffect.com/expressionengine/ce-cache/license-agreement.html
 * @link		https://www.causingeffect.com
 */
class Ce_cache_redis extends Ce_cache_driver
{
	private $redis;
	private $is_set_up = false;
	private $is_predis = false;

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Is the driver supported?
	 *
	 * @return bool
	 */
	public function is_supported()
	{
		//make sure the extension is loaded
		return ( extension_loaded( 'redis' ) || version_compare( phpversion(), '5.3.0', '>=' ) );
	}

	/**
	 * The driver's name.
	 *
	 * @return mixed
	 */
	public function name()
	{
		return str_replace( 'Ce_cache_', '', __CLASS__ );
	}

	/**
	 * Store a cache item. The item will be stored in an array with the keys 'ttl', 'made', and 'content'.
	 *
	 * @link http://redis.io/commands/set
	 * @param string $id The cache item's id.
	 * @param string $content The content to store.
	 * @param int $seconds The time to live for the cached item in seconds. Zero (0) seconds will store the item for a long time. Default is 360 seconds.
	 * @return bool
	 */
	public function set( $id, $content = '', $seconds = 360 )
	{
		if ( ! $this->setup() )
		{
			return false;
		}

		//create the data array
		$data = array(
			'ttl' => $seconds,
			'made' => time(),
			'content' => $content
		);

		$success = $this->redis->hMset( $id, $data );

		if ( $success )
		{
			if ( $seconds )
			{
				$this->redis->expire( $id, $seconds );
			}

			return true;
		}

		return false;
	}

	/**
	 * Retrieve an item from the cache.
	 *
	 * @link http://redis.io/commands/get
	 * @param string $id The cache item's id.
	 * @return mixed
	 */
	public function get( $id )
	{
		if ( ! $this->setup() )
		{
			return false;
		}

		//fetch the data
		$data = $this->redis->hmGet( $id, array( 'ttl', 'made', 'content' ) );

		if ( $this->is_predis )
		{
			$data = $this->check_predis_hmGet( $data );
		}

		//the data should be in the array format we left it in. If it isn't, we'll just return false.
		return ( is_array( $data ) && isset( $data['content'] ) ) ? $data['content'] : false;
	}

	/**
	 * Remove an item from the cache.
	 *
	 * @link http://redis.io/commands/del
	 * @param string $id The cache item's id.
	 * @return bool
	 */
	public function delete( $id )
	{
		if ( ! $this->setup() )
		{
			return false;
		}

		if ( $this->is_predis ) //predis
		{
			return ( $this->redis->del( $id ) === 1 );
		}
		else //phpredis
		{
			if ( method_exists( $this->redis, 'del' ) )
			{
				return ( $this->redis->del( $id ) === 1 );
			}
			else if ( method_exists( $this->redis, 'delete' ) )
			{
				return ( $this->redis->delete( $id ) === 1 );
			}
		}

		return false;
	}

	/**
	 * Gives information about the item.
	 *
	 * @param string $id The cache item's id.
	 * @param bool $get_content Include the content in the return array?
	 * @return array|bool
	 */
	public function meta( $id, $get_content = true )
	{
		if ( ! $this->setup() )
		{
			return false;
		}

		//attempt to get the stored data
		$data = $this->redis->hmGet( $id, array( 'ttl', 'made', 'content' ) );

		if ( $this->is_predis )
		{
			$data = $this->check_predis_hmGet( $data );
		}

		//make sure the data is in the format we save data in
		if ( empty( $data ) || ! is_array( $data ) || count( $data ) != 3 )
		{
			return false;
		}

		//determine the expiration timestamp
		$expiry = ( $data['ttl'] == 0 ) ? 0 : $data['made'] + $data['ttl'];

		//get the content size
		$size = parent::size( $data['content'] );

		//return the meta array
		$final = array(
			//if the time to live is 0, the data will not auto-expire
			'expiry' => $expiry,
			'made' => $data['made'],
			'ttl' => $data['ttl'],
			'ttl_remaining' => ( $data['ttl'] == 0 ) ? 0 : ( $expiry - time() ),
			'size' => parent::convert_size( $size ),
			'size_raw' => $size
		);

		//include the content in the final array?
		if ( $get_content )
		{
			$final['content'] = $data['content'];
		}

		unset( $data, $expiry );

		return $final;
	}

	/**
	 * Purges the entire cache.
	 *
	 * @link http://redis.io/commands/flushall
	 * @return bool
	 */
	public function clear()
	{
		if ( ! $this->setup() )
		{
			return false;
		}

		//clear the Redis database cache
		return $this->redis->flushDB();
	}

	/**
	 * Retrieves all of the cached items at the specified relative path.
	 *
	 * @link http://redis.io/commands/keys
	 * @param string $relative_path The relative path from the cache base.
	 * @return array
	 */
	public function get_all( $relative_path )
	{
		if ( ! $this->setup() )
		{
			return false;
		}

		//ensure a trailing slash - to maintain consistency with other drivers
		$relative_path = rtrim( $relative_path, '/' ) . '/';

		//get all keys that start with the path
		if ( $this->is_predis ) //predis
		{
			$keys = $this->redis->keys( $relative_path . '*' );
		}
		else //phpredis
		{
			if ( method_exists( $this->redis, 'keys' ) )
			{
				$keys = $this->redis->keys( $relative_path . '*' );
			}
			else if ( method_exists( $this->redis, 'getKeys' ) ) //support for much older versions of phpredis
			{
				$keys = $this->redis->getKeys( $relative_path . '*' );
			}
			else
			{
				return false;
			}
		}



		$items = array();

		foreach ( $keys as $key )
		{
			$items[] = substr( $key, strlen( $relative_path ) );
		}

		sort( $items, SORT_STRING );

		return $items;
	}

	/**
	 * Retrieves basic info about the cache.
	 *
	 * @link http://redis.io/commands/info
	 * @return array|bool
	 */
	public function info()
	{
		if ( ! $this->setup() )
		{
			return false;
		}

		return $this->redis->info();
	}

	/**
	 * Sets up the Redis object if it has not been setup already and adds any servers from the config or global array, or uses the default if none are found.
	 *
	 * @return bool Return true if the class is setup, or false on failure.
	 */
	private function setup()
	{
		if ( ! $this->is_set_up ) //if not already setup
		{
			//servers
			$servers = array();

			//make sure this driver is supported
			if ( ! $this->is_supported() )
			{
				return false;
			}

			if ( class_exists('Redis') )
			{
				//instantiate the class
				$redis = new Redis();

				//default server array
				$servers[] = array( '127.0.0.1' );

				//server config
				$config = '';

				//check for config settings
				if ( ! empty( ee()->config->_global_vars[ 'ce_cache_redis_servers' ] ) ) //first check the global array
				{
					$config = ee()->config->_global_vars[ 'ce_cache_redis_servers' ];
				}
				else if ( ee()->config->item( 'ce_cache_redis_servers' ) != false ) //then check the config
				{
					$config = ee()->config->item( 'ce_cache_redis_servers' );
				}

				if ( ! empty( $config ) ) //we have some settings
				{
					//if the config is not an array, let's explode it out to be one
					if ( ! is_array( $config ) )
					{
						$servers = explode( '|', $config );
						foreach ( $servers as $index => $server )
						{
							$servers[$index] = explode( ',', $server );
						}
					}
					else
					{
						$servers = $config;
					}
				}

				$success_count = 0;

				//add each server
				foreach ( $servers as $server )
				{
					try
					{
						$success = call_user_func_array( array( $redis, 'connect' ), $server );

						//authentication contribution from Juking the Stats: http://devot-ee.com/add-ons/support/ce-cache/viewthread/9084
						if ( ee()->config->item('ce_cache_redis_auth') != false )
						{
							$redis->auth( ee()->config->item( 'ce_cache_redis_auth' ) );
						}

						//select a particular db if it's specified
						if ( false !== $db_index = ee()->config->item('ce_cache_redis_db_index') )
						{
							$redis->select( intval( $db_index ) );
						}
					}
					catch ( RedisException $e )
					{
						parent::log_messages( 'Caught Redis exception "' . $e->getMessage() . '" in "' . __CLASS__ . '" "' . __METHOD__ . '" method.' );
						$success = false;
					}

					if ( $success )
					{
						$success_count++;
					}
				}

				if ( ! $success_count )
				{
					return false;
				}
			}
			else
			{
				if ( ! class_exists( '\Predis\Autoloader' ) )
				{
					require PATH_THIRD . 'ce_cache/libraries/drivers/Predis/Autoloader.php';
					Predis\Autoloader::register();
				}

				//check for config settings
				if ( ! empty( ee()->config->_global_vars[ 'ce_cache_redis_servers' ] ) ) //first check the global array
				{
					$config = ee()->config->_global_vars[ 'ce_cache_redis_servers' ];
				}
				else if ( ee()->config->item( 'ce_cache_redis_servers' ) != false ) //then check the config
				{
					$config = ee()->config->item( 'ce_cache_redis_servers' );
				}

				//no config settings were found
				if ( empty( $config ) )
				{
					return false;
				}

				try
				{
					$redis = new Predis\Client( $config );
				}
				catch ( Exception $e )
				{
					parent::log_messages( 'Caught Predis exception "' . $e->getMessage() . '" in "' . __CLASS__ . '" "' . __METHOD__ . '" method.' );
					return false;
				}

				$this->is_predis = true;
			}

			$this->redis = $redis;

			//flag that setup has been completed
			$this->is_set_up = true;
		}

		return true;
	}

	/**
	 * Changes Predis numeric keys back to the expected keys, as needed.
	 *
	 * @param array $data
	 * @return array|bool
	 */
	private function check_predis_hmGet( $data )
	{
		if ( ! isset( $data['content'] ) ) //predis just returns the numbers, but not the keys
		{
			if ( empty( $data[0] ) && empty( $data[1] ) && empty( $data[2] ) )
			{
				$data = false;
			}
			else if ( is_array( $data ) && isset( $data[1] ) && count( $data ) == 3 )
			{
				$data = array( 'ttl' => $data[0], 'made' => $data[1], 'content' =>  $data[2] );
			}
		}

		return $data;
	}

	/**
	 * Close the redis connection on destruct.
	 */
	public function __destruct()
	{
		if ( $this->is_set_up && ! $this->is_predis )
		{
			$this->redis->close();
		}
	}
}