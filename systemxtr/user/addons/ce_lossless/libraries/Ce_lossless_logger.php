<?php

/**
 * A crazy-simple logger class. Created by Aaron Waldon. All rights reserved.
 */
class Ce_lossless_logger {

	private $path;
	private $file_pointer;

	function __construct( $path )
	{
		$this->path = $path;
	}

	/**
	 * Writes a message to the log file.
	 *
	 * @param array $messages
	 * @param bool $add_separator
	 */
	public function log( $messages = array(), $add_separator = true )
	{
		if ( is_string( $messages ) )
		{
			$messages = array( $messages );
		}

		if ( ! is_array( $messages ) || empty( $messages ) )
		{
			return;
		}

		if ( $this->open() )
		{
			if ( $add_separator )
			{
				fwrite( $this->file_pointer, '----------------------' . PHP_EOL );
			}

			foreach ($messages as $message)
			{
				//write the time and message
				fwrite( $this->file_pointer, $message . PHP_EOL );
			}
		}
	}

	/**
	 * Close the log file.
	 */
	public function close()
	{
		if ( is_resource( $this->file_pointer ) )
		{
			fclose( $this->file_pointer );
		}
	}

	/**
	 * Open the log file
	 */
	private function open()
	{
		if ( is_resource( $this->file_pointer ) ) //we have a resource already, we're good
		{
			return true;
		}

		//determine if the file is new
		$new = ! @file_exists( $this->path );

		//open the log file
		$this->file_pointer = @fopen( $this->path, 'a' );

		if ( !! $this->file_pointer && $new ) //new file, try to set permissions
		{
			@chmod( $this->path, 0644 );
		}

		return is_resource( $this->file_pointer );
	}

	/**
	 * Close the log resource on destruct.
	 */
	public function __destruct()
	{
		$this->close();
	}
}