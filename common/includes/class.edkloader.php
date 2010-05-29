<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

class edkloader
{
	private static $classes = array();
	private static $dir = "";

    public static function load($name)
    {
		$name = strtolower($name);

		if(isset(self::$classes[$name]))
		{
			require_once(self::$classes[$name]);
			return true;
		}
		else if(is_file(self::$dir."common/includes/class.".$name.".php"))
		{
			require_once(self::$dir."common/includes/class.".$name.".php");
			return true;
		}
		else
		{
			//trigger_error("Class '".addslashes($name)."' not found", E_USER_ERROR);
			return false;
		}
    }
	public static function register($name, $file)
	{
		if(!is_file($file))
		{
			trigger_error("Class '".addslashes($name)."' file '".$addslashes($file)."' not found", E_USER_WARNING);
			return false;
		}
		elseif(isset(self::$classes[$name]))
		{
			trigger_error("Class '".addslashes($name)."' already registered found", E_USER_WARNING);
			return false;
		}
		self::$classes[strtolower($name)] = $file;
		return true;
	}
	public static function unregister($name)
	{
		unset(self::$classes[strtolower($name)]);
	}
	public static function setRoot($dir)
	{
		if(!is_dir($dir)) return false;
		if(substr($dir,-1) != "/") $dir .= "/";

		self::$dir = $dir;
	}
}

spl_autoload_register('edkloader::load');
