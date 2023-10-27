<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * CE Lossless
 *
 * Last Updated: 22 March 2013
 *
 * License:
 * CE Lossless is licensed under the Commercial License Agreement found at http://www.causingeffect.com/software/expressionengine/ce-lossless/license-agreement
 * Here are a couple of specific points from the license to note again:
 *   - One license grants the right to perform one installation of CE Lossless. Each additional installation of CE Lossless requires an additional purchased license.
 *   - You may not reproduce, distribute, or transfer CE Lossless, or portions thereof, to any third party.
 *   - You may not sell, rent, lease, assign, or sublet CE Lossless or portions thereof.
 *   - You may not grant rights to any other person.
 *   - You may not use CE Lossless in violation of any United States or international law or regulation.
 *  If you have any questions about the terms of the license, or would like to report abuse of its terms, please contact software@causingeffect.com.
 *
 * @package CE Lossless
 * @author Causing Effect, Aaron Waldon
 * @link http://www.causingeffect.com
 * @copyright 2012
 * @version 1.1
 * @license http://www.causingeffect.com/software/expressionengine/ce-lossless/license-agreement Causing Effect Commercial License Agreement
 */

class Ce_lossless
{
	public static $debug_messages = array();
	public static $debug_mode = false;
	public static $linux_path = '';
	public static $mac_path = '/usr/local/bin/';
	protected static $confirmed_drivers = array();
	protected static $allowed_drivers = array('pngout', 'optipng', 'pngcrush', 'jpegtran', 'jpegoptim', 'gifsicle', 'smushit', 'pngquant');
	protected static $enabled_drivers = array();
	/**
	 * Configuration.
	 *
	 * @type array
	 */
	protected static $commands = array(
		'gifsicle' => '-b -O2 --no-names --no-comments --same-delay --same-loopcount --no-warnings {{path}}',
		'pngquant' => '--quality=70-85 --ext=.png --force {{path}}',
		'pngcrush' => '-reduce -fix -rem alla -cc -q {{path}} {{temp}}',
		'optipng' => '-quiet -o7 {{path}}',
		'pngout' => '-q {{path}}',
		//note that the smushit driver is not configurable
		'jpegtran' => '-copy none -outfile {{temp}} -optimize -progressive {{path}}',
		'jpegoptim' => '--strip-all -q {{path}}'
	);

	/**
	 * Sets the enabled drivers.
	 *
	 * @static
	 * @param array $drivers
	 */
	public static function set_enabled_drivers($drivers = array())
	{
		foreach ($drivers as $driver)
		{
			if (in_array($driver, self::$allowed_drivers))
			{
				self::$enabled_drivers[] = $driver;
			}
		}

		self::$enabled_drivers = array_unique(self::$enabled_drivers);
	}

	/**
	 * Returns the enabled drivers.
	 *
	 * @static
	 * @return array
	 */
	public static function get_enabled_drivers()
	{
		return self::$enabled_drivers;
	}

	/**
	 * Return a specific configuration key value.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public static function get_config($key) {
		return isset(self::$commands[$key]) ? self::$commands[$key] : null;
	}

	/**
	 * Apply multiple configurations at once.
	 *
	 * @param array $commands
	 * @return self
	 */
	public static function set_config(array $commands)
	{
		if ( empty( $commands ) || ! is_array( $commands ) ) {
			return;
		}

		foreach ($commands as $key => $value) {
			if ( isset( self::$commands[$key] ) )
			{
				self::$commands[$key] = $value;
			}
		}
	}

	/**
	 * Compresses the image.
	 *
	 * @static
	 * @param string $path The server path to the image.
	 * @param string $type The image type or mime type. If left empty, the image type will be determined automatically.
	 * @param bool $check_path Check if the file exists? If you already know that the file exists for sure, set this to false.
	 * @return bool
	 */
	public static function compress($path = '', $type = '', $check_path = true)
	{
		//reset the debug messages
		self::$debug_messages = array();

		if (!function_exists('exec'))
		{
			self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' The exec() function is not enabled on this server. Only the smushit driver can be used without it!';

			if (in_array('smushit', self::$enabled_drivers))
			{
				self::$enabled_drivers = array('smushit');
			}
			else
			{
				return false;
			}

		}

		//if there are no enabled drivers, there is no reason to stick around
		if (empty(self::$enabled_drivers) && self::$debug_mode)
		{
			self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' There are no enabled drivers.';
			return false;
		}

		//make sure the path exists
		$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
		if (empty($path) || ($check_path && !is_file($path)))
		{
			if (self::$debug_mode)
			{
				self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' The file "' . $path . '" could not be found.';
			}
			return false;
		}

		//if no type is provided, let's make sure we are working with a valid type
		if (!$type)
		{
			$info = @getimagesize($path);
			if (empty($info[2]))
			{
				return false;
			}

			switch ($info[2])
			{
				case IMAGETYPE_GIF:
					$type = 'gif';
					break;
				case IMAGETYPE_JPEG:
					$type = 'jpg';
					break;
				case IMAGETYPE_PNG:
					$type = 'png';
					break;
				default:
					if (self::$debug_mode)
					{
						self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' The file "' . $path . '" is not a valid gif, jpg, or png image.';
					}
					return false;
			}
		}

		if ( self::$debug_mode )
		{
			self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' CE Lossless is beginning processing of the "' . $path . '" ' . $type . ' image.';
		}

		//success count
		$success_count = 0;

		//if debug mode, get the original file size
		if (self::$debug_mode)
		{
			//the original file size
			self::clear_stat($path);
			$orig = filesize($path);
		}

		//process the images
		switch (strtolower($type))
		{
			case 'gif':
			case 'image/gif':
				if (in_array('gifsicle', self::$enabled_drivers) && !self::go($path, 'gifsicle', self::$commands['gifsicle']))
				{
					return false;
				}
				break;
			case 'png':
			case 'image/png':
			case 'image/x-png':
				if (in_array('pngquant', self::$enabled_drivers) && self::go($path, 'pngquant', self::$commands['pngquant']))
				{
					$success_count++;
				}

				//I thought pngcrush could crush files in place, but it doesn't appear to be working on Windows, so we're using the temp output method.
				if (in_array('pngcrush', self::$enabled_drivers) && self::go($path, 'pngcrush', self::$commands['pngcrush'], true))
				{
					$success_count++;
				}

				if (in_array('optipng', self::$enabled_drivers) && self::go($path, 'optipng', self::$commands['optipng']))
				{
					$success_count++;
				}

				if (in_array('pngout', self::$enabled_drivers) && self::go($path, 'pngout', self::$commands['pngout']))
				{
					$success_count++;
				}

				if (in_array('smushit', self::$enabled_drivers) && self::smushit($path))
				{
					$success_count++;
				}
				break;
			case 'jpg':
			case 'jpeg':
			case 'image/jpeg':
			case 'image/pjpeg':
				if (in_array('jpegtran', self::$enabled_drivers) && self::go($path, 'jpegtran', self::$commands['jpegtran'], true))
				{
					$success_count++;
				}

				if (in_array('jpegoptim', self::$enabled_drivers) && self::go($path, 'jpegoptim', self::$commands['jpegoptim']))
				{
					$success_count++;
				}

				if (in_array('smushit', self::$enabled_drivers) && self::smushit($path))
				{
					$success_count++;
				}
				break;
			default:
				if (self::$debug_mode)
				{
					self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' The file type is not valid.';
				}
				return false;
		}

		if ($success_count > 1 && self::$debug_mode)
		{
			//the new file size
			self::clear_stat($path);
			$new = filesize($path);
			$difference = $orig - $new;
			$difference_percent = round((1 - ($new / $orig)) * 100, 2);

			$orig = self::convert($orig);
			$new = self::convert($new);
			$difference = self::convert($difference);

			self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' The image was compressed from ' . $orig . ' to ' . $new . ' for a total savings of ' . $difference . ' (' . $difference_percent . '%).';
		}

		return true;
	}

	/**
	 * Do the actual image processing.
	 *
	 * @static
	 * @param      $path
	 * @param      $driver
	 * @param      $args
	 * @param bool $manual_copy
	 * @return bool
	 */
	protected static function go($path, $driver, $args, $manual_copy = false)
	{
		if (!in_array($driver, self::$allowed_drivers))
		{
			return false;
		}

		$is_windows = (strtolower(substr(PHP_OS, 0, 3)) === 'win');
		$is_mac = (strtolower(substr(PHP_OS, 0, 6)) === 'darwin');

		//I was never able to get gifsicle to work on windows, so I am going to disable its use
		if ($is_windows && $driver == 'gifsicle')
		{
			if (self::$debug_mode)
			{
				self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' Sorry, but CE Lossless does not support gifsicle on Windows.';
			}
			return false;
		}

		//if the image will need to be manually copied, we'll need a temp file
		$temp = $manual_copy ? str_replace('/', DIRECTORY_SEPARATOR, $path . '.tmp') : '';

		//if debug mode, we'll get the file size before compression
		if (self::$debug_mode)
		{
			//the original file size
			self::clear_stat($path);
			$orig = filesize($path);
		}

		//parse the args
		$args = str_replace(array('{{path}}', '{{temp}}'), array(escapeshellarg($path), escapeshellarg($temp)), $args);
		$args = trim($args);

		if ($is_windows || $is_mac || !empty(self::$linux_path)) //windows, mac, or linux binary
		{
			//set the path to the included driver executable
			if ($is_windows) //windows
			{
				$binary = PATH_THIRD . 'ce_lossless/win/' . $driver . '.exe';
				$binary = str_replace('/', DIRECTORY_SEPARATOR, $binary);

				//make sure the driver's binary is found
				if (!isset(self::$confirmed_drivers[$driver]))
				{
					self::$confirmed_drivers[$driver] = file_exists($binary);
				}
			}
			else //mac or linux with binary path
			{
				//note: OS X will not return the output of the which command via PHP like the rest of Linux will, so we have to check for the actual binary's existence
				$binary_path = $is_mac ? self::$mac_path : self::$linux_path;
				$binary = rtrim($binary_path, '/') . '/' . $driver;

				//make sure the driver's binary is found
				//we'll run this check each time, because the user has the ability to change the path
				self::$confirmed_drivers[$driver] = file_exists($binary);
			}

			if (!self::$confirmed_drivers[$driver]) //driver is a no go
			{
				if (self::$debug_mode)
				{
					self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' It doesn\'t look like ' . $driver . ' is available.';
				}
				return false;
			}

			//run the image through the driver
			exec(escapeshellarg($binary) . ' ' . $args);
		}
		else //linux without a default binary path specified
		{
			//make sure the driver is found
			if (!isset(self::$confirmed_drivers[$driver])) //driver has not been confirmed
			{
				exec('which ' . $driver, $output = array(), $return = -1);
				self::$confirmed_drivers[$driver] = (count($output) && $return == 0);
			}
			if (!self::$confirmed_drivers[$driver]) //driver is a no go
			{
				if (self::$debug_mode)
				{
					self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' It doesn\'t look like the ' . $driver . ' is available.';
				}
				return false;
			}

			//run the image through the the driver
			exec($driver . ' ' . $args);
		}

		//most of the drivers can process the images in place, but jpegtran and pngcrush cannot, so it must be done manually
		if ($manual_copy)
		{
			if (file_exists($temp)) //the temp file was created
			{
				//copy the temp file to the original path
				if (copy($temp, $path)) //the image was copied successfully
				{
					//remove the temp file
					if (!unlink($temp) && self::$debug_mode)
					{
						if (self::$debug_mode)
						{
							self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' The image was compressed by ' . $driver . ', but there was a problem removing the temporary image it created.';
						}
					}
				}
				else //problem copying the image
				{
					if (self::$debug_mode)
					{
						self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' The temporary image was created by ' . $driver . ', but there was a problem overwriting the uncompressed image with the compressed version.';
					}
					return false;
				}
			}
			else //the temp file was not created
			{
				if (self::$debug_mode)
				{
					self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' It appears that ' . $driver . ' was unable to process the image.';
				}
				return false;
			}
		}

		//if debug mode, we'll get the final file size
		if (self::$debug_mode)
		{
			//the new file size
			self::clear_stat($path);
			$new = filesize($path);
			$difference = $orig - $new;
			$difference_percent = round((1 - ($new / $orig)) * 100, 2);

			$orig = self::convert($orig);
			$new = self::convert($new);
			$difference = self::convert($difference);

			if ($difference == 0)
			{
				self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' No compression occurred with the ' . $driver . ' driver. This could be because the image is already compressed, or there was a problem processing the image.';
			}
			else
			{
				self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' The image was compressed by ' . $driver . ' from ' . $orig . ' to ' . $new . ' for a savings of ' . $difference . ' (' . $difference_percent . '%).';
			}
		}

		return true;
	}

	/**
	 * Converts a file size from bytes to a human readable format.
	 *
	 * A method derived from a function originally posted by xelozz -at- gmail.com 18-Feb-2010 10:34 to http://us2.php.net/manual/en/function.memory-get-usage.php#96280
	 * Original code licensed under: http://creativecommons.org/licenses/by/3.0/legalcode
	 *
	 * @static
	 * @param int $size Bytes.
	 *
	 * @param int $precision
	 * @return string Human readable file size.
	 */
	public static function convert($size, $precision = 2)
	{
		$negative = false;

		if ($size == 0)
		{
			return '0 b';
		}
		else {
			if ($size < 0)
			{
				$negative = true;
				$size = abs($size);
			}
		}

		$unit = array('b', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
		$size = @round($size / pow(1024, ($i = floor(log($size, 1024)))), $precision) . ' ' . $unit[$i];
		return ($negative) ? '-' . $size : $size;
	}


	/**
	 * Compresses an image via Smush.it.
	 *
	 * @param string $path The path to the image.
	 * @return bool
	 */
	protected static function smushit($path)
	{
		if (function_exists('curl_init')) //try to use cURL
		{
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, 'http://api.resmush.it/ws.php?');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($curl, CURLOPT_POST, true);
			if ( function_exists('curl_file_create') )
			{
				curl_setopt($curl, CURLOPT_POSTFIELDS, array('files' =>  curl_file_create( $path ) ));
			}
			else
			{
				curl_setopt($curl, CURLOPT_POSTFIELDS, array('files' =>  '@' . $path));
			}
			$response = curl_exec($curl);
			curl_close($curl);
		}
		else
		{
			if (self::$debug_mode)
			{
				self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' Smush.it requires cURL to be enabled.';
			}
			return false;
		}

		//parse the curl response
		$json = json_decode($response);

		if (empty($json)) //empty response
		{
			if (self::$debug_mode)
			{
				self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' Bad response from Smush.it.';
			}
			return false;
		}

		if (isset($json->error)) //error
		{
			if (self::$debug_mode)
			{
				self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' Smush.it error: ' . $json->error;
			}
			return false;
		}

		$difference = self::convert($json->src_size - $json->dest_size);

		if ($difference > 0)
		{
			//save the smushed image
			if (!self::save_remote_image($json->dest, $path))
			{
				return false;
			}
		}
		else //hopefully this never happens...
		{
			if (self::$debug_mode)
			{
				self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' The smushed image was larger than the original, so it was not downloaded.';
			}
			return false;
		}

		if (self::$debug_mode)
		{
			self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' The image was compressed by Smush.it from ' . self::convert($json->src_size) . ' to ' . self::convert($json->dest_size) . ' for a total difference of ' . $difference . ' (' . $json->percent . '% of the original size).';
		}

		return true;
	}

	/**
	 * Saves a remote image.
	 *
	 * @param string $url The URL of the remote image.
	 * @param string $path The file path on the server.
	 *
	 * @return bool Returns true if the image is saved successfully and false otherwise.
	 */
	protected static function save_remote_image($url, $path)
	{
		@ini_set('default_socket_timeout', 30);
		@ini_set('allow_url_fopen', true);
		@ini_set('user_agent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:5.0) Gecko/20100101');

		if (function_exists('curl_init')) //try to get the image using cURL
		{
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url); //set the URL
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //no output
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, false); //no timeout
			//follow location will throw an error if safe mode or an open basedir restrictions is enabled
			if (!ini_get('open_basedir') && !ini_get('safe_mode'))
			{
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			}

			$remote_image = curl_exec($curl);
			if (empty($remote_image)) //cURL failed
			{
				if (self::$debug_mode)
				{
					self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' Could not get remote image using cURL.';
				}
				return false;
			}
		}
		else //try to get the image using file_get_contents
		{
			$remote_image = @file_get_contents($url);

			if (empty($remote_image)) //file_get_contents failed and cURL is not enabled, not much more we can do...
			{
				if (self::$debug_mode)
				{
					self::$debug_messages[] = date('[d/M/Y:H:i:s]') . ' Could not get remote image using file_get_contents.';
				}
				return false;
			}
		}

		//save the image
		if (!@is_writable($path) || file_put_contents($path, $remote_image) === false)
		{
			if (self::$debug_mode)
			{
				self::$debug_messages[] = date('[d/M/Y:H:i:s]') . " Could not write the remote image '{$path}'.";
			}
			return false;
		}

		$remote_image = null;

		if (self::$debug_mode)
		{
			self::$debug_messages[] = date('[d/M/Y:H:i:s]') . " The remote image '{$url}' was downloaded successfully.";
		}
		return true;
	}

	/**
	 * Clear the stat cache helper. Will use 5.3+ if possible to only clear a specific cache, but will fallback to clear the entire stat cache for earlier versions.
	 *
	 * @param $path
	 */
	protected static function clear_stat($path)
	{
		if (version_compare(PHP_VERSION, '5.3.0') >= 0)
		{
			clearstatcache(TRUE, $path);
		}
		else
		{
			clearstatcache();
		}
	}
}