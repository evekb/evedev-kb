<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * @package EDK
 */
class edkloader
{
	private static $classes = array();
	private static $dir = "";

    public static function load($name)
    {
//		echo $name."<br />";
		$name = strtolower($name);
		$splitpos = strpos($name, "_");
		if($splitpos > 0)
		{
			$subdirname = substr($name, 0, $splitpos);
			$subfilename = substr($name, $splitpos + 1);
		}
		$name = str_replace("_", "", $name);

		if(isset(self::$classes[$name]))
		{
			require_once(self::$classes[$name]);
			return true;
		}
		else if($splitpos && is_file(self::$dir."common/includes/".$subdirname."/class.".$subfilename.".php"))
		{
			require_once(self::$dir."common/includes/".$subdirname."/class.".$subfilename.".php");
			return true;
		}
		else if(is_file(self::$dir."common/includes/class.".$name.".php"))
		{
			require_once(self::$dir."common/includes/class.".$name.".php");
			return true;
		}
		else
		{
			return false;
		}
    }
	public static function register($name, $file)
	{
		if(!is_file($file))
		{
			trigger_error("Class '".addslashes($name)."' file '".addslashes($file)."' not found", E_USER_WARNING);
			return false;
		}
		elseif(isset(self::$classes[strtolower($name)]))
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
