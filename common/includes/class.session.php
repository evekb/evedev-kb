<?php
/**
 * @package EDK
 */
class session
{
	private static $vars;

	public static function init()
	{
		self::sessionName();
		if (isset($_COOKIE[session_name()])) {
			session_cache_limiter("");
			session_start();
			self::$vars = $_SESSION;

			if (isset(self::$vars['user'])) {
				user::loggedin(true);
			}
		}
	}

	public static function isAdmin()
	{
		if (!isset(self::$vars['admin'])
				|| !isset(self::$vars['rsite'])
				|| !isset(self::$vars['site'])) {
			return false;
		}
		return  (bool) self::$vars['admin']
				&& self::$vars['rsite'] == $_SERVER["HTTP_HOST"]
				&& md5(KB_SITE) == self::$vars['site']
				&& self::makeKey() == $_GET['akey']
				&& (!isset($_SERVER['HTTP_REFERER'])
						|| parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)
						== $_SERVER["HTTP_HOST"]);
	}

	public static function isSuperAdmin()
	{
		if (!isset(self::$vars['admin_super'])) {
				return false;
		}
		return (bool) self::$vars['admin_super']
				&& self::isAdmin();
	}

	public static function create($admin = false)
	{
		self::sessionName();
		session_start();
		session_regenerate_id();
		$_SESSION['admin'] = $admin;
		$_SESSION['rsite'] = $_SERVER["HTTP_HOST"];
		$_SESSION['site'] = md5(KB_SITE);
	}

	public static function destroy()
	{
		self::sessionName();
		session_start();
		session_destroy();
	}

	public static function get($key)
	{
		if (!isset(self::$vars[$key])) {
			return null;
		} else {
			return self::$vars[$key];
		}
	}

	public static function makeKey()
	{
		return hash('md5', KB_SITE.session_id());
	}

	private static function sessionName()
	{
		return session_name("EDK_".substr(hash('md5', KB_SITE), 0, 6));
	}
}