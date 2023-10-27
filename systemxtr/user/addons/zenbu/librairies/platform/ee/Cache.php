<?php namespace Zenbu\librairies\platform\ee;

use Zenbu\librairies\platform\ee\Session;

class Cache
{
	public static function set($key, $value, $duration = 120, $subfolder = FALSE)
	{
		if($subfolder === FALSE)
		{
			$subfolder = Session::user()->id;
		}

		if(isset(ee()->cache))
		{
	        ee()->cache->save('/zenbu/'.$subfolder.'/'.$key, $value, $duration);
		}
		else
		{
			Session::setCache($key, $value);
		}
	}

	public static function get($key, $subfolder = FALSE)
	{
		if(ee()->config->item('zenbuDisableCache'))
        {
            return FALSE;
        }

		if($subfolder === FALSE)
		{
			$subfolder = Session::user()->id;
		}

		if(isset(ee()->cache))
		{
	        return ee()->cache->get('/zenbu/'.$subfolder.'/'.$key);
		}
		else
		{
			Session::getCache($key);
		}
	}

	public static function delete($key = '', $subfolder = FALSE)
	{
		if($subfolder === FALSE)
		{
			$subfolder = Session::user()->id;
		}

		if(isset(ee()->cache))
		{
        	ee()->cache->delete('/zenbu/'.$subfolder.'/'.$key);
        }
	}
}