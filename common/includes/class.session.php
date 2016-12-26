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
class session
{
	private static $vars;
    /** session variable name for forcing not to cache any pages */
    private static $SESSION_VAR_FORCE_NO_CACHING = "FORCE_NO_CACHING";

	public static function init()
	{
		session_name("EDK_".substr(hash('md5', KB_SITE),0,6));
		if (isset($_COOKIE[session_name()]))
		{
			session_cache_limiter("");
			session_start();
			self::$vars = $_SESSION;
			//session_commit();
			
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
		if(!isset(self::$vars['admin_super']) || !isset(self::$vars['rsite']) || !isset(self::$vars['site']) ) return false;
		return (bool)(self::$vars['admin_super'] && self::$vars['rsite'] == $_SERVER["HTTP_HOST"] && md5(KB_SITE) == self::$vars['site']);
	}

	public static function create($admin = false)
	{
		session_name("EDK_".substr(hash('md5', KB_SITE),0,6));
		session_start();
		if(function_exists('session_regenerate_id')) session_regenerate_id();
		$_SESSION['admin'] = $admin;
		$_SESSION['rsite'] = $_SERVER["HTTP_HOST"];
		$_SESSION['site'] = md5(KB_SITE);
	}

	public static function destroy()
	{
		session_name("EDK_".substr(hash('md5', KB_SITE),0,6));
		session_start();
		session_destroy();
	}

	public static function get($key)
	{
		if(!isset(self::$vars[$key])) return null;
		else return self::$vars[$key];
	}
    
    /**
     * Sets a flag to forcibly disabling page caching. This flag will be evaluated by the cache handler.
     * 
     * @param bool $noCaching flag indicating whether to force disabling page caching for this session
     */
    public static function forceNoCaching($noCaching = true)
    {
        if(!isset(self::$vars))
        {
            self::create();
        }
        $_SESSION[self::$SESSION_VAR_FORCE_NO_CACHING] = (bool) $noCaching;
        self::$vars[self::$SESSION_VAR_FORCE_NO_CACHING] = $_SESSION[self::$SESSION_VAR_FORCE_NO_CACHING];
    }
    
    /**
     * Evaluates the session's flag indicating not to cache any site viewed during this session
     * @return boolean <code>true</code> if caching is forcibly disabled, otherwise <code>false</code>
     */
    public static function isCachingForciblyDisabled()
    {
        if(!isset(self::$vars[self::$SESSION_VAR_FORCE_NO_CACHING])) return false;
		return (bool)(self::$vars[self::$SESSION_VAR_FORCE_NO_CACHING]);
    }
}