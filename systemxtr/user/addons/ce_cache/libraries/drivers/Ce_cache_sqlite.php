<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - Database driver.
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2017 Causing Effect
 * @license		http://docs.causingeffect.com/expressionengine/ce-cache/license-agreement.html
 * @link		https://www.causingeffect.com
 */
class Ce_cache_sqlite extends Ce_cache_driver
{
	protected $get_all_is_array = true;

	private $sqlite3;
	private $is_set_up = false;
	private $cache_base = '';
	private $db_location = '';

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
		//see if the native class exists
		return class_exists( 'SQLite3' );
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
	 * @param string $id The cache item's id.
	 * @param string $content The content to store.
	 * @param int $seconds The time to live for the cached item in seconds. Zero (0) seconds will result store the item for a long, long time. Default is 360 seconds.
	 * @return bool
	 */
	public function set( $id, $content = '', $seconds = 360 )
	{
		if ( !$this->setup() || !$this->open() )
		{
			return false;
		}
		$this->delete( $id );
		$this->close();

		if ( ! $this->open() )
		{
			return false;
		}

		//create the data
		$id = SQLite3::escapeString( $id );
		$ttl = $seconds;
		$made = time();
		$content = SQLite3::escapeString( $content );

		//prepare insert statement
		$sql = "INSERT INTO ce_cache ( id, ttl, made, content) VALUES ( '$id', '$ttl', '$made', '$content' );";

		//attempt to store the data
		$result = $this->sqlite3->exec( $sql );
		$this->close();
		return $result;
	}

	/**
	 * Retrieve an item from the cache.
	 *
	 * @param string $id The cache item's id.
	 * @return mixed
	 */
	public function get( $id )
	{
		if ( ! $this->setup() )
		{
			return false;
		}

		//get rid of the old entries
		$this->flush();

		if ( ! $this->open() )
		{
			return false;
		}
		$id = SQLite3::escapeString( $id );

		//fetch the data
		$results = $this->sqlite3->query( "SELECT ttl, made, content FROM ce_cache WHERE id = '$id';");

		if ( $results )
		{
			if ( $data = $results->fetchArray() )
			{
				$this->close();
				//if seconds is set to 0 then the cache is never deleted, unless done so manually
				if ( $data['ttl'] != 0 && time() > $data['made'] + $data['ttl'] )
				{
					//the item has expired, get rid of it
					$this->delete( $id );

					return false;
				}

				//return the content
				return $data['content'];
			}
		}

		$this->close();

		return false;
	}

	/**
	 * Clears all expired caches from db.
	 *
	 * @return bool
	 */
	private function flush()
	{
		if ( !$this->setup() || !$this->open() )
		{
			return false;
		}

		$this->sqlite3->query( "DELETE FROM ce_cache WHERE ttl != 0 AND " . time() . " > made + ttl" );

		$this->close();
	}

	/**
	 * Remove an item from the cache.
	 *
	 * @param string $id The cache item's id.
	 * @return bool
	 */
	public function delete( $id )
	{
		if ( !$this->setup() || !$this->open() )
		{
			return false;
		}

		$id = SQLite3::escapeString( $id );

		$this->sqlite3->query( "DELETE FROM ce_cache WHERE id = '$id';" );

		$this->close();

		return true;
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
		if ( !$this->setup() || !$this->open() )
		{
			return false;
		}

		$id = SQLite3::escapeString( $id );

		//fetch the data
		$results = $this->sqlite3->query( "SELECT
		made,
		ttl,
		CASE ttl WHEN '0' then '0' ELSE ( made + ttl) END as expiry,
		CASE ttl WHEN '0' then '0' ELSE ( CAST( made AS UNSIGNED) + CAST( ttl AS UNSIGNED) - strftime('%s','now') ) END as ttl_remaining,
		content
		FROM ce_cache
		WHERE id = '$id';" );

		if ( $results )
		{
			if ( $data = $results->fetchArray() )
			{
				$this->close();

				//if seconds is set to 0 then the cache is never deleted, unless done so manually
				if ( $data['ttl'] != 0 && time() > $data['expiry'] )
				{
					//the item has expired, get rid of it
					$this->delete( $id );

					return false;
				}

				//get the content size
				$size = parent::size( $data['content'] );

				//set the size variables
				$data[ 'size' ] = parent::convert_size( $size );
				$data[ 'size_raw' ] = $size;

				//include the content in the final array?
				if ( ! $get_content )
				{
					unset( $data['content'] );
				}

				return $data;
			}
		}

		$this->close();

		return false;
	}

	/**
	 * Purges the entire cache.
	 *
	 * @return bool
	 */
	public function clear()
	{
		if ( ! $this->setup() )
		{
			return false;
		}

		//delete the table contents
		$outcome = $this->sqlite3->exec( 'DELETE FROM ce_cache;' );

		//repackage db space
		$this->sqlite3->exec( 'VACUUM' );

		$this->close();

		return $outcome;
	}

	/**
	 * Retrieves all of the cached items at the specified relative path.
	 *
	 * This method differs from the other drivers, as it also returns the metadata for the objects, without the 'content' item. Most of the other driver implementations only return the item id.
	 *
	 * @param string $relative_path The relative path from the cache base.
	 * @return array|bool
	 */
	public function get_all( $relative_path )
	{
		if ( ! $this->setup() )
		{
			return false;
		}

		//get rid of the old entries
		$this->flush();

		if ( ! $this->open() )
		{
			return false;
		}

		$results = $this->sqlite3->query( "SELECT
		SUBSTR( id, " . ( strlen( $relative_path ) + 1 )  . ") as id,
		ttl,
		CASE ttl WHEN '0' then '0' ELSE (made + ttl) END as expiry,
		CASE ttl WHEN '0' then '0' ELSE (made + ttl - strftime('%s','now') ) END as ttl_remaining,
		made
		FROM ce_cache
		WHERE SUBSTR( id, 1, " . strlen( $relative_path )  . ") = '" . SQLite3::escapeString( $relative_path ) . "'
		ORDER BY id ASC;
		" );

		if ( $results )
		{
			$rows = array();
			$deletes = array();

			while ( $row = $results->fetchArray( SQLITE3_ASSOC ) )
			{
				//print_r( $row );
				if ( $row['ttl_remaining'] < 0 )
				{
					$deletes[] = $relative_path . $row['id'];
				}
				else
				{
					$rows[] = $row;
				}
			}

			//close the connection
			$this->close();

			//delete expired items, if needed
			foreach ( $deletes as $delete )
			{
				$this->delete( $delete );
			}

			if ( empty( $rows ) )
			{
				return false;
			}

			return $rows;
		}

		//close the connection
		$this->close();

		return false;
	}

	/**
	 * Retrieves basic info about the cache.
	 *
	 * @return array|bool
	 */
	public function info()
	{
		if ( ! $this->setup() )
		{
			return false;
		}

		//TODO add this in
		return false;
	}

	/**
	 * Sets up the SQLite object if it has not been setup already and adds any servers from the config or global array, or uses the default if none are found.
	 *
	 * @return bool Return true if the class is setup, or false on failure.
	 */
	private function setup()
	{
		if ( ! $this->is_set_up )
		{
			//include CE Cache Utilities
			if ( ! class_exists( 'Ce_cache_utils' ) )
			{
				include PATH_THIRD . 'ce_cache/libraries/Ce_cache_utils.php';
			}

			//set the base cache path
			$this->cache_base = str_replace('\\', '/', PATH_CACHE);

			//override setting
			$sqlite_path_config_override = Ce_cache_utils::determine_setting( 'sqlite_path', false, '', true, false );
			if ( $sqlite_path_config_override )
			{
				$this->cache_base = $sqlite_path_config_override;
			}

			$this->db_location = $this->cache_base . 'ce_cache.sqlite3.db';

			if ( ! $this->open() )
			{
				return false;
			}

			if ( ! $this->sqlite3->exec('CREATE TABLE IF NOT EXISTS ce_cache ( id TEXT PRIMARY KEY, ttl INTEGER, made INTEGER, content TEXT )') )
			{
				trigger_error( htmlentities($this->sqlite3->lastErrorMsg()) );
				return false;
			}

			$this->close();

			$this->set_up = true;
		}

		return true;
	}

	/**
	 * Opens up the database.
	 *
	 * @return bool
	 */
	private function open()
	{
		//attempt to initialize the database
		try
		{
			$sqlite3 = new SQLite3( $this->db_location );
		}
		catch ( Exception $e )
		{
			parent::log_messages( 'Caught exception "' . $e->getMessage() . '" in "' . __CLASS__ . '" "' . __METHOD__ . '" method.' );
			return false;
		}

		$this->sqlite3 = $sqlite3;
		if ( function_exists( 'SQLite3::busyTimeout' ) )
		{
			$this->sqlite3->busyTimeout(1000);
		}

		return true;
	}

	/**
	 * Closes the database if it's open.
	 */
	private function close()
	{
		if ( isset( $this->sqlite3 ) && is_resource( $this->sqlite3 ) )
		{
			$this->sqlite3->close();
		}
	}
}