<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache - File Utilities class
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2017 Causing Effect
 * @license		http://docs.causingeffect.com/expressionengine/ce-cache/license-agreement.html
 * @link		https://www.causingeffect.com
 */
class Ce_cache_file_utils
{
	/**
	 * Deletes all files in a path, and optionally the directory itself.
	 *
	 * @param string $path The server path to the directory.
	 * @param bool $delete_dir Whether or not to delete the top-level directory (the child directories will always be deleted).
	 * @return bool
	 */
	public static function delete_files( $path, $delete_dir = false )
	{
		//trim the trailing slash
		$path = rtrim( $path, DIRECTORY_SEPARATOR );

		//open the directory for reading
		if ( ! $current_dir = @opendir( $path ) )
		{
			return false;
		}

		//read through the directory
		while( false !== ( $filename = @readdir( $current_dir ) ) )
		{
			if ( $filename != '.' and $filename != '..') //skip dots
			{
				if ( is_dir( $path . DIRECTORY_SEPARATOR . $filename ) ) //directory
				{
					self::delete_files( $path . DIRECTORY_SEPARATOR . $filename, true );
				}
				else //file
				{
					unlink( $path . DIRECTORY_SEPARATOR . $filename );
				}
			}
		}

		//close the directory
		@closedir($current_dir);

		//delete the directory
		return ( $delete_dir ) ? @rmdir($path) : true;
	}
}