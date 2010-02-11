<?php

class session
{
	public static function init()
	{
		session_name("EDK_".preg_replace('/[^a-zA-Z0-9_-]/', '',KB_SITE));
		if (isset($_COOKIE[session_name()]))
		{
			session_cache_limiter("");
			session_start();
			if (isset($_SESSION['user']))
			{
				user::loggedin(true);
			}
		}
	}
	
	public static function isAdmin()
	{
		if(!isset($_SESSION['admin']) || !isset($_SESSION['rsite']) || !isset($_SESSION['site']) ) return false;
		return (bool)($_SESSION['admin'] && $_SESSION['rsite'] == $_SERVER["HTTP_HOST"] && md5(KB_SITE) == $_SESSION['site']);
	}

	public static function isSuperAdmin()
	{
		if(!isset($_SESSION['admin']) || !isset($_SESSION['rsite']) || !isset($_SESSION['site']) ) return false;
		return (bool)($_SESSION['admin_super'] && $_SESSION['rsite'] == $_SERVER["HTTP_HOST"] && md5(KB_SITE) == $_SESSION['site']);
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
		session_destroy();
	}
}