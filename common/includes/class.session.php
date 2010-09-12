<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */


class session
{
	private static $vars;

	public static function init()
	{
		session_name("EDK_".preg_replace('/[^a-zA-Z0-9_-]/', '',KB_SITE));
		if (isset($_COOKIE[session_name()]))
		{
			session_cache_limiter("");
			session_start();
			self::$vars = $_SESSION;
			session_commit();
			
			if (isset(self::$vars['user'])) user::loggedin(true);
		}
	}
	
	public static function isAdmin()
	{
		if(!isset(self::$vars['admin']) || !isset(self::$vars['rsite']) || !isset(self::$vars['site']) ) return false;
		return (bool)(self::$vars['admin'] && self::$vars['rsite'] == $_SERVER["HTTP_HOST"] && md5(KB_SITE) == self::$vars['site']);
	}

	public static function isSuperAdmin()
	{
		if(!isset(self::$vars['admin']) || !isset(self::$vars['rsite']) || !isset(self::$vars['site']) ) return false;
		return (bool)(self::$vars['admin_super'] && self::$vars['rsite'] == $_SERVER["HTTP_HOST"] && md5(KB_SITE) == self::$vars['site']);
	}

	public static function create($admin = false)
	{
		session_name("EDK_".preg_replace('/[^a-zA-Z0-9_-]/', '',KB_SITE));
		session_start();
		if(function_exists('session_regenerate_id')) session_regenerate_id();
		$_SESSION['admin'] = $admin;
		$_SESSION['rsite'] = $_SERVER["HTTP_HOST"];
		$_SESSION['site'] = md5(KB_SITE);
	}

	public static function destroy()
	{
		session_name("EDK_".preg_replace('/[^a-zA-Z0-9_-]/', '',KB_SITE));
		session_start();
		session_destroy();
	}

	public static function get($key)
	{
		if(!isset(self::$vars[$key])) return null;
		else return self::$vars[$key];
	}
}